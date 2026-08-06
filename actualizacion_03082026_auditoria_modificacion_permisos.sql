-- Auditoria de modificaciones de permisos. Compatible con MySQL 5.6 / PHP 7.2.
-- No registra INSERT ni DELETE por decision funcional.

CREATE TABLE IF NOT EXISTS accesosuser_auditoria (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  idaccesosUser INT NULL,
  idlistadodeaccesoFK INT NULL,
  usuarios_idusario INT NOT NULL,
  tipo VARCHAR(60) NULL,
  codigo_permiso VARCHAR(160) NULL,
  nombre_permiso VARCHAR(255) NULL,
  accion_anterior VARCHAR(20) NULL,
  accion_nueva VARCHAR(20) NULL,
  fecha_hora DATETIME NOT NULL,
  cod_usuario_actor INT NULL,
  origen VARCHAR(255) NULL,
  ip VARCHAR(45) NULL,
  navegador VARCHAR(500) NULL,
  usuario_bd VARCHAR(160) NULL,
  conexion_id BIGINT UNSIGNED NULL,
  grupo_cambio VARCHAR(64) NULL,
  PRIMARY KEY (id),
  KEY idx_accesos_audit_afectado_fecha (usuarios_idusario, fecha_hora),
  KEY idx_accesos_audit_actor_fecha (cod_usuario_actor, fecha_hora),
  KEY idx_accesos_audit_permiso_fecha (idlistadodeaccesoFK, fecha_hora),
  KEY idx_accesos_audit_grupo (grupo_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TRIGGER IF EXISTS trg_accesosuser_auditoria_update;
DELIMITER $$
CREATE TRIGGER trg_accesosuser_auditoria_update
AFTER UPDATE ON accesosuser
FOR EACH ROW
BEGIN
  IF NOT (OLD.accion <=> NEW.accion) THEN
    INSERT INTO accesosuser_auditoria (
      idaccesosUser, idlistadodeaccesoFK, usuarios_idusario, tipo,
      codigo_permiso, nombre_permiso, accion_anterior, accion_nueva,
      fecha_hora, cod_usuario_actor, origen, ip, navegador,
      usuario_bd, conexion_id, grupo_cambio
    )
    SELECT NEW.idaccesosUser, NEW.idlistadodeaccesoFK, NEW.usuarios_idusario, NEW.tipo,
      lta.codigo, lta.nombre, OLD.accion, NEW.accion,
      NOW(), NULLIF(@gv_audit_actor,0), NULLIF(@gv_audit_origen,''),
      NULLIF(@gv_audit_ip,''), NULLIF(@gv_audit_navegador,''),
      CONCAT(CURRENT_USER(),' / ',USER()), CONNECTION_ID(), NULLIF(@gv_audit_grupo,'')
    FROM listadodeacceso lta
    WHERE lta.idlistadodeacceso=NEW.idlistadodeaccesoFK
    LIMIT 1;
  END IF;
END$$
DELIMITER ;
