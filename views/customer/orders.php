<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $orders */
?>
<h1 class="text-2xl font-bold">Meus pedidos</h1>
<?php if (!empty($_SESSION['flash_ok'])): ?><p class="mt-3 text-sm text-emerald-700"><?= htmlspecialchars((string) $_SESSION['flash_ok']) ?></p><?php unset($_SESSION['flash_ok']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?><p class="mt-3 text-sm text-red-700"><?= htmlspecialchars((string) $_SESSION['flash_error']) ?></p><?php unset($_SESSION['flash_error']); endif; ?>
<div class="mt-6 space-y-3">
    <?php foreach ($orders as $o): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-sm font-semibold text-slate-900">#<?= htmlspecialchars((string) $o['order_number']) ?> · <?= htmlspecialchars((string) $o['unit_name']) ?></p>
                    <p class="text-xs text-slate-500"><?= htmlspecialchars((string) $o['created_at']) ?></p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700"><?= htmlspecialchars((string) $o['status']) ?></span>
            </div>
            <p class="mt-2 text-sm text-slate-700">Total <span class="tabular font-semibold">R$ <?= number_format((float) $o['total'], 2, ',', '.') ?></span></p>
            <div class="mt-3 flex flex-wrap gap-2 text-sm">
                <a class="rounded-full border border-slate-200 px-3 py-1 hover:border-orange-300" href="/acompanhar/<?= htmlspecialchars((string) $o['tracking_token']) ?>">Acompanhar</a>
                <form method="post" action="/cliente/pedido/<?= (int) $o['id'] ?>/repetir" class="inline">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Helpers\Csrf::token()) ?>">
                    <button type="submit" class="rounded-full border border-orange-200 px-3 py-1 font-semibold text-orange-700 hover:bg-orange-50">Pedir novamente</button>
                </form>
                <?php if (($o['payment_method'] ?? '') === 'pix' && ($o['payment_status'] ?? '') === 'pendente'): ?>
                    <a class="rounded-full bg-orange-500 px-3 py-1 font-semibold text-white" href="/cliente/pedido/<?= (int) $o['id'] ?>/pix">Pagar PIX</a>
                <?php endif; ?>
                <?php if (($o['payment_method'] ?? '') === 'card' && ($o['payment_status'] ?? '') === 'pendente'): ?>
                    <a class="rounded-full bg-orange-500 px-3 py-1 font-semibold text-white" href="/cliente/pedido/<?= (int) $o['id'] ?>/cartao">Pagar com cartão</a>
                <?php endif; ?>
                <?php
                $onlinePaid = in_array((string) ($o['payment_method'] ?? ''), ['pix', 'card'], true)
                    && ($o['payment_status'] ?? '') === 'pago';
                ?>
                <?php if (in_array((string) ($o['status'] ?? ''), ['pendente', 'confirmado'], true) && !$onlinePaid): ?>
                    <form method="post" action="/cliente/pedido/<?= (int) $o['id'] ?>/cancelar" class="inline" onsubmit="return confirm('Cancelar este pedido?');">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Helpers\Csrf::token()) ?>">
                        <button type="submit" class="rounded-full border border-red-200 px-3 py-1 text-red-700 hover:bg-red-50">Cancelar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if ($orders === []): ?>
        <p class="text-slate-600">Você ainda não fez pedidos.</p>
    <?php endif; ?>
</div>
