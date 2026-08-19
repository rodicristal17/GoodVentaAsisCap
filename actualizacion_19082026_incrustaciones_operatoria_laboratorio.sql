-- Clinident Salud / Sistema Telar
-- Habilita para laboratorio ocho incrustaciones activas de OPERATORIA sin
-- cambiar precios, stock ni tratamientos historicos. Normaliza de forma
-- dirigida el codigo 10043 si un ambiente anterior aun lo conserva en
-- PROTESIS; los demas productos deben estar ya en OPERATORIA.
--
-- La actualizacion es idempotente: solamente completa productos que todavia
-- no tienen una decision explicita de laboratorio ni un modo configurado.

START TRANSACTION;

-- Diferencia historica detectada en produccion: este Inlay pertenece a la
-- misma familia de Operatoria que los otros siete codigos. La correccion solo
-- procede desde PROTESIS hacia la categoria 86 comprobada como OPERATORIA y
-- mientras el producto continua sin una decision explicita de laboratorio.
UPDATE producto p
INNER JOIN categoria actual ON actual.cod_categoria = p.cod_categoriaFK
INNER JOIN categoria destino ON destino.cod_categoria = 86
SET p.cod_categoriaFK = destino.cod_categoria
WHERE p.cod_producto = '10043'
  AND p.estado = 'Activo'
  AND UPPER(TRIM(actual.descripcion)) = 'PROTESIS'
  AND UPPER(TRIM(destino.descripcion)) = 'OPERATORIA'
  AND UPPER(p.nombre_producto) LIKE '%INLAY%'
  AND p.requiere_laboratorio IS NULL
  AND (p.modo_individualizacion IS NULL OR TRIM(p.modo_individualizacion) = '');

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
-- UPDATE producto
-- SET cod_categoriaFK=91
-- WHERE cod_producto='10043'
--   AND cod_categoriaFK=86
--   AND requiere_laboratorio IS NULL
--   AND modo_individualizacion IS NULL;
