-- Clinident Salud / Sistema Telar
-- Corrige el alcance odontologico de seis tratamientos unitarios cuya
-- individualizacion de laboratorio ya esta configurada por pieza.
--
-- La actualizacion es cerrada e idempotente: solo cambia productos activos
-- que todavia conservan exactamente la combinacion contradictoria
-- arcada + pieza_individual. No modifica ventas, tratamientos, ubicaciones
-- historicas ni trabajos de laboratorio.

START TRANSACTION;

UPDATE producto p
INNER JOIN categoria c ON c.cod_categoria = p.cod_categoriaFK
SET p.alcance_odontologico = 'pieza_dental'
WHERE c.cod_categoria = 91
  AND UPPER(TRIM(c.descripcion)) = 'PROTESIS'
  AND p.estado = 'Activo'
  AND p.cod_producto IN ('10014', '10131', '10132', '10145', '10217', '10220')
  AND p.requiere_laboratorio = 1
  AND p.modo_individualizacion = 'pieza_individual'
  AND LOWER(TRIM(COALESCE(p.alcance_odontologico, ''))) = 'arcada';

COMMIT;

-- Comprobacion esperada luego de aplicar:
--   * Los seis productos conservan pieza_individual.
--   * Todos poseen alcance_odontologico = pieza_dental.
--   * No se reescribe ningun vinculo odontologico existente.

-- Reversion controlada, solo si no hubo ajustes posteriores de catalogo:
-- UPDATE producto
-- SET alcance_odontologico = 'arcada'
-- WHERE cod_categoriaFK = 91
--   AND cod_producto IN ('10014', '10131', '10132', '10145', '10217', '10220')
--   AND requiere_laboratorio = 1
--   AND modo_individualizacion = 'pieza_individual'
--   AND alcance_odontologico = 'pieza_dental';
