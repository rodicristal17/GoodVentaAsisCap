DELIMITER $$

DROP PROCEDURE IF EXISTS add_index_if_missing_interconsulta_perf$$
CREATE PROCEDURE add_index_if_missing_interconsulta_perf(
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

CALL add_index_if_missing_interconsulta_perf(
    'mensaje',
    'idx_mensaje_interconsulta_estado_fecha',
    'ALTER TABLE mensaje ADD INDEX idx_mensaje_interconsulta_estado_fecha (cod_interConsultaFK, estado, fecha_creacion)'
)$$

CALL add_index_if_missing_interconsulta_perf(
    'mensaje',
    'idx_mensaje_interconsulta_fecha',
    'ALTER TABLE mensaje ADD INDEX idx_mensaje_interconsulta_fecha (cod_interConsultaFK, fecha_creacion)'
)$$

CALL add_index_if_missing_interconsulta_perf(
    'menciones',
    'idx_menciones_mensaje_usuario_leido',
    'ALTER TABLE menciones ADD INDEX idx_menciones_mensaje_usuario_leido (cod_mensajeFK, cod_usuarioFK, isLeido)'
)$$

CALL add_index_if_missing_interconsulta_perf(
    'menciones',
    'idx_menciones_usuario_mensaje_leido',
    'ALTER TABLE menciones ADD INDEX idx_menciones_usuario_mensaje_leido (cod_usuarioFK, cod_mensajeFK, isLeido)'
)$$

CALL add_index_if_missing_interconsulta_perf(
    'gastos',
    'idx_gastos_interconsulta_estado',
    'ALTER TABLE gastos ADD INDEX idx_gastos_interconsulta_estado (cod_interConsultaFK, estado)'
)$$

DROP PROCEDURE IF EXISTS add_index_if_missing_interconsulta_perf$$

DELIMITER ;
