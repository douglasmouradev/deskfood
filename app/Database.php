<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

/**
 * Singleton de conexão PDO com MySQL.
 *
 * Garante uma única instância por requisição e opções alinhadas à segurança
 * (emulação de prepared statements desligada, erros como exceções).
 */
final class Database
{
    private static ?PDO $pdo = null;

    /** @var array<string, mixed> */
    private static array $config = [];

    /**
     * Define parâmetros de conexão antes de obter o PDO.
     *
     * @param array<string, mixed> $config host, port, database, username, password, charset
     */
    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Retorna instância PDO reutilizável.
     *
     * @throws PDOException Quando a conexão falhar
     */
    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = (string) (self::$config['host'] ?? '127.0.0.1');
        $port = (int) (self::$config['port'] ?? 3306);
        $db = (string) (self::$config['database'] ?? 'desk_food');
        $user = (string) (self::$config['username'] ?? 'root');
        $pass = (string) (self::$config['password'] ?? '');
        $charset = (string) (self::$config['charset'] ?? 'utf8mb4');

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $db,
            $charset
        );

        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }
}
