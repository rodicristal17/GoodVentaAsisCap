-- Reversion de GoHighLevel fase 1.
-- No afecta contactos, automatizaciones ni configuraciones en HighLevel.

SET NAMES utf8mb4;

DELETE s
FROM dashboard_user_shortcuts s
INNER JOIN dashboard_access_catalog c ON c.id=s.access_id
WHERE c.access_key='gohighlevel';

DELETE FROM dashboard_access_catalog WHERE access_key='gohighlevel';

DROP TABLE IF EXISTS gohighlevel_evento;
DROP TABLE IF EXISTS gohighlevel_vinculo_contacto;
DROP TABLE IF EXISTS gohighlevel_permiso_usuario;
