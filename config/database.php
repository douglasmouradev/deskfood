<?php

declare(strict_types=1);

/**
 * Configuração e fábrica de conexão PDO com o MySQL.
 *
 * Centraliza DSN, opções seguras e o singleton da conexão reutilizada
 * por models, services e o manipulador de sessão.
 */

use App\Database;
use App\Helpers\Env;

Database::configure([
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => (int) Env::get('DB_PORT', '3306'),
    'database' => Env::get('DB_DATABASE', 'desk_food'),
    'username' => Env::get('DB_USERNAME', 'root'),
    'password' => Env::get('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
]);
