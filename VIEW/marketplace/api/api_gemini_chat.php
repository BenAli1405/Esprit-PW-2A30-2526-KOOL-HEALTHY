<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/api_bootstrap.php';
require_once __DIR__ . '/../../../config/Lib/ProfanityFilter.php';

$data = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'JSON invalide']);
    exit;
}

$message = isset($data['message']) ? trim((string) $data['message']) : '';
$history = isset($data['history']) && is_array($data['history']) ? $data['history'] : [];

if ($message === '' || strlen($message) > 4000) {
    echo json_encode(['success' => false, 'error' => 'Message vide ou trop long']);
    exit;
}

$purgeMsg = ProfanityFilter::cleanOrReject($message);
if (!$purgeMsg['ok']) {
    echo json_encode(['success' => false, 'error' => $purgeMsg['error'] ?? 'Message refuse (moderation).']);
    exit;
}

$config = kool_config();
$key = $config['gemini_api_key'] ?? '';
if ($key === '') {
    echo json_encode(['success' => false, 'error' => 'Cle Gemini non configuree (gemini_api_key).']);
    exit;
}

$modelRaw = trim((string) ($config['gemini_model'] ?? 'gemini-2.5-flash-lite'));
if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_.-]{0,127}$/', $modelRaw)) {
    $modelRaw = 'gemini-2.5-flash-lite';
}

$contents = [];
foreach ($history as $turn) {
    if (!is_array($turn)) {
        continue;
    }
    $role = ($turn['role'] ?? '') === 'model' ? 'model' : 'user';
    $text = isset($turn['text']) ? trim((string) $turn['text']) : '';
    if ($text === '') {
        continue;
    }
    if ($role === 'user' && $text !== '') {
        $purgeTurn = ProfanityFilter::cleanOrReject($text);
        if (!$purgeTurn['ok']) {
            echo json_encode(['success' => false, 'error' => 'Historique : message refuse par la moderation. Rechargez le chat.']);
            exit;
        }
    }
    $contents[] = [
        'role' => $role === 'model' ? 'model' : 'user',
        'parts' => [['text' => $text]],
    ];
}

$contents[] = [
    'role' => 'user',
    'parts' => [['text' => $message]],
];

$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($modelRaw) . ':generateContent?key=' . rawurlencode($key);

$body = json_encode([
    'contents' => $contents,
    'systemInstruction' => [
        'parts' => [[
            'text' => 'Tu es l assistant Kool Healthy, application anti-gaspillage alimentaire. Reponds en francais, de maniere courte et utile.',
        ]],
    ],
], JSON_UNESCAPED_UNICODE);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 60,
]);

$response = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    echo json_encode(['success' => false, 'error' => 'Erreur reseau vers Gemini']);
    exit;
}

$json = json_decode($response, true);
if ($code >= 400) {
    $err = is_array($json) ? ($json['error']['message'] ?? $response) : $response;
    echo json_encode(['success' => false, 'error' => (string) $err]);
    exit;
}

$reply = '';
if (is_array($json)) {
    $candidates = $json['candidates'] ?? [];
    if (isset($candidates[0]['content']['parts'][0]['text'])) {
        $reply = (string) $candidates[0]['content']['parts'][0]['text'];
    }
}

if ($reply === '') {
    echo json_encode(['success' => false, 'error' => 'Reponse Gemini vide']);
    exit;
}

echo json_encode(['success' => true, 'reply' => $reply], JSON_UNESCAPED_UNICODE);
