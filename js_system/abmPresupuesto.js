var totalPrecioPresupuesto =0
function verCerrarAbmDetallesPresupuesto(mostrar, historial){
	if(mostrar){
        if (historial) {
            document.getElementById('divListPresupuesto').style.display= "";
            document.getElementById("divAbmDetallesPresupuesto2").style.display="none";
        } else {
			buscarvistaPresupuesto();
            document.getElementById('divListPresupuesto').style.display= "none";
            document.getElementById("divAbmDetallesPresupuesto").style.display=""
            // document.getElementById("tdEfectoAbmDetallePresupuesto").className="magictime slideLeftReturn"
            
            document.getElementById("inptEntregaPresupuesto").value=0
            document.getElementById("inptProductoPresupuesto").value=document.getElementById('inptNombreProducto').value
        }
	}else{
        if (historial) {
            $("div[id=divListPresupuesto]").fadeOut(500);
            document.getElementById("divAbmDetallesPresupuesto2").style.display="";
            document.getElementById('divListPresupuesto').style.display= "none";
        } else {
            // document.getElementById("tdEfectoAbmDetallePresupuesto").className="magictime slideRight"
            $("div[id=divAbmDetallesPresupuesto]").fadeOut(500);
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
	var buscador = document.getElementById('inptProductoPresupuesto').value
	document.getElementById("table_vista_producto_Presupuesto").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"local": cod_localFKUSer,
		"cod_producto": document.getElementById('inptCodigoPresupuesto').value,
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
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_vista_producto_Presupuesto").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_producto_Presupuesto").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				if(datos_buscados!=""){
				document.getElementById("table_vista_producto_Presupuesto").innerHTML = datos_buscados
				
	
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
		document.getElementById('inptCodigoPresupuesto').value = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoPresupuesto').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inpTSeleccCostoPresupuesto').innerHTML = $(datostr).children('td[id="td_datos_11"]').html();
		document.getElementById('inptCantidadPresupuesto').value = "1";
		document.getElementById('inptPrecioPresupuesto').value = $(datostr).children('td[id="td_datos_4"]').html();
		document.getElementById('inptCantidadPresupuesto').focus();
		calcularTotalPresupuesto(document.getElementById('inptPrecioPresupuesto'))
		
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

function buscarproductoporcodigoPresupuesto() {
	
	var buscador = document.getElementById('inptCodigoPresupuesto').value
	
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
		document.getElementById('inptCodigoPresupuesto').value = datos["5"];
		document.getElementById('inptProductoPresupuesto').value = datos["3"];
		// document.getElementById('inpTSeleccCostoPresupuesto').innerHTML = datos["2"];
		document.getElementById('inptCantidadPresupuesto').value = "1";
		document.getElementById('inptPrecioPresupuesto').value = datos["4"];
		document.getElementById('inptCantidadPresupuesto').focus();
		calcularTotalPresupuesto(document.getElementById('inptPrecioPresupuesto'))					
					
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
	let tabla= fila.parentElement.parentElement;
	const idDetalle= tabla.id.substring(15);
	
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idDetalle": idDetalle,
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
					fila.remove();
    				recalcularTotalPresupuesto();
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

  filas.forEach(fila => {
    let valor = fila.cells[4]?.innerText.replace(/\./g,"").replace(",",".") || "0";
    total += parseFloat(valor) || 0;
  });
 
document.getElementById("inptTotalPresupuesto2").innerHTML=separadordemilesnumero(total);
document.getElementById("inptTOTALPresupuestoFORM").value=separadordemilesnumero(total);
 
document.getElementById("table_vista_detalles_presupuesto").innerHTML=""
 generarTabla()
 
}


 
function generarTabla() {
	
	var total =document.getElementById("inptTOTALPresupuestoFORM").value
	total=QuitarSeparadorMilValor(total)
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
  
  var cantCuota =document.getElementById("inptTotalcuotasPresupuesto").value
  var cuotasList = [2,3,4,5,6,8,10,12,15,18];
  if(cantCuota=="12"){
	   cuotasList = [2,3,4,5,6,8,10,12];
  } 
  
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
}
 
function abmDetallesPresupuesto(cod_presupuestoFK, cod_productoFK, precio, cantidad, codigo_ficticio_presupuesto, nombre_producto, total_presupuesto,es_precio_contado) {
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"accion": "abmDetallesPresupuesto",
		"cod_presupuestoFK": cod_presupuestoFK,
		"cod_productoFK": cod_productoFK,
		"cantidad": cantidad,
		"precio": precio,
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
						+ "<td  id='td_datos_2' style='width:50%;'>" + nombre_producto + "</td>"
						+ "<td  id='td_datos_3' style='width:10%'>" + cantidad + "</td>"
						+ "<td  id='td_datos_4' style='width:15%'>" + precio + "</td>"
						+ "<td  id='td_datos_5' style='width:15%'>" + total_presupuesto + "</td>"
						+ "<td  id='td_datos_6' style='display:none'></td>"
						+ "<td  id='td_datos_7' style='display:none'>" + 0 + "</td>"
						+ "<td  id='td_datos_8' style='display:none'>" + 0 + "</td>"
						+ "<td  id='td_datos_9' style='display:none'>" + es_precio_contado + "</td>"
						+ "<td  id='td_datos_10' style='display:none'>" + separadordemilesnumero(precio) + "</td>"
						+ "<td  id='td_datos_11' style='display:none'>" + separadordemilesnumero(total_presupuesto) + "</td>"
						+ "<td style='display:none' > <button class='btn-eliminar' >❌</button> </td>"
						+ "</tr>"
						+ "</table>"

					document.getElementById("table_vista_producto_presupuestoDetalle").innerHTML += pagina;

					totalPresupuesto = 0;
					var totalEntrega = document.getElementById('inptTotalPresupuesto').value;

					$("tr[name=tdDetallePresupuesto]").each(function (i, elementohtml) {
						var total = $(elementohtml).children('td[id="td_datos_11"]').html();
						total = QuitarSeparadorMilValor(total)
						totalPresupuesto = Number(totalPresupuesto) + Number(total)
					});

					document.getElementById("inptTotalPresupuesto2").innerHTML = separadordemilesnumero(totalPresupuesto);
					document.getElementById("inptTOTALPresupuestoFORM").value = separadordemilesnumero(totalPresupuesto);

					generarTabla();
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
function anhadirPrPresupuesto() {
	var entrega = document.getElementById('inptEntregaPresupuesto').value

	var inptCodigoPresupuesto = document.getElementById('inptCodigoPresupuesto').value
	var inptProductoPresupuesto = document.getElementById('inptProductoPresupuesto').value
	var inptPrecioPresupuesto = document.getElementById('inptPrecioPresupuesto').value
	var inptCantidadPresupuesto = document.getElementById('inptCantidadPresupuesto').value
	var inptTotalPresupuesto = document.getElementById('inptTotalPresupuesto').value
	var inpTSeleccCostoPresupuesto = $("select[id=inpTSeleccCostoPresupuesto]").children(":selected").attr("class")
	var inpCuotero = $("select[id=inpTSeleccCostoPresupuesto]").children(":selected").attr("id")
	var inpPrecioContado = $("select[id=inpTSeleccCostoPresupuesto]").children(":selected").attr("url")

	if (inptCodigoPresupuesto != "") {
		if (!idabmPresupuesto) {
			ver_vetana_informativa("Faltan datos", "Favor seleccionar el cliente", "error");
			return false;
		}

		if (inptCantidadPresupuesto <= 0 || inptCantidadPresupuesto == "") {
			ver_vetana_informativa("Faltan datos", "FAVOR AGREGAR CANTIDAD");
			return false;
		}

		if (inptPrecioPresupuesto <= 0 || inptPrecioPresupuesto == "") {
			ver_vetana_informativa("Faltan datos", "FAVOR AGREGAR EL PRECIO");
			return false;
		}

		if (inptTotalPresupuesto == "0" || inptTotalPresupuesto == "") {
			ver_vetana_informativa("Faltan datos", "TOTAL NO VALIDO");
			return false;
		}

		// Agrega al presupuesto existente
		abmDetallesPresupuesto(idabmPresupuesto, idFkProducto, inptPrecioPresupuesto.replace('.', ''), inptCantidadPresupuesto, inptCodigoPresupuesto, inptProductoPresupuesto,inptTotalPresupuesto,inpPrecioContado);
	}

	document.getElementById('inptCodigoPresupuesto').value = ""
	document.getElementById('inptProductoPresupuesto').value = ""
	document.getElementById('inptPrecioPresupuesto').value = ""
	document.getElementById('inpTSeleccCostoPresupuesto').value = ""
	limpirarAddPresupuesto()
	document.getElementById('inptCantidadPresupuesto').value = ""
	document.getElementById('inptTotalPresupuesto').value = ""
}

function limpirarPresupuesto(){
	document.getElementById('inptCodigoPresupuesto').value = ""
document.getElementById('inptProductoPresupuesto').value = ""
document.getElementById('inptPrecioPresupuesto').value = ""
document.getElementById('inpTSeleccCostoPresupuesto').value = ""
document.getElementById('inptEntregaPresupuesto').value = "0"
document.getElementById('inptCantidadPresupuesto').value = ""
document.getElementById('inptTotalPresupuesto').value = ""
totalPresupuesto=0
document.getElementById('inptTotalPresupuesto2').innerHTML = ""
document.getElementById('inptTOTALPresupuestoFORM').value = "0"
document.getElementById('table_vista_producto_Presupuesto').innerHTML = ""
document.getElementById('table_vista_producto_presupuestoDetalle').innerHTML = ""
document.getElementById('table_vista_detalles_presupuesto').innerHTML = ""

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
    document.getElementById('inptTotalPresupuesto2').innerHTML = $(elemento).children('td[id="td_datos_7"]').html();
    document.getElementById('inptTOTALPresupuestoFORM').value = $(elemento).children('td[id="td_datos_7"]').html();

	buscarDetallesPresupuesto($(elemento).children('td[id="td_id"]').html());
    document.getElementById('table_vista_producto_presupuestoDetalle').innerHTML = "";$(elemento).children('td[id="td_datos_"]').html();
    document.getElementById('table_vista_detalles_presupuesto').innerHTML = "";$(elemento).children('td[id="td_datos_"]').html();

    document.getElementById('inptDocumentoClientePresupuesto').value= $(elemento).children('td[id="td_datos_5"]').html();
    document.getElementById('inptNombreClientePresupuesto').value= $(elemento).children('td[id="td_datos_4"]').html();
    idFkCliente= $(elemento).children('td[id="td_datos_3"]').html();
	verCerrarAbmDetallesPresupuesto(false, true);
}

function buscarDetallesPresupuesto(cod_presupuestoFK) {
	document.getElementById("table_vista_producto_presupuestoDetalle").innerHTML= paginacargando;
	document.getElementById("table_vista_detalles_presupuesto").innerHTML= paginacargando;

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
