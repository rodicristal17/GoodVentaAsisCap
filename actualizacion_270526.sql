CREATE TABLE tareas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255),
    fecha_inicio DATE,
    fecha_fin DATE,
    progreso INT,
    estado VARCHAR(50),
    dependencia INT,
    cod_localFK INT(11),
    cod_usuario_asignadoFK INT(11),
    cod_usuario_createFK INT(11),
    fecha_create DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_edit DATETIME,
    cod_usuario_editFK INT(11),
    FOREIGN KEY (cod_localFK) REFERENCES local(cod_local),
    FOREIGN KEY (cod_usuario_asignadoFK) REFERENCES usuario(cod_usuario)
);

CREATE TABLE IF NOT EXISTS tarea_usuarios (
    tarea_id INT NOT NULL,
    cod_usuario INT(11) NOT NULL,
    fecha_create DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (tarea_id, cod_usuario),
    FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE,
    FOREIGN KEY (cod_usuario) REFERENCES usuario(cod_usuario)
);

ALTER TABLE dictamenes
    MODIFY estado VARCHAR(20) DEFAULT 'emitido';

CREATE TABLE IF NOT EXISTS dictamenes_trazabilidad (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cod_dictamen_originalFK INT(11) NOT NULL,
    cod_dictamen_nuevoFK INT(11) DEFAULT NULL,
    tipo_accion VARCHAR(30) NOT NULL,
    motivo VARCHAR(750) NOT NULL,
    estado_anterior VARCHAR(20) DEFAULT NULL,
    estado_nuevo VARCHAR(20) DEFAULT NULL,
    fecha_create DATETIME DEFAULT CURRENT_TIMESTAMP,
    cod_usuarioFK_create INT(11) NOT NULL,
    FOREIGN KEY (cod_dictamen_originalFK) REFERENCES dictamenes(id),
    FOREIGN KEY (cod_dictamen_nuevoFK) REFERENCES dictamenes(id),
    FOREIGN KEY (cod_usuarioFK_create) REFERENCES usuario(cod_usuario)
);
