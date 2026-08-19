-- Clinident Salud / Sistema Telar
-- Habilita para laboratorio ocho incrustaciones activas de OPERATORIA sin
-- cambiar su categoria, precios, stock ni tratamientos historicos.
--
-- La actualizacion es idempotente: solamente completa productos que todavia
-- no tienen una decision explicita de laboratorio ni un modo configurado.

START TRANSACTION;

UPDATE producto p
INNER JOIN categoria c ON c.cod_categoria = p.cod_categoriaFK
SET p.requiere_laboratorio = 1,
    p.modo_individualizacion = 'pieza_individual'
WHERE c.cod_categoria = 86
  AND UPPER(TRIM(c.descripcion)) = 'OPERATORIA'
  AND p.estado = 'Activo'
  AND p.cod_producto IN (
      '10066', '10128', '10043', '10125',
      '10044', '10126', '10060', '10127'
  )
  AND p.requiere_laboratorio IS NULL
  AND (p.modo_individualizacion IS NULL OR TRIM(p.modo_individualizacion) = '');

COMMIT;

-- Reversion controlada, unicamente si no hubo decisiones manuales posteriores:
-- UPDATE producto
-- SET requiere_laboratorio=NULL, modo_individualizacion=NULL
-- WHERE cod_categoriaFK=86
--   AND cod_producto IN ('10066','10128','10043','10125','10044','10126','10060','10127')
--   AND requiere_laboratorio=1
--   AND modo_individualizacion='pieza_individual';
