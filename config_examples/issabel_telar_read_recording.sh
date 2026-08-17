#!/bin/bash

# Comando forzado para la clave SSH exclusiva del worker de transcripcion.
# Instalar fuera del repositorio en Issabel, por ejemplo en:
# /usr/local/sbin/issabel-telar-read-recording

set -f
PATH=/usr/bin:/bin
export PATH

referencia="${SSH_ORIGINAL_COMMAND:-}"
case "$referencia" in
  ''|*/*|*\\*|*..*) exit 64 ;;
esac

case "$referencia" in
  *.wav|*.WAV|*.mp3|*.MP3|*.m4a|*.M4A|*.ogg|*.OGG|*.flac|*.FLAC) ;;
  *) exit 64 ;;
esac

base=/var/spool/asterisk/monitor
archivo=$(find "$base" -type f -name "$referencia" -print -quit 2>/dev/null)
[ -n "$archivo" ] || exit 66
[ -f "$archivo" ] || exit 66

exec /bin/cat -- "$archivo"
