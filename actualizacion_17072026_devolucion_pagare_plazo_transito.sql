-- Devolucion de pagares vinculada a Hilos y plazo visible de documentos en transito.
-- Compatible con MySQL 5.7+/8 y consumidores PHP 7.2.
-- Migracion aditiva e idempotente: no completa ni altera plazos historicos.

SET NAMES latin1;
SET SESSION lock_wait_timeout = 15;

DROP PROCEDURE IF EXISTS migrar_documentos_transito_17072026;

DELIMITER $$

CREATE PROCEDURE migrar_documentos_transito_17072026()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='centro_legajo_pagare_solicitud'
      AND column_name='cod_interConsultaFK'
  ) THEN
    ALTER TABLE centro_legajo_pagare_solicitud
      ADD COLUMN cod_interConsultaFK int DEFAULT NULL AFTER cod_ventaFK;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema=DATABASE() AND table_name='centro_legajo_pagare_solicitud'
      AND index_name='idx_clps_hilo_fecha'
  ) THEN
    ALTER TABLE centro_legajo_pagare_solicitud
      ADD KEY idx_clps_hilo_fecha (cod_interConsultaFK,fecha_solicitud,id_solicitud);
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='centro_legajo_lote'
      AND column_name='dias_plazo_transito'
  ) THEN
    ALTER TABLE centro_legajo_lote
      ADD COLUMN dias_plazo_transito tinyint unsigned DEFAULT NULL AFTER fecha_envio;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='centro_legajo_lote'
      AND column_name='fecha_limite_recepcion'
  ) THEN
    ALTER TABLE centro_legajo_lote
      ADD COLUMN fecha_limite_recepcion datetime DEFAULT NULL AFTER dias_plazo_transito;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='centro_legajo_lote'
      AND column_name='cod_usuario_plazo_transitoFK'
  ) THEN
    ALTER TABLE centro_legajo_lote
      ADD COLUMN cod_usuario_plazo_transitoFK int DEFAULT NULL AFTER fecha_limite_recepcion;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='centro_legajo_lote'
      AND column_name='fecha_asignacion_plazo'
  ) THEN
    ALTER TABLE centro_legajo_lote
      ADD COLUMN fecha_asignacion_plazo datetime DEFAULT NULL AFTER cod_usuario_plazo_transitoFK;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema=DATABASE() AND table_name='centro_legajo_lote'
      AND index_name='idx_cll_transito_plazo'
  ) THEN
    ALTER TABLE centro_legajo_lote
      ADD KEY idx_cll_transito_plazo (estado,fecha_limite_recepcion,id_lote);
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='centro_factura_lote'
      AND column_name='dias_plazo_transito'
  ) THEN
    ALTER TABLE centro_factura_lote
      ADD COLUMN dias_plazo_transito tinyint unsigned DEFAULT NULL AFTER fecha_envio;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='centro_factura_lote'
      AND column_name='fecha_limite_recepcion'
  ) THEN
    ALTER TABLE centro_factura_lote
      ADD COLUMN fecha_limite_recepcion datetime DEFAULT NULL AFTER dias_plazo_transito;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='centro_factura_lote'
      AND column_name='cod_usuario_plazo_transitoFK'
  ) THEN
    ALTER TABLE centro_factura_lote
      ADD COLUMN cod_usuario_plazo_transitoFK int DEFAULT NULL AFTER fecha_limite_recepcion;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='centro_factura_lote'
      AND column_name='fecha_asignacion_plazo'
  ) THEN
    ALTER TABLE centro_factura_lote
      ADD COLUMN fecha_asignacion_plazo datetime DEFAULT NULL AFTER cod_usuario_plazo_transitoFK;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema=DATABASE() AND table_name='centro_factura_lote'
      AND index_name='idx_cfl_transito_plazo'
  ) THEN
    ALTER TABLE centro_factura_lote
      ADD KEY idx_cfl_transito_plazo (estado,fecha_limite_recepcion,id_lote);
  END IF;
END$$

DELIMITER ;

CALL migrar_documentos_transito_17072026();
DROP PROCEDURE IF EXISTS migrar_documentos_transito_17072026;

-- Acceso directo exclusivo. La autorizacion real sigue dependiendo de
-- GESTIONARLEGAJOSVENTA y de las validaciones del local en el servidor.
SET @dp_catalogo_existe := (
  SELECT COUNT(*) FROM dashboard_access_catalog
  WHERE access_key='devolucion_pagare'
);

UPDATE dashboard_access_catalog
SET default_quick_order=default_quick_order+1
WHERE @dp_catalogo_existe=0
  AND is_default_quick_access=1
  AND default_quick_order>=19;

INSERT INTO dashboard_access_catalog
  (access_key,label,module_key,module_label,icon_key,route_path,permission_key,
   is_active,is_default_quick_access,default_quick_order)
VALUES
  ('devolucion_pagare','Devolucion de pagare','administrativo','Administrativo',
   'devolucion-pagare',NULL,'GESTIONARLEGAJOSVENTA',1,1,19)
ON DUPLICATE KEY UPDATE
  label=VALUES(label),
  module_key=VALUES(module_key),
  module_label=VALUES(module_label),
  icon_key=VALUES(icon_key),
  route_path=VALUES(route_path),
  permission_key=VALUES(permission_key),
  is_active=VALUES(is_active),
  is_default_quick_access=VALUES(is_default_quick_access),
  default_quick_order=VALUES(default_quick_order),
  updated_at=CURRENT_TIMESTAMP;

-- Conserva personalizaciones existentes y agrega el acceso solo cuando hay lugar.
INSERT INTO dashboard_user_shortcuts (user_id,access_id,shortcut_order,is_visible)
SELECT configurados.user_id,catalogo.id,COALESCE(MAX(actuales.shortcut_order),0)+1,1
FROM (SELECT DISTINCT user_id FROM dashboard_user_shortcuts) configurados
INNER JOIN dashboard_access_catalog catalogo ON catalogo.access_key='devolucion_pagare'
LEFT JOIN dashboard_user_shortcuts actuales
  ON actuales.user_id=configurados.user_id AND actuales.is_visible=1
GROUP BY configurados.user_id,catalogo.id
HAVING SUM(CASE WHEN actuales.is_visible=1 THEN 1 ELSE 0 END)<20
ON DUPLICATE KEY UPDATE is_visible=1,updated_at=CURRENT_TIMESTAMP;

SELECT
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema=DATABASE() AND table_name='centro_legajo_pagare_solicitud'
     AND column_name='cod_interConsultaFK') AS vinculo_hilo_disponible,
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema=DATABASE() AND table_name IN ('centro_legajo_lote','centro_factura_lote')
     AND column_name IN ('dias_plazo_transito','fecha_limite_recepcion',
                         'cod_usuario_plazo_transitoFK','fecha_asignacion_plazo')) AS columnas_plazo_disponibles,
  (SELECT COUNT(*) FROM dashboard_access_catalog
   WHERE access_key='devolucion_pagare' AND is_active=1) AS acceso_dashboard_disponible;

-- Reversion controlada (no ejecutar sin revisar primero los registros nuevos):
-- 1. Ocultar el acceso y bloquear nuevos envios/solicitudes desde la aplicacion.
-- 2. Exportar solicitudes y lotes que ya contengan los nuevos campos.
-- 3. DELETE us FROM dashboard_user_shortcuts us INNER JOIN dashboard_access_catalog c
--      ON c.id=us.access_id WHERE c.access_key='devolucion_pagare';
-- 4. DELETE FROM dashboard_access_catalog WHERE access_key='devolucion_pagare';
-- 5. ALTER TABLE centro_legajo_pagare_solicitud DROP INDEX idx_clps_hilo_fecha,
--      DROP COLUMN cod_interConsultaFK;
-- 6. ALTER TABLE centro_legajo_lote DROP INDEX idx_cll_transito_plazo,
--      DROP COLUMN fecha_asignacion_plazo,DROP COLUMN cod_usuario_plazo_transitoFK,
--      DROP COLUMN fecha_limite_recepcion,DROP COLUMN dias_plazo_transito;
-- 7. Repetir el paso 6 en centro_factura_lote con idx_cfl_transito_plazo.
