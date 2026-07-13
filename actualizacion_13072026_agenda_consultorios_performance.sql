-- Agenda de consultorios: indices para lectura por fecha y relaciones clinicas.
-- Compatible con MySQL 8 / InnoDB. Idempotente por nombre de indice.
-- Ejecutar en horario de baja actividad. Si expira lock_wait_timeout, reprogramar.
-- Prerrequisitos estructurales: actualizacion_060626_agenda_insumos.sql,
-- actualizacion_10062026_variantes_insumos.sql y
-- actualizacion_15062026_asignacion_consultorios.sql.

SET SESSION lock_wait_timeout = 15;

-- Preflight: el resultado debe ser "OK" antes de continuar con un despliegue.
SELECT IF(COUNT(c.column_name) = 11, 'OK', 'ERROR: faltan estructuras previas de agenda') AS preflight_agenda
FROM (
    SELECT 'agenda_tratamientos' AS tabla, 'id_agenda' AS columna
    UNION ALL SELECT 'agenda_insumo_base', 'id_insumo'
    UNION ALL SELECT 'insumo_producto', 'cod_producto'
    UNION ALL SELECT 'agenda_consumo_insumos', 'id_variante'
    UNION ALL SELECT 'insumo_stock_consultorio', 'id_variante'
    UNION ALL SELECT 'insumo_variantes', 'id_variante'
    UNION ALL SELECT 'consultorio_doctor_asignacion', 'id_horario_usuario'
    UNION ALL SELECT 'horario_usuario', 'estado_horario'
    UNION ALL SELECT 'horario_usuario', 'vigente_desde'
    UNION ALL SELECT 'horario_usuario', 'vigente_hasta'
    UNION ALL SELECT 'horario_usuario', 'hora_salida'
) esperado
LEFT JOIN information_schema.columns c
    ON c.table_schema = DATABASE()
   AND c.table_name = esperado.tabla
   AND c.column_name = esperado.columna;

SET @existe := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'agenda'
      AND index_name = 'idx_agenda_fecha_consultorio_hora'
);
SET @sql := IF(
    @existe = 0,
    'ALTER TABLE `agenda` ADD INDEX `idx_agenda_fecha_consultorio_hora` (`fecha`,`id_consultorio`,`hora_inicio`,`id_agenda`), ALGORITHM=INPLACE, LOCK=NONE',
    'SELECT ''idx_agenda_fecha_consultorio_hora ya existe'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @existe := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'consulta'
      AND index_name = 'idx_consulta_agendamiento'
);
SET @sql := IF(
    @existe = 0,
    'ALTER TABLE `consulta` ADD INDEX `idx_consulta_agendamiento` (`cod_agendamientoFK`), ALGORITHM=INPLACE, LOCK=NONE',
    'SELECT ''idx_consulta_agendamiento ya existe'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @existe := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'evoluciontratamiento'
      AND index_name = 'idx_evolucion_agenda_id'
);
SET @sql := IF(
    @existe = 0,
    'ALTER TABLE `evoluciontratamiento` ADD INDEX `idx_evolucion_agenda_id` (`cod_agendaFK`,`cod_evoluciontratamiento`), ALGORITHM=INPLACE, LOCK=NONE',
    'SELECT ''idx_evolucion_agenda_id ya existe'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT table_name,
       index_name,
       non_unique,
       index_type,
       GROUP_CONCAT(column_name ORDER BY seq_in_index) AS columnas
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND index_name IN (
      'idx_agenda_fecha_consultorio_hora',
      'idx_consulta_agendamiento',
      'idx_evolucion_agenda_id'
  )
GROUP BY table_name, index_name, non_unique, index_type
ORDER BY table_name, index_name;

-- Validacion de firma: todas las filas deben devolver "OK".
SELECT esperado.table_name,
       esperado.index_name,
       esperado.columnas AS columnas_esperadas,
       IFNULL(actual.columnas, 'AUSENTE') AS columnas_actuales,
       IF(actual.columnas = esperado.columnas, 'OK', 'ERROR: revisar indice') AS estado
FROM (
    SELECT 'agenda' AS table_name, 'idx_agenda_fecha_consultorio_hora' AS index_name,
           'fecha,id_consultorio,hora_inicio,id_agenda' AS columnas
    UNION ALL SELECT 'consulta', 'idx_consulta_agendamiento', 'cod_agendamientoFK'
    UNION ALL SELECT 'evoluciontratamiento', 'idx_evolucion_agenda_id',
                     'cod_agendaFK,cod_evoluciontratamiento'
) esperado
LEFT JOIN (
    SELECT table_name, index_name,
           GROUP_CONCAT(column_name ORDER BY seq_in_index) AS columnas
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
    GROUP BY table_name, index_name
) actual
    ON actual.table_name = esperado.table_name
   AND actual.index_name = esperado.index_name
ORDER BY esperado.table_name, esperado.index_name;

-- Rollback manual, solo si se comprobara una regresion:
-- ALTER TABLE `agenda` DROP INDEX `idx_agenda_fecha_consultorio_hora`, ALGORITHM=INPLACE, LOCK=NONE;
-- ALTER TABLE `consulta` DROP INDEX `idx_consulta_agendamiento`, ALGORITHM=INPLACE, LOCK=NONE;
-- ALTER TABLE `evoluciontratamiento` DROP INDEX `idx_evolucion_agenda_id`, ALGORITHM=INPLACE, LOCK=NONE;
