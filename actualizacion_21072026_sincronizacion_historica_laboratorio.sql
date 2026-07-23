-- Sistema Telar / Clinident Salud
-- Sincronizacion segura de trabajos mecanicos dentales historicos.
--
-- Migracion ADITIVA e idempotente. Requiere que previamente se haya aplicado
-- actualizacion_21072026_trabajos_laboratorio_telar.sql.
--
-- Principios:
--   * trabajo_mecanico_dental se conserva sin cambios;
--   * no se infiere automaticamente un detalle de venta;
--   * no se atribuye la sincronizacion a un usuario;
--   * no se conceden permisos;
--   * las correcciones posteriores quedan versionadas en eventos inmutables.
--
-- Compatible con MySQL 5.6+ y PHP 7.2.

SET @telar_lab_hist_db = DATABASE();

-- El profesional queda disponible tambien en el trabajo operativo nuevo.
DROP PROCEDURE IF EXISTS telar_lab_hist_add_column;
DELIMITER $$
CREATE PROCEDURE telar_lab_hist_add_column(
    IN p_tabla VARCHAR(64),
    IN p_columna VARCHAR(64),
    IN p_definicion VARCHAR(1000)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_tabla
          AND COLUMN_NAME = p_columna
    ) THEN
        SET @telar_lab_hist_sql = CONCAT(
            'ALTER TABLE `', REPLACE(p_tabla, '`', ''),
            '` ADD COLUMN `', REPLACE(p_columna, '`', ''), '` ', p_definicion
        );
        PREPARE telar_lab_hist_stmt FROM @telar_lab_hist_sql;
        EXECUTE telar_lab_hist_stmt;
        DEALLOCATE PREPARE telar_lab_hist_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_hist_add_column(
    'trabajo_laboratorio',
    'cod_especialistaFK',
    'INT NULL'
);

DROP PROCEDURE IF EXISTS telar_lab_hist_add_column;

DROP PROCEDURE IF EXISTS telar_lab_hist_add_index;
DELIMITER $$
CREATE PROCEDURE telar_lab_hist_add_index(
    IN p_tabla VARCHAR(64),
    IN p_indice VARCHAR(64),
    IN p_definicion VARCHAR(1000)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_tabla
          AND INDEX_NAME = p_indice
    ) THEN
        SET @telar_lab_hist_sql = CONCAT(
            'ALTER TABLE `', REPLACE(p_tabla, '`', ''), '` ', p_definicion
        );
        PREPARE telar_lab_hist_stmt FROM @telar_lab_hist_sql;
        EXECUTE telar_lab_hist_stmt;
        DEALLOCATE PREPARE telar_lab_hist_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_hist_add_index(
    'trabajo_laboratorio',
    'idx_trabajo_laboratorio_especialista',
    'ADD KEY `idx_trabajo_laboratorio_especialista` (`cod_especialistaFK`)'
);

DROP PROCEDURE IF EXISTS telar_lab_hist_add_index;

DROP PROCEDURE IF EXISTS telar_lab_hist_add_fk;
DELIMITER $$
CREATE PROCEDURE telar_lab_hist_add_fk(
    IN p_tabla VARCHAR(64),
    IN p_restriccion VARCHAR(64),
    IN p_definicion VARCHAR(1000)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = p_tabla
          AND CONSTRAINT_NAME = p_restriccion
    ) THEN
        SET @telar_lab_hist_sql = CONCAT(
            'ALTER TABLE `', REPLACE(p_tabla, '`', ''), '` ', p_definicion
        );
        PREPARE telar_lab_hist_stmt FROM @telar_lab_hist_sql;
        EXECUTE telar_lab_hist_stmt;
        DEALLOCATE PREPARE telar_lab_hist_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_hist_add_fk(
    'trabajo_laboratorio',
    'fk_trabajo_laboratorio_especialista',
    'ADD CONSTRAINT `fk_trabajo_laboratorio_especialista` FOREIGN KEY (`cod_especialistaFK`) REFERENCES `usuario` (`cod_usuario`)'
);

DROP PROCEDURE IF EXISTS telar_lab_hist_add_fk;

-- Cabecera de convalidacion. Los campos *_snapshot no llevan FK: representan
-- exactamente lo observado al sincronizar y deben sobrevivir a cambios futuros
-- de catalogos. Los campos declarados si validan contra las tablas vigentes.
CREATE TABLE IF NOT EXISTS trabajo_laboratorio_historico (
    id INT NOT NULL AUTO_INCREMENT,
    cod_trabajo_mecanico_legacyFK INT NOT NULL,
    cod_venta_snapshot INT NULL,
    cod_cliente_snapshot INT NULL,
    cod_tipo_trabajo_snapshot INT NULL,
    cod_mecanico_dental_snapshot INT NULL,
    cod_local_snapshot INT NULL,
    cod_especialista_snapshot INT NULL,
    cod_usuario_creador_snapshot INT NULL,
    fecha_creacion_snapshot DATETIME NULL,
    cod_usuario_editor_snapshot INT NULL,
    fecha_edicion_snapshot DATETIME NULL,
    estado_legacy_snapshot VARCHAR(20) NULL,
    fecha_retiro_snapshot DATE NULL,
    fecha_entrega_snapshot DATE NULL,
    observacion_snapshot VARCHAR(150) NULL,
    colorimetro_snapshot VARCHAR(12) NULL,
    costo_snapshot INT NULL,
    fuente_hash CHAR(64) NOT NULL,
    estado_convalidacion VARCHAR(40) NOT NULL,
    estado_declarado VARCHAR(40) NULL,
    origen_estado VARCHAR(40) NOT NULL,
    cod_detalle_ventaFK INT NULL,
    cod_mecanico_dental_declaradoFK INT NULL,
    cod_tecnico_usuarioFK INT NULL,
    cod_custodio_actualFK INT NULL,
    cod_local_declaradoFK INT NULL,
    fecha_objetivo DATETIME NULL,
    fecha_retiro_declarada DATETIME NULL,
    fecha_entrega_declarada DATETIME NULL,
    fecha_situacion_declarada DATETIME NULL,
    justificacion_ultima VARCHAR(750) NULL,
    id_trabajo_laboratorioFK INT NULL,
    fecha_sincronizacion DATETIME NOT NULL,
    fecha_convalidacion DATETIME NULL,
    cod_usuarioFK_convalida INT NULL,
    fecha_actualizacion DATETIME NOT NULL,
    cod_usuarioFK_update INT NULL,
    version INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tlab_historico_legacy (cod_trabajo_mecanico_legacyFK),
    UNIQUE KEY uq_tlab_historico_trabajo (id_trabajo_laboratorioFK),
    KEY idx_tlab_historico_convalidacion (estado_convalidacion, estado_declarado),
    KEY idx_tlab_historico_venta (cod_venta_snapshot),
    KEY idx_tlab_historico_cliente (cod_cliente_snapshot),
    KEY idx_tlab_historico_detalle (cod_detalle_ventaFK),
    KEY idx_tlab_historico_mecanico (cod_mecanico_dental_declaradoFK),
    KEY idx_tlab_historico_tecnico (cod_tecnico_usuarioFK),
    KEY idx_tlab_historico_custodio (cod_custodio_actualFK),
    KEY idx_tlab_historico_local (cod_local_declaradoFK),
    KEY idx_tlab_historico_convalida (cod_usuarioFK_convalida),
    KEY idx_tlab_historico_update (cod_usuarioFK_update),
    CONSTRAINT fk_tlab_historico_legacy
        FOREIGN KEY (cod_trabajo_mecanico_legacyFK)
        REFERENCES trabajo_mecanico_dental (cod_trabajo_mecanico_dental),
    CONSTRAINT fk_tlab_historico_detalle
        FOREIGN KEY (cod_detalle_ventaFK) REFERENCES detalle_venta (cod_detalle),
    CONSTRAINT fk_tlab_historico_mecanico
        FOREIGN KEY (cod_mecanico_dental_declaradoFK)
        REFERENCES mecanico_dental (cod_mecanico_dental),
    CONSTRAINT fk_tlab_historico_tecnico
        FOREIGN KEY (cod_tecnico_usuarioFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_tlab_historico_custodio
        FOREIGN KEY (cod_custodio_actualFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_tlab_historico_local
        FOREIGN KEY (cod_local_declaradoFK) REFERENCES local (cod_local),
    CONSTRAINT fk_tlab_historico_trabajo
        FOREIGN KEY (id_trabajo_laboratorioFK) REFERENCES trabajo_laboratorio (id),
    CONSTRAINT fk_tlab_historico_convalida
        FOREIGN KEY (cod_usuarioFK_convalida) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_tlab_historico_update
        FOREIGN KEY (cod_usuarioFK_update) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Compatibilidad con instalaciones que ya sincronizaron antes de incorporar
-- los tres datos descriptivos como snapshots explicitos.
DROP PROCEDURE IF EXISTS telar_lab_hist_add_snapshot_column;
DELIMITER $$
CREATE PROCEDURE telar_lab_hist_add_snapshot_column(
    IN p_columna VARCHAR(64),
    IN p_definicion VARCHAR(1000)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'trabajo_laboratorio_historico'
          AND COLUMN_NAME = p_columna
    ) THEN
        SET @telar_lab_hist_sql = CONCAT(
            'ALTER TABLE `trabajo_laboratorio_historico` ADD COLUMN `',
            REPLACE(p_columna, '`', ''), '` ', p_definicion
        );
        PREPARE telar_lab_hist_stmt FROM @telar_lab_hist_sql;
        EXECUTE telar_lab_hist_stmt;
        DEALLOCATE PREPARE telar_lab_hist_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_hist_add_snapshot_column(
    'observacion_snapshot',
    'VARCHAR(150) NULL'
);
CALL telar_lab_hist_add_snapshot_column(
    'colorimetro_snapshot',
    'VARCHAR(12) NULL'
);
CALL telar_lab_hist_add_snapshot_column(
    'costo_snapshot',
    'INT NULL'
);

DROP PROCEDURE IF EXISTS telar_lab_hist_add_snapshot_column;

-- Auditoria append-only de cada sincronizacion, convalidacion, rectificacion
-- y promocion al flujo operativo.
CREATE TABLE IF NOT EXISTS trabajo_laboratorio_historico_evento (
    id INT NOT NULL AUTO_INCREMENT,
    id_historicoFK INT NOT NULL,
    tipo_evento VARCHAR(50) NOT NULL,
    estado_convalidacion_anterior VARCHAR(40) NULL,
    estado_convalidacion_nuevo VARCHAR(40) NULL,
    estado_declarado_anterior VARCHAR(40) NULL,
    estado_declarado_nuevo VARCHAR(40) NULL,
    cod_detalle_venta_anteriorFK INT NULL,
    cod_detalle_venta_nuevoFK INT NULL,
    cod_mecanico_dental_anteriorFK INT NULL,
    cod_mecanico_dental_nuevoFK INT NULL,
    cod_tecnico_usuario_anteriorFK INT NULL,
    cod_tecnico_usuario_nuevoFK INT NULL,
    cod_custodio_anteriorFK INT NULL,
    cod_custodio_nuevoFK INT NULL,
    cod_local_anteriorFK INT NULL,
    cod_local_nuevoFK INT NULL,
    fecha_servidor DATETIME NOT NULL,
    cod_usuario_actorFK INT NULL,
    justificacion VARCHAR(750) NULL,
    metadata_json MEDIUMTEXT NULL,
    clave_idempotencia VARCHAR(100) NOT NULL,
    payload_hash CHAR(64) NOT NULL,
    version_resultante INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tlab_hist_evento_idempotencia (id_historicoFK, clave_idempotencia),
    KEY idx_tlab_hist_evento_timeline (id_historicoFK, fecha_servidor, id),
    KEY idx_tlab_hist_evento_actor (cod_usuario_actorFK),
    KEY idx_tlab_hist_ev_detalle_ant (cod_detalle_venta_anteriorFK),
    KEY idx_tlab_hist_ev_detalle_nuevo (cod_detalle_venta_nuevoFK),
    KEY idx_tlab_hist_ev_mecanico_ant (cod_mecanico_dental_anteriorFK),
    KEY idx_tlab_hist_ev_mecanico_nuevo (cod_mecanico_dental_nuevoFK),
    KEY idx_tlab_hist_ev_tecnico_ant (cod_tecnico_usuario_anteriorFK),
    KEY idx_tlab_hist_ev_tecnico_nuevo (cod_tecnico_usuario_nuevoFK),
    KEY idx_tlab_hist_ev_custodio_ant (cod_custodio_anteriorFK),
    KEY idx_tlab_hist_ev_custodio_nuevo (cod_custodio_nuevoFK),
    KEY idx_tlab_hist_ev_local_ant (cod_local_anteriorFK),
    KEY idx_tlab_hist_ev_local_nuevo (cod_local_nuevoFK),
    CONSTRAINT fk_tlab_hist_evento_historico
        FOREIGN KEY (id_historicoFK) REFERENCES trabajo_laboratorio_historico (id),
    CONSTRAINT fk_tlab_hist_evento_actor
        FOREIGN KEY (cod_usuario_actorFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_tlab_hist_ev_detalle_ant
        FOREIGN KEY (cod_detalle_venta_anteriorFK) REFERENCES detalle_venta (cod_detalle),
    CONSTRAINT fk_tlab_hist_ev_detalle_nuevo
        FOREIGN KEY (cod_detalle_venta_nuevoFK) REFERENCES detalle_venta (cod_detalle),
    CONSTRAINT fk_tlab_hist_ev_mecanico_ant
        FOREIGN KEY (cod_mecanico_dental_anteriorFK)
        REFERENCES mecanico_dental (cod_mecanico_dental),
    CONSTRAINT fk_tlab_hist_ev_mecanico_nuevo
        FOREIGN KEY (cod_mecanico_dental_nuevoFK)
        REFERENCES mecanico_dental (cod_mecanico_dental),
    CONSTRAINT fk_tlab_hist_ev_tecnico_ant
        FOREIGN KEY (cod_tecnico_usuario_anteriorFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_tlab_hist_ev_tecnico_nuevo
        FOREIGN KEY (cod_tecnico_usuario_nuevoFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_tlab_hist_ev_custodio_ant
        FOREIGN KEY (cod_custodio_anteriorFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_tlab_hist_ev_custodio_nuevo
        FOREIGN KEY (cod_custodio_nuevoFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_tlab_hist_ev_local_ant
        FOREIGN KEY (cod_local_anteriorFK) REFERENCES local (cod_local),
    CONSTRAINT fk_tlab_hist_ev_local_nuevo
        FOREIGN KEY (cod_local_nuevoFK) REFERENCES local (cod_local)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- El hash usa todos los valores legacy, incluidos observacion, colorimetro y
-- costo, mas cliente y local derivados de venta.
-- HEX preserva los bytes y diferencia NULL de una cadena vacia.
-- En una estructura anterior el trigger aun no conoce estas columnas, por lo
-- que el backfill puede completarlas. En ejecuciones posteriores el WHERE no
-- actualiza filas iguales y la proteccion nueva permanece activa.

START TRANSACTION;

UPDATE trabajo_laboratorio_historico h
INNER JOIN trabajo_mecanico_dental t
    ON t.cod_trabajo_mecanico_dental = h.cod_trabajo_mecanico_legacyFK
SET h.observacion_snapshot = t.observacion,
    h.colorimetro_snapshot = t.colorimetro,
    h.costo_snapshot = t.costo
WHERE NOT (h.observacion_snapshot <=> t.observacion)
   OR NOT (h.colorimetro_snapshot <=> t.colorimetro)
   OR NOT (h.costo_snapshot <=> t.costo);

INSERT INTO trabajo_laboratorio_historico (
    cod_trabajo_mecanico_legacyFK,
    cod_venta_snapshot,
    cod_cliente_snapshot,
    cod_tipo_trabajo_snapshot,
    cod_mecanico_dental_snapshot,
    cod_local_snapshot,
    cod_especialista_snapshot,
    cod_usuario_creador_snapshot,
    fecha_creacion_snapshot,
    cod_usuario_editor_snapshot,
    fecha_edicion_snapshot,
    estado_legacy_snapshot,
    fecha_retiro_snapshot,
    fecha_entrega_snapshot,
    observacion_snapshot,
    colorimetro_snapshot,
    costo_snapshot,
    fuente_hash,
    estado_convalidacion,
    estado_declarado,
    origen_estado,
    cod_detalle_ventaFK,
    cod_mecanico_dental_declaradoFK,
    cod_tecnico_usuarioFK,
    cod_custodio_actualFK,
    cod_local_declaradoFK,
    fecha_objetivo,
    fecha_retiro_declarada,
    fecha_entrega_declarada,
    fecha_situacion_declarada,
    justificacion_ultima,
    id_trabajo_laboratorioFK,
    fecha_sincronizacion,
    fecha_convalidacion,
    cod_usuarioFK_convalida,
    fecha_actualizacion,
    cod_usuarioFK_update,
    version
)
SELECT
    t.cod_trabajo_mecanico_dental,
    t.cod_ventaFK,
    v.cod_clienteFK,
    t.cod_tipo_trabajoFK,
    t.cod_mecanicoDentalFK,
    t.cod_localFK,
    t.cod_especialistaFK,
    t.cod_usuarioFK_create,
    t.fecha_creacion,
    t.cod_usuarioFK_edit,
    t.fecha_edit,
    t.estado,
    NULLIF(t.fecha_retiro, '0000-00-00'),
    NULLIF(t.fecha_entrega, '0000-00-00'),
    t.observacion,
    t.colorimetro,
    t.costo,
    SHA2(
        CONCAT_WS(
            CHAR(31),
            'v1',
            IF(t.cod_trabajo_mecanico_dental IS NULL, 'N', CONCAT('V', HEX(CAST(t.cod_trabajo_mecanico_dental AS CHAR)))),
            IF(t.cod_ventaFK IS NULL, 'N', CONCAT('V', HEX(CAST(t.cod_ventaFK AS CHAR)))),
            IF(v.cod_clienteFK IS NULL, 'N', CONCAT('V', HEX(CAST(v.cod_clienteFK AS CHAR)))),
            IF(v.cod_local IS NULL, 'N', CONCAT('V', HEX(CAST(v.cod_local AS CHAR)))),
            IF(t.cod_tipo_trabajoFK IS NULL, 'N', CONCAT('V', HEX(CAST(t.cod_tipo_trabajoFK AS CHAR)))),
            IF(t.cod_mecanicoDentalFK IS NULL, 'N', CONCAT('V', HEX(CAST(t.cod_mecanicoDentalFK AS CHAR)))),
            IF(t.estado IS NULL, 'N', CONCAT('V', HEX(CAST(t.estado AS CHAR)))),
            IF(t.observacion IS NULL, 'N', CONCAT('V', HEX(CAST(t.observacion AS BINARY)))),
            IF(t.colorimetro IS NULL, 'N', CONCAT('V', HEX(CAST(t.colorimetro AS BINARY)))),
            IF(t.costo IS NULL, 'N', CONCAT('V', HEX(CAST(t.costo AS CHAR)))),
            IF(t.fecha_entrega IS NULL, 'N', CONCAT('V', HEX(CAST(t.fecha_entrega AS CHAR)))),
            IF(t.fecha_retiro IS NULL, 'N', CONCAT('V', HEX(CAST(t.fecha_retiro AS CHAR)))),
            IF(t.fecha_creacion IS NULL, 'N', CONCAT('V', HEX(CAST(t.fecha_creacion AS CHAR)))),
            IF(t.cod_usuarioFK_create IS NULL, 'N', CONCAT('V', HEX(CAST(t.cod_usuarioFK_create AS CHAR)))),
            IF(t.fecha_edit IS NULL, 'N', CONCAT('V', HEX(CAST(t.fecha_edit AS CHAR)))),
            IF(t.cod_usuarioFK_edit IS NULL, 'N', CONCAT('V', HEX(CAST(t.cod_usuarioFK_edit AS CHAR)))),
            IF(t.cod_especialistaFK IS NULL, 'N', CONCAT('V', HEX(CAST(t.cod_especialistaFK AS CHAR)))),
            IF(t.cod_localFK IS NULL, 'N', CONCAT('V', HEX(CAST(t.cod_localFK AS CHAR))))
        ),
        256
    ),
    CASE
        WHEN t.estado IN ('retirado', 'entregado', 'inactivo') THEN 'sincronizado_automatico'
        ELSE 'situacion_por_actualizar'
    END,
    CASE
        WHEN t.estado = 'retirado' THEN 'en_laboratorio'
        WHEN t.estado = 'entregado' THEN 'pendiente_revision'
        WHEN t.estado = 'inactivo' THEN 'cancelado'
        ELSE NULL
    END,
    CASE
        WHEN t.estado IN ('retirado', 'entregado', 'inactivo') THEN 'migracion_automatica'
        ELSE 'legacy_sin_definir'
    END,
    NULL,
    NULL,
    NULL,
    NULL,
    COALESCE(ll.cod_local, lv.cod_local),
    NULLIF(t.fecha_entrega, '0000-00-00'),
    NULLIF(t.fecha_retiro, '0000-00-00'),
    NULLIF(t.fecha_entrega, '0000-00-00'),
    CASE
        WHEN t.estado = 'retirado' THEN NULLIF(t.fecha_retiro, '0000-00-00')
        WHEN t.estado = 'entregado' THEN NULLIF(t.fecha_entrega, '0000-00-00')
        ELSE NULL
    END,
    NULL,
    NULL,
    NOW(),
    CASE
        WHEN t.estado IN ('retirado', 'entregado', 'inactivo') THEN NOW()
        ELSE NULL
    END,
    NULL,
    NOW(),
    NULL,
    1
FROM trabajo_mecanico_dental t
LEFT JOIN venta v ON v.cod_venta = t.cod_ventaFK
LEFT JOIN local ll ON ll.cod_local = t.cod_localFK
LEFT JOIN local lv ON lv.cod_local = v.cod_local
WHERE NOT EXISTS (
    SELECT 1
    FROM trabajo_laboratorio_historico h
    WHERE h.cod_trabajo_mecanico_legacyFK = t.cod_trabajo_mecanico_dental
)
AND NOT EXISTS (
    SELECT 1
    FROM trabajo_laboratorio tl
    WHERE tl.cod_trabajo_mecanico_legacyFK = t.cod_trabajo_mecanico_dental
);

INSERT INTO trabajo_laboratorio_historico_evento (
    id_historicoFK,
    tipo_evento,
    estado_convalidacion_anterior,
    estado_convalidacion_nuevo,
    estado_declarado_anterior,
    estado_declarado_nuevo,
    cod_detalle_venta_anteriorFK,
    cod_detalle_venta_nuevoFK,
    cod_mecanico_dental_anteriorFK,
    cod_mecanico_dental_nuevoFK,
    cod_tecnico_usuario_anteriorFK,
    cod_tecnico_usuario_nuevoFK,
    cod_custodio_anteriorFK,
    cod_custodio_nuevoFK,
    cod_local_anteriorFK,
    cod_local_nuevoFK,
    fecha_servidor,
    cod_usuario_actorFK,
    justificacion,
    metadata_json,
    clave_idempotencia,
    payload_hash,
    version_resultante
)
SELECT
    h.id,
    'sincronizacion_historica',
    NULL,
    h.estado_convalidacion,
    NULL,
    h.estado_declarado,
    NULL,
    h.cod_detalle_ventaFK,
    NULL,
    h.cod_mecanico_dental_declaradoFK,
    NULL,
    h.cod_tecnico_usuarioFK,
    NULL,
    h.cod_custodio_actualFK,
    NULL,
    h.cod_local_declaradoFK,
    h.fecha_sincronizacion,
    NULL,
    'Sincronizacion automatica desde el registro historico; no representa una declaracion personal.',
    '{"fuente":"trabajo_mecanico_dental","modo":"sincronizacion_automatica"}',
    CONCAT('sync-legacy-', h.cod_trabajo_mecanico_legacyFK),
    SHA2(
        CONCAT_WS(
            CHAR(31),
            'sincronizacion_historica',
            CAST(h.id AS CHAR),
            h.fuente_hash,
            h.estado_convalidacion,
            IF(h.estado_declarado IS NULL, 'N', CONCAT('V', h.estado_declarado)),
            IF(h.cod_detalle_ventaFK IS NULL, 'N', CONCAT('V', CAST(h.cod_detalle_ventaFK AS CHAR))),
            IF(h.cod_mecanico_dental_declaradoFK IS NULL, 'N', CONCAT('V', CAST(h.cod_mecanico_dental_declaradoFK AS CHAR))),
            IF(h.cod_tecnico_usuarioFK IS NULL, 'N', CONCAT('V', CAST(h.cod_tecnico_usuarioFK AS CHAR))),
            IF(h.cod_custodio_actualFK IS NULL, 'N', CONCAT('V', CAST(h.cod_custodio_actualFK AS CHAR))),
            IF(h.cod_local_declaradoFK IS NULL, 'N', CONCAT('V', CAST(h.cod_local_declaradoFK AS CHAR))),
            CAST(h.version AS CHAR)
        ),
        256
    ),
    h.version
FROM trabajo_laboratorio_historico h
WHERE NOT EXISTS (
    SELECT 1
    FROM trabajo_laboratorio_historico_evento e
    WHERE e.id_historicoFK = h.id
      AND e.clave_idempotencia = CONCAT('sync-legacy-', h.cod_trabajo_mecanico_legacyFK)
);

COMMIT;

-- Los eventos son append-only y la cabecera historica nunca se elimina. La
-- cabecera si puede actualizarse de forma versionada por el servicio autorizado.
DROP TRIGGER IF EXISTS trg_tlab_hist_evento_no_update;
DROP TRIGGER IF EXISTS trg_tlab_hist_evento_no_delete;
DROP TRIGGER IF EXISTS trg_tlab_historico_origen_no_update;
DROP TRIGGER IF EXISTS trg_tlab_historico_no_delete;

DELIMITER $$
CREATE TRIGGER trg_tlab_hist_evento_no_update
BEFORE UPDATE ON trabajo_laboratorio_historico_evento
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Los eventos historicos de laboratorio son inmutables';
END$$

CREATE TRIGGER trg_tlab_hist_evento_no_delete
BEFORE DELETE ON trabajo_laboratorio_historico_evento
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Los eventos historicos de laboratorio no se eliminan';
END$$

CREATE TRIGGER trg_tlab_historico_origen_no_update
BEFORE UPDATE ON trabajo_laboratorio_historico
FOR EACH ROW
BEGIN
    IF NOT (NEW.id <=> OLD.id)
       OR NOT (NEW.cod_trabajo_mecanico_legacyFK <=> OLD.cod_trabajo_mecanico_legacyFK)
       OR NOT (NEW.cod_venta_snapshot <=> OLD.cod_venta_snapshot)
       OR NOT (NEW.cod_cliente_snapshot <=> OLD.cod_cliente_snapshot)
       OR NOT (NEW.cod_tipo_trabajo_snapshot <=> OLD.cod_tipo_trabajo_snapshot)
       OR NOT (NEW.cod_mecanico_dental_snapshot <=> OLD.cod_mecanico_dental_snapshot)
       OR NOT (NEW.cod_local_snapshot <=> OLD.cod_local_snapshot)
       OR NOT (NEW.cod_especialista_snapshot <=> OLD.cod_especialista_snapshot)
       OR NOT (NEW.cod_usuario_creador_snapshot <=> OLD.cod_usuario_creador_snapshot)
       OR NOT (NEW.fecha_creacion_snapshot <=> OLD.fecha_creacion_snapshot)
       OR NOT (NEW.cod_usuario_editor_snapshot <=> OLD.cod_usuario_editor_snapshot)
       OR NOT (NEW.fecha_edicion_snapshot <=> OLD.fecha_edicion_snapshot)
       OR NOT (NEW.estado_legacy_snapshot <=> OLD.estado_legacy_snapshot)
       OR NOT (NEW.fecha_retiro_snapshot <=> OLD.fecha_retiro_snapshot)
       OR NOT (NEW.fecha_entrega_snapshot <=> OLD.fecha_entrega_snapshot)
       OR NOT (NEW.observacion_snapshot <=> OLD.observacion_snapshot)
       OR NOT (NEW.colorimetro_snapshot <=> OLD.colorimetro_snapshot)
       OR NOT (NEW.costo_snapshot <=> OLD.costo_snapshot)
       OR NOT (NEW.fuente_hash <=> OLD.fuente_hash)
       OR NOT (NEW.fecha_sincronizacion <=> OLD.fecha_sincronizacion) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El origen sincronizado del trabajo historico es inmutable';
    END IF;
END$$

CREATE TRIGGER trg_tlab_historico_no_delete
BEFORE DELETE ON trabajo_laboratorio_historico
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Los trabajos historicos se conservan y no se eliminan';
END$$
DELIMITER ;

-- ================================================================
-- REVERSION MANUAL (NO EJECUTAR CON DATOS CONVALIDADOS SIN RESPALDO)
-- ================================================================
-- DROP TRIGGER IF EXISTS trg_tlab_historico_no_delete;
-- DROP TRIGGER IF EXISTS trg_tlab_historico_origen_no_update;
-- DROP TRIGGER IF EXISTS trg_tlab_hist_evento_no_delete;
-- DROP TRIGGER IF EXISTS trg_tlab_hist_evento_no_update;
-- DROP TABLE IF EXISTS trabajo_laboratorio_historico_evento;
-- DROP TABLE IF EXISTS trabajo_laboratorio_historico;
-- ALTER TABLE trabajo_laboratorio DROP FOREIGN KEY fk_trabajo_laboratorio_especialista;
-- ALTER TABLE trabajo_laboratorio DROP INDEX idx_trabajo_laboratorio_especialista;
-- ALTER TABLE trabajo_laboratorio DROP COLUMN cod_especialistaFK;
