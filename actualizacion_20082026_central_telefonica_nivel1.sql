-- Clinident Salud / Sistema Telar
-- Central Telefonica - Nivel 1: llamadas salientes y reconocimiento entrante.
-- Compatible con MySQL 5.6, MariaDB 10.6 y PHP 7.2.
--
-- Migracion aditiva: no modifica CDR, extensiones, colas, troncales ni el
-- enrutamiento de Issabel. Las credenciales AMI permanecen fuera de la base.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS central_telefonica_operacion_servicio (
  id_servicio TINYINT UNSIGNED NOT NULL,
  estado VARCHAR(24) NOT NULL DEFAULT 'sin_configurar',
  mensaje VARCHAR(255) NOT NULL DEFAULT '',
  evento_conectado TINYINT(1) NOT NULL DEFAULT 0,
  origenacion_disponible TINYINT(1) NOT NULL DEFAULT 0,
  fecha_ultimo_evento DATETIME DEFAULT NULL,
  fecha_ultimo_latido DATETIME DEFAULT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_servicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO central_telefonica_operacion_servicio
  (id_servicio,estado,mensaje,evento_conectado,origenacion_disponible,fecha_actualizacion)
VALUES
  (1,'sin_configurar','El conector telefonico todavia no fue habilitado.',0,0,NOW())
ON DUPLICATE KEY UPDATE id_servicio=VALUES(id_servicio);

CREATE TABLE IF NOT EXISTS central_telefonica_paciente_telefono (
  id_telefono BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_clienteFK INT NOT NULL,
  telefono_normalizado VARCHAR(24) NOT NULL,
  ultimos_digitos VARCHAR(15) NOT NULL DEFAULT '',
  fuente VARCHAR(20) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_telefono),
  UNIQUE KEY uq_central_paciente_telefono (cod_clienteFK,telefono_normalizado,fuente),
  KEY idx_central_paciente_numero (telefono_normalizado,activo),
  KEY idx_central_paciente_ultimos (ultimos_digitos,activo),
  KEY idx_central_paciente_cliente (cod_clienteFK,activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS central_telefonica_solicitud_llamada (
  id_solicitud BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  token CHAR(36) NOT NULL,
  cod_usuarioFK INT NOT NULL,
  cod_clienteFK INT DEFAULT NULL,
  extension VARCHAR(20) NOT NULL,
  telefono_normalizado VARCHAR(24) NOT NULL,
  estado VARCHAR(24) NOT NULL DEFAULT 'pendiente',
  mensaje VARCHAR(255) NOT NULL DEFAULT '',
  action_id VARCHAR(80) NOT NULL DEFAULT '',
  linkedid VARCHAR(80) NOT NULL DEFAULT '',
  intentos TINYINT UNSIGNED NOT NULL DEFAULT 0,
  ip_solicitud VARCHAR(45) NOT NULL DEFAULT '',
  fecha_solicitud DATETIME NOT NULL,
  fecha_tomada DATETIME DEFAULT NULL,
  fecha_respuesta DATETIME DEFAULT NULL,
  fecha_fin DATETIME DEFAULT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_solicitud),
  UNIQUE KEY uq_central_solicitud_token (token),
  KEY idx_central_solicitud_cola (estado,fecha_solicitud),
  KEY idx_central_solicitud_usuario (cod_usuarioFK,fecha_solicitud),
  KEY idx_central_solicitud_cliente (cod_clienteFK,fecha_solicitud),
  KEY idx_central_solicitud_linkedid (linkedid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS central_telefonica_llamada_viva (
  id_llamada_viva BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  clave_llamada CHAR(64) NOT NULL,
  linkedid VARCHAR(80) NOT NULL DEFAULT '',
  uniqueid VARCHAR(80) NOT NULL DEFAULT '',
  direccion VARCHAR(20) NOT NULL DEFAULT 'sin_clasificar',
  telefono_normalizado VARCHAR(24) NOT NULL DEFAULT '',
  extension VARCHAR(20) NOT NULL DEFAULT '',
  estado VARCHAR(24) NOT NULL DEFAULT 'detectada',
  cod_usuarioFK INT DEFAULT NULL,
  cod_clienteFK INT DEFAULT NULL,
  coincidencias_cliente SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  id_solicitudFK BIGINT UNSIGNED DEFAULT NULL,
  fecha_inicio DATETIME NOT NULL,
  fecha_contestada DATETIME DEFAULT NULL,
  fecha_fin DATETIME DEFAULT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_llamada_viva),
  UNIQUE KEY uq_central_llamada_viva (clave_llamada),
  KEY idx_central_viva_usuario (cod_usuarioFK,estado,fecha_actualizacion),
  KEY idx_central_viva_extension (extension,estado,fecha_actualizacion),
  KEY idx_central_viva_linkedid (linkedid),
  KEY idx_central_viva_solicitud (id_solicitudFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS central_telefonica_operacion_evento (
  id_evento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_llamada_vivaFK BIGINT UNSIGNED DEFAULT NULL,
  id_solicitudFK BIGINT UNSIGNED DEFAULT NULL,
  tipo_evento VARCHAR(32) NOT NULL,
  estado VARCHAR(24) NOT NULL DEFAULT '',
  telefono_normalizado VARCHAR(24) NOT NULL DEFAULT '',
  extension VARCHAR(20) NOT NULL DEFAULT '',
  cod_usuarioFK INT DEFAULT NULL,
  cod_clienteFK INT DEFAULT NULL,
  detalle_seguro VARCHAR(255) NOT NULL DEFAULT '',
  fecha_evento DATETIME NOT NULL,
  PRIMARY KEY (id_evento),
  KEY idx_central_evento_llamada (id_llamada_vivaFK,fecha_evento),
  KEY idx_central_evento_solicitud (id_solicitudFK,fecha_evento),
  KEY idx_central_evento_usuario (cod_usuarioFK,fecha_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La mesa operativa es para cualquier usuario activo. El permiso historico
-- sigue protegiendo el CDR, grabaciones, transcripciones y datos tecnicos.
UPDATE dashboard_access_catalog
SET permission_key=NULL,updated_at=CURRENT_TIMESTAMP
WHERE access_key='central_telefonica';

-- Agrega el acceso a escritorios personalizados solamente cuando hay lugar.
INSERT INTO dashboard_user_shortcuts
  (user_id,access_id,shortcut_order,is_visible)
SELECT
  u.cod_usuario,
  catalogo.id,
  COALESCE(MAX(CASE WHEN existentes.is_visible=1 THEN existentes.shortcut_order END),0)+1,
  1
FROM usuario u
INNER JOIN dashboard_access_catalog catalogo
  ON catalogo.access_key='central_telefonica' AND catalogo.is_active=1
LEFT JOIN dashboard_user_shortcuts existentes
  ON existentes.user_id=u.cod_usuario
WHERE UPPER(TRIM(u.estado))='ACTIVO'
GROUP BY u.cod_usuario,catalogo.id
HAVING SUM(CASE WHEN existentes.is_visible=1 THEN 1 ELSE 0 END)<20
ON DUPLICATE KEY UPDATE is_visible=1,updated_at=CURRENT_TIMESTAMP;

SELECT COUNT(*) AS tablas_operativas
FROM information_schema.tables
WHERE table_schema=DATABASE() AND table_name IN (
  'central_telefonica_operacion_servicio',
  'central_telefonica_paciente_telefono',
  'central_telefonica_solicitud_llamada',
  'central_telefonica_llamada_viva',
  'central_telefonica_operacion_evento'
);
