-- Clinident Salud / Sistema Telar
-- Hilo de custodia para trabajos de laboratorio dental.
--
-- Migracion ADITIVA e idempotente. Conserva los eventos y trabajos existentes,
-- no reescribe el historial inmutable y es compatible con MySQL 5.6+.

DROP PROCEDURE IF EXISTS telar_lab_hilo_add_column;
DELIMITER $$
CREATE PROCEDURE telar_lab_hilo_add_column(
    IN p_tabla VARCHAR(64),
    IN p_columna VARCHAR(64),
    IN p_definicion VARCHAR(1000)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE()
          AND TABLE_NAME=p_tabla
          AND COLUMN_NAME=p_columna
    ) THEN
        SET @telar_lab_hilo_sql = CONCAT(
            'ALTER TABLE `', REPLACE(p_tabla, '`', ''),
            '` ADD COLUMN `', REPLACE(p_columna, '`', ''), '` ', p_definicion
        );
        PREPARE telar_lab_hilo_stmt FROM @telar_lab_hilo_sql;
        EXECUTE telar_lab_hilo_stmt;
        DEALLOCATE PREPARE telar_lab_hilo_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_hilo_add_column(
    'trabajo_laboratorio',
    'id_evento_custodia_actualFK',
    'INT NULL AFTER `cod_custodio_actualFK`'
);
CALL telar_lab_hilo_add_column(
    'trabajo_laboratorio_evento',
    'id_evento_custodiaFK',
    'INT NULL AFTER `id_idempotenciaFK`'
);
CALL telar_lab_hilo_add_column(
    'trabajo_laboratorio_evento',
    'actor_nombre_snapshot',
    'VARCHAR(255) NULL AFTER `cod_usuario_actorFK`'
);
CALL telar_lab_hilo_add_column(
    'trabajo_laboratorio_evento',
    'actor_rol_snapshot',
    'VARCHAR(100) NULL AFTER `actor_nombre_snapshot`'
);
CALL telar_lab_hilo_add_column(
    'trabajo_laboratorio_evento',
    'local_nombre_snapshot',
    'VARCHAR(255) NULL AFTER `cod_localFK`'
);

DROP PROCEDURE IF EXISTS telar_lab_hilo_add_column;

DROP PROCEDURE IF EXISTS telar_lab_hilo_add_index;
DELIMITER $$
CREATE PROCEDURE telar_lab_hilo_add_index(
    IN p_tabla VARCHAR(64),
    IN p_indice VARCHAR(64),
    IN p_definicion VARCHAR(1000)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA=DATABASE()
          AND TABLE_NAME=p_tabla
          AND INDEX_NAME=p_indice
    ) THEN
        SET @telar_lab_hilo_sql = CONCAT(
            'ALTER TABLE `', REPLACE(p_tabla, '`', ''), '` ', p_definicion
        );
        PREPARE telar_lab_hilo_stmt FROM @telar_lab_hilo_sql;
        EXECUTE telar_lab_hilo_stmt;
        DEALLOCATE PREPARE telar_lab_hilo_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_hilo_add_index(
    'trabajo_laboratorio',
    'idx_trabajo_laboratorio_evento_custodia_actual',
    'ADD KEY `idx_trabajo_laboratorio_evento_custodia_actual` (`id_evento_custodia_actualFK`)'
);
CALL telar_lab_hilo_add_index(
    'trabajo_laboratorio_evento',
    'idx_trabajo_laboratorio_evento_custodia',
    'ADD KEY `idx_trabajo_laboratorio_evento_custodia` (`id_evento_custodiaFK`,`fecha_servidor`,`id`)'
);

DROP PROCEDURE IF EXISTS telar_lab_hilo_add_index;

-- Se recupera solamente el puntero del trabajo. Los eventos historicos no se
-- actualizan porque son inmutables y no corresponde inventarles snapshots.
UPDATE trabajo_laboratorio tl
SET id_evento_custodia_actualFK=(
    SELECT MAX(ev.id)
    FROM trabajo_laboratorio_evento ev
    WHERE ev.id_trabajoFK=tl.id
      AND ev.cod_custodio_nuevoFK=tl.cod_custodio_actualFK
      AND ev.tipo_evento IN (
          'trabajo_iniciado',
          'recepcion_mecanico_confirmada',
          'devolucion_confirmada',
          'hilo_tomado',
          'custodia_rectificada',
          'instalacion_registrada'
      )
)
WHERE tl.id_evento_custodia_actualFK IS NULL;

DROP PROCEDURE IF EXISTS telar_lab_hilo_add_fk;
DELIMITER $$
CREATE PROCEDURE telar_lab_hilo_add_fk(
    IN p_tabla VARCHAR(64),
    IN p_restriccion VARCHAR(64),
    IN p_definicion VARCHAR(1000)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA=DATABASE()
          AND TABLE_NAME=p_tabla
          AND CONSTRAINT_NAME=p_restriccion
    ) THEN
        SET @telar_lab_hilo_sql = CONCAT(
            'ALTER TABLE `', REPLACE(p_tabla, '`', ''), '` ', p_definicion
        );
        PREPARE telar_lab_hilo_stmt FROM @telar_lab_hilo_sql;
        EXECUTE telar_lab_hilo_stmt;
        DEALLOCATE PREPARE telar_lab_hilo_stmt;
    END IF;
END$$
DELIMITER ;

CALL telar_lab_hilo_add_fk(
    'trabajo_laboratorio',
    'fk_trabajo_laboratorio_evento_custodia_actual',
    'ADD CONSTRAINT `fk_trabajo_laboratorio_evento_custodia_actual` FOREIGN KEY (`id_evento_custodia_actualFK`) REFERENCES `trabajo_laboratorio_evento` (`id`)'
);
CALL telar_lab_hilo_add_fk(
    'trabajo_laboratorio_evento',
    'fk_trabajo_laboratorio_evento_custodia',
    'ADD CONSTRAINT `fk_trabajo_laboratorio_evento_custodia` FOREIGN KEY (`id_evento_custodiaFK`) REFERENCES `trabajo_laboratorio_evento` (`id`)'
);

DROP PROCEDURE IF EXISTS telar_lab_hilo_add_fk;

SELECT
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='trabajo_laboratorio'
       AND COLUMN_NAME='id_evento_custodia_actualFK') AS columna_puntero_instalada,
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='trabajo_laboratorio_evento'
       AND COLUMN_NAME IN ('id_evento_custodiaFK','actor_nombre_snapshot','actor_rol_snapshot','local_nombre_snapshot'))
       AS columnas_evento_instaladas,
    (SELECT COUNT(*) FROM trabajo_laboratorio
     WHERE id_evento_custodia_actualFK IS NOT NULL) AS trabajos_con_puntero_recuperado;

-- Reversion fisica controlada (NO ejecutar en operacion normal):
-- 1. ALTER TABLE trabajo_laboratorio DROP FOREIGN KEY fk_trabajo_laboratorio_evento_custodia_actual;
-- 2. ALTER TABLE trabajo_laboratorio_evento DROP FOREIGN KEY fk_trabajo_laboratorio_evento_custodia;
-- 3. Eliminar indices y columnas solamente despues de respaldar los eventos
--    nuevos. La reversion funcional recomendada es conservar las columnas.
