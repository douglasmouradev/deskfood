<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\AuditLogService;
use App\Services\CryptoService;
use App\Services\MotoboyTokenService;

/**
 * Cadastro e listagem de motoboys da unidade.
 */
final class OperatorMotoboyController extends Controller
{
    public function index(): void
    {
        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT id, name, phone, is_active,
                    (access_token_hash IS NOT NULL AND access_token_hash != "") AS has_link
             FROM motoboys WHERE unit_id = :u AND deleted_at IS NULL ORDER BY id DESC'
        );
        $st->execute(['u' => $unitId]);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $cfg = require dirname(__DIR__, 2) . '/config/app.php';
        $linkFlash = $_SESSION['motoboy_link_flash'] ?? null;
        unset($_SESSION['motoboy_link_flash']);

        $this->view('operator/motoboys', [
            'motoboys' => $rows,
            'csrf' => Csrf::token(),
            'app_url' => $cfg['url'],
            'link_flash' => $linkFlash,
            'title' => 'Motoboys',
        ], 'operator');
    }

    public function create(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador/motoboys');
        }

        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $name = trim((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW));
        $phone = trim((string) filter_input(INPUT_POST, 'phone', FILTER_UNSAFE_RAW));
        $cpf = preg_replace('/\D+/', '', (string) filter_input(INPUT_POST, 'cpf', FILTER_UNSAFE_RAW)) ?? '';
        if ($name === '' || strlen($cpf) !== 11) {
            Redirect::to('/operador/motoboys');
        }

        $cipher = CryptoService::encrypt($cpf);
        $token = MotoboyTokenService::generate();
        $hash = MotoboyTokenService::hash($token);

        $pdo = Database::pdo();
        $expires = (new \DateTimeImmutable('+90 days'))->format('Y-m-d H:i:s');
        $pdo->prepare(
            'INSERT INTO motoboys (unit_id, name, phone, cpf_encrypted, access_token, access_token_hash, token_expires_at, is_active, created_at, updated_at)
             VALUES (:u,:n,:p,:c,NULL,:h,:exp,1,NOW(),NOW())'
        )->execute(['u' => $unitId, 'n' => $name, 'p' => $phone, 'c' => $cipher, 'h' => $hash, 'exp' => $expires]);

        $cfg = require dirname(__DIR__, 2) . '/config/app.php';
        $_SESSION['motoboy_link_flash'] = [
            'name' => $name,
            'url' => rtrim((string) $cfg['url'], '/') . '/m/' . $token,
            'expires' => $expires,
        ];

        Redirect::to('/operador/motoboys');
    }

    public function revokeToken(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador/motoboys');
        }

        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $token = MotoboyTokenService::generate();
        $hash = MotoboyTokenService::hash($token);
        $expires = (new \DateTimeImmutable('+90 days'))->format('Y-m-d H:i:s');

        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'UPDATE motoboys SET access_token = NULL, access_token_hash = :h, token_expires_at = :exp, updated_at = NOW()
             WHERE id = :id AND unit_id = :u AND deleted_at IS NULL'
        );
        $st->execute(['h' => $hash, 'exp' => $expires, 'id' => $id, 'u' => $unitId]);
        if ($st->rowCount() > 0) {
            AuditLogService::record('operator', (int) ($_SESSION['admin_id'] ?? 0), 'motoboy.token_revoke', 'motoboy', $id, []);
            $cfg = require dirname(__DIR__, 2) . '/config/app.php';
            $_SESSION['motoboy_link_flash'] = [
                'name' => 'Motoboy #' . $id,
                'url' => rtrim((string) $cfg['url'], '/') . '/m/' . $token,
                'expires' => $expires,
            ];
        }

        Redirect::to('/operador/motoboys');
    }
}
