<?php

function pruebaFallar($mensaje)
{
    fwrite(STDERR, 'FALLO: '.$mensaje.PHP_EOL);
    exit(1);
}

function pruebaAfirmar($condicion, $mensaje)
{
    if (!$condicion) {
        pruebaFallar($mensaje);
    }
}

function pruebaCargarFuncion($archivo, $nombre)
{
    $codigo = file_get_contents($archivo);
    $inicio = strpos($codigo, 'function '.$nombre.'(');
    pruebaAfirmar($inicio !== false, 'No se encontro la funcion '.$nombre.'.');

    $inicioCuerpo = strpos($codigo, '{', $inicio);
    pruebaAfirmar($inicioCuerpo !== false, 'No se encontro el cuerpo de '.$nombre.'.');

    $nivel = 0;
    $fin = null;
    $largo = strlen($codigo);
    for ($i = $inicioCuerpo; $i < $largo; $i++) {
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

    pruebaAfirmar($fin !== null, 'No se pudo aislar la funcion '.$nombre.'.');
    eval(substr($codigo, $inicio, $fin - $inicio + 1));
}

$raiz = dirname(__DIR__);
$agenda = $raiz.'/php_system/abmCalendar.php';
$planificacion = $raiz.'/php_system/abmPlanificacionEspecialistas.php';
$consulta = $raiz.'/php_system/abmConsulta.php';
$usuarios = $raiz.'/php_system/abmusuarios.php';
$inventario = $raiz.'/php_system/abmInventarioLocal.php';
$tareas = $raiz.'/php_system/abmTareaProgramada.php';
$migracion = $raiz.'/actualizacion_17082026_agenda_estado_ausente.sql';
$rollback = $raiz.'/actualizacion_17082026_agenda_estado_ausente_rollback.sql';

foreach (array($agenda, $planificacion, $consulta, $usuarios, $inventario, $tareas, $migracion, $rollback) as $archivo) {
    pruebaAfirmar(is_file($archivo), 'Falta el archivo '.basename($archivo).'.');
}

pruebaCargarFuncion($agenda, 'normalizarEstadoCitaAgenda');
pruebaAfirmar(normalizarEstadoCitaAgenda('', 'AGENDADO') === 'AGENDADO', 'Agenda no aplica el estado inicial.');
pruebaAfirmar(normalizarEstadoCitaAgenda(' no_asistio ') === 'AUSENTE', 'Agenda no normaliza un alias de ausencia.');
pruebaAfirmar(normalizarEstadoCitaAgenda('desconocido') === false, 'Agenda admite un estado fuera del ENUM.');

pruebaCargarFuncion($usuarios, 'normalizarFechaHorarioUsuario');
pruebaCargarFuncion($usuarios, 'normalizarDiaHorarioUsuario');
pruebaAfirmar(normalizarFechaHorarioUsuario('2024-02-29') === '2024-02-29', 'Horario rechaza una fecha valida.');
pruebaAfirmar(normalizarFechaHorarioUsuario('2023-02-29') === null, 'Horario admite una fecha inexistente.');
pruebaAfirmar(normalizarDiaHorarioUsuario('MARTES') === 'martes', 'Horario no normaliza un dia valido.');
pruebaAfirmar(normalizarDiaHorarioUsuario('feriado') === false, 'Horario admite un dia fuera del ENUM.');

pruebaCargarFuncion($inventario, 'normalizarEnumInventarioLocal');
pruebaAfirmar(normalizarEnumInventarioLocal(' ACTIVO ', array('activo', 'inactivo')) === 'activo', 'Inventario no normaliza un estado valido.');
pruebaAfirmar(normalizarEnumInventarioLocal('pendiente', array('activo', 'inactivo')) === false, 'Inventario admite un estado no valido.');

pruebaCargarFuncion($tareas, 'normalizar_fecha_realizado_tarea_programada');
pruebaAfirmar(normalizar_fecha_realizado_tarea_programada('2026-08-17T10:30') === '2026-08-17 10:30:00', 'Tareas no normaliza datetime-local.');
pruebaAfirmar(normalizar_fecha_realizado_tarea_programada('2026-02-30 10:30') === false, 'Tareas admite una fecha inexistente.');
pruebaAfirmar(normalizar_fecha_realizado_tarea_programada('') === null, 'Tareas no conserva NULL para fecha vacia.');

$codigoPlanificacion = file_get_contents($planificacion);
pruebaAfirmar(substr_count($codigoPlanificacion, "'AUSENTE','AUSENCIA','NO_ASISTIO','NOASISTIO','NO ASISTIO'") >= 3, 'Planificacion no excluye ausencias en todas sus consultas de ocupacion.');
pruebaAfirmar(strpos($codigoPlanificacion, "UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO'") === false, 'Permanece una condicion de ocupacion incompatible.');

$codigoConsulta = file_get_contents($consulta);
pruebaAfirmar(strpos($codigoConsulta, "'AUSENTE','AUSENCIA','NO_ASISTIO','NOASISTIO','NO ASISTIO'") !== false, 'Consulta sigue considerando una ausencia como fecha planificada.');

$codigoUsuarios = file_get_contents($usuarios);
pruebaAfirmar(strpos($codigoUsuarios, '$mysqli->begin_transaction();') !== false, 'La sustitucion de horarios no inicia una transaccion.');
pruebaAfirmar(strpos($codigoUsuarios, 'Los horarios anteriores se conservaron.') !== false, 'La sustitucion de horarios no informa el rollback.');

$codigoInventario = file_get_contents($inventario);
pruebaAfirmar(strpos($codigoInventario, "isset(\$_POST['cod_marcaFK'])") !== false, 'Inventario sigue condicionando la marca con otro campo.');

$codigoMigracion = file_get_contents($migracion);
pruebaAfirmar(strpos($codigoMigracion, 'information_schema.COLUMNS') !== false, 'La migracion no comprueba el esquema actual.');
pruebaAfirmar(strpos($codigoMigracion, "LOCATE('''AUSENTE'''") !== false, 'La migracion no es idempotente.');
pruebaAfirmar(strpos($codigoMigracion, 'ausente_disponible') !== false, 'La migracion no entrega firma de verificacion.');

$codigoRollback = file_get_contents($rollback);
pruebaAfirmar(strpos($codigoRollback, '@telar_ausentes > 0') !== false, 'El rollback no protege las citas ausentes.');

echo 'OK: validaciones estrictas de Presupuesto, Agenda, Horarios, Inventario y Tareas verificadas.'.PHP_EOL;
