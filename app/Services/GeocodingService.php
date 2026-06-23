<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Helpers\Env;
use App\Helpers\Logger;

/**
 * Geocodifica endereços: cache local → Google Geocoding → Nominatim (fallback).
 */
final class GeocodingService
{
    private const CACHE_DAYS = 90;

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
        $query = self::buildQuery($street, $number, $neighborhood, $city, $state, $zip);
        if ($query === null) {
            return null;
        }

        $hash = hash('sha256', mb_strtolower($query, 'UTF-8'));
        $cached = self::readCache($hash);
        if ($cached !== null) {
            return $cached;
        }

        $apiKey = trim((string) Env::get('GOOGLE_MAPS_API_KEY', ''));
        $coords = null;
        $provider = 'nominatim';

        if ($apiKey !== '') {
            $coords = self::geocodeWithGoogle($query, $apiKey);
            if ($coords !== null) {
                $provider = 'google';
            }
        }

        if ($coords === null) {
            $coords = self::geocodeWithNominatim($query);
        }

        if ($coords === null) {
            return null;
        }

        self::writeCache($hash, $coords['lat'], $coords['lng'], $provider);

        return $coords;
    }

    private static function buildQuery(
        string $street,
        string $number,
        string $neighborhood,
        string $city,
        string $state,
        string $zip
    ): ?string {
        $city = trim($city);
        $state = trim($state);
        $street = trim($street);
        if ($street === '' && $city === '') {
            return null;
        }
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

        return ($query !== '' && strlen($query) >= 5) ? $query : null;
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private static function readCache(string $hash): ?array
    {
        try {
            $pdo = Database::pdo();
            $st = $pdo->prepare(
                'SELECT latitude, longitude FROM geocode_cache
                 WHERE query_hash = :h AND created_at >= DATE_SUB(NOW(), INTERVAL ' . self::CACHE_DAYS . ' DAY)
                 LIMIT 1'
            );
            $st->execute(['h' => $hash]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if ($row === false) {
                return null;
            }

            return ['lat' => (float) $row['latitude'], 'lng' => (float) $row['longitude']];
        } catch (\Throwable) {
            return null;
        }
    }

    private static function writeCache(string $hash, float $lat, float $lng, string $provider): void
    {
        try {
            $pdo = Database::pdo();
            $pdo->prepare(
                'INSERT INTO geocode_cache (query_hash, latitude, longitude, provider, created_at)
                 VALUES (:h, :lat, :lng, :p, NOW())
                 ON DUPLICATE KEY UPDATE latitude = VALUES(latitude), longitude = VALUES(longitude),
                     provider = VALUES(provider), created_at = NOW()'
            )->execute([
                'h' => $hash,
                'lat' => round($lat, 8),
                'lng' => round($lng, 8),
                'p' => $provider,
            ]);
        } catch (\Throwable $e) {
            Logger::log('warning', 'geocode_cache_write_failed', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private static function geocodeWithGoogle(string $query, string $apiKey): ?array
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
            'address' => $query,
            'key' => $apiKey,
            'region' => 'br',
            'language' => 'pt-BR',
        ]);

        $body = self::httpGet($url);
        if ($body === null) {
            return null;
        }

        try {
            /** @var array{status?: string, results?: list<array{geometry?: array{location?: array{lat?: float, lng?: float}}}>} $data */
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            if (($data['status'] ?? '') !== 'OK' || empty($data['results'][0]['geometry']['location'])) {
                return null;
            }
            $loc = $data['results'][0]['geometry']['location'];

            return [
                'lat' => (float) ($loc['lat'] ?? 0),
                'lng' => (float) ($loc['lng'] ?? 0),
            ];
        } catch (\Throwable $e) {
            Logger::log('warning', 'google_geocoding_failed', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private static function geocodeWithNominatim(string $query): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q' => $query,
            'format' => 'json',
            'limit' => 1,
            'countrycodes' => 'br',
        ]);

        $body = self::httpGet($url, "User-Agent: DeskFood/1.0 (delivery tracking)\r\nAccept: application/json\r\n");
        if ($body === null) {
            return null;
        }

        try {
            /** @var list<array{lat?: string, lon?: string}> $data */
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data) || $data === [] || !isset($data[0]['lat'], $data[0]['lon'])) {
                return null;
            }

            return ['lat' => (float) $data[0]['lat'], 'lng' => (float) $data[0]['lon']];
        } catch (\Throwable $e) {
            Logger::log('warning', 'nominatim_geocoding_failed', ['message' => $e->getMessage()]);

            return null;
        }
    }

    private static function httpGet(string $url, ?string $extraHeaders = null): ?string
    {
        $headers = $extraHeaders ?? "Accept: application/json\r\n";
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $headers,
                'timeout' => 8,
            ],
        ]);

        try {
            $body = @file_get_contents($url, false, $ctx);

            return ($body === false || $body === '') ? null : $body;
        } catch (\Throwable) {
            return null;
        }
    }
}
