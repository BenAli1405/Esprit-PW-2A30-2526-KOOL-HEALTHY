<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../../config/Lib/ProfanityFilter.php';

$data = json_decode((string) file_get_contents('php://input'), true);
$text = '';
if (is_array($data) && isset($data['text'])) {
    $text = (string) $data['text'];
}

$result = ProfanityFilter::cleanOrReject($text);
if (!$result['ok']) {
    echo json_encode(['success' => false, 'error' => $result['error']]);
    exit;
}

echo json_encode(['success' => true, 'text' => $result['text']]);
