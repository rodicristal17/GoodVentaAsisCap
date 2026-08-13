-- Clinident Salud / Sistema Telar
-- Central Telefonica - Fase 1 de solo lectura.
-- Compatible con MySQL 5.6 y PHP 7.2.
--
-- Crea almacenamiento local de segmentos CDR, llamadas consolidadas y
-- auditoria de sincronizacion. No conecta ni modifica Issabel/Asterisk.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS central_telefonica_cdr_segmento (
  id_segmento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  fuente VARCHAR(30) NOT NULL DEFAULT 'issabel',
  fuente_clave CHAR(64) NOT NULL,
  grupo_clave VARCHAR(110) NOT NULL,
  cdr_uniqueid VARCHAR(80) NOT NULL,
  cdr_linkedid VARCHAR(80) DEFAULT NULL,
  cdr_sequence INT DEFAULT NULL,
  fecha_inicio DATETIME NOT NULL,
  origen_original VARCHAR(80) NOT NULL DEFAULT '',
  destino_original VARCHAR(80) NOT NULL DEFAULT '',
  origen_normalizado VARCHAR(32) NOT NULL DEFAULT '',
  destino_normalizado VARCHAR(32) NOT NULL DEFAULT '',
  extension VARCHAR(20) NOT NULL DEFAULT '',
  contexto VARCHAR(120) NOT NULL DEFAULT '',
  canal VARCHAR(190) NOT NULL DEFAULT '',
  canal_destino VARCHAR(190) NOT NULL DEFAULT '',
  disposicion VARCHAR(40) NOT NULL DEFAULT '',
  duracion_seg INT UNSIGNED NOT NULL DEFAULT 0,
  hablado_seg INT UNSIGNED NOT NULL DEFAULT 0,
  grabacion_disponible TINYINT(1) NOT NULL DEFAULT 0,
  grabacion_referencia VARCHAR(500) DEFAULT NULL,
  datos_tecnicos LONGTEXT,
  fecha_captura TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_segmento),
  UNIQUE KEY uq_central_cdr_fuente_clave (fuente,fuente_clave),
  KEY idx_central_cdr_grupo (grupo_clave),
  KEY idx_central_cdr_fecha (fecha_inicio),
  KEY idx_central_cdr_uniqueid (cdr_uniqueid),
  KEY idx_central_cdr_linkedid (cdr_linkedid),
  KEY idx_central_cdr_origen_normalizado (origen_normalizado),
  KEY idx_central_cdr_destino_normalizado (destino_normalizado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS central_telefonica_llamada (
  id_llamada BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  llamada_clave CHAR(64) NOT NULL,
  grupo_clave VARCHAR(110) NOT NULL,
  cdr_linkedid VARCHAR(80) DEFAULT NULL,
  cdr_uniqueid_principal VARCHAR(80) NOT NULL,
  fecha_inicio DATETIME NOT NULL,
  fecha_fin DATETIME NOT NULL,
  tipo VARCHAR(30) NOT NULL DEFAULT 'sin_clasificar',
  estado VARCHAR(30) NOT NULL DEFAULT 'sin_estado',
  origen_original VARCHAR(80) NOT NULL DEFAULT '',
  destino_original VARCHAR(80) NOT NULL DEFAULT '',
  origen_normalizado VARCHAR(32) NOT NULL DEFAULT '',
  destino_normalizado VARCHAR(32) NOT NULL DEFAULT '',
  extension VARCHAR(20) NOT NULL DEFAULT '',
  duracion_seg INT UNSIGNED NOT NULL DEFAULT 0,
  hablado_seg INT UNSIGNED NOT NULL DEFAULT 0,
  cantidad_segmentos INT UNSIGNED NOT NULL DEFAULT 1,
  grabacion_disponible TINYINT(1) NOT NULL DEFAULT 0,
  grabacion_segmento_id BIGINT UNSIGNED DEFAULT NULL,
  clasificacion_motivo VARCHAR(255) NOT NULL DEFAULT '',
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_llamada),
  UNIQUE KEY uq_central_llamada_clave (llamada_clave),
  KEY idx_central_llamada_grupo (grupo_clave),
  KEY idx_central_llamada_fecha (fecha_inicio),
  KEY idx_central_llamada_tipo_estado (tipo,estado),
  KEY idx_central_llamada_extension (extension),
  KEY idx_central_llamada_origen_normalizado (origen_normalizado),
  KEY idx_central_llamada_destino_normalizado (destino_normalizado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS central_telefonica_sincronizacion (
  id_sincronizacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  fecha_inicio DATETIME NOT NULL,
  fecha_fin DATETIME DEFAULT NULL,
  estado VARCHAR(20) NOT NULL,
  fuente_desde DATETIME DEFAULT NULL,
  registros_consultados INT UNSIGNED NOT NULL DEFAULT 0,
  registros_nuevos INT UNSIGNED NOT NULL DEFAULT 0,
  registros_actualizados INT UNSIGNED NOT NULL DEFAULT 0,
  duracion_ms INT UNSIGNED NOT NULL DEFAULT 0,
  watermark_fecha DATETIME DEFAULT NULL,
  watermark_uniqueid VARCHAR(80) DEFAULT NULL,
  codigo_error VARCHAR(80) DEFAULT NULL,
  PRIMARY KEY (id_sincronizacion),
  KEY idx_central_sync_estado_fecha (estado,fecha_inicio),
  KEY idx_central_sync_watermark (watermark_fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

SET @central_permiso_grupo := 35;

SET @central_id_ver := (
  SELECT idlistadodeacceso FROM listadodeacceso
  WHERE codigo='VERCENTRALTELEFONICA' AND tipo='Administrativo'
  ORDER BY idlistadodeacceso LIMIT 1
);
SET @central_id_ver := IFNULL(
  @central_id_ver,
  (SELECT IFNULL(MAX(idlistadodeacceso),0)+1 FROM listadodeacceso)
);
INSERT INTO listadodeacceso
  (idlistadodeacceso,nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT @central_id_ver,@central_permiso_grupo,'CENTRAL TELEFONICA',
       'VERCENTRALTELEFONICA','Ver Central Telefonica','NO',1,'Administrativo'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='VERCENTRALTELEFONICA' AND tipo='Administrativo'
);

SET @central_id_telefonos := (
  SELECT idlistadodeacceso FROM listadodeacceso
  WHERE codigo='VERTELEFONOSCOMPLETOSCENTRALTELEFONICA' AND tipo='Administrativo'
  ORDER BY idlistadodeacceso LIMIT 1
);
SET @central_id_telefonos := IFNULL(
  @central_id_telefonos,
  (SELECT IFNULL(MAX(idlistadodeacceso),0)+1 FROM listadodeacceso)
);
INSERT INTO listadodeacceso
  (idlistadodeacceso,nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT @central_id_telefonos,@central_permiso_grupo,'CENTRAL TELEFONICA',
       'VERTELEFONOSCOMPLETOSCENTRALTELEFONICA','Ver telefonos completos','NO',2,'Administrativo'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='VERTELEFONOSCOMPLETOSCENTRALTELEFONICA' AND tipo='Administrativo'
);

SET @central_id_tecnicos := (
  SELECT idlistadodeacceso FROM listadodeacceso
  WHERE codigo='VERDATOSTECNICOSCENTRALTELEFONICA' AND tipo='Administrativo'
  ORDER BY idlistadodeacceso LIMIT 1
);
SET @central_id_tecnicos := IFNULL(
  @central_id_tecnicos,
  (SELECT IFNULL(MAX(idlistadodeacceso),0)+1 FROM listadodeacceso)
);
INSERT INTO listadodeacceso
  (idlistadodeacceso,nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT @central_id_tecnicos,@central_permiso_grupo,'CENTRAL TELEFONICA',
       'VERDATOSTECNICOSCENTRALTELEFONICA','Ver datos tecnicos de llamadas','NO',3,'Administrativo'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='VERDATOSTECNICOSCENTRALTELEFONICA' AND tipo='Administrativo'
);

SET @central_id_grabaciones := (
  SELECT idlistadodeacceso FROM listadodeacceso
  WHERE codigo='ESCUCHARGRABACIONCENTRALTELEFONICA' AND tipo='Administrativo'
  ORDER BY idlistadodeacceso LIMIT 1
);
SET @central_id_grabaciones := IFNULL(
  @central_id_grabaciones,
  (SELECT IFNULL(MAX(idlistadodeacceso),0)+1 FROM listadodeacceso)
);
INSERT INTO listadodeacceso
  (idlistadodeacceso,nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT @central_id_grabaciones,@central_permiso_grupo,'CENTRAL TELEFONICA',
       'ESCUCHARGRABACIONCENTRALTELEFONICA','Escuchar grabaciones','NO',4,'Administrativo'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='ESCUCHARGRABACIONCENTRALTELEFONICA' AND tipo='Administrativo'
);

DROP TEMPORARY TABLE IF EXISTS tmp_central_telefonica_permisos;
CREATE TEMPORARY TABLE tmp_central_telefonica_permisos (
  id_permiso INT NOT NULL,
  codigo VARCHAR(80) NOT NULL,
  habilitar_administrativo TINYINT(1) NOT NULL,
  PRIMARY KEY (id_permiso),
  UNIQUE KEY uq_tmp_central_codigo (codigo)
) ENGINE=MEMORY;

INSERT INTO tmp_central_telefonica_permisos
  (id_permiso,codigo,habilitar_administrativo)
SELECT idlistadodeacceso,codigo,
       CASE WHEN codigo='ESCUCHARGRABACIONCENTRALTELEFONICA' THEN 0 ELSE 1 END
FROM listadodeacceso
WHERE tipo='Administrativo'
  AND codigo IN (
    'VERCENTRALTELEFONICA',
    'VERTELEFONOSCOMPLETOSCENTRALTELEFONICA',
    'VERDATOSTECNICOSCENTRALTELEFONICA',
    'ESCUCHARGRABACIONCENTRALTELEFONICA'
  );

INSERT INTO detallesniveles (accion,idlistadodeacceso,cod_nivelesfk)
SELECT
  CASE
    WHEN UPPER(TRIM(ln.nombre))='ADMINISTRATIVO'
      AND tp.habilitar_administrativo=1 THEN 'SI'
    ELSE 'NO'
  END,
  tp.id_permiso,
  ln.cod_niveles
FROM listado_niveles ln
CROSS JOIN tmp_central_telefonica_permisos tp
WHERE ln.tipo='Administrativo'
  AND NOT EXISTS (
    SELECT 1 FROM detallesniveles dn
    WHERE dn.cod_nivelesfk=ln.cod_niveles
      AND dn.idlistadodeacceso=tp.id_permiso
  );

INSERT INTO accesosuser
  (formulario,anhadir,modificar,buscar,informes,frmname,orden,
   usuarios_idusario,accion,agrupacion,idlistadodeaccesoFK,tipo)
SELECT
  NULL,NULL,NULL,NULL,NULL,'',IFNULL(la.orden,0),
  u.cod_usuario,IFNULL(dn.accion,'NO'),NULL,tp.id_permiso,'Administrativo'
FROM usuario u
CROSS JOIN tmp_central_telefonica_permisos tp
INNER JOIN listadodeacceso la ON la.idlistadodeacceso=tp.id_permiso
LEFT JOIN detallesniveles dn
  ON dn.cod_nivelesfk=CAST(u.acceso AS UNSIGNED)
 AND dn.idlistadodeacceso=tp.id_permiso
WHERE NOT EXISTS (
  SELECT 1 FROM accesosuser au
  WHERE au.usuarios_idusario=u.cod_usuario
    AND au.idlistadodeaccesoFK=tp.id_permiso
    AND au.tipo='Administrativo'
);

SET @central_orden_catalogo := (
  SELECT IFNULL(MAX(default_quick_order),0)+1
  FROM dashboard_access_catalog
  WHERE is_default_quick_access=1
);

INSERT INTO dashboard_access_catalog
  (access_key,label,module_key,module_label,icon_key,route_path,permission_key,
   is_active,is_default_quick_access,default_quick_order)
VALUES
  ('central_telefonica','Central Telefonica','administrativo','Administrativo',
   'central-telefonica',NULL,'VERCENTRALTELEFONICA',1,1,@central_orden_catalogo)
ON DUPLICATE KEY UPDATE
  label=VALUES(label),module_key=VALUES(module_key),module_label=VALUES(module_label),
  icon_key=VALUES(icon_key),permission_key=VALUES(permission_key),is_active=1,
  is_default_quick_access=1,updated_at=CURRENT_TIMESTAMP;

-- Los usuarios sin personalizacion reciben el acceso por el catalogo default.
-- En configuraciones personalizadas se agrega al final solamente si hay lugar.
INSERT INTO dashboard_user_shortcuts
  (user_id,access_id,shortcut_order,is_visible)
SELECT
  configurados.user_id,
  catalogo.id,
  COALESCE(MAX(CASE WHEN existentes.is_visible=1 THEN existentes.shortcut_order END),0)+1,
  1
FROM (SELECT DISTINCT user_id FROM dashboard_user_shortcuts) configurados
INNER JOIN dashboard_access_catalog catalogo
  ON catalogo.access_key='central_telefonica'
INNER JOIN accesosuser permiso
  ON permiso.usuarios_idusario=configurados.user_id
 AND permiso.idlistadodeaccesoFK=@central_id_ver
 AND permiso.tipo='Administrativo'
 AND UPPER(TRIM(permiso.accion))='SI'
LEFT JOIN dashboard_user_shortcuts existentes
  ON existentes.user_id=configurados.user_id
GROUP BY configurados.user_id,catalogo.id
HAVING SUM(CASE WHEN existentes.is_visible=1 THEN 1 ELSE 0 END)<20
ON DUPLICATE KEY UPDATE
  is_visible=1,updated_at=CURRENT_TIMESTAMP;

COMMIT;

-- Verificacion posterior sin exponer llamadas ni telefonos:
SELECT codigo,idlistadodeacceso
FROM listadodeacceso
WHERE codigo LIKE '%CENTRALTELEFONICA%'
ORDER BY idlistadodeacceso;

SELECT COUNT(*) AS tablas_central
FROM information_schema.tables
WHERE table_schema=DATABASE()
  AND table_name IN (
    'central_telefonica_cdr_segmento',
    'central_telefonica_llamada',
    'central_telefonica_sincronizacion'
  );
