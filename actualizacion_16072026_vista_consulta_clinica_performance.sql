-- Indices para la busqueda de Historial Clinico y Evolucion.
-- Compatible con MySQL 5.6. Puede ejecutarse mas de una vez.

SET @indice_existe := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'venta'
    AND INDEX_NAME = 'idx_venta_num_factura'
);
SET @sql := IF(
  @indice_existe = 0,
  'ALTER TABLE venta ADD INDEX idx_venta_num_factura (num_factura)',
  'SELECT ''idx_venta_num_factura ya existe'' AS resultado'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @indice_existe := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'persona'
    AND INDEX_NAME = 'idx_persona_telefono'
);
SET @sql := IF(
  @indice_existe = 0,
  'ALTER TABLE persona ADD INDEX idx_persona_telefono (telefono)',
  'SELECT ''idx_persona_telefono ya existe'' AS resultado'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @indice_existe := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cliente'
    AND INDEX_NAME = 'idx_cliente_ci_cliente'
);
SET @sql := IF(
  @indice_existe = 0,
  'ALTER TABLE cliente ADD INDEX idx_cliente_ci_cliente (ci_cliente)',
  'SELECT ''idx_cliente_ci_cliente ya existe'' AS resultado'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @indice_existe := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cliente'
    AND INDEX_NAME = 'idx_cliente_rut_cliente'
);
SET @sql := IF(
  @indice_existe = 0,
  'ALTER TABLE cliente ADD INDEX idx_cliente_rut_cliente (rut_cliente)',
  'SELECT ''idx_cliente_rut_cliente ya existe'' AS resultado'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
