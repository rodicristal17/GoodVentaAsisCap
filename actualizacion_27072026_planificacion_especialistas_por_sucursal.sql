-- Clinident Salud / Sistema Telar
-- Vinculo permanente de profesionales con sucursales de planificacion.
-- Compatible con MySQL 5.6 y PHP 7.2.
--
-- IMPORTANTE:
-- - Migracion aditiva e idempotente.
-- - No modifica usuario.cod_localFK ni horario_usuario.
-- - No concede permisos.
-- - Aplicar solamente con respaldo y autorizacion separada.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS planificacion_especialista_local (
  id_vinculo BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_profesionalFK INT(11) NOT NULL,
  cod_localFK INT(11) NOT NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  motivo VARCHAR(255) NULL,
  version INT UNSIGNED NOT NULL DEFAULT 1,
  fecha_create DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cod_usuarioFK_create INT(11) NOT NULL,
  fecha_edit DATETIME NULL,
  cod_usuarioFK_edit INT(11) NULL,
  PRIMARY KEY (id_vinculo),
  UNIQUE KEY uk_planificacion_profesional_local (cod_profesionalFK,cod_localFK),
  KEY idx_planificacion_local_estado (cod_localFK,estado),
  KEY idx_planificacion_profesional_estado (cod_profesionalFK,estado),
  CONSTRAINT fk_planificacion_vinculo_profesional
    FOREIGN KEY (cod_profesionalFK) REFERENCES usuario (cod_usuario),
  CONSTRAINT fk_planificacion_vinculo_local
    FOREIGN KEY (cod_localFK) REFERENCES local (cod_local),
  CONSTRAINT fk_planificacion_vinculo_creador
    FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario (cod_usuario),
  CONSTRAINT fk_planificacion_vinculo_editor
    FOREIGN KEY (cod_usuarioFK_edit) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE planificacion_especialista_historial
  MODIFY entidad ENUM('asignacion','regla','perfil','vinculo_local') NOT NULL;

