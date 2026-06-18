<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $categories */
/** @var list<array<string,mixed>> $products */
/** @var string $csrf */
/** @var string|null $flash_error */
?>
<?php if (!empty($flash_error)): ?>
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"><?= htmlspecialchars((string) $flash_error) ?></div>
<?php endif; ?>
<div class="grid gap-6 md:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <h2 class="font-semibold">Nova categoria</h2>
        <form method="post" action="/operador/cardapio/categoria" class="mt-3 space-y-2">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <input name="name" required class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Nome">
            <button class="rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white">Adicionar</button>
        </form>
        <?php if ($categories !== []): ?>
            <ul class="mt-4 space-y-2 border-t border-slate-100 pt-3 text-sm">
                <?php foreach ($categories as $c): ?>
                    <li>
                        <form method="post" action="/operador/cardapio/categoria/<?= (int) $c['id'] ?>/editar" class="flex gap-2">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <input name="name" value="<?= htmlspecialchars((string) $c['name']) ?>" class="min-w-0 flex-1 rounded-lg border px-2 py-1 text-sm" required>
                            <button class="shrink-0 rounded-full bg-slate-700 px-3 py-1 text-xs font-semibold text-white">Salvar</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <h2 class="font-semibold">Novo produto</h2>
        <form method="post" action="/operador/cardapio/produto" enctype="multipart/form-data" class="mt-3 space-y-2">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <select name="category_id" class="w-full rounded-lg border px-2 py-2 text-sm">
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars((string) $c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input name="name" required class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Nome do produto">
            <input name="price" type="number" step="0.01" required class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Preço">
            <label class="block text-xs text-slate-600">Imagem (JPEG/PNG/WebP, máx. 2MB)</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="w-full text-xs">
            <button class="rounded-full bg-orange-500 px-4 py-2 text-xs font-semibold text-white">Salvar</button>
        </form>
    </div>
</div>
<div class="mt-8">
    <h2 class="font-semibold">Produtos cadastrados</h2>
    <ul class="mt-3 space-y-2 text-sm">
        <?php foreach ($products as $p): ?>
            <li class="rounded-xl border border-slate-100 bg-white px-3 py-3 <?= ($p['status'] ?? '') === 'inactive' ? 'opacity-60' : '' ?>">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="flex items-center gap-3">
                        <?php if (!empty($p['image_path'])): ?>
                            <img src="/<?= htmlspecialchars(ltrim((string) $p['image_path'], '/')) ?>" alt="<?= htmlspecialchars((string) ($p['name'] ?? 'Produto')) ?>" class="h-10 w-10 rounded-lg object-cover">
                        <?php endif; ?>
                        <span>
                            <?= htmlspecialchars((string) $p['name']) ?>
                            <span class="text-xs text-slate-500">(<?= htmlspecialchars((string) $p['category_name']) ?>)</span>
                            <?php if (($p['status'] ?? '') === 'inactive'): ?>
                                <span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-slate-600">Indisponível</span>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="flex items-center gap-3">
                        <span class="font-semibold">R$ <?= number_format((float) $p['price'], 2, ',', '.') ?></span>
                        <form method="post" action="/operador/cardapio/produto/<?= (int) $p['id'] ?>/toggle" class="inline">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <button type="submit" class="text-xs font-semibold text-brand-700 hover:underline">
                                <?= ($p['status'] ?? '') === 'active' ? 'Indisponível' : 'Ativar' ?>
                            </button>
                        </form>
                    </span>
                </div>
                <details class="mt-2 text-xs">
                    <summary class="cursor-pointer font-semibold text-slate-600">Editar / adicional</summary>
                    <form method="post" action="/operador/cardapio/produto/<?= (int) $p['id'] ?>/editar" class="mt-2 grid gap-2 sm:grid-cols-3">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <input name="name" value="<?= htmlspecialchars((string) $p['name']) ?>" class="rounded border px-2 py-1">
                        <input name="price" type="number" step="0.01" value="<?= (float) $p['price'] ?>" class="rounded border px-2 py-1">
                        <input name="description" value="<?= htmlspecialchars((string) ($p['description'] ?? '')) ?>" placeholder="Descrição" class="rounded border px-2 py-1 sm:col-span-2">
                        <button class="rounded bg-slate-800 px-2 py-1 text-white">Salvar</button>
                    </form>
                    <form method="post" action="/operador/cardapio/adicional" class="mt-2 flex flex-wrap gap-2">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                        <input name="name" placeholder="Nome adicional" required class="rounded border px-2 py-1">
                        <input name="price" type="number" step="0.01" value="0" class="w-24 rounded border px-2 py-1">
                        <button class="rounded bg-orange-500 px-2 py-1 text-white">+ Adicional</button>
                    </form>
                </details>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
