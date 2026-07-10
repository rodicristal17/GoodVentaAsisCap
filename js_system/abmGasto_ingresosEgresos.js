function mostrarItems(id_elemento) {
	switch (id_elemento) {
		case 'zonaGastosGastosOperativos':
        	if(controlacceso("VERGASTOSZONAOPERATIVOS","accion")==false){return;}
			break;
		case 'zonaGastosCostosDirectos':
			if(controlacceso("VERGASTOSZONACOSTOSDIRECTOS","accion")==false){return;}
			break;
		case 'zonaGastosIngreso':
			if(controlacceso("VERGASTOSZONAINGRESOS","accion")==false){return;}
			break;
	}

	const elemento= document.getElementById(id_elemento);
	
	// Despliega o oculta segun el estado actual
	bootstrap.Collapse.getOrCreateInstance(elemento).toggle();
}

function verCerrarAbmGasto(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmGastos").style.display==""){
		conciliarEgresoUenoMostrarModal(false);
		document.getElementById("divMinimizadoEgresoIngreso").style.display="none"
		document.getElementById("tdEfectoAbmGasto").className="magictime vanishOut"
		$("div[id=divAbmGastos]").fadeOut(500);	
		limpiarcamposGasto()
		limpiarcamposbuscadoregresoingreso()
	}else{	
        if(controlacceso("VERLISTADOEGRESOINGRESO","accion")==false){return;}
		checkfiltroshistorialegresoingreso(1);
		actualizarEncabezadoFlujoGasto();
        buscaroptionMotivoEgresoIngreso();
		buscarabmGasto();
		buscarProyectosVistaSelecc();
		document.getElementById("divAbmGastos").style.display=""
        document.getElementById("tdEfectoAbmGasto").className="magictime slideDownReturn"
	}
}
function limpiarcamposbuscadoregresoingreso(){
	document.getElementById("inptBuscarIngresoEgreso1").value=""
	document.getElementById("inptBuscarIngresoEgreso2").value=""
	document.getElementById("inptBuscarGastoF1").value=""
	document.getElementById("inptBuscarGastoF2").value=""
	document.getElementById("inptRegistroNroGastos").value=""
	document.getElementById("inptTotalGasto").value=""
	document.getElementById("inptRegistroSeleccGasto").value=""
	document.getElementById("table_abm_gasto_imprimir").innerHTML="";
	actualizarEncabezadoFlujoGasto();
	actualizarResumenNetoFlujoGasto(0, 0, 0);
	actualizarComposicionFlujoGasto(null);
}
function minimizarventanaingresoegreso(){
	conciliarEgresoUenoMostrarModal(false);
	document.getElementById("divMinimizadoEgresoIngreso").style.display=""
    document.getElementById("tdEfectoAbmGasto").className="magictime slideDown"
	$("div[id=divAbmGastos]").fadeOut(500);
}

var movimientoFinancieroContextoActual = {
	modo: "general"
};

function normalizarTipoMovimientoFinanciero(tipoMovimiento) {
	var tipo = ((tipoMovimiento || "") + "").toLowerCase();
	return tipo == "ingreso" ? "Ingreso" : "Egreso";
}

function obtenerCategoriaConceptoMovimientoFinanciero() {
	if (movimientoFinancieroContextoActual && movimientoFinancieroContextoActual.modo == "crear") {
		var categoria = ((movimientoFinancieroContextoActual.categoriaCodigo || "") + "").toLowerCase();
		if (categoria == "ingreso" || categoria == "directo" || categoria == "operativo") {
			return categoria;
		}
	}
	return "";
}

function obtenerFechaDefaultMovimientoFinanciero() {
	var hoy = obtenerFechaLocalISOFlujoGasto();
	var fechaInicio = document.getElementById("inptBuscarGastoF1") ? document.getElementById("inptBuscarGastoF1").value : "";
	var fechaFin = document.getElementById("inptBuscarGastoF2") ? document.getElementById("inptBuscarGastoF2").value : "";
	if (fechaInicio && fechaFin && hoy >= fechaInicio && hoy <= fechaFin) {
		return hoy;
	}
	return fechaInicio || fechaFin || hoy;
}

function configurarModalMovimientoFinanciero(contexto) {
	contexto = contexto || {};
	movimientoFinancieroContextoActual = contexto;
	var modal = document.getElementById("divAbmGasto2");
	var titulo = document.getElementById("tituloMovimientoFinanciero");
	var chip = document.getElementById("chipContextoMovimientoFinanciero");
	if (modal) {
		modal.classList.remove("movimiento-financiero-modal--ingreso", "movimiento-financiero-modal--egreso", "movimiento-financiero-modal--editar");
	}
	if (!titulo) { return; }
	if (contexto.modo == "editar") {
		titulo.textContent = "Editar movimiento financiero";
		if (modal) { modal.classList.add("movimiento-financiero-modal--editar"); }
		if (chip) {
			chip.textContent = "Editando registro existente";
			chip.style.display = "";
		}
		return;
	}
	if (contexto.modo == "crear") {
		var tipo = normalizarTipoMovimientoFinanciero(contexto.tipoMovimiento);
		var conceptoNombre = contexto.conceptoNombre || "";
		if (contexto.esNuevoProyecto && contexto.proyectoNombre) {
			titulo.textContent = "Nuevo proyecto - " + contexto.proyectoNombre;
		} else {
			titulo.textContent = (tipo == "Ingreso" ? "Nuevo ingreso" : "Nuevo egreso") + (conceptoNombre ? " - " + conceptoNombre : "");
		}
		if (modal) {
			modal.classList.add(tipo == "Ingreso" ? "movimiento-financiero-modal--ingreso" : "movimiento-financiero-modal--egreso");
		}
		if (chip) {
			chip.textContent = "Impactar\u00e1 en: " + (contexto.categoriaFlujo || "Flujo financiero") + (conceptoNombre ? " > " + conceptoNombre : "") + (contexto.proyectoNombre ? " | Proyecto: " + contexto.proyectoNombre : "");
			chip.style.display = "";
		}
		return;
	}
	titulo.textContent = "Movimiento financiero";
	if (chip) {
		chip.textContent = "";
		chip.style.display = "none";
	}
}

function asegurarOpcionConceptoMovimientoFinanciero(codConcepto, conceptoNombre) {
	var selectConcepto = document.getElementById("inptMotivoMisGastos");
	if (!selectConcepto || !codConcepto) { return; }
	for (var i = 0; i < selectConcepto.options.length; i++) {
		if (selectConcepto.options[i].value == codConcepto) {
			return;
		}
	}
	var opcion = document.createElement("option");
	opcion.value = codConcepto;
	opcion.text = conceptoNombre || codConcepto;
	opcion.setAttribute("data-contextual-temporal", "true");
	selectConcepto.appendChild(opcion);
}

function enfocarPagoYPlanificacionMovimientoFinanciero() {
	var monto = document.getElementById("inptMontoGasto");
	if (!monto) { return; }
	var panel = monto.closest ? monto.closest(".movimiento-financiero-panel") : null;
	if (panel && panel.scrollIntoView) {
		panel.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
	}
	setTimeout(function () {
		monto.focus();
		if (monto.select) { monto.select(); }
	}, 250);
}

function aplicarContextoCrearMovimientoFinanciero(contexto) {
	var tipo = normalizarTipoMovimientoFinanciero(contexto.tipoMovimiento);
	var codConcepto = contexto.conceptoId || "";
	var conceptoNombre = contexto.conceptoNombre || "";
	var localFiltrado = contexto.localId || (document.getElementById("inptlocalMisGastosBusca") ? document.getElementById("inptlocalMisGastosBusca").value : "");
	configurarModalMovimientoFinanciero(contexto);
	idAbmGasto = "";
	document.getElementById("btnAbmGastos").value = (tipo == "Ingreso" ? "Guardar ingreso" : "Guardar egreso");
	document.getElementById("inptTipoGasto").value = tipo;
	if (contexto.interconsultaId) {
		cod_interConsulta = contexto.interconsultaId;
	}
	if (contexto.interconsultaNombre && document.getElementById("inptAbmInterConsultaGasto")) {
		document.getElementById("inptAbmInterConsultaGasto").value = contexto.interconsultaNombre;
	}
	if (codConcepto) {
		asegurarOpcionConceptoMovimientoFinanciero(codConcepto, conceptoNombre);
		document.getElementById("inptMotivoMisGastos").value = codConcepto;
	}
	if (conceptoNombre && document.getElementById("inptDescripcionGasto").value == "") {
		document.getElementById("inptDescripcionGasto").value = conceptoNombre;
	}
	if (localFiltrado && document.getElementById("inptlocalMisGastos")) {
		document.getElementById("inptlocalMisGastos").value = localFiltrado;
	}
	if (document.getElementById("inptFechaGasto") && document.getElementById("inptFechaGasto").value == "") {
		document.getElementById("inptFechaGasto").value = obtenerFechaDefaultMovimientoFinanciero();
	}
	if (codConcepto) {
		setTimeout(function () {
			asegurarOpcionConceptoMovimientoFinanciero(codConcepto, conceptoNombre);
			document.getElementById("inptMotivoMisGastos").value = codConcepto;
			actualizarVistaPreviaPlanificacionGasto();
		}, 350);
	}
	if (contexto.proyectoId) {
		buscarProyectosVistaSelecc(contexto.proyectoId, function () {
			actualizarVisibilidadCantidadCuotasGasto();
			actualizarVistaPreviaPlanificacionGasto();
			if (contexto.focoPlanificacion) {
				enfocarPagoYPlanificacionMovimientoFinanciero();
			}
		});
	} else if (contexto.interconsultaId) {
		buscarProyectosVistaSelecc();
	}
	inicializarVistaPreviaPlanificacionGasto();
	if (contexto.focoPlanificacion && !contexto.proyectoId) {
		enfocarPagoYPlanificacionMovimientoFinanciero();
	}
}

function abrirMovimientoFinanciero(contexto) {
	contexto = contexto || {};
	if (contexto.modo == "editar") {
		var filaMovimiento = contexto.fila || obtenerFilaMovimientoFinancieroPorId(contexto.movimientoId);
		if (filaMovimiento) {
			obtenerdatosabmGasto(filaMovimiento);
		} else if (contexto.movimientoId) {
			ver_vetana_informativa("No se encontraron los datos del movimiento para editar.");
			return;
		} else {
			configurarModalMovimientoFinanciero({ modo: "editar" });
		}
		verVentanaEditarGasto();
		return;
	}
	if (contexto.modo == "crear") {
		if(controlacceso("INSERTARLISTADOEGRESOINGRESO","accion")==false){return;}
		if(idabmAperturacierrecaja==""){
			verCerrarVentanaAbmGasto(true, true, false);
			return;
		}
		verCerrarVentanaAbmGasto(true, true, false);
		aplicarContextoCrearMovimientoFinanciero(contexto);
		return;
	}
	verCerrarVentanaAbmGasto(true, true);
}

function abrirMovimientoFinancieroDesdeBotonConcepto(evento, boton) {
	if (evento && evento.stopPropagation) {
		evento.stopPropagation();
	}
	if (evento && evento.preventDefault) {
		evento.preventDefault();
	}
	if (!boton) { return; }
	abrirMovimientoFinanciero({
		modo: "crear",
		tipoMovimiento: boton.getAttribute("data-tipo-movimiento") || "Egreso",
		categoriaFlujo: boton.getAttribute("data-categoria-flujo") || "",
		categoriaCodigo: boton.getAttribute("data-categoria-codigo") || "",
		conceptoId: boton.getAttribute("data-concepto-id") || "",
		conceptoNombre: boton.getAttribute("data-concepto-nombre") || "",
		localId: document.getElementById("inptlocalMisGastosBusca") ? document.getElementById("inptlocalMisGastosBusca").value : ""
	});
}

function planificacionGastoValor(id) {
	var elemento = document.getElementById(id);
	return elemento ? elemento.value : "";
}

function planificacionGastoNumero(valor) {
	valor = String(valor == null ? "" : valor).replace(/Gs\.?/gi, "").replace(/\s/g, "").replace(/\./g, "").replace(",", ".");
	return Math.round(Number(valor) || 0);
}

function planificacionGastoFormatoMonto(valor) {
	var numero = planificacionGastoNumero(valor);
	if (typeof separadordemilesnumero == "function") {
		return separadordemilesnumero(String(numero));
	}
	return String(numero).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function planificacionGastoEscape(valor) {
	return String(valor == null ? "" : valor).replace(/[&<>"']/g, function (char) {
		return {"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[char];
	});
}

function planificacionGastoFecha(valor) {
	var partes = String(valor || "").split("-");
	if (partes.length != 3) { return null; }
	var anio = Number(partes[0]);
	var mes = Number(partes[1]);
	var dia = Number(partes[2]);
	if (!anio || !mes || !dia) { return null; }
	return new Date(anio, mes - 1, dia, 12, 0, 0);
}

function planificacionGastoClonarFecha(fecha) {
	return new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate(), 12, 0, 0);
}

function planificacionGastoUltimoDia(anio, mesBaseCero) {
	return new Date(anio, mesBaseCero + 1, 0).getDate();
}

function planificacionGastoSumarMeses(fechaBase, mesesASumar, diaObjetivo) {
	var mesTotal = fechaBase.getMonth() + mesesASumar;
	var nuevoAnio = fechaBase.getFullYear() + Math.floor(mesTotal / 12);
	var nuevoMes = ((mesTotal % 12) + 12) % 12;
	var ultimoDia = planificacionGastoUltimoDia(nuevoAnio, nuevoMes);
	return new Date(nuevoAnio, nuevoMes, Math.min(diaObjetivo, ultimoDia), 12, 0, 0);
}

function planificacionGastoFechaQuincenal(fechaBase, indice) {
	if (indice <= 0) {
		return planificacionGastoClonarFecha(fechaBase);
	}
	var anio = fechaBase.getFullYear();
	var mes = fechaBase.getMonth();
	var dia = fechaBase.getDate();
	var ultimoDia = planificacionGastoUltimoDia(anio, mes);
	var fechaCuota;
	if (dia < 15) {
		fechaCuota = new Date(anio, mes, 15, 12, 0, 0);
	} else if (dia < ultimoDia) {
		fechaCuota = new Date(anio, mes, ultimoDia, 12, 0, 0);
	} else {
		fechaCuota = new Date(anio, mes + 1, 15, 12, 0, 0);
	}
	for (var paso = 1; paso < indice; paso++) {
		var anioActual = fechaCuota.getFullYear();
		var mesActual = fechaCuota.getMonth();
		var diaActual = fechaCuota.getDate();
		if (diaActual === 15) {
			fechaCuota = new Date(anioActual, mesActual, planificacionGastoUltimoDia(anioActual, mesActual), 12, 0, 0);
		} else {
			fechaCuota = new Date(anioActual, mesActual + 1, 15, 12, 0, 0);
		}
	}
	return fechaCuota;
}

function planificacionGastoCalcularFecha(fechaBase, periodicidad, indice) {
	var diaObjetivo = fechaBase.getDate();
	if (indice <= 0) { return planificacionGastoClonarFecha(fechaBase); }
	if (periodicidad == "semanal") {
		var fechaSemanal = planificacionGastoClonarFecha(fechaBase);
		fechaSemanal.setDate(fechaSemanal.getDate() + (7 * indice));
		return fechaSemanal;
	}
	if (periodicidad == "quincenal") {
		return planificacionGastoFechaQuincenal(fechaBase, indice);
	}
	if (periodicidad == "mensual") {
		return planificacionGastoSumarMeses(fechaBase, indice, diaObjetivo);
	}
	if (periodicidad == "semestral") {
		return planificacionGastoSumarMeses(fechaBase, 6 * indice, diaObjetivo);
	}
	if (periodicidad == "anual") {
		return planificacionGastoSumarMeses(fechaBase, 12 * indice, diaObjetivo);
	}
	return null;
}

function planificacionGastoFechaCorta(fecha) {
	if (!fecha) { return ""; }
	var diaNumero = fecha.getDate();
	var mesNumero = fecha.getMonth() + 1;
	var dia = (diaNumero < 10 ? "0" : "") + diaNumero;
	var mes = (mesNumero < 10 ? "0" : "") + mesNumero;
	return dia + "/" + mes + "/" + fecha.getFullYear();
}

function planificacionGastoProyectoTexto(cantidadCuotas) {
	var selectProyecto = document.getElementById("inptProyectoGasto");
	var descripcion = (planificacionGastoValor("inptDescripcionGasto") || "este movimiento").trim();
	if (!selectProyecto) {
		return "";
	}
	var valor = selectProyecto.value || "";
	var texto = "";
	if (selectProyecto.selectedIndex >= 0 && selectProyecto.options[selectProyecto.selectedIndex]) {
		texto = selectProyecto.options[selectProyecto.selectedIndex].text || "";
	}
	if (cantidadCuotas > 1) {
		if (valor && valor != "0") {
			return "Proyecto del hilo: " + texto + ". Las cuotas quedaran agrupadas ahi.";
		}
		return "Nueva serie/proyecto: al guardar, el sistema agrupara estas cuotas dentro del hilo usando la descripcion \"" + descripcion + "\".";
	}
	if (valor && valor != "0") {
		return "Proyecto asociado: " + texto + ". No se generan cuotas nuevas.";
	}
	return "Pago unico aislado dentro del hilo. No se agregara a una serie de cuotas.";
}

function actualizarVisibilidadCantidadCuotasGasto() {
	var filaCantidad = document.getElementById("tablePeriodicidad");
	if (!filaCantidad) { return; }
	var proyecto = planificacionGastoValor("inptProyectoGasto");
	var esEdicion = (typeof idAbmGasto != "undefined" && idAbmGasto != "");
	var esCredito = (typeof gastoSeleccionadoTieneCuotasAsociadas == "function" && gastoSeleccionadoTieneCuotasAsociadas());
	filaCantidad.style.display = (!esEdicion || esCredito || (proyecto != "" && proyecto != "0")) ? "" : "none";
}

function actualizarVistaPreviaPlanificacionGasto() {
	var contenedor = document.getElementById("vistaPreviaPlanificacionGasto");
	if (!contenedor) { return; }
	var monto = planificacionGastoNumero(planificacionGastoValor("inptMontoGasto"));
	var fechaBase = planificacionGastoFecha(planificacionGastoValor("inptFechaGasto"));
	var cantidad = Number(planificacionGastoValor("inptCantCuotaGasto") || 0);
	var periodicidad = planificacionGastoValor("inptPeriodicidadGasto");
	if (!cantidad || cantidad < 1) { cantidad = 1; }
	var esPlan = cantidad > 1;
	var tipo = planificacionGastoValor("inptTipoGasto") || "Movimiento";
	var estadoTexto = planificacionGastoValor("inptEstadoGasto") == "Activo" ? "pagado" : "inactivo";
	var titulo = esPlan ? ("Plan en " + cantidad + " cuotas") : "Pago unico";
	var subtitulo = esPlan ? ("Monto por cuota: " + planificacionGastoFormatoMonto(monto) + " Gs.") : ("Monto del pago: " + planificacionGastoFormatoMonto(monto) + " Gs.");
	var totalPlan = monto * cantidad;
	var html = "<div class='movimiento-plan-preview__head'>"
		+ "<div><strong>" + planificacionGastoEscape(titulo) + "</strong><span>" + planificacionGastoEscape(tipo + " - " + estadoTexto) + "</span></div>"
		+ "<small>" + subtitulo + "</small>"
		+ "</div>";
	html += "<div class='movimiento-plan-preview__meta'>"
		+ "<span>Total planificado: <b>" + planificacionGastoFormatoMonto(totalPlan) + " Gs.</b></span>"
		+ "<span>" + planificacionGastoEscape(planificacionGastoProyectoTexto(cantidad)) + "</span>"
		+ "</div>";
	if (!fechaBase) {
		html += "<div class='movimiento-plan-preview__empty'>Seleccione una fecha para ver el calendario previsto.</div>";
		contenedor.innerHTML = html;
		return;
	}
	if (esPlan && periodicidad == "") {
		html += "<div class='movimiento-plan-preview__empty'>Seleccione una periodicidad para calcular las fechas de las cuotas.</div>";
		contenedor.innerHTML = html;
		return;
	}
	var limiteVista = Math.min(cantidad, 24);
	html += "<div class='movimiento-plan-preview__list'>";
	for (var i = 0; i < limiteVista; i++) {
		var fechaCuota = esPlan ? planificacionGastoCalcularFecha(fechaBase, periodicidad, i) : fechaBase;
		if (!fechaCuota) { continue; }
		html += "<div class='movimiento-plan-preview__item'>"
			+ "<b>" + (esPlan ? ("Cuota " + (i + 1) + "/" + cantidad) : "Pago unico") + "</b>"
			+ "<span>" + planificacionGastoFechaCorta(fechaCuota) + "</span>"
			+ "<em>" + planificacionGastoFormatoMonto(monto) + " Gs.</em>"
			+ "</div>";
	}
	html += "</div>";
	if (cantidad > limiteVista) {
		html += "<div class='movimiento-plan-preview__empty'>Se muestran las primeras " + limiteVista + " cuotas de " + cantidad + ".</div>";
	}
	contenedor.innerHTML = html;
}

function inicializarVistaPreviaPlanificacionGasto() {
	var ids = ["inptMotivoMisGastos", "inptDescripcionGasto", "inptFechaGasto", "inptProyectoGasto", "inptMontoGasto", "inptTipoGasto", "inptEstadoGasto", "inptCantCuotaGasto", "inptPeriodicidadGasto"];
	for (var i = 0; i < ids.length; i++) {
		var elemento = document.getElementById(ids[i]);
		if (elemento && !elemento.getAttribute("data-plan-preview-bound")) {
			elemento.setAttribute("data-plan-preview-bound", "true");
			elemento.addEventListener("change", actualizarVistaPreviaPlanificacionGasto);
			elemento.addEventListener("keyup", actualizarVistaPreviaPlanificacionGasto);
		}
	}
	actualizarVistaPreviaPlanificacionGasto();
}

var conciliacionEgresoUenoContexto = {
	gastoPrincipal: null,
	movimientoBanco: null,
	distribucion: [],
	codMotivo: "",
	codLocal: ""
};

function conciliarEgresoUenoNumero(valor) {
	valor = String(valor == null ? "" : valor).replace(/Gs\.?/gi, "").replace(/\s/g, "").replace(/\./g, "").replace(",", ".");
	return Math.round(Number(valor) || 0);
}

function conciliarEgresoUenoFormato(valor) {
	var numero = conciliarEgresoUenoNumero(valor);
	if (typeof separadordemilesnumero == "function") {
		return separadordemilesnumero(String(numero));
	}
	return String(numero).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function conciliarEgresoUenoValorInputMonto(valor) {
	return conciliarEgresoUenoFormato(valor).replace(/"/g, "&quot;");
}

function conciliarEgresoUenoEscape(valor) {
	return String(valor == null ? "" : valor).replace(/[&<>"']/g, function (char) {
		return {"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[char];
	});
}

function conciliarEgresoUenoAjax(funt, extras, alExito) {
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", funt);
	extras = extras || {};
	for (var clave in extras) {
		if (Object.prototype.hasOwnProperty.call(extras, clave)) {
			datos.append(clave, extras[clave]);
		}
	}
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConciliacionUeno.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "conciliarEgresoUeno");
		},
		success: function (responseText) {
			verCerrarEfectoCargando("");
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "UI") {
					ir_a_login();
					return;
				}
				if (respuesta["1"] == "NI") {
					ver_vetana_informativa(respuesta["2"] || "NO TIENES PERMISO", "", "error");
					return;
				}
				if (respuesta["1"] == "tablasfaltantes") {
					ver_vetana_informativa(respuesta["2"] || "Falta ejecutar la actualizacion de conciliacion de egresos Ueno.", "", "error");
					return;
				}
				if (respuesta["1"] != "exito") {
					ver_vetana_informativa(respuesta["2"] || "No se pudo procesar la conciliacion.", "", "error");
					return;
				}
				if (typeof alExito == "function") {
					alExito(respuesta);
				}
			} catch (error) {
				ver_vetana_informativa("Error inesperado en conciliacion de egresos Ueno", String(error), "error");
			}
		}
	});
}

function conciliarEgresoUenoMostrarModal(mostrar) {
	var modal = document.getElementById("divConciliarEgresoUeno");
	if (!modal) { return; }
	var fondo = document.getElementById("fondoConciliarEgresoUeno");
	if (!fondo && document.body) {
		fondo = document.createElement("div");
		fondo.id = "fondoConciliarEgresoUeno";
		fondo.className = "conciliacion-egreso-backdrop";
		fondo.style.display = "none";
		fondo.onclick = function () { conciliarEgresoUenoMostrarModal(false); };
		document.body.appendChild(fondo);
	}
	if (mostrar) {
		if (document.body && modal.parentNode !== document.body) {
			document.body.appendChild(modal);
		}
		if (fondo) {
			fondo.style.display = "";
		}
		modal.classList.add("conciliacion-egreso-modal--open");
		modal.style.display = "";
		setTimeout(function () {
			var primerCampo = modal.querySelector("input:not([type='hidden']), select, button");
			if (primerCampo && primerCampo.focus) {
				primerCampo.focus();
			}
		}, 0);
		return;
	}
	if (fondo) {
		fondo.style.display = "none";
	}
	modal.classList.remove("conciliacion-egreso-modal--open");
	modal.style.display = "none";
}

function conciliarEgresoUenoLimpiar() {
	conciliacionEgresoUenoContexto = {
		gastoPrincipal: null,
		movimientoBanco: null,
		distribucion: [],
		codMotivo: "",
		codLocal: ""
	};
	var ids = [
		"chipContextoConciliarEgresoUeno",
		"panelGastoConciliarEgresoUeno",
		"panelBancoConciliarEgresoUeno",
		"tableUenoEgresosDisponibles",
		"tableUenoGastosPendientesConciliar",
		"tableUenoAsignacionesGasto",
		"listaDistribucionConciliarEgresoUeno",
		"lblConciliarEgresoUenoResumen"
	];
	for (var i = 0; i < ids.length; i++) {
		if (document.getElementById(ids[i])) {
			document.getElementById(ids[i]).innerHTML = "";
		}
	}
	if (document.getElementById("txtConciliarEgresoUenoObs")) {
		document.getElementById("txtConciliarEgresoUenoObs").value = "";
	}
}

function conciliarEgresoUenoPintarGasto(gasto) {
	var panel = document.getElementById("panelGastoConciliarEgresoUeno");
	var chip = document.getElementById("chipContextoConciliarEgresoUeno");
	if (!panel || !gasto) { return; }
	if (chip) {
		chip.textContent = "Destino: " + (gasto.grupo || "Egresos") + " > " + (gasto.concepto || "Concepto") + " > Gasto #" + gasto.idgastos;
	}
	panel.innerHTML = "<div class='conciliacion-egreso-context-grid'>"
		+ "<span><b>Concepto</b>" + conciliarEgresoUenoEscape(gasto.concepto || "") + "</span>"
		+ "<span><b>Descripcion</b>" + conciliarEgresoUenoEscape(gasto.descripcion || "") + "</span>"
		+ "<span><b>Local</b>" + conciliarEgresoUenoEscape(gasto.local || "") + "</span>"
		+ "<span><b>Vencimiento</b>" + conciliarEgresoUenoEscape(gasto.fecha || "") + "</span>"
		+ "<span><b>Monto total</b>" + conciliarEgresoUenoEscape(gasto.monto_fmt || "0") + " Gs.</span>"
		+ "<span><b>Conciliado</b>" + conciliarEgresoUenoEscape(gasto.conciliado_fmt || "0") + " Gs.</span>"
		+ "<span><b>Pendiente</b>" + conciliarEgresoUenoEscape(gasto.pendiente_fmt || "0") + " Gs.</span>"
		+ "<span><b>Estado</b>" + conciliarEgresoUenoEscape(gasto.estado_conciliacion_texto || gasto.estado || "") + "</span>"
		+ "</div>";
}

function conciliarEgresoUenoPintarGastoPendienteConcepto(concepto) {
	var panel = document.getElementById("panelGastoConciliarEgresoUeno");
	if (!panel) { return; }
	panel.innerHTML = "<div class='conciliacion-egreso-empty conciliacion-egreso-empty--attention'>"
		+ "<b>Falta elegir un gasto concreto</b>"
		+ "<span>El concepto " + conciliarEgresoUenoEscape(concepto || "seleccionado") + " agrupa movimientos, pero no se concilia directamente. Seleccione abajo un gasto pendiente o registre primero el gasto con el boton + del concepto.</span>"
		+ "</div>";
}

function conciliarEgresoUenoPintarBanco() {
	var panel = document.getElementById("panelBancoConciliarEgresoUeno");
	var banco = conciliacionEgresoUenoContexto.movimientoBanco;
	if (!panel) { return; }
	if (!banco) {
		panel.innerHTML = "<div class='conciliacion-egreso-empty'>Seleccione un egreso bancario disponible.</div>";
		return;
	}
	panel.innerHTML = "<div class='conciliacion-egreso-bank-selected'>"
		+ "<span><b>Fecha</b>" + conciliarEgresoUenoEscape(banco.fecha_confirmacion || "") + "</span>"
		+ "<span><b>Cuenta</b>" + conciliarEgresoUenoEscape(banco.cuenta || "") + "</span>"
		+ "<span><b>Comprobante</b>" + conciliarEgresoUenoEscape(banco.nro_comprobante || "") + "</span>"
		+ "<span><b>Debito</b>" + conciliarEgresoUenoEscape(banco.importe_debito_fmt || "0") + " Gs.</span>"
		+ "<span><b>Asignado</b>" + conciliarEgresoUenoEscape(banco.monto_asignado_fmt || "0") + " Gs.</span>"
		+ "<span><b>Disponible</b>" + conciliarEgresoUenoEscape(banco.saldo_disponible_fmt || "0") + " Gs.</span>"
		+ "<span class='conciliacion-egreso-bank-wide'><b>Descripcion</b>" + conciliarEgresoUenoEscape(banco.descripcion || banco.concepto || "") + "</span>"
		+ "</div>";
}

function conciliarEgresoUenoMontoRequerido() {
	var input = document.getElementById("inptConciliarEgresoUenoMonto");
	return input ? conciliarEgresoUenoNumero(input.value) : 0;
}

function conciliarEgresoUenoAgregarGastoDistribucion(gasto) {
	if (!gasto || !gasto.idgastos) { return; }
	for (var i = 0; i < conciliacionEgresoUenoContexto.distribucion.length; i++) {
		if (String(conciliacionEgresoUenoContexto.distribucion[i].gasto.idgastos) == String(gasto.idgastos)) {
			ver_vetana_informativa("Ese gasto ya esta en la distribucion.");
			return;
		}
	}
	var montoSugerido = conciliarEgresoUenoNumero(gasto.pendiente || 0);
	var totalActual = conciliarEgresoUenoTotalDistribuido();
	var montoRequerido = conciliarEgresoUenoMontoRequerido();
	if (montoRequerido > 0) {
		montoSugerido = Math.min(montoSugerido, montoRequerido - totalActual);
	}
	if (conciliacionEgresoUenoContexto.movimientoBanco) {
		montoSugerido = Math.min(montoSugerido, conciliarEgresoUenoNumero(conciliacionEgresoUenoContexto.movimientoBanco.saldo_disponible || 0) - totalActual);
	}
	if (montoSugerido < 0) { montoSugerido = 0; }
	conciliacionEgresoUenoContexto.distribucion.push({
		gasto: gasto,
		monto: montoSugerido
	});
	conciliarEgresoUenoRenderDistribucion();
}

function conciliarEgresoUenoTotalDistribuido() {
	var total = 0;
	for (var i = 0; i < conciliacionEgresoUenoContexto.distribucion.length; i++) {
		total += conciliarEgresoUenoNumero(conciliacionEgresoUenoContexto.distribucion[i].monto || 0);
	}
	return total;
}

function conciliarEgresoUenoAjustarDistribucionAlBanco() {
	var banco = conciliacionEgresoUenoContexto.movimientoBanco;
	var disponible = banco ? conciliarEgresoUenoNumero(banco.saldo_disponible || 0) : 0;
	var requerido = conciliarEgresoUenoMontoRequerido();
	var limite = banco ? disponible : requerido;
	if (requerido > 0) {
		limite = banco ? Math.min(disponible, requerido) : requerido;
	}
	if (limite <= 0) { return; }
	var total = conciliarEgresoUenoTotalDistribuido();
	var debeAjustar = total == 0 || total > limite;
	for (var i = 0; i < conciliacionEgresoUenoContexto.distribucion.length; i++) {
		var item = conciliacionEgresoUenoContexto.distribucion[i];
		var pendiente = conciliarEgresoUenoNumero(item.gasto ? item.gasto.pendiente : 0);
		if (conciliarEgresoUenoNumero(item.monto || 0) > pendiente) {
			debeAjustar = true;
		}
	}
	if (!debeAjustar && total > 0) { return; }
	var restante = limite;
	for (var j = 0; j < conciliacionEgresoUenoContexto.distribucion.length; j++) {
		var itemDistribucion = conciliacionEgresoUenoContexto.distribucion[j];
		var saldoGasto = conciliarEgresoUenoNumero(itemDistribucion.gasto ? itemDistribucion.gasto.pendiente : 0);
		itemDistribucion.monto = Math.max(0, Math.min(saldoGasto, restante));
		restante -= conciliarEgresoUenoNumero(itemDistribucion.monto || 0);
	}
}

function conciliarEgresoUenoAplicarMontoRequerido() {
	// El monto requerido filtra/sugiere egresos bancarios; no debe pisar montos ya cargados.
	conciliarEgresoUenoActualizarResumen();
}

function conciliarEgresoUenoCambiarMontoDistribucion(indice, valor) {
	if (!conciliacionEgresoUenoContexto.distribucion[indice]) { return; }
	conciliacionEgresoUenoContexto.distribucion[indice].monto = conciliarEgresoUenoNumero(valor);
	conciliarEgresoUenoActualizarResumen();
}

function conciliarEgresoUenoEliminarDistribucion(indice) {
	conciliacionEgresoUenoContexto.distribucion.splice(indice, 1);
	conciliarEgresoUenoRenderDistribucion();
}

function conciliarEgresoUenoRenderDistribucion() {
	var contenedor = document.getElementById("listaDistribucionConciliarEgresoUeno");
	if (!contenedor) { return; }
	var html = "";
	for (var i = 0; i < conciliacionEgresoUenoContexto.distribucion.length; i++) {
		var item = conciliacionEgresoUenoContexto.distribucion[i];
		var gasto = item.gasto || {};
		var montoFila = conciliarEgresoUenoNumero(item.monto || 0);
		html += "<div class='conciliacion-egreso-distrib-row'>"
			+ "<span><b>Grupo</b>" + conciliarEgresoUenoEscape(gasto.grupo || "") + "</span>"
			+ "<span><b>Concepto</b>" + conciliarEgresoUenoEscape(gasto.concepto || "") + "</span>"
			+ "<span><b>Movimiento</b>#" + conciliarEgresoUenoEscape(gasto.idgastos || "") + " - " + conciliarEgresoUenoEscape(gasto.descripcion || "") + "</span>"
			+ "<span><b>Saldo pend.</b>" + conciliarEgresoUenoEscape(gasto.pendiente_fmt || "0") + " Gs.</span>"
			+ "<label><b>Monto</b><input class='inputText conciliacion-egreso-monto-input' type='text' value=\"" + conciliarEgresoUenoValorInputMonto(montoFila) + "\" data-monto=\"" + montoFila + "\" onkeyup='conciliarEgresoUenoCambiarMontoDistribucion(" + i + ", this.value);separadordemiles(this)' /></label>"
			+ "<button type='button' class='conciliacion-egreso-remove' title='Quitar gasto' onclick='conciliarEgresoUenoEliminarDistribucion(" + i + ")'>X</button>"
			+ "</div>";
	}
	if (html == "") {
		var requerido = conciliarEgresoUenoMontoRequerido();
		html = "<div class='conciliacion-egreso-empty'>"
			+ (requerido > 0
				? "Monto requerido: " + conciliarEgresoUenoFormato(requerido) + " Gs. Seleccione un gasto pendiente para aplicarlo."
				: "Seleccione un gasto pendiente para distribuir el egreso bancario.")
			+ "</div>";
	}
	contenedor.innerHTML = html;
	conciliarEgresoUenoNormalizarInputsMontoDistribucion(contenedor);
	conciliarEgresoUenoActualizarResumen();
}

function conciliarEgresoUenoNormalizarInputsMontoDistribucion(contenedor) {
	var inputs = contenedor.querySelectorAll(".conciliacion-egreso-monto-input");
	for (var i = 0; i < inputs.length; i++) {
		var monto = conciliarEgresoUenoNumero(inputs[i].getAttribute("data-monto") || inputs[i].value || 0);
		inputs[i].value = conciliarEgresoUenoFormato(monto);
	}
}

function conciliarEgresoUenoActualizarResumen() {
	var resumen = document.getElementById("lblConciliarEgresoUenoResumen");
	if (!resumen) { return; }
	var banco = conciliacionEgresoUenoContexto.movimientoBanco;
	var total = conciliarEgresoUenoTotalDistribuido();
	var requerido = conciliarEgresoUenoMontoRequerido();
	var disponible = banco ? conciliarEgresoUenoNumero(banco.saldo_disponible || 0) : 0;
	var restante = Math.max(0, disponible - total);
	resumen.innerHTML = "Total a asignar: <b>" + conciliarEgresoUenoFormato(total) + " Gs.</b>"
		+ (requerido > 0 ? " | Monto requerido: <b>" + conciliarEgresoUenoFormato(requerido) + " Gs.</b>" : "")
		+ (banco ? " | Saldo bancario restante: <b>" + conciliarEgresoUenoFormato(restante) + " Gs.</b>" : "")
		+ (banco && total == 0 ? " | Falta seleccionar un gasto pendiente." : "");
}

function conciliarEgresoUenoSeleccionarBanco(banco) {
	conciliacionEgresoUenoContexto.movimientoBanco = banco || null;
	conciliarEgresoUenoPintarBanco();
	if (conciliacionEgresoUenoContexto.gastoPrincipal && conciliacionEgresoUenoContexto.distribucion.length == 0) {
		conciliarEgresoUenoAgregarGastoDistribucion(conciliacionEgresoUenoContexto.gastoPrincipal);
	} else {
		conciliarEgresoUenoAjustarDistribucionAlBanco();
		conciliarEgresoUenoRenderDistribucion();
	}
}

function abrirConciliacionEgresoUenoDesdeBoton(evento, boton) {
	if (evento && evento.stopPropagation) { evento.stopPropagation(); }
	if (evento && evento.preventDefault) { evento.preventDefault(); }
	if (!boton) { return; }
	conciliarEgresoUenoLimpiar();
	conciliarEgresoUenoMostrarModal(true);
	conciliarEgresoUenoCargarGasto(boton.getAttribute("data-idgastos") || "");
}

function abrirConciliacionEgresoUenoDesdeConcepto(evento, boton) {
	if (evento && evento.stopPropagation) { evento.stopPropagation(); }
	if (evento && evento.preventDefault) { evento.preventDefault(); }
	conciliarEgresoUenoLimpiar();
	conciliacionEgresoUenoContexto.codMotivo = boton ? (boton.getAttribute("data-cod-motivo") || "") : "";
	conciliacionEgresoUenoContexto.codLocal = document.getElementById("inptlocalMisGastosBusca") ? document.getElementById("inptlocalMisGastosBusca").value : "";
	conciliarEgresoUenoMostrarModal(true);
	var chip = document.getElementById("chipContextoConciliarEgresoUeno");
	if (chip) {
		chip.textContent = "Destino: " + (boton ? (boton.getAttribute("data-categoria-flujo") || "Egresos") : "Egresos") + " > " + (boton ? (boton.getAttribute("data-concepto-nombre") || "") : "");
	}
	conciliarEgresoUenoPintarGastoPendienteConcepto(boton ? (boton.getAttribute("data-concepto-nombre") || "") : "");
	conciliarEgresoUenoBuscarGastosPendientes();
	conciliarEgresoUenoBuscarBanco();
}

function conciliarEgresoUenoCargarGasto(idgastos) {
	if (!idgastos) { return; }
	verCerrarEfectoCargando("1");
	conciliarEgresoUenoAjax("buscar_contexto_gasto_egreso", { idgastos: idgastos }, function (respuesta) {
		var gasto = respuesta.gasto || null;
		conciliacionEgresoUenoContexto.gastoPrincipal = gasto;
		conciliacionEgresoUenoContexto.codMotivo = gasto ? (gasto.cod_motivo || "") : "";
		conciliacionEgresoUenoContexto.codLocal = gasto ? (gasto.cod_local || "") : "";
		conciliarEgresoUenoPintarGasto(gasto);
		document.getElementById("tableUenoAsignacionesGasto").innerHTML = respuesta.asignaciones || "";
		conciliarEgresoUenoBuscarBanco();
		conciliarEgresoUenoAgregarGastoDistribucion(gasto);
		conciliarEgresoUenoBuscarGastosPendientes();
	});
}

function conciliarEgresoUenoPrepararFiltrosBanco() {
	var inicio = document.getElementById("inptConciliarEgresoUenoDesde");
	var fin = document.getElementById("inptConciliarEgresoUenoHasta");
	if (inicio && inicio.value == "" && document.getElementById("inptBuscarGastoF1")) {
		inicio.value = document.getElementById("inptBuscarGastoF1").value;
	}
	if (fin && fin.value == "" && document.getElementById("inptBuscarGastoF2")) {
		fin.value = document.getElementById("inptBuscarGastoF2").value;
	}
}

function conciliarEgresoUenoBuscarBanco() {
	conciliarEgresoUenoPrepararFiltrosBanco();
	verCerrarEfectoCargando("1");
	conciliarEgresoUenoAjax("buscar_egresos_bancarios_disponibles", {
		fecha_desde: document.getElementById("inptConciliarEgresoUenoDesde") ? document.getElementById("inptConciliarEgresoUenoDesde").value : "",
		fecha_hasta: document.getElementById("inptConciliarEgresoUenoHasta") ? document.getElementById("inptConciliarEgresoUenoHasta").value : "",
		comprobante: document.getElementById("inptConciliarEgresoUenoComprobante") ? document.getElementById("inptConciliarEgresoUenoComprobante").value : "",
		descripcion: document.getElementById("inptConciliarEgresoUenoDescripcion") ? document.getElementById("inptConciliarEgresoUenoDescripcion").value : "",
		monto: document.getElementById("inptConciliarEgresoUenoMonto") ? document.getElementById("inptConciliarEgresoUenoMonto").value : "",
		cuenta: document.getElementById("inptConciliarEgresoUenoCuenta") ? document.getElementById("inptConciliarEgresoUenoCuenta").value : "",
		estado_conciliacion: document.getElementById("inptConciliarEgresoUenoEstado") ? document.getElementById("inptConciliarEgresoUenoEstado").value : "",
		mostrar_todos: document.getElementById("chkConciliarEgresoUenoTodos") && document.getElementById("chkConciliarEgresoUenoTodos").checked ? "true" : "false"
	}, function (respuesta) {
		document.getElementById("tableUenoEgresosDisponibles").innerHTML = respuesta["2"] || "";
	});
}

function conciliarEgresoUenoBuscarGastosPendientes() {
	verCerrarEfectoCargando("1");
	conciliarEgresoUenoAjax("buscar_gastos_pendientes_egreso", {
		texto: document.getElementById("inptConciliarEgresoUenoBuscarGasto") ? document.getElementById("inptConciliarEgresoUenoBuscarGasto").value : "",
		cod_motivo: conciliacionEgresoUenoContexto.codMotivo || "",
		cod_local: conciliacionEgresoUenoContexto.codLocal || ""
	}, function (respuesta) {
		document.getElementById("tableUenoGastosPendientesConciliar").innerHTML = respuesta["2"] || "";
	});
}

function conciliarEgresoUenoBuscarGastosTodosLocales() {
	conciliacionEgresoUenoContexto.codLocal = "";
	var chip = document.getElementById("chipContextoConciliarEgresoUeno");
	if (chip && chip.textContent.indexOf("todos los locales") < 0) {
		chip.textContent += " > todos los locales";
	}
	conciliarEgresoUenoBuscarGastosPendientes();
}

function conciliarEgresoUenoTextoConfirmacion(distribucion, total, disponible) {
	var banco = conciliacionEgresoUenoContexto.movimientoBanco || {};
	var restante = Math.max(0, conciliarEgresoUenoNumero(disponible) - conciliarEgresoUenoNumero(total));
	var lineas = [
		"Vas a registrar una conciliacion bancaria de egreso.",
		"",
		"Se asignara: " + conciliarEgresoUenoFormato(total) + " Gs.",
		"Egreso bancario: comprobante " + (banco.nro_comprobante || "sin comprobante") + " / cuenta " + (banco.cuenta || "sin cuenta"),
		"Saldo disponible actual del banco: " + conciliarEgresoUenoFormato(disponible) + " Gs.",
		"Saldo bancario despues de guardar: " + conciliarEgresoUenoFormato(restante) + " Gs.",
		"Gastos seleccionados: " + distribucion.length,
		""
	];
	for (var i = 0; i < conciliacionEgresoUenoContexto.distribucion.length; i++) {
		var item = conciliacionEgresoUenoContexto.distribucion[i];
		var gasto = item.gasto || {};
		lineas.push("- #" + (gasto.idgastos || "") + " " + (gasto.concepto || gasto.descripcion || "Gasto") + ": " + conciliarEgresoUenoFormato(item.monto || 0) + " Gs.");
	}
	lineas.push("");
	lineas.push("Esto no modifica el importe original del extracto bancario.");
	lineas.push("Para corregirlo despues, se debe revertir la asignacion.");
	lineas.push("");
	lineas.push("Confirmas guardar esta conciliacion?");
	return lineas.join("\n");
}

function conciliarEgresoUenoGuardar() {
	var banco = conciliacionEgresoUenoContexto.movimientoBanco;
	if (!banco || !banco.id_movimiento) {
		ver_vetana_informativa("Seleccione un egreso bancario disponible.");
		return;
	}
	var disponible = conciliarEgresoUenoNumero(banco.saldo_disponible || 0);
	var total = conciliarEgresoUenoTotalDistribuido();
	if (conciliacionEgresoUenoContexto.distribucion.length == 0) {
		ver_vetana_informativa("Seleccione un gasto pendiente para aplicar el egreso bancario.");
		return;
	}
	if (total <= 0) {
		ver_vetana_informativa("Ingrese un monto a aplicar.");
		return;
	}
	if (total > disponible) {
		ver_vetana_informativa("El monto distribuido supera el saldo disponible del egreso bancario.");
		return;
	}
	var distribucion = [];
	for (var i = 0; i < conciliacionEgresoUenoContexto.distribucion.length; i++) {
		var item = conciliacionEgresoUenoContexto.distribucion[i];
		var monto = conciliarEgresoUenoNumero(item.monto || 0);
		var pendiente = conciliarEgresoUenoNumero(item.gasto.pendiente || 0);
		if (monto <= 0 || monto > pendiente) {
			ver_vetana_informativa("Revise los montos de la distribucion.");
			return;
		}
		distribucion.push({ idgastos: item.gasto.idgastos, monto: monto });
	}
	if (!confirm(conciliarEgresoUenoTextoConfirmacion(distribucion, total, disponible))) {
		return;
	}
	verCerrarEfectoCargando("1");
	conciliarEgresoUenoAjax("guardar_conciliacion_egreso", {
		id_movimiento: banco.id_movimiento,
		distribucion: JSON.stringify(distribucion),
		observacion: document.getElementById("txtConciliarEgresoUenoObs") ? document.getElementById("txtConciliarEgresoUenoObs").value : ""
	}, function (respuesta) {
		var mensaje = (respuesta["2"] || "Conciliacion registrada correctamente.")
			+ " Se asignaron " + (respuesta.total_asignado || conciliarEgresoUenoFormato(total)) + " Gs. "
			+ "Saldo disponible del egreso: " + (respuesta.saldo_bancario_restante || "0") + " Gs.";
		if (conciliarEgresoUenoNumero(respuesta.saldo_bancario_restante_num || 0) > 0) {
			mensaje += " Este egreso quedo parcialmente conciliado y podra utilizarse posteriormente.";
		} else {
			mensaje += " El egreso bancario quedo completamente conciliado.";
		}
		ver_vetana_informativa(mensaje, "", "info");
		conciliarEgresoUenoMostrarModal(false);
		buscarabmGasto();
		if (typeof uenoBuscarMovimientos == "function") {
			uenoBuscarMovimientos();
		}
	});
}

function conciliarEgresoUenoRevertirAsignacion(idAsignacion) {
	if (!confirm("Confirmas revertir esta asignacion bancaria?")) {
		return;
	}
	var motivo = prompt("Motivo de reversion");
	if (!motivo) {
		ver_vetana_informativa("Debe indicar el motivo de reversion.");
		return;
	}
	verCerrarEfectoCargando("1");
	conciliarEgresoUenoAjax("revertir_conciliacion_egreso", {
		id_asignacion: idAsignacion,
		motivo: motivo
	}, function (respuesta) {
		ver_vetana_informativa(respuesta["2"] || "Asignacion revertida correctamente.", "", "info");
		if (conciliacionEgresoUenoContexto.gastoPrincipal) {
			conciliarEgresoUenoCargarGasto(conciliacionEgresoUenoContexto.gastoPrincipal.idgastos);
		}
		buscarabmGasto();
		if (typeof uenoBuscarMovimientos == "function") {
			uenoBuscarMovimientos();
		}
	});
}

function conciliarEgresoUenoVerAsignacionesBanco(idMovimiento) {
	conciliarEgresoUenoLimpiar();
	conciliarEgresoUenoMostrarModal(true);
	verCerrarEfectoCargando("1");
	conciliarEgresoUenoAjax("buscar_asignaciones_egreso_banco", {
		id_movimiento: idMovimiento
	}, function (respuesta) {
		var chip = document.getElementById("chipContextoConciliarEgresoUeno");
		var panel = document.getElementById("panelBancoConciliarEgresoUeno");
		if (chip) {
			chip.textContent = "Consulta de asignaciones del egreso bancario #" + idMovimiento;
		}
		if (panel) {
			panel.innerHTML = "<div class='conciliacion-egreso-selected-title'>Asignaciones del egreso bancario #" + conciliarEgresoUenoEscape(idMovimiento) + "</div>";
		}
		document.getElementById("tableUenoAsignacionesGasto").innerHTML = respuesta["2"] || "";
	});
}

function verCerrarVentanaAbmGasto(mostrar, limpiar= false, recargarProyectos= true) {
	if (mostrar) {
		if(idabmAperturacierrecaja==""){
			document.getElementById("divAbmGastos").style.display="none"
		   ver_vetana_informativa("FALTO INICIAR UNA CAJA")
		   verCerrarVentanaAbmAperturaCierreCaja1()
		   return
	   }
		
		if (limpiar) {
			limpiarcamposGasto();
			if (recargarProyectos) {
				buscarProyectosVistaSelecc();
			}
            BuscarAbmMotivoEgresoIngreso();
			if(controlacceso("INSERTARLISTADOEGRESOINGRESO","accion")==false){return;}	
		}
		$("div[id=divAbmGasto2]").fadeIn(250)
		document.getElementById('divAbmGasto1').style.display = "none"
	} else {
		$("div[id=divAbmGasto1]").fadeIn(250)
		document.getElementById('divAbmGasto2').style.display = "none"
		
		const ultimaVentana = ventanaAnterior.pop();
		switch (ultimaVentana) {
			case 'divAbmDetallesInterConsulta':
				verCerrarAbmGasto();
				break;
			case 'divListadoInterConsulta':
				verCerrarAbmGasto();
				break;
		}
	}
}

function verVentanaEditarGasto(vent_anterior= "") {
	if(controlacceso("EDITARLISTADOEGRESOINGRESO","accion")==false){return;}
	if(idabmAperturacierrecaja==""){
		document.getElementById("divAbmGastos").style.display="none"
		ver_vetana_informativa("FALTO INICIAR UNA CAJA")
		verCerrarVentanaAbmAperturaCierreCaja1()
		return
	}
	
	if (idAbmGasto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		return;
	}

	ventanaAnterior.push(vent_anterior);
	verCerrarVentanaAbmGasto(true, false)
}

function obtenerFilaMovimientoFinancieroPorId(movimientoId) {
	if (!movimientoId) { return null; }
	var filas = document.querySelectorAll('tr[id="tbSelecRegistro"]');
	for (var i = 0; i < filas.length; i++) {
		var celdaId = filas[i].querySelector('td[id="td_id"]');
		if (celdaId && celdaId.innerHTML == movimientoId) {
			return filas[i];
		}
	}
	return null;
}

function editarGastoDesdeFila(evento, elemento) {
	if (evento && evento.stopPropagation) {
		evento.stopPropagation();
	}
	var fila = elemento;
	while (fila && fila.tagName && fila.tagName.toLowerCase() != "tr") {
		fila = fila.parentElement;
	}
	if (!fila) {
		return;
	}
	var celdaId = fila.querySelector('td[id="td_id"]');
	abrirMovimientoFinanciero({
		modo: "editar",
		movimientoId: celdaId ? celdaId.innerHTML : "",
		fila: fila
	});
}

function alternarCuotasProgramadas(evento, fila) {
	if (!fila) {
		return;
	}
	obtenerdatosabmGasto(fila);
	var detalle = fila.nextElementSibling;
	while (detalle && (!detalle.classList || !detalle.classList.contains("cuotas-programadas-row"))) {
		detalle = detalle.nextElementSibling;
	}
	if (!detalle) {
		return;
	}
	var expandido = detalle.style.display != "none";
	detalle.style.display = expandido ? "none" : "table-row";
	if (fila.classList) {
		fila.classList.toggle("gasto-programado-expandido", !expandido);
	}
	var indicador = fila.querySelector("[data-cuotas-toggle]");
	if (indicador) {
		indicador.innerHTML = expandido ? "+" : "-";
	}
}

function alternarSubgrupoFlujoConcepto(evento, encabezado) {
	if (evento && evento.stopPropagation) {
		evento.stopPropagation();
	}
	if (!encabezado) { return; }
	var contenedor = encabezado.nextElementSibling;
	if (!contenedor) { return; }
	var oculto = contenedor.style.display == "none";
	contenedor.style.display = oculto ? "" : "none";
	if (encabezado.classList) {
		encabezado.classList.toggle("flujo-concepto-subgrupo__head--contraido", !oculto);
	}
	var boton = encabezado.querySelector(".flujo-concepto-subgrupo__toggle");
	if (boton) {
		boton.innerHTML = oculto ? "-" : "+";
	}
}

var idAbmGasto = "";
var usuarioCreadorEgresoIngreso = "";
function obtenerdatosabmGasto(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptMontoGasto').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccGasto').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptDescripcionGasto').value = $(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('inptMotivoMisGastos').value = $(datostr).children('td[id="td_datos_20"]').html();
	document.getElementById('inptFechaGasto').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptProyectoGasto').value = $(datostr).children('td[id="td_datos_22"]').html();
	document.getElementById('inptIdGasto').value = $(datostr).children('td[id="td_id"]').html();
	
	document.getElementById('inptEstadoGasto').value = ($(datostr).children('td[id="td_datos_5"]').html() == 'Inactivo' ? 'Inactivo' : 'Activo');
	document.getElementById('inptlocalMisGastos').value = $(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptNroBoletaGasto').value = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptBancoGasto').value = $(datostr).children('td[id="td_datos_9"]').html();
	document.getElementById('inptCuentaGasto').value = $(datostr).children('td[id="td_datos_10"]').html();
	document.getElementById('inptTipoGasto').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptArregloGasto').value = $(datostr).children('td[id="td_datos_11"]').html();
	document.getElementById('btnAbmGastos').value = "Actualizar movimiento";
	configurarModalMovimientoFinanciero({ modo: "editar" });
	document.getElementById('btnEditarGastos').style.backgroundColor="";
	document.getElementById('btnImprimirRegistroGastos').style.backgroundColor="";
	document.getElementById('btnAutorizarGastos').style.backgroundColor="#28a745";
	document.getElementById('btnInterConsultaGastos').style.backgroundColor= "";
	idAbmGasto = $(datostr).children('td[id="td_id"]').html();
	usuarioCreadorEgresoIngreso = $(datostr).children('td[id="td_datos_21"]').html() || "";

	cod_interConsulta= $(datostr).children('td[id="td_datos_15"]').html();
	document.getElementById("inptAbmInterConsultaGasto").value= $(datostr).children('td[id="td_datos_16"]').html();
	buscarProyectosVistaSelecc();
	inicializarVistaPreviaPlanificacionGasto();

	// Revisa si existe gastos asociados
	obtenerGastosAsociados(idAbmGasto);

	// Ocultar datos de periodicidad
	document.getElementById('tablePeriodicidad').style.display= "none";
	actualizarVisibilidadCantidadCuotasGasto();

	// Auditoria de autorizacion
	document.getElementById("inptCodigoAutorizacionEgreso").value= $(datostr).children('td[id="td_id"]').html();
	document.getElementById("inptMotivoAutorizacionEgreso").value= $(datostr).children('td[id="td_datos_14"]').html();
	document.getElementById('inptMontoAutorizacionEgreso').value = $(datostr).children('td[id="td_datos_1"]').html();
	if ($(datostr).children('td[id="td_datos_5"]').html() == 'solicitado') {
		document.getElementById("inptUsuarioAutorizacionEgreso").value= "";
		document.getElementById("inptFechaAutorizacionEgreso").value= "";
		document.getElementById('divbtnAprobarMovimiento').style.display= "";
	} else {
		document.getElementById("inptUsuarioAutorizacionEgreso").value= $(datostr).children('td[id="td_datos_18"]').html();
		document.getElementById("inptFechaAutorizacionEgreso").value= $(datostr).children('td[id="td_datos_19"]').html();
		document.getElementById('divbtnAprobarMovimiento').style.display= "none";
	}

	// Carga la imagen
	let imagen= $(datostr).children('td[id="td_datos_12"]').html();
	imagen= imagen ? imagen : '/GoodVentaAsisCap/iconos/imagenphoto.png';
    document.getElementById('imgfotoGasto').style.backgroundImage= "url("+ imagen +")";
	let documentoFirmado= $(datostr).children('td[id="td_datos_25"]').html();
	documentoFirmado= documentoFirmado ? documentoFirmado : '/GoodVentaAsisCap/iconos/imagenphoto.png';
    document.getElementById('imgdocumentoFirmadoGasto').style.backgroundImage= "url("+ documentoFirmado +")";
}

function verCerrarAutorizacionEgreso(mostrar) {
	if(controlacceso("AUTORIZAREGRESOINGRESO","accion")==false){return;}
	if (mostrar) {
		document.getElementById('divAutorizacionEgreso').style.display= "";
	} else {
		document.getElementById('divAutorizacionEgreso').style.display= "none";
	}
}

function aprobarMovimiento(opcion, elemento= null) {
	if(controlacceso("AUTORIZAREGRESOINGRESO","accion")==false){return;}

	if (elemento != null) {
		obtenerdatosabmGasto(elemento);
	}

	const inptCodigoAutorizacionEgreso= document.getElementById('inptCodigoAutorizacionEgreso').value;
	obtener_datos_user();

	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", 'aprobarMovimiento');
	datos.append("decision", opcion);
	datos.append("idgastos", inptCodigoAutorizacionEgreso);

	verCerrarEfectoCargando("1")
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
			xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var porce= ~~((evt.loaded / evt.total) * 100); 
		if(porce>90){
		porce=Number(porce)-7				
		}
		document.getElementById("lbltitulomensaje_b").innerHTML="Cargando<br>("+porce+"%)";
		var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("")
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			verCerrarEfectoCargando("")
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				   ver_vetana_informativa("Datos guardados.", "", "info");
				   switch (ventanaAnterior[ventanaAnterior.length - 1]) {
						case 'divListadoInterConsulta':
							buscarInterConsultasYContenido(cod_interConsulta);
							break;
						default: 
							buscarabmGasto();
							break;
					}
					verCerrarAutorizacionEgreso(false);
				}				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function seleccionarGastosAsociados(element) {
	obtenerdatosabmGasto(element);
	
	// Identifica si no se esta presente en la ventana de abm
	if (document.getElementById('divAbmGasto2').style.display == "none") {
		verCerrarAbmGasto();
		verVentanaEditarGasto("divAbmDetallesInterConsulta");
	}
}

function existeOpcionSelectProyecto(selectProyecto, valor) {
	for (var i = 0; i < selectProyecto.options.length; i++) {
		if (selectProyecto.options[i].value == valor) {
			return true;
		}
	}
	return false;
}

function buscarProyectosVistaSelecc(valorSeleccionar= "", alFinalizar) {
	const selectProyecto= document.getElementById('inptProyectoGasto');
	if (!selectProyecto) {
		if (typeof alFinalizar == "function") { alFinalizar(false); }
		return;
	}
	const valor= valorSeleccionar || selectProyecto.value;
	const codInterConsultaActual= (typeof cod_interConsulta != "undefined" && cod_interConsulta && cod_interConsulta != "0") ? cod_interConsulta : "";
	if (!codInterConsultaActual) {
		selectProyecto.innerHTML= '<option value="0">PAGO AISLADO (sin proyecto)</option>';
		selectProyecto.value= "0";
		selectProyecto.disabled= true;
		selectProyecto.setAttribute("data-proyecto-hilo-bloqueado", "true");
		selectProyecto.setAttribute("data-valor-anterior", "0");
		actualizarVistaPreviaPlanificacionGasto();
		if (typeof alFinalizar == "function") { alFinalizar(false); }
		return;
	}
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", 'buscarVistaSelect')
	if (codInterConsultaActual) {
		datos.append("cod_interConsultaFK", codInterConsultaActual)
	}
	
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmProyectoGasto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
			xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var porce= ~~((evt.loaded / evt.total) * 100); 
		if(porce>90){
		porce=Number(porce)-7				
		}
		document.getElementById("lbltitulomensaje_b").innerHTML="Cargando<br>("+porce+"%)";
		var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("")
		manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			if (typeof alFinalizar == "function") { alFinalizar(false); }
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			verCerrarEfectoCargando("")
			console.log(Respuesta)
			try {
				const codInterConsultaVigente= (typeof cod_interConsulta != "undefined" && cod_interConsulta && cod_interConsulta != "0") ? cod_interConsulta : "";
				if (codInterConsultaVigente != codInterConsultaActual) {
					if (typeof alFinalizar == "function") { alFinalizar(false); }
					return;
				}
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				   selectProyecto.innerHTML= '<option value="0">PAGO AISLADO (sin proyecto)</option>' + datos[2];
				   if (valor && existeOpcionSelectProyecto(selectProyecto, valor)) {
					   selectProyecto.value= valor;
				   } else {
					   selectProyecto.value= "0";
			}
				   selectProyecto.disabled= false;
				   selectProyecto.setAttribute("data-proyecto-hilo-bloqueado", "false");
				   selectProyecto.setAttribute("data-valor-anterior", selectProyecto.value);
				   actualizarVisibilidadCantidadCuotasGasto();
				   actualizarVistaPreviaPlanificacionGasto();
				   if (typeof alFinalizar == "function") { alFinalizar(true); }
				}				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				if (typeof alFinalizar == "function") { alFinalizar(false); }
			}
		}
	});
}

function manejarCambioProyectoGasto() {
	const selectProyecto= document.getElementById('inptProyectoGasto');
	if (!selectProyecto) {
		return;
	}
	selectProyecto.setAttribute("data-valor-anterior", selectProyecto.value || "0");
	actualizarVisibilidadCantidadCuotasGasto();
	actualizarVistaPreviaPlanificacionGasto();
}

function crearProyectoGastoDesdeSelector(nombreProyecto, valorAnterior= "0", opciones) {
	opciones = opciones || {};
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "nuevo/editar");
	datos.append("nombre", nombreProyecto);
	datos.append("estado", "activo");
	if (typeof cod_interConsulta != "undefined" && cod_interConsulta && cod_interConsulta != "0") {
		datos.append("cod_interConsultaFK", cod_interConsulta);
	}

	verCerrarEfectoCargando("1");
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmProyectoGasto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("");
			document.getElementById('inptProyectoGasto').value= valorAnterior || "0";
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
			return false;
		},
		success: function (responseText) {
			verCerrarEfectoCargando("");
			try {
				var datos = $.parseJSON(responseText);
				var respuesta = respuestaJqueryAjax(datos["1"]);
				if (respuesta == true) {
					var idProyecto= datos["id"] || datos["2"] || "";
					if (!opciones.silencioso) {
						ver_vetana_informativa("Proyecto creado. Quedo disponible para seleccionar en este hilo.", "", "info");
					}
					if (typeof opciones.alCrear == "function") {
						opciones.alCrear(idProyecto, nombreProyecto);
					} else {
						buscarProyectosVistaSelecc(idProyecto);
					}
				} else {
					document.getElementById('inptProyectoGasto').value= valorAnterior || "0";
					ver_vetana_informativa(datos["2"] || "No se pudo crear el proyecto.");
				}
			} catch (error) {
				document.getElementById('inptProyectoGasto').value= valorAnterior || "0";
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
				var titulo="Error: "+error+" \r\n Consola: "+responseText;
				GuardarArchivosLog(titulo);
			}
		}
	});
}

function crearProyectoGastoDesdeHilo(nombreSugerido= "", opciones) {
	opciones = opciones || {};
	if (typeof cod_interConsulta == "undefined" || !cod_interConsulta || cod_interConsulta == "0") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA INTERCONSULTA.");
		return;
	}
	let nombreProyecto= prompt("Nombre del nuevo proyecto del hilo", nombreSugerido || "");
	if (nombreProyecto == null || nombreProyecto.trim() == "") {
		return;
	}
	crearProyectoGastoDesdeSelector(nombreProyecto.trim(), "0", opciones);
}

function obtenerContextoHiloMovimientoActual() {
	var selectConcepto = document.getElementById("inptMotivoMisGastos");
	var conceptoId = selectConcepto ? (selectConcepto.value || "") : "";
	var conceptoTexto = "";
	if (selectConcepto && selectConcepto.selectedIndex >= 0 && selectConcepto.options[selectConcepto.selectedIndex]) {
		conceptoTexto = selectConcepto.options[selectConcepto.selectedIndex].text || "";
	}
	var descripcion = document.getElementById("inptDescripcionGasto") ? document.getElementById("inptDescripcionGasto").value : "";
	var tipo = document.getElementById("inptTipoGasto") ? document.getElementById("inptTipoGasto").value : "Egreso";
	var local = document.getElementById("inptlocalMisGastos") ? document.getElementById("inptlocalMisGastos").value : "";
	var nombreHilo = document.getElementById("inptAbmInterConsultaGasto") ? document.getElementById("inptAbmInterConsultaGasto").value : "";
	var asunto = (nombreHilo || descripcion || conceptoTexto || "Movimiento financiero").trim();
	return {
		conceptoId: conceptoId,
		conceptoTexto: conceptoTexto,
		descripcion: descripcion,
		tipo: tipo,
		local: local,
		nombreHilo: nombreHilo,
		asunto: asunto
	};
}

function obtenerOCrearInterConsultaMovimientoActual(alExito) {
	var contexto = obtenerContextoHiloMovimientoActual();
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", "obtener_crear_interconsulta_movimiento");
	datos.append("motivo", contexto.asunto);
	datos.append("tipo", contexto.tipo);
	datos.append("cod_local", contexto.local);
	datos.append("cod_motivoFK", contexto.conceptoId);
	verCerrarEfectoCargando("1");
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
		},
		success: function (responseText) {
			verCerrarEfectoCargando("");
			try {
				var datosRespuesta = $.parseJSON(responseText);
				var respuesta = respuestaJqueryAjax(datosRespuesta["1"]);
				if (respuesta == true) {
					cod_interConsulta = datosRespuesta["2"] || "";
					var asunto = datosRespuesta["3"] || contexto.asunto;
					var inputInterConsulta = document.getElementById("inptAbmInterConsultaGasto");
					if (inputInterConsulta) {
						inputInterConsulta.value = asunto;
					}
					if (typeof alExito == "function") {
						alExito(cod_interConsulta, asunto, contexto);
					}
					return;
				}
				ver_vetana_informativa(datosRespuesta["2"] || "No se pudo obtener la interconsulta del proyecto.");
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
				var titulo = "Error: " + error + " \r\n Consola: " + responseText;
				GuardarArchivosLog(titulo);
			}
		}
	});
}

function crearProyectoGastoDesdeVentanaMovimiento(evento) {
	if (evento && evento.stopPropagation) {
		evento.stopPropagation();
	}
	if (evento && evento.preventDefault) {
		evento.preventDefault();
	}
	if (typeof cod_interConsulta == "undefined" || !cod_interConsulta || cod_interConsulta == "0") {
		obtenerOCrearInterConsultaMovimientoActual(function () {
			crearProyectoGastoDesdeVentanaMovimiento();
		});
		return;
	}
	var contextoHilo = obtenerContextoHiloMovimientoActual();
	var sugerencia = (contextoHilo.descripcion || contextoHilo.conceptoTexto || contextoHilo.nombreHilo || "").trim();
	crearProyectoGastoDesdeHilo(sugerencia, {
		silencioso: true,
		alCrear: function (idProyecto, nombreProyecto) {
			buscarProyectosVistaSelecc(idProyecto, function () {
				var titulo = document.getElementById("tituloMovimientoFinanciero");
				if (titulo && nombreProyecto) {
					titulo.textContent = "Nuevo proyecto - " + nombreProyecto;
				}
				var chip = document.getElementById("chipContextoMovimientoFinanciero");
				if (chip) {
					var baseChip = chip.textContent || "Proyecto del hilo";
					baseChip = baseChip.replace(/\s\|\sProyecto:.*/, "");
					chip.textContent = baseChip + " | Proyecto: " + nombreProyecto;
					chip.style.display = "";
				}
				actualizarVisibilidadCantidadCuotasGasto();
				actualizarVistaPreviaPlanificacionGasto();
				enfocarPagoYPlanificacionMovimientoFinanciero();
			});
		}
	});
}

function crearProyectoGastoDesdeBotonHilo(evento, boton) {
	if (evento && evento.stopPropagation) {
		evento.stopPropagation();
	}
	if (!boton) {
		return;
	}
	if (evento && evento.preventDefault) {
		evento.preventDefault();
	}
	var codInterConsultaBoton= boton.getAttribute("data-cod-interconsulta") || "";
	var nombreHilo= boton.getAttribute("data-nombre-hilo") || "";
	var sugerencia= boton.getAttribute("data-sugerencia-proyecto") || nombreHilo;
	var conceptoId= boton.getAttribute("data-concepto-id") || "";
	var conceptoNombre= boton.getAttribute("data-concepto-nombre") || sugerencia;
	var tipoMovimiento= boton.getAttribute("data-tipo-movimiento") || "Egreso";
	var localId= boton.getAttribute("data-local-id") || "";
	if (!codInterConsultaBoton || codInterConsultaBoton == "0") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA INTERCONSULTA.");
		return;
	}
	cod_interConsulta= codInterConsultaBoton;
	var inputInterConsulta= document.getElementById("inptAbmInterConsultaGasto");
	if (inputInterConsulta) {
		inputInterConsulta.value= nombreHilo;
	}
	crearProyectoGastoDesdeHilo(sugerencia, {
		silencioso: true,
		alCrear: function (idProyecto, nombreProyecto) {
			abrirMovimientoFinanciero({
				modo: "crear",
				tipoMovimiento: tipoMovimiento,
				categoriaFlujo: "Proyecto del hilo",
				categoriaCodigo: "",
				conceptoId: conceptoId,
				conceptoNombre: conceptoNombre,
				localId: localId,
				interconsultaId: codInterConsultaBoton,
				interconsultaNombre: nombreHilo,
				proyectoId: idProyecto,
				proyectoNombre: nombreProyecto,
				esNuevoProyecto: true,
				focoPlanificacion: true
			});
		}
	});
}

function verificarcamposGasto() {
	var inptMontoGasto = document.getElementById('inptMontoGasto').value
	var inptDescripcionGasto = document.getElementById('inptDescripcionGasto').value
	var inptFechaGasto = document.getElementById('inptFechaGasto').value
	var inptEstadoGasto = document.getElementById('inptEstadoGasto').value
	var inptArregloGasto = document.getElementById('inptArregloGasto').value
	var inptlocalMisGastos = document.getElementById('inptlocalMisGastos').value
	var inptTipoGasto = document.getElementById('inptTipoGasto').value
	var inptNroBoletaGasto = document.getElementById('inptNroBoletaGasto').value
	var inptBancoGasto = document.getElementById('inptBancoGasto').value
	var inptCuentaGasto = document.getElementById('inptCuentaGasto').value
	var inptCantCuotaGasto = Number(document.getElementById('inptCantCuotaGasto').value || 0)
	var inptPeriodicidadGasto = document.getElementById('inptPeriodicidadGasto').value;
	var inptProyectoGasto = document.getElementById('inptProyectoGasto').value;
	let actualizar_caja= false;

    const inptMotivoMisGastos= document.getElementById('inptMotivoMisGastos').value;

    if (inptMotivoMisGastos == '') {
        ver_vetana_informativa("FALTO SELECCIONAR UN MOTIVO DE LA LISTA.");
        return false;
    }
	if (inptMontoGasto == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MONTO DEL GASTO")
		return false;
	}
	if (inptDescripcionGasto == "") {
		ver_vetana_informativa("FALTO INGRESAR LA DESCRIPCION DEL GASTO")
		return false;
	}
	if (inptFechaGasto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DEL GASTO")
		return false;
	}
	if (inptCantCuotaGasto > 1 && inptPeriodicidadGasto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA PERIODICIDAD DEL GASTO")
		return false;
	}

	// Se evalua si ya existen gastos asociados
	if (gastoSeleccionadoTieneCuotasAsociadas()) {
		if (inptPeriodicidadGasto == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA PERIODICIDAD DEL GASTO")
			return false;
		}
		if (inptProyectoGasto == "" || inptProyectoGasto == "0") {
			ver_vetana_informativa("FALTO SELECCIONAR EL PROYECTO DEL GASTO")
			return false;
		}
	}
	var accion = "";
	if (idAbmGasto != "") {
		accion = "editar";
		if(controlacceso("EDITARLISTADOEGRESOINGRESO","accion")==false){return;}	
	} else {
		if(controlacceso("INSERTARLISTADOEGRESOINGRESO","accion")==false){return;}	
		accion = "nuevo";
	}
	abmgastos(inptArregloGasto,inptNroBoletaGasto, inptBancoGasto , inptCuentaGasto ,inptMontoGasto, inptDescripcionGasto, inptFechaGasto, inptEstadoGasto, idAbmGasto, inptTipoGasto, inptlocalMisGastos, inptMotivoMisGastos,accion, inptCantCuotaGasto, inptPeriodicidadGasto, inptProyectoGasto);
}

function gastoSeleccionadoTieneCuotasAsociadas() {
	return document.getElementById('divGastoAsociadosGastos').getAttribute('data-es-credito') == 'true';
}

function abmgastos(Arreglo,nroboleta ,banco ,nrocuenta,monto, descripcion, fecha, estado, idgastos, tipo, cod_local,cod_motivoFK, accion, cantCuotas= 0, periodicidad= "", proyecto_gasto="") {
	verCerrarEfectoCargando("1")
	let editar_cuotas= true;
	
	if (accion == "editar" && gastoSeleccionadoTieneCuotasAsociadas()) {
		editar_cuotas= confirm("¿Modificar tambien las cuotas asociadas?");
	}
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idgastos", idgastos)
	datos.append("monto", monto)
	datos.append("motivo", descripcion);
    datos.append("cod_motivoFK", cod_motivoFK)
	datos.append("fecha", fecha)
	datos.append("estado", estado)
	datos.append("tipo", tipo)
	datos.append("cod_local", cod_local)
	datos.append("cod_proyecto_gastoFK", proyecto_gasto)
	datos.append("codcaja", cajapredeterminada)
	datos.append("idaperturacierrecaja", idabmAperturacierrecaja)
	datos.append("nroboleta", nroboleta)
	datos.append("banco", banco)
	datos.append("Arreglo", Arreglo)
	datos.append("nrocuenta", nrocuenta)
	datos.append("foto", fotoGasto);
    datos.append("ext", extGasto);
	datos.append("foto_documento_firmado", fotoDocumentoFirmadoGasto);
    datos.append("ext_documento_firmado", extDocumentoFirmadoGasto);
	datos.append("cod_interConsultaFK", cod_interConsulta);
	datos.append("cantCuotas", cantCuotas);
	datos.append("periodicidad", periodicidad);
	datos.append("editar_cuotas", (editar_cuotas ? "true" : "false"));
	
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
			xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var porce= ~~((evt.loaded / evt.total) * 100); 
		if(porce>90){
		porce=Number(porce)-7				
		}
		document.getElementById("lbltitulomensaje_b").innerHTML="Cargando<br>("+porce+"%)";
		var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("")
		manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			verCerrarEfectoCargando("")
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					if (Number.isNaN(parseInt(datos["2"]))) {
						ver_vetana_informativa(datos["2"]);
						return false;
					}
				   if(accion=="nuevo"){
						ImprimirTicketEgreso()
					}
					
					ver_vetana_informativa("Datos guardados.", "", "info");
					limpiarcamposGasto()

					idAbmGasto = "";
					switch (ventanaAnterior[ventanaAnterior.length - 1]) {
						case 'divListadoInterConsulta':
							buscarInterConsultasYContenido(cod_interConsulta);
							document.getElementById('divAbmGastos').style.display= "none";
							break;
						default: 
							buscarabmGasto();
							verCerrarVentanaAbmGasto(false, false);
							break;
					}
					comprobarLimiteMotivo(cod_motivoFK, cod_local);
				} else {
					ver_vetana_informativa(datos["2"]);
				}				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function obtenerGastosAsociados(id_gasto) {
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", 'obtenerGastosAsociados');
    datos.append("idgastos", id_gasto);
	document.getElementById('divGastoAsociadosGastos').setAttribute('data-es-credito', 'false');
	document.getElementById('divGastoAsociadosGastos').style.display= "none";
	document.getElementById('divTableProyecto').innerHTML= "";
	
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
			xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var porce= ~~((evt.loaded / evt.total) * 100); 
		if(porce>90){
		porce=Number(porce)-7				
		}
		document.getElementById("lbltitulomensaje_b").innerHTML="Cargando<br>("+porce+"%)";
		var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("")
		manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			verCerrarEfectoCargando("")
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var gastoPrincipal = datos["3"] || {};
					var modalidad = ((gastoPrincipal["modalidad"] || "") + "").toLowerCase();
					var codProyecto = ((gastoPrincipal["cod_proyecto_gastoFK"] || "") + "");
					var cantidadGastos = parseInt(datos["6"] || "0");
					var esCredito = (modalidad == "credito" || (codProyecto != "" && codProyecto != "0") || cantidadGastos > 1);

					if (datos["2"] && esCredito) {
						document.getElementById('divGastoAsociadosGastos').setAttribute('data-es-credito', 'true');
						document.getElementById('divGastoAsociadosGastos').style.display= "none";
						document.getElementById('divNombreProyectoGasto').innerHTML= "";
						document.getElementById('divTableProyecto').innerHTML= "";
					} else {
						document.getElementById('divGastoAsociadosGastos').setAttribute('data-es-credito', 'false');
						document.getElementById('divGastoAsociadosGastos').style.display= "none";
						document.getElementById('divNombreProyectoGasto').innerHTML= "";
						document.getElementById('divTableProyecto').innerHTML= "";
					}
					actualizarVisibilidadCantidadCuotasGasto();
				}				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

var extractoActual= null;
var bsExtracto = null;
var botonExtractoActivo = null;

function limpiarBotonExtractoActivo() {
	document.querySelectorAll(".btn-menu-extracto.active").forEach(function (btn) {
		btn.classList.remove("active");
	});
	botonExtractoActivo = null;
}

function establecerBotonExtractoActivo(id_gastos) {
	limpiarBotonExtractoActivo();
	botonExtractoActivo = document.querySelector('.btn-menu-extracto[data-id="' + id_gastos + '"]');
	if (botonExtractoActivo) {
		botonExtractoActivo.classList.add("active");
	}
}

function inicializarEventosExtracto(panelExtracto) {
	if (!panelExtracto || panelExtracto.dataset.extractoEventosInicializados == "1") {
		return;
	}
	panelExtracto.dataset.extractoEventosInicializados = "1";
	panelExtracto.addEventListener("hidden.bs.collapse", function () {
		limpiarBotonExtractoActivo();
		extractoActual = null;
	});
}

function cerrarExtractoGasto() {
	const panelExtracto = document.getElementById("collapseExtracto");
	if (!panelExtracto) {
		return;
	}
	const instancia = bootstrap.Collapse.getOrCreateInstance(panelExtracto, { toggle: false });
	instancia.hide();
}

function mostrarExtractoGasto(id_gastos) {
	const panelExtracto = document.getElementById("collapseExtracto");
	if (!panelExtracto) {
		return;
	}

	inicializarEventosExtracto(panelExtracto);

	bsExtracto = bootstrap.Collapse.getOrCreateInstance(panelExtracto, { toggle: false });
    const panelAbierto = panelExtracto.classList.contains("show");
	if (extractoActual === id_gastos && panelAbierto) {
      bsExtracto.hide();
      return;
    }

	extractoActual = id_gastos;
	establecerBotonExtractoActivo(id_gastos);
	bsExtracto.show();

	document.getElementById('tableExtractoGastosInterConsulta').innerHTML= paginacargando;
	document.getElementById('tituloExtractoGastosInterconsulta').innerHTML= "Cargando...";
	document.getElementById('tableExtractoGastosInterConsultaTotal').innerHTML= "0";

	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", 'obtenerGastosAsociados');
    datos.append("idgastos", id_gastos);

	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
			xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var porce= ~~((evt.loaded / evt.total) * 100); 
		if(porce>90){
		porce=Number(porce)-7				
		}
		document.getElementById("lbltitulomensaje_b").innerHTML="Cargando<br>("+porce+"%)";
		var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("")
		manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			verCerrarEfectoCargando("")
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				   document.getElementById('tituloExtractoGastosInterconsulta').innerHTML= "Extracto de " + datos["4"];
				   document.getElementById('tableExtractoGastosInterConsulta').innerHTML= datos["2"];
				   document.getElementById('tableExtractoGastosInterConsultaTotal').innerHTML= datos["5"];
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function comprobarLimiteMotivo(cod_motivo, cod_local) {
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", 'verficiarLimiteMotivo')
    datos.append("cod_motivo", cod_motivo)
    datos.append("cod_local", cod_local)
	
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPresupuestoMotivoGasto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
			xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var porce= ~~((evt.loaded / evt.total) * 100); 
		if(porce>90){
		porce=Number(porce)-7				
		}
		document.getElementById("lbltitulomensaje_b").innerHTML="Cargando<br>("+porce+"%)";
		var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("")
		manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			verCerrarEfectoCargando("")
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				   const limite = parseInt(datos["2"]);
				   const total = parseInt(datos["3"].toString().replace('.',''));

				   if (!(Number.isNaN(limite)) && total >= limite && limite > 0) {
					   ver_vetana_informativa("Ha llegado al limite permitido para el "+datos[4]+" motivo de gasto.");
				   } else if (total >= (limite * 0.9)) {
					   ver_vetana_informativa("Esta llegando al limite presupuestado para el "+datos[4]+" motivo de gasto.");
				   }
				}				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

var fotoGasto= "";
var extGasto= "";
var fotoDocumentoFirmadoGasto= "";
var extDocumentoFirmadoGasto= "";
function subirImagenGasto(cod_abmGasto) {
    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("funt", "cargar_imagen");
    datos.append("idgastos", cod_abmGasto);
    datos.append("foto", fotoGasto);
    datos.append("ext", extGasto);
	datos.append("foto_documento_firmado", fotoDocumentoFirmadoGasto);
    datos.append("ext_documento_firmado", extDocumentoFirmadoGasto);
    
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmgasto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					ver_vetana_informativa("Datos guardados.", "", "info");
					limpiarcamposGasto()

					idAbmGasto = "";
					buscarabmGasto();
					verCerrarVentanaAbmGasto(false, false);
				} else {
					throw new Error("Error producido en subirImagenGasto de JavaScript.");
                }
				verCerrarEfectoCargando("");
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				verCerrarEfectoCargando("");
			}
		}
	});
}

function ImprimirTicketEgreso(){
	var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()+1
	if(mes<10){
		mes="0"+mes;
	}
	var hora =f.getHours()
	if(hora<10){
		hora="0"+hora;
	}
	var min =f.getMinutes()
	if(min<10){
		min="0"+min;
	}

pagina="<div  style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >BOLETA DE CONTROL</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Imp.:</b></td>"
+"<td style=''>"+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Usuario :</b></td>"
+"<td style=''>"+ document.getElementById("ptituloUser2").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Local:</b></td>"
+"<td style=''>"+ $("select[id=inptlocalMisGastos]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<br>"
+"<br>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Caja:</b></td>"
+"<td style=''>"+ $("select[id=inptcajaAperturaCierreCaja]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Tipo :</b></td>"
+"<td style=''>"+ document.getElementById("inptTipoGasto").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Monto :</b></td>"
+"<td style=''>"+document.getElementById("inptMontoGasto").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<br>"
+"<br>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Motivo :</b></td>"
+"<td style=''>"+document.getElementById("inptDescripcionGasto").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Boleta Nro :</b></td>"
+"<td style=''>"+document.getElementById("inptNroBoletaGasto").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Banco :</b></td>"
+"<td style=''>"+document.getElementById("inptBancoGasto").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Cuenta :</b></td>"
+"<td style=''>"+document.getElementById("inptCuentaGasto").value+"</td>"
+"</tr>"
+"</table>"
+"</center>"
+"</div>"


var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
}

function textoSeguroImpresion(valor) {
	return String(valor == null ? "" : valor)
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

function obtenerFechaHoraImpresionEgresoIngreso() {
	var f = new Date();
	var dia = f.getDate();
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1;
	if (mes < 10) {
		mes = "0" + mes;
	}
	var hora = f.getHours();
	if (hora < 10) {
		hora = "0" + hora;
	}
	var min = f.getMinutes();
	if (min < 10) {
		min = "0" + min;
	}
	return f.getFullYear() + "-" + mes + "-" + dia + " " + hora + ":" + min;
}

function filaDatoImpresionEgresoIngreso(titulo, valor) {
	return "<tr>"
		+ "<td style='width:32%;padding:8px;border:1px solid #d7d7d7;background:#f6f6f6;font-weight:bold;'>" + titulo + "</td>"
		+ "<td style='padding:8px;border:1px solid #d7d7d7;'>" + textoSeguroImpresion(valor) + "</td>"
		+ "</tr>";
}

function imprimirRegistroEgresoIngreso() {
	if (idAbmGasto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO");
		return;
	}

	var concepto = $("select[id=inptMotivoMisGastos]").children(":selected").text();
	var local = $("select[id=inptlocalMisGastos]").children(":selected").text();
	var usuario = usuarioCreadorEgresoIngreso || (document.getElementById("ptituloUser2") ? document.getElementById("ptituloUser2").innerHTML : "");
	var usuarioAutorizacion = document.getElementById("inptUsuarioAutorizacionEgreso").value;
	var estado = document.getElementById("inptEstadoGasto").value;
	var imagen = document.getElementById("imgfotoGasto").style.backgroundImage || "";
	imagen = imagen.replace(/^url\(["']?/, "").replace(/["']?\)$/, "");
	var imagenDocumentoFirmado = document.getElementById("imgdocumentoFirmadoGasto").style.backgroundImage || "";
	imagenDocumentoFirmado = imagenDocumentoFirmado.replace(/^url\(["']?/, "").replace(/["']?\)$/, "");

	var comprobante = "";
	if (imagen != "" && imagen.indexOf("imagenphoto.png") == -1) {
		comprobante = "<div style='margin-top:18px;page-break-inside:avoid;'>"
			+ "<p class='pTituloC' style='font-weight:bold;margin-bottom:8px;'>COMPROBANTE ADJUNTO</p>"
			+ "<img src='" + textoSeguroImpresion(imagen) + "' style='max-width:100%;max-height:420px;border:1px solid #d7d7d7;padding:6px;box-sizing:border-box;'>"
			+ "</div>";
	}
	var documentoFirmado = "";
	if (imagenDocumentoFirmado != "" && imagenDocumentoFirmado.indexOf("imagenphoto.png") == -1) {
		documentoFirmado = "<div style='margin-top:18px;page-break-inside:avoid;'>"
			+ "<p class='pTituloC' style='font-weight:bold;margin-bottom:8px;'>DOCUMENTO FIRMADO</p>"
			+ "<img src='" + textoSeguroImpresion(imagenDocumentoFirmado) + "' style='max-width:100%;max-height:420px;border:1px solid #d7d7d7;padding:6px;box-sizing:border-box;'>"
			+ "</div>";
	}

	var pagina = "<div style='background-color:#fff;color:#111;font-family:Arial, sans-serif;width:90%;margin:0 auto;'>"
		+ "<center><h1 class='pTituloD' style='font-size:18px;margin-bottom:6px;'>REGISTRO DE EGRESO / INGRESO</h1></center>"
		+ "<table class='TableRepor0' style='width:100%;margin-bottom:16px;'>"
		+ "<tr>"
		+ "<td style='width:25%;text-align:left'><p class='pTituloC'><b>Codigo</b></p><p class='pTituloC'>" + textoSeguroImpresion(idAbmGasto) + "</p></td>"
		+ "<td style='width:25%;text-align:left'><p class='pTituloC'><b>Tipo</b></p><p class='pTituloC'>" + textoSeguroImpresion(document.getElementById("inptTipoGasto").value) + "</p></td>"
		+ "<td style='width:25%;text-align:left'><p class='pTituloC'><b>Estado</b></p><p class='pTituloC'>" + textoSeguroImpresion(estado) + "</p></td>"
		+ "<td style='width:25%;text-align:left'><p class='pTituloC'><b>Fecha impresion</b></p><p class='pTituloC'>" + textoSeguroImpresion(obtenerFechaHoraImpresionEgresoIngreso()) + "</p></td>"
		+ "</tr>"
		+ "</table>"
		+ "<table style='width:100%;border-collapse:collapse;font-size:12px;'>"
		+ filaDatoImpresionEgresoIngreso("Monto", document.getElementById("inptMontoGasto").value + " Gs.")
		+ filaDatoImpresionEgresoIngreso("Fecha del movimiento", document.getElementById("inptFechaGasto").value)
		+ filaDatoImpresionEgresoIngreso("Concepto contable", concepto)
		+ filaDatoImpresionEgresoIngreso("Descripcion", document.getElementById("inptDescripcionGasto").value)
		+ filaDatoImpresionEgresoIngreso("Local", local)
		+ filaDatoImpresionEgresoIngreso("Usuario", usuario)
		+ filaDatoImpresionEgresoIngreso("Nro de boleta", document.getElementById("inptNroBoletaGasto").value)
		+ filaDatoImpresionEgresoIngreso("Banco", document.getElementById("inptBancoGasto").value)
		+ filaDatoImpresionEgresoIngreso("Cuenta", document.getElementById("inptCuentaGasto").value)
		+ filaDatoImpresionEgresoIngreso("Interconsulta", document.getElementById("inptAbmInterConsultaGasto").value)
		+ filaDatoImpresionEgresoIngreso("Usuario autorizacion", usuarioAutorizacion)
		+ filaDatoImpresionEgresoIngreso("Fecha autorizacion", document.getElementById("inptFechaAutorizacionEgreso").value)
		+ "</table>"
		+ comprobante
		+ documentoFirmado
		+ "<table style='width:100%;margin-top:70px;border-collapse:collapse;font-size:12px;page-break-inside:avoid;'>"
		+ "<tr>"
		+ "<td style='width:50%;text-align:center;padding:0 28px;'>"
		+ "<div style='border-top:1px solid #111;padding-top:8px;min-height:38px;'>" + textoSeguroImpresion(usuario) + "<br><b>Firma responsable del registro</b></div>"
		+ "</td>"
		+ "<td style='width:50%;text-align:center;padding:0 28px;'>"
		+ "<div style='border-top:1px solid #111;padding-top:8px;min-height:38px;'>" + textoSeguroImpresion(usuarioAutorizacion) + "<br><b>Firma responsable de autorizacion</b></div>"
		+ "</td>"
		+ "</tr>"
		+ "</table>"
		+ "</div>";

	localStorage.setItem("reporte", pagina);
	localStorage.setItem("tipo", "ticket");
	window.open("/GoodVentaAsisCap/system/reportInformes.html");
}

function checkfiltroshistorialegresoingreso(d){
	if(d=="1"){
	document.getElementById('inptCheckingresoegreso1').checked=true
	document.getElementById('inptCheckingresoegreso2').checked=false	
     
	 	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarGastoF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarGastoF2').value = f.getFullYear() + "-" + mes + "-" + dia;
	 
	}else{		
	document.getElementById('inptCheckingresoegreso1').checked=false
	document.getElementById('inptCheckingresoegreso2').checked=true
	document.getElementById('inptBuscarGastoF1').value="";
      document.getElementById('inptBuscarGastoF2').value="";
	
	}
	actualizarEncabezadoFlujoGasto();
}

function obtenerFechaLocalISOFlujoGasto() {
	var fecha = new Date();
	var mes = fecha.getMonth() + 1;
	var dia = fecha.getDate();
	if (mes < 10) { mes = "0" + mes; }
	if (dia < 10) { dia = "0" + dia; }
	return fecha.getFullYear() + "-" + mes + "-" + dia;
}

function crearFechaFlujoGasto(valor) {
	if (!valor) { return null; }
	var partes = valor.split("-");
	if (partes.length !== 3) { return null; }
	return new Date(Number(partes[0]), Number(partes[1]) - 1, Number(partes[2]));
}

function rellenarDosDigitosFlujoGasto(valor) {
	return valor < 10 ? "0" + valor : String(valor);
}

function nombreMesFlujoGasto(fecha) {
	var meses = [
		"Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
		"Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
	];
	return meses[fecha.getMonth()];
}

function formatearFechaCortaFlujoGasto(fecha) {
	if (!fecha) { return ""; }
	return rellenarDosDigitosFlujoGasto(fecha.getDate()) + "/" +
		rellenarDosDigitosFlujoGasto(fecha.getMonth() + 1) + "/" +
		fecha.getFullYear();
}

function formatearMesInputFlujoGasto(fecha) {
	if (!fecha) { return ""; }
	return fecha.getFullYear() + "-" + rellenarDosDigitosFlujoGasto(fecha.getMonth() + 1);
}

function obtenerMesPeriodoFlujoGasto(fecha1Valor, fecha2Valor) {
	var fecha1 = crearFechaFlujoGasto(fecha1Valor);
	var fecha2 = crearFechaFlujoGasto(fecha2Valor);
	if (fecha1) {
		return formatearMesInputFlujoGasto(fecha1);
	}
	if (fecha2) {
		return formatearMesInputFlujoGasto(fecha2);
	}
	return "";
}

function obtenerEtiquetaPeriodoFlujoGasto(fecha1Valor, fecha2Valor) {
	var hoyISO = obtenerFechaLocalISOFlujoGasto();
	var fecha1 = crearFechaFlujoGasto(fecha1Valor);
	var fecha2 = crearFechaFlujoGasto(fecha2Valor);

	if (!fecha1Valor && !fecha2Valor) {
		return "Todos los movimientos";
	}
	if (fecha1Valor === hoyISO && fecha2Valor === hoyISO) {
		return "Hoy";
	}
	if (fecha1 && fecha2 && fecha1.getFullYear() === fecha2.getFullYear() && fecha1.getMonth() === fecha2.getMonth()) {
		var ultimoDiaMes = new Date(fecha1.getFullYear(), fecha1.getMonth() + 1, 0).getDate();
		var nombreMes = nombreMesFlujoGasto(fecha1) + " " + fecha1.getFullYear();
		if (fecha1.getDate() === 1 && fecha2.getDate() === ultimoDiaMes) {
			return nombreMes;
		}
		if (fecha1.getDate() === 1 && fecha2Valor === hoyISO) {
			return nombreMes + " - hasta hoy";
		}
		return nombreMes + " - " + rellenarDosDigitosFlujoGasto(fecha1.getDate()) + " al " + rellenarDosDigitosFlujoGasto(fecha2.getDate());
	}
	if (fecha1 && fecha2) {
		return formatearFechaCortaFlujoGasto(fecha1) + " al " + formatearFechaCortaFlujoGasto(fecha2);
	}
	if (fecha1) {
		return "Desde " + formatearFechaCortaFlujoGasto(fecha1);
	}
	if (fecha2) {
		return "Hasta " + formatearFechaCortaFlujoGasto(fecha2);
	}
	return "Periodo actual";
}

function obtenerLocalFlujoGasto() {
	var selectLocal = document.getElementById("inptlocalMisGastosBusca");
	if (!selectLocal) { return "Todos los locales"; }
	var opcion = selectLocal.options[selectLocal.selectedIndex];
	var texto = opcion ? opcion.text : "";
	if (!texto || texto.toUpperCase() === "TODOS" || selectLocal.value === "") {
		return "Todos los locales";
	}
	return texto;
}

function abrirSelectorMesFlujoGasto() {
	var inputMes = document.getElementById("inptPeriodoMesGasto");
	if (!inputMes) { return; }
	if (inputMes.showPicker) {
		inputMes.showPicker();
	} else {
		inputMes.focus();
	}
}

function prepararSelectorMesFlujoGasto() {
	var titulo = document.getElementById("txtPeriodoGasto");
	if (!titulo) { return null; }
	var inputMes = document.getElementById("inptPeriodoMesGasto");
	if (!inputMes) {
		inputMes = document.createElement("input");
		inputMes.type = "month";
		inputMes.id = "inptPeriodoMesGasto";
		inputMes.className = "flujo-caja-periodo__mes flujo-caja-periodo__mes--superior";
		titulo.insertAdjacentElement("afterend", inputMes);
	}
	inputMes.title = "Seleccionar mes";
	inputMes.setAttribute("aria-label", "Seleccionar mes del flujo");
	inputMes.onchange = function () {
		seleccionarMesFlujoGasto(this.value);
	};
	titulo.setAttribute("role", "button");
	titulo.setAttribute("tabindex", "0");
	titulo.title = "Seleccionar mes";
	titulo.onclick = abrirSelectorMesFlujoGasto;
	titulo.onkeydown = function (evento) {
		if (evento.key == "Enter" || evento.key == " ") {
			evento.preventDefault();
			abrirSelectorMesFlujoGasto();
		}
	};
	return inputMes;
}

function seleccionarMesFlujoGasto(mesValor) {
	if (!/^\d{4}-\d{2}$/.test(mesValor || "")) {
		return;
	}
	var partes = mesValor.split("-");
	var anio = Number(partes[0]);
	var mes = Number(partes[1]);
	var ultimoDia = new Date(anio, mes, 0).getDate();
	var fechaInicio = mesValor + "-01";
	var fechaFin = mesValor + "-" + rellenarDosDigitosFlujoGasto(ultimoDia);
	var inputInicio = document.getElementById("inptBuscarGastoF1");
	var inputFin = document.getElementById("inptBuscarGastoF2");
	if (inputInicio) { inputInicio.value = fechaInicio; }
	if (inputFin) { inputFin.value = fechaFin; }
	if (document.getElementById("inptCheckingresoegreso1")) {
		document.getElementById("inptCheckingresoegreso1").checked = true;
	}
	if (document.getElementById("inptCheckingresoegreso2")) {
		document.getElementById("inptCheckingresoegreso2").checked = false;
	}
	actualizarEncabezadoFlujoGasto();
	if (typeof buscarabmGasto == "function") {
		buscarabmGasto();
	}
}

function actualizarEncabezadoFlujoGasto() {
	var titulo = document.getElementById("txtPeriodoGasto");
	if (!titulo) { return; }
	var fecha1 = document.getElementById("inptBuscarGastoF1") ? document.getElementById("inptBuscarGastoF1").value : "";
	var fecha2 = document.getElementById("inptBuscarGastoF2") ? document.getElementById("inptBuscarGastoF2").value : "";
	var detalle = document.getElementById("txtPeriodoGastoDetalle");
	var inputMes = prepararSelectorMesFlujoGasto();
	titulo.textContent = obtenerEtiquetaPeriodoFlujoGasto(fecha1, fecha2);
	if (inputMes) {
		inputMes.value = obtenerMesPeriodoFlujoGasto(fecha1, fecha2);
	}
	if (detalle) {
		detalle.textContent = obtenerLocalFlujoGasto() + " - Ingresos -> Costos variables -> Gastos fijos -> Administracion";
	}
}

function numeroFlujoGastoDesdeRespuesta(valor) {
	if (valor === null || typeof valor === "undefined") {
		return 0;
	}
	if (typeof valor === "number") {
		return isNaN(valor) ? 0 : valor;
	}
	var texto = (valor + "")
		.replace(/Gs\.?/gi, "")
		.replace(/\s/g, "")
		.replace(/\./g, "")
		.replace(",", ".");
	var numero = parseFloat(texto);
	return isNaN(numero) ? 0 : numero;
}

function formatearMontoResumenFlujoGasto(valor) {
	var monto = Math.round(Math.abs(numeroFlujoGastoDesdeRespuesta(valor)));
	if (typeof separadordemilesnumero == "function") {
		return separadordemilesnumero(monto.toString());
	}
	return monto.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function actualizarResumenNetoFlujoGasto(ingresosValor, costosVariablesValor, gastosFijosValor, otrosEgresosValor, administracionValor) {
	var resumen = document.getElementById("resumenNetoFlujoGasto");
	if (!resumen) { return; }
	var ingresos = numeroFlujoGastoDesdeRespuesta(ingresosValor);
	var costosVariables = numeroFlujoGastoDesdeRespuesta(costosVariablesValor);
	var gastosFijos = numeroFlujoGastoDesdeRespuesta(gastosFijosValor);
	var otrosEgresos = numeroFlujoGastoDesdeRespuesta(otrosEgresosValor);
	var administracion = numeroFlujoGastoDesdeRespuesta(administracionValor);
	var totalEgresos = costosVariables + gastosFijos + administracion + otrosEgresos;
	var resultado = ingresos - totalEgresos;
	var estado = resultado > 0 ? "positivo" : (resultado < 0 ? "negativo" : "neutro");
	var flecha = resultado > 0 ? "&uarr;" : (resultado < 0 ? "&darr;" : "=");
	var signo = resultado < 0 ? "-" : "";

	resumen.className = "flujo-neto-resumen flujo-neto-resumen--" + estado;
	resumen.title = "Ingresos: " + formatearMontoResumenFlujoGasto(ingresos) + " Gs. | Egresos: " + formatearMontoResumenFlujoGasto(totalEgresos) + " Gs.";
	resumen.innerHTML =
		"<span class='flujo-neto-resumen__flecha'>" + flecha + "</span>" +
		"<div>" +
		"<small>Resultado del flujo</small>" +
		"<strong>" + signo + formatearMontoResumenFlujoGasto(resultado) + " Gs.</strong>" +
		"<em>Ingresos - egresos</em>" +
		"</div>";
}

function actualizarResumenNetoFlujoDesdeDatos(datos) {
	if (!datos) {
		actualizarResumenNetoFlujoGasto(0, 0, 0);
		actualizarComposicionFlujoGasto(null);
		return;
	}
	var administracionAsignada = datos[14] || (datos[13] && datos[13].totales ? datos[13].totales.administracion_asignada : 0);
	actualizarResumenNetoFlujoGasto(datos[5], datos[6], datos[7], datos[8], administracionAsignada);
	actualizarComposicionFlujoGasto(datos[13] || null, datos);
}

function escaparHtmlFlujoGasto(valor) {
	return ((valor === null || typeof valor === "undefined") ? "" : valor + "")
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

function porcentajeFlujoGasto(valor, base) {
	valor = numeroFlujoGastoDesdeRespuesta(valor);
	base = numeroFlujoGastoDesdeRespuesta(base);
	if (base <= 0) { return 0; }
	return (valor / base) * 100;
}

function formatearPorcentajeFlujoGasto(valor) {
	var numero = Math.round((Number(valor) || 0) * 10) / 10;
	var texto = (Math.abs(numero % 1) < 0.05) ? numero.toFixed(0) : numero.toFixed(1);
	return texto.replace(".", ",") + "%";
}

function formatearPorcentajeSobreBaseFlujoGasto(valor, base) {
	valor = numeroFlujoGastoDesdeRespuesta(valor);
	base = numeroFlujoGastoDesdeRespuesta(base);
	if (base <= 0 && valor > 0) {
		return "sin ingresos";
	}
	return formatearPorcentajeFlujoGasto(porcentajeFlujoGasto(valor, base));
}

function formatearMontoFlujoConSigno(valor) {
	var numero = numeroFlujoGastoDesdeRespuesta(valor);
	var signo = numero < 0 ? "-" : "";
	return signo + formatearMontoResumenFlujoGasto(numero) + " Gs.";
}

function formatearPorCada100FlujoGasto(valor) {
	var numero = Math.round((Number(valor) || 0) * 10) / 10;
	var texto = (Math.abs(numero % 1) < 0.05) ? numero.toFixed(0) : numero.toFixed(1);
	return texto.replace(".", ",") + " Gs.";
}

function obtenerTotalesComposicionFlujoGasto(resumen, datosFallback) {
	var totales = resumen && resumen.totales ? resumen.totales : {};
	return {
		ingresos: numeroFlujoGastoDesdeRespuesta(totales.ingresos || (datosFallback ? datosFallback[5] : 0)),
		costosVariables: numeroFlujoGastoDesdeRespuesta(totales.costos_variables || (datosFallback ? datosFallback[6] : 0)),
		gastosFijos: numeroFlujoGastoDesdeRespuesta(totales.gastos_fijos || (datosFallback ? datosFallback[7] : 0)),
		administracion: numeroFlujoGastoDesdeRespuesta(totales.administracion_asignada || (datosFallback ? datosFallback[14] : 0)),
		sinCategorizar: numeroFlujoGastoDesdeRespuesta(totales.sin_categorizar || (datosFallback ? datosFallback[8] : 0))
	};
}

function claseCategoriaComposicionFlujoGasto(codigo) {
	codigo = (codigo || "") + "";
	if (codigo == "ingreso") { return "ingresos"; }
	if (codigo == "directo") { return "variables"; }
	if (codigo == "operativo") { return "fijos"; }
	if (codigo == "administracion") { return "administracion"; }
	return "sin-categoria";
}

function tituloCategoriaComposicionFlujoGasto(codigo) {
	if (codigo == "ingreso") { return "Ingresos"; }
	if (codigo == "directo") { return "Costos variables"; }
	if (codigo == "operativo") { return "Gastos fijos"; }
	if (codigo == "administracion") { return "Administracion asignada"; }
	return "Sin categorizar";
}

function categoriasComposicionFlujoGasto(resumen) {
	var categorias = resumen && resumen.categorias ? resumen.categorias : [];
	var mapa = {};
	Array.prototype.forEach.call(categorias, function (categoria) {
		mapa[categoria.codigo || "sinCategoria"] = categoria;
	});
	return ["ingreso", "directo", "operativo", "administracion", "sinCategoria"].map(function (codigo) {
		return mapa[codigo] || {
			codigo: codigo,
			titulo: tituloCategoriaComposicionFlujoGasto(codigo),
			total: 0,
			conceptos: []
		};
	});
}

function construirSegmentoComposicionFlujoGasto(clase, etiqueta, porcentaje, monto, escala) {
	var alto = escala > 0 ? Math.max(0, (porcentaje / escala) * 100) : 0;
	if (porcentaje <= 0 || alto <= 0) {
		return "";
	}
	var claseChico = alto < 9 ? " flujo-composicion-segmento--chico" : "";
	return "<div class='flujo-composicion-segmento flujo-composicion-segmento--" + clase + claseChico + "' style='height:" + alto + "%' title='" + escaparHtmlFlujoGasto(etiqueta + ": " + formatearPorcentajeFlujoGasto(porcentaje) + " - " + formatearMontoFlujoConSigno(monto)) + "'>"
		+ "<b>" + formatearPorcentajeFlujoGasto(porcentaje) + "</b>"
		+ "<span>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(monto)) + "</span>"
		+ "</div>";
}

function construirGraficoComposicionFlujoGasto(totales) {
	var resultado = totales.ingresos - totales.costosVariables - totales.gastosFijos - totales.administracion - totales.sinCategorizar;
	var totalEgresos = totales.costosVariables + totales.gastosFijos + totales.administracion + totales.sinCategorizar;
	var sinIngresosConEgresos = totales.ingresos <= 0 && totalEgresos > 0;
	var baseGrafico = sinIngresosConEgresos ? totalEgresos : totales.ingresos;
	var porcentajeResultado = sinIngresosConEgresos ? 0 : Math.max(0, porcentajeFlujoGasto(resultado, baseGrafico));
	var porcentajeVariables = porcentajeFlujoGasto(totales.costosVariables, baseGrafico);
	var porcentajeFijos = porcentajeFlujoGasto(totales.gastosFijos, baseGrafico);
	var porcentajeAdministracion = porcentajeFlujoGasto(totales.administracion, baseGrafico);
	var porcentajeSinCategoria = porcentajeFlujoGasto(totales.sinCategorizar, baseGrafico);
	var totalGrafico = porcentajeResultado + porcentajeVariables + porcentajeFijos + porcentajeAdministracion + porcentajeSinCategoria;
	var escala = Math.max(100, totalGrafico || 100);
	var baseTop = sinIngresosConEgresos ? 100 : (escala > 0 ? ((escala - 100) / escala) * 100 : 0);
	var claseDeficit = resultado < 0 ? " flujo-composicion-grafico--deficit" : "";
	var alertaDeficit = resultado < 0
		? "<div class='flujo-composicion-deficit'>Deficit: " + formatearMontoFlujoConSigno(resultado) + " (" + (sinIngresosConEgresos ? "sin ingresos para cubrir egresos" : formatearPorcentajeFlujoGasto(Math.abs(porcentajeFlujoGasto(resultado, totales.ingresos)))) + ")</div>"
		: "";
	return "<div class='flujo-composicion-grafico" + claseDeficit + "'>"
		+ alertaDeficit
		+ "<div class='flujo-composicion-eje'><span>" + formatearPorcentajeFlujoGasto(escala) + "</span><span>100%</span><span>50%</span><span>0%</span></div>"
		+ "<div class='flujo-composicion-barra' style='--flujo-base-top:" + baseTop + "%'>"
		+ "<div class='flujo-composicion-base-line'><span>Ingresos 100%</span></div>"
		+ construirSegmentoComposicionFlujoGasto("resultado", "Resultado / utilidad", porcentajeResultado, resultado, escala)
		+ construirSegmentoComposicionFlujoGasto("variables", "Costos variables", porcentajeVariables, totales.costosVariables, escala)
		+ construirSegmentoComposicionFlujoGasto("fijos", "Gastos fijos", porcentajeFijos, totales.gastosFijos, escala)
		+ construirSegmentoComposicionFlujoGasto("administracion", "Administracion asignada", porcentajeAdministracion, totales.administracion, escala)
		+ construirSegmentoComposicionFlujoGasto("sin-categoria", "Sin categorizar", porcentajeSinCategoria, totales.sinCategorizar, escala)
		+ "</div>"
		+ "<div class='flujo-composicion-leyenda'>"
		+ "<span><i class='flujo-dot flujo-dot--resultado'></i>Resultado</span>"
		+ "<span><i class='flujo-dot flujo-dot--variables'></i>Costos variables</span>"
		+ "<span><i class='flujo-dot flujo-dot--fijos'></i>Gastos fijos</span>"
		+ "<span><i class='flujo-dot flujo-dot--administracion'></i>Administracion</span>"
		+ "<span><i class='flujo-dot flujo-dot--sin-categoria'></i>Sin categorizar</span>"
		+ "</div>"
		+ "</div>";
}

function construirLecturaPorCada100FlujoGasto(totales) {
	var resultado = totales.ingresos - totales.costosVariables - totales.gastosFijos - totales.administracion - totales.sinCategorizar;
	if (totales.ingresos <= 0) {
		return "<div class='flujo-composicion-lectura'>"
			+ "<strong>Sin ingresos en el periodo:</strong>"
			+ "<span class='flujo-composicion-lectura--deficit'><b>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(Math.abs(resultado))) + "</b> quedan como deficit a cubrir</span>"
			+ "<span><b>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totales.costosVariables)) + "</b> en costos variables</span>"
			+ "<span><b>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totales.gastosFijos)) + "</b> en gastos fijos</span>"
			+ "<span><b>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totales.administracion)) + "</b> en administracion asignada</span>"
			+ "<span><b>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totales.sinCategorizar)) + "</b> sin categorizar</span>"
			+ "</div>";
	}
	var porcentajeResultado = porcentajeFlujoGasto(resultado, totales.ingresos);
	var textoResultado = resultado >= 0
		? "<span><b>" + formatearPorCada100FlujoGasto(porcentajeResultado) + "</b> quedan como resultado</span>"
		: "<span class='flujo-composicion-lectura--deficit'><b>" + formatearPorCada100FlujoGasto(Math.abs(porcentajeResultado)) + "</b> faltan para cubrir egresos</span>";
	return "<div class='flujo-composicion-lectura'>"
		+ "<strong>Por cada 100 Gs. que ingresan:</strong>"
		+ textoResultado
		+ "<span><b>" + formatearPorCada100FlujoGasto(porcentajeFlujoGasto(totales.costosVariables, totales.ingresos)) + "</b> van a costos variables</span>"
		+ "<span><b>" + formatearPorCada100FlujoGasto(porcentajeFlujoGasto(totales.gastosFijos, totales.ingresos)) + "</b> van a gastos fijos</span>"
		+ "<span><b>" + formatearPorCada100FlujoGasto(porcentajeFlujoGasto(totales.administracion, totales.ingresos)) + "</b> van a administracion asignada</span>"
		+ "<span><b>" + formatearPorCada100FlujoGasto(porcentajeFlujoGasto(totales.sinCategorizar, totales.ingresos)) + "</b> estan sin categorizar</span>"
		+ "</div>";
}

function construirPanelAdministracionCompartidaFlujoGasto(info) {
	if (!info || !info.aplica) {
		return "";
	}
	var modo = (info.modo || "") + "";
	var totalOrigen = numeroFlujoGastoDesdeRespuesta(info.total_origen || 0);
	var montoAsignado = numeroFlujoGastoDesdeRespuesta(info.monto_asignado || 0);
	var cantidadLocales = numeroFlujoGastoDesdeRespuesta(info.cantidad_locales || 0);
	var localDestino = info.local_destino || {};
	var distribuciones = info.distribuciones || [];
	var titulo = modo == "origen" ? "Distribucion administrativa" : "Administracion asignada";
	var resumen = modo == "origen"
		? "Total cargado en Administracion compartidos"
		: "Parte del gasto administrativo incluida en este local";
	var filas = distribuciones.map(function (local) {
		var clase = local.es_local_seleccionado ? " flujo-composicion-admin__local--actual" : "";
		return "<li class='" + clase + "'>"
			+ "<span>" + escaparHtmlFlujoGasto(local.nombre || ("Local " + (local.codigo || ""))) + "</span>"
			+ "<b>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(local.monto || 0)) + "</b>"
			+ "</li>";
	}).join("");
	var datoPrincipal = modo == "origen"
		? formatearMontoFlujoConSigno(totalOrigen)
		: formatearMontoFlujoConSigno(montoAsignado);
	var destino = modo == "asignado" && localDestino.nombre
		? "<em>" + escaparHtmlFlujoGasto(localDestino.nombre) + "</em>"
		: "";
	return "<div class='flujo-composicion-admin'>"
		+ "<div class='flujo-composicion-admin__head'>"
		+ "<span>" + escaparHtmlFlujoGasto(titulo) + "</span>"
		+ "<strong>" + escaparHtmlFlujoGasto(datoPrincipal) + "</strong>"
		+ "</div>"
		+ "<p>" + escaparHtmlFlujoGasto(resumen) + "</p>"
		+ destino
		+ "<small>Regla: partes iguales entre " + escaparHtmlFlujoGasto(cantidadLocales || distribuciones.length || 0) + " sucursales.</small>"
		+ (filas ? "<ul>" + filas + "</ul>" : "")
		+ "</div>";
}

function construirMovimientoComposicionFlujoGasto(movimiento) {
	var descripcion = movimiento.descripcion || movimiento.interconsulta || "Movimiento sin descripcion";
	var estado = movimiento.estado || "";
	var referencia = movimiento.id ? "#" + movimiento.id : "Caja";
	return "<tr>"
		+ "<td>" + escaparHtmlFlujoGasto(movimiento.fecha || "-") + "</td>"
		+ "<td><b>" + escaparHtmlFlujoGasto(referencia) + "</b><span>" + escaparHtmlFlujoGasto(descripcion) + "</span></td>"
		+ "<td>" + escaparHtmlFlujoGasto(estado || "-") + "</td>"
		+ "<td>" + escaparHtmlFlujoGasto(movimiento.usuario || "-") + "</td>"
		+ "<td class='flujo-composicion-monto'>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(movimiento.monto || 0)) + "</td>"
		+ "</tr>";
}

function construirConceptoComposicionFlujoGasto(concepto, totalCategoria, ingresos) {
	var total = numeroFlujoGastoDesdeRespuesta(concepto.total || 0);
	var porcentajeCategoria = porcentajeFlujoGasto(total, totalCategoria);
	var movimientos = concepto.movimientos || [];
	var filasMovimientos = movimientos.length > 0
		? movimientos.map(construirMovimientoComposicionFlujoGasto).join("")
		: "<tr><td colspan='5' class='flujo-composicion-sin-movimientos'>Sin movimientos en el periodo seleccionado.</td></tr>";
	return "<details class='flujo-composicion-concepto'>"
		+ "<summary>"
		+ "<span class='flujo-composicion-concepto__nombre'>" + escaparHtmlFlujoGasto(concepto.nombre || "Concepto sin nombre") + "</span>"
		+ "<span class='flujo-composicion-concepto__monto'>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(total)) + "</span>"
		+ "<span class='flujo-composicion-percent'><b>" + formatearPorcentajeFlujoGasto(porcentajeCategoria) + "</b><small>del bloque</small></span>"
		+ "<span class='flujo-composicion-percent flujo-composicion-percent--secundario'><b>" + formatearPorcentajeSobreBaseFlujoGasto(total, ingresos) + "</b><small>de ingresos</small></span>"
		+ "<i class='flujo-composicion-mini-bar'><em style='width:" + Math.min(100, Math.max(0, porcentajeCategoria)) + "%'></em></i>"
		+ "</summary>"
		+ "<div class='flujo-composicion-movimientos'>"
		+ "<table><thead><tr><th>Fecha</th><th>Movimiento</th><th>Estado</th><th>Usuario</th><th>Monto</th></tr></thead><tbody>"
		+ filasMovimientos
		+ "</tbody></table>"
		+ "</div>"
		+ "</details>";
}

function construirCategoriaComposicionFlujoGasto(categoria, ingresos) {
	var totalCategoria = numeroFlujoGastoDesdeRespuesta(categoria.total || 0);
	var codigo = categoria.codigo || "sinCategoria";
	var clase = claseCategoriaComposicionFlujoGasto(codigo);
	var conceptos = categoria.conceptos || [];
	var alerta = (codigo == "sinCategoria")
		? "<span class='flujo-composicion-alerta'>Debe clasificarse</span>"
		: "";
	var contenido = conceptos.length > 0
		? conceptos.map(function (concepto) {
			return construirConceptoComposicionFlujoGasto(concepto, totalCategoria, ingresos);
		}).join("")
		: "<div class='flujo-composicion-sin-conceptos'>Sin conceptos cargados para este bloque.</div>";
	return "<details class='flujo-composicion-categoria flujo-composicion-categoria--" + clase + "'>"
		+ "<summary>"
		+ "<span><b>" + escaparHtmlFlujoGasto(categoria.titulo || tituloCategoriaComposicionFlujoGasto(codigo)) + "</b>" + alerta + "</span>"
		+ "<strong>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totalCategoria)) + "</strong>"
		+ "<em>" + formatearPorcentajeSobreBaseFlujoGasto(totalCategoria, ingresos) + " sobre ingresos</em>"
		+ "</summary>"
		+ "<div class='flujo-composicion-conceptos'>" + contenido + "</div>"
		+ "</details>";
}

function construirComposicionFlujoGastoHTML(resumen, datosFallback) {
	var totales = obtenerTotalesComposicionFlujoGasto(resumen, datosFallback);
	var resultado = totales.ingresos - totales.costosVariables - totales.gastosFijos - totales.administracion - totales.sinCategorizar;
	var categorias = categoriasComposicionFlujoGasto(resumen);
	var administracionCompartida = resumen && resumen.administracion_compartida ? resumen.administracion_compartida : null;
	var claseResultado = resultado < 0 ? " flujo-composicion-estado--deficit" : " flujo-composicion-estado--saludable";
	return "<div class='flujo-composicion__head'>"
		+ "<div><span>Composicion sobre ingresos</span><strong>Ingresos (Base 100%)</strong></div>"
		+ "<button type='button' title='El grafico usa ingresos como 100% y resta todos los egresos.'>i</button>"
		+ "</div>"
		+ "<div class='flujo-composicion-base'>"
		+ "<span>Ingresos del periodo</span>"
		+ "<strong>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totales.ingresos)) + "</strong>"
		+ "</div>"
		+ construirGraficoComposicionFlujoGasto(totales)
		+ construirLecturaPorCada100FlujoGasto(totales)
		+ construirPanelAdministracionCompartidaFlujoGasto(administracionCompartida)
		+ "<div class='flujo-composicion-estado" + claseResultado + "'>"
		+ "<span>Margen del periodo</span>"
		+ "<strong>" + (totales.ingresos <= 0 && resultado < 0 ? "sin ingresos" : formatearPorcentajeFlujoGasto(porcentajeFlujoGasto(resultado, totales.ingresos))) + "</strong>"
		+ "<em>" + escaparHtmlFlujoGasto(resultado < 0 ? "Deficit" : "Saludable") + "</em>"
		+ "</div>"
		+ "<div class='flujo-composicion-desglose'>"
		+ categorias.map(function (categoria) {
			return construirCategoriaComposicionFlujoGasto(categoria, totales.ingresos);
		}).join("")
		+ "</div>";
}

function construirComposicionFlujoGastoImpresion(resumen, datosFallback) {
	if (!resumen && !datosFallback) { return ""; }
	var totales = obtenerTotalesComposicionFlujoGasto(resumen, datosFallback);
	var resultado = totales.ingresos - totales.costosVariables - totales.gastosFijos - totales.administracion - totales.sinCategorizar;
	var categorias = categoriasComposicionFlujoGasto(resumen);
	var ingresosBase = totales.ingresos;
	var segmentos = [
		{ titulo: "Resultado", valor: Math.max(0, resultado), color: "#168a68" },
		{ titulo: "Costos variables", valor: totales.costosVariables, color: "#e58a12" },
		{ titulo: "Gastos fijos", valor: totales.gastosFijos, color: "#e33d3d" },
		{ titulo: "Administracion", valor: totales.administracion, color: "#3b6ea8" },
		{ titulo: "Sin categorizar", valor: totales.sinCategorizar, color: "#7b8794" }
	];
	var baseImpresion = ingresosBase > 0 ? ingresosBase : Math.max(totales.costosVariables + totales.gastosFijos + totales.administracion + totales.sinCategorizar, 1);
	var barra = segmentos.map(function (segmento) {
		var pct = porcentajeFlujoGasto(segmento.valor, baseImpresion);
		if (pct <= 0) { return ""; }
		return "<span style='display:inline-block;height:18px;width:" + Math.min(160, pct) + "%;background:" + segmento.color + ";vertical-align:top;color:#fff;font-size:10px;text-align:center;line-height:18px;'>" + escaparHtmlFlujoGasto(formatearPorcentajeFlujoGasto(pct)) + "</span>";
	}).join("");
	var filasConceptos = categorias.map(function (categoria) {
		var totalCategoria = numeroFlujoGastoDesdeRespuesta(categoria.total || 0);
		var filas = "<tr><td colspan='4' style='background:#eef3f7;font-weight:bold;padding:6px;border:1px solid #d8e1ea;'>" + escaparHtmlFlujoGasto(categoria.titulo || "") + " - " + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totalCategoria)) + " (" + formatearPorcentajeSobreBaseFlujoGasto(totalCategoria, ingresosBase) + " sobre ingresos)</td></tr>";
		(categoria.conceptos || []).forEach(function (concepto) {
			var total = numeroFlujoGastoDesdeRespuesta(concepto.total || 0);
			filas += "<tr>"
				+ "<td style='padding:5px;border:1px solid #e1e7ee;'>" + escaparHtmlFlujoGasto(concepto.nombre || "Concepto") + "</td>"
				+ "<td style='padding:5px;border:1px solid #e1e7ee;text-align:right;'>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(total)) + "</td>"
				+ "<td style='padding:5px;border:1px solid #e1e7ee;text-align:right;'>" + formatearPorcentajeFlujoGasto(porcentajeFlujoGasto(total, totalCategoria)) + " del bloque</td>"
				+ "<td style='padding:5px;border:1px solid #e1e7ee;text-align:right;'>" + formatearPorcentajeSobreBaseFlujoGasto(total, ingresosBase) + " de ingresos</td>"
				+ "</tr>";
		});
		return filas;
	}).join("");
	return "<div style='width:100%;box-sizing:border-box;margin:0 0 12px;padding:10px;border:1px solid #cfdbe6;border-radius:6px;background:#fff;font-family:Arial,sans-serif;'>"
		+ "<h2 style='margin:0 0 8px;font-size:16px;color:#12263a;'>Composicion economica del periodo</h2>"
		+ "<table style='width:100%;border-collapse:collapse;margin-bottom:8px;'><tr>"
		+ "<td style='padding:5px;border:1px solid #d8e1ea;'><b>Ingresos base</b><br>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totales.ingresos)) + "</td>"
		+ "<td style='padding:5px;border:1px solid #d8e1ea;'><b>Costos variables</b><br>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totales.costosVariables)) + "</td>"
		+ "<td style='padding:5px;border:1px solid #d8e1ea;'><b>Gastos fijos</b><br>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totales.gastosFijos)) + "</td>"
		+ "<td style='padding:5px;border:1px solid #d8e1ea;'><b>Administracion</b><br>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totales.administracion)) + "</td>"
		+ "<td style='padding:5px;border:1px solid #d8e1ea;'><b>Sin categorizar</b><br>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(totales.sinCategorizar)) + "</td>"
		+ "<td style='padding:5px;border:1px solid #d8e1ea;'><b>" + escaparHtmlFlujoGasto(resultado < 0 ? "Deficit" : "Resultado") + "</b><br>" + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(resultado)) + "</td>"
		+ "</tr></table>"
		+ "<div style='width:100%;border:1px solid #ccd7e2;background:#f7fafc;margin-bottom:8px;white-space:nowrap;overflow:visible;'>" + barra + "</div>"
		+ (resultado < 0 ? "<p style='margin:4px 0 8px;color:#b3261e;font-weight:bold;'>Los egresos superan los ingresos en " + escaparHtmlFlujoGasto(formatearMontoFlujoConSigno(Math.abs(resultado))) + ".</p>" : "")
		+ "<table style='width:100%;border-collapse:collapse;font-size:11px;'>" + filasConceptos + "</table>"
		+ "</div>";
}

function actualizarComposicionFlujoGasto(resumen, datosFallback) {
	var panel = document.getElementById("panelComposicionFlujoGasto");
	if (!panel) { return; }
	if (!resumen && !datosFallback) {
		panel.innerHTML = "<div class='flujo-composicion__vacio'><strong>Composicion sobre ingresos</strong><span>Busca un periodo para ver ingresos, costos, resultado y conceptos.</span></div>";
		return;
	}
	panel.innerHTML = construirComposicionFlujoGastoHTML(resumen, datosFallback);
}

function mostrarCargaComposicionFlujoGasto() {
	var panel = document.getElementById("panelComposicionFlujoGasto");
	if (!panel) { return; }
	panel.innerHTML = "<div class='flujo-composicion__vacio'><strong>Cargando composicion...</strong><span>Calculando ingresos, costos, resultado y conceptos.</span></div>";
}

function aplicarLecturaCascadaGasto() {
	var contenedor = document.getElementById("table_abm_gasto");
	if (!contenedor) { return; }
	contenedor.classList.add("flujo-caja-cascada");

	Array.prototype.forEach.call(contenedor.querySelectorAll(".card"), function (card) {
		var tituloZona = card.querySelector(".card-header h4");
		if (!tituloZona) { return; }
		var texto = (tituloZona.textContent || "").toLowerCase();
		card.classList.add("flujo-caja-zona");
		if (texto.indexOf("ingresos") !== -1) {
			card.classList.add("flujo-caja-zona--ingresos");
		} else if (texto.indexOf("costos variables") !== -1) {
			card.classList.add("flujo-caja-zona--variables");
		} else if (texto.indexOf("gastos fijos") !== -1 || texto.indexOf("costos fijos") !== -1) {
			card.classList.add("flujo-caja-zona--fijos");
		} else if (texto.indexOf("administracion") !== -1 || texto.indexOf("administración") !== -1) {
			card.classList.add("flujo-caja-zona--administracion");
		} else {
			card.classList.add("flujo-caja-zona--otros");
		}
	});

	Array.prototype.forEach.call(contenedor.querySelectorAll(".flujo-caja-zona"), function (zona, indice) {
		zona.style.setProperty("--flujo-posicion", indice + 1);
	});

	Array.prototype.forEach.call(contenedor.querySelectorAll(".card"), function (card) {
		if (card.classList.contains("flujo-caja-zona")) { return; }
		var tituloMotivo = card.querySelector(".card-header h6");
		if (!tituloMotivo) { return; }
		var encabezado = card.querySelector(".card-header");
		var estiloEncabezado = encabezado ? (encabezado.getAttribute("style") || "").toLowerCase() : "";
		card.classList.add("flujo-caja-motivo");
		if (card.parentElement) {
			card.parentElement.classList.add("flujo-caja-motivo-wrap");
		}
		if (estiloEncabezado.indexOf("#ff5050") !== -1 || estiloEncabezado.indexOf("255, 80, 80") !== -1) {
			card.classList.add("flujo-caja-motivo--alerta");
		}
	});

	Array.prototype.forEach.call(contenedor.querySelectorAll(".flujo-caja-motivo .list-group-item"), function (item) {
		if (item.querySelector("table")) {
			item.classList.add("flujo-caja-registro-wrap");
		}
	});
}

function buscarabmGasto() {
if(controlacceso("BUSCARLISTADOEGRESOINGRESO","accion")==false){return;}	
	var fecha1 = document.getElementById('inptBuscarGastoF1').value
	var fecha2 = document.getElementById('inptBuscarGastoF2').value
	const ocultar_inactivos = document.getElementById("inptSeleccEstadoBuscarGasto2").checked;
	var estado =""
	var tipo = document.getElementById('inptSeleccTipoBuscarGasto').value
	var arreglo = ""; //document.getElementById('inptSeleccArregloBuscarGasto').value
	var cod_local = document.getElementById('inptlocalMisGastosBusca').value
	var usuario = document.getElementById('inptBuscarIngresoEgreso1').value
	var fecha = document.getElementById('inptBuscarIngresoEgreso2').value
    let cod_motivoFK= '';
	const interConsulta= document.getElementById('inptBuscarIngresoEgreso4').value;
    $("input[id=inptBuscarIngresoEgreso3]").each(function (i, Elemento) {
      var $input = $(this),
          val = $input.val();
		 
          list = $input.attr('list'),
          match = $('#'+list + ' option').filter(function() {
              return ($(this).val() === val);			 
          });

       if(match.length > 0) {
         cod_motivoFK=$(match).attr("id")
       } else {
           // value is not in list
       }
    });
	
	document.getElementById("table_abm_gasto").innerHTML = paginacargando;
	mostrarCargaComposicionFlujoGasto();
	actualizarEncabezadoFlujoGasto();
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"estado": estado,
		"ocultar_inactivos": ocultar_inactivos,
		"cod_local": cod_local,
		"tipo": tipo,
		"usuario": usuario,
		"fecha": fecha,
		"arreglo": arreglo,
        "cod_motivoFK": cod_motivoFK,
		"cod_interConsultaFK": "",
		"nombre_interConsulta": interConsulta,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
		var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		beforeSend: function () {

		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_abm_gasto_imprimir").innerHTML = '';
			document.getElementById("table_abm_gasto").innerHTML = '';
			actualizarResumenNetoFlujoGasto(0, 0, 0);
			actualizarComposicionFlujoGasto(null);
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_gasto_imprimir").innerHTML = '';
			document.getElementById("table_abm_gasto").innerHTML = '';
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "UI") {
					actualizarResumenNetoFlujoGasto(0, 0, 0);
					actualizarComposicionFlujoGasto(null);
					ir_a_login()
					ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
					return false;
				}
				if (Respuesta == "NI") {
					actualizarResumenNetoFlujoGasto(0, 0, 0);
					actualizarComposicionFlujoGasto(null);
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
				if (Respuesta == "exito") {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_gasto_imprimir").innerHTML = construirComposicionFlujoGastoImpresion(datos[13] || null, datos) + (datos[12] || "");
					document.getElementById("table_abm_gasto").innerHTML = datos[2];
					aplicarLecturaCascadaGasto();
					actualizarEncabezadoFlujoGasto();

					document.getElementById("inptTotalGasto").value = datos[4];
					separadordemiles(document.getElementById("inptTotalGasto"));
					actualizarResumenNetoFlujoDesdeDatos(datos);
				}
			} catch (error) {
				actualizarResumenNetoFlujoGasto(0, 0, 0);
				actualizarComposicionFlujoGasto(null);
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function limpiarcamposGasto() {
	document.getElementById('inptMontoGasto').value = "";
	document.getElementById('inptRegistroSeleccGasto').value = "";
	document.getElementById('inptDescripcionGasto').value = "";
	document.getElementById('inptFechaGasto').value = "";
	document.getElementById('inptPersonalGasto').value = "";
	document.getElementById('inptNroBoletaGasto').value = "";
	document.getElementById('inptBancoGasto').value = "";
	document.getElementById('inptCuentaGasto').value = "";
	document.getElementById('inptArregloGasto').value = "";
	document.getElementById('inptCantCuotaGasto').value = "";
	document.getElementById('inptPeriodicidadGasto').value = "";
	document.getElementById('btnEditarGastos').style.backgroundColor="#b7b7b7";
	document.getElementById('btnImprimirRegistroGastos').style.backgroundColor="#b7b7b7";
	document.getElementById('btnAutorizarGastos').style.backgroundColor="#b7b7b7";
	document.getElementById('btnInterConsultaGastos').style.backgroundColor="#b7b7b7";
	document.getElementById('inptEstadoGasto').value = "Activo";
	document.getElementById('btnAbmGastos').value = "Guardar movimiento";
	configurarModalMovimientoFinanciero({ modo: "general" });
	document.getElementById('inptMotivoMisGastos').value ="";
	document.getElementById('inptMotivoMisGastos').setAttribute("data-categoria-cargada", "");
	document.getElementById('inptAbmInterConsultaGasto').value= "";
	document.getElementById('divGastoAsociadosGastos').style.display= "none";
	document.getElementById('divGastoAsociadosGastos').setAttribute('data-es-credito', 'false');
	document.getElementById('tablePeriodicidad').style.display= "";
	actualizarVisibilidadCantidadCuotasGasto();
	document.getElementById('inptIdGasto').value = "";
	document.getElementById('inptProyectoGasto').innerHTML = '<option value="0">PAGO AISLADO (sin proyecto)</option>';
	document.getElementById('inptProyectoGasto').value = "0";
	document.getElementById('inptProyectoGasto').disabled = true;
	document.getElementById('inptProyectoGasto').setAttribute("data-proyecto-hilo-bloqueado", "true");
	document.getElementById('inptProyectoGasto').setAttribute("data-valor-anterior", "0");
	cod_interConsulta= "";
	usuarioCreadorEgresoIngreso = "";
	idAbmGasto = "";
	seleccionarLocalUSer()
	fotoGasto= "";
	extGasto= "";
	fotoDocumentoFirmadoGasto= "";
	extDocumentoFirmadoGasto= "";
    document.getElementById('imgfotoGasto').style.backgroundImage= "url("+ '/GoodVentaAsisCap/iconos/imagenphoto.png' +")";
    document.getElementById('imgdocumentoFirmadoGasto').style.backgroundImage= "url("+ '/GoodVentaAsisCap/iconos/imagenphoto.png' +")";
	inicializarVistaPreviaPlanificacionGasto();
}

/* ABM MOTIVO EN EGRESO/INGRESO */
function verCerrarAbmNuevoMotivo(){
	if(controlacceso("CREARNUEVOMOTIVO","accion")==false){return;}
	if(document.getElementById("divAbmNuevoMotivo").style.display==""){
		
		$("div[id=divAbmNuevoMotivo]").fadeOut(500);	

		// Se indica el motivo seleccionado si el estado es activo
		if (idAbmMotivoEgresoIngreso && document.getElementById("inptEstadoMotivoEgresoIngreso").value == 'activo') {
			document.getElementById('inptMotivoMisGastos').value= document.getElementById("inptNuevoMotivoEgresoIngreso").value;
		} else {
			document.getElementById('inptMotivoMisGastos').value= "";
		}		
	}else{		
		document.getElementById("divAbmNuevoMotivo").style.display=""
		BuscarAbmMotivoEgresoIngreso();
	}
}

function VerificarDatosMotivoEgresoIngreso() {
	var inptNuevoMotivo = document.getElementById('inptNuevoMotivoEgresoIngreso').value
	var inptEstadoMotivoEgresoIngreso = document.getElementById('inptEstadoMotivoEgresoIngreso').value
	const inptCategoriaMotivoEgresoIngreso = document.getElementById('inptCategoriaMotivoEgresoIngreso').value;
	const inptAutorizacionMotivoEgresoIngreso= document.getElementById('inptAutorizacionMotivoEgresoIngreso').checked;
	let accion = "";
	
	if (inptNuevoMotivo == "") {
		ver_vetana_informativa("FALTO AGREGAR NUEVO MOTIVO")
		return false;
	}
	if (!inptCategoriaMotivoEgresoIngreso) {
		ver_vetana_informativa("FALTO SELECCIONAR LA CATEGORIA");
		return false;
	}

	if(idAbmMotivoEgresoIngreso != ''){
		accion = "editarMotivo";
	}else{
		accion = "NuevoMotivo";
	}
		
	abmNuevoMotivo(inptNuevoMotivo,inptEstadoMotivoEgresoIngreso, inptCategoriaMotivoEgresoIngreso, inptAutorizacionMotivoEgresoIngreso, accion);
}

function abmNuevoMotivo(motivo, estado , categoria, necesita_autorizacion, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("motivo", motivo)
	datos.append("estado", estado)
	datos.append("categoria", categoria);
	datos.append("idabm", idAbmMotivoEgresoIngreso)
	datos.append("necesita_autorizacion", (necesita_autorizacion ? 1 : 0)); // El 1 es equivalente a true
	
	var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmgasto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("")
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")

			return false;
		},
		success: function (responseText) {
			verCerrarEfectoCargando("")
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					
					// Se actualizan los presupuestos de los locales
					const tabla= document.getElementById("divPresupuestoLocalMotivoEgresoIngreso").children;
					for (let i = 0; i < tabla.length; i++) {
						const datostr = $(tabla[i]).children('tbody').children('tr');

						// Captura los datos
						const cod_monto_limite_gasto_motivo= $(datostr).children('td[id="td_id"]').html();
						const cod_localFK= $(datostr).children('td[id="td_datos_3"]').html();
						let monto_limite= $(datostr).children('td[id="td_datos_2"]').children('input').val() || "";
						monto_limite= monto_limite.replace('.','');
						const cod_motivo_ingreso_egreso= $(datostr).children('td[id="td_datos_4"]').html();

						if (monto_limite && monto_limite != "0") {
							abmLimiteMotivoGasto(cod_monto_limite_gasto_motivo, monto_limite, cod_localFK, cod_motivo_ingreso_egreso);
						}
					}
					
					// Busca los datos
					buscaroptionMotivoEgresoIngreso()
					// verCerrarAbmNuevoMotivo()
					BuscarAbmMotivoEgresoIngreso()
					limpiarcamposmotivoegresoingreso()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function verCerrarResumenGastosMotivos(mostrar) {
	if (mostrar) {
		// Obtiene las fechas
		const fechaActual= new Date();
		const primerDiaMes = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 1);
		const ultimoDiaMes = new Date(fechaActual.getFullYear(), fechaActual.getMonth() + 1, 0);
		buscarResumenGastosMotivo();
		
		// Asigna las fechas a los input
		document.getElementById('inptFecha1ResumenGastosMotivo').value= primerDiaMes.toISOString().slice(0, 10);
		document.getElementById('inptFecha2ResumenGastosMotivo').value= ultimoDiaMes.toISOString().slice(0, 10);
		document.getElementById('divAbmResumenGastosMotivos').style.display= "";
	} else {
		document.getElementById('divAbmResumenGastosMotivos').style.display= "none";
	}
}

function buscarResumenGastosMotivo() {
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscarResumenGastosMotivo",
		"fecha_inicio": document.getElementById('inptFecha1ResumenGastosMotivo').value,
		"fecha_fin": document.getElementById('inptFecha2ResumenGastosMotivo').value,
	};
	$.ajax({
		data: datos,
		url: "../php_system/abmgasto.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("divResumenGastosMotivo").innerHTML = ""
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divResumenGastosMotivo").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				   	var datos_buscados = datos[2];
					document.getElementById("divResumenGastosMotivo").innerHTML = datos_buscados;
					document.getElementById("inptTotalResumenGastoMotivo").value= datos["3"];
					separadordemiles(document.getElementById("inptTotalResumenGastoMotivo"));
					document.getElementById("inptCantResumenGastoMotivo").value= datos["4"];
					
					// Construye el grafico
					const canvas = document.getElementById('chartResumenGastosMOtivos');
					const ctx = canvas.getContext('2d');
					
					const data = datos["5"];
					const formattedData = data.map(item => ({
						label: item.descripcion,
						value: (() => {
							const monto = parseInt((item.monto || "0").replace(/\./g, ''), 10);
							const divisor = parseInt(datos["3"] || "0", 10);

							// valida el numero
							if (!divisor || isNaN(divisor)) return 0;
							if (isNaN(monto)) return 0;

							return (monto / divisor) * 100;
						})(),
						color: '#' + Math.floor(Math.random()*16777215).toString(16)
					}));

					let startAngle = 0;
					let total = 100;

					formattedData.forEach(item => {
						const sliceAngle = 2 * Math.PI * item.value / total;
						ctx.fillStyle = item.color;
						ctx.beginPath();
						ctx.moveTo(canvas.width / 2, canvas.height / 2);
						ctx.arc(canvas.width / 2, canvas.height / 2, Math.min(canvas.width / 2, canvas.height / 2), startAngle, startAngle + sliceAngle);
						ctx.closePath();
						ctx.fill();
						startAngle += sliceAngle;

						document.getElementById('leyendResumenGastosMOtivos').innerHTML += '<div style="width: fit-content; display: inline-flex;margin-inline: 10px;">'+
							'<div style="background-color: '+item.color+';height: 10px; margin-right: 5px;width: 10px;margin-top: 5px;"></div>'+
							'<p class="pTitulo8" style="width: fit-content; text-align: start;color: '+item.color+';">'+item.label+'('+item.value.toFixed(1)+')</p>'+
						'</div>';
					});
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscaroptionMotivoEgresoIngreso() {
	var selectConcepto= document.getElementById("inptMotivoMisGastos");
	var categoriaConcepto= obtenerCategoriaConceptoMovimientoFinanciero();
	var categoriaCargada= selectConcepto.getAttribute("data-categoria-cargada") || "";
	if (selectConcepto.innerHTML != '' && categoriaCargada == categoriaConcepto && !selectConcepto.querySelector('[data-contextual-temporal="true"]')) {
		return;
	}
	const valor= selectConcepto.value;
	selectConcepto.innerHTML = ""
	selectConcepto.setAttribute("data-categoria-cargada", categoriaConcepto);
	document.getElementById("listBuscarIngresoEgreso3").innerHTML = ""

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscaroption",
		"categoria": categoriaConcepto
	};
	$.ajax({

		data: datos,
		url: "../php_system/abmgasto.php",
		type: "post",
		
		beforeSend: function () {


		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("inptMotivoMisGastos").innerHTML = ''
			document.getElementById("listBuscarIngresoEgreso3").innerHTML = ""
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("inptMotivoMisGastos").innerHTML = ''
			document.getElementById("listBuscarIngresoEgreso3").innerHTML = ""
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				   var datos_buscados = datos[2];
					document.getElementById("inptMotivoMisGastos").innerHTML = datos[2]
					if (movimientoFinancieroContextoActual && movimientoFinancieroContextoActual.modo == "crear" && movimientoFinancieroContextoActual.conceptoId) {
						asegurarOpcionConceptoMovimientoFinanciero(movimientoFinancieroContextoActual.conceptoId, movimientoFinancieroContextoActual.conceptoNombre);
						document.getElementById("inptMotivoMisGastos").value = movimientoFinancieroContextoActual.conceptoId;
					} else {
						document.getElementById("inptMotivoMisGastos").value = valor;
					}
					document.getElementById("listBuscarIngresoEgreso3").innerHTML = datos[4]
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function combinarMotivoEgresoIngreso() {
	// Verifica si posee el permiso
	if (!controlacceso("COMBINARMOTIVOSEGRESOINGRESO", "accion")) {return false;}

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_motivo_ingreso_egreso": document.getElementById('inptCodAbmMotivoEgresoIngreso1').value,
		"cod_motivo_ingreso_egreso_destino": document.getElementById('inptCodAbmMotivoEgresoIngreso2').value,
		"funt": "combinarmotivoingresoegreso"
	};
	$.ajax({
		data: datos,
        url: "../php_system/abmgasto.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("divBuscadorMotivoEgresoIngreso").innerHTML = ''
			document.getElementById("lblNroRegistroMotivoEgresoIngreso").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					limpiarcamposmotivoegresoingreso();
					BuscarAbmMotivoEgresoIngreso();
					ver_vetana_informativa("Datos guardados.", "", "info");
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function BuscarAbmMotivoEgresoIngreso() {
	var buscador = document.getElementById("inptBuscarAbmMotivoEgresoIngreso").value

	document.getElementById("divBuscadorMotivoEgresoIngreso").innerHTML = paginacargando
    document.getElementById("lblNroRegistroMotivoEgresoIngreso").innerHTML="";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"estado": "",
		"funt": "buscarabmmotivoingresoegreso"
	};
	$.ajax({
		data: datos,
        url: "../php_system/abmgasto.php",
		type: "post",
		 
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("divBuscadorMotivoEgresoIngreso").innerHTML = ''
			document.getElementById("lblNroRegistroMotivoEgresoIngreso").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorMotivoEgresoIngreso").innerHTML = ''
			document.getElementById("lblNroRegistroMotivoEgresoIngreso").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("divBuscadorMotivoEgresoIngreso").innerHTML = datos_buscados
                   document.getElementById("lblNroRegistroMotivoEgresoIngreso").innerHTML="Se encontraron "+datos[3]+" registro(s)";
				   buscaroptionMotivoEgresoIngreso()
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function abmLimiteMotivoGasto(cod_monto_limite_gasto_motivo, monto_limite, cod_localFK, cod_motivoFK) {
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_monto_limite_gasto_motivo": cod_monto_limite_gasto_motivo,
		"monto_limite": monto_limite,
		"cod_localFK": cod_localFK,
		"cod_motivo_ingreso_egresoFK": cod_motivoFK,
		"funt": "nuevo/editar"
	};
	
	$.ajax({
        data: datos,
        url: "../php_system/abmPresupuestoMotivoGasto.php",
        type: "post",
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
        },
        success: function (responseText) {
            var Respuesta = responseText;
            console.log(Respuesta)
            try {
                var datos = $.parseJSON(Respuesta);
                Respuesta = datos["1"];
                Respuesta=respuestaJqueryAjax(Respuesta)
                if (Respuesta == true) {
                    ver_vetana_informativa("Datos guardados.", "", "info");
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
                GuardarArchivosLog(titulo)
            }
        }
    });
}

function buscarLimitesMotivoEgresoIngreso(cod_motivos) {
	obtener_datos_user();
	var datos= {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscarVista",
		"cod_motivo_ingreso_egresoFK": cod_motivos
	}
	document.getElementById("divPresupuestoLocalMotivoEgresoIngreso").innerHTML = paginacargando;

	$.ajax({
		data: datos,
        url: "../php_system/abmPresupuestoMotivoGasto.php",
		type: "post",
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					document.getElementById("divPresupuestoLocalMotivoEgresoIngreso").innerHTML = datos[2];
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

var idAbmMotivoEgresoIngreso = "";
function ObtenerdatosAbmMotivoEgresoIngreso(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	ElementoSeleccMarca=datostr
	datostr.className = 'tableRegistroSelec'
    document.getElementById("inptNuevoMotivoEgresoIngreso").value = $(datostr).children('td[id="td_datos_1"]').html();
    document.getElementById("inptEstadoMotivoEgresoIngreso").value = $(datostr).children('td[id="td_datos_2"]').html();
    document.getElementById("inptCategoriaMotivoEgresoIngreso").value = $(datostr).children('td[id="td_datos_3"]').html().toLowerCase();
	const necesita_autorizacion= $(datostr).children('td[id="td_datos_4"]').html();
	if (necesita_autorizacion == "1") {
		document.getElementById("inptAutorizacionMotivoEgresoIngreso").checked = true;
	} else {
		document.getElementById("inptAutorizacionMotivoEgresoIngreso").checked = false;
	}
	idAbmMotivoEgresoIngreso= $(datostr).children('td[id="td_id"]').html();
     document.getElementById("btnMotivoIngresoEgreso").value="Editar Datos";

	// Datos para combinacion de motivos
	if (!(document.getElementById('inptCodAbmMotivoEgresoIngreso1').value)) {
		document.getElementById('inptCodAbmMotivoEgresoIngreso1').value= $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptNombreAbmMotivoEgresoIngreso1').value= $(datostr).children('td[id="td_datos_1"]').html();
	} else {
		document.getElementById('inptCodAbmMotivoEgresoIngreso2').value= $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptNombreAbmMotivoEgresoIngreso2').value= $(datostr).children('td[id="td_datos_1"]').html();
	}

	buscarLimitesMotivoEgresoIngreso(idAbmMotivoEgresoIngreso);
}

function limpiarcamposmotivoegresoingreso(){
	  document.getElementById("inptNuevoMotivoEgresoIngreso").value = ''
	  document.getElementById("inptCategoriaMotivoEgresoIngreso").value = '';
    document.getElementById("inptEstadoMotivoEgresoIngreso").value = 'activo';
	document.getElementById("inptAutorizacionMotivoEgresoIngreso").checked = false;
	cod_interConsulta= "";
	document.getElementById("inptAbmInterConsultaGasto").value= "";
	idAbmMotivoEgresoIngreso=''
     document.getElementById("btnMotivoIngresoEgreso").value="Guardar"

	 // DAtos para combinacion de motivos
	 document.getElementById('inptCodAbmMotivoEgresoIngreso1').value= "";
	 document.getElementById('inptNombreAbmMotivoEgresoIngreso1').value= "";
	 document.getElementById('inptCodAbmMotivoEgresoIngreso2').value= "";
	 document.getElementById('inptNombreAbmMotivoEgresoIngreso2').value= "";
}

function verCerrarVentanaABMLimiteCaja(mostrar) {
	if (controlacceso("VERABMLIMITECAJA","accion")==false){return;}
	if (mostrar) {
		$("div[id=divAbmLimiteCaja]").fadeIn(250);
	} else {
		$("div[id=divAbmLimiteCaja]").fadeOut(500);
	}
}

function agregarLimiteCaja() {
	let inptLimiteCaja = document.getElementById("inptLimitecaja").value;
	if (inptLimiteCaja === "") {
		ver_vetana_informativa("FALTO INGRESAR EL LIMITE DE CAJA");
		return false;
	}

	// Elimina los puntos de miles
	inptLimiteCaja = inptLimiteCaja.replace(/\./g, '');
	verCerrarEfectoCargando("1");
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"monto": inptLimiteCaja,
		"funt": "agregarLimiteCaja"
	};
	$.ajax({
		data: datos,
        url: "../php_system/abmgasto.php",
		type: "post",
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					ver_vetana_informativa("LIMITE DE CAJA AGREGADO CORRECTAMENTE.");
					verCerrarVentanaABMLimiteCaja(false);
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
				verCerrarEfectoCargando("");
			}
		}
	});
}

var limiteCajaMonto= "";
function obtenerUltimoLimiteCaja() {
	obtener_datos_user();
	const datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "obtenerUltimoLimiteCaja",
	}
	var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmgasto.php",
		type: "post",
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					limiteCajaMonto = datos["2"];
					document.getElementById("inptLimitecaja").value = limiteCajaMonto;
					separadordemiles(document.getElementById("inptLimitecaja"));
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
/*
INFORME DE EVALUACIÓN
*/
function verCerrarInformeDeEvaluacion(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divInformeEvaluacion").style.display==""){
limpiarcamposinformeevaluacion()
		document.getElementById("divMinimizadoInformeEvaluacion").style.display="none"
document.getElementById("tdEfectoInformeEvaluacion").className="magictime vanishOut"
	$("div[id=divInformeEvaluacion]").fadeOut(500);	
	}else{	
if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
		document.getElementById("divInformeEvaluacion").style.display=""
document.getElementById("tdEfectoInformeEvaluacion").className="magictime slideDownReturn"
		
	}
}
function minimizarinformeevaluacion(){
	//document.getElementById("divInformeEvaluacion").style.display = "none";
	 document.getElementById("divMinimizadoInformeEvaluacion").style.display = "";
document.getElementById("tdEfectoInformeEvaluacion").className="magictime slideDown"
	$("div[id=divInformeEvaluacion]").fadeOut(500);	
}
function limpiarcamposinformeevaluacion(){
 document.getElementById("inptBuscarEvaluacionF1").value=""
 document.getElementById("inptBuscarEvaluacionF2").value=""
 document.getElementById("inptRegistroEvaluacionGastos").value=""
 document.getElementById("inptTotalEvaluacionGastos").value=""
 document.getElementById("table_evaluacion_gasto").innerHTML=""
 document.getElementById("inptRegistroEvaluacionPagos").value=""
 document.getElementById("inptTotalEvaluacionPagos").value=""
 document.getElementById("table_evaluacion_pagos").innerHTML=""
 document.getElementById("inptRegistroEvaluacionProductosVendidos").value=""
 document.getElementById("inptTotalEvaluacionProductosVendidos").value=""
 document.getElementById("table_evaluacion_producto_vendidos").innerHTML=""
 document.getElementById("inptRegistroEvaluacionProductoComprados").value=""
 document.getElementById("inptTotalEvaluacionProductosComprados").value=""
 document.getElementById("table_evaluacion_producto_comprados").innerHTML=""
 document.getElementById("inptRegistroEvaluacionPagosCompras").value=""
 document.getElementById("inptTotalEvaluacionPagosCompras").value=""
 document.getElementById("table_evaluacion_pagos_compras").innerHTML=""
}
function verCerrarVentanasEvaluacionInforme(d){
	document.getElementById("btnHistoriaEvaluacion1").style=''
	document.getElementById("btnHistoriaEvaluacion2").style=''
	document.getElementById("btnHistoriaEvaluacion4").style=''
	document.getElementById("btnHistoriaEvaluacion5").style=''
	document.getElementById("btnHistoriaEvaluacion6").style=''
	document.getElementById("divEvaluacionGastos").style.display='none'
	document.getElementById("divEvaluacionPagoCuota").style.display='none'
	document.getElementById("divEvualcionProductosComprados").style.display='none'
	document.getElementById("divEvualcionProductosVendidos").style.display='none'
	document.getElementById("divEvualcionPagosCompras").style.display='none'
	if(d=="1"){
		document.getElementById("btnHistoriaEvaluacion1").style='background-color:#ff9800;color:#fff'
		document.getElementById("divEvaluacionGastos").style.display=''
	}
	if(d=="2"){		
		 	document.getElementById("btnHistoriaEvaluacion2").style='background-color:#ff9800;color:#fff'
		document.getElementById("divEvaluacionPagoCuota").style.display=''
	}
		if(d=="3"){		
		document.getElementById("btnHistoriaEvaluacion3").style='background-color:#ff9800;color:#fff'
			document.getElementById("divEvaluacionEntrega").style.display=''			
		}
		if(d=="4"){	
		document.getElementById("btnHistoriaEvaluacion4").style='background-color:#ff9800;color:#fff'
			document.getElementById("divEvualcionProductosComprados").style.display=''			
		}
		if(d=="5"){	
		document.getElementById("btnHistoriaEvaluacion5").style='background-color:#ff9800;color:#fff'
			document.getElementById("divEvualcionProductosVendidos").style.display=''			
		}
		if(d=="6"){	
		document.getElementById("btnHistoriaEvaluacion6").style='background-color:#ff9800;color:#fff'
			document.getElementById("divEvualcionPagosCompras").style.display=''			
		}	
}
function buscarevaluacion(){
	buscarevaluacionGasto()
	buscarevaluacionPago()
	buscarevaluacionProductosvendidos()
	buscarevaluacionProductosComprados()
	buscarevaluacionPagosCompra()		
}
function buscarevaluacionGasto() {
	if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inptBuscarEvaluacionF1").value
	var fecha2 = document.getElementById("inptBuscarEvaluacionF2").value
	var local = document.getElementById("inptlocalInformeEvaluacion").value
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN")
		return false;
	}
	document.getElementById("table_evaluacion_gasto").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "evaluacionGasto"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_evaluacion_gasto").innerHTML = ""	
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
				document.getElementById("table_evaluacion_gasto").innerHTML = ""	
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];				
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var pagina = datos[2];
					document.getElementById("table_evaluacion_gasto").innerHTML = pagina
		document.getElementById("inptRegistroEvaluacionGastos").value = datos[3]
	document.getElementById("inptTotalEvaluacionGastos").value = datos[4]	
	
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
	}
function buscarevaluacionPago() {
	if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inptBuscarEvaluacionF1").value
	var fecha2 = document.getElementById("inptBuscarEvaluacionF2").value
	var local = document.getElementById("inptlocalInformeEvaluacion").value
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN")
		return false;
	}
	document.getElementById("table_evaluacion_pagos").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "evaluacionpagosventa"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
	document.getElementById("table_evaluacion_pagos").innerHTML = ""	
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)				
	document.getElementById("table_evaluacion_pagos").innerHTML = ""	
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var pagina = datos[2];
					document.getElementById("table_evaluacion_pagos").innerHTML = pagina	
					document.getElementById("inptRegistroEvaluacionPagos").value = datos[3]
					document.getElementById("inptTotalEvaluacionPagos").value = datos[4]	
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarevaluacionProductosvendidos() {
	if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inptBuscarEvaluacionF1").value
	var fecha2 = document.getElementById("inptBuscarEvaluacionF2").value
	var local = document.getElementById("inptlocalInformeEvaluacion").value
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN")
		return false;
	}
	document.getElementById("table_evaluacion_producto_vendidos").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "evaluacionproductodvendidos"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")			
	document.getElementById("table_evaluacion_producto_vendidos").innerHTML = ""
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)				
	document.getElementById("table_evaluacion_producto_vendidos").innerHTML = ""
	try {	
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
			Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var pagina = datos[2];
					document.getElementById("table_evaluacion_producto_vendidos").innerHTML = pagina
	document.getElementById("inptRegistroEvaluacionProductosVendidos").value = datos[3]
	document.getElementById("inptTotalEvaluacionProductosVendidos").value = datos[4]
	
				}				
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarevaluacionProductosComprados() {
	if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inptBuscarEvaluacionF1").value
	var fecha2 = document.getElementById("inptBuscarEvaluacionF2").value
	var local = document.getElementById("inptlocalInformeEvaluacion").value
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN")
		return false;
	}
	document.getElementById("table_evaluacion_producto_comprados").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "evaluacionproductodcomprados"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")		
	document.getElementById("table_evaluacion_producto_comprados").innerHTML = ""
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)				
	document.getElementById("table_evaluacion_producto_comprados").innerHTML = ""
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var pagina = datos[2];
				document.getElementById("table_evaluacion_producto_comprados").innerHTML = pagina		
	document.getElementById("inptRegistroEvaluacionProductoComprados").value = datos[3]
	document.getElementById("inptTotalEvaluacionProductosComprados").value = datos[4]
	
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}


function buscarevaluacionPagosCompra() {
	if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inptBuscarEvaluacionF1").value
	var fecha2 = document.getElementById("inptBuscarEvaluacionF2").value
	var local = document.getElementById("inptlocalInformeEvaluacion").value
	
	
	
	
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN")
		return false;
	}
	document.getElementById("table_evaluacion_pagos_compras").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "evaluacionpagoscomprados"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
	document.getElementById("table_evaluacion_pagos_compras").innerHTML = ""
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)			
	document.getElementById("table_evaluacion_pagos_compras").innerHTML = ""
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   			   
					var paginaCompras = datos[2];
					document.getElementById("table_evaluacion_pagos_compras").innerHTML = paginaCompras
					document.getElementById("inptRegistroEvaluacionPagosCompras").value = datos[3]
					document.getElementById("inptTotalEvaluacionPagosCompras").value = datos[4]	
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
			var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}


/*
INFORME PROXIMOSPAGOS
*/
function verCerrarInformeProximosPagos(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divVistaPagoInterconsulta").style.display==""){  
		document.getElementById("divVistaPagoInterconsulta").style.display="none" 
	}else{
		
			var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inpFechaProximoPagoF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inpFechaProximoPagoF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
		
			buscarProximosPagos() 	 	
			document.getElementById("divVistaPagoInterconsulta").style.display="" 
		}
}






function buscarProximosPagos() {
	// if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inpFechaProximoPagoF1").value
	var fecha2 = document.getElementById("inpFechaProximoPagoF2").value
	var local = document.getElementById("inptlocalProximoPago").value
	var descripcion = document.getElementById("inpDescripcionProximoPago").value
	
	
	var estadoFiltroPagoprogrtamado="";
	if(document.getElementById('checProximoPago1').checked==true){
		estadoFiltroPagoprogrtamado="Todo"
	}else{
		estadoFiltroPagoprogrtamado="Pendiente"
	}
	
	
	
	
	
	
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN")
		return false;
	}
	document.getElementById("DivontenedorProximoPago").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"descripcion": descripcion,
		"estadoFiltroPagoprogrtamado": estadoFiltroPagoprogrtamado,
		"funt": "buscarProximosPagos"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
		type: "post",
		xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
         cargarConectividad("enviado",kb,"0")           
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
		kb=0.1;
		}
        cargarConectividad("recibido","0",kb)  
        }, false);
        return xhr;
    },
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
	document.getElementById("DivontenedorProximoPago").innerHTML = ""
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)			
	document.getElementById("DivontenedorProximoPago").innerHTML = ""
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   			   
					var paginaCompras = datos[2];
					document.getElementById("DivontenedorProximoPago").innerHTML = paginaCompras
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
			var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}






function checProximoPago(d){
	if(d=="1"){
	document.getElementById('checProximoPago1').checked=true
	document.getElementById('checProximoPago2').checked=false	
	}else{
	document.getElementById('checProximoPago1').checked=false
	document.getElementById('checProximoPago2').checked=true
	}
}
