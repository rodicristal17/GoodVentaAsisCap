<?php

$ruta = '/var/www/html/php_system/abmOdontograma.php';
$contenido = file_get_contents($ruta);
if ($contenido === false) {
    exit(1);
}
$anterior = 'CONCAT_WS(CHAR(32),p.nombre_persona,p.apellido_persona)';
$nuevo = 'CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)';
$cantidad = substr_count($contenido, $anterior);
if ($cantidad !== 4) {
    fwrite(STDERR, 'Se esperaban 4 coincidencias y se encontraron '.$cantidad."\n");
    exit(2);
}
if (!copy($ruta, $ruta.'.bak-20260828-apellido-nombre')) {
    exit(3);
}
if (file_put_contents($ruta, str_replace($anterior, $nuevo, $contenido)) === false) {
    exit(4);
}
echo "Cambios aplicados: ".$cantidad."\n";

?>
