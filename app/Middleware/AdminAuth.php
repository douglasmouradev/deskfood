<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Database;
use App\Helpers\Redirect;
use App\Helpers\SessionHelper;

/**
 * Protege rotas administrativas exigindo sessão de `admins` e papel correto.
 */
final class AdminAuth
{
    /**
     * Exige autenticação e papel específico (`super_admin` ou `unit_operator`).
     *
     * @return bool Verdadeiro quando autorizado; encerra com redirect caso contrário
     */
    public static function handle(string $requiredRole): bool
    {
        if (empty($_SESSION['admin_id'])) {
            $target = $requiredRole === 'super_admin' ? '/admin/login' : '/operador/login';
            Redirect::to($target);
        }

        $role = (string) ($_SESSION['admin_role'] ?? '');
        if ($requiredRole === 'super_admin' && $role !== 'super_admin') {
            Redirect::to('/');
        }

        if ($requiredRole === 'unit_operator' && $role !== 'unit_operator') {
            Redirect::to('/');
        }

        self::assertAdminStillActive((int) $_SESSION['admin_id']);
        \App\Middleware\AdminSecurity::enforce();

        return true;
    }

    /**
     * Permite qualquer admin autenticado (dono ou operador) acessar rota comum.
     *
     * @return bool Verdadeiro quando há sessão administrativa válida
     */
    public static function handleAny(): bool
    {
        if (empty($_SESSION['admin_id'])) {
            Redirect::to('/admin/login');
        }

        self::assertAdminStillActive((int) $_SESSION['admin_id']);
        \App\Middleware\AdminSecurity::enforce();

        return true;
    }

    private static function assertAdminStillActive(int $adminId): void
    {
        if ($adminId <= 0) {
            return;
        }

        $st = Database::pdo()->prepare(
            'SELECT is_active FROM admins WHERE id = :id AND deleted_at IS NULL LIMIT 1'
        );
        $st->execute(['id' => $adminId]);
        $active = $st->fetchColumn();
        if ($active === false || (int) $active !== 1) {
            SessionHelper::destroy();
            Redirect::to('/admin/login');
        }
    }
}
