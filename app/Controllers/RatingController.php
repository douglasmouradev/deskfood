<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;

/**
 * Avaliação pública após entrega do pedido.
 */
final class RatingController extends Controller
{
    public function submit(string $token): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/acompanhar/' . $token);
        }

        $stars = (int) filter_input(INPUT_POST, 'stars', FILTER_VALIDATE_INT);
        $comment = trim((string) (filter_input(INPUT_POST, 'comment', FILTER_UNSAFE_RAW) ?: ''));
        if ($stars < 1 || $stars > 5) {
            $_SESSION['flash_error'] = 'Selecione de 1 a 5 estrelas.';
            Redirect::to('/acompanhar/' . $token);
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare(
            "SELECT id, unit_id, status FROM orders WHERE tracking_token = :t AND deleted_at IS NULL LIMIT 1"
        );
        $st->execute(['t' => $token]);
        $order = $st->fetch(\PDO::FETCH_ASSOC);
        if ($order === false || ($order['status'] ?? '') !== 'entregue') {
            $_SESSION['flash_error'] = 'Só é possível avaliar pedidos entregues.';
            Redirect::to('/acompanhar/' . $token);
        }

        $exists = $pdo->prepare('SELECT id FROM order_ratings WHERE order_id = :oid LIMIT 1');
        $exists->execute(['oid' => (int) $order['id']]);
        if ($exists->fetch() !== false) {
            $_SESSION['flash_ok'] = 'Você já avaliou este pedido. Obrigado!';
            Redirect::to('/acompanhar/' . $token);
        }

        $pdo->prepare(
            'INSERT INTO order_ratings (order_id, unit_id, stars, comment, created_at)
             VALUES (:oid,:uid,:s,:c,NOW())'
        )->execute([
            'oid' => (int) $order['id'],
            'uid' => (int) $order['unit_id'],
            's' => $stars,
            'c' => $comment !== '' ? substr($comment, 0, 500) : null,
        ]);

        $_SESSION['flash_ok'] = 'Obrigado pela avaliação!';
        Redirect::to('/acompanhar/' . $token);
    }
}
