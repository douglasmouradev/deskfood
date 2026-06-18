<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $units */
/** @var string $orderHref */
/** @var array<string,int> $stats */
$orderHref = $orderHref ?? '/#onde-pedir';
?>
<section class="relative overflow-hidden rounded-3xl border border-zinc-200/80 bg-white px-6 py-10 md:px-10 md:py-14">
    <div class="df-hero-pattern pointer-events-none absolute inset-0 opacity-60" aria-hidden="true"></div>
    <div class="relative grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
        <div>
            <p class="df-eyebrow">Peça online</p>
            <h1 class="font-display mt-3 text-4xl font-semibold leading-[1.1] text-zinc-900 md:text-5xl">
                Escolha a loja.<br class="hidden sm:block"> O resto é com a gente.
            </h1>
            <p class="mt-4 max-w-lg text-base leading-relaxed text-zinc-600">
                Cardápio atualizado, pagamento na hora e acompanhamento do pedido — direto com o restaurante, sem intermediário.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars($orderHref) ?>" class="df-btn-primary px-6 py-3">Ver lojas abertas</a>
                <a href="/cliente/login" class="df-btn-ghost px-6 py-3">Já tenho conta</a>
            </div>
        </div>
        <dl class="grid grid-cols-3 gap-4 rounded-2xl border border-zinc-100 bg-stone-50/80 p-5">
            <div>
                <dt class="text-[11px] font-medium uppercase tracking-wide text-zinc-500">Unidades</dt>
                <dd class="tabular mt-1 text-2xl font-semibold text-zinc-900"><?= (int) ($stats['units_total'] ?? 0) ?></dd>
            </div>
            <div>
                <dt class="text-[11px] font-medium uppercase tracking-wide text-zinc-500">Abertas agora</dt>
                <dd class="tabular mt-1 text-2xl font-semibold text-emerald-700"><?= (int) ($stats['units_active'] ?? 0) ?></dd>
            </div>
            <div>
                <dt class="text-[11px] font-medium uppercase tracking-wide text-zinc-500">Pedidos hoje</dt>
                <dd class="tabular mt-1 text-2xl font-semibold text-zinc-900"><?= (int) ($stats['orders_today'] ?? 0) ?></dd>
            </div>
        </dl>
    </div>
</section>

<section id="onde-pedir" class="mt-12 scroll-mt-24">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-zinc-900">Lojas disponíveis</h2>
            <p class="mt-1 text-sm text-zinc-600">Entrega ou retirada conforme cada unidade.</p>
        </div>
        <a href="/landing" class="text-sm font-medium text-zinc-600 underline-offset-4 hover:text-zinc-900 hover:underline">Sou dono de restaurante</a>
    </div>
    <div class="mt-6 grid gap-3 sm:grid-cols-2">
        <?php foreach ($units as $u): ?>
            <a href="/u/<?= htmlspecialchars((string) $u['slug']) ?>" class="unit-tile df-card flex items-center justify-between gap-4 px-5 py-4">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-zinc-900"><?= htmlspecialchars((string) $u['name']) ?></p>
                    <p class="mt-0.5 truncate text-sm text-zinc-500"><?= htmlspecialchars((string) $u['city']) ?></p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-[11px] font-medium uppercase tracking-wide text-zinc-400">Entrega</p>
                    <p class="tabular text-sm font-semibold text-zinc-900">R$ <?= number_format((float) $u['delivery_fee'], 2, ',', '.') ?></p>
                </div>
            </a>
        <?php endforeach; ?>
        <?php if ($units === []): ?>
            <div class="df-card col-span-full px-6 py-12 text-center">
                <p class="font-medium text-zinc-800">Nenhuma loja aberta no momento</p>
                <p class="mt-2 text-sm text-zinc-500">Volte mais tarde ou fale com o restaurante.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="mt-16 grid gap-6 md:grid-cols-3">
    <article class="df-card p-6">
        <p class="tabular text-sm font-semibold text-zinc-400">01</p>
        <h3 class="mt-3 font-semibold text-zinc-900">Escolha os itens</h3>
        <p class="mt-2 text-sm leading-relaxed text-zinc-600">Cardápio com adicionais, busca e carrinho que salva enquanto você navega.</p>
    </article>
    <article class="df-card p-6">
        <p class="tabular text-sm font-semibold text-zinc-400">02</p>
        <h3 class="mt-3 font-semibold text-zinc-900">Pague como preferir</h3>
        <p class="mt-2 text-sm leading-relaxed text-zinc-600">PIX com confirmação automática, cartão online ou pagamento na entrega.</p>
    </article>
    <article class="df-card p-6">
        <p class="tabular text-sm font-semibold text-zinc-400">03</p>
        <h3 class="mt-3 font-semibold text-zinc-900">Acompanhe o pedido</h3>
        <p class="mt-2 text-sm leading-relaxed text-zinc-600">Link de rastreio com status em tempo real — sem precisar ligar para a loja.</p>
    </article>
</section>
