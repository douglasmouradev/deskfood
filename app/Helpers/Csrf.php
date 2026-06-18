<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Proteção CSRF baseada em sessão para formulários POST.
 */
final class Csrf
{
    /**
     * Gera ou reutiliza token atual na sessão.
     */
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['_csrf'];
    }

    /**
     * Valida token enviado via campo `_csrf` ou cabeçalho `X-CSRF-Token`.
     */
    public static function validate(): bool
    {
        $expected = $_SESSION['_csrf'] ?? '';
        $given = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!is_string($given) || !is_string($expected) || $expected === '') {
            return false;
        }

        return hash_equals($expected, $given);
    }

    /**
     * Gera novo token após login ou ação sensível.
     */
    public static function regenerate(): void
    {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
}
