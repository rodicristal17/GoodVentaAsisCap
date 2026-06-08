CREATE TABLE IF NOT EXISTS agenda_tratamientos (
    id INT NOT NULL AUTO_INCREMENT,
    id_agenda INT NOT NULL,
    cod_ventaFK INT NOT NULL,
    cod_detalle_ventaFK INT NOT NULL,
    estado ENUM('previsto','realizado','pendiente','cancelado') NOT NULL DEFAULT 'previsto',
    creado_por INT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    realizado_por INT NULL,
    realizado_en DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_agenda_detalle (id_agenda, cod_detalle_ventaFK),
    KEY idx_agenda_trat_agenda (id_agenda),
    KEY idx_agenda_trat_venta (cod_ventaFK),
    KEY idx_agenda_trat_detalle (cod_detalle_ventaFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS agenda_insumo_base (
    id INT NOT NULL AUTO_INCREMENT,
    id_insumo INT NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL DEFAULT 1,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    creado_por INT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_agenda_insumo_base (id_insumo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS agenda_consumo_insumos (
    id INT NOT NULL AUTO_INCREMENT,
    id_agenda INT NOT NULL,
    id_insumo INT NOT NULL,
    cantidad_prevista DECIMAL(12,3) NOT NULL DEFAULT 0,
    cantidad_confirmada DECIMAL(12,3) NULL,
    unidad_medida VARCHAR(40) NULL,
    estado ENUM('previsto','confirmado','ajustado') NOT NULL DEFAULT 'previsto',
    usuario_confirmo INT NULL,
    fecha_confirmo DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_agenda_consumo (id_agenda, id_insumo),
    KEY idx_agenda_consumo_agenda (id_agenda),
    KEY idx_agenda_consumo_insumo (id_insumo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS agenda_consumo_ajustes (
    id INT NOT NULL AUTO_INCREMENT,
    id_agenda INT NOT NULL,
    id_insumo INT NOT NULL,
    usuario_id INT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    paciente VARCHAR(160) NULL,
    venta_apodo VARCHAR(160) NULL,
    id_consultorio INT NULL,
    cantidad_anterior DECIMAL(12,3) NOT NULL DEFAULT 0,
    cantidad_nueva DECIMAL(12,3) NOT NULL DEFAULT 0,
    diferencia_stock DECIMAL(12,3) NOT NULL DEFAULT 0,
    motivo VARCHAR(255) NOT NULL DEFAULT 'Correccion de consumo confirmado',
    PRIMARY KEY (id),
    KEY idx_agenda_ajuste_agenda (id_agenda),
    KEY idx_agenda_ajuste_insumo (id_insumo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
