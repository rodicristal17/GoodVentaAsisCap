-- Clinident Salud - Hilos por colaborador y confirmaciones de lectura
-- Fecha: 2026-07-17
-- Migracion aditiva. No elimina ni modifica mensajes, menciones o hilos.
-- Compatible con la aplicacion PHP 7.2.

CREATE TABLE IF NOT EXISTS interconsulta_lectura_usuario (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_interConsultaFK INT NOT NULL,
  cod_usuarioFK INT NOT NULL,
  fecha_inicio_conteo DATETIME NOT NULL,
  fecha_ultima_apertura DATETIME DEFAULT NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (id),
  UNIQUE KEY uq_interconsulta_lectura_usuario (cod_interConsultaFK,cod_usuarioFK),
  KEY idx_interconsulta_lectura_usuario_pendientes (cod_usuarioFK,estado,cod_interConsultaFK),
  KEY idx_interconsulta_lectura_usuario_apertura (fecha_ultima_apertura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS interconsulta_mensaje_lectura (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_mensajeFK INT NOT NULL,
  cod_interConsultaFK INT NOT NULL,
  cod_usuarioFK INT NOT NULL,
  fecha_lectura DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_interconsulta_mensaje_lector (cod_mensajeFK,cod_usuarioFK),
  KEY idx_interconsulta_mensaje_lectura_hilo (cod_interConsultaFK,cod_usuarioFK,fecha_lectura),
  KEY idx_interconsulta_mensaje_lectura_usuario (cod_usuarioFK,fecha_lectura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- El historial existente comienza en cero. Solo los mensajes disponibles a
-- partir de esta instalacion podran incrementar el contador de no leidos.
INSERT IGNORE INTO interconsulta_lectura_usuario
  (cod_interConsultaFK,cod_usuarioFK,fecha_inicio_conteo,fecha_ultima_apertura,estado)
SELECT ic.cod_interConsulta,ic.cod_usuarioFK_create,NOW(),NOW(),'activo'
FROM interconsulta ic
INNER JOIN usuario u ON u.cod_usuario=ic.cod_usuarioFK_create
WHERE ic.estado<>'inactivo' AND IFNULL(ic.cod_usuarioFK_create,0)>0;

INSERT IGNORE INTO interconsulta_lectura_usuario
  (cod_interConsultaFK,cod_usuarioFK,fecha_inicio_conteo,fecha_ultima_apertura,estado)
SELECT ic.cod_interConsulta,mn.cod_usuarioFK,NOW(),NOW(),'activo'
FROM interconsulta ic
INNER JOIN mensaje ultimo ON ultimo.cod_mensaje=(
  SELECT m2.cod_mensaje
  FROM mensaje m2
  WHERE m2.cod_interConsultaFK=ic.cod_interConsulta
    AND m2.estado='activo' AND m2.fecha_creacion<=NOW()
  ORDER BY m2.fecha_creacion DESC,m2.cod_mensaje DESC
  LIMIT 1
)
INNER JOIN menciones mn ON mn.cod_mensajeFK=ultimo.cod_mensaje AND mn.estado='activo'
INNER JOIN usuario u ON u.cod_usuario=mn.cod_usuarioFK
WHERE ic.estado<>'inactivo' AND IFNULL(mn.cod_usuarioFK,0)>0;

