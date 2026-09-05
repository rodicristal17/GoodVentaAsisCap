<?php

$base = isset($argv[1]) ? rtrim($argv[1], '/\\') : '/var/www/html';
$soloVistaPrevia = in_array('--dry-run', $argv, true);

/* Lineas auditadas individualmente: solo representan clientes o pacientes. */
$lineasVisuales = array(
    'php_system/abmCalendar.php' => array(238, 244, 828, 1355, 1461, 4637, 5022, 5533),
    'php_system/centro_facturas_helper.php' => array(2926, 2962),
    'php_system/mi_cartera_helper.php' => array(1321),
    'php_system/centro_legajo_pagares_helper.php' => array(430, 606, 697, 840),
    'php_system/centro_legajos_helper.php' => array(183, 674, 845, 1456),
    'php_system/gohighlevel_helper.php' => array(1301),
    'php_system/central_telefonica_operacion_helper.php' => array(411, 457, 474),
    'php_system/abmCentralTelefonica.php' => array(538),
    'php_system/abminforemcaja.php' => array(346),
    'php_system/abmaperturacierrecaja.php' => array(573),
    'php_system/gohighlevel_productividad_helper.php' => array(481),
    'php_system/abmConsulta.php' => array(514, 1794, 1889, 4588),
    'php_system/abmRecetarioIndicaciones.php' => array(340, 361, 395, 1478, 1479),
    'php_system/trabajo_laboratorio_helper.php' => array(1612, 2821, 4848, 6027),
    'php_system/abmSolicitudCredito.php' => array(821, 826),
    'php_system/abmConciliacionUeno.php' => array(4564)
);

/* En busquedas se agregan ambos ordenes en la misma cadena de comparacion. */
$lineasBusqueda = array(
    'php_system/abmCalendar.php' => array(230, 1362, 3439, 4622, 4970, 5514),
    'php_system/centro_legajos_helper.php' => array(593, 827),
    'php_system/mi_cartera_helper.php' => array(1282),
    'php_system/centro_legajo_pagares_helper.php' => array(592, 878),
    'php_system/central_telefonica_operacion_helper.php' => array(461),
    'php_system/abmConsulta.php' => array(5218, 6068),
    'php_system/trabajo_laboratorio_helper.php' => array(5982),
    'php_system/abmSolicitudCredito.php' => array(809),
    'php_system/abmclientes.php' => array(1434, 1584, 1769, 2067),
    'php_system/abmventa.php' => array(1194, 1501, 1915, 2103, 2265, 2388, 2713, 2862, 3569, 4114, 4529, 4612),
    'php_system/abmdetalleventa.php' => array(2817, 3029, 3212, 3404),
    'php_system/abmcreditos.php' => array(2756, 2881, 3348, 3603, 4486, 4507, 4528, 4548),
    'php_system/abmpagos.php' => array(2975, 2999, 3301),
    'php_system/ABMAgendamiento.php' => array(129),
    'php_system/abmAgenda.php' => array(254),
    'php_system/abmCobrarCuota.php' => array(251, 531),
    'php_system/abmCuotasSalteadas.php' => array(166),
    'php_system/trabajo_laboratorio_historico_helper.php' => array(1151)
);

function invertirNombrePacienteEnLinea($linea)
{
    $linea = str_replace(
        'CONCAT_WS(CHAR(32),nombre_persona,apellido_persona)',
        'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)',
        $linea
    );
    return preg_replace(
        '/CONCAT_WS\(CHAR\(32\),([a-z_][a-z0-9_]*\.)?nombre_persona,\1apellido_persona\)/i',
        'CONCAT_WS(CHAR(32),$1apellido_persona,$1nombre_persona)',
        $linea
    );
}

function habilitarBusquedaDobleEnLinea($linea)
{
    $linea = str_replace(
        'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona)',
        'CONCAT_WS(CHAR(32),apellido_persona,nombre_persona,nombre_persona,apellido_persona)',
        invertirNombrePacienteEnLinea($linea)
    );
    return preg_replace(
        '/CONCAT_WS\(CHAR\(32\),([a-z_][a-z0-9_]*\.)?apellido_persona,\1nombre_persona\)/i',
        'CONCAT_WS(CHAR(32),$1apellido_persona,$1nombre_persona,$1nombre_persona,$1apellido_persona)',
        $linea
    );
}

$archivos = array_unique(array_merge(array_keys($lineasVisuales), array_keys($lineasBusqueda)));
$totalArchivos = 0;
$totalLineas = 0;
foreach ($archivos as $relativa) {
    $ruta = $base.'/'.$relativa;
    if (!is_file($ruta)) {
        echo "OMITIDO ".$relativa."\n";
        continue;
    }
    $lineas = file($ruta);
    if ($lineas === false) {
        fwrite(STDERR, "No se pudo leer ".$relativa."\n");
        exit(2);
    }
    $original = implode('', $lineas);
    $cambios = 0;
    foreach (isset($lineasVisuales[$relativa]) ? $lineasVisuales[$relativa] : array() as $numero) {
        $indice = $numero - 1;
        if (isset($lineas[$indice])) {
            $nueva = invertirNombrePacienteEnLinea($lineas[$indice]);
            if ($nueva !== $lineas[$indice]) {
                $lineas[$indice] = $nueva;
                $cambios++;
            }
        }
    }
    foreach (isset($lineasBusqueda[$relativa]) ? $lineasBusqueda[$relativa] : array() as $numero) {
        $indice = $numero - 1;
        if (isset($lineas[$indice])) {
            $nueva = habilitarBusquedaDobleEnLinea($lineas[$indice]);
            if ($nueva !== $lineas[$indice]) {
                $lineas[$indice] = $nueva;
                $cambios++;
            }
        }
    }
    $nuevo = implode('', $lineas);
    if ($nuevo === $original) {
        echo "SIN_CAMBIOS ".$relativa."\n";
        continue;
    }
    if ($soloVistaPrevia) {
        echo "VISTA_PREVIA ".$relativa." lineas=".$cambios."\n";
        foreach (array_merge(
            isset($lineasVisuales[$relativa]) ? $lineasVisuales[$relativa] : array(),
            isset($lineasBusqueda[$relativa]) ? $lineasBusqueda[$relativa] : array()
        ) as $numero) {
            $indice = $numero - 1;
            if (isset($lineas[$indice]) && isset(file($ruta)[$indice]) && $lineas[$indice] !== file($ruta)[$indice]) {
                echo $relativa.':'.$numero.': '.trim($lineas[$indice])."\n";
            }
        }
        continue;
    }
    $respaldo = $ruta.'.bak-20260828-pacientes-por-linea';
    if (!is_file($respaldo) && !copy($ruta, $respaldo)) {
        fwrite(STDERR, "No se pudo respaldar ".$relativa."\n");
        exit(3);
    }
    if (file_put_contents($ruta, $nuevo) === false) {
        fwrite(STDERR, "No se pudo escribir ".$relativa."\n");
        exit(4);
    }
    $totalArchivos++;
    $totalLineas += $cambios;
    echo "OK ".$relativa." lineas=".$cambios."\n";
}

echo "TOTAL archivos=".$totalArchivos." lineas=".$totalLineas."\n";

?>
