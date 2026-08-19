-- Reversion conservadora de actualizacion_18082026_activos_fijos_control.sql.
-- No elimina columnas ni tablas porque contienen trazabilidad financiera y fisica.
-- Para revertir la interfaz, restaure los archivos respaldados y conserve este esquema aditivo.

SELECT
    'NO_DESTRUCTIVO' AS estado,
    'El rollback conserva sectores, verificaciones, depreciaciones y columnas de activos.' AS detalle,
    (SELECT COUNT(*) FROM inventario_local_sector) AS sectores_conservados,
    (SELECT COUNT(*) FROM inventario_local_verificacion) AS verificaciones_conservadas,
    (SELECT COUNT(*) FROM inventario_local_depreciacion_historial) AS depreciaciones_conservadas;
