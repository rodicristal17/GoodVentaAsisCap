-- Clinident Salud / Sistema Telar
-- GoHighLevel fase 1: consulta segura, vinculacion por telefono y permisos.
-- Compatible con MySQL 5.6, MariaDB 10.6 y PHP 7.2.
-- No almacena tokens ni modifica datos de GoHighLevel.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS gohighlevel_permiso_usuario (
  cod_usuarioFK INT NOT NULL,
  puede_ver TINYINT(1) NOT NULL DEFAULT 0,
  puede_configurar TINYINT(1) NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  cod_usuario_actualizaFK INT DEFAULT NULL,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (cod_usuarioFK),
  KEY idx_ghl_permiso_activo (activo,puede_ver),
  KEY idx_ghl_permiso_configura (activo,puede_configurar)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gohighlevel_vinculo_contacto (
  id_vinculo BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ghl_contact_id VARCHAR(80) NOT NULL,
  cod_clienteFK INT DEFAULT NULL,
  telefono_normalizado VARCHAR(32) NOT NULL DEFAULT '',
  estado VARCHAR(24) NOT NULL DEFAULT 'sin_coincidencia',
  coincidencias SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  fuente VARCHAR(24) NOT NULL DEFAULT 'telefono_exacto',
  fecha_vinculacion DATETIME DEFAULT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_vinculo),
  UNIQUE KEY uq_ghl_vinculo_contacto (ghl_contact_id),
  KEY idx_ghl_vinculo_cliente (cod_clienteFK,estado),
  KEY idx_ghl_vinculo_telefono (telefono_normalizado,estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gohighlevel_evento (
  id_evento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_usuario_actorFK INT NOT NULL,
  tipo_evento VARCHAR(40) NOT NULL,
  entidad VARCHAR(40) NOT NULL DEFAULT '',
  entidad_id VARCHAR(80) NOT NULL DEFAULT '',
  detalle_seguro TEXT,
  ip_solicitud VARCHAR(45) NOT NULL DEFAULT '',
  fecha_evento DATETIME NOT NULL,
  PRIMARY KEY (id_evento),
  KEY idx_ghl_evento_actor (cod_usuario_actorFK,fecha_evento),
  KEY idx_ghl_evento_tipo (tipo_evento,fecha_evento),
  KEY idx_ghl_evento_entidad (entidad,entidad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- El propietario inicial conserva acceso para poder configurar el equipo.
INSERT INTO gohighlevel_permiso_usuario
  (cod_usuarioFK,puede_ver,puede_configurar,activo,cod_usuario_actualizaFK,fecha_creacion,fecha_actualizacion)
VALUES (5994,1,1,1,5994,NOW(),NOW())
ON DUPLICATE KEY UPDATE
  puede_ver=1,puede_configurar=1,activo=1,
  cod_usuario_actualizaFK=VALUES(cod_usuario_actualizaFK),fecha_actualizacion=NOW();

SET @ghl_orden_catalogo := (
  SELECT IFNULL(MAX(default_quick_order),0)+1
  FROM dashboard_access_catalog
  WHERE is_default_quick_access=1
);

INSERT INTO dashboard_access_catalog
  (access_key,label,module_key,module_label,icon_key,route_path,permission_key,
   is_active,is_default_quick_access,default_quick_order)
VALUES
  ('gohighlevel','GoHighLevel','comunicaciones','Comunicaciones','gohighlevel',NULL,NULL,
   1,1,@ghl_orden_catalogo)
ON DUPLICATE KEY UPDATE
  label=VALUES(label),module_key=VALUES(module_key),module_label=VALUES(module_label),
  icon_key=VALUES(icon_key),route_path=NULL,permission_key=NULL,is_active=1,
  is_default_quick_access=1,updated_at=CURRENT_TIMESTAMP;

INSERT INTO dashboard_user_shortcuts
  (user_id,access_id,shortcut_order,is_visible)
SELECT
  u.cod_usuario,
  catalogo.id,
  COALESCE(MAX(CASE WHEN existentes.is_visible=1 THEN existentes.shortcut_order END),0)+1,
  1
FROM usuario u
INNER JOIN dashboard_access_catalog catalogo
  ON catalogo.access_key='gohighlevel' AND catalogo.is_active=1
LEFT JOIN dashboard_user_shortcuts existentes
  ON existentes.user_id=u.cod_usuario
WHERE u.cod_usuario=5994 AND UPPER(TRIM(u.estado))='ACTIVO'
GROUP BY u.cod_usuario,catalogo.id
HAVING SUM(CASE WHEN existentes.is_visible=1 THEN 1 ELSE 0 END)<20
ON DUPLICATE KEY UPDATE is_visible=1,updated_at=CURRENT_TIMESTAMP;

SELECT COUNT(*) AS tablas_gohighlevel
FROM information_schema.tables
WHERE table_schema=DATABASE() AND table_name IN (
  'gohighlevel_permiso_usuario','gohighlevel_vinculo_contacto','gohighlevel_evento'
);
