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
$total = is_array($enriched)
    ? (float) $enriched['subtotal'] + (float) ($enriched['delivery_fee'] ?? 0)
    : 0.0;
?>
<div class="max-w-4xl">
    <h1 class="font-display text-2xl font-semibold text-zinc-900">Finalizar pedido</h1>
    <p class="mt-1 text-sm text-zinc-600"><?= htmlspecialchars((string) $unit['name']) ?></p>

    <?php if (!$unitOpen): ?>
        <div class="mt-4 rounded-2xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-sm text-amber-950"><?= htmlspecialchars($hoursLabel ?? 'Unidade fechada') ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <p class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?= htmlspecialchars((string) $_SESSION['flash_error']) ?></p>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_300px] lg:items-start">
        <form method="post" action="/cliente/checkout" class="space-y-8" id="checkout-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <section class="df-card p-5 md:p-6">
                <h2 class="font-semibold text-zinc-900">Entrega</h2>
                <div class="mt-4 flex flex-wrap gap-4 text-sm">
                    <label class="flex items-center gap-2"><input type="radio" name="delivery_type" value="delivery" checked data-delivery-toggle class="text-zinc-900"> Entregar no endereço</label>
                    <label class="flex items-center gap-2"><input type="radio" name="delivery_type" value="pickup" data-delivery-toggle class="text-zinc-900"> Retirar no balcão</label>
                </div>

                <?php if ($addresses !== []): ?>
                    <div id="saved-addresses" class="mt-5 space-y-2">
                        <p class="df-label">Endereços salvos</p>
                        <?php foreach ($addresses as $i => $addr): ?>
                            <label class="flex items-start gap-3 rounded-xl border border-zinc-200 px-3 py-3 text-sm has-[:checked]:border-zinc-900 has-[:checked]:bg-stone-50">
                                <input type="radio" name="saved_address_id" value="<?= (int) $addr['id'] ?>" <?= $i === 0 ? 'checked' : '' ?> class="mt-1">
                                <span class="text-zinc-700"><?= htmlspecialchars((string) $addr['street']) ?>, <?= htmlspecialchars((string) $addr['number']) ?> — <?= htmlspecialchars((string) $addr['neighborhood']) ?>, <?= htmlspecialchars((string) $addr['city']) ?>/<?= htmlspecialchars((string) $addr['state']) ?></span>
                            </label>
                        <?php endforeach; ?>
                        <label class="flex items-center gap-2 text-sm text-zinc-600">
                            <input type="radio" name="saved_address_id" value="0"> Informar outro endereço
                        </label>
                    </div>
                <?php endif; ?>

                <div id="delivery-fields" class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="df-label" for="delivery_zip">CEP</label>
                        <input id="delivery_zip" name="delivery_zip" required value="<?= htmlspecialchars((string) ($defaultAddress['zip'] ?? '')) ?>" class="df-input mt-1.5 max-w-xs">
                    </div>
                    <div class="md:col-span-2">
                        <label class="df-label" for="delivery_street">Rua</label>
                        <input id="delivery_street" name="delivery_street" required value="<?= htmlspecialchars((string) ($defaultAddress['street'] ?? '')) ?>" class="df-input mt-1.5">
                    </div>
                    <div>
                        <label class="df-label">Número</label>
                        <input name="delivery_number" required value="<?= htmlspecialchars((string) ($defaultAddress['number'] ?? '')) ?>" class="df-input mt-1.5">
                    </div>
                    <div>
                        <label class="df-label">Complemento</label>
                        <input name="delivery_complement" value="<?= htmlspecialchars((string) ($defaultAddress['complement'] ?? '')) ?>" class="df-input mt-1.5">
                    </div>
                    <div>
                        <label class="df-label" for="delivery_neighborhood">Bairro</label>
                        <input id="delivery_neighborhood" name="delivery_neighborhood" required value="<?= htmlspecialchars((string) ($defaultAddress['neighborhood'] ?? '')) ?>" class="df-input mt-1.5">
                    </div>
                    <div>
                        <label class="df-label" for="delivery_city">Cidade</label>
                        <input id="delivery_city" name="delivery_city" required value="<?= htmlspecialchars((string) ($defaultAddress['city'] ?? '')) ?>" class="df-input mt-1.5">
                    </div>
                    <div>
                        <label class="df-label" for="delivery_state">UF</label>
                        <input id="delivery_state" name="delivery_state" maxlength="2" required value="<?= htmlspecialchars((string) ($defaultAddress['state'] ?? '')) ?>" class="df-input mt-1.5">
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-zinc-700"><input type="checkbox" name="save_address" value="1" class="rounded border-zinc-300"> Salvar este endereço</label>
                    </div>
                </div>
            </section>

            <section class="df-card p-5 md:p-6">
                <h2 class="font-semibold text-zinc-900">Detalhes</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="df-label">Cupom</label>
                        <input name="coupon_code" placeholder="Se tiver, digite aqui" class="df-input mt-1.5 max-w-xs uppercase">
                    </div>
                    <div>
                        <label class="df-label">Observações para a cozinha</label>
                        <textarea name="notes" rows="2" placeholder="Ex.: sem cebola, interfone 402" class="df-input mt-1.5"></textarea>
                    </div>
                </div>
            </section>

            <section class="df-card p-5 md:p-6">
                <h2 class="font-semibold text-zinc-900">Pagamento</h2>
                <div class="mt-4 space-y-2">
                    <?php if ($pixAvailable): ?>
                    <label class="pay-option"><input type="radio" name="payment_method" value="pix" checked> <span><span class="block font-medium text-zinc-900">PIX</span><span class="text-xs text-zinc-500">QR Code com confirmação automática</span></span></label>
                    <?php endif; ?>
                    <?php if ($cardAvailable): ?>
                    <label class="pay-option"><input type="radio" name="payment_method" value="card" <?= !$pixAvailable ? 'checked' : '' ?>> <span><span class="block font-medium text-zinc-900">Cartão online</span><span class="text-xs text-zinc-500">Ambiente seguro Mercado Pago</span></span></label>
                    <?php endif; ?>
                    <label class="pay-option"><input type="radio" name="payment_method" value="on_delivery" <?= !$pixAvailable && !$cardAvailable ? 'checked' : '' ?>> <span><span class="block font-medium text-zinc-900">Na entrega</span><span class="text-xs text-zinc-500">Dinheiro ou cartão físico</span></span></label>
                </div>
                <?php if (!$pixAvailable && !$cardAvailable): ?>
                <p class="mt-3 text-xs text-amber-800">Pagamento online indisponível nesta unidade.</p>
                <?php endif; ?>
                <div id="on_delivery_box" class="mt-4 hidden space-y-3 rounded-xl border border-zinc-100 bg-stone-50 p-4 text-sm">
                    <label class="flex items-center gap-2"><input type="radio" name="on_delivery_type" value="cash" checked> Dinheiro</label>
                    <label class="flex items-center gap-2"><input type="radio" name="on_delivery_type" value="card"> Cartão na entrega</label>
                    <div>
                        <label class="df-label">Troco para</label>
                        <input type="number" step="0.01" name="change_for" class="df-input mt-1.5" placeholder="R$ 0,00">
                    </div>
                </div>
            </section>

            <button type="submit" class="df-btn-primary w-full py-3.5 lg:hidden" <?= $unitOpen ? '' : 'disabled' ?>>Confirmar pedido</button>
        </form>

        <aside class="df-summary">
            <?php if (is_array($enriched)): ?>
                <p class="text-sm font-medium text-zinc-500">Total estimado</p>
                <p class="tabular mt-2 text-3xl font-semibold text-zinc-900">R$ <?= number_format($total, 2, ',', '.') ?></p>
                <dl class="df-divider mt-4 space-y-2 pt-4 text-sm">
                    <div class="flex justify-between"><dt class="text-zinc-600">Subtotal</dt><dd class="tabular">R$ <?= number_format((float) $enriched['subtotal'], 2, ',', '.') ?></dd></div>
                    <?php if ((float) $enriched['delivery_fee'] > 0): ?>
                    <div class="flex justify-between"><dt class="text-zinc-600">Entrega</dt><dd class="tabular">R$ <?= number_format((float) $enriched['delivery_fee'], 2, ',', '.') ?></dd></div>
                    <?php endif; ?>
                </dl>
            <?php endif; ?>
            <button type="submit" form="checkout-form" class="df-btn-primary mt-6 hidden w-full py-3.5 lg:inline-flex" <?= $unitOpen ? '' : 'disabled' ?>>Confirmar pedido</button>
            <p class="mt-4 text-center text-[11px] leading-relaxed text-zinc-400">Ao confirmar, você concorda com os termos da loja e da plataforma.</p>
        </aside>
    </div>
</div>

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
