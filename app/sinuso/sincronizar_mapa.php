
<?php
// Configuración de la base de datos
require_once "/voltmap/app/conexion.php";

// Endpoint de OpenChargeMap filtrado por Uruguay (código UY) o coordenadas específicas
$apiUrl = "https://api.openchargemap.io/v3/poi?output=json&countrycode=UY&maxresults=700&key=$OCM_API_KEY";

// Inicializar cURL para consumir la API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// OpenChargeMap requiere un User-Agent identificativo
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: VoltMap-App',
    'X-API-Key: TU_API_KEY_OPENCARAGEMAP' // Opcional si te registraste para mayor límite
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $response) {
    $data = json_decode($response, true);
    
    // Preparar sentencia SQL con UPSERT (si ya existe el external_id, actualiza los datos)
    $sql = "INSERT INTO estaciones_carga (external_id, nombre, latitud, longitud, direccion) 
            VALUES (:external_id, :nombre, :latitud, :longitud, :direccion)
            ON DUPLICATE KEY UPDATE 
                nombre = VALUES(nombre), 
                latitud = VALUES(latitud), 
                longitud = VALUES(longitud), 
                direccion = VALUES(direccion)";
    
    $stmt = $pdo->prepare($sql);
    $importados = 0;

    foreach ($data as $item) {
        $external_id = $item['ID'] ?? null;
        $nombre      = $item['AddressInfo']['Title'] ?? 'Estación sin nombre';
        $latitud     = $item['AddressInfo']['Latitude'] ?? 0;
        $longitud    = $item['AddressInfo']['Longitude'] ?? 0;
        $direccion   = $item['AddressInfo']['AddressLine1'] ?? 'Sin dirección especificada';

        if ($external_id) {
            $stmt->execute([
                ':external_id' => $external_id,
                ':nombre'      => $nombre,
                ':latitud'     => $latitud,
                ':longitud'    => $longitud,
                ':direccion'   => $direccion
            ]);
            $importados++;
        }
    }
    
    echo "Sincronización exitosa. Se procesaron $importados estaciones.";
} else {
    echo "Error al conectar con la API de mapas. Código HTTP: $httpCode";
}
?>