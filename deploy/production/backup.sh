#!/usr/bin/env bash
set -Eeuo pipefail
umask 027

DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="/opt/telar/backups/daily"

cd "$DEPLOY_DIR"
set -a
source ./.env
set +a

install -d -m 0750 "$BACKUP_DIR"
timestamp="$(date +%Y%m%d_%H%M%S)"
target="$BACKUP_DIR/${TELAR_DB_NAME}_${timestamp}.sql.gz"

docker compose exec -T \
  -e MYSQL_PWD="$TELAR_DB_ROOT_PASSWORD" \
  database mariadb-dump \
  --user=root \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  --hex-blob \
  "$TELAR_DB_NAME" | gzip -9 > "$target"

gzip -t "$target"
sha256sum "$target" > "${target}.sha256"
find "$BACKUP_DIR" -type f -name '*.sql.gz' -mtime +14 -delete
find "$BACKUP_DIR" -type f -name '*.sql.gz.sha256' -mtime +14 -delete

printf 'Backup verificado: %s\n' "$target"
