<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$appName = (string) ($config['name'] ?? 'Desk Food');
$headTitle = ($title ?? 'Cliente') . ' · ' . $appName;
$headRobots = 'noindex, nofollow';
$headAlpine = false;
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <?php require BASE_PATH . '/views/partials/head.php'; ?>
</head>
<body class="df-shell antialiased">
<header class="df-header sticky top-0 z-40">
    <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3.5">
        <a href="/" class="flex min-w-0 items-center gap-3">
            <img src="/assets/img/logo.png" alt="<?= htmlspecialchars($appName) ?>" class="h-8 w-auto shrink-0">
            <span class="truncate text-sm font-semibold text-zinc-900"><?= htmlspecialchars($appName) ?></span>
        </a>
        <button type="button" class="rounded-lg border border-zinc-200 p-2 text-zinc-700 hover:bg-zinc-50 md:hidden" data-df-nav-toggle aria-expanded="false" aria-controls="nav-customer-mobile">
            <span class="sr-only">Menu</span>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <nav id="nav-customer" class="hidden items-center gap-1 text-sm font-medium text-zinc-600 md:flex">
            <a class="rounded-lg px-3 py-2 hover:bg-zinc-100 hover:text-zinc-900" href="/cliente/pedidos">Pedidos</a>
            <a class="rounded-lg px-3 py-2 hover:bg-zinc-100 hover:text-zinc-900" href="/cliente/enderecos">Endereços</a>
            <a class="rounded-lg px-3 py-2 hover:bg-zinc-100 hover:text-zinc-900" href="/cliente/lgpd">Dados pessoais</a>
            <?php
            $action = '/cliente/sair';
            $class = 'inline ml-1';
            $buttonClass = 'df-btn-ghost px-4 py-2 text-xs';
            require BASE_PATH . '/views/partials/logout_form.php';
            ?>
        </nav>
    </div>
    <div id="nav-customer-mobile" class="border-t border-zinc-100 bg-white px-4 py-3 md:hidden" hidden>
        <nav class="flex flex-col gap-1 text-sm font-medium text-zinc-700">
            <a class="rounded-lg px-2 py-2.5 hover:bg-zinc-50" href="/cliente/pedidos">Pedidos</a>
            <a class="rounded-lg px-2 py-2.5 hover:bg-zinc-50" href="/cliente/enderecos">Endereços</a>
            <a class="rounded-lg px-2 py-2.5 hover:bg-zinc-50" href="/cliente/lgpd">Dados pessoais</a>
            <?php
            $action = '/cliente/sair';
            $class = 'mt-2 border-t border-zinc-100 pt-2';
            $buttonClass = 'w-full rounded-lg px-2 py-2.5 text-left font-semibold text-zinc-900 hover:bg-zinc-50';
            require BASE_PATH . '/views/partials/logout_form.php';
            ?>
        </nav>
    </div>
</header>
<main class="df-main">
    <?php require BASE_PATH . '/views/partials/flash.php'; ?>
    <?php require $__content_path; ?>
</main>
<script src="/assets/js/df-nav.js"></script>
</body>
</html>
