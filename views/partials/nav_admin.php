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
    ['href' => '/admin/sair', 'label' => 'Sair'],
];
?>
<nav class="flex flex-col gap-2 text-sm" aria-label="Administração">
    <?php foreach ($navLinks as $link): ?>
    <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="<?= htmlspecialchars($link['href']) ?>"><?= htmlspecialchars($link['label']) ?></a>
    <?php endforeach; ?>
</nav>
