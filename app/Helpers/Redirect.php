<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Redirecionamentos HTTP 302 com URL resolvida a partir da configuração.
 */
final class Redirect
{
    /**
     * Encerra a execução após enviar cabeçalho Location.
     */
    public static function to(string $path): void
    {
        $config = require dirname(__DIR__, 2) . '/config/app.php';
        $base = rtrim((string) ($config['url'] ?? ''), '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $url = $path;
        } else {
            $path = $path === '' ? '/' : $path;
            if ($path[0] !== '/') {
                $path = '/' . $path;
            }
            $url = $base . $path;
        }

        header('Location: ' . $url, true, 302);
        exit;
    }
}
