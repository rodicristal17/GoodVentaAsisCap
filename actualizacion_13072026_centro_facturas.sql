-- Centro de Facturas de Clinident Salud.
-- Compatible con MySQL 5.7+/8 y PHP 7.2.
-- Cambios aditivos: no transforma facturas, ventas, gastos, compras ni adjuntos historicos.
-- Ejecutar con respaldo previo y antes de publicar la interfaz.

SET NAMES latin1;
SET SESSION lock_wait_timeout = 15;

-- Preflight: las tablas base deben existir antes de continuar.
SELECT IF(COUNT(*) = 11, 'OK', 'ERROR: faltan tablas base para Centro de Facturas') AS preflight_centro_facturas
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN (
    'interconsulta','mensaje','gastos','compra','venta','nrofactura',
    'proveedor','usuario','persona','local','dashboard_access_catalog'
  );

-- Clasificacion opcional del adjunto. Los mensajes historicos conservan NULL.
SET @cf_existe_tipo_adjunto := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'mensaje'
    AND column_name = 'tipo_adjunto'
);
SET @cf_sql := IF(
  @cf_existe_tipo_adjunto = 0,
  'ALTER TABLE `mensaje` ADD COLUMN `tipo_adjunto` varchar(20) NULL AFTER `url`, ALGORITHM=INPLACE, LOCK=NONE',
  'SELECT ''mensaje.tipo_adjunto ya existe'' AS info'
);
PREPARE cf_stmt FROM @cf_sql;
EXECUTE cf_stmt;
DEALLOCATE PREPARE cf_stmt;

CREATE TABLE IF NOT EXISTS `centro_factura_configuracion` (
  `id_configuracion` tinyint unsigned NOT NULL,
  `dias_plazo_original` smallint unsigned NOT NULL DEFAULT 5,
  `requiere_archivo_manual` tinyint(1) NOT NULL DEFAULT 1,
  `moneda_predeterminada` varchar(10) NOT NULL DEFAULT 'PYG',
  `ocr_habilitado` tinyint(1) NOT NULL DEFAULT 0,
  `ocr_proveedor` varchar(60) DEFAULT NULL,
  `cod_usuarioFK_create` int DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuarioFK_update` int DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_configuracion`),
  KEY `idx_cf_config_usuario_create` (`cod_usuarioFK_create`),
  KEY `idx_cf_config_usuario_update` (`cod_usuarioFK_update`),
  CONSTRAINT `fk_cf_config_usuario_create` FOREIGN KEY (`cod_usuarioFK_create`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_config_usuario_update` FOREIGN KEY (`cod_usuarioFK_update`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `centro_factura_configuracion`
  (`id_configuracion`,`dias_plazo_original`,`requiere_archivo_manual`,`moneda_predeterminada`,`ocr_habilitado`)
SELECT 1,5,1,'PYG',0
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `centro_factura_configuracion` WHERE `id_configuracion`=1
);

CREATE TABLE IF NOT EXISTS `centro_factura` (
  `id_factura` int NOT NULL AUTO_INCREMENT,
  `direccion` enum('entrante','emitida') NOT NULL DEFAULT 'entrante',
  `tipo_documento` enum('factura','recibo') NOT NULL DEFAULT 'factura',
  `fuente` enum('hilo','manual','compra','otra') NOT NULL DEFAULT 'hilo',
  `cod_interConsultaFK` int DEFAULT NULL,
  `cod_localFK` int NOT NULL,
  `tipo_contraparte` enum('proveedor','funcionario','otro') NOT NULL DEFAULT 'otro',
  `cod_proveedorFK` int DEFAULT NULL,
  `cod_funcionarioFK` int DEFAULT NULL,
  `nombre_contraparte` varchar(255) NOT NULL DEFAULT '',
  `documento_contraparte` varchar(45) DEFAULT NULL,
  `numero_factura` varchar(80) DEFAULT NULL,
  `numero_factura_normalizado` varchar(80) DEFAULT NULL,
  `timbrado` varchar(45) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `importe_total` decimal(15,2) NOT NULL DEFAULT 0,
  `moneda` varchar(10) NOT NULL DEFAULT 'PYG',
  `concepto` varchar(255) NOT NULL DEFAULT '',
  `observaciones` text,
  `estado_validacion` enum('pendiente','en_revision','validada','rechazada','anulada') NOT NULL DEFAULT 'pendiente',
  `estado_original` enum('en_proceso','enviado_central','recibido','observado','no_requiere_original') NOT NULL DEFAULT 'en_proceso',
  `fecha_registro_digital` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dias_plazo_original` smallint unsigned NOT NULL DEFAULT 5,
  `fecha_limite_original` date NOT NULL,
  `fecha_envio_central` datetime DEFAULT NULL,
  `fecha_recepcion_fisica` datetime DEFAULT NULL,
  `cod_responsable_envioFK` int DEFAULT NULL,
  `cod_usuario_recepcionFK` int DEFAULT NULL,
  `lote_archivo` varchar(100) DEFAULT NULL,
  `carpeta_archivo` varchar(100) DEFAULT NULL,
  `caja_archivo` varchar(100) DEFAULT NULL,
  `periodo_archivo` varchar(60) DEFAULT NULL,
  `ubicacion_fisica` varchar(255) DEFAULT NULL,
  `motivo_observacion` varchar(100) DEFAULT NULL,
  `comentario_observacion` text,
  `cod_responsable_observacionFK` int DEFAULT NULL,
  `cod_usuario_observacionFK` int DEFAULT NULL,
  `fecha_observacion` datetime DEFAULT NULL,
  `idgastosFK` int DEFAULT NULL,
  `cod_compraFK` int DEFAULT NULL,
  `fecha_vinculacion_pago` datetime DEFAULT NULL,
  `cod_usuario_vinculacion_pagoFK` int DEFAULT NULL,
  `firma_fiscal` char(64) DEFAULT NULL,
  `posible_duplicado` tinyint(1) NOT NULL DEFAULT 0,
  `duplicado_confirmado` tinyint(1) NOT NULL DEFAULT 0,
  `motivo_confirmacion_duplicado` varchar(255) DEFAULT NULL,
  `cod_usuario_confirmacion_duplicadoFK` int DEFAULT NULL,
  `fecha_confirmacion_duplicado` datetime DEFAULT NULL,
  `estado_registro` enum('activo','anulado') NOT NULL DEFAULT 'activo',
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `cod_usuario_anulacionFK` int DEFAULT NULL,
  `fecha_anulacion` datetime DEFAULT NULL,
  `cod_usuario_registroFK` int NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuario_actualizacionFK` int DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  `version_registro` int unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_factura`),
  UNIQUE KEY `uq_cf_gasto` (`idgastosFK`),
  UNIQUE KEY `uq_cf_compra` (`cod_compraFK`),
  KEY `idx_cf_local_fecha` (`cod_localFK`,`fecha_registro_digital`,`id_factura`),
  KEY `idx_cf_direccion_fuente` (`direccion`,`fuente`,`estado_registro`),
  KEY `idx_cf_original_limite` (`estado_registro`,`estado_original`,`fecha_limite_original`),
  KEY `idx_cf_validacion` (`estado_registro`,`estado_validacion`,`fecha_registro_digital`),
  KEY `idx_cf_proveedor` (`cod_proveedorFK`,`estado_registro`),
  KEY `idx_cf_funcionario` (`cod_funcionarioFK`,`estado_registro`),
  KEY `idx_cf_hilo` (`cod_interConsultaFK`,`estado_registro`),
  KEY `idx_cf_numero_factura` (`numero_factura_normalizado`,`timbrado`),
  KEY `idx_cf_firma_fiscal` (`firma_fiscal`,`estado_registro`),
  KEY `idx_cf_responsable_envio` (`cod_responsable_envioFK`,`estado_original`,`fecha_limite_original`),
  CONSTRAINT `fk_cf_hilo` FOREIGN KEY (`cod_interConsultaFK`) REFERENCES `interconsulta` (`cod_interConsulta`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_local` FOREIGN KEY (`cod_localFK`) REFERENCES `local` (`cod_local`),
  CONSTRAINT `fk_cf_proveedor` FOREIGN KEY (`cod_proveedorFK`) REFERENCES `proveedor` (`cod_proveedor`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_funcionario` FOREIGN KEY (`cod_funcionarioFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_resp_envio` FOREIGN KEY (`cod_responsable_envioFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_usuario_recepcion` FOREIGN KEY (`cod_usuario_recepcionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_resp_observacion` FOREIGN KEY (`cod_responsable_observacionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_usuario_observacion` FOREIGN KEY (`cod_usuario_observacionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_gasto` FOREIGN KEY (`idgastosFK`) REFERENCES `gastos` (`idgastos`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_compra` FOREIGN KEY (`cod_compraFK`) REFERENCES `compra` (`cod_compra`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_usuario_vinculo` FOREIGN KEY (`cod_usuario_vinculacion_pagoFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_usuario_dup` FOREIGN KEY (`cod_usuario_confirmacion_duplicadoFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_usuario_anula` FOREIGN KEY (`cod_usuario_anulacionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_usuario_registro` FOREIGN KEY (`cod_usuario_registroFK`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_cf_usuario_actualiza` FOREIGN KEY (`cod_usuario_actualizacionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

SET @cf_existe_tipo_documento := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE() AND table_name='centro_factura' AND column_name='tipo_documento'
);
SET @cf_sql := IF(
  @cf_existe_tipo_documento = 0,
  "ALTER TABLE `centro_factura` ADD COLUMN `tipo_documento` enum('factura','recibo') NOT NULL DEFAULT 'factura' AFTER `direccion`, ALGORITHM=INPLACE, LOCK=NONE",
  "SELECT 'centro_factura.tipo_documento ya existe' AS info"
);
PREPARE cf_stmt FROM @cf_sql;
EXECUTE cf_stmt;
DEALLOCATE PREPARE cf_stmt;

CREATE TABLE IF NOT EXISTS `centro_factura_archivo` (
  `id_archivo` int NOT NULL AUTO_INCREMENT,
  `id_facturaFK` int NOT NULL,
  `tipo_origen` enum('mensaje_hilo','carga_manual','evidencia_observacion','otro') NOT NULL,
  `cod_mensajeFK` int DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `nombre_original` varchar(255) DEFAULT NULL,
  `extension` varchar(15) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `hash_sha256` char(64) DEFAULT NULL,
  `orden_pagina` smallint unsigned NOT NULL DEFAULT 1,
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `cod_usuarioFK_create` int NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuarioFK_inactiva` int DEFAULT NULL,
  `fecha_inactivacion` datetime DEFAULT NULL,
  `motivo_inactivacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_archivo`),
  UNIQUE KEY `uq_cf_archivo_mensaje` (`cod_mensajeFK`),
  KEY `idx_cf_archivo_factura` (`id_facturaFK`,`estado`,`orden_pagina`),
  KEY `idx_cf_archivo_hash` (`hash_sha256`,`estado`),
  CONSTRAINT `fk_cf_archivo_factura` FOREIGN KEY (`id_facturaFK`) REFERENCES `centro_factura` (`id_factura`),
  CONSTRAINT `fk_cf_archivo_mensaje` FOREIGN KEY (`cod_mensajeFK`) REFERENCES `mensaje` (`cod_mensaje`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_archivo_usuario` FOREIGN KEY (`cod_usuarioFK_create`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_cf_archivo_usuario_inactiva` FOREIGN KEY (`cod_usuarioFK_inactiva`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `centro_factura_lote` (
  `id_lote` int NOT NULL AUTO_INCREMENT,
  `codigo_lote` varchar(40) NOT NULL,
  `cod_local_origenFK` int NOT NULL,
  `estado` enum('borrador','enviado','recibido_parcial','recibido','observado','anulado') NOT NULL DEFAULT 'borrador',
  `destino` varchar(150) NOT NULL DEFAULT 'Administracion central',
  `observaciones` text,
  `cod_usuario_entregaFK` int DEFAULT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `cod_usuario_envioFK` int DEFAULT NULL,
  `fecha_recepcion` datetime DEFAULT NULL,
  `cod_usuario_recepcionFK` int DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `cod_usuario_anulacionFK` int DEFAULT NULL,
  `fecha_anulacion` datetime DEFAULT NULL,
  `cod_usuarioFK_create` int NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuarioFK_update` int DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_lote`),
  UNIQUE KEY `uq_cf_lote_codigo` (`codigo_lote`),
  KEY `idx_cf_lote_local_estado` (`cod_local_origenFK`,`estado`,`fecha_creacion`),
  KEY `idx_cf_lote_envio` (`estado`,`fecha_envio`),
  CONSTRAINT `fk_cf_lote_local` FOREIGN KEY (`cod_local_origenFK`) REFERENCES `local` (`cod_local`),
  CONSTRAINT `fk_cf_lote_entrega` FOREIGN KEY (`cod_usuario_entregaFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_lote_usuario_envio` FOREIGN KEY (`cod_usuario_envioFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_lote_usuario_recibe` FOREIGN KEY (`cod_usuario_recepcionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_lote_usuario_anula` FOREIGN KEY (`cod_usuario_anulacionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_lote_usuario_create` FOREIGN KEY (`cod_usuarioFK_create`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_cf_lote_usuario_update` FOREIGN KEY (`cod_usuarioFK_update`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `centro_factura_lote_detalle` (
  `id_lote_detalle` int NOT NULL AUTO_INCREMENT,
  `id_loteFK` int NOT NULL,
  `id_facturaFK` int NOT NULL,
  `estado` enum('incluida','enviada','recibida','faltante','observada','retirada') NOT NULL DEFAULT 'incluida',
  `observacion` varchar(255) DEFAULT NULL,
  `fecha_estado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuario_estadoFK` int NOT NULL,
  PRIMARY KEY (`id_lote_detalle`),
  UNIQUE KEY `uq_cf_lote_factura` (`id_loteFK`,`id_facturaFK`),
  KEY `idx_cf_lote_det_factura` (`id_facturaFK`,`estado`),
  KEY `idx_cf_lote_det_estado` (`id_loteFK`,`estado`),
  CONSTRAINT `fk_cf_lote_det_lote` FOREIGN KEY (`id_loteFK`) REFERENCES `centro_factura_lote` (`id_lote`),
  CONSTRAINT `fk_cf_lote_det_factura` FOREIGN KEY (`id_facturaFK`) REFERENCES `centro_factura` (`id_factura`),
  CONSTRAINT `fk_cf_lote_det_usuario` FOREIGN KEY (`cod_usuario_estadoFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `centro_factura_ocr_sugerencia` (
  `id_sugerencia` int NOT NULL AUTO_INCREMENT,
  `id_archivoFK` int NOT NULL,
  `proveedor_motor` varchar(60) DEFAULT NULL,
  `estado` enum('sugerida','confirmada','descartada','error','no_disponible') NOT NULL DEFAULT 'sugerida',
  `documento_contraparte` varchar(45) DEFAULT NULL,
  `nombre_contraparte` varchar(255) DEFAULT NULL,
  `numero_factura` varchar(80) DEFAULT NULL,
  `timbrado` varchar(45) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `importe_total` decimal(15,2) DEFAULT NULL,
  `confianza` decimal(5,2) DEFAULT NULL,
  `datos_crudos` longtext,
  `cod_usuario_confirmacionFK` int DEFAULT NULL,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sugerencia`),
  KEY `idx_cf_ocr_archivo_estado` (`id_archivoFK`,`estado`),
  CONSTRAINT `fk_cf_ocr_archivo` FOREIGN KEY (`id_archivoFK`) REFERENCES `centro_factura_archivo` (`id_archivo`),
  CONSTRAINT `fk_cf_ocr_usuario` FOREIGN KEY (`cod_usuario_confirmacionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `centro_factura_auditoria` (
  `id_auditoria` bigint NOT NULL AUTO_INCREMENT,
  `entidad` varchar(30) NOT NULL DEFAULT 'factura',
  `id_entidad` int NOT NULL,
  `id_facturaFK` int DEFAULT NULL,
  `accion` varchar(60) NOT NULL,
  `valor_anterior` longtext,
  `valor_nuevo` longtext,
  `motivo` varchar(255) DEFAULT NULL,
  `cod_usuarioFK` int NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_cf_audit_factura_fecha` (`id_facturaFK`,`fecha_hora`,`id_auditoria`),
  KEY `idx_cf_audit_entidad` (`entidad`,`id_entidad`,`fecha_hora`),
  KEY `idx_cf_audit_usuario_fecha` (`cod_usuarioFK`,`fecha_hora`),
  CONSTRAINT `fk_cf_audit_factura` FOREIGN KEY (`id_facturaFK`) REFERENCES `centro_factura` (`id_factura`) ON DELETE SET NULL,
  CONSTRAINT `fk_cf_audit_usuario` FOREIGN KEY (`cod_usuarioFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Permisos explicitos. Todos nacen en NO: no se heredan ni se asignan masivamente.
INSERT INTO `listadodeacceso` (`nro`,`formulario`,`codigo`,`nombre`,`accion`,`orden`,`tipo`)
SELECT p.nro,p.formulario,p.codigo,p.nombre,'NO',p.orden,'Administrativo'
FROM (
  SELECT 1 AS nro,'CENTRO DE FACTURAS' AS formulario,'VERCENTROFACTURAS' AS codigo,'Ver Centro de Facturas' AS nombre,10 AS orden
  UNION ALL SELECT 2,'CENTRO DE FACTURAS','VERCENTROFACTURASTODOSLOCALES','Ver facturas de todos los locales',20
  UNION ALL SELECT 3,'CENTRO DE FACTURAS','REGISTRARFACTURAHILO','Registrar facturas desde Hilos',30
  UNION ALL SELECT 4,'CENTRO DE FACTURAS','REGISTRARFACTURAMANUAL','Registrar facturas manualmente',40
  UNION ALL SELECT 5,'CENTRO DE FACTURAS','VINCULARPAGOFACTURA','Vincular movimiento financiero existente',50
  UNION ALL SELECT 6,'CENTRO DE FACTURAS','ENVIARORIGINALFACTURA','Marcar originales enviados a central',60
  UNION ALL SELECT 7,'CENTRO DE FACTURAS','RECIBIRORIGINALFACTURA','Recibir y observar originales fisicos',70
  UNION ALL SELECT 8,'CENTRO DE FACTURAS','GESTIONARLOTESFACTURAS','Gestionar lotes de originales',80
  UNION ALL SELECT 9,'CENTRO DE FACTURAS','ADMINCENTROFACTURAS','Administrar, corregir y revertir facturas',90
) p
LEFT JOIN `listadodeacceso` l
  ON l.codigo=p.codigo AND l.tipo='Administrativo'
WHERE l.idlistadodeacceso IS NULL;

INSERT INTO `detallesniveles` (`accion`,`idlistadodeacceso`,`cod_nivelesfk`)
SELECT 'NO',l.idlistadodeacceso,n.cod_niveles
FROM `listadodeacceso` l
CROSS JOIN `listado_niveles` n
LEFT JOIN `detallesniveles` d
  ON d.idlistadodeacceso=l.idlistadodeacceso
 AND d.cod_nivelesfk=n.cod_niveles
WHERE l.formulario='CENTRO DE FACTURAS'
  AND l.tipo='Administrativo'
  AND n.tipo='Administrativo'
  AND d.iddetallesniveles IS NULL;

INSERT INTO `accesosuser` (`frmname`,`orden`,`idlistadodeaccesoFK`,`tipo`,`usuarios_idusario`,`accion`)
SELECT '',CAST(IFNULL(l.orden,0) AS UNSIGNED),l.idlistadodeacceso,'Administrativo',u.cod_usuario,'NO'
FROM `listadodeacceso` l
CROSS JOIN `usuario` u
LEFT JOIN `accesosuser` a
  ON a.idlistadodeaccesoFK=l.idlistadodeacceso
 AND a.tipo='Administrativo'
 AND a.usuarios_idusario=u.cod_usuario
WHERE l.formulario='CENTRO DE FACTURAS'
  AND l.tipo='Administrativo'
  AND u.estado='Activo'
  AND a.idaccesosUser IS NULL;

-- Acceso rapido predeterminado. No se alteran las selecciones personalizadas:
-- quienes ya las tengan pueden agregarlo desde "Editar accesos rapidos".
START TRANSACTION;

SET @cf_catalogo_existe := (
  SELECT COUNT(*)
  FROM `dashboard_access_catalog`
  WHERE `access_key`='centro_facturas'
);

UPDATE `dashboard_access_catalog`
SET `default_quick_order`=`default_quick_order`+1
WHERE @cf_catalogo_existe=0
  AND `is_default_quick_access`=1
  AND `default_quick_order`>=12;

INSERT INTO `dashboard_access_catalog`
  (`access_key`,`label`,`module_key`,`module_label`,`icon_key`,`route_path`,`permission_key`,`is_active`,`is_default_quick_access`,`default_quick_order`)
VALUES
  ('centro_facturas','Centro de Facturas','administrativo','Administrativo','centro-facturas',NULL,'VERCENTROFACTURAS',1,1,12)
ON DUPLICATE KEY UPDATE
  `label`=VALUES(`label`),
  `module_key`=VALUES(`module_key`),
  `module_label`=VALUES(`module_label`),
  `icon_key`=VALUES(`icon_key`),
  `route_path`=VALUES(`route_path`),
  `permission_key`=VALUES(`permission_key`),
  `is_active`=VALUES(`is_active`),
  `is_default_quick_access`=VALUES(`is_default_quick_access`),
  `default_quick_order`=VALUES(`default_quick_order`),
  `updated_at`=CURRENT_TIMESTAMP;

COMMIT;

-- Firma de verificacion posterior. Debe devolver siete tablas, nueve permisos,
-- un acceso de dashboard, plazo cinco y la columna de adjuntos.
SELECT
  (SELECT COUNT(*) FROM information_schema.tables
   WHERE table_schema=DATABASE() AND table_name IN (
     'centro_factura_configuracion','centro_factura','centro_factura_archivo',
     'centro_factura_lote','centro_factura_lote_detalle',
     'centro_factura_ocr_sugerencia','centro_factura_auditoria'
   )) AS tablas_centro_facturas,
  (SELECT COUNT(*) FROM listadodeacceso
   WHERE formulario='CENTRO DE FACTURAS' AND tipo='Administrativo') AS permisos_centro_facturas,
  (SELECT COUNT(*) FROM dashboard_access_catalog
   WHERE access_key='centro_facturas' AND permission_key='VERCENTROFACTURAS'
     AND is_active=1) AS acceso_dashboard_centro_facturas,
  (SELECT dias_plazo_original FROM centro_factura_configuracion WHERE id_configuracion=1) AS plazo_dias,
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema=DATABASE() AND table_name='mensaje' AND column_name='tipo_adjunto') AS columna_tipo_adjunto,
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema=DATABASE() AND table_name='centro_factura' AND column_name='tipo_documento') AS columna_tipo_documento;

-- Reversion controlada (no ejecutar si existen facturas o lotes que deban conservarse):
-- 1. Ocultar primero la interfaz y exportar centro_factura_*.
-- 2. DROP TABLE centro_factura_auditoria, centro_factura_ocr_sugerencia,
--    centro_factura_lote_detalle, centro_factura_lote, centro_factura_archivo,
--    centro_factura, centro_factura_configuracion;
-- 3. Restaurar el catalogo en forma exacta:
--    SET @cf_revertir_orden := (SELECT COUNT(*) FROM dashboard_access_catalog WHERE access_key='centro_facturas');
--    DELETE us FROM dashboard_user_shortcuts us INNER JOIN dashboard_access_catalog c
--      ON c.id=us.access_id WHERE c.access_key='centro_facturas';
--    DELETE FROM dashboard_access_catalog WHERE access_key='centro_facturas';
--    UPDATE dashboard_access_catalog SET default_quick_order=default_quick_order-1
--      WHERE @cf_revertir_orden=1 AND is_default_quick_access=1 AND default_quick_order>=13;
-- 4. Eliminar accesosuser/detallesniveles/listadodeacceso de formulario CENTRO DE FACTURAS.
-- 5. ALTER TABLE mensaje DROP COLUMN tipo_adjunto solo si ningun consumidor nuevo lo utiliza.
