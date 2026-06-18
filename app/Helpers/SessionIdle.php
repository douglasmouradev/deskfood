<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Expira sessão por inatividade (staff e cliente).
 */
final class SessionIdle
{
    private const SESSION_KEY = '_last_activity';

    public static function enforce(): void
    {
        if (PHP_SAPI === 'cli' || session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $maxMinutes = max(15, (int) Env::get('SESSION_IDLE_MINUTES', '120'));
        $now = time();
        $last = (int) ($_SESSION[self::SESSION_KEY] ?? 0);

        if ($last > 0 && ($now - $last) > ($maxMinutes * 60)) {
            SessionHelper::destroy();
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            return;
        }

        $_SESSION[self::SESSION_KEY] = $now;
    }
}
