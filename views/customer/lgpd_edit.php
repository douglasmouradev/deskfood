<?php
declare(strict_types=1);
/** @var array<string,mixed> $user */
/** @var string $csrf */
?>
<h1 class="text-2xl font-bold">Corrigir dados</h1>
<?php if (!empty($_SESSION['flash_ok'])): ?><p class="mt-3 text-sm text-emerald-700"><?= htmlspecialchars((string) $_SESSION['flash_ok']) ?></p><?php unset($_SESSION['flash_ok']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?><p class="mt-3 text-sm text-red-700"><?= htmlspecialchars((string) $_SESSION['flash_error']) ?></p><?php unset($_SESSION['flash_error']); endif; ?>
<form method="post" action="/cliente/lgpd/editar" class="mt-6 max-w-md space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div>
        <label class="text-sm font-medium">Nome</label>
        <input name="name" value="<?= htmlspecialchars((string) $user['name']) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" required>
    </div>
    <div>
        <label class="text-sm font-medium">E-mail <span class="font-normal text-slate-500">(opcional, confirmações de pedido)</span></label>
        <input type="email" name="email" value="<?= htmlspecialchars((string) ($user['email'] ?? '')) ?>" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" placeholder="voce@email.com">
    </div>
    <p class="text-xs text-slate-500">Para alterar telefone, contate o suporte ou refaça o cadastro — exige novo fluxo OTP.</p>
    <button class="rounded-full bg-orange-500 px-5 py-2 text-sm font-semibold text-white">Salvar</button>
</form>
