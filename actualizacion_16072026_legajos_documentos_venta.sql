-- Legajos documentales de ventas y envios internos de Clinident Salud.
-- Compatible con MySQL 5.7+/8 y PHP 7.2.
-- Cambio aditivo: no transforma ventas, adjuntos, facturas ni lotes existentes.
-- Ejecutar con respaldo previo y antes de publicar la cuarta pestana del Centro.

SET NAMES latin1;
SET SESSION lock_wait_timeout = 15;

-- Preflight bloqueante: evita aplicar DDL o permisos sobre un esquema incompleto.
DROP PROCEDURE IF EXISTS preflight_legajos_venta_16072026;

DELIMITER $$

CREATE PROCEDURE preflight_legajos_venta_16072026()
BEGIN
  DECLARE tablas_base INT DEFAULT 0;
  SELECT COUNT(*) INTO tablas_base
  FROM information_schema.tables
  WHERE table_schema = DATABASE()
    AND table_name IN (
      'venta','detalle_venta','cliente','persona','local','usuario','cancelaciones',
      'listadodeacceso','detallesniveles','listado_niveles','accesosuser'
    );
  IF tablas_base <> 11 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Legajos de ventas: faltan tablas base; migracion cancelada';
  END IF;
  SELECT 'OK' AS preflight_legajos_venta;
END$$

DELIMITER ;

CALL preflight_legajos_venta_16072026();
DROP PROCEDURE IF EXISTS preflight_legajos_venta_16072026;

-- Una fila representa una copia fisica concreta dentro del legajo de una venta.
-- No se agrega FK a venta para no bloquear el flujo legacy que mueve ventas eliminadas;
-- el identificador se conserva como referencia historica y se valida en servidor.
CREATE TABLE IF NOT EXISTS `centro_legajo_documento` (
  `id_documento` int NOT NULL AUTO_INCREMENT,
  `cod_ventaFK` int NOT NULL,
  `tipo_documento` enum('contrato','pagare','consentimiento','cedula','detalle_venta') NOT NULL,
  `es_requerido` tinyint(1) NOT NULL DEFAULT 1,
  `estado_documental` enum('pendiente','disponible','validado','observado','no_aplica') NOT NULL DEFAULT 'pendiente',
  `estado_fisico` enum('pendiente','en_sucursal','en_lote','pendiente_custodia','en_transito','recibido','faltante','observado','no_aplica') NOT NULL DEFAULT 'pendiente',
  `cod_local_ubicacionFK` int DEFAULT NULL,
  `ubicacion_fisica` varchar(255) DEFAULT NULL,
  `observaciones` text,
  `cod_usuario_confirmacionFK` int DEFAULT NULL,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `cod_usuarioFK_create` int NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuarioFK_update` int DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  `version_registro` int unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_documento`),
  UNIQUE KEY `uq_cld_venta_tipo` (`cod_ventaFK`,`tipo_documento`),
  KEY `idx_cld_venta_estado` (`cod_ventaFK`,`estado_documental`,`estado_fisico`),
  KEY `idx_cld_ubicacion` (`cod_local_ubicacionFK`,`estado_fisico`),
  KEY `idx_cld_usuario_confirma` (`cod_usuario_confirmacionFK`,`fecha_confirmacion`),
  CONSTRAINT `fk_cld_local_ubicacion` FOREIGN KEY (`cod_local_ubicacionFK`) REFERENCES `local` (`cod_local`) ON DELETE SET NULL,
  CONSTRAINT `fk_cld_usuario_confirma` FOREIGN KEY (`cod_usuario_confirmacionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cld_usuario_create` FOREIGN KEY (`cod_usuarioFK_create`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_cld_usuario_update` FOREIGN KEY (`cod_usuarioFK_update`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `centro_legajo_lote` (
  `id_lote` int NOT NULL AUTO_INCREMENT,
  `codigo_lote` varchar(40) NOT NULL,
  `cod_local_origenFK` int NOT NULL,
  `cod_local_destinoFK` int NOT NULL,
  `destino_snapshot` varchar(150) NOT NULL,
  `estado` enum('borrador','pendiente_custodia','en_transito','recibido_parcial','recibido','observado','anulado') NOT NULL DEFAULT 'borrador',
  `observaciones` text,
  `cod_usuario_transportistaFK` int DEFAULT NULL,
  `fecha_asignacion_transportista` datetime DEFAULT NULL,
  `cod_usuario_envioFK` int DEFAULT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `cod_usuario_custodiaFK` int DEFAULT NULL,
  `fecha_aceptacion_custodia` datetime DEFAULT NULL,
  `cod_usuario_recepcionFK` int DEFAULT NULL,
  `fecha_recepcion` datetime DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `cod_usuario_anulacionFK` int DEFAULT NULL,
  `fecha_anulacion` datetime DEFAULT NULL,
  `cod_usuarioFK_create` int NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuarioFK_update` int DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_lote`),
  UNIQUE KEY `uq_cll_codigo` (`codigo_lote`),
  KEY `idx_cll_origen_estado` (`cod_local_origenFK`,`estado`,`fecha_creacion`),
  KEY `idx_cll_destino_estado` (`cod_local_destinoFK`,`estado`,`fecha_creacion`),
  KEY `idx_cll_transportista` (`cod_usuario_transportistaFK`,`estado`,`fecha_envio`),
  CONSTRAINT `fk_cll_local_origen` FOREIGN KEY (`cod_local_origenFK`) REFERENCES `local` (`cod_local`),
  CONSTRAINT `fk_cll_local_destino` FOREIGN KEY (`cod_local_destinoFK`) REFERENCES `local` (`cod_local`),
  CONSTRAINT `fk_cll_transportista` FOREIGN KEY (`cod_usuario_transportistaFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cll_usuario_envio` FOREIGN KEY (`cod_usuario_envioFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cll_usuario_custodia` FOREIGN KEY (`cod_usuario_custodiaFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cll_usuario_recepcion` FOREIGN KEY (`cod_usuario_recepcionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cll_usuario_anulacion` FOREIGN KEY (`cod_usuario_anulacionFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_cll_usuario_create` FOREIGN KEY (`cod_usuarioFK_create`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_cll_usuario_update` FOREIGN KEY (`cod_usuarioFK_update`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- El usuario selecciona legajos completos, pero el lote controla cada documento
-- para admitir faltantes, observaciones y recepciones parciales sin perder el grupo.
CREATE TABLE IF NOT EXISTS `centro_legajo_lote_detalle` (
  `id_lote_detalle` int NOT NULL AUTO_INCREMENT,
  `id_loteFK` int NOT NULL,
  `id_documentoFK` int NOT NULL,
  `cod_ventaFK` int NOT NULL,
  `estado` enum('incluido','pendiente_custodia','en_transito','recibido','faltante','observado','retirado') NOT NULL DEFAULT 'incluido',
  `observacion` varchar(255) DEFAULT NULL,
  `fecha_estado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuario_estadoFK` int NOT NULL,
  PRIMARY KEY (`id_lote_detalle`),
  UNIQUE KEY `uq_clld_lote_documento` (`id_loteFK`,`id_documentoFK`),
  KEY `idx_clld_documento_estado` (`id_documentoFK`,`estado`),
  KEY `idx_clld_venta_estado` (`cod_ventaFK`,`estado`),
  KEY `idx_clld_lote_estado` (`id_loteFK`,`estado`),
  CONSTRAINT `fk_clld_lote` FOREIGN KEY (`id_loteFK`) REFERENCES `centro_legajo_lote` (`id_lote`),
  CONSTRAINT `fk_clld_documento` FOREIGN KEY (`id_documentoFK`) REFERENCES `centro_legajo_documento` (`id_documento`),
  CONSTRAINT `fk_clld_usuario_estado` FOREIGN KEY (`cod_usuario_estadoFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Bitacora append-only del recorrido y de la custodia del lote.
CREATE TABLE IF NOT EXISTS `centro_legajo_lote_evento` (
  `id_evento` bigint NOT NULL AUTO_INCREMENT,
  `id_loteFK` int NOT NULL,
  `tipo_evento` varchar(50) NOT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `estado_nuevo` varchar(30) DEFAULT NULL,
  `detalle` text,
  `cod_usuario_actorFK` int NOT NULL,
  `cod_usuario_responsableFK` int DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evento`),
  KEY `idx_clle_lote_fecha` (`id_loteFK`,`fecha_hora`,`id_evento`),
  KEY `idx_clle_responsable` (`cod_usuario_responsableFK`,`fecha_hora`),
  CONSTRAINT `fk_clle_lote` FOREIGN KEY (`id_loteFK`) REFERENCES `centro_legajo_lote` (`id_lote`),
  CONSTRAINT `fk_clle_usuario_actor` FOREIGN KEY (`cod_usuario_actorFK`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_clle_usuario_responsable` FOREIGN KEY (`cod_usuario_responsableFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Historial append-only de cada copia antes y despues de formar un lote.
CREATE TABLE IF NOT EXISTS `centro_legajo_documento_evento` (
  `id_evento` bigint NOT NULL AUTO_INCREMENT,
  `id_documentoFK` int NOT NULL,
  `cod_ventaFK` int NOT NULL,
  `accion` varchar(50) NOT NULL,
  `estado_documental_anterior` varchar(30) DEFAULT NULL,
  `estado_documental_nuevo` varchar(30) DEFAULT NULL,
  `estado_fisico_anterior` varchar(30) DEFAULT NULL,
  `estado_fisico_nuevo` varchar(30) DEFAULT NULL,
  `detalle` text,
  `cod_usuario_actorFK` int NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evento`),
  KEY `idx_clde_documento_fecha` (`id_documentoFK`,`fecha_hora`,`id_evento`),
  KEY `idx_clde_venta_fecha` (`cod_ventaFK`,`fecha_hora`,`id_evento`),
  CONSTRAINT `fk_clde_documento` FOREIGN KEY (`id_documentoFK`) REFERENCES `centro_legajo_documento` (`id_documento`),
  CONSTRAINT `fk_clde_usuario_actor` FOREIGN KEY (`cod_usuario_actorFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Permisos especificos. Nacen en NO y ADMINCENTROFACTURAS funciona como
-- superpermiso tecnico sin otorgarlos de forma masiva.
INSERT INTO `listadodeacceso` (`nro`,`formulario`,`codigo`,`nombre`,`accion`,`orden`,`tipo`)
SELECT p.nro,p.formulario,p.codigo,p.nombre,'NO',p.orden,'Administrativo'
FROM (
  SELECT 10 AS nro,'CENTRO DE FACTURAS' AS formulario,'VERLEGAJOSVENTA' AS codigo,'Ver legajos documentales de ventas' AS nombre,100 AS orden
  UNION ALL SELECT 11,'CENTRO DE FACTURAS','GESTIONARLEGAJOSVENTA','Confirmar y observar copias de legajos',110
  UNION ALL SELECT 12,'CENTRO DE FACTURAS','GESTIONARLOTESLEGAJOS','Preparar lotes separados de legajos',120
  UNION ALL SELECT 13,'CENTRO DE FACTURAS','ENVIARLOTELEGAJOS','Enviar y aceptar custodia de legajos',130
  UNION ALL SELECT 14,'CENTRO DE FACTURAS','RECIBIRLOTELEGAJOS','Recibir y observar documentos de legajos',140
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
WHERE l.codigo IN ('VERLEGAJOSVENTA','GESTIONARLEGAJOSVENTA','GESTIONARLOTESLEGAJOS','ENVIARLOTELEGAJOS','RECIBIRLOTELEGAJOS')
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
WHERE l.codigo IN ('VERLEGAJOSVENTA','GESTIONARLEGAJOSVENTA','GESTIONARLOTESLEGAJOS','ENVIARLOTELEGAJOS','RECIBIRLOTELEGAJOS')
  AND l.tipo='Administrativo'
  AND u.estado='Activo'
  AND a.idaccesosUser IS NULL;

-- Firma posterior: 5 tablas, 5 permisos y ningun permiso nuevo otorgado.
SELECT
  (SELECT COUNT(*) FROM information_schema.tables
   WHERE table_schema=DATABASE() AND table_name IN (
     'centro_legajo_documento','centro_legajo_lote',
     'centro_legajo_lote_detalle','centro_legajo_lote_evento',
     'centro_legajo_documento_evento'
   )) AS tablas_legajos,
  (SELECT COUNT(*) FROM listadodeacceso
   WHERE codigo IN ('VERLEGAJOSVENTA','GESTIONARLEGAJOSVENTA','GESTIONARLOTESLEGAJOS','ENVIARLOTELEGAJOS','RECIBIRLOTELEGAJOS')
     AND tipo='Administrativo') AS permisos_legajos,
  (SELECT COUNT(*) FROM accesosuser a
   INNER JOIN listadodeacceso l ON l.idlistadodeacceso=a.idlistadodeaccesoFK
   WHERE l.codigo IN ('VERLEGAJOSVENTA','GESTIONARLEGAJOSVENTA','GESTIONARLOTESLEGAJOS','ENVIARLOTELEGAJOS','RECIBIRLOTELEGAJOS')
     AND a.tipo='Administrativo' AND a.accion='SI') AS permisos_nuevos_otorgados;

-- Reversion controlada (solo si no existe trazabilidad que deba conservarse):
-- 1. Ocultar la pestana y exportar centro_legajo_*.
-- 2. DROP TABLE centro_legajo_documento_evento, centro_legajo_lote_evento, centro_legajo_lote_detalle,
--    centro_legajo_lote, centro_legajo_documento;
-- 3. Eliminar accesosuser y detallesniveles de los cinco codigos, luego
--    eliminar sus filas de listadodeacceso.
