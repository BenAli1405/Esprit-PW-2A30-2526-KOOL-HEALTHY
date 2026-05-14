<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/api_bootstrap.php';
require_once __DIR__ . '/../../../config/Lib/MeteoClient.php';

$config = kool_config();

$lat = (float) ($config['weather_latitude'] ?? 36.8065);
$lon = (float) ($config['weather_longitude'] ?? 10.1815);
$tz = trim((string) ($config['weather_timezone'] ?? 'Africa/Tunis'));
$label = trim((string) ($config['weather_city_label'] ?? ''));

if (isset($_GET['lat'], $_GET['lon'])) {
    $lat = (float) $_GET['lat'];
    $lon = (float) $_GET['lon'];
}

if ($tz === '') {
    $tz = 'Africa/Tunis';
}

$result = MeteoClient::fetchCurrent($lat, $lon, $tz);

if (!$result['ok']) {
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Erreur meteo',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'summary' => $result['summary_fr'],
    'location_label' => $label !== '' ? $label : null,
    'latitude' => $lat,
    'longitude' => $lon,
    'timezone' => $tz,
    'temperature' => $result['temperature'],
    'humidity' => $result['humidity'],
    'wind_kmh' => $result['wind_kmh'],
    'weather_code' => $result['code'],
], JSON_UNESCAPED_UNICODE);
