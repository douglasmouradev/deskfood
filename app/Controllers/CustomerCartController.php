<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;

/**
 * Carrinho de compras armazenado em sessão (por unidade).
 */
final class CustomerCartController extends Controller
{
    /**
     * Visualização do carrinho atual.
     */
    public function index(): void
    {
        $cart = $_SESSION['cart'] ?? null;
        $pdo = Database::pdo();
        $unit = null;
        if (is_array($cart) && !empty($cart['unit_id'])) {
            $st = $pdo->prepare('SELECT * FROM units WHERE id = :id LIMIT 1');
            $st->execute(['id' => (int) $cart['unit_id']]);
            $unit = $st->fetch(\PDO::FETCH_ASSOC) ?: null;
        }

        $this->view('customer/cart', [
            'cart' => $cart,
            'unit' => $unit,
            'csrf' => Csrf::token(),
            'title' => 'Carrinho',
        ], 'customer');
    }

    /**
     * Adiciona item ao carrinho a partir do cardápio.
     */
    public function add(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/cliente/carrinho');
        }

        $unitId = (int) filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT);
        $productId = (int) filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $qty = max(1, (int) filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT) ?: 1);
        $addons = $_POST['addons'] ?? [];
        if (!is_array($addons)) {
            $addons = [];
        }
        $addons = array_map('intval', $addons);

        if ($unitId <= 0 || $productId <= 0) {
            Redirect::to('/');
        }

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || (int) ($_SESSION['cart']['unit_id'] ?? 0) !== $unitId) {
            $_SESSION['cart'] = ['unit_id' => $unitId, 'items' => []];
        }

        $_SESSION['cart']['items'][] = [
            'product_id' => $productId,
            'qty' => $qty,
            'addons' => $addons,
        ];

        $slug = $this->lookupUnitSlug($unitId);
        Redirect::to('/u/' . $slug . '?added=1');
    }

    /**
     * Atualiza quantidades ou remove itens do carrinho.
     */
    public function update(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/cliente/carrinho');
        }

        $cart = $_SESSION['cart'] ?? null;
        if (!is_array($cart)) {
            Redirect::to('/cliente/carrinho');
        }

        $qtys = $_POST['qty'] ?? [];
        if (!is_array($qtys)) {
            $qtys = [];
        }

        $newItems = [];
        foreach (($cart['items'] ?? []) as $idx => $it) {
            $q = (int) ($qtys[$idx] ?? 0);
            if ($q > 0) {
                $it['qty'] = $q;
                $newItems[] = $it;
            }
        }
        $cart['items'] = $newItems;
        $_SESSION['cart'] = $cart;

        Redirect::to('/cliente/carrinho');
    }

    /**
     * Resolve slug da unidade para redirecionamento pós-adicionar ao carrinho.
     */
    private function lookupUnitSlug(int $unitId): string
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT slug FROM units WHERE id = :id LIMIT 1');
        $st->execute(['id' => $unitId]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);

        return $row !== false ? (string) $row['slug'] : 'centro';
    }
}
