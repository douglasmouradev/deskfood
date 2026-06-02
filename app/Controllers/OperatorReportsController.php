<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;

/**
 * Relatórios exportáveis para a unidade (operador).
 */
final class OperatorReportsController extends Controller
{
    /**
     * Exporta pedidos dos últimos 90 dias em CSV (UTF-8 com BOM para Excel).
     */
    public function ordersCsv(): void
    {
        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        if ($unitId <= 0) {
            http_response_code(403);
            return;
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT order_number, status, payment_method, payment_status, customer_name, customer_phone, total, created_at
             FROM orders
             WHERE unit_id = :u AND deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
             ORDER BY created_at DESC'
        );
        $stmt->execute(['u' => $unitId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $filename = 'deskfood-pedidos-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }

        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Pedido', 'Status', 'Pagamento', 'Status pag.', 'Cliente', 'Telefone', 'Total', 'Criado em'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                (string) $r['order_number'],
                (string) $r['status'],
                (string) $r['payment_method'],
                (string) $r['payment_status'],
                (string) $r['customer_name'],
                (string) $r['customer_phone'],
                number_format((float) $r['total'], 2, ',', '.'),
                (string) $r['created_at'],
            ], ';');
        }
        fclose($out);
        exit;
    }
}
