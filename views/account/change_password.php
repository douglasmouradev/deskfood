<?php
declare(strict_types=1);
/** @var string $csrf */
/** @var string $base_path */
/** @var string|null $flash_error */
/** @var string|null $flash_success */
?>
<div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="text-lg font-semibold text-slate-900">Alterar senha</h1>
    <p class="mt-1 text-sm text-slate-600">Por segurança, defina uma senha forte antes de continuar.</p>
    <?php if (!empty($flash_error)): ?>
        <p class="mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"><?= htmlspecialchars((string) $flash_error) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= htmlspecialchars($base_path) ?>/senha" class="mt-4 space-y-3">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <label class="block text-sm font-medium text-slate-700">Senha atual
            <input type="password" name="current_password" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        </label>
        <label class="block text-sm font-medium text-slate-700">Nova senha (mín. 8 caracteres)
            <input type="password" name="new_password" required minlength="8" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        </label>
        <label class="block text-sm font-medium text-slate-700">Confirmar nova senha
            <input type="password" name="confirm_password" required minlength="8" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        </label>
        <button type="submit" class="w-full rounded-full bg-orange-500 py-2.5 text-sm font-semibold text-white">Salvar senha</button>
    </form>
</div>
