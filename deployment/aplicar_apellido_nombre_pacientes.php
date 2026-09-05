<?php

$base = isset($argv[1]) ? rtrim($argv[1], '/\\') : '/var/www/html';
$sufijo = '.bak-20260828-apellido-nombre';
$reglas = array(
    'php_system/abmclientes.php' => array(
        'CONCAT_WS(CHAR(32),pr.nombre_persona,pr.apellido_persona)' => 'CONCAT_WS(CHAR(32),pr.apellido_persona,pr.nombre_persona)',
        'CONCAT_WS(CHAR(32),p.nombre_persona,p.apellido_persona)' => 'CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)',
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/abmventa.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/abmdetalleventa.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/abmcreditos.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/abmpagos.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/calcularInteresDirecto.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/calcularintereses.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/ABMAgendamiento.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/abmAgenda.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/abmgasto.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/abmConsulta.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/dashboard_flujo_financiero.php' => array(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)' => 'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)'
    ),
    'php_system/abmCobrarCuota.php' => array(
        'CONCAT_WS(CHAR(32),pe.nombre_persona,pe.apellido_persona)' => 'CONCAT_WS(CHAR(32),pe.apellido_persona,pe.nombre_persona)'
    ),
    'php_system/abmCuotasSalteadas.php' => array(
        'CONCAT_WS(CHAR(32),pe.nombre_persona,pe.apellido_persona)' => 'CONCAT_WS(CHAR(32),pe.apellido_persona,pe.nombre_persona)'
    ),
    'php_system/trabajo_laboratorio_historico_helper.php' => array(
        'CONCAT_WS(CHAR(32),pc.nombre_persona,pc.apellido_persona)' => 'CONCAT_WS(CHAR(32),pc.apellido_persona,pc.nombre_persona)'
    ),
    'php_system/interconsulta_seguimiento_paciente_helper.php' => array(
        'CONCAT_WS(CHAR(32),p.nombre_persona,p.apellido_persona)' => 'CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)',
        'CONCAT_WS(CHAR(32),p_dir.nombre_persona,p_dir.apellido_persona)' => 'CONCAT_WS(CHAR(32),p_dir.apellido_persona,p_dir.nombre_persona)'
    ),
    'php_system/cliente_venta_validacion_helper.php' => array(
        'CONCAT_WS(CHAR(32),p.nombre_persona,p.apellido_persona)' => 'CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)'
    ),
    'php_system/abmInterConsulta.php' => array(
        'CONCAT_WS(CHAR(32),paciente.nombre_persona,paciente.apellido_persona)' => 'CONCAT_WS(CHAR(32),paciente.apellido_persona,paciente.nombre_persona)',
        'CONCAT_WS(CHAR(32),p_sel.nombre_persona,p_sel.apellido_persona)' => 'CONCAT_WS(CHAR(32),p_sel.apellido_persona,p_sel.nombre_persona)',
        'CONCAT_WS(CHAR(32),p_conf.nombre_persona,p_conf.apellido_persona)' => 'CONCAT_WS(CHAR(32),p_conf.apellido_persona,p_conf.nombre_persona)',
        'CONCAT_WS(CHAR(32),p_ic.nombre_persona,p_ic.apellido_persona)' => 'CONCAT_WS(CHAR(32),p_ic.apellido_persona,p_ic.nombre_persona)'
    ),
    'php_system/centro_legajos_helper.php' => array(
        'CONCAT_WS(CHAR(32),p.nombre_persona,p.apellido_persona)' => 'CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)'
    ),
    'php_system/abmOdontograma.php' => array(
        'CONCAT_WS(CHAR(32),p.nombre_persona,p.apellido_persona)' => 'CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)'
    ),
    'php_system/abmRecetarioIndicaciones.php' => array(
        'CONCAT_WS(CHAR(32),p.nombre_persona,p.apellido_persona)' => 'CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)',
        'CONCAT_WS(CHAR(32),pt.nombre_persona,pt.apellido_persona)' => 'CONCAT_WS(CHAR(32),pt.apellido_persona,pt.nombre_persona)'
    ),
    'php_system/trabajo_laboratorio_helper.php' => array(
        'CONCAT_WS(CHAR(32),pc.nombre_persona,pc.apellido_persona)' => 'CONCAT_WS(CHAR(32),pc.apellido_persona,pc.nombre_persona)',
        'CONCAT_WS(CHAR(32),pp.nombre_persona,pp.apellido_persona)' => 'CONCAT_WS(CHAR(32),pp.apellido_persona,pp.nombre_persona)'
    ),
    'php_system/abmSolicitudCredito.php' => array(
        'CONCAT_WS(CHAR(32),pr.nombre_persona,pr.apellido_persona)' => 'CONCAT_WS(CHAR(32),pr.apellido_persona,pr.nombre_persona)'
    ),
    'php_system/abminforemcaja.php' => array(
        'CONCAT_WS(CHAR(32),pe.nombre_persona,pe.apellido_persona)' => 'CONCAT_WS(CHAR(32),pe.apellido_persona,pe.nombre_persona)'
    ),
    'php_system/abmaperturacierrecaja.php' => array(
        'CONCAT_WS(CHAR(32),pe.nombre_persona,pe.apellido_persona)' => 'CONCAT_WS(CHAR(32),pe.apellido_persona,pe.nombre_persona)'
    ),
    'php_system/gohighlevel_productividad_helper.php' => array(
        'CONCAT_WS(CHAR(32),p.nombre_persona,p.apellido_persona)' => 'CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)'
    ),
    'php_system/abmConciliacionUeno.php' => array(
        'CONCAT_WS(CHAR(32),per.nombre_persona,per.apellido_persona)' => 'CONCAT_WS(CHAR(32),per.apellido_persona,per.nombre_persona)'
    )
);

$archivosCambiados = 0;
$reemplazos = 0;
foreach ($reglas as $relativa => $cambios) {
    $ruta = $base.'/'.$relativa;
    if (!is_file($ruta)) {
        echo "OMITIDO ".$relativa." (no existe)\n";
        continue;
    }
    $contenido = file_get_contents($ruta);
    $nuevo = $contenido;
    $cantidadArchivo = 0;
    foreach ($cambios as $anterior => $posterior) {
        $cantidad = substr_count($nuevo, $anterior);
        if ($cantidad > 0) {
            $nuevo = str_replace($anterior, $posterior, $nuevo);
            $cantidadArchivo += $cantidad;
        }
    }
    if ($nuevo === $contenido) {
        echo "SIN_CAMBIOS ".$relativa."\n";
        continue;
    }
    if (!is_file($ruta.$sufijo) && !copy($ruta, $ruta.$sufijo)) {
        fwrite(STDERR, "No se pudo respaldar ".$relativa."\n");
        exit(2);
    }
    if (file_put_contents($ruta, $nuevo) === false) {
        fwrite(STDERR, "No se pudo escribir ".$relativa."\n");
        exit(3);
    }
    $archivosCambiados++;
    $reemplazos += $cantidadArchivo;
    echo "OK ".$relativa." reemplazos=".$cantidadArchivo."\n";
}

echo "TOTAL archivos=".$archivosCambiados." reemplazos=".$reemplazos."\n";

?>
