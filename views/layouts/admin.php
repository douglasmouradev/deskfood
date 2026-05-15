<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$title = $title ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="/assets/img/logo.png" type="image/png">
    <title><?= htmlspecialchars($title) ?> — <?= htmlspecialchars((string) ($config['name'] ?? 'Desk Food')) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-full bg-slate-100" style="font-family:'Plus Jakarta Sans',system-ui,sans-serif">
<div class="flex min-h-screen">
    <aside class="hidden w-64 flex-col border-r border-slate-200 bg-slate-900 p-6 text-slate-100 md:flex">
        <div class="mb-8 flex items-center gap-2">
            <img src="/assets/img/logo.png" class="h-9 w-auto brightness-0 invert" alt="">
            <span class="font-semibold">Dono</span>
        </div>
        <nav class="flex flex-col gap-2 text-sm">
            <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="/admin">Dashboard</a>
            <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="/admin/unidades">Unidades</a>
            <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="/admin/operadores">Operadores</a>
            <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="/admin/leads">Leads</a>
            <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="/admin/cupons">Cupons</a>
            <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="/admin/relatorios">Relatórios</a>
            <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="/admin/auditoria">Auditoria</a>
            <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="/ajuda">Ajuda</a>
            <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="/admin/sair">Sair</a>
        </nav>
    </aside>
    <div class="flex-1">
        <header class="border-b border-slate-200 bg-white px-6 py-4">
            <h1 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($title) ?></h1>
        </header>
        <div class="p-6">
            <?php if (!empty($_SESSION['show_onboarding_admin'])): ?>
                <?php require BASE_PATH . '/views/partials/onboarding_admin.php'; ?>
            <?php endif; ?>
            <?php require $__content_path; ?>
        </div>
    </div>
</div>
</body>
</html>
