<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Services\AuditLogService;

/**
 * Relatórios globais do dono.
 */
final class AdminReportsController extends Controller
{
    public function stats(): void
    {
        $pdo = Database::pdo();
        $ordersToday = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL")->fetchColumn();
        $revenueToday = (float) $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status IN ('pago','confirmado_entrega') AND deleted_at IS NULL")->fetchColumn();
        $byDay = $pdo->query(
            "SELECT DATE(created_at) AS d, COUNT(*) AS c, COALESCE(SUM(total),0) AS rev
             FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND deleted_at IS NULL
             GROUP BY DATE(created_at) ORDER BY d ASC"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->view('admin/reports', [
            'orders_today' => $ordersToday,
            'revenue_today' => $revenueToday,
            'by_day' => $byDay,
            'csrf' => Csrf::token(),
            'title' => 'Relatórios',
        ], 'admin');
    }

    public function ordersCsv(): void
    {
        if (!Csrf::validate()) {
            header('Location: /admin');
            exit;
        }

        AuditLogService::record('admin', (int) ($_SESSION['admin_id'] ?? 0), 'report.orders_csv', null, null, []);

        $pdo = Database::pdo();
        $rows = $pdo->query(
            "SELECT o.order_number, o.status, o.payment_status, o.total, o.delivery_type,
                    u.name AS unit_name, o.customer_name, o.customer_phone, o.created_at
             FROM orders o
             INNER JOIN units u ON u.id = o.unit_id
             WHERE o.deleted_at IS NULL
             ORDER BY o.created_at DESC
             LIMIT 5000"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="pedidos-deskfood.csv"');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        fputcsv($out, ['pedido', 'status', 'pagamento', 'total', 'tipo', 'unidade', 'cliente', 'telefone', 'criado_em'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['order_number'] ?? '',
                $r['status'] ?? '',
                $r['payment_status'] ?? '',
                $r['total'] ?? '',
                $r['delivery_type'] ?? 'delivery',
                $r['unit_name'] ?? '',
                $r['customer_name'] ?? '',
                $r['customer_phone'] ?? '',
                $r['created_at'] ?? '',
            ], ';');
        }
        fclose($out);
        exit;
    }
}
