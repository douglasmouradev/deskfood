<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

if (!defined('BASE_PATH')) {
    define('BASE_PATH', $root);
}

\App\Helpers\Env::load($root . '/.env');
require $root . '/config/database.php';
date_default_timezone_set('America/Sao_Paulo');
