<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../CONTROLLER/SaleController.php';
require_once __DIR__ . '/../../../MODEL/Sale.php';

$controller = new SaleController();

try {
    $sales = $controller->getAllSales();
    $result = [];

    foreach ($sales as $sale) {
        $result[] = [
            'id_vente' => $sale->getIdVente(),
            'id_produit' => $sale->getIdProduit(),
            'nom_produit' => $sale->getNomProduit(),
            'date_expiration' => $sale->getDateExpiration(),
            'qte_a_vendre' => $sale->getQteAVendre(),
            'prix' => $sale->getPrix(),
            'statut' => $sale->getStatut(),
            'date_creation' => $sale->getDateCreation(),
        ];
    }

    echo json_encode($result);
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
