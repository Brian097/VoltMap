async function obtenerYmapearCargadores(apiKey) {
    // Ejemplo filtrando por Uruguay (UY) o puedes ajustar la lat/lon y distancia
    const url = `https://api.openchargemap.io/v3/poi/?output=json&countrycode=UY&maxresults=50&key=${apiKey}`;

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error('Error en la conexión con Open Charge Map');

        const stations = await response.json();
        const listaCargadores = [];

        stations.forEach(station => {
            // Open Charge Map agrupa varios conectores dentro de una misma estación (POI)
            if (station.Connections && station.Connections.length > 0) {
                station.Connections.forEach((conn, index) => {
                    
                    // Mapeo directo con los atributos de tu clase Cargador
                    const cargador = {
                        id: `${station.ID}-${index}`, // ID único compuesto por estación + conector
                        visible: station.IsOperational ?? true,
                        potenciaKilowatts: conn.PowerKW || 0,
                        tipoConector: conn.ConnectionType?.Title || 'Desconocido',
                        tipoCargador: conn.CurrentType?.Title || conn.Level?.Title || 'Desconocido',
                        estadoUso: 'Disponible', // OCM usualmente no provee estado de uso en tiempo real sin integración adicional
                        estadoOperativo: station.StatusType?.Title || 'Desconocido',
                        precioKwh: 0.0,  // OCM almacena costos en texto plano (UsageCost), requiere parseo manual si se desea
                        precioHora: 0.0
                    };

                    listaCargadores.push(cargador);
                });
            }
        });

        // Llamada para persistir en tu base de datos (ejemplo enviándolo a tu backend)
        await guardarEnBaseDeDatos(listaCargadores);
        console.log(listaCargadores);
        
        return listaCargadores;
    } catch (error) {
        console.error("Fallo al obtener los datos:", error);
    }
}

async function guardarEnBaseDeDatos(cargadores) {
    // Reemplaza esto con la petición POST a tu API/Backend que interactúa con la BD
    /*
    await fetch('/api/cargadores', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cargadores)
    });
    */
    console.log(`Se procesaron ${cargadores.length} registros listos para insertar en la BD.`);
}
