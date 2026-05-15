<?php

declare(strict_types=1);

use App\Services\DeliveryService;
use PHPUnit\Framework\TestCase;

final class DeliveryServiceTest extends TestCase
{
    public function testAllowsWhenRadiusZero(): void
    {
        $unit = ['delivery_radius_km' => 0, 'city' => 'São Paulo', 'state' => 'SP', 'zip' => '01001000'];
        $delivery = ['city' => 'Rio de Janeiro', 'state' => 'RJ', 'zip' => '20040020'];
        DeliveryService::assertDeliverable($unit, $delivery);
        self::assertTrue(true);
    }

    public function testRejectsDifferentState(): void
    {
        $unit = ['delivery_radius_km' => 5, 'city' => 'São Paulo', 'state' => 'SP', 'zip' => '01001000'];
        $delivery = ['city' => 'São Paulo', 'state' => 'RJ', 'zip' => '01001000'];
        $this->expectException(\RuntimeException::class);
        DeliveryService::assertDeliverable($unit, $delivery);
    }
}
