<?php
    require_once __DIR__ . '/../utils/cargar_env.php'; 
    cargarEnv(__DIR__ . '/../.env');

    header("Location: /voltmap/app/view/content/mapa.php");
?>