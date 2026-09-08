<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/conexion.php';
    
if (!empty($_POST["btningresar"])) {
    if (!empty($_POST["usuario"]) && !empty($_POST["password"])) {
        
        $usuario  = trim($_POST["usuario"]); // Puede ser correo, cédula o RUT
        $password = trim($_POST["password"]);

        // Buscamos si coincide con correo, cédula o rut
        $stmt = $conexion->prepare("SELECT id, nombre, correo, password FROM usuarios WHERE correo = ? OR cedula = ? OR rut = ?");
        $stmt->bind_param("sss", $usuario, $usuario, $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($datos = $resultado->fetch_assoc()) {
            if (password_verify($password, $datos['password'])) {
                
                $_SESSION["id"] = $datos['id'];
                $_SESSION["usuario"] = $datos['nombre'];
                $_SESSION["correo"] = $datos['correo'];

                echo '<script>window.location.href = "/voltmap/app/view/content/mapa.php";</script>';
                exit();
            } else {
                echo "<div class='alert alert-danger' style='color: red; margin-bottom: 10px;'>Contraseña incorrecta.</div>";
            }
        } else {
            echo "<div class='alert alert-danger' style='color: red; margin-bottom: 10px;'>El usuario, correo, cédula o RUT no están registrados.</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='alert alert-warning' style='color: orange; margin-bottom: 10px;'>Por favor completa todos los campos.</div>";
    }
}
?>