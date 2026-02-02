/* 
    divEliminados:
    - divGarantiaProductoHistorial
    - divProductoEnGarantia
*/
/*
HISTORIAL DE GARANTIAS
*/
function verCerrarInformeProductoEnGarantia(d){
	document.getElementById("divSegundoPlano").style.display="none";
	if(d=="1"){
		if(controlacceso("VERINFORMEGARANTIA","accion")==false){return;}
		 document.getElementById("divProductoEnGarantia").style.display = "";
		document.getElementById("tdEfectoProductoEnGarantia").className="magictime slideDownReturn"		
	}else{
		document.getElementById("divMinimizadoProductoEnGarantia").style.display = "none";
		limpiarcamposproductosganrantia()
document.getElementById("tdEfectoProductoEnGarantia").className="magictime vanishOut"
	$("div[id=divProductoEnGarantia]").fadeOut(500);	
		
	}	
}
function limpiarcamposproductosganrantia(){
	document.getElementById("inptBuscarProductosGarantia1").value=""
	document.getElementById("inptBuscarProductosGarantia2").value=""
	document.getElementById("inptBuscarProductosGarantia3").value=""
	document.getElementById("table_ProductoGarantia").innerHTML = ""
	document.getElementById("inptTotalRegistoProductoGarantia").value = ""
	
}
function minimizarproductogarantia(){
	 document.getElementById("divMinimizadoProductoEnGarantia").style.display = "";
document.getElementById("tdEfectoProductoEnGarantia").className="magictime slideDown"
	$("div[id=divProductoEnGarantia]").fadeOut(500);	
}

function buscarHistorialGarantia() {
	if(controlacceso("BUSCARINFORMEGARANTIA","accion")==false){return;}
	var nrofactura = document.getElementById("inptBuscarProductosGarantia1").value
	var cod_local = document.getElementById("inptlocalProductoGarantia").value
	var documento = document.getElementById("inptBuscarProductosGarantia2").value
	var cliente = document.getElementById("inptBuscarProductosGarantia3").value
	var estado = document.getElementById("inptBuscarProductosGarantia6").value
	document.getElementById("table_ProductoGarantia").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"nrofactura": nrofactura,
		"documento": documento,
		"cliente": cliente,
		"cod_local": cod_local,
		"estado": estado,
		"funt": "buscarHistorialGarantia"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmdetalleventa.php",
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
			document.getElementById("table_ProductoGarantia").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_ProductoGarantia").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_ProductoGarantia").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoProductoGarantia").value = datos[3]
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
var idGarantiaModificar="";
var observacionGarantiaTikect="";
function obtenerdatosvistaproductosgarantia(datostr) {	
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	idGarantiaModificar = $(datostr).children('td[id="td_id_1"]').html();
	var estadogarantia = $(datostr).children('td[id="td_datos_9"]').html();
	var optionselect=""
	if(estadogarantia=="Pendiente a verificar"){
	optionselect="<option  value='verificacion' >EN VERIFICACION</option>";  
	}
	if(estadogarantia=="verificacion"){
	optionselect="<option  value='listo' >LISTO PARA ENTREGAR</option>";  
	}
	if(estadogarantia=="listo"){
	optionselect="<option  value='entregado' >ENTREGADO</option>";  
	}
	
	document.getElementById('inputSelectEstadoengarantiaHistorial').innerHTML = optionselect;
	document.getElementById('inptRegistroSeleccionadoProductoGarantia').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNroVentaGarantiaHistorial').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptProductoDevolucionGarantiaHistorial').value = $(datostr).children('td[id="td_datos_5"]').html();
	observacionGarantiaTikect = $(datostr).children('td[id="td_datos_6"]').html();
	SeleccEstadoGarantia()
	document.getElementById('inptFechaEntregaGarantiaHistorial').value ="";	
	if(estadogarantia=="entregado"){
	idGarantiaModificar="";
		document.getElementById('inptRegistroSeleccionadoProductoGarantia').value = "";
	}
	
}
function verCerrarHistorialProductoEnGarantia(d){
	if(d=="1"){
		if(idGarantiaModificar==""){
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
			return
		}
		
		 document.getElementById("divGarantiaProductoHistorial").style.display = "";
	}else{
		 document.getElementById("divGarantiaProductoHistorial").style.display = "none";
	}	
}
function SeleccEstadoGarantia(){		
    document.getElementById('divFechaEnvio').style.display="none"
	document.getElementById('divFechaDevuelto').style.display="none"
	document.getElementById('divFechaEntrega').style.display="none"
	if(document.getElementById('inputSelectEstadoengarantiaHistorial').value=="verificacion"){
		document.getElementById('divFechaEnvio').style.display=""
	}
	if(document.getElementById('inputSelectEstadoengarantiaHistorial').value=="listo"){
		document.getElementById('divFechaDevuelto').style.display=""
	}
	if(document.getElementById('inputSelectEstadoengarantiaHistorial').value=="entregado"){
		document.getElementById('divFechaEntrega').style.display=""
	}
}
function modificarRegistroGarantia() {
   	
     var inputSelectEstadoengarantiaHistorial=document.getElementById("inputSelectEstadoengarantiaHistorial").value
     var fecha=""
	 if(inputSelectEstadoengarantiaHistorial=="verificacion"){
		fecha=document.getElementById("inptFechaEnvioGarantiaHistorial").value		
	}
	if(inputSelectEstadoengarantiaHistorial=="listo"){
		fecha=document.getElementById("inptFechaDevueltaGarantiaHistorial").value
	}
	if(inputSelectEstadoengarantiaHistorial=="entregado"){
		fecha=document.getElementById("inptFechaEntregaGarantiaHistorial").value
	}	 
	if (idGarantiaModificar == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		return false;
	}
	if (inptFechaEntregaGarantiaHistorial == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN FECHA ")
		return false;
	}
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "editarusogarantia")
	datos.append("idgarantia", idGarantiaModificar)
	datos.append("fecha", fecha)
	datos.append("estado", inputSelectEstadoengarantiaHistorial)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmdetalleventa.php",
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
			verCerrarEfectoCargando("")
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];

				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					if(inputSelectEstadoengarantiaHistorial=="verificacion"){
						ImprimirDivTicketGarantiaVerificacion()
					}
					if(inputSelectEstadoengarantiaHistorial=="entregado"){
						ImprimirDivTicketGarantiaEntrega()
					}
					document.getElementById("divGarantiaProductoHistorial").style.display = "none";
				   buscarHistorialGarantia()
                   				   
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});	
}
