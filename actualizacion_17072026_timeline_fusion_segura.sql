-- Clinident Salud - Fecha real de registro del timeline y fusion segura de Hilos.
-- Compatible con MySQL 5.7+/8 y PHP 7.2.
-- Migracion aditiva: no elimina mensajes ni hilos historicos.

SET NAMES latin1;
SET SESSION lock_wait_timeout = 15;

CREATE TABLE IF NOT EXISTS interconsulta_fusion (
  id_fusion bigint unsigned NOT NULL AUTO_INCREMENT,
  cod_interConsulta_origenFK int NOT NULL,
  cod_interConsulta_destinoFK int NOT NULL,
  cod_usuarioFK int NOT NULL,
  fecha_fusion datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resumen_movimientos longtext,
  estado varchar(20) NOT NULL DEFAULT 'aplicada',
  PRIMARY KEY (id_fusion),
  UNIQUE KEY uq_interconsulta_fusion_origen (cod_interConsulta_origenFK),
  KEY idx_interconsulta_fusion_destino (cod_interConsulta_destinoFK,fecha_fusion,id_fusion),
  KEY idx_interconsulta_fusion_usuario (cod_usuarioFK,fecha_fusion)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP PROCEDURE IF EXISTS migrar_timeline_fusion_17072026;

DELIMITER $$

CREATE PROCEDURE migrar_timeline_fusion_17072026()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='mensaje'
      AND column_name='fecha_registro_timeline'
  ) THEN
    ALTER TABLE mensaje
      ADD COLUMN fecha_registro_timeline datetime DEFAULT NULL AFTER fecha_creacion;

    -- Esta reconstruccion se ejecuta una sola vez, en el instante en que nace
    -- la columna. Asi un programado legacy no adquiere falsamente como fecha
    -- de alta su fecha programada si la migracion se vuelve a ejecutar meses despues.
    UPDATE mensaje
    SET fecha_registro_timeline=fecha_creacion
    WHERE fecha_registro_timeline IS NULL AND fecha_creacion<=NOW();
  END IF;

  -- Para mensajes historicos no programados, la fecha conocida de guardado es
  -- fecha_creacion. Los programados legacy futuros quedan NULL porque no existe
  -- una fecha de alta confiable para reconstruirlos sin inventar informacion.
  ALTER TABLE mensaje
    MODIFY fecha_registro_timeline datetime DEFAULT CURRENT_TIMESTAMP;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema=DATABASE() AND table_name='mensaje'
      AND index_name='idx_mensaje_hilo_registro_timeline'
  ) THEN
    ALTER TABLE mensaje
      ADD KEY idx_mensaje_hilo_registro_timeline
        (cod_interConsultaFK,fecha_registro_timeline,cod_mensaje);
  END IF;
END$$

DELIMITER ;

CALL migrar_timeline_fusion_17072026();
DROP PROCEDURE IF EXISTS migrar_timeline_fusion_17072026;

SELECT
  (SELECT COUNT(*) FROM information_schema.tables
   WHERE table_schema=DATABASE() AND table_name='interconsulta_fusion') AS tabla_fusion_disponible,
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema=DATABASE() AND table_name='mensaje'
     AND column_name='fecha_registro_timeline') AS fecha_registro_disponible,
  (SELECT COUNT(*) FROM mensaje
   WHERE fecha_registro_timeline IS NULL AND fecha_creacion<=NOW()) AS mensajes_historicos_sin_fecha_real;

-- Reversion controlada (no ejecutar con fusiones aplicadas sin exportarlas):
-- 1. Exportar interconsulta_fusion y verificar los consumidores del timeline.
-- 2. DROP TABLE interconsulta_fusion;
-- 3. ALTER TABLE mensaje DROP INDEX idx_mensaje_hilo_registro_timeline,
--      DROP COLUMN fecha_registro_timeline;
