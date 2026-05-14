<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\CashRegisterService;
use App\Services\OrderService;

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

        if ($status === 'cancelado') {
            $reason = trim((string) filter_input(INPUT_POST, 'cancel_reason', FILTER_UNSAFE_RAW));
            $pdo->prepare('UPDATE orders SET status = :s, cancel_reason = :r, updated_at = NOW() WHERE id = :id')
                ->execute(['s' => $status, 'r' => $reason, 'id' => $id]);
        } else {
            $pdo->prepare('UPDATE orders SET status = :s, updated_at = NOW() WHERE id = :id')
                ->execute(['s' => $status, 'id' => $id]);
        }

        if ($status === 'entregue' && ($order['payment_method'] ?? '') === 'on_delivery') {
            $pdo->prepare('UPDATE orders SET payment_status = :ps, updated_at = NOW() WHERE id = :id')
                ->execute(['ps' => 'confirmado_entrega', 'id' => $id]);
            $pdo->prepare('UPDATE payments SET status = :st, updated_at = NOW() WHERE order_id = :oid AND type = "on_delivery"')
                ->execute(['st' => 'pago', 'oid' => $id]);
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
        if ($m->fetch() === false) {
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

        Redirect::to('/operador');
    }
}
