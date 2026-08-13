var uenoMovimientosPreview = [];
var uenoHashArchivo = "";
var uenoIdImportacionSeleccionada = "";
var uenoIdConciliacionManual = "";
var uenoMovimientoTrabajo = null;
var uenoCuotaGoodVentaSeleccionada = null;
var uenoFiltroRapidoMovimientos = "todos";
var uenoMesaSoloConsulta = true;
var uenoPreviewValidando = false;
var uenoAuditoriaMovimientoActual = "";
var uenoImportacionesModalAbierto = false;
var uenoDetalleImportacionActual = "";
var uenoMesaTrabajoModalAbierta = false;
var uenoBancoSeleccionado = "AUTO";
var uenoBancoDetectado = "";
var uenoFormatoArchivo = "";
var uenoPreviewListaParaImportar = false;
var uenoLecturaArchivoVersion = 0;
var uenoFiltroBancoSeleccionado = "TODOS";
var uenoFiltroBancoVersion = 0;
var uenoMetadatosExtracto = {
	moneda: "PYG",
	tipo_cuenta: "",
	saldo_anterior: null,
	saldo_final: null,
	total_creditos_declarado: null,
	total_debitos_declarado: null
};

function uenoModoBancoActual() {
	var selector = document.getElementById("inptConciliacionBanco");
	var banco = selector ? String(selector.value || "AUTO").toUpperCase() : uenoBancoSeleccionado;
	if (["AUTO", "UENO", "FAMILIAR"].indexOf(banco) < 0) {
		banco = "AUTO";
	}
	uenoBancoSeleccionado = banco;
	return banco;
}

function uenoBancoActual() {
	var modo = uenoModoBancoActual();
	if (modo == "UENO" || modo == "FAMILIAR") {
		return modo;
	}
	return uenoBancoDetectado == "FAMILIAR" ? "FAMILIAR" : "UENO";
}

function uenoBancoNombre(codigo) {
	var banco = String(codigo || (uenoBancoDetectado || uenoModoBancoActual())).toUpperCase();
	if (banco == "FAMILIAR") {
		return "Banco Familiar";
	}
	return banco == "AUTO" ? "banco detectado" : "Ueno";
}

function uenoAgregarBanco(datos) {
	datos.append("banco_codigo", uenoBancoActual());
	return datos;
}

function uenoFiltroBancoActual() {
	var banco = String(uenoFiltroBancoSeleccionado || "TODOS").toUpperCase();
	if (["TODOS", "UENO", "FAMILIAR"].indexOf(banco) < 0) {
		banco = "TODOS";
	}
	uenoFiltroBancoSeleccionado = banco;
	return banco;
}

function uenoFiltroBancoNombre(codigo) {
	var banco = String(codigo || uenoFiltroBancoActual()).toUpperCase();
	if (banco == "FAMILIAR") {
		return "Banco Familiar";
	}
	return banco == "UENO" ? "Ueno" : "Todos los bancos";
}

function uenoAgregarFiltroBanco(datos) {
	datos.append("banco_codigo", uenoFiltroBancoActual());
	return datos;
}

function uenoActualizarBotonesFiltroBanco() {
	var banco = uenoFiltroBancoActual();
	var botones = document.querySelectorAll("#divConciliacionUeno [data-ueno-bank-filter]");
	for (var i = 0; i < botones.length; i++) {
		var activo = String(botones[i].getAttribute("data-ueno-bank-filter") || "").toUpperCase() == banco;
		botones[i].classList.toggle("ueno-bank-filter-button--active", activo);
		botones[i].setAttribute("aria-pressed", activo ? "true" : "false");
	}
}

function uenoFechaExtractoIso(valor) {
	var texto = String(valor || "").trim();
	var matchIso = texto.match(/\b(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})\b/);
	if (matchIso) {
		return matchIso[1] + "-" + ("0" + matchIso[2]).slice(-2) + "-" + ("0" + matchIso[3]).slice(-2);
	}
	var matchLocal = texto.match(/\b(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})\b/);
	if (matchLocal) {
		return matchLocal[3] + "-" + ("0" + matchLocal[2]).slice(-2) + "-" + ("0" + matchLocal[1]).slice(-2);
	}
	return "";
}

function uenoFechaFamiliarIso(valor) {
	return uenoFechaExtractoIso(valor);
}

function uenoActualizarContextoBanco() {
	var modo = uenoModoBancoActual();
	var banco = modo == "AUTO" ? uenoBancoDetectado : modo;
	var familiar = banco == "FAMILIAR";
	var inputArchivo = document.getElementById("uenoArchivoExtracto");
	if (inputArchivo) {
		if (modo == "AUTO") {
			inputArchivo.accept = ".pdf,.xls,.xlsx,.csv,application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
		} else {
			inputArchivo.accept = familiar
				? ".pdf,.xls,.xlsx,application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
				: ".xls,.xlsx,.csv";
		}
	}
	var cuenta = document.getElementById("inptUenoCuenta");
	if (cuenta) {
		cuenta.readOnly = familiar;
		cuenta.title = familiar ? "La cuenta se obtiene del extracto de Banco Familiar" : "Cuenta informada en el extracto bancario";
	}
	if (modo == "AUTO") {
		uenoSetTexto("lblUenoFormatoArchivo", "Selecciona un extracto: Telar detecta Banco Familiar (PDF o Excel) y Ueno (Excel o CSV), valida su estructura y muestra una vista previa antes de habilitar la importacion.");
	} else if (familiar) {
		uenoSetTexto("lblUenoFormatoArchivo", "Carga el extracto de Banco Familiar en PDF o Excel. Se validan cuenta corriente en guaranies, periodo, movimientos y saldos; el archivo original no se almacena.");
	} else {
		uenoSetTexto("lblUenoFormatoArchivo", "Carga el archivo Ueno en formato Excel o CSV. Los totales de credito/debito ayudan a confirmar que el archivo fue leido correctamente.");
	}
}

function uenoCambiarBanco() {
	uenoBancoSeleccionado = uenoModoBancoActual();
	uenoLimpiarPreviewExtracto();
	uenoActualizarContextoBanco();
	return true;
}

function uenoActualizarContextoFiltroBanco() {
	var banco = uenoFiltroBancoActual();
	var nombre = uenoFiltroBancoNombre(banco);
	var todos = banco == "TODOS";
	uenoActualizarBotonesFiltroBanco();
	uenoSetTexto("lblUenoBancoTesoreria", todos ? "Fecha bancaria" : "Fecha banco " + nombre);
	uenoSetTexto("lblUenoTesoreriaHelp", todos
		? "Compara el total acreditado por ambos bancos contra las transferencias registradas en Telar para la fecha seleccionada."
		: "Compara el total acreditado por " + nombre + " contra las transferencias registradas en Telar para la fecha seleccionada.");
	uenoSetTexto("lblUenoTotalBanco", todos ? "Total bancos" : "Total " + nombre + " banco");
	uenoSetTexto("lblUenoSaldoBanco", todos ? "Saldo bancario disponible" : "Saldo " + nombre + " disponible");
	uenoSetTexto("lblUenoMesaCompactTitulo", todos ? "5. Movimientos bancarios - mesa de trabajo" : "5. Movimientos " + nombre + " - mesa de trabajo");
	uenoSetTexto("lblUenoMesaPopupTitulo", "Mesa de trabajo - " + nombre);
	uenoSetTexto("lblUenoMesaTitulo", todos ? "5. Movimientos bancarios - mesa de trabajo" : "5. Movimientos " + nombre + " - mesa de trabajo");
	uenoSetTexto("lblUenoAuditPopupTitulo", todos ? "Aplicacion del movimiento bancario" : "Aplicacion del movimiento - " + nombre);
}

function uenoCambiarFiltroBanco(codigo) {
	var banco = String(codigo || "TODOS").toUpperCase();
	if (["TODOS", "UENO", "FAMILIAR"].indexOf(banco) < 0) {
		banco = "TODOS";
	}
	if (uenoFiltroBancoSeleccionado == banco) {
		uenoActualizarContextoFiltroBanco();
		return;
	}
	uenoFiltroBancoSeleccionado = banco;
	uenoFiltroBancoVersion++;
	uenoIdImportacionSeleccionada = "";
	uenoDetalleImportacionActual = "";
	uenoCerrarDetalleImportacionPopup();
	uenoCerrarDetalleMesaTrabajo();
	uenoLimpiarMovimientoTrabajo(false);
	uenoActualizarContextoFiltroBanco();
	uenoBuscarImportaciones();
	uenoBuscarMovimientos();
	uenoBuscarResumenTesoreria();
	uenoBuscarPagosPendientes();
	uenoBuscarAuditoria();
}

function uenoAvisarMesaSoloConsulta() {
	ver_vetana_informativa("No se puede procesar pagos desde la mesa de trabajo. Utiliza el modulo de caja/cobros.", "", "error");
	return true;
}

function uenoTienePermiso(codigo) {
	if (typeof userid !== "undefined" && String(userid) == "2") {
		return true;
	}
	if (typeof permisoAccesoUser === "function") {
		return permisoAccesoUser(codigo, "accion") != false;
	}
	return !!(typeof accesosuser !== "undefined" && accesosuser && accesosuser[codigo] && accesosuser[codigo]["accion"] == "SI");
}

function verCerrarConciliacionUeno(d) {
	if (d == "1") {
		if (!uenoTienePermiso("VERCONCILIACIONUENO")) {
			ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUAR", "", "error");
			return;
		}
		uenoFiltroBancoSeleccionado = "TODOS";
		uenoFiltroBancoVersion++;
		document.getElementById("divConciliacionUeno").style.display = "";
		uenoActualizarContextoBanco();
		uenoActualizarContextoFiltroBanco();
		uenoPrepararColapsables(true);
		uenoPrepararFechasTesoreria();
		uenoLimpiarMovimientoTrabajo(true);
		uenoLimpiarAsignacionManual();
		uenoBuscarImportaciones();
		uenoBuscarMovimientos();
		uenoBuscarPagosPendientes();
		uenoBuscarResumenTesoreria();
		uenoBuscarAuditoria();
	} else {
		uenoCerrarDetalleImportacionPopup();
		uenoCerrarModalImportaciones();
		uenoCerrarMesaTrabajoPopup();
		uenoCerrarAuditoriaMovimientoPopup();
		document.getElementById("divConciliacionUeno").style.display = "none";
	}
}

function uenoSeleccionarArchivo() {
	if (!uenoTienePermiso("IMPORTAREXTRACTOUENO")) {
		ver_vetana_informativa("NO TIENES PERMISO PARA IMPORTAR EXTRACTOS", "", "error");
		return;
	}
	var input = document.getElementById("uenoArchivoExtracto");
	if (input) {
		input.value = "";
		input.click();
	}
}

function uenoLimpiarPreviewExtracto() {
	uenoLecturaArchivoVersion++;
	uenoMovimientosPreview = [];
	uenoHashArchivo = "";
	uenoBancoDetectado = "";
	uenoFormatoArchivo = "";
	uenoPreviewListaParaImportar = false;
	uenoPreviewValidando = false;
	uenoMetadatosExtracto = {
		moneda: "PYG",
		tipo_cuenta: "",
		saldo_anterior: null,
		saldo_final: null,
		total_creditos_declarado: null,
		total_debitos_declarado: null
	};
	var campos = [
		"inptUenoFechaExtracto",
		"inptUenoPeriodoDesde",
		"inptUenoPeriodoHasta",
		"inptUenoCuenta",
		"inptUenoDenominacion",
		"inptUenoArchivo",
		"inptUenoHash",
		"inptUenoCantidadLeida",
		"inptUenoTotalCreditoPreview",
		"inptUenoTotalDebitoPreview"
	];
	for (var i = 0; i < campos.length; i++) {
		var campo = document.getElementById(campos[i]);
		if (campo) {
			campo.value = "";
		}
	}
	var preview = document.getElementById("table_ueno_preview");
	if (preview) {
		preview.innerHTML = "";
	}
	var resumenImportacion = document.getElementById("divUenoResumenImportacion");
	if (resumenImportacion) {
		resumenImportacion.innerHTML = "";
	}
	uenoHabilitarBotonImportar(false);
	uenoMostrarEstadoArchivo("pendiente", "Selecciona un extracto para comenzar.");
}

function uenoHabilitarBotonImportar(habilitar) {
	var boton = document.getElementById("btnUenoImportarExtracto");
	if (!boton) {
		return;
	}
	boton.disabled = !habilitar;
	boton.setAttribute("aria-disabled", habilitar ? "false" : "true");
}

function uenoMostrarEstadoArchivo(estado, mensaje) {
	var contenedor = document.getElementById("divUenoEstadoArchivo");
	if (!contenedor) {
		return;
	}
	var clase = "ueno-file-status ueno-file-status--" + (estado || "pendiente");
	var titulo = estado == "listo" ? "Listo para importar" : (estado == "error" ? "Necesita revision" : (estado == "validando" ? "Validando extracto" : "Preparacion del extracto"));
	var detalles = "";
	if (uenoBancoDetectado) {
		detalles += "<span><b>Banco:</b> " + uenoEscapeHtml(uenoBancoNombre(uenoBancoDetectado)) + "</span>";
	}
	if (uenoFormatoArchivo) {
		detalles += "<span><b>Formato:</b> " + uenoEscapeHtml(uenoFormatoArchivo) + "</span>";
	}
	var cuenta = document.getElementById("inptUenoCuenta");
	if (cuenta && cuenta.value) {
		detalles += "<span><b>Cuenta:</b> " + uenoEscapeHtml(cuenta.value) + "</span>";
	}
	if (uenoMovimientosPreview.length) {
		detalles += "<span><b>Movimientos:</b> " + uenoEscapeHtml(String(uenoMovimientosPreview.length)) + "</span>";
	}
	var desde = document.getElementById("inptUenoPeriodoDesde");
	var hasta = document.getElementById("inptUenoPeriodoHasta");
	if (desde && hasta && desde.value && hasta.value) {
		detalles += "<span><b>Periodo:</b> " + uenoEscapeHtml(desde.value) + " al " + uenoEscapeHtml(hasta.value) + "</span>";
	}
	contenedor.className = clase;
	contenedor.innerHTML = "<b>" + titulo + "</b>" + detalles + (mensaje ? "<span>" + uenoEscapeHtml(mensaje) + "</span>" : "");
}

function uenoNormalizarTexto(valor) {
	valor = String(valor || "");
	if (valor.normalize) {
		valor = valor.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
	}
	return valor.toUpperCase().replace(/\s+/g, " ").trim();
}

function uenoEscapeHtml(valor) {
	return String(valor || "").replace(/[&<>"']/g, function(char) {
		return {
			"&": "&amp;",
			"<": "&lt;",
			">": "&gt;",
			'"': "&quot;",
			"'": "&#039;"
		}[char];
	});
}

function uenoActualizarIndicadorColapsable(tarjeta) {
	var indicador = tarjeta.querySelector(".ueno-collapse-indicator");
	if (!indicador) {
		return;
	}
	var cerrado = tarjeta.classList.contains("ueno-collapsed");
	indicador.innerHTML = "<span class='ueno-collapse-symbol'>" + (cerrado ? "+" : "-") + "</span>"
		+ "<span class='ueno-collapse-text'>" + (cerrado ? "Mostrar" : "Ocultar") + "</span>";
	indicador.setAttribute("aria-label", cerrado ? "Mostrar bloque" : "Ocultar bloque");
}

function uenoPrepararColapsables(resetear) {
	var tarjetas = document.querySelectorAll("#divConciliacionUeno .ueno-collapsible-card");
	for (var i = 0; i < tarjetas.length; i++) {
		var tarjeta = tarjetas[i];
		var encabezado = tarjeta.querySelector(".ueno-section-head");
		if (!encabezado) {
			continue;
		}
		if (resetear) {
			tarjeta.classList.add("ueno-collapsed");
		}
		if (!tarjeta.getAttribute("data-ueno-collapse-ready")) {
			var indicador = document.createElement("span");
			indicador.className = "ueno-collapse-indicator";
			encabezado.appendChild(indicador);
			encabezado.setAttribute("role", "button");
			encabezado.setAttribute("tabindex", "0");
			encabezado.addEventListener("click", function(event) {
				var tag = event.target && event.target.tagName ? event.target.tagName.toLowerCase() : "";
				if (tag == "input" || tag == "select" || tag == "textarea" || tag == "a") {
					return;
				}
				var card = this.closest(".ueno-collapsible-card");
				if (card) {
					card.classList.toggle("ueno-collapsed");
					uenoActualizarIndicadorColapsable(card);
				}
			});
			encabezado.addEventListener("keydown", function(event) {
				if (event.key != "Enter" && event.key != " ") {
					return;
				}
				event.preventDefault();
				var card = this.closest(".ueno-collapsible-card");
				if (card) {
					card.classList.toggle("ueno-collapsed");
					uenoActualizarIndicadorColapsable(card);
				}
			});
			tarjeta.setAttribute("data-ueno-collapse-ready", "1");
		}
		uenoActualizarIndicadorColapsable(tarjeta);
	}
}

function uenoNormalizarMontoExtracto(valor) {
	var texto = String(valor == null ? "" : valor).trim();
	if (texto == "") {
		return "0";
	}
	var negativo = /^\(.*\)$/.test(texto) || texto.indexOf("-") >= 0;
	texto = texto.replace(/Gs\.?|PYG|\u20b2/gi, "");
	texto = texto.replace(/[\s\u00a0]/g, "");
	texto = texto.replace(/[^\d.,]/g, "");
	if (texto == "") {
		return "0";
	}

	var coma = texto.lastIndexOf(",");
	var punto = texto.lastIndexOf(".");
	if (coma >= 0 && punto >= 0) {
		if (coma > punto) {
			texto = texto.replace(/\./g, "").replace(",", ".");
		} else {
			texto = texto.replace(/,/g, "");
		}
	} else if (coma >= 0) {
		var partesComa = texto.split(",");
		var decimalesComa = partesComa[partesComa.length - 1];
		if (decimalesComa.length == 3 && partesComa.length > 1) {
			texto = texto.replace(/,/g, "");
		} else {
			texto = texto.replace(",", ".");
		}
	} else if (punto >= 0) {
		var partesPunto = texto.split(".");
		var decimalesPunto = partesPunto[partesPunto.length - 1];
		if (decimalesPunto.length == 3 || partesPunto.length > 2) {
			texto = texto.replace(/\./g, "");
		}
	}

	var numero = Math.round(parseFloat(texto) || 0);
	if (negativo) {
		numero = numero * -1;
	}
	return String(numero);
}

function uenoFormatoMonto(valor) {
	var numero = Number(uenoNormalizarMontoExtracto(valor));
	if (typeof separadordemilesnumero === "function") {
		return separadordemilesnumero(String(numero));
	}
	return String(numero);
}

function uenoNumeroMonto(valor) {
	return Number(uenoNormalizarMontoExtracto(valor)) || 0;
}

function uenoClaseEstado(texto) {
	texto = uenoNormalizarTexto(texto);
	if (texto.indexOf("CONCILIADO") >= 0 || texto.indexOf("ASIGNADO TOTAL") >= 0 || texto.indexOf("IMPORTADO") >= 0) {
		return "ueno-row--ok";
	}
	if (texto.indexOf("PARCIAL") >= 0 || texto.indexOf("ASIGNADO PARCIAL") >= 0) {
		return "ueno-row--partial";
	}
	if (texto.indexOf("DISPONIBLE") >= 0) {
		return "ueno-row--available";
	}
	if (texto.indexOf("PENDIENTE") >= 0) {
		return "ueno-row--pending";
	}
	if (texto.indexOf("OBSERV") >= 0 || texto.indexOf("RECHAZ") >= 0 || texto.indexOf("SIN SALDO") >= 0 || texto.indexOf("DIFERENCIA") >= 0 || texto.indexOf("REVISAR") >= 0) {
		return "ueno-row--alert";
	}
	return "";
}

function uenoModernizarTabla(idTabla, etiquetas, indiceEstado) {
	var contenedor = document.getElementById(idTabla);
	if (!contenedor) {
		return;
	}
	var filas = contenedor.querySelectorAll("tr");
	for (var i = 0; i < filas.length; i++) {
		var fila = filas[i];
		var tabla = fila;
		while (tabla && tabla.tagName != "TABLE") {
			tabla = tabla.parentNode;
		}
		if (tabla) {
			tabla.classList.add("ueno-row-table");
		}
		fila.classList.add("ueno-row");
		var celdas = fila.children;
		var visibles = [];
		for (var j = 0; j < celdas.length; j++) {
			if (celdas[j].style.display != "none") {
				visibles.push(celdas[j]);
			}
		}
		if (visibles.length <= 1) {
			fila.classList.add("ueno-empty-row");
			continue;
		}
		for (var k = 0; k < visibles.length; k++) {
			var etiqueta = etiquetas[k] || "Dato";
			visibles[k].setAttribute("data-label", etiqueta);
			if (etiqueta == "Descripcion" || etiqueta == "Concepto" || etiqueta == "Obs." || etiqueta == "Archivo" || etiqueta == "Cliente/Cuota") {
				visibles[k].setAttribute("title", String(visibles[k].textContent || "").trim());
			}
		}
		if (indiceEstado !== null && indiceEstado !== undefined && visibles[indiceEstado]) {
			var clase = uenoClaseEstado(visibles[indiceEstado].textContent || "");
			if (clase != "") {
				fila.classList.add(clase);
			}
		}
		var estadoMovimiento = fila.getAttribute("data-ueno-estado");
		if (estadoMovimiento) {
			fila.classList.add("ueno-movimiento-row");
			fila.classList.add("ueno-movimiento-row--" + estadoMovimiento);
		}
	}
}

function uenoNormalizarFechaAgrupacion(valor) {
	valor = String(valor || "").trim();
	if (valor == "") { return ""; }
	var partesIso = valor.match(/^(\d{4})-(\d{2})-(\d{2})/);
	if (partesIso) {
		return partesIso[1] + "-" + partesIso[2] + "-" + partesIso[3];
	}
	var partesLocal = valor.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
	if (partesLocal) {
		return partesLocal[3] + "-" + partesLocal[2] + "-" + partesLocal[1];
	}
	return "";
}

function uenoFechaAgrupacionDesdeFila(fila) {
	if (!fila) { return ""; }
	var celdas = fila.children || [];
	var visibles = [];
	for (var i = 0; i < celdas.length; i++) {
		if (celdas[i].style.display != "none") {
			visibles.push(celdas[i]);
		}
	}
	var fechaTransaccion = visibles[2] ? visibles[2].textContent : "";
	var fechaConfirmacion = visibles[1] ? visibles[1].textContent : "";
	return uenoNormalizarFechaAgrupacion(fechaTransaccion) || uenoNormalizarFechaAgrupacion(fechaConfirmacion);
}

function uenoEtiquetaMesAgrupacion(fechaIso) {
	var meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
	var partes = fechaIso.split("-");
	var mes = Number(partes[1] || 0) - 1;
	return (meses[mes] || "Mes") + " " + partes[0];
}

function uenoEtiquetaDiaAgrupacion(fechaIso) {
	var dias = ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado"];
	var partes = fechaIso.split("-");
	var fecha = new Date(Number(partes[0]), Number(partes[1]) - 1, Number(partes[2]), 12, 0, 0);
	var diaSemana = dias[fecha.getDay()] || "Dia";
	return diaSemana + " " + partes[2] + "/" + partes[1] + "/" + partes[0];
}

function uenoCrearSeparadorFecha(tipo, texto) {
	var separador = document.createElement("div");
	separador.className = "ueno-date-separator ueno-date-separator--" + tipo;
	separador.innerHTML = "<span>" + uenoEscapeHtml(texto) + "</span>";
	return separador;
}

function uenoAgregarSeparadoresFechaMovimientos() {
	var contenedor = document.getElementById("table_ueno_movimientos");
	if (!contenedor) { return; }
	var separadoresActuales = contenedor.querySelectorAll(".ueno-date-separator");
	for (var i = 0; i < separadoresActuales.length; i++) {
		separadoresActuales[i].parentNode.removeChild(separadoresActuales[i]);
	}
	var tablas = Array.prototype.slice.call(contenedor.querySelectorAll("table.ueno-row-table"));
	var mesActual = "";
	var diaActual = "";
	for (var j = 0; j < tablas.length; j++) {
		var fila = tablas[j].querySelector("tr");
		var fecha = uenoFechaAgrupacionDesdeFila(fila);
		if (fecha == "") { continue; }
		var mes = fecha.substr(0, 7);
		if (mes != mesActual) {
			contenedor.insertBefore(uenoCrearSeparadorFecha("mes", uenoEtiquetaMesAgrupacion(fecha)), tablas[j]);
			mesActual = mes;
			diaActual = "";
		}
		if (fecha != diaActual) {
			contenedor.insertBefore(uenoCrearSeparadorFecha("dia", uenoEtiquetaDiaAgrupacion(fecha)), tablas[j]);
			diaActual = fecha;
		}
	}
}

function uenoEtiquetasAuditoria() {
	return ["Banco", "ID", "Fecha", "Accion", "Tabla", "Factura", "Mov.", "Cliente/Cuota", "Antes", "Ahora", "Monto", "User", "Obs."];
}

function uenoModernizarTablaAuditoria(idTabla) {
	uenoModernizarTabla(idTabla, uenoEtiquetasAuditoria(), 9);
}

function uenoModernizarVista() {
	uenoModernizarTabla("table_ueno_preview", ["F. conf.", "F. trans.", "Comprobante", "Descripcion", "Concepto", "Debito", "Credito", "Estado"], 7);
	uenoModernizarTabla("table_ueno_resumen_tesoreria", ["Local", "Turno", "Caja", "Lote", "Apertura", "Cierre", "Caja", "Transf. GV", "Conc.", "Pend.", "Obs.", "S/C", "Estado"], 12);
	uenoModernizarTabla("table_ueno_importaciones", ["Banco", "ID", "Cuenta", "Fecha", "Archivo", "Importado", "Mov.", "Cred.", "Deb.", "Estado"], 9);
	uenoModernizarTabla("table_ueno_importaciones_modal", ["Banco", "ID", "Cuenta", "Fecha", "Archivo", "Importado", "Mov.", "Cred.", "Deb.", "Estado", "Encontrado"], 9);
	uenoModernizarTabla("table_ueno_detalle_importacion", ["Nro.", "F. conf.", "F. trans.", "Comprobante", "Detalle", "Deb.", "Cred.", "Disp.", "Duplicado", "Estado"], 9);
	uenoModernizarTabla("table_ueno_movimientos", ["Banco", "F. conf.", "F. trans.", "Comprobante", "Descripcion", "Concepto", "Deb.", "Credito original", "Aplicado", "Disponible", "Estado", "Aplicacion contable", "Cliente / Venta", "Usuario", "Accion"], 10);
	uenoAgregarSeparadoresFechaMovimientos();
	uenoModernizarTabla("table_ueno_candidatos_manual", ["ID", "F. conf.", "Comprobante", "Descripcion", "Credito", "Disponible", "Estado", "Coinc.", "Accion"], 6);
	uenoModernizarTabla("table_ueno_pagos_pendientes", ["Banco", "Cliente", "CI", "Venta", "Cuota/Pago", "Venc.", "Saldo pend.", "Monto sug.", "Estado", "Coinc.", "Accion"], 8);
	uenoModernizarTablaAuditoria("table_ueno_auditoria");
	uenoModernizarTablaAuditoria("table_ueno_auditoria_movimiento");
}

function uenoMarcarImportacionSeleccionada(idImportacion) {
	var contenedores = ["table_ueno_importaciones", "table_ueno_importaciones_modal"];
	for (var c = 0; c < contenedores.length; c++) {
		var contenedor = document.getElementById(contenedores[c]);
		if (!contenedor) {
			continue;
		}
		var filas = contenedor.querySelectorAll("tr");
		for (var i = 0; i < filas.length; i++) {
			filas[i].classList.remove("ueno-row-selected");
			var idFila = filas[i].getAttribute("data-ueno-importacion-id");
			var primeraCelda = filas[i].children && filas[i].children.length ? filas[i].children[0] : null;
			if ((idFila && String(idFila) == String(idImportacion)) || (!idFila && primeraCelda && String(primeraCelda.textContent || "").trim() == String(idImportacion))) {
				filas[i].classList.add("ueno-row-selected");
			}
		}
	}
}

function uenoMostrarMovimientoTrabajo() {
	var contenedor = document.getElementById("divUenoMovimientoSeleccionadoPopup");
	if (!contenedor) {
		return;
	}
	if (!uenoMovimientoTrabajo) {
		contenedor.innerHTML = "<div class='ueno-selected-empty'>"
			+ "<b>Detalle del movimiento</b>"
			+ "<span>Selecciona Ver detalle en la tabla de movimientos para revisar datos, saldo disponible y trazabilidad. Los pagos se procesan desde Caja / Cobrar cuota.</span>"
			+ "</div>";
		return;
	}

	var movimiento = uenoMovimientoTrabajo;
	var disponible = movimiento["monto_disponible_fmt"] || uenoFormatoMonto(movimiento["monto_disponible"] || "0");
	var credito = movimiento["importe_credito_fmt"] || uenoFormatoMonto(movimiento["importe_credito"] || "0");
	var debito = movimiento["importe_debito_fmt"] || uenoFormatoMonto(movimiento["importe_debito"] || "0");
	var saldoDebito = uenoNumeroMonto(movimiento["saldo_disponible"] || movimiento["monto_disponible"] || 0);
	var sugerenciasMigracion = Number(movimiento["sugerencias_migracion"] || 0);
	var depositoFaraone = String(movimiento["depositos_conciliacion"] || "").trim();
	var depositoFaraoneHtml = depositoFaraone != ""
		? "<span class='ueno-selected-wide'><b>Depósito Faraone</b>Conciliado con " + uenoEscapeHtml(depositoFaraone) + "</span>"
		: "";
	var puedeConciliarEgreso = uenoTienePermiso("CONCILIAREGRESOUENO") || uenoTienePermiso("ASIGNARMANUALUENO");
	var estadoDebito = String(movimiento["estado_bancario"] || "").toLowerCase().trim();
	var estadoDebitoDisponible = ["registrado", "disponible", "asignado_parcial"].indexOf(estadoDebito) >= 0;
	var accionGasto = puedeConciliarEgreso && estadoDebitoDisponible && uenoNumeroMonto(movimiento["importe_debito"] || 0) > 0 && saldoDebito > 0
		? "<input type='button' value='Registrar gasto distribuido' class='btn4 ueno-row-action ueno-row-action--available' onclick='uenoRegistrarGastoDesdeDebito(uenoMovimientoTrabajo)' style='width:190px'>"
		: "";
	var accion = accionGasto
		+ "<input type='button' value='Ver trazabilidad' class='btn4 ueno-row-action ueno-row-action--trace' onclick='uenoVerAplicacionMovimiento(" + Number(movimiento["id_movimiento"] || 0) + ")'>"
		+ "<input type='button' value='Limpiar seleccion' class='btn4 ueno-btn-secondary' onclick='uenoLimpiarMovimientoTrabajo(true)' style='width:145px'>";
	var avisoMigracion = sugerenciasMigracion > 0
		? "<div class='ueno-migration-hint'><b>Coincidencia sugerida</b><span>Hay " + sugerenciasMigracion + " deposito/s pendiente/s con este mismo importe exacto.</span></div>"
		: "";

	contenedor.innerHTML = "<div class='ueno-selected-card'>"
		+ "<div class='ueno-selected-title'>Detalle del movimiento - " + uenoEscapeHtml(uenoBancoNombre(movimiento["banco_codigo"] || uenoBancoActual())) + "</div>"
		+ "<div class='ueno-selected-grid'>"
		+ "<span><b>Comprobante</b>" + uenoEscapeHtml(movimiento["nro_comprobante"] || "") + "</span>"
		+ "<span><b>F. confirmacion</b>" + uenoEscapeHtml(movimiento["fecha_confirmacion"] || "") + "</span>"
		+ "<span><b>Credito original</b>" + uenoEscapeHtml(credito) + "</span>"
		+ "<span><b>Disponible</b>" + uenoEscapeHtml(disponible) + "</span>"
		+ "<span><b>Debito</b>" + uenoEscapeHtml(debito) + "</span>"
		+ "<span><b>Estado</b>" + uenoEscapeHtml(movimiento["estado"] || "") + "</span>"
		+ depositoFaraoneHtml
		+ "<span class='ueno-selected-wide'><b>Concepto</b>" + uenoEscapeHtml((movimiento["concepto"] || movimiento["descripcion"] || "")) + "</span>"
		+ "</div>"
		+ avisoMigracion
		+ "<div id='divUenoMigracionSugerida' class='ueno-migration-suggestions'></div>"
		+ "<div class='ueno-selected-actions'>" + accion + "</div>"
		+ "</div>";
}

function uenoRegistrarGastoDesdeDebito(movimiento) {
	if (!uenoTienePermiso("CONCILIAREGRESOUENO") && !uenoTienePermiso("ASIGNARMANUALUENO")) {
		ver_vetana_informativa("No tiene permiso para conciliar egresos Ueno.", "", "error");
		return;
	}
	if (!uenoTienePermiso("INSERTARLISTADOEGRESOINGRESO")) {
		ver_vetana_informativa("No tiene permiso para crear movimientos financieros.", "", "error");
		return;
	}
	movimiento = movimiento || uenoMovimientoTrabajo;
	var saldo = movimiento ? uenoNumeroMonto(movimiento["saldo_disponible"] || movimiento["monto_disponible"] || 0) : 0;
	if (!movimiento || !movimiento["id_movimiento"] || uenoNumeroMonto(movimiento["importe_debito"] || 0) <= 0 || saldo <= 0) {
		ver_vetana_informativa("El debito seleccionado ya no tiene saldo disponible para registrar un gasto.", "", "error");
		return;
	}
	if (typeof abrirMovimientoFinancieroDesdeDebitoUeno != "function") {
		ver_vetana_informativa("El formulario de movimientos financieros no esta disponible.", "", "error");
		return;
	}
	var moduloGastos = document.getElementById("divAbmGastos");
	if (moduloGastos && window.getComputedStyle(moduloGastos).display == "none" && typeof verCerrarAbmGasto == "function") {
		verCerrarAbmGasto();
	}
	uenoCerrarDetalleMesaTrabajo();
	abrirMovimientoFinancieroDesdeDebitoUeno(movimiento);
}

function uenoLimpiarMovimientoTrabajo(limpiarFiltros) {
	uenoMovimientoTrabajo = null;
	uenoLimpiarCuotaGoodVenta();
	if (limpiarFiltros) {
		uenoSetValorTesoreria("inptUenoPagoComprobante", "");
		uenoSetValorTesoreria("inptUenoBuscarCuotaMonto", "");
		var estadoPago = document.getElementById("inptUenoPagoEstado");
		if (estadoPago) {
			estadoPago.value = "pendiente_conciliacion";
		}
	}
	uenoMostrarMovimientoTrabajo();
}

function uenoAbrirCobrarCuotaMovimiento() {
	if (uenoMesaSoloConsulta) {
		return uenoAvisarMesaSoloConsulta();
	}
	if (!uenoMovimientoTrabajo || !uenoMovimientoTrabajo["id_movimiento"]) {
		ver_vetana_informativa("Primero selecciona un movimiento bancario con credito disponible");
		return;
	}
	if (typeof cobrarCuotaAbrirDesdeUeno !== "function") {
		ver_vetana_informativa("El modulo Cobrar cuota todavia no esta cargado");
		return;
	}
	cobrarCuotaAbrirDesdeUeno(uenoMovimientoTrabajo);
}

function uenoSeleccionarMovimientoTrabajo(movimiento) {
	uenoMovimientoTrabajo = movimiento || null;
	uenoIdConciliacionManual = "";
	if (!uenoMesaTrabajoEstaAbierta()) {
		uenoAbrirMesaTrabajoPopup();
	}
	uenoMostrarMovimientoTrabajo();
	uenoLimpiarAsignacionManual();
	uenoAbrirDetalleMesaTrabajo();
	uenoBuscarSugerenciasMigracion();
}

function uenoMesaTrabajoEstaAbierta() {
	var popup = document.getElementById("divUenoMesaTrabajoPopup");
	return !!(popup && popup.style.display != "none" && window.getComputedStyle(popup).display != "none");
}

function uenoAbrirDetalleMesaTrabajo() {
	var overlay = document.getElementById("divUenoDetalleMesaOverlay");
	if (!overlay) { return; }
	var mesa = document.querySelector("#divUenoMesaTrabajoPopup .ueno-workbench-card");
	var listado = document.getElementById("table_ueno_movimientos");
	var scrollPagina = window.pageYOffset || document.documentElement.scrollTop || 0;
	var scrollMesa = mesa ? mesa.scrollTop : 0;
	var scrollListado = listado ? listado.scrollTop : 0;
	overlay.style.display = "flex";
	uenoMostrarVistaDetalleMesa("detalle");
	window.requestAnimationFrame(function() {
		if (mesa) { mesa.scrollTop = scrollMesa; }
		if (listado) { listado.scrollTop = scrollListado; }
		window.scrollTo(0, scrollPagina);
	});
}

function uenoCerrarDetalleMesaTrabajo() {
	var overlay = document.getElementById("divUenoDetalleMesaOverlay");
	if (overlay) { overlay.style.display = "none"; }
	uenoAuditoriaMovimientoActual = "";
}

function uenoMostrarVistaDetalleMesa(vista) {
	var esTraza = vista == "trazabilidad";
	var detalle = document.getElementById("divUenoDetalleMesaDatos");
	var traza = document.getElementById("divUenoDetalleMesaTrazabilidad");
	var btnDetalle = document.getElementById("btnUenoDetalleMesaDatos");
	var btnTraza = document.getElementById("btnUenoDetalleMesaTraza");
	if (detalle) { detalle.style.display = esTraza ? "none" : "block"; }
	if (traza) { traza.style.display = esTraza ? "block" : "none"; }
	if (btnDetalle) { btnDetalle.classList.toggle("ueno-workbench-detail-tab--active", !esTraza); }
	if (btnTraza) { btnTraza.classList.toggle("ueno-workbench-detail-tab--active", esTraza); }
}

function uenoBuscarSugerenciasMigracion() {
	var contenedor = document.getElementById("divUenoMigracionSugerida");
	if (!contenedor || !uenoMovimientoTrabajo || !uenoMovimientoTrabajo["id_movimiento"]) {
		return;
	}
	var credito = uenoNumeroMonto(uenoMovimientoTrabajo["importe_credito"] || uenoMovimientoTrabajo["importe_credito_fmt"] || 0);
	var disponible = uenoNumeroMonto(uenoMovimientoTrabajo["monto_disponible"] || uenoMovimientoTrabajo["monto_disponible_fmt"] || 0);
	if (credito <= 0 || disponible <= 0 || credito != disponible) {
		contenedor.innerHTML = "";
		return;
	}
	contenedor.innerHTML = "<div class='ueno-loading-inline'>Buscando depositos pendientes...</div>";
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "buscar_sugerencias_migracion");
	datos.append("id_movimiento", uenoMovimientoTrabajo["id_movimiento"]);

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					contenedor.innerHTML = "<div class='ueno-migration-title'>Depositos a Faraone Capital S.A.</div>" + (respuesta["2"] || "");
					uenoModernizarTabla("divUenoMigracionSugerida", ["ID", "Fecha", "Monto", "Envia", "Caja origen", "Control", "Accion"], 5);
					return;
				}
				if (respuesta["1"] == "tablasfaltantes") {
					contenedor.innerHTML = "<div class='ueno-migration-empty ueno-migration-empty--alert'>" + uenoEscapeHtml(respuesta["2"] || "Falta configurar conciliacion de migraciones.") + "</div>";
					return;
				}
				if (respuesta["1"] == "NI") {
					contenedor.innerHTML = "";
					return;
				}
				contenedor.innerHTML = "<div class='ueno-migration-empty'>" + uenoEscapeHtml(respuesta["2"] || "No se pudieron buscar montos migrados.") + "</div>";
			} catch (error) {
				contenedor.innerHTML = "<div class='ueno-migration-empty'>No se pudo interpretar la busqueda de montos migrados.</div>";
			}
		},
		error: function() {
			contenedor.innerHTML = "<div class='ueno-migration-empty'>No se pudo conectar para buscar montos migrados.</div>";
		}
	});
}

function uenoConfirmarConciliacionDeposito(idMovimiento, origenTipo, origenId) {
	if (!idMovimiento || !origenId || (origenTipo != "gasto" && origenTipo != "migrar_caja")) {
		ver_vetana_informativa("No se pudo identificar el deposito seleccionado.", "", "error");
		return;
	}
	var origenTexto = origenTipo == "gasto" ? "deposito registrado" : "registro historico";
	var mensaje = "Confirmar la conciliacion definitiva del credito Ueno con el " + origenTexto + " #" + origenId + "?\n\nEsta operacion no se puede revertir.";
	if (!window.confirm(mensaje)) { return; }
	verCerrarEfectoCargando("1");
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "conciliar_deposito_faraone");
	datos.append("id_movimiento", idMovimiento);
	datos.append("origen_tipo", origenTipo);
	datos.append("origen_id", origenId);
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function(jqXHR, textstatus) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "uenoConfirmarConciliacionDeposito");
		},
		success: function(responseText) {
			verCerrarEfectoCargando("");
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] != "exito") {
					ver_vetana_informativa(respuesta["2"] || "No se pudo conciliar el deposito.", "", "error");
					return;
				}
				ver_vetana_informativa(respuesta["2"], "", "exito");
				uenoCerrarDetalleMesaTrabajo();
				uenoLimpiarMovimientoTrabajo(false);
				uenoBuscarMovimientos(uenoIdImportacionSeleccionada || "");
				uenoBuscarResumenTesoreria();
				uenoBuscarAuditoria();
			} catch (error) {
				ver_vetana_informativa("Error inesperado al conciliar el deposito.", String(error), "error");
			}
		}
	});
}

function uenoConfirmarConciliacionMigracion(idMovimiento, idMigracion, requiereAdvertencia) {
	if (!idMovimiento || !idMigracion) {
		ver_vetana_informativa("No se pudo identificar la migracion seleccionada.", "", "error");
		return;
	}
	var mensaje = "Confirmar conciliacion interna del credito Ueno con el monto migrado #" + idMigracion + "?";
	if (String(requiereAdvertencia || "") == "SI") {
		mensaje = "ATENCION: esta coincidencia tiene diferencia de fechas fuera del rango normal. Confirmar solo si Tesoreria verifico que corresponde al monto migrado #" + idMigracion + ".\n\n" + mensaje;
	}
	if (!window.confirm(mensaje)) {
		return;
	}
	var observacion = window.prompt("Observacion opcional para la auditoria:", "Conciliacion interna de monto migrado #" + idMigracion);
	if (observacion === null) {
		return;
	}
	verCerrarEfectoCargando("1");
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "conciliar_migracion_caja");
	datos.append("id_movimiento", idMovimiento);
	datos.append("idmigrar_caja", idMigracion);
	datos.append("confirmar_advertencia", String(requiereAdvertencia || "") == "SI" ? "SI" : "NO");
	datos.append("observacion", observacion || "");

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function(jqXHR, textstatus) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "uenoConfirmarConciliacionMigracion");
		},
		success: function(responseText) {
			verCerrarEfectoCargando("");
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] != "exito") {
					ver_vetana_informativa(respuesta["2"] || "No se pudo conciliar el monto migrado.", "", "error");
					return;
				}
				ver_vetana_informativa(respuesta["2"] || "Monto migrado conciliado internamente.", "", "exito");
				uenoLimpiarMovimientoTrabajo(false);
				uenoBuscarMovimientos(uenoIdImportacionSeleccionada || "");
				uenoBuscarResumenTesoreria();
				uenoBuscarAuditoria();
			} catch (error) {
				ver_vetana_informativa("Error inesperado al conciliar migracion.", String(error), "error");
			}
		}
	});
}

function uenoAsignarMovimientoSeleccionado() {
	return uenoAvisarMesaSoloConsulta();
}

function uenoMostrarCuotaGoodVentaSeleccionada() {
	var contenedor = document.getElementById("divUenoCuotaSeleccionada");
	if (!contenedor) {
		return;
	}
	if (!uenoCuotaGoodVentaSeleccionada) {
		contenedor.innerHTML = "<div class='ueno-selected-empty'>"
			+ "<b>Consulta de cuota</b>"
			+ "<span>La mesa Ueno ya no permite vincular movimientos a cuotas. Consulta pagos desde auditoria o usa Caja / Cobrar cuota para registrar cobros.</span>"
			+ "</div>";
		return;
	}

	var cuota = uenoCuotaGoodVentaSeleccionada;
	var disponible = uenoMovimientoTrabajo ? uenoNumeroMonto(uenoMovimientoTrabajo["monto_disponible"] || uenoMovimientoTrabajo["monto_disponible_fmt"] || 0) : 0;
	var saldo = uenoNumeroMonto(cuota["saldo_pendiente_num"] || cuota["saldo_pendiente"] || cuota["monto_num"] || cuota["monto"] || 0);
	var sugerido = uenoNumeroMonto(cuota["monto_sugerido_num"] || 0);
	if (sugerido <= 0) {
		sugerido = disponible > 0 ? Math.min(disponible, saldo) : saldo;
	}
	contenedor.innerHTML = "<div class='ueno-selected-card ueno-selected-card--quota'>"
		+ "<div class='ueno-selected-title'>Cuota Telar seleccionada</div>"
		+ "<div class='ueno-selected-grid ueno-selected-grid--quota'>"
		+ "<span><b>Cliente</b>" + uenoEscapeHtml(cuota["cliente"] || "") + "</span>"
		+ "<span><b>Cedula</b>" + uenoEscapeHtml(cuota["cedula"] || "") + "</span>"
		+ "<span><b>Venta</b>" + uenoEscapeHtml(cuota["venta"] || "") + "</span>"
		+ "<span><b>Cuota/Pago</b>" + uenoEscapeHtml(cuota["cuota"] || cuota["factura"] || "") + "</span>"
		+ "<span><b>Vencimiento</b>" + uenoEscapeHtml(cuota["fecha"] || "") + "</span>"
		+ "<span><b>Saldo pendiente</b>" + uenoEscapeHtml(cuota["saldo_pendiente"] || cuota["monto"] || "0") + "</span>"
		+ "<span><b>Estado</b>" + uenoEscapeHtml(cuota["estado"] || "") + "</span>"
		+ "<span><b>Coincidencia</b>" + uenoEscapeHtml(cuota["coincidencia"] || "") + "</span>"
		+ "</div>"
		+ "<div class='ueno-apply-panel'>"
		+ "<label><b>Monto a aplicar</b><input class='inputText' type='text' id='inptUenoMontoAplicarCuota' value='" + uenoEscapeHtml(uenoFormatoMonto(sugerido)) + "' onkeyup='uenoValidarAplicacionCuota()' style='text-align:right'></label>"
		+ "<label><b>Observacion</b><input class='inputText' type='text' id='txtUenoManualObservacion' placeholder='Motivo si hay diferencia o aplicacion parcial'></label>"
		+ "<div class='ueno-apply-actions'>"
		+ "<span id='lblUenoAplicarCuotaAyuda' class='ueno-apply-help'>Solo consulta. El cobro se registra desde Caja / Cobrar cuota.</span>"
		+ "</div>"
		+ "</div>"
		+ "</div>";
	uenoValidarAplicacionCuota();
}

function uenoLimpiarCuotaGoodVenta() {
	uenoCuotaGoodVentaSeleccionada = null;
	uenoIdConciliacionManual = "";
	uenoMostrarCuotaGoodVentaSeleccionada();
	uenoMarcarCuotaGoodVentaSeleccionada("");
}

function uenoMarcarCuotaGoodVentaSeleccionada(idConciliacion) {
	var contenedor = document.getElementById("table_ueno_pagos_pendientes");
	if (!contenedor) {
		return;
	}
	var filas = contenedor.querySelectorAll("tr");
	for (var i = 0; i < filas.length; i++) {
		filas[i].classList.remove("ueno-row-selected");
		if (idConciliacion != "" && String(filas[i].getAttribute("data-ueno-pago-id") || "") == String(idConciliacion)) {
			filas[i].classList.add("ueno-row-selected");
		}
	}
}

function uenoSeleccionarCuotaGoodVenta(cuota) {
	if (uenoMesaSoloConsulta) {
		return uenoAvisarMesaSoloConsulta();
	}
	uenoCuotaGoodVentaSeleccionada = cuota || null;
	uenoIdConciliacionManual = cuota && cuota["id"] ? cuota["id"] : "";
	uenoCargarPagoManual(cuota || {});
	uenoMostrarCuotaGoodVentaSeleccionada();
	uenoMarcarCuotaGoodVentaSeleccionada(uenoIdConciliacionManual);
	var panel = document.getElementById("divUenoCuotaSeleccionada");
	if (panel && panel.scrollIntoView) {
		panel.scrollIntoView({ behavior: "smooth", block: "center" });
	}
}

function uenoValidarAplicacionCuota() {
	var boton = document.getElementById("btnUenoAplicarCreditoCuota");
	var ayuda = document.getElementById("lblUenoAplicarCuotaAyuda");
	if (!boton) {
		return false;
	}
	var mensaje = "";
	var valido = true;
	if (!uenoMovimientoTrabajo || !uenoMovimientoTrabajo["id_movimiento"]) {
		valido = false;
		mensaje = "Primero selecciona un movimiento bancario.";
	} else if (!uenoCuotaGoodVentaSeleccionada || !uenoCuotaGoodVentaSeleccionada["id"]) {
		valido = false;
		mensaje = "Primero selecciona una cuota.";
	} else {
		var monto = uenoNumeroMonto(document.getElementById("inptUenoMontoAplicarCuota") ? document.getElementById("inptUenoMontoAplicarCuota").value : 0);
		var disponible = uenoNumeroMonto(uenoMovimientoTrabajo["monto_disponible"] || uenoMovimientoTrabajo["monto_disponible_fmt"] || 0);
		var saldo = uenoNumeroMonto(uenoCuotaGoodVentaSeleccionada["saldo_pendiente_num"] || uenoCuotaGoodVentaSeleccionada["saldo_pendiente"] || 0);
		if (monto <= 0) {
			valido = false;
			mensaje = "El monto debe ser mayor a cero.";
		} else if (disponible <= 0) {
			valido = false;
			mensaje = "El movimiento bancario no tiene disponible.";
		} else if (monto > disponible) {
			valido = false;
			mensaje = "El monto supera el saldo disponible del movimiento bancario.";
		} else if (monto > saldo) {
			valido = false;
			mensaje = "El monto supera el saldo pendiente de la cuota.";
		} else if (uenoNormalizarTexto(uenoCuotaGoodVentaSeleccionada["estado"] || "").indexOf("CONCILIADO") >= 0) {
			valido = false;
			mensaje = "Esta cuota ya esta conciliada.";
		} else {
			mensaje = "Listo para aplicar " + uenoFormatoMonto(monto) + ".";
		}
	}
	boton.disabled = !valido;
	boton.classList.toggle("ueno-btn-disabled", !valido);
	boton.title = valido ? "" : mensaje;
	if (ayuda) {
		ayuda.textContent = mensaje;
		ayuda.classList.toggle("ueno-apply-help--alert", !valido);
	}
	return valido;
}

function uenoAplicarCreditoACuota() {
	if (uenoMesaSoloConsulta) {
		return uenoAvisarMesaSoloConsulta();
	}
	if (!uenoMovimientoTrabajo || !uenoMovimientoTrabajo["id_movimiento"]) {
		ver_vetana_informativa("Selecciona primero un movimiento bancario disponible");
		return;
	}
	if (!uenoCuotaGoodVentaSeleccionada || uenoIdConciliacionManual == "") {
		ver_vetana_informativa("Primero selecciona una cuota de Telar");
		return;
	}
	if (!uenoValidarAplicacionCuota()) {
		ver_vetana_informativa(document.getElementById("lblUenoAplicarCuotaAyuda") ? document.getElementById("lblUenoAplicarCuotaAyuda").textContent : "Verifica la seleccion");
		return;
	}
	uenoAsignarMovimientoManual(uenoMovimientoTrabajo["id_movimiento"]);
}

function uenoTextoFila(fila) {
	var texto = "";
	for (var i = 0; i < fila.length; i++) {
		texto += " " + String(fila[i] || "");
	}
	return texto;
}

function uenoBuscarIndiceEncabezado(filas) {
	var requeridos = [
		"FECHA DE CONFIRMACION",
		"FECHA DE TRANSACCION",
		"NRO DE COMPROBANTE",
		"DESCRIPCION",
		"CONCEPTO",
		"IMPORTE DEBITO",
		"IMPORTE CREDITO"
	];

	for (var i = 0; i < filas.length; i++) {
		var texto = uenoNormalizarTexto(uenoTextoFila(filas[i]));
		var encontrados = 0;
		for (var r = 0; r < requeridos.length; r++) {
			if (texto.indexOf(requeridos[r]) >= 0) {
				encontrados++;
			}
		}
		if (encontrados >= 5) {
			return i;
		}
	}
	return -1;
}

function uenoResolverColumna(headers, opciones) {
	for (var i = 0; i < headers.length; i++) {
		var header = uenoNormalizarTexto(headers[i]);
		for (var o = 0; o < opciones.length; o++) {
			if (header == opciones[o] || header.indexOf(opciones[o]) >= 0) {
				return i;
			}
		}
	}
	return -1;
}

function uenoValorDespuesDeEtiqueta(fila, indiceEtiqueta) {
	var etiqueta = String(fila[indiceEtiqueta] == null ? "" : fila[indiceEtiqueta]).trim();
	var posicionDosPuntos = etiqueta.indexOf(":");
	if (posicionDosPuntos >= 0) {
		var valorMismaCelda = etiqueta.substring(posicionDosPuntos + 1).trim();
		if (valorMismaCelda != "") {
			return valorMismaCelda;
		}
	}
	for (var i = indiceEtiqueta + 1; i < fila.length; i++) {
		var valor = String(fila[i] == null ? "" : fila[i]).trim();
		if (valor != "") {
			return valor;
		}
	}
	return "";
}

function uenoExtraerMeta(filas, clave) {
	var limite = Math.min(filas.length, 25);
	var regexCuenta = /(CUENTA|NRO CUENTA|NUMERO DE CUENTA|NRO\. DE CUENTA)[^0-9]{0,20}([0-9]{6,})/i;
	for (var i = 0; i < limite; i++) {
		var fila = filas[i] || [];
		for (var c = 0; c < fila.length; c++) {
			var etiqueta = uenoNormalizarTexto(fila[c]);
			if (clave == "cuenta" && /^(CUENTA|NRO CUENTA|NUMERO DE CUENTA|NRO DE CUENTA)\s*:/.test(etiqueta)) {
				var cuentaCeldas = uenoValorDespuesDeEtiqueta(fila, c).replace(/\D/g, "");
				if (cuentaCeldas.length >= 6) {
					return cuentaCeldas;
				}
			}
			if (clave == "denominacion" && /^(DENOMINACION|TITULAR|EMPRESA)\s*:/.test(etiqueta)) {
				var denominacionCeldas = uenoValorDespuesDeEtiqueta(fila, c);
				if (denominacionCeldas != "") {
					return denominacionCeldas;
				}
			}
		}
		var texto = uenoTextoFila(filas[i]);
		if (clave == "cuenta") {
			var matchCuenta = texto.match(regexCuenta);
			if (matchCuenta && matchCuenta[2]) {
				return matchCuenta[2];
			}
		}
		if (clave == "denominacion") {
			var normal = uenoNormalizarTexto(texto);
			if (normal.indexOf("DENOMINACION") >= 0 || normal.indexOf("TITULAR") >= 0 || normal.indexOf("EMPRESA") >= 0) {
				return String(texto).replace(/Denominacion|Titular|Empresa|:/ig, "").trim();
			}
		}
	}
	if (clave == "cuenta") {
		for (var j = 0; j < limite; j++) {
			var textoLibre = uenoTextoFila(filas[j]);
			var matchLibre = textoLibre.match(/([0-9]{8,})/);
			if (matchLibre && matchLibre[1]) {
				return matchLibre[1];
			}
		}
	}
	return "";
}

function uenoExtraerPeriodo(filas, movimientos) {
	var limite = Math.min(filas.length, 25);
	for (var i = 0; i < limite; i++) {
		var texto = uenoTextoFila(filas[i]);
		if (uenoNormalizarTexto(texto).indexOf("PERIODO") < 0) {
			continue;
		}
		var fechasDeclaradas = texto.match(/\b\d{1,4}[-\/]\d{1,2}[-\/]\d{1,4}\b/g) || [];
		if (fechasDeclaradas.length >= 2) {
			var desdeDeclarado = uenoFechaExtractoIso(fechasDeclaradas[0]);
			var hastaDeclarado = uenoFechaExtractoIso(fechasDeclaradas[1]);
			if (desdeDeclarado && hastaDeclarado) {
				return { desde: desdeDeclarado, hasta: hastaDeclarado };
			}
		}
	}

	var fechas = [];
	for (var m = 0; m < (movimientos || []).length; m++) {
		var fecha = uenoFechaExtractoIso(movimientos[m].fecha_confirmacion || movimientos[m].fecha_transaccion || "");
		if (fecha) {
			fechas.push(fecha);
		}
	}
	fechas.sort();
	return fechas.length ? { desde: fechas[0], hasta: fechas[fechas.length - 1] } : { desde: "", hasta: "" };
}

function uenoAplicarPeriodoUeno(filas, movimientos) {
	var periodo = uenoExtraerPeriodo(filas || [], movimientos || []);
	document.getElementById("inptUenoPeriodoDesde").value = periodo.desde || "";
	document.getElementById("inptUenoPeriodoHasta").value = periodo.hasta || "";
	document.getElementById("inptUenoFechaExtracto").value = periodo.hasta || "";
}

function uenoParsearMovimientos(filas) {
	var indiceHeader = uenoBuscarIndiceEncabezado(filas);
	if (indiceHeader < 0) {
		throw new Error("No se encontro la fila de encabezados Ueno");
	}

	var headers = filas[indiceHeader];
	var columnas = {
		fecha_confirmacion: uenoResolverColumna(headers, ["FECHA DE CONFIRMACION", "FECHA CONFIRMACION"]),
		fecha_transaccion: uenoResolverColumna(headers, ["FECHA DE TRANSACCION", "FECHA TRANSACCION"]),
		nro_comprobante: uenoResolverColumna(headers, ["NRO DE COMPROBANTE", "NRO. DE COMPROBANTE", "NRO COMPROBANTE", "COMPROBANTE"]),
		descripcion: uenoResolverColumna(headers, ["DESCRIPCION"]),
		concepto: uenoResolverColumna(headers, ["CONCEPTO"]),
		importe_debito: uenoResolverColumna(headers, ["IMPORTE DEBITO", "DEBITO"]),
		importe_credito: uenoResolverColumna(headers, ["IMPORTE CREDITO", "CREDITO"]),
		saldo_banco: uenoResolverColumna(headers, ["SALDO"])
	};

	if (columnas.nro_comprobante < 0 || columnas.importe_credito < 0 || columnas.importe_debito < 0) {
		throw new Error("El extracto no tiene las columnas minimas de comprobante, credito y debito");
	}

	var movimientos = [];
	for (var i = indiceHeader + 1; i < filas.length; i++) {
		var fila = filas[i] || [];
		var debitoRaw = columnas.importe_debito >= 0 ? fila[columnas.importe_debito] : "";
		var creditoRaw = columnas.importe_credito >= 0 ? fila[columnas.importe_credito] : "";
		var saldoRaw = columnas.saldo_banco >= 0 ? fila[columnas.saldo_banco] : "";
		var mov = {
			fecha_confirmacion: columnas.fecha_confirmacion >= 0 ? fila[columnas.fecha_confirmacion] : "",
			fecha_transaccion: columnas.fecha_transaccion >= 0 ? fila[columnas.fecha_transaccion] : "",
			nro_comprobante: columnas.nro_comprobante >= 0 ? fila[columnas.nro_comprobante] : "",
			descripcion: columnas.descripcion >= 0 ? fila[columnas.descripcion] : "",
			concepto: columnas.concepto >= 0 ? fila[columnas.concepto] : "",
			importe_debito: uenoNormalizarMontoExtracto(debitoRaw),
			importe_credito: uenoNormalizarMontoExtracto(creditoRaw),
			saldo_banco: uenoNormalizarMontoExtracto(saldoRaw)
		};
		var texto = uenoTextoFila(fila).trim();
		if (texto != "" && (String(mov.nro_comprobante).trim() != "" || Number(mov.importe_credito) != 0 || Number(mov.importe_debito) != 0)) {
			movimientos.push(mov);
		}
	}

	return movimientos;
}

function uenoBufferToHex(buffer) {
	var bytes = new Uint8Array(buffer);
	var hex = "";
	for (var i = 0; i < bytes.length; i++) {
		hex += ("00" + bytes[i].toString(16)).slice(-2);
	}
	return hex;
}

function uenoCalcularHash(buffer) {
	function calcularLocal() {
		if (typeof TelarSha256 !== "undefined" && typeof TelarSha256.calcular === "function") {
			try {
				return Promise.resolve(TelarSha256.calcular(buffer));
			} catch (error) {
				return Promise.reject(error);
			}
		}
		return Promise.reject(new Error("No se cargo el calculador SHA-256 local"));
	}
	if (window.crypto && window.crypto.subtle && typeof window.crypto.subtle.digest === "function") {
		try {
			return window.crypto.subtle.digest("SHA-256", buffer).then(uenoBufferToHex).catch(calcularLocal);
		} catch (error) {
			return calcularLocal();
		}
	}
	return calcularLocal();
}

function uenoExtensionArchivo(nombre) {
	var match = String(nombre || "").toLowerCase().match(/\.([a-z0-9]+)$/);
	return match ? match[1] : "";
}

function uenoResolverBancoArchivo(file, filas) {
	var extension = uenoExtensionArchivo(file ? file.name : "");
	var banco = "";
	if (extension == "pdf") {
		banco = "FAMILIAR";
	} else if (["xls", "xlsx", "csv"].indexOf(extension) >= 0) {
		if (typeof BancoFamiliarExcel !== "undefined" && typeof BancoFamiliarExcel.esFormato === "function" && BancoFamiliarExcel.esFormato(filas || [])) {
			banco = "FAMILIAR";
		} else if (uenoBuscarIndiceEncabezado(filas || []) >= 0) {
			banco = "UENO";
		}
	}
	if (!banco) {
		throw new Error("El archivo no coincide con la estructura esperada de Banco Familiar ni de Ueno");
	}
	var modo = uenoModoBancoActual();
	if (modo != "AUTO" && modo != banco) {
		throw new Error("El archivo corresponde a " + uenoBancoNombre(banco) + ", pero el selector esta configurado como " + uenoBancoNombre(modo) + ". Cambia el selector o usa Deteccion automatica.");
	}
	return banco;
}

function uenoAplicarResultadoFamiliar(resultado) {
	uenoMetadatosExtracto = resultado.metadatos || uenoMetadatosExtracto;
	uenoMovimientosPreview = resultado.movimientos || [];
	document.getElementById("inptUenoCuenta").value = uenoMetadatosExtracto.cuenta || "";
	document.getElementById("inptUenoDenominacion").value = uenoMetadatosExtracto.denominacion || "";
	var desde = uenoFechaFamiliarIso(uenoMetadatosExtracto.periodo_desde);
	var hasta = uenoFechaFamiliarIso(uenoMetadatosExtracto.periodo_hasta);
	document.getElementById("inptUenoPeriodoDesde").value = desde;
	document.getElementById("inptUenoPeriodoHasta").value = hasta;
	document.getElementById("inptUenoFechaExtracto").value = hasta;
}

function uenoProcesarArchivo(event) {
	var file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
	if (!file) {
		return;
	}
	uenoLimpiarPreviewExtracto();
	var lecturaVersion = uenoLecturaArchivoVersion;
	document.getElementById("inptUenoArchivo").value = file.name;
	var extension = uenoExtensionArchivo(file.name);
	var esPdf = extension == "pdf";
	var esHoja = ["xls", "xlsx", "csv"].indexOf(extension) >= 0;
	if (!esPdf && !esHoja) {
		uenoMostrarEstadoArchivo("error", "Usa un archivo PDF, XLS, XLSX o CSV compatible.");
		ver_vetana_informativa("Formato de extracto no admitido", "Usa PDF o Excel para Banco Familiar, y Excel o CSV para Ueno.", "error");
		return;
	}
	if (esHoja && typeof XLSX === "undefined") {
		ver_vetana_informativa("No se cargo el lector Excel local. Verifica js_system/excel.js", "", "error");
		return;
	}
	if (esPdf && (typeof BancoFamiliarPdf === "undefined" || typeof BancoFamiliarPdf.parsear !== "function")) {
		ver_vetana_informativa("No se cargo el lector PDF local de Banco Familiar", "", "error");
		return;
	}
	uenoFormatoArchivo = esPdf ? "PDF" : (extension == "csv" ? "CSV" : "Excel");
	uenoMostrarEstadoArchivo("validando", "Leyendo el archivo y calculando su huella de seguridad...");

	var reader = new FileReader();
	reader.onload = function(e) {
		if (lecturaVersion != uenoLecturaArchivoVersion) {
			return;
		}
		var data = e.target.result;
		var filas = null;
		var banco;
		try {
			if (esHoja) {
				var workbook = XLSX.read(data, { type: "array", raw: false, cellDates: false });
				var sheetName = workbook.SheetNames[0];
				filas = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], { header: 1, raw: false, defval: "" });
			}
			banco = uenoResolverBancoArchivo(file, filas);
			uenoBancoDetectado = banco;
			uenoActualizarContextoBanco();
			uenoMostrarEstadoArchivo("validando", "Formato reconocido. Validando movimientos, saldos y duplicados...");
		} catch (errorDeteccion) {
			uenoMostrarEstadoArchivo("error", errorDeteccion.message || String(errorDeteccion));
			ver_vetana_informativa("No se pudo reconocer el extracto", errorDeteccion.message || String(errorDeteccion), "error");
			return;
		}
		uenoCalcularHash(data).then(function(hash) {
			if (lecturaVersion != uenoLecturaArchivoVersion) {
				throw { uenoLecturaCancelada: true };
			}
			uenoHashArchivo = hash;
			if (banco == "FAMILIAR" && esPdf) {
				return BancoFamiliarPdf.parsear(data).then(function(resultado) {
					if (lecturaVersion != uenoLecturaArchivoVersion) {
						throw { uenoLecturaCancelada: true };
					}
					uenoAplicarResultadoFamiliar(resultado);
				});
			}
			if (banco == "FAMILIAR") {
				if (typeof BancoFamiliarExcel === "undefined" || typeof BancoFamiliarExcel.parsear !== "function") {
					throw new Error("No se cargo el lector Excel local de Banco Familiar");
				}
				uenoAplicarResultadoFamiliar(BancoFamiliarExcel.parsear(filas));
				return null;
			}
			uenoMovimientosPreview = uenoParsearMovimientos(filas);
			document.getElementById("inptUenoCuenta").value = uenoExtraerMeta(filas, "cuenta");
			document.getElementById("inptUenoDenominacion").value = uenoExtraerMeta(filas, "denominacion");
			uenoAplicarPeriodoUeno(filas, uenoMovimientosPreview);
			return null;
		}).then(function() {
				if (lecturaVersion != uenoLecturaArchivoVersion) {
					return;
				}
				document.getElementById("inptUenoHash").value = uenoHashArchivo;
				document.getElementById("inptUenoCantidadLeida").value = separadordemilesnumero(String(uenoMovimientosPreview.length));
				uenoRenderPreview(uenoMovimientosPreview);
				uenoMostrarEstadoArchivo("validando", "Estructura y saldos correctos. Verificando movimientos ya importados...");
				uenoPrevalidarPreview();
		}).catch(function(error) {
			if (error && error.uenoLecturaCancelada) {
				return;
			}
			uenoPreviewListaParaImportar = false;
			uenoHabilitarBotonImportar(false);
			var detalle = String(error && error.message ? error.message : error);
			uenoMostrarEstadoArchivo("error", detalle);
			ver_vetana_informativa("No se pudo leer el extracto de " + uenoBancoNombre(banco), detalle, "error");
		});
	};
	reader.onerror = function() {
		if (lecturaVersion != uenoLecturaArchivoVersion) {
			return;
		}
		uenoMostrarEstadoArchivo("error", "El navegador no pudo abrir el archivo seleccionado.");
		ver_vetana_informativa("No se pudo abrir el extracto seleccionado", "Vuelve a descargarlo del banco e intenta nuevamente.", "error");
	};
	reader.readAsArrayBuffer(file);
}

function uenoEstadoPreviewTexto(mov) {
	var estado = mov && mov.estado_importacion_preview ? String(mov.estado_importacion_preview) : "";
	if (estado == "ya_importado") {
		return "Ya importado";
	}
	if (estado == "repetido_archivo") {
		return "Repetido en archivo";
	}
	if (estado == "nuevo") {
		return "Nuevo";
	}
	if (uenoPreviewValidando) {
		return "Verificando";
	}
	return "Sin verificar";
}

function uenoEstadoPreviewClase(mov) {
	var estado = mov && mov.estado_importacion_preview ? String(mov.estado_importacion_preview) : "";
	if (estado == "ya_importado" || estado == "repetido_archivo") {
		return " ueno-preview-duplicate";
	}
	if (estado == "nuevo") {
		return " ueno-preview-new";
	}
	return "";
}

function uenoContarPreviewPorEstado() {
	var resumen = { nuevos: 0, duplicados: 0, sin_verificar: 0 };
	for (var i = 0; i < uenoMovimientosPreview.length; i++) {
		var estado = String(uenoMovimientosPreview[i].estado_importacion_preview || "");
		if (estado == "ya_importado" || estado == "repetido_archivo") {
			resumen.duplicados++;
		} else if (estado == "nuevo") {
			resumen.nuevos++;
		} else {
			resumen.sin_verificar++;
		}
	}
	return resumen;
}

function uenoMostrarResumenPreview(mensajeExtra, alerta) {
	var contenedor = document.getElementById("divUenoResumenImportacion");
	if (!contenedor) {
		return;
	}
	var resumen = uenoContarPreviewPorEstado();
	var clase = alerta ? " ueno-import-summary--alert" : "";
	var html = "<div class='ueno-import-summary" + clase + "'>"
		+ "<b>Vista previa</b>"
		+ "<span>Leidos: " + uenoEscapeHtml(String(uenoMovimientosPreview.length)) + "</span>"
		+ "<span>Nuevos: " + uenoEscapeHtml(String(resumen.nuevos)) + "</span>"
		+ "<span>Ya importados: " + uenoEscapeHtml(String(resumen.duplicados)) + "</span>";
	if (resumen.sin_verificar > 0) {
		html += "<span>Sin verificar: " + uenoEscapeHtml(String(resumen.sin_verificar)) + "</span>";
	}
	if (mensajeExtra) {
		html += "<span>" + uenoEscapeHtml(mensajeExtra) + "</span>";
	}
	html += "</div>";
	contenedor.innerHTML = html;
}

function uenoPrevalidarPreview() {
	uenoPreviewListaParaImportar = false;
	uenoHabilitarBotonImportar(false);
	if (!uenoMovimientosPreview.length) {
		uenoMostrarResumenPreview("", false);
		return;
	}
	var cuenta = document.getElementById("inptUenoCuenta") ? document.getElementById("inptUenoCuenta").value : "";
	if (cuenta == "") {
		uenoMostrarResumenPreview("Completa la cuenta para verificar duplicados", true);
		uenoMostrarEstadoArchivo("error", "No se pudo identificar la cuenta. Completa el campo para continuar con la verificacion.");
		return;
	}
	if (!uenoTienePermiso("IMPORTAREXTRACTOUENO")) {
		uenoMostrarEstadoArchivo("error", "El usuario no tiene permiso para importar extractos.");
		return;
	}

	uenoPreviewValidando = true;
	uenoRenderPreview(uenoMovimientosPreview);
	uenoMostrarResumenPreview("Verificando contra la base de datos...", false);
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "prevalidar_importacion");
	uenoAgregarBanco(datos);
	datos.append("cuenta", cuenta);
	datos.append("movimientos_json", JSON.stringify(uenoMovimientosPreview));

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			uenoPreviewValidando = false;
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] != "exito") {
					uenoMostrarResumenPreview(datos["2"] || "No se pudo verificar duplicados", true);
					uenoMostrarEstadoArchivo("error", datos["2"] || "No se pudo verificar los movimientos contra la base de datos.");
					uenoRenderPreview(uenoMovimientosPreview);
					return;
				}
				var movimientos = datos["movimientos"] || [];
				for (var i = 0; i < movimientos.length; i++) {
					var item = movimientos[i];
					var indice = parseInt(item["indice"], 10);
					if (!isNaN(indice) && uenoMovimientosPreview[indice]) {
						uenoMovimientosPreview[indice].estado_importacion_preview = item["estado"];
						uenoMovimientosPreview[indice].detalle_importacion_preview = item["detalle"] || "";
						uenoMovimientosPreview[indice].id_movimiento_existente = item["id_movimiento"] || "";
						uenoMovimientosPreview[indice].id_importacion_existente = item["id_importacion"] || "";
					}
				}
				uenoRenderPreview(uenoMovimientosPreview);
				var resumen = uenoContarPreviewPorEstado();
				uenoPreviewListaParaImportar = resumen.sin_verificar == 0 && resumen.nuevos > 0;
				uenoHabilitarBotonImportar(uenoPreviewListaParaImportar);
				if (uenoPreviewListaParaImportar) {
					uenoMostrarResumenPreview("Validaciones completas", false);
					uenoMostrarEstadoArchivo("listo", "Estructura, saldos y duplicados verificados. Revisa la vista previa antes de importar.");
				} else {
					uenoMostrarResumenPreview("Todos los movimientos ya fueron importados", true);
					uenoMostrarEstadoArchivo("error", "No hay movimientos nuevos para importar.");
				}
			} catch (error) {
				uenoMostrarResumenPreview("No se pudo interpretar la verificacion", true);
				uenoMostrarEstadoArchivo("error", "La respuesta de verificacion del servidor no es valida.");
				uenoRenderPreview(uenoMovimientosPreview);
			}
		},
		error: function(jqXHR, textstatus) {
			uenoPreviewValidando = false;
			uenoMostrarResumenPreview("No se pudo verificar duplicados: " + textstatus, true);
			uenoMostrarEstadoArchivo("error", "No se pudo verificar el extracto contra la base de datos.");
			uenoRenderPreview(uenoMovimientosPreview);
		}
	});
}

function uenoRenderPreview(movimientos) {
	var html = "";
	var totalCreditos = 0;
	var totalDebitos = 0;
	var limitePreview = Math.min(movimientos.length, 80);
	for (var i = 0; i < movimientos.length; i++) {
		var mov = movimientos[i];
		totalCreditos += Number(QuitarSeparadorMilValor(mov.importe_credito || 0));
		totalDebitos += Number(QuitarSeparadorMilValor(mov.importe_debito || 0));
		if (i >= limitePreview) {
			continue;
		}
		var estadoTexto = uenoEstadoPreviewTexto(mov);
		var detalleEstado = mov.detalle_importacion_preview || "";
		var titleEstado = detalleEstado ? " title='" + uenoEscapeHtml(detalleEstado) + "'" : "";
		html += "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr class='" + uenoEstadoPreviewClase(mov) + "'>"
			+ "<td style='width:9%'>" + uenoEscapeHtml(mov.fecha_confirmacion || "") + "</td>"
			+ "<td style='width:9%'>" + uenoEscapeHtml(mov.fecha_transaccion || "") + "</td>"
			+ "<td style='width:14%'>" + uenoEscapeHtml(mov.nro_comprobante || "") + "</td>"
			+ "<td style='width:22%'>" + uenoEscapeHtml(mov.descripcion || "") + "</td>"
			+ "<td style='width:16%'>" + uenoEscapeHtml(mov.concepto || "") + "</td>"
			+ "<td style='width:10%;text-align:right'>" + uenoEscapeHtml(uenoFormatoMonto(mov.importe_debito || "0")) + "</td>"
			+ "<td style='width:10%;text-align:right'>" + uenoEscapeHtml(uenoFormatoMonto(mov.importe_credito || "0")) + "</td>"
			+ "<td style='width:10%;text-align:center'" + titleEstado + ">" + uenoEscapeHtml(estadoTexto) + "</td>"
			+ "</tr></table>";
	}
	if (movimientos.length > limitePreview) {
		html += "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr>"
			+ "<td style='width:100%;text-align:center'>Mostrando " + limitePreview + " de " + movimientos.length + " movimientos. Los totales incluyen todos los movimientos leidos.</td>"
			+ "</tr></table>";
	}
	document.getElementById("table_ueno_preview").innerHTML = html;
	document.getElementById("inptUenoTotalCreditoPreview").value = separadordemilesnumero(String(totalCreditos));
	document.getElementById("inptUenoTotalDebitoPreview").value = separadordemilesnumero(String(totalDebitos));
	uenoModernizarVista();
}

function uenoGuardarImportacion() {
	if (!uenoTienePermiso("IMPORTAREXTRACTOUENO")) {
		ver_vetana_informativa("NO TIENES PERMISO PARA IMPORTAR EXTRACTOS", "", "error");
		return;
	}
	if (uenoMovimientosPreview.length == 0) {
		ver_vetana_informativa("Primero selecciona y revisa un extracto de " + uenoBancoNombre());
		return;
	}
	if (uenoPreviewValidando) {
		ver_vetana_informativa("Espera a que termine la verificacion de movimientos ya importados");
		return;
	}
	if (!uenoPreviewListaParaImportar) {
		ver_vetana_informativa("La importacion permanece bloqueada hasta completar todas las validaciones del extracto", "", "error");
		return;
	}
	if (!/^[a-f0-9]{64}$/i.test(uenoHashArchivo)) {
		ver_vetana_informativa("No se pudo validar la huella SHA-256 del archivo", "Vuelve a seleccionar el extracto.", "error");
		return;
	}
	var resumenPreview = uenoContarPreviewPorEstado();
	if (resumenPreview.nuevos == 0 && resumenPreview.sin_verificar == 0) {
		ver_vetana_informativa("Todos los movimientos del extracto ya fueron importados. No se migrara ningun registro duplicado.", "", "error");
		return;
	}
	var cuenta = document.getElementById("inptUenoCuenta").value;
	if (cuenta == "") {
		ver_vetana_informativa("Falta detectar o completar la cuenta de " + uenoBancoNombre());
		return;
	}

	verCerrarEfectoCargando("1");
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "guardar_importacion");
	uenoAgregarBanco(datos);
	datos.append("cuenta", cuenta);
	datos.append("denominacion", document.getElementById("inptUenoDenominacion").value);
	datos.append("fecha_extracto", document.getElementById("inptUenoFechaExtracto").value);
	datos.append("periodo_desde", document.getElementById("inptUenoPeriodoDesde").value);
	datos.append("periodo_hasta", document.getElementById("inptUenoPeriodoHasta").value);
	datos.append("nombre_archivo_original", document.getElementById("inptUenoArchivo").value);
	datos.append("formato_archivo", uenoFormatoArchivo);
	datos.append("hash_archivo", uenoHashArchivo);
	datos.append("observacion", document.getElementById("txtUenoObservacionImportacion").value);
	datos.append("movimientos_json", JSON.stringify(uenoMovimientosPreview));
	datos.append("moneda_codigo", uenoMetadatosExtracto.moneda || "PYG");
	datos.append("tipo_cuenta", uenoMetadatosExtracto.tipo_cuenta || "");
	datos.append("saldo_anterior", uenoMetadatosExtracto.saldo_anterior === null ? "" : uenoMetadatosExtracto.saldo_anterior);
	datos.append("saldo_final", uenoMetadatosExtracto.saldo_final === null ? "" : uenoMetadatosExtracto.saldo_final);
	datos.append("total_creditos_declarado", uenoMetadatosExtracto.total_creditos_declarado === null ? "" : uenoMetadatosExtracto.total_creditos_declarado);
	datos.append("total_debitos_declarado", uenoMetadatosExtracto.total_debitos_declarado === null ? "" : uenoMetadatosExtracto.total_debitos_declarado);

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function(jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "uenoGuardarImportacion");
		},
		success: function(responseText) {
			verCerrarEfectoCargando("");
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] == "tablasfaltantes") {
					ver_vetana_informativa(datos["2"], "", "error");
					return;
				}
				if (datos["1"] != "exito") {
					ver_vetana_informativa(datos["2"] || "No se pudo importar el extracto", "", "error");
					return;
				}
				uenoMostrarResumenImportacion(datos);
				uenoMostrarResumenConciliacion(datos["conciliacion"]);
				uenoIdImportacionSeleccionada = datos["id_importacion"] || "";
				uenoLimpiarMovimientoTrabajo(false);
				uenoLimpiarAsignacionManual();
				document.getElementById("table_ueno_movimientos").innerHTML = datos["tabla"] || "";
				uenoModernizarVista();
				uenoBuscarImportaciones();
				uenoBuscarMovimientos(uenoIdImportacionSeleccionada || "");
				uenoBuscarPagosPendientes();
				uenoBuscarResumenTesoreria();
				uenoBuscarAuditoria();
			} catch (error) {
				ver_vetana_informativa("Error inesperado al importar " + uenoBancoNombre(), String(error), "error");
			}
		}
	});
}

function uenoMostrarResumenImportacion(datos) {
	var estado = "Extracto importado";
	if (datos["estado_importacion"] == "duplicado_archivo") {
		estado = "Archivo repetido";
	}
	if (datos["estado_importacion"] == "sin_movimientos_nuevos") {
		estado = "Sin movimientos nuevos";
	}
	var html = "<div class='ueno-import-summary'>"
		+ "<b>" + estado + "</b>"
		+ "<span>Leidos: " + (datos["movimientos_leidos"] || "0") + "</span>"
		+ "<span>Nuevos: " + (datos["movimientos_nuevos"] || "0") + "</span>"
		+ "<span>Duplicados: " + (datos["movimientos_duplicados"] || "0") + "</span>"
		+ "<span>Creditos: " + (datos["total_creditos"] || "0") + "</span>"
		+ "<span>Debitos: " + (datos["total_debitos"] || "0") + "</span>"
		+ "</div>";
	document.getElementById("divUenoResumenImportacion").innerHTML = html;
}

function uenoMostrarResumenConciliacion(resumen) {
	var contenedor = document.getElementById("divUenoResumenConciliacion");
	if (!contenedor) {
		return;
	}
	if (!resumen) {
		contenedor.innerHTML = "";
		return;
	}
	if (resumen["error"]) {
		contenedor.innerHTML = "<div class='ueno-conciliation-summary ueno-conciliation-summary--alert'>Conciliacion automatica: " + uenoEscapeHtml(resumen["error"]) + "</div>";
		return;
	}

	var monto = resumen["monto_conciliado"] || "0";
	if (typeof separadordemilesnumero === "function") {
		monto = separadordemilesnumero(String(monto));
	}

	contenedor.innerHTML = "<div class='ueno-conciliation-summary'>"
		+ "<b>Resultado automatico</b>"
		+ "<span>Procesados: " + uenoEscapeHtml(resumen["procesados"] || "0") + "</span>"
		+ "<span>Conciliados: " + uenoEscapeHtml(resumen["conciliados"] || "0") + "</span>"
		+ "<span>Observados: " + uenoEscapeHtml(resumen["observados"] || "0") + "</span>"
		+ "<span>Sin movimiento: " + uenoEscapeHtml(resumen["sin_movimiento"] || "0") + "</span>"
		+ "<span>Monto conciliado: " + uenoEscapeHtml(monto) + "</span>"
		+ "</div>";
}

function uenoFechaActualIso() {
	var hoy = new Date();
	var mes = String(hoy.getMonth() + 1);
	var dia = String(hoy.getDate());
	if (mes.length < 2) {
		mes = "0" + mes;
	}
	if (dia.length < 2) {
		dia = "0" + dia;
	}
	return hoy.getFullYear() + "-" + mes + "-" + dia;
}

function uenoPrepararFechasTesoreria() {
	var fechaOperativa = document.getElementById("inptUenoTesoreriaFechaOperativa");
	var fechaBancaria = document.getElementById("inptUenoTesoreriaFechaBancaria");
	if (!fechaOperativa || !fechaBancaria) {
		return;
	}
	var hoy = uenoFechaActualIso();
	if (fechaOperativa.value == "") {
		fechaOperativa.value = hoy;
	}
	if (fechaBancaria.value == "") {
		fechaBancaria.value = fechaOperativa.value;
	}
}

function uenoSetValorTesoreria(id, valor) {
	var elemento = document.getElementById(id);
	if (elemento) {
		elemento.value = valor == null ? "" : valor;
	}
}

function uenoSetTexto(id, valor) {
	var elemento = document.getElementById(id);
	if (elemento) {
		elemento.textContent = valor == null ? "" : valor;
	}
}

function uenoSetValorAuditoria(id, valor) {
	var elemento = document.getElementById(id);
	if (!elemento) {
		return;
	}
	if (typeof elemento.value !== "undefined") {
		elemento.value = valor == null ? "" : valor;
	} else {
		elemento.textContent = valor == null ? "" : valor;
	}
}

function uenoActualizarFiltrosRapidosMovimientos(resumen) {
	resumen = resumen || {};
	uenoSetTexto("lblUenoChipTodos", resumen.total_base || "0");
	uenoSetTexto("lblUenoChipDisponibles", resumen.disponibles || "0");
	uenoSetTexto("lblUenoChipParciales", resumen.parciales || "0");
	uenoSetTexto("lblUenoChipConciliados", resumen.conciliados || "0");
	uenoSetTexto("lblUenoChipConSaldo", resumen.con_saldo || "0");
	uenoSetTexto("lblUenoMovDisponibles", resumen.disponibles || "0");
	uenoSetTexto("lblUenoMovParciales", resumen.parciales || "0");
	uenoSetTexto("lblUenoMovConciliados", resumen.conciliados || "0");
	uenoSetTexto("lblUenoMovSaldoDisponible", resumen.saldo_disponible_fmt || "0");

	var contenedor = document.getElementById("divUenoMovFiltrosRapidos");
	if (!contenedor) {
		return;
	}
	var botones = contenedor.querySelectorAll("[data-ueno-mov-filter]");
	for (var i = 0; i < botones.length; i++) {
		var activo = botones[i].getAttribute("data-ueno-mov-filter") == uenoFiltroRapidoMovimientos;
		botones[i].classList.toggle("ueno-quick-filter--active", activo);
	}
}

function uenoCambiarFiltroRapidoMovimientos(filtro) {
	uenoFiltroRapidoMovimientos = filtro || "todos";
	uenoActualizarFiltrosRapidosMovimientos({});
	uenoBuscarMovimientos(uenoIdImportacionSeleccionada || "");
}

function uenoAbrirMesaTrabajoPopup() {
	var popup = document.getElementById("divUenoMesaTrabajoPopup");
	if (!popup) {
		return;
	}
	uenoMesaTrabajoModalAbierta = true;
	popup.style.display = "flex";
	uenoModernizarVista();
}

function uenoCerrarMesaTrabajoPopup() {
	uenoCerrarDetalleMesaTrabajo();
	var popup = document.getElementById("divUenoMesaTrabajoPopup");
	if (popup) {
		popup.style.display = "none";
	}
	uenoMesaTrabajoModalAbierta = false;
}

function uenoVerAplicacionMovimiento(idMovimiento) {
	if (!idMovimiento) {
		ver_vetana_informativa("No se pudo identificar el movimiento seleccionado.", "", "error");
		return;
	}
	var overlay = document.getElementById("divUenoDetalleMesaOverlay");
	if (uenoMesaTrabajoEstaAbierta() && overlay) {
		uenoAbrirDetalleMesaTrabajo();
		uenoCargarTrazabilidadDetalleMesa(idMovimiento);
		return;
	}
	uenoMostrarAuditoriaMovimientoPopup(idMovimiento);
}

function uenoCargarTrazabilidadDetalleMesa(idMovimiento) {
	if (!uenoTienePermiso("VERAUDITORIAUENO")) {
		ver_vetana_informativa("NO TIENES PERMISO PARA VER AUDITORIA UENO", "", "error");
		return;
	}
	var tabla = document.getElementById("table_ueno_auditoria_movimiento_mesa");
	if (!tabla) { return; }
	uenoAuditoriaMovimientoActual = String(idMovimiento || "");
	uenoSetTexto("lblUenoDetalleMesaMovimiento", uenoAuditoriaMovimientoActual);
	uenoSetTexto("lblUenoDetalleMesaTotal", "0");
	tabla.innerHTML = "<div class='ueno-loading-inline'>Cargando trazabilidad...</div>";
	uenoMostrarVistaDetalleMesa("trazabilidad");
	uenoCargarAuditoria({
		tablaId: "table_ueno_auditoria_movimiento_mesa",
		totalId: "lblUenoDetalleMesaTotal",
		accion: uenoAuditoriaMovimientoActual,
		mostrarErrores: true
	});
}

function uenoMostrarAuditoriaMovimientoPopup(idMovimiento) {
	if (!uenoTienePermiso("VERAUDITORIAUENO")) {
		ver_vetana_informativa("NO TIENES PERMISO PARA VER AUDITORIA UENO", "", "error");
		return;
	}
	var popup = document.getElementById("divUenoAuditoriaMovimientoPopup");
	var tabla = document.getElementById("table_ueno_auditoria_movimiento");
	if (!popup || !tabla) {
		var filtro = document.getElementById("inptUenoAuditAccion");
		if (filtro) {
			filtro.value = idMovimiento || "";
		}
		uenoBuscarAuditoria();
		return;
	}
	uenoAuditoriaMovimientoActual = String(idMovimiento || "");
	uenoSetTexto("lblUenoAuditPopupMovimiento", uenoAuditoriaMovimientoActual);
	uenoSetTexto("lblUenoAuditPopupTotal", "0");
	uenoSetTexto("lblUenoAuditPopupContexto", "Trazabilidad del movimiento seleccionado.");
	tabla.innerHTML = "<div class='ueno-loading-inline'>Cargando trazabilidad...</div>";
	popup.style.display = "flex";
	uenoCargarAuditoria({
		tablaId: "table_ueno_auditoria_movimiento",
		totalId: "lblUenoAuditPopupTotal",
		accion: uenoAuditoriaMovimientoActual,
		mostrarErrores: true
	});
}

function uenoCerrarAuditoriaMovimientoPopup() {
	var popup = document.getElementById("divUenoAuditoriaMovimientoPopup");
	if (popup) {
		popup.style.display = "none";
	}
	uenoAuditoriaMovimientoActual = "";
}

function uenoNumeroTesoreria(valor) {
	return Number(String(valor || "0").replace(/\./g, "").replace(",", ".")) || 0;
}

function uenoMostrarEstadoTesoreria(datos) {
	var contenedor = document.getElementById("divUenoTesoreriaEstado");
	if (!contenedor) {
		return;
	}
	var diferencia = Math.abs(uenoNumeroTesoreria(datos["diferencia"]));
	var pendientes = Number(datos["cierres_pendientes"] || 0);
	var observados = Number(datos["cierres_observacion"] || 0);
	var color = "#159447";
	var texto = "Tesoreria sin alertas";
	if (pendientes > 0 || observados > 0 || diferencia > 0) {
		color = "#c0392b";
		texto = "Tesoreria con pendientes o diferencias";
	}
	contenedor.innerHTML = "<div class='ueno-tesoreria-status' style='border-left-color:" + color + "'>"
		+ "<b>" + uenoEscapeHtml(texto) + "</b>"
		+ "<span>Cierre: " + uenoEscapeHtml(datos["fecha_operativa"] || "") + "</span>"
		+ "<span>Banco: " + uenoEscapeHtml(datos["fecha_bancaria"] || "") + "</span>"
		+ "</div>";
}

function uenoBuscarResumenTesoreria() {
	if (!document.getElementById("table_ueno_resumen_tesoreria")) {
		return;
	}
	uenoPrepararFechasTesoreria();
	obtener_datos_user();
	var datos = new FormData();
	var filtroVersion = uenoFiltroBancoVersion;
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "buscar_resumen_tesoreria");
	uenoAgregarFiltroBanco(datos);
	datos.append("fecha_operativa", document.getElementById("inptUenoTesoreriaFechaOperativa").value);
	datos.append("fecha_bancaria", document.getElementById("inptUenoTesoreriaFechaBancaria").value);
	datos.append("local", document.getElementById("inptUenoTesoreriaLocal").value);

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			if (filtroVersion != uenoFiltroBancoVersion) { return; }
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] == "NI") {
					ver_vetana_informativa(datos["2"] || "NO TIENES PERMISO", "", "error");
					return;
				}
				if (datos["1"] == "tablasfaltantes") {
					ver_vetana_informativa(datos["2"], "", "error");
					return;
				}
				if (datos["1"] != "exito") {
					ver_vetana_informativa(datos["2"] || "No se pudo cargar la tesoreria bancaria", "", "error");
					return;
				}
				document.getElementById("table_ueno_resumen_tesoreria").innerHTML = datos["2"] || "";
				uenoSetValorTesoreria("inptUenoTesCierresEsperados", datos["cierres_esperados"]);
				uenoSetValorTesoreria("inptUenoTesCierresRealizados", datos["cierres_realizados"]);
				uenoSetValorTesoreria("inptUenoTesCierresPendientes", datos["cierres_pendientes"]);
				uenoSetValorTesoreria("inptUenoTesCierresObservacion", datos["cierres_observacion"]);
				uenoSetValorTesoreria("inptUenoTesTotalUeno", datos["total_ueno"]);
				uenoSetValorTesoreria("inptUenoTesTotalGV", datos["total_gv"]);
				uenoSetValorTesoreria("inptUenoTesMigracionInterna", datos["total_migracion_interna"]);
				uenoSetValorTesoreria("inptUenoTesDiferencia", datos["diferencia"]);
				uenoSetValorTesoreria("inptUenoTesConciliado", datos["total_conciliado"]);
				uenoSetValorTesoreria("inptUenoTesPendiente", datos["total_pendiente"]);
				uenoSetValorTesoreria("inptUenoTesObservado", datos["total_observado"]);
				uenoSetValorTesoreria("inptUenoTesSinComprobante", datos["total_sin_comprobante"]);
				uenoSetValorTesoreria("inptUenoTesDisponible", datos["ueno_disponible"]);
				uenoSetValorTesoreria("inptUenoTesSinAplicar", datos["ueno_sin_aplicar"]);
				uenoMostrarEstadoTesoreria(datos);
				uenoModernizarVista();
			} catch (error) {
				ver_vetana_informativa("Error inesperado al cargar la tesoreria bancaria", String(error), "error");
			}
		}
	});
}

function uenoCargarAuditoria(opciones) {
	opciones = opciones || {};
	var tablaId = opciones.tablaId || "table_ueno_auditoria";
	var totalId = opciones.totalId || "inptUenoAuditTotal";
	var tabla = document.getElementById(tablaId);
	if (!tabla) {
		return;
	}
	if (!uenoTienePermiso("VERAUDITORIAUENO")) {
		return;
	}

	var fechaDesde = typeof opciones.fecha_desde !== "undefined"
		? opciones.fecha_desde
		: (document.getElementById("inptUenoAuditDesde") ? document.getElementById("inptUenoAuditDesde").value : "");
	var fechaHasta = typeof opciones.fecha_hasta !== "undefined"
		? opciones.fecha_hasta
		: (document.getElementById("inptUenoAuditHasta") ? document.getElementById("inptUenoAuditHasta").value : "");
	var accion = typeof opciones.accion !== "undefined"
		? opciones.accion
		: (document.getElementById("inptUenoAuditAccion") ? document.getElementById("inptUenoAuditAccion").value : "");
	var estado = typeof opciones.estado !== "undefined"
		? opciones.estado
		: (document.getElementById("inptUenoAuditEstado") ? document.getElementById("inptUenoAuditEstado").value : "todos");

	obtener_datos_user();
	var datos = new FormData();
	var filtroVersion = uenoFiltroBancoVersion;
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "buscar_auditoria");
	uenoAgregarFiltroBanco(datos);
	datos.append("fecha_desde", fechaDesde || "");
	datos.append("fecha_hasta", fechaHasta || "");
	datos.append("accion", accion || "");
	datos.append("estado", estado || "todos");

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			if (filtroVersion != uenoFiltroBancoVersion) { return; }
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "tablasfaltantes") {
					tabla.innerHTML = "";
					uenoSetValorAuditoria(totalId, "0");
					if (opciones.mostrarErrores) {
						ver_vetana_informativa(respuesta["2"] || "Falta configurar la auditoria bancaria", "", "error");
					}
					uenoModernizarTablaAuditoria(tablaId);
					return;
				}
				if (respuesta["1"] == "exito") {
					tabla.innerHTML = respuesta["2"] || "";
					uenoSetValorAuditoria(totalId, respuesta["3"] || "0");
					uenoModernizarTablaAuditoria(tablaId);
					return;
				}
				tabla.innerHTML = "<div class='ueno-empty-message'>No se pudo cargar la trazabilidad.</div>";
				uenoSetValorAuditoria(totalId, "0");
				if (opciones.mostrarErrores) {
					ver_vetana_informativa(respuesta["2"] || "No se pudo cargar la auditoria bancaria", "", "error");
				}
			} catch (error) {
				tabla.innerHTML = "<div class='ueno-empty-message'>No se pudo interpretar la respuesta de auditoria.</div>";
				uenoSetValorAuditoria(totalId, "0");
				if (opciones.mostrarErrores) {
					ver_vetana_informativa("Error inesperado al cargar la auditoria bancaria", String(error), "error");
				}
			}
		},
		error: function() {
			if (filtroVersion != uenoFiltroBancoVersion) { return; }
			tabla.innerHTML = "<div class='ueno-empty-message'>No se pudo conectar con auditoria Ueno.</div>";
			uenoSetValorAuditoria(totalId, "0");
			if (opciones.mostrarErrores) {
				ver_vetana_informativa("No se pudo conectar con la auditoria bancaria", "", "error");
			}
		}
	});
}

function uenoBuscarAuditoria() {
	uenoCargarAuditoria({
		tablaId: "table_ueno_auditoria",
		totalId: "inptUenoAuditTotal"
	});
}

function uenoOpcionesImportacionesModal() {
	return {
		tablaId: "table_ueno_importaciones_modal",
		totalId: "lblUenoModalTotalImportaciones",
		desdeId: "inptUenoModalBuscarDesde",
		hastaId: "inptUenoModalBuscarHasta",
		comprobanteId: "inptUenoModalBuscarComprobante",
		montoId: "inptUenoModalBuscarMonto",
		vista: "modal",
		mostrarErrores: true
	};
}

function uenoAbrirModalImportaciones() {
	var popup = document.getElementById("divUenoImportacionesPopup");
	if (!popup) {
		uenoBuscarImportaciones();
		return;
	}
	uenoImportacionesModalAbierto = true;
	popup.style.display = "flex";
	var desdeModal = document.getElementById("inptUenoModalBuscarDesde");
	var hastaModal = document.getElementById("inptUenoModalBuscarHasta");
	var desdePrincipal = document.getElementById("inptUenoBuscarDesde");
	var hastaPrincipal = document.getElementById("inptUenoBuscarHasta");
	if (desdeModal && desdePrincipal && desdeModal.value == "") {
		desdeModal.value = desdePrincipal.value;
	}
	if (hastaModal && hastaPrincipal && hastaModal.value == "") {
		hastaModal.value = hastaPrincipal.value;
	}
	uenoBuscarImportaciones(uenoOpcionesImportacionesModal());
}

function uenoCerrarModalImportaciones() {
	var popup = document.getElementById("divUenoImportacionesPopup");
	if (popup) {
		popup.style.display = "none";
	}
	uenoImportacionesModalAbierto = false;
}

function uenoBuscarImportacionesModal() {
	uenoBuscarImportaciones(uenoOpcionesImportacionesModal());
}

function uenoVerDetalleImportacionDesdeImportacion(idImportacion) {
	var btnCargar = document.getElementById("btnUenoDetalleCargarEnMesa");
	if (btnCargar) {
		btnCargar.style.display = "";
	}
	var comprobanteOrigen = document.getElementById("inptUenoModalBuscarComprobante");
	var montoOrigen = document.getElementById("inptUenoModalBuscarMonto");
	var comprobanteDestino = document.getElementById("inptUenoDetalleFiltroComprobante");
	var montoDestino = document.getElementById("inptUenoDetalleFiltroMonto");
	if (comprobanteDestino && comprobanteOrigen) {
		comprobanteDestino.value = comprobanteOrigen.value;
	}
	if (montoDestino && montoOrigen) {
		montoDestino.value = montoOrigen.value;
	}
	uenoVerDetalleImportacion(idImportacion, true);
}

function uenoVerDetalleImportacionDesdeAuditoria(idImportacion) {
	var btnCargar = document.getElementById("btnUenoDetalleCargarEnMesa");
	if (btnCargar) {
		btnCargar.style.display = "none";
	}
	uenoVerDetalleImportacion(idImportacion, false);
}

function uenoResumenDetalleImportacion(importacion) {
	importacion = importacion || {};
	var bloques = [
		["Banco", importacion["banco_nombre"] || uenoBancoNombre(importacion["banco_codigo"])],
		["Archivo", importacion["archivo"] || ""],
		["Cuenta", importacion["cuenta"] || ""],
		["Titular", importacion["denominacion"] || ""],
		["Fecha extracto", importacion["fecha_extracto"] || ""],
		["Periodo", (importacion["periodo_desde"] || "-") + " a " + (importacion["periodo_hasta"] || "-")],
		["Importado", importacion["fecha_importacion"] || ""],
		["Movimientos", importacion["movimientos"] || "0"],
		["Creditos", (importacion["creditos"] || "0") + " / " + (importacion["total_creditos"] || "0")],
		["Debitos", (importacion["debitos"] || "0") + " / " + (importacion["total_debitos"] || "0")],
		["Estado", importacion["estado"] || ""],
		["Usuario", importacion["usuario"] || ""],
		["Moneda", importacion["moneda_codigo"] || "PYG"],
		["Saldo anterior", importacion["saldo_anterior"] || "-"],
		["Saldo final", importacion["saldo_final"] || "-"],
		["Huella", importacion["hash_archivo"] || ""]
	];
	var html = "";
	for (var i = 0; i < bloques.length; i++) {
		html += "<span><b>" + uenoEscapeHtml(bloques[i][0]) + "</b><strong>" + uenoEscapeHtml(bloques[i][1]) + "</strong></span>";
	}
	if (importacion["observacion"]) {
		html += "<span class='ueno-detalle-importacion-wide'><b>Observacion</b><strong>" + uenoEscapeHtml(importacion["observacion"]) + "</strong></span>";
	}
	return html;
}

function uenoValorCampo(idCampo) {
	var campo = document.getElementById(idCampo);
	return campo ? campo.value : "";
}

function uenoLimpiarCamposDetalleImportacion() {
	var campos = ["inptUenoDetalleFiltroComprobante", "inptUenoDetalleFiltroMonto"];
	for (var i = 0; i < campos.length; i++) {
		var campo = document.getElementById(campos[i]);
		if (campo) {
			campo.value = "";
		}
	}
}

function uenoBuscarDetalleImportacion() {
	if (!uenoDetalleImportacionActual) {
		ver_vetana_informativa("Primero selecciona un archivo migrado.", "", "error");
		return;
	}
	uenoVerDetalleImportacion(uenoDetalleImportacionActual, true);
}

function uenoLimpiarFiltrosDetalleImportacion() {
	uenoLimpiarCamposDetalleImportacion();
	if (uenoDetalleImportacionActual) {
		uenoBuscarDetalleImportacion();
	}
}

function uenoVerDetalleImportacion(idImportacion, conservarFiltros) {
	if (!idImportacion) {
		ver_vetana_informativa("No se pudo identificar el archivo migrado.", "", "error");
		return;
	}
	if (!conservarFiltros) {
		uenoLimpiarCamposDetalleImportacion();
	}
	uenoDetalleImportacionActual = String(idImportacion);
	uenoMarcarImportacionSeleccionada(idImportacion);
	var popup = document.getElementById("divUenoDetalleImportacionPopup");
	var resumen = document.getElementById("divUenoDetalleImportacionResumen");
	var tabla = document.getElementById("table_ueno_detalle_importacion");
	var totalFiltrado = document.getElementById("lblUenoDetalleTotalFiltrado");
	if (!popup || !tabla) {
		uenoSeleccionarImportacion(idImportacion);
		return;
	}
	if (resumen) {
		resumen.innerHTML = "<div class='ueno-loading-inline'>Cargando detalle del archivo...</div>";
	}
	if (totalFiltrado) {
		totalFiltrado.textContent = "0";
	}
	tabla.innerHTML = "";
	popup.style.display = "flex";

	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "detalle_importacion");
	datos.append("id_importacion", idImportacion);
	datos.append("nro_comprobante", uenoValorCampo("inptUenoDetalleFiltroComprobante"));
	datos.append("monto", uenoValorCampo("inptUenoDetalleFiltroMonto"));

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] != "exito") {
					if (resumen) {
						resumen.innerHTML = "<div class='ueno-empty-message'>No se pudo cargar el detalle del archivo migrado.</div>";
					}
					ver_vetana_informativa(respuesta["2"] || "No se pudo cargar el detalle del archivo", "", "error");
					return;
				}
				var importacion = respuesta["importacion"] || {};
				if (resumen) {
					resumen.innerHTML = uenoResumenDetalleImportacion(importacion);
				}
				tabla.innerHTML = respuesta["tabla"] || "";
				if (totalFiltrado) {
					totalFiltrado.textContent = respuesta["total_movimientos"] || "0";
				}
				var subtitulo = document.getElementById("lblUenoDetalleImportacionSubtitulo");
				if (subtitulo) {
					subtitulo.textContent = "Movimientos migrados: " + (respuesta["total_movimientos"] || "0");
				}
				uenoModernizarTabla("table_ueno_detalle_importacion", ["Nro.", "F. conf.", "F. trans.", "Comprobante", "Detalle", "Deb.", "Cred.", "Disp.", "Duplicado", "Estado"], 9);
			} catch (error) {
				if (resumen) {
					resumen.innerHTML = "<div class='ueno-empty-message'>No se pudo interpretar el detalle del archivo.</div>";
				}
				ver_vetana_informativa("Error inesperado al cargar detalle del archivo", String(error), "error");
			}
		},
		error: function(jqXHR, textstatus) {
			if (resumen) {
				resumen.innerHTML = "<div class='ueno-empty-message'>No se pudo conectar con detalle del archivo.</div>";
			}
			ver_vetana_informativa("No se pudo cargar detalle del archivo: " + textstatus, "", "error");
		}
	});
}

function uenoCerrarDetalleImportacionPopup() {
	var popup = document.getElementById("divUenoDetalleImportacionPopup");
	if (popup) {
		popup.style.display = "none";
	}
}

function uenoCargarImportacionDesdeDetalle() {
	if (!uenoDetalleImportacionActual) {
		ver_vetana_informativa("Primero selecciona un archivo migrado.", "", "error");
		return;
	}
	uenoIdImportacionSeleccionada = uenoDetalleImportacionActual;
	uenoMarcarImportacionSeleccionada(uenoDetalleImportacionActual);
	uenoBuscarMovimientos(uenoDetalleImportacionActual);
	uenoCerrarDetalleImportacionPopup();
	uenoCerrarModalImportaciones();
}

function uenoBuscarImportaciones(opciones) {
	opciones = opciones || {};
	var tablaId = opciones.tablaId || "table_ueno_importaciones";
	var totalId = opciones.totalId || "inptUenoTotalImportaciones";
	var desdeId = opciones.desdeId || "inptUenoBuscarDesde";
	var hastaId = opciones.hastaId || "inptUenoBuscarHasta";
	var tabla = document.getElementById(tablaId);
	if (tabla) {
		tabla.innerHTML = "<div class='ueno-loading-inline'>Cargando archivos migrados...</div>";
	}
	uenoSetValorAuditoria(totalId, "0");
	obtener_datos_user();
	var datos = new FormData();
	var filtroVersion = uenoFiltroBancoVersion;
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "buscar_importaciones");
	uenoAgregarFiltroBanco(datos);
	datos.append("fecha_desde", document.getElementById(desdeId) ? document.getElementById(desdeId).value : "");
	datos.append("fecha_hasta", document.getElementById(hastaId) ? document.getElementById(hastaId).value : "");
	datos.append("nro_comprobante", uenoValorCampo(opciones.comprobanteId || ""));
	datos.append("monto", uenoValorCampo(opciones.montoId || ""));
	datos.append("vista", opciones.vista || "");

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			if (filtroVersion != uenoFiltroBancoVersion) { return; }
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					if (tabla) {
						tabla.innerHTML = respuesta["2"] || "";
					}
					uenoSetValorAuditoria(totalId, respuesta["3"] || "0");
					var etiquetas = opciones.vista == "modal"
						? ["Banco", "ID", "Cuenta", "Fecha", "Archivo", "Importado", "Mov.", "Cred.", "Deb.", "Estado", "Encontrado"]
						: ["Banco", "ID", "Cuenta", "Fecha", "Archivo", "Importado", "Mov.", "Cred.", "Deb.", "Estado"];
					uenoModernizarTabla(tablaId, etiquetas, 9);
					uenoMarcarImportacionSeleccionada(uenoIdImportacionSeleccionada);
					return;
				}
				if (tabla) {
					tabla.innerHTML = "<div class='ueno-empty-message'>No se pudo cargar el listado de archivos migrados.</div>";
				}
				if (opciones.mostrarErrores) {
					ver_vetana_informativa(respuesta["2"] || "No se pudo cargar archivos migrados", "", "error");
				}
			} catch (error) {
				if (tabla) {
					tabla.innerHTML = "<div class='ueno-empty-message'>No se pudo interpretar el listado de archivos migrados.</div>";
				}
				if (opciones.mostrarErrores) {
					ver_vetana_informativa("Error inesperado al cargar archivos migrados", String(error), "error");
				}
			}
		},
		error: function(jqXHR, textstatus) {
			if (filtroVersion != uenoFiltroBancoVersion) { return; }
			if (tabla) {
				tabla.innerHTML = "<div class='ueno-empty-message'>No se pudo conectar con archivos migrados.</div>";
			}
			if (opciones.mostrarErrores) {
				ver_vetana_informativa("No se pudo cargar archivos migrados: " + textstatus, "", "error");
			}
		}
	});
}

function uenoSeleccionarImportacion(idImportacion) {
	uenoIdImportacionSeleccionada = idImportacion;
	uenoMarcarImportacionSeleccionada(idImportacion);
	uenoBuscarMovimientos(idImportacion);
	if (uenoImportacionesModalAbierto) {
		uenoCerrarModalImportaciones();
	}
}

function uenoBuscarMovimientos(idImportacion) {
	obtener_datos_user();
	var datos = new FormData();
	var filtroVersion = uenoFiltroBancoVersion;
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "buscar_movimientos");
	uenoAgregarFiltroBanco(datos);
	datos.append("id_importacion", idImportacion || "");
	datos.append("fecha_desde", document.getElementById("inptUenoMovDesde") ? document.getElementById("inptUenoMovDesde").value : "");
	datos.append("fecha_hasta", document.getElementById("inptUenoMovHasta") ? document.getElementById("inptUenoMovHasta").value : "");
	datos.append("nro_comprobante", document.getElementById("inptUenoMovComprobante") ? document.getElementById("inptUenoMovComprobante").value : "");
	datos.append("monto", uenoValorCampo("inptUenoMovMonto"));
	datos.append("estado", document.getElementById("inptUenoMovEstado") ? document.getElementById("inptUenoMovEstado").value : "");
	datos.append("filtro_rapido", uenoFiltroRapidoMovimientos || "todos");
	datos.append("origen_probable", document.getElementById("inptUenoMovOrigen") ? document.getElementById("inptUenoMovOrigen").value : "todos");

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			if (filtroVersion != uenoFiltroBancoVersion) { return; }
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] == "exito") {
					document.getElementById("table_ueno_movimientos").innerHTML = datos["2"];
					document.getElementById("inptUenoTotalMovimientos").value = datos["3"];
					document.getElementById("inptUenoTotalCreditos").value = datos["4"];
					document.getElementById("inptUenoTotalDebitos").value = datos["5"];
					uenoModernizarVista();
					uenoActualizarFiltrosRapidosMovimientos(datos["resumen"] || {});
				}
			} catch (error) {}
		}
	});
}

function uenoBuscarPagosPendientes() {
	if (!document.getElementById("table_ueno_pagos_pendientes")) {
		return;
	}
	uenoLimpiarCuotaGoodVenta();
	obtener_datos_user();
	var datos = new FormData();
	var filtroVersion = uenoFiltroBancoVersion;
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "buscar_pagos_pendientes");
	uenoAgregarFiltroBanco(datos);
	datos.append("estado", document.getElementById("inptUenoPagoEstado") ? document.getElementById("inptUenoPagoEstado").value : "");
	datos.append("nro_comprobante", document.getElementById("inptUenoPagoComprobante") ? document.getElementById("inptUenoPagoComprobante").value : "");
	datos.append("cliente", document.getElementById("inptUenoBuscarCuotaCliente") ? document.getElementById("inptUenoBuscarCuotaCliente").value : "");
	datos.append("venta", document.getElementById("inptUenoBuscarCuotaVenta") ? document.getElementById("inptUenoBuscarCuotaVenta").value : "");
	datos.append("monto", document.getElementById("inptUenoBuscarCuotaMonto") ? document.getElementById("inptUenoBuscarCuotaMonto").value : "");
	datos.append("monto_referencia", uenoMovimientoTrabajo ? (uenoMovimientoTrabajo["monto_disponible"] || uenoMovimientoTrabajo["monto_disponible_fmt"] || "") : "");

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			if (filtroVersion != uenoFiltroBancoVersion) { return; }
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] == "exito") {
					document.getElementById("table_ueno_pagos_pendientes").innerHTML = datos["2"];
					document.getElementById("inptUenoTotalPagosPendientes").value = datos["3"];
					document.getElementById("inptUenoMontoPagosPendientes").value = datos["4"];
					uenoModernizarVista();
					uenoMarcarCuotaGoodVentaSeleccionada(uenoIdConciliacionManual);
				}
			} catch (error) {}
		}
	});
}

function uenoBuscarCoincidenciasCuota() {
	if (uenoMesaSoloConsulta) {
		return uenoAvisarMesaSoloConsulta();
	}
	if (!uenoMovimientoTrabajo || !uenoMovimientoTrabajo["id_movimiento"]) {
		ver_vetana_informativa("Primero selecciona un movimiento bancario con credito disponible");
		return;
	}
	var comprobante = document.getElementById("inptUenoPagoComprobante");
	if (comprobante) {
		comprobante.value = uenoMovimientoTrabajo["nro_comprobante"] || "";
	}
	var monto = document.getElementById("inptUenoBuscarCuotaMonto");
	if (monto) {
		monto.value = "";
	}
	var estado = document.getElementById("inptUenoPagoEstado");
	if (estado) {
		estado.value = "pendiente_conciliacion";
	}
	uenoBuscarPagosPendientes();
}

function uenoLimpiarBusquedaCuotas() {
	uenoSetValorTesoreria("inptUenoBuscarCuotaCliente", "");
	uenoSetValorTesoreria("inptUenoBuscarCuotaVenta", "");
	uenoSetValorTesoreria("inptUenoBuscarCuotaMonto", "");
	uenoSetValorTesoreria("inptUenoPagoComprobante", "");
	var estado = document.getElementById("inptUenoPagoEstado");
	if (estado) {
		estado.value = "pendiente_conciliacion";
	}
	uenoSetValorTesoreria("inptUenoTotalPagosPendientes", "");
	uenoSetValorTesoreria("inptUenoMontoPagosPendientes", "");
	uenoLimpiarCuotaGoodVenta();
	var tabla = document.getElementById("table_ueno_pagos_pendientes");
	if (tabla) {
		tabla.innerHTML = "";
	}
	uenoModernizarVista();
}

function uenoLimpiarAsignacionManual() {
	uenoIdConciliacionManual = "";
	uenoLimpiarCuotaGoodVenta();
	uenoSetValorTesoreria("inptUenoManualIdPago", "");
	uenoSetValorTesoreria("inptUenoManualFactura", "");
	uenoSetValorTesoreria("inptUenoManualComprobante", "");
	uenoSetValorTesoreria("inptUenoManualMonto", "");
	uenoSetValorTesoreria("inptUenoManualEstado", "");
	uenoSetValorTesoreria("inptUenoManualTotalCandidatos", "");
	uenoSetValorTesoreria("inptUenoManualCliente", "");
	uenoSetValorTesoreria("inptUenoManualCobrador", "");
	var observacion = document.getElementById("txtUenoManualObservacion");
	if (observacion) {
		observacion.value = "";
	}
	var tabla = document.getElementById("table_ueno_candidatos_manual");
	if (tabla) {
		tabla.innerHTML = "";
		uenoModernizarVista();
	}
}

function uenoCargarPagoManual(pago) {
	uenoSetValorTesoreria("inptUenoManualIdPago", pago["id"] || "");
	uenoSetValorTesoreria("inptUenoManualFactura", pago["factura"] || "");
	uenoSetValorTesoreria("inptUenoManualComprobante", pago["comprobante"] || "");
	uenoSetValorTesoreria("inptUenoManualMonto", pago["monto"] || "");
	uenoSetValorTesoreria("inptUenoManualEstado", pago["estado"] || "");
	uenoSetValorTesoreria("inptUenoManualCliente", pago["cliente"] || "");
	uenoSetValorTesoreria("inptUenoManualCobrador", pago["cobrador"] || "");
}

function uenoSeleccionarPagoManual(idConciliacion) {
	uenoIdConciliacionManual = idConciliacion;
	uenoBuscarCandidatosManual();
}

function uenoBuscarCandidatosManual() {
	if (!document.getElementById("table_ueno_candidatos_manual")) {
		return;
	}
	if (!uenoTienePermiso("VERASIGNACIONMANUALUENO") && !uenoTienePermiso("ASIGNARMANUALUENO")) {
		ver_vetana_informativa("NO TIENES PERMISO PARA VER ASIGNACION MANUAL UENO", "", "error");
		return;
	}
	if (uenoIdConciliacionManual == "") {
		ver_vetana_informativa("Selecciona un pago pendiente u observado para asignacion manual");
		return;
	}

	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "buscar_candidatos_manual");
	datos.append("id_conciliacion", uenoIdConciliacionManual);

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] == "NI") {
					ver_vetana_informativa(datos["2"] || "NO TIENES PERMISO", "", "error");
					return;
				}
				if (datos["1"] == "tablasfaltantes") {
					ver_vetana_informativa(datos["2"], "", "error");
					return;
				}
				if (datos["1"] != "exito") {
					ver_vetana_informativa(datos["2"] || "No se pudo buscar candidatos Ueno", "", "error");
					return;
				}
				uenoCargarPagoManual(datos["pago"] || {});
				document.getElementById("table_ueno_candidatos_manual").innerHTML = datos["tabla"] || "";
				uenoSetValorTesoreria("inptUenoManualTotalCandidatos", datos["total"] || "0");
				uenoModernizarVista();
			} catch (error) {
				ver_vetana_informativa("Error inesperado al buscar candidatos Ueno", String(error), "error");
			}
		}
	});
}

function uenoAsignarMovimientoManual(idMovimiento) {
	if (uenoMesaSoloConsulta) {
		return uenoAvisarMesaSoloConsulta();
	}
	if (!uenoTienePermiso("ASIGNARMANUALUENO")) {
		ver_vetana_informativa("NO TIENES PERMISO PARA ASIGNAR MOVIMIENTOS UENO", "", "error");
		return;
	}
	if (uenoIdConciliacionManual == "") {
		ver_vetana_informativa("Primero selecciona una cuota de Telar");
		return;
	}
	var montoAplicar = document.getElementById("inptUenoMontoAplicarCuota") ? document.getElementById("inptUenoMontoAplicarCuota").value : "";
	if (!confirm("Confirmas aplicar este credito Ueno a la cuota Telar seleccionada?")) {
		return;
	}

	verCerrarEfectoCargando("1");
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "asignar_movimiento_manual");
	datos.append("id_conciliacion", uenoIdConciliacionManual);
	datos.append("id_movimiento", idMovimiento);
	datos.append("monto_aplicar", montoAplicar);
	datos.append("observacion", document.getElementById("txtUenoManualObservacion") ? document.getElementById("txtUenoManualObservacion").value : "");

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function(jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "uenoAsignarMovimientoManual");
		},
		success: function(responseText) {
			verCerrarEfectoCargando("");
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] == "NI") {
					ver_vetana_informativa(datos["2"] || "NO TIENES PERMISO", "", "error");
					return;
				}
				if (datos["1"] == "tablasfaltantes") {
					ver_vetana_informativa(datos["2"], "", "error");
					return;
				}
				if (datos["1"] != "exito") {
					ver_vetana_informativa(datos["2"] || "No se pudo asignar el movimiento bancario", "", "error");
					return;
				}
				ver_vetana_informativa(datos["2"] || "Pago asignado manualmente");
				uenoLimpiarMovimientoTrabajo(false);
				uenoLimpiarAsignacionManual();
				uenoBuscarMovimientos(uenoIdImportacionSeleccionada || "");
				uenoBuscarPagosPendientes();
				uenoBuscarResumenTesoreria();
				uenoBuscarAuditoria();
			} catch (error) {
				ver_vetana_informativa("Error inesperado al asignar movimiento bancario", String(error), "error");
			}
		}
	});
}

function uenoConciliarAutomaticamente() {
	if (uenoMesaSoloConsulta) {
		return uenoAvisarMesaSoloConsulta();
	}
	if (!uenoTienePermiso("CONCILIARPAGOSUENO")) {
		ver_vetana_informativa("NO TIENES PERMISO PARA CONCILIAR PAGOS UENO", "", "error");
		return;
	}
	if (!confirm("Ejecutar conciliacion automatica con movimientos Ueno importados?")) {
		return;
	}

	verCerrarEfectoCargando("1");
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "conciliar_automaticamente");
	datos.append("id_importacion", uenoIdImportacionSeleccionada || "");

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function(jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "uenoConciliarAutomaticamente");
		},
		success: function(responseText) {
			verCerrarEfectoCargando("");
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] == "NI") {
					ver_vetana_informativa(datos["2"] || "NO TIENES PERMISO", "", "error");
					return;
				}
				if (datos["1"] == "tablasfaltantes") {
					ver_vetana_informativa(datos["2"], "", "error");
					return;
				}
				if (datos["1"] != "exito") {
					ver_vetana_informativa(datos["2"] || "No se pudo conciliar automaticamente", "", "error");
					return;
				}
				uenoMostrarResumenConciliacion(datos["2"]);
				uenoBuscarMovimientos(uenoIdImportacionSeleccionada || "");
				uenoBuscarPagosPendientes();
				uenoBuscarResumenTesoreria();
				uenoBuscarAuditoria();
			} catch (error) {
				ver_vetana_informativa("Error inesperado al conciliar Ueno", String(error), "error");
			}
		}
	});
}

document.addEventListener("DOMContentLoaded", function() {
	var input = document.getElementById("uenoArchivoExtracto");
	if (input) {
		input.addEventListener("change", uenoProcesarArchivo);
	}
	uenoActualizarContextoBanco();
	uenoHabilitarBotonImportar(false);
	uenoPrepararColapsables(false);
	uenoModernizarVista();
});
