<?php
declare(strict_types=1);
use App\Helpers\Str;
/** @var array<string,mixed> $unit */
/** @var list<array<string,mixed>> $categories */
/** @var list<array<string,mixed>> $products */
/** @var array<int, list<array<string,mixed>>> $addonsByProduct */
/** @var bool $unitOpen */
/** @var string $hoursLabel */
$unitOpen = $unitOpen ?? true;
$unitImage = !empty($unit['image_path']) ? '/' . ltrim((string) $unit['image_path'], '/') : null;
?>
<div class="df-hero df-hero-pattern">
    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
        <div class="flex gap-4">
            <?php if ($unitImage): ?>
                <img src="<?= htmlspecialchars($unitImage) ?>" alt="" width="72" height="72" class="h-[4.5rem] w-[4.5rem] shrink-0 rounded-2xl border border-zinc-200 object-cover">
            <?php endif; ?>
            <div>
                <p class="df-eyebrow"><?= htmlspecialchars((string) $unit['city']) ?></p>
                <h1 class="font-display mt-1 text-3xl font-semibold text-zinc-900 md:text-4xl"><?= htmlspecialchars((string) $unit['name']) ?></h1>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <?php if ($unitOpen): ?>
                        <span class="df-badge-open"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aberto agora</span>
                    <?php else: ?>
                        <span class="df-badge-closed">Fechado</span>
                    <?php endif; ?>
                    <span class="text-sm text-zinc-500"><?= htmlspecialchars($hoursLabel ?? '') ?></span>
                </div>
                <p class="mt-3 text-sm text-zinc-600">
                    Entrega <span class="df-price">R$ <?= number_format((float) $unit['delivery_fee'], 2, ',', '.') ?></span>
                    <?php if ((float) ($unit['minimum_order'] ?? 0) > 0): ?>
                        <span class="text-zinc-400">·</span> Mínimo <span class="df-price">R$ <?= number_format((float) $unit['minimum_order'], 2, ',', '.') ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <a href="/cliente/carrinho" class="df-btn-primary shrink-0 self-start">Ver carrinho</a>
    </div>
</div>

<?php if (!$unitOpen): ?>
    <div class="mt-4 rounded-2xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-sm text-amber-950">
        Esta unidade não está recebendo pedidos no momento. Você pode montar o carrinho e voltar quando abrir.
    </div>
<?php endif; ?>

<div class="mt-8" id="menu-search-wrap">
    <label class="sr-only" for="menu-search">Buscar no cardápio</label>
    <input id="menu-search" type="search" placeholder="Buscar no cardápio…" class="df-input max-w-md" autocomplete="off">

    <div class="mt-10 space-y-12">
        <?php
        $byCat = [];
        foreach ($products as $p) {
            $byCat[(int) $p['category_id']][] = $p;
        }
        foreach ($categories as $cat):
            $cid = (int) $cat['id'];
            $list = $byCat[$cid] ?? [];
            if ($list === []) {
                continue;
            }
            ?>
            <section>
                <h2 class="font-display text-xl font-semibold text-zinc-900"><?= htmlspecialchars((string) $cat['name']) ?></h2>
                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <?php foreach ($list as $p):
                        $pid = (int) $p['id'];
                        $addons = $addonsByProduct[$pid] ?? [];
                        ?>
                        <article class="menu-product-card df-card p-4 md:p-5" data-menu-name="<?= htmlspecialchars(Str::lower((string) $p['name']), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="flex gap-4">
                                <?php if (!empty($p['image_path'])): ?>
                                    <img src="/<?= htmlspecialchars(ltrim((string) $p['image_path'], '/')) ?>" alt="<?= htmlspecialchars((string) ($p['name'] ?? 'Produto')) ?>" width="88" height="88" loading="lazy" decoding="async" class="menu-product-img shrink-0 rounded-xl">
                                <?php endif; ?>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="font-semibold text-zinc-900"><?= htmlspecialchars((string) $p['name']) ?></h3>
                                            <?php if (!empty($p['description'])): ?>
                                                <p class="mt-1.5 text-sm leading-relaxed text-zinc-600"><?= nl2br(htmlspecialchars((string) $p['description'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <p class="tabular shrink-0 text-lg font-semibold text-zinc-900">R$ <?= number_format((float) $p['price'], 2, ',', '.') ?></p>
                                    </div>
                                </div>
                            </div>
                            <form method="post" action="/cliente/carrinho/adicionar" class="mt-4 space-y-3 border-t border-zinc-100 pt-4">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Helpers\Csrf::token()) ?>">
                                <input type="hidden" name="unit_id" value="<?= (int) $unit['id'] ?>">
                                <input type="hidden" name="product_id" value="<?= $pid ?>">
                                <?php if ($addons !== []): ?>
                                    <div class="space-y-2">
                                        <p class="text-xs font-medium text-zinc-500">Adicionais</p>
                                        <?php foreach ($addons as $a): ?>
                                            <label class="flex items-center justify-between gap-2 text-sm text-zinc-700">
                                                <span class="flex items-center gap-2">
                                                    <input type="checkbox" name="addons[]" value="<?= (int) $a['id'] ?>" <?= !empty($a['is_required']) ? 'checked required' : '' ?> class="rounded border-zinc-300 text-zinc-900">
                                                    <?= htmlspecialchars((string) $a['name']) ?>
                                                </span>
                                                <span class="tabular text-zinc-500">+ R$ <?= number_format((float) $a['price'], 2, ',', '.') ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex items-center gap-3">
                                    <input type="number" name="qty" value="1" min="1" class="df-input w-20 text-center" aria-label="Quantidade">
                                    <button class="df-btn-primary flex-1" <?= !$unitOpen ? 'disabled' : '' ?>>Adicionar ao pedido</button>
                                </div>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</div>
<script src="/assets/js/menu-search.js"></script>
