;
-- Clinident Salud / Sistema Telar
-- Hilo de custodia para trabajos de laboratorio dental.
--
-- Variante para hosting MariaDB 10.6+ ejecutada desde la pestana SQL de
-- phpMyAdmin. El punto y coma inicial cierra la consulta SELECT que phpMyAdmin
-- puede dejar precargada al abrir la pestana SQL desde una tabla.
--
-- Migracion ADITIVA e idempotente. No requiere CREATE ROUTINE ni DELIMITER.
-- Conserva los eventos y trabajos existentes y no reescribe el historial
-- inmutable.

ALTER TABLE `trabajo_laboratorio`
    ADD COLUMN IF NOT EXISTS `id_evento_custodia_actualFK`
        INT NULL AFTER `cod_custodio_actualFK`;

ALTER TABLE `trabajo_laboratorio_evento`
    ADD COLUMN IF NOT EXISTS `id_evento_custodiaFK`
        INT NULL AFTER `id_idempotenciaFK`,
    ADD COLUMN IF NOT EXISTS `actor_nombre_snapshot`
        VARCHAR(255) NULL AFTER `cod_usuario_actorFK`,
    ADD COLUMN IF NOT EXISTS `actor_rol_snapshot`
        VARCHAR(100) NULL AFTER `actor_nombre_snapshot`,
    ADD COLUMN IF NOT EXISTS `local_nombre_snapshot`
        VARCHAR(255) NULL AFTER `cod_localFK`;

ALTER TABLE `trabajo_laboratorio`
    ADD INDEX IF NOT EXISTS `idx_trabajo_laboratorio_evento_custodia_actual`
        (`id_evento_custodia_actualFK`);

ALTER TABLE `trabajo_laboratorio_evento`
    ADD INDEX IF NOT EXISTS `idx_trabajo_laboratorio_evento_custodia`
        (`id_evento_custodiaFK`, `fecha_servidor`, `id`);

-- Se recupera solamente el puntero del trabajo. Los eventos historicos no se
-- actualizan porque son inmutables y no corresponde inventarles snapshots.
UPDATE `trabajo_laboratorio` AS tl
SET tl.`id_evento_custodia_actualFK` = (
    SELECT MAX(ev.`id`)
    FROM `trabajo_laboratorio_evento` AS ev
    WHERE ev.`id_trabajoFK` = tl.`id`
      AND ev.`cod_custodio_nuevoFK` = tl.`cod_custodio_actualFK`
      AND ev.`tipo_evento` IN (
          'trabajo_iniciado',
          'recepcion_mecanico_confirmada',
          'devolucion_confirmada',
          'hilo_tomado',
          'custodia_rectificada',
          'instalacion_registrada'
      )
)
WHERE tl.`id_evento_custodia_actualFK` IS NULL;

ALTER TABLE `trabajo_laboratorio`
    ADD CONSTRAINT `fk_trabajo_laboratorio_evento_custodia_actual`
        FOREIGN KEY IF NOT EXISTS (`id_evento_custodia_actualFK`)
        REFERENCES `trabajo_laboratorio_evento` (`id`);

ALTER TABLE `trabajo_laboratorio_evento`
    ADD CONSTRAINT `fk_trabajo_laboratorio_evento_custodia`
        FOREIGN KEY IF NOT EXISTS (`id_evento_custodiaFK`)
        REFERENCES `trabajo_laboratorio_evento` (`id`);

-- Resultado esperado:
-- columna_puntero_instalada = 1
-- columnas_evento_instaladas = 4
-- claves_foraneas_instaladas = 2
SELECT
    (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'trabajo_laboratorio'
          AND COLUMN_NAME = 'id_evento_custodia_actualFK'
    ) AS columna_puntero_instalada,
    (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'trabajo_laboratorio_evento'
          AND COLUMN_NAME IN (
              'id_evento_custodiaFK',
              'actor_nombre_snapshot',
              'actor_rol_snapshot',
              'local_nombre_snapshot'
          )
    ) AS columnas_evento_instaladas,
    (
        SELECT COUNT(*)
        FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND CONSTRAINT_NAME IN (
              'fk_trabajo_laboratorio_evento_custodia_actual',
              'fk_trabajo_laboratorio_evento_custodia'
          )
    ) AS claves_foraneas_instaladas,
    (
        SELECT COUNT(*)
        FROM `trabajo_laboratorio`
        WHERE `id_evento_custodia_actualFK` IS NOT NULL
    ) AS trabajos_con_puntero_recuperado;

-- Reversion fisica controlada (NO ejecutar en operacion normal):
-- 1. ALTER TABLE trabajo_laboratorio DROP FOREIGN KEY
--    fk_trabajo_laboratorio_evento_custodia_actual;
-- 2. ALTER TABLE trabajo_laboratorio_evento DROP FOREIGN KEY
--    fk_trabajo_laboratorio_evento_custodia;
-- 3. Eliminar indices y columnas solamente despues de respaldar los eventos
--    nuevos. La reversion funcional recomendada es conservar las columnas.
