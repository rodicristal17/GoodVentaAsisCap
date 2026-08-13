import crypto from "node:crypto";
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const raiz = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
globalThis.window = globalThis;
globalThis.eval(fs.readFileSync(path.join(raiz, "js_system", "sha256Local.js"), "utf8"));

function verificar(buffer, etiqueta) {
	const vista = new Uint8Array(buffer.buffer, buffer.byteOffset, buffer.byteLength);
	const esperado = crypto.createHash("sha256").update(buffer).digest("hex");
	const obtenido = globalThis.TelarSha256.calcular(vista);
	if (obtenido !== esperado) {
		throw new Error("La huella SHA-256 local no coincide para " + etiqueta);
	}
}

verificar(Buffer.alloc(0), "archivo vacio");
verificar(Buffer.from("abc", "utf8"), "vector conocido");
verificar(crypto.randomBytes(4097), "contenido binario");

const rutaArchivo = process.argv[2];
if (rutaArchivo) {
	verificar(fs.readFileSync(rutaArchivo), "archivo indicado");
}

console.log(JSON.stringify({ sha256_local: "OK", archivo_adicional: rutaArchivo ? "validado" : "no indicado" }, null, 2));
