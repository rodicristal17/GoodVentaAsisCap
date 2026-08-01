-- Compatibilidad posterior a la importacion de syscvxco_ac (7).sql en MySQL 5.6.
-- Conserva la logica de la columna generada y fusiona triggers que MariaDB
-- permite mantener separados, pero MySQL 5.6 exige unificar por evento/tabla.

SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE ueno_movimiento_pago
  ADD CONSTRAINT fk_ueno_mov_pago_mov FOREIGN KEY (id_movimiento) REFERENCES ueno_movimiento_bancario (id_movimiento),
  ADD CONSTRAINT fk_ueno_mov_pago_pago FOREIGN KEY (cod_pagoFK) REFERENCES pago (idPago);

SET FOREIGN_KEY_CHECKS=1;

DELIMITER $$

DROP TRIGGER IF EXISTS trg_ueno_movimiento_bancario_528ad270_inac$$
DROP TRIGGER IF EXISTS trg_ueno_movimiento_bu$$
CREATE TRIGGER trg_ueno_movimiento_bu
BEFORE UPDATE ON ueno_movimiento_bancario
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

  IF LOWER(IFNULL(OLD.estado, '')) <> 'inactivo' AND LOWER(IFNULL(NEW.estado, '')) = 'inactivo' THEN
    INSERT INTO log_registros_inactivos
      (tabla_nombre, registro_pk_columna, registro_pk_valor, registro_resumen,
       estado_anterior, estado_nuevo, cod_usuario_accion, nombre_usuario_accion,
       fecha_accion, usuario_bd, datos_json)
    VALUES
      ('ueno_movimiento_bancario', 'id_movimiento', NEW.id_movimiento, NEW.descripcion,
       OLD.estado, NEW.estado, NULL, NULL, NOW(), CURRENT_USER(),
       CONCAT('{"tabla":"ueno_movimiento_bancario","pk":"', NEW.id_movimiento, '"}'));
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_ueno_mov_gasto_bu$$
DROP TRIGGER IF EXISTS trg_ueno_movimiento_gasto_42c1e67b_inac$$
CREATE TRIGGER trg_ueno_mov_gasto_bu
BEFORE UPDATE ON ueno_movimiento_gasto
FOR EACH ROW
BEGIN
  IF NEW.monto_aplicado <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno egresos: monto aplicado invalido';
  END IF;

  IF NEW.estado NOT IN ('activo','revertido') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ueno egresos: estado invalido';
  END IF;

  IF LOWER(IFNULL(OLD.estado, '')) <> 'inactivo' AND LOWER(IFNULL(NEW.estado, '')) = 'inactivo' THEN
    INSERT INTO log_registros_inactivos
      (tabla_nombre, registro_pk_columna, registro_pk_valor, registro_resumen,
       estado_anterior, estado_nuevo, cod_usuario_accion, nombre_usuario_accion,
       fecha_accion, usuario_bd, datos_json)
    VALUES
      ('ueno_movimiento_gasto', 'id', NEW.id, NEW.observacion,
       OLD.estado, NEW.estado, NULL, NULL, NOW(), CURRENT_USER(),
       CONCAT('{"tabla":"ueno_movimiento_gasto","pk":"', NEW.id, '"}'));
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_ueno_mov_pago_bu$$
DROP TRIGGER IF EXISTS trg_ueno_movimiento_pago_4479224a_inac$$
CREATE TRIGGER trg_ueno_mov_pago_bu
BEFORE UPDATE ON ueno_movimiento_pago
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

  IF LOWER(IFNULL(OLD.estado, '')) <> 'inactivo' AND LOWER(IFNULL(NEW.estado, '')) = 'inactivo' THEN
    INSERT INTO log_registros_inactivos
      (tabla_nombre, registro_pk_columna, registro_pk_valor, registro_resumen,
       estado_anterior, estado_nuevo, cod_usuario_accion, nombre_usuario_accion,
       fecha_accion, usuario_bd, datos_json)
    VALUES
      ('ueno_movimiento_pago', 'id', NEW.id, NEW.observacion,
       OLD.estado, NEW.estado, NULL, NULL, NOW(), CURRENT_USER(),
       CONCAT('{"tabla":"ueno_movimiento_pago","pk":"', NEW.id, '"}'));
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_centro_legajo_pagare_solicitud_bi$$
CREATE TRIGGER trg_centro_legajo_pagare_solicitud_bi
BEFORE INSERT ON centro_legajo_pagare_solicitud
FOR EACH ROW
BEGIN
  SET NEW.solicitud_abierta = IF(
    NEW.estado IN ('solicitada','aprobada','esperando_recepcion','preparada'),
    1,
    NULL
  );
END$$

DROP TRIGGER IF EXISTS trg_centro_legajo_pagare_solicitud_bu$$
CREATE TRIGGER trg_centro_legajo_pagare_solicitud_bu
BEFORE UPDATE ON centro_legajo_pagare_solicitud
FOR EACH ROW
BEGIN
  SET NEW.solicitud_abierta = IF(
    NEW.estado IN ('solicitada','aprobada','esperando_recepcion','preparada'),
    1,
    NULL
  );
END$$

DELIMITER ;

UPDATE centro_legajo_pagare_solicitud
SET solicitud_abierta = IF(
  estado IN ('solicitada','aprobada','esperando_recepcion','preparada'),
  1,
  NULL
);
