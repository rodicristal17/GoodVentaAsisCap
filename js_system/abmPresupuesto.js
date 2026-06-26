var vistaPresupuestoOrigen= ""
var totalPrecioPresupuesto =0
var justificacionProductoPresupuestoSeleccionado = "";
var presupuestoCuotaSeleccionadaVisual = {
	total: null,
	prioritario: null
};
var PRESUPUESTO_CUOTA_RECOMENDADA_MIN = 250000;
var PRESUPUESTO_CUOTA_RECOMENDADA_MAX = 300000;
function verCerrarAbmDetallesPresupuesto(mostrar, historial){
	vistaPresupuestoOrigen= "historial";
	if(mostrar){
		if (historial) {
			document.getElementById("divAbmDetallesPresupuesto").style.display=""
            document.getElementById('divListPresupuesto').style.display= "";
            document.getElementById("divAbmDetallesPresupuesto2").style.display="none";
			buscarvistaPresupuesto();
		} else {
			if(controlacceso("VERHISTORIALPRESUPUESTO","accion")==false){return;}
			
            document.getElementById('divListPresupuesto').style.display= "none";
            document.getElementById("divAbmDetallesPresupuesto2").style.display="";
            // document.getElementById("tdEfectoAbmDetallePresupuesto").className="magictime slideLeftReturn"
            
            document.getElementById("inptEntregaPresupuesto").value=0
            document.getElementById("inptProductoPresupuesto").value=document.getElementById('inptNombreProducto').value
			limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "presupuesto");
        }
	}else{
        if (historial) {
            $("div[id=divListPresupuesto]").fadeOut(500);
            document.getElementById("divAbmDetallesPresupuesto2").style.display="";
            $("div[id=divAbmDetallesPresupuesto]").fadeOut(500);
        } else {
			// document.getElementById("tdEfectoAbmDetallePresupuesto").className="magictime slideRight"
            document.getElementById('divListPresupuesto').style.display= "none";
			vistaPresupuestoOrigen= ""
        }
	}
}

var idabmPresupuesto= "";
var pasoVistaPresupuestoDoc = 1;
var presupuestoDocDropDestinoPlan = "";
var presupuestoDocPlanesGuardados = false;
var presupuestoDocEstadoPlanes = "sin_cambios";
var presupuestoDocSeleccionProvisorioInicial = {};
var presupuestoGuardando = false;
var presupuestoDocBusquedaTimer = null;
var presupuestoDocBusquedaSilenciosa = false;
var idAgendaPresupuestoDoctorActiva = "";
var idPacientePresupuestoDoctorActivo = "";

function presupuestoDocMoverModalPlanesAlBody(modalPlanes) {
	if (modalPlanes && document.body && modalPlanes.parentNode !== document.body) {
		document.body.appendChild(modalPlanes);
	}
}

function presupuestoDocCerrarConfirmacionSuperpuesta() {
	const confirmacion = document.getElementById("confirmDialogGenerico");
	if (confirmacion && confirmacion.classList.contains("show")) {
		if (typeof bootstrap != "undefined" && bootstrap.Modal) {
			const instancia = bootstrap.Modal.getInstance(confirmacion);
			if (instancia) {
				instancia.hide();
			}
		}
		confirmacion.classList.remove("show");
		confirmacion.style.display = "none";
		confirmacion.setAttribute("aria-hidden", "true");
	}
	document.querySelectorAll(".modal-backdrop").forEach(function (backdrop) {
		backdrop.remove();
	});
	document.body.classList.remove("modal-open");
	document.body.style.removeProperty("overflow");
	document.body.style.removeProperty("padding-right");
}

function presupuestoDocAbrirModalPlanes(modalPlanes, paso2Header, panelDetalle) {
	presupuestoDocCerrarConfirmacionSuperpuesta();
	presupuestoDocMoverModalPlanesAlBody(modalPlanes);
	if (paso2Header) {
		paso2Header.style.display = "block";
		paso2Header.style.visibility = "visible";
	}
	if (panelDetalle) {
		panelDetalle.style.display = "grid";
		panelDetalle.style.visibility = "visible";
	}
	modalPlanes.classList.add("presupuesto-doc-planes-modal--abierto");
	modalPlanes.style.display = "flex";
	modalPlanes.style.visibility = "visible";
	modalPlanes.style.opacity = "1";
	document.body.classList.add("presupuesto-doc-modal-abierto");
}

function presupuestoDocCerrarModalPlanes() {
	const modalPlanes = document.getElementById("presupuestoDocPlanesModal");
	const paso2Header = document.getElementById("presupuestoDocPrioritarioHeader");
	const panelDetalle = document.getElementById("presupuestoDocDetallePanel");
	if (modalPlanes) {
		modalPlanes.classList.remove("presupuesto-doc-planes-modal--abierto");
		modalPlanes.style.display = "none";
	}
	if (paso2Header) {
		paso2Header.style.display = "none";
	}
	if (panelDetalle) {
		panelDetalle.style.display = "none";
	}
	document.body.classList.remove("presupuesto-doc-modal-abierto");
}

function presupuestoDocEnfocarModalPlanes(modalPlanes, panelDetalle) {
	if (!modalPlanes) {
		return;
	}
	presupuestoDocMoverModalPlanesAlBody(modalPlanes);

	modalPlanes.setAttribute("role", "dialog");
	modalPlanes.setAttribute("aria-modal", "true");
	modalPlanes.setAttribute("tabindex", "-1");

	function resetearScroll() {
		const dialogo = modalPlanes.querySelector(".presupuesto-doc-planes-dialog");
		[modalPlanes, dialogo, panelDetalle].forEach(function (elemento) {
			if (elemento) {
				elemento.scrollTop = 0;
			}
		});
		try {
			modalPlanes.focus({ preventScroll: true });
		} catch (error) {
			if (document.activeElement && document.activeElement.blur) {
				document.activeElement.blur();
			}
		}
	}

	resetearScroll();
	if (typeof requestAnimationFrame == "function") {
		requestAnimationFrame(resetearScroll);
	} else {
		setTimeout(resetearScroll, 0);
	}
}

function parsearRespuestaAjaxPresupuesto(respuesta) {
	if (typeof respuesta === "string") {
		return $.parseJSON(respuesta);
	}
	return respuesta || {};
}

function textoRespuestaAjaxPresupuesto(respuesta) {
	if (typeof respuesta === "string") {
		return respuesta;
	}
	try {
		return JSON.stringify(respuesta);
	} catch (error) {
		return String(respuesta);
	}
}

function obtenerAgendaPresupuestoDoctorActual() {
	if (
		idAgendaPresupuestoDoctorActiva != "" &&
		idPacientePresupuestoDoctorActivo != "" &&
		String(idPacientePresupuestoDoctorActivo) == String(idFkCliente || "")
	) {
		return idAgendaPresupuestoDoctorActiva;
	}

	return "";
}

function limpiarAgendaPresupuestoDoctorActiva() {
	idAgendaPresupuestoDoctorActiva = "";
	idPacientePresupuestoDoctorActivo = "";
}

function abmPresupuesto(cod_presupuesto, cant_cuotas, cod_clienteFK, cod_ventaFK, plan_vendido, opciones) {
	opciones = opciones || {};
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"accion": "abmPresupuesto",
		"id": cod_presupuesto,
		"cant_cuotas": cant_cuotas,
		"cod_clienteFK": cod_clienteFK,
		"cod_ventaFK": cod_ventaFK,
		"plan_vendido": plan_vendido,
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPresupuesto.php",
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
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
			console.error(jqXHR.status,textstatus,errorThrowm);
			if (opciones.mostrarError !== false) {
				ver_vetana_informativa("LO SENTIMOS, HA OCURRIDO UN ERROR", "", "error");
			}
			if (opciones.conservarDatosEnError !== true) {
				limpirarPresupuesto();
			}
			if (typeof opciones.onError === "function") {
				opciones.onError(jqXHR, textstatus, errorThrowm);
			}
		},
		success: function (responseText) {
			var Respuesta = responseText;
			var ejecutarOnSuccess = false;
			var datosPresupuesto = null;
			console.log(Respuesta)
			try {
				var datos = parsearRespuestaAjaxPresupuesto(Respuesta);
				datosPresupuesto = datos;
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					idabmPresupuesto = datos[2];
					actualizarResumenPresupuestoVenta();
					if (opciones.silencioso !== true) {
						ver_vetana_informativa("Datos guardados exitosamente", "", "info");
					}

					// Actualiza tambien los datos de la agenda
					var idAgendaPresupuestoActual = obtenerAgendaPresupuestoDoctorActual();
					if (idAgendaPresupuestoActual != "") {
						idAbmAgenda = idAgendaPresupuestoActual;
						asignarCodPresupuestoAgenda();
					}
					ejecutarOnSuccess = true;
				} else {
					throw new Error(datos);
				}
			} catch (error) {
				if (opciones.mostrarError !== false) {
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ", responseText, "error")
				}
				var titulo="Error: "+error+" \r\n Consola: "+textoRespuestaAjaxPresupuesto(responseText)
				GuardarArchivosLog(titulo);
				if (opciones.conservarDatosEnError !== true) {
					limpirarPresupuesto();
				}
				if (typeof opciones.onError === "function") {
					opciones.onError(error, responseText);
				}
			}
			if (ejecutarOnSuccess && typeof opciones.onSuccess === "function") {
				opciones.onSuccess(idabmPresupuesto, datosPresupuesto);
			}
		}
	});
}

function buscarvistaproductoPresupuesto() {
	let buscador = "";
	let cod_productoFK= "";
	if (vistaPresupuestoOrigen == 'doctor') {
		buscador = document.getElementById('inptProductoPresupuestoDoc').value;
		cod_productoFK= document.getElementById('inptCodigoPresupuestoDoc').value;
		document.getElementById("table_vista_producto_Presupuesto_doctor").innerHTML = paginacargando
		presupuestoDocSetEstadoBusqueda("Actualizando resultados por nombre...", "buscar");
		limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "doctor");
	} else {
		buscador = document.getElementById('inptProductoPresupuesto').value
		cod_productoFK= document.getElementById('inptCodigoPresupuesto').value;
		document.getElementById("table_vista_producto_Presupuesto").innerHTML = paginacargando
		limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "presupuesto");
	}
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"local": cod_localFKUSer,
		"cod_producto": cod_productoFK,
		"vista_origen": vistaPresupuestoOrigen,
		"funt": "buscarpresupuesto"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmproductos.php",
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
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			presupuestoDocBusquedaSilenciosa = false;
			if (vistaPresupuestoOrigen == "doctor") {
				document.getElementById("table_vista_producto_Presupuesto_doctor").innerHTML = ''
				presupuestoDocSetEstadoBusqueda("No se pudo actualizar la busqueda. Intente nuevamente.", "alerta");
			} else {
				document.getElementById("table_vista_producto_Presupuesto").innerHTML = ''
			}
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			
			if (vistaPresupuestoOrigen == "doctor") {
				document.getElementById("table_vista_producto_Presupuesto_doctor").innerHTML = ''
			} else {
				document.getElementById("table_vista_producto_Presupuesto").innerHTML = ''
			}

			try {
				var datos = parsearRespuestaAjaxPresupuesto(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				if(datos_buscados!=""){
					if (vistaPresupuestoOrigen == "doctor") {
						document.getElementById("table_vista_producto_Presupuesto_doctor").innerHTML = datos_buscados;
						presupuestoDocSetEstadoBusqueda("Lista actualizada. Seleccione un tratamiento.", "ok");
					} else {
						document.getElementById("table_vista_producto_Presupuesto").innerHTML = datos_buscados;
					}
				}else{
					if (vistaPresupuestoOrigen == "doctor") {
						presupuestoDocSetEstadoBusqueda("Sin resultados para esa busqueda.", "alerta");
					}
					if (!presupuestoDocBusquedaSilenciosa) {
						ver_vetana_informativa("PRODUCTO NO ECONTRADO")
					}
					limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", vistaPresupuestoOrigen == "doctor" ? "doctor" : "presupuesto");
				}
				}
				presupuestoDocBusquedaSilenciosa = false;
			} catch (error) {
				presupuestoDocBusquedaSilenciosa = false;
				if (vistaPresupuestoOrigen == "doctor") {
					presupuestoDocSetEstadoBusqueda("No se pudo leer la respuesta de la busqueda.", "alerta");
				}
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function obtenerdatosvistaproductodesdePresupuesto(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'

	idFkProducto = $(datostr).children('td[id="td_id"]').html();
	justificacionProductoPresupuestoSeleccionado = $(datostr).children('td[id="td_datos_2"]').html() || "";
	cargarInsumosProductoPresupuesto(idFkProducto, vistaPresupuestoOrigen == "doctor" ? "doctor" : "presupuesto");

	if (vistaPresupuestoOrigen == "doctor") {
		document.getElementById('inptCodigoPresupuestoDoc').value = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoPresupuestoDoc').value = $(datostr).children('td[id="td_datos_1"]').html();
		//document.getElementById('inpTSeleccCostoPresupuestoDoc').innerHTML = $(datostr).children('td[id="td_datos_11"]').html();
		document.getElementById('inptCantidadPresupuestoDoc').value = "1";
		document.getElementById('inptPrecioPresupuestoDoc').value = $(datostr).children('td[id="td_datos_4"]').html();
		if (typeof odontogramaPrepararTratamientoPresupuesto == "function") {
			odontogramaPrepararTratamientoPresupuesto(idFkProducto, document.getElementById('inptProductoPresupuestoDoc').value);
		}
		presupuestoDocSetEstadoBusqueda("Tratamiento seleccionado. Revise cantidad y agregue.", "ok");
		//document.getElementById('inptCantidadPresupuestoDoc').focus();
	} else {
		document.getElementById('inptCodigoPresupuesto').value = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoPresupuesto').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inpTSeleccCostoPresupuesto').innerHTML = $(datostr).children('td[id="td_datos_11"]').html();
		document.getElementById('inptCantidadPresupuesto').value = "1";
		document.getElementById('inptPrecioPresupuesto').value = $(datostr).children('td[id="td_datos_4"]').html();
		document.getElementById('inptCantidadPresupuesto').focus();
		separadordemiles(document.getElementById('inptPrecioPresupuesto'))
		calcular_total_Presupuesto()
	}
}

function obtenerContenedoresInsumosPresupuesto(vistaOrigen) {
	if (vistaOrigen == "doctor") {
		return ["table_presupuesto_doc_insumos_producto"];
	}
	return ["table_presupuesto_insumos_producto", "table_presupuesto_insumos_producto_prioritario"];
}

function escaparHtmlPresupuesto(valor) {
	return String(valor == null ? "" : valor)
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

const PRESUPUESTO_JUSTIFICACION_GENERICA = "Este tratamiento forma parte del plan recomendado y ayuda a prevenir complicaciones futuras. La indicacion final debe ser validada por el profesional tratante.";

function presupuestoTextoElemento(id) {
	const elemento = document.getElementById(id);
	return elemento ? String(elemento.value || elemento.textContent || "").trim() : "";
}

function presupuestoAsignarTexto(id, texto) {
	const elemento = document.getElementById(id);
	if (elemento) {
		elemento.textContent = texto || "-";
	}
}

function presupuestoMontoVisual(valor) {
	valor = String(valor || "0").trim();
	if (valor == "" || valor == "0") {
		return "0 Gs.";
	}
	return /gs\.?$/i.test(valor) ? valor : valor + " Gs.";
}

function presupuestoObtenerNombreUsuarioActual() {
	if (typeof nombreUsuarioVentaActual == "function") {
		return nombreUsuarioVentaActual();
	}
	if (typeof datosPerfilUsuarioActual != "undefined" && datosPerfilUsuarioActual) {
		return datosPerfilUsuarioActual.nombre_persona || datosPerfilUsuarioActual.nombre || "";
	}
	if (typeof userid != "undefined") {
		return localStorage.getItem("nombreUsuario" + userid) || "";
	}
	return "";
}

function actualizarResumenPresupuestoVenta() {
	const paciente = presupuestoTextoElemento("inptNombreClientePresupuesto") || presupuestoTextoElemento("inptNombreClientePresupuestoDoc") || presupuestoTextoElemento("inptNombreApellidoCliente");
	const cedula = presupuestoTextoElemento("inptDocumentoClientePresupuesto") || presupuestoTextoElemento("inptDocumentoClientePresupuestoDoc") || presupuestoTextoElemento("inptNroDocCliente");
	const telefono = presupuestoTextoElemento("inptWhatsappClientePresupuestoDoc") || presupuestoTextoElemento("inptTelefonoClientePresupuestoDoc") || presupuestoTextoElemento("inptNrowhatsappCliente") || presupuestoTextoElemento("inptNroTelefCliente");
	const direccion = presupuestoTextoElemento("inptDireccionClientePresupuestoDoc") || presupuestoTextoElemento("inptDireccionCliente") || presupuestoTextoElemento("inptReferenciaCliente");
	const fecha = new Date();
	const fechaTexto = String(fecha.getDate()).padStart(2, "0") + "/" + String(fecha.getMonth() + 1).padStart(2, "0") + "/" + fecha.getFullYear();
	const profesional = typeof presupuestoDocObtenerProfesionalNombre == "function" ? presupuestoDocObtenerProfesionalNombre() : presupuestoObtenerNombreUsuarioActual();

	presupuestoAsignarTexto("presupuestoResumenPaciente", paciente || "Sin seleccionar");
	presupuestoAsignarTexto("presupuestoResumenCedula", cedula || "-");
	presupuestoAsignarTexto("presupuestoResumenTelefono", telefono || "-");
	presupuestoAsignarTexto("presupuestoResumenDireccion", direccion || "-");
	presupuestoAsignarTexto("presupuestoResumenNumero", idabmPresupuesto ? "#" + idabmPresupuesto : "Pendiente");
	presupuestoAsignarTexto("presupuestoResumenFecha", fechaTexto);
	presupuestoAsignarTexto("presupuestoResumenUsuario", presupuestoObtenerNombreUsuarioActual() || "-");
	presupuestoAsignarTexto("presupuestoResumenProfesional", profesional || "-");
	presupuestoAsignarTexto("presupuestoPlanTotalResumen", presupuestoMontoVisual(presupuestoTextoElemento("inptTOTALPresupuestoFORM")));
	presupuestoAsignarTexto("presupuestoPlanProvisorioResumen", presupuestoMontoVisual(presupuestoTextoElemento("inptTOTALPresupuestoFORMPrioritario")));
	actualizarConfirmacionPresupuestoVisual();
}

function obtenerPlanPresupuestoSeleccionadoVisual() {
	const select = document.getElementById("inptSelecctPlanPresupuesto");
	return presupuestoDocNormalizarPlanVenta(select ? select.value : "total");
}

function obtenerLabelPlanPresupuesto(plan) {
	plan = presupuestoDocNormalizarPlanVenta(plan);
	return plan == "prioritario" ? "Plan provisorio" : "Plan de rehabilitacion total";
}

function obtenerMontoPlanPresupuesto(plan) {
	plan = presupuestoDocNormalizarPlanVenta(plan);
	return plan == "prioritario"
		? presupuestoTextoElemento("inptTOTALPresupuestoFORMPrioritario")
		: presupuestoTextoElemento("inptTOTALPresupuestoFORM");
}

function seleccionarPlanPresupuestoVisual(plan) {
	plan = presupuestoDocNormalizarPlanVenta(plan);
	const select = document.getElementById("inptSelecctPlanPresupuesto");
	if (select) {
		select.value = plan;
	}
	document.querySelectorAll("[data-presupuesto-plan-card]").forEach(function (card) {
		card.classList.toggle("is-selected", card.getAttribute("data-presupuesto-plan-card") == plan);
	});
	actualizarConfirmacionPresupuestoVisual();
}

function obtenerContenedorCuotasPresupuesto(plan) {
	return document.getElementById(plan == "prioritario" ? "table_vista_detalles_presupuesto_prioritario" : "table_vista_detalles_presupuesto");
}

function seleccionarCuotaPresupuestoVisual(plan, tabla, actualizarSelect) {
	plan = presupuestoDocNormalizarPlanVenta(plan);
	const contenedor = obtenerContenedorCuotasPresupuesto(plan);
	if (!contenedor || !tabla) {
		return;
	}
	contenedor.querySelectorAll(".presupuesto-cuota-row").forEach(function (fila) {
		fila.classList.remove("is-selected");
	});
	tabla.classList.add("is-selected");
	presupuestoCuotaSeleccionadaVisual[plan] = {
		modalidad: tabla.getAttribute("data-modalidad-presupuesto") || "CREDITO",
		cuotas: tabla.getAttribute("data-cuotas-presupuesto") || "",
		descripcion: tabla.getAttribute("data-descripcion-presupuesto") || "",
		monto: tabla.getAttribute("data-monto-cuota-presupuesto") || ""
	};
	if (actualizarSelect !== false) {
		const selectModalidad = document.getElementById("inptSelecctModalidadPresupuesto");
		if (selectModalidad) {
			selectModalidad.value = presupuestoCuotaSeleccionadaVisual[plan].modalidad;
		}
	}
	seleccionarPlanPresupuestoVisual(plan);
}

function presupuestoNumeroDesdeTexto(valor) {
	if (typeof QuitarSeparadorMilValor == "function") {
		const numero = Number(QuitarSeparadorMilValor(String(valor || "0")));
		return isNaN(numero) ? 0 : numero;
	}
	const normalizado = String(valor || "0").replace(/\./g, "").replace(",", ".").replace(/[^\d.-]/g, "");
	const numero = Number(normalizado);
	return isNaN(numero) ? 0 : numero;
}

function presupuestoDistanciaARangoCuota(monto) {
	if (monto >= PRESUPUESTO_CUOTA_RECOMENDADA_MIN && monto <= PRESUPUESTO_CUOTA_RECOMENDADA_MAX) {
		return 0;
	}
	if (monto < PRESUPUESTO_CUOTA_RECOMENDADA_MIN) {
		return PRESUPUESTO_CUOTA_RECOMENDADA_MIN - monto;
	}
	return monto - PRESUPUESTO_CUOTA_RECOMENDADA_MAX;
}

function obtenerCuotaRecomendadaPresupuesto(filas) {
	const opcionesCredito = filas.map(function (fila) {
		return {
			fila: fila,
			cuotas: Number(fila.getAttribute("data-cuotas-presupuesto") || "0") || 0,
			monto: presupuestoNumeroDesdeTexto(fila.getAttribute("data-monto-cuota-presupuesto") || "0"),
			modalidad: fila.getAttribute("data-modalidad-presupuesto") || ""
		};
	}).filter(function (opcion) {
		return opcion.modalidad == "CREDITO" && opcion.cuotas > 0 && opcion.monto > 0;
	});
	if (!opcionesCredito.length) {
		return null;
	}
	const enRango = opcionesCredito.filter(function (opcion) {
		return opcion.monto >= PRESUPUESTO_CUOTA_RECOMENDADA_MIN && opcion.monto <= PRESUPUESTO_CUOTA_RECOMENDADA_MAX;
	});
	const candidatas = enRango.length ? enRango : opcionesCredito;
	candidatas.sort(function (a, b) {
		const distanciaA = presupuestoDistanciaARangoCuota(a.monto);
		const distanciaB = presupuestoDistanciaARangoCuota(b.monto);
		if (distanciaA != distanciaB) {
			return distanciaA - distanciaB;
		}
		return a.cuotas - b.cuotas;
	});
	return candidatas[0].fila;
}

function seleccionarCuotaDefaultPresupuesto(plan, modalidad) {
	plan = presupuestoDocNormalizarPlanVenta(plan);
	modalidad = String(modalidad || "CREDITO").toUpperCase();
	const contenedor = obtenerContenedorCuotasPresupuesto(plan);
	if (!contenedor) {
		return null;
	}
	const filas = Array.from(contenedor.querySelectorAll(".presupuesto-cuota-row"));
	if (!filas.length) {
		return null;
	}
	let elegida = null;
	if (modalidad == "CONTADO") {
		elegida = filas.find(function (fila) {
			return fila.getAttribute("data-modalidad-presupuesto") == "CONTADO";
		});
	} else {
		elegida = obtenerCuotaRecomendadaPresupuesto(filas) || filas.find(function (fila) {
			return fila.getAttribute("data-modalidad-presupuesto") == "CREDITO";
		});
	}
	if (elegida) {
		contenedor.querySelectorAll(".presupuesto-cuota-row").forEach(function (fila) {
			fila.classList.remove("is-selected");
		});
		elegida.classList.add("is-selected");
		presupuestoCuotaSeleccionadaVisual[plan] = {
			modalidad: elegida.getAttribute("data-modalidad-presupuesto") || modalidad,
			cuotas: elegida.getAttribute("data-cuotas-presupuesto") || "",
			descripcion: elegida.getAttribute("data-descripcion-presupuesto") || "",
			monto: elegida.getAttribute("data-monto-cuota-presupuesto") || ""
		};
	}
	return elegida;
}

function actualizarConfirmacionPresupuestoVisual() {
	const plan = obtenerPlanPresupuestoSeleccionadoVisual();
	const selectModalidad = document.getElementById("inptSelecctModalidadPresupuesto");
	const modalidad = String(selectModalidad ? selectModalidad.value : "CREDITO").toUpperCase();
	let cuota = presupuestoCuotaSeleccionadaVisual[plan];
	if (!cuota || cuota.modalidad != modalidad) {
		seleccionarCuotaDefaultPresupuesto(plan, modalidad);
		cuota = presupuestoCuotaSeleccionadaVisual[plan];
	}
	const detalle = cuota && cuota.descripcion
		? modalidad.charAt(0) + modalidad.slice(1).toLowerCase() + " - " + cuota.descripcion
		: modalidad.charAt(0) + modalidad.slice(1).toLowerCase();
	presupuestoAsignarTexto("presupuestoConfirmacionPlan", obtenerLabelPlanPresupuesto(plan));
	presupuestoAsignarTexto("presupuestoConfirmacionDetalle", detalle);
	presupuestoAsignarTexto("presupuestoConfirmacionMonto", presupuestoMontoVisual(obtenerMontoPlanPresupuesto(plan)));
	document.querySelectorAll("[data-presupuesto-plan-card]").forEach(function (card) {
		card.classList.toggle("is-selected", card.getAttribute("data-presupuesto-plan-card") == plan);
	});
	actualizarEstadoConfirmacionPresupuesto();
}

function obtenerCuotasPresupuestoSeleccionadas() {
	const plan = obtenerPlanPresupuestoSeleccionadoVisual();
	const cuota = presupuestoCuotaSeleccionadaVisual[plan];
	if (cuota && cuota.cuotas) {
		return cuota.cuotas;
	}
	const modalidad = String(presupuestoTextoElemento("inptSelecctModalidadPresupuesto") || "CREDITO").toUpperCase();
	return modalidad == "CONTADO" ? "1" : "";
}

function planPresupuestoTieneTratamientos(plan) {
	plan = presupuestoDocNormalizarPlanVenta(plan);
	const contenedor = document.getElementById(plan == "prioritario" ? "table_vista_producto_presupuestoDetalle_prioritario" : "table_vista_producto_presupuestoDetalle");
	return !!(contenedor && contenedor.querySelector("tr[name=tdDetallePresupuesto]"));
}

function actualizarEstadoConfirmacionPresupuesto() {
	const boton = document.getElementById("btnConfirmarVentaPresupuesto");
	if (!boton) {
		return;
	}
	const plan = obtenerPlanPresupuestoSeleccionadoVisual();
	const monto = Number(QuitarSeparadorMilValor(obtenerMontoPlanPresupuesto(plan) || "0")) || 0;
	const cuota = presupuestoCuotaSeleccionadaVisual[plan];
	const listo = planPresupuestoTieneTratamientos(plan) && monto > 0 && !!cuota;
	boton.disabled = !listo;
	boton.classList.toggle("is-disabled", !listo);
	boton.title = listo ? "Confirmar venta" : "Seleccione un plan con tratamientos y una forma de pago.";
}

function mejorarCuotasPresupuestoVisual() {
	[
		{ plan: "total", id: "table_vista_detalles_presupuesto" },
		{ plan: "prioritario", id: "table_vista_detalles_presupuesto_prioritario" }
	].forEach(function (grupo) {
		const contenedor = document.getElementById(grupo.id);
		if (!contenedor) {
			return;
		}
		Array.from(contenedor.querySelectorAll("table.tableRegistroSearch")).forEach(function (tabla) {
			const celdas = tabla.querySelectorAll("td");
			const cuotasTexto = (celdas[0]?.textContent || "").trim();
			const montoCuota = (celdas[1]?.textContent || "").trim();
			const descripcion = (celdas[2]?.textContent || "").trim();
			const modalidad = cuotasTexto.toUpperCase() == "CONTADO" ? "CONTADO" : "CREDITO";
			tabla.classList.add("presupuesto-cuota-row");
			tabla.setAttribute("data-modalidad-presupuesto", modalidad);
			tabla.setAttribute("data-cuotas-presupuesto", cuotasTexto.toUpperCase() == "CONTADO" ? "1" : cuotasTexto);
			tabla.setAttribute("data-monto-cuota-presupuesto", montoCuota);
			tabla.setAttribute("data-descripcion-presupuesto", descripcion);
			tabla.classList.remove("is-suggested");
			if (tabla.getAttribute("data-presupuesto-cuota-ready") != "1") {
				tabla.setAttribute("data-presupuesto-cuota-ready", "1");
				tabla.addEventListener("click", function () {
					seleccionarCuotaPresupuestoVisual(grupo.plan, tabla, true);
				});
			}
		});
		const cuotaRecomendada = obtenerCuotaRecomendadaPresupuesto(Array.from(contenedor.querySelectorAll(".presupuesto-cuota-row")));
		if (cuotaRecomendada) {
			cuotaRecomendada.classList.add("is-suggested");
		}
		seleccionarCuotaDefaultPresupuesto(grupo.plan, document.getElementById("inptSelecctModalidadPresupuesto")?.value || "CREDITO");
	});
}

function renderizarJustificacionesPresupuesto() {
	["table_vista_producto_presupuestoDetalle", "table_vista_producto_presupuestoDetalle_prioritario"].forEach(function (idContenedor) {
		const contenedor = document.getElementById(idContenedor);
		if (!contenedor) {
			return;
		}
		contenedor.querySelectorAll(".presupuesto-tratamiento-justificacion").forEach(function (fila) {
			fila.remove();
		});
		Array.from(contenedor.querySelectorAll("table.tableRegistroSearch")).forEach(function (tabla) {
			const fila = tabla.querySelector("tr[name=tdDetallePresupuesto]");
			if (!fila) {
				return;
			}
			const texto = (tabla.querySelector("#td_datos_16")?.textContent || "").trim() || PRESUPUESTO_JUSTIFICACION_GENERICA;
			const filaJustificacion = document.createElement("tr");
			filaJustificacion.className = "presupuesto-tratamiento-justificacion";
			filaJustificacion.innerHTML = "<td colspan='5'><span>Por que realizarlo?</span><p>" + escaparHtmlPresupuesto(texto) + "</p></td>";
			fila.parentNode.appendChild(filaJustificacion);
		});
	});
}

function inicializarPresupuestoVisual() {
	document.querySelectorAll("[data-presupuesto-plan-card]").forEach(function (card) {
		if (card.getAttribute("data-presupuesto-card-ready") == "1") {
			return;
		}
		card.setAttribute("data-presupuesto-card-ready", "1");
		card.setAttribute("tabindex", "0");
		card.addEventListener("click", function (event) {
			if (event.target.closest("button,input,select,textarea,a") || event.target.closest("tr[name=tdDetallePresupuesto]")) {
				return;
			}
			seleccionarPlanPresupuestoVisual(card.getAttribute("data-presupuesto-plan-card"));
		});
		card.addEventListener("keydown", function (event) {
			if (event.key == "Enter" || event.key == " ") {
				event.preventDefault();
				seleccionarPlanPresupuestoVisual(card.getAttribute("data-presupuesto-plan-card"));
			}
		});
	});
	mejorarCuotasPresupuestoVisual();
	renderizarJustificacionesPresupuesto();
	actualizarResumenPresupuestoVenta();
}

async function confirmarVentaPresupuestoSeleccionada() {
	actualizarConfirmacionPresupuestoVisual();
	const plan = obtenerPlanPresupuestoSeleccionadoVisual();
	const modalidad = presupuestoTextoElemento("inptSelecctModalidadPresupuesto") || "CREDITO";
	const monto = presupuestoMontoVisual(obtenerMontoPlanPresupuesto(plan));
	const cuota = presupuestoCuotaSeleccionadaVisual[plan];
	const formaPago = cuota && cuota.descripcion ? cuota.descripcion : modalidad;
	if (!planPresupuestoTieneTratamientos(plan)) {
		ver_vetana_informativa("Plan vacio", "Seleccione un plan con tratamientos para confirmar la venta.", "advertencia");
		return false;
	}
	const mensaje = "Va a confirmar el <b>" + escaparHtmlPresupuesto(obtenerLabelPlanPresupuesto(plan)) + "</b><br>Forma de pago: <b>" + escaparHtmlPresupuesto(formaPago) + "</b><br>Total: <b>" + escaparHtmlPresupuesto(monto) + "</b><br><br>Desea continuar?";
	let confirmar = true;
	if (typeof ver_ventana_confirmacion == "function") {
		document.body.classList.add("presupuesto-confirmacion-venta-abierta");
		try {
			confirmar = await ver_ventana_confirmacion(mensaje, "Confirmar venta");
		} finally {
			document.body.classList.remove("presupuesto-confirmacion-venta-abierta");
		}
	} else {
		confirmar = confirm("Va a confirmar el " + obtenerLabelPlanPresupuesto(plan) + ". Desea continuar?");
	}
	if (!confirmar) {
		return false;
	}
	return presupuestoAVenta();
}

if (typeof document != "undefined") {
	if (document.readyState == "loading") {
		document.addEventListener("DOMContentLoaded", inicializarPresupuestoVisual);
	} else {
		setTimeout(inicializarPresupuestoVisual, 0);
	}
}

function limpiarPanelInsumosProductoPresupuesto(mensaje, vistaOrigen) {
	obtenerContenedoresInsumosPresupuesto(vistaOrigen).forEach(function (contenedorId) {
		var contenedor = document.getElementById(contenedorId);
		if (!contenedor) {
			return;
		}
		contenedor.innerHTML = "<p class='pTituloC' style='padding:12px;text-align:center;color:#607080'>" + escaparHtmlPresupuesto(mensaje || "") + "</p>";
	});
}

function cargarInsumosProductoPresupuesto(codProducto, vistaOrigen) {
	vistaOrigen = vistaOrigen == "doctor" ? "doctor" : "presupuesto";
	if (codProducto == "") {
		limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", vistaOrigen);
		return;
	}

	obtenerContenedoresInsumosPresupuesto(vistaOrigen).forEach(function (contenedorId) {
		var contenedor = document.getElementById(contenedorId);
		if (contenedor) {
			contenedor.innerHTML = paginacargando;
		}
	});

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_producto": codProducto,
		"funt": "obtener_insumos_producto"
	};

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmInsumos.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
			limpiarPanelInsumosProductoPresupuesto("No se pudieron cargar los insumos.", vistaOrigen);
		},
		success: function (responseText) {
			try {
				var datos = parsearRespuestaAjaxPresupuesto(responseText);
				if (respuestaJqueryAjax(datos["1"]) == true) {
					renderInsumosProductoPresupuesto(datos.insumos || [], vistaOrigen);
				} else {
					limpiarPanelInsumosProductoPresupuesto("No se pudieron cargar los insumos.", vistaOrigen);
				}
			} catch (error) {
				limpiarPanelInsumosProductoPresupuesto("No se pudieron cargar los insumos.", vistaOrigen);
				GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
			}
		}
	});
}

function renderInsumosProductoPresupuesto(insumos, vistaOrigen) {
	if (insumos.length == 0) {
		limpiarPanelInsumosProductoPresupuesto("Este tratamiento no tiene insumos asociados.", vistaOrigen);
		return;
	}

	var html = "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' style='width:100%'>";
	html += "<tr>";
	html += "<td class='tdRegistroSearch' style='width:12%;font-weight:bold'>Cod.</td>";
	html += "<td class='tdRegistroSearch' style='width:42%;font-weight:bold'>Insumo</td>";
	html += "<td class='tdRegistroSearch' style='width:18%;font-weight:bold'>Cant.</td>";
	html += "<td class='tdRegistroSearch' style='width:28%;font-weight:bold'>Unidad</td>";
	html += "</tr>";
	for (var i = 0; i < insumos.length; i++) {
		var fila = insumos[i];
		html += "<tr>";
		html += "<td class='tdRegistroSearch'>" + escaparHtmlPresupuesto(fila.id_insumo) + "</td>";
		html += "<td class='tdRegistroSearch'>" + escaparHtmlPresupuesto(fila.nombre) + "</td>";
		html += "<td class='tdRegistroSearch' style='text-align:center'>" + escaparHtmlPresupuesto(fila.cantidad) + "</td>";
		html += "<td class='tdRegistroSearch'>" + escaparHtmlPresupuesto(fila.unidad_medida) + "</td>";
		html += "</tr>";
	}
	html += "</table>";

	obtenerContenedoresInsumosPresupuesto(vistaOrigen).forEach(function (contenedorId) {
		var contenedor = document.getElementById(contenedorId);
		if (contenedor) {
			contenedor.innerHTML = html;
		}
	});
}

function calcular_total_Presupuesto() {
	var c = QuitarSeparadorMilValor(document.getElementById('inptCantidadPresupuesto').value);
	var t = QuitarSeparadorMilValor(document.getElementById('inptPrecioPresupuesto').value);
	

	if (isNaN(c)) {
		document.getElementById('inptPrecioPresupuesto').value = 0;
		c = 0;
	}

	var c = parseFloat(c);
	var t = parseFloat(t);
	document.getElementById('inptTotalPresupuesto').value = (t * c);
	separadordemiles(document.getElementById('inptPrecioPresupuesto'))
	separadordemiles(document.getElementById('inptTotalPresupuesto'))
	// separadordemiles(document.getElementById('inptTOTALPresupuestoFORM'))	
	
}

function seleccionarpreciospresupuesto(datos) {
	document.getElementById("inptPrecioPresupuesto").value = datos.value
	calcular_total_Presupuesto();
}

function limpirarAddPresupuesto(vistaOrigen){
	justificacionProductoPresupuestoSeleccionado = "";
	if (vistaOrigen == "doctor") {
		document.getElementById('inptCodigoPresupuestoDoc').value = ""
		document.getElementById('inptProductoPresupuestoDoc').value = ""
		document.getElementById('inptPrecioPresupuestoDoc').value = ""
		document.getElementById('inpTSeleccCostoPresupuestoDoc').value = ""
		document.getElementById('inptCantidadPresupuestoDoc').value = ""
		document.getElementById('inptTotalPresupuestoDoc').value = ""
		limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "doctor");
		presupuestoDocMostrarEstadoInicialBusqueda();
		return;
	}

	document.getElementById('inptCodigoPresupuesto').value = ""
	document.getElementById('inptProductoPresupuesto').value = ""
	document.getElementById('inptPrecioPresupuesto').value = ""
	document.getElementById('inpTSeleccCostoPresupuesto').value = ""
	document.getElementById('inptCantidadPresupuesto').value = ""
	document.getElementById('inptTotalPresupuesto').value = ""
	limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "presupuesto");
}

function buscarproductoporcodigoPresupuesto(vistaOrigen= 'presupuesto') {
	
	var buscador = document.getElementById('inptCodigoPresupuesto').value;
	if (vistaOrigen == "doctor") {
		buscador = document.getElementById('inptCodigoPresupuestoDoc').value;
	}
	
	if(buscador==""){
		if (vistaOrigen == "doctor") {
			presupuestoDocSetEstadoBusqueda("Ingrese un codigo exacto para buscar.", "alerta");
		}
		return
	}
	verCerrarEfectoCargando("1")
	if (vistaOrigen == "doctor") {
		presupuestoDocSetEstadoBusqueda("Buscando codigo exacto...", "buscar");
	}
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"funt": "buscarporcodigoPresupuesto"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmproductos.php",
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
			verCerrarEfectoCargando("2")
			if (vistaOrigen == "doctor") {
				presupuestoDocSetEstadoBusqueda("No se pudo buscar el codigo. Intente nuevamente.", "alerta");
			}
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			verCerrarEfectoCargando("2")
			try {
				var datos = parsearRespuestaAjaxPresupuesto(Respuesta);
				Respuesta = datos["1"];
				
					 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					datos_buscados = datos["2"];
					
					idFkProducto = datos["2"];
					justificacionProductoPresupuestoSeleccionado = datos["6"] || "";
					cargarInsumosProductoPresupuesto(idFkProducto, vistaOrigen == "doctor" ? "doctor" : "presupuesto");

					if (vistaOrigen == "doctor") {
						document.getElementById('inptCodigoPresupuestoDoc').value = datos["5"];
						document.getElementById('inptProductoPresupuestoDoc').value = datos["3"];
						document.getElementById('inptCantidadPresupuestoDoc').value = "1";
						document.getElementById('inptPrecioPresupuestoDoc').value = datos["4"];
						if (typeof odontogramaPrepararTratamientoPresupuesto == "function") {
							odontogramaPrepararTratamientoPresupuesto(idFkProducto, datos["3"]);
						}
						presupuestoDocSetEstadoBusqueda("Tratamiento encontrado por codigo. Revise cantidad y agregue.", "ok");
					} else {
						document.getElementById('inptCodigoPresupuesto').value = datos["5"];
						document.getElementById('inptProductoPresupuesto').value = datos["3"];
						// document.getElementById('inpTSeleccCostoPresupuesto').innerHTML = datos["2"];
						document.getElementById('inptCantidadPresupuesto').value = "1";
						document.getElementById('inptPrecioPresupuesto').value = datos["4"];
						document.getElementById('inptCantidadPresupuesto').focus();
						separadordemiles(document.getElementById('inptPrecioPresupuesto'));
						calcular_total_Presupuesto()
					}
				} else if (vistaOrigen == "doctor") {
					presupuestoDocSetEstadoBusqueda("No se encontro un tratamiento con ese codigo.", "alerta");
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				if (vistaOrigen == "doctor") {
					presupuestoDocSetEstadoBusqueda("No se pudo leer la respuesta del codigo.", "alerta");
				}
					var titulo="Error: "+error+" \r\n Consola: "+textoRespuestaAjaxPresupuesto(responseText)
				GuardarArchivosLog(titulo)
			}
		}
	});
}


// Auto focus siguiente input
function focusSiguiente() {
  document.getElementById("inptPrecioPresupuesto").focus();
}

// Calcular total dinámico
function calcular_total_Presupuesto() {
  const esDoctor = vistaPresupuestoOrigen == "doctor" && document.getElementById("inptPrecioPresupuestoDoc");
  const inputPrecio = document.getElementById(esDoctor ? "inptPrecioPresupuestoDoc" : "inptPrecioPresupuesto");
  const inputCantidad = document.getElementById(esDoctor ? "inptCantidadPresupuestoDoc" : "inptCantidadPresupuesto");
  const inputTotal = document.getElementById(esDoctor ? "inptTotalPresupuestoDoc" : "inptTotalPresupuesto");
  if (!inputPrecio || !inputCantidad || !inputTotal) {
	  return;
  }
  let precio = parseFloat(String(inputPrecio.value || "0").replace(/\./g,"")) || 0;
  let cantidad = parseInt(inputCantidad.value, 10) || 0;
  inputTotal.value = (precio * cantidad).toLocaleString("es-ES");
}

function presupuestoDocPacienteSeleccionado() {
	const documento = document.getElementById("inptDocumentoClientePresupuestoDoc")?.value.trim() || "";
	const nombre = document.getElementById("inptNombreClientePresupuestoDoc")?.value.trim() || "";
	return !!(idFkCliente && (documento || nombre));
}

function presupuestoDocDatosPaciente() {
	return {
		nombre: document.getElementById("inptNombreClientePresupuestoDoc")?.value.trim() || "",
		documento: document.getElementById("inptDocumentoClientePresupuestoDoc")?.value.trim() || "",
		telefono: document.getElementById("inptTelefonoClientePresupuestoDoc")?.value.trim() || "",
		whatsapp: document.getElementById("inptWhatsappClientePresupuestoDoc")?.value.trim() || "",
		direccion: document.getElementById("inptDireccionClientePresupuestoDoc")?.value.trim() || ""
	};
}

function presupuestoDocDatosFaltantesPaciente() {
	const datos = presupuestoDocDatosPaciente();
	const faltantes = [];
	if (!datos.nombre) { faltantes.push("nombre"); }
	if (!datos.documento) { faltantes.push("cedula"); }
	if (!datos.telefono && !datos.whatsapp) { faltantes.push("telefono/WhatsApp"); }
	if (!datos.direccion) { faltantes.push("direccion"); }
	return faltantes;
}

function presupuestoDocPacienteCompleto() {
	return presupuestoDocPacienteSeleccionado() && presupuestoDocDatosFaltantesPaciente().length == 0;
}

function presupuestoDocPacienteListoParaAvanzar() {
	return presupuestoDocPacienteSeleccionado();
}

function presupuestoDocObtenerResumenTotales() {
	let cantidad = 0;
	let total = 0;
	document.querySelectorAll("#table_vista_producto_presupuestoDetalle_doctor tr[name=tdDetallePresupuesto]").forEach(function (fila) {
		cantidad++;
		const valor = fila.querySelector("#td_datos_11")?.textContent.trim() || "0";
		total += Number(QuitarSeparadorMilValor(valor)) || 0;
	});
	return { cantidad: cantidad, total: total };
}

function actualizarResumenPacientePresupuestoDoc() {
	const datosPaciente = presupuestoDocDatosPaciente();
	const documento = datosPaciente.documento;
	const nombre = datosPaciente.nombre;
	const contacto = datosPaciente.whatsapp || datosPaciente.telefono;
	const faltantes = presupuestoDocDatosFaltantesPaciente();
	const texto = nombre || documento ? [nombre, documento].filter(Boolean).join(" - ") : "Sin seleccionar";
	const campoResumen = document.getElementById("presupuestoDocPacienteResumen");
	const nombreCard = document.getElementById("presupuestoDocPacienteNombreCard");
	const documentoCard = document.getElementById("presupuestoDocPacienteDocumentoCard");
	const telefonoCard = document.getElementById("presupuestoDocPacienteTelefonoCard");
	const direccionCard = document.getElementById("presupuestoDocPacienteDireccionCard");
	const estadoCard = document.getElementById("presupuestoDocPacienteEstadoCard");
	const panelBusqueda = document.getElementById("presupuestoDocPacienteBusqueda");
	const panelResumen = document.getElementById("presupuestoDocPacienteResumenCard");

	if (campoResumen) {
		campoResumen.textContent = texto;
	}
	if (nombreCard) {
		nombreCard.textContent = nombre || "Sin seleccionar";
	}
	if (documentoCard) {
		documentoCard.textContent = documento || "-";
	}
	if (telefonoCard) {
		telefonoCard.textContent = contacto || "-";
	}
	if (direccionCard) {
		direccionCard.textContent = datosPaciente.direccion || "-";
	}
	if (estadoCard) {
		if (!presupuestoDocPacienteSeleccionado()) {
			estadoCard.innerHTML = "";
		} else if (faltantes.length) {
			estadoCard.className = "presupuesto-paciente-estado is-warning";
			estadoCard.textContent = "Datos pendientes: " + faltantes.join(", ") + ". Puede continuar.";
		} else {
			estadoCard.className = "presupuesto-paciente-estado is-ok";
			estadoCard.textContent = "Ficha completa. La doctora solo debe corroborar y continuar.";
		}
	}
	if (panelBusqueda && panelResumen) {
		const seleccionado = presupuestoDocPacienteSeleccionado();
		panelBusqueda.style.display = seleccionado ? "none" : "flex";
		panelResumen.style.display = seleccionado ? "grid" : "none";
	}
	presupuestoDocActualizarEstado();
}

function presupuestoDocActualizarEstado() {
	const contenedor = document.getElementById("divAbmDetallesPresupuestoDoc");
	const etiqueta = document.getElementById("presupuestoDocStepLabel");
	const btnAnterior = document.getElementById("btnPresupuestoRapidoAnterior");
	const btnPrincipal = document.getElementById("btnPresupuestoRapidoPrincipal");
	const totalTexto = document.getElementById("presupuestoDocTotalEstimado");
	const cantidadTexto = document.getElementById("presupuestoDocCantidadTratamientos");
	const resumen = presupuestoDocObtenerResumenTotales();
	const pacienteSeleccionado = presupuestoDocPacienteSeleccionado();
	const pacienteListo = presupuestoDocPacienteListoParaAvanzar();
	const pasos = {
		1: { texto: "Paso 1 de 3: verificar datos del paciente", boton: "Confirmar y continuar" },
		2: { texto: "Paso 2 de 3: registrar situacion actual", boton: "Continuar a tratamientos" },
		3: { texto: "Paso 3 de 3: asignar tratamientos", boton: "Definir plan total y provisorio" },
		4: { texto: "Punto 4: definir plan total o plan provisorio", boton: "Guardar division de planes" }
	};
	const paso = pasos[pasoVistaPresupuestoDoc] ? pasoVistaPresupuestoDoc : 1;

	if (contenedor) {
		contenedor.setAttribute("data-paso", String(paso));
		contenedor.classList.toggle("presupuesto-sin-paciente", !pacienteListo);
	}
	if (etiqueta) {
		etiqueta.textContent = pasos[paso].texto;
	}
	if (btnAnterior) {
		btnAnterior.style.display = paso == 1 ? "none" : "";
	}
	if (btnPrincipal) {
		btnPrincipal.textContent = pasos[paso].boton;
		btnPrincipal.disabled = (paso == 1 && !pacienteSeleccionado) || (paso > 1 && !pacienteListo) || (paso == 3 && resumen.cantidad == 0);
		btnPrincipal.classList.toggle("is-disabled", btnPrincipal.disabled);
	}
	if (cantidadTexto) {
		cantidadTexto.textContent = resumen.cantidad;
	}
	if (totalTexto) {
		totalTexto.textContent = separadordemilesnumero(resumen.total);
	}
	presupuestoDocActualizarStepper(paso > 3 ? 3 : paso, pacienteListo, resumen.cantidad > 0);
	presupuestoDocActualizarAccionesPlanes();
}

function presupuestoDocActualizarStepper(paso, pacienteListo, tieneTratamientos) {
	for (let i = 1; i <= 3; i++) {
		const boton = document.getElementById("presupuestoPasoBtn" + i);
		if (!boton) {
			continue;
		}
		const habilitado = i == 1 || (i == 2 && pacienteListo) || (i == 3 && pacienteListo);
		boton.disabled = !habilitado;
		boton.classList.toggle("is-active", i == paso);
		boton.classList.toggle("is-complete", (i == 1 && pacienteListo && paso > 1) || (i == 2 && paso > 2) || (i == 3 && tieneTratamientos && paso >= 3));
		boton.setAttribute("aria-current", i == paso ? "step" : "false");
	}
}

function presupuestoDocPasoAnterior() {
	if (pasoVistaPresupuestoDoc > 1) {
		verPasoPresupuestoDoc(pasoVistaPresupuestoDoc - 1);
	}
}

function presupuestoDocAccionPrincipal() {
	if (pasoVistaPresupuestoDoc == 1) {
		if (!presupuestoDocPacienteSeleccionado()) {
			ver_vetana_informativa("Faltan datos", "Selecciona el paciente antes de continuar.", "error");
			return false;
		}
		return verPasoPresupuestoDoc(2);
	}
	if (pasoVistaPresupuestoDoc == 2) {
		return verPasoPresupuestoDoc(3);
	}
	return presupuestoDocMostrarPlanes();
}

function presupuestoDocCompletarDatosPaciente() {
	const datosPaciente = presupuestoDocDatosPaciente();
	idAbmCliente = idFkCliente || idAbmCliente;
	document.getElementById("inptNombreApellidoCliente").value = datosPaciente.nombre;
	document.getElementById("inptNroDocCliente").value = datosPaciente.documento;
	document.getElementById("inptNroTelefCliente").value = datosPaciente.telefono;
	document.getElementById("inptNrowhatsappCliente").value = datosPaciente.whatsapp || datosPaciente.telefono;
	document.getElementById("inptDireccionCliente").value = datosPaciente.direccion;
	controlseleccvistacliente = "presupuestoDoctor";
	verCerrarVentanaAbmCliente(true, true, false);
	$("#divAbmCliente2 .abm-cliente-datos-extra").show();
	document.getElementById("divAbmCliente2").style.width = "auto";
	document.getElementById("btnAbmCliente").value = "Actualizar y guardar";
	ver_vetana_informativa("Datos pendientes", "Recepcion debe completar cedula, telefono/WhatsApp y direccion antes de continuar.", "advertencia");
	return false;
}

function presupuestoDocCambiarPaciente() {
	if (tieneTratamientosPresupuestoDoctor() && !confirm("Cambiar el paciente limpiara los tratamientos cargados en este presupuesto. Desea continuar?")) {
		return false;
	}
	if (tieneTratamientosPresupuestoDoctor()) {
		limpirarPresupuesto();
	}
	controlseleccvistacliente = "presupuestoDoctor";
	vercerrarvistacliente("1", "presupuestoDoctor");
}

function presupuestoDocSetEstadoBusqueda(texto, tipo) {
	const estado = document.getElementById("presupuestoDocBusquedaEstado");
	if (!estado) {
		return;
	}
	estado.textContent = texto;
	estado.className = "presupuesto-doc-busqueda-estado";
	estado.classList.add("is-" + (tipo || "info"));
}

function presupuestoDocMostrarEstadoInicialBusqueda() {
	presupuestoDocSetEstadoBusqueda("Escriba un nombre para ver resultados en vivo o ingrese un codigo exacto.", "info");
}

function presupuestoDocLimpiarResultadosBusqueda(texto, tipo) {
	const tabla = document.getElementById("table_vista_producto_Presupuesto_doctor");
	if (tabla) {
		tabla.innerHTML = "";
	}
	presupuestoDocSetEstadoBusqueda(texto, tipo || "info");
}

function presupuestoDocLimpiarProductoSeleccionadoBusqueda() {
	idFkProducto = "";
	justificacionProductoPresupuestoSeleccionado = "";
	["inptPrecioPresupuestoDoc", "inptCantidadPresupuestoDoc", "inptTotalPresupuestoDoc"].forEach(function (idCampo) {
		const campo = document.getElementById(idCampo);
		if (campo) {
			campo.value = "";
		}
	});
	limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "doctor");
}

function presupuestoDocBusquedaDinamica(evento, tipo) {
	const codigoInput = document.getElementById("inptCodigoPresupuestoDoc");
	const nombreInput = document.getElementById("inptProductoPresupuestoDoc");
	const codigo = codigoInput?.value.trim() || "";
	const nombre = nombreInput?.value.trim() || "";
	const esEnter = evento && (evento.keyCode == 13 || evento.key === "Enter");
	clearTimeout(presupuestoDocBusquedaTimer);
	presupuestoDocLimpiarProductoSeleccionadoBusqueda();

	if (tipo == "codigo") {
		if (codigo && nombreInput?.value) {
			nombreInput.value = "";
		}
		const tablaResultados = document.getElementById("table_vista_producto_Presupuesto_doctor");
		if (tablaResultados) {
			tablaResultados.innerHTML = "";
		}
		if (esEnter) {
			buscarproductoporcodigoPresupuesto("doctor");
			return;
		}
		if (!codigo) {
			presupuestoDocLimpiarResultadosBusqueda("Escriba un nombre para ver resultados en vivo o ingrese un codigo exacto.", "info");
			return;
		}
		presupuestoDocSetEstadoBusqueda("Codigo listo. Presione Enter o Buscar codigo para buscar coincidencia exacta.", "info");
		return;
	}

	if (nombre && codigoInput?.value) {
		codigoInput.value = "";
	}
	if (esEnter) {
		if (nombre.length < 3) {
			presupuestoDocLimpiarResultadosBusqueda("Escriba al menos 3 letras para buscar por nombre.", "alerta");
			return;
		}
		presupuestoDocSetEstadoBusqueda("Actualizando resultados por nombre...", "buscar");
		buscarvistaproductoPresupuesto("doctor");
		return;
	}
	if (!nombre) {
		presupuestoDocLimpiarResultadosBusqueda("Escriba un nombre para ver resultados en vivo o ingrese un codigo exacto.", "info");
		return;
	}
	if (nombre.length < 3) {
		presupuestoDocLimpiarResultadosBusqueda("Escriba al menos 3 letras para actualizar la lista.", "alerta");
		return;
	}
	presupuestoDocSetEstadoBusqueda("Actualizando resultados por nombre...", "buscar");
	presupuestoDocBusquedaTimer = setTimeout(function () {
		const nombreActual = document.getElementById("inptProductoPresupuestoDoc")?.value.trim() || "";
		if (nombreActual.length >= 3) {
			presupuestoDocBusquedaSilenciosa = true;
			buscarvistaproductoPresupuesto("doctor");
		}
	}, 360);
}

function aplicarEstadoPrioritarioDetalleDoc() {
	document.querySelectorAll("#table_vista_producto_presupuestoDetalle_doctor > table").forEach(function (tabla) {
		asegurarCampoAlternativoPresupuestoDoc(tabla);
		const esPrioritario = tabla.querySelector('#td_datos_12')?.textContent.trim() === "1";
		const esAlternativo = tabla.querySelector('#td_datos_13')?.textContent.trim() === "1";
		tabla.classList.toggle("presupuesto-doc-plan-a", !esAlternativo);
		//tabla.classList.toggle("presupuesto-doc-prioritario", esPrioritario);
	});
	document.querySelectorAll("#table_vista_producto_presupuestoDetalle_plan_a_doctor > table").forEach(function (tabla) {
		tabla.classList.add("presupuesto-doc-plan-a");
	});
	document.querySelectorAll("#table_vista_producto_presupuestoDetalle_prioritario_doctor > table").forEach(function (tabla) {
		tabla.classList.add("presupuesto-doc-plan-b");
	});
	prepararDragDropPresupuestoDoc();
}

function asegurarCampoAlternativoPresupuestoDoc(tabla) {
	if (!tabla || tabla.querySelector("#td_datos_13")) {
		return;
	}
	const fila = tabla.querySelector("tr[name=tdDetallePresupuesto]");
	if (!fila) {
		return;
	}
	const campo = document.createElement("td");
	campo.id = "td_datos_13";
	campo.style.display = "none";
	campo.textContent = "0";
	fila.appendChild(campo);
}

function obtenerIdDetallePresupuestoDoc(tabla) {
	if (tabla?.dataset?.idDetallePresupuesto) {
		return tabla.dataset.idDetallePresupuesto;
	}
	if (!tabla || !tabla.id) {
		return "";
	}
	if (tabla.id.indexOf("tdDetalleVenta_") !== 0) {
		return "";
	}
	return tabla.id.replace("tdDetalleVenta_", "");
}

function obtenerTablaDetallePresupuestoDoc(idDetalle, contenedorId) {
	return document.querySelector("#" + contenedorId + " #tdDetalleVenta_" + idDetalle);
}

function clonarDetallePlanPresupuestoDoc(tablaOrigen) {
	const clon = tablaOrigen.cloneNode(true);
	asegurarCampoAlternativoPresupuestoDoc(clon);
	clon.removeAttribute("draggable");
	clon.querySelectorAll("[ondragstart]").forEach(function (elemento) {
		elemento.removeAttribute("ondragstart");
	});
	return clon;
}

function obtenerDatosDetallePresupuestoDoc(tabla) {
	if (!tabla) {
		return null;
	}
	asegurarCampoAlternativoPresupuestoDoc(tabla);
	return {
		cod_producto: tabla.querySelector("#td_datos_14")?.textContent.trim() || "",
		codigo: tabla.querySelector("#td_datos_1")?.textContent.trim() || "",
		nombre: tabla.querySelector("#td_datos_2")?.textContent.trim() || "",
		cantidad: tabla.querySelector("#td_datos_3 span")?.textContent.trim() || tabla.querySelector("#td_datos_3")?.textContent.trim() || "1",
		precio: tabla.querySelector("#td_datos_10")?.textContent.trim() || "0",
		total: tabla.querySelector("#td_datos_11")?.textContent.trim() || "0",
		precio_contado: tabla.querySelector("#td_datos_9")?.textContent.trim() || "",
		justificacion: tabla.querySelector("#td_datos_16")?.textContent.trim() || ""
	};
}

function presupuestoDocTablaEsPrioritaria(tabla) {
	asegurarCampoAlternativoPresupuestoDoc(tabla);
	return tabla?.querySelector("#td_datos_12")?.textContent.trim() === "1";
}

function presupuestoDocEtiquetaCantidad(cantidad) {
	const numero = Number(cantidad);
	return (numero == 1 ? "1 tratamiento" : cantidad + " tratamientos");
}

function presupuestoDocActualizarContadoresPlanes(total, provisorio) {
	const totalTexto = document.getElementById("presupuestoDocPlanTotalCantidad");
	const provisorioTexto = document.getElementById("presupuestoDocPlanProvisorioCantidad");
	if (totalTexto) {
		totalTexto.textContent = presupuestoDocEtiquetaCantidad(total);
	}
	if (provisorioTexto) {
		provisorioTexto.textContent = presupuestoDocEtiquetaCantidad(provisorio);
	}
}

function presupuestoDocCrearEstadoVacio(texto, ayuda, mostrarAccion) {
	const bloque = document.createElement("div");
	bloque.className = "presupuesto-doc-empty-state";
	const titulo = document.createElement("strong");
	titulo.textContent = texto;
	bloque.appendChild(titulo);
	if (ayuda) {
		const subtitulo = document.createElement("span");
		subtitulo.textContent = ayuda;
		bloque.appendChild(subtitulo);
	}
	if (mostrarAccion) {
		const boton = document.createElement("button");
		boton.type = "button";
		boton.className = "presupuesto-accion-secundaria";
		boton.textContent = "Volver al paso 3";
		boton.onclick = presupuestoDocVolverPaso3;
		bloque.appendChild(boton);
	}
	return bloque;
}

function presupuestoDocPrepararTablaPlanClinico(clon, plan, incluido) {
	const idDetalle = obtenerIdDetallePresupuestoDoc(clon);
	const idSeguro = String(idDetalle || "").replace(/[^0-9A-Za-z_-]/g, "");
	clon.classList.add("presupuesto-doc-plan-row");
	clon.classList.toggle("presupuesto-doc-plan-row-incluido", !!incluido);
	clon.classList.toggle("presupuesto-doc-plan-a", plan == "total");
	clon.classList.toggle("presupuesto-doc-plan-b", plan == "provisorio");
	clon.removeAttribute("draggable");
	clon.removeAttribute("ondragstart");
	clon.removeAttribute("onpointerdown");

	const fila = clon.querySelector("tr[name=tdDetallePresupuesto]");
	if (fila) {
		fila.removeAttribute("onclick");
		fila.onclick = null;
	}
	clon.querySelectorAll(".presupuesto-doc-trash-btn").forEach(function (boton) {
		boton.remove();
	});
	clon.querySelectorAll("button, input[type=button]").forEach(function (boton) {
		const texto = String(boton.textContent || boton.value || "").trim().toLowerCase();
		if (texto.indexOf("ubicacion") >= 0) {
			boton.remove();
		}
	});

	const accion = document.createElement("div");
	accion.className = "presupuesto-doc-plan-row-action";
	if (plan == "total") {
		const etiqueta = document.createElement("label");
		etiqueta.className = "presupuesto-doc-check-provisorio";
		const checkbox = document.createElement("input");
		checkbox.type = "checkbox";
		checkbox.checked = !!incluido;
		checkbox.setAttribute("aria-label", "Incluir en plan provisorio");
		checkbox.onchange = function () {
			presupuestoDocToggleProvisorio(idSeguro, checkbox.checked);
		};
		const texto = document.createElement("span");
		texto.textContent = "Incluir en plan provisorio";
		etiqueta.appendChild(checkbox);
		etiqueta.appendChild(texto);
		accion.appendChild(etiqueta);
		if (incluido) {
			const estado = document.createElement("span");
			estado.className = "presupuesto-doc-incluido-label";
			estado.textContent = "Incluido en provisorio";
			accion.appendChild(estado);
		}
	} else {
		const botonQuitar = document.createElement("button");
		botonQuitar.type = "button";
		botonQuitar.className = "presupuesto-doc-quitar-provisorio";
		botonQuitar.textContent = "Quitar del provisorio";
		botonQuitar.onclick = function () {
			presupuestoDocQuitarProvisorio(idSeguro);
		};
		accion.appendChild(botonQuitar);
	}
	const filaAccion = document.createElement("tr");
	filaAccion.className = "presupuesto-doc-plan-row-action-row";
	const celdaAccion = document.createElement("td");
	celdaAccion.colSpan = 16;
	celdaAccion.appendChild(accion);
	filaAccion.appendChild(celdaAccion);
	(clon.tBodies[0] || clon).appendChild(filaAccion);
	return clon;
}

function renderizarPlanesDetallePresupuestoDoc() {
	const origen = document.getElementById("table_vista_producto_presupuestoDetalle_doctor");
	const planA = document.getElementById("table_vista_producto_presupuestoDetalle_plan_a_doctor");
	const planB = document.getElementById("table_vista_producto_presupuestoDetalle_prioritario_doctor");

	if (!origen || !planA || !planB) {
		return;
	}

	planA.innerHTML = "";
	planB.innerHTML = "";
	let total = 0;
	let provisorio = 0;
	Array.from(origen.children).forEach(function (tablaOriginal) {
		if (!tablaOriginal.matches("table")) {
			return;
		}
		asegurarCampoAlternativoPresupuestoDoc(tablaOriginal);
		const esPrioritario = presupuestoDocTablaEsPrioritaria(tablaOriginal);
		actualizarCamposPlanDetallePresupuestoDoc(obtenerIdDetallePresupuestoDoc(tablaOriginal), esPrioritario ? 1 : 0, 0);
		planA.appendChild(presupuestoDocPrepararTablaPlanClinico(clonarDetallePlanPresupuestoDoc(tablaOriginal), "total", esPrioritario));
		total++;
		if (esPrioritario) {
			planB.appendChild(presupuestoDocPrepararTablaPlanClinico(clonarDetallePlanPresupuestoDoc(tablaOriginal), "provisorio", true));
			provisorio++;
		}
	});

	if (total == 0) {
		planA.appendChild(presupuestoDocCrearEstadoVacio("No hay tratamientos definidos para el plan total.", "", true));
	}
	if (provisorio == 0) {
		planB.appendChild(presupuestoDocCrearEstadoVacio("Sin plan provisorio", "Aun no se seleccionaron tratamientos para el plan provisorio. Seleccione tratamientos desde el plan total.", false));
	}
	presupuestoDocActualizarContadoresPlanes(total, provisorio);
}

function presupuestoDocCargarPlanTotalEnProvisorioSiEstaVacio() {
	const origen = document.getElementById("table_vista_producto_presupuestoDetalle_doctor");
	if (!origen) {
		return false;
	}
	const tablas = Array.from(origen.children).filter(function (tabla) {
		return tabla.matches("table") && obtenerIdDetallePresupuestoDoc(tabla);
	});
	if (tablas.length == 0) {
		return false;
	}
	const yaTieneProvisorio = tablas.some(function (tabla) {
		return presupuestoDocTablaEsPrioritaria(tabla);
	});
	if (yaTieneProvisorio) {
		return false;
	}
	tablas.forEach(function (tabla) {
		actualizarCamposPlanDetallePresupuestoDoc(obtenerIdDetallePresupuestoDoc(tabla), 1, 0);
	});
	return true;
}

function prepararDragDropPresupuestoDoc() {
	const origen = document.getElementById("table_vista_producto_presupuestoDetalle_doctor");
	if (origen) {
		Array.from(origen.children).forEach(function (tabla) {
			if (!tabla.matches("table")) {
				return;
			}
			asegurarCampoAlternativoPresupuestoDoc(tabla);
			tabla.setAttribute("draggable", "true");
			tabla.ondragstart = function (evento) {
				evento.dataTransfer.setData("text/plain", obtenerIdDetallePresupuestoDoc(tabla));
				evento.dataTransfer.effectAllowed = "copy";
			};
			tabla.onpointerdown = function (evento) {
				iniciarArrastreTactilPresupuestoDoc(evento, tabla);
			};
		});
	}

	document.querySelectorAll(".presupuesto-doc-dropzone").forEach(function (zona) {
		zona.ondragover = function (evento) {
			evento.preventDefault();
			evento.dataTransfer.dropEffect = "copy";
			zona.classList.add("presupuesto-doc-dropzone-activa");
		};
		zona.ondragleave = function () {
			zona.classList.remove("presupuesto-doc-dropzone-activa");
		};
		zona.ondrop = function (evento) {
			evento.preventDefault();
			zona.classList.remove("presupuesto-doc-dropzone-activa");
			const idDetalle = evento.dataTransfer.getData("text/plain");
			if (idDetalle) {
				agregarDetalleAPlanPresupuestoDoc(idDetalle, zona.dataset.plan);
			}
		};
	});
}

function desplazarTratamientosPresupuestoDoc(nombreElemento, direccion) {
	const contenedor = document.getElementById(nombreElemento);
	if (!contenedor) {
		return;
	}
	const distancia = Math.max(160, Math.round(contenedor.clientHeight * 0.65));
	contenedor.scrollBy({
		top: distancia * direccion,
		behavior: "smooth"
	});
}

function alternarTratamientosPresupuestoDoc(forzarMostrar) {
	const panel = document.getElementById("presupuestoDocDetallePanel");
	if (!panel) {
		return;
	}
	const debeMostrar = forzarMostrar === true || (forzarMostrar !== false && panel.classList.contains("presupuesto-doc-tratamientos-plegado"));
	panel.classList.toggle("presupuesto-doc-tratamientos-plegado", !debeMostrar);
	const boton = panel.querySelector(".presupuesto-doc-toggle-tratamientos");
	if (boton) {
		boton.textContent = debeMostrar ? "Ocultar origen" : "Mostrar origen";
		boton.setAttribute("aria-expanded", debeMostrar ? "true" : "false");
	}
}

function presupuestoDocNormalizarPlanVenta(plan) {
	plan = String(plan || "").toLowerCase();
	return (plan == "prioritario" || plan == "provisorio" || plan == "urgente") ? "prioritario" : "total";
}

function presupuestoDocEnVistaPlanes() {
	return pasoVistaPresupuestoDoc == 4;
}

function presupuestoDocObtenerProfesionalNombre() {
	let nombre = "";
	let tratamiento = "";
	if (typeof datosPerfilUsuarioActual != "undefined" && datosPerfilUsuarioActual) {
		nombre = datosPerfilUsuarioActual.nombre_persona || datosPerfilUsuarioActual.nombre || "";
		tratamiento = datosPerfilUsuarioActual.tratamiento || datosPerfilUsuarioActual.titulo || datosPerfilUsuarioActual.tipo_profesional || "";
	}
	if (nombre == "" && typeof userid != "undefined") {
		nombre = localStorage.getItem("nombreUsuario" + userid) || "";
	}
	tratamiento = String(tratamiento || "").trim();
	if (/^(dr|dra)\.?$/i.test(tratamiento) && nombre != "" && !/^(dr|dra)\.?\s/i.test(nombre)) {
		nombre = tratamiento.replace(/\.$/, "") + ". " + nombre;
	}
	return nombre || "Usuario autenticado";
}

function presupuestoDocActualizarProfesionalPlan() {
	const profesional = document.getElementById("presupuestoDocProfesionalNombre");
	if (profesional) {
		profesional.textContent = presupuestoDocObtenerProfesionalNombre();
	}
}

function presupuestoDocEstadoTexto(estado) {
	if (estado == "guardado") {
		return "Guardado";
	}
	if (estado == "pendiente") {
		return "Cambios sin guardar";
	}
	return "Sin cambios";
}

function presupuestoDocActualizarAccionesPlanes() {
	const estado = document.getElementById("presupuestoDocPlanesEstado");
	const btnGuardar = document.getElementById("btnPresupuestoDocGuardarPlanes");

	if (estado) {
		estado.textContent = presupuestoDocEstadoTexto(presupuestoDocEstadoPlanes);
		estado.classList.toggle("is-ok", presupuestoDocEstadoPlanes == "guardado");
		estado.classList.toggle("is-pending", presupuestoDocEstadoPlanes == "pendiente");
	}
	if (btnGuardar) {
		btnGuardar.textContent = presupuestoDocEstadoPlanes == "sin_cambios" ? "Guardar division de planes" : "Guardar cambios";
	}
}

function presupuestoDocMarcarPlanesPendientes() {
	presupuestoDocPlanesGuardados = false;
	presupuestoDocEstadoPlanes = "pendiente";
	presupuestoDocActualizarAccionesPlanes();
}

function presupuestoDocMarcarPlanesSinCambios() {
	presupuestoDocPlanesGuardados = false;
	presupuestoDocEstadoPlanes = "sin_cambios";
	presupuestoDocActualizarAccionesPlanes();
}

function presupuestoDocMarcarPlanesGuardados() {
	presupuestoDocPlanesGuardados = true;
	presupuestoDocEstadoPlanes = "guardado";
	presupuestoDocActualizarAccionesPlanes();
}

function presupuestoDocObtenerSeleccionProvisorio() {
	const origen = document.getElementById("table_vista_producto_presupuestoDetalle_doctor");
	const seleccion = {};
	if (!origen) {
		return seleccion;
	}
	Array.from(origen.children).forEach(function (tabla) {
		if (!tabla.matches("table")) {
			return;
		}
		const idDetalle = obtenerIdDetallePresupuestoDoc(tabla);
		if (!idDetalle) {
			return;
		}
		seleccion[idDetalle] = presupuestoDocTablaEsPrioritaria(tabla);
	});
	return seleccion;
}

function presupuestoDocGuardarSeleccionInicial() {
	presupuestoDocSeleccionProvisorioInicial = presupuestoDocObtenerSeleccionProvisorio();
}

function presupuestoDocTieneCambiosSinGuardar() {
	if (presupuestoDocEstadoPlanes == "pendiente") {
		return true;
	}
	const actual = presupuestoDocObtenerSeleccionProvisorio();
	const claves = {};
	Object.keys(actual).forEach(function (clave) { claves[clave] = true; });
	Object.keys(presupuestoDocSeleccionProvisorioInicial || {}).forEach(function (clave) { claves[clave] = true; });
	return Object.keys(claves).some(function (clave) {
		return !!actual[clave] !== !!presupuestoDocSeleccionProvisorioInicial[clave];
	});
}

function presupuestoDocActualizarSeleccionProvisorioLocal(idDetalle, incluir) {
	if (!idDetalle) {
		return false;
	}
	actualizarCamposPlanDetallePresupuestoDoc(idDetalle, incluir ? 1 : 0, 0);
	renderizarPlanesDetallePresupuestoDoc();
	presupuestoDocSincronizarPlanVenta();
	presupuestoDocMarcarPlanesPendientes();
	return true;
}

function presupuestoDocToggleProvisorio(idDetalle, incluir) {
	return presupuestoDocActualizarSeleccionProvisorioLocal(idDetalle, incluir);
}

function presupuestoDocQuitarProvisorio(idDetalle) {
	return presupuestoDocActualizarSeleccionProvisorioLocal(idDetalle, false);
}

function presupuestoDocVolverPaso3() {
	if (!presupuestoDocTieneCambiosSinGuardar()) {
		return verPasoPresupuestoDoc(3);
	}
	const btnAceptar = document.getElementById("btnConfirmDialogGenericoAceptar");
	const btnCancelar = document.getElementById("btnConfirmDialogGenericoCancelar");
	if (typeof ver_ventana_confirmacion == "function" && btnAceptar && btnCancelar) {
		const textoAceptar = btnAceptar.textContent;
		const textoCancelar = btnCancelar.textContent;
		btnAceptar.textContent = "Volver sin guardar";
		btnCancelar.textContent = "Cancelar";
		ver_ventana_confirmacion("Hay cambios sin guardar. Desea volver al paso 3 sin guardarlos?", "Cambios sin guardar").then(function (confirmado) {
			btnAceptar.textContent = textoAceptar;
			btnCancelar.textContent = textoCancelar;
			if (confirmado) {
				verPasoPresupuestoDoc(3);
			}
		});
		return false;
	}
	if (confirm("Hay cambios sin guardar. Desea volver al paso 3 sin guardarlos?")) {
		return verPasoPresupuestoDoc(3);
	}
	return false;
}

function presupuestoDocTieneItemsPlan(plan) {
	const planNormalizado = presupuestoDocNormalizarPlanVenta(plan);
	const contenedorId = planNormalizado == "prioritario"
		? "table_vista_producto_presupuestoDetalle_prioritario"
		: "table_vista_producto_presupuestoDetalle";
	const contenedor = document.getElementById(contenedorId);
	return !!(contenedor && contenedor.querySelector("tr[name=tdDetallePresupuesto]"));
}

function presupuestoDocMostrarConfirmacionGuardado() {
	const mensaje = "<strong>Division de planes guardada</strong><br>El plan total y el plan provisorio quedaron guardados correctamente en el historial del presupuesto.";
	const btnAceptar = document.getElementById("btnConfirmDialogGenericoAceptar");
	const btnCancelar = document.getElementById("btnConfirmDialogGenericoCancelar");
	if (typeof ver_ventana_confirmacion != "function" || !btnAceptar || !btnCancelar) {
		ver_vetana_informativa("Division de planes guardada", "El plan total y el plan provisorio quedaron guardados correctamente.", "info");
		return;
	}
	const textoAceptar = btnAceptar.textContent;
	const textoCancelar = btnCancelar.textContent;
	const claseAceptar = btnAceptar.className;
	const claseCancelar = btnCancelar.className;
	btnAceptar.textContent = "Cerrar";
	btnCancelar.textContent = "Volver al paso 3";
	btnAceptar.className = "btn btn-primary";
	btnCancelar.className = "btn btn-outline-secondary";
	ver_ventana_confirmacion(mensaje, "Division de planes guardada").then(function (cerrarConfirmacion) {
		btnAceptar.textContent = textoAceptar;
		btnCancelar.textContent = textoCancelar;
		btnAceptar.className = claseAceptar;
		btnCancelar.className = claseCancelar;
		if (!cerrarConfirmacion) {
			verPasoPresupuestoDoc(3);
		}
	});
}

function presupuestoDocMostrarPlanes() {
	if (!validarPresupuestoDoctorListo()) {
		return false;
	}
	const layout = document.getElementById("presupuestoDocLayout");
	const modalPlanes = document.getElementById("presupuestoDocPlanesModal");
	const paso2Header = document.getElementById("presupuestoDocPrioritarioHeader");
	const panelDetalle = document.getElementById("presupuestoDocDetallePanel");
	if (!layout || !modalPlanes || !paso2Header || !panelDetalle) {
		return false;
	}
	presupuestoDocCargarPlanTotalEnProvisorioSiEstaVacio();
	renderizarPlanesDetallePresupuestoDoc();
	if (!presupuestoDocSincronizarPlanVenta()) {
		ver_vetana_informativa("Error al guardar", "No se pudo preparar la division de planes.", "error");
		return false;
	}
	presupuestoDocMoverModalPlanesAlBody(modalPlanes);
	pasoVistaPresupuestoDoc = 4;
	layout.style.display = "grid";
	presupuestoDocAbrirModalPlanes(modalPlanes, paso2Header, panelDetalle);
	presupuestoDocEnfocarModalPlanes(modalPlanes, panelDetalle);
	presupuestoDocGuardarSeleccionInicial();
	presupuestoDocMarcarPlanesSinCambios();
	presupuestoDocActualizarProfesionalPlan();
	actualizarResumenPacientePresupuestoDoc();
	return true;
}

function presupuestoDocObtenerTablasOrigenPlan() {
	const origen = document.getElementById("table_vista_producto_presupuestoDetalle_doctor");
	if (!origen) {
		return [];
	}
	return Array.from(origen.children).filter(function (tabla) {
		return tabla.matches("table") && obtenerIdDetallePresupuestoDoc(tabla);
	});
}

function presupuestoDocPersistirDetallePlan(tabla) {
	const idDetalle = obtenerIdDetallePresupuestoDoc(tabla);
	const esPrioritario = presupuestoDocTablaEsPrioritaria(tabla) ? 1 : 0;
	obtener_datos_user();
	return new Promise(function (resolve, reject) {
		$.ajax({
			data: {
				"useru": userid,
				"passu": passuser,
				"navegador": navegador,
				"accion": "abmDetallesPresupuesto",
				"id": idDetalle,
				"cod_presupuestoFK": idabmPresupuesto,
				"cod_clienteFK": idFkCliente,
				"es_prioritario": esPrioritario,
				"es_alternativo": 0
			},
			url: "/GoodVentaAsisCap/php_system/abmPresupuesto.php",
			type: "post",
			error: function (jqXHR, textstatus, errorThrowm) {
				reject({
					jqXHR: jqXHR,
					textstatus: textstatus,
					errorThrowm: errorThrowm
				});
			},
			success: function (responseText) {
				try {
					const respuesta = parsearRespuestaAjaxPresupuesto(responseText);
					if (respuestaJqueryAjax(respuesta["1"]) == true) {
						resolve(respuesta);
					} else {
						reject({
							mensaje: respuesta["mensaje"] || "El servidor no pudo guardar la division de planes."
						});
					}
				} catch (error) {
					reject({
						error: error,
						respuesta: responseText
					});
				}
			}
		});
	});
}

function presupuestoDocGuardarPlanes() {
	if (!validarPresupuestoDoctorListo()) {
		return false;
	}
	renderizarPlanesDetallePresupuestoDoc();
	if (!presupuestoDocSincronizarPlanVenta()) {
		ver_vetana_informativa("Error al guardar", "No se pudo sincronizar el plan total y el plan provisorio.", "error");
		return false;
	}
	const detalles = presupuestoDocObtenerTablasOrigenPlan();
	if (detalles.length == 0) {
		ver_vetana_informativa("Sin tratamientos", "No hay tratamientos definidos para el plan total.", "advertencia");
		return false;
	}
	verCerrarEfectoCargando("1");
	Promise.all(detalles.map(presupuestoDocPersistirDetallePlan))
		.then(function () {
			verCerrarEfectoCargando("");
			presupuestoDocGuardarSeleccionInicial();
			presupuestoDocMarcarPlanesGuardados();
			renderizarPlanesDetallePresupuestoDoc();
			presupuestoDocSincronizarPlanVenta();
			presupuestoDocMostrarConfirmacionGuardado();
		})
		.catch(function (error) {
			verCerrarEfectoCargando("");
			if (error && error.jqXHR) {
				manejadordeerroresjquery(error.jqXHR.status, error.textstatus, "abmventana");
				console.error(error.jqXHR.status, error.textstatus, error.errorThrowm);
			} else {
				console.error(error);
			}
			presupuestoDocMarcarPlanesPendientes();
			ver_vetana_informativa("Error al guardar", "No se pudo guardar la division de planes. Revise la conexion e intente nuevamente.", "error");
		});
	return false;
}

function presupuestoDocContinuarVentaSeleccionada() {
	const selectPlan = document.getElementById("inptPlanVentaPresupuestoDoc");
	const planSeleccionado = selectPlan ? selectPlan.value : "total";
	return presupuestoDocContinuarNuevaVenta(planSeleccionado);
}

function presupuestoDocConcretarPlan(plan) {
	const planNormalizado = presupuestoDocNormalizarPlanVenta(plan);
	if (!presupuestoDocPlanesGuardados) {
		ver_vetana_informativa("Guarda los planes", "Primero guarda el plan total y el plan provisorio.", "advertencia");
		return false;
	}
	if (!presupuestoDocTieneItemsPlan(planNormalizado)) {
		ver_vetana_informativa("Plan vacio", planNormalizado == "prioritario" ? "El plan provisorio no tiene tratamientos." : "El plan total no tiene tratamientos.", "advertencia");
		return false;
	}
	return presupuestoDocContinuarNuevaVenta(planNormalizado);
}

function iniciarArrastreTactilPresupuestoDoc(evento, tabla) {
	if (evento.pointerType === "mouse") {
		return;
	}
	const idDetalle = obtenerIdDetallePresupuestoDoc(tabla);
	if (!idDetalle) {
		return;
	}

	evento.preventDefault();
	const fantasma = tabla.cloneNode(true);
	fantasma.style.position = "fixed";
	fantasma.style.left = "0";
	fantasma.style.top = "0";
	fantasma.style.width = tabla.getBoundingClientRect().width + "px";
	fantasma.style.pointerEvents = "none";
	fantasma.style.opacity = "0.86";
	fantasma.style.zIndex = "99999";
	fantasma.style.transform = "translate(" + evento.clientX + "px, " + evento.clientY + "px)";
	document.body.appendChild(fantasma);

	function mover(eventoMover) {
		fantasma.style.transform = "translate(" + eventoMover.clientX + "px, " + eventoMover.clientY + "px)";
		document.querySelectorAll(".presupuesto-doc-dropzone").forEach(function (zona) {
			zona.classList.remove("presupuesto-doc-dropzone-activa");
		});
		const elemento = document.elementFromPoint(eventoMover.clientX, eventoMover.clientY);
		const zona = elemento ? elemento.closest(".presupuesto-doc-dropzone") : null;
		if (zona) {
			zona.classList.add("presupuesto-doc-dropzone-activa");
		}
	}

	function terminar(eventoTerminar) {
		const elemento = document.elementFromPoint(eventoTerminar.clientX, eventoTerminar.clientY);
		const zona = elemento ? elemento.closest(".presupuesto-doc-dropzone") : null;
		if (zona) {
			agregarDetalleAPlanPresupuestoDoc(idDetalle, zona.dataset.plan);
		}
		document.querySelectorAll(".presupuesto-doc-dropzone").forEach(function (zonaDrop) {
			zonaDrop.classList.remove("presupuesto-doc-dropzone-activa");
		});
		fantasma.remove();
		window.removeEventListener("pointermove", mover);
		window.removeEventListener("pointerup", terminar);
		window.removeEventListener("pointercancel", terminar);
	}

	window.addEventListener("pointermove", mover);
	window.addEventListener("pointerup", terminar);
	window.addEventListener("pointercancel", terminar);
}

function actualizarCamposPlanDetallePresupuestoDoc(idDetalle, esPrioritario, esAlternativo) {
	[
		"table_vista_producto_presupuestoDetalle_doctor",
		"table_vista_producto_presupuestoDetalle_plan_a_doctor",
		"table_vista_producto_presupuestoDetalle_prioritario_doctor",
		"table_vista_producto_presupuestoDetalle",
		"table_vista_producto_presupuestoDetalle_prioritario"
	].forEach(function (contenedorId) {
		const tabla = obtenerTablaDetallePresupuestoDoc(idDetalle, contenedorId);
		if (!tabla) {
			return;
		}
		asegurarCampoAlternativoPresupuestoDoc(tabla);
		const campoPrioritario = tabla.querySelector('#td_datos_12');
		const campoAlternativo = tabla.querySelector('#td_datos_13');
		if (campoPrioritario) {
			campoPrioritario.textContent = esPrioritario ? "1" : "0";
		}
		if (campoAlternativo) {
			campoAlternativo.textContent = esAlternativo ? "1" : "0";
		}
	});
}

function verPasoPresupuestoDoc(paso) {
	const layout = document.getElementById("presupuestoDocLayout");
	const modalPlanes = document.getElementById("presupuestoDocPlanesModal");
	const paso2Header = document.getElementById("presupuestoDocPrioritarioHeader");
	const panelDetalle = document.getElementById("presupuestoDocDetallePanel");

	if (!layout || !modalPlanes || !paso2Header || !panelDetalle) {
		return;
	}

	paso = Number(paso) || 1;
	if (paso < 1) {
		paso = 1;
	}
	if (paso > 3) {
		paso = 3;
	}
	if (paso > 1 && !presupuestoDocPacienteListoParaAvanzar()) {
		ver_vetana_informativa("Faltan datos", "Selecciona el paciente antes de continuar.", "error");
		paso = 1;
	}
	pasoVistaPresupuestoDoc = paso;
	layout.style.display = "grid";
	presupuestoDocCerrarModalPlanes();
	if (typeof odontogramaEstados != "undefined" && odontogramaEstados.presupuesto) {
		odontogramaEstados.presupuesto.pasoClinico = paso == 3 ? "tratamientos" : "situacion";
		odontogramaEstados.presupuesto.modo = paso == 3 ? "asignar" : "hallazgo";
		odontogramaEstados.presupuesto.filtroVisual = paso == 3 ? "tratamientos" : "situacion";
		odontogramaRender("presupuesto");
	}
	aplicarEstadoPrioritarioDetalleDoc();
	actualizarResumenPacientePresupuestoDoc();
	return true;
}

function sincronizarResumenDetallePresupuestoDoc() {
	const origen = document.getElementById("table_vista_producto_presupuestoDetalle_doctor");
	const destino = document.getElementById("table_vista_producto_presupuestoDetalle_doctor_resumen");

	if (!origen || !destino) {
		return;
	}

	destino.innerHTML = "";

	Array.from(origen.children).forEach(function (tablaOriginal) {
		const idDetalle = obtenerIdDetallePresupuestoDoc(tablaOriginal);
		const tablaClonada = tablaOriginal.cloneNode(true);
		tablaClonada.dataset.idDetallePresupuesto = idDetalle;
		tablaClonada.removeAttribute("id");
		tablaClonada.querySelectorAll("#td_datos_4, #td_datos_5").forEach(function (celda) {
			celda.style.display = "";
		});
		tablaClonada.querySelectorAll("[id]").forEach(function (elemento) {
			elemento.removeAttribute("id");
		});
		destino.appendChild(tablaClonada);
	});

	aplicarEstadoPrioritarioDetalleDoc();
	actualizarResumenPacientePresupuestoDoc();
	presupuestoDocActualizarEstado();
}

function actualizarPlanesDetallePresupuestoDoc(idDetalle, enPlanA, enPlanB) {
	const esPrioritario = enPlanB ? 1 : 0;
	const esAlternativo = enPlanA ? 0 : 1;
	obtener_datos_user();
	verCerrarEfectoCargando("1");
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"accion": "abmDetallesPresupuesto",
		"id": idDetalle,
		"cod_presupuestoFK": idabmPresupuesto,
		"es_prioritario": esPrioritario,
		"es_alternativo": esAlternativo
	};

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPresupuesto.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
			console.error(jqXHR.status, textstatus, errorThrowm);
			ver_vetana_informativa("Lo sentimos, ha ocurrido un error", "", "error");
			verCerrarEfectoCargando("");
		},
		success: function (responseText) {
			try {
				var respuesta = parsearRespuestaAjaxPresupuesto(responseText);
				var operacionOk = respuestaJqueryAjax(respuesta["1"]);
				if (operacionOk == true) {
					actualizarCamposPlanDetallePresupuestoDoc(idDetalle, esPrioritario, esAlternativo);
					renderizarPlanesDetallePresupuestoDoc();
					sincronizarResumenDetallePresupuestoDoc();
					presupuestoDocMarcarPlanesPendientes();
				}
			} catch (error) {
				presupuestoDocDropDestinoPlan = "";
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ", responseText, "error");
				var titulo="Error: "+error+" \r\n Consola: "+responseText;
				GuardarArchivosLog(titulo);
			} finally {
				verCerrarEfectoCargando("");
			}
		}
	});
}

function agregarDetalleAPlanPresupuestoDoc(idDetalle, plan) {
	const tablaOrigen = obtenerTablaDetallePresupuestoDoc(idDetalle, "table_vista_producto_presupuestoDetalle_doctor");
	const detalle = obtenerDatosDetallePresupuestoDoc(tablaOrigen);
	if (!detalle || !detalle.cod_producto) {
		return;
	}
	presupuestoDocDropDestinoPlan = plan;
	const esPrioritario = plan === "b";
	const esAlternativo = plan === "b";
	abmDetallesPresupuesto(
		idabmPresupuesto,
		detalle.cod_producto,
		detalle.precio,
		detalle.cantidad,
		detalle.codigo,
		detalle.nombre,
		detalle.total,
		detalle.precio_contado,
		esPrioritario,
		esAlternativo,
		detalle.justificacion
	);
}

function quitarDetalleDePlanPresupuestoDoc(idDetalle, plan) {
	let enPlanA = !!obtenerTablaDetallePresupuestoDoc(idDetalle, "table_vista_producto_presupuestoDetalle_plan_a_doctor");
	let enPlanB = !!obtenerTablaDetallePresupuestoDoc(idDetalle, "table_vista_producto_presupuestoDetalle_prioritario_doctor");

	if (plan === "a") {
		enPlanA = false;
	}
	if (plan === "b") {
		enPlanB = false;
	}

	actualizarPlanesDetallePresupuestoDoc(idDetalle, enPlanA, enPlanB);
}

function eliminarDetallePlanPresupuestoDoc(idDetalle, tablaDetalle, pedirConfirmacion) {
	if (!idDetalle || (pedirConfirmacion !== false && !confirm("¿Seguro que deseas eliminar este tratamiento del plan?"))) {
		return;
	}
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idDetalle": idDetalle,
		"solo_eliminar_prioritario": false,
		"cod_presupuestoFK": idabmPresupuesto,
		"accion": "eliminarDetallePresupuesto"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPresupuesto.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
			console.error(jqXHR.status,textstatus,errorThrowm);
			ver_vetana_informativa("Lo sentimos, ha ocurrido un error", "", "error");
		},
		success: function (responseText) {
			try {
				var datos = parsearRespuestaAjaxPresupuesto(responseText);
				var respuesta = respuestaJqueryAjax(datos["1"]);
				if (respuesta == true) {
					removerDetallePresupuestoDeVista(idDetalle);
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ", responseText, "error");
				var titulo="Error: "+error+" \r\n Consola: "+responseText;
				GuardarArchivosLog(titulo);
			}
		}
	});
}

function quitarDetalleSoloDePlanPresupuesto(idDetalle, plan, esDoctor) {
	const contenedorPlanA = esDoctor ? "table_vista_producto_presupuestoDetalle_plan_a_doctor" : "table_vista_producto_presupuestoDetalle";
	const contenedorPlanB = esDoctor ? "table_vista_producto_presupuestoDetalle_prioritario_doctor" : "table_vista_producto_presupuestoDetalle_prioritario";
	let enPlanA = !!obtenerTablaDetallePresupuestoDoc(idDetalle, contenedorPlanA);
	let enPlanB = !!obtenerTablaDetallePresupuestoDoc(idDetalle, contenedorPlanB);

	if (plan === "a") {
		enPlanA = false;
	}
	if (plan === "b") {
		enPlanB = false;
	}

	const esPrioritario = enPlanB ? 1 : 0;
	const esAlternativo = enPlanA ? 0 : 1;
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"accion": "abmDetallesPresupuesto",
		"id": idDetalle,
		"cod_presupuestoFK": idabmPresupuesto,
		"es_prioritario": esPrioritario,
		"es_alternativo": esAlternativo
	};

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPresupuesto.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
			console.error(jqXHR.status, textstatus, errorThrowm);
			ver_vetana_informativa("Lo sentimos, ha ocurrido un error", "", "error");
		},
		success: function (responseText) {
			try {
				var respuesta = parsearRespuestaAjaxPresupuesto(responseText);
				var operacionOk = respuestaJqueryAjax(respuesta["1"]);
				if (operacionOk == true) {
					const tablaPlan = obtenerTablaDetallePresupuestoDoc(idDetalle, plan === "b" ? contenedorPlanB : contenedorPlanA);
					if (tablaPlan) {
						tablaPlan.remove();
					}
					actualizarCamposPlanDetallePresupuestoDoc(idDetalle, esPrioritario, esAlternativo);
					if (esDoctor) {
						sincronizarResumenDetallePresupuestoDoc();
						presupuestoDocMarcarPlanesPendientes();
					} else {
						recalcularTotalPresupuesto();
					}
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ", responseText, "error");
				var titulo="Error: "+error+" \r\n Consola: "+responseText;
				GuardarArchivosLog(titulo);
			}
		}
	});
}

function removerDetallePresupuestoDeVista(idDetalle) {
	document.querySelectorAll("table").forEach(function (tabla) {
		if (String(obtenerIdDetallePresupuestoDoc(tabla)) == String(idDetalle)) {
			tabla.remove();
		}
	});

	if (vistaPresupuestoOrigen == "doctor") {
		sincronizarResumenDetallePresupuestoDoc();
		presupuestoDocMarcarPlanesPendientes();
	} else {
		recalcularTotalPresupuesto();
	}
}

function eliminarDetallePresupuestoPorId(idDetalle) {
	if (!idDetalle) {
		return false;
	}

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idDetalle": idDetalle,
		"solo_eliminar_prioritario": false,
		"cod_presupuestoFK": idabmPresupuesto,
		"accion": "eliminarDetallePresupuesto"
	};

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPresupuesto.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
			console.error(jqXHR.status,textstatus,errorThrowm);
			ver_vetana_informativa("Lo sentimos, ha ocurrido un error", "", "error");
		},
		success: function (responseText) {
			try {
				var datos = parsearRespuestaAjaxPresupuesto(responseText);
				var respuesta = respuestaJqueryAjax(datos["1"]);
				if (respuesta == true) {
					removerDetallePresupuestoDeVista(idDetalle);
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ", responseText, "error");
				var titulo="Error: "+error+" \r\n Consola: "+responseText;
				GuardarArchivosLog(titulo);
			}
		}
	});
	return false;
}



function eliminarFila(btn, omitirConfirmacion) {
	omitirConfirmacion = omitirConfirmacion === true || !!btn?.classList?.contains("presupuesto-doc-trash-btn");
	const filaDoctor = btn.closest("tr");
	const tablaDetalleDoctor = filaDoctor?.parentElement?.parentElement;
	const contenedorDoctor = tablaDetalleDoctor?.parentElement;

	if (
		vistaPresupuestoOrigen == "doctor" &&
		contenedorDoctor?.id === "table_vista_producto_presupuestoDetalle_doctor_resumen"
	) {
		const idDetalleDoctor = obtenerIdDetallePresupuestoDoc(tablaDetalleDoctor);
		const tablaOriginal = obtenerTablaDetallePresupuestoDoc(idDetalleDoctor, "table_vista_producto_presupuestoDetalle_doctor");
		const filaOriginal = tablaOriginal?.querySelector("tr[name=tdDetallePresupuesto]");
		if (filaOriginal) {
			eliminarFila(filaOriginal, omitirConfirmacion);
		}
		return;
	}

	if (vistaPresupuestoOrigen == "doctor" && presupuestoDocEnVistaPlanes()) {
		const idDetalleDoctor = obtenerIdDetallePresupuestoDoc(tablaDetalleDoctor);
		if (
			(contenedorDoctor?.id === "table_vista_producto_presupuestoDetalle_plan_a_doctor") ||
			(contenedorDoctor?.id === "table_vista_producto_presupuestoDetalle_prioritario_doctor")
		) {
			if (omitirConfirmacion) {
				quitarDetalleSoloDePlanPresupuesto(
					idDetalleDoctor,
					contenedorDoctor.id === "table_vista_producto_presupuestoDetalle_prioritario_doctor" ? "b" : "a",
					true
				);
				return;
			}
			eliminarDetallePlanPresupuestoDoc(idDetalleDoctor, tablaDetalleDoctor, !omitirConfirmacion);
			return;
		}
		if (contenedorDoctor?.id !== "table_vista_producto_presupuestoDetalle_doctor") {
			return;
		}
	}
  if (omitirConfirmacion || confirm("¿Seguro que deseas eliminar este producto del presupuesto?")) {
    let fila = btn.closest("tr");
	const es_prioritario= fila.querySelector('#td_datos_12')?.textContent.trim();
	let tabla= fila.parentElement.parentElement;
	const idDetalle= obtenerIdDetallePresupuestoDoc(tabla);
	tabla= tabla.parentElement;
	const esTablaPrioritaria = tabla.id.includes("prioritario");
	if (
		omitirConfirmacion &&
		(
			tabla.id === "table_vista_producto_presupuestoDetalle" ||
			tabla.id === "table_vista_producto_presupuestoDetalle_prioritario"
		)
	) {
		quitarDetalleSoloDePlanPresupuesto(idDetalle, esTablaPrioritaria ? "b" : "a", false);
		return;
	}
	
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idDetalle": idDetalle,
		"solo_eliminar_prioritario": omitirConfirmacion ? false : esTablaPrioritaria,
		"cod_presupuestoFK": idabmPresupuesto, 
		"accion": "eliminarDetallePresupuesto"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPresupuesto.php",
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
			verCerrarEfectoCargando("2")
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			verCerrarEfectoCargando("2")
			try {
				var datos = parsearRespuestaAjaxPresupuesto(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					if (omitirConfirmacion) {
						removerDetallePresupuestoDeVista(idDetalle);
						return;
					}
					const contenedorPrincipal = vistaPresupuestoOrigen == "doctor"
						? "#table_vista_producto_presupuestoDetalle_doctor"
						: "#table_vista_producto_presupuestoDetalle";
					const contenedorPrioritario = vistaPresupuestoOrigen == "doctor"
						? "#table_vista_producto_presupuestoDetalle_prioritario_doctor"
						: "#table_vista_producto_presupuestoDetalle_prioritario";

					const tablaPrincipal = document.querySelector(
						`${contenedorPrincipal} #tdDetalleVenta_${idDetalle}`
					);
					const tablaPrioritaria = document.querySelector(
						`${contenedorPrioritario} #tdDetalleVenta_${idDetalle}`
					);

					if (esTablaPrioritaria) {
						const tablaPlanADesdePrioritaria = tablaPrioritaria ? tablaPrioritaria.cloneNode(true) : null;
						if (tablaPrioritaria) {
							tablaPrioritaria.remove();
						}
						if (tablaPrincipal) {
							const campoPrioritario = tablaPrincipal.querySelector('#td_datos_12');
							const campoAlternativo = tablaPrincipal.querySelector('#td_datos_13');
							if (campoPrioritario) {
								campoPrioritario.textContent = "0";
							}
							if (campoAlternativo) {
								campoAlternativo.textContent = "0";
							}
						} else if (tablaPlanADesdePrioritaria) {
							const campoPrioritario = tablaPlanADesdePrioritaria.querySelector('#td_datos_12');
							const campoAlternativo = tablaPlanADesdePrioritaria.querySelector('#td_datos_13');
							if (campoPrioritario) {
								campoPrioritario.textContent = "0";
							}
							if (campoAlternativo) {
								campoAlternativo.textContent = "0";
							}
							document.querySelector(contenedorPrincipal).appendChild(tablaPlanADesdePrioritaria);
						}
					} else {
						if (tablaPrincipal) {
							tablaPrincipal.remove();
						}
						if ((es_prioritario === "1") && tablaPrioritaria) {
							tablaPrioritaria.remove();
						}
					}

					if (vistaPresupuestoOrigen == "doctor") {
						sincronizarResumenDetallePresupuestoDoc();
					} else {
						recalcularTotalPresupuesto();
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
}

function recalcularTotalPresupuesto() {
  let filas = document.querySelectorAll("#table_vista_producto_presupuestoDetalle tr");
  let filasPrioritarias = document.querySelectorAll("#table_vista_producto_presupuestoDetalle_prioritario tr");
  let total = 0;
  let total_prioritario = 0;

  filas.forEach(fila => {
    let valor = fila.cells[4]?.innerText.replace(/\./g,"").replace(",",".") || "0";
    total += parseFloat(valor) || 0;
  });

  filasPrioritarias.forEach(fila => {
    let valor = fila.cells[4]?.innerText.replace(/\./g,"").replace(",",".") || "0";
    total_prioritario += parseFloat(valor) || 0;
  });
 
document.getElementById("inptTotalPresupuesto2").innerHTML=separadordemilesnumero(total);
document.getElementById("inptTOTALPresupuestoFORM").value=separadordemilesnumero(total);
document.getElementById("inptTOTALPresupuestoFORMPrioritario").value=separadordemilesnumero(total_prioritario);
 
document.getElementById("table_vista_detalles_presupuesto").innerHTML=""
document.getElementById("table_vista_detalles_presupuesto_prioritario").innerHTML=""
 generarTabla()
 
}


 
function generarTabla() {
	let total = document.getElementById("inptTOTALPresupuestoFORM").value || 0;
	let total_prioritario = document.getElementById("inptTOTALPresupuestoFORMPrioritario").value || 0;

	total = total != 0 ? total.replace(/\./g, '') : 0;
	total_prioritario = total_prioritario != 0 ? total_prioritario.replace(/\./g, '') : 0;

 if(total==0){
	 return false;
 }
  const divCuerpo = document.getElementById("table_vista_detalles_presupuesto"); 
 
  let html = " <table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >"
    +"<tr>"
    +"  <td style='width:25%'>CONTADO</td>"
    +"  <td style='width:25%'>"+ separadordemilesnumero(total) +"</td>"
    +"  <td style='width:25%'>"+ 1 +" x "+ separadordemilesnumero(total)+"</td>"
    +"  <td style='width:25%'>"+separadordemilesnumero(total)+"</td>"
    +"</tr>"
	+"</table>";

  // Planes en cuotas  
  var cuotasList = [2,3,4,5,6,8,10,12,15,18];

  cuotasList.forEach(cuotas => {
    // let valorCuota = Math.round(total / cuotas); // redondeo al entero más cercano
	let valorCuota = Math.round((total / cuotas) / 1000) * 1000;
	
    html += "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >"
     +" <tr>"
      +"  <td style='width:25%'>"+cuotas+"</td>"
      +"  <td style='width:25%'>"+separadordemilesnumero(valorCuota)+"</td>"
      +"  <td style='width:25%'>"+ cuotas +" x "+ separadordemilesnumero(valorCuota)+"</td>"
      +"  <td style='width:25%'>"+separadordemilesnumero(total)+"</td>"
     +" </tr>"
	+"</table>";
  });
 
  divCuerpo.innerHTML = html;

  // Ahora con los datos del plan prioritario
  const divCuerpoPrioritario = document.getElementById("table_vista_detalles_presupuesto_prioritario"); 
  html = " <table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >"
    +"<tr>"
    +"  <td style='width:25%'>CONTADO</td>"
    +"  <td style='width:25%'>"+ separadordemilesnumero(total_prioritario) +"</td>"
    +"  <td style='width:25%'>"+ 1 +" x "+ separadordemilesnumero(total_prioritario)+"</td>"
    +"  <td style='width:25%'>"+separadordemilesnumero(total_prioritario)+"</td>"
    +"</tr>"
	+"</table>";

	cuotasList.forEach(cuotas => {
		// let valorCuota = Math.round(total / cuotas); // redondeo al entero más cercano
		let valorCuota = Math.round((total_prioritario / cuotas) / 1000) * 1000;
		
		html += "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >"
		+" <tr>"
		+"  <td style='width:25%'>"+cuotas+"</td>"
		+"  <td style='width:25%'>"+separadordemilesnumero(valorCuota)+"</td>"
		+"  <td style='width:25%'>"+ cuotas +" x "+ separadordemilesnumero(valorCuota)+"</td>"
		+"  <td style='width:25%'>"+separadordemilesnumero(total_prioritario)+"</td>"
		+" </tr>"
		+"</table>";
	});
	
	divCuerpoPrioritario.innerHTML = html;
	mejorarCuotasPresupuestoVisual();
	renderizarJustificacionesPresupuesto();
	actualizarResumenPresupuestoVenta();
}
 
function abmDetallesPresupuesto(cod_presupuestoFK, cod_productoFK, precio, cantidad, codigo_ficticio_presupuesto, nombre_producto, total_presupuesto,es_precio_contado, es_prioritario, es_alternativo, justificacion_presupuesto) {
	obtener_datos_user();
	justificacion_presupuesto = justificacion_presupuesto || justificacionProductoPresupuestoSeleccionado || "";
	const esPrioritario = es_prioritario === true || es_prioritario === 1 || es_prioritario === "1";
	const esAlternativo = es_alternativo === true || es_alternativo === 1 || es_alternativo === "1";
	precio = QuitarSeparadorMilValor(precio || 0);
	cantidad = QuitarSeparadorMilValor(cantidad || 0);
	if (!total_presupuesto || total_presupuesto == "0") {
		total_presupuesto = Number(precio) * Number(cantidad);
	}
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"accion": "abmDetallesPresupuesto",
		"cod_presupuestoFK": cod_presupuestoFK,
		"cod_clienteFK": idFkCliente,
		"cod_productoFK": cod_productoFK,
		"cantidad": cantidad,
		"precio": precio,
		"es_prioritario": (esPrioritario ? 1 : 0),
		"es_alternativo": (esAlternativo ? 1 : 0),
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPresupuesto.php",
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
			presupuestoDocDropDestinoPlan = "";
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			console.error(jqXHR.status,textstatus,errorThrowm);
			ver_vetana_informativa("Lo sentimos, ha ocurrido un error", "", "error");
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = parsearRespuestaAjaxPresupuesto(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var pagina = "<table id='tdDetalleVenta_" + datos[2] + "' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>"
						+ "<tr id='tbSelecRegistro' onclick='eliminarFila(this)'  name='tdDetallePresupuesto'>"
						+ "<td  id='td_datos_1' style='width:10%;'>" + codigo_ficticio_presupuesto + "</td>"
						+ "<td  id='td_datos_2' >" + nombre_producto + "</td>"
						+ "<td  id='td_datos_3' class='presupuesto-doc-cantidad-acciones' style='width:10%;'><button type='button' class='btn-eliminar presupuesto-doc-trash-btn' title='Eliminar tratamiento' onclick='event.stopPropagation(); eliminarFila(this); return false;'><i class='fa-solid fa-trash-can'></i></button><span>" + cantidad + "</span></td>"
						+ "<td  id='td_datos_4' style='width:15%;"+ (vistaPresupuestoOrigen == 'doctor' ? 'display: none;' : '') +"'>" + separadordemilesnumero(precio) + "</td>"
						+ "<td  id='td_datos_5' style='width:15%;"+ (vistaPresupuestoOrigen == 'doctor' ? 'display: none;' : '') +"'>" + total_presupuesto + "</td>"
						+ "<td  id='td_datos_6' style='display:none'></td>"
						+ "<td  id='td_datos_7' style='display:none'>" + 0 + "</td>"
						+ "<td  id='td_datos_8' style='display:none'>" + 0 + "</td>"
						+ "<td  id='td_datos_9' style='display:none'>" + es_precio_contado + "</td>"
						+ "<td id='td_datos_10' style='display:none'>" + precio + "</td>"
						+ "<td  id='td_datos_11' style='display:none'>" + total_presupuesto + "</td>"
						+ "<td  id='td_datos_12' style='display:none'>" + (esPrioritario ? 1 : 0) + "</td>"
						+ "<td  id='td_datos_13' style='display:none'>" + (esAlternativo ? 1 : 0) + "</td>"
						+ "<td  id='td_datos_14' style='display:none'>" + cod_productoFK + "</td>"
						+ "<td  id='td_datos_15' style='display:none'>" + datos[3] + "</td>"
						+ "<td  id='td_datos_16' style='display:none'>" + escaparHtmlPresupuesto(justificacion_presupuesto) + "</td>"
						+ "</tr>"
						+ "</table>"

					if (vistaPresupuestoOrigen == "doctor") {
						const destinoDrop = presupuestoDocDropDestinoPlan == "b"
							? document.getElementById("table_vista_producto_presupuestoDetalle_prioritario_doctor")
							: presupuestoDocDropDestinoPlan == "a"
								? document.getElementById("table_vista_producto_presupuestoDetalle_plan_a_doctor")
								: null;

						if (destinoDrop) {
							destinoDrop.innerHTML += pagina;
							presupuestoDocDropDestinoPlan = "";
							aplicarEstadoPrioritarioDetalleDoc();
						} else {
							document.getElementById("table_vista_producto_presupuestoDetalle_doctor").innerHTML += pagina;
							aplicarEstadoPrioritarioDetalleDoc();
							sincronizarResumenDetallePresupuestoDoc();
							if (typeof odontogramaVincularDetallePresupuestoAgregado == "function") {
								odontogramaVincularDetallePresupuestoAgregado(datos[2], cod_productoFK, nombre_producto);
							}
						}

						$("#table_vista_producto_presupuestoDetalle_doctor tr[name=tdDetallePresupuesto]").each(function (i, elementohtml) {
							var total = $(elementohtml).children('td[id="td_datos_11"]').html();
							total = QuitarSeparadorMilValor(total)
							totalPresupuesto = Number(totalPresupuesto) + Number(total)
						});
					} else {
						if (!esAlternativo) {
							document.getElementById("table_vista_producto_presupuestoDetalle").innerHTML += pagina;
						}
						if (esPrioritario) {
							document.getElementById("table_vista_producto_presupuestoDetalle_prioritario").innerHTML += pagina;
						}

						totalPresupuesto = 0;
						totalPresupuestoPrioritario = 0;
						var totalEntrega = document.getElementById('inptTotalPresupuesto').value;
	
						$("#table_vista_producto_presupuestoDetalle tr[name=tdDetallePresupuesto]").each(function (i, elementohtml) {
							var total = $(elementohtml).children('td[id="td_datos_11"]').html();
							total = QuitarSeparadorMilValor(total)
							totalPresupuesto = Number(totalPresupuesto) + Number(total)
						});

						$("#table_vista_producto_presupuestoDetalle_prioritario tr[name=tdDetallePresupuesto]").each(function (i, elementohtml) {
							var total = $(elementohtml).children('td[id="td_datos_11"]').html();
							total = QuitarSeparadorMilValor(total)
							totalPresupuestoPrioritario = Number(totalPresupuestoPrioritario) + Number(total)
						});
	
						document.getElementById("inptTotalPresupuesto2").innerHTML = separadordemilesnumero(totalPresupuesto);
						document.getElementById("inptTOTALPresupuestoFORM").value = separadordemilesnumero(totalPresupuesto);
						document.getElementById("inptTOTALPresupuestoFORMPrioritario").value = separadordemilesnumero(totalPresupuestoPrioritario);

						generarTabla();
					}
					limpirarAddPresupuesto(vistaPresupuestoOrigen);
					if (vistaPresupuestoOrigen == "doctor") {
						presupuestoDocMarcarPlanesPendientes();
					}
				} else {
					ver_vetana_informativa("No se pudo guardar el tratamiento", datos["mensaje"] || "El presupuesto no corresponde al paciente seleccionado.", "error");
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ", responseText, "error")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
 
var totalPresupuesto=0;
var totalPresupuestoPrioritario=0;
function tieneTratamientosPresupuestoDoctor() {
	return !!document.querySelector("#table_vista_producto_presupuestoDetalle_doctor tr[name=tdDetallePresupuesto]");
}

function validarPresupuestoDoctorListo() {
	if (!presupuestoDocPacienteListoParaAvanzar()) {
		ver_vetana_informativa("Faltan datos", "Selecciona el paciente antes de definir los planes.", "error");
		return false;
	}

	if (!tieneTratamientosPresupuestoDoctor()) {
		ver_vetana_informativa("Faltan datos", "Todavia no agregaste tratamientos.", "error");
		return false;
	}

	if (!idabmPresupuesto) {
		ver_vetana_informativa("Error al guardar", "No se pudo crear el presupuesto. Revise la conexion e intente agregar el tratamiento nuevamente.", "error");
		return false;
	}

	return true;
}

function presupuestoDocSincronizarPlanVenta(planSeleccionado) {
	const origen = document.getElementById("table_vista_producto_presupuestoDetalle_doctor");
	const planTotal = document.getElementById("table_vista_producto_presupuestoDetalle");
	const planPrioritario = document.getElementById("table_vista_producto_presupuestoDetalle_prioritario");
	const docLegacy = document.getElementById("inptDocumentoClientePresupuesto");
	const nombreLegacy = document.getElementById("inptNombreClientePresupuesto");
	const docDoctor = document.getElementById("inptDocumentoClientePresupuestoDoc");
	const nombreDoctor = document.getElementById("inptNombreClientePresupuestoDoc");
	const selectPlan = document.getElementById("inptSelecctPlanPresupuesto");
	const selectPlanDoc = document.getElementById("inptPlanVentaPresupuestoDoc");
	const totalGeneral = document.getElementById("inptTOTALPresupuestoFORM");
	const totalPrioritario = document.getElementById("inptTOTALPresupuestoFORMPrioritario");

	if (!origen || !planTotal || !planPrioritario) {
		return false;
	}

	planTotal.innerHTML = "";
	planPrioritario.innerHTML = "";
	let total = 0;
	let totalPlanB = 0;
	Array.from(origen.children).forEach(function (tablaOriginal) {
		if (!tablaOriginal.matches("table")) {
			return;
		}
		asegurarCampoAlternativoPresupuestoDoc(tablaOriginal);
		const fila = tablaOriginal.querySelector("tr[name=tdDetallePresupuesto]");
		const esPrioritario = fila?.querySelector("#td_datos_12")?.textContent.trim() === "1";
		const campoAlternativo = fila?.querySelector("#td_datos_13");
		if (campoAlternativo) {
			campoAlternativo.textContent = "0";
		}
		const subtotal = Number(QuitarSeparadorMilValor(fila?.querySelector("#td_datos_11")?.textContent.trim() || "0")) || 0;
		const clonTotal = tablaOriginal.cloneNode(true);
		clonTotal.querySelectorAll("#td_datos_4, #td_datos_5").forEach(function (celda) {
			celda.style.display = "";
		});
		planTotal.appendChild(clonTotal);
		total += subtotal;
		if (esPrioritario) {
			const clonPrioritario = tablaOriginal.cloneNode(true);
			clonPrioritario.querySelectorAll("#td_datos_4, #td_datos_5").forEach(function (celda) {
				celda.style.display = "";
			});
			planPrioritario.appendChild(clonPrioritario);
			totalPlanB += subtotal;
		}
	});
	if (docLegacy && docDoctor) {
		docLegacy.value = docDoctor.value;
	}
	if (nombreLegacy && nombreDoctor) {
		nombreLegacy.value = nombreDoctor.value;
	}
	if (totalGeneral) {
		totalGeneral.value = separadordemilesnumero(total);
	}
	if (totalPrioritario) {
		totalPrioritario.value = separadordemilesnumero(totalPlanB);
	}
	if (selectPlan) {
		if (planSeleccionado) {
			selectPlan.value = presupuestoDocNormalizarPlanVenta(planSeleccionado);
		} else if (selectPlan.value == "" || selectPlan.value == "urgente") {
			selectPlan.value = selectPlan.value == "urgente" ? "prioritario" : "total";
		}
	}
	if (selectPlanDoc) {
		if (planSeleccionado) {
			selectPlanDoc.value = presupuestoDocNormalizarPlanVenta(planSeleccionado);
		} else if (selectPlanDoc.value == "" || selectPlanDoc.value == "urgente") {
			selectPlanDoc.value = selectPlanDoc.value == "urgente" ? "prioritario" : "total";
		}
	}
	if (typeof idAbmCliente != "undefined") {
		idAbmCliente = idFkCliente;
	}
	if (typeof generarTabla == "function") {
		generarTabla();
	}
	return true;
}

function presupuestoDocContinuarNuevaVenta(planSeleccionado) {
	if (!validarPresupuestoDoctorListo()) {
		return false;
	}
	const planVenta = presupuestoDocNormalizarPlanVenta(planSeleccionado);
	if (!presupuestoDocPlanesGuardados) {
		ver_vetana_informativa("Guarda la division de planes", "Antes de continuar a venta guarda el plan total y el plan provisorio.", "advertencia");
		return presupuestoDocMostrarPlanes();
	}
	if (!presupuestoDocSincronizarPlanVenta(planVenta)) {
		ver_vetana_informativa("Error al continuar", "No se pudo preparar el presupuesto para la venta.", "error");
		return false;
	}
	const resultado = presupuestoAVenta();
	if (resultado === false) {
		return false;
	}
	const ventana = document.getElementById("divAbmDetallesPresupuestoDoc");
	if (ventana) {
		ventana.style.display = "none";
	}
	presupuestoDocCerrarModalPlanes();
	vistaPresupuestoOrigen = "";
	limpiarAgendaPresupuestoDoctorActiva();
	return true;
}

function crearPresupuestoDoctorSiHaceFalta(callback) {
	if (idabmPresupuesto) {
		callback();
		return true;
	}

	if (!idFkCliente) {
		ver_vetana_informativa("Faltan datos", "Favor seleccionar el cliente", "error");
		return false;
	}

	if (presupuestoGuardando) {
		ver_vetana_informativa("Guardando", "Se esta creando el presupuesto, espere un momento.", "info");
		return false;
	}

	presupuestoGuardando = true;
	verCerrarEfectoCargando("1");
	abmPresupuesto("", "", idFkCliente, null, null, {
		silencioso: true,
		mostrarError: false,
		conservarDatosEnError: true,
		onSuccess: function () {
			presupuestoGuardando = false;
			verCerrarEfectoCargando("");
			if (!idabmPresupuesto) {
				ver_vetana_informativa("Error al guardar", "No se pudo crear el presupuesto. Intente agregar el tratamiento nuevamente.", "error");
				return;
			}
			callback();
		},
		onError: function () {
			presupuestoGuardando = false;
			verCerrarEfectoCargando("");
			ver_vetana_informativa("Error al guardar", "No se pudo crear el presupuesto. Revise la conexion e intente agregar el tratamiento nuevamente.", "error");
		}
	});
	return false;
}

function anhadirPrPresupuesto() {
	let entrega = "";

	let inptCodigoPresupuesto = "";
	let inptProductoPresupuesto = "";
	let inptPrecioPresupuesto = "";
	let inptCantidadPresupuesto = "";
	let inptTotalPresupuesto = "";
	let inpTSeleccCostoPresupuesto = "";
	let inpCuotero = "";
	let inpPrecioContado = "";
	let inptPrioritarioPresupuesto= true;
	let inptAlternativoPresupuesto = false;

	if (vistaPresupuestoOrigen == "doctor") {
		inptCodigoPresupuesto = document.getElementById('inptCodigoPresupuestoDoc').value
		inptProductoPresupuesto = document.getElementById('inptProductoPresupuestoDoc').value
		inptPrecioPresupuesto = document.getElementById('inptPrecioPresupuestoDoc').value
		inptCantidadPresupuesto = document.getElementById('inptCantidadPresupuestoDoc').value
		inptTotalPresupuesto = document.getElementById('inptTotalPresupuestoDoc').value
		inpTSeleccCostoPresupuesto = $("select[id=inpTSeleccCostoPresupuestoDoc]").children(":selected").attr("class")
		inpCuotero = $("select[id=inpTSeleccCostoPresupuestoDoc]").children(":selected").attr("id")
		inpPrecioContado = $("select[id=inpTSeleccCostoPresupuestoDoc]").children(":selected").attr("url")
		inptPrioritarioPresupuesto= true;
		inptAlternativoPresupuesto = false;
	} else {
		entrega = document.getElementById('inptEntregaPresupuesto').value
		inptCodigoPresupuesto = document.getElementById('inptCodigoPresupuesto').value
		inptProductoPresupuesto = document.getElementById('inptProductoPresupuesto').value
		inptPrecioPresupuesto = document.getElementById('inptPrecioPresupuesto').value
		inptCantidadPresupuesto = document.getElementById('inptCantidadPresupuesto').value
		inptTotalPresupuesto = document.getElementById('inptTotalPresupuesto').value
		inpTSeleccCostoPresupuesto = $("select[id=inpTSeleccCostoPresupuesto]").children(":selected").attr("class")
		inpCuotero = $("select[id=inpTSeleccCostoPresupuesto]").children(":selected").attr("id")
		inpPrecioContado = $("select[id=inpTSeleccCostoPresupuesto]").children(":selected").attr("url")
		inptPrioritarioPresupuesto= document.getElementById('inptPrioritarioPresupuesto').checked;
		inptAlternativoPresupuesto = document.getElementById('inptAlternativoPresupuesto').checked;
		if (inptAlternativoPresupuesto) {
			inptPrioritarioPresupuesto = true;
		}
	}
	
	if (inptCodigoPresupuesto != "") {
		if (!idFkCliente) {
			ver_vetana_informativa("Faltan datos", "Favor seleccionar el cliente", "error");
			return false;
		}

		if (inptCantidadPresupuesto <= 0 || inptCantidadPresupuesto == "") {
			ver_vetana_informativa("Faltan datos", "FAVOR AGREGAR CANTIDAD");
			return false;
		}

		if (vistaPresupuestoOrigen == "historial") {
			if (inptPrecioPresupuesto <= 0 || inptPrecioPresupuesto == "") {
				ver_vetana_informativa("Faltan datos", "FAVOR AGREGAR EL PRECIO");
				return false;
			}
	
			if (inptTotalPresupuesto == "0" || inptTotalPresupuesto == "") {
				ver_vetana_informativa("Faltan datos", "TOTAL NO VALIDO");
				return false;
			}
		} else {
			if (inptPrecioPresupuesto <= 0 || inptPrecioPresupuesto == "") {
				ver_vetana_informativa("Faltan datos", "Error al seleccionar el producto.");
				return false;
			}
		}

		if (!idabmPresupuesto) {
			if (vistaPresupuestoOrigen == "doctor") {
				return crearPresupuestoDoctorSiHaceFalta(function () {
					anhadirPrPresupuesto();
				});
			}

			ver_vetana_informativa("Error al guardar", "No se pudo crear el presupuesto. Vuelva a seleccionar el cliente e intente nuevamente.", "error");
			return false;
		}

		// Agrega al presupuesto existente
		abmDetallesPresupuesto(idabmPresupuesto, idFkProducto, inptPrecioPresupuesto.replace('.', ''), inptCantidadPresupuesto, inptCodigoPresupuesto, inptProductoPresupuesto,inptTotalPresupuesto,inpPrecioContado, inptPrioritarioPresupuesto, inptAlternativoPresupuesto, justificacionProductoPresupuestoSeleccionado);
	} else {
		ver_vetana_informativa("Faltan datos", "Favor seleccionar un producto", "error");
		return false;
	}
}

function limpirarPresupuesto(){
	idabmPresupuesto= "";
	presupuestoDocPlanesGuardados = false;
	presupuestoDocEstadoPlanes = "sin_cambios";
	presupuestoDocSeleccionProvisorioInicial = {};
	document.getElementById('inptCodigoPresupuestoDoc').value = ""
	document.getElementById('inptProductoPresupuestoDoc').value = ""
	document.getElementById('inptPrecioPresupuestoDoc').value = ""
	document.getElementById('inptCantidadPresupuestoDoc').value = ""
	document.getElementById('inptTotalPresupuestoDoc').value = ""
	document.getElementById('inptPrioritarioPresupuestoDoc').checked = true;

	document.getElementById('inptCodigoPresupuesto').value = ""
	document.getElementById('inptProductoPresupuesto').value = ""
	document.getElementById('inptPrecioPresupuesto').value = ""
	document.getElementById('inpTSeleccCostoPresupuesto').value = ""
	document.getElementById('inptEntregaPresupuesto').value = "0"
	document.getElementById('inptCantidadPresupuesto').value = ""
	document.getElementById('inptTotalPresupuesto').value = ""
	document.getElementById('inptPrioritarioPresupuesto').checked = true;
	document.getElementById('inptAlternativoPresupuesto').checked = false;
	document.getElementById('table_vista_producto_presupuestoDetalle_doctor').innerHTML = ""
	document.getElementById('table_vista_producto_presupuestoDetalle_plan_a_doctor').innerHTML = ""
	document.getElementById('table_vista_producto_presupuestoDetalle_prioritario_doctor').innerHTML = ""
	document.getElementById('table_vista_producto_presupuestoDetalle_doctor_resumen').innerHTML = ""
	document.getElementById('table_vista_producto_Presupuesto_doctor').innerHTML = ""
	limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "doctor");
	verPasoPresupuestoDoc(1)
	
	totalPresupuesto=0;
	totalPresupuestoPrioritario=0;
	tipo_plan="";
	presupuestoCuotaSeleccionadaVisual = { total: null, prioritario: null };
	justificacionProductoPresupuestoSeleccionado = "";
	document.getElementById('inptTotalPresupuesto2').innerHTML = ""
	document.getElementById('inptTOTALPresupuestoFORM').value = "0"
	document.getElementById('inptTOTALPresupuestoFORMPrioritario').value = "0"
	document.getElementById('table_vista_producto_Presupuesto').innerHTML = ""
	document.getElementById('table_vista_producto_presupuestoDetalle').innerHTML = ""
	document.getElementById('table_vista_producto_presupuestoDetalle_prioritario').innerHTML = ""
	document.getElementById('table_vista_detalles_presupuesto').innerHTML = ""
	document.getElementById('table_vista_detalles_presupuesto_prioritario').innerHTML = ""
	limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "presupuesto");

	document.getElementById('inptDocumentoClientePresupuesto').value= "";
	document.getElementById('inptNombreClientePresupuesto').value= "";
	idFkCliente= "";
	document.getElementById('inptDocumentoClientePresupuestoDoc').value= "";
	document.getElementById('inptNombreClientePresupuestoDoc').value= "";
	if (document.getElementById('inptTelefonoClientePresupuestoDoc')) {
		document.getElementById('inptTelefonoClientePresupuestoDoc').value = "";
	}
	if (document.getElementById('inptWhatsappClientePresupuestoDoc')) {
		document.getElementById('inptWhatsappClientePresupuestoDoc').value = "";
	}
	if (document.getElementById('inptDireccionClientePresupuestoDoc')) {
		document.getElementById('inptDireccionClientePresupuestoDoc').value = "";
	}

	document.getElementById("divCabeceraImpresiones").innerHTML=""
	document.getElementById("tbTitulosImpresiones").innerHTML=""
	document.getElementById("tbDatosImpresiones").innerHTML=""

	document.getElementById("divPieImpresiones").innerHTML=""
	actualizarResumenPacientePresupuestoDoc()
	inicializarPresupuestoVisual()
	presupuestoDocActualizarAccionesPlanes()
	if (typeof odontogramaEstados != "undefined") {
		odontogramaEstados.presupuesto = null;
	}
	if (document.getElementById("odontogramaPresupuestoDoctor")) {
		document.getElementById("odontogramaPresupuestoDoctor").innerHTML = "<div class='odontograma-empty'>Seleccione un paciente para activar el odontograma del presupuesto.</div>";
	}
}

function limpiarCamposGenerarTratamiento(forzar= false) {
	if (!forzar && !validarPresupuestoDoctorListo()) {
		return false;
	}

	ver_vetana_informativa("Datos guardados exitosamente", "El plan de tratamientos fue guardado y la ventana quedo lista para una nueva carga.", "info");

	idabmPresupuesto = "";
	idFkCliente = "";
	document.getElementById('inptDocumentoClientePresupuestoDoc').value = "";	
	document.getElementById('inptNombreClientePresupuestoDoc').value = "";
	limpirarPresupuesto();

	if (document.getElementById('inptDocumentoClientePresupuestoDoc')) {
		document.getElementById('inptDocumentoClientePresupuestoDoc').focus();
	}

	return true;
}

totalregistroPresupuesto= 0;
registrocargadoPresupuesto= 0;
controldebusquedadPresupuesto= true;
busquedaActivaPresupuesto= 0;
var PRESUPUESTO_LISTADO_LIMITE = 25;
var presupuestoFiltroRapidoTimer = null;
var presupuestoListadoCargando = false;

function presupuestoValorFiltro(id) {
	const elemento = document.getElementById(id);
	return elemento ? elemento.value : "";
}

function presupuestoPrimerValorFiltro() {
	for (var i = 0; i < arguments.length; i++) {
		const valor = presupuestoValorFiltro(arguments[i]);
		if (String(valor || "").trim() != "") {
			return valor;
		}
	}
	return "";
}

function presupuestoSetTexto(id, texto) {
	const elemento = document.getElementById(id);
	if (elemento) {
		elemento.textContent = texto;
	}
}

function presupuestoActualizarEstadoListado() {
	const inputTotal = document.getElementById("inptTotalRegistoPresupuesto");
	if (inputTotal) {
		inputTotal.value = registrocargadoPresupuesto;
	}

	presupuestoSetTexto("txtTotalRegistrosPresupuesto", "de " + totalregistroPresupuesto);
	presupuestoSetTexto(
		"txtEstadoRegistrosPresupuesto",
		totalregistroPresupuesto > 0
			? "Mostrando " + registrocargadoPresupuesto + " de " + totalregistroPresupuesto
			: "Sin resultados"
	);

	const btnVerMas = document.getElementById("btnVerMasPresupuesto");
	if (btnVerMas) {
		const quedanRegistros = totalregistroPresupuesto > registrocargadoPresupuesto;
		btnVerMas.style.display = quedanRegistros ? "" : "none";
		btnVerMas.disabled = presupuestoListadoCargando;
		btnVerMas.value = presupuestoListadoCargando ? "Cargando..." : "Ver mas presupuestos";
	}

	const proceso = document.getElementById("tbProcessPresupuesto");
	if (proceso) {
		proceso.style.display = "none";
	}
}

function presupuestoCrearDatosBusqueda(offset) {
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "obtenerPresupuesto");
	datos.append("cod_clienteFK", idFkCliente);
	datos.append("nombre_cedula_cliente", presupuestoPrimerValorFiltro("inptClienteCedulaFiltroPresupuestoRapido", "inptClienteCedulaFiltroPresupuesto"));
	datos.append("id", presupuestoPrimerValorFiltro("inptIdFiltroPresupuestoRapido", "inptIdFiltroPresupuesto"));
	datos.append("plan_vendido", presupuestoValorFiltro("inptPlanFiltroPresupuesto"));
	datos.append("cod_localFK", presupuestoValorFiltro("inptCodLocalFiltroPresupuesto"));
	datos.append("nombre_usuario_create", presupuestoValorFiltro("inptNombreCreadorFiltroPresupuesto"));
	datos.append("fecha_inicio", presupuestoValorFiltro("inptFechaInicioFiltroPresupuesto"));
	datos.append("fecha_fin", presupuestoValorFiltro("inptFechaFinFiltroPresupuesto"));
	datos.append("limite", PRESUPUESTO_LISTADO_LIMITE);
	datos.append("offset", offset || 0);
	return datos;
}

function presupuestoMostrarCargaListado(esContinuacion) {
	const contenedor = document.getElementById("table_vista_presupuesto");
	if (!contenedor) {
		return;
	}

	if (!esContinuacion) {
		contenedor.innerHTML = "<div class='presupuesto-search-loading'>Cargando presupuestos recientes...</div>";
	}
}

function presupuestoEjecutarBusqueda(offset, esContinuacion) {
	const busquedaPresupuesto = ++busquedaActivaPresupuesto;
	presupuestoListadoCargando = true;
	presupuestoActualizarEstadoListado();
	presupuestoMostrarCargaListado(esContinuacion);

	$.ajax({
		data: presupuestoCrearDatosBusqueda(offset),
		url: "/GoodVentaAsisCap/php_system/abmPresupuesto.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus, errorThrowm) {
			if (busquedaPresupuesto !== busquedaActivaPresupuesto) {
				return;
			}
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
			ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR", "", "error");
			console.error(jqXHR, textstatus, errorThrowm);
		},
		success: function (responseText) {
			if (busquedaPresupuesto !== busquedaActivaPresupuesto) {
				return;
			}

			try {
				var datos = parsearRespuestaAjaxPresupuesto(responseText);
				if (datos["1"] == "exito") {
					const contenedor = document.getElementById("table_vista_presupuesto");
					const registrosRecibidos = parseInt(datos["4"], 10) || 0;
					totalregistroPresupuesto = parseInt(datos["5"], 10) || 0;

					if (esContinuacion) {
						contenedor.innerHTML += datos["3"] || "";
						registrocargadoPresupuesto += registrosRecibidos;
					} else {
						contenedor.innerHTML = datos["3"] || "";
						registrocargadoPresupuesto = registrosRecibidos;
					}
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
				var titulo="Error: "+error+" \r\n Consola: "+textoRespuestaAjaxPresupuesto(responseText);
				GuardarArchivosLog(titulo);
			}
		},
		complete: function () {
			if (busquedaPresupuesto !== busquedaActivaPresupuesto) {
				return;
			}
			presupuestoListadoCargando = false;
			presupuestoActualizarEstadoListado();
		}
	});
}

function buscarvistaPresupuesto() {
	totalregistroPresupuesto= 0;
	registrocargadoPresupuesto= 0;
	controldebusquedadPresupuesto= true;
	idabmPresupuesto = "";
	presupuestoActualizarEstadoListado();
	presupuestoEjecutarBusqueda(0, false);
}

function buscarmasVistaPresupuesto() {
	if (presupuestoListadoCargando || totalregistroPresupuesto <= registrocargadoPresupuesto) {
		return;
	}
	presupuestoEjecutarBusqueda(registrocargadoPresupuesto, true);
}

function presupuestoBuscarRapido(event) {
	if (presupuestoFiltroRapidoTimer) {
		clearTimeout(presupuestoFiltroRapidoTimer);
	}

	if (event && event.keyCode == 13) {
		buscarvistaPresupuesto();
		return;
	}

	const texto = (presupuestoValorFiltro("inptClienteCedulaFiltroPresupuestoRapido") + presupuestoValorFiltro("inptIdFiltroPresupuestoRapido")).trim();
	if (texto.length == 1) {
		return;
	}

	presupuestoFiltroRapidoTimer = setTimeout(function () {
		buscarvistaPresupuesto();
	}, 450);
}

function limpiarFiltroPresupuesto() {
	idFkCliente = "";
	if (document.getElementById('inptClienteCedulaFiltroPresupuestoRapido')) {
		document.getElementById('inptClienteCedulaFiltroPresupuestoRapido').value = "";
	}
	if (document.getElementById('inptIdFiltroPresupuestoRapido')) {
		document.getElementById('inptIdFiltroPresupuestoRapido').value = "";
	}
	document.getElementById('inptClienteCedulaFiltroPresupuesto').value = "";
	document.getElementById('inptIdFiltroPresupuesto').value = "";
	document.getElementById('inptCodLocalFiltroPresupuesto').value = "";
	document.getElementById('inptFechaInicioFiltroPresupuesto').value = "";
	document.getElementById('inptNombreCreadorFiltroPresupuesto').value= "";
	document.getElementById('inptPlanFiltroPresupuesto').value= "";
	document.getElementById('inptFechaFinFiltroPresupuesto').value = "";

	buscarvistaPresupuesto();
}

function cancelarListadoPresupuesto() {
	controldebusquedadPresupuesto= false;
	busquedaActivaPresupuesto++;
	presupuestoListadoCargando = false;
	presupuestoActualizarEstadoListado();
	const progreso = document.getElementById("divProgressPresupuesto");
	if (progreso) {
		progreso.style.backgroundColor='#ff5722';
	}
}

function obtenerValorCeldaPresupuesto(elemento, celdaId) {
	const celda = $(elemento).children('td[id="' + celdaId + '"]');
	return celda.length ? (celda.html() || "") : "";
}

function asignarValorElementoPresupuesto(elementoId, valor) {
	const elemento = document.getElementById(elementoId);
	if (elemento) {
		elemento.value = valor || "";
	}
}

function obtenerValorElementoPresupuesto(elementoId) {
	const elemento = document.getElementById(elementoId);
	return elemento ? (elemento.value || "") : "";
}

function obtenerDatosPresupuesto(elemento) {
	cancelarListadoPresupuesto();
    idabmPresupuesto = obtenerValorCeldaPresupuesto(elemento, "td_id");
	const registroSeleccionadoPresupuesto = document.getElementById('inptRegistroSeleccPresupuesto');
	if (registroSeleccionadoPresupuesto) {
		registroSeleccionadoPresupuesto.value = idabmPresupuesto + " - " + obtenerValorCeldaPresupuesto(elemento, "td_datos_4");
	}
    document.getElementById('inptCodigoPresupuesto').value = idabmPresupuesto;
    totalPresupuesto= obtenerValorCeldaPresupuesto(elemento, "td_datos_7");
    totalPresupuestoPrioritario= obtenerValorCeldaPresupuesto(elemento, "td_datos_8");
    document.getElementById('inptTotalPresupuesto2').innerHTML = totalPresupuesto;
    document.getElementById('inptTOTALPresupuestoFORM').value = totalPresupuesto;
    document.getElementById('inptTOTALPresupuestoFORMPrioritario').value = totalPresupuestoPrioritario;

    document.getElementById('table_vista_producto_presupuestoDetalle').innerHTML = "";$(elemento).children('td[id="td_datos_"]').html();
    document.getElementById('table_vista_detalles_presupuesto').innerHTML = "";$(elemento).children('td[id="td_datos_"]').html();
    document.getElementById('table_vista_detalles_presupuesto_prioritario').innerHTML = "";$(elemento).children('td[id="td_datos_"]').html();
	buscarDetallesPresupuesto(idabmPresupuesto);

    document.getElementById('inptDocumentoClientePresupuesto').value= obtenerValorCeldaPresupuesto(elemento, "td_datos_5");
    document.getElementById('inptNombreClientePresupuesto').value= obtenerValorCeldaPresupuesto(elemento, "td_datos_4");
    idFkCliente= obtenerValorCeldaPresupuesto(elemento, "td_datos_3");
    idAbmCliente= idFkCliente;
	verCerrarAbmDetallesPresupuesto(true, false);
	asignarValorElementoPresupuesto('inptNombreApellidoCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_4"));
	asignarValorElementoPresupuesto('inptNroDocCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_5"));
	asignarValorElementoPresupuesto('inptNroRucCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_13"));
	asignarValorElementoPresupuesto('inptNrowhatsappCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_14"));
	asignarValorElementoPresupuesto('inptNroTelefCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_16"));
	asignarValorElementoPresupuesto('inptDireccionCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_17"));
	asignarValorElementoPresupuesto('inptReferenciaCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_18"));
	asignarValorElementoPresupuesto('inptZonaCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_11"));
	asignarValorElementoPresupuesto('inptFechaNacCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_15"));
	asignarValorElementoPresupuesto('inptLugrarTrabajoCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_19"));
	asignarValorElementoPresupuesto('inptDireccionTrabajoCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_20"));
	asignarValorElementoPresupuesto('inptSalarioCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_21"));
	asignarValorElementoPresupuesto('inptAntiguedadCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_22"));
	asignarValorElementoPresupuesto('inptNroTelefTrabajoCliente1', obtenerValorCeldaPresupuesto(elemento, "td_datos_23"));
	asignarValorElementoPresupuesto('inptNroTelefTrabajoCliente2', obtenerValorCeldaPresupuesto(elemento, "td_datos_24"));
	asignarValorElementoPresupuesto('inptAccesoCreditoCliente', obtenerValorCeldaPresupuesto(elemento, "td_datos_25"));
	idFKZona= obtenerValorCeldaPresupuesto(elemento, "td_datos_12");
	
	verificarDatosCliente(true);
	actualizarResumenPresupuestoVenta();
}

function buscarDetallesPresupuesto(cod_presupuestoFK) {
	document.getElementById("table_vista_producto_presupuestoDetalle").innerHTML= paginacargando;
	document.getElementById("table_vista_producto_presupuestoDetalle_prioritario").innerHTML= paginacargando;
	document.getElementById("table_vista_detalles_presupuesto").innerHTML= paginacargando;
	document.getElementById("table_vista_detalles_presupuesto_prioritario").innerHTML= paginacargando;

	obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", "obtenerDetallesPresupuesto");
	datos.append("cod_presupuestoFK", cod_presupuestoFK);

	verCerrarEfectoCargando("1")
    var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPresupuesto.php",
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
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR", "", "error");
			console.error(jqXHR, textstatus, errorThrowm);
		},
		success: function (responseText) {
			document.getElementById("table_vista_presupuesto").innerHTML= '';
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = parsearRespuestaAjaxPresupuesto(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					document.getElementById("table_vista_producto_presupuestoDetalle").innerHTML= datos["3"];
					document.getElementById("table_vista_producto_presupuestoDetalle_prioritario").innerHTML= datos["4"];
					recalcularTotalPresupuesto();
					generarTabla();
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ", error, "error")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function verCerrarFiltrosPresupuesto(mostrar) {
	if (mostrar) {
		//document.getElementById('divListPresupuesto').style.display= "none";
		document.getElementById('overlayFiltrosPresupuesto').style.display= "";
	} else {
		//document.getElementById('divListPresupuesto').style.display= "";
		document.getElementById('overlayFiltrosPresupuesto').style.display= "none";
	}
}

function verCerrarAbmDetallesPresupuestoDoc(mostrar){
	vistaPresupuestoOrigen= "doctor"
	if(mostrar){
		if(document.getElementById("divAbmDetallesPresupuestoDoc").style.display!=""){
			limpirarPresupuesto();
			limpiarAgendaPresupuestoDoctorActiva();
		}
		document.getElementById("divAbmDetallesPresupuestoDoc").style.display=""
		presupuestoDocPlanesGuardados = false;
		presupuestoDocEstadoPlanes = "sin_cambios";
		presupuestoDocSeleccionProvisorioInicial = {};
		presupuestoDocActualizarAccionesPlanes();
		limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "doctor");
		verPasoPresupuestoDoc(1);
		sincronizarResumenDetallePresupuestoDoc();
		if (typeof cargarOdontogramaPresupuestoDoctor == "function") {
			cargarOdontogramaPresupuestoDoctor();
		}
	}else{
		presupuestoDocCerrarModalPlanes();
		$("div[id=divAbmDetallesPresupuestoDoc]").fadeOut(500);
		vistaPresupuestoOrigen= "";
		limpiarAgendaPresupuestoDoctorActiva();

		switch (ventanaAnterior[ventanaAnterior.length - 1]) {
			case 'calendario':
				AbrirAgendaConsultorios(false);
				break;
			case 'divAgendaConsultorios':
				document.getElementById('divAgendaConsultorios').style.display= "";
				ventanaAnterior.pop();
				break;
		}
	}
}

var tipo_plan= "";
function presupuestoAVenta(){
	if (typeof bloquearAccionMientrasGuardaCliente == "function" && bloquearAccionMientrasGuardaCliente()) {
		return false;
	}

	if ((document.getElementById('inptTOTALPresupuestoFORM').value == "0" && document.getElementById('inptTOTALPresupuestoFORMPrioritario').value == "0") ||
		(document.getElementById('inptTOTALPresupuestoFORM').value == "" && document.getElementById('inptTOTALPresupuestoFORMPrioritario').value == "")) {
		ver_vetana_informativa("Faltan datos", "Favor armar el presupuesto primeramente", "error");
		return false;
	}
	const codClientePresupuesto = idFkCliente;
	const documentoClientePresupuesto = String(document.getElementById('inptDocumentoClientePresupuesto').value || "").split('-')[0].trim();
	const nombreClientePresupuesto = document.getElementById('inptNombreClientePresupuesto').value;

	if (codClientePresupuesto == "" || documentoClientePresupuesto == "" || nombreClientePresupuesto == "") {
		ver_vetana_informativa("Faltan datos del cliente", "Se debe seleccionar un cliente valido para concretar la venta.", "advertencia");
		return false;
	}

	var documentoAbmCliente = String(document.getElementById('inptNroDocCliente').value || "").split('-')[0].trim();
	if (String(idAbmCliente || "") != String(codClientePresupuesto) || documentoAbmCliente != documentoClientePresupuesto) {
		document.getElementById('inptNombreApellidoCliente').value = nombreClientePresupuesto;
		document.getElementById('inptNroDocCliente').value = documentoClientePresupuesto;
		document.getElementById('inptNroTelefCliente').value = "";
		document.getElementById('inptNrowhatsappCliente').value = "";
		document.getElementById('inptDireccionCliente').value = "";
		document.getElementById('inptReferenciaCliente').value = "";
		idAbmCliente = codClientePresupuesto;
	}

	if (!verificarDatosCliente(true)) {
		return false;
	}

	limpiarcamposventa("2");

	// Se evalua cual fue el plan seleccionado y si la venta es a credito
	let plan= "";
	const planSeleccionado = presupuestoDocNormalizarPlanVenta(document.getElementById('inptSelecctPlanPresupuesto').value);
	document.getElementById('inptSelecctPlanPresupuesto').value = planSeleccionado;
	if (planSeleccionado == "total") {
		plan= document.getElementById('table_vista_producto_presupuestoDetalle');
		tipo_plan= "total";
	} else {
		plan= document.getElementById('table_vista_producto_presupuestoDetalle_prioritario');
		tipo_plan= "prioritario";
	}
	if (!plan || !plan.querySelector("tr[name=tdDetallePresupuesto]")) {
		ver_vetana_informativa("Plan vacio", planSeleccionado == "prioritario" ? "El plan provisorio no tiene tratamientos para concretar." : "El plan total no tiene tratamientos para concretar.", "advertencia");
		return false;
	}

	document.getElementById('inptSeleccTipoVenta').value= document.getElementById('inptSelecctModalidadPresupuesto').value;

	// Agrega los datos del cliente
	const inptDocClienteVenta= document.getElementById('inptDocClienteVenta');
	inptDocClienteVenta.value= documentoClientePresupuesto;
	document.getElementById('inptDocClienteVenta2').value= documentoClientePresupuesto;
	document.getElementById('inptClienteVenta').value= nombreClientePresupuesto;
	document.getElementById('inptClienteVenta2').value= nombreClientePresupuesto;
	idFkCliente= codClientePresupuesto;
	asignarValorElementoPresupuesto('inptDireccionVenta', obtenerValorElementoPresupuesto('inptDireccionCliente'));
	asignarValorElementoPresupuesto('inptTelefVenta', obtenerValorElementoPresupuesto('inptNroTelefCliente') || obtenerValorElementoPresupuesto('inptNrowhatsappCliente'));
	asignarValorElementoPresupuesto('inptAccesoCreditoVentaCliente', obtenerValorElementoPresupuesto('inptAccesoCreditoCliente'));
	if (document.getElementById("btnMasInfoClienteVenta")) {
		document.getElementById("btnMasInfoClienteVenta").style.display='';
	}
	if (document.getElementById("btnNuevoClienteVenta")) {
		document.getElementById("btnNuevoClienteVenta").style.display='none';
	}

	// Agrega los productos
	Array.from(plan.children).forEach(tabla => {
		const cod_producto= $(tabla).find("#td_datos_14").html();
		const cod_barra= $(tabla).find("#td_datos_1").html();
		const nombre_producto= $(tabla).find("#td_datos_2").html();
		const detalle_venta= "";//$(tabla).find("#td_datos_").html();
		const costo= separadordemilesnumero($(tabla).find("#td_datos_10").html());
		const cantidad= $(tabla).find("#td_datos_3 span").first().text() || $(tabla).find("#td_datos_3").text().trim();
		const total_costo= separadordemilesnumero($(tabla).find("#td_datos_11").html());
		const cuota_nro= 1;//$(tabla).find("#td_datos_").html()

		const porcentaje_contado= $(tabla).find("#td_datos_15")['context'].children[0].style;
		const porcentaje_credito= $(tabla).find("#td_datos_15")['context'].children[0].class;
		const precio_contado_producto= $(tabla).find("#td_datos_15")['context'].children[0].url;

		const costo_total_venta= separadordemilesnumero($(tabla).find("#td_datos_11").html());
		
		const nroid = Math.floor((Math.random() * 1000) + 1);
		mostrarProductoEnDetalleVenta(cod_producto,cod_barra,nombre_producto,detalle_venta,costo,cantidad,0,total_costo,0,cuota_nro,0,porcentaje_contado,porcentaje_credito,precio_contado_producto,costo_total_venta,nroid);
		
		// Calcula los totales
		let totalVenta = 0;
		let SubtotalVenta = 0;
		let totaldescuento = 0;
		let control = 0;
		$("tr[name=tdDetalleVentaOffline]").each(function (i, elementohtml) {
			let total = $(elementohtml).children('td[id="td_datos_15"]').html();
			let totaldescuentos = $(elementohtml).children('td[id="td_datos_9"]').html();
			totaldescuentos = QuitarSeparadorMilValor(totaldescuentos)
			total = QuitarSeparadorMilValor(total)
			totalVenta = Number(totalVenta) + Number(total)
			totaldescuento = Number(totaldescuento) + Number(totaldescuentos)
			SubtotalVenta = Number(totalVenta) + Number(totaldescuento)
			control = control + 1;
		});
	
		if (control == "1") {
			DatosAutoCompleteCredito.push(1)
		}
		buscarDescripcionProducto(cod_producto, totalVenta, nroid);

		document.getElementById("inptSubTotalVenta").value=separadordemilesnumero(SubtotalVenta);
		document.getElementById("inptTotalVenta").value=separadordemilesnumero(totalVenta);
		document.getElementById("inptTotalVenta2").innerHTML=separadordemilesnumero(totalVenta);
		document.getElementById("inptTotalDescuento").value=separadordemilesnumero(totaldescuento);
		
		OpcionesTipoVenta();
	});

	limpiarCamposAnhadirProductosVenta();
	verCerrarAbmVenta();
}

function cargarTratamientoDesdeAgenda() {
	verCerrarAbmDetallesPresupuestoDoc(true);
	limpirarPresupuesto();
	const idPacienteAgenda = document.getElementById('detAgendaPacienteId') ? document.getElementById('detAgendaPacienteId').textContent.trim() : "";
	const nombrePaciente = document.getElementById('detAgendaPaciente') ? (document.getElementById('detAgendaPaciente').getAttribute('data-nombre-paciente') || document.getElementById('detAgendaPaciente').textContent).trim() : "";
	const documentoPaciente = document.getElementById('detAgendaCedula') ? (document.getElementById('detAgendaCedula').getAttribute('data-documento-paciente') || document.getElementById('detAgendaCedula').textContent).trim() : "";
	const telefonoPaciente = document.getElementById('detAgendaCedula') ? (document.getElementById('detAgendaCedula').getAttribute('data-telefono-paciente') || "").trim() : "";
	const whatsappPaciente = document.getElementById('detAgendaCedula') ? (document.getElementById('detAgendaCedula').getAttribute('data-whatsapp-paciente') || "").trim() : "";
	const direccionPaciente = document.getElementById('detAgendaCedula') ? (document.getElementById('detAgendaCedula').getAttribute('data-direccion-paciente') || "").trim() : "";

	if (idPacienteAgenda == "") {
		ver_vetana_informativa("Faltan datos", "No se pudo identificar el paciente del agendamiento.", "error");
		return false;
	}

	idFkCliente = idPacienteAgenda;
	idAgendaPresupuestoDoctorActiva = document.getElementById('detAgendaId') ? document.getElementById('detAgendaId').textContent.trim() : "";
	idAbmAgenda = idAgendaPresupuestoDoctorActiva;
	idPacientePresupuestoDoctorActivo = idPacienteAgenda;
	document.getElementById('inptDocumentoClientePresupuestoDoc').value= documentoPaciente;
	document.getElementById('inptNombreClientePresupuestoDoc').value= nombrePaciente;
	if (document.getElementById('inptTelefonoClientePresupuestoDoc')) {
		document.getElementById('inptTelefonoClientePresupuestoDoc').value = telefonoPaciente;
	}
	if (document.getElementById('inptWhatsappClientePresupuestoDoc')) {
		document.getElementById('inptWhatsappClientePresupuestoDoc').value = whatsappPaciente;
	}
	if (document.getElementById('inptDireccionClientePresupuestoDoc')) {
		document.getElementById('inptDireccionClientePresupuestoDoc').value = direccionPaciente;
	}
	idAbmCliente = idFkCliente;
	document.getElementById('inptNombreApellidoCliente').value = nombrePaciente;
	document.getElementById('inptNroDocCliente').value = documentoPaciente;
	document.getElementById('inptNroTelefCliente').value = telefonoPaciente;
	document.getElementById('inptNrowhatsappCliente').value = whatsappPaciente || telefonoPaciente;
	document.getElementById('inptDireccionCliente').value = direccionPaciente;
	actualizarResumenPacientePresupuestoDoc();
	if (typeof cargarOdontogramaPresupuestoDoctor === "function") {
		cargarOdontogramaPresupuestoDoctor();
	}
	cerrarDetalleAgenda();
	cerrarAgendaConsultorios();
}

function confirmarDatosGuardados() {
	if (pasoVistaPresupuestoDoc == 4 || document.getElementById("presupuestoDocPlanesModal")?.style.display == "flex") {
		return presupuestoDocGuardarPlanes();
	}
	return presupuestoDocMostrarPlanes();
}
