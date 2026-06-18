<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Utilitários de string com fallback quando mbstring não está instalado.
 */
final class Str
{
    public static function lower(string $value): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }
}
