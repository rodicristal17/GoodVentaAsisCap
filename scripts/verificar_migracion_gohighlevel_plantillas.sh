#!/bin/sh
set -eu

RAIZ_PREDETERMINADA=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
RAIZ_APP=${TELAR_PRUEBA_RAIZ_APP:-$RAIZ_PREDETERMINADA}
RAIZ_ARCHIVOS=${TELAR_PRUEBA_RAIZ_ARCHIVOS:-$RAIZ_APP}
COMPOSE="$RAIZ_APP/deploy/production/compose.yml"

limpiar() {
  docker compose -f "$COMPOSE" exec -T database sh -lc \
    'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS telar_codex_test_plantillas"' >/dev/null 2>&1 || true
}
trap limpiar EXIT INT TERM

limpiar
docker compose -f "$COMPOSE" exec -T database sh -lc \
  'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" -e "CREATE DATABASE telar_codex_test_plantillas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"'

docker compose -f "$COMPOSE" exec -T database sh -lc \
  'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" telar_codex_test_plantillas' \
  < "$RAIZ_ARCHIVOS/scripts/fixtures/gohighlevel_plantillas_schema_minimo.sql"

docker compose -f "$COMPOSE" exec -T database sh -lc \
  'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" telar_codex_test_plantillas' \
  < "$RAIZ_ARCHIVOS/actualizacion_23082026_gohighlevel_plantillas_whatsapp.sql"

resultado=$(docker compose -f "$COMPOSE" exec -T database sh -lc \
  'mariadb -N -uroot -p"$MARIADB_ROOT_PASSWORD" -e "SELECT CONCAT((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=\"telar_codex_test_plantillas\" AND table_name=\"gohighlevel_permiso_usuario\" AND column_name=\"puede_enviar_plantilla\"),\":\",(SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=\"telar_codex_test_plantillas\" AND table_name IN (\"gohighlevel_plantilla_config\",\"gohighlevel_envio_plantilla\")),\":\",(SELECT puede_enviar_plantilla FROM telar_codex_test_plantillas.gohighlevel_permiso_usuario WHERE cod_usuarioFK=5994))"')

if [ "$resultado" != "1:2:1" ]; then
  echo "Migracion de plantillas incompleta: $resultado" >&2
  exit 1
fi

docker compose -f "$COMPOSE" exec -T database sh -lc \
  'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" telar_codex_test_plantillas' \
  < "$RAIZ_ARCHIVOS/actualizacion_23082026_gohighlevel_plantillas_whatsapp_rollback.sql"

resultado_rollback=$(docker compose -f "$COMPOSE" exec -T database sh -lc \
  'mariadb -N -uroot -p"$MARIADB_ROOT_PASSWORD" -e "SELECT CONCAT((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=\"telar_codex_test_plantillas\" AND table_name=\"gohighlevel_permiso_usuario\" AND column_name=\"puede_enviar_plantilla\"),\":\",(SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=\"telar_codex_test_plantillas\" AND table_name IN (\"gohighlevel_plantilla_config\",\"gohighlevel_envio_plantilla\")))"')

if [ "$resultado_rollback" != "0:0" ]; then
  echo "Rollback de plantillas incompleto: $resultado_rollback" >&2
  exit 1
fi

echo "GoHighLevel plantillas: migracion y rollback correctos."
