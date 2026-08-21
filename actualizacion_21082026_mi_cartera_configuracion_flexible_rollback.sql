-- Reversion conservadora de la configuracion flexible de Mi cartera.
-- No elimina equipos, asignaciones, gestiones, promesas ni auditorias.
-- Solo devuelve a 30 dias el valor inicial que siga exactamente en 90.

SET NAMES utf8mb4;

ALTER TABLE cartera_configuracion
  MODIFY dias_escalamiento SMALLINT UNSIGNED NOT NULL DEFAULT 30;

SET @cartera_revertir_regla_90 := (
  SELECT IF(
    cc.dias_escalamiento=90
    AND (SELECT COUNT(*) FROM cartera_evento WHERE tipo_evento='migracion_regla_90')
      > (SELECT COUNT(*) FROM cartera_evento WHERE tipo_evento='rollback_regla_90'),
    1,
    0
  )
  FROM cartera_configuracion cc
  WHERE cc.id_configuracion=1
);

UPDATE cartera_configuracion
SET dias_escalamiento=30,
    fecha_actualizacion=NOW()
WHERE id_configuracion=1
  AND @cartera_revertir_regla_90=1;

INSERT INTO cartera_evento
  (cod_clienteFK,id_asignacionFK,cod_usuario_actorFK,tipo_evento,detalle,
   datos_anteriores,datos_nuevos,fecha_evento)
SELECT NULL,NULL,0,'rollback_regla_90',
  'La reversion restauro 30 dias como plazo inicial de Cobranza central.',
  '{"dias_escalamiento":90}',
  '{"dias_escalamiento":30}',
  NOW()
WHERE @cartera_revertir_regla_90=1;

SELECT id_configuracion,dias_prevencion,dias_escalamiento,intentos_escalamiento,activo
FROM cartera_configuracion
WHERE id_configuracion=1;

SELECT tipo_evento,COUNT(*) total
FROM cartera_evento
WHERE tipo_evento IN ('migracion_regla_90','rollback_regla_90')
GROUP BY tipo_evento;
