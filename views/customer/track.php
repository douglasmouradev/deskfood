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
$showLiveMap = $current === 'saiu_entrega' && ($order['delivery_type'] ?? 'delivery') === 'delivery';
/** @var array{lat: float, lng: float}|null $map_destination */
$mapDestination = $map_destination ?? null;
/** @var string $google_maps_api_key */
$googleMapsKey = (string) ($google_maps_api_key ?? '');
?>
<?php if ($showLiveMap): ?>
<style>
    #track-map { min-height: 18rem; width: 100%; border-radius: 0 0 1rem 1rem; }
</style>
<?php endif; ?>
<div class="df-card mx-auto max-w-3xl p-8">
    <p class="df-eyebrow">Rastreio</p>
    <h1 class="font-display mt-2 text-2xl font-semibold text-zinc-900">Pedido <?= htmlspecialchars((string) $order['order_number']) ?></h1>
    <p class="mt-1 text-sm text-zinc-600"><?= htmlspecialchars((string) ($order['unit_name'] ?? '')) ?></p>

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
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold <?= $done ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-400' ?>"><?= $i + 1 ?></span>
                <div>
                    <p class="text-sm font-semibold <?= $active ? 'text-zinc-900' : 'text-zinc-600' ?>"><?= htmlspecialchars($step['label']) ?></p>
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

    <?php if ($showLiveMap): ?>
        <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200">
            <div class="flex items-center justify-between border-b border-zinc-100 bg-zinc-50 px-4 py-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Entregador no mapa</p>
                <p id="track-map-status" class="text-xs text-zinc-500">Carregando…</p>
            </div>
            <div id="track-map" class="h-72 w-full bg-zinc-100" role="img" aria-label="Mapa com posição do entregador"></div>
            <p class="border-t border-zinc-100 px-4 py-2 text-xs text-zinc-500">Abra o link do motoboy no celular e permita o GPS. Em <strong>localhost</strong>, o navegador pode bloquear localização — use o IP da rede (ex.: 192.168.x.x:8080) ou HTTPS.</p>
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
<?php if ($showLiveMap): ?>
<script>
window.__trackMapConfig = <?= json_encode([
    'token' => $token,
    'lastStatus' => $order['status'],
    'showLiveMap' => $showLiveMap,
    'mapDestination' => $mapDestination,
    'googleMapsKey' => $googleMapsKey,
], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/assets/js/track-map.js"></script>
<?php if ($googleMapsKey !== ''): ?>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($googleMapsKey) ?>&amp;callback=dfInitTrackMap&amp;loading=async"></script>
<?php endif; ?>
<?php else: ?>
<script>
    const token = <?= json_encode($token, JSON_THROW_ON_ERROR) ?>;
    let lastStatus = <?= json_encode($order['status'], JSON_THROW_ON_ERROR) ?>;
    const terminal = ['entregue', 'cancelado'];

    async function poll() {
        if (terminal.includes(lastStatus)) return;
        try {
            const res = await fetch('/api/pedido/' + encodeURIComponent(token) + '/status', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data && data.ok && data.status && data.status !== lastStatus) {
                location.reload();
            }
        } catch (e) {}
    }
    if (!terminal.includes(lastStatus)) {
        setInterval(poll, 12000);
    }
</script>
<?php endif; ?>
