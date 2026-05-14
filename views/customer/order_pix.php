<?php
declare(strict_types=1);
/** @var array<string,mixed> $order */
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode((string) ($order['copy_paste'] ?? ''));
?>
<h1 class="text-2xl font-bold">Pagamento PIX</h1>
<p class="mt-2 text-sm text-slate-600">Pedido #<?= htmlspecialchars((string) $order['order_number']) ?> · expira em <?= htmlspecialchars((string) ($order['expires_at'] ?? '')) ?></p>
<div class="mt-6 grid gap-6 md:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center">
        <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR Code PIX" class="mx-auto h-56 w-56">
        <p class="mt-2 text-xs text-slate-500">Escaneie com o app do seu banco</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <p class="text-sm font-semibold text-slate-900">Copia e cola</p>
        <textarea readonly rows="6" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs"><?= htmlspecialchars((string) ($order['copy_paste'] ?? '')) ?></textarea>
        <p class="mt-3 text-xs text-slate-500">Após o pagamento, o webhook <code>/webhooks/pix</code> confirma automaticamente. Em desenvolvimento, use o <code>txid</code> retornado pelo gateway mock.</p>
    </div>
</div>
