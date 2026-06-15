-- Etapas 1 y 2 - Finanzas / Conciliacion bancaria Ueno
-- Ejecutar con respaldo previo. No modifica pagos historicos ni caja.

CREATE TABLE IF NOT EXISTS `ueno_importacion_extracto` (
  `id_importacion` int(11) NOT NULL AUTO_INCREMENT,
  `cuenta` varchar(45) NOT NULL,
  `denominacion` varchar(150) DEFAULT NULL,
  `fecha_extracto` date DEFAULT NULL,
  `periodo_desde` date DEFAULT NULL,
  `periodo_hasta` date DEFAULT NULL,
  `nombre_archivo_original` varchar(190) NOT NULL,
  `hash_archivo` varchar(128) NOT NULL,
  `usuario_importo` int(11) NOT NULL,
  `fecha_hora_importacion` datetime NOT NULL DEFAULT current_timestamp(),
  `cantidad_movimientos` int(11) NOT NULL DEFAULT 0,
  `cantidad_creditos` int(11) NOT NULL DEFAULT 0,
  `cantidad_debitos` int(11) NOT NULL DEFAULT 0,
  `total_creditos` int(11) NOT NULL DEFAULT 0,
  `total_debitos` int(11) NOT NULL DEFAULT 0,
  `estado` varchar(45) NOT NULL DEFAULT 'importado',
  `observacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_importacion`),
  UNIQUE KEY `uk_ueno_importacion_hash` (`hash_archivo`),
  KEY `idx_ueno_importacion_fecha` (`fecha_extracto`),
  KEY `idx_ueno_importacion_cuenta_fecha` (`cuenta`,`fecha_extracto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `ueno_movimiento_bancario` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_importacion` int(11) NOT NULL,
  `cuenta` varchar(45) NOT NULL,
  `fecha_confirmacion` date DEFAULT NULL,
  `fecha_transaccion` date DEFAULT NULL,
  `nro_comprobante` varchar(80) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `concepto` varchar(255) DEFAULT NULL,
  `importe_debito` int(11) NOT NULL DEFAULT 0,
  `importe_credito` int(11) NOT NULL DEFAULT 0,
  `tipo_movimiento` varchar(45) NOT NULL DEFAULT 'otro',
  `saldo_banco` int(11) DEFAULT NULL,
  `monto_disponible` int(11) NOT NULL DEFAULT 0,
  `estado` varchar(45) NOT NULL DEFAULT 'disponible',
  `hash_movimiento` varchar(128) NOT NULL,
  `fecha_hora_registro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_movimiento`),
  UNIQUE KEY `uk_ueno_movimiento_hash` (`hash_movimiento`),
  KEY `idx_ueno_movimiento_importacion` (`id_importacion`),
  KEY `idx_ueno_movimiento_comprobante` (`nro_comprobante`),
  KEY `idx_ueno_movimiento_cuenta_fecha` (`cuenta`,`fecha_confirmacion`,`fecha_transaccion`),
  CONSTRAINT `fk_ueno_mov_importacion` FOREIGN KEY (`id_importacion`) REFERENCES `ueno_importacion_extracto` (`id_importacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `pago_transferencia_conciliacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_pagoFK` int(11) NOT NULL,
  `grupo_pago` varchar(80) DEFAULT NULL,
  `nro_comprobante_informado` varchar(80) NOT NULL,
  `monto_pago` int(11) NOT NULL DEFAULT 0,
  `estado_conciliacion` varchar(45) NOT NULL DEFAULT 'pendiente_conciliacion',
  `usuario_registro` int(11) NOT NULL,
  `fecha_hora_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_ultima_revision` int(11) DEFAULT NULL,
  `fecha_hora_revision` datetime DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `origen` varchar(45) NOT NULL DEFAULT 'pago_credito',
  `activo` varchar(2) NOT NULL DEFAULT 'SI',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pago_transferencia_activo` (`cod_pagoFK`,`activo`),
  KEY `idx_pago_transferencia_comprobante` (`nro_comprobante_informado`),
  KEY `idx_pago_transferencia_estado` (`estado_conciliacion`),
  KEY `idx_pago_transferencia_grupo` (`grupo_pago`),
  CONSTRAINT `fk_pago_transferencia_pago` FOREIGN KEY (`cod_pagoFK`) REFERENCES `pago` (`idPago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `ueno_movimiento_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_movimiento` int(11) NOT NULL,
  `cod_pagoFK` int(11) NOT NULL,
  `monto_aplicado` int(11) NOT NULL DEFAULT 0,
  `usuario_asocio` int(11) NOT NULL,
  `fecha_hora_asociacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(45) NOT NULL DEFAULT 'activo',
  `observacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ueno_mov_pago_activo` (`id_movimiento`,`cod_pagoFK`,`estado`),
  KEY `idx_ueno_mov_pago_pago` (`cod_pagoFK`),
  CONSTRAINT `fk_ueno_mov_pago_mov` FOREIGN KEY (`id_movimiento`) REFERENCES `ueno_movimiento_bancario` (`id_movimiento`),
  CONSTRAINT `fk_ueno_mov_pago_pago` FOREIGN KEY (`cod_pagoFK`) REFERENCES `pago` (`idPago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'VERCONCILIACIONUENO', 'VER ICONO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='VERCONCILIACIONUENO' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'IMPORTAREXTRACTOUENO', 'IMPORTAR EXTRACTO UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='IMPORTAREXTRACTOUENO' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'VEREXTRACTOSUENO', 'VER EXTRACTOS UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='VEREXTRACTOSUENO' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'VERPAGOSPENDIENTESUENO', 'VER PAGOS PENDIENTES UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='VERPAGOSPENDIENTESUENO' AND `tipo`='Administrativo');
