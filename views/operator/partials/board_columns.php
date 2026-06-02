<?php
declare(strict_types=1);
/** @var array{novos:list,confirmados:list,em_preparo:list,saiu:list,pendentes:list} $board */
/** @var list<array<string,mixed>> $motoboys */
/** @var string $csrf */
$cols = [
    ['id' => 'pendentes', 'title' => 'Pendentes', 'hint' => 'PIX aguardando', 'ring' => 'ring-amber-200', 'head' => 'bg-amber-50 text-amber-950', 'badge' => 'bg-amber-700 text-white', 'list' => $board['pendentes']],
    ['id' => 'novos', 'title' => 'Novos', 'hint' => 'Aguardando confirmação', 'ring' => 'ring-slate-200', 'head' => 'bg-slate-50 text-slate-800', 'badge' => 'bg-slate-800 text-white', 'list' => $board['novos']],
    ['id' => 'confirmados', 'title' => 'Confirmados', 'hint' => 'Aceitos pela loja', 'ring' => 'ring-sky-200', 'head' => 'bg-sky-50 text-sky-950', 'badge' => 'bg-sky-700 text-white', 'list' => $board['confirmados']],
    ['id' => 'em_preparo', 'title' => 'Em preparo', 'hint' => 'Cozinha', 'ring' => 'ring-orange-200', 'head' => 'bg-orange-50 text-orange-950', 'badge' => 'bg-orange-600 text-white', 'list' => $board['em_preparo']],
    ['id' => 'saiu', 'title' => 'Saiu p/ entrega', 'hint' => 'Motoboy', 'ring' => 'ring-emerald-200', 'head' => 'bg-emerald-50 text-emerald-950', 'badge' => 'bg-emerald-600 text-white', 'list' => $board['saiu']],
];
?>
<div class="board-scroll">
    <?php foreach ($cols as $col): ?>
        <section class="flex min-h-[260px] min-w-0 flex-1 flex-col rounded-2xl border border-slate-200 bg-slate-50/80 ring-1 <?= htmlspecialchars($col['ring']) ?>" data-board-col="<?= htmlspecialchars($col['id']) ?>">
            <header class="flex shrink-0 items-start justify-between gap-2 rounded-t-2xl border-b border-slate-200/80 px-3 py-2.5 <?= htmlspecialchars($col['head']) ?>">
                <div>
                    <h2 class="text-xs font-bold"><?= htmlspecialchars($col['title']) ?></h2>
                    <p class="text-[10px] opacity-80"><?= htmlspecialchars($col['hint']) ?></p>
                </div>
                <span class="board-col-count tabular flex h-6 min-w-[1.5rem] items-center justify-center rounded-full px-1.5 text-[10px] font-bold <?= htmlspecialchars($col['badge']) ?>"><?= count($col['list']) ?></span>
            </header>
            <div class="board-col-body flex flex-1 flex-col gap-2 p-2">
                <?php if ($col['list'] === []): ?>
                    <p class="flex flex-1 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white/60 px-2 py-6 text-center text-[10px] text-slate-500">Vazio</p>
                <?php else: ?>
                    <?php foreach ($col['list'] as $order): ?>
                        <?php require BASE_PATH . '/views/operator/partials/order_kanban_card.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
