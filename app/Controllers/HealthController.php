<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;

/**
 * Healthcheck leve para balanceadores e monitoramento (sem dados sensíveis).
 */
final class HealthController extends Controller
{
    /**
     * Retorna JSON com status da aplicação e ping no banco.
     */
    public function index(): void
    {
        $ok = true;
        $db = 'ok';
        try {
            Database::pdo()->query('SELECT 1');
        } catch (\Throwable) {
            $ok = false;
            $db = 'error';
        }

        $this->json([
            'status' => $ok ? 'healthy' : 'degraded',
            'database' => $db,
            'time' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ], $ok ? 200 : 503);
    }
}
