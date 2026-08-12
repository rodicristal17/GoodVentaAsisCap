-- Clinident Salud / Sistema Telar
-- Historial inmutable de pagos realizados fuera de secuencia.
-- No modifica pago, credito, venta, cliente ni registros historicos existentes.
-- Compatible con MySQL 5.6 y ejecutable mas de una vez.

CREATE TABLE IF NOT EXISTS historial_pago_salteado (
  id_historial BIGINT NOT NULL AUTO_INCREMENT,
  fecha_deteccion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_deteccion INT NOT NULL,
  cod_cliente INT NOT NULL,
  cliente_snapshot VARCHAR(255) DEFAULT NULL,
  documento_snapshot VARCHAR(80) DEFAULT NULL,
  cod_venta INT NOT NULL,
  factura_snapshot VARCHAR(80) DEFAULT NULL,
  cod_local INT NOT NULL,
  local_snapshot VARCHAR(180) DEFAULT NULL,
  cod_credito_pendiente INT NOT NULL,
  nro_cuota_pendiente INT NOT NULL,
  plazo_pendiente VARCHAR(45) NOT NULL,
  vencimiento_pendiente DATE DEFAULT NULL,
  capital_pendiente INT NOT NULL DEFAULT 0,
  capital_pagado_pendiente INT NOT NULL DEFAULT 0,
  interes_pendiente INT NOT NULL DEFAULT 0,
  interes_pagado_pendiente INT NOT NULL DEFAULT 0,
  saldo_pendiente INT NOT NULL DEFAULT 0,
  cod_credito_pagado INT NOT NULL,
  nro_cuota_pagada INT NOT NULL,
  plazo_pagado VARCHAR(45) NOT NULL,
  id_pago INT NOT NULL,
  fecha_pago DATE DEFAULT NULL,
  fecha_hora_pago DATETIME DEFAULT NULL,
  monto_pago INT NOT NULL DEFAULT 0,
  tipo_pago VARCHAR(45) DEFAULT NULL,
  forma_pago VARCHAR(80) DEFAULT NULL,
  comprobante_snapshot VARCHAR(120) DEFAULT NULL,
  ultima_cuota_pagada INT NOT NULL DEFAULT 0,
  filtros_snapshot TEXT,
  huella CHAR(40) NOT NULL,
  PRIMARY KEY (id_historial),
  UNIQUE KEY uq_hist_pago_salteado_huella (huella),
  KEY idx_hps_fecha_id (fecha_deteccion,id_historial),
  KEY idx_hps_cliente_fecha (cod_cliente,fecha_deteccion),
  KEY idx_hps_venta_fecha (cod_venta,fecha_deteccion),
  KEY idx_hps_credito_pendiente (cod_credito_pendiente),
  KEY idx_hps_pago (id_pago),
  KEY idx_hps_local_fecha (cod_local,fecha_deteccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS historial_pago_salteado_cuota (
  id_detalle BIGINT NOT NULL AUTO_INCREMENT,
  id_historialFK BIGINT NOT NULL,
  cod_credito INT NOT NULL,
  nro_cuota INT NOT NULL,
  plazo VARCHAR(45) NOT NULL,
  vencimiento DATE DEFAULT NULL,
  capital_debido INT NOT NULL DEFAULT 0,
  capital_pagado INT NOT NULL DEFAULT 0,
  interes_debido INT NOT NULL DEFAULT 0,
  interes_pagado INT NOT NULL DEFAULT 0,
  saldo INT NOT NULL DEFAULT 0,
  pagada TINYINT(1) NOT NULL DEFAULT 0,
  tiene_pago TINYINT(1) NOT NULL DEFAULT 0,
  salteada TINYINT(1) NOT NULL DEFAULT 0,
  estado_snapshot VARCHAR(45) NOT NULL,
  PRIMARY KEY (id_detalle),
  UNIQUE KEY uq_hpsc_historial_credito (id_historialFK,cod_credito),
  KEY idx_hpsc_historial_cuota (id_historialFK,nro_cuota),
  KEY idx_hpsc_credito (cod_credito),
  CONSTRAINT fk_hpsc_historial FOREIGN KEY (id_historialFK)
    REFERENCES historial_pago_salteado(id_historial) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS historial_pago_salteado_venta (
  id_historial_venta BIGINT NOT NULL AUTO_INCREMENT,
  fecha_deteccion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_deteccion INT NOT NULL,
  cod_cliente INT NOT NULL,
  cliente_snapshot VARCHAR(255) DEFAULT NULL,
  documento_snapshot VARCHAR(80) DEFAULT NULL,
  telefono_snapshot VARCHAR(80) DEFAULT NULL,
  cod_venta INT NOT NULL,
  factura_snapshot VARCHAR(80) DEFAULT NULL,
  fecha_venta DATE DEFAULT NULL,
  cod_local INT NOT NULL,
  local_snapshot VARCHAR(180) DEFAULT NULL,
  cuotas_pagadas TEXT,
  ultima_cuota_pagada INT NOT NULL DEFAULT 0,
  cuotas_pendientes TEXT,
  cuotas_salteadas INT NOT NULL DEFAULT 0,
  cuotas_parciales INT NOT NULL DEFAULT 0,
  saldo_huecos INT NOT NULL DEFAULT 0,
  primer_vencimiento DATE DEFAULT NULL,
  cantidad_entregas INT NOT NULL DEFAULT 0,
  monto_entrega INT NOT NULL DEFAULT 0,
  pagado_entrega INT NOT NULL DEFAULT 0,
  id_historial_detalleFK BIGINT NOT NULL,
  filtros_snapshot TEXT,
  huella CHAR(40) NOT NULL,
  PRIMARY KEY (id_historial_venta),
  UNIQUE KEY uq_hpsv_huella (huella),
  KEY idx_hpsv_fecha_id (fecha_deteccion,id_historial_venta),
  KEY idx_hpsv_cliente_fecha (cod_cliente,fecha_deteccion),
  KEY idx_hpsv_venta_fecha (cod_venta,fecha_deteccion),
  KEY idx_hpsv_local_fecha (cod_local,fecha_deteccion),
  KEY idx_hpsv_detalle (id_historial_detalleFK),
  CONSTRAINT fk_hpsv_detalle FOREIGN KEY (id_historial_detalleFK)
    REFERENCES historial_pago_salteado(id_historial) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS historial_pago_salteado_entrega (
  id_entrega BIGINT NOT NULL AUTO_INCREMENT,
  id_historial_ventaFK BIGINT NOT NULL,
  cod_credito INT NOT NULL,
  plazo VARCHAR(45) NOT NULL DEFAULT 'ENTREGA',
  vencimiento DATE DEFAULT NULL,
  capital_debido INT NOT NULL DEFAULT 0,
  capital_pagado INT NOT NULL DEFAULT 0,
  saldo INT NOT NULL DEFAULT 0,
  pagada TINYINT(1) NOT NULL DEFAULT 0,
  tiene_pago TINYINT(1) NOT NULL DEFAULT 0,
  estado_snapshot VARCHAR(45) NOT NULL,
  PRIMARY KEY (id_entrega),
  UNIQUE KEY uq_hpse_historial_credito (id_historial_ventaFK,cod_credito),
  CONSTRAINT fk_hpse_historial_venta FOREIGN KEY (id_historial_ventaFK)
    REFERENCES historial_pago_salteado_venta(id_historial_venta) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS historial_pago_salteado_intento_seguridad (
  usuario_id INT NOT NULL,
  intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0,
  ultimo_intento DATETIME DEFAULT NULL,
  bloqueo_hasta DATETIME DEFAULT NULL,
  PRIMARY KEY (usuario_id),
  KEY idx_hpsis_bloqueo (bloqueo_hasta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Auditoria inmutable de regularizaciones individuales y sus reversiones.
CREATE TABLE IF NOT EXISTS regularizacion_pago_salteado (
  id_regularizacion BIGINT NOT NULL AUTO_INCREMENT,
  fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_id INT NOT NULL,
  cod_venta INT NOT NULL,
  monto_reasignado INT NOT NULL DEFAULT 0,
  pagos_origen INT NOT NULL DEFAULT 0,
  pagos_creados INT NOT NULL DEFAULT 0,
  huella_previa CHAR(40) NOT NULL,
  PRIMARY KEY (id_regularizacion),
  KEY idx_rps_venta_huella (cod_venta,huella_previa),
  KEY idx_rps_venta_fecha (cod_venta,fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET @rps_uq_existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='regularizacion_pago_salteado'
    AND INDEX_NAME='uq_rps_venta_huella');
SET @hps_sql := IF(@rps_uq_existe>0,
  'ALTER TABLE regularizacion_pago_salteado DROP INDEX uq_rps_venta_huella, ADD INDEX idx_rps_venta_huella (cod_venta,huella_previa)',
  'SELECT ''El indice de ciclos de regularizacion ya esta actualizado''');
PREPARE hps_stmt FROM @hps_sql;
EXECUTE hps_stmt;
DEALLOCATE PREPARE hps_stmt;

CREATE TABLE IF NOT EXISTS regularizacion_pago_salteado_detalle (
  id_detalle BIGINT NOT NULL AUTO_INCREMENT,
  id_regularizacionFK BIGINT NOT NULL,
  id_pago_original INT NOT NULL,
  id_pago_creado INT DEFAULT NULL,
  cod_credito_origen INT NOT NULL,
  cod_credito_destino INT NOT NULL,
  monto_original INT NOT NULL,
  monto_aplicado INT NOT NULL,
  datos_originales TEXT NOT NULL,
  PRIMARY KEY (id_detalle),
  KEY idx_rpsd_regularizacion (id_regularizacionFK),
  KEY idx_rpsd_pago_original (id_pago_original),
  CONSTRAINT fk_rpsd_regularizacion FOREIGN KEY (id_regularizacionFK)
    REFERENCES regularizacion_pago_salteado(id_regularizacion) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS regularizacion_pago_salteado_reversion (
  id_reversion BIGINT NOT NULL AUTO_INCREMENT,
  id_regularizacionFK BIGINT NOT NULL,
  fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_id INT NOT NULL,
  motivo VARCHAR(255) NOT NULL DEFAULT 'Reversion manual',
  PRIMARY KEY (id_reversion),
  UNIQUE KEY uq_rpsr_regularizacion (id_regularizacionFK),
  CONSTRAINT fk_rpsr_regularizacion FOREIGN KEY (id_regularizacionFK)
    REFERENCES regularizacion_pago_salteado(id_regularizacion) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Clasificacion historica de una sola ejecucion sobre la venta.
-- No se recalcula con pagos posteriores ni al volver a ejecutar esta migracion.
SET @hps_columna_venta_nueva := (
  SELECT IF(COUNT(*)=0,1,0)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='venta'
    AND COLUMN_NAME='estado_salteo_cuotas'
);

SET @hps_sql := IF(
  @hps_columna_venta_nueva=1,
  'ALTER TABLE venta ADD COLUMN estado_salteo_cuotas VARCHAR(10) NOT NULL DEFAULT ''Normal'' AFTER estadocuenta',
  'SELECT ''La columna venta.estado_salteo_cuotas ya existe'''
);
PREPARE hps_stmt FROM @hps_sql;
EXECUTE hps_stmt;
DEALLOCATE PREPARE hps_stmt;

SET @hps_indice_venta_nuevo := (
  SELECT IF(COUNT(*)=0,1,0)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='venta'
    AND INDEX_NAME='idx_venta_estado_salteo_cuotas'
);

SET @hps_sql := IF(
  @hps_indice_venta_nuevo=1,
  'ALTER TABLE venta ADD INDEX idx_venta_estado_salteo_cuotas (estado_salteo_cuotas,cod_venta)',
  'SELECT ''El indice idx_venta_estado_salteo_cuotas ya existe'''
);
PREPARE hps_stmt FROM @hps_sql;
EXECUTE hps_stmt;
DEALLOCATE PREPARE hps_stmt;

SET @hps_sql := IF(
  @hps_columna_venta_nueva=1,
  'UPDATE venta v SET v.estado_salteo_cuotas=CASE WHEN EXISTS (SELECT 1 FROM historial_pago_salteado_venta h WHERE h.cod_venta=v.cod_venta) THEN ''Salteado'' ELSE ''Normal'' END',
  'SELECT ''Clasificacion historica conservada sin recalcular'''
);
PREPARE hps_stmt FROM @hps_sql;
EXECUTE hps_stmt;
DEALLOCATE PREPARE hps_stmt;

SET @hps_codigo_ver := 'VERHISTORIALPAGOSSALTEADOS';
SET @hps_codigo_registrar := 'REGISTRARHISTORIALPAGOSSALTEADOS';
SET @hps_codigo_regularizar := 'REGULARIZARPAGOSSALTEADOS';
SET @hps_codigo_revertir := 'REVERTIRREGULARIZACIONPAGOSSALTEADOS';

INSERT INTO listadodeacceso (nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT 89,'FORMULARIO HISTORIAL PAGOS SALTEADOS',@hps_codigo_ver,'VER HISTORIAL PAGOS SALTEADOS','NO',31,'Administrativo'
FROM DUAL WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso WHERE codigo=@hps_codigo_ver AND tipo='Administrativo'
);

INSERT INTO listadodeacceso (nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT 89,'FORMULARIO HISTORIAL PAGOS SALTEADOS',@hps_codigo_registrar,'REGISTRAR HISTORIAL PAGOS SALTEADOS','NO',32,'Administrativo'
FROM DUAL WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso WHERE codigo=@hps_codigo_registrar AND tipo='Administrativo'
);

INSERT INTO listadodeacceso (nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT 89,'FORMULARIO CUOTAS SALTEADAS',@hps_codigo_regularizar,'REGULARIZAR PAGOS SALTEADOS','NO',33,'Administrativo'
FROM DUAL WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso WHERE codigo=@hps_codigo_regularizar AND tipo='Administrativo'
);

INSERT INTO listadodeacceso (nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT 89,'FORMULARIO CUOTAS SALTEADAS',@hps_codigo_revertir,'REVERTIR REGULARIZACION PAGOS SALTEADOS','NO',34,'Administrativo'
FROM DUAL WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso WHERE codigo=@hps_codigo_revertir AND tipo='Administrativo'
);

INSERT INTO detallesniveles (accion,idlistadodeacceso,cod_nivelesfk)
SELECT CASE WHEN UPPER(TRIM(IFNULL(niv.nombre,'')))='ADMINISTRATIVO' THEN 'SI' ELSE 'NO' END,
       la.idlistadodeacceso,niv.cod_niveles
FROM listado_niveles niv
INNER JOIN listadodeacceso la
  ON la.codigo IN (@hps_codigo_ver,@hps_codigo_registrar,@hps_codigo_regularizar,@hps_codigo_revertir) AND la.tipo='Administrativo'
WHERE niv.tipo='Administrativo' AND niv.estado='Activo'
  AND NOT EXISTS (
    SELECT 1 FROM detallesniveles dn
    WHERE dn.idlistadodeacceso=la.idlistadodeacceso AND dn.cod_nivelesfk=niv.cod_niveles
  );

INSERT INTO accesosuser (idlistadodeaccesoFK,tipo,usuarios_idusario,accion)
SELECT la.idlistadodeacceso,'Administrativo',us.cod_usuario,
       CASE WHEN UPPER(TRIM(IFNULL(niv.nombre,'')))='ADMINISTRATIVO' THEN 'SI' ELSE 'NO' END
FROM usuario us
LEFT JOIN listado_niveles niv ON niv.cod_niveles=us.Acceso
INNER JOIN listadodeacceso la
  ON la.codigo IN (@hps_codigo_ver,@hps_codigo_registrar,@hps_codigo_regularizar,@hps_codigo_revertir) AND la.tipo='Administrativo'
WHERE us.estado='Activo'
  AND NOT EXISTS (
    SELECT 1 FROM accesosuser au
    WHERE au.idlistadodeaccesoFK=la.idlistadodeacceso
      AND au.tipo='Administrativo' AND au.usuarios_idusario=us.cod_usuario
  );

DELIMITER $$

DROP TRIGGER IF EXISTS trg_historial_pago_salteado_no_update$$
CREATE TRIGGER trg_historial_pago_salteado_no_update
BEFORE UPDATE ON historial_pago_salteado
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT='El historial de pagos salteados es inmutable';
END$$

DROP TRIGGER IF EXISTS trg_historial_pago_salteado_no_delete$$
CREATE TRIGGER trg_historial_pago_salteado_no_delete
BEFORE DELETE ON historial_pago_salteado
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT='El historial de pagos salteados no se elimina';
END$$

DROP TRIGGER IF EXISTS trg_historial_pago_salteado_cuota_no_update$$
CREATE TRIGGER trg_historial_pago_salteado_cuota_no_update
BEFORE UPDATE ON historial_pago_salteado_cuota
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT='Las cuotas del historial de pagos salteados son inmutables';
END$$

DROP TRIGGER IF EXISTS trg_historial_pago_salteado_cuota_no_delete$$
CREATE TRIGGER trg_historial_pago_salteado_cuota_no_delete
BEFORE DELETE ON historial_pago_salteado_cuota
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT='Las cuotas del historial de pagos salteados no se eliminan';
END$$

DROP TRIGGER IF EXISTS trg_historial_pago_salteado_venta_no_update$$
CREATE TRIGGER trg_historial_pago_salteado_venta_no_update
BEFORE UPDATE ON historial_pago_salteado_venta
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT='El historial agrupado de pagos salteados es inmutable';
END$$

DROP TRIGGER IF EXISTS trg_historial_pago_salteado_venta_no_delete$$
CREATE TRIGGER trg_historial_pago_salteado_venta_no_delete
BEFORE DELETE ON historial_pago_salteado_venta
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT='El historial agrupado de pagos salteados no se elimina';
END$$

DROP TRIGGER IF EXISTS trg_historial_pago_salteado_entrega_no_update$$
CREATE TRIGGER trg_historial_pago_salteado_entrega_no_update
BEFORE UPDATE ON historial_pago_salteado_entrega FOR EACH ROW
BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Las entregas del historial son inmutables'; END$$
DROP TRIGGER IF EXISTS trg_historial_pago_salteado_entrega_no_delete$$
CREATE TRIGGER trg_historial_pago_salteado_entrega_no_delete
BEFORE DELETE ON historial_pago_salteado_entrega FOR EACH ROW
BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Las entregas del historial no se eliminan'; END$$

DROP TRIGGER IF EXISTS trg_regularizacion_pago_salteado_no_update$$
CREATE TRIGGER trg_regularizacion_pago_salteado_no_update BEFORE UPDATE ON regularizacion_pago_salteado
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La auditoria de regularizacion es inmutable'; END$$
DROP TRIGGER IF EXISTS trg_regularizacion_pago_salteado_no_delete$$
CREATE TRIGGER trg_regularizacion_pago_salteado_no_delete BEFORE DELETE ON regularizacion_pago_salteado
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La auditoria de regularizacion no se elimina'; END$$
DROP TRIGGER IF EXISTS trg_regularizacion_pago_salteado_detalle_no_update$$
CREATE TRIGGER trg_regularizacion_pago_salteado_detalle_no_update BEFORE UPDATE ON regularizacion_pago_salteado_detalle
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El detalle de regularizacion es inmutable'; END$$
DROP TRIGGER IF EXISTS trg_regularizacion_pago_salteado_detalle_no_delete$$
CREATE TRIGGER trg_regularizacion_pago_salteado_detalle_no_delete BEFORE DELETE ON regularizacion_pago_salteado_detalle
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El detalle de regularizacion no se elimina'; END$$
DROP TRIGGER IF EXISTS trg_regularizacion_pago_salteado_reversion_no_update$$
CREATE TRIGGER trg_regularizacion_pago_salteado_reversion_no_update BEFORE UPDATE ON regularizacion_pago_salteado_reversion
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La reversion es inmutable'; END$$
DROP TRIGGER IF EXISTS trg_regularizacion_pago_salteado_reversion_no_delete$$
CREATE TRIGGER trg_regularizacion_pago_salteado_reversion_no_delete BEFORE DELETE ON regularizacion_pago_salteado_reversion
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La reversion no se elimina'; END$$

DELIMITER ;

SELECT la.codigo,la.idlistadodeacceso
FROM listadodeacceso la
WHERE la.codigo IN (@hps_codigo_ver,@hps_codigo_registrar,@hps_codigo_regularizar,@hps_codigo_revertir)
  AND la.tipo='Administrativo'
ORDER BY la.codigo;

SELECT estado_salteo_cuotas,COUNT(*) AS ventas
FROM venta
GROUP BY estado_salteo_cuotas
ORDER BY estado_salteo_cuotas;

-- Reversion estructural manual, solamente si nunca se cargaron registros:
-- DROP TRIGGER IF EXISTS trg_historial_pago_salteado_no_update;
-- DROP TRIGGER IF EXISTS trg_historial_pago_salteado_no_delete;
-- DROP TRIGGER IF EXISTS trg_historial_pago_salteado_cuota_no_update;
-- DROP TRIGGER IF EXISTS trg_historial_pago_salteado_cuota_no_delete;
-- DROP TRIGGER IF EXISTS trg_historial_pago_salteado_venta_no_update;
-- DROP TRIGGER IF EXISTS trg_historial_pago_salteado_venta_no_delete;
-- DROP TABLE historial_pago_salteado_venta;
-- DROP TABLE historial_pago_salteado_cuota;
-- DROP TABLE historial_pago_salteado;
