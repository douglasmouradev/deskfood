<?php
declare(strict_types=1);
/** @var array<string,mixed>|null $unit */
/** @var string $csrf */
$isEdit = is_array($unit);
$action = $isEdit ? '/admin/unidades/' . (int) $unit['id'] . '/editar' : '/admin/unidades/nova';
?>
<form method="post" action="<?= htmlspecialchars($action) ?>" class="mx-auto max-w-3xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="text-sm font-medium">Nome</label>
            <input name="name" required value="<?= htmlspecialchars((string) ($unit['name'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <?php if (!$isEdit): ?>
        <div>
            <label class="text-sm font-medium">Slug URL</label>
            <input name="slug" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" placeholder="auto se vazio">
        </div>
        <?php else: ?>
        <div>
            <label class="text-sm font-medium">Slug</label>
            <p class="mt-1 text-sm text-slate-600"><?= htmlspecialchars((string) $unit['slug']) ?></p>
        </div>
        <?php endif; ?>
        <div>
            <label class="text-sm font-medium">CNPJ</label>
            <input name="cnpj" required value="<?= htmlspecialchars((string) ($unit['cnpj'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="text-sm font-medium">Logradouro</label>
            <input name="address_street" required value="<?= htmlspecialchars((string) ($unit['address_street'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Número</label>
            <input name="address_number" required value="<?= htmlspecialchars((string) ($unit['address_number'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Complemento</label>
            <input name="address_complement" value="<?= htmlspecialchars((string) ($unit['address_complement'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Bairro</label>
            <input name="neighborhood" required value="<?= htmlspecialchars((string) ($unit['neighborhood'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Cidade</label>
            <input name="city" required value="<?= htmlspecialchars((string) ($unit['city'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">UF</label>
            <input name="state" maxlength="2" required value="<?= htmlspecialchars((string) ($unit['state'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">CEP</label>
            <input name="zip" required value="<?= htmlspecialchars((string) ($unit['zip'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Telefone</label>
            <input name="phone" required value="<?= htmlspecialchars((string) ($unit['phone'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Raio (km)</label>
            <input name="delivery_radius_km" type="number" step="0.1" value="<?= htmlspecialchars((string) ($unit['delivery_radius_km'] ?? '5')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Taxa entrega</label>
            <input name="delivery_fee" type="number" step="0.01" value="<?= htmlspecialchars((string) ($unit['delivery_fee'] ?? '0')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Pedido mínimo (R$)</label>
            <input name="minimum_order" type="number" step="0.01" min="0" value="<?= htmlspecialchars((string) ($unit['minimum_order'] ?? '0')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="text-sm font-medium">Horários (JSON)</label>
            <textarea name="business_hours" rows="4" class="mt-1 w-full rounded-xl border px-3 py-2 font-mono text-xs"><?= htmlspecialchars((string) ($unit['business_hours'] ?? '{}')) ?></textarea>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <h2 class="text-lg font-semibold text-slate-900">Pagamentos desta unidade</h2>
        <p class="mt-1 text-xs text-slate-500">Deixe em branco para herdar do arquivo <code class="rounded bg-slate-100 px-1">.env</code> global.</p>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium">Provedor</label>
                <select name="payment_provider" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
                    <option value="" <?= empty($unit['payment_provider'] ?? '') ? 'selected' : '' ?>>Herdar do .env</option>
                    <option value="mock" <?= ($unit['payment_provider'] ?? '') === 'mock' ? 'selected' : '' ?>>Mock (dev)</option>
                    <option value="mercadopago" <?= ($unit['payment_provider'] ?? '') === 'mercadopago' ? 'selected' : '' ?>>Mercado Pago</option>
                    <option value="efipay" <?= ($unit['payment_provider'] ?? '') === 'efipay' ? 'selected' : '' ?>>Efi Pay</option>
                </select>
            </div>
            <div class="flex flex-col justify-end gap-2 text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" name="payment_pix_enabled" value="1" <?= ($unit['payment_pix_enabled'] ?? 1) ? 'checked' : '' ?>> PIX ativo</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="payment_card_enabled" value="1" <?= ($unit['payment_card_enabled'] ?? 0) ? 'checked' : '' ?>> Cartão online (Mercado Pago)</label>
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium">Chave PIX (Efi / fallback)</label>
                <input name="pix_key" value="<?= htmlspecialchars((string) ($unit['pix_key'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" placeholder="e-mail, CPF ou chave aleatória">
            </div>
            <div>
                <label class="text-sm font-medium">MP Access Token</label>
                <input name="mp_access_token" type="password" autocomplete="new-password" value="" placeholder="<?= !empty($unit['mp_access_token']) ? 'Configurado — deixe vazio para manter' : 'Access token Mercado Pago' ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">MP Public Key (opcional)</label>
                <input name="mp_public_key" value="<?= htmlspecialchars((string) ($unit['mp_public_key'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Efi Client ID</label>
                <input name="efi_client_id" value="<?= htmlspecialchars((string) ($unit['efi_client_id'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Efi Client Secret</label>
                <input name="efi_client_secret" type="password" autocomplete="new-password" value="" placeholder="<?= !empty($unit['efi_client_secret']) ? 'Configurado — deixe vazio para manter' : 'Client secret Efi' ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="efi_sandbox" value="1" <?= !empty($unit['efi_sandbox']) ? 'checked' : '' ?>> Efi sandbox</label>
            </div>
        </div>
    </div>
    <button class="rounded-full bg-slate-900 px-6 py-2 text-sm font-semibold text-white"><?= $isEdit ? 'Atualizar' : 'Salvar' ?></button>
</form>
