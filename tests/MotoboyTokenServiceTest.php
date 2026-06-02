<?php

declare(strict_types=1);

use App\Services\MotoboyTokenService;
use PHPUnit\Framework\TestCase;

final class MotoboyTokenServiceTest extends TestCase
{
    public function testHashAndMatch(): void
    {
        $token = MotoboyTokenService::generate();
        $hash = MotoboyTokenService::hash($token);
        $this->assertTrue(MotoboyTokenService::matches($token, $hash, null));
        $this->assertFalse(MotoboyTokenService::matches('wrong', $hash, null));
    }

    public function testLegacyPlainToken(): void
    {
        $token = 'abc123legacytoken012345678901234';
        $this->assertTrue(MotoboyTokenService::matches($token, '', $token));
    }
}
