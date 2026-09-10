<?php
require_once __DIR__ . '/../model/conexion.php';
require_once __DIR__ . '/../model/usuario.php';
require_once __DIR__ . '/../model/usuarioParticular.php';
require_once __DIR__ . '/../model/usuarioEmpresa.php';

if (!empty($_POST["btnregistrar"])) {
    if (!empty($_POST["nombre"]) && !empty($_POST["correo"]) && !empty($_POST["password"]) && !empty($_POST["confirm_password"])) {
        
        $tipo_usuario     = trim($_POST["tipo_usuario"] ?? 'part');
        $nombre           = trim($_POST["nombre"]);
        $cedula           = trim($_POST["cedula"] ?? '');
        $rut              = trim($_POST["rut"] ?? '');
        $correo           = trim($_POST["correo"]);
        $password         = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm_password"]);

        if ($password !== $confirm_password) {
            echo "<div class='alert alert-danger' style='color: red;'>Las contraseñas no coinciden.</div>";
            return;
        }

        if (strlen($password) < 8) {
            echo "<div class='alert alert-danger' style='color: red;'>La contraseña debe tener al menos 8 caracteres.</div>";
            return;
        }

        if ($tipo_usuario === 'part' && empty($cedula)) {
            echo "<div class='alert alert-danger' style='color: red;'>La cédula es obligatoria para particulares.</div>";
            return;
        }

        if ($tipo_usuario === 'emp' && empty($rut)) {
            echo "<div class='alert alert-danger' style='color: red;'>El RUT es obligatorio para empresas.</div>";
            return;
        }

        // Verificar si el correo ya existe
        $stmtCheck = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmtCheck->bind_param("s", $correo);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            echo "<div class='alert alert-danger' style='color: red;'>El correo electrónico ya está registrado.</div>";
        } else {
            $stmtCheck->close();

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $estado_inicial = 'activo';
            $seudonimo_def = null;
            $foto_def = null;

            // Instanciar la clase correcta según la selección del formulario
            if ($tipo_usuario === 'part') {
                $usuarioObj = new UsuarioParticular(null, $correo, $nombre, $password_hash, $seudonimo_def, $foto_def, $estado_inicial, $cedula, null);
            } else {
                $usuarioObj = new UsuarioEmpresa(null, $correo, $nombre, $password_hash, $seudonimo_def, $foto_def, $estado_inicial, $rut);
            }

            // Ejecutar el método polimórfico de registro
            $stmt = $usuarioObj->Registrar($conexion);
            
            if ($stmt && $stmt->execute()) {
                echo "<div class='alert alert-success' style='color: green;'>¡Registro exitoso! Redirigiendo...</div>";
                echo "<script>
                        setTimeout(function() {
                            window.location.href = '/app/view/content/login.php';
                        }, 2000);
                      </script>";
            } else {
                echo "<div class='alert alert-danger' style='color: red;'>Error al registrarse. Inténtalo de nuevo.</div>";
            }

            if ($stmt) {
                $stmt->close();
            }
        }
    } else {
        echo "<div class='alert alert-warning' style='color: orange;'>Por favor completa todos los campos obligatorios.</div>";
    }
}
?>