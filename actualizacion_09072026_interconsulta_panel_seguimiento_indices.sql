-- Indices de apoyo para el panel de seguimiento de pacientes en Hilos.
-- Ejecutar en la base de GoodVenta/Clinident donde ya existen agenda y credito.

SET @idx_agenda_paciente := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
        AND table_name = 'agenda'
        AND index_name = 'idx_agenda_paciente_fecha_estado'
);
SET @sql_agenda_paciente := IF(
    @idx_agenda_paciente = 0,
    'ALTER TABLE agenda ADD INDEX idx_agenda_paciente_fecha_estado (id_paciente, fecha, estado)',
    'SELECT ''idx_agenda_paciente_fecha_estado ya existe'' AS mensaje'
);
PREPARE stmt_agenda_paciente FROM @sql_agenda_paciente;
EXECUTE stmt_agenda_paciente;
DEALLOCATE PREPARE stmt_agenda_paciente;

SET @idx_agenda_venta := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
        AND table_name = 'agenda'
        AND index_name = 'idx_agenda_venta_fecha_estado'
);
SET @sql_agenda_venta := IF(
    @idx_agenda_venta = 0,
    'ALTER TABLE agenda ADD INDEX idx_agenda_venta_fecha_estado (cod_ventaFK, fecha, estado)',
    'SELECT ''idx_agenda_venta_fecha_estado ya existe'' AS mensaje'
);
PREPARE stmt_agenda_venta FROM @sql_agenda_venta;
EXECUTE stmt_agenda_venta;
DEALLOCATE PREPARE stmt_agenda_venta;

SET @idx_credito_venta := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
        AND table_name = 'credito'
        AND index_name = 'idx_credito_venta_estado_fecha'
);
SET @sql_credito_venta := IF(
    @idx_credito_venta = 0,
    'ALTER TABLE credito ADD INDEX idx_credito_venta_estado_fecha (cod_venta, Esado, fechapago)',
    'SELECT ''idx_credito_venta_estado_fecha ya existe'' AS mensaje'
);
PREPARE stmt_credito_venta FROM @sql_credito_venta;
EXECUTE stmt_credito_venta;
DEALLOCATE PREPARE stmt_credito_venta;
