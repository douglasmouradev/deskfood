<?php
declare(strict_types=1);
/** @var array<string,mixed>|null $cart */
/** @var array<string,mixed>|null $unit */
/** @var array<string,mixed>|null $enriched */
/** @var string $csrf */
?>
<h1 class="text-2xl font-bold text-slate-900">Carrinho</h1>
<?php if (!is_array($enriched) || $enriched['items'] === []): ?>
    <p class="mt-4 text-slate-600">Seu carrinho está vazio.</p>
    <a class="mt-4 inline-flex rounded-full bg-orange-500 px-5 py-2 text-sm font-semibold text-white" href="/">Ver unidades</a>
<?php else: ?>
    <div class="mt-4 rounded-2xl border border-orange-100 bg-orange-50/50 px-4 py-3 text-sm">
        <p>Subtotal: <strong class="text-slate-900">R$ <?= number_format((float) $enriched['subtotal'], 2, ',', '.') ?></strong>
        <?php if ((float) $enriched['delivery_fee'] > 0): ?>
            · Entrega: R$ <?= number_format((float) $enriched['delivery_fee'], 2, ',', '.') ?>
        <?php endif; ?>
        <?php if ((float) $enriched['minimum_order'] > 0): ?>
            · Mínimo: R$ <?= number_format((float) $enriched['minimum_order'], 2, ',', '.') ?>
        <?php endif; ?>
        </p>
        <?php if ((float) $enriched['minimum_order'] > 0 && (float) $enriched['subtotal'] < (float) $enriched['minimum_order']): ?>
            <p class="mt-1 text-amber-800">Faltam R$ <?= number_format((float) $enriched['minimum_order'] - (float) $enriched['subtotal'], 2, ',', '.') ?> para o pedido mínimo.</p>
        <?php endif; ?>
    </div>
    <form method="post" action="/cliente/carrinho/atualizar" class="mt-6 space-y-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <?php foreach ($enriched['items'] as $line): ?>
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-900"><?= htmlspecialchars((string) $line['product_name']) ?></p>
                        <?php if (!empty($line['addons'])): ?>
                            <p class="mt-1 text-xs text-slate-500">
                                <?php foreach ($line['addons'] as $a): ?>
                                    + <?= htmlspecialchars((string) $a['name']) ?> (R$ <?= number_format((float) $a['price'], 2, ',', '.') ?>)
                                <?php endforeach; ?>
                            </p>
                        <?php endif; ?>
                        <p class="mt-1 text-sm text-orange-600">R$ <?= number_format((float) $line['unit_price'], 2, ',', '.') ?> × <?= (int) $line['qty'] ?> = <strong>R$ <?= number_format((float) $line['line_total'], 2, ',', '.') ?></strong></p>
                    </div>
                    <input type="number" name="qty[<?= (int) $line['index'] ?>]" value="<?= (int) $line['qty'] ?>" min="0" class="w-20 rounded-lg border px-2 py-1 text-sm" aria-label="Quantidade">
                </div>
            </div>
        <?php endforeach; ?>
        <button class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Atualizar carrinho</button>
    </form>
    <?php
    $canCheckout = (float) $enriched['minimum_order'] <= 0 || (float) $enriched['subtotal'] >= (float) $enriched['minimum_order'];
    ?>
    <?php if (is_array($unit) && $canCheckout): ?>
        <a class="mt-6 inline-flex rounded-full bg-orange-500 px-6 py-3 text-sm font-semibold text-white" href="/cliente/checkout">Continuar para checkout</a>
    <?php elseif (is_array($unit)): ?>
        <p class="mt-4 text-sm text-amber-800">Adicione mais itens para atingir o pedido mínimo.</p>
    <?php endif; ?>
<?php endif; ?>
