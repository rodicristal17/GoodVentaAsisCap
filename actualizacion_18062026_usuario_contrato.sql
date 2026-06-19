-- Agrega fecha de vigencia del contrato al funcionario/usuario.
-- Campo opcional: permite dejarlo vacio si el contrato aun no tiene vencimiento definido.

SET @usuario_fecha_contrato_existe := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'usuario'
    AND column_name = 'fecha_vencimiento_contrato'
);

SET @usuario_fecha_contrato_sql := IF(
  @usuario_fecha_contrato_existe = 0,
  'ALTER TABLE usuario ADD COLUMN fecha_vencimiento_contrato DATE DEFAULT NULL AFTER fecha_creacion',
  'SELECT ''fecha_vencimiento_contrato ya existe en usuario'' AS mensaje'
);

PREPARE stmt_usuario_fecha_contrato FROM @usuario_fecha_contrato_sql;
EXECUTE stmt_usuario_fecha_contrato;
DEALLOCATE PREPARE stmt_usuario_fecha_contrato;
