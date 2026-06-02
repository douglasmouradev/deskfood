<?php
declare(strict_types=1);
/** @var array<string,mixed> $order */
/** @var string $appUrl */
$orderId = (int) ($order['id'] ?? 0);
$copy = (string) ($order['copy_paste'] ?? '');
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . rawurlencode($copy);
$expires = (string) ($order['expires_at'] ?? '');
$expiresTs = $expires !== '' ? strtotime($expires) : false;
?>
<h1 class="text-2xl font-bold">Pagamento PIX</h1>
<p class="mt-2 text-sm text-slate-600">Pedido #<?= htmlspecialchars((string) $order['order_number']) ?> · Total <strong>R$ <?= number_format((float) $order['total'], 2, ',', '.') ?></strong></p>

<div id="pix-paid-banner" class="mt-4 hidden rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
    Pagamento confirmado! Redirecionando…
</div>

<?php if ($expiresTs): ?>
    <p id="pix-timer" class="mt-2 text-sm text-amber-700" data-expires="<?= (int) $expiresTs ?>">Calculando tempo restante…</p>
<?php endif; ?>

<div class="mt-6 grid gap-6 md:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center">
        <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR Code PIX" class="mx-auto h-64 w-64 max-w-full">
        <p class="mt-2 text-xs text-slate-500">Escaneie com o app do seu banco</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <p class="text-sm font-semibold text-slate-900">Copia e cola</p>
        <textarea id="pix-copy" readonly rows="7" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs font-mono"><?= htmlspecialchars($copy) ?></textarea>
        <button type="button" id="pix-copy-btn" class="mt-3 w-full rounded-full bg-slate-900 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Copiar código PIX</button>
        <p class="mt-3 text-xs text-slate-500">Após pagar, a confirmação é automática. Esta página atualiza sozinha.</p>
    </div>
</div>

<script>
(function () {
    var btn = document.getElementById('pix-copy-btn');
    var ta = document.getElementById('pix-copy');
    if (btn && ta) {
        btn.addEventListener('click', function () {
            ta.select();
            navigator.clipboard.writeText(ta.value).then(function () {
                btn.textContent = 'Copiado!';
                setTimeout(function () { btn.textContent = 'Copiar código PIX'; }, 2000);
            }).catch(function () {
                document.execCommand('copy');
                btn.textContent = 'Copiado!';
            });
        });
    }
    var timer = document.getElementById('pix-timer');
    if (timer) {
        var exp = parseInt(timer.getAttribute('data-expires'), 10) * 1000;
        function tick() {
            var left = Math.max(0, exp - Date.now());
            if (left <= 0) {
                timer.textContent = 'PIX expirado — gere um novo pedido se necessário.';
                return;
            }
            var m = Math.floor(left / 60000);
            var s = Math.floor((left % 60000) / 1000);
            timer.textContent = 'Expira em ' + m + ' min ' + s + ' s';
            setTimeout(tick, 1000);
        }
        tick();
    }
    var pixPoll = setInterval(function () {
        fetch('/cliente/pedido/<?= $orderId ?>/pix/status', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j && j.ok && j.payment_status === 'pago') {
                    clearInterval(pixPoll);
                    var b = document.getElementById('pix-paid-banner');
                    if (b) b.classList.remove('hidden');
                    setTimeout(function () { location.href = '/cliente/pedidos'; }, 2000);
                }
            }).catch(function () {});
    }, 5000);
})();
</script>
