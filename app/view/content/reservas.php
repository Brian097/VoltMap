<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../model/conexion.php";

$usuario_id = $_SESSION['usuario_id'] ?? 1;

// Cambiamos p.ubicacion por p.direccion
$sql = "SELECT r.id, p.direccion AS ubicacion, r.fecha, r.hora_inicio, r.hora_fin, r.estado 
        FROM reservas r 
        INNER JOIN cargadores c ON r.cargador_id = c.id 
        INNER JOIN puntos_carga p ON c.idPuntoCarga = p.id 
        WHERE r.usuario_id = ? 
        ORDER BY r.fecha DESC, r.hora_inicio DESC";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error en la consulta SQL: " . $conexion->error);
}

$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$reservas = $resultado->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas</title>
    <link rel="stylesheet" href="../css/reservas.css">
</head>
<body>

<div class="contenedor-reserva contenedor-ancho">
    <h1>Mis Reservas</h1>
    
    <?php if(empty($reservas)): ?>
        <p style="text-align: center; color: #6c757d;">No tienes reservas en tu historial.</p>
    <?php else: ?>
        <table class="tabla-historial">
            <thead>
                <tr>
                    <th>Ubicación</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reservas as $res): ?>
                <tr>
                    <td><?= htmlspecialchars($res['ubicacion']) ?></td>
                    <td><?= htmlspecialchars($res['fecha']) ?></td>
                    <td><?= htmlspecialchars($res['hora_inicio']) ?> - <?= htmlspecialchars($res['hora_fin']) ?></td>
                    <td>
                        <span class="estado <?= strtolower($res['estado']) ?>">
                            <?= ucfirst($res['estado']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if($res['estado'] == 'pendiente'): ?>
                            <form action="cancelar_reserva.php" method="POST" style="margin:0; padding:0;">
                                <input type="hidden" name="reserva_id" value="<?= $res['id'] ?>">
                                <button type="submit" class="btn-principal" style="margin-top:0; padding: 6px 12px; font-size: 12px; background-color: #dc3545;">Cancelar</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="mapa.php" class="enlace-secundario">← Volver al Mapa</a>
</div>

</body>
</html>