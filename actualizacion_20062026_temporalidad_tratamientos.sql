ALTER TABLE producto
  ADD COLUMN IF NOT EXISTS usa_temporalidad TINYINT(1) NOT NULL DEFAULT 0 AFTER nivel_riesgo_actualizado_en,
  ADD COLUMN IF NOT EXISTS temporalidad_tipo VARCHAR(40) NULL AFTER usa_temporalidad,
  ADD COLUMN IF NOT EXISTS temporalidad_intervalo_recomendado INT NULL AFTER temporalidad_tipo,
  ADD COLUMN IF NOT EXISTS temporalidad_intervalo_minimo INT NULL AFTER temporalidad_intervalo_recomendado,
  ADD COLUMN IF NOT EXISTS temporalidad_intervalo_maximo INT NULL AFTER temporalidad_intervalo_minimo,
  ADD COLUMN IF NOT EXISTS temporalidad_sesiones_estimadas INT NULL AFTER temporalidad_intervalo_maximo,
  ADD COLUMN IF NOT EXISTS temporalidad_duracion_sillon INT NULL AFTER temporalidad_sesiones_estimadas,
  ADD COLUMN IF NOT EXISTS temporalidad_observacion VARCHAR(255) NULL AFTER temporalidad_duracion_sillon;
