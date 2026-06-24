DELIMITER $$

DROP PROCEDURE IF EXISTS aplicar_odontograma_interactivo_17062026$$
CREATE PROCEDURE aplicar_odontograma_interactivo_17062026()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'producto'
      AND COLUMN_NAME = 'alcance_odontologico'
  ) THEN
    ALTER TABLE producto
      ADD COLUMN alcance_odontologico VARCHAR(40) NOT NULL DEFAULT 'no_requiere' AFTER tipo;
  END IF;

  CREATE TABLE IF NOT EXISTS odontogramas (
    id INT NOT NULL AUTO_INCREMENT,
    cedula VARCHAR(45) NULL,
    paciente_id INT NULL,
    paciente_nombre_snapshot VARCHAR(255) NULL,
    venta_base_id INT NULL,
    presupuesto_id INT NULL,
    plan_definitivo_id INT NULL,
    denticion VARCHAR(20) NOT NULL DEFAULT 'permanente',
    estado VARCHAR(20) NOT NULL DEFAULT 'borrador',
    version_actual INT NOT NULL DEFAULT 1,
    creado_por INT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_por INT NULL,
    fecha_actualizacion DATETIME NULL,
    convalidado_por INT NULL,
    fecha_convalidacion DATETIME NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_odontograma_paciente (paciente_id, activo),
    KEY idx_odontograma_cedula (cedula, activo),
    KEY idx_odontograma_venta (venta_base_id),
    KEY idx_odontograma_presupuesto (presupuesto_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS odontograma_marcas (
    id INT NOT NULL AUTO_INCREMENT,
    odontograma_id INT NOT NULL,
    pieza VARCHAR(5) NOT NULL,
    denticion VARCHAR(20) NOT NULL DEFAULT 'permanente',
    superficie VARCHAR(30) NULL,
    tipo_marca VARCHAR(40) NOT NULL,
    estado_marca VARCHAR(30) NOT NULL DEFAULT 'observado',
    color VARCHAR(15) NOT NULL DEFAULT 'rojo',
    observacion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_por INT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_por INT NULL,
    fecha_actualizacion DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_odontograma_marcas_odo (odontograma_id, activo),
    KEY idx_odontograma_marcas_pieza (odontograma_id, pieza, superficie, activo)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS odontograma_tratamiento_links (
    id INT NOT NULL AUTO_INCREMENT,
    odontograma_id INT NOT NULL,
    venta_id INT NULL,
    detalle_venta_id INT NULL,
    presupuesto_id INT NULL,
    presupuesto_item_id INT NULL,
    producto_id VARCHAR(45) NULL,
    nombre_tratamiento_snapshot VARCHAR(255) NULL,
    nivel_riesgo_snapshot INT NULL,
    alcance_odontologico VARCHAR(40) NOT NULL DEFAULT 'pieza_dental',
    pieza VARCHAR(5) NULL,
    denticion VARCHAR(20) NULL DEFAULT 'permanente',
    superficies_json TEXT NULL,
    arcada VARCHAR(30) NULL,
    cuadrante VARCHAR(30) NULL,
    boca_completa TINYINT(1) NOT NULL DEFAULT 0,
    origen VARCHAR(40) NOT NULL DEFAULT 'ficha_clinica',
    estado_link VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_por INT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_por INT NULL,
    fecha_actualizacion DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_odontograma_links_odo (odontograma_id, activo),
    KEY idx_odontograma_links_detalle (detalle_venta_id, activo),
    KEY idx_odontograma_links_presupuesto_item (presupuesto_item_id, activo),
    KEY idx_odontograma_links_presupuesto (presupuesto_id, activo),
    KEY idx_odontograma_links_venta (venta_id, activo),
    KEY idx_odontograma_links_pieza (odontograma_id, pieza, activo)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS odontograma_historial (
    id INT NOT NULL AUTO_INCREMENT,
    odontograma_id INT NOT NULL,
    version INT NOT NULL DEFAULT 1,
    accion VARCHAR(60) NOT NULL,
    descripcion TEXT NOT NULL,
    pieza VARCHAR(5) NULL,
    superficie VARCHAR(30) NULL,
    marca_id INT NULL,
    link_id INT NULL,
    tratamiento_id VARCHAR(45) NULL,
    venta_id INT NULL,
    detalle_venta_id INT NULL,
    presupuesto_id INT NULL,
    presupuesto_item_id INT NULL,
    valor_anterior TEXT NULL,
    valor_nuevo TEXT NULL,
    motivo VARCHAR(255) NULL,
    usuario_id INT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_odontograma_hist_odo (odontograma_id, fecha_hora),
    KEY idx_odontograma_hist_link (link_id),
    KEY idx_odontograma_hist_marca (marca_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

  UPDATE producto
  SET alcance_odontologico = 'boca_completa'
  WHERE alcance_odontologico = 'no_requiere'
    AND (
      LOWER(nombre_producto) LIKE '%profilaxis%'
      OR LOWER(nombre_producto) LIKE '%limpieza%'
      OR LOWER(nombre_producto) LIKE '%sarro%'
      OR LOWER(nombre_producto) LIKE '%modelo de estudio%'
      OR LOWER(nombre_producto) LIKE '%blanqueamiento%'
    );

  UPDATE producto
  SET alcance_odontologico = 'arcada'
  WHERE alcance_odontologico = 'no_requiere'
    AND (
      LOWER(nombre_producto) LIKE '%ortodon%'
      OR LOWER(nombre_producto) LIKE '%protesis%'
      OR LOWER(nombre_producto) LIKE '%pr%tesis%'
      OR LOWER(nombre_producto) LIKE '%ppr%'
    );

  UPDATE producto
  SET alcance_odontologico = 'pieza_dental'
  WHERE alcance_odontologico = 'no_requiere'
    AND (
      LOWER(nombre_producto) LIKE '%endodon%'
      OR LOWER(nombre_producto) LIKE '%extracci%'
      OR LOWER(nombre_producto) LIKE '%exodon%'
      OR LOWER(nombre_producto) LIKE '%corona%'
      OR LOWER(nombre_producto) LIKE '%perno%'
      OR LOWER(nombre_producto) LIKE '%incrust%'
      OR LOWER(nombre_producto) LIKE '%radiograf%'
    );

  UPDATE producto
  SET alcance_odontologico = 'pieza_superficie'
  WHERE alcance_odontologico = 'no_requiere'
    AND (
      LOWER(nombre_producto) LIKE '%restaur%'
      OR LOWER(nombre_producto) LIKE '%caries%'
      OR LOWER(nombre_producto) LIKE '%obtur%'
      OR LOWER(nombre_producto) LIKE '%sellado%'
    );
END$$

CALL aplicar_odontograma_interactivo_17062026()$$
DROP PROCEDURE IF EXISTS aplicar_odontograma_interactivo_17062026$$

DELIMITER ;
