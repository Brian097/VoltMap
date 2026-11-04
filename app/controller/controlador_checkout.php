<?php
/**
 * checkout simulado. Recalcula todo en el servidor (no confía en lo que muestra el formulario),
 * cobra con una "tarjeta ficticia", reparte comisión / propietario y guarda el recibo.
 * Solo se guardan los últimos 4 dígitos: nunca el número completo ni el CVV.
 * el numero de tarjeta de prueba 4242 4242 4242 4242 siempre aprueba el pago.
 * el numero de tarjeta de prueba 4000 0000 0000 0002 simula fondos insuficientes y rechaza el pago.
 * (aparece en el programa para acordarse)
 */
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../model/conexion.php';
require_once __DIR__ . '/../model/calculos.php';

$uid = vm_usuario_id();
if (!$uid) {
    vm_redirect('/app/view/content/login.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !vm_csrf_ok($_POST['csrf'] ?? null)) {
    vm_redirect('/app/view/content/reservas.php');
}

$reservaId = (int)($_POST['reserva_id'] ?? 0);
$volver = '/app/view/content/checkout.php?reserva=' . $reservaId;

$titular = trim((string)($_POST['titular'] ?? ''));
$numero  = preg_replace('/\D/', '', (string)($_POST['numero'] ?? ''));
$venc    = trim((string)($_POST['vencimiento'] ?? ''));
$cvv     = trim((string)($_POST['cvv'] ?? ''));

// Validaciones de la tarjeta ficticia (flujos alternativos del CU de pago)
if ($titular === '' || !preg_match('/^\d{3,4}$/', $cvv) || !tarjetaValidaLuhn($numero)) {
    vm_redirect($volver . '&error=tarjeta');
}
if (!vencimientoValido($venc)) {
    vm_redirect($volver . '&error=vencida');
}
if ($numero === '4000000000000002') {   
    vm_redirect($volver . '&error=fondos');
}

$conexion->begin_transaction();
try {
    $r = vm_resumen_reserva($conexion, $reservaId, $uid, true);   
    if (!$r) {
        $conexion->rollback();
        vm_redirect('/app/view/content/reservas.php');
    }
    $minutos = $r['minutos'];
    $kwh     = $r['kwh'];
    $pct     = $r['comision_pct'];
    $reparto = ['total' => $r['total'], 'comision' => $r['comision'], 'propietario' => $r['monto_propietario']];
    $ult4    = substr($numero, -4);

    $st = $conexion->prepare(
        "INSERT INTO recibos (reserva_id, cliente_id, propietario_id, cargador_id, minutos, kwh,
                              monto_total, comision_pct, comision, monto_propietario, tarjeta_ult4)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $st->bind_param('iiisiddddds', $reservaId, $uid, $r['propietario_id'], $r['cargador_id'], $minutos, $kwh,
                    $reparto['total'], $pct, $reparto['comision'], $reparto['propietario'], $ult4);
    $st->execute();
    $reciboId = $conexion->insert_id;
    $st->close();

    $st = $conexion->prepare("UPDATE reservas SET estado = 'finalizada', fin_real = NOW(), hora_fin = CURTIME() WHERE id = ?");
    $st->bind_param('i', $reservaId);
    $st->execute();
    $st->close();

    $st = $conexion->prepare("UPDATE cargadores SET estadoUso = 'Disponible', libre_estimado = NULL WHERE id = ?");
    $st->bind_param('s', $r['cargador_id']);
    $st->execute();
    $st->close();

    $conexion->commit();
    vm_redirect('/app/view/content/recibos.php?nuevo=' . $reciboId);
} catch (Throwable $e) {
    $conexion->rollback();
    vm_redirect($volver . '&error=servidor');
}
