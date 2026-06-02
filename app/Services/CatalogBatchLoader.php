<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * Carrega produtos e adicionais em lote (evita N+1 no cardápio, carrinho e pedidos).
 */
final class CatalogBatchLoader
{
    /**
     * @param list<int> $productIds
     * @return array<int, array<string, mixed>>
     */
    public static function productsByIds(PDO $pdo, int $unitId, array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds), static fn (int $id): bool => $id > 0)));
        if ($productIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $sql = "SELECT id, name, price FROM products WHERE unit_id = ? AND id IN ($placeholders)
                AND status = 'active' AND deleted_at IS NULL";
        $st = $pdo->prepare($sql);
        $st->execute(array_merge([$unitId], $productIds));
        $map = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $map[(int) $row['id']] = $row;
        }

        return $map;
    }

    /**
     * @param list<int> $productIds
     * @return array<int, list<array<string, mixed>>>
     */
    public static function addonsGroupedByProduct(PDO $pdo, array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds), static fn (int $id): bool => $id > 0)));
        if ($productIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $sql = "SELECT * FROM product_addons WHERE product_id IN ($placeholders)
                AND is_active = 1 AND deleted_at IS NULL ORDER BY sort_order ASC, id ASC";
        $st = $pdo->prepare($sql);
        $st->execute($productIds);
        $grouped = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $pid = (int) $row['product_id'];
            $grouped[$pid][] = $row;
        }

        return $grouped;
    }

    /**
     * @param list<int> $productIds
     * @param list<int> $addonIds
     * @return array<int, array<string, mixed>> keyed by addon id
     */
    public static function addonsByIds(PDO $pdo, array $productIds, array $addonIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds), static fn (int $id): bool => $id > 0)));
        $addonIds = array_values(array_unique(array_filter(array_map('intval', $addonIds), static fn (int $id): bool => $id > 0)));
        if ($productIds === [] || $addonIds === []) {
            return [];
        }

        $phP = implode(',', array_fill(0, count($productIds), '?'));
        $phA = implode(',', array_fill(0, count($addonIds), '?'));
        $sql = "SELECT id, product_id, name, price FROM product_addons
                WHERE product_id IN ($phP) AND id IN ($phA) AND is_active = 1 AND deleted_at IS NULL";
        $st = $pdo->prepare($sql);
        $st->execute(array_merge($productIds, $addonIds));
        $map = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $map[(int) $row['id']] = $row;
        }

        return $map;
    }
}
