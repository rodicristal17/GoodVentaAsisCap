-- Clinident Salud / Sistema Telar
-- Mi cartera: separacion de obligaciones heredadas de una administracion anterior.
-- Los pacientes con solo deuda heredada salen de la operacion diaria; los casos
-- mixtos conservan ambas cuentas y se presentan con una advertencia.
-- Compatible con MySQL 5.6, MariaDB 10.6 y PHP 7.2.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS cartera_fuente_heredada (
  id_fuente BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_localFK INT NOT NULL,
  etiqueta VARCHAR(120) NOT NULL,
  mensaje VARCHAR(255) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  cod_usuario_actualizaFK INT DEFAULT NULL,
  fecha_creacion DATETIME NOT NULL,
  fecha_actualizacion DATETIME NOT NULL,
  PRIMARY KEY (id_fuente),
  UNIQUE KEY uq_cartera_fuente_heredada_local (cod_localFK),
  KEY idx_cartera_fuente_heredada_activo (activo,cod_localFK)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cartera_fuente_heredada
  (cod_localFK,etiqueta,mensaje,activo,cod_usuario_actualizaFK,
   fecha_creacion,fecha_actualizacion)
SELECT l.cod_local,
  'Administracion anterior',
  'Incluye deuda correspondiente a la administracion anterior.',
  1,NULL,NOW(),NOW()
FROM local l
WHERE l.cod_local=1
  AND UPPER(TRIM(l.Nombre))='CLINIDENT (ADMINISTRACION) COMPARTIDOS'
ON DUPLICATE KEY UPDATE
  etiqueta=VALUES(etiqueta),mensaje=VALUES(mensaje),activo=1,
  fecha_actualizacion=NOW();

-- Se conserva la asignacion y todo su historial. Solo se cambia a inactiva
-- cuando existe saldo heredado pendiente y no existe una obligacion operativa
-- pendiente dentro de la ventana de Mi cartera. La tabla temporal hace que la
-- migracion sea repetible sin volver a tocar asignaciones reactivadas luego.
DROP TEMPORARY TABLE IF EXISTS tmp_cartera_archivar_heredada;
CREATE TEMPORARY TABLE tmp_cartera_archivar_heredada (
  id_asignacion BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (id_asignacion)
) ENGINE=InnoDB;

INSERT INTO tmp_cartera_archivar_heredada (id_asignacion)
SELECT ca.id_asignacion
FROM cartera_asignacion ca
WHERE ca.estado='activa'
  AND EXISTS (
    SELECT 1
    FROM venta vh
    INNER JOIN cartera_fuente_heredada fh
      ON fh.cod_localFK=vh.cod_local AND fh.activo=1
    INNER JOIN credito ch ON ch.cod_venta=vh.cod_venta
    LEFT JOIN (
      SELECT cod_creditoFK,
        SUM(CASE WHEN Tipo='Pago Cuota'
          AND LOWER(TRIM(IFNULL(anulado,''))) NOT IN ('si','anulado','activo')
          THEN Monto ELSE 0 END) pago_cuota,
        SUM(CASE WHEN Tipo='Interes'
          AND LOWER(TRIM(IFNULL(anulado,''))) NOT IN ('si','anulado','activo')
          THEN Monto ELSE 0 END) pago_interes
      FROM pago GROUP BY cod_creditoFK
    ) ph ON ph.cod_creditoFK=ch.idcredito
    WHERE vh.cod_clienteFK=ca.cod_clienteFK
      AND IFNULL(vh.anulado,'')=''
      AND IFNULL(vh.estadocuenta,'Activo')<>'Anulado'
      AND ch.fechapago<=DATE_ADD(CURDATE(),INTERVAL 7 DAY)
      AND (
        GREATEST(0,(IFNULL(ch.Monto,0)-IFNULL(ch.descuento,0))-IFNULL(ph.pago_cuota,0))
        +GREATEST(0,(IFNULL(ch.totalinteres,0)+IFNULL(ch.deudaInteres,0))-IFNULL(ph.pago_interes,0))
      )>0
  )
  AND NOT EXISTS (
    SELECT 1
    FROM venta vo
    LEFT JOIN cartera_fuente_heredada fo
      ON fo.cod_localFK=vo.cod_local AND fo.activo=1
    INNER JOIN credito co ON co.cod_venta=vo.cod_venta
    LEFT JOIN (
      SELECT cod_creditoFK,
        SUM(CASE WHEN Tipo='Pago Cuota'
          AND LOWER(TRIM(IFNULL(anulado,''))) NOT IN ('si','anulado','activo')
          THEN Monto ELSE 0 END) pago_cuota,
        SUM(CASE WHEN Tipo='Interes'
          AND LOWER(TRIM(IFNULL(anulado,''))) NOT IN ('si','anulado','activo')
          THEN Monto ELSE 0 END) pago_interes
      FROM pago GROUP BY cod_creditoFK
    ) po ON po.cod_creditoFK=co.idcredito
    WHERE vo.cod_clienteFK=ca.cod_clienteFK
      AND fo.id_fuente IS NULL
      AND IFNULL(vo.anulado,'')=''
      AND IFNULL(vo.estadocuenta,'Activo')<>'Anulado'
      AND co.fechapago<=DATE_ADD(CURDATE(),INTERVAL 7 DAY)
      AND (
        GREATEST(0,(IFNULL(co.Monto,0)-IFNULL(co.descuento,0))-IFNULL(po.pago_cuota,0))
        +GREATEST(0,(IFNULL(co.totalinteres,0)+IFNULL(co.deudaInteres,0))-IFNULL(po.pago_interes,0))
      )>0
  );

INSERT INTO cartera_evento
  (cod_clienteFK,id_asignacionFK,cod_usuario_actorFK,tipo_evento,detalle,
   datos_anteriores,datos_nuevos,fecha_evento)
SELECT ca.cod_clienteFK,ca.id_asignacion,
  IFNULL(ca.cod_usuario_asignaFK,0),
  'fuente_heredada_archivada',
  'Asignacion archivada: el paciente posee exclusivamente deuda de la administracion anterior.',
  CONCAT('{"estado":"activa","responsable":',IFNULL(ca.cod_usuario_responsableFK,0),'}'),
  '{"estado":"inactiva","motivo":"fuente_heredada_archivada"}',
  NOW()
FROM cartera_asignacion ca
INNER JOIN tmp_cartera_archivar_heredada tmp
  ON tmp.id_asignacion=ca.id_asignacion;

UPDATE cartera_asignacion ca
INNER JOIN tmp_cartera_archivar_heredada tmp
  ON tmp.id_asignacion=ca.id_asignacion
SET ca.estado='inactiva',
    ca.motivo_asignacion='fuente_heredada_archivada',
    ca.fecha_actualizacion=NOW()
WHERE ca.estado='activa';

DROP TEMPORARY TABLE IF EXISTS tmp_cartera_archivar_heredada;

SELECT cod_localFK,etiqueta,mensaje,activo
FROM cartera_fuente_heredada
ORDER BY cod_localFK;

SELECT COUNT(*) AS asignaciones_heredadas_archivadas
FROM cartera_evento
WHERE tipo_evento='fuente_heredada_archivada';
