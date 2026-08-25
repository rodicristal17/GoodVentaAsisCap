-- Rollback conservador: no elimina columnas porque pueden contener movimientos
-- y trazabilidad de stock ya utilizados por otras funciones del sistema.
-- Para revertir el comportamiento alcanza con restaurar los archivos PHP/JS.
SELECT 'NO_SE_ELIMINARON_COLUMNAS: se preservaron agenda, consumos y movimientos historicos' AS estado;
