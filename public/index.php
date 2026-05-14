<?php

declare(strict_types=1);

/**
 * Front controller único do Desk Food.
 *
 * Todas as requisições HTTP entram por este arquivo; ele carrega o bootstrap,
 * aplica cabeçalhos de segurança e delega ao roteador HTTP da aplicação.
 */

$root = dirname(__DIR__);

require $root . '/bootstrap.php';

use App\Middleware\SecurityHeaders;
use App\Router;

SecurityHeaders::send();

$router = new Router(require $root . '/config/routes.php');
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
