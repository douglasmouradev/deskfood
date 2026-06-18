<?php

declare(strict_types=1);

use App\Helpers\Str;
use PHPUnit\Framework\TestCase;

final class StrHelperTest extends TestCase
{
    public function testLowerAscii(): void
    {
        self::assertSame('são paulo', Str::lower('SÃO PAULO'));
    }
}
