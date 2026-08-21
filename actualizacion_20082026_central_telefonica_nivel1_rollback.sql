-- Reversion segura del Nivel 1 de Central Telefonica.
-- Se deshabilita la operacion en vivo y se restaura la proteccion del acceso.
-- Las tablas y su auditoria se conservan para no perder trazabilidad.

UPDATE central_telefonica_operacion_servicio
SET estado='deshabilitado',mensaje='Conector telefonico deshabilitado por reversion.',
    evento_conectado=0,origenacion_disponible=0,fecha_actualizacion=NOW()
WHERE id_servicio=1;

UPDATE dashboard_access_catalog
SET permission_key='VERCENTRALTELEFONICA',updated_at=CURRENT_TIMESTAMP
WHERE access_key='central_telefonica';

SELECT estado,mensaje
FROM central_telefonica_operacion_servicio
WHERE id_servicio=1;
