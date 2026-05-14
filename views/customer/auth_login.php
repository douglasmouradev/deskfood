<?php
declare(strict_types=1);
/** @var string $csrf */
/** @var string|null $error */
?>
<div class="mx-auto max-w-md rounded-3xl border border-orange-100 bg-white p-8 shadow-xl shadow-orange-100/50">
    <h1 class="text-2xl font-bold text-slate-900">Entrar com telefone</h1>
    <p class="mt-2 text-sm text-slate-600">Enviaremos um código SMS de 6 dígitos válido por 5 minutos.</p>
    <?php if (!empty($error)): ?>
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/cliente/login" class="mt-6 space-y-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div>
            <label class="text-sm font-medium text-slate-700">Nome completo</label>
            <input name="name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none ring-brand-500 focus:ring-2">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Celular (DDD + número)</label>
            <input name="phone" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none ring-brand-500 focus:ring-2" placeholder="11999998888">
        </div>
        <div class="space-y-2 text-sm text-slate-700">
            <label class="flex items-start gap-2"><input type="checkbox" name="accept_terms" value="1" required class="mt-1"> Aceito os <a class="text-brand-600 underline" href="/termos" target="_blank">Termos de Uso</a>.</label>
            <label class="flex items-start gap-2"><input type="checkbox" name="accept_privacy" value="1" required class="mt-1"> Li a <a class="text-brand-600 underline" href="/privacidade" target="_blank">Política de Privacidade</a>.</label>
            <label class="flex items-start gap-2"><input type="checkbox" name="accept_sms" value="1" required class="mt-1"> Autorizo o envio de SMS transacionais.</label>
        </div>
        <button class="w-full rounded-full bg-orange-500 py-3 text-sm font-semibold text-white hover:bg-orange-600">Receber código</button>
    </form>
</div>
