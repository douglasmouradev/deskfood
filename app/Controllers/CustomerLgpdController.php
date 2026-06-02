<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Logger;
use App\Helpers\Redirect;
use App\Services\AuditLogService;

/**
 * Painel LGPD do titular (visualizar, exportar, corrigir, excluir).
 */
final class CustomerLgpdController extends Controller
{
    /**
     * Visão geral das ações disponíveis.
     */
    public function index(): void
    {
        $this->view('customer/lgpd_index', ['csrf' => Csrf::token(), 'title' => 'LGPD'], 'customer');
    }

    /**
     * Exibe dados cadastrais atuais.
     */
    public function data(): void
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT id, name, phone, phone_e164, email, created_at FROM users WHERE id = :id LIMIT 1');
        $st->execute(['id' => (int) $_SESSION['user_id']]);
        $user = $st->fetch(\PDO::FETCH_ASSOC) ?: [];

        $this->view('customer/lgpd_data', ['user' => $user, 'title' => 'Meus dados'], 'customer');
    }

    /**
     * Exporta JSON com dados do titular (portabilidade).
     */
    public function export(): void
    {
        $uid = (int) $_SESSION['user_id'];
        $pdo = Database::pdo();
        $payload = [
            'user' => $this->fetchOne($pdo, 'SELECT * FROM users WHERE id = :id', ['id' => $uid]),
            'orders' => $this->fetchAll(
                $pdo,
                'SELECT * FROM orders WHERE user_id = :id ORDER BY id DESC',
                ['id' => $uid]
            ),
            'consents' => $this->fetchAll(
                $pdo,
                'SELECT * FROM lgpd_consents WHERE user_id = :id ORDER BY id DESC',
                ['id' => $uid]
            ),
        ];

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="deskfood-dados-' . $uid . '.json"');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Formulário para correção de nome e telefone.
     */
    public function editForm(): void
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $st->execute(['id' => (int) $_SESSION['user_id']]);
        $user = $st->fetch(\PDO::FETCH_ASSOC) ?: [];
        $this->view('customer/lgpd_edit', ['user' => $user, 'csrf' => Csrf::token(), 'title' => 'Corrigir dados'], 'customer');
    }

    /**
     * Persiste correção — telefone exige novo OTP fora deste fluxo simplificado.
     */
    public function editSave(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/cliente/lgpd/editar');
        }

        $name = trim((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW));
        $emailRaw = trim((string) filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW));
        $email = $emailRaw === '' ? null : $emailRaw;
        if (strlen($name) < 2) {
            $_SESSION['flash_error'] = 'Nome inválido.';
            Redirect::to('/cliente/lgpd/editar');
        }
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'E-mail inválido.';
            Redirect::to('/cliente/lgpd/editar');
        }

        $pdo = Database::pdo();
        try {
            $stmt = $pdo->prepare('UPDATE users SET name = :n, email = :e, updated_at = NOW() WHERE id = :id');
            $stmt->execute(['n' => $name, 'e' => $email, 'id' => (int) $_SESSION['user_id']]);
        } catch (\Throwable) {
            $stmt = $pdo->prepare('UPDATE users SET name = :n, updated_at = NOW() WHERE id = :id');
            $stmt->execute(['n' => $name, 'id' => (int) $_SESSION['user_id']]);
        }

        AuditLogService::record('customer', (int) $_SESSION['user_id'], 'lgpd.edit', 'user', (int) $_SESSION['user_id'], []);

        $_SESSION['flash_ok'] = 'Dados atualizados.';
        Redirect::to('/cliente/lgpd');
    }

    /**
     * Anonimiza titular mantendo histórico de pedidos agregado.
     */
    public function delete(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/cliente/lgpd');
        }

        $uid = (int) $_SESSION['user_id'];
        $pdo = Database::pdo();
        $e164 = '+5511000' . str_pad((string) $uid, 6, '0', STR_PAD_LEFT);

        try {
            $pdo->beginTransaction();
            $pdo->prepare(
                'INSERT INTO lgpd_requests (user_id, request_type, status, payload, created_at, updated_at)
                 VALUES (:u,:t,:st,NULL,NOW(),NOW())'
            )->execute(['u' => $uid, 't' => 'delete', 'st' => 'completed']);

            $pdo->prepare(
                'UPDATE users SET name = :n, phone = :p, phone_e164 = :e, email = NULL, anonymized_at = NOW(), updated_at = NOW() WHERE id = :id'
            )->execute([
                'n' => 'Usuário anonimizado',
                'p' => '00000000000',
                'e' => $e164,
                'id' => $uid,
            ]);

            $pdo->prepare('UPDATE orders SET user_id = NULL WHERE user_id = :id')->execute(['id' => $uid]);
            $pdo->commit();
            AuditLogService::record('customer', $uid, 'lgpd.delete', 'user', $uid, []);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Logger::log('error', 'Falha LGPD delete', ['e' => $e->getMessage()]);
            $_SESSION['flash_error'] = 'Não foi possível concluir a exclusão.';
            Redirect::to('/cliente/lgpd');

            return;
        }

        session_regenerate_id(true);
        unset($_SESSION['user_id']);
        $_SESSION['flash_ok'] = 'Conta anonimizada com sucesso.';
        Redirect::to('/');
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>|false
     */
    private function fetchOne(\PDO $pdo, string $sql, array $params): array|false
    {
        $st = $pdo->prepare($sql . ' LIMIT 1');
        $st->execute($params);

        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, mixed> $params
     * @return list<array<string, mixed>>
     */
    private function fetchAll(\PDO $pdo, string $sql, array $params): array
    {
        $st = $pdo->prepare($sql);
        $st->execute($params);

        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
