<?php
// C:\xampp\htdocs\app\view\inc\cron_sync.php


require_once __DIR__ . '/../../model/conexion.php';
require_once __DIR__ . '/../../service/SyncOpenChargeMap.php';

$resultado = sincronizarCargadoresOCM($conexion);

if ($resultado) {
    echo "[" . date('Y-m-d H:i:s') . "] Sincronización exitosa con OCM.\n";
} else {
    echo "[" . date('Y-m-d H:i:s') . "] Error al sincronizar con OCM.\n";
}

?>
