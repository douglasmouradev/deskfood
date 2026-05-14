<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;

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

        $this->view('customer/track', ['order' => $order, 'token' => $token, 'title' => 'Acompanhar pedido'], 'public');
    }

    /**
     * Endpoint JSON consumido pelo polling a cada 15 segundos.
     */
    public function poll(string $token): void
    {
        $order = $this->loadOrderByToken($token);
        if ($order === null) {
            $this->json(['ok' => false, 'message' => 'Não encontrado'], 404);

            return;
        }

        $this->json([
            'ok' => true,
            'status' => $order['status'],
            'payment_status' => $order['payment_status'],
            'motoboy' => $order['motoboy_name'] ? [
                'name' => $order['motoboy_name'],
                'photo' => $order['motoboy_photo'],
            ] : null,
            'updated_at' => $order['updated_at'],
        ]);
    }

    /**
     * Carrega pedido com joins opcionais do motoboy ativo.
     *
     * @return array<string, mixed>|null
     */
    private function loadOrderByToken(string $token): ?array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT o.*, m.name AS motoboy_name, m.photo_path AS motoboy_photo
             FROM orders o
             LEFT JOIN deliveries d ON d.order_id = o.id
             LEFT JOIN motoboys m ON m.id = d.motoboy_id
             WHERE o.tracking_token = :t AND o.deleted_at IS NULL
             LIMIT 1'
        );
        $st->execute(['t' => $token]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }
}
