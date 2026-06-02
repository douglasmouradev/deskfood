<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Tokens de acesso do motoboy — armazenamento por hash SHA-256.
 */
final class MotoboyTokenService
{
    public static function generate(): string
    {
        return bin2hex(random_bytes(16));
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function matches(string $token, string $storedHash, ?string $legacyPlain = null): bool
    {
        if ($storedHash !== '' && hash_equals($storedHash, self::hash($token))) {
            return true;
        }

        return $legacyPlain !== null && $legacyPlain !== '' && hash_equals($legacyPlain, $token);
    }
}
