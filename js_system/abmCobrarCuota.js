var cobrarCuotaSeleccionada = null;
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
	if (cobrarCuotaContextoUeno.coincidencia_exacta === false) {
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
		puede_usar: movimiento.puede_usar !== false
	};
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
	cobrarCuotaClienteSeleccionado = null;
	cobrarCuotaPlanSeleccionado = null;
	cobrarCuotaClientes = [];
	cobrarCuotaPlanes = [];
	cobrarCuotaCuotas = [];
	cobrarCuotaUltimoRecibo = null;
	cobrarCuotaUltimoPagoRegistrado = null;
	cobrarCuotaFiltroEstado = "todas";
	cobrarCuotaSetHoy();
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
			cobrarCuotaMostrarContextoUeno();
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
	var texto = select && select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : "";
	var transferencia = cobrarCuotaEsTransferenciaTexto(texto);
	var panelTransfer = cobrarCuotaId("divCobrarCuotaTransferencia");
	var panelEfectivo = cobrarCuotaId("divCobrarCuotaEfectivo");
	if (panelTransfer) {
		panelTransfer.style.display = transferencia ? "" : "none";
	}
	if (panelEfectivo) {
		panelEfectivo.style.display = transferencia ? "none" : "";
	}
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
	var select = cobrarCuotaId("inptCobrarCuotaTipoPago");
	var texto = select && select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : "";
	var transferencia = cobrarCuotaEsTransferenciaTexto(texto);
	if (transferencia && cobrarCuotaTieneMovimientoUenoValido()) {
		btn.value = "Registrar y conciliar";
	} else if (transferencia && cobrarCuotaUenoBusquedaActiva && cobrarCuotaUenoResultadosTotal > 0) {
		btn.value = "Selecciona movimiento Ueno";
	} else if (transferencia) {
		btn.value = "Registrar pendiente de conciliacion";
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
	var select = cobrarCuotaId("inptCobrarCuotaTipoPago");
	var texto = select && select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : "";
	var transferencia = cobrarCuotaEsTransferenciaTexto(texto);
	var bloquearPorUeno = transferencia && !cobrarCuotaTieneMovimientoUenoValido() && cobrarCuotaUenoBusquedaActiva && cobrarCuotaUenoResultadosTotal > 0;
	btn.disabled = cobrarCuotaProcesando || !cobrarCuotaSeleccionada || !cobrarCuotaSeleccionada.idcredito || !cobrarCuotaEsCuotaCobrable(cobrarCuotaSeleccionada) || bloquearPorUeno;
	if (!cobrarCuotaSeleccionada || !cobrarCuotaSeleccionada.idcredito) {
		btn.title = "Selecciona una cuota para registrar el cobro";
	} else if (!cobrarCuotaEsCuotaCobrable(cobrarCuotaSeleccionada)) {
		btn.title = "Esta cuota no se puede cobrar porque ya esta pagada o anulada";
	} else if (bloquearPorUeno) {
		btn.title = "Hay movimientos Ueno candidatos. Selecciona uno con comprobante exacto o corrige la busqueda.";
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
	cobrarCuotaMarcarFila("");
	cobrarCuotaActualizarResumenUeno();
	cobrarCuotaActualizarBotonRegistrar();
}

function cobrarCuotaLimpiarDatosCobroActual(limpiarResultado) {
	cobrarCuotaContextoUeno = null;
	cobrarCuotaResetBusquedaUeno();
	cobrarCuotaSeleccionada = null;
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
						var ventaPlanActual = String(cobrarCuotaPlanSeleccionado.cod_venta || cobrarCuotaPlanSeleccionado.venta_id || "");
						for (var i = 0; i < cobrarCuotaPlanes.length; i++) {
							if (String(cobrarCuotaPlanes[i].cod_venta || cobrarCuotaPlanes[i].venta_id || "") == ventaPlanActual) {
								cobrarCuotaPlanes[i].cuotas_pendientes = respuesta.total || "0";
								cobrarCuotaPlanes[i].saldo_pendiente_total_fmt = respuesta.saldo_total || "0";
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
		var seleccionado = cobrarCuotaSeleccionada && String(cobrarCuotaSeleccionada.idcredito) == String(cuota.idcredito);
		var cobrable = cobrarCuotaEsCuotaCobrable(cuota);
		var estadoSlug = cobrarCuotaEstadoSlug(cuota.estado);
		var accion = cobrable
			? "<input type='button' value='Cobrar esta cuota' class='btn4 cobrar-cuota__btn-tabla' onclick='cobrarCuotaSeleccionarPorId(\"" + cobrarCuotaEscape(cuota.idcredito) + "\")'>"
			: "<span class='cobrar-cuota__accion-segura'>" + (cobrarCuotaNormalizarTexto(cuota.estado) == "PAGADA" ? "Cobro registrado" : "No cobrable") + "</span>";
		html += "<table class='tableRegistroSearch cobrar-cuota__result-table' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' class='cobrar-cuota__result-row" + (seleccionado ? " cobrar-cuota__result-row--selected" : "") + (!cobrable ? " cobrar-cuota__result-row--locked" : "") + "' data-cobrar-cuota-id='" + cobrarCuotaEscape(cuota.idcredito) + "'>"
			+ "<td data-label='Seleccionar' style='width:6%;text-align:center'>" + (cobrable ? "<input type='radio' name='cobrarCuotaRadio' " + (seleccionado ? "checked" : "") + " onclick='cobrarCuotaSeleccionarPorId(\"" + cobrarCuotaEscape(cuota.idcredito) + "\")'>" : "<span class='cobrar-cuota__radio-placeholder'></span>") + "</td>"
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

function cobrarCuotaSeleccionar(cuota) {
	cobrarCuotaSeleccionada = cuota || null;
	if (!cobrarCuotaSeleccionada) {
		return;
	}
	if (cobrarCuotaPlanSeleccionado && String(cobrarCuotaSeleccionada.venta_id || cobrarCuotaSeleccionada.cod_venta) != String(cobrarCuotaPlanSeleccionado.cod_venta || cobrarCuotaPlanSeleccionado.venta_id)) {
		cobrarCuotaSeleccionada = null;
		cobrarCuotaAviso("La cuota seleccionada no pertenece al plan elegido", "error");
		cobrarCuotaActualizarBotonRegistrar();
		return;
	}
	if (!cobrarCuotaEsCuotaCobrable(cobrarCuotaSeleccionada)) {
		cobrarCuotaSeleccionada = null;
		cobrarCuotaAviso("No se puede cobrar una cuota pagada o anulada", "error");
		cobrarCuotaActualizarBotonRegistrar();
		return;
	}
	cobrarCuotaMarcarFila(cobrarCuotaSeleccionada.idcredito);
	cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", cobrarCuotaSeleccionada.saldo_pendiente || "");
	cobrarCuotaSetValor("inptCobrarCuotaMontoRecibido", cobrarCuotaSeleccionada.saldo_pendiente || "");
	cobrarCuotaCalcularVuelto();
	var contenedor = cobrarCuotaId("divCobrarCuotaSeleccionada");
	if (contenedor) {
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
	}
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
		var seleccionada = String(filas[i].getAttribute("data-cobrar-cuota-id")) == String(idcredito);
		var radio = filas[i].querySelector("input[type='radio']");
		if (radio) {
			radio.checked = seleccionada && String(idcredito || "") != "";
		}
		if (seleccionada && String(idcredito || "") != "") {
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
	for (var i = 0; i < cobrarCuotaCuotas.length; i++) {
		var cuota = cobrarCuotaCuotas[i];
		if (!cobrarCuotaEsCuotaCobrable(cuota)) {
			continue;
		}
		if (cobrarCuotaSeleccionada && String(cuota.idcredito) == String(cobrarCuotaSeleccionada.idcredito)) {
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
	var disponible = Number(cobrarCuotaContextoUeno.monto_disponible || 0);
	var montoSolicitado = cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "");
	var saldoCuota = cobrarCuotaSeleccionada ? Number(cobrarCuotaSeleccionada.saldo_pendiente_num || 0) : 0;
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
		html += "<div class='cobrar-cuota__ueno-note'>La cuota quedara como Pago parcial si registras este monto.</div>";
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

function cobrarCuotaBuscarMovimientoUeno() {
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
	if (cobrarCuotaTieneMovimientoUenoValido()) {
		cobrarCuotaMostrarContextoUeno();
		return;
	}
	var comprobante = cobrarCuotaId("inptCobrarCuotaComprobante") ? cobrarCuotaId("inptCobrarCuotaComprobante").value : "";
	var monto = cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "";
	var fechaPago = cobrarCuotaId("inptCobrarCuotaFechaPago") ? cobrarCuotaId("inptCobrarCuotaFechaPago").value : "";
	if (comprobante == "" && monto == "") {
		cobrarCuotaResetBusquedaUeno();
		contenedor.innerHTML = "<div class='cobrar-cuota__ueno-empty'>Ingresa comprobante o monto para buscar un movimiento Ueno candidato.</div>";
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
					contenedor.innerHTML = datos["2"];
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
	if (movimiento.puede_usar === false || movimiento.coincidencia_exacta === false || movimiento.monto_valido === false) {
		cobrarCuotaAuditar(
			"INTENTO_USAR_MOVIMIENTO_UENO_BLOQUEADO",
			"rechazado",
			"validacion_visual",
			cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : ""),
			"Transferencia",
			movimiento.comprobante_masked || "",
			movimiento.mensaje_accion || "Movimiento Ueno no habilitado para aplicar"
		);
		cobrarCuotaAviso(movimiento.mensaje_accion || "Para usar el movimiento se requiere comprobante exacto, saldo disponible y monto valido.", "error");
		return;
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
	if (!confirm("No se usara ningun movimiento Ueno de la lista. Deseas registrar este cobro como pendiente de conciliacion?")) {
		return;
	}
	cobrarCuotaForzarPendienteUeno = true;
	var contexto = cobrarCuotaObtenerContextoRegistro();
	cobrarCuotaForzarPendienteUeno = false;
	if (!contexto) {
		return;
	}
	contexto.forzarPendienteUeno = true;
	cobrarCuotaAuditar(
		"REGISTRAR_PENDIENTE_UENO_SOLICITADO",
		"pendiente_solicitado",
		"pendiente_conciliacion",
		contexto.monto,
		contexto.textoTipo,
		cobrarCuotaMaskComprobante(contexto.comprobante || ""),
		"El usuario eligio registrar pendiente aunque habia movimientos Ueno candidatos"
	);
	cobrarCuotaAbrirConfirmacion(contexto);
}

function cobrarCuotaObtenerContextoRegistro() {
	if (cobrarCuotaProcesando) {
		return null;
	}
	if (!cobrarCuotaTienePermiso("REGISTRARCOBRARCUOTA")) {
		cobrarCuotaAviso("No tiene permiso para registrar cobros", "error");
		return null;
	}
	if (!cobrarCuotaSeleccionada || !cobrarCuotaSeleccionada.idcredito) {
		cobrarCuotaAviso("Selecciona una cuota antes de registrar el cobro");
		return null;
	}
	if (!cobrarCuotaEsCuotaCobrable(cobrarCuotaSeleccionada)) {
		cobrarCuotaAviso("No se puede cobrar una cuota pagada o anulada", "error");
		return null;
	}
	if (typeof idabmAperturacierrecaja !== "undefined" && idabmAperturacierrecaja == "") {
		cobrarCuotaAviso("Falta iniciar una caja antes de registrar cobros");
		if (typeof verCerrarVentanaAbmAperturaCierreCaja1 === "function") {
			verCerrarVentanaAbmAperturaCierreCaja1();
		}
		return null;
	}
	var monto = cobrarCuotaNumero(cobrarCuotaId("inptCobrarCuotaMontoCobrar") ? cobrarCuotaId("inptCobrarCuotaMontoCobrar").value : "");
	var saldo = Number(cobrarCuotaSeleccionada.saldo_pendiente_num || 0);
	if (monto <= 0) {
		cobrarCuotaAviso("Ingresa un monto mayor a cero");
		return null;
	}
	if (monto > saldo) {
		cobrarCuotaAviso("No se puede cobrar mas que el saldo pendiente");
		cobrarCuotaSetValor("inptCobrarCuotaMontoCobrar", cobrarCuotaSeleccionada.saldo_pendiente || "");
		return null;
	}
	var fecha = cobrarCuotaId("inptCobrarCuotaFechaPago") ? cobrarCuotaId("inptCobrarCuotaFechaPago").value : "";
	if (fecha == "") {
		cobrarCuotaAviso("Ingresa la fecha de pago");
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
	var comprobante = cobrarCuotaId("inptCobrarCuotaComprobante") ? cobrarCuotaId("inptCobrarCuotaComprobante").value.replace(/\s+/g, "").trim() : "";
	if (transferencia && comprobante == "") {
		cobrarCuotaAviso("Ingresa el numero de comprobante de transferencia");
		return null;
	}
	if (transferencia && !cobrarCuotaForzarPendienteUeno && !cobrarCuotaTieneMovimientoUenoValido() && cobrarCuotaUenoBusquedaActiva && cobrarCuotaUenoResultadosTotal > 0) {
		cobrarCuotaAviso("Hay movimientos Ueno candidatos. Selecciona uno con comprobante exacto o corrige la busqueda antes de registrar pendiente.", "error");
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
		banco: cobrarCuotaId("inptCobrarCuotaBanco") ? cobrarCuotaId("inptCobrarCuotaBanco").value : "",
		montoRecibido: cobrarCuotaId("inptCobrarCuotaMontoRecibido") ? cobrarCuotaId("inptCobrarCuotaMontoRecibido").value : "",
		vuelto: cobrarCuotaId("inptCobrarCuotaVuelto") ? cobrarCuotaId("inptCobrarCuotaVuelto").value : "",
		observacion: cobrarCuotaId("txtCobrarCuotaObservacion") ? cobrarCuotaId("txtCobrarCuotaObservacion").value : ""
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
	if (!contexto || !cobrarCuotaSeleccionada) { return ""; }
	var cuota = cobrarCuotaSeleccionada;
	var cliente = "";
	cliente += cobrarCuotaConfirmacionFila("Cliente", cuota.cliente || "");
	cliente += cobrarCuotaConfirmacionFila("Cedula", cuota.cedula || "");
	cliente += cobrarCuotaConfirmacionFila("Venta", cuota.venta || cuota.venta_id || cuota.cod_venta || "");
	cliente += cobrarCuotaConfirmacionFila("Alias / apodo", cuota.alias || "");

	var detalleCuota = "";
	detalleCuota += cobrarCuotaConfirmacionFila("Producto / plan", cuota.producto || "", "cobrar-cuota-confirmacion__row--wide");
	detalleCuota += cobrarCuotaConfirmacionFila("Cuota", cuota.cuota || "");
	detalleCuota += cobrarCuotaConfirmacionFila("Vencimiento", cuota.fecha_vencimiento || "");
	detalleCuota += cobrarCuotaConfirmacionFilaMonto("Monto cuota", cuota.monto_cuota || "");
	detalleCuota += cobrarCuotaConfirmacionFilaMonto("Saldo pendiente", cuota.saldo_pendiente || "");

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
	} else {
		pago += cobrarCuotaConfirmacionFilaMonto("Monto recibido", contexto.montoRecibido ? contexto.montoRecibido + " Gs." : "");
		pago += cobrarCuotaConfirmacionFilaMonto("Vuelto", contexto.vuelto ? contexto.vuelto + " Gs." : "");
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

function cobrarCuotaEjecutarRegistro(contexto) {
	contexto = contexto || cobrarCuotaObtenerContextoRegistro();
	if (!contexto || cobrarCuotaProcesando) { return; }
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
	datos.append("Fecha", fecha);
	datos.append("totalDeudaCuota", cobrarCuotaFormato(cobrarCuotaSeleccionada.saldo_cuota_num || saldo));
	datos.append("cod_creditoFK", cobrarCuotaSeleccionada.idcredito);
	datos.append("cod_cobradorFK", cobrarCuotaSeleccionada.cobrador_id || userid);
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
						cobrarCuotaConciliarTransferencia(comprobante, monto, textoTipo, contexto);
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
	var ueno = cobrarCuotaClonarSimple(opciones.ueno || cobrarCuotaContextoUeno || {});
	var montoAplicado = Number(opciones.montoAplicado !== undefined ? opciones.montoAplicado : (contexto.monto || 0)) || 0;
	var saldoCuotaAnterior = Number(cuota.saldo_pendiente_num || contexto.saldo || 0) || 0;
	var saldoCuotaRestante = Math.max(0, saldoCuotaAnterior - montoAplicado);
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
		producto: cuota.producto || plan.producto || "",
		montoAplicado: montoAplicado,
		montoAplicadoFmt: cobrarCuotaFormato(montoAplicado),
		formaPago: contexto.textoTipo || "",
		banco: contexto.banco || (tieneUeno ? "Ueno" : ""),
		comprobante: cobrarCuotaMaskComprobante(contexto.comprobante || ""),
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
		fecha: pago.fecha || "",
		cliente: pago.cliente || "",
		cedula: pago.cedula || "",
		venta: pago.venta || "",
		cuota: pago.cuota || "",
		producto: pago.producto || "",
		monto: pago.montoAplicadoFmt || "0",
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
	return {
		fecha: cobrarCuotaId("inptCobrarCuotaFechaPago") ? cobrarCuotaId("inptCobrarCuotaFechaPago").value : "",
		cliente: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.cliente : "",
		cedula: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.cedula : "",
		venta: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.venta : "",
		cuota: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.cuota : "",
		producto: cobrarCuotaSeleccionada ? cobrarCuotaSeleccionada.producto : "",
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
	datosPago += cobrarCuotaConfirmacionFila("Fecha de pago", pago.fecha || "");
	datosPago += cobrarCuotaConfirmacionFila("Forma de pago", pago.formaPago || "");
	datosPago += cobrarCuotaConfirmacionFila("Banco", pago.banco || "");
	datosPago += cobrarCuotaConfirmacionFila("Comprobante", pago.comprobante || "");
	datosPago += cobrarCuotaConfirmacionFilaMonto("Monto aplicado", (pago.montoAplicadoFmt || "0") + " Gs.");

	var datosCuota = "";
	datosCuota += cobrarCuotaConfirmacionFila("Venta", pago.venta || pago.codVenta || "");
	datosCuota += cobrarCuotaConfirmacionFila("Cliente", pago.cliente || "");
	datosCuota += cobrarCuotaConfirmacionFila("Cuota aplicada", pago.cuota || "");
	datosCuota += cobrarCuotaConfirmacionFila("Saldo anterior", (pago.saldoCuotaAnteriorFmt || "0") + " Gs.");
	datosCuota += cobrarCuotaConfirmacionFila("Saldo pendiente", (pago.saldoCuotaRestanteFmt || "0") + " Gs.");
	datosCuota += cobrarCuotaConfirmacionFila("Estado final", pago.estadoFinalCuota || "");

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

function cobrarCuotaImprimirRecibo() {
	if (!cobrarCuotaUltimoRecibo) {
		cobrarCuotaAviso("No hay recibo para imprimir");
		return;
	}
	var r = cobrarCuotaUltimoRecibo;
	var detalleUeno = "";
	if (r.banco) {
		detalleUeno += "<div class='fila'><b>Banco</b><span>" + cobrarCuotaEscape(r.banco) + "</span></div>";
	}
	if (r.movimientoUeno || r.idMovimientoUeno) {
		detalleUeno += "<div class='fila'><b>Movimiento Ueno</b><span>" + cobrarCuotaEscape(r.movimientoUeno || r.idMovimientoUeno) + "</span></div>";
		detalleUeno += "<div class='fila'><b>Disponible anterior</b><span>" + cobrarCuotaEscape(r.saldoDisponibleAnterior || "0") + " Gs.</span></div>";
		detalleUeno += "<div class='fila'><b>Saldo aplicado Ueno</b><span>" + cobrarCuotaEscape(r.saldoAplicadoUeno || "0") + " Gs.</span></div>";
		detalleUeno += "<div class='fila'><b>Disponible restante</b><span>" + cobrarCuotaEscape(r.saldoDisponibleRestante || "0") + " Gs.</span></div>";
		if (r.saldoFavor && cobrarCuotaNumero(r.saldoFavor) > 0) {
			detalleUeno += "<div class='fila'><b>Saldo a favor</b><span>" + cobrarCuotaEscape(r.saldoFavor) + " Gs.</span></div>";
		}
	}
	var html = "<html><head><title>Recibo de cobro</title>"
		+ "<style>body{font-family:Arial,sans-serif;margin:28px;color:#172033}h1{font-size:22px;margin:0 0 12px}.recibo{max-width:620px;border:1px solid #d8e2ee;border-radius:8px;padding:18px}.fila{display:flex;justify-content:space-between;border-bottom:1px solid #edf2f7;padding:8px 0}.fila b{color:#475569}.estado{margin-top:14px;padding:10px;border-radius:8px;background:#eef6ff;color:#123f66;font-weight:700}@media print{button{display:none}}</style>"
		+ "</head><body><div class='recibo'><h1>Recibo de cobro de cuota</h1>"
		+ "<div class='fila'><b>Fecha</b><span>" + cobrarCuotaEscape(r.fecha) + "</span></div>"
		+ "<div class='fila'><b>Cliente</b><span>" + cobrarCuotaEscape(r.cliente) + "</span></div>"
		+ "<div class='fila'><b>Cedula</b><span>" + cobrarCuotaEscape(r.cedula) + "</span></div>"
		+ "<div class='fila'><b>Venta</b><span>" + cobrarCuotaEscape(r.venta) + "</span></div>"
		+ "<div class='fila'><b>Cuota</b><span>" + cobrarCuotaEscape(r.cuota) + "</span></div>"
		+ "<div class='fila'><b>Producto</b><span>" + cobrarCuotaEscape(r.producto) + "</span></div>"
		+ "<div class='fila'><b>Monto cobrado</b><span>" + cobrarCuotaEscape(r.monto) + " Gs.</span></div>"
		+ "<div class='fila'><b>Forma de pago</b><span>" + cobrarCuotaEscape(r.formaPago) + "</span></div>"
		+ "<div class='fila'><b>Comprobante</b><span>" + cobrarCuotaEscape(r.comprobante || "-") + "</span></div>"
		+ detalleUeno
		+ "<div class='fila'><b>Cajero</b><span>" + cobrarCuotaEscape(r.cajero) + "</span></div>"
		+ "<div class='estado'>" + cobrarCuotaEscape(r.estado) + "</div>"
		+ "<br><button onclick='window.print()'>Imprimir</button></div></body></html>";
	var ventana = window.open("", "ReciboCobrarCuota", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=720,height=720");
	if (ventana) {
		ventana.document.open();
		ventana.document.write(html);
		ventana.document.close();
		ventana.focus();
	}
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
