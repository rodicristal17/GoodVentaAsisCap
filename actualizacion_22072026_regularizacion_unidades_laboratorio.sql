-- Clinident Salud / Sistema Telar
-- Regularizacion segura de detalles historicos con varias unidades de laboratorio.
-- Compatible con MySQL usado por GoodVenta y ejecutable mas de una vez.

DROP PROCEDURE IF EXISTS telar_lab_reg_add_column;
DELIMITER $$
CREATE PROCEDURE telar_lab_reg_add_column(
    IN p_tabla VARCHAR(64),
    IN p_columna VARCHAR(64),
    IN p_definicion TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=p_tabla AND COLUMN_NAME=p_columna
    ) THEN
        SET @telar_lab_sql = CONCAT('ALTER TABLE `', p_tabla, '` ADD COLUMN `', p_columna, '` ', p_definicion);
        PREPARE telar_lab_stmt FROM @telar_lab_sql;
        EXECUTE telar_lab_stmt;
        DEALLOCATE PREPARE telar_lab_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_reg_add_column('trabajo_laboratorio', 'codigo_origen', 'VARCHAR(100) NULL AFTER codigo_visible');
CALL telar_lab_reg_add_column('trabajo_laboratorio', 'unidad_origen', 'INT NOT NULL DEFAULT 1 AFTER cod_detalle_activo_unico');
CALL telar_lab_reg_add_column('trabajo_laboratorio', 'cantidad_unidades_origen', 'INT NOT NULL DEFAULT 1 AFTER unidad_origen');
CALL telar_lab_reg_add_column('trabajo_laboratorio', 'id_regularizacion_unidadFK', 'INT NULL AFTER cantidad_unidades_origen');

DROP PROCEDURE IF EXISTS telar_lab_reg_add_column;

CREATE TABLE IF NOT EXISTS trabajo_laboratorio_regularizacion (
    id INT NOT NULL AUTO_INCREMENT,
    codigo_origen VARCHAR(100) NOT NULL,
    cod_detalle_ventaFK INT NOT NULL,
    cod_detalle_pendiente_unico INT NULL,
    cantidad_unidades INT NOT NULL,
    estado VARCHAR(25) NOT NULL DEFAULT 'pendiente_preparacion',
    clave_idempotencia VARCHAR(100) NOT NULL,
    payload_hash CHAR(64) NOT NULL,
    fecha_creacion DATETIME NOT NULL,
    cod_usuarioFK_create INT NOT NULL,
    fecha_consumo DATETIME NULL,
    cod_usuarioFK_consumo INT NULL,
    version INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tlab_regularizacion_codigo (codigo_origen),
    UNIQUE KEY uq_tlab_regularizacion_detalle_pendiente (cod_detalle_pendiente_unico),
    UNIQUE KEY uq_tlab_regularizacion_idempotencia (cod_usuarioFK_create, clave_idempotencia),
    KEY idx_tlab_regularizacion_detalle (cod_detalle_ventaFK, fecha_creacion),
    CONSTRAINT fk_tlab_regularizacion_detalle FOREIGN KEY (cod_detalle_ventaFK) REFERENCES detalle_venta (cod_detalle),
    CONSTRAINT fk_tlab_regularizacion_creador FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_tlab_regularizacion_consumidor FOREIGN KEY (cod_usuarioFK_consumo) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS trabajo_laboratorio_regularizacion_unidad (
    id INT NOT NULL AUTO_INCREMENT,
    id_regularizacionFK INT NOT NULL,
    numero_unidad INT NOT NULL,
    pieza VARCHAR(5) NULL,
    piezas_json TEXT NULL,
    denticion VARCHAR(20) NULL,
    alcance_odontologico VARCHAR(40) NOT NULL,
    fecha_creacion DATETIME NOT NULL,
    cod_usuarioFK_create INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tlab_regularizacion_unidad (id_regularizacionFK, numero_unidad),
    CONSTRAINT fk_tlab_reg_unidad_cabecera FOREIGN KEY (id_regularizacionFK) REFERENCES trabajo_laboratorio_regularizacion (id),
    CONSTRAINT fk_tlab_reg_unidad_creador FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

UPDATE trabajo_laboratorio
SET codigo_origen=CONCAT('LEG-', id)
WHERE codigo_origen IS NULL OR TRIM(codigo_origen)='';

DROP PROCEDURE IF EXISTS telar_lab_reg_drop_index;
DELIMITER $$
CREATE PROCEDURE telar_lab_reg_drop_index(IN p_tabla VARCHAR(64), IN p_indice VARCHAR(64))
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=p_tabla AND INDEX_NAME=p_indice
    ) THEN
        SET @telar_lab_sql = CONCAT('ALTER TABLE `', p_tabla, '` DROP INDEX `', p_indice, '`');
        PREPARE telar_lab_stmt FROM @telar_lab_sql;
        EXECUTE telar_lab_stmt;
        DEALLOCATE PREPARE telar_lab_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_reg_drop_index('trabajo_laboratorio', 'uq_trabajo_laboratorio_detalle_activo');
DROP PROCEDURE IF EXISTS telar_lab_reg_drop_index;

DROP PROCEDURE IF EXISTS telar_lab_reg_add_index;
DELIMITER $$
CREATE PROCEDURE telar_lab_reg_add_index(IN p_tabla VARCHAR(64), IN p_indice VARCHAR(64), IN p_definicion TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=p_tabla AND INDEX_NAME=p_indice
    ) THEN
        SET @telar_lab_sql = CONCAT('ALTER TABLE `', p_tabla, '` ', p_definicion);
        PREPARE telar_lab_stmt FROM @telar_lab_sql;
        EXECUTE telar_lab_stmt;
        DEALLOCATE PREPARE telar_lab_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_reg_add_index(
    'trabajo_laboratorio',
    'uq_trabajo_laboratorio_detalle_unidad_activa',
    'ADD UNIQUE KEY `uq_trabajo_laboratorio_detalle_unidad_activa` (`cod_detalle_activo_unico`,`unidad_origen`)'
);
CALL telar_lab_reg_add_index(
    'trabajo_laboratorio',
    'uq_trabajo_laboratorio_regularizacion_unidad',
    'ADD UNIQUE KEY `uq_trabajo_laboratorio_regularizacion_unidad` (`id_regularizacion_unidadFK`)'
);
CALL telar_lab_reg_add_index(
    'trabajo_laboratorio',
    'idx_trabajo_laboratorio_origen',
    'ADD KEY `idx_trabajo_laboratorio_origen` (`codigo_origen`,`unidad_origen`)'
);

DROP PROCEDURE IF EXISTS telar_lab_reg_add_index;

DROP PROCEDURE IF EXISTS telar_lab_reg_add_fk;
DELIMITER $$
CREATE PROCEDURE telar_lab_reg_add_fk(IN p_tabla VARCHAR(64), IN p_nombre VARCHAR(64), IN p_definicion TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME=p_tabla AND CONSTRAINT_NAME=p_nombre
    ) THEN
        SET @telar_lab_sql = CONCAT('ALTER TABLE `', p_tabla, '` ', p_definicion);
        PREPARE telar_lab_stmt FROM @telar_lab_sql;
        EXECUTE telar_lab_stmt;
        DEALLOCATE PREPARE telar_lab_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_reg_add_fk(
    'trabajo_laboratorio',
    'fk_trabajo_laboratorio_regularizacion_unidad',
    'ADD CONSTRAINT `fk_trabajo_laboratorio_regularizacion_unidad` FOREIGN KEY (`id_regularizacion_unidadFK`) REFERENCES `trabajo_laboratorio_regularizacion_unidad` (`id`)'
);

DROP PROCEDURE IF EXISTS telar_lab_reg_add_fk;

-- Reversion controlada (no ejecutar en produccion sin respaldar):
-- 1. Desvincular y eliminar solo regularizaciones que no hayan originado trabajos.
-- 2. Restaurar uq_trabajo_laboratorio_detalle_activo solo si no existen lotes activos.
-- 3. Quitar FK/indices/columnas nuevas y finalmente las dos tablas de regularizacion.
