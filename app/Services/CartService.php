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
        $items = $cart['items'];

        $productIds = [];
        $addonIds = [];
        foreach ($items as $it) {
            $pid = (int) ($it['product_id'] ?? 0);
            if ($pid > 0) {
                $productIds[] = $pid;
            }
            foreach ((array) ($it['addons'] ?? []) as $aid) {
                $aid = (int) $aid;
                if ($aid > 0) {
                    $addonIds[] = $aid;
                }
            }
        }

        $products = CatalogBatchLoader::productsByIds($pdo, $unitId, $productIds);
        $addonsMap = CatalogBatchLoader::addonsByIds($pdo, $productIds, $addonIds);

        $lines = [];
        $subtotal = 0.0;

        foreach ($items as $idx => $it) {
            $pid = (int) ($it['product_id'] ?? 0);
            $qty = max(1, (int) ($it['qty'] ?? 1));
            $p = $products[$pid] ?? null;
            if ($p === null) {
                continue;
            }

            $addons = [];
            $addonsTotal = 0.0;
            foreach ((array) ($it['addons'] ?? []) as $aid) {
                $aid = (int) $aid;
                $a = $addonsMap[$aid] ?? null;
                if ($a === null || (int) ($a['product_id'] ?? 0) !== $pid) {
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
