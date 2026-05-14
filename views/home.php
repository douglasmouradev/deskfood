<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $units */
/** @var array<string,int> $stats */
?>
<section class="grid gap-12 lg:grid-cols-2 lg:items-center">
    <div>
        <p class="inline-flex rounded-full bg-brand-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-brand-800">Plataforma B2B</p>
        <h1 class="mt-4 text-4xl font-bold leading-tight text-ink-900 md:text-5xl">Delivery que escala com a sua operação.</h1>
        <p class="mt-4 max-w-xl text-lg text-ink-700">Cardápio por unidade, pedidos em tempo real, PIX e caixa integrados — com rastreio para o cliente e conformidade LGPD.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="/cliente/login" class="inline-flex items-center justify-center rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-200 transition hover:bg-brand-700">Pedir agora</a>
            <a href="/operador/login" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-ink-800 hover:border-brand-200">Área da loja</a>
            <a href="/landing" class="inline-flex items-center justify-center rounded-full border border-transparent px-4 py-3 text-sm font-semibold text-brand-700 hover:underline">Plataforma</a>
            <a href="/admin/login" class="inline-flex items-center justify-center rounded-full border border-transparent px-4 py-3 text-sm font-semibold text-brand-700 hover:underline">Dono</a>
        </div>
        <dl class="mt-10 grid grid-cols-3 gap-4 border-t border-slate-200 pt-8">
            <div>
                <dt class="text-xs font-medium uppercase text-ink-500">Unidades</dt>
                <dd class="tabular mt-1 text-2xl font-semibold text-ink-900"><?= (int) ($stats['units_total'] ?? 0) ?></dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-ink-500">Pedidos hoje</dt>
                <dd class="tabular mt-1 text-2xl font-semibold text-brand-600"><?= (int) ($stats['orders_today'] ?? 0) ?></dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-ink-500">Ativas</dt>
                <dd class="tabular mt-1 text-2xl font-semibold text-emerald-600"><?= (int) ($stats['units_active'] ?? 0) ?></dd>
            </div>
        </dl>
    </div>
    <div class="rounded-3xl border border-orange-100 bg-gradient-to-br from-white to-orange-50 p-6 shadow-xl shadow-orange-100/60">
        <h2 class="text-lg font-semibold text-ink-900">Onde pedir</h2>
        <p class="mt-1 text-sm text-ink-600">Unidades com delivery no momento.</p>
        <div class="mt-4 space-y-3">
            <?php foreach ($units as $u): ?>
                <a href="/u/<?= htmlspecialchars((string) $u['slug']) ?>" class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white px-4 py-3 transition hover:border-brand-200 hover:shadow-md">
                    <div>
                        <p class="font-semibold text-ink-900"><?= htmlspecialchars((string) $u['name']) ?></p>
                        <p class="text-sm text-ink-500"><?= htmlspecialchars((string) $u['city']) ?></p>
                    </div>
                    <span class="tabular text-sm font-semibold text-brand-700">R$ <?= number_format((float) $u['delivery_fee'], 2, ',', '.') ?></span>
                </a>
            <?php endforeach; ?>
            <?php if ($units === []): ?>
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white/60 px-4 py-8 text-center">
                    <p class="text-sm font-medium text-ink-800">Nenhuma unidade ativa no momento</p>
                    <p class="mt-2 text-xs text-ink-500">O dono pode cadastrar unidades no painel ou rode o seed de demonstração.</p>
                    <a href="/admin/login" class="mt-4 inline-flex text-sm font-semibold text-brand-600 hover:underline">Acessar painel do dono</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="mt-16 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
    <h2 class="text-center text-2xl font-bold text-ink-900">Como funciona</h2>
    <div class="mt-10 grid gap-8 md:grid-cols-3">
        <div class="text-center">
            <p class="tabular mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-lg font-bold text-brand-700">1</p>
            <h3 class="mt-4 font-semibold text-ink-900">Cliente pede</h3>
            <p class="mt-2 text-sm text-ink-600">SMS para login, carrinho, endereço e PIX ou pagamento na entrega.</p>
        </div>
        <div class="text-center">
            <p class="tabular mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-lg font-bold text-brand-700">2</p>
            <h3 class="mt-4 font-semibold text-ink-900">Loja opera</h3>
            <p class="mt-2 text-sm text-ink-600">Confirmação, preparo, motoboy e caixa com sangrias e fechamento.</p>
        </div>
        <div class="text-center">
            <p class="tabular mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-lg font-bold text-brand-700">3</p>
            <h3 class="mt-4 font-semibold text-ink-900">Todos acompanham</h3>
            <p class="mt-2 text-sm text-ink-600">Link de rastreio com status e entregador quando houver.</p>
        </div>
    </div>
</section>

<section class="mt-16 rounded-3xl bg-slate-900 px-8 py-10 text-center text-slate-100">
    <h2 class="text-xl font-semibold">Pronto para uso em produção</h2>
    <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-400">HTTPS, webhook PIX com segredo opcional, rate limit de login, healthcheck <code class="rounded bg-slate-800 px-1">/health</code>, exportação CSV e logs estruturados.</p>
    <a href="/ajuda" class="mt-6 inline-flex rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 hover:bg-slate-100">Ver central de ajuda</a>
</section>
