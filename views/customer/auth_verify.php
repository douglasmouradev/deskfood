<?php
declare(strict_types=1);
/** @var string $csrf */
/** @var string $phone */
/** @var string|null $ok */
/** @var string|null $error */
?>
<div class="mx-auto max-w-md rounded-3xl border border-orange-100 bg-white p-8 shadow-xl">
    <h1 class="text-2xl font-bold">Informe o código</h1>
    <p class="mt-2 text-sm text-slate-600">Digite os 6 números recebidos por SMS.</p>
    <?php if (!empty($ok)): ?><p class="mt-3 text-sm text-emerald-700"><?= htmlspecialchars($ok) ?></p><?php endif; ?>
    <?php if (!empty($error)): ?><p class="mt-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="/cliente/verificar" class="mt-6 space-y-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
        <input name="code" maxlength="6" pattern="\d{6}" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-center text-2xl tracking-[0.4em] outline-none ring-orange-500 focus:ring-2" placeholder="000000">
        <button class="w-full rounded-full bg-orange-500 py-3 text-sm font-semibold text-white hover:bg-orange-600">Confirmar</button>
    </form>
</div>
