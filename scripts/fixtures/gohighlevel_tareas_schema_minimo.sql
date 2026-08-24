-- Esquema minimo y datos ficticios para probar la migracion de tareas.
CREATE TABLE gohighlevel_permiso_usuario (
  cod_usuarioFK INT NOT NULL PRIMARY KEY,
  puede_ver TINYINT(1) NOT NULL DEFAULT 0,
  puede_responder TINYINT(1) NOT NULL DEFAULT 0,
  puede_enviar_plantilla TINYINT(1) NOT NULL DEFAULT 0,
  puede_configurar TINYINT(1) NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  cod_usuario_actualizaFK INT DEFAULT NULL,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL
);

INSERT INTO gohighlevel_permiso_usuario
  (cod_usuarioFK,puede_ver,puede_responder,puede_enviar_plantilla,puede_configurar,activo,
   cod_usuario_actualizaFK,fecha_creacion,fecha_actualizacion)
VALUES (5994,1,1,1,1,1,5994,NOW(),NOW());
