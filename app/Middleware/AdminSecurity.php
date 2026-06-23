<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Database;
use App\Helpers\Redirect;

/**
 * Troca de senha obrigatória e desafio 2FA após login admin/operador.
 */
final class AdminSecurity
{
    /** @var list<string> */
    private const EXEMPT_PREFIXES = [
        '/admin/login',
        '/operador/login',
        '/admin/senha',
        '/operador/senha',
        '/admin/2fa',
        '/operador/2fa',
        '/admin/seguranca',
        '/operador/seguranca',
    ];

    public static function enforce(): void
    {
        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        if ($adminId <= 0) {
            return;
        }

        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
        foreach (self::EXEMPT_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return;
            }
        }

        $st = Database::pdo()->prepare(
            'SELECT must_change_password, totp_enabled FROM admins WHERE id = :id AND deleted_at IS NULL LIMIT 1'
        );
        $st->execute(['id' => $adminId]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return;
        }

        $role = (string) ($_SESSION['admin_role'] ?? '');
        $base = $role === 'super_admin' ? '/admin' : '/operador';

        if ((int) ($row['must_change_password'] ?? 0) === 1) {
            Redirect::to($base . '/senha');
        }

        if ((int) ($row['totp_enabled'] ?? 0) === 1 && empty($_SESSION['admin_totp_verified'])) {
            Redirect::to($base . '/2fa');
        }
    }
}
