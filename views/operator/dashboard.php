<?php
declare(strict_types=1);
/** @var array<string,mixed> $unit */
/** @var array{novos:list,confirmados:list,em_preparo:list,saiu:list,pendentes:list,finalizados:list} $board */
/** @var list<array<string,mixed>> $motoboys */
/** @var string $csrf */
/** @var string $boardRevision */
/** @var int $boardPollMs */

$boardPollMs = (int) ($boardPollMs ?? 0);
$boardRevision = (string) ($boardRevision ?? '');
$activeTotal = count($board['novos']) + count($board['confirmados']) + count($board['em_preparo'])
    + count($board['saiu']) + count($board['pendentes']);
$cols = [
    ['id' => 'pendentes', 'title' => 'Pendentes', 'hint' => 'PIX aguardando', 'ring' => 'ring-amber-200', 'head' => 'bg-amber-50 text-amber-950', 'badge' => 'bg-amber-700 text-white', 'list' => $board['pendentes']],
    ['id' => 'novos', 'title' => 'Novos', 'hint' => 'Aguardando confirmação', 'ring' => 'ring-slate-200', 'head' => 'bg-slate-50 text-slate-800', 'badge' => 'bg-slate-800 text-white', 'list' => $board['novos']],
    ['id' => 'confirmados', 'title' => 'Confirmados', 'hint' => 'Aceitos pela loja', 'ring' => 'ring-sky-200', 'head' => 'bg-sky-50 text-sky-950', 'badge' => 'bg-sky-700 text-white', 'list' => $board['confirmados']],
    ['id' => 'em_preparo', 'title' => 'Em preparo', 'hint' => 'Cozinha', 'ring' => 'ring-orange-200', 'head' => 'bg-orange-50 text-orange-950', 'badge' => 'bg-orange-600 text-white', 'list' => $board['em_preparo']],
    ['id' => 'saiu', 'title' => 'Saiu p/ entrega', 'hint' => 'Motoboy', 'ring' => 'ring-emerald-200', 'head' => 'bg-emerald-50 text-emerald-950', 'badge' => 'bg-emerald-600 text-white', 'list' => $board['saiu']],
];
?>
<p class="text-sm text-slate-600">Unidade: <span class="font-semibold text-slate-900"><?= htmlspecialchars((string) ($unit['name'] ?? '')) ?></span></p>

<?php if ($activeTotal === 0): ?>
    <p class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        Quadro ao vivo — pedidos aparecem nas colunas conforme o status<?= $boardPollMs > 0 ? ' (atualização a cada ' . (int) round($boardPollMs / 1000) . 's)' : '' ?>.
    </p>
<?php endif; ?>

<div id="board-live" class="mt-6">
    <?php require BASE_PATH . '/views/operator/partials/board_columns.php'; ?>
</div>

<?php if ($board['finalizados'] !== []): ?>
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <h3 class="text-sm font-bold text-slate-900">Recentes (entregue / cancelado)</h3>
        <ul class="mt-3 divide-y divide-slate-100 text-sm">
            <?php foreach (array_slice($board['finalizados'], 0, 12) as $o): ?>
                <li class="flex flex-wrap justify-between gap-2 py-2">
                    <span class="font-mono text-xs"><?= htmlspecialchars((string) $o['order_number']) ?></span>
                    <span class="text-xs uppercase"><?= htmlspecialchars((string) $o['status']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($boardPollMs > 0): ?>
<script>
(function () {
    var lastNovos = <?= (int) count($board['novos']) ?>;
    function notifyNew() {
        try { new Audio('data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YUtvT19QUk9GSUxFA').play(); } catch (e) {}
        if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
            new Notification('Desk Food', { body: 'Atualização no quadro de pedidos' });
        }
    }
    var lastRev = <?= json_encode($boardRevision, JSON_THROW_ON_ERROR) ?>;
    var boardBusy = false;
    function refreshBoard(force) {
        if (boardBusy && !force) return;
        boardBusy = true;
        fetch('/operador/api/quadro-html', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.ok || !j.html) return;
                if (!force && j.rev && j.rev === lastRev) return;
                var el = document.getElementById('board-live');
                if (el) el.innerHTML = j.html;
                if (j.rev) lastRev = j.rev;
                if (j.counts && j.counts.novos > lastNovos) {
                    lastNovos = j.counts.novos;
                    notifyNew();
                }
            }).catch(function () {}).finally(function () { boardBusy = false; });
    }
    function onBoardEvent(j) {
        if (!j || !j.rev) return;
        if (j.rev === lastRev) return;
        if (j.counts && j.counts.novos > lastNovos) notifyNew();
        refreshBoard(true);
    }
    if (typeof EventSource !== 'undefined') {
        var es = new EventSource('/operador/api/quadro-stream');
        es.addEventListener('board', function (ev) {
            try { onBoardEvent(JSON.parse(ev.data)); } catch (e) {}
        });
        es.onerror = function () { es.close(); fallbackPoll(); };
    } else {
        fallbackPoll();
    }
    function fallbackPoll() {
        var pollMs = <?= (int) $boardPollMs ?>;
        setInterval(function () {
            fetch('/operador/api/quadro-rev', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (!j || !j.ok || !j.rev || j.rev === lastRev) return;
                    onBoardEvent(j);
                }).catch(function () {});
        }, pollMs);
    }
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
        Notification.requestPermission();
    }
})();
</script>
<?php endif; ?>
