<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../CONTROLLER/SaleController.php';

$controller = new SaleController();
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id_vente'])) {
    echo json_encode(['success' => false, 'error' => 'Missing sale id']);
    exit;
}

try {
    $success = $controller->deleteSale((int) $data['id_vente']);
    echo json_encode(['success' => $success]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
