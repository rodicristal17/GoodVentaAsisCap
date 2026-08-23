-- Clinident Salud / Sistema Telar
-- GoHighLevel fase 2A: respuestas manuales de WhatsApp dentro de 24 horas.
-- Compatible con MySQL 5.6, MariaDB 10.6 y PHP 7.2.
-- No almacena el contenido de los mensajes.

SET NAMES utf8mb4;

SET @ghl_responde_existe := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE()
    AND table_name='gohighlevel_permiso_usuario'
    AND column_name='puede_responder'
);
SET @ghl_responde_sql := IF(
  @ghl_responde_existe=0,
  'ALTER TABLE gohighlevel_permiso_usuario ADD COLUMN puede_responder TINYINT(1) NOT NULL DEFAULT 0 AFTER puede_ver, ADD KEY idx_ghl_permiso_responde (activo,puede_responder)',
  'SELECT 1'
);
PREPARE ghl_responde_stmt FROM @ghl_responde_sql;
EXECUTE ghl_responde_stmt;
DEALLOCATE PREPARE ghl_responde_stmt;

-- El propietario inicial puede probar y administrar el envio manual.
UPDATE gohighlevel_permiso_usuario
SET puede_responder=1,fecha_actualizacion=NOW()
WHERE cod_usuarioFK=5994 AND activo=1;

CREATE TABLE IF NOT EXISTS gohighlevel_envio_manual (
  id_envio BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  token_cliente VARCHAR(64) NOT NULL,
  cod_usuarioFK INT NOT NULL,
  ghl_conversation_id VARCHAR(80) NOT NULL,
  ghl_contact_id VARCHAR(80) NOT NULL,
  canal VARCHAR(24) NOT NULL DEFAULT 'WhatsApp',
  estado VARCHAR(16) NOT NULL DEFAULT 'procesando',
  longitud_mensaje SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  ghl_message_id VARCHAR(80) NOT NULL DEFAULT '',
  codigo_resultado VARCHAR(48) NOT NULL DEFAULT '',
  fecha_ultimo_inbound DATETIME DEFAULT NULL,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_envio),
  UNIQUE KEY uq_ghl_envio_token (token_cliente),
  KEY idx_ghl_envio_actor (cod_usuarioFK,fecha_creacion),
  KEY idx_ghl_envio_conversacion (ghl_conversation_id,fecha_creacion),
  KEY idx_ghl_envio_estado (estado,fecha_actualizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT COUNT(*) AS tablas_respuesta_manual
FROM information_schema.tables
WHERE table_schema=DATABASE() AND table_name='gohighlevel_envio_manual';

