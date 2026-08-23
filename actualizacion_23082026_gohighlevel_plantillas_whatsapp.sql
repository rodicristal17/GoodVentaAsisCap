-- Clinident Salud / Sistema Telar
-- GoHighLevel fase 2B: plantillas aprobadas de WhatsApp fuera de 24 horas.
-- Compatible con MySQL 5.6, MariaDB 10.6 y PHP 7.2.
-- No almacena el cuerpo de las plantillas ni el contenido de los mensajes.

SET NAMES utf8mb4;

SET @ghl_envia_plantilla_existe := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE()
    AND table_name='gohighlevel_permiso_usuario'
    AND column_name='puede_enviar_plantilla'
);
SET @ghl_envia_plantilla_sql := IF(
  @ghl_envia_plantilla_existe=0,
  'ALTER TABLE gohighlevel_permiso_usuario ADD COLUMN puede_enviar_plantilla TINYINT(1) NOT NULL DEFAULT 0 AFTER puede_responder, ADD KEY idx_ghl_permiso_plantilla (activo,puede_enviar_plantilla)',
  'SELECT 1'
);
PREPARE ghl_envia_plantilla_stmt FROM @ghl_envia_plantilla_sql;
EXECUTE ghl_envia_plantilla_stmt;
DEALLOCATE PREPARE ghl_envia_plantilla_stmt;

UPDATE gohighlevel_permiso_usuario
SET puede_enviar_plantilla=1,fecha_actualizacion=NOW()
WHERE cod_usuarioFK=5994 AND activo=1;

CREATE TABLE IF NOT EXISTS gohighlevel_plantilla_config (
  ghl_template_id VARCHAR(120) NOT NULL,
  nombre VARCHAR(200) NOT NULL DEFAULT '',
  idioma VARCHAR(32) NOT NULL DEFAULT '',
  categoria VARCHAR(32) NOT NULL DEFAULT '',
  estado VARCHAR(32) NOT NULL DEFAULT '',
  habilitada TINYINT(1) NOT NULL DEFAULT 0,
  sensible_manual TINYINT(1) NOT NULL DEFAULT 0,
  tiene_variables TINYINT(1) NOT NULL DEFAULT 0,
  cod_usuario_actualizaFK INT DEFAULT NULL,
  fecha_ultima_consulta DATETIME NOT NULL,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (ghl_template_id),
  KEY idx_ghl_plantilla_estado (estado,idioma,categoria),
  KEY idx_ghl_plantilla_habilitada (habilitada,tiene_variables)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gohighlevel_envio_plantilla (
  id_envio BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  token_cliente VARCHAR(64) NOT NULL,
  cod_usuarioFK INT NOT NULL,
  ghl_conversation_id VARCHAR(80) NOT NULL,
  ghl_contact_id VARCHAR(80) NOT NULL,
  ghl_template_id VARCHAR(120) NOT NULL,
  nombre_plantilla VARCHAR(200) NOT NULL DEFAULT '',
  idioma VARCHAR(32) NOT NULL DEFAULT '',
  categoria VARCHAR(32) NOT NULL DEFAULT '',
  sensible TINYINT(1) NOT NULL DEFAULT 0,
  estado VARCHAR(16) NOT NULL DEFAULT 'procesando',
  ghl_message_id VARCHAR(80) NOT NULL DEFAULT '',
  codigo_resultado VARCHAR(48) NOT NULL DEFAULT '',
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_envio),
  UNIQUE KEY uq_ghl_envio_plantilla_token (token_cliente),
  KEY idx_ghl_envio_plantilla_actor (cod_usuarioFK,fecha_creacion),
  KEY idx_ghl_envio_plantilla_conversacion (ghl_conversation_id,fecha_creacion),
  KEY idx_ghl_envio_plantilla_catalogo (ghl_template_id,fecha_creacion),
  KEY idx_ghl_envio_plantilla_estado (estado,fecha_actualizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT COUNT(*) AS tablas_plantillas_whatsapp
FROM information_schema.tables
WHERE table_schema=DATABASE() AND table_name IN (
  'gohighlevel_plantilla_config','gohighlevel_envio_plantilla'
);
