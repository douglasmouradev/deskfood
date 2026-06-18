<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Services\BusinessHoursService;
use App\Services\CatalogBatchLoader;
use App\Services\CatalogCacheService;

/**
 * Cardápio público da unidade selecionada pelo slug.
 */
final class CustomerMenuController extends Controller
{
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
        $cached = CatalogCacheService::remember($cid, static function () use ($pdo, $cid): array {
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

            $productIds = array_map(static fn (array $pr): int => (int) $pr['id'], $rows);
            $byProduct = CatalogBatchLoader::addonsGroupedByProduct($pdo, $productIds);

            return [
                'categories' => $categories,
                'products' => $rows,
                'addonsByProduct' => $byProduct,
            ];
        });

        $isOpen = BusinessHoursService::isOpen($unit);
        $hoursLabel = BusinessHoursService::statusLabel($unit);
        $layout = empty($_SESSION['user_id']) ? 'customer_guest' : 'customer';

        $this->view('customer/menu', [
            'unit' => $unit,
            'categories' => $cached['categories'],
            'products' => $cached['products'],
            'addonsByProduct' => $cached['addonsByProduct'],
            'cart' => $_SESSION['cart'] ?? null,
            'unitOpen' => $isOpen,
            'hoursLabel' => $hoursLabel,
            'title' => (string) $unit['name'],
        ], $layout);
    }
}
