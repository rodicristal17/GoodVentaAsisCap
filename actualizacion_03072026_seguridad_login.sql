CREATE TABLE IF NOT EXISTS `seguridad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` varchar(45) DEFAULT NULL,
  `navegador` varchar(45) DEFAULT NULL,
  `pass` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_seguridad_usuario` (`id_usuario`),
  KEY `idx_seguridad_usuario_pass_nav` (`id_usuario`,`pass`,`navegador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET @tiene_pk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'seguridad'
    AND CONSTRAINT_TYPE = 'PRIMARY KEY'
);

SET @sql := IF(
  @tiene_pk = 0,
  'ALTER TABLE `seguridad` ADD PRIMARY KEY (`id`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @es_auto_increment := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'seguridad'
    AND COLUMN_NAME = 'id'
    AND EXTRA LIKE '%auto_increment%'
);

SET @sql := IF(
  @es_auto_increment = 0,
  'ALTER TABLE `seguridad` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @tiene_idx_usuario := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'seguridad'
    AND INDEX_NAME = 'idx_seguridad_usuario'
);

SET @sql := IF(
  @tiene_idx_usuario = 0,
  'ALTER TABLE `seguridad` ADD KEY `idx_seguridad_usuario` (`id_usuario`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @tiene_idx_usuario_pass_nav := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'seguridad'
    AND INDEX_NAME = 'idx_seguridad_usuario_pass_nav'
);

SET @sql := IF(
  @tiene_idx_usuario_pass_nav = 0,
  'ALTER TABLE `seguridad` ADD KEY `idx_seguridad_usuario_pass_nav` (`id_usuario`,`pass`,`navegador`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
