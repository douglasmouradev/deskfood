<?php
declare(strict_types=1);
/** @var array<string,mixed> $motoboy */
/** @var list<array<string,mixed>> $deliveries */
/** @var string $token */
/** @var string $csrf */
$activeDeliveries = array_values(array_filter(
    $deliveries,
    static fn (array $d): bool => ($d['delivery_status'] ?? '') === 'out_for_delivery'
));
?>
<h1 class="text-xl font-bold">Olá, <?= htmlspecialchars((string) $motoboy['name']) ?></h1>
<p class="mt-2 text-sm text-slate-300">Entregas de hoje</p>

<?php if ($activeDeliveries !== []): ?>
    <div class="mt-4 rounded-2xl border border-emerald-800/60 bg-emerald-950/40 p-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Rastreamento ativo</p>
        <p class="mt-1 text-xs text-slate-300">Sua localização é compartilhada com o cliente apenas durante a entrega em rota.</p>
        <button type="button" id="motoboy-gps-start" class="mt-3 w-full rounded-full bg-emerald-500 py-2.5 text-sm font-semibold text-slate-950">
            Compartilhar minha localização
        </button>
        <p id="motoboy-gps-status" class="mt-2 text-xs text-amber-400">Toque no botão acima para permitir o GPS</p>
    </div>
<?php endif; ?>

<div class="mt-6 space-y-4">
    <?php foreach ($deliveries as $d): ?>
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4" data-delivery-id="<?= (int) $d['delivery_id'] ?>">
            <p class="text-xs uppercase text-slate-400">Pedido #<?= htmlspecialchars((string) $d['order_number']) ?></p>
            <p class="mt-2 text-sm text-slate-100"><?= htmlspecialchars((string) $d['delivery_street']) ?>, <?= htmlspecialchars((string) $d['delivery_number']) ?> - <?= htmlspecialchars((string) $d['delivery_neighborhood']) ?></p>
            <a class="mt-2 inline-flex text-sm font-semibold text-orange-400" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode((string) $d['delivery_street'] . ' ' . (string) $d['delivery_number'] . ' ' . (string) $d['delivery_city']) ?>">Abrir no Maps</a>
            <?php if (($d['delivery_status'] ?? '') === 'out_for_delivery'): ?>
                <p class="mt-2 text-xs text-emerald-400">Em rota — localização visível ao cliente</p>
            <?php endif; ?>
            <?php if (($d['delivery_status'] ?? '') !== 'delivered'): ?>
                <form method="post" action="/m/<?= htmlspecialchars($token) ?>/entregue" class="mt-3">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="delivery_id" value="<?= (int) $d['delivery_id'] ?>">
                    <button class="w-full rounded-full bg-emerald-500 py-2 text-sm font-semibold text-slate-950">Marcar entregue</button>
                </form>
            <?php else: ?>
                <p class="mt-3 text-xs text-emerald-400">Entregue</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if ($deliveries === []): ?>
        <p class="text-sm text-slate-400">Sem entregas por enquanto.</p>
    <?php endif; ?>
</div>

<?php if ($activeDeliveries !== []): ?>
<script>
window.__motoboyTrack = <?= json_encode([
    'token' => $token,
    'csrf' => $csrf,
    'deliveries' => array_map(static fn (array $d): array => [
        'delivery_id' => (int) $d['delivery_id'],
        'delivery_status' => (string) ($d['delivery_status'] ?? ''),
    ], $activeDeliveries),
], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/assets/js/motoboy-location.js" defer></script>
<?php endif; ?>
