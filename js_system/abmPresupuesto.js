var vistaPresupuestoOrigen= ""
var totalPrecioPresupuesto =0
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
var presupuestoGuardando = false;
var idAgendaPresupuestoDoctorActiva = "";
var idPacientePresupuestoDoctorActivo = "";

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
				var datos = $.parseJSON(Respuesta);
				datosPresupuesto = datos;
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					idabmPresupuesto = datos[2];
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
				var titulo="Error: "+error+" \r\n Consola: "+responseText
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
			if (vistaPresupuestoOrigen == "doctor") {
				document.getElementById("table_vista_producto_Presupuesto_doctor").innerHTML = ''
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
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				if(datos_buscados!=""){
					if (vistaPresupuestoOrigen == "doctor") {
						document.getElementById("table_vista_producto_Presupuesto_doctor").innerHTML = datos_buscados;
					} else {
						document.getElementById("table_vista_producto_Presupuesto").innerHTML = datos_buscados;
					}
				}else{
					ver_vetana_informativa("PRODUCTO NO ECONTRADO")
					limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", vistaPresupuestoOrigen == "doctor" ? "doctor" : "presupuesto");
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

function obtenerdatosvistaproductodesdePresupuesto(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'

	idFkProducto = $(datostr).children('td[id="td_id"]').html();
	cargarInsumosProductoPresupuesto(idFkProducto, vistaPresupuestoOrigen == "doctor" ? "doctor" : "presupuesto");

	if (vistaPresupuestoOrigen == "doctor") {
		document.getElementById('inptCodigoPresupuestoDoc').value = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoPresupuestoDoc').value = $(datostr).children('td[id="td_datos_1"]').html();
		//document.getElementById('inpTSeleccCostoPresupuestoDoc').innerHTML = $(datostr).children('td[id="td_datos_11"]').html();
		document.getElementById('inptCantidadPresupuestoDoc').value = "1";
		document.getElementById('inptPrecioPresupuestoDoc').value = $(datostr).children('td[id="td_datos_4"]').html();
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
				var datos = $.parseJSON(responseText);
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
	if (vistaOrigen == "doctor") {
		document.getElementById('inptCodigoPresupuestoDoc').value = ""
		document.getElementById('inptProductoPresupuestoDoc').value = ""
		document.getElementById('inptPrecioPresupuestoDoc').value = ""
		document.getElementById('inpTSeleccCostoPresupuestoDoc').value = ""
		document.getElementById('inptCantidadPresupuestoDoc').value = ""
		document.getElementById('inptTotalPresupuestoDoc').value = ""
		limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "doctor");
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
		return
	}
	verCerrarEfectoCargando("1")
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
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			verCerrarEfectoCargando("2")
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				
					 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					datos_buscados = datos["2"];
					
					idFkProducto = datos["2"];
					cargarInsumosProductoPresupuesto(idFkProducto, vistaOrigen == "doctor" ? "doctor" : "presupuesto");

					if (vistaOrigen == "doctor") {
						document.getElementById('inptCodigoPresupuestoDoc').value = datos["5"];
						document.getElementById('inptProductoPresupuestoDoc').value = datos["3"];
						document.getElementById('inptCantidadPresupuestoDoc').value = "1";
						document.getElementById('inptPrecioPresupuestoDoc').value = datos["4"];
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
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
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
  let precio = parseFloat(document.getElementById("inptPrecioPresupuesto").value.replace(/\./g,"")) || 0;
  let cantidad = parseInt(document.getElementById("inptCantidadPresupuesto").value) || 0;
  document.getElementById("inptTotalPresupuesto").value = (precio * cantidad).toLocaleString("es-ES");
}

function actualizarResumenPacientePresupuestoDoc() {
	const campoResumen = document.getElementById("presupuestoDocPacienteResumen");
	if (!campoResumen) {
		return;
	}

	const documento = document.getElementById("inptDocumentoClientePresupuestoDoc")?.value.trim();
	const nombre = document.getElementById("inptNombreClientePresupuestoDoc")?.value.trim();
	campoResumen.textContent = nombre || documento ? [nombre, documento].filter(Boolean).join(" - ") : "Sin seleccionar";
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
		precio_contado: tabla.querySelector("#td_datos_9")?.textContent.trim() || ""
	};
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
	Array.from(origen.children).forEach(function (tablaOriginal) {
		if (!tablaOriginal.matches("table")) {
			return;
		}
		asegurarCampoAlternativoPresupuestoDoc(tablaOriginal);
		const esPrioritario = tablaOriginal.querySelector('#td_datos_12')?.textContent.trim() === "1";
		const esAlternativo = tablaOriginal.querySelector('#td_datos_13')?.textContent.trim() === "1";

		if (!esAlternativo) {
			planA.appendChild(clonarDetallePlanPresupuestoDoc(tablaOriginal));
		}
		if (esPrioritario) {
			planB.appendChild(clonarDetallePlanPresupuestoDoc(tablaOriginal));
		}
	});
	aplicarEstadoPrioritarioDetalleDoc();
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
		boton.textContent = debeMostrar ? "Plegar" : "Tratamientos";
		boton.setAttribute("aria-expanded", debeMostrar ? "true" : "false");
	}
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
	const paso1 = document.getElementById("presupuestoDocLayout");
	const paso2Header = document.getElementById("presupuestoDocPrioritarioHeader");
	const paso2 = document.getElementById("presupuestoDocDetallePanel");
	const etiqueta = document.getElementById("presupuestoDocStepLabel");

	if (!paso1 || !paso2Header || !paso2 || !etiqueta) {
		return;
	}

	if (paso === 2) {
		const tieneTratamientos = document.querySelector("#table_vista_producto_presupuestoDetalle_doctor tr[name=tdDetallePresupuesto]");
		if (!tieneTratamientos) {
			ver_vetana_informativa("Faltan datos", "Primero agrega tratamientos al plan total.", "error");
			return false;
		}
	}

	pasoVistaPresupuestoDoc = paso;
	const mostrarPaso1 = paso === 1;
	paso1.style.display = mostrarPaso1 ? "grid" : "none";
	paso2Header.style.display = mostrarPaso1 ? "none" : "";
	paso2.style.display = mostrarPaso1 ? "none" : "grid";
	etiqueta.textContent = mostrarPaso1
		? "Ventana 1 de 2: Paciente y todos los tratamientos"
		: "Ventana 2 de 2: Division de tratamientos en planes";
	if (paso === 2) {
		alternarTratamientosPresupuestoDoc(false);
		const planA = document.getElementById("table_vista_producto_presupuestoDetalle_plan_a_doctor");
		const planB = document.getElementById("table_vista_producto_presupuestoDetalle_prioritario_doctor");
		if (!planA?.children.length && !planB?.children.length) {
			renderizarPlanesDetallePresupuestoDoc();
		} else {
			aplicarEstadoPrioritarioDetalleDoc();
		}
	} else {
		aplicarEstadoPrioritarioDetalleDoc();
	}
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
		tablaClonada.querySelectorAll("[id]").forEach(function (elemento) {
			elemento.removeAttribute("id");
		});
		destino.appendChild(tablaClonada);
	});

	aplicarEstadoPrioritarioDetalleDoc();
	actualizarResumenPacientePresupuestoDoc();
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
				var respuesta = $.parseJSON(responseText);
				var operacionOk = respuestaJqueryAjax(respuesta["1"]);
				if (operacionOk == true) {
					actualizarCamposPlanDetallePresupuestoDoc(idDetalle, esPrioritario, esAlternativo);
					renderizarPlanesDetallePresupuestoDoc();
					sincronizarResumenDetallePresupuestoDoc();
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
		esAlternativo
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
	if (!idDetalle || (pedirConfirmacion !== false && !confirm("Â¿Seguro que deseas eliminar este tratamiento del plan?"))) {
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
				var datos = $.parseJSON(responseText);
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
				var respuesta = $.parseJSON(responseText);
				var operacionOk = respuestaJqueryAjax(respuesta["1"]);
				if (operacionOk == true) {
					const tablaPlan = obtenerTablaDetallePresupuestoDoc(idDetalle, plan === "b" ? contenedorPlanB : contenedorPlanA);
					if (tablaPlan) {
						tablaPlan.remove();
					}
					actualizarCamposPlanDetallePresupuestoDoc(idDetalle, esPrioritario, esAlternativo);
					if (esDoctor) {
						sincronizarResumenDetallePresupuestoDoc();
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
				var datos = $.parseJSON(responseText);
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

	if (vistaPresupuestoOrigen == "doctor" && pasoVistaPresupuestoDoc === 2) {
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
				var datos = $.parseJSON(Respuesta);
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
}
 
function abmDetallesPresupuesto(cod_presupuestoFK, cod_productoFK, precio, cantidad, codigo_ficticio_presupuesto, nombre_producto, total_presupuesto,es_precio_contado, es_prioritario, es_alternativo) {
	obtener_datos_user();
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
				var datos = $.parseJSON(Respuesta);
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
	if (!tieneTratamientosPresupuestoDoctor()) {
		ver_vetana_informativa("Faltan datos", "Primero agrega tratamientos al plan total.", "error");
		return false;
	}

	if (!idabmPresupuesto) {
		ver_vetana_informativa("Error al guardar", "No se pudo crear el presupuesto. Revise la conexion e intente agregar el tratamiento nuevamente.", "error");
		return false;
	}

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
		abmDetallesPresupuesto(idabmPresupuesto, idFkProducto, inptPrecioPresupuesto.replace('.', ''), inptCantidadPresupuesto, inptCodigoPresupuesto, inptProductoPresupuesto,inptTotalPresupuesto,inpPrecioContado, inptPrioritarioPresupuesto, inptAlternativoPresupuesto);
	} else {
		ver_vetana_informativa("Faltan datos", "Favor seleccionar un producto", "error");
		return false;
	}
}

function limpirarPresupuesto(){
	idabmPresupuesto= "";
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

	document.getElementById("divCabeceraImpresiones").innerHTML=""
	document.getElementById("tbTitulosImpresiones").innerHTML=""
	document.getElementById("tbDatosImpresiones").innerHTML=""

	document.getElementById("divPieImpresiones").innerHTML=""
	actualizarResumenPacientePresupuestoDoc()
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
function buscarvistaPresupuesto() {
	const busquedaPresupuesto = ++busquedaActivaPresupuesto;
	totalregistroPresupuesto= 0;
	registrocargadoPresupuesto= 0;
	controldebusquedadPresupuesto= true;
	idabmPresupuesto = "";
	document.getElementById("inptTotalRegistoPresupuesto").value= 0;
	document.getElementById("table_vista_presupuesto").innerHTML= paginacargando;

	obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", "obtenerPresupuesto");
	datos.append("cod_clienteFK", idFkCliente);
	datos.append("nombre_cedula_cliente", document.getElementById('inptClienteCedulaFiltroPresupuesto').value);
	datos.append("id", document.getElementById('inptIdFiltroPresupuesto').value);
	datos.append("plan_vendido", document.getElementById('inptPlanFiltroPresupuesto').value);
	datos.append("cod_localFK", document.getElementById('inptCodLocalFiltroPresupuesto').value);
	datos.append("nombre_usuario_create", document.getElementById('inptNombreCreadorFiltroPresupuesto').value);
	datos.append("fecha_inicio", document.getElementById('inptFechaInicioFiltroPresupuesto').value);
	datos.append("fecha_fin", document.getElementById('inptFechaFinFiltroPresupuesto').value);
	datos.append("limite", 10);

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
			if (busquedaPresupuesto !== busquedaActivaPresupuesto) {
				return;
			}
			document.getElementById("table_vista_presupuesto").innerHTML= '';
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					document.getElementById("table_vista_presupuesto").innerHTML= datos["3"];
					totalregistroPresupuesto= parseInt(datos["5"]);
					registrocargadoPresupuesto = parseInt(datos["4"]);
					
					document.getElementById("inptTotalRegistoPresupuesto").value= registrocargadoPresupuesto;

					// Controla el progreso de la busqueda
					if(totalregistroPresupuesto>registrocargadoPresupuesto){
						document.getElementById("divProgressPresupuesto").style.backgroundColor='';
						
						controldebusquedadPresupuesto=true;
						var porce=((registrocargadoPresupuesto*100)/totalregistroPresupuesto).toFixed(0)
						//registrocargadoPresupuesto += 10;
						document.getElementById('tbProcessPresupuesto').style.display= ""
						document.getElementById("divProgressPresupuesto").style.width=porce+"%"
						buscarmasVistaPresupuesto(busquedaPresupuesto);
					 }else{
						document.getElementById('tbProcessPresupuesto').style.display= "none";
						controldebusquedadPresupuesto=false
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

function buscarmasVistaPresupuesto(busquedaPresupuesto) {
	if (busquedaPresupuesto !== busquedaActivaPresupuesto) {
		return;
	}
	obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", "obtenerPresupuesto");
	datos.append("cod_clienteFK", idFkCliente);
	datos.append("nombre_cedula_cliente", document.getElementById('inptClienteCedulaFiltroPresupuesto').value);
	datos.append("id", document.getElementById('inptIdFiltroPresupuesto').value);
	datos.append("plan_vendido", document.getElementById('inptPlanFiltroPresupuesto').value);
	datos.append("cod_localFK", document.getElementById('inptCodLocalFiltroPresupuesto').value);
	datos.append("nombre_usuario_create", document.getElementById('inptNombreCreadorFiltroPresupuesto').value);
	datos.append("fecha_inicio", document.getElementById('inptFechaInicioFiltroPresupuesto').value);
	datos.append("fecha_fin", document.getElementById('inptFechaFinFiltroPresupuesto').value);
	datos.append("limite", "10 OFFSET " + registrocargadoPresupuesto);
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
			if (busquedaPresupuesto !== busquedaActivaPresupuesto) {
				return;
			}
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					document.getElementById("table_vista_presupuesto").innerHTML += datos["3"];
					registrocargadoPresupuesto += parseInt(datos["4"]);
					
					document.getElementById("inptTotalRegistoPresupuesto").value= registrocargadoPresupuesto;
					// Controla el progreso de la busqueda
					if(controldebusquedadPresupuesto && totalregistroPresupuesto>registrocargadoPresupuesto){
						document.getElementById("divProgressPresupuesto").style.backgroundColor='';
						
						var porce=((registrocargadoPresupuesto*100)/totalregistroPresupuesto).toFixed(0)
						document.getElementById('tbProcessPresupuesto').style.display= ""
						document.getElementById("divProgressPresupuesto").style.width=porce+"%"
						buscarmasVistaPresupuesto(busquedaPresupuesto);
					 }else{
						document.getElementById('tbProcessPresupuesto').style.display= "none";
						controldebusquedadPresupuesto=false
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

function limpiarFiltroPresupuesto() {
	idFkCliente = "";
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
	document.getElementById("divProgressPresupuesto").style.backgroundColor='#ff5722'
}

function obtenerDatosPresupuesto(elemento) {
	cancelarListadoPresupuesto();
    idabmPresupuesto = $(elemento).children('td[id="td_id"]').html();
	const registroSeleccionadoPresupuesto = document.getElementById('inptRegistroSeleccPresupuesto');
	if (registroSeleccionadoPresupuesto) {
		registroSeleccionadoPresupuesto.value = idabmPresupuesto + " - " + $(elemento).children('td[id="td_datos_4"]').html();
	}
    document.getElementById('inptCodigoPresupuesto').value = $(elemento).children('td[id="td_id"]').html();
    totalPresupuesto= $(elemento).children('td[id="td_datos_7"]').html();
    totalPresupuestoPrioritario= $(elemento).children('td[id="td_datos_8"]').html();
    document.getElementById('inptTotalPresupuesto2').innerHTML = $(elemento).children('td[id="td_datos_7"]').html();
    document.getElementById('inptTOTALPresupuestoFORM').value = $(elemento).children('td[id="td_datos_7"]').html();
    document.getElementById('inptTOTALPresupuestoFORMPrioritario').value = $(elemento).children('td[id="td_datos_8"]').html();

    document.getElementById('table_vista_producto_presupuestoDetalle').innerHTML = "";$(elemento).children('td[id="td_datos_"]').html();
    document.getElementById('table_vista_detalles_presupuesto').innerHTML = "";$(elemento).children('td[id="td_datos_"]').html();
    document.getElementById('table_vista_detalles_presupuesto_prioritario').innerHTML = "";$(elemento).children('td[id="td_datos_"]').html();
	buscarDetallesPresupuesto($(elemento).children('td[id="td_id"]').html());

    document.getElementById('inptDocumentoClientePresupuesto').value= $(elemento).children('td[id="td_datos_5"]').html();
    document.getElementById('inptNombreClientePresupuesto').value= $(elemento).children('td[id="td_datos_4"]').html();
    idFkCliente= $(elemento).children('td[id="td_datos_3"]').html();
    idAbmCliente= $(elemento).children('td[id="td_datos_3"]').html();
	verCerrarAbmDetallesPresupuesto(true, false);
	document.getElementById('inptNombreApellidoCliente').value= $(elemento).children('td[id="td_datos_4"]').html();
	document.getElementById('inptNroDocCliente').value= $(elemento).children('td[id="td_datos_5"]').html();
	document.getElementById('inptNroRucCliente').value= $(elemento).children('td[id="td_datos_13"]').html();
	document.getElementById('inptNrowhatsappCliente').value= $(elemento).children('td[id="td_datos_14"]').html();
	document.getElementById('inptZonaCliente').value= $(elemento).children('td[id="td_datos_11"]').html();
	document.getElementById('inptFechaNacCliente').value= $(elemento).children('td[id="td_datos_"]').html();
	idFKZona= $(elemento).children('td[id="td_datos_12"]').html();
	
	verificarDatosCliente(true);
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
				var datos = $.parseJSON(Respuesta);
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
		limpiarPanelInsumosProductoPresupuesto("Seleccione un tratamiento para ver sus insumos.", "doctor");
		verPasoPresupuestoDoc(1);
		sincronizarResumenDetallePresupuestoDoc();
	}else{
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
	if ((document.getElementById('inptTOTALPresupuestoFORM').value == "0" && document.getElementById('inptTOTALPresupuestoFORMPrioritario').value == "0") ||
		(document.getElementById('inptTOTALPresupuestoFORM').value == "" && document.getElementById('inptTOTALPresupuestoFORMPrioritario').value == "")) {
		ver_vetana_informativa("Faltan datos", "Favor armar el presupuesto primeramente", "error");
		return false;
	}
	const codClientePresupuesto = idFkCliente;
	const documentoClientePresupuesto = document.getElementById('inptDocumentoClientePresupuesto').value.split('-')[0];
	const nombreClientePresupuesto = document.getElementById('inptNombreClientePresupuesto').value;

	limpiarcamposventa("2");

	// Se evalua cual fue el plan seleccionado y si la venta es a credito
	let plan= "";
	if (document.getElementById('inptSelecctPlanPresupuesto').value == "total") {
		plan= document.getElementById('table_vista_producto_presupuestoDetalle');
		tipo_plan= "total";
	} else {
		plan= document.getElementById('table_vista_producto_presupuestoDetalle_prioritario');
		tipo_plan= "prioritario";
	}

	document.getElementById('inptSeleccTipoVenta').value= document.getElementById('inptSelecctModalidadPresupuesto').value;

	// Agrega los datos del cliente
	const inptDocClienteVenta= document.getElementById('inptDocClienteVenta');
	inptDocClienteVenta.value= documentoClientePresupuesto;
	document.getElementById('inptDocClienteVenta2').value= documentoClientePresupuesto;
	document.getElementById('inptClienteVenta').value= nombreClientePresupuesto;
	document.getElementById('inptClienteVenta2').value= nombreClientePresupuesto;
	idFkCliente= codClientePresupuesto;
	if (documentoClientePresupuesto != "") {
		buscarClientePorCiVista(inptDocClienteVenta,'inptDocClienteVenta', 'inptClienteVenta', 'venta');
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
		buscarDescripcionProducto(idFkProducto, totalVenta, nroid);

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
	actualizarResumenPacientePresupuestoDoc();
	cerrarDetalleAgenda();
	cerrarAgendaConsultorios();
}

function confirmarDatosGuardados() {
	if (!validarPresupuestoDoctorListo()) {
		return false;
	}
	if(confirm('Todos los datos son correctos?')){
		limpiarCamposGenerarTratamiento(true);
		verCerrarAbmDetallesPresupuestoDoc(false);
	}
}
