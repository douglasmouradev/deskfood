<?php

declare(strict_types=1);

namespace App\Services;

/**
 * TOTP (RFC 6238) para autenticação em dois fatores.
 */
final class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $length = 16): string
    {
        $bytes = random_bytes($length);
        $out = '';
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        foreach (str_split($bits, 5) as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $out .= self::BASE32_ALPHABET[bindec($chunk)];
        }

        return substr($out, 0, 32);
    }

    public static function otpAuthUri(string $secret, string $email, string $issuer): string
    {
        $label = rawurlencode($issuer . ':' . $email);
        $issuerEnc = rawurlencode($issuer);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuerEnc}&digits=6&period=30";
    }

    public static function verify(string $secretBase32, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D+/', '', $code) ?? '';
        if (strlen($code) !== 6) {
            return false;
        }

        $key = self::base32Decode($secretBase32);
        if ($key === '') {
            return false;
        }

        $timeSlice = (int) floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::hotp($key, $timeSlice + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    public static function currentCode(string $secretBase32): string
    {
        $key = self::base32Decode($secretBase32);

        return self::hotp($key, (int) floor(time() / 30));
    }

    private static function hotp(string $key, int $counter): string
    {
        $binCounter = pack('N*', 0, $counter);
        $hash = hash_hmac('sha1', $binCounter, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $value = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $input): string
    {
        $input = strtoupper(preg_replace('/\s+/', '', $input) ?? '');
        $bits = '';
        $len = strlen($input);
        for ($i = 0; $i < $len; $i++) {
            $pos = strpos(self::BASE32_ALPHABET, $input[$i]);
            if ($pos === false) {
                continue;
            }
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) < 8) {
                break;
            }
            $out .= chr(bindec($chunk));
        }

        return $out;
    }
}
