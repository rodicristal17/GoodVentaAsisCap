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

UPDATE historialactualizacion SET codigo='X-GT-1-JMTG-V1.61', detalles='Actualizacion php', fecha='2026-02-16' WHERE idhistorialactualizacion= 2;

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

