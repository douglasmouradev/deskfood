<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Renderizador simples de views PHP com extração controlada de dados.
 *
 * Converte o nome lógico da view em caminho físico em `views/` e executa
 * o arquivo PHP isolando variáveis injetadas no escopo local.
 */
final class View
{
    /**
     * Renderiza uma view e encerra o fluxo opcionalmente com layout.
     *
     * @param string $view Nome com barras, ex.: `customer/menu`
     * @param array<string, mixed> $data Variáveis disponíveis na view
     * @param array{layout?:string}|null $options Quando `layout` definido, envolve a view
     */
    public static function render(string $view, array $data = [], ?array $options = null): void
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $path = $base . '/views/' . $view . '.php';
        if (!is_file($path)) {
            http_response_code(500);
            echo 'View não encontrada.';
            return;
        }

        if ($options !== null && isset($options['layout'])) {
            $layout = (string) $options['layout'];
            $layoutPath = $base . '/views/layouts/' . $layout . '.php';
            if (!is_file($layoutPath)) {
                http_response_code(500);
                echo 'Layout não encontrado.';

                return;
            }

            $data['__content_path'] = $path;
            extract($data, EXTR_SKIP);
            require $layoutPath;

            return;
        }

        extract($data, EXTR_SKIP);
        require $path;
    }
}
