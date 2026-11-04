<?php
    require_once __DIR__ . "/../../view/inc/auth.php";
    require_once __DIR__ . "/../../view/inc/lang.php";
    require_once __DIR__ . "/../../controller/helpers.php";
    require_once __DIR__ . "/../../model/conexion.php";
    /** @var array $lang */
    if (vm_tipo_usuario() !== 'administrador') { vm_redirect('/app/view/content/mapa.php'); }
    $comisionActual = vm_comision_pct($conexion);
    $acum = $conexion->query("SELECT COALESCE(SUM(comision),0) AS t, COUNT(*) AS n FROM recibos")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual ?? 'es'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - VoltMap</title>
    <link rel="stylesheet" href="../../view/css/estilosPanelAdmin.css">
    <link rel="stylesheet" href="../../view/css/entrega3.css">
    <link rel="icon" type="image/x-icon" href="/app/view/img/VoltMap-ICO.ico">

</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Panel de Administración</h1>
            <p>Gestión y mantenimiento del sistema VoltMap</p>
        </div>
        <div class="admin-actions">
            <button class="btn-admin btn-admin-secundario" onclick="location.href='/app/view/content/mapa.php'">
                ← Volver al Mapa
            </button>
            <button class="btn-admin" onclick="location.href='/app/view/content/sincronizar_cargadores.php'">
                Actualizar Cargadores Públicos (BD)
            </button>
            <button class="btn-admin" onclick="location.href='#'">
                Promover Usuario
            </button>
        </div>

        <div class="vm-card" style="margin:24px auto 0;max-width:460px">
            <h2 style="margin-top:0"><?php echo vm_h($lang['vm_comision_titulo'] ?? 'Comisión de la plataforma'); ?></h2>
            <?php if (isset($_GET['ok'])): ?><div class="vm-aviso ok"><?php echo vm_h($lang['vm_guardado'] ?? 'Guardado.'); ?></div><?php endif; ?>
            <?php if (isset($_GET['error'])): ?><div class="vm-aviso err">0 – 50</div><?php endif; ?>
            <form method="POST" action="/app/controller/controlador_comision.php">
                <input type="hidden" name="csrf" value="<?php echo vm_h(vm_csrf()); ?>">
                <label class="vm-campo"><?php echo vm_h($lang['vm_comision_actual'] ?? 'Porcentaje retenido en cada pago (0 a 50)'); ?>
                    <input type="number" name="comision" min="0" max="50" step="0.5" value="<?php echo vm_h($comisionActual); ?>">
                </label>
                <button class="vm-btn grande" type="submit"><?php echo vm_h($lang['vm_guardar'] ?? 'Guardar'); ?></button>
            </form>
            <p class="vm-nota"><?php echo vm_h($lang['vm_comision_acumulada'] ?? 'Comisión acumulada'); ?>:
               <b>$<?php echo number_format((float)$acum['t'], 2); ?></b> (<?php echo (int)$acum['n']; ?>)</p>
        </div>
    </div>
</body>
</html>