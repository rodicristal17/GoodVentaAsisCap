SET NAMES utf8mb4;

INSERT INTO listadodeacceso (nro, formulario, codigo, nombre, accion, orden, tipo)
SELECT 88, 'Errores del sistema', 'VERERRORESSISTEMA', 'Ver errores del sistema', 'NO', '91', 'Administrativo'
WHERE NOT EXISTS (SELECT 1 FROM listadodeacceso WHERE codigo='VERERRORESSISTEMA');

INSERT INTO detallesniveles (cod_nivelesfk, idlistadodeacceso, accion)
SELECT n.cod_niveles, l.idlistadodeacceso, 'NO'
FROM listado_niveles n
CROSS JOIN listadodeacceso l
LEFT JOIN detallesniveles d ON d.cod_nivelesfk=n.cod_niveles AND d.idlistadodeacceso=l.idlistadodeacceso
WHERE l.codigo='VERERRORESSISTEMA' AND d.iddetallesniveles IS NULL;

INSERT INTO accesosuser (formulario, frmname, orden, idlistadodeaccesoFK, tipo, usuarios_idusario, accion)
SELECT l.formulario, l.codigo, CAST(l.orden AS DECIMAL(10,2)), l.idlistadodeacceso, 'Administrativo', u.cod_usuario, 'NO'
FROM usuario u
CROSS JOIN listadodeacceso l
LEFT JOIN accesosuser a ON a.idlistadodeaccesoFK=l.idlistadodeacceso AND a.usuarios_idusario=u.cod_usuario
WHERE l.codigo='VERERRORESSISTEMA' AND a.idaccesosUser IS NULL;
