(function(global) {
	"use strict";

	function normalizarTexto(valor) {
		valor = String(valor == null ? "" : valor);
		if (valor.normalize) {
			valor = valor.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
		}
		return valor.toUpperCase().replace(/\s+/g, " ").trim();
	}

	function monto(valor) {
		var texto = String(valor == null ? "" : valor).trim();
		if (!texto) {
			return 0;
		}
		var negativo = /^\(.*\)$/.test(texto) || texto.indexOf("-") >= 0;
		texto = texto.replace(/Gs\.?|PYG|\u20b2/gi, "").replace(/[\s\u00a0]/g, "").replace(/[^\d.,]/g, "");
		var coma = texto.lastIndexOf(",");
		var punto = texto.lastIndexOf(".");
		if (coma >= 0 && punto >= 0) {
			texto = coma > punto ? texto.replace(/\./g, "").replace(",", ".") : texto.replace(/,/g, "");
		} else if (coma >= 0) {
			var partesComa = texto.split(",");
			texto = partesComa[partesComa.length - 1].length == 3 ? texto.replace(/,/g, "") : texto.replace(",", ".");
		} else if (punto >= 0) {
			var partesPunto = texto.split(".");
			if (partesPunto[partesPunto.length - 1].length == 3 || partesPunto.length > 2) {
				texto = texto.replace(/\./g, "");
			}
		}
		var numero = Math.round(parseFloat(texto || "0"));
		if (isNaN(numero)) {
			return 0;
		}
		return negativo ? -Math.abs(numero) : numero;
	}

	function normalizarFecha(valor) {
		var texto = String(valor == null ? "" : valor).trim();
		var match = texto.match(/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/);
		if (!match) {
			return "";
		}
		var dia = ("0" + match[1]).slice(-2);
		var mes = ("0" + match[2]).slice(-2);
		var fecha = new Date(Number(match[3]), Number(mes) - 1, Number(dia));
		if (fecha.getFullYear() != Number(match[3]) || fecha.getMonth() != Number(mes) - 1 || fecha.getDate() != Number(dia)) {
			return "";
		}
		return dia + "/" + mes + "/" + match[3];
	}

	function valorEtiqueta(filas, etiqueta) {
		var buscada = normalizarTexto(etiqueta);
		for (var i = 0; i < Math.min(filas.length, 15); i++) {
			var fila = filas[i] || [];
			for (var c = 0; c < fila.length; c++) {
				var celda = normalizarTexto(fila[c]).replace(/:$/, "");
				if (celda == buscada) {
					for (var siguiente = c + 1; siguiente < fila.length; siguiente++) {
						if (String(fila[siguiente] == null ? "" : fila[siguiente]).trim() != "") {
							return fila[siguiente];
						}
					}
				}
			}
		}
		return "";
	}

	function resolverColumnas(filas) {
		for (var i = 0; i < filas.length; i++) {
			var fila = filas[i] || [];
			var columnas = {};
			for (var c = 0; c < fila.length; c++) {
				var encabezado = normalizarTexto(fila[c]);
				if (encabezado == "FECHA CONFIRMACION") columnas.fecha_confirmacion = c;
				if (encabezado == "FECHA MOVIMIENTO") columnas.fecha_transaccion = c;
				if (encabezado == "COMPROBANTE") columnas.nro_comprobante = c;
				if (encabezado == "TRANSACCION") columnas.descripcion = c;
				if (encabezado == "DEBITO") columnas.importe_debito = c;
				if (encabezado == "CREDITO") columnas.importe_credito = c;
				if (encabezado == "SALDO") columnas.saldo_banco = c;
			}
			if (typeof columnas.fecha_confirmacion !== "undefined" && typeof columnas.fecha_transaccion !== "undefined"
				&& typeof columnas.nro_comprobante !== "undefined" && typeof columnas.descripcion !== "undefined"
				&& typeof columnas.importe_debito !== "undefined" && typeof columnas.importe_credito !== "undefined"
				&& typeof columnas.saldo_banco !== "undefined") {
				columnas.indice = i;
				return columnas;
			}
		}
		return null;
	}

	function esFormato(filas) {
		var marcaBanco = false;
		for (var i = 0; i < Math.min(filas.length, 15); i++) {
			if (normalizarTexto((filas[i] || []).join(" ")).indexOf("BANCO FAMILIAR") >= 0) {
				marcaBanco = true;
				break;
			}
		}
		return marcaBanco && !!resolverColumnas(filas);
	}

	function parsear(filas) {
		if (!esFormato(filas)) {
			throw new Error("El Excel no coincide con el formato esperado de Banco Familiar");
		}
		var columnas = resolverColumnas(filas);
		var cuenta = String(valorEtiqueta(filas, "NRO. CUENTA") || "").replace(/[^0-9]/g, "");
		var denominacion = String(valorEtiqueta(filas, "DENOMINACION") || "").trim();
		var monedaTexto = normalizarTexto(valorEtiqueta(filas, "MONEDA"));
		var periodoDesde = normalizarFecha(valorEtiqueta(filas, "FECHA DESDE"));
		var periodoHasta = normalizarFecha(valorEtiqueta(filas, "FECHA HASTA"));
		var saldoAnterior = monto(valorEtiqueta(filas, "SALDO ANTERIOR"));
		if (!cuenta) {
			throw new Error("No se pudo identificar la cuenta en el Excel de Banco Familiar");
		}
		if (monedaTexto.indexOf("GUARANI") < 0 && monedaTexto.indexOf("PYG") < 0) {
			throw new Error("El Excel de Banco Familiar debe corresponder a una cuenta en guaranies");
		}
		if (!periodoDesde || !periodoHasta) {
			throw new Error("No se pudo identificar el periodo del Excel de Banco Familiar");
		}

		var movimientos = [];
		var totalDebitos = 0;
		var totalCreditos = 0;
		var saldoEsperado = saldoAnterior;
		for (var i = columnas.indice + 1; i < filas.length; i++) {
			var fila = filas[i] || [];
			var fechaConfirmacion = normalizarFecha(fila[columnas.fecha_confirmacion]);
			var fechaMovimiento = normalizarFecha(fila[columnas.fecha_transaccion]);
			var comprobante = String(fila[columnas.nro_comprobante] == null ? "" : fila[columnas.nro_comprobante]).trim();
			var debito = monto(fila[columnas.importe_debito]);
			var credito = monto(fila[columnas.importe_credito]);
			var tieneContenido = (fila || []).join("").trim() != "";
			if (!tieneContenido) {
				continue;
			}
			if (!fechaConfirmacion && !fechaMovimiento && !comprobante && debito == 0 && credito == 0) {
				continue;
			}
			if (!fechaConfirmacion || !fechaMovimiento || !comprobante) {
				throw new Error("Hay un movimiento de Banco Familiar con fecha o comprobante incompleto");
			}
			if ((debito > 0 && credito > 0) || (debito <= 0 && credito <= 0)) {
				throw new Error("Cada movimiento debe tener solamente un debito o un credito mayor a cero");
			}
			var saldoBanco = monto(fila[columnas.saldo_banco]);
			saldoEsperado += credito - debito;
			if (saldoBanco !== saldoEsperado) {
				throw new Error("El saldo de un movimiento no coincide con el historial del Excel de Banco Familiar");
			}
			movimientos.push({
				fecha_confirmacion: fechaConfirmacion,
				fecha_transaccion: fechaMovimiento,
				nro_comprobante: comprobante,
				descripcion: String(fila[columnas.descripcion] == null ? "" : fila[columnas.descripcion]).trim(),
				concepto: "",
				importe_debito: debito,
				importe_credito: credito,
				saldo_banco: saldoBanco
			});
			totalDebitos += debito;
			totalCreditos += credito;
		}
		if (!movimientos.length) {
			throw new Error("No se encontraron movimientos en el Excel de Banco Familiar");
		}

		return {
			metadatos: {
				cuenta: cuenta,
				denominacion: denominacion,
				moneda: "PYG",
				tipo_cuenta: "CUENTA_CORRIENTE",
				periodo_desde: periodoDesde,
				periodo_hasta: periodoHasta,
				saldo_anterior: saldoAnterior,
				saldo_final: movimientos[movimientos.length - 1].saldo_banco,
				total_debitos_declarado: totalDebitos,
				total_creditos_declarado: totalCreditos,
				total_debitos_calculado: totalDebitos,
				total_creditos_calculado: totalCreditos,
				saldo_actual_informativo: monto(valorEtiqueta(filas, "SALDO ACTUAL"))
			},
			movimientos: movimientos
		};
	}

	global.BancoFamiliarExcel = { esFormato: esFormato, parsear: parsear };
})(typeof window !== "undefined" ? window : globalThis);
