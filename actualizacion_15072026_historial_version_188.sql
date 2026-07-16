-- GoodVenta / Clinident Salud
-- Registra la version de interfaz X-GT-1-JMTG-V1.88 sin eliminar versiones anteriores.
-- Compatible con MySQL 5.6+.

START TRANSACTION;

UPDATE historialactualizacion
SET estado = 'Inactivo'
WHERE estado = 'Activo';

INSERT INTO historialactualizacion (codigo, detalles, fecha, estado)
SELECT
    'X-GT-1-JMTG-V1.88',
    'Integracion de mejoras locales con diseno responsive y flujo financiero',
    '2026-07-15',
    'Inactivo'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM historialactualizacion
    WHERE codigo = 'X-GT-1-JMTG-V1.88'
);

UPDATE historialactualizacion
SET detalles = 'Integracion de mejoras locales con diseno responsive y flujo financiero',
    fecha = '2026-07-15',
    estado = 'Activo'
WHERE idhistorialactualizacion = (
    SELECT version_actual.idhistorialactualizacion
    FROM (
        SELECT MAX(idhistorialactualizacion) AS idhistorialactualizacion
        FROM historialactualizacion
        WHERE codigo = 'X-GT-1-JMTG-V1.88'
    ) AS version_actual
);

COMMIT;

-- Validacion esperada: una sola version activa con codigo X-GT-1-JMTG-V1.88.
SELECT idhistorialactualizacion, codigo, detalles, fecha, estado
FROM historialactualizacion
ORDER BY idhistorialactualizacion DESC;

-- Reversion controlada para este ambiente, si fuera necesaria:
-- START TRANSACTION;
-- UPDATE historialactualizacion SET estado = 'Inactivo' WHERE estado = 'Activo';
-- UPDATE historialactualizacion SET estado = 'Activo'
-- WHERE idhistorialactualizacion = (
--     SELECT version_anterior.idhistorialactualizacion
--     FROM (
--         SELECT MAX(idhistorialactualizacion) AS idhistorialactualizacion
--         FROM historialactualizacion
--         WHERE codigo = 'X-GT-1-JMTG-V1.87'
--     ) AS version_anterior
-- );
-- COMMIT;
