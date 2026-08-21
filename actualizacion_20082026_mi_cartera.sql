-- Clinident Salud / Sistema Telar
-- Mi cartera: asignacion y seguimiento telefonico de pacientes con cuotas
-- vencidas o con vencimiento dentro de los proximos 7 dias.
-- Compatible con MySQL 5.6, MariaDB 10.6 y PHP 7.2.
--
-- Migracion aditiva. El saldo no se copia a estas tablas: siempre se obtiene
-- de credito y pago para conservar una unica fuente contable.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS cartera_configuracion (
  id_configuracion TINYINT UNSIGNED NOT NULL,
  cod_jefeFK INT DEFAULT NULL,
  dias_prevencion SMALLINT UNSIGNED NOT NULL DEFAULT 7,
  dias_escalamiento SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  intentos_escalamiento TINYINT UNSIGNED NOT NULL DEFAULT 2,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  cod_usuario_actualizaFK INT DEFAULT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_configuracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cartera_configuracion
  (id_configuracion,cod_jefeFK,dias_prevencion,dias_escalamiento,
   intentos_escalamiento,activo,cod_usuario_actualizaFK,fecha_actualizacion)
VALUES (1,NULL,7,30,2,1,NULL,NOW())
ON DUPLICATE KEY UPDATE id_configuracion=VALUES(id_configuracion);

CREATE TABLE IF NOT EXISTS cartera_equipo (
  id_equipo BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_usuarioFK INT NOT NULL,
  rol VARCHAR(24) NOT NULL,
  cod_localFK INT NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  cod_usuario_asignaFK INT NOT NULL,
  fecha_asignacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_equipo),
  UNIQUE KEY uq_cartera_equipo_usuario_rol_local (cod_usuarioFK,rol,cod_localFK),
  KEY idx_cartera_equipo_rol (rol,activo,cod_localFK),
  KEY idx_cartera_equipo_usuario (cod_usuarioFK,activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cartera_asignacion (
  id_asignacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_clienteFK INT NOT NULL,
  cod_usuario_responsableFK INT DEFAULT NULL,
  cod_local_origenFK INT NOT NULL DEFAULT 0,
  tipo_responsable VARCHAR(24) NOT NULL DEFAULT 'sin_asignar',
  estado VARCHAR(20) NOT NULL DEFAULT 'activa',
  prioridad VARCHAR(12) NOT NULL DEFAULT 'media',
  motivo_asignacion VARCHAR(80) NOT NULL DEFAULT 'asignacion_inicial',
  cod_usuario_asignaFK INT NOT NULL,
  fecha_asignacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_asignacion),
  UNIQUE KEY uq_cartera_asignacion_cliente (cod_clienteFK),
  KEY idx_cartera_asignacion_responsable (cod_usuario_responsableFK,estado),
  KEY idx_cartera_asignacion_local (cod_local_origenFK,estado),
  KEY idx_cartera_asignacion_tipo (tipo_responsable,estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cartera_gestion (
  id_gestion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_asignacionFK BIGINT UNSIGNED NOT NULL,
  cod_clienteFK INT NOT NULL,
  cod_usuarioFK INT NOT NULL,
  tipo VARCHAR(20) NOT NULL DEFAULT 'llamada',
  resultado VARCHAR(32) NOT NULL,
  telefono_normalizado VARCHAR(24) NOT NULL DEFAULT '',
  id_solicitud_llamadaFK BIGINT UNSIGNED DEFAULT NULL,
  nota VARCHAR(1000) NOT NULL DEFAULT '',
  fecha_proxima_accion DATETIME DEFAULT NULL,
  fecha_gestion DATETIME NOT NULL,
  PRIMARY KEY (id_gestion),
  KEY idx_cartera_gestion_cliente (cod_clienteFK,fecha_gestion),
  KEY idx_cartera_gestion_asignacion (id_asignacionFK,fecha_gestion),
  KEY idx_cartera_gestion_usuario (cod_usuarioFK,fecha_gestion),
  KEY idx_cartera_gestion_resultado (resultado,fecha_gestion),
  KEY idx_cartera_gestion_proxima (fecha_proxima_accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cartera_compromiso (
  id_compromiso BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_asignacionFK BIGINT UNSIGNED NOT NULL,
  id_gestion_origenFK BIGINT UNSIGNED NOT NULL,
  cod_clienteFK INT NOT NULL,
  cod_usuarioFK INT NOT NULL,
  fecha_compromiso DATE NOT NULL,
  monto_comprometido DECIMAL(14,2) NOT NULL,
  monto_pagado_base DECIMAL(14,2) NOT NULL DEFAULT 0,
  estado VARCHAR(20) NOT NULL DEFAULT 'vigente',
  fecha_resolucion DATETIME DEFAULT NULL,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_compromiso),
  KEY idx_cartera_compromiso_cliente (cod_clienteFK,estado,fecha_compromiso),
  KEY idx_cartera_compromiso_asignacion (id_asignacionFK,estado),
  KEY idx_cartera_compromiso_fecha (estado,fecha_compromiso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cartera_evento (
  id_evento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_clienteFK INT DEFAULT NULL,
  id_asignacionFK BIGINT UNSIGNED DEFAULT NULL,
  cod_usuario_actorFK INT NOT NULL,
  tipo_evento VARCHAR(40) NOT NULL,
  detalle VARCHAR(500) NOT NULL DEFAULT '',
  datos_anteriores TEXT,
  datos_nuevos TEXT,
  fecha_evento DATETIME NOT NULL,
  PRIMARY KEY (id_evento),
  KEY idx_cartera_evento_cliente (cod_clienteFK,fecha_evento),
  KEY idx_cartera_evento_asignacion (id_asignacionFK,fecha_evento),
  KEY idx_cartera_evento_actor (cod_usuario_actorFK,fecha_evento),
  KEY idx_cartera_evento_tipo (tipo_evento,fecha_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @cartera_orden_catalogo := (
  SELECT IFNULL(MAX(default_quick_order),0)+1
  FROM dashboard_access_catalog
  WHERE is_default_quick_access=1
);

INSERT INTO dashboard_access_catalog
  (access_key,label,module_key,module_label,icon_key,route_path,permission_key,
   is_active,is_default_quick_access,default_quick_order)
VALUES
  ('mi_cartera','Mi cartera','cobranzas','Cobranzas','mi-cartera',NULL,NULL,
   1,1,@cartera_orden_catalogo)
ON DUPLICATE KEY UPDATE
  label=VALUES(label),module_key=VALUES(module_key),module_label=VALUES(module_label),
  icon_key=VALUES(icon_key),route_path=NULL,permission_key=NULL,is_active=1,
  is_default_quick_access=1,updated_at=CURRENT_TIMESTAMP;

-- Carlos Faraone puede ingresar antes de configurar el equipo. Los demas
-- accesos se agregan al guardar responsables desde el engranaje del modulo.
INSERT INTO dashboard_user_shortcuts
  (user_id,access_id,shortcut_order,is_visible)
SELECT
  u.cod_usuario,
  catalogo.id,
  COALESCE(MAX(CASE WHEN existentes.is_visible=1 THEN existentes.shortcut_order END),0)+1,
  1
FROM usuario u
INNER JOIN dashboard_access_catalog catalogo
  ON catalogo.access_key='mi_cartera' AND catalogo.is_active=1
LEFT JOIN dashboard_user_shortcuts existentes
  ON existentes.user_id=u.cod_usuario
WHERE u.cod_usuario=5994 AND UPPER(TRIM(u.estado))='ACTIVO'
GROUP BY u.cod_usuario,catalogo.id
HAVING SUM(CASE WHEN existentes.is_visible=1 THEN 1 ELSE 0 END)<20
ON DUPLICATE KEY UPDATE is_visible=1,updated_at=CURRENT_TIMESTAMP;

SELECT COUNT(*) AS tablas_mi_cartera
FROM information_schema.tables
WHERE table_schema=DATABASE() AND table_name IN (
  'cartera_configuracion','cartera_equipo','cartera_asignacion',
  'cartera_gestion','cartera_compromiso','cartera_evento'
);
