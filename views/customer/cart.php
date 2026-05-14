<?php
declare(strict_types=1);
/** @var array<string,mixed>|null $cart */
/** @var array<string,mixed>|null $unit */
/** @var string $csrf */
?>
<h1 class="text-2xl font-bold text-slate-900">Carrinho</h1>
<?php if (!is_array($cart) || empty($cart['items'])): ?>
    <p class="mt-4 text-slate-600">Seu carrinho está vazio.</p>
    <a class="mt-4 inline-flex rounded-full bg-orange-500 px-5 py-2 text-sm font-semibold text-white" href="/">Ver unidades</a>
<?php else: ?>
    <form method="post" action="/cliente/carrinho/atualizar" class="mt-6 space-y-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <?php foreach ($cart['items'] as $idx => $it): ?>
            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Produto #<?= (int) $it['product_id'] ?></p>
                    <p class="text-xs text-slate-500">Adicionais: <?= htmlspecialchars(implode(',', array_map('strval', $it['addons'] ?? []))) ?></p>
                </div>
                <input type="number" name="qty[<?= (int) $idx ?>]" value="<?= (int) $it['qty'] ?>" min="0" class="w-20 rounded-lg border px-2 py-1 text-sm">
            </div>
        <?php endforeach; ?>
        <button class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Atualizar carrinho</button>
    </form>
    <?php if (is_array($unit)): ?>
        <a class="mt-6 inline-flex rounded-full bg-orange-500 px-6 py-3 text-sm font-semibold text-white" href="/cliente/checkout">Continuar</a>
    <?php endif; ?>
<?php endif; ?>
