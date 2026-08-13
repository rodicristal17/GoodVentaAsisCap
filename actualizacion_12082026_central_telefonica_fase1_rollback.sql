-- Reversion conservadora de Central Telefonica Fase 1.
-- Deshabilita el acceso y conserva tablas, auditoria y CDR ya sincronizados.
-- No modifica Issabel/Asterisk.

SET NAMES utf8mb4;
START TRANSACTION;

UPDATE dashboard_access_catalog
SET is_active=0,is_default_quick_access=0,updated_at=CURRENT_TIMESTAMP
WHERE access_key='central_telefonica';

UPDATE dashboard_user_shortcuts us
INNER JOIN dashboard_access_catalog c ON c.id=us.access_id
SET us.is_visible=0,us.updated_at=CURRENT_TIMESTAMP
WHERE c.access_key='central_telefonica';

UPDATE accesosuser au
INNER JOIN listadodeacceso la
  ON la.idlistadodeacceso=au.idlistadodeaccesoFK
SET au.accion='NO'
WHERE la.codigo IN (
  'VERCENTRALTELEFONICA',
  'VERTELEFONOSCOMPLETOSCENTRALTELEFONICA',
  'VERDATOSTECNICOSCENTRALTELEFONICA',
  'ESCUCHARGRABACIONCENTRALTELEFONICA'
);

UPDATE detallesniveles dn
INNER JOIN listadodeacceso la
  ON la.idlistadodeacceso=dn.idlistadodeacceso
SET dn.accion='NO'
WHERE la.codigo IN (
  'VERCENTRALTELEFONICA',
  'VERTELEFONOSCOMPLETOSCENTRALTELEFONICA',
  'VERDATOSTECNICOSCENTRALTELEFONICA',
  'ESCUCHARGRABACIONCENTRALTELEFONICA'
);

COMMIT;

-- Las tablas central_telefonica_* se conservan intencionalmente para que la
-- reversion no destruya historico ni trazabilidad.
