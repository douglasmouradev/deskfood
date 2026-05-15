<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $logs */
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead class="border-b bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Quando</th>
                <th class="px-4 py-3">Ator</th>
                <th class="px-4 py-3">Ação</th>
                <th class="px-4 py-3">Entidade</th>
                <th class="px-4 py-3">Detalhes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="whitespace-nowrap px-4 py-3 text-xs"><?= htmlspecialchars((string) ($log['created_at'] ?? '')) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars((string) ($log['actor_type'] ?? '')) ?> #<?= (int) ($log['actor_id'] ?? 0) ?></td>
                    <td class="px-4 py-3 font-mono text-xs"><?= htmlspecialchars((string) ($log['action'] ?? '')) ?></td>
                    <td class="px-4 py-3 text-xs"><?= htmlspecialchars((string) ($log['entity_type'] ?? '')) ?> #<?= (int) ($log['entity_id'] ?? 0) ?></td>
                    <td class="max-w-md truncate px-4 py-3 font-mono text-[10px] text-slate-600" title="<?= htmlspecialchars((string) ($log['details'] ?? '')) ?>">
                        <?= htmlspecialchars((string) ($log['details'] ?? '—')) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($logs === []): ?>
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Nenhum registro de auditoria.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
