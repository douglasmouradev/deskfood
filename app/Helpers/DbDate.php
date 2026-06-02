<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Filtros de data compatíveis com índices em colunas DATETIME.
 */
final class DbDate
{
    public static function todayWhere(string $column = 'created_at'): string
    {
        return sprintf('%s >= CURDATE() AND %s < CURDATE() + INTERVAL 1 DAY', $column, $column);
    }
}
