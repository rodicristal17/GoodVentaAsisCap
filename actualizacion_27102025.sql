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
    cod_personaFK INT(11),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    Foreign Key (cod_personaFK) REFERENCES persona(cod_persona)
);

CREATE TABLE trabajo_mecanico_dental (
    cod_trabajo_mecanico_dental INT PRIMARY KEY AUTO_INCREMENT,
    cod_ventaFK INT(11),
    cod_tipo_trabajoFK INT(11),
    cod_mecanicoDentalFK INT(11),
    estado ENUM('pendiente', 'entregado', 'retirado', 'pagado', 'inactivo') DEFAULT 'pendiente',
    observacion VARCHAR(150),
    colorimetro VARCHAR(12),
    costo INT,
    fecha_entrega DATE,
    fecha_retiro DATE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    cod_usuarioFK_create INT(11),
    fecha_edit DATETIME,
    cod_usuarioFK_edit INT(11),
    Foreign Key (cod_mecanicoDentalFK) REFERENCES mecanico_dental(cod_mecanico_dental),
    Foreign Key (cod_ventaFK) REFERENCES venta(cod_venta),
    Foreign Key (cod_tipo_trabajoFK) REFERENCES tipo_trabajo_mecanico_dental(cod_tipo_trabajo_mecanico_dental)
);

ALTER TABLE persona ADD COLUMN tipo_relacion VARCHAR(100);
ALTER TABLE persona ADD COLUMN telefono_referencia INT(13);
ALTER TABLE usuario ADD COLUMN fecha_creacion DATE;

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

ALTER TABLE insumos_local ADD COLUMN url1 VARCHAR(100);
ALTER TABLE insumos_local ADD COLUMN url2 VARCHAR(100);
ALTER TABLE insumos_local ADD COLUMN url3 VARCHAR(100);

ALTER TABLE gastos ADD COLUMN url1 VARCHAR(100);
ALTER TABLE gastos ADD COLUMN cod_motivo INT(11);

ALTER TABLE trabajo_mecanico_dental ADD COLUMN cod_especialistaFK int(11);
ALTER TABLE trabajo_mecanico_dental ADD COLUMN cod_localFK INT(11);

ALTER TABLE trabajo_mecanico_dental ADD CONSTRAINT fk_local_mecanico_dental
    FOREIGN KEY (cod_localFK) REFERENCES local(cod_local);


CREATE TABLE interconsulta (
    cod_interConsulta INT PRIMARY KEY AUTO_INCREMENT,
    asunto VARCHAR(100),
    estado ENUM('pendiente', 'proceso', 'finalizado', 'inactivo') DEFAULT 'pendiente',
    tipo ENUM('clinico', 'administrativo'),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    cod_usuarioFK_edit INT,
    fecha_edit DATETIME,
    cod_ventaFK INT,
    cod_usuarioFK_create INT,
    Foreign Key (cod_ventaFK) REFERENCES venta(cod_venta),
    Foreign Key (cod_usuarioFK_create) REFERENCES usuario(cod_usuario)
);

CREATE TABLE mensaje (
    cod_mensaje INT PRIMARY KEY AUTO_INCREMENT,
    contenido VARCHAR(500) NOT NULL,
    url VARCHAR(100),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    cod_interConsultaFK INT,
    cod_usuarioFK INT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cod_interConsultaFK) REFERENCES interconsulta(cod_interConsulta),
    FOREIGN KEY (cod_usuarioFK) REFERENCES usuario(cod_usuario)
);

CREATE TABLE menciones (
    cod_mencion INT PRIMARY KEY AUTO_INCREMENT,
    cod_usuarioFK INT,
    cod_mensajeFK INT,
    isLeido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (cod_usuarioFK) REFERENCES usuario(cod_usuario),
    FOREIGN KEY (cod_mensajeFK) REFERENCES mensaje(cod_mensaje)
);

ALTER TABLE interconsulta CHANGE tipo tipo VARCHAR(14) NOT NULL;

UPDATE historialactualizacion SET codigo='X-GT-1-JMTG-V1.52', detalles='Implementacion Interconsulta', fecha='2026-01-05' WHERE idhistorialactualizacion= 2;


-- Eliminar motivos duplicados
UPDATE gastos SET cod_motivoIngresoEgresoFK= 17 WHERE cod_motivoIngresoEgresoFK = 13;
UPDATE motivos_ingreso_egreso SET estado= '' WHERE cod_motivo_ingreso_egreso = 13;
UPDATE gastos SET cod_motivoIngresoEgresoFK= 10 WHERE cod_motivoIngresoEgresoFK = 22;
UPDATE motivos_ingreso_egreso SET estado= '' WHERE cod_motivo_ingreso_egreso = 22;
UPDATE gastos SET cod_motivoIngresoEgresoFK= 29 WHERE cod_motivoIngresoEgresoFK = 30;
UPDATE motivos_ingreso_egreso SET estado= '' WHERE cod_motivo_ingreso_egreso = 30;
UPDATE gastos SET cod_motivoIngresoEgresoFK= 54 WHERE cod_motivoIngresoEgresoFK = 61;
UPDATE motivos_ingreso_egreso SET estado= '' WHERE cod_motivo_ingreso_egreso = 61;
UPDATE gastos SET cod_motivoIngresoEgresoFK= 26 WHERE cod_motivoIngresoEgresoFK = 69;
UPDATE motivos_ingreso_egreso SET estado= '' WHERE cod_motivo_ingreso_egreso = 69;
UPDATE gastos SET cod_motivoIngresoEgresoFK= 34 WHERE cod_motivoIngresoEgresoFK = 84;
UPDATE motivos_ingreso_egreso SET estado= '' WHERE cod_motivo_ingreso_egreso = 84;

ALTER TABLE motivos_ingreso_egreso ADD COLUMN categoria ENUM('directo', 'ingreso', 'operativo');

-- Agregar permisos::
-- CREARNUEVOMOTIVO, VERABMLIMITECAJA
-- VERLISTADOTIPOTRABAJOMECANICODENTAL, VERLISTADOMECANICODENTAL, VERLISTADOASISTENCIA
-- EDITARLISTADOINVENTARIOLOCAL, VERLISTADOINVENTARIOLOCAL, CREARLISTADOINVENTARIOLOCAL
-- VERGASTOSZONAOPERATIVOS, VERGASTOSZONACOSTOSDIRECTOS,VERGASTOSZONAINGRESOS