#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Limpeza periódica: tentativas de login antigas, PIX expirados, logs antigos.
 * Uso: php bin/cleanup-old-data.php [--days=90] [--log-days=30]
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

$days = 90;
$logDays = 30;
foreach ($argv as $arg) {
    if (preg_match('/^--days=(\d+)$/', $arg, $m)) {
        $days = max(7, (int) $m[1]);
    }
    if (preg_match('/^--log-days=(\d+)$/', $arg, $m)) {
        $logDays = max(1, (int) $m[1]);
    }
}

$pdo = \App\Database::pdo();
$since = (new DateTimeImmutable("-{$days} days"))->format('Y-m-d H:i:s');

$st = $pdo->prepare('DELETE FROM login_attempts WHERE created_at < :s');
$st->execute(['s' => $since]);
$loginDeleted = $st->rowCount();

$otpDeleted = 0;
try {
    $st = $pdo->prepare('DELETE FROM otp_codes WHERE expires_at < :s OR used_at < :s');
    $st->execute(['s' => $since]);
    $otpDeleted = $st->rowCount();
} catch (Throwable) {
}

$pixDeleted = 0;
try {
    $st = $pdo->prepare(
        "DELETE FROM pix_transactions WHERE status IN ('expirado','cancelado')
         AND updated_at < :s"
    );
    $st->execute(['s' => $since]);
    $pixDeleted = $st->rowCount();
} catch (Throwable) {
    // tabela pode não existir em instalações antigas
}

$logPath = $base . '/' . ($_ENV['LOG_PATH'] ?? 'storage/logs');
$logCutoff = time() - ($logDays * 86400);
$logsDeleted = 0;
if (is_dir($logPath)) {
    foreach (glob($logPath . '/*.log') ?: [] as $file) {
        if (is_file($file) && filemtime($file) < $logCutoff) {
            if (@unlink($file)) {
                ++$logsDeleted;
            }
        }
    }
}

echo sprintf(
    "Cleanup OK: login_attempts=%d, otp_codes=%d, pix_transactions=%d, log_files=%d (>%d / >%d dias)\n",
    $loginDeleted,
    $otpDeleted,
    $pixDeleted,
    $logsDeleted,
    $days,
    $logDays
);
