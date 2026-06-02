<?php

declare(strict_types=1);

use App\Helpers\OrderEntry;
use PHPUnit\Framework\TestCase;

final class OrderEntryTest extends TestCase
{
    public function testSingleUnitGoesToMenu(): void
    {
        $href = OrderEntry::hrefFromUnits([['slug' => 'centro']]);
        $this->assertSame('/u/centro', $href);
    }

    public function testMultipleUnitsGoesToPickerAnchor(): void
    {
        $href = OrderEntry::hrefFromUnits([['slug' => 'a'], ['slug' => 'b']]);
        $this->assertSame('/#onde-pedir', $href);
    }

    public function testNoUnitsGoesToHome(): void
    {
        $this->assertSame('/', OrderEntry::hrefFromUnits([]));
    }
}
