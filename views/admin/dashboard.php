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

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
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

<div class="mt-4 grid gap-4 md:grid-cols-3">
    <a href="/admin/leads" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-orange-200">
        <p class="text-xs uppercase text-slate-500">Leads comerciais</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900"><?= (int) ($leads_count ?? 0) ?></p>
    </a>
    <a href="/admin/operadores" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-orange-200">
        <p class="text-xs uppercase text-slate-500">Operadores</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900"><?= (int) ($operators_count ?? 0) ?></p>
    </a>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs uppercase text-slate-500">Nota média</p>
        <p class="mt-2 text-2xl font-semibold text-amber-600"><?= isset($avg_rating) && $avg_rating !== null ? number_format((float) $avg_rating, 1, ',', '.') . ' ★' : '—' ?></p>
    </div>
</div>

<form method="post" action="/admin/relatorios/pedidos.csv" class="mt-6">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Helpers\Csrf::token()) ?>">
    <button type="submit" class="rounded-full border border-slate-300 bg-white px-5 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50">Exportar todos os pedidos (CSV)</button>
</form>
