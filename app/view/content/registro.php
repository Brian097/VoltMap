<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - VoltMap</title>
    <link rel="stylesheet" href="/voltmap/app/view/css/estilosRegistro.css">
</head>
<body>

<?php
    require_once __DIR__ . "/../../view/inc/auth2.php";
?>

<div class="sc on" id="s-registro" style="align-items:center;justify-content:center;overflow-y:auto;background:var(--fondo)">
    <div class="reg-card">
      <div class="login-logo">
        <img src="/voltmap/app/view/img/VoltMap-3.png" alt="Logo de la app">
      </div>
      <h2 class="card-titulo">Crear cuenta</h2>
      <p class="card-sub">Completa tus datos para registrarte</p>
      <div class="tipo-usuario">
        <button class="tipo-btn on" onclick="selTipo(this,'part')">Particular</button>
        <button class="tipo-btn" onclick="selTipo(this,'emp')">Empresa</button>
      </div>
      <div class="campo"><label>Nombre completo</label><input type="text" placeholder="Ej. Juan Perez"></div>
      <div class="campo" id="r-ci"><label>Cédula de identidad</label><input type="text" placeholder="Ej. 4.123.456-7">
      </div>
      <div class="campo" id="r-rut" style="display:none"><label>Número de RUT</label><input type="text" placeholder="Ej. 21 000000 0001"></div>
      <div class="campo"><label>Correo electrónico</label><input type="email" placeholder="Ej. juan@correo.com"></div>
      <div class="campo"><label>Contraseña</label><input type="password" placeholder="Mínimo 8 caracteres"></div>
      <div class="campo"><label>Confirmar contraseña</label><input type="password" placeholder="Repetí tu contraseña">
      </div>
      <div class="check-row">
        <input type="checkbox" id="tos">
        <label for="tos" style="color:var(--sub);font-weight:400;margin:0">Acepto los <a style="color:var(--azul2)">términos y condiciones</a></label>
      </div>
      <button class="btn-full btn-azul" onclick="ir('s-mapa')">Registrarme</button>
      <p class="link-row" style="margin-top:12px">Ya tienes cuenta? <a onclick="location.href='login.php'">Iniciar sesión</a></p>
    </div>
  </div>
  <script src="js/script.js"></script>
</body>
</html>