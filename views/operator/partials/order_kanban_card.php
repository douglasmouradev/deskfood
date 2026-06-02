<?php
declare(strict_types=1);
/** @var array<string,mixed> $order */
/** @var string $csrf */
/** @var list<array<string,mixed>> $motoboys */
$st = (string) ($order['status'] ?? '');
$pixPend = ($order['payment_method'] ?? '') === 'pix' && ($order['payment_status'] ?? '') === 'pendente';
$cardPend = ($order['payment_method'] ?? '') === 'card' && ($order['payment_status'] ?? '') === 'pendente';
?>
<div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
    <div class="flex items-start justify-between gap-2">
        <p class="font-mono text-xs font-semibold text-slate-900"><?= htmlspecialchars((string) $order['order_number']) ?></p>
        <?php if ($pixPend): ?>
            <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-900">PIX</span>
        <?php elseif ($cardPend): ?>
            <span class="shrink-0 rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-900">Cartão</span>
        <?php endif; ?>
    </div>
    <p class="mt-1 text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) $order['customer_name']) ?></p>
    <p class="text-xs text-slate-500"><?= htmlspecialchars((string) $order['customer_phone']) ?></p>
    <p class="mt-2 text-sm font-bold text-orange-600">R$ <?= number_format((float) $order['total'], 2, ',', '.') ?></p>
    <p class="mt-1 text-[10px] uppercase tracking-wide text-slate-400"><?= htmlspecialchars($st) ?></p>
    <a href="/operador/pedidos/<?= (int) $order['id'] ?>/imprimir" target="_blank" rel="noopener" class="mt-2 inline-block text-[10px] font-semibold text-slate-600 underline hover:text-orange-600">Imprimir comanda</a>

    <div class="mt-3 space-y-2 border-t border-slate-100 pt-3">
        <form method="post" action="/operador/pedidos/<?= (int) $order['id'] ?>/status" class="space-y-1.5">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <select name="status" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                <?php
                $opts = [
                    'confirmado' => 'Confirmado',
                    'em_preparo' => 'Em preparo',
                    'saiu_entrega' => 'Saiu entrega',
                    'entregue' => 'Entregue',
                    'cancelado' => 'Cancelado',
                ];
                foreach ($opts as $val => $label):
                    $sel = ($st === $val || ($st === 'pendente' && $val === 'confirmado')) ? ' selected' : '';
                ?>
                    <option value="<?= htmlspecialchars($val) ?>"<?= $sel ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($st === 'pendente'): ?>
                <p class="text-[10px] text-slate-500">Pedido novo — escolha <strong>Confirmado</strong> para aceitar.</p>
            <?php endif; ?>
            <input name="note" placeholder="Nota interna" class="w-full rounded-lg border border-slate-200 px-2 py-1 text-xs">
            <input name="cancel_reason" placeholder="Motivo (se cancelar)" class="w-full rounded-lg border border-slate-200 px-2 py-1 text-xs">
            <button type="submit" class="w-full rounded-full bg-slate-900 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">Atualizar status</button>
        </form>
        <?php if ($st === 'em_preparo' && $motoboys !== []): ?>
            <form method="post" action="/operador/pedidos/<?= (int) $order['id'] ?>/motoboy" class="space-y-1.5">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <select name="motoboy_id" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                    <?php foreach ($motoboys as $m): ?>
                        <option value="<?= (int) $m['id'] ?>"><?= htmlspecialchars((string) $m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="w-full rounded-full bg-orange-500 py-1.5 text-xs font-semibold text-white hover:bg-orange-600">Atribuir motoboy</button>
            </form>
        <?php elseif ($st === 'em_preparo' && $motoboys === []): ?>
            <p class="text-[10px] text-amber-700">Cadastre um motoboy para despachar.</p>
        <?php endif; ?>
    </div>
</div>
