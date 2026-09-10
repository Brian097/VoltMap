<?php
$passwordPlana = "Brian2307__";

// Genera un hash nuevo en vivo para probar
$hashPrueba = password_hash($passwordPlana, PASSWORD_DEFAULT);

echo "Hash generado: " . $hashPrueba . "<br>";

// Prueba si la verificación da true
if (password_verify($passwordPlana, $hashPrueba)) {
    echo "✅ La función password_verify funciona correctamente.";
} else {
    echo "❌ Hay un problema con PHP.";
}