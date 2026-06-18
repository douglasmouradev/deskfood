<?php
declare(strict_types=1);
/** @var string $csrf */
/** @var string|null $error */
?>
<div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
    <div class="flex items-center gap-3">
        <img src="/assets/img/logo.png" class="h-10 w-auto" alt="Desk Food">
        <div>
            <p class="text-sm font-semibold text-slate-900">Desk Food</p>
            <p class="text-xs text-slate-500">Painel do dono</p>
        </div>
    </div>
    <?php if (!empty($error)): ?>
        <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-800"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="/admin/login" class="mt-6 space-y-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div>
            <label class="text-sm font-medium text-slate-700">E-mail</label>
            <input type="email" name="email" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Senha</label>
            <input type="password" name="password" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <button class="w-full rounded-full bg-slate-900 py-3 text-sm font-semibold text-white">Entrar</button>
    </form>
    <p class="mt-4 text-xs text-slate-500">Credenciais demo: dono@deskfood.local / Admin123!</p>
</div>
