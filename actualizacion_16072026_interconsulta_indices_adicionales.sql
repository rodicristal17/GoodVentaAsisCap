-- Indices adicionales para el flujo de InterConsulta.
-- Ejecutar en una ventana de bajo trafico. Compatible con MySQL 5.6.

DELIMITER $$

DROP PROCEDURE IF EXISTS add_index_if_missing_interconsulta_extra$$
CREATE PROCEDURE add_index_if_missing_interconsulta_extra(
    IN p_table_name VARCHAR(64),
    IN p_index_name VARCHAR(64),
    IN p_index_sql TEXT
)
BEGIN
    IF EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=p_table_name
    ) AND NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=p_table_name AND INDEX_NAME=p_index_name
    ) THEN
        SET @sql_index=p_index_sql;
        PREPARE stmt_index FROM @sql_index;
        EXECUTE stmt_index;
        DEALLOCATE PREPARE stmt_index;
    END IF;
END$$

CALL add_index_if_missing_interconsulta_extra('cancelaciones','idx_cancelaciones_venta','ALTER TABLE cancelaciones ADD INDEX idx_cancelaciones_venta (cod_venta)')$$
CALL add_index_if_missing_interconsulta_extra('pago','idx_pago_credito_tipo','ALTER TABLE pago ADD INDEX idx_pago_credito_tipo (cod_creditoFK, Tipo)')$$
CALL add_index_if_missing_interconsulta_extra('interconsulta_paciente','idx_interconsulta_paciente_hilo_estado','ALTER TABLE interconsulta_paciente ADD INDEX idx_interconsulta_paciente_hilo_estado (cod_interConsultaFK, estado)')$$
CALL add_index_if_missing_interconsulta_extra('interconsulta_paciente','idx_interconsulta_paciente_cliente_estado','ALTER TABLE interconsulta_paciente ADD INDEX idx_interconsulta_paciente_cliente_estado (cod_clienteFK_principal, estado)')$$
CALL add_index_if_missing_interconsulta_extra('interconsulta_paciente_venta','idx_interconsulta_paciente_venta_hilo_estado_venta','ALTER TABLE interconsulta_paciente_venta ADD INDEX idx_interconsulta_paciente_venta_hilo_estado_venta (cod_interConsultaFK, estado, cod_ventaFK)')$$
CALL add_index_if_missing_interconsulta_extra('gastos','idx_gastos_interconsulta_estado_fecha','ALTER TABLE gastos ADD INDEX idx_gastos_interconsulta_estado_fecha (cod_interConsultaFK, estado, fecha)')$$
CALL add_index_if_missing_interconsulta_extra('dictamenes','idx_dictamenes_interconsulta','ALTER TABLE dictamenes ADD INDEX idx_dictamenes_interconsulta (cod_interConsultaFK)')$$
CALL add_index_if_missing_interconsulta_extra('seguridad','idx_seguridad_usuario_pass_navegador','ALTER TABLE seguridad ADD INDEX idx_seguridad_usuario_pass_navegador (id_usuario, pass, navegador)')$$

DROP PROCEDURE IF EXISTS add_index_if_missing_interconsulta_extra$$

DELIMITER ;
