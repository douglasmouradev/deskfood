<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;

/**
 * Executa migrations SQL registrando em schema_migrations.
 */
final class MigrationRunner
{
    public static function ensureTable(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
              id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              migration VARCHAR(190) NOT NULL,
              applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (id),
              UNIQUE KEY uq_schema_migrations_name (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public static function isApplied(PDO $pdo, string $name): bool
    {
        self::ensureTable($pdo);
        $st = $pdo->prepare('SELECT 1 FROM schema_migrations WHERE migration = :m LIMIT 1');
        $st->execute(['m' => $name]);

        return $st->fetchColumn() !== false;
    }

    public static function apply(PDO $pdo, string $filePath, string $name): void
    {
        self::ensureTable($pdo);
        if (self::isApplied($pdo, $name)) {
            echo '  (já registrada, pulando)' . PHP_EOL;

            return;
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new \RuntimeException('Não foi possível ler: ' . $filePath);
        }

        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            if (preg_match('/\b(1050|1060|1061|1062)\b/', (string) $e->getCode() . ' ' . $e->getMessage())) {
                echo '  (estrutura já existe, registrando)' . PHP_EOL;
            } else {
                throw $e;
            }
        }

        $ins = $pdo->prepare('INSERT INTO schema_migrations (migration, applied_at) VALUES (:m, NOW())');
        $ins->execute(['m' => $name]);
    }
}
