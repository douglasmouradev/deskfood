<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Cifra credenciais de gateway antes de persistir no banco.
 */
final class SecretVault
{
    private const PREFIX = 'enc:v1:';

    public static function seal(?string $plain): ?string
    {
        $plain = $plain !== null ? trim($plain) : '';
        if ($plain === '') {
            return null;
        }

        if (str_starts_with($plain, self::PREFIX)) {
            return $plain;
        }

        return self::PREFIX . CryptoService::encrypt($plain);
    }

    public static function open(?string $stored): string
    {
        $stored = trim((string) $stored);
        if ($stored === '') {
            return '';
        }

        if (!str_starts_with($stored, self::PREFIX)) {
            return $stored;
        }

        try {
            return CryptoService::decrypt(substr($stored, strlen(self::PREFIX)));
        } catch (\Throwable) {
            return '';
        }
    }
}
