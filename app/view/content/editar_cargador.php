<?php
    require_once __DIR__ . "/../../view/inc/auth.php";
    require_once __DIR__ . "/../../view/inc/lang.php";
    require_once __DIR__ . "/../../model/conexion.php";

    $idUsuarioActual = $_SESSION['id'] ?? 0;
    $idPunto = intval($_GET['id'] ?? 0);

    // Validar que el punto pertenezca al usuario actual antes de renderizar la vista
    $stmt = $conexion->prepare("SELECT p.*, c.potenciaKilowatts, c.tipoConector, c.tipoCargador, c.precioKwh, c.precioHora, c.estadoOperativo 
                                FROM puntos_carga p 
                                INNER JOIN cargadores c ON p.id = c.idPuntoCarga 
                                WHERE p.id = ? AND p.id_usuario = ?");
    $stmt->bind_param("ii", $idPunto, $idUsuarioActual);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        header("Location: mis_cargadores.php");
        exit();
    }
    $data = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual ?? 'es'; ?>">
<head>
    <meta charset="UTF-8">
    <title>Editar Cargador - VoltMap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Estilo base unificado -->
    <link rel="stylesheet" href="../../view/css/estilosPublicar.css">
    <!-- Estilo específico para edición -->
    <link rel="stylesheet" href="../../view/css/estilosEditarCargador.css">
</head>
<body>
    <div class="sc on">
        <div class="perfil-container editar-container"> 
            <div class="perfil-nav-volver">
                <a href="mis_cargadores.php" class="btn-volver">← Volver a Mis Cargadores</a>
            </div>

            <div class="perfil-header-info">
                <h1>Editar Punto de Carga</h1>
                <p>Modifica la ubicación geográfica, los parámetros técnicos y los costos de tu cargador.</p>
            </div>

            <div class="perfil-card">
                <!-- El formulario apunta al Controlador MVC independiente -->
                <form action="../../controller/controlador_editar.php" method="POST" class="formulario-editar">
                    <input type="hidden" name="id_punto" value="<?php echo $data['id']; ?>">

                    <!-- Sección de Ubicación interactiva -->
                    <div class="form-grupo">
                        <label>Ubicación en el Mapa</label>
                        <span class="mapa-instruccion">Haz clic en el mapa o arrastra el marcador para actualizar la posición exacta.</span>
                        <div id="map-editar"></div>
                    </div>

                    <div class="form-grid">
                        <div class="form-grupo">
                            <label>Latitud</label>
                            <input type="text" id="latitud" name="latitud" value="<?php echo htmlspecialchars($data['latitud']); ?>" readonly required>
                        </div>
                        <div class="form-grupo">
                            <label>Longitud</label>
                            <input type="text" id="longitud" name="longitud" value="<?php echo htmlspecialchars($data['longitud']); ?>" readonly required>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label>Dirección</label>
                        <input type="text" name="direccion" value="<?php echo htmlspecialchars($data['direccion']); ?>" required>
                    </div>

                    <div class="form-grupo">
                        <label>Ciudad y Departamento</label>
                        <input type="text" name="ciudadYDepartamento" value="<?php echo htmlspecialchars($data['ciudadYDepartamento']); ?>" required>
                    </div>

                    <!-- Sección Técnica -->
                    <div class="form-grid">
                        <div class="form-grupo">
                            <label>Potencia (kW)</label>
                            <input type="number" step="0.1" name="potenciaKilowatts" value="<?php echo $data['potenciaKilowatts']; ?>" required>
                        </div>
                        <div class="form-grupo">
                            <label>Tipo de Conector</label>
                            <input type="text" name="tipoConector" value="<?php echo htmlspecialchars($data['tipoConector']); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-grupo">
                            <label>Precio por kWh ($)</label>
                            <input type="number" step="0.01" name="precioKwh" value="<?php echo $data['precioKwh']; ?>">
                        </div>
                        <div class="form-grupo">
                            <label>Precio por Hora ($)</label>
                            <input type="number" step="0.01" name="precioHora" value="<?php echo $data['precioHora']; ?>">
                        </div>
                    </div>

                    <button type="submit" name="btneditar" class="btn-azul" style="margin-top: 10px;">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- Script JavaScript externo modular -->
    <script src="../../view/js/editar_cargador.js"></script>
</body>
</html>