<?php
    require_once __DIR__ . "/../../view/inc/auth.php";
    require_once __DIR__ . "/../../view/inc/lang.php";
    /** @var array $lang */
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['publicar_titulo'] ?? 'Publicar Cargador'; ?> - VoltMap</title>
    <!-- CSS Independiente -->
    <link rel="stylesheet" href="../../view/css/estilosPublicar.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
</head>
<body>
    <div class="sc on" id="s-perfil">
        <div class="perfil-container" style="max-width: 800px;">
            
            <div style="margin-bottom: 15px;">
                <a href="mapa.php" class="btn-volver">
                    ← <?php echo $lang['volver_mapa'] ?? 'Volver al Mapa'; ?>
                </a>
            </div>

            <div class="perfil-header-info">
                <h1><?php echo $lang['publicar_titulo'] ?? 'Publicar Cargador'; ?></h1>
                <p><?php echo $lang['sub_publicar'] ?? 'Registra un nuevo punto de carga y sus conectores en el mapa'; ?></p>
            </div>

            <?php include_once __DIR__ . '/../../controller/controlador_publicar.php'; ?>

            <?php if (!empty($mensaje)): ?>
                <div class="perfil-alert <?php echo $tipoAlerta; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="perfil-card">
                <form method="POST" action="">
                    <div class="perfil-section-title">
                        <?php echo $lang['info_punto'] ?? 'Información del Punto de Carga'; ?>
                    </div>

                    <div class="campo" style="margin-bottom: 16px;">
                        <label><?php echo $lang['selecciona_mapa_lbl'] ?? 'Selecciona la ubicación en el mapa (Haz clic para marcar)'; ?></label>
                        <div id="mapa-publicar"></div>
                    </div>

                    <div class="perfil-form-row">
                        <div class="campo">
                            <label><?php echo $lang['latitud_lbl'] ?? 'Latitud'; ?></label>
                            <input type="text" id="latitud" name="latitud" placeholder="Ej. -34.4811" required readonly>
                        </div>
                        <div class="campo">
                            <label><?php echo $lang['longitud_lbl'] ?? 'Longitud'; ?></label>
                            <input type="text" id="longitud" name="longitud" placeholder="Ej. -54.3333" required readonly>
                        </div>
                    </div>

                    <div class="perfil-form-row">
                        <div class="campo">
                            <label><?php echo $lang['direccion_lbl'] ?? 'Dirección'; ?></label>
                            <input type="text" name="direccion" placeholder="Ej. Av. 18 de Julio 1234" required>
                        </div>
                        <div class="campo">
                            <label><?php echo $lang['ciudad_depto_lbl'] ?? 'Ciudad y Departamento'; ?></label>
                            <input type="text" name="ciudadYDepartamento" placeholder="Ej. Rocha, Rocha" required>
                        </div>
                    </div>

                    <div class="perfil-divider"></div>

                    <div class="perfil-section-title">
                        <?php echo $lang['info_cargador'] ?? 'Información del Cargador / Conector'; ?>
                    </div>

                    <div class="perfil-form-row">
                        <div class="campo">
                            <label><?php echo $lang['potencia_lbl'] ?? 'Potencia (kW)'; ?></label>
                            <input type="number" step="0.1" name="potenciaKilowatts" placeholder="Ej. 22" required>
                        </div>
                        <div class="campo">
                            <label><?php echo $lang['tipo_conector_lbl'] ?? 'Tipo de Conector'; ?></label>
                            <input type="text" name="tipoConector" placeholder="Ej. Type 2, CCS, CHAdeMO" required>
                        </div>
                    </div>

                    <div class="perfil-form-row">
                        <div class="campo">
                            <label><?php echo $lang['tipo_cargador_lbl'] ?? 'Tipo de Corriente / Cargador'; ?></label>
                            <input type="text" name="tipoCargador" placeholder="Ej. AC Level 2 / DC Fast" required>
                        </div>
                        <div class="campo">
                            <label><?php echo $lang['precio_kwh_lbl'] ?? 'Precio por kWh ($)'; ?></label>
                            <input type="number" step="0.01" name="precioKwh" value="0.00" required>
                        </div>
                    </div>

                    <div class="perfil-form-row">
                        <div class="campo">
                            <label><?php echo $lang['precio_hora_lbl'] ?? 'Precio por Hora ($)'; ?></label>
                            <input type="number" step="0.01" name="precioHora" value="0.00" required>
                        </div>
                    </div>

                    <div style="margin-top: 24px;">
                        <button type="submit" name="btnpublicar" value="1" class="btn-full btn-azul">
                            <?php echo $lang['btn_publicar'] ?? 'Publicar Cargador'; ?>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        var map = L.map('mapa-publicar').setView([-34.4833, -54.3333], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var marker = null;

        map.on('click', function(e) {
            var lat = e.latlng.lat.toFixed(6);
            var lng = e.latlng.lng.toFixed(6);

            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lng;

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });
    </script>
</body>
</html>