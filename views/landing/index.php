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
                <span class="text-xs font-medium text-zinc-500">Plataforma de delivery B2B</span>
            </div>
            <h1 class="lp-title mt-6 text-4xl text-white md:text-5xl lg:text-[3.25rem]">
                Pare de pagar comissão<br>para vender <span class="text-[#fb923c]">o seu</span> delivery.
            </h1>
            <p class="lp-lead mt-6">
                Lance um canal próprio com a sua marca: cardápio online, PIX e cartão com confirmação automática,
                painel da cozinha em tempo real e rastreio para o cliente — tudo em um só lugar.
            </p>
            <ul class="mt-8 space-y-2.5 text-sm text-zinc-400">
                <li class="flex items-start gap-2.5"><span class="lp-check" aria-hidden="true"></span>Sem taxa por pedido como marketplace</li>
                <li class="flex items-start gap-2.5"><span class="lp-check" aria-hidden="true"></span>Demonstração ao vivo, sem compromisso</li>
                <li class="flex items-start gap-2.5"><span class="lp-check" aria-hidden="true"></span>Multi-unidade, LGPD e pagamento por filial</li>
            </ul>
            <div class="mt-10 flex flex-wrap items-center gap-4">
                <a href="#contato" class="lp-btn-primary">
                    Quero uma demonstração
                    <?= landing_icon('arrow') ?>
                </a>
                <a href="#produto" class="lp-btn-ghost">Ver como funciona</a>
            </div>
            <p class="mt-5 text-xs text-zinc-600">
                Já é cliente? <a href="<?= htmlspecialchars($orderHref) ?>" class="text-zinc-400 hover:text-white underline-offset-2 hover:underline">Pedir em uma loja aberta</a>
                · <a href="/admin/login" class="text-zinc-400 hover:text-white underline-offset-2 hover:underline">Área do dono</a>
            </p>
        </div>

        <div class="lp-device-wrap relative mx-auto w-full max-w-md md:max-w-none" aria-hidden="true">
            <div class="lp-hero-float lp-hero-float--1">PIX confirmado</div>
            <div class="lp-hero-float lp-hero-float--2">+1 pedido #1044</div>
            <div class="lp-device">
                <div class="lp-device__shell relative p-4">
                    <div class="lp-device__shine"></div>
                    <div class="flex items-center justify-between border-b border-white/10 pb-3">
                        <div class="flex gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-red-500/80"></span>
                            <span class="h-2 w-2 rounded-full bg-amber-500/80"></span>
                            <span class="h-2 w-2 rounded-full bg-emerald-500/80"></span>
                        </div>
                        <span class="text-[10px] font-medium tracking-widest text-zinc-500 uppercase"><?= htmlspecialchars($appName) ?> · Operador</span>
                    </div>
                    <div class="mt-4 grid grid-cols-4 gap-2">
                        <?php foreach ([['Pend.', '2'], ['Prep.', '5'], ['Rota', '1'], ['Ok', '4']] as $c): ?>
                        <div class="rounded-lg border border-white/10 bg-white/5 px-2 py-2">
                            <p class="text-[9px] font-medium text-zinc-500"><?= $c[0] ?></p>
                            <p class="mt-0.5 font-display text-lg font-semibold text-white"><?= $c[1] ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-2.5">
                            <div>
                                <p class="text-xs font-medium text-white">#1042 · Smash duplo</p>
                                <p class="text-[10px] text-emerald-400">Pagamento confirmado · Prep.</p>
                            </div>
                            <span class="text-xs font-semibold text-white">R$ 45,80</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-white/[0.03] px-3 py-2.5">
                            <div>
                                <p class="text-xs font-medium text-zinc-400">#1043 · Combo família</p>
                                <p class="text-[10px] text-amber-400/90">Aguardando pagamento</p>
                            </div>
                            <span class="text-xs font-medium text-zinc-500">R$ 89,00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Confiança -->
<div class="lp-panel lp-panel--flat lp-trust" aria-label="Diferenciais">
    <div class="mx-auto grid max-w-6xl grid-cols-2 gap-6 px-4 py-10 md:grid-cols-4 md:gap-8">
        <div class="lp-trust__item">
            <p class="lp-trust__value">0%</p>
            <p class="lp-trust__label">Comissão por pedido no seu canal</p>
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

<!-- Marquee -->
<div class="lp-panel lp-panel--flat lp-marquee" aria-hidden="true">
    <div class="lp-marquee__track">
        <?php
        $tags = [
            'Canal com sua marca',
            'Cardápio digital',
            'PIX por unidade',
            'Cartão Mercado Pago',
            'Quadro da cozinha',
            'Rastreio do pedido',
            'Cupons e promoções',
            'Caixa e motoboy',
            'Relatórios CSV',
            'Multi-unidade',
        ];
        foreach (array_merge($tags, $tags) as $t):
        ?>
        <span><?= htmlspecialchars($t) ?></span>
        <?php endforeach; ?>
    </div>
</div>

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
            <a href="#contato" class="lp-link-cta">Quero sair desse ciclo <?= landing_icon('arrow') ?></a>
        </p>
    </div>
</section>

<!-- Produto -->
<section id="produto" class="lp-panel lp-panel--depth lp-band-dark scroll-mt-24 py-20 md:py-28" data-panel>
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="lp-eyebrow">O produto</p>
                <h2 class="lp-section-title mt-3 text-white">Tudo que o delivery precisa — com a cara da sua loja.</h2>
                <p class="mt-4 text-zinc-400 leading-relaxed">
                    <?= htmlspecialchars($appName) ?> não é cardápio PDF nem link de pagamento solto. É operação completa:
                    do primeiro clique do cliente ao fechamento do caixa.
                </p>
                <ul class="mt-8 space-y-4">
                    <?php
                    $productPoints = [
                        ['Site da unidade', 'Cada loja com URL própria, cardápio, adicionais e cupom.'],
                        ['Pagamento que confirma sozinho', 'PIX e cartão por filial — webhook atualiza o pedido na hora.'],
                        ['Cozinha no controle', 'Quadro por status, comanda, motoboy e atualização em tempo real.'],
                    ];
                    foreach ($productPoints as $pt):
                    ?>
                    <li class="flex gap-4">
                        <span class="lp-check lp-check--dark mt-0.5 shrink-0" aria-hidden="true"></span>
                        <div>
                            <p class="font-semibold text-white"><?= htmlspecialchars($pt[0]) ?></p>
                            <p class="mt-1 text-sm text-zinc-500"><?= htmlspecialchars($pt[1]) ?></p>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="#contato" class="lp-btn-primary mt-10 inline-flex">Agendar demonstração <?= landing_icon('arrow') ?></a>
            </div>
            <div class="lp-showcase-grid">
                <div class="lp-browser">
                    <div class="lp-browser__bar">
                        <span></span><span></span><span></span>
                        <p class="lp-browser__url">sualoja.<?= strtolower(preg_replace('/\s+/', '', $appName)) ?>.com.br</p>
                    </div>
                    <div class="lp-browser__body">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Cardápio</p>
                        <div class="mt-3 flex gap-3 rounded-lg border border-white/10 bg-white/5 p-3">
                            <div class="h-12 w-12 shrink-0 rounded-md bg-zinc-700"></div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-semibold text-white">Smash clássico</p>
                                <p class="text-[10px] text-zinc-500">A partir de R$ 32,90</p>
                            </div>
                            <span class="self-center rounded-full bg-[#ea580c] px-2 py-1 text-[10px] font-bold text-white">+</span>
                        </div>
                        <div class="mt-2 flex gap-3 rounded-lg border border-white/10 bg-white/[0.03] p-3 opacity-80">
                            <div class="h-12 w-12 shrink-0 rounded-md bg-zinc-800"></div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium text-zinc-400">Combo família</p>
                                <p class="text-[10px] text-zinc-600">R$ 89,00</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lp-browser lp-browser--accent">
                    <div class="lp-browser__bar">
                        <span></span><span></span><span></span>
                        <p class="lp-browser__url">Checkout · PIX</p>
                    </div>
                    <div class="lp-browser__body text-center">
                        <p class="text-2xl font-display font-semibold text-white">R$ 45,80</p>
                        <p class="mt-1 text-[10px] text-emerald-400">QR Code · Copia e cola</p>
                        <div class="mx-auto mt-4 h-24 w-24 rounded-lg border border-dashed border-white/20 bg-white/5"></div>
                        <p class="mt-3 text-[10px] text-zinc-500">Confirmação automática no painel</p>
                    </div>
                </div>
            </div>
        </div>
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

<!-- Comparativo -->
<section id="comparativo" class="lp-panel lp-panel--flat lp-band-light scroll-mt-24 border-t border-zinc-200 py-20 md:py-28" data-panel>
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <p class="lp-eyebrow text-[#c2410c]">Comparativo</p>
        <h2 class="lp-section-title mt-3">WhatsApp e planilha não escalam.<br><?= htmlspecialchars($appName) ?> escala.</h2>
        <p class="lp-section-lead mt-4">Mesma equipe, menos ruído — pedido, pagamento e status no lugar certo.</p>
        <div class="lp-compare mt-12">
            <table class="lp-compare w-full">
                <thead>
                    <tr>
                        <th class="text-left">Na operação</th>
                        <th class="text-left">Improviso</th>
                        <th class="text-left text-[#c2410c]"><?= htmlspecialchars($appName) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rows = [
                        ['Fila de pedidos', 'Conferência manual no chat', 'Quadro em tempo real'],
                        ['PIX recebido', 'Print e conferência humana', 'Webhook confirma sozinho'],
                        ['Cartão online', 'Link avulso, sem vínculo', 'Checkout integrado ao pedido'],
                        ['Várias lojas', 'Grupos e planilhas separados', 'Uma plataforma, credencial por unidade'],
                        ['Cliente acompanha', 'Ligação e mensagem', 'Link de rastreio público'],
                    ];
                    foreach ($rows as $row):
                    ?>
                    <tr>
                        <td class="font-medium text-zinc-900"><?= htmlspecialchars($row[0]) ?></td>
                        <td class="lp-mark-no"><?= htmlspecialchars($row[1]) ?></td>
                        <td class="lp-mark-yes"><?= htmlspecialchars($row[2]) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Recursos -->
<section id="recursos" class="lp-panel lp-panel--flat lp-band-light scroll-mt-24 py-20 md:py-28" data-panel>
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <div class="max-w-xl">
            <p class="lp-eyebrow text-[#c2410c]">Recursos</p>
            <h2 class="lp-section-title mt-3">Feito para vender mais e operar melhor</h2>
            <p class="lp-section-lead mt-4">Módulos que se conversam — do pedido ao relatório que o dono exporta.</p>
        </div>
        <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <?php
            $features = [
                ['menu', 'Vitrine que converte', 'Cardápio por loja, adicionais, cupom e URL /u/sua-loja para divulgar.'],
                ['pay', 'Dinheiro na conta, status certo', 'PIX e cartão por unidade. Efi, Mercado Pago ou ambiente de testes.'],
                ['phone', 'Cliente volta sozinho', 'Login por SMS, endereços salvos e área LGPD no app.'],
                ['board', 'Cozinha sem surpresa', 'Colunas de status, comanda, motoboy e atualização automática.'],
                ['chart', 'Dono no comando', 'Unidades, operadores, permissões e exportação CSV de pedidos.'],
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
    </div>
</section>

<!-- CTA meio -->
<section class="lp-panel lp-panel--flat lp-mid-cta py-16 md:py-20" data-panel>
    <div class="lp-panel__inner mx-auto max-w-4xl px-4 text-center">
        <h2 class="lp-title text-2xl text-white md:text-3xl">Veja o <?= htmlspecialchars($appName) ?> rodando com o seu cardápio</h2>
        <p class="mx-auto mt-3 max-w-lg text-zinc-400">Em 30 minutos mostramos pedido, pagamento e painel — você decide se faz sentido para a sua rede.</p>
        <a href="#contato" class="lp-btn-primary mt-8 inline-flex">Marcar demonstração gratuita</a>
    </div>
</section>

<!-- Fluxo -->
<section id="fluxo" class="lp-panel lp-panel--depth lp-band-dark scroll-mt-24 py-20 md:py-28" data-panel>
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <div class="grid gap-16 lg:grid-cols-2 lg:items-start">
            <div>
                <p class="lp-eyebrow">Como funciona</p>
                <h2 class="lp-section-title mt-3 text-white">Três papéis.<br>Uma operação alinhada.</h2>
                <p class="mt-4 text-zinc-400 leading-relaxed">Implantação guiada pela <?= htmlspecialchars($commercialCompany) ?>. Sua equipe costuma dominar o operador no primeiro turno.</p>
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

<!-- Depoimentos -->
<section id="depoimentos" class="lp-panel lp-panel--flat lp-band-light scroll-mt-24 border-t border-zinc-200/80 py-20 md:py-28" data-panel>
    <div class="lp-panel__inner mx-auto max-w-6xl px-4">
        <div class="mx-auto max-w-2xl text-center">
            <p class="lp-eyebrow text-[#c2410c]">Quem usa</p>
            <h2 class="lp-section-title mt-3">Restaurantes que trocaram improviso por processo</h2>
        </div>
        <div class="mt-14 grid gap-6 md:grid-cols-3">
            <?php
            $testimonials = [
                ['initials' => 'DL', 'tone' => 'orange', 'quote' => 'O quadro ao vivo acabou com pedido anotado errado. A cozinha enxerga a fila inteira sem gritaria.', 'role' => 'Gerente de operação', 'place' => 'Delivery · Grande SP', 'featured' => false],
                ['initials' => 'RF', 'tone' => 'zinc', 'quote' => 'Duas lojas, cada uma com seu PIX. O financeiro finalmente bate com o que entrou no sistema.', 'role' => 'Sócia administrativa', 'place' => 'Rede · 2 unidades', 'featured' => true],
                ['initials' => 'DK', 'tone' => 'orange', 'quote' => 'Cliente paga e o status muda sozinho. O telefone ficou para exceção, não para rotina.', 'role' => 'Dono', 'place' => 'Dark kitchen · Interior', 'featured' => false],
            ];
            foreach ($testimonials as $t):
            ?>
            <blockquote class="lp-testimonial <?= $t['featured'] ? 'lp-testimonial--featured' : '' ?>">
                <div class="lp-testimonial__stars" aria-label="5 de 5 estrelas"><?= str_repeat(landing_icon('star'), 5) ?></div>
                <p class="lp-testimonial__text"><?= htmlspecialchars($t['quote']) ?></p>
                <footer class="lp-testimonial__footer">
                    <span class="lp-testimonial__avatar lp-testimonial__avatar--<?= htmlspecialchars($t['tone']) ?>"><?= htmlspecialchars($t['initials']) ?></span>
                    <div>
                        <cite class="not-italic font-semibold text-zinc-900"><?= htmlspecialchars($t['role']) ?></cite>
                        <p class="text-xs text-zinc-500"><?= htmlspecialchars($t['place']) ?></p>
                    </div>
                </footer>
            </blockquote>
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
            <h2 class="lp-section-title mt-3">Investimento alinhado ao seu tamanho</h2>
            <p class="lp-section-lead mx-auto mt-4">Proposta personalizada após entender unidades, volume e integrações. Sem surpresa escondida.</p>
        </div>
        <div class="mt-14 grid items-stretch gap-6 lg:grid-cols-3">
            <?php
            $plans = [
                [
                    'tag' => 'Starter',
                    'name' => 'Uma unidade',
                    'desc' => 'Ideal para validar o canal próprio',
                    'featured' => false,
                    'cta' => 'Pedir proposta',
                    'ctaClass' => 'lp-btn-ghost border-zinc-300 !text-zinc-900',
                    'feats' => ['Cardápio e pedidos no seu site', 'PIX e pagamento na entrega', 'Painel operador + rastreio'],
                ],
                [
                    'tag' => 'Profissional',
                    'name' => 'Multi-unidade',
                    'desc' => 'Redes, franquias e alto volume',
                    'featured' => true,
                    'cta' => 'Solicitar demonstração',
                    'ctaClass' => 'lp-btn-primary',
                    'feats' => ['Tudo do Starter', 'Cartão online (Mercado Pago)', 'PIX e credenciais por filial', 'Caixa, motoboy, relatórios CSV'],
                ],
                [
                    'tag' => 'Enterprise',
                    'name' => 'Projeto dedicado',
                    'desc' => 'Regras específicas ou escala nacional',
                    'featured' => false,
                    'cta' => 'Falar com vendas',
                    'ctaClass' => 'lp-btn-ghost border-zinc-300 !text-zinc-900',
                    'feats' => ['Infra e SLA sob medida', 'Suporte prioritário', 'Customizações combinadas'],
                ],
            ];
            foreach ($plans as $plan):
            ?>
            <article class="lp-price <?= $plan['featured'] ? 'lp-price--featured' : '' ?>">
                <?php if ($plan['featured']): ?>
                <span class="lp-price__badge">Mais pedido</span>
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
    </div>
</section>

<!-- FAQ -->
<section id="faq" class="lp-panel lp-panel--flat lp-faq-section scroll-mt-24 pb-24 md:pb-32" data-panel>
    <div class="lp-panel__inner mx-auto max-w-3xl px-4">
        <div class="text-center">
            <p class="lp-eyebrow text-[#c2410c]">Dúvidas</p>
            <h2 class="lp-section-title mt-3">Perguntas de quem está avaliando</h2>
            <p class="lp-section-lead mx-auto mt-3">Respostas diretas antes de marcar a demonstração.</p>
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
                <h2 class="lp-section-title mt-3">Comece com uma demonstração</h2>
                <p class="lp-section-lead mt-4">
                    A <?= htmlspecialchars($commercialCompany) ?> apresenta o <?= htmlspecialchars($appName) ?> ao vivo:
                    fluxo de pedido, pagamento e painel — e monta proposta para o seu cenário.
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
                    <?php if ($commercialTel !== '' && $commercialPhone !== ''): ?>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $commercialTel)) ?>" class="lp-btn-primary"><?= htmlspecialchars($commercialPhone) ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-xl shadow-zinc-200/40">
                <h3 class="text-lg font-semibold text-zinc-900">Solicitar demonstração</h3>
                <p class="mt-1 text-sm text-zinc-500">Conte sobre sua operação — retornamos com data e horário.</p>
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
                    <div>
                        <label class="text-xs font-medium text-zinc-600">O que você busca?</label>
                        <textarea name="message" rows="4" class="mt-1" placeholder="Quantas unidades, pedidos por mês, usa marketplace hoje?"></textarea>
                    </div>
                    <button type="submit" class="lp-btn-primary w-full">Quero a demonstração</button>
                </form>
                <p class="mt-4 text-center text-xs text-zinc-500">
                    Ao enviar, você concorda com a <a class="text-[#c2410c] underline" href="/privacidade">política de privacidade</a>.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA final -->
<section class="lp-panel lp-panel--flat lp-cta-close py-24 md:py-32" data-panel>
    <div class="lp-panel__inner relative mx-auto max-w-3xl px-4 text-center">
        <p class="lp-eyebrow">Próximo passo</p>
        <h2 class="lp-title mt-4 text-3xl text-white md:text-4xl">Seu delivery pode ser seu negócio de verdade.</h2>
        <p class="mx-auto mt-4 max-w-md text-zinc-400">Marque uma demonstração e veja o <?= htmlspecialchars($appName) ?> com o fluxo completo antes de decidir.</p>
        <div class="mt-10 flex flex-wrap justify-center gap-4">
            <a href="#contato" class="lp-btn-primary">Agendar demonstração gratuita</a>
            <a href="/ajuda" class="lp-btn-ghost">Ler documentação</a>
        </div>
    </div>
</section>

</div>
</div>

<!-- CTA fixo mobile -->
<div class="lp-sticky-cta" aria-hidden="false">
    <a href="#contato" class="lp-sticky-cta__btn">Demonstração gratuita</a>
</div>
