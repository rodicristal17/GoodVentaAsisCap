#!/usr/bin/env bash
set -Eeuo pipefail

DB="syscvxco_ac"
DUMP="/root/syscvxco_ac_27.sql"
ERROR_LOG="/root/import_syscvxco_ac_27.err"
BACKUP="/root/syscvxco_ac_antes_27_$(date +%Y%m%d_%H%M%S).sql"
APACHECTL="/usr/local/apps/apache2/bin/apachectl"
APACHE_DETENIDO=0

restaurar_apache() {
  if [[ "$APACHE_DETENIDO" -eq 1 ]]; then
    "$APACHECTL" -k start || true
  fi
}
trap restaurar_apache EXIT

if [[ ! -s "$DUMP" ]]; then
  echo "ERROR: no existe el respaldo nuevo o esta vacio: $DUMP" >&2
  exit 1
fi

command -v mysql >/dev/null 2>&1 || { echo "ERROR: no se encontro mysql" >&2; exit 1; }
command -v mysqldump >/dev/null 2>&1 || { echo "ERROR: no se encontro mysqldump" >&2; exit 1; }

if [[ -x "$APACHECTL" ]]; then
  "$APACHECTL" -k stop
  APACHE_DETENIDO=1
fi

echo "Creando respaldo previo: $BACKUP"
mysqldump --single-transaction --routines --events --triggers "$DB" > "$BACKUP"
if [[ ! -s "$BACKUP" ]]; then
  echo "ERROR: el respaldo previo quedo vacio; no se reemplazara la base" >&2
  exit 1
fi

echo "Recreando la base $DB"
mysql -e "DROP DATABASE IF EXISTS \`$DB\`; CREATE DATABASE \`$DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Importando $DUMP"
: > "$ERROR_LOG"
if ! mysql --default-character-set=utf8mb4 "$DB" < "$DUMP" 2> "$ERROR_LOG"; then
  echo "ERROR: la importacion fallo. Respaldo anterior: $BACKUP" >&2
  tail -n 50 "$ERROR_LOG" >&2 || true
  exit 1
fi

TABLAS="$(mysql -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB';")"
RUTINAS="$(mysql -N -e "SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema='$DB';")"
EVENTOS="$(mysql -N -e "SELECT COUNT(*) FROM information_schema.events WHERE event_schema='$DB';")"
TRIGGERS="$(mysql -N -e "SELECT COUNT(*) FROM information_schema.triggers WHERE trigger_schema='$DB';")"

echo "IMPORTACION_OK"
echo "respaldo_anterior=$BACKUP"
echo "tablas=$TABLAS rutinas=$RUTINAS eventos=$EVENTOS triggers=$TRIGGERS"
echo "errores_importacion=$(wc -l < "$ERROR_LOG")"
mysql -e "SHOW GRANTS FOR 'goodtech_clinident'@'localhost';" || true
