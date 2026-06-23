#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Valida .env antes do go-live.
 * Uso: php bin/check-production.php
 * Exit 0 = pronto (sem erros); 1 = há erros bloqueantes.
 */

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';
\App\Helpers\Env::load($base . '/.env');

use App\Services\ProductionConfigService;

$issues = ProductionConfigService::issues();
$errors = array_filter($issues, static fn (array $i): bool => $i['level'] === 'error');
$warnings = array_filter($issues, static fn (array $i): bool => $i['level'] === 'warning');

echo "Desk Food — auditoria de produção\n";
echo 'APP_ENV=' . (\App\Helpers\Env::get('APP_ENV', '?')) . "\n\n";

if ($errors === [] && $warnings === []) {
    echo "OK: nenhum problema encontrado.\n";
    exit(0);
}

foreach ($errors as $e) {
    echo "[ERRO] {$e['message']}\n";
}
foreach ($warnings as $w) {
    echo "[AVISO] {$w['message']}\n";
}

echo "\n";
if ($errors !== []) {
    echo "Corrija os ERROS antes do go-live.\n";
    exit(1);
}

echo "Sem erros bloqueantes. Revise os AVISOS.\n";
exit(0);
