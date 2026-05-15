<?php
declare(strict_types=1);
/** @var array<string,mixed> $unit */
/** @var list<array<string,mixed>> $categories */
/** @var list<array<string,mixed>> $products */
/** @var array<int, list<array<string,mixed>>> $addonsByProduct */
/** @var bool $unitOpen */
/** @var string $hoursLabel */
$unitOpen = $unitOpen ?? true;
?>
<?php if (!$unitOpen): ?>
    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
        <strong>Fechado no momento.</strong> <?= htmlspecialchars($hoursLabel ?? '') ?>
    </div>
<?php endif; ?>
<div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
    <div>
        <p class="text-sm font-semibold text-orange-600"><?= htmlspecialchars((string) $unit['city']) ?></p>
        <h1 class="text-3xl font-bold text-slate-900"><?= htmlspecialchars((string) $unit['name']) ?></h1>
        <p class="mt-2 text-sm text-slate-600">Taxa de entrega: <span class="font-semibold text-slate-900">R$ <?= number_format((float) $unit['delivery_fee'], 2, ',', '.') ?></span></p>
        <?php if ((float) ($unit['minimum_order'] ?? 0) > 0): ?>
            <p class="mt-1 text-sm text-slate-600">Pedido mínimo: <span class="font-semibold text-slate-900">R$ <?= number_format((float) $unit['minimum_order'], 2, ',', '.') ?></span></p>
        <?php endif; ?>
    </div>
    <a href="/cliente/carrinho" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">Ver carrinho</a>
</div>

<div class="mt-6" x-data="{ q: '' }">
    <label class="sr-only" for="menu-search">Buscar no cardápio</label>
    <input id="menu-search" type="search" x-model="q" placeholder="Buscar produto…" class="w-full max-w-md rounded-full border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-100">

<div class="mt-8 space-y-10">
    <?php
    $byCat = [];
    foreach ($products as $p) {
        $cid = (int) $p['category_id'];
        $byCat[$cid][] = $p;
    }
    foreach ($categories as $cat):
        $cid = (int) $cat['id'];
        $list = $byCat[$cid] ?? [];
        if ($list === []) {
            continue;
        }
        ?>
        <section>
            <h2 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars((string) $cat['name']) ?></h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <?php foreach ($list as $p):
                    $pid = (int) $p['id'];
                    $addons = $addonsByProduct[$pid] ?? [];
                    ?>
                    <div class="rounded-2xl border border-orange-100 bg-white p-4 shadow-sm" x-show="!q || <?= json_encode(mb_strtolower((string) $p['name']), JSON_THROW_ON_ERROR) ?>.includes(q.toLowerCase())">
                        <div class="flex gap-3">
                            <?php if (!empty($p['image_path'])): ?>
                                <img src="/<?= htmlspecialchars(ltrim((string) $p['image_path'], '/')) ?>" alt="" class="h-20 w-20 shrink-0 rounded-xl object-cover">
                            <?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-semibold text-slate-900"><?= htmlspecialchars((string) $p['name']) ?></h3>
                                        <?php if (!empty($p['description'])): ?>
                                            <p class="mt-1 text-sm text-slate-600"><?= nl2br(htmlspecialchars((string) $p['description'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <p class="tabular shrink-0 text-lg font-semibold text-orange-600">R$ <?= number_format((float) $p['price'], 2, ',', '.') ?></p>
                                </div>
                            </div>
                        </div>
                        <form method="post" action="/cliente/carrinho/adicionar" class="mt-4 space-y-3">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Helpers\Csrf::token()) ?>">
                            <input type="hidden" name="unit_id" value="<?= (int) $unit['id'] ?>">
                            <input type="hidden" name="product_id" value="<?= $pid ?>">
                            <?php if ($addons !== []): ?>
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Personalize</p>
                                    <?php foreach ($addons as $a): ?>
                                        <label class="flex items-center justify-between gap-2 text-sm">
                                            <span class="flex items-center gap-2">
                                                <input type="checkbox" name="addons[]" value="<?= (int) $a['id'] ?>" <?= !empty($a['is_required']) ? 'checked required' : '' ?>>
                                                <?= htmlspecialchars((string) $a['name']) ?>
                                            </span>
                                            <span class="tabular text-slate-600">+ R$ <?= number_format((float) $a['price'], 2, ',', '.') ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-center gap-3">
                                <input type="number" name="qty" value="1" min="1" class="w-20 rounded-lg border border-slate-200 px-2 py-1 text-sm">
                                <button class="flex-1 rounded-full bg-orange-500 py-2 text-sm font-semibold text-white hover:bg-orange-600">Adicionar</button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
</div>
