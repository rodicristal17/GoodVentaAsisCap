-- Permite registrar pagos administrativos sin geolocalizacion.
-- Compatible con MySQL 5.6 y MariaDB 10.6.
ALTER TABLE pago
    MODIFY COLUMN lat DOUBLE NOT NULL DEFAULT 0,
    MODIFY COLUMN lot DOUBLE NOT NULL DEFAULT 0,
    MODIFY COLUMN titulocuota VARCHAR(150) NOT NULL DEFAULT '';
