-- Reversion de GoHighLevel fase 3.
-- Elimina solamente el indice local, los vinculos de usuarios y permisos de tareas.
-- No elimina ni modifica tareas, usuarios, conversaciones o automatizaciones en GoHighLevel.

SET NAMES utf8mb4;

DROP TABLE IF EXISTS gohighlevel_tarea_operacion;
DROP TABLE IF EXISTS gohighlevel_tarea_sync;
DROP TABLE IF EXISTS gohighlevel_tarea_cache;
DROP TABLE IF EXISTS gohighlevel_usuario_vinculo;

SET @ghl_drop_gestionar_tareas := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE() AND table_name='gohighlevel_permiso_usuario'
    AND column_name='puede_gestionar_tareas'
);
SET @ghl_drop_gestionar_tareas_sql := IF(
  @ghl_drop_gestionar_tareas=1,
  'ALTER TABLE gohighlevel_permiso_usuario DROP COLUMN puede_gestionar_tareas',
  'SELECT 1'
);
PREPARE ghl_drop_gestionar_tareas_stmt FROM @ghl_drop_gestionar_tareas_sql;
EXECUTE ghl_drop_gestionar_tareas_stmt;
DEALLOCATE PREPARE ghl_drop_gestionar_tareas_stmt;

SET @ghl_drop_ver_equipo := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE() AND table_name='gohighlevel_permiso_usuario'
    AND column_name='puede_ver_equipo'
);
SET @ghl_drop_ver_equipo_sql := IF(
  @ghl_drop_ver_equipo=1,
  'ALTER TABLE gohighlevel_permiso_usuario DROP COLUMN puede_ver_equipo',
  'SELECT 1'
);
PREPARE ghl_drop_ver_equipo_stmt FROM @ghl_drop_ver_equipo_sql;
EXECUTE ghl_drop_ver_equipo_stmt;
DEALLOCATE PREPARE ghl_drop_ver_equipo_stmt;

SET @ghl_drop_ver_tareas := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE() AND table_name='gohighlevel_permiso_usuario'
    AND column_name='puede_ver_tareas'
);
SET @ghl_drop_ver_tareas_sql := IF(
  @ghl_drop_ver_tareas=1,
  'ALTER TABLE gohighlevel_permiso_usuario DROP COLUMN puede_ver_tareas',
  'SELECT 1'
);
PREPARE ghl_drop_ver_tareas_stmt FROM @ghl_drop_ver_tareas_sql;
EXECUTE ghl_drop_ver_tareas_stmt;
DEALLOCATE PREPARE ghl_drop_ver_tareas_stmt;
