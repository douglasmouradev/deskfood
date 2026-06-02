<?php

declare(strict_types=1);

use App\Services\RateLimitService;
use PHPUnit\Framework\TestCase;

final class RateLimitServiceTest extends TestCase
{
    public function testServiceIsLoadable(): void
    {
        self::assertTrue(class_exists(RateLimitService::class));
    }
}
