-- Clinident Salud / Sistema Telar
-- Alta guiada de funcionarios: perfil minimo para mecanicos dentales.
-- Compatible con MySQL 5.6. Idempotente.

START TRANSACTION;

-- Garantiza que los tres permisos operativos existan, sin concederlos
-- automaticamente a otros perfiles o usuarios.
INSERT INTO listadodeacceso (nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT 5,'TRABAJOS LABORATORIO','VERTRABAJOSLABORATORIO','Ver','NO',NULL,'Administrativo'
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso WHERE codigo='VERTRABAJOSLABORATORIO'
);

INSERT INTO listadodeacceso (nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT 5,'TRABAJOS LABORATORIO','RECIBIRTRABAJOLABORATORIO','Confirmar recepcion','NO',NULL,'Administrativo'
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso WHERE codigo='RECIBIRTRABAJOLABORATORIO'
);

INSERT INTO listadodeacceso (nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT 5,'TRABAJOS LABORATORIO','ENTREGARTRABAJOLABORATORIO','Entregar','NO',NULL,'Administrativo'
WHERE NOT EXISTS (
  SELECT 1 FROM listadodeacceso WHERE codigo='ENTREGARTRABAJOLABORATORIO'
);

INSERT INTO listado_niveles (nombre,estado,tipo)
SELECT 'MECANICO DENTAL / LABORATORIO','Activo','Administrativo'
WHERE NOT EXISTS (
  SELECT 1
  FROM listado_niveles
  WHERE UPPER(TRIM(nombre))='MECANICO DENTAL / LABORATORIO'
    AND tipo='Administrativo'
);

SET @nivel_mecanico_dental := (
  SELECT cod_niveles
  FROM listado_niveles
  WHERE UPPER(TRIM(nombre))='MECANICO DENTAL / LABORATORIO'
    AND tipo='Administrativo'
  ORDER BY cod_niveles ASC
  LIMIT 1
);

UPDATE listado_niveles
SET estado='Activo'
WHERE cod_niveles=@nivel_mecanico_dental;

-- El perfil conserva una fila por permiso. Solamente los tres permisos
-- operativos del laboratorio quedan habilitados.
INSERT INTO detallesniveles (accion,idlistadodeacceso,cod_nivelesfk)
SELECT
  CASE
    WHEN la.codigo IN (
      'VERTRABAJOSLABORATORIO',
      'RECIBIRTRABAJOLABORATORIO',
      'ENTREGARTRABAJOLABORATORIO'
    ) THEN 'SI'
    ELSE 'NO'
  END,
  la.idlistadodeacceso,
  @nivel_mecanico_dental
FROM listadodeacceso la
LEFT JOIN detallesniveles dn
  ON dn.idlistadodeacceso=la.idlistadodeacceso
 AND dn.cod_nivelesfk=@nivel_mecanico_dental
WHERE dn.iddetallesniveles IS NULL;

UPDATE detallesniveles dn
INNER JOIN listadodeacceso la
  ON la.idlistadodeacceso=dn.idlistadodeacceso
SET dn.accion=CASE
  WHEN la.codigo IN (
    'VERTRABAJOSLABORATORIO',
    'RECIBIRTRABAJOLABORATORIO',
    'ENTREGARTRABAJOLABORATORIO'
  ) THEN 'SI'
  ELSE 'NO'
END
WHERE dn.cod_nivelesfk=@nivel_mecanico_dental;

-- Sincroniza solamente cuentas que ya tuvieran asignado este perfil.
DELETE au
FROM accesosuser au
INNER JOIN usuario u ON u.cod_usuario=au.usuarios_idusario
WHERE u.acceso=@nivel_mecanico_dental;

INSERT INTO accesosuser (idlistadodeaccesoFK,tipo,usuarios_idusario,accion)
SELECT dn.idlistadodeacceso,'Administrativo',u.cod_usuario,dn.accion
FROM usuario u
INNER JOIN detallesniveles dn ON dn.cod_nivelesfk=@nivel_mecanico_dental
WHERE u.acceso=@nivel_mecanico_dental;

COMMIT;

-- Verificacion esperada: tres permisos SI y ninguno adicional.
SELECT
  ln.cod_niveles,
  ln.nombre,
  SUM(CASE WHEN dn.accion='SI' THEN 1 ELSE 0 END) AS permisos_habilitados,
  GROUP_CONCAT(CASE WHEN dn.accion='SI' THEN la.codigo END ORDER BY la.codigo SEPARATOR ', ') AS permisos
FROM listado_niveles ln
INNER JOIN detallesniveles dn ON dn.cod_nivelesfk=ln.cod_niveles
INNER JOIN listadodeacceso la ON la.idlistadodeacceso=dn.idlistadodeacceso
WHERE ln.cod_niveles=@nivel_mecanico_dental
GROUP BY ln.cod_niveles,ln.nombre;

-- Reversion controlada (no ejecutar junto con la migracion):
-- DELETE FROM accesosuser WHERE usuarios_idusario IN (
--   SELECT cod_usuario FROM usuario WHERE acceso=@nivel_mecanico_dental
-- );
-- UPDATE usuario SET acceso=4 WHERE acceso=@nivel_mecanico_dental;
-- DELETE FROM detallesniveles WHERE cod_nivelesfk=@nivel_mecanico_dental;
-- DELETE FROM listado_niveles WHERE cod_niveles=@nivel_mecanico_dental;
