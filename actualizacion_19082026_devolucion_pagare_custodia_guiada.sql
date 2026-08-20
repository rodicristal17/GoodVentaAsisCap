-- Devolucion de pagares: responsables de Cobranza y custodia guiada.
-- Compatible con MySQL 5.7+/8 y consumidores PHP 7.2.
-- Cambio aditivo e idempotente: conserva solicitudes, eventos y documentos existentes.

SET NAMES latin1;
SET SESSION lock_wait_timeout = 15;

SET @clpg_tiene_hilo := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE() AND table_name='centro_legajo_pagare_solicitud'
    AND column_name='cod_interConsultaFK'
);
SET @clpg_sql_hilo := IF(
  @clpg_tiene_hilo=0,
  'ALTER TABLE centro_legajo_pagare_solicitud ADD COLUMN cod_interConsultaFK int DEFAULT NULL AFTER cod_ventaFK',
  'SELECT ''cod_interConsultaFK ya disponible'' AS info'
);
PREPARE clpg_stmt FROM @clpg_sql_hilo;
EXECUTE clpg_stmt;
DEALLOCATE PREPARE clpg_stmt;

SET @clpg_tiene_idx_hilo := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema=DATABASE() AND table_name='centro_legajo_pagare_solicitud'
    AND index_name='idx_clps_hilo_fecha'
);
SET @clpg_sql_idx_hilo := IF(
  @clpg_tiene_idx_hilo=0,
  'ALTER TABLE centro_legajo_pagare_solicitud ADD KEY idx_clps_hilo_fecha (cod_interConsultaFK,fecha_solicitud,id_solicitud)',
  'SELECT ''idx_clps_hilo_fecha ya disponible'' AS info'
);
PREPARE clpg_stmt FROM @clpg_sql_idx_hilo;
EXECUTE clpg_stmt;
DEALLOCATE PREPARE clpg_stmt;

CREATE TABLE IF NOT EXISTS `centro_legajo_pagare_responsable_cobranza` (
  `cod_usuarioFK` int NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `cod_usuario_configuraFK` int NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_usuarioFK`),
  KEY `idx_clprc_estado_usuario` (`estado`,`cod_usuarioFK`),
  KEY `idx_clprc_configura` (`cod_usuario_configuraFK`,`fecha_actualizacion`),
  CONSTRAINT `fk_clprc_usuario` FOREIGN KEY (`cod_usuarioFK`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_clprc_configura` FOREIGN KEY (`cod_usuario_configuraFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `centro_legajo_pagare_responsable_evento` (
  `id_evento` bigint NOT NULL AUTO_INCREMENT,
  `cod_usuario_responsableFK` int NOT NULL,
  `estado_anterior` varchar(15) DEFAULT NULL,
  `estado_nuevo` varchar(15) NOT NULL,
  `cod_usuario_actorFK` int NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evento`),
  KEY `idx_clpre_responsable_fecha` (`cod_usuario_responsableFK`,`fecha_hora`,`id_evento`),
  KEY `idx_clpre_actor_fecha` (`cod_usuario_actorFK`,`fecha_hora`),
  CONSTRAINT `fk_clpre_responsable` FOREIGN KEY (`cod_usuario_responsableFK`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_clpre_actor` FOREIGN KEY (`cod_usuario_actorFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Primera instalacion: conserva como responsables iniciales a quienes ya podian
-- resolver solicitudes por ADMINCENTROFACTURAS. Las ejecuciones posteriores no
-- reactivan usuarios que un administrador haya desmarcado.
SET @clprc_configurados := (SELECT COUNT(*) FROM centro_legajo_pagare_responsable_cobranza);

INSERT INTO centro_legajo_pagare_responsable_cobranza
  (cod_usuarioFK,estado,cod_usuario_configuraFK,fecha_creacion,fecha_actualizacion)
SELECT DISTINCT u.cod_usuario,'activo',u.cod_usuario,NOW(),NOW()
FROM usuario u
INNER JOIN accesosuser au ON au.usuarios_idusario=u.cod_usuario AND au.accion='SI'
INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK
WHERE @clprc_configurados=0
  AND u.estado='Activo'
  AND la.codigo='ADMINCENTROFACTURAS'
  AND EXISTS (
    SELECT 1
    FROM accesosuser auv
    INNER JOIN listadodeacceso lav ON lav.idlistadodeacceso=auv.idlistadodeaccesoFK
    WHERE auv.usuarios_idusario=u.cod_usuario AND auv.accion='SI'
      AND lav.codigo='VERCENTROFACTURAS'
  );

SELECT
  (SELECT COUNT(*) FROM centro_legajo_pagare_responsable_cobranza WHERE estado='activo') AS responsables_cobranza_activos,
  (SELECT COUNT(*) FROM centro_legajo_pagare_responsable_evento) AS eventos_configuracion_conservados,
  (SELECT COUNT(*) FROM centro_legajo_pagare_solicitud) AS solicitudes_conservadas,
  (SELECT COUNT(*) FROM centro_legajo_pagare_solicitud_evento) AS eventos_solicitud_conservados;

-- Reversion controlada:
-- 1. Ocultar el engranaje y volver a la autorizacion ADMINCENTROFACTURAS.
-- 2. Exportar ambas tablas si se necesita conservar el historial de configuracion.
-- 3. DROP TABLE centro_legajo_pagare_responsable_evento;
-- 4. DROP TABLE centro_legajo_pagare_responsable_cobranza;
