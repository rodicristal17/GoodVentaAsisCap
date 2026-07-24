-- Vinculo obligatorio para nuevas reposiciones. Los registros historicos
-- permanecen con NULL para conservar compatibilidad.
ALTER TABLE reposiciones_insumos
    ADD COLUMN cod_interConsultaFK INT NULL AFTER consultorio_id,
    ADD COLUMN cod_mensajeFK INT NULL AFTER cod_interConsultaFK,
    ADD KEY idx_reposicion_hilo (cod_interConsultaFK);
