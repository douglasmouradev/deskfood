<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\DbDate;
use App\Helpers\Redirect;
use App\Services\DeliveryLocationService;
use App\Services\MotoboyTokenService;
use App\Services\OrderService;
use App\Services\RateLimitService;

/**
 * Painel simplificado do entregador acessado pelo token secreto.
 */
final class MotoboyDeliveryController extends Controller
{
    public function index(string $token): void
    {
        if (RateLimitService::isLimited('motoboy_view', substr(hash('sha256', $token), 0, 16), 120, 3600)) {
            http_response_code(429);
            $this->view('errors/404', ['title' => 'Muitas tentativas'], 'public');

            return;
        }
        RateLimitService::hit('motoboy_view', substr(hash('sha256', $token), 0, 16));

        $pdo = Database::pdo();
        $motoboy = self::findActiveMotoboy($pdo, $token);
        if ($motoboy === null) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Link inválido'], 'public');

            return;
        }

        $today = DbDate::todayWhere('o.created_at');
        $st = $pdo->prepare(
            "SELECT o.id, o.order_number, o.status, o.payment_method, o.payment_status, o.total, o.customer_name,
                    o.delivery_street, o.delivery_number, o.delivery_neighborhood, o.delivery_city,
                    d.id AS delivery_id, d.status AS delivery_status
             FROM deliveries d
             INNER JOIN orders o ON o.id = d.order_id
             WHERE d.motoboy_id = :mid AND {$today}
             ORDER BY o.id DESC"
        );
        $st->execute(['mid' => (int) $motoboy['id']]);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->view('motoboy/index', [
            'motoboy' => $motoboy,
            'deliveries' => $rows,
            'token' => $token,
            'csrf' => Csrf::token(),
            'title' => 'Entregas',
        ], 'motoboy');
    }

    public function complete(string $token): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/m/' . $token);
        }

        if (RateLimitService::isLimited('motoboy_complete', substr(hash('sha256', $token), 0, 16), 60, 3600)) {
            Redirect::to('/m/' . $token);
        }
        RateLimitService::hit('motoboy_complete', substr(hash('sha256', $token), 0, 16));

        $deliveryId = (int) filter_input(INPUT_POST, 'delivery_id', FILTER_VALIDATE_INT);
        $pdo = Database::pdo();
        $motoboy = self::findActiveMotoboy($pdo, $token);
        if ($motoboy === null) {
            Redirect::to('/');
        }

        $d = $pdo->prepare('SELECT * FROM deliveries WHERE id = :id AND motoboy_id = :mid LIMIT 1');
        $d->execute(['id' => $deliveryId, 'mid' => (int) $motoboy['id']]);
        $del = $d->fetch(\PDO::FETCH_ASSOC);
        if ($del === false) {
            Redirect::to('/m/' . $token);
        }

        $oid = (int) $del['order_id'];
        $orderSt = $pdo->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $orderSt->execute(['id' => $oid]);
        $order = $orderSt->fetch(\PDO::FETCH_ASSOC);

        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE deliveries SET status = :s, delivered_at = NOW(), updated_at = NOW() WHERE id = :id')
                ->execute(['s' => 'delivered', 'id' => $deliveryId]);
            $pdo->prepare('UPDATE orders SET status = :st, updated_at = NOW() WHERE id = :id')->execute(['st' => 'entregue', 'id' => $oid]);
            if (is_array($order)) {
                OrderService::confirmOnDeliveryPayment($pdo, $oid, $order);
            }
            $pdo->prepare(
                'INSERT INTO order_status_logs (order_id, status, note, actor_type, actor_id, created_at)
                 VALUES (:oid,:st,:n,:atype,:mid,NOW())'
            )->execute([
                'oid' => $oid,
                'st' => 'entregue',
                'n' => 'Confirmado pelo motoboy',
                'atype' => 'motoboy',
                'mid' => (int) $motoboy['id'],
            ]);

            $pdo->commit();
        } catch (\Throwable) {
            $pdo->rollBack();
            Redirect::to('/m/' . $token);
        }

        try {
            \App\Services\CashRegisterService::recordSaleIfOpen($oid);
        } catch (\Throwable) {
        }

        OrderService::notifyStatusSms($oid, 'entregue');

        Redirect::to('/m/' . $token);
    }

    /**
     * Recebe coordenadas GPS do entregador (JSON).
     */
    public function location(string $token): void
    {
        $hashId = substr(hash('sha256', $token), 0, 16);
        if (RateLimitService::isLimited('motoboy_location', $hashId, 480, 3600)) {
            $this->json(['ok' => false, 'message' => 'Muitas atualizações'], 429);

            return;
        }
        RateLimitService::hit('motoboy_location', $hashId);

        $raw = file_get_contents('php://input') ?: '';
        try {
            /** @var array<string, mixed> $body */
            $body = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            $this->json(['ok' => false, 'message' => 'JSON inválido'], 400);

            return;
        }

        $deliveryId = (int) ($body['delivery_id'] ?? 0);
        $lat = filter_var($body['lat'] ?? null, FILTER_VALIDATE_FLOAT);
        $lng = filter_var($body['lng'] ?? null, FILTER_VALIDATE_FLOAT);
        $accuracy = isset($body['accuracy']) ? filter_var($body['accuracy'], FILTER_VALIDATE_FLOAT) : null;

        if ($deliveryId <= 0 || $lat === false || $lng === false) {
            $this->json(['ok' => false, 'message' => 'Dados incompletos'], 400);

            return;
        }

        $pdo = Database::pdo();
        $motoboy = self::findActiveMotoboy($pdo, $token);
        if ($motoboy === null) {
            $this->json(['ok' => false, 'message' => 'Link inválido'], 404);

            return;
        }

        $result = DeliveryLocationService::record(
            $deliveryId,
            (int) $motoboy['id'],
            (float) $lat,
            (float) $lng,
            $accuracy === false ? null : (float) $accuracy
        );

        if (!$result['ok']) {
            $this->json($result, 400);

            return;
        }

        $this->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function findActiveMotoboy(\PDO $pdo, string $token): ?array
    {
        $hash = MotoboyTokenService::hash($token);
        $m = $pdo->prepare(
            'SELECT * FROM motoboys
             WHERE is_active = 1 AND deleted_at IS NULL
               AND (token_expires_at IS NULL OR token_expires_at > NOW())
               AND access_token_hash = :h
             LIMIT 1'
        );
        $m->execute(['h' => $hash]);
        $row = $m->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        if (!MotoboyTokenService::matches($token, (string) ($row['access_token_hash'] ?? ''))) {
            return null;
        }

        return $row;
    }
}
