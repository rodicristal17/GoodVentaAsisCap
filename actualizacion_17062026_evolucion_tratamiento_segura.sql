DROP PROCEDURE IF EXISTS agregar_columnas_evolucion_tratamiento_segura;

DELIMITER $$

CREATE PROCEDURE agregar_columnas_evolucion_tratamiento_segura()
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'evoluciontratamiento'
      AND COLUMN_NAME = 'porcentaje_anterior'
  ) THEN
    ALTER TABLE evoluciontratamiento ADD COLUMN porcentaje_anterior INT NULL;
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'evoluciontratamiento'
      AND COLUMN_NAME = 'observacion'
  ) THEN
    ALTER TABLE evoluciontratamiento ADD COLUMN observacion VARCHAR(255) NULL;
  END IF;
END$$

DELIMITER ;

CALL agregar_columnas_evolucion_tratamiento_segura();

DROP PROCEDURE IF EXISTS agregar_columnas_evolucion_tratamiento_segura;
