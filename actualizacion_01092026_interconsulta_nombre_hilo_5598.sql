-- Corrige el hilo automatico 5598, creado despues de la migracion historica
-- y antes de publicar el alias nombre_persona en el generador.

START TRANSACTION;

INSERT IGNORE INTO interconsulta_paciente_nombre_backup_20260901
    (id,cod_interConsultaFK,nombre_paciente_snapshot_anterior,asunto_anterior,fecha_backup)
SELECT ip.id,ip.cod_interConsultaFK,ip.nombre_paciente_snapshot,ic.asunto,NOW()
FROM interconsulta_paciente ip
INNER JOIN interconsulta ic ON ic.cod_interConsulta=ip.cod_interConsultaFK
INNER JOIN persona p ON p.cod_persona=ip.cod_clienteFK_principal
WHERE ip.id=4892
  AND ip.cod_interConsultaFK=5598
  AND LENGTH(TRIM(CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)))>0;

UPDATE interconsulta_paciente ip
INNER JOIN persona p ON p.cod_persona=ip.cod_clienteFK_principal
SET ip.nombre_paciente_snapshot=TRIM(CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)),
    ip.fecha_actualizacion=NOW()
WHERE ip.id=4892
  AND ip.cod_interConsultaFK=5598
  AND LENGTH(TRIM(CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)))>0;

UPDATE interconsulta ic
INNER JOIN interconsulta_paciente ip ON ip.cod_interConsultaFK=ic.cod_interConsulta
SET ic.asunto=LEFT(CONCAT(ip.nombre_paciente_snapshot,' - CI ',ip.cedula),180),
    ic.fecha_edit=NOW()
WHERE ic.cod_interConsulta=5598
  AND ip.id=4892
  AND LENGTH(TRIM(IFNULL(ip.nombre_paciente_snapshot,'')))>0;

COMMIT;

SELECT
    ic.cod_interConsulta,
    CHAR_LENGTH(TRIM(IFNULL(ip.nombre_paciente_snapshot,''))) AS largo_nombre,
    IF(ic.asunto LIKE 'Paciente sin nombre%',1,0) AS conserva_marcador
FROM interconsulta ic
INNER JOIN interconsulta_paciente ip ON ip.cod_interConsultaFK=ic.cod_interConsulta
WHERE ic.cod_interConsulta=5598 AND ip.id=4892;
