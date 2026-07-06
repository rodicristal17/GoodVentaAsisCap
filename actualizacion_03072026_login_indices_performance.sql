SET @tabla_usuario_existe := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuario'
);

SET @idx_login_usuario := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuario'
    AND INDEX_NAME = 'idx_usuario_login_password_local_estado'
);

SET @sql := IF(
  @tabla_usuario_existe > 0 AND @idx_login_usuario = 0,
  'ALTER TABLE `usuario` ADD KEY `idx_usuario_login_password_local_estado` (`login`,`password`,`cod_localFK`,`estado`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @tabla_seguridad_existe := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'seguridad'
);

SET @idx_seguridad_usuario := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'seguridad'
    AND INDEX_NAME = 'idx_seguridad_usuario'
);

SET @sql := IF(
  @tabla_seguridad_existe > 0 AND @idx_seguridad_usuario = 0,
  'ALTER TABLE `seguridad` ADD KEY `idx_seguridad_usuario` (`id_usuario`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_seguridad_usuario_pass_nav := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'seguridad'
    AND INDEX_NAME = 'idx_seguridad_usuario_pass_nav'
);

SET @sql := IF(
  @tabla_seguridad_existe > 0 AND @idx_seguridad_usuario_pass_nav = 0,
  'ALTER TABLE `seguridad` ADD KEY `idx_seguridad_usuario_pass_nav` (`id_usuario`,`pass`,`navegador`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
