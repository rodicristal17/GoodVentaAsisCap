<?php

$base = isset($argv[1]) ? rtrim($argv[1], '/\\') : '/var/www/html';
$archivos = array(
    'php_system/abmclientes.php',
    'php_system/abmventa.php',
    'php_system/abmdetalleventa.php',
    'php_system/abmcreditos.php',
    'php_system/abmpagos.php',
    'php_system/ABMAgendamiento.php',
    'php_system/abmAgenda.php',
    'php_system/abmCobrarCuota.php',
    'php_system/abmCuotasSalteadas.php',
    'php_system/trabajo_laboratorio_historico_helper.php',
    'php_system/interconsulta_seguimiento_paciente_helper.php',
    'php_system/abmInterConsulta.php'
);

$total = 0;
foreach ($archivos as $relativa) {
    $ruta = $base.'/'.$relativa;
    if (!is_file($ruta)) {
        echo "OMITIDO ".$relativa."\n";
        continue;
    }
    $contenido = file_get_contents($ruta);
    $nuevo = $contenido;
    $cantidad = 0;

    /* Subconsultas de paciente usadas directamente como filtro LIKE. */
    $nuevo = preg_replace(
        '/CONCAT_WS\(CHAR\(32\),apellido_persona,nombre_persona\)(\s+from\s+persona\s+where[^\r\n]+?\)\s+like)/i',
        'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona,nombre_persona,apellido_persona)$1',
        $nuevo,
        -1,
        $c1
    );
    $cantidad += $c1;

    /* Alias de persona-paciente usados directamente como filtro LIKE. */
    $nuevo = preg_replace(
        '/CONCAT_WS\(CHAR\(32\),([a-z_][a-z0-9_]*)\.apellido_persona,\1\.nombre_persona\)(\s+LIKE)/i',
        'CONCAT_WS(CHAR(32),$1.apellido_persona,$1.nombre_persona,$1.nombre_persona,$1.apellido_persona)$2',
        $nuevo,
        -1,
        $c2
    );
    $cantidad += $c2;

    /* Buscadores compuestos que agregan telefono, factura o cobrador. */
    $nuevo = preg_replace(
        '/concat\(\(Select\s+CONCAT_WS\(CHAR\(32\),apellido_persona,nombre_persona\)(\s+from\s+persona\s+where[^\r\n]+?\))/i',
        'concat((Select CONCAT_WS(CHAR(32),apellido_persona,nombre_persona,nombre_persona,apellido_persona)$1',
        $nuevo,
        -1,
        $c3
    );
    $cantidad += $c3;

    if ($nuevo === $contenido) {
        echo "SIN_CAMBIOS ".$relativa."\n";
        continue;
    }
    $respaldo = $ruta.'.bak-20260828-busqueda-doble-orden';
    if (!is_file($respaldo) && !copy($ruta, $respaldo)) {
        fwrite(STDERR, "No se pudo respaldar ".$relativa."\n");
        exit(2);
    }
    if (file_put_contents($ruta, $nuevo) === false) {
        fwrite(STDERR, "No se pudo escribir ".$relativa."\n");
        exit(3);
    }
    $total += $cantidad;
    echo "OK ".$relativa." reemplazos=".$cantidad."\n";
}

echo "TOTAL reemplazos=".$total."\n";

?>
