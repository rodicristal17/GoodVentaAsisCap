CREATE TABLE IF NOT EXISTS tareas_programadas (
    cod_tarea INT(11) NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    prioridad ENUM('BAJA','MEDIA','ALTA','URGENTE') NOT NULL DEFAULT 'MEDIA',
    fecha_programada DATE NULL,
    hora_programada TIME NULL,
    fecha_limite DATETIME NULL,
    estado ENUM('Activo','Inactivo','Cancelado') NOT NULL DEFAULT 'Activo',
    cod_administradorFK INT(11) NULL,
    fecha_insert DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_update DATETIME NULL,
    PRIMARY KEY (cod_tarea),
    INDEX idx_estado (estado),
    INDEX idx_fecha_programada (fecha_programada),
    INDEX idx_cod_administradorFK (cod_administradorFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS tareas_programadas_asignadas (
    cod_tarea_asignada INT(11) NOT NULL AUTO_INCREMENT,
    cod_tareaFK INT(11) NOT NULL,
    cod_usuarioFK INT(11) NOT NULL,
    estado_tarea ENUM('Pendiente','En Proceso','Completada','Cancelada') NOT NULL DEFAULT 'Pendiente',
    visto ENUM('Si','No') NOT NULL DEFAULT 'No',
    fecha_visto DATETIME NULL,
    observacion_admin TEXT NULL,
    observacion_usuario TEXT NULL,
    fecha_completada DATETIME NULL,
    fecha_insert DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_update DATETIME NULL,
    fecha_tarea date NULL,
    PRIMARY KEY (cod_tarea_asignada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS tareas_programadas_diarias (
    cod_tarea_diaria INT(11) NOT NULL AUTO_INCREMENT,

    cod_tareaFK INT(11) NOT NULL,
    cod_usuarioFK INT(11) NOT NULL,

    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',

    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,

    lunes ENUM('Si','No') NOT NULL DEFAULT 'Si',
    martes ENUM('Si','No') NOT NULL DEFAULT 'Si',
    miercoles ENUM('Si','No') NOT NULL DEFAULT 'Si',
    jueves ENUM('Si','No') NOT NULL DEFAULT 'Si',
    viernes ENUM('Si','No') NOT NULL DEFAULT 'Si',
    sabado ENUM('Si','No') NOT NULL DEFAULT 'Si',
    domingo ENUM('Si','No') NOT NULL DEFAULT 'Si',

    observacion_admin TEXT NULL,

    ultima_fecha_generada DATE NULL,

    cod_usuarioFK_create INT(11) NULL,
    fecha_insert DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_update DATETIME NULL,

    PRIMARY KEY (cod_tarea_diaria),

    KEY idx_tarea_diaria_tarea (cod_tareaFK),
    KEY idx_tarea_diaria_usuario (cod_usuarioFK),
    KEY idx_tarea_diaria_estado (estado),
    KEY idx_tarea_diaria_ultima_fecha (ultima_fecha_generada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;