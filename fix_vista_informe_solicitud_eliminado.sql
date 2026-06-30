DROP VIEW IF EXISTS vista_informe_solicitud_eliminado;

CREATE VIEW vista_informe_solicitud_eliminado AS
SELECT
    se.id_solicitud_eliminado AS id_solicitud_eliminado,
    se.id_usuario_solicitud AS id_usuario_solicitud,
    ps.nombre_persona AS usuario_solicitud,
    se.fecha_solicitud AS fecha_solicitud,
    se.tabla_nombre AS tabla_nombre,
    se.registro_pk_columna AS registro_pk_columna,
    se.registro_pk_valor AS registro_pk_valor,
    se.estado_columna AS estado_columna,
    se.registro_resumen AS registro_resumen,
    (
        SELECT COUNT(0)
        FROM solicitud_eliminado_detalle sed
        WHERE sed.id_solicitud_eliminado = se.id_solicitud_eliminado
    ) AS total_registros_relacionados,
    se.motivo AS motivo,
    se.estado AS estado,
    se.fecha_aprobacion AS fecha_aprobacion,
    se.id_usuario_aprobacion AS id_usuario_aprobacion,
    pa.nombre_persona AS usuario_aprobacion,
    se.observacion_aprobacion AS observacion_aprobacion
FROM solicitud_eliminado se
LEFT JOIN persona ps ON ps.cod_persona = se.id_usuario_solicitud
LEFT JOIN persona pa ON pa.cod_persona = se.id_usuario_aprobacion;
