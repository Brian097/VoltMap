<?php
    // 1. Cargamos la configuración global
    require_once __DIR__ . '/../../../config/config.php';
    
    // 2. Verificamos la autenticación
    require_once __DIR__ . "/../../view/inc/auth.php";

    // 3. Incluimos el sistema de idiomas
    require_once __DIR__ . "/../../view/inc/lang.php";
    /** @var array $lang */
?>

<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $lang['titulo_mapa'] ?? 'VoltMap — Mapa de Cargadores'; ?></title>

  <!-- Hojas de estilo de la app y Leaflet -->
  <link rel="stylesheet" href="../../view/css/estilosMapa.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>

<body>
<!-- Mapa principal -->
  <div class="sc on" id="s-mapa">
    <!-- Barra superior de búsqueda -->
    <div class="mapa-topbar">
      <div class="mapa-logo">
        <img src="../../view/img/VoltMap-3.png" alt="Logo de la app">
      </div>
      <input class="search-bar" placeholder="<?php echo $lang['buscar_placeholder'] ?? 'Buscar dirección o lugar...'; ?>">
      <button class="icon-btn" title="<?php echo $lang['cerrar_sesion'] ?? 'Cerrar Sesion'; ?>" onclick="location.href='/app/controller/controlador_cerrar_sesion.php'">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--sub)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
        <polyline points="10 17 15 12 10 7"></polyline>
        <line x1="15" y1="12" x2="3" y2="12"></line>
      </svg>
      </button>
    </div>

    <!-- Barra de filtros rápidos -->
    <div class="filtros-bar">
      <button class="chip sel" onclick="selChip(this)"><?php echo $lang['chip_todos'] ?? 'Todos'; ?></button>
      <button class="chip" onclick="selChip(this)"><span class="dot dot-v" style="margin-right:5px"></span><?php echo $lang['chip_disponible'] ?? 'Disponible'; ?></button>
      <button class="chip" onclick="selChip(this)"><span class="dot dot-a" style="margin-right:5px"></span><?php echo $lang['chip_en_uso'] ?? 'En uso'; ?></button>
      <button class="chip" onclick="selChip(this)"><span class="dot dot-r" style="margin-right:5px"></span><?php echo $lang['chip_fuera_servicio'] ?? 'Fuera de servicio'; ?></button>
      <button class="chip" onclick="selChip(this)"><?php echo $lang['chip_mas_filtros'] ?? 'Más filtros'; ?></button>
    </div>

    <!-- Contenedor del mapa -->
    <div class="mapa-area">
      <!-- Aquí se renderiza el mapa leyendo de tu base de datos -->
      <div id="map"></div>

      <div class="leyenda-mapa">
        <h5><?php echo $lang['referencias_titulo'] ?? 'Referencias'; ?></h5>
        <div class="leg-row"><span class="dot dot-v"></span> <?php echo $lang['chip_disponible'] ?? 'Disponible'; ?></div>
        <div class="leg-row"><span class="dot dot-a"></span> <?php echo $lang['chip_en_uso'] ?? 'En uso'; ?></div>
        <div class="leg-row"><span class="dot dot-r"></span> <?php echo $lang['chip_fuera_servicio'] ?? 'Fuera de servicio'; ?></div>
      </div>
    </div>

    <!-- Menú inferior (Bottom Nav) -->
    <div class="bottom-nav">
      <a href="mis_cargadores.php" class="bn on" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="8" y1="6" x2="21" y2="6"></line>
        <line x1="8" y1="12" x2="21" y2="12"></line>
        <line x1="8" y1="18" x2="21" y2="18"></line>
        <line x1="3" y1="6" x2="3.01" y2="6"></line>
        <line x1="3" y1="12" x2="3.01" y2="12"></line>
        <line x1="3" y1="18" x2="3.01" y2="18"></line>
      </svg>
      <?php echo $lang['mis_cargadores'] ?? 'Mis Cargadores'; ?>
    </a>
      <button class="bn" onclick="location.href='reservas.php'">
        <svg viewBox="0 0 24 24">
          <rect x="3" y="4" width="18" height="18" rx="2" />
          <line x1="16" y1="2" x2="16" y2="6" />
          <line x1="8" y1="2" x2="8" y2="6" />
        </svg>
        <?php echo $lang['nav_reservas'] ?? 'Reservas'; ?>
      </button>
      <button class="bn-plus-wrap" onclick="location.href='publicar.php'">
        <div class="bn-plus">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
        </div>
        <?php echo $lang['nav_publicar'] ?? 'Publicar'; ?>
      </button>
      <button class="bn" onclick="location.href='/app/view/content/perfil.php'">
        <svg viewBox="0 0 24 24">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
          <circle cx="12" cy="7" r="4" />
        </svg>
        <?php echo $lang['nav_perfil'] ?? 'Perfil'; ?>
      </button>
      <button class="bn" onclick="location.href='vehiculos.php'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="7" width="20" height="10" rx="2" ry="2" />
          <path d="M6 7V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2" />
          <path d="M16 17v2a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-2" />
        </svg>
        <?php echo $lang['nav_vehiculos'] ?? 'Vehículos'; ?>
      </button>
    </div>
  </div>

  <!-- Librería de Leaflet -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <!-- Lógica para obtener los datos de TU base de datos (mediante api_puntos.php) -->
  <script src="../../view/js/mapaCargadores.js"></script>

  <!-- Interacción general de la interfaz (chips, menú, etc.) -->
  <script src="/app/view/js/script.js"></script>
</body>
</html>