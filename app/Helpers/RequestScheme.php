<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Detecta HTTPS real (proxy reverso ou APP_URL).
 */
final class RequestScheme
{
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        $forwarded = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
        if ($forwarded === 'https') {
            return ClientIp::trustsProxies();
        }

        return str_starts_with((string) Env::get('APP_URL', ''), 'https://');
    }
}
