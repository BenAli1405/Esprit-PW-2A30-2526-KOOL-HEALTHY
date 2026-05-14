<?php
declare(strict_types=1);

/**
 * Moderation grossieretes (PurgoMalum) — utilisable en GET ou POST JSON.
 * Reponse : { "success": true, "allowed": true } ou { "success": false, "allowed": false, "error": "..." }.
 */
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../../config/Lib/ProfanityFilter.php';

$text = '';

if (isset($_GET['text'])) {
    $text = (string) $_GET['text'];
} else {
    $data = json_decode((string) file_get_contents('php://input'), true);
    if (is_array($data) && isset($data['text'])) {
        $text = (string) $data['text'];
    }
}

$text = trim($text);

if ($text === '') {
    echo json_encode(['success' => true, 'allowed' => true, 'empty' => true]);
    exit;
}

if (strlen($text) > 8000) {
    echo json_encode(['success' => false, 'allowed' => false, 'error' => 'Texte trop long (max 8000 caracteres).']);
    exit;
}

$result = ProfanityFilter::cleanOrReject($text);

if (!$result['ok']) {
    echo json_encode([
        'success' => false,
        'allowed' => false,
        'error' => $result['error'] ?? 'Texte refuse.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'allowed' => true,
    'text' => $result['text'],
], JSON_UNESCAPED_UNICODE);
