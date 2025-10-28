ALTER TABLE arqueocaja ADD COLUMN cant500 INT, 
    ADD COLUMN cant1000 INT, 
    ADD COLUMN cant2000 INT, 
    ADD COLUMN cant5000 INT, 
    ADD COLUMN cant10000 INT,
    ADD COLUMN cant20000 INT,
    ADD COLUMN cant50000 INT,
    ADD COLUMN cant100000 INT;

CREATE TABLE motivos_ingreso_egreso (
    cod_motivo_ingreso_egreso INT PRIMARY KEY AUTO_INCREMENT,
    descripcion VARCHAR(100) NOT NULL,
    estado enum('activo', 'inactivo') DEFAULT 'activo'
);

ALTER TABLE gastos ADD COLUMN cod_motivoIngresoEgresoFK INT;