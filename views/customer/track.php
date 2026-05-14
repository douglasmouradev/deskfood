<?php
declare(strict_types=1);
/** @var array<string,mixed> $order */
/** @var string $token */
$steps = ['pendente' => 1, 'confirmado' => 2, 'em_preparo' => 3, 'saiu_entrega' => 4, 'entregue' => 5];
$current = $steps[(string) $order['status']] ?? 1;
?>
<div class="mx-auto max-w-3xl rounded-3xl border border-orange-100 bg-white p-8 shadow-xl">
    <h1 class="text-2xl font-bold text-slate-900">Acompanhe seu pedido</h1>
    <p class="mt-2 text-sm text-slate-600">Código <?= htmlspecialchars((string) $order['order_number']) ?></p>
    <div class="mt-6">
        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-gradient-to-r from-orange-400 to-red-500" style="width: <?= (int) ($current * 20) ?>%"></div>
        </div>
        <p class="mt-3 text-sm font-semibold text-slate-800">Status atual: <span class="text-orange-600"><?= htmlspecialchars((string) $order['status']) ?></span></p>
        <?php if (!empty($order['motoboy_name'])): ?>
            <div class="mt-4 flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                <?php if (!empty($order['motoboy_photo'])): ?>
                    <img src="/<?= htmlspecialchars(ltrim((string) $order['motoboy_photo'], '/')) ?>" class="h-12 w-12 rounded-full object-cover" alt="">
                <?php endif; ?>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Entregador</p>
                    <p class="font-semibold text-slate-900"><?= htmlspecialchars((string) $order['motoboy_name']) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    const token = <?= json_encode($token, JSON_THROW_ON_ERROR) ?>;
    let lastStatus = <?= json_encode($order['status'], JSON_THROW_ON_ERROR) ?>;
    async function poll() {
        try {
            const res = await fetch('/api/pedido/' + encodeURIComponent(token) + '/status', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data && data.ok && data.status && data.status !== lastStatus) {
                lastStatus = data.status;
                location.reload();
            }
        } catch (e) {}
    }
    setInterval(poll, 15000);
</script>
