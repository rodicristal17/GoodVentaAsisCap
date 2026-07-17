var cod_asistencia= "";
var asistenciaUsuarioVerificada = false;
var asistenciaUsuarioTieneEntradaHoy = false;
var asistenciaUsuarioRegistrosHoy = [];
var asistenciaUsuarioUltimoEstadoReal = null;
var asistenciaUsuarioJustificacionPendiente = "";
var cod_asistenciaJustificacion = "";
var asistenciaUsuarioSolicitudEnCurso = false;
var asistenciaUsuarioConfirmacionTimer = null;

function formatearFechaLocalAsistencia(fecha) {
	var anio = fecha.getFullYear();
	var mes = String(fecha.getMonth() + 1).padStart(2, '0');
	var dia = String(fecha.getDate()).padStart(2, '0');
	return anio + "-" + mes + "-" + dia;
}

function normalizarHoraAsistencia(hora) {
	return hora ? String(hora).substring(0, 5) : "";
}

function obtenerContenedorJornadaAsistencia(elemento) {
	while (elemento && elemento !== document) {
		if (elemento.classList && elemento.classList.contains("perfil-widget__jornada")) {
			return elemento;
		}
		elemento = elemento.parentNode;
	}
	return null;
}

function actualizarEstadoVisualAsistencia(estado, detalle) {
	var boton = document.getElementById("btnRegistrarAsistencia");
	var estadoVisual = document.getElementById("estadoAsistenciaUsuario");
	var textoEstado = document.getElementById("textoEstadoAsistencia");
	var jornada = obtenerContenedorJornadaAsistencia(estadoVisual || boton);
	var presenciaActiva = estado === "abierta"
		|| estado === "registrando-salida"
		|| (estado === "error" && String(cod_asistencia || "") !== "");
	var pendiente = detalle && typeof detalle === "object" ? !!detalle.justificacion_pendiente : false;

	if (jornada) {
		var clasesEstado = [
			"perfil-widget__jornada--abierta",
			"perfil-widget__jornada--cerrada",
			"perfil-widget__jornada--procesando",
			"perfil-widget__jornada--registrando-entrada",
			"perfil-widget__jornada--registrando-salida",
			"perfil-widget__jornada--verificando",
			"perfil-widget__jornada--justificacion-pendiente",
			"perfil-widget__jornada--error"
		];
		for (var i = 0; i < clasesEstado.length; i++) {
			jornada.classList.remove(clasesEstado[i]);
		}
		jornada.classList.add(presenciaActiva ? "perfil-widget__jornada--abierta" : "perfil-widget__jornada--cerrada");
		if (estado !== "abierta" && estado !== "cerrada") {
			jornada.classList.add("perfil-widget__jornada--" + estado);
		}
		if (pendiente) {
			jornada.classList.add("perfil-widget__jornada--justificacion-pendiente");
		}
	}

	if (estado === "abierta") {
		if (boton) {
			boton.value = "Marcar salida";
			boton.title = "Registrar salida de la jornada";
			boton.setAttribute("aria-busy", "false");
		}
		if (textoEstado) {
			var horaEntrada = normalizarHoraAsistencia(detalle && typeof detalle === "object" ? detalle.hora_entrada : detalle);
			textoEstado.innerHTML = pendiente ? "Justificacion pendiente" : (horaEntrada ? "En jornada" : "Jornada abierta");
			if (estadoVisual) { estadoVisual.title = horaEntrada ? "Entrada registrada " + horaEntrada : ""; }
		}
	} else if (estado === "cerrada") {
		if (boton) {
			boton.value = "Marcar entrada";
			boton.title = "Registrar entrada de la jornada";
			boton.setAttribute("aria-busy", "false");
		}
		var textoCerrado = pendiente ? "Justificacion pendiente" : (asistenciaUsuarioTieneEntradaHoy ? "Salida registrada" : "Sin entrada registrada");
		var tituloCerrado = "";
		if (detalle && typeof detalle == "object") {
			textoCerrado = pendiente ? "Justificacion pendiente" : (detalle.estado || textoCerrado);
			tituloCerrado = detalle.ultima_marcacion || detalle.detalle || "";
		}
		if (estadoVisual) { estadoVisual.title = tituloCerrado; }
		if (textoEstado) textoEstado.innerHTML = textoCerrado;
	} else if (estado === "registrando-entrada" || estado === "registrando-salida" || estado === "verificando" || estado === "procesando") {
		if (boton) {
			boton.value = estado === "registrando-salida" ? "Registrando salida..." : (estado === "registrando-entrada" ? "Registrando entrada..." : boton.value);
			boton.setAttribute("aria-busy", "true");
		}
		if (textoEstado) textoEstado.innerHTML = detalle || (estado === "verificando" ? "Verificando jornada" : "Procesando");
	} else if (estado === "error") {
		if (textoEstado) textoEstado.innerHTML = detalle || "No verificado";
	}

	if (typeof actualizarProgresoJornadaTopbarUsuario == "function") {
		setTimeout(actualizarProgresoJornadaTopbarUsuario, 0);
	}
	if (typeof evaluarRecordatorioEntradaPendiente == "function") {
		setTimeout(evaluarRecordatorioEntradaPendiente, 80);
	}
}

function mostrarConfirmacionBotonAsistencia(tipo, hora, alFinalizar) {
	var boton = document.getElementById("btnRegistrarAsistencia");
	if (!boton) {
		if (typeof alFinalizar === "function") alFinalizar();
		return;
	}
	if (asistenciaUsuarioConfirmacionTimer) {
		clearTimeout(asistenciaUsuarioConfirmacionTimer);
	}
	boton.disabled = true;
	boton.setAttribute("aria-busy", "false");
	boton.classList.add("perfil-widget__btn--confirmado");
	boton.value = tipo === "entrada" ? "\u2713 Entrada registrada" : "\u2713 Salida registrada";
	boton.title = (tipo === "entrada" ? "Entrada registrada " : "Salida registrada ") + normalizarHoraAsistencia(hora);
	asistenciaUsuarioConfirmacionTimer = setTimeout(function () {
		boton.classList.remove("perfil-widget__btn--confirmado");
		boton.disabled = false;
		if (typeof alFinalizar === "function") alFinalizar();
	}, 850);
}

function notificarCambioAsistenciaOtrasPestanas() {
	try {
		localStorage.setItem("clinidentAsistenciaCambio_" + String(userid || "0"), String(Date.now()));
	} catch (error) {
	}
}

if (window.addEventListener) {
	window.addEventListener("storage", function (evento) {
		if (evento && evento.key === "clinidentAsistenciaCambio_" + String(userid || "0") && !asistenciaUsuarioSolicitudEnCurso) {
			obtenerAsistenciaUsuario({ silencioso: true });
		}
	});
	document.addEventListener("visibilitychange", function () {
		if (!document.hidden && asistenciaUsuarioVerificada && !asistenciaUsuarioSolicitudEnCurso) {
			obtenerAsistenciaUsuario({ silencioso: true });
		}
	});
}

function mostrarJustificacionAsistencia(tipo, datos) {
	var divJustificacion = document.getElementById('divJustificacionAsistencia');
	var titulo = document.getElementById('tituloJustificacionAsistencia');
	var descripcion = document.getElementById('descripcionJustificacionAsistencia');
	var nombreUsuario = document.getElementById('inptNombreUsuarioJustificacionAsistencia');
	var horaEntrada = document.getElementById('inptHoraEntradaJustificacionAsistencia');
	var horaRegistrada = document.getElementById('inptHoraRegistradaJustificacionAsistencia');
	var filaHoraEntrada = document.getElementById('filaHoraEntradaJustificacionAsistencia');
	var filaHoraRegistrada = document.getElementById('filaHoraRegistradaJustificacionAsistencia');
	var etiquetaHoraRegistrada = document.getElementById('lblHoraRegistradaJustificacionAsistencia');
	var detalleUbicacion = document.getElementById('detalleUbicacionJustificacionAsistencia');
	var justificacion = document.getElementById('inptJustificacionJustificacionAsistencia');

	if (!divJustificacion) return;
	cod_asistenciaJustificacion = datos && datos.cod_asistencia ? String(datos.cod_asistencia) : String(cod_asistencia || "");
	asistenciaUsuarioJustificacionPendiente = tipo;

	divJustificacion.style.display = "";
	if (nombreUsuario) nombreUsuario.value = document.getElementById('nombrePerfilUsuario').innerHTML;
	if (justificacion) {
		justificacion.value = "";
		setTimeout(function () { justificacion.focus(); }, 150);
	}

	if (tipo === 'salida_ubicacion') {
		if (titulo) titulo.innerHTML = "Justificar salida en otra ubicacion";
		if (descripcion) {
			descripcion.innerHTML = "La salida se marco desde una ubicacion diferente a la registrada en la entrada. Indique el motivo para completar el registro.";
		}
		if (filaHoraEntrada) filaHoraEntrada.style.display = "";
		if (filaHoraRegistrada) filaHoraRegistrada.style.display = "";
		if (etiquetaHoraRegistrada) etiquetaHoraRegistrada.innerHTML = "Hora de salida:";
		if (horaEntrada) horaEntrada.value = datos && datos.hora_entrada ? datos.hora_entrada : "";
		if (horaRegistrada) horaRegistrada.value = datos && datos.hora_salida ? datos.hora_salida : "";
		if (detalleUbicacion) {
			var ipEntrada = datos && datos.ip_entrada ? datos.ip_entrada : "No registrada";
			var ipSalida = datos && datos.ip_salida ? datos.ip_salida : "No registrada";
			detalleUbicacion.innerHTML = "<strong>Entrada:</strong> " + ipEntrada + "<br><strong>Salida:</strong> " + ipSalida;
			detalleUbicacion.style.display = "";
		}
		return;
	}

	if (titulo) titulo.innerHTML = "Justificar entrada tardia";
	if (descripcion) descripcion.innerHTML = "La entrada se registro fuera del horario asignado. Indique el motivo para completar el registro.";
	if (filaHoraEntrada) filaHoraEntrada.style.display = "";
	if (filaHoraRegistrada) filaHoraRegistrada.style.display = "";
	if (etiquetaHoraRegistrada) etiquetaHoraRegistrada.innerHTML = "Hora registrada:";
	if (horaEntrada) horaEntrada.value = datos && datos.hora_entrada_usuario ? datos.hora_entrada_usuario : "";
	if (horaRegistrada) horaRegistrada.value = datos && datos.hora_entrada ? datos.hora_entrada : "";
	if (detalleUbicacion) {
		detalleUbicacion.innerHTML = "";
		detalleUbicacion.style.display = "none";
	}
}

function registrarAsistencia() {
	if (asistenciaUsuarioSolicitudEnCurso) {
		return;
	}
	var esEntrada = cod_asistencia === "";
	var asistenciaSolicitada = cod_asistencia;
	var boton = document.getElementById("btnRegistrarAsistencia");
	asistenciaUsuarioSolicitudEnCurso = true;
	if (boton) boton.disabled = true;
	if (esEntrada && typeof pausarRecordatorioEntradaPendienteMarcacion == "function") {
		pausarRecordatorioEntradaPendienteMarcacion();
	}
	actualizarEstadoVisualAsistencia(esEntrada ? "registrando-entrada" : "registrando-salida", esEntrada ? "Registrando entrada" : "Registrando salida");

	obtener_datos_user();
	var formulario = new FormData();
	formulario.append("useru", userid);
	formulario.append("passu", passuser);
	formulario.append("navegador", navegador);
	formulario.append("accion", esEntrada ? "nuevo" : "registrarSalida");
	if (!esEntrada) {
		formulario.append("cod_local", cod_localFKUSer);
		formulario.append("cod_asistencia", asistenciaSolicitada);
	}

	$.ajax({
		data: formulario,
		url: "/GoodVentaAsisCap/php_system/abmAsistencia.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus) {
			asistenciaUsuarioSolicitudEnCurso = false;
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
			ver_vetana_informativa("Error de conexion al registrar asistencia");
			actualizarEstadoVisualAsistencia("error", "No se registro");
			if (boton) boton.disabled = false;
			if (typeof liberarRecordatorioEntradaPendienteMarcacion == "function") liberarRecordatorioEntradaPendienteMarcacion();
			obtenerAsistenciaUsuario({ silencioso: true });
		},
		success: function (responseText) {
			asistenciaUsuarioSolicitudEnCurso = false;
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] !== "exito") {
					ver_vetana_informativa(respuesta["2"] || "No se pudo registrar la marcacion.");
					if (boton) boton.disabled = false;
					if (typeof liberarRecordatorioEntradaPendienteMarcacion == "function") liberarRecordatorioEntradaPendienteMarcacion();
					obtenerAsistenciaUsuario({ silencioso: true });
					return;
				}

				asistenciaUsuarioVerificada = true;
				if (esEntrada) {
					cod_asistencia = String(respuesta.cod_asistencia || "");
					asistenciaUsuarioTieneEntradaHoy = true;
					asistenciaUsuarioJustificacionPendiente = respuesta.justificacion_pendiente == 1 ? "entrada_tardia" : "";
					var registroEntrada = {
						cod_asistencia: cod_asistencia,
						fecha: respuesta.fecha || (formatearFechaLocalAsistencia(new Date()) + " " + respuesta.hora_entrada),
						hora_entrada: respuesta.hora_entrada,
						hora_salida: "",
						fecha_salida: ""
					};
					asistenciaUsuarioRegistrosHoy.unshift(registroEntrada);
					actualizarEstadoVisualAsistencia("abierta", {
						hora_entrada: respuesta.hora_entrada,
						justificacion_pendiente: asistenciaUsuarioJustificacionPendiente !== ""
					});
					mostrarConfirmacionBotonAsistencia("entrada", respuesta.hora_entrada, function () {
						actualizarEstadoVisualAsistencia("abierta", {
							hora_entrada: respuesta.hora_entrada,
							justificacion_pendiente: asistenciaUsuarioJustificacionPendiente !== ""
						});
					});
					if (asistenciaUsuarioJustificacionPendiente !== "") {
						mostrarJustificacionAsistencia("entrada_tardia", respuesta);
					}
				} else {
					var asistenciaCerrada = String(respuesta.cod_asistencia || asistenciaSolicitada);
					for (var i = 0; i < asistenciaUsuarioRegistrosHoy.length; i++) {
						if (String(asistenciaUsuarioRegistrosHoy[i].cod_asistencia || "") === asistenciaCerrada) {
							asistenciaUsuarioRegistrosHoy[i].hora_salida = respuesta.hora_salida || "";
							asistenciaUsuarioRegistrosHoy[i].fecha_salida = respuesta.fecha_salida || "";
						}
					}
					cod_asistencia = "";
					cod_asistenciaJustificacion = respuesta.justificacion_pendiente == 1 ? asistenciaCerrada : "";
					asistenciaUsuarioJustificacionPendiente = respuesta.justificacion_pendiente == 1 ? "salida_ubicacion" : "";
					actualizarEstadoVisualAsistencia("cerrada", {
						estado: asistenciaUsuarioJustificacionPendiente !== "" ? "Justificacion pendiente" : "Salida registrada",
						ultima_marcacion: "Salida " + normalizarHoraAsistencia(respuesta.hora_salida),
						justificacion_pendiente: asistenciaUsuarioJustificacionPendiente !== ""
					});
					mostrarConfirmacionBotonAsistencia("salida", respuesta.hora_salida, function () {
						actualizarEstadoVisualAsistencia("cerrada", {
							estado: asistenciaUsuarioJustificacionPendiente !== "" ? "Justificacion pendiente" : "Salida registrada",
							ultima_marcacion: "Salida " + normalizarHoraAsistencia(respuesta.hora_salida),
							justificacion_pendiente: asistenciaUsuarioJustificacionPendiente !== ""
						});
					});
					if (asistenciaUsuarioJustificacionPendiente !== "") {
						mostrarJustificacionAsistencia("salida_ubicacion", respuesta);
					}
				}
				notificarCambioAsistenciaOtrasPestanas();
				if (typeof liberarRecordatorioEntradaPendienteMarcacion == "function") liberarRecordatorioEntradaPendienteMarcacion();
				setTimeout(function () { obtenerAsistenciaUsuario({ silencioso: true }); }, 1100);
			} catch (error) {
				if (boton) boton.disabled = false;
				if (typeof liberarRecordatorioEntradaPendienteMarcacion == "function") liberarRecordatorioEntradaPendienteMarcacion();
				actualizarEstadoVisualAsistencia("error", "Error de estado");
				GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
				obtenerAsistenciaUsuario({ silencioso: true });
			}
		}
	});
}

function justificarAsistencia() {
	const inptJustificacionJustificacionAsistencia= document.getElementById('inptJustificacionJustificacionAsistencia').value
	if (!inptJustificacionJustificacionAsistencia) {
		ver_vetana_informativa("Faltan Datos", "Debe ingresar una justificacion", "error");
		return false;
	}

	obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", "registrarJustificacion");
	datos.append("justificacion", inptJustificacionJustificacionAsistencia);
	datos.append("cod_asistencia", cod_asistenciaJustificacion || cod_asistencia);

	verCerrarEfectoCargando("1")
    var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAsistencia.php",
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
            ver_vetana_informativa("Error de conexion al registrar asistencia");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta);
			verCerrarEfectoCargando("")
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				console.log(datos);
				if (Respuesta == "exito") {
					asistenciaUsuarioJustificacionPendiente = "";
					cod_asistenciaJustificacion = "";
					var modalJustificacion=document.getElementById("divJustificacionAsistencia");
					if(modalJustificacion) modalJustificacion.style.display="none";
					ver_vetana_informativa("Justificacion guardada correctamente");
					notificarCambioAsistenciaOtrasPestanas();
					obtenerAsistenciaUsuario({ silencioso: true });
				} else {
					let mensaje= datos["2"];
					mensaje += (datos["3"] !== undefined) ? "<br><br>"+datos["3"] : "";
					ver_vetana_informativa(mensaje);
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
				document.getElementById("btnRegistrarAsistencia").disabled = false;
            }
		}
	});
}

var registrocargadoinformeAsistencia=0;
var totalregistroinformeAsistencia=0;
var controldebusquedadInformeAsistencia= true;

function formatearFechaInputInformeAsistencia(fecha) {
	var anio = fecha.getFullYear();
	var mes = String(fecha.getMonth() + 1).padStart(2, '0');
	var dia = String(fecha.getDate()).padStart(2, '0');
	return anio + "-" + mes + "-" + dia;
}

function obtenerFechaInputInformeAsistencia(valor) {
	if (!valor || !/^\d{4}-\d{2}-\d{2}$/.test(valor)) { return null; }
	var partes = valor.split("-");
	return new Date(parseInt(partes[0], 10), parseInt(partes[1], 10) - 1, parseInt(partes[2], 10));
}

function formatearFechaVistaInformeAsistencia(fecha) {
	if (!fecha) { return ""; }
	return String(fecha.getDate()).padStart(2, '0') + "/" + String(fecha.getMonth() + 1).padStart(2, '0') + "/" + fecha.getFullYear();
}

function obtenerNombreDiaInformeAsistencia(fecha) {
	var dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
	return dias[fecha.getDay()];
}

function obtenerNombreMesInformeAsistencia(fecha) {
	var meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
	return meses[fecha.getMonth()];
}

function obtenerDiferenciaDiasInformeAsistencia(fecha) {
	var hoy = new Date();
	var base = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
	var comparada = new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate());
	return Math.round((comparada.getTime() - base.getTime()) / 86400000);
}

function describirFechaInformeAsistencia(fecha) {
	var diferencia = obtenerDiferenciaDiasInformeAsistencia(fecha);
	var dia = obtenerNombreDiaInformeAsistencia(fecha);
	var fechaVista = formatearFechaVistaInformeAsistencia(fecha);
	if (diferencia === 0) { return "Hoy " + dia + " " + fechaVista; }
	if (diferencia === -1) { return "Ayer " + dia + " " + fechaVista; }
	if (diferencia === 1) { return "Mañana " + dia + " " + fechaVista; }
	return dia + " " + fechaVista;
}

function rangoEsMesCompletoInformeAsistencia(desde, hasta) {
	if (!desde || !hasta) { return false; }
	var inicioMes = new Date(desde.getFullYear(), desde.getMonth(), 1);
	var finMes = new Date(desde.getFullYear(), desde.getMonth() + 1, 0);
	return desde.getTime() === inicioMes.getTime()
		&& hasta.getTime() === finMes.getTime()
		&& desde.getMonth() === hasta.getMonth()
		&& desde.getFullYear() === hasta.getFullYear();
}

function describirPeriodoInformeAsistencia(desdeValor, hastaValor) {
	var desde = obtenerFechaInputInformeAsistencia(desdeValor);
	var hasta = obtenerFechaInputInformeAsistencia(hastaValor);
	if (!desde || !hasta) { return "Sin periodo definido"; }
	if (desde.getTime() > hasta.getTime()) {
		var temporal = desde;
		desde = hasta;
		hasta = temporal;
	}
	if (desde.getTime() === hasta.getTime()) {
		return describirFechaInformeAsistencia(desde);
	}
	var texto = formatearFechaVistaInformeAsistencia(desde) + " al " + formatearFechaVistaInformeAsistencia(hasta);
	if (rangoEsMesCompletoInformeAsistencia(desde, hasta)) {
		texto += " · " + obtenerNombreMesInformeAsistencia(desde) + " " + desde.getFullYear();
	}
	return texto;
}

function escaparHtmlInformeAsistencia(texto) {
	return String(texto || "").replace(/[&<>"']/g, function (caracter) {
		return {
			"&": "&amp;",
			"<": "&lt;",
			">": "&gt;",
			'"': "&quot;",
			"'": "&#039;"
		}[caracter];
	});
}

function obtenerTextoSelectInformeAsistencia(id, textoTodos) {
	var select = document.getElementById(id);
	if (!select || select.value === "") { return textoTodos; }
	return select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : textoTodos;
}

function actualizarResumenFiltrosInformeAsistencia() {
	var resumen = document.getElementById("resumenFiltrosInformeAsistencia");
	if (!resumen) { return; }
	var fechaDesde = document.getElementById("inptBuscarInformeAsistenciaF1");
	var fechaHasta = document.getElementById("inptBuscaInformeAsistenciaF2");
	var funcionario = document.getElementById("inptInformeAsistencia2");
	var periodo = describirPeriodoInformeAsistencia(fechaDesde ? fechaDesde.value : "", fechaHasta ? fechaHasta.value : "");
	var local = obtenerTextoSelectInformeAsistencia("inptLocalInformeAsistencia", "Todos");
	var estado = obtenerTextoSelectInformeAsistencia("inptEstadoInformeAsistencia", "Todos");
	var funcionarioTexto = funcionario && funcionario.value.trim() !== "" ? funcionario.value.trim() : "Todos";
	resumen.innerHTML = "<strong>Filtros aplicados</strong>"
		+ "<span>Periodo: " + escaparHtmlInformeAsistencia(periodo) + "</span>"
		+ "<span>Local: " + escaparHtmlInformeAsistencia(local) + "</span>"
		+ "<span>Funcionario: " + escaparHtmlInformeAsistencia(funcionarioTexto) + "</span>"
		+ "<span>Estado: " + escaparHtmlInformeAsistencia(estado) + "</span>";
}

function asegurarPeriodoInformeAsistencia(forzarHoy) {
	var fechaDesde = document.getElementById("inptBuscarInformeAsistenciaF1");
	var fechaHasta = document.getElementById("inptBuscaInformeAsistenciaF2");
	var fechaUnica = document.getElementById("inptInformeAsistencia3");

	if (!fechaDesde || !fechaHasta) { return; }
	if (forzarHoy === true) {
		var hoyForzado = formatearFechaInputInformeAsistencia(new Date());
		fechaDesde.value = hoyForzado;
		fechaHasta.value = hoyForzado;
		if (fechaUnica) { fechaUnica.value = ""; }
		return;
	}
	if (fechaUnica && fechaUnica.value != "") {
		fechaDesde.value = fechaUnica.value;
		fechaHasta.value = fechaUnica.value;
		return;
	}

	if (fechaDesde.value == "" && fechaHasta.value == "") {
		var hoy = new Date();
		var hoyTexto = formatearFechaInputInformeAsistencia(hoy);
		fechaDesde.value = hoyTexto;
		fechaHasta.value = hoyTexto;
		return;
	}

	if (fechaDesde.value == "") {
		fechaDesde.value = fechaHasta.value;
	}
	if (fechaHasta.value == "") {
		fechaHasta.value = fechaDesde.value;
	}
}

function aplicarPeriodoRapidoInformeAsistencia(tipo) {
	var fechaDesde = document.getElementById("inptBuscarInformeAsistenciaF1");
	var fechaHasta = document.getElementById("inptBuscaInformeAsistenciaF2");
	var fechaUnica = document.getElementById("inptInformeAsistencia3");
	if (!fechaDesde || !fechaHasta) { return; }

	var hoy = new Date();
	var desde = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
	var hasta = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());

	if (tipo === "ayer") {
		desde.setDate(desde.getDate() - 1);
		hasta = new Date(desde.getFullYear(), desde.getMonth(), desde.getDate());
	} else if (tipo === "semana") {
		var diaSemana = hoy.getDay();
		var diferenciaLunes = diaSemana === 0 ? -6 : 1 - diaSemana;
		desde.setDate(hoy.getDate() + diferenciaLunes);
	} else if (tipo === "mes") {
		desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
	} else if (tipo === "mes_anterior") {
		desde = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
		hasta = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
	}

	fechaDesde.value = formatearFechaInputInformeAsistencia(desde);
	fechaHasta.value = formatearFechaInputInformeAsistencia(hasta);
	if (fechaUnica) { fechaUnica.value = ""; }
	actualizarResumenFiltrosInformeAsistencia();
	obtenerVistaInformeAsistencia();
}

function limpiarFiltrosInformeAsistencia() {
	var campos = [
		"inptInformeAsistencia1",
		"inptInformeAsistencia2",
		"inptInformeAsistencia3",
		"inptEstadoInformeAsistencia"
	];
	for (var i = 0; i < campos.length; i++) {
		var campo = document.getElementById(campos[i]);
		if (campo) { campo.value = ""; }
	}
	var local = document.getElementById("inptLocalInformeAsistencia");
	if (local) { local.value = ""; }
	var fechaDesde = document.getElementById("inptBuscarInformeAsistenciaF1");
	var fechaHasta = document.getElementById("inptBuscaInformeAsistenciaF2");
	if (fechaDesde) { fechaDesde.value = ""; }
	if (fechaHasta) { fechaHasta.value = ""; }
	asegurarPeriodoInformeAsistencia(true);
	actualizarResumenFiltrosInformeAsistencia();
	obtenerVistaInformeAsistencia();
}

function toggleDetalleAsistenciaEmpleado(boton) {
	var tarjeta = boton ? boton.parentNode : null;
	if (!tarjeta) { return; }
	tarjeta.classList.toggle("asistencia-empleado-card--abierta");
}

function obtenerVistaInformeAsistencia() {
	if(controlacceso("VERLISTADOASISTENCIA","accion")==false){ return;}
	asegurarPeriodoInformeAsistencia();
	actualizarResumenFiltrosInformeAsistencia();

	// Obtiene los datos de filtros
	let fecha_desde= document.getElementById("inptBuscarInformeAsistenciaF1").value;
	let fecha_hasta= document.getElementById("inptBuscaInformeAsistenciaF2").value;
	const usuario= document.getElementById("inptInformeAsistencia2").value;
	const local= document.getElementById('inptLocalInformeAsistencia').value;
	const estado_incidencia= document.getElementById('inptEstadoInformeAsistencia') ? document.getElementById('inptEstadoInformeAsistencia').value : "";
	const fecha= document.getElementById('inptInformeAsistencia3').value;
	const cod_asistencia_filtro= document.getElementById('inptInformeAsistencia1').value;

	// Prioriza la fecha individual de la tabla
	if (fecha != "") {
		fecha_desde = fecha;
		fecha_hasta = fecha;
	}

	obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", "buscarVistaInforme");
	datos.append("fecha_desde", fecha_desde);
	datos.append("fecha_hasta", fecha_hasta);
	datos.append("nombre_usuario", usuario);
	datos.append("cod_local", local);
	datos.append("estado_incidencia", estado_incidencia);
	datos.append("cod_asistencia", cod_asistencia_filtro);
	datos.append("limite", 10);

	verCerrarEfectoCargando("1")
    var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAsistencia.php",
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
			document.getElementById("table_InformeAsistencia").innerHTML= '';
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					document.getElementById("table_InformeAsistencia").innerHTML= datos["2"];
					document.getElementById("inptTotalRegistoInformeAsistencia").value= datos["5"];
					document.getElementById("inptTotalMinutosInformeAsistencia").value= datos["6"];
					totalregistroinformeAsistencia= parseInt(datos["5"]);
					registrocargadoinformeAsistencia= parseInt(datos["4"]);

					// Controla el progreso de la busqueda
					if(totalregistroinformeAsistencia>registrocargadoinformeAsistencia){
						document.getElementById("divProgressInformeAsistencia").style.backgroundColor='';
						
						controldebusquedadInformeAsistencia=true;
						var porce=((registrocargadoinformeAsistencia*100)/totalregistroinformeAsistencia).toFixed(0)
						document.getElementById('tbProcessInformeAsistencia').style.display= ""
						document.getElementById("divProgressInformeAsistencia").style.width=porce+"%"
						//document.getElementById("table_InformeAsistencia").innerHTML += "<div id='table_mas_InformeAsistencia'></div>"
						obtenermasVistaInformeAsistencia();
					 }else{
						document.getElementById('tbProcessInformeAsistencia').style.display= "none";
						controldebusquedadInformeAsistencia=false
					 }
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

function obtenermasVistaInformeAsistencia() {
	asegurarPeriodoInformeAsistencia();
	actualizarResumenFiltrosInformeAsistencia();

	// Obtiene los datos de filtros
	let fecha_desde= document.getElementById("inptBuscarInformeAsistenciaF1").value;
	let fecha_hasta= document.getElementById("inptBuscaInformeAsistenciaF2").value;
	const usuario= document.getElementById("inptInformeAsistencia2").value;
	const local= document.getElementById('inptLocalInformeAsistencia').value;
	const estado_incidencia= document.getElementById('inptEstadoInformeAsistencia') ? document.getElementById('inptEstadoInformeAsistencia').value : "";
	const fecha= document.getElementById('inptInformeAsistencia3').value;
	const cod_asistencia_filtro= document.getElementById('inptInformeAsistencia1').value;

	// Prioriza la fecha individual de la tabla
	if (fecha != "") {
		fecha_desde = fecha;
		fecha_hasta = fecha;
	}

	obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", "buscarMasVistaInforme");
	datos.append("fecha_desde", fecha_desde);
	datos.append("fecha_hasta", fecha_hasta);
	datos.append("nombre_usuario", usuario);
	datos.append("cod_local", local);
	datos.append("estado_incidencia", estado_incidencia);
	datos.append("cod_asistencia", cod_asistencia_filtro);
	datos.append("limite", "10 OFFSET "+registrocargadoinformeAsistencia);

	verCerrarEfectoCargando("1")
    var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAsistencia.php",
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
					document.getElementById("table_InformeAsistencia").innerHTML += datos["2"];
					document.getElementById("inptTotalMinutosInformeAsistencia").value= datos["6"];
					registrocargadoinformeAsistencia += parseInt(datos["4"]);

					// Controla el progreso de la busqueda
					if(totalregistroinformeAsistencia>registrocargadoinformeAsistencia){
						var porce=((registrocargadoinformeAsistencia*100)/totalregistroinformeAsistencia).toFixed(0)
						document.getElementById("divProgressInformeAsistencia").style.width=porce+"%"
						//document.getElementById("table_InformeAsistencia").innerHTML += "<div id='table_mas_InformeAsistencia' style='width: 100%;'></div>"
						if(controldebusquedadInformeAsistencia) {obtenermasVistaInformeAsistencia();}
					 }else{
						controldebusquedadInformeAsistencia=false;
						document.getElementById("divProgressInformeAsistencia").style.display="none"
						document.getElementById('tbProcessInformeAsistencia').style.display= "none";
					 }
				}
			} catch (error) {
				controldebusquedadInformeAsistencia=false;
				document.getElementById("divProgressInformeAsistencia").style.backgroundColor='#ff5722'

                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function obtenerAsistenciaUsuario(opciones) {
	opciones = opciones || {};
	var silencioso = opciones.silencioso === true;
	if (asistenciaUsuarioSolicitudEnCurso) return;
	var boton = document.getElementById("btnRegistrarAsistencia");
	var fechaActual = formatearFechaLocalAsistencia(new Date());
	if (!silencioso) {
		if (boton) boton.disabled = true;
		asistenciaUsuarioVerificada = false;
		actualizarEstadoVisualAsistencia("verificando", "Verificando jornada");
	}

	obtener_datos_user();
	var formulario = new FormData();
	formulario.append("useru", userid);
	formulario.append("passu", passuser);
	formulario.append("navegador", navegador);
	formulario.append("accion", "buscarEstadoUsuario");

	$.ajax({
		data: formulario,
		url: "/GoodVentaAsisCap/php_system/abmAsistencia.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus) {
			if (!silencioso) {
				manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
				actualizarEstadoVisualAsistencia("error", "No verificado");
				if (boton) boton.disabled = false;
			}
		},
		success: function (responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] !== "exito") throw new Error("Estado de asistencia no disponible");
				var registros = Array.isArray(respuesta.registros) ? respuesta.registros : [];
				var registroAbierto = respuesta.registro_abierto && respuesta.registro_abierto.cod_asistencia ? respuesta.registro_abierto : null;
				asistenciaUsuarioRegistrosHoy = registros;
				asistenciaUsuarioVerificada = true;
				asistenciaUsuarioTieneEntradaHoy = false;
				for (var i = 0; i < registros.length; i++) {
					if (registros[i].hora_entrada && String(registros[i].fecha || "").substring(0, 10) === fechaActual) {
						asistenciaUsuarioTieneEntradaHoy = true;
					}
				}
				asistenciaUsuarioUltimoEstadoReal = (typeof calcularAvanceRealJornada == "function")
					? calcularAvanceRealJornada(fechaActual, registros, (typeof obtenerJornadaProgramadaHoyTopbarUsuario == "function" ? obtenerJornadaProgramadaHoyTopbarUsuario() : null), new Date())
					: null;
				asistenciaUsuarioJustificacionPendiente = respuesta.justificacion_pendiente || "";
				var codJustificacionPendiente = String(respuesta.cod_asistencia_justificacion || "");
				var tablaRegistros = document.getElementById("tableRegistroEntrada");
				if (registroAbierto) {
					cod_asistencia = String(registroAbierto.cod_asistencia);
					cod_asistenciaJustificacion = asistenciaUsuarioJustificacionPendiente !== "" ? codJustificacionPendiente : "";
					actualizarEstadoVisualAsistencia("abierta", {
						hora_entrada: registroAbierto.hora_entrada,
						justificacion_pendiente: asistenciaUsuarioJustificacionPendiente !== ""
					});
					if (tablaRegistros) {
						tablaRegistros.style.display = "";
						var fila = $(tablaRegistros).children("tbody").children("tr")[0];
						if (fila) fila.innerHTML = "<td>" + String(registroAbierto.fecha || "").substring(0, 10) + "</td><td>" + registroAbierto.hora_entrada + "</td>";
					}
				} else {
					cod_asistencia = "";
					cod_asistenciaJustificacion = asistenciaUsuarioJustificacionPendiente !== "" ? codJustificacionPendiente : "";
					if (tablaRegistros) tablaRegistros.style.display = "none";
					var estadoCerrado = asistenciaUsuarioUltimoEstadoReal || {};
					if (registros[0] && registros[0].hora_salida) {
						estadoCerrado.estado = "Salida registrada";
						estadoCerrado.ultima_marcacion = "Salida " + normalizarHoraAsistencia(registros[0].hora_salida);
					}
					estadoCerrado.justificacion_pendiente = asistenciaUsuarioJustificacionPendiente !== "";
					actualizarEstadoVisualAsistencia("cerrada", estadoCerrado);
				}
				if (boton) boton.disabled = false;
			} catch (error) {
				if (!silencioso) {
					GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
					asistenciaUsuarioVerificada = false;
					actualizarEstadoVisualAsistencia("error", "No verificado");
					if (boton) boton.disabled = false;
				}
			}
		}
	});
}

function verCerrarInformeAsistencia(mostrar) {
	if(controlacceso("VERLISTADOASISTENCIA","accion")==false){ return;}
	if (mostrar) {
		document.getElementById("divInformeAsistencia").style.display = "";
		asegurarPeriodoInformeAsistencia();
		actualizarResumenFiltrosInformeAsistencia();
		var tablaInforme = document.getElementById("table_InformeAsistencia");
		if (tablaInforme && tablaInforme.innerHTML.trim() === "") {
			obtenerVistaInformeAsistencia();
		}
	} else {
		document.getElementById("divInformeAsistencia").style.display = "none";
	}
}

function minimizarInformeAsistencia() {
	document.getElementById('divMinimizadoInformeAsistencia').style.display = '';
	document.getElementById("divInformeAsistencia").style.display = "none";
}

function cancelarInformeAsistencia(){
	controldebusquedadInformeAsistencia=false
	document.getElementById("divProgressInformeAsistencia").style.backgroundColor='#ff5722'
}
