<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\Env;
use PDO;

/**
 * Configuração de pagamento por unidade com fallback para variáveis globais (.env).
 */
final class UnitPaymentConfig
{
    /** @var array<int, array<string, mixed>> */
    private static array $cache = [];

    /**
     * @return array{
     *   unit_id:int,
     *   provider:string,
     *   pix_enabled:bool,
     *   card_enabled:bool,
     *   pix_key:string,
     *   mp_access_token:string,
     *   mp_public_key:string,
     *   efi_client_id:string,
     *   efi_client_secret:string,
     *   efi_sandbox:bool
     * }
     */
    public static function forUnit(int $unitId): array
    {
        if (isset(self::$cache[$unitId])) {
            return self::$cache[$unitId];
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT id, payment_provider, payment_pix_enabled, payment_card_enabled,
                    pix_key, mp_access_token, mp_public_key,
                    efi_client_id, efi_client_secret, efi_sandbox
             FROM units WHERE id = :id AND deleted_at IS NULL LIMIT 1'
        );
        $st->execute(['id' => $unitId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new \RuntimeException('Unidade não encontrada.');
        }

        $provider = trim((string) ($row['payment_provider'] ?? ''));
        if ($provider === '') {
            $provider = strtolower(trim((string) Env::get('PIX_PROVIDER', 'mock')));
        }

        $efiSandbox = $row['efi_sandbox'];
        if ($efiSandbox === null || $efiSandbox === '') {
            $efiSandbox = Env::get('PIX_SANDBOX', '0') === '1';
        } else {
            $efiSandbox = (bool) (int) $efiSandbox;
        }

        $config = [
            'unit_id' => $unitId,
            'provider' => $provider,
            'pix_enabled' => (bool) (int) ($row['payment_pix_enabled'] ?? 1),
            'card_enabled' => (bool) (int) ($row['payment_card_enabled'] ?? 0),
            'pix_key' => trim((string) ($row['pix_key'] ?? '')) ?: trim((string) Env::get('PIX_PIX_KEY', '')),
            'mp_access_token' => SecretVault::open($row['mp_access_token'] ?? null) ?: trim((string) Env::get('PIX_CLIENT_SECRET', '')),
            'mp_public_key' => trim((string) ($row['mp_public_key'] ?? '')) ?: trim((string) Env::get('PIX_MP_PUBLIC_KEY', '')),
            'efi_client_id' => trim((string) ($row['efi_client_id'] ?? '')) ?: trim((string) Env::get('PIX_CLIENT_ID', '')),
            'efi_client_secret' => SecretVault::open($row['efi_client_secret'] ?? null) ?: trim((string) Env::get('PIX_CLIENT_SECRET', '')),
            'efi_sandbox' => $efiSandbox,
        ];

        self::$cache[$unitId] = $config;

        return $config;
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function supportsPix(array $config): bool
    {
        if (!($config['pix_enabled'] ?? false)) {
            return false;
        }

        $provider = (string) ($config['provider'] ?? 'mock');
        if ($provider === 'mock') {
            return true;
        }
        if ($provider === 'mercadopago') {
            return ($config['mp_access_token'] ?? '') !== '';
        }
        if ($provider === 'efipay' || $provider === 'efi') {
            return ($config['efi_client_id'] ?? '') !== ''
                && ($config['efi_client_secret'] ?? '') !== ''
                && ($config['pix_key'] ?? '') !== '';
        }

        return false;
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function supportsCard(array $config): bool
    {
        if (!($config['card_enabled'] ?? false)) {
            return false;
        }

        return ($config['provider'] ?? '') === 'mercadopago'
            && ($config['mp_access_token'] ?? '') !== '';
    }

    public static function paymentReference(int $paymentId): string
    {
        return 'deskfood-payment-' . $paymentId;
    }

    public static function parsePaymentReference(string $reference): ?int
    {
        if (preg_match('/deskfood-payment-(\d+)/', $reference, $m)) {
            return (int) $m[1];
        }

        return null;
    }
}
