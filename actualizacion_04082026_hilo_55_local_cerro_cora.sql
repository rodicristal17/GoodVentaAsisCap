-- Hilo 55: conserva la sucursal ya utilizada por sus 25 egresos historicos.
-- La condicion evita reemplazar una asignacion realizada posteriormente.
UPDATE interconsulta
SET cod_localFK = 3
WHERE cod_interConsulta = 55
  AND (cod_localFK IS NULL OR cod_localFK = 0);

