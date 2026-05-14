<?php
declare(strict_types=1);

/**
 * Filtre de grossieretes via l'API publique PurgoMalum (sans cle).
 * @see https://www.purgomalum.com
 */
class ProfanityFilter {

    private const SERVICE_URL = 'https://www.purgomalum.com/service/json?text=';

    public static function cleanOrReject(string $text): array {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return ['ok' => true, 'text' => $text];
        }

        $url = self::SERVICE_URL . rawurlencode($trimmed);
        $raw = self::httpGet($url);
        if ($raw === null) {
            return ['ok' => false, 'error' => 'Service de moderation indisponible. Reessayez plus tard.'];
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['result'])) {
            return ['ok' => false, 'error' => 'Reponse de moderation invalide.'];
        }

        $cleaned = (string) $data['result'];
        if (strcasecmp($trimmed, $cleaned) !== 0) {
            return ['ok' => false, 'error' => 'Le texte contient des termes inappropries. Veuillez le modifier.'];
        }

        return ['ok' => true, 'text' => $trimmed];
    }

    private static function httpGet(string $url): ?string {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch !== false) {
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 8,
                    CURLOPT_HTTPHEADER => ['Accept: application/json'],
                ]);
                $body = curl_exec($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($body !== false && $code < 500) {
                    return (string) $body;
                }
            }
        }

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 8,
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents($url, false, $ctx);

        return $raw === false ? null : (string) $raw;
    }
}
