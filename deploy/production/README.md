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

Las llamadas iniciadas desde Telar y el reconocimiento entrante usan dos cuentas AMI separadas. Las claves se guardan solamente en `secrets/issabel_ami_event_secret` y `secrets/issabel_ami_originate_secret`; no se incorporan a `.env`, Git ni al vault. La cuenta de eventos no puede escribir y la cuenta de origenacion no recibe lectura de eventos. Si el servicio `central-telefonica-live` se detiene, MicroSIP y la sincronizacion CDR siguen funcionando de manera independiente. El procedimiento detallado esta en `README-central-telefonica-nivel1.md`.

## Acceso

- Proxmox: `https://10.220.100.200:8006/`, nodo `clinident`.
- VM `100 (cld-telar)`: Sistema Telar, IP `10.220.100.201`.
- VM `101 (cld-pbx)`: Issabel PBX. Mantenerla fuera de los despliegues de Telar salvo solicitud explícita.
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

Las evidencias nuevas de trabajos de laboratorio se guardan fuera del directorio público en el volumen `telar_lab_media`, montado como `/var/lib/telar/trabajos_laboratorio`. El contenedor web recibe esa ruta mediante `TELAR_LAB_MEDIA_DIR` y prepara el directorio para `www-data` con modo `0770`. Las fotografías continúan disponibles solamente mediante el endpoint autenticado del módulo.

Los adjuntos históricos de Hilos conservan su ruta compatible en `fotos/fotosMensaje`. Al iniciar, el servicio web prepara solamente esa carpeta con grupo `www-data` y modo `2775`, de modo que el usuario de despliegue conserva la propiedad y Apache puede crear archivos. El verificador `scripts/verificar_almacenamiento_adjuntos_hilos.php` crea, comprueba y elimina un archivo técnico como `www-data`. Si la preparación falla, Telar inicia igualmente, registra una advertencia y el endpoint informa que el almacenamiento no está disponible sin crear mensajes incompletos.

`backup.sh` genera dos respaldos coordinados: el dump transaccional comprimido de la base y el archivo de evidencias de laboratorio. Antes de publicar los resultados valida el gzip, la estructura del archivo de evidencias y ambos SHA-256. Si el volumen no está montado o no es legible, el proceso falla y elimina los temporales en vez de dejar un respaldo vacío como si fuera válido. Ambos conjuntos se conservan durante 14 días.

Para una restauración, detener primero el contenedor web, crear un respaldo previo y validar tanto la base como las evidencias en volúmenes separados antes de reemplazar producción. No restaurar rutas registradas en la base sin comprobar que sus archivos y miniaturas correspondientes estén presentes.

## Rendimiento

- MariaDB reserva 2 GB para el buffer InnoDB y registra consultas que superen 1 segundo en `telar-slow.log`.
- PHP usa OPcache con 192 MB y validación de cambios cada 2 segundos.
- Apache comprime respuestas de texto y publica recursos estáticos con caché de 7 días.
- El fondo del login usa WebP con el JPG original como fallback.

## Pendiente conocido

La carpeta local `archivos/cierres_caja/evidencias` no fue copiada porque sus ACL de Windows impiden leer varios archivos. Debe transferirse posteriormente con una cuenta autorizada, sin cambiar permisos de documentos clínicos o financieros de manera improvisada.
