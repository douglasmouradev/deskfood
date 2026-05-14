<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $units */
/** @var string $csrf */
?>
<div class="flex items-center justify-between">
    <p class="text-sm text-slate-600">Gerencie filiais, taxas e disponibilidade.</p>
    <a href="/admin/unidades/nova" class="rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white">Nova unidade</a>
</div>
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
        <tr>
            <th class="px-4 py-3">Nome</th>
            <th class="px-4 py-3">Cidade</th>
            <th class="px-4 py-3">Taxa</th>
            <th class="px-4 py-3">Ativa</th>
            <th class="px-4 py-3"></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($units as $u): ?>
            <tr class="border-t border-slate-100">
                <td class="px-4 py-3 font-medium"><?= htmlspecialchars((string) $u['name']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars((string) $u['city']) ?></td>
                <td class="px-4 py-3">R$ <?= number_format((float) $u['delivery_fee'], 2, ',', '.') ?></td>
                <td class="px-4 py-3"><?= !empty($u['is_active']) ? 'Sim' : 'Não' ?></td>
                <td class="px-4 py-3 text-right">
                    <form method="post" action="/admin/unidades/<?= (int) $u['id'] ?>/toggle" class="inline">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <button class="text-xs font-semibold text-orange-600">Alternar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
