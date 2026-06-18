<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;

/**
 * Registro e leitura da posição GPS do entregador durante a rota.
 */
final class DeliveryLocationService
{
    public const STALE_SECONDS = 180;

    public const MIN_INTERVAL_SECONDS = 8;

    /**
     * Valida par de coordenadas geográficas.
     */
    public static function isValidCoordinate(float $lat, float $lng): bool
    {
        return $lat >= -90.0 && $lat <= 90.0 && $lng >= -180.0 && $lng <= 180.0
            && !($lat === 0.0 && $lng === 0.0);
    }

    /**
     * Registra posição enviada pelo motoboy.
     *
     * @return array{ok: true}|array{ok: false, message: string}
     */
    public static function record(
        int $deliveryId,
        int $motoboyId,
        float $lat,
        float $lng,
        ?float $accuracyM = null
    ): array {
        if (!self::isValidCoordinate($lat, $lng)) {
            return ['ok' => false, 'message' => 'Coordenadas inválidas'];
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT d.id, d.status, d.last_location_at, o.status AS order_status
             FROM deliveries d
             INNER JOIN orders o ON o.id = d.order_id
             WHERE d.id = :id AND d.motoboy_id = :mid
             LIMIT 1'
        );
        $st->execute(['id' => $deliveryId, 'mid' => $motoboyId]);
        $del = $st->fetch(\PDO::FETCH_ASSOC);
        if ($del === false) {
            return ['ok' => false, 'message' => 'Entrega não encontrada'];
        }

        if ((string) $del['status'] !== 'out_for_delivery' || (string) $del['order_status'] !== 'saiu_entrega') {
            return ['ok' => false, 'message' => 'Entrega não está em rota'];
        }

        $lastAt = $del['last_location_at'] ?? null;
        if (is_string($lastAt) && $lastAt !== '') {
            $elapsed = time() - strtotime($lastAt);
            if ($elapsed >= 0 && $elapsed < self::MIN_INTERVAL_SECONDS) {
                return ['ok' => true];
            }
        }

        $accuracy = null;
        if ($accuracyM !== null && $accuracyM >= 0 && $accuracyM <= 5000) {
            $accuracy = (int) round($accuracyM);
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'INSERT INTO delivery_locations (delivery_id, motoboy_id, latitude, longitude, accuracy_m, recorded_at)
                 VALUES (:did, :mid, :lat, :lng, :acc, NOW())'
            )->execute([
                'did' => $deliveryId,
                'mid' => $motoboyId,
                'lat' => round($lat, 8),
                'lng' => round($lng, 8),
                'acc' => $accuracy,
            ]);

            $pdo->prepare(
                'UPDATE deliveries
                 SET last_latitude = :lat, last_longitude = :lng, last_location_at = NOW(), updated_at = NOW()
                 WHERE id = :id'
            )->execute([
                'lat' => round($lat, 8),
                'lng' => round($lng, 8),
                'id' => $deliveryId,
            ]);

            $pdo->commit();
        } catch (\Throwable) {
            $pdo->rollBack();

            return ['ok' => false, 'message' => 'Falha ao salvar localização'];
        }

        return ['ok' => true];
    }

    /**
     * Dados públicos de rastreio para o cliente (sem endereço completo).
     *
     * @return array<string, mixed>|null
     */
    public static function getPublicTracking(string $trackingToken): ?array
    {
        try {
            return self::getPublicTrackingUnsafe($trackingToken);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function getPublicTrackingUnsafe(string $trackingToken): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/i', $trackingToken)) {
            return null;
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT o.id AS order_id, o.status, o.delivery_type,
                    d.id AS delivery_id, d.last_latitude, d.last_longitude, d.last_location_at,
                    m.name AS motoboy_name
             FROM orders o
             LEFT JOIN deliveries d ON d.order_id = o.id
             LEFT JOIN motoboys m ON m.id = d.motoboy_id
             WHERE o.tracking_token = :t AND o.deleted_at IS NULL
             LIMIT 1'
        );
        $st->execute(['t' => $trackingToken]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        $status = (string) ($row['status'] ?? '');
        $deliveryType = (string) ($row['delivery_type'] ?? 'delivery');
        if ($deliveryType !== 'delivery' || $status !== 'saiu_entrega') {
            return [
                'trackable' => false,
                'status' => $status,
                'delivery_type' => $deliveryType,
            ];
        }

        $lat = $row['last_latitude'] ?? null;
        $lng = $row['last_longitude'] ?? null;
        $locatedAt = $row['last_location_at'] ?? null;
        /** @var array<string, mixed>|null $motoboy */
        $motoboy = null;
        $destination = GeocodingService::ensureOrderCoordinates((int) $row['order_id']);

        if (!empty($row['motoboy_name'])) {
            $motoboy = ['name' => (string) $row['motoboy_name']];
        }

        if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
            $stale = true;
            if (is_string($locatedAt) && $locatedAt !== '') {
                $age = time() - strtotime($locatedAt);
                $stale = $age > self::STALE_SECONDS;
            }
            $motoboy = array_merge($motoboy ?? [], [
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'updated_at' => is_string($locatedAt) ? $locatedAt : null,
                'stale' => $stale,
            ]);
        }

        return [
            'trackable' => true,
            'status' => $status,
            'delivery_type' => $deliveryType,
            'motoboy' => $motoboy,
            'destination' => $destination,
        ];
    }

    /**
     * Remove histórico de localização antigo (LGPD / retenção).
     */
    public static function purgeOlderThan(int $days): int
    {
        $days = max(1, $days);
        $since = (new \DateTimeImmutable("-{$days} days"))->format('Y-m-d H:i:s');
        $pdo = Database::pdo();
        $st = $pdo->prepare('DELETE FROM delivery_locations WHERE recorded_at < :s');
        $st->execute(['s' => $since]);

        return $st->rowCount();
    }
}
