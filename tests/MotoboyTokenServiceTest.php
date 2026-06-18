<?php

declare(strict_types=1);

use App\Services\MotoboyTokenService;
use PHPUnit\Framework\TestCase;

final class MotoboyTokenServiceTest extends TestCase
{
    public function testHashAndMatch(): void
    {
        $token = MotoboyTokenService::generate();
        $this->assertSame(64, strlen($token));
        $hash = MotoboyTokenService::hash($token);
        $this->assertTrue(MotoboyTokenService::matches($token, $hash));
        $this->assertFalse(MotoboyTokenService::matches('wrong', $hash));
    }
}
