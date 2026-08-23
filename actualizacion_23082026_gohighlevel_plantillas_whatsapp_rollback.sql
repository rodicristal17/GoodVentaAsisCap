-- Revierte solamente la fase 2B de plantillas de WhatsApp.
-- No modifica plantillas, conversaciones ni automatizaciones en HighLevel.

DROP TABLE IF EXISTS gohighlevel_envio_plantilla;
DROP TABLE IF EXISTS gohighlevel_plantilla_config;

SET @ghl_envia_plantilla_existe := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE()
    AND table_name='gohighlevel_permiso_usuario'
    AND column_name='puede_enviar_plantilla'
);
SET @ghl_envia_plantilla_sql := IF(
  @ghl_envia_plantilla_existe=1,
  'ALTER TABLE gohighlevel_permiso_usuario DROP COLUMN puede_enviar_plantilla',
  'SELECT 1'
);
PREPARE ghl_envia_plantilla_stmt FROM @ghl_envia_plantilla_sql;
EXECUTE ghl_envia_plantilla_stmt;
DEALLOCATE PREPARE ghl_envia_plantilla_stmt;
