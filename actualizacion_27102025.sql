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

CREATE TABLE asistencia (
    cod_asistencia INT PRIMARY KEY AUTO_INCREMENT,
    cod_usuarioFK INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    hora_entrada TIME NOT NULL,
    hora_salida TIME,
    direccion_ip VARCHAR(40),
    FOREIGN KEY (cod_usuarioFK) REFERENCES usuario(cod_usuario)
);

CREATE TABLE tipo_trabajo_mecanico_dental (
    cod_tipo_trabajo_mecanico_dental INT PRIMARY KEY AUTO_INCREMENT,
    descripcion VARCHAR(100),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

CREATE TABLE mecanico_dental (
    cod_mecanico_dental INT PRIMARY KEY AUTO_INCREMENT,
    cod_ventaFK INT(11),
    cod_tipo_trabajoFK INT(11),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    observacion VARCHAR(150),
    colorimetro VARCHAR(12),
    costo INT,
    fecha_entrega DATE,
    fecha_retiro DATE,
    Foreign Key (cod_ventaFK) REFERENCES venta(cod_venta),
    Foreign Key (cod_tipo_trabajoFK) REFERENCES tipo_trabajo_mecanico_dental(cod_tipo_trabajo_mecanico_dental)
);

-- Agregar ppermisos::
--CREARNUEVOMOTIVO, VERABMLIMITECAJA
--VERLISTADOTIPOTRABAJOMECANICODENTAL, VERLISTADOMECANICODENTAL

SELECT md.*, t.descripcion as nombre_tipo_trabajo, 
         (SELECT nombre_persona FROM persona JOIN venta v ON v.cod_clienteFK = cod_persona WHERE v.cod_venta = md.cod_ventaFK ) AS nombre_paciente
         FROM mecanico_dental md JOIN tipo_trabajo_mecanico_dental t ON t.cod_tipo_trabajo_mecanico_dental = md.cod_tipo_trabajoFK  ORDER BY md.fecha_entrega DESC;