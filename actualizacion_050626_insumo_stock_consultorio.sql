ALTER TABLE insumosconsl ADD COLUMN IF NOT EXISTS stock_minimo INT NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS insumo_stock_consultorio (
  id_stock INT NOT NULL AUTO_INCREMENT,
  id_insumo INT NOT NULL,
  cod_local INT NOT NULL,
  id_consultorio INT NOT NULL,
  cantidad DECIMAL(12,3) NOT NULL DEFAULT 0,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_stock),
  UNIQUE KEY uq_insumo_local_consultorio (id_insumo, cod_local, id_consultorio),
  KEY idx_insumo_stock_local (cod_local),
  KEY idx_insumo_stock_consultorio (id_consultorio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS movimientos_insumos (
  id INT NOT NULL AUTO_INCREMENT,
  grupo_movimiento VARCHAR(40) NULL,
  tipo ENUM('entrada','salida','ajuste') NOT NULL,
  insumo_id INT NOT NULL,
  sucursal_id INT NOT NULL,
  consultorio_id INT NOT NULL,
  cantidad DECIMAL(10,3) NOT NULL,
  motivo VARCHAR(255) NOT NULL,
  usuario_id INT NULL,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_mov_insumo (insumo_id),
  KEY idx_mov_fecha (fecha),
  KEY idx_mov_sucursal (sucursal_id),
  KEY idx_mov_consultorio (consultorio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

ALTER TABLE movimientos_insumos ADD COLUMN IF NOT EXISTS grupo_movimiento VARCHAR(40) NULL;
