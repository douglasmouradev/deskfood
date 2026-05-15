<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $units */
/** @var string $csrf */
?>
<form method="post" action="/admin/operadores/nova" class="mx-auto max-w-lg space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div>
        <label class="text-sm font-medium">Nome</label>
        <input name="name" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">E-mail</label>
        <input name="email" type="email" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Telefone</label>
        <input name="phone" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Unidade</label>
        <select name="unit_id" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
            <option value="">Selecione…</option>
            <?php foreach ($units as $u): ?>
                <option value="<?= (int) $u['id'] ?>"><?= htmlspecialchars((string) $u['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="text-sm font-medium">Senha inicial (mín. 8)</label>
        <input name="password" type="password" minlength="8" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
    </div>
    <button class="rounded-full bg-slate-900 px-6 py-2 text-sm font-semibold text-white">Criar operador</button>
</form>
