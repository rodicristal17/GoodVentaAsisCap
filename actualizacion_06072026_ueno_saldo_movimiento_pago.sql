CREATE TABLE IF NOT EXISTS `ueno_movimiento_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_movimiento` int(11) NOT NULL,
  `cod_pagoFK` int(11) NOT NULL,
  `monto_aplicado` int(11) NOT NULL DEFAULT 0,
  `usuario_asocio` int(11) NOT NULL,
  `fecha_hora_asociacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(45) NOT NULL DEFAULT 'activo',
  `observacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ueno_mov_pago_activo` (`id_movimiento`,`cod_pagoFK`,`estado`),
  KEY `idx_ueno_mov_pago_pago` (`cod_pagoFK`),
  KEY `idx_ueno_mov_pago_movimiento_estado` (`id_movimiento`,`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
