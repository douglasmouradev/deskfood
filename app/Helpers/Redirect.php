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
     *
     * Caminhos internos (começando com /) são relativos ao host atual da requisição,
     * preservando sessão quando APP_URL difere do host usado no navegador (ex.: localhost vs IP LAN).
     */
    public static function to(string $path): void
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            header('Location: ' . $path, true, 302);
            exit;
        }

        $path = $path === '' ? '/' : $path;
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        header('Location: ' . $path, true, 302);
        exit;
    }
}
