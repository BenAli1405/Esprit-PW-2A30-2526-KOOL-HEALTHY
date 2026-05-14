<?php
declare(strict_types=1);

require_once __DIR__ . '/api_bootstrap.php';
require_once __DIR__ . '/../../../CONTROLLER/SaleController.php';
require_once __DIR__ . '/../../../config/Lib/PaymentMailer.php';
require_once __DIR__ . '/../../../config/Lib/StripeReceiptLock.php';
require_once __DIR__ . '/../../../config/Lib/StripeCartMeta.php';
require_once __DIR__ . '/../../../config/Lib/StripeFinalizeLock.php';
require_once __DIR__ . '/../../../config/Lib/StripeNative.php';

$config = kool_config();
$secret = $config['stripe_secret_key'] ?? '';
$whSecret = $config['stripe_webhook_secret'] ?? '';

$payload = @file_get_contents('php://input');
$sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if ($payload === false || $payload === '') {
    http_response_code(400);
    exit('empty body');
}

if ($secret === '' || $whSecret === '') {
    http_response_code(500);
    exit('webhook non configure');
}

try {
    $event = StripeNative::constructWebhookEvent($payload, $sig, (string) $whSecret);
} catch (Throwable $e) {
    http_response_code(400);
    exit('signature invalide');
}

if (($event['type'] ?? '') === 'checkout.session.completed') {
    $session = is_array($event['data']['object'] ?? null) ? $event['data']['object'] : [];
    $sessionIdForLock = (string) ($session['id'] ?? '');
    $meta = [];
    if (!empty($session['metadata']) && is_array($session['metadata'])) {
        $meta = $session['metadata'];
    }

    $cartLines = StripeCartMeta::parseFromMetadata($meta);

    $buyerEmail = !empty($meta['buyer_email']) ? trim((string) $meta['buyer_email']) : '';
    if ($buyerEmail === '' && !empty($session['customer_email'])) {
        $buyerEmail = trim((string) $session['customer_email']);
    }
    if ($buyerEmail === '' && !empty($session['customer_details']['email'])) {
        $buyerEmail = trim((string) $session['customer_details']['email']);
    }

    if ($cartLines !== [] && $buyerEmail !== '' && $sessionIdForLock !== '') {
        if (StripeFinalizeLock::exists($sessionIdForLock)) {
            http_response_code(200);
            echo json_encode(['received' => true, 'finalize' => 'already_done']);
            exit;
        }

        $controller = new SaleController();
        $ok = $controller->completeCartAfterPayment($cartLines);

        if ($ok) {
            StripeFinalizeLock::create($sessionIdForLock);
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
                        static function ($s) use ($qtyBySaleId) {
                            $id = $s->getIdVente();
                            $q = (int) ($qtyBySaleId[$id] ?? 1);

                            return (int) round($s->getPrix() * 100 * $q);
                        },
                        $salesForMail
                    )),
                    $qtyBySaleId
                );
            }
            if ($mailOk && $sessionIdForLock !== '') {
                StripeReceiptLock::create($sessionIdForLock);
            }
        }
    }
}

http_response_code(200);
echo json_encode(['received' => true]);
