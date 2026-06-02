<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $addresses */
/** @var string $csrf */
?>
<h1 class="text-2xl font-bold text-slate-900">Meus endereços</h1>
<?php if (!empty($_SESSION['flash_ok'])): ?>
    <p class="mt-3 text-sm text-emerald-700"><?= htmlspecialchars((string) $_SESSION['flash_ok']) ?></p>
    <?php unset($_SESSION['flash_ok']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <p class="mt-3 text-sm text-red-700"><?= htmlspecialchars((string) $_SESSION['flash_error']) ?></p>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<ul class="mt-6 space-y-3">
    <?php foreach ($addresses as $a): ?>
        <li class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm">
            <div>
                <p class="font-semibold"><?= htmlspecialchars((string) ($a['label'] ?? 'Casa')) ?><?= (int) ($a['is_default'] ?? 0) === 1 ? ' · padrão' : '' ?></p>
                <p class="text-slate-600"><?= htmlspecialchars((string) $a['street']) ?>, <?= htmlspecialchars((string) $a['number']) ?> — <?= htmlspecialchars((string) $a['neighborhood']) ?></p>
                <p class="text-slate-500"><?= htmlspecialchars((string) $a['city']) ?>/<?= htmlspecialchars((string) $a['state']) ?> · CEP <?= htmlspecialchars((string) $a['zip']) ?></p>
            </div>
            <form method="post" action="/cliente/enderecos/<?= (int) $a['id'] ?>/excluir">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Excluir</button>
            </form>
        </li>
    <?php endforeach; ?>
    <?php if ($addresses === []): ?>
        <li class="text-slate-600">Nenhum endereço salvo ainda.</li>
    <?php endif; ?>
</ul>

<form method="post" action="/cliente/enderecos" class="mt-8 max-w-lg space-y-3 rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="font-semibold">Novo endereço</h2>
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input name="street" required placeholder="Rua" class="w-full rounded-xl border px-3 py-2 text-sm">
    <div class="grid grid-cols-2 gap-2">
        <input name="number" required placeholder="Número" class="rounded-xl border px-3 py-2 text-sm">
        <input name="complement" placeholder="Complemento" class="rounded-xl border px-3 py-2 text-sm">
    </div>
    <input name="neighborhood" required placeholder="Bairro" class="w-full rounded-xl border px-3 py-2 text-sm">
    <div class="grid grid-cols-2 gap-2">
        <input name="city" required placeholder="Cidade" class="rounded-xl border px-3 py-2 text-sm">
        <input name="state" maxlength="2" required placeholder="UF" class="rounded-xl border px-3 py-2 text-sm">
    </div>
    <input name="zip" required placeholder="CEP" class="w-full rounded-xl border px-3 py-2 text-sm">
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1"> Definir como padrão</label>
    <button class="rounded-full bg-orange-500 px-5 py-2 text-sm font-semibold text-white">Salvar</button>
</form>
