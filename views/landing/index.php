<?php
declare(strict_types=1);
use App\Helpers\OrderEntry;
$orderHref = OrderEntry::hrefForActiveUnits();
require __DIR__ . '/icons.php';

if (!isset($config) || !is_array($config)) {
    $config = require BASE_PATH . '/config/app.php';
}
$appName = (string) ($config['name'] ?? 'Desk Food');
$commercialCompany = (string) ($config['commercial_company'] ?? 'TDesk Solutions');
$commercialEmail = trim((string) ($config['commercial_email'] ?? ''));
$commercialPhone = trim((string) ($config['commercial_phone_label'] ?? ''));
$commercialTel = trim((string) ($config['commercial_phone_tel'] ?? ''));
$stats = is_array($stats ?? null) ? $stats : ['units_active' => 0, 'orders_today' => 0];
$demoSlug = (string) ($demoSlug ?? 'centro');
$demoMenuUrl = '/u/' . rawurlencode($demoSlug);
$waContact = preg_replace('/\D+/', '', $commercialTel);
if ($waContact !== '' && !str_starts_with($waContact, '55')) {
    $waContact = '55' . $waContact;
}
?>
<div class="lp-scroll-progress" aria-hidden="true"></div>

<div class="lp-scene">
<div class="lp-track">

<!-- Hero -->
<section class="lp-panel lp-panel--depth lp-hero scroll-mt-24" data-panel>
    <div class="lp-hero__grid" aria-hidden="true"></div>
    <div class="lp-hero__glow" aria-hidden="true"></div>
    <div class="lp-panel__inner relative mx-auto flex max-w-6xl flex-col gap-14 px-4 py-16 md:grid md:min-h-[inherit] md:grid-cols-2 md:items-center md:gap-12 md:py-24 lg:py-28">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="lp-hero-badge"><?= htmlspecialchars($appName) ?></span>
                <span class="text-xs font-medium text-zinc-500">Software para restaurantes</span>
            </div>
            <h1 class="lp-title mt-6 text-4xl text-white md:text-5xl lg:text-[3.25rem]">
                Seu delivery.<br>Sua marca. <span class="text-[#fb923c]">Sua margem.</span>
            </h1>
            <p class="lp-lead mt-6">
                Cardápio online, pagamento com confirmação automática e painel da cozinha —
                sem repassar comissão por pedido para marketplace.
            </p>
            <ul class="mt-8 space-y-2.5 text-sm text-zinc-400">
                <li class="flex items-start gap-2.5"><span class="lp-check" aria-hidden="true"></span>Canal próprio com URL da sua loja</li>
                <li class="flex items-start gap-2.5"><span class="lp-check" aria-hidden="true"></span>Implantação acompanhada, sem fidelidade longa</li>
                <li class="flex items-start gap-2.5"><span class="lp-check" aria-hidden="true"></span>PIX, cartão e operação multi-unidade</li>
            </ul>
            <div class="mt-10 flex flex-wrap items-center gap-4">
                <a href="#contato" class="lp-btn-primary">
                    Agendar conversa
                    <?= landing_icon('arrow') ?>
                </a>
                <a href="<?= htmlspecialchars($demoMenuUrl) ?>" class="lp-btn-ghost" target="_blank" rel="noopener">Ver loja demo</a>
            </div>
            <p class="mt-5 text-xs text-zinc-600">
                Já é cliente? <a href="<?= htmlspecialchars($orderHref) ?>" class="text-zinc-400 hover:text-white underline-offset-2 hover:underline">Pedir em uma loja aberta</a>
                · <a href="/admin/login" class="text-zinc-400 hover:text-white underline-offset-2 hover:underline">Área do dono</a>
            </p>
        </div>

        <div class="lp-live-demo" id="demo">
            <p class="mb-3 text-center text-[10px] font-semibold uppercase tracking-widest text-zinc-500 md:text-left">Demo ao vivo — sistema real</p>
            <div class="lp-live-demo__frame">
                <iframe src="<?= htmlspecialchars($demoMenuUrl) ?>" title="Cardápio demo Desk Food Centro" loading="lazy" tabindex="-1"></iframe>
            </div>
            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                <a href="<?= htmlspecialchars($demoMenuUrl) ?>" class="lp-live-demo__link" target="_blank" rel="noopener">
                    <span class="lp-live-demo__link-title">Cardápio do cliente</span>
                    <span class="lp-live-demo__link-sub">/u/<?= htmlspecialchars($demoSlug) ?></span>
                </a>
                <a href="/operador/login" class="lp-live-demo__link">
                    <span class="lp-live-demo__link-title">Painel da cozinha</span>
                    <span class="lp-live-demo__link-sub">Acesso operador</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Confiança -->
<div class="lp-panel lp-panel--flat lp-trust" aria-label="Diferenciais">
    <div class="mx-auto grid max-w-6xl grid-cols-2 gap-6 px-4 py-10 md:grid-cols-4 md:gap-8">
        <div class="lp-trust__item">
            <p class="lp-trust__value">Sem comissão</p>
            <p class="lp-trust__label">Por pedido no canal próprio da loja</p>
        </div>
        <div class="lp-trust__item">
            <p class="lp-trust__value">PIX + cartão</p>
            <p class="lp-trust__label">Confirmação automática na operação</p>
        </div>
        <div class="lp-trust__item">
            <p class="lp-trust__value">3 perfis</p>
            <p class="lp-trust__label">Dono, operador e cliente integrados</p>
        </div>
        <div class="lp-trust__item">
            <p class="lp-trust__value">LGPD</p>
            <p class="lp-trust__label">Termos, privacidade e dados do cliente</p>
        </div>
    </div>
</div>

<!-- Prova + case piloto -->
<section class="lp-panel lp-panel--flat lp-proof scroll-mt-24" aria-label="Operação real">
    <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
        <div class="grid gap-8 lg:grid-cols-[1fr_1.1fr] lg:items-center">
            <div>
                <p class="lp-eyebrow text-[#c2410c]">Em operação</p>
                <h2 class="lp-section-title mt-3">Não é mockup — você pode pedir agora.</h2>
                <p class="lp-section-lead mt-4">A unidade piloto <strong class="font-semibold text-zinc-900">Desk Food Centro</strong> roda o fluxo completo: cardápio, carrinho, pagamento e rastreio.</p>
                <dl class="mt-8 grid grid-cols-2 gap-4">
                    <div class="rounded-xl border border-zinc-200 bg-white px-4 py-3">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-zinc-500">Unidades ativas</dt>
                        <dd class="font-display mt-1 text-2xl font-semibold text-zinc-900"><?= (int) $stats['units_active'] ?></dd>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-white px-4 py-3">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-zinc-500">Pedidos hoje</dt>
                        <dd class="font-display mt-1 text-2xl font-semibold text-zinc-900"><?= (int) $stats['orders_today'] ?></dd>
                    </div>
                </dl>
            </div>
            <article class="lp-proof-case">
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Case piloto</p>
                <h3 class="mt-2 font-display text-xl font-semibold text-zinc-900">Desk Food Centro · São Paulo</h3>
                <p class="mt-3 text-sm leading-relaxed text-zinc-600">Cardápio publicado em <code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs">/u/centro</code>, PIX com webhook, quadro da cozinha e link de rastreio para o cliente — o mesmo pacote que implantamos na sua rede.</p>
                <ul class="mt-5 space-y-2 text-sm text-zinc-700">
                    <li class="flex gap-2"><span class="lp-check lp-check--light shrink-0" aria-hidden="true"></span>Cliente pede sem instalar app</li>
                    <li class="flex gap-2"><span class="lp-check lp-check--light shrink-0" aria-hidden="true"></span>Operador vê fila por status</li>
                    <li class="flex gap-2"><span class="lp-check lp-check--light shrink-0" aria-hidden="true"></span>Dono configura unidade e pagamentos</li>
                </ul>
                <a href="<?= htmlspecialchars($demoMenuUrl) ?>" class="lp-btn-primary mt-6 inline-flex" target="_blank" rel="noopener">Abrir loja demo <?= landing_icon('arrow') ?></a>
            </article>
        </div>
    </div>
</section>

<!-- Marquee removido — menos ruído visual -->

<!-- Dor -->
<section id="por-que" class="lp-panel lp-panel--flat lp-band-light scroll-mt-24 py-20 md:py-28" data-panel>
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <div class="mx-auto max-w-2xl text-center">
            <p class="lp-eyebrow text-[#c2410c]">O problema</p>
            <h2 class="lp-section-title mt-3">Marketplace traz pedido.<br>Leva margem e controle.</h2>
            <p class="lp-section-lead mx-auto mt-4">
                Enquanto você depende só de app, o cliente é deles, a comissão é fixa e a cozinha vive no improviso do WhatsApp.
            </p>
        </div>
        <div class="mt-14 grid gap-6 md:grid-cols-3">
            <article class="lp-pain-card">
                <span class="lp-pain-card__num">01</span>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Comissão em todo pedido</h3>
                <p class="mt-2 text-sm leading-relaxed text-zinc-600">
                    Até 27% por venda some do seu lucro. Com canal próprio, o valor da venda fica na sua operação.
                </p>
            </article>
            <article class="lp-pain-card">
                <span class="lp-pain-card__num">02</span>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Pedido fora do lugar</h3>
                <p class="mt-2 text-sm leading-relaxed text-zinc-600">
                    Print de PIX, áudio, planilha — erro humano e fila bagunçada. Um sistema, uma fila, um status.
                </p>
            </article>
            <article class="lp-pain-card">
                <span class="lp-pain-card__num">03</span>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Cliente sem visibilidade</h3>
                <p class="mt-2 text-sm leading-relaxed text-zinc-600">
                    “Cadê meu pedido?” ocupa sua equipe. Com rastreio público, o cliente acompanha sozinho.
                </p>
            </article>
        </div>
        <p class="mt-12 text-center">
            <a href="#contato" class="lp-link-cta">Agendar conversa <?= landing_icon('arrow') ?></a>
        </p>
    </div>
</section>

<!-- Produto -->
<section id="produto" class="lp-panel lp-panel--depth lp-band-dark scroll-mt-24 py-20 md:py-28" data-panel>
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <div class="max-w-2xl">
            <p class="lp-eyebrow">O produto</p>
            <h2 class="lp-section-title mt-3 text-white">Do cardápio ao caixa — uma plataforma.</h2>
            <p class="mt-4 text-zinc-400 leading-relaxed">
                Cada loja com URL própria, pagamento integrado e painel da cozinha.
                O cliente acompanha o pedido; a equipe não perde tempo conferindo print de PIX.
            </p>
        </div>
        <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <?php
            $features = [
                ['menu', 'Cardápio por loja', 'Adicionais, cupom e busca — link /u/sua-loja para divulgar.'],
                ['pay', 'Pagamento integrado', 'PIX e cartão por unidade. Webhook confirma sem intervenção manual.'],
                ['phone', 'Cliente identificado', 'Login por SMS, endereços salvos e painel LGPD.'],
                ['board', 'Fila da cozinha', 'Colunas por status, comanda, motoboy e quadro ao vivo.'],
                ['chart', 'Gestão do dono', 'Unidades, operadores, permissões e CSV de pedidos.'],
                ['shield', 'Pronto para produção', 'HTTPS, auditoria, healthcheck e papéis de acesso.', true],
            ];
            foreach ($features as $f):
                $dark = !empty($f[3]);
            ?>
            <article class="lp-card <?= $dark ? 'lp-card--dark' : '' ?>">
                <div class="lp-icon"><?= landing_icon($f[0]) ?></div>
                <h3 class="mt-5 text-base font-semibold <?= $dark ? 'text-white' : 'text-zinc-900' ?>"><?= htmlspecialchars($f[1]) ?></h3>
                <p class="mt-2 text-sm leading-relaxed <?= $dark ? 'text-zinc-400' : 'text-zinc-600' ?>"><?= htmlspecialchars($f[2]) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="mt-12 text-center">
            <a href="#contato" class="lp-btn-primary inline-flex">Agendar conversa <?= landing_icon('arrow') ?></a>
        </p>
    </div>
</section>

<!-- Economia -->
<section class="lp-panel lp-panel--flat lp-band-light scroll-mt-24 py-16 md:py-20" data-panel
         x-data="{
            pedidos: 350,
            ticket: 42,
            taxa: 25,
            get mensal() { return Math.round(this.pedidos * this.ticket * (this.taxa / 100)); },
            get anual() { return this.mensal * 12; }
         }">
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <div class="lp-savings rounded-2xl border border-zinc-200 bg-white p-8 md:p-12 lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
            <div>
                <p class="lp-eyebrow text-[#c2410c]">Na prática</p>
                <h2 class="lp-section-title mt-3">Quanto da sua margem vai para o app?</h2>
                <p class="lp-section-lead mt-3">Simule com o volume da sua operação. No canal próprio, essa fatia volta para você.</p>
            </div>
            <div>
                <label class="text-xs font-medium text-zinc-600">Pedidos delivery por mês</label>
                <input type="range" min="50" max="2000" step="50" x-model.number="pedidos" class="lp-range mt-2 w-full">
                <p class="text-right text-sm font-semibold text-zinc-900" x-text="pedidos + ' pedidos'"></p>
                <label class="mt-5 block text-xs font-medium text-zinc-600">Ticket médio (R$)</label>
                <input type="range" min="20" max="120" step="5" x-model.number="ticket" class="lp-range mt-2 w-full">
                <p class="text-right text-sm font-semibold text-zinc-900" x-text="'R$ ' + ticket.toFixed(2).replace('.', ',')"></p>
                <label class="mt-5 block text-xs font-medium text-zinc-600">Comissão estimada do marketplace (%)</label>
                <input type="range" min="15" max="35" step="1" x-model.number="taxa" class="lp-range mt-2 w-full">
                <p class="text-right text-sm font-semibold text-zinc-900" x-text="taxa + '%'"></p>
                <div class="mt-8 rounded-xl bg-zinc-900 px-6 py-5 text-center text-white">
                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">Economia potencial por ano</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-[#fb923c]" x-text="'R$ ' + anual.toLocaleString('pt-BR')"></p>
                    <p class="mt-2 text-xs text-zinc-500">Estimativa ilustrativa — não inclui taxas de gateway</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Fluxo -->
<section id="fluxo" class="lp-panel lp-panel--depth lp-band-dark scroll-mt-24 py-20 md:py-28" data-panel>
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <div class="grid gap-16 lg:grid-cols-2 lg:items-start">
            <div>
                <p class="lp-eyebrow">Como funciona</p>
                <h2 class="lp-section-title mt-3 text-white">Dono, operador e cliente<br>no mesmo fluxo.</h2>
                <p class="mt-4 text-zinc-400 leading-relaxed">A <?= htmlspecialchars($commercialCompany) ?> acompanha a implantação. A equipe costuma dominar o painel no primeiro dia de operação.</p>
                <ol class="mt-12 space-y-10">
                    <li class="flex gap-5">
                        <span class="lp-step-num">01</span>
                        <div>
                            <p class="font-semibold text-white">Dono configura</p>
                            <p class="mt-2 text-sm text-zinc-500 leading-relaxed">Unidades, cardápio, PIX/cartão por filial e quem acessa o quê.</p>
                        </div>
                    </li>
                    <li class="flex gap-5">
                        <span class="lp-step-num">02</span>
                        <div>
                            <p class="font-semibold text-white">Operador executa</p>
                            <p class="mt-2 text-sm text-zinc-500 leading-relaxed">Recebe, confirma pagamento, prepara, despacha e fecha o caixa.</p>
                        </div>
                    </li>
                    <li class="flex gap-5">
                        <span class="lp-step-num">03</span>
                        <div>
                            <p class="font-semibold text-white">Cliente compra e acompanha</p>
                            <p class="mt-2 text-sm text-zinc-500 leading-relaxed">Pede no seu site, paga como preferir e rastreia sem ligar.</p>
                        </div>
                    </li>
                </ol>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-8">
                <p class="text-xs font-semibold tracking-widest text-zinc-500 uppercase">Integrações</p>
                <ul class="mt-6 grid grid-cols-2 gap-3 text-sm text-zinc-300">
                    <li class="rounded-lg border border-white/10 px-3 py-2.5">Mercado Pago</li>
                    <li class="rounded-lg border border-white/10 px-3 py-2.5">Efi Pay</li>
                    <li class="rounded-lg border border-white/10 px-3 py-2.5">Twilio / Zenvia</li>
                    <li class="rounded-lg border border-white/10 px-3 py-2.5">SMTP</li>
                    <li class="rounded-lg border border-white/10 px-3 py-2.5">ViaCEP</li>
                    <li class="rounded-lg border border-white/10 px-3 py-2.5">Google Analytics</li>
                </ul>
                <a href="/ajuda" class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-[#fb923c] hover:text-[#fdba74]">
                    Central de ajuda <?= landing_icon('arrow') ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Cenários de uso -->
<section id="depoimentos" class="lp-panel lp-panel--flat lp-band-light scroll-mt-24 border-t border-zinc-200/80 py-20 md:py-28" data-panel>
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <div class="mx-auto max-w-2xl text-center">
            <p class="lp-eyebrow text-[#c2410c]">Onde encaixa</p>
            <h2 class="lp-section-title mt-3">Operações que o <?= htmlspecialchars($appName) ?> foi desenhado para atender</h2>
        </div>
        <div class="mt-14 grid gap-6 md:grid-cols-3">
            <?php
            $scenarios = [
                ['title' => 'Delivery com alto volume', 'text' => 'Fila única no painel, PIX confirmando sozinho e menos tempo no telefone conferindo pagamento.'],
                ['title' => 'Duas ou mais unidades', 'text' => 'Cada filial com cardápio, credencial de pagamento e operador — um dono enxerga tudo.'],
                ['title' => 'Canal próprio além do app', 'text' => 'Divulga o link da loja para clientes recorrentes e mantém a margem que hoje vai para comissão.'],
            ];
            foreach ($scenarios as $s):
            ?>
            <article class="lp-pain-card">
                <h3 class="text-lg font-semibold text-zinc-900"><?= htmlspecialchars($s['title']) ?></h3>
                <p class="mt-3 text-sm leading-relaxed text-zinc-600"><?= htmlspecialchars($s['text']) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Planos -->
<section id="planos" class="lp-panel lp-panel--flat lp-plans-section scroll-mt-24 py-20 md:py-28" data-panel>
    <div class="lp-plans-section__bg" aria-hidden="true"></div>
    <div class="lp-panel__inner relative mx-auto max-w-6xl px-4">
        <div class="mx-auto max-w-2xl text-center">
            <p class="lp-eyebrow text-[#c2410c]">Planos</p>
            <h2 class="lp-section-title mt-3">Proposta conforme o tamanho da operação</h2>
            <p class="lp-section-lead mx-auto mt-4">Valores e escopo definidos depois da conversa inicial — sem letras miúdas escondidas.</p>
        </div>
        <div class="mt-14 grid items-stretch gap-6 lg:grid-cols-3">
            <?php
            $plans = [
                [
                    'tag' => 'Uma loja',
                    'name' => 'Operação única',
                    'desc' => 'Para quem quer testar o canal próprio',
                    'featured' => false,
                    'cta' => 'Agendar conversa',
                    'ctaClass' => 'lp-btn-ghost border-zinc-300 !text-zinc-900',
                    'feats' => ['Cardápio e pedidos no seu site', 'PIX e pagamento na entrega', 'Painel operador + rastreio'],
                ],
                [
                    'tag' => 'Rede',
                    'name' => 'Multi-unidade',
                    'desc' => 'Franquias e grupos com várias filiais',
                    'featured' => true,
                    'cta' => 'Agendar conversa',
                    'ctaClass' => 'lp-btn-primary',
                    'feats' => ['Tudo da operação única', 'Cartão online (Mercado Pago)', 'PIX e credenciais por filial', 'Caixa, motoboy, relatórios CSV'],
                ],
                [
                    'tag' => 'Projeto',
                    'name' => 'Sob medida',
                    'desc' => 'Regras específicas ou escala nacional',
                    'featured' => false,
                    'cta' => 'Agendar conversa',
                    'ctaClass' => 'lp-btn-ghost border-zinc-300 !text-zinc-900',
                    'feats' => ['Infra e SLA combinados', 'Suporte prioritário', 'Customizações acordadas'],
                ],
            ];
            foreach ($plans as $plan):
            ?>
            <article class="lp-price <?= $plan['featured'] ? 'lp-price--featured' : '' ?>">
                <?php if ($plan['featured']): ?>
                <span class="lp-price__badge">Mais comum</span>
                <?php endif; ?>
                <p class="lp-price__tag <?= $plan['featured'] ? 'text-[#c2410c]' : '' ?>"><?= htmlspecialchars($plan['tag']) ?></p>
                <p class="mt-4 font-display text-2xl font-semibold text-zinc-900"><?= htmlspecialchars($plan['name']) ?></p>
                <p class="mt-2 text-sm <?= $plan['featured'] ? 'text-zinc-600' : 'text-zinc-500' ?>"><?= htmlspecialchars($plan['desc']) ?></p>
                <ul class="lp-price__list mt-8 flex-1">
                    <?php foreach ($plan['feats'] as $feat): ?>
                    <li class="lp-price__feat"><?= landing_icon('check') ?><span><?= htmlspecialchars($feat) ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <a href="#contato" class="<?= htmlspecialchars($plan['ctaClass']) ?> mt-8 w-full justify-center"><?= htmlspecialchars($plan['cta']) ?></a>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="mx-auto mt-10 max-w-xl text-center text-sm text-zinc-500">Valores e mensalidade definidos na conversa inicial — conforme unidades, volume e integrações.</p>
    </div>
</section>

<!-- Quem está por trás -->
<section id="sobre" class="lp-panel lp-panel--flat lp-band-light scroll-mt-24 border-t border-zinc-200 py-16 md:py-20" data-panel>
    <div class="lp-panel__inner mx-auto max-w-3xl px-4 text-center">
        <p class="lp-eyebrow text-[#c2410c]">Quem desenvolve</p>
        <h2 class="lp-section-title mt-3"><?= htmlspecialchars($commercialCompany) ?></h2>
        <p class="lp-section-lead mx-auto mt-4">
            A <?= htmlspecialchars($commercialCompany) ?> implanta e acompanha o <?= htmlspecialchars($appName) ?> em restaurantes e redes de delivery.
            Suporte em português, documentação no site e conversa direta com quem entende de operação — não só de software.
        </p>
        <?php if ($commercialEmail !== ''): ?>
        <p class="mt-6 text-sm text-zinc-600">
            <a href="mailto:<?= htmlspecialchars($commercialEmail) ?>" class="font-semibold text-zinc-900 hover:underline"><?= htmlspecialchars($commercialEmail) ?></a>
            <?php if ($commercialPhone !== ''): ?>
                <span class="text-zinc-400"> · </span>
                <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $commercialTel)) ?>" class="font-semibold text-zinc-900 hover:underline"><?= htmlspecialchars($commercialPhone) ?></a>
            <?php endif; ?>
        </p>
        <?php endif; ?>
    </div>
</section>

<!-- FAQ -->
<section id="faq" class="lp-panel lp-panel--flat lp-faq-section scroll-mt-24 pb-24 md:pb-32" data-panel>
    <div class="lp-panel__inner mx-auto max-w-3xl px-4">
        <div class="text-center">
            <p class="lp-eyebrow text-[#c2410c]">Dúvidas</p>
            <h2 class="lp-section-title mt-3">Perguntas de quem está avaliando</h2>
            <p class="lp-section-lead mx-auto mt-3">Respostas diretas antes de agendar a conversa.</p>
        </div>
        <div class="lp-faq-list mt-12">
            <?php
            $faqs = [
                ['market', 'Isso substitui iFood ou Rappi?', 'Não precisa substituir de um dia para o outro. O ' . $appName . ' é seu canal próprio: sem comissão por pedido, com sua marca. Muitos usam marketplace para aquisição e o site próprio para margem e recorrência.'],
                ['clock', 'Quanto tempo para começar?', 'Com cardápio definido, a operação básica sobe em poucos dias. Pagamentos reais exigem HTTPS e configuração do gateway — orientamos passo a passo.'],
                ['pay', 'Quais pagamentos aceita?', 'PIX (Efi ou Mercado Pago) e cartão via MP. Cada unidade pode ter credenciais próprias. Na entrega: dinheiro ou cartão físico.'],
                ['menu', 'Esta página e o site de pedidos', 'Aqui você conhece o produto para o seu restaurante. Em / o cliente final vê só as lojas abertas para pedir.'],
                ['shield', 'LGPD e dados do cliente', 'Termos, privacidade, consentimento e painel do cliente para exportar ou excluir dados pessoais.'],
            ];
            foreach ($faqs as $i => $item):
            ?>
            <details class="lp-faq-item" <?= $i === 0 ? 'open' : '' ?>>
                <summary class="lp-faq-summary">
                    <span class="lp-faq-summary__icon"><?= landing_icon($item[0]) ?></span>
                    <span class="lp-faq-summary__text"><?= htmlspecialchars($item[1]) ?></span>
                    <span class="lp-faq-chevron" aria-hidden="true"></span>
                </summary>
                <div class="lp-faq-body">
                    <p><?= htmlspecialchars($item[2]) ?></p>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
        <p class="mt-10 text-center text-sm text-zinc-500">
            Ainda com dúvida? <a href="#contato" class="font-semibold text-[#c2410c] hover:underline">Fale com a equipe comercial</a>
        </p>
    </div>
</section>

<!-- Contato -->
<section id="contato" class="lp-panel lp-panel--flat lp-band-light scroll-mt-24 border-t border-zinc-200 py-20 md:py-28" data-panel>
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <div class="grid gap-14 lg:grid-cols-2 lg:items-start">
            <div>
                <p class="lp-eyebrow text-[#c2410c]">Fale conosco</p>
                <h2 class="lp-section-title mt-3">Agende uma conversa</h2>
                <p class="lp-section-lead mt-4">
                    A <?= htmlspecialchars($commercialCompany) ?> mostra o <?= htmlspecialchars($appName) ?> ao vivo
                    e monta proposta para o seu cenário — em cerca de 30 minutos.
                </p>
                <ul class="mt-8 space-y-3 text-sm text-zinc-600">
                    <li class="flex gap-2"><span class="lp-check lp-check--light shrink-0" aria-hidden="true"></span>Sem custo para conhecer</li>
                    <li class="flex gap-2"><span class="lp-check lp-check--light shrink-0" aria-hidden="true"></span>Resposta em até 1 dia útil</li>
                    <li class="flex gap-2"><span class="lp-check lp-check--light shrink-0" aria-hidden="true"></span>Proposta sem compromisso</li>
                </ul>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <?php if ($commercialEmail !== ''): ?>
                    <a href="mailto:<?= htmlspecialchars($commercialEmail) ?>" class="lp-btn-ghost !text-zinc-800"><?= htmlspecialchars($commercialEmail) ?></a>
                    <?php endif; ?>
                    <?php if ($commercialTel !== '' && $commercialPhone !== '' && $waContact !== ''): ?>
                    <a href="https://wa.me/<?= htmlspecialchars($waContact) ?>?text=<?= rawurlencode('Olá! Quero agendar uma conversa sobre o Desk Food.') ?>" class="lp-btn-ghost !text-zinc-800" target="_blank" rel="noopener">WhatsApp</a>
                    <?php endif; ?>
                    <?php if ($commercialTel !== '' && $commercialPhone !== ''): ?>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $commercialTel)) ?>" class="lp-btn-primary"><?= htmlspecialchars($commercialPhone) ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-xl shadow-zinc-200/40">
                <h3 class="text-lg font-semibold text-zinc-900">Agendar conversa</h3>
                <p class="mt-1 text-sm text-zinc-500">Retornamos em até 1 dia útil com horário sugerido.</p>
                <form method="post" action="/landing/contato" class="lp-form relative mt-6 space-y-4">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="pointer-events-none absolute h-0 w-0 opacity-0" aria-hidden="true">
                    <div>
                        <label class="text-xs font-medium text-zinc-600">Nome</label>
                        <input name="name" required class="mt-1" placeholder="Seu nome">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-zinc-600">E-mail</label>
                        <input name="email" type="email" required class="mt-1" placeholder="contato@restaurante.com.br">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-medium text-zinc-600">WhatsApp</label>
                            <input name="phone" class="mt-1" placeholder="(00) 00000-0000">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-zinc-600">Restaurante / rede</label>
                            <input name="company" class="mt-1" placeholder="Nome da marca">
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="text-xs font-medium text-zinc-600">Unidades</label>
                            <select name="units_count" class="mt-1 w-full">
                                <option value="">Selecione</option>
                                <option value="1">1 loja</option>
                                <option value="2-5">2 a 5</option>
                                <option value="6+">6 ou mais</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-zinc-600">Pedidos/mês</label>
                            <select name="orders_month" class="mt-1 w-full">
                                <option value="">Selecione</option>
                                <option value="Até 100">Até 100</option>
                                <option value="100-500">100 a 500</option>
                                <option value="500+">Mais de 500</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-zinc-600">Usa marketplace?</label>
                            <select name="marketplace" class="mt-1 w-full">
                                <option value="">Selecione</option>
                                <option value="Não">Não</option>
                                <option value="Sim (iFood/Rappi)">Sim</option>
                                <option value="Canal próprio + app">Ambos</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-zinc-600">Observações (opcional)</label>
                        <textarea name="message" rows="3" class="mt-1" placeholder="Algo específico da sua operação?"></textarea>
                    </div>
                    <button type="submit" class="lp-btn-primary w-full">Enviar</button>
                </form>
                <p class="mt-4 text-center text-xs text-zinc-500">
                    Ao enviar, você concorda com a <a class="text-[#c2410c] underline" href="/privacidade">política de privacidade</a>.
                </p>
            </div>
        </div>
    </div>
</section>

</div>
</div>

<!-- CTA fixo mobile -->
<div class="lp-sticky-cta" aria-hidden="false">
    <a href="#contato" class="lp-sticky-cta__btn">Agendar conversa</a>
</div>
