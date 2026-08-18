# Despliegue de producción de Sistema Telar

El servidor ejecuta Apache/PHP y MariaDB mediante Docker Compose. La aplicación conserva su ruta histórica `/GoodVentaAsisCap/` y obtiene las credenciales de base de datos desde variables de entorno.

La versión objetivo de producción es PHP 8.5.9. La imagen anterior PHP 7.4 se conserva localmente para reversión controlada.

Los módulos históricos bajo `php/` conservan su contrato latin1 mediante `php_compat/encoding.php`, cargado automáticamente solo en PHP 8.x. Esto evita que las funciones obsoletas `utf8_encode` y `utf8_decode` contaminen respuestas JSON o HTML.

## Operación

```bash
cd /opt/telar/app/deploy/production
docker compose ps
docker compose logs --tail=100 web
docker compose logs --tail=100 database
docker compose up -d
```

La configuración secreta vive únicamente en `/opt/telar/app/deploy/production/.env`, con permisos `0600`. No debe incorporarse a Git ni copiarse al vault.

El directorio de Central Telefónica usa una credencial distinta de la lectura CDR. Debe comenzar con `TELAR_ISSABEL_DIRECTORY_ENABLED=false` y activarse únicamente después de validar en `--dry-run` un usuario MySQL con `SELECT` limitado a `asterisk.users`, `asterisk.devices` y `asterisk.queues_config`. Su ausencia no detiene la sincronización principal de llamadas.

## Acceso

- URL VPN/LAN: `http://10.220.100.201:8080/`
- La raíz redirige a `/GoodVentaAsisCap/system/login.html`.
- UFW permite SSH y HTTP solamente desde `10.220.100.0/24`.
- Cockpit: `https://10.220.100.201:9090/`, limitado a LAN/VPN.
- Portainer: `https://10.220.100.201:9443/`, limitado a LAN/VPN.

## Monitor interno de errores

- Los errores PHP se registran fuera del directorio público en el volumen `telar_error_logs`.
- Se conservan 30 días y se sanitizan contraseñas, tokens y cabeceras sensibles.
- No se almacenan cuerpos POST, cookies, diagnósticos ni historias clínicas.
- El permiso `VERERRORESSISTEMA` se crea en `NO` para usuarios y roles. Debe asignarse explícitamente desde el gestor de accesos.

## Respaldo y restauración

`backup.sh` genera un dump transaccional comprimido, valida el gzip, escribe SHA-256 y conserva 14 días. Para una restauración, detener primero el contenedor web, crear un respaldo previo y validar la restauración en un volumen separado antes de reemplazar producción.

## Rendimiento

- MariaDB reserva 2 GB para el buffer InnoDB y registra consultas que superen 1 segundo en `telar-slow.log`.
- PHP usa OPcache con 192 MB y validación de cambios cada 2 segundos.
- Apache comprime respuestas de texto y publica recursos estáticos con caché de 7 días.
- El fondo del login usa WebP con el JPG original como fallback.

## Pendiente conocido

La carpeta local `archivos/cierres_caja/evidencias` no fue copiada porque sus ACL de Windows impiden leer varios archivos. Debe transferirse posteriormente con una cuenta autorizada, sin cambiar permisos de documentos clínicos o financieros de manera improvisada.
