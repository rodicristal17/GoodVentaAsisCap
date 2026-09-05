-- Completa exclusivamente hilos que conservan el marcador "Paciente sin nombre"
-- aunque el cliente principal tenga nombre y apellido en persona.

CREATE TABLE IF NOT EXISTS interconsulta_paciente_nombre_backup_20260901 (
    id INT NOT NULL,
    cod_interConsultaFK INT NOT NULL,
    nombre_paciente_snapshot_anterior VARCHAR(150) NULL,
    asunto_anterior VARCHAR(180) NULL,
    fecha_backup DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_ip_nombre_backup_hilo (cod_interConsultaFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT IGNORE INTO interconsulta_paciente_nombre_backup_20260901
    (id,cod_interConsultaFK,nombre_paciente_snapshot_anterior,asunto_anterior,fecha_backup)
SELECT ip.id,ip.cod_interConsultaFK,ip.nombre_paciente_snapshot,ic.asunto,NOW()
FROM interconsulta_paciente ip
INNER JOIN interconsulta ic ON ic.cod_interConsulta=ip.cod_interConsultaFK
INNER JOIN persona p ON p.cod_persona=ip.cod_clienteFK_principal
WHERE ip.estado='activo'
  AND ic.asunto LIKE 'Paciente sin nombre%'
  AND LENGTH(TRIM(CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)))>0;

UPDATE interconsulta_paciente ip
INNER JOIN interconsulta_paciente_nombre_backup_20260901 b ON b.id=ip.id
INNER JOIN persona p ON p.cod_persona=ip.cod_clienteFK_principal
SET ip.nombre_paciente_snapshot=TRIM(CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)),
    ip.fecha_actualizacion=NOW()
WHERE LENGTH(TRIM(CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)))>0;

UPDATE interconsulta ic
INNER JOIN interconsulta_paciente_nombre_backup_20260901 b ON b.cod_interConsultaFK=ic.cod_interConsulta
INNER JOIN interconsulta_paciente ip ON ip.id=b.id
SET ic.asunto=LEFT(CONCAT(ip.nombre_paciente_snapshot,' - CI ',ip.cedula),180),
    ic.fecha_edit=NOW()
WHERE ic.asunto LIKE 'Paciente sin nombre%';

SELECT COUNT(*) AS respaldados
FROM interconsulta_paciente_nombre_backup_20260901;

SELECT COUNT(*) AS pendientes_con_nombre_real
FROM interconsulta ic
INNER JOIN interconsulta_paciente ip ON ip.cod_interConsultaFK=ic.cod_interConsulta AND ip.estado='activo'
INNER JOIN persona p ON p.cod_persona=ip.cod_clienteFK_principal
WHERE ic.asunto LIKE 'Paciente sin nombre%'
  AND LENGTH(TRIM(CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)))>0;
