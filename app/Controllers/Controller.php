<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Redirect;
use App\Helpers\View;

/**
 * Controller base com utilitários de resposta HTTP comuns.
 *
 * Fornece métodos auxiliares para renderização de views, redirecionamentos
 * e respostas JSON padronizadas para os controllers específicos do sistema.
 */
abstract class Controller
{
    /**
     * Renderiza uma view opcionalmente com layout padrão administrativo.
     *
     * @param array<string, mixed> $data Variáveis injetadas na view
     * @param 'admin'|'operator'|'customer'|'motoboy'|'public'|'landing'|null $layout Qual layout utilizar
     */
    protected function view(string $name, array $data = [], ?string $layout = 'public'): void
    {
        if ($layout === null) {
            View::render($name, $data);

            return;
        }

        $layoutFile = match ($layout) {
            'admin' => 'admin',
            'operator' => 'operator',
            'customer' => 'customer',
            'motoboy' => 'motoboy',
            'landing' => 'landing',
            default => 'public',
        };

        View::render($name, $data, ['layout' => $layoutFile]);
    }

    /**
     * Redireciona para uma URL absoluta ou caminho relativo ao APP_URL.
     */
    protected function redirect(string $to): void
    {
        Redirect::to($to);
    }

    /**
     * Envia resposta JSON com código HTTP informado.
     *
     * @param array<string, mixed> $payload Corpo serializado em JSON
     */
    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
