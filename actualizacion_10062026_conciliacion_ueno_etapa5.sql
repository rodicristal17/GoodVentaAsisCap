-- Etapa 5 - Asignacion manual asistida Ueno
-- Ejecutar despues de actualizacion_10062026_conciliacion_ueno.sql, etapa 3 y etapa 4.
-- Solo agrega permisos; no modifica pagos, caja ni movimientos existentes.

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'ASIGNARMANUALUENO', 'ASIGNAR MANUALMENTE UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='ASIGNARMANUALUENO' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'VERASIGNACIONMANUALUENO', 'VER ASIGNACION MANUAL UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='VERASIGNACIONMANUALUENO' AND `tipo`='Administrativo');
