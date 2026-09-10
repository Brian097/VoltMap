<?php
require_once __DIR__ . '/../../config/config.php';

$conexion = new mysqli($_ENV['DB_SERVER'], $_ENV['DB_USER'], $_ENV['DB_PASSW'], $_ENV['DB_NAME']);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>

