<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;

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
        $ins = $pdo->prepare(
            'INSERT INTO units (name, slug, cnpj, address_street, address_number, address_complement, neighborhood, city, state, zip, phone, delivery_radius_km, delivery_fee, business_hours, is_active, created_at, updated_at)
             VALUES (:name,:slug,:cnpj,:st,:num,:comp,:nei,:city,:state,:zip,:phone,:rad,:fee,:bh,1,NOW(),NOW())'
        );
        $ins->execute([
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
            'bh' => (string) filter_input(INPUT_POST, 'business_hours', FILTER_UNSAFE_RAW),
        ]);

        Redirect::to('/admin/unidades');
    }

    /**
     * Alterna flag `is_active` da unidade.
     */
    public function toggle(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin/unidades');
        }

        $pdo = Database::pdo();
        $pdo->prepare('UPDATE units SET is_active = 1 - is_active, updated_at = NOW() WHERE id = :id')->execute(['id' => $id]);
        Redirect::to('/admin/unidades');
    }

    /**
     * Normaliza texto para slug de URL.
     */
    private function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text;
        $text = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $text));
        $text = trim($text, '-');

        return substr($text, 0, 120);
    }
}
