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

CREATE TABLE limite_caja (
    cod_limite_caja INT PRIMARY KEY AUTO_INCREMENT,
    cod_usuarioFK INT NOT NULL,
    limite_monto INT NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cod_usuarioFK) REFERENCES usuario(cod_usuario)
);

-- Agregar ppermisos::
--CREARNUEVOMOTIVO, VERABMLIMITECAJA