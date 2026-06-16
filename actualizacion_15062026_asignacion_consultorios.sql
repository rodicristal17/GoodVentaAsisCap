CREATE TABLE IF NOT EXISTS consultorio_doctor_asignacion (
    id_asignacion INT NOT NULL AUTO_INCREMENT,
    id_consultorio INT NOT NULL,
    id_horario_usuario INT NOT NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    fecha_create DATETIME DEFAULT CURRENT_TIMESTAMP,
    cod_usuarioFK_create INT NULL,
    fecha_edit DATETIME NULL,
    cod_usuarioFK_edit INT NULL,
    PRIMARY KEY (id_asignacion),
    KEY idx_asig_horario_estado (id_horario_usuario, estado),
    KEY idx_asig_consultorio_estado (id_consultorio, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

