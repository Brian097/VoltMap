/* ==========================================================================
   LÓGICA MAPA - EDITAR CARGADOR (Leaflet)
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
    const inputLat = document.getElementById('latitud');
    const inputLng = document.getElementById('longitud');
    const contenedorMapa = document.getElementById('map-editar');

    if (!contenedorMapa || !inputLat || !inputLng) return;

    const latInicial = parseFloat(inputLat.value) || -34.4811;
    const lngInicial = parseFloat(inputLng.value) || -54.3333;

    // Inicializar el mapa
    const map = L.map('map-editar').setView([latInicial, lngInicial], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Crear marcador desplazable (draggable)
    const marker = L.marker([latInicial, lngInicial], {
        draggable: true
    }).addTo(map);

    // Actualizar inputs al arrastrar el marcador
    marker.on('dragend', function (e) {
        const position = marker.getLatLng();
        inputLat.value = position.lat.toFixed(6);
        inputLng.value = position.lng.toFixed(6);
    });

    // Actualizar al hacer clic en cualquier parte del mapa
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        inputLat.value = e.latlng.lat.toFixed(6);
        inputLng.value = e.latlng.lng.toFixed(6);
    });
});