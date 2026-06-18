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
?>
<p class="text-sm text-slate-600">Unidade: <span class="font-semibold text-slate-900"><?= htmlspecialchars((string) ($unit['name'] ?? '')) ?></span></p>

<?php if ($activeTotal === 0): ?>
    <p class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        Quadro ao vivo — pedidos aparecem nas colunas conforme o status<?= $boardPollMs > 0 ? ' (atualização a cada ' . (int) round($boardPollMs / 1000) . 's)' : '' ?>.
    </p>
<?php endif; ?>

<div class="mt-4 flex items-center justify-end gap-2 md:hidden">
    <button type="button" class="board-view-toggle rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700" data-board-view="kanban" aria-pressed="true">Colunas</button>
    <button type="button" class="board-view-toggle rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700" data-board-view="list" aria-pressed="false">Lista</button>
</div>

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
                bindBoardViewToggles();
            }).catch(function () {}).finally(function () { boardBusy = false; });
    }
    function onBoardEvent(j) {
        if (!j || !j.rev) return;
        if (j.rev === lastRev) return;
        if (j.counts && j.counts.novos > lastNovos) notifyNew();
        refreshBoard(true);
    }
    var pollMs = <?= (int) $boardPollMs ?>;
    setInterval(function () {
        fetch('/operador/api/quadro-rev', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.ok || !j.rev || j.rev === lastRev) return;
                onBoardEvent(j);
            }).catch(function () {});
    }, pollMs);
    function bindBoardViewToggles() {
        document.querySelectorAll('.board-view-toggle').forEach(function (btn) {
            btn.onclick = function () {
                var view = btn.getAttribute('data-board-view');
                var scroll = document.querySelector('#board-live .board-scroll');
                if (!scroll) return;
                scroll.classList.toggle('board-scroll--list', view === 'list');
                document.querySelectorAll('.board-view-toggle').forEach(function (b) {
                    b.setAttribute('aria-pressed', (b === btn).toString());
                });
            };
        });
    }
    bindBoardViewToggles();
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
        Notification.requestPermission();
    }
})();
</script>
<?php endif; ?>
