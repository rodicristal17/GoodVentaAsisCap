-- Corrige exclusivamente reposiciones vinculadas cuyo mensaje inicial quedo
-- entre 2 y 4 horas adelantado respecto del reloj de MySQL.
-- Es idempotente: al igualar el mensaje inicial con fecha_creacion, una nueva
-- ejecucion ya no encuentra el desfase requerido.
START TRANSACTION;

UPDATE movimientos_insumos mi
INNER JOIN reposiciones_insumos r ON r.codigo=mi.grupo_movimiento
INNER JOIN mensaje origen ON origen.cod_mensaje=r.cod_mensajeFK
SET mi.fecha=DATE_SUB(
    mi.fecha,
    INTERVAL TIMESTAMPDIFF(SECOND, r.fecha_creacion, origen.fecha_creacion) SECOND
)
WHERE r.cod_interConsultaFK IS NOT NULL
  AND TIMESTAMPDIFF(SECOND, r.fecha_creacion, origen.fecha_creacion) BETWEEN 7200 AND 14400;

UPDATE mensaje confirmacion
INNER JOIN reposiciones_insumos r
    ON confirmacion.cod_interConsultaFK=r.cod_interConsultaFK
   AND confirmacion.contenido LIKE CONCAT('Recepcion de insumos ', r.codigo, ' confirmada por %')
INNER JOIN mensaje origen ON origen.cod_mensaje=r.cod_mensajeFK
SET confirmacion.fecha_creacion=DATE_SUB(
    confirmacion.fecha_creacion,
    INTERVAL TIMESTAMPDIFF(SECOND, r.fecha_creacion, origen.fecha_creacion) SECOND
)
WHERE TIMESTAMPDIFF(SECOND, r.fecha_creacion, origen.fecha_creacion) BETWEEN 7200 AND 14400;

UPDATE reposiciones_insumos r
INNER JOIN mensaje origen ON origen.cod_mensaje=r.cod_mensajeFK
SET r.fecha_recepcion=DATE_SUB(
    r.fecha_recepcion,
    INTERVAL TIMESTAMPDIFF(SECOND, r.fecha_creacion, origen.fecha_creacion) SECOND
)
WHERE r.cod_interConsultaFK IS NOT NULL
  AND r.fecha_recepcion IS NOT NULL
  AND TIMESTAMPDIFF(SECOND, r.fecha_creacion, origen.fecha_creacion) BETWEEN 7200 AND 14400;

UPDATE mensaje origen
INNER JOIN reposiciones_insumos r ON r.cod_mensajeFK=origen.cod_mensaje
SET origen.fecha_creacion=r.fecha_creacion
WHERE r.cod_interConsultaFK IS NOT NULL
  AND TIMESTAMPDIFF(SECOND, r.fecha_creacion, origen.fecha_creacion) BETWEEN 7200 AND 14400;

COMMIT;
