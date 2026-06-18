#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Processa fila background_jobs (SMS, e-mail).
 * Cron: * * * * * php /var/www/deskfood/bin/worker.php
 */
$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';
require $base . '/bootstrap.php';

$max = max(1, (int) ($argv[1] ?? 25));
$n = \App\Services\JobQueueService::work($max);
echo "Jobs processados: {$n}\n";
