<?php

$archivo = isset($argv[1]) ? $argv[1] : '';
if ($archivo === '' || !is_file($archivo)) {
    fwrite(STDERR, "Archivo objetivo no encontrado.\n");
    exit(1);
}

$contenido = file_get_contents($archivo);
$seleccionAnterior = "COALESCE(NULLIF(ip_seg.nombre_paciente_snapshot, ''), CONCAT_WS(CHAR(32),p_ic.apellido_persona,p_ic.nombre_persona)) as nombre_persona,";
$seleccionNueva = "COALESCE(\n"
    ."                NULLIF(NULLIF(TRIM(ip_seg.nombre_paciente_snapshot), ''), 'Paciente sin nombre'),\n"
    ."                NULLIF(TRIM(CONCAT_WS(CHAR(32),p_seg.apellido_persona,p_seg.nombre_persona)), ''),\n"
    ."                NULLIF(TRIM(CONCAT_WS(CHAR(32),p_ic.apellido_persona,p_ic.nombre_persona)), '')\n"
    ."            ) as nombre_persona,";
$joinAnterior = "            LEFT JOIN venta vt_ic ON vt_ic.cod_venta = ic.cod_ventaFK";
$joinNuevo = "            LEFT JOIN persona p_seg ON p_seg.cod_persona = ip_seg.cod_clienteFK_principal\n"
    .$joinAnterior;

if (strpos($contenido, "LEFT JOIN persona p_seg ON p_seg.cod_persona = ip_seg.cod_clienteFK_principal") !== false
    && strpos($contenido, "NULLIF(NULLIF(TRIM(ip_seg.nombre_paciente_snapshot)") !== false) {
    echo "YA_APLICADO\n";
    exit(0);
}
if (substr_count($contenido, $seleccionAnterior) !== 1 || substr_count($contenido, $joinAnterior) !== 1) {
    fwrite(STDERR, "No se encontro exactamente el bloque productivo esperado.\n");
    exit(2);
}

$contenido = str_replace($seleccionAnterior, $seleccionNueva, $contenido);
$contenido = str_replace($joinAnterior, $joinNuevo, $contenido);
if (file_put_contents($archivo, $contenido) === false) {
    fwrite(STDERR, "No se pudo escribir el archivo objetivo.\n");
    exit(3);
}
echo "APLICADO\n";

