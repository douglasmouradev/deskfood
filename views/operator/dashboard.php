<?php
declare(strict_types=1);
/** @var array<string,mixed> $unit */
/** @var array{novos: list<array<string,mixed>>, prontos: list<array<string,mixed>>, saiu: list<array<string,mixed>>, pendentes: list<array<string,mixed>>, finalizados: list<array<string,mixed>>} $board */
/** @var list<array<string,mixed>> $motoboys */
/** @var string $csrf */
/** @var string $boardRevision */
/** @var int $boardPollMs */

$boardPollMs = (int) ($boardPollMs ?? 0);
$boardRevision = (string) ($boardRevision ?? '');
$activeTotal = count($board['novos']) + count($board['prontos']) + count($board['saiu']) + count($board['pendentes']);
$cols = [
    [
        'id' => 'novos',
        'title' => 'Novos pedidos',
        'hint' => 'Aguardando confirmação da loja',
        'ring' => 'ring-slate-200',
        'head' => 'bg-slate-50 text-slate-800',
        'badge' => 'bg-slate-800 text-white',
        'list' => $board['novos'],
    ],
    [
        'id' => 'prontos',
        'title' => 'Prontos',
        'hint' => 'Confirmados e em preparo',
        'ring' => 'ring-orange-200',
        'head' => 'bg-orange-50 text-orange-950',
        'badge' => 'bg-orange-600 text-white',
        'list' => $board['prontos'],
    ],
    [
        'id' => 'saiu',
        'title' => 'Saiu para entrega',
        'hint' => 'Com motoboy a caminho',
        'ring' => 'ring-emerald-200',
        'head' => 'bg-emerald-50 text-emerald-950',
        'badge' => 'bg-emerald-600 text-white',
        'list' => $board['saiu'],
    ],
    [
        'id' => 'pendentes',
        'title' => 'Pendentes',
        'hint' => 'PIX aguardando confirmação',
        'ring' => 'ring-amber-200',
        'head' => 'bg-amber-50 text-amber-950',
        'badge' => 'bg-amber-700 text-white',
        'list' => $board['pendentes'],
    ],
];
?>
<p class="text-sm text-slate-600">Unidade: <span class="font-semibold text-slate-900"><?= htmlspecialchars((string) ($unit['name'] ?? '')) ?></span></p>

<?php if ($activeTotal === 0): ?>
    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        <strong class="text-slate-800">Quadro de pedidos</strong> — Os quatro painéis abaixo mostram sempre o fluxo: novos → preparo → rota → PIX pendente. Quando chegar pedido, o cartão aparece na coluna correspondente.
    </div>
<?php endif; ?>

<div class="mt-6 flex flex-col gap-4 xl:flex-row xl:items-stretch xl:overflow-x-auto xl:pb-2">
    <?php foreach ($cols as $col): ?>
        <section class="flex min-h-[280px] min-w-0 flex-1 flex-col rounded-2xl border border-slate-200 bg-slate-50/80 ring-1 <?= htmlspecialchars($col['ring']) ?> xl:min-w-[240px]" aria-labelledby="col-<?= htmlspecialchars($col['id']) ?>">
            <header class="flex shrink-0 items-start justify-between gap-2 rounded-t-2xl border-b border-slate-200/80 px-4 py-3 <?= htmlspecialchars($col['head']) ?>">
                <div>
                    <h2 id="col-<?= htmlspecialchars($col['id']) ?>" class="text-sm font-bold"><?= htmlspecialchars($col['title']) ?></h2>
                    <p class="mt-0.5 text-[11px] font-medium opacity-80"><?= htmlspecialchars($col['hint']) ?></p>
                </div>
                <span class="tabular flex h-7 min-w-[1.75rem] items-center justify-center rounded-full px-2 text-xs font-bold <?= htmlspecialchars($col['badge']) ?>"><?= count($col['list']) ?></span>
            </header>
            <div class="flex flex-1 flex-col gap-3 p-3">
                <?php if ($col['list'] === []): ?>
                    <p class="flex flex-1 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white/60 px-3 py-8 text-center text-xs text-slate-500">Nenhum pedido nesta etapa</p>
                <?php else: ?>
                    <?php foreach ($col['list'] as $order): ?>
                        <?php require BASE_PATH . '/views/operator/partials/order_kanban_card.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<?php if ($activeTotal === 0): ?>
    <div class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center">
        <p class="text-sm font-semibold text-slate-800">Nenhum pedido ativo no momento</p>
        <p class="mt-1 text-xs text-slate-500">Clientes que pedirem pela vitrine aparecerão primeiro em <strong>Novos pedidos</strong> ou em <strong>Pendentes</strong> se o PIX ainda não tiver sido pago.</p>
        <div class="mt-5 flex flex-wrap justify-center gap-3">
            <a href="/operador/cardapio" class="inline-flex rounded-full bg-orange-500 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-600">Montar cardápio</a>
            <a href="/ajuda" class="inline-flex rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Central de ajuda</a>
        </div>
    </div>
<?php endif; ?>

<?php if ($board['finalizados'] !== []): ?>
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <h3 class="text-sm font-bold text-slate-900">Recentes (entregue / cancelado)</h3>
        <ul class="mt-3 divide-y divide-slate-100 text-sm">
            <?php foreach (array_slice($board['finalizados'], 0, 12) as $o): ?>
                <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <span class="font-mono text-xs text-slate-600"><?= htmlspecialchars((string) $o['order_number']) ?></span>
                    <span class="font-medium text-slate-800"><?= htmlspecialchars((string) $o['customer_name']) ?></span>
                    <span class="text-xs uppercase text-slate-500"><?= htmlspecialchars((string) $o['status']) ?></span>
                    <span class="text-xs font-semibold text-orange-600">R$ <?= number_format((float) $o['total'], 2, ',', '.') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($boardPollMs > 0): ?>
    <p class="mt-4 text-center text-[11px] text-slate-400" role="status" aria-live="polite">Atualização automática do quadro a cada <?= (int) round($boardPollMs / 1000) ?>s quando houver mudanças.</p>
    <script>
    (function () {
        var pollMs = <?= (int) $boardPollMs ?>;
        var last = <?= json_encode($boardRevision, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_THROW_ON_ERROR) ?>;
        setInterval(function () {
            fetch('/operador/api/quadro-rev', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j && j.ok && j.rev && j.rev !== last) {
                        last = j.rev;
                        window.location.reload();
                    }
                })
                .catch(function () {});
        }, pollMs);
    })();
    </script>
<?php endif; ?>
