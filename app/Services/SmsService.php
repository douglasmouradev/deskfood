<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;
use App\Helpers\Logger;
use App\Helpers\LogSanitizer;

/**
 * Integração com provedores de SMS para OTP e notificações transacionais.
 *
 * Suporta `twilio` (HTTP básico via cURL) e modo `log` para desenvolvimento.
 */
final class SmsService
{
    /**
     * Envia SMS de texto curto para o número informado em E.164.
     */
    public static function send(string $toE164, string $message): bool
    {
        $cfg = require dirname(__DIR__, 2) . '/config/sms.php';
        $provider = (string) ($cfg['provider'] ?? 'log');

        if ($provider === 'log') {
            $logged = LogSanitizer::scrubString($message);
            Logger::log('info', 'SMS (log)', ['to' => $toE164, 'message' => $logged]);

            return true;
        }

        if ($provider === 'twilio') {
            return self::sendTwilio($cfg, $toE164, $message);
        }

        if ($provider === 'zenvia') {
            return self::sendZenvia($cfg, $toE164, $message);
        }

        Logger::log('warning', 'SMS provider desconhecido', ['provider' => $provider]);

        return false;
    }

    /**
     * Chamada HTTP à API REST do Twilio usando Account SID + Auth Token.
     *
     * @param array<string, mixed> $cfg Configuração carregada de `config/sms.php`
     */
    private static function sendTwilio(array $cfg, string $to, string $message): bool
    {
        $sid = (string) ($cfg['api_key'] ?? '');
        $token = (string) ($cfg['api_secret'] ?? '');
        $from = (string) ($cfg['from_number'] ?? '');
        if ($sid === '' || $token === '' || $from === '') {
            Logger::log('error', 'Twilio sem credenciais completas');

            return false;
        }

        $url = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', $sid);
        $body = http_build_query([
            'To' => $to,
            'From' => $from,
            'Body' => $message,
        ]);

        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_USERPWD => $sid . ':' . $token,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return true;
        }

        Logger::log('error', 'Twilio falhou', ['http' => $code, 'body' => (string) $response]);

        return false;
    }

    /**
     * @param array<string, mixed> $cfg
     */
    private static function sendZenvia(array $cfg, string $to, string $message): bool
    {
        $token = (string) ($cfg['api_key'] ?? '');
        $from = (string) ($cfg['sender'] ?? 'DeskFood');
        if ($token === '') {
            Logger::log('error', 'Zenvia sem API key');

            return false;
        }

        $payload = [
            'from' => $from,
            'to' => $to,
            'contents' => [['type' => 'text', 'text' => $message]],
        ];

        $ch = curl_init('https://api.zenvia.com/v2/channels/sms/messages');
        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'X-API-TOKEN: ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return true;
        }

        Logger::log('error', 'Zenvia falhou', ['http' => $code, 'body' => (string) $response]);

        return false;
    }
}
