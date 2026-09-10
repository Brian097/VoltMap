<?php
require_once __DIR__ . '/../model/conexion.php';

$idUsuario = $_SESSION["id"] ?? null;
$mensaje = "";
$tipoAlerta = "";

if (!$idUsuario) {
    exit("Acceso no autorizado.");
}

// 2. Procesar la actualización cuando se envía el formulario
if (!empty($_POST["btnactualizar"])) {
    $nombre    = trim($_POST["nombre"] ?? '');
    $seudonimo = trim($_POST["seudonimo"] ?? '');
    $correo    = trim($_POST["correo"] ?? '');
    $nuevaPass = trim($_POST["nueva_password"] ?? '');
    $confPass  = trim($_POST["confirmar_password"] ?? '');

    if (!empty($nombre) && !empty($correo)) {
        
        // Verificamos si escribió algo en las contraseñas
        if (!empty($nuevaPass) || !empty($confPass)) {
            // Validar longitud mínima de 8 caracteres
            if (strlen($nuevaPass) < 8) {
                $mensaje = $lang['pass_corta'] ?? 'La contraseña debe tener al menos 8 caracteres.';
                $tipoAlerta = "danger";
            } 
            // Validar que coincidan
            elseif ($nuevaPass !== $confPass) {
                $mensaje = $lang['pass_no_coinciden'] ?? 'Las contraseñas nuevas no coinciden.';
                $tipoAlerta = "danger";
            } else {
                $passwordHash = password_hash($nuevaPass, PASSWORD_DEFAULT);
                $stmtUpdate = $conexion->prepare("UPDATE usuarios SET nombre = ?, seudonimo = ?, correo = ?, password = ? WHERE id = ?");
                $stmtUpdate->bind_param("ssssi", $nombre, $seudonimo, $correo, $passwordHash, $idUsuario);
            }
        } else {
            // Sin cambio de contraseña
            $stmtUpdate = $conexion->prepare("UPDATE usuarios SET nombre = ?, seudonimo = ?, correo = ? WHERE id = ?");
            $stmtUpdate->bind_param("sssi", $nombre, $seudonimo, $correo, $idUsuario);
        }

        // Si no hay errores y se preparó el update, ejecutamos
        if (empty($mensaje) && isset($stmtUpdate)) {
            if ($stmtUpdate->execute()) {
                $mensaje = $lang['perfil_actualizado_exito'] ?? 'Perfil actualizado correctamente.';
                $tipoAlerta = "success";
                
                $_SESSION["usuario"] = $nombre;
                $_SESSION["correo"] = $correo;
            } else {
                $mensaje = "Error al actualizar los datos: " . $conexion->error;
                $tipoAlerta="danger";
            }
            $stmtUpdate->close();
        }

    } else {
        $mensaje = $lang['nombre_correo_obligatorios'] ?? 'El nombre y el correo son obligatorios.';
        $tipoAlerta = "warning";
    }
}

// 1. Obtener siempre los datos actuales para pintar los inputs
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$datosUsuario = $resultado->fetch_assoc();
$stmt->close();
?>