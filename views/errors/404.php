<?php
declare(strict_types=1);
/** @var string $title */
?>
<div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
    <p class="text-sm font-semibold text-brand-600">404</p>
    <h1 class="mt-2 text-2xl font-bold text-ink-900"><?= htmlspecialchars($title) ?></h1>
    <p class="mt-2 text-ink-600">A página que você procura não existe ou foi movida.</p>
    <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a class="inline-flex rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700" href="/">Vitrine de unidades</a>
        <a class="inline-flex rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold text-ink-800 hover:bg-slate-50" href="/landing">Plataforma</a>
        <a class="inline-flex rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold text-ink-800 hover:bg-slate-50" href="/ajuda">Central de ajuda</a>
    </div>
</div>
