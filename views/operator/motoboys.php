<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $motoboys */
/** @var string $csrf */
/** @var string $app_url */
?>
<div class="rounded-2xl border border-slate-200 bg-white p-4">
    <h2 class="font-semibold">Novo motoboy</h2>
    <form method="post" action="/operador/motoboys" class="mt-3 grid gap-2 md:grid-cols-2">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input name="name" required class="rounded-lg border px-3 py-2 text-sm" placeholder="Nome completo">
        <input name="phone" required class="rounded-lg border px-3 py-2 text-sm" placeholder="Telefone">
        <input name="cpf" required class="md:col-span-2 rounded-lg border px-3 py-2 text-sm" placeholder="CPF (somente números)">
        <button class="md:col-span-2 rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white">Cadastrar</button>
    </form>
</div>
<div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
        <tr><th class="px-3 py-2">Nome</th><th class="px-3 py-2">Telefone</th><th class="px-3 py-2">Link</th><th class="px-3 py-2"></th></tr>
        </thead>
        <tbody>
        <?php foreach ($motoboys as $m): ?>
            <tr class="border-t border-slate-100">
                <td class="px-3 py-2"><?= htmlspecialchars((string) $m['name']) ?></td>
                <td class="px-3 py-2"><?= htmlspecialchars((string) $m['phone']) ?></td>
                <td class="px-3 py-2 text-xs break-all">
                    <a class="text-orange-600 underline" href="<?= htmlspecialchars($app_url) ?>/m/<?= htmlspecialchars((string) $m['access_token']) ?>" target="_blank">Abrir painel</a>
                </td>
                <td class="px-3 py-2">
                    <form method="post" action="/operador/motoboys/<?= (int) $m['id'] ?>/revogar" class="inline" onsubmit="return confirm('Gerar novo link? O link antigo deixa de funcionar.');">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit" class="text-xs font-semibold text-red-700 hover:underline">Revogar link</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
