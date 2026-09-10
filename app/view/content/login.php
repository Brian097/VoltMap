<?php
    require_once __DIR__ . "/../../view/inc/auth2.php";
    require_once __DIR__ . "/../../view/inc/lang.php"; // <--- Incluir el gestor de idiomas
    /** @var array $lang */
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VoltMap</title>
    <link rel="stylesheet" href="../../view/css/estilosLogin.css"></head>
<body>
    <div class="sc on" id="s-login" style="align-items:center;justify-content:center;background:var(--fondo)">
        <div class="login-card">
            <div class="login-logo">
                <img src="../../view/img/VoltMap-3.png" alt="Logo de la app">
            </div>
            <h2 class="card-titulo"><?php echo $lang['bienvenida']; ?></h2>
            <p class="card-sub"><?php echo $lang['sub_login']; ?></p>

            <form method="POST" action="">
                <?php include_once __DIR__ . '/../../controller/controlador_login.php'; ?>

                <div class="campo">
                    <label for="usuario"><?php echo $lang['usuario_lbl']; ?></label>
                    <input type="text" id="usuario" name="usuario" class="campo-input" required>
                </div>

                <div class="campo">
                    <label for="password"><?php echo $lang['pass_lbl']; ?></label>
                    <input type="password" id="password" name="password" class="campo-input" required>
                </div>

                <button type="submit" name="btningresar" value="1" class="btn-full btn-azul">
                    <?php echo $lang['btn_ingresar']; ?>
                </button>
            </form>
            
            <p class="link-row"><?php echo $lang['no_cuenta']; ?> <a onclick="location.href='registro.php'"><?php echo $lang['registrate']; ?></a></p>

            <div class="iconos-login">
                <div class="ico-feat">
                    <!-- Botón para alternar idioma recargando la página con el parámetro GET -->
                    <button id="btnIdioma" onclick="cambiarIdioma('<?php echo ($idioma_actual === 'es') ? 'en' : 'es'; ?>')" class="btn-out">
                        <?php echo strtoupper($idioma_actual); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function cambiarIdioma(nuevoLang) {
            // Recarga la página actual agregando el parámetro ?lang=en o ?lang=es
            const url = new URL(window.location.href);
            url.searchParams.set('lang', nuevoLang);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>