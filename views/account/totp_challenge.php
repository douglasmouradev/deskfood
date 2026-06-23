<?php
declare(strict_types=1);
/** @var string $csrf */
/** @var string $base_path */
/** @var string|null $flash_error */
?>
<div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="text-lg font-semibold text-slate-900">Verificação em duas etapas</h1>
    <p class="mt-1 text-sm text-slate-600">Digite o código de 6 dígitos do seu aplicativo autenticador.</p>
    <?php if (!empty($flash_error)): ?>
        <p class="mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"><?= htmlspecialchars((string) $flash_error) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= htmlspecialchars($base_path) ?>/2fa" class="mt-4 space-y-3">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code"
               class="w-full rounded-lg border px-3 py-3 text-center text-lg tracking-widest" placeholder="000000">
        <button type="submit" class="w-full rounded-full bg-orange-500 py-2.5 text-sm font-semibold text-white">Verificar</button>
    </form>
</div>
