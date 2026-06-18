<?php
declare(strict_types=1);
/** @var string $csrf */
/** @var string|null $error */
?>
<div class="df-card p-8">
    <h1 class="font-display text-2xl font-semibold text-zinc-900">Entrar com celular</h1>
    <p class="mt-2 text-sm leading-relaxed text-zinc-600">Enviamos um código de 6 dígitos por SMS. Válido por 5 minutos.</p>
    <?php if (!empty($error)): ?>
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-800"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/cliente/login" class="mt-6 space-y-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div>
            <label class="df-label">Nome completo</label>
            <input name="name" required class="df-input mt-1.5">
        </div>
        <div>
            <label class="df-label">Celular</label>
            <input name="phone" required class="df-input mt-1.5" placeholder="DDD + número, só dígitos" inputmode="numeric">
        </div>
        <div class="space-y-2.5 rounded-xl border border-zinc-100 bg-stone-50/80 p-4 text-sm text-zinc-700">
            <label class="flex items-start gap-2.5"><input type="checkbox" name="accept_terms" value="1" required class="mt-0.5 rounded border-zinc-300"> Li e aceito os <a class="font-medium text-zinc-900 underline underline-offset-2" href="/termos" target="_blank">Termos de Uso</a></label>
            <label class="flex items-start gap-2.5"><input type="checkbox" name="accept_privacy" value="1" required class="mt-0.5 rounded border-zinc-300"> Li a <a class="font-medium text-zinc-900 underline underline-offset-2" href="/privacidade" target="_blank">Política de Privacidade</a></label>
            <label class="flex items-start gap-2.5"><input type="checkbox" name="accept_sms" value="1" required class="mt-0.5 rounded border-zinc-300"> Autorizo SMS para login e avisos do pedido</label>
        </div>
        <button class="df-btn-primary w-full py-3">Receber código</button>
    </form>
</div>
