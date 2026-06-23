<?php

declare(strict_types=1);

use App\Services\ProductionConfigService;
use PHPUnit\Framework\TestCase;

final class ProductionConfigServiceTest extends TestCase
{
    public function testIssuesReturnsStructuredList(): void
    {
        $issues = ProductionConfigService::issues();
        self::assertIsArray($issues);
        foreach ($issues as $issue) {
            self::assertArrayHasKey('level', $issue);
            self::assertArrayHasKey('message', $issue);
            self::assertContains($issue['level'], ['error', 'warning']);
        }
    }
}
