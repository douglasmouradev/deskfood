<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;
use RuntimeException;

/**
 * Criptografia simétrica AES-256-GCM para dados sensíveis (ex.: CPF).
 *
 * Utiliza chave derivada de `APP_SECRET` via SHA-256 e concatena IV, tag
 * e texto cifrado em uma string Base64 única para persistência em banco.
 */
final class CryptoService
{
    /**
     * Cifra texto plano retornando payload Base64 seguro para armazenar.
     *
     * @throws RuntimeException Quando a extensão OpenSSL não estiver disponível
     */
    public static function encrypt(string $plain): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            throw new RuntimeException('Falha ao cifrar dado sensível.');
        }

        return base64_encode($iv . $tag . $cipher);
    }

    /**
     * Decifra payload previamente gerado por `encrypt`.
     *
     * @throws RuntimeException Quando o formato ou a autenticação falharem
     */
    public static function decrypt(string $payload): string
    {
        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < 28) {
            throw new RuntimeException('Payload inválido.');
        }

        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new RuntimeException('Falha ao decifrar dado sensível.');
        }

        return $plain;
    }

    /**
     * Deriva chave binária de 32 bytes a partir do segredo da aplicação.
     */
    private static function key(): string
    {
        $secret = (string) Env::get('APP_SECRET', 'change-me');

        return hash('sha256', $secret, true);
    }
}
