SELECT DATABASE() AS base_actual;

SELECT TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('usuario', 'seguridad', 'persona', 'local')
ORDER BY TABLE_NAME;

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY, EXTRA
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
    (TABLE_NAME = 'usuario' AND COLUMN_NAME IN ('cod_usuario', 'login', 'password', 'cod_localFK', 'estado', 'url', 'acceso', 'fecha_creacion', 'rut_usuario', 'tipo'))
    OR
    (TABLE_NAME = 'seguridad' AND COLUMN_NAME IN ('id', 'id_usuario', 'navegador', 'pass'))
    OR
    (TABLE_NAME = 'persona' AND COLUMN_NAME IN ('cod_persona', 'nombre_persona', 'telefono', 'direccion', 'tipo_relacion', 'telefono_referencia'))
    OR
    (TABLE_NAME = 'local' AND COLUMN_NAME IN ('cod_local', 'Nombre'))
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columnas
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('usuario', 'seguridad')
GROUP BY TABLE_NAME, INDEX_NAME
ORDER BY TABLE_NAME, INDEX_NAME;
