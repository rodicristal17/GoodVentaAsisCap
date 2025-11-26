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

ALTER TABLE persona ADD COLUMN tipo_relacion VARCHAR(100);
ALTER TABLE persona ADD COLUMN telefono_referencia INT(13);

CREATE TABLE insumos_local (
    cod_insumo INT PRIMARY KEY AUTO_INCREMENT,
    descripcion VARCHAR(100),
    nombre VARCHAR(75) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    cantidad INT,
    costo BIGINT,
    observacion VARCHAR(150),
    fecha_edit DATETIME,
    cod_localFK INT,
    cod_usuarioFK_edit INT,
    FOREIGN KEY (cod_localFK) REFERENCES local(cod_local),
    FOREIGN KEY (cod_usuarioFK_edit) REFERENCES usuario(cod_usuario)
);

ALTER TABLE mecanico_dental MODIFY COLUMN estado
    ENUM('pendiente', 'entregado', 'retirado', 'pagado', 'inactivo');

ALTER TABLE mecanico_dental MODIFY COLUMN fecha_entrega
    REMOVE DEFAULT;

-- Agregar permisos::
-- CREARNUEVOMOTIVO, VERABMLIMITECAJA
-- VERLISTADOTIPOTRABAJOMECANICODENTAL, VERLISTADOMECANICODENTAL, VERLISTADOASISTENCIA
-- EDITARLISTADOINVENTARIOLOCAL, VERLISTADOINVENTARIOLOCAL