<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Reporta erros críticos para webhook (Sentry-compatible ingest ou Slack).
 */
final class ErrorReporter
{
    /**
     * @param array<string, mixed> $context
     */
    public static function capture(string $message, array $context = []): void
    {
        $url = trim((string) Env::get('ERROR_WEBHOOK_URL', ''));
        if ($url === '') {
            return;
        }

        $body = json_encode([
            'text' => $message,
            'context' => LogSanitizer::context($context),
            'app' => Env::get('APP_NAME', 'Desk Food'),
            'env' => Env::get('APP_ENV', 'production'),
            'request_id' => defined('REQUEST_ID') ? REQUEST_ID : null,
        ], JSON_UNESCAPED_UNICODE);

        if ($body === false) {
            return;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
