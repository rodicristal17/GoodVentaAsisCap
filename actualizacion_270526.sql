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

