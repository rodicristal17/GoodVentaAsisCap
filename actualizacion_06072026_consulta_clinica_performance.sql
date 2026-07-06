-- Optimiza la busqueda de Historial Clinico y Evolucion.
-- Compatible con MySQL 5.6: cada indice se agrega solo si todavia no existe.

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'detalle_venta');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'detalle_venta' AND INDEX_NAME = 'idx_detalle_venta_venta_producto');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE detalle_venta ADD INDEX idx_detalle_venta_venta_producto (cod_ventaFK, cod_productoFK)',
	'SELECT ''idx_detalle_venta_venta_producto ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'detalle_venta');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'detalle_venta' AND INDEX_NAME = 'idx_detalle_venta_cod_detalle');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE detalle_venta ADD INDEX idx_detalle_venta_cod_detalle (cod_detalle)',
	'SELECT ''idx_detalle_venta_cod_detalle ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'producto');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'producto' AND INDEX_NAME = 'idx_producto_cod_producto');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE producto ADD INDEX idx_producto_cod_producto (cod_producto)',
	'SELECT ''idx_producto_cod_producto ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento' AND INDEX_NAME = 'idx_plan_def_id');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE plan_definitivo_tratamiento ADD INDEX idx_plan_def_id (id)',
	'SELECT ''idx_plan_def_id ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento' AND INDEX_NAME = 'idx_plan_def_paciente_cedula_activo');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE plan_definitivo_tratamiento ADD INDEX idx_plan_def_paciente_cedula_activo (paciente_id, cedula, activo)',
	'SELECT ''idx_plan_def_paciente_cedula_activo ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento' AND INDEX_NAME = 'idx_plan_def_base_activo');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE plan_definitivo_tratamiento ADD INDEX idx_plan_def_base_activo (venta_base_id, activo)',
	'SELECT ''idx_plan_def_base_activo ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento_items');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento_items' AND INDEX_NAME = 'idx_plan_items_venta_activo_plan');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE plan_definitivo_tratamiento_items ADD INDEX idx_plan_items_venta_activo_plan (venta_id, activo, plan_definitivo_id)',
	'SELECT ''idx_plan_items_venta_activo_plan ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento_items');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento_items' AND INDEX_NAME = 'idx_plan_items_plan_activo_venta');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE plan_definitivo_tratamiento_items ADD INDEX idx_plan_items_plan_activo_venta (plan_definitivo_id, activo, venta_id)',
	'SELECT ''idx_plan_items_plan_activo_venta ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento_items');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_definitivo_tratamiento_items' AND INDEX_NAME = 'idx_plan_items_detalle_activo');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE plan_definitivo_tratamiento_items ADD INDEX idx_plan_items_detalle_activo (detalle_venta_id, activo)',
	'SELECT ''idx_plan_items_detalle_activo ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cliente');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cliente' AND INDEX_NAME = 'idx_cliente_estado_cod');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE cliente ADD INDEX idx_cliente_estado_cod (estado, cod_cliente)',
	'SELECT ''idx_cliente_estado_cod ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cliente');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cliente' AND INDEX_NAME = 'idx_cliente_ci');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE cliente ADD INDEX idx_cliente_ci (ci_cliente)',
	'SELECT ''idx_cliente_ci ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cliente');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cliente' AND INDEX_NAME = 'idx_cliente_rut');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE cliente ADD INDEX idx_cliente_rut (rut_cliente)',
	'SELECT ''idx_cliente_rut ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'venta');
SET @index_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'venta' AND INDEX_NAME = 'idx_venta_num_factura');
SET @sql := IF(@table_exists = 1 AND @index_exists = 0,
	'ALTER TABLE venta ADD INDEX idx_venta_num_factura (num_factura)',
	'SELECT ''idx_venta_num_factura ya existe o tabla ausente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
