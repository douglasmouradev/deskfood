<?php
declare(strict_types=1);
/** @var string $csrf */
?>
<form method="post" action="/admin/unidades/nova" class="mx-auto max-w-3xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="text-sm font-medium">Nome</label>
            <input name="name" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Slug URL</label>
            <input name="slug" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" placeholder="auto se vazio">
        </div>
        <div>
            <label class="text-sm font-medium">CNPJ</label>
            <input name="cnpj" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="text-sm font-medium">Logradouro</label>
            <input name="address_street" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Número</label>
            <input name="address_number" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Complemento</label>
            <input name="address_complement" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Bairro</label>
            <input name="neighborhood" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Cidade</label>
            <input name="city" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">UF</label>
            <input name="state" maxlength="2" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">CEP</label>
            <input name="zip" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Telefone</label>
            <input name="phone" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Raio (km)</label>
            <input name="delivery_radius_km" type="number" step="0.1" value="5" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Taxa entrega</label>
            <input name="delivery_fee" type="number" step="0.01" value="0" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="text-sm font-medium">Horários (JSON livre)</label>
            <textarea name="business_hours" rows="3" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">{}</textarea>
        </div>
    </div>
    <button class="rounded-full bg-slate-900 px-6 py-2 text-sm font-semibold text-white">Salvar</button>
</form>
