<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Services\DeliveryLocationService;
use App\Services\GeocodingService;
use App\Services\RateLimitService;

/**
 * Acompanhamento público do pedido com polling JSON.
 */
final class TrackController extends Controller
{
    /**
     * Página visual com barra de progresso e dados do entregador.
     */
    public function page(string $token): void
    {
        $order = $this->loadOrderByToken($token);
        if ($order === null) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Pedido não encontrado'], 'public');

            return;
        }

        $rating = null;
        try {
            $pdo = Database::pdo();
            $rs = $pdo->prepare('SELECT stars, comment FROM order_ratings WHERE order_id = :oid LIMIT 1');
            $rs->execute(['oid' => (int) $order['id']]);
            $rating = $rs->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable) {
            $rating = null;
        }

        $mapDestination = null;
        if (($order['status'] ?? '') === 'saiu_entrega' && ($order['delivery_type'] ?? 'delivery') === 'delivery') {
            $mapDestination = GeocodingService::ensureOrderCoordinates((int) $order['id']);
        }

        $cfg = require dirname(__DIR__, 2) . '/config/app.php';

        $this->view('customer/track', [
            'order' => self::sanitizeForPublic($order),
            'token' => $token,
            'rating' => $rating,
            'map_destination' => $mapDestination,
            'google_maps_api_key' => (string) ($cfg['google_maps_api_key'] ?? ''),
            'csrf' => \App\Helpers\Csrf::token(),
            'flash_ok' => $_SESSION['flash_ok'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
            'title' => 'Acompanhar pedido',
        ], 'public');
        unset($_SESSION['flash_ok'], $_SESSION['flash_error']);
    }

    /**
     * Endpoint JSON consumido pelo polling a cada 15 segundos.
     */
    public function poll(string $token): void
    {
        if (RateLimitService::isLimited('track_poll', substr($token, 0, 32), 120, 3600)) {
            $this->json(['ok' => false, 'message' => 'Muitas consultas. Aguarde.'], 429);

            return;
        }
        RateLimitService::hit('track_poll', substr($token, 0, 32));

        $order = $this->loadOrderByToken($token);
        if ($order === null) {
            $this->json(['ok' => false, 'message' => 'Não encontrado'], 404);

            return;
        }

        $public = self::sanitizeForPublic($order);
        $this->json([
            'ok' => true,
            'status' => $public['status'],
            'payment_status' => $public['payment_status'],
            'motoboy' => $public['motoboy_name'] ? [
                'name' => $public['motoboy_name'],
            ] : null,
            'updated_at' => $public['updated_at'],
        ]);
    }

    /**
     * Posição do entregador para mapa em tempo real (polling).
     */
    public function location(string $token): void
    {
        if (RateLimitService::isLimited('track_location', substr($token, 0, 32), 240, 3600)) {
            $this->json(['ok' => false, 'message' => 'Muitas consultas. Aguarde.'], 429);

            return;
        }
        RateLimitService::hit('track_location', substr($token, 0, 32));

        $data = DeliveryLocationService::getPublicTracking($token);
        if ($data === null) {
            $this->json(['ok' => false, 'message' => 'Não encontrado'], 404);

            return;
        }

        $this->json(['ok' => true] + $data);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadOrderByToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/i', $token)) {
            return null;
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT o.id, o.order_number, o.status, o.payment_status, o.payment_method,
                    o.delivery_type, o.updated_at, o.created_at,
                    u.name AS unit_name, u.phone AS unit_phone,
                    m.name AS motoboy_name
             FROM orders o
             INNER JOIN units u ON u.id = o.unit_id
             LEFT JOIN deliveries d ON d.order_id = o.id
             LEFT JOIN motoboys m ON m.id = d.motoboy_id
             WHERE o.tracking_token = :t AND o.deleted_at IS NULL
             LIMIT 1'
        );
        $st->execute(['t' => $token]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * Remove dados sensíveis antes de expor ao titular ou polling público.
     *
     * @param array<string, mixed> $order
     * @return array<string, mixed>
     */
    public static function sanitizeForPublic(array $order): array
    {
        return [
            'id' => (int) ($order['id'] ?? 0),
            'order_number' => (string) ($order['order_number'] ?? ''),
            'status' => (string) ($order['status'] ?? ''),
            'payment_status' => (string) ($order['payment_status'] ?? ''),
            'payment_method' => (string) ($order['payment_method'] ?? ''),
            'delivery_type' => (string) ($order['delivery_type'] ?? 'delivery'),
            'unit_name' => (string) ($order['unit_name'] ?? ''),
            'unit_phone' => (string) ($order['unit_phone'] ?? ''),
            'motoboy_name' => $order['motoboy_name'] ?? null,
            'updated_at' => (string) ($order['updated_at'] ?? ''),
            'created_at' => (string) ($order['created_at'] ?? ''),
        ];
    }
}
