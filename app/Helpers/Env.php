<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Carregamento e leitura simples de variáveis de ambiente (.env).
 *
 * Implementação leve sem dependências externas: parseia linhas chave=valor,
 * ignora comentários e não sobrescreve variáveis já definidas no servidor.
 */
final class Env
{
    /** @var array<string, string> */
    private static array $vars = [];

    /**
     * Carrega o arquivo .env para memória (idempotente para chaves já carregadas).
     *
     * @param string $path Caminho absoluto do arquivo .env
     */
    public static function load(string $path): void
    {
        if (!is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if ($name === '') {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
                self::$vars[$name] = $value;
            }
        }
    }

    /**
     * Obtém variável de ambiente com valor padrão opcional.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, $_ENV)) {
            return (string) $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return (string) $_SERVER[$key];
        }

        return $default;
    }
}
