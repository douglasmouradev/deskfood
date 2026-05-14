<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;

/**
 * Cardápio público da unidade selecionada pelo slug.
 */
final class CustomerMenuController extends Controller
{
    /**
     * Lista categorias e produtos ativos da unidade.
     */
    public function index(string $slug): void
    {
        $pdo = Database::pdo();
        $u = $pdo->prepare('SELECT * FROM units WHERE slug = :s AND is_active = 1 AND deleted_at IS NULL LIMIT 1');
        $u->execute(['s' => $slug]);
        $unit = $u->fetch(\PDO::FETCH_ASSOC);
        if ($unit === false) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Unidade não encontrada'], 'public');

            return;
        }

        $cid = (int) $unit['id'];
        $cats = $pdo->prepare(
            'SELECT * FROM categories WHERE unit_id = :u AND is_active = 1 AND deleted_at IS NULL ORDER BY sort_order ASC, id ASC'
        );
        $cats->execute(['u' => $cid]);
        $categories = $cats->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $products = $pdo->prepare(
            'SELECT p.*, c.name AS category_name FROM products p
             INNER JOIN categories c ON c.id = p.category_id
             WHERE p.unit_id = :u AND p.status = "active" AND p.deleted_at IS NULL
             ORDER BY c.sort_order ASC, p.sort_order ASC'
        );
        $products->execute(['u' => $cid]);
        $rows = $products->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $addonsStmt = $pdo->prepare(
            'SELECT * FROM product_addons WHERE product_id = :pid AND is_active = 1 AND deleted_at IS NULL ORDER BY sort_order ASC'
        );
        $byProduct = [];
        foreach ($rows as $pr) {
            $pid = (int) $pr['id'];
            if (!isset($byProduct[$pid])) {
                $addonsStmt->execute(['pid' => $pid]);
                $byProduct[$pid] = $addonsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            }
        }

        $this->view('customer/menu', [
            'unit' => $unit,
            'categories' => $categories,
            'products' => $rows,
            'addonsByProduct' => $byProduct,
            'cart' => $_SESSION['cart'] ?? null,
            'title' => (string) $unit['name'],
        ], 'customer');
    }
}
