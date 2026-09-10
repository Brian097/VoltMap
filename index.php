<?php
// /www/wwwroot/voltmap.duckdns.org/index.php

// 1. Cargamos el archivo que contiene la función
//require_once __DIR__ . '/app/utils/cargar_env.php';

// 2. Ejecutamos la función pasando la ruta correcta de tu archivo .env
// (Si tu .env está dentro de la misma carpeta raíz del dominio, usa __DIR__ . '/.env')
//cargarEnv(__DIR__ . '/.env');

// 3. Redirigimos al mapa
header("Location: /app/view/content/mapa.php");
exit;