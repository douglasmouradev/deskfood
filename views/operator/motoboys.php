<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $motoboys */
/** @var string $csrf */
/** @var array{name?:string,url?:string,expires?:string}|null $link_flash */
?>
<?php if (!empty($link_flash['url'])): ?>
<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-950">
    <p class="font-semibold">Link de acesso gerado<?= !empty($link_flash['name']) ? ' — ' . htmlspecialchars((string) $link_flash['name']) : '' ?></p>
    <p class="mt-2 text-xs">Copie agora. Por segurança, o link completo não será exibido novamente.</p>
    <p class="mt-3 break-all rounded-lg bg-white/80 px-3 py-2 font-mono text-xs"><?= htmlspecialchars((string) $link_flash['url']) ?></p>
    <?php if (!empty($link_flash['expires'])): ?>
    <p class="mt-2 text-xs opacity-80">Válido até <?= htmlspecialchars((string) $link_flash['expires']) ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>
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
        <tr><th class="px-3 py-2">Nome</th><th class="px-3 py-2">Telefone</th><th class="px-3 py-2">Acesso</th><th class="px-3 py-2"></th></tr>
        </thead>
        <tbody>
        <?php foreach ($motoboys as $m): ?>
            <tr class="border-t border-slate-100">
                <td class="px-3 py-2"><?= htmlspecialchars((string) $m['name']) ?></td>
                <td class="px-3 py-2"><?= htmlspecialchars((string) $m['phone']) ?></td>
                <td class="px-3 py-2 text-xs">
                    <?php if (!empty($m['has_link'])): ?>
                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 font-medium text-emerald-800">Link ativo</span>
                    <?php else: ?>
                        <span class="text-slate-400">Sem link</span>
                    <?php endif; ?>
                </td>
                <td class="px-3 py-2">
                    <form method="post" action="/operador/motoboys/<?= (int) $m['id'] ?>/revogar" class="inline" onsubmit="return confirm('Gerar novo link? O link antigo deixa de funcionar.');">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit" class="text-xs font-semibold text-orange-700 hover:underline">Gerar / renovar link</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
