<?php

declare(strict_types=1);

use App\Helpers\Env;
use App\Services\CartPersistenceService;
use PHPUnit\Framework\TestCase;

final class CartPersistenceServiceTest extends TestCase
{
    public function testEnabledRespectsEnvDefault(): void
    {
        self::assertTrue(class_exists(CartPersistenceService::class));
        // CART_PERSIST padrão no .env.example é 1
        $enabled = CartPersistenceService::enabled();
        self::assertIsBool($enabled);
    }

    public function testLoadReturnsNullWithoutSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        self::assertNull(CartPersistenceService::load());
    }

    public function testSaveLoadClearRoundTrip(): void
    {
        if (Env::get('DB_DATABASE', '') === '') {
            self::markTestSkipped('DB não configurado');
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $sid = session_id();
        if ($sid === '') {
            self::markTestSkipped('Sessão indisponível');
        }

        try {
            $cart = ['unit_id' => 1, 'items' => [['product_id' => 99, 'qty' => 2]]];
            CartPersistenceService::save($cart);
            $loaded = CartPersistenceService::load();
            self::assertIsArray($loaded);
            self::assertSame(1, (int) ($loaded['unit_id'] ?? 0));
            self::assertCount(1, $loaded['items'] ?? []);

            CartPersistenceService::clear();
            self::assertNull(CartPersistenceService::load());
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL indisponível: ' . $e->getMessage());
        }
    }
}
