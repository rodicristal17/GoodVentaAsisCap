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

ALTER TABLE pago ADD COLUMN num_comprobante VARCHAR(15);
ALTER TABLE pago ADD COLUMN fecha_facturado DATE;

ALTER TABLE insumos_local ADD COLUMN modelo VARCHAR(100);
ALTER TABLE insumos_local ADD COLUMN nro_serie VARCHAR(100);
ALTER TABLE insumos_local ADD COLUMN cod_marcaFK INT(11);
ALTER TABLE insumos_local ADD CONSTRAINT fk_insumos_local_marca
    FOREIGN KEY (cod_marcaFK) REFERENCES marcas(cod_marcas);
ALTER TABLE insumos_local ADD COLUMN url_factura VARCHAR(255);

CREATE TABLE historial_insumo_local (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cod_usuarioFK_responsable_anterior INT(11),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    cod_insumoFK INT(11) NOT NULL,
    cod_usuarioFK_edit INT(11),
    FOREIGN KEY (cod_insumoFK) REFERENCES insumos_local(cod_insumo),
    FOREIGN KEY (cod_usuarioFK_responsable_anterior) REFERENCES usuario(cod_usuario),
    FOREIGN KEY (cod_usuarioFK_edit) REFERENCES usuario(cod_usuario)
);

DROP TRIGGER IF EXISTS trg_historial_responsable_insumo_local;

DELIMITER $$

CREATE TRIGGER trg_historial_responsable_insumo_local
AFTER UPDATE ON insumos_local
FOR EACH ROW
BEGIN
    IF OLD.cod_usuario_responsableFK IS NOT NULL AND NOT (OLD.cod_usuario_responsableFK <=> NEW.cod_usuario_responsableFK) THEN
        INSERT INTO historial_insumo_local (
            cod_usuarioFK_responsable_anterior,
            cod_insumoFK,
            cod_usuarioFK_edit
        ) VALUES (
            OLD.cod_usuario_responsableFK,
            NEW.cod_insumo,
            NEW.cod_usuarioFK_edit
        );
    END IF;
END$$

DELIMITER ;

ALTER TABLE insumos_local ADD COLUMN url_compromiso VARCHAR(255);

CREATE TABLE historial_personas_usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_persona VARCHAR(255),
    telefono VARCHAR(25),
    fecha_creacion DATE,
    fecha_cambio DATE,
    cod_usuarioFK INT(11),
    FOREIGN KEY (cod_usuarioFK) REFERENCES usuario(cod_usuario)
);

DROP TRIGGER IF EXISTS trg_historial_personas_usuario;

DELIMITER $$

CREATE TRIGGER trg_historial_personas_usuario
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN
    IF NOT (OLD.rut_usuario <=> NEW.rut_usuario) THEN
        INSERT INTO historial_personas_usuario (
            nombre_persona,
            telefono,
            fecha_creacion,
            fecha_cambio,
            cod_usuarioFK
        )
        SELECT
            p.nombre_persona,
            p.telefono,
            OLD.fecha_creacion,
            CURDATE(),
            NEW.cod_usuario
        FROM persona p
        WHERE p.cod_persona = NEW.cod_usuario;
    END IF;
END$$

DELIMITER ;


ALTER TABLE insumos_local ADD COLUMN estado_fisico ENUM('excelente', 'mantenimiento', 'dañado');
ALTER TABLE insumos_local ADD COLUMN categoria ENUM('mobiliario', 'medico');

CREATE TABLE dictamenes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asunto VARCHAR(150) NOT NULL,
    dictamen VARCHAR(750) NOT NULL,
    estado VARCHAR(15) DEFAULT 'solicitada',
    fecha_create DATETIME DEFAULT CURRENT_TIMESTAMP,
    cod_usuarioFK_create INT(11),
    fecha_ejecut DATETIME,
    cod_usuarioFK_ejecut INT(11),
    fecha_autoriz DATETIME,
    cod_usuarioFK_autoriz INT(11),
    cod_interConsultaFK INT(11),
    FOREIGN KEY (cod_interConsultaFK) REFERENCES interconsulta(cod_interConsulta)
);

ALTER TABLE mensaje ADD COLUMN cod_dictamenFK INT(11);

DELIMITER $$
DROP PROCEDURE IF EXISTS generar_mensajes_interconsulta_vencimiento$$
CREATE PROCEDURE generar_mensajes_interconsulta_vencimiento()
BEGIN
    -- Marcar como no leídas las menciones del último mensaje
    UPDATE menciones m
    JOIN mensaje msg ON m.cod_mensajeFK = msg.cod_mensaje
    JOIN (
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
SET GLOBAL event_scheduler = ON;
DROP EVENT IF EXISTS generar_mensajes_interconsulta_vencimiento_evento;
CREATE EVENT generar_mensajes_interconsulta_vencimiento_evento
ON SCHEDULE EVERY 1 DAY
STARTS CURDATE()
DO
    CALL generar_mensajes_interconsulta_vencimiento();

DELIMITER $$

DROP PROCEDURE IF EXISTS actualizar_gastos_pendientes$$
CREATE PROCEDURE actualizar_gastos_pendientes()
BEGIN
    UPDATE gastos
    SET estado = 'solicitado'
    WHERE fecha <= CURDATE()
      AND estado = 'pendiente';
END$$

DELIMITER ;

DROP EVENT IF EXISTS actualizar_estado_gastos_by_fecha;
CREATE EVENT actualizar_estado_gastos_by_fecha
ON SCHEDULE EVERY 1 DAY
STARTS CURDATE()
DO
    CALL actualizar_gastos_pendientes();

DELIMITER $$

ALTER TABLE dictamenes 
MODIFY estado VARCHAR(15) DEFAULT 'autorizado';

CREATE TABLE presupuesto(
    id INT PRIMARY KEY AUTO_INCREMENT,
    cant_cuotas INT,
    cod_clienteFK INT(11),
    fecha_create DATETIME DEFAULT CURRENT_TIMESTAMP,
    cod_usuarioFK_create INT(11) NOT NULL,
    FOREIGN KEY (cod_clienteFK) REFERENCES cliente(cod_cliente)
);

CREATE TABLE detalles_presupuesto(
    id INT PRIMARY KEY AUTO_INCREMENT,
    cod_productoFK VARCHAR(45),
    precio bigint NOT NULL,
    cantidad INT DEFAULT 1,
    cod_presupuestoFK INT,
    FOREIGN KEY (cod_presupuestoFK) REFERENCES presupuesto(id)
);


ALTER TABLE detalles_presupuesto ADD COLUMN es_prioritario BOOLEAN DEFAULT 0;
ALTER TABLE presupuesto ADD COLUMN cod_usuarioFK_edit INT(11);
ALTER TABLE presupuesto ADD COLUMN fecha_edit DATETIME;

ALTER TABLE gastos ADD COLUMN cod_gasto_padre INT(11);

UPDATE gastos
SET cod_gasto_padre = CAST(
    SUBSTRING_INDEX(
        SUBSTRING_INDEX(TRIM(motivo), '(', -1),
        ')',
        1
    ) AS UNSIGNED
)
WHERE TRIM(motivo) REGEXP '^Cuota [0-9]+ de .+ \\([0-9]+\\)$'
  AND (
      cod_gasto_padre IS NULL
      OR cod_gasto_padre <> CAST(
          SUBSTRING_INDEX(
              SUBSTRING_INDEX(TRIM(motivo), '(', -1),
              ')',
              1
          ) AS UNSIGNED
      )
  );

ALTER TABLE detalles_presupuesto ADD COLUMN es_alternativo BOOLEAN DEFAULT 0;

UPDATE historialactualizacion SET codigo='X-GT-1-JMTG-V1.76', detalles='Re-estructuracion de presupuesto.', fecha='2026-04-22' WHERE idhistorialactualizacion= 2;

CREATE TABLE tareas_programadas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    hora TIME,
    estado ENUM('pendiente', 'completada', 'inactivo') DEFAULT 'pendiente',
    fecha_realizado DATETIME,
    cod_usuarioFK INT(11),
    cod_usuarioFK_create INT(11),
    fecha_create DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Cargar permisos
-- EDITARINTERCONSULTA, CREARINTERCONSULTA, FUSIONARINTERCONSULTA
-- CREARDICTAMEN, EDITARDICTAMEN
-- VERHISTORIALPRESUPUESTO, 
