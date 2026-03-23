-- Evento para generar mensaje cerca de fecha de pago
SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS generar_mensajes_gastos_fijos;

DELIMITER $$
CREATE EVENT generar_mensajes_gastos_fijos
ON SCHEDULE EVERY 1 Day
STARTS CURDATE()
DO
BEGIN
    DECLARE last_msg_id INT;
    DECLARE last_interconsulta INT;

    -- 1. Insertar el nuevo mensaje
        INSERT INTO mensaje (contenido, url, estado, cod_interConsultaFK, cod_usuarioFK)
        SELECT 
                CONCAT('Recordatorio: La cuenta de "', CAST(gf.descripcion AS CHAR CHARACTER SET utf8mb4), '" vence mañana (día ', gf.dia, ')'),
                NULL,
                'activo',
                gf.cod_interConsultaFK
        FROM gastos_fijos gf
        WHERE gf.dia = DAY(CURDATE()) + 1
            AND gf.estado = 'activo'
            AND NOT EXISTS (
                    SELECT 1 FROM mensaje m2
                    WHERE m2.cod_interConsultaFK = gf.cod_interConsultaFK
                        AND m2.contenido = CONCAT('Recordatorio: La cuenta de "', CAST(gf.descripcion AS CHAR CHARACTER SET utf8mb4), '" vence mañana (día ', gf.dia, ')')
                        AND DATE(m2.fecha_creacion) = CURDATE()
            );

    -- 2. Obtener el último mensaje insertado
    SET last_msg_id = LAST_INSERT_ID();

    IF last_msg_id > 0 THEN
        -- 3. Obtener la interconsulta asociada a ese mensaje
        SELECT cod_interConsultaFK INTO last_interconsulta
        FROM mensaje
        WHERE cod_mensaje = last_msg_id;

        -- 4. Insertar menciones copiando las del último mensaje de esa interconsulta
        INSERT INTO menciones (cod_usuarioFK, cod_mensajeFK, isLeido, estado)
        SELECT 
            m.cod_usuarioFK,
            last_msg_id,
            0,
            'activo'
        FROM menciones m
        INNER JOIN mensaje msg ON msg.cod_mensaje = m.cod_mensajeFK
        WHERE msg.cod_interConsultaFK = last_interconsulta
        ORDER BY msg.fecha_creacion DESC;
    END IF;
END$$

DELIMITER ;

UPDATE gastos_fijos SET estado="inactivo" WHERE cod_gastos_fijos IS NOT NULL;

ALTER TABLE interconsulta ADD COLUMN fecha_vencimiento DATE;
ALTER TABLE interconsulta ADD COLUMN monto_limite INT;

SET GLOBAL event_scheduler = ON;

DELIMITER $$

DROP EVENT IF EXISTS generar_mensajes_interconsulta_vencimiento$$
CREATE EVENT generar_mensajes_interconsulta_vencimiento
ON SCHEDULE EVERY 1 DAY
STARTS CURDATE()
DO
BEGIN
    -- Marcar como no leídas las menciones del último mensaje
    UPDATE menciones m
    JOIN mensaje msg ON m.cod_mensajeFK = msg.cod_mensaje
    JOIN (
        -- Subconsulta que obtiene el cod_mensaje del último mensaje de cada interconsulta vencida
        SELECT 
            ic.cod_interConsulta,
            MAX(msg_inner.cod_mensaje) AS ultimo_cod_mensaje
        FROM interconsulta ic
        JOIN mensaje msg_inner ON msg_inner.cod_interConsultaFK = ic.cod_interConsulta
        WHERE ic.fecha_vencimiento = CURDATE()
        AND msg_inner.fecha_creacion <= NOW()
        GROUP BY ic.cod_interConsulta
        HAVING MAX(msg_inner.fecha_creacion) = (
            SELECT MAX(msg_max.fecha_creacion)
            FROM mensaje msg_max
            WHERE msg_max.cod_interConsultaFK = ic.cod_interConsulta
            AND msg_max.fecha_creacion <= NOW()
        )
    ) ultimos ON msg.cod_mensaje = ultimos.ultimo_cod_mensaje
    SET m.isLeido = 0;
END$$

DELIMITER ;

ALTER TABLE interconsulta DROP COLUMN fecha_vencimiento;

ALTER TABLE mensaje DROP FOREIGN KEY mensaje_ibfk_2;

ALTER TABLE gastos ADD COLUMN cod_usuarioFK_edit INT;

ALTER TABLE detallesventaeliminado ADD COLUMN cod_ventaFK INT;

ALTER TABLE motivos_ingreso_egreso DROP COLUMN presupuesto;

CREATE TABLE montos_limites_gasto_motivo (
    cod_monto_limite_gasto_motivo INT PRIMARY KEY AUTO_INCREMENT,
    monto_limite INT,
    cod_motivo_ingreso_egresoFK INT,
    cod_localFK INT,
    cod_usuarioFK_edit INT,
    fecha_edit DATETIME,
    Foreign Key (cod_localFK) REFERENCES local(cod_local),
    Foreign Key (cod_motivo_ingreso_egresoFK) REFERENCES motivos_ingreso_egreso(cod_motivo_ingreso_egreso)
);

INSERT INTO montos_limites_gasto_motivo (
    monto_limite,
    cod_motivo_ingreso_egresoFK,
    cod_localFK,
    fecha_edit,
    cod_usuarioFK_edit
)
SELECT 
    NULL,
    m.cod_motivo_ingreso_egreso,
    l.cod_local,
    NULL,
    NULL
FROM motivos_ingreso_egreso m
CROSS JOIN local l
LEFT JOIN montos_limites_gasto_motivo ml
    ON ml.cod_motivo_ingreso_egresoFK = m.cod_motivo_ingreso_egreso
   AND ml.cod_localFK = l.cod_local
WHERE ml.cod_monto_limite_gasto_motivo IS NULL;

ALTER TABLE cancelaciones ADD COLUMN cod_usuarioFK INT;

ALTER TABLE gastos ADD COLUMN cod_mensajeFK INT;

DELIMITER $$

CREATE TRIGGER trg_gastos_inactivo
AFTER UPDATE ON gastos
FOR EACH ROW
BEGIN
    -- Validamos que el nuevo estado sea 'Inactivo'
    IF NEW.estado = 'Inactivo' THEN
        -- Actualizamos el mensaje asociado
        UPDATE mensaje
        SET estado = 'inactivo'
        WHERE cod_mensaje = NEW.cod_mensajeFK;
    END IF;
END$$

DELIMITER ;

DROP TABLE gastos_fijos;

ALTER TABLE gastos ADD COLUMN modalidad ENUM('contado', 'credito') DEFAULT 'contado'; 

UPDATE gastos SET estado="Activo" WHERE estado like 'Activo and g.idgastos%';

UPDATE gastos SET modalidad= 'credito', estado= 'pendiente' WHERE motivo like 'Cuota % de % (%)';

ALTER TABLE interconsulta ADD COLUMN observacion VARCHAR(255);

ALTER TABLE asistencia ADD COLUMN justificacion VARCHAR(255);
ALTER TABLE usuario ADD COLUMN hora_entrada_lunes TIME;
ALTER TABLE usuario ADD COLUMN hora_entrada_martes TIME;
ALTER TABLE usuario ADD COLUMN hora_entrada_miercoles TIME;
ALTER TABLE usuario ADD COLUMN hora_entrada_jueves TIME;
ALTER TABLE usuario ADD COLUMN hora_entrada_viernes TIME;
ALTER TABLE usuario ADD COLUMN hora_entrada_sabado TIME;

ALTER TABLE insumos_local ADD COLUMN cod_usuario_responsableFK INT(11);
ALTER TABLE insumos_local ADD COLUMN cod_usuarioFK_create INT(11);
UPDATE insumos_local
SET cod_usuarioFK_edit = cod_usuarioFK_create;


UPDATE historialactualizacion SET codigo='X-GT-1-JMTG-V1.69', detalles='Horarios de entrada agregado a los usuarios.', fecha='2026-03-17' WHERE idhistorialactualizacion= 2;

ALTER TABLE pago ADD COLUMN num_comprobante VARCHAR(15);

-- Cargar permisos
-- EDITARINTERCONSULTA, CREARINTERCONSULTA, FUSIONARINTERCONSULTA