<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;
use App\Helpers\Logger;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envio de e-mail transacional (PHPMailer). Em local sem SMTP usa modo log.
 */
final class EmailService
{
    public static function send(string $to, string $subject, string $htmlBody, ?string $textBody = null): bool
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $driver = Env::get('MAIL_DRIVER', 'log');
        if ($driver === 'log') {
            Logger::log('info', 'E-mail (log)', ['to' => $to, 'subject' => $subject, 'body' => strip_tags($htmlBody)]);

            return true;
        }

        if ($driver !== 'smtp') {
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = (string) Env::get('MAIL_HOST', 'localhost');
            $mail->Port = (int) Env::get('MAIL_PORT', '587');
            $mail->SMTPAuth = Env::get('MAIL_USERNAME', '') !== '';
            $mail->Username = (string) Env::get('MAIL_USERNAME', '');
            $mail->Password = (string) Env::get('MAIL_PASSWORD', '');
            $enc = Env::get('MAIL_ENCRYPTION', 'tls');
            if ($enc === 'tls' || $enc === 'ssl') {
                $mail->SMTPSecure = $enc;
            }

            $from = (string) Env::get('MAIL_FROM', 'noreply@deskfood.local');
            $fromName = (string) Env::get('MAIL_FROM_NAME', 'Desk Food');
            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?? strip_tags($htmlBody);
            $mail->CharSet = 'UTF-8';
            $mail->send();

            return true;
        } catch (\Throwable $e) {
            Logger::log('error', 'Falha ao enviar e-mail', ['to' => $to, 'e' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * @param array<string, mixed> $order
     * @param array<string, mixed> $unit
     */
    /**
     * Notifica e-mail operacional/comercial sobre novo pedido (cliente usa SMS/rastreio).
     *
     * @param array<string, mixed> $order order_number, total, customer_name?, customer_phone?
     * @param array<string, mixed> $unit
     */
    public static function sendOrderConfirmation(string $to, array $order, array $unit, string $trackUrl): void
    {
        $num = htmlspecialchars((string) ($order['order_number'] ?? ''));
        $total = number_format((float) ($order['total'] ?? 0), 2, ',', '.');
        $unitName = htmlspecialchars((string) ($unit['name'] ?? 'Loja'));
        $track = htmlspecialchars($trackUrl);
        $client = htmlspecialchars((string) ($order['customer_name'] ?? ''));
        $phone = htmlspecialchars((string) ($order['customer_phone'] ?? ''));

        $html = <<<HTML
        <p><strong>Novo pedido #{$num}</strong> — {$unitName}</p>
        <p>Cliente: {$client} · {$phone}</p>
        <p>Total: <strong>R$ {$total}</strong></p>
        <p><a href="{$track}">Abrir rastreio</a> · confira o painel do operador.</p>
        HTML;

        self::send(
            $to,
            "Novo pedido #{$num} — Desk Food",
            $html,
            "Novo pedido #{$num} em {$unitName}. Total R$ {$total}. Rastreio: {$trackUrl}"
        );
    }

    /**
     * Confirmação de pedido para o cliente (quando cadastrou e-mail no perfil LGPD).
     *
     * @param array<string, mixed> $order order_number, total
     * @param array<string, mixed> $unit
     */
    public static function sendCustomerOrderCreated(string $to, array $order, array $unit, string $trackUrl): void
    {
        $num = htmlspecialchars((string) ($order['order_number'] ?? ''));
        $total = number_format((float) ($order['total'] ?? 0), 2, ',', '.');
        $unitName = htmlspecialchars((string) ($unit['name'] ?? 'Loja'));
        $track = htmlspecialchars($trackUrl);

        $html = <<<HTML
        <p>Recebemos seu pedido <strong>#{$num}</strong> em {$unitName}.</p>
        <p>Total: <strong>R$ {$total}</strong></p>
        <p><a href="{$track}">Acompanhar pedido</a></p>
        HTML;

        self::send(
            $to,
            "Pedido #{$num} confirmado — Desk Food",
            $html,
            "Pedido #{$num} em {$unitName}. Total R$ {$total}. Acompanhe: {$trackUrl}"
        );
    }
}
