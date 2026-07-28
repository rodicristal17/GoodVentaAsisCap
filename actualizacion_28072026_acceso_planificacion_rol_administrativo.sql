-- Clinident Salud / Sistema Telar
-- Habilita la gestion de Planificacion para el rol ADMINISTRATIVO.
-- Compatible con MySQL 5.6 y PHP 7.2.
--
-- Alcance:
-- - Rol exacto ADMINISTRATIVO, tipo Administrativo, id 1.
-- - Concede ver, gestionar, recurrencias, historial y todas las sucursales.
-- - PROPONERPLANIFICACIONESPECIALISTAS no se concede porque el gestor
--   confirma directamente.
-- - Conserva todos los demas permisos y excepciones individuales.
-- - No modifica accesos rapidos personales, Agenda ni datos clinicos.
--
-- Aplicar solamente con respaldo validado y ventana controlada.

SET NAMES utf8mb4;
START TRANSACTION;

SET @rol_administrativo_id := 1;

DROP TEMPORARY TABLE IF EXISTS tmp_planificacion_admin_guard;
CREATE TEMPORARY TABLE tmp_planificacion_admin_guard (
  valor TINYINT NOT NULL,
  PRIMARY KEY (valor)
) ENGINE=MEMORY;

-- La segunda insercion provoca error y revierte la conexion si el rol exacto
-- no existe una unica vez.
INSERT INTO tmp_planificacion_admin_guard (valor) VALUES (1);
INSERT INTO tmp_planificacion_admin_guard (valor)
SELECT 1
WHERE (
  SELECT COUNT(*)
  FROM listado_niveles
  WHERE cod_niveles=@rol_administrativo_id
    AND UPPER(TRIM(nombre))='ADMINISTRATIVO'
    AND UPPER(TRIM(tipo))='ADMINISTRATIVO'
    AND UPPER(TRIM(estado))='ACTIVO'
)<>1;

DROP TEMPORARY TABLE IF EXISTS tmp_planificacion_admin_permisos;
CREATE TEMPORARY TABLE tmp_planificacion_admin_permisos (
  idlistadodeacceso INT NOT NULL,
  codigo VARCHAR(80) NOT NULL,
  PRIMARY KEY (idlistadodeacceso),
  UNIQUE KEY uq_tmp_planificacion_admin_codigo (codigo)
) ENGINE=MEMORY;

INSERT INTO tmp_planificacion_admin_permisos
  (idlistadodeacceso, codigo)
SELECT idlistadodeacceso, UPPER(TRIM(codigo))
FROM listadodeacceso
WHERE UPPER(TRIM(codigo)) IN (
  'VERPLANIFICACIONESPECIALISTAS',
  'GESTIONARPLANIFICACIONESPECIALISTAS',
  'GESTIONARRECURRENCIASPLANIFICACION',
  'VERHISTORIALPLANIFICACION',
  'VERPLANIFICACIONTODASSUCURSALES'
);

-- La migracion no continua con un catalogo parcial o duplicado.
INSERT INTO tmp_planificacion_admin_guard (valor)
SELECT 1
WHERE (SELECT COUNT(*) FROM tmp_planificacion_admin_permisos)<>5;

DROP TEMPORARY TABLE IF EXISTS tmp_planificacion_admin_afectados;
CREATE TEMPORARY TABLE tmp_planificacion_admin_afectados (
  cod_usuario INT NOT NULL,
  PRIMARY KEY (cod_usuario)
) ENGINE=MEMORY;

INSERT INTO tmp_planificacion_admin_afectados (cod_usuario)
SELECT DISTINCT u.cod_usuario
FROM usuario u
INNER JOIN listado_niveles ln
  ON ln.cod_niveles=CAST(u.acceso AS UNSIGNED)
 AND ln.cod_niveles=@rol_administrativo_id
 AND UPPER(TRIM(ln.nombre))='ADMINISTRATIVO'
 AND UPPER(TRIM(ln.tipo))='ADMINISTRATIVO'
CROSS JOIN tmp_planificacion_admin_permisos tp
LEFT JOIN accesosuser au
  ON au.usuarios_idusario=u.cod_usuario
 AND au.idlistadodeaccesoFK=tp.idlistadodeacceso
 AND au.tipo='Administrativo'
 AND UPPER(TRIM(au.accion))='SI'
WHERE au.idaccesosUser IS NULL;

-- Actualiza solamente los cinco permisos seleccionados de la plantilla.
UPDATE detallesniveles dn
INNER JOIN tmp_planificacion_admin_permisos tp
  ON tp.idlistadodeacceso=dn.idlistadodeacceso
SET dn.accion='SI'
WHERE dn.cod_nivelesfk=@rol_administrativo_id
  AND UPPER(TRIM(IFNULL(dn.accion,'')))<>'SI';

INSERT INTO detallesniveles
  (accion, idlistadodeacceso, cod_nivelesfk)
SELECT 'SI', tp.idlistadodeacceso, @rol_administrativo_id
FROM tmp_planificacion_admin_permisos tp
LEFT JOIN detallesniveles dn
  ON dn.idlistadodeacceso=tp.idlistadodeacceso
 AND dn.cod_nivelesfk=@rol_administrativo_id
WHERE dn.iddetallesniveles IS NULL;

-- Sincroniza unicamente estos permisos con todas las cuentas del rol,
-- incluidas las inactivas. Una cuenta inactiva continua sin poder ingresar.
UPDATE accesosuser au
INNER JOIN usuario u
  ON u.cod_usuario=au.usuarios_idusario
 AND CAST(u.acceso AS UNSIGNED)=@rol_administrativo_id
INNER JOIN tmp_planificacion_admin_permisos tp
  ON tp.idlistadodeacceso=au.idlistadodeaccesoFK
SET au.accion='SI'
WHERE au.tipo='Administrativo'
  AND UPPER(TRIM(IFNULL(au.accion,'')))<>'SI';

INSERT INTO accesosuser
  (formulario, anhadir, modificar, buscar, informes, frmname, orden,
   usuarios_idusario, accion, agrupacion, idlistadodeaccesoFK, tipo)
SELECT
  NULL, NULL, NULL, NULL, NULL, '', IFNULL(la.orden,0),
  u.cod_usuario, 'SI', NULL, tp.idlistadodeacceso, 'Administrativo'
FROM usuario u
INNER JOIN listado_niveles ln
  ON ln.cod_niveles=CAST(u.acceso AS UNSIGNED)
 AND ln.cod_niveles=@rol_administrativo_id
 AND UPPER(TRIM(ln.nombre))='ADMINISTRATIVO'
 AND UPPER(TRIM(ln.tipo))='ADMINISTRATIVO'
CROSS JOIN tmp_planificacion_admin_permisos tp
INNER JOIN listadodeacceso la
  ON la.idlistadodeacceso=tp.idlistadodeacceso
LEFT JOIN accesosuser au
  ON au.usuarios_idusario=u.cod_usuario
 AND au.idlistadodeaccesoFK=tp.idlistadodeacceso
 AND au.tipo='Administrativo'
WHERE au.idaccesosUser IS NULL;

-- Auditoria de las cuentas cuya matriz no contenia todos los permisos.
INSERT INTO usuario_historial_cambios
  (cod_usuarioFK, campo, valor_anterior, valor_nuevo, fecha_hora,
   cod_usuario_modifico, origen, estado)
SELECT
  a.cod_usuario,
  'Permisos heredados del rol',
  'Planificacion administrativa incompleta',
  'VER, GESTIONAR, RECURRENCIAS, HISTORIAL Y TODAS LAS SUCURSALES',
  NOW(),
  NULL,
  'Migracion controlada 2026-07-28',
  'Registrado'
FROM tmp_planificacion_admin_afectados a;

COMMIT;

-- Verificacion posterior:
SELECT ln.cod_niveles, ln.nombre, COUNT(DISTINCT u.cod_usuario) AS usuarios_rol,
       SUM(UPPER(TRIM(IFNULL(u.estado,'')))='ACTIVO') AS usuarios_activos
FROM listado_niveles ln
LEFT JOIN usuario u ON CAST(u.acceso AS UNSIGNED)=ln.cod_niveles
WHERE ln.cod_niveles=@rol_administrativo_id
GROUP BY ln.cod_niveles, ln.nombre;

SELECT la.codigo, MAX(UPPER(TRIM(dn.accion))) AS permiso_rol
FROM detallesniveles dn
INNER JOIN listadodeacceso la
  ON la.idlistadodeacceso=dn.idlistadodeacceso
WHERE dn.cod_nivelesfk=@rol_administrativo_id
  AND UPPER(TRIM(la.codigo)) IN (
    'VERPLANIFICACIONESPECIALISTAS',
    'GESTIONARPLANIFICACIONESPECIALISTAS',
    'GESTIONARRECURRENCIASPLANIFICACION',
    'VERHISTORIALPLANIFICACION',
    'VERPLANIFICACIONTODASSUCURSALES'
  )
GROUP BY la.codigo
ORDER BY la.codigo;

SELECT
  COUNT(DISTINCT CONCAT(u.cod_usuario,'|',tp.idlistadodeacceso))
    AS combinaciones_usuario_permiso,
  COUNT(DISTINCT u.cod_usuario) AS usuarios_con_acceso
FROM usuario u
CROSS JOIN tmp_planificacion_admin_permisos tp
INNER JOIN accesosuser au
  ON au.usuarios_idusario=u.cod_usuario
 AND au.idlistadodeaccesoFK=tp.idlistadodeacceso
 AND au.tipo='Administrativo'
 AND UPPER(TRIM(au.accion))='SI'
WHERE CAST(u.acceso AS UNSIGNED)=@rol_administrativo_id;

-- Reversion controlada:
-- 1. Confirmar que no existan operaciones administrativas dependientes.
-- 2. Cambiar a NO los cinco permisos en detallesniveles para el rol 1.
-- 3. Cambiar a NO los mismos permisos en accesosuser para las cuentas del rol.
-- 4. Conservar la auditoria y no eliminar permisos del catalogo.
