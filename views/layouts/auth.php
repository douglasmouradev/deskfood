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
<body class="df-shell antialiased">
<div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-12">
    <a href="/" class="mb-10 flex flex-col items-center gap-3 text-center">
        <img src="/assets/img/logo.png" alt="<?= htmlspecialchars($appName) ?>" class="h-11 w-auto">
        <span class="text-sm font-medium text-zinc-500"><?= htmlspecialchars($appName) ?></span>
    </a>
    <?php require BASE_PATH . '/views/partials/flash.php'; ?>
    <?php require $__content_path; ?>
    <p class="mt-10 text-center text-xs text-zinc-400">
        <a href="/" class="hover:text-zinc-700">Início</a>
        <span class="mx-2">·</span>
        <a href="/landing" class="hover:text-zinc-700">Para restaurantes</a>
    </p>
</div>
</body>
</html>
