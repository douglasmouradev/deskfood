<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Env;

/**
 * Healthcheck leve para balanceadores e monitoramento (sem dados sensíveis).
 */
final class HealthController extends Controller
{
    public function index(): void
    {
        $token = trim((string) Env::get('HEALTH_TOKEN', ''));
        if ($token !== '') {
            $given = (string) ($_SERVER['HTTP_X_HEALTH_TOKEN'] ?? '');
            if ($given === '' || !hash_equals($token, $given)) {
                http_response_code(401);
                $this->json(['status' => 'unauthorized'], 401);

                return;
            }
        }

        $ok = true;
        $db = 'ok';
        try {
            Database::pdo()->query('SELECT 1');
        } catch (\Throwable) {
            $ok = false;
            $db = 'error';
        }

        $storageOk = is_writable(BASE_PATH . '/storage/logs');
        $cacheDir = BASE_PATH . '/storage/cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        $cacheOk = is_dir($cacheDir) && is_writable($cacheDir);
        $uploadsOk = is_dir(BASE_PATH . '/public/uploads') && is_writable(BASE_PATH . '/public/uploads');
        $ok = $ok && $storageOk && $uploadsOk && $cacheOk;

        $this->json([
            'status' => $ok ? 'healthy' : 'degraded',
            'checks' => [
                'database' => $db,
                'storage_writable' => $storageOk,
                'cache_writable' => $cacheOk,
                'uploads_writable' => $uploadsOk,
            ],
            'time' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ], $ok ? 200 : 503);
    }
}
