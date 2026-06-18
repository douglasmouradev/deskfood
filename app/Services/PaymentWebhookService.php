<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;
use App\Helpers\Logger;

/**
 * Processa webhooks de pagamento (PIX Efi/MP, cartão MP).
 */
final class PaymentWebhookService
{
    /**
     * @param array<string, mixed> $payload
     * @return array{ok:bool,http:int,duplicate?:bool,message?:string,processed?:int}
     */
    public static function handle(array $payload, string $rawBody = ''): array
    {
        if (!self::assertAuthorized($payload, $rawBody)) {
            AuditLogService::record('system', null, 'webhook.payment.unauthorized', null, null, []);

            return ['ok' => false, 'http' => 401, 'message' => 'Webhook não autorizado.'];
        }

        if (PixWebhookNormalizer::looksLikeMercadoPagoNotification($payload)) {
            return self::handleMercadoPago($payload);
        }

        $externalIds = PixWebhookNormalizer::extractExternalIds($payload);
        if ($externalIds === []) {
            $ref = (string) ($payload['external_reference'] ?? '');
            $paymentId = UnitPaymentConfig::parsePaymentReference($ref);
            if ($paymentId !== null) {
                $result = PaymentConfirmationService::confirmByPaymentId($paymentId, $payload);

                return $result;
            }

            return ['ok' => false, 'http' => 400, 'message' => 'Payload sem identificador.'];
        }

        $processed = 0;
        $last = ['ok' => false, 'http' => 404, 'message' => 'Transação não encontrada.'];
        foreach ($externalIds as $externalId) {
            $paymentId = UnitPaymentConfig::parsePaymentReference($externalId);
            if ($paymentId !== null) {
                $last = PaymentConfirmationService::confirmByPaymentId($paymentId, $payload);
            } else {
                $last = PaymentConfirmationService::confirmByExternalId($externalId, $payload);
            }
            if (($last['ok'] ?? false) === true) {
                ++$processed;
            }
        }

        if ($processed > 0) {
            $last['processed'] = $processed;
        }

        return $last;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{ok:bool,http:int,duplicate?:bool,message?:string}
     */
    private static function handleMercadoPago(array $payload): array
    {
        $data = $payload['data'] ?? null;
        $mpPaymentId = is_array($data) ? (string) ($data['id'] ?? '') : '';
        if ($mpPaymentId === '') {
            return ['ok' => true, 'http' => 200, 'message' => 'Notificação MP sem id.'];
        }

        $token = self::resolveMercadoPagoToken($mpPaymentId);
        if ($token === '') {
            return ['ok' => false, 'http' => 500, 'message' => 'Token MP não configurado.'];
        }

        $ch = curl_init('https://api.mercadopago.com/v1/payments/' . rawurlencode($mpPaymentId));
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
        ]);
        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            Logger::log('warning', 'MP payment fetch failed', ['id' => $mpPaymentId, 'http' => $code]);

            return ['ok' => false, 'http' => 502, 'message' => 'Falha ao consultar pagamento MP.'];
        }

        $payment = json_decode((string) $response, true);
        if (!is_array($payment) || (string) ($payment['status'] ?? '') !== 'approved') {
            return ['ok' => true, 'http' => 200, 'message' => 'Pagamento MP ainda não aprovado.'];
        }

        $ref = (string) ($payment['external_reference'] ?? '');
        $paymentId = UnitPaymentConfig::parsePaymentReference($ref);
        if ($paymentId === null) {
            $paymentId = self::paymentIdByMpPaymentId($mpPaymentId);
        }

        if ($paymentId === null) {
            return PaymentConfirmationService::confirmByExternalId($mpPaymentId, $payment);
        }

        self::updateExternalIds($paymentId, $mpPaymentId);

        return PaymentConfirmationService::confirmByPaymentId($paymentId, $payment);
    }

    private static function resolveMercadoPagoToken(string $mpPaymentId): string
    {
        $paymentId = self::paymentIdByMpPaymentId($mpPaymentId);
        if ($paymentId !== null) {
            $pdo = \App\Database::pdo();
            $st = $pdo->prepare(
                'SELECT o.unit_id FROM payments p INNER JOIN orders o ON o.id = p.order_id WHERE p.id = :id LIMIT 1'
            );
            $st->execute(['id' => $paymentId]);
            $unitId = $st->fetchColumn();
            if ($unitId !== false) {
                $config = UnitPaymentConfig::forUnit((int) $unitId);
                $token = (string) ($config['mp_access_token'] ?? '');
                if ($token !== '') {
                    return $token;
                }
            }
        }

        return trim((string) Env::get('PIX_CLIENT_SECRET', ''));
    }

    private static function paymentIdByMpPaymentId(string $mpPaymentId): ?int
    {
        $pdo = \App\Database::pdo();
        foreach (['pix_transactions', 'card_transactions'] as $table) {
            $st = $pdo->prepare("SELECT payment_id FROM {$table} WHERE external_id = :e LIMIT 1");
            $st->execute(['e' => $mpPaymentId]);
            $id = $st->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }
        }

        return null;
    }

    private static function updateExternalIds(int $paymentId, string $mpPaymentId): void
    {
        $pdo = \App\Database::pdo();
        $pdo->prepare(
            'UPDATE pix_transactions SET external_id = :e, updated_at = NOW() WHERE payment_id = :p AND external_id != :e'
        )->execute(['e' => $mpPaymentId, 'p' => $paymentId]);
        $pdo->prepare(
            'UPDATE card_transactions SET external_id = :e, updated_at = NOW() WHERE payment_id = :p'
        )->execute(['e' => $mpPaymentId, 'p' => $paymentId]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function assertAuthorized(array $payload, string $rawBody): bool
    {
        $secret = trim((string) Env::get('PIX_WEBHOOK_SECRET', ''));
        $env = Env::get('APP_ENV', 'production');

        if ($secret !== '' && self::matchesDeskfoodSecret($secret)) {
            return true;
        }

        if ($rawBody !== '' && self::verifyMercadoPagoSignature($rawBody)) {
            return true;
        }

        if (PixWebhookNormalizer::isEfiPixPayload($payload) && Env::get('PIX_EFI_TRUST_WEBHOOK', '0') === '1') {
            if ($secret !== '' && self::matchesDeskfoodSecret($secret)) {
                return true;
            }
            Logger::log('warning', 'PIX_EFI_TRUST_WEBHOOK ignorado sem segredo Deskfood', []);

            return false;
        }

        if ($env !== 'production' && $secret === '') {
            return true;
        }

        return false;
    }

    private static function matchesDeskfoodSecret(string $secret): bool
    {
        $header = (string) ($_SERVER['HTTP_X_DESKFOOD_WEBHOOK_SECRET'] ?? '');
        if ($header !== '' && hash_equals($secret, $header)) {
            return true;
        }

        $auth = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
        if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return hash_equals($secret, trim($m[1]));
        }

        return false;
    }

    private static function verifyMercadoPagoSignature(string $rawBody): bool
    {
        $mpSecret = trim((string) Env::get('PIX_MP_WEBHOOK_SECRET', ''));
        if ($mpSecret === '' || $rawBody === '') {
            return false;
        }

        $xSignature = (string) ($_SERVER['HTTP_X_SIGNATURE'] ?? '');
        $xRequestId = (string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? '');
        if ($xSignature === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $xSignature) as $segment) {
            $kv = explode('=', trim($segment), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        $ts = (string) ($parts['ts'] ?? '');
        $v1 = (string) ($parts['v1'] ?? '');
        if ($ts === '' || $v1 === '') {
            return false;
        }

        $data = json_decode($rawBody, true);
        $dataId = is_array($data) ? (string) ($data['data']['id'] ?? '') : '';
        $manifest = 'id:' . $dataId . ';request-id:' . $xRequestId . ';ts:' . $ts . ';';
        $expected = hash_hmac('sha256', $manifest, $mpSecret);

        return hash_equals($expected, $v1);
    }
}
