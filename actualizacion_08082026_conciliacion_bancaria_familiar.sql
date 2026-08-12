-- Conciliacion bancaria - incorporacion de Banco Familiar
-- Aditiva y compatible con los registros historicos Ueno.
-- Ejecutar con respaldo previo de las tablas afectadas.

SET @schema_actual = DATABASE();

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@schema_actual AND TABLE_NAME='ueno_importacion_extracto' AND COLUMN_NAME='banco_codigo'),
  'SELECT 1',
  "ALTER TABLE ueno_importacion_extracto ADD COLUMN banco_codigo varchar(20) NOT NULL DEFAULT 'UENO' AFTER id_importacion"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@schema_actual AND TABLE_NAME='ueno_importacion_extracto' AND COLUMN_NAME='moneda_codigo'),
  'SELECT 1',
  "ALTER TABLE ueno_importacion_extracto ADD COLUMN moneda_codigo varchar(10) NOT NULL DEFAULT 'PYG' AFTER denominacion"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@schema_actual AND TABLE_NAME='ueno_importacion_extracto' AND COLUMN_NAME='saldo_anterior'),
  'SELECT 1',
  'ALTER TABLE ueno_importacion_extracto ADD COLUMN saldo_anterior bigint(20) DEFAULT NULL AFTER total_debitos'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@schema_actual AND TABLE_NAME='ueno_importacion_extracto' AND COLUMN_NAME='saldo_final'),
  'SELECT 1',
  'ALTER TABLE ueno_importacion_extracto ADD COLUMN saldo_final bigint(20) DEFAULT NULL AFTER saldo_anterior'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@schema_actual AND TABLE_NAME='ueno_movimiento_bancario' AND COLUMN_NAME='banco_codigo'),
  'SELECT 1',
  "ALTER TABLE ueno_movimiento_bancario ADD COLUMN banco_codigo varchar(20) NOT NULL DEFAULT 'UENO' AFTER id_importacion"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@schema_actual AND TABLE_NAME='pago_transferencia_conciliacion' AND COLUMN_NAME='banco_codigo'),
  'SELECT 1',
  "ALTER TABLE pago_transferencia_conciliacion ADD COLUMN banco_codigo varchar(20) NOT NULL DEFAULT 'UENO' AFTER grupo_pago"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE ueno_importacion_extracto SET banco_codigo='UENO' WHERE banco_codigo IS NULL OR TRIM(banco_codigo)='';
UPDATE ueno_movimiento_bancario SET banco_codigo='UENO' WHERE banco_codigo IS NULL OR TRIM(banco_codigo)='';
UPDATE pago_transferencia_conciliacion SET banco_codigo='UENO' WHERE banco_codigo IS NULL OR TRIM(banco_codigo)='';

UPDATE pago_transferencia_conciliacion pc
INNER JOIN ueno_movimiento_pago ump ON ump.cod_pagoFK=pc.cod_pagoFK AND ump.estado='activo'
INNER JOIN ueno_movimiento_bancario mv ON mv.id_movimiento=ump.id_movimiento
SET pc.banco_codigo=mv.banco_codigo
WHERE pc.activo='SI';

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@schema_actual AND TABLE_NAME='ueno_importacion_extracto' AND INDEX_NAME='idx_ueno_importacion_banco_fecha'),
  'SELECT 1',
  'ALTER TABLE ueno_importacion_extracto ADD KEY idx_ueno_importacion_banco_fecha (banco_codigo, fecha_extracto)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@schema_actual AND TABLE_NAME='ueno_movimiento_bancario' AND INDEX_NAME='idx_ueno_movimiento_banco_cuenta_fecha'),
  'SELECT 1',
  'ALTER TABLE ueno_movimiento_bancario ADD KEY idx_ueno_movimiento_banco_cuenta_fecha (banco_codigo, cuenta, fecha_confirmacion)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@schema_actual AND TABLE_NAME='pago_transferencia_conciliacion' AND INDEX_NAME='idx_pago_transferencia_banco_estado'),
  'SELECT 1',
  'ALTER TABLE pago_transferencia_conciliacion ADD KEY idx_pago_transferencia_banco_estado (banco_codigo, estado_conciliacion)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE listadodeacceso
SET formulario='FORMULARIO CONCILIACION BANCARIA'
WHERE codigo IN ('VERCONCILIACIONUENO','IMPORTAREXTRACTOUENO','VEREXTRACTOSUENO','VERPAGOSPENDIENTESUENO','VERAUDITORIAUENO');

UPDATE listadodeacceso SET nombre='IMPORTAR EXTRACTO BANCARIO' WHERE codigo='IMPORTAREXTRACTOUENO';
UPDATE listadodeacceso SET nombre='VER EXTRACTOS BANCARIOS' WHERE codigo='VEREXTRACTOSUENO';
