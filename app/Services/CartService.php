<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use PDO;

/**
 * Enriquece carrinho em sessão com nomes, preços e totais vindos do banco.
 */
final class CartService
{
    /**
     * @param array<string,mixed>|null $cart
     * @return array{items:list<array<string,mixed>>,subtotal:float,delivery_fee:float,minimum_order:float,item_count:int}|null
     */
    public static function enrich(?array $cart, ?array $unit): ?array
    {
        if (!is_array($cart) || empty($cart['items']) || !is_array($unit)) {
            return null;
        }

        $unitId = (int) ($cart['unit_id'] ?? 0);
        $pdo = Database::pdo();
        $lines = [];
        $subtotal = 0.0;

        $pStmt = $pdo->prepare(
            'SELECT id, name, price FROM products WHERE id = :id AND unit_id = :u AND status = "active" AND deleted_at IS NULL LIMIT 1'
        );
        $aStmt = $pdo->prepare(
            'SELECT id, name, price FROM product_addons WHERE id = :id AND product_id = :pid AND is_active = 1 AND deleted_at IS NULL LIMIT 1'
        );

        foreach ($cart['items'] as $idx => $it) {
            $pid = (int) ($it['product_id'] ?? 0);
            $qty = max(1, (int) ($it['qty'] ?? 1));
            $pStmt->execute(['id' => $pid, 'u' => $unitId]);
            $p = $pStmt->fetch(PDO::FETCH_ASSOC);
            if ($p === false) {
                continue;
            }

            $addons = [];
            $addonsTotal = 0.0;
            foreach ((array) ($it['addons'] ?? []) as $aid) {
                $aid = (int) $aid;
                if ($aid <= 0) {
                    continue;
                }
                $aStmt->execute(['id' => $aid, 'pid' => $pid]);
                $a = $aStmt->fetch(PDO::FETCH_ASSOC);
                if ($a === false) {
                    continue;
                }
                $addons[] = ['id' => (int) $a['id'], 'name' => (string) $a['name'], 'price' => (float) $a['price']];
                $addonsTotal += (float) $a['price'];
            }

            $unitPrice = (float) $p['price'] + $addonsTotal;
            $lineTotal = round($unitPrice * $qty, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'index' => $idx,
                'product_id' => $pid,
                'product_name' => (string) $p['name'],
                'qty' => $qty,
                'unit_price' => round($unitPrice, 2),
                'line_total' => $lineTotal,
                'addons' => $addons,
            ];
        }

        if ($lines === []) {
            return null;
        }

        return [
            'items' => $lines,
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => (float) ($unit['delivery_fee'] ?? 0),
            'minimum_order' => max(0, (float) ($unit['minimum_order'] ?? 0)),
            'item_count' => array_sum(array_column($lines, 'qty')),
        ];
    }

    /**
     * Mescla item igual (mesmo produto + mesmos adicionais) no carrinho.
     *
     * @param array<string,mixed> $cart
     */
    public static function addItem(array &$cart, int $productId, int $qty, array $addons): void
    {
        $addons = array_values(array_unique(array_map('intval', $addons)));
        sort($addons);

        foreach ($cart['items'] as &$it) {
            $existing = array_values(array_unique(array_map('intval', (array) ($it['addons'] ?? []))));
            sort($existing);
            if ((int) ($it['product_id'] ?? 0) === $productId && $existing === $addons) {
                $it['qty'] = max(1, (int) ($it['qty'] ?? 1)) + $qty;
                return;
            }
        }
        unset($it);

        $cart['items'][] = [
            'product_id' => $productId,
            'qty' => $qty,
            'addons' => $addons,
        ];
    }
}
