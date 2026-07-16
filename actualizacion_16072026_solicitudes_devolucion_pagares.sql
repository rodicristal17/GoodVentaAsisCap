-- Solicitudes y trazabilidad de devolucion de pagares de legajos de venta.
-- Compatible con MySQL 5.7+/8 y PHP 7.2.
-- Cambio aditivo e idempotente: no modifica solicitudes, lotes ni documentos historicos.
-- Ejecutar con respaldo previo y antes de publicar la pestana de solicitudes de pagares.

SET NAMES latin1;
SET SESSION lock_wait_timeout = 15;

-- Preflight bloqueante: exige el circuito base de ventas, usuarios y legajos completo.
DROP PROCEDURE IF EXISTS preflight_devolucion_pagares_16072026;

DELIMITER $$

CREATE PROCEDURE preflight_devolucion_pagares_16072026()
BEGIN
  DECLARE tablas_requeridas INT DEFAULT 0;
  DECLARE columnas_requeridas INT DEFAULT 0;
  DECLARE tipo_pagare_disponible INT DEFAULT 0;
  DECLARE permisos_reutilizables INT DEFAULT 0;

  SELECT COUNT(*) INTO tablas_requeridas
  FROM information_schema.tables
  WHERE table_schema = DATABASE()
    AND table_name IN (
      'venta','cliente','persona','local','usuario','listadodeacceso',
      'centro_legajo_documento','centro_legajo_lote','centro_legajo_lote_detalle',
      'centro_legajo_lote_evento','centro_legajo_documento_evento'
    );
  IF tablas_requeridas <> 11 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Devolucion de pagares: faltan tablas base o de legajos';
  END IF;

  SELECT COUNT(*) INTO columnas_requeridas
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND (
      (table_name='centro_legajo_documento' AND column_name IN (
        'id_documento','cod_ventaFK','tipo_documento','estado_fisico',
        'cod_local_ubicacionFK','ubicacion_fisica'
      ))
      OR (table_name='centro_legajo_lote' AND column_name IN (
        'id_lote','codigo_lote','estado'
      ))
      OR (table_name='centro_legajo_lote_detalle' AND column_name IN (
        'id_loteFK','id_documentoFK','estado'
      ))
    );
  IF columnas_requeridas <> 12 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Devolucion de pagares: estructura de legajos incompleta';
  END IF;

  SELECT COUNT(*) INTO tipo_pagare_disponible
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'centro_legajo_documento'
    AND column_name = 'tipo_documento'
    AND data_type = 'enum'
    AND column_type LIKE '%''pagare''%';
  IF tipo_pagare_disponible <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Devolucion de pagares: tipo documental pagare no disponible';
  END IF;

  SELECT COUNT(*) INTO permisos_reutilizables
  FROM listadodeacceso
  WHERE codigo IN ('GESTIONARLEGAJOSVENTA','ADMINCENTROFACTURAS')
    AND tipo = 'Administrativo';
  IF permisos_reutilizables <> 2 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Devolucion de pagares: faltan permisos administrativos base';
  END IF;

  SELECT 'OK' AS preflight_devolucion_pagares;
END$$

DELIMITER ;

CALL preflight_devolucion_pagares_16072026();
DROP PROCEDURE IF EXISTS preflight_devolucion_pagares_16072026;

-- Agrega el estado al final del ENUM actual, sin reconstruir ni quitar estados existentes.
SET @clps_estado_fisico_tipo := (
  SELECT column_type
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'centro_legajo_documento'
    AND column_name = 'estado_fisico'
  LIMIT 1
);
SET @clps_estado_fisico_sql := IF(
  @clps_estado_fisico_tipo LIKE '%''devuelto_cliente''%',
  'SELECT ''centro_legajo_documento.estado_fisico ya admite devuelto_cliente'' AS info',
  CONCAT(
    'ALTER TABLE `centro_legajo_documento` MODIFY COLUMN `estado_fisico` ',
    LEFT(@clps_estado_fisico_tipo, CHAR_LENGTH(@clps_estado_fisico_tipo) - 1),
    ',''devuelto_cliente'') NOT NULL DEFAULT ''pendiente'', ALGORITHM=INPLACE, LOCK=NONE'
  )
);
PREPARE clps_stmt FROM @clps_estado_fisico_sql;
EXECUTE clps_stmt;
DEALLOCATE PREPARE clps_stmt;

-- Una solicitud siempre apunta a la copia fisica de tipo pagare. El servidor valida
-- el tipo documental, los cambios de estado y la espera obligatoria de lotes en transito.
CREATE TABLE IF NOT EXISTS `centro_legajo_pagare_solicitud` (
  `id_solicitud` int NOT NULL AUTO_INCREMENT,
  `codigo_solicitud` varchar(40) NOT NULL,
  `id_documentoFK` int NOT NULL,
  `cod_ventaFK` int NOT NULL,
  `estado` enum('solicitada','aprobada','esperando_recepcion','preparada','entregada','rechazada','cancelada') NOT NULL DEFAULT 'solicitada',
  `solicitud_abierta` tinyint(1) GENERATED ALWAYS AS (
    CASE WHEN `estado` IN ('solicitada','aprobada','esperando_recepcion','preparada') THEN 1 ELSE NULL END
  ) STORED,
  `solicitante_nombre` varchar(255) NOT NULL,
  `solicitante_documento` varchar(45) DEFAULT NULL,
  `motivo_solicitud` text NOT NULL,
  `estado_fisico_snapshot` varchar(30) NOT NULL,
  `cod_local_ubicacion_snapshotFK` int DEFAULT NULL,
  `ubicacion_fisica_snapshot` varchar(255) DEFAULT NULL,
  `id_lote_snapshotFK` int DEFAULT NULL,
  `codigo_lote_snapshot` varchar(40) DEFAULT NULL,
  `estado_lote_snapshot` varchar(30) DEFAULT NULL,
  `observacion_resolucion` text,
  `observacion_entrega` text,
  `cod_usuario_solicitaFK` int NOT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuario_apruebaFK` int DEFAULT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `fecha_esperando_recepcion` datetime DEFAULT NULL,
  `cod_usuario_preparaFK` int DEFAULT NULL,
  `fecha_preparacion` datetime DEFAULT NULL,
  `receptor_nombre` varchar(255) DEFAULT NULL,
  `receptor_documento` varchar(45) DEFAULT NULL,
  `receptor_relacion` varchar(100) DEFAULT NULL,
  `cod_usuario_entregaFK` int DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `cod_usuario_rechazaFK` int DEFAULT NULL,
  `fecha_rechazo` datetime DEFAULT NULL,
  `cod_usuario_cancelaFK` int DEFAULT NULL,
  `fecha_cancelacion` datetime DEFAULT NULL,
  `evidencia_nombre_fisico` varchar(100) DEFAULT NULL,
  `evidencia_nombre_original` varchar(255) DEFAULT NULL,
  `evidencia_extension` varchar(10) DEFAULT NULL,
  `evidencia_mime_type` varchar(100) DEFAULT NULL,
  `evidencia_hash_sha256` char(64) DEFAULT NULL,
  `cod_usuario_evidenciaFK` int DEFAULT NULL,
  `fecha_evidencia` datetime DEFAULT NULL,
  `cod_usuarioFK_update` int DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  `version_registro` int unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_solicitud`),
  UNIQUE KEY `uq_clps_codigo` (`codigo_solicitud`),
  UNIQUE KEY `uq_clps_documento_abierto` (`id_documentoFK`,`solicitud_abierta`),
  KEY `idx_clps_estado_fecha` (`estado`,`fecha_solicitud`,`id_solicitud`),
  KEY `idx_clps_documento_fecha` (`id_documentoFK`,`fecha_solicitud`,`id_solicitud`),
  KEY `idx_clps_venta_estado` (`cod_ventaFK`,`estado`,`fecha_solicitud`),
  KEY `idx_clps_lote_estado` (`id_lote_snapshotFK`,`estado`),
  KEY `idx_clps_solicita_fecha` (`cod_usuario_solicitaFK`,`fecha_solicitud`),
  KEY `idx_clps_evidencia_hash` (`evidencia_hash_sha256`),
  CONSTRAINT `fk_clps_documento` FOREIGN KEY (`id_documentoFK`) REFERENCES `centro_legajo_documento` (`id_documento`),
  CONSTRAINT `fk_clps_local_snapshot` FOREIGN KEY (`cod_local_ubicacion_snapshotFK`) REFERENCES `local` (`cod_local`) ON DELETE SET NULL,
  CONSTRAINT `fk_clps_lote_snapshot` FOREIGN KEY (`id_lote_snapshotFK`) REFERENCES `centro_legajo_lote` (`id_lote`) ON DELETE SET NULL,
  CONSTRAINT `fk_clps_usuario_solicita` FOREIGN KEY (`cod_usuario_solicitaFK`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_clps_usuario_aprueba` FOREIGN KEY (`cod_usuario_apruebaFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_clps_usuario_prepara` FOREIGN KEY (`cod_usuario_preparaFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_clps_usuario_entrega` FOREIGN KEY (`cod_usuario_entregaFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_clps_usuario_rechaza` FOREIGN KEY (`cod_usuario_rechazaFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_clps_usuario_cancela` FOREIGN KEY (`cod_usuario_cancelaFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_clps_usuario_evidencia` FOREIGN KEY (`cod_usuario_evidenciaFK`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_clps_usuario_update` FOREIGN KEY (`cod_usuarioFK_update`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Bitacora append-only. Cada transicion conserva tambien la ubicacion conocida
-- en ese instante; los snapshots de codigo sobreviven aunque un FK quede en NULL.
CREATE TABLE IF NOT EXISTS `centro_legajo_pagare_solicitud_evento` (
  `id_evento` bigint NOT NULL AUTO_INCREMENT,
  `id_solicitudFK` int NOT NULL,
  `id_documentoFK` int NOT NULL,
  `cod_ventaFK` int NOT NULL,
  `tipo_evento` varchar(50) NOT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `estado_nuevo` varchar(30) DEFAULT NULL,
  `estado_fisico_snapshot` varchar(30) DEFAULT NULL,
  `cod_local_ubicacion_snapshotFK` int DEFAULT NULL,
  `ubicacion_fisica_snapshot` varchar(255) DEFAULT NULL,
  `id_lote_snapshotFK` int DEFAULT NULL,
  `codigo_lote_snapshot` varchar(40) DEFAULT NULL,
  `estado_lote_snapshot` varchar(30) DEFAULT NULL,
  `detalle` text,
  `cod_usuario_actorFK` int NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evento`),
  KEY `idx_clpse_solicitud_fecha` (`id_solicitudFK`,`fecha_hora`,`id_evento`),
  KEY `idx_clpse_documento_fecha` (`id_documentoFK`,`fecha_hora`,`id_evento`),
  KEY `idx_clpse_venta_fecha` (`cod_ventaFK`,`fecha_hora`,`id_evento`),
  KEY `idx_clpse_actor_fecha` (`cod_usuario_actorFK`,`fecha_hora`),
  KEY `idx_clpse_lote_fecha` (`id_lote_snapshotFK`,`fecha_hora`),
  CONSTRAINT `fk_clpse_solicitud` FOREIGN KEY (`id_solicitudFK`) REFERENCES `centro_legajo_pagare_solicitud` (`id_solicitud`),
  CONSTRAINT `fk_clpse_documento` FOREIGN KEY (`id_documentoFK`) REFERENCES `centro_legajo_documento` (`id_documento`),
  CONSTRAINT `fk_clpse_local_snapshot` FOREIGN KEY (`cod_local_ubicacion_snapshotFK`) REFERENCES `local` (`cod_local`) ON DELETE SET NULL,
  CONSTRAINT `fk_clpse_lote_snapshot` FOREIGN KEY (`id_lote_snapshotFK`) REFERENCES `centro_legajo_lote` (`id_lote`) ON DELETE SET NULL,
  CONSTRAINT `fk_clpse_usuario_actor` FOREIGN KEY (`cod_usuario_actorFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Firma posterior: dos tablas, estado fisico ampliado, evidencia privada y permisos reutilizados.
SELECT
  (SELECT COUNT(*) FROM information_schema.tables
   WHERE table_schema=DATABASE() AND table_name IN (
     'centro_legajo_pagare_solicitud','centro_legajo_pagare_solicitud_evento'
   )) AS tablas_devolucion_pagare,
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema=DATABASE()
     AND table_name='centro_legajo_documento'
     AND column_name='estado_fisico'
     AND column_type LIKE '%''devuelto_cliente''%') AS estado_devuelto_cliente_disponible,
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema=DATABASE()
     AND table_name='centro_legajo_pagare_solicitud'
     AND column_name IN (
       'evidencia_nombre_fisico','evidencia_nombre_original','evidencia_extension',
       'evidencia_mime_type','evidencia_hash_sha256','fecha_evidencia'
     )) AS campos_evidencia_privada,
  (SELECT COUNT(*) FROM information_schema.table_constraints
   WHERE constraint_schema=DATABASE()
     AND table_name IN ('centro_legajo_pagare_solicitud','centro_legajo_pagare_solicitud_evento')
     AND constraint_type='FOREIGN KEY') AS claves_foraneas_devolucion,
  (SELECT COUNT(*) FROM listadodeacceso
   WHERE codigo IN ('GESTIONARLEGAJOSVENTA','ADMINCENTROFACTURAS')
     AND tipo='Administrativo') AS permisos_reutilizados,
  (SELECT COUNT(*) FROM centro_legajo_pagare_solicitud) AS solicitudes_conservadas,
  (SELECT COUNT(*) FROM centro_legajo_pagare_solicitud_evento) AS eventos_conservados;

-- Reversion controlada (no ejecutar si existe trazabilidad que deba conservarse):
-- 1. Ocultar primero la pestana, bloquear nuevas solicitudes y exportar ambas tablas.
-- 2. Respaldar por separado los archivos privados identificados por evidencia_nombre_fisico.
-- 3. Verificar que no existan documentos con estado_fisico='devuelto_cliente'.
-- 4. DROP TABLE centro_legajo_pagare_solicitud_evento;
-- 5. DROP TABLE centro_legajo_pagare_solicitud;
-- 6. Solo con conteo cero y despues de revisar el ENUM vigente, retirar devuelto_cliente:
--    ALTER TABLE centro_legajo_documento MODIFY COLUMN estado_fisico
--      enum('pendiente','en_sucursal','en_lote','pendiente_custodia','en_transito','recibido','faltante','observado','no_aplica')
--      NOT NULL DEFAULT 'pendiente', ALGORITHM=INPLACE, LOCK=NONE;
-- 7. No eliminar ni modificar permisos: esta migracion no crea accesos nuevos.
