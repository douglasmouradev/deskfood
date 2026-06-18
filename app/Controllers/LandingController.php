<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\DbDate;

/**
 * Landing de marketing (vitrine do produto), separada da home operacional de unidades.
 */
final class LandingController extends Controller
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $stats = [
            'units_active' => (int) $pdo->query('SELECT COUNT(*) FROM units WHERE is_active = 1 AND deleted_at IS NULL')->fetchColumn(),
            'orders_today' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE ' . DbDate::todayWhere() . ' AND deleted_at IS NULL')->fetchColumn(),
        ];

        $this->view('landing/index', [
            'title' => 'Desk Food — Delivery próprio para restaurantes',
            'metaDescription' => 'Canal de pedidos com sua marca: cardápio online, PIX com confirmação automática e painel da cozinha. Sem comissão por pedido.',
            'csrf' => \App\Helpers\Csrf::token(),
            'stats' => $stats,
            'demoSlug' => 'centro',
        ], 'landing');
    }
}
