<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\AuditLogService;
use App\Services\CryptoService;

/**
 * Cadastro e listagem de motoboys da unidade.
 */
final class OperatorMotoboyController extends Controller
{
    /**
     * Lista motoboys ativos/inativos.
     */
    public function index(): void
    {
        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT id, name, phone, is_active, access_token FROM motoboys WHERE unit_id = :u AND deleted_at IS NULL ORDER BY id DESC');
        $st->execute(['u' => $unitId]);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $cfg = require dirname(__DIR__, 2) . '/config/app.php';
        $this->view('operator/motoboys', [
            'motoboys' => $rows,
            'csrf' => Csrf::token(),
            'app_url' => $cfg['url'],
            'title' => 'Motoboys',
        ], 'operator');
    }

    /**
     * Processa cadastro com CPF cifrado.
     */
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
        $token = bin2hex(random_bytes(16));

        $pdo = Database::pdo();
        $expires = (new \DateTimeImmutable('+90 days'))->format('Y-m-d H:i:s');
        $pdo->prepare(
            'INSERT INTO motoboys (unit_id, name, phone, cpf_encrypted, access_token, token_expires_at, is_active, created_at, updated_at)
             VALUES (:u,:n,:p,:c,:t,:exp,1,NOW(),NOW())'
        )->execute(['u' => $unitId, 'n' => $name, 'p' => $phone, 'c' => $cipher, 't' => $token, 'exp' => $expires]);

        Redirect::to('/operador/motoboys');
    }

    /**
     * Revoga link atual e gera novo token com validade de 90 dias.
     */
    public function revokeToken(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador/motoboys');
        }

        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $token = bin2hex(random_bytes(16));
        $expires = (new \DateTimeImmutable('+90 days'))->format('Y-m-d H:i:s');

        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'UPDATE motoboys SET access_token = :t, token_expires_at = :exp, updated_at = NOW()
             WHERE id = :id AND unit_id = :u AND deleted_at IS NULL'
        );
        $st->execute(['t' => $token, 'exp' => $expires, 'id' => $id, 'u' => $unitId]);
        if ($st->rowCount() > 0) {
            AuditLogService::record('operator', (int) ($_SESSION['admin_id'] ?? 0), 'motoboy.token_revoke', 'motoboy', $id, []);
        }

        Redirect::to('/operador/motoboys');
    }
}
