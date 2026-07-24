-- El rollback elimina solamente el vinculo con Hilos. No modifica stock,
-- movimientos, reposiciones ni mensajes ya registrados.
ALTER TABLE reposiciones_insumos
    DROP INDEX idx_reposicion_hilo,
    DROP COLUMN cod_mensajeFK,
    DROP COLUMN cod_interConsultaFK;
