-- Renombre visible del Centro de Facturas y Documentos.
-- No cambia claves tecnicas, permisos, IDs ni asignaciones existentes.
-- Migracion idempotente y compatible con MySQL 5.7+/8.
-- Debe ejecutarse despues de actualizacion_13072026_centro_facturas.sql y
-- actualizacion_16072026_legajos_documentos_venta.sql.
-- No reejecutar luego esas migraciones anteriores: restauran la etiqueta antigua y
-- sus reparaciones historicas filtran literalmente el nombre anterior del formulario.

SET NAMES latin1;

START TRANSACTION;

UPDATE `dashboard_access_catalog`
SET `label`='Centro de Facturas y Documentos'
WHERE `access_key`='centro_facturas'
  AND COALESCE(`label`,'')<>'Centro de Facturas y Documentos';

UPDATE `listadodeacceso`
SET `formulario`='CENTRO DE FACTURAS Y DOCUMENTOS'
WHERE `tipo`='Administrativo'
  AND `codigo` IN (
    'VERCENTROFACTURAS','VERCENTROFACTURASTODOSLOCALES',
    'REGISTRARFACTURAHILO','REGISTRARFACTURAMANUAL','VINCULARPAGOFACTURA',
    'ENVIARORIGINALFACTURA','RECIBIRORIGINALFACTURA','GESTIONARLOTESFACTURAS',
    'ADMINCENTROFACTURAS','VERLEGAJOSVENTA','GESTIONARLEGAJOSVENTA',
    'GESTIONARLOTESLEGAJOS','ENVIARLOTELEGAJOS','RECIBIRLOTELEGAJOS'
  )
  AND COALESCE(`formulario`,'')<>'CENTRO DE FACTURAS Y DOCUMENTOS';

UPDATE `listadodeacceso`
SET `nombre`=CASE `codigo`
  WHEN 'VERCENTROFACTURAS' THEN 'Ver Centro de Facturas y Documentos'
  WHEN 'VERCENTROFACTURASTODOSLOCALES' THEN 'Ver facturas y documentos de todos los locales'
  WHEN 'ADMINCENTROFACTURAS' THEN 'Administrar el Centro de Facturas y Documentos'
  ELSE `nombre`
END
WHERE `tipo`='Administrativo'
  AND `codigo` IN ('VERCENTROFACTURAS','VERCENTROFACTURASTODOSLOCALES','ADMINCENTROFACTURAS');

COMMIT;

SELECT
  (SELECT COUNT(*) FROM `dashboard_access_catalog`
   WHERE `access_key`='centro_facturas' AND `label`='Centro de Facturas y Documentos') AS acceso_renombrado,
  (SELECT COUNT(*) FROM `listadodeacceso`
   WHERE `tipo`='Administrativo' AND `formulario`='CENTRO DE FACTURAS Y DOCUMENTOS'
     AND `codigo` IN (
       'VERCENTROFACTURAS','VERCENTROFACTURASTODOSLOCALES',
       'REGISTRARFACTURAHILO','REGISTRARFACTURAMANUAL','VINCULARPAGOFACTURA',
       'ENVIARORIGINALFACTURA','RECIBIRORIGINALFACTURA','GESTIONARLOTESFACTURAS',
       'ADMINCENTROFACTURAS','VERLEGAJOSVENTA','GESTIONARLEGAJOSVENTA',
       'GESTIONARLOTESLEGAJOS','ENVIARLOTELEGAJOS','RECIBIRLOTELEGAJOS'
     )) AS permisos_renombrados;

-- Reversion visible, si fuera necesaria, sin cambiar IDs ni asignaciones:
-- UPDATE dashboard_access_catalog SET label='Centro de Facturas' WHERE access_key='centro_facturas';
-- UPDATE listadodeacceso SET formulario='CENTRO DE FACTURAS'
-- WHERE tipo='Administrativo' AND formulario='CENTRO DE FACTURAS Y DOCUMENTOS'
--   AND codigo IN (
--     'VERCENTROFACTURAS','VERCENTROFACTURASTODOSLOCALES',
--     'REGISTRARFACTURAHILO','REGISTRARFACTURAMANUAL','VINCULARPAGOFACTURA',
--     'ENVIARORIGINALFACTURA','RECIBIRORIGINALFACTURA','GESTIONARLOTESFACTURAS',
--     'ADMINCENTROFACTURAS','VERLEGAJOSVENTA','GESTIONARLEGAJOSVENTA',
--     'GESTIONARLOTESLEGAJOS','ENVIARLOTELEGAJOS','RECIBIRLOTELEGAJOS'
--   );
-- UPDATE listadodeacceso SET nombre='Ver Centro de Facturas' WHERE codigo='VERCENTROFACTURAS' AND tipo='Administrativo';
-- UPDATE listadodeacceso SET nombre='Ver facturas de todos los locales' WHERE codigo='VERCENTROFACTURASTODOSLOCALES' AND tipo='Administrativo';
-- UPDATE listadodeacceso SET nombre='Administrar, corregir y revertir facturas' WHERE codigo='ADMINCENTROFACTURAS' AND tipo='Administrativo';
