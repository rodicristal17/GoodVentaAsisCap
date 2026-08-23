-- Revierte solamente la fase 2A de respuestas manuales.
-- No modifica conversaciones, contactos ni automatizaciones de HighLevel.

DROP TABLE IF EXISTS gohighlevel_envio_manual;

SET @ghl_responde_existe := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE()
    AND table_name='gohighlevel_permiso_usuario'
    AND column_name='puede_responder'
);
SET @ghl_responde_sql := IF(
  @ghl_responde_existe=1,
  'ALTER TABLE gohighlevel_permiso_usuario DROP COLUMN puede_responder',
  'SELECT 1'
);
PREPARE ghl_responde_stmt FROM @ghl_responde_sql;
EXECUTE ghl_responde_stmt;
DEALLOCATE PREPARE ghl_responde_stmt;

