-- Reversion conservadora de Central Telefonica - Directorio.
--
-- No se eliminan la tabla ni las columnas porque contienen snapshots de
-- atribucion historica. Para volver al comportamiento anterior basta revertir
-- los archivos PHP/JavaScript/CSS y desactivar TELAR_ISSABEL_DIRECTORY_ENABLED.

SELECT COUNT(*) AS directorio_conservado
FROM central_telefonica_directorio;
