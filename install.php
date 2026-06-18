<?php

declare(strict_types=1);

/**
 * Script de instalação: cria o banco (opcional), executa migrations e seeds.
 *
 * Uso em CLI: `php install.php` ou `php install.php --force` para reexecutar.
 * Uso via navegador: `/install.php?key=SUA_INSTALL_KEY` (somente se permitido pelo .env).
 *
 * Cria `storage/.installed` após sucesso (marca ambiente). Migrations rodam sempre; o seed de demo
 * só na primeira execução ou com `php install.php --force`. Em produção, instalação via browser exige `ALLOW_INSTALL=1`.
 */

use App\Helpers\Env;
use App\Services\MigrationRunner;

$root = __DIR__;

require_once $root . '/vendor/autoload.php';

Env::load($root . '/.env');

$isCli = PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
$argv = $_SERVER['argv'] ?? [];
$force = $isCli && in_array('--force', $argv, true);
$allowInstall = Env::get('ALLOW_INSTALL', '0') === '1';
$appEnv = Env::get('APP_ENV', 'production');
$installedFile = $root . '/storage/.installed';
$installed = is_file($installedFile);

if (!$isCli && $installed && $appEnv === 'production' && !$allowInstall) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Sistema já instalado. Use CLI: php install.php';
    exit(1);
}

if (!$isCli) {
    if ($appEnv === 'production' && !$allowInstall) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Instalação via navegador desativada em produção. Defina ALLOW_INSTALL=1 temporariamente ou use o CLI.';
        exit(1);
    }

    $key = $_GET['key'] ?? '';
    if ($key === '' || $key !== Env::get('INSTALL_KEY', '')) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Instalação bloqueada: defina INSTALL_KEY no .env e informe ?key= na URL.';
        exit(1);
    }
    header('Content-Type: text/plain; charset=utf-8');
}

$host = Env::get('DB_HOST', '127.0.0.1');
$port = (int) Env::get('DB_PORT', '3306');
$dbName = Env::get('DB_DATABASE', 'desk_food');
$user = Env::get('DB_USERNAME', 'root');
$pass = Env::get('DB_PASSWORD', '');

try {
    $pdoAdmin = new PDO(
        sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port),
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdoAdmin->exec(
        'CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $dbName) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );

    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $dbName),
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $migrationFiles = glob($root . '/database/migrations/*.sql') ?: [];
    sort($migrationFiles);

    MigrationRunner::ensureTable($pdo);

    foreach ($migrationFiles as $file) {
        $name = basename($file);
        echo 'Executando migration: ' . $name . PHP_EOL;
        MigrationRunner::apply($pdo, $file, $name);
    }

    $seed = $root . '/database/seeds/initial_data.sql';
    if (is_readable($seed)) {
        if ($installed && !$force) {
            echo 'Pulando seed (instalação já marcada). Use: php install.php --force' . PHP_EOL;
        } else {
            echo "Executando seed: initial_data.sql" . PHP_EOL;
            $sqlSeed = file_get_contents($seed);
            if ($sqlSeed === false) {
                throw new RuntimeException('Não foi possível ler seed.');
            }
            $pdo->exec($sqlSeed);
        }
    }

    foreach (['storage/logs', 'storage/cache', 'storage/reports', 'public/uploads', 'public/uploads/products'] as $dir) {
        $path = $root . '/' . $dir;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    if (!is_dir(dirname($installedFile))) {
        mkdir(dirname($installedFile), 0755, true);
    }
    file_put_contents(
        $installedFile,
        json_encode(['installed_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)], JSON_THROW_ON_ERROR)
    );

    echo PHP_EOL . 'Instalação concluída com sucesso.' . PHP_EOL;
    echo 'Migrations registradas em schema_migrations. Seed só na primeira vez ou com --force.' . PHP_EOL;
} catch (Throwable $e) {
    if (!$isCli && !headers_sent()) {
        http_response_code(500);
    }
    echo 'Erro na instalação: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
