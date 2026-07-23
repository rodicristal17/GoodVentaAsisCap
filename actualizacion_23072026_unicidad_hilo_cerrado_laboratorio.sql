-- Sistema Telar / Clinident Salud
-- Un tratamiento instalado y entregado conserva un unico hilo cerrado.
-- La correccion es aditiva: no elimina trabajos, eventos, fichas ni medios.

CREATE TABLE IF NOT EXISTS trabajo_laboratorio_consolidacion (
    id INT NOT NULL AUTO_INCREMENT,
    id_trabajo_canonicoFK INT NOT NULL,
    id_trabajo_consolidadoFK INT NOT NULL,
    motivo VARCHAR(120) NOT NULL,
    detalle VARCHAR(500) NULL,
    origen VARCHAR(40) NOT NULL,
    fecha_creacion DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tlab_consolidacion_consolidado (id_trabajo_consolidadoFK),
    KEY idx_tlab_consolidacion_canonico (id_trabajo_canonicoFK),
    CONSTRAINT fk_tlab_consolidacion_canonico
        FOREIGN KEY (id_trabajo_canonicoFK) REFERENCES trabajo_laboratorio (id),
    CONSTRAINT fk_tlab_consolidacion_consolidado
        FOREIGN KEY (id_trabajo_consolidadoFK) REFERENCES trabajo_laboratorio (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

START TRANSACTION;

-- Si un ambiente ya contiene varios cierres para el mismo tratamiento y
-- unidad, se conserva como canonico el primer cierre por fecha e ID. Los
-- posteriores quedan relacionados, no eliminados. En el incidente confirmado
-- esta regla conserva LAB-4 y consolida LAB-5.
INSERT IGNORE INTO trabajo_laboratorio_consolidacion (
    id_trabajo_canonicoFK,
    id_trabajo_consolidadoFK,
    motivo,
    detalle,
    origen,
    fecha_creacion
)
SELECT
    canonico.id,
    consolidado.id,
    'duplicacion_historica_mismo_tratamiento',
    'El segundo cierre historico se conserva como antecedente y deja de representar un hilo independiente.',
    'migracion_correctiva',
    NOW()
FROM trabajo_laboratorio canonico
INNER JOIN trabajo_laboratorio consolidado
    ON consolidado.cod_detalle_ventaFK=canonico.cod_detalle_ventaFK
    AND consolidado.unidad_origen=canonico.unidad_origen
    AND consolidado.id<>canonico.id
WHERE canonico.estado_derivado='instalado'
  AND consolidado.estado_derivado='instalado'
  AND NOT EXISTS (
      SELECT 1
      FROM trabajo_laboratorio anterior
      WHERE anterior.cod_detalle_ventaFK=canonico.cod_detalle_ventaFK
        AND anterior.unidad_origen=canonico.unidad_origen
        AND anterior.estado_derivado='instalado'
        AND (
            COALESCE(anterior.fecha_instalado,anterior.fecha_actualizacion,anterior.fecha_creacion)
                < COALESCE(canonico.fecha_instalado,canonico.fecha_actualizacion,canonico.fecha_creacion)
            OR (
                COALESCE(anterior.fecha_instalado,anterior.fecha_actualizacion,anterior.fecha_creacion)
                    = COALESCE(canonico.fecha_instalado,canonico.fecha_actualizacion,canonico.fecha_creacion)
                AND anterior.id<canonico.id
            )
        )
  )
  AND (
      COALESCE(consolidado.fecha_instalado,consolidado.fecha_actualizacion,consolidado.fecha_creacion)
          > COALESCE(canonico.fecha_instalado,canonico.fecha_actualizacion,canonico.fecha_creacion)
      OR (
          COALESCE(consolidado.fecha_instalado,consolidado.fecha_actualizacion,consolidado.fecha_creacion)
              = COALESCE(canonico.fecha_instalado,canonico.fecha_actualizacion,canonico.fecha_creacion)
          AND consolidado.id>canonico.id
      )
  );

-- La reserva unica deja de ser solamente temporal: permanece en el hilo
-- canonico instalado para impedir otro trabajo sobre el mismo tratamiento.
UPDATE trabajo_laboratorio canonico
INNER JOIN trabajo_laboratorio_consolidacion consolidacion
    ON consolidacion.id_trabajo_canonicoFK=canonico.id
LEFT JOIN trabajo_laboratorio ocupado
    ON ocupado.id<>canonico.id
    AND ocupado.cod_detalle_activo_unico=canonico.cod_detalle_ventaFK
    AND ocupado.unidad_origen=canonico.unidad_origen
SET canonico.cod_detalle_activo_unico=canonico.cod_detalle_ventaFK
WHERE canonico.estado_derivado='instalado'
  AND canonico.cod_detalle_activo_unico IS NULL
  AND ocupado.id IS NULL;

COMMIT;
