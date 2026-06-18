<?php
declare(strict_types=1);
$navLinks = [
    ['href' => '/admin', 'label' => 'Dashboard'],
    ['href' => '/admin/unidades', 'label' => 'Unidades'],
    ['href' => '/admin/operadores', 'label' => 'Operadores'],
    ['href' => '/admin/leads', 'label' => 'Leads'],
    ['href' => '/admin/cupons', 'label' => 'Cupons'],
    ['href' => '/admin/relatorios', 'label' => 'Relatórios'],
    ['href' => '/admin/auditoria', 'label' => 'Auditoria'],
    ['href' => '/ajuda', 'label' => 'Ajuda'],
];
?>
<nav class="flex flex-col gap-2 text-sm" aria-label="Administração">
    <?php foreach ($navLinks as $link): ?>
    <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="<?= htmlspecialchars($link['href']) ?>"><?= htmlspecialchars($link['label']) ?></a>
    <?php endforeach; ?>
    <?php
    $action = '/admin/sair';
    $buttonClass = 'w-full rounded-lg px-3 py-2 text-left hover:bg-white/10';
    require BASE_PATH . '/views/partials/logout_form.php';
    ?>
</nav>
