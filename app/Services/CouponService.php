<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use PDO;

/**
 * Validação e aplicação de cupons de desconto.
 */
final class CouponService
{
    /**
     * @return array{id:int,code:string,discount:float}
     */
    public static function resolve(string $code, int $unitId, float $subtotal): array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            throw new \InvalidArgumentException('Informe o cupom.');
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM coupons
             WHERE code = :c AND is_active = 1
               AND (unit_id IS NULL OR unit_id = :u)
             ORDER BY unit_id DESC
             LIMIT 1'
        );
        $stmt->execute(['c' => $code, 'u' => $unitId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new \RuntimeException('Cupom inválido ou expirado.');
        }

        if ($row['valid_from'] !== null && strtotime((string) $row['valid_from']) > time()) {
            throw new \RuntimeException('Cupom ainda não está válido.');
        }
        if ($row['valid_until'] !== null && strtotime((string) $row['valid_until']) < time()) {
            throw new \RuntimeException('Cupom expirado.');
        }

        $maxUses = $row['max_uses'] !== null ? (int) $row['max_uses'] : null;
        if ($maxUses !== null && (int) $row['uses_count'] >= $maxUses) {
            throw new \RuntimeException('Cupom esgotado.');
        }

        $minSub = (float) $row['min_subtotal'];
        if ($subtotal < $minSub) {
            throw new \RuntimeException(
                'Pedido mínimo para este cupom: R$ ' . number_format($minSub, 2, ',', '.')
            );
        }

        $discount = 0.0;
        if (($row['discount_type'] ?? '') === 'percent') {
            $discount = round($subtotal * ((float) $row['discount_value'] / 100), 2);
        } else {
            $discount = min($subtotal, (float) $row['discount_value']);
        }

        if ($discount <= 0) {
            throw new \RuntimeException('Cupom sem desconto aplicável.');
        }

        return [
            'id' => (int) $row['id'],
            'code' => $code,
            'discount' => $discount,
        ];
    }

    public static function incrementUsage(PDO $pdo, int $couponId): void
    {
        $pdo->prepare('UPDATE coupons SET uses_count = uses_count + 1, updated_at = NOW() WHERE id = :id')
            ->execute(['id' => $couponId]);
    }
}
