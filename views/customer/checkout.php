<?php
declare(strict_types=1);
/** @var array<string,mixed> $unit */
/** @var array<string,mixed>|null $enriched */
/** @var list<array<string,mixed>> $addresses */
/** @var array<string,mixed>|null $defaultAddress */
/** @var string $csrf */
/** @var bool $unitOpen */
/** @var string $hoursLabel */
/** @var bool $pixAvailable */
/** @var bool $cardAvailable */
$unitOpen = $unitOpen ?? true;
$defaultAddress = $defaultAddress ?? null;
$pixAvailable = $pixAvailable ?? true;
$cardAvailable = $cardAvailable ?? false;
?>
<h1 class="text-2xl font-bold text-slate-900">Checkout</h1>
<?php if (is_array($enriched)): ?>
    <p class="mt-2 text-sm text-slate-600">Subtotal: <strong>R$ <?= number_format((float) $enriched['subtotal'], 2, ',', '.') ?></strong></p>
<?php endif; ?>
<?php if (!$unitOpen): ?>
    <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950"><?= htmlspecialchars($hoursLabel ?? 'Unidade fechada') ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <p class="mt-3 text-sm text-red-700"><?= htmlspecialchars((string) $_SESSION['flash_error']) ?></p>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<form method="post" action="/cliente/checkout" class="mt-6 space-y-6" id="checkout-form">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

    <fieldset>
        <legend class="text-sm font-semibold uppercase tracking-wide text-slate-500">Como receber</legend>
        <div class="mt-3 flex flex-wrap gap-4 text-sm">
            <label class="flex items-center gap-2"><input type="radio" name="delivery_type" value="delivery" checked data-delivery-toggle> Entrega</label>
            <label class="flex items-center gap-2"><input type="radio" name="delivery_type" value="pickup" data-delivery-toggle> Retirada no balcão</label>
        </div>
    </fieldset>

    <?php if ($addresses !== []): ?>
        <div id="saved-addresses" class="space-y-2">
            <p class="text-sm font-medium text-slate-700">Endereço salvo</p>
            <?php foreach ($addresses as $i => $addr): ?>
                <label class="flex items-start gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                    <input type="radio" name="saved_address_id" value="<?= (int) $addr['id'] ?>" <?= $i === 0 ? 'checked' : '' ?> class="mt-1">
                    <span><?= htmlspecialchars((string) $addr['street']) ?>, <?= htmlspecialchars((string) $addr['number']) ?> — <?= htmlspecialchars((string) $addr['neighborhood']) ?>, <?= htmlspecialchars((string) $addr['city']) ?>/<?= htmlspecialchars((string) $addr['state']) ?></span>
                </label>
            <?php endforeach; ?>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="radio" name="saved_address_id" value="0"> Usar outro endereço
            </label>
        </div>
    <?php endif; ?>

    <div id="delivery-fields" class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="text-sm font-medium">CEP</label>
            <input id="delivery_zip" name="delivery_zip" required
                   value="<?= htmlspecialchars((string) ($defaultAddress['zip'] ?? '')) ?>"
                   class="mt-1 w-full max-w-xs rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="text-sm font-medium">Rua</label>
            <input id="delivery_street" name="delivery_street" required
                   value="<?= htmlspecialchars((string) ($defaultAddress['street'] ?? '')) ?>"
                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Número</label>
            <input name="delivery_number" required value="<?= htmlspecialchars((string) ($defaultAddress['number'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Complemento</label>
            <input name="delivery_complement" value="<?= htmlspecialchars((string) ($defaultAddress['complement'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Bairro</label>
            <input id="delivery_neighborhood" name="delivery_neighborhood" required value="<?= htmlspecialchars((string) ($defaultAddress['neighborhood'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Cidade</label>
            <input id="delivery_city" name="delivery_city" required value="<?= htmlspecialchars((string) ($defaultAddress['city'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">UF</label>
            <input id="delivery_state" name="delivery_state" maxlength="2" required value="<?= htmlspecialchars((string) ($defaultAddress['state'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="save_address" value="1"> Salvar este endereço</label>
        </div>
    </div>

    <div>
        <label class="text-sm font-medium">Cupom de desconto</label>
        <input name="coupon_code" placeholder="Código" class="mt-1 w-full max-w-xs rounded-xl border px-3 py-2 text-sm uppercase">
    </div>

    <div>
        <label class="text-sm font-medium">Observações</label>
        <textarea name="notes" rows="2" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></textarea>
    </div>

    <fieldset>
        <legend class="text-sm font-semibold uppercase tracking-wide text-slate-500">Pagamento</legend>
        <div class="mt-3 space-y-2 text-sm">
            <?php if ($pixAvailable): ?>
            <label class="flex items-center gap-2"><input type="radio" name="payment_method" value="pix" checked> PIX (QR Code)</label>
            <?php endif; ?>
            <?php if ($cardAvailable): ?>
            <label class="flex items-center gap-2"><input type="radio" name="payment_method" value="card" <?= !$pixAvailable ? 'checked' : '' ?>> Cartão de crédito (online)</label>
            <?php endif; ?>
            <label class="flex items-center gap-2"><input type="radio" name="payment_method" value="on_delivery" <?= !$pixAvailable && !$cardAvailable ? 'checked' : '' ?>> Na entrega</label>
        </div>
        <?php if (!$pixAvailable && !$cardAvailable): ?>
        <p class="mt-2 text-xs text-amber-700">Pagamento online indisponível nesta unidade — use pagamento na entrega ou configure credenciais no painel admin.</p>
        <?php endif; ?>
        <div id="on_delivery_box" class="mt-4 hidden space-y-2 rounded-xl border bg-slate-50 p-3 text-sm">
            <label class="flex items-center gap-2"><input type="radio" name="on_delivery_type" value="cash" checked> Dinheiro</label>
            <label class="flex items-center gap-2"><input type="radio" name="on_delivery_type" value="card"> Cartão</label>
            <label class="block">Troco para quanto?
                <input type="number" step="0.01" name="change_for" class="mt-1 w-full rounded-lg border px-2 py-1">
            </label>
        </div>
    </fieldset>

    <button type="submit" class="w-full rounded-full bg-orange-500 py-3 text-sm font-semibold text-white disabled:opacity-50" <?= $unitOpen ? '' : 'disabled' ?>>Confirmar pedido</button>
</form>

<script>
(function () {
    var zip = document.getElementById('delivery_zip');
    if (zip) {
        zip.addEventListener('blur', function () {
            var cep = (zip.value || '').replace(/\D/g, '');
            if (cep.length !== 8) return;
            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.erro) return;
                    var set = function (id, v) { var el = document.getElementById(id); if (el && v) el.value = v; };
                    set('delivery_street', d.logradouro || '');
                    set('delivery_neighborhood', d.bairro || '');
                    set('delivery_city', d.localidade || '');
                    set('delivery_state', d.uf || '');
                }).catch(function () {});
        });
    }
    function syncDelivery() {
        var pickup = document.querySelector('input[name=delivery_type][value=pickup]');
        var fields = document.getElementById('delivery-fields');
        var saved = document.getElementById('saved-addresses');
        var isPickup = pickup && pickup.checked;
        if (fields) fields.classList.toggle('hidden', isPickup);
        if (saved) saved.classList.toggle('hidden', isPickup);
        if (fields) fields.querySelectorAll('input').forEach(function (inp) {
            if (['delivery_street','delivery_number','delivery_neighborhood','delivery_city','delivery_state','delivery_zip'].indexOf(inp.name) >= 0) {
                inp.required = !isPickup;
            }
        });
    }
    document.querySelectorAll('[data-delivery-toggle]').forEach(function (el) { el.addEventListener('change', syncDelivery); });
    syncDelivery();
    var box = document.getElementById('on_delivery_box');
    function syncPay() {
        var sel = document.querySelector('input[name=payment_method]:checked');
        if (box && sel) box.classList.toggle('hidden', sel.value !== 'on_delivery');
    }
    document.querySelectorAll('input[name=payment_method]').forEach(function (el) { el.addEventListener('change', syncPay); });
    syncPay();
})();
</script>
