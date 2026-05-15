<?php
declare(strict_types=1);
/** @var array<string,mixed> $order */
/** @var list<array<string,mixed>> $items */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Comanda #<?= htmlspecialchars((string) ($order['order_number'] ?? '')) ?></title>
    <style>
        body { font-family: system-ui, sans-serif; font-size: 14px; max-width: 320px; margin: 1rem auto; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td { padding: 4px 0; border-bottom: 1px dashed #ccc; }
        .meta { color: #444; font-size: 12px; }
        @media print { body { margin: 0; } button { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <button type="button" onclick="window.print()">Imprimir</button>
    <h1><?= htmlspecialchars((string) ($order['unit_name'] ?? 'Desk Food')) ?></h1>
    <p class="meta">Pedido #<?= htmlspecialchars((string) $order['order_number']) ?> · <?= htmlspecialchars((string) ($order['created_at'] ?? '')) ?></p>
    <p><strong><?= htmlspecialchars((string) $order['customer_name']) ?></strong><br><?= htmlspecialchars((string) $order['customer_phone']) ?></p>
    <p class="meta"><?= htmlspecialchars((string) $order['delivery_street']) ?>, <?= htmlspecialchars((string) $order['delivery_number']) ?> — <?= htmlspecialchars((string) $order['delivery_neighborhood']) ?></p>
    <?php if (!empty($order['notes'])): ?>
        <p><em>Obs: <?= htmlspecialchars((string) $order['notes']) ?></em></p>
    <?php endif; ?>
    <table>
        <?php foreach ($items as $it): ?>
            <tr>
                <td><?= (int) $it['quantity'] ?>× <?= htmlspecialchars((string) $it['product_name']) ?></td>
                <td style="text-align:right">R$ <?= number_format((float) $it['line_total'], 2, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p style="margin-top:12px;font-size:16px"><strong>Total: R$ <?= number_format((float) $order['total'], 2, ',', '.') ?></strong></p>
    <p class="meta">Pagamento: <?= htmlspecialchars((string) $order['payment_method']) ?> / <?= htmlspecialchars((string) $order['payment_status']) ?></p>
</body>
</html>
