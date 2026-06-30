# Migracion GoodVentaAsisCap en Webuzo

## Rutas

- Usuario del VPS/Webuzo: `goodtech`
- Carpeta publica: `/home/goodtech/public_html`
- Carpeta del sistema: `/home/goodtech/public_html/GoodVentaAsisCap`
- URL esperada: `http://clinident.com.py/GoodVentaAsisCap/system/login.html`

## Base de datos

Base esperada por la app:

```text
syscvxco_ac
```

Usuario MySQL sugerido para el VPS:

```text
goodtech
```

La conexion quedo en el formato original del sistema. Si se quiere usar el
usuario MySQL `goodtech`, editar directamente estos archivos con la contrasena
real del VPS:

```text
php/conexion.php
php_system/conexion.php
php_system/importarDatos.php
```

## Importacion recomendada

Importar el dump limpio:

```text
syscvxco_ac_16_sin_definer.sql
```

o su version comprimida:

```text
syscvxco_ac_16_sin_definer.zip
```

Si la base quedo a medias por un intento anterior, vaciarla o eliminarla antes de reimportar.

## Permisos por SSH

```bash
chown -R goodtech:goodtech /home/goodtech/public_html/GoodVentaAsisCap
find /home/goodtech/public_html/GoodVentaAsisCap -type d -exec chmod 755 {} \;
find /home/goodtech/public_html/GoodVentaAsisCap -type f -exec chmod 644 {} \;
chmod -R 755 /home/goodtech/public_html/GoodVentaAsisCap/fotos /home/goodtech/public_html/GoodVentaAsisCap/archivos
```
