-- Evento para generar mensaje cerca de fecha de pago
SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS generar_mensajes_gastos_fijos;

DELIMITER $$
CREATE EVENT generar_mensajes_gastos_fijos
ON SCHEDULE EVERY 1 DAY
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
                gf.cod_interConsultaFK,
                2
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

UPDATE historialactualizacion SET codigo='X-GT-1-JMTG-V1.59', detalles='Rediseño de ventana de egreso / ingreso', fecha='2026-02-06' WHERE idhistorialactualizacion= 2;

ALTER TABLE interconsulta ADD COLUMN fecha_vencimiento DATE;
ALTER TABLE interConsulta ADD COLUMN monto_limite INT;