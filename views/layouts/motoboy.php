<?php
declare(strict_types=1);
/** @var string $__content_path */
$title = $title ?? 'Entregador';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="/assets/img/logo.png" type="image/png">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-full bg-slate-950 text-slate-50" style="font-family:'Plus Jakarta Sans',system-ui,sans-serif">
<div class="mx-auto max-w-lg px-4 py-6">
    <?php require $__content_path; ?>
</div>
</body>
</html>
