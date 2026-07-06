SET @next_pago_id := (SELECT IFNULL(MAX(idPago),0) FROM pago);

UPDATE pago
SET idPago = (@next_pago_id := @next_pago_id + 1)
WHERE idPago = 0
ORDER BY hora ASC, cod_venta_fk ASC, cod_creditoFK ASC, Monto ASC;

SET @pago_pk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'pago'
  AND CONSTRAINT_TYPE = 'PRIMARY KEY'
);
SET @sql := IF(@pago_pk = 0, 'ALTER TABLE pago ADD PRIMARY KEY (idPago)', 'SELECT ''pago primary key ok''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @pago_ai := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'pago'
  AND COLUMN_NAME = 'idPago'
  AND EXTRA LIKE '%auto_increment%'
);
SET @sql := IF(@pago_ai = 0, 'ALTER TABLE pago MODIFY idPago int(11) NOT NULL AUTO_INCREMENT', 'SELECT ''pago auto_increment ok''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @next_cobrar_auditoria_id := (SELECT IFNULL(MAX(id_auditoria),0) FROM cobrar_cuota_auditoria);

UPDATE cobrar_cuota_auditoria
SET id_auditoria = (@next_cobrar_auditoria_id := @next_cobrar_auditoria_id + 1)
WHERE id_auditoria = 0
ORDER BY fecha_hora ASC, cod_venta ASC, cod_creditoFK ASC, monto ASC, accion ASC;

SET @auditoria_pk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'cobrar_cuota_auditoria'
  AND CONSTRAINT_TYPE = 'PRIMARY KEY'
);
SET @sql := IF(@auditoria_pk = 0, 'ALTER TABLE cobrar_cuota_auditoria ADD PRIMARY KEY (id_auditoria)', 'SELECT ''cobrar_cuota_auditoria primary key ok''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @auditoria_ai := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'cobrar_cuota_auditoria'
  AND COLUMN_NAME = 'id_auditoria'
  AND EXTRA LIKE '%auto_increment%'
);
SET @sql := IF(@auditoria_ai = 0, 'ALTER TABLE cobrar_cuota_auditoria MODIFY id_auditoria int(11) NOT NULL AUTO_INCREMENT', 'SELECT ''cobrar_cuota_auditoria auto_increment ok''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @next_transferencia_id := (SELECT IFNULL(MAX(id),0) FROM pago_transferencia_conciliacion);

UPDATE pago_transferencia_conciliacion
SET id = (@next_transferencia_id := @next_transferencia_id + 1)
WHERE id = 0
ORDER BY fecha_hora_registro ASC, cod_pagoFK ASC, monto_pago ASC;

SET @transferencia_pk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'pago_transferencia_conciliacion'
  AND CONSTRAINT_TYPE = 'PRIMARY KEY'
);
SET @sql := IF(@transferencia_pk = 0, 'ALTER TABLE pago_transferencia_conciliacion ADD PRIMARY KEY (id)', 'SELECT ''pago_transferencia_conciliacion primary key ok''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @transferencia_ai := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'pago_transferencia_conciliacion'
  AND COLUMN_NAME = 'id'
  AND EXTRA LIKE '%auto_increment%'
);
SET @sql := IF(@transferencia_ai = 0, 'ALTER TABLE pago_transferencia_conciliacion MODIFY id int(11) NOT NULL AUTO_INCREMENT', 'SELECT ''pago_transferencia_conciliacion auto_increment ok''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @next_ueno_auditoria_id := (SELECT IFNULL(MAX(id_auditoria),0) FROM ueno_auditoria_conciliacion);

UPDATE ueno_auditoria_conciliacion
SET id_auditoria = (@next_ueno_auditoria_id := @next_ueno_auditoria_id + 1)
WHERE id_auditoria = 0
ORDER BY fecha_hora ASC, tabla_afectada ASC, registro_id ASC, accion ASC;

SET @ueno_auditoria_pk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'ueno_auditoria_conciliacion'
  AND CONSTRAINT_TYPE = 'PRIMARY KEY'
);
SET @sql := IF(@ueno_auditoria_pk = 0, 'ALTER TABLE ueno_auditoria_conciliacion ADD PRIMARY KEY (id_auditoria)', 'SELECT ''ueno_auditoria_conciliacion primary key ok''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @ueno_auditoria_ai := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'ueno_auditoria_conciliacion'
  AND COLUMN_NAME = 'id_auditoria'
  AND EXTRA LIKE '%auto_increment%'
);
SET @sql := IF(@ueno_auditoria_ai = 0, 'ALTER TABLE ueno_auditoria_conciliacion MODIFY id_auditoria int(11) NOT NULL AUTO_INCREMENT', 'SELECT ''ueno_auditoria_conciliacion auto_increment ok''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
