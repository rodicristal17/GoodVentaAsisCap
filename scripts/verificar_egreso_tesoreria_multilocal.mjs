import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const raiz = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const rutaJs = path.join(raiz, "js_system", "abmGasto_ingresosEgresos.js");
const rutaJsInterConsulta = path.join(raiz, "js_system", "abmInterConsulta.js");
const rutaPhp = path.join(raiz, "php_system", "abmgasto.php");
const rutaPhpInterConsulta = path.join(raiz, "php_system", "abmInterConsulta.php");
const rutaHtml = path.join(raiz, "system", "inicio.html");
const fuenteJs = fs.readFileSync(rutaJs, "utf8");
const fuenteJsInterConsulta = fs.readFileSync(rutaJsInterConsulta, "utf8");
const fuentePhp = fs.readFileSync(rutaPhp, "utf8");
const fuentePhpInterConsulta = fs.readFileSync(rutaPhpInterConsulta, "utf8");
const fuenteHtml = fs.readFileSync(rutaHtml, "utf8");

function afirmar(condicion, mensaje) {
	if (!condicion) {
		throw new Error(mensaje);
	}
	console.log(`[OK] ${mensaje}`);
}

function extraerFuncion(fuente, nombre) {
	const inicio = fuente.indexOf(`function ${nombre}(`);
	if (inicio < 0) {
		throw new Error(`No se encontro la funcion ${nombre}.`);
	}
	const inicioBloque = fuente.indexOf("{", inicio);
	let nivel = 0;
	for (let indice = inicioBloque; indice < fuente.length; indice++) {
		if (fuente[indice] === "{") {
			nivel++;
		} else if (fuente[indice] === "}") {
			nivel--;
			if (nivel === 0) {
				return fuente.slice(inicio, indice + 1);
			}
		}
	}
	throw new Error(`La funcion ${nombre} esta incompleta.`);
}

const funciones = [
	"gastoTesoreriaEsResponsableOficial",
	"gastoDistribucionNumero",
	"gastoDistribucionTotalMovimiento",
	"gastoDistribucionLocalPago",
	"gastoUsuarioPuedeRegistrarTesoreriaMultilocal",
	"gastoDistribucionMinimoPersonalizado",
	"gastoAplicarImpactoTesoreriaDesdeLocalHilo",
	"gastoDistribucionEquitativa",
	"gastoDistribucionEscalarProporcional",
	"gastoDistribucionAsignacionesActuales",
	"validarDistribucionGasto",
	"actualizarDistribucionPorMontoGasto"
];

const elementos = {};
globalThis.document = {
	getElementById(id) {
		return Object.prototype.hasOwnProperty.call(elementos, id) ? elementos[id] : null;
	}
};
globalThis.gastoDistribucionLocalesGraficos = [3, 5, 6, 7, 9];
globalThis.gastoDistribucionEstado = { modo: "personalizado", valores: {}, cargando: false };
globalThis.normalizarTipoMovimientoFinanciero = valor => valor;
globalThis.ver_vetana_informativa = () => {};
globalThis.renderizarDistribucionLocalGasto = () => {};
globalThis.idAbmGasto = "";
globalThis.cod_localFKUSer = "6";
globalThis.gastoContextoTesoreria = { cargado: true, esResponsable: false };

let permisoTesoreria = true;
globalThis.permisoAccesoUser = codigo => codigo === "INSERTARLISTADOEGRESOINGRESO" || permisoTesoreria;

for (const nombre of funciones) {
	globalThis.eval(`${extraerFuncion(fuenteJs, nombre)}\n//# sourceURL=${nombre}.js`);
}

elementos.inptlocalMisGastos = { value: "1" };
elementos.inptMontoGasto = { value: "150000" };
elementos.inptTipoGasto = { value: "Egreso" };
elementos.chkDistribucionGastoLocal3 = { checked: true };
elementos.inptDistribucionGastoLocal3 = { value: "150000" };

afirmar(gastoDistribucionMinimoPersonalizado() === 1, "Tesoreria con caja de Administracion puede asignar una sucursal.");
afirmar(validarDistribucionGasto(false).ok === true, "Una asignacion unica y completa de Tesoreria es valida.");

gastoDistribucionEstado.valores = { 3: 1 };
actualizarDistribucionPorMontoGasto();
afirmar(gastoDistribucionEstado.valores[3] === 150000, "El monto unico se sincroniza con el total del egreso.");

permisoTesoreria = false;
afirmar(gastoDistribucionMinimoPersonalizado() === 2, "Un usuario sin permiso conserva el minimo de dos sucursales.");
afirmar(validarDistribucionGasto(false).ok === false, "Un usuario comun no obtiene la excepcion de Tesoreria.");

permisoTesoreria = true;
elementos.inptlocalMisGastos.value = "6";
afirmar(gastoDistribucionMinimoPersonalizado() === 2, "Tesoreria no obtiene la excepcion cuando declara una caja de sucursal.");

elementos.inptlocalMisGastos.value = "6";
gastoDistribucionEstado.modo = "local";
gastoDistribucionEstado.valores = {};
afirmar(
	gastoAplicarImpactoTesoreriaDesdeLocalHilo("6") === true
		&& elementos.inptlocalMisGastos.value === "1"
		&& gastoDistribucionEstado.modo === "personalizado"
		&& gastoDistribucionEstado.valores[6] === 150000
		&& !extraerFuncion(fuenteJs, "gastoAplicarImpactoTesoreriaDesdeLocalHilo").includes("cod_localFKUSer"),
	"Tesoreria puede pagar desde Administracion e imputar al Hilo aunque su cuenta pertenezca a otra sede."
);

permisoTesoreria = false;
gastoDistribucionEstado.modo = "local";
gastoDistribucionEstado.valores = {};
afirmar(
	gastoAplicarImpactoTesoreriaDesdeLocalHilo("5") === false
		&& gastoDistribucionEstado.modo === "local"
		&& Object.keys(gastoDistribucionEstado.valores).length === 0,
	"Un usuario sin permiso no recibe la imputacion automatica de Tesoreria."
);
permisoTesoreria = true;

afirmar(
	fuentePhp.includes("function gastoUsuarioPuedeRegistrarTesoreriaMultilocal")
		&& fuentePhp.includes("'VERCIERRESTESORERIA'")
		&& fuentePhp.includes("$puedeTesoreriaAdministracion")
		&& fuentePhp.includes("count($asignaciones) < $minimoPersonalizado"),
	"El servidor revalida el permiso y el minimo de asignaciones."
);
afirmar(
	(fuentePhp.match(/La caja abierta no pertenece al local de pago seleccionado/g) || []).length === 2,
	"Las dos protecciones de correspondencia entre caja y local permanecen activas."
);
afirmar(
	fuentePhp.includes("gastoBuscarCajaActivaDelCreador($mysqli, $registroBloqueado, true)"),
	"La aprobacion conserva la caja activa del creador en el local pagador."
);
afirmar(
	fuenteHtml.includes("id='avisoDistribucionTesoreriaGasto'")
		&& fuenteHtml.includes("id='ayudaDistribucionPersonalizadaGasto'")
		&& fuenteHtml.includes("tesoreria-responsable-20260820-1"),
	"La interfaz explica el modo Tesoreria y fuerza la carga de los recursos actualizados."
);
afirmar(
	fuenteJsInterConsulta.includes('localDetalleInterConsulta.setAttribute("data-cod-local"')
		&& (fuenteJsInterConsulta.match(/localId: contexto\.localId/g) || []).length === 2
		&& fuenteJsInterConsulta.includes("gastoAplicarImpactoTesoreriaDesdeLocalHilo(codLocalHiloGasto)"),
	"El local del hilo llega a los dos accesos de alta y a la seleccion manual del formulario."
);
afirmar(
	(fuentePhpInterConsulta.match(/id="td_datos_11"/g) || []).length >= 3
		&& fuentePhpInterConsulta.includes("SELECT ic.*"),
	"El backend de Hilos entrega el local tanto en las filas seleccionables como en el detalle."
);

console.log("RESULTADO: APROBADO");
