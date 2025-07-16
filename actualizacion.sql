ALTER TABLE `syscvxco_ac`.`consulta` 
ADD COLUMN `cod_clienteFK` INT(11) NULL AFTER `diagnostico`;

CREATE TABLE `syscvxco_ac`.`detalle_observacion_consulta` (
  `iddetalle_observacion_consulta` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(150) NULL,
  `cod_clienteFK` INT(11) NULL,
  PRIMARY KEY (`iddetalle_observacion_consulta`));
  
  

update venta set apodo = '';



CREATE TABLE `antecedente_paciente` (
  `idantecedente_paciente` int(11) NOT NULL AUTO_INCREMENT,
  `cod_ventaFK` int(11) DEFAULT NULL,
  `cod_clienteFK` int(11) DEFAULT NULL,
  `observacion` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`idantecedente_paciente`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

  /* EJECUTAR EN LINEA */
ALTER TABLE `syscvxco_ac`.`detalle_venta` 
ADD COLUMN `progreso_porcentaje` INT(11) NULL DEFAULT 0 AFTER `estado_tratamiento`;

