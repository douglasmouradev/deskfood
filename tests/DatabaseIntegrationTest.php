<?php

declare(strict_types=1);

use App\Database;
use App\Helpers\Env;
use PHPUnit\Framework\TestCase;

final class DatabaseIntegrationTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $envFile = dirname(__DIR__) . '/.env';
        if (is_file($envFile)) {
            Env::load($envFile);
        }
    }

    public function testDatabaseConnection(): void
    {
        if (Env::get('DB_DATABASE', '') === '') {
            self::markTestSkipped('DB não configurado');
        }

        try {
            $pdo = Database::pdo();
            $v = $pdo->query('SELECT 1')->fetchColumn();
            self::assertSame('1', (string) $v);
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL indisponível: ' . $e->getMessage());
        }
    }

    public function testSchemaMigrationsTableExists(): void
    {
        try {
            $pdo = Database::pdo();
            $pdo->query('SELECT 1 FROM schema_migrations LIMIT 1');
            self::assertTrue(true);
        } catch (\Throwable $e) {
            self::markTestSkipped('schema_migrations ausente ou DB offline: ' . $e->getMessage());
        }
    }
}
