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

        $storageOk = is_writable(BASE_PATH . '/storage/logs');
        $uploadsOk = is_dir(BASE_PATH . '/public/uploads') && is_writable(BASE_PATH . '/public/uploads');
        $ok = $ok && $storageOk && $uploadsOk;

        $this->json([
            'status' => $ok ? 'healthy' : 'degraded',
            'checks' => [
                'database' => $db,
                'storage_writable' => $storageOk,
                'uploads_writable' => $uploadsOk,
            ],
            'time' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ], $ok ? 200 : 503);
    }
}
