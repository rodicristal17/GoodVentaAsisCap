var odontogramaEstados = {
	ficha: null,
	presupuesto: null
};
var odontogramaCargaFichaSecuencia = 0;
var odontogramaSelectorRapidoLaboratorioEstado = null;

var odontogramaPiezas = {
	superior: ["18","17","16","15","14","13","12","11","21","22","23","24","25","26","27","28"],
	inferior: ["48","47","46","45","44","43","42","41","31","32","33","34","35","36","37","38"]
};

var odontogramaFilas = [
	{ clase: "temporal-superior", denticion: "temporal", izquierda: ["55","54","53","52","51"], derecha: ["61","62","63","64","65"] },
	{ clase: "permanente-superior", denticion: "permanente", izquierda: ["18","17","16","15","14","13","12","11"], derecha: ["21","22","23","24","25","26","27","28"] },
	{ clase: "permanente-inferior", denticion: "permanente", izquierda: ["48","47","46","45","44","43","42","41"], derecha: ["31","32","33","34","35","36","37","38"] },
	{ clase: "temporal-inferior", denticion: "temporal", izquierda: ["85","84","83","82","81"], derecha: ["71","72","73","74","75"] }
];

var odontogramaSuperficies = [
	{ id: "vestibular", nombre: "Vestibular" },
	{ id: "mesial", nombre: "Mesial" },
	{ id: "oclusal_incisal", nombre: "Oclusal / Incisal" },
	{ id: "distal", nombre: "Distal" },
	{ id: "lingual_palatina", nombre: "Lingual / Palatina" }
];

var odontogramaModos = [
	{ id: "explorar", nombre: "Explorar" },
	{ id: "asignar", nombre: "Asignar tratamiento" },
	{ id: "hallazgo", nombre: "Situacion actual" },
	{ id: "editar", nombre: "Editar" }
];

var odontogramaFiltros = [
	{ id: "todo", nombre: "Todo" },
	{ id: "situacion", nombre: "Situacion actual" },
	{ id: "tratamientos", nombre: "Tratamientos" },
	{ id: "realizados", nombre: "Realizados" }
];

var odontogramaPasosClinicos = [
	{
		id: "situacion",
		numero: "1",
		titulo: "Situacion actual",
		texto: "Marca hallazgos actuales: caries, ausencias, obturaciones o protesis existentes.",
		modo: "hallazgo",
		filtro: "situacion"
	},
	{
		id: "tratamientos",
		numero: "2",
		titulo: "Tratamientos",
		texto: "Selecciona tratamientos y vinculalos a pieza, superficie, arcada o boca completa.",
		modo: "asignar",
		filtro: "tratamientos"
	},
	{
		id: "revision",
		numero: "3",
		titulo: "Revision",
		texto: "Revisa ubicaciones, pendientes e historial antes de guardar o convalidar.",
		modo: "editar",
		filtro: "todo"
	}
];

var odontogramaContextosConfig = {
	ficha: {
		titulo: "Odontograma clinico",
		mostrarPasosClinicos: true,
		mostrarRevision: true,
		mostrarHistorial: true,
		mostrarDeshacer: true,
		mostrarGuia: true,
		mostrarConvalidar: true,
		leyendaExpandida: false,
		modos: ["explorar", "asignar", "hallazgo", "editar"],
		filtros: ["todo", "situacion", "tratamientos", "realizados"],
		mostrarCuadrantes: true,
		superficiesAvanzadas: true,
		pasos: odontogramaPasosClinicos
	},
	presupuesto: {
		titulo: "Odontograma del presupuesto",
		mostrarPasosClinicos: false,
		mostrarRevision: false,
		mostrarHistorial: false,
		mostrarDeshacer: false,
		mostrarGuia: false,
		mostrarConvalidar: false,
		leyendaExpandida: false,
		modos: [],
		filtros: ["tratamientos"],
		mostrarCuadrantes: false,
		superficiesAvanzadas: false,
		pasos: [
			{
				id: "situacion",
				numero: "2",
				titulo: "Situacion actual",
				texto: "Marca solo lo necesario para preparar el presupuesto.",
				modo: "hallazgo",
				filtro: "situacion"
			},
			{
				id: "tratamientos",
				numero: "3",
				titulo: "Tratamientos",
				texto: "Selecciona piezas o ubicaciones y vincula tratamientos.",
				modo: "asignar",
				filtro: "tratamientos"
			}
		]
	}
};

function odontogramaConfig(contexto) {
	return odontogramaContextosConfig[contexto] || odontogramaContextosConfig.ficha;
}

function odontogramaEscape(valor) {
	return String(valor == null ? "" : valor)
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

function odontogramaEstadoInicial(extra) {
	var base = {
		modo: "hallazgo",
		pasoClinico: "situacion",
		filtroVisual: "todo",
		ayudaContextual: "",
		piezaSeleccionada: "",
		ubicacionActual: null,
		tratamientoSeleccionado: null,
		tratamientoPendiente: null,
		agregandoAutomatico: false,
		ubicacionAutomaticaPendiente: null,
		seleccionMultipleActiva: false,
		piezasMultiples: [],
		mensajeFlash: "",
		leyendaVisible: false
	};
	extra = extra || {};
	Object.keys(extra).forEach(function (key) {
		base[key] = extra[key];
	});
	return base;
}

function odontogramaNormalizarAlcance(alcance) {
	alcance = String(alcance || "").toLowerCase();
	return ["no_requiere", "boca_completa", "arcada", "cuadrante", "pieza_dental", "pieza_superficie", "piezas_multiples"].indexOf(alcance) >= 0
		? alcance
		: "pieza_dental";
}

function odontogramaTextoAlcance(alcance) {
	var mapa = {
		no_requiere: "No requiere odontograma",
		boca_completa: "Boca completa",
		arcada: "Arcada",
		cuadrante: "Cuadrante",
		pieza_dental: "Pieza dental",
		pieza_superficie: "Pieza + superficie",
		piezas_multiples: "Varias piezas"
	};
	return mapa[odontogramaNormalizarAlcance(alcance)] || "Pieza dental";
}

function odontogramaAlcancePresupuestoUnidadPieza() {
	return "pieza_dental";
}

function odontogramaAlcancePorModoIndividualizacion(producto) {
	producto = producto || {};
	var modo = String(producto.modo_individualizacion || "cantidad_libre");
	if (modo == "pieza_individual") { return "pieza_dental"; }
	if (modo == "multipieza" || modo == "sector") { return "piezas_multiples"; }
	if (modo == "arcada") { return "arcada"; }
	if (modo == "dispositivo") {
		return odontogramaNormalizarAlcance(producto.alcance_odontologico || producto.alcance || "boca_completa");
	}
	return odontogramaAlcancePresupuestoUnidadPieza(producto.alcance_odontologico || producto.alcance);
}

function odontogramaUbicacionPresupuestoUnidadPieza(ubicacion) {
	if (!ubicacion || !ubicacion.pieza) {
		return null;
	}
	return {
		pieza: ubicacion.pieza,
		denticion: ubicacion.denticion || odontogramaDenticionPieza(ubicacion.pieza)
	};
}

function odontogramaPiezasUbicacion(ubicacion) {
	var lista = [];
	if (!ubicacion) { return lista; }
	try {
		var dec = ubicacion.piezas_json ? JSON.parse(ubicacion.piezas_json) : [];
		if (Array.isArray(dec)) {
			dec.forEach(function (pieza) {
				pieza = String(pieza || "");
				if (pieza && lista.indexOf(pieza) < 0) { lista.push(pieza); }
			});
		}
	} catch (e) {}
	return lista;
}

function odontogramaTextoPiezasMultiples(piezas) {
	piezas = piezas || [];
	return piezas.length ? "Piezas " + piezas.join(", ") : "Varias piezas";
}

function odontogramaUbicacionPresupuestoPiezasMultiples(piezas) {
	piezas = piezas || [];
	if (!piezas.length) { return null; }
	return {
		piezas_json: JSON.stringify(piezas),
		piezas: piezas.slice()
	};
}

function odontogramaUbicacionPresupuestoNormalizada(ubicacion) {
	if (!ubicacion) { return null; }
	if (odontogramaPiezasUbicacion(ubicacion).length) {
		return ubicacion;
	}
	if (odontogramaEsVerdadero(ubicacion.boca_completa)) {
		return { boca_completa: 1 };
	}
	if (ubicacion.arcada) {
		return { arcada: odontogramaNormalizarArcada(ubicacion.arcada) };
	}
	if (ubicacion.cuadrante) {
		return { cuadrante: odontogramaNormalizarCuadrante(ubicacion.cuadrante) };
	}
	return odontogramaUbicacionPresupuestoUnidadPieza(ubicacion);
}

function odontogramaAlcanceUbicacionPresupuesto(ubicacion) {
	if (!ubicacion) { return "pieza_dental"; }
	if (odontogramaPiezasUbicacion(ubicacion).length) { return "piezas_multiples"; }
	if (odontogramaEsVerdadero(ubicacion.boca_completa)) { return "boca_completa"; }
	if (ubicacion.arcada) { return "arcada"; }
	if (ubicacion.cuadrante) { return "cuadrante"; }
	return "pieza_dental";
}

function odontogramaPresupuestoEstaAgregandoAutomatico() {
	return !!(odontogramaEstados.presupuesto && odontogramaEstados.presupuesto.agregandoAutomatico);
}

function odontogramaPresupuestoAutoFinalizar(ok, mensaje) {
	var estado = odontogramaEstados.presupuesto;
	if (!estado) { return; }
	estado.agregandoAutomatico = false;
	estado.ubicacionAutomaticaPendiente = null;
	estado.ubicacionActual = null;
	if (!ok) {
		estado.mensajeFlash = mensaje || "No se pudo agregar automaticamente. Revisa el tratamiento y vuelve a tocar la pieza.";
		odontogramaRender("presupuesto");
	}
}

function odontogramaTextoSuperficie(superficie) {
	var mapa = {
		mesial: "Mesial",
		distal: "Distal",
		vestibular: "Vestibular",
		lingual_palatina: "Lingual / Palatina",
		oclusal_incisal: "Oclusal / Incisal"
	};
	return mapa[superficie] || superficie || "";
}

function odontogramaParsearRespuesta(respuesta) {
	if (typeof respuesta === "string") {
		return $.parseJSON(respuesta);
	}
	return respuesta || {};
}

function odontogramaApi(accion, datos, callback) {
	obtener_datos_user();
	datos = datos || {};
	datos.useru = userid;
	datos.passu = passuser;
	datos.navegador = navegador;
	datos.accion = accion;
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmOdontograma.php",
		type: "post",
		error: function (jqXHR, textstatus) {
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
			if (typeof callback === "function") {
				callback(false, { "1": "error", mensaje: "No se pudo conectar con odontograma." });
			}
		},
		success: function (responseText) {
			try {
				var datosRespuesta = odontogramaParsearRespuesta(responseText);
				var estado = datosRespuesta["1"] || datosRespuesta[1] || datosRespuesta["0"] || datosRespuesta[0];
				if (estado == "requiere_motivo") {
					if (typeof callback === "function") {
						callback(false, datosRespuesta);
					}
					return;
				}
				var ok = respuestaJqueryAjax(estado) == true;
				if (typeof callback === "function") {
					callback(ok, datosRespuesta);
				}
			} catch (error) {
				GuardarArchivosLog("Error odontograma: " + error + " \r\n Consola: " + responseText);
				if (typeof callback === "function") {
					callback(false, { "1": "error", mensaje: responseText });
				}
			}
		}
	});
}

function odontogramaDatosBaseFicha() {
	return {
		paciente_id: cod_clienteConsulta || "",
		cedula: document.getElementById("inptCIConsulta") ? document.getElementById("inptCIConsulta").value : "",
		venta_id: cod_ventaFKConsulta || ""
	};
}

function odontogramaDatosBasePresupuesto() {
	return {
		paciente_id: idFkCliente || "",
		cedula: document.getElementById("inptDocumentoClientePresupuestoDoc") ? document.getElementById("inptDocumentoClientePresupuestoDoc").value : "",
		presupuesto_id: idabmPresupuesto || ""
	};
}

function odontogramaCallbackOpcion(opciones, nombres, argumentos) {
	opciones = opciones || {};
	for (var i = 0; i < nombres.length; i++) {
		if (typeof opciones[nombres[i]] === "function") {
			opciones[nombres[i]].apply(null, argumentos || []);
			return;
		}
	}
}

function cargarOdontogramaFichaClinica(opciones) {
	if (typeof opciones === "function") { opciones = { alCargar: opciones }; }
	opciones = opciones || {};
	var contenedor = document.getElementById("odontogramaFichaClinica");
	var solicitudActual = ++odontogramaCargaFichaSecuencia;
	if (!contenedor) {
		odontogramaCallbackOpcion(opciones, ["alError", "onError"], ["No se encontro el odontograma de la ficha clinica."]);
		return false;
	}
	if (!cod_clienteConsulta && !cod_ventaFKConsulta) {
		var mensajePaciente = "Seleccione un paciente para ver el odontograma.";
		contenedor.innerHTML = "<div class='odontograma-empty'>" + mensajePaciente + "</div>";
		odontogramaCallbackOpcion(opciones, ["alError", "onError"], [mensajePaciente]);
		return false;
	}
	contenedor.innerHTML = "<div class='odontograma-empty'>Cargando odontograma...</div>";
	var base = odontogramaDatosBaseFicha();
	odontogramaApi("obtenerOdontogramaPaciente", base, function (ok, datos) {
		if (solicitudActual !== odontogramaCargaFichaSecuencia) { return; }
		if (!ok) {
			var mensajeError = datos.mensaje || "No se pudo cargar el odontograma.";
			contenedor.innerHTML = "<div class='odontograma-error'>" + odontogramaEscape(mensajeError) + "</div>";
			odontogramaCallbackOpcion(opciones, ["alError", "onError"], [mensajeError, datos]);
			return;
		}
		var previo = odontogramaEstados.ficha || {};
		var preservarPendiente = opciones.preservarTratamientoPendiente === true;
		odontogramaEstados.ficha = odontogramaEstadoInicial({
			tipo: "ficha",
			contenedor: "odontogramaFichaClinica",
			base: base,
			datos: datos,
			pasoClinico: previo.pasoClinico || "situacion",
			modo: previo.modo || "hallazgo",
			filtroVisual: previo.filtroVisual || "todo",
			mensajeFlash: previo.mensajeFlash || "",
			leyendaVisible: previo.leyendaVisible || false,
			piezaSeleccionada: previo.piezaSeleccionada || "",
			ubicacionActual: preservarPendiente ? (previo.ubicacionActual || null) : null,
			tratamientoPendiente: preservarPendiente ? (previo.tratamientoPendiente || null) : null,
			tratamientoSeleccionado: preservarPendiente ? (previo.tratamientoSeleccionado || null) : null,
			seleccionMultipleActiva: preservarPendiente ? !!previo.seleccionMultipleActiva : false,
			piezasMultiples: preservarPendiente ? (previo.piezasMultiples || []) : []
		});
		odontogramaRender("ficha");
		odontogramaCallbackOpcion(opciones, ["alCargar", "onReady"], [odontogramaEstados.ficha, datos]);
	});
	return true;
}

function cargarOdontogramaPresupuestoDoctor() {
	var contenedor = document.getElementById("odontogramaPresupuestoDoctor");
	if (!contenedor) { return; }
	var base = odontogramaDatosBasePresupuesto();
	if (!base.paciente_id && !base.cedula) {
		contenedor.innerHTML = "<div class='odontograma-empty'>Seleccione un paciente para activar el odontograma del presupuesto.</div>";
		return;
	}
	contenedor.innerHTML = "<div class='odontograma-empty'>Cargando odontograma...</div>";
	odontogramaApi("obtenerOdontogramaPaciente", base, function (ok, datos) {
		if (!ok) {
			contenedor.innerHTML = "<div class='odontograma-error'>" + odontogramaEscape(datos.mensaje || "No se pudo cargar el odontograma.") + "</div>";
			return;
		}
		var previo = odontogramaEstados.presupuesto || {};
		odontogramaEstados.presupuesto = odontogramaEstadoInicial({
			tipo: "presupuesto",
			contenedor: "odontogramaPresupuestoDoctor",
			base: base,
			datos: datos,
			pasoClinico: "tratamientos",
			modo: "asignar",
			filtroVisual: "tratamientos",
			ayudaContextual: previo.ayudaContextual || "",
			mensajeFlash: previo.mensajeFlash || "",
			leyendaVisible: previo.leyendaVisible || false,
			piezaSeleccionada: previo.piezaSeleccionada || "",
			tratamientoSeleccionado: previo.tratamientoSeleccionado || null,
			ubicacionActual: previo.ubicacionActual || null,
			tratamientoPendiente: previo.tratamientoPendiente || null,
			agregandoAutomatico: previo.agregandoAutomatico || false,
			ubicacionAutomaticaPendiente: previo.ubicacionAutomaticaPendiente || null,
			seleccionMultipleActiva: previo.seleccionMultipleActiva || false,
			piezasMultiples: previo.piezasMultiples || []
		});
		odontogramaRender("presupuesto");
	});
}

function odontogramaRefrescar(contexto) {
	if (contexto == "presupuesto") {
		cargarOdontogramaPresupuestoDoctor();
		return;
	}
	cargarOdontogramaFichaClinica();
}

function odontogramaCompletarEstado(estado) {
	if (!estado.modo) { estado.modo = estado.tratamientoPendiente ? "asignar" : "explorar"; }
	if (!estado.pasoClinico) { estado.pasoClinico = estado.modo == "asignar" ? "tratamientos" : "situacion"; }
	if (!estado.filtroVisual) { estado.filtroVisual = "todo"; }
	if (estado.ayudaContextual == null) { estado.ayudaContextual = ""; }
	if (estado.mensajeFlash == null) { estado.mensajeFlash = ""; }
	if (estado.leyendaVisible == null) { estado.leyendaVisible = false; }
}

function odontogramaResumenProgreso(estado) {
	var links = estado && estado.datos && estado.datos.links ? estado.datos.links.length : 0;
	var pendientes = estado && estado.datos && estado.datos.tratamientos_sin_ubicacion ? estado.datos.tratamientos_sin_ubicacion.length : 0;
	var hallazgos = estado && estado.datos && estado.datos.marcas ? estado.datos.marcas.length : 0;
	var total = links + pendientes;
	return {
		ubicados: links,
		pendientes: pendientes,
		hallazgos: hallazgos,
		total: total,
		texto: total ? (links + "/" + total + " tratamientos ubicados") : "Sin tratamientos por ubicar"
	};
}

function odontogramaRender(contexto) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	odontogramaCompletarEstado(estado);
	var contenedor = document.getElementById(estado.contenedor);
	if (!contenedor) { return; }
	var datos = estado.datos || {};
	var odo = datos.odontograma || {};
	var estadoTexto = odo.estado ? odo.estado : "borrador";
	var convalidado = estadoTexto == "convalidado";
	var config = odontogramaConfig(contexto);
	var titulo = config.titulo;
	var progreso = odontogramaResumenProgreso(estado);
	var html = "";
	html += "<section class='odontograma-wrapper odontograma-" + contexto + "'>";
	html += "<div class='odontograma-header odontograma-layout'>";
	html += "<div><strong>" + titulo + "</strong><span class='odontograma-progreso'>" + odontogramaEscape(progreso.texto) + " &middot; Hallazgos " + odontogramaEscape(progreso.hallazgos) + (contexto == "ficha" ? " &middot; Estado: " + odontogramaEscape(estadoTexto) : "") + "</span></div>";
	html += "<div class='odontograma-header__actions'>";
	if (config.mostrarHistorial) {
		html += "<span class='odontograma-badge odontograma-badge--" + odontogramaEscape(estadoTexto) + "'>" + odontogramaEscape(estadoTexto) + " v" + odontogramaEscape(odo.version_actual || "1") + "</span>";
		html += "<button type='button' class='odontograma-historial-btn' title='Historial completo' onclick='odontogramaVerHistorialCompleto(\"" + contexto + "\")'>Historial</button>";
	}
	if (config.mostrarDeshacer) {
		html += "<button type='button' class='odontograma-deshacer' title='Deshacer ultima accion' onclick='odontogramaDeshacer(\"" + contexto + "\")'>Deshacer</button>";
	}
	if (config.mostrarGuia) {
		html += "<button type='button' title='Guia rapida' onclick='odontogramaMostrarGuia(\"" + contexto + "\")'>?</button>";
	}
	if (!config.leyendaExpandida && contexto == "presupuesto") {
		html += "<button type='button' class='odontograma-leyenda-toggle' onclick='odontogramaToggleLeyenda(\"" + contexto + "\")'>" + (estado.leyendaVisible ? "Ocultar leyenda" : "Ver leyenda") + "</button>";
	}
	if (config.mostrarConvalidar) {
		html += "<button type='button' class='odontograma-btn-primary' onclick='odontogramaConvalidar(\"" + contexto + "\")'>" + (convalidado ? "Convalidado" : "Convalidar") + "</button>";
	}
	html += "</div></div>";
	html += odontogramaRenderPasosClinicos(contexto);
	html += odontogramaRenderToolbar(contexto);
	html += odontogramaRenderAyudaContextual(contexto);
	html += odontogramaRenderRevisionClinica(contexto);
	html += "<div class='odontograma-main odontograma-main--paso-" + odontogramaEscape(estado.pasoClinico) + "'>";
	html += "<aside class='odontograma-panel-situacion'>" + odontogramaRenderPanelSituacionActual(contexto) + "</aside>";
	html += "<div class='odontograma-canvas'>" + odontogramaRenderDiagrama(contexto) + odontogramaRenderSituacionMarcadaResumen(contexto) + "</div>";
	html += "<aside class='odontograma-panel-pieza' id='odontogramaPanel_" + contexto + "'>" + odontogramaRenderPanel(contexto) + "</aside>";
	html += "</div>";
	if (contexto == "ficha") {
		html += odontogramaRenderTratamientosSinUbicacion(contexto);
	}
	html += "</section>";
	contenedor.innerHTML = html;
}

function odontogramaRenderPasosClinicos(contexto) {
	var estado = odontogramaEstados[contexto];
	var config = odontogramaConfig(contexto);
	if (!config.mostrarPasosClinicos) { return ""; }
	var pasos = config.pasos || odontogramaPasosClinicos;
	var html = "<div class='odontograma-guia-pasos'>";
	pasos.forEach(function (paso) {
		var activo = estado.pasoClinico == paso.id ? " odontograma-paso-activo" : "";
		html += "<button type='button' class='odontograma-paso" + activo + "' onclick='odontogramaCambiarPasoClinico(\"" + contexto + "\",\"" + paso.id + "\")'>"
			+ "<b class='odontograma-paso-numero'>" + odontogramaEscape(paso.numero || "") + "</b>"
			+ "<span class='odontograma-paso-copy'><strong>" + odontogramaEscape(paso.titulo) + "</strong><em>" + odontogramaEscape(paso.texto) + "</em></span>"
			+ "</button>";
	});
	html += "</div>";
	return html;
}

function odontogramaPasoClinicoActual(estado) {
	var config = odontogramaConfig(estado && estado.tipo ? estado.tipo : "ficha");
	var pasos = config.pasos || odontogramaPasosClinicos;
	for (var i = 0; i < pasos.length; i++) {
		if (pasos[i].id == estado.pasoClinico) {
			return pasos[i];
		}
	}
	return pasos[0] || odontogramaPasosClinicos[0];
}

function odontogramaRenderRevisionClinica(contexto) {
	var estado = odontogramaEstados[contexto];
	if (!odontogramaConfig(contexto).mostrarRevision) { return ""; }
	if (!estado || estado.pasoClinico != "revision") { return ""; }
	var progreso = odontogramaResumenProgreso(estado);
	var odo = estado.datos && estado.datos.odontograma ? estado.datos.odontograma : {};
	var estadoTexto = odo.estado || "borrador";
	var convalidacion = "";
	if (odo.fecha_convalidacion) {
		convalidacion = "<span>Convalidado el " + odontogramaEscape(odo.fecha_convalidacion) + "</span>";
	} else if (String(estadoTexto).toLowerCase() == "modificado") {
		convalidacion = "<span>Modificado despues de convalidacion.</span>";
	}
	return "<div class='odontograma-revision odontograma-auditoria'>"
		+ "<strong>Pendiente de completar</strong>"
		+ "<span>Hallazgos cargados: " + odontogramaEscape(progreso.hallazgos) + "</span>"
		+ "<span>Tratamientos ubicados: " + odontogramaEscape(progreso.ubicados) + "/" + odontogramaEscape(progreso.total) + "</span>"
		+ "<span>Faltan ubicar: " + odontogramaEscape(progreso.pendientes) + "</span>"
		+ convalidacion
		+ "</div>";
}

function odontogramaRenderToolbar(contexto) {
	var estado = odontogramaEstados[contexto];
	var html = "<div class='odontograma-toolbar'>";
	html += odontogramaRenderModos(contexto);
	html += odontogramaRenderFiltros(contexto);
	html += "<div class='odontograma-toolbar-actions'>" + odontogramaRenderAtajosUbicacion(contexto) + "</div>";
	html += "</div>";
	html += odontogramaRenderTratamientoActivo(contexto);
	return html;
}

function odontogramaRenderModos(contexto) {
	var estado = odontogramaEstados[contexto];
	var config = odontogramaConfig(contexto);
	var modosPermitidos = config.modos || odontogramaModos.map(function (modo) { return modo.id; });
	if (!modosPermitidos.length) { return ""; }
	var trActivo = estado.tratamientoPendiente || (contexto == "presupuesto" ? estado.tratamientoSeleccionado : null);
	var modoActual = trActivo ? "asignar" : estado.modo;
	var html = "<div class='odontograma-modos'>";
	odontogramaModos.forEach(function (modo) {
		if (modosPermitidos.indexOf(modo.id) < 0) { return; }
		var activo = modoActual == modo.id ? " odontograma-modo-activo" : "";
		html += "<button type='button' class='odontograma-modo" + activo + "' onclick='odontogramaCambiarModo(\"" + contexto + "\",\"" + modo.id + "\")'>" + odontogramaEscape(modo.nombre) + "</button>";
	});
	html += "</div>";
	return html;
}

function odontogramaRenderFiltros(contexto) {
	var estado = odontogramaEstados[contexto];
	var config = odontogramaConfig(contexto);
	var filtrosPermitidos = config.filtros || odontogramaFiltros.map(function (filtro) { return filtro.id; });
	var html = "<div class='odontograma-filtro-visual'>";
	odontogramaFiltros.forEach(function (filtro) {
		if (filtrosPermitidos.indexOf(filtro.id) < 0) { return; }
		var activo = estado.filtroVisual == filtro.id ? " is-active" : "";
		html += "<button type='button' class='" + activo + "' onclick='odontogramaCambiarFiltro(\"" + contexto + "\",\"" + filtro.id + "\")'>" + odontogramaEscape(filtro.nombre) + "</button>";
	});
	html += "</div>";
	return html;
}

function odontogramaRenderTratamientoActivo(contexto) {
	var estado = odontogramaEstados[contexto];
	var tr = estado.tratamientoPendiente || (contexto == "presupuesto" ? estado.tratamientoSeleccionado : null);
	if (!tr) { return ""; }
	var ubicacion = estado.ubicacionActual ? odontogramaTextoUbicacion(estado.ubicacionActual) : "Falta ubicar";
	return "<div class='odontograma-active-treatment odontograma-tratamiento-activo'>"
		+ "<span>Asignando tratamiento</span>"
		+ "<strong>" + odontogramaEscape(tr.nombre || "Tratamiento") + "</strong>"
		+ "<em>Requiere: " + odontogramaEscape(odontogramaTextoAlcance(tr.alcance)) + "</em>"
		+ "<b class='" + (estado.ubicacionActual ? "is-ready" : "") + "'>" + odontogramaEscape(ubicacion) + "</b>"
		+ "<button type='button' onclick='odontogramaCancelarAsignacion(\"" + contexto + "\")'>Cancelar asignacion</button>"
		+ "</div>";
}

function odontogramaAyudaActual(contexto) {
	var estado = odontogramaEstados[contexto];
	if (estado.mensajeFlash) { return estado.mensajeFlash; }
	var tr = estado.tratamientoPendiente || (contexto == "presupuesto" ? estado.tratamientoSeleccionado : null);
	if (contexto == "presupuesto") {
		if (tr) {
			if (estado.piezaSeleccionada) {
				return "Asignando: " + (tr.nombre || "tratamiento") + ". Confirma la pieza o ubicacion para agregarla al presupuesto.";
			}
			return "Selecciona una pieza, arcada o boca completa para ubicar " + (tr.nombre || "el tratamiento") + ".";
		}
		if (estado.piezaSeleccionada) {
			return "Pieza " + estado.piezaSeleccionada + " seleccionada. Puedes marcar situacion actual o pasar a tratamientos.";
		}
		if (estado.ubicacionActual) {
			return odontogramaTextoUbicacion(estado.ubicacionActual) + " seleccionado.";
		}
		return estado.pasoClinico == "tratamientos"
			? "Selecciona piezas y busca un tratamiento para agregarlo al resumen."
			: "Selecciona una o varias piezas para marcar la situacion actual cuando corresponda.";
	}
	if (tr) {
		if (estado.piezaSeleccionada) {
			if (odontogramaPiezaMarcadaAusente(estado, estado.piezaSeleccionada)) {
				return "Paso 2: pieza " + estado.piezaSeleccionada + " marcada como ausente en Situacion actual. Verifica si corresponde asignar " + (tr.nombre || "este tratamiento") + ".";
			}
			return "Asignando: " + (tr.nombre || "tratamiento") + ". Estas editando la pieza " + estado.piezaSeleccionada + "; elegi superficie, pieza, arcada o boca completa segun corresponda.";
		}
		return "Asignando: " + (tr.nombre || "tratamiento") + ". Toca la pieza, arcada o boca completa correspondiente.";
	}
	if (estado.pasoClinico == "tratamientos" && estado.piezaSeleccionada && odontogramaPiezaMarcadaAusente(estado, estado.piezaSeleccionada)) {
		return "Paso 2: la pieza " + estado.piezaSeleccionada + " esta marcada como ausente. Se sugieren tratamientos de rehabilitacion o revision antes de vincular.";
	}
	if (estado.piezaSeleccionada) {
		return "Estas editando la pieza " + estado.piezaSeleccionada + ". La ficha derecha muestra vistas, superficies, tratamientos, hallazgos e historial.";
	}
	if (estado.ubicacionActual) {
		return odontogramaTextoUbicacion(estado.ubicacionActual) + " seleccionado en el odontograma.";
	}
	if (estado.modo == "hallazgo") {
		return "Paso 1: marca la situacion actual del paciente. Toca una pieza y registra caries, ausencias, obturaciones o protesis existentes.";
	}
	if (estado.modo == "editar") {
		return "Paso 3: revisa tratamientos, hallazgos y trazabilidad. Podes editar, quitar vinculos o deshacer.";
	}
	if (estado.modo == "asignar") {
		return "Paso 2: selecciona un tratamiento y toca la pieza, superficie, arcada o boca completa correspondiente.";
	}
	return "Primero revisa como esta la boca. Despues vincula tratamientos y finalmente convalida.";
}

function odontogramaRenderAyudaContextual(contexto) {
	return "<div class='odontograma-ayuda-contextual'>" + odontogramaEscape(odontogramaAyudaActual(contexto)) + "</div>";
}

function odontogramaRenderAtajosUbicacion(contexto) {
	var config = odontogramaConfig(contexto);
	var estado = odontogramaEstados[contexto] || {};
	var html = "<div class='odontograma-quick-actions selector-ubicacion-grafica'>"
		+ "<button type='button' class='selector-boca-completa-card' onclick='odontogramaSeleccionGeneral(\"" + contexto + "\",\"boca_completa\",\"\")'>" + odontogramaIconoUbicacion("boca-completa", "") + "<span>Boca completa</span></button>"
		+ "<button type='button' class='selector-arcada-card' onclick='odontogramaSeleccionGeneral(\"" + contexto + "\",\"arcada\",\"superior\")'>" + odontogramaIconoUbicacion("arcada-superior", "") + "<span>Arcada sup.</span></button>"
		+ "<button type='button' class='selector-arcada-card' onclick='odontogramaSeleccionGeneral(\"" + contexto + "\",\"arcada\",\"inferior\")'>" + odontogramaIconoUbicacion("arcada-inferior", "") + "<span>Arcada inf.</span></button>"
		+ "<button type='button' class='selector-arcada-card' onclick='odontogramaSeleccionGeneral(\"" + contexto + "\",\"arcada\",\"ambas\")'>" + odontogramaIconoUbicacion("ambas-arcadas", "") + "<span>Ambas</span></button>";
	var tratamientoMultiple = estado.tratamientoPendiente || (contexto == "presupuesto" ? estado.tratamientoSeleccionado : null);
	var permiteVariasPiezas = contexto == "presupuesto"
		|| (tratamientoMultiple && (tratamientoMultiple.modo_individualizacion == "multipieza" || tratamientoMultiple.modo_individualizacion == "sector"));
	if (permiteVariasPiezas) {
		var cantidadMultiple = estado.piezasMultiples && estado.piezasMultiples.length ? estado.piezasMultiples.length : 0;
		html += "<button type='button' class='selector-multiple-card" + (estado.seleccionMultipleActiva ? " is-active" : "") + "' onclick='odontogramaToggleSeleccionMultiple(\"" + contexto + "\")'>" + odontogramaIconoUbicacion("piezas-multiples", "") + "<span>Mas de 1</span></button>";
		if (estado.seleccionMultipleActiva && cantidadMultiple > 0) {
			html += "<button type='button' class='selector-multiple-ok' onclick='odontogramaConfirmarSeleccionMultiple(\"" + contexto + "\")'>OK (" + cantidadMultiple + ")</button>";
		}
	}
	if (config.mostrarCuadrantes) {
		html += "<button type='button' title='Cuadrante superior derecho' onclick='odontogramaSeleccionGeneral(\"" + contexto + "\",\"cuadrante\",\"1\")'>C1</button>"
			+ "<button type='button' title='Cuadrante superior izquierdo' onclick='odontogramaSeleccionGeneral(\"" + contexto + "\",\"cuadrante\",\"2\")'>C2</button>"
			+ "<button type='button' title='Cuadrante inferior izquierdo' onclick='odontogramaSeleccionGeneral(\"" + contexto + "\",\"cuadrante\",\"3\")'>C3</button>"
			+ "<button type='button' title='Cuadrante inferior derecho' onclick='odontogramaSeleccionGeneral(\"" + contexto + "\",\"cuadrante\",\"4\")'>C4</button>";
	}
	html += "<button type='button' onclick='odontogramaLimpiarSeleccion(\"" + contexto + "\")'>Limpiar</button></div>";
	return html;
}

function odontogramaRenderDiagrama(contexto) {
	var estado = odontogramaEstados[contexto];
	var config = odontogramaConfig(contexto);
	var html = "<div class='odontograma-diagrama'>";
	html += "<div class='odontograma-tablero'>";
	html += "<span class='odontograma-eje odontograma-eje--vertical'></span>";
	html += "<span class='odontograma-eje odontograma-eje--horizontal'></span>";
	html += "<span class='odontograma-cuadrante-label odontograma-cuadrante-label--sd'>Superior derecho</span>";
	html += "<span class='odontograma-cuadrante-label odontograma-cuadrante-label--si'>Superior izquierdo</span>";
	html += "<span class='odontograma-cuadrante-label odontograma-cuadrante-label--id'>Inferior derecho</span>";
	html += "<span class='odontograma-cuadrante-label odontograma-cuadrante-label--ii'>Inferior izquierdo</span>";
	odontogramaFilas.forEach(function (fila) {
		html += odontogramaRenderFila(contexto, fila);
	});
	html += "</div>";
	if (config.leyendaExpandida || contexto != "presupuesto" || (estado && estado.leyendaVisible)) {
		html += odontogramaRenderLeyenda(contexto);
	}
	html += "</div>";
	return html;
}

function odontogramaTextoMarcaResumen(tipo) {
	var mapa = {
		caries: "Caries",
		obturacion: "Obturacion existente",
		pieza_ausente: "Ausente",
		extraccion_indicada: "Extraccion indicada",
		protesis_prevista: "Protesis prevista",
		protesis_existente: "Protesis existente"
	};
	return mapa[tipo] || odontogramaTextoMarca(tipo);
}

function odontogramaClaseColorMarcaResumen(marca) {
	var color = String(marca && marca.color ? marca.color : "").toLowerCase();
	var tipo = String(marca && marca.tipo_marca ? marca.tipo_marca : "");
	if (color == "azul" || tipo == "obturacion" || tipo == "protesis_existente") { return "azul"; }
	if (color == "gris" || tipo == "pieza_ausente") { return "gris"; }
	return "rojo";
}

function odontogramaRenderSituacionMarcadaResumen(contexto) {
	var estado = odontogramaEstados[contexto];
	if (contexto != "presupuesto" || !estado || ["situacion", "tratamientos"].indexOf(estado.pasoClinico) < 0) { return ""; }
	var marcas = (estado.datos && estado.datos.marcas ? estado.datos.marcas : []).slice();
	marcas.sort(function (a, b) {
		return Number(a.id || 0) - Number(b.id || 0);
	});
	var html = "<section class='odontograma-situacion-resumen' aria-label='Croquis y situacion de los dientes'>";
	html += "<header><strong>Croquis y situacion de los dientes</strong><span>Ayuda memoria antes de tratamientos</span></header>";
	if (!marcas.length) {
		html += "<p>Aun no se marcaron hallazgos.</p></section>";
		return html;
	}
	html += "<ul>";
	marcas.forEach(function (marca) {
		var pieza = marca.pieza || "-";
		var info = odontogramaInfoPiezaFDI(pieza);
		var superficie = marca.superficie ? odontogramaTextoSuperficiePieza(marca.superficie, info) : "";
		var claseColor = odontogramaClaseColorMarcaResumen(marca);
		html += "<li>"
			+ "<i class='odontograma-situacion-resumen-dot odontograma-situacion-resumen-dot--" + odontogramaEscape(claseColor) + "'></i>"
			+ "<b>Pieza " + odontogramaEscape(pieza) + "</b>"
			+ "<span>" + odontogramaEscape(odontogramaTextoMarcaResumen(marca.tipo_marca)) + "</span>"
			+ (superficie ? "<em>" + odontogramaEscape(superficie) + "</em>" : "")
			+ "</li>";
	});
	html += "</ul></section>";
	return html;
}

function odontogramaRenderFila(contexto, fila) {
	var html = "<div class='odontograma-fila odontograma-fila--" + fila.clase + "'>";
	html += odontogramaRenderGrupoPiezas(contexto, fila.izquierda, fila.denticion, "izquierda");
	html += "<span class='odontograma-centro'></span>";
	html += odontogramaRenderGrupoPiezas(contexto, fila.derecha, fila.denticion, "derecha");
	html += "</div>";
	return html;
}

function odontogramaRenderGrupoPiezas(contexto, piezas, denticion, lado) {
	var html = "<div class='odontograma-grupo odontograma-grupo--" + lado + "'>";
	piezas.forEach(function (pieza) {
		html += odontogramaRenderPieza(contexto, pieza, denticion);
	});
	html += "</div>";
	return html;
}

function odontogramaDenticionPieza(pieza) {
	return /^[5-8]/.test(String(pieza || "")) ? "temporal" : "permanente";
}

function odontogramaTipoPieza(pieza) {
	var digito = String(pieza || "").slice(-1);
	var temporal = odontogramaDenticionPieza(pieza) == "temporal";
	if (digito == "1" || digito == "2") { return "incisivo"; }
	if (digito == "3") { return "canino"; }
	if (temporal && (digito == "4" || digito == "5")) { return "molar"; }
	if (digito == "4" || digito == "5") { return "premolar"; }
	return "molar";
}

function odontogramaInfoPiezaFDI(pieza) {
	var texto = String(pieza || "");
	var cuadrante = texto.charAt(0);
	var tipo = odontogramaTipoPieza(pieza);
	var denticion = odontogramaDenticionPieza(pieza);
	var mapaCuadrante = {
		"1": { arcada: "superior", lado: "derecho", etiqueta: "Superior derecho" },
		"2": { arcada: "superior", lado: "izquierdo", etiqueta: "Superior izquierdo" },
		"3": { arcada: "inferior", lado: "izquierdo", etiqueta: "Inferior izquierdo" },
		"4": { arcada: "inferior", lado: "derecho", etiqueta: "Inferior derecho" },
		"5": { arcada: "superior", lado: "derecho", etiqueta: "Superior derecho" },
		"6": { arcada: "superior", lado: "izquierdo", etiqueta: "Superior izquierdo" },
		"7": { arcada: "inferior", lado: "izquierdo", etiqueta: "Inferior izquierdo" },
		"8": { arcada: "inferior", lado: "derecho", etiqueta: "Inferior derecho" }
	};
	var mapaTipo = {
		incisivo: "Incisivo",
		canino: "Canino",
		premolar: "Premolar",
		molar: "Molar"
	};
	var ubicacion = mapaCuadrante[cuadrante] || { arcada: "", lado: "", etiqueta: "" };
	var anterior = tipo == "incisivo" || tipo == "canino";
	return {
		pieza: texto,
		cuadrante: cuadrante,
		denticion: denticion,
		denticionTexto: denticion == "temporal" ? "Temporal" : "Permanente",
		arcada: ubicacion.arcada,
		arcadaTexto: ubicacion.arcada == "superior" ? "Superior" : (ubicacion.arcada == "inferior" ? "Inferior" : ""),
		lado: ubicacion.lado,
		ladoTexto: ubicacion.lado == "derecho" ? "Derecho" : (ubicacion.lado == "izquierdo" ? "Izquierdo" : ""),
		ubicacionTexto: ubicacion.etiqueta,
		tipo: tipo,
		tipoTexto: mapaTipo[tipo] || "",
		superficieCentral: anterior ? "Incisal" : "Oclusal"
	};
}

function odontogramaMetaPiezaTexto(info) {
	var partes = [];
	if (info.denticionTexto) { partes.push(info.denticionTexto); }
	if (info.ubicacionTexto) { partes.push(info.ubicacionTexto); }
	if (info.tipoTexto) { partes.push(info.tipoTexto); }
	return partes.join(" - ");
}

function odontogramaSvgPathsPieza(tipo) {
	var mapas = {
		incisivo: {
			outline: 23,
			vestibular: "M20 8 C27 5 37 5 44 8 L40 19 C35 17 29 17 24 19 Z",
			mesial: "M14 13 C10 23 10 41 14 51 L23 43 C20 36 20 28 23 21 Z",
			distal: "M50 13 C54 23 54 41 50 51 L41 43 C44 36 44 28 41 21 Z",
			lingual_palatina: "M20 56 C27 59 37 59 44 56 L40 45 C35 47 29 47 24 45 Z",
			oclusal_incisal: "M24 21 C29 18 35 18 40 21 L40 43 C35 46 29 46 24 43 Z",
			center: 11
		},
		canino: {
			outline: 24,
			vestibular: "M16 11 C24 4 40 4 48 11 L39 22 C35 18 29 18 25 22 Z",
			mesial: "M11 16 C5 25 6 40 13 50 L22 42 C18 35 18 29 22 23 Z",
			distal: "M53 16 C59 25 58 40 51 50 L42 42 C46 35 46 29 42 23 Z",
			lingual_palatina: "M16 54 C24 60 40 60 48 54 L40 45 C35 49 29 49 24 45 Z",
			oclusal_incisal: "M24 23 L32 17 L40 23 C45 31 45 38 40 43 C35 48 29 48 24 43 C19 38 19 31 24 23 Z",
			center: 12
		},
		premolar: {
			outline: 25,
			vestibular: "M15 10 C24 4 40 4 49 10 L42 19 C36 15 28 15 22 19 Z",
			mesial: "M10 15 C4 24 4 40 10 49 L19 42 C15 36 15 28 19 22 Z",
			distal: "M54 15 C60 24 60 40 54 49 L45 42 C49 36 49 28 45 22 Z",
			lingual_palatina: "M15 54 C24 60 40 60 49 54 L42 45 C36 49 28 49 22 45 Z",
			oclusal_incisal: "M22 22 C28 18 36 18 42 22 C46 28 46 36 42 42 C36 46 28 46 22 42 C18 36 18 28 22 22 Z",
			center: 13
		},
		molar: {
			outline: 27,
			vestibular: "M13 10 C22 3 42 3 51 10 L43 20 C36 16 28 16 21 20 Z",
			mesial: "M9 14 C2 24 2 41 10 51 L20 43 C16 36 16 28 20 21 Z",
			distal: "M55 14 C62 24 62 41 54 51 L44 43 C48 36 48 28 44 21 Z",
			lingual_palatina: "M13 54 C22 61 42 61 51 54 L43 44 C36 49 28 49 21 44 Z",
			oclusal_incisal: "M21 21 C28 16 36 16 43 21 C49 28 49 36 43 43 C36 48 28 48 21 43 C15 36 15 28 21 21 Z",
			center: 14
		}
	};
	return mapas[tipo] || mapas.premolar;
}

function odontogramaEsVerdadero(valor) {
	return valor === true || valor === 1 || String(valor || "") == "1";
}

function odontogramaArcadaPieza(pieza) {
	var primerDigito = String(pieza || "").charAt(0);
	return ["1", "2", "5", "6"].indexOf(primerDigito) >= 0 ? "superior" : "inferior";
}

function odontogramaNormalizarArcada(arcada) {
	arcada = String(arcada || "").toLowerCase().replace(/\s+/g, "_");
	if (arcada == "superior_e_inferior" || arcada == "superior_inferior" || arcada == "ambas_arcadas") {
		return "ambas";
	}
	return arcada;
}

function odontogramaTextoArcada(arcada) {
	arcada = odontogramaNormalizarArcada(arcada);
	if (arcada == "ambas") { return "Arcada superior e inferior"; }
	if (arcada == "superior") { return "Arcada superior"; }
	if (arcada == "inferior") { return "Arcada inferior"; }
	return "Arcada " + String(arcada || "").replace(/_/g, " ");
}

function odontogramaArcadaIncluyePieza(arcada, pieza) {
	arcada = odontogramaNormalizarArcada(arcada);
	if (arcada == "ambas") { return true; }
	return arcada && arcada == odontogramaArcadaPieza(pieza);
}

function odontogramaCuadrantePieza(pieza) {
	var primerDigito = String(pieza || "").charAt(0);
	var mapa = { "1": "1", "5": "1", "2": "2", "6": "2", "3": "3", "7": "3", "4": "4", "8": "4" };
	return mapa[primerDigito] || "";
}

function odontogramaNormalizarCuadrante(cuadrante) {
	cuadrante = String(cuadrante || "").toLowerCase();
	var mapa = {
		"1": "1",
		"cuadrante_1": "1",
		"superior_derecho": "1",
		"2": "2",
		"cuadrante_2": "2",
		"superior_izquierdo": "2",
		"3": "3",
		"cuadrante_3": "3",
		"inferior_izquierdo": "3",
		"4": "4",
		"cuadrante_4": "4",
		"inferior_derecho": "4"
	};
	return mapa[cuadrante] || cuadrante;
}

function odontogramaUbicacionIncluyePieza(ubicacion, pieza) {
	if (!ubicacion) { return false; }
	var piezas = odontogramaPiezasUbicacion(ubicacion);
	if (piezas.indexOf(String(pieza)) >= 0) { return true; }
	if (odontogramaEsVerdadero(ubicacion.boca_completa)) { return true; }
	if (ubicacion.arcada && odontogramaArcadaIncluyePieza(ubicacion.arcada, pieza)) { return true; }
	if (ubicacion.cuadrante && odontogramaNormalizarCuadrante(ubicacion.cuadrante) == odontogramaCuadrantePieza(pieza)) { return true; }
	if (ubicacion.pieza && String(ubicacion.pieza) == String(pieza)) { return true; }
	return false;
}

function odontogramaUbicacionEsGeneral(ubicacion) {
	return !!ubicacion && (odontogramaPiezasUbicacion(ubicacion).length > 0 || odontogramaEsVerdadero(ubicacion.boca_completa) || !!ubicacion.arcada || !!ubicacion.cuadrante);
}

function odontogramaMarcaVisiblePorFiltro(marca, filtro) {
	if (!filtro || filtro == "todo") { return true; }
	var tipo = String(marca.tipo_marca || "");
	var realizada = tipo == "obturacion" || tipo == "protesis_existente";
	if (filtro == "situacion" || filtro == "hallazgos") {
		return ["caries", "obturacion", "extraccion_indicada", "pieza_ausente", "protesis_prevista", "protesis_existente"].indexOf(tipo) >= 0;
	}
	if (filtro == "realizados") {
		return realizada;
	}
	if (filtro == "tratamientos") {
		return false;
	}
	return true;
}

function odontogramaLinkVisiblePorFiltro(link, filtro) {
	if (!filtro || filtro == "todo") { return true; }
	var completado = String(link.estado_link || "") == "completado" || Number(link.progreso_actual || 0) >= 100;
	if (filtro == "situacion" || filtro == "hallazgos") { return false; }
	if (filtro == "tratamientos") { return !completado; }
	if (filtro == "realizados") { return completado; }
	return true;
}

function odontogramaMapaPieza(contexto, pieza) {
	var estado = odontogramaEstados[contexto];
	var filtro = estado.filtroVisual || "todo";
	var filtroMarcas = contexto == "presupuesto" && filtro == "tratamientos" ? "situacion" : filtro;
	var marcas = (estado.datos.marcas || []).filter(function (m) {
		return String(m.pieza) == String(pieza) && odontogramaMarcaVisiblePorFiltro(m, filtroMarcas);
	});
	var todosLinks = estado.datos.links || [];
	var links = todosLinks.filter(function (l) {
		return String(l.pieza || "") == String(pieza) && odontogramaLinkVisiblePorFiltro(l, filtro);
	});
	var linksGenerales = todosLinks.filter(function (l) {
		return !l.pieza && odontogramaUbicacionIncluyePieza(l, pieza) && odontogramaLinkVisiblePorFiltro(l, filtro);
	});
	var superficies = {};
	var clases = [];
	var marcadores = {};
	marcas.forEach(function (m) {
		var tipoMarca = String(m.tipo_marca || "");
		if (m.superficie) {
			superficies[m.superficie] = m.color || "rojo";
		} else if (["caries", "obturacion", "pieza_ausente", "extraccion_indicada", "protesis_prevista", "protesis_existente"].indexOf(tipoMarca) < 0) {
			clases.push("has-marca-" + (m.color || "rojo"));
		}
		if (tipoMarca == "caries") {
			clases.push("has-caries");
			marcadores.caries = true;
		}
		if (tipoMarca == "obturacion") {
			clases.push("has-obturacion");
			marcadores.obturacion = true;
		}
		if (tipoMarca == "pieza_ausente") {
			clases.push("is-ausente");
		}
		if (tipoMarca == "extraccion_indicada") {
			clases.push("is-extraccion");
		}
		if (tipoMarca == "protesis_prevista") {
			clases.push("is-protesis-prevista");
			marcadores.protesisPrevista = true;
		}
		if (tipoMarca == "protesis_existente") {
			clases.push("is-protesis-existente");
			marcadores.protesisExistente = true;
		}
	});
	links.forEach(function (l) {
		var color = (String(l.estado_link || "") == "completado" || Number(l.progreso_actual || 0) >= 100) ? "azul" : "rojo";
		var lista = [];
		try { lista = l.superficies_json ? JSON.parse(l.superficies_json) : []; } catch (e) { lista = []; }
		if (lista.length) {
			lista.forEach(function (sup) { superficies[sup] = color; });
		} else {
			clases.push("has-link-" + color);
		}
	});
	linksGenerales.forEach(function (l) {
		var color = (String(l.estado_link || "") == "completado" || Number(l.progreso_actual || 0) >= 100) ? "azul" : "rojo";
		clases.push("is-region-link-" + color);
	});
	if (estado.ubicacionActual && odontogramaUbicacionIncluyePieza(estado.ubicacionActual, pieza)) {
		clases.push(odontogramaUbicacionEsGeneral(estado.ubicacionActual) ? "is-region-preview" : "is-location-preview");
		if (odontogramaPiezasUbicacion(estado.ubicacionActual).indexOf(String(pieza)) >= 0) {
			clases.push("is-multiple-preview");
		}
	}
	return { superficies: superficies, clases: clases, marcadores: marcadores, total: marcas.length + links.length + linksGenerales.length };
}

function odontogramaMapaPiezaPanel(contexto, pieza) {
	var estado = odontogramaEstados[contexto];
	var marcas = (estado.datos.marcas || []).filter(function (m) {
		return String(m.pieza) == String(pieza);
	});
	var todosLinks = estado.datos.links || [];
	var links = todosLinks.filter(function (l) {
		return String(l.pieza || "") == String(pieza);
	});
	var linksGenerales = todosLinks.filter(function (l) {
		return !l.pieza && odontogramaUbicacionIncluyePieza(l, pieza);
	});
	var superficies = {};
	var clases = [];
	var marcadores = {};
	marcas.forEach(function (m) {
		var tipoMarca = String(m.tipo_marca || "");
		if (m.superficie) {
			superficies[m.superficie] = m.color || "rojo";
		} else if (["caries", "obturacion", "pieza_ausente", "extraccion_indicada", "protesis_prevista", "protesis_existente"].indexOf(tipoMarca) < 0) {
			clases.push("has-marca-" + (m.color || "rojo"));
		}
		if (tipoMarca == "caries") {
			clases.push("has-caries");
			marcadores.caries = true;
		}
		if (tipoMarca == "obturacion") {
			clases.push("has-obturacion");
			marcadores.obturacion = true;
		}
		if (tipoMarca == "pieza_ausente") {
			clases.push("is-ausente");
			marcadores.ausente = true;
		}
		if (tipoMarca == "extraccion_indicada") {
			clases.push("is-extraccion");
			marcadores.extraccion = true;
		}
		if (tipoMarca == "protesis_prevista") {
			clases.push("is-protesis-prevista");
			marcadores.protesisPrevista = true;
		}
		if (tipoMarca == "protesis_existente") {
			clases.push("is-protesis-existente");
			marcadores.protesisExistente = true;
		}
	});
	links.forEach(function (l) {
		var color = odontogramaLinkCompletado(l) ? "azul" : "rojo";
		var lista = odontogramaSuperficiesLink(l);
		if (lista.length) {
			lista.forEach(function (sup) { superficies[sup] = color; });
		} else {
			clases.push("has-link-" + color);
		}
	});
	linksGenerales.forEach(function (l) {
		clases.push(odontogramaLinkCompletado(l) ? "is-region-link-azul" : "is-region-link-rojo");
	});
	if (estado.ubicacionActual && odontogramaUbicacionIncluyePieza(estado.ubicacionActual, pieza)) {
		clases.push(odontogramaUbicacionEsGeneral(estado.ubicacionActual) ? "is-region-preview" : "is-location-preview");
		if (odontogramaPiezasUbicacion(estado.ubicacionActual).indexOf(String(pieza)) >= 0) {
			clases.push("is-multiple-preview");
		}
	}
	return {
		superficies: superficies,
		clases: clases,
		marcadores: marcadores,
		marcas: marcas,
		links: links,
		linksGenerales: linksGenerales,
		total: marcas.length + links.length + linksGenerales.length
	};
}

function odontogramaRenderPieza(contexto, pieza, denticion) {
	var mapa = odontogramaMapaPieza(contexto, pieza);
	var tipo = odontogramaTipoPieza(pieza);
	var paths = odontogramaSvgPathsPieza(tipo);
	var seleccionada = odontogramaEstados[contexto].piezaSeleccionada == pieza ? " is-selected" : "";
	var clase = "odontograma-pieza odontograma-pieza-anatomica odontograma-pieza--" + denticion + " odontograma-pieza--" + tipo + " " + mapa.clases.join(" ") + seleccionada;
	var badge = mapa.total ? "<span class='odontograma-pieza__count'>" + mapa.total + "</span>" : "";
	var html = "<button type='button' class='" + clase + "' data-pieza='" + pieza + "' title='Pieza " + pieza + "' onclick='odontogramaSeleccionarPieza(event,\"" + contexto + "\",\"" + pieza + "\")'>";
	html += "<span class='odontograma-pieza__numero'>" + pieza + "</span>";
	html += badge;
	html += "<span class='odontograma-pieza__marcadores'>" + odontogramaRenderMarcadoresPieza(mapa) + "</span>";
	html += odontogramaRenderProtesisPieza(mapa);
	html += "<svg class='odontograma-svg' viewBox='0 0 64 64' aria-hidden='true'>";
	html += "<circle class='odontograma-tooth-outline' cx='32' cy='32' r='" + paths.outline + "'></circle>";
	html += odontogramaSvgSurface(contexto, pieza, "vestibular", paths.vestibular, mapa.superficies);
	html += odontogramaSvgSurface(contexto, pieza, "mesial", paths.mesial, mapa.superficies);
	html += odontogramaSvgSurface(contexto, pieza, "distal", paths.distal, mapa.superficies);
	html += odontogramaSvgSurface(contexto, pieza, "lingual_palatina", paths.lingual_palatina, mapa.superficies);
	html += odontogramaSvgSurface(contexto, pieza, "oclusal_incisal", paths.oclusal_incisal, mapa.superficies);
	html += "<circle class='odontograma-tooth-centerline' cx='32' cy='32' r='" + paths.center + "'></circle>";
	html += "</svg></button>";
	return html;
}

function odontogramaRenderMarcadoresPieza(mapa) {
	var html = "";
	if (mapa.marcadores.caries) {
		html += "<i class='odontograma-marker odontograma-marker--caries'></i>";
	}
	if (mapa.marcadores.obturacion) {
		html += "<i class='odontograma-marker odontograma-marker--obturacion'></i>";
	}
	return html;
}

function odontogramaRenderProtesisPieza(mapa) {
	if (mapa.marcadores.protesisExistente) {
		return "<span class='odontograma-protesis odontograma-protesis--existente'></span>";
	}
	if (mapa.marcadores.protesisPrevista) {
		return "<span class='odontograma-protesis odontograma-protesis--prevista'></span>";
	}
	return "";
}

function odontogramaSvgSurface(contexto, pieza, superficie, points, mapa) {
	var color = mapa[superficie] || "";
	var clase = "odontograma-superficie superficie-" + superficie + (color ? " odontograma-superficie--" + color : "");
	return "<path class='" + clase + "' d='" + points + "' onclick='odontogramaSeleccionarSuperficie(event,\"" + contexto + "\",\"" + pieza + "\",\"" + superficie + "\")'></path>";
}

function odontogramaTipoUbicacionVisual(ubicacion, falta) {
	if (falta) { return "pendiente"; }
	if (!ubicacion) { return "pendiente"; }
	if (odontogramaPiezasUbicacion(ubicacion).length) { return "piezas-multiples"; }
	if (odontogramaEsVerdadero(ubicacion.boca_completa)) { return "boca-completa"; }
	if (ubicacion.arcada) {
		var arcada = odontogramaNormalizarArcada(ubicacion.arcada);
		if (arcada == "ambas") { return "ambas-arcadas"; }
		if (arcada == "superior") { return "arcada-superior"; }
		if (arcada == "inferior") { return "arcada-inferior"; }
		return "ambas-arcadas";
	}
	if (ubicacion.pieza) { return "pieza"; }
	if (ubicacion.cuadrante) { return "ambas-arcadas"; }
	return "pendiente";
}

function odontogramaIconoUbicacion(tipo, pieza) {
	var clase = "odontograma-location-icon odontograma-icon-" + tipo;
	if (tipo == "pieza") {
		return "<span class='" + clase + " mini-diente-perfil mini-diente-perfil-" + odontogramaTipoPieza(pieza) + "' aria-hidden='true'>"
			+ "<svg viewBox='0 0 44 44'><path class='icon-tooth-root' d='M17 23 C18 28 19 35 22 40 C25 35 26 28 27 23 C24 25 20 25 17 23 Z'></path><path class='icon-tooth-crown' d='M12 9 C16 3 28 3 32 9 C34 16 30 23 24 26 C22 24 20 24 18 26 C12 23 10 16 12 9 Z'></path></svg>"
			+ (pieza ? "<b>" + odontogramaEscape(pieza) + "</b>" : "")
			+ "</span>";
	}
	if (tipo == "arcada-superior") {
		return "<span class='" + clase + "' aria-hidden='true'><svg viewBox='0 0 44 44'><path d='M8 28 C13 14 31 14 36 28'></path><circle cx='14' cy='27' r='2.5'></circle><circle cx='22' cy='23' r='2.5'></circle><circle cx='30' cy='27' r='2.5'></circle></svg></span>";
	}
	if (tipo == "arcada-inferior") {
		return "<span class='" + clase + "' aria-hidden='true'><svg viewBox='0 0 44 44'><path d='M8 16 C13 30 31 30 36 16'></path><circle cx='14' cy='17' r='2.5'></circle><circle cx='22' cy='21' r='2.5'></circle><circle cx='30' cy='17' r='2.5'></circle></svg></span>";
	}
	if (tipo == "ambas-arcadas") {
		return "<span class='" + clase + "' aria-hidden='true'><svg viewBox='0 0 44 44'><path d='M9 20 C14 9 30 9 35 20'></path><path d='M9 24 C14 35 30 35 35 24'></path><circle cx='16' cy='20' r='2'></circle><circle cx='22' cy='17' r='2'></circle><circle cx='28' cy='20' r='2'></circle><circle cx='16' cy='24' r='2'></circle><circle cx='22' cy='27' r='2'></circle><circle cx='28' cy='24' r='2'></circle></svg></span>";
	}
	if (tipo == "boca-completa") {
		return "<span class='" + clase + "' aria-hidden='true'><svg viewBox='0 0 44 44'><path d='M7 22 C10 8 34 8 37 22 C34 36 10 36 7 22 Z'></path><path d='M12 20 C17 15 27 15 32 20'></path><path d='M12 24 C17 29 27 29 32 24'></path><path d='M22 15 L22 29'></path></svg></span>";
	}
	if (tipo == "piezas-multiples") {
		return "<span class='" + clase + "' aria-hidden='true'><svg viewBox='0 0 44 44'><circle cx='14' cy='16' r='5'></circle><circle cx='28' cy='16' r='5'></circle><circle cx='21' cy='29' r='5'></circle><path d='M14 21 L21 24'></path><path d='M28 21 L21 24'></path></svg></span>";
	}
	return "<span class='" + clase + "' aria-hidden='true'><svg viewBox='0 0 44 44'><circle cx='22' cy='22' r='13'></circle><path d='M22 14 L22 30'></path><path d='M14 22 L30 22'></path></svg></span>";
}

function odontogramaDatosUbicacionVisual(ubicacion, falta, alcance) {
	var tipo = odontogramaTipoUbicacionVisual(ubicacion, falta);
	var texto = falta ? "Ubicacion pendiente" : odontogramaTextoUbicacion(ubicacion);
	var detalle = "";
	if (tipo == "pieza" && ubicacion) {
		var superficies = odontogramaSuperficiesUbicacion(ubicacion).map(function (sup) {
			return odontogramaTextoSuperficiePieza(sup, odontogramaInfoPiezaFDI(ubicacion.pieza));
		});
		if (superficies.length) {
			texto = "Pieza " + ubicacion.pieza + " - " + superficies.join(", ");
		}
	}
	if (tipo == "piezas-multiples") {
		var piezas = odontogramaPiezasUbicacion(ubicacion);
		texto = odontogramaTextoPiezasMultiples(piezas);
		detalle = "Seleccion multiple";
	}
	if (tipo == "boca-completa") { detalle = "Aplica a todos los dientes"; }
	if (tipo == "arcada-superior" || tipo == "arcada-inferior" || tipo == "ambas-arcadas") { detalle = "Ubicacion general"; }
	if (tipo == "pendiente" && alcance) { detalle = "Requiere: " + odontogramaTextoAlcance(alcance); }
	return { tipo: tipo, texto: texto, detalle: detalle };
}

function odontogramaRenderUbicacionVisual(contexto, ubicacion, opciones) {
	opciones = opciones || {};
	var falta = !!opciones.falta;
	var datos = odontogramaDatosUbicacionVisual(ubicacion, falta, opciones.alcance || "");
	var pieza = ubicacion && ubicacion.pieza ? ubicacion.pieza : "";
	var piezas = odontogramaPiezasUbicacion(ubicacion);
	var arcada = ubicacion && ubicacion.arcada ? ubicacion.arcada : "";
	var cuadrante = ubicacion && ubicacion.cuadrante ? ubicacion.cuadrante : "";
	var boca = ubicacion && odontogramaEsVerdadero(ubicacion.boca_completa) ? "1" : "";
	var superficies = odontogramaSuperficiesUbicacion(ubicacion);
	var superficie = superficies.length ? superficies[0] : "";
	var piezasValor = piezas.length ? piezas.join(",") : "";
	var clase = "tratamiento-ubicacion-visual tratamiento-ubicacion-" + datos.tipo + (falta ? " tratamiento-ubicacion-pendiente" : " tratamiento-ubicacion-completa");
	var contenido = odontogramaIconoUbicacion(datos.tipo, pieza)
		+ "<span class='tratamiento-ubicacion-texto'><b>" + odontogramaEscape(datos.texto || "Ubicacion pendiente") + "</b>"
		+ (datos.detalle ? "<small>" + odontogramaEscape(datos.detalle) + "</small>" : "")
		+ "</span>";
	if (falta) {
		return "<span class='" + clase + "'>" + contenido + "</span>";
	}
	return "<button type='button' class='" + clase + "' title='Ver ubicacion en odontograma' onclick='event.stopPropagation(); odontogramaEnfocarUbicacion(\"" + contexto + "\",\"" + odontogramaEscape(pieza) + "\",\"" + odontogramaEscape(arcada) + "\",\"" + odontogramaEscape(cuadrante) + "\",\"" + odontogramaEscape(boca) + "\",\"" + odontogramaEscape(superficie) + "\",\"" + odontogramaEscape(piezasValor) + "\")'>" + contenido + "</button>";
}

function odontogramaRenderLeyenda(contexto) {
	var config = odontogramaConfig(contexto || "ficha");
	var estado = odontogramaEstados[contexto] || {};
	var expandida = config.leyendaExpandida || !!estado.leyendaVisible;
	var claseEstado = expandida ? " is-open" : " is-collapsed";
	var textoToggle = expandida ? "Contraer leyenda" : "Expandir leyenda";
	var seccionTratamientos = contexto == "presupuesto"
		? "<section><b>Tratamientos</b>"
			+ "<span><i class='odonto-dot odonto-red'></i>Tratamiento pendiente</span>"
			+ "<span><i class='odonto-dot odonto-blue'></i>Tratamiento realizado</span></section>"
		: "<section><b>Tratamientos</b>"
			+ "<span><i class='odonto-x odonto-red'></i>Extraccion indicada</span>"
			+ "<span><i class='odonto-bridge odonto-red'></i>Protesis prevista</span>"
			+ "<span><i class='odonto-dot odonto-red'></i>Tratamiento pendiente</span>"
			+ "<span><i class='odonto-dot odonto-blue'></i>Tratamiento realizado</span></section>";
	var contenido = expandida
		? "<div class='odontograma-leyenda__contenido'>"
			+ "<section><b>Colores</b>"
			+ "<span><i class='odonto-dot odonto-red'></i>Rojo: observado / indicado / pendiente</span>"
			+ "<span><i class='odonto-dot odonto-blue'></i>Azul: realizado / existente</span>"
			+ "<span><i class='odonto-dot odonto-gray'></i>Gris: ausente / no aplicable</span></section>"
			+ "<section><b>Situacion actual</b>"
			+ "<span><i class='odonto-dot odonto-red'></i>Caries observada</span>"
			+ "<span><i class='odonto-x odonto-blue'></i>Pieza ausente</span>"
			+ "<span><i class='odonto-dot odonto-blue'></i>Obturacion existente</span>"
			+ "<span><i class='odonto-bridge odonto-blue'></i>Protesis existente</span></section>"
			+ seccionTratamientos
			+ "<section><b>Ubicacion</b>"
			+ "<span>" + odontogramaIconoUbicacion("pieza", "") + "Pieza especifica</span>"
			+ "<span>" + odontogramaIconoUbicacion("arcada-superior", "") + "Arcada superior</span>"
			+ "<span>" + odontogramaIconoUbicacion("arcada-inferior", "") + "Arcada inferior</span>"
			+ "<span>" + odontogramaIconoUbicacion("ambas-arcadas", "") + "Ambas arcadas</span>"
			+ "<span>" + odontogramaIconoUbicacion("boca-completa", "") + "Boca completa</span>"
			+ "<span>" + odontogramaIconoUbicacion("pendiente", "") + "Ubicacion pendiente</span></section>"
			+ "</div>"
		: "";
	return "<aside class='odontograma-leyenda odontograma-leyenda-fija" + claseEstado + "' aria-expanded='" + (expandida ? "true" : "false") + "'>"
		+ "<button type='button' class='odontograma-leyenda__toggle' title='" + textoToggle + "' aria-label='" + textoToggle + "' onclick='odontogramaToggleLeyenda(\"" + contexto + "\")'>"
		+ "<strong class='odontograma-leyenda__titulo'>Leyenda</strong>"
		+ "<span class='odontograma-leyenda__chips' aria-hidden='true'><i class='odonto-dot odonto-red'></i><i class='odonto-dot odonto-blue'></i><i class='odonto-dot odonto-gray'></i></span>"
		+ "<span class='odontograma-leyenda__estado'>" + (expandida ? "-" : "+") + "</span>"
		+ "</button>"
		+ contenido
		+ "</aside>";
}

function odontogramaToggleLeyenda(contexto) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	estado.leyendaVisible = !estado.leyendaVisible;
	odontogramaRender(contexto);
}

function odontogramaSuperficiesLink(link) {
	var lista = [];
	try { lista = link && link.superficies_json ? JSON.parse(link.superficies_json) : []; } catch (e) { lista = []; }
	return Array.isArray(lista) ? lista : [];
}

function odontogramaSuperficiesUbicacion(ubicacion) {
	var lista = [];
	if (!ubicacion) { return lista; }
	if (ubicacion.superficie) { lista.push(ubicacion.superficie); }
	try {
		var dec = ubicacion.superficies_json ? JSON.parse(ubicacion.superficies_json) : [];
		if (Array.isArray(dec)) {
			dec.forEach(function (sup) {
				if (lista.indexOf(sup) < 0) { lista.push(sup); }
			});
		}
	} catch (e) {
		lista = lista;
	}
	return lista;
}

function odontogramaLinkCompletado(link) {
	var estadoLink = String(link && link.estado_link ? link.estado_link : "").toLowerCase();
	var estadoTratamiento = String(link && link.estado_tratamiento_actual ? link.estado_tratamiento_actual : "").toLowerCase();
	return estadoLink == "completado" || estadoTratamiento == "realizado" || estadoTratamiento == "completado" || Number(link && link.progreso_actual ? link.progreso_actual : 0) >= 100;
}

function odontogramaTextoEstadoLink(link) {
	if (odontogramaLinkCompletado(link)) { return "Completado"; }
	if (link && link.estado_tratamiento_actual) { return link.estado_tratamiento_actual; }
	return link && link.estado_link ? link.estado_link : "Pendiente";
}

function odontogramaTextoMarca(tipo) {
	var mapa = {
		caries: "Caries observada",
		obturacion: "Obturacion existente",
		pieza_ausente: "Pieza ausente",
		extraccion_indicada: "Extraccion indicada",
		protesis_prevista: "Protesis prevista",
		protesis_existente: "Protesis existente"
	};
	return mapa[tipo] || String(tipo || "Marca clinica").replace(/_/g, " ");
}

function odontogramaTextoSuperficiePieza(superficie, info) {
	if (superficie == "oclusal_incisal") {
		return info && info.superficieCentral ? info.superficieCentral : "Oclusal / Incisal";
	}
	return odontogramaTextoSuperficie(superficie);
}

function odontogramaSuperficieActiva(contexto, pieza, superficie) {
	var estado = odontogramaEstados[contexto];
	var ubicacion = estado ? estado.ubicacionActual : null;
	if (!ubicacion || String(ubicacion.pieza || "") != String(pieza)) { return false; }
	if (ubicacion.superficie && ubicacion.superficie == superficie) { return true; }
	var lista = [];
	try { lista = ubicacion.superficies_json ? JSON.parse(ubicacion.superficies_json) : []; } catch (e) { lista = []; }
	return lista.indexOf(superficie) >= 0;
}

function odontogramaTextoContextoGeneral(link, pieza) {
	if (odontogramaEsVerdadero(link.boca_completa)) {
		return "Boca completa - Aplica tambien a esta pieza";
	}
	if (link.arcada) {
		return odontogramaTextoArcada(link.arcada) + " - Aplica a esta pieza";
	}
	if (link.cuadrante) {
		return "Cuadrante " + odontogramaNormalizarCuadrante(link.cuadrante) + " - Incluye esta pieza";
	}
	return (link.ubicacion_texto || ("Incluye pieza " + pieza)) + " - Aplica a esta pieza";
}

function odontogramaRenderContextoPasoTratamientos(contexto, pieza, mapa) {
	var estado = odontogramaEstados[contexto];
	if (!estado || estado.pasoClinico != "tratamientos") { return ""; }
	var trActivo = estado.tratamientoPendiente || (contexto == "presupuesto" ? estado.tratamientoSeleccionado : null);
	var marcas = mapa.marcas || [];
	var ausente = !!mapa.marcadores.ausente;
	var html = "<section class='odontograma-panel-section odontograma-contexto-tratamientos" + (ausente ? " odontograma-contexto-tratamientos--alerta" : "") + "'>";
	html += "<strong>Contexto para tratamientos</strong>";
	if (ausente) {
		html += "<div class='odontograma-contexto-alerta'><b>Pieza marcada como ausente en el paso 1.</b><span>Revisar antes de asignar restauraciones, caries u obturaciones sobre esta pieza.</span></div>";
		html += "<div class='odontograma-sugerencias-clinicas'><span>Compatibles: protesis, implante, puente o revision.</span><span>Si continua igual, el sistema pedira confirmacion.</span></div>";
	} else if (marcas.length) {
		html += "<div class='odontograma-contexto-chips'>";
		marcas.slice(0, 4).forEach(function (marca) {
			html += "<span>" + odontogramaEscape(odontogramaTextoMarca(marca.tipo_marca)) + "</span>";
		});
		html += "</div>";
	} else {
		html += "<p>Sin hallazgos registrados en el paso 1 para esta pieza.</p>";
	}
	if (trActivo) {
		html += "<p>Tratamiento activo: <b>" + odontogramaEscape(trActivo.nombre || "Tratamiento") + "</b></p>";
	}
	html += "</section>";
	return html;
}

function odontogramaRenderPasoGuiaAsignacion(numero, titulo, detalle, estadoClase, ayuda) {
	return "<article class='odontograma-asignacion-paso " + estadoClase + "'>"
		+ "<b>" + odontogramaEscape(numero) + "</b>"
		+ "<div><strong>" + odontogramaEscape(titulo) + "</strong>"
		+ "<span>" + odontogramaEscape(detalle) + "</span>"
		+ (ayuda ? "<small>" + odontogramaEscape(ayuda) + "</small>" : "")
		+ "</div></article>";
}

function odontogramaRenderGuiaAsignacionPresupuesto(contexto) {
	var estado = odontogramaEstados[contexto];
	var trActivo = estado.tratamientoPendiente || estado.tratamientoSeleccionado;
	var tieneTratamiento = !!(trActivo && (trActivo.nombre || trActivo.producto_id));
	var tieneUbicacion = !!estado.ubicacionActual;
	var cantidadMultiple = estado.piezasMultiples && estado.piezasMultiples.length ? estado.piezasMultiples.length : 0;
	var modoMultiple = !!estado.seleccionMultipleActiva;
	var tratamientoTexto = tieneTratamiento ? (trActivo.nombre || "Tratamiento seleccionado") : "Falta elegir tratamiento";
	var ubicacionTexto = tieneUbicacion
		? odontogramaTextoUbicacion(estado.ubicacionActual)
		: (modoMultiple && cantidadMultiple ? odontogramaTextoPiezasMultiples(estado.piezasMultiples) : (estado.piezaSeleccionada ? "Pieza " + estado.piezaSeleccionada + " enfocada" : "Falta elegir pieza dentaria"));
	var listo = tieneTratamiento && tieneUbicacion;
	var estadoTexto = estado.agregandoAutomatico
		? "Agregando al detalle del plan"
		: (modoMultiple ? (cantidadMultiple >= 2 ? "Confirma con OK" : "Seleccion multiple activa") : (listo ? "Se agregara automaticamente" : (tieneTratamiento ? "Ahora toca una pieza" : "Primero selecciona el tratamiento")));
	var ayudaTexto = estado.agregandoAutomatico
		? "Espera unos segundos. Luego podras tocar otra pieza con el mismo tratamiento."
		: (modoMultiple ? "Toca dos o mas piezas y pulsa OK para generar una sola linea de tratamiento." : (listo ? "El detalle se genera al tocar la pieza dentaria." : "Segui el orden: tratamiento y pieza dentaria."));
	var html = "<div class='odontograma-asignacion-guia'>";
	html += "<header class='odontograma-asignacion-guia-head'>"
		+ "<div><strong>Guia rapida de asignacion</strong><span>Orden recomendado para avanzar sin dudas</span></div>"
		+ "<em class='" + (listo ? "is-ready" : "is-pending") + "'>" + odontogramaEscape(estadoTexto) + "</em>"
		+ "</header>";
	html += "<div class='odontograma-asignacion-pasos'>";
	html += odontogramaRenderPasoGuiaAsignacion("1", "Tratamiento", tratamientoTexto, tieneTratamiento ? "is-complete" : "is-active", tieneTratamiento ? "Seleccionado" : "Busca y selecciona uno");
	html += odontogramaRenderPasoGuiaAsignacion("2", modoMultiple ? "Piezas dentarias" : "Pieza dentaria", ubicacionTexto, tieneUbicacion ? "is-complete" : (tieneTratamiento ? "is-active" : "is-pending"), tieneUbicacion ? "Seleccionada" : (modoMultiple ? "Toca varias piezas" : "Toca una pieza del odontograma"));
	html += odontogramaRenderPasoGuiaAsignacion("3", modoMultiple ? "Confirmar" : "Automatico", listo ? "Agregando al plan" : (modoMultiple ? "Pulsa OK al terminar" : "Aun falta completar"), listo ? "is-active is-ready" : "is-pending", listo ? "Sin boton adicional" : (modoMultiple ? "Crea una sola linea" : "Se activa al tocar la pieza"));
	html += "</div>";
	html += "<p class='odontograma-asignacion-ayuda'>" + odontogramaEscape(ayudaTexto) + "</p>";
	html += "</div>";
	return html;
}

function odontogramaRenderPanelSituacionActual(contexto) {
	var estado = odontogramaEstados[contexto];
	var paso = odontogramaPasoClinicoActual(estado);
	var pieza = estado.piezaSeleccionada;
	if (contexto == "presupuesto" && estado.pasoClinico == "tratamientos") {
		return odontogramaRenderGuiaAsignacionPresupuesto(contexto);
	}
	var html = "<div class='odontograma-situacion-card'>";
	html += "<header class='odontograma-situacion-head'>"
		+ "<b>" + odontogramaEscape(paso.numero || "1") + "</b>"
		+ "<div><strong>" + odontogramaEscape(paso.titulo || "Situacion actual") + "</strong><span>" + odontogramaEscape(paso.texto || "") + "</span></div>"
		+ "</header>";
	if (!pieza) {
		if (contexto == "presupuesto") {
			html += "<div class='odontograma-situacion-empty odontograma-situacion-empty--compacta'>"
				+ "<strong>Selecciona una o varias piezas para continuar.</strong>"
				+ "<span>Usa boca completa o arcadas cuando el tratamiento no dependa de una pieza puntual.</span>"
				+ "</div></div>";
			return html;
		}
		html += "<div class='odontograma-situacion-empty'>"
			+ "<strong>Selecciona una pieza</strong>"
			+ "<span>Los hallazgos y la ubicacion actual se mostraran aca para trabajar el paso activo.</span>"
			+ "</div></div>";
		return html;
	}
	var info = odontogramaInfoPiezaFDI(pieza);
	var mapa = odontogramaMapaPiezaPanel(contexto, pieza);
	var ubicacionTexto = estado.ubicacionActual && odontogramaUbicacionIncluyePieza(estado.ubicacionActual, pieza)
		? odontogramaTextoUbicacion(estado.ubicacionActual)
		: "Pieza " + pieza + " seleccionada";
	html += "<section class='odontograma-situacion-pieza'>"
		+ "<span>Pieza seleccionada</span>"
		+ "<strong>" + odontogramaEscape(pieza) + "</strong>"
		+ "<small>" + odontogramaEscape(odontogramaMetaPiezaTexto(info)) + "</small>"
		+ "</section>";
	html += "<section class='odontograma-panel-section odontograma-ubicacion-actual'>"
		+ "<strong>Ubicacion actual</strong>"
		+ "<p>" + odontogramaEscape(ubicacionTexto) + "</p>"
		+ "</section>";
	html += odontogramaRenderSelectorSuperficiesActual(contexto, pieza, info);
	html += odontogramaRenderContextoPasoTratamientos(contexto, pieza, mapa);
	html += odontogramaRenderHallazgosPieza(contexto, pieza, mapa.marcas, info);
	html += "</div>";
	return html;
}

function odontogramaRenderSelectorSuperficiesActual(contexto, pieza, info) {
	var estado = odontogramaEstados[contexto];
	var trActivo = estado.tratamientoPendiente || (contexto == "presupuesto" ? estado.tratamientoSeleccionado : null);
	if (contexto == "presupuesto") { return ""; }
	if (trActivo) { return ""; }
	var seleccionadas = estado.ubicacionActual && String(estado.ubicacionActual.pieza || "") == String(pieza)
		? odontogramaSuperficiesUbicacion(estado.ubicacionActual)
		: [];
	var todas = seleccionadas.length == odontogramaSuperficies.length;
	var html = "<section class='odontograma-panel-section odontograma-selector-superficies'>"
		+ "<strong>Seleccion de superficies</strong>"
		+ "<p>Toca una cara del grafico para agregarla o quitarla.</p>"
		+ "<div class='odontograma-superficie-controles'>"
		+ "<button type='button' class='" + (!seleccionadas.length ? "is-active" : "") + "' onclick='odontogramaSeleccionarPiezaCompleta(\"" + contexto + "\",\"" + odontogramaEscape(pieza) + "\")'>Pieza completa</button>"
		+ "<button type='button' class='" + (todas ? "is-active" : "") + "' onclick='odontogramaSeleccionarTodasSuperficies(\"" + contexto + "\",\"" + odontogramaEscape(pieza) + "\")'>Todas las superficies</button>"
		+ "</div>";
	if (seleccionadas.length) {
		html += "<div class='odontograma-superficies-seleccionadas'>";
		seleccionadas.forEach(function (sup) {
			html += "<span>" + odontogramaEscape(odontogramaTextoSuperficiePieza(sup, info)) + "</span>";
		});
		html += "</div>";
	}
	html += "</section>";
	return html;
}

function odontogramaRenderPanel(contexto) {
	var estado = odontogramaEstados[contexto];
	var config = odontogramaConfig(contexto);
	var pieza = estado.piezaSeleccionada;
	if (!pieza) {
		if (contexto == "presupuesto") {
			return "<div class='odontograma-ficha-pieza odontograma-ficha-pieza--empty odontograma-ficha-pieza--compacta odontograma-panel-empty'>"
				+ "<strong>Selecciona una pieza</strong>"
				+ "<span>El detalle aparecera cuando elijas una pieza o una ubicacion.</span>"
				+ "</div>";
		}
		return "<div class='odontograma-ficha-pieza odontograma-ficha-pieza--empty odontograma-panel-empty'>"
			+ "<strong>Selecciona una pieza</strong>"
			+ "<span>Toca una pieza del odontograma para ver sus hallazgos, tratamientos vinculados y superficies.</span>"
			+ "</div>";
	}
	var info = odontogramaInfoPiezaFDI(pieza);
	var mapa = odontogramaMapaPiezaPanel(contexto, pieza);
	var totalLinks = mapa.links.length + mapa.linksGenerales.length;
	var ausente = !!mapa.marcadores.ausente;
	var trActivo = estado.tratamientoPendiente || (contexto == "presupuesto" ? estado.tratamientoSeleccionado : null);
	var claseAusente = ausente ? " odontograma-pieza-ausente-panel" : "";
	var html = "<div class='odontograma-ficha-pieza" + claseAusente + "'>";
	html += "<header class='odontograma-ficha-header'>"
		+ "<div class='odontograma-ficha-header__main'>"
		+ "<span class='odontograma-ficha-kicker'>Pieza seleccionada</span>"
		+ "<strong class='odontograma-pieza-numero-grande'>" + odontogramaEscape(pieza) + "</strong>"
		+ "<span class='odontograma-pieza-meta'>" + odontogramaEscape(odontogramaMetaPiezaTexto(info)) + "</span>"
		+ "</div>";
	if (config.mostrarHistorial) {
		html += "<button type='button' class='odontograma-ficha-historial' onclick='odontogramaVerHistorialPieza(\"" + contexto + "\",\"" + odontogramaEscape(pieza) + "\")'>Historial</button>";
	}
	html += "</header>";
	if (ausente) {
		html += "<div class='odontograma-panel-alerta'>Esta pieza esta marcada como ausente.</div>";
	}
	if (trActivo) {
		html += "<div class='odontograma-panel-contexto'>Asignando: <strong>" + odontogramaEscape(trActivo.nombre || "Tratamiento") + "</strong>. Elegi la superficie correspondiente en pieza " + odontogramaEscape(pieza) + ".</div>";
	}
	if (config.superficiesAvanzadas || (trActivo && odontogramaNormalizarAlcance(trActivo.alcance) == "pieza_superficie")) {
		html += "<div class='odontograma-pieza-vistas'>"
			+ odontogramaRenderVistaSuperiorAmpliada(contexto, pieza, mapa, info)
			+ (config.superficiesAvanzadas ? odontogramaRenderVistaPerfil(contexto, pieza, mapa, info) : "")
			+ "</div>";
	}
	html += "<div class='odontograma-pieza-resumen'>"
		+ "<span><b>" + totalLinks + "</b> tratamientos</span>"
		+ "<span><b>" + mapa.marcas.length + "</b> situaciones</span>"
		+ (config.superficiesAvanzadas ? "<span><b>" + info.superficieCentral + "</b> superficie central</span>" : "")
		+ "</div>";
	html += odontogramaRenderTratamientosGrupo(contexto, pieza, mapa.links, "Tratamientos de esta pieza", "odontograma-tratamientos-pieza", false);
	html += odontogramaRenderTratamientosGrupo(contexto, pieza, mapa.linksGenerales, "Tratamientos generales aplicables", "odontograma-tratamientos-generales", true);
	html += "</div>";
	return html;
}

function odontogramaRenderVistaSuperiorAmpliada(contexto, pieza, mapa, info) {
	var paths = odontogramaSvgPathsPieza(info.tipo);
	var clase = "odontograma-vista-superior odontograma-pieza-anatomica odontograma-vista-superior--" + info.denticion + " odontograma-vista-superior--" + info.tipo + " " + mapa.clases.join(" ");
	var html = "<section class='" + clase + "'>"
		+ "<div class='odontograma-vista-title'><strong>Vista superior</strong><span>Seleccion de superficies</span></div>"
		+ "<div class='odontograma-superior-canvas'>"
		+ "<span class='odontograma-superior-label odontograma-superior-label--vestibular'>Vestibular</span>"
		+ "<span class='odontograma-superior-label odontograma-superior-label--mesial'>Mesial</span>"
		+ "<span class='odontograma-superior-label odontograma-superior-label--distal'>Distal</span>"
		+ "<span class='odontograma-superior-label odontograma-superior-label--lingual'>Lingual / Palatina</span>"
		+ "<svg class='odontograma-svg odontograma-svg-ampliado' viewBox='0 0 64 64' aria-hidden='true'>"
		+ "<circle class='odontograma-tooth-outline' cx='32' cy='32' r='" + paths.outline + "'></circle>"
		+ odontogramaSvgSurfacePanel(contexto, pieza, "vestibular", paths.vestibular, mapa.superficies, info)
		+ odontogramaSvgSurfacePanel(contexto, pieza, "mesial", paths.mesial, mapa.superficies, info)
		+ odontogramaSvgSurfacePanel(contexto, pieza, "distal", paths.distal, mapa.superficies, info)
		+ odontogramaSvgSurfacePanel(contexto, pieza, "lingual_palatina", paths.lingual_palatina, mapa.superficies, info)
		+ odontogramaSvgSurfacePanel(contexto, pieza, "oclusal_incisal", paths.oclusal_incisal, mapa.superficies, info)
		+ "<circle class='odontograma-tooth-centerline' cx='32' cy='32' r='" + paths.center + "'></circle>"
		+ "</svg>"
		+ "<span class='odontograma-superior-label odontograma-superior-label--centro'>" + odontogramaEscape(info.superficieCentral) + "</span>"
		+ "</div></section>";
	return html;
}

function odontogramaSvgSurfacePanel(contexto, pieza, superficie, points, superficies, info) {
	var color = superficies[superficie] || "";
	var activa = odontogramaSuperficieActiva(contexto, pieza, superficie) ? " is-active" : "";
	var clase = "odontograma-superficie odontograma-superficie-panel superficie-" + superficie + (color ? " odontograma-superficie--" + color : "") + activa;
	return "<path class='" + clase + "' data-label='" + odontogramaEscape(odontogramaTextoSuperficiePieza(superficie, info)) + "' d='" + points + "' onclick='odontogramaSeleccionarSuperficie(event,\"" + contexto + "\",\"" + pieza + "\",\"" + superficie + "\")'></path>";
}

function odontogramaRenderVistaPerfil(contexto, pieza, mapa, info) {
	var perfil = odontogramaPerfilPaths(info.tipo);
	var clase = "odontograma-vista-perfil" + (mapa.marcadores.ausente ? " odontograma-pieza-ausente-panel" : "");
	var dienteClase = "odontograma-diente-perfil odontograma-diente-perfil-" + info.tipo + " odontograma-diente-perfil-" + info.denticion + (info.denticion == "temporal" ? " odontograma-diente-temporal" : "");
	var tieneRojo = mapa.marcadores.caries || mapa.marcadores.extraccion || mapa.marcadores.protesisPrevista || odontogramaMapaTieneColor(mapa, "rojo");
	var tieneAzul = mapa.marcadores.obturacion || mapa.marcadores.protesisExistente || odontogramaMapaTieneColor(mapa, "azul");
	var html = "<section class='" + clase + "'>"
		+ "<div class='odontograma-vista-title'><strong>Vista perfil</strong><span>" + odontogramaEscape(info.tipoTexto || "Pieza") + "</span></div>"
		+ "<svg class='" + dienteClase + "' viewBox='0 0 120 150' aria-hidden='true'>"
		+ "<path class='odontograma-perfil-raiz' d='" + perfil.raiz + "'></path>";
	if (perfil.raiz2) {
		html += "<path class='odontograma-perfil-raiz odontograma-perfil-raiz--secundaria' d='" + perfil.raiz2 + "'></path>";
	}
	html += "<path class='odontograma-perfil-corona' d='" + perfil.corona + "'></path>"
		+ "<path class='odontograma-perfil-cuello' d='" + perfil.cuello + "'></path>";
	if (tieneRojo) {
		html += "<circle class='odontograma-perfil-marca odontograma-perfil-marca--rojo' cx='78' cy='48' r='7'></circle>";
	}
	if (tieneAzul) {
		html += "<circle class='odontograma-perfil-marca odontograma-perfil-marca--azul' cx='44' cy='57' r='6'></circle>";
	}
	if (mapa.marcadores.protesisPrevista || mapa.marcadores.protesisExistente) {
		html += "<path class='odontograma-perfil-protesis" + (mapa.marcadores.protesisExistente ? " odontograma-perfil-protesis--azul" : " odontograma-perfil-protesis--rojo") + "' d='M32 32 C50 18 72 18 90 32'></path>";
	}
	if (mapa.marcadores.ausente) {
		html += "<g class='odontograma-perfil-ausente-x'><path d='M34 34 L88 104'></path><path d='M88 34 L34 104'></path></g>";
	}
	html += "</svg></section>";
	return html;
}

function odontogramaPerfilPaths(tipo) {
	var mapas = {
		incisivo: {
			corona: "M42 24 C49 15 70 15 78 24 C81 38 78 55 69 66 C62 72 51 72 44 66 C37 55 38 38 42 24 Z",
			cuello: "M43 64 C54 70 66 70 77 64",
			raiz: "M49 67 C55 78 58 104 60 134 C62 104 66 78 72 67 C65 73 56 73 49 67 Z"
		},
		canino: {
			corona: "M39 28 C48 12 72 12 82 28 C79 47 69 62 61 70 C52 62 42 47 39 28 Z",
			cuello: "M43 64 C55 72 68 72 79 64",
			raiz: "M50 68 C56 83 59 111 60 140 C63 111 67 83 74 68 C66 74 57 74 50 68 Z"
		},
		premolar: {
			corona: "M34 29 C42 16 78 16 86 29 C88 45 80 62 69 70 C63 65 57 65 51 70 C40 62 32 45 34 29 Z",
			cuello: "M39 66 C52 73 68 73 81 66",
			raiz: "M47 69 C54 83 58 110 59 138 C63 110 67 83 74 69 C66 75 55 75 47 69 Z"
		},
		molar: {
			corona: "M27 32 C36 14 84 14 93 32 C96 50 86 66 73 73 C66 68 55 68 47 73 C34 66 24 50 27 32 Z",
			cuello: "M34 68 C49 76 72 76 87 68",
			raiz: "M42 71 C45 88 43 112 37 138 C52 119 58 91 55 73 C50 77 46 76 42 71 Z",
			raiz2: "M78 71 C75 88 77 112 83 138 C68 119 62 91 65 73 C70 77 74 76 78 71 Z"
		}
	};
	return mapas[tipo] || mapas.premolar;
}

function odontogramaMapaTieneColor(mapa, color) {
	var superficies = mapa && mapa.superficies ? mapa.superficies : {};
	for (var key in superficies) {
		if (superficies.hasOwnProperty(key) && superficies[key] == color) {
			return true;
		}
	}
	return false;
}

function odontogramaRenderTratamientosGrupo(contexto, pieza, links, titulo, clase, generales) {
	var html = "<section class='odontograma-panel-section " + clase + "'><strong>" + odontogramaEscape(titulo) + "</strong>";
	if (!links.length) {
		html += "<p>" + (generales ? "No hay tratamientos generales que incluyan esta pieza." : "Sin tratamientos especificos vinculados a esta pieza.") + "</p></section>";
		return html;
	}
	links.forEach(function (l) {
		var superficies = odontogramaSuperficiesLink(l).map(function (sup) {
			return odontogramaTextoSuperficie(sup);
		}).join(", ");
		var ubicacion = generales ? odontogramaTextoContextoGeneral(l, pieza) : (l.ubicacion_texto || ("Pieza " + pieza));
		if (!generales && superficies) {
			ubicacion = "Pieza " + pieza + " - " + superficies;
		}
		var referencia = l.venta_id ? "Venta #" + l.venta_id : (l.presupuesto_id ? "Presupuesto #" + l.presupuesto_id : "Sin venta");
		var riesgo = l.nivel_riesgo_snapshot ? odontogramaTextoRiesgo(l.nivel_riesgo_snapshot) : "";
		var ubicacionVisual = odontogramaRenderUbicacionVisual(contexto, l, { falta: false, alcance: l.alcance_odontologico || "" });
		html += "<article class='odontograma-link-item odontograma-tratamiento-ficha-card" + (generales ? " odontograma-tratamiento-general-card" : "") + "'>"
			+ "<div><b>" + odontogramaEscape(l.nombre_tratamiento_snapshot || "Tratamiento") + "</b>"
			+ ubicacionVisual
			+ "<span>" + odontogramaEscape(ubicacion) + "</span>"
			+ "<small>" + odontogramaEscape(referencia) + " &middot; " + odontogramaEscape(odontogramaTextoEstadoLink(l)) + (riesgo ? " &middot; " + odontogramaEscape(riesgo) : "") + "</small></div>"
			+ "<div class='odontograma-link-actions'>"
			+ "<button type='button' onclick='odontogramaEditarLink(\"" + contexto + "\",\"" + odontogramaEscape(l.id) + "\")'>Editar</button>"
			+ "<button type='button' title='Quitar ubicacion' onclick='odontogramaEliminarLink(\"" + contexto + "\",\"" + odontogramaEscape(l.id) + "\",\"" + odontogramaEscape(l.detalle_venta_id || "") + "\")'>Quitar</button>"
			+ "</div>"
			+ "</article>";
	});
	html += "</section>";
	return html;
}

function odontogramaRenderHallazgosPieza(contexto, pieza, marcas, info) {
	var html = "<section class='odontograma-panel-section odontograma-hallazgos-pieza'><strong>Hallazgos / situacion actual</strong>";
	if (!marcas.length) {
		html += "<p>Sin hallazgos registrados en esta pieza.</p>";
	} else {
		marcas.forEach(function (m) {
			var superficie = m.superficie ? odontogramaTextoSuperficiePieza(m.superficie, info) : "Pieza completa";
			var color = m.color ? " odontograma-marca-card--" + m.color : "";
			html += "<article class='odontograma-link-item odontograma-marca-card" + color + "'>"
				+ "<div><b>" + odontogramaEscape(odontogramaTextoMarca(m.tipo_marca)) + "</b><span>" + odontogramaEscape(superficie) + "</span></div>"
				+ "<button type='button' title='Eliminar marca' onclick='odontogramaEliminarMarca(\"" + contexto + "\",\"" + odontogramaEscape(m.id) + "\")'>x</button>"
				+ "</article>";
		});
	}
	html += "<div class='odontograma-panel-actions odontograma-hallazgo-actions'>"
		+ "<button type='button' onclick='odontogramaGuardarMarcaRapida(\"" + contexto + "\",\"caries\",\"observado\",\"rojo\")'>+ Caries</button>"
		+ "<button type='button' onclick='odontogramaGuardarMarcaRapida(\"" + contexto + "\",\"pieza_ausente\",\"existente\",\"gris\")'>+ Ausente</button>"
		+ "<button type='button' onclick='odontogramaGuardarMarcaRapida(\"" + contexto + "\",\"obturacion\",\"realizado\",\"azul\")'>+ Obturacion</button>";
	if (contexto == "ficha") {
		html += "<button type='button' onclick='odontogramaGuardarMarcaRapida(\"" + contexto + "\",\"extraccion_indicada\",\"indicado\",\"rojo\")'>+ Extraccion</button>"
			+ "<button type='button' onclick='odontogramaGuardarMarcaRapida(\"" + contexto + "\",\"protesis_prevista\",\"indicado\",\"rojo\")'>+ Protesis prev.</button>";
	}
	html += "<button type='button' onclick='odontogramaGuardarMarcaRapida(\"" + contexto + "\",\"protesis_existente\",\"existente\",\"azul\")'>+ Protesis exist.</button>"
		+ "</div></section>";
	return html;
}

function odontogramaRenderSuperficiesAmpliadas(contexto, pieza, mapa, info) {
	mapa = mapa || odontogramaMapaPiezaPanel(contexto, pieza);
	info = info || odontogramaInfoPiezaFDI(pieza);
	var botones = [
		{ id: "vestibular", clase: "superficie-vestibular" },
		{ id: "mesial", clase: "superficie-mesial" },
		{ id: "oclusal_incisal", clase: "superficie-oclusal" },
		{ id: "distal", clase: "superficie-distal" },
		{ id: "lingual_palatina", clase: "superficie-lingual" }
	];
	var html = "<div class='odontograma-superficies-ampliadas'>";
	botones.forEach(function (sup) {
		var color = mapa.superficies[sup.id] || "";
		var activa = odontogramaSuperficieActiva(contexto, pieza, sup.id);
		var clase = sup.clase + (color ? " is-marcada is-marcada-" + color : "") + (activa ? " is-active" : "");
		html += "<button type='button' class='" + clase + "' onclick='odontogramaSeleccionarSuperficie(event,\"" + contexto + "\",\"" + pieza + "\",\"" + sup.id + "\")'>"
			+ "<span>" + odontogramaEscape(odontogramaTextoSuperficiePieza(sup.id, info)) + "</span>"
			+ (color ? "<i aria-hidden='true'></i>" : "")
			+ "</button>";
	});
	html += "</div>";
	return html;
}

function odontogramaRenderTratamientosSinUbicacion(contexto) {
	var estado = odontogramaEstados[contexto];
	var items = estado.datos.tratamientos_sin_ubicacion || [];
	var html = "<section class='odontograma-sin-ubicacion odontograma-tratamientos-pendientes'><div class='odontograma-subhead'><strong>Tratamientos sin ubicacion</strong><span>" + items.length + "</span></div>";
	if (!items.length) {
		html += "<div class='odontograma-empty-inline'>Todos los tratamientos con alcance odontologico tienen ubicacion.</div>";
	} else {
		html += "<div class='odontograma-pendientes-grid'>";
		items.slice(0, 8).forEach(function (item) {
			var activo = estado.tratamientoPendiente && String(estado.tratamientoPendiente.detalle_venta_id || "") == String(item.detalle_venta_id || "");
			html += "<article class='odontograma-sin-ubicacion-item odontograma-tratamiento-card" + (activo ? " odontograma-tratamiento-activo-card" : "") + "' onclick='odontogramaAsignarTratamientoFicha(\"" + odontogramaEscape(item.detalle_venta_id) + "\",\"" + odontogramaEscape(item.venta_id) + "\",\"" + odontogramaEscape(item.producto_id) + "\",\"" + odontogramaEscape(item.nombre_tratamiento) + "\",\"" + odontogramaEscape(item.alcance_odontologico) + "\",\"" + odontogramaEscape(item.modo_individualizacion || "cantidad_libre") + "\",\"" + odontogramaEscape(item.requiere_laboratorio || 0) + "\")'>"
				+ "<div><strong>" + odontogramaEscape(item.nombre_tratamiento) + "</strong><span>Venta #" + odontogramaEscape(item.venta_id) + " &middot; " + odontogramaEscape(odontogramaTextoAlcance(item.alcance_odontologico)) + "</span><em>" + odontogramaEscape(odontogramaTextoRiesgo(item.nivel_riesgo_financiero)) + "</em></div>"
				+ "<button type='button' onclick='event.stopPropagation(); odontogramaAsignarTratamientoFicha(\"" + odontogramaEscape(item.detalle_venta_id) + "\",\"" + odontogramaEscape(item.venta_id) + "\",\"" + odontogramaEscape(item.producto_id) + "\",\"" + odontogramaEscape(item.nombre_tratamiento) + "\",\"" + odontogramaEscape(item.alcance_odontologico) + "\",\"" + odontogramaEscape(item.modo_individualizacion || "cantidad_libre") + "\",\"" + odontogramaEscape(item.requiere_laboratorio || 0) + "\")'>Asignar</button>"
				+ "</article>";
		});
		html += "</div>";
	}
	html += "</section>";
	return html;
}

function odontogramaTextoRiesgo(nivel) {
	nivel = Number(nivel || 1);
	var mapa = {
		1: "N1 Bajo",
		2: "N2 Moderado",
		3: "N3 Controlado",
		4: "N4 Alto",
		5: "N5 Critico"
	};
	return mapa[nivel] || ("N" + nivel);
}

function odontogramaCambiarModo(contexto, modo) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	var config = odontogramaConfig(contexto);
	if (config.modos && config.modos.indexOf(modo) < 0) { return; }
	estado.modo = modo;
	if (modo == "hallazgo") {
		estado.pasoClinico = "situacion";
		estado.filtroVisual = "situacion";
	} else if (modo == "asignar") {
		estado.pasoClinico = "tratamientos";
		estado.filtroVisual = "tratamientos";
	} else if (modo == "editar") {
		estado.pasoClinico = "revision";
		estado.filtroVisual = "todo";
	}
	if (modo != "asignar" && !estado.tratamientoPendiente && contexto != "presupuesto") {
		estado.ubicacionActual = null;
	}
	odontogramaRender(contexto);
}

function odontogramaCambiarPasoClinico(contexto, pasoId) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	var pasos = odontogramaConfig(contexto).pasos || odontogramaPasosClinicos;
	var paso = null;
	for (var i = 0; i < pasos.length; i++) {
		if (pasos[i].id == pasoId) {
			paso = pasos[i];
			break;
		}
	}
	if (!paso) { return; }
	estado.pasoClinico = paso.id;
	estado.modo = paso.modo;
	estado.filtroVisual = paso.filtro;
	estado.mensajeFlash = paso.texto;
	if (paso.id != "tratamientos" && !estado.tratamientoPendiente) {
		estado.ubicacionActual = null;
	}
	odontogramaRender(contexto);
}

function odontogramaCambiarFiltro(contexto, filtro) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	estado.filtroVisual = filtro;
	odontogramaRender(contexto);
}

function odontogramaCancelarAsignacion(contexto) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	estado.tratamientoPendiente = null;
	estado.tratamientoSeleccionado = null;
	estado.ubicacionActual = null;
	estado.mensajeFlash = "Asignacion cancelada. Podes seleccionar otro tratamiento o explorar una pieza.";
	if (contexto == "presupuesto") {
		estado.modo = "asignar";
		estado.pasoClinico = "tratamientos";
		estado.filtroVisual = "tratamientos";
	}
	odontogramaRender(contexto);
}

function odontogramaEditarLink(contexto, linkId) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	var links = estado.datos.links || [];
	var link = null;
	for (var i = 0; i < links.length; i++) {
		if (String(links[i].id) == String(linkId)) {
			link = links[i];
			break;
		}
	}
	if (!link) { return; }
	estado.modo = "asignar";
	estado.tratamientoPendiente = {
		detalle_venta_id: link.detalle_venta_id || "",
		presupuesto_item_id: link.presupuesto_item_id || "",
		venta_id: link.venta_id || "",
		producto_id: link.producto_id || "",
		nombre: link.nombre_tratamiento_snapshot || "Tratamiento",
		alcance: odontogramaNormalizarAlcance(link.alcance_odontologico)
	};
	estado.tratamientoSeleccionado = estado.tratamientoPendiente;
	estado.ubicacionActual = null;
	estado.mensajeFlash = "Editando ubicacion de " + (link.nombre_tratamiento_snapshot || "tratamiento") + ". Toca la nueva ubicacion.";
	odontogramaRender(contexto);
}

function odontogramaAsegurarModalHistorial() {
	var existente = document.getElementById("modalOdontogramaHistorial");
	if (existente) { return existente; }
	var overlay = document.createElement("div");
	overlay.id = "overlayOdontogramaHistorial";
	overlay.className = "odontograma-historial-overlay";
	overlay.style.display = "none";
	overlay.onclick = function () { odontogramaCerrarModalHistorial(); };
	document.body.appendChild(overlay);
	var modal = document.createElement("div");
	modal.id = "modalOdontogramaHistorial";
	modal.className = "odontograma-historial-modal";
	modal.style.display = "none";
	modal.innerHTML = "<div class='odontograma-historial-modal__head'>"
		+ "<div><h3 id='modalOdontogramaHistorialTitulo'>Historial del odontograma</h3><span id='modalOdontogramaHistorialSubtitulo'></span></div>"
		+ "<button type='button' onclick='odontogramaCerrarModalHistorial()'>&times;</button>"
		+ "</div><div class='odontograma-historial-modal__body' id='modalOdontogramaHistorialCuerpo'></div>"
		+ "<div class='odontograma-historial-modal__footer'><button type='button' class='odontograma-btn-primary' onclick='odontogramaCerrarModalHistorial()'>Entendido</button></div>";
	document.body.appendChild(modal);
	return modal;
}

function odontogramaAbrirModalHistorial(titulo, subtitulo, cuerpo) {
	odontogramaAsegurarModalHistorial();
	document.getElementById("modalOdontogramaHistorialTitulo").innerHTML = titulo || "Historial del odontograma";
	document.getElementById("modalOdontogramaHistorialSubtitulo").innerHTML = subtitulo || "";
	document.getElementById("modalOdontogramaHistorialCuerpo").innerHTML = cuerpo || "";
	document.getElementById("overlayOdontogramaHistorial").style.display = "";
	document.getElementById("modalOdontogramaHistorial").style.display = "flex";
}

function odontogramaCerrarModalHistorial() {
	var overlay = document.getElementById("overlayOdontogramaHistorial");
	var modal = document.getElementById("modalOdontogramaHistorial");
	if (overlay) { overlay.style.display = "none"; }
	if (modal) { modal.style.display = "none"; }
}

function odontogramaTextoAccionHistorial(accion) {
	var mapa = {
		crear_odontograma: "Creacion del odontograma",
		modificar_convalidado: "Modificacion sobre convalidado",
		vincular_tratamiento: "Tratamiento ubicado",
		actualizar_ubicacion_tratamiento: "Ubicacion modificada",
		agregar_marca: "Hallazgo agregado",
		eliminar_marca: "Hallazgo eliminado",
		quitar_vinculo_tratamiento: "Ubicacion quitada",
		convalidar_odontograma: "Odontograma convalidado",
		deshacer_accion: "Accion deshecha",
		migrar_presupuesto_venta: "Presupuesto vinculado a venta"
	};
	if (mapa[accion]) { return mapa[accion]; }
	return String(accion || "Accion").replace(/_/g, " ").replace(/\b\w/g, function (letra) {
		return letra.toUpperCase();
	});
}

function odontogramaEtiquetaCampoHistorial(campo) {
	var mapa = {
		pieza: "Pieza",
		superficie: "Superficie",
		superficies_json: "Superficies",
		arcada: "Arcada",
		cuadrante: "Cuadrante",
		boca_completa: "Boca completa",
		estado_marca: "Estado",
		tipo_marca: "Tipo",
		color: "Color",
		alcance: "Alcance",
		motivo: "Motivo"
	};
	return mapa[campo] || String(campo || "").replace(/_/g, " ");
}

function odontogramaValorLegibleHistorial(valor, nivel) {
	var texto;
	if (valor == null) { return ""; }
	nivel = nivel || 0;
	if (nivel > 2) { return ""; }
	if (typeof valor == "number" || typeof valor == "boolean") { return String(valor); }
	if (typeof valor == "string") {
		texto = valor.replace(/^\s+|\s+$/g, "");
		if (texto == "") { return ""; }
		if (texto.charAt(0) == "{" || texto.charAt(0) == "[") {
			try {
				return odontogramaValorLegibleHistorial(JSON.parse(texto), nivel + 1);
			} catch (error) {}
		}
		return texto;
	}
	if (Object.prototype.toString.call(valor) == "[object Array]") {
		var partesArray = [];
		for (var i = 0; i < valor.length && i < 8; i++) {
			var itemArray = odontogramaValorLegibleHistorial(valor[i], nivel + 1);
			if (itemArray) { partesArray.push(itemArray); }
		}
		return partesArray.join(", ");
	}
	if (typeof valor == "object") {
		var partes = [];
		for (var key in valor) {
			if (!valor.hasOwnProperty(key)) { continue; }
			var item = odontogramaValorLegibleHistorial(valor[key], nivel + 1);
			if (item == "") { continue; }
			if (key == "superficie") { item = odontogramaTextoSuperficie(item); }
			if (key == "superficies_json") {
				item = item.split(", ").map(function (superficie) {
					return odontogramaTextoSuperficie(superficie);
				}).join(", ");
			}
			if (key == "boca_completa") {
				item = (String(item) == "1" || String(item).toLowerCase() == "true") ? "Si" : "No";
			}
			partes.push(odontogramaEtiquetaCampoHistorial(key) + ": " + item);
			if (partes.length >= 8) { break; }
		}
		return partes.join(" | ");
	}
	return String(valor);
}

function odontogramaRenderBloqueCambioHistorial(titulo, valor) {
	var texto = odontogramaValorLegibleHistorial(valor, 0);
	if (!texto) { return ""; }
	return "<div class='odontograma-historial-valor'>"
		+ "<b>" + odontogramaEscape(titulo) + "</b>"
		+ "<span>" + odontogramaEscape(texto) + "</span>"
		+ "</div>";
}

function odontogramaRenderListaHistorial(items, mensajeVacio) {
	if (!items || !items.length) {
		return "<div class='odontograma-historial-empty'>" + odontogramaEscape(mensajeVacio || "Sin historial registrado.") + "</div>";
	}
	var html = "<div class='odontograma-historial-lista'>";
	items.forEach(function (h) {
		var meta = [];
		var antes = odontogramaRenderBloqueCambioHistorial("Antes", h.valor_anterior);
		var despues = odontogramaRenderBloqueCambioHistorial("Despues", h.valor_nuevo);
		if (h.pieza) { meta.push("Pieza " + h.pieza); }
		if (h.superficie) { meta.push(odontogramaTextoSuperficie(h.superficie)); }
		if (h.venta_id) { meta.push("Venta #" + h.venta_id); }
		if (h.presupuesto_id) { meta.push("Presupuesto #" + h.presupuesto_id); }
		html += "<article class='odontograma-historial-item'>"
			+ "<div class='odontograma-historial-top'>"
				+ "<time>" + odontogramaEscape(h.fecha_hora || "") + "</time>"
				+ "<em>Version " + odontogramaEscape(h.version || "1") + "</em>"
			+ "</div>"
			+ "<strong>" + odontogramaEscape(odontogramaTextoAccionHistorial(h.accion || "")) + "</strong>"
			+ "<span class='odontograma-historial-user'>" + odontogramaEscape(h.usuario_nombre || "Usuario") + "</span>";
		if (meta.length) {
			html += "<div class='odontograma-historial-meta'>" + odontogramaEscape(meta.join(" / ")) + "</div>";
		}
		if (h.descripcion) {
			html += "<p>" + odontogramaEscape(h.descripcion) + "</p>";
		}
		if (antes || despues) {
			html += "<div class='odontograma-historial-cambio'>" + antes + despues + "</div>";
		}
		if (h.motivo) {
			html += "<p class='odontograma-historial-motivo'>Motivo: " + odontogramaEscape(h.motivo) + "</p>";
		}
		html += "</article>";
	});
	html += "</div>";
	return html;
}

function odontogramaCargarHistorial(contexto, callback) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	odontogramaApi("obtenerHistorialOdontograma", estado.base, function (ok, datos) {
		var historial = ok ? (datos.historial || []) : (estado.datos.historial || []);
		if (ok) { estado.datos.historial = historial; }
		if (typeof callback == "function") {
			callback(historial, ok);
		}
	});
}

function odontogramaVerHistorialCompleto(contexto) {
	odontogramaAbrirModalHistorial(
		"Historial del odontograma",
		"Auditoria de cambios, usuario y version",
		"<div class='odontograma-historial-empty'>Cargando historial...</div>"
	);
	odontogramaCargarHistorial(contexto, function (historial) {
		var cuerpo = document.getElementById("modalOdontogramaHistorialCuerpo");
		if (cuerpo) {
			cuerpo.innerHTML = odontogramaRenderListaHistorial(historial, "Sin historial registrado para este odontograma.");
		}
	});
}

function odontogramaVerHistorialPieza(contexto, pieza) {
	odontogramaAbrirModalHistorial(
		"Historial de pieza " + odontogramaEscape(pieza || ""),
		"Cambios realizados sobre la pieza seleccionada",
		"<div class='odontograma-historial-empty'>Cargando historial...</div>"
	);
	odontogramaCargarHistorial(contexto, function (historial) {
		var items = (historial || []).filter(function (h) {
			return !pieza || String(h.pieza || "") == String(pieza);
		});
		var cuerpo = document.getElementById("modalOdontogramaHistorialCuerpo");
		if (cuerpo) {
			cuerpo.innerHTML = odontogramaRenderListaHistorial(items, "Sin historial visible para esta pieza.");
		}
	});
}

function odontogramaMostrarLeyendaCompleta() {
	var mensaje = "Caries observada: punto rojo.<br>"
		+ "Obturacion realizada: punto azul o superficie azul.<br>"
		+ "Diente ausente: X azul.<br>"
		+ "Extraccion prevista: X roja.<br>"
		+ "Protesis prevista: puente rojo.<br>"
		+ "Protesis existente: puente azul.<br>"
		+ "Rojo indica observado, pendiente o indicado. Azul indica realizado o existente.";
	ver_vetana_informativa("Leyenda del odontograma", mensaje, "info");
}

function odontogramaResaltarTratamientosRelacionados(contexto, pieza) {
	var estado = odontogramaEstados[contexto];
	if (!estado || !pieza) { return; }
	document.querySelectorAll(".odontograma-plan-resaltado").forEach(function (el) {
		el.classList.remove("odontograma-plan-resaltado");
	});
	var links = (estado.datos.links || []).filter(function (l) {
		return String(l.pieza || "") == String(pieza) || (!l.pieza && odontogramaUbicacionIncluyePieza(l, pieza));
	});
	links.forEach(function (link) {
		var detalle = link.detalle_venta_id || "";
		if (!detalle) { return; }
		document.querySelectorAll("[data-detalle-tratamiento='" + detalle + "'], [data-detalle-odontograma='" + detalle + "']").forEach(function (el) {
			el.classList.add("odontograma-plan-resaltado");
		});
	});
}

function odontogramaPiezaMarcadaAusente(estado, pieza) {
	if (!estado || !pieza) { return false; }
	var marcas = estado.datos && estado.datos.marcas ? estado.datos.marcas : [];
	for (var i = 0; i < marcas.length; i++) {
		if (String(marcas[i].pieza) == String(pieza) && String(marcas[i].tipo_marca || "") == "pieza_ausente") {
			return true;
		}
	}
	return false;
}

function odontogramaEnfocarUbicacionFicha(pieza, arcada, cuadrante, bocaCompleta, superficie, piezasJson) {
	odontogramaEnfocarUbicacion("ficha", pieza, arcada, cuadrante, bocaCompleta, superficie, piezasJson);
}

function odontogramaEnfocarUbicacion(contexto, pieza, arcada, cuadrante, bocaCompleta, superficie, piezasJson) {
	var estado = odontogramaEstados[contexto];
	if (!estado) {
		if (contexto == "ficha" && typeof cargarOdontogramaFichaClinica == "function") {
			cargarOdontogramaFichaClinica();
		}
		return;
	}
	var ubicacion = {};
	pieza = String(pieza || "");
	arcada = String(arcada || "");
	cuadrante = String(cuadrante || "");
	superficie = String(superficie || "");
	var piezasMultiples = [];
	try {
		piezasMultiples = piezasJson ? JSON.parse(piezasJson) : [];
		if (!Array.isArray(piezasMultiples)) { piezasMultiples = []; }
	} catch (e) {
		piezasMultiples = String(piezasJson || "").split(",").filter(function (valor) {
			return String(valor || "").trim() !== "";
		});
	}
	if (bocaCompleta && String(bocaCompleta) != "0") {
		ubicacion.boca_completa = 1;
		estado.piezaSeleccionada = "";
		estado.mensajeFlash = "Boca completa seleccionada. El tratamiento aplica a todas las piezas.";
	} else if (piezasMultiples.length) {
		ubicacion = odontogramaUbicacionPresupuestoPiezasMultiples(piezasMultiples) || {};
		estado.piezaSeleccionada = piezasMultiples[0] || "";
		estado.mensajeFlash = odontogramaTextoPiezasMultiples(piezasMultiples) + " seleccionadas en el odontograma.";
	} else if (arcada) {
		ubicacion.arcada = odontogramaNormalizarArcada(arcada);
		estado.piezaSeleccionada = "";
		estado.mensajeFlash = odontogramaTextoArcada(arcada) + " seleccionada en el odontograma.";
	} else if (cuadrante) {
		ubicacion.cuadrante = cuadrante;
		estado.piezaSeleccionada = "";
		estado.mensajeFlash = "Cuadrante " + odontogramaNormalizarCuadrante(cuadrante) + " seleccionado en el odontograma.";
	} else if (pieza) {
		ubicacion.pieza = pieza;
		ubicacion.denticion = odontogramaDenticionPieza(pieza);
		if (superficie) {
			ubicacion.superficie = superficie;
			ubicacion.superficies_json = JSON.stringify([superficie]);
		}
		estado.piezaSeleccionada = pieza;
		estado.mensajeFlash = "Pieza " + pieza + " enfocada desde la ubicacion del tratamiento.";
	}
	estado.ubicacionActual = Object.keys(ubicacion).length ? ubicacion : null;
	odontogramaRender(contexto);
}

function odontogramaToggleSeleccionMultiple(contexto) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	var tratamiento = estado.tratamientoPendiente || (contexto == "presupuesto" ? estado.tratamientoSeleccionado : null);
	if (!tratamiento) {
		estado.mensajeFlash = "Primero selecciona un tratamiento. Despues activa Mas de 1 para elegir varias piezas.";
		odontogramaRender(contexto);
		return;
	}
	if (contexto != "presupuesto" && tratamiento.modo_individualizacion != "multipieza" && tratamiento.modo_individualizacion != "sector") {
		estado.mensajeFlash = "Este tratamiento no admite varias piezas en una sola unidad.";
		odontogramaRender(contexto);
		return;
	}
	if (estado.agregandoAutomatico) {
		estado.mensajeFlash = "Se esta agregando el tratamiento anterior. Espera unos segundos.";
		odontogramaRender(contexto);
		return;
	}
	estado.seleccionMultipleActiva = !estado.seleccionMultipleActiva;
	estado.piezasMultiples = [];
	estado.ubicacionActual = null;
	if (estado.seleccionMultipleActiva) {
		estado.piezaSeleccionada = "";
		estado.mensajeFlash = "Seleccion multiple activa. Toca dos o mas piezas y confirma con OK.";
	} else {
		estado.mensajeFlash = "Seleccion multiple cancelada. Podes tocar una pieza para agregar el tratamiento normal.";
	}
	odontogramaRender(contexto);
}

function odontogramaTogglePiezaMultiple(contexto, pieza) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	pieza = String(pieza || "");
	if (!pieza) { return; }
	var lista = estado.piezasMultiples || [];
	var indice = lista.indexOf(pieza);
	if (indice >= 0) {
		lista.splice(indice, 1);
	} else {
		lista.push(pieza);
	}
	estado.piezasMultiples = lista;
	estado.piezaSeleccionada = pieza;
	estado.ubicacionActual = odontogramaUbicacionPresupuestoPiezasMultiples(lista);
	estado.mensajeFlash = lista.length
		? odontogramaTextoPiezasMultiples(lista) + ". Pulsa OK cuando termines la seleccion."
		: "Seleccion multiple activa. Toca dos o mas piezas y confirma con OK.";
	odontogramaRender(contexto);
}

function odontogramaConfirmarSeleccionMultiple(contexto) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	var piezas = estado.piezasMultiples || [];
	if (piezas.length < 2) {
		estado.mensajeFlash = "Selecciona al menos dos piezas para usar Mas de 1.";
		odontogramaRender(contexto);
		return;
	}
	var ubicacion = odontogramaUbicacionPresupuestoPiezasMultiples(piezas);
	if (!ubicacion) { return; }
	estado.ubicacionActual = ubicacion;
	estado.seleccionMultipleActiva = false;
	if (estado.tratamientoPendiente) {
		odontogramaRender(contexto);
		odontogramaGuardarLink(contexto, estado.tratamientoPendiente, ubicacion);
		return;
	}
	if (contexto == "presupuesto" && estado.tratamientoSeleccionado) {
		odontogramaRender(contexto);
		odontogramaAgregarTratamientoPresupuestoAutomatico();
		return;
	}
	estado.mensajeFlash = "Primero selecciona un tratamiento para guardar varias piezas.";
	odontogramaRender(contexto);
}

function odontogramaSeleccionarPieza(evento, contexto, pieza) {
	if (evento) { evento.stopPropagation(); }
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	estado.piezaSeleccionada = pieza;
	var pendiente = estado.tratamientoPendiente || (contexto == "presupuesto" ? estado.tratamientoSeleccionado : null);
	var alcance = pendiente ? odontogramaNormalizarAlcance(pendiente.alcance) : "";
	var piezaAusente = odontogramaPiezaMarcadaAusente(estado, pieza);
	if (!pendiente) {
		estado.ubicacionActual = null;
		if (estado.pasoClinico == "tratamientos" && piezaAusente) {
			estado.mensajeFlash = "Pieza " + pieza + " seleccionada: esta marcada como ausente en Situacion actual. Revisar tratamientos compatibles.";
		} else if (contexto == "presupuesto") {
			estado.mensajeFlash = "Pieza " + pieza + " seleccionada. Puedes marcar situacion actual o asignar un tratamiento.";
		} else {
			estado.mensajeFlash = "Pieza " + pieza + " seleccionada. El panel izquierdo muestra hallazgos y ubicacion actual; la ficha derecha muestra tratamientos y superficies.";
		}
		odontogramaResaltarTratamientosRelacionados(contexto, pieza);
		odontogramaRender(contexto);
		return;
	}
	if (contexto == "presupuesto") {
		if (estado.seleccionMultipleActiva) {
			odontogramaTogglePiezaMultiple(contexto, pieza);
			return;
		}
		if (pendiente && (pendiente.modo_individualizacion == "multipieza" || pendiente.modo_individualizacion == "sector" || alcance == "piezas_multiples")) {
			estado.seleccionMultipleActiva = true;
			odontogramaTogglePiezaMultiple(contexto, pieza);
			return;
		}
		if (alcance == "boca_completa") {
			odontogramaAplicarUbicacion(contexto, { boca_completa: 1 });
			return;
		}
		if (alcance == "arcada") {
			odontogramaAplicarUbicacion(contexto, { arcada: odontogramaArcadaPieza(pieza) });
			return;
		}
		if (alcance == "cuadrante") {
			odontogramaAplicarUbicacion(contexto, { cuadrante: odontogramaCuadrantePieza(pieza) });
			return;
		}
		odontogramaAplicarUbicacion(contexto, { pieza: pieza, denticion: odontogramaDenticionPieza(pieza) });
		return;
	}
	if (estado.seleccionMultipleActiva || (pendiente && (pendiente.modo_individualizacion == "multipieza" || pendiente.modo_individualizacion == "sector" || alcance == "piezas_multiples"))) {
		estado.seleccionMultipleActiva = true;
		odontogramaTogglePiezaMultiple(contexto, pieza);
		return;
	}
	if (pendiente && alcance == "boca_completa") {
		odontogramaAplicarUbicacion(contexto, { boca_completa: 1 });
		return;
	}
	if (pendiente && alcance == "arcada") {
		odontogramaAplicarUbicacion(contexto, { arcada: odontogramaArcadaPieza(pieza) });
		return;
	}
	if (pendiente && alcance == "cuadrante") {
		odontogramaAplicarUbicacion(contexto, { cuadrante: odontogramaCuadrantePieza(pieza) });
		return;
	}
	if (pendiente && alcance == "pieza_dental") {
		odontogramaAplicarUbicacion(contexto, { pieza: pieza, denticion: odontogramaDenticionPieza(pieza) });
		return;
	}
	if (pendiente && alcance == "pieza_superficie") {
		if (contexto == "presupuesto") {
			odontogramaAplicarUbicacion(contexto, { pieza: pieza, denticion: odontogramaDenticionPieza(pieza) });
			return;
		}
		if (estado.seleccionMultipleActiva || pendiente.modo_individualizacion == "multipieza" || pendiente.modo_individualizacion == "sector" || alcance == "piezas_multiples") {
			estado.seleccionMultipleActiva = true;
			odontogramaTogglePiezaMultiple(contexto, pieza);
			return;
		}
		estado.mensajeFlash = piezaAusente
			? "Pieza " + pieza + " marcada como ausente. Confirma si corresponde elegir una superficie para este tratamiento."
			: "Pieza " + pieza + " seleccionada. Elegi una superficie en la vista ampliada o en los botones tactiles.";
		odontogramaRender(contexto);
		return;
	}
	odontogramaRender(contexto);
}

function odontogramaSeleccionarSuperficie(evento, contexto, pieza, superficie) {
	if (evento) { evento.stopPropagation(); }
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	estado.piezaSeleccionada = pieza;
	var pendiente = estado.tratamientoPendiente || (contexto == "presupuesto" ? estado.tratamientoSeleccionado : null);
	if (pendiente) {
		var alcance = odontogramaNormalizarAlcance(pendiente.alcance);
		if (contexto == "presupuesto") {
			if (estado.seleccionMultipleActiva) {
				odontogramaTogglePiezaMultiple(contexto, pieza);
				return;
			}
			if (pendiente.modo_individualizacion == "multipieza" || pendiente.modo_individualizacion == "sector" || alcance == "piezas_multiples") {
				estado.seleccionMultipleActiva = true;
				odontogramaTogglePiezaMultiple(contexto, pieza);
				return;
			}
			if (alcance == "arcada") {
				odontogramaAplicarUbicacion(contexto, { arcada: odontogramaArcadaPieza(pieza) });
				return;
			}
			odontogramaAplicarUbicacion(contexto, { pieza: pieza, denticion: odontogramaDenticionPieza(pieza) });
			return;
		}
		if (estado.seleccionMultipleActiva || pendiente.modo_individualizacion == "multipieza" || pendiente.modo_individualizacion == "sector" || alcance == "piezas_multiples") {
			estado.seleccionMultipleActiva = true;
			odontogramaTogglePiezaMultiple(contexto, pieza);
			return;
		}
		if (alcance == "boca_completa") {
			odontogramaAplicarUbicacion(contexto, { boca_completa: 1 });
			return;
		}
		if (alcance == "arcada") {
			odontogramaAplicarUbicacion(contexto, { arcada: odontogramaArcadaPieza(pieza) });
			return;
		}
		if (alcance == "cuadrante") {
			odontogramaAplicarUbicacion(contexto, { cuadrante: odontogramaCuadrantePieza(pieza) });
			return;
		}
		odontogramaAplicarUbicacion(contexto, { pieza: pieza, denticion: odontogramaDenticionPieza(pieza), superficie: superficie, superficies_json: JSON.stringify([superficie]) });
		return;
	}
	var ubicacionBase = estado.ubicacionActual && String(estado.ubicacionActual.pieza || "") == String(pieza)
		? estado.ubicacionActual
		: { pieza: pieza, denticion: odontogramaDenticionPieza(pieza) };
	var lista = odontogramaSuperficiesUbicacion(ubicacionBase);
	var indice = lista.indexOf(superficie);
	if (indice >= 0) {
		lista.splice(indice, 1);
	} else {
		lista.push(superficie);
	}
	if (lista.length) {
		estado.ubicacionActual = {
			pieza: pieza,
			denticion: odontogramaDenticionPieza(pieza),
			superficie: lista[0],
			superficies_json: JSON.stringify(lista)
		};
	} else {
		estado.ubicacionActual = null;
	}
	var info = odontogramaInfoPiezaFDI(pieza);
	var textoSeleccion = lista.length
		? lista.map(function (sup) { return odontogramaTextoSuperficiePieza(sup, info); }).join(", ")
		: "pieza completa";
	estado.mensajeFlash = "Seleccion actual en pieza " + pieza + ": " + textoSeleccion + ". Podes agregar o quitar superficies y luego guardar el hallazgo.";
	odontogramaResaltarTratamientosRelacionados(contexto, pieza);
	odontogramaRender(contexto);
}

function odontogramaSeleccionarPiezaCompleta(contexto, pieza) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	estado.piezaSeleccionada = pieza;
	estado.ubicacionActual = null;
	estado.mensajeFlash = "Pieza " + pieza + " completa seleccionada. El proximo hallazgo se guardara sin superficie especifica.";
	odontogramaRender(contexto);
}

function odontogramaSeleccionarTodasSuperficies(contexto, pieza) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	var lista = odontogramaSuperficies.map(function (sup) { return sup.id; });
	estado.piezaSeleccionada = pieza;
	estado.ubicacionActual = {
		pieza: pieza,
		denticion: odontogramaDenticionPieza(pieza),
		superficie: lista[0],
		superficies_json: JSON.stringify(lista)
	};
	estado.mensajeFlash = "Todas las superficies de la pieza " + pieza + " quedaron seleccionadas para el proximo hallazgo.";
	odontogramaRender(contexto);
}

function odontogramaSeleccionGeneral(contexto, tipo, valor) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	if (estado.seleccionMultipleActiva || contexto == "presupuesto") {
		estado.seleccionMultipleActiva = false;
		estado.piezasMultiples = [];
	}
	var ubicacion = {};
	if (tipo == "boca_completa") {
		ubicacion.boca_completa = 1;
	} else if (tipo == "arcada") {
		ubicacion.arcada = valor;
	} else if (tipo == "cuadrante") {
		ubicacion.cuadrante = valor;
	}
	if (contexto == "ficha" && !estado.tratamientoPendiente) {
		estado.ubicacionActual = ubicacion;
		estado.piezaSeleccionada = "";
		odontogramaRender(contexto);
		return;
	}
	odontogramaAplicarUbicacion(contexto, ubicacion);
}

function odontogramaAplicarUbicacion(contexto, ubicacion) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	if (contexto == "presupuesto" && estado.tratamientoPendiente) {
		var ubicacionPresupuestoPendiente = odontogramaUbicacionPresupuestoNormalizada(ubicacion);
		if (!ubicacionPresupuestoPendiente) {
			estado.mensajeFlash = "Selecciona una pieza, varias piezas, arcada o boca completa para guardar el tratamiento.";
			odontogramaRender(contexto);
			return;
		}
		estado.ubicacionActual = ubicacionPresupuestoPendiente;
		odontogramaRender(contexto);
		odontogramaGuardarLink(contexto, estado.tratamientoPendiente, ubicacionPresupuestoPendiente);
		return;
	}
	if (contexto == "presupuesto" && estado.tratamientoSeleccionado) {
		if (estado.agregandoAutomatico) {
			estado.mensajeFlash = "Se esta agregando el tratamiento anterior. Espera unos segundos antes de tocar otra pieza.";
			odontogramaRender(contexto);
			return;
		}
		var ubicacionPresupuesto = odontogramaUbicacionPresupuestoNormalizada(ubicacion);
		if (!ubicacionPresupuesto) {
			estado.mensajeFlash = "Para agregar al plan selecciona una pieza, varias piezas, arcada o boca completa.";
			odontogramaRender(contexto);
			return;
		}
		estado.ubicacionActual = ubicacionPresupuesto;
		odontogramaRender(contexto);
		odontogramaAgregarTratamientoPresupuestoAutomatico();
		return;
	}
	if (contexto == "presupuesto") {
		estado.ubicacionActual = odontogramaUbicacionPresupuestoNormalizada(ubicacion);
		odontogramaRender(contexto);
		return;
	}
	if (contexto == "ficha" && estado.tratamientoPendiente) {
		estado.ubicacionActual = ubicacion;
		odontogramaRender(contexto);
		odontogramaGuardarLink(contexto, estado.tratamientoPendiente, ubicacion);
		return;
	}
	odontogramaRender(contexto);
}

function odontogramaAgregarTratamientoPresupuestoAutomatico() {
	var estado = odontogramaEstados.presupuesto;
	if (!estado || !estado.tratamientoSeleccionado || !estado.ubicacionActual) { return; }
	if (typeof anhadirPrPresupuesto != "function") {
		estado.mensajeFlash = "No se encontro la funcion para agregar el tratamiento al presupuesto.";
		odontogramaRender("presupuesto");
		return;
	}
	estado.agregandoAutomatico = true;
	estado.ubicacionAutomaticaPendiente = Object.assign({}, estado.ubicacionActual);
	estado.mensajeFlash = "Agregando " + (estado.tratamientoSeleccionado.nombre || "tratamiento") + " en " + odontogramaTextoUbicacion(estado.ubicacionActual) + ".";
	if (typeof presupuestoDocSetEstadoBusqueda == "function") {
		presupuestoDocSetEstadoBusqueda("Agregando al detalle del plan...", "info");
	}
	odontogramaRender("presupuesto");
	anhadirPrPresupuesto({ origenOdontogramaAuto: true });
}

function odontogramaTextoUbicacion(ubicacion) {
	if (!ubicacion) { return "Falta ubicar"; }
	var piezas = odontogramaPiezasUbicacion(ubicacion);
	if (piezas.length) { return odontogramaTextoPiezasMultiples(piezas); }
	if (odontogramaEsVerdadero(ubicacion.boca_completa)) { return "Boca completa"; }
	if (ubicacion.arcada) { return odontogramaTextoArcada(ubicacion.arcada); }
	if (ubicacion.cuadrante) { return "Cuadrante " + odontogramaNormalizarCuadrante(ubicacion.cuadrante); }
	var texto = ubicacion.pieza ? "Pieza " + ubicacion.pieza : "";
	var superficies = odontogramaSuperficiesUbicacion(ubicacion).map(function (sup) {
		return odontogramaTextoSuperficiePieza(sup, ubicacion.pieza ? odontogramaInfoPiezaFDI(ubicacion.pieza) : null);
	});
	if (superficies.length) {
		texto += " - " + superficies.join(", ");
	}
	return texto || "Falta ubicar";
}

function odontogramaPrepararTratamientoPresupuesto(codProducto, nombre) {
	var estado = odontogramaEstados.presupuesto;
	if (!estado) {
		odontogramaEstados.presupuesto = odontogramaEstadoInicial({
			tipo: "presupuesto",
			contenedor: "odontogramaPresupuestoDoctor",
			base: odontogramaDatosBasePresupuesto(),
			datos: { odontograma: {}, marcas: [], links: [], historial: [], tratamientos_sin_ubicacion: [] },
			modo: "asignar"
		});
		cargarOdontogramaPresupuestoDoctor();
	}
	odontogramaApi("obtenerAlcanceProducto", { producto_id: codProducto, unidad_pieza_presupuesto: "0" }, function (ok, datos) {
		var producto = ok ? datos.producto : { cod_producto: codProducto, nombre_producto: nombre, alcance_odontologico: "pieza_dental" };
		var modoIndividualizacion = String(producto.modo_individualizacion || "cantidad_libre");
		var alcance = odontogramaAlcancePorModoIndividualizacion(producto);
		var usaSeleccionMultiple = modoIndividualizacion == "multipieza" || modoIndividualizacion == "sector";
		var ubicacion = null;
		if (!odontogramaEstados.presupuesto) { return; }
		odontogramaEstados.presupuesto.tratamientoSeleccionado = {
			producto_id: producto.cod_producto || codProducto,
			nombre: producto.nombre_producto || nombre,
			alcance: alcance,
			requiere_laboratorio: producto.requiere_laboratorio == 1 ? 1 : 0,
			modo_individualizacion: modoIndividualizacion
		};
		odontogramaEstados.presupuesto.modo = "asignar";
		odontogramaEstados.presupuesto.pasoClinico = "tratamientos";
		odontogramaEstados.presupuesto.filtroVisual = "tratamientos";
		odontogramaEstados.presupuesto.mensajeFlash = usaSeleccionMultiple
			? "Asignando: " + (producto.nombre_producto || nombre) + ". Toca todas las piezas incluidas y confirma con OK; se guardara como un unico tratamiento."
			: (alcance == "arcada"
				? "Asignando: " + (producto.nombre_producto || nombre) + ". Toca una pieza de la arcada correspondiente o usa los botones de arcada."
				: "Asignando: " + (producto.nombre_producto || nombre) + ". Toca la ubicacion clinica para agregar una unidad independiente al plan.");
		odontogramaEstados.presupuesto.ubicacionActual = ubicacion;
		odontogramaEstados.presupuesto.ubicacionAutomaticaPendiente = null;
		odontogramaEstados.presupuesto.seleccionMultipleActiva = usaSeleccionMultiple;
		odontogramaEstados.presupuesto.piezasMultiples = [];
		odontogramaEstados.presupuesto.agregandoAutomatico = false;
		odontogramaEstados.presupuesto.tratamientoPendiente = null;
		odontogramaRender("presupuesto");
	});
}

function odontogramaVincularDetallePresupuestoAgregado(presupuestoItemId, codProducto, nombreProducto) {
	var estado = odontogramaEstados.presupuesto;
	if (!estado) { return; }
	var esAutomatico = !!estado.agregandoAutomatico;
	var tr = estado.tratamientoSeleccionado || { producto_id: codProducto, nombre: nombreProducto, alcance: "pieza_dental", modo_individualizacion: "cantidad_libre", requiere_laboratorio: 0 };
	var ubicacion = estado.ubicacionAutomaticaPendiente || estado.ubicacionActual;
	var esMultiple = odontogramaPiezasUbicacion(ubicacion).length > 0;
	var alcanceUbicacion = odontogramaAlcanceUbicacionPresupuesto(ubicacion);
	var esGeneral = ["boca_completa", "arcada", "cuadrante"].indexOf(alcanceUbicacion) >= 0;
	if (esMultiple || esGeneral) {
		tr.alcance = alcanceUbicacion;
	} else if (esAutomatico) {
		tr.alcance = odontogramaAlcancePresupuestoUnidadPieza(tr.alcance);
	}
	odontogramaActualizarFilaPresupuesto(presupuestoItemId, ubicacion ? odontogramaTextoUbicacion(ubicacion) : "Falta ubicar", !ubicacion, tr, ubicacion);
	if (typeof sincronizarResumenDetallePresupuestoDoc == "function") {
		sincronizarResumenDetallePresupuestoDoc();
	}
	if (!ubicacion || tr.alcance == "no_requiere") {
		if (esAutomatico) {
			odontogramaPresupuestoAutoFinalizar(false, "No se pudo agregar automaticamente porque falta seleccionar una pieza dentaria.");
		} else {
			estado.tratamientoSeleccionado = null;
			estado.ubicacionActual = null;
			odontogramaRender("presupuesto");
		}
		return;
	}
	var payload = Object.assign({}, estado.base, ubicacion, {
		presupuesto_id: idabmPresupuesto || estado.base.presupuesto_id,
		presupuesto_item_id: presupuestoItemId,
		producto_id: tr.producto_id || codProducto,
		nombre_tratamiento: tr.nombre || nombreProducto,
		alcance_odontologico: tr.alcance || "pieza_dental",
		unidad_pieza_presupuesto: (esAutomatico && !esMultiple && !esGeneral) ? "1" : "0",
		origen: "presupuesto"
	});
	odontogramaApi("guardarLinkTratamientoOdontograma", payload, function (ok, datos) {
		if (ok) {
			odontogramaActualizarFilaPresupuesto(presupuestoItemId, datos.ubicacion_texto || odontogramaTextoUbicacion(ubicacion), false, tr, ubicacion);
			if (typeof sincronizarResumenDetallePresupuestoDoc == "function") {
				sincronizarResumenDetallePresupuestoDoc();
			}
			if (esAutomatico) {
				estado.tratamientoSeleccionado = {
					producto_id: tr.producto_id || codProducto,
					nombre: tr.nombre || nombreProducto,
					alcance: odontogramaAlcancePorModoIndividualizacion(tr),
					requiere_laboratorio: tr.requiere_laboratorio == 1 ? 1 : 0,
					modo_individualizacion: tr.modo_individualizacion || "cantidad_libre"
				};
				estado.tratamientoPendiente = null;
				estado.ubicacionActual = null;
				estado.ubicacionAutomaticaPendiente = null;
				estado.seleccionMultipleActiva = tr.modo_individualizacion == "multipieza" || tr.modo_individualizacion == "sector";
				estado.piezasMultiples = [];
				estado.agregandoAutomatico = false;
				estado.modo = "asignar";
				estado.pasoClinico = "tratamientos";
				estado.filtroVisual = "tratamientos";
				estado.mensajeFlash = "Agregado en " + odontogramaTextoUbicacion(ubicacion) + ". Toca otra pieza o selecciona otro tratamiento.";
				if (typeof presupuestoDocSetEstadoBusqueda == "function") {
					presupuestoDocSetEstadoBusqueda("Tratamiento activo. Toque otra pieza para repetirlo o busque otro tratamiento.", "ok");
				}
			} else {
				estado.tratamientoSeleccionado = null;
				estado.ubicacionActual = null;
				estado.ubicacionAutomaticaPendiente = null;
				estado.seleccionMultipleActiva = false;
				estado.piezasMultiples = [];
				estado.agregandoAutomatico = false;
			}
			odontogramaRefrescar("presupuesto");
			return;
		}
		if (esAutomatico) {
			odontogramaPresupuestoAutoFinalizar(false, datos.mensaje || "El tratamiento se agrego, pero no se pudo guardar la pieza.");
		}
	});
}

function odontogramaActualizarFilaPresupuesto(presupuestoItemId, ubicacionTexto, falta, tratamiento, ubicacionVisualDatos) {
	var tablas = document.querySelectorAll("#tdDetalleVenta_" + presupuestoItemId);
	tablas.forEach(function (tabla) {
		var celda = tabla.querySelector("#td_datos_2");
		if (!celda) { return; }
		var existente = celda.querySelector(".odontograma-presupuesto-linea");
		if (existente) { existente.remove(); }
		var div = document.createElement("div");
		div.className = "odontograma-presupuesto-linea" + (falta ? " odontograma-presupuesto-linea--falta" : "");
		var visual = odontogramaRenderUbicacionVisual("presupuesto", ubicacionVisualDatos || null, { falta: falta, alcance: tratamiento && tratamiento.alcance ? tratamiento.alcance : "" });
		var textoBoton = falta ? "Asignar ubicacion" : "Editar ubicacion";
		div.innerHTML = visual
			+ "<button type='button' onclick='event.stopPropagation(); odontogramaAsignarItemPresupuesto(\"" + odontogramaEscape(presupuestoItemId) + "\",\"" + odontogramaEscape(tratamiento && tratamiento.producto_id ? tratamiento.producto_id : "") + "\",\"" + odontogramaEscape(tratamiento && tratamiento.nombre ? tratamiento.nombre : celda.childNodes[0].textContent) + "\",\"" + odontogramaEscape(tratamiento && tratamiento.alcance ? tratamiento.alcance : "pieza_dental") + "\",\"" + odontogramaEscape(tratamiento && tratamiento.modo_individualizacion ? tratamiento.modo_individualizacion : "cantidad_libre") + "\",\"" + odontogramaEscape(tratamiento && tratamiento.requiere_laboratorio ? tratamiento.requiere_laboratorio : 0) + "\")'>" + textoBoton + "</button>";
		celda.appendChild(div);
	});
}

function odontogramaAsignarItemPresupuesto(itemId, productoId, nombre, alcance, modoFallback, requiereFallback) {
	var estado = odontogramaEstados.presupuesto;
	if (!estado) { return; }
	odontogramaApi("obtenerAlcanceProducto", { producto_id: productoId, unidad_pieza_presupuesto: "0" }, function (ok, datos) {
		var producto = ok && datos.producto ? datos.producto : {
			cod_producto: productoId,
			nombre_producto: nombre,
			alcance_odontologico: alcance,
			modo_individualizacion: modoFallback || "cantidad_libre",
			requiere_laboratorio: requiereFallback == 1 ? 1 : 0
		};
		var modo = String(producto.modo_individualizacion || modoFallback || "cantidad_libre");
		var alcanceEfectivo = modo === "cantidad_libre"
			? odontogramaNormalizarAlcance(producto.alcance_odontologico || alcance)
			: odontogramaAlcancePorModoIndividualizacion(producto);
		var usaSeleccionMultiple = modo == "multipieza" || modo == "sector";
		estado.tratamientoPendiente = {
			presupuesto_item_id: itemId,
			producto_id: producto.cod_producto || productoId,
			nombre: producto.nombre_producto || nombre,
			alcance: alcanceEfectivo,
			requiere_laboratorio: producto.requiere_laboratorio == 1 ? 1 : 0,
			modo_individualizacion: modo
		};
		estado.tratamientoSeleccionado = estado.tratamientoPendiente;
		estado.ubicacionActual = null;
		estado.seleccionMultipleActiva = usaSeleccionMultiple;
		estado.piezasMultiples = [];
		estado.modo = "asignar";
		estado.pasoClinico = "tratamientos";
		estado.filtroVisual = "tratamientos";
		estado.mensajeFlash = usaSeleccionMultiple
			? "Asignando: " + nombre + ". Toca todas las piezas incluidas y confirma con OK."
			: "Asignando: " + nombre + ". Toca la ubicacion clinica correspondiente.";
		odontogramaRender("presupuesto");
	});
}

function odontogramaAsignarTratamientoFicha(detalleId, ventaId, productoId, nombre, alcance, modoFallback, requiereFallback, opciones) {
	if (modoFallback && typeof modoFallback === "object") {
		opciones = modoFallback;
		modoFallback = "";
		requiereFallback = 0;
	} else if (requiereFallback && typeof requiereFallback === "object") {
		opciones = requiereFallback;
		requiereFallback = 0;
	}
	opciones = opciones || {};

	function mostrarPestana() {
		if (opciones.abrirPestana === false) { return; }
		if (typeof cambiarTabFichaClinicaConsulta === "function") {
			cambiarTabFichaClinicaConsulta("odontograma", {
				recargarOdontograma: false,
				enfocar: opciones.enfocar !== false,
				demoraEnfoque: 40
			});
			return;
		}
		var contenedor = document.getElementById("odontogramaFichaClinica");
		var panel = contenedor && contenedor.closest ? contenedor.closest("[data-consulta-tab-panel='odontograma']") : null;
		if (panel) {
			panel.removeAttribute("hidden");
			panel.style.removeProperty("display");
			if (!panel.hasAttribute("tabindex")) { panel.setAttribute("tabindex", "-1"); }
			panel.focus();
		}
	}

	function informarError(mensaje, datosError) {
		var texto = mensaje || "No se pudo preparar la asignacion en el odontograma.";
		var contenedor = document.getElementById("odontogramaFichaClinica");
		if (contenedor && !odontogramaEstados.ficha) {
			contenedor.innerHTML = "<div class='odontograma-error'>" + odontogramaEscape(texto) + "</div>";
		}
		odontogramaCallbackOpcion(opciones, ["alError", "onError"], [texto, datosError || {}]);
	}

	function estadoCorrespondeFicha(estado) {
		if (!estado || !estado.base) { return false; }
		var actual = odontogramaDatosBaseFicha();
		if (String(estado.base.paciente_id || "") !== String(actual.paciente_id || "")) { return false; }
		if (String(estado.base.venta_id || "") !== String(actual.venta_id || "")) { return false; }
		return true;
	}

	function prepararAsignacion(estado) {
		if (!estado) {
			informarError("El odontograma de la ficha clinica no pudo inicializarse.");
			return;
		}
		odontogramaApi("obtenerAlcanceProducto", { producto_id: productoId, unidad_pieza_presupuesto: "0" }, function (ok, datos) {
			var estadoActual = odontogramaEstados.ficha;
			if (!estadoActual || !estadoCorrespondeFicha(estadoActual)) {
				informarError("El paciente cambio mientras se preparaba la ubicacion. Vuelva a intentarlo.");
				return;
			}
			var producto = ok && datos.producto ? datos.producto : {
				cod_producto: productoId,
				nombre_producto: nombre,
				alcance_odontologico: alcance,
				modo_individualizacion: modoFallback || "cantidad_libre",
				requiere_laboratorio: requiereFallback == 1 ? 1 : 0
			};
			var modo = String(producto.modo_individualizacion || modoFallback || "cantidad_libre");
			var alcanceEfectivo = modo === "cantidad_libre"
				? odontogramaNormalizarAlcance(producto.alcance_odontologico || alcance)
				: odontogramaAlcancePorModoIndividualizacion(producto);
			var usaSeleccionMultiple = modo == "multipieza" || modo == "sector";
			estadoActual.tratamientoPendiente = {
				detalle_venta_id: detalleId,
				venta_id: ventaId,
				producto_id: producto.cod_producto || productoId,
				nombre: producto.nombre_producto || nombre,
				alcance: alcanceEfectivo,
				requiere_laboratorio: producto.requiere_laboratorio == 1 ? 1 : 0,
				modo_individualizacion: modo
			};
			estadoActual.tratamientoSeleccionado = estadoActual.tratamientoPendiente;
			estadoActual.modo = "asignar";
			estadoActual.pasoClinico = "tratamientos";
			estadoActual.filtroVisual = "tratamientos";
			estadoActual.ubicacionActual = null;
			estadoActual.seleccionMultipleActiva = usaSeleccionMultiple;
			estadoActual.piezasMultiples = [];
			estadoActual.mensajeFlash = usaSeleccionMultiple
				? "Asignando: " + nombre + ". Toca todas las piezas incluidas y confirma con OK."
				: "Asignando: " + nombre + ". Toca la pieza, arcada o boca completa correspondiente.";
			odontogramaRender("ficha");
			mostrarPestana();
			odontogramaCallbackOpcion(opciones, ["alPreparar", "onReady"], [estadoActual, estadoActual.tratamientoPendiente]);
		});
	}

	mostrarPestana();
	if (estadoCorrespondeFicha(odontogramaEstados.ficha)) {
		/* Invalida una carga anterior que pudiera sobrescribir la seleccion pendiente. */
		odontogramaCargaFichaSecuencia++;
		prepararAsignacion(odontogramaEstados.ficha);
		return true;
	}
	return cargarOdontogramaFichaClinica({
		preservarTratamientoPendiente: true,
		alCargar: function (estado) { prepararAsignacion(estado); },
		alError: informarError
	});
}

function odontogramaSelectorRapidoLaboratorioPiezas(denticion) {
	var piezas = [];
	odontogramaFilas.forEach(function (fila) {
		if (fila.denticion !== denticion) { return; }
		fila.izquierda.concat(fila.derecha).forEach(function (pieza) {
			if (piezas.indexOf(pieza) < 0) { piezas.push(pieza); }
		});
	});
	return piezas;
}

function odontogramaSelectorRapidoLaboratorioMinimo(estado) {
	var modo = String(estado && estado.modo_individualizacion || "cantidad_libre");
	return modo === "multipieza" || modo === "sector" ? 2 : 1;
}

function odontogramaSelectorRapidoLaboratorioTextoModo(estado) {
	var minimo = odontogramaSelectorRapidoLaboratorioMinimo(estado);
	if (estado && estado.soloCapturar && estado.cantidadTrabajos > 1) {
		return "Selecciona una o varias piezas solo para el trabajo " + estado.trabajoActual
			+ " de " + estado.cantidadTrabajos + ". Los demas trabajos se completaran por separado.";
	}
	if (minimo > 1) {
		return "Selecciona al menos dos piezas. Todas quedaran dentro de un unico trabajo de laboratorio.";
	}
	return "Selecciona una o varias piezas. Se generara un unico trabajo de laboratorio para este tratamiento.";
}

function odontogramaSelectorRapidoLaboratorioAsegurar() {
	var modal = document.getElementById("odontogramaSelectorRapidoLaboratorio");
	if (modal) { return modal; }
	modal = document.createElement("div");
	modal.id = "odontogramaSelectorRapidoLaboratorio";
	modal.className = "odontograma-selector-rapido";
	modal.hidden = true;
	modal.setAttribute("aria-hidden", "true");
	modal.innerHTML = "<div class='odontograma-selector-rapido__dialog' role='dialog' aria-modal='true' aria-labelledby='odontogramaSelectorRapidoTitulo'>"
		+ "<div data-odontograma-selector-contenido></div>"
		+ "</div>";
	document.body.appendChild(modal);
	modal.addEventListener("click", function (event) {
		var accion = event.target.closest ? event.target.closest("[data-odontograma-selector-accion]") : null;
		if (!accion) { return; }
		var tipo = accion.getAttribute("data-odontograma-selector-accion");
		if (tipo === "cerrar") {
			if (odontogramaSelectorRapidoLaboratorioEstado
				&& odontogramaSelectorRapidoLaboratorioEstado.guardando) { return; }
			odontogramaCerrarSelectorRapidoLaboratorio(false);
			return;
		}
		if (!odontogramaSelectorRapidoLaboratorioEstado || odontogramaSelectorRapidoLaboratorioEstado.guardando) { return; }
		if (tipo === "denticion") {
			odontogramaSelectorRapidoLaboratorioEstado.denticion = accion.getAttribute("data-denticion") || "permanente";
			odontogramaRenderSelectorRapidoLaboratorio();
			return;
		}
		if (tipo === "pieza") {
			odontogramaAlternarPiezaSelectorRapidoLaboratorio(accion.getAttribute("data-pieza"));
			return;
		}
		if (tipo === "quitar") {
			odontogramaAlternarPiezaSelectorRapidoLaboratorio(accion.getAttribute("data-pieza"), true);
			return;
		}
		if (tipo === "revisar") {
			var minimo = odontogramaSelectorRapidoLaboratorioMinimo(odontogramaSelectorRapidoLaboratorioEstado);
			if (odontogramaSelectorRapidoLaboratorioEstado.piezas.length < minimo) {
				odontogramaSelectorRapidoLaboratorioEstado.error = minimo > 1
					? "Selecciona al menos dos piezas para continuar."
					: "Selecciona al menos una pieza para continuar.";
				odontogramaRenderSelectorRapidoLaboratorio();
				return;
			}
			odontogramaSelectorRapidoLaboratorioEstado.paso = 2;
			odontogramaSelectorRapidoLaboratorioEstado.error = "";
			odontogramaRenderSelectorRapidoLaboratorio();
			return;
		}
		if (tipo === "volver") {
			odontogramaSelectorRapidoLaboratorioEstado.paso = 1;
			odontogramaSelectorRapidoLaboratorioEstado.error = "";
			odontogramaRenderSelectorRapidoLaboratorio();
			return;
		}
		if (tipo === "guardar") { odontogramaGuardarSelectorRapidoLaboratorio(); }
	});
	modal.addEventListener("input", function (event) {
		if (!odontogramaSelectorRapidoLaboratorioEstado) { return; }
		if (event.target && event.target.matches("[data-odontograma-selector-motivo]")) {
			odontogramaSelectorRapidoLaboratorioEstado.motivo = event.target.value || "";
		}
	});
	return modal;
}

function odontogramaAlternarPiezaSelectorRapidoLaboratorio(pieza, quitar) {
	var estado = odontogramaSelectorRapidoLaboratorioEstado;
	pieza = String(pieza || "");
	if (!estado || !pieza) { return; }
	var indice = estado.piezas.indexOf(pieza);
	if (indice >= 0) {
		estado.piezas.splice(indice, 1);
	} else if (!quitar) {
		estado.piezas.push(pieza);
	}
	estado.error = "";
	odontogramaRenderSelectorRapidoLaboratorio();
}

function odontogramaSelectorRapidoLaboratorioPiezasOrdenadas(estado) {
	var orden = odontogramaSelectorRapidoLaboratorioPiezas("permanente")
		.concat(odontogramaSelectorRapidoLaboratorioPiezas("temporal"));
	return (estado.piezas || []).slice().sort(function (a, b) {
		return orden.indexOf(a) - orden.indexOf(b);
	});
}

function odontogramaRenderSelectorRapidoLaboratorio() {
	var estado = odontogramaSelectorRapidoLaboratorioEstado;
	var modal = document.getElementById("odontogramaSelectorRapidoLaboratorio");
	if (!estado || !modal) { return; }
	var contenido = modal.querySelector("[data-odontograma-selector-contenido]");
	var piezasSeleccionadas = odontogramaSelectorRapidoLaboratorioPiezasOrdenadas(estado);
	var minimo = odontogramaSelectorRapidoLaboratorioMinimo(estado);
	var paso = estado.paso === 2 ? 2 : 1;
	var tituloSelector = estado.soloCapturar && estado.cantidadTrabajos > 1
		? "Trabajo " + estado.trabajoActual + " de " + estado.cantidadTrabajos
		: "Designar piezas dentarias";
	var etiquetaSelector = estado.soloCapturar && estado.cantidadTrabajos > 1
		? "Regularizacion guiada" : "Ubicacion clinica";
	var html = "<header class='odontograma-selector-rapido__header'>"
		+ "<div><span class='odontograma-selector-rapido__eyebrow'>" + odontogramaEscape(etiquetaSelector) + "</span>"
		+ "<h2 id='odontogramaSelectorRapidoTitulo'>" + odontogramaEscape(tituloSelector) + "</h2>"
		+ "<p>" + odontogramaEscape(estado.nombre || "Tratamiento de laboratorio") + "</p></div>"
		+ "<button type='button' class='odontograma-selector-rapido__cerrar' data-odontograma-selector-accion='cerrar' aria-label='Cerrar' " + (estado.guardando ? "disabled" : "") + ">&times;</button>"
		+ "</header>"
		+ "<ol class='odontograma-selector-rapido__pasos' aria-label='Pasos para designar piezas'>"
		+ "<li class='" + (paso === 1 ? "is-active" : "is-complete") + "'><b>1</b><span>Seleccionar</span></li>"
		+ "<li class='" + (paso === 2 ? "is-active" : "") + "'><b>2</b><span>Revisar y guardar</span></li>"
		+ "</ol>";
	if (estado.cargando) {
		html += "<div class='odontograma-selector-rapido__loading' role='status'>Preparando selector de piezas...</div>";
	} else if (paso === 1) {
		html += "<main class='odontograma-selector-rapido__body'>"
			+ "<div class='odontograma-selector-rapido__instruccion'><b>Paso 1. Toca las piezas implicadas</b><span>" + odontogramaEscape(odontogramaSelectorRapidoLaboratorioTextoModo(estado)) + "</span></div>"
			+ "<div class='odontograma-selector-rapido__tabs' role='tablist'>"
			+ "<button type='button' role='tab' aria-selected='" + (estado.denticion === "permanente" ? "true" : "false") + "' class='" + (estado.denticion === "permanente" ? "is-active" : "") + "' data-odontograma-selector-accion='denticion' data-denticion='permanente'>Denticion permanente</button>"
			+ "<button type='button' role='tab' aria-selected='" + (estado.denticion === "temporal" ? "true" : "false") + "' class='" + (estado.denticion === "temporal" ? "is-active" : "") + "' data-odontograma-selector-accion='denticion' data-denticion='temporal'>Denticion temporal</button>"
			+ "</div>"
			+ odontogramaRenderPiezasSelectorRapidoLaboratorio(estado)
			+ odontogramaRenderResumenSelectorRapidoLaboratorio(piezasSeleccionadas, false)
			+ odontogramaRenderErrorSelectorRapidoLaboratorio(estado)
			+ "</main>"
			+ "<footer class='odontograma-selector-rapido__footer'><button type='button' class='btn-secundario' data-odontograma-selector-accion='cerrar'>Cancelar</button>"
			+ "<button type='button' class='btn-primario' data-odontograma-selector-accion='revisar' " + (piezasSeleccionadas.length >= minimo ? "" : "disabled") + ">Revisar seleccion <span aria-hidden='true'>&rarr;</span></button></footer>";
	} else {
		var textoConfirmacion = estado.soloCapturar && estado.cantidadTrabajos > 1
			? "Estas piezas quedaran reservadas exclusivamente para el trabajo " + estado.trabajoActual + " de " + estado.cantidadTrabajos + "."
			: "Estas piezas quedaran vinculadas al mismo tratamiento y al mismo trabajo de laboratorio.";
		var textoGuardar = estado.soloCapturar && estado.cantidadTrabajos > 1
			? (estado.trabajoActual < estado.cantidadTrabajos
				? "Guardar trabajo " + estado.trabajoActual + " y continuar"
				: "Guardar trabajo " + estado.trabajoActual + " y revisar")
			: "Guardar y preparar laboratorio";
		html += "<main class='odontograma-selector-rapido__body'>"
			+ "<div class='odontograma-selector-rapido__instruccion'><b>Paso 2. Confirma la ubicacion</b><span>" + odontogramaEscape(textoConfirmacion) + "</span></div>"
			+ odontogramaRenderResumenSelectorRapidoLaboratorio(piezasSeleccionadas, true)
			+ (estado.requiereMotivo
				? "<label class='odontograma-selector-rapido__motivo'><span>Motivo de modificacion</span><textarea rows='2' maxlength='500' data-odontograma-selector-motivo placeholder='Explica brevemente por que se modifica el odontograma'>" + odontogramaEscape(estado.motivo || "") + "</textarea></label>"
				: "")
			+ odontogramaRenderErrorSelectorRapidoLaboratorio(estado)
			+ "</main>"
			+ "<footer class='odontograma-selector-rapido__footer'><button type='button' class='btn-secundario' data-odontograma-selector-accion='volver' " + (estado.guardando ? "disabled" : "") + ">&larr; Volver</button>"
			+ "<button type='button' class='btn-primario' data-odontograma-selector-accion='guardar' " + (estado.guardando ? "disabled" : "") + ">" + (estado.guardando ? "Guardando..." : odontogramaEscape(textoGuardar)) + "</button></footer>";
	}
	contenido.innerHTML = html;
	var enfoque = paso === 2
		? contenido.querySelector("[data-odontograma-selector-accion='guardar']")
		: contenido.querySelector("[data-odontograma-selector-accion='pieza']");
	if (!estado.cargando && enfoque) { setTimeout(function () { enfoque.focus(); }, 0); }
}

function odontogramaRenderPiezasSelectorRapidoLaboratorio(estado) {
	var html = "<div class='odontograma-selector-rapido__odontograma'>";
	odontogramaFilas.forEach(function (fila) {
		if (fila.denticion !== estado.denticion) { return; }
		var piezas = fila.izquierda.concat(fila.derecha);
		html += "<div class='odontograma-selector-rapido__fila" + (fila.clase.indexOf("inferior") >= 0 ? " is-inferior" : "") + "'>";
		piezas.forEach(function (pieza, indice) {
			var seleccionada = estado.piezas.indexOf(pieza) >= 0;
			html += "<button type='button' class='odontograma-selector-rapido__pieza " + (seleccionada ? "is-selected" : "") + (indice === fila.izquierda.length ? " is-quadrant-start" : "") + "' data-odontograma-selector-accion='pieza' data-pieza='" + pieza + "' aria-pressed='" + (seleccionada ? "true" : "false") + "' aria-label='Pieza " + pieza + (seleccionada ? ", seleccionada" : "") + "'>"
				+ odontogramaIconoUbicacion("pieza", pieza)
				+ "<span>" + pieza + "</span><i aria-hidden='true'>&#10003;</i></button>";
		});
		html += "</div>";
	});
	return html + "</div>";
}

function odontogramaRenderResumenSelectorRapidoLaboratorio(piezas, revision) {
	var html = "<section class='odontograma-selector-rapido__seleccion " + (revision ? "is-review" : "") + "'>"
		+ "<div><b>" + piezas.length + " pieza" + (piezas.length === 1 ? "" : "s") + " seleccionada" + (piezas.length === 1 ? "" : "s") + "</b>"
		+ "<span>" + (piezas.length ? "Puedes quitar una pieza antes de guardar." : "Todavia no seleccionaste piezas.") + "</span></div>"
		+ "<div class='odontograma-selector-rapido__chips'>";
	piezas.forEach(function (pieza) {
		html += "<button type='button' data-odontograma-selector-accion='quitar' data-pieza='" + pieza + "' aria-label='Quitar pieza " + pieza + "'>Pieza " + pieza + " <span aria-hidden='true'>&times;</span></button>";
	});
	return html + "</div></section>";
}

function odontogramaRenderErrorSelectorRapidoLaboratorio(estado) {
	return estado.error
		? "<div class='odontograma-selector-rapido__error' role='alert'>" + odontogramaEscape(estado.error) + "</div>"
		: "";
}

function odontogramaAbrirSelectorRapidoLaboratorio(detalleId, ventaId, productoId, nombre, alcance, modoFallback, requiereFallback, opciones) {
	opciones = opciones || {};
	var modal = odontogramaSelectorRapidoLaboratorioAsegurar();
	odontogramaSelectorRapidoLaboratorioEstado = {
		detalle_id: String(detalleId || ""),
		venta_id: String(ventaId || ""),
		producto_id: String(productoId || ""),
		nombre: nombre || "Tratamiento de laboratorio",
		alcance: alcance || "pieza_dental",
		modo_individualizacion: modoFallback || "cantidad_libre",
		requiere_laboratorio: requiereFallback == 1 ? 1 : 0,
		denticion: "permanente",
		piezas: Array.isArray(opciones.seleccionInicial) ? opciones.seleccionInicial.slice() : [],
		paso: 1,
		cargando: true,
		guardando: false,
		requiereMotivo: false,
		motivo: "",
		error: "",
		soloCapturar: opciones.soloCapturar === true,
		trabajoActual: parseInt(opciones.trabajoActual, 10) || 1,
		cantidadTrabajos: parseInt(opciones.cantidadTrabajos, 10) || 1,
		opciones: opciones,
		elementoAnterior: document.activeElement
	};
	modal.hidden = false;
	modal.setAttribute("aria-hidden", "false");
	document.body.classList.add("odontograma-selector-rapido-abierto");
	odontogramaRenderSelectorRapidoLaboratorio();
	odontogramaApi("obtenerAlcanceProducto", { producto_id: productoId, unidad_pieza_presupuesto: "0" }, function (ok, datos) {
		var estado = odontogramaSelectorRapidoLaboratorioEstado;
		if (!estado || estado.detalle_id !== String(detalleId || "")) { return; }
		if (ok && datos.producto) {
			estado.producto_id = String(datos.producto.cod_producto || estado.producto_id);
			estado.nombre = datos.producto.nombre_producto || estado.nombre;
			estado.alcance = datos.producto.alcance_odontologico || estado.alcance;
			estado.modo_individualizacion = datos.producto.modo_individualizacion || estado.modo_individualizacion;
			estado.requiere_laboratorio = datos.producto.requiere_laboratorio == 1 ? 1 : estado.requiere_laboratorio;
		} else if (!estado.producto_id) {
			estado.error = datos.mensaje || "No se pudo preparar el tratamiento seleccionado.";
		}
		estado.cargando = false;
		odontogramaRenderSelectorRapidoLaboratorio();
	});
	return true;
}

function odontogramaCerrarSelectorRapidoLaboratorio(guardado) {
	var estado = odontogramaSelectorRapidoLaboratorioEstado;
	var modal = document.getElementById("odontogramaSelectorRapidoLaboratorio");
	if (modal) {
		modal.hidden = true;
		modal.setAttribute("aria-hidden", "true");
	}
	document.body.classList.remove("odontograma-selector-rapido-abierto");
	odontogramaSelectorRapidoLaboratorioEstado = null;
	if (estado && !guardado) {
		odontogramaCallbackOpcion(estado.opciones, ["alCancelar", "onCancel"], []);
	}
	if (estado && estado.elementoAnterior && document.body.contains(estado.elementoAnterior)) {
		setTimeout(function () { estado.elementoAnterior.focus(); }, 0);
	}
}

function odontogramaGuardarSelectorRapidoLaboratorio() {
	var estado = odontogramaSelectorRapidoLaboratorioEstado;
	if (!estado || estado.guardando) { return; }
	var piezas = odontogramaSelectorRapidoLaboratorioPiezasOrdenadas(estado);
	var minimo = odontogramaSelectorRapidoLaboratorioMinimo(estado);
	if (piezas.length < minimo) {
		estado.error = minimo > 1 ? "Selecciona al menos dos piezas." : "Selecciona al menos una pieza.";
		estado.paso = 1;
		odontogramaRenderSelectorRapidoLaboratorio();
		return;
	}
	if (estado.requiereMotivo && !String(estado.motivo || "").trim()) {
		estado.error = "Indica el motivo para guardar esta modificacion.";
		odontogramaRenderSelectorRapidoLaboratorio();
		return;
	}
	var denticiones = [];
	piezas.forEach(function (pieza) {
		var denticion = odontogramaDenticionPieza(pieza);
		if (denticiones.indexOf(denticion) < 0) { denticiones.push(denticion); }
	});
	var ubicacion = piezas.length === 1
		? { pieza: piezas[0], denticion: denticiones[0] }
		: { piezas: piezas, piezas_json: JSON.stringify(piezas), denticion: denticiones.length === 1 ? denticiones[0] : "mixta" };
	ubicacion.piezas = piezas.slice();
	ubicacion.alcance = piezas.length > 1 ? "piezas_multiples" : "pieza_dental";
	ubicacion.numero_unidad = estado.trabajoActual || 1;
	if (estado.soloCapturar) {
		var opcionesCaptura = estado.opciones;
		odontogramaCerrarSelectorRapidoLaboratorio(true);
		odontogramaCallbackOpcion(opcionesCaptura, ["alGuardar", "onSave"], [ubicacion, {
			capturada: true,
			numero_unidad: ubicacion.numero_unidad,
			cantidad_trabajos: estado.cantidadTrabajos || 1
		}]);
		return;
	}
	var payload = {
		detalle_venta_id: estado.detalle_id,
		venta_id: estado.venta_id,
		producto_id: estado.producto_id,
		nombre_tratamiento: estado.nombre,
		alcance_odontologico: piezas.length > 1 ? "piezas_multiples" : "pieza_dental",
		pieza: ubicacion.pieza || "",
		piezas_json: ubicacion.piezas_json || "",
		denticion: ubicacion.denticion,
		selector_rapido_laboratorio: "1",
		origen: "ficha_clinica",
		motivo: String(estado.motivo || "").trim()
	};
	if (typeof cod_clienteConsulta !== "undefined") { payload.paciente_id = cod_clienteConsulta || ""; }
	estado.guardando = true;
	estado.error = "";
	odontogramaRenderSelectorRapidoLaboratorio();
	odontogramaApi("guardarLinkTratamientoOdontograma", payload, function (ok, datos) {
		var estadoActual = odontogramaSelectorRapidoLaboratorioEstado;
		if (!estadoActual || estadoActual.detalle_id !== estado.detalle_id) { return; }
		if (!ok && (datos["1"] === "requiere_motivo" || datos[1] === "requiere_motivo")) {
			estadoActual.guardando = false;
			estadoActual.requiereMotivo = true;
			estadoActual.error = datos.mensaje || "Indica el motivo de la modificacion.";
			odontogramaRenderSelectorRapidoLaboratorio();
			return;
		}
		if (!ok) {
			estadoActual.guardando = false;
			estadoActual.error = datos.mensaje || "No se pudo guardar la ubicacion. Revisa los datos y vuelve a intentar.";
			odontogramaRenderSelectorRapidoLaboratorio();
			return;
		}
		var opciones = estadoActual.opciones;
		odontogramaActualizarLaboratorioTrasUbicacion(estadoActual.detalle_id, ubicacion);
		odontogramaCerrarSelectorRapidoLaboratorio(true);
		odontogramaCallbackOpcion(opciones, ["alGuardar", "onSave"], [ubicacion, datos]);
	});
}

document.addEventListener("keydown", function (event) {
	if (event.key === "Escape" && odontogramaSelectorRapidoLaboratorioEstado
		&& !odontogramaSelectorRapidoLaboratorioEstado.guardando) {
		event.preventDefault();
		odontogramaCerrarSelectorRapidoLaboratorio(false);
	}
});

function odontogramaInvalidarContextoLaboratorioDetalle(detalleId) {
	detalleId = String(detalleId || "");
	if (!detalleId) { return ""; }
	if (typeof tratamientoLaboratorioClinicoCache !== "undefined") {
		delete tratamientoLaboratorioClinicoCache[detalleId];
	}
	if (typeof tratamientoLaboratorioClinicoEstado !== "undefined") {
		var contextoActual = tratamientoLaboratorioClinicoEstado.contexto || {};
		var detalleActual = contextoActual.detalle || {};
		var idActual = String(detalleActual.cod_detalle_venta || contextoActual.cod_detalle_venta
			|| tratamientoLaboratorioClinicoEstado.detalleSolicitado || "");
		if (idActual === detalleId) {
			tratamientoLaboratorioClinicoEstado.solicitudSecuencia++;
			tratamientoLaboratorioClinicoEstado.contexto = null;
			tratamientoLaboratorioClinicoEstado.detalleSolicitado = "";
			tratamientoLaboratorioClinicoEstado.error = "";
			tratamientoLaboratorioClinicoEstado.mostrarPanel = false;
		}
	}
	return detalleId;
}

function odontogramaActualizarLaboratorioTrasUbicacion(detalleId, ubicacion) {
	detalleId = odontogramaInvalidarContextoLaboratorioDetalle(detalleId);
	if (!detalleId) { return; }
	Array.prototype.forEach.call(document.querySelectorAll("[data-tratamiento-laboratorio='1'][data-detalle-tratamiento]"), function (tarjeta) {
		if (String(tarjeta.getAttribute("data-detalle-tratamiento") || "") !== detalleId) { return; }
		tarjeta.setAttribute("data-laboratorio-ubicacion-falta", "0");
		var etiqueta = tarjeta.querySelector("[data-tratamiento-laboratorio-accion-texto]");
		var resumen = tarjeta.querySelector("[data-tratamiento-laboratorio-resumen]");
		var boton = tarjeta.querySelector("[data-tratamiento-laboratorio-accion]");
		var ubicacionAnterior = tarjeta.querySelector(".tratamiento-ubicacion-visual");
		if (ubicacionAnterior && ubicacion && typeof odontogramaRenderUbicacionVisual === "function") {
			var contenedorTemporal = document.createElement("div");
			contenedorTemporal.innerHTML = odontogramaRenderUbicacionVisual("ficha", ubicacion, {
				falta: false,
				alcance: tarjeta.getAttribute("data-tratamiento-alcance") || ""
			});
			if (contenedorTemporal.firstElementChild && ubicacionAnterior.parentNode) {
				ubicacionAnterior.parentNode.replaceChild(contenedorTemporal.firstElementChild, ubicacionAnterior);
			}
		}
		if (etiqueta) { etiqueta.textContent = "Preparar trabajo de laboratorio"; }
		if (boton) { boton.disabled = false; }
		if (resumen) {
			resumen.textContent = "Ubicacion guardada";
			resumen.hidden = false;
			resumen.classList.remove("is-readonly", "is-historical");
		}
	});
	if (typeof tratamientoProgresoActualConsulta !== "undefined"
		&& tratamientoProgresoActualConsulta.laboratorioDatos
		&& String(tratamientoProgresoActualConsulta.laboratorioDatos.cod_detalle_venta || "") === detalleId) {
		tratamientoProgresoActualConsulta.laboratorioDatos.ubicacion_falta = false;
	}
}

function odontogramaActualizarLaboratorioTrasQuitarUbicacion(detalleId) {
	detalleId = odontogramaInvalidarContextoLaboratorioDetalle(detalleId);
	if (!detalleId) { return; }
	Array.prototype.forEach.call(document.querySelectorAll("[data-tratamiento-laboratorio='1'][data-detalle-tratamiento]"), function (tarjeta) {
		if (String(tarjeta.getAttribute("data-detalle-tratamiento") || "") !== detalleId) { return; }
		tarjeta.setAttribute("data-laboratorio-ubicacion-falta", "1");
		var etiqueta = tarjeta.querySelector("[data-tratamiento-laboratorio-accion-texto]");
		var resumen = tarjeta.querySelector("[data-tratamiento-laboratorio-resumen]");
		var boton = tarjeta.querySelector("[data-tratamiento-laboratorio-accion]");
		var ubicacionAnterior = tarjeta.querySelector(".tratamiento-ubicacion-visual");
		if (ubicacionAnterior && typeof odontogramaRenderUbicacionVisual === "function") {
			var contenedorTemporal = document.createElement("div");
			contenedorTemporal.innerHTML = odontogramaRenderUbicacionVisual("ficha", null, {
				falta: true,
				alcance: tarjeta.getAttribute("data-tratamiento-alcance") || ""
			});
			if (contenedorTemporal.firstElementChild && ubicacionAnterior.parentNode) {
				ubicacionAnterior.parentNode.replaceChild(contenedorTemporal.firstElementChild, ubicacionAnterior);
			}
		}
		if (etiqueta) { etiqueta.textContent = "Asignar ubicacion para iniciar"; }
		if (boton) { boton.disabled = false; }
		if (resumen) {
			resumen.textContent = "Ubicacion clinica pendiente";
			resumen.hidden = false;
			resumen.classList.remove("is-readonly", "is-historical", "is-regularization");
		}
	});
	if (typeof tratamientoProgresoActualConsulta !== "undefined"
		&& tratamientoProgresoActualConsulta.laboratorioDatos
		&& String(tratamientoProgresoActualConsulta.laboratorioDatos.cod_detalle_venta || "") === detalleId) {
		tratamientoProgresoActualConsulta.laboratorioDatos.ubicacion_falta = true;
	}
}

function odontogramaGuardarLink(contexto, tratamiento, ubicacion, motivo) {
	var estado = odontogramaEstados[contexto];
	if (!estado || !tratamiento || !ubicacion) { return; }
	var esMultiple = odontogramaPiezasUbicacion(ubicacion).length > 0;
	var alcanceUbicacion = esMultiple
		? "piezas_multiples"
		: (contexto == "presupuesto" ? odontogramaAlcanceUbicacionPresupuesto(ubicacion) : "");
	var esGeneral = ["boca_completa", "arcada", "cuadrante"].indexOf(alcanceUbicacion) >= 0;
	if (ubicacion.pieza && odontogramaPiezaMarcadaAusente(estado, ubicacion.pieza) && !tratamiento._confirmoPiezaAusente) {
		if (!confirm("La pieza " + ubicacion.pieza + " esta marcada como ausente/inexistente en Situacion actual. Revise si corresponde asignar " + (tratamiento.nombre || "este tratamiento") + ". Desea continuar igual?")) {
			estado.mensajeFlash = "Asignacion cancelada para revisar la pieza ausente.";
			odontogramaRender(contexto);
			return;
		}
		tratamiento._confirmoPiezaAusente = true;
	}
	var payload = Object.assign({}, estado.base, ubicacion, {
		producto_id: tratamiento.producto_id || "",
		nombre_tratamiento: tratamiento.nombre || "",
		alcance_odontologico: (esMultiple || esGeneral) ? alcanceUbicacion : (tratamiento.alcance || "pieza_dental"),
		unidad_pieza_presupuesto: (contexto == "presupuesto" && !esMultiple && !esGeneral) ? "1" : "0",
		origen: contexto == "presupuesto" ? "presupuesto" : "ficha_clinica",
		motivo: motivo || ""
	});
	if (tratamiento.detalle_venta_id) {
		payload.detalle_venta_id = tratamiento.detalle_venta_id;
		payload.venta_id = tratamiento.venta_id || estado.base.venta_id;
	}
	if (tratamiento.presupuesto_item_id) {
		payload.presupuesto_item_id = tratamiento.presupuesto_item_id;
		payload.presupuesto_id = idabmPresupuesto || estado.base.presupuesto_id;
	}
	odontogramaApi("guardarLinkTratamientoOdontograma", payload, function (ok, datos) {
		if (!ok && datos["1"] == "requiere_motivo") {
			var motivoNuevo = prompt(datos.mensaje || "Ingrese motivo de modificacion:");
			if (motivoNuevo) {
				odontogramaGuardarLink(contexto, tratamiento, ubicacion, motivoNuevo);
			} else {
				odontogramaRefrescar(contexto);
			}
			return;
		}
		if (!ok) {
			ver_vetana_informativa("No se pudo guardar ubicacion", datos.mensaje || "", "error");
			odontogramaRefrescar(contexto);
			return;
		}
		if (tratamiento.presupuesto_item_id) {
			odontogramaActualizarFilaPresupuesto(tratamiento.presupuesto_item_id, datos.ubicacion_texto, false, tratamiento, ubicacion);
		}
		estado.tratamientoPendiente = null;
		estado.tratamientoSeleccionado = null;
		estado.ubicacionActual = null;
		estado.seleccionMultipleActiva = false;
		estado.piezasMultiples = [];
		estado.modo = "explorar";
		estado.mensajeFlash = "Ubicacion guardada correctamente. Podes deshacer si hubo un error.";
		if (contexto == "ficha" && tratamiento.detalle_venta_id) {
			odontogramaActualizarLaboratorioTrasUbicacion(tratamiento.detalle_venta_id, ubicacion);
		}
		odontogramaRefrescar(contexto);
	});
}

function odontogramaGuardarMarcaRapida(contexto, tipo, estadoMarca, color, motivo) {
	var estado = odontogramaEstados[contexto];
	if (!estado || !estado.piezaSeleccionada) { return; }
	var usarSuperficies = ["caries", "obturacion"].indexOf(tipo) >= 0
		&& estado.ubicacionActual
		&& String(estado.ubicacionActual.pieza || "") == String(estado.piezaSeleccionada)
		&& odontogramaSuperficiesUbicacion(estado.ubicacionActual).length;
	var superficiesSeleccionadas = usarSuperficies ? odontogramaSuperficiesUbicacion(estado.ubicacionActual) : [];
	if (superficiesSeleccionadas.length > 1) {
		odontogramaGuardarMarcaRapidaSuperficies(contexto, tipo, estadoMarca, color, superficiesSeleccionadas, motivo, 0);
		return;
	}
	var payload = Object.assign({}, estado.base, {
		pieza: estado.piezaSeleccionada,
		tipo_marca: tipo,
		estado_marca: estadoMarca,
		color: color,
		motivo: motivo || ""
	});
	if (superficiesSeleccionadas.length == 1) {
		payload.superficie = superficiesSeleccionadas[0];
	}
	odontogramaApi("guardarMarcaOdontograma", payload, function (ok, datos) {
		if (!ok && datos["1"] == "requiere_motivo") {
			var motivoNuevo = prompt(datos.mensaje || "Ingrese motivo de modificacion:");
			if (motivoNuevo) {
				odontogramaGuardarMarcaRapida(contexto, tipo, estadoMarca, color, motivoNuevo);
			}
			return;
		}
		if (ok) {
			estado.mensajeFlash = "Marca guardada correctamente.";
			odontogramaRefrescar(contexto);
		}
	});
}

function odontogramaGuardarMarcaRapidaSuperficies(contexto, tipo, estadoMarca, color, superficies, motivo, indice) {
	var estado = odontogramaEstados[contexto];
	if (!estado || !estado.piezaSeleccionada) { return; }
	indice = indice || 0;
	if (indice >= superficies.length) {
		estado.mensajeFlash = "Marca guardada en " + superficies.length + " superficies.";
		odontogramaRefrescar(contexto);
		return;
	}
	var payload = Object.assign({}, estado.base, {
		pieza: estado.piezaSeleccionada,
		tipo_marca: tipo,
		estado_marca: estadoMarca,
		color: color,
		superficie: superficies[indice],
		motivo: motivo || ""
	});
	odontogramaApi("guardarMarcaOdontograma", payload, function (ok, datos) {
		if (!ok && datos["1"] == "requiere_motivo") {
			var motivoNuevo = prompt(datos.mensaje || "Ingrese motivo de modificacion:");
			if (motivoNuevo) {
				odontogramaGuardarMarcaRapidaSuperficies(contexto, tipo, estadoMarca, color, superficies, motivoNuevo, indice);
			}
			return;
		}
		if (!ok) {
			ver_vetana_informativa("No se pudo guardar marca", datos.mensaje || "", "error");
			odontogramaRefrescar(contexto);
			return;
		}
		odontogramaGuardarMarcaRapidaSuperficies(contexto, tipo, estadoMarca, color, superficies, motivo, indice + 1);
	});
}

function odontogramaEliminarMarca(contexto, marcaId, motivo) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	var payload = Object.assign({}, estado.base, { marca_id: marcaId, motivo: motivo || "" });
	odontogramaApi("eliminarMarcaOdontograma", payload, function (ok, datos) {
		if (!ok && datos["1"] == "requiere_motivo") {
			var motivoNuevo = prompt(datos.mensaje || "Ingrese motivo de modificacion:");
			if (motivoNuevo) { odontogramaEliminarMarca(contexto, marcaId, motivoNuevo); }
			return;
		}
		if (ok) {
			estado.mensajeFlash = "Marca quitada correctamente.";
			odontogramaRefrescar(contexto);
		}
	});
}

function odontogramaEliminarLink(contexto, linkId, detalleId, motivo) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	var payload = Object.assign({}, estado.base, {
		link_id: linkId,
		detalle_venta_id: detalleId || "",
		motivo: motivo || ""
	});
	odontogramaApi("eliminarLinkTratamientoOdontograma", payload, function (ok, datos) {
		if (!ok && datos["1"] == "requiere_motivo") {
			var motivoNuevo = prompt(datos.mensaje || "Ingrese motivo de modificacion:");
			if (motivoNuevo) { odontogramaEliminarLink(contexto, linkId, detalleId, motivoNuevo); }
			return;
		}
		if (!ok) {
			ver_vetana_informativa("No se pudo quitar la ubicacion", datos.mensaje || "Revise sus permisos y el estado del trabajo de laboratorio.", "error");
			return;
		}
		if (ok) {
			estado.mensajeFlash = "Ubicacion quitada correctamente. El tratamiento y la venta se mantienen.";
			odontogramaActualizarLaboratorioTrasQuitarUbicacion(detalleId);
			odontogramaRefrescar(contexto);
		}
	});
}

function odontogramaConvalidar(contexto) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	var progreso = odontogramaResumenProgreso(estado);
	if (progreso.pendientes > 0 && !confirm("Hay " + progreso.pendientes + " tratamientos sin ubicacion. Desea convalidar igualmente?")) {
		return;
	}
	odontogramaApi("convalidarOdontograma", estado.base, function (ok, datos) {
		if (ok) {
			ver_vetana_informativa("Odontograma convalidado", "La version clinica quedo registrada.", "info");
			odontogramaRefrescar(contexto);
		} else {
			ver_vetana_informativa("No se pudo convalidar", datos.mensaje || "", "error");
		}
	});
}

function odontogramaDeshacer(contexto) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	odontogramaApi("deshacerUltimaAccionOdontograma", estado.base, function (ok, datos) {
		if (ok) {
			estado.mensajeFlash = "Ultima accion deshecha correctamente.";
			if (datos.detalle_venta_laboratorio_id) {
				odontogramaActualizarLaboratorioTrasQuitarUbicacion(datos.detalle_venta_laboratorio_id);
			}
			odontogramaRefrescar(contexto);
		} else {
			ver_vetana_informativa("No se pudo deshacer", datos.mensaje || "No hay una accion disponible para deshacer.", "error");
		}
	});
}

function odontogramaLimpiarSeleccion(contexto) {
	var estado = odontogramaEstados[contexto];
	if (!estado) { return; }
	estado.tratamientoPendiente = null;
	estado.ubicacionActual = null;
	estado.piezaSeleccionada = "";
	estado.seleccionMultipleActiva = false;
	estado.piezasMultiples = [];
	if (contexto == "presupuesto") {
		estado.tratamientoSeleccionado = null;
		estado.ubicacionAutomaticaPendiente = null;
		estado.agregandoAutomatico = false;
		estado.modo = "asignar";
		estado.pasoClinico = "tratamientos";
		estado.filtroVisual = "tratamientos";
		if (typeof presupuestoDocLimpiarSeleccionTratamiento == "function") {
			presupuestoDocLimpiarSeleccionTratamiento("Busque o ingrese un nuevo tratamiento.", "info");
		}
	}
	odontogramaRender(contexto);
}

function odontogramaMostrarGuia(contexto) {
	var texto = "1. Primero marca la situacion actual del paciente.<br>"
		+ "2. Toca una pieza para registrar caries, ausencias, obturaciones o protesis existentes.<br>"
		+ "3. Ficha de pieza seleccionada: al tocar una pieza, el panel derecho muestra numero grande, vista superior, vista de perfil, tratamientos, hallazgos y superficies.<br>"
		+ "4. Iconos de ubicacion: diente = pieza, arcada = superior/inferior, boca = todos los dientes, signo + = pendiente.<br>"
		+ "5. Luego selecciona o agrega tratamientos y vinculalos a su ubicacion.<br>"
		+ "6. Si requiere superficie, toca la superficie del diente o usa la pieza ampliada del panel.<br>"
		+ "7. Si te equivocas, usa Deshacer, Editar ubicacion o Quitar ubicacion.<br>"
		+ "8. Al finalizar, revisa pendientes y convalida. Todo cambio queda en historial.";
	ver_vetana_informativa("Como completar el odontograma", texto, "info");
}

function odontogramaMigrarPresupuestoAVenta(presupuestoId, ventaId) {
	if (!presupuestoId || !ventaId) { return; }
	var base = odontogramaDatosBasePresupuesto();
	base.presupuesto_id = presupuestoId;
	base.venta_id = ventaId;
	odontogramaApi("migrarLinksPresupuestoAVenta", base, function (ok, datos) {
		if (!ok) {
			ver_vetana_informativa(
				"Venta guardada; ubicaciones pendientes",
				datos.mensaje || "Un profesional autorizado debe vincular las ubicaciones de laboratorio desde la ficha clinica.",
				"info"
			);
		}
	});
}
