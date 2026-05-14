<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../../CONTROLLER/SaleController.php';
require_once __DIR__ . '/../../../config/Lib/PaymentMailer.php';

$data = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'JSON invalide']);
    exit;
}

$ids = [];
if (isset($data['id_ventes']) && is_array($data['id_ventes'])) {
    foreach ($data['id_ventes'] as $v) {
        $n = (int) $v;
        if ($n > 0) {
            $ids[] = $n;
        }
    }
    $ids = array_values(array_unique($ids));
} elseif (isset($data['id_vente'])) {
    $n = (int) $data['id_vente'];
    if ($n > 0) {
        $ids[] = $n;
    }
}

if ($ids === []) {
    echo json_encode(['success' => false, 'error' => 'id_vente ou id_ventes requis']);
    exit;
}

$buyerEmail = isset($data['buyer_email']) ? trim((string) $data['buyer_email']) : '';
$emailValid = $buyerEmail === '' || filter_var($buyerEmail, FILTER_VALIDATE_EMAIL);

if (!$emailValid) {
    echo json_encode(['success' => false, 'error' => 'Email invalide']);
    exit;
}

$controller = new SaleController();
$reserved = [];
$failed = [];

foreach ($ids as $idVente) {
    $sale = $controller->getSaleById($idVente);
    if ($sale === null || $sale->getStatut() !== 'disponible') {
        $failed[] = $idVente;
        continue;
    }
    if ($controller->reserveSaleSurPlace($idVente)) {
        $reserved[] = $idVente;
    } else {
        $failed[] = $idVente;
    }
}

if ($reserved !== [] && $buyerEmail !== '') {
    $lines = [];
    foreach ($reserved as $vid) {
        $s = $controller->getSaleById($vid);
        if ($s !== null) {
            $lines[] = $s;
        }
    }
    if ($lines !== []) {
        PaymentMailer::sendReservationSurPlace($buyerEmail, $lines);
    }
}

echo json_encode([
    'success' => $reserved !== [],
    'reserved' => $reserved,
    'failed' => $failed,
    'error' => $reserved === [] ? 'Aucune annonce n a pu etre reservee (deja reservee ou vendue).' : null,
]);
