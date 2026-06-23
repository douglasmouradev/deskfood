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

$sessionsDeleted = 0;
try {
    $sessionDays = max(7, (int) ($_ENV['SESSION_CLEANUP_DAYS'] ?? 14));
    $sessionSince = (new DateTimeImmutable("-{$sessionDays} days"))->format('Y-m-d H:i:s');
    $st = $pdo->prepare('DELETE FROM sessions WHERE last_activity < :s');
    $st->execute(['s' => $sessionSince]);
    $sessionsDeleted = $st->rowCount();
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
}

$webhookCleared = 0;
try {
    $st = $pdo->prepare(
        "UPDATE pix_transactions SET webhook_payload = NULL
         WHERE webhook_payload IS NOT NULL AND updated_at < :s"
    );
    $st->execute(['s' => $since]);
    $webhookCleared = $st->rowCount();
    $st2 = $pdo->prepare(
        "UPDATE card_transactions SET webhook_payload = NULL
         WHERE webhook_payload IS NOT NULL AND updated_at < :s"
    );
    $st2->execute(['s' => $since]);
    $webhookCleared += $st2->rowCount();
} catch (Throwable) {
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

$locationsDeleted = 0;
try {
    $locDays = max(7, (int) ($_ENV['LOCATION_RETENTION_DAYS'] ?? 30));
    $locationsDeleted = \App\Services\DeliveryLocationService::purgeOlderThan($locDays);
} catch (Throwable) {
}

$geocodeDeleted = 0;
try {
    $st = $pdo->prepare('DELETE FROM geocode_cache WHERE created_at < :s');
    $st->execute(['s' => $since]);
    $geocodeDeleted = $st->rowCount();
} catch (Throwable) {
}

echo sprintf(
    "Cleanup OK: login_attempts=%d, otp_codes=%d, sessions=%d, pix_transactions=%d, webhook_payloads=%d, delivery_locations=%d, geocode_cache=%d, log_files=%d\n",
    $loginDeleted,
    $otpDeleted,
    $sessionsDeleted,
    $pixDeleted,
    $webhookCleared,
    $locationsDeleted,
    $geocodeDeleted,
    $logsDeleted
);
