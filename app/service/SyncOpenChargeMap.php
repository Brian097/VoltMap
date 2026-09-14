<?php
function sincronizarCargadoresOCM($conexion) {
    // Ampliar el tiempo máximo de ejecución a 5 minutos para evitar timeouts de PHP
    set_time_limit(300);
    ini_set('default_socket_timeout', 300);

    $OCM_API_KEY = $_ENV['OCM_API_KEY'] ?? getenv('OCM_API_KEY') ?? '';
    $apiUrl = "https://api.openchargemap.io/v3/poi?output=json&countrycode=UY&maxresults=700&key=$OCM_API_KEY";

    // Usar cURL en lugar de file_get_contents para un mejor control de errores y tiempos de espera
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Timeout de 2 minutos para la respuesta de la API
    curl_setopt($ch, CURLOPT_USERAGENT, 'VoltMap-App');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Validación de errores de red o códigos HTTP incorrectos
    if ($response === false || $httpCode !== 200) {
        error_log("[OCM Sync Error] cURL Error: " . $curlError . " | HTTP Code: " . $httpCode);
        return false;
    }

    $stations = json_decode($response, true);
    if (!is_array($stations)) {
        error_log("[OCM Sync Error] Error al decodificar JSON de la respuesta.");
        return false;
    }

    $conexion->begin_transaction();

    try {
        foreach ($stations as $station) {
            $address = $station['AddressInfo'] ?? [];
            $puntoId = $station['ID'];
            $visible = ($station['IsOperational'] ?? true) ? 1 : 0;
            $tipoUsuario = $station['OperatorInfo']['Title'] ?? 'Público';
            $latitud = $address['Latitude'] ?? 0;
            $longitud = $address['Longitude'] ?? 0;
            $direccion = $address['AddressLine1'] ?? 'Sin dirección';
            $ciudadYDepartamento = trim(($address['Town'] ?? '') . ', ' . ($address['StateOrProvince'] ?? ''));

            $sqlPunto = "INSERT INTO puntos_carga (id, visible, tipoUsuario, latitud, longitud, direccion, ciudadYDepartamento) 
                         VALUES (?, ?, ?, ?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE 
                         visible = VALUES(visible), tipoUsuario = VALUES(tipoUsuario), 
                         latitud = VALUES(latitud), longitud = VALUES(longitud), 
                         direccion = VALUES(direccion), ciudadYDepartamento = VALUES(ciudadYDepartamento)";
            
            $stmtPunto = $conexion->prepare($sqlPunto);
            $stmtPunto->bind_param("iissdss", $puntoId, $visible, $tipoUsuario, $latitud, $longitud, $direccion, $ciudadYDepartamento);
            $stmtPunto->execute();
            $stmtPunto->close();
        
            if (!empty($station['Connections'])) {
                $stmtDel = $conexion->prepare("DELETE FROM cargadores WHERE idPuntoCarga = ?");
                $stmtDel->bind_param("i", $puntoId);
                $stmtDel->execute();
                $stmtDel->close();

                foreach ($station['Connections'] as $index => $conn) {
                    $cantidadConectores = isset($conn['Quantity']) && $conn['Quantity'] > 0 ? (int)$conn['Quantity'] : 1;

                    for ($q = 0; $q < $cantidadConectores; $q++) {
                        $cId = "{$puntoId}-{$index}-{$q}"; 
                        
                        $potencia = $conn['PowerKW'] ?? 0;
                        $tConector = $conn['ConnectionType']['Title'] ?? 'Desconocido';
                        $tCargador = $conn['CurrentType']['Title'] ?? ($conn['Level']['Title'] ?? 'Desconocido');
                        $eUso = 'Disponible';
                        $eOp = $conn['StatusType']['Title'] ?? ($station['StatusType']['Title'] ?? 'Desconocido');
                        $pKwh = 0.0;
                        $pHora = 0.0;

                        $sqlCargador = "INSERT INTO cargadores (id, idPuntoCarga, potenciaKilowatts, tipoConector, tipoCargador, estadoUso, estadoOperativo, precioKwh, precioHora) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                        $stmtCargador = $conexion->prepare($sqlCargador);
                        $stmtCargador->bind_param("sidsdssdd", $cId, $puntoId, $potencia, $tConector, $tCargador, $eUso, $eOp, $pKwh, $pHora);
                        $stmtCargador->execute();
                        $stmtCargador->close();
                    }
                }
            }
        }
        $conexion->commit();
        return true;
    } catch (Exception $e) {
        $conexion->rollback();
        // Guardar el mensaje exacto de la excepción en el log del servidor para depuración
        error_log("[OCM Sync Exception] " . $e->getMessage());
        return false;
    }
}