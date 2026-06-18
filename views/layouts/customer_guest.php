<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$appName = (string) ($config['name'] ?? 'Desk Food');
$headTitle = ($title ?? 'Cardápio') . ' · ' . $appName;
$headAlpine = false;
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
<body class="df-shell antialiased">
<header class="df-header sticky top-0 z-40">
    <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3.5">
        <a href="/" class="flex min-w-0 items-center gap-2.5 text-sm font-medium text-zinc-600 hover:text-zinc-900">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M15 18l-6-6 6-6"/></svg>
            <span class="truncate">Todas as lojas</span>
        </a>
        <nav class="flex items-center gap-2">
            <a href="/cliente/carrinho" class="relative df-btn-ghost px-4 py-2 text-xs">
                Carrinho
                <?php if ($cartCount > 0): ?>
                    <span class="ml-1 inline-flex min-w-[1.25rem] justify-center rounded-full bg-zinc-900 px-1.5 py-0.5 text-[10px] font-bold text-white"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <a class="df-btn-primary hidden px-4 py-2 text-xs sm:inline-flex" href="/cliente/login">Entrar</a>
            <button type="button" class="rounded-lg border border-zinc-200 p-2 text-zinc-700 hover:bg-zinc-50 sm:hidden" data-df-nav-toggle aria-expanded="false" aria-controls="nav-guest-mobile">
                <span class="sr-only">Menu</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </nav>
    </div>
    <div id="nav-guest-mobile" class="border-t border-zinc-100 bg-white px-4 py-3 sm:hidden" hidden>
        <a class="block rounded-lg py-2 text-sm font-semibold text-zinc-900" href="/cliente/login">Entrar para finalizar pedido</a>
    </div>
</header>
<main class="df-main">
    <?php require BASE_PATH . '/views/partials/flash.php'; ?>
    <?php require $__content_path; ?>
</main>
<script src="/assets/js/df-nav.js"></script>
</body>
</html>
