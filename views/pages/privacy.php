<?php
declare(strict_types=1);
/** @var array<string,mixed> $config */
?>
<article class="max-w-3xl space-y-4 text-slate-700">
    <h1>Política de Privacidade</h1>
    <p>Versão <?= htmlspecialchars((string) ($config['privacy_version'] ?? '1.0')) ?> — Lei 13.709/2018 (LGPD)</p>
    <p>O <?= htmlspecialchars((string) ($config['name'] ?? 'Desk Food')) ?> trata dados pessoais com transparência, segurança e finalidades legítimas descritas abaixo.</p>
    <h2>1. Controlador e encarregado (DPO)</h2>
    <p><strong>Encarregado:</strong> <?= htmlspecialchars((string) ($config['dpo_name'] ?? 'Encarregado Desk Food')) ?> — e-mail <?= htmlspecialchars((string) ($config['dpo_email'] ?? 'privacidade@seudominio.com.br')) ?>.</p>
    <h2>2. Dados coletados</h2>
    <ul>
        <li>Cliente: nome, telefone, endereço de entrega, histórico de pedidos, consentimentos e logs de acesso (IP, user agent).</li>
        <li>Operação: dados de unidade, cardápio, pedidos, pagamentos, caixa, motoboys (incluindo CPF cifrado).</li>
        <li>Sessões persistidas em banco para escalabilidade.</li>
    </ul>
    <h2>3. Finalidades</h2>
    <p>Execução de pedidos, autenticação por SMS, cumprimento legal, segurança, métricas operacionais e suporte.</p>
    <h2>4. Compartilhamento</h2>
    <p>Fornecedores de SMS, gateway PIX e infraestrutura de hospedagem podem receber dados estritamente necessários. Não vendemos dados pessoais.</p>
    <h2>5. Retenção</h2>
    <p>Mantemos dados pelo tempo necessário à prestação do serviço e obrigações legais/fiscais. Pedidos podem permanecer anonimizados após solicitação do titular.</p>
    <h2>6. Direitos do titular</h2>
    <p>Acesse o painel do cliente para visualizar, exportar JSON, corrigir nome e solicitar anonimização da conta, conforme LGPD.</p>
    <h2>7. Segurança</h2>
    <p>Utilizamos HTTPS recomendado, headers de segurança, prepared statements, criptografia para dados sensíveis de motoboys e logs de auditoria para ações críticas.</p>
</article>
