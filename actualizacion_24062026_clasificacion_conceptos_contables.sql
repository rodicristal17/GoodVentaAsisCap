-- Clasificacion de conceptos contables actuales para el flujo financiero.
-- ingreso   = Ingresos
-- directo   = Costos Variables
-- operativo = Gastos Fijos

UPDATE motivos_ingreso_egreso
SET categoria = 'ingreso'
WHERE cod_motivo_ingreso_egreso IN (67, 146);

UPDATE motivos_ingreso_egreso
SET categoria = 'directo'
WHERE cod_motivo_ingreso_egreso IN (
	7, 28, 33, 38, 93, 109, 118, 122, 125, 128, 132, 134, 140, 141, 142, 143, 148
);

UPDATE motivos_ingreso_egreso
SET categoria = 'operativo'
WHERE cod_motivo_ingreso_egreso IN (
	8, 11, 46, 115, 116, 124, 126, 127, 130, 144, 145, 147
);
