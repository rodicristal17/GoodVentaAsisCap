-- Clinident Salud / Sistema Telar
-- Administracion segura de las asociaciones del directorio telefonico.
-- Compatible con MySQL 5.6 y PHP 7.2.
--
-- La migracion es aditiva: no modifica Issabel, extensiones, colas ni CDR.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS central_telefonica_directorio_evento (
  id_evento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  extension VARCHAR(20) NOT NULL,
  accion VARCHAR(30) NOT NULL,
  datos_anteriores MEDIUMTEXT NULL,
  datos_nuevos MEDIUMTEXT NULL,
  cod_usuarioFK_accion INT NOT NULL,
  ip_accion VARCHAR(45) NOT NULL DEFAULT '',
  fecha_evento DATETIME NOT NULL,
  PRIMARY KEY (id_evento),
  KEY idx_central_directorio_evento_extension (extension,fecha_evento),
  KEY idx_central_directorio_evento_usuario (cod_usuarioFK_accion,fecha_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

SET @central_permiso_grupo := 35;
SET @central_id_administrar_directorio := (
  SELECT idlistadodeacceso FROM listadodeacceso
  WHERE codigo='ADMINISTRARDIRECTORIOCENTRALTELEFONICA' AND tipo='Administrativo'
  ORDER BY idlistadodeacceso LIMIT 1
);
SET @central_id_administrar_directorio := IFNULL(
  @central_id_administrar_directorio,
  (SELECT IFNULL(MAX(idlistadodeacceso),0)+1 FROM listadodeacceso)
);

INSERT INTO listadodeacceso
  (idlistadodeacceso,nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT @central_id_administrar_directorio,@central_permiso_grupo,'CENTRAL TELEFONICA',
       'ADMINISTRARDIRECTORIOCENTRALTELEFONICA','Administrar directorio telefonico','NO',6,'Administrativo'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso
  WHERE codigo='ADMINISTRARDIRECTORIOCENTRALTELEFONICA' AND tipo='Administrativo'
);

INSERT INTO detallesniveles (accion,idlistadodeacceso,cod_nivelesfk)
SELECT 'NO',@central_id_administrar_directorio,ln.cod_niveles
FROM listado_niveles ln
WHERE ln.tipo='Administrativo'
  AND NOT EXISTS (
    SELECT 1 FROM detallesniveles dn
    WHERE dn.cod_nivelesfk=ln.cod_niveles
      AND dn.idlistadodeacceso=@central_id_administrar_directorio
  );

UPDATE detallesniveles
SET accion='NO'
WHERE idlistadodeacceso=@central_id_administrar_directorio;

INSERT INTO accesosuser
  (formulario,anhadir,modificar,buscar,informes,frmname,orden,
   usuarios_idusario,accion,agrupacion,idlistadodeaccesoFK,tipo)
SELECT
  NULL,NULL,NULL,NULL,NULL,'',6,u.cod_usuario,'NO',NULL,
  @central_id_administrar_directorio,'Administrativo'
FROM usuario u
WHERE NOT EXISTS (
  SELECT 1 FROM accesosuser au
  WHERE au.usuarios_idusario=u.cod_usuario
    AND au.idlistadodeaccesoFK=@central_id_administrar_directorio
    AND au.tipo='Administrativo'
);

UPDATE accesosuser
SET accion='NO'
WHERE idlistadodeaccesoFK=@central_id_administrar_directorio
  AND tipo='Administrativo';

UPDATE accesosuser au
INNER JOIN usuario u ON u.cod_usuario=au.usuarios_idusario
INNER JOIN persona p ON p.cod_persona=u.cod_usuario
SET au.accion='SI'
WHERE au.idlistadodeaccesoFK=@central_id_administrar_directorio
  AND au.tipo='Administrativo'
  AND u.cod_usuario=5994
  AND LOWER(TRIM(IFNULL(u.login,'')))='cf'
  AND UPPER(TRIM(IFNULL(u.tipo,'')))='ADMINISTRATIVO'
  AND UPPER(TRIM(IFNULL(u.estado,'')))='ACTIVO'
  AND UPPER(TRIM(IFNULL(p.nombre_persona,''))) LIKE 'CARLOS FARAONE CLINIDENT%';

COMMIT;

SELECT COUNT(*) AS tablas_administracion_directorio
FROM information_schema.tables
WHERE table_schema=DATABASE()
  AND table_name='central_telefonica_directorio_evento';

SELECT COUNT(*) AS usuarios_directorio_habilitados
FROM accesosuser au
INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK
WHERE la.codigo='ADMINISTRARDIRECTORIOCENTRALTELEFONICA'
  AND au.tipo='Administrativo'
  AND UPPER(TRIM(au.accion))='SI';
