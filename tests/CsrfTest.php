<?php

declare(strict_types=1);

use App\Helpers\Csrf;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
        $_SERVER = [];
    }

    public function testTokenIsStableInSession(): void
    {
        $a = Csrf::token();
        $b = Csrf::token();
        $this->assertSame($a, $b);
        $this->assertSame(64, strlen($a));
    }

    public function testValidatePostField(): void
    {
        $token = Csrf::token();
        $_POST['_csrf'] = $token;
        $this->assertTrue(Csrf::validate());
    }

    public function testValidateHeader(): void
    {
        $token = Csrf::token();
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
        $this->assertTrue(Csrf::validate());
    }

    public function testRejectInvalidToken(): void
    {
        Csrf::token();
        $_POST['_csrf'] = 'invalid';
        $this->assertFalse(Csrf::validate());
    }
}
