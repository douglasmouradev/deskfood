<?php
declare(strict_types=1);
/** @var array<string,mixed>|null $open */
/** @var list<array<string,mixed>> $entries */
/** @var list<array<string,mixed>> $history */
/** @var string $csrf */
?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"><?= htmlspecialchars((string) $_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if ($open === null): ?>
    <form method="post" action="/operador/caixa/abrir" class="max-w-md rounded-2xl border border-slate-200 bg-white p-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <label class="text-sm font-medium">Fundo de troco inicial</label>
        <input type="number" step="0.01" name="opening_balance" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <button class="mt-3 rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Abrir caixa</button>
    </form>
<?php else: ?>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
        Caixa aberto desde <?= htmlspecialchars((string) $open['opened_at']) ?> · Fundo inicial R$ <?= number_format((float) $open['opening_balance'], 2, ',', '.') ?>
    </div>
    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <form method="post" action="/operador/caixa/sangria" class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <h3 class="font-semibold">Sangria</h3>
            <input type="number" step="0.01" name="amount" required class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Valor">
            <input name="reason" required class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Motivo">
            <button class="rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white">Registrar</button>
        </form>
        <form method="post" action="/operador/caixa/fechar" class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <h3 class="font-semibold">Fechamento</h3>
            <input type="number" step="0.01" name="closing_balance" required class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Valor contado em gaveta">
            <textarea name="note" rows="2" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Observações"></textarea>
            <button class="rounded-full bg-orange-600 px-4 py-2 text-xs font-semibold text-white">Fechar e gerar PDF</button>
        </form>
    </div>
    <div class="mt-6">
        <h3 class="font-semibold">Movimentações recentes</h3>
        <ul class="mt-2 space-y-1 text-sm">
            <?php foreach ($entries as $e): ?>
                <li class="flex justify-between rounded-lg border border-slate-100 px-3 py-2">
                    <span><?= htmlspecialchars((string) $e['entry_type']) ?> · <?= htmlspecialchars((string) ($e['reason'] ?? '')) ?></span>
                    <span class="font-semibold">R$ <?= number_format((float) $e['amount'], 2, ',', '.') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="mt-10">
    <h3 class="font-semibold">Histórico</h3>
    <ul class="mt-2 space-y-2 text-xs text-slate-600">
        <?php foreach ($history as $h): ?>
            <li class="rounded-lg border border-slate-100 px-3 py-2">
                #<?= (int) $h['id'] ?> · aberto <?= htmlspecialchars((string) $h['opened_at']) ?>
                <?php if (!empty($h['closed_at'])): ?> · fechado <?= htmlspecialchars((string) $h['closed_at']) ?><?php endif; ?>
                <?php if (!empty($h['report_path'])): ?>
                    · <a class="text-orange-600 underline" href="/operador/caixa/relatorio/<?= (int) $h['id'] ?>" target="_blank" rel="noopener">PDF</a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
