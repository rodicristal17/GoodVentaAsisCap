-- Etapa 6 - Reglas fuertes Ueno
-- Ejecutar despues de las etapas 1 a 5, con respaldo previo.
-- Agrega auditoria y triggers defensivos. No modifica pagos historicos ni caja.

CREATE TABLE IF NOT EXISTS `ueno_auditoria_conciliacion` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `tabla_afectada` varchar(80) NOT NULL,
  `registro_id` varchar(45) DEFAULT NULL,
  `cod_pagoFK` int(11) DEFAULT NULL,
  `id_movimiento` int(11) DEFAULT NULL,
  `accion` varchar(80) NOT NULL,
  `estado_anterior` varchar(45) DEFAULT NULL,
  `estado_nuevo` varchar(45) DEFAULT NULL,
  `monto` int(11) NOT NULL DEFAULT 0,
  `usuario` int(11) DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` varchar(255) DEFAULT NULL,
  `datos` text DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_ueno_audit_pago` (`cod_pagoFK`),
  KEY `idx_ueno_audit_movimiento` (`id_movimiento`),
  KEY `idx_ueno_audit_fecha` (`fecha_hora`),
  KEY `idx_ueno_audit_accion` (`accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'VERAUDITORIAUENO', 'VER AUDITORIA UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='VERAUDITORIAUENO' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'REGLASFUERTESUENO', 'ADMINISTRAR REGLAS FUERTES UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='REGLASFUERTESUENO' AND `tipo`='Administrativo');

DELIMITER $$

DROP TRIGGER IF EXISTS `trg_ueno_movimiento_bi`$$
CREATE TRIGGER `trg_ueno_movimiento_bi`
BEFORE INSERT ON `ueno_movimiento_bancario`
FOR EACH ROW
BEGIN
  IF NEW.importe_credito < 0 OR NEW.importe_debito < 0 OR NEW.monto_disponible < 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: montos negativos no permitidos';
  END IF;

  IF NEW.tipo_movimiento = 'credito' THEN
    IF NEW.importe_credito <= 0 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: movimiento credito sin importe credito';
    END IF;
    IF NEW.monto_disponible > NEW.importe_credito THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: saldo disponible supera el credito';
    END IF;
  ELSE
    IF NEW.monto_disponible <> 0 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: solo los creditos habilitan saldo disponible';
    END IF;
  END IF;

  IF NEW.estado NOT IN ('disponible','registrado','asignado_parcial','asignado_total','conciliado','observado','duplicado','ignorado') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: estado de movimiento invalido';
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_ueno_movimiento_bu`$$
CREATE TRIGGER `trg_ueno_movimiento_bu`
BEFORE UPDATE ON `ueno_movimiento_bancario`
FOR EACH ROW
BEGIN
  IF NEW.importe_credito < 0 OR NEW.importe_debito < 0 OR NEW.monto_disponible < 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: montos negativos no permitidos';
  END IF;

  IF NEW.tipo_movimiento = 'credito' THEN
    IF NEW.importe_credito <= 0 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: movimiento credito sin importe credito';
    END IF;
    IF NEW.monto_disponible > NEW.importe_credito THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: saldo disponible supera el credito';
    END IF;
  ELSE
    IF NEW.monto_disponible <> 0 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: solo los creditos habilitan saldo disponible';
    END IF;
  END IF;

  IF NEW.estado NOT IN ('disponible','registrado','asignado_parcial','asignado_total','conciliado','observado','duplicado','ignorado') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: estado de movimiento invalido';
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_ueno_pago_conciliacion_bi`$$
CREATE TRIGGER `trg_ueno_pago_conciliacion_bi`
BEFORE INSERT ON `pago_transferencia_conciliacion`
FOR EACH ROW
BEGIN
  IF TRIM(IFNULL(NEW.nro_comprobante_informado,'')) = '' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: comprobante obligatorio para transferencia';
  END IF;

  IF NEW.monto_pago <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: monto de pago invalido';
  END IF;

  IF NEW.estado_conciliacion NOT IN ('pendiente_conciliacion','conciliado_ueno','observado','rechazado','anulado') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: estado de conciliacion invalido';
  END IF;

  IF NEW.activo NOT IN ('SI','NO') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: activo invalido';
  END IF;

  IF NEW.activo = 'SI'
    AND NEW.estado_conciliacion NOT IN ('anulado','rechazado')
    AND EXISTS (
      SELECT 1
      FROM pago_transferencia_conciliacion pc
      WHERE pc.activo = 'SI'
        AND pc.estado_conciliacion NOT IN ('anulado','rechazado')
        AND pc.nro_comprobante_informado = NEW.nro_comprobante_informado
        AND (IFNULL(NEW.grupo_pago,'') = '' OR IFNULL(pc.grupo_pago,'') <> IFNULL(NEW.grupo_pago,''))
      LIMIT 1
    ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: comprobante duplicado activo';
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_ueno_pago_conciliacion_bu`$$
CREATE TRIGGER `trg_ueno_pago_conciliacion_bu`
BEFORE UPDATE ON `pago_transferencia_conciliacion`
FOR EACH ROW
BEGIN
  IF TRIM(IFNULL(NEW.nro_comprobante_informado,'')) = '' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: comprobante obligatorio para transferencia';
  END IF;

  IF NEW.monto_pago <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: monto de pago invalido';
  END IF;

  IF NEW.estado_conciliacion NOT IN ('pendiente_conciliacion','conciliado_ueno','observado','rechazado','anulado') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: estado de conciliacion invalido';
  END IF;

  IF NEW.activo NOT IN ('SI','NO') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: activo invalido';
  END IF;

  IF NEW.activo = 'SI'
    AND NEW.estado_conciliacion NOT IN ('anulado','rechazado')
    AND EXISTS (
      SELECT 1
      FROM pago_transferencia_conciliacion pc
      WHERE pc.id <> NEW.id
        AND pc.activo = 'SI'
        AND pc.estado_conciliacion NOT IN ('anulado','rechazado')
        AND pc.nro_comprobante_informado = NEW.nro_comprobante_informado
        AND (IFNULL(NEW.grupo_pago,'') = '' OR IFNULL(pc.grupo_pago,'') <> IFNULL(NEW.grupo_pago,''))
      LIMIT 1
    ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: comprobante duplicado activo';
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_ueno_mov_pago_bi`$$
CREATE TRIGGER `trg_ueno_mov_pago_bi`
BEFORE INSERT ON `ueno_movimiento_pago`
FOR EACH ROW
BEGIN
  DECLARE v_credito int DEFAULT 0;
  DECLARE v_tipo varchar(45) DEFAULT '';
  DECLARE v_aplicado int DEFAULT 0;

  IF NEW.monto_aplicado <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: monto aplicado invalido';
  END IF;

  SELECT importe_credito, tipo_movimiento INTO v_credito, v_tipo
  FROM ueno_movimiento_bancario
  WHERE id_movimiento = NEW.id_movimiento
  LIMIT 1;

  IF v_tipo <> 'credito' OR v_credito <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: solo creditos pueden aplicarse a pagos';
  END IF;

  IF NEW.estado = 'activo' THEN
    SELECT IFNULL(SUM(monto_aplicado),0) INTO v_aplicado
    FROM ueno_movimiento_pago
    WHERE id_movimiento = NEW.id_movimiento
      AND estado = 'activo';

    IF v_aplicado + NEW.monto_aplicado > v_credito THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: sobreaplicacion de saldo no permitida';
    END IF;
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_ueno_mov_pago_bu`$$
CREATE TRIGGER `trg_ueno_mov_pago_bu`
BEFORE UPDATE ON `ueno_movimiento_pago`
FOR EACH ROW
BEGIN
  DECLARE v_credito int DEFAULT 0;
  DECLARE v_tipo varchar(45) DEFAULT '';
  DECLARE v_aplicado int DEFAULT 0;

  IF NEW.monto_aplicado <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: monto aplicado invalido';
  END IF;

  SELECT importe_credito, tipo_movimiento INTO v_credito, v_tipo
  FROM ueno_movimiento_bancario
  WHERE id_movimiento = NEW.id_movimiento
  LIMIT 1;

  IF v_tipo <> 'credito' OR v_credito <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: solo creditos pueden aplicarse a pagos';
  END IF;

  IF NEW.estado = 'activo' THEN
    SELECT IFNULL(SUM(monto_aplicado),0) INTO v_aplicado
    FROM ueno_movimiento_pago
    WHERE id_movimiento = NEW.id_movimiento
      AND estado = 'activo'
      AND id <> NEW.id;

    IF v_aplicado + NEW.monto_aplicado > v_credito THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno: sobreaplicacion de saldo no permitida';
    END IF;
  END IF;
END$$

DELIMITER ;
