-- Depositos bancarios a Faraone Capital S.A. y conciliacion con Ueno.
-- Compatible con MySQL 5.6. No duplica egresos ni modifica arqueos historicos.

INSERT INTO motivos_ingreso_egreso (descripcion, estado, categoria, necesita_autorizacion)
SELECT 'DEPOSITO BANCARIO - FARAONE CAPITAL S.A.', 'activo', 'operativo', 'SI'
WHERE NOT EXISTS (
  SELECT 1 FROM motivos_ingreso_egreso
  WHERE UPPER(TRIM(descripcion)) = 'DEPOSITO BANCARIO - FARAONE CAPITAL S.A.'
);

CREATE TABLE IF NOT EXISTS ueno_movimiento_deposito (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_movimiento int(11) NOT NULL,
  origen_tipo varchar(20) NOT NULL,
  origen_id int(11) NOT NULL,
  monto_aplicado int(11) NOT NULL DEFAULT 0,
  usuario_asocio int(11) NOT NULL,
  fecha_hora_asociacion datetime NOT NULL,
  estado varchar(20) NOT NULL DEFAULT 'activo',
  observacion varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_ueno_deposito_mov_estado (id_movimiento, estado),
  UNIQUE KEY uk_ueno_deposito_origen_estado (origen_tipo, origen_id, estado),
  KEY idx_ueno_deposito_fecha (fecha_hora_asociacion),
  CONSTRAINT fk_ueno_deposito_movimiento FOREIGN KEY (id_movimiento)
    REFERENCES ueno_movimiento_bancario (id_movimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Las migraciones historicas no se copian a gastos: se reconocen por su origen,
-- destinatario y fecha para evitar descontarlas dos veces del arqueo.
-- Vista de control previa a la puesta en produccion.
SELECT mc.idmigrar_caja, mc.fecha, mc.monto, mc.estado,
       p.nombre_persona AS destinatario,
       'PENDIENTE' AS control
FROM migrar_caja mc
INNER JOIN persona p ON p.cod_persona = mc.cod_usuRecibeFK
WHERE mc.fecha >= '2026-06-19 00:00:00'
  AND UPPER(TRIM(p.nombre_persona)) LIKE '%CARLOS%FARAONE%'
ORDER BY mc.fecha, mc.idmigrar_caja;
