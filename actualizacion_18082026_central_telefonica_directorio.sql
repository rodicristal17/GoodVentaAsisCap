-- Clinident Salud / Sistema Telar
-- Central Telefonica - Directorio, ruta/cola y funcionario que atendio.
-- Compatible con MySQL 5.6 y PHP 7.2.
--
-- Migracion aditiva: no elimina CDR, no modifica Issabel y conserva la columna
-- legacy `extension` para consumidores anteriores.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS central_telefonica_directorio (
  extension VARCHAR(20) NOT NULL,
  tipo VARCHAR(20) NOT NULL DEFAULT 'interna',
  nombre VARCHAR(120) NOT NULL DEFAULT '',
  descripcion VARCHAR(160) NOT NULL DEFAULT '',
  cod_usuarioFK INT DEFAULT NULL,
  cod_localFK INT DEFAULT NULL,
  sede_nombre VARCHAR(100) NOT NULL DEFAULT '',
  fuente VARCHAR(20) NOT NULL DEFAULT 'issabel',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_ultima_fuente DATETIME DEFAULT NULL,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (extension),
  KEY idx_central_directorio_tipo_activo (tipo,activo),
  KEY idx_central_directorio_local_activo (cod_localFK,activo),
  KEY idx_central_directorio_usuario (cod_usuarioFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='ruta_extension'),
  'SELECT 1',
  "ALTER TABLE central_telefonica_llamada ADD COLUMN ruta_extension VARCHAR(20) NOT NULL DEFAULT '' AFTER extension"
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='ruta_tipo'),
  'SELECT 1',
  "ALTER TABLE central_telefonica_llamada ADD COLUMN ruta_tipo VARCHAR(20) NOT NULL DEFAULT '' AFTER ruta_extension"
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='ruta_nombre'),
  'SELECT 1',
  "ALTER TABLE central_telefonica_llamada ADD COLUMN ruta_nombre VARCHAR(120) NOT NULL DEFAULT '' AFTER ruta_tipo"
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='funcionario_extension'),
  'SELECT 1',
  "ALTER TABLE central_telefonica_llamada ADD COLUMN funcionario_extension VARCHAR(20) NOT NULL DEFAULT '' AFTER ruta_nombre"
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='funcionario_nombre'),
  'SELECT 1',
  "ALTER TABLE central_telefonica_llamada ADD COLUMN funcionario_nombre VARCHAR(255) NOT NULL DEFAULT '' AFTER funcionario_extension"
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='funcionario_sede'),
  'SELECT 1',
  "ALTER TABLE central_telefonica_llamada ADD COLUMN funcionario_sede VARCHAR(100) NOT NULL DEFAULT '' AFTER funcionario_nombre"
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='funcionario_cod_usuario'),
  'SELECT 1',
  'ALTER TABLE central_telefonica_llamada ADD COLUMN funcionario_cod_usuario INT DEFAULT NULL AFTER funcionario_sede'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='funcionario_cod_local'),
  'SELECT 1',
  'ALTER TABLE central_telefonica_llamada ADD COLUMN funcionario_cod_local INT DEFAULT NULL AFTER funcionario_cod_usuario'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='funcionario_destino_extension'),
  'SELECT 1',
  "ALTER TABLE central_telefonica_llamada ADD COLUMN funcionario_destino_extension VARCHAR(20) NOT NULL DEFAULT '' AFTER funcionario_cod_local"
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='funcionario_destino_nombre'),
  'SELECT 1',
  "ALTER TABLE central_telefonica_llamada ADD COLUMN funcionario_destino_nombre VARCHAR(255) NOT NULL DEFAULT '' AFTER funcionario_destino_extension"
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='funcionario_destino_sede'),
  'SELECT 1',
  "ALTER TABLE central_telefonica_llamada ADD COLUMN funcionario_destino_sede VARCHAR(100) NOT NULL DEFAULT '' AFTER funcionario_destino_nombre"
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='funcionario_destino_cod_usuario'),
  'SELECT 1',
  'ALTER TABLE central_telefonica_llamada ADD COLUMN funcionario_destino_cod_usuario INT DEFAULT NULL AFTER funcionario_destino_sede'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND column_name='funcionario_destino_cod_local'),
  'SELECT 1',
  'ALTER TABLE central_telefonica_llamada ADD COLUMN funcionario_destino_cod_local INT DEFAULT NULL AFTER funcionario_destino_cod_usuario'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND index_name='idx_central_llamada_ruta'),
  'SELECT 1',
  'ALTER TABLE central_telefonica_llamada ADD KEY idx_central_llamada_ruta (ruta_extension,fecha_inicio)'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND index_name='idx_central_llamada_funcionario'),
  'SELECT 1',
  'ALTER TABLE central_telefonica_llamada ADD KEY idx_central_llamada_funcionario (funcionario_extension,fecha_inicio)'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada' AND index_name='idx_central_llamada_sede'),
  'SELECT 1',
  'ALTER TABLE central_telefonica_llamada ADD KEY idx_central_llamada_sede (funcionario_cod_local,fecha_inicio)'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Precarga conservadora: salientes e internas poseen un origen interno
-- conocido. En entrantes no se copia `extension` como funcionario porque
-- puede representar una cola (por ejemplo 9000).
UPDATE central_telefonica_llamada
SET funcionario_extension=extension
WHERE funcionario_extension='' AND tipo IN ('saliente_externa','interna')
  AND extension<>'';

UPDATE central_telefonica_llamada
SET ruta_extension=extension,ruta_tipo='interna'
WHERE ruta_extension='' AND tipo='entrante_externa' AND extension<>'';

UPDATE central_telefonica_llamada
SET ruta_tipo='salida',ruta_nombre='Salida directa'
WHERE tipo='saliente_externa' AND ruta_tipo='';

UPDATE central_telefonica_llamada
SET ruta_tipo='interna',ruta_nombre='Llamada interna'
WHERE tipo='interna' AND ruta_tipo='';

SELECT COUNT(*) AS tablas_directorio
FROM information_schema.tables
WHERE table_schema=DATABASE() AND table_name='central_telefonica_directorio';

SELECT COUNT(*) AS columnas_identidad
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='central_telefonica_llamada'
  AND column_name IN (
    'ruta_extension','ruta_tipo','ruta_nombre','funcionario_extension',
    'funcionario_nombre','funcionario_sede','funcionario_cod_usuario',
    'funcionario_cod_local','funcionario_destino_extension',
    'funcionario_destino_nombre','funcionario_destino_sede',
    'funcionario_destino_cod_usuario','funcionario_destino_cod_local'
  );
