<?php

/**
 * Comprueba que la version declarada por la interfaz sea la unica version
 * activa en historialactualizacion.
 *
 * Uso:
 *   php scripts/verificar_historial_actualizacion.php
 *
 * Compatible con PHP 7.2. No imprime credenciales ni datos de dispositivos.
 */

require_once dirname(__DIR__).'/php_system/conexion.php';

function actualizacionFallar($mensaje)
{
    fwrite(STDERR, '[ERROR] '.$mensaje.PHP_EOL);
    exit(1);
}

function actualizacionOk($mensaje)
{
    fwrite(STDOUT, '[OK] '.$mensaje.PHP_EOL);
}

function actualizacionEscalar($mysqli, $sql)
{
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        actualizacionFallar('No se pudo consultar el historial de actualizacion.');
    }
    $fila = $resultado->fetch_row();
    $resultado->free();
    return $fila ? (string)$fila[0] : '';
}

$rutaInicio = dirname(__DIR__).'/js_system/inicio.js';
$fuenteInicio = file_get_contents($rutaInicio);
if ($fuenteInicio === false) {
    actualizacionFallar('No se pudo leer js_system/inicio.js.');
}

$coincidencias = array();
if (!preg_match(
    '/var\s+codigodeactualizacion\s*=\s*["\']([^"\']+)["\']\s*;/',
    $fuenteInicio,
    $coincidencias
)) {
    actualizacionFallar('No se encontro el codigo de actualizacion de la interfaz.');
}
$codigoInterfaz = trim($coincidencias[1]);

$mysqli = conectar_al_servidor();
if ($mysqli->connect_errno) {
    actualizacionFallar('No se pudo conectar con la base configurada.');
}

$cantidadActivas = intval(actualizacionEscalar(
    $mysqli,
    "SELECT COUNT(*) FROM historialactualizacion WHERE estado = 'Activo'"
));
$codigoActivo = actualizacionEscalar(
    $mysqli,
    "SELECT codigo FROM historialactualizacion
     WHERE estado = 'Activo'
     ORDER BY idhistorialactualizacion DESC
     LIMIT 1"
);
$cantidadCodigoInterfaz = intval(actualizacionEscalar(
    $mysqli,
    "SELECT COUNT(*) FROM historialactualizacion
     WHERE codigo = '".$mysqli->real_escape_string($codigoInterfaz)."'"
));

$mysqli->close();

if ($cantidadActivas !== 1) {
    actualizacionFallar('Debe existir exactamente una version activa.');
}
actualizacionOk('Existe exactamente una version activa.');

if ($codigoActivo !== $codigoInterfaz) {
    actualizacionFallar(
        'La interfaz solicita '.$codigoInterfaz.' y la base reconoce '.$codigoActivo.'.'
    );
}
actualizacionOk('La version activa coincide con '.$codigoInterfaz.'.');

if ($cantidadCodigoInterfaz !== 1) {
    actualizacionFallar('La version de la interfaz debe tener una unica fila historica.');
}
actualizacionOk('La version de la interfaz no esta duplicada.');

fwrite(STDOUT, 'RESULTADO: APROBADO'.PHP_EOL);

