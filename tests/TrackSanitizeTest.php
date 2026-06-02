<?php

declare(strict_types=1);

use App\Controllers\TrackController;
use PHPUnit\Framework\TestCase;

final class TrackSanitizeTest extends TestCase
{
    public function testSanitizeRemovesSensitiveFields(): void
    {
        $raw = [
            'id' => 1,
            'order_number' => 'ABC',
            'status' => 'pendente',
            'payment_status' => 'pendente',
            'payment_method' => 'pix',
            'delivery_type' => 'delivery',
            'unit_name' => 'Loja',
            'unit_phone' => '71999999999',
            'motoboy_name' => 'João',
            'customer_phone' => '71988887777',
            'delivery_street' => 'Rua Secreta',
            'updated_at' => '2026-01-01',
            'created_at' => '2026-01-01',
        ];

        $public = TrackController::sanitizeForPublic($raw);

        self::assertArrayNotHasKey('customer_phone', $public);
        self::assertArrayNotHasKey('delivery_street', $public);
        self::assertSame('João', $public['motoboy_name']);
        self::assertSame('pendente', $public['status']);
    }
}
