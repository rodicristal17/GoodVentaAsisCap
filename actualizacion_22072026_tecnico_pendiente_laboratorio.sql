-- Clinident Salud / Sistema Telar
-- Permite preparar trabajos de laboratorio sin tecnico asignado.
-- Compatible con MySQL usado por GoodVenta y ejecutable mas de una vez.

DROP PROCEDURE IF EXISTS telar_lab_tecnico_pendiente_nullable;
DELIMITER $$
CREATE PROCEDURE telar_lab_tecnico_pendiente_nullable(
    IN p_tabla VARCHAR(64),
    IN p_columna VARCHAR(64)
)
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE()
          AND TABLE_NAME=p_tabla
          AND COLUMN_NAME=p_columna
          AND IS_NULLABLE='NO'
    ) THEN
        SET @telar_lab_sql = CONCAT(
            'ALTER TABLE `', p_tabla, '` MODIFY COLUMN `', p_columna, '` INT NULL'
        );
        PREPARE telar_lab_stmt FROM @telar_lab_sql;
        EXECUTE telar_lab_stmt;
        DEALLOCATE PREPARE telar_lab_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_tecnico_pendiente_nullable('trabajo_laboratorio', 'cod_mecanico_dentalFK');
CALL telar_lab_tecnico_pendiente_nullable('trabajo_laboratorio', 'cod_tecnico_usuarioFK');

DROP PROCEDURE IF EXISTS telar_lab_tecnico_pendiente_nullable;

-- La tabla legacy ya admite cod_mecanicoDentalFK NULL. No se modifican datos,
-- estados ni relaciones existentes.

-- Reversion controlada (no ejecutar mientras existan trabajos sin tecnico):
-- 1. Asignar mecanico y usuario tecnico a todos los trabajos pendientes.
-- 2. Restaurar ambas columnas con MODIFY COLUMN ... INT NOT NULL.
