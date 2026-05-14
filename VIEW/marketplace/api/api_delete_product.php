<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../CONTROLLER/ProductController.php';

$controller = new ProductController();
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing product id']);
    exit;
}

try {
    $success = $controller->deleteProduct($data['id']);
    echo json_encode(['success' => $success]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
