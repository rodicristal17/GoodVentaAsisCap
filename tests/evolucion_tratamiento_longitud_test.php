<?php

function pruebaFallarEvolucion($mensaje)
{
    fwrite(STDERR, 'FALLO: '.$mensaje.PHP_EOL);
    exit(1);
}

function pruebaAfirmarEvolucion($condicion, $mensaje)
{
    if (!$condicion) {
        pruebaFallarEvolucion($mensaje);
    }
}

function pruebaCargarFuncionEvolucion($archivo, $nombre)
{
    $codigo = file_get_contents($archivo);
    $inicio = strpos($codigo, 'function '.$nombre.'(');
    pruebaAfirmarEvolucion($inicio !== false, 'No se encontro la funcion '.$nombre.'.');
    $inicioCuerpo = strpos($codigo, '{', $inicio);
    $nivel = 0;
    $fin = null;
    for ($i = $inicioCuerpo, $largo = strlen($codigo); $i < $largo; $i++) {
        if ($codigo[$i] === '{') {
            $nivel++;
        } elseif ($codigo[$i] === '}') {
            $nivel--;
            if ($nivel === 0) {
                $fin = $i;
                break;
            }
        }
    }
    pruebaAfirmarEvolucion($fin !== null, 'No se pudo aislar la funcion '.$nombre.'.');
    eval(substr($codigo, $inicio, $fin - $inicio + 1));
}

$raiz = dirname(__DIR__);
$consulta = $raiz.'/php_system/abmConsulta.php';
$inicio = $raiz.'/system/inicio.html';
$javascript = $raiz.'/js_system/consulta.js';
$migracion = $raiz.'/actualizacion_25082026_evolucion_tratamiento_observacion_1000.sql';
$rollback = $raiz.'/actualizacion_25082026_evolucion_tratamiento_observacion_1000_rollback.sql';

foreach (array($consulta, $inicio, $javascript, $migracion, $rollback) as $archivo) {
    pruebaAfirmarEvolucion(is_file($archivo), 'Falta el archivo '.basename($archivo).'.');
}

pruebaCargarFuncionEvolucion($consulta, 'longitudEvolucionTratamientoConsultaValida');
pruebaAfirmarEvolucion(longitudEvolucionTratamientoConsultaValida(str_repeat('a', 1000), 1000), 'Se rechazo una evolucion de 1.000 caracteres.');
pruebaAfirmarEvolucion(!longitudEvolucionTratamientoConsultaValida(str_repeat('a', 1001), 1000), 'Se admitio una evolucion mayor al limite.');

$codigoConsulta = file_get_contents($consulta);
pruebaAfirmarEvolucion(strpos($codigoConsulta, 'MySQL=".$stmtEvolucion->errno') !== false, 'El fallo de evolucion no conserva diagnostico tecnico en el log.');

$codigoInicio = file_get_contents($inicio);
pruebaAfirmarEvolucion(strpos($codigoInicio, 'contadorTrabajoRealizadoConsulta') !== false, 'Falta el contador accesible del formulario.');

$codigoJavascript = file_get_contents($javascript);
pruebaAfirmarEvolucion(strpos($codigoJavascript, 'LIMITE_EVOLUCION_TRATAMIENTO_CONSULTA = 1000') !== false, 'JavaScript no aplica el limite acordado.');

$codigoMigracion = file_get_contents($migracion);
pruebaAfirmarEvolucion(strpos($codigoMigracion, 'CHARACTER_MAXIMUM_LENGTH < 1000') !== false, 'La migracion no comprueba el tamano actual.');
pruebaAfirmarEvolucion(strpos($codigoMigracion, 'VARCHAR(1000)') !== false, 'La migracion no amplia la observacion a 1.000 caracteres.');

$codigoRollback = file_get_contents($rollback);
pruebaAfirmarEvolucion(strpos($codigoRollback, 'longitud_maxima <= 255') !== false, 'El rollback puede truncar observaciones extensas.');

echo 'OK: evolucion clinica admite hasta 1.000 caracteres con migracion y rollback seguros.'.PHP_EOL;
