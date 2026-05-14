<?php
declare(strict_types=1);

/**
 * Evite d envoyer deux fois le meme recu e-mail pour une session Checkout.
 */
class StripeReceiptLock {

    private static function dir(): string {
        return dirname(__DIR__) . '/storage/cache';
    }

    public static function path(string $checkoutSessionId): string {
        return self::dir() . '/stripe_receipt_' . hash('sha256', $checkoutSessionId) . '.sent';
    }

    public static function exists(string $checkoutSessionId): bool {
        return is_readable(self::path($checkoutSessionId));
    }

    public static function create(string $checkoutSessionId): void {
        $dir = self::dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents(self::path($checkoutSessionId), date('c'), LOCK_EX);
    }
}
