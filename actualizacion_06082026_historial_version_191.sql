-- GoodVenta / Clinident Salud / Sistema Telar
-- Sincroniza la interfaz X-GT-1-JMTG-V1.91 sin eliminar versiones anteriores.
-- Compatible con MySQL 5.6+.

START TRANSACTION;

UPDATE historialactualizacion
SET estado = 'Inactivo'
WHERE estado = 'Activo';

INSERT INTO historialactualizacion (codigo, detalles, fecha, estado)
SELECT
    'X-GT-1-JMTG-V1.91',
    'Mejoras de interconsulta y seleccion de sucursal',
    '2026-08-06',
    'Inactivo'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM historialactualizacion
    WHERE codigo = 'X-GT-1-JMTG-V1.91'
);

UPDATE historialactualizacion
SET detalles = 'Mejoras de interconsulta y seleccion de sucursal',
    fecha = '2026-08-06',
    estado = 'Activo'
WHERE idhistorialactualizacion = (
    SELECT version_actual.idhistorialactualizacion
    FROM (
        SELECT MAX(idhistorialactualizacion) AS idhistorialactualizacion
        FROM historialactualizacion
        WHERE codigo = 'X-GT-1-JMTG-V1.91'
    ) AS version_actual
);

COMMIT;

-- Validacion esperada: una sola version activa con codigo X-GT-1-JMTG-V1.91.
SELECT idhistorialactualizacion, codigo, detalles, fecha, estado
FROM historialactualizacion
ORDER BY idhistorialactualizacion DESC;

-- Reversion controlada para este ambiente, si fuera necesaria:
-- Debe ejecutarse solamente si la interfaz tambien vuelve a solicitar V1.90.
-- START TRANSACTION;
-- UPDATE historialactualizacion SET estado = 'Inactivo' WHERE estado = 'Activo';
-- UPDATE historialactualizacion SET estado = 'Activo'
-- WHERE idhistorialactualizacion = (
--     SELECT version_anterior.idhistorialactualizacion
--     FROM (
--         SELECT MAX(idhistorialactualizacion) AS idhistorialactualizacion
--         FROM historialactualizacion
--         WHERE codigo = 'X-GT-1-JMTG-V1.90'
--     ) AS version_anterior
-- );
-- COMMIT;
