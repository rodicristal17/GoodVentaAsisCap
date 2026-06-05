SET @schema_actual = DATABASE();

SET @sql_nombre_paciente = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE recetarios_indicaciones ADD COLUMN nombre_paciente VARCHAR(180) DEFAULT NULL AFTER cedula_titular',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_actual
    AND TABLE_NAME = 'recetarios_indicaciones'
    AND COLUMN_NAME = 'nombre_paciente'
);
PREPARE stmt_nombre_paciente FROM @sql_nombre_paciente;
EXECUTE stmt_nombre_paciente;
DEALLOCATE PREPARE stmt_nombre_paciente;

SET @sql_nombre_titular = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE recetarios_indicaciones ADD COLUMN nombre_titular VARCHAR(180) DEFAULT NULL AFTER nombre_paciente',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_actual
    AND TABLE_NAME = 'recetarios_indicaciones'
    AND COLUMN_NAME = 'nombre_titular'
);
PREPARE stmt_nombre_titular FROM @sql_nombre_titular;
EXECUTE stmt_nombre_titular;
DEALLOCATE PREPARE stmt_nombre_titular;

SET @sql_nombre_doctor = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE recetarios_indicaciones ADD COLUMN nombre_doctor VARCHAR(180) DEFAULT NULL AFTER sucursal_id',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_actual
    AND TABLE_NAME = 'recetarios_indicaciones'
    AND COLUMN_NAME = 'nombre_doctor'
);
PREPARE stmt_nombre_doctor FROM @sql_nombre_doctor;
EXECUTE stmt_nombre_doctor;
DEALLOCATE PREPARE stmt_nombre_doctor;

SET @sql_nombre_sucursal = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE recetarios_indicaciones ADD COLUMN nombre_sucursal VARCHAR(180) DEFAULT NULL AFTER nombre_doctor',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_actual
    AND TABLE_NAME = 'recetarios_indicaciones'
    AND COLUMN_NAME = 'nombre_sucursal'
);
PREPARE stmt_nombre_sucursal FROM @sql_nombre_sucursal;
EXECUTE stmt_nombre_sucursal;
DEALLOCATE PREPARE stmt_nombre_sucursal;

UPDATE recetarios_indicaciones r
LEFT JOIN persona pt ON pt.cod_persona = r.titular_id
LEFT JOIN persona pd ON pd.cod_persona = r.doctor_id
LEFT JOIN local l ON l.cod_local = r.sucursal_id
SET
    r.nombre_titular = IF(NULLIF(r.nombre_titular, '') IS NULL, pt.nombre_persona, r.nombre_titular),
    r.nombre_paciente = IF(
        NULLIF(r.nombre_paciente, '') IS NULL,
        CONCAT(IFNULL(pt.nombre_persona, ''), IF(IFNULL(r.apodo_venta, '') <> '', CONCAT(' (', r.apodo_venta, ')'), '')),
        r.nombre_paciente
    ),
    r.nombre_doctor = IF(NULLIF(r.nombre_doctor, '') IS NULL, pd.nombre_persona, r.nombre_doctor),
    r.nombre_sucursal = IF(NULLIF(r.nombre_sucursal, '') IS NULL, l.Nombre, r.nombre_sucursal);
