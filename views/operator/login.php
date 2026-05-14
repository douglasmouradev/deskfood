<?php
declare(strict_types=1);
/** @var string $csrf */
/** @var string|null $error */
?>
<div class="mx-auto max-w-md rounded-2xl border border-orange-100 bg-white p-8 shadow-sm">
    <h1 class="text-xl font-bold text-slate-900">Operador da unidade</h1>
    <?php if (!empty($error)): ?><p class="mt-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="/operador/login" class="mt-6 space-y-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="email" name="email" required class="w-full rounded-xl border px-3 py-2 text-sm" placeholder="E-mail">
        <input type="password" name="password" required class="w-full rounded-xl border px-3 py-2 text-sm" placeholder="Senha">
        <button class="w-full rounded-full bg-orange-500 py-3 text-sm font-semibold text-white">Entrar</button>
    </form>
    <p class="mt-4 text-xs text-slate-500">Demo: operador@deskfood.local / Admin123!</p>
</div>
