<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$appName = (string) ($config['name'] ?? 'Desk Food');
$headTitle = ($title ?? 'Cardápio') . ' · ' . $appName;
$headAlpine = true;
$cartCount = 0;
if (!empty($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items'])) {
    foreach ($_SESSION['cart']['items'] as $it) {
        $cartCount += max(1, (int) ($it['qty'] ?? 1));
    }
}
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
            <img src="/assets/img/logo.png" alt="<?= htmlspecialchars($appName) ?>" class="h-9 w-auto shrink-0">
            <span class="truncate font-semibold"><?= htmlspecialchars($appName) ?></span>
        </a>
        <button type="button" class="rounded-lg border border-slate-200 p-2 text-slate-700 hover:bg-slate-50 md:hidden" @click="navOpen = !navOpen" aria-controls="nav-guest">
            <span class="sr-only">Menu</span>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <nav id="nav-guest" class="hidden items-center gap-4 text-sm font-medium text-slate-600 md:flex">
            <a class="hover:text-brand-600" href="/">Lojas</a>
            <a class="relative hover:text-brand-600" href="/cliente/carrinho">
                Carrinho
                <?php if ($cartCount > 0): ?>
                    <span class="ml-1 inline-flex min-w-[1.25rem] justify-center rounded-full bg-brand-500 px-1.5 py-0.5 text-xs font-bold text-white"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <a class="rounded-full bg-brand-500 px-3 py-1 text-white hover:bg-brand-600" href="/cliente/login">Entrar</a>
        </nav>
    </div>
    <div x-cloak x-show="navOpen" x-transition class="border-t border-orange-100 bg-white px-4 py-3 md:hidden">
        <nav class="flex flex-col gap-2 text-sm font-medium text-slate-700">
            <a class="rounded-lg py-2 hover:text-brand-600" href="/" @click="navOpen=false">Lojas</a>
            <a class="rounded-lg py-2 hover:text-brand-600" href="/cliente/carrinho" @click="navOpen=false">Carrinho<?= $cartCount > 0 ? ' (' . $cartCount . ')' : '' ?></a>
            <a class="rounded-lg py-2 font-semibold text-brand-600" href="/cliente/login" @click="navOpen=false">Entrar para pedir</a>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-5xl px-4 py-8">
    <?php require BASE_PATH . '/views/partials/flash.php'; ?>
    <?php require $__content_path; ?>
</main>
</body>
</html>
