/* 
    divEliminados:
    - divAbmCheque
*/
/*
ABM CHEQUE
*/
function verCerrarAbmCheque(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmCheque").style.display==""){
		document.getElementById("divMinimizadoCheque").style.display="none"
		limpiarcamposCheque()
		limpiarcamposbuscarCheque()
document.getElementById("tdEfectoAbmCheque").className="magictime vanishOut"
	$("div[id=divAbmCheque]").fadeOut(500);	
	}else{		
		
		// if(controlacceso("VERLISTADODECAJA","accion")==false){return;}
		
		document.getElementById("divAbmCheque").style.display=""
document.getElementById("tdEfectoAbmCheque").className="magictime slideDownReturn"
	}
}

function verCerrarVentanaAbmCheque(d, l) {
	if (d == "1") {		
		if (l == "1") {
			// if(controlacceso("INSERTARLISTADODECAJA","accion")==false){return;}
			limpiarcamposCheque()
		}
		$("div[id=divAbmCheque2]").fadeIn(250)
		document.getElementById('divAbmCheque1').style.display = "none"
	} else {
		$("div[id=divAbmCheque1]").fadeIn(250)
		document.getElementById('divAbmCheque2').style.display = "none"
	}
}


function limpiarcamposbuscarCheque(){
	    document.getElementById('inptBuscarAbmCheque1').value=""
		document.getElementById('inptBuscarAbmCheque2').value=""
		document.getElementById('inptBuscarAbmCheque3').value=""
		document.getElementById('inptBuscarAbmCheque4').value=""
		document.getElementById('inptBuscarAbmCheque5').value=""
		document.getElementById('inptBuscarAbmCheque6').value=""
		document.getElementById('inptBuscarAbmCheque7').value=""
		document.getElementById("table_abm_Cheque").innerHTML = ""
		document.getElementById("inptTotalRegistoCheque").value = "";
		document.getElementById("inptTotalMontoRegistoCheque").value = "";
}
function minimizarabmCheque(){
document.getElementById("tdEfectoAbmCheque").className="magictime slideDown"
	$("div[id=divAbmCheque]").fadeOut(500);	
	document.getElementById("divMinimizadoCheque").style.display=""
}

function verVentanaEditarCheque() {
	if (idAbmCheque == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		return;
	}
	// if(controlacceso("EDITARLISTADODECAJA","accion")==false){return;}
	verCerrarVentanaAbmCheque("1", "2")
}
var idAbmCheque = ""
function ObtenerdatosAbmCheque(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	
	document.getElementById('inptFechEmiCheque').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptFechaVenCheque').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptNroCheque').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptOrdenCheque').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptConceptoCheque').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptImporteCheque').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptNombreBancoCheque').value = $(datostr).children('td[id="td_datos_9"]').html();
	document.getElementById('inptEstadoCheque').value = $(datostr).children('td[id="td_datos_10"]').html();
	document.getElementById('inptPagadoCheque').value = $(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptRegistroSeleccCheque').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('btnEditarDatosCheque').style.backgroundColor="";
	document.getElementById('btnAbmCheque').value =  "Editar datos";
		
	idAbmCheque = $(datostr).children('td[id="td_id"]').html();
}
function verificarcamposCheque() {
	var inptFechEmiCheque = document.getElementById('inptFechEmiCheque').value
	var inptFechaVenCheque = document.getElementById('inptFechaVenCheque').value
	var inptNroCheque = document.getElementById('inptNroCheque').value
	var inptOrdenCheque = document.getElementById('inptOrdenCheque').value
	var inptConceptoCheque = document.getElementById('inptConceptoCheque').value
	var inptImporteCheque = document.getElementById('inptImporteCheque').value
	var inptNombreBancoCheque = document.getElementById('inptNombreBancoCheque').value
	var inptEstadoCheque = document.getElementById('inptEstadoCheque').value
	var inptPagadoCheque = document.getElementById('inptPagadoCheque').value
	
	if (inptFechEmiCheque == "") {
		ver_vetana_informativa("FALTO INGRESAR LA FECHA DE EMISION")
		return false;
	}
	
	if (inptFechaVenCheque == "") {
		ver_vetana_informativa("FALTO INGRESAR LA FECHA DE VENCIMIENTO")
		return false;
	}
	
	if (inptNroCheque == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NUMERO DE CHEQUE")
		return false;
	}
	
	if (inptOrdenCheque == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE ")
		return false;
	}
	if (inptConceptoCheque == "") {
		ver_vetana_informativa("FALTO INGRESAR EL CONCEPTO")
		return false;
	}
	if (inptImporteCheque == "") {
		ver_vetana_informativa("FALTO INGRESAR EL IMPORTE")
		return false;
	}
	if (inptNombreBancoCheque == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL BANCO")
		return false;
	}
	if (inptEstadoCheque == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL ESTADO")
		return false;
	}
	
	var accion = "";
	if (idAbmCheque != "") {
		accion = "editar";
		// if(controlacceso("EDITARLISTADODECAJA","accion")==false){return;}
	} else {
		accion = "nuevo";
		// if(controlacceso("INSERTARLISTADODECAJA","accion")==false){return;}
	}
	abmCheque(inptPagadoCheque,inptFechEmiCheque ,inptFechaVenCheque , inptNroCheque ,inptOrdenCheque , inptConceptoCheque ,inptImporteCheque , inptNombreBancoCheque ,inptEstadoCheque , idAbmCheque, accion);
}
function abmCheque(pagado,fechaemi,fechaven ,nroCheque,orden ,concepto,importe ,banco,estado , idAbmCheque, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idAbmCheque", idAbmCheque)
	datos.append("fechaemi", fechaemi)
	datos.append("fechaven", fechaven)
	datos.append("nroCheque", nroCheque)
	datos.append("orden", orden)
	datos.append("concepto", concepto)
	datos.append("importe", importe)
	datos.append("banco", banco)
	datos.append("pagado", pagado)
	datos.append("estado", estado)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCheque.php",
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
					limpiarcamposCheque()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmCheque = ""
					buscarabmCheque();
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}


function checkestadoCheque(d){	
	if(d=="1"){
		document.getElementById('inptSeleccEstadoBuscarCheque1').checked=true
		document.getElementById('inptSeleccEstadoBuscarCheque2').checked=false
		document.getElementById('inptFechaCheque1').value = "";
	    document.getElementById('inptFechaCheque2').value = "";	
	}else{		
		document.getElementById('inptSeleccEstadoBuscarCheque1').checked=false
		document.getElementById('inptSeleccEstadoBuscarCheque2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptFechaCheque1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptFechaCheque2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
function buscarabmCheque() {
	// if(controlacceso("BUSCARLISTADODECAJA","accion")==false){return;}
	var fechaEmi = document.getElementById('inptBuscarAbmCheque1').value
	var NroCheque = document.getElementById('inptBuscarAbmCheque2').value
	var fechaven = document.getElementById('inptBuscarAbmCheque3').value
	var orden = document.getElementById('inptBuscarAbmCheque4').value
	var concepto = document.getElementById('inptBuscarAbmCheque5').value
	var pago = document.getElementById('inptBuscarAbmCheque6').value
	var banco = document.getElementById('inptBuscarAbmCheque7').value
	
	// alert(pago)
	var Fecha1 = ""
	var Fecha2 = ""
	
	if(document.getElementById('inptSeleccEstadoBuscarCheque1').checked!=true){
		Fecha1 = document.getElementById('inptFechaCheque1').value
		Fecha2 = document.getElementById('inptFechaCheque2').value
	}
	
	document.getElementById("table_abm_Cheque").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fechaEmi": fechaEmi,
		"NroCheque": NroCheque,
		"fechaven": fechaven,
		"orden": orden,
		"concepto": concepto,
		"pago": pago,
		"banco": banco,
		"Fecha1": Fecha1,
		"Fecha2": Fecha2,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCheque.php",
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
			document.getElementById("table_abm_Cheque").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_Cheque").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_Cheque").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoCheque").value = datos[3];
					document.getElementById("inptTotalMontoRegistoCheque").value =  datos[4];
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function limpiarcamposCheque() {
	document.getElementById('inptFechEmiCheque').value = "";
	document.getElementById('inptFechaVenCheque').value = "";
	document.getElementById('inptNroCheque').value = "";
	document.getElementById('inptOrdenCheque').value = "";
	document.getElementById('inptConceptoCheque').value = "";
	document.getElementById('inptImporteCheque').value = "";
	document.getElementById('inptNombreBancoCheque').value = "";
	document.getElementById('inptEstadoCheque').value = "Activo";
	document.getElementById('inptPagadoCheque').value = "PENDIENTE";
	document.getElementById('inptRegistroSeleccCheque').value = "";
	document.getElementById('btnEditarDatosCheque').style.backgroundColor="#d5d3d3";
	document.getElementById('btnAbmCheque').value = "Guardar datos";
	idAbmCheque= "";
}