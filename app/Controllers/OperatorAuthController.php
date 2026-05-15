<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Helpers\SessionHelper;
use App\Services\LoginThrottleService;

/**
 * Autenticação do operador da unidade (`unit_operator`).
 */
final class OperatorAuthController extends Controller
{
    /**
     * Formulário de login do operador.
     */
    public function showLogin(): void
    {
        $this->view('operator/login', ['csrf' => Csrf::token(), 'error' => $_SESSION['flash_error'] ?? null, 'title' => 'Operador'], 'public');
        unset($_SESSION['flash_error']);
    }

    /**
     * Valida credenciais e exige papel `unit_operator`.
     */
    public function login(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador/login');
        }

        $email = strtolower(trim((string) filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)));
        $pass = (string) filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
        if ($email === '' || $pass === '') {
            $_SESSION['flash_error'] = 'Preencha e-mail e senha.';
            Redirect::to('/operador/login');
        }

        if (LoginThrottleService::isLockedOut('operator', $email)) {
            $_SESSION['flash_error'] = 'Muitas tentativas. Aguarde alguns minutos.';
            Redirect::to('/operador/login');
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM admins WHERE email = :e AND is_active = 1 AND deleted_at IS NULL LIMIT 1');
        $st->execute(['e' => $email]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if ($row === false || ($row['role'] ?? '') !== 'unit_operator' || !password_verify($pass, (string) $row['password_hash'])) {
            LoginThrottleService::recordFailure('operator', $email);
            $_SESSION['flash_error'] = 'Credenciais inválidas.';
            Redirect::to('/operador/login');
        }

        if (empty($row['unit_id'])) {
            LoginThrottleService::recordFailure('operator', $email);
            $_SESSION['flash_error'] = 'Operador sem unidade vinculada.';
            Redirect::to('/operador/login');
        }

        LoginThrottleService::clearFor('operator', $email);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $row['id'];
        $_SESSION['admin_role'] = (string) $row['role'];
        $_SESSION['admin_name'] = (string) $row['name'];
        $_SESSION['unit_id'] = (int) $row['unit_id'];
        $_SESSION['show_onboarding_operator'] = true;

        Redirect::to('/operador');
    }

    /**
     * Logout do painel do operador.
     */
    public function logout(): void
    {
        SessionHelper::destroy();
        Redirect::to('/operador/login');
    }
}
