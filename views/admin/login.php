<?php
declare(strict_types=1);
/** @var string $csrf */
/** @var string|null $error */
use App\Helpers\Env;
?>
<div class="df-card p-8">
    <div class="flex items-center gap-3">
        <img src="/assets/img/logo.png" class="h-10 w-auto" alt="Desk Food">
        <div>
            <p class="text-sm font-semibold text-zinc-900">Desk Food</p>
            <p class="text-xs text-zinc-500">Painel do dono</p>
        </div>
    </div>
    <?php if (!empty($error)): ?>
        <p class="mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="/admin/login" class="mt-6 space-y-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div>
            <label class="df-label">E-mail</label>
            <input type="email" name="email" required class="df-input mt-1.5" autocomplete="username">
        </div>
        <div>
            <label class="df-label">Senha</label>
            <input type="password" name="password" required class="df-input mt-1.5" autocomplete="current-password">
        </div>
        <button class="df-btn-primary w-full py-3">Entrar</button>
    </form>
    <?php if (Env::get('APP_ENV', 'production') === 'local'): ?>
    <p class="mt-5 text-center text-[11px] text-zinc-400">Ambiente local: dono@deskfood.local</p>
    <?php endif; ?>
</div>
