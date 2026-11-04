<?php

/**
 * Importa los cargadores públicos de Uruguay desde Open Charge Map (OCM).
 *
 * OCM aporta ubicación y datos técnicos (potencia, conector). NO aporta si el cargador está ocupado ahora:
 * eso lo informan los usuarios con los reportes. Por eso la sincronización ACTUALIZA (no borra y reinserta):
 *   - se refrescan los datos fijos (potencia, conector, tipo de corriente, dirección, ubicación);
 *   - NO se toca el estado de uso, la fila de espera, el tiempo estimado ni los precios;
 *   - el estado operativo de OCM no pisa un reporte de la comunidad de las últimas 24 h;
 *   - los conectores que OCM ya no lista se eliminan.
 */

/** Último motivo de fallo de la sincronización (se muestra en cron_sync.php). */
$GLOBALS['ocm_sync_error'] = '';
function ocmFallo($mensaje)
{
    $GLOBALS['ocm_sync_error'] = $mensaje;
    error_log("[OCM Sync Error] " . $mensaje);
    return false;
}

/** Sincroniza los cargadores de OCM con la base de datos. */
function sincronizarCargadoresOCM($conexion)
{
    // Ampliar el tiempo máximo de ejecución a 5 minutos para evitar timeouts de PHP
    set_time_limit(300);
    ini_set('default_socket_timeout', 300);

    $OCM_API_KEY = $_ENV['OCM_API_KEY'] ?? getenv('OCM_API_KEY') ?? '';
    $apiUrl = "https://api.openchargemap.io/v3/poi?output=json&countrycode=UY&maxresults=700&key=$OCM_API_KEY";

    // cURL para obtener los datos de OCM (con un timeout de 120 segundos)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_USERAGENT, 'VoltMap-App');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return ocmFallo("Fallo al consultar la API de OCM (HTTP $httpCode). cURL: " . ($curlError ?: 'sin error de cURL'));
    }

    $stations = json_decode($response, true);
    if (!is_array($stations)) {
        return ocmFallo("OCM respondió algo que no es una lista JSON: " . substr((string)$response, 0, 200));
    }

    return importarEstacionesOCM($conexion, $stations);
}

/** Importa un arreglo de estaciones con el formato de OCM (separado para poder probarlo sin internet). */
function importarEstacionesOCM($conexion, array $stations)
{
    $conexion->begin_transaction();

    try {
        // Un punto cuyo id ya pertenece a un usuario de VoltMap nunca se pisa
        $stmtDueno = $conexion->prepare("SELECT id_usuario FROM puntos_carga WHERE id = ?");

        $stmtPunto = $conexion->prepare(
            "INSERT INTO puntos_carga (id, id_usuario, visible, tipoUsuario, latitud, longitud, direccion, ciudadYDepartamento)
            VALUES (?, NULL, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                visible = VALUES(visible), tipoUsuario = VALUES(tipoUsuario),
                latitud = VALUES(latitud), longitud = VALUES(longitud),
                direccion = VALUES(direccion), ciudadYDepartamento = VALUES(ciudadYDepartamento)"
        );
        // Solo se actualizan los datos fijos. estadoUso, fila_espera, libre_estimado y precios se conservan
        $stmtCargador = $conexion->prepare(
            "INSERT INTO cargadores (id, idPuntoCarga, potenciaKilowatts, tipoConector, tipoCargador, estadoUso, estadoOperativo, precioKwh, precioHora)
             VALUES (?, ?, ?, ?, ?, 'Disponible', ?, 0, 0)
             ON DUPLICATE KEY UPDATE
                potenciaKilowatts = VALUES(potenciaKilowatts),
                tipoConector = VALUES(tipoConector),
                tipoCargador = VALUES(tipoCargador),
                estadoOperativo = IF(ultimo_reporte IS NOT NULL AND ultimo_reporte > (NOW() - INTERVAL 24 HOUR),
                                     estadoOperativo, VALUES(estadoOperativo))"
        );

        foreach ($stations as $station) {
            $puntoId = (int)($station['ID'] ?? 0);
            if ($puntoId <= 0) {
                continue;
            }

            $stmtDueno->bind_param("i", $puntoId);
            $stmtDueno->execute();
            $fila = $stmtDueno->get_result()->fetch_assoc();
            if ($fila && (int)$fila['id_usuario'] > 0) {
                error_log("[OCM Sync] Se omite el punto $puntoId: el id ya pertenece a un cargador publicado por un usuario.");
                continue;
            }

            $address = $station['AddressInfo'] ?? [];
            $visible = ($station['IsOperational'] ?? true) ? 1 : 0;
            $tipoUsuario = $station['OperatorInfo']['Title'] ?? 'Público';
            $latitud = $address['Latitude'] ?? 0;
            $longitud = $address['Longitude'] ?? 0;
            $direccion = $address['AddressLine1'] ?? 'Sin dirección';
            $ciudadYDepartamento = trim(($address['Town'] ?? '') . ', ' . ($address['StateOrProvince'] ?? ''));

            $latitud = (float)$latitud;
            $longitud = (float)$longitud;
            $stmtPunto->bind_param("iisddss", $puntoId, $visible, $tipoUsuario, $latitud, $longitud, $direccion, $ciudadYDepartamento);
            $stmtPunto->execute();

            if (empty($station['Connections'])) {
                continue;
            }

            $idsVigentes = [];
            foreach ($station['Connections'] as $index => $conn) {
                $cantidad = isset($conn['Quantity']) && $conn['Quantity'] > 0 ? (int)$conn['Quantity'] : 1;

                for ($q = 0; $q < $cantidad; $q++) {
                    $cId = "{$puntoId}-{$index}-{$q}";
                    $idsVigentes[] = $cId;

                    $potencia = (float)($conn['PowerKW'] ?? 0);
                    $tConector = $conn['ConnectionType']['Title'] ?? 'Desconocido';
                    $tCargador = $conn['CurrentType']['Title'] ?? ($conn['Level']['Title'] ?? 'Desconocido');
                    $eOp = $conn['StatusType']['Title'] ?? ($station['StatusType']['Title'] ?? 'Desconocido');

                    // Tipos: s,i,d,s,s,s  (antes tipoCargador se ponía 's' aunque fuera 'i' o 'd', pero no importa mucho)
                    $stmtCargador->bind_param("sidsss", $cId, $puntoId, $potencia, $tConector, $tCargador, $eOp);
                    $stmtCargador->execute();
                }
            }

            // Se eliminan solo los conectores de este punto que OCM ya no lista
            $marcas = implode(',', array_fill(0, count($idsVigentes), '?'));
            $stmtDel = $conexion->prepare("DELETE FROM cargadores WHERE idPuntoCarga = ? AND id NOT IN ($marcas)");
            $stmtDel->bind_param("i" . str_repeat("s", count($idsVigentes)), $puntoId, ...$idsVigentes);
            $stmtDel->execute();
            $stmtDel->close();
        }

        $stmtDueno->close();
        $stmtPunto->close();
        $stmtCargador->close();
        $conexion->commit();
        return true;
    } catch (Throwable $e) {
        $conexion->rollback();
        return ocmFallo("Excepción al guardar en la base de datos: " . $e->getMessage());
    }
}