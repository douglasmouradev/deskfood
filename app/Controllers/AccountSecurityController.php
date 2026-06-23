<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\CryptoService;
use App\Services\TotpService;

/**
 * Troca de senha, 2FA TOTP e configurações de segurança (admin e operador).
 */
final class AccountSecurityController extends Controller
{
    public function showChangePassword(): void
    {
        $data = $this->baseViewData('Alterar senha');
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        $this->view('account/change_password', $data, 'auth');
    }

    public function saveChangePassword(): void
    {
        if (!Csrf::validate()) {
            Redirect::to($this->basePath() . '/senha');
        }

        $current = (string) filter_input(INPUT_POST, 'current_password', FILTER_UNSAFE_RAW);
        $new = (string) filter_input(INPUT_POST, 'new_password', FILTER_UNSAFE_RAW);
        $confirm = (string) filter_input(INPUT_POST, 'confirm_password', FILTER_UNSAFE_RAW);

        if (strlen($new) < 8 || $new !== $confirm) {
            $_SESSION['flash_error'] = 'A nova senha deve ter 8+ caracteres e coincidir com a confirmação.';
            Redirect::to($this->basePath() . '/senha');
        }

        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT password_hash FROM admins WHERE id = :id LIMIT 1');
        $st->execute(['id' => $adminId]);
        $hash = $st->fetchColumn();
        if ($hash === false || !password_verify($current, (string) $hash)) {
            $_SESSION['flash_error'] = 'Senha atual incorreta.';
            Redirect::to($this->basePath() . '/senha');
        }

        $pdo->prepare(
            'UPDATE admins SET password_hash = :h, must_change_password = 0, updated_at = NOW() WHERE id = :id'
        )->execute(['h' => password_hash($new, PASSWORD_DEFAULT), 'id' => $adminId]);

        $_SESSION['flash_success'] = 'Senha atualizada com sucesso.';
        Redirect::to($this->homePath());
    }

    public function showTotpChallenge(): void
    {
        $data = $this->baseViewData('Verificação em duas etapas');
        unset($_SESSION['flash_error']);
        $this->view('account/totp_challenge', $data, 'auth');
    }

    public function verifyTotpChallenge(): void
    {
        if (!Csrf::validate()) {
            Redirect::to($this->basePath() . '/2fa');
        }

        $code = trim((string) filter_input(INPUT_POST, 'code', FILTER_UNSAFE_RAW));
        $secret = $this->loadTotpSecret();
        if ($secret === null || !TotpService::verify($secret, $code)) {
            $_SESSION['flash_error'] = 'Código inválido ou expirado.';
            Redirect::to($this->basePath() . '/2fa');
        }

        $_SESSION['admin_totp_verified'] = true;
        Redirect::to($this->homePath());
    }

    public function showSecuritySettings(): void
    {
        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        $st = Database::pdo()->prepare('SELECT totp_enabled FROM admins WHERE id = :id LIMIT 1');
        $st->execute(['id' => $adminId]);
        $enabled = (int) ($st->fetchColumn() ?: 0) === 1;

        $pendingSecret = $_SESSION['totp_setup_secret'] ?? null;
        $otpUri = null;
        if (is_string($pendingSecret) && $pendingSecret !== '') {
            $cfg = require dirname(__DIR__, 2) . '/config/app.php';
            $email = $this->adminEmail();
            $otpUri = TotpService::otpAuthUri($pendingSecret, $email, (string) ($cfg['name'] ?? 'Desk Food'));
        }

        $viewData = array_merge($this->baseViewData('Segurança da conta'), [
            'totp_enabled' => $enabled,
            'totp_secret' => is_string($pendingSecret) ? $pendingSecret : null,
            'otp_uri' => $otpUri,
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        $this->view('account/security_settings', $viewData, $this->layoutName());
    }

    public function beginTotpSetup(): void
    {
        if (!Csrf::validate()) {
            Redirect::to($this->basePath() . '/seguranca');
        }

        $_SESSION['totp_setup_secret'] = TotpService::generateSecret();
        Redirect::to($this->basePath() . '/seguranca');
    }

    public function enableTotp(): void
    {
        if (!Csrf::validate()) {
            Redirect::to($this->basePath() . '/seguranca');
        }

        $secret = $_SESSION['totp_setup_secret'] ?? null;
        $code = trim((string) filter_input(INPUT_POST, 'code', FILTER_UNSAFE_RAW));
        if (!is_string($secret) || $secret === '' || !TotpService::verify($secret, $code)) {
            $_SESSION['flash_error'] = 'Confirme o código do aplicativo autenticador.';
            Redirect::to($this->basePath() . '/seguranca');
        }

        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        Database::pdo()->prepare(
            'UPDATE admins SET totp_secret_encrypted = :s, totp_enabled = 1, updated_at = NOW() WHERE id = :id'
        )->execute(['s' => CryptoService::encrypt($secret), 'id' => $adminId]);

        unset($_SESSION['totp_setup_secret']);
        $_SESSION['admin_totp_verified'] = true;
        $_SESSION['flash_success'] = 'Autenticação em duas etapas ativada.';
        Redirect::to($this->basePath() . '/seguranca');
    }

    public function disableTotp(): void
    {
        if (!Csrf::validate()) {
            Redirect::to($this->basePath() . '/seguranca');
        }

        $code = trim((string) filter_input(INPUT_POST, 'code', FILTER_UNSAFE_RAW));
        $secret = $this->loadTotpSecret();
        if ($secret === null || !TotpService::verify($secret, $code)) {
            $_SESSION['flash_error'] = 'Código inválido.';
            Redirect::to($this->basePath() . '/seguranca');
        }

        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        Database::pdo()->prepare(
            'UPDATE admins SET totp_secret_encrypted = NULL, totp_enabled = 0, updated_at = NOW() WHERE id = :id'
        )->execute(['id' => $adminId]);

        unset($_SESSION['admin_totp_verified']);
        $_SESSION['flash_success'] = '2FA desativado.';
        Redirect::to($this->basePath() . '/seguranca');
    }

    private function basePath(): string
    {
        return ((string) ($_SESSION['admin_role'] ?? '')) === 'super_admin' ? '/admin' : '/operador';
    }

    private function homePath(): string
    {
        return $this->basePath() === '/admin' ? '/admin' : '/operador';
    }

    private function layoutName(): string
    {
        return $this->basePath() === '/admin' ? 'admin' : 'operator';
    }

    /**
     * @return array<string, mixed>
     */
    private function baseViewData(string $title): array
    {
        return [
            'title' => $title,
            'csrf' => Csrf::token(),
            'base_path' => $this->basePath(),
            'flash_error' => $_SESSION['flash_error'] ?? null,
            'flash_success' => $_SESSION['flash_success'] ?? null,
        ];
    }

    private function adminEmail(): string
    {
        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        $st = Database::pdo()->prepare('SELECT email FROM admins WHERE id = :id LIMIT 1');
        $st->execute(['id' => $adminId]);

        return (string) ($st->fetchColumn() ?: '');
    }

    private function loadTotpSecret(): ?string
    {
        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        $st = Database::pdo()->prepare('SELECT totp_secret_encrypted FROM admins WHERE id = :id LIMIT 1');
        $st->execute(['id' => $adminId]);
        $enc = $st->fetchColumn();
        if ($enc === false || $enc === null || $enc === '') {
            return null;
        }

        try {
            return CryptoService::decrypt((string) $enc);
        } catch (\Throwable) {
            return null;
        }
    }
}
