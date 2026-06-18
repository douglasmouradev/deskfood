<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\Logger;

/**
 * Geocodifica endereços de entrega via Nominatim (OpenStreetMap).
 */
final class GeocodingService
{
    /**
     * Garante coordenadas do pedido; geocodifica e persiste se necessário.
     *
     * @return array{lat: float, lng: float}|null
     */
    public static function ensureOrderCoordinates(int $orderId): ?array
    {
        try {
            return self::ensureOrderCoordinatesUnsafe($orderId);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private static function ensureOrderCoordinatesUnsafe(int $orderId): ?array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT delivery_street, delivery_number, delivery_neighborhood,
                    delivery_city, delivery_state, delivery_zip,
                    delivery_latitude, delivery_longitude
             FROM orders WHERE id = :id AND deleted_at IS NULL LIMIT 1'
        );
        $st->execute(['id' => $orderId]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        $lat = $row['delivery_latitude'] ?? null;
        $lng = $row['delivery_longitude'] ?? null;
        if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
            return ['lat' => (float) $lat, 'lng' => (float) $lng];
        }

        $coords = self::geocodeAddress(
            (string) $row['delivery_street'],
            (string) $row['delivery_number'],
            (string) $row['delivery_neighborhood'],
            (string) $row['delivery_city'],
            (string) $row['delivery_state'],
            (string) $row['delivery_zip']
        );
        if ($coords === null) {
            $coords = self::geocodeAddress('', '', '', (string) $row['delivery_city'], (string) $row['delivery_state'], '');
        }
        if ($coords === null) {
            return null;
        }

        $pdo->prepare(
            'UPDATE orders SET delivery_latitude = :lat, delivery_longitude = :lng, updated_at = NOW() WHERE id = :id'
        )->execute([
            'lat' => $coords['lat'],
            'lng' => $coords['lng'],
            'id' => $orderId,
        ]);

        return $coords;
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    public static function geocodeAddress(
        string $street,
        string $number,
        string $neighborhood,
        string $city,
        string $state,
        string $zip
    ): ?array {
        $city = trim($city);
        $state = trim($state);
        if ($street === '' && $city !== '') {
            $query = trim(sprintf('%s, %s, Brasil', $city, $state));
        } else {
            $query = trim(sprintf(
                '%s %s, %s, %s - %s, %s, Brasil',
                $street,
                $number,
                $neighborhood,
                $city,
                $state,
                preg_replace('/\D+/', '', $zip)
            ));
        }
        if ($query === '' || strlen($query) < 5) {
            return null;
        }

        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q' => $query,
            'format' => 'json',
            'limit' => 1,
            'countrycodes' => 'br',
        ]);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: DeskFood/1.0 (delivery tracking)\r\nAccept: application/json\r\n",
                'timeout' => 8,
            ],
        ]);

        try {
            $body = @file_get_contents($url, false, $ctx);
            if ($body === false || $body === '') {
                return null;
            }
            /** @var list<array{lat?: string, lon?: string}> $data */
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data) || $data === []) {
                return null;
            }
            $first = $data[0];
            if (!isset($first['lat'], $first['lon'])) {
                return null;
            }

            return ['lat' => (float) $first['lat'], 'lng' => (float) $first['lon']];
        } catch (\Throwable $e) {
            Logger::log('warning', 'geocoding_failed', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
