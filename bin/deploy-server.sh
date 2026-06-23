#!/usr/bin/env bash
# Deploy Desk Food em VPS Linux (Ubuntu/Debian).
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/deskfood}"
BRANCH="${BRANCH:-main}"
PHP_BIN="${PHP_BIN:-php}"

echo "==> Atualizando código em ${APP_DIR}"
cd "$APP_DIR"
git fetch origin
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

echo "==> Dependências"
composer install --no-dev --optimize-autoloader --no-interaction
if [ -f package.json ]; then
  npm ci
  npm run build
fi

echo "==> Migrações"
$PHP_BIN install.php

echo "==> Permissões storage"
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || chmod -R 775 storage

echo "==> Auditoria produção"
$PHP_BIN bin/check-production.php

echo "==> Deploy concluído. Reinicie PHP-FPM/nginx se necessário."
