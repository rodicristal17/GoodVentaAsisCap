CREATE TABLE tareas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255),
    fecha_inicio DATE,
    fecha_fin DATE,
    progreso INT,
    estado VARCHAR(50),
    dependencia INT,
    sucursal VARCHAR(255),
    responsable VARCHAR(255)
);

CREATE TABLE tarea_usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tarea_id INT,
    cod_usuario INT(11),
    FOREIGN KEY (cod_usuario) REFERENCES usuario(cod_usuario),
    FOREIGN KEY (tarea_id) REFERENCES tareas(id)
);