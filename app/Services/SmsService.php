<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Logger;

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
            Logger::log('info', 'SMS (log)', ['to' => $toE164, 'message' => $message]);

            return true;
        }

        if ($provider === 'twilio') {
            return self::sendTwilio($cfg, $toE164, $message);
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
}
