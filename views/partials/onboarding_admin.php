<?php
declare(strict_types=1);
$csrf = \App\Helpers\Csrf::token();
?>
<div class="mb-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-950">
    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div>
            <p class="font-semibold">Boas-vindas ao painel do dono</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sky-900/90">
                <li>Cadastre <strong>unidades</strong> e associe operadores.</li>
                <li>Antes do go-live rode <code class="rounded bg-sky-100 px-1">php bin/check-production.php</code> no servidor.</li>
                <li>Revise <strong>APP_URL</strong>, <strong>HTTPS</strong>, <strong>PIX</strong>, <strong>SMS</strong> e <strong>GOOGLE_MAPS_API_KEY</strong>.</li>
                <li>Consulte a <a class="font-semibold underline" href="/ajuda">central de ajuda</a>.</li>
            </ul>
        </div>
        <form method="post" action="/admin/onboarding/dismiss" class="shrink-0">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit" class="rounded-full bg-sky-900 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-800">Ocultar</button>
        </form>
    </div>
</div>
