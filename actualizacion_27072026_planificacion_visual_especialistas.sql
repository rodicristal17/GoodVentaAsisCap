-- Clinident Salud / Sistema Telar
-- Modulo de planificacion visual de especialistas.
-- Compatible con MySQL 5.6 y PHP 7.2.
--
-- IMPORTANTE:
-- - Esta migracion es aditiva e idempotente.
-- - No concede permisos automaticamente.
-- - No modifica ni elimina horarios, consultorios, feriados o asignaciones
--   existentes de Agenda.
-- - Aplicar solamente con respaldo y en una ventana controlada.

SET NAMES utf8mb4;
START TRANSACTION;

CREATE TABLE IF NOT EXISTS planificacion_especialista_perfil (
  cod_usuarioFK INT(11) NOT NULL,
  especialidad VARCHAR(120) NULL,
  fecha_edit DATETIME NULL,
  cod_usuarioFK_edit INT(11) NULL,
  PRIMARY KEY (cod_usuarioFK),
  CONSTRAINT fk_planificacion_perfil_usuario
    FOREIGN KEY (cod_usuarioFK) REFERENCES usuario (cod_usuario),
  CONSTRAINT fk_planificacion_perfil_editor
    FOREIGN KEY (cod_usuarioFK_edit) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS planificacion_especialista_regla (
  id_regla BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_profesionalFK INT(11) NOT NULL,
  cod_localFK INT(11) NOT NULL,
  id_consultorioFK INT(11) NOT NULL,
  dia_semana TINYINT UNSIGNED NOT NULL COMMENT '1=lunes, 7=domingo',
  fecha_desde DATE NOT NULL,
  fecha_hasta DATE NULL,
  id_horario_usuarioFK INT(11) NULL,
  estado_asignacion ENUM('confirmada','pendiente_horario','propuesta') NOT NULL DEFAULT 'pendiente_horario',
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  motivo VARCHAR(255) NULL,
  version INT UNSIGNED NOT NULL DEFAULT 1,
  fecha_create DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cod_usuarioFK_create INT(11) NOT NULL,
  fecha_edit DATETIME NULL,
  cod_usuarioFK_edit INT(11) NULL,
  PRIMARY KEY (id_regla),
  KEY idx_planificacion_regla_rango (cod_localFK, estado, fecha_desde, fecha_hasta),
  KEY idx_planificacion_regla_profesional (cod_profesionalFK, estado),
  KEY idx_planificacion_regla_consultorio (id_consultorioFK, estado),
  CONSTRAINT fk_planificacion_regla_profesional
    FOREIGN KEY (cod_profesionalFK) REFERENCES usuario (cod_usuario),
  CONSTRAINT fk_planificacion_regla_local
    FOREIGN KEY (cod_localFK) REFERENCES local (cod_local),
  CONSTRAINT fk_planificacion_regla_consultorio
    FOREIGN KEY (id_consultorioFK) REFERENCES consultorios (id_consultorio),
  CONSTRAINT fk_planificacion_regla_horario
    FOREIGN KEY (id_horario_usuarioFK) REFERENCES horario_usuario (id),
  CONSTRAINT fk_planificacion_regla_creador
    FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario (cod_usuario),
  CONSTRAINT fk_planificacion_regla_editor
    FOREIGN KEY (cod_usuarioFK_edit) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS planificacion_especialista_asignacion (
  id_asignacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_profesionalFK INT(11) NOT NULL,
  cod_localFK INT(11) NOT NULL,
  id_consultorioFK INT(11) NOT NULL,
  fecha DATE NOT NULL,
  id_horario_usuarioFK INT(11) NULL,
  id_reglaFK BIGINT UNSIGNED NULL,
  tipo_origen ENUM('puntual','ajuste_regla') NOT NULL DEFAULT 'puntual',
  estado ENUM('confirmada','pendiente_horario','propuesta','anulada') NOT NULL DEFAULT 'pendiente_horario',
  motivo VARCHAR(255) NULL,
  version INT UNSIGNED NOT NULL DEFAULT 1,
  fecha_create DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cod_usuarioFK_create INT(11) NOT NULL,
  fecha_edit DATETIME NULL,
  cod_usuarioFK_edit INT(11) NULL,
  PRIMARY KEY (id_asignacion),
  KEY idx_planificacion_asignacion_fecha (cod_localFK, fecha, estado),
  KEY idx_planificacion_asignacion_profesional (cod_profesionalFK, fecha, estado),
  KEY idx_planificacion_asignacion_consultorio (id_consultorioFK, fecha, estado),
  KEY idx_planificacion_asignacion_regla (id_reglaFK, fecha),
  CONSTRAINT fk_planificacion_asignacion_profesional
    FOREIGN KEY (cod_profesionalFK) REFERENCES usuario (cod_usuario),
  CONSTRAINT fk_planificacion_asignacion_local
    FOREIGN KEY (cod_localFK) REFERENCES local (cod_local),
  CONSTRAINT fk_planificacion_asignacion_consultorio
    FOREIGN KEY (id_consultorioFK) REFERENCES consultorios (id_consultorio),
  CONSTRAINT fk_planificacion_asignacion_horario
    FOREIGN KEY (id_horario_usuarioFK) REFERENCES horario_usuario (id),
  CONSTRAINT fk_planificacion_asignacion_regla
    FOREIGN KEY (id_reglaFK) REFERENCES planificacion_especialista_regla (id_regla),
  CONSTRAINT fk_planificacion_asignacion_creador
    FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario (cod_usuario),
  CONSTRAINT fk_planificacion_asignacion_editor
    FOREIGN KEY (cod_usuarioFK_edit) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS planificacion_especialista_historial (
  id_historial BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  entidad ENUM('asignacion','regla','perfil') NOT NULL,
  id_entidad BIGINT UNSIGNED NOT NULL,
  accion VARCHAR(40) NOT NULL,
  datos_anteriores LONGTEXT NULL,
  datos_nuevos LONGTEXT NULL,
  motivo VARCHAR(255) NULL,
  fecha_create DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cod_usuarioFK_create INT(11) NOT NULL,
  PRIMARY KEY (id_historial),
  KEY idx_planificacion_historial_entidad (entidad, id_entidad, fecha_create),
  KEY idx_planificacion_historial_fecha (fecha_create),
  CONSTRAINT fk_planificacion_historial_usuario
    FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Permisos nuevos. Se registran en NO y no se asignan a ningun perfil o usuario.
INSERT INTO listadodeacceso
  (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'PLANIFICACION ESPECIALISTAS', 'VERPLANIFICACIONESPECIALISTAS',
  'Ver planificacion', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='VERPLANIFICACIONESPECIALISTAS'
);

INSERT INTO listadodeacceso
  (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'PLANIFICACION ESPECIALISTAS', 'GESTIONARPLANIFICACIONESPECIALISTAS',
  'Crear, mover y anular asignaciones', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='GESTIONARPLANIFICACIONESPECIALISTAS'
);

INSERT INTO listadodeacceso
  (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'PLANIFICACION ESPECIALISTAS', 'PROPONERPLANIFICACIONESPECIALISTAS',
  'Proponer asignaciones', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='PROPONERPLANIFICACIONESPECIALISTAS'
);

INSERT INTO listadodeacceso
  (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'PLANIFICACION ESPECIALISTAS', 'GESTIONARRECURRENCIASPLANIFICACION',
  'Gestionar recurrencias', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='GESTIONARRECURRENCIASPLANIFICACION'
);

INSERT INTO listadodeacceso
  (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'PLANIFICACION ESPECIALISTAS', 'VERHISTORIALPLANIFICACION',
  'Ver historial', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='VERHISTORIALPLANIFICACION'
);

INSERT INTO listadodeacceso
  (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'PLANIFICACION ESPECIALISTAS', 'VERPLANIFICACIONTODASSUCURSALES',
  'Ver y seleccionar todas las sucursales', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='VERPLANIFICACIONTODASSUCURSALES'
);

-- El acceso rapido queda disponible en el catalogo, pero no se agrega como
-- predeterminado ni se activa para usuarios existentes.
INSERT INTO dashboard_access_catalog
  (access_key, label, module_key, module_label, icon_key, route_path,
   permission_key, is_active, is_default_quick_access, default_quick_order)
VALUES
  ('planificacion_especialistas', 'Planificacion visual',
   'agendamientos', 'Agendamientos', 'calendario', NULL,
   'VERPLANIFICACIONESPECIALISTAS', 1, 0, NULL)
ON DUPLICATE KEY UPDATE
  label=VALUES(label),
  module_key=VALUES(module_key),
  module_label=VALUES(module_label),
  icon_key=VALUES(icon_key),
  route_path=VALUES(route_path),
  permission_key=VALUES(permission_key),
  is_active=VALUES(is_active),
  updated_at=CURRENT_TIMESTAMP;

COMMIT;

-- Verificacion posterior sugerida:
SELECT codigo, nombre, accion
FROM listadodeacceso
WHERE codigo IN (
  'VERPLANIFICACIONESPECIALISTAS',
  'GESTIONARPLANIFICACIONESPECIALISTAS',
  'PROPONERPLANIFICACIONESPECIALISTAS',
  'GESTIONARRECURRENCIASPLANIFICACION',
  'VERHISTORIALPLANIFICACION',
  'VERPLANIFICACIONTODASSUCURSALES'
)
ORDER BY codigo;

SELECT access_key, permission_key, is_active, is_default_quick_access
FROM dashboard_access_catalog
WHERE access_key='planificacion_especialistas';

-- Reversion controlada:
-- 1. Inactivar el acceso rapido:
--    UPDATE dashboard_access_catalog SET is_active=0
--    WHERE access_key='planificacion_especialistas';
-- 2. Cambiar los seis permisos a NO para los perfiles y usuarios autorizados.
-- 3. Conservar tablas e historial mientras existan datos operativos.
-- 4. Eliminar tablas y permisos solamente si estan vacios y existe respaldo.
