CREATE TABLE IF NOT EXISTS solicitud_eliminado (
    id_solicitud_eliminado int(11) NOT NULL AUTO_INCREMENT,
    id_usuario_solicitud int(11) NOT NULL,
    fecha_solicitud datetime default CURRENT_TIMESTAMP,
    tabla_nombre varchar(120) DEFAULT NULL,
    registro_pk_columna varchar(120) DEFAULT NULL,
    registro_pk_valor varchar(120) DEFAULT NULL,
    registro_resumen text,
    motivo text NOT NULL,
    estado enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
    fecha_aprobacion datetime DEFAULT NULL,
    id_usuario_aprobacion int(11) DEFAULT NULL,
    observacion_aprobacion text,
    PRIMARY KEY (id_solicitud_eliminado),
    FOREIGN KEY (id_usuario_solicitud) REFERENCES usuario(cod_usuario),
    FOREIGN KEY (id_usuario_aprobacion) REFERENCES usuario(cod_usuario)
);

DROP PROCEDURE IF EXISTS add_column_if_missing_solicitud_eliminado;
DELIMITER $$
CREATE PROCEDURE add_column_if_missing_solicitud_eliminado(IN p_columna VARCHAR(120), IN p_definicion TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'solicitud_eliminado'
          AND COLUMN_NAME = p_columna
    ) THEN
        SET @sql_add_col = CONCAT('ALTER TABLE solicitud_eliminado ADD COLUMN ', p_columna, ' ', p_definicion);
        PREPARE stmt_add_col FROM @sql_add_col;
        EXECUTE stmt_add_col;
        DEALLOCATE PREPARE stmt_add_col;
    END IF;
END$$
DELIMITER ;

CALL add_column_if_missing_solicitud_eliminado('tabla_nombre', 'varchar(120) DEFAULT NULL AFTER fecha_solicitud');
CALL add_column_if_missing_solicitud_eliminado('registro_pk_columna', 'varchar(120) DEFAULT NULL AFTER tabla_nombre');
CALL add_column_if_missing_solicitud_eliminado('registro_pk_valor', 'varchar(120) DEFAULT NULL AFTER registro_pk_columna');
CALL add_column_if_missing_solicitud_eliminado('registro_resumen', 'text AFTER registro_pk_valor');
CALL add_column_if_missing_solicitud_eliminado('observacion_aprobacion', 'text AFTER id_usuario_aprobacion');

DROP PROCEDURE IF EXISTS add_column_if_missing_solicitud_eliminado;

CREATE OR REPLACE VIEW vista_informe_solicitud_eliminado AS
SELECT
    se.id_solicitud_eliminado,
    se.id_usuario_solicitud,
    ps.nombre_persona AS usuario_solicitud,
    se.fecha_solicitud,
    se.tabla_nombre,
    se.registro_pk_columna,
    se.registro_pk_valor,
    se.registro_resumen,
    se.motivo,
    se.estado,
    se.fecha_aprobacion,
    se.id_usuario_aprobacion,
    pa.nombre_persona AS usuario_aprobacion,
    se.observacion_aprobacion
FROM solicitud_eliminado se
LEFT JOIN persona ps ON ps.cod_persona = se.id_usuario_solicitud
LEFT JOIN persona pa ON pa.cod_persona = se.id_usuario_aprobacion;

INSERT INTO dashboard_access_catalog
    (access_key, label, module_key, module_label, icon_key, route_path, permission_key, is_active, is_default_quick_access, default_quick_order)
VALUES
    ('informe_solicitud_eliminado', 'Informe Solicitud Eliminados', 'informes', 'Informes', 'report', NULL, NULL, 1, 0, NULL)
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    module_key = VALUES(module_key),
    module_label = VALUES(module_label),
    icon_key = VALUES(icon_key),
    route_path = VALUES(route_path),
    permission_key = VALUES(permission_key),
    is_active = VALUES(is_active),
    is_default_quick_access = VALUES(is_default_quick_access),
    default_quick_order = VALUES(default_quick_order);
