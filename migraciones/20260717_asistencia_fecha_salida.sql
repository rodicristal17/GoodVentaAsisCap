-- Clinident Salud
-- Conserva la fecha real de una salida cuando la jornada atraviesa medianoche.
-- No completa ni modifica registros historicos: solamente agrega estructura.

SET @clinident_tiene_fecha_salida := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asistencia'
      AND COLUMN_NAME = 'fecha_salida'
);
SET @clinident_sql_fecha_salida := IF(
    @clinident_tiene_fecha_salida = 0,
    'ALTER TABLE asistencia ADD COLUMN fecha_salida DATETIME NULL AFTER hora_salida',
    'SELECT 1'
);
PREPARE clinident_stmt_fecha_salida FROM @clinident_sql_fecha_salida;
EXECUTE clinident_stmt_fecha_salida;
DEALLOCATE PREPARE clinident_stmt_fecha_salida;

SET @clinident_tiene_indice_abierta := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asistencia'
      AND INDEX_NAME = 'idx_asistencia_usuario_abierta'
);
SET @clinident_sql_indice_abierta := IF(
    @clinident_tiene_indice_abierta = 0,
    'ALTER TABLE asistencia ADD INDEX idx_asistencia_usuario_abierta (cod_usuarioFK, hora_salida, fecha)',
    'SELECT 1'
);
PREPARE clinident_stmt_indice_abierta FROM @clinident_sql_indice_abierta;
EXECUTE clinident_stmt_indice_abierta;
DEALLOCATE PREPARE clinident_stmt_indice_abierta;
