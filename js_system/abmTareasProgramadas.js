var idAbmTareaProgramada = "";
var codUsuarioVistaTareas = "";
var origenVistaTareas = "listado";

function verCerrarAdministradorTareas(d) {
	codUsuarioVistaTareas = typeof userid != "undefined" ? userid : "";
	verCerrarVentanaTareasProgramadas(d == "1" || d === true, false, "");
}

function verCerrarVentanaTareasProgramadas(mostrar, abm, ventanaOrigen) {
    if (mostrar) {
		if (codUsuarioVistaTareas == "" && typeof userid != "undefined") {
			codUsuarioVistaTareas = userid;
		}
        document.getElementById('divAbmAdministradorTareas').style.display= "";
        if (abm) {
			document.getElementById('divListTareasProgramadas').style.display= "none";
            document.getElementById('divAbmTareasProgramadas').style.display= "";
        } else {
			document.getElementById('divAbmTareasProgramadas').style.display= "none";
            document.getElementById('divListTareasProgramadas').style.display= "";
			buscarVistaTareas();
        }
    } else {
        if (abm) {
            document.getElementById('divAbmTareasProgramadas').style.display= "none";
			document.getElementById('divListTareasProgramadas').style.display= "";
			buscarVistaTareas();
        } else {
			verCerrarFiltrosTareasProgramadas(false);
            document.getElementById('divAbmAdministradorTareas').style.display= "none";
        }
        
        if (typeof ventanaAnterior != "undefined" && ventanaAnterior[ventanaAnterior.length - 1]) {
            document.getElementById(ventanaAnterior[ventanaAnterior.length - 1]).style.display = "";
        }
    }

    if (ventanaOrigen !== undefined && ventanaOrigen != "") {
        ventanaAnterior.push(ventanaOrigen);
    }
}

function buscarVistaTareas() {
	const id = document.getElementById('inptBuscarIdTareasProgramadas').value;
	const nombre = document.getElementById('inptBuscarNombreTareasProgramadas').value;
	const estado = document.getElementById('inptBuscarEstadoTareasProgramadas').value;
	const fecha_inicio = document.getElementById('inptBuscarFechaInicioTareasProgramadas').value;
	const fecha_fin = document.getElementById('inptBuscarFechaFinTareasProgramadas').value;

	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "buscarVistaInforme");
	datos.append("id", id);
	datos.append("nombre", nombre);
	datos.append("estado", estado);
	datos.append("fecha_inicio", fecha_inicio);
	datos.append("fecha_fin", fecha_fin);
	datos.append("cod_usuarioFK", idAbmUsuario);
	datos.append("limite", 0);

	verCerrarEfectoCargando("1");
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmTareasProgramadas.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		xhr: function () {
			var xhr = new window.XMLHttpRequest();
			xhr.upload.addEventListener("progress", function (evt) {
				var kb = ((evt.loaded * 1) / 1000).toFixed(1);
				if (kb == "0.0") {
					kb = 0.1;
				}
				cargarConectividad("enviado", kb, "0");
			}, false);
			xhr.addEventListener("progress", function (evt) {
				var kb = ((evt.loaded * 1) / 1000).toFixed(1);
				if (kb == "0.0") {
					kb = 0.1;
				}
				cargarConectividad("recibido", "0", kb);
			}, false);
			return xhr;
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
		},
		success: function (responseText) {
			verCerrarEfectoCargando("");
			Respuesta = responseText;
			console.log(Respuesta);
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
                    document.getElementById('table_vista_tareas_programadas').innerHTML= datos["2"];
					
				} else {
					Respuesta = respuestaJqueryAjax(Respuesta);
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
				var titulo = "Error: " + error + " \r\n Consola: " + responseText;
				GuardarArchivosLog(titulo);
			}
		}
	});
}

function verCerrarFiltrosTareasProgramadas(mostrar) {
	var overlay = document.getElementById("overlayFiltrosTareasProgramadas");
	if (overlay == null) {
		return;
	}
	if (mostrar) {
		overlay.style.display = "";
	} else {
		overlay.style.display = "none";
	}
}

function limpiarFiltrosTareasProgramadas() {
    document.getElementById('inptBuscarIdTareasProgramadas').value = "";
	document.getElementById('inptBuscarNombreTareasProgramadas').value = "";
	document.getElementById('inptBuscarEstadoTareasProgramadas').value = "";
	document.getElementById('inptBuscarFechaInicioTareasProgramadas').value = "";
	document.getElementById('inptBuscarFechaFinTareasProgramadas').value = "";
}

function abmTarea(id, nombre, hora, estado, fecha_realizado) {
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "nuevo/editar");
	datos.append("id", id);
	datos.append("nombre", nombre);
	datos.append("hora", hora);
	datos.append("estado", estado);
	datos.append("fecha_realizado", fecha_realizado.replace("T", " "));
	datos.append("cod_usuarioFK", idAbmUsuario);

	verCerrarEfectoCargando("1");
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmTareasProgramadas.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		xhr: function () {
			var xhr = new window.XMLHttpRequest();
			xhr.upload.addEventListener("progress", function (evt) {
				var kb = ((evt.loaded * 1) / 1000).toFixed(1);
				if (kb == "0.0") {
					kb = 0.1;
				}
				cargarConectividad("enviado", kb, "0");
			}, false);
			xhr.addEventListener("progress", function (evt) {
				var kb = ((evt.loaded * 1) / 1000).toFixed(1);
				if (kb == "0.0") {
					kb = 0.1;
				}
				cargarConectividad("recibido", "0", kb);
			}, false);
			return xhr;
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
		},
		success: function (responseText) {
			verCerrarEfectoCargando("");
			Respuesta = responseText;
			console.log(Respuesta);
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					limpiarCamposTareasProgramadas();
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...");
					idAbmTareaProgramada = "";
					if (origenVistaTareas == "listado") {
						verCerrarVentanaTareasProgramadas(false, true);
					}
				} else {
					Respuesta = respuestaJqueryAjax(Respuesta);
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
				var titulo = "Error: " + error + " \r\n Consola: " + responseText;
				GuardarArchivosLog(titulo);
			}
		}
	});
}

function obtenerDatosTareas(datostr) {
	if (datostr == null) {
		return;
	}

	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = "";
	});
	datostr.className = "tableRegistroSelec";

	idAbmTareaProgramada = $(datostr).children('td[id="td_id"]').html() || "";
	document.getElementById('inptNombreTareasProgramadas').value= $(datostr).children('td[id="td_datos_1"]').html() || "";
	document.getElementById('inptHoraTareasProgramadas').value= $(datostr).children('td[id="td_datos_2"]').html() || "";
	document.getElementById('inptEstadoTareasProgramadas').value= $(datostr).children('td[id="td_datos_3"]').html() || "pendiente";
	document.getElementById('inptFechaRealizadoTareasProgramadas').value= $(datostr).children('td[id="td_datos_4"]').html().replace(" ", "T").substring(0, 16);

	var botonGuardar = document.getElementById('btnAbmTareasProgramadas').value="Editar datos";
}

function guardarDatosTareaProgramada() {
	const nombre = document.getElementById('inptNombreTareasProgramadas').value;
	const hora = document.getElementById('inptHoraTareasProgramadas').value;
	const estado = document.getElementById('inptEstadoTareasProgramadas').value;
	const fecha_realizado = document.getElementById('inptFechaRealizadoTareasProgramadas').value;

	if (nombre == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE");
		return false;
	}

	abmTarea(idAbmTareaProgramada, nombre, hora, estado, fecha_realizado, "nuevo/editar");
}

function limpiarCamposTareasProgramadas() {
	idAbmTareaProgramada = "";
	document.getElementById('inptNombreTareasProgramadas').value= "";
	document.getElementById('inptHoraTareasProgramadas').value= "";
	document.getElementById('inptEstadoTareasProgramadas').value= "pendiente";
	document.getElementById('inptFechaRealizadoTareasProgramadas').value= "pendiente";

    document.getElementById('btnAbmTareasProgramadas').value = "Guardar datos";
}
