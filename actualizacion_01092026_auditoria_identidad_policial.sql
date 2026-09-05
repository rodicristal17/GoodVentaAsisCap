CREATE TABLE IF NOT EXISTS cliente_identidad_policial_auditoria (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    cod_clienteFK INT NOT NULL,
    ci VARCHAR(40) NOT NULL,
    nombre_anterior VARCHAR(255) NULL,
    apellido_anterior VARCHAR(255) NULL,
    nombre_nuevo VARCHAR(255) NOT NULL,
    apellido_nuevo VARCHAR(255) NOT NULL,
    contexto VARCHAR(30) NOT NULL,
    cod_usuarioFK INT NULL,
    fecha_creacion DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_identidad_cliente_fecha (cod_clienteFK,fecha_creacion),
    KEY idx_identidad_usuario_fecha (cod_usuarioFK,fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

