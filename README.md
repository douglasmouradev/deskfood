# Desk Food

SaaS web de delivery em **PHP 8.3** (MVC manual, sem framework), **MySQL 8**, front com **Tailwind CDN** + **Alpine.js** e identidade própria. Pronto para VPS, shared hosting ou `php -S` com roteador embutido.

## Requisitos

- PHP 8.3+ (extensões `pdo_mysql`, `openssl`, `json`, `mbstring`, `curl`)
- MySQL 8.0+
- Composer 2
- Apache com `mod_rewrite` **ou** Nginx apontando o docroot para `public/`

## Instalação rápida

```bash
cd deskfood
cp .env.example .env
# Ajuste DB_*, APP_URL, APP_SECRET, INSTALL_KEY, SMS_*, PIX_* e, se quiser, APP_COMMERCIAL_* e OPERATOR_BOARD_POLL_MS
composer install
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

## Credenciais de demonstração (seed)

| Papel    | E-mail                 | Senha     |
|----------|------------------------|-----------|
| Dono     | `dono@deskfood.local`  | `Admin123!` |
| Operador | `operador@deskfood.local` | `Admin123!` |

Cliente: fluxo **OTP** — com `SMS_PROVIDER=log` o código aparece em `storage/logs/app-YYYY-MM-DD.log`.

## Webhook PIX

- **URL:** `POST /webhooks/pix`
- **Corpo:** JSON com `txid` ou `external_id` igual a `pix_transactions.external_id` (modo mock: prefixo `MOCK-`).
- **Idempotência:** reenvios com o mesmo `txid` retornam `200` com `duplicate: true` sem alterar o pedido duas vezes.
- **Segredo (produção):** defina `PIX_WEBHOOK_SECRET` no `.env` e envie o mesmo valor em:
  - cabeçalho `X-Deskfood-Webhook-Secret`, **ou**
  - `Authorization: Bearer <segredo>`

Sem `PIX_WEBHOOK_SECRET` definido, o endpoint aceita chamadas sem autenticação (apenas para desenvolvimento).

Exemplo com segredo:

```bash
curl -X POST http://localhost:8080/webhooks/pix \
  -H "Content-Type: application/json" \
  -H "X-Deskfood-Webhook-Secret: seu_segredo_aqui" \
  -d '{"txid":"MOCK-substitua-pelo-txid-real"}'
```

Eventos são registrados em `audit_logs` (`webhook.pix.*`).

## Motoboy

Cada motoboy recebe link `APP_URL/m/{access_token}` (listado no painel do operador). Ao marcar **entregue**, o pedido vai para status `entregue` e pagamentos na entrega são confirmados no caixa, se aplicável.

## LGPD

- Consentimentos no cadastro (termos, privacidade, SMS).
- Painel `/cliente/lgpd` com exportação JSON e anonimização.
- Páginas `/termos` e `/privacidade`.

## Ajuda e relatórios

- **Landing de produto:** `GET /landing` (marketing; a home operacional de unidades continua em `/`).
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

## Produção

- Defina `APP_ENV=production`, `APP_URL` com HTTPS e `APP_SECRET` forte (32+ caracteres aleatórios).
- Defina **`PIX_WEBHOOK_SECRET`** e configure o gateway para enviar o cabeçalho correspondente.
- Defina `ALLOW_INSTALL=0` e use apenas CLI para migrations (`php install.php` ainda aplica SQL; seed use `--force` se necessário).
- Limite de login: `LOGIN_RATE_MAX` e `LOGIN_RATE_WINDOW` (segundos) no `.env`.
- Garanta permissões de escrita em `storage/` e `public/uploads/`.
- Restrinja acesso a `install.php` (não coloque em docroot público se copiar o projeto inteiro para o servidor).

## Licença

Uso interno / proprietário — ajuste conforme sua empresa.
