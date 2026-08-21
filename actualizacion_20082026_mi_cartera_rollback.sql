-- Reversion segura de Mi cartera.
-- No se eliminan tablas ni eventos para conservar la trazabilidad.

UPDATE cartera_configuracion
SET activo=0,fecha_actualizacion=NOW()
WHERE id_configuracion=1;

UPDATE cartera_equipo
SET activo=0,fecha_actualizacion=NOW()
WHERE activo=1;

UPDATE cartera_asignacion
SET estado='pausada',fecha_actualizacion=NOW()
WHERE estado='activa';

UPDATE dashboard_access_catalog
SET is_active=0,updated_at=CURRENT_TIMESTAMP
WHERE access_key='mi_cartera';

UPDATE dashboard_user_shortcuts us
INNER JOIN dashboard_access_catalog c ON c.id=us.access_id
SET us.is_visible=0,us.updated_at=CURRENT_TIMESTAMP
WHERE c.access_key='mi_cartera';

SELECT id_configuracion,activo,fecha_actualizacion
FROM cartera_configuracion
WHERE id_configuracion=1;
