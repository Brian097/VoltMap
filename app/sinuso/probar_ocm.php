<?php
require_once __DIR__ . '/app/model/conexion.php'; // Ajusta la ruta a tu archivo de conexión si es necesario

$OCM_API_KEY = '';
$apiUrl = "https://api.openchargemap.io/v3/poi?output=json&countrycode=UY&maxresults=700&key=$OCM_API_KEY";

$options = [
    "http" => [
        "header" => "User-Agent: VoltMap-App\r\n"
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);
$stations = json_decode($response, true);

foreach ($stations as $station) {
    if ($station['ID'] == 471875) {
        echo "<h1>Punto encontrado: 471875</h1>";
        echo "Total de conectores que trae la API: " . count($station['Connections']) . "<br><pre>";
        print_r($station['Connections']);
        echo "</pre>";
        break;
    }
}
?>