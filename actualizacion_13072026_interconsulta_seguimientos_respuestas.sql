-- Hilos: seguimientos internos programados, plantillas administrables y respuestas citadas.
-- Compatible con MySQL 5.7+/8 e implementacion PHP 7.2.
-- Ejecutar con respaldo previo de mensaje y en horario de baja actividad.
-- No transforma ni elimina mensajes futuros heredados.

SET NAMES utf8mb4;
SET SESSION lock_wait_timeout = 15;

SET @codigo_permiso_plantillas := 'ADMINPLANTILLASSEGUIMIENTOHILOS';
SET @codigo_permiso_base := 'FUSIONARINTERCONSULTA';

-- Preflight: debe devolver OK antes de aplicar la estructura.
SELECT IF(COUNT(*) = 3, 'OK', 'ERROR: faltan tablas base de Hilos') AS preflight_hilos
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN ('interconsulta', 'mensaje', 'usuario');

-- Evidencia previa: conservar estos conteos junto con el respaldo.
SELECT COUNT(*) AS mensajes_activos,
       SUM(fecha_creacion > NOW()) AS mensajes_futuros_legacy,
       SUM(fecha_creacion > NOW() AND IFNULL(cod_usuarioFK, 0) = 0) AS recordatorios_sistema_futuros
FROM mensaje
WHERE estado = 'activo';

CREATE TABLE IF NOT EXISTS `interconsulta_seguimiento_plantilla` (
  `id_plantilla` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `categoria` varchar(80) DEFAULT NULL,
  `mensaje` varchar(750) NOT NULL,
  `orden` int NOT NULL DEFAULT 0,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `cod_usuarioFK_create` int DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuarioFK_edit` int DEFAULT NULL,
  `fecha_edit` datetime DEFAULT NULL,
  PRIMARY KEY (`id_plantilla`),
  KEY `idx_isp_estado_orden` (`estado`,`orden`,`nombre`),
  KEY `idx_isp_usuario_create` (`cod_usuarioFK_create`),
  KEY `idx_isp_usuario_edit` (`cod_usuarioFK_edit`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `interconsulta_seguimiento_programado` (
  `id_seguimiento` int NOT NULL AUTO_INCREMENT,
  `cod_interConsultaFK` int NOT NULL,
  `id_plantillaFK` int DEFAULT NULL,
  `motivo` varchar(120) NOT NULL,
  `mensaje` varchar(750) NOT NULL,
  `fecha_programada` datetime NOT NULL,
  `cod_responsableFK` int NOT NULL,
  `estado` enum('programado','completado','reprogramado','cancelado') NOT NULL DEFAULT 'programado',
  `resultado` varchar(750) DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `id_seguimiento_origenFK` int DEFAULT NULL,
  `token_solicitud` varchar(64) DEFAULT NULL,
  `cod_usuarioFK_create` int NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuarioFK_update` int DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_seguimiento`),
  KEY `idx_isp_hilo_estado_fecha` (`cod_interConsultaFK`,`estado`,`fecha_programada`,`id_seguimiento`),
  KEY `idx_isp_responsable_estado_fecha` (`cod_responsableFK`,`estado`,`fecha_programada`,`id_seguimiento`),
  KEY `idx_isp_plantilla` (`id_plantillaFK`),
  KEY `idx_isp_origen` (`id_seguimiento_origenFK`),
  UNIQUE KEY `uq_isp_token_solicitud` (`token_solicitud`),
  KEY `idx_isp_usuario_create` (`cod_usuarioFK_create`),
  KEY `idx_isp_usuario_update` (`cod_usuarioFK_update`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Permiso administrativo explicito. Hereda inicialmente la asignacion de Fusionar Hilos.
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'INTERCONSULTA', @codigo_permiso_plantillas, 'Administrar plantillas de seguimiento', 'NO', 30, 'Administrativo'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso WHERE codigo=@codigo_permiso_plantillas AND tipo='Administrativo'
);

SET @id_permiso_plantillas := (
  SELECT idlistadodeacceso FROM listadodeacceso
  WHERE codigo=@codigo_permiso_plantillas AND tipo='Administrativo'
  ORDER BY idlistadodeacceso DESC LIMIT 1
);
SET @id_permiso_base := (
  SELECT idlistadodeacceso FROM listadodeacceso
  WHERE codigo=@codigo_permiso_base AND tipo='Administrativo'
  ORDER BY idlistadodeacceso DESC LIMIT 1
);

INSERT INTO detallesniveles (accion,idlistadodeacceso,cod_nivelesfk)
SELECT IFNULL(base.accion,'NO'),@id_permiso_plantillas,niv.cod_niveles
FROM listado_niveles niv
LEFT JOIN detallesniveles base
  ON base.idlistadodeacceso=@id_permiso_base
 AND base.cod_nivelesfk=niv.cod_niveles
WHERE niv.tipo='Administrativo'
  AND @id_permiso_plantillas IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM detallesniveles existente
    WHERE existente.idlistadodeacceso=@id_permiso_plantillas
      AND existente.cod_nivelesfk=niv.cod_niveles
  );

INSERT INTO accesosuser (frmname,orden,idlistadodeaccesoFK,tipo,usuarios_idusario,accion)
SELECT '',0,@id_permiso_plantillas,'Administrativo',u.cod_usuario,IFNULL(base.accion,'NO')
FROM usuario u
LEFT JOIN accesosuser base
  ON base.idlistadodeaccesoFK=@id_permiso_base
 AND base.tipo='Administrativo'
 AND base.usuarios_idusario=u.cod_usuario
WHERE u.estado='Activo'
  AND @id_permiso_plantillas IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM accesosuser existente
    WHERE existente.idlistadodeaccesoFK=@id_permiso_plantillas
      AND existente.tipo='Administrativo'
      AND existente.usuarios_idusario=u.cod_usuario
  );

-- Columna nullable: todos los mensajes historicos quedan sin respuesta asociada.
SET @existe_columna_respuesta := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'mensaje'
    AND column_name = 'cod_mensaje_respuestaFK'
);
SET @sql := IF(
  @existe_columna_respuesta = 0,
  'ALTER TABLE `mensaje` ADD COLUMN `cod_mensaje_respuestaFK` int NULL AFTER `cod_dictamenFK`, ALGORITHM=INPLACE, LOCK=NONE',
  'SELECT ''cod_mensaje_respuestaFK ya existe'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @existe_indice_respuesta := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'mensaje'
    AND index_name = 'idx_mensaje_respuesta'
);
SET @sql := IF(
  @existe_indice_respuesta = 0,
  'ALTER TABLE `mensaje` ADD INDEX `idx_mensaje_respuesta` (`cod_mensaje_respuestaFK`), ALGORITHM=INPLACE, LOCK=NONE',
  'SELECT ''idx_mensaje_respuesta ya existe'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Catalogo inicial. Los textos quedan editables desde Hilos.
INSERT INTO interconsulta_seguimiento_plantilla
  (nombre, categoria, mensaje, orden, estado)
SELECT 'No atiende', 'Contacto', 'No fue posible contactar. Volver a intentar el seguimiento.', 10, 'activo'
WHERE NOT EXISTS (
  SELECT 1 FROM interconsulta_seguimiento_plantilla WHERE LOWER(TRIM(nombre)) = 'no atiende'
);

INSERT INTO interconsulta_seguimiento_plantilla
  (nombre, categoria, mensaje, orden, estado)
SELECT 'Contactar nuevamente', 'Contacto', 'Contactar nuevamente para dar continuidad al seguimiento.', 20, 'activo'
WHERE NOT EXISTS (
  SELECT 1 FROM interconsulta_seguimiento_plantilla WHERE LOWER(TRIM(nombre)) = 'contactar nuevamente'
);

INSERT INTO interconsulta_seguimiento_plantilla
  (nombre, categoria, mensaje, orden, estado)
SELECT 'No fue posible localizar', 'Contacto', 'No fue posible localizar. Verificar los datos y volver a contactar.', 30, 'activo'
WHERE NOT EXISTS (
  SELECT 1 FROM interconsulta_seguimiento_plantilla WHERE LOWER(TRIM(nombre)) = 'no fue posible localizar'
);

INSERT INTO interconsulta_seguimiento_plantilla
  (nombre, categoria, mensaje, orden, estado)
SELECT 'Quiere agendar una cita', 'Agenda', 'Contactar para coordinar la fecha y hora de la cita.', 40, 'activo'
WHERE NOT EXISTS (
  SELECT 1 FROM interconsulta_seguimiento_plantilla WHERE LOWER(TRIM(nombre)) = 'quiere agendar una cita'
);

INSERT INTO interconsulta_seguimiento_plantilla
  (nombre, categoria, mensaje, orden, estado)
SELECT 'Pendiente de confirmar cita', 'Agenda', 'Confirmar si mantiene la fecha y hora propuestas para la cita.', 50, 'activo'
WHERE NOT EXISTS (
  SELECT 1 FROM interconsulta_seguimiento_plantilla WHERE LOWER(TRIM(nombre)) = 'pendiente de confirmar cita'
);

-- Validacion de firma: todas las filas deben devolver OK.
SELECT esperado.table_name,
       esperado.column_name,
       IF(actual.column_name IS NULL, 'AUSENTE', 'OK') AS estado
FROM (
  SELECT 'mensaje' AS table_name, 'cod_mensaje_respuestaFK' AS column_name
  UNION ALL SELECT 'interconsulta_seguimiento_plantilla', 'id_plantilla'
  UNION ALL SELECT 'interconsulta_seguimiento_programado', 'id_seguimiento'
  UNION ALL SELECT 'interconsulta_seguimiento_programado', 'cod_responsableFK'
  UNION ALL SELECT 'interconsulta_seguimiento_programado', 'fecha_programada'
) esperado
LEFT JOIN information_schema.columns actual
  ON actual.table_schema = DATABASE()
 AND actual.table_name = esperado.table_name
 AND actual.column_name = esperado.column_name
ORDER BY esperado.table_name, esperado.column_name;

SELECT table_name,
       index_name,
       GROUP_CONCAT(column_name ORDER BY seq_in_index) AS columnas
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND index_name IN (
    'idx_mensaje_respuesta',
    'idx_isp_estado_orden',
    'idx_isp_hilo_estado_fecha',
    'idx_isp_responsable_estado_fecha'
  )
GROUP BY table_name, index_name
ORDER BY table_name, index_name;

-- Verificacion de compatibilidad: los conteos legacy deben coincidir con el preflight.
SELECT COUNT(*) AS mensajes_activos,
       SUM(fecha_creacion > NOW()) AS mensajes_futuros_legacy,
       SUM(fecha_creacion > NOW() AND IFNULL(cod_usuarioFK, 0) = 0) AS recordatorios_sistema_futuros
FROM mensaje
WHERE estado = 'activo';

-- Reversion manual, solo si no existen seguimientos nuevos o luego de respaldarlos:
-- ALTER TABLE `mensaje` DROP INDEX `idx_mensaje_respuesta`, ALGORITHM=INPLACE, LOCK=NONE;
-- ALTER TABLE `mensaje` DROP COLUMN `cod_mensaje_respuestaFK`, ALGORITHM=INPLACE, LOCK=NONE;
-- DROP TABLE `interconsulta_seguimiento_programado`;
-- DROP TABLE `interconsulta_seguimiento_plantilla`;
-- DELETE FROM accesosuser WHERE idlistadodeaccesoFK=(SELECT idlistadodeacceso FROM listadodeacceso WHERE codigo='ADMINPLANTILLASSEGUIMIENTOHILOS' LIMIT 1);
-- DELETE FROM detallesniveles WHERE idlistadodeacceso=(SELECT idlistadodeacceso FROM listadodeacceso WHERE codigo='ADMINPLANTILLASSEGUIMIENTOHILOS' LIMIT 1);
-- DELETE FROM listadodeacceso WHERE codigo='ADMINPLANTILLASSEGUIMIENTOHILOS';
