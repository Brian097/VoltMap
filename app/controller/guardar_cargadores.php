<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../model/conexion.php'; // Tu archivo de conexión existente

// 1. Leer el JSON enviado por fetch desde JavaScript
$inputJSON = file_get_contents('php://input');
$puntosCarga = json_decode($inputJSON, true);

if (!$puntosCarga || !is_array($puntosCarga)) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos o vacíos.']);
    exit;
}

$conexion->begin_transaction();

try {
    foreach ($puntosCarga as $punto) {
        $id = $punto['id'];
        $visible = $punto['visible'] ? 1 : 0;
        $tipoUsuario = $punto['tipoUsuario'];
        $latitud = $punto['latitud'];
        $longitud = $punto['longitud'];
        $direccion = $punto['direccion'];
        $ciudadYDepartamento = $punto['ciudadYDepartamento'];

        // 2. Insertar o actualizar el Punto de Carga (upsert usando MySQL)
        $sqlPunto = "INSERT INTO puntos_carga (id, visible, tipoUsuario, latitud, longitud, direccion, ciudadYDepartamento) 
                     VALUES (?, ?, ?, ?, ?, ?, ?) 
                     ON DUPLICATE KEY UPDATE 
                     visible = VALUES(visible), 
                     tipoUsuario = VALUES(tipoUsuario), 
                     latitud = VALUES(latitud), 
                     longitud = VALUES(longitud), 
                     direccion = VALUES(direccion), 
                     ciudadYDepartamento = VALUES(ciudadYDepartamento)";
        
        $stmtPunto = $conexion->prepare($sqlPunto);
        $stmtPunto->bind_param("iissdss", $id, $visible, $tipoUsuario, $latitud, $longitud, $direccion, $ciudadYDepartamento);
        $stmtPunto->execute();
        $stmtPunto->close();

        // 3. Procesar y guardar los cargadores (conectores) asociados
        if (!empty($punto['cargadores'])) {
            foreach ($punto['cargadores'] as $cargador) {
                $cId = $cargador['id'];
                $idPunto = $cargador['idPuntoCarga'];
                $potencia = $cargador['potenciaKilowatts'];
                $tConector = $cargador['tipoConector'];
                $tCargador = $cargador['tipoCargador'];
                $eUso = $cargador['estadoUso'];
                $eOp = $cargador['estadoOperativo'];
                $pKwh = $cargador['precioKwh'];
                $pHora = $cargador['precioHora'];

                $sqlCargador = "INSERT INTO cargadores (id, idPuntoCarga, potenciaKilowatts, tipoConector, tipoCargador, estadoUso, estadoOperativo, precioKwh, precioHora) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE 
                                potenciaKilowatts = VALUES(potenciaKilowatts), 
                                tipoConector = VALUES(tipoConector), 
                                tipoCargador = VALUES(tipoCargador), 
                                estadoUso = VALUES(estadoUso), 
                                estadoOperativo = VALUES(estadoOperativo), 
                                precioKwh = VALUES(precioKwh), 
                                precioHora = VALUES(precioHora)";

                $stmtCargador = $conexion->prepare($sqlCargador);
                $stmtCargador->bind_param("sidsdssdd", $cId, $idPunto, $potencia, $tConector, $tCargador, $eUso, $eOp, $pKwh, $pHora);
                $stmtCargador->execute();
                $stmtCargador->close();
            }
        }
    }

    // Confirmar transacción en la base de datos
    $conexion->commit();
    echo json_encode(['success' => true, 'message' => 'Datos sincronizados correctamente.']);

} catch (Exception $e) {
    // Revertir cambios si algo falla
    $conexion->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>