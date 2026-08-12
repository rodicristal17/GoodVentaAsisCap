(function(global) {
	"use strict";

	var pdfjsPromise = null;
	var RUTA_PDFJS = "/GoodVentaAsisCap/js_system/vendor/pdfjs/pdf.min.mjs";
	var RUTA_WORKER = "/GoodVentaAsisCap/js_system/vendor/pdfjs/pdf.worker.min.mjs";

	function normalizarTexto(valor) {
		valor = String(valor || "");
		if (valor.normalize) {
			valor = valor.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
		}
		return valor.toUpperCase().replace(/\s+/g, " ").trim();
	}

	function cargarPdfJs() {
		if (!pdfjsPromise) {
			pdfjsPromise = import(RUTA_PDFJS).then(function(pdfjsLib) {
				pdfjsLib.GlobalWorkerOptions.workerSrc = RUTA_WORKER;
				return pdfjsLib;
			});
		}
		return pdfjsPromise;
	}

	function monto(valor) {
		var texto = String(valor || "").replace(/Gs\.?/ig, "").replace(/\s|\u00a0/g, "");
		var negativo = /^\(.*\)$/.test(texto) || texto.indexOf("-") >= 0;
		texto = texto.replace(/[().-]/g, "").replace(/\./g, "").replace(/,/g, ".");
		var numero = Math.round(Number(texto || 0));
		if (isNaN(numero)) {
			return 0;
		}
		return negativo ? -Math.abs(numero) : numero;
	}

	function esFecha(valor) {
		return /^\d{2}\/\d{2}\/\d{4}$/.test(String(valor || "").trim());
	}

	function agruparPorLinea(items, tolerancia) {
		var ordenados = items.slice().sort(function(a, b) {
			if (a.page != b.page) {
				return a.page - b.page;
			}
			if (Math.abs(a.y - b.y) > tolerancia) {
				return b.y - a.y;
			}
			return a.x - b.x;
		});
		var lineas = [];
		for (var i = 0; i < ordenados.length; i++) {
			var item = ordenados[i];
			var linea = null;
			for (var j = lineas.length - 1; j >= 0; j--) {
				if (lineas[j].page != item.page) {
					break;
				}
				if (Math.abs(lineas[j].y - item.y) <= tolerancia) {
					linea = lineas[j];
					break;
				}
				if (lineas[j].y - item.y > tolerancia) {
					break;
				}
			}
			if (!linea) {
				linea = { page: item.page, y: item.y, items: [] };
				lineas.push(linea);
			}
			linea.items.push(item);
			linea.items.sort(function(a, b) { return a.x - b.x; });
		}
		lineas.sort(function(a, b) {
			return a.page == b.page ? b.y - a.y : a.page - b.page;
		});
		return lineas;
	}

	function textoLinea(linea) {
		return linea.items.map(function(item) { return item.str; }).join(" ").replace(/\s+/g, " ").trim();
	}

	function textoColumna(linea, desde, hasta) {
		return linea.items.filter(function(item) {
			return item.x >= desde && (typeof hasta === "undefined" || item.x < hasta);
		}).map(function(item) { return item.str; }).join(" ").replace(/\s+/g, " ").trim();
	}

	function valorDebajo(lineas, indice, desde, hasta, distanciaMaxima) {
		var origen = lineas[indice];
		for (var i = indice + 1; i < lineas.length; i++) {
			var candidata = lineas[i];
			if (candidata.page != origen.page || origen.y - candidata.y > distanciaMaxima) {
				break;
			}
			var valor = textoColumna(candidata, desde, hasta);
			if (valor) {
				return valor;
			}
		}
		return "";
	}

	function extraerMetadatos(lineas) {
		var metadatos = {
			cuenta: "",
			tipo_cuenta: "",
			denominacion: "",
			moneda: "",
			periodo_desde: "",
			periodo_hasta: "",
			saldo_anterior: null,
			total_debitos_declarado: null,
			total_creditos_declarado: null
		};
		for (var i = 0; i < lineas.length; i++) {
			var texto = textoLinea(lineas[i]);
			var normal = normalizarTexto(texto);
			var match;
			if (normal.indexOf("CUENTA CORRIENTE") >= 0) {
				metadatos.tipo_cuenta = "CUENTA_CORRIENTE";
			}
			if (!metadatos.cuenta) {
				match = texto.match(/(?:CUENTA|NRO\.?\s*DE?\s*CUENTA|CUENTA\s*NRO\.?)\s*:?\s*([0-9][0-9.\-]*)/i);
				if (match) {
					metadatos.cuenta = match[1].replace(/[^0-9]/g, "");
				} else if (normal.indexOf("NRO. CUENTA") >= 0 || normal.indexOf("NRO CUENTA") >= 0) {
					metadatos.cuenta = valorDebajo(lineas, i, 300, 445, 16).replace(/[^0-9]/g, "");
				}
			}
			if (!metadatos.denominacion && normal.indexOf("DENOMINACION") >= 0) {
				metadatos.denominacion = texto.replace(/^.*?Denominaci[oó]n\s*:?/i, "").trim();
				if (!metadatos.denominacion) {
					metadatos.denominacion = valorDebajo(lineas, i, 0, 330, 16);
				}
			}
			if (normal.indexOf("MONEDA") >= 0) {
				var valorMoneda = normal.indexOf("GUARANI") >= 0 ? texto : valorDebajo(lineas, i, 445, 595, 16);
				var monedaNormal = normalizarTexto(valorMoneda);
				if (monedaNormal.indexOf("GUARANI") >= 0 || monedaNormal.indexOf("PYG") >= 0 || monedaNormal.indexOf("GS") >= 0) {
					metadatos.moneda = "PYG";
				} else {
					metadatos.moneda = valorMoneda.replace(/^.*?Moneda\s*:?/i, "").trim().toUpperCase();
				}
			}
			match = texto.match(/Desde\s*:?\s*(\d{2}\/\d{2}\/\d{4}).*Hasta\s*:?\s*(\d{2}\/\d{2}\/\d{4})/i);
			if (match) {
				metadatos.periodo_desde = match[1];
				metadatos.periodo_hasta = match[2];
			}
			if (normal.indexOf("SALDO ANTERIOR") >= 0) {
				metadatos.saldo_anterior = monto(textoColumna(lineas[i], 500));
			}
			if (normal.indexOf("TOTALES") >= 0) {
				metadatos.total_debitos_declarado = monto(textoColumna(lineas[i], 365, 445));
				metadatos.total_creditos_declarado = monto(textoColumna(lineas[i], 445, 525));
			}
		}
		return metadatos;
	}

	function validarEncabezado(lineas) {
		var partes = [];
		for (var i = 0; i < lineas.length; i++) {
			var normal = normalizarTexto(textoLinea(lineas[i]));
			if (normal.indexOf("FECHA") >= 0 || normal.indexOf("COMPROBANTE") >= 0 || normal.indexOf("TRANSACC") >= 0 || normal.indexOf("DEBITO") >= 0 || normal.indexOf("CREDITO") >= 0 || normal.indexOf("SALDO") >= 0) {
				partes.push(normal);
			}
		}
		var encabezados = partes.join(" ");
		if (encabezados.indexOf("COMPROBANTE") < 0 || encabezados.indexOf("TRANSACC") < 0 || encabezados.indexOf("DEBITO") < 0 || encabezados.indexOf("CREDITO") < 0 || encabezados.indexOf("SALDO") < 0) {
			throw new Error("El PDF no coincide con el formato esperado de extracto de Banco Familiar");
		}
	}

	function extraerMovimientos(lineas) {
		var movimientos = [];
		var ultimoMovimiento = null;
		var ultimaLineaMovimiento = null;
		for (var i = 0; i < lineas.length; i++) {
			var linea = lineas[i];
			var normal = normalizarTexto(textoLinea(linea));
			if (!normal || normal.indexOf("SALDO ANTERIOR") >= 0 || normal.indexOf("TOTALES") >= 0 || normal.indexOf("FECHA CONF") >= 0 || normal.indexOf("FECHA MOV") >= 0) {
				continue;
			}
			var fechaConfirmacion = textoColumna(linea, 0, 65);
			var fechaMovimiento = textoColumna(linea, 65, 125);
			if (esFecha(fechaConfirmacion) && esFecha(fechaMovimiento)) {
				var transaccion = textoColumna(linea, 190, 365);
				var saldoTexto = textoColumna(linea, 525);
				var movimiento = {
					fecha_confirmacion: fechaConfirmacion,
					fecha_transaccion: fechaMovimiento,
					nro_comprobante: textoColumna(linea, 125, 190).replace(/\s+/g, ""),
					descripcion: transaccion,
					concepto: "",
					importe_debito: monto(textoColumna(linea, 365, 445)),
					importe_credito: monto(textoColumna(linea, 445, 525)),
					saldo_banco: saldoTexto ? monto(saldoTexto) : null
				};
				if (movimiento.nro_comprobante || movimiento.importe_debito !== 0 || movimiento.importe_credito !== 0) {
					movimientos.push(movimiento);
					ultimoMovimiento = movimiento;
					ultimaLineaMovimiento = linea;
				}
				continue;
			}
			var continuacion = textoColumna(linea, 190, 365);
			if (ultimoMovimiento && continuacion && ultimaLineaMovimiento && linea.page == ultimaLineaMovimiento.page && Math.abs(ultimaLineaMovimiento.y - linea.y) <= 14) {
				ultimoMovimiento.descripcion = (ultimoMovimiento.descripcion + " " + continuacion).replace(/\s+/g, " ").trim();
			}
		}
		return movimientos;
	}

	function validarResultado(metadatos, movimientos) {
		if (!metadatos.cuenta) {
			throw new Error("No se pudo identificar la cuenta corriente en el PDF");
		}
		if (metadatos.tipo_cuenta != "CUENTA_CORRIENTE") {
			throw new Error("El PDF de Banco Familiar debe corresponder a una cuenta corriente");
		}
		if (normalizarTexto(metadatos.moneda).indexOf("PYG") < 0) {
			throw new Error("El extracto de Banco Familiar debe corresponder a una cuenta en guaranies");
		}
		if (!metadatos.periodo_desde || !metadatos.periodo_hasta) {
			throw new Error("No se pudo identificar el periodo del extracto de Banco Familiar");
		}
		if (metadatos.saldo_anterior === null || metadatos.total_debitos_declarado === null || metadatos.total_creditos_declarado === null) {
			throw new Error("No se pudieron identificar los totales de control del extracto de Banco Familiar");
		}
		if (!movimientos.length) {
			throw new Error("No se encontraron movimientos bancarios en el PDF");
		}
		var totalDebitos = 0;
		var totalCreditos = 0;
		for (var i = 0; i < movimientos.length; i++) {
			totalDebitos += Number(movimientos[i].importe_debito || 0);
			totalCreditos += Number(movimientos[i].importe_credito || 0);
		}
		if (totalDebitos !== metadatos.total_debitos_declarado) {
			throw new Error("El total de debitos leido no coincide con el total declarado por Banco Familiar");
		}
		if (totalCreditos !== metadatos.total_creditos_declarado) {
			throw new Error("El total de creditos leido no coincide con el total declarado por Banco Familiar");
		}
		var saldoFinal = movimientos[movimientos.length - 1].saldo_banco;
		if (saldoFinal === null || metadatos.saldo_anterior + totalCreditos - totalDebitos !== saldoFinal) {
			throw new Error("Los movimientos no reconstruyen el saldo final declarado por Banco Familiar");
		}
		metadatos.total_debitos_calculado = totalDebitos;
		metadatos.total_creditos_calculado = totalCreditos;
		metadatos.saldo_final = saldoFinal;
	}

	function parsearItems(paginas) {
		var items = [];
		for (var p = 0; p < paginas.length; p++) {
			for (var i = 0; i < paginas[p].length; i++) {
				var item = paginas[p][i];
				var texto = String(item.str || "").trim();
				if (!texto) {
					continue;
				}
				items.push({
					page: p + 1,
					x: Number(item.x != null ? item.x : (item.transform ? item.transform[4] : 0)),
					y: Number(item.y != null ? item.y : (item.transform ? item.transform[5] : 0)),
					str: texto
				});
			}
		}
		var lineas = agruparPorLinea(items, 2.2);
		validarEncabezado(lineas);
		var metadatos = extraerMetadatos(lineas);
		var movimientos = extraerMovimientos(lineas);
		validarResultado(metadatos, movimientos);
		return { metadatos: metadatos, movimientos: movimientos, paginas: paginas.length };
	}

	function leerPaginas(documento, numero, paginas) {
		if (numero > documento.numPages) {
			return Promise.resolve(paginas);
		}
		return documento.getPage(numero).then(function(pagina) {
			return pagina.getTextContent();
		}).then(function(contenido) {
			paginas.push(contenido.items || []);
			return leerPaginas(documento, numero + 1, paginas);
		});
	}

	function parsear(buffer) {
		return cargarPdfJs().then(function(pdfjsLib) {
			return pdfjsLib.getDocument({ data: new Uint8Array(buffer) }).promise;
		}).then(function(documento) {
			return leerPaginas(documento, 1, []);
		}).then(parsearItems);
	}

	global.BancoFamiliarPdf = {
		parsear: parsear,
		parsearItems: parsearItems,
		normalizarMonto: monto
	};
})(window);
