<?php
declare(strict_types=1);
/** @var int $orders_today */
/** @var float $revenue_today */
/** @var list<array<string,mixed>> $by_day */
/** @var string $csrf */
?>
<div class="grid gap-4 md:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-xs uppercase text-slate-500">Pedidos hoje</p>
        <p class="mt-2 text-3xl font-bold text-slate-900"><?= (int) $orders_today ?></p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-xs uppercase text-slate-500">Receita hoje (paga)</p>
        <p class="mt-2 text-3xl font-bold text-emerald-700">R$ <?= number_format((float) $revenue_today, 2, ',', '.') ?></p>
    </div>
</div>

<form method="post" action="/admin/relatorios/pedidos.csv" class="mt-6">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <button type="submit" class="rounded-full border border-slate-300 bg-white px-5 py-2 text-sm font-semibold">Exportar pedidos CSV</button>
</form>

<div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr><th class="px-4 py-3">Dia</th><th class="px-4 py-3">Pedidos</th><th class="px-4 py-3">Receita</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($by_day as $row): ?>
                <tr>
                    <td class="px-4 py-3"><?= htmlspecialchars((string) $row['d']) ?></td>
                    <td class="px-4 py-3"><?= (int) $row['c'] ?></td>
                    <td class="px-4 py-3">R$ <?= number_format((float) $row['rev'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($by_day === []): ?>
                <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Sem dados nos últimos 7 dias.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
