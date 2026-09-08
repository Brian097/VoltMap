<?php
    require_once __DIR__ . "/../../view/inc/auth2.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VoltMap</title>
    <link rel="stylesheet" href="/voltmap/app/view/css/estilosLogin.css">
</head>
<body>
    <div class="sc on" id="s-login" style="align-items:center;justify-content:center;background:var(--fondo)">
        <div class="login-card">
            <div class="login-logo">
                <img src="/voltmap/app/view/img/VoltMap-3.png" alt="Logo de la app">
            </div>
            <h2 class="card-titulo">Bienvenido de nuevo</h2>
            <p class="card-sub">Inicia sesión para continuar</p>

            <form method="POST" action="">
                <?php include_once __DIR__ . '/../../controller/controlador_login.php'; ?>

                <div class="campo">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" class="campo-input" required>
                </div>

                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="campo-input" required>
                </div>

                <button type="submit" name="btningresar" value="1" class="btn-full btn-azul">
                    Iniciar sesión
                </button>

            </form>
            
            <p class="link-row">¿No tienes cuenta? <a onclick="location.href='/voltmap/app/view/content/registro.php'">Regístrate aquí</a></p>

            <div class="iconos-login">
                <div class="ico-feat">
                    <button id="btnIdioma" onclick="alternarIdioma()" class="btn-out">ES</button>
                </div>
            </div>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>