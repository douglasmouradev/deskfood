<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PaymentWebhookService;
use App\Services\PixService;
use App\Services\RateLimitService;

/**
 * Webhooks de gateways externos (PIX e cartão).
 */
final class WebhookController extends Controller
{
    /**
     * Webhook unificado (Mercado Pago, Efi, testes manuais).
     */
    public function payment(): void
    {
        if (RateLimitService::isLimited('webhook_payment', 'global', 120, 60)) {
            $this->json(['ok' => false, 'message' => 'rate limit'], 429);

            return;
        }
        RateLimitService::hit('webhook_payment', 'global');

        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST;
        }

        $fromQuery = \App\Services\PixWebhookNormalizer::mercadoPagoFromQuery($_GET);
        if ($fromQuery !== null) {
            $data = array_merge($data, $fromQuery);
        }

        $result = PaymentWebhookService::handle($data, $raw);
        $this->jsonWebhookResult($result);
    }

    /** @deprecated Use /webhooks/payment — mantido por compatibilidade */
    public function pix(): void
    {
        if (RateLimitService::isLimited('webhook_pix', 'global', 120, 60)) {
            $this->json(['ok' => false, 'message' => 'rate limit'], 429);

            return;
        }
        RateLimitService::hit('webhook_pix', 'global');

        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST;
        }

        $result = PixService::handleWebhook($data, $raw);
        $this->jsonWebhookResult($result);
    }

    /**
     * @param array<string, mixed> $result
     */
    private function jsonWebhookResult(array $result): void
    {
        $http = (int) ($result['http'] ?? 500);
        $this->json([
            'ok' => (bool) ($result['ok'] ?? false),
            'duplicate' => (bool) ($result['duplicate'] ?? false),
            'message' => (string) ($result['message'] ?? ''),
            'processed' => (int) ($result['processed'] ?? 0),
        ], $http);
    }
}
