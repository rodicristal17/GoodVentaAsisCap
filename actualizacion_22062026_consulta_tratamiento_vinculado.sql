DROP PROCEDURE IF EXISTS agregar_vinculo_consulta_tratamiento;

DELIMITER $$

CREATE PROCEDURE agregar_vinculo_consulta_tratamiento()
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'consulta'
      AND COLUMN_NAME = 'cod_detalle_ventaFK'
  ) THEN
    ALTER TABLE consulta ADD COLUMN cod_detalle_ventaFK INT NULL AFTER cod_clienteFK;
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'consulta'
      AND INDEX_NAME = 'idx_consulta_cod_detalle_ventaFK'
  ) THEN
    CREATE INDEX idx_consulta_cod_detalle_ventaFK ON consulta(cod_detalle_ventaFK);
  END IF;
END$$

DELIMITER ;

CALL agregar_vinculo_consulta_tratamiento();

DROP PROCEDURE IF EXISTS agregar_vinculo_consulta_tratamiento;
