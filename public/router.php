<?php

declare(strict_types=1);

/**
 * Roteador embutido do PHP (`php -S`) para servir arquivos estáticos e o front controller.
 */
$uri = urldecode((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'));
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

require __DIR__ . '/index.php';
