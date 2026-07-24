CREATE TABLE IF NOT EXISTS reposiciones_insumos (
    id_reposicion INT NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(40) NOT NULL,
    sucursal_id INT NOT NULL,
    consultorio_id INT NULL,
    fecha_envio DATETIME NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    usuario_creo INT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_recibio INT NULL,
    fecha_recepcion DATETIME NULL,
    PRIMARY KEY (id_reposicion),
    UNIQUE KEY uq_reposicion_codigo (codigo),
    KEY idx_reposicion_fecha (fecha_envio),
    KEY idx_reposicion_destino (sucursal_id, consultorio_id),
    KEY idx_reposicion_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS reposiciones_insumos_detalle (
    id_detalle INT NOT NULL AUTO_INCREMENT,
    id_reposicion INT NOT NULL,
    consultorio_id INT NOT NULL,
    insumo_id INT NOT NULL,
    id_variante INT NOT NULL DEFAULT 0,
    cantidad DECIMAL(12,3) NOT NULL,
    PRIMARY KEY (id_detalle),
    UNIQUE KEY uq_reposicion_consultorio_insumo (id_reposicion, consultorio_id, insumo_id, id_variante),
    KEY idx_reposicion_detalle (id_reposicion),
    KEY idx_reposicion_detalle_consultorio (consultorio_id),
    KEY idx_reposicion_detalle_insumo (insumo_id, id_variante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
