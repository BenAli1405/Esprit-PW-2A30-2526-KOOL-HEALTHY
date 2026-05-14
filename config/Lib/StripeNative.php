<?php
declare(strict_types=1);

/**
 * Appels Stripe REST sans SDK (PHP natif + cURL).
 *
 * @see https://stripe.com/docs/api
 */
final class StripeNative {

    private const API_BASE = 'https://api.stripe.com/v1';

    /**
     * @param array<string, mixed> $lineItems Chaque element : quantity, price_data[currency, unit_amount, product_data[name, description?]]
     * @param array<string, string> $metadata
     * @return array<string, mixed> Session decodee (cle url, id, etc.) ou tableau avec cle error
     */
    public static function createCheckoutSession(
        string $secretKey,
        string $buyerEmail,
        string $successUrl,
        string $cancelUrl,
        array $lineItems,
        array $metadata
    ): array {
        $flat = [
            'mode' => 'payment',
            'customer_email' => $buyerEmail,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];
        foreach ($metadata as $k => $v) {
            $flat['metadata[' . $k . ']'] = (string) $v;
        }
        $i = 0;
        foreach ($lineItems as $line) {
            $base = 'line_items[' . $i . ']';
            $flat[$base . '[quantity]'] = (int) ($line['quantity'] ?? 1);
            $pd = $line['price_data'] ?? [];
            $flat[$base . '[price_data][currency]'] = (string) ($pd['currency'] ?? 'eur');
            $flat[$base . '[price_data][unit_amount]'] = (int) ($pd['unit_amount'] ?? 0);
            $prod = $pd['product_data'] ?? [];
            $flat[$base . '[price_data][product_data][name]'] = (string) ($prod['name'] ?? 'Achat');
            if (!empty($prod['description'])) {
                $flat[$base . '[price_data][product_data][description]'] = (string) $prod['description'];
            }
            $i++;
        }

        $body = http_build_query($flat, '', '&', PHP_QUERY_RFC3986);

        return self::request('POST', '/checkout/sessions', $secretKey, $body);
    }

    /**
     * @return array<string, mixed>
     */
    public static function retrieveCheckoutSession(string $secretKey, string $sessionId): array {
        $path = '/checkout/sessions/' . rawurlencode($sessionId);

        return self::request('GET', $path, $secretKey, null);
    }

    /**
     * Verifie la signature Stripe des webhooks.
     *
     * @return array<string, mixed> Evenement JSON decode
     * @throws RuntimeException si signature ou payload invalide
     */
    public static function constructWebhookEvent(string $payload, string $stripeSignatureHeader, string $webhookSecret): array {
        $key = $webhookSecret;
        if (str_starts_with($webhookSecret, 'whsec_')) {
            $decoded = base64_decode(substr($webhookSecret, 6), true);
            if ($decoded !== false && $decoded !== '') {
                $key = $decoded;
            }
        }
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $stripeSignatureHeader) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $eq = strpos($part, '=');
            if ($eq === false) {
                continue;
            }
            $key = substr($part, 0, $eq);
            $val = substr($part, $eq + 1);
            if ($key === 't') {
                $timestamp = (int) $val;
            } elseif ($key === 'v1') {
                $signatures[] = $val;
            }
        }
        if ($timestamp === null || $signatures === []) {
            throw new RuntimeException('En-tete Stripe-Signature incomplet');
        }
        if (abs(time() - $timestamp) > 600) {
            throw new RuntimeException('Horodatage webhook trop ancien');
        }
        $signedPayload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $key);
        $ok = false;
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                $ok = true;
                break;
            }
        }
        if (!$ok) {
            throw new RuntimeException('Signature webhook incorrecte');
        }

        $data = json_decode($payload, true);
        if (!is_array($data)) {
            throw new RuntimeException('JSON webhook invalide');
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private static function request(string $method, string $path, string $secretKey, ?string $body): array {
        $url = self::API_BASE . $path;
        $ch = curl_init($url);
        if ($ch === false) {
            return ['error' => ['message' => 'curl_init a echoue']];
        }
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $secretKey . ':',
            CURLOPT_HTTPHEADER => ['Stripe-Version: 2023-10-16'],
            CURLOPT_TIMEOUT => 30,
        ];
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $body ?? '';
            $opts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
        } else {
            $opts[CURLOPT_HTTPGET] = true;
        }
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);
        if ($raw === false) {
            return ['error' => ['message' => $cerr ?: 'Erreur reseau Stripe']];
        }
        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return ['error' => ['message' => 'Reponse Stripe non JSON']];
        }
        if ($code >= 400) {
            $decoded['_http_status'] = $code;
        }

        return $decoded;
    }
}
