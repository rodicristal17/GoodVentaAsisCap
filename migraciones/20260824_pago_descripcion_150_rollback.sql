-- Reversion manual de 20260824_pago_descripcion_150.sql.
-- Antes de ejecutar, esta consulta debe devolver 0:
-- SELECT COUNT(*) FROM pago WHERE CHAR_LENGTH(descripcion) > 45;
-- Si existen filas mayores a 45 caracteres, no ejecutar hasta revisarlas.
ALTER TABLE pago
    MODIFY COLUMN descripcion VARCHAR(45) NOT NULL DEFAULT '';
