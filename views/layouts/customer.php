<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$headTitle = ($title ?? 'Cliente') . ' · ' . ($config['name'] ?? 'Desk Food');
$headRobots = 'noindex, nofollow';
$headAlpine = true;
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <?php require BASE_PATH . '/views/partials/head.php'; ?>
</head>
<body class="min-h-full bg-gradient-to-b from-orange-50 to-white text-slate-900 antialiased" x-data="{ navOpen: false }">
<header class="border-b border-orange-100 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-4">
        <a href="/" class="flex min-w-0 items-center gap-3">
            <img src="/assets/img/logo.png" alt="" class="h-9 w-auto shrink-0">
            <span class="truncate font-semibold"><?= htmlspecialchars((string) ($config['name'] ?? 'Desk Food')) ?></span>
        </a>
        <button type="button" class="rounded-lg border border-slate-200 p-2 text-slate-700 hover:bg-slate-50 md:hidden" @click="navOpen = !navOpen" aria-controls="nav-customer">
            <span class="sr-only">Menu</span>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <nav id="nav-customer" class="hidden items-center gap-4 text-sm font-medium text-slate-600 md:flex">
            <a class="hover:text-brand-600" href="/cliente/pedidos">Meus pedidos</a>
            <a class="hover:text-brand-600" href="/cliente/enderecos">Endereços</a>
            <a class="hover:text-brand-600" href="/cliente/lgpd">Privacidade</a>
            <?php
            $action = '/cliente/sair';
            $class = 'inline';
            $buttonClass = 'rounded-full bg-brand-500 px-3 py-1 text-white hover:bg-brand-600';
            require BASE_PATH . '/views/partials/logout_form.php';
            ?>
        </nav>
    </div>
    <div x-cloak x-show="navOpen" x-transition class="border-t border-orange-100 bg-white px-4 py-3 md:hidden">
        <nav class="flex flex-col gap-2 text-sm font-medium text-slate-700">
            <a class="rounded-lg py-2 hover:text-brand-600" href="/cliente/pedidos" @click="navOpen=false">Meus pedidos</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/cliente/enderecos" @click="navOpen=false">Endereços</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/cliente/lgpd" @click="navOpen=false">Privacidade (LGPD)</a>
            <?php
            $action = '/cliente/sair';
            $class = 'mt-1';
            $buttonClass = 'rounded-lg py-2 font-semibold text-brand-600 text-left w-full';
            require BASE_PATH . '/views/partials/logout_form.php';
            ?>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-5xl px-4 py-8">
    <?php require BASE_PATH . '/views/partials/flash.php'; ?>
    <?php require $__content_path; ?>
</main>
</body>
</html>
