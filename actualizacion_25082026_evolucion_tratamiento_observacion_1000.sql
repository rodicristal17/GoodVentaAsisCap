DROP PROCEDURE IF EXISTS ampliar_observacion_evolucion_tratamiento_1000;

DELIMITER $$

CREATE PROCEDURE ampliar_observacion_evolucion_tratamiento_1000()
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'evoluciontratamiento'
      AND COLUMN_NAME = 'observacion'
  ) THEN
    ALTER TABLE evoluciontratamiento
      ADD COLUMN observacion VARCHAR(1000) NULL;
  ELSEIF EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'evoluciontratamiento'
      AND COLUMN_NAME = 'observacion'
      AND CHARACTER_MAXIMUM_LENGTH < 1000
  ) THEN
    ALTER TABLE evoluciontratamiento
      MODIFY COLUMN observacion VARCHAR(1000) NULL;
  END IF;
END$$

DELIMITER ;

CALL ampliar_observacion_evolucion_tratamiento_1000();

DROP PROCEDURE IF EXISTS ampliar_observacion_evolucion_tratamiento_1000;

SELECT TABLE_NAME,
       COLUMN_NAME,
       COLUMN_TYPE,
       CHARACTER_MAXIMUM_LENGTH,
       IF(CHARACTER_MAXIMUM_LENGTH >= 1000, 'OK', 'ERROR') AS estado
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'evoluciontratamiento'
  AND COLUMN_NAME = 'observacion';
