<?php

declare(strict_types=1);

use App\Helpers\ClientIp;
use PHPUnit\Framework\TestCase;

final class ClientIpTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR']);
        putenv('TRUSTED_PROXIES');
    }

    public function testUsesRemoteAddrWithoutTrustedProxy(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1';

        self::assertSame('203.0.113.10', ClientIp::get());
    }

    public function testUsesXForwardedForWhenProxyTrusted(): void
    {
        $_ENV['TRUSTED_PROXIES'] = '203.0.113.1';
        $_SERVER['REMOTE_ADDR'] = '203.0.113.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.5, 203.0.113.1';

        self::assertSame('198.51.100.5', ClientIp::get());

        unset($_ENV['TRUSTED_PROXIES']);
    }
}
