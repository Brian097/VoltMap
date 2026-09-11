<?php
error_reporting(0);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Asegurar que la sesión esté iniciada para obtener el ID del usuario actual
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

        // Insertar o actualizar sus Cargadores
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

// B. OBTENER PUNTOS Y CARGADORES PARA EL MAPA (GET)
if ($method === 'GET') {
    // Consulta optimizada para traer los puntos públicos O los del usuario logueado en la sesión
    $sql = "SELECT 
                p.id AS punto_id, p.latitud, p.longitud, p.direccion, p.ciudadYDepartamento, p.tipoUsuario, p.visible, p.id_usuario,
                c.id AS cargador_id, c.potenciaKilowatts, c.tipoConector, c.tipoCargador, c.estadoUso, c.estadoOperativo, c.precioKwh, c.precioHora
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
                'cargadores' => []
            ];
        }

        // Si el punto tiene un cargador asociado (gracias al LEFT JOIN), lo agregamos al array
        if (!empty($row['cargador_id'])) {
            $puntosMap[$idPunto]['cargadores'][] = [
                'id' => $row['cargador_id'],
                'idPuntoCarga' => $idPunto,
                'potenciaKilowatts' => $row['potenciaKilowatts'],
                'tipoConector' => $row['tipoConector'],
                'tipoCargador' => $row['tipoCargador'],
                'estadoUso' => $row['estadoUso'],
                'estadoOperativo' => $row['estadoOperativo'],
                'precioKwh' => $row['precioKwh'],
                'precioHora' => $row['precioHora']
            ];
        }
    }

    // Convertimos el array asociativo a un array indexado para el JSON final
    echo json_encode(array_values($puntosMap));
    exit;
}