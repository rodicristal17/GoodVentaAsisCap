var vistaPresupuestoOrigen= ""
var totalPrecioPresupuesto =0
function verCerrarAbmDetallesPresupuesto(mostrar, historial){
	vistaPresupuestoOrigen= "historial";
	if(mostrar){
		if (historial) {
			document.getElementById("divAbmDetallesPresupuesto").style.display=""
            document.getElementById('divListPresupuesto').style.display= "";
            document.getElementById("divAbmDetallesPresupuesto2").style.display="none";
        } else {
			if(controlacceso("VERHISTORIALPRESUPUESTO","accion")==false){return;}
			buscarvistaPresupuesto();
            document.getElementById('divListPresupuesto').style.display= "none";
            document.getElementById("divAbmDetallesPresupuesto2").style.display="";
            // document.getElementById("tdEfectoAbmDetallePresupuesto").className="magictime slideLeftReturn"
            
            document.getElementById("inptEntregaPresupuesto").value=0
            document.getElementById("inptProductoPresupuesto").value=document.getElementById('inptNombreProducto').value
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
function abmPresupuesto(cod_presupuesto, cant_cuotas, cod_clienteFK) {
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"accion": "abmPresupuesto",
		"id": cod_presupuesto,
		"cant_cuotas": cant_cuotas,
		"cod_clienteFK": cod_clienteFK,
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
			ver_vetana_informativa("LO SENTIMOS, HA OCURRIDO UN ERROR", "", "error");
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				idabmPresupuesto = datos[2];
				ver_vetana_informativa("Datos guardados exitosamente", "", "info");
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ", responseText, "error")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
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
	} else {
		buscador = document.getElementById('inptProductoPresupuesto').value
		cod_productoFK= document.getElementById('inptCodigoPresupuesto').value;
		document.getElementById("table_vista_producto_Presupuesto").innerHTML = paginacargando
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

	if (vistaPresupuestoOrigen == "doctor") {
		document.getElementById('inptCodigoPresupuestoDoc').value = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoPresupuestoDoc').value = $(datostr).children('td[id="td_datos_1"]').html();
		//document.getElementById('inpTSeleccCostoPresupuestoDoc').innerHTML = $(datostr).children('td[id="td_datos_11"]').html();
		document.getElementById('inptCantidadPresupuestoDoc').value = "1";
		document.getElementById('inptPrecioPresupuestoDoc').value = $(datostr).children('td[id="td_datos_4"]').html();
		document.getElementById('inptCantidadPresupuestoDoc').focus();
	} else {
		document.getElementById('inptCodigoPresupuesto').value = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoPresupuesto').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inpTSeleccCostoPresupuesto').innerHTML = $(datostr).children('td[id="td_datos_11"]').html();
		document.getElementById('inptCantidadPresupuesto').value = "1";
		document.getElementById('inptPrecioPresupuesto').value = $(datostr).children('td[id="td_datos_4"]').html();
		document.getElementById('inptCantidadPresupuesto').focus();
		calcularTotalPresupuesto(document.getElementById('inptPrecioPresupuesto'))
	}
}


function calcularTotalPresupuesto(datos) {
	separadordemiles(datos)
	calcular_total_Presupuesto()
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

function limpirarAddPresupuesto(){
	
document.getElementById('inptCodigoPresupuesto').value = ""
document.getElementById('inptProductoPresupuesto').value = ""
document.getElementById('inptPrecioPresupuesto').value = ""
document.getElementById('inpTSeleccCostoPresupuesto').value = ""
document.getElementById('inptCantidadPresupuesto').value = ""
document.getElementById('inptTotalPresupuesto').value = ""
	
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

					if (vistaOrigen == "doctor") {
						document.getElementById('inptCodigoPresupuestoDoc').value = datos["5"];
						document.getElementById('inptProductoPresupuestoDoc').value = datos["3"];
						document.getElementById('inptCantidadPresupuestoDoc').value = "1";
					} else {
						document.getElementById('inptCodigoPresupuesto').value = datos["5"];
						document.getElementById('inptProductoPresupuesto').value = datos["3"];
						// document.getElementById('inpTSeleccCostoPresupuesto').innerHTML = datos["2"];
						document.getElementById('inptCantidadPresupuesto').value = "1";
						document.getElementById('inptPrecioPresupuesto').value = datos["4"];
						document.getElementById('inptCantidadPresupuesto').focus();
						calcularTotalPresupuesto(document.getElementById('inptPrecioPresupuesto'))					
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



function eliminarFila(btn) {
  if (confirm("¿Seguro que deseas eliminar este producto del presupuesto?")) {
    let fila = btn.closest("tr");
	const es_prioritario= fila.querySelector('#td_datos_12')?.textContent.trim();
	let tabla= fila.parentElement.parentElement;
	const idDetalle= tabla.id.substring(15);
	tabla= tabla.parentElement;
	const esTablaPrioritaria = tabla.id.includes("prioritario");
	
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idDetalle": idDetalle,
		"solo_eliminar_prioritario": esTablaPrioritaria,
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
						if (tablaPrioritaria) {
							tablaPrioritaria.remove();
						}
						if (tablaPrincipal) {
							const campoPrioritario = tablaPrincipal.querySelector('#td_datos_12');
							if (campoPrioritario) {
								campoPrioritario.textContent = "0";
							}
						}
					} else {
						if (tablaPrincipal) {
							tablaPrincipal.remove();
						}
						if ((es_prioritario === "1") && tablaPrioritaria) {
							tablaPrioritaria.remove();
						}
					}

					if (vistaPresupuestoOrigen != "doctor") {
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
  let total = 0;
  let total_prioritario = 0;

  filas.forEach(fila => {
    let valor = fila.cells[4]?.innerText.replace(/\./g,"").replace(",",".") || "0";
    total += parseFloat(valor) || 0;
	if (fila.cells[11]?.innerText == "1") {
		total_prioritario += parseFloat(valor) || 0;
	}
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
 
function abmDetallesPresupuesto(cod_presupuestoFK, cod_productoFK, precio, cantidad, codigo_ficticio_presupuesto, nombre_producto, total_presupuesto,es_precio_contado, es_prioritario) {
	obtener_datos_user();
	const esPrioritario = es_prioritario === true || es_prioritario === 1 || es_prioritario === "1";
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"accion": "abmDetallesPresupuesto",
		"cod_presupuestoFK": cod_presupuestoFK,
		"cod_productoFK": cod_productoFK,
		"cantidad": cantidad,
		"precio": precio,
		"es_prioritario": (esPrioritario ? 1 : 0),
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
						+ "<td  id='td_datos_3' style='width:10%;'>" + cantidad + "</td>"
						+ "<td  id='td_datos_4' style='width:15%;"+ (vistaPresupuestoOrigen == 'doctor' ? 'display: none;' : '') +"'>" + separadordemilesnumero(precio) + "</td>"
						+ "<td  id='td_datos_5' style='width:15%;"+ (vistaPresupuestoOrigen == 'doctor' ? 'display: none;' : '') +"'>" + total_presupuesto + "</td>"
						+ "<td  id='td_datos_6' style='display:none'></td>"
						+ "<td  id='td_datos_7' style='display:none'>" + 0 + "</td>"
						+ "<td  id='td_datos_8' style='display:none'>" + 0 + "</td>"
						+ "<td  id='td_datos_9' style='display:none'>" + es_precio_contado + "</td>"
						+ "<td id='td_datos_10' style='display:none'>" + precio + "</td>"
						+ "<td  id='td_datos_11' style='display:none'>" + total_presupuesto + "</td>"
						+ "<td  id='td_datos_12' style='display:none'>" + (esPrioritario ? 1 : 0) + "</td>"
						+ "<td style='display:none' > <button class='btn-eliminar' >❌</button> </td>"
						+ "</tr>"
						+ "</table>"

					if (vistaPresupuestoOrigen == "doctor") {
						document.getElementById("table_vista_producto_presupuestoDetalle_doctor").innerHTML += pagina;
						if (esPrioritario) {
							document.getElementById("table_vista_producto_presupuestoDetalle_prioritario_doctor").innerHTML += pagina;
						}

						$("#table_vista_producto_presupuestoDetalle_doctor tr[name=tdDetallePresupuesto]").each(function (i, elementohtml) {
							var total = $(elementohtml).children('td[id="td_datos_11"]').html();
							total = QuitarSeparadorMilValor(total)
							totalPresupuesto = Number(totalPresupuesto) + Number(total)
						});
					} else {
						document.getElementById("table_vista_producto_presupuestoDetalle").innerHTML += pagina;
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
							if ($(elementohtml).children('td[id="td_datos_12"]').html() === "1") {
								totalPresupuestoPrioritario = Number(totalPresupuestoPrioritario) + Number(total)
							}
						});
	
						document.getElementById("inptTotalPresupuesto2").innerHTML = separadordemilesnumero(totalPresupuesto);
						document.getElementById("inptTOTALPresupuestoFORM").value = separadordemilesnumero(totalPresupuesto);
						document.getElementById("inptTOTALPresupuestoFORMPrioritario").value = separadordemilesnumero(totalPresupuestoPrioritario);
	
						generarTabla();
					}
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
	let inptPrioritarioPresupuesto= "";

	if (vistaPresupuestoOrigen == "doctor") {
		inptCodigoPresupuesto = document.getElementById('inptCodigoPresupuestoDoc').value
		inptProductoPresupuesto = document.getElementById('inptProductoPresupuestoDoc').value
		inptPrecioPresupuesto = document.getElementById('inptPrecioPresupuestoDoc').value
		inptCantidadPresupuesto = document.getElementById('inptCantidadPresupuestoDoc').value
		inptTotalPresupuesto = document.getElementById('inptTotalPresupuestoDoc').value
		inpTSeleccCostoPresupuesto = $("select[id=inpTSeleccCostoPresupuestoDoc]").children(":selected").attr("class")
		inpCuotero = $("select[id=inpTSeleccCostoPresupuestoDoc]").children(":selected").attr("id")
		inpPrecioContado = $("select[id=inpTSeleccCostoPresupuestoDoc]").children(":selected").attr("url")
		inptPrioritarioPresupuesto= document.getElementById('inptPrioritarioPresupuestoDoc').checked;
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
	}
	
	if (inptCodigoPresupuesto != "") {
		if (!idabmPresupuesto) {
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
		}

		// Agrega al presupuesto existente
		abmDetallesPresupuesto(idabmPresupuesto, idFkProducto, inptPrecioPresupuesto.replace('.', ''), inptCantidadPresupuesto, inptCodigoPresupuesto, inptProductoPresupuesto,inptTotalPresupuesto,inpPrecioContado, inptPrioritarioPresupuesto);
	}
	document.getElementById('inptCodigoPresupuestoDoc').value = ""
	document.getElementById('inptProductoPresupuestoDoc').value = ""
	document.getElementById('inptPrecioPresupuestoDoc').value = ""
	//document.getElementById('inpTSeleccCostoPresupuestoDoc').value = ""
	limpirarAddPresupuesto()
	document.getElementById('inptCantidadPresupuestoDoc').value = ""
	document.getElementById('inptTotalPresupuestoDoc').value = ""
	document.getElementById('inptPrioritarioPresupuestoDoc').checked = false;

	document.getElementById('inptCodigoPresupuesto').value = ""
	document.getElementById('inptProductoPresupuesto').value = ""
	document.getElementById('inptPrecioPresupuesto').value = ""
	document.getElementById('inpTSeleccCostoPresupuesto').value = ""
	document.getElementById('inptPrioritarioPresupuesto').checked = false;
	limpirarAddPresupuesto()
	document.getElementById('inptCantidadPresupuesto').value = ""
	document.getElementById('inptTotalPresupuesto').value = ""
}

function limpirarPresupuesto(){
	document.getElementById('inptCodigoPresupuestoDoc').value = ""
	document.getElementById('inptProductoPresupuestoDoc').value = ""
	document.getElementById('inptPrecioPresupuestoDoc').value = ""
	document.getElementById('inptCantidadPresupuestoDoc').value = ""
	document.getElementById('inptTotalPresupuestoDoc').value = ""
	document.getElementById('inptPrioritarioPresupuestoDoc').checked = false;

	document.getElementById('inptCodigoPresupuesto').value = ""
	document.getElementById('inptProductoPresupuesto').value = ""
	document.getElementById('inptPrecioPresupuesto').value = ""
	document.getElementById('inpTSeleccCostoPresupuesto').value = ""
	document.getElementById('inptEntregaPresupuesto').value = "0"
	document.getElementById('inptCantidadPresupuesto').value = ""
	document.getElementById('inptTotalPresupuesto').value = ""
	document.getElementById('inptPrioritarioPresupuesto').checked = false;
	document.getElementById('table_vista_producto_presupuestoDetalle_doctor').innerHTML = ""
	document.getElementById('table_vista_producto_presupuestoDetalle_prioritario_doctor').innerHTML = ""
	document.getElementById('table_vista_producto_Presupuesto_doctor').innerHTML = ""
	
	totalPresupuesto=0;
	totalPresupuestoPrioritario=0;
	document.getElementById('inptTotalPresupuesto2').innerHTML = ""
	document.getElementById('inptTOTALPresupuestoFORM').value = "0"
	document.getElementById('inptTOTALPresupuestoFORMPrioritario').value = "0"
	document.getElementById('table_vista_producto_Presupuesto').innerHTML = ""
	document.getElementById('table_vista_producto_presupuestoDetalle').innerHTML = ""
	document.getElementById('table_vista_producto_presupuestoDetalle_prioritario').innerHTML = ""
	document.getElementById('table_vista_detalles_presupuesto').innerHTML = ""
	document.getElementById('table_vista_detalles_presupuesto_prioritario').innerHTML = ""

	document.getElementById('inptDocumentoClientePresupuesto').value= "";
	document.getElementById('inptNombreClientePresupuesto').value= "";
	idFkCliente= "";

	document.getElementById("divCabeceraImpresiones").innerHTML=""
	document.getElementById("tbTitulosImpresiones").innerHTML=""
	document.getElementById("tbDatosImpresiones").innerHTML=""

	document.getElementById("divPieImpresiones").innerHTML=""
}

totalregistroPresupuesto= 0;
registrocargadoPresupuesto= 0;
controldebusquedadPresupuesto= true;
function buscarvistaPresupuesto() {
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
	datos.append("fecha_inicio", document.getElementById('inptFechaInicioFiltroPresupuesto').value);
	datos.append("fecha_fin", document.getElementById('inptFechaFinFiltroPresupuesto').value);

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
					document.getElementById("table_vista_presupuesto").innerHTML= datos["3"];
					totalregistroPresupuesto= parseInt(datos["5"]);
					registrocargadoPresupuesto= parseInt(datos["4"]);

					// Controla el progreso de la busqueda
					if(totalregistroPresupuesto>registrocargadoPresupuesto){
						document.getElementById("divProgressPresupuesto").style.backgroundColor='';
						
						controldebusquedadPresupuesto=true;
						var porce=((registrocargadoPresupuesto*100)/totalregistroPresupuesto).toFixed(0)
						document.getElementById('tbProcessPresupuesto').style.display= ""
						document.getElementById("divProgressPresupuesto").style.width=porce+"%"
						buscarmasVistaPresupuesto(cod_clienteFK);
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
	document.getElementById('inptFechaInicioFiltroPresupuesto').value = "";
	document.getElementById('inptFechaFinFiltroPresupuesto').value = "";

	buscarvistaPresupuesto();
}

function cancelarListadoPresupuesto() {
	controldebusquedadPresupuesto= false;
	document.getElementById("divProgressPresupuesto").style.backgroundColor='#ff5722'
}

function obtenerDatosPresupuesto(elemento) {
    idabmPresupuesto = $(elemento).children('td[id="td_id"]').html();
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
	verCerrarAbmDetallesPresupuesto(true, false);
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
		document.getElementById("divAbmDetallesPresupuestoDoc").style.display=""
	}else{
		$("div[id=divAbmDetallesPresupuestoDoc]").fadeOut(500);
		vistaPresupuestoOrigen= ""
	}
}
