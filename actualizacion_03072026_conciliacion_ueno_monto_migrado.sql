-- Conciliacion interna Ueno contra montos migrados de caja
-- Ejecutar con respaldo previo, despues de las etapas de conciliacion Ueno existentes.
-- No modifica pagos historicos, cuotas ni importes originales del extracto.

CREATE TABLE IF NOT EXISTS `ueno_movimiento_migracion_caja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_movimiento` int(11) NOT NULL,
  `idmigrar_caja` int(11) NOT NULL,
  `monto_aplicado` int(11) NOT NULL DEFAULT 0,
  `usuario_asocio` int(11) NOT NULL,
  `fecha_hora_asociacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(45) NOT NULL DEFAULT 'activo',
  `observacion` varchar(255) DEFAULT NULL,
  `advertencia` varchar(255) DEFAULT NULL,
  `usuario_reversion` int(11) DEFAULT NULL,
  `fecha_hora_reversion` datetime DEFAULT NULL,
  `motivo_reversion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ueno_mov_migracion_estado` (`id_movimiento`,`idmigrar_caja`,`estado`),
  KEY `idx_ueno_mov_migracion_movimiento` (`id_movimiento`,`estado`),
  KEY `idx_ueno_mov_migracion_caja` (`idmigrar_caja`,`estado`),
  KEY `idx_ueno_mov_migracion_fecha` (`fecha_hora_asociacion`),
  CONSTRAINT `fk_ueno_mov_migracion_mov` FOREIGN KEY (`id_movimiento`) REFERENCES `ueno_movimiento_bancario` (`id_movimiento`),
  CONSTRAINT `fk_ueno_mov_migracion_caja` FOREIGN KEY (`idmigrar_caja`) REFERENCES `migrar_caja` (`idmigrar_caja`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'VERMIGRACIONUENO', 'VER MIGRACION UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='VERMIGRACIONUENO' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 87, 'FORMULARIO CONCILIACION UENO', 'CONCILIARMIGRACIONUENO', 'CONCILIAR MIGRACION UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='CONCILIARMIGRACIONUENO' AND `tipo`='Administrativo');

INSERT INTO `detallesniveles` (`cod_nivelesfk`, `idlistadodeacceso`, `accion`)
SELECT niv.cod_niveles, nuevo.idlistadodeacceso, COALESCE(base_nivel.accion, 'NO')
FROM `listado_niveles` niv
CROSS JOIN (
  SELECT 'VERMIGRACIONUENO' AS codigo_nuevo, 'VERCONCILIACIONUENO' AS codigo_base
  UNION ALL SELECT 'CONCILIARMIGRACIONUENO', 'ASIGNARMANUALUENO'
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
  SELECT 'VERMIGRACIONUENO' AS codigo_nuevo, 'VERCONCILIACIONUENO' AS codigo_base
  UNION ALL SELECT 'CONCILIARMIGRACIONUENO', 'ASIGNARMANUALUENO'
) mapa
INNER JOIN `listadodeacceso` nuevo ON nuevo.codigo=mapa.codigo_nuevo AND nuevo.tipo='Administrativo'
LEFT JOIN `listadodeacceso` base ON base.codigo=mapa.codigo_base AND base.tipo='Administrativo'
LEFT JOIN `accesosuser` base_user ON base_user.idlistadodeaccesoFK=base.idlistadodeacceso AND base_user.tipo='Administrativo' AND base_user.usuarios_idusario=us.cod_usuario
LEFT JOIN `detallesniveles` base_nivel ON base_nivel.cod_nivelesfk=us.Acceso AND base_nivel.idlistadodeacceso=base.idlistadodeacceso
LEFT JOIN `accesosuser` existente ON existente.idlistadodeaccesoFK=nuevo.idlistadodeacceso AND existente.tipo='Administrativo' AND existente.usuarios_idusario=us.cod_usuario
WHERE us.estado='Activo'
  AND existente.idaccesosUser IS NULL;

DELIMITER $$

DROP TRIGGER IF EXISTS `trg_ueno_mov_migracion_bi`$$
CREATE TRIGGER `trg_ueno_mov_migracion_bi`
BEFORE INSERT ON `ueno_movimiento_migracion_caja`
FOR EACH ROW
BEGIN
  DECLARE v_credito int DEFAULT 0;
  DECLARE v_tipo varchar(45) DEFAULT '';
  DECLARE v_migracion int DEFAULT 0;
  DECLARE v_aplicado_pago int DEFAULT 0;
  DECLARE v_aplicado_migracion int DEFAULT 0;

  IF NEW.monto_aplicado <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno migracion: monto aplicado invalido';
  END IF;

  IF NEW.estado NOT IN ('activo','revertido') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno migracion: estado invalido';
  END IF;

  SELECT importe_credito, tipo_movimiento INTO v_credito, v_tipo
  FROM ueno_movimiento_bancario
  WHERE id_movimiento = NEW.id_movimiento
  LIMIT 1;

  IF v_tipo <> 'credito' OR v_credito <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno migracion: solo creditos pueden conciliar montos migrados';
  END IF;

  SELECT monto INTO v_migracion
  FROM migrar_caja
  WHERE idmigrar_caja = NEW.idmigrar_caja
  LIMIT 1;

  IF v_migracion <= 0 OR NEW.monto_aplicado <> v_migracion THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno migracion: el monto debe coincidir exactamente con la migracion';
  END IF;

  IF NEW.estado = 'activo' THEN
    SELECT IFNULL(SUM(monto_aplicado),0) INTO v_aplicado_pago
    FROM ueno_movimiento_pago
    WHERE id_movimiento = NEW.id_movimiento
      AND estado = 'activo';

    SELECT IFNULL(SUM(monto_aplicado),0) INTO v_aplicado_migracion
    FROM ueno_movimiento_migracion_caja
    WHERE id_movimiento = NEW.id_movimiento
      AND estado = 'activo';

    IF v_aplicado_pago + v_aplicado_migracion + NEW.monto_aplicado > v_credito THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno migracion: sobreaplicacion del credito no permitida';
    END IF;
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_ueno_mov_migracion_bu`$$
CREATE TRIGGER `trg_ueno_mov_migracion_bu`
BEFORE UPDATE ON `ueno_movimiento_migracion_caja`
FOR EACH ROW
BEGIN
  IF NEW.monto_aplicado <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno migracion: monto aplicado invalido';
  END IF;

  IF NEW.estado NOT IN ('activo','revertido') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno migracion: estado invalido';
  END IF;
END$$

DELIMITER ;
