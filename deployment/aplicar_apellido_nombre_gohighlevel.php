<?php

$ruta = '/var/www/html/php_system/gohighlevel_contactos_telar_helper.php';
$contenido = file_get_contents($ruta);
if ($contenido === false) {
    fwrite(STDERR, "No se pudo leer el archivo.\n");
    exit(1);
}

$cambios = array(
    '$condiciones[] = "(p.nombre_persona LIKE \'".$like."\' OR ct.telefono_normalizado LIKE \'".$like."\')";' =>
        '$condiciones[] = "(TRIM(CONCAT_WS(CHAR(32),NULLIF(TRIM(p.apellido_persona),\'\'),NULLIF(TRIM(p.nombre_persona),\'\'))) LIKE \'".$like."\' "'."\r\n"
        .'            ."OR TRIM(CONCAT_WS(CHAR(32),NULLIF(TRIM(p.nombre_persona),\'\'),NULLIF(TRIM(p.apellido_persona),\'\'))) LIKE \'".$like."\' "'."\r\n"
        .'            ."OR p.nombre_persona LIKE \'".$like."\' "'."\r\n"
        .'            ."OR p.apellido_persona LIKE \'".$like."\' "'."\r\n"
        .'            ."OR ct.telefono_normalizado LIKE \'".$like."\')";',
    '"SELECT c.cod_cliente,IFNULL(p.nombre_persona,\'\') nombre,"' =>
        '"SELECT c.cod_cliente,TRIM(CONCAT_WS(CHAR(32),NULLIF(TRIM(p.apellido_persona),\'\'),NULLIF(TRIM(p.nombre_persona),\'\'))) nombre,"',
    'GROUP BY c.cod_cliente,p.nombre_persona,vt.cod_venta' =>
        'GROUP BY c.cod_cliente,p.nombre_persona,p.apellido_persona,vt.cod_venta'
);

foreach ($cambios as $anterior => $nuevo) {
    $cantidad = substr_count($contenido, $anterior);
    if ($cantidad !== 1) {
        fwrite(STDERR, "Coincidencias inesperadas: ".$cantidad." para ".$anterior."\n");
        exit(2);
    }
    $contenido = str_replace($anterior, $nuevo, $contenido);
}

if (file_put_contents($ruta, $contenido) === false) {
    fwrite(STDERR, "No se pudo escribir el archivo.\n");
    exit(3);
}

echo "Cambios aplicados: ".count($cambios)."\n";

?>
