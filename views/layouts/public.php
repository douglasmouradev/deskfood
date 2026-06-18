<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$appName = (string) ($config['name'] ?? 'Desk Food');
$headTitle = $title ?? $appName;
$headAlpine = false;
$headAnalytics = true;
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <link rel="manifest" href="/manifest.webmanifest">
    <?php require BASE_PATH . '/views/partials/head.php'; ?>
</head>
<body class="df-shell antialiased">
<header class="df-header sticky top-0 z-40">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3.5">
        <a href="/" class="flex min-w-0 items-center gap-3">
            <img src="/assets/img/logo.png" alt="<?= htmlspecialchars($appName) ?>" class="h-9 w-auto shrink-0">
            <div class="min-w-0 leading-tight">
                <p class="truncate text-sm font-semibold text-zinc-900"><?= htmlspecialchars($appName) ?></p>
                <p class="truncate text-xs text-zinc-500">Pedido direto com o restaurante</p>
            </div>
        </a>
        <button type="button" class="inline-flex items-center justify-center rounded-lg border border-zinc-200 p-2 text-zinc-700 hover:bg-zinc-50 md:hidden" data-df-nav-toggle aria-expanded="false" aria-controls="nav-site-mobile">
            <span class="sr-only">Menu</span>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <nav id="nav-site" class="hidden items-center gap-1 text-sm font-medium text-zinc-600 md:flex">
            <a class="rounded-lg px-3 py-2 hover:bg-zinc-100 hover:text-zinc-900" href="/">Lojas</a>
            <a class="rounded-lg px-3 py-2 hover:bg-zinc-100 hover:text-zinc-900" href="/cliente/login">Entrar</a>
            <a class="rounded-lg px-3 py-2 hover:bg-zinc-100 hover:text-zinc-900" href="/ajuda">Ajuda</a>
            <a class="df-btn-primary ml-2 px-4 py-2 text-xs" href="/landing">Para restaurantes</a>
        </nav>
    </div>
    <div id="nav-site-mobile" class="border-t border-zinc-100 bg-white px-4 py-3 md:hidden" hidden>
        <nav class="flex flex-col gap-1 text-sm font-medium text-zinc-700">
            <a class="rounded-lg px-2 py-2.5 hover:bg-zinc-50" href="/">Lojas</a>
            <a class="rounded-lg px-2 py-2.5 hover:bg-zinc-50" href="/cliente/login">Entrar</a>
            <a class="rounded-lg px-2 py-2.5 hover:bg-zinc-50" href="/ajuda">Ajuda</a>
            <a class="rounded-lg px-2 py-2.5 font-semibold text-zinc-900" href="/landing">Para restaurantes</a>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-6xl px-4 py-8 md:py-12">
    <?php require BASE_PATH . '/views/partials/flash.php'; ?>
    <?php require $__content_path; ?>
</main>
<footer class="border-t border-zinc-200/80 bg-white">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <div class="flex flex-col gap-6 border-b border-zinc-100 pb-8 md:flex-row md:items-start md:justify-between">
            <?php require BASE_PATH . '/views/partials/contact_footer.php'; ?>
        </div>
        <div class="flex flex-col gap-3 pt-6 text-sm text-zinc-500 md:flex-row md:items-center md:justify-between">
            <span>© <?= date('Y') ?> <?= htmlspecialchars($appName) ?></span>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a class="hover:text-zinc-800" href="/landing">Plataforma</a>
                <a class="hover:text-zinc-800" href="/operador/login">Área da loja</a>
                <a class="hover:text-zinc-800" href="/privacidade">Privacidade</a>
                <a class="hover:text-zinc-800" href="/termos">Termos</a>
            </div>
        </div>
    </div>
</footer>
<script src="/assets/js/df-nav.js"></script>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(function () {});
}
</script>
</body>
</html>
