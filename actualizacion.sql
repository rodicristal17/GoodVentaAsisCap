ALTER TABLE `syscvxco_ac`.`consulta` 
ADD COLUMN `cod_clienteFK` INT(11) NULL AFTER `diagnostico`;

CREATE TABLE `syscvxco_ac`.`detalle_observacion_consulta` (
  `iddetalle_observacion_consulta` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(150) NULL,
  `cod_clienteFK` INT(11) NULL,
  PRIMARY KEY (`iddetalle_observacion_consulta`));

