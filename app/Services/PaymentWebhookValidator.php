<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;
use App\Helpers\Logger;

/**
 * Valida valor recebido em webhooks de pagamento antes de confirmar pedido.
 */
final class PaymentWebhookValidator
{
    private const TOLERANCE = 0.02;

    /**
     * @param array<string, mixed> $payload
     */
    public static function amountMatchesOrderTotal(float $orderTotal, array $payload): bool
    {
        $received = self::extractAmount($payload);
        if ($received === null) {
            if (Env::get('APP_ENV', 'production') !== 'production') {
                return true;
            }
            Logger::log('warning', 'Webhook sem valor em produção', []);

            return false;
        }

        return abs($received - round($orderTotal, 2)) <= self::TOLERANCE;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function extractAmount(array $payload): ?float
    {
        if (isset($payload['transaction_amount']) && is_numeric($payload['transaction_amount'])) {
            return (float) $payload['transaction_amount'];
        }

        if (isset($payload['valor']) && is_numeric($payload['valor'])) {
            return (float) $payload['valor'];
        }

        if (isset($payload['pix']) && is_array($payload['pix'])) {
            foreach ($payload['pix'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (isset($item['valor']) && is_numeric($item['valor'])) {
                    return (float) $item['valor'];
                }
            }
        }

        return null;
    }
}
