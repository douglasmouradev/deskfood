<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Env;
use PDO;

/**
 * Painel inicial do operador com pedidos em aberto.
 */
final class OperatorDashboardController extends Controller
{
    /**
     * Lista pedidos recentes da unidade vinculada ao operador.
     */
    public function index(): void
    {
        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        $pdo = Database::pdo();
        $u = $pdo->prepare('SELECT * FROM units WHERE id = :id LIMIT 1');
        $u->execute(['id' => $unitId]);
        $unit = $u->fetch(PDO::FETCH_ASSOC) ?: [];

        $orders = self::loadOrdersForUnit($pdo, $unitId);
        $board = self::partitionOrdersForBoard($orders);

        $motoboys = $pdo->prepare('SELECT id, name FROM motoboys WHERE unit_id = :u AND is_active = 1 AND deleted_at IS NULL');
        $motoboys->execute(['u' => $unitId]);
        $mb = $motoboys->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $touch = self::maxOrdersTouch($pdo, $unitId);
        $boardRevision = self::computeBoardRevision($board, $touch);
        $pollMs = max(0, (int) Env::get('OPERATOR_BOARD_POLL_MS', '20000'));

        $this->view('operator/dashboard', [
            'unit' => $unit,
            'board' => $board,
            'motoboys' => $mb,
            'csrf' => \App\Helpers\Csrf::token(),
            'title' => 'Pedidos ao vivo',
            'boardRevision' => $boardRevision,
            'boardPollMs' => $pollMs,
        ], 'operator');
    }

    /**
     * JSON leve para o navegador detectar mudanças no quadro e recarregar a página.
     */
    public function boardPoll(): void
    {
        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        if ($unitId <= 0) {
            $this->json(['ok' => false, 'error' => 'forbidden'], 403);

            return;
        }

        $pdo = Database::pdo();
        $orders = self::loadOrdersForUnit($pdo, $unitId);
        $board = self::partitionOrdersForBoard($orders);
        $touch = self::maxOrdersTouch($pdo, $unitId);
        $rev = self::computeBoardRevision($board, $touch);

        $this->json([
            'ok' => true,
            'rev' => $rev,
            'counts' => [
                'novos' => count($board['novos']),
                'prontos' => count($board['prontos']),
                'saiu' => count($board['saiu']),
                'pendentes' => count($board['pendentes']),
                'finalizados' => count($board['finalizados']),
            ],
        ]);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private static function loadOrdersForUnit(PDO $pdo, int $unitId): array
    {
        $st = $pdo->prepare(
            'SELECT * FROM orders WHERE unit_id = :u AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 80'
        );
        $st->execute(['u' => $unitId]);

        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private static function maxOrdersTouch(PDO $pdo, int $unitId): string
    {
        $st = $pdo->prepare(
            'SELECT MAX(updated_at) AS t FROM orders WHERE unit_id = :u AND deleted_at IS NULL'
        );
        $st->execute(['u' => $unitId]);
        $v = $st->fetchColumn();

        return $v !== false && $v !== null ? (string) $v : '';
    }

    /**
     * @param array{novos: list, prontos: list, saiu: list, pendentes: list, finalizados: list} $board
     */
    private static function computeBoardRevision(array $board, string $maxUpdated): string
    {
        $payload = [
            'n' => count($board['novos']),
            'p' => count($board['prontos']),
            's' => count($board['saiu']),
            'x' => count($board['pendentes']),
            'f' => count($board['finalizados']),
            't' => $maxUpdated,
        ];

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * Agrupa pedidos ativos em colunas do quadro: novos, prontos (cozinha), saiu, pendentes (PIX).
     *
     * @param list<array<string,mixed>> $orders
     * @return array{
     *   novos: list<array<string,mixed>>,
     *   prontos: list<array<string,mixed>>,
     *   saiu: list<array<string,mixed>>,
     *   pendentes: list<array<string,mixed>>,
     *   finalizados: list<array<string,mixed>>
     * }
     */
    private static function partitionOrdersForBoard(array $orders): array
    {
        $novos = [];
        $prontos = [];
        $saiu = [];
        $pendentes = [];
        $finalizados = [];

        foreach ($orders as $o) {
            $status = (string) ($o['status'] ?? '');
            if ($status === 'entregue' || $status === 'cancelado') {
                $finalizados[] = $o;
                continue;
            }

            $pixPending = ($o['payment_method'] ?? '') === 'pix' && ($o['payment_status'] ?? '') === 'pendente';
            if ($pixPending) {
                $pendentes[] = $o;
                continue;
            }

            if ($status === 'pendente') {
                $novos[] = $o;
            } elseif ($status === 'confirmado' || $status === 'em_preparo') {
                $prontos[] = $o;
            } elseif ($status === 'saiu_entrega') {
                $saiu[] = $o;
            }
        }

        $cmp = static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
        usort($novos, $cmp);
        usort($prontos, $cmp);
        usort($saiu, $cmp);
        usort($pendentes, $cmp);
        usort($finalizados, $cmp);

        return [
            'novos' => $novos,
            'prontos' => $prontos,
            'saiu' => $saiu,
            'pendentes' => $pendentes,
            'finalizados' => $finalizados,
        ];
    }
}
