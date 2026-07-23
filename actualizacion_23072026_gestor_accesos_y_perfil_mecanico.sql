-- Clinident Salud / Sistema Telar
-- Administrador visual de accesos y perfil operativo del mecanico dental.
-- Compatible con MySQL 5.6. Idempotente.

START TRANSACTION;

-- La evidencia permite documentar avances y notas despues de tomar el hilo.
INSERT INTO listadodeacceso (nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT 5,'TRABAJOS LABORATORIO','EVIDENCIATRABAJOLABORATORIO','Agregar evidencia','NO',NULL,'Administrativo'
WHERE NOT EXISTS (
  SELECT 1
  FROM listadodeacceso
  WHERE codigo='EVIDENCIATRABAJOLABORATORIO'
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

-- Completa cualquier permiso agregado despues de la creacion original del rol.
INSERT INTO detallesniveles (accion,idlistadodeacceso,cod_nivelesfk)
SELECT
  CASE
    WHEN la.codigo IN (
      'VERTRABAJOSLABORATORIO',
      'RECIBIRTRABAJOLABORATORIO',
      'ENTREGARTRABAJOLABORATORIO',
      'EVIDENCIATRABAJOLABORATORIO'
    ) THEN 'SI'
    ELSE 'NO'
  END,
  la.idlistadodeacceso,
  @nivel_mecanico_dental
FROM listadodeacceso la
LEFT JOIN detallesniveles dn
  ON dn.idlistadodeacceso=la.idlistadodeacceso
 AND dn.cod_nivelesfk=@nivel_mecanico_dental
WHERE la.tipo='Administrativo'
  AND @nivel_mecanico_dental IS NOT NULL
  AND dn.iddetallesniveles IS NULL;

-- El perfil queda cerrado a cuatro capacidades de laboratorio.
UPDATE detallesniveles dn
INNER JOIN listadodeacceso la
  ON la.idlistadodeacceso=dn.idlistadodeacceso
SET dn.accion=CASE
  WHEN la.codigo IN (
    'VERTRABAJOSLABORATORIO',
    'RECIBIRTRABAJOLABORATORIO',
    'ENTREGARTRABAJOLABORATORIO',
    'EVIDENCIATRABAJOLABORATORIO'
  ) THEN 'SI'
  ELSE 'NO'
END
WHERE dn.cod_nivelesfk=@nivel_mecanico_dental
  AND la.tipo='Administrativo';

-- Al aplicar la migracion, las cuentas del perfil vuelven a heredar su
-- plantilla completa. No se alteran accesos de otros tipos.
DELETE au
FROM accesosuser au
INNER JOIN usuario u ON u.cod_usuario=au.usuarios_idusario
WHERE u.acceso=CAST(@nivel_mecanico_dental AS CHAR)
  AND au.tipo='Administrativo';

INSERT INTO accesosuser (idlistadodeaccesoFK,tipo,usuarios_idusario,accion)
SELECT dn.idlistadodeacceso,'Administrativo',u.cod_usuario,dn.accion
FROM usuario u
INNER JOIN detallesniveles dn ON dn.cod_nivelesfk=@nivel_mecanico_dental
INNER JOIN listadodeacceso la ON la.idlistadodeacceso=dn.idlistadodeacceso
WHERE u.acceso=CAST(@nivel_mecanico_dental AS CHAR)
  AND la.tipo='Administrativo';

-- El catalogo de accesos rapidos tambien debe respetar el permiso de entrada
-- al modulo. La seleccion personal sigue siendo solamente una preferencia.
UPDATE dashboard_access_catalog
SET permission_key='VERTRABAJOSLABORATORIO'
WHERE access_key='trabajos_mecanicos_dentales';

COMMIT;

-- Verificacion esperada: cuatro permisos habilitados y ninguno adicional.
SELECT
  ln.cod_niveles,
  ln.nombre,
  SUM(CASE WHEN dn.accion='SI' THEN 1 ELSE 0 END) AS permisos_habilitados,
  GROUP_CONCAT(
    CASE WHEN dn.accion='SI' THEN la.codigo END
    ORDER BY la.codigo SEPARATOR ', '
  ) AS permisos
FROM listado_niveles ln
INNER JOIN detallesniveles dn ON dn.cod_nivelesfk=ln.cod_niveles
INNER JOIN listadodeacceso la ON la.idlistadodeacceso=dn.idlistadodeacceso
WHERE ln.cod_niveles=@nivel_mecanico_dental
GROUP BY ln.cod_niveles,ln.nombre;

-- Reversion controlada:
-- 1. Volver EVIDENCIATRABAJOLABORATORIO a NO en detallesniveles.
-- 2. Resincronizar solamente las cuentas asignadas al perfil.
-- 3. Dejar permission_key en NULL solo si se restaura tambien el comportamiento
--    universal anterior del dashboard de laboratorio.
