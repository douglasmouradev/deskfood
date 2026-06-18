<?php
declare(strict_types=1);
/** @var string $csrf */
/** @var string $phone */
/** @var string|null $ok */
/** @var string|null $error */
?>
<div class="df-card p-8">
    <h1 class="font-display text-2xl font-semibold text-zinc-900">Código de verificação</h1>
    <p class="mt-2 text-sm text-zinc-600">Digite os 6 dígitos enviados para o seu celular.</p>
    <?php if (!empty($ok)): ?><p class="mt-3 text-sm text-emerald-700"><?= htmlspecialchars($ok) ?></p><?php endif; ?>
    <?php if (!empty($error)): ?><p class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="/cliente/verificar" class="mt-6 space-y-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
        <input name="code" maxlength="6" pattern="\d{6}" required inputmode="numeric" autocomplete="one-time-code" class="df-input py-4 text-center text-2xl tracking-[0.35em]" placeholder="000000" aria-label="Código de 6 dígitos">
        <button class="df-btn-primary w-full py-3">Confirmar</button>
    </form>
</div>
