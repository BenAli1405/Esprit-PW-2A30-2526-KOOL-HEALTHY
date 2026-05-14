<?php

require_once dirname(__DIR__, 2) . '/MODEL/Sale.php';
require_once __DIR__ . '/MailSender.php';

class PaymentMailer {

    public static function sendPaymentConfirmation(
        string $toEmail,
        Sale $sale,
        string $currency,
        int $amountMinor,
        ?int $quantiteAchetee = null
    ): bool {
        $subject = 'Paiement confirme - Kool Healthy';
        $amountHuman = number_format($amountMinor / 100, 2, ',', ' ') . ' ' . strtoupper($currency);
        $qtyLine = $quantiteAchetee !== null && $quantiteAchetee > 0 ? $quantiteAchetee : $sale->getQteAVendre();
        $body = "Bonjour,\r\n\r\n";
        $body .= "Votre paiement a bien ete enregistre.\r\n\r\n";
        $body .= 'Vente #' . $sale->getIdVente() . "\r\n";
        $body .= 'Produit : ' . ($sale->getNomProduit() ?? '') . "\r\n";
        $body .= 'Quantite achetee : ' . $qtyLine . "\r\n";
        $body .= 'Montant paye : ' . $amountHuman . "\r\n\r\n";
        $body .= "Merci d utiliser Kool Healthy.\r\n";

        return MailSender::send($toEmail, $subject, $body);
    }

    /**
     * @param Sale[] $sales
     */
    public static function sendCartPaymentConfirmation(
        string $toEmail,
        array $sales,
        string $currency,
        int $amountMinor,
        ?array $qtyBySaleId = null
    ): bool {
        if ($sales === []) {
            return false;
        }

        $subject = 'Paiement panier confirme - Kool Healthy';
        $amountHuman = number_format($amountMinor / 100, 2, ',', ' ') . ' ' . strtoupper($currency);
        $body = "Bonjour,\r\n\r\nVotre paiement pour plusieurs articles a bien ete enregistre.\r\n\r\n";
        $body .= 'Montant total : ' . $amountHuman . "\r\n\r\n";
        foreach ($sales as $sale) {
            if (!$sale instanceof Sale) {
                continue;
            }
            $id = (int) $sale->getIdVente();
            $bought = ($qtyBySaleId !== null && isset($qtyBySaleId[$id])) ? (int) $qtyBySaleId[$id] : $sale->getQteAVendre();
            $body .= '- Vente #' . $id . ' : ' . ($sale->getNomProduit() ?? '') . ' (quantite achetee : ' . $bought . ")\r\n";
        }
        $body .= "\r\nMerci d utiliser Kool Healthy.\r\n";

        return MailSender::send($toEmail, $subject, $body);
    }

    /**
     * @param Sale[] $sales
     */
    public static function sendReservationSurPlace(string $toEmail, array $sales): bool {
        if ($sales === []) {
            return false;
        }

        $subject = 'Reservation sur place - Kool Healthy';
        $body = "Bonjour,\r\n\r\nVotre commande a ete reservee pour retrait / paiement sur place.\r\n\r\n";
        foreach ($sales as $sale) {
            if (!$sale instanceof Sale) {
                continue;
            }
            $body .= '- Vente #' . $sale->getIdVente() . ' : ' . ($sale->getNomProduit() ?? '') . ' — '
                . number_format($sale->getPrix(), 2, ',', ' ') . " TND (quantite " . $sale->getQteAVendre() . ")\r\n";
        }
        $body .= "\r\nPresentez-vous au point de retrait pour finaliser le paiement.\r\n";

        return MailSender::send($toEmail, $subject, $body);
    }
}
