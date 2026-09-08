<?php
    function cargarEnv($ruta) {
    if (!file_exists($ruta)) {
        return false;
    }

    // Lee el archivo línea por línea
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        // Ignorar comentarios
        if (strpos(trim($linea), '#') === 0) {
            continue;
        }

        // Dividir por el primer signo "="
        list($clave, $valor) = explode('=', $linea, 2);

        $clave = trim($clave);
        $valor = trim($valor);

        // Guardar en las variables de entorno de PHP
        putenv("$clave=$valor");
        $_ENV[$clave] = $valor;
        $_SERVER[$clave] = $valor;
    }
    return true;
}

// Llamar a la función apuntando a la raíz
cargarEnv(__DIR__ . '/../.env');


?>