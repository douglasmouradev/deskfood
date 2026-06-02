<?php

declare(strict_types=1);

use App\Services\UnitPaymentConfig;
use PHPUnit\Framework\TestCase;

final class UnitPaymentConfigTest extends TestCase
{
    public function testPaymentReferenceRoundTrip(): void
    {
        $ref = UnitPaymentConfig::paymentReference(42);
        $this->assertSame('deskfood-payment-42', $ref);
        $this->assertSame(42, UnitPaymentConfig::parsePaymentReference($ref));
    }

    public function testSupportsMercadoPagoCard(): void
    {
        $config = [
            'provider' => 'mercadopago',
            'pix_enabled' => true,
            'card_enabled' => true,
            'mp_access_token' => 'token-test',
            'pix_key' => '',
            'efi_client_id' => '',
            'efi_client_secret' => '',
        ];
        $this->assertTrue(UnitPaymentConfig::supportsCard($config));
        $this->assertTrue(UnitPaymentConfig::supportsPix($config));
    }
}
