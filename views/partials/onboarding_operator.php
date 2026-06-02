<?php
declare(strict_types=1);
$csrf = \App\Helpers\Csrf::token();
?>
<div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div>
            <p class="font-semibold">Primeiros passos nesta sessão</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-amber-900/90">
                <li>Abra o <strong>caixa</strong> e registre o fundo de troco.</li>
                <li>Revise o <strong>cardápio</strong> e cadastre <strong>motoboys</strong>.</li>
                <li>Use <strong>Pedidos (CSV)</strong> no menu para exportar pedidos.</li>
            </ul>
        </div>
        <form method="post" action="/operador/onboarding/dismiss" class="shrink-0">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit" class="rounded-full bg-amber-900 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-800">Entendi, ocultar</button>
        </form>
    </div>
</div>
