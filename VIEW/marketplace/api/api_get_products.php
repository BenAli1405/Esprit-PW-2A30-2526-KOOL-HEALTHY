<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../CONTROLLER/ProductController.php';
require_once __DIR__ . '/../../../MODEL/Product.php';

$controller = new ProductController();

try {
    $products = $controller->getAllProducts();
    $result = [];

    foreach ($products as $product) {
        $result[] = [
            'id' => $product->getId(),
            'nom' => $product->getNom(),
            'date_expiration' => $product->getDateExpiration(),
            'qte' => $product->getQte(),
        ];
    }

    echo json_encode($result);
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
