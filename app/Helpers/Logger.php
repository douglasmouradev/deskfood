<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Logging em arquivo com filtro por LOG_LEVEL.
 */
final class Logger
{
    /** @var array<string, int> */
    private const LEVELS = [
        'debug' => 10,
        'info' => 20,
        'warning' => 30,
        'error' => 40,
        'critical' => 50,
    ];

    /**
     * @param array<string, mixed> $context
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $configured = strtolower((string) Env::get('LOG_LEVEL', 'info'));
        $min = self::LEVELS[$configured] ?? self::LEVELS['info'];
        $current = self::LEVELS[strtolower($level)] ?? self::LEVELS['info'];
        if ($current < $min) {
            return;
        }

        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $dir = $base . '/' . trim((string) Env::get('LOG_PATH', 'storage/logs'), '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $dir . '/app-' . date('Y-m-d') . '.log';
        $rid = defined('REQUEST_ID') ? REQUEST_ID : '-';
        $record = [
            'timestamp' => date('c'),
            'request_id' => $rid,
            'level' => strtoupper($level),
            'message' => $message,
        ];
        if ($context !== []) {
            $record['context'] = LogSanitizer::context($context);
        }
        $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . "\n";

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
