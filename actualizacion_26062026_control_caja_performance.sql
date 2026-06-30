DELIMITER $$

DROP PROCEDURE IF EXISTS add_index_if_missing_control_caja_perf$$
CREATE PROCEDURE add_index_if_missing_control_caja_perf(
    IN p_table_name VARCHAR(64),
    IN p_index_name VARCHAR(64),
    IN p_index_sql TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table_name
          AND INDEX_NAME = p_index_name
    ) THEN
        SET @sql_index = p_index_sql;
        PREPARE stmt_index FROM @sql_index;
        EXECUTE stmt_index;
        DEALLOCATE PREPARE stmt_index;
    END IF;
END$$

CALL add_index_if_missing_control_caja_perf(
    'arqueocaja',
    'idx_arqueocaja_control_fecha_estado',
    'ALTER TABLE arqueocaja ADD INDEX idx_arqueocaja_control_fecha_estado (fechaapertura, estado, idarqueocaja)'
)$$

CALL add_index_if_missing_control_caja_perf(
    'arqueocaja',
    'idx_arqueocaja_control_cierre_estado',
    'ALTER TABLE arqueocaja ADD INDEX idx_arqueocaja_control_cierre_estado (fechacierre, estado, idarqueocaja)'
)$$

CALL add_index_if_missing_control_caja_perf(
    'pago',
    'idx_pago_codapertura_monto_tipo_venta',
    'ALTER TABLE pago ADD INDEX idx_pago_codapertura_monto_tipo_venta (codApertura, Monto, cod_tipoPagoFK, cod_venta_fk)'
)$$

CALL add_index_if_missing_control_caja_perf(
    'gastos',
    'idx_gastos_codapertura_estado_tipo',
    'ALTER TABLE gastos ADD INDEX idx_gastos_codapertura_estado_tipo (codApertura, estado, tipo)'
)$$

CALL add_index_if_missing_control_caja_perf(
    'migrar_caja',
    'idx_migrar_caja_desde',
    'ALTER TABLE migrar_caja ADD INDEX idx_migrar_caja_desde (cod_caja_desdeFK)'
)$$

CALL add_index_if_missing_control_caja_perf(
    'migrar_caja',
    'idx_migrar_caja_hasta',
    'ALTER TABLE migrar_caja ADD INDEX idx_migrar_caja_hasta (cod_caja_hastaFK)'
)$$

DROP PROCEDURE IF EXISTS add_index_if_missing_control_caja_perf$$

DELIMITER ;
