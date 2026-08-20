import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const raiz = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const leer = ruta => fs.readFileSync(path.join(raiz, ruta), "utf8");
const js = leer("js_system/abmGasto_ingresosEgresos.js");
const php = leer("php_system/abmgasto.php");
const helper = leer("php_system/gasto_tesoreria_helper.php");
const dashboard = leer("php_system/dashboard_flujo_financiero.php");
const html = leer("system/inicio.html");
const sql = leer("actualizacion_20082026_responsable_tesoreria_correcciones.sql");

function afirmar(condicion, mensaje) {
	if (!condicion) throw new Error(mensaje);
	console.log(`[OK] ${mensaje}`);
}

function extraerFuncion(fuente, nombre) {
	const inicio = fuente.indexOf(`function ${nombre}(`);
	if (inicio < 0) throw new Error(`No se encontro ${nombre}.`);
	const inicioBloque = fuente.indexOf("{", inicio);
	let nivel = 0;
	for (let i = inicioBloque; i < fuente.length; i++) {
		if (fuente[i] === "{") nivel++;
		if (fuente[i] === "}") {
			nivel--;
			if (nivel === 0) return fuente.slice(inicio, i + 1);
		}
	}
	throw new Error(`La funcion ${nombre} esta incompleta.`);
}

afirmar(
	["gasto_tesoreria_configuracion", "gasto_tesoreria_responsable_evento", "gasto_tesoreria_modificacion", "gasto_tesoreria_impacto"]
		.every(tabla => sql.includes(`CREATE TABLE IF NOT EXISTS \`${tabla}\``)),
	"La migracion crea configuracion unica, historial, modificaciones e impactos."
);
afirmar(
	sql.includes("CONFIGURARRESPONSABLETESORERIA")
		&& sql.includes("u.cod_usuario=5994")
		&& sql.includes("CARLOS FARAONE%")
		&& sql.includes("UPDATE `detallesniveles` d")
		&& sql.includes("UPDATE `accesosuser` a")
		&& helper.includes("$codUsuario !== 5994"),
	"El engranaje niega la configuracion por defecto y valida expresamente la identidad de Carlos Faraone."
);
afirmar(
	helper.includes("WHERE c.id_configuracion=1")
		&& helper.includes("ON DUPLICATE KEY UPDATE")
		&& helper.includes("gasto_tesoreria_responsable_evento"),
	"Solo puede existir una responsable oficial y cada reemplazo queda auditado."
);
afirmar(
	helper.includes("tipo_modificacion='edicion_pendiente'")
		&& helper.includes("IN ('pendiente','solicitado')")
		&& helper.includes("Solo pueden modificarse movimientos pendientes o corregirse movimientos pagados"),
	"La escritura directa esta restringida a cuotas pendientes o solicitadas."
);
afirmar(
	helper.includes("'correccion_pagada'")
		&& helper.includes("'reversion' => $anterior")
		&& helper.includes("'aplicacion' => $nuevo")
		&& helper.includes("No hay cambios financieros para registrar")
		&& php.includes("El movimiento ya esta pagado y no puede reescribirse"),
	"Los pagos usan correccion compensatoria, rechazan operaciones sin cambio y el ABM comun no puede reescribirlos."
);
afirmar(
	helper.includes("gastoTesoreriaCajaActivaUsuarioLocal($mysqli, $codUsuario, 1, true)")
		&& helper.includes("SET monto=?,fecha=?,cod_local=?,codCaja=?,codApertura=?")
		&& php.includes("gastoTesoreriaActorUltimaEdicionPendiente"),
	"La modalidad multilocal conserva Administracion como caja pagadora y puede aprobarse con la caja de Tesoreria."
);
afirmar(
	helper.includes("alcance_monto") && helper.includes("alcance_fecha")
		&& html.includes("name='alcanceMontoTesoreria'")
		&& html.includes("name='alcanceFechaTesoreria'")
		&& html.includes("id='vistaPreviaModificacionTesoreria'")
		&& helper.includes("gastoTesoreriaFirmaPlan")
		&& helper.includes("hash_equals")
		&& js.includes("firma_previa"),
	"Monto y fechas tienen alcances independientes y confirmacion con vista previa."
);
afirmar(
	html.includes("id='avisoEdicionAcotadaTesoreria'")
		&& (html.match(/data-tesoreria-fuera-alcance/g) || []).length >= 9
		&& js.includes("gastoTesoreriaEsEdicionAcotada")
		&& js.includes("gastoTesoreriaConfigurarEdicionAcotada")
		&& js.includes("edicionProtegidaTesoreria"),
	"La responsable ve una edicion acotada y los campos ajenos a monto, fecha y distribucion quedan bloqueados."
);
afirmar(
	html.includes("id='btnConfigurarResponsableTesoreria'")
		&& html.includes("Buscar por nombre, cargo o local")
		&& js.includes("obtener_contexto_tesoreria")
		&& js.includes("guardar_responsable_tesoreria"),
	"El engranaje permite buscar entre usuarios activos de Telar y guardar la seleccion."
);
afirmar(
	dashboard.includes("gasto_tesoreria_impacto")
		&& dashboard.includes("Correccion trazable de Tesoreria")
		&& php.includes("monto_impacto"),
	"Las correcciones impactan el resumen, el detalle y el presupuesto por sucursal."
);

globalThis.gastoDistribucionNumero = valor => Math.round(Number(valor) || 0);
globalThis.eval(`${extraerFuncion(js, "gastoDistribucionEscalarProporcional")}\n//# sourceURL=proporcional.js`);
const reparto = gastoDistribucionEscalarProporcional(333, { 3: 600, 5: 400 });
afirmar(
	reparto[3] + reparto[5] === 333 && reparto[3] === 200 && reparto[5] === 133,
	"Al cambiar el monto, la distribucion personalizada se recalcula proporcionalmente sin perder guaranies."
);

for (const fuente of [helper, php, dashboard]) {
	afirmar(!/\bfn\s*\(|\?->|\bmatch\s*\(/.test(fuente), "El PHP modificado no usa sintaxis posterior a PHP 7.2.");
}

console.log("RESULTADO: APROBADO");
