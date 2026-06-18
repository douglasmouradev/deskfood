<?php
declare(strict_types=1);
/** @var array<string,mixed>|null $cart */
/** @var array<string,mixed>|null $unit */
/** @var array<string,mixed>|null $enriched */
/** @var string $csrf */
?>
<h1 class="font-display text-2xl font-semibold text-zinc-900">Seu pedido</h1>
<?php if (!is_array($enriched) || $enriched['items'] === []): ?>
    <div class="df-card mt-6 px-6 py-12 text-center">
        <p class="text-zinc-700">O carrinho está vazio.</p>
        <a class="df-btn-primary mt-5 inline-flex" href="/">Escolher uma loja</a>
    </div>
<?php else: ?>
    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_320px] lg:items-start">
        <form method="post" action="/cliente/carrinho/atualizar" class="space-y-3">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <?php foreach ($enriched['items'] as $line): ?>
                <div class="df-card px-4 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-zinc-900"><?= htmlspecialchars((string) $line['product_name']) ?></p>
                            <?php if (!empty($line['addons'])): ?>
                                <p class="mt-1 text-xs text-zinc-500"><?= htmlspecialchars(implode(', ', array_map(static fn ($a) => (string) $a['name'], $line['addons']))) ?></p>
                            <?php endif; ?>
                            <p class="tabular mt-2 text-sm text-zinc-600">
                                R$ <?= number_format((float) $line['unit_price'], 2, ',', '.') ?> × <?= (int) $line['qty'] ?>
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <p class="tabular font-semibold text-zinc-900">R$ <?= number_format((float) $line['line_total'], 2, ',', '.') ?></p>
                            <input type="number" name="qty[<?= (int) $line['index'] ?>]" value="<?= (int) $line['qty'] ?>" min="0" class="df-input w-16 text-center text-xs" aria-label="Quantidade">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <button class="df-btn-ghost mt-2">Atualizar quantidades</button>
        </form>

        <aside class="df-summary">
            <p class="text-sm font-medium text-zinc-500">Resumo</p>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-600">Subtotal</dt>
                    <dd class="tabular font-semibold text-zinc-900">R$ <?= number_format((float) $enriched['subtotal'], 2, ',', '.') ?></dd>
                </div>
                <?php if ((float) $enriched['delivery_fee'] > 0): ?>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-600">Entrega</dt>
                    <dd class="tabular text-zinc-900">R$ <?= number_format((float) $enriched['delivery_fee'], 2, ',', '.') ?></dd>
                </div>
                <?php endif; ?>
            </dl>
            <?php if ((float) $enriched['minimum_order'] > 0): ?>
                <p class="mt-4 text-xs text-zinc-500">Pedido mínimo: R$ <?= number_format((float) $enriched['minimum_order'], 2, ',', '.') ?></p>
                <?php if ((float) $enriched['subtotal'] < (float) $enriched['minimum_order']): ?>
                    <p class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-900">
                        Faltam R$ <?= number_format((float) $enriched['minimum_order'] - (float) $enriched['subtotal'], 2, ',', '.') ?> no subtotal.
                    </p>
                <?php endif; ?>
            <?php endif; ?>
            <?php
            $canCheckout = (float) $enriched['minimum_order'] <= 0 || (float) $enriched['subtotal'] >= (float) $enriched['minimum_order'];
            ?>
            <?php if (is_array($unit) && $canCheckout): ?>
                <a class="df-btn-primary mt-6 w-full" href="/cliente/checkout">Continuar</a>
            <?php elseif (is_array($unit)): ?>
                <p class="mt-4 text-xs text-zinc-500">Adicione mais itens para continuar.</p>
            <?php endif; ?>
            <?php if (is_array($unit)): ?>
                <a href="/u/<?= htmlspecialchars((string) ($unit['slug'] ?? '')) ?>" class="mt-3 block text-center text-xs font-medium text-zinc-500 hover:text-zinc-800">Voltar ao cardápio</a>
            <?php endif; ?>
        </aside>
    </div>
<?php endif; ?>
