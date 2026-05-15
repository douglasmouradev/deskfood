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
                'confirmados' => count($board['confirmados']),
                'em_preparo' => count($board['em_preparo']),
                'saiu' => count($board['saiu']),
                'pendentes' => count($board['pendentes']),
                'finalizados' => count($board['finalizados']),
            ],
        ]);
    }

    /**
     * Server-Sent Events: envia nova revisão quando o quadro muda (substitui reload completo).
     */
    public function boardStream(): void
    {
        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        if ($unitId <= 0) {
            http_response_code(403);
            exit;
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        if (function_exists('session_write_close')) {
            session_write_close();
        }

        $pdo = Database::pdo();
        $lastRev = '';
        $iterations = 0;
        $maxIter = 90;

        while ($iterations < $maxIter && !connection_aborted()) {
            $orders = self::loadOrdersForUnit($pdo, $unitId);
            $board = self::partitionOrdersForBoard($orders);
            $touch = self::maxOrdersTouch($pdo, $unitId);
            $rev = self::computeBoardRevision($board, $touch);

            if ($rev !== $lastRev) {
                $payload = json_encode([
                    'rev' => $rev,
                    'counts' => [
                        'novos' => count($board['novos']),
                        'confirmados' => count($board['confirmados']),
                        'em_preparo' => count($board['em_preparo']),
                        'saiu' => count($board['saiu']),
                        'pendentes' => count($board['pendentes']),
                    ],
                ], JSON_THROW_ON_ERROR);
                echo "event: board\n";
                echo 'data: ' . $payload . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                $lastRev = $rev;
            } else {
                echo ": ping\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }

            sleep(2);
            ++$iterations;
        }
        exit;
    }

    /**
     * Fragmento HTML do quadro para atualização sem reload da página.
     */
    public function boardHtml(): void
    {
        $unitId = (int) ($_SESSION['unit_id'] ?? 0);
        if ($unitId <= 0) {
            $this->json(['ok' => false], 403);

            return;
        }

        $pdo = Database::pdo();
        $orders = self::loadOrdersForUnit($pdo, $unitId);
        $board = self::partitionOrdersForBoard($orders);
        $touch = self::maxOrdersTouch($pdo, $unitId);
        $rev = self::computeBoardRevision($board, $touch);

        $motoboys = $pdo->prepare('SELECT id, name FROM motoboys WHERE unit_id = :u AND is_active = 1 AND deleted_at IS NULL');
        $motoboys->execute(['u' => $unitId]);
        $mb = $motoboys->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $csrf = \App\Helpers\Csrf::token();
        ob_start();
        require BASE_PATH . '/views/operator/partials/board_columns.php';
        $html = (string) ob_get_clean();

        $this->json([
            'ok' => true,
            'rev' => $rev,
            'html' => $html,
            'counts' => [
                'novos' => count($board['novos']),
                'pendentes' => count($board['pendentes']),
            ],
        ]);
    }

    /** @return list<array<string,mixed>> */
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
     * @param array{novos:list,confirmados:list,em_preparo:list,saiu:list,pendentes:list,finalizados:list} $board
     */
    private static function computeBoardRevision(array $board, string $maxUpdated): string
    {
        $payload = [
            'n' => count($board['novos']),
            'c' => count($board['confirmados']),
            'e' => count($board['em_preparo']),
            's' => count($board['saiu']),
            'x' => count($board['pendentes']),
            'f' => count($board['finalizados']),
            't' => $maxUpdated,
        ];

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * @param list<array<string,mixed>> $orders
     * @return array{
     *   novos: list<array<string,mixed>>,
     *   confirmados: list<array<string,mixed>>,
     *   em_preparo: list<array<string,mixed>>,
     *   saiu: list<array<string,mixed>>,
     *   pendentes: list<array<string,mixed>>,
     *   finalizados: list<array<string,mixed>>
     * }
     */
    private static function partitionOrdersForBoard(array $orders): array
    {
        $novos = [];
        $confirmados = [];
        $emPreparo = [];
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
            } elseif ($status === 'confirmado') {
                $confirmados[] = $o;
            } elseif ($status === 'em_preparo') {
                $emPreparo[] = $o;
            } elseif ($status === 'saiu_entrega') {
                $saiu[] = $o;
            }
        }

        $cmp = static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
        usort($novos, $cmp);
        usort($confirmados, $cmp);
        usort($emPreparo, $cmp);
        usort($saiu, $cmp);
        usort($pendentes, $cmp);
        usort($finalizados, $cmp);

        return [
            'novos' => $novos,
            'confirmados' => $confirmados,
            'em_preparo' => $emPreparo,
            'saiu' => $saiu,
            'pendentes' => $pendentes,
            'finalizados' => $finalizados,
        ];
    }
}
