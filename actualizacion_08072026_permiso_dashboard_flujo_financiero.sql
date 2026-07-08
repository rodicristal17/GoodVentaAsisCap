-- Permiso por usuario para mostrar u ocultar el dashboard de composicion economica por sucursal.
-- No modifica movimientos, pagos, gastos ni datos historicos.

SET @codigo_permiso := 'VERDASHBOARDFLUJOFINANCIERO';
SET @codigo_base := 'VERLISTADOEGRESOINGRESO';
SET @formulario_permiso := 'DASHBOARD GERENCIAL';
SET @nombre_permiso := 'VER COMPOSICION ECONOMICA POR SUCURSAL';

SET @id_permiso := (
    SELECT idlistadodeacceso
    FROM listadodeacceso
    WHERE codigo = @codigo_permiso
      AND tipo = 'Administrativo'
    ORDER BY idlistadodeacceso DESC
    LIMIT 1
);

SET @id_permiso_repetido := (
    SELECT COUNT(1)
    FROM listadodeacceso
    WHERE idlistadodeacceso = @id_permiso
);

SET @id_permiso_nuevo := (
    SELECT IFNULL(MAX(idlistadodeacceso), 0) + 1
    FROM listadodeacceso
);

UPDATE listadodeacceso
SET idlistadodeacceso = @id_permiso_nuevo
WHERE codigo = @codigo_permiso
  AND tipo = 'Administrativo'
  AND @id_permiso IS NOT NULL
  AND (@id_permiso = 0 OR @id_permiso_repetido > 1);

SET @id_permiso := (
    SELECT idlistadodeacceso
    FROM listadodeacceso
    WHERE codigo = @codigo_permiso
      AND tipo = 'Administrativo'
    ORDER BY idlistadodeacceso DESC
    LIMIT 1
);

SET @id_permiso_insertar := IFNULL(
    @id_permiso,
    (SELECT IFNULL(MAX(idlistadodeacceso), 0) + 1 FROM listadodeacceso)
);

INSERT INTO listadodeacceso (idlistadodeacceso, nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT @id_permiso_insertar, 29, @formulario_permiso, @codigo_permiso, @nombre_permiso, 'NO', 20, 'Administrativo'
FROM DUAL
WHERE @id_permiso IS NULL;

SET @id_permiso := (
    SELECT idlistadodeacceso
    FROM listadodeacceso
    WHERE codigo = @codigo_permiso
      AND tipo = 'Administrativo'
    ORDER BY idlistadodeacceso DESC
    LIMIT 1
);

SET @id_permiso_base := (
    SELECT idlistadodeacceso
    FROM listadodeacceso
    WHERE codigo = @codigo_base
      AND tipo = 'Administrativo'
    ORDER BY idlistadodeacceso DESC
    LIMIT 1
);

SET @idx_accesosuser_permiso_usuario := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'accesosuser'
      AND INDEX_NAME = 'idx_accesosuser_permiso_usuario'
);

SET @sql_idx_accesosuser := IF(
    @idx_accesosuser_permiso_usuario = 0,
    'ALTER TABLE accesosuser ADD INDEX idx_accesosuser_permiso_usuario (idlistadodeaccesoFK, tipo, usuarios_idusario)',
    'DO 0'
);
PREPARE stmt_idx_accesosuser FROM @sql_idx_accesosuser;
EXECUTE stmt_idx_accesosuser;
DEALLOCATE PREPARE stmt_idx_accesosuser;

INSERT INTO detallesniveles (accion, idlistadodeacceso, cod_nivelesfk)
SELECT
    COALESCE(
        base_nivel.accion,
        CASE WHEN UPPER(IFNULL(niv.nombre, '')) = 'ADMINISTRATIVO' THEN 'SI' ELSE 'NO' END
    ),
    @id_permiso,
    niv.cod_niveles
FROM listado_niveles niv
LEFT JOIN detallesniveles base_nivel
    ON base_nivel.cod_nivelesfk = niv.cod_niveles
   AND base_nivel.idlistadodeacceso = @id_permiso_base
WHERE niv.tipo = 'Administrativo'
  AND niv.estado = 'Activo'
  AND @id_permiso IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM detallesniveles existente
      WHERE existente.idlistadodeacceso = @id_permiso
        AND existente.cod_nivelesfk = niv.cod_niveles
  );

INSERT INTO accesosuser (idlistadodeaccesoFK, tipo, usuarios_idusario, accion)
SELECT
    @id_permiso,
    'Administrativo',
    us.cod_usuario,
    COALESCE(
        base_user.accion,
        base_nivel.accion,
        CASE WHEN UPPER(IFNULL(niv.nombre, '')) = 'ADMINISTRATIVO' THEN 'SI' ELSE 'NO' END
    )
FROM usuario us
LEFT JOIN listado_niveles niv
    ON niv.cod_niveles = us.Acceso
LEFT JOIN detallesniveles base_nivel
    ON base_nivel.cod_nivelesfk = us.Acceso
   AND base_nivel.idlistadodeacceso = @id_permiso_base
LEFT JOIN accesosuser base_user
    ON base_user.idlistadodeaccesoFK = @id_permiso_base
   AND base_user.tipo = 'Administrativo'
   AND base_user.usuarios_idusario = us.cod_usuario
WHERE us.estado = 'Activo'
  AND @id_permiso IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM accesosuser existente
      WHERE existente.idlistadodeaccesoFK = @id_permiso
        AND existente.tipo = 'Administrativo'
        AND existente.usuarios_idusario = us.cod_usuario
  );
