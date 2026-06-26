CREATE TABLE IF NOT EXISTS plan_definitivo_tratamiento (
  id INT NOT NULL AUTO_INCREMENT,
  cedula VARCHAR(60) NOT NULL,
  paciente_id INT NULL,
  venta_base_id INT NOT NULL,
  doctor_cabecera_id INT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'borrador',
  version_actual INT NOT NULL DEFAULT 1,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  creado_por INT NULL,
  fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  actualizado_por INT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_plan_definitivo_cedula (cedula),
  KEY idx_plan_definitivo_paciente (paciente_id),
  KEY idx_plan_definitivo_venta_base (venta_base_id),
  KEY idx_plan_definitivo_estado (estado),
  KEY idx_plan_definitivo_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS plan_definitivo_tratamiento_items (
  id INT NOT NULL AUTO_INCREMENT,
  plan_definitivo_id INT NOT NULL,
  venta_id INT NOT NULL,
  detalle_venta_id INT NOT NULL,
  producto_id VARCHAR(45) NULL,
  nombre_tratamiento_snapshot VARCHAR(255) NOT NULL,
  nivel_riesgo_snapshot TINYINT NULL,
  orden INT NOT NULL,
  etapa VARCHAR(60) NULL,
  observacion_clinica TEXT NULL,
  origen VARCHAR(30) NOT NULL DEFAULT 'plan_principal',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_agregado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  agregado_por INT NULL,
  PRIMARY KEY (id),
  KEY idx_plan_definitivo_items_plan (plan_definitivo_id),
  KEY idx_plan_definitivo_items_venta (venta_id),
  KEY idx_plan_definitivo_items_detalle (detalle_venta_id),
  KEY idx_plan_definitivo_items_orden (plan_definitivo_id, orden),
  KEY idx_plan_definitivo_items_activo (activo),
  CONSTRAINT fk_plan_definitivo_items_plan
    FOREIGN KEY (plan_definitivo_id)
    REFERENCES plan_definitivo_tratamiento (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS plan_definitivo_tratamiento_historial (
  id INT NOT NULL AUTO_INCREMENT,
  plan_definitivo_id INT NOT NULL,
  version INT NOT NULL,
  accion VARCHAR(80) NOT NULL,
  descripcion TEXT NOT NULL,
  valor_anterior TEXT NULL,
  valor_nuevo TEXT NULL,
  motivo TEXT NULL,
  usuario_id INT NULL,
  rol VARCHAR(80) NULL,
  fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_plan_definitivo_historial_plan (plan_definitivo_id),
  KEY idx_plan_definitivo_historial_version (plan_definitivo_id, version),
  KEY idx_plan_definitivo_historial_fecha (fecha_hora),
  CONSTRAINT fk_plan_definitivo_historial_plan
    FOREIGN KEY (plan_definitivo_id)
    REFERENCES plan_definitivo_tratamiento (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
