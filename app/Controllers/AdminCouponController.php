<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\AuditLogService;

/**
 * Gestão de cupons pelo dono.
 */
final class AdminCouponController extends Controller
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $coupons = [];
        $units = [];
        try {
            $coupons = $pdo->query(
                'SELECT c.*, u.name AS unit_name FROM coupons c LEFT JOIN units u ON u.id = c.unit_id ORDER BY c.id DESC'
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $units = $pdo->query('SELECT id, name FROM units WHERE deleted_at IS NULL ORDER BY name')->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable) {
            // migration pendente
        }

        $this->view('admin/coupons_index', [
            'coupons' => $coupons,
            'units' => $units,
            'csrf' => Csrf::token(),
            'title' => 'Cupons',
        ], 'admin');
    }

    public function create(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin/cupons');
        }

        $code = strtoupper(trim((string) filter_input(INPUT_POST, 'code', FILTER_UNSAFE_RAW)));
        $unitId = filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT);
        $unitId = $unitId !== false && $unitId > 0 ? (int) $unitId : null;
        $type = (string) filter_input(INPUT_POST, 'discount_type', FILTER_UNSAFE_RAW);
        $value = (float) filter_input(INPUT_POST, 'discount_value', FILTER_VALIDATE_FLOAT);
        $min = max(0, (float) filter_input(INPUT_POST, 'min_subtotal', FILTER_VALIDATE_FLOAT));

        if ($code === '' || $value <= 0 || !in_array($type, ['percent', 'fixed'], true)) {
            Redirect::to('/admin/cupons');
        }

        $pdo = Database::pdo();
        $pdo->prepare(
            'INSERT INTO coupons (unit_id, code, discount_type, discount_value, min_subtotal, is_active, created_at, updated_at)
             VALUES (:u,:c,:t,:v,:m,1,NOW(),NOW())'
        )->execute([
            'u' => $unitId,
            'c' => $code,
            't' => $type,
            'v' => $value,
            'm' => $min,
        ]);

        AuditLogService::record('admin', (int) ($_SESSION['admin_id'] ?? 0), 'coupon.create', 'coupon', (int) $pdo->lastInsertId(), [
            'code' => $code,
        ]);

        Redirect::to('/admin/cupons');
    }

    public function update(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin/cupons');
        }

        $value = (float) filter_input(INPUT_POST, 'discount_value', FILTER_VALIDATE_FLOAT);
        $min = max(0, (float) filter_input(INPUT_POST, 'min_subtotal', FILTER_VALIDATE_FLOAT));
        $maxUses = filter_input(INPUT_POST, 'max_uses', FILTER_VALIDATE_INT);
        $maxUses = $maxUses !== false && $maxUses > 0 ? (int) $maxUses : null;

        if ($value <= 0) {
            Redirect::to('/admin/cupons');
        }

        $pdo = Database::pdo();
        $pdo->prepare(
            'UPDATE coupons SET discount_value = :v, min_subtotal = :m, max_uses = :mu, updated_at = NOW() WHERE id = :id'
        )->execute(['v' => $value, 'm' => $min, 'mu' => $maxUses, 'id' => $id]);

        AuditLogService::record('admin', (int) ($_SESSION['admin_id'] ?? 0), 'coupon.update', 'coupon', $id, []);

        Redirect::to('/admin/cupons');
    }

    public function toggle(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin/cupons');
        }

        $pdo = Database::pdo();
        $pdo->prepare('UPDATE coupons SET is_active = 1 - is_active, updated_at = NOW() WHERE id = :id')->execute(['id' => $id]);
        AuditLogService::record('admin', (int) ($_SESSION['admin_id'] ?? 0), 'coupon.toggle', 'coupon', $id, []);
        Redirect::to('/admin/cupons');
    }
}
