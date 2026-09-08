<?php

require_once  __DIR__ . '/../model/conexion.php';
    
if (!empty($_POST["btningresar"])) {
    if (!empty($_POST["usuario"]) && !empty($_POST["password"])) {
        
        $usuario  = trim($_POST["usuario"]);
        $password = trim($_POST["password"]);

        $stmt = $conexion->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($datos = $resultado->fetch_assoc()) {
            
            if ($password === $datos['password_hash']) {
                
                $_SESSION["id"] = $datos['id'];
                $_SESSION["usuario"] = $datos['username'];

                header("Location: /voltmap/app/view/content/mapa.php");
                exit();
            } else {
                echo "<div class='alert alert-danger'>Contraseña incorrecta.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>El usuario no existe.</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='alert alert-warning'>Por favor completa todos los campos.</div>";
    }
}
?>