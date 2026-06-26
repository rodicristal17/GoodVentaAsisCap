ALTER TABLE producto
  ADD COLUMN IF NOT EXISTS nivel_riesgo_financiero TINYINT NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS nivel_riesgo_origen VARCHAR(20) NOT NULL DEFAULT 'automatico',
  ADD COLUMN IF NOT EXISTS nivel_riesgo_observacion VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS nivel_riesgo_actualizado_por INT NULL,
  ADD COLUMN IF NOT EXISTS nivel_riesgo_actualizado_en DATETIME NULL;

CREATE TABLE IF NOT EXISTS producto_riesgo_auditoria (
  id_producto_riesgo_auditoria INT NOT NULL AUTO_INCREMENT,
  cod_productoFK INT NOT NULL,
  nivel_anterior TINYINT NULL,
  nivel_nuevo TINYINT NOT NULL,
  origen_anterior VARCHAR(20) NULL,
  origen_nuevo VARCHAR(20) NOT NULL,
  motivo VARCHAR(255) NULL,
  precio_producto DECIMAL(18,2) NULL,
  cod_usuarioFK INT NULL,
  fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_producto_riesgo_auditoria),
  KEY idx_producto_riesgo_auditoria_producto (cod_productoFK),
  KEY idx_producto_riesgo_auditoria_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

UPDATE producto
SET
  nivel_riesgo_financiero = CASE
    WHEN CAST(precio_producto AS DECIMAL(18,2)) <= 350000 THEN 1
    WHEN CAST(precio_producto AS DECIMAL(18,2)) <= 800000 THEN 2
    WHEN CAST(precio_producto AS DECIMAL(18,2)) <= 1500000 THEN 3
    WHEN CAST(precio_producto AS DECIMAL(18,2)) <= 3000000 THEN 4
    ELSE 5
  END,
  nivel_riesgo_origen = 'automatico',
  nivel_riesgo_actualizado_en = COALESCE(nivel_riesgo_actualizado_en, NOW())
WHERE nivel_riesgo_financiero IS NULL
   OR nivel_riesgo_origen IS NULL
   OR nivel_riesgo_origen = ''
   OR nivel_riesgo_origen = 'automatico';
