-- Responsable oficial de Tesoreria y correcciones trazables de movimientos.
-- Compatible con MySQL/MariaDB y ejecutable mas de una vez.

CREATE TABLE IF NOT EXISTS `gasto_tesoreria_configuracion` (
  `id_configuracion` tinyint unsigned NOT NULL DEFAULT 1,
  `cod_usuario_responsableFK` int DEFAULT NULL,
  `cod_usuario_configuraFK` int DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_configuracion`),
  KEY `idx_gtc_responsable` (`cod_usuario_responsableFK`),
  KEY `idx_gtc_configura` (`cod_usuario_configuraFK`),
  CONSTRAINT `fk_gtc_responsable` FOREIGN KEY (`cod_usuario_responsableFK`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_gtc_configura` FOREIGN KEY (`cod_usuario_configuraFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT IGNORE INTO `gasto_tesoreria_configuracion`
  (`id_configuracion`,`cod_usuario_responsableFK`,`cod_usuario_configuraFK`,`fecha_creacion`)
VALUES (1,NULL,NULL,NOW());

CREATE TABLE IF NOT EXISTS `gasto_tesoreria_responsable_evento` (
  `id_evento` bigint NOT NULL AUTO_INCREMENT,
  `cod_usuario_anteriorFK` int DEFAULT NULL,
  `cod_usuario_nuevoFK` int DEFAULT NULL,
  `cod_usuario_actorFK` int NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evento`),
  KEY `idx_gtre_fecha` (`fecha_hora`,`id_evento`),
  KEY `idx_gtre_actor` (`cod_usuario_actorFK`,`fecha_hora`),
  CONSTRAINT `fk_gtre_anterior` FOREIGN KEY (`cod_usuario_anteriorFK`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_gtre_nuevo` FOREIGN KEY (`cod_usuario_nuevoFK`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_gtre_actor` FOREIGN KEY (`cod_usuario_actorFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `gasto_tesoreria_modificacion` (
  `id_modificacion` bigint NOT NULL AUTO_INCREMENT,
  `idgastosFK` int NOT NULL,
  `tipo_modificacion` varchar(30) NOT NULL,
  `estado_movimiento` varchar(25) NOT NULL,
  `alcance_monto` varchar(20) NOT NULL,
  `alcance_fecha` varchar(20) NOT NULL,
  `motivo` varchar(500) NOT NULL,
  `valores_anteriores_json` longtext NOT NULL,
  `valores_nuevos_json` longtext NOT NULL,
  `ids_afectados_json` longtext NOT NULL,
  `cod_usuario_actorFK` int NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_modificacion`),
  KEY `idx_gtm_gasto_fecha` (`idgastosFK`,`fecha_hora`,`id_modificacion`),
  KEY `idx_gtm_actor_fecha` (`cod_usuario_actorFK`,`fecha_hora`),
  KEY `idx_gtm_tipo_fecha` (`tipo_modificacion`,`fecha_hora`),
  CONSTRAINT `fk_gtm_gasto` FOREIGN KEY (`idgastosFK`) REFERENCES `gastos` (`idgastos`),
  CONSTRAINT `fk_gtm_actor` FOREIGN KEY (`cod_usuario_actorFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `gasto_tesoreria_impacto` (
  `id_impacto` bigint NOT NULL AUTO_INCREMENT,
  `id_modificacionFK` bigint NOT NULL,
  `idgastosFK` int NOT NULL,
  `cod_localFK` int NOT NULL,
  `cod_local_pago_snapshot` int NOT NULL,
  `cod_motivoIngresoEgresoFK` int NOT NULL,
  `fecha_impacto` date NOT NULL,
  `monto_impacto` bigint NOT NULL,
  `tipo_impacto` varchar(15) NOT NULL,
  `cod_usuario_actorFK` int NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_impacto`),
  KEY `idx_gti_flujo` (`cod_localFK`,`cod_motivoIngresoEgresoFK`,`fecha_impacto`),
  KEY `idx_gti_gasto_fecha` (`idgastosFK`,`fecha_hora`,`id_impacto`),
  KEY `idx_gti_modificacion` (`id_modificacionFK`,`id_impacto`),
  CONSTRAINT `fk_gti_modificacion` FOREIGN KEY (`id_modificacionFK`) REFERENCES `gasto_tesoreria_modificacion` (`id_modificacion`),
  CONSTRAINT `fk_gti_gasto` FOREIGN KEY (`idgastosFK`) REFERENCES `gastos` (`idgastos`),
  CONSTRAINT `fk_gti_local` FOREIGN KEY (`cod_localFK`) REFERENCES `local` (`cod_local`),
  CONSTRAINT `fk_gti_local_pago` FOREIGN KEY (`cod_local_pago_snapshot`) REFERENCES `local` (`cod_local`),
  CONSTRAINT `fk_gti_actor` FOREIGN KEY (`cod_usuario_actorFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- El permiso nace negado para todos. Ademas del permiso, el servidor valida
-- expresamente la identidad de Carlos Faraone antes de mostrar o usar el engranaje.
INSERT INTO `listadodeacceso` (`nro`,`formulario`,`codigo`,`nombre`,`accion`,`orden`,`tipo`)
SELECT 5,'tesoreria','CONFIGURARRESPONSABLETESORERIA','Configurar responsable oficial de Tesoreria','NO',410,'Administrativo'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `listadodeacceso`
  WHERE `codigo`='CONFIGURARRESPONSABLETESORERIA' AND `tipo`='Administrativo'
);

INSERT INTO `detallesniveles` (`accion`,`idlistadodeacceso`,`cod_nivelesfk`)
SELECT 'NO',l.idlistadodeacceso,n.cod_niveles
FROM `listadodeacceso` l
CROSS JOIN `listado_niveles` n
LEFT JOIN `detallesniveles` d
  ON d.idlistadodeacceso=l.idlistadodeacceso
 AND d.cod_nivelesfk=n.cod_niveles
WHERE l.codigo='CONFIGURARRESPONSABLETESORERIA'
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
WHERE l.codigo='CONFIGURARRESPONSABLETESORERIA'
  AND l.tipo='Administrativo'
  AND u.estado='Activo'
  AND a.idaccesosUser IS NULL;

-- El catalogo, los niveles y todas las excepciones individuales permanecen
-- negados. La concesion se realiza despues solamente para Carlos.
UPDATE `listadodeacceso`
SET `accion`='NO'
WHERE `codigo`='CONFIGURARRESPONSABLETESORERIA'
  AND `tipo`='Administrativo';

UPDATE `detallesniveles` d
INNER JOIN `listadodeacceso` l ON l.idlistadodeacceso=d.idlistadodeacceso
SET d.accion='NO'
WHERE l.codigo='CONFIGURARRESPONSABLETESORERIA'
  AND l.tipo='Administrativo';

UPDATE `accesosuser` a
INNER JOIN `listadodeacceso` l ON l.idlistadodeacceso=a.idlistadodeaccesoFK
SET a.accion='NO'
WHERE l.codigo='CONFIGURARRESPONSABLETESORERIA'
  AND l.tipo='Administrativo';

UPDATE `accesosuser` a
INNER JOIN `listadodeacceso` l ON l.idlistadodeacceso=a.idlistadodeaccesoFK
INNER JOIN `usuario` u ON u.cod_usuario=a.usuarios_idusario AND u.estado='Activo'
INNER JOIN `persona` p ON p.cod_persona=u.cod_usuario
SET a.accion='SI'
WHERE l.codigo='CONFIGURARRESPONSABLETESORERIA'
  AND l.tipo='Administrativo'
  AND u.cod_usuario=5994
  AND UPPER(TRIM(p.nombre_persona)) LIKE 'CARLOS FARAONE%';

SELECT
  (SELECT COUNT(*) FROM information_schema.tables
   WHERE table_schema=DATABASE() AND table_name IN (
     'gasto_tesoreria_configuracion','gasto_tesoreria_responsable_evento',
     'gasto_tesoreria_modificacion','gasto_tesoreria_impacto'
   )) AS tablas_tesoreria,
  (SELECT COUNT(*) FROM gasto_tesoreria_configuracion WHERE id_configuracion=1) AS configuracion_unica,
  (SELECT COUNT(*) FROM accesosuser a
   INNER JOIN listadodeacceso l ON l.idlistadodeacceso=a.idlistadodeaccesoFK
   WHERE l.codigo='CONFIGURARRESPONSABLETESORERIA' AND a.accion='SI') AS configuradores_habilitados;

-- Reversion controlada:
-- 1. Ocultar el engranaje y desactivar CONFIGURARRESPONSABLETESORERIA.
-- 2. No borrar las tablas si ya contienen correcciones: forman parte del historial financiero.
-- 3. Para revertir una correccion, registrar una nueva correccion compensatoria.
