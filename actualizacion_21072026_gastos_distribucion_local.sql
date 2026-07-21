-- Distribucion contable de egresos entre sucursales.
-- Compatible con MySQL 5.6/MariaDB y PHP 7.2.
-- Cambio aditivo: no redistribuye ni modifica gastos historicos.
-- Los gastos sin filas hijas conservan el comportamiento historico de la aplicacion.
-- Ejecutar con respaldo previo y antes de publicar la interfaz multilocal.
-- Reversion segura: volver primero al codigo anterior. No eliminar estas tablas sin
-- exportar sus asignaciones y auditoria, porque contienen el detalle contable nuevo.

SET NAMES utf8;

SELECT 1 AS preflight_gastos FROM `gastos` LIMIT 1;
SELECT 1 AS preflight_local FROM `local` LIMIT 1;
SELECT 1 AS preflight_usuario FROM `usuario` LIMIT 1;

CREATE TABLE IF NOT EXISTS `gasto_distribucion_local` (
  `id_distribucion` int(11) NOT NULL AUTO_INCREMENT,
  `idgastosFK` int(11) NOT NULL,
  `cod_localFK` int(11) NOT NULL,
  `monto_asignado` decimal(18,2) NOT NULL,
  `modo_distribucion` enum('local','compartido','personalizado') NOT NULL DEFAULT 'local',
  `origen` varchar(30) NOT NULL DEFAULT 'flujo_financiero',
  `cod_usuarioFK_create` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cod_usuarioFK_update` int(11) DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_distribucion`),
  UNIQUE KEY `uq_gasto_distribucion_local` (`idgastosFK`,`cod_localFK`),
  KEY `idx_gasto_distribucion_local_periodo` (`cod_localFK`,`idgastosFK`),
  KEY `idx_gasto_distribucion_usuario_create` (`cod_usuarioFK_create`),
  KEY `idx_gasto_distribucion_usuario_update` (`cod_usuarioFK_update`),
  CONSTRAINT `fk_gasto_distribucion_gasto` FOREIGN KEY (`idgastosFK`) REFERENCES `gastos` (`idgastos`),
  CONSTRAINT `fk_gasto_distribucion_local` FOREIGN KEY (`cod_localFK`) REFERENCES `local` (`cod_local`),
  CONSTRAINT `fk_gasto_distribucion_usuario_create` FOREIGN KEY (`cod_usuarioFK_create`) REFERENCES `usuario` (`cod_usuario`),
  CONSTRAINT `fk_gasto_distribucion_usuario_update` FOREIGN KEY (`cod_usuarioFK_update`) REFERENCES `usuario` (`cod_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `gasto_distribucion_auditoria` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `idgastosFK` int(11) NOT NULL,
  `accion` enum('crear','editar','materializar','copiar_cuota') NOT NULL,
  `modo_anterior` varchar(20) DEFAULT NULL,
  `modo_nuevo` varchar(20) DEFAULT NULL,
  `distribucion_anterior` longtext,
  `distribucion_nueva` longtext,
  `origen` varchar(30) NOT NULL DEFAULT 'flujo_financiero',
  `cod_usuarioFK` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_gasto_distribucion_aud_gasto_fecha` (`idgastosFK`,`fecha_registro`),
  KEY `idx_gasto_distribucion_aud_usuario` (`cod_usuarioFK`),
  CONSTRAINT `fk_gasto_distribucion_aud_gasto` FOREIGN KEY (`idgastosFK`) REFERENCES `gastos` (`idgastos`),
  CONSTRAINT `fk_gasto_distribucion_aud_usuario` FOREIGN KEY (`cod_usuarioFK`) REFERENCES `usuario` (`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

DELIMITER $$

DROP TRIGGER IF EXISTS `trg_gasto_distribucion_bi`$$
CREATE TRIGGER `trg_gasto_distribucion_bi`
BEFORE INSERT ON `gasto_distribucion_local`
FOR EACH ROW
BEGIN
  IF NEW.monto_asignado <= 0 OR NEW.monto_asignado <> FLOOR(NEW.monto_asignado) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Distribucion de gasto: el monto debe ser un guarani entero positivo';
  END IF;
  IF NEW.modo_distribucion NOT IN ('local','compartido','personalizado') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Distribucion de gasto: modo no valido';
  END IF;
  IF EXISTS (
    SELECT 1
    FROM gasto_distribucion_local existente
    WHERE existente.idgastosFK = NEW.idgastosFK
      AND existente.modo_distribucion <> NEW.modo_distribucion
    LIMIT 1
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Distribucion de gasto: todas las filas deben usar el mismo modo';
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_gasto_distribucion_bu`$$
CREATE TRIGGER `trg_gasto_distribucion_bu`
BEFORE UPDATE ON `gasto_distribucion_local`
FOR EACH ROW
BEGIN
  IF NEW.monto_asignado <= 0 OR NEW.monto_asignado <> FLOOR(NEW.monto_asignado) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Distribucion de gasto: el monto debe ser un guarani entero positivo';
  END IF;
  IF NEW.modo_distribucion NOT IN ('local','compartido','personalizado') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Distribucion de gasto: modo no valido';
  END IF;
  IF EXISTS (
    SELECT 1
    FROM gasto_distribucion_local existente
    WHERE existente.idgastosFK = NEW.idgastosFK
      AND existente.id_distribucion <> OLD.id_distribucion
      AND existente.modo_distribucion <> NEW.modo_distribucion
    LIMIT 1
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Distribucion de gasto: todas las filas deben usar el mismo modo';
  END IF;
END$$

DELIMITER ;
