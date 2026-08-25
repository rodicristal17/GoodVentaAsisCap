-- Alinea el asunto de Hilos con el limite de 180 caracteres usado por la aplicacion.
-- Compatible con MySQL 5.6 y MariaDB 10.6.
-- No modifica ni elimina registros existentes.
ALTER TABLE interconsulta
    MODIFY COLUMN asunto VARCHAR(180) NULL DEFAULT NULL;
