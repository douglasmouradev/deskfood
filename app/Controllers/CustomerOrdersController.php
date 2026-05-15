<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\OrderService;

/**
 * Lista de pedidos do cliente autenticado.
 */
final class CustomerOrdersController extends Controller
{
    /**
     * Histórico resumido com links de acompanhamento e pagamento PIX.
     */
    public function index(): void
    {
        $uid = (int) $_SESSION['user_id'];
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT o.*, u.name AS unit_name FROM orders o
             INNER JOIN units u ON u.id = o.unit_id
             WHERE o.user_id = :u AND o.deleted_at IS NULL
             ORDER BY o.created_at DESC LIMIT 50'
        );
        $st->execute(['u' => $uid]);
        $orders = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->view('customer/orders', ['orders' => $orders, 'title' => 'Meus pedidos'], 'customer');
    }

    /**
     * Tela com QR Code / copia e cola para pagamento PIX do pedido.
     */
    public function pix(int $id): void
    {
        $uid = (int) $_SESSION['user_id'];
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT o.*, pt.copy_paste, pt.qr_code_payload, pt.expires_at, pt.status AS pix_status, p.status AS pay_status
             FROM orders o
             INNER JOIN payments p ON p.order_id = o.id AND p.type = "pix"
             LEFT JOIN pix_transactions pt ON pt.payment_id = p.id
             WHERE o.id = :id AND o.user_id = :u LIMIT 1'
        );
        $st->execute(['id' => $id, 'u' => $uid]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Pedido não encontrado'], 'customer');

            return;
        }

        $config = require BASE_PATH . '/config/app.php';
        $this->view('customer/order_pix', [
            'order' => $row,
            'title' => 'Pagamento PIX',
            'appUrl' => (string) ($config['url'] ?? ''),
        ], 'customer');
    }

    /**
     * Poll JSON para a tela PIX detectar pagamento confirmado.
     */
    public function pixStatus(int $id): void
    {
        $uid = (int) $_SESSION['user_id'];
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT o.payment_status, o.status, pt.expires_at
             FROM orders o
             INNER JOIN payments p ON p.order_id = o.id AND p.type = "pix"
             LEFT JOIN pix_transactions pt ON pt.payment_id = p.id
             WHERE o.id = :id AND o.user_id = :u LIMIT 1'
        );
        $st->execute(['id' => $id, 'u' => $uid]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            $this->json(['ok' => false], 404);

            return;
        }

        $this->json([
            'ok' => true,
            'payment_status' => (string) $row['payment_status'],
            'status' => (string) $row['status'],
            'expires_at' => (string) ($row['expires_at'] ?? ''),
        ]);
    }

    /**
     * Repete itens de um pedido anterior no carrinho.
     */
    public function reorder(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/cliente/pedidos');
        }

        $uid = (int) $_SESSION['user_id'];
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT o.id, o.unit_id FROM orders o WHERE o.id = :id AND o.user_id = :u AND o.deleted_at IS NULL LIMIT 1'
        );
        $st->execute(['id' => $id, 'u' => $uid]);
        $order = $st->fetch(\PDO::FETCH_ASSOC);
        if ($order === false) {
            Redirect::to('/cliente/pedidos');
        }

        $items = $pdo->prepare(
            'SELECT product_id, quantity FROM order_items WHERE order_id = :oid AND product_id IS NOT NULL'
        );
        $items->execute(['oid' => $id]);
        $rows = $items->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $cartItems = [];
        foreach ($rows as $row) {
            $pid = (int) ($row['product_id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $cartItems[] = [
                'product_id' => $pid,
                'qty' => max(1, (int) ($row['quantity'] ?? 1)),
                'addons' => [],
            ];
        }

        if ($cartItems === []) {
            $_SESSION['flash_error'] = 'Não foi possível repetir este pedido.';
            Redirect::to('/cliente/pedidos');
        }

        $_SESSION['cart'] = ['unit_id' => (int) $order['unit_id'], 'items' => $cartItems];
        Redirect::to('/cliente/carrinho');
    }

    /**
     * Cancela pedido pelo cliente (pendente/confirmado, PIX não pago).
     */
    public function cancel(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/cliente/pedidos');
        }

        $uid = (int) $_SESSION['user_id'];
        $reason = trim((string) filter_input(INPUT_POST, 'reason', FILTER_UNSAFE_RAW));

        try {
            OrderService::cancelByCustomer($id, $uid, $reason !== '' ? $reason : null);
            $_SESSION['flash_ok'] = 'Pedido cancelado.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        Redirect::to('/cliente/pedidos');
    }
}
