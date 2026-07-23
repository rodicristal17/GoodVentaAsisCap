-- Clinident Salud / Sistema Telar
-- Configuracion inicial, explicita y reversible de productos activos de
-- PROTESIS para el modulo de trabajos de laboratorio dental.
--
-- Criterios:
--   * No se modifica la categoria completa: cada producto conserva su modo.
--   * Los cuatro procedimientos de cementado se declaran explicitamente como
--     actos clinicos que no originan un trabajo nuevo de laboratorio.
--   * Los puentes tarifados por corona/diente se mantienen como
--     pieza_individual. Los detalles historicos con cantidad mayor que uno no
--     se alteran y deben regularizarse por la bandeja historica.
--   * Los nueve productos inactivos no se modifican.
--   * Solo se completan filas que aun no poseen configuracion explicita, para
--     no sobrescribir decisiones posteriores o propias de otro ambiente.

START TRANSACTION;

-- Actos clinicos de cementado: forman parte de PROTESIS, pero no generan por
-- si mismos una orden nueva al mecanico dental.
UPDATE producto p
INNER JOIN categoria c ON c.cod_categoria = p.cod_categoriaFK
SET p.requiere_laboratorio = 0,
    p.modo_individualizacion = NULL
WHERE c.cod_categoria = 91
  AND UPPER(TRIM(c.descripcion)) = 'PROTESIS'
  AND p.estado = 'Activo'
  AND p.cod_producto IN ('10172', '10085', '10086', '10084')
  AND p.requiere_laboratorio IS NULL
  AND (p.modo_individualizacion IS NULL OR TRIM(p.modo_individualizacion) = '');

-- Unidad fisica independiente por pieza. Los cinco productos de puente se
-- conservan por corona/diente para respetar la facturacion y los vinculos
-- historicos existentes.
UPDATE producto p
INNER JOIN categoria c ON c.cod_categoria = p.cod_categoriaFK
SET p.requiere_laboratorio = 1,
    p.modo_individualizacion = 'pieza_individual'
WHERE c.cod_categoria = 91
  AND UPPER(TRIM(c.descripcion)) = 'PROTESIS'
  AND p.estado = 'Activo'
  AND p.cod_producto IN (
      '10220', '10147', '10075', '10129', '10130', '10083', '10035',
      '10156', '10182', '10145', '10079', '10080', '10081', '10131',
      '10217', '10133', '10135', '10225', '10132', '10014', '10134'
  )
  AND p.requiere_laboratorio IS NULL
  AND (p.modo_individualizacion IS NULL OR TRIM(p.modo_individualizacion) = '');

-- Protesis completas o parciales que se gestionan por arcada.
UPDATE producto p
INNER JOIN categoria c ON c.cod_categoria = p.cod_categoriaFK
SET p.requiere_laboratorio = 1,
    p.modo_individualizacion = 'arcada'
WHERE c.cod_categoria = 91
  AND UPPER(TRIM(c.descripcion)) = 'PROTESIS'
  AND p.estado = 'Activo'
  AND p.cod_producto IN ('10138', '10137', '10016', '10141', '10139', '10223')
  AND p.requiere_laboratorio IS NULL
  AND (p.modo_individualizacion IS NULL OR TRIM(p.modo_individualizacion) = '');

-- Protesis unilaterales que requieren identificar un sector o conjunto de
-- piezas sin convertir cada una en un trabajo fisico separado.
UPDATE producto p
INNER JOIN categoria c ON c.cod_categoria = p.cod_categoriaFK
SET p.requiere_laboratorio = 1,
    p.modo_individualizacion = 'sector'
WHERE c.cod_categoria = 91
  AND UPPER(TRIM(c.descripcion)) = 'PROTESIS'
  AND p.estado = 'Activo'
  AND p.cod_producto IN ('10218', '10219')
  AND p.requiere_laboratorio IS NULL
  AND (p.modo_individualizacion IS NULL OR TRIM(p.modo_individualizacion) = '');

-- Dispositivos, reparaciones y componentes que no deben obligar a inventar
-- una pieza dental individual.
UPDATE producto p
INNER JOIN categoria c ON c.cod_categoria = p.cod_categoriaFK
SET p.requiere_laboratorio = 1,
    p.modo_individualizacion = 'dispositivo'
WHERE c.cod_categoria = 91
  AND UPPER(TRIM(c.descripcion)) = 'PROTESIS'
  AND p.estado = 'Activo'
  AND p.cod_producto IN ('10136', '10144', '10140', '10189', '10113', '10154', '10146')
  AND p.requiere_laboratorio IS NULL
  AND (p.modo_individualizacion IS NULL OR TRIM(p.modo_individualizacion) = '');

COMMIT;

-- Comprobacion esperada luego de aplicar:
--   36 activos con requiere_laboratorio=1 y modo valido.
--    4 activos con requiere_laboratorio=0 (cementados).
--    9 inactivos sin modificacion y solo disponibles como antecedente.

-- Reversion controlada para un ambiente que no haya recibido ajustes
-- manuales posteriores: restaurar a NULL solamente los codigos de esta
-- migracion y volver a ejecutar el verificador en modo lectura.
-- UPDATE producto
-- SET requiere_laboratorio=NULL, modo_individualizacion=NULL
-- WHERE cod_categoriaFK=91
--   AND cod_producto IN (
--     '10172','10085','10086','10084','10220','10147','10075','10129',
--     '10130','10083','10035','10156','10182','10145','10079','10080',
--     '10081','10131','10217','10133','10135','10225','10132','10014',
--     '10134','10138','10137','10016','10141','10139','10223','10218',
--     '10219','10136','10144','10140','10189','10113','10154','10146'
--   );
