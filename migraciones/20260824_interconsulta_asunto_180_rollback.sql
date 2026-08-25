-- Reversion manual de 20260824_interconsulta_asunto_180.sql.
-- Antes de ejecutar, esta consulta debe devolver 0:
-- SELECT COUNT(*) FROM interconsulta WHERE CHAR_LENGTH(asunto) > 100;
-- Si existen filas mayores a 100 caracteres, no ejecutar hasta revisarlas.
ALTER TABLE interconsulta
    MODIFY COLUMN asunto VARCHAR(100) NULL DEFAULT NULL;
