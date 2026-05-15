<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $leads */
/** @var string $csrf */
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-slate-600"><?= count($leads) ?> lead(s) recentes (máx. 200).</p>
    <form method="post" action="/admin/leads/exportar.csv">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <button type="submit" class="rounded-full border border-slate-300 bg-white px-5 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50">Exportar CSV</button>
    </form>
</div>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead class="border-b bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Data</th>
                <th class="px-4 py-3">Nome</th>
                <th class="px-4 py-3">E-mail</th>
                <th class="px-4 py-3">Telefone</th>
                <th class="px-4 py-3">Empresa</th>
                <th class="px-4 py-3">Mensagem</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($leads as $l): ?>
                <tr>
                    <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500"><?= htmlspecialchars((string) ($l['created_at'] ?? '')) ?></td>
                    <td class="px-4 py-3 font-medium"><?= htmlspecialchars((string) ($l['name'] ?? '')) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars((string) ($l['email'] ?? '')) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars((string) ($l['phone'] ?? '')) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars((string) ($l['company'] ?? '')) ?></td>
                    <td class="max-w-xs truncate px-4 py-3" title="<?= htmlspecialchars((string) ($l['message'] ?? '')) ?>"><?= htmlspecialchars((string) ($l['message'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($leads === []): ?>
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Nenhum lead ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
