<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Utilitário simples de logging em arquivo com rotação leve por dia.
 */
final class Logger
{
    /**
     * Registra mensagem em arquivo dentro de `storage/logs`.
     *
     * @param array<string, mixed> $context Dados adicionais serializados em JSON
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $dir = $base . '/' . trim((string) Env::get('LOG_PATH', 'storage/logs'), '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $dir . '/app-' . date('Y-m-d') . '.log';
        $rid = defined('REQUEST_ID') ? REQUEST_ID : '-';
        $line = sprintf(
            "[%s] [%s] %s %s %s\n",
            date('c'),
            $rid,
            strtoupper($level),
            $message,
            $context !== [] ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );

        file_put_contents($file, $line, FILE_APPEND);
    }
}
