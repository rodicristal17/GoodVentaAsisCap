-- Indices requeridos por las claves foraneas de ueno_movimiento_gasto.
-- MySQL 5.6 no soporta CREATE INDEX IF NOT EXISTS, por eso se valida INFORMATION_SCHEMA.

SET @index_exists := (
	SELECT COUNT(*)
	FROM INFORMATION_SCHEMA.STATISTICS
	WHERE TABLE_SCHEMA = DATABASE()
	  AND TABLE_NAME = 'gastos'
	  AND INDEX_NAME = 'idx_gastos_idgastos'
);
SET @sql := IF(@index_exists = 0,
	'ALTER TABLE gastos ADD INDEX idx_gastos_idgastos (idgastos)',
	'SELECT ''idx_gastos_idgastos ya existe''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @index_exists := (
	SELECT COUNT(*)
	FROM INFORMATION_SCHEMA.STATISTICS
	WHERE TABLE_SCHEMA = DATABASE()
	  AND TABLE_NAME = 'ueno_movimiento_bancario'
	  AND INDEX_NAME = 'idx_ueno_movimiento_id'
);
SET @sql := IF(@index_exists = 0,
	'ALTER TABLE ueno_movimiento_bancario ADD INDEX idx_ueno_movimiento_id (id_movimiento)',
	'SELECT ''idx_ueno_movimiento_id ya existe''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
