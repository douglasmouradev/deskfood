<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\AddressService;
use App\Services\BusinessHoursService;
use App\Services\CartPersistenceService;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\RateLimitService;
use App\Services\UnitPaymentConfig;

/**
 * Checkout: endereço, pagamento e criação do pedido.
 */
final class CustomerCheckoutController extends Controller
{
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

        $userId = (int) $_SESSION['user_id'];
        $user = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $user->execute(['id' => $userId]);
        $u = $user->fetch(\PDO::FETCH_ASSOC) ?: [];

        $enriched = CartService::enrich($cart, $unit);
        $addresses = AddressService::listForUser($userId);
        $defaultAddress = $addresses[0] ?? null;

        $paymentConfig = UnitPaymentConfig::forUnit((int) $unit['id']);

        $this->view('customer/checkout', [
            'cart' => $cart,
            'unit' => $unit,
            'user' => $u,
            'enriched' => $enriched,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'unitOpen' => BusinessHoursService::isOpen($unit),
            'hoursLabel' => BusinessHoursService::statusLabel($unit),
            'paymentConfig' => $paymentConfig,
            'pixAvailable' => UnitPaymentConfig::supportsPix($paymentConfig),
            'cardAvailable' => UnitPaymentConfig::supportsCard($paymentConfig),
            'csrf' => Csrf::token(),
            'title' => 'Checkout',
        ], 'customer');
    }

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

        $deliveryType = (string) filter_input(INPUT_POST, 'delivery_type', FILTER_UNSAFE_RAW);
        if (!in_array($deliveryType, ['delivery', 'pickup'], true)) {
            $deliveryType = 'delivery';
        }

        $addressId = (int) filter_input(INPUT_POST, 'saved_address_id', FILTER_VALIDATE_INT);
        if ($addressId > 0 && $deliveryType === 'delivery') {
            $saved = AddressService::find($userId, $addressId);
            if ($saved !== null) {
                $delivery = [
                    'street' => (string) $saved['street'],
                    'number' => (string) $saved['number'],
                    'complement' => (string) ($saved['complement'] ?? ''),
                    'neighborhood' => (string) $saved['neighborhood'],
                    'city' => (string) $saved['city'],
                    'state' => (string) $saved['state'],
                    'zip' => (string) $saved['zip'],
                    'notes' => trim((string) (filter_input(INPUT_POST, 'notes', FILTER_UNSAFE_RAW) ?: '')),
                ];
            } else {
                $delivery = $this->deliveryFromPost();
            }
        } else {
            $delivery = $this->deliveryFromPost();
        }

        if ($deliveryType === 'delivery') {
            foreach (['street', 'number', 'neighborhood', 'city', 'state', 'zip'] as $req) {
                if (($delivery[$req] ?? '') === '') {
                    $_SESSION['flash_error'] = 'Preencha o endereço completo.';
                    Redirect::to('/cliente/checkout');
                }
            }
        } else {
            $delivery['notes'] = trim((string) (filter_input(INPUT_POST, 'notes', FILTER_UNSAFE_RAW) ?: ''));
        }

        $unitStmt = $pdo->prepare('SELECT * FROM units WHERE id = :id AND is_active = 1 AND deleted_at IS NULL LIMIT 1');
        $unitStmt->execute(['id' => (int) $cart['unit_id']]);
        $unit = $unitStmt->fetch(\PDO::FETCH_ASSOC);
        if ($unit === false) {
            Redirect::to('/cliente/carrinho');
        }

        $paymentMethod = (string) filter_input(INPUT_POST, 'payment_method', FILTER_UNSAFE_RAW);
        $payment = ['payment_method' => $paymentMethod];
        if ($paymentMethod === 'on_delivery') {
            $payment['on_delivery_type'] = (string) filter_input(INPUT_POST, 'on_delivery_type', FILTER_UNSAFE_RAW);
            $cf = filter_input(INPUT_POST, 'change_for', FILTER_VALIDATE_FLOAT);
            $payment['change_for'] = $cf !== false && $cf !== null ? (float) $cf : null;
        }

        if (!BusinessHoursService::isOpen($unit)) {
            $_SESSION['flash_error'] = 'Esta unidade está fechada no momento.';
            Redirect::to('/cliente/checkout');
        }

        if (RateLimitService::isLimited('checkout', 'checkout:' . $userId, 10, 3600)) {
            $_SESSION['flash_error'] = 'Muitos pedidos em pouco tempo. Aguarde e tente novamente.';
            Redirect::to('/cliente/checkout');
        }
        RateLimitService::hit('checkout', 'checkout:' . $userId);

        $couponCode = trim((string) filter_input(INPUT_POST, 'coupon_code', FILTER_UNSAFE_RAW));
        $saveAddress = filter_input(INPUT_POST, 'save_address', FILTER_VALIDATE_BOOL) ?? false;

        try {
            $created = OrderService::createFromCart(
                $userId,
                $cart,
                $delivery,
                $payment,
                ['name' => (string) $u['name'], 'phone' => (string) $u['phone']],
                [
                    'delivery_type' => $deliveryType,
                    'coupon_code' => $couponCode,
                ]
            );
        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            Redirect::to('/cliente/checkout');
        } catch (\Throwable) {
            $_SESSION['flash_error'] = 'Não foi possível concluir o pedido.';
            Redirect::to('/cliente/checkout');
        }

        if ($saveAddress && $deliveryType === 'delivery') {
            AddressService::saveFromCheckout($userId, $delivery, true);
        }

        $_SESSION['cart'] = ['unit_id' => (int) $cart['unit_id'], 'items' => []];
        CartPersistenceService::clear();

        if ($paymentMethod === 'pix') {
            Redirect::to('/cliente/pedido/' . $created['order_id'] . '/pix');
        }

        if ($paymentMethod === 'card') {
            $url = (string) ($created['checkout_url'] ?? '');
            if ($url !== '') {
                Redirect::to($url);
            }
            Redirect::to('/cliente/pedido/' . $created['order_id'] . '/cartao');
        }

        Redirect::to('/acompanhar/' . $created['tracking_token'] . '?ok=1');
    }

    /** @return array<string,string> */
    private function deliveryFromPost(): array
    {
        return [
            'street' => trim((string) filter_input(INPUT_POST, 'delivery_street', FILTER_UNSAFE_RAW)),
            'number' => trim((string) filter_input(INPUT_POST, 'delivery_number', FILTER_UNSAFE_RAW)),
            'complement' => trim((string) (filter_input(INPUT_POST, 'delivery_complement', FILTER_UNSAFE_RAW) ?: '')),
            'neighborhood' => trim((string) filter_input(INPUT_POST, 'delivery_neighborhood', FILTER_UNSAFE_RAW)),
            'city' => trim((string) filter_input(INPUT_POST, 'delivery_city', FILTER_UNSAFE_RAW)),
            'state' => strtoupper(trim((string) filter_input(INPUT_POST, 'delivery_state', FILTER_UNSAFE_RAW))),
            'zip' => trim((string) filter_input(INPUT_POST, 'delivery_zip', FILTER_UNSAFE_RAW)),
            'notes' => trim((string) (filter_input(INPUT_POST, 'notes', FILTER_UNSAFE_RAW) ?: '')),
        ];
    }
}
