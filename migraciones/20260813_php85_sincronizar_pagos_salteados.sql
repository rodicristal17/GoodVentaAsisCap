-- Sincroniza en produccion las tablas ya presentes en desarrollo.
CREATE TABLE IF NOT EXISTS historial_pago_salteado_entrega (
  id_entrega BIGINT(20) NOT NULL AUTO_INCREMENT,
  id_historial_ventaFK BIGINT(20) NOT NULL,
  cod_credito INT(11) NOT NULL,
  plazo VARCHAR(45) NOT NULL DEFAULT 'ENTREGA',
  vencimiento DATE DEFAULT NULL,
  capital_debido INT(11) NOT NULL DEFAULT 0,
  capital_pagado INT(11) NOT NULL DEFAULT 0,
  saldo INT(11) NOT NULL DEFAULT 0,
  pagada TINYINT(1) NOT NULL DEFAULT 0,
  tiene_pago TINYINT(1) NOT NULL DEFAULT 0,
  estado_snapshot VARCHAR(45) NOT NULL,
  PRIMARY KEY (id_entrega),
  UNIQUE KEY uq_hpse_historial_credito (id_historial_ventaFK,cod_credito),
  CONSTRAINT fk_hpse_historial_venta FOREIGN KEY (id_historial_ventaFK)
    REFERENCES historial_pago_salteado_venta (id_historial_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS regularizacion_pago_salteado (
  id_regularizacion BIGINT(20) NOT NULL AUTO_INCREMENT,
  fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_id INT(11) NOT NULL,
  cod_venta INT(11) NOT NULL,
  monto_reasignado INT(11) NOT NULL DEFAULT 0,
  pagos_origen INT(11) NOT NULL DEFAULT 0,
  pagos_creados INT(11) NOT NULL DEFAULT 0,
  huella_previa CHAR(40) NOT NULL,
  PRIMARY KEY (id_regularizacion),
  KEY idx_rps_venta_fecha (cod_venta,fecha_hora),
  KEY idx_rps_venta_huella (cod_venta,huella_previa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS regularizacion_pago_salteado_detalle (
  id_detalle BIGINT(20) NOT NULL AUTO_INCREMENT,
  id_regularizacionFK BIGINT(20) NOT NULL,
  id_pago_original INT(11) NOT NULL,
  id_pago_creado INT(11) DEFAULT NULL,
  cod_credito_origen INT(11) NOT NULL,
  cod_credito_destino INT(11) NOT NULL,
  monto_original INT(11) NOT NULL,
  monto_aplicado INT(11) NOT NULL,
  datos_originales TEXT NOT NULL,
  PRIMARY KEY (id_detalle),
  KEY idx_rpsd_regularizacion (id_regularizacionFK),
  KEY idx_rpsd_pago_original (id_pago_original),
  CONSTRAINT fk_rpsd_regularizacion FOREIGN KEY (id_regularizacionFK)
    REFERENCES regularizacion_pago_salteado (id_regularizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS regularizacion_pago_salteado_reversion (
  id_reversion BIGINT(20) NOT NULL AUTO_INCREMENT,
  id_regularizacionFK BIGINT(20) NOT NULL,
  fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_id INT(11) NOT NULL,
  motivo VARCHAR(255) NOT NULL DEFAULT 'Reversion manual',
  PRIMARY KEY (id_reversion),
  UNIQUE KEY uq_rpsr_regularizacion (id_regularizacionFK),
  CONSTRAINT fk_rpsr_regularizacion FOREIGN KEY (id_regularizacionFK)
    REFERENCES regularizacion_pago_salteado (id_regularizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
