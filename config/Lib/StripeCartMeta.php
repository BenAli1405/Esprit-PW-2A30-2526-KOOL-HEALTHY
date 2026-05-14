<?php
declare(strict_types=1);

/**
 * Encode / decode des lignes panier pour metadata Stripe (id_vente:qty).
 */
class StripeCartMeta {

    /**
     * @param list<array{id_vente: int, qty: int}> $lines
     */
    public static function encode(array $lines): string {
        $parts = [];
        foreach ($lines as $row) {
            $id = (int) ($row['id_vente'] ?? 0);
            $qty = (int) ($row['qty'] ?? 0);
            if ($id > 0 && $qty > 0) {
                $parts[] = $id . ':' . $qty;
            }
        }

        return implode(',', $parts);
    }

    /**
     * @param array<string, string> $meta
     * @return list<array{id_vente: int, qty: int}>
     */
    public static function parseFromMetadata(array $meta): array {
        $cl = isset($meta['cart_lines']) ? trim((string) $meta['cart_lines']) : '';
        if ($cl !== '') {
            $lines = [];
            foreach (explode(',', $cl) as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }
                if (preg_match('/^(\d+):(\d+)$/', $part, $m)) {
                    $lines[] = ['id_vente' => (int) $m[1], 'qty' => (int) $m[2]];
                } elseif (preg_match('/^(\d+)$/', $part, $m)) {
                    $lines[] = ['id_vente' => (int) $m[1], 'qty' => 1];
                }
            }

            return self::mergeQuantities($lines);
        }

        $raw = isset($meta['id_ventes']) ? trim((string) $meta['id_ventes']) : '';
        if ($raw === '') {
            return [];
        }
        $lines = [];
        foreach (explode(',', $raw) as $p) {
            $n = (int) trim($p);
            if ($n > 0) {
                $lines[] = ['id_vente' => $n, 'qty' => 1];
            }
        }

        return $lines;
    }

    /**
     * @param list<array{id_vente: int, qty: int}> $lines
     * @return list<array{id_vente: int, qty: int}>
     */
    private static function mergeQuantities(array $lines): array {
        $map = [];
        foreach ($lines as $row) {
            $id = (int) ($row['id_vente'] ?? 0);
            $qty = (int) ($row['qty'] ?? 0);
            if ($id <= 0 || $qty <= 0) {
                continue;
            }
            $map[$id] = ($map[$id] ?? 0) + $qty;
        }
        $out = [];
        foreach ($map as $id => $qty) {
            $out[] = ['id_vente' => $id, 'qty' => $qty];
        }

        return $out;
    }
}
