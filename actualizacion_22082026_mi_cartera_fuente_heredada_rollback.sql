-- Reversion conservadora de la separacion de cartera heredada.
-- No borra gestiones, promesas ni eventos. Reactiva solamente las asignaciones
-- archivadas por esta migracion que continuen con el mismo motivo.

SET NAMES utf8mb4;

INSERT INTO cartera_evento
  (cod_clienteFK,id_asignacionFK,cod_usuario_actorFK,tipo_evento,detalle,
   datos_anteriores,datos_nuevos,fecha_evento)
SELECT ca.cod_clienteFK,ca.id_asignacion,IFNULL(ca.cod_usuario_asignaFK,0),
  'rollback_fuente_heredada',
  'La reversion reactivo una asignacion archivada por la separacion de cartera heredada.',
  '{"estado":"inactiva","motivo":"fuente_heredada_archivada"}',
  '{"estado":"activa","motivo":"rollback_fuente_heredada"}',
  NOW()
FROM cartera_asignacion ca
WHERE ca.estado='inactiva'
  AND ca.motivo_asignacion='fuente_heredada_archivada';

UPDATE cartera_asignacion
SET estado='activa',
    motivo_asignacion='rollback_fuente_heredada',
    fecha_actualizacion=NOW()
WHERE estado='inactiva'
  AND motivo_asignacion='fuente_heredada_archivada';

DROP TABLE IF EXISTS cartera_fuente_heredada;

SELECT COUNT(*) AS asignaciones_reactivadas
FROM cartera_evento
WHERE tipo_evento='rollback_fuente_heredada';
