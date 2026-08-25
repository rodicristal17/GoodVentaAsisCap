DROP PROCEDURE IF EXISTS asegurar_agenda_insumos_atendido;

DELIMITER $$

CREATE PROCEDURE asegurar_agenda_insumos_atendido()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agenda_consumo_insumos' AND COLUMN_NAME = 'id_variante'
  ) THEN
    ALTER TABLE agenda_consumo_insumos ADD COLUMN id_variante INT NOT NULL DEFAULT 0;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agenda_consumo_insumos' AND COLUMN_NAME = 'stock_descontado'
  ) THEN
    ALTER TABLE agenda_consumo_insumos ADD COLUMN stock_descontado TINYINT(1) NOT NULL DEFAULT 0;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agenda_consumo_insumos' AND COLUMN_NAME = 'cantidad_descontada'
  ) THEN
    ALTER TABLE agenda_consumo_insumos ADD COLUMN cantidad_descontada DECIMAL(12,3) NOT NULL DEFAULT 0;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agenda_consumo_insumos' AND COLUMN_NAME = 'fecha_descontado'
  ) THEN
    ALTER TABLE agenda_consumo_insumos ADD COLUMN fecha_descontado DATETIME NULL;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agenda_consumo_insumos' AND COLUMN_NAME = 'usuario_desconto'
  ) THEN
    ALTER TABLE agenda_consumo_insumos ADD COLUMN usuario_desconto INT NULL;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'insumo_stock_consultorio' AND COLUMN_NAME = 'id_variante'
  ) THEN
    ALTER TABLE insumo_stock_consultorio ADD COLUMN id_variante INT NOT NULL DEFAULT 0;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'movimientos_insumos' AND COLUMN_NAME = 'grupo_movimiento'
  ) THEN
    ALTER TABLE movimientos_insumos ADD COLUMN grupo_movimiento VARCHAR(40) NULL;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'movimientos_insumos' AND COLUMN_NAME = 'id_variante'
  ) THEN
    ALTER TABLE movimientos_insumos ADD COLUMN id_variante INT NOT NULL DEFAULT 0;
  END IF;
END$$

DELIMITER ;

CALL asegurar_agenda_insumos_atendido();
DROP PROCEDURE IF EXISTS asegurar_agenda_insumos_atendido;

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
    (TABLE_NAME = 'agenda_consumo_insumos' AND COLUMN_NAME IN ('id_variante','stock_descontado','cantidad_descontada','fecha_descontado','usuario_desconto'))
    OR (TABLE_NAME = 'insumo_stock_consultorio' AND COLUMN_NAME = 'id_variante')
    OR (TABLE_NAME = 'movimientos_insumos' AND COLUMN_NAME IN ('grupo_movimiento','id_variante'))
  )
ORDER BY TABLE_NAME, COLUMN_NAME;
