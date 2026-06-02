<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * IP do cliente com suporte opcional a proxy confiável (TRUSTED_PROXIES).
 */
final class ClientIp
{
    public static function trustsProxies(): bool
    {
        return self::trustedProxies() !== [];
    }

    public static function get(): string
    {
        $remote = substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
        $trusted = self::trustedProxies();
        if ($trusted === [] || !self::isTrustedProxy($remote, $trusted)) {
            return $remote;
        }

        $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if (!is_string($xff) || $xff === '') {
            return $remote;
        }

        $parts = array_map('trim', explode(',', $xff));
        $client = substr($parts[0] ?? $remote, 0, 45);

        return filter_var($client, FILTER_VALIDATE_IP) ? $client : $remote;
    }

    /**
     * @return list<string>
     */
    private static function trustedProxies(): array
    {
        $raw = trim((string) Env::get('TRUSTED_PROXIES', ''));
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    /**
     * @param list<string> $trusted
     */
    private static function isTrustedProxy(string $remote, array $trusted): bool
    {
        foreach ($trusted as $proxy) {
            if ($proxy === $remote) {
                return true;
            }
            if (str_contains($proxy, '/') && self::ipInCidr($remote, $proxy)) {
                return true;
            }
        }

        return false;
    }

    private static function ipInCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return false;
        }
        [$subnet, $mask] = explode('/', $cidr, 2);
        $mask = (int) $mask;
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        if ($ipLong === false || $subnetLong === false || $mask < 0 || $mask > 32) {
            return false;
        }
        $maskLong = -1 << (32 - $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}
