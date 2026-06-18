<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $motoboys */
/** @var string $csrf */
/** @var array{name?:string,url?:string,expires?:string}|null $link_flash */
?>
<?php if (!empty($link_flash['url'])): ?>
<div id="motoboy-link-flash" class="mb-6 rounded-xl border-2 border-emerald-300 bg-emerald-50 px-4 py-4 text-sm text-emerald-950 shadow-sm">
    <p class="font-semibold text-base">Link de acesso gerado<?= !empty($link_flash['name']) ? ' — ' . htmlspecialchars((string) $link_flash['name']) : '' ?></p>
    <p class="mt-2 text-xs">Copie agora e abra no celular do entregador. Por segurança, o link completo não será exibido novamente.</p>
    <p id="motoboy-link-url" class="mt-3 break-all rounded-lg bg-white px-3 py-2 font-mono text-xs ring-1 ring-emerald-200"><?= htmlspecialchars((string) $link_flash['url']) ?></p>
    <?php if (!empty($link_flash['expires'])): ?>
    <p class="mt-2 text-xs opacity-80">Válido até <?= htmlspecialchars((string) $link_flash['expires']) ?></p>
    <?php endif; ?>
    <div class="mt-4 flex flex-wrap gap-2">
        <button type="button" id="motoboy-link-copy" class="rounded-full bg-emerald-700 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-800">Copiar link</button>
        <a href="<?= htmlspecialchars((string) $link_flash['url']) ?>" target="_blank" rel="noopener" class="rounded-full border border-emerald-300 bg-white px-4 py-2 text-xs font-semibold text-emerald-900 hover:bg-emerald-100">Abrir link</a>
    </div>
</div>
<script>
(function () {
    var box = document.getElementById('motoboy-link-flash');
    if (box) {
        box.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    var btn = document.getElementById('motoboy-link-copy');
    var urlEl = document.getElementById('motoboy-link-url');
    if (btn && urlEl) {
        btn.addEventListener('click', function () {
            var url = urlEl.textContent || '';
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () {
                    btn.textContent = 'Copiado!';
                    setTimeout(function () { btn.textContent = 'Copiar link'; }, 2000);
                }).catch(fallback);
            } else {
                fallback();
            }
            function fallback() {
                var ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); btn.textContent = 'Copiado!'; } catch (e) {}
                document.body.removeChild(ta);
            }
        });
    }
})();
</script>
<?php endif; ?>
<div class="rounded-2xl border border-slate-200 bg-white p-4">
    <h2 class="font-semibold">Novo motoboy</h2>
    <form method="post" action="/operador/motoboys" class="mt-3 grid gap-2 md:grid-cols-2">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input name="name" required class="rounded-lg border px-3 py-2 text-sm" placeholder="Nome completo">
        <input name="phone" required class="rounded-lg border px-3 py-2 text-sm" placeholder="Telefone">
        <input name="cpf" required class="md:col-span-2 rounded-lg border px-3 py-2 text-sm" placeholder="CPF (somente números)">
        <button type="submit" class="md:col-span-2 rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white">Cadastrar</button>
    </form>
</div>
<div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
        <tr><th class="px-3 py-2">Nome</th><th class="px-3 py-2">Telefone</th><th class="px-3 py-2">Acesso</th><th class="px-3 py-2"></th></tr>
        </thead>
        <tbody>
        <?php foreach ($motoboys as $m): ?>
            <tr class="border-t border-slate-100">
                <td class="px-3 py-2"><?= htmlspecialchars((string) $m['name']) ?></td>
                <td class="px-3 py-2"><?= htmlspecialchars((string) $m['phone']) ?></td>
                <td class="px-3 py-2 text-xs">
                    <?php if (!empty($m['has_link'])): ?>
                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 font-medium text-emerald-800">Link ativo</span>
                    <?php else: ?>
                        <span class="text-slate-400">Sem link</span>
                    <?php endif; ?>
                </td>
                <td class="px-3 py-2">
                    <form method="post" action="/operador/motoboys/<?= (int) $m['id'] ?>/revogar" class="inline">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit" class="rounded-full bg-orange-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-600">Renovar link</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($motoboys === []): ?>
            <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">Nenhum motoboy cadastrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
