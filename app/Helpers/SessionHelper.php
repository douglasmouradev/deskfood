<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Encerramento seguro de sessão (staff e genérico).
 */
final class SessionHelper
{
    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }
        session_destroy();
    }
}
