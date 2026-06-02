<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $operators */
/** @var string $csrf */
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-slate-600">Operadores acessam o painel em <code class="rounded bg-slate-100 px-1">/operador/login</code>.</p>
    <a href="/admin/operadores/nova" class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Novo operador</a>
</div>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <p class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800"><?= htmlspecialchars((string) $_SESSION['flash_error']) ?></p>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead class="border-b bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Nome</th>
                <th class="px-4 py-3">E-mail</th>
                <th class="px-4 py-3">Unidade</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($operators as $op): ?>
                <tr>
                    <td class="px-4 py-3 font-medium"><?= htmlspecialchars((string) $op['name']) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars((string) $op['email']) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars((string) ($op['unit_name'] ?? '—')) ?></td>
                    <td class="px-4 py-3">
                        <?php if ((int) ($op['is_active'] ?? 0) === 1): ?>
                            <span class="text-emerald-700">Ativo</span>
                        <?php else: ?>
                            <span class="text-slate-500">Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form method="post" action="/admin/operadores/<?= (int) $op['id'] ?>/toggle" class="inline">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <button type="submit" class="text-xs font-semibold text-brand-700 hover:underline">
                                <?= (int) ($op['is_active'] ?? 0) === 1 ? 'Desativar' : 'Ativar' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($operators === []): ?>
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Nenhum operador cadastrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
