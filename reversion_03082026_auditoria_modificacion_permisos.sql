-- Reversion controlada. Elimina solamente el trigger y la tabla de esta auditoria.
DROP TRIGGER IF EXISTS trg_accesosuser_auditoria_update;
DROP TABLE IF EXISTS accesosuser_auditoria;
