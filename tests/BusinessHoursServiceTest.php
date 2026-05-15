<?php

declare(strict_types=1);

use App\Services\BusinessHoursService;
use PHPUnit\Framework\TestCase;

final class BusinessHoursServiceTest extends TestCase
{
    public function testOpenWhenNoHoursConfigured(): void
    {
        self::assertTrue(BusinessHoursService::isOpen(['business_hours' => '']));
    }

    public function testClosedOutsideWindow(): void
    {
        $unit = [
            'business_hours' => json_encode([
                'seg' => ['open' => '11:00', 'close' => '12:00'],
            ], JSON_THROW_ON_ERROR),
        ];
        $at = new \DateTimeImmutable('2026-05-11 15:00:00', new \DateTimeZone('America/Sao_Paulo'));
        self::assertFalse(BusinessHoursService::isOpen($unit, $at));
    }

    public function testOpenInsideWindow(): void
    {
        $unit = [
            'business_hours' => json_encode([
                'seg' => ['open' => '11:00', 'close' => '23:00'],
            ], JSON_THROW_ON_ERROR),
        ];
        $at = new \DateTimeImmutable('2026-05-11 15:00:00', new \DateTimeZone('America/Sao_Paulo'));
        self::assertTrue(BusinessHoursService::isOpen($unit, $at));
    }
}
