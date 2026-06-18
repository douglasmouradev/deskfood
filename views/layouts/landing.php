<?php
declare(strict_types=1);
/** @var string $__content_path */
use App\Helpers\OrderEntry;

$config = require BASE_PATH . '/config/app.php';
$orderHref = OrderEntry::hrefForActiveUnits();
$title = $title ?? ($config['name'] ?? 'Desk Food');
$metaDescription = $metaDescription ?? ($config['default_meta_description'] ?? '');
$appName = (string) ($config['name'] ?? 'Desk Food');
$baseUrl = rtrim((string) ($config['url'] ?? ''), '/');
$ogUrl = $baseUrl !== '' ? $baseUrl . '/landing' : '/landing';
$ogImage = $baseUrl !== '' ? $baseUrl . '/assets/img/og-landing.svg' : '/assets/img/og-landing.svg';
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => $appName,
    'applicationCategory' => 'BusinessApplication',
    'operatingSystem' => 'Web',
    'description' => $metaDescription,
    'url' => $ogUrl,
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'BRL',
        'availability' => 'https://schema.org/InStock',
        'description' => 'Proposta sob consulta',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name' => (string) ($config['commercial_company'] ?? $appName),
    ],
];
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta name="theme-color" content="#09090b">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="<?= htmlspecialchars($ogUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($ogUrl) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" href="/assets/img/logo.png" type="image/png">
    <link rel="apple-touch-icon" href="/assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <?php require BASE_PATH . '/views/partials/tailwind_assets.php'; ?>
    <link rel="stylesheet" href="/assets/css/landing.css">
    <style>[x-cloak]{display:none!important}</style>
    <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script defer src="/assets/js/landing-nav.js"></script>
    <script defer src="/assets/js/landing-scroll.js"></script>
</head>
<body class="landing-page min-h-full antialiased">
<a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-zinc-900">Ir para o conteúdo</a>

<header class="lp-header fixed top-0 left-0 right-0 z-50 border-b border-transparent">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-4">
        <a href="/landing" class="flex min-w-0 items-center gap-3">
            <img src="/assets/img/logo.png" alt="<?= htmlspecialchars($appName ?? 'Desk Food') ?>" width="32" height="32" class="h-8 w-auto">
            <span class="hidden font-display text-sm font-semibold tracking-tight text-white sm:inline"><?= htmlspecialchars($appName) ?></span>
        </a>
        <nav id="nav-landing" class="hidden items-center gap-7 text-sm font-medium lg:flex" aria-label="Principal">
            <a class="lp-nav-link" href="#demo">Demo ao vivo</a>
            <a class="lp-nav-link" href="#por-que">Por quê</a>
            <a class="lp-nav-link" href="#produto">Produto</a>
            <a class="lp-nav-link" href="#planos">Planos</a>
            <a class="lp-nav-link" href="#contato">Contato</a>
        </nav>
        <div class="hidden items-center gap-3 lg:flex">
            <a href="<?= htmlspecialchars($orderHref) ?>" class="text-sm font-medium text-zinc-400 hover:text-white">Pedir</a>
            <a href="#contato" class="lp-cta rounded-full px-4 py-2 text-sm font-semibold">Agendar conversa</a>
        </div>
        <button type="button" class="lp-nav-toggle rounded-lg border border-white/10 p-2 text-zinc-300 hover:bg-white/5 lg:hidden" data-lp-nav-toggle aria-expanded="false" aria-controls="nav-landing-mobile">
            <span class="sr-only">Abrir menu</span>
            <svg class="h-5 w-5 lp-nav-toggle__open" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg class="h-5 w-5 lp-nav-toggle__close hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div id="nav-landing-mobile" class="lp-mobile-nav border-t border-white/10 bg-zinc-950/95 px-4 py-4 backdrop-blur-lg lg:hidden" data-lp-nav-panel hidden>
        <nav class="flex flex-col gap-1 text-sm font-medium" aria-label="Menu mobile">
            <a class="rounded-lg px-3 py-2.5 text-zinc-300 hover:bg-white/5" href="#demo">Demo ao vivo</a>
            <a class="rounded-lg px-3 py-2.5 text-zinc-300 hover:bg-white/5" href="#por-que">Por quê</a>
            <a class="rounded-lg px-3 py-2.5 text-zinc-300 hover:bg-white/5" href="#produto">Produto</a>
            <a class="rounded-lg px-3 py-2.5 text-zinc-300 hover:bg-white/5" href="#planos">Planos</a>
            <a class="rounded-lg px-3 py-2.5 text-zinc-300 hover:bg-white/5" href="#contato">Contato</a>
            <a class="rounded-lg px-3 py-2.5 text-zinc-300 hover:bg-white/5" href="<?= htmlspecialchars($orderHref) ?>">Pedir comida</a>
            <a class="mt-2 rounded-full bg-[#ea580c] px-4 py-2.5 text-center text-white" href="#contato">Agendar conversa</a>
        </nav>
    </div>
</header>

<main id="conteudo" class="w-full overflow-x-hidden pt-[4.25rem]">
    <div class="lp-flash-slot">
        <?php require BASE_PATH . '/views/partials/flash.php'; ?>
    </div>
    <?php require $__content_path; ?>
</main>

<footer class="lp-footer">
    <div class="mx-auto flex max-w-6xl flex-col gap-10 px-4 py-14 md:flex-row md:justify-between">
        <div class="max-w-xs">
            <p class="font-display text-sm font-semibold text-white"><?= htmlspecialchars($appName) ?></p>
            <p class="mt-3 text-sm leading-relaxed text-zinc-500">Delivery com marca própria para restaurantes que querem controle — não dependência de app.</p>
        </div>
        <div class="flex flex-wrap gap-12 text-sm">
            <div class="flex flex-col gap-2">
                <span class="text-[10px] font-semibold tracking-widest text-zinc-600 uppercase">Produto</span>
                <a class="text-zinc-400 hover:text-white" href="#demo">Demo</a>
                <a class="text-zinc-400 hover:text-white" href="#planos">Planos</a>
                <a class="text-zinc-400 hover:text-white" href="/ajuda">Ajuda</a>
            </div>
            <div class="flex flex-col gap-2">
                <span class="text-[10px] font-semibold tracking-widest text-zinc-600 uppercase">Acesso</span>
                <a class="text-zinc-400 hover:text-white" href="/cliente/login">Cliente</a>
                <a class="text-zinc-400 hover:text-white" href="/operador/login">Operador</a>
                <a class="text-zinc-400 hover:text-white" href="/admin/login">Dono</a>
            </div>
            <?php require BASE_PATH . '/views/partials/contact_footer.php'; ?>
            <div class="flex flex-col gap-2">
                <span class="text-[10px] font-semibold tracking-widest text-zinc-600 uppercase">Legal</span>
                <a class="text-zinc-400 hover:text-white" href="/privacidade">Privacidade</a>
                <a class="text-zinc-400 hover:text-white" href="/termos">Termos</a>
            </div>
        </div>
    </div>
    <div class="border-t border-white/5 py-5 text-center text-xs text-zinc-600">
        © <?= date('Y') ?> <?= htmlspecialchars($appName) ?>
    </div>
</footer>
</body>
</html>
