CREATE TABLE IF NOT EXISTS tarea_historial (
    id INT(11) NOT NULL AUTO_INCREMENT,
    tarea_id INT(11) NOT NULL,
    usuario_id INT(11) NULL,
    accion VARCHAR(80) NOT NULL,
    campo VARCHAR(80) NULL,
    valor_anterior TEXT NULL,
    valor_nuevo TEXT NULL,
    motivo VARCHAR(255) NULL,
    origen VARCHAR(80) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    metadata_json LONGTEXT NULL,
    PRIMARY KEY (id),
    KEY idx_tarea_historial_tarea (tarea_id),
    KEY idx_tarea_historial_usuario (usuario_id),
    KEY idx_tarea_historial_fecha (created_at),
    KEY idx_tarea_historial_accion (accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$

DROP PROCEDURE IF EXISTS add_col_tareas_gantt $$
CREATE PROCEDURE add_col_tareas_gantt(IN p_columna VARCHAR(64), IN p_definicion TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tareas'
          AND COLUMN_NAME = p_columna
    ) THEN
        SET @sql_add_col = CONCAT('ALTER TABLE tareas ADD COLUMN ', p_definicion);
        PREPARE stmt_add_col FROM @sql_add_col;
        EXECUTE stmt_add_col;
        DEALLOCATE PREPARE stmt_add_col;
    END IF;
END $$

DROP PROCEDURE IF EXISTS add_idx_tareas_gantt $$
CREATE PROCEDURE add_idx_tareas_gantt(IN p_indice VARCHAR(64), IN p_definicion TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tareas'
          AND INDEX_NAME = p_indice
    ) THEN
        SET @sql_add_idx = CONCAT('ALTER TABLE tareas ADD INDEX ', p_definicion);
        PREPARE stmt_add_idx FROM @sql_add_idx;
        EXECUTE stmt_add_idx;
        DEALLOCATE PREPARE stmt_add_idx;
    END IF;
END $$

DELIMITER ;

CALL add_col_tareas_gantt('observacion', '`observacion` TEXT NULL AFTER `responsable`');
CALL add_col_tareas_gantt('prioridad', '`prioridad` VARCHAR(30) NOT NULL DEFAULT ''Normal'' AFTER `observacion`');
CALL add_col_tareas_gantt('culminada_por', '`culminada_por` INT(11) NULL AFTER `prioridad`');
CALL add_col_tareas_gantt('culminada_en', '`culminada_en` DATETIME NULL AFTER `culminada_por`');
CALL add_col_tareas_gantt('anulada_por', '`anulada_por` INT(11) NULL AFTER `culminada_en`');
CALL add_col_tareas_gantt('anulada_en', '`anulada_en` DATETIME NULL AFTER `anulada_por`');
CALL add_col_tareas_gantt('motivo_anulacion', '`motivo_anulacion` VARCHAR(255) NULL AFTER `anulada_en`');
CALL add_col_tareas_gantt('deleted_at', '`deleted_at` DATETIME NULL AFTER `motivo_anulacion`');
CALL add_col_tareas_gantt('created_by', '`created_by` INT(11) NULL AFTER `deleted_at`');
CALL add_col_tareas_gantt('updated_by', '`updated_by` INT(11) NULL AFTER `created_by`');
CALL add_col_tareas_gantt('created_at', '`created_at` DATETIME NULL AFTER `updated_by`');
CALL add_col_tareas_gantt('updated_at', '`updated_at` DATETIME NULL AFTER `created_at`');

CALL add_idx_tareas_gantt('idx_tareas_estado', 'idx_tareas_estado (`estado`)');
CALL add_idx_tareas_gantt('idx_tareas_deleted_at', 'idx_tareas_deleted_at (`deleted_at`)');
CALL add_idx_tareas_gantt('idx_tareas_fecha_inicio_fin', 'idx_tareas_fecha_inicio_fin (`fecha_inicio`, `fecha_fin`)');

DROP PROCEDURE IF EXISTS add_col_tareas_gantt;
DROP PROCEDURE IF EXISTS add_idx_tareas_gantt;
