-- Clinident Salud / Sistema Telar
-- Central Telefonica - Transcripcion OpenAI bajo demanda.
-- Compatible con MySQL 5.6 y PHP 7.2.
--
-- La migracion es aditiva. No copia audios, no modifica Issabel/Asterisk y
-- concede el permiso exclusivamente a la cuenta protegida de Carlos Faraone.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS central_telefonica_transcripcion (
  id_transcripcion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_llamada BIGINT UNSIGNED NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'en_cola',
  proveedor VARCHAR(30) NOT NULL DEFAULT 'openai',
  modelo VARCHAR(80) NOT NULL DEFAULT 'gpt-4o-transcribe-diarize',
  idioma VARCHAR(10) NOT NULL DEFAULT 'es',
  solicitado_por INT NOT NULL,
  fecha_solicitud DATETIME NOT NULL,
  fecha_inicio DATETIME DEFAULT NULL,
  fecha_fin DATETIME DEFAULT NULL,
  intentos INT UNSIGNED NOT NULL DEFAULT 0,
  transcripcion_texto LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  segmentos_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  roles_hablantes_json TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  roles_fuente VARCHAR(20) NOT NULL DEFAULT 'sugerido',
  roles_actualizados_por INT DEFAULT NULL,
  roles_fecha_actualizacion DATETIME DEFAULT NULL,
  duracion_audio_seg DECIMAL(12,3) DEFAULT NULL,
  uso_entrada_tokens BIGINT UNSIGNED NOT NULL DEFAULT 0,
  uso_salida_tokens BIGINT UNSIGNED NOT NULL DEFAULT 0,
  uso_total_tokens BIGINT UNSIGNED NOT NULL DEFAULT 0,
  uso_json TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  costo_estimado_usd DECIMAL(14,8) DEFAULT NULL,
  proveedor_request_id VARCHAR(160) DEFAULT NULL,
  codigo_error VARCHAR(80) DEFAULT NULL,
  mensaje_error VARCHAR(255) DEFAULT NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_transcripcion),
  UNIQUE KEY uq_central_transcripcion_llamada (id_llamada),
  KEY idx_central_transcripcion_estado_fecha (estado,fecha_solicitud),
  KEY idx_central_transcripcion_solicitante (solicitado_por,fecha_solicitud)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS central_telefonica_transcripcion_evento (
  id_evento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_transcripcion BIGINT UNSIGNED NOT NULL,
  estado VARCHAR(30) NOT NULL,
  codigo VARCHAR(80) DEFAULT NULL,
  detalle VARCHAR(255) DEFAULT NULL,
  actor_usuario INT DEFAULT NULL,
  fecha_evento TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_evento),
  KEY idx_central_transcripcion_evento (id_transcripcion,fecha_evento),
  KEY idx_central_transcripcion_evento_estado (estado,fecha_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS central_telefonica_transcripcion_servicio (
  id_servicio TINYINT UNSIGNED NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'sin_configurar',
  proveedor VARCHAR(30) NOT NULL DEFAULT 'openai',
  modelo VARCHAR(80) NOT NULL DEFAULT 'gpt-4o-transcribe-diarize',
  ultima_actividad DATETIME DEFAULT NULL,
  ultima_transcripcion_id BIGINT UNSIGNED DEFAULT NULL,
  codigo_error VARCHAR(80) DEFAULT NULL,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_servicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO central_telefonica_transcripcion_servicio
  (id_servicio,estado,proveedor,modelo,ultima_actividad,codigo_error)
VALUES
  (1,'sin_configurar','openai','gpt-4o-transcribe-diarize',NULL,NULL)
ON DUPLICATE KEY UPDATE id_servicio=VALUES(id_servicio);

START TRANSACTION;

SET @central_permiso_grupo := 35;
SET @central_id_transcribir := (
  SELECT idlistadodeacceso FROM listadodeacceso
  WHERE codigo='TRANSCRIBIRLLAMADACENTRALTELEFONICA' AND tipo='Administrativo'
  ORDER BY idlistadodeacceso LIMIT 1
);
SET @central_id_transcribir := IFNULL(
  @central_id_transcribir,
  (SELECT IFNULL(MAX(idlistadodeacceso),0)+1 FROM listadodeacceso)
);

INSERT INTO listadodeacceso
  (idlistadodeacceso,nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT @central_id_transcribir,@central_permiso_grupo,'CENTRAL TELEFONICA',
       'TRANSCRIBIRLLAMADACENTRALTELEFONICA','Transcribir llamadas con IA','NO',5,'Administrativo'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='TRANSCRIBIRLLAMADACENTRALTELEFONICA' AND tipo='Administrativo'
);

INSERT INTO detallesniveles (accion,idlistadodeacceso,cod_nivelesfk)
SELECT 'NO',@central_id_transcribir,ln.cod_niveles
FROM listado_niveles ln
WHERE ln.tipo='Administrativo'
  AND NOT EXISTS (
    SELECT 1 FROM detallesniveles dn
    WHERE dn.cod_nivelesfk=ln.cod_niveles
      AND dn.idlistadodeacceso=@central_id_transcribir
  );

UPDATE detallesniveles
SET accion='NO'
WHERE idlistadodeacceso=@central_id_transcribir;

INSERT INTO accesosuser
  (formulario,anhadir,modificar,buscar,informes,frmname,orden,
   usuarios_idusario,accion,agrupacion,idlistadodeaccesoFK,tipo)
SELECT
  NULL,NULL,NULL,NULL,NULL,'',5,u.cod_usuario,'NO',NULL,
  @central_id_transcribir,'Administrativo'
FROM usuario u
WHERE NOT EXISTS (
  SELECT 1 FROM accesosuser au
  WHERE au.usuarios_idusario=u.cod_usuario
    AND au.idlistadodeaccesoFK=@central_id_transcribir
    AND au.tipo='Administrativo'
);

UPDATE accesosuser
SET accion='NO'
WHERE idlistadodeaccesoFK=@central_id_transcribir
  AND tipo='Administrativo';

UPDATE accesosuser au
INNER JOIN usuario u ON u.cod_usuario=au.usuarios_idusario
INNER JOIN persona p ON p.cod_persona=u.cod_usuario
SET au.accion='SI'
WHERE au.idlistadodeaccesoFK=@central_id_transcribir
  AND au.tipo='Administrativo'
  AND u.cod_usuario=5994
  AND LOWER(TRIM(IFNULL(u.login,'')))='cf'
  AND UPPER(TRIM(IFNULL(u.tipo,'')))='ADMINISTRATIVO'
  AND UPPER(TRIM(IFNULL(u.estado,'')))='ACTIVO'
  AND UPPER(TRIM(IFNULL(p.nombre_persona,''))) LIKE 'CARLOS FARAONE CLINIDENT%';

COMMIT;

-- Verificacion posterior sin exponer llamadas, audio ni transcripciones.
SELECT COUNT(*) AS tablas_transcripcion
FROM information_schema.tables
WHERE table_schema=DATABASE()
  AND table_name IN (
    'central_telefonica_transcripcion',
    'central_telefonica_transcripcion_evento',
    'central_telefonica_transcripcion_servicio'
  );

SELECT COUNT(*) AS usuarios_transcripcion_habilitados
FROM accesosuser au
INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK
WHERE la.codigo='TRANSCRIBIRLLAMADACENTRALTELEFONICA'
  AND au.tipo='Administrativo'
  AND UPPER(TRIM(au.accion))='SI';
