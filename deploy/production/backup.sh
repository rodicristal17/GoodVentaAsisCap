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
db_target="$BACKUP_DIR/${TELAR_DB_NAME}_${timestamp}.sql.gz"
media_target="$BACKUP_DIR/${TELAR_DB_NAME}_laboratorio_${timestamp}.tar.gz"
db_tmp="${db_target}.tmp"
media_tmp="${media_target}.tmp"

cleanup() {
  rm -f -- "$db_tmp" "$media_tmp"
}
trap cleanup EXIT

docker compose exec -T \
  -e MYSQL_PWD="$TELAR_DB_ROOT_PASSWORD" \
  database mariadb-dump \
  --user=root \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  --hex-blob \
  "$TELAR_DB_NAME" | gzip -9 > "$db_tmp"

test -s "$db_tmp"
gzip -t "$db_tmp"

docker compose exec -T web sh -ceu '
  test "${TELAR_LAB_MEDIA_DIR:-}" = "/var/lib/telar/trabajos_laboratorio"
  test -d "$TELAR_LAB_MEDIA_DIR"
  test -r "$TELAR_LAB_MEDIA_DIR"
  test -x "$TELAR_LAB_MEDIA_DIR"
  tar -C /var/lib/telar -czf - trabajos_laboratorio
' > "$media_tmp"

test -s "$media_tmp"
tar -tzf "$media_tmp" >/dev/null
tar -tzf "$media_tmp" | awk '
  $0 == "trabajos_laboratorio" || $0 == "trabajos_laboratorio/" { found = 1 }
  END { exit found ? 0 : 1 }
'

mv -- "$db_tmp" "$db_target"
mv -- "$media_tmp" "$media_target"
sha256sum "$db_target" > "${db_target}.sha256"
sha256sum "$media_target" > "${media_target}.sha256"

find "$BACKUP_DIR" -type f -name '*.sql.gz' -mtime +14 -delete
find "$BACKUP_DIR" -type f -name '*.sql.gz.sha256' -mtime +14 -delete
find "$BACKUP_DIR" -type f -name '*_laboratorio_*.tar.gz' -mtime +14 -delete
find "$BACKUP_DIR" -type f -name '*_laboratorio_*.tar.gz.sha256' -mtime +14 -delete

trap - EXIT
printf 'Backup de base verificado: %s\n' "$db_target"
printf 'Backup de evidencias verificado: %s\n' "$media_target"
