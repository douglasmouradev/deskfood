<?php
declare(strict_types=1);
/** @var string $csrf */
?>
<h1 class="text-2xl font-bold">Privacidade e LGPD</h1>
<p class="mt-2 text-sm text-slate-600">Exercite seus direitos do titular conforme a Lei 13.709/2018.</p>
<div class="mt-6 grid gap-4 md:grid-cols-2">
    <a class="rounded-2xl border border-slate-200 bg-white p-4 hover:border-orange-300" href="/cliente/lgpd/dados">
        <p class="font-semibold">Ver meus dados</p>
        <p class="text-sm text-slate-600">Consulta dos dados cadastrais.</p>
    </a>
    <a class="rounded-2xl border border-slate-200 bg-white p-4 hover:border-orange-300" href="/cliente/lgpd/exportar">
        <p class="font-semibold">Exportar JSON</p>
        <p class="text-sm text-slate-600">Portabilidade dos dados.</p>
    </a>
    <a class="rounded-2xl border border-slate-200 bg-white p-4 hover:border-orange-300" href="/cliente/lgpd/editar">
        <p class="font-semibold">Corrigir dados</p>
        <p class="text-sm text-slate-600">Atualização de nome (telefone exige novo OTP).</p>
    </a>
    <form method="post" action="/cliente/lgpd/excluir" onsubmit="return confirm('Confirma anonimização da conta?');" class="rounded-2xl border border-red-200 bg-red-50 p-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <p class="font-semibold text-red-800">Excluir / anonimizar</p>
        <p class="text-sm text-red-700">Histórico de pedidos permanece, sem identificação pessoal.</p>
        <button class="mt-3 rounded-full bg-red-600 px-4 py-2 text-sm font-semibold text-white">Solicitar anonimização</button>
    </form>
</div>
