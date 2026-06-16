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

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `cobrar_cuota_auditoria` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `accion` varchar(80) NOT NULL,
  `cod_creditoFK` int(11) DEFAULT NULL,
  `cod_venta` int(11) DEFAULT NULL,
  `cod_cliente` int(11) DEFAULT NULL,
  `cliente` varchar(180) DEFAULT NULL,
  `forma_pago` varchar(80) DEFAULT NULL,
  `monto` int(11) NOT NULL DEFAULT 0,
  `comprobante` varchar(120) DEFAULT NULL,
  `id_movimiento_ueno` int(11) DEFAULT NULL,
  `estado_pago` varchar(45) DEFAULT NULL,
  `estado_conciliacion` varchar(45) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL,
  `cod_local` int(11) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `datos` text DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_cobrar_cuota_audit_fecha` (`fecha_hora`),
  KEY `idx_cobrar_cuota_audit_credito` (`cod_creditoFK`),
  KEY `idx_cobrar_cuota_audit_venta` (`cod_venta`),
  KEY `idx_cobrar_cuota_audit_ueno` (`id_movimiento_ueno`),
  KEY `idx_cobrar_cuota_audit_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


SET @cobrar_cuota_catalog_existe := (
  SELECT COUNT(*)
  FROM dashboard_access_catalog
  WHERE access_key = 'cobrar_cuota'
);

UPDATE dashboard_access_catalog
SET default_quick_order = default_quick_order + 1
WHERE @cobrar_cuota_catalog_existe = 0
  AND is_default_quick_access = 1
  AND default_quick_order >= 3;

INSERT INTO dashboard_access_catalog
  (access_key, label, module_key, module_label, icon_key, route_path, permission_key, is_active, is_default_quick_access, default_quick_order)
VALUES
  ('cobrar_cuota', 'Cobrar cuota', 'administrativo', 'Administrativo', 'cash-register', NULL, 'VERCOBRARCUOTA', 1, 1, 3)
ON DUPLICATE KEY UPDATE
  label = VALUES(label),
  module_key = VALUES(module_key),
  module_label = VALUES(module_label),
  icon_key = VALUES(icon_key),
  route_path = VALUES(route_path),
  permission_key = VALUES(permission_key),
  is_active = VALUES(is_active),
  is_default_quick_access = VALUES(is_default_quick_access),
  default_quick_order = VALUES(default_quick_order);

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `caja_cierres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_arqueocaja` int(11) NOT NULL,
  `id_lote` varchar(80) NOT NULL,
  `id_usuario_cajera` int(11) DEFAULT NULL,
  `id_local` int(11) DEFAULT NULL,
  `fecha_inicio_lote` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `efectivo_esperado` int(11) NOT NULL DEFAULT 0,
  `efectivo_contado` int(11) NOT NULL DEFAULT 0,
  `diferencia_efectivo` int(11) NOT NULL DEFAULT 0,
  `total_transferencias` int(11) NOT NULL DEFAULT 0,
  `total_transferencias_conciliadas` int(11) NOT NULL DEFAULT 0,
  `total_tarjetas` int(11) NOT NULL DEFAULT 0,
  `total_billeteras` int(11) NOT NULL DEFAULT 0,
  `total_otros` int(11) NOT NULL DEFAULT 0,
  `estado_cierre` varchar(60) NOT NULL DEFAULT 'Caja cuadrada',
  `estado_revision` varchar(60) NOT NULL DEFAULT 'Cerrada',
  `motivo_diferencia` varchar(120) DEFAULT NULL,
  `observacion_diferencia` text DEFAULT NULL,
  `foto_adjunta` varchar(2) NOT NULL DEFAULT 'NO',
  `firma_adjunta` varchar(2) NOT NULL DEFAULT 'NO',
  `ruta_foto` varchar(255) DEFAULT NULL,
  `ruta_firma` varchar(255) DEFAULT NULL,
  `cerrado_por` int(11) DEFAULT NULL,
  `cerrado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_caja_cierres_arqueo` (`id_arqueocaja`),
  KEY `idx_caja_cierres_lote` (`id_lote`),
  KEY `idx_caja_cierres_usuario` (`id_usuario_cajera`),
  KEY `idx_caja_cierres_local` (`id_local`),
  KEY `idx_caja_cierres_estado` (`estado_cierre`,`estado_revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `caja_cierre_denominaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cierre` int(11) NOT NULL,
  `denominacion` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `subtotal` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_caja_cierre_denominacion` (`id_cierre`,`denominacion`),
  KEY `idx_caja_cierre_denominacion_cierre` (`id_cierre`),
  CONSTRAINT `fk_caja_cierre_denominaciones_cierre` FOREIGN KEY (`id_cierre`) REFERENCES `caja_cierres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `caja_cierre_evidencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cierre` int(11) NOT NULL,
  `tipo_evidencia` varchar(60) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `nombre_archivo` varchar(190) DEFAULT NULL,
  `mime_type` varchar(80) DEFAULT NULL,
  `size` int(11) NOT NULL DEFAULT 0,
  `usuario_carga` int(11) DEFAULT NULL,
  `fecha_carga` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_caja_cierre_evidencia_cierre` (`id_cierre`),
  KEY `idx_caja_cierre_evidencia_tipo` (`tipo_evidencia`),
  CONSTRAINT `fk_caja_cierre_evidencias_cierre` FOREIGN KEY (`id_cierre`) REFERENCES `caja_cierres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `caja_cierre_firmas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cierre` int(11) NOT NULL,
  `ruta_firma` varchar(255) NOT NULL,
  `usuario_firmante` int(11) DEFAULT NULL,
  `nombre_firmante` varchar(180) DEFAULT NULL,
  `fecha_firma` datetime NOT NULL DEFAULT current_timestamp(),
  `texto_confirmacion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_caja_cierre_firma_cierre` (`id_cierre`),
  KEY `idx_caja_cierre_firma_usuario` (`usuario_firmante`),
  CONSTRAINT `fk_caja_cierre_firmas_cierre` FOREIGN KEY (`id_cierre`) REFERENCES `caja_cierres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `caja_cierre_auditoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cierre` int(11) DEFAULT NULL,
  `id_lote` varchar(80) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL,
  `accion` varchar(80) NOT NULL,
  `detalle` text DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_caja_cierre_audit_cierre` (`id_cierre`),
  KEY `idx_caja_cierre_audit_lote` (`id_lote`),
  KEY `idx_caja_cierre_audit_usuario` (`usuario`),
  KEY `idx_caja_cierre_audit_accion` (`accion`),
  KEY `idx_caja_cierre_audit_fecha` (`fecha_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Permisos
-- VERCONCILIACIONUENO, IMPORTAREXTRACTOUENO, VEREXTRACTOSUENO, VERPAGOSPENDIENTESUENO
-- CONCILIARPAGOSUENO, OBSERVARPAGOUENO, REVISARPAGOUENO
-- VERCIERRESTESORERIA, VERREPORTESFINANZAS
-- ASIGNARMANUALUENO, VERASIGNACIONMANUALUENO
-- VERAUDITORIAUENO, REGLASFUERTESUENO
-- VERCOBRARCUOTA, REGISTRARCOBRARCUOTA, CONCILIARCOBRARCUOTA, IMPRIMIRRECIBOCOBRARCUOTA, ANULARCOBRARCUOTA