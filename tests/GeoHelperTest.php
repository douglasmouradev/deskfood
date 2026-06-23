<?php

declare(strict_types=1);

use App\Helpers\GeoHelper;
use PHPUnit\Framework\TestCase;

final class GeoHelperTest extends TestCase
{
    public function testHaversineKnownDistance(): void
    {
        // São Paulo centro ~ Avenida Paulista (aprox. 6 km)
        $km = GeoHelper::haversineKm(-23.5505, -46.6333, -23.5614, -46.6559);
        self::assertGreaterThan(2.0, $km);
        self::assertLessThan(5.0, $km);
    }

    public function testEstimateMinutesMinimumOne(): void
    {
        self::assertSame(0, GeoHelper::estimateMinutes(0));
        self::assertSame(1, GeoHelper::estimateMinutes(0.1));
    }
}
