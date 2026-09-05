-- Corrige nombre y apellido de diez clientes con ventas vigentes.
-- Fuente autorizada: registro_policial local, cruce exacto por numero de cedula.
-- Los valores personales se conservan codificados en hexadecimal.

START TRANSACTION;

CREATE TEMPORARY TABLE tmp_clientes_nombre_policial (
    cod_cliente INT NOT NULL,
    ci INT UNSIGNED NOT NULL,
    nombre_nuevo VARCHAR(255) NOT NULL,
    apellido_nuevo VARCHAR(255) NOT NULL,
    PRIMARY KEY (cod_cliente),
    UNIQUE KEY uq_tmp_cliente_ci (ci)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_clientes_nombre_policial
    (cod_cliente,ci,nombre_nuevo,apellido_nuevo)
VALUES
    (7589,5882767,CONVERT(UNHEX('434553415220414E44524553') USING utf8mb4),CONVERT(UNHEX('5645524120414355') USING utf8mb4)),
    (9441,2397432,CONVERT(UNHEX('43454C534F') USING utf8mb4),CONVERT(UNHEX('474152434941204159414C41') USING utf8mb4)),
    (17489,4461099,CONVERT(UNHEX('4D49524E41') USING utf8mb4),CONVERT(UNHEX('43414252414C20474F4E5A414C455A') USING utf8mb4)),
    (17570,7288915,CONVERT(UNHEX('4D4152495A41') USING utf8mb4),CONVERT(UNHEX('4F525545204645525245495241') USING utf8mb4)),
    (19585,651637,CONVERT(UNHEX('41524D414E444F204A554C494F204345534152') USING utf8mb4),CONVERT(UNHEX('47414C4C5550504920434942494C53') USING utf8mb4)),
    (20082,5924435,CONVERT(UNHEX('524F4C414E444F') USING utf8mb4),CONVERT(UNHEX('47494C4C20455350494E4F4C41') USING utf8mb4)),
    (20086,7759593,CONVERT(UNHEX('4245524E4152444F2046414249414E') USING utf8mb4),CONVERT(UNHEX('524F4C4F4E204341') USING utf8mb4)),
    (19977,2092235,CONVERT(UNHEX('414E544F4C49414E41') USING utf8mb4),CONVERT(UNHEX('4D4952414E444120444520445541525445') USING utf8mb4)),
    (20096,1470279,CONVERT(UNHEX('474C4F524941204245415452495A') USING utf8mb4),CONVERT(UNHEX('504552455A20474F4E5A414C455A') USING utf8mb4)),
    (20110,2986856,CONVERT(UNHEX('524F444F4C464F') USING utf8mb4),CONVERT(UNHEX('4D455A41204C4F50455A') USING utf8mb4));

CREATE TABLE IF NOT EXISTS persona_nombre_policial_backup_20260901 (
    cod_persona INT NOT NULL,
    nombre_anterior VARCHAR(255) NULL,
    apellido_anterior VARCHAR(255) NULL,
    nombre_nuevo VARCHAR(255) NOT NULL,
    apellido_nuevo VARCHAR(255) NOT NULL,
    fecha_backup DATETIME NOT NULL,
    PRIMARY KEY (cod_persona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS interconsulta_nombre_policial_backup_20260901 (
    id_interconsulta_paciente INT NOT NULL,
    cod_interConsultaFK INT NOT NULL,
    nombre_snapshot_anterior VARCHAR(150) NULL,
    asunto_anterior VARCHAR(180) NULL,
    fecha_backup DATETIME NOT NULL,
    PRIMARY KEY (id_interconsulta_paciente),
    KEY idx_nombre_policial_hilo (cod_interConsultaFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT IGNORE INTO persona_nombre_policial_backup_20260901
    (cod_persona,nombre_anterior,apellido_anterior,nombre_nuevo,apellido_nuevo,fecha_backup)
SELECT p.cod_persona,p.nombre_persona,p.apellido_persona,
       m.nombre_nuevo,m.apellido_nuevo,NOW()
FROM tmp_clientes_nombre_policial m
INNER JOIN cliente cl ON cl.cod_cliente=m.cod_cliente
INNER JOIN persona p ON p.cod_persona=m.cod_cliente
WHERE CAST(TRIM(cl.ci_cliente) AS UNSIGNED)=m.ci;

INSERT IGNORE INTO interconsulta_nombre_policial_backup_20260901
    (id_interconsulta_paciente,cod_interConsultaFK,nombre_snapshot_anterior,asunto_anterior,fecha_backup)
SELECT ip.id,ip.cod_interConsultaFK,ip.nombre_paciente_snapshot,ic.asunto,NOW()
FROM tmp_clientes_nombre_policial m
INNER JOIN interconsulta_paciente ip ON ip.cod_clienteFK_principal=m.cod_cliente
INNER JOIN interconsulta ic ON ic.cod_interConsulta=ip.cod_interConsultaFK;

UPDATE persona p
INNER JOIN persona_nombre_policial_backup_20260901 b ON b.cod_persona=p.cod_persona
SET p.nombre_persona=b.nombre_nuevo,
    p.apellido_persona=b.apellido_nuevo;

UPDATE interconsulta_paciente ip
INNER JOIN interconsulta_nombre_policial_backup_20260901 b
        ON b.id_interconsulta_paciente=ip.id
INNER JOIN persona p ON p.cod_persona=ip.cod_clienteFK_principal
SET ip.nombre_paciente_snapshot=TRIM(CONCAT_WS(CHAR(32),p.apellido_persona,p.nombre_persona)),
    ip.fecha_actualizacion=NOW();

UPDATE interconsulta ic
INNER JOIN interconsulta_nombre_policial_backup_20260901 b
        ON b.cod_interConsultaFK=ic.cod_interConsulta
INNER JOIN interconsulta_paciente ip
        ON ip.id=b.id_interconsulta_paciente
SET ic.asunto=LEFT(CONCAT(ip.nombre_paciente_snapshot,' - CI ',ip.cedula),180),
    ic.fecha_edit=NOW();

COMMIT;

SELECT
    (SELECT COUNT(*) FROM persona_nombre_policial_backup_20260901) AS personas_respaldadas,
    (SELECT COUNT(*) FROM interconsulta_nombre_policial_backup_20260901) AS hilos_respaldados;

