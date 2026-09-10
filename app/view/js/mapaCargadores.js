// view/js/mapaCargadores.js
document.addEventListener("DOMContentLoaded", async () => {
    // 1. Inicializar el mapa centrado en Uruguay (o tus coordenadas base)
    const map = L.map('map').setView([-34.9011, -56.1645], 7);

    // 2. Cargar la capa base de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    try {
        // 3. Consultar TU endpoint local en lugar de Open Charge Map
        const response = await fetch('/app/controller/api_puntos.php');
        const puntos = await response.json();

        if (!Array.isArray(puntos)) {
            console.error("Los datos recibidos no tienen el formato esperado:", puntos);
            return;
        }

        // 4. Recorrer los puntos de carga de la base de datos y pintarlos
        puntos.forEach(punto => {
            // Definir color del marcador según el estado de sus cargadores
            let colorMarcador = '#22c55e'; // Verde (Disponible por defecto)

            if (punto.cargadores && punto.cargadores.length > 0) {
                punto.cargadores.forEach(c => {
                    let estadoOp = (c.estadoOperativo || '').toLowerCase();
                    let estadoUso = (c.estadoUso || '').toLowerCase();

                    if (estadoOp.includes('fuera') || estadoOp.includes('out') || estadoOp.includes('fault')) {
                        colorMarcador = '#ef4444'; // Rojo (Fuera de servicio)
                    } else if (estadoUso.includes('uso') || estadoUso.includes('ocupado')) {
                        colorMarcador = '#f59e0b'; // Amarillo (En uso)
                    }
                });
            }

            // Crear el marcador circular en Leaflet
            const marker = L.circleMarker([parseFloat(punto.latitud), parseFloat(punto.longitud)], {
                radius: 8,
                fillColor: colorMarcador,
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map);

            // Construir el HTML interno del Popup con los conectores de la BD
            let htmlCargadores = '';
            if (punto.cargadores && punto.cargadores.length > 0) {
                punto.cargadores.forEach(c => {
                    htmlCargadores += `
                        <div style="border-top: 1px solid #e2e8f0; margin-top: 6px; padding-top: 4px; font-size: 12px;">
                            <b>Conector:</b> ${c.tipoConector} (${c.potenciaKilowatts} kW)<br>
                            <b>Tipo:</b> ${c.tipoCargador}<br>
                            <b>Estado:</b> ${c.estadoOperativo}
                        </div>
                    `;
                });
            } else {
                htmlCargadores = '<div style="font-size: 12px; color: #64748b; margin-top: 4px;">Sin cargadores registrados.</div>';
            }

            // Asignar el popup al marcador
            marker.bindPopup(`
                <div style="font-family: 'Inter', sans-serif; min-width: 200px;">
                    <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 700;">${punto.direccion}</h4>
                    <p style="margin: 0 0 6px 0; font-size: 12px; color: #64748b;">${punto.ciudadYDepartamento}</p>
                    <span style="font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 600;">${punto.tipoUsuario}</span>
                    <div style="margin-top: 8px;">
                        <strong style="font-size: 12px;">Conectores (${punto.cargadores.length}):</strong>
                        ${htmlCargadores}
                    </div>
                </div>
            `);
        });

    } catch (error) {
        console.error("Error al cargar los puntos desde la base de datos:", error);
    }
});