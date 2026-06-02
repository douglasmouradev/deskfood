<?php
declare(strict_types=1);
/** @var array<string,mixed> $user */
?>
<h1 class="text-2xl font-bold">Meus dados</h1>
<dl class="mt-6 space-y-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm">
    <div class="flex justify-between gap-4"><dt class="text-slate-500">Nome</dt><dd class="font-medium"><?= htmlspecialchars((string) $user['name']) ?></dd></div>
    <div class="flex justify-between gap-4"><dt class="text-slate-500">Telefone</dt><dd class="font-medium"><?= htmlspecialchars((string) $user['phone']) ?></dd></div>
    <?php if (!empty($user['email'])): ?>
    <div class="flex justify-between gap-4"><dt class="text-slate-500">E-mail</dt><dd class="font-medium"><?= htmlspecialchars((string) $user['email']) ?></dd></div>
    <?php endif; ?>
    <div class="flex justify-between gap-4"><dt class="text-slate-500">Cadastro</dt><dd class="font-medium"><?= htmlspecialchars((string) $user['created_at']) ?></dd></div>
</dl>
<a class="mt-4 inline-flex text-sm font-semibold text-orange-600" href="/cliente/lgpd">Voltar</a>
