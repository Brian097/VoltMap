<?php
require_once __DIR__ . "/../../view/inc/auth.php";
require_once __DIR__ . "/../../view/inc/lang.php";
require_once __DIR__ . "/../../controller/helpers.php";
require_once __DIR__ . "/../../model/conexion.php";
/** @var array $lang */
/** @var string $idioma_actual */

$reservaId = (int)($_GET['reserva'] ?? 0);
$r = vm_resumen_reserva($conexion, $reservaId, (int)$_SESSION['id']);
if (!$r) { vm_redirect('reservas.php'); }

$tx = fn($k, $d) => vm_h($lang[$k] ?? $d);
$errores = ['tarjeta' => 'vm_err_tarjeta', 'vencida' => 'vm_err_vencida', 'fondos' => 'vm_err_fondos', 'servidor' => 'vm_err_servidor'];
$claveError = $errores[$_GET['error'] ?? ''] ?? null;
$tarifa = (float)$r['precioKwh'] > 0
    ? '$' . number_format((float)$r['precioKwh'], 2) . ' / kWh'
    : '$' . number_format((float)$r['precioHora'], 2) . ' / h';
?>
<!DOCTYPE html>
<html lang="<?php echo vm_h($idioma_actual); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tx('vm_checkout_titulo', 'Finalizar carga y pagar'); ?></title>
    <link rel="stylesheet" href="../css/entrega3.css">
    <link rel="icon" type="image/x-icon" href="/app/view/img/VoltMap-ICO.ico">
</head>
<body class="vm-pagina">
<div class="vm-card estrecha">
    <h1><?php echo $tx('vm_checkout_titulo', 'Finalizar carga y pagar'); ?></h1>
    <?php if ($claveError): ?><div class="vm-aviso err"><?php echo $tx($claveError, 'Error'); ?></div><?php endif; ?>

    <h2><?php echo $tx('vm_resumen', 'Resumen de la carga'); ?></h2>
    <div class="vm-resumen">
        <div><span><?php echo $tx('vm_cargador', 'Cargador'); ?></span><span><?php echo vm_h($r['direccion']); ?> · <?php echo vm_h($r['tipoConector']); ?></span></div>
        <div><span><?php echo $tx('vm_duracion', 'Duración'); ?></span><span><?php echo (int)$r['minutos']; ?> min</span></div>
        <div><span><?php echo $tx('vm_energia', 'Energía entregada'); ?></span><span><?php echo number_format($r['kwh'], 2); ?> kWh</span></div>
        <div><span><?php echo $tx('vm_tarifa', 'Tarifa'); ?></span><span><?php echo vm_h($tarifa); ?></span></div>
        <div class="total"><span><?php echo $tx('vm_total', 'Total a pagar'); ?></span><span>$<?php echo number_format($r['total'], 2); ?></span></div>
        <div class="sub"><span><?php echo $tx('vm_comision_plataforma', 'Comisión de la plataforma'); ?> (<?php echo rtrim(rtrim(number_format($r['comision_pct'], 2), '0'), '.'); ?>%)</span><span>$<?php echo number_format($r['comision'], 2); ?></span></div>
        <div class="sub"><span><?php echo $tx('vm_acreditado_prop', 'Se acredita al propietario'); ?></span><span>$<?php echo number_format($r['monto_propietario'], 2); ?></span></div>
    </div>

    <div class="vm-aviso info"><?php echo $tx('vm_pago_simulado', 'Pago simulado.'); ?></div>
    <form method="POST" action="/app/controller/controlador_checkout.php" autocomplete="off">
        <input type="hidden" name="csrf" value="<?php echo vm_h(vm_csrf()); ?>">
        <input type="hidden" name="reserva_id" value="<?php echo (int)$r['id']; ?>">
        <label class="vm-campo"><?php echo $tx('vm_titular', 'Titular'); ?><input type="text" name="titular" required maxlength="60"></label>
        <label class="vm-campo"><?php echo $tx('vm_numero_tarjeta', 'Número de tarjeta'); ?><input type="text" name="numero" inputmode="numeric" required maxlength="23" placeholder="4242 4242 4242 4242"></label>
        <div style="display:flex;gap:10px">
            <label class="vm-campo" style="flex:1"><?php echo $tx('vm_vencimiento', 'Vencimiento (MM/AA)'); ?><input type="text" name="vencimiento" required maxlength="7" placeholder="12/30"></label>
            <label class="vm-campo" style="flex:1"><?php echo $tx('vm_cvv', 'CVV'); ?><input type="password" name="cvv" inputmode="numeric" required maxlength="4"></label>
        </div>
        <button class="vm-btn verde grande" type="submit"><?php echo $tx('vm_pagar', 'Pagar'); ?> $<?php echo number_format($r['total'], 2); ?></button>
    </form>
    <a class="vm-enlace" href="reservas.php"><?php echo $tx('vm_cancelar', 'Cancelar'); ?></a>
</div>
</body>
</html>
