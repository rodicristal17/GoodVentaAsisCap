-- Clinident Salud / Sistema Telar
-- Indices para la bandeja de Trabajos de laboratorio dental.
-- Compatible con MySQL 5.6. Idempotente.
--
-- Aplicacion controlada: ejecutar por ambiente y fuera del horario de mayor uso.
-- Esta migracion no modifica ni elimina registros.

SET @indice_existe := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'trabajo_laboratorio'
      AND INDEX_NAME = 'idx_tlab_estado_actualizacion'
);
SET @sql_indice := IF(
    @indice_existe = 0,
    'ALTER TABLE trabajo_laboratorio ADD INDEX idx_tlab_estado_actualizacion (estado_derivado, fecha_actualizacion, id)',
    'SELECT ''idx_tlab_estado_actualizacion ya existe'' AS resultado'
);
PREPARE stmt_indice FROM @sql_indice;
EXECUTE stmt_indice;
DEALLOCATE PREPARE stmt_indice;

SET @indice_existe := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'trabajo_laboratorio'
      AND INDEX_NAME = 'idx_tlab_tecnico_estado_actualizacion'
);
SET @sql_indice := IF(
    @indice_existe = 0,
    'ALTER TABLE trabajo_laboratorio ADD INDEX idx_tlab_tecnico_estado_actualizacion (cod_tecnico_usuarioFK, estado_derivado, fecha_actualizacion, id)',
    'SELECT ''idx_tlab_tecnico_estado_actualizacion ya existe'' AS resultado'
);
PREPARE stmt_indice FROM @sql_indice;
EXECUTE stmt_indice;
DEALLOCATE PREPARE stmt_indice;

SET @indice_existe := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'trabajo_laboratorio'
      AND INDEX_NAME = 'idx_tlab_custodio_estado_actualizacion'
);
SET @sql_indice := IF(
    @indice_existe = 0,
    'ALTER TABLE trabajo_laboratorio ADD INDEX idx_tlab_custodio_estado_actualizacion (cod_custodio_actualFK, estado_derivado, fecha_actualizacion, id)',
    'SELECT ''idx_tlab_custodio_estado_actualizacion ya existe'' AS resultado'
);
PREPARE stmt_indice FROM @sql_indice;
EXECUTE stmt_indice;
DEALLOCATE PREPARE stmt_indice;

-- Verificacion posterior:
SELECT
    INDEX_NAME,
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ', ') AS columnas
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'trabajo_laboratorio'
  AND INDEX_NAME IN (
      'idx_tlab_estado_actualizacion',
      'idx_tlab_tecnico_estado_actualizacion',
      'idx_tlab_custodio_estado_actualizacion'
  )
GROUP BY INDEX_NAME
ORDER BY INDEX_NAME;

-- Reversion manual, solamente si se confirma una regresion:
-- ALTER TABLE trabajo_laboratorio DROP INDEX idx_tlab_estado_actualizacion;
-- ALTER TABLE trabajo_laboratorio DROP INDEX idx_tlab_tecnico_estado_actualizacion;
-- ALTER TABLE trabajo_laboratorio DROP INDEX idx_tlab_custodio_estado_actualizacion;
