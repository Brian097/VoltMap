<?php
    require_once __DIR__ . "/../../view/inc/auth.php";
    require_once __DIR__ . "/../../view/inc/lang.php";
    /** @var array $lang */
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual ?? 'es'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - VoltMap</title>
    <link rel="stylesheet" href="../../view/css/estilosPanelAdmin.css">
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
    </div>
</body>
</html>