-- Clinident Salud / Sistema Telar
-- Reversion conservadora del directorio manual.
--
-- La columna cargo_visible, las asociaciones y la auditoria se conservan para
-- evitar perdida de informacion. Solo se desactivan precargas nunca asignadas.

SET NAMES utf8mb4;

UPDATE central_telefonica_directorio
SET activo=0,fecha_actualizacion=NOW()
WHERE fuente='telar'
  AND cod_usuarioFK IS NULL
  AND extension IN (
    '1000','1001','1002','1003','1004','1005','1006','1007','1009','1010','1011',
    '2000','2002','2003','2100','2101','2102','2200','2201','2202','2300','2301','2302'
  );
