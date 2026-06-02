<?php
declare(strict_types=1);
/** @var string $__content_path */
$headTitle = $title ?? 'Entregador';
$headRobots = 'noindex, nofollow';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <?php require BASE_PATH . '/views/partials/head.php'; ?>
</head>
<body class="min-h-full bg-slate-950 text-slate-50 antialiased">
<div class="mx-auto max-w-lg px-4 py-6">
    <?php require BASE_PATH . '/views/partials/flash.php'; ?>
    <?php require $__content_path; ?>
</div>
</body>
</html>
