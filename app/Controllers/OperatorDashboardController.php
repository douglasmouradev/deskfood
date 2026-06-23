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
    private const ORDER_BOARD_COLUMNS = 'o.id, o.unit_id, o.order_number, o.status, o.payment_method, o.payment_status, o.total, o.customer_name, o.customer_phone, o.delivery_type, o.notes, o.created_at, o.updated_at, d.motoboy_id';

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
        $boardRevision = self::lightBoardRevision($pdo, $unitId, $touch);
        $pollMs = max(0, (int) Env::get('OPERATOR_BOARD_POLL_MS', '20000'));
        $whatsappFlash = $_SESSION['whatsapp_assign_flash'] ?? null;
        unset($_SESSION['whatsapp_assign_flash']);

        $this->view('operator/dashboard', [
            'unit' => $unit,
            'board' => $board,
            'motoboys' => $mb,
            'csrf' => \App\Helpers\Csrf::token(),
            'title' => 'Pedidos ao vivo',
            'boardRevision' => $boardRevision,
            'boardPollMs' => $pollMs,
            'whatsapp_flash' => $whatsappFlash,
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
        $touch = self::maxOrdersTouch($pdo, $unitId);
        $rev = self::lightBoardRevision($pdo, $unitId, $touch);
        $counts = self::quickBoardCounts($pdo, $unitId);

        $this->json([
            'ok' => true,
            'rev' => $rev,
            'counts' => array_merge($counts, ['finalizados' => 0]),
        ]);
    }

    /**
     * SSE descontinuado — use poll em /operador/api/quadro-rev (evita esgotar PHP-FPM).
     */
    public function boardStream(): void
    {
        http_response_code(410);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'message' => 'Use polling em /operador/api/quadro-rev',
        ], JSON_UNESCAPED_UNICODE);
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
        $rev = self::lightBoardRevision($pdo, $unitId, $touch);

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
        $cols = self::ORDER_BOARD_COLUMNS;
        $st = $pdo->prepare(
            "SELECT {$cols}
             FROM orders o
             LEFT JOIN deliveries d ON d.order_id = o.id
             WHERE o.unit_id = :u AND o.deleted_at IS NULL
             ORDER BY o.created_at DESC
             LIMIT 80"
        );
        $st->execute(['u' => $unitId]);

        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Revisão leve (contagens SQL + touch) — alinhada entre poll e HTML.
     */
    private static function lightBoardRevision(PDO $pdo, int $unitId, ?string $touch = null): string
    {
        $counts = self::quickBoardCounts($pdo, $unitId);
        $touch ??= self::maxOrdersTouch($pdo, $unitId);
        $payload = [
            'n' => $counts['novos'],
            'c' => $counts['confirmados'],
            'e' => $counts['em_preparo'],
            's' => $counts['saiu'],
            'x' => $counts['pendentes'],
            'f' => 0,
            't' => $touch,
        ];

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * @return array{novos:int,confirmados:int,em_preparo:int,saiu:int,pendentes:int}
     */
    private static function quickBoardCounts(PDO $pdo, int $unitId): array
    {
        $pendingExpr = "((payment_method = 'pix' AND payment_status = 'pendente') OR (payment_method = 'card' AND payment_status = 'pendente'))";
        $st = $pdo->prepare(
            "SELECT
                SUM(CASE WHEN {$pendingExpr} THEN 1 ELSE 0 END) AS pendentes,
                SUM(CASE WHEN status = 'pendente' AND NOT {$pendingExpr} THEN 1 ELSE 0 END) AS novos,
                SUM(CASE WHEN status = 'confirmado' AND NOT {$pendingExpr} THEN 1 ELSE 0 END) AS confirmados,
                SUM(CASE WHEN status = 'em_preparo' AND NOT {$pendingExpr} THEN 1 ELSE 0 END) AS em_preparo,
                SUM(CASE WHEN status = 'saiu_entrega' AND NOT {$pendingExpr} THEN 1 ELSE 0 END) AS saiu
             FROM orders
             WHERE unit_id = :u AND deleted_at IS NULL AND status NOT IN ('entregue', 'cancelado')"
        );
        $st->execute(['u' => $unitId]);
        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'novos' => (int) ($row['novos'] ?? 0),
            'confirmados' => (int) ($row['confirmados'] ?? 0),
            'em_preparo' => (int) ($row['em_preparo'] ?? 0),
            'saiu' => (int) ($row['saiu'] ?? 0),
            'pendentes' => (int) ($row['pendentes'] ?? 0),
        ];
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
            $cardPending = ($o['payment_method'] ?? '') === 'card' && ($o['payment_status'] ?? '') === 'pendente';
            if ($pixPending || $cardPending) {
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
