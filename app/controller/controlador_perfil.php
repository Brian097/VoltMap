<?php
require_once __DIR__ . '/../model/conexion.php';

$idUsuario = $_SESSION["id"] ?? null;
$mensaje = "";
$tipoAlerta = "";

if (!$idUsuario) {
    exit("Acceso no autorizado.");
}

// 1. Obtener los datos actuales del usuario para rellenar el formulario
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$datosUsuario = $resultado->fetch_assoc();
$stmt->close();

// 2. Procesar la actualización cuando se envía el formulario
if (!empty($_POST["btnactualizar"])) {
    $nombre    = trim($_POST["nombre"] ?? '');
    $seudonimo = trim($_POST["seudonimo"] ?? '');
    $correo    = trim($_POST["correo"] ?? '');
    $nuevaPass = trim($_POST["nueva_password"] ?? '');
    $confPass  = trim($_POST["confirmar_password"] ?? '');

    if (!empty($nombre) && !empty($correo)) {
        
        // Si el usuario llenó ambos campos de contraseña a propósito
        if (!empty($nuevaPass) && !empty($confPass)) {
            if ($nuevaPass === $confPass) {
                $passwordHash = password_hash($nuevaPass, PASSWORD_DEFAULT);
                $stmtUpdate = $conexion->prepare("UPDATE usuarios SET nombre = ?, seudonimo = ?, correo = ?, password = ? WHERE id = ?");
                $stmtUpdate->bind_param("ssssi", $nombre, $seudonimo, $correo, $passwordHash, $idUsuario);
            } else {
                $mensaje = $lang['pass_no_coinciden'] ?? "Las contraseñas nuevas no coinciden.";
                $tipoAlerta = "danger";
            }
        } 
        // Si las contraseñas están vacías o el navegador autocompletó solo una, se actualizan los datos de texto de forma limpia
        else {
            $stmtUpdate = $conexion->prepare("UPDATE usuarios SET nombre = ?, seudonimo = ?, correo = ? WHERE id = ?");
            $stmtUpdate->bind_param("sssi", $nombre, $seudonimo, $correo, $idUsuario);
        }

        // Ejecutar la actualización si no hubo errores previos
        if (empty($mensaje) && isset($stmtUpdate)) {
            if ($stmtUpdate->execute()) {
                $mensaje = $lang['perfil_actualizado_exito'] ?? "Perfil actualizado correctamente.";
                $tipoAlerta = "success";
                
                // Actualizar variables de sesión esenciales
                $_SESSION["usuario"] = $nombre;
                $_SESSION["correo"] = $correo;

                // Recargar datos frescos de la base de datos
                $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->bind_param("i", $idUsuario);
                $stmt->execute();
                $datosUsuario = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } else {
                $mensaje = "Error al actualizar los datos: " . $conexion->error;
                $tipoAlerta = "danger";
            }
            $stmtUpdate->close();
        }

    } else {
        $mensaje = $lang['nombre_correo_obligatorios'] ?? "El nombre y el correo son obligatorios.";
        $tipoAlerta = "warning";
    }

    // Mostrar alerta visual
    $colorCss = ($tipoAlerta === 'success') ? 'var(--verde)' : 'var(--rojo)';
    if($tipoAlerta === 'warning') $colorCss = 'var(--ambar)';
    
    echo "<div class='alert alert-{$tipoAlerta}' style='color: #fff; background: {$colorCss}; padding: 10px 15px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; font-weight: 600;'>{$mensaje}</div>";
}
?>