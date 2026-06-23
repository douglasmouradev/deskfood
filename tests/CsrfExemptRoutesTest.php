<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CsrfExemptRoutesTest extends TestCase
{
    /** @var list<string> */
    private array $patterns;

    protected function setUp(): void
    {
        $this->patterns = require dirname(__DIR__) . '/config/csrf.php';
    }

    public function testMotoboyLocationIsExempt(): void
    {
        $path = '/m/ab94b7dad9b9e81ffdee0843bd1d204fd664707622876af3e585b314e2c15daa/localizacao';
        self::assertTrue($this->pathMatchesCsrfExempt($path), 'POST GPS do motoboy deve ser isento de CSRF');
    }

    public function testWebhookIsExempt(): void
    {
        self::assertTrue($this->pathMatchesCsrfExempt('/webhooks/payment'));
    }

    public function testOperatorPostIsNotExempt(): void
    {
        self::assertFalse($this->pathMatchesCsrfExempt('/operador/motoboys/1/revogar'));
    }

    private function pathMatchesCsrfExempt(string $path): bool
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $path) === 1) {
                return true;
            }
        }

        return false;
    }
}
