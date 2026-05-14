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
            <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-white px-3 py-2">
                <span class="flex items-center gap-3">
                    <?php if (!empty($p['image_path'])): ?>
                        <img src="/<?= htmlspecialchars(ltrim((string) $p['image_path'], '/')) ?>" alt="" class="h-10 w-10 rounded-lg object-cover">
                    <?php endif; ?>
                    <span><?= htmlspecialchars((string) $p['name']) ?> <span class="text-xs text-slate-500">(<?= htmlspecialchars((string) $p['category_name']) ?>)</span></span>
                </span>
                <span class="font-semibold">R$ <?= number_format((float) $p['price'], 2, ',', '.') ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
