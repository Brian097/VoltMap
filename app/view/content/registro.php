<?php
    require_once __DIR__ . "/../../view/inc/auth2.php";
    require_once __DIR__ . "/../../view/inc/lang.php";
    /** @var array $lang */
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['crear_cuenta']; ?> - VoltMap</title>
    <link rel="stylesheet" href="/voltmap/app/view/css/estilosRegistro.css">
</head>
<body>

<div class="sc on" id="s-registro" style="align-items:center;justify-content:center;overflow-y:auto;background:var(--fondo)">
    <div class="reg-card">
      <div class="login-logo">
        <img src="/voltmap/app/view/img/VoltMap-3.png" alt="Logo de la app">
      </div>
      <h2 class="card-titulo"><?php echo $lang['crear_cuenta']; ?></h2>
      <p class="card-sub"><?php echo $lang['sub_registro']; ?></p>

      <form method="POST" action="">
          <?php include_once __DIR__ . '/../../controller/controlador_registro.php'; ?>

          <!-- Campo oculto o manejo del tipo de usuario si lo requiere tu controlador -->
          <input type="hidden" id="tipo_usuario" name="tipo_usuario" value="part">

          <div class="tipo-usuario">
            <button type="button" class="tipo-btn on" onclick="selTipo(this,'part')"><?php echo $lang['particular']; ?></button>
            <button type="button" class="tipo-btn" onclick="selTipo(this,'emp')"><?php echo $lang['empresa']; ?></button>
          </div>

          <div class="campo">
              <label for="nombre"><?php echo $lang['nombre_completo']; ?></label>
              <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan Perez" required>
          </div>

          <div class="campo" id="r-ci">
              <label for="cedula"><?php echo $lang['cedula']; ?></label>
              <input type="text" id="cedula" name="cedula" placeholder="Ej. 4.123.456-7">
          </div>

          <div class="campo" id="r-rut" style="display:none">
              <label for="rut"><?php echo $lang['rut']; ?></label>
              <input type="text" id="rut" name="rut" placeholder="Ej. 21 000000 0001">
          </div>

          <div class="campo">
              <label for="correo"><?php echo $lang['correo']; ?></label>
              <input type="email" id="correo" name="correo" placeholder="Ej. juan@correo.com" required>
          </div>

          <div class="campo">
              <label for="password"><?php echo $lang['pass_lbl']; ?></label>
              <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
          </div>

          <div class="campo">
              <label for="confirm_password"><?php echo $lang['confirm_pass']; ?></label>
              <input type="password" id="confirm_password" name="confirm_password" placeholder="Repetí tu contraseña" required>
          </div>

          <div class="check-row">
            <input type="checkbox" id="tos" name="tos" required>
            <label for="tos" style="color:var(--sub);font-weight:400;margin:0"><?php echo $lang['terminos']; ?></label>
          </div>

          <button type="submit" name="btnregistrar" value="1" class="btn-full btn-azul"><?php echo $lang['btn_registrar']; ?></button>
      </form>

      <p class="link-row" style="margin-top:12px"><?php echo $lang['ya_cuenta']; ?> <a onclick="location.href='login.php'"><?php echo $lang['iniciar_sesion']; ?></a></p>

      <div class="iconos-login" style="margin-top: 15px; display: flex; justify-content: center;">
          <div class="ico-feat">
              <button id="btnIdioma" onclick="cambiarIdioma('<?php echo ($idioma_actual === 'es') ? 'en' : 'es'; ?>')" class="btn-out" style="padding: 6px 12px; cursor: pointer;">
                  <?php echo strtoupper($idioma_actual); ?>
              </button>
          </div>
      </div>
    </div>
</div>

<script src="../js/script.js"></script>
<script>
    function cambiarIdioma(nuevoLang) {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', nuevoLang);
        window.location.href = url.toString();
    }
</script>
</body>
</html>