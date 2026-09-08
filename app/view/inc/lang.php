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

// Idioma por defecto: español si no está definido
$idioma_actual = $_SESSION['lang'] ?? 'es';

// Cargar el archivo correspondiente
require_once __DIR__ . "/../../lang/{$idioma_actual}.php";