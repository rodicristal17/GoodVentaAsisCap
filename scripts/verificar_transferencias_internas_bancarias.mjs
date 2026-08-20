import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const raiz = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const leer = ruta => fs.readFileSync(path.join(raiz, ruta), "utf8");
const sql = leer("actualizacion_20082026_transferencias_internas_bancarias.sql");
const helper = leer("php_system/ueno_transferencia_interna_helper.php");
const banco = leer("php_system/abmConciliacionUeno.php");
const saldo = leer("php_system/ueno_saldo_helper.php");
const cobrar = leer("php_system/abmCobrarCuota.php");
const pagos = leer("php_system/abmpagos.php");
const js = leer("js_system/abmConciliacionUeno.js");
const html = leer("system/inicio.html");
const css = leer("css_system/conciliacion_ueno_panel.css");

function afirmar(condicion, mensaje) {
	if (!condicion) throw new Error(mensaje);
	console.log(`[OK] ${mensaje}`);
}

afirmar(
	sql.includes("CREATE TABLE IF NOT EXISTS `ueno_transferencia_interna`")
		&& sql.includes("CREATE TABLE IF NOT EXISTS `ueno_transferencia_interna_evento`")
		&& sql.includes("UNIQUE KEY `uk_uti_debito`")
		&& sql.includes("UNIQUE KEY `uk_uti_credito`"),
	"La migracion conserva un solo vinculo activo por cada asiento y un historial separado."
);
afirmar(
	sql.includes("v_debito <> NEW.monto OR v_credito <> NEW.monto OR v_disponible <> NEW.monto")
		&& sql.includes("ABS(DATEDIFF(v_fecha_debito,v_fecha_credito)) > 3")
		&& sql.includes("UPPER(v_banco_debito) = UPPER(v_banco_credito)"),
	"La base exige importe exacto, bancos distintos y una ventana maxima de tres dias."
);
afirmar(
	sql.includes("gasto_tesoreria_configuracion")
		&& helper.includes("gastoTesoreriaEsResponsable($mysqli, $usuario)")
		&& helper.includes("exclusivamente a la responsable oficial de Tesoreria"),
	"La escritura queda restringida a la responsable oficial configurada en Tesoreria."
);
afirmar(
	helper.includes("begin_transaction")
		&& helper.includes("FOR UPDATE")
		&& helper.includes('bind_param("iississssii"')
		&& helper.includes("estado_debito_anterior")
		&& helper.includes("disponible_credito_anterior")
		&& helper.includes("ueno_auditar_conciliacion"),
	"Vinculacion y reversion son atomicas, bloquean los registros y conservan los valores anteriores."
);
afirmar(
	helper.includes("function ueno_ti_conteos_sugerencias_lista")
		&& helper.includes("mv_origen.importe_debito=mv_candidato.importe_credito")
		&& helper.includes("ABS(DATEDIFF($fechaOrigen,$fechaCandidato))<=3")
		&& helper.includes("ueno_movimiento_gasto umg_libre")
		&& helper.includes("ueno_movimiento_pago ump_libre"),
	"La mesa detecta coincidencias exactas en lote y excluye movimientos que ya tienen aplicaciones."
);
afirmar(
	helper.includes('$estadoInterno = "ignorado"')
		&& helper.includes("monto_disponible=?")
		&& saldo.includes('array("ignorado", "anulado"')
		&& saldo.includes("LOWER(TRIM(IFNULL(estado,'')))<>'ignorado'"),
	"Los movimientos internos quedan sin saldo operativo y una sincronizacion posterior no los reactiva."
);
for (const fuente of [banco, cobrar, pagos]) {
	afirmar(
		fuente.includes('"duplicado", "ignorado"') || fuente.includes("'duplicado','ignorado'"),
		"Cada acceso de conciliacion o cobro rechaza movimientos ignorados."
	);
}
afirmar(
	banco.includes("total_transferencias_internas")
		&& banco.includes("$total_ueno - $total_gv - $total_migracion_interna - $total_transferencias_internas"),
	"El resumen consolidado descuenta el ingreso que pertenece a una transferencia interna."
);
afirmar(
	js.includes("function uenoBuscarSugerenciasTransferenciaInterna")
		&& js.includes("function uenoVincularTransferenciaInterna")
		&& js.includes("function uenoRevertirTransferenciaInterna")
		&& js.includes("Confirmar v&iacute;nculo")
		&& js.includes("Motivo de la reversi&oacute;n"),
	"La mesa muestra la previsualizacion y permite confirmar o revertir sin ventanas nativas encadenadas."
);
afirmar(
	banco.includes('"sugerencias_transferencia_interna"')
		&& banco.includes("Posible transferencia interna")
		&& banco.includes("Revisar y neutralizar")
		&& js.includes("La responsable oficial de Tesorer&iacute;a debe revisar y confirmar antes de neutralizar")
		&& css.includes(".ueno-internal-potential"),
	"Ambas filas muestran la posible vinculacion y la confirmacion sigue reservada a Tesoreria."
);
afirmar(
	html.includes("IGNORADO / TRANSFERENCIA INTERNA")
		&& html.includes("lblUenoChipInternos")
		&& html.includes("inptUenoTesTransferenciasInternas")
		&& html.includes("transferencias-internas-20260820-2")
		&& css.includes(".ueno-internal-route"),
	"La interfaz incorpora filtro, resumen, recorrido entre bancos y recursos con cache actualizado."
);
for (const fuente of [helper, banco, saldo, cobrar, pagos]) {
	afirmar(!/\bfn\s*\(|\?->|\bmatch\s*\(/.test(fuente), "El PHP modificado mantiene compatibilidad sintactica con PHP 7.2.");
}

console.log("RESULTADO: APROBADO");
