-- Reestructura segura del modulo "Funcionarios y Accesos"
-- Fecha: 2026-06-05
-- Migracion aditiva: no renombra ni elimina tablas/columnas existentes.

CREATE TABLE IF NOT EXISTS usuario_perfil_extendido (
  id INT(11) NOT NULL AUTO_INCREMENT,
  cod_usuarioFK INT(11) NOT NULL,
  barrio VARCHAR(120) DEFAULT NULL,
  ciudad VARCHAR(120) DEFAULT NULL,
  referencia_domicilio VARCHAR(255) DEFAULT NULL,
  contacto_emergencia_nombre VARCHAR(160) DEFAULT NULL,
  contacto_emergencia_telefono VARCHAR(45) DEFAULT NULL,
  segundo_telefono VARCHAR(45) DEFAULT NULL,
  correo VARCHAR(160) DEFAULT NULL,
  responsable_directoFK INT(11) DEFAULT NULL,
  area VARCHAR(120) DEFAULT NULL,
  cargo_funcion VARCHAR(160) DEFAULT NULL,
  fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  cod_usuarioFK_update INT(11) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_perfil_extendido_usuario (cod_usuarioFK),
  KEY idx_usuario_perfil_extendido_responsable (responsable_directoFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_historial_cambios (
  id INT(11) NOT NULL AUTO_INCREMENT,
  cod_usuarioFK INT(11) NOT NULL,
  campo VARCHAR(120) NOT NULL,
  valor_anterior TEXT DEFAULT NULL,
  valor_nuevo TEXT DEFAULT NULL,
  fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cod_usuario_modifico INT(11) DEFAULT NULL,
  origen VARCHAR(80) DEFAULT NULL,
  estado VARCHAR(45) DEFAULT 'Registrado',
  responsable_revisionFK INT(11) DEFAULT NULL,
  fecha_revision DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_usuario_historial_usuario (cod_usuarioFK),
  KEY idx_usuario_historial_fecha (fecha_hora),
  KEY idx_usuario_historial_modifico (cod_usuario_modifico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_ubicacion_domicilio (
  id INT(11) NOT NULL AUTO_INCREMENT,
  cod_usuarioFK INT(11) NOT NULL,
  direccion VARCHAR(255) DEFAULT NULL,
  barrio VARCHAR(120) DEFAULT NULL,
  ciudad VARCHAR(120) DEFAULT NULL,
  referencia VARCHAR(255) DEFAULT NULL,
  latitud DECIMAL(11,8) DEFAULT NULL,
  longitud DECIMAL(11,8) DEFAULT NULL,
  fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  cod_usuarioFK_update INT(11) DEFAULT NULL,
  valor_anterior_json TEXT DEFAULT NULL,
  estado VARCHAR(45) DEFAULT 'Activo',
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_ubicacion_usuario (cod_usuarioFK),
  KEY idx_usuario_ubicacion_update (cod_usuarioFK_update)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_ubicacion_visualizaciones_log (
  id INT(11) NOT NULL AUTO_INCREMENT,
  cod_usuarioFK_consultado INT(11) NOT NULL,
  cod_usuarioFK_visualizo INT(11) NOT NULL,
  fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  origen VARCHAR(80) DEFAULT NULL,
  motivo VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_usuario_ubicacion_log_consultado (cod_usuarioFK_consultado),
  KEY idx_usuario_ubicacion_log_visualizo (cod_usuarioFK_visualizo),
  KEY idx_usuario_ubicacion_log_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_documentos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  cod_usuarioFK INT(11) NOT NULL,
  tipo_documento VARCHAR(80) NOT NULL,
  nombre_archivo VARCHAR(180) NOT NULL,
  url_archivo VARCHAR(255) NOT NULL,
  estado VARCHAR(45) DEFAULT 'Activo',
  fecha_carga DATETIME DEFAULT CURRENT_TIMESTAMP,
  cod_usuarioFK_carga INT(11) DEFAULT NULL,
  observacion VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_usuario_documentos_usuario (cod_usuarioFK),
  KEY idx_usuario_documentos_tipo (tipo_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_perfil_completitud (
  id INT(11) NOT NULL AUTO_INCREMENT,
  cod_usuarioFK INT(11) NOT NULL,
  porcentaje INT(3) NOT NULL DEFAULT 0,
  obligatorios_pendientes TEXT DEFAULT NULL,
  recomendados_pendientes TEXT DEFAULT NULL,
  fecha_calculo DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_perfil_completitud_usuario (cod_usuarioFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
