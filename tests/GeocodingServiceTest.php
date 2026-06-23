<?php

declare(strict_types=1);

use App\Services\GeocodingService;
use PHPUnit\Framework\TestCase;

final class GeocodingServiceTest extends TestCase
{
    public function testRejectsEmptyAddress(): void
    {
        self::assertNull(GeocodingService::geocodeAddress('', '', '', '', '', ''));
    }
}
