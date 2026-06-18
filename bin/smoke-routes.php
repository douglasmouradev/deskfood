#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Smoke test HTTP das rotas públicas e páginas de login.
 * Uso: php bin/smoke-routes.php [base_url]
 */

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';
if (is_file($base . '/.env')) {
    foreach (file($base . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
}

$root = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
$routes = require $base . '/config/routes.php';

$paths = [];
foreach ($routes as $r) {
    if (!is_array($r) || empty($r['path'])) {
        continue;
    }
    $path = (string) $r['path'];
    if (str_contains($path, '{')) {
        continue;
    }
    $mw = $r['middleware'] ?? [];
    if (is_array($mw) && $mw !== []) {
        continue;
    }
    $methods = $r['methods'] ?? ['GET'];
    if (!in_array('GET', $methods, true)) {
        continue;
    }
    $paths[$path] = true;
}

$extra = ['/', '/landing', '/u/centro', '/operador/login', '/admin/login', '/cliente/login', '/ajuda', '/termos', '/privacidade'];
foreach ($extra as $p) {
    $paths[$p] = true;
}

$failed = [];
$ok = 0;
foreach (array_keys($paths) as $path) {
    $url = $root . $path;
    $ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 8, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    $headers = function_exists('http_get_last_response_headers')
        ? http_get_last_response_headers()
        : ($http_response_header ?? []);
    if (isset($headers[0]) && preg_match('/\s(\d{3})\s/', (string) $headers[0], $m)) {
        $code = (int) $m[1];
    }
    $isError = $body === false || $code >= 500 || ($body !== false && (
        str_contains($body, 'Fatal error')
        || str_contains($body, 'Uncaught')
        || str_contains($body, 'SQLSTATE[')
        || str_contains($body, 'Stack trace')
    ));
    if ($isError) {
        $snippet = $body !== false ? substr(strip_tags($body), 0, 200) : 'no response';
        $failed[] = ['path' => $path, 'code' => $code, 'snippet' => $snippet];
    } else {
        ++$ok;
    }
}

echo "Smoke OK: {$ok}/" . count($paths) . "\n";
foreach ($failed as $f) {
    echo "FAIL [{$f['code']}] {$f['path']}\n  {$f['snippet']}\n";
}
exit($failed === [] ? 0 : 1);
