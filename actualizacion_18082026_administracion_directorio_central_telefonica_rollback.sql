-- Reversion conservadora del administrador de directorio telefonico.
-- Revoca el permiso, pero conserva las asociaciones y su auditoria.

START TRANSACTION;

SET @central_id_administrar_directorio := (
  SELECT idlistadodeacceso FROM listadodeacceso
  WHERE codigo='ADMINISTRARDIRECTORIOCENTRALTELEFONICA' AND tipo='Administrativo'
  ORDER BY idlistadodeacceso LIMIT 1
);

UPDATE accesosuser
SET accion='NO'
WHERE idlistadodeaccesoFK=@central_id_administrar_directorio
  AND tipo='Administrativo';

UPDATE detallesniveles
SET accion='NO'
WHERE idlistadodeacceso=@central_id_administrar_directorio;

COMMIT;
