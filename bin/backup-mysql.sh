#!/usr/bin/env bash
# Backup MySQL do Desk Food — lê credenciais do .env na raiz do projeto.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="${ROOT}/.env"
OUT_DIR="${ROOT}/storage/backups"
mkdir -p "$OUT_DIR"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Arquivo .env não encontrado em $ROOT" >&2
  exit 1
fi

# shellcheck disable=SC1090
source <(grep -E '^DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)=' "$ENV_FILE" | sed 's/^/export /')

: "${DB_HOST:=127.0.0.1}"
: "${DB_PORT:=3306}"
: "${DB_DATABASE:?DB_DATABASE não definido}"
: "${DB_USERNAME:?DB_USERNAME não definido}"

STAMP="$(date +%Y%m%d-%H%M%S)"
FILE="${OUT_DIR}/${DB_DATABASE}-${STAMP}.sql.gz"

export MYSQL_PWD="${DB_PASSWORD:-}"
mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" \
  --single-transaction --routines --triggers "$DB_DATABASE" | gzip > "$FILE"
unset MYSQL_PWD

echo "Backup salvo: $FILE"
