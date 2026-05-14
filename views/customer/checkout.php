<?php
declare(strict_types=1);
/** @var array<string,mixed> $cart */
/** @var array<string,mixed> $unit */
/** @var array<string,mixed> $user */
/** @var string $csrf */
?>
<h1 class="text-2xl font-bold">Checkout</h1>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <p class="mt-3 text-sm text-red-700"><?= htmlspecialchars((string) $_SESSION['flash_error']) ?></p>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
<form method="post" action="/cliente/checkout" class="mt-6 grid gap-4 md:grid-cols-2">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div class="md:col-span-2">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Entrega</h2>
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium">Rua</label>
        <input name="delivery_street" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Número</label>
        <input name="delivery_number" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Complemento</label>
        <input name="delivery_complement" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Bairro</label>
        <input name="delivery_neighborhood" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Cidade</label>
        <input name="delivery_city" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">UF</label>
        <input name="delivery_state" maxlength="2" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">CEP</label>
        <input name="delivery_zip" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium">Observações</label>
        <textarea name="notes" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></textarea>
    </div>
    <div class="md:col-span-2">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Pagamento</h2>
        <div class="mt-3 space-y-2 text-sm">
            <label class="flex items-center gap-2"><input type="radio" name="payment_method" value="pix" checked> PIX (QR Code)</label>
            <label class="flex items-center gap-2"><input type="radio" name="payment_method" value="on_delivery"> Na entrega</label>
        </div>
        <div id="on_delivery_box" class="mt-4 hidden space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">
            <p class="font-medium">Pagar na entrega</p>
            <label class="flex items-center gap-2"><input type="radio" name="on_delivery_type" value="cash" checked> Dinheiro</label>
            <label class="flex items-center gap-2"><input type="radio" name="on_delivery_type" value="card"> Cartão</label>
            <label class="block">Troco para quanto? (opcional)
                <input type="number" step="0.01" name="change_for" class="mt-1 w-full rounded-lg border px-2 py-1">
            </label>
        </div>
        <script>
            (function () {
                const box = document.getElementById('on_delivery_box');
                function sync() {
                    const sel = document.querySelector('input[name=payment_method]:checked');
                    if (!box || !sel) return;
                    box.classList.toggle('hidden', sel.value !== 'on_delivery');
                }
                document.querySelectorAll('input[name=payment_method]').forEach(function (el) {
                    el.addEventListener('change', sync);
                });
                sync();
            })();
        </script>
    </div>
    <div class="md:col-span-2">
        <button class="w-full rounded-full bg-orange-500 py-3 text-sm font-semibold text-white">Confirmar pedido</button>
    </div>
</form>
