<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Converte erros PHP recuperáveis em log estruturado (produção não exibe detalhes).
 */
final class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler([self::class, 'handle']);
    }

    /**
     * @return bool true = erro tratado; false = delegar ao handler interno do PHP
     */
    public static function handle(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        Logger::log('error', 'PHP error', [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
        ]);

        $env = Env::get('APP_ENV', 'production');
        if ($env === 'local' || $env === 'development') {
            return false;
        }

        return true;
    }
}
