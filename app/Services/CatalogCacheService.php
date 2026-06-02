<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;

/**
 * Cache em arquivo do cardápio público por unidade.
 */
final class CatalogCacheService
{
    public static function remember(int $unitId, callable $loader): array
    {
        $ttl = max(0, (int) Env::get('CATALOG_CACHE_TTL', '120'));
        if ($ttl === 0) {
            return $loader();
        }

        $path = self::path($unitId);
        if (is_file($path) && (time() - filemtime($path)) < $ttl) {
            $raw = file_get_contents($path);
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        $data = $loader();
        self::write($unitId, $data);

        return $data;
    }

    public static function bust(int $unitId): void
    {
        $path = self::path($unitId);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public static function bustAll(): void
    {
        $dir = self::dir();
        foreach (glob($dir . '/menu_*.json') ?: [] as $file) {
            @unlink($file);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function write(int $unitId, array $data): void
    {
        $dir = self::dir();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::path($unitId), json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    private static function dir(): string
    {
        return BASE_PATH . '/storage/cache';
    }

    private static function path(int $unitId): string
    {
        return self::dir() . '/menu_' . $unitId . '.json';
    }
}
