-- Indices derivados de la consulta lenta de seguimiento de Interconsulta.
-- MariaDB 10.6 admite IF NOT EXISTS para evitar duplicados.
ALTER TABLE interconsulta
    ADD INDEX IF NOT EXISTS idx_interconsulta_venta (cod_ventaFK);

ALTER TABLE venta
    ADD INDEX IF NOT EXISTS idx_venta_cliente_fecha (cod_clienteFK, fecha_venta, cod_venta);

ALTER TABLE plan_definitivo_tratamiento
    ADD INDEX IF NOT EXISTS idx_plan_activo_paciente (activo, paciente_id, id);

