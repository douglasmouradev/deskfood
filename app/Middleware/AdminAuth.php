<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Redirect;

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

        return true;
    }
}
