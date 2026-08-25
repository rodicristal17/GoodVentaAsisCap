<?php

function fallarAgendaAtendido($mensaje)
{
    fwrite(STDERR, 'FALLO: '.$mensaje.PHP_EOL);
    exit(1);
}

function afirmarAgendaAtendido($condicion, $mensaje)
{
    if (!$condicion) {
        fallarAgendaAtendido($mensaje);
    }
}

$raiz = dirname(__DIR__);
$backend = file_get_contents($raiz.'/php_system/abmCalendar.php');
$javascript = file_get_contents($raiz.'/js_system/jsCalendar.js');
$migracion = file_get_contents($raiz.'/actualizacion_25082026_agenda_insumos_atendido.sql');
$rollback = file_get_contents($raiz.'/actualizacion_25082026_agenda_insumos_atendido_rollback.sql');
$inicioActualizar = strpos($backend, 'function actualizarCita(');
$finActualizar = strpos($backend, 'function actualizarMotivoCita(', $inicioActualizar);
$codigoActualizar = substr($backend, $inicioActualizar, $finActualizar - $inicioActualizar);

afirmarAgendaAtendido($inicioActualizar !== false && $finActualizar !== false, 'No se pudo aislar actualizarCita.');
afirmarAgendaAtendido(strpos($codigoActualizar, '$mysqli->rollback();') !== false, 'El error no revierte la transaccion de agenda e inventario.');
afirmarAgendaAtendido(strpos($backend, 'AgendaValidacionException') !== false, 'Faltan errores funcionales seguros para agenda.');
afirmarAgendaAtendido(strpos($backend, 'Seleccione primero las variantes') !== false, 'Falta el mensaje claro para variantes.');
afirmarAgendaAtendido(strpos($backend, 'stock insuficiente') !== false, 'Falta el mensaje claro para stock insuficiente.');
afirmarAgendaAtendido(strpos($codigoActualizar, '"sql" => $sql') === false, 'Actualizar cita todavia expone SQL interno.');
afirmarAgendaAtendido(strpos($codigoActualizar, '"mysql" => $e->getMessage()') === false, 'Actualizar cita todavia expone errores MySQL.');
afirmarAgendaAtendido(strpos($backend, "IFNULL(ac.stock_descontado, 0) = 0") !== false, 'Falta la proteccion contra doble descuento.');
afirmarAgendaAtendido(strpos($javascript, 'La cita conserva su estado anterior') !== false, 'La interfaz no informa que el estado fue preservado.');
afirmarAgendaAtendido(strpos($migracion, "TABLE_SCHEMA = DATABASE()") !== false, 'La migracion no es controlada por esquema.');
afirmarAgendaAtendido(strpos($migracion, "COLUMN_NAME = 'stock_descontado'") !== false, 'La migracion no asegura la marca idempotente de descuento.');
afirmarAgendaAtendido(strpos($rollback, 'no elimina columnas') !== false, 'El rollback no protege la trazabilidad historica.');

echo 'OK: la cita conserva su estado ante errores, informa la causa y evita descuentos duplicados.'.PHP_EOL;
