-- Indices para Resumen de flujo financiero (compatible con MySQL 5.6).
-- Cada bloque es idempotente porque valida INFORMATION_SCHEMA antes de alterar.

SET @idx_pago_fecha := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pago'
      AND INDEX_NAME = 'idx_pago_fecha_venta_monto'
);
SET @sql := IF(
    @idx_pago_fecha = 0,
    'ALTER TABLE pago ADD INDEX idx_pago_fecha_venta_monto (Fecha, cod_venta_fk, Monto)',
    'SELECT ''idx_pago_fecha_venta_monto ya existe'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_venta_codigo := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'venta'
      AND INDEX_NAME = 'idx_venta_cod_venta'
);
SET @sql := IF(
    @idx_venta_codigo = 0,
    'ALTER TABLE venta ADD INDEX idx_venta_cod_venta (cod_venta)',
    'SELECT ''idx_venta_cod_venta ya existe'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_gastos_flujo := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gastos'
      AND INDEX_NAME = 'idx_gastos_flujo_local_fecha_motivo'
);
SET @sql := IF(
    @idx_gastos_flujo = 0,
    'ALTER TABLE gastos ADD INDEX idx_gastos_flujo_local_fecha_motivo (cod_local, fecha, cod_motivoIngresoEgresoFK, estado)',
    'SELECT ''idx_gastos_flujo_local_fecha_motivo ya existe'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Reversion manual, solo si fuera necesaria:
-- ALTER TABLE pago DROP INDEX idx_pago_fecha_venta_monto;
-- ALTER TABLE venta DROP INDEX idx_venta_cod_venta;
-- ALTER TABLE gastos DROP INDEX idx_gastos_flujo_local_fecha_motivo;
