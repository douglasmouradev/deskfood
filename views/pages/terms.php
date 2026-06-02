<?php
declare(strict_types=1);
/** @var array<string,mixed> $config */
?>
<article class="max-w-3xl space-y-4 text-slate-700">
    <h1>Termos de Uso</h1>
    <p>Versão <?= htmlspecialchars((string) ($config['terms_version'] ?? '1.0')) ?> — <?= htmlspecialchars((string) ($config['name'] ?? 'Desk Food')) ?></p>
    <p>Estes Termos regem o uso da plataforma SaaS de delivery Desk Food, incluindo site, painéis administrativos e funcionalidades associadas.</p>
    <h2>1. Objeto</h2>
    <p>O Desk Food oferece ferramentas para cadastro de unidades, cardápio, pedidos, pagamentos, caixa e logística de entrega. O uso implica ciência e concordância integral destes Termos.</p>
    <h2>2. Cadastro e contas</h2>
    <p>Clientes finais autenticam-se por SMS (OTP). Administradores utilizam e-mail e senha com boas práticas de segurança. Você é responsável pela veracidade dos dados informados.</p>
    <h2>3. Pedidos e pagamentos</h2>
    <p>Pedidos ficam sujeitos à confirmação da unidade. Pagamentos via PIX seguem regras do gateway configurado. Pagamentos na entrega dependem da conferência presencial pelo operador.</p>
    <h2>4. Limitação de responsabilidade</h2>
    <p>A plataforma é fornecida “como está”, sem garantias de disponibilidade ininterrupta. Integrações de terceiros (SMS, PIX, mapas) seguem termos próprios dos fornecedores.</p>
    <h2>5. Contato</h2>
    <p>Dúvidas jurídicas: <?= htmlspecialchars((string) ($config['dpo_email'] ?? 'contato@seudominio.com.br')) ?>.</p>
</article>
