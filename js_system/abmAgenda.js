/* 
    divEliminados:
    - divAbmAgenda
    - 
*/
/*
ABM AGENDA
*/
function verCerrarAbmAgenda(){
		document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmAgenda").style.display==""){
		document.getElementById("divMinimizadoAgenda").style.display="none"
		limpiarcamposAgenda()
		// limpiarcamposbuscarTipoPago()
		document.getElementById("tdEfectoAbmAgenda").className="magictime vanishOut"
		$("div[id=divAbmAgenda]").fadeOut(500);	
	}else{		
		
		// if(controlacceso("VERLISTADODECAJA","accion")==false){return;}
		
		document.getElementById("divAbmAgenda").style.display=""
		document.getElementById("tdEfectoAbmAgenda").className="magictime slideDownReturn"
	}
}

function verCerrarVentanaAbmAgenda(d, l) {
	if (d == "1") {		
		if (l == "1") {
			// if(controlacceso("INSERTARLISTADODECAJA","accion")==false){return;}
			limpiarcamposAgenda()
		}
		$("div[id=divAbmAgenda2]").fadeIn(250)
		document.getElementById('divAbmAgenda1').style.display = "none"
	} else {
		$("div[id=divAbmAgenda1]").fadeIn(250)
		document.getElementById('divAbmAgenda2').style.display = "none"
	}
}


function limpiarcamposbuscarAgenda(){
	    document.getElementById('inptBuscarAbmAgenda2').value=""
		document.getElementById("table_abm_Agenda").innerHTML = ""
		document.getElementById("inptTotalRegistoAgenda").value = "";
}
function minimizarabmAgenda(){
document.getElementById("tdEfectoAbmTipoPago").className="magictime slideDown"
	$("div[id=divAbmAgenda]").fadeOut(500);	
	document.getElementById("divMinimizadoAgenda").style.display=""
}


function verVentanaEditarAgenda() {
	if (idAbmAgenda == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		return;
	}
	// if(controlacceso("EDITARLISTADODECAJA","accion")==false){return;}
	verCerrarVentanaAbmAgenda("1", "2")
}
var idAbmAgenda = ""
var cod_clienteAgenda = ""
function obtenerdatosabmAgenda(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptClienteAgenda').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptRegistroSeleccAgenda').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptMotivoAgenda').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptCompromisoAgenda').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptEstadoAgenda').value = $(datostr).children('td[id="td_datos_6"]').html();
	
	document.getElementById('btnAbmAgenda').value = "Editar datos";
	document.getElementById('btnEditarDatosAgenda').style.backgroundColor="";
	idAbmAgenda = $(datostr).children('td[id="td_id"]').html();
	cod_clienteAgenda = $(datostr).children('td[id="td_datos_7"]').html();
}
function verificarcamposAgenda() {
	var inptMotivoAgenda = document.getElementById('inptMotivoAgenda').value
	var inptCompromisoAgenda = document.getElementById('inptCompromisoAgenda').value
	var inptEstadoAgenda = document.getElementById('inptEstadoAgenda').value
	
	if (inptMotivoAgenda == "") {
		ver_vetana_informativa("FALTO INGRESAR UN MOTIVO")
		return false;
	}
	
	if (cod_clienteAgenda == "") {
		ver_vetana_informativa("FALTO SELECCIONAR CLIENTE")
		return false;
	}
	
	
	var accion = "";
	if (idAbmAgenda != "") {
		accion = "editar";
		// if(controlacceso("EDITARLISTADODECAJA","accion")==false){return;}
	} else {
		accion = "nuevo";
		// if(controlacceso("INSERTARLISTADODECAJA","accion")==false){return;}
	}
	abmAgenda(inptMotivoAgenda, inptCompromisoAgenda  ,inptEstadoAgenda ,cod_clienteAgenda , idAbmAgenda, accion);
}
function abmAgenda(motivo, fechaCompromiso  ,estado , cod_clienteAgenda , idAgenda, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idAgenda", idAgenda)
	datos.append("motivo", motivo)
	datos.append("fechaCompromiso", fechaCompromiso)
	datos.append("estado", estado)
	datos.append("Cod_cobrador", idFkCobrador)
	datos.append("cod_clienteAgenda", cod_clienteAgenda)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAgenda.php",
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
					limpiarcamposAgenda()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmAgenda = ""
					buscarabmAgenda();
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}


function checkestadoAgenda(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarAgenda1').checked=true
	document.getElementById('inptSeleccEstadoBuscarAgenda2').checked=false	
	}else{
	document.getElementById('inptSeleccEstadoBuscarAgenda1').checked=false
	document.getElementById('inptSeleccEstadoBuscarAgenda2').checked=true
	}
}

function buscarabmAgenda() {
	// if(controlacceso("BUSCARLISTADODECAJA","accion")==false){return;}
	var fecha1 = document.getElementById('inptBuscarAgendaF1').value
	var fecha2 = document.getElementById('inptBuscarAgendaF2').value
	var local = ""
	var cliente= document.getElementById("inptBuscarAbmAgenda2").value
	var cobrador= document.getElementById("inptBuscarAbmAgenda3").value
	
	var tipo=""
	if(document.getElementById('checkHistorialAgendaFC').checked==true){
		tipo="compromiso"
	}else{
		tipo="visita"
	}

	if(document.getElementById('checkHistorialFechaAgenda2').checked==true){
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO")
		return
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN")
		return
	}
	}else{
	fecha1 = ""
	fecha2 = ""
	}
	
	var estado = ""
	if(document.getElementById('inptSeleccEstadoBuscarAgenda1').checked==true){
		estado = "Activo"
	}else{
		estado = "Inactivo"
	}
	document.getElementById("table_abm_Agenda").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cliente": cliente,
		"cobrador": cobrador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"estado": estado,
		"tipo": tipo,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAgenda.php",
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
			document.getElementById("table_abm_Agenda").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_Agenda").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_Agenda").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoAgenda").value = datos[3];
					
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function limpiarcamposAgenda() {
	document.getElementById('inptClienteAgenda').value = "";
	document.getElementById('inptMotivoAgenda').value = "";	
	document.getElementById('inptCompromisoAgenda').value = "";
	document.getElementById('inptEstadoAgenda').value = "Activo";
	document.getElementById('btnEditarDatosAgenda').style.backgroundColor="#d5d3d3";
	document.getElementById('btnAbmAgenda').value = "Guardar datos";
	idAbmAgenda= "";
	cod_clienteAgenda = "";
}


function checkHistorialAgenda(d){	
	if(d=="1"){
		document.getElementById('checkHistorialAgendaFC').checked=true
		document.getElementById('checkHistorialAgendaFV').checked=false
	}else{		
		document.getElementById('checkHistorialAgendaFC').checked=false
		document.getElementById('checkHistorialAgendaFV').checked=true
	
	}
}



function checkHistorialFechaAgenda(d){	
	if(d=="1"){
		document.getElementById('checkHistorialFechaAgenda1').checked=true
		document.getElementById('checkHistorialFechaAgenda2').checked=false
		document.getElementById('inptBuscarAgendaF1').value = "";
	    document.getElementById('inptBuscarAgendaF2').value = "";	
	}else{		
		document.getElementById('checkHistorialFechaAgenda1').checked=false
		document.getElementById('checkHistorialFechaAgenda2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarAgendaF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarAgendaF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}

