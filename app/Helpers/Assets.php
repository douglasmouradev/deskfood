<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Assets estáticos (Tailwind compilado vs CDN).
 */
final class Assets
{
    private const TAILWIND_BUILD = '/public/assets/css/tailwind.css';

    public static function useTailwindCdn(): bool
    {
        if (Env::get('TAILWIND_CDN', '0') === '1') {
            return true;
        }

        return !is_file(BASE_PATH . self::TAILWIND_BUILD);
    }

    public static function tailwindStylesheetHref(): string
    {
        $path = BASE_PATH . self::TAILWIND_BUILD;
        if (!is_file($path)) {
            return '/assets/css/tailwind.css';
        }

        $v = (string) filemtime($path);

        return '/assets/css/tailwind.css?v=' . rawurlencode($v);
    }

    public static function appStylesheetHref(): string
    {
        $path = BASE_PATH . '/public/assets/css/app.css';
        if (!is_file($path)) {
            return '/assets/css/app.css';
        }

        return '/assets/css/app.css?v=' . rawurlencode((string) filemtime($path));
    }
}
