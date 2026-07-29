-- Índices para la consulta paginada de mensajes por hilo.
-- Compatible con MySQL 5.6 / MariaDB.
-- Reversible:
--   ALTER TABLE mensaje DROP INDEX idx_mensaje_hilo_fecha_codigo;
--   ALTER TABLE mensaje DROP INDEX idx_mensaje_hilo_estado_fecha_codigo;

DELIMITER $$

DROP PROCEDURE IF EXISTS agregar_indice_mensaje_si_falta$$
CREATE PROCEDURE agregar_indice_mensaje_si_falta(
    IN p_indice VARCHAR(64),
    IN p_columnas VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'mensaje'
          AND INDEX_NAME = p_indice
    ) THEN
        SET @sql_indice = CONCAT(
            'ALTER TABLE mensaje ADD INDEX ',
            p_indice,
            ' (',
            p_columnas,
            ')'
        );
        PREPARE stmt_indice FROM @sql_indice;
        EXECUTE stmt_indice;
        DEALLOCATE PREPARE stmt_indice;
    END IF;
END$$

CALL agregar_indice_mensaje_si_falta(
    'idx_mensaje_hilo_fecha_codigo',
    'cod_interConsultaFK, fecha_creacion, cod_mensaje'
)$$

CALL agregar_indice_mensaje_si_falta(
    'idx_mensaje_hilo_estado_fecha_codigo',
    'cod_interConsultaFK, estado, fecha_creacion, cod_mensaje'
)$$

DROP PROCEDURE IF EXISTS agregar_indice_mensaje_si_falta$$

DELIMITER ;
