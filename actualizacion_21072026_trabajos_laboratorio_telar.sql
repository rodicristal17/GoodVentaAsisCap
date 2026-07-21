-- Sistema Telar / Clinident Salud
-- Nucleo de trabajos de laboratorio dental.
--
-- Migracion ADITIVA. No transforma ni elimina trabajo_mecanico_dental.
-- Compatible con MySQL 5.6+ y PHP 7.2.
-- Aplicar primero en un respaldo verificado. El bloque de reversion del final
-- esta comentado deliberadamente para evitar perdidas accidentales.

SET @telar_lab_db = DATABASE();

DROP PROCEDURE IF EXISTS telar_lab_add_column;
DELIMITER $$
CREATE PROCEDURE telar_lab_add_column(
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
        SET @telar_lab_sql = CONCAT(
            'ALTER TABLE `', REPLACE(p_tabla, '`', ''),
            '` ADD COLUMN `', REPLACE(p_columna, '`', ''), '` ', p_definicion
        );
        PREPARE telar_lab_stmt FROM @telar_lab_sql;
        EXECUTE telar_lab_stmt;
        DEALLOCATE PREPARE telar_lab_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_add_column(
    'categoria',
    'requiere_laboratorio',
    'TINYINT(1) NOT NULL DEFAULT 0'
);
CALL telar_lab_add_column(
    'categoria',
    'modo_individualizacion',
    'VARCHAR(30) NULL'
);
CALL telar_lab_add_column(
    'producto',
    'requiere_laboratorio',
    'TINYINT(1) NULL'
);
CALL telar_lab_add_column(
    'producto',
    'modo_individualizacion',
    'VARCHAR(30) NULL'
);
CALL telar_lab_add_column(
    'mecanico_dental',
    'cod_usuarioFK',
    'INT NULL'
);

DROP PROCEDURE IF EXISTS telar_lab_add_column;

DROP PROCEDURE IF EXISTS telar_lab_add_index;
DELIMITER $$
CREATE PROCEDURE telar_lab_add_index(
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
        SET @telar_lab_sql = CONCAT(
            'ALTER TABLE `', REPLACE(p_tabla, '`', ''), '` ', p_definicion
        );
        PREPARE telar_lab_stmt FROM @telar_lab_sql;
        EXECUTE telar_lab_stmt;
        DEALLOCATE PREPARE telar_lab_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_add_index(
    'mecanico_dental',
    'uq_mecanico_dental_usuario',
    'ADD UNIQUE KEY `uq_mecanico_dental_usuario` (`cod_usuarioFK`)'
);

DROP PROCEDURE IF EXISTS telar_lab_add_index;

DROP PROCEDURE IF EXISTS telar_lab_add_fk;
DELIMITER $$
CREATE PROCEDURE telar_lab_add_fk(
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
        SET @telar_lab_sql = CONCAT(
            'ALTER TABLE `', REPLACE(p_tabla, '`', ''), '` ', p_definicion
        );
        PREPARE telar_lab_stmt FROM @telar_lab_sql;
        EXECUTE telar_lab_stmt;
        DEALLOCATE PREPARE telar_lab_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_add_fk(
    'mecanico_dental',
    'fk_mecanico_dental_usuario_formal',
    'ADD CONSTRAINT `fk_mecanico_dental_usuario_formal` FOREIGN KEY (`cod_usuarioFK`) REFERENCES `usuario` (`cod_usuario`)'
);

CREATE TABLE IF NOT EXISTS trabajo_laboratorio (
    id INT NOT NULL AUTO_INCREMENT,
    codigo_visible VARCHAR(100) NULL,
    cod_trabajo_mecanico_legacyFK INT NULL,
    cod_ventaFK INT NOT NULL,
    numero_venta_snapshot VARCHAR(45) NOT NULL,
    sigla_local_snapshot VARCHAR(10) NOT NULL,
    cod_detalle_ventaFK INT NOT NULL,
    cod_detalle_activo_unico INT NULL,
    cod_clienteFK INT NOT NULL,
    cod_productoFK VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    cod_tipo_trabajoFK INT NULL,
    cod_consulta_origenFK INT NULL,
    cod_evolucion_origenFK INT NULL,
    cod_interConsultaFK INT NOT NULL,
    cod_localFK INT NOT NULL,
    cod_mecanico_dentalFK INT NOT NULL,
    cod_tecnico_usuarioFK INT NOT NULL,
    cod_custodio_actualFK INT NOT NULL,
    ciclo_actual INT NOT NULL DEFAULT 1,
    estado_derivado VARCHAR(40) NOT NULL,
    fecha_objetivo DATETIME NOT NULL,
    fecha_retiro DATETIME NULL,
    fecha_entrega DATETIME NULL,
    id_transferencia_pendienteFK INT NULL,
    colorimetro VARCHAR(30) NULL,
    instrucciones VARCHAR(1000) NULL,
    costo_estimado BIGINT NULL,
    version INT NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL,
    cod_usuarioFK_create INT NOT NULL,
    fecha_actualizacion DATETIME NOT NULL,
    cod_usuarioFK_update INT NOT NULL,
    fecha_completado DATETIME NULL,
    fecha_instalado DATETIME NULL,
    fecha_cancelado DATETIME NULL,
    motivo_cancelacion VARCHAR(500) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_trabajo_laboratorio_codigo (codigo_visible),
    UNIQUE KEY uq_trabajo_laboratorio_detalle_activo (cod_detalle_activo_unico),
    UNIQUE KEY uq_trabajo_laboratorio_legacy (cod_trabajo_mecanico_legacyFK),
    KEY idx_trabajo_laboratorio_venta (cod_ventaFK),
    KEY idx_trabajo_laboratorio_detalle (cod_detalle_ventaFK),
    KEY idx_trabajo_laboratorio_cliente (cod_clienteFK),
    KEY idx_trabajo_laboratorio_local_estado (cod_localFK, estado_derivado),
    KEY idx_trabajo_laboratorio_tecnico_estado (cod_tecnico_usuarioFK, estado_derivado),
    KEY idx_trabajo_laboratorio_custodio (cod_custodio_actualFK),
    KEY idx_trabajo_laboratorio_hilo (cod_interConsultaFK),
    KEY idx_trabajo_laboratorio_objetivo (fecha_objetivo),
    KEY idx_trabajo_laboratorio_transferencia (id_transferencia_pendienteFK),
    CONSTRAINT fk_trabajo_laboratorio_venta FOREIGN KEY (cod_ventaFK) REFERENCES venta (cod_venta),
    CONSTRAINT fk_trabajo_laboratorio_detalle FOREIGN KEY (cod_detalle_ventaFK) REFERENCES detalle_venta (cod_detalle),
    CONSTRAINT fk_trabajo_laboratorio_cliente FOREIGN KEY (cod_clienteFK) REFERENCES cliente (cod_cliente),
    CONSTRAINT fk_trabajo_laboratorio_producto FOREIGN KEY (cod_productoFK) REFERENCES producto (cod_producto),
    CONSTRAINT fk_trabajo_laboratorio_tipo FOREIGN KEY (cod_tipo_trabajoFK) REFERENCES tipo_trabajo_mecanico_dental (cod_tipo_trabajo_mecanico_dental),
    CONSTRAINT fk_trabajo_laboratorio_consulta FOREIGN KEY (cod_consulta_origenFK) REFERENCES consulta (cod_consulta),
    CONSTRAINT fk_trabajo_laboratorio_evolucion FOREIGN KEY (cod_evolucion_origenFK) REFERENCES evoluciontratamiento (cod_evoluciontratamiento),
    CONSTRAINT fk_trabajo_laboratorio_hilo FOREIGN KEY (cod_interConsultaFK) REFERENCES interconsulta (cod_interConsulta),
    CONSTRAINT fk_trabajo_laboratorio_local FOREIGN KEY (cod_localFK) REFERENCES local (cod_local),
    CONSTRAINT fk_trabajo_laboratorio_mecanico FOREIGN KEY (cod_mecanico_dentalFK) REFERENCES mecanico_dental (cod_mecanico_dental),
    CONSTRAINT fk_trabajo_laboratorio_tecnico FOREIGN KEY (cod_tecnico_usuarioFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_custodio FOREIGN KEY (cod_custodio_actualFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_creador FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_editor FOREIGN KEY (cod_usuarioFK_update) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS trabajo_laboratorio_ciclo (
    id INT NOT NULL AUTO_INCREMENT,
    id_trabajoFK INT NOT NULL,
    numero_ciclo INT NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    motivo VARCHAR(80) NULL,
    justificacion VARCHAR(500) NULL,
    fecha_objetivo DATETIME NOT NULL,
    cod_usuario_solicitanteFK INT NOT NULL,
    fecha_creacion DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_trabajo_laboratorio_ciclo (id_trabajoFK, numero_ciclo),
    KEY idx_trabajo_laboratorio_ciclo_usuario (cod_usuario_solicitanteFK),
    CONSTRAINT fk_trabajo_laboratorio_ciclo_trabajo FOREIGN KEY (id_trabajoFK) REFERENCES trabajo_laboratorio (id),
    CONSTRAINT fk_trabajo_laboratorio_ciclo_usuario FOREIGN KEY (cod_usuario_solicitanteFK) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS trabajo_laboratorio_transferencia (
    id INT NOT NULL AUTO_INCREMENT,
    id_trabajoFK INT NOT NULL,
    id_cicloFK INT NOT NULL,
    tipo VARCHAR(35) NOT NULL,
    cod_custodio_anteriorFK INT NOT NULL,
    cod_remitenteFK INT NOT NULL,
    cod_destinatario_previstoFK INT NOT NULL,
    cod_local_origenFK INT NOT NULL,
    cod_local_destinoFK INT NOT NULL,
    observacion VARCHAR(500) NULL,
    cod_usuarioFK_create INT NOT NULL,
    fecha_creacion DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_trabajo_laboratorio_transferencia_trabajo (id_trabajoFK, fecha_creacion),
    KEY idx_trabajo_laboratorio_transferencia_destino (cod_destinatario_previstoFK, fecha_creacion),
    CONSTRAINT fk_trabajo_laboratorio_transferencia_trabajo FOREIGN KEY (id_trabajoFK) REFERENCES trabajo_laboratorio (id),
    CONSTRAINT fk_trabajo_laboratorio_transferencia_ciclo FOREIGN KEY (id_cicloFK) REFERENCES trabajo_laboratorio_ciclo (id),
    CONSTRAINT fk_trabajo_laboratorio_transferencia_custodio FOREIGN KEY (cod_custodio_anteriorFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_transferencia_remitente FOREIGN KEY (cod_remitenteFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_transferencia_destinatario FOREIGN KEY (cod_destinatario_previstoFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_transferencia_local_origen FOREIGN KEY (cod_local_origenFK) REFERENCES local (cod_local),
    CONSTRAINT fk_trabajo_laboratorio_transferencia_local_destino FOREIGN KEY (cod_local_destinoFK) REFERENCES local (cod_local),
    CONSTRAINT fk_trabajo_laboratorio_transferencia_creador FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- La referencia pendiente se agrega una vez que ambas tablas existen.
CALL telar_lab_add_fk(
    'trabajo_laboratorio',
    'fk_trabajo_laboratorio_transferencia_pendiente',
    'ADD CONSTRAINT `fk_trabajo_laboratorio_transferencia_pendiente` FOREIGN KEY (`id_transferencia_pendienteFK`) REFERENCES `trabajo_laboratorio_transferencia` (`id`)'
);

DROP PROCEDURE IF EXISTS telar_lab_add_fk;

CREATE TABLE IF NOT EXISTS trabajo_laboratorio_idempotencia (
    id INT NOT NULL AUTO_INCREMENT,
    cod_usuarioFK INT NOT NULL,
    accion VARCHAR(50) NOT NULL,
    clave VARCHAR(100) NOT NULL,
    payload_hash CHAR(64) NOT NULL,
    id_trabajoFK INT NULL,
    estado VARCHAR(20) NOT NULL,
    respuesta_json MEDIUMTEXT NULL,
    fecha_creacion DATETIME NOT NULL,
    fecha_completado DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_trabajo_laboratorio_idempotencia (cod_usuarioFK, accion, clave),
    KEY idx_trabajo_laboratorio_idempotencia_trabajo (id_trabajoFK),
    CONSTRAINT fk_trabajo_laboratorio_idempotencia_usuario FOREIGN KEY (cod_usuarioFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_idempotencia_trabajo FOREIGN KEY (id_trabajoFK) REFERENCES trabajo_laboratorio (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS trabajo_laboratorio_evento (
    id INT NOT NULL AUTO_INCREMENT,
    id_trabajoFK INT NOT NULL,
    id_cicloFK INT NOT NULL,
    id_transferenciaFK INT NULL,
    id_idempotenciaFK INT NOT NULL,
    cod_consulta_origenFK INT NULL,
    cod_evolucion_origenFK INT NULL,
    tipo_evento VARCHAR(45) NOT NULL,
    cod_usuario_actorFK INT NOT NULL,
    cod_custodio_anteriorFK INT NULL,
    cod_custodio_nuevoFK INT NULL,
    cod_remitenteFK INT NULL,
    cod_destinatario_previstoFK INT NULL,
    cod_localFK INT NOT NULL,
    fecha_servidor DATETIME NOT NULL,
    observacion VARCHAR(750) NULL,
    metadata_json MEDIUMTEXT NULL,
    version_resultante INT NOT NULL,
    cod_interConsultaFK INT NOT NULL,
    cod_mensaje_hiloFK INT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_trabajo_laboratorio_evento_idempotencia (id_idempotenciaFK),
    KEY idx_trabajo_laboratorio_evento_timeline (id_trabajoFK, fecha_servidor, id),
    KEY idx_trabajo_laboratorio_evento_ciclo (id_cicloFK, fecha_servidor),
    KEY idx_trabajo_laboratorio_evento_transferencia (id_transferenciaFK),
    KEY idx_trabajo_laboratorio_evento_hilo (cod_interConsultaFK, cod_mensaje_hiloFK),
    CONSTRAINT fk_trabajo_laboratorio_evento_trabajo FOREIGN KEY (id_trabajoFK) REFERENCES trabajo_laboratorio (id),
    CONSTRAINT fk_trabajo_laboratorio_evento_ciclo FOREIGN KEY (id_cicloFK) REFERENCES trabajo_laboratorio_ciclo (id),
    CONSTRAINT fk_trabajo_laboratorio_evento_transferencia FOREIGN KEY (id_transferenciaFK) REFERENCES trabajo_laboratorio_transferencia (id),
    CONSTRAINT fk_trabajo_laboratorio_evento_idempotencia FOREIGN KEY (id_idempotenciaFK) REFERENCES trabajo_laboratorio_idempotencia (id),
    CONSTRAINT fk_trabajo_laboratorio_evento_consulta FOREIGN KEY (cod_consulta_origenFK) REFERENCES consulta (cod_consulta),
    CONSTRAINT fk_trabajo_laboratorio_evento_evolucion FOREIGN KEY (cod_evolucion_origenFK) REFERENCES evoluciontratamiento (cod_evoluciontratamiento),
    CONSTRAINT fk_trabajo_laboratorio_evento_actor FOREIGN KEY (cod_usuario_actorFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_evento_custodio_anterior FOREIGN KEY (cod_custodio_anteriorFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_evento_custodio_nuevo FOREIGN KEY (cod_custodio_nuevoFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_evento_remitente FOREIGN KEY (cod_remitenteFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_evento_destinatario FOREIGN KEY (cod_destinatario_previstoFK) REFERENCES usuario (cod_usuario),
    CONSTRAINT fk_trabajo_laboratorio_evento_local FOREIGN KEY (cod_localFK) REFERENCES local (cod_local),
    CONSTRAINT fk_trabajo_laboratorio_evento_hilo FOREIGN KEY (cod_interConsultaFK) REFERENCES interconsulta (cod_interConsulta),
    CONSTRAINT fk_trabajo_laboratorio_evento_mensaje FOREIGN KEY (cod_mensaje_hiloFK) REFERENCES mensaje (cod_mensaje)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS trabajo_laboratorio_ubicacion (
    id INT NOT NULL AUTO_INCREMENT,
    id_trabajoFK INT NOT NULL,
    id_odontograma_link_origenFK INT NULL,
    pieza VARCHAR(5) NULL,
    piezas_json TEXT NULL,
    superficies_json TEXT NULL,
    denticion VARCHAR(20) NULL,
    arcada VARCHAR(30) NULL,
    cuadrante VARCHAR(30) NULL,
    boca_completa TINYINT(1) NOT NULL DEFAULT 0,
    alcance_odontologico VARCHAR(40) NULL,
    fecha_creacion DATETIME NOT NULL,
    cod_usuarioFK_create INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_trabajo_laboratorio_ubicacion_origen (id_trabajoFK, id_odontograma_link_origenFK),
    KEY idx_trabajo_laboratorio_ubicacion_trabajo (id_trabajoFK),
    CONSTRAINT fk_trabajo_laboratorio_ubicacion_trabajo FOREIGN KEY (id_trabajoFK) REFERENCES trabajo_laboratorio (id),
    CONSTRAINT fk_trabajo_laboratorio_ubicacion_link FOREIGN KEY (id_odontograma_link_origenFK) REFERENCES odontograma_tratamiento_links (id),
    CONSTRAINT fk_trabajo_laboratorio_ubicacion_usuario FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS trabajo_laboratorio_media (
    id INT NOT NULL AUTO_INCREMENT,
    id_trabajoFK INT NOT NULL,
    id_cicloFK INT NOT NULL,
    id_eventoFK INT NOT NULL,
    cod_usuarioFK_upload INT NOT NULL,
    tipo_media VARCHAR(30) NOT NULL,
    ruta_relativa VARCHAR(500) NOT NULL,
    miniatura_relativa VARCHAR(500) NULL,
    nombre_original VARCHAR(255) NULL,
    mime VARCHAR(100) NOT NULL,
    extension VARCHAR(10) NOT NULL,
    tamano_bytes INT NOT NULL,
    sha256 CHAR(64) NOT NULL,
    descripcion VARCHAR(255) NULL,
    visibilidad VARCHAR(30) NOT NULL DEFAULT 'autorizados_trabajo',
    fecha_creacion DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_trabajo_laboratorio_media_evento_hash (id_eventoFK, sha256),
    KEY idx_trabajo_laboratorio_media_trabajo (id_trabajoFK, fecha_creacion),
    KEY idx_trabajo_laboratorio_media_ciclo (id_cicloFK, fecha_creacion),
    CONSTRAINT fk_trabajo_laboratorio_media_trabajo FOREIGN KEY (id_trabajoFK) REFERENCES trabajo_laboratorio (id),
    CONSTRAINT fk_trabajo_laboratorio_media_ciclo FOREIGN KEY (id_cicloFK) REFERENCES trabajo_laboratorio_ciclo (id),
    CONSTRAINT fk_trabajo_laboratorio_media_evento FOREIGN KEY (id_eventoFK) REFERENCES trabajo_laboratorio_evento (id),
    CONSTRAINT fk_trabajo_laboratorio_media_usuario FOREIGN KEY (cod_usuarioFK_upload) REFERENCES usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Permisos nuevos. No se conceden automaticamente a ningun usuario.
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'VERTRABAJOSLABORATORIO', 'Ver', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='VERTRABAJOSLABORATORIO');
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'CREARTRABAJOLABORATORIO', 'Crear', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='CREARTRABAJOLABORATORIO');
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'ENTREGARTRABAJOLABORATORIO', 'Entregar', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='ENTREGARTRABAJOLABORATORIO');
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'RECIBIRTRABAJOLABORATORIO', 'Confirmar recepcion', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='RECIBIRTRABAJOLABORATORIO');
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'EVIDENCIATRABAJOLABORATORIO', 'Agregar evidencia', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='EVIDENCIATRABAJOLABORATORIO');
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'APROBARTRABAJOLABORATORIO', 'Aprobar', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='APROBARTRABAJOLABORATORIO');
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'AJUSTARTRABAJOLABORATORIO', 'Solicitar ajuste', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='AJUSTARTRABAJOLABORATORIO');
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'INSTALARTRABAJOLABORATORIO', 'Registrar instalacion', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='INSTALARTRABAJOLABORATORIO');
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'CANCELARTRABAJOLABORATORIO', 'Cancelar con motivo', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='CANCELARTRABAJOLABORATORIO');
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'AUDITARTRABAJOLABORATORIO', 'Auditar todos los locales', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='AUDITARTRABAJOLABORATORIO');
INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 5, 'TRABAJOS LABORATORIO', 'GESTIONARTECNICOSLABORATORIO', 'Vincular tecnicos a usuarios', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='GESTIONARTECNICOSLABORATORIO');

-- Los ciclos, transferencias, eventos, ubicaciones y evidencias son historicos.
-- Las correcciones se registran como eventos nuevos; no se sobrescriben filas.
DROP TRIGGER IF EXISTS trg_telar_lab_ciclo_no_update;
DROP TRIGGER IF EXISTS trg_telar_lab_ciclo_no_delete;
DROP TRIGGER IF EXISTS trg_telar_lab_transfer_no_update;
DROP TRIGGER IF EXISTS trg_telar_lab_transfer_no_delete;
DROP TRIGGER IF EXISTS trg_telar_lab_evento_no_update;
DROP TRIGGER IF EXISTS trg_telar_lab_evento_no_delete;
DROP TRIGGER IF EXISTS trg_telar_lab_ubicacion_no_update;
DROP TRIGGER IF EXISTS trg_telar_lab_ubicacion_no_delete;
DROP TRIGGER IF EXISTS trg_telar_lab_media_no_update;
DROP TRIGGER IF EXISTS trg_telar_lab_media_no_delete;
DROP TRIGGER IF EXISTS trg_telar_lab_trabajo_no_delete;

DELIMITER $$
CREATE TRIGGER trg_telar_lab_ciclo_no_update BEFORE UPDATE ON trabajo_laboratorio_ciclo
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Los ciclos de laboratorio son inmutables'; END$$
CREATE TRIGGER trg_telar_lab_ciclo_no_delete BEFORE DELETE ON trabajo_laboratorio_ciclo
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Los ciclos de laboratorio no se eliminan'; END$$
CREATE TRIGGER trg_telar_lab_transfer_no_update BEFORE UPDATE ON trabajo_laboratorio_transferencia
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Las transferencias de laboratorio son inmutables'; END$$
CREATE TRIGGER trg_telar_lab_transfer_no_delete BEFORE DELETE ON trabajo_laboratorio_transferencia
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Las transferencias de laboratorio no se eliminan'; END$$
CREATE TRIGGER trg_telar_lab_evento_no_update BEFORE UPDATE ON trabajo_laboratorio_evento
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Los eventos de laboratorio son inmutables'; END$$
CREATE TRIGGER trg_telar_lab_evento_no_delete BEFORE DELETE ON trabajo_laboratorio_evento
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Los eventos de laboratorio no se eliminan'; END$$
CREATE TRIGGER trg_telar_lab_ubicacion_no_update BEFORE UPDATE ON trabajo_laboratorio_ubicacion
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Las ubicaciones historicas de laboratorio son inmutables'; END$$
CREATE TRIGGER trg_telar_lab_ubicacion_no_delete BEFORE DELETE ON trabajo_laboratorio_ubicacion
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Las ubicaciones historicas de laboratorio no se eliminan'; END$$
CREATE TRIGGER trg_telar_lab_media_no_update BEFORE UPDATE ON trabajo_laboratorio_media
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Las evidencias de laboratorio son inmutables'; END$$
CREATE TRIGGER trg_telar_lab_media_no_delete BEFORE DELETE ON trabajo_laboratorio_media
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Las evidencias de laboratorio no se eliminan'; END$$
CREATE TRIGGER trg_telar_lab_trabajo_no_delete BEFORE DELETE ON trabajo_laboratorio
FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Los trabajos de laboratorio se cancelan, no se eliminan'; END$$
DELIMITER ;

-- ================================================================
-- REVERSION MANUAL (NO EJECUTAR CON DATOS OPERATIVOS SIN RESPALDO)
-- ================================================================
-- DROP TRIGGER IF EXISTS trg_telar_lab_trabajo_no_delete;
-- DROP TRIGGER IF EXISTS trg_telar_lab_media_no_delete;
-- DROP TRIGGER IF EXISTS trg_telar_lab_media_no_update;
-- DROP TRIGGER IF EXISTS trg_telar_lab_ubicacion_no_delete;
-- DROP TRIGGER IF EXISTS trg_telar_lab_ubicacion_no_update;
-- DROP TRIGGER IF EXISTS trg_telar_lab_evento_no_delete;
-- DROP TRIGGER IF EXISTS trg_telar_lab_evento_no_update;
-- DROP TRIGGER IF EXISTS trg_telar_lab_transfer_no_delete;
-- DROP TRIGGER IF EXISTS trg_telar_lab_transfer_no_update;
-- DROP TRIGGER IF EXISTS trg_telar_lab_ciclo_no_delete;
-- DROP TRIGGER IF EXISTS trg_telar_lab_ciclo_no_update;
-- ALTER TABLE trabajo_laboratorio DROP FOREIGN KEY fk_trabajo_laboratorio_transferencia_pendiente;
-- DROP TABLE IF EXISTS trabajo_laboratorio_media;
-- DROP TABLE IF EXISTS trabajo_laboratorio_ubicacion;
-- DROP TABLE IF EXISTS trabajo_laboratorio_evento;
-- DROP TABLE IF EXISTS trabajo_laboratorio_idempotencia;
-- DROP TABLE IF EXISTS trabajo_laboratorio_transferencia;
-- DROP TABLE IF EXISTS trabajo_laboratorio_ciclo;
-- DROP TABLE IF EXISTS trabajo_laboratorio;
-- ALTER TABLE mecanico_dental DROP FOREIGN KEY fk_mecanico_dental_usuario_formal;
-- ALTER TABLE mecanico_dental DROP INDEX uq_mecanico_dental_usuario;
-- ALTER TABLE mecanico_dental DROP COLUMN cod_usuarioFK;
-- ALTER TABLE producto DROP COLUMN modo_individualizacion, DROP COLUMN requiere_laboratorio;
-- ALTER TABLE categoria DROP COLUMN modo_individualizacion, DROP COLUMN requiere_laboratorio;
-- DELETE FROM listadodeacceso WHERE codigo IN (
--   'VERTRABAJOSLABORATORIO','CREARTRABAJOLABORATORIO','ENTREGARTRABAJOLABORATORIO',
--   'RECIBIRTRABAJOLABORATORIO','EVIDENCIATRABAJOLABORATORIO','APROBARTRABAJOLABORATORIO',
--   'AJUSTARTRABAJOLABORATORIO','INSTALARTRABAJOLABORATORIO','CANCELARTRABAJOLABORATORIO',
--   'AUDITARTRABAJOLABORATORIO','GESTIONARTECNICOSLABORATORIO'
-- );
