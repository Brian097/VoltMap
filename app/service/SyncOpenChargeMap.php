<?php
function sincronizarCargadoresOCM($conexion) {
    $OCM_API_KEY = $_ENV['OCM_API_KEY'] ?? getenv('OCM_API_KEY') ?? '';
    $apiUrl = "https://api.openchargemap.io/v3/poi?output=json&countrycode=UY&maxresults=700&key=$OCM_API_KEY";


    $options = [
        "http" => [
            "header" => "User-Agent: VoltMap-App\r\n"
        ]
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        return false;
    }

    $stations = json_decode($response, true);
    if (!is_array($stations)) {
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
                    // Obtenemos la cantidad que indica OCM (si no viene o está vacío, asumimos al menos 1)
                    $cantidadConectores = isset($conn['Quantity']) && $conn['Quantity'] > 0 ? (int)$conn['Quantity'] : 1;

                    // Iteramos tantas veces como indique la cantidad física del conector
                    for ($q = 0; $q < $cantidadConectores; $q++) {
                        // Creamos un ID único combinando el punto, el índice del array y la subcantidad
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
        return false;
    }
}