<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Links wa.me para notificar motoboys e clientes.
 */
final class WhatsAppLinkService
{
    public static function normalizePhoneBr(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            return $digits;
        }

        return '55' . ltrim($digits, '0');
    }

    public static function url(string $phone, string $message): ?string
    {
        $normalized = self::normalizePhoneBr($phone);
        if ($normalized === '') {
            return null;
        }

        return 'https://wa.me/' . $normalized . '?text=' . rawurlencode($message);
    }
}
