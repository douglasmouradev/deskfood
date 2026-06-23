<?php

declare(strict_types=1);

use App\Services\TotpService;
use PHPUnit\Framework\TestCase;

final class TotpServiceTest extends TestCase
{
    public function testGenerateSecretIsBase32(): void
    {
        $secret = TotpService::generateSecret();
        self::assertGreaterThanOrEqual(16, strlen($secret));
        self::assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function testVerifyAcceptsCurrentCode(): void
    {
        $secret = TotpService::generateSecret();
        $code = TotpService::currentCode($secret);
        self::assertTrue(TotpService::verify($secret, $code));
    }

    public function testVerifyRejectsInvalidCode(): void
    {
        $secret = TotpService::generateSecret();
        self::assertFalse(TotpService::verify($secret, '000000'));
        self::assertFalse(TotpService::verify($secret, 'abc'));
    }

    public function testOtpAuthUriContainsIssuerAndSecret(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $uri = TotpService::otpAuthUri($secret, 'user@example.com', 'Desk Food');
        self::assertStringContainsString('otpauth://totp/', $uri);
        self::assertStringContainsString('secret=' . $secret, $uri);
        self::assertStringContainsString('issuer=Desk%20Food', $uri);
    }
}
