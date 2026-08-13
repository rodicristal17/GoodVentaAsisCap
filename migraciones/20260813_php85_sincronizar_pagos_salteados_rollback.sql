-- Ejecutar solo antes de usar el modulo; elimina las tablas creadas por la migracion.
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS regularizacion_pago_salteado_reversion;
DROP TABLE IF EXISTS regularizacion_pago_salteado_detalle;
DROP TABLE IF EXISTS regularizacion_pago_salteado;
DROP TABLE IF EXISTS historial_pago_salteado_entrega;
SET FOREIGN_KEY_CHECKS=1;
