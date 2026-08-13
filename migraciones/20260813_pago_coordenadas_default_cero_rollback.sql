-- Revierte los valores predeterminados sin modificar datos existentes.
ALTER TABLE pago
    MODIFY COLUMN lat DOUBLE NOT NULL,
    MODIFY COLUMN lot DOUBLE NOT NULL,
    MODIFY COLUMN titulocuota VARCHAR(150) NOT NULL;
