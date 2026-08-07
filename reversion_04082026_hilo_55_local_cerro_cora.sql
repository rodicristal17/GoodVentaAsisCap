-- Reversion puntual. Ejecutar solamente si el Hilo 55 no recibio movimientos
-- nuevos despues de aplicar la actualizacion.
UPDATE interconsulta
SET cod_localFK = NULL
WHERE cod_interConsulta = 55
  AND cod_localFK = 3;

