<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$appName = (string) ($config['name'] ?? 'Desk Food');
$headTitle = $title ?? 'Entrar';
$headRobots = 'noindex, nofollow';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <?php require BASE_PATH . '/views/partials/head.php'; ?>
</head>
<body class="min-h-full bg-gradient-to-b from-orange-50/80 to-slate-50 text-slate-900 antialiased">
<div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-12">
    <a href="/" class="mb-8 flex flex-col items-center gap-3 text-center">
        <img src="/assets/img/logo.png" alt="" class="h-12 w-auto">
        <span class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($appName) ?></span>
    </a>
    <?php require BASE_PATH . '/views/partials/flash.php'; ?>
    <?php require $__content_path; ?>
    <p class="mt-8 text-center text-xs text-slate-500">
        <a href="/" class="hover:text-brand-600">Voltar ao início</a>
        · <a href="/landing" class="hover:text-brand-600">Conhecer a plataforma</a>
    </p>
</div>
</body>
</html>
