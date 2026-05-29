SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS log_registros_inactivos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tabla_nombre VARCHAR(128) NOT NULL,
    registro_pk_columna VARCHAR(128) NOT NULL,
    registro_pk_valor VARCHAR(128) NOT NULL,
    registro_resumen VARCHAR(255) NULL,
    estado_anterior VARCHAR(50) NULL,
    estado_nuevo VARCHAR(50) NOT NULL DEFAULT 'inactivo',
    cod_usuario_accion BIGINT NULL,
    nombre_usuario_accion VARCHAR(180) NULL,
    fecha_accion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_bd VARCHAR(128) NOT NULL,
    origen VARCHAR(40) NOT NULL DEFAULT 'trigger',
    datos_json TEXT NULL,
    PRIMARY KEY (id),
    KEY idx_log_inactivos_tabla_registro (tabla_nombre, registro_pk_valor),
    KEY idx_log_inactivos_usuario (cod_usuario_accion),
    KEY idx_log_inactivos_fecha (fecha_accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$

DROP PROCEDURE IF EXISTS crear_trigger_log_inactivo $$
CREATE PROCEDURE crear_trigger_log_inactivo(
    IN p_tabla VARCHAR(128),
    IN p_pk_columna VARCHAR(128),
    IN p_usuario_columna VARCHAR(128),
    IN p_resumen_columna VARCHAR(128)
)
BEGIN
    SET @trigger_name = CONCAT('trg_', LEFT(p_tabla, 44), '_', LEFT(MD5(p_tabla), 8), '_inac');

    SET @drop_sql = CONCAT('DROP TRIGGER IF EXISTS `', @trigger_name, '`');
    PREPARE stmt_drop FROM @drop_sql;
    EXECUTE stmt_drop;
    DEALLOCATE PREPARE stmt_drop;

    SET @usuario_expr = IF(
        p_usuario_columna IS NULL OR p_usuario_columna = '',
        'NULL',
        CONCAT('NEW.`', p_usuario_columna, '`')
    );

    SET @resumen_expr = IF(
        p_resumen_columna IS NULL OR p_resumen_columna = '',
        'NULL',
        CONCAT('NEW.`', p_resumen_columna, '`')
    );

    SET @nombre_usuario_expr = IF(
        p_usuario_columna IS NULL OR p_usuario_columna = '',
        'NULL',
        CONCAT('(SELECT nombre_persona FROM persona WHERE cod_persona = NEW.`', p_usuario_columna, '` LIMIT 1)')
    );

    SET @trigger_sql = CONCAT(
        'CREATE TRIGGER `', @trigger_name, '` BEFORE UPDATE ON `', p_tabla, '` ',
        'FOR EACH ROW BEGIN ',
        'IF LOWER(IFNULL(OLD.`estado`, '''')) <> ''inactivo'' ',
        'AND LOWER(IFNULL(NEW.`estado`, '''')) = ''inactivo'' THEN ',
        'INSERT INTO log_registros_inactivos ',
        '(tabla_nombre, registro_pk_columna, registro_pk_valor, registro_resumen, estado_anterior, estado_nuevo, ',
        'cod_usuario_accion, nombre_usuario_accion, fecha_accion, usuario_bd, datos_json) VALUES (',
        QUOTE(p_tabla), ', ',
        QUOTE(p_pk_columna), ', ',
        'NEW.`', p_pk_columna, '`, ',
        @resumen_expr, ', OLD.`estado`, NEW.`estado`, ',
        @usuario_expr, ', ',
        @nombre_usuario_expr, ', ',
        'NOW(), CURRENT_USER(), ',
        'CONCAT(''{"tabla":"'', ', QUOTE(p_tabla), ', ''","pk":"'', NEW.`', p_pk_columna, '`, ''"}'')); ',
        'END IF; END'
    );

    PREPARE stmt_create FROM @trigger_sql;
    EXECUTE stmt_create;
    DEALLOCATE PREPARE stmt_create;
END $$

DROP PROCEDURE IF EXISTS instalar_triggers_log_inactivos $$
CREATE PROCEDURE instalar_triggers_log_inactivos()
BEGIN
    DECLARE v_done INT DEFAULT 0;
    DECLARE v_tabla VARCHAR(128);
    DECLARE v_pk_columna VARCHAR(128);
    DECLARE v_usuario_columna VARCHAR(128);
    DECLARE v_resumen_columna VARCHAR(128);

    DECLARE cur CURSOR FOR
        SELECT
            c.TABLE_NAME,
            k.COLUMN_NAME AS pk_columna,
            (
                SELECT c2.COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS c2
                WHERE c2.TABLE_SCHEMA = DATABASE()
                  AND c2.TABLE_NAME = c.TABLE_NAME
                  AND c2.COLUMN_NAME IN (
                    'cod_usuarioFK_edit',
                    'cod_usuario_editFK',
                    'cod_usuarioFK_editado',
                    'cod_user_edit',
                    'cod_usuario_edit',
                    'cod_usuario',
                    'cod_usuarioFK',
                    'codusuarioce',
                    'cod_usuFK_resol'
                  )
                ORDER BY FIELD(
                    c2.COLUMN_NAME,
                    'cod_usuarioFK_edit',
                    'cod_usuario_editFK',
                    'cod_usuarioFK_editado',
                    'cod_user_edit',
                    'cod_usuario_edit',
                    'cod_usuario',
                    'cod_usuarioFK',
                    'codusuarioce',
                    'cod_usuFK_resol'
                )
                LIMIT 1
            ) AS usuario_columna,
            (
                SELECT c3.COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS c3
                WHERE c3.TABLE_SCHEMA = DATABASE()
                  AND c3.TABLE_NAME = c.TABLE_NAME
                  AND c3.COLUMN_NAME IN (
                    'nombre',
                    'nombre_persona',
                    'nombre_producto',
                    'descripcion',
                    'titulo',
                    'asunto',
                    'observacion'
                  )
                ORDER BY FIELD(
                    c3.COLUMN_NAME,
                    'nombre',
                    'nombre_persona',
                    'nombre_producto',
                    'descripcion',
                    'titulo',
                    'asunto',
                    'observacion'
                )
                LIMIT 1
            ) AS resumen_columna
        FROM INFORMATION_SCHEMA.COLUMNS c
        JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
          ON k.TABLE_SCHEMA = c.TABLE_SCHEMA
         AND k.TABLE_NAME = c.TABLE_NAME
         AND k.CONSTRAINT_NAME = 'PRIMARY'
        WHERE c.TABLE_SCHEMA = DATABASE()
          AND c.COLUMN_NAME = 'estado'
          AND c.TABLE_NAME <> 'log_registros_inactivos'
          AND (
              SELECT COUNT(*)
              FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k2
              WHERE k2.TABLE_SCHEMA = c.TABLE_SCHEMA
                AND k2.TABLE_NAME = c.TABLE_NAME
                AND k2.CONSTRAINT_NAME = 'PRIMARY'
          ) = 1;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_tabla, v_pk_columna, v_usuario_columna, v_resumen_columna;
        IF v_done = 1 THEN
            LEAVE read_loop;
        END IF;

        CALL crear_trigger_log_inactivo(v_tabla, v_pk_columna, v_usuario_columna, v_resumen_columna);
    END LOOP;

    CLOSE cur;
END $$

DELIMITER ;

CALL instalar_triggers_log_inactivos();
