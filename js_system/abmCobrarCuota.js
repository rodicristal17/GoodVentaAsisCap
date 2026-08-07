var cobrarCuotaSeleccionada = null;
var cobrarCuotaSeleccionadas = [];
var cobrarCuotaClienteSeleccionado = null;
var cobrarCuotaPlanSeleccionado = null;
var cobrarCuotaClientes = [];
var cobrarCuotaPlanes = [];
var cobrarCuotaCuotas = [];
var cobrarCuotaContextoUeno = null;
var cobrarCuotaProcesando = false;
var cobrarCuotaTiposPagoCargados = false;
var cobrarCuotaUltimoRecibo = null;
var cobrarCuotaUltimoPagoRegistrado = null;
var cobrarCuotaConfirmacionPendiente = null;
var cobrarCuotaAvisoTimer = null;
var cobrarCuotaFiltroEstado = "todas";
var cobrarCuotaUenoResultadosTotal = 0;
var cobrarCuotaUenoBusquedaActiva = false;
var cobrarCuotaUenoTieneCoincidenciaExacta = false;
var cobrarCuotaForzarPendienteUeno = false;
var cobrarCuotaUenoModalFecha = "";
var cobrarCuotaUenoModalModoFecha = "dia";
var cobrarCuotaUenoBusquedaServidorTimer = null;

function cobrarCuotaId(id) {
	return document.getElementById(id);
}

function cobrarCuotaNormalizarTexto(valor) {
	valor = String(valor || "").toUpperCase();
	if (valor.normalize) {
		valor = valor.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
	}
	return valor.replace(/\s+/g, " ").trim();
}

function cobrarCuotaEstadoSlug(valor) {
	return cobrarCuotaNormalizarTexto(valor).toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
}

function cobrarCuotaEsCuotaCobrable(cuota) {
	if (!cuota) {
		return false;
	}
	var estado = cobrarCuotaNormalizarTexto(cuota.estado);
	return Number(cuota.saldo_pendiente_num || 0) > 0 && estado != "PAGADA" && estado != "ANULADA";
}

function cobrarCuotaEsEntrega(cuota) {
	return cobrarCuotaNormalizarTexto(cuota && cuota.cuota).toUpperCase() == "ENTREGA";
}

function cobrarCuotaEsTransferenciaTexto(valor) {
	return cobrarCuotaNormalizarTexto(valor).indexOf("TRANSFERENCIA") !== -1;
}

function cobrarCuotaNumero(valor) {
	if (typeof QuitarSeparadorMilValor === "function") {
		return Number(QuitarSeparadorMilValor(valor)) || 0;
	}
	valor = String(valor || "").replace(/\./g, "").replace(",", ".");
	return Number(valor) || 0;
}

function cobrarCuotaNormalizarDocumento(valor) {
	return String(valor || "").replace(/[^0-9A-Za-z]/g, "");
}

function cobrarCuotaFormato(valor) {
	valor = Number(valor) || 0;
	if (typeof separadordemilesnumero === "function") {
		return separadordemilesnumero(String(Math.round(valor)));
	}
	return String(Math.round(valor)).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function cobrarCuotaEscape(valor) {
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

function cobrarCuotaMaskComprobante(valor) {
	valor = String(valor || "").replace(/\s+/g, "").trim();
	if (valor.length <= 0) {
		return "";
	}
	if (valor.length <= 2) {
		return "**";
	}
	if (valor.length < 7) {
		return valor.charAt(0) + "***" + valor.charAt(valor.length - 1);
	}
	return valor.substring(0, 3) + "******" + valor.substring(valor.length - 3);
}

function cobrarCuotaResetBusquedaUeno() {
	cobrarCuotaUenoResultadosTotal = 0;
	cobrarCuotaUenoBusquedaActiva = false;
	cobrarCuotaUenoTieneCoincidenciaExacta = false;
	cobrarCuotaForzarPendienteUeno = false;
}

function cobrarCuotaTieneMovimientoUenoValido() {
	if (!cobrarCuotaContextoUeno || !cobrarCuotaContextoUeno.id_movimiento) {
		return false;
	}
	if (cobrarCuotaContextoUeno.puede_usar === false || cobrarCuotaContextoUeno.monto_valido === false) {
		return false;
	}
	return true;
}

function cobrarCuotaMovimientoUenoSeguro(movimiento) {
	if (!movimiento) {
		return null;
	}
	return {
		id_movimiento: movimiento.id_movimiento || "",
		nro_comprobante: movimiento.nro_comprobante || "",
		comprobante_masked: movimiento.comprobante_masked || cobrarCuotaMaskComprobante(movimiento.nro_comprobante || ""),
		fecha_confirmacion: movimiento.fecha_confirmacion || "",
		fecha_transaccion: movimiento.fecha_transaccion || "",
		fecha_movimiento: movimiento.fecha_movimiento || "",
		monto_disponible: movimiento.monto_disponible || 0,
		monto_disponible_fmt: movimiento.monto_disponible_fmt || "",
		importe_credito: movimiento.importe_credito || 0,
		importe_credito_fmt: movimiento.importe_credito_fmt || "",
		estado: movimiento.estado || "",
		coincidencia_exacta: movimiento.coincidencia_exacta === true,
		fecha_pago_coincide: movimiento.fecha_pago_coincide === true,
		monto_valido: movimiento.monto_valido !== false,
		pago_parcial_sugerido: movimiento.pago_parcial_sugerido === true,
		puede_usar: movimiento.puede_usar !== false
	};
}

function cobrarCuotaNormalizarFiltroUeno(valor) {
	return cobrarCuotaNormalizarTexto(valor).replace(/[^0-9A-Z]/g, "");
}

function cobrarCuotaNormalizarMontoFiltroUeno(valor) {
	valor = String(valor || "").trim();
	if (valor == "") {
		return "";
	}
	return String(Math.round(cobrarCuotaNumero(valor) || 0)).replace(/[^0-9]/g, "");
}

function cobrarCuotaCoincideMontoUeno(item, filtroMonto) {
	if (filtroMonto == "") {
		return true;
	}
	var campos = [
		item.getAttribute("data-ueno-importe") || "",
		item.getAttribute("data-ueno-disponible") || "",
		item.getAttribute("data-ueno-saldo") || ""
	];
	for (var i = 0; i < campos.length; i++) {
		var monto = String(Math.round(Number(campos[i]) || 0));
		if (monto == filtroMonto || monto.indexOf(filtroMonto) !== -1) {
			return true;
		}
	}
	return false;
}

function cobrarCuotaFechaDosDigitos(valor) {
	valor = Number(valor) || 0;
	return valor < 10 ? "0" + valor : String(valor);
}

function cobrarCuotaFechaLocalAISO(fecha) {
	return fecha.getFullYear() + "-" + cobrarCuotaFechaDosDigitos(fecha.getMonth() + 1) + "-" + cobrarCuotaFechaDosDigitos(fecha.getDate());
}

function cobrarCuotaFechaHoyISO() {
	return cobrarCuotaFechaLocalAISO(new Date());
}

function cobrarCuotaFechaDesdeISO(valor) {
	var partes = String(valor || "").split("-");
	if (partes.length != 3) {
		return null;
	}
	var anho = Number(partes[0]);
	var mes = Number(partes[1]);
	var dia = Number(partes[2]);
	if (!anho || !mes || !dia) {
		return null;
	}
	return new Date(anho, mes - 1, dia);
}

function cobrarCuotaFechaDiferenciaDiasISO(fechaBase, fechaComparar) {
	var base = cobrarCuotaFechaDesdeISO(fechaBase);
	var comparar = cobrarCuotaFechaDesdeISO(fechaComparar);
	if (!base || !comparar) {
		return null;
	}
	base.setHours(0, 0, 0, 0);
	comparar.setHours(0, 0, 0, 0);
	return Math.round((comparar.getTime() - base.getTime()) / 86400000);
}

function cobrarCuotaFechaSumarDiasISO(valor, dias) {
	var fecha = cobrarCuotaFechaDesdeISO(valor) || new Date();
	fecha.setDate(fecha.getDate() + (Number(dias) || 0));
	return cobrarCuotaFechaLocalAISO(fecha);
}

function cobrarCuotaFechaVisualISO(valor) {
	var fecha = cobrarCuotaFechaDesdeISO(valor);
	if (!fecha) {
		return "";
	}
	return cobrarCuotaFechaDosDigitos(fecha.getDate()) + "/" + cobrarCuotaFechaDosDigitos(fecha.getMonth() + 1) + "/" + fecha.getFullYear();
}

function cobrarCuotaFechaEtiquetaRelativa(valor) {
	var hoy = cobrarCuotaFechaHoyISO();
	if (valor == hoy) {
		return "hoy";
	}
	if (valor == cobrarCuotaFechaSumarDiasISO(hoy, -1)) {
		return "ayer";
	}
	return "";
}

function cobrarCuotaFechaMesEtiqueta(valor) {
	var fecha = cobrarCuotaFechaDesdeISO(valor);
	var meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
	if (!fecha) {
		return "Sin fecha";
	}
	return meses[fecha.getMonth()] + " " + fecha.getFullYear();
}

function cobrarCuotaFechaTituloGrupo(valor) {
	var visual = cobrarCuotaFechaVisualISO(valor);
	var relativa = cobrarCuotaFechaEtiquetaRelativa(valor);
	if (visual == "") {
		return "Sin fecha";
	}
	return relativa != "" ? visual + " - " + relativa : visual;
}

function cobrarCuotaActualizarFechaModalUeno() {
	var etiqueta = cobrarCuotaId("lblCobrarCuotaUenoFechaFiltro");
	var btnHoy = cobrarCuotaId("btnCobrarCuotaUenoFechaHoy");
	var btnTodas = cobrarCuotaId("btnCobrarCuotaUenoFechaTodas");
	if (!cobrarCuotaUenoModalFecha) {
		cobrarCuotaUenoModalFecha = cobrarCuotaFechaHoyISO();
	}
	if (etiqueta) {
		etiqueta.textContent = cobrarCuotaUenoModalModoFecha == "todas"
			? "Todas las fechas"
			: cobrarCuotaFechaTituloGrupo(cobrarCuotaUenoModalFecha);
	}
	if (btnHoy && btnHoy.classList) {
		if (cobrarCuotaUenoModalModoFecha == "dia" && cobrarCuotaUenoModalFecha == cobrarCuotaFechaHoyISO()) {
			btnHoy.classList.add("is-active");
		} else {
			btnHoy.classList.remove("is-active");
		}
	}
	if (btnTodas && btnTodas.classList) {
		if (cobrarCuotaUenoModalModoFecha == "todas") {
			btnTodas.classList.add("is-active");
		} else {
			btnTodas.classList.remove("is-active");
		}
	}
}

function cobrarCuotaAplicarFiltroFechaModalUeno() {
	var cuerpo = cobrarCuotaId("divCobrarCuotaUenoModalCuerpo");
	cobrarCuotaActualizarFechaModalUeno();
	if (cuerpo) {
		cobrarCuotaBuscarFiltroServidorUeno(cuerpo);
	}
}

function cobrarCuotaCambiarFechaUeno(dias) {
	if (!cobrarCuotaUenoModalFecha) {
		cobrarCuotaUenoModalFecha = cobrarCuotaFechaHoyISO();
	}
	cobrarCuotaUenoModalModoFecha = "dia";
	cobrarCuotaUenoModalFecha = cobrarCuotaFechaSumarDiasISO(cobrarCuotaUenoModalFecha, dias);
	cobrarCuotaAplicarFiltroFechaModalUeno();
}

function cobrarCuotaIrHoyUeno() {
	cobrarCuotaUenoModalModoFecha = "dia";
	cobrarCuotaUenoModalFecha = cobrarCuotaFechaHoyISO();
	cobrarCuotaAplicarFiltroFechaModalUeno();
}

function cobrarCuotaVerTodasFechasUeno() {
	cobrarCuotaUenoModalModoFecha = "todas";
	if (!cobrarCuotaUenoModalFecha) {
		cobrarCuotaUenoModalFecha = cobrarCuotaFechaHoyISO();
	}
	cobrarCuotaAplicarFiltroFechaModalUeno();
}

function cobrarCuotaLimpiarGruposFechaUeno(contenedor) {
	if (!contenedor || !contenedor.querySelectorAll) {
		return;
	}
	var grupos = contenedor.querySelectorAll(".cobrar-cuota-ueno-modal__date-group");
	for (var i = 0; i < grupos.length; i++) {
		if (grupos[i].parentNode) {
			grupos[i].parentNode.removeChild(grupos[i]);
		}
	}
}

function cobrarCuotaCrearGrupoFechaUeno(clase, texto) {
	var grupo = document.createElement("div");
	var etiqueta = document.createElement("span");
	grupo.className = "cobrar-cuota-ueno-modal__date-group " + clase;
	etiqueta.textContent = texto;
	grupo.appendChild(etiqueta);
	return grupo;
}

function cobrarCuotaAgruparTodasFechasUeno(contenedor) {
	var lista = contenedor && contenedor.querySelector ? contenedor.querySelector(".cobrar-cuota-ueno-modal__list") : null;
	if (!lista) {
		return;
	}
	var items = Array.prototype.slice.call(lista.querySelectorAll(".cobrar-cuota__ueno-item"));
	var ultimoMes = "";
	var ultimoDia = "";
	for (var i = 0; i < items.length; i++) {
		var item = items[i];
		if (item.style.display == "none") {
			continue;
		}
		var fecha = item.getAttribute("data-ueno-fecha") || "";
		var mes = fecha ? fecha.substring(0, 7) : "sin-fecha";
		if (mes != ultimoMes) {
			lista.insertBefore(cobrarCuotaCrearGrupoFechaUeno("cobrar-cuota-ueno-modal__date-group--month", cobrarCuotaFechaMesEtiqueta(fecha)), item);
			ultimoMes = mes;
			ultimoDia = "";
		}
		if (fecha != ultimoDia) {
			lista.insertBefore(cobrarCuotaCrearGrupoFechaUeno("cobrar-cuota-ueno-modal__date-group--day", cobrarCuotaFechaTituloGrupo(fecha)), item);
			ultimoDia = fecha;
		}
	}
}

function cobrarCuotaLimpiarSugerenciasUeno(contenedor) {
	if (!contenedor || !contenedor.querySelectorAll) {
		return;
	}
	var items = contenedor.querySelectorAll(".cobrar-cuota__ueno-item");
	for (var i = 0; i < items.length; i++) {
		items[i].classList.remove("cobrar-cuota__ueno-item--sugerida");
	}
	var badges = contenedor.querySelectorAll(".js-cobrar-cuota-ueno-sugerida");
	for (i = 0; i < badges.length; i++) {
		if (badges[i].parentNode) {
			badges[i].parentNode.removeChild(badges[i]);
		}
	}
}

function cobrarCuotaMarcarSugerenciasUeno(contenedor) {
	cobrarCuotaLimpiarSugerenciasUeno(contenedor);
	if (!contenedor || !contenedor.querySelectorAll) {
		return;
	}
	var montoBase = cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "");
	var fechaBase = cobrarCuotaId("inptCobrarCuotaFechaPago") ? cobrarCuotaId("inptCobrarCuotaFechaPago").value : "";
	if (montoBase <= 0 || fechaBase == "") {
		return;
	}
	var toleranciaMonto = 1000;
	var items = contenedor.querySelectorAll(".cobrar-cuota__ueno-item");
	for (var i = 0; i < items.length; i++) {
		var item = items[i];
		var montoMovimiento = Number(item.getAttribute("data-ueno-disponible") || item.getAttribute("data-ueno-importe") || 0);
		var diferenciaMonto = Math.abs(montoMovimiento - montoBase);
		var diferenciaDias = cobrarCuotaFechaDiferenciaDiasISO(fechaBase, item.getAttribute("data-ueno-fecha") || "");
		if (diferenciaMonto <= toleranciaMonto && diferenciaDias !== null && Math.abs(diferenciaDias) <= 2) {
			item.classList.add("cobrar-cuota__ueno-item--sugerida");
			var contenedorBadges = item.querySelector(".cobrar-cuota__ueno-badges");
			if (contenedorBadges) {
				var badge = document.createElement("span");
				badge.className = "cobrar-cuota__ueno-badge cobrar-cuota__ueno-badge--suggest js-cobrar-cuota-ueno-sugerida";
				badge.textContent = "Sugerida";
				contenedorBadges.insertBefore(badge, contenedorBadges.firstChild);
			}
		}
	}
}

function cobrarCuotaResolverContenedorFiltroUeno(origen) {
	if (origen && origen.nodeType === 1) {
		if (origen.id == "divCobrarCuotaUeno" || (origen.classList && origen.classList.contains("cobrar-cuota-ueno-modal__body"))) {
			return origen;
		}
		if (origen.closest) {
			return origen.closest(".cobrar-cuota-ueno-modal__body") || origen.closest("#divCobrarCuotaUeno") || cobrarCuotaId("divCobrarCuotaUeno");
		}
	}
	return cobrarCuotaId("divCobrarCuotaUeno");
}

function cobrarCuotaBuscarEnFiltroUeno(contenedor, selector, idFallback) {
	if (contenedor && contenedor.querySelector) {
		var elemento = contenedor.querySelector(selector);
		if (elemento) {
			return elemento;
		}
	}
	return idFallback ? cobrarCuotaId(idFallback) : null;
}

function cobrarCuotaFiltrarMovimientosUeno(origen) {
	var contenedor = cobrarCuotaResolverContenedorFiltroUeno(origen);
	if (!contenedor) {
		return;
	}
	var esModal = contenedor.classList && contenedor.classList.contains("cobrar-cuota-ueno-modal__body");
	if (esModal) {
		cobrarCuotaLimpiarGruposFechaUeno(contenedor);
		cobrarCuotaActualizarFechaModalUeno();
	}
	var inputComprobante = cobrarCuotaBuscarEnFiltroUeno(contenedor, ".js-cobrar-cuota-ueno-filtro-comprobante", "inptCobrarCuotaFiltroUenoComprobante");
	var inputMonto = cobrarCuotaBuscarEnFiltroUeno(contenedor, ".js-cobrar-cuota-ueno-filtro-monto", "inptCobrarCuotaFiltroUenoMonto");
	var filtroComprobante = cobrarCuotaNormalizarFiltroUeno(inputComprobante ? inputComprobante.value : "");
	var filtroMonto = cobrarCuotaNormalizarMontoFiltroUeno(inputMonto ? inputMonto.value : "");
	var items = contenedor.querySelectorAll(".cobrar-cuota__ueno-item");
	var filtroManualActivo = filtroComprobante != "" || filtroMonto != "";
	var visibles = 0;
	for (var i = 0; i < items.length; i++) {
		var item = items[i];
		var comprobante = cobrarCuotaNormalizarFiltroUeno(item.getAttribute("data-ueno-comprobante") || "");
		var comprobanteMask = cobrarCuotaNormalizarFiltroUeno(item.getAttribute("data-ueno-comprobante-mask") || "");
		var coincideComprobante = filtroComprobante == ""
			|| comprobante.indexOf(filtroComprobante) !== -1
			|| comprobanteMask.indexOf(filtroComprobante) !== -1;
		var coincideMonto = cobrarCuotaCoincideMontoUeno(item, filtroMonto);
		var coincideFecha = true;
		if (esModal && cobrarCuotaUenoModalModoFecha == "dia") {
			coincideFecha = (item.getAttribute("data-ueno-fecha") || "") == cobrarCuotaUenoModalFecha;
		}
		var visible = coincideComprobante && coincideMonto && coincideFecha;
		item.style.display = visible ? "" : "none";
		if (visible) {
			visibles++;
		}
	}
	var resultado = cobrarCuotaBuscarEnFiltroUeno(contenedor, ".js-cobrar-cuota-ueno-filtro-resultado", "lblCobrarCuotaFiltroUenoResultado");
	if (resultado) {
		var total = items.length;
		resultado.textContent = visibles == total
			? cobrarCuotaFormato(total) + " transferencias"
			: cobrarCuotaFormato(visibles) + " de " + cobrarCuotaFormato(total) + " transferencias";
	}
	var vacio = cobrarCuotaBuscarEnFiltroUeno(contenedor, ".js-cobrar-cuota-ueno-filtro-vacio", "divCobrarCuotaFiltroUenoVacio");
	if (vacio) {
		if (visibles == 0 && esModal && !filtroManualActivo && cobrarCuotaUenoModalModoFecha == "dia") {
			vacio.textContent = cobrarCuotaUenoModalFecha == cobrarCuotaFechaHoyISO()
				? "No hay transferencias el dia de hoy."
				: "No hay transferencias para esta fecha.";
		} else if (visibles == 0 && esModal && !filtroManualActivo && cobrarCuotaUenoModalModoFecha == "todas") {
			vacio.textContent = "No hay transferencias disponibles.";
		} else {
			vacio.textContent = "No hay transferencias con ese comprobante o monto.";
		}
		vacio.style.display = visibles == 0 && items.length > 0 ? "block" : "none";
	}
	if (esModal && cobrarCuotaUenoModalModoFecha == "todas") {
		cobrarCuotaAgruparTodasFechasUeno(contenedor);
	}
}

function cobrarCuotaFiltrarMovimientosUenoInteractivo(origen) {
	var contenedor = cobrarCuotaResolverContenedorFiltroUeno(origen);
	cobrarCuotaFiltrarMovimientosUeno(contenedor || origen);
	if (!contenedor || !contenedor.classList || !contenedor.classList.contains("cobrar-cuota-ueno-modal__body")) {
		return;
	}
	var inputComprobante = cobrarCuotaBuscarEnFiltroUeno(contenedor, ".js-cobrar-cuota-ueno-filtro-comprobante", "");
	var inputMonto = cobrarCuotaBuscarEnFiltroUeno(contenedor, ".js-cobrar-cuota-ueno-filtro-monto", "");
	var filtroComprobante = inputComprobante ? cobrarCuotaNormalizarFiltroUeno(inputComprobante.value) : "";
	var filtroMonto = inputMonto ? cobrarCuotaNormalizarMontoFiltroUeno(inputMonto.value) : "";
	if (cobrarCuotaUenoBusquedaServidorTimer) {
		clearTimeout(cobrarCuotaUenoBusquedaServidorTimer);
		cobrarCuotaUenoBusquedaServidorTimer = null;
	}
	if (filtroComprobante == "" && filtroMonto == "") {
		return;
	}
	cobrarCuotaUenoBusquedaServidorTimer = setTimeout(function() {
		cobrarCuotaBuscarFiltroServidorUeno(contenedor);
	}, 450);
}

function cobrarCuotaLimpiarFiltroMovimientosUeno(origen) {
	var contenedor = cobrarCuotaResolverContenedorFiltroUeno(origen);
	var inputComprobante = cobrarCuotaBuscarEnFiltroUeno(contenedor, ".js-cobrar-cuota-ueno-filtro-comprobante", "inptCobrarCuotaFiltroUenoComprobante");
	var inputMonto = cobrarCuotaBuscarEnFiltroUeno(contenedor, ".js-cobrar-cuota-ueno-filtro-monto", "inptCobrarCuotaFiltroUenoMonto");
	if (inputComprobante) {
		inputComprobante.value = "";
	}
	if (inputMonto) {
		inputMonto.value = "";
	}
	cobrarCuotaFiltrarMovimientosUeno(contenedor);
	if (inputComprobante) {
		inputComprobante.focus();
	}
}

function cobrarCuotaBuscarFiltroServidorUeno(origen) {
	var contenedor = cobrarCuotaResolverContenedorFiltroUeno(origen);
	var cuerpoModal = cobrarCuotaId("divCobrarCuotaUenoModalCuerpo");
	var esModal = contenedor && contenedor.classList && contenedor.classList.contains("cobrar-cuota-ueno-modal__body");
	var inputComprobante = cobrarCuotaBuscarEnFiltroUeno(contenedor, ".js-cobrar-cuota-ueno-filtro-comprobante", "inptCobrarCuotaFiltroUenoComprobante");
	var inputMonto = cobrarCuotaBuscarEnFiltroUeno(contenedor, ".js-cobrar-cuota-ueno-filtro-monto", "inptCobrarCuotaFiltroUenoMonto");
	var filtroComprobante = inputComprobante ? inputComprobante.value : "";
	var filtroMonto = inputMonto ? inputMonto.value : "";
	if (!esModal || !cuerpoModal) {
		cobrarCuotaFiltrarMovimientosUeno(contenedor);
		return;
	}
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "buscar_movimiento_ueno");
	datos.append("comprobante", cobrarCuotaId("inptCobrarCuotaComprobante") ? cobrarCuotaId("inptCobrarCuotaComprobante").value : "");
	datos.append("monto", cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "");
	datos.append("fecha_pago", cobrarCuotaUenoModalModoFecha == "dia" ? cobrarCuotaUenoModalFecha : "");
	datos.append("ver_todos", cobrarCuotaUenoModalModoFecha == "todas" ? "SI" : "NO");
	datos.append("vista_amplia", "SI");
	datos.append("comprobante_busqueda", filtroComprobante);
	datos.append("monto_busqueda", filtroMonto);
	cuerpoModal.innerHTML = "<div class='cobrar-cuota__ueno-empty'>Buscando transferencias Ueno...</div>";
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCobrarCuota.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] != "exito") {
					cuerpoModal.innerHTML = "<div class='cobrar-cuota__ueno-empty cobrar-cuota__ueno-pending'>" + cobrarCuotaEscape(respuesta["2"] || "No se pudo buscar transferencias Ueno.") + "</div>";
					return;
				}
				cobrarCuotaUenoResultadosTotal = Number(respuesta["3"] || 0);
				cuerpoModal.innerHTML = respuesta["2"] || "";
				cobrarCuotaPrepararModalUeno(cuerpoModal);
				var modalComprobante = cobrarCuotaBuscarEnFiltroUeno(cuerpoModal, ".js-cobrar-cuota-ueno-filtro-comprobante", "");
				var modalMonto = cobrarCuotaBuscarEnFiltroUeno(cuerpoModal, ".js-cobrar-cuota-ueno-filtro-monto", "");
				if (modalComprobante) {
					modalComprobante.value = filtroComprobante;
				}
				if (modalMonto) {
					modalMonto.value = filtroMonto;
				}
				if (respuesta["ver_todos"] == "SI") {
					cobrarCuotaUenoModalModoFecha = "todas";
				} else if (respuesta["fecha_pago"]) {
					cobrarCuotaUenoModalModoFecha = "dia";
					cobrarCuotaUenoModalFecha = respuesta["fecha_pago"];
				}
				cobrarCuotaActualizarFechaModalUeno();
				cobrarCuotaFiltrarMovimientosUeno(cuerpoModal);
				if (modalComprobante && filtroComprobante != "") {
					modalComprobante.focus();
				} else if (modalMonto) {
					modalMonto.focus();
				}
			} catch (error) {
				cuerpoModal.innerHTML = "<div class='cobrar-cuota__ueno-empty cobrar-cuota__ueno-pending'>No se pudo interpretar la busqueda Ueno.</div>";
			}
		},
		error: function() {
			cuerpoModal.innerHTML = "<div class='cobrar-cuota__ueno-empty cobrar-cuota__ueno-pending'>Error de conexion al buscar transferencias Ueno.</div>";
		}
	});
}

function cobrarCuotaPrepararModalUeno(cuerpo) {
	if (!cuerpo) {
		return;
	}
	var nodosConId = cuerpo.querySelectorAll("[id]");
	for (var i = 0; i < nodosConId.length; i++) {
		nodosConId[i].id = nodosConId[i].id + "Modal";
	}
	var herramientas = cuerpo.querySelector(".cobrar-cuota__ueno-toolbar");
	if (herramientas && herramientas.parentNode) {
		herramientas.parentNode.removeChild(herramientas);
	}
	var botonVistaAmplia = cuerpo.querySelector(".cobrar-cuota__ueno-warning-action");
	if (botonVistaAmplia && botonVistaAmplia.parentNode) {
		botonVistaAmplia.parentNode.removeChild(botonVistaAmplia);
	}
	var encabezadoLista = cuerpo.querySelector(".cobrar-cuota__ueno-list-head span");
	if (encabezadoLista) {
		encabezadoLista.textContent = "Listado completo de transferencias disponibles";
	}
	var items = Array.prototype.slice.call(cuerpo.querySelectorAll(".cobrar-cuota__ueno-item"));
	if (items.length == 0) {
		return;
	}
	var lista = cuerpo.querySelector(".cobrar-cuota__ueno-list");
	if (!lista) {
		lista = document.createElement("div");
		items[0].parentNode.insertBefore(lista, items[0]);
		for (i = 0; i < items.length; i++) {
			lista.appendChild(items[i]);
		}
	}
	lista.classList.remove("cobrar-cuota__ueno-list--compact");
	lista.classList.add("cobrar-cuota-ueno-modal__list");
	for (i = 0; i < items.length; i++) {
		items[i].classList.remove("cobrar-cuota__ueno-item--extra");
	}
	cobrarCuotaMarcarSugerenciasUeno(cuerpo);
	var cabecera = document.createElement("div");
	cabecera.className = "cobrar-cuota-ueno-modal__list-head";
	cabecera.innerHTML = "<span>Fecha / Comprobante</span><span>Importe</span><span>Disponible</span><span>Saldo</span><span>Estado</span><span>Accion</span>";
	lista.insertBefore(cabecera, lista.firstChild);
	var aviso = cuerpo.querySelector(".cobrar-cuota__ueno-warning");
	if (aviso) {
		aviso.classList.add("cobrar-cuota__ueno-warning--modal");
	}
}

function cobrarCuotaAbrirModalUeno() {
	var origen = cobrarCuotaId("divCobrarCuotaUeno");
	var modal = cobrarCuotaId("divCobrarCuotaUenoModal");
	var cuerpo = cobrarCuotaId("divCobrarCuotaUenoModalCuerpo");
	if (!origen || !modal || !cuerpo) {
		cobrarCuotaAviso("No se encontro la vista ampliada de transferencias Ueno.", "error");
		return;
	}
	if (origen.querySelectorAll(".cobrar-cuota__ueno-item").length == 0) {
		cobrarCuotaAviso("No hay transferencias Ueno para mostrar en grande.");
		return;
	}
	var filtroComprobante = cobrarCuotaBuscarEnFiltroUeno(origen, ".js-cobrar-cuota-ueno-filtro-comprobante", "inptCobrarCuotaFiltroUenoComprobante");
	var filtroMonto = cobrarCuotaBuscarEnFiltroUeno(origen, ".js-cobrar-cuota-ueno-filtro-monto", "inptCobrarCuotaFiltroUenoMonto");
	var valorComprobante = filtroComprobante ? filtroComprobante.value : "";
	var valorMonto = filtroMonto ? filtroMonto.value : "";
	cuerpo.innerHTML = origen.innerHTML;
	cobrarCuotaPrepararModalUeno(cuerpo);
	var modalComprobante = cobrarCuotaBuscarEnFiltroUeno(cuerpo, ".js-cobrar-cuota-ueno-filtro-comprobante", "");
	var modalMonto = cobrarCuotaBuscarEnFiltroUeno(cuerpo, ".js-cobrar-cuota-ueno-filtro-monto", "");
	if (modalComprobante) {
		modalComprobante.value = valorComprobante;
	}
	if (modalMonto) {
		modalMonto.value = valorMonto;
	}
	cobrarCuotaUenoModalModoFecha = "dia";
	cobrarCuotaUenoModalFecha = cobrarCuotaFechaHoyISO();
	cobrarCuotaActualizarFechaModalUeno();
	modal.style.display = "";
	cobrarCuotaBuscarFiltroServidorUeno(cuerpo);
	if (modalComprobante) {
		modalComprobante.focus();
	}
}

function cobrarCuotaCerrarModalUeno() {
	var modal = cobrarCuotaId("divCobrarCuotaUenoModal");
	var cuerpo = cobrarCuotaId("divCobrarCuotaUenoModalCuerpo");
	if (modal) {
		modal.style.display = "none";
	}
	if (cuerpo) {
		cuerpo.innerHTML = "";
	}
}

function cobrarCuotaClonarSimple(objeto) {
	var copia = {};
	if (!objeto) {
		return copia;
	}
	for (var clave in objeto) {
		if (Object.prototype.hasOwnProperty.call(objeto, clave)) {
			copia[clave] = objeto[clave];
		}
	}
	return copia;
}

function cobrarCuotaTextoTipoPagoActual() {
	var select = cobrarCuotaId("inptCobrarCuotaTipoPago");
	return select && select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : "";
}

function cobrarCuotaEsTransferenciaActual() {
	return cobrarCuotaEsTransferenciaTexto(cobrarCuotaTextoTipoPagoActual());
}

function cobrarCuotaObtenerCuotasSeleccionadas() {
	var cuotas = [];
	var usados = {};
	var i, cuota, id;
	if (Array.isArray(cobrarCuotaSeleccionadas)) {
		for (i = 0; i < cobrarCuotaSeleccionadas.length; i++) {
			cuota = cobrarCuotaSeleccionadas[i];
			id = cuota && cuota.idcredito ? String(cuota.idcredito) : "";
			if (id != "" && !usados[id] && cobrarCuotaEsCuotaCobrable(cuota)) {
				usados[id] = true;
				cuotas.push(cuota);
			}
		}
	}
	if (cuotas.length == 0 && cobrarCuotaSeleccionada && cobrarCuotaSeleccionada.idcredito && cobrarCuotaEsCuotaCobrable(cobrarCuotaSeleccionada)) {
		cuotas.push(cobrarCuotaSeleccionada);
	}
	cobrarCuotaSeleccionadas = cuotas;
	cobrarCuotaSeleccionada = cuotas.length > 0 ? cuotas[cuotas.length - 1] : null;
	return cuotas.slice(0);
}

function cobrarCuotaEstaSeleccionada(idcredito) {
	var cuotas = cobrarCuotaObtenerCuotasSeleccionadas();
	for (var i = 0; i < cuotas.length; i++) {
		if (String(cuotas[i].idcredito) == String(idcredito)) {
			return true;
		}
	}
	return false;
}

function cobrarCuotaTotalSeleccionadas(cuotas) {
	cuotas = cuotas || cobrarCuotaObtenerCuotasSeleccionadas();
	var total = 0;
	for (var i = 0; i < cuotas.length; i++) {
		total += Number(cuotas[i].saldo_pendiente_num || 0);
	}
	return total;
}

function cobrarCuotaOrdenarCuotasSeleccionadas(cuotas) {
	cuotas = cuotas || cobrarCuotaObtenerCuotasSeleccionadas();
	var mapa = {};
	for (var i = 0; i < cuotas.length; i++) {
		if (cuotas[i] && cuotas[i].idcredito) {
			mapa[String(cuotas[i].idcredito)] = cuotas[i];
		}
	}
	var ordenadas = [];
	for (var j = 0; j < cobrarCuotaCuotas.length; j++) {
		var id = String(cobrarCuotaCuotas[j].idcredito || "");
		if (mapa[id]) {
			ordenadas.push(mapa[id]);
			delete mapa[id];
		}
	}
	for (var clave in mapa) {
		if (Object.prototype.hasOwnProperty.call(mapa, clave)) {
			ordenadas.push(mapa[clave]);
		}
	}
	return ordenadas;
}

function cobrarCuotaCalcularAplicaciones(cuotas, monto) {
	cuotas = cobrarCuotaOrdenarCuotasSeleccionadas(cuotas);
	monto = Number(monto) || 0;
	var restante = monto;
	var aplicaciones = [];
	for (var i = 0; i < cuotas.length; i++) {
		var saldo = Number(cuotas[i].saldo_pendiente_num || 0);
		var aplicado = Math.min(Math.max(0, restante), saldo);
		if (aplicado > 0) {
			aplicaciones.push({
				cuota: cuotas[i],
				monto: aplicado,
				saldoAnterior: saldo,
				saldoRestante: Math.max(0, saldo - aplicado),
				parcial: aplicado < saldo
			});
			restante -= aplicado;
		}
	}
	return aplicaciones;
}

function cobrarCuotaMantenerSoloSeleccionActual() {
	var cuotas = cobrarCuotaObtenerCuotasSeleccionadas();
	var cuota = cobrarCuotaSeleccionada || (cuotas.length > 0 ? cuotas[0] : null);
	if (cuota && cuota.idcredito && cobrarCuotaEsCuotaCobrable(cuota)) {
		cobrarCuotaSeleccionada = cuota;
		cobrarCuotaSeleccionadas = [cuota];
	} else {
		cobrarCuotaSeleccionada = null;
		cobrarCuotaSeleccionadas = [];
	}
}

function cobrarCuotaAgregarSeleccion(cuota) {
	if (!cuota || !cuota.idcredito || !cobrarCuotaEsCuotaCobrable(cuota)) {
		return;
	}
	var cuotas = cobrarCuotaObtenerCuotasSeleccionadas();
	var existe = false;
	for (var i = 0; i < cuotas.length; i++) {
		if (String(cuotas[i].idcredito) == String(cuota.idcredito)) {
			cuotas[i] = cuota;
			existe = true;
			break;
		}
	}
	if (!existe) {
		cuotas.push(cuota);
	}
	cobrarCuotaSeleccionadas = cuotas;
	cobrarCuotaSeleccionada = cuota;
}

function cobrarCuotaQuitarSeleccion(idcredito) {
	var cuotas = cobrarCuotaObtenerCuotasSeleccionadas();
	var nuevas = [];
	for (var i = 0; i < cuotas.length; i++) {
		if (String(cuotas[i].idcredito) != String(idcredito)) {
			nuevas.push(cuotas[i]);
		}
	}
	cobrarCuotaSeleccionadas = nuevas;
	cobrarCuotaSeleccionada = nuevas.length > 0 ? nuevas[nuevas.length - 1] : null;
}

function cobrarCuotaActualizarEstadoMonto() {
	var monto = cobrarCuotaId("inptCobrarCuotaMontoCobrar");
	if (!monto) {
		return;
	}
	var cuotas = cobrarCuotaObtenerCuotasSeleccionadas();
	monto.readOnly = false;
	monto.title = cuotas.length > 1 ? "El monto se aplicara en orden a las cuotas seleccionadas; puede dejar la ultima como parcial." : "";
}

function cobrarCuotaActualizarMontosDesdeSeleccion(forzarSimple) {
	var cuotas = cobrarCuotaObtenerCuotasSeleccionadas();
	if (cuotas.length == 0) {
		cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", "");
		cobrarCuotaSetValor("inptCobrarCuotaMontoRecibido", "");
		cobrarCuotaSetValor("inptCobrarCuotaVuelto", "");
		cobrarCuotaActualizarEstadoMonto();
		return;
	}
	var total = cobrarCuotaTotalSeleccionadas(cuotas);
	if (cuotas.length > 1) {
		cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", cobrarCuotaFormato(total));
		var recibidoActual = cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoRecibido") ? cobrarCuotaId("inptCobrarCuotaMontoRecibido").value : "");
		if (recibidoActual < total) {
			cobrarCuotaSetValor("inptCobrarCuotaMontoRecibido", cobrarCuotaFormato(total));
		}
	} else if (forzarSimple !== false) {
		cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", cuotas[0].saldo_pendiente || cobrarCuotaFormato(total));
		cobrarCuotaSetValor("inptCobrarCuotaMontoRecibido", cuotas[0].saldo_pendiente || cobrarCuotaFormato(total));
	}
	cobrarCuotaActualizarEstadoMonto();
	cobrarCuotaCalcularVuelto();
}

function cobrarCuotaRenderSeleccionActual() {
	var contenedor = cobrarCuotaId("divCobrarCuotaSeleccionada");
	if (!contenedor) {
		return;
	}
	var cuotas = cobrarCuotaObtenerCuotasSeleccionadas();
	if (cuotas.length == 0) {
		contenedor.innerHTML = "<div class='cobrar-cuota__placeholder'><b>Selecciona una cuota</b><span>Primero selecciona un plan y luego una cuota pendiente.</span></div>";
		return;
	}
	if (cuotas.length == 1) {
		var cuota = cuotas[0];
		contenedor.innerHTML = "<div class='cobrar-cuota__selected-card'>"
			+ "<div class='cobrar-cuota__selected-title'>Cuota seleccionada</div>"
			+ "<div class='cobrar-cuota__selected-grid'>"
			+ "<span><b>Cliente</b>" + cobrarCuotaEscape(cuota.cliente) + "</span>"
			+ "<span><b>Cedula</b>" + cobrarCuotaEscape(cuota.cedula) + "</span>"
			+ (cuota.alias ? "<span><b>Alias / apodo</b>" + cobrarCuotaEscape(cuota.alias) + "</span>" : "")
			+ "<span><b>Venta</b>" + cobrarCuotaEscape(cuota.venta) + "</span>"
			+ "<span class='cobrar-cuota__wide'><b>Producto / plan</b>" + cobrarCuotaEscape(cuota.producto) + "</span>"
			+ "<span><b>Cuota</b>" + cobrarCuotaEscape(cuota.cuota) + "</span>"
			+ "<span><b>Vencimiento</b>" + cobrarCuotaEscape(cuota.fecha_vencimiento) + "</span>"
			+ "<span><b>Monto cuota</b>" + cobrarCuotaEscape(cuota.monto_cuota) + "</span>"
			+ "<span><b>Pagado</b>" + cobrarCuotaEscape(cuota.pagado_total || "0") + "</span>"
			+ "<span><b>Interes pendiente</b>" + cobrarCuotaEscape(cuota.saldo_interes) + "</span>"
			+ "<span><b>Saldo pendiente</b>" + cobrarCuotaEscape(cuota.saldo_pendiente) + "</span>"
			+ "<span><b>Estado</b>" + cobrarCuotaEscape(cuota.estado) + "</span>"
			+ "</div>"
			+ "</div>";
		return;
	}
	var total = cobrarCuotaTotalSeleccionadas(cuotas);
	var detalle = "";
	for (var i = 0; i < cuotas.length; i++) {
		detalle += "<span><b>Cuota " + cobrarCuotaEscape(cuotas[i].cuota) + "</b>"
			+ "Venc. " + cobrarCuotaEscape(cuotas[i].fecha_vencimiento || "-")
			+ " - Saldo " + cobrarCuotaEscape(cuotas[i].saldo_pendiente || "0") + "</span>";
	}
	contenedor.innerHTML = "<div class='cobrar-cuota__selected-card'>"
		+ "<div class='cobrar-cuota__selected-title'>Cuotas seleccionadas</div>"
		+ "<div class='cobrar-cuota__selected-grid'>"
		+ "<span><b>Cliente</b>" + cobrarCuotaEscape(cuotas[0].cliente) + "</span>"
		+ "<span><b>Cedula</b>" + cobrarCuotaEscape(cuotas[0].cedula) + "</span>"
		+ "<span><b>Venta</b>" + cobrarCuotaEscape(cuotas[0].venta) + "</span>"
		+ "<span><b>Cantidad</b>" + cuotas.length + " cuotas</span>"
		+ "<span class='cobrar-cuota__wide'><b>Total seleccionado</b>" + cobrarCuotaEscape(cobrarCuotaFormato(total)) + " Gs.</span>"
		+ detalle
		+ "</div>"
		+ "</div>";
}

function cobrarCuotaActualizarSeleccionVisual(forzarMonto) {
	cobrarCuotaObtenerCuotasSeleccionadas();
	cobrarCuotaActualizarMontosDesdeSeleccion(forzarMonto);
	cobrarCuotaRenderSeleccionActual();
	if (cobrarCuotaCuotas.length > 0) {
		cobrarCuotaRenderCuotas();
	} else {
		cobrarCuotaMarcarFila("");
	}
	if (cobrarCuotaEsTransferenciaActual()) {
		cobrarCuotaBuscarMovimientoUeno();
	}
	cobrarCuotaActualizarResumenUeno();
	cobrarCuotaActualizarBotonRegistrar();
}

function cobrarCuotaToggleAyuda(forzarEstado) {
	var panel = cobrarCuotaId("divCobrarCuotaAyuda");
	var btn = cobrarCuotaId("btnCobrarCuotaAyuda");
	if (!panel) {
		return;
	}
	var visible = panel.style.display != "none" && panel.style.display != "";
	var abrir = typeof forzarEstado == "boolean" ? forzarEstado : !visible;
	panel.style.display = abrir ? "block" : "none";
	if (btn) {
		btn.setAttribute("aria-expanded", abrir ? "true" : "false");
		if (btn.classList) {
			if (abrir) {
				btn.classList.add("cobrar-cuota__guide-help-btn--open");
			} else {
				btn.classList.remove("cobrar-cuota__guide-help-btn--open");
			}
		}
	}
}

function cobrarCuotaAviso(texto, tipo) {
	if (typeof ver_vetana_informativa === "function") {
		ver_vetana_informativa(texto, "", tipo || "");
	} else {
		var aviso = cobrarCuotaId("divCobrarCuotaAvisoLocal");
		if (!aviso) {
			aviso = document.createElement("div");
			aviso.id = "divCobrarCuotaAvisoLocal";
			aviso.className = "cobrar-cuota-aviso-local";
			document.body.appendChild(aviso);
		}
		var tipoAviso = String(tipo || "info").replace(/[^a-zA-Z0-9_-]/g, "") || "info";
		aviso.className = "cobrar-cuota-aviso-local cobrar-cuota-aviso-local--" + tipoAviso;
		aviso.innerHTML = "<div>" + cobrarCuotaEscape(texto) + "</div>";
		aviso.style.display = "block";
		if (cobrarCuotaAvisoTimer) {
			clearTimeout(cobrarCuotaAvisoTimer);
		}
		cobrarCuotaAvisoTimer = setTimeout(function() {
			aviso.style.display = "none";
		}, 4200);
	}
}

function cobrarCuotaTienePermiso(codigo) {
	if (typeof accesosuser !== "undefined" && accesosuser && accesosuser[codigo]) {
		return accesosuser[codigo]["accion"] == "SI";
	}
	if (codigo == "VERCOBRARCUOTA") {
		return cobrarCuotaTienePermiso("VERPAGOSCREDITO");
	}
	if (codigo == "REGISTRARCOBRARCUOTA") {
		return cobrarCuotaTienePermiso("INSERTARPAGOSCREDITO");
	}
	return true;
}

function verCerrarCobrarCuota(d, contexto) {
	var modal = cobrarCuotaId("divCobrarCuota");
	var efecto = cobrarCuotaId("tdEfectoCobrarCuota");
	if (!modal) {
		cobrarCuotaAviso("No se encontro la pantalla Cobrar cuota");
		return;
	}
	if (d == "1") {
		if (!cobrarCuotaTienePermiso("VERCOBRARCUOTA")) {
			cobrarCuotaAviso("No tiene permiso para abrir Cobrar cuota", "error");
			return;
		}
		modal.style.display = "";
		if (efecto) {
			efecto.className = "magictime slideDownReturn";
		}
		cobrarCuotaPreparar(contexto || null);
	} else {
		if (efecto) {
			efecto.className = "magictime vanishOut";
		}
		$("div[id=divCobrarCuota]").fadeOut(250);
	}
}

function cobrarCuotaPreparar(contexto) {
	cobrarCuotaContextoUeno = null;
	cobrarCuotaResetBusquedaUeno();
	cobrarCuotaSeleccionada = null;
	cobrarCuotaSeleccionadas = [];
	cobrarCuotaClienteSeleccionado = null;
	cobrarCuotaPlanSeleccionado = null;
	cobrarCuotaClientes = [];
	cobrarCuotaPlanes = [];
	cobrarCuotaCuotas = [];
	cobrarCuotaUltimoRecibo = null;
	cobrarCuotaUltimoPagoRegistrado = null;
	cobrarCuotaFiltroEstado = "todas";
	cobrarCuotaToggleAyuda(false);
	cobrarCuotaSetHoy(true);
	cobrarCuotaBloquearFechaProceso();
	cobrarCuotaLimpiarResultado();
	cobrarCuotaCargarTiposPago(function() {
		cobrarCuotaSeleccionarTipoPorTexto("EFECTIVO");
	});
	cobrarCuotaLimpiarFlujo(true);

	if (contexto && contexto.origen == "cuentas") {
		cobrarCuotaCargarDesdeVenta(contexto.venta || "");
	}
	if (contexto && contexto.origen == "ueno") {
		cobrarCuotaContextoUeno = contexto.movimiento || null;
		cobrarCuotaSetValor("inptCobrarCuotaComprobante", cobrarCuotaContextoUeno ? (cobrarCuotaContextoUeno.nro_comprobante || "") : "");
		cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", cobrarCuotaContextoUeno ? (cobrarCuotaContextoUeno.monto_disponible_fmt || cobrarCuotaFormato(cobrarCuotaContextoUeno.monto_disponible || 0)) : "");
		cobrarCuotaCargarTiposPago(function() {
			cobrarCuotaSeleccionarTipoPorTexto("TRANSFERENCIA");
			cobrarCuotaActualizarFormaPago();
			cobrarCuotaBuscarMovimientoUeno(true);
		});
	}
}

function cobrarCuotaSetHoy(forzar) {
	var fecha = cobrarCuotaId("inptCobrarCuotaFechaPago");
	if (!fecha || (!forzar && fecha.value != "")) {
		return;
	}
	var f = new Date();
	var dia = String(f.getDate()).padStart(2, "0");
	var mes = String(f.getMonth() + 1).padStart(2, "0");
	fecha.value = f.getFullYear() + "-" + mes + "-" + dia;
}

function cobrarCuotaBloquearFechaProceso() {
	var fecha = cobrarCuotaId("inptCobrarCuotaFechaPago");
	if (!fecha) {
		return;
	}
	fecha.readOnly = true;
	fecha.setAttribute("aria-readonly", "true");
	fecha.setAttribute("tabindex", "-1");
	fecha.title = "Fecha automatica del dia de procesamiento";
}

function cobrarCuotaSetValor(id, valor) {
	var elem = cobrarCuotaId(id);
	if (elem) {
		elem.value = valor || "";
	}
}

function cobrarCuotaCargarTiposPago(callback) {
	var select = cobrarCuotaId("inptCobrarCuotaTipoPago");
	if (!select) {
		if (callback) {
			callback();
		}
		return;
	}
	if (cobrarCuotaTiposPagoCargados) {
		if (callback) {
			callback();
		}
		return;
	}
	obtener_datos_user();
	$.ajax({
		data: {
			useru: userid,
			passu: passuser,
			navegador: navegador,
			funt: "buscaroption"
		},
		url: "/GoodVentaAsisCap/php_system/abmTipoVenta.php",
		type: "post",
		success: function(responseText) {
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] == "exito") {
					select.innerHTML = "<option value=''>SELECCIONAR</option>" + datos[2];
					cobrarCuotaTiposPagoCargados = true;
					if (callback) {
						callback();
					}
				}
			} catch (error) {
				cobrarCuotaAviso("No se pudieron cargar las formas de pago", "error");
			}
		}
	});
}

function cobrarCuotaSeleccionarTipoPorTexto(texto) {
	var select = cobrarCuotaId("inptCobrarCuotaTipoPago");
	if (!select) {
		return;
	}
	var buscado = cobrarCuotaNormalizarTexto(texto);
	for (var i = 0; i < select.options.length; i++) {
		if (cobrarCuotaNormalizarTexto(select.options[i].text).indexOf(buscado) !== -1) {
			select.selectedIndex = i;
			break;
		}
	}
	cobrarCuotaActualizarFormaPago();
}

function cobrarCuotaActualizarFormaPago() {
	var select = cobrarCuotaId("inptCobrarCuotaTipoPago");
	var texto = cobrarCuotaTextoTipoPagoActual();
	var transferencia = cobrarCuotaEsTransferenciaTexto(texto);
	var panelTransfer = cobrarCuotaId("divCobrarCuotaTransferencia");
	var panelEfectivo = cobrarCuotaId("divCobrarCuotaEfectivo");
	if (panelTransfer) {
		panelTransfer.style.display = transferencia ? "" : "none";
	}
	if (panelEfectivo) {
		panelEfectivo.style.display = transferencia ? "none" : "";
	}
	if (!transferencia) {
		cobrarCuotaContextoUeno = null;
		cobrarCuotaResetBusquedaUeno();
		cobrarCuotaSetValor("inptCobrarCuotaComprobante", "");
		var ueno = cobrarCuotaId("divCobrarCuotaUeno");
		if (ueno) {
			ueno.innerHTML = "";
		}
	}
	cobrarCuotaActualizarEstadoMonto();
	if (cobrarCuotaCuotas.length > 0) {
		cobrarCuotaRenderCuotas();
	} else {
		cobrarCuotaMarcarFila("");
	}
	cobrarCuotaRenderSeleccionActual();
	cobrarCuotaActualizarEtiquetaBotonRegistrar();
	cobrarCuotaActualizarBotonRegistrar();
	if (transferencia) {
		cobrarCuotaBuscarMovimientoUeno();
	}
}

function cobrarCuotaActualizarEtiquetaBotonRegistrar() {
	var btn = cobrarCuotaId("btnCobrarCuotaRegistrar");
	if (!btn || cobrarCuotaProcesando) {
		return;
	}
	var texto = cobrarCuotaTextoTipoPagoActual();
	var transferencia = cobrarCuotaEsTransferenciaTexto(texto);
	var cuotas = cobrarCuotaObtenerCuotasSeleccionadas();
	if (transferencia && cobrarCuotaTieneMovimientoUenoValido()) {
		btn.value = cuotas.length > 1 ? "Registrar y conciliar " + cuotas.length + " cuotas" : "Registrar y conciliar";
	} else if (transferencia) {
		btn.value = "Selecciona transferencia Ueno";
	} else if (cuotas.length > 1) {
		btn.value = "Registrar " + cuotas.length + " cuotas";
	} else {
		btn.value = "Registrar cobro";
	}
}

function cobrarCuotaActualizarBotonRegistrar() {
	var btn = cobrarCuotaId("btnCobrarCuotaRegistrar");
	if (!btn) {
		return;
	}
	cobrarCuotaActualizarEtiquetaBotonRegistrar();
	var texto = cobrarCuotaTextoTipoPagoActual();
	var transferencia = cobrarCuotaEsTransferenciaTexto(texto);
	var bloquearPorUeno = transferencia && !cobrarCuotaTieneMovimientoUenoValido();
	var cuotas = cobrarCuotaObtenerCuotasSeleccionadas();
	if (transferencia && cobrarCuotaTieneMovimientoUenoValido()) {
		var totalSeleccionado = cobrarCuotaTotalSeleccionadas(cuotas);
		var montoSolicitado = cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "");
		if (cuotas.length > 1) {
			montoSolicitado = totalSeleccionado;
		}
		if (montoSolicitado > Number(cobrarCuotaContextoUeno.monto_disponible || 0)) {
			bloquearPorUeno = true;
		}
	}
	var seleccionValida = cuotas.length > 0;
	for (var i = 0; i < cuotas.length; i++) {
		if (!cobrarCuotaEsCuotaCobrable(cuotas[i])) {
			seleccionValida = false;
			break;
		}
	}
	btn.disabled = cobrarCuotaProcesando || !seleccionValida || bloquearPorUeno;
	if (!seleccionValida) {
		btn.title = "Selecciona una cuota para registrar el cobro";
	} else if (cuotas.length == 1 && !cobrarCuotaEsCuotaCobrable(cuotas[0])) {
		btn.title = "Esta cuota no se puede cobrar porque ya esta pagada o anulada";
	} else if (bloquearPorUeno) {
		btn.title = transferencia && cobrarCuotaTieneMovimientoUenoValido()
			? "El movimiento Ueno seleccionado no cubre el monto total."
			: "Selecciona una transferencia Ueno disponible para registrar el cobro.";
	} else {
		btn.title = "";
	}
}

function cobrarCuotaLimpiar() {
	cobrarCuotaCerrarModalExito();
	cobrarCuotaContextoUeno = null;
	cobrarCuotaResetBusquedaUeno();
	cobrarCuotaUltimoRecibo = null;
	cobrarCuotaUltimoPagoRegistrado = null;
	cobrarCuotaConfirmacionPendiente = null;
	cobrarCuotaSetValor("inptCobrarCuotaBuscar", "");
	cobrarCuotaSetValor("inptCobrarCuotaDocumento", "");
	cobrarCuotaSetValor("inptCobrarCuotaNombre", "");
	cobrarCuotaSetValor("inptCobrarCuotaVenta", "");
	cobrarCuotaSetValor("inptCobrarCuotaTelefono", "");
	cobrarCuotaLimpiarFlujo(true);
	cobrarCuotaLimpiarResultado();
}

function cobrarCuotaLimpiarFlujo(mostrarInicial) {
	cobrarCuotaClienteSeleccionado = null;
	cobrarCuotaPlanSeleccionado = null;
	cobrarCuotaClientes = [];
	cobrarCuotaPlanes = [];
	cobrarCuotaCuotas = [];
	cobrarCuotaFiltroEstado = "todas";
	cobrarCuotaActualizarFiltrosUI();
	cobrarCuotaLimpiarSeleccion();
	var clientes = cobrarCuotaId("divCobrarCuotaClientes");
	if (clientes) {
		clientes.innerHTML = mostrarInicial === false ? "" : "<div class='cobrar-cuota__placeholder'><b>Ingresa una cedula o nombre</b><span>La busqueda mostrara clientes con planes pendientes.</span></div>";
	}
	var planes = cobrarCuotaId("divCobrarCuotaPlanes");
	if (planes) {
		planes.innerHTML = "<div class='cobrar-cuota__placeholder'><b>Selecciona un cliente</b><span>Aqui apareceran sus planes vendidos ordenados por fecha.</span></div>";
	}
	cobrarCuotaRenderPlanSeleccionado(null);
	var tabla = cobrarCuotaId("table_cobrar_cuota_resultados");
	if (tabla) {
		tabla.innerHTML = "<div class='cobrar-cuota__empty'><b>Sin plan seleccionado</b><span>Selecciona un plan vendido para ver sus cuotas.</span></div>";
	}
	cobrarCuotaSetValor("inptCobrarCuotaTotalResultados", "");
	cobrarCuotaSetValor("inptCobrarCuotaTotalSaldo", "");
}

function cobrarCuotaLimpiarSeleccion() {
	cobrarCuotaSeleccionada = null;
	cobrarCuotaSeleccionadas = [];
	cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", "");
	cobrarCuotaSetValor("inptCobrarCuotaMontoRecibido", "");
	cobrarCuotaSetValor("inptCobrarCuotaVuelto", "");
	if (!cobrarCuotaContextoUeno) {
		cobrarCuotaSetValor("inptCobrarCuotaComprobante", "");
	} else if (cobrarCuotaContextoUeno.nro_comprobante) {
		cobrarCuotaSetValor("inptCobrarCuotaComprobante", cobrarCuotaContextoUeno.nro_comprobante || "");
	}
	cobrarCuotaSetValor("txtCobrarCuotaObservacion", "");
	var contenedor = cobrarCuotaId("divCobrarCuotaSeleccionada");
	if (contenedor) {
		contenedor.innerHTML = "<div class='cobrar-cuota__placeholder'><b>Selecciona una cuota</b><span>Primero selecciona un plan y luego una cuota pendiente.</span></div>";
	}
	cobrarCuotaActualizarEstadoMonto();
	cobrarCuotaMarcarFila("");
	cobrarCuotaActualizarResumenUeno();
	cobrarCuotaActualizarBotonRegistrar();
}

function cobrarCuotaLimpiarDatosCobroActual(limpiarResultado) {
	cobrarCuotaContextoUeno = null;
	cobrarCuotaResetBusquedaUeno();
	cobrarCuotaSeleccionada = null;
	cobrarCuotaSeleccionadas = [];
	cobrarCuotaConfirmacionPendiente = null;
	cobrarCuotaSetHoy(true);
	cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", "");
	cobrarCuotaSetValor("inptCobrarCuotaMontoRecibido", "");
	cobrarCuotaSetValor("inptCobrarCuotaVuelto", "");
	cobrarCuotaSetValor("inptCobrarCuotaComprobante", "");
	cobrarCuotaSetValor("inptCobrarCuotaBanco", "Ueno");
	cobrarCuotaSetValor("txtCobrarCuotaObservacion", "");
	var modalConfirmacion = cobrarCuotaId("divCobrarCuotaConfirmacion");
	if (modalConfirmacion) {
		modalConfirmacion.style.display = "none";
	}
	var contenedor = cobrarCuotaId("divCobrarCuotaSeleccionada");
	if (contenedor) {
		contenedor.innerHTML = "<div class='cobrar-cuota__placeholder'><b>Selecciona una cuota</b><span>Primero selecciona un plan y luego una cuota pendiente.</span></div>";
	}
	var ueno = cobrarCuotaId("divCobrarCuotaUeno");
	if (ueno) {
		ueno.innerHTML = "";
	}
	cobrarCuotaMarcarFila("");
	cobrarCuotaActualizarEstadoMonto();
	if (limpiarResultado !== false) {
		cobrarCuotaLimpiarResultado();
	}
	if (cobrarCuotaTiposPagoCargados) {
		cobrarCuotaSeleccionarTipoPorTexto("EFECTIVO");
	} else {
		cobrarCuotaCargarTiposPago(function() {
			cobrarCuotaSeleccionarTipoPorTexto("EFECTIVO");
		});
	}
	cobrarCuotaActualizarBotonRegistrar();
}

function cobrarCuotaLimpiarResultado() {
	var resultado = cobrarCuotaId("divCobrarCuotaResultado");
	if (resultado) {
		resultado.innerHTML = "";
	}
	var ueno = cobrarCuotaId("divCobrarCuotaUeno");
	if (ueno) {
		ueno.innerHTML = "";
	}
	cobrarCuotaResetBusquedaUeno();
}

function cobrarCuotaBuscarEnter(evento) {
	evento = evento || window.event;
	var tecla = evento.key || evento.keyCode;
	if (tecla === "Enter" || tecla === 13) {
		if (evento.preventDefault) {
			evento.preventDefault();
		}
		cobrarCuotaBuscar();
	}
}

function cobrarCuotaObtenerBusqueda() {
	var principal = cobrarCuotaId("inptCobrarCuotaBuscar");
	if (principal) {
		return principal.value;
	}
	var doc = cobrarCuotaId("inptCobrarCuotaDocumento") ? cobrarCuotaId("inptCobrarCuotaDocumento").value : "";
	var nombre = cobrarCuotaId("inptCobrarCuotaNombre") ? cobrarCuotaId("inptCobrarCuotaNombre").value : "";
	return doc || nombre;
}

function cobrarCuotaBuscar() {
	var query = cobrarCuotaObtenerBusqueda();
	if (String(query || "").trim() == "") {
		cobrarCuotaAviso("Ingresa una cedula o nombre para buscar.");
		return;
	}
	cobrarCuotaLimpiarFlujo(false);
	var clientes = cobrarCuotaId("divCobrarCuotaClientes");
	if (clientes) {
		clientes.innerHTML = (typeof paginacargando != "undefined" ? paginacargando : "Buscando...");
	}
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "buscar_clientes");
	datos.append("query", query);
	datos.append("query_documento", cobrarCuotaNormalizarDocumento(query));

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCobrarCuota.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function(jqXHR, textstatus) {
			if (typeof manejadordeerroresjquery === "function") {
				manejadordeerroresjquery(jqXHR.status, textstatus, "cobrarCuotaBuscar");
			}
			if (clientes) {
				clientes.innerHTML = "<div class='cobrar-cuota__empty'><b>No se pudo buscar</b><span>Revisa la conexion e intenta nuevamente.</span></div>";
			}
		},
		success: function(responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					cobrarCuotaClientes = Array.isArray(respuesta.clientes) ? respuesta.clientes : [];
					cobrarCuotaRenderClientes();
					if (cobrarCuotaClientes.length == 1) {
						cobrarCuotaSeleccionarCliente(cobrarCuotaClientes[0].cliente_id);
					}
					return;
				}
				cobrarCuotaAviso(respuesta["2"] || "No se pudo buscar clientes", "error");
				if (clientes) {
					clientes.innerHTML = "<div class='cobrar-cuota__empty'><b>No encontramos clientes</b><span>Proba con otra cedula o nombre.</span></div>";
				}
			} catch (error) {
				cobrarCuotaAviso("No se pudo interpretar la busqueda de clientes", "error");
				if (clientes) {
					clientes.innerHTML = "<div class='cobrar-cuota__empty'><b>No se pudo interpretar la busqueda</b><span>Intenta nuevamente.</span></div>";
				}
			}
		}
	});
}

function cobrarCuotaBuscarClientePorId(clienteId) {
	for (var i = 0; i < cobrarCuotaClientes.length; i++) {
		if (String(cobrarCuotaClientes[i].cliente_id) == String(clienteId)) {
			return cobrarCuotaClientes[i];
		}
	}
	return null;
}

function cobrarCuotaBuscarPlanPorId(ventaId) {
	for (var i = 0; i < cobrarCuotaPlanes.length; i++) {
		if (String(cobrarCuotaPlanes[i].cod_venta || cobrarCuotaPlanes[i].venta_id) == String(ventaId)) {
			return cobrarCuotaPlanes[i];
		}
	}
	return null;
}

function cobrarCuotaBuscarCuotaPorId(idcredito) {
	for (var i = 0; i < cobrarCuotaCuotas.length; i++) {
		if (String(cobrarCuotaCuotas[i].idcredito) == String(idcredito)) {
			return cobrarCuotaCuotas[i];
		}
	}
	return null;
}

function cobrarCuotaCuotaCoincideFiltro(cuota) {
	var estado = cobrarCuotaNormalizarTexto(cuota ? cuota.estado : "");
	if (cobrarCuotaFiltroEstado == "pagadas") {
		return estado == "PAGADA";
	}
	if (cobrarCuotaFiltroEstado == "parciales") {
		return estado == "PAGO PARCIAL";
	}
	if (cobrarCuotaFiltroEstado == "pendientes") {
		return estado == "PENDIENTE" || estado == "VENCIDA";
	}
	return true;
}

function cobrarCuotaActualizarFiltrosUI() {
	var contenedor = cobrarCuotaId("divCobrarCuotaFiltros");
	if (!contenedor) {
		return;
	}
	var botones = contenedor.querySelectorAll("[data-cobrar-filtro]");
	for (var i = 0; i < botones.length; i++) {
		var activo = botones[i].getAttribute("data-cobrar-filtro") == cobrarCuotaFiltroEstado;
		if (activo) {
			botones[i].classList.add("cobrar-cuota__filter--active");
		} else {
			botones[i].classList.remove("cobrar-cuota__filter--active");
		}
	}
}

function cobrarCuotaCambiarFiltro(filtro) {
	cobrarCuotaFiltroEstado = filtro || "todas";
	cobrarCuotaActualizarFiltrosUI();
	cobrarCuotaRenderCuotas();
}

function cobrarCuotaRenderClientes() {
	var contenedor = cobrarCuotaId("divCobrarCuotaClientes");
	if (!contenedor) {
		return;
	}
	if (!cobrarCuotaClientes.length) {
		contenedor.innerHTML = "<div class='cobrar-cuota__empty'><b>No encontramos clientes</b><span>Proba con otra cedula o nombre.</span></div>";
		return;
	}
	var html = "";
	for (var i = 0; i < cobrarCuotaClientes.length; i++) {
		var cliente = cobrarCuotaClientes[i];
		var seleccionado = cobrarCuotaClienteSeleccionado && String(cobrarCuotaClienteSeleccionado.cliente_id) == String(cliente.cliente_id);
		html += "<div class='cobrar-cuota__client-card" + (seleccionado ? " cobrar-cuota__client-card--selected" : "") + "' data-cobrar-cliente='" + cobrarCuotaEscape(cliente.cliente_id) + "'>"
			+ "<div><b>" + cobrarCuotaEscape(cliente.cliente || "Sin nombre") + "</b>"
			+ "<span>Cedula " + cobrarCuotaEscape(cliente.cedula || "-") + (cliente.telefono ? " - Tel. " + cobrarCuotaEscape(cliente.telefono) : "") + "</span></div>"
			+ "<div class='cobrar-cuota__client-meta'><strong>" + cobrarCuotaEscape(cliente.planes || "0") + "</strong><span>planes</span></div>"
			+ "<div class='cobrar-cuota__client-meta'><strong>" + cobrarCuotaEscape(cliente.saldo_total_fmt || "0") + "</strong><span>saldo</span></div>"
			+ "<input type='button' value='Ver planes' class='btn4 cobrar-cuota__btn-secundario' onclick='cobrarCuotaSeleccionarCliente(\"" + cobrarCuotaEscape(cliente.cliente_id) + "\")'>"
			+ "</div>";
	}
	contenedor.innerHTML = html;
}

function cobrarCuotaSeleccionarCliente(clienteId) {
	var cliente = cobrarCuotaBuscarClientePorId(clienteId);
	if (!cliente) {
		cobrarCuotaAviso("No se encontro el cliente seleccionado", "error");
		return;
	}
	cobrarCuotaClienteSeleccionado = cliente;
	cobrarCuotaPlanSeleccionado = null;
	cobrarCuotaPlanes = [];
	cobrarCuotaCuotas = [];
	cobrarCuotaLimpiarSeleccion();
	cobrarCuotaRenderClientes();
	cobrarCuotaRenderPlanSeleccionado(null);
	cobrarCuotaSetValor("inptCobrarCuotaTotalResultados", "");
	cobrarCuotaSetValor("inptCobrarCuotaTotalSaldo", "");
	var cuotas = cobrarCuotaId("table_cobrar_cuota_resultados");
	if (cuotas) {
		cuotas.innerHTML = "<div class='cobrar-cuota__empty'><b>Selecciona un plan</b><span>Este paso evita mezclar cuotas de distintas ventas.</span></div>";
	}
	cobrarCuotaCargarPlanes(cliente.cliente_id, "");
}

function cobrarCuotaCargarDesdeVenta(ventaId) {
	if (String(ventaId || "").trim() == "") {
		return;
	}
	cobrarCuotaLimpiarFlujo(false);
	var planes = cobrarCuotaId("divCobrarCuotaPlanes");
	if (planes) {
		planes.innerHTML = (typeof paginacargando != "undefined" ? paginacargando : "Buscando plan...");
	}
	cobrarCuotaCargarPlanes("", ventaId);
}

function cobrarCuotaCargarPlanes(clienteId, ventaId) {
	var planes = cobrarCuotaId("divCobrarCuotaPlanes");
	if (planes) {
		planes.innerHTML = (typeof paginacargando != "undefined" ? paginacargando : "Buscando planes...");
	}
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "listar_planes");
	datos.append("cliente_id", clienteId || "");
	datos.append("venta", ventaId || "");

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCobrarCuota.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function(jqXHR, textstatus) {
			if (typeof manejadordeerroresjquery === "function") {
				manejadordeerroresjquery(jqXHR.status, textstatus, "cobrarCuotaCargarPlanes");
			}
			if (planes) {
				planes.innerHTML = "<div class='cobrar-cuota__empty'><b>No se pudieron cargar planes</b><span>Intenta nuevamente.</span></div>";
			}
		},
		success: function(responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					cobrarCuotaPlanes = Array.isArray(respuesta.planes) ? respuesta.planes : [];
					if (!cobrarCuotaClienteSeleccionado && cobrarCuotaPlanes.length) {
						cobrarCuotaClienteSeleccionado = {
							cliente_id: cobrarCuotaPlanes[0].cliente_id,
							cliente: cobrarCuotaPlanes[0].cliente,
							cedula: cobrarCuotaPlanes[0].cedula,
							telefono: cobrarCuotaPlanes[0].telefono,
							planes: cobrarCuotaPlanes.length,
							saldo_total_fmt: cobrarCuotaPlanes[0].saldo_pendiente_total_fmt
						};
						cobrarCuotaClientes = [cobrarCuotaClienteSeleccionado];
						cobrarCuotaRenderClientes();
					}
					cobrarCuotaRenderPlanes();
					if (cobrarCuotaPlanes.length > 0 && (cobrarCuotaPlanes.length == 1 || ventaId)) {
						cobrarCuotaSeleccionarPlan(cobrarCuotaPlanes[0].cod_venta || cobrarCuotaPlanes[0].venta_id);
					}
					return;
				}
				if (planes) {
					planes.innerHTML = "<div class='cobrar-cuota__empty'><b>Sin planes pendientes</b><span>" + cobrarCuotaEscape(respuesta["2"] || "Este cliente no tiene planes pendientes.") + "</span></div>";
				}
			} catch (error) {
				if (planes) {
					planes.innerHTML = "<div class='cobrar-cuota__empty'><b>No se pudo interpretar la lista de planes</b><span>Intenta nuevamente.</span></div>";
				}
			}
		}
	});
}

function cobrarCuotaRenderPlanes() {
	var contenedor = cobrarCuotaId("divCobrarCuotaPlanes");
	if (!contenedor) {
		return;
	}
	if (!cobrarCuotaPlanes.length) {
		cobrarCuotaPlanSeleccionado = null;
		cobrarCuotaCuotas = [];
		cobrarCuotaLimpiarSeleccion();
		cobrarCuotaRenderPlanSeleccionado(null);
		cobrarCuotaSetValor("inptCobrarCuotaTotalResultados", "");
		cobrarCuotaSetValor("inptCobrarCuotaTotalSaldo", "");
		var tabla = cobrarCuotaId("table_cobrar_cuota_resultados");
		if (tabla) {
			tabla.innerHTML = "<div class='cobrar-cuota__empty'><b>Sin cuotas pendientes</b><span>No hay cuotas para cobrar en este plan o cliente.</span></div>";
		}
		contenedor.innerHTML = "<div class='cobrar-cuota__empty'><b>Sin planes pendientes</b><span>No hay ventas con cuotas pendientes para este cliente.</span></div>";
		return;
	}
	var aviso = cobrarCuotaPlanes.length > 1 ? "<div class='cobrar-cuota__hint'>Este cliente tiene varios planes. Selecciona el plan correcto para ver sus cuotas.</div>" : "";
	var html = aviso;
	for (var i = 0; i < cobrarCuotaPlanes.length; i++) {
		var plan = cobrarCuotaPlanes[i];
		var ventaId = plan.cod_venta || plan.venta_id;
		var seleccionado = cobrarCuotaPlanSeleccionado && String(cobrarCuotaPlanSeleccionado.cod_venta || cobrarCuotaPlanSeleccionado.venta_id) == String(ventaId);
		html += "<div class='cobrar-cuota__plan-card" + (seleccionado ? " cobrar-cuota__plan-card--selected" : "") + "' data-cobrar-plan='" + cobrarCuotaEscape(ventaId) + "'>"
			+ "<div class='cobrar-cuota__plan-main'>"
			+ "<b>Venta " + cobrarCuotaEscape(plan.venta || ventaId) + "</b>"
			+ (plan.alias ? "<span class='cobrar-cuota__alias'>Alias: " + cobrarCuotaEscape(plan.alias) + "</span>" : "")
			+ "<span>" + cobrarCuotaEscape(plan.producto || "Venta sin detalle visible") + "</span>"
			+ "</div>"
			+ "<div class='cobrar-cuota__plan-data'><span>Fecha</span><b>" + cobrarCuotaEscape(plan.fecha_venta || "-") + "</b></div>"
			+ "<div class='cobrar-cuota__plan-data'><span>Cuotas</span><b>" + cobrarCuotaEscape(plan.cuotas_pendientes || "0") + "</b></div>"
			+ "<div class='cobrar-cuota__plan-data'><span>Saldo</span><b>" + cobrarCuotaEscape(plan.saldo_pendiente_total_fmt || "0") + "</b></div>"
			+ "<input type='button' value='Seleccionar plan' class='btn4 cobrar-cuota__btn-tabla' onclick='cobrarCuotaSeleccionarPlan(\"" + cobrarCuotaEscape(ventaId) + "\")'>"
			+ "</div>";
	}
	contenedor.innerHTML = html;
}

function cobrarCuotaSeleccionarPlan(ventaId) {
	var plan = cobrarCuotaBuscarPlanPorId(ventaId);
	if (!plan) {
		cobrarCuotaAviso("No se encontro el plan seleccionado", "error");
		return;
	}
	cobrarCuotaPlanSeleccionado = plan;
	cobrarCuotaCuotas = [];
	cobrarCuotaFiltroEstado = "todas";
	cobrarCuotaActualizarFiltrosUI();
	cobrarCuotaLimpiarSeleccion();
	cobrarCuotaRenderPlanes();
	cobrarCuotaRenderPlanSeleccionado(plan);
	cobrarCuotaCargarCuotas(plan.cod_venta || plan.venta_id);
}

function cobrarCuotaRenderPlanSeleccionado(plan) {
	var contenedor = cobrarCuotaId("divCobrarCuotaPlanSeleccionado");
	if (!contenedor) {
		return;
	}
	if (!plan) {
		contenedor.innerHTML = "";
		return;
	}
	contenedor.innerHTML = "<div class='cobrar-cuota__selected-plan'>"
		+ "<div><span>Plan seleccionado</span><b>Venta " + cobrarCuotaEscape(plan.venta || plan.cod_venta) + "</b></div>"
		+ "<div><span>Cliente</span><b>" + cobrarCuotaEscape(plan.cliente || "-") + "</b></div>"
		+ "<div><span>Cedula</span><b>" + cobrarCuotaEscape(plan.cedula || "-") + "</b></div>"
		+ (plan.alias ? "<div><span>Alias / apodo</span><b>" + cobrarCuotaEscape(plan.alias) + "</b></div>" : "")
		+ "<div class='cobrar-cuota__wide'><span>Producto / plan</span><b>" + cobrarCuotaEscape(plan.producto || "-") + "</b></div>"
		+ "<div><span>Fecha</span><b>" + cobrarCuotaEscape(plan.fecha_venta || "-") + "</b></div>"
		+ "<div><span>Cuotas pendientes</span><b>" + cobrarCuotaEscape(plan.cuotas_pendientes || "0") + "</b></div>"
		+ "<div><span>Saldo pendiente</span><b>" + cobrarCuotaEscape(plan.saldo_pendiente_total_fmt || "0") + "</b></div>"
		+ "</div>";
}

function cobrarCuotaCargarCuotas(ventaId) {
	var tabla = cobrarCuotaId("table_cobrar_cuota_resultados");
	if (tabla) {
		tabla.innerHTML = (typeof paginacargando != "undefined" ? paginacargando : "Buscando cuotas...");
	}
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "listar_cuotas");
	datos.append("venta", ventaId || "");
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCobrarCuota.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function(jqXHR, textstatus) {
			if (typeof manejadordeerroresjquery === "function") {
				manejadordeerroresjquery(jqXHR.status, textstatus, "cobrarCuotaCargarCuotas");
			}
			if (tabla) {
				tabla.innerHTML = "<div class='cobrar-cuota__empty'><b>No se pudieron cargar cuotas</b><span>Intenta nuevamente.</span></div>";
			}
		},
		success: function(responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					cobrarCuotaCuotas = Array.isArray(respuesta.cuotas) ? respuesta.cuotas : [];
					cobrarCuotaSetValor("inptCobrarCuotaTotalResultados", respuesta.total || "0");
					cobrarCuotaSetValor("inptCobrarCuotaTotalSaldo", respuesta.saldo_total || "0");
					if (cobrarCuotaPlanSeleccionado) {
						cobrarCuotaPlanSeleccionado.cuotas_pendientes = respuesta.total || "0";
						cobrarCuotaPlanSeleccionado.saldo_pendiente_total_fmt = respuesta.saldo_total || "0";
						cobrarCuotaPlanSeleccionado.saldo_pendiente_total = respuesta.saldo_total_num !== undefined ? Number(respuesta.saldo_total_num || 0) : cobrarCuotaNumero(respuesta.saldo_total || "0");
						if (respuesta.total_venta !== undefined) {
							cobrarCuotaPlanSeleccionado.total_venta = Number(respuesta.total_venta || 0);
							cobrarCuotaPlanSeleccionado.total_venta_fmt = respuesta.total_venta_fmt || cobrarCuotaFormato(respuesta.total_venta || 0);
						}
						var ventaPlanActual = String(cobrarCuotaPlanSeleccionado.cod_venta || cobrarCuotaPlanSeleccionado.venta_id || "");
						for (var i = 0; i < cobrarCuotaPlanes.length; i++) {
							if (String(cobrarCuotaPlanes[i].cod_venta || cobrarCuotaPlanes[i].venta_id || "") == ventaPlanActual) {
								cobrarCuotaPlanes[i].cuotas_pendientes = respuesta.total || "0";
								cobrarCuotaPlanes[i].saldo_pendiente_total_fmt = respuesta.saldo_total || "0";
								cobrarCuotaPlanes[i].saldo_pendiente_total = respuesta.saldo_total_num !== undefined ? Number(respuesta.saldo_total_num || 0) : cobrarCuotaNumero(respuesta.saldo_total || "0");
								if (respuesta.total_venta !== undefined) {
									cobrarCuotaPlanes[i].total_venta = Number(respuesta.total_venta || 0);
									cobrarCuotaPlanes[i].total_venta_fmt = respuesta.total_venta_fmt || cobrarCuotaFormato(respuesta.total_venta || 0);
								}
							}
						}
						cobrarCuotaRenderPlanSeleccionado(cobrarCuotaPlanSeleccionado);
						cobrarCuotaRenderPlanes();
					}
					cobrarCuotaRenderCuotas();
					return;
				}
				if (tabla) {
					tabla.innerHTML = "<div class='cobrar-cuota__empty'><b>Sin cuotas</b><span>" + cobrarCuotaEscape(respuesta["2"] || "Este plan no tiene cuotas cargadas.") + "</span></div>";
				}
			} catch (error) {
				if (tabla) {
					tabla.innerHTML = "<div class='cobrar-cuota__empty'><b>No se pudo interpretar la lista de cuotas</b><span>Intenta nuevamente.</span></div>";
				}
			}
		}
	});
}

function cobrarCuotaRenderCuotas() {
	var tabla = cobrarCuotaId("table_cobrar_cuota_resultados");
	if (!tabla) {
		return;
	}
	if (!cobrarCuotaPlanSeleccionado) {
		tabla.innerHTML = "<div class='cobrar-cuota__empty'><b>Sin plan seleccionado</b><span>Selecciona un plan vendido para ver sus cuotas.</span></div>";
		return;
	}
	if (!cobrarCuotaCuotas.length) {
		tabla.innerHTML = "<div class='cobrar-cuota__empty'><b>Este plan no tiene cuotas cargadas</b><span>Puedes elegir otro plan del cliente.</span></div>";
		return;
	}
	var html = "";
	var totalVisibles = 0;
	for (var i = 0; i < cobrarCuotaCuotas.length; i++) {
		var cuota = cobrarCuotaCuotas[i];
		if (!cobrarCuotaCuotaCoincideFiltro(cuota)) {
			continue;
		}
		totalVisibles++;
		var seleccionado = cobrarCuotaEstaSeleccionada(cuota.idcredito);
		var cobrable = cobrarCuotaEsCuotaCobrable(cuota);
		var estadoSlug = cobrarCuotaEstadoSlug(cuota.estado);
		var controlSeleccion = "";
		var accion = "";
		if (cobrable) {
			controlSeleccion = "<input type='checkbox' name='cobrarCuotaCheck' " + (seleccionado ? "checked" : "") + " onclick='cobrarCuotaToggleSeleccionPorId(\"" + cobrarCuotaEscape(cuota.idcredito) + "\", this.checked)'>";
			accion = "<input type='button' value='" + (seleccionado ? "Quitar" : "Agregar cuota") + "' class='btn4 cobrar-cuota__btn-tabla' onclick='cobrarCuotaToggleSeleccionPorId(\"" + cobrarCuotaEscape(cuota.idcredito) + "\")'>";
		} else {
			controlSeleccion = "<span class='cobrar-cuota__radio-placeholder'></span>";
			accion = "<span class='cobrar-cuota__accion-segura'>" + (cobrarCuotaNormalizarTexto(cuota.estado) == "PAGADA" ? "Cobro registrado" : "No cobrable") + "</span>";
		}
		html += "<table class='tableRegistroSearch cobrar-cuota__result-table' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' class='cobrar-cuota__result-row" + (seleccionado ? " cobrar-cuota__result-row--selected" : "") + (!cobrable ? " cobrar-cuota__result-row--locked" : "") + "' data-cobrar-cuota-id='" + cobrarCuotaEscape(cuota.idcredito) + "'>"
			+ "<td data-label='Seleccionar' style='width:6%;text-align:center'>" + controlSeleccion + "</td>"
			+ "<td data-label='Cuota' style='width:13%;text-align:center'>" + cobrarCuotaEscape(cuota.cuota) + "</td>"
			+ "<td data-label='Vencimiento' style='width:14%;text-align:center'>" + cobrarCuotaEscape(cuota.fecha_vencimiento) + "</td>"
			+ "<td data-label='Monto' style='width:14%;text-align:right'>" + cobrarCuotaEscape(cuota.monto_cuota) + "</td>"
			+ "<td data-label='Pagado' style='width:14%;text-align:right'>" + cobrarCuotaEscape(cuota.pagado_total || "0") + "</td>"
			+ "<td data-label='Saldo' style='width:14%;text-align:right'>" + cobrarCuotaEscape(cuota.saldo_pendiente) + "</td>"
			+ "<td data-label='Estado' style='width:12%;text-align:center'><span class='cobrar-cuota__estado cobrar-cuota__estado--" + cobrarCuotaEscape(estadoSlug) + "'>" + cobrarCuotaEscape(cuota.estado) + "</span></td>"
			+ "<td data-label='Accion' style='width:13%;text-align:center'>" + accion + "</td>"
			+ "</tr></table>";
	}
	if (totalVisibles <= 0) {
		tabla.innerHTML = "<div class='cobrar-cuota__empty'><b>Sin cuotas para este filtro</b><span>Cambia el filtro para ver el historial completo del plan.</span></div>";
		return;
	}
	tabla.innerHTML = html;
}

function cobrarCuotaSeleccionarPorId(idcredito) {
	var cuota = cobrarCuotaBuscarCuotaPorId(idcredito);
	if (!cuota) {
		cobrarCuotaAviso("No se encontro la cuota seleccionada", "error");
		return;
	}
	if (!cobrarCuotaEsCuotaCobrable(cuota)) {
		cobrarCuotaAviso("Esta cuota ya no puede cobrarse. Queda visible solo para control y trazabilidad.", "error");
		return;
	}
	cobrarCuotaSeleccionar(cuota);
}

function cobrarCuotaToggleSeleccionPorId(idcredito, forzarEstado) {
	var cuota = cobrarCuotaBuscarCuotaPorId(idcredito);
	if (!cuota) {
		cobrarCuotaAviso("No se encontro la cuota seleccionada", "error");
		return;
	}
	if (!cobrarCuotaEsCuotaCobrable(cuota)) {
		cobrarCuotaAviso("Esta cuota ya no puede cobrarse. Queda visible solo para control y trazabilidad.", "error");
		return;
	}
	var estaSeleccionada = cobrarCuotaEstaSeleccionada(idcredito);
	var agregar = typeof forzarEstado == "boolean" ? forzarEstado : !estaSeleccionada;
	if (agregar) {
		cobrarCuotaAgregarSeleccion(cuota);
	} else {
		cobrarCuotaQuitarSeleccion(idcredito);
	}
	cobrarCuotaActualizarSeleccionVisual(true);
}

function cobrarCuotaSeleccionar(cuota) {
	cobrarCuotaSeleccionada = cuota || null;
	cobrarCuotaSeleccionadas = cobrarCuotaSeleccionada ? [cobrarCuotaSeleccionada] : [];
	if (!cobrarCuotaSeleccionada) {
		return;
	}
	if (cobrarCuotaPlanSeleccionado && String(cobrarCuotaSeleccionada.venta_id || cobrarCuotaSeleccionada.cod_venta) != String(cobrarCuotaPlanSeleccionado.cod_venta || cobrarCuotaPlanSeleccionado.venta_id)) {
		cobrarCuotaSeleccionada = null;
		cobrarCuotaSeleccionadas = [];
		cobrarCuotaAviso("La cuota seleccionada no pertenece al plan elegido", "error");
		cobrarCuotaActualizarBotonRegistrar();
		return;
	}
	if (!cobrarCuotaEsCuotaCobrable(cobrarCuotaSeleccionada)) {
		cobrarCuotaSeleccionada = null;
		cobrarCuotaSeleccionadas = [];
		cobrarCuotaAviso("No se puede cobrar una cuota pagada o anulada", "error");
		cobrarCuotaActualizarBotonRegistrar();
		return;
	}
	cobrarCuotaMarcarFila(cobrarCuotaSeleccionada.idcredito);
	cobrarCuotaActualizarMontosDesdeSeleccion(true);
	cobrarCuotaRenderSeleccionActual();
	cobrarCuotaBuscarMovimientoUeno();
	cobrarCuotaActualizarResumenUeno();
	cobrarCuotaActualizarBotonRegistrar();
}

function cobrarCuotaMarcarFila(idcredito) {
	var contenedor = cobrarCuotaId("table_cobrar_cuota_resultados");
	if (!contenedor) {
		return;
	}
	var filas = contenedor.querySelectorAll("[data-cobrar-cuota-id]");
	for (var i = 0; i < filas.length; i++) {
		filas[i].classList.remove("cobrar-cuota__result-row--selected");
		var idFila = filas[i].getAttribute("data-cobrar-cuota-id");
		var seleccionada = cobrarCuotaEstaSeleccionada(idFila);
		if (String(idcredito || "") != "" && String(idFila) == String(idcredito)) {
			seleccionada = true;
		}
		var control = filas[i].querySelector("input[type='radio'], input[type='checkbox']");
		if (control) {
			control.checked = seleccionada;
		}
		if (seleccionada) {
			filas[i].classList.add("cobrar-cuota__result-row--selected");
		}
	}
}

function cobrarCuotaCalcularVuelto() {
	var recibido = cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoRecibido") ? cobrarCuotaId("inptCobrarCuotaMontoRecibido").value : "");
	var cobrar = cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "");
	var vuelto = Math.max(0, recibido - cobrar);
	cobrarCuotaSetValor("inptCobrarCuotaVuelto", cobrarCuotaFormato(vuelto));
	cobrarCuotaActualizarResumenUeno();
}

function cobrarCuotaBuscarSiguienteCuotaCubierta(sobrante) {
	sobrante = Number(sobrante) || 0;
	if (sobrante <= 0) {
		return null;
	}
	var seleccionadas = cobrarCuotaObtenerCuotasSeleccionadas();
	for (var i = 0; i < cobrarCuotaCuotas.length; i++) {
		var cuota = cobrarCuotaCuotas[i];
		if (!cobrarCuotaEsCuotaCobrable(cuota)) {
			continue;
		}
		var yaSeleccionada = false;
		for (var j = 0; j < seleccionadas.length; j++) {
			if (String(cuota.idcredito) == String(seleccionadas[j].idcredito)) {
				yaSeleccionada = true;
				break;
			}
		}
		if (yaSeleccionada) {
			continue;
		}
		if (Number(cuota.saldo_pendiente_num || 0) > 0 && Number(cuota.saldo_pendiente_num || 0) <= sobrante) {
			return cuota;
		}
	}
	return null;
}

function cobrarCuotaResumenUeno() {
	if (!cobrarCuotaContextoUeno || !cobrarCuotaContextoUeno.id_movimiento) {
		return null;
	}
	var cuotas = cobrarCuotaObtenerCuotasSeleccionadas();
	var disponible = Number(cobrarCuotaContextoUeno.monto_disponible || 0);
	var montoSolicitado = cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "");
	var saldoCuota = cuotas.length > 1 ? cobrarCuotaTotalSeleccionadas(cuotas) : (cobrarCuotaSeleccionada ? Number(cobrarCuotaSeleccionada.saldo_pendiente_num || 0) : 0);
	var baseAplicar = montoSolicitado > 0 ? montoSolicitado : saldoCuota;
	var montoAplicable = Math.min(baseAplicar, disponible);
	if (saldoCuota > 0) {
		montoAplicable = Math.min(montoAplicable, saldoCuota);
	}
	montoAplicable = Math.max(0, montoAplicable);
	var resultado = "exacto";
	var diferencia = 0;
	var mensaje = "Cuota cubierta completamente";
	if (saldoCuota > 0 && montoAplicable < saldoCuota) {
		resultado = "falta";
		diferencia = saldoCuota - montoAplicable;
		mensaje = "Faltan: " + cobrarCuotaFormato(diferencia);
	} else if (disponible > montoAplicable) {
		resultado = "sobra";
		diferencia = disponible - montoAplicable;
		mensaje = "Saldo a favor: " + cobrarCuotaFormato(diferencia);
	}
	return {
		disponible: disponible,
		montoSolicitado: montoSolicitado,
		montoAplicable: montoAplicable,
		saldoCuota: saldoCuota,
		cantidadCuotas: cuotas.length,
		resultado: resultado,
		diferencia: diferencia,
		mensaje: mensaje,
		siguiente: cobrarCuotaBuscarSiguienteCuotaCubierta(diferencia)
	};
}

function cobrarCuotaRenderResumenUeno() {
	var resumen = cobrarCuotaResumenUeno();
	if (!resumen) {
		return "";
	}
	var html = "<div class='cobrar-cuota__ueno-resumen cobrar-cuota__ueno-resumen--" + cobrarCuotaEscape(resumen.resultado) + "'>"
		+ "<b>Resumen de aplicacion</b>"
		+ "<div class='cobrar-cuota__ueno-resumen-grid'>"
		+ "<span><small>Disponible en movimiento</small><strong>" + cobrarCuotaEscape(cobrarCuotaFormato(resumen.disponible)) + "</strong></span>"
		+ "<span><small>Monto a aplicar</small><strong>" + cobrarCuotaEscape(cobrarCuotaFormato(resumen.montoAplicable)) + "</strong></span>"
		+ "<span><small>Resultado</small><strong>" + cobrarCuotaEscape(resumen.mensaje) + "</strong></span>"
		+ "</div>";
	if (resumen.resultado == "falta") {
		if (resumen.cantidadCuotas > 1) {
			html += "<div class='cobrar-cuota__ueno-note'>El movimiento no cubre todas las cuotas seleccionadas. Se aplicara en orden y la ultima cuota alcanzada quedara parcial.</div>";
		} else {
			html += "<div class='cobrar-cuota__ueno-note'>La cuota quedara como Pago parcial si registras este monto.</div>";
		}
		if (resumen.montoAplicable > 0 && resumen.montoSolicitado != resumen.montoAplicable) {
			html += "<button type='button' class='cobrar-cuota__ueno-mini-btn' onclick='cobrarCuotaUsarDisponibleUeno()'>Usar disponible como pago parcial</button>";
		}
	}
	if (resumen.resultado == "sobra") {
		html += "<div class='cobrar-cuota__ueno-note'>Despues de registrar este cobro quedara saldo disponible del movimiento.</div>";
		if (resumen.siguiente) {
			html += "<div class='cobrar-cuota__ueno-note'>Esta transferencia permite cubrir otra cuota. Selecciona la siguiente cuota para aplicarla manualmente.</div>";
		}
	}
	html += "</div>";
	return html;
}

function cobrarCuotaActualizarResumenUeno() {
	if (!cobrarCuotaContextoUeno || !cobrarCuotaContextoUeno.id_movimiento) {
		return;
	}
	cobrarCuotaMostrarContextoUeno();
}

function cobrarCuotaUsarDisponibleUeno() {
	var resumen = cobrarCuotaResumenUeno();
	if (!resumen || resumen.montoAplicable <= 0) {
		return;
	}
	cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", cobrarCuotaFormato(resumen.montoAplicable));
	cobrarCuotaSetValor("inptCobrarCuotaMontoRecibido", cobrarCuotaFormato(resumen.montoAplicable));
	cobrarCuotaCalcularVuelto();
	cobrarCuotaActualizarResumenUeno();
}

function cobrarCuotaDescontarSaldoUenoLocal(montoAplicado) {
	var disponibleAnterior = Number(cobrarCuotaContextoUeno ? (cobrarCuotaContextoUeno.monto_disponible || 0) : 0);
	var saldoAplicado = Math.min(disponibleAnterior, Math.max(0, Number(montoAplicado || 0)));
	var disponibleNuevo = Math.max(0, disponibleAnterior - saldoAplicado);
	if (cobrarCuotaContextoUeno) {
		cobrarCuotaContextoUeno.monto_disponible = disponibleNuevo;
		cobrarCuotaContextoUeno.monto_disponible_fmt = cobrarCuotaFormato(disponibleNuevo);
		cobrarCuotaContextoUeno.monto_valido = disponibleNuevo > 0;
		cobrarCuotaContextoUeno.puede_usar = disponibleNuevo > 0;
	}
	return {
		disponibleAnterior: disponibleAnterior,
		disponibleNuevo: disponibleNuevo,
		saldoAplicado: saldoAplicado
	};
}

function cobrarCuotaBuscarMovimientoUeno(forzarBusqueda) {
	forzarBusqueda = forzarBusqueda === true;
	var select = cobrarCuotaId("inptCobrarCuotaTipoPago");
	var texto = select && select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : "";
	if (!cobrarCuotaEsTransferenciaTexto(texto)) {
		cobrarCuotaResetBusquedaUeno();
		return;
	}
	var contenedor = cobrarCuotaId("divCobrarCuotaUeno");
	if (!contenedor) {
		return;
	}
	if (!forzarBusqueda && cobrarCuotaTieneMovimientoUenoValido()) {
		cobrarCuotaMostrarContextoUeno();
		return;
	}
	var comprobante = cobrarCuotaId("inptCobrarCuotaComprobante") ? cobrarCuotaId("inptCobrarCuotaComprobante").value : "";
	var monto = cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "";
	var fechaPago = cobrarCuotaId("inptCobrarCuotaFechaPago") ? cobrarCuotaId("inptCobrarCuotaFechaPago").value : "";
	var idMovimiento = cobrarCuotaContextoUeno && cobrarCuotaContextoUeno.id_movimiento ? cobrarCuotaContextoUeno.id_movimiento : "";
	if (comprobante == "" && monto == "" && idMovimiento == "") {
		cobrarCuotaResetBusquedaUeno();
		contenedor.innerHTML = "<div class='cobrar-cuota__ueno-empty'>Ingresa el monto a cobrar para buscar transferencias Ueno disponibles.</div>";
		cobrarCuotaActualizarBotonRegistrar();
		return;
	}
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "buscar_movimiento_ueno");
	datos.append("comprobante", comprobante);
	datos.append("monto", monto);
	datos.append("fecha_pago", fechaPago);
	if (forzarBusqueda && idMovimiento != "") {
		datos.append("id_movimiento", idMovimiento);
	}
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCobrarCuota.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] == "exito") {
					cobrarCuotaUenoResultadosTotal = Number(datos["3"] || 0);
					cobrarCuotaUenoBusquedaActiva = true;
					cobrarCuotaUenoTieneCoincidenciaExacta = datos["5"] == "SI";
					cobrarCuotaCerrarModalUeno();
					if (forzarBusqueda && idMovimiento != "") {
						if (datos["4"] && datos["4"].id_movimiento) {
							cobrarCuotaContextoUeno = datos["4"];
							if (!cobrarCuotaSeleccionada) {
								cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", cobrarCuotaContextoUeno.monto_disponible_fmt || cobrarCuotaFormato(cobrarCuotaContextoUeno.monto_disponible || 0));
							}
							cobrarCuotaMostrarContextoUeno();
						} else {
							cobrarCuotaContextoUeno = null;
							contenedor.innerHTML = datos["2"];
						}
					} else {
						contenedor.innerHTML = datos["2"];
					}
					cobrarCuotaAuditar(
						"BUSCAR_MOVIMIENTO_UENO",
						"consulta",
						cobrarCuotaUenoResultadosTotal > 0 ? "candidatos" : "sin_resultados",
						cobrarCuotaNumero(monto),
						texto,
						cobrarCuotaMaskComprobante(comprobante),
						"Busqueda Ueno desde Cobrar cuota. Resultados: " + cobrarCuotaUenoResultadosTotal + ". Coincidencia exacta: " + (cobrarCuotaUenoTieneCoincidenciaExacta ? "SI" : "NO")
					);
					cobrarCuotaActualizarBotonRegistrar();
				}
			} catch (error) {}
		}
	});
}

function cobrarCuotaInvalidarMovimientoUenoPorEdicion() {
	if (cobrarCuotaContextoUeno && cobrarCuotaContextoUeno.id_movimiento) {
		cobrarCuotaContextoUeno = null;
		cobrarCuotaResetBusquedaUeno();
	}
	cobrarCuotaBuscarMovimientoUeno();
}

function cobrarCuotaUsarMovimientoUeno(movimiento) {
	movimiento = movimiento || null;
	if (!movimiento || !movimiento.id_movimiento) {
		return;
	}
	var montoActual = cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "");
	var disponibleMovimiento = Number(movimiento.monto_disponible || 0);
	var pagoParcialSugerido = movimiento.pago_parcial_sugerido === true || (movimiento.monto_valido === false && disponibleMovimiento > 0 && montoActual > disponibleMovimiento);
	if (movimiento.puede_usar === false || (movimiento.monto_valido === false && !pagoParcialSugerido)) {
		cobrarCuotaAuditar(
			"INTENTO_USAR_MOVIMIENTO_UENO_BLOQUEADO",
			"rechazado",
			"validacion_visual",
			montoActual,
			"Transferencia",
			movimiento.comprobante_masked || "",
			movimiento.mensaje_accion || "Movimiento Ueno no habilitado para aplicar"
		);
		cobrarCuotaAviso(movimiento.mensaje_accion || "Para usar el movimiento se requiere saldo disponible y monto valido.", "error");
		return;
	}
	if (pagoParcialSugerido) {
		var cuotasSeleccionadas = cobrarCuotaObtenerCuotasSeleccionadas();
		var saldoCuota = cuotasSeleccionadas.length > 1 ? cobrarCuotaTotalSeleccionadas(cuotasSeleccionadas) : (cobrarCuotaSeleccionada ? Number(cobrarCuotaSeleccionada.saldo_pendiente_num || 0) : 0);
		var montoParcial = saldoCuota > 0 ? Math.min(disponibleMovimiento, saldoCuota) : disponibleMovimiento;
		if (montoParcial <= 0) {
			cobrarCuotaAviso("El movimiento Ueno seleccionado no tiene saldo disponible.", "error");
			return;
		}
		cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", cobrarCuotaFormato(montoParcial));
		cobrarCuotaSetValor("inptCobrarCuotaMontoRecibido", cobrarCuotaFormato(montoParcial));
		cobrarCuotaCalcularVuelto();
		movimiento.monto_valido = true;
		movimiento.pago_parcial_sugerido = true;
		cobrarCuotaAviso(cuotasSeleccionadas.length > 1 ? "Se usara el saldo disponible y la ultima cuota alcanzada quedara parcial." : "Se usara el saldo disponible como pago parcial.");
	}
	cobrarCuotaContextoUeno = cobrarCuotaMovimientoUenoSeguro(movimiento);
	cobrarCuotaAuditar(
		"MOVIMIENTO_UENO_SELECCIONADO",
		"seleccionado",
		"pendiente_validacion_final",
		cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : ""),
		"Transferencia",
		cobrarCuotaContextoUeno.comprobante_masked || "",
		"Movimiento Ueno seleccionado con comprobante enmascarado"
	);
	cobrarCuotaMostrarContextoUeno();
	cobrarCuotaCerrarModalUeno();
	cobrarCuotaActualizarFormaPago();
}

function cobrarCuotaMostrarContextoUeno() {
	var contenedor = cobrarCuotaId("divCobrarCuotaUeno");
	if (!contenedor || !cobrarCuotaContextoUeno) {
		return;
	}
	var comprobanteSeguro = cobrarCuotaContextoUeno.comprobante_masked || cobrarCuotaMaskComprobante(cobrarCuotaContextoUeno.nro_comprobante || "");
	var fechaMovimiento = cobrarCuotaContextoUeno.fecha_movimiento || cobrarCuotaContextoUeno.fecha_confirmacion || cobrarCuotaContextoUeno.fecha_transaccion || "";
	var fechaBadge = cobrarCuotaContextoUeno.fecha_pago_coincide === true
		? "<span class='cobrar-cuota__ueno-badge cobrar-cuota__ueno-badge--ok'>Fecha coincide</span>"
		: (fechaMovimiento ? "<span class='cobrar-cuota__ueno-badge cobrar-cuota__ueno-badge--warn'>Revisar fecha</span>" : "");
	contenedor.innerHTML = "<div class='cobrar-cuota__ueno-selected'>"
		+ "<div><b>Movimiento Ueno seleccionado</b>"
		+ (fechaMovimiento ? "<span><strong class='cobrar-cuota__ueno-date-main'>" + cobrarCuotaEscape(fechaMovimiento) + "</strong> " + fechaBadge + "</span>" : "")
		+ "<span>Comprobante " + cobrarCuotaEscape(comprobanteSeguro || "**") + " - Disponible " + cobrarCuotaEscape(cobrarCuotaFormato(cobrarCuotaContextoUeno.monto_disponible || 0)) + "</span></div>"
		+ "<button type='button' onclick='cobrarCuotaQuitarMovimientoUeno()'>Quitar</button>"
		+ "</div>"
		+ cobrarCuotaRenderResumenUeno();
}

function cobrarCuotaQuitarMovimientoUeno() {
	if (cobrarCuotaContextoUeno && cobrarCuotaContextoUeno.id_movimiento) {
		cobrarCuotaAuditar(
			"MOVIMIENTO_UENO_CAMBIADO",
			"seleccion_removida",
			"pendiente_validacion",
			cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : ""),
			"Transferencia",
			cobrarCuotaContextoUeno.comprobante_masked || "",
			"Se quito el movimiento Ueno seleccionado"
		);
	}
	cobrarCuotaContextoUeno = null;
	cobrarCuotaResetBusquedaUeno();
	cobrarCuotaBuscarMovimientoUeno();
	cobrarCuotaActualizarFormaPago();
}

function cobrarCuotaRegistrarPendienteUeno() {
	cobrarCuotaAviso("Para cobrar con transferencia debes seleccionar una transferencia Ueno disponible.", "error");
}

function cobrarCuotaObtenerContextoRegistro() {
	if (cobrarCuotaProcesando) {
		return null;
	}
	if (!cobrarCuotaTienePermiso("REGISTRARCOBRARCUOTA")) {
		cobrarCuotaAviso("No tiene permiso para registrar cobros", "error");
		return null;
	}
	if (typeof idabmAperturacierrecaja !== "undefined" && idabmAperturacierrecaja == "") {
		cobrarCuotaAviso("Falta iniciar una caja antes de registrar cobros");
		if (typeof verCerrarVentanaAbmAperturaCierreCaja1 === "function") {
			verCerrarVentanaAbmAperturaCierreCaja1();
		}
		return null;
	}
	var select = cobrarCuotaId("inptCobrarCuotaTipoPago");
	var idTipoPago = select ? select.value : "";
	var textoTipo = select && select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : "";
	if (idTipoPago == "") {
		cobrarCuotaAviso("Elegi la forma de pago");
		return null;
	}
	var transferencia = cobrarCuotaEsTransferenciaTexto(textoTipo);
	var cuotasSeleccionadas = cobrarCuotaObtenerCuotasSeleccionadas();
	if (cuotasSeleccionadas.length == 0) {
		cobrarCuotaAviso("Selecciona una cuota antes de registrar el cobro");
		return null;
	}
	for (var i = 0; i < cuotasSeleccionadas.length; i++) {
		if (!cobrarCuotaEsCuotaCobrable(cuotasSeleccionadas[i])) {
			cobrarCuotaAviso("No se puede cobrar una cuota pagada o anulada", "error");
			return null;
		}
	}
	cobrarCuotaSeleccionada = cuotasSeleccionadas[0];
	var monto = cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "");
	var esMultiple = cuotasSeleccionadas.length > 1;
	var saldo = esMultiple ? cobrarCuotaTotalSeleccionadas(cuotasSeleccionadas) : Number(cobrarCuotaSeleccionada.saldo_pendiente_num || 0);
	if (monto <= 0) {
		cobrarCuotaAviso("Ingresa un monto mayor a cero");
		return null;
	}
	if (monto > saldo) {
		cobrarCuotaAviso("No se puede cobrar mas que el saldo pendiente");
		cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", esMultiple ? cobrarCuotaFormato(saldo) : (cobrarCuotaSeleccionada.saldo_pendiente || ""));
		return null;
	}
	var aplicaciones = cobrarCuotaCalcularAplicaciones(cuotasSeleccionadas, monto);
	if (aplicaciones.length == 0) {
		cobrarCuotaAviso("El monto no alcanza para aplicar a las cuotas seleccionadas.");
		return null;
	}
	cobrarCuotaSetHoy(true);
	var fecha = cobrarCuotaId("inptCobrarCuotaFechaPago") ? cobrarCuotaId("inptCobrarCuotaFechaPago").value : "";
	if (fecha == "") {
		cobrarCuotaAviso("Ingresa la fecha de pago");
		return null;
	}
	var comprobante = cobrarCuotaId("inptCobrarCuotaComprobante") ? cobrarCuotaId("inptCobrarCuotaComprobante").value.replace(/\s+/g, "").trim() : "";
	if (transferencia && !cobrarCuotaTieneMovimientoUenoValido()) {
		cobrarCuotaAviso("Selecciona una transferencia Ueno disponible para registrar el cobro.", "error");
		return null;
	}
	if (transferencia && cobrarCuotaContextoUeno && cobrarCuotaContextoUeno.id_movimiento && !cobrarCuotaTieneMovimientoUenoValido()) {
		cobrarCuotaAviso("El movimiento Ueno seleccionado no paso la validacion visual. Quita la seleccion y busca nuevamente.", "error");
		return null;
	}
	if (transferencia && cobrarCuotaTieneMovimientoUenoValido()) {
		var disponibleUeno = Number(cobrarCuotaContextoUeno.monto_disponible || 0);
		if (disponibleUeno > 0 && monto > disponibleUeno) {
			cobrarCuotaAviso("El movimiento Ueno seleccionado no cubre ese monto. Usa el disponible como pago parcial o ajusta el monto.");
			return null;
		}
	}
	return {
		monto: monto,
		saldo: saldo,
		fecha: fecha,
		select: select,
		idTipoPago: idTipoPago,
		textoTipo: textoTipo,
		transferencia: transferencia,
		comprobante: comprobante,
		banco: transferencia && cobrarCuotaId("inptCobrarCuotaBanco") ? cobrarCuotaId("inptCobrarCuotaBanco").value : "",
		montoRecibido: cobrarCuotaId("inptCobrarCuotaMontoRecibido") ? cobrarCuotaId("inptCobrarCuotaMontoRecibido").value : "",
		vuelto: cobrarCuotaId("inptCobrarCuotaVuelto") ? cobrarCuotaId("inptCobrarCuotaVuelto").value : "",
		observacion: cobrarCuotaId("txtCobrarCuotaObservacion") ? cobrarCuotaId("txtCobrarCuotaObservacion").value : "",
		cuotas: cuotasSeleccionadas,
		aplicaciones: aplicaciones,
		multiple: esMultiple,
		totalSeleccionado: saldo
	};
}

function cobrarCuotaConfirmacionFila(etiqueta, valor, claseExtra) {
	valor = String(valor || "").trim();
	if (valor == "") { return ""; }
	return "<div class='cobrar-cuota-confirmacion__row" + (claseExtra ? " " + claseExtra : "") + "'>" +
		"<span class='cobrar-cuota-confirmacion__label'>" + cobrarCuotaEscape(etiqueta) + "</span>" +
		"<span class='cobrar-cuota-confirmacion__value'>" + cobrarCuotaEscape(valor) + "</span>" +
	"</div>";
}

function cobrarCuotaConfirmacionFilaMonto(etiqueta, valor) {
	valor = String(valor || "").trim();
	if (valor == "") { return ""; }
	return "<div class='cobrar-cuota-confirmacion__row'>" +
		"<span class='cobrar-cuota-confirmacion__label'>" + cobrarCuotaEscape(etiqueta) + "</span>" +
		"<span class='cobrar-cuota-confirmacion__value cobrar-cuota-confirmacion__value--money'>" + cobrarCuotaEscape(valor) + "</span>" +
	"</div>";
}

function cobrarCuotaConfirmacionSeccion(titulo, contenido) {
	if ($.trim(contenido || "") == "") { return ""; }
	return "<section class='cobrar-cuota-confirmacion__section'>" +
		"<h4>" + cobrarCuotaEscape(titulo) + "</h4>" +
		"<div class='cobrar-cuota-confirmacion__grid'>" + contenido + "</div>" +
	"</section>";
}

function cobrarCuotaRenderConfirmacion(contexto) {
	if (!contexto) { return ""; }
	var cuotas = contexto.cuotas && contexto.cuotas.length ? contexto.cuotas : cobrarCuotaObtenerCuotasSeleccionadas();
	if (!cuotas.length) { return ""; }
	var cuota = cuotas[0];
	var esMultiple = cuotas.length > 1;
	var cliente = "";
	cliente += cobrarCuotaConfirmacionFila("Cliente", cuota.cliente || "");
	cliente += cobrarCuotaConfirmacionFila("Cedula", cuota.cedula || "");
	cliente += cobrarCuotaConfirmacionFila("Venta", cuota.venta || cuota.venta_id || cuota.cod_venta || "");
	cliente += cobrarCuotaConfirmacionFila("Alias / apodo", cuota.alias || "");

	var detalleCuota = "";
	if (esMultiple) {
		var detalleSeleccion = "";
		var aplicaciones = contexto.aplicaciones && contexto.aplicaciones.length ? contexto.aplicaciones : cobrarCuotaCalcularAplicaciones(cuotas, contexto.monto || 0);
		for (var i = 0; i < aplicaciones.length; i++) {
			detalleSeleccion += "Cuota " + (aplicaciones[i].cuota.cuota || "-") + " / aplica " + cobrarCuotaFormato(aplicaciones[i].monto) + " Gs.";
			if (aplicaciones[i].saldoRestante > 0) {
				detalleSeleccion += " / queda " + cobrarCuotaFormato(aplicaciones[i].saldoRestante) + " Gs.";
			}
			if (i < aplicaciones.length - 1) {
				detalleSeleccion += " | ";
			}
		}
		detalleCuota += cobrarCuotaConfirmacionFila("Producto / plan", cuota.producto || "", "cobrar-cuota-confirmacion__row--wide");
		detalleCuota += cobrarCuotaConfirmacionFila("Cuotas", detalleSeleccion, "cobrar-cuota-confirmacion__row--wide");
		detalleCuota += cobrarCuotaConfirmacionFila("Cantidad", aplicaciones.length + " cuotas con aplicacion");
		detalleCuota += cobrarCuotaConfirmacionFilaMonto("Saldo total seleccionado", cobrarCuotaFormato(contexto.totalSeleccionado || contexto.monto) + " Gs.");
		if ((contexto.totalSeleccionado || 0) > (contexto.monto || 0)) {
			detalleCuota += cobrarCuotaConfirmacionFilaMonto("Saldo que quedara", cobrarCuotaFormato((contexto.totalSeleccionado || 0) - (contexto.monto || 0)) + " Gs.");
		}
	} else {
		detalleCuota += cobrarCuotaConfirmacionFila("Producto / plan", cuota.producto || "", "cobrar-cuota-confirmacion__row--wide");
		detalleCuota += cobrarCuotaConfirmacionFila("Cuota", cuota.cuota || "");
		detalleCuota += cobrarCuotaConfirmacionFila("Vencimiento", cuota.fecha_vencimiento || "");
		detalleCuota += cobrarCuotaConfirmacionFilaMonto("Monto cuota", cuota.monto_cuota || "");
		detalleCuota += cobrarCuotaConfirmacionFilaMonto("Saldo pendiente", cuota.saldo_pendiente || "");
	}

	var pago = "";
	pago += cobrarCuotaConfirmacionFila("Fecha de pago", contexto.fecha || "");
	pago += cobrarCuotaConfirmacionFila("Forma de pago", contexto.textoTipo || "");
	pago += cobrarCuotaConfirmacionFilaMonto("Monto a cobrar", cobrarCuotaFormato(contexto.monto) + " Gs.");
	if (contexto.transferencia) {
		pago += cobrarCuotaConfirmacionFila("Banco", contexto.banco || "");
		pago += cobrarCuotaConfirmacionFila("Comprobante", cobrarCuotaMaskComprobante(contexto.comprobante || ""));
		if (cobrarCuotaContextoUeno && cobrarCuotaContextoUeno.id_movimiento) {
			pago += cobrarCuotaConfirmacionFila("Movimiento Ueno", cobrarCuotaContextoUeno.comprobante_masked || cobrarCuotaMaskComprobante(cobrarCuotaContextoUeno.nro_comprobante || ""));
			var resumenUeno = cobrarCuotaResumenUeno();
			if (resumenUeno) {
				pago += cobrarCuotaConfirmacionFilaMonto("Disponible Ueno", cobrarCuotaFormato(resumenUeno.disponible) + " Gs.");
				pago += cobrarCuotaConfirmacionFilaMonto("Monto a aplicar", cobrarCuotaFormato(resumenUeno.montoAplicable) + " Gs.");
				if (resumenUeno.resultado == "sobra") {
					pago += cobrarCuotaConfirmacionFilaMonto("Saldo a favor", cobrarCuotaFormato(resumenUeno.diferencia) + " Gs.");
				}
				if (resumenUeno.resultado == "falta") {
					pago += cobrarCuotaConfirmacionFilaMonto("Faltan", cobrarCuotaFormato(resumenUeno.diferencia) + " Gs.");
				}
			}
		}
		pago += "<div class='cobrar-cuota-confirmacion__row cobrar-cuota-confirmacion__row--wide'>" +
			"<span class='cobrar-cuota-confirmacion__label'>Estado esperado</span>" +
			"<span class='cobrar-cuota-confirmacion__status'>" + (cobrarCuotaContextoUeno && cobrarCuotaContextoUeno.id_movimiento ? "Conciliado con Ueno" : "Pendiente de conciliacion bancaria") + "</span>" +
		"</div>";
		if (!cobrarCuotaContextoUeno || !cobrarCuotaContextoUeno.id_movimiento) {
			pago += "<div class='cobrar-cuota-confirmacion__row cobrar-cuota-confirmacion__row--wide'>" +
				"<span class='cobrar-cuota-confirmacion__label'>Confirmacion Ueno</span>" +
				"<span class='cobrar-cuota-confirmacion__value'>No se vinculara un movimiento Ueno ahora; quedara para conciliacion bancaria.</span>" +
			"</div>";
		}
		if (esMultiple) {
			pago += cobrarCuotaConfirmacionFila("Aplicacion", "Se registrara y conciliara un pago por cada cuota alcanzada por el monto.", "cobrar-cuota-confirmacion__row--wide");
		}
	} else {
		pago += cobrarCuotaConfirmacionFilaMonto("Monto recibido", contexto.montoRecibido ? contexto.montoRecibido + " Gs." : "");
		pago += cobrarCuotaConfirmacionFilaMonto("Vuelto", contexto.vuelto ? contexto.vuelto + " Gs." : "");
		if (esMultiple) {
			pago += cobrarCuotaConfirmacionFila("Aplicacion", "Se registrara un pago por cada cuota alcanzada por el monto.", "cobrar-cuota-confirmacion__row--wide");
		}
	}
	pago += cobrarCuotaConfirmacionFila("Observacion", contexto.observacion || "", "cobrar-cuota-confirmacion__row--wide");

	return cobrarCuotaConfirmacionSeccion("Resumen del cliente", cliente) +
		cobrarCuotaConfirmacionSeccion("Resumen de la cuota", detalleCuota) +
		cobrarCuotaConfirmacionSeccion("Resumen del pago", pago);
}

function cobrarCuotaAbrirConfirmacion(contexto) {
	var modal = cobrarCuotaId("divCobrarCuotaConfirmacion");
	var cuerpo = cobrarCuotaId("divCobrarCuotaConfirmacionResumen");
	var btn = cobrarCuotaId("btnCobrarCuotaConfirmarRegistro");
	if (!modal || !cuerpo) {
		cobrarCuotaEjecutarRegistro(contexto);
		return;
	}
	cobrarCuotaConfirmacionPendiente = contexto;
	cuerpo.innerHTML = cobrarCuotaRenderConfirmacion(contexto);
	modal.style.display = "";
	if (btn) {
		btn.disabled = false;
		btn.value = "Confirmar cobro";
		setTimeout(function() { btn.focus(); }, 80);
	}
}

function cobrarCuotaCerrarConfirmacion() {
	if (cobrarCuotaProcesando) { return; }
	var modal = cobrarCuotaId("divCobrarCuotaConfirmacion");
	if (modal) { modal.style.display = "none"; }
	cobrarCuotaConfirmacionPendiente = null;
}

function cobrarCuotaConfirmarRegistro() {
	if (cobrarCuotaProcesando || !cobrarCuotaConfirmacionPendiente) { return; }
	var btn = cobrarCuotaId("btnCobrarCuotaConfirmarRegistro");
	if (btn) {
		btn.disabled = true;
		btn.value = "Procesando...";
	}
	cobrarCuotaEjecutarRegistro(cobrarCuotaConfirmacionPendiente);
}

function cobrarCuotaCerrarConfirmacionEscape(evento) {
	evento = evento || window.event;
	if ((evento.key == "Escape" || evento.keyCode == 27) && cobrarCuotaId("divCobrarCuotaConfirmacion") && cobrarCuotaId("divCobrarCuotaConfirmacion").style.display != "none") {
		cobrarCuotaCerrarConfirmacion();
	}
}

if (document.addEventListener) {
	document.addEventListener("keydown", cobrarCuotaCerrarConfirmacionEscape);
}

function cobrarCuotaRegistrar() {
	var contexto = cobrarCuotaObtenerContextoRegistro();
	if (!contexto) { return; }
	cobrarCuotaAbrirConfirmacion(contexto);
}

function cobrarCuotaCrearDatosRegistroCuota(contexto, cuota, monto, nrofactura) {
	var select = contexto.select || cobrarCuotaId("inptCobrarCuotaTipoPago");
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "cargartipospagoscredito");
	datos.append("origen_cobro", "COBRAR_CUOTA");
	datos.append("Fecha", contexto.fecha);
	datos.append("totalDeudaCuota", cobrarCuotaFormato(cuota.saldo_cuota_num || monto));
	datos.append("cod_creditoFK", cuota.idcredito);
	datos.append("cod_cobradorFK", userid);
	datos.append("cod_venta", cuota.venta_id || cuota.cod_venta);
	datos.append("totalInteres", cobrarCuotaFormato(cuota.saldo_interes_num || 0));
	datos.append("nrofactura", nrofactura || "");
	datos.append("descuento", "0");
	datos.append("MontoTarjeta", "0");
	datos.append("codcaja", typeof cajapredeterminada !== "undefined" ? cajapredeterminada : "");
	datos.append("codApertura", typeof idabmAperturacierrecaja !== "undefined" ? idabmAperturacierrecaja : "");
	datos.append("CargoAdministrativo", "0");
	datos.append("cod_local", typeof cod_localFKUSer !== "undefined" ? cod_localFKUSer : (cuota.local_id || ""));
	datos.append("totalregistro", "1");
	datos.append("exigir_movimiento_ueno", contexto.transferencia ? "SI" : "NO");
	datos.append("idtipopago1", contexto.idTipoPago);
	datos.append("monto1", cobrarCuotaFormato(monto));
	datos.append("valor1", select && select.options[select.selectedIndex] ? (select.options[select.selectedIndex].id || "NO") : "NO");
	datos.append("ueno_comprobante1", contexto.transferencia ? (contexto.comprobante || "") : "");
	datos.append("ueno_id_movimiento1", contexto.transferencia && cobrarCuotaContextoUeno ? (cobrarCuotaContextoUeno.id_movimiento || "") : "");
	datos.append("ueno_observacion1", contexto.transferencia ? (contexto.observacion || "") : "");
	return datos;
}

function cobrarCuotaCrearPagoRegistradoMultiple(contexto, resultados, nrofactura, titulo, detalle, tipo) {
	resultados = resultados || [];
	var plan = cobrarCuotaClonarSimple(cobrarCuotaPlanSeleccionado || {});
	var esTransferencia = !!contexto.transferencia || cobrarCuotaEsTransferenciaTexto(contexto.textoTipo || "");
	var ueno = esTransferencia ? cobrarCuotaClonarSimple(contexto.uenoInicial || cobrarCuotaContextoUeno || {}) : {};
	var total = 0;
	var detalles = [];
	var parciales = 0;
	for (var i = 0; i < resultados.length; i++) {
		var cuota = resultados[i].cuota || {};
		var monto = Number(resultados[i].monto || cuota.saldo_pendiente_num || 0) || 0;
		var saldoAnteriorCuota = Number(cuota.saldo_pendiente_num || monto || 0) || 0;
		var saldoRestanteCuota = Math.max(0, saldoAnteriorCuota - monto);
		if (saldoRestanteCuota > 0) {
			parciales++;
		}
		total += monto;
		detalles.push({
			cuota: cuota.cuota || "",
			fechaVencimiento: cuota.fecha_vencimiento || "",
			producto: cuota.producto || "",
			monto: cobrarCuotaFormato(monto),
			montoNum: monto,
			saldoCuotaAnterior: cobrarCuotaFormato(saldoAnteriorCuota),
			saldoCuotaRestante: cobrarCuotaFormato(saldoRestanteCuota),
			saldoCuotaRestanteNum: saldoRestanteCuota,
			estado: saldoRestanteCuota > 0 ? "Pago parcial" : "Pagada"
		});
	}
	var primera = resultados.length > 0 ? (resultados[0].cuota || {}) : {};
	var totalVenta = Number(plan.total_venta || primera.total_venta || 0) || 0;
	var saldoVentaAnterior = Number(plan.saldo_pendiente_total || contexto.totalSeleccionado || total) || 0;
	if (saldoVentaAnterior <= 0) {
		saldoVentaAnterior = cobrarCuotaNumero(plan.saldo_pendiente_total_fmt || primera.saldo_pendiente_total_fmt || cobrarCuotaFormato(total));
	}
	var saldoVentaRestante = Math.max(0, saldoVentaAnterior - total);
	var disponibleAnterior = contexto.disponibleAnterior !== undefined ? Number(contexto.disponibleAnterior || 0) : Number(ueno.monto_disponible || 0);
	var disponibleNuevo = contexto.disponibleNuevo !== undefined ? Number(contexto.disponibleNuevo || 0) : (esTransferencia ? Math.max(0, disponibleAnterior - total) : 0);
	var saldoAplicadoUeno = contexto.saldoAplicadoUeno !== undefined ? Number(contexto.saldoAplicadoUeno || 0) : (esTransferencia ? Math.max(0, disponibleAnterior - disponibleNuevo) : 0);
	var mensaje = "El pago de las cuotas seleccionadas fue registrado correctamente.";
	if (esTransferencia && contexto.conciliadoUeno) {
		mensaje = "El pago de las cuotas seleccionadas fue registrado y conciliado correctamente con Banco Ueno.";
	} else if (esTransferencia) {
		mensaje = "El pago de las cuotas seleccionadas fue registrado, pero queda pendiente de conciliacion bancaria.";
	}
	return {
		titulo: titulo || "Cuotas cobradas correctamente",
		mensaje: mensaje,
		detalle: detalle || "",
		tipo: tipo || "exito",
		multiple: true,
		detalles: detalles,
		cantidadCuotas: detalles.length,
		numero: nrofactura || "",
		nroRecibo: nrofactura || "",
		fecha: contexto.fecha || "",
		cliente: primera.cliente || plan.cliente || "",
		cedula: primera.cedula || plan.cedula || "",
		venta: primera.venta || plan.venta || plan.cod_venta || "",
		codVenta: primera.venta_id || primera.cod_venta || plan.venta_id || plan.cod_venta || "",
		cuota: detalles.map(function(item) { return item.cuota; }).join(", "),
		fechaVencimiento: detalles.length > 0 ? detalles[0].fechaVencimiento : "",
		producto: primera.producto || plan.producto || "",
		totalVenta: totalVenta,
		totalVentaFmt: (plan.total_venta_fmt || primera.total_venta_fmt || (totalVenta > 0 ? cobrarCuotaFormato(totalVenta) : "")),
		totalPlan: plan.saldo_pendiente_total_fmt || "",
		saldoVentaAnterior: saldoVentaAnterior,
		saldoVentaAnteriorFmt: cobrarCuotaFormato(saldoVentaAnterior),
		saldoVentaRestante: saldoVentaRestante,
		saldoVentaRestanteFmt: cobrarCuotaFormato(saldoVentaRestante),
		montoAplicado: total,
		montoAplicadoFmt: cobrarCuotaFormato(total),
		formaPago: contexto.textoTipo || "",
		banco: esTransferencia ? (contexto.banco || "Ueno") : "",
		comprobante: esTransferencia ? cobrarCuotaMaskComprobante(contexto.comprobante || ueno.nro_comprobante || ueno.comprobante_masked || "") : "",
		movimientoUeno: esTransferencia ? (ueno.comprobante_masked || cobrarCuotaMaskComprobante(ueno.nro_comprobante || "") || ueno.id_movimiento || "") : "",
		idMovimientoUeno: esTransferencia ? (ueno.id_movimiento || "") : "",
		saldoDisponibleAnterior: disponibleAnterior,
		saldoDisponibleAnteriorFmt: cobrarCuotaFormato(disponibleAnterior),
		saldoAplicadoUeno: saldoAplicadoUeno,
		saldoAplicadoUenoFmt: cobrarCuotaFormato(saldoAplicadoUeno),
		saldoDisponibleRestante: esTransferencia ? disponibleNuevo : 0,
		saldoDisponibleRestanteFmt: cobrarCuotaFormato(esTransferencia ? disponibleNuevo : 0),
		saldoFavor: esTransferencia ? Math.max(0, disponibleNuevo) : 0,
		saldoFavorFmt: cobrarCuotaFormato(esTransferencia ? Math.max(0, disponibleNuevo) : 0),
		saldoCuotaAnterior: total,
		saldoCuotaAnteriorFmt: cobrarCuotaFormato(total),
		saldoCuotaRestante: 0,
		saldoCuotaRestanteFmt: "0",
		estadoFinalCuota: parciales > 0 ? (detalles.length + " cuotas aplicadas, " + parciales + " parcial") : (detalles.length + " cuotas pagadas"),
		estadoFinalMovimiento: contexto.estadoMovimiento || (esTransferencia ? (disponibleNuevo > 0 ? "Parcial" : "Conciliado") : "No aplica"),
		cajero: (typeof lblUser !== "undefined" && lblUser ? lblUser.innerHTML : "")
	};
}

function cobrarCuotaFinalizarRegistroMultiple(contexto, resultados, nrofactura, titulo, detalle, tipo) {
	var ventaActual = cobrarCuotaPlanSeleccionado ? (cobrarCuotaPlanSeleccionado.cod_venta || cobrarCuotaPlanSeleccionado.venta_id) : "";
	var pagoRegistrado = cobrarCuotaCrearPagoRegistradoMultiple(contexto, resultados, nrofactura, titulo, detalle, tipo);
	cobrarCuotaUltimoPagoRegistrado = pagoRegistrado;
	cobrarCuotaUltimoRecibo = cobrarCuotaCrearReciboDesdePago(pagoRegistrado);
	cobrarCuotaProcesando = false;
	cobrarCuotaLimpiarDatosCobroActual(false);
	cobrarCuotaRestaurarBoton();
	cobrarCuotaMostrarResultado(titulo || "Cuotas cobradas correctamente", detalle || "Pago registrado.", tipo || "exito");
	cobrarCuotaAviso(titulo || "Cuotas cobradas correctamente");
	if (ventaActual != "") {
		cobrarCuotaCargarCuotas(ventaActual);
	}
	cobrarCuotaMostrarModalExito(pagoRegistrado);
}

function cobrarCuotaConciliarTransferenciaMultiple(contexto, cuota, monto, callback) {
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "conciliar_transferencia");
	datos.append("id_movimiento", cobrarCuotaContextoUeno ? (cobrarCuotaContextoUeno.id_movimiento || "") : "");
	datos.append("comprobante", contexto.comprobante || "");
	datos.append("cod_credito", cuota.idcredito || "");
	datos.append("monto", cobrarCuotaFormato(monto));
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCobrarCuota.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					var disponibleAnterior = Number(cobrarCuotaContextoUeno ? cobrarCuotaContextoUeno.monto_disponible || 0 : 0);
					var disponibleNuevo = respuesta.monto_disponible !== undefined ? Number(respuesta.monto_disponible || 0) : Math.max(0, disponibleAnterior - Number(monto || 0));
					if (cobrarCuotaContextoUeno) {
						cobrarCuotaContextoUeno.monto_disponible = disponibleNuevo;
						cobrarCuotaContextoUeno.monto_disponible_fmt = cobrarCuotaFormato(disponibleNuevo);
					}
					callback(null, {
						respuesta: respuesta,
						disponibleAnterior: disponibleAnterior,
						disponibleNuevo: disponibleNuevo,
						saldoAplicado: monto,
						detalle: respuesta["2"] || "Pago conciliado con Banco Ueno"
					});
					return;
				}
				callback(respuesta["2"] || "No se pudo conciliar automaticamente", { respuesta: respuesta });
			} catch (error) {
				callback("No se pudo interpretar la respuesta de conciliacion", { error: error, responseText: responseText });
			}
		},
		error: function() {
			callback("Error de conexion al conciliar con Ueno", {});
		}
	});
}

function cobrarCuotaEjecutarRegistroMultiple(contexto) {
	var cuotas = contexto.cuotas && contexto.cuotas.length ? contexto.cuotas : cobrarCuotaObtenerCuotasSeleccionadas();
	var aplicaciones = contexto.aplicaciones && contexto.aplicaciones.length ? contexto.aplicaciones : cobrarCuotaCalcularAplicaciones(cuotas, contexto.monto || 0);
	if (!aplicaciones.length) {
		return false;
	}
	cobrarCuotaProcesando = true;
	var modalConfirmacion = cobrarCuotaId("divCobrarCuotaConfirmacion");
	if (modalConfirmacion) { modalConfirmacion.style.display = "none"; }
	cobrarCuotaConfirmacionPendiente = null;
	var btn = cobrarCuotaId("btnCobrarCuotaRegistrar");
	if (btn) {
		btn.disabled = true;
		btn.value = "Procesando...";
	}
	obtener_datos_user();
	var indice = 0;
	var resultados = [];
	var nrofactura = "";
	var ventaActual = cobrarCuotaPlanSeleccionado ? (cobrarCuotaPlanSeleccionado.cod_venta || cobrarCuotaPlanSeleccionado.venta_id) : "";
	var transferencia = !!contexto.transferencia;
	var disponibleInicial = transferencia && cobrarCuotaContextoUeno ? Number(cobrarCuotaContextoUeno.monto_disponible || 0) : 0;
	var disponibleActual = disponibleInicial;
	var saldoAplicadoUeno = 0;
	contexto.uenoInicial = transferencia ? cobrarCuotaClonarSimple(cobrarCuotaContextoUeno) : {};
	contexto.disponibleAnterior = disponibleInicial;
	var registrarSiguiente = function() {
		if (indice >= aplicaciones.length) {
			contexto.disponibleNuevo = disponibleActual;
			contexto.saldoAplicadoUeno = saldoAplicadoUeno;
			contexto.conciliadoUeno = transferencia;
			contexto.estadoMovimiento = transferencia ? (disponibleActual > 0 ? "Parcial" : "Conciliado") : "No aplica";
			cobrarCuotaFinalizarRegistroMultiple(
				contexto,
				resultados,
				nrofactura,
				transferencia ? "Cuotas cobradas y conciliadas con Banco Ueno" : "Cuotas cobradas correctamente",
				resultados.length + (transferencia ? " cuotas con pago conciliado." : " cuotas con pago registrado."),
				"exito"
			);
			if (transferencia) {
				if (typeof uenoBuscarMovimientos === "function") {
					uenoBuscarMovimientos();
				}
				if (typeof uenoBuscarPagosPendientes === "function") {
					uenoBuscarPagosPendientes();
				}
			}
			return;
		}
		var aplicacion = aplicaciones[indice];
		var cuota = aplicacion.cuota;
		var montoCuota = Number(aplicacion.monto || 0);
		if (montoCuota <= 0 || !cobrarCuotaEsCuotaCobrable(cuota)) {
			cobrarCuotaProcesando = false;
			cobrarCuotaRestaurarBoton();
			cobrarCuotaAviso("Una de las cuotas seleccionadas ya no tiene saldo pendiente.", "error");
			if (ventaActual != "") {
				cobrarCuotaCargarCuotas(ventaActual);
			}
			return;
		}
		if (transferencia && cobrarCuotaContextoUeno && Number(cobrarCuotaContextoUeno.monto_disponible || 0) < montoCuota) {
			cobrarCuotaProcesando = false;
			cobrarCuotaRestaurarBoton();
			cobrarCuotaAviso("El movimiento Ueno ya no tiene saldo suficiente para la siguiente cuota.", "error");
			if (ventaActual != "") {
				cobrarCuotaCargarCuotas(ventaActual);
			}
			return;
		}
		cobrarCuotaSeleccionada = cuota;
		cobrarCuotaSeleccionadas = [cuota];
		var datos = cobrarCuotaCrearDatosRegistroCuota(contexto, cuota, montoCuota, nrofactura);
		$.ajax({
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmpagos.php",
			type: "post",
			cache: false,
			contentType: false,
			processData: false,
			error: function(jqXHR, textstatus) {
				cobrarCuotaProcesando = false;
				cobrarCuotaRestaurarBoton();
				if (typeof manejadordeerroresjquery === "function") {
					manejadordeerroresjquery(jqXHR.status, textstatus, "cobrarCuotaRegistrarMultiple");
				}
				if (resultados.length > 0 && ventaActual != "") {
					cobrarCuotaCargarCuotas(ventaActual);
				}
			},
			success: function(responseText) {
				try {
					var respuesta = $.parseJSON(responseText);
					if (respuesta["1"] == "exito") {
						if (nrofactura == "" && respuesta["8"]) {
							nrofactura = respuesta["8"];
						}
						var continuarLuegoDeRegistro = function(datosConciliacion) {
							datosConciliacion = datosConciliacion || {};
							resultados.push({ cuota: cuota, monto: montoCuota, respuesta: respuesta, conciliacion: datosConciliacion.respuesta || null });
							cobrarCuotaAuditar(
								transferencia ? "REGISTRAR_Y_CONCILIAR_UENO_MULTIPLE" : "REGISTRAR_COBRO_MULTIPLE_EFECTIVO",
								"registrado",
								transferencia ? "conciliado_ueno" : "no_aplica",
								montoCuota,
								contexto.textoTipo,
								transferencia ? contexto.comprobante : "",
								transferencia ? "Cobro multiple por transferencia conciliado desde Cobrar cuota" : "Cobro multiple en efectivo desde Cobrar cuota"
							);
							indice++;
							registrarSiguiente();
						};
						if (transferencia && cobrarCuotaContextoUeno && cobrarCuotaContextoUeno.id_movimiento) {
							var datosUeno = cobrarCuotaDescontarSaldoUenoLocal(montoCuota);
							disponibleActual = datosUeno.disponibleNuevo;
							saldoAplicadoUeno += Number(datosUeno.saldoAplicado || 0);
							continuarLuegoDeRegistro({
								respuesta: respuesta,
								disponibleAnterior: datosUeno.disponibleAnterior,
								disponibleNuevo: datosUeno.disponibleNuevo,
								saldoAplicado: datosUeno.saldoAplicado,
								detalle: "Pago registrado y conciliado con Banco Ueno"
							});
						} else {
							continuarLuegoDeRegistro();
						}
						return;
					}
					cobrarCuotaProcesando = false;
					cobrarCuotaRestaurarBoton();
					cobrarCuotaAviso(respuesta["2"] || "No se pudo registrar una de las cuotas seleccionadas", "error");
					if (resultados.length > 0 && ventaActual != "") {
						cobrarCuotaCargarCuotas(ventaActual);
					}
				} catch (error) {
					cobrarCuotaProcesando = false;
					cobrarCuotaRestaurarBoton();
					cobrarCuotaAviso("No se pudo interpretar la respuesta del cobro multiple", "error");
					if (typeof GuardarArchivosLog === "function") {
						GuardarArchivosLog("Error cobrarCuotaRegistrarMultiple: " + error + " \r\n Consola: " + responseText);
					}
				}
			}
		});
	};
	registrarSiguiente();
	return true;
}

function cobrarCuotaEjecutarRegistro(contexto) {
	contexto = contexto || cobrarCuotaObtenerContextoRegistro();
	if (!contexto || cobrarCuotaProcesando) { return; }
	if (contexto.multiple) {
		cobrarCuotaEjecutarRegistroMultiple(contexto);
		return;
	}
	var monto = contexto.monto;
	var saldo = contexto.saldo;
	var fecha = contexto.fecha;
	var select = contexto.select || cobrarCuotaId("inptCobrarCuotaTipoPago");
	var idTipoPago = contexto.idTipoPago;
	var textoTipo = contexto.textoTipo;
	var transferencia = contexto.transferencia;
	var comprobante = contexto.comprobante;
	if (transferencia && !contexto.forzarPendienteUeno && !cobrarCuotaForzarPendienteUeno && !cobrarCuotaTieneMovimientoUenoValido() && cobrarCuotaUenoBusquedaActiva && cobrarCuotaUenoResultadosTotal > 0) {
		cobrarCuotaAviso("Hay movimientos Ueno candidatos. Selecciona uno validado antes de confirmar.", "error");
		return;
	}
	if (transferencia && cobrarCuotaContextoUeno && cobrarCuotaContextoUeno.id_movimiento && !cobrarCuotaTieneMovimientoUenoValido()) {
		cobrarCuotaAviso("El movimiento Ueno seleccionado no es valido para conciliacion.", "error");
		return;
	}
	cobrarCuotaProcesando = true;
	var modalConfirmacion = cobrarCuotaId("divCobrarCuotaConfirmacion");
	if (modalConfirmacion) { modalConfirmacion.style.display = "none"; }
	cobrarCuotaConfirmacionPendiente = null;
	var btn = cobrarCuotaId("btnCobrarCuotaRegistrar");
	if (btn) {
		btn.disabled = true;
		btn.value = "Procesando...";
	}
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "cargartipospagoscredito");
	datos.append("origen_cobro", "COBRAR_CUOTA");
	datos.append("Fecha", fecha);
	datos.append("totalDeudaCuota", cobrarCuotaFormato(cobrarCuotaSeleccionada.saldo_cuota_num || saldo));
	datos.append("cod_creditoFK", cobrarCuotaSeleccionada.idcredito);
	datos.append("cod_cobradorFK", userid);
	datos.append("cod_venta", cobrarCuotaSeleccionada.venta_id || cobrarCuotaSeleccionada.cod_venta);
	datos.append("totalInteres", cobrarCuotaFormato(cobrarCuotaSeleccionada.saldo_interes_num || 0));
	datos.append("nrofactura", "");
	datos.append("descuento", "0");
	datos.append("MontoTarjeta", "0");
	datos.append("codcaja", typeof cajapredeterminada !== "undefined" ? cajapredeterminada : "");
	datos.append("codApertura", typeof idabmAperturacierrecaja !== "undefined" ? idabmAperturacierrecaja : "");
	datos.append("CargoAdministrativo", "0");
	datos.append("cod_local", typeof cod_localFKUSer !== "undefined" ? cod_localFKUSer : (cobrarCuotaSeleccionada.local_id || ""));
	datos.append("totalregistro", "1");
	datos.append("exigir_movimiento_ueno", transferencia ? "SI" : "NO");
	datos.append("idtipopago1", idTipoPago);
	datos.append("monto1", cobrarCuotaFormato(monto));
	datos.append("valor1", select && select.options[select.selectedIndex] ? (select.options[select.selectedIndex].id || "NO") : "NO");
	datos.append("ueno_comprobante1", transferencia ? comprobante : "");
	datos.append("ueno_id_movimiento1", transferencia && cobrarCuotaContextoUeno ? (cobrarCuotaContextoUeno.id_movimiento || "") : "");
	datos.append("ueno_observacion1", transferencia ? (cobrarCuotaId("txtCobrarCuotaObservacion") ? cobrarCuotaId("txtCobrarCuotaObservacion").value : "") : "");

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmpagos.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function(jqXHR, textstatus) {
			cobrarCuotaProcesando = false;
			cobrarCuotaRestaurarBoton();
			if (typeof manejadordeerroresjquery === "function") {
				manejadordeerroresjquery(jqXHR.status, textstatus, "cobrarCuotaRegistrar");
			}
		},
		success: function(responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					var estadoRecibo = transferencia ? "Pendiente de conciliacion bancaria" : (monto < saldo ? "Pago parcial registrado" : "Registrado");
					cobrarCuotaUltimoRecibo = cobrarCuotaCrearRecibo(monto, textoTipo, comprobante, estadoRecibo);
					if (transferencia && cobrarCuotaContextoUeno && cobrarCuotaContextoUeno.id_movimiento) {
						var movimientoAntes = cobrarCuotaClonarSimple(cobrarCuotaContextoUeno);
						var saldoUeno = cobrarCuotaDescontarSaldoUenoLocal(monto);
						if (cobrarCuotaUltimoRecibo) {
							cobrarCuotaUltimoRecibo.estado = "Conciliado con Banco Ueno";
						}
						var detalle = "Pago registrado y conciliado con Banco Ueno";
						if (saldoUeno.disponibleNuevo > 0) {
							detalle += ". Saldo disponible del movimiento: " + cobrarCuotaFormato(saldoUeno.disponibleNuevo) + " Gs.";
						}
						cobrarCuotaAuditar("REGISTRAR_Y_CONCILIAR_UENO", "registrado", "conciliado_ueno", monto, textoTipo || "Transferencia", comprobante, detalle);
						cobrarCuotaFinalizarRegistro("Pago registrado y conciliado con Banco Ueno", detalle, "exito", {
							contexto: contexto,
							ueno: movimientoAntes,
							montoAplicado: monto,
							saldoAplicadoUeno: saldoUeno.saldoAplicado,
							disponibleAnterior: saldoUeno.disponibleAnterior,
							disponibleNuevo: saldoUeno.disponibleNuevo,
							conciliadoUeno: true,
							estadoMovimiento: saldoUeno.disponibleNuevo > 0 ? "Parcial" : "Conciliado"
						});
						if (typeof uenoBuscarMovimientos === "function") {
							uenoBuscarMovimientos();
						}
						if (typeof uenoBuscarPagosPendientes === "function") {
							uenoBuscarPagosPendientes();
						}
					} else {
						cobrarCuotaAuditar(
							transferencia ? "REGISTRAR_TRANSFERENCIA_PENDIENTE" : "REGISTRAR_COBRO",
							monto < saldo ? "parcial" : "registrado",
							transferencia ? "pendiente_conciliacion" : "no_aplica",
							monto,
							textoTipo,
							comprobante,
							transferencia ? "Pago por transferencia pendiente de conciliacion bancaria" : "Cobro registrado desde Cobrar cuota"
						);
						cobrarCuotaFinalizarRegistro("La cuota fue cobrada correctamente", transferencia ? "El pago quedo pendiente de conciliacion bancaria." : "Pago registrado.", "exito", {
							contexto: contexto,
							montoAplicado: monto,
							estadoMovimiento: transferencia ? "Pendiente de conciliacion bancaria" : "No aplica"
						});
					}
					return;
				}
				cobrarCuotaProcesando = false;
				cobrarCuotaRestaurarBoton();
				if (transferencia) {
					cobrarCuotaAuditar(
						"VALIDACION_UENO_FALLIDA",
						"rechazado",
						"validacion_backend",
						monto,
						textoTipo,
						comprobante,
						respuesta["2"] || "No se pudo registrar el cobro por validacion Ueno"
					);
				}
				cobrarCuotaAviso(respuesta["2"] || "No se pudo registrar el cobro", "error");
			} catch (error) {
				cobrarCuotaProcesando = false;
				cobrarCuotaRestaurarBoton();
				if (transferencia) {
					cobrarCuotaAuditar("VALIDACION_UENO_FALLIDA", "rechazado", "respuesta_invalida", monto, textoTipo, comprobante, "No se pudo interpretar la respuesta del cobro");
				}
				cobrarCuotaAviso("No se pudo interpretar la respuesta del cobro", "error");
				if (typeof GuardarArchivosLog === "function") {
					GuardarArchivosLog("Error cobrarCuotaRegistrar: " + error + " \r\n Consola: " + responseText);
				}
			}
		}
	});
}

function cobrarCuotaConciliarTransferencia(comprobante, monto, formaPago, contexto) {
	var movimientoAntes = cobrarCuotaClonarSimple(cobrarCuotaContextoUeno);
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "conciliar_transferencia");
	datos.append("id_movimiento", cobrarCuotaContextoUeno.id_movimiento || "");
	datos.append("comprobante", comprobante);
	datos.append("cod_credito", cobrarCuotaSeleccionada.idcredito || "");
	datos.append("monto", cobrarCuotaFormato(monto));
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCobrarCuota.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function(responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					if (cobrarCuotaUltimoRecibo) {
						cobrarCuotaUltimoRecibo.estado = "Conciliado con Banco Ueno";
					}
					var disponibleAnterior = Number(cobrarCuotaContextoUeno ? cobrarCuotaContextoUeno.monto_disponible || 0 : 0);
					var disponibleNuevo = respuesta.monto_disponible !== undefined ? Number(respuesta.monto_disponible || 0) : Math.max(0, disponibleAnterior - Number(monto || 0));
					if (cobrarCuotaContextoUeno) {
						cobrarCuotaContextoUeno.monto_disponible = disponibleNuevo;
						cobrarCuotaContextoUeno.monto_disponible_fmt = cobrarCuotaFormato(disponibleNuevo);
					}
					var detalle = respuesta["2"] || "Pago conciliado con Banco Ueno";
					if (disponibleNuevo > 0) {
						detalle += " Saldo disponible del movimiento: " + cobrarCuotaFormato(disponibleNuevo) + " Gs.";
					}
					cobrarCuotaAuditar("REGISTRAR_Y_CONCILIAR_UENO", "registrado", "conciliado_ueno", monto, formaPago || "Transferencia", comprobante, detalle);
					cobrarCuotaFinalizarRegistro("Pago registrado y conciliado con Banco Ueno", detalle, "exito", {
						contexto: contexto,
						ueno: movimientoAntes,
						montoAplicado: monto,
						saldoAplicadoUeno: monto,
						disponibleAnterior: disponibleAnterior,
						disponibleNuevo: disponibleNuevo,
						conciliadoUeno: true,
						estadoMovimiento: disponibleNuevo > 0 ? "Parcial" : "Conciliado"
					});
					if (typeof uenoBuscarMovimientos === "function") {
						uenoBuscarMovimientos();
					}
					if (typeof uenoBuscarPagosPendientes === "function") {
						uenoBuscarPagosPendientes();
					}
				} else {
					cobrarCuotaAuditar("REGISTRAR_TRANSFERENCIA_PENDIENTE", "registrado", "pendiente_conciliacion", monto, formaPago || "Transferencia", comprobante, respuesta["2"] || "No se pudo conciliar automaticamente");
					cobrarCuotaFinalizarRegistro("Pago registrado", respuesta["2"] || "No se pudo conciliar automaticamente; queda pendiente en Ueno.", "pendiente", {
						contexto: contexto,
						ueno: movimientoAntes,
						montoAplicado: monto,
						saldoAplicadoUeno: 0,
						disponibleAnterior: Number(movimientoAntes.monto_disponible || 0),
						disponibleNuevo: Number(movimientoAntes.monto_disponible || 0),
						estadoMovimiento: "Pendiente de conciliacion"
					});
				}
			} catch (error) {
				cobrarCuotaAuditar("REGISTRAR_TRANSFERENCIA_PENDIENTE", "registrado", "pendiente_conciliacion", monto, formaPago || "Transferencia", comprobante, "No se pudo interpretar la respuesta de conciliacion");
				cobrarCuotaFinalizarRegistro("Pago registrado", "No se pudo conciliar automaticamente; queda pendiente en Ueno.", "pendiente", {
					contexto: contexto,
					ueno: movimientoAntes,
					montoAplicado: monto,
					saldoAplicadoUeno: 0,
					disponibleAnterior: Number(movimientoAntes.monto_disponible || 0),
					disponibleNuevo: Number(movimientoAntes.monto_disponible || 0),
					estadoMovimiento: "Pendiente de conciliacion"
				});
			}
		},
		error: function() {
			cobrarCuotaAuditar("REGISTRAR_TRANSFERENCIA_PENDIENTE", "registrado", "pendiente_conciliacion", monto, formaPago || "Transferencia", comprobante, "Error de conexion al conciliar con Ueno");
			cobrarCuotaFinalizarRegistro("Pago registrado", "No se pudo conciliar automaticamente; queda pendiente en Ueno.", "pendiente", {
				contexto: contexto,
				ueno: movimientoAntes,
				montoAplicado: monto,
				saldoAplicadoUeno: 0,
				disponibleAnterior: Number(movimientoAntes.monto_disponible || 0),
				disponibleNuevo: Number(movimientoAntes.monto_disponible || 0),
				estadoMovimiento: "Pendiente de conciliacion"
			});
		}
	});
}

function cobrarCuotaAuditar(accion, estadoPago, estadoConciliacion, monto, formaPago, comprobante, observacion) {
	if (!cobrarCuotaSeleccionada || !cobrarCuotaSeleccionada.idcredito) {
		return;
	}
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "auditar_cobro");
	datos.append("accion", accion || "REGISTRAR_COBRO");
	datos.append("cod_credito", cobrarCuotaSeleccionada.idcredito || "");
	datos.append("cod_venta", cobrarCuotaSeleccionada.venta_id || cobrarCuotaSeleccionada.cod_venta || "");
	datos.append("cod_cliente", cobrarCuotaSeleccionada.cliente_id || "");
	datos.append("cliente", cobrarCuotaSeleccionada.cliente || "");
	datos.append("forma_pago", formaPago || "");
	datos.append("monto", cobrarCuotaFormato(monto));
	datos.append("comprobante", cobrarCuotaMaskComprobante(comprobante || ""));
	datos.append("id_movimiento", cobrarCuotaContextoUeno && cobrarCuotaContextoUeno.id_movimiento ? cobrarCuotaContextoUeno.id_movimiento : "");
	datos.append("estado_pago", estadoPago || "registrado");
	datos.append("estado_conciliacion", estadoConciliacion || "");
	datos.append("cod_local", typeof cod_localFKUSer !== "undefined" ? cod_localFKUSer : (cobrarCuotaSeleccionada.local_id || ""));
	datos.append("observacion", observacion || "");
	datos.append("datos", JSON.stringify({
		venta: cobrarCuotaSeleccionada.venta || "",
		cuota: cobrarCuotaSeleccionada.cuota || "",
		saldo_pendiente: cobrarCuotaSeleccionada.saldo_pendiente || "",
		producto: cobrarCuotaSeleccionada.producto || "",
		ueno: cobrarCuotaMovimientoUenoSeguro(cobrarCuotaContextoUeno)
	}));
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCobrarCuota.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false
	});
}

function cobrarCuotaCrearPagoRegistrado(titulo, detalle, tipo, opciones) {
	opciones = opciones || {};
	var contexto = opciones.contexto || {};
	var cuota = cobrarCuotaClonarSimple(opciones.cuota || cobrarCuotaSeleccionada || {});
	var plan = cobrarCuotaClonarSimple(opciones.plan || cobrarCuotaPlanSeleccionado || {});
	var esTransferencia = !!contexto.transferencia || cobrarCuotaEsTransferenciaTexto(contexto.textoTipo || "");
	var ueno = esTransferencia ? cobrarCuotaClonarSimple(opciones.ueno || cobrarCuotaContextoUeno || {}) : {};
	var montoAplicado = Number(opciones.montoAplicado !== undefined ? opciones.montoAplicado : (contexto.monto || 0)) || 0;
	var saldoCuotaAnterior = Number(cuota.saldo_pendiente_num || contexto.saldo || 0) || 0;
	var saldoCuotaRestante = Math.max(0, saldoCuotaAnterior - montoAplicado);
	var totalVenta = Number(plan.total_venta || cuota.total_venta || 0) || 0;
	var totalVentaFmt = plan.total_venta_fmt || cuota.total_venta_fmt || (totalVenta > 0 ? cobrarCuotaFormato(totalVenta) : "");
	var saldoVentaAnterior = Number(plan.saldo_pendiente_total || cuota.saldo_pendiente_total || 0) || 0;
	if (saldoVentaAnterior <= 0) {
		saldoVentaAnterior = cobrarCuotaNumero(plan.saldo_pendiente_total_fmt || cuota.saldo_pendiente_total_fmt || cuota.saldo_pendiente || saldoCuotaAnterior);
	}
	var saldoVentaRestante = Math.max(0, saldoVentaAnterior - montoAplicado);
	var disponibleAnterior = opciones.disponibleAnterior !== undefined ? Number(opciones.disponibleAnterior || 0) : Number(ueno.monto_disponible || 0);
	var disponibleNuevo = opciones.disponibleNuevo !== undefined ? Number(opciones.disponibleNuevo || 0) : Math.max(0, disponibleAnterior - montoAplicado);
	var saldoAplicadoUeno = opciones.saldoAplicadoUeno !== undefined ? Number(opciones.saldoAplicadoUeno || 0) : (ueno.id_movimiento ? Math.max(0, disponibleAnterior - disponibleNuevo) : 0);
	var tieneUeno = !!(ueno && ueno.id_movimiento);
	var mensajePrincipal = "El pago fue registrado correctamente.";
	if (tipo == "pendiente") {
		mensajePrincipal = "El pago fue registrado, pero queda pendiente de conciliacion bancaria.";
	} else if (tieneUeno && opciones.conciliadoUeno) {
		mensajePrincipal = "El pago fue registrado y conciliado correctamente con Banco Ueno.";
	}
	return {
		titulo: titulo || "Pago realizado con exito",
		mensaje: mensajePrincipal,
		detalle: detalle || "",
		tipo: tipo || "exito",
		fecha: contexto.fecha || (cobrarCuotaId("inptCobrarCuotaFechaPago") ? cobrarCuotaId("inptCobrarCuotaFechaPago").value : ""),
		cliente: cuota.cliente || plan.cliente || "",
		cedula: cuota.cedula || plan.cedula || "",
		venta: cuota.venta || plan.venta || plan.cod_venta || "",
		codVenta: cuota.venta_id || cuota.cod_venta || plan.venta_id || plan.cod_venta || "",
		cuota: cuota.cuota || "",
		fechaVencimiento: cuota.fecha_vencimiento || "",
		producto: cuota.producto || plan.producto || "",
		montoCuota: cuota.monto_cuota || "",
		montoCuotaNum: Number(cuota.monto_cuota_num || 0) || 0,
		saldoInteres: cuota.saldo_interes || "0",
		saldoInteresNum: Number(cuota.saldo_interes_num || 0) || 0,
		pagadoTotal: cuota.pagado_total || "0",
		tipoVenta: cuota.tipo_venta || plan.tipo_venta || "",
		totalPlan: plan.saldo_pendiente_total_fmt || "",
		totalVenta: totalVenta,
		totalVentaFmt: totalVentaFmt,
		saldoVentaAnterior: saldoVentaAnterior,
		saldoVentaAnteriorFmt: cobrarCuotaFormato(saldoVentaAnterior),
		saldoVentaRestante: saldoVentaRestante,
		saldoVentaRestanteFmt: cobrarCuotaFormato(saldoVentaRestante),
		montoAplicado: montoAplicado,
		montoAplicadoFmt: cobrarCuotaFormato(montoAplicado),
		formaPago: contexto.textoTipo || "",
		banco: esTransferencia ? (contexto.banco || (tieneUeno ? "Ueno" : "")) : "",
		comprobante: esTransferencia ? cobrarCuotaMaskComprobante(contexto.comprobante || "") : "",
		movimientoUeno: tieneUeno ? (ueno.comprobante_masked || cobrarCuotaMaskComprobante(ueno.nro_comprobante || "") || ueno.id_movimiento) : "",
		idMovimientoUeno: tieneUeno ? (ueno.id_movimiento || "") : "",
		saldoDisponibleAnterior: disponibleAnterior,
		saldoDisponibleAnteriorFmt: cobrarCuotaFormato(disponibleAnterior),
		saldoAplicadoUeno: saldoAplicadoUeno,
		saldoAplicadoUenoFmt: cobrarCuotaFormato(saldoAplicadoUeno),
		saldoDisponibleRestante: tieneUeno ? disponibleNuevo : 0,
		saldoDisponibleRestanteFmt: cobrarCuotaFormato(tieneUeno ? disponibleNuevo : 0),
		saldoFavor: tieneUeno ? Math.max(0, disponibleNuevo) : 0,
		saldoFavorFmt: cobrarCuotaFormato(tieneUeno ? Math.max(0, disponibleNuevo) : 0),
		saldoCuotaAnterior: saldoCuotaAnterior,
		saldoCuotaAnteriorFmt: cobrarCuotaFormato(saldoCuotaAnterior),
		saldoCuotaRestante: saldoCuotaRestante,
		saldoCuotaRestanteFmt: cobrarCuotaFormato(saldoCuotaRestante),
		estadoFinalCuota: saldoCuotaRestante <= 0 ? "Pagada" : "Pago parcial",
		estadoFinalMovimiento: opciones.estadoMovimiento || (tieneUeno ? (disponibleNuevo > 0 ? "Parcial" : "Conciliado") : "No aplica"),
		cajero: (typeof lblUser !== "undefined" && lblUser ? lblUser.innerHTML : "")
	};
}

function cobrarCuotaCrearReciboDesdePago(pago) {
	pago = pago || {};
	return {
		numero: pago.numero || pago.nroRecibo || "",
		nroRecibo: pago.nroRecibo || pago.numero || "",
		fecha: pago.fecha || "",
		cliente: pago.cliente || "",
		cedula: pago.cedula || "",
		venta: pago.venta || "",
		cuota: pago.cuota || "",
		fechaVencimiento: pago.fechaVencimiento || "",
		producto: pago.producto || "",
		montoCuota: pago.montoCuota || "",
		saldoCuotaAnterior: pago.saldoCuotaAnteriorFmt || "",
		saldoCuotaRestante: pago.saldoCuotaRestanteFmt || "",
		saldoInteres: pago.saldoInteres || "",
		pagadoTotal: pago.pagadoTotal || "",
		tipoVenta: pago.tipoVenta || "",
		totalPlan: pago.totalPlan || "",
		totalVenta: pago.totalVentaFmt || "",
		totalVentaNum: pago.totalVenta || 0,
		saldoVentaAnterior: pago.saldoVentaAnteriorFmt || "",
		saldoVentaRestante: pago.saldoVentaRestanteFmt || "",
		monto: pago.montoAplicadoFmt || "0",
		detalles: Array.isArray(pago.detalles) ? pago.detalles : [],
		multiple: pago.multiple === true,
		cantidadCuotas: pago.cantidadCuotas || (Array.isArray(pago.detalles) ? pago.detalles.length : 0),
		formaPago: pago.formaPago || "",
		banco: pago.banco || "",
		comprobante: pago.comprobante || "",
		movimientoUeno: pago.movimientoUeno || "",
		idMovimientoUeno: pago.idMovimientoUeno || "",
		saldoDisponibleAnterior: pago.saldoDisponibleAnteriorFmt || "",
		saldoAplicadoUeno: pago.saldoAplicadoUenoFmt || "",
		saldoDisponibleRestante: pago.saldoDisponibleRestanteFmt || "",
		saldoFavor: pago.saldoFavorFmt || "",
		estadoFinalCuota: pago.estadoFinalCuota || "",
		estadoFinalMovimiento: pago.estadoFinalMovimiento || "",
		cajero: pago.cajero || "",
		estado: pago.estadoFinalMovimiento && pago.estadoFinalMovimiento != "No aplica" ? pago.estadoFinalMovimiento : (pago.tipo == "pendiente" ? "Pendiente de conciliacion bancaria" : "Registrado")
	};
}

function cobrarCuotaFinalizarRegistro(titulo, detalle, tipo, opciones) {
	opciones = opciones || {};
	var ventaActual = cobrarCuotaPlanSeleccionado ? (cobrarCuotaPlanSeleccionado.cod_venta || cobrarCuotaPlanSeleccionado.venta_id) : "";
	var pagoRegistrado = cobrarCuotaCrearPagoRegistrado(titulo, detalle, tipo, opciones);
	cobrarCuotaUltimoPagoRegistrado = pagoRegistrado;
	cobrarCuotaUltimoRecibo = cobrarCuotaCrearReciboDesdePago(pagoRegistrado);
	cobrarCuotaProcesando = false;
	cobrarCuotaLimpiarDatosCobroActual(false);
	cobrarCuotaRestaurarBoton();
	cobrarCuotaMostrarResultado(titulo, detalle, tipo);
	cobrarCuotaAviso(titulo);
	if (ventaActual != "") {
		cobrarCuotaCargarCuotas(ventaActual);
	}
	cobrarCuotaMostrarModalExito(pagoRegistrado);
}

function cobrarCuotaRestaurarBoton() {
	var btn = cobrarCuotaId("btnCobrarCuotaRegistrar");
	if (btn) {
		btn.disabled = false;
	}
	cobrarCuotaActualizarFormaPago();
}

function cobrarCuotaCrearRecibo(monto, formaPago, comprobante, estado) {
	var saldoAnterior = cobrarCuotaSeleccionada ? Number(cobrarCuotaSeleccionada.saldo_pendiente_num || 0) : 0;
	var saldoRestante = Math.max(0, saldoAnterior - (Number(monto) || 0));
	var totalVenta = Number((cobrarCuotaPlanSeleccionado && cobrarCuotaPlanSeleccionado.total_venta) || (cobrarCuotaSeleccionada && cobrarCuotaSeleccionada.total_venta) || 0) || 0;
	var saldoVentaAnterior = Number(cobrarCuotaPlanSeleccionado && cobrarCuotaPlanSeleccionado.saldo_pendiente_total || 0) || 0;
	if (saldoVentaAnterior <= 0) {
		saldoVentaAnterior = cobrarCuotaNumero((cobrarCuotaPlanSeleccionado && cobrarCuotaPlanSeleccionado.saldo_pendiente_total_fmt) || (cobrarCuotaSeleccionada && cobrarCuotaSeleccionada.saldo_pendiente) || saldoAnterior);
	}
	var saldoVentaRestante = Math.max(0, saldoVentaAnterior - (Number(monto) || 0));
	return {
		fecha: cobrarCuotaId("inptCobrarCuotaFechaPago") ? cobrarCuotaId("inptCobrarCuotaFechaPago").value : "",
		cliente: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.cliente : "",
		cedula: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.cedula : "",
		venta: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.venta : "",
		cuota: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.cuota : "",
		fechaVencimiento: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.fecha_vencimiento : "",
		producto: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.producto : "",
		montoCuota: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.monto_cuota : "",
		saldoCuotaAnterior: cobrarCuotaFormato(saldoAnterior),
		saldoCuotaRestante: cobrarCuotaFormato(saldoRestante),
		saldoInteres: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.saldo_interes : "",
		pagadoTotal: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.pagado_total : "",
		tipoVenta: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.tipo_venta : "",
		totalPlan: cobrarCuotaPlanSeleccionado ? (cobrarCuotaPlanSeleccionado.saldo_pendiente_total_fmt || "") : "",
		totalVenta: (cobrarCuotaPlanSeleccionado && cobrarCuotaPlanSeleccionado.total_venta_fmt) || (cobrarCuotaSeleccionada && cobrarCuotaSeleccionada.total_venta_fmt) || (totalVenta > 0 ? cobrarCuotaFormato(totalVenta) : ""),
		totalVentaNum: totalVenta,
		saldoVentaAnterior: cobrarCuotaFormato(saldoVentaAnterior),
		saldoVentaRestante: cobrarCuotaFormato(saldoVentaRestante),
		monto: cobrarCuotaFormato(monto),
		formaPago: formaPago,
		comprobante: cobrarCuotaMaskComprobante(comprobante || ""),
		cajero: (typeof lblUser !== "undefined" && lblUser ? lblUser.innerHTML : ""),
		estado: estado
	};
}

function cobrarCuotaRenderResumenPagoExitoso(pago) {
	if (!pago) {
		return "";
	}
	var principal = "<div class='cobrar-cuota-exito__hero'>"
		+ "<span>Monto aplicado</span>"
		+ "<strong>" + cobrarCuotaEscape(pago.montoAplicadoFmt || "0") + " Gs.</strong>"
		+ "<small>" + cobrarCuotaEscape(pago.estadoFinalCuota || "") + "</small>"
		+ "</div>";
	var datosPago = "";
	var esTransferencia = cobrarCuotaEsTransferenciaTexto(pago.formaPago || "") || !!(pago.idMovimientoUeno || pago.movimientoUeno);
	datosPago += cobrarCuotaConfirmacionFila("Fecha de pago", pago.fecha || "");
	datosPago += cobrarCuotaConfirmacionFila("Forma de pago", pago.formaPago || "");
	if (esTransferencia) {
		datosPago += cobrarCuotaConfirmacionFila("Banco", pago.banco || "");
		datosPago += cobrarCuotaConfirmacionFila("Comprobante", pago.comprobante || "");
	}
	datosPago += cobrarCuotaConfirmacionFilaMonto("Monto aplicado", (pago.montoAplicadoFmt || "0") + " Gs.");

	var datosCuota = "";
	datosCuota += cobrarCuotaConfirmacionFila("Venta", pago.venta || pago.codVenta || "");
	datosCuota += cobrarCuotaConfirmacionFila("Cliente", pago.cliente || "");
	if (pago.multiple && pago.detalles && pago.detalles.length > 1) {
		var detalleCuotas = "";
		for (var i = 0; i < pago.detalles.length; i++) {
			detalleCuotas += "Cuota " + (pago.detalles[i].cuota || "-") + " / " + (pago.detalles[i].monto || "0") + " Gs.";
			if (pago.detalles[i].saldoCuotaRestanteNum > 0) {
				detalleCuotas += " / queda " + (pago.detalles[i].saldoCuotaRestante || "0") + " Gs.";
			}
			if (i < pago.detalles.length - 1) {
				detalleCuotas += " | ";
			}
		}
		datosCuota += cobrarCuotaConfirmacionFila("Cuotas aplicadas", detalleCuotas, "cobrar-cuota-confirmacion__row--wide");
		datosCuota += cobrarCuotaConfirmacionFila("Cantidad", pago.detalles.length + " cuotas");
		datosCuota += cobrarCuotaConfirmacionFila("Saldo anterior del plan", (pago.saldoVentaAnteriorFmt || "0") + " Gs.");
		datosCuota += cobrarCuotaConfirmacionFila("Saldo pendiente del plan", (pago.saldoVentaRestanteFmt || "0") + " Gs.");
	} else {
		datosCuota += cobrarCuotaConfirmacionFila("Cuota aplicada", pago.cuota || "");
		datosCuota += cobrarCuotaConfirmacionFila("Saldo anterior", (pago.saldoCuotaAnteriorFmt || "0") + " Gs.");
		datosCuota += cobrarCuotaConfirmacionFila("Saldo pendiente", (pago.saldoCuotaRestanteFmt || "0") + " Gs.");
		datosCuota += cobrarCuotaConfirmacionFila("Estado final", pago.estadoFinalCuota || "");
	}

	var datosUeno = "";
	if (pago.idMovimientoUeno || pago.movimientoUeno) {
		datosUeno += cobrarCuotaConfirmacionFila("Movimiento Ueno", pago.movimientoUeno || pago.idMovimientoUeno || "");
		datosUeno += cobrarCuotaConfirmacionFila("Disponible anterior", (pago.saldoDisponibleAnteriorFmt || "0") + " Gs.");
		datosUeno += cobrarCuotaConfirmacionFila("Saldo aplicado", (pago.saldoAplicadoUenoFmt || "0") + " Gs.");
		datosUeno += cobrarCuotaConfirmacionFila("Disponible restante", (pago.saldoDisponibleRestanteFmt || "0") + " Gs.");
		if (Number(pago.saldoFavor || 0) > 0) {
			datosUeno += cobrarCuotaConfirmacionFilaMonto("Saldo a favor", (pago.saldoFavorFmt || "0") + " Gs.");
		}
		datosUeno += cobrarCuotaConfirmacionFila("Estado movimiento", pago.estadoFinalMovimiento || "");
	}

	return principal
		+ cobrarCuotaConfirmacionSeccion("Resumen del pago", datosPago)
		+ cobrarCuotaConfirmacionSeccion("Cuota aplicada", datosCuota)
		+ cobrarCuotaConfirmacionSeccion("Banco Ueno", datosUeno);
}

function cobrarCuotaMostrarModalExito(pago) {
	var modal = cobrarCuotaId("divCobrarCuotaExito");
	var cuerpo = cobrarCuotaId("divCobrarCuotaExitoResumen");
	var mensaje = cobrarCuotaId("lblCobrarCuotaExitoMensaje");
	var btnImprimir = cobrarCuotaId("btnCobrarCuotaExitoImprimir");
	if (!modal || !cuerpo) {
		return;
	}
	if (mensaje) {
		mensaje.innerHTML = cobrarCuotaEscape(pago && pago.mensaje ? pago.mensaje : "El pago fue registrado correctamente.");
	}
	cuerpo.innerHTML = cobrarCuotaRenderResumenPagoExitoso(pago);
	if (btnImprimir) {
		btnImprimir.disabled = !cobrarCuotaUltimoRecibo;
	}
	modal.style.display = "";
	var btnOtro = cobrarCuotaId("btnCobrarCuotaExitoOtroPlan");
	if (btnOtro) {
		setTimeout(function() { btnOtro.focus(); }, 80);
	}
}

function cobrarCuotaCerrarModalExito() {
	var modal = cobrarCuotaId("divCobrarCuotaExito");
	if (modal) {
		modal.style.display = "none";
	}
}

function cobrarCuotaRegistrarOtroPagoPlan() {
	var ventaActual = cobrarCuotaPlanSeleccionado ? (cobrarCuotaPlanSeleccionado.cod_venta || cobrarCuotaPlanSeleccionado.venta_id) : "";
	if (ventaActual == "" && cobrarCuotaUltimoPagoRegistrado) {
		ventaActual = cobrarCuotaUltimoPagoRegistrado.codVenta || "";
	}
	cobrarCuotaCerrarModalExito();
	cobrarCuotaLimpiarDatosCobroActual(true);
	if (ventaActual != "") {
		if (cobrarCuotaPlanSeleccionado) {
			cobrarCuotaCargarCuotas(ventaActual);
		} else {
			cobrarCuotaCargarPlanes("", ventaActual);
		}
	}
}

function cobrarCuotaNuevoCobroDesdeExito() {
	cobrarCuotaCerrarModalExito();
	cobrarCuotaLimpiar();
}

function cobrarCuotaMostrarResultado(titulo, detalle, tipo) {
	var contenedor = cobrarCuotaId("divCobrarCuotaResultado");
	if (!contenedor) {
		return;
	}
	contenedor.innerHTML = "<div class='cobrar-cuota__resultado cobrar-cuota__resultado--" + cobrarCuotaEscape(tipo || "exito") + "'>"
		+ "<b>" + cobrarCuotaEscape(titulo) + "</b>"
		+ "<span>" + cobrarCuotaEscape(detalle) + "</span>"
		+ "<input type='button' value='Imprimir recibo' class='btn4 cobrar-cuota__btn-secundario' onclick='cobrarCuotaImprimirRecibo()'>"
		+ "</div>";
}

function cobrarCuotaReciboValor(valor, defecto) {
	if (valor === undefined || valor === null || String(valor).trim() == "") {
		return defecto !== undefined ? String(defecto) : "-";
	}
	return String(valor);
}

function cobrarCuotaReciboFila(etiqueta, valor, anchoEtiqueta) {
	return "<table class='tableTicket'><tr>"
		+ "<td style='width:" + (anchoEtiqueta || "80px") + "'><b>" + cobrarCuotaEscape(etiqueta) + ":</b></td>"
		+ "<td style='word-break:break-word;overflow-wrap:anywhere;'>" + cobrarCuotaEscape(cobrarCuotaReciboValor(valor)) + "</td>"
		+ "</tr></table>";
}

function cobrarCuotaReciboFecha(fecha) {
	fecha = String(fecha || "").trim();
	if (fecha == "") {
		return null;
	}
	var partes = fecha.replace(/\//g, "-").split("-");
	if (partes.length == 3) {
		var anho = partes[0].length == 4 ? partes[0] : partes[2];
		var mes = partes[0].length == 4 ? partes[1] : partes[1];
		var dia = partes[0].length == 4 ? partes[2] : partes[0];
		var resultado = new Date(Number(anho), Number(mes) - 1, Number(dia));
		return isNaN(resultado.getTime()) ? null : resultado;
	}
	var fechaDate = new Date(fecha);
	return isNaN(fechaDate.getTime()) ? null : fechaDate;
}

function cobrarCuotaReciboDiasAtraso(fechaVencimiento, fechaPago) {
	var vencimiento = cobrarCuotaReciboFecha(fechaVencimiento);
	var pago = cobrarCuotaReciboFecha(fechaPago) || new Date();
	if (!vencimiento) {
		return "0";
	}
	var diff = Math.floor((pago.getTime() - vencimiento.getTime()) / 86400000);
	return String(Math.max(0, diff));
}

function cobrarCuotaReciboConcepto(r) {
	if (r.detalles && r.detalles.length > 1 && r.venta) {
		return "Pago de cuotas seleccionadas - Factura nro: " + r.venta;
	}
	if (r.venta) {
		return "Factura nro: " + r.venta;
	}
	if (r.cuota) {
		return "Pago de cuota " + r.cuota;
	}
	return "Pago de cuota";
}

function cobrarCuotaReciboTipoCorto(tipo) {
	var texto = String(tipo || "").toUpperCase();
	if (texto.indexOf("TRANSFER") !== -1) {
		return "TRANSF.";
	}
	if (texto.indexOf("EFECT") !== -1) {
		return "EFECT.";
	}
	if (texto.indexOf("TARJ") !== -1) {
		return "TARJ.";
	}
	return tipo || "PAGO";
}

function cobrarCuotaImprimirRecibo() {
	if (!cobrarCuotaUltimoRecibo) {
		cobrarCuotaAviso("No hay recibo para imprimir");
		return;
	}
	var r = cobrarCuotaUltimoRecibo;
	var numeroRecibo = cobrarCuotaReciboValor(r.numero || r.nroRecibo || r.nro_recibo || r.recibo || r.venta);
	var fechaPago = cobrarCuotaReciboValor(r.fecha, "");
	var fechaVencimiento = cobrarCuotaReciboValor(r.fechaVencimiento || r.vencimiento, "-");
	var diasAtraso = cobrarCuotaReciboDiasAtraso(fechaVencimiento, fechaPago);
	var concepto = cobrarCuotaReciboConcepto(r);
	var monto = cobrarCuotaReciboValor(r.monto, "0");
	var totalCuota = cobrarCuotaReciboValor(r.totalVenta || r.totalVentaFmt || r.montoVenta || r.total_factura || r.saldoCuotaAnterior || r.montoCuota || r.totalPlan || r.monto, "0");
	var saldoActual = cobrarCuotaReciboValor(r.saldoVentaRestante || r.saldoActualVenta || r.saldoActual || r.saldoCuotaRestante || "0", "0");
	var estadoRecibo = cobrarCuotaReciboValor(r.estado || r.estadoFinalCuota, "Registrado");
	var tipoPagoCorto = cobrarCuotaReciboTipoCorto(r.formaPago);
	var detalleUeno = "";
	if (r.banco) {
		detalleUeno += "<br><b>Banco:</b> " + cobrarCuotaEscape(r.banco);
	}
	if (r.comprobante && r.comprobante != "-") {
		detalleUeno += "<br><b>Comprobante:</b> " + cobrarCuotaEscape(r.comprobante);
	}
	if (r.movimientoUeno || r.idMovimientoUeno) {
		detalleUeno += "<br><b>Movimiento Ueno:</b> " + cobrarCuotaEscape(r.movimientoUeno || r.idMovimientoUeno);
		detalleUeno += "<br><b>Disponible anterior:</b> " + cobrarCuotaEscape(r.saldoDisponibleAnterior || "0") + " Gs.";
		detalleUeno += "<br><b>Saldo aplicado Ueno:</b> " + cobrarCuotaEscape(r.saldoAplicadoUeno || "0") + " Gs.";
		detalleUeno += "<br><b>Disponible restante:</b> " + cobrarCuotaEscape(r.saldoDisponibleRestante || "0") + " Gs.";
		if (r.saldoFavor && cobrarCuotaNumero(r.saldoFavor) > 0) {
			detalleUeno += "<br><b>Saldo a favor:</b> " + cobrarCuotaEscape(r.saldoFavor) + " Gs.";
		}
	}
	if (detalleUeno != "") {
		detalleUeno = "<div class='cobrar-cuota-recibo-ueno'>"
			+ "<b>Detalle de transferencia:</b>"
			+ detalleUeno
			+ "<br><b>Estado:</b> " + cobrarCuotaEscape(estadoRecibo)
			+ "</div>";
	}

	var detallesRecibo = (r.detalles && r.detalles.length) ? r.detalles : [{
		cuota: r.cuota || "",
		fechaVencimiento: r.fechaVencimiento || r.vencimiento || "",
		producto: r.producto || "",
		monto: monto
	}];
	var detalleCuota = "";
	for (var i = 0; i < detallesRecibo.length; i++) {
		var detalle = detallesRecibo[i] || {};
		var descripcionCuota = cobrarCuotaEscape("PAGO DE CUOTA--" + cobrarCuotaReciboValor(detalle.cuota, ""));
		var productoDetalle = detalle.producto || r.producto || "";
		if (productoDetalle) {
			descripcionCuota += "<br>" + cobrarCuotaEscape(String(productoDetalle).toUpperCase());
		}
		detalleCuota += "<table class='tableTicket cobrar-cuota-recibo-detalle'>"
			+ "<tr>"
			+ "<td class='cobrar-cuota-recibo-fecha'>" + cobrarCuotaEscape(fechaPago) + "</td>"
			+ "<td class='cobrar-cuota-recibo-fecha'>" + cobrarCuotaEscape(cobrarCuotaReciboValor(detalle.fechaVencimiento || fechaVencimiento, "-")) + "</td>"
			+ "<td class='cobrar-cuota-recibo-desc'>" + descripcionCuota + "</td>"
			+ "<td class='cobrar-cuota-recibo-tipo'>" + cobrarCuotaEscape(tipoPagoCorto) + "</td>"
			+ "<td class='cobrar-cuota-recibo-importe'><b>" + cobrarCuotaEscape(cobrarCuotaReciboValor(detalle.monto, monto)) + " Gs.</b></td>"
			+ "</tr>"
			+ "</table>";
	}

	var estilos = "<style>"
		+ "@page{size:A4 portrait;margin:0.45cm;}"
		+ "body{margin:0;background:#fff;}"
		+ ".cobrar-cuota-recibo-copy{width:100%;height:131mm;position:relative;page-break-inside:avoid;break-inside:avoid;background:#fff;font-family:Arial,sans-serif;overflow:hidden;}"
		+ ".cobrar-cuota-recibo-print{width:86%;height:121mm;margin:0 auto;position:relative;background:#fff;padding:3mm 7mm 2mm 7mm;box-sizing:border-box;overflow:hidden;}"
		+ ".cobrar-cuota-recibo-print:before{content:'';position:absolute;inset:0;border:1px solid rgba(0,0,0,.18);border-radius:10px;pointer-events:none;}"
		+ ".cobrar-cuota-recibo-watermark{position:absolute;left:14%;top:32mm;width:72%;height:55mm;object-fit:contain;opacity:.10;z-index:0;}"
		+ ".cobrar-cuota-recibo-content{position:relative;z-index:1;}"
		+ ".cobrar-cuota-recibo-title{font-family:Arial,sans-serif;font-size:24px;font-weight:800;margin:0;text-align:center;line-height:24px;letter-spacing:0;}"
		+ ".cobrar-cuota-recibo-address{font-size:8px;font-family:Arial,sans-serif;line-height:10px;margin:1px 0 0;text-align:center;}"
		+ ".cobrar-cuota-recibo-label{display:inline-block;font-size:10px;font-weight:800;line-height:11px;background:#eceff3;border-radius:2px;padding:1px 8px;margin-top:2px;}"
		+ ".cobrar-cuota-recibo-marca{display:inline-block;margin-left:6px;font-size:9px;font-weight:800;line-height:11px;border:1px solid #111;border-radius:2px;padding:1px 7px;}"
		+ ".cobrar-cuota-recibo-head{text-align:center;margin-bottom:3mm;}"
		+ ".cobrar-cuota-recibo-data{width:100%;margin-top:1mm;table-layout:fixed;}"
		+ ".cobrar-cuota-recibo-data .tableTicket{width:98%;font-size:8px;line-height:10px;margin:0;font-family:Arial,sans-serif;}"
		+ ".cobrar-cuota-recibo-data td{vertical-align:top;}"
		+ ".cobrar-cuota-recibo-concepto{font-size:10px;font-family:Arial,sans-serif;text-align:left;margin:5mm 0 2mm 0;font-weight:400;}"
		+ ".cobrar-cuota-recibo-concepto b{font-weight:800;}"
		+ ".cobrar-cuota-recibo-body{width:100%;table-layout:fixed;}"
		+ ".cobrar-cuota-recibo-body .tableTicket{width:100%;font-size:8px;line-height:9px;margin:0;font-family:Arial,sans-serif;}"
		+ ".cobrar-cuota-recibo-header{font-weight:800;font-size:8px;line-height:9px;text-align:center;}"
		+ ".cobrar-cuota-recibo-detalle{font-size:8px;line-height:9px;margin-top:2mm;}"
		+ ".cobrar-cuota-recibo-fecha{width:16%;text-align:center;}"
		+ ".cobrar-cuota-recibo-desc{width:34%;text-align:left;word-break:break-word;overflow-wrap:anywhere;}"
		+ ".cobrar-cuota-recibo-tipo{width:15%;text-align:center;}"
		+ ".cobrar-cuota-recibo-importe{width:19%;text-align:right;white-space:nowrap;}"
		+ ".cobrar-cuota-recibo-totales{width:100%;font-size:8px;line-height:9px;font-family:Arial,sans-serif;}"
		+ ".cobrar-cuota-recibo-totales td:first-child{width:52%;font-weight:800;text-align:left;}"
		+ ".cobrar-cuota-recibo-totales td:last-child{text-align:right;font-weight:800;white-space:nowrap;}"
		+ ".cobrar-cuota-recibo-ueno{width:92%;border:1px solid #7fa6c3;background:#eef7ff;padding:3px;margin:2mm auto 0;font-size:7px;text-align:left;line-height:9px;box-sizing:border-box;}"
		+ ".cobrar-cuota-recibo-separador{height:8mm;}"
		+ "@media print{.cobrar-cuota-recibo-copy{height:131mm;}.cobrar-cuota-recibo-separador{height:6mm;}}"
		+ "</style>";

	var construirCopiaRecibo = function(marca) {
		return "<div class='cobrar-cuota-recibo-copy'>"
			+ "<div class='divTicket cobrar-cuota-recibo-print'>"
			+ "<img class='cobrar-cuota-recibo-watermark' src='/GoodVentaAsisCap/iconos/iconoEmpresa.JPG' />"
			+ "<div class='cobrar-cuota-recibo-content'>"
			+ "<div class='cobrar-cuota-recibo-head'>"
			+ "<p class='cobrar-cuota-recibo-title'>CLINIDENT</p>"
			+ "<p class='cobrar-cuota-recibo-address'>Humait&aacute; esq. Dr. Bottrel<br>Cel: (0982) 104 622<br>Villarrica - Paraguay</p>"
			+ "<span class='cobrar-cuota-recibo-label'>RECIBO DE DINERO</span>"
			+ "<span class='cobrar-cuota-recibo-marca'>" + cobrarCuotaEscape(marca) + "</span>"
			+ "</div>"
			+ "<table class='cobrar-cuota-recibo-data'>"
			+ "<tr>"
			+ "<td style='width:50%'>"
			+ cobrarCuotaReciboFila("Numero", numeroRecibo, "80px")
			+ cobrarCuotaReciboFila("Cliente", r.cliente, "60px")
			+ cobrarCuotaReciboFila("RUC o C.I.", r.cedula, "85px")
			+ "</td>"
			+ "<td style='width:50%'>"
			+ cobrarCuotaReciboFila("Fecha", fechaPago, "60px")
			+ cobrarCuotaReciboFila("Cajero", r.cajero, "60px")
			+ cobrarCuotaReciboFila("D. Atrasado", diasAtraso + " Dia(s)", "100px")
			+ "</td>"
			+ "</tr>"
			+ "</table>"
			+ "<p class='cobrar-cuota-recibo-concepto'><b>EN CONCEPTO DE :</b>" + cobrarCuotaEscape(concepto) + "</p>"
			+ "<table class='cobrar-cuota-recibo-body'><tr><td style='width:67%;padding-right:4mm;'>"
			+ "<table class='tableTicket cobrar-cuota-recibo-header'>"
			+ "<tr>"
			+ "<td style='width:16%;'>FECHA P.</td>"
			+ "<td style='width:16%;'>FECHA<br>VENC.</td>"
			+ "<td style='width:34%;'>DESCRIPCION</td>"
			+ "<td style='width:15%;'>TIPO</td>"
			+ "<td style='width:19%;text-align:right;'>IMPORTE</td>"
			+ "</tr>"
			+ "</table>"
			+ detalleCuota
			+ "</td>"
			+ "<td style='width:33%;'>"
			+ "<table class='cobrar-cuota-recibo-totales'>"
			+ "<tr><td>TOTAL FACTURA:</td><td>" + cobrarCuotaEscape(totalCuota) + " Gs.</td></tr>"
			+ "<tr><td>TOTAL PAGADO:</td><td>" + cobrarCuotaEscape(monto) + " Gs.</td></tr>"
			+ "<tr><td>TOTAL DESCUENTO:</td><td>0 Gs.</td></tr>"
			+ "<tr><td>SALDO ACTUAL:</td><td>" + cobrarCuotaEscape(saldoActual) + " Gs.</td></tr>"
			+ "</table>"
			+ "</td>"
			+ "</tr></table>"
			+ detalleUeno
			+ "</div>"
			+ "</div>"
			+ "</div>";
	};
	var pagina = estilos + construirCopiaRecibo("ORIGINAL") + "<div class='cobrar-cuota-recibo-separador'></div>" + construirCopiaRecibo("DUPLICADO");

	var contenedor = document.getElementById("DivImprimir");
	if (contenedor) {
		contenedor.innerHTML = pagina;
		localStorage.setItem("reporte", contenedor.innerHTML);
		localStorage.setItem("tipo", "ticket");
		window.open("/GoodVentaAsisCap/system/reportTicket.html");
		contenedor.innerHTML = "";
		return;
	}
	localStorage.setItem("reporte", pagina);
	localStorage.setItem("tipo", "ticket");
	window.open("/GoodVentaAsisCap/system/reportTicket.html");
}

function abrirCobrarCuotaDesdeCuentas() {
	if (typeof idFkVenta === "undefined" || idFkVenta == "") {
		cobrarCuotaAviso("Selecciona primero una cuenta en Cuentas a Cobrar");
		return;
	}
	verCerrarCobrarCuota("1", { origen: "cuentas", venta: idFkVenta });
}

function cobrarCuotaAbrirDesdeUeno(movimiento) {
	if (!movimiento || !movimiento.id_movimiento) {
		cobrarCuotaAviso("Selecciona primero un movimiento Ueno");
		return;
	}
	verCerrarCobrarCuota("1", { origen: "ueno", movimiento: movimiento });
}
