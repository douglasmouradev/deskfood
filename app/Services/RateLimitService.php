<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\ClientIp;
use App\Helpers\Env;

/**
 * Rate limiting genérico por IP + canal (reutiliza login_attempts).
 */
final class RateLimitService
{
    /**
     * Verifica se o limite foi excedido.
     */
    public static function isLimited(string $channel, string $identifier, ?int $max = null, ?int $windowSeconds = null): bool
    {
        $max = $max ?? max(5, (int) Env::get('RATE_LIMIT_MAX', '30'));
        $window = $windowSeconds ?? max(60, (int) Env::get('RATE_LIMIT_WINDOW', '3600'));
        $since = (new \DateTimeImmutable('-' . $window . ' seconds'))->format('Y-m-d H:i:s');
        $ip = self::clientIp();
        $id = strtolower(trim($identifier)) ?: '_';

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE ip_address = :ip AND channel = :ch AND identifier = :id AND created_at >= :since'
        );
        $stmt->execute(['ip' => $ip, 'ch' => $channel, 'id' => $id, 'since' => $since]);

        return (int) $stmt->fetchColumn() >= $max;
    }

    /**
     * Registra um hit no limite.
     */
    public static function hit(string $channel, string $identifier): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO login_attempts (ip_address, channel, identifier, created_at)
             VALUES (:ip,:ch,:id,NOW())'
        );
        $stmt->execute([
            'ip' => self::clientIp(),
            'ch' => $channel,
            'id' => strtolower(trim($identifier)) ?: '_',
        ]);
    }

    public static function clientIp(): string
    {
        return ClientIp::get();
    }
}
