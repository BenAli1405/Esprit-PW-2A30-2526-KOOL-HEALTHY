<?php
declare(strict_types=1);

/**
 * Appele depuis le navigateur apres success_url (?payment=success&session_id=cs_...)
 * pour (1) finaliser les ventes si le webhook na pas atteint le serveur local,
 * (2) envoyer le recu e-mail si la premiere tentative a echoue (mail() XAMPP, etc.).
 */
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/api_bootstrap.php';
require_once __DIR__ . '/../../../CONTROLLER/SaleController.php';
require_once __DIR__ . '/../../../config/Lib/PaymentMailer.php';
require_once __DIR__ . '/../../../config/Lib/StripeReceiptLock.php';
require_once __DIR__ . '/../../../config/Lib/StripeCartMeta.php';
require_once __DIR__ . '/../../../config/Lib/StripeFinalizeLock.php';
require_once __DIR__ . '/../../../config/Lib/StripeNative.php';

$sessionId = isset($_GET['session_id']) ? trim((string) $_GET['session_id']) : '';
if ($sessionId === '' || !preg_match('/^cs_[a-zA-Z0-9_]+$/', $sessionId)) {
    echo json_encode(['success' => false, 'error' => 'session_id Checkout invalide']);
    exit;
}

if (StripeReceiptLock::exists($sessionId)) {
    echo json_encode([
        'success' => true,
        'email' => 'already_sent',
        'message' => 'Recu deja envoye (ou traiement deja fait).',
    ]);
    exit;
}

$config = kool_config();
$secret = trim((string) ($config['stripe_secret_key'] ?? ''));
if ($secret === '') {
    echo json_encode(['success' => false, 'error' => 'stripe_secret_key non configure']);
    exit;
}

$session = StripeNative::retrieveCheckoutSession($secret, $sessionId);
if (isset($session['error'])) {
    $msg = is_array($session['error']) ? (string) ($session['error']['message'] ?? 'Stripe') : 'Stripe';
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

if (($session['payment_status'] ?? '') !== 'paid') {
    echo json_encode(['success' => false, 'error' => 'Session non payee']);
    exit;
}

$meta = [];
if (!empty($session['metadata']) && is_array($session['metadata'])) {
    $meta = $session['metadata'];
}
$cartLines = StripeCartMeta::parseFromMetadata($meta);

$buyerEmail = '';
if (!empty($meta['buyer_email'])) {
    $buyerEmail = trim((string) $meta['buyer_email']);
}
if ($buyerEmail === '' && !empty($session['customer_email'])) {
    $buyerEmail = trim((string) $session['customer_email']);
}
if ($buyerEmail === '' && !empty($session['customer_details']['email'])) {
    $buyerEmail = trim((string) $session['customer_details']['email']);
}
if ($buyerEmail === '' || !filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Email acheteur introuvable sur la session']);
    exit;
}

if ($cartLines === []) {
    echo json_encode(['success' => false, 'error' => 'Metadata panier (cart_lines / id_ventes) manquante']);
    exit;
}

$controller = new SaleController();
$completed = false;

if (StripeFinalizeLock::exists($sessionId)) {
    $completed = true;
} else {
    $completed = $controller->completeCartAfterPayment($cartLines);
    if ($completed) {
        StripeFinalizeLock::create($sessionId);
    }
}

if (!$completed && StripeFinalizeLock::exists($sessionId)) {
    $completed = true;
}

if (!$completed) {
    echo json_encode(['success' => false, 'error' => 'Impossible de finaliser les ventes (stock ou statut).']);
    exit;
}

$qtyBySaleId = [];
foreach ($cartLines as $row) {
    $qtyBySaleId[(int) $row['id_vente']] = (int) $row['qty'];
}

$currency = (string) ($session['currency'] ?? $config['stripe_currency'] ?? 'eur');
$total = (int) ($session['amount_total'] ?? 0);

$salesForMail = [];
foreach ($cartLines as $row) {
    $s = $controller->getSaleById((int) $row['id_vente']);
    if ($s !== null) {
        $salesForMail[] = $s;
    }
}

$mailOk = false;
if (count($salesForMail) === 1) {
    $one = $salesForMail[0];
    $bought = (int) ($qtyBySaleId[(int) $one->getIdVente()] ?? 1);
    $mailOk = PaymentMailer::sendPaymentConfirmation(
        $buyerEmail,
        $one,
        $currency,
        $total > 0 ? $total : (int) round($one->getPrix() * 100 * max(1, $bought)),
        $bought
    );
} elseif ($salesForMail !== []) {
    $mailOk = PaymentMailer::sendCartPaymentConfirmation(
        $buyerEmail,
        $salesForMail,
        $currency,
        $total > 0 ? $total : array_sum(array_map(
            static function ($sale) use ($qtyBySaleId) {
                $id = (int) $sale->getIdVente();
                $q = (int) ($qtyBySaleId[$id] ?? 1);

                return (int) round($sale->getPrix() * 100 * $q);
            },
            $salesForMail
        )),
        $qtyBySaleId
    );
}

if ($mailOk) {
    StripeReceiptLock::create($sessionId);
}

echo json_encode([
    'success' => true,
    'sales_finalized' => $completed,
    'email_sent' => $mailOk,
    'email_hint' => $mailOk
        ? 'Un e-mail de confirmation a ete envoye (verifiez aussi les courriers indesirables).'
        : 'Echec envoi e-mail : configurez SMTP (smtp_host dans config.local.php) ou consultez config/storage/logs/mail.log',
], JSON_UNESCAPED_UNICODE);
