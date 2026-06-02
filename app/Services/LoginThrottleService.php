<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\ClientIp;
use App\Helpers\Env;
use PDO;

/**
 * Limita tentativas de login por IP + identificador (e-mail) em janela deslizante.
 */
final class LoginThrottleService
{
    /**
     * Verifica se o limite de falhas foi excedido para o canal informado.
     */
    public static function isLockedOut(string $channel, string $email): bool
    {
        $max = max(3, (int) Env::get('LOGIN_RATE_MAX', '8'));
        $window = max(60, (int) Env::get('LOGIN_RATE_WINDOW', '900'));
        $ip = self::clientIp();
        $since = (new \DateTimeImmutable('-' . $window . ' seconds'))->format('Y-m-d H:i:s');

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE ip_address = :ip AND channel = :ch AND identifier = :id AND created_at >= :since'
        );
        $stmt->execute([
            'ip' => $ip,
            'ch' => $channel,
            'id' => strtolower(trim($email)),
            'since' => $since,
        ]);

        return (int) $stmt->fetchColumn() >= $max;
    }

    /**
     * Registra uma tentativa falha de autenticação.
     */
    public static function recordFailure(string $channel, string $email): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO login_attempts (ip_address, channel, identifier, created_at)
             VALUES (:ip,:ch,:id,NOW())'
        );
        $stmt->execute([
            'ip' => self::clientIp(),
            'ch' => $channel,
            'id' => strtolower(trim($email)),
        ]);
    }

    /**
     * Remove tentativas antigas do mesmo IP+e-mail após login bem-sucedido (limpa janela atual).
     */
    public static function clearFor(string $channel, string $email): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'DELETE FROM login_attempts WHERE ip_address = :ip AND channel = :ch AND identifier = :id'
        );
        $stmt->execute([
            'ip' => self::clientIp(),
            'ch' => $channel,
            'id' => strtolower(trim($email)),
        ]);
    }

    /**
     * Obtém IP do cliente respeitando cabeçalho de proxy quando configurado.
     */
    private static function clientIp(): string
    {
        return ClientIp::get();
    }
}
