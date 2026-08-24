-- Clinident Salud / Sistema Telar
-- GoHighLevel fase 3: usuarios, filtros por responsable y tareas separadas de Telar.
-- Compatible con MySQL 5.6, MariaDB 10.6 y PHP 7.2.
-- GoHighLevel permanece como fuente oficial; esta migracion crea un indice local reversible.

SET NAMES utf8mb4;

SET @ghl_ver_tareas_existe := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE()
    AND table_name='gohighlevel_permiso_usuario'
    AND column_name='puede_ver_tareas'
);
SET @ghl_ver_tareas_sql := IF(
  @ghl_ver_tareas_existe=0,
  'ALTER TABLE gohighlevel_permiso_usuario ADD COLUMN puede_ver_tareas TINYINT(1) NOT NULL DEFAULT 0 AFTER puede_enviar_plantilla',
  'SELECT 1'
);
PREPARE ghl_ver_tareas_stmt FROM @ghl_ver_tareas_sql;
EXECUTE ghl_ver_tareas_stmt;
DEALLOCATE PREPARE ghl_ver_tareas_stmt;

SET @ghl_ver_equipo_existe := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE()
    AND table_name='gohighlevel_permiso_usuario'
    AND column_name='puede_ver_equipo'
);
SET @ghl_ver_equipo_sql := IF(
  @ghl_ver_equipo_existe=0,
  'ALTER TABLE gohighlevel_permiso_usuario ADD COLUMN puede_ver_equipo TINYINT(1) NOT NULL DEFAULT 0 AFTER puede_ver_tareas',
  'SELECT 1'
);
PREPARE ghl_ver_equipo_stmt FROM @ghl_ver_equipo_sql;
EXECUTE ghl_ver_equipo_stmt;
DEALLOCATE PREPARE ghl_ver_equipo_stmt;

SET @ghl_gestionar_tareas_existe := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE()
    AND table_name='gohighlevel_permiso_usuario'
    AND column_name='puede_gestionar_tareas'
);
SET @ghl_gestionar_tareas_sql := IF(
  @ghl_gestionar_tareas_existe=0,
  'ALTER TABLE gohighlevel_permiso_usuario ADD COLUMN puede_gestionar_tareas TINYINT(1) NOT NULL DEFAULT 0 AFTER puede_ver_equipo',
  'SELECT 1'
);
PREPARE ghl_gestionar_tareas_stmt FROM @ghl_gestionar_tareas_sql;
EXECUTE ghl_gestionar_tareas_stmt;
DEALLOCATE PREPARE ghl_gestionar_tareas_stmt;

UPDATE gohighlevel_permiso_usuario
SET puede_ver_tareas=1,puede_ver_equipo=1,puede_gestionar_tareas=1,fecha_actualizacion=NOW()
WHERE cod_usuarioFK=5994 AND activo=1;

CREATE TABLE IF NOT EXISTS gohighlevel_usuario_vinculo (
  ghl_user_id VARCHAR(80) NOT NULL,
  cod_usuarioFK INT DEFAULT NULL,
  nombre_ghl VARCHAR(180) NOT NULL DEFAULT '',
  email_hash CHAR(64) NOT NULL DEFAULT '',
  avatar_ghl VARCHAR(500) NOT NULL DEFAULT '',
  estado VARCHAR(24) NOT NULL DEFAULT 'sin_coincidencia',
  fuente VARCHAR(24) NOT NULL DEFAULT 'correo_exacto',
  cod_usuario_actualizaFK INT DEFAULT NULL,
  fecha_vinculacion DATETIME DEFAULT NULL,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (ghl_user_id),
  UNIQUE KEY uq_ghl_usuario_telar (cod_usuarioFK),
  KEY idx_ghl_usuario_estado (estado,fecha_actualizacion),
  KEY idx_ghl_usuario_email (email_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gohighlevel_tarea_cache (
  ghl_task_id VARCHAR(80) NOT NULL,
  ghl_contact_id VARCHAR(80) NOT NULL,
  ghl_assigned_user_id VARCHAR(80) NOT NULL DEFAULT '',
  titulo VARCHAR(255) NOT NULL DEFAULT '',
  descripcion TEXT,
  contacto_nombre VARCHAR(255) NOT NULL DEFAULT '',
  fecha_vencimiento_utc DATETIME DEFAULT NULL,
  completada TINYINT(1) NOT NULL DEFAULT 0,
  eliminada TINYINT(1) NOT NULL DEFAULT 0,
  fecha_origen VARCHAR(40) NOT NULL DEFAULT '',
  fecha_sincronizacion DATETIME NOT NULL,
  PRIMARY KEY (ghl_task_id),
  KEY idx_ghl_tarea_contacto (ghl_contact_id,eliminada),
  KEY idx_ghl_tarea_responsable (ghl_assigned_user_id,completada,eliminada),
  KEY idx_ghl_tarea_vencimiento (completada,eliminada,fecha_vencimiento_utc),
  KEY idx_ghl_tarea_sync (fecha_sincronizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gohighlevel_tarea_sync (
  location_id VARCHAR(80) NOT NULL,
  cursor_fecha VARCHAR(40) NOT NULL DEFAULT '',
  cursor_id VARCHAR(80) NOT NULL DEFAULT '',
  en_curso TINYINT(1) NOT NULL DEFAULT 0,
  contactos_procesados INT UNSIGNED NOT NULL DEFAULT 0,
  tareas_procesadas INT UNSIGNED NOT NULL DEFAULT 0,
  codigo_estado VARCHAR(48) NOT NULL DEFAULT 'pendiente',
  cod_usuario_iniciaFK INT DEFAULT NULL,
  fecha_inicio DATETIME DEFAULT NULL,
  fecha_ultima_ejecucion DATETIME DEFAULT NULL,
  fecha_completa DATETIME DEFAULT NULL,
  PRIMARY KEY (location_id),
  KEY idx_ghl_tarea_sync_estado (en_curso,fecha_ultima_ejecucion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gohighlevel_tarea_operacion (
  id_operacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  token_cliente VARCHAR(64) NOT NULL,
  cod_usuarioFK INT NOT NULL,
  accion VARCHAR(24) NOT NULL,
  ghl_task_id VARCHAR(80) NOT NULL DEFAULT '',
  ghl_contact_id VARCHAR(80) NOT NULL,
  ghl_assigned_user_id VARCHAR(80) NOT NULL DEFAULT '',
  estado VARCHAR(16) NOT NULL DEFAULT 'procesando',
  codigo_resultado VARCHAR(48) NOT NULL DEFAULT '',
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_operacion),
  UNIQUE KEY uq_ghl_tarea_operacion_token (token_cliente),
  KEY idx_ghl_tarea_operacion_actor (cod_usuarioFK,fecha_creacion),
  KEY idx_ghl_tarea_operacion_tarea (ghl_task_id,fecha_creacion),
  KEY idx_ghl_tarea_operacion_estado (estado,fecha_actualizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT COUNT(*) AS tablas_gohighlevel_tareas
FROM information_schema.tables
WHERE table_schema=DATABASE() AND table_name IN (
  'gohighlevel_usuario_vinculo','gohighlevel_tarea_cache',
  'gohighlevel_tarea_sync','gohighlevel_tarea_operacion'
);
