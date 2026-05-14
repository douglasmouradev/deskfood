<?php
declare(strict_types=1);
if (!isset($config) || !is_array($config)) {
    $config = require BASE_PATH . '/config/app.php';
}
?>
<section class="relative isolate overflow-hidden bg-gradient-to-b from-brand-50 via-white to-white">
    <div class="pointer-events-none absolute -right-32 top-0 h-96 w-96 rounded-full bg-orange-200/40 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-amber-100/50 blur-3xl" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-6xl px-4 pb-20 pt-14 md:pb-28 md:pt-20">
        <div class="max-w-3xl">
            <p class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-brand-700 shadow-sm">
                <span class="tabular h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                PHP 8.3 · MySQL · pronto para VPS
            </p>
            <h1 class="mt-6 text-4xl font-extrabold leading-[1.1] tracking-tight text-ink-900 md:text-5xl lg:text-6xl">
                A stack de delivery que a sua marca merece.
            </h1>
            <p class="mt-6 text-lg text-ink-700 md:text-xl md:leading-relaxed">
                Uma aplicação completa para <strong class="font-semibold text-ink-900">várias unidades</strong>, com painel do dono, operação da loja, cliente com OTP e rastreio — sem depender de marketplaces genéricos.
            </p>
            <div class="mt-10 flex flex-wrap items-center gap-3">
                <a href="/admin/login" class="inline-flex items-center justify-center rounded-full bg-brand-600 px-7 py-3.5 text-sm font-bold text-white shadow-lg shadow-orange-200/50 transition hover:bg-brand-700">
                    Começar como dono
                </a>
                <a href="/operador/login" class="inline-flex items-center justify-center rounded-full border-2 border-slate-200 bg-white px-6 py-3.5 text-sm font-bold text-ink-800 hover:border-brand-300 hover:bg-brand-50/50">
                    Área da loja
                </a>
                <a href="/" class="inline-flex items-center justify-center text-sm font-semibold text-brand-700 underline-offset-4 hover:underline">
                    Fazer um pedido
                </a>
            </div>
            <dl class="mt-14 grid grid-cols-2 gap-6 border-t border-orange-100/80 pt-10 sm:grid-cols-4">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-ink-500">Multi-unidade</dt>
                    <dd class="tabular mt-1 text-2xl font-bold text-ink-900">Sim</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-ink-500">PIX + webhook</dt>
                    <dd class="tabular mt-1 text-2xl font-bold text-emerald-600">Sim</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-ink-500">LGPD</dt>
                    <dd class="tabular mt-1 text-2xl font-bold text-ink-900">Painel</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-ink-500">Open source você</dt>
                    <dd class="tabular mt-1 text-2xl font-bold text-ink-900">Seu código</dd>
                </div>
            </dl>
        </div>
    </div>
</section>

<section id="recursos" class="mx-auto max-w-6xl scroll-mt-24 px-4 py-16 md:py-24">
    <div class="mx-auto max-w-2xl text-center">
        <h2 class="text-3xl font-bold tracking-tight text-ink-900 md:text-4xl">Tudo o que a operação pede</h2>
        <p class="mt-4 text-ink-600">Do cardápio ao caixa, com trilha clara para o cliente e segurança no servidor.</p>
    </div>
    <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        <article class="rounded-3xl border border-slate-200 bg-slate-50/50 p-6 shadow-sm transition hover:border-brand-200 hover:shadow-md">
            <p class="tabular text-2xl font-bold text-brand-600">01</p>
            <h3 class="mt-3 text-lg font-bold text-ink-900">Cardápio por unidade</h3>
            <p class="mt-2 text-sm leading-relaxed text-ink-600">Categorias, produtos, taxa de entrega e vitrine pública por slug.</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-brand-200 hover:shadow-md">
            <p class="tabular text-2xl font-bold text-brand-600">02</p>
            <h3 class="mt-3 text-lg font-bold text-ink-900">Pedidos em tempo real</h3>
            <p class="mt-2 text-sm leading-relaxed text-ink-600">Fluxo de status, atribuição de motoboy e link de acompanhamento para o cliente.</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-slate-50/50 p-6 shadow-sm transition hover:border-brand-200 hover:shadow-md md:col-span-2 lg:col-span-1">
            <p class="tabular text-2xl font-bold text-brand-600">03</p>
            <h3 class="mt-3 text-lg font-bold text-ink-900">PIX e caixa</h3>
            <p class="mt-2 text-sm leading-relaxed text-ink-600">Transações rastreadas, webhook com segredo opcional, abertura e fechamento de caixa com sangrias.</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-brand-200 hover:shadow-md lg:col-span-2">
            <p class="tabular text-2xl font-bold text-brand-600">04</p>
            <h3 class="mt-3 text-lg font-bold text-ink-900">Cliente com OTP e LGPD</h3>
            <p class="mt-2 text-sm leading-relaxed text-ink-600">Login por SMS (modo log em desenvolvimento), checkout, exportação de dados e exclusão conforme boas práticas.</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-gradient-to-br from-ink-900 to-slate-800 p-6 text-white shadow-lg">
            <p class="tabular text-2xl font-bold text-brand-400">05</p>
            <h3 class="mt-3 text-lg font-bold">Operação segura</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-300">Rate limit de login, healthcheck, auditoria de webhook, headers de segurança e exportação CSV para conferência.</p>
        </article>
    </div>
</section>

<section id="fluxo" class="scroll-mt-24 bg-slate-900 py-16 text-white md:py-24">
    <div class="mx-auto max-w-6xl px-4">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="text-3xl font-bold tracking-tight md:text-4xl">Um fluxo que a equipe entende na primeira semana</h2>
                <p class="mt-4 text-slate-400">Dono cadastra unidades e libera operadores. A loja abre caixa, confirma pedidos e despacha. O cliente acompanha pelo link.</p>
                <ol class="mt-10 space-y-6">
                    <li class="flex gap-4">
                        <span class="tabular flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brand-500 text-sm font-bold">1</span>
                        <div>
                            <p class="font-semibold">Dono</p>
                            <p class="mt-1 text-sm text-slate-400">Unidades ativas, visão consolidada e acesso administrativo.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="tabular flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-sm font-bold">2</span>
                        <div>
                            <p class="font-semibold">Operador</p>
                            <p class="mt-1 text-sm text-slate-400">Cardápio, pedidos, motoboys e caixa no mesmo painel.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="tabular flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-sm font-bold">3</span>
                        <div>
                            <p class="font-semibold">Cliente</p>
                            <p class="mt-1 text-sm text-slate-400">OTP, carrinho, PIX ou na entrega, com rastreio transparente.</p>
                        </div>
                    </li>
                </ol>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-8 backdrop-blur">
                <p class="text-sm font-semibold uppercase tracking-wider text-brand-400">Por dentro</p>
                <p class="mt-4 text-lg font-semibold text-white">MVC em PHP puro</p>
                <p class="mt-2 text-sm text-slate-400">Sem framework pesado: rotas declarativas, PDO, views em PHP e front com Tailwind CDN — fácil de hospedar e evoluir.</p>
                <ul class="mt-8 space-y-3 text-sm text-slate-300">
                    <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> MySQL 8 + migrations</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Instalador com seed opcional</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Documentação em README</li>
                </ul>
                <a href="/ajuda" class="mt-8 inline-flex rounded-full bg-white px-5 py-2.5 text-sm font-bold text-ink-900 hover:bg-slate-100">Abrir central de ajuda</a>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-6xl scroll-mt-24 px-4 py-16 md:py-24" x-data="{ open: null }" id="faq">
    <div class="mx-auto max-w-2xl text-center">
        <h2 class="text-3xl font-bold tracking-tight text-ink-900 md:text-4xl">Perguntas frequentes</h2>
        <p class="mt-4 text-ink-600">Respostas rápidas para quem está avaliando a plataforma.</p>
    </div>
    <div class="mx-auto mt-12 max-w-3xl divide-y divide-slate-200 rounded-3xl border border-slate-200 bg-white px-2 shadow-sm">
        <?php
        $faqs = [
            ['q' => 'Esta landing substitui a home de pedidos?', 'a' => 'Não. A home em / continua sendo a vitrine das unidades ativas para o cliente. Esta página é só marketing e visão geral do produto.'],
            ['q' => 'Posso usar em produção?', 'a' => 'O projeto foi pensado para VPS ou hosting com PHP 8.3 e MySQL. Configure .env, HTTPS, PIX_WEBHOOK_SECRET e provedor de SMS real quando for ao ar.'],
            ['q' => 'Onde está a documentação técnica?', 'a' => 'No README do repositório e na central de ajuda em /ajuda, com healthcheck, webhook PIX e fluxos de papéis.'],
        ];
        foreach ($faqs as $i => $item):
        ?>
        <div class="px-4 py-1">
            <button type="button" class="flex w-full items-center justify-between gap-4 py-4 text-left text-sm font-bold text-ink-900 md:text-base" @click="open === <?= (int) $i ?> ? open = null : open = <?= (int) $i ?>">
                <span><?= htmlspecialchars($item['q']) ?></span>
                <span class="tabular text-brand-600" x-text="open === <?= (int) $i ?> ? '−' : '+'"></span>
            </button>
            <div x-show="open === <?= (int) $i ?>" x-cloak x-transition class="pb-4 text-sm leading-relaxed text-ink-600">
                <?= htmlspecialchars($item['a']) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="contato" class="scroll-mt-24 border-y border-slate-200 bg-slate-50 py-16 md:py-20">
    <div class="mx-auto max-w-6xl px-4 text-center">
        <h2 class="text-3xl font-bold tracking-tight text-ink-900 md:text-4xl">Fale com o comercial</h2>
        <p class="mx-auto mt-3 max-w-xl text-ink-600">
            <?= htmlspecialchars((string) ($config['commercial_company'] ?? 'TDesk Solutions')) ?> — implantação, contratos e suporte à operação Desk Food.
        </p>
        <div class="mx-auto mt-10 flex max-w-lg flex-col gap-4 rounded-3xl border border-slate-200 bg-white px-8 py-8 text-left shadow-sm sm:flex-row sm:items-center sm:justify-center sm:text-center">
            <?php $em = trim((string) ($config['commercial_email'] ?? '')); ?>
            <?php if ($em !== ''): ?>
                <a href="mailto:<?= htmlspecialchars($em) ?>" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-ink-900 hover:border-brand-300 hover:bg-brand-50">
                    E-mail<span class="sr-only">:</span> <?= htmlspecialchars($em) ?>
                </a>
            <?php endif; ?>
            <?php
            $tel = trim((string) ($config['commercial_phone_tel'] ?? ''));
            $lab = trim((string) ($config['commercial_phone_label'] ?? ''));
            ?>
            <?php if ($tel !== '' && $lab !== ''): ?>
                <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $tel)) ?>" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-ink-900 px-4 py-3 text-sm font-semibold text-white hover:bg-ink-800">
                    Telefone<span class="sr-only">:</span> <?= htmlspecialchars($lab) ?>
                </a>
            <?php endif; ?>
        </div>
        <p class="mt-6 text-sm text-ink-500">Resposta em horário comercial. Para dúvidas técnicas do produto, veja também a <a class="font-semibold text-brand-600 underline" href="/ajuda">central de ajuda</a>.</p>
    </div>
</section>

<section class="border-y border-orange-100 bg-gradient-to-r from-brand-50 to-amber-50 py-16 md:py-20">
    <div class="mx-auto max-w-6xl px-4 text-center">
        <h2 class="text-2xl font-bold text-ink-900 md:text-3xl">Pronto para colocar a marca no delivery?</h2>
        <p class="mx-auto mt-3 max-w-xl text-ink-600">Acesse o painel do dono, cadastre a primeira unidade e convide o time da loja.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="/admin/login" class="inline-flex rounded-full bg-ink-900 px-8 py-3 text-sm font-bold text-white hover:bg-ink-800">Entrar como dono</a>
            <a href="/" class="inline-flex rounded-full border-2 border-white bg-white px-8 py-3 text-sm font-bold text-ink-900 shadow-sm hover:bg-slate-50">Ver unidades para pedir</a>
        </div>
    </div>
</section>
