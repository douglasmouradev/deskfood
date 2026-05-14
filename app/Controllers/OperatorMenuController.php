<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\ImageUploadService;

/**
 * Gestão simplificada de categorias e produtos do cardápio.
 */
final class OperatorMenuController extends Controller
{
    /**
     * Visão geral do cardápio da unidade.
     */
    public function index(): void
    {
        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $pdo = Database::pdo();
        $cats = $pdo->prepare('SELECT * FROM categories WHERE unit_id = :u AND deleted_at IS NULL ORDER BY sort_order ASC');
        $cats->execute(['u' => $unitId]);
        $categories = $cats->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $products = $pdo->prepare(
            'SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id
             WHERE p.unit_id = :u AND p.deleted_at IS NULL ORDER BY p.sort_order ASC'
        );
        $products->execute(['u' => $unitId]);
        $prows = $products->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $flash = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        $this->view('operator/menu', [
            'categories' => $categories,
            'products' => $prows,
            'csrf' => Csrf::token(),
            'title' => 'Cardápio',
            'flash_error' => $flash,
        ], 'operator');
    }

    /**
     * Cria categoria via POST rápido.
     */
    public function createCategory(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador/cardapio');
        }

        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $name = trim((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW));
        if ($name === '') {
            Redirect::to('/operador/cardapio');
        }

        $pdo = Database::pdo();
        $pdo->prepare(
            'INSERT INTO categories (unit_id, name, sort_order, is_active, created_at, updated_at)
             VALUES (:u,:n,99,1,NOW(),NOW())'
        )->execute(['u' => $unitId, 'n' => $name]);

        Redirect::to('/operador/cardapio');
    }

    /**
     * Cria produto com upload opcional de imagem (JPEG/PNG/WebP, até 2MB).
     */
    public function createProduct(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador/cardapio');
        }

        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $cid = (int) filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $name = trim((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW));
        $price = (float) filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        if ($cid <= 0 || $name === '' || $price <= 0) {
            Redirect::to('/operador/cardapio');
        }

        $pdo = Database::pdo();
        $check = $pdo->prepare('SELECT id FROM categories WHERE id = :id AND unit_id = :u LIMIT 1');
        $check->execute(['id' => $cid, 'u' => $unitId]);
        if ($check->fetch() === false) {
            Redirect::to('/operador/cardapio');
        }

        $cfg = require dirname(__DIR__, 2) . '/config/app.php';
        $publicRoot = defined('BASE_PATH') ? BASE_PATH . '/public' : dirname(__DIR__, 2) . '/public';
        $max = (int) ($cfg['upload_max'] ?? 2097152);

        $imagePath = null;
        try {
            $file = $_FILES['image'] ?? null;
            $imagePath = ImageUploadService::storeProductImage(is_array($file) ? $file : null, $max, $publicRoot);
        } catch (\Throwable) {
            $_SESSION['flash_error'] = 'Imagem inválida ou muito grande.';
            Redirect::to('/operador/cardapio');
        }

        $pdo->prepare(
            'INSERT INTO products (unit_id, category_id, name, description, price, image_path, status, sort_order, created_at, updated_at)
             VALUES (:u,:c,:n,:d,:p,:img,:st,0,NOW(),NOW())'
        )->execute([
            'u' => $unitId,
            'c' => $cid,
            'n' => $name,
            'd' => '',
            'p' => $price,
            'img' => $imagePath,
            'st' => 'active',
        ]);

        Redirect::to('/operador/cardapio');
    }
}
