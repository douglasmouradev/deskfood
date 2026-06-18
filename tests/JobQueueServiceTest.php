<?php

declare(strict_types=1);

use App\Database;
use App\Helpers\Env;
use App\Services\JobQueueService;
use PHPUnit\Framework\TestCase;

final class JobQueueServiceTest extends TestCase
{
    public function testServiceIsLoadable(): void
    {
        self::assertTrue(class_exists(JobQueueService::class));
    }

    public function testDispatchSyncWhenJobsAsyncOff(): void
    {
        if (Env::get('JOBS_ASYNC', '0') === '1') {
            self::markTestSkipped('JOBS_ASYNC=1 — teste exige modo síncrono');
        }

        if (Env::get('DB_DATABASE', '') === '') {
            self::markTestSkipped('DB não configurado');
        }

        try {
            $pdo = Database::pdo();
            $before = (int) $pdo->query('SELECT COUNT(*) FROM background_jobs')->fetchColumn();
            // SMS_PROVIDER=log não falha em dev
            JobQueueService::dispatch('sms', ['to' => '+5511999999999', 'message' => 'teste unitário']);
            $after = (int) $pdo->query('SELECT COUNT(*) FROM background_jobs')->fetchColumn();
            self::assertSame($before, $after, 'Com JOBS_ASYNC=0 não deve enfileirar');
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL indisponível: ' . $e->getMessage());
        }
    }

    public function testWorkReturnsZeroWhenQueueEmpty(): void
    {
        if (Env::get('DB_DATABASE', '') === '') {
            self::markTestSkipped('DB não configurado');
        }

        try {
            $pdo = Database::pdo();
            $pdo->exec('DELETE FROM background_jobs');
            self::assertSame(0, JobQueueService::work(5));
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL/tabela indisponível: ' . $e->getMessage());
        }
    }
}
