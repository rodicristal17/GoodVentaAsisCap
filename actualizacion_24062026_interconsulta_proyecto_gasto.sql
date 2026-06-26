CREATE TABLE IF NOT EXISTS interconsulta_proyecto_gasto (
  id INT NOT NULL AUTO_INCREMENT,
  cod_interConsultaFK INT NOT NULL,
  cod_proyecto_gastoFK INT NOT NULL,
  estado ENUM('activo','inactivo') DEFAULT 'activo',
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_edit DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_interconsulta_proyecto (cod_interConsultaFK, cod_proyecto_gastoFK),
  KEY idx_ipg_hilo (cod_interConsultaFK),
  KEY idx_ipg_proyecto (cod_proyecto_gastoFK)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT IGNORE INTO interconsulta_proyecto_gasto (cod_interConsultaFK, cod_proyecto_gastoFK, estado)
SELECT DISTINCT g.cod_interConsultaFK, g.cod_proyecto_gastoFK, 'activo'
FROM gastos g
WHERE g.cod_interConsultaFK IS NOT NULL
  AND g.cod_interConsultaFK <> ''
  AND g.cod_interConsultaFK <> 0
  AND g.cod_proyecto_gastoFK IS NOT NULL
  AND g.cod_proyecto_gastoFK <> ''
  AND g.cod_proyecto_gastoFK <> 0;
