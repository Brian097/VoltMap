// C:\xampp\htdocs\voltmap\config.js
const fs = require('fs');
const path = require('path');

function cargarEnv() {
    const rutasPosibles = [
        path.join(__dirname, '.env'),                     
        path.join(__dirname, '..', '.env'),                
        'C:\\xampp\\htdocs\\voltmap\\.env'                 
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

    const contenido = fs.readFileSync(rutaFinal, 'utf-8');
    const lineas = contenido.split(/\r?\n/);

    lineas.forEach(linea => {
        linea = linea.trim();

        if (!linea || linea.startsWith('#')) return;

        if (linea.includes('=')) {
            const [clave, ...resto] = linea.split('=');
            const valorRaw = resto.join('=').trim();

            const valor = valorRaw.replace(/^['"]|['"]$/g, '');
            
            process.env[clave.trim()] = valor;
        }
    });

    return true;
}


cargarEnv();
