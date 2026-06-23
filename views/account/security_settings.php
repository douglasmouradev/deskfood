<?php
declare(strict_types=1);
/** @var string $csrf */
/** @var string $base_path */
/** @var bool $totp_enabled */
/** @var string|null $totp_secret */
/** @var string|null $otp_uri */
/** @var string|null $flash_error */
/** @var string|null $flash_success */
?>
<div class="mx-auto max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="text-lg font-semibold text-slate-900">Segurança da conta</h1>
    <p class="mt-1 text-sm text-slate-600">Autenticação em duas etapas (Google Authenticator, Authy, etc.).</p>
    <?php if (!empty($flash_success)): ?>
        <p class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800"><?= htmlspecialchars((string) $flash_success) ?></p>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <p class="mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"><?= htmlspecialchars((string) $flash_error) ?></p>
    <?php endif; ?>

    <?php if ($totp_enabled): ?>
        <p class="mt-4 text-sm font-medium text-emerald-700">2FA ativo nesta conta.</p>
        <form method="post" action="<?= htmlspecialchars($base_path) ?>/seguranca/totp/desativar" class="mt-4 space-y-2">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <input name="code" inputmode="numeric" maxlength="6" required placeholder="Código para desativar"
                   class="w-full rounded-lg border px-3 py-2 text-sm">
            <button type="submit" class="rounded-full border border-red-200 px-4 py-2 text-sm font-semibold text-red-700">Desativar 2FA</button>
        </form>
    <?php elseif ($totp_secret && $otp_uri): ?>
        <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm">
            <p class="font-medium text-slate-800">1. Adicione no app autenticador</p>
            <p class="mt-2 break-all font-mono text-xs text-slate-600"><?= htmlspecialchars($totp_secret) ?></p>
            <p class="mt-3 font-medium text-slate-800">2. Confirme com um código</p>
        </div>
        <form method="post" action="<?= htmlspecialchars($base_path) ?>/seguranca/totp/ativar" class="mt-4 space-y-2">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <input name="code" inputmode="numeric" maxlength="6" required class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="000000">
            <button type="submit" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Ativar 2FA</button>
        </form>
    <?php else: ?>
        <form method="post" action="<?= htmlspecialchars($base_path) ?>/seguranca/totp/iniciar" class="mt-4">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit" class="rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white">Configurar 2FA</button>
        </form>
    <?php endif; ?>
</div>
