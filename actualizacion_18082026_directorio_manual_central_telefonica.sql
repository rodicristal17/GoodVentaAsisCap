-- Clinident Salud / Sistema Telar
-- Directorio manual de extensiones y cargo visible.
-- Compatible con MySQL 5.6 y PHP 7.2.
--
-- Migracion aditiva: no modifica Issabel, Asterisk, CDR ni llamadas.

SET NAMES utf8mb4;

SET @sql := IF(
  EXISTS(
    SELECT 1 FROM information_schema.columns
    WHERE table_schema=DATABASE()
      AND table_name='central_telefonica_directorio'
      AND column_name='cargo_visible'
  ),
  'SELECT 1',
  "ALTER TABLE central_telefonica_directorio ADD COLUMN cargo_visible VARCHAR(100) NOT NULL DEFAULT '' AFTER descripcion"
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

START TRANSACTION;

INSERT INTO central_telefonica_directorio
  (extension,tipo,nombre,descripcion,cargo_visible,cod_usuarioFK,cod_localFK,
   sede_nombre,fuente,activo,fecha_ultima_fuente,fecha_creacion,fecha_actualizacion)
SELECT
  s.extension,'interna',s.nombre_tecnico,s.nombre_tecnico,s.cargo,NULL,
  (SELECT MIN(l.cod_local) FROM local l
   WHERE UPPER(TRIM(IFNULL(l.Nombre,'')))=UPPER(TRIM(s.sede))
     AND UPPER(TRIM(IFNULL(l.estado,'')))='ACTIVO'),
  s.sede,'telar',1,NULL,NOW(),NOW()
FROM (
  SELECT '1000' extension,'recepcion-sl' nombre_tecnico,'Recepción' cargo,'Casa Central' sede
  UNION ALL SELECT '1001','caja-sl','Caja','Casa Central'
  UNION ALL SELECT '1002','admin-sl','Administración','Casa Central'
  UNION ALL SELECT '1003','admin-general','Administración general','Casa Central'
  UNION ALL SELECT '1004','tesoreria','Tesorería','Casa Central'
  UNION ALL SELECT '1005','judiciales','Judiciales','Casa Central'
  UNION ALL SELECT '1006','contabilidad','Contabilidad','Casa Central'
  UNION ALL SELECT '1007','marketing','Marketing','Casa Central'
  UNION ALL SELECT '1009','cobranzas','Cobranzas','Casa Central'
  UNION ALL SELECT '1010','ceo','CEO','Casa Central'
  UNION ALL SELECT '1011','cobranza-2','Cobranzas 2','Casa Central'
  UNION ALL SELECT '2000','recepcion-vi','Recepción','Villa Industrial'
  UNION ALL SELECT '2002','caja-vi','Caja','Villa Industrial'
  UNION ALL SELECT '2003','admin-vi','Administración','Villa Industrial'
  UNION ALL SELECT '2100','recepcion-vm','Recepción','Villa Morra'
  UNION ALL SELECT '2101','caja-vm','Caja','Villa Morra'
  UNION ALL SELECT '2102','admin-vm','Administración','Villa Morra'
  UNION ALL SELECT '2200','recepcion-pm','Recepción','Padre Molas'
  UNION ALL SELECT '2201','caja-pm','Caja','Padre Molas'
  UNION ALL SELECT '2202','admin-pm','Administración','Padre Molas'
  UNION ALL SELECT '2300','recepcion-cc','Recepción','Cerro Corá'
  UNION ALL SELECT '2301','caja-cc','Caja','Cerro Corá'
  UNION ALL SELECT '2302','admin-cc','Administración','Cerro Corá'
) s
ON DUPLICATE KEY UPDATE
  tipo=IF(TRIM(tipo)='',VALUES(tipo),tipo),
  nombre=IF(TRIM(nombre)='',VALUES(nombre),nombre),
  descripcion=IF(TRIM(descripcion)='',VALUES(descripcion),descripcion),
  cargo_visible=IF(TRIM(cargo_visible)='',VALUES(cargo_visible),cargo_visible),
  cod_localFK=IFNULL(cod_localFK,VALUES(cod_localFK)),
  sede_nombre=IF(TRIM(sede_nombre)='',VALUES(sede_nombre),sede_nombre),
  activo=1,
  fecha_actualizacion=NOW();

COMMIT;

SELECT COUNT(*) AS extensiones_precargadas
FROM central_telefonica_directorio
WHERE extension IN (
  '1000','1001','1002','1003','1004','1005','1006','1007','1009','1010','1011',
  '2000','2002','2003','2100','2101','2102','2200','2201','2202','2300','2301','2302'
);

SELECT COUNT(*) AS cargos_precargados
FROM central_telefonica_directorio
WHERE extension IN (
  '1000','1001','1002','1003','1004','1005','1006','1007','1009','1010','1011',
  '2000','2002','2003','2100','2101','2102','2200','2201','2202','2300','2301','2302'
)
  AND TRIM(cargo_visible)<>'';
