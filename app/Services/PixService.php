<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\Env;
use App\Helpers\Logger;
use PDO;
use Ramsey\Uuid\Uuid;

/**
 * Cobrança PIX por unidade (mock, Efi Pay, Mercado Pago).
 */
final class PixService
{
    /**
     * @return array{copy_paste:string,qr_payload:string,expires_at:string,external_id:string}
     */
    public static function createForPayment(int $paymentId, float $amount, int $unitId): array
    {
        $config = UnitPaymentConfig::forUnit($unitId);
        $provider = (string) $config['provider'];

        if ($provider === 'mercadopago') {
            return self::createMercadoPago($paymentId, $amount, $config);
        }

        if ($provider === 'efipay' || $provider === 'efi') {
            return self::createEfiPay($paymentId, $amount, $config);
        }

        if ($provider !== 'mock') {
            Logger::log('warning', 'PIX provider não implementado; usando mock', ['provider' => $provider]);
        }

        $txid = 'MOCK-' . Uuid::uuid4()->toString();
        $copy = self::buildMockEmv($txid, $amount);

        return [
            'copy_paste' => $copy,
            'qr_payload' => $copy,
            'expires_at' => (new \DateTimeImmutable('+30 minutes'))->format('Y-m-d H:i:s'),
            'external_id' => $txid,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{ok:bool,http:int,duplicate?:bool,message?:string,processed?:int}
     */
    public static function handleWebhook(array $payload, string $rawBody = ''): array
    {
        return PaymentWebhookService::handle($payload, $rawBody);
    }

    public static function trySyncOrderPixStatus(int $orderId): bool
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT o.unit_id, pt.external_id, pt.status AS pt_status, p.status AS pay_status, p.id AS payment_id
             FROM orders o
             INNER JOIN payments p ON p.order_id = o.id AND p.type = "pix"
             INNER JOIN pix_transactions pt ON pt.payment_id = p.id
             WHERE o.id = :oid AND o.payment_status = "pendente" AND pt.status = "criado"
             LIMIT 1'
        );
        $st->execute(['oid' => $orderId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return false;
        }

        $config = UnitPaymentConfig::forUnit((int) $row['unit_id']);
        if (($config['provider'] ?? 'mock') === 'mock') {
            return false;
        }

        $externalId = (string) ($row['external_id'] ?? '');
        $paymentId = (int) $row['payment_id'];
        if ($externalId === '') {
            return false;
        }

        if (!self::isPaidOnGateway($externalId, $paymentId, (int) $row['unit_id'])) {
            return false;
        }

        $result = PaymentConfirmationService::confirmByExternalId($externalId, ['source' => 'sync']);

        return ($result['ok'] ?? false) === true;
    }

    private static function isPaidOnGateway(string $externalId, int $paymentId, int $unitId): bool
    {
        $config = UnitPaymentConfig::forUnit($unitId);
        $provider = (string) $config['provider'];

        if ($provider === 'mercadopago') {
            $data = self::fetchMercadoPagoPayment($externalId, $config);
            return is_array($data) && (string) ($data['status'] ?? '') === 'approved';
        }

        if ($provider === 'efipay' || $provider === 'efi') {
            $data = self::fetchEfiCob($externalId, $config);
            return is_array($data) && (string) ($data['status'] ?? '') === 'CONCLUIDA';
        }

        return false;
    }

    /**
     * @param array<string, mixed> $config
     * @return array{copy_paste:string,qr_payload:string,expires_at:string,external_id:string}
     */
    private static function createMercadoPago(int $paymentId, float $amount, array $config): array
    {
        $token = (string) $config['mp_access_token'];
        if ($token === '') {
            throw new \RuntimeException('Access Token Mercado Pago não configurado para esta unidade.');
        }

        $reference = UnitPaymentConfig::paymentReference($paymentId);
        $payload = [
            'transaction_amount' => round($amount, 2),
            'description' => 'Pedido Desk Food #' . $paymentId,
            'payment_method_id' => 'pix',
            'external_reference' => $reference,
            'payer' => ['email' => 'cliente@deskfood.local'],
        ];

        $ch = curl_init('https://api.mercadopago.com/v1/payments');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'X-Idempotency-Key: ' . $reference,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            Logger::log('error', 'Mercado Pago PIX falhou', ['http' => $code, 'body' => (string) $response]);
            throw new \RuntimeException('Não foi possível gerar cobrança PIX.');
        }

        $data = json_decode((string) $response, true);
        $poi = $data['point_of_interaction']['transaction_data'] ?? [];

        return [
            'copy_paste' => (string) ($poi['qr_code'] ?? ''),
            'qr_payload' => (string) ($poi['qr_code_base64'] ?? $poi['qr_code'] ?? ''),
            'expires_at' => (new \DateTimeImmutable('+30 minutes'))->format('Y-m-d H:i:s'),
            'external_id' => (string) ($data['id'] ?? $reference),
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @return array{copy_paste:string,qr_payload:string,expires_at:string,external_id:string}
     */
    private static function createEfiPay(int $paymentId, float $amount, array $config): array
    {
        if (($config['pix_key'] ?? '') === '' || ($config['efi_client_id'] ?? '') === '' || ($config['efi_client_secret'] ?? '') === '') {
            throw new \RuntimeException('Configure chave PIX e credenciais Efi para esta unidade.');
        }

        $accessToken = self::efiAccessToken($config);
        if ($accessToken === null) {
            throw new \RuntimeException('Falha na autenticação Efi Pay.');
        }

        $base = self::efiBaseUrl($config);
        $txid = substr(preg_replace('/[^a-zA-Z0-9]/', '', 'DF' . $paymentId . bin2hex(random_bytes(8))), 0, 35);
        $cobPayload = [
            'calendario' => ['expiracao' => 1800],
            'valor' => ['original' => number_format(round($amount, 2), 2, '.', '')],
            'chave' => $config['pix_key'],
            'solicitacaoPagador' => 'Pedido Desk Food #' . $paymentId,
        ];

        $ch = curl_init($base . '/v2/cob/' . rawurlencode($txid));
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($cobPayload, JSON_THROW_ON_ERROR),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        self::applyEfiTls($ch);

        $cobRes = curl_exec($ch);
        $cobCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $cob = json_decode((string) $cobRes, true);
        if ($cobCode < 200 || $cobCode >= 300) {
            Logger::log('error', 'Efi cob falhou', ['http' => $cobCode, 'body' => (string) $cobRes]);
            throw new \RuntimeException('Não foi possível criar cobrança PIX Efi.');
        }

        $copy = (string) ($cob['pixCopiaECola'] ?? '');
        if ($copy === '' && isset($cob['loc']['id'])) {
            $locId = (int) $cob['loc']['id'];
            $ch2 = curl_init($base . '/v2/loc/' . $locId . '/qrcode');
            curl_setopt_array($ch2, [
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
            ]);
            self::applyEfiTls($ch2);
            $qrRes = curl_exec($ch2);
            curl_close($ch2);
            $qrData = json_decode((string) $qrRes, true);
            $copy = (string) ($qrData['qrcode'] ?? $qrData['pixCopiaECola'] ?? '');
        }

        return [
            'copy_paste' => $copy,
            'qr_payload' => $copy,
            'expires_at' => (new \DateTimeImmutable('+30 minutes'))->format('Y-m-d H:i:s'),
            'external_id' => (string) ($cob['txid'] ?? $txid),
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>|null
     */
    private static function fetchMercadoPagoPayment(string $paymentId, array $config): ?array
    {
        $token = (string) ($config['mp_access_token'] ?? '');
        if ($token === '') {
            return null;
        }

        $ch = curl_init('https://api.mercadopago.com/v1/payments/' . rawurlencode($paymentId));
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
        ]);
        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            return null;
        }

        $data = json_decode((string) $response, true);

        return is_array($data) ? $data : null;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>|null
     */
    private static function fetchEfiCob(string $txid, array $config): ?array
    {
        $token = self::efiAccessToken($config);
        if ($token === null) {
            return null;
        }

        $base = self::efiBaseUrl($config);
        $ch = curl_init($base . '/v2/cob/' . rawurlencode($txid));
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
        ]);
        self::applyEfiTls($ch);
        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            return null;
        }

        $data = json_decode((string) $response, true);

        return is_array($data) ? $data : null;
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function efiAccessToken(array $config): ?string
    {
        $clientId = (string) ($config['efi_client_id'] ?? '');
        $clientSecret = (string) ($config['efi_client_secret'] ?? '');
        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        $base = self::efiBaseUrl($config);
        $auth = base64_encode($clientId . ':' . $clientSecret);
        $ch = curl_init($base . '/oauth/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $auth, 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['grant_type' => 'client_credentials'], JSON_THROW_ON_ERROR),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);
        self::applyEfiTls($ch);

        $authRes = curl_exec($ch);
        $authCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $authData = json_decode((string) $authRes, true);
        $accessToken = (string) ($authData['access_token'] ?? '');
        if ($authCode < 200 || $authCode >= 300 || $accessToken === '') {
            Logger::log('error', 'Efi OAuth falhou', ['http' => $authCode]);

            return null;
        }

        return $accessToken;
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function efiBaseUrl(array $config): string
    {
        return ($config['efi_sandbox'] ?? false)
            ? 'https://pix-h.api.efipay.com.br'
            : 'https://pix.api.efipay.com.br';
    }

    /**
     * @param resource $ch
     */
    private static function applyEfiTls($ch): void
    {
        $cert = trim((string) Env::get('PIX_MTLS_CERT', ''));
        $key = trim((string) Env::get('PIX_MTLS_KEY', ''));
        if ($cert !== '' && is_readable($cert)) {
            curl_setopt($ch, CURLOPT_SSLCERT, $cert);
        }
        if ($key !== '' && is_readable($key)) {
            curl_setopt($ch, CURLOPT_SSLKEY, $key);
        }
    }

    private static function buildMockEmv(string $txid, float $amount): string
    {
        $amountStr = number_format($amount, 2, '.', '');

        return sprintf(
            '00020126580014br.gov.bcb.pix0136%s520400005303986540%s5802BR5913%s62070503***6304',
            $txid,
            $amountStr,
            'DESKFOOD*DEMO'
        );
    }
}
