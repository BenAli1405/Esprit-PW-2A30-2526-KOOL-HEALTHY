<?php
declare(strict_types=1);

/**
 * Meteo courante via Open-Meteo (sans cle API).
 *
 * @see https://open-meteo.com/
 */
class MeteoClient {

    private const FORECAST_URL = 'https://api.open-meteo.com/v1/forecast';

    /**
     * @return array{ok: bool, error?: string, temperature?: float, humidity?: int, wind_kmh?: float, code?: int, summary_fr?: string}
     */
    public static function fetchCurrent(float $latitude, float $longitude, string $timezone): array {
        if ($latitude < -90.0 || $latitude > 90.0 || $longitude < -180.0 || $longitude > 180.0) {
            return ['ok' => false, 'error' => 'Coordonnees geographiques invalides.'];
        }

        $query = http_build_query([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timezone' => $timezone,
            'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
        ], '', '&', PHP_QUERY_RFC3986);

        $url = self::FORECAST_URL . '?' . $query;

        $ch = curl_init($url);
        if ($ch === false) {
            return ['ok' => false, 'error' => 'Impossible d initialiser la requete meteo.'];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $code >= 400) {
            return ['ok' => false, 'error' => 'Service meteo indisponible.'];
        }

        $json = json_decode($raw, true);
        if (!is_array($json) || !isset($json['current']) || !is_array($json['current'])) {
            return ['ok' => false, 'error' => 'Reponse meteo invalide.'];
        }

        $cur = $json['current'];
        $temp = isset($cur['temperature_2m']) ? (float) $cur['temperature_2m'] : null;
        $hum = isset($cur['relative_humidity_2m']) ? (int) $cur['relative_humidity_2m'] : null;
        $wind = isset($cur['wind_speed_10m']) ? (float) $cur['wind_speed_10m'] : null;
        $wcode = isset($cur['weather_code']) ? (int) $cur['weather_code'] : 0;

        $summary = self::summarizeFr($temp, $hum, $wind, $wcode);

        return [
            'ok' => true,
            'temperature' => $temp,
            'humidity' => $hum,
            'wind_kmh' => $wind,
            'code' => $wcode,
            'summary_fr' => $summary,
        ];
    }

    private static function summarizeFr(?float $temp, ?int $hum, ?float $windKmh, int $code): string {
        $sky = self::weatherCodeLabelFr($code);
        $parts = [];
        if ($temp !== null) {
            $parts[] = round($temp, 1) . ' °C';
        }
        $parts[] = $sky;
        if ($hum !== null && $hum > 0) {
            $parts[] = 'hum. ' . $hum . '%';
        }
        if ($windKmh !== null && $windKmh > 0) {
            $parts[] = 'vent ' . round($windKmh, 0) . ' km/h';
        }

        return implode(' · ', $parts);
    }

    private static function weatherCodeLabelFr(int $code): string {
        static $ranges = [
            [0, 0, 'Ciel degage'],
            [1, 3, 'Nuages partiels'],
            [45, 48, 'Brouillard'],
            [51, 57, 'Bruine'],
            [61, 67, 'Pluie'],
            [71, 77, 'Neige'],
            [80, 82, 'Averses'],
            [85, 86, 'Averses de neige'],
            [95, 95, 'Orages'],
            [96, 99, 'Orages avec grele'],
        ];
        foreach ($ranges as $r) {
            if ($code >= $r[0] && $code <= $r[1]) {
                return $r[2];
            }
        }

        return 'Conditions variables';
    }
}
