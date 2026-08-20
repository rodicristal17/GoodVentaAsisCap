-- Transferencias internas entre cuentas bancarias de Clinident Salud.
-- Conserva los movimientos originales, neutraliza su uso operativo y registra
-- vinculacion/reversion con responsable, fecha y valores anteriores.

CREATE TABLE IF NOT EXISTS `ueno_transferencia_interna` (
  `id_transferencia` bigint NOT NULL AUTO_INCREMENT,
  `id_movimiento_debitoFK` int NOT NULL,
  `id_movimiento_creditoFK` int NOT NULL,
  `banco_origen` varchar(20) NOT NULL,
  `banco_destino` varchar(20) NOT NULL,
  `monto` bigint NOT NULL,
  `fecha_debito` date NOT NULL,
  `fecha_credito` date NOT NULL,
  `estado_debito_anterior` varchar(45) NOT NULL,
  `estado_credito_anterior` varchar(45) NOT NULL,
  `disponible_credito_anterior` bigint NOT NULL,
  `cod_usuario_vinculaFK` int NOT NULL,
  `fecha_hora_vinculacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_transferencia`),
  UNIQUE KEY `uk_uti_debito` (`id_movimiento_debitoFK`),
  UNIQUE KEY `uk_uti_credito` (`id_movimiento_creditoFK`),
  KEY `idx_uti_fecha` (`fecha_hora_vinculacion`,`id_transferencia`),
  KEY `idx_uti_bancos` (`banco_origen`,`banco_destino`,`fecha_debito`),
  CONSTRAINT `fk_uti_debito` FOREIGN KEY (`id_movimiento_debitoFK`) REFERENCES `ueno_movimiento_bancario` (`id_movimiento`),
  CONSTRAINT `fk_uti_credito` FOREIGN KEY (`id_movimiento_creditoFK`) REFERENCES `ueno_movimiento_bancario` (`id_movimiento`),
  CONSTRAINT `fk_uti_usuario` FOREIGN KEY (`cod_usuario_vinculaFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `ueno_transferencia_interna_evento` (
  `id_evento` bigint NOT NULL AUTO_INCREMENT,
  `id_transferencia_snapshot` bigint NOT NULL,
  `accion` varchar(20) NOT NULL,
  `id_movimiento_debitoFK` int NOT NULL,
  `id_movimiento_creditoFK` int NOT NULL,
  `banco_origen` varchar(20) NOT NULL,
  `banco_destino` varchar(20) NOT NULL,
  `monto` bigint NOT NULL,
  `fecha_debito` date NOT NULL,
  `fecha_credito` date NOT NULL,
  `cod_usuario_actorFK` int NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `motivo` varchar(500) DEFAULT NULL,
  `datos_json` longtext,
  PRIMARY KEY (`id_evento`),
  KEY `idx_utie_transferencia` (`id_transferencia_snapshot`,`fecha_hora`),
  KEY `idx_utie_debito` (`id_movimiento_debitoFK`,`fecha_hora`),
  KEY `idx_utie_credito` (`id_movimiento_creditoFK`,`fecha_hora`),
  KEY `idx_utie_actor` (`cod_usuario_actorFK`,`fecha_hora`),
  CONSTRAINT `fk_utie_debito` FOREIGN KEY (`id_movimiento_debitoFK`) REFERENCES `ueno_movimiento_bancario` (`id_movimiento`),
  CONSTRAINT `fk_utie_credito` FOREIGN KEY (`id_movimiento_creditoFK`) REFERENCES `ueno_movimiento_bancario` (`id_movimiento`),
  CONSTRAINT `fk_utie_actor` FOREIGN KEY (`cod_usuario_actorFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

DELIMITER $$

DROP TRIGGER IF EXISTS `trg_ueno_transferencia_interna_bi`$$
CREATE TRIGGER `trg_ueno_transferencia_interna_bi`
BEFORE INSERT ON `ueno_transferencia_interna`
FOR EACH ROW
BEGIN
  DECLARE v_tipo_debito varchar(45) DEFAULT '';
  DECLARE v_tipo_credito varchar(45) DEFAULT '';
  DECLARE v_debito bigint DEFAULT 0;
  DECLARE v_credito bigint DEFAULT 0;
  DECLARE v_disponible bigint DEFAULT 0;
  DECLARE v_banco_debito varchar(20) DEFAULT '';
  DECLARE v_banco_credito varchar(20) DEFAULT '';
  DECLARE v_fecha_debito date DEFAULT NULL;
  DECLARE v_fecha_credito date DEFAULT NULL;
  DECLARE v_estado_debito varchar(45) DEFAULT '';
  DECLARE v_estado_credito varchar(45) DEFAULT '';

  IF NEW.id_movimiento_debitoFK = NEW.id_movimiento_creditoFK OR NEW.monto <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transferencia interna: movimientos o monto invalidos';
  END IF;

  IF NOT EXISTS (SELECT 1 FROM gasto_tesoreria_configuracion
    WHERE id_configuracion=1 AND cod_usuario_responsableFK=NEW.cod_usuario_vinculaFK) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transferencia interna: solo la responsable oficial de Tesoreria puede vincular';
  END IF;

  SELECT tipo_movimiento, IFNULL(importe_debito,0), banco_codigo,
    LOWER(TRIM(IFNULL(estado,''))),
    COALESCE(NULLIF(fecha_confirmacion,'0000-00-00'), fecha_transaccion)
  INTO v_tipo_debito, v_debito, v_banco_debito, v_estado_debito, v_fecha_debito
  FROM ueno_movimiento_bancario
  WHERE id_movimiento=NEW.id_movimiento_debitoFK
  LIMIT 1;

  SELECT tipo_movimiento, IFNULL(importe_credito,0), IFNULL(monto_disponible,0), banco_codigo,
    LOWER(TRIM(IFNULL(estado,''))),
    COALESCE(NULLIF(fecha_confirmacion,'0000-00-00'), fecha_transaccion)
  INTO v_tipo_credito, v_credito, v_disponible, v_banco_credito, v_estado_credito, v_fecha_credito
  FROM ueno_movimiento_bancario
  WHERE id_movimiento=NEW.id_movimiento_creditoFK
  LIMIT 1;

  IF LOWER(v_tipo_debito) <> 'debito' OR LOWER(v_tipo_credito) <> 'credito'
    OR v_debito <> NEW.monto OR v_credito <> NEW.monto OR v_disponible <> NEW.monto THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transferencia interna: debito y credito deben estar completos y coincidir exactamente';
  END IF;

  IF v_estado_debito IN ('conciliado','conciliada','asignado_total','anulado','anulada','rechazado','rechazada','duplicado','ignorado')
    OR v_estado_credito IN ('conciliado','conciliada','asignado_total','anulado','anulada','rechazado','rechazada','duplicado','ignorado') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transferencia interna: uno de los movimientos ya no esta disponible';
  END IF;

  IF UPPER(v_banco_debito) = UPPER(v_banco_credito)
    OR UPPER(v_banco_debito) <> UPPER(NEW.banco_origen)
    OR UPPER(v_banco_credito) <> UPPER(NEW.banco_destino) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transferencia interna: los bancos deben ser distintos y coincidir con los movimientos';
  END IF;

  IF v_fecha_debito IS NULL OR v_fecha_credito IS NULL
    OR ABS(DATEDIFF(v_fecha_debito,v_fecha_credito)) > 3
    OR NEW.fecha_debito <> v_fecha_debito OR NEW.fecha_credito <> v_fecha_credito THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transferencia interna: las fechas deben coincidir y estar dentro de tres dias';
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_ueno_transferencia_interna_bu`$$
CREATE TRIGGER `trg_ueno_transferencia_interna_bu`
BEFORE UPDATE ON `ueno_transferencia_interna`
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transferencia interna: el vinculo activo es inmutable; debe revertirse';
END$$

DROP TRIGGER IF EXISTS `trg_ueno_transferencia_evento_bi`$$
CREATE TRIGGER `trg_ueno_transferencia_evento_bi`
BEFORE INSERT ON `ueno_transferencia_interna_evento`
FOR EACH ROW
BEGIN
  IF NEW.accion NOT IN ('vinculada','revertida') OR NEW.monto <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transferencia interna: evento invalido';
  END IF;
  IF NEW.accion='revertida' AND (NEW.motivo IS NULL OR CHAR_LENGTH(TRIM(NEW.motivo)) < 5) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transferencia interna: la reversion requiere un motivo';
  END IF;
  IF NOT EXISTS (SELECT 1 FROM gasto_tesoreria_configuracion
    WHERE id_configuracion=1 AND cod_usuario_responsableFK=NEW.cod_usuario_actorFK) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transferencia interna: solo la responsable oficial de Tesoreria puede registrar eventos';
  END IF;
END$$

DELIMITER ;
