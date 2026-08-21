-- Clinident Salud / Sistema Telar
-- Vinculo trazable entre llamadas iniciadas desde Telar y el Hilo operativo.
-- Compatible con MySQL 5.6 y MariaDB 10.6.
--
-- Migracion aditiva: no modifica CDR, pacientes, ventas ni llamadas historicas.

SET NAMES utf8mb4;

SET @telar_sql := IF(
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema=DATABASE()
     AND table_name='central_telefonica_solicitud_llamada'
     AND column_name='cod_interConsultaFK') = 0,
  'ALTER TABLE central_telefonica_solicitud_llamada ADD COLUMN cod_interConsultaFK INT NULL AFTER cod_clienteFK',
  'SELECT 1'
);
PREPARE telar_stmt FROM @telar_sql;
EXECUTE telar_stmt;
DEALLOCATE PREPARE telar_stmt;

SET @telar_sql := IF(
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema=DATABASE()
     AND table_name='central_telefonica_solicitud_llamada'
     AND column_name='origen_solicitud') = 0,
  "ALTER TABLE central_telefonica_solicitud_llamada ADD COLUMN origen_solicitud VARCHAR(30) NOT NULL DEFAULT 'central_telefonica' AFTER cod_interConsultaFK",
  'SELECT 1'
);
PREPARE telar_stmt FROM @telar_sql;
EXECUTE telar_stmt;
DEALLOCATE PREPARE telar_stmt;

SET @telar_sql := IF(
  (SELECT COUNT(*) FROM information_schema.statistics
   WHERE table_schema=DATABASE()
     AND table_name='central_telefonica_solicitud_llamada'
     AND index_name='idx_central_solicitud_hilo') = 0,
  'ALTER TABLE central_telefonica_solicitud_llamada ADD KEY idx_central_solicitud_hilo (cod_interConsultaFK,fecha_solicitud)',
  'SELECT 1'
);
PREPARE telar_stmt FROM @telar_sql;
EXECUTE telar_stmt;
DEALLOCATE PREPARE telar_stmt;

-- Conserva y vincula las solicitudes anteriores cuando ya existe un Hilo
-- maestro activo para el mismo paciente. No crea Hilos durante la migracion.
UPDATE central_telefonica_solicitud_llamada s
INNER JOIN interconsulta_paciente ip
  ON ip.cod_clienteFK_principal=s.cod_clienteFK AND ip.estado='activo'
SET s.cod_interConsultaFK=ip.cod_interConsultaFK
WHERE s.cod_interConsultaFK IS NULL;
