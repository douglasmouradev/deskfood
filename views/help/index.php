<?php
declare(strict_types=1);
if (!isset($config) || !is_array($config)) {
    $config = require BASE_PATH . '/config/app.php';
}
?>
<section class="mx-auto max-w-3xl space-y-6 text-slate-700">
    <h1 class="text-3xl font-bold text-ink-900">Central de ajuda</h1>
    <p class="text-ink-600">Primeiros passos e respostas rápidas para operar o Desk Food com segurança.</p>

    <h2 class="mt-10 text-xl font-semibold text-ink-900">Para o dono (super admin)</h2>
    <ol class="list-decimal space-y-2 pl-5 text-ink-700">
        <li>Cadastre cada <strong>unidade</strong> com endereço, taxa de entrega e horários.</li>
        <li>Crie o <strong>operador</strong> vinculado à unidade (e-mail + senha forte).</li>
        <li>Configure <strong>PIX</strong> e <strong>SMS</strong> no <code>.env</code> antes de ir a produção.</li>
        <li>Após instalar, defina <code>PIX_WEBHOOK_SECRET</code> e <code>ALLOW_INSTALL=0</code>.</li>
    </ol>

    <h2 class="mt-10 text-xl font-semibold text-ink-900">Para o operador</h2>
    <ol class="list-decimal space-y-2 pl-5 text-ink-700">
        <li><strong>Abra o caixa</strong> no início do expediente com o fundo de troco.</li>
        <li>Monte o <strong>cardápio</strong> (categorias, produtos e fotos).</li>
        <li>Cadastre <strong>motoboys</strong> e use o link mágico enviado a cada entregador.</li>
        <li>Acompanhe pedidos: confirmar → preparar → atribuir motoboy → entregue.</li>
        <li>Baixe o <strong>CSV de pedidos</strong> em Relatórios (últimos 90 dias) para conferência.</li>
    </ol>

    <h2 class="mt-10 text-xl font-semibold text-ink-900">Cliente final</h2>
    <p class="text-ink-700">Login por <strong>SMS (OTP)</strong>. Aceite termos e política de privacidade. Pagamento PIX ou na entrega com rastreio em tempo real.</p>

    <h2 class="mt-10 text-xl font-semibold text-ink-900">LGPD</h2>
    <p class="text-ink-700">O cliente acessa <a class="text-brand-600 underline" href="/cliente/lgpd">Privacidade (LGPD)</a> após login para exportar dados ou solicitar anonimização.</p>

    <h2 class="mt-10 text-xl font-semibold text-ink-900">Contato comercial</h2>
    <p class="text-ink-700">
        <strong><?= htmlspecialchars((string) ($config['commercial_company'] ?? 'TDesk Solutions')) ?></strong> — comercial e implantação:
    </p>
    <ul class="mt-2 list-none space-y-2 text-ink-700">
        <?php $em = trim((string) ($config['commercial_email'] ?? '')); ?>
        <?php if ($em !== ''): ?>
            <li>E-mail: <a class="font-medium text-brand-600 underline hover:no-underline" href="mailto:<?= htmlspecialchars($em) ?>"><?= htmlspecialchars($em) ?></a></li>
        <?php endif; ?>
        <?php
        $tel = trim((string) ($config['commercial_phone_tel'] ?? ''));
        $lab = trim((string) ($config['commercial_phone_label'] ?? ''));
        ?>
        <?php if ($tel !== '' && $lab !== ''): ?>
            <li>Telefone / WhatsApp: <a class="font-medium text-brand-600 underline hover:no-underline" href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $tel)) ?>"><?= htmlspecialchars($lab) ?></a></li>
        <?php endif; ?>
    </ul>

    <p class="mt-10 text-sm text-ink-500">Para LGPD e dados pessoais do cliente final, use o fluxo em <a class="text-brand-600 underline" href="/cliente/lgpd">/cliente/lgpd</a> após login.</p>
</section>
