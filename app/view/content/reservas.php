<?php
require_once __DIR__ . "/../../view/inc/auth.php";
require_once __DIR__ . "/../../view/inc/lang.php";
require_once __DIR__ . "/../../controller/helpers.php";
require_once __DIR__ . "/../../model/conexion.php";
/** @var array $lang */
/** @var string $idioma_actual */

$usuario_id = (int)$_SESSION['id'];   // antes: $_SESSION['usuario_id'] ?? 1

$sql = "SELECT r.id, p.direccion AS ubicacion, r.fecha, r.hora_inicio, r.hora_fin, r.estado
          FROM reservas r
          INNER JOIN cargadores c ON r.cargador_id = c.id
          INNER JOIN puntos_carga p ON c.idPuntoCarga = p.id
         WHERE r.usuario_id = ?
         ORDER BY r.id DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$reservas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$csrf = vm_csrf();
$tx = fn($k, $d) => vm_h($lang[$k] ?? $d);
?>
<!DOCTYPE html>
<html lang="<?php echo vm_h($idioma_actual); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tx('vm_mis_reservas', 'Mis reservas y cargas'); ?></title>
    <link rel="stylesheet" href="../css/entrega3.css">
    <link rel="icon" type="image/x-icon" href="/app/view/img/VoltMap-ICO.ico">
</head>
<body class="vm-pagina">
<div class="vm-card">
    <h1><?php echo $tx('vm_mis_reservas', 'Mis reservas y cargas'); ?></h1>

    <?php if (empty($reservas)): ?>
        <p class="vm-nota"><?php echo $tx('vm_sin_reservas', 'No tenés reservas en tu historial.'); ?></p>
    <?php else: ?>
        <div class="vm-scroll">
        <table class="vm-tabla">
            <thead><tr>
                <th><?php echo $tx('vm_ubicacion', 'Ubicación'); ?></th><th><?php echo $tx('vm_fecha', 'Fecha'); ?></th>
                <th><?php echo $tx('vm_horario', 'Horario'); ?></th><th><?php echo $tx('vm_estado', 'Estado'); ?></th>
                <th><?php echo $tx('vm_accion', 'Acción'); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($reservas as $res):
                $est = $res['estado'];
                $activa = in_array($est, ['en_curso', 'pendiente'], true); ?>
                <tr>
                    <td><?php echo vm_h($res['ubicacion']); ?></td>
                    <td><?php echo vm_h($res['fecha']); ?></td>
                    <td><?php echo vm_h(substr((string)$res['hora_inicio'], 0, 5)); ?><?php echo $res['hora_fin'] ? ' - ' . vm_h(substr((string)$res['hora_fin'], 0, 5)) : ''; ?></td>
                    <td><span class="vm-estado <?php echo vm_h($est); ?>"><?php echo $tx('vm_est_' . $est, ucfirst($est)); ?></span></td>
                    <td>
                    <?php if ($activa): ?>
                        <a class="vm-btn verde" style="text-decoration:none;display:inline-block" href="checkout.php?reserva=<?php echo (int)$res['id']; ?>"><?php echo $tx('vm_finalizar_pagar', 'Finalizar y pagar'); ?></a>
                        <form action="/app/controller/controlador_reserva.php" method="POST" style="display:inline">
                            <input type="hidden" name="accion" value="cancelar">
                            <input type="hidden" name="reserva_id" value="<?php echo (int)$res['id']; ?>">
                            <input type="hidden" name="csrf" value="<?php echo vm_h($csrf); ?>">
                            <button class="vm-btn sec" type="submit"><?php echo $tx('vm_cancelar', 'Cancelar'); ?></button>
                        </form>
                    <?php else: ?>-<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>

    <a class="vm-enlace" href="recibos.php"><?php echo $tx('vm_ver_recibos', 'Ver historial de recibos'); ?></a>
    <a class="vm-enlace" href="mapa.php"><?php echo $tx('vm_volver_mapa', '← Volver al mapa'); ?></a>
</div>
</body>
</html>
