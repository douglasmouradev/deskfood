<?php
declare(strict_types=1);
/** @var string $__content_path */
$config = require BASE_PATH . '/config/app.php';
$appName = (string) ($config['name'] ?? 'Desk Food');
$title = $title ?? 'Operador';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($title) ?> — <?= htmlspecialchars($appName) ?></title>
    <link rel="icon" href="/assets/img/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-full bg-slate-50" style="font-family:'Plus Jakarta Sans',system-ui,sans-serif">
<div class="flex min-h-screen">
    <aside class="hidden w-60 flex-col border-r border-slate-200 bg-white p-5 md:flex">
        <div class="mb-6 flex items-center gap-2">
            <img src="/assets/img/logo.png" class="h-8 w-auto" alt="<?= htmlspecialchars($appName) ?>">
            <div class="leading-tight">
                <span class="block text-xs font-semibold text-orange-600"><?= htmlspecialchars($appName) ?></span>
                <span class="text-sm font-semibold text-slate-900">Operação</span>
            </div>
        </div>
        <nav class="flex flex-col gap-1 text-sm text-slate-700">
            <a class="rounded-md px-3 py-2 hover:bg-orange-50 hover:text-orange-700" href="/operador">Pedidos</a>
            <a class="rounded-md px-3 py-2 hover:bg-orange-50 hover:text-orange-700" href="/operador/cardapio">Cardápio</a>
            <a class="rounded-md px-3 py-2 hover:bg-orange-50 hover:text-orange-700" href="/operador/motoboys">Motoboys</a>
            <a class="rounded-md px-3 py-2 hover:bg-orange-50 hover:text-orange-700" href="/operador/caixa">Caixa</a>
            <a class="rounded-md px-3 py-2 hover:bg-orange-50 hover:text-orange-700" href="/operador/relatorios/pedidos.csv">Pedidos (CSV)</a>
            <a class="rounded-md px-3 py-2 hover:bg-orange-50 hover:text-orange-700" href="/ajuda">Ajuda</a>
            <a class="rounded-md px-3 py-2 hover:bg-slate-100" href="/operador/sair">Sair</a>
        </nav>
    </aside>
    <main class="flex-1">
        <header class="border-b border-slate-200 bg-white px-6 py-4">
            <h1 class="text-base font-semibold text-slate-900"><?= htmlspecialchars($title) ?></h1>
        </header>
        <div class="p-6">
            <?php if (!empty($_SESSION['show_onboarding_operator'])): ?>
                <?php require BASE_PATH . '/views/partials/onboarding_operator.php'; ?>
            <?php endif; ?>
            <?php require $__content_path; ?>
        </div>
    </main>
</div>
</body>
</html>
