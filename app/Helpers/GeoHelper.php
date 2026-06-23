<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Cálculos geográficos simples (distância e ETA).
 */
final class GeoHelper
{
    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public static function estimateMinutes(float $distanceKm, float $avgKmh = 22.0): int
    {
        if ($distanceKm <= 0) {
            return 0;
        }

        return max(1, (int) round(($distanceKm / $avgKmh) * 60));
    }
}
