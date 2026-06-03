function abrirInformeSolicitudEliminado() {
	var ventana = document.getElementById("divInformeSolicitudEliminado");
	if (!ventana) { return; }

	ventana.style.display = "block";
	var minimizado = document.getElementById("divMinimizadoInformeSolicitudEliminado");
	if (minimizado) {
		minimizado.style.display = "none";
	}
	buscarInformeSolicitudEliminado();
}

function valorSeguroSolicitudEliminado(valor) {
	return (valor == null || valor == undefined) ? "" : valor;
}

function cerrarInformeSolicitudEliminado() {
	var ventana = document.getElementById("divInformeSolicitudEliminado");
	if (!ventana) { return; }

	ventana.style.display = "none";
}

function minimizarInformeSolicitudEliminado() {
	document.getElementById("divMinimizadoInformeSolicitudEliminado").style.display = "";
	document.getElementById("divInformeSolicitudEliminado").style.display = "none";
}

function buscarInformeSolicitudEliminado() {
	obtener_datos_user();

	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "buscar");
	datos.append("buscar", document.getElementById("inptSolicitudEliminadoBuscar").value);
	datos.append("usuario", document.getElementById("inptSolicitudEliminadoUsuario").value);
	datos.append("estado", document.getElementById("inptSolicitudEliminadoEstado").value);
	datos.append("fecha_desde", document.getElementById("inptSolicitudEliminadoFechaDesde").value);
	datos.append("fecha_hasta", document.getElementById("inptSolicitudEliminadoFechaHasta").value);

	verCerrarEfectoCargando("1");
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmSolicitudEliminadoInforme.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
			ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					document.getElementById("table_InformeSolicitudEliminado").innerHTML = respuesta["2"];
					document.getElementById("inptTotalRegistroSolicitudEliminado").value = respuesta["4"];
				} else {
					ver_vetana_informativa(respuesta["2"] || "No se pudo consultar las solicitudes.");
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
				GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
			} finally {
				verCerrarEfectoCargando("");
			}
		}
	});
}

function solicitarEliminacionRegistro(tabla, pkColumna, pkValor, resumen, motivo, callbackExito) {
	if (!tabla || !pkColumna || !pkValor || !motivo) {
		ver_vetana_informativa("FALTAN DATOS PARA SOLICITAR LA ELIMINACION");
		return;
	}

	obtener_datos_user();

	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "solicitar");
	datos.append("tabla_nombre", tabla);
	datos.append("registro_pk_columna", pkColumna);
	datos.append("registro_pk_valor", pkValor);
	datos.append("registro_resumen", resumen || "");
	datos.append("motivo", motivo);

	verCerrarEfectoCargando("1");
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmSolicitudEliminadoInforme.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
			ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					ver_vetana_informativa(respuesta["2"] || "Solicitud registrada.", "", "info");
					cargarSolicitudesEliminacionPendientes();
					if (typeof callbackExito == "function") {
						callbackExito(respuesta);
					}
				} else {
					ver_vetana_informativa(respuesta["2"] || "No se pudo registrar la solicitud.");
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
				GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
			} finally {
				verCerrarEfectoCargando("");
			}
		}
	});
}

function toggleDropdownSolicitudEliminado() {
	var dropdown = document.getElementById("dropdownSolicitudEliminadoPendiente");
	if (!dropdown) { return; }

	if (dropdown.style.display == "block") {
		dropdown.style.display = "none";
		return;
	}

	dropdown.style.display = "block";
	cargarSolicitudesEliminacionPendientes();
}

function cerrarDropdownSolicitudEliminado() {
	var dropdown = document.getElementById("dropdownSolicitudEliminadoPendiente");
	if (dropdown) {
		dropdown.style.display = "none";
	}
}

function cargarSolicitudesEliminacionPendientes() {
	if (!document.getElementById("listaSolicitudEliminadoPendiente")) { return; }

	obtener_datos_user();

	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "pendientes");

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmSolicitudEliminadoInforme.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		success: function (responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					var total = parseInt(respuesta["3"] || "0");
					document.getElementById("listaSolicitudEliminadoPendiente").innerHTML = respuesta["2"];
					document.getElementById("badgeSolicitudEliminadoPendiente").innerHTML = total;
					document.getElementById("badgeSolicitudEliminadoPendiente").style.display = total > 0 ? "flex" : "none";
					document.getElementById("tituloSolicitudEliminadoPendiente").innerHTML = total == 1 ? "1 solicitud pendiente" : total + " solicitudes pendientes";
				}
			} catch (error) {
				GuardarArchivosLog("Error solicitudes eliminacion pendientes: " + error + " \r\n Consola: " + responseText);
			}
		}
	});
}

function abrirInformeSolicitudEliminadoDesdeNotificacion(idSolicitud) {
	cerrarDropdownSolicitudEliminado();
	abrirInformeSolicitudEliminado();

	var estado = document.getElementById("inptSolicitudEliminadoEstado");
	var buscar = document.getElementById("inptSolicitudEliminadoBuscar");
	if (estado) { estado.value = "pendiente"; }
	if (buscar) { buscar.value = idSolicitud || ""; }

	buscarInformeSolicitudEliminado();
}

function abrirVentanaEvaluarSolicitudEliminado(idSolicitud) {
	if (!idSolicitud) { return; }
	cerrarDropdownSolicitudEliminado();
	obtener_datos_user();

	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "detalle");
	datos.append("id_solicitud_eliminado", idSolicitud);

	verCerrarEfectoCargando("1");
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmSolicitudEliminadoInforme.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
			ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					cargarVentanaEvaluarSolicitudEliminado(respuesta["2"]);
				} else {
					ver_vetana_informativa(respuesta["2"] || "No se pudo consultar la solicitud.");
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
				GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
			} finally {
				verCerrarEfectoCargando("");
			}
		}
	});
}

function cargarVentanaEvaluarSolicitudEliminado(solicitud) {
	document.getElementById("inptEvaluarSolicitudEliminadoId").value = valorSeguroSolicitudEliminado(solicitud.id_solicitud_eliminado);
	document.getElementById("inptEvaluarSolicitudEliminadoCodigo").value = "#" + valorSeguroSolicitudEliminado(solicitud.id_solicitud_eliminado);
	document.getElementById("inptEvaluarSolicitudEliminadoEstado").value = valorSeguroSolicitudEliminado(solicitud.estado);
	document.getElementById("inptEvaluarSolicitudEliminadoUsuario").value = valorSeguroSolicitudEliminado(solicitud.usuario_solicitud || ("Usuario " + solicitud.id_usuario_solicitud));
	document.getElementById("inptEvaluarSolicitudEliminadoFecha").value = valorSeguroSolicitudEliminado(solicitud.fecha_solicitud);
	document.getElementById("inptEvaluarSolicitudEliminadoTabla").value = valorSeguroSolicitudEliminado(solicitud.tabla_nombre);
	document.getElementById("inptEvaluarSolicitudEliminadoRegistro").value = valorSeguroSolicitudEliminado(solicitud.registro_pk_columna) + ": " + valorSeguroSolicitudEliminado(solicitud.registro_pk_valor);
	document.getElementById("txtEvaluarSolicitudEliminadoResumen").value = valorSeguroSolicitudEliminado(solicitud.registro_resumen);
	document.getElementById("txtEvaluarSolicitudEliminadoMotivo").value = valorSeguroSolicitudEliminado(solicitud.motivo);
	document.getElementById("txtEvaluarSolicitudEliminadoObservacion").value = "";

	var alerta = document.getElementById("lblEvaluarSolicitudEliminadoAlerta");
	var botonAprobar = document.getElementById("btnAprobarSolicitudEliminado");
	var pendiente = solicitud.estado == "pendiente";
	var puedeAprobar = solicitud.puede_aprobar == "1";

	botonAprobar.disabled = (!pendiente || !puedeAprobar);
	botonAprobar.style.opacity = botonAprobar.disabled ? "0.45" : "";
	alerta.style.display = puedeAprobar ? "none" : "";
	alerta.innerHTML = puedeAprobar ? "" : "Esta solicitud no tiene tabla, columna o codigo del registro. Se puede rechazar, pero no aprobar.";

	document.getElementById("divEvaluarSolicitudEliminado").style.display = "";
	document.getElementById("tdEfectoEvaluarSolicitudEliminado").className = "magictime slideDownReturn";
}

function cerrarVentanaEvaluarSolicitudEliminado() {
	document.getElementById("tdEfectoEvaluarSolicitudEliminado").className = "magictime vanishOut";
	$("div[id=divEvaluarSolicitudEliminado]").fadeOut(250);
}

function resolverSolicitudEliminado(decision) {
	var idSolicitud = document.getElementById("inptEvaluarSolicitudEliminadoId").value;
	var observacion = document.getElementById("txtEvaluarSolicitudEliminadoObservacion").value;
	if (!idSolicitud) {
		ver_vetana_informativa("FALTO SELECCIONAR LA SOLICITUD");
		return;
	}

	var mensaje = decision == "aprobar"
		? "¿Aprobar la solicitud e inactivar el registro?"
		: "¿Rechazar la solicitud?";
	if (!confirm(mensaje)) {
		return;
	}

	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", decision);
	datos.append("id_solicitud_eliminado", idSolicitud);
	datos.append("observacion", observacion);

	verCerrarEfectoCargando("1");
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmSolicitudEliminadoInforme.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
			ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					ver_vetana_informativa(respuesta["2"] || "Solicitud actualizada.", "", "info");
					cerrarVentanaEvaluarSolicitudEliminado();
					cargarSolicitudesEliminacionPendientes();
					buscarInformeSolicitudEliminado();
				} else {
					ver_vetana_informativa(respuesta["2"] || "No se pudo actualizar la solicitud.");
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
				GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
			} finally {
				verCerrarEfectoCargando("");
			}
		}
	});
}

document.addEventListener("click", function (event) {
	var contenedor = document.getElementById("contenedorSolicitudEliminadoPendiente");
	var dropdown = document.getElementById("dropdownSolicitudEliminadoPendiente");
	if (!contenedor || !dropdown || dropdown.style.display != "block") { return; }
	if (!contenedor.contains(event.target)) {
		cerrarDropdownSolicitudEliminado();
	}
});

window.addEventListener("load", function () {
	cargarSolicitudesEliminacionPendientes();
	setInterval(cargarSolicitudesEliminacionPendientes, 60000);
});
