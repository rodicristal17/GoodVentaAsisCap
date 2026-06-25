-- Permiso para el modulo gerencial de comparativa automatica.
-- No modifica ventas, cobros ni datos historicos.

SET @codigo_permiso := 'VERCOMPARATIVAVENTASCOBRANZAS';
SET @formulario_permiso := 'DASHBOARD GERENCIAL';
SET @nombre_permiso := 'VER COMPARATIVA DE VENTAS Y COBRANZAS';

INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 29, @formulario_permiso, @codigo_permiso, @nombre_permiso, 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (
    SELECT 1
    FROM listadodeacceso
    WHERE codigo = @codigo_permiso
      AND tipo = 'Administrativo'
);

SET @id_permiso := (
    SELECT idlistadodeacceso
    FROM listadodeacceso
    WHERE codigo = @codigo_permiso
      AND tipo = 'Administrativo'
    ORDER BY idlistadodeacceso DESC
    LIMIT 1
);

INSERT INTO detallesniveles (accion, idlistadodeacceso, cod_nivelesfk)
SELECT
    CASE
        WHEN UPPER(IFNULL(ln.nombre, '')) = 'ADMINISTRATIVO' THEN 'SI'
        ELSE 'NO'
    END,
    @id_permiso,
    ln.cod_niveles
FROM listado_niveles ln
WHERE ln.tipo = 'Administrativo'
  AND ln.estado = 'Activo'
  AND @id_permiso IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM detallesniveles dn
      WHERE dn.idlistadodeacceso = @id_permiso
        AND dn.cod_nivelesfk = ln.cod_niveles
  );

INSERT INTO accesosuser (idlistadodeaccesoFK, tipo, usuarios_idusario, accion)
SELECT @id_permiso, 'Administrativo', u.cod_usuario, dn.accion
FROM usuario u
INNER JOIN detallesniveles dn
    ON dn.idlistadodeacceso = @id_permiso
   AND dn.cod_nivelesfk = u.acceso
WHERE u.estado = 'Activo'
  AND @id_permiso IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM accesosuser au
      WHERE au.idlistadodeaccesoFK = @id_permiso
        AND au.tipo = 'Administrativo'
        AND au.usuarios_idusario = u.cod_usuario
  );
