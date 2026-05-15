<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$title = $title ?? ($config['name'] ?? 'Desk Food');
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="/assets/img/logo.png" type="image/png">
    <title><?= htmlspecialchars($title) ?> · Cliente</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { 500: '#f97316', 600: '#ea580c' }, ink: { 900: '#0f172a' } }, fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'] } } } };
    </script>
</head>
<body class="min-h-full bg-gradient-to-b from-orange-50 to-white text-slate-900">
<header class="border-b border-orange-100 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
        <a href="/" class="flex items-center gap-3">
            <img src="/assets/img/logo.png" alt="" class="h-9 w-auto">
            <span class="font-semibold">Desk Food</span>
        </a>
        <nav class="flex items-center gap-4 text-sm font-medium text-slate-600">
            <a class="hover:text-brand-600" href="/cliente/pedidos">Meus pedidos</a>
            <a class="hover:text-brand-600" href="/cliente/enderecos">Endereços</a>
            <a class="hover:text-brand-600" href="/cliente/lgpd">Privacidade (LGPD)</a>
            <a class="rounded-full bg-brand-500 px-3 py-1 text-white hover:bg-brand-600" href="/cliente/sair">Sair</a>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-5xl px-4 py-8">
    <?php require $__content_path; ?>
</main>
</body>
</html>
