<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/conexion.php';
require_once __DIR__ . '/../model/usuario.php';
require_once __DIR__ . '/../model/usuarioParticular.php';
require_once __DIR__ . '/../model/usuarioEmpresa.php';
require_once __DIR__ . '/../model/moderador.php';
require_once __DIR__ . '/../model/administrador.php';
    
if (!empty($_POST["btningresar"])) {
    if (!empty($_POST["usuario"]) && !empty($_POST["password"])) {
        
        $usuario  = trim($_POST["usuario"]); // Puede ser correo, cédula o RUT
        $password = trim($_POST["password"]);

        // Corregido: se usa 'cedula_identidad' en lugar de 'cedula' y seleccionamos todos los campos
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ? OR cedula_identidad = ? OR rut = ?");
        $stmt->bind_param("sss", $usuario, $usuario, $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($datos = $resultado->fetch_assoc()) {
            if (password_verify($password, $datos['password'])) {
                
                // Instanciar la clase correcta según el rol del usuario (Programación Orientada a Objetos)
                $usuarioObj = null;
                switch ($datos['tipo_usuario']) {
                    case 'particular':
                        $usuarioObj = new UsuarioParticular(
                            $datos['id'], $datos['correo'], $datos['nombre'], 
                            $datos['password'], $datos['seudonimo'], $datos['foto_perfil'], 
                            $datos['estado'], $datos['cedula_identidad'], $datos['foto_cedula']
                        );
                        break;
                    case 'empresa':
                        $usuarioObj = new UsuarioEmpresa(
                            $datos['id'], $datos['correo'], $datos['nombre'], 
                            $datos['password'], $datos['seudonimo'], $datos['foto_perfil'], 
                            $datos['estado'], $datos['rut']
                        );
                        break;
                    case 'moderador':
                        $usuarioObj = new Moderador(
                            $datos['id'], $datos['correo'], $datos['nombre'], 
                            $datos['password'], $datos['seudonimo'], $datos['foto_perfil'], 
                            $datos['estado']
                        );
                        break;
                    case 'administrador':
                        $usuarioObj = new Administrador(
                            $datos['id'], $datos['correo'], $datos['nombre'], 
                            $datos['password'], $datos['seudonimo'], $datos['foto_perfil'], 
                            $datos['estado']
                        );
                        break;
                }

                // Guardar los datos esenciales en la sesión
                $_SESSION["id"] = $datos['id'];
                $_SESSION["usuario"] = $datos['nombre'];
                $_SESSION["correo"] = $datos['correo'];
                $_SESSION["tipo_usuario"] = $datos['tipo_usuario'];
                
                // Opcional: Si quieres guardar el objeto completo en la sesión
                // $_SESSION["usuario_obj"] = $usuarioObj;

                echo '<script>window.location.href = "/app/view/content/mapa.php";</script>';
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