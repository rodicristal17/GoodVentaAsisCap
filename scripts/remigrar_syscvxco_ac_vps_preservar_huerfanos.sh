#!/usr/bin/env bash
set -Eeuo pipefail

DB="syscvxco_ac"
DUMP="/root/syscvxco_ac_27.sql"
ERROR_LOG="/root/import_syscvxco_ac_27_preservando_huerfanos.err"
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

if [[ ! -s /root/syscvxco_ac_antes_27_20260717_141054.sql ]]; then
  echo "ERROR: no se encontro el respaldo anterior de seguridad" >&2
  exit 1
fi

if [[ -x "$APACHECTL" ]]; then
  "$APACHECTL" -k stop
  APACHE_DETENIDO=1
fi

echo "Recreando la base $DB"
mysql -e "DROP DATABASE IF EXISTS \`$DB\`; CREATE DATABASE \`$DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Importando y preservando registros huerfanos"
: > "$ERROR_LOG"
if ! mysql --default-character-set=utf8mb4 --init-command="SET SESSION FOREIGN_KEY_CHECKS=0" "$DB" < "$DUMP" 2> "$ERROR_LOG"; then
  echo "ERROR: la importacion forzada tambien fallo" >&2
  tail -n 50 "$ERROR_LOG" >&2 || true
  exit 1
fi

TABLAS="$(mysql -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB';")"
RUTINAS="$(mysql -N -e "SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema='$DB';")"
EVENTOS="$(mysql -N -e "SELECT COUNT(*) FROM information_schema.events WHERE event_schema='$DB';")"
TRIGGERS="$(mysql -N -e "SELECT COUNT(*) FROM information_schema.triggers WHERE trigger_schema='$DB';")"
HUERFANOS_MOV="$(mysql -N "$DB" -e "SELECT COUNT(*) FROM ueno_movimiento_pago ump LEFT JOIN ueno_movimiento_bancario umb ON umb.id_movimiento=ump.id_movimiento WHERE umb.id_movimiento IS NULL;")"
HUERFANOS_PAGO="$(mysql -N "$DB" -e "SELECT COUNT(*) FROM ueno_movimiento_pago ump LEFT JOIN pago p ON p.idPago=ump.cod_pagoFK WHERE p.idPago IS NULL;")"

echo "IMPORTACION_OK_PRESERVANDO_HUERFANOS"
echo "tablas=$TABLAS rutinas=$RUTINAS eventos=$EVENTOS triggers=$TRIGGERS"
echo "huerfanos_id_movimiento=$HUERFANOS_MOV"
echo "huerfanos_cod_pago=$HUERFANOS_PAGO"
echo "errores_importacion=$(wc -l < "$ERROR_LOG")"
echo "respaldo_anterior=/root/syscvxco_ac_antes_27_20260717_141054.sql"
mysql -e "SHOW GRANTS FOR 'goodtech_clinident'@'localhost';" || true
