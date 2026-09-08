<?php
// C:\xampp\htdocs\voltmap\config.php

function cargarEnv() {
    // Definimos las 3 rutas más posibles donde podría estar el .env
    $rutasPosibles = [
        __DIR__ . '/.env',                      // En la misma carpeta que config.php
        dirname(__DIR__) . '/.env',             // Una carpeta arriba
        'C:/xampp/htdocs/voltmap/.env'          // Ruta absoluta directa de Windows
    ];

    $rutaFinal = null;

    foreach ($rutasPosibles as $ruta) {
        if (file_exists($ruta)) {
            $rutaFinal = $ruta;
            break;
        }
    }

    // Si después de buscar en todas no lo encuentra, te muestra la lista para investigar
    if (!$rutaFinal) {
        echo "<h3>❌ Error Crítico: PHP no puede leer el archivo .env</h3>";
        echo "Buscamos en las siguientes ubicaciones y todas fallaron:<br>";
        foreach ($rutasPosibles as $r) {
            echo "- " . (file_exists($r) ? "✅ Existe pero sin permisos" : "❌ No existe en: " . $r) . "<br>";
        }
        echo "<br><b>Tu directorio actual de ejecución es:</b> " . __DIR__ . "<br>";
        return false;
    }

    // Si lo encuentra, lo procesa
    $contenido = file_get_contents($rutaFinal);
    $lineas = preg_split('/\r\n|\r|\n/', $contenido);

    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if (empty($linea) || strpos($linea, '#') === 0) continue;

        if (strpos($linea, '=') !== false) {
            list($clave, $valor) = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor, " \t\n\r\0\x0B\"'");

            putenv("$clave=$valor");
            $_ENV[$clave] = $valor;
            $_SERVER[$clave] = $valor;
        }
    }
    return true;
}

// Ejecutar la búsqueda
cargarEnv();
