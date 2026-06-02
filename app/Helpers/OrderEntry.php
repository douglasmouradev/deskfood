<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Database;

/**
 * URL de entrada no fluxo de pedido (cardápio ou escolha de unidade).
 */
final class OrderEntry
{
    /**
     * @param list<array<string,mixed>> $units Unidades ativas (com chave slug)
     */
    public static function hrefFromUnits(array $units): string
    {
        if (count($units) === 1) {
            $slug = (string) ($units[0]['slug'] ?? '');
            if ($slug !== '') {
                return '/u/' . rawurlencode($slug);
            }
        }

        if ($units !== []) {
            return '/#onde-pedir';
        }

        return '/';
    }

    /**
     * Resolve o link "Pedir" para unidades ativas no banco.
     */
    public static function hrefForActiveUnits(): string
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query(
            'SELECT slug FROM units WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name ASC'
        );
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return self::hrefFromUnits($rows);
    }
}
