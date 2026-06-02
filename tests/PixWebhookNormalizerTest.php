<?php

declare(strict_types=1);

use App\Services\PixWebhookNormalizer;
use PHPUnit\Framework\TestCase;

final class PixWebhookNormalizerTest extends TestCase
{
    public function testExtractDirectTxid(): void
    {
        $ids = PixWebhookNormalizer::extractExternalIds(['txid' => 'abc123']);
        $this->assertSame(['abc123'], $ids);
    }

    public function testExtractEfiPixArray(): void
    {
        $ids = PixWebhookNormalizer::extractExternalIds([
            'pix' => [
                ['txid' => 'efi-txid-1', 'valor' => '10.00'],
            ],
        ]);
        $this->assertContains('efi-txid-1', $ids);
    }

    public function testExtractMercadoPagoDataId(): void
    {
        $ids = PixWebhookNormalizer::extractExternalIds([
            'type' => 'payment',
            'action' => 'payment.updated',
            'data' => ['id' => '987654321'],
        ]);
        $this->assertContains('987654321', $ids);
    }

    public function testMercadoPagoFromQuery(): void
    {
        $payload = PixWebhookNormalizer::mercadoPagoFromQuery(['topic' => 'payment', 'id' => '123']);
        $this->assertNotNull($payload);
        $this->assertSame('123', $payload['data']['id']);
    }

    public function testIsEfiPixPayload(): void
    {
        $this->assertTrue(PixWebhookNormalizer::isEfiPixPayload(['pix' => [['txid' => 'x']]]));
        $this->assertFalse(PixWebhookNormalizer::isEfiPixPayload(['foo' => 'bar']));
    }
}
