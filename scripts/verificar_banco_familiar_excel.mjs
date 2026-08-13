import fs from "node:fs";
import path from "node:path";
import { createRequire } from "node:module";
import { fileURLToPath } from "node:url";

const raiz = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const rutaExcel = process.argv[2];
if (!rutaExcel || !fs.existsSync(rutaExcel)) {
	throw new Error("Indica la ruta local de un extracto Excel de Banco Familiar");
}

const require = createRequire(import.meta.url);
const XLSX = require(path.join(raiz, "js_system", "excel.js"));
globalThis.window = globalThis;
globalThis.eval(fs.readFileSync(path.join(raiz, "js_system", "conciliacionBancoFamiliarExcel.js"), "utf8"));

const workbook = XLSX.readFile(rutaExcel, { raw: false, cellDates: false });
const hoja = workbook.Sheets[workbook.SheetNames[0]];
const filas = XLSX.utils.sheet_to_json(hoja, { header: 1, raw: false, defval: "" });
const resultado = globalThis.BancoFamiliarExcel.parsear(filas);

const filasAlteradas = filas.map((fila) => fila.slice());
const indiceCabecera = filasAlteradas.findIndex((fila) => fila.some((celda) => String(celda).trim() === "Fecha Confirmación"));
filasAlteradas[indiceCabecera + 1][6] = String(Number(filasAlteradas[indiceCabecera + 1][6]) + 1);
let saldoAlteradoRechazado = false;
try {
	globalThis.BancoFamiliarExcel.parsear(filasAlteradas);
} catch (error) {
	saldoAlteradoRechazado = true;
}
if (!saldoAlteradoRechazado) {
	throw new Error("El parser no rechazo un saldo secuencial alterado");
}

const cuenta = String(resultado.metadatos.cuenta || "");
console.log(JSON.stringify({
	formato_detectado: globalThis.BancoFamiliarExcel.esFormato(filas),
	movimientos: resultado.movimientos.length,
	cuenta_ultimos4: cuenta.slice(-4),
	moneda: resultado.metadatos.moneda,
	periodo_desde: resultado.metadatos.periodo_desde,
	periodo_hasta: resultado.metadatos.periodo_hasta,
	saldo_validado: resultado.metadatos.saldo_anterior + resultado.metadatos.total_creditos_calculado - resultado.metadatos.total_debitos_calculado === resultado.metadatos.saldo_final,
	saldo_alterado_rechazado: saldoAlteradoRechazado
}, null, 2));
