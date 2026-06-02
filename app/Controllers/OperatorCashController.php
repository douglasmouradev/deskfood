<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\CashRegisterService;

/**
 * Abertura, sangrias e fechamento de caixa da unidade.
 */
final class OperatorCashController extends Controller
{
    /**
     * Painel do caixa atual e formulários.
     */
    public function index(): void
    {
        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $open = CashRegisterService::getOpen($unitId);
        $entries = [];
        if ($open !== null) {
            $pdo = \App\Database::pdo();
            $st = $pdo->prepare('SELECT * FROM cash_entries WHERE cash_register_id = :id ORDER BY id DESC LIMIT 50');
            $st->execute(['id' => $open['id']]);
            $entries = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        $history = [];
        $pdo = \App\Database::pdo();
        $h = $pdo->prepare('SELECT * FROM cash_registers WHERE unit_id = :u ORDER BY id DESC LIMIT 10');
        $h->execute(['u' => $unitId]);
        $history = $h->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->view('operator/cash', [
            'open' => $open,
            'entries' => $entries,
            'history' => $history,
            'csrf' => Csrf::token(),
            'title' => 'Caixa',
        ], 'operator');
    }

    /**
     * Abre caixa com valor inicial de troco.
     */
    public function open(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador/caixa');
        }

        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        $balance = (float) filter_input(INPUT_POST, 'opening_balance', FILTER_VALIDATE_FLOAT);
        try {
            CashRegisterService::open($unitId, $adminId, $balance);
        } catch (\Throwable) {
            $_SESSION['flash_error'] = 'Não foi possível abrir o caixa.';
        }

        Redirect::to('/operador/caixa');
    }

    /**
     * Registra sangria.
     */
    public function withdraw(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador/caixa');
        }

        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $open = CashRegisterService::getOpen($unitId);
        if ($open === null) {
            Redirect::to('/operador/caixa');
        }

        $amount = (float) filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $reason = trim((string) filter_input(INPUT_POST, 'reason', FILTER_UNSAFE_RAW));
        if ($amount <= 0 || $reason === '') {
            Redirect::to('/operador/caixa');
        }

        CashRegisterService::withdraw((int) $open['id'], $amount, $reason);
        Redirect::to('/operador/caixa');
    }

    /**
     * Fecha caixa e gera PDF de conferência.
     */
    public function close(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador/caixa');
        }

        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $open = CashRegisterService::getOpen($unitId);
        if ($open === null) {
            Redirect::to('/operador/caixa');
        }

        $counted = (float) filter_input(INPUT_POST, 'closing_balance', FILTER_VALIDATE_FLOAT);
        $note = trim((string) (filter_input(INPUT_POST, 'note', FILTER_UNSAFE_RAW) ?: ''));
        try {
            CashRegisterService::close((int) $open['id'], $counted, $note);
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Erro ao fechar: ' . $e->getMessage();
        }

        Redirect::to('/operador/caixa');
    }
}
