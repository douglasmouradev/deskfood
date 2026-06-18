<?php

declare(strict_types=1);

use App\Services\DeliveryLocationService;
use PHPUnit\Framework\TestCase;

final class DeliveryLocationServiceTest extends TestCase
{
    public function testValidCoordinates(): void
    {
        self::assertTrue(DeliveryLocationService::isValidCoordinate(-23.5505, -46.6333));
        self::assertTrue(DeliveryLocationService::isValidCoordinate(0.5, -47.9));
    }

    public function testRejectsInvalidCoordinates(): void
    {
        self::assertFalse(DeliveryLocationService::isValidCoordinate(91.0, 0.0));
        self::assertFalse(DeliveryLocationService::isValidCoordinate(0.0, 181.0));
        self::assertFalse(DeliveryLocationService::isValidCoordinate(0.0, 0.0));
    }

    public function testStaleThresholdIsReasonable(): void
    {
        self::assertGreaterThan(60, DeliveryLocationService::STALE_SECONDS);
        self::assertLessThanOrEqual(600, DeliveryLocationService::STALE_SECONDS);
    }
}
