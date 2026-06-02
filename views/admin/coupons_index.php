<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $coupons */
/** @var list<array<string,mixed>> $units */
/** @var string $csrf */
?>
<div class="grid gap-8 lg:grid-cols-2">
    <form method="post" action="/admin/cupons" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
        <h2 class="font-semibold text-slate-900">Novo cupom</h2>
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input name="code" required placeholder="Código (ex: BEMVINDO10)" class="w-full rounded-xl border px-3 py-2 text-sm uppercase">
        <select name="unit_id" class="w-full rounded-xl border px-3 py-2 text-sm">
            <option value="">Todas as unidades</option>
            <?php foreach ($units as $u): ?>
                <option value="<?= (int) $u['id'] ?>"><?= htmlspecialchars((string) $u['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="discount_type" class="w-full rounded-xl border px-3 py-2 text-sm">
            <option value="percent">Percentual (%)</option>
            <option value="fixed">Valor fixo (R$)</option>
        </select>
        <input name="discount_value" type="number" step="0.01" required placeholder="Valor do desconto" class="w-full rounded-xl border px-3 py-2 text-sm">
        <input name="min_subtotal" type="number" step="0.01" value="0" placeholder="Pedido mínimo" class="w-full rounded-xl border px-3 py-2 text-sm">
        <button class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Criar</button>
    </form>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900">Cupons ativos</h2>
        <ul class="mt-4 space-y-2 text-sm">
            <?php foreach ($coupons as $c): ?>
                <li class="rounded-lg border border-slate-100 px-3 py-3 space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="font-mono font-semibold"><?= htmlspecialchars((string) $c['code']) ?></span>
                        <span class="text-slate-600">
                            <?= ($c['discount_type'] ?? '') === 'percent' ? (float) $c['discount_value'] . '%' : 'R$ ' . number_format((float) $c['discount_value'], 2, ',', '.') ?>
                            · <?= htmlspecialchars((string) ($c['unit_name'] ?? 'Global')) ?>
                            · <?= (int) ($c['is_active'] ?? 0) === 1 ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </div>
                    <form method="post" action="/admin/cupons/<?= (int) $c['id'] ?>/editar" class="flex flex-wrap items-end gap-2 text-xs">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <label class="flex flex-col gap-0.5">Valor<input name="discount_value" type="number" step="0.01" value="<?= (float) $c['discount_value'] ?>" class="w-20 rounded border px-2 py-1"></label>
                        <label class="flex flex-col gap-0.5">Mín.<input name="min_subtotal" type="number" step="0.01" value="<?= (float) ($c['min_subtotal'] ?? 0) ?>" class="w-20 rounded border px-2 py-1"></label>
                        <label class="flex flex-col gap-0.5">Máx. usos<input name="max_uses" type="number" value="<?= htmlspecialchars((string) ($c['max_uses'] ?? '')) ?>" class="w-16 rounded border px-2 py-1" placeholder="∞"></label>
                        <button type="submit" class="rounded bg-slate-800 px-2 py-1 text-white">Salvar</button>
                    </form>
                    <form method="post" action="/admin/cupons/<?= (int) $c['id'] ?>/toggle" class="inline text-xs">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit" class="font-semibold text-brand-700 hover:underline">Alternar ativo/inativo</button>
                    </form>
                </li>
            <?php endforeach; ?>
            <?php if ($coupons === []): ?>
                <li class="text-slate-500">Nenhum cupom ou migration 015 pendente.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>
