<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\AuditLogService;
use App\Services\CatalogCacheService;
use App\Services\SecretVault;
use App\Services\UnitPaymentConfig;
use App\Services\UnitPaymentConfig;

/**
 * CRUD de unidades exclusivo do super admin.
 */
final class AdminUnitController extends Controller
{
    /**
     * Lista unidades cadastradas.
     */
    public function index(): void
    {
        $pdo = Database::pdo();
        $rows = $pdo->query('SELECT * FROM units WHERE deleted_at IS NULL ORDER BY id DESC')->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $this->view('admin/units_index', ['units' => $rows, 'csrf' => Csrf::token(), 'title' => 'Unidades'], 'admin');
    }

    /**
     * Formulário de criação de unidade.
     */
    public function createForm(): void
    {
        $this->view('admin/units_form', ['unit' => null, 'csrf' => Csrf::token(), 'title' => 'Nova unidade'], 'admin');
    }

    /**
     * Persiste nova unidade com slug único.
     */
    public function createSave(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin/unidades/nova');
        }

        $slug = $this->slugify((string) filter_input(INPUT_POST, 'slug', FILTER_UNSAFE_RAW));
        if ($slug === '') {
            $slug = $this->slugify((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW));
        }

        $pdo = Database::pdo();
        $pay = self::paymentFieldsFromPost();
        $ins = $pdo->prepare(
            'INSERT INTO units (name, slug, cnpj, address_street, address_number, address_complement, neighborhood, city, state, zip, phone, delivery_radius_km, delivery_fee, minimum_order, business_hours, payment_provider, payment_pix_enabled, payment_card_enabled, pix_key, mp_access_token, mp_public_key, efi_client_id, efi_client_secret, efi_sandbox, is_active, created_at, updated_at)
             VALUES (:name,:slug,:cnpj,:st,:num,:comp,:nei,:city,:state,:zip,:phone,:rad,:fee,:min,:bh,:pp,:ppe,:pce,:pk,:mpat,:mppk,:efiid,:efisec,:efisb,1,NOW(),NOW())'
        );
        $ins->execute(array_merge([
            'name' => trim((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW)),
            'slug' => $slug,
            'cnpj' => trim((string) filter_input(INPUT_POST, 'cnpj', FILTER_UNSAFE_RAW)),
            'st' => trim((string) filter_input(INPUT_POST, 'address_street', FILTER_UNSAFE_RAW)),
            'num' => trim((string) filter_input(INPUT_POST, 'address_number', FILTER_UNSAFE_RAW)),
            'comp' => trim((string) (filter_input(INPUT_POST, 'address_complement', FILTER_UNSAFE_RAW) ?: '')),
            'nei' => trim((string) filter_input(INPUT_POST, 'neighborhood', FILTER_UNSAFE_RAW)),
            'city' => trim((string) filter_input(INPUT_POST, 'city', FILTER_UNSAFE_RAW)),
            'state' => strtoupper(trim((string) filter_input(INPUT_POST, 'state', FILTER_UNSAFE_RAW))),
            'zip' => trim((string) filter_input(INPUT_POST, 'zip', FILTER_UNSAFE_RAW)),
            'phone' => trim((string) filter_input(INPUT_POST, 'phone', FILTER_UNSAFE_RAW)),
            'rad' => (float) filter_input(INPUT_POST, 'delivery_radius_km', FILTER_VALIDATE_FLOAT),
            'fee' => (float) filter_input(INPUT_POST, 'delivery_fee', FILTER_VALIDATE_FLOAT),
            'min' => max(0, (float) filter_input(INPUT_POST, 'minimum_order', FILTER_VALIDATE_FLOAT)),
            'bh' => (string) filter_input(INPUT_POST, 'business_hours', FILTER_UNSAFE_RAW),
        ], $pay));
        UnitPaymentConfig::clearCache();

        AuditLogService::record('admin', (int) ($_SESSION['admin_id'] ?? 0), 'unit.create', 'unit', (int) $pdo->lastInsertId(), [
            'slug' => $slug,
        ]);

        Redirect::to('/admin/unidades');
    }

    /**
     * Alterna flag `is_active` da unidade.
     */
    public function editForm(int $id): void
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM units WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $st->execute(['id' => $id]);
        $unit = $st->fetch(\PDO::FETCH_ASSOC);
        if ($unit === false) {
            Redirect::to('/admin/unidades');
        }

        $this->view('admin/units_form', [
            'unit' => $unit,
            'csrf' => Csrf::token(),
            'title' => 'Editar unidade',
        ], 'admin');
    }

    public function editSave(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin/unidades/' . $id . '/editar');
        }

        $pdo = Database::pdo();
        $cur = $pdo->prepare('SELECT mp_access_token, efi_client_secret FROM units WHERE id = :id LIMIT 1');
        $cur->execute(['id' => $id]);
        $existing = $cur->fetch(\PDO::FETCH_ASSOC) ?: [];
        $pay = self::paymentFieldsFromPost(true);
        if ($pay['mpat'] === null) {
            $pay['mpat'] = $existing['mp_access_token'] ?? null;
        }
        if ($pay['efisec'] === null) {
            $pay['efisec'] = $existing['efi_client_secret'] ?? null;
        }
        $pdo->prepare(
            'UPDATE units SET
                name = :name, cnpj = :cnpj, address_street = :st, address_number = :num,
                address_complement = :comp, neighborhood = :nei, city = :city, state = :state, zip = :zip,
                phone = :phone, delivery_radius_km = :rad, delivery_fee = :fee, minimum_order = :min,
                business_hours = :bh,
                payment_provider = :pp, payment_pix_enabled = :ppe, payment_card_enabled = :pce,
                pix_key = :pk, mp_access_token = :mpat, mp_public_key = :mppk,
                efi_client_id = :efiid, efi_client_secret = :efisec, efi_sandbox = :efisb,
                updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL'
        )->execute(array_merge([
            'id' => $id,
            'name' => trim((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW)),
            'cnpj' => trim((string) filter_input(INPUT_POST, 'cnpj', FILTER_UNSAFE_RAW)),
            'st' => trim((string) filter_input(INPUT_POST, 'address_street', FILTER_UNSAFE_RAW)),
            'num' => trim((string) filter_input(INPUT_POST, 'address_number', FILTER_UNSAFE_RAW)),
            'comp' => trim((string) (filter_input(INPUT_POST, 'address_complement', FILTER_UNSAFE_RAW) ?: '')),
            'nei' => trim((string) filter_input(INPUT_POST, 'neighborhood', FILTER_UNSAFE_RAW)),
            'city' => trim((string) filter_input(INPUT_POST, 'city', FILTER_UNSAFE_RAW)),
            'state' => strtoupper(trim((string) filter_input(INPUT_POST, 'state', FILTER_UNSAFE_RAW))),
            'zip' => trim((string) filter_input(INPUT_POST, 'zip', FILTER_UNSAFE_RAW)),
            'phone' => trim((string) filter_input(INPUT_POST, 'phone', FILTER_UNSAFE_RAW)),
            'rad' => (float) filter_input(INPUT_POST, 'delivery_radius_km', FILTER_VALIDATE_FLOAT),
            'fee' => (float) filter_input(INPUT_POST, 'delivery_fee', FILTER_VALIDATE_FLOAT),
            'min' => max(0, (float) filter_input(INPUT_POST, 'minimum_order', FILTER_VALIDATE_FLOAT)),
            'bh' => (string) filter_input(INPUT_POST, 'business_hours', FILTER_UNSAFE_RAW),
        ], $pay));
        UnitPaymentConfig::clearCache();
        CatalogCacheService::bust($id);

        AuditLogService::record('admin', (int) ($_SESSION['admin_id'] ?? 0), 'unit.update', 'unit', $id, []);

        Redirect::to('/admin/unidades');
    }

    public function toggle(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin/unidades');
        }

        $pdo = Database::pdo();
        $pdo->prepare('UPDATE units SET is_active = 1 - is_active, updated_at = NOW() WHERE id = :id')->execute(['id' => $id]);
        AuditLogService::record('admin', (int) ($_SESSION['admin_id'] ?? 0), 'unit.toggle', 'unit', $id, []);
        Redirect::to('/admin/unidades');
    }

    /**
     * Normaliza texto para slug de URL.
     */
    /** @return array<string, mixed> */
    private static function paymentFieldsFromPost(bool $optionalSecrets = false): array
    {
        $provider = trim((string) filter_input(INPUT_POST, 'payment_provider', FILTER_UNSAFE_RAW));
        $efiSandbox = filter_input(INPUT_POST, 'efi_sandbox', FILTER_VALIDATE_BOOL);

        return [
            'pp' => $provider !== '' ? $provider : null,
            'ppe' => filter_input(INPUT_POST, 'payment_pix_enabled', FILTER_VALIDATE_BOOL) ? 1 : 0,
            'pce' => filter_input(INPUT_POST, 'payment_card_enabled', FILTER_VALIDATE_BOOL) ? 1 : 0,
            'pk' => self::nullIfEmpty((string) filter_input(INPUT_POST, 'pix_key', FILTER_UNSAFE_RAW)),
            'mpat' => self::secretFromPost('mp_access_token', $optionalSecrets),
            'mppk' => self::nullIfEmpty((string) filter_input(INPUT_POST, 'mp_public_key', FILTER_UNSAFE_RAW)),
            'efiid' => self::nullIfEmpty((string) filter_input(INPUT_POST, 'efi_client_id', FILTER_UNSAFE_RAW)),
            'efisec' => self::secretFromPost('efi_client_secret', $optionalSecrets),
            'efisb' => $efiSandbox === null ? null : ($efiSandbox ? 1 : 0),
        ];
    }

    private static function secretFromPost(string $field, bool $optional): ?string
    {
        $raw = trim((string) filter_input(INPUT_POST, $field, FILTER_UNSAFE_RAW));
        if ($raw === '' || str_starts_with($raw, 'enc:v1:')) {
            return $optional ? null : null;
        }

        return SecretVault::seal($raw);
    }

    private static function nullIfEmpty(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text;
        $text = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $text));
        $text = trim($text, '-');

        return substr($text, 0, 120);
    }
}
