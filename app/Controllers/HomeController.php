<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;

/**
 * Página inicial com listagem de unidades ativas para o cliente escolher.
 */
final class HomeController extends Controller
{
    /**
     * Exibe vitrine das unidades disponíveis para delivery.
     */
    public function index(): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query(
            'SELECT id, name, slug, city, delivery_fee, logo_path FROM units
             WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name ASC'
        );
        $units = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $stats = [
            'units_total' => (int) $pdo->query('SELECT COUNT(*) FROM units WHERE deleted_at IS NULL')->fetchColumn(),
            'units_active' => (int) $pdo->query('SELECT COUNT(*) FROM units WHERE is_active = 1 AND deleted_at IS NULL')->fetchColumn(),
            'orders_today' => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL")->fetchColumn(),
        ];
        $app = require BASE_PATH . '/config/app.php';
        $this->view('home', [
            'units' => $units,
            'stats' => $stats,
            'title' => 'Desk Food — Delivery',
            'metaDescription' => (string) ($app['default_meta_description'] ?? ''),
            'canonicalPath' => '/',
        ], 'public');
    }
}
