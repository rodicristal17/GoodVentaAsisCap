-- Clinident Salud / Sistema Telar
-- GoHighLevel fase 4: adjuntos persistentes y asistente DeepSeek controlado.
-- Compatible con MySQL 5.6, MariaDB 10.6 y PHP 7.2.
-- Las credenciales permanecen fuera de la base de datos.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS gohighlevel_adjunto_cache (
  id_adjunto BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ghl_conversation_id VARCHAR(80) NOT NULL,
  ghl_message_id VARCHAR(80) NOT NULL,
  indice SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  url_origen MEDIUMTEXT NOT NULL,
  url_hash CHAR(64) NOT NULL,
  nombre_origen VARCHAR(255) NOT NULL DEFAULT '',
  mime_type VARCHAR(120) NOT NULL DEFAULT '',
  extension VARCHAR(16) NOT NULL DEFAULT '',
  ruta_relativa VARCHAR(255) NOT NULL DEFAULT '',
  tamano_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
  estado VARCHAR(24) NOT NULL DEFAULT 'pendiente',
  codigo_error VARCHAR(48) NOT NULL DEFAULT '',
  fecha_origen VARCHAR(40) NOT NULL DEFAULT '',
  fecha_descarga DATETIME DEFAULT NULL,
  fecha_ultima_vista DATETIME DEFAULT NULL,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_adjunto),
  UNIQUE KEY uq_ghl_adjunto_mensaje_indice (ghl_message_id,indice),
  KEY idx_ghl_adjunto_conversacion (ghl_conversation_id,fecha_creacion),
  KEY idx_ghl_adjunto_estado (estado,fecha_actualizacion),
  KEY idx_ghl_adjunto_hash (url_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gohighlevel_ia_config (
  id_config TINYINT UNSIGNED NOT NULL,
  asistente_habilitado TINYINT(1) NOT NULL DEFAULT 0,
  automatico_habilitado TINYINT(1) NOT NULL DEFAULT 0,
  modelo VARCHAR(64) NOT NULL DEFAULT 'deepseek-v4-flash',
  prompt_base MEDIUMTEXT,
  informacion_clinica MEDIUMTEXT,
  preguntas_frecuentes MEDIUMTEXT,
  tono VARCHAR(255) NOT NULL DEFAULT 'Cordial, claro y breve.',
  reglas_derivacion MEDIUMTEXT,
  cod_usuario_actualizaFK INT DEFAULT NULL,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_config)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO gohighlevel_ia_config
  (id_config,asistente_habilitado,automatico_habilitado,modelo,prompt_base,
   informacion_clinica,preguntas_frecuentes,tono,reglas_derivacion,
   cod_usuario_actualizaFK,fecha_creacion,fecha_actualizacion)
VALUES
  (1,0,0,'deepseek-v4-flash','', '', '', 'Cordial, claro y breve.',
   'Derivar siempre consultas medicas, pagos, reclamos, asuntos legales y mensajes con adjuntos.',
   5994,NOW(),NOW())
ON DUPLICATE KEY UPDATE id_config=VALUES(id_config);

CREATE TABLE IF NOT EXISTS gohighlevel_ia_operacion (
  id_operacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  token_cliente VARCHAR(64) NOT NULL,
  cod_usuarioFK INT NOT NULL,
  tipo_operacion VARCHAR(24) NOT NULL DEFAULT 'sugerencia',
  ghl_conversation_id VARCHAR(80) NOT NULL,
  ghl_message_id VARCHAR(80) NOT NULL DEFAULT '',
  modelo VARCHAR(64) NOT NULL DEFAULT '',
  estado VARCHAR(20) NOT NULL DEFAULT 'procesando',
  codigo_resultado VARCHAR(48) NOT NULL DEFAULT '',
  intencion VARCHAR(80) NOT NULL DEFAULT '',
  confianza DECIMAL(5,4) NOT NULL DEFAULT 0,
  requiere_humano TINYINT(1) NOT NULL DEFAULT 1,
  caracteres_entrada INT UNSIGNED NOT NULL DEFAULT 0,
  caracteres_salida INT UNSIGNED NOT NULL DEFAULT 0,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_operacion),
  UNIQUE KEY uq_ghl_ia_token (token_cliente),
  KEY idx_ghl_ia_actor (cod_usuarioFK,fecha_creacion),
  KEY idx_ghl_ia_conversacion (ghl_conversation_id,fecha_creacion),
  KEY idx_ghl_ia_mensaje (ghl_message_id,tipo_operacion),
  KEY idx_ghl_ia_estado (estado,fecha_actualizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT COUNT(*) AS tablas_gohighlevel_fase4
FROM information_schema.tables
WHERE table_schema=DATABASE() AND table_name IN (
  'gohighlevel_adjunto_cache','gohighlevel_ia_config','gohighlevel_ia_operacion'
);
