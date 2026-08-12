-- Clinident Salud / Sistema Telar
-- Habilita el informe administrativo de cuotas pagadas fuera de secuencia.
-- No modifica creditos, pagos, recibos, intereses ni registros historicos.
-- Compatible con MySQL 5.6 y ejecutable mas de una vez.

SET @cs_codigo := 'VERCUOTASSALTEADAS';

INSERT INTO listadodeacceso (nro,formulario,codigo,nombre,accion,orden,tipo)
SELECT 88,'FORMULARIO CUOTAS SALTEADAS',@cs_codigo,'VER CUOTAS SALTEADAS','NO',30,'Administrativo'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM listadodeacceso
    WHERE codigo=@cs_codigo AND tipo='Administrativo'
);

SET @cs_permiso_id := (
    SELECT idlistadodeacceso FROM listadodeacceso
    WHERE codigo=@cs_codigo AND tipo='Administrativo'
    ORDER BY idlistadodeacceso DESC LIMIT 1
);

INSERT INTO detallesniveles (accion,idlistadodeacceso,cod_nivelesfk)
SELECT CASE WHEN UPPER(TRIM(IFNULL(niv.nombre,'')))='ADMINISTRATIVO' THEN 'SI' ELSE 'NO' END,
       @cs_permiso_id,niv.cod_niveles
FROM listado_niveles niv
WHERE niv.tipo='Administrativo' AND niv.estado='Activo'
  AND @cs_permiso_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM detallesniveles dn
      WHERE dn.idlistadodeacceso=@cs_permiso_id
        AND dn.cod_nivelesfk=niv.cod_niveles
  );

INSERT INTO accesosuser (idlistadodeaccesoFK,tipo,usuarios_idusario,accion)
SELECT @cs_permiso_id,'Administrativo',us.cod_usuario,
       CASE WHEN UPPER(TRIM(IFNULL(niv.nombre,'')))='ADMINISTRATIVO' THEN 'SI' ELSE 'NO' END
FROM usuario us
LEFT JOIN listado_niveles niv ON niv.cod_niveles=us.Acceso
WHERE us.estado='Activo' AND @cs_permiso_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM accesosuser au
      WHERE au.idlistadodeaccesoFK=@cs_permiso_id
        AND au.tipo='Administrativo'
        AND au.usuarios_idusario=us.cod_usuario
  );

DELIMITER $$

DROP PROCEDURE IF EXISTS agregar_indice_cuotas_salteadas$$
CREATE PROCEDURE agregar_indice_cuotas_salteadas(
    IN p_tabla VARCHAR(64),
    IN p_indice VARCHAR(64),
    IN p_columnas VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA=DATABASE()
          AND TABLE_NAME=p_tabla
          AND INDEX_NAME=p_indice
    ) THEN
        SET @cs_sql_indice=CONCAT('ALTER TABLE `',p_tabla,'` ADD INDEX `',p_indice,'` (',p_columnas,')');
        PREPARE cs_stmt_indice FROM @cs_sql_indice;
        EXECUTE cs_stmt_indice;
        DEALLOCATE PREPARE cs_stmt_indice;
    END IF;
END$$

CALL agregar_indice_cuotas_salteadas(
    'pago','idx_pago_credito_tipo_monto','`cod_creditoFK`,`Tipo`,`Monto`'
)$$
CALL agregar_indice_cuotas_salteadas(
    'credito','idx_credito_venta_estado_plazo','`cod_venta`,`Esado`,`plazo`,`idcredito`'
)$$
CALL agregar_indice_cuotas_salteadas(
    'accesosuser','idx_accesosuser_permiso_usuario','`idlistadodeaccesoFK`,`tipo`,`usuarios_idusario`'
)$$

DROP PROCEDURE IF EXISTS agregar_indice_cuotas_salteadas$$

DELIMITER ;

SELECT la.codigo,dn.cod_nivelesfk,dn.accion
FROM listadodeacceso la
LEFT JOIN detallesniveles dn ON dn.idlistadodeacceso=la.idlistadodeacceso
WHERE la.codigo=@cs_codigo AND la.tipo='Administrativo'
ORDER BY dn.cod_nivelesfk;
