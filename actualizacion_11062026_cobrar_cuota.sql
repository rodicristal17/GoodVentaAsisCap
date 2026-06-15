-- Modulo Cobrar cuota - etapa 1
-- Ejecutar despues de las etapas de Conciliacion Ueno.
-- Cambios aditivos: permisos, auditoria basica y catalogo de acceso rapido.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `cobrar_cuota_auditoria` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `accion` varchar(80) NOT NULL,
  `cod_creditoFK` int(11) DEFAULT NULL,
  `cod_venta` int(11) DEFAULT NULL,
  `cod_cliente` int(11) DEFAULT NULL,
  `cliente` varchar(180) DEFAULT NULL,
  `forma_pago` varchar(80) DEFAULT NULL,
  `monto` int(11) NOT NULL DEFAULT 0,
  `comprobante` varchar(120) DEFAULT NULL,
  `id_movimiento_ueno` int(11) DEFAULT NULL,
  `estado_pago` varchar(45) DEFAULT NULL,
  `estado_conciliacion` varchar(45) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL,
  `cod_local` int(11) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `datos` text DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_cobrar_cuota_audit_fecha` (`fecha_hora`),
  KEY `idx_cobrar_cuota_audit_credito` (`cod_creditoFK`),
  KEY `idx_cobrar_cuota_audit_venta` (`cod_venta`),
  KEY `idx_cobrar_cuota_audit_ueno` (`id_movimiento_ueno`),
  KEY `idx_cobrar_cuota_audit_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 88, 'FORMULARIO COBRAR CUOTA', 'VERCOBRARCUOTA', 'VER ICONO COBRAR CUOTA', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='VERCOBRARCUOTA' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 88, 'FORMULARIO COBRAR CUOTA', 'REGISTRARCOBRARCUOTA', 'REGISTRAR COBRO DE CUOTA', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='REGISTRARCOBRARCUOTA' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 88, 'FORMULARIO COBRAR CUOTA', 'CONCILIARCOBRARCUOTA', 'CONCILIAR COBRO CON UENO', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='CONCILIARCOBRARCUOTA' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 88, 'FORMULARIO COBRAR CUOTA', 'IMPRIMIRRECIBOCOBRARCUOTA', 'IMPRIMIR RECIBO COBRAR CUOTA', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='IMPRIMIRRECIBOCOBRARCUOTA' AND `tipo`='Administrativo');

INSERT INTO `listadodeacceso` (`nro`, `formulario`, `codigo`, `nombre`, `accion`, `orden`, `tipo`)
SELECT 88, 'FORMULARIO COBRAR CUOTA', 'ANULARCOBRARCUOTA', 'ANULAR COBRO DE CUOTA', 'NO', NULL, 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM `listadodeacceso` WHERE `codigo`='ANULARCOBRARCUOTA' AND `tipo`='Administrativo');

INSERT INTO `detallesniveles` (`cod_nivelesfk`, `idlistadodeacceso`, `accion`)
SELECT ln.cod_niveles, la.idlistadodeacceso,
  COALESCE((
    SELECT dn_old.accion
    FROM detallesniveles dn_old
    INNER JOIN listadodeacceso la_old ON la_old.idlistadodeacceso = dn_old.idlistadodeacceso
    WHERE dn_old.cod_nivelesfk = ln.cod_niveles
      AND la_old.codigo = CASE la.codigo
        WHEN 'VERCOBRARCUOTA' THEN 'VERPAGOSCREDITO'
        WHEN 'REGISTRARCOBRARCUOTA' THEN 'INSERTARPAGOSCREDITO'
        WHEN 'CONCILIARCOBRARCUOTA' THEN 'VERCONCILIACIONUENO'
        WHEN 'IMPRIMIRRECIBOCOBRARCUOTA' THEN 'VERPAGOSCREDITO'
        ELSE ''
      END
    LIMIT 1
  ), 'NO') AS accion
FROM listado_niveles ln
INNER JOIN listadodeacceso la ON la.codigo IN (
  'VERCOBRARCUOTA',
  'REGISTRARCOBRARCUOTA',
  'CONCILIARCOBRARCUOTA',
  'IMPRIMIRRECIBOCOBRARCUOTA',
  'ANULARCOBRARCUOTA'
)
WHERE ln.tipo = 'Administrativo'
  AND ln.estado = 'Activo'
  AND NOT EXISTS (
    SELECT 1
    FROM detallesniveles dn
    WHERE dn.cod_nivelesfk = ln.cod_niveles
      AND dn.idlistadodeacceso = la.idlistadodeacceso
  );

INSERT INTO `accesosuser` (`idlistadodeaccesoFK`, `tipo`, `usuarios_idusario`, `accion`)
SELECT la.idlistadodeacceso, 'Administrativo', u.cod_usuario,
  COALESCE((
    SELECT au_old.accion
    FROM accesosuser au_old
    INNER JOIN listadodeacceso la_old ON la_old.idlistadodeacceso = au_old.idlistadodeaccesoFK
    WHERE au_old.usuarios_idusario = u.cod_usuario
      AND au_old.tipo = 'Administrativo'
      AND la_old.codigo = CASE la.codigo
        WHEN 'VERCOBRARCUOTA' THEN 'VERPAGOSCREDITO'
        WHEN 'REGISTRARCOBRARCUOTA' THEN 'INSERTARPAGOSCREDITO'
        WHEN 'CONCILIARCOBRARCUOTA' THEN 'VERCONCILIACIONUENO'
        WHEN 'IMPRIMIRRECIBOCOBRARCUOTA' THEN 'VERPAGOSCREDITO'
        ELSE ''
      END
    LIMIT 1
  ), COALESCE(dn.accion, 'NO')) AS accion
FROM usuario u
INNER JOIN listadodeacceso la ON la.codigo IN (
  'VERCOBRARCUOTA',
  'REGISTRARCOBRARCUOTA',
  'CONCILIARCOBRARCUOTA',
  'IMPRIMIRRECIBOCOBRARCUOTA',
  'ANULARCOBRARCUOTA'
)
LEFT JOIN detallesniveles dn ON dn.idlistadodeacceso = la.idlistadodeacceso
  AND dn.cod_nivelesfk = u.Acceso
WHERE u.estado = 'Activo'
  AND NOT EXISTS (
    SELECT 1
    FROM accesosuser au
    WHERE au.idlistadodeaccesoFK = la.idlistadodeacceso
      AND au.tipo = 'Administrativo'
      AND au.usuarios_idusario = u.cod_usuario
  );

SET @cobrar_cuota_catalog_existe := (
  SELECT COUNT(*)
  FROM dashboard_access_catalog
  WHERE access_key = 'cobrar_cuota'
);

UPDATE dashboard_access_catalog
SET default_quick_order = default_quick_order + 1
WHERE @cobrar_cuota_catalog_existe = 0
  AND is_default_quick_access = 1
  AND default_quick_order >= 3;

INSERT INTO dashboard_access_catalog
  (access_key, label, module_key, module_label, icon_key, route_path, permission_key, is_active, is_default_quick_access, default_quick_order)
VALUES
  ('cobrar_cuota', 'Cobrar cuota', 'administrativo', 'Administrativo', 'cash-register', NULL, 'VERCOBRARCUOTA', 1, 1, 3)
ON DUPLICATE KEY UPDATE
  label = VALUES(label),
  module_key = VALUES(module_key),
  module_label = VALUES(module_label),
  icon_key = VALUES(icon_key),
  route_path = VALUES(route_path),
  permission_key = VALUES(permission_key),
  is_active = VALUES(is_active),
  is_default_quick_access = VALUES(is_default_quick_access),
  default_quick_order = VALUES(default_quick_order);
