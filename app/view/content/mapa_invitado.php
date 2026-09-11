<?php
    require_once __DIR__ . "/../../view/inc/auth2.php";
    require_once __DIR__ . "/../../view/inc/lang.php";
    /** @var array $lang */
    /** @var string $idioma_actual */
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoltMap - Modo Invitado</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="./../css/estilosMapa.css" />
    <link rel="stylesheet" href="./../css/estilosInvitados.css" />
</head>
<body>

    <!-- Barra superior con el logo pequeño y más espacio libre -->
    <div class="mapa-topbar">
        <div class="mapa-logo">
            <!-- REEMPLAZA "VoltMap-icono.png" por el nombre de tu archivo de logo pequeño -->
            <img src="../../view/img/VoltMap-ICO.png" alt="Logo VoltMap">
        </div>
        <div class="mapa-divider"></div>
        <div class="search-wrapper">
            <span class="search-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
            <input class="search-bar" type="text" placeholder="<?php echo $lang['buscar_placeholder']; ?>">
        </div>
        <div class="mapa-divider"></div>
        <button id="btnIdioma" onclick="cambiarIdioma('<?php echo ($idioma_actual === 'es') ? 'en' : 'es'; ?>')" class="lang-btn-toggle">
            <?php echo strtoupper($idioma_actual); ?>
        </button>
    </div>

    <!-- Banner flotante de invitado -->
    <div class="guest-banner">
        <span><?php echo $lang['explorando_invitado']; ?></span>
        <a href="login.php" class="btn-login"><?php echo $lang['iniciar_sesion']; ?></a>
    </div>

    <!-- Contenedor del Mapa -->
    <div id="map"></div>

    <!-- Scripts necesarios -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../../view/js/mapaCargadores.js"></script>
    <script>
        function cambiarIdioma(nuevoLang) {
            const url = new URL(window.location.href);
            url.searchParams.set('lang', nuevoLang);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>