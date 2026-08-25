DROP PROCEDURE IF EXISTS revertir_observacion_evolucion_tratamiento_255;

DELIMITER $$

CREATE PROCEDURE revertir_observacion_evolucion_tratamiento_255()
BEGIN
  DECLARE longitud_maxima INT DEFAULT 0;

  IF EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'evoluciontratamiento'
      AND COLUMN_NAME = 'observacion'
  ) THEN
    SELECT IFNULL(MAX(CHAR_LENGTH(observacion)), 0)
      INTO longitud_maxima
    FROM evoluciontratamiento;

    IF longitud_maxima <= 255 THEN
      ALTER TABLE evoluciontratamiento
        MODIFY COLUMN observacion VARCHAR(255) NULL;
    ELSE
      SELECT 'NO_REVERTIDO: existen observaciones mayores a 255 caracteres' AS estado,
             longitud_maxima AS longitud_maxima;
    END IF;
  END IF;
END$$

DELIMITER ;

CALL revertir_observacion_evolucion_tratamiento_255();

DROP PROCEDURE IF EXISTS revertir_observacion_evolucion_tratamiento_255;
