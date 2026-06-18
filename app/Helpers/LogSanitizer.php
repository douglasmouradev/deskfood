<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Redação de dados sensíveis antes de gravar em log.
 */
final class LogSanitizer
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'password', 'secret', 'token', 'authorization', 'api_key', 'api_secret',
        'access_token', 'mp_access_token', 'body', 'webhook_payload',
    ];

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public static function context(array $context): array
    {
        $out = [];
        foreach ($context as $key => $value) {
            $lk = strtolower((string) $key);
            if (self::isSensitiveKey($lk)) {
                $out[$key] = '[redacted]';
                continue;
            }
            if (is_string($value)) {
                $out[$key] = self::scrubString($value);
                continue;
            }
            if (is_array($value)) {
                $out[$key] = self::context($value);
                continue;
            }
            $out[$key] = $value;
        }

        return $out;
    }

    public static function scrubString(string $value): string
    {
        if (strlen($value) > 2000) {
            $value = substr($value, 0, 2000) . '…';
        }

        return (string) preg_replace('/\b\d{6}\b/', '******', $value);
    }

    private static function isSensitiveKey(string $key): bool
    {
        foreach (self::SENSITIVE_KEYS as $needle) {
            if (str_contains($key, $needle)) {
                return true;
            }
        }

        return false;
    }
}
