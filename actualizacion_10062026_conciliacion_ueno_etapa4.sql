-- Etapa 4 - Panel tesoreria diaria Ueno
-- Ejecutar despues de actualizacion_10062026_conciliacion_ueno.sql y etapa 3.
-- Solo agrega permisos; no modifica pagos, caja ni movimientos existentes.

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'VERCIERRESTESORERIA', 'VER CIERRES TESORERIA', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='VERCIERRESTESORERIA' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'VERREPORTESFINANZAS', 'VER REPORTES FINANZAS', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='VERREPORTESFINANZAS' AND `tipo`='Administrativo');
