<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si el usuario hace clic para cambiar idioma vía GET (ej: ?lang=en)
if (isset($_GET['lang'])) {
    if ($_GET['lang'] === 'en' || $_GET['lang'] === 'es') {
        $_SESSION['lang'] = $_GET['lang'];
    }
}

// el idioma por defecto es español si no hay sesión
$idioma_actual = $_SESSION['lang'] ?? 'es';

// Cargar el archivo correspondiente
require_once __DIR__ . "/../../lang/{$idioma_actual}.php";

// Textos nuevos (checkout, reportes, filtros, recibos). Si el archivo no existe no pasa nada.
$archivoExtra = __DIR__ . "/../../lang/{$idioma_actual}_extra.php";
if (file_exists($archivoExtra)) {
    $lang = array_merge($lang, require $archivoExtra);
}
