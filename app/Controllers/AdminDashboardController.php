<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;

/**
 * Dashboard consolidado do dono com métricas simples.
 */
final class AdminDashboardController extends Controller
{
    /**
     * Cards de resumo de pedidos e unidades ativas.
     */
    public function index(): void
    {
        $pdo = Database::pdo();
        $units = (int) $pdo->query('SELECT COUNT(*) FROM units WHERE deleted_at IS NULL')->fetchColumn();
        $activeUnits = (int) $pdo->query('SELECT COUNT(*) FROM units WHERE is_active = 1 AND deleted_at IS NULL')->fetchColumn();
        $ordersToday = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL")->fetchColumn();
        $revenue = (float) $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status IN ('pago','confirmado_entrega') AND deleted_at IS NULL")->fetchColumn();
        $leadsCount = 0;
        $operatorsCount = 0;
        $avgRating = null;
        try {
            $leadsCount = (int) $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
            $operatorsCount = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'unit_operator' AND deleted_at IS NULL")->fetchColumn();
            $avgRating = $pdo->query('SELECT ROUND(AVG(stars),1) FROM order_ratings')->fetchColumn();
        } catch (\Throwable) {
            // migrations opcionais ainda não aplicadas
        }

        $this->view('admin/dashboard', [
            'units' => $units,
            'active_units' => $activeUnits,
            'orders_today' => $ordersToday,
            'revenue_total' => $revenue,
            'leads_count' => $leadsCount,
            'operators_count' => $operatorsCount,
            'avg_rating' => $avgRating !== false && $avgRating !== null ? (float) $avgRating : null,
            'show_setup_hint' => $units === 0 || $activeUnits === 0,
            'title' => 'Dashboard',
        ], 'admin');
    }
}
