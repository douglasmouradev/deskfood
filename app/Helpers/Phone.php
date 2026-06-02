<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Normalização de telefone brasileiro para formato E.164 (+55...).
 */
final class Phone
{
    /**
     * Remove caracteres não numéricos e valida tamanho mínimo/máximo.
     */
    public static function normalizeBr(string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', $input) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            return '+' . $digits;
        }

        if (strlen($digits) >= 10 && strlen($digits) <= 11) {
            return '+55' . $digits;
        }

        return null;
    }

    /**
     * Formatação simples para exibição (XX) XXXXX-XXXX.
     */
    public static function formatDisplay(string $e164): string
    {
        $d = preg_replace('/\D+/', '', $e164) ?? '';
        if (str_starts_with($d, '55')) {
            $d = substr($d, 2);
        }
        if (strlen($d) === 11) {
            return sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 5), substr($d, 7));
        }
        if (strlen($d) === 10) {
            return sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 4), substr($d, 6));
        }

        return $e164;
    }
}
