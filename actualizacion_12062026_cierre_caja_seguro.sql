-- Cierre de caja seguro
-- Ejecutar con respaldo previo. Cambios aditivos para evidencias, firma, resumen y auditoria.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `caja_cierres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_arqueocaja` int(11) NOT NULL,
  `id_lote` varchar(80) NOT NULL,
  `id_usuario_cajera` int(11) DEFAULT NULL,
  `id_local` int(11) DEFAULT NULL,
  `fecha_inicio_lote` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `efectivo_esperado` int(11) NOT NULL DEFAULT 0,
  `efectivo_contado` int(11) NOT NULL DEFAULT 0,
  `diferencia_efectivo` int(11) NOT NULL DEFAULT 0,
  `total_transferencias` int(11) NOT NULL DEFAULT 0,
  `total_transferencias_conciliadas` int(11) NOT NULL DEFAULT 0,
  `total_tarjetas` int(11) NOT NULL DEFAULT 0,
  `total_billeteras` int(11) NOT NULL DEFAULT 0,
  `total_otros` int(11) NOT NULL DEFAULT 0,
  `estado_cierre` varchar(60) NOT NULL DEFAULT 'Caja cuadrada',
  `estado_revision` varchar(60) NOT NULL DEFAULT 'Cerrada',
  `motivo_diferencia` varchar(120) DEFAULT NULL,
  `observacion_diferencia` text DEFAULT NULL,
  `foto_adjunta` varchar(2) NOT NULL DEFAULT 'NO',
  `firma_adjunta` varchar(2) NOT NULL DEFAULT 'NO',
  `ruta_foto` varchar(255) DEFAULT NULL,
  `ruta_firma` varchar(255) DEFAULT NULL,
  `cerrado_por` int(11) DEFAULT NULL,
  `cerrado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_caja_cierres_arqueo` (`id_arqueocaja`),
  KEY `idx_caja_cierres_lote` (`id_lote`),
  KEY `idx_caja_cierres_usuario` (`id_usuario_cajera`),
  KEY `idx_caja_cierres_local` (`id_local`),
  KEY `idx_caja_cierres_estado` (`estado_cierre`,`estado_revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `caja_cierre_denominaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cierre` int(11) NOT NULL,
  `denominacion` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `subtotal` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_caja_cierre_denominacion` (`id_cierre`,`denominacion`),
  KEY `idx_caja_cierre_denominacion_cierre` (`id_cierre`),
  CONSTRAINT `fk_caja_cierre_denominaciones_cierre` FOREIGN KEY (`id_cierre`) REFERENCES `caja_cierres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `caja_cierre_evidencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cierre` int(11) NOT NULL,
  `tipo_evidencia` varchar(60) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `nombre_archivo` varchar(190) DEFAULT NULL,
  `mime_type` varchar(80) DEFAULT NULL,
  `size` int(11) NOT NULL DEFAULT 0,
  `usuario_carga` int(11) DEFAULT NULL,
  `fecha_carga` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_caja_cierre_evidencia_cierre` (`id_cierre`),
  KEY `idx_caja_cierre_evidencia_tipo` (`tipo_evidencia`),
  CONSTRAINT `fk_caja_cierre_evidencias_cierre` FOREIGN KEY (`id_cierre`) REFERENCES `caja_cierres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `caja_cierre_firmas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cierre` int(11) NOT NULL,
  `ruta_firma` varchar(255) NOT NULL,
  `usuario_firmante` int(11) DEFAULT NULL,
  `nombre_firmante` varchar(180) DEFAULT NULL,
  `fecha_firma` datetime NOT NULL DEFAULT current_timestamp(),
  `texto_confirmacion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_caja_cierre_firma_cierre` (`id_cierre`),
  KEY `idx_caja_cierre_firma_usuario` (`usuario_firmante`),
  CONSTRAINT `fk_caja_cierre_firmas_cierre` FOREIGN KEY (`id_cierre`) REFERENCES `caja_cierres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `caja_cierre_auditoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cierre` int(11) DEFAULT NULL,
  `id_lote` varchar(80) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL,
  `accion` varchar(80) NOT NULL,
  `detalle` text DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_caja_cierre_audit_cierre` (`id_cierre`),
  KEY `idx_caja_cierre_audit_lote` (`id_lote`),
  KEY `idx_caja_cierre_audit_usuario` (`usuario`),
  KEY `idx_caja_cierre_audit_accion` (`accion`),
  KEY `idx_caja_cierre_audit_fecha` (`fecha_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
