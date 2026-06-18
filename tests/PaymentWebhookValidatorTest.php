<?php

declare(strict_types=1);

use App\Services\PaymentWebhookValidator;
use PHPUnit\Framework\TestCase;

final class PaymentWebhookValidatorTest extends TestCase
{
    public function testMercadoPagoAmountMatches(): void
    {
        $this->assertTrue(PaymentWebhookValidator::amountMatchesOrderTotal(49.90, [
            'transaction_amount' => 49.90,
            'status' => 'approved',
        ]));
    }

    public function testAmountMismatch(): void
    {
        $this->assertFalse(PaymentWebhookValidator::amountMatchesOrderTotal(100.0, [
            'transaction_amount' => 1.0,
        ]));
    }
}
