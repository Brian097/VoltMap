<?php

require_once __DIR__ . "/../../view/inc/auth.php";
require_once __DIR__ . "/../../view/inc/lang.php";
require_once __DIR__ . "/../../model/conexion.php"; 

$usuario_id = (int)$_SESSION['id'];

// la logica para agregar los vehículo eléctrico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_vehiculo'])) {
    $marca_modelo = htmlspecialchars($_POST['marca_modelo']);
    $puerto_carga = htmlspecialchars($_POST['puerto_carga']);
    $capacidad = (float)str_replace(',', '.', $_POST['capacidad_kwh'] ?? 0);
    $potencia_max = (float)str_replace(',', '.', $_POST['potencia_max_kw'] ?? 0);
    
    if (!empty($marca_modelo) && !empty($puerto_carga) && $capacidad > 0 && $potencia_max > 0) {
        
        $sqlInsert = "INSERT INTO vehiculos (usuario_id, marca_modelo, puerto_carga, capacidad_kwh, potencia_max_kw) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $conexion->prepare($sqlInsert);
        
        if ($stmtInsert) {
            $stmtInsert->bind_param("issdd", $usuario_id, $marca_modelo, $puerto_carga, $capacidad, $potencia_max);
            if ($stmtInsert->execute()) {
                $mensaje = "Vehículo agregado con éxito.";
            } else {
                $error = "Error al guardar el vehículo.";
            }
            $stmtInsert->close();
        } else {
            $error = "Error en la consulta SQL: " . $conexion->error;
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}

$vehiculos = [];
$sqlSelect = "SELECT id, marca_modelo, puerto_carga, capacidad_kwh, potencia_max_kw FROM vehiculos WHERE usuario_id = ? ORDER BY id DESC";
$stmtSelect = $conexion->prepare($sqlSelect);

if ($stmtSelect) {
    $stmtSelect->bind_param("i", $usuario_id);
    $stmtSelect->execute();
    $resultado = $stmtSelect->get_result();
    $vehiculos = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmtSelect->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Vehículos Eléctricos</title>
    <link rel="stylesheet" href="../css/vehiculos.css">
    <link rel="icon" type="image/x-icon" href="/app/view/img/VoltMap-ICO.ico">

</head>
<body>
    <div class="container">
        <div class="nav-volver">
            <a href="mapa.php" class="btn-volver">← Volver al Mapa</a>
        </div>
        <h1>Mis Vehículos Eléctricos</h1>
        
        <?php if (isset($mensaje)): ?>
            <div class="alert success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <p>Agrega un nuevo auto eléctrico especificando su puerto de carga.</p>
            <form action="vehiculos.php" method="POST">
                <div class="form-group">
                    <label for="marca_modelo">Marca y Modelo del Auto:</label>
                    <input type="text" id="marca_modelo" name="marca_modelo" placeholder="Ej. Nissan Leaf" required>
                </div>
                
                <div class="form-group">
                    <label for="puerto_carga">Tipo de Puerto de Carga:</label>
                    <select id="puerto_carga" name="puerto_carga" required>
                        <option value="">Selecciona un puerto...</option>
                        <option value="Type 1 (J1772)">Type 1 (J1772)</option>
                        <option value="Type 2 (Mennekes)">Type 2 (Mennekes)</option>
                        <option value="CCS1">CCS1</option>
                        <option value="CCS2">CCS2</option>
                        <option value="CHAdeMO">CHAdeMO</option>
                        <option value="Tesla (NACS)">Tesla (NACS)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="capacidad_kwh"><?php echo $lang['vm_capacidad_kwh'] ?? 'Capacidad de la batería (kWh)'; ?>:</label>
                    <input type="number" id="capacidad_kwh" name="capacidad_kwh" min="1" max="250" step="0.1" placeholder="Ej. 40" required>
                </div>
                <div class="form-group">
                    <label for="potencia_max_kw"><?php echo $lang['vm_potencia_max_kw'] ?? 'Potencia máxima de carga (kW)'; ?>:</label>
                    <input type="number" id="potencia_max_kw" name="potencia_max_kw" min="1" max="400" step="0.1" placeholder="Ej. 50" required>
                </div>
                
                <button type="submit" name="agregar_vehiculo" class="btn-submit">Agregar Vehículo</button>
            </form>
        </div>

        <div class="list-card">
            <h2>Vehículos Registrados</h2>
            <?php if (empty($vehiculos)): ?>
                <p>No tienes vehículos registrados aún.</p>
            <?php else: ?>
                <ul class="vehiculo-list">
                    <?php foreach ($vehiculos as $vehiculo): ?>
                        <li>
                            <div class="vehiculo-info">
                                <strong><?php echo htmlspecialchars($vehiculo['marca_modelo']); ?></strong>
                                <span>Puerto: <?php echo htmlspecialchars($vehiculo['puerto_carga']); ?> · <?php echo (float)$vehiculo['capacidad_kwh']; ?> kWh · <?php echo (float)$vehiculo['potencia_max_kw']; ?> kW</span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>