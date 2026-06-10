ALTER TABLE insumosconsl
  ADD COLUMN IF NOT EXISTS tiene_variantes TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS tipo_variante VARCHAR(60) NULL;

ALTER TABLE insumosconsl
  MODIFY COLUMN cant_stock DECIMAL(12,3) NOT NULL DEFAULT 0,
  MODIFY COLUMN stock_minimo DECIMAL(12,3) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS insumo_variantes (
  id_variante INT NOT NULL AUTO_INCREMENT,
  insumo_id INT NOT NULL,
  nombre_variante VARCHAR(120) NOT NULL,
  stock DECIMAL(12,3) NOT NULL DEFAULT 0,
  stock_minimo DECIMAL(12,3) NOT NULL DEFAULT 0,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_variante),
  UNIQUE KEY uq_insumo_variante (insumo_id, nombre_variante),
  KEY idx_variante_insumo (insumo_id),
  KEY idx_variante_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

ALTER TABLE insumo_stock_consultorio
  ADD COLUMN IF NOT EXISTS id_variante INT NOT NULL DEFAULT 0;

ALTER TABLE insumo_stock_consultorio
  DROP INDEX uq_insumo_local_consultorio;

ALTER TABLE insumo_stock_consultorio
  ADD UNIQUE KEY uq_insumo_local_consultorio_variante (id_insumo, id_variante, cod_local, id_consultorio);

ALTER TABLE movimientos_insumos
  ADD COLUMN IF NOT EXISTS id_variante INT NOT NULL DEFAULT 0;

ALTER TABLE agenda_consumo_insumos
  ADD COLUMN IF NOT EXISTS id_variante INT NOT NULL DEFAULT 0;
