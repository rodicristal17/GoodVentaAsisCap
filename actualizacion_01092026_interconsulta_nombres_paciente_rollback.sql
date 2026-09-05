-- Restaura exactamente los snapshots y asuntos respaldados por la actualizacion.

UPDATE interconsulta_paciente ip
INNER JOIN interconsulta_paciente_nombre_backup_20260901 b ON b.id=ip.id
SET ip.nombre_paciente_snapshot=b.nombre_paciente_snapshot_anterior;

UPDATE interconsulta ic
INNER JOIN interconsulta_paciente_nombre_backup_20260901 b ON b.cod_interConsultaFK=ic.cod_interConsulta
SET ic.asunto=b.asunto_anterior;

SELECT COUNT(*) AS restaurados
FROM interconsulta_paciente_nombre_backup_20260901;
