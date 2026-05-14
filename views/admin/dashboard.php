<?php
declare(strict_types=1);
/** @var int $units */
/** @var int $active_units */
/** @var int $orders_today */
/** @var float $revenue_total */
/** @var bool $show_setup_hint */
?>
<?php if (!empty($show_setup_hint)): ?>
    <div class="mb-6 rounded-2xl border border-orange-200 bg-orange-50 px-4 py-4 text-sm text-orange-950">
        <p class="font-semibold">Configure sua primeira unidade</p>
        <p class="mt-1 text-orange-900/90">Cadastre ao menos uma unidade ativa para começar a receber pedidos e vincular operadores.</p>
        <a href="/admin/unidades/nova" class="mt-3 inline-flex rounded-full bg-orange-600 px-4 py-2 text-xs font-semibold text-white hover:bg-orange-700">Nova unidade</a>
    </div>
<?php endif; ?>

<div class="grid gap-4 md:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs uppercase text-slate-500">Unidades</p>
        <p class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) $units ?></p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs uppercase text-slate-500">Ativas</p>
        <p class="mt-2 text-3xl font-semibold text-emerald-700"><?= (int) $active_units ?></p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs uppercase text-slate-500">Pedidos hoje</p>
        <p class="mt-2 text-3xl font-semibold text-orange-600"><?= (int) $orders_today ?></p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs uppercase text-slate-500">Receita paga</p>
        <p class="mt-2 text-3xl font-semibold text-slate-900">R$ <?= number_format($revenue_total, 2, ',', '.') ?></p>
    </div>
</div>
