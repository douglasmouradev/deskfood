# Desk Food

SaaS web de delivery em **PHP 8.3** (MVC manual, sem framework), **MySQL 8**, front com **Tailwind compilado** + **Alpine.js** e identidade própria. Pronto para VPS, shared hosting ou `php -S` com roteador embutido.

## Requisitos

- PHP 8.3+ (extensões `pdo_mysql`, `openssl`, `json`, `mbstring`, `curl`)
- MySQL 8.0+
- Composer 2
- **Node.js 18+** (apenas para compilar CSS; o servidor de produção não precisa de Node se você commitar `public/assets/css/tailwind.css`)
- Apache com `mod_rewrite` **ou** Nginx apontando o docroot para `public/`

## Instalação rápida

```bash
cd deskfood
cp .env.example .env
# Ajuste DB_*, APP_URL, APP_SECRET, INSTALL_KEY, SMS_*, PIX_* e, se quiser, APP_COMMERCIAL_* e OPERATOR_BOARD_POLL_MS
composer install
npm install
npm run build
php install.php
```

- **Migrations** rodam sempre ao executar `php install.php` (útil ao adicionar arquivos em `database/migrations/`).
- O **seed** de demo só na primeira execução; para repetir: `php install.php --force`.
- É criado `storage/.installed` como marcador.
- Navegador: `https://seu-dominio/install.php?key=SUA_INSTALL_KEY`. Em `APP_ENV=production` o browser exige também `ALLOW_INSTALL=1` no `.env`.

## Healthcheck

`GET /health` — JSON com `database: ok|error` (HTTP 200 ou 503).

## Servidor embutido (desenvolvimento)

```bash
php -S 0.0.0.0:8080 -t public public/router.php
```

Defina `APP_URL=http://localhost:8080` no `.env`.

## Frontend (Tailwind)

O CSS do Tailwind é gerado a partir de `resources/css/app.source.css` e salvo em `public/assets/css/tailwind.css`. Estilos extras do app ficam em `public/assets/css/app.css`; a landing usa também `landing.css`.

```bash
npm run build        # compila e minifica (produção)
npm run watch:css    # recompila ao editar views/CSS
```

- **`TAILWIND_CDN=0`** (padrão): usa o arquivo compilado — mais rápido e CSP mais restrito.
- **`TAILWIND_CDN=1`**: fallback para `cdn.tailwindcss.com` (útil se ainda não rodou `npm run build`).

Após alterar classes nas views PHP, rode `npm run build` antes do deploy.

## Credenciais de demonstração (seed)

| Papel    | E-mail                 | Senha     |
|----------|------------------------|-----------|
| Dono     | `dono@deskfood.local`  | `Admin123!` |
| Operador | `operador@deskfood.local` | `Admin123!` |

Cliente: fluxo **OTP** por SMS — ver [SMS (Zenvia)](#sms-zenvia-em-produção) abaixo.

## SMS (Zenvia) em produção

Recomendado para clientes no **Brasil** (login OTP e, opcionalmente, avisos de pedido). O código já integra **Zenvia** e **Twilio**; em dev use `log`.

### Desenvolvimento (`SMS_PROVIDER=log`)

```env
SMS_PROVIDER=log
```

1. Acesse `/cliente/login`, informe nome e celular (DDD + número, ex.: `11999998888`).
2. Abra `storage/logs/app-YYYY-MM-DD.log` e procure `SMS (log)` — o código de 6 dígitos aparece na mensagem (em `APP_ENV=production` o código é mascarado no log).
3. Em `/cliente/verificar`, digite o código.

### Conta Zenvia (passo a passo)

1. Crie conta em [https://www.zenvia.com](https://www.zenvia.com) e acesse o painel.
2. Gere um **API Token** (área de desenvolvedor / API) com permissão para envio SMS.
3. Cadastre o **remetente** (`SMS_SENDER`, ex.: `DeskFood`) conforme as regras da Zenvia (nome curto ou número aprovado).
4. Confirme **saldo** ou pacote de SMS transacional; teste primeiro com seu próprio celular.
5. No servidor, configure o `.env`:

```env
SMS_PROVIDER=zenvia
SMS_API_KEY=seu_token_api_zenvia
SMS_SENDER=DeskFood
```

6. Reinicie PHP-FPM (ou o servidor embutido) após alterar o `.env`.
7. Teste o fluxo completo:
   - `GET /cliente/login` → enviar código
   - Verifique o SMS no celular (pode levar alguns segundos)
   - `GET /cliente/verificar?phone=11999998888` → informe os 6 dígitos
   - Confirme redirecionamento para `/cliente/pedidos`

### Teste e diagnóstico

| Sintoma | O que verificar |
|---------|------------------|
| Tela diz “enviado” mas não chega SMS | `storage/logs/app-*.log` — erros `Zenvia falhou` com HTTP/body |
| “Muitas tentativas” | Rate limit: 15 envios por IP em 15 min; 3 códigos por telefone em 15 min |
| Código inválido | Expira em **5 minutos**; solicite novo código |
| Telefone inválido | Use DDD + número, sem espaços (ex.: `21987654321`) |

Logs úteis: `Zenvia falhou` (credencial, remetente não aprovado, saldo) e `SMS (log)` em modo desenvolvimento.

### Notificações de status do pedido (opcional)

```env
NOTIFY_ORDER_SMS=1
```

Envia SMS ao cliente quando o status do pedido muda (mesmo provedor Zenvia). Mantenha `0` até validar OTP em produção.

### Alternativa: Twilio

Se já usar Twilio globalmente:

```env
SMS_PROVIDER=twilio
SMS_API_KEY=ACxxxxxxxx          # Account SID
SMS_API_SECRET=seu_auth_token
SMS_FROM_NUMBER=+5511999998888  # número Twilio em E.164
```

Documentação: [Twilio SMS](https://www.twilio.com/docs/sms).

## PIX em produção

### Por unidade (recomendado)

Em **Admin → Unidades → editar**, configure por filial:

- Provedor (`mock`, `efipay`, `mercadopago` ou vazio = herda `.env`)
- Chave PIX, credenciais Efi ou **Access Token** Mercado Pago
- Ative **PIX** e/ou **Cartão online** (cartão exige Mercado Pago com token válido)

### Provedores globais (`PIX_PROVIDER` no `.env`)

| Valor | Uso |
|-------|-----|
| `mock` | Desenvolvimento — QR fictício; confirmação manual via webhook |
| `efipay` | Efi Pay — PIX por chave da unidade ou global |
| `mercadopago` | Mercado Pago — PIX + cartão (Checkout Pro) |

### Cartão de crédito

- Checkout redireciona para o **Mercado Pago** (ambiente seguro).
- Webhook: `POST /webhooks/payment` (ou `/webhooks/pix` legado).
- Retorno: `/cliente/pedido/{id}/cartao/retorno`.

Sandbox Efi: `PIX_SANDBOX=1`.

### Webhook PIX

- **URL:** `POST /webhooks/pix` (também aceita query MP: `?topic=payment&id=123`)
- **Formatos aceitos (normalizados automaticamente):**
  - Genérico: `{"txid":"..."}` ou `{"external_id":"..."}`
  - **Efi:** `{"pix":[{"txid":"..."}]}`
  - **Mercado Pago:** `{"type":"payment","data":{"id":"..."}}` — o sistema consulta a API e confirma se `status=approved`
- **Idempotência:** reenvios retornam `200` com `duplicate: true`
- **Autenticação em produção** (pelo menos uma):
  - `PIX_WEBHOOK_SECRET` + header `X-Deskfood-Webhook-Secret` ou `Authorization: Bearer <segredo>`
  - `PIX_MP_WEBHOOK_SECRET` + assinatura `x-signature` do Mercado Pago
  - `PIX_EFI_TRUST_WEBHOOK=1` para webhooks Efi (recomende restringir IP no Nginx)

Em `APP_ENV=local` sem segredo, webhooks são aceitos para testes.

### Sincronização ativa (fallback)

- Na tela de pagamento PIX, poll em `/cliente/pedido/{id}/pix/status` consulta o gateway se ainda pendente.
- Cron opcional: `php bin/pix-sync-pending.php` a cada 2–5 min (`deploy/cron.example`).

Exemplo mock (desenvolvimento):

```bash
curl -X POST http://localhost:8080/webhooks/pix \
  -H "Content-Type: application/json" \
  -H "X-Deskfood-Webhook-Secret: seu_segredo_aqui" \
  -d '{"txid":"MOCK-substitua-pelo-external_id-do-pedido"}'
```

Eventos em `audit_logs` (`webhook.pix.*`).

## Motoboy

Cada motoboy recebe link `APP_URL/m/{access_token}` (listado no painel do operador). Ao marcar **entregue**, o pedido vai para status `entregue` e pagamentos na entrega são confirmados no caixa, se aplicável.

## LGPD

- Consentimentos no cadastro (termos, privacidade, SMS).
- Painel `/cliente/lgpd` com exportação JSON e anonimização.
- Páginas `/termos` e `/privacidade`.

## Ajuda e relatórios

- **Landing de marketing:** `GET /landing` — página comercial (demonstração, planos, comparativo). A home operacional para pedidos continua em `/`.
- **Central de ajuda:** `GET /ajuda` (FAQ e primeiros passos).
- **Export CSV (operador):** `GET /operador/relatorios/pedidos.csv` — últimos 90 dias da unidade logada (UTF-8 com BOM, separador `;`).

## Painel do operador (tempo real)

- `GET /operador/api/quadro-rev` — JSON com `rev` (hash do quadro) e contadores; usado para recarregar **Pedidos ao vivo** quando há mudança. Intervalo em `OPERATOR_BOARD_POLL_MS` no `.env` (0 desliga o auto-refresh).

## Estrutura principal

- `public/index.php` — front controller
- `app/` — controllers, services, middleware, helpers
- `config/` — rotas e parâmetros
- `database/migrations` — SQL numerado
- `database/seeds/initial_data.sql` — dados demo
- `views/` — camadas de apresentação
- `storage/logs` — logs da aplicação

## Cancelamento pelo cliente

Pedidos em status `pendente` ou `confirmado` (PIX não pago) podem ser cancelados em **Meus pedidos**.

## Checklist go-live

1. `cp .env.example .env` — preencher DB, `APP_SECRET`, `APP_URL` (HTTPS), `PIX_PROVIDER` real, credenciais PIX/SMS/e-mail e webhook (ver seções [SMS](#sms-zenvia-em-produção) e [PIX](#pix-em-produção)).
2. `composer install` e `php install.php` (aplica migrations, inclusive `leads`).
3. `ALLOW_INSTALL=0` em produção; backups: `chmod +x bin/backup-mysql.sh` e agendar no cron.
4. `MAIL_DRIVER=smtp` + `NOTIFY_ORDER_EMAIL=1` para avisar novo pedido no e-mail comercial.
5. `ANALYTICS_GA_ID` (opcional) para GA4 na landing e site público.
6. Testar `/health`, OTP cliente (Zenvia ou `log`), webhook PIX e painel operador (5 colunas + som).

## Deploy (Nginx + PHP-FPM)

Arquivos em `deploy/`:

- `nginx-deskfood.conf` — virtual host (docroot `public/`)
- `php-fpm-deskfood.conf` — pool PHP 8.3
- `cron.example` — backup e limpeza
- `.env.production.example` — modelo para o servidor

Passos resumidos:

```bash
cd /var/www/deskfood
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.production.example .env   # edite credenciais
php install.php
sudo cp deploy/nginx-deskfood.conf /etc/nginx/sites-available/deskfood
sudo cp deploy/php-fpm-deskfood.conf /etc/php/8.3/fpm/pool.d/deskfood.conf
sudo nginx -t && sudo systemctl reload nginx php8.3-fpm
sudo certbot --nginx -d deskfood.seudominio.com.br
```

## Produção

- `APP_ENV=production`, `APP_URL` com **HTTPS**, `APP_SECRET` forte (32+ caracteres).
- **`PIX_WEBHOOK_SECRET`** obrigatório; webhook em `POST /webhooks/payment` com header `X-Deskfood-Webhook-Secret` ou `Authorization: Bearer`.
- `ALLOW_INSTALL=0`; migrations via CLI: `php install.php`.
- Atrás de proxy: `TRUSTED_PROXIES` (IPs/CIDR) para IP real e cookie `secure`.
- Opcional: `HEALTH_TOKEN` + header `X-Health-Token` em `GET /health`.
- Credenciais MP/Efi por unidade são **cifradas** no banco (`SecretVault`); no formulário deixe o campo vazio para manter o valor atual.
- Links de motoboy: só hash no banco; o operador vê o URL **uma vez** ao criar/renovar.
- Cache de cardápio: `CATALOG_CACHE_TTL` (invalida ao editar cardápio no operador).
- Cron sugerido: `deploy/cron.example` (`payment-sync`, `cleanup-old-data`).
- Docroot **somente** `public/`; raiz do repo tem `.htaccess` negando acesso acidental.
- Permissões de escrita: `storage/`, `storage/cache/`, `public/uploads/`.

## Licença

Uso interno / proprietário — ajuste conforme sua empresa.
