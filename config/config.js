// C:\xampp\htdocs\voltmap\config.js
const fs = require('fs');
const path = require('path');

function cargarEnv() {
    // Definimos las rutas más probables donde podría estar el .env
    const rutasPosibles = [
        path.join(__dirname, '.env'),                      // En la misma carpeta que config.js
        path.join(__dirname, '..', '.env'),                // Una carpeta arriba
        'C:\\xampp\\htdocs\\voltmap\\.env'                 // Ruta absoluta directa en Windows
    ];

    let rutaFinal = null;

    for (const ruta of rutasPosibles) {
        if (fs.existsSync(ruta)) {
            rutaFinal = ruta;
            break;
        }
    }

    // Si no lo encuentra en ningún lado, muestra un diagnóstico claro en la terminal
    if (!rutaFinal) {
        console.error("❌ Error Crítico: Node.js no puede leer el archivo .env");
        console.log("Se buscaron las siguientes rutas y todas fallaron:");
        rutasPosibles.forEach(r => console.log(` - ❌ No existe en: ${r}`));
        return false;
    }

    // Leer el archivo y separar por cualquier tipo de salto de línea (\n o \r\n)
    const contenido = fs.readFileSync(rutaFinal, 'utf-8');
    const lineas = contenido.split(/\r?\n/);

    lineas.forEach(linea => {
        linea = linea.trim();
        
        // Ignorar líneas vacías o comentarios
        if (!linea || linea.startsWith('#')) return;

        // Buscar el primer signo "=" para separar clave y valor
        if (linea.includes('=')) {
            const [clave, ...resto] = linea.split('=');
            const valorRaw = resto.join('=').trim();
            
            // Limpiar comillas iniciales o finales si las hay
            const valor = valorRaw.replace(/^['"]|['"]$/g, '');

            // Guardar globalmente en el objeto nativo de entorno de Node.js
            process.env[clave.trim()] = valor;
        }
    });

    return true;
}

// Ejecutar la función inmediatamente al requerir este archivo
cargarEnv();
