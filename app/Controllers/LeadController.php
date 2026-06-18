<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\ClientIp;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\EmailService;
use App\Services\RateLimitService;

/**
 * Captura de leads comerciais a partir da landing.
 */
final class LeadController extends Controller
{
    public function submit(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Sessão expirada. Tente novamente.';
            Redirect::to('/landing#contato');
        }

        $honeypot = trim((string) filter_input(INPUT_POST, 'website', FILTER_UNSAFE_RAW));
        if ($honeypot !== '') {
            Redirect::to('/landing#contato');
        }

        $name = trim((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW));
        $email = trim((string) filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW));
        $phone = trim((string) (filter_input(INPUT_POST, 'phone', FILTER_UNSAFE_RAW) ?: ''));
        $company = trim((string) (filter_input(INPUT_POST, 'company', FILTER_UNSAFE_RAW) ?: ''));
        $message = trim((string) (filter_input(INPUT_POST, 'message', FILTER_UNSAFE_RAW) ?: ''));

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Informe nome e e-mail válidos.';
            Redirect::to('/landing#contato');
        }

        if (RateLimitService::isLimited('lead_form', $email, 5, 3600)) {
            $_SESSION['flash_error'] = 'Muitas mensagens enviadas. Tente mais tarde.';
            Redirect::to('/landing#contato');
        }
        $clientIp = ClientIp::get();
        if (RateLimitService::isLimited('lead_form_ip', $clientIp, 20, 3600)) {
            $_SESSION['flash_error'] = 'Muitas mensagens deste dispositivo. Tente mais tarde.';
            Redirect::to('/landing#contato');
        }
        RateLimitService::hit('lead_form', $email);
        RateLimitService::hit('lead_form_ip', $clientIp);

        $pdo = Database::pdo();
        $pdo->prepare(
            'INSERT INTO leads (name, email, phone, company, message, source, ip_address, created_at)
             VALUES (:n,:e,:p,:c,:m,:s,:ip,NOW())'
        )->execute([
            'n' => $name,
            'e' => $email,
            'p' => $phone !== '' ? $phone : null,
            'c' => $company !== '' ? $company : null,
            'm' => $message !== '' ? $message : null,
            's' => 'landing',
            'ip' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
        ]);

        $config = require BASE_PATH . '/config/app.php';
        $commercial = (string) ($config['commercial_email'] ?? '');
        if ($commercial !== '') {
            $body = '<p><strong>' . htmlspecialchars($name) . '</strong> (' . htmlspecialchars($email) . ')</p>';
            if ($phone !== '') {
                $body .= '<p>Telefone: ' . htmlspecialchars($phone) . '</p>';
            }
            if ($company !== '') {
                $body .= '<p>Empresa: ' . htmlspecialchars($company) . '</p>';
            }
            if ($message !== '') {
                $body .= '<p>' . nl2br(htmlspecialchars($message)) . '</p>';
            }
            EmailService::send($commercial, 'Novo lead Desk Food — ' . $name, $body);
        }

        $_SESSION['flash_success'] = 'Mensagem enviada! Entraremos em contato em breve.';
        Redirect::to('/landing#contato');
    }
}
