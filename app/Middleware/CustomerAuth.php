<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Redirect;

/**
 * Garante que apenas clientes autenticados acessem rotas protegidas.
 */
final class CustomerAuth
{
    /**
     * Interrompe a requisição com redirecionamento quando não autenticado.
     *
     * @return bool Sempre true quando autenticado; encerra execução caso contrário
     */
    public static function handle(): bool
    {
        if (empty($_SESSION['user_id'])) {
            Redirect::to('/cliente/login');
        }

        return true;
    }
}
