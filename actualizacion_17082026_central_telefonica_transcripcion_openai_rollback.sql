-- Reversion conservadora de la transcripcion bajo demanda.
-- Revoca la accion y detiene logicamente el servicio, pero conserva todas las
-- transcripciones y eventos para no destruir trazabilidad.

START TRANSACTION;

SET @central_id_transcribir := (
  SELECT idlistadodeacceso FROM listadodeacceso
  WHERE codigo='TRANSCRIBIRLLAMADACENTRALTELEFONICA' AND tipo='Administrativo'
  ORDER BY idlistadodeacceso LIMIT 1
);

UPDATE accesosuser
SET accion='NO'
WHERE idlistadodeaccesoFK=@central_id_transcribir
  AND tipo='Administrativo';

UPDATE detallesniveles
SET accion='NO'
WHERE idlistadodeacceso=@central_id_transcribir;

UPDATE central_telefonica_transcripcion_servicio
SET estado='deshabilitado',codigo_error='rollback_aplicado',ultima_actividad=NOW()
WHERE id_servicio=1;

COMMIT;
