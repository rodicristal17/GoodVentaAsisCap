-- Reversion de GoHighLevel fase 4.
-- No borra los archivos descargados en /var/lib/telar/gohighlevel_adjuntos.
-- Esa eliminacion debe autorizarse y ejecutarse por separado.

SET NAMES utf8mb4;

DROP TABLE IF EXISTS gohighlevel_ia_operacion;
DROP TABLE IF EXISTS gohighlevel_ia_config;
DROP TABLE IF EXISTS gohighlevel_adjunto_cache;
