<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use PDO;

/**
 * Regras de caixa: abertura, sangrias, entradas automáticas e fechamento.
 */
final class CashRegisterService
{
    /**
     * Retorna caixa aberto da unidade, se existir.
     *
     * @return array<string, mixed>|null
     */
    public static function getOpen(int $unitId): ?array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT * FROM cash_registers WHERE unit_id = :u AND closed_at IS NULL ORDER BY id DESC LIMIT 1'
        );
        $st->execute(['u' => $unitId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * Abre novo caixa para a unidade.
     */
    public static function open(int $unitId, int $adminId, float $openingBalance): void
    {
        if (self::getOpen($unitId) !== null) {
            throw new \RuntimeException('Já existe caixa aberto.');
        }

        $pdo = Database::pdo();
        $pdo->prepare(
            'INSERT INTO cash_registers (unit_id, admin_id, opened_at, opening_balance, created_at, updated_at)
             VALUES (:u,:a,NOW(),:ob,NOW(),NOW())'
        )->execute(['u' => $unitId, 'a' => $adminId, 'ob' => round($openingBalance, 2)]);
    }

    /**
     * Registra sangria manual.
     */
    public static function withdraw(int $registerId, float $amount, string $reason): void
    {
        $pdo = Database::pdo();
        $pdo->prepare(
            'INSERT INTO cash_entries (cash_register_id, entry_type, amount, payment_method, reason, created_at, updated_at)
             VALUES (:rid,:etype,:amt,NULL,:rs,NOW(),NOW())'
        )->execute(['rid' => $registerId, 'etype' => 'sangria', 'amt' => round($amount, 2), 'rs' => $reason]);
    }

    /**
     * Registra entrada automática de venda quando houver caixa aberto.
     */
    public static function recordSaleIfOpen(int $orderId): void
    {
        $pdo = Database::pdo();
        $o = $pdo->prepare('SELECT id, unit_id, total, payment_method FROM orders WHERE id = :id LIMIT 1');
        $o->execute(['id' => $orderId]);
        $order = $o->fetch(PDO::FETCH_ASSOC);
        if ($order === false) {
            return;
        }

        $reg = self::getOpen((int) $order['unit_id']);
        if ($reg === null) {
            return;
        }

        $method = match ($order['payment_method']) {
            'pix' => 'pix',
            'card' => 'card',
            'on_delivery' => 'cash',
            default => 'other',
        };

        $pdo->prepare(
            'INSERT INTO cash_entries (cash_register_id, entry_type, amount, payment_method, reason, order_id, created_at, updated_at)
             VALUES (:rid,:etype,:amt,:pm,:rs,:oid,NOW(),NOW())'
        )->execute([
            'rid' => $reg['id'],
            'etype' => 'entrada',
            'amt' => (float) $order['total'],
            'pm' => $method,
            'rs' => 'Pedido #' . $order['id'],
            'oid' => $orderId,
        ]);
    }

    /**
     * Fecha caixa calculando totais e gerando PDF simples em `storage/`.
     *
     * @return string Caminho relativo ao projeto do relatório PDF gerado
     */
    public static function close(int $registerId, float $countedBalance, ?string $note = null): string
    {
        $pdo = Database::pdo();
        $reg = $pdo->prepare('SELECT * FROM cash_registers WHERE id = :id AND closed_at IS NULL LIMIT 1');
        $reg->execute(['id' => $registerId]);
        $row = $reg->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new \RuntimeException('Caixa não encontrado ou já fechado.');
        }

        $entries = $pdo->prepare(
            'SELECT entry_type, payment_method, SUM(amount) AS total FROM cash_entries WHERE cash_register_id = :id GROUP BY entry_type, payment_method'
        );
        $entries->execute(['id' => $registerId]);
        $sums = $entries->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $entradas = 0.0;
        $sangrias = 0.0;
        foreach ($sums as $s) {
            if ($s['entry_type'] === 'entrada') {
                $entradas += (float) $s['total'];
            } else {
                $sangrias += (float) $s['total'];
            }
        }

        $opening = (float) $row['opening_balance'];
        $expected = $opening + $entradas - $sangrias;
        $diff = round($countedBalance - $expected, 2);

        $relPath = 'uploads/reports/caixa-' . $registerId . '-' . date('YmdHis') . '.pdf';
        $abs = dirname(__DIR__, 2) . '/public/' . $relPath;
        if (!is_dir(dirname($abs))) {
            mkdir(dirname($abs), 0755, true);
        }

        $html = self::buildReportHtml($row, $sums, $opening, $entradas, $sangrias, $expected, $countedBalance, $diff, $note);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
        file_put_contents($abs, $dompdf->output());

        $pdo->prepare(
            'UPDATE cash_registers SET closed_at = NOW(), closing_balance = :cb, expected_balance = :eb, difference_amount = :df, difference_note = :dn, report_path = :rp, updated_at = NOW() WHERE id = :id'
        )->execute([
            'cb' => round($countedBalance, 2),
            'eb' => round($expected, 2),
            'df' => $diff,
            'dn' => $note,
            'rp' => $relPath,
            'id' => $registerId,
        ]);

        return $relPath;
    }

    /**
     * Monta HTML simples para conversão em PDF pelo Dompdf.
     *
     * @param list<array<string, mixed>> $sums
     * @return string HTML completo
     */
    private static function buildReportHtml(
        array $register,
        array $sums,
        float $opening,
        float $entradas,
        float $sangrias,
        float $expected,
        float $counted,
        float $diff,
        ?string $note
    ): string {
        $rows = '';
        foreach ($sums as $s) {
            $rows .= '<tr><td>' . htmlspecialchars((string) $s['entry_type']) . '</td><td>' . htmlspecialchars((string) ($s['payment_method'] ?? '-')) . '</td><td>' . number_format((float) $s['total'], 2, ',', '.') . '</td></tr>';
        }

        return '<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans;font-size:12px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ccc;padding:6px}</style></head><body>'
            . '<h1>Relatório de caixa #' . (int) $register['id'] . '</h1>'
            . '<p>Abertura: ' . htmlspecialchars((string) $register['opened_at']) . '</p>'
            . '<p>Fundo inicial: R$ ' . number_format($opening, 2, ',', '.') . '</p>'
            . '<h2>Movimentações</h2><table><thead><tr><th>Tipo</th><th>Forma</th><th>Total</th></tr></thead><tbody>' . $rows . '</tbody></table>'
            . '<p>Entradas: R$ ' . number_format($entradas, 2, ',', '.') . '</p>'
            . '<p>Sangrias: R$ ' . number_format($sangrias, 2, ',', '.') . '</p>'
            . '<p>Saldo esperado: R$ ' . number_format($expected, 2, ',', '.') . '</p>'
            . '<p>Saldo contado: R$ ' . number_format($counted, 2, ',', '.') . '</p>'
            . '<p>Diferença: R$ ' . number_format($diff, 2, ',', '.') . '</p>'
            . '<p>Observações: ' . htmlspecialchars((string) ($note ?? '')) . '</p>'
            . '</body></html>';
    }
}
