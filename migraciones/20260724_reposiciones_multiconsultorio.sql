ALTER TABLE reposiciones_insumos
    MODIFY COLUMN consultorio_id INT NULL;

ALTER TABLE reposiciones_insumos_detalle
    ADD COLUMN consultorio_id INT NOT NULL DEFAULT 0 AFTER id_reposicion;

UPDATE reposiciones_insumos_detalle d
INNER JOIN reposiciones_insumos r ON r.id_reposicion=d.id_reposicion
SET d.consultorio_id=r.consultorio_id
WHERE d.consultorio_id=0
  AND r.consultorio_id IS NOT NULL;

ALTER TABLE reposiciones_insumos_detalle
    DROP INDEX uq_reposicion_insumo_variante,
    ADD UNIQUE KEY uq_reposicion_consultorio_insumo (id_reposicion, consultorio_id, insumo_id, id_variante),
    ADD KEY idx_reposicion_detalle_consultorio (consultorio_id);
