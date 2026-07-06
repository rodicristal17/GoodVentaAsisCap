-- Conciliacion de egresos Ueno contra Resumen de flujo financiero
-- Ejecutar con respaldo previo, despues de las etapas de conciliacion Ueno existentes.
-- No modifica importes originales del extracto ni la conciliacion de ingresos/cobros.

CREATE TABLE IF NOT EXISTS `ueno_movimiento_gasto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_movimiento` int(11) NOT NULL,
  `idgastos` int(11) NOT NULL,
  `monto_aplicado` int(11) NOT NULL DEFAULT 0,
  `usuario_asocio` int(11) NOT NULL,
  `fecha_hora_asociacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(45) NOT NULL DEFAULT 'activo',
  `observacion` varchar(255) DEFAULT NULL,
  `usuario_reversion` int(11) DEFAULT NULL,
  `fecha_hora_reversion` datetime DEFAULT NULL,
  `motivo_reversion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ueno_mov_gasto_movimiento` (`id_movimiento`, `estado`),
  KEY `idx_ueno_mov_gasto_gasto` (`idgastos`, `estado`),
  KEY `idx_ueno_mov_gasto_fecha` (`fecha_hora_asociacion`),
  CONSTRAINT `fk_ueno_mov_gasto_mov` FOREIGN KEY (`id_movimiento`) REFERENCES `ueno_movimiento_bancario` (`id_movimiento`),
  CONSTRAINT `fk_ueno_mov_gasto_gasto` FOREIGN KEY (`idgastos`) REFERENCES `gastos` (`idgastos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'VERCONCILIACIONEGRESOUENO', 'VER CONCILIACION EGRESOS UENO', 'NO', NULL, 'Administrativo'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='VERCONCILIACIONEGRESOUENO' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'CONCILIAREGRESOUENO', 'CONCILIAR EGRESOS UENO', 'NO', NULL, 'Administrativo'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='CONCILIAREGRESOUENO' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'REVERTIRCONCILIACIONEGRESOUENO', 'REVERTIR CONCILIACION EGRESOS UENO', 'NO', NULL, 'Administrativo'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='REVERTIRCONCILIACIONEGRESOUENO' AND `tipo`='Administrativo');

INSERT INTO `detallesniveles` (`cod_nivelesfk`, `idlistadodeacceso`, `accion`)
SELECT niv.cod_niveles, nuevo.idlistadodeacceso, COALESCE(base_nivel.accion, 'NO')
FROM `listado_niveles` niv
CROSS JOIN (
  SELECT 'VERCONCILIACIONEGRESOUENO' AS codigo_nuevo, 'VERCONCILIACIONUENO' AS codigo_base
  UNION ALL SELECT 'CONCILIAREGRESOUENO', 'ASIGNARMANUALUENO'
  UNION ALL SELECT 'REVERTIRCONCILIACIONEGRESOUENO', 'ASIGNARMANUALUENO'
) mapa
INNER JOIN `listadodeacceso` nuevo ON nuevo.codigo=mapa.codigo_nuevo AND nuevo.tipo='Administrativo'
LEFT JOIN `listadodeacceso` base ON base.codigo=mapa.codigo_base AND base.tipo='Administrativo'
LEFT JOIN `detallesniveles` base_nivel ON base_nivel.cod_nivelesfk=niv.cod_niveles AND base_nivel.idlistadodeacceso=base.idlistadodeacceso
LEFT JOIN `detallesniveles` existente ON existente.cod_nivelesfk=niv.cod_niveles AND existente.idlistadodeacceso=nuevo.idlistadodeacceso
WHERE niv.tipo='Administrativo'
  AND existente.iddetallesniveles IS NULL;

INSERT INTO `accesosuser` (`idlistadodeaccesoFK`, `tipo`, `usuarios_idusario`, `accion`)
SELECT nuevo.idlistadodeacceso, 'Administrativo', us.cod_usuario, COALESCE(base_user.accion, base_nivel.accion, 'NO')
FROM `usuario` us
CROSS JOIN (
  SELECT 'VERCONCILIACIONEGRESOUENO' AS codigo_nuevo, 'VERCONCILIACIONUENO' AS codigo_base
  UNION ALL SELECT 'CONCILIAREGRESOUENO', 'ASIGNARMANUALUENO'
  UNION ALL SELECT 'REVERTIRCONCILIACIONEGRESOUENO', 'ASIGNARMANUALUENO'
) mapa
INNER JOIN `listadodeacceso` nuevo ON nuevo.codigo=mapa.codigo_nuevo AND nuevo.tipo='Administrativo'
LEFT JOIN `listadodeacceso` base ON base.codigo=mapa.codigo_base AND base.tipo='Administrativo'
LEFT JOIN `accesosuser` base_user ON base_user.idlistadodeaccesoFK=base.idlistadodeacceso AND base_user.tipo='Administrativo' AND base_user.usuarios_idusario=us.cod_usuario
LEFT JOIN `detallesniveles` base_nivel ON base_nivel.cod_nivelesfk=us.Acceso AND base_nivel.idlistadodeacceso=base.idlistadodeacceso
LEFT JOIN `accesosuser` existente ON existente.idlistadodeaccesoFK=nuevo.idlistadodeacceso AND existente.tipo='Administrativo' AND existente.usuarios_idusario=us.cod_usuario
WHERE us.estado='Activo'
  AND existente.idaccesosUser IS NULL;

DELIMITER $$

DROP TRIGGER IF EXISTS `trg_ueno_mov_gasto_bi`$$
CREATE TRIGGER `trg_ueno_mov_gasto_bi`
BEFORE INSERT ON `ueno_movimiento_gasto`
FOR EACH ROW
BEGIN
  DECLARE v_debito int DEFAULT 0;
  DECLARE v_tipo varchar(45) DEFAULT '';
  DECLARE v_aplicado_mov int DEFAULT 0;
  DECLARE v_monto_gasto int DEFAULT 0;
  DECLARE v_tipo_gasto varchar(45) DEFAULT '';
  DECLARE v_aplicado_gasto int DEFAULT 0;

  IF NEW.monto_aplicado <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno egresos: monto aplicado invalido';
  END IF;

  IF NEW.estado NOT IN ('activo','revertido') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno egresos: estado invalido';
  END IF;

  SELECT importe_debito, tipo_movimiento INTO v_debito, v_tipo
  FROM ueno_movimiento_bancario
  WHERE id_movimiento = NEW.id_movimiento
  LIMIT 1;

  IF v_tipo <> 'debito' OR v_debito <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno egresos: solo debitos pueden conciliar gastos';
  END IF;

  SELECT monto, tipo INTO v_monto_gasto, v_tipo_gasto
  FROM gastos
  WHERE idgastos = NEW.idgastos
  LIMIT 1;

  IF UPPER(v_tipo_gasto) <> 'EGRESO' OR v_monto_gasto <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno egresos: el gasto seleccionado no es egreso valido';
  END IF;

  IF NEW.estado = 'activo' THEN
    SELECT IFNULL(SUM(monto_aplicado),0) INTO v_aplicado_mov
    FROM ueno_movimiento_gasto
    WHERE id_movimiento = NEW.id_movimiento
      AND estado = 'activo';

    IF v_aplicado_mov + NEW.monto_aplicado > v_debito THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno egresos: sobreaplicacion del debito no permitida';
    END IF;

    SELECT IFNULL(SUM(monto_aplicado),0) INTO v_aplicado_gasto
    FROM ueno_movimiento_gasto
    WHERE idgastos = NEW.idgastos
      AND estado = 'activo';

    IF v_aplicado_gasto + NEW.monto_aplicado > v_monto_gasto THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno egresos: sobreaplicacion del gasto no permitida';
    END IF;
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_ueno_mov_gasto_bu`$$
CREATE TRIGGER `trg_ueno_mov_gasto_bu`
BEFORE UPDATE ON `ueno_movimiento_gasto`
FOR EACH ROW
BEGIN
  IF NEW.monto_aplicado <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno egresos: monto aplicado invalido';
  END IF;

  IF NEW.estado NOT IN ('activo','revertido') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno egresos: estado invalido';
  END IF;
END$$

DELIMITER ;
