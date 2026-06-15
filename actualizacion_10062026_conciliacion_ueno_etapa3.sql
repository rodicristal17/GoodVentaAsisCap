-- Etapa 3 - Conciliacion automatica Ueno
-- Ejecutar despues de actualizacion_10062026_conciliacion_ueno.sql.
-- Solo agrega permisos; no modifica pagos, caja ni movimientos existentes.

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'CONCILIARPAGOSUENO', 'CONCILIAR PAGOS UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='CONCILIARPAGOSUENO' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'OBSERVARPAGOUENO', 'OBSERVAR PAGO UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='OBSERVARPAGOUENO' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'REVISARPAGOUENO', 'REVISAR PAGO UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='REVISARPAGOUENO' AND `tipo`='Administrativo');
