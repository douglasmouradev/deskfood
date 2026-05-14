<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;

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

        $this->view('customer/order_pix', ['order' => $row, 'title' => 'Pagamento PIX'], 'customer');
    }
}
