<?php

declare(strict_types=1);

/**
 * Bootstrap da aplicação Desk Food.
 *
 * Responsável por carregar variáveis de ambiente, timezone, exibição de erros
 * conforme APP_ENV e registrar o manipulador de sessão em banco de dados.
 */

use App\Helpers\Env;
use App\Helpers\ExceptionHandler;
use App\Services\SessionHandler;

$root = __DIR__;

if (!defined('BASE_PATH')) {
    define('BASE_PATH', $root);
}

require_once $root . '/vendor/autoload.php';

Env::load($root . '/.env');

date_default_timezone_set('America/Sao_Paulo');

$env = Env::get('APP_ENV', 'production');
if ($env === 'local' || $env === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}

$logPath = $root . '/' . trim(Env::get('LOG_PATH', 'storage/logs'), '/');
if (!is_dir($logPath)) {
    mkdir($logPath, 0755, true);
}

ini_set('log_errors', '1');
ini_set('error_log', $logPath . '/php-errors.log');

require_once $root . '/config/database.php';

$sessionHandler = new SessionHandler();
session_set_save_handler($sessionHandler, true);

$secure = str_starts_with((string) Env::get('APP_URL', ''), 'https://');
session_name('DESKFOODSESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ExceptionHandler::register();

$requestId = bin2hex(random_bytes(8));
if (!defined('REQUEST_ID')) {
    define('REQUEST_ID', $requestId);
}
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Request-Id: ' . $requestId);
}

if ($env === 'production') {
    $secret = Env::get('APP_SECRET', '');
    if ($secret === null || strlen($secret) < 16 || str_contains($secret, 'altere')) {
        http_response_code(503);
        echo 'Configuração inválida: defina APP_SECRET forte no .env.';
        exit;
    }

    $pixSecret = trim((string) Env::get('PIX_WEBHOOK_SECRET', ''));
    if ($pixSecret === '') {
        http_response_code(503);
        echo 'Configuração inválida: defina PIX_WEBHOOK_SECRET em produção.';
        exit;
    }
}
