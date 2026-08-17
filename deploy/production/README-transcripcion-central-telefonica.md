# Transcripcion bajo demanda de Central Telefonica

Este componente procesa una sola llamada por vez. El contenedor web no recibe
la clave de OpenAI ni acceso SSH a Issabel; ambas credenciales pertenecen
exclusivamente al servicio `central-telefonica-transcription`.

## Preparacion privada en Telar

Crear `deploy/production/secrets` con propietario `33:33` y modo `0700`. Dentro
del directorio deben existir, sin agregarlos a Git:

- `issabel_audio_ssh_key`: clave privada exclusiva, modo `0400`.
- `issabel_known_hosts`: huella SSH verificada de Issabel, modo `0400`.

Agregar en `deploy/production/.env`:

```dotenv
TELAR_OPENAI_API_KEY=
TELAR_OPENAI_PROJECT=
TELAR_OPENAI_ORGANIZATION=
TELAR_ISSABEL_AUDIO_HOST=10.220.100.230
TELAR_ISSABEL_AUDIO_PORT=22
TELAR_ISSABEL_AUDIO_USER=telar_audio
```

La clave de API debe pertenecer a un proyecto exclusivo con presupuesto y
limites de uso. No debe copiarse al repositorio, al vault ni al contenedor web.

## Restriccion en Issabel

Instalar `config_examples/issabel_telar_read_recording.sh` como
`/usr/local/sbin/issabel-telar-read-recording`, propietario `root:root` y modo
`0755`. La clave publica exclusiva debe agregarse a `authorized_keys` con un
comando forzado equivalente a:

```text
restrict,command="/usr/local/sbin/issabel-telar-read-recording" ssh-ed25519 CLAVE_PUBLICA telar-transcription
```

El comando solo admite nombres de grabacion, busca dentro de
`/var/spool/asterisk/monitor` y entrega el contenido por la salida estandar. No
habilita shell, escritura, puertos, agente, X11 ni terminal.

## Activacion controlada

1. Ejecutar el respaldo privado de permisos.
2. Prevalidar la migracion sin cambios:
   `php scripts/aplicar_migracion_transcripcion_central_telefonica.php`.
3. Aplicarla con `--apply`.
4. Reconstruir la imagen para incorporar cURL y el cliente SSH.
5. Recrear solamente `central-telefonica-transcription` y, por los recursos
   versionados de la pantalla, `web`.
6. Confirmar que el servicio figure disponible antes de iniciar el piloto.

La grabacion temporal existe unicamente en el `tmpfs` privado del worker y se
elimina en un bloque `finally`, tanto si OpenAI responde como si ocurre un error.
El texto, segmentos, roles, consumo, costo estimado e historial se conservan en
Telar. En esta etapa no se calcula un puntaje de calidad.

## Datos enviados

Al solicitar una transcripcion, el audio completo se envia a OpenAI sin
anonimizar. Esta decision responde al alcance confirmado para control interno de
calidad; el acceso al boton, al texto y a los reintentos se limita al usuario
protegido de Carlos Faraone.
