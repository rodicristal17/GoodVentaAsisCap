-- Permite guardar descripciones de pagos con el numero completo de factura.
-- Compatible con MySQL 5.6 y MariaDB 10.6.
-- No modifica ni elimina registros existentes.
ALTER TABLE pago
    MODIFY COLUMN descripcion VARCHAR(150) NOT NULL DEFAULT '';
