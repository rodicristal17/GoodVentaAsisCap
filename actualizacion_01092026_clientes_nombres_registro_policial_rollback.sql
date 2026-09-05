-- Revierte exclusivamente la correccion de nombres basada en registro_policial.

START TRANSACTION;

UPDATE persona p
INNER JOIN persona_nombre_policial_backup_20260901 b ON b.cod_persona=p.cod_persona
SET p.nombre_persona=b.nombre_anterior,
    p.apellido_persona=b.apellido_anterior;

UPDATE interconsulta_paciente ip
INNER JOIN interconsulta_nombre_policial_backup_20260901 b
        ON b.id_interconsulta_paciente=ip.id
SET ip.nombre_paciente_snapshot=b.nombre_snapshot_anterior,
    ip.fecha_actualizacion=NOW();

UPDATE interconsulta ic
INNER JOIN interconsulta_nombre_policial_backup_20260901 b
        ON b.cod_interConsultaFK=ic.cod_interConsulta
SET ic.asunto=b.asunto_anterior,
    ic.fecha_edit=NOW();

COMMIT;
