<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../CONTROLLER/SaleController.php';
require_once __DIR__ . '/../../../MODEL/Sale.php';

$controller = new SaleController();
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit;
}

try {
    $sale = new Sale(
        null,
        $data['id_produit'],
        (int) $data['qte_a_vendre'],
        (float) $data['prix'],
        $data['statut'] ?? 'disponible'
    );

    $success = $controller->addSale($sale);
    echo json_encode(['success' => $success]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
