<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Redirect;

/**
 * Exige sessão admin sem aplicar redirecionamentos de senha/2FA (páginas de segurança).
 */
final class AdminSession
{
    public static function handle(string $requiredRole): bool
    {
        if (empty($_SESSION['admin_id'])) {
            Redirect::to($requiredRole === 'super_admin' ? '/admin/login' : '/operador/login');
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
}
