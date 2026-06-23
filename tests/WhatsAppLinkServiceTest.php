<?php

declare(strict_types=1);

use App\Services\WhatsAppLinkService;
use PHPUnit\Framework\TestCase;

final class WhatsAppLinkServiceTest extends TestCase
{
    public function testNormalizeAddsBrazilCountryCode(): void
    {
        self::assertSame('5511999998888', WhatsAppLinkService::normalizePhoneBr('(11) 99999-8888'));
    }

    public function testUrlEncodesMessage(): void
    {
        $url = WhatsAppLinkService::url('11999998888', 'Olá motoboy');
        self::assertNotNull($url);
        self::assertStringStartsWith('https://wa.me/5511999998888?text=', $url);
        self::assertStringContainsString('Ol%C3%A1', $url);
    }

    public function testUrlReturnsNullForEmptyPhone(): void
    {
        self::assertNull(WhatsAppLinkService::url('', 'msg'));
    }
}
