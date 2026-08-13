import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const raiz = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const selectorBanco = { value: "AUTO" };
globalThis.window = globalThis;
Object.defineProperty(globalThis, "crypto", { value: null, configurable: true });
globalThis.document = {
	addEventListener: function() {},
	getElementById: function(id) { return id === "inptConciliacionBanco" ? selectorBanco : null; },
	querySelectorAll: function() { return []; }
};

globalThis.eval(fs.readFileSync(path.join(raiz, "js_system", "sha256Local.js"), "utf8"));
globalThis.eval(fs.readFileSync(path.join(raiz, "js_system", "conciliacionBancoFamiliarExcel.js"), "utf8"));
globalThis.eval(fs.readFileSync(path.join(raiz, "js_system", "abmConciliacionUeno.js"), "utf8"));

const filasUeno = [
	["", "Cuenta:", "", "12345678"],
	["", "Denominacion:", "", "CUENTA DE PRUEBA", "", "", "Saldo actual disponible:", "100000"],
	["", "", "", "", "", "", "Periodo:", "01/08/2026 al 02/08/2026"],
	["Fecha de confirmacion", "Fecha de transaccion", "Nro de comprobante", "Descripcion", "Concepto", "Importe Debito", "Importe Credito", "Saldo"],
	["01/08/2026", "01/08/2026", "PRUEBA-1", "Credito de prueba", "Transferencia", "0", "150000", "150000"],
	["02/08/2026", "02/08/2026", "PRUEBA-2", "Debito de prueba", "Transferencia", "50000", "0", "100000"]
];

const bancoUeno = globalThis.uenoResolverBancoArchivo({ name: "extracto_ueno.xlsx" }, filasUeno);
const movimientosUeno = globalThis.uenoParsearMovimientos(filasUeno);
const periodoUeno = globalThis.uenoExtraerPeriodo(filasUeno, movimientosUeno);
if (bancoUeno !== "UENO" || movimientosUeno.length !== 2 || globalThis.uenoExtraerMeta(filasUeno, "cuenta") !== "12345678"
	|| globalThis.uenoExtraerMeta(filasUeno, "denominacion") !== "CUENTA DE PRUEBA"
	|| periodoUeno.desde !== "2026-08-01" || periodoUeno.hasta !== "2026-08-02") {
	throw new Error("La deteccion o el parser Ueno no conservaron el contrato anterior");
}

selectorBanco.value = "FAMILIAR";
let conflictoManualRechazado = false;
try {
	globalThis.uenoResolverBancoArchivo({ name: "extracto_ueno.xlsx" }, filasUeno);
} catch (error) {
	conflictoManualRechazado = true;
}
if (!conflictoManualRechazado) {
	throw new Error("El selector manual no rechazo un archivo de otro banco");
}

selectorBanco.value = "AUTO";
const bancoPdf = globalThis.uenoResolverBancoArchivo({ name: "extracto_familiar.pdf" }, null);
if (bancoPdf !== "FAMILIAR") {
	throw new Error("La deteccion automatica no asigno el PDF a Banco Familiar");
}

const bytes = new TextEncoder().encode("abc");
const huella = await globalThis.uenoCalcularHash(bytes.buffer);
if (huella !== "ba7816bf8f01cfea414140de5dae2223b00361a396177a9cb410ff61f20015ad") {
	throw new Error("El flujo principal no utilizo correctamente el SHA-256 local");
}

console.log(JSON.stringify({
	parser_ueno: "OK",
	deteccion_ueno: bancoUeno,
	metadatos_ueno: "OK",
	deteccion_pdf: bancoPdf,
	conflicto_manual_rechazado: conflictoManualRechazado,
	sha256_sin_webcrypto: "OK"
}, null, 2));
