const OCM_API_KEY = "";
const latInicial = -34.4811;
const lngInicial = -54.3333;

const map = L.map('map').setView([latInicial, lngInicial], 10);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap | Open Charge Map'
}).addTo(map);

async function obtenerCargadores() {
    const url = `https://api.openchargemap.io/v3/poi?output=json&countrycode=UY&maxresults=700&key=${OCM_API_KEY}`;
    

    try {
        const response = await fetch(url);
        const data = await response.json();
        console.log(data);
        data.forEach(estacion => {
            const info = estacion.AddressInfo;
            if (info && info.Latitude && info.Longitude) {
                
                const titulo = info.Title || "Estación de carga";
                const direccion = info.AddressLine1 || "Dirección no disponible";
                const numPuntos = estacion.NumberOfPoints || 1;

                const marker = L.marker([info.Latitude, info.Longitude]).addTo(map);
                
                marker.bindPopup(`
                    <b>${titulo}</b><br>
                    ${direccion}<br>
                    <small>Puntos disponibles: ${numPuntos}</small>
                `);
            }
        });
    } catch (error) {
        console.error("Error cargando los datos de Open Charge Map:", error);
    }
}

obtenerCargadores();