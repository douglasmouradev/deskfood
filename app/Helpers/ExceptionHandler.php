<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Helpers\View;
use Throwable;

/**
 * Handler global para exceções não capturadas.
 */
final class ExceptionHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(Throwable $e): void
    {
        Logger::log('error', 'Exceção não tratada', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        if (!headers_sent()) {
            http_response_code(500);
        }

        $env = Env::get('APP_ENV', 'production');
        if ($env === 'local' || $env === 'development') {
            echo '<pre style="padding:1rem;font-family:monospace">';
            echo htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString());
            echo '</pre>';

            return;
        }

        if (!headers_sent()) {
            View::render('errors/500', ['title' => 'Erro interno'], ['layout' => 'public']);
        } else {
            echo 'Erro interno. Tente novamente.';
        }
    }
}
