<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;

/**
 * Listagem e exportação de leads da landing.
 */
final class AdminLeadController extends Controller
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $rows = $pdo->query(
            'SELECT * FROM leads ORDER BY created_at DESC LIMIT 200'
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->view('admin/leads_index', [
            'leads' => $rows,
            'csrf' => Csrf::token(),
            'title' => 'Leads comerciais',
        ], 'admin');
    }

    public function exportCsv(): void
    {
        if (!Csrf::validate()) {
            header('Location: /admin/leads');
            exit;
        }

        $pdo = Database::pdo();
        $rows = $pdo->query('SELECT * FROM leads ORDER BY created_at DESC')->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="leads-deskfood.csv"');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }

        fputcsv($out, ['id', 'name', 'email', 'phone', 'company', 'message', 'source', 'created_at'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'] ?? '',
                $r['name'] ?? '',
                $r['email'] ?? '',
                $r['phone'] ?? '',
                $r['company'] ?? '',
                $r['message'] ?? '',
                $r['source'] ?? '',
                $r['created_at'] ?? '',
            ], ';');
        }
        fclose($out);
        exit;
    }
}
