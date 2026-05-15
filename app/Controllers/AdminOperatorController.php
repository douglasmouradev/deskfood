<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\AuditLogService;

/**
 * CRUD de operadores (admins com role unit_operator).
 */
final class AdminOperatorController extends Controller
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $rows = $pdo->query(
            "SELECT a.*, u.name AS unit_name FROM admins a
             LEFT JOIN units u ON u.id = a.unit_id
             WHERE a.role = 'unit_operator' AND a.deleted_at IS NULL
             ORDER BY a.id DESC"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->view('admin/operators_index', [
            'operators' => $rows,
            'csrf' => Csrf::token(),
            'title' => 'Operadores',
        ], 'admin');
    }

    public function createForm(): void
    {
        $pdo = Database::pdo();
        $units = $pdo->query('SELECT id, name FROM units WHERE deleted_at IS NULL AND is_active = 1 ORDER BY name')->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $this->view('admin/operators_form', [
            'operator' => null,
            'units' => $units,
            'csrf' => Csrf::token(),
            'title' => 'Novo operador',
        ], 'admin');
    }

    public function createSave(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin/operadores/nova');
        }

        $name = trim((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW));
        $email = strtolower(trim((string) filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW)));
        $phone = trim((string) filter_input(INPUT_POST, 'phone', FILTER_UNSAFE_RAW));
        $unitId = (int) filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT);
        $password = (string) filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

        if ($name === '' || $email === '' || $unitId <= 0 || strlen($password) < 8) {
            $_SESSION['flash_error'] = 'Preencha nome, e-mail, unidade e senha (mín. 8 caracteres).';
            Redirect::to('/admin/operadores/nova');
        }

        $pdo = Database::pdo();
        $chk = $pdo->prepare('SELECT id FROM admins WHERE email = :e AND deleted_at IS NULL LIMIT 1');
        $chk->execute(['e' => $email]);
        if ($chk->fetch() !== false) {
            $_SESSION['flash_error'] = 'E-mail já cadastrado.';
            Redirect::to('/admin/operadores/nova');
        }

        $pdo->prepare(
            "INSERT INTO admins (name, email, password_hash, role, unit_id, phone, is_active, created_at, updated_at)
             VALUES (:n,:e,:ph,'unit_operator',:u,:phone,1,NOW(),NOW())"
        )->execute([
            'n' => $name,
            'e' => $email,
            'ph' => password_hash($password, PASSWORD_DEFAULT),
            'u' => $unitId,
            'phone' => $phone,
        ]);

        $adminId = (int) $_SESSION['admin_id'];
        AuditLogService::record('admin', $adminId, 'operator.create', 'admin', (int) $pdo->lastInsertId(), ['email' => $email, 'unit_id' => $unitId]);

        Redirect::to('/admin/operadores');
    }

    public function toggle(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin/operadores');
        }

        $pdo = Database::pdo();
        $pdo->prepare(
            "UPDATE admins SET is_active = 1 - is_active, updated_at = NOW()
             WHERE id = :id AND role = 'unit_operator' AND deleted_at IS NULL"
        )->execute(['id' => $id]);

        AuditLogService::record('admin', (int) ($_SESSION['admin_id'] ?? 0), 'operator.toggle', 'admin', $id, []);

        Redirect::to('/admin/operadores');
    }
}
