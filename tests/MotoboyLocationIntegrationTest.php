<?php

declare(strict_types=1);

use App\Database;
use App\Helpers\Env;
use App\Services\DeliveryLocationService;
use App\Services\MotoboyTokenService;
use App\Services\ProductionConfigService;
use PHPUnit\Framework\TestCase;

final class MotoboyLocationIntegrationTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $envFile = dirname(__DIR__) . '/.env';
        if (is_file($envFile)) {
            Env::load($envFile);
        }
    }

    public function testRecordAndPublicTrackingRoundTrip(): void
    {
        try {
            $pdo = Database::pdo();
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL indisponível: ' . $e->getMessage());
        }

        $row = $pdo->query(
            'SELECT d.id AS delivery_id, d.motoboy_id, o.tracking_token
             FROM deliveries d
             INNER JOIN orders o ON o.id = d.order_id
             WHERE d.status = "out_for_delivery" AND o.status = "saiu_entrega"
             ORDER BY d.id DESC LIMIT 1'
        )->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            self::markTestSkipped('Nenhuma entrega em rota — rode php bin/seed-demo-order.php --status=saiu_entrega');
        }

        $deliveryId = (int) $row['delivery_id'];
        $motoboyId = (int) $row['motoboy_id'];
        $token = (string) $row['tracking_token'];

        $result = DeliveryLocationService::record($deliveryId, $motoboyId, -12.9714, -38.5014, 12.0);
        self::assertTrue($result['ok'] ?? false);

        $public = DeliveryLocationService::getPublicTracking($token);
        self::assertIsArray($public);
        self::assertTrue($public['trackable'] ?? false);
        self::assertIsArray($public['motoboy'] ?? null);
        self::assertEqualsWithDelta(-12.9714, (float) ($public['motoboy']['lat'] ?? 0), 0.0001);
    }

    public function testProductionConfigDetectsWeakSecret(): void
    {
        $issues = ProductionConfigService::issues();
        self::assertIsArray($issues);
        self::assertNotEmpty($issues);
    }

    public function testMotoboyTokenHashRoundTrip(): void
    {
        $plain = MotoboyTokenService::generate();
        $hash = MotoboyTokenService::hash($plain);
        self::assertTrue(MotoboyTokenService::matches($plain, $hash));
        self::assertFalse(MotoboyTokenService::matches('invalid', $hash));
    }
}
