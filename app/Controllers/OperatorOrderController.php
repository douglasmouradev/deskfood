<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\AuditLogService;
use App\Services\CashRegisterService;
use App\Services\OrderService;
use App\Services\WhatsAppLinkService;

/**
 * Ações de mudança de status e atribuição de entregador.
 */
final class OperatorOrderController extends Controller
{
    /**
     * Atualiza status macro do pedido com registro em log.
     */
    public function status(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador');
        }

        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $status = (string) filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);
        $note = trim((string) (filter_input(INPUT_POST, 'note', FILTER_UNSAFE_RAW) ?: ''));
        $allowed = ['confirmado', 'em_preparo', 'saiu_entrega', 'entregue', 'cancelado'];
        if (!in_array($status, $allowed, true)) {
            Redirect::to('/operador');
        }

        $pdo = Database::pdo();
        $o = $pdo->prepare('SELECT * FROM orders WHERE id = :id AND unit_id = :u LIMIT 1');
        $o->execute(['id' => $id, 'u' => $unitId]);
        $order = $o->fetch(\PDO::FETCH_ASSOC);
        if ($order === false) {
            Redirect::to('/operador');
        }

        if ($status === 'saiu_entrega') {
            $del = $pdo->prepare('SELECT id FROM deliveries WHERE order_id = :oid LIMIT 1');
            $del->execute(['oid' => $id]);
            if ($del->fetch() === false) {
                $_SESSION['flash_error'] = 'Atribua um motoboy antes de marcar "Saiu para entrega".';
                Redirect::to('/operador');
            }
        }

        if ($status === 'cancelado') {
            $reason = trim((string) filter_input(INPUT_POST, 'cancel_reason', FILTER_UNSAFE_RAW));
            $pdo->prepare('UPDATE orders SET status = :s, cancel_reason = :r, updated_at = NOW() WHERE id = :id')
                ->execute(['s' => $status, 'r' => $reason, 'id' => $id]);
        } else {
            $pdo->prepare('UPDATE orders SET status = :s, updated_at = NOW() WHERE id = :id')
                ->execute(['s' => $status, 'id' => $id]);
        }

        if ($status === 'entregue' && ($order['payment_method'] ?? '') === 'on_delivery') {
            OrderService::confirmOnDeliveryPayment($pdo, $id, $order);
        }

        $pdo->prepare(
            'INSERT INTO order_status_logs (order_id, status, note, actor_type, actor_id, created_at)
             VALUES (:oid,:st,:n,:atype,:aid,NOW())'
        )->execute([
            'oid' => $id,
            'st' => $status,
            'n' => $note,
            'atype' => 'operator',
            'aid' => (int) $_SESSION['admin_id'],
        ]);

        OrderService::notifyStatusSms($id, $status);

        AuditLogService::record('operator', (int) ($_SESSION['admin_id'] ?? 0), 'order.status.' . $status, 'order', $id, [
            'note' => $note,
            'unit_id' => $unitId,
        ]);

        if ($status === 'entregue') {
            try {
                CashRegisterService::recordSaleIfOpen($id);
            } catch (\Throwable) {
                // silencioso: caixa pode estar fechado
            }
        }

        Redirect::to('/operador');
    }

    /**
     * Atribui motoboy ao pedido em preparo e move status para saiu_entrega.
     */
    public function assign(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador');
        }

        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $mid = (int) filter_input(INPUT_POST, 'motoboy_id', FILTER_VALIDATE_INT);
        if ($mid <= 0) {
            Redirect::to('/operador');
        }

        $pdo = Database::pdo();
        $o = $pdo->prepare('SELECT * FROM orders WHERE id = :id AND unit_id = :u LIMIT 1');
        $o->execute(['id' => $id, 'u' => $unitId]);
        $order = $o->fetch(\PDO::FETCH_ASSOC);
        if ($order === false) {
            Redirect::to('/operador');
        }

        $m = $pdo->prepare('SELECT * FROM motoboys WHERE id = :id AND unit_id = :u LIMIT 1');
        $m->execute(['id' => $mid, 'u' => $unitId]);
        $motoboy = $m->fetch(\PDO::FETCH_ASSOC);
        if ($motoboy === false) {
            Redirect::to('/operador');
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM deliveries WHERE order_id = :oid')->execute(['oid' => $id]);
            $pdo->prepare(
                'INSERT INTO deliveries (order_id, motoboy_id, status, started_at, created_at, updated_at)
                 VALUES (:oid,:mid,:st,NOW(),NOW(),NOW())'
            )->execute(['oid' => $id, 'mid' => $mid, 'st' => 'out_for_delivery']);

            $pdo->prepare('UPDATE orders SET status = "saiu_entrega", updated_at = NOW() WHERE id = :id')->execute(['id' => $id]);
            $pdo->prepare(
                'INSERT INTO order_status_logs (order_id, status, note, actor_type, actor_id, created_at)
                 VALUES (:oid,:st,:n,:atype,:aid,NOW())'
            )->execute([
                'oid' => $id,
                'st' => 'saiu_entrega',
                'n' => 'Motoboy atribuído #' . $mid,
                'atype' => 'operator',
                'aid' => (int) $_SESSION['admin_id'],
            ]);

            $pdo->commit();
        } catch (\Throwable) {
            $pdo->rollBack();
            Redirect::to('/operador');
        }

        OrderService::notifyStatusSms($id, 'saiu_entrega');

        try {
            \App\Services\GeocodingService::ensureOrderCoordinates($id);
        } catch (\Throwable) {
        }

        $phone = trim((string) ($motoboy['phone'] ?? ''));
        if ($phone !== '') {
            $orderNo = (string) ($order['order_number'] ?? $id);
            $msg = 'Desk Food: nova entrega atribuída — Pedido #' . $orderNo
                . '. Abra seu painel do entregador para ver o endereço e iniciar a rota.';
            $waUrl = WhatsAppLinkService::url($phone, $msg);
            if ($waUrl !== null) {
                $_SESSION['whatsapp_assign_flash'] = [
                    'name' => (string) ($motoboy['name'] ?? 'Motoboy'),
                    'url' => $waUrl,
                    'order_number' => $orderNo,
                ];
            }
        }

        Redirect::to('/operador');
    }

    /**
     * Comanda de cozinha para impressão (layout minimal, sem sidebar).
     */
    public function print(int $id): void
    {
        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $pdo = Database::pdo();
        $o = $pdo->prepare('SELECT o.*, u.name AS unit_name FROM orders o INNER JOIN units u ON u.id = o.unit_id WHERE o.id = :id AND o.unit_id = :u LIMIT 1');
        $o->execute(['id' => $id, 'u' => $unitId]);
        $order = $o->fetch(\PDO::FETCH_ASSOC);
        if ($order === false) {
            http_response_code(404);
            echo 'Pedido não encontrado.';

            return;
        }

        $items = $pdo->prepare('SELECT product_name, quantity, unit_price, line_total FROM order_items WHERE order_id = :id ORDER BY id ASC');
        $items->execute(['id' => $id]);
        $lines = $items->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->view('operator/print_order', [
            'order' => $order,
            'items' => $lines,
            'title' => 'Comanda #' . ($order['order_number'] ?? ''),
        ], null);
    }
}
