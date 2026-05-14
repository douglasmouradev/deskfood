<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PixService;

/**
 * Webhooks de gateways externos (PIX).
 */
final class WebhookController extends Controller
{
    /**
     * Recebe confirmação de pagamento PIX (JSON ou form).
     */
    public function pix(): void
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST;
        }

        $result = PixService::handleWebhook($data);
        $http = (int) ($result['http'] ?? 500);
        $body = [
            'ok' => (bool) ($result['ok'] ?? false),
            'duplicate' => (bool) ($result['duplicate'] ?? false),
            'message' => (string) ($result['message'] ?? ''),
        ];
        $this->json($body, $http);
    }
}
