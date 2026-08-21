# Central Telefonica Nivel 1

Esta integracion permite iniciar una llamada desde Telar sin reemplazar MicroSIP. Telar solicita la llamada, suena la extension asociada al usuario y, cuando el usuario atiende, Issabel marca al telefono del paciente. Para llamadas entrantes, el conector identifica el numero y avisa al usuario de la extension que esta sonando.

## Separacion y minimo privilegio

Crear en Issabel dos usuarios AMI distintos, limitados por la red privada al servidor Telar:

- `telar_eventos`: `read=call`, `write=none`.
- `telar_origenacion`: `read=none`, `write=originate`.

Ambas deben usar `allowmultiplelogin=no`, negar primero `0.0.0.0/0.0.0.0` y permitir exclusivamente `10.220.100.201/255.255.255.255`. En `telar_eventos`, limitar además los eventos a `Newchannel`, `NewCallerid`, `DialBegin`, `DialEnd`, `Newstate`, `BridgeEnter` y `Hangup`.

No otorgar `system`, `command`, `config`, `agent`, `user`, `dtmf` ni permisos administrativos. La cuenta de origenacion sigue siendo una capacidad sensible: debe aceptar conexiones solamente desde la IP privada de Telar. No modificar extensiones, colas, troncales ni rutas para habilitar esta fase.

Guardar las claves, sin salto de linea adicional, en:

```text
/opt/telar/app/deploy/production/secrets/issabel_ami_event_secret
/opt/telar/app/deploy/production/secrets/issabel_ami_originate_secret
```

Los archivos deben ser legibles por el usuario `www-data` del contenedor y no deben incorporarse a Git.

## Validacion previa

1. Aplicar `actualizacion_20082026_central_telefonica_nivel1.sql` con respaldo dirigido previo.
2. Confirmar que cada usuario tenga una unica extension activa en el directorio de Telar.
3. Ejecutar dentro del contenedor:

```bash
php scripts/procesar_central_telefonica_tiempo_real.php --check
```

Confirmar también desde Issabel, sin mostrar secretos, que el usuario de eventos tenga sólo `read=call` y el de originación sólo `write=originate`, ambos con la ACL exclusiva de Telar.

4. Levantar solamente el servicio nuevo y revisar sus mensajes seguros:

```bash
docker compose up -d central-telefonica-live
docker compose logs --tail=100 central-telefonica-live
```

5. Probar primero con una extension y un numero interno de control. Luego probar un paciente de prueba sin datos clinicos reales.

## Reversion

Detener `central-telefonica-live` y aplicar `actualizacion_20082026_central_telefonica_nivel1_rollback.sql`. La reversion deshabilita el conector y restaura la proteccion del acceso historico; conserva las tablas de auditoria. MicroSIP y el CDR no dependen de esta capa.
