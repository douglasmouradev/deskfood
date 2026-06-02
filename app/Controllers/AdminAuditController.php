<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;

/**
 * Visualização somente leitura dos logs de auditoria.
 */
final class AdminAuditController extends Controller
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $rows = $pdo->query(
            'SELECT * FROM audit_logs ORDER BY id DESC LIMIT 150'
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->view('admin/audit_index', [
            'logs' => $rows,
            'title' => 'Auditoria',
        ], 'admin');
    }
}
