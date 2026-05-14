<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\OrderService;

/**
 * Checkout: endereço, pagamento e criação do pedido.
 */
final class CustomerCheckoutController extends Controller
{
    /**
     * Formulário de endereço e forma de pagamento.
     */
    public function form(): void
    {
        $cart = $_SESSION['cart'] ?? null;
        if (!is_array($cart) || empty($cart['items'])) {
            Redirect::to('/cliente/carrinho');
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM units WHERE id = :id AND is_active = 1 LIMIT 1');
        $st->execute(['id' => (int) $cart['unit_id']]);
        $unit = $st->fetch(\PDO::FETCH_ASSOC);
        if ($unit === false) {
            Redirect::to('/');
        }

        $user = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $user->execute(['id' => (int) $_SESSION['user_id']]);
        $u = $user->fetch(\PDO::FETCH_ASSOC) ?: [];

        $this->view('customer/checkout', [
            'cart' => $cart,
            'unit' => $unit,
            'user' => $u,
            'csrf' => Csrf::token(),
            'title' => 'Checkout',
        ], 'customer');
    }

    /**
     * Persiste pedido e redireciona para pagamento PIX ou confirmação na entrega.
     */
    public function submit(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/cliente/checkout');
        }

        $cart = $_SESSION['cart'] ?? null;
        if (!is_array($cart) || empty($cart['items'])) {
            Redirect::to('/cliente/carrinho');
        }

        $userId = (int) $_SESSION['user_id'];
        $pdo = Database::pdo();
        $user = $pdo->prepare('SELECT name, phone FROM users WHERE id = :id LIMIT 1');
        $user->execute(['id' => $userId]);
        $u = $user->fetch(\PDO::FETCH_ASSOC);
        if ($u === false) {
            Redirect::to('/cliente/login');
        }

        $delivery = [
            'street' => trim((string) filter_input(INPUT_POST, 'delivery_street', FILTER_UNSAFE_RAW)),
            'number' => trim((string) filter_input(INPUT_POST, 'delivery_number', FILTER_UNSAFE_RAW)),
            'complement' => trim((string) (filter_input(INPUT_POST, 'delivery_complement', FILTER_UNSAFE_RAW) ?: '')),
            'neighborhood' => trim((string) filter_input(INPUT_POST, 'delivery_neighborhood', FILTER_UNSAFE_RAW)),
            'city' => trim((string) filter_input(INPUT_POST, 'delivery_city', FILTER_UNSAFE_RAW)),
            'state' => strtoupper(trim((string) filter_input(INPUT_POST, 'delivery_state', FILTER_UNSAFE_RAW))),
            'zip' => trim((string) filter_input(INPUT_POST, 'delivery_zip', FILTER_UNSAFE_RAW)),
            'notes' => trim((string) (filter_input(INPUT_POST, 'notes', FILTER_UNSAFE_RAW) ?: '')),
        ];

        foreach (['street', 'number', 'neighborhood', 'city', 'state', 'zip'] as $req) {
            if ($delivery[$req] === '') {
                $_SESSION['flash_error'] = 'Preencha o endereço completo.';
                Redirect::to('/cliente/checkout');
            }
        }

        $paymentMethod = (string) filter_input(INPUT_POST, 'payment_method', FILTER_UNSAFE_RAW);
        $payment = ['payment_method' => $paymentMethod];
        if ($paymentMethod === 'on_delivery') {
            $payment['on_delivery_type'] = (string) filter_input(INPUT_POST, 'on_delivery_type', FILTER_UNSAFE_RAW);
            $cf = filter_input(INPUT_POST, 'change_for', FILTER_VALIDATE_FLOAT);
            $payment['change_for'] = $cf !== false && $cf !== null ? (float) $cf : null;
        }

        try {
            $created = OrderService::createFromCart(
                $userId,
                $cart,
                $delivery,
                $payment,
                ['name' => (string) $u['name'], 'phone' => (string) $u['phone']]
            );
        } catch (\Throwable) {
            $_SESSION['flash_error'] = 'Não foi possível concluir o pedido.';
            Redirect::to('/cliente/checkout');
        }

        $_SESSION['cart'] = ['unit_id' => (int) $cart['unit_id'], 'items' => []];

        if ($paymentMethod === 'pix') {
            Redirect::to('/cliente/pedido/' . $created['order_id'] . '/pix');
        }

        Redirect::to('/acompanhar/' . $created['tracking_token'] . '?ok=1');
    }
}
