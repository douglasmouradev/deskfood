<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Helpers\SessionHelper;
use App\Services\LoginThrottleService;

/**
 * Autenticação do dono (super admin) por e-mail e senha.
 */
final class AdminAuthController extends Controller
{
    /**
     * Formulário de login administrativo.
     */
    public function showLogin(): void
    {
        $this->view('admin/login', ['csrf' => Csrf::token(), 'error' => $_SESSION['flash_error'] ?? null, 'title' => 'Dono'], 'auth');
        unset($_SESSION['flash_error']);
    }

    /**
     * Valida credenciais e abre sessão somente para `super_admin`.
     */
    public function login(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin/login');
        }

        $email = strtolower(trim((string) filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)));
        $pass = (string) filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
        if ($email === '' || $pass === '') {
            $_SESSION['flash_error'] = 'Preencha e-mail e senha.';
            Redirect::to('/admin/login');
        }

        if (LoginThrottleService::isLockedOut('admin', $email)) {
            $_SESSION['flash_error'] = 'Muitas tentativas. Aguarde alguns minutos.';
            Redirect::to('/admin/login');
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM admins WHERE email = :e AND is_active = 1 AND deleted_at IS NULL LIMIT 1');
        $st->execute(['e' => $email]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if ($row === false || ($row['role'] ?? '') !== 'super_admin' || !password_verify($pass, (string) $row['password_hash'])) {
            LoginThrottleService::recordFailure('admin', $email);
            $_SESSION['flash_error'] = 'Credenciais inválidas.';
            Redirect::to('/admin/login');
        }

        LoginThrottleService::clearFor('admin', $email);
        session_regenerate_id(true);
        Csrf::regenerate();
        $_SESSION['admin_id'] = (int) $row['id'];
        $_SESSION['admin_role'] = (string) $row['role'];
        $_SESSION['admin_name'] = (string) $row['name'];
        $_SESSION['unit_id'] = $row['unit_id'] !== null ? (int) $row['unit_id'] : null;
        $_SESSION['show_onboarding_admin'] = true;

        Redirect::to('/admin');
    }

    /**
     * Encerra sessão administrativa.
     */
    public function logout(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin');
        }
        SessionHelper::destroy();
        Redirect::to('/admin/login');
    }
}
