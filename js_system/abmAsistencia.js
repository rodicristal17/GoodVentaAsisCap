var cod_asistencia= "";
var asistenciaUsuarioVerificada = false;
var asistenciaUsuarioTieneEntradaHoy = false;
var asistenciaUsuarioRegistrosHoy = [];
var asistenciaUsuarioUltimoEstadoReal = null;

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
	if (typeof actualizarProgresoJornadaTopbarUsuario == "function") {
		setTimeout(actualizarProgresoJornadaTopbarUsuario, 0);
	}
	if (typeof evaluarRecordatorioEntradaPendiente == "function") {
		setTimeout(evaluarRecordatorioEntradaPendiente, 80);
	}

	if (jornada) {
		var clasesEstado = [
			"perfil-widget__jornada--abierta",
			"perfil-widget__jornada--cerrada",
			"perfil-widget__jornada--procesando",
			"perfil-widget__jornada--error"
		];
		for (var i = 0; i < clasesEstado.length; i++) {
			jornada.classList.remove(clasesEstado[i]);
		}
		jornada.classList.add("perfil-widget__jornada--" + estado);
	}

	if (estado === "abierta") {
		if (boton) {
			boton.value = "Marcar salida";
			boton.title = "Registrar salida de la jornada";
		}
		if (textoEstado) {
			var horaEntrada = normalizarHoraAsistencia(detalle);
			textoEstado.innerHTML = horaEntrada ? "En jornada" : "Jornada abierta";
			if (estadoVisual) { estadoVisual.title = horaEntrada ? "Entrada registrada " + horaEntrada : ""; }
		}
		return;
	}

	if (estado === "cerrada") {
		if (boton) {
			boton.value = "Marcar entrada";
			boton.title = "Registrar entrada de la jornada";
		}
		var textoCerrado = asistenciaUsuarioTieneEntradaHoy ? "Salida registrada" : "Sin entrada registrada";
		var tituloCerrado = "";
		if (detalle && typeof detalle == "object") {
			textoCerrado = detalle.estado || textoCerrado;
			tituloCerrado = detalle.ultima_marcacion || detalle.detalle || "";
		}
		if (estadoVisual) { estadoVisual.title = tituloCerrado; }
		if (textoEstado) textoEstado.innerHTML = textoCerrado;
		return;
	}

	if (estado === "procesando") {
		if (textoEstado) textoEstado.innerHTML = detalle || "Procesando";
		return;
	}

	if (estado === "error") {
		if (textoEstado) textoEstado.innerHTML = detalle || "No verificado";
	}
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
    // Obtiene la hora actual
    const fechaActual = new Date();
    const hora= fechaActual.toLocaleTimeString('es-PY', { hour12: false });
	
	// Deshabilita temporalmente el boton para marcar asistencia
	document.getElementById("btnRegistrarAsistencia").disabled = true;
	if (cod_asistencia === "" && typeof pausarRecordatorioEntradaPendienteMarcacion == "function") {
		pausarRecordatorioEntradaPendienteMarcacion();
	}
	actualizarEstadoVisualAsistencia("procesando", cod_asistencia === "" ? "Registrando entrada" : "Registrando salida");

    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	
    if (cod_asistencia === "") {
        // Registrar entrada
        datos.append("accion", "nuevo");
    } else {
        // Registrar salida
        datos.append("accion", "registrarSalida");
		datos.append("cod_local", cod_localFKUSer);
        datos.append("cod_asistencia", cod_asistencia);
    }

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
			actualizarEstadoVisualAsistencia("error", "No se registro");
			if (typeof liberarRecordatorioEntradaPendienteMarcacion == "function") {
				liberarRecordatorioEntradaPendienteMarcacion();
			}
			document.getElementById("btnRegistrarAsistencia").disabled = false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				console.log(datos);
				if (Respuesta == "exito") {
					// Se evalua si es entrada y si esta en hora para pedir justificacion
					if (!cod_asistencia) {
						if (datos['llegada_tardia'] == 1) {
							cod_asistencia= datos['cod_asistencia'];
							asistenciaUsuarioVerificada = true;
							asistenciaUsuarioTieneEntradaHoy = true;
							asistenciaUsuarioRegistrosHoy = [{
								cod_asistencia: datos['cod_asistencia'],
								fecha: formatearFechaLocalAsistencia(new Date()),
								hora_entrada: datos['hora_entrada'],
								hora_salida: ""
							}];
							actualizarEstadoVisualAsistencia("abierta", datos['hora_entrada']);
							mostrarJustificacionAsistencia('entrada_tardia', datos);
						} else {
							//obtenerAsistenciaUsuario();
							location.reload();
							document.getElementById("btnRegistrarAsistencia").disabled = false;
						}
					} else {
						if (datos['ip_valida'] == 1) {
							//obtenerAsistenciaUsuario();
							location.reload();
							document.getElementById("btnRegistrarAsistencia").disabled = false;
						} else {
							actualizarEstadoVisualAsistencia("procesando", "Justificar salida");
							mostrarJustificacionAsistencia('salida_ubicacion', datos);
						}
					}
				} else {
					let mensaje= datos["2"];
					mensaje += (datos["3"] !== undefined) ? "<br><br>"+datos["3"] : "";
					ver_vetana_informativa(mensaje);
					if (Respuesta == 'red') {
						if (typeof liberarRecordatorioEntradaPendienteMarcacion == "function") {
							liberarRecordatorioEntradaPendienteMarcacion();
						}
						obtenerAsistenciaUsuario();
					} else {
						actualizarEstadoVisualAsistencia("error", "No se registro");
						if (typeof liberarRecordatorioEntradaPendienteMarcacion == "function") {
							liberarRecordatorioEntradaPendienteMarcacion();
						}
						document.getElementById("btnRegistrarAsistencia").disabled = false;
					}
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo);
				actualizarEstadoVisualAsistencia("error", "Error de estado");
				if (typeof liberarRecordatorioEntradaPendienteMarcacion == "function") {
					liberarRecordatorioEntradaPendienteMarcacion();
				}
				document.getElementById("btnRegistrarAsistencia").disabled = false;
			} finally {
                verCerrarEfectoCargando("");
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
	datos.append("cod_asistencia", cod_asistencia);

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
					//obtenerAsistenciaUsuario();
					ver_vetana_informativa("Datos guardados correctamente");
					setTimeout(() => {
						location.reload();
					}, 1000);
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

function obtenerAsistenciaUsuario() {
	// Bloquea el boton para evitar multiples marcas
	document.getElementById("btnRegistrarAsistencia").disabled = true;
	asistenciaUsuarioVerificada = false;
	asistenciaUsuarioTieneEntradaHoy = false;
	asistenciaUsuarioRegistrosHoy = [];
	asistenciaUsuarioUltimoEstadoReal = null;
	actualizarEstadoVisualAsistencia("procesando", "Verificando jornada");
	let fechaActual= new Date();
	
	obtener_datos_user()
	let datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", "buscar");
	datos.append("cod_usuario", userid);
	fechaActual = formatearFechaLocalAsistencia(fechaActual);
	datos.append("fecha_desde", fechaActual);
	datos.append("fecha_hasta", fechaActual);
	datos.append("limite", 20);

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
			actualizarEstadoVisualAsistencia("error", "No verificado");
			document.getElementById("btnRegistrarAsistencia").disabled = false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					let registros= Array.isArray(datos["registros"]) ? datos["registros"] : [];
					asistenciaUsuarioRegistrosHoy = registros;
					asistenciaUsuarioUltimoEstadoReal = (typeof calcularAvanceRealJornada == "function")
						? calcularAvanceRealJornada(fechaActual, asistenciaUsuarioRegistrosHoy, (typeof obtenerJornadaProgramadaHoyTopbarUsuario == "function" ? obtenerJornadaProgramadaHoyTopbarUsuario() : null), new Date())
						: null;
					tablaRegistros= document.getElementById("tableRegistroEntrada");
					document.getElementById("btnRegistrarAsistencia").disabled = false;
					asistenciaUsuarioVerificada = true;
					asistenciaUsuarioTieneEntradaHoy = false;
					var registroAbierto = null;
					for (var i = 0; i < registros.length; i++) {
						if (registros[i]['hora_entrada']) {
							asistenciaUsuarioTieneEntradaHoy = true;
						}
						if (!registroAbierto && registros[i]['hora_entrada'] && !registros[i]['hora_salida']) {
							registroAbierto = registros[i];
						}
					}
					if (registroAbierto) {
						cod_asistencia= registroAbierto['cod_asistencia'];
						actualizarEstadoVisualAsistencia("abierta", registroAbierto['hora_entrada']);
						tablaRegistros.style.display= '';
						let fila= $(tablaRegistros).children('tbody');
						fila= $(fila).children('tr')[0];
						fila.innerHTML = "<td>"+registroAbierto['fecha'].substring(0, 10)+"</td>"
      						+"<td>"+registroAbierto['hora_entrada']+"</td>";
					} else {
						cod_asistencia= "";
						tablaRegistros.style.display= 'none';
						actualizarEstadoVisualAsistencia("cerrada", asistenciaUsuarioUltimoEstadoReal);
					}
				} else {
					asistenciaUsuarioVerificada = false;
					actualizarEstadoVisualAsistencia("error", "No verificado");
					document.getElementById("btnRegistrarAsistencia").disabled = false;
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				asistenciaUsuarioVerificada = false;
				actualizarEstadoVisualAsistencia("error", "No verificado");
			} finally {
                verCerrarEfectoCargando("");
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
