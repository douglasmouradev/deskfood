<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$appName = (string) ($config['name'] ?? 'Desk Food');
$headTitle = ($title ?? 'Admin') . ' — ' . $appName;
$headRobots = 'noindex, nofollow';
$headAlpine = true;
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <?php require BASE_PATH . '/views/partials/head.php'; ?>
</head>
<body class="min-h-full bg-slate-100 antialiased" x-data="{ navOpen: false }">
<div class="flex min-h-screen">
    <aside class="hidden w-64 flex-col border-r border-slate-200 bg-slate-900 p-6 text-slate-100 md:flex">
        <div class="mb-8 flex items-center gap-2">
            <img src="/assets/img/logo.png" class="h-9 w-auto brightness-0 invert" alt="">
            <span class="font-semibold">Dono</span>
        </div>
        <?php require BASE_PATH . '/views/partials/nav_admin.php'; ?>
    </aside>
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3 md:px-6 md:py-4">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" class="rounded-lg border border-slate-200 p-2 text-slate-700 hover:bg-slate-50 md:hidden" @click="navOpen = !navOpen" aria-controls="nav-admin-mobile">
                    <span class="sr-only">Menu</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="truncate text-lg font-semibold text-slate-900"><?= htmlspecialchars($title ?? 'Admin') ?></h1>
            </div>
        </header>
        <div x-cloak x-show="navOpen" x-transition class="border-b border-slate-800 bg-slate-900 px-4 py-3 text-slate-100 md:hidden">
            <?php require BASE_PATH . '/views/partials/nav_admin.php'; ?>
        </div>
        <main class="flex-1 p-4 md:p-6">
            <?php require BASE_PATH . '/views/partials/flash.php'; ?>
            <?php if (!empty($_SESSION['show_onboarding_admin'])): ?>
                <?php require BASE_PATH . '/views/partials/onboarding_admin.php'; ?>
            <?php endif; ?>
            <?php require $__content_path; ?>
        </main>
    </div>
</div>
</body>
</html>
