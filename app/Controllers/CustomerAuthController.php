<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\AuthService;

/**
 * Autenticação do cliente via OTP por SMS.
 */
final class CustomerAuthController extends Controller
{
    /**
     * Formulário inicial de cadastro/login com telefone.
     */
    public function showLogin(): void
    {
        $this->view('customer/auth_login', ['csrf' => Csrf::token(), 'error' => $_SESSION['flash_error'] ?? null, 'title' => 'Entrar'], 'auth');
        unset($_SESSION['flash_error']);
    }

    /**
     * Recebe nome + telefone e dispara envio de OTP.
     */
    public function sendOtp(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Sessão expirada. Atualize a página.';
            Redirect::to('/cliente/login');
        }

        $name = filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW) ?: '';
        $phone = filter_input(INPUT_POST, 'phone', FILTER_UNSAFE_RAW) ?: '';
        $consents = [
            'terms' => filter_input(INPUT_POST, 'accept_terms', FILTER_VALIDATE_BOOL) ?? false,
            'privacy' => filter_input(INPUT_POST, 'accept_privacy', FILTER_VALIDATE_BOOL) ?? false,
            'sms' => filter_input(INPUT_POST, 'accept_sms', FILTER_VALIDATE_BOOL) ?? false,
        ];

        $res = AuthService::requestOtp((string) $name, (string) $phone, $consents);
        if (!$res['ok']) {
            $_SESSION['flash_error'] = $res['message'];
            Redirect::to('/cliente/login');
        }

        $_SESSION['flash_ok'] = $res['message'];
        Redirect::to('/cliente/verificar?phone=' . urlencode((string) $phone));
    }

    /**
     * Tela de digitação do código de 6 dígitos recebido por SMS.
     */
    public function showVerify(): void
    {
        $phone = filter_input(INPUT_GET, 'phone', FILTER_UNSAFE_RAW) ?: '';
        $this->view('customer/auth_verify', [
            'csrf' => Csrf::token(),
            'phone' => (string) $phone,
            'ok' => $_SESSION['flash_ok'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null,
            'title' => 'Verificar código',
        ], 'public');
        unset($_SESSION['flash_ok'], $_SESSION['flash_error']);
    }

    /**
     * Valida OTP e abre sessão autenticada do cliente.
     */
    public function verify(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Sessão expirada.';
            Redirect::to('/cliente/login');
        }

        $phone = filter_input(INPUT_POST, 'phone', FILTER_UNSAFE_RAW) ?: '';
        $code = filter_input(INPUT_POST, 'code', FILTER_UNSAFE_RAW) ?: '';
        $cfg = require dirname(__DIR__, 2) . '/config/app.php';

        $res = AuthService::verifyOtp((string) $phone, (string) $code, [
            'terms' => (string) $cfg['terms_version'],
            'privacy' => (string) $cfg['privacy_version'],
            'sms' => (string) $cfg['privacy_version'],
        ]);

        if (!$res['ok']) {
            $_SESSION['flash_error'] = $res['message'];
            Redirect::to('/cliente/verificar?phone=' . urlencode((string) $phone));
        }

        Redirect::to('/cliente/pedidos');
    }

    /**
     * Encerra sessão do cliente.
     */
    public function logout(): void
    {
        AuthService::logoutCustomer();
        Redirect::to('/');
    }
}
