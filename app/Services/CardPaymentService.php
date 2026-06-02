<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;
use App\Helpers\Logger;

/**
 * Checkout com cartão via Mercado Pago (Preference / Checkout Pro).
 */
final class CardPaymentService
{
    /**
     * @return array{checkout_url:string,external_id:string}
     */
    public static function createCheckout(int $unitId, int $paymentId, int $orderId, float $amount, string $description): array
    {
        $config = UnitPaymentConfig::forUnit($unitId);
        if (!UnitPaymentConfig::supportsCard($config)) {
            throw new \RuntimeException('Cartão online não está configurado para esta unidade.');
        }

        $token = (string) $config['mp_access_token'];
        $appUrl = rtrim((string) Env::get('APP_URL', 'http://localhost:8080'), '/');
        $reference = UnitPaymentConfig::paymentReference($paymentId);

        $payload = [
            'items' => [
                [
                    'title' => mb_substr($description, 0, 256),
                    'quantity' => 1,
                    'unit_price' => round($amount, 2),
                    'currency_id' => 'BRL',
                ],
            ],
            'external_reference' => $reference,
            'back_urls' => [
                'success' => $appUrl . '/cliente/pedido/' . $orderId . '/cartao/retorno?status=approved',
                'failure' => $appUrl . '/cliente/pedido/' . $orderId . '/cartao/retorno?status=failure',
                'pending' => $appUrl . '/cliente/pedido/' . $orderId . '/cartao/retorno?status=pending',
            ],
            'auto_return' => 'approved',
            'notification_url' => $appUrl . '/webhooks/payment',
            'payment_methods' => [
                'excluded_payment_types' => [
                    ['id' => 'ticket'],
                    ['id' => 'atm'],
                    ['id' => 'bank_transfer'],
                ],
                'excluded_payment_methods' => [
                    ['id' => 'pix'],
                ],
            ],
        ];

        $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
        if ($ch === false) {
            throw new \RuntimeException('Falha ao iniciar checkout de cartão.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            Logger::log('error', 'MP preference falhou', ['http' => $code, 'body' => (string) $response]);
            throw new \RuntimeException('Não foi possível abrir o pagamento com cartão.');
        }

        $data = json_decode((string) $response, true);
        $preferenceId = (string) ($data['id'] ?? '');
        $initPoint = (string) ($data['init_point'] ?? $data['sandbox_init_point'] ?? '');
        if ($preferenceId === '' || $initPoint === '') {
            throw new \RuntimeException('Resposta inválida do Mercado Pago.');
        }

        return [
            'checkout_url' => $initPoint,
            'external_id' => $preferenceId,
        ];
    }

    /**
     * Consulta pagamento MP e confirma se aprovado (retorno do checkout ou cron).
     */
    public static function syncByPaymentId(int $paymentId, int $unitId): bool
    {
        $config = UnitPaymentConfig::forUnit($unitId);
        $token = (string) ($config['mp_access_token'] ?? '');
        if ($token === '') {
            return false;
        }

        $reference = UnitPaymentConfig::paymentReference($paymentId);
        $searchUrl = 'https://api.mercadopago.com/v1/payments/search?external_reference='
            . rawurlencode($reference) . '&sort=date_created&criteria=desc';

        $ch = curl_init($searchUrl);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
        ]);
        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            return false;
        }

        $data = json_decode((string) $response, true);
        $results = $data['results'] ?? [];
        if (!is_array($results)) {
            return false;
        }

        foreach ($results as $payment) {
            if (!is_array($payment)) {
                continue;
            }
            if ((string) ($payment['status'] ?? '') !== 'approved') {
                continue;
            }
            $mpId = (string) ($payment['id'] ?? '');
            if ($mpId !== '') {
                self::updateCardExternalId($paymentId, $mpId);
            }
            $result = PaymentConfirmationService::confirmByPaymentId($paymentId, $payment);

            return ($result['ok'] ?? false) === true;
        }

        return false;
    }

    private static function updateCardExternalId(int $paymentId, string $mpPaymentId): void
    {
        $pdo = \App\Database::pdo();
        $pdo->prepare(
            'UPDATE card_transactions SET external_id = :ext, updated_at = NOW() WHERE payment_id = :pid'
        )->execute(['ext' => $mpPaymentId, 'pid' => $paymentId]);
    }
}
