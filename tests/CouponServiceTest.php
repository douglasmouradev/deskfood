<?php

declare(strict_types=1);

use App\Services\CouponService;
use PHPUnit\Framework\TestCase;

final class CouponServiceTest extends TestCase
{
    public function testServiceIsLoadable(): void
    {
        self::assertTrue(class_exists(CouponService::class));
    }

    public function testEmptyCodeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CouponService::resolve('   ', 1, 50.0);
    }
}
