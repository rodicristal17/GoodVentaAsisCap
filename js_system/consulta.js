 
/*
ABM PRE CONSULTA
*/
function verCerrarAbmPreConsulta(){

	if(document.getElementById("divAbmPreConsulta").style.display==""){
		document.getElementById("divMinimizadoPreConsulta").style.display="none"
		limpiarcamposbuscarPreConsulta()
		limpiarcamposPreConsulta()
	
document.getElementById("tdEfectoAbmPreConsulta").className="magictime vanishOut"
	$("div[id=divAbmPreConsulta]").fadeOut(500);	
	}else{		
	// if(controlacceso("VERLISTADOCOBRADORES","accion")==false){return;}
		document.getElementById("divAbmPreConsulta").style.display=""
		document.getElementById("tdEfectoAbmPreConsulta").className="magictime slideDownReturn"
			buscarabmPreConsulta()
		
	}
}
function limpiarcamposbuscarPreConsulta(){
	document.getElementById('inptBuscarAbmPreConsulta1').value=""
	document.getElementById('inptBuscarAbmPreConsulta2').value=""
	document.getElementById("table_abm_PreConsulta").innerHTML = ""
	document.getElementById("inptRegistroNroPreConsulta").value = ""
}
function minimizarabmPreConsulta(){
document.getElementById("tdEfectoAbmPreConsulta").className="magictime slideDown"
	$("div[id=divAbmPreConsulta]").fadeOut(500);	
	document.getElementById("divMinimizadoPreConsulta").style.display=""
}

function verCerrarVentanaDetallePreConsulta(d) {
	if (d == "1") {
		// if(controlacceso("INSERTARLISTADOCOBRADORES","accion")==false){return;}
		
		if(cod_preConsultaFK ==""){
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
			return false;
		}
		limpiarcamposDetallePreConsulta()
		buscarabmDetallePreConsulta()
		document.getElementById("tdEfectoSignosVitales").className="magictime slideRight"
		$("div[id=divAbmSignosVitales]").fadeIn(500)


	} else {
		document.getElementById("tdEfectoSignosVitales").className="magictime vanishOut"
		$("div[id=divAbmSignosVitales]").fadeOut(500);	
	}
}

function verCerrarVentanaAbmPreConsulta(d, l) {
	if (d == "1") {		
		if (l == "1") {
			// if(controlacceso("INSERTARLISTADOCOBRADORES","accion")==false){return;}
			limpiarcamposPreConsulta()
		}
		$("div[id=divAbmPreConsulta2]").fadeIn(250)
		document.getElementById('divAbmPreConsulta1').style.display = "none"
	} else {
		$("div[id=divAbmPreConsulta1]").fadeIn(250)
		document.getElementById('divAbmPreConsulta2').style.display = "none"
	}
}
function verVentanaEditarPreConsulta() {
	// if(controlacceso("EDITARLISTADOCOBRADORES","accion")==false){return;}
	if (cod_preConsultaFK == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmPreConsulta("1", "2")
}
var idAbmPreConsulta = ""
function obtenerdatosabmPreConsulta(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptPacientePreConsulta').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptRegistroSeleccPreConsulta').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptFechaPreConsulta').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptSintomaPreConsulta').value = $(datostr).children('td[id="td_datos_3"]').html();
	

	document.getElementById('btnAbmPreConsulta').value ="Editar datos";
	document.getElementById('btnEditarPreConsulta').style.backgroundColor="";
	document.getElementById('btnAddDatosPreConsulta').style.backgroundColor="";
	idAbmPreConsulta = $(datostr).children('td[id="td_id"]').html();
	cod_preConsultaFK = $(datostr).children('td[id="td_id"]').html();

}

let idPacienteFkPreConsulta=""
function verificarcamposPreConsulta() {
	var inptPacientePreConsulta = document.getElementById('inptPacientePreConsulta').value
	var inptFechaPreConsulta = document.getElementById('inptFechaPreConsulta').value
	var inptSintomaPreConsulta = document.getElementById('inptSintomaPreConsulta').value
	
	if(idPacienteFkPreConsulta==""){
			
		$("input[id=inptPacientePreConsulta]").each(function (i, Elemento) {
      var $input = $(this),
          val = $input.val();
		 
          list = $input.attr('list'),
          match = $('#'+list + ' option').filter(function() {
              return ($(this).val() === val);			 
          });

       if(match.length > 0) {
         idPacienteFkPreConsulta=$(match).attr("id")
       } else {
           // value is not in list
       }
});
	}
	if (inptFechaPreConsulta == "") {
		ver_vetana_informativa("FALTO INGRESAR LA FECHA", "#")
		return false;
	}

	
	var accion = "";
	if (cod_preConsultaFK != "") {
		accion = "editar";
		// if(controlacceso("EDITARLISTADOCOBRADORES","accion")==false){return;}
	} else {		
		accion = "nuevo";
		// if(controlacceso("INSERTARLISTADOCOBRADORES","accion")==false){return;}
	}
	abmPreConsulta( idPacienteFkPreConsulta, inptFechaPreConsulta,inptSintomaPreConsulta, cod_preConsultaFK, accion);
}

let cod_preConsultaFK =""

function abmPreConsulta( cod_Paciente, fecha, sintomas, idAbm, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idAbmPreConsulta", idAbm)
	datos.append("cod_Paciente", cod_Paciente)
	datos.append("fecha", fecha)
	datos.append("sintomas", sintomas)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPreConsulta.php",
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
					
					idAbmPreConsulta =  datos["2"]; 
					limpiarcamposDetallePreConsulta()
					buscarabmDetallePreConsulta()
					document.getElementById("tdEfectoSignosVitales").className="magictime slideRight"
					$("div[id=divAbmSignosVitales]").fadeIn(500)
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					
					buscarabmPreConsulta()
					cod_preConsultaFK =  datos["2"]; 
					
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarabmPreConsulta() {
// if(controlacceso("BUSCARLISTADOCOBRADORES","accion")==false){return;}
	var ci = document.getElementById('inptBuscarAbmPreConsulta1').value
	var Paciente = document.getElementById('inptBuscarAbmPreConsulta2').value
	
	document.getElementById("table_abm_PreConsulta").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"ci": ci,
		"Paciente": Paciente,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmPreConsulta.php",
		type: "post",
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
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
          manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_abm_PreConsulta").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_PreConsulta").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_PreConsulta").innerHTML = datos_buscados
					document.getElementById("inptRegistroNroPreConsulta").value = datos[3]
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}


function limpiarcamposPreConsulta() {
	
			 
var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()+1
	if(mes<10){
		mes="0"+mes;
	}
	var hora =f.getHours()
	if(hora<10){
		hora="0"+hora;
	}
	var min =f.getMinutes()
	if(min<10){
		min="0"+min;
	}
	 	document.getElementById('inptFechaPreConsulta').value=f.getFullYear()+"-"+mes+"-"+dia+"T"+hora+":"+min+":00";
	document.getElementById('inptPacientePreConsulta').value = "";
	document.getElementById('inptSintomaPreConsulta').value = "";
	document.getElementById('btnAbmPreConsulta').value = "Guardar datos";
	document.getElementById('btnEditarPreConsulta').style.backgroundColor="#b7b7b7";
	document.getElementById('btnAddDatosPreConsulta').style.backgroundColor="#b7b7b7";
	idAbmPreConsulta = "";
	cod_preConsultaFK = "";
}


function ControlPacientePreConsulta(inp){
	
	if(inp.value==""){
		 document.getElementById("btnPreConsulta1").style.display="";
		 document.getElementById("btnPreConsulta2").style.display="none";
	}else{
		 document.getElementById("btnPreConsulta1").style.display="none";
		 document.getElementById("btnPreConsulta2").style.display="";
	}
}


let idAbmDetallePreConsulta = ""


function verificarcamposDetallePreConsulta() {
	var inptDescripcionSignosVitales = document.getElementById('inptDescripcionSignosVitales').value
	var inptRespuestaSignosVitales = document.getElementById('inptRespuestaSignosVitales').value
	
	if (inptDescripcionSignosVitales == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN SIGNO VITAL", "#")
		return false;
	}
	
	if (inptRespuestaSignosVitales == "") {
		ver_vetana_informativa("FALTO INGRESAR EL PARAMETRO", "#")
		return false;
	}
	
	if (cod_preConsultaFK == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO3", "#")
		return false;
	}
	
	var accion = "";
	if (idAbmDetallePreConsulta != "") {
		accion = "editar";
		// if(controlacceso("EDITARLISTADOCOBRADORES","accion")==false){return;}
	} else {		
		accion = "nuevo";
		// if(controlacceso("INSERTARLISTADOCOBRADORES","accion")==false){return;}
	}
		
	abmDetallePreConsulta( inptDescripcionSignosVitales, inptRespuestaSignosVitales,idAbmDetallePreConsulta, accion);
}


function verificarcamposEliminarDetallePreConsulta() {
	if (idAbmDetallePreConsulta == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
		// if(controlacceso("EDITARLISTADOCOBRADORES","accion")==false){return;}
	}
	accion = "quitar";
	abmDetallePreConsulta( "-", "-",idAbmDetallePreConsulta, accion);
}


function abmDetallePreConsulta( cod_descripcion, respuesta, idAbm, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_descripcion", cod_descripcion)
	datos.append("respuesta", respuesta)
	datos.append("idAbm", idAbm)
	datos.append("idAbmPreConsulta", cod_preConsultaFK)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmDetallePreConsulta.php",
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
					limpiarcamposDetallePreConsulta()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmDetallePreConsulta = ""
					buscarabmDetallePreConsulta()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}


function limpiarcamposDetallePreConsulta() {
	

	document.getElementById('inptDescripcionSignosVitales').value = "";
	document.getElementById('inptRespuestaSignosVitales').value = "";
	document.getElementById('btnDetallePreConsulta1').value = "Guardar datos";
	document.getElementById('btnDetallePreConsulta2').style.display="none";
	idAbmDetallePreConsulta = "";
}



function obtenerdatosabmDetallePreConsulta(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptDescripcionSignosVitales').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptRespuestaSignosVitales').value = $(datostr).children('td[id="td_datos_2"]').html();
	

	document.getElementById('btnDetallePreConsulta1').value ="Editar datos";
	document.getElementById('btnDetallePreConsulta2').style.display="";
	idAbmDetallePreConsulta = $(datostr).children('td[id="td_id"]').html();

}




function buscarabmDetallePreConsulta() {

	
	document.getElementById("divBuscadorSignosVitales").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idAbmPreConsulta": cod_preConsultaFK,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmDetallePreConsulta.php",
		type: "post",
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
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
          manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("divBuscadorSignosVitales").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorSignosVitales").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("divBuscadorSignosVitales").innerHTML = datos_buscados
										
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}





function GuardarRegistroDetallePreCOnsulta(datos) {
	
	let respuesta = datos.value;
	let cod_DetalleDescripcion = datos.name;
	let cod_detalle = datos.id;
	
	
	if (respuesta == "") {
		ver_vetana_informativa("FALTO INGRESAR EL PARAMETRO", "#")
		return false;
	}
	
	if (cod_preConsultaFK == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	
	var accion = "";
	if (cod_detalle != "0") {
		accion = "editar";
		// if(controlacceso("EDITARLISTADOCOBRADORES","accion")==false){return;}
	} else {		
		accion = "nuevo";
		// if(controlacceso("INSERTARLISTADOCOBRADORES","accion")==false){return;}
	}
		
	abmDetallePreConsulta( cod_DetalleDescripcion, respuesta,cod_detalle, accion);
}



function verCerrarAbmVistaConsulta() { 
	if(document.getElementById("divFrmVistaConsulta").style.display==""){ 
	$("div[id=divFrmVistaConsulta]").fadeOut(500);	
	}else{
		
		 	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	} 
	// document.getElementById('inptBuscarFrmFechaPaciente').value = f.getFullYear() + "-" + mes + "-" + dia;
	
		document.getElementById("divFrmVistaConsulta").style.display="" 
	}
}

 


/*
ABM HISTORIAL Consulta
*/


function verCerrarHistorialConsulta(){

	if(document.getElementById("divHistorialConsulta").style.display==""){
	
	if(controldebusquedadHistorialConsulta==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
document.getElementById("tdEfectoHistorialConsulta").className="magictime vanishOut"
	$("div[id=divHistorialConsulta]").fadeOut(500);
		document.getElementById("divMinimizadoHistorialConsulta").style.display='none'

	}else{
       // if(controlacceso("VERHISTORIALVENTA","accion")==false){ return;}
		document.getElementById("divHistorialConsulta").style.display=""
		document.getElementById("tdEfectoHistorialConsulta").className="magictime slideDownReturn"
			
	}
}

var registrocargadohistorialConsulta="";
var totalregistrohistorialConsulta="";
var controldebusquedadHistorialConsulta=false
function cancelarHistorialConsulta(){
	controldebusquedadHistorialConsulta=false
	document.getElementById("divProgressHistorialConsulta").style.backgroundColor='#ff5722'
}

function minimizarHistorialConsulta(){
	//document.getElementById("divHistorialVenta").style.display='none'
document.getElementById("tdEfectoHistorialConsulta").className="magictime slideDown"
	$("div[id=divHistorialConsulta]").fadeOut(500);	
	document.getElementById("divMinimizadoHistorialConsulta").style.display=''
}

function checkfiltroshistorialConsulta(d){
	if(d=="1"){
	document.getElementById('inptCheckHistorialConsulta1').checked=true
	document.getElementById('inptCheckHistorialConsulta2').checked=false	
     
	 	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarInfHistorialConsultaF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarInfHistorialConsultaF2').value = f.getFullYear() + "-" + mes + "-" + dia;
	 
	}else{		
	document.getElementById('inptCheckHistorialConsulta1').checked=false
	document.getElementById('inptCheckHistorialConsulta2').checked=true
	document.getElementById('inptBuscarInfHistorialConsultaF1').value="";
      document.getElementById('inptBuscarInfHistorialConsultaF2').value="";
	
	}
}


function buscarhistorialConsulta() {    
	
	var fechafiltro = document.getElementById("inptBuscarHistorialConsulta1").value
	var documento = document.getElementById('inptBuscarHistorialConsulta2').value
	var paciente = document.getElementById('inptBuscarHistorialConsulta3').value
	var especialista = document.getElementById('inptBuscarHistorialConsulta4').value
	var fecha1 = document.getElementById('inptBuscarInfHistorialConsultaF1').value
	var fecha2 = document.getElementById('inptBuscarInfHistorialConsultaF2').value
	var local = document.getElementById('inptBuscarHistorialConsulta5').value
	var selectespecialista = document.getElementById('inptBuscarInfHistorialEspecialista').value
	
	
	if(document.getElementById('inptCheckHistorialConsulta1').checked==true){
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
			return
		}
	}
	if(document.getElementById('inptCheckHistorialConsulta2').checked==true){
		var fecha1 = ""
		var fecha2 = ""
	}	
	
/* 	if(controldebusquedadHistorialConsulta==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadHistorialConsulta=true */

	// document.getElementById("tbProcessHistorialConsulta").style.display="none"
	document.getElementById("table_historial_Consulta").innerHTML = paginacargando
	document.getElementById("inptRegistroNroHistorialConsulta").value = "";
    // document.getElementById("inptTotalHistorialConsulta").value = "";
	// document.getElementById("inptTotalComisionHistorialConsulta").value = "";
	// document.getElementById("inptTotalEvaluacionHistorialConsulta").value = "";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,	
		"fecha1": fecha1,
		"fecha2": fecha2,
		"fechafiltro": fechafiltro,
		"documento": documento,
		"paciente": paciente,
		"especialista": especialista,
		"local": local,
		"selectespecialista": selectespecialista,
		"funt": "historialConsulta"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",

		
		beforeSend: function () {

		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_historial_Consulta").innerHTML = ''
			controldebusquedadHistorialConsulta=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_Consulta").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];              
			  Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_historial_Consulta").innerHTML = datos_buscados
					document.getElementById("inptRegistroNroHistorialConsulta").value = datos[3];
					/* document.getElementById("inptTotalHistorialConsulta").value = datos[4];
					document.getElementById("inptTotalComisionHistorialConsulta").value = datos[5];
					document.getElementById("inptTotalEvaluacionHistorialConsulta").value = datos[6];
					registrocargadohistorialConsulta=datos[99];
					totalregistrohistorialConsulta=datos[100];					
						 if(totalregistrohistorialConsulta>registrocargadohistorialConsulta){
						 	var porce=((registrocargadohistorialConsulta*100)/totalregistrohistorialConsulta).toFixed(0)
							document.getElementById("divProgressHistorialConsulta").style.width=porce+"%"
						 document.getElementById("table_historial_Consulta").innerHTML += "<div id='table_mas_historial_Consulta'></div>"
						  buscarMashistorialConsulta();
					 }else{
						 controldebusquedadHistorialConsulta=false
					 } */
					
					}
			} catch (error) {
				controldebusquedadHistorialConsulta=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarMashistorialConsulta() {    
	var fechafiltro = document.getElementById("inptBuscarHistorialConsulta1").value
	var documento = document.getElementById('inptBuscarHistorialConsulta2').value
	var paciente = document.getElementById('inptBuscarHistorialConsulta3').value
	var especialista = document.getElementById('inptBuscarHistorialConsulta4').value
	var usuario = document.getElementById('inptBuscarHistorialConsulta6').value 
	var seguro = document.getElementById('inptBuscarHistorialConsulta7').value
	var fecha1 = document.getElementById('inptBuscarInfHistorialConsultaF1').value
	var fecha2 = document.getElementById('inptBuscarInfHistorialConsultaF2').value
	
	
	if(document.getElementById('inptCheckHistorialConsulta1').checked==true){
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
			return
		}
	}
	if(document.getElementById('inptCheckHistorialConsulta2').checked==true){
		var fecha1 = ""
		var fecha2 = ""
	}	
	
	
	if(controldebusquedadHistorialConsulta==false){
			return
	}
		controldebusquedadHistorialConsulta=true
document.getElementById("tbProcessHistorialConsulta").style.display=""
document.getElementById("divProgressHistorialConsulta").style.backgroundColor=''
	document.getElementById("table_mas_historial_Consulta").innerHTML = paginacargando
    var totalConsulta=document.getElementById("inptTotalHistorialConsulta").value;
	var totalComision=document.getElementById("inptTotalComisionHistorialConsulta").value;
	var totalEvaluacion=document.getElementById("inptTotalEvaluacionHistorialConsulta").value;
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,	
		"fecha1": fecha1,
		"fecha2": fecha2,
		"fechafiltro": fechafiltro,
		"documento": documento,
		"paciente": paciente,
		"especialista": especialista,
		"seguro": seguro,
		"usuario": usuario,
		"totalConsulta": totalConsulta,
		"totalComision": totalComision,
		"totalEvaluacion": totalEvaluacion,
		"registrocargado": registrocargadohistorialConsulta,		
		"funt": "mashistorialConsulta"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",
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
		
		beforeSend: function () {

		},
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_mas_historial_Consulta").innerHTML = ''
			document.getElementById("divProgressHistorialConsulta").style.backgroundColor='#ff5722'
			controldebusquedadHistorialConsulta=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_mas_historial_Consulta").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];              
			  Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("inptRegistroNroHistorialConsulta").value = datos[3];
					document.getElementById("inptTotalHistorialConsulta").value = datos[4];
					document.getElementById("inptTotalComisionHistorialConsulta").value = datos[5];
					document.getElementById("inptTotalEvaluacionHistorialConsulta").value = datos[6];
					document.getElementById("table_mas_historial_Consulta").innerHTML = datos_buscados
						registrocargadohistorialConsulta=datos[99];
					
						 if(totalregistrohistorialConsulta>registrocargadohistorialConsulta){
						 	var porce=((registrocargadohistorialConsulta*100)/totalregistrohistorialConsulta).toFixed(0)
							document.getElementById("divProgressHistorialConsulta").style.width=porce+"%"
				 document.getElementById("table_mas_historial_Consulta").innerHTML += "<div id='table_mas_historial_Consulta'></div>"
						 document.getElementById("table_mas_historial_Consulta").id=""
						  buscarMashistorialConsulta();
					 }else{
						 document.getElementById("tbProcessHistorialConsulta").style.display="none"
						 controldebusquedadHistorialConsulta=false
					 }
					
					}
			} catch (error) {
					document.getElementById("divProgressHistorialConsulta").style.backgroundColor='#ff5722'
					controldebusquedadHistorialConsulta=false
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
}

 

function verCerrarOpcionesCalendario(){
if(document.getElementById("divCalendario").style.display==""){
	
	document.getElementById("tdEfectoOpcionesCalendario").className="magictime vanishOut"
	$("div[id=divCalendario]").fadeOut(500);	
	
}else{
	
	document.getElementById("divCalendario").style.display=""
    document.getElementById("tdEfectoOpcionesCalendario").className="magictime slideDownReturn"
}
}

 

function ImprimirConsultas(){
	var pagina="<div class='divMenuh' >"
	
		var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()+1
	if(mes<10){
		mes="0"+mes;
	}
	var hora =f.getHours()
	if(hora<10){
		hora="0"+hora;
	}
	var min =f.getMinutes()
	if(min<10){
		min="0"+min;
	}
  var fechaimpresion=f.getFullYear()+"-"+mes+"-"+dia;
  document.getElementById("DivImpresionesConsultas").innerHTML=""
  
 
let paginaIndicaciones="";
let paginaReceta="";
let paginaAnalisis="";
let paginaEstudios="";
let paginaConsultas="";
if (TablaConsultaIndicaciones != "" && document.getElementById('inptCheckIndicaciones').checked==true ) {

		paginaIndicaciones =
"<div class='divFloat2' style='width:48%;height: 710px ;margin: 1%;'> "
+"<img src='/GoodVentaAsisCap/iconos/Membrete.jpg' style='width: 100%;border-radius: 5px;height: 115px;' />"
+"<br><center><b class='pTituloD' style='font-weight: 800;font-size: 16px;'>INDICACIONES</b><center><br>"
+"<table class='tableCabeceraRegistro'><tbody><tr><td class='td_registro' style='width:100%;'> DATOS PERSONALES</td></tr></tbody></table>"

+"<table class='td_DatosPersonales' ><tbody><tr><td style='width:100%;'> NOMBRE: "+document.getElementById("inptPacienteConsulta").value+" </td></tr></tbody></table>"
+"<table class='td_DatosPersonales'  ><tbody><tr><td style='width:100%;'> CI: "+document.getElementById("inptCIConsulta").value+" </td></tr></tbody></table>"
+"<table class='td_DatosPersonales'  ><tbody><tr><td style='width:100%;'> FECHA: "+fechaimpresion+"</td></tr></tbody></table>"

 +"<table class='tableCabeceraRegistro'><tbody><tr><td class='td_registro' style='width:100%;'> INDICACIONES ASIGNADOS</td></tr></tbody></table>"
 
 +"<div style='height:400px'>"+TablaConsultaIndicaciones+"</div>"
 
 +"<div class='PieDEPaginaConsultas'></div>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> serviciosmedicosycnia1@gmail.com</td></tr></tbody></table>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> Tel:0541 40634 / 0541 40635</td></tr></tbody></table>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> Villarrica - Paraguay</td></tr></tbody></table>"
+"</div>"

	pagina= pagina + paginaIndicaciones;

document.getElementById("DivImpresionesConsultas").innerHTML=pagina
}

if (TablaConsultaReceta != "" && document.getElementById('inptCheckReceta').checked==true ) {

		paginaReceta =
"<div class='divFloat2' style='width:48%;height: 710px ;margin: 1%;'> "
+"<img src='/GoodVentaAsisCap/iconos/Membrete.jpg' style='width: 100%;border-radius: 5px;height: 115px;' />"
+"<br><center><b class='pTituloD' style='font-weight: 800;font-size: 16px;'>RECETA MEDICA</b><center><br>"
+"<table class='tableCabeceraRegistro'><tbody><tr><td class='td_registro' style='width:100%;'> DATOS PERSONALES</td></tr></tbody></table>"

+"<table class='td_DatosPersonales' ><tbody><tr><td style='width:100%;'> NOMBRE: "+document.getElementById("inptPacienteConsulta").value+" </td></tr></tbody></table>"
+"<table class='td_DatosPersonales'  ><tbody><tr><td style='width:100%;'> CI: "+document.getElementById("inptCIConsulta").value+" </td></tr></tbody></table>"
+"<table class='td_DatosPersonales'  ><tbody><tr><td style='width:100%;'> FECHA: "+fechaimpresion+"</td></tr></tbody></table>"

 +"<table class='tableCabeceraRegistro'><tbody><tr><td class='td_registro' style='width:100%;'> LISTADO DE MEDICAMENTOS </td></tr></tbody></table>"
 
 +"<div style='height:400px'>"+TablaConsultaReceta+"</div>"
 
 +"<div class='PieDEPaginaConsultas'></div>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> serviciosmedicosycnia1@gmail.com</td></tr></tbody></table>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> Tel:0541 40634 / 0541 40635</td></tr></tbody></table>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> Villarrica - Paraguay</td></tr></tbody></table>"
+"</div>"

		pagina= pagina + paginaReceta;

document.getElementById("DivImpresionesConsultas").innerHTML=pagina
}

if (TablaConsultaAnalisis != "" && document.getElementById('inptCheckAnalisis').checked==true) {

		paginaAnalisis =
"<div class='divFloat2' style='width:48%;height: 710px ;margin: 1%;'> "
+"<img src='/GoodVentaAsisCap/iconos/Membrete.jpg' style='width: 100%;border-radius: 5px;height: 115px;' />"
+"<br><center><b class='pTituloD' style='font-weight: 800;font-size: 16px;'>ANALISIS</b><center><br>"
+"<table class='tableCabeceraRegistro'><tbody><tr><td class='td_registro' style='width:100%;'> DATOS PERSONALES</td></tr></tbody></table>"

+"<table class='td_DatosPersonales' ><tbody><tr><td style='width:100%;'> NOMBRE: "+document.getElementById("inptPacienteConsulta").value+" </td></tr></tbody></table>"
+"<table class='td_DatosPersonales'  ><tbody><tr><td style='width:100%;'> CI: "+document.getElementById("inptCIConsulta").value+" </td></tr></tbody></table>"
+"<table class='td_DatosPersonales'  ><tbody><tr><td style='width:100%;'> FECHA: "+fechaimpresion+"</td></tr></tbody></table>"

 +"<table class='tableCabeceraRegistro'><tbody><tr><td class='td_registro' style='width:100%;'> LISTADO DE ANALISIS </td></tr></tbody></table>"
 +"<div style='height:400px'>"+TablaConsultaAnalisis+"</div>"
 
 +"<div class='PieDEPaginaConsultas'></div>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> serviciosmedicosycnia1@gmail.com</td></tr></tbody></table>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> Tel:0541 40634 / 0541 40635</td></tr></tbody></table>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> Villarrica - Paraguay</td></tr></tbody></table>"
+"</div>"

	pagina= pagina + paginaAnalisis;

document.getElementById("DivImpresionesConsultas").innerHTML=pagina
}

if (TablaConsultaEstudios != "" && document.getElementById('inptCheckEstudios').checked==true) {

		paginaEstudios =
"<div class='divFloat2' style='width:48%;height: 710px ;margin: 1%;'> "
+"<img src='/GoodVentaAsisCap/iconos/Membrete.jpg' style='width: 100%;border-radius: 5px;height: 115px;' />"
+"<br><center><b class='pTituloD' style='font-weight: 800;font-size: 16px;'>ESTUDIOS</b><center><br>"
+"<table class='tableCabeceraRegistro'><tbody><tr><td class='td_registro' style='width:100%;'> DATOS PERSONALES</td></tr></tbody></table>"

+"<table class='td_DatosPersonales' ><tbody><tr><td style='width:100%;'> NOMBRE: "+document.getElementById("inptPacienteConsulta").value+" </td></tr></tbody></table>"
+"<table class='td_DatosPersonales'  ><tbody><tr><td style='width:100%;'> CI: "+document.getElementById("inptCIConsulta").value+" </td></tr></tbody></table>"
+"<table class='td_DatosPersonales'  ><tbody><tr><td style='width:100%;'> FECHA: "+fechaimpresion+"</td></tr></tbody></table>"

 +"<table class='tableCabeceraRegistro'><tbody><tr><td class='td_registro' style='width:100%;'> LISTADO DE ESTUDIOS </td></tr></tbody></table>"
 +"<div style='height:400px'>"+TablaConsultaEstudios+"</div>"
 
 +"<div class='PieDEPaginaConsultas'></div>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> serviciosmedicosycnia1@gmail.com</td></tr></tbody></table>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> Tel:0541 40634 / 0541 40635</td></tr></tbody></table>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> Villarrica - Paraguay</td></tr></tbody></table>"
+"</div>"

	pagina= pagina + paginaEstudios;

document.getElementById("DivImpresionesConsultas").innerHTML=pagina
}


if(document.getElementById('inptCheckConsulta').checked==true){
	
paginaConsultas =
"<div class='divFloat2' style='width:48%;height: 710px ;margin: 1%;'> "
+"<img src='/GoodVentaAsisCap/iconos/Membrete.jpg' style='width: 100%;border-radius: 5px;height: 115px;' />"
+"<br><center><b class='pTituloD' style='font-weight: 800;font-size: 16px;'>CONSULTA</b><center><br>"
+"<table class='tableCabeceraRegistro'><tbody><tr><td class='td_registro' style='width:100%;'> DATOS PERSONALES</td></tr></tbody></table>"

+"<table class='td_DatosPersonales' ><tbody><tr><td style='width:100%;'> NOMBRE: "+document.getElementById("inptPacienteConsulta").value+" </td></tr></tbody></table>"
+"<table class='td_DatosPersonales'  ><tbody><tr><td style='width:100%;'> CI: "+document.getElementById("inptCIConsulta").value+" </td></tr></tbody></table>"
+"<table class='td_DatosPersonales'  ><tbody><tr><td style='width:100%;'> FECHA: "+fechaimpresion+"</td></tr></tbody></table>"

 +"<table class='tableCabeceraRegistro'><tbody><tr><td class='td_registro' style='width:100%;'> DESCRIPCION </td></tr></tbody></table>"
 +"<div style='height:400px'>"
 
 +"<table class='td_DatosPersonales' ><tbody><tr><td style='width:100%;'> ESPECIALISTA: "+document.getElementById("inptEspecialistaConsulta").value+" </td></tr></tbody></table> <br>"
 +"<table class='td_DatosPersonales' ><tbody><tr><td style='width:100%;'> MOTIVO: "+document.getElementById("inptMotivoConsulta").value+" </td></tr></tbody></table> <br>"
 +"<table class='td_DatosPersonales' ><tbody><tr><td style='width:100%;'> DIAGNOSTICO: "+document.getElementById("inptDiagnosticoConsulta").value+" </td></tr></tbody></table> <br>"
 
 +"</div>"
 
 +"<div class='PieDEPaginaConsultas'></div>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> serviciosmedicosycnia1@gmail.com</td></tr></tbody></table>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> Tel:0541 40634 / 0541 40635</td></tr></tbody></table>"
 +"<table class='td_PiePagina'  ><tbody><tr><td style='width:100%;'> Villarrica - Paraguay</td></tr></tbody></table>"
+"</div>"

	pagina= pagina + paginaConsultas;

document.getElementById("DivImpresionesConsultas").innerHTML=pagina
	
}





if(pagina=="<div class='divMenuh' style='overflow:auto'>"){
	return false;
}


	var documento=document.getElementById("DivImpresionesConsultas").innerHTML + "</div>";

	 localStorage.setItem("reporte", documento);
	 localStorage.setItem("tipo", "reporte");
	 window.open("/GoodVentaAsisCap/system/reportInformes.html");

}


function Controlcheck(MiCheck){
	
	if(document.getElementById(MiCheck).checked==true){
		document.getElementById(MiCheck).checked=false
	}else{
		document.getElementById(MiCheck).checked=true
	}
	
}










function ordenimpresion2(ventana){
	var pagina=""
	var paginaPie = ""
		var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()+1
	if(mes<10){
		mes="0"+mes;
	}
	var hora =f.getHours()
	if(hora<10){
		hora="0"+hora;
	}
	var min =f.getMinutes()
	if(min<10){
		min="0"+min;
	}
  var fechaimpresion=f.getFullYear()+"-"+mes+"-"+dia;
  document.getElementById("divCabeceraImpresiones").innerHTML=""
document.getElementById("divPieImpresiones").innerHTML=""
document.getElementById("tbTitulosImpresiones").innerHTML=""
document.getElementById("tbDatosImpresiones").innerHTML=""

if (ventana == "historialAgendamiento") {

		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA INICIO:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarInfHistorialAgendamientoF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA FIN:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarInfHistorialAgendamientoF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>ESPECIALISTA:</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarHistorialAgendamiento4").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>SEGURO:</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarHistorialAgendamiento7").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><center><h1 class='pTituloD' >LISTADO DE AGENDAMIENTO</h1><br></center>";

paginaPie =
"<br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptRegistroNroHistorialAgendamiento").value+"</p>"
+"</td>"
+"<td style='width:10%;text-align:left'> </td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalHistorialAgendamiento").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total seguro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalSeguroHistorialAgendamiento").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Pagado</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalPagosHistorialAgendamiento").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Pendiente</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalPendienteHistorialAgendamiento").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Comisión</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalComisionHistorialAgendamiento").value+"</p>"
+"</td>"
+"</tr>"
+"</table>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tbTituloImpreHistorialAgendamiento").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_historial_Agendamiento").innerHTML
document.getElementById("divPieImpresiones").innerHTML=paginaPie
}


if (ventana == "historialTratamiento") {

		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA INICIO:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarInfHistorialTratamientoF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA FIN:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarInfHistorialTratamientoF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>ESPECIALISTA:</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarHistorialTratamiento4").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>CAMA:</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarHistorialTratamiento5").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><center><h1 class='pTituloD' >LISTADO DE TRATAMIENTOS</h1><br></center>";

paginaPie =
"<br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptRegistroNroHistorialTratamiento").value+"</p>"
+"</td>"
+"<td style='width:10%;text-align:left'> </td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalHistorialTratamiento").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"

+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Pagado</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalPagosHistorialTratamiento").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"

+"</td>"
+"<td style='width:15%;text-align:left'>"

+"</td>"
+"</tr>"
+"</table>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tbTituloImpreHistorialTratamiento").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_historial_Tratamiento").innerHTML
document.getElementById("divPieImpresiones").innerHTML=paginaPie
}



if (ventana == "historialConsulta") {

		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA INICIO:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarInfHistorialConsultaF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA FIN:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarInfHistorialConsultaF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>ESPECIALISTA:</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarHistorialConsulta4").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>SEGURO:</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarHistorialConsulta7").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><center><h1 class='pTituloD' >LISTADO DE CONSULTAS</h1><br></center>"

paginaPie =
"<br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptRegistroNroHistorialConsulta").value+"</p>"
+"</td>"
+"<td style='width:10%;text-align:left'> </td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalHistorialConsulta").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total seguro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalSeguroHistorialConsulta").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Pagado</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalPagosHistorialConsulta").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Pendiente</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalPendienteHistorialConsulta").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Comisión</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalComisionHistorialConsulta").value+"</p>"
+"</td>"
+"</tr>"
+"</table>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tbTituloImpreHistorialConsulta").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_historial_Consulta").innerHTML
document.getElementById("divPieImpresiones").innerHTML=paginaPie
}


if (ventana == "GIAdmin") {

		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA INICIO:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarGIAdminF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA FIN:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarGIAdminF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>LOCAL:</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarIngresoGasto6").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>TIPO:</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarIngresoGasto3").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><center><h1 class='pTituloD' >GASTO / INGRESO CAJA ADMINISTRATIVO</h1><br></center>"

paginaPie =
"<br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptRegistroGIAdmin").value+"</p>"
+"</td>"
+"<td style='width:5%;text-align:left'> </td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Gasto </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalGastoAdmin").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Ingreso</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalIngresoAdmin").value+"</p>"
+"</td>"

+"<td style='width:5%;text-align:left'>"
+"</td>"

+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Total Caja Admin</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalBalanceAdmin").value+"</p>"
+"</td>"
+"<td style='width:30%;text-align:left'>"
+"</td>"

+"</tr>"
+"</table>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreGIAdmin1").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_abm_gasto_ingresoAdmin").innerHTML
document.getElementById("divPieImpresiones").innerHTML=paginaPie
}


	var documento=document.getElementById("DivImpresiones").innerHTML;

	 localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "reporte");
	 window.open("/GoodVentaAsisCap/system/reportInformes.html");

}



function buscarVistaConsulta() {	 
	let local = document.getElementById("inptBuscarLocalPaciente").value 
	 

	let paciente =  document.getElementById("inptBuscarFrmPacienteVistaConsulta").value 
 // alert(paciente)
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,   
		"Paciente": paciente, 
		"local": local, 
		"funt": "buscarVistaConsulta"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",
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
		
		beforeSend: function () {


		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_frm_VistaConsulta").innerHTML = ''
 
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_frm_VistaConsulta").innerHTML = ''
	 
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_frm_VistaConsulta").innerHTML = datos_buscados
				 					
				}
			} catch (error) {
				
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}

    
let cod_Agendamiento="";
let cod_ventaFKConsulta="";
let cod_clienteConsulta = "";
function ObtenerdatosAbmConsulta(elemento) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});		
	
	
	cod_Agendamiento= elemento.querySelector('#td_datos_4')?.textContent.trim();
	document.getElementById("inptPacienteConsulta").value= elemento.querySelector('#td_datos_1')?.textContent.trim();
	document.getElementById("inptCIConsulta").value= elemento.querySelector('#td_datos_2')?.textContent.trim();
	document.getElementById("inptCodigoConsulta").value= elemento.querySelector('#td_datos_3')?.textContent.trim();
	document.getElementById("inptApodoConsulta").value= elemento.querySelector('#td_datos_7')?.textContent.trim();
	cod_ventaFKConsulta= elemento.querySelector('#td_datos_5')?.textContent.trim();
	
	cod_clienteConsulta= elemento.querySelector('#td_datos_6')?.textContent.trim();
	document.getElementById("inptEspecialistaConsulta").value = userid
 
	verCerrarAbmConsulta() 
	buscarDetalleVentaConsulta(cod_ventaFKConsulta)
	buscarabmConsultaParaConsulta(cod_ventaFKConsulta)
	vercuotasatrazadas(cod_ventaFKConsulta)
	buscarPacienteConsulta()	
	buscarVistaGaleriaFoto();
	buscarResumenAntecedenteConsulta()
}

function agregarObservacionConsulta(){
	let descripcion = document.getElementById('inputObservacion').value;
	if(descripcion ==''){
		ver_vetana_informativa("FALTO INGRESAR LA OBSERVACIÓN");
		return;
	}
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_clienteConsulta": cod_clienteConsulta,
		"descripcion": descripcion,
		"cod_venta": cod_ventaFKConsulta,
		"funt": "agregar_observacion_consulta"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",

		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
          manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					ver_vetana_informativa('CARGADO CORRECTAMENTE');
buscarPacienteConsulta()				
document.getElementById('inputObservacion').value = '';	
				}
			} catch (error) {
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarPacienteConsulta(){
	document.getElementById("divObservacionConsulta").innerHTML = '';
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_clienteConsulta": cod_clienteConsulta,
		"cod_venta": cod_ventaFKConsulta,
		"funt": "buscar_observacion_consulta"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",

		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
          manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
		  document.getElementById("divObservacionConsulta").innerHTML = '';
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					document.getElementById("divObservacionConsulta").innerHTML = datos[2];	 
				}
			} catch (error) {
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
}



function buscarDetalleVentaConsulta(cod_ventaFKConsulta) {
// if(controlacceso("BUSCARLISTADOCOBRADORES","accion")==false){return;}
 			
	document.getElementById("divPreConsultaDetalle_Consulta").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_venta": cod_ventaFKConsulta,
		"funt": "buscarDetalleCompradoConsulta"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",
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
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
          manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("divPreConsultaDetalle_Consulta").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divPreConsultaDetalle_Consulta").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("divPreConsultaDetalle_Consulta").innerHTML = datos_buscados	
				
				// cod_personaFK="";
				
					
					var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()+1
	if(mes<10){
		mes="0"+mes;
	}
	var hora =f.getHours()
	if(hora<10){
		hora="0"+hora;
	}
	var min =f.getMinutes()
	if(min<10){
		min="0"+min;
	}
	 	document.getElementById('inptFechaConsulta').value=f.getFullYear()+"-"+mes+"-"+dia;
				}
			} catch (error) {
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
}





function buscarabmConsultaParaConsulta(cod_ventaFKConsulta) {
// if(controlacceso("BUSCARLISTADOCOBRADORES","accion")==false){return;}
 			
	document.getElementById("divHistorial_Consulta").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_venta": cod_ventaFKConsulta,
		"funt": "buscarHistorialConsulta"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",
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
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
          manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("divHistorial_Consulta").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divHistorial_Consulta").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("divHistorial_Consulta").innerHTML = datos_buscados	 
				}
			} catch (error) {
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
}

 

function verCerrarAbmConsulta(){
if(document.getElementById("divAbmConsulta").style.display==""){
document.getElementById("tdEfectoAbmConsulta").className="magictime vanishOut"
	$("div[id=divAbmConsulta]").fadeOut(500);
	cod_consulta = "";
 
	limpiarcamposConsulta()
document.getElementById('btn_flotante_consulta').style.display= 'none'
}else{		
	document.getElementById('btn_flotante_consulta').style.display= ''
	document.getElementById("divAbmConsulta").style.display=""
    document.getElementById("tdEfectoAbmConsulta").className="magictime slideDownReturn"
}
}



function limpiarcamposConsulta(){

	var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()+1
	if(mes<10){
		mes="0"+mes;
	}
	var hora =f.getHours()
	if(hora<10){
		hora="0"+hora;
	}
	var min =f.getMinutes()
	if(min<10){
		min="0"+min;
	}
	
	document.getElementById('inptFechaConsulta').value=f.getFullYear()+"-"+mes+"-"+dia;
 
	document.getElementById("inptMotivoConsulta").value="";	
	document.getElementById("inptDiagnosticoConsulta").value=""; 
	document.getElementById("inptTrabajoRealizadoConsulta").value=""; 
	document.getElementById("inptProximaConsultaConsulta").value=""; 
 
	document.getElementById("btnAbmConsulta").value="Guardar Datos"
 
	cod_consulta=""
 	cod_ventaFKConsulta="";
}

let cod_consulta ="";
function VerificarAbmConsulta() {
	
	let inptMotivoConsulta  = document.getElementById("inptMotivoConsulta").value
	let inptDiagnosticoConsulta  = document.getElementById("inptDiagnosticoConsulta").value
	let inptTrabajoRealizadoConsulta  = document.getElementById("inptTrabajoRealizadoConsulta").value
	let inptProximaConsultaConsulta  = document.getElementById("inptProximaConsultaConsulta").value
	let inptFechaConsulta  = document.getElementById("inptFechaConsulta").value
	let inptApodoConsulta  = document.getElementById("inptApodoConsulta").value
	
	var cod_especialista=  document.getElementById("inptEspecialistaConsulta").value
 
	// alert(cod_especialista)
	if(cod_especialista==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN ESPECIALISTA")
		return
	}

	if(inptMotivoConsulta==""){
		ver_vetana_informativa("Falto agregar un motivo")
		return
	}
	
	if(inptTrabajoRealizadoConsulta==""){
		document.getElementById("inptTrabajoRealizadoConsulta").focus()
		ver_vetana_informativa("Falto Agregar el trabajo realizado")
		return
	}
	
	if(inptProximaConsultaConsulta==""){
		document.getElementById("inptProximaConsultaConsulta").focus()
		ver_vetana_informativa("Falto agregar proxima consulta")
		return
	}	
 
	var accion = "nuevo";
	if(cod_consulta!=""){
		accion = "editar";
	}
	
	AbmConsulta(inptApodoConsulta,inptMotivoConsulta,inptDiagnosticoConsulta,inptTrabajoRealizadoConsulta,inptProximaConsultaConsulta,inptFechaConsulta,cod_consulta,cod_especialista,accion)

}


function AbmConsulta(apodo,motivo,diagnostico,trabajoreali,prxtrabajo,fecha,cod_consulta,cod_especialista,accion) {	
		
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
  
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_consulta", cod_consulta)
	datos.append("motivo", motivo)
	datos.append("diagnostico", diagnostico)
	datos.append("prxtrabajo", prxtrabajo)
	datos.append("trabajoreali", trabajoreali)
	datos.append("fecha", fecha)
	datos.append("cod_estecialista", cod_especialista) 
	datos.append("cod_agendamiento", cod_Agendamiento) 
	datos.append("cod_venta", cod_ventaFKConsulta) 
	datos.append("cod_clienteConsulta", cod_clienteConsulta) 
	datos.append("apodo", apodo) 
 
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
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
					
					cod_consulta = datos[2];
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					buscarabmConsultaParaConsulta(cod_ventaFKConsulta)
 
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}



function abrirModal(el) {
  document.getElementById('modalTitulo').innerText = "Consulta Nº " + el.dataset.codconsulta;
  document.getElementById('modalFecha').value = el.dataset.fecha;
  document.getElementById('modalTrabajo').value = el.dataset.trabajo;
  document.getElementById('modalProximo').value = el.dataset.proximo;
  document.getElementById('modalMotivo').value = el.dataset.motivo;
  document.getElementById('modalDiagnostico').value = el.dataset.diagnostico;
  document.getElementById('modalEspecialista').value = el.dataset.especialista;

  document.getElementById("modalConsulta").style.display = "block";
}
 
function cerrarModal() {
  document.getElementById("modalConsulta").style.display = "none";
}

 
function vercuotasatrazadas(cod_ventaFKConsulta) {
// if(controlacceso("BUSCARLISTADOCOBRADORES","accion")==false){return;}
 			
	document.getElementById("inptEstadoCuentaConsulta").value = ""
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_venta": cod_ventaFKConsulta,
		"funt": "vercuotasatrazadas"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",
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
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
          manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("inptEstadoCuentaConsulta").value= datos_buscados	 
				}
			} catch (error) {
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
}




function buscarobtenermedicos(){
	 
	
		 document.getElementById("ListConsultaAgendamiento").innerHTML="" 
		 document.getElementById("inptEspecialistaConsulta").innerHTML=""
		 document.getElementById("inptBuscarInfHistorialEspecialista").innerHTML=""
		 
			obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador, 
			 "cod_venta": cod_localFKUSer, 
			"funt": "obtenermedicos"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmusuarios.php",
			type:"post",
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
		
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana") 
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta) 
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
			if (Respuesta == "exito") {
				
		   var datos_buscados=datos[2];		 
			document.getElementById("ListConsultaAgendamiento").innerHTML=datos_buscados	 
			document.getElementById("inptEspecialistaConsulta").innerHTML="<option value='' >SELECCIONAR</option>"+datos_buscados	
			document.getElementById("inptBuscarInfHistorialEspecialista").innerHTML="<option value='' >SELECCIONAR</option>"+datos_buscados	
			
 
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}

