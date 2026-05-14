<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../../CONTROLLER/ProductController.php';
require_once __DIR__ . '/../../../MODEL/Product.php';
require_once __DIR__ . '/../../../config/Lib/ProfanityFilter.php';

$controller = new ProductController();
$data = json_decode((string) file_get_contents('php://input'), true);

if (!is_array($data)) {
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
        (string) $data['id'],
        (string) $data['nom'],
        (string) $data['date_expiration'],
        (int) $data['qte']
    );

    if (!$controller->addProduct($product)) {
        echo json_encode(['success' => false, 'error' => 'Echec insertion produit']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
