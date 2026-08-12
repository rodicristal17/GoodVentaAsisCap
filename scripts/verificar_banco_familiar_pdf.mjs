import fs from "node:fs";
import path from "node:path";
import { pathToFileURL } from "node:url";

const raiz = path.resolve(path.dirname(new URL(import.meta.url).pathname.replace(/^\/(.:)/, "$1")), "..");
const rutaPdf = process.argv[2];
if (!rutaPdf || !fs.existsSync(rutaPdf)) {
	throw new Error("Indica la ruta local de un extracto PDF de prueba");
}

globalThis.window = globalThis;
globalThis.DOMMatrix = class DOMMatrix {
	constructor() {
		this.a = 1; this.b = 0; this.c = 0; this.d = 1; this.e = 0; this.f = 0;
	}
};
globalThis.ImageData = class ImageData {};
globalThis.Path2D = class Path2D {};
if (typeof Uint8Array.prototype.toHex !== "function") {
	Uint8Array.prototype.toHex = function() {
		return Array.from(this, (byte) => byte.toString(16).padStart(2, "0")).join("");
	};
}
const adaptador = fs.readFileSync(path.join(raiz, "js_system", "conciliacionBancoFamiliarPdf.js"), "utf8");
globalThis.eval(adaptador);

const pdfjs = await import(pathToFileURL(path.join(raiz, "js_system", "vendor", "pdfjs", "pdf.min.mjs")).href);
pdfjs.GlobalWorkerOptions.workerSrc = pathToFileURL(path.join(raiz, "js_system", "vendor", "pdfjs", "pdf.worker.min.mjs")).href;
const documento = await pdfjs.getDocument({
	data: new Uint8Array(fs.readFileSync(rutaPdf)),
	disableFontFace: true,
	useSystemFonts: true,
	verbosity: 0
}).promise;
const paginas = [];
for (let numero = 1; numero <= documento.numPages; numero++) {
	const pagina = await documento.getPage(numero);
	const contenido = await pagina.getTextContent();
	paginas.push(contenido.items || []);
}

if (process.env.DEBUG_PDF_ESTRUCTURA === "SI") {
	console.log(paginas[0].filter((item) => Number(item.transform?.[5] || 0) > 680).map((item) => ({
		x: Math.round(Number(item.transform?.[4] || 0)),
		y: Math.round(Number(item.transform?.[5] || 0)),
		str: String(item.str || "").replace(/[0-9]{4,}/g, "####")
	})));
}

const resultado = globalThis.BancoFamiliarPdf.parsearItems(paginas);
const paginasAlteradas = paginas.map((items) => items.map((item) => ({
	...item,
	transform: Array.isArray(item.transform) ? item.transform.slice() : item.transform
})));
let totalesAlteradosRechazados = false;
for (const items of paginasAlteradas) {
	const etiquetaTotales = items.find((item) => /totales/i.test(String(item.str || "")));
	if (!etiquetaTotales) continue;
	const yTotales = Number(etiquetaTotales.transform?.[5] || 0);
	const creditoTotal = items.find((item) => {
		const x = Number(item.transform?.[4] || 0);
		const y = Number(item.transform?.[5] || 0);
		return Math.abs(y - yTotales) <= 2.2 && x >= 445 && x < 525 && /\d/.test(String(item.str || ""));
	});
	if (creditoTotal) {
		creditoTotal.str = String(creditoTotal.str || "") + "1";
		break;
	}
}
try {
	globalThis.BancoFamiliarPdf.parsearItems(paginasAlteradas);
} catch (error) {
	totalesAlteradosRechazados = true;
}
if (!totalesAlteradosRechazados) {
	throw new Error("El parser no rechazo un total de control alterado");
}
const cuenta = String(resultado.metadatos.cuenta || "");
console.log(JSON.stringify({
	paginas: resultado.paginas,
	movimientos: resultado.movimientos.length,
	cuenta_ultimos4: cuenta.slice(-4),
	moneda: resultado.metadatos.moneda,
	periodo_desde: resultado.metadatos.periodo_desde,
	periodo_hasta: resultado.metadatos.periodo_hasta,
	total_debitos: resultado.metadatos.total_debitos_calculado,
	total_creditos: resultado.metadatos.total_creditos_calculado,
	saldo_validado: resultado.metadatos.saldo_anterior + resultado.metadatos.total_creditos_calculado - resultado.metadatos.total_debitos_calculado === resultado.metadatos.saldo_final,
	totales_alterados_rechazados: totalesAlteradosRechazados
}, null, 2));
