ALTER TABLE interconsulta DROP INDEX IF EXISTS idx_interconsulta_venta;
ALTER TABLE venta DROP INDEX IF EXISTS idx_venta_cliente_fecha;
ALTER TABLE plan_definitivo_tratamiento DROP INDEX IF EXISTS idx_plan_activo_paciente;
