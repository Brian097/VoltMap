async function guardarEnBaseDeDatos(puntosCarga) {
    try {
        const response = await fetch('/app/controller/guardar_cargadores.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(puntosCarga)
        });

        const resultado = await response.json();
        if (!resultado.success) {
            throw new Error(resultado.message || 'Error al guardar en la base de datos');
        }

        console.log(`Se sincronizaron exitosamente ${puntosCarga.length} Puntos de Carga con la BD.`);
    } catch (error) {
        console.error("Fallo al persistir en la base de datos:", error);
    }
}


async function sincronizarDatos() {
    const estadoTxt = document.getElementById('estadoSincronizacion');
    const boton = document.getElementById('btnSincronizar');

    try {
        boton.disabled = true;
        estadoTxt.textContent = "Sincronizando con Open Charge Map...";
        estadoTxt.style.color = "var(--azul)";

        // Llamada a la función principal de cargadores.js
        const resultado = await obtenerYmapearCargadores();

        estadoTxt.textContent = "¡Sincronización exitosa!";
        estadoTxt.style.color = "var(--verde)";
        
        // Opcional: Recargar el mapa o la página después de un momento para ver los cambios
        setTimeout(() => {
            location.reload(); 
        }, 1500);

    } catch (error) {
        console.error(error);
        estadoTxt.textContent = "Error en la sincronización.";
        estadoTxt.style.color = "var(--rojo)";
        boton.disabled = false;
    }
}