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


ALTER TABLE `syscvxco_ac`.`detalle_venta` 
ADD COLUMN `progreso_porcentaje` INT(11) NULL DEFAULT 0 AFTER `estado_tratamiento`;



update pago set cod_tipoPagoFK = 1 where cod_tipoPagoFK = 0;

  /* EJECUTAR EN LINEA */
  CREATE TABLE `migrar_caja` (
  `idmigrar_caja` int(11) NOT NULL AUTO_INCREMENT,
  `obs` varchar(150) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `monto` int(11) DEFAULT NULL,
  `cod_caja_desdeFK` int(11) DEFAULT NULL,
  `cod_caja_hastaFK` int(11) DEFAULT NULL,
  `estado` varchar(45) DEFAULT NULL,
  `tipo` varchar(45) DEFAULT NULL,
  `cod_usuRecibeFK` int(11) DEFAULT NULL,
  `cod_UsuEnviaFK` int(11) DEFAULT NULL,
  PRIMARY KEY (`idmigrar_caja`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
