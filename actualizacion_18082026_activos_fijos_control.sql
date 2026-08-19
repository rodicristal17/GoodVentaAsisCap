-- Sistema Telar / Clinident Salud - Activos fijos
-- Estructura aditiva para control fisico, sectores y depreciacion manual.
-- No recalcula costos historicos: todo registro existente queda pendiente de validar.

SET @telar_schema := DATABASE();

CREATE TABLE IF NOT EXISTS inventario_local_sector (
    id INT NOT NULL AUTO_INCREMENT,
    cod_localFK INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    cod_usuarioFK_create INT NULL,
    cod_usuarioFK_edit INT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_edit DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_inventario_sector_local_nombre (cod_localFK, nombre),
    KEY idx_inventario_sector_estado (cod_localFK, estado),
    CONSTRAINT fk_inventario_sector_local
        FOREIGN KEY (cod_localFK) REFERENCES local(cod_local),
    CONSTRAINT fk_inventario_sector_usuario_create
        FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario(cod_usuario) ON DELETE SET NULL,
    CONSTRAINT fk_inventario_sector_usuario_edit
        FOREIGN KEY (cod_usuarioFK_edit) REFERENCES usuario(cod_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP PROCEDURE IF EXISTS telar_activos_add_column_18082026;
DELIMITER $$
CREATE PROCEDURE telar_activos_add_column_18082026(
    IN p_columna VARCHAR(64),
    IN p_definicion VARCHAR(500)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'insumos_local'
          AND COLUMN_NAME = p_columna
    ) THEN
        SET @telar_sql = CONCAT(
            'ALTER TABLE insumos_local ADD COLUMN `',
            REPLACE(p_columna, '`', ''),
            '` ',
            p_definicion
        );
        PREPARE telar_stmt FROM @telar_sql;
        EXECUTE telar_stmt;
        DEALLOCATE PREPARE telar_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_activos_add_column_18082026('cod_sectorFK', 'INT NULL AFTER cod_localFK');
CALL telar_activos_add_column_18082026('tipo_control', 'ENUM(''pendiente'',''individual'',''lote'') NOT NULL DEFAULT ''pendiente'' AFTER categoria');
CALL telar_activos_add_column_18082026('costo_tipo', 'ENUM(''pendiente'',''unitario'',''lote'') NOT NULL DEFAULT ''pendiente'' AFTER costo');
CALL telar_activos_add_column_18082026('fecha_adquisicion', 'DATE NULL AFTER fecha_creacion');
CALL telar_activos_add_column_18082026('depreciacion_acumulada', 'BIGINT(20) NOT NULL DEFAULT 0 AFTER costo_tipo');
CALL telar_activos_add_column_18082026('fecha_depreciacion', 'DATE NULL AFTER depreciacion_acumulada');
CALL telar_activos_add_column_18082026('cod_usuarioFK_depreciacion', 'INT NULL AFTER fecha_depreciacion');
CALL telar_activos_add_column_18082026('fecha_actualizacion_depreciacion', 'DATETIME NULL AFTER cod_usuarioFK_depreciacion');
CALL telar_activos_add_column_18082026('frecuencia_verificacion', 'ENUM(''mensual'',''semestral'') NOT NULL DEFAULT ''semestral'' AFTER estado_fisico');

DROP PROCEDURE telar_activos_add_column_18082026;

DROP PROCEDURE IF EXISTS telar_activos_add_index_18082026;
DELIMITER $$
CREATE PROCEDURE telar_activos_add_index_18082026(
    IN p_indice VARCHAR(64),
    IN p_columnas VARCHAR(300)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'insumos_local'
          AND INDEX_NAME = p_indice
    ) THEN
        SET @telar_sql = CONCAT(
            'ALTER TABLE insumos_local ADD INDEX `',
            REPLACE(p_indice, '`', ''),
            '` (', p_columnas, ')'
        );
        PREPARE telar_stmt FROM @telar_sql;
        EXECUTE telar_stmt;
        DEALLOCATE PREPARE telar_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_activos_add_index_18082026('idx_insumos_local_sector', 'cod_sectorFK');
CALL telar_activos_add_index_18082026('idx_insumos_local_control', 'tipo_control, costo_tipo');
CALL telar_activos_add_index_18082026('idx_insumos_local_verificacion', 'frecuencia_verificacion, estado_fisico');
DROP PROCEDURE telar_activos_add_index_18082026;

SET @telar_fk_sector_existe := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'insumos_local'
      AND CONSTRAINT_NAME = 'fk_insumos_local_sector'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @telar_sql := IF(
    @telar_fk_sector_existe = 0,
    'ALTER TABLE insumos_local ADD CONSTRAINT fk_insumos_local_sector FOREIGN KEY (cod_sectorFK) REFERENCES inventario_local_sector(id) ON DELETE SET NULL',
    'SELECT ''SIN_CAMBIOS: fk_insumos_local_sector ya existe'' AS resultado'
);
PREPARE telar_stmt FROM @telar_sql;
EXECUTE telar_stmt;
DEALLOCATE PREPARE telar_stmt;

SET @telar_fk_depreciacion_existe := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'insumos_local'
      AND CONSTRAINT_NAME = 'fk_insumos_local_usuario_depreciacion'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @telar_sql := IF(
    @telar_fk_depreciacion_existe = 0,
    'ALTER TABLE insumos_local ADD CONSTRAINT fk_insumos_local_usuario_depreciacion FOREIGN KEY (cod_usuarioFK_depreciacion) REFERENCES usuario(cod_usuario) ON DELETE SET NULL',
    'SELECT ''SIN_CAMBIOS: fk_insumos_local_usuario_depreciacion ya existe'' AS resultado'
);
PREPARE telar_stmt FROM @telar_sql;
EXECUTE telar_stmt;
DEALLOCATE PREPARE telar_stmt;

CREATE TABLE IF NOT EXISTS inventario_local_verificacion (
    id BIGINT NOT NULL AUTO_INCREMENT,
    cod_insumoFK INT NOT NULL,
    fecha_verificacion DATE NOT NULL,
    cantidad_esperada INT NOT NULL,
    cantidad_encontrada INT NOT NULL,
    estado_fisico ENUM('excelente','mantenimiento','dañado') NOT NULL,
    cod_localFK INT NOT NULL,
    cod_sectorFK INT NULL,
    frecuencia_aplicada ENUM('mensual','semestral') NOT NULL DEFAULT 'semestral',
    proxima_verificacion DATE NOT NULL,
    observacion VARCHAR(500) NULL,
    cod_usuarioFK_verificador INT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inventario_verificacion_activo_fecha (cod_insumoFK, fecha_verificacion, id),
    KEY idx_inventario_verificacion_proxima (proxima_verificacion),
    CONSTRAINT fk_inventario_verificacion_activo
        FOREIGN KEY (cod_insumoFK) REFERENCES insumos_local(cod_insumo),
    CONSTRAINT fk_inventario_verificacion_local
        FOREIGN KEY (cod_localFK) REFERENCES local(cod_local),
    CONSTRAINT fk_inventario_verificacion_sector
        FOREIGN KEY (cod_sectorFK) REFERENCES inventario_local_sector(id) ON DELETE SET NULL,
    CONSTRAINT fk_inventario_verificacion_usuario
        FOREIGN KEY (cod_usuarioFK_verificador) REFERENCES usuario(cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS inventario_local_depreciacion_historial (
    id BIGINT NOT NULL AUTO_INCREMENT,
    cod_insumoFK INT NOT NULL,
    valor_anterior BIGINT(20) NOT NULL DEFAULT 0,
    valor_nuevo BIGINT(20) NOT NULL DEFAULT 0,
    fecha_depreciacion DATE NULL,
    observacion VARCHAR(255) NULL,
    cod_usuarioFK INT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inventario_depreciacion_activo_fecha (cod_insumoFK, fecha_creacion, id),
    CONSTRAINT fk_inventario_depreciacion_activo
        FOREIGN KEY (cod_insumoFK) REFERENCES insumos_local(cod_insumo),
    CONSTRAINT fk_inventario_depreciacion_usuario
        FOREIGN KEY (cod_usuarioFK) REFERENCES usuario(cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Firma final. Los registros existentes deben conservar costo_tipo='pendiente'.
SELECT
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='insumos_local'
        AND COLUMN_NAME IN (
            'cod_sectorFK','tipo_control','costo_tipo','fecha_adquisicion',
            'depreciacion_acumulada','fecha_depreciacion',
            'cod_usuarioFK_depreciacion','fecha_actualizacion_depreciacion',
            'frecuencia_verificacion'
        )) AS columnas_activos_disponibles,
    (SELECT COUNT(*) FROM information_schema.TABLES
      WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN (
          'inventario_local_sector',
          'inventario_local_verificacion',
          'inventario_local_depreciacion_historial'
      )) AS tablas_control_disponibles,
    (SELECT COUNT(*) FROM insumos_local WHERE costo_tipo='pendiente') AS costos_pendientes_validar;
