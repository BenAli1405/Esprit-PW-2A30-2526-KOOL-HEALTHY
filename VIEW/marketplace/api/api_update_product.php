<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../CONTROLLER/ProductController.php';
require_once __DIR__ . '/../../../MODEL/Product.php';
require_once __DIR__ . '/../../../config/Lib/ProfanityFilter.php';

$controller = new ProductController();
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit;
}

$purge = ProfanityFilter::cleanOrReject((string) ($data['nom'] ?? ''));
if (!$purge['ok']) {
    echo json_encode(['success' => false, 'error' => $purge['error']]);
    exit;
}
$data['nom'] = $purge['text'];

try {
    $product = new Product(
        $data['id'],
        $data['nom'],
        $data['date_expiration'],
        (int) $data['qte']
    );

    $success = $controller->updateProduct($data['original_id'], $product);
    echo json_encode(['success' => $success]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
