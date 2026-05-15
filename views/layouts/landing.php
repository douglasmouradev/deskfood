<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$title = $title ?? ($config['name'] ?? 'Desk Food');
$metaDescription = $metaDescription ?? ($config['default_meta_description'] ?? '');
$appName = (string) ($config['name'] ?? 'Desk Food');
$baseUrl = rtrim((string) ($config['url'] ?? ''), '/');
$ogUrl = $baseUrl . '/landing';
$ogImage = $baseUrl . '/assets/img/logo.png';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta name="theme-color" content="#f97316">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="<?= htmlspecialchars($ogUrl) ?>">
    <meta property="og:type" content="website">
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#fff4ed', 100: '#ffe4d4', 400: '#fb923c', 500: '#f97316', 600: '#ea580c', 700: '#c2410c', 900: '#7c2d12' },
                        ink: { 900: '#0f172a', 800: '#1e293b', 700: '#334155', 500: '#64748b', 400: '#94a3b8' }
                    },
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'], display: ['"Space Grotesk"', 'system-ui', 'sans-serif'] }
                }
            }
        };
    </script>
    <style>
        body { font-family: "Plus Jakarta Sans", system-ui, sans-serif; }
        .tabular { font-family: "Space Grotesk", system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <?php require BASE_PATH . '/views/partials/analytics.php'; ?>
</head>
<body class="min-h-full bg-white text-ink-900 antialiased">
<header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/85 backdrop-blur-md" x-data="{ navOpen: false }">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3.5">
        <a href="/landing" class="flex min-w-0 items-center gap-3">
            <img src="/assets/img/logo.png" alt="<?= htmlspecialchars($appName) ?>" class="h-9 w-auto shrink-0">
            <div class="hidden min-w-0 sm:block">
                <p class="truncate text-sm font-bold text-ink-900"><?= htmlspecialchars($appName) ?></p>
                <p class="text-[11px] font-medium uppercase tracking-wide text-brand-600">Plataforma</p>
            </div>
        </a>
        <button type="button" class="inline-flex rounded-lg border border-slate-200 p-2 text-slate-700 hover:bg-slate-50 lg:hidden" @click="navOpen = !navOpen" :aria-expanded="navOpen.toString()" aria-controls="nav-landing">
            <span class="sr-only">Menu</span>
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <nav id="nav-landing" class="hidden items-center gap-7 text-sm font-semibold text-ink-700 lg:flex">
            <a class="hover:text-brand-600" href="#recursos">Recursos</a>
            <a class="hover:text-brand-600" href="#fluxo">Como funciona</a>
            <a class="hover:text-brand-600" href="#planos">Planos</a>
            <a class="hover:text-brand-600" href="#contato">Contato</a>
            <a class="hover:text-brand-600" href="#faq">FAQ</a>
            <a class="hover:text-brand-600" href="/ajuda">Ajuda</a>
        </nav>
        <div class="hidden items-center gap-2 sm:flex sm:gap-3">
            <a href="/" class="hidden rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-ink-800 hover:bg-slate-50 sm:inline-flex">Ver unidades</a>
            <a href="/admin/login" class="inline-flex rounded-full bg-ink-900 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-ink-800 sm:px-5 sm:text-sm">Sou dono</a>
        </div>
    </div>
    <div x-cloak x-show="navOpen" x-transition class="border-t border-slate-200 bg-white px-4 py-3 lg:hidden">
        <nav class="flex flex-col gap-2 text-sm font-semibold text-ink-800">
            <a class="rounded-lg py-2 hover:text-brand-600" href="#recursos" @click="navOpen=false">Recursos</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="#fluxo" @click="navOpen=false">Como funciona</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="#contato" @click="navOpen=false">Contato</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="#faq" @click="navOpen=false">FAQ</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/ajuda" @click="navOpen=false">Ajuda</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/" @click="navOpen=false">Ver unidades</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/admin/login" @click="navOpen=false">Sou dono</a>
        </nav>
    </div>
</header>
<main class="w-full overflow-x-hidden">
    <div class="mx-auto max-w-6xl px-4 pt-4">
        <?php require BASE_PATH . '/views/partials/flash.php'; ?>
    </div>
    <?php require $__content_path; ?>
</main>
<footer class="border-t border-slate-200 bg-slate-50">
    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-12 md:flex-row md:items-start md:justify-between">
        <div>
            <p class="text-sm font-bold text-ink-900"><?= htmlspecialchars($appName) ?></p>
            <p class="mt-2 max-w-xs text-sm text-ink-500">Delivery B2B com operação diária: cardápio, pedidos, PIX e conformidade.</p>
        </div>
        <div class="flex flex-wrap gap-8 text-sm">
            <div class="flex flex-col gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-ink-400">Produto</span>
                <a class="text-ink-700 hover:text-brand-600" href="/landing">Landing</a>
                <a class="text-ink-700 hover:text-brand-600" href="/ajuda">Central de ajuda</a>
                <a class="text-ink-700 hover:text-brand-600" href="/">Unidades ativas</a>
            </div>
            <div class="flex flex-col gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-ink-400">Acessos</span>
                <a class="text-ink-700 hover:text-brand-600" href="/cliente/login">Cliente</a>
                <a class="text-ink-700 hover:text-brand-600" href="/operador/login">Operador</a>
                <a class="text-ink-700 hover:text-brand-600" href="/admin/login">Dono</a>
            </div>
            <?php require BASE_PATH . '/views/partials/contact_footer.php'; ?>
            <div class="flex flex-col gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-ink-400">Legal</span>
                <a class="text-ink-700 hover:text-brand-600" href="/privacidade">Privacidade</a>
                <a class="text-ink-700 hover:text-brand-600" href="/termos">Termos</a>
            </div>
        </div>
    </div>
    <div class="border-t border-slate-200/80 py-4 text-center text-xs text-ink-500">
        © <?= date('Y') ?> <?= htmlspecialchars($appName) ?>
    </div>
</footer>
</body>
</html>
