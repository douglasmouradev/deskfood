<?php
declare(strict_types=1);
/** @var string $appName */
$appName = $appName ?? 'Desk Food';
$navLinks = [
    ['href' => '/operador', 'label' => 'Pedidos'],
    ['href' => '/operador/cardapio', 'label' => 'Cardápio'],
    ['href' => '/operador/motoboys', 'label' => 'Motoboys'],
    ['href' => '/operador/caixa', 'label' => 'Caixa'],
    ['href' => '/operador/relatorios/pedidos.csv', 'label' => 'Pedidos (CSV)'],
    ['href' => '/ajuda', 'label' => 'Ajuda'],
];
?>
<nav class="flex flex-col gap-1 text-sm text-slate-700" aria-label="Operação">
    <?php foreach ($navLinks as $link): ?>
    <a class="rounded-md px-3 py-2 hover:bg-orange-50 hover:text-orange-700"
       href="<?= htmlspecialchars($link['href']) ?>"><?= htmlspecialchars($link['label']) ?></a>
    <?php endforeach; ?>
    <?php
    $action = '/operador/sair';
    $buttonClass = 'w-full rounded-md px-3 py-2 text-left hover:bg-slate-100';
    require BASE_PATH . '/views/partials/logout_form.php';
    ?>
</nav>
