<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/api_bootstrap.php';
require_once __DIR__ . '/../../../CONTROLLER/SaleController.php';
require_once __DIR__ . '/../../../config/Lib/StripeCartMeta.php';
require_once __DIR__ . '/../../../config/Lib/StripeNative.php';

$data = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'JSON invalide']);
    exit;
}

$buyerEmail = isset($data['buyer_email']) ? trim((string) $data['buyer_email']) : '';
if ($buyerEmail === '' || !filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'buyer_email valide requis']);
    exit;
}

$cartLines = [];
if (isset($data['cart']) && is_array($data['cart'])) {
    foreach ($data['cart'] as $item) {
        if (is_array($item) && isset($item['id_vente'])) {
            $n = (int) $item['id_vente'];
            $q = (int) ($item['qty'] ?? 1);
            if ($n > 0 && $q > 0) {
                $cartLines[] = ['id_vente' => $n, 'qty' => $q];
            }
        } else {
            $n = (int) $item;
            if ($n > 0) {
                $cartLines[] = ['id_vente' => $n, 'qty' => 1];
            }
        }
    }
} elseif (isset($data['id_vente'])) {
    $n = (int) $data['id_vente'];
    $q = (int) ($data['qty'] ?? 1);
    if ($n > 0 && $q > 0) {
        $cartLines[] = ['id_vente' => $n, 'qty' => $q];
    }
}

$merged = [];
foreach ($cartLines as $row) {
    $id = $row['id_vente'];
    $merged[$id] = ($merged[$id] ?? 0) + $row['qty'];
}
$cartLines = [];
foreach ($merged as $idVente => $qty) {
    $cartLines[] = ['id_vente' => $idVente, 'qty' => $qty];
}

if ($cartLines === []) {
    echo json_encode(['success' => false, 'error' => 'id_vente ou cart requis']);
    exit;
}

$config = kool_config();
$secret = trim((string) ($config['stripe_secret_key'] ?? ''));
if ($secret === '') {
    $projRoot = dirname(__DIR__, 2);
    $localFile = $projRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.local.php';
    $hint = is_readable($localFile)
        ? 'Ouvrez config/config.local.php et renseignez stripe_secret_key (cle secrete sk_test_... ou sk_live_... sur https://dashboard.stripe.com/apikeys ).'
        : 'Copiez config/config.local.php.example vers config/config.local.php, puis ajoutez stripe_secret_key et stripe_publishable_key depuis https://dashboard.stripe.com/apikeys';
    echo json_encode(['success' => false, 'error' => 'Stripe : cle secrete absente. ' . $hint]);
    exit;
}

$currency = strtolower(preg_replace('/[^a-z]/', '', (string) ($config['stripe_currency'] ?? 'eur'))) ?: 'eur';
$baseUrl = rtrim((string) ($config['app_base_url'] ?? ''), '/');

$controller = new SaleController();
$sales = [];
$lineItems = [];

foreach ($cartLines as $row) {
    $idVente = (int) $row['id_vente'];
    $qtyBuy = (int) $row['qty'];
    $sale = $controller->getSaleById($idVente);
    if ($sale === null || $sale->getStatut() === 'vendue') {
        echo json_encode(['success' => false, 'error' => 'Vente #' . $idVente . ' introuvable ou deja vendue']);
        exit;
    }
    if ($sale->getStatut() !== 'disponible') {
        echo json_encode(['success' => false, 'error' => 'Vente #' . $idVente . ' non disponible a l achat en ligne']);
        exit;
    }
    if ($sale->getQteAVendre() < $qtyBuy) {
        echo json_encode(['success' => false, 'error' => 'Stock annonce insuffisant pour la vente #' . $idVente]);
        exit;
    }

    $unitAmount = (int) round($sale->getPrix() * 100);
    if ($unitAmount < 1) {
        echo json_encode(['success' => false, 'error' => 'Prix unitaire invalide pour la vente #' . $idVente]);
        exit;
    }
    if ($unitAmount * $qtyBuy < 50) {
        echo json_encode(['success' => false, 'error' => 'Montant minimum Stripe non atteint pour la vente #' . $idVente . ' (augmentez la quantite ou le prix).']);
        exit;
    }

    $sales[$idVente] = $sale;
    $lineItems[] = [
        'quantity' => $qtyBuy,
        'price_data' => [
            'currency' => $currency,
            'unit_amount' => $unitAmount,
            'product_data' => [
                'name' => 'Achat: ' . ($sale->getNomProduit() ?? 'Produit') . ' x' . $qtyBuy,
                'description' => 'Vente #' . $idVente . ' — prix unitaire TND, encaissement ' . strtoupper($currency) . '.',
            ],
        ],
    ];
}

$metaIdVentes = implode(',', array_map(static fn (array $r) => (string) $r['id_vente'], $cartLines));
$metaCartLines = StripeCartMeta::encode($cartLines);

$session = StripeNative::createCheckoutSession(
    $secret,
    $buyerEmail,
    $baseUrl . '/VIEW/marketplace/client.php?payment=success&session_id={CHECKOUT_SESSION_ID}',
    $baseUrl . '/VIEW/marketplace/client.php?payment=cancel',
    $lineItems,
    [
        'id_ventes' => $metaIdVentes,
        'cart_lines' => $metaCartLines,
        'buyer_email' => $buyerEmail,
    ]
);

if (isset($session['error'])) {
    $msg = is_array($session['error']) ? (string) ($session['error']['message'] ?? 'Erreur Stripe') : 'Erreur Stripe';
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

$url = (string) ($session['url'] ?? '');
$sid = (string) ($session['id'] ?? '');
if ($url === '' || $sid === '') {
    echo json_encode(['success' => false, 'error' => 'Reponse Stripe incomplete (pas d URL de session).']);
    exit;
}

echo json_encode([
    'success' => true,
    'url' => $url,
    'session_id' => $sid,
]);
