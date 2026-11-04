<?php
require_once __DIR__ . "/../../view/inc/auth.php";
require_once __DIR__ . "/../../view/inc/lang.php";
require_once __DIR__ . "/../../controller/helpers.php";
require_once __DIR__ . "/../../model/conexion.php";
/** @var array $lang */
/** @var string $idioma_actual */

$uid = (int)$_SESSION['id'];
$tx = fn($k, $d) => vm_h($lang[$k] ?? $d);

// Función para obtener los recibos de pagos y cobros del usuario
function vm_recibos(mysqli $db, string $columna, int $uid): array {
    $sql = "SELECT rc.*, p.direccion FROM recibos rc
              JOIN cargadores c ON c.id = rc.cargador_id JOIN puntos_carga p ON p.id = c.idPuntoCarga
             WHERE rc.$columna = ? ORDER BY rc.id DESC";
    $st = $db->prepare($sql);
    $st->bind_param('i', $uid);
    $st->execute();
    $f = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $f;
}
$pagos  = vm_recibos($conexion, 'cliente_id', $uid);
$cobros = vm_recibos($conexion, 'propietario_id', $uid);
$totalPagado    = array_sum(array_column($pagos, 'monto_total'));
$totalAcreditado = array_sum(array_column($cobros, 'monto_propietario'));
$nuevo = (int)($_GET['nuevo'] ?? 0);
?>
<!DOCTYPE html>
<html lang="<?php echo vm_h($idioma_actual); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tx('vm_historial_recibos', 'Historial de recibos'); ?></title>
    <link rel="stylesheet" href="../css/entrega3.css">
    <link rel="icon" type="image/x-icon" href="/app/view/img/VoltMap-ICO.ico">
</head>
<body class="vm-pagina">
<div class="vm-card">
    <h1><?php echo $tx('vm_historial_recibos', 'Historial de recibos'); ?></h1>
    <?php if ($nuevo): ?><div class="vm-aviso ok"><?php echo $tx('vm_recibo_generado', '¡Pago aprobado!'); ?> (#<?php echo $nuevo; ?>)</div><?php endif; ?>

    <h2><?php echo $tx('vm_mis_pagos', 'Mis pagos (como cliente)'); ?></h2>
    <?php if (!$pagos): ?><p class="vm-nota"><?php echo $tx('vm_sin_pagos', 'Todavía no realizaste pagos.'); ?></p>
    <?php else: ?>
    <div class="vm-scroll"><table class="vm-tabla">
        <thead><tr><th><?php echo $tx('vm_nro', 'N.º'); ?></th><th><?php echo $tx('vm_fecha', 'Fecha'); ?></th><th><?php echo $tx('vm_ubicacion', 'Ubicación'); ?></th>
            <th class="num">min</th><th class="num">kWh</th><th class="num"><?php echo $tx('vm_monto', 'Monto'); ?></th><th><?php echo $tx('vm_tarjeta', 'Tarjeta'); ?></th></tr></thead>
        <tbody>
        <?php foreach ($pagos as $x): ?>
            <tr><td>#<?php echo (int)$x['id']; ?></td><td><?php echo vm_h($x['fecha']); ?></td><td><?php echo vm_h($x['direccion']); ?></td>
                <td class="num"><?php echo (int)$x['minutos']; ?></td><td class="num"><?php echo number_format($x['kwh'], 2); ?></td>
                <td class="num">$<?php echo number_format($x['monto_total'], 2); ?></td><td>•••• <?php echo vm_h($x['tarjeta_ult4']); ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <div class="vm-total-linea"><?php echo $tx('vm_total_pagado', 'Total pagado'); ?>: $<?php echo number_format($totalPagado, 2); ?></div>
    <?php endif; ?>

    <h2><?php echo $tx('vm_mis_cobros', 'Mis cobros (como propietario)'); ?></h2>
    <?php if (!$cobros): ?><p class="vm-nota"><?php echo $tx('vm_sin_cobros', 'Todavía no recibiste cobros.'); ?></p>
    <?php else: ?>
    <div class="vm-scroll"><table class="vm-tabla">
        <thead><tr><th><?php echo $tx('vm_nro', 'N.º'); ?></th><th><?php echo $tx('vm_fecha', 'Fecha'); ?></th><th><?php echo $tx('vm_ubicacion', 'Ubicación'); ?></th>
            <th class="num"><?php echo $tx('vm_monto', 'Monto'); ?></th><th class="num"><?php echo $tx('vm_comision', 'Comisión'); ?></th><th class="num"><?php echo $tx('vm_neto', 'Acreditado'); ?></th></tr></thead>
        <tbody>
        <?php foreach ($cobros as $x): ?>
            <tr><td>#<?php echo (int)$x['id']; ?></td><td><?php echo vm_h($x['fecha']); ?></td><td><?php echo vm_h($x['direccion']); ?></td>
                <td class="num">$<?php echo number_format($x['monto_total'], 2); ?></td>
                <td class="num">-$<?php echo number_format($x['comision'], 2); ?> (<?php echo rtrim(rtrim(number_format($x['comision_pct'], 2), '0'), '.'); ?>%)</td>
                <td class="num"><b>$<?php echo number_format($x['monto_propietario'], 2); ?></b></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <div class="vm-total-linea"><?php echo $tx('vm_total_acreditado', 'Total acreditado'); ?>: $<?php echo number_format($totalAcreditado, 2); ?></div>
    <?php endif; ?>

    <a class="vm-enlace" href="reservas.php"><?php echo $tx('vm_mis_reservas', 'Mis reservas y cargas'); ?></a>
    <a class="vm-enlace" href="mapa.php"><?php echo $tx('vm_volver_mapa', '← Volver al mapa'); ?></a>
</div>
</body>
</html>
