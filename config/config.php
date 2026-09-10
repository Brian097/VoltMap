<?php
function cargarEnv() {
    // Definimos las rutas posibles dentro de los límites permitidos por el servidor
    $rutasPosibles = [
        __DIR__ . '/.env',          // Si config.php está en la raíz
        dirname(__DIR__) . '/.env', // Si config.php está dentro de una carpeta (ej. /config/)
    ];

    $rutaFinal = null;

    foreach ($rutasPosibles as $ruta) {
        // Usamos realpath solo si el archivo existe para evitar avisos
        if (file_exists($ruta)) {
            $rutaFinal = realpath($ruta);
            break;
        }
    }

    if (!$rutaFinal) {
        echo "<h3>❌ Error Crítico: PHP no puede leer el archivo .env</h3>";
        echo "Buscamos en las siguientes ubicaciones y todas fallaron:<br>";
        foreach ($rutasPosibles as $r) {
            echo "- ❌ No existe o está fuera de path: " . htmlspecialchars($r) . "<br>";
        }
        echo "<br><b>Tu directorio actual de ejecución es:</b> " . __DIR__ . "<br>";
        return false;
    }

    $contenido = file_get_contents($rutaFinal);
    $lineas = preg_split('/\r\n|\r|\n/', $contenido);

    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if (empty($linea) || strpos($linea, '#') === 0) continue;

        if (strpos($linea, '=') !== false) {
            list($clave, $valor) = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor, " \t\n\r\0\x0B\"'");

            // ELIMINAMOS putenv() porque está bloqueado en tu servidor.
            // Usamos únicamente los arrays globales:
            $_ENV[$clave] = $valor;
            $_SERVER[$clave] = $valor;
        }
    }
    return true;
}

cargarEnv();