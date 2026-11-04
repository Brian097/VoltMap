<?php
/** El administrador cambia el porcentaje de comisión de la plataforma, el limite era hasta 50% */
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../model/conexion.php';

if (vm_tipo_usuario() !== 'administrador') {
    vm_redirect('/app/view/content/mapa.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && vm_csrf_ok($_POST['csrf'] ?? null)) {
    $pct = (float)str_replace(',', '.', (string)($_POST['comision'] ?? ''));
    if ($pct >= 0 && $pct <= 50) {
        $v = (string)$pct;
        $st = $conexion->prepare("INSERT INTO configuracion (clave, valor) VALUES ('comision_porcentaje', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        $st->bind_param('s', $v);
        $st->execute();
        vm_redirect('/app/view/content/panel_admin.php?ok=1');
    }
    vm_redirect('/app/view/content/panel_admin.php?error=1');
}
vm_redirect('/app/view/content/panel_admin.php');
