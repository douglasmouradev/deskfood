<?php
declare(strict_types=1);
/** @var array<string,mixed> $order */
/** @var string $token */
$steps = [
    ['key' => 'pendente', 'label' => 'Pedido recebido'],
    ['key' => 'confirmado', 'label' => 'Confirmado pela loja'],
    ['key' => 'em_preparo', 'label' => 'Em preparo'],
    ['key' => 'saiu_entrega', 'label' => 'Saiu para entrega'],
    ['key' => 'entregue', 'label' => 'Entregue'],
];
$current = (string) ($order['status'] ?? 'pendente');
$idx = 0;
foreach ($steps as $i => $s) {
    if ($s['key'] === $current) {
        $idx = $i;
        break;
    }
}
$waPhone = preg_replace('/\D+/', '', (string) ($order['unit_phone'] ?? ''));
if (str_starts_with($waPhone, '55') === false && strlen($waPhone) >= 10) {
    $waPhone = '55' . $waPhone;
}
?>
<div class="mx-auto max-w-3xl rounded-3xl border border-orange-100 bg-white p-8 shadow-xl">
    <h1 class="text-2xl font-bold text-slate-900">Acompanhe seu pedido</h1>
    <p class="mt-2 text-sm text-slate-600">Código <?= htmlspecialchars((string) $order['order_number']) ?> · <?= htmlspecialchars((string) ($order['unit_name'] ?? '')) ?></p>

    <?php if (($order['payment_method'] ?? '') === 'pix'): ?>
        <p class="mt-2 text-xs text-slate-500">Pagamento: <span class="font-semibold uppercase"><?= htmlspecialchars((string) $order['payment_status']) ?></span></p>
    <?php endif; ?>

    <ol class="mt-8 space-y-4">
        <?php foreach ($steps as $i => $step): ?>
            <?php
            $done = $i <= $idx;
            $active = $i === $idx;
            ?>
            <li class="flex gap-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold <?= $done ? 'bg-orange-500 text-white' : 'bg-slate-100 text-slate-400' ?>"><?= $i + 1 ?></span>
                <div>
                    <p class="text-sm font-semibold <?= $active ? 'text-orange-600' : 'text-slate-700' ?>"><?= htmlspecialchars($step['label']) ?></p>
                    <?php if ($active): ?>
                        <p class="text-xs text-slate-500">Status atual</p>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>

    <?php if (!empty($order['motoboy_name'])): ?>
        <div class="mt-6 flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
            <div>
                <p class="text-xs uppercase text-slate-500">Entregador</p>
                <p class="font-semibold text-slate-900"><?= htmlspecialchars((string) $order['motoboy_name']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($waPhone !== ''): ?>
        <a href="https://wa.me/<?= htmlspecialchars($waPhone) ?>?text=<?= rawurlencode('Olá! Tenho dúvida sobre o pedido ' . ($order['order_number'] ?? '')) ?>"
           class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700"
           target="_blank" rel="noopener">Falar com a loja no WhatsApp</a>
    <?php endif; ?>

    <?php if (!empty($flash_ok)): ?>
        <p class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800"><?= htmlspecialchars((string) $flash_ok) ?></p>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <p class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800"><?= htmlspecialchars((string) $flash_error) ?></p>
    <?php endif; ?>

    <?php if ($current === 'entregue'): ?>
        <div class="mt-8 border-t border-slate-100 pt-6">
            <?php if (!empty($rating)): ?>
                <p class="text-sm text-slate-600">Sua avaliação: <strong><?= (int) $rating['stars'] ?> estrelas</strong></p>
                <?php if (!empty($rating['comment'])): ?>
                    <p class="mt-1 text-sm italic text-slate-500">"<?= htmlspecialchars((string) $rating['comment']) ?>"</p>
                <?php endif; ?>
            <?php else: ?>
                <h2 class="text-sm font-semibold text-slate-900">Como foi seu pedido?</h2>
                <form method="post" action="/acompanhar/<?= htmlspecialchars($token) ?>/avaliar" class="mt-3 space-y-3">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) ($csrf ?? '')) ?>">
                    <div class="flex gap-2">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="stars" value="<?= $s ?>" class="peer sr-only" required>
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-lg peer-checked:border-amber-400 peer-checked:bg-amber-50">★</span>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <textarea name="comment" rows="2" maxlength="500" placeholder="Comentário (opcional)" class="w-full rounded-xl border px-3 py-2 text-sm"></textarea>
                    <button type="submit" class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Enviar avaliação</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<script>
    const token = <?= json_encode($token, JSON_THROW_ON_ERROR) ?>;
    let lastStatus = <?= json_encode($order['status'], JSON_THROW_ON_ERROR) ?>;
    async function poll() {
        try {
            const res = await fetch('/api/pedido/' + encodeURIComponent(token) + '/status', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data && data.ok && data.status && data.status !== lastStatus) {
                location.reload();
            }
        } catch (e) {}
    }
    setInterval(poll, 12000);
</script>
