<?php
// Si la vista se carga de forma directa por URL sin pasar por el controlador, protegemos las variables básicas
if (!isset($resultado)) {
    require_once __DIR__ . "/../inc/auth.php";
    require_once __DIR__ . "/../inc/lang.php";
    require_once __DIR__ . "/../../model/conexion.php";
    
    $idUsuarioActual = $_SESSION['id'] ?? 0;
    $mensaje = "";
    $tipoAlerta = "";

    $sql = "SELECT p.id as idPunto, p.direccion, p.ciudadYDepartamento, c.potenciaKilowatts, c.tipoConector, p.id_usuario 
            FROM puntos_carga p 
            INNER JOIN cargadores c ON p.id = c.idPuntoCarga 
            WHERE p.id_usuario = ?";
    $stmtLista = $conexion->prepare($sql);
    $stmtLista->bind_param("i", $idUsuarioActual);
    $stmtLista->execute();
    $resultado = $stmtLista->get_result();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual ?? 'es'; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $lang['mis_cargadores'] ?? 'Mis Cargadores'; ?> - VoltMap</title>
    <!-- Etiqueta indispensable para diseño responsive en teléfonos -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Estilo base unificado del sistema -->
    <link rel="stylesheet" href="/app/view/css/estilosPublicar.css">
    <!-- Estilo específico para mis cargadores -->
    <link rel="stylesheet" href="/app/view/css/estilosMisCargadores.css">
<body>
    <div class="sc on">
        <div class="perfil-container mis-cargadores-container">
            <div class="perfil-nav-volver">
                <a href="/app/view/content/mapa.php" class="btn-volver">← Volver al Mapa</a>
            </div>

            <div class="perfil-header-info">
                <h1><?php echo $lang['mis_cargadores'] ?? 'Mis Cargadores Publicados'; ?></h1>
                <p>Gestiona, edita o elimina los puntos de carga que has registrado.</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="perfil-alert <?php echo $tipoAlerta; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="perfil-card">
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <div class="cargadores-lista">
                        <?php while($row = $resultado->fetch_assoc()): ?>
                            <div class="cargador-item-card">
                                <div class="cargador-info">
                                    <strong><?php echo htmlspecialchars($row['direccion']); ?> (<?php echo htmlspecialchars($row['ciudadYDepartamento']); ?>)</strong>
                                    <span>Conector: <?php echo htmlspecialchars($row['tipoConector']); ?> | Potencia: <?php echo $row['potenciaKilowatts']; ?> kW</span>
                                </div>
                                <div class="cargador-acciones">
                                    <a href="editar_cargador.php?id=<?php echo $row['idPunto']; ?>" class="btn-accion-editar">
                                        <?php echo $lang['editar'] ?? 'Editar'; ?>
                                    </a>
                                    <!-- Si quieres que el enlace de eliminar pase por el controlador, puedes apuntarlo a ../../controller/controlador_mis_cargadores.php?eliminar=... o mantenerlo si la vista redirige -->
                                    <a href="../../controller/controlador_mis_cargadores.php?eliminar=<?php echo $row['idPunto']; ?>" class="btn-accion-eliminar" onclick="return confirm('<?php echo $lang['confirmar_eliminar'] ?? '¿Estás seguro de eliminar este cargador?'; ?>');">
                                        <?php echo $lang['eliminar'] ?? 'Eliminar'; ?>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="perfil-texto-vacio">No tienes cargadores publicados todavía.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>