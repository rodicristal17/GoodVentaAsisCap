-- Ejecutar en una ventana de bajo trafico y verificar primero el esquema activo.
-- Compatible con MySQL 5.6. No elimina ni modifica registros existentes.

DELIMITER $$

DROP PROCEDURE IF EXISTS add_index_if_missing_interconsulta_alertas$$
CREATE PROCEDURE add_index_if_missing_interconsulta_alertas(
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

CALL add_index_if_missing_interconsulta_alertas(
    'interconsulta_seguimiento_programado',
    'idx_seguimiento_alertas_responsable_estado_fecha',
    'ALTER TABLE interconsulta_seguimiento_programado ADD INDEX idx_seguimiento_alertas_responsable_estado_fecha (cod_responsableFK, estado, fecha_programada, cod_interConsultaFK)'
)$$

DROP PROCEDURE IF EXISTS add_index_if_missing_interconsulta_alertas$$

DELIMITER ;

-- Verificacion posterior:
-- SHOW INDEX FROM interconsulta_seguimiento_programado
-- WHERE Key_name = 'idx_seguimiento_alertas_responsable_estado_fecha';
