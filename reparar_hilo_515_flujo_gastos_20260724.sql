-- Reparacion reversible del flujo de gastos del Hilo 515.
-- Conserva los pagos confirmados y una unica programacion futura mensual.
-- Compatible con MySQL 5.6+.

CREATE TABLE IF NOT EXISTS respaldo_hilo515_gastos_20260724 LIKE gastos;
CREATE TABLE IF NOT EXISTS respaldo_hilo515_mensajes_20260724 LIKE mensaje;
CREATE TABLE IF NOT EXISTS respaldo_hilo515_proyectos_20260724 LIKE proyectos_gasto;
CREATE TABLE IF NOT EXISTS respaldo_hilo515_vinculos_20260724 LIKE interconsulta_proyecto_gasto;

INSERT IGNORE INTO respaldo_hilo515_gastos_20260724
SELECT * FROM gastos
WHERE cod_interConsultaFK = 515
   OR idgastos IN (3919,3921,3922,3923,3924,3925,3926,3927,3928,3929,3930)
   OR idgastos BETWEEN 6849 AND 6860
   OR idgastos BETWEEN 6918 AND 6942
   OR idgastos BETWEEN 6951 AND 6955;

INSERT IGNORE INTO respaldo_hilo515_mensajes_20260724
SELECT m.*
FROM mensaje m
INNER JOIN respaldo_hilo515_gastos_20260724 g
  ON g.cod_mensajeFK = m.cod_mensaje;

INSERT IGNORE INTO respaldo_hilo515_proyectos_20260724
SELECT * FROM proyectos_gasto WHERE id IN (24,67);

INSERT IGNORE INTO respaldo_hilo515_vinculos_20260724
SELECT * FROM interconsulta_proyecto_gasto
WHERE cod_interConsultaFK = 515
   OR cod_proyecto_gastoFK IN (24,67);

START TRANSACTION;

-- Proyecto canonico: PAGO DE TELEFONIA ADMINISTRATIVO A NOMBRE DE CARLOS FARAONE.
UPDATE proyectos_gasto SET estado = 'activo' WHERE id = 67;
UPDATE interconsulta_proyecto_gasto
SET estado = 'activo', fecha_edit = NOW()
WHERE cod_interConsultaFK = 515 AND cod_proyecto_gastoFK = 67;

-- Conserva los cuatro pagos confirmados y los integra al proyecto canonico.
UPDATE gastos
SET cod_proyecto_gastoFK = 67
WHERE idgastos IN (3919,3921,3922,3925)
  AND LOWER(TRIM(estado)) = 'activo';

UPDATE gastos SET cod_gasto_padre = NULL WHERE idgastos = 3919;
UPDATE gastos
SET cod_gasto_padre = 3919
WHERE idgastos IN (3921,3922,3925);

-- Conserva la programacion mensual vigente del dia 6 y la une a la misma serie.
UPDATE gastos
SET cod_proyecto_gastoFK = 67,
    cod_gasto_padre = 3919,
    motivo = CONCAT(
      'Cuota ',
      idgastos - 6910,
      ' de Telefonia Personal Carlos Faraone (3919)'
    )
WHERE idgastos BETWEEN 6918 AND 6942
  AND LOWER(TRIM(estado)) = 'pendiente';

-- Actualiza los recordatorios para que reflejen la numeracion consolidada.
UPDATE mensaje m
INNER JOIN gastos g ON g.cod_mensajeFK = m.cod_mensaje
SET m.contenido = CONCAT('El gasto ', g.motivo, ' vence hoy '),
    m.estado = 'activo'
WHERE g.idgastos BETWEEN 6918 AND 6942;

-- Desactiva la cuota solicitada que duplica al pago confirmado del 06/07.
UPDATE gastos
SET estado = 'Inactivo'
WHERE idgastos = 3924
  AND LOWER(TRIM(estado)) = 'solicitado';

-- Desactiva la segunda programacion alternativa creada desde la cuota pagada 3925.
UPDATE gastos
SET estado = 'Inactivo'
WHERE idgastos BETWEEN 6951 AND 6955
  AND LOWER(TRIM(estado)) = 'pendiente';

UPDATE mensaje m
INNER JOIN gastos g ON g.cod_mensajeFK = m.cod_mensaje
SET m.estado = 'inactivo'
WHERE g.idgastos = 3924
   OR g.idgastos BETWEEN 6951 AND 6955;

-- El proyecto anterior queda solo como respaldo historico, sin mostrarse como flujo activo.
UPDATE interconsulta_proyecto_gasto
SET estado = 'inactivo', fecha_edit = NOW()
WHERE cod_interConsultaFK = 515 AND cod_proyecto_gastoFK = 24;

UPDATE proyectos_gasto
SET estado = 'inactivo'
WHERE id = 24
  AND NOT EXISTS (
    SELECT 1
    FROM gastos g
    WHERE g.cod_proyecto_gastoFK = 24
      AND LOWER(TRIM(IFNULL(g.estado,''))) IN ('activo','pendiente','solicitado')
  );

COMMIT;

-- Verificacion esperada:
-- proyecto 67: 4 pagos Activo + 25 cuotas pendientes;
-- proyecto 24: sin movimientos activos/pendientes/solicitados;
-- Hilo 515: sin gastos solicitados o pendientes vencidos.
SELECT cod_proyecto_gastoFK, LOWER(TRIM(estado)) AS estado,
       COUNT(*) AS cantidad, SUM(monto) AS total
FROM gastos
WHERE cod_interConsultaFK = 515
  AND LOWER(TRIM(IFNULL(estado,''))) IN ('activo','pendiente','solicitado')
GROUP BY cod_proyecto_gastoFK, LOWER(TRIM(estado))
ORDER BY cod_proyecto_gastoFK, estado;

SELECT COUNT(*) AS vencidos_pendientes_hilo_515
FROM gastos
WHERE cod_interConsultaFK = 515
  AND LOWER(TRIM(estado)) IN ('pendiente','solicitado')
  AND fecha <= CURDATE();
