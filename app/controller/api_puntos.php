<?php
error_reporting(0);
error_reporting(E_ALL);
header('Content-Type: application/json');

// aseguraria que la sesión esté iniciada para obtener el ID del usuario actual
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$idUsuarioActual = $_SESSION['id'] ?? 0;

require_once __DIR__ . '/../model/conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

// A. GUARDAR PUNTOS Y CARGADORES (POST)
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $puntosCarga = json_decode($input, true);

    if (!$puntosCarga) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
        exit;
    }

    foreach ($puntosCarga as $punto) {
        // Insertar o actualizar Punto Carga
        $stmtPunto = $conexion->prepare("INSERT INTO puntos_carga (id, visible, tipoUsuario, latitud, longitud, direccion, ciudadYDepartamento) 
            VALUES (?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            visible=VALUES(visible), tipoUsuario=VALUES(tipoUsuario), direccion=VALUES(direccion), ciudadYDepartamento=VALUES(ciudadYDepartamento)");
        
        $visibleInt = $punto['visible'] ? 1 : 0;
        $stmtPunto->bind_param("iisddss", $punto['id'], $visibleInt, $punto['tipoUsuario'], $punto['latitud'], $punto['longitud'], $punto['direccion'], $punto['ciudadYDepartamento']);
        $stmtPunto->execute();
        $stmtPunto->close();

        // Insertar o actualizar los Cargadores
        if (!empty($punto['cargadores'])) {
            // Borramos los conectores anteriores de este punto para evitar desincronizaciones
            $stmtDel = $conexion->prepare("DELETE FROM cargadores WHERE idPuntoCarga = ?");
            $stmtDel->bind_param("i", $punto['id']);
            $stmtDel->execute();
            $stmtDel->close();

            foreach ($punto['cargadores'] as $cargador) {
                $stmtCargador = $conexion->prepare("INSERT INTO cargadores (id, idPuntoCarga, potenciaKilowatts, tipoConector, tipoCargador, estadoUso, estadoOperativo, precioKwh, precioHora) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmtCargador->bind_param("sidssssdd", 
                    $cargador['id'], $cargador['idPuntoCarga'], $cargador['potenciaKilowatts'], 
                    $cargador['tipoConector'], $cargador['tipoCargador'], $cargador['estadoUso'], 
                    $cargador['estadoOperativo'], $cargador['precioKwh'], $cargador['precioHora']
                );
                $stmtCargador->execute();
                $stmtCargador->close();
            }
        }
    }
}

// B. obtener puntos y cargadores (GET)
if ($method === 'GET') {
    // Consulta optimizada para traer los puntos públicos O los del usuario logueado en la sesión
    $sql = "SELECT 
                p.id AS punto_id, p.latitud, p.longitud, p.direccion, p.ciudadYDepartamento, p.tipoUsuario, p.visible, p.id_usuario,
                c.id AS cargador_id, c.potenciaKilowatts, c.tipoConector, c.tipoCargador, c.estadoUso, c.estadoOperativo, c.precioKwh, c.precioHora,
                c.fila_espera, c.ultimo_reporte,
                IF(c.libre_estimado IS NULL, NULL, GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), c.libre_estimado))) AS seg_restantes,
                EXISTS(SELECT 1 FROM reservas r WHERE r.cargador_id = c.id AND r.estado IN ('en_curso','pendiente')) AS reservado
            FROM puntos_carga p
            LEFT JOIN cargadores c ON p.id = c.idPuntoCarga
            WHERE p.visible = 1 OR p.id_usuario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idUsuarioActual);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $puntosMap = [];

    while ($row = $result->fetch_assoc()) {
        $idPunto = $row['punto_id'];

        // Si el punto aún no está en nuestro array temporal, lo inicializamos
        if (!isset($puntosMap[$idPunto])) {
            $puntosMap[$idPunto] = [
                'id' => $idPunto,
                'latitud' => $row['latitud'],
                'longitud' => $row['longitud'],
                'direccion' => $row['direccion'],
                'ciudadYDepartamento' => $row['ciudadYDepartamento'],
                'tipoUsuario' => $row['tipoUsuario'],
                'visible' => $row['visible'],
                // Un punto es "privado" (alquilable) si lo publicó un usuario de VoltMap
                'esPrivado' => (int)$row['id_usuario'] > 0,
                'esMio' => $idUsuarioActual > 0 && (int)$row['id_usuario'] === (int)$idUsuarioActual,
                'cargadores' => []
            ];
        }

        // Si el punto tiene un cargador asociado (gracias al LEFT JOIN), lo agregamos al array
        if (!empty($row['cargador_id'])) {
            $estadoUso = $row['estadoUso'];
            $segRestantes = $row['seg_restantes'] === null ? null : (int)$row['seg_restantes'];
            $reservado = (bool)$row['reservado'];
            // Si ya pasó la hora estimada y nadie lo tiene reservado en VoltMap, se considera libre
            if ($segRestantes === 0 && !$reservado && stripos($estadoUso, 'uso') !== false) {
                $estadoUso = 'Disponible';
            }
            $puntosMap[$idPunto]['cargadores'][] = [
                'id' => $row['cargador_id'],
                'idPuntoCarga' => $idPunto,
                'potenciaKilowatts' => $row['potenciaKilowatts'],
                'tipoConector' => $row['tipoConector'],
                'tipoCargador' => $row['tipoCargador'],
                'estadoUso' => $estadoUso,
                'estadoOperativo' => $row['estadoOperativo'],
                'precioKwh' => $row['precioKwh'],
                'precioHora' => $row['precioHora'],
                'filaEspera' => (int)$row['fila_espera'],
                'minutosRestantes' => $segRestantes === null ? null : (int)ceil($segRestantes / 60),
                'reservado' => $reservado,
                'ultimoReporte' => $row['ultimo_reporte']
            ];
        }
    }

    // Convertimos el array asociativo a un array indexado para el JSON final
    echo json_encode(array_values($puntosMap));
    exit;
}