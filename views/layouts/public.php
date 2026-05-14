<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$title = $title ?? ($config['name'] ?? 'Desk Food');
$metaDescription = $metaDescription ?? ($config['default_meta_description'] ?? '');
$canonicalPath = $canonicalPath ?? null;
$baseUrl = (string) ($config['url'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($metaDescription !== ''): ?>
        <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <?php endif; ?>
    <?php if ($canonicalPath !== null && $canonicalPath !== ''): ?>
        <link rel="canonical" href="<?= htmlspecialchars($baseUrl . $canonicalPath) ?>">
    <?php endif; ?>
    <meta name="theme-color" content="#f97316">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" href="/assets/img/logo.png" type="image/png">
    <link rel="apple-touch-icon" href="/assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#fff4ed', 100: '#ffe4d4', 500: '#f97316', 600: '#ea580c', 700: '#c2410c', 900: '#7c2d12' },
                        ink: { 900: '#0f172a', 700: '#334155', 500: '#64748b' }
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
</head>
<body class="min-h-full bg-slate-50 text-ink-900 antialiased">
<header class="border-b border-slate-200 bg-white/90 backdrop-blur" x-data="{ navOpen: false }">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-4">
        <a href="/" class="flex min-w-0 items-center gap-3">
            <img src="/assets/img/logo.png" alt="<?= htmlspecialchars((string) ($config['name'] ?? 'Desk Food')) ?>" class="h-10 w-auto shrink-0">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-ink-900"><?= htmlspecialchars((string) ($config['name'] ?? 'Desk Food')) ?></p>
                <p class="truncate text-xs text-ink-500">Delivery com estilo</p>
            </div>
        </a>
        <button type="button" class="inline-flex items-center justify-center rounded-lg border border-slate-200 p-2 text-slate-700 hover:bg-slate-50 md:hidden" @click="navOpen = !navOpen" :aria-expanded="navOpen.toString()" aria-controls="nav-site">
            <span class="sr-only">Menu</span>
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <nav id="nav-site" class="hidden items-center gap-6 text-sm font-medium text-ink-700 md:flex">
            <a class="hover:text-brand-600" href="/landing">Plataforma</a>
            <a class="hover:text-brand-600" href="/">Unidades</a>
            <a class="hover:text-brand-600" href="/ajuda">Ajuda</a>
            <a class="hover:text-brand-600" href="/cliente/login">Cliente</a>
            <a class="hover:text-brand-600" href="/operador/login">Operador</a>
            <a class="hover:text-brand-600" href="/admin/login">Dono</a>
        </nav>
    </div>
    <div x-cloak x-show="navOpen" x-transition class="border-t border-slate-200 bg-white px-4 py-3 md:hidden">
        <nav class="flex flex-col gap-2 text-sm font-medium text-ink-800">
            <a class="rounded-lg py-2 hover:text-brand-600" href="/landing" @click="navOpen=false">Plataforma</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/" @click="navOpen=false">Unidades</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/ajuda" @click="navOpen=false">Ajuda</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/cliente/login" @click="navOpen=false">Cliente</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/operador/login" @click="navOpen=false">Operador</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/admin/login" @click="navOpen=false">Dono</a>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-6xl px-4 py-10">
    <?php require $__content_path; ?>
</main>
<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="flex flex-col gap-6 border-b border-slate-100 pb-6 md:flex-row md:items-start md:justify-between">
            <?php require BASE_PATH . '/views/partials/contact_footer.php'; ?>
        </div>
        <div class="flex flex-col gap-2 pt-6 text-sm text-ink-500 md:flex-row md:items-center md:justify-between">
            <span>© <?= date('Y') ?> <?= htmlspecialchars((string) ($config['name'] ?? 'Desk Food')) ?></span>
            <div class="flex flex-wrap gap-4">
                <a class="hover:text-brand-600" href="/landing">Plataforma</a>
                <a class="hover:text-brand-600" href="/ajuda">Ajuda</a>
                <a class="hover:text-brand-600" href="/privacidade">Privacidade</a>
                <a class="hover:text-brand-600" href="/termos">Termos</a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
