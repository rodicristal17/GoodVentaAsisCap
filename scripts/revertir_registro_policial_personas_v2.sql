-- Reversion estructural. Usar solo si persona_antes_migracion sigue presente.
-- La tabla migrada se conserva como persona_migracion_revertida para inspeccion.
RENAME TABLE
  registro_policial.persona TO registro_policial.persona_migracion_revertida,
  registro_policial.persona_antes_migracion TO registro_policial.persona;

