 
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
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
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
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
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
		ver_vetana_informativa("FALTO INGRESAR LA FECHA")
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
		ver_vetana_informativa("FALTO SELECCIONAR UN SIGNO VITAL")
		return false;
	}
	
	if (inptRespuestaSignosVitales == "") {
		ver_vetana_informativa("FALTO INGRESAR EL PARAMETRO")
		return false;
	}
	
	if (cod_preConsultaFK == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO3")
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
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
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
		ver_vetana_informativa("FALTO INGRESAR EL PARAMETRO")
		return false;
	}
	
	if (cod_preConsultaFK == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
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


var controlVentanaConsulta="";
function verCerrarAbmVistaConsulta(controlVentana, volverAtras= false) { 
	if(document.getElementById("divFrmVistaConsulta").style.display==""){ 
		cerrarModalConsultaLecturaSiAbierta();
		$("div[id=divFrmVistaConsulta]").fadeOut(500);	
		
		if (volverAtras && ventanaAnterior.length > 0) {
			document.getElementById(ventanaAnterior[ventanaAnterior.length - 1]).style.display= "";
			ventanaAnterior.pop();
		}
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

		if (controlVentana !== undefined && controlVentana !== "") {
			controlVentanaConsulta= controlVentana;
		}
		document.getElementById("divFrmVistaConsulta").style.display="" 
		var resultadosVistaConsulta = document.getElementById("table_frm_VistaConsulta");
		if (resultadosVistaConsulta && resultadosVistaConsulta.innerHTML.trim() == "") {
			mostrarEstadoVistaConsultaClinica("inicial");
		}
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

function escribirKpiHistorialConsulta(id, valor) {
	var elemento = document.getElementById(id);
	if (elemento) {
		elemento.textContent = valor;
	}
}

function prepararDashboardHistorialConsulta() {
	escribirKpiHistorialConsulta("kpiHistorialConsultaTotal", "...");
	escribirKpiHistorialConsulta("kpiHistorialConsultaTopEspecialista", "-");
	escribirKpiHistorialConsulta("kpiHistorialConsultaIncompletas", "...");
	escribirKpiHistorialConsulta("kpiHistorialConsultaPeriodo", "...");
}

function actualizarDashboardHistorialConsulta(totalTexto) {
	var contenedor = document.getElementById("table_historial_Consulta");
	var filas = contenedor ? contenedor.querySelectorAll(".consulta-audit-row") : [];
	var conteoEspecialistas = {};
	var incompletas = 0;
	var i;

	for (i = 0; i < filas.length; i++) {
		var especialista = filas[i].getAttribute("data-especialista") || "Sin especialista";
		conteoEspecialistas[especialista] = (conteoEspecialistas[especialista] || 0) + 1;
		if (filas[i].getAttribute("data-incompleta") == "1") {
			incompletas++;
		}
	}

	var topEspecialista = "-";
	var topCantidad = 0;
	for (var nombreEspecialista in conteoEspecialistas) {
		if (conteoEspecialistas[nombreEspecialista] > topCantidad) {
			topEspecialista = nombreEspecialista;
			topCantidad = conteoEspecialistas[nombreEspecialista];
		}
	}
	if (topCantidad > 1) {
		topEspecialista += " (" + topCantidad + ")";
	}

	var fechaExacta = document.getElementById("inptBuscarHistorialConsulta1").value;
	var fecha1 = document.getElementById("inptBuscarInfHistorialConsultaF1").value;
	var fecha2 = document.getElementById("inptBuscarInfHistorialConsultaF2").value;
	var periodo = "Todos";
	if (document.getElementById("inptCheckHistorialConsulta1").checked == true && fecha1 != "" && fecha2 != "") {
		periodo = fecha1 + " al " + fecha2;
	} else if (fechaExacta != "") {
		periodo = fechaExacta;
	}

	escribirKpiHistorialConsulta("kpiHistorialConsultaTotal", totalTexto || filas.length);
	escribirKpiHistorialConsulta("kpiHistorialConsultaTopEspecialista", topEspecialista);
	escribirKpiHistorialConsulta("kpiHistorialConsultaIncompletas", incompletas);
	escribirKpiHistorialConsulta("kpiHistorialConsultaPeriodo", periodo);
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
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN")
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
	prepararDashboardHistorialConsulta();
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
			actualizarDashboardHistorialConsulta("0");
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
					actualizarDashboardHistorialConsulta(datos[3]);
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
				actualizarDashboardHistorialConsulta("0")
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
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN")
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
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_abm_gasto_imprimir_ingresoAdmin").innerHTML
document.getElementById("divPieImpresiones").innerHTML=paginaPie
}


	var documento=document.getElementById("DivImpresiones").innerHTML;

	 localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "reporte");
	 window.open("/GoodVentaAsisCap/system/reportInformes.html");

}



function estadoVistaConsultaClinica(tipo) {
	var estados = {
		inicial: {
			icono: "/GoodVentaAsisCap/iconos/historialmedico.png",
			titulo: "Buscá un paciente",
			texto: "Ingresá el nombre, CI o número de venta para consultar su historial clínico."
		},
		buscando: {
			icono: "/GoodVentaAsisCap/iconos/lupa.png",
			titulo: "Buscando pacientes",
			texto: "Estamos consultando los registros clínicos disponibles."
		},
		sinResultados: {
			icono: "/GoodVentaAsisCap/iconos/cliente.png",
			titulo: "Sin pacientes encontrados",
			texto: "Probá con otro nombre, CI o número de venta."
		},
		error: {
			icono: "/GoodVentaAsisCap/iconos/botonCerrar.png",
			titulo: "No se pudo cargar el listado",
			texto: "Intentá nuevamente o revisá la conexión."
		}
	};
	var estado = estados[tipo] || estados.inicial;
	return "<div class='vista-consulta-empty-state vista-consulta-empty-state--" + tipo + "'>" +
		"<img src='" + estado.icono + "' alt='' />" +
		"<strong>" + estado.titulo + "</strong>" +
		"<span>" + estado.texto + "</span>" +
		"</div>";
}

function mostrarEstadoVistaConsultaClinica(tipo) {
	var contenedor = document.getElementById("table_frm_VistaConsulta");
	if (contenedor) {
		contenedor.classList.remove("vista-consulta-results--loaded");
		contenedor.innerHTML = estadoVistaConsultaClinica(tipo || "inicial");
	}
}

function limpiarVistaConsultaClinica() {
	var nroVenta = document.getElementById("inptBuscarFrmNumFacturaVistaConsulta");
	var paciente = document.getElementById("inptBuscarFrmPacienteVistaConsulta");
	if (nroVenta) nroVenta.value = "";
	if (paciente) {
		paciente.value = "";
		paciente.focus();
	}
	mostrarEstadoVistaConsultaClinica("inicial");
}

function buscarVistaConsulta() {	 
	const paciente =  document.getElementById("inptBuscarFrmPacienteVistaConsulta").value
	const num_factura= document.getElementById("inptBuscarFrmNumFacturaVistaConsulta").value;
 // alert(paciente)
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,   
		"Paciente": paciente, 
		"num_factura": num_factura, 
		"funt": "buscarVistaConsulta"
	};

	verCerrarEfectoCargando("1");
	mostrarEstadoVistaConsultaClinica("buscando");
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
			mostrarEstadoVistaConsultaClinica("error");
	verCerrarEfectoCargando("");
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_frm_VistaConsulta").innerHTML = ''
			verCerrarEfectoCargando("");
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					if (datos_buscados && datos_buscados.replace(/\s+/g, "") != "") {
						var contenedorVistaConsulta = document.getElementById("table_frm_VistaConsulta");
						contenedorVistaConsulta.classList.add("vista-consulta-results--loaded");
						contenedorVistaConsulta.innerHTML = datos_buscados
						autoSeleccionarVentaAtencionAgendaConsulta();
					} else {
						mostrarEstadoVistaConsultaClinica("sinResultados");
					}
				 					
				} else {
					mostrarEstadoVistaConsultaClinica("sinResultados");
				}
			} catch (error) {
				
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				mostrarEstadoVistaConsultaClinica("error");
			}
		}
	});


}

    
let cod_Agendamiento="";
let cod_ventaFKConsulta="";
let cod_clienteConsulta = "";
let cod_localVentaConsulta = "";
let nombre_localVentaConsulta = "";
let contextoAtencionAgendaConsulta = {
	activo: false,
	id_agenda: "",
	cod_venta: "",
	cod_cliente: "",
	paciente: "",
	cedula: "",
	fecha: "",
	horario: "",
	doctor: "",
	tratamientos_ids: [],
	tratamientos_texto: [],
	autoseleccionPendiente: false
};
var insumosFichaClinicaConsultaSeleccionados = {};
var insumosFichaClinicaConsultaCatalogo = [];
var temporizadorBuscarInsumosFichaClinicaConsulta = null;
var solicitudInsumosFichaClinicaConsulta = 0;

function normalizarIdsAgendaConsulta(ids) {
	var salida = [];
	var mapa = {};
	if (!ids) { return salida; }
	for (var i = 0; i < ids.length; i++) {
		var id = String(ids[i] || "").trim();
		if (id != "" && !mapa[id]) {
			mapa[id] = true;
			salida.push(id);
		}
	}
	return salida;
}

function textosTratamientosAgendaConsulta(texto) {
	var partes = String(texto || "").split(/<br\s*\/?>/i);
	var salida = [];
	for (var i = 0; i < partes.length; i++) {
		var limpio = partes[i].replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
		if (limpio != "") { salida.push(limpio); }
	}
	return salida;
}

function limpiarContextoAtencionAgendaConsulta() {
	if (typeof idAbmAgenda !== "undefined") {
		idAbmAgenda = "";
	}
	contextoAtencionAgendaConsulta = {
		activo: false,
		id_agenda: "",
		cod_venta: "",
		cod_cliente: "",
		paciente: "",
		cedula: "",
		fecha: "",
		horario: "",
		doctor: "",
		tratamientos_ids: [],
		tratamientos_texto: [],
		autoseleccionPendiente: false
	};
	insumosFichaClinicaConsultaSeleccionados = {};
	insumosFichaClinicaConsultaCatalogo = [];
	contextoAgendaPlanMadreConsulta = {
		id_agenda: "",
		cargado: false,
		agenda: { existe: false },
		tratamientos: [],
		ids: [],
		idsMapa: {}
	};
	var guia = document.getElementById("consultaAgendaAtencionGuia");
	if (guia) {
		guia.style.display = "none";
		guia.innerHTML = "";
	}
	var marcados = document.querySelectorAll("#divPreConsultaDetalle_Consulta .plan-definitivo-item--agenda-target");
	for (var i = 0; i < marcados.length; i++) {
		marcados[i].classList.remove("plan-definitivo-item--agenda-target");
		var badge = marcados[i].querySelector(".consulta-agenda-target-badge");
		if (badge && badge.parentNode) { badge.parentNode.removeChild(badge); }
	}
}

function prepararAtencionAgendaParaConsulta(evento, autoseleccionar) {
	evento = evento || {};
	var ids = normalizarIdsAgendaConsulta(evento.tratamientos_ids || []);
	if (ids.length == 0 && evento.cod_detalle_ventaFK) {
		ids = normalizarIdsAgendaConsulta([evento.cod_detalle_ventaFK]);
	}
	contextoAtencionAgendaConsulta = {
		activo: true,
		id_agenda: String(evento.id || "").trim(),
		cod_venta: String(evento.cod_ventaFK || "").trim(),
		cod_cliente: String(evento.cod_cliente || evento.id_paciente || "").trim(),
		paciente: String(evento.paciente || "").trim(),
		cedula: String(evento.ci_cliente || "").replace(/\./g, "").trim(),
		fecha: String(evento.fecha || "").trim(),
		horario: (String(evento.inicio || "").trim() + (evento.fin ? " a " + String(evento.fin).trim() : "")).trim(),
		doctor: String(evento.nombre_doctor || "").trim(),
		tratamientos_ids: ids,
		tratamientos_texto: textosTratamientosAgendaConsulta(evento.nombres_tratamiento || evento.nombre_tratamiento_pendiente || ""),
		autoseleccionPendiente: autoseleccionar === true
	};
	insumosFichaClinicaConsultaSeleccionados = {};
	insumosFichaClinicaConsultaCatalogo = [];
	if (typeof idAbmAgenda !== "undefined" && contextoAtencionAgendaConsulta.id_agenda != "") {
		idAbmAgenda = contextoAtencionAgendaConsulta.id_agenda;
	}
	contextoAgendaPlanMadreConsulta = {
		id_agenda: "",
		cargado: false,
		agenda: { existe: false },
		tratamientos: [],
		ids: [],
		idsMapa: {}
	};
}

function autoSeleccionarVentaAtencionAgendaConsulta() {
	if (!contextoAtencionAgendaConsulta.activo || !contextoAtencionAgendaConsulta.autoseleccionPendiente) { return; }
	var tarjetas = document.querySelectorAll("#table_frm_VistaConsulta .tarjeta-paciente");
	if (!tarjetas.length) { return; }
	var objetivo = null;
	for (var i = 0; i < tarjetas.length; i++) {
		var venta = tarjetas[i].querySelector("#td_datos_5");
		if (venta && String(venta.textContent || "").trim() == contextoAtencionAgendaConsulta.cod_venta) {
			objetivo = tarjetas[i];
			break;
		}
	}
	if (!objetivo && contextoAtencionAgendaConsulta.cod_venta != "") {
		contextoAtencionAgendaConsulta.autoseleccionPendiente = false;
		return;
	}
	if (!objetivo) { objetivo = tarjetas[0]; }
	contextoAtencionAgendaConsulta.autoseleccionPendiente = false;
	setTimeout(function () {
		ObtenerdatosAbmConsulta(objetivo);
	}, 30);
}

function renderizarGuiaAtencionAgendaConsulta() {
	var guia = document.getElementById("consultaAgendaAtencionGuia");
	if (!guia) { return; }
	if (!contextoAtencionAgendaConsulta.activo) {
		guia.style.display = "none";
		guia.innerHTML = "";
		return;
	}
	var ctx = contextoAtencionAgendaConsulta;
	var fecha = ctx.fecha;
	if (fecha && fecha.indexOf("-") > -1) {
		var partesFecha = fecha.split("-");
		if (partesFecha.length == 3) { fecha = partesFecha[2] + "/" + partesFecha[1] + "/" + partesFecha[0]; }
	}
	var tratamientos = "";
	if (ctx.tratamientos_texto.length > 0) {
		for (var i = 0; i < ctx.tratamientos_texto.length; i++) {
			tratamientos += "<span>" + escaparHtmlConsulta(ctx.tratamientos_texto[i]) + "</span>";
		}
	} else {
		tratamientos = "<span class='consulta-agenda-guide__empty'>Esta cita no tiene tratamiento seleccionado.</span>";
	}
	guia.innerHTML = ""
		+ "<div class='consulta-agenda-guide__main'>"
		+ "	<div><span>Atencion desde calendario</span><strong>Tratamiento de esta cita</strong><small>" + escaparHtmlConsulta(fecha || "Fecha no definida") + (ctx.horario ? " - " + escaparHtmlConsulta(ctx.horario) : "") + (ctx.doctor ? " - " + escaparHtmlConsulta(ctx.doctor) : "") + "</small></div>"
		+ "	<div class='consulta-agenda-guide__treatments'>" + tratamientos + "</div>"
		+ "</div>"
		+ "<div class='consulta-agenda-guide__actions'>"
		+ "	<button type='button' class='consulta-agenda-guide__insumos-btn' onclick='toggleInsumosFichaClinicaConsulta()'><i class='fa-solid fa-box-open' aria-hidden='true'></i> Insumos utilizados <b id='contadorInsumosFichaClinicaConsulta'>...</b></button>"
		+ "	<button type='button' class='consulta-agenda-guide__primary' onclick='abrirNuevaConsultaGuiadaDesdeAgendaConsulta()'>Registrar evolucion</button>"
		+ "	<button type='button' onclick='cambiarTabFichaClinicaConsulta(\"plan\")'>Ver plan madre</button>"
		+ "	<button type='button' onclick='cambiarTabFichaClinicaConsulta(\"evolucion\")'>Ver historial</button>"
		+ "</div>"
		+ "<section class='consulta-insumos-clinica' id='panelInsumosFichaClinicaConsulta' hidden>"
		+ "	<header><div><span>Consumo de esta cita</span><strong>Insumos utilizados</strong></div><button type='button' onclick='toggleInsumosFichaClinicaConsulta()' aria-label='Cerrar insumos'><i class='fa-solid fa-xmark'></i></button></header>"
		+ "	<div class='consulta-insumos-clinica__lista' id='listaInsumosFichaClinicaConsulta'><div class='consulta-insumos-clinica__vacio'>Cargando insumos...</div></div>"
		+ "	<div class='consulta-insumos-clinica__acciones'><button type='button' onclick='toggleAgregarInsumosFichaClinicaConsulta(true)'><i class='fa-solid fa-plus'></i> Agregar insumos</button></div>"
		+ "	<div class='consulta-insumos-clinica__editor' id='editorInsumosFichaClinicaConsulta' hidden>"
		+ "		<div class='consulta-insumos-clinica__buscador'><label>Buscar insumos disponibles</label><input type='search' id='buscarInsumosFichaClinicaConsulta' placeholder='Nombre, codigo, descripcion o variante' oninput='programarBusquedaInsumosFichaClinicaConsulta(this.value)'></div>"
		+ "		<div class='consulta-insumos-clinica__catalogo' id='catalogoInsumosFichaClinicaConsulta'></div>"
		+ "		<div class='consulta-insumos-clinica__seleccion' id='resumenSeleccionInsumosFichaClinicaConsulta'>0 insumos seleccionados</div>"
		+ "		<footer><button type='button' onclick='toggleAgregarInsumosFichaClinicaConsulta(false)'>Cancelar</button><button type='button' class='is-primary' id='guardarInsumosFichaClinicaConsulta' onclick='guardarInsumosFichaClinicaConsulta()'>Guardar insumos</button></footer>"
		+ "	</div>"
		+ "</section>";
	guia.style.display = "";
	setTimeout(function () { cargarInsumosFichaClinicaConsulta(false); }, 0);
}

function formatearCantidadInsumosFichaClinicaConsulta(valor) {
	var numero = parseFloat(String(valor == null ? 0 : valor).replace(",", "."));
	if (isNaN(numero)) { return "0"; }
	return numero.toFixed(3).replace(/0+$/, "").replace(/\.$/, "");
}

function toggleInsumosFichaClinicaConsulta() {
	var panel = document.getElementById("panelInsumosFichaClinicaConsulta");
	if (!panel || !contextoAtencionAgendaConsulta.activo || contextoAtencionAgendaConsulta.id_agenda == "") {
		ver_vetana_informativa("No se pudo identificar la cita.", "", "error");
		return;
	}
	panel.hidden = !panel.hidden;
	if (!panel.hidden) {
		cargarInsumosFichaClinicaConsulta(true);
	}
}

function cargarInsumosFichaClinicaConsulta(mostrarCargando) {
	var idAgenda = contextoAtencionAgendaConsulta.id_agenda;
	var lista = document.getElementById("listaInsumosFichaClinicaConsulta");
	if (!idAgenda) { return; }
	if (lista && mostrarCargando) {
		lista.innerHTML = "<div class='consulta-insumos-clinica__vacio'>Cargando insumos...</div>";
	}
	obtener_datos_user();
	$.ajax({
		data: {
			"useru": userid,
			"passu": passuser,
			"navegador": navegador,
			"id_agenda": idAgenda,
			"funt": "listarConsumosFichaClinica"
		},
		url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
		type: "post",
		success: function (responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuestaJqueryAjax(respuesta["1"]) != true) {
					if (lista) { lista.innerHTML = "<div class='consulta-insumos-clinica__vacio'>" + escaparHtmlConsulta(respuesta.mensaje || "No se pudieron cargar los insumos.") + "</div>"; }
					return;
				}
				var filas = respuesta.filas || [];
				var contador = document.getElementById("contadorInsumosFichaClinicaConsulta");
				if (contador) { contador.textContent = filas.length; }
				if (!lista) { return; }
				var html = "";
				for (var i = 0; i < filas.length; i++) {
					var fila = filas[i];
					var nombre = fila.nombre_insumo + (fila.nombre_variante ? " - " + fila.nombre_variante : "");
					var estado = fila.estado_stock || "previsto";
					var etiquetaEstado = estado == "descontado" ? "Descontado"
						: (estado == "sin_stock" ? "Sin stock" : (estado == "pendiente_atencion" ? "Pendiente de atencion" : "Previsto"));
					html += "<article class='consulta-insumos-clinica__item is-" + escaparHtmlConsulta(estado) + "'>";
					html += "<div><strong>" + escaparHtmlConsulta(nombre) + "</strong><small>Stock actual: " + escaparHtmlConsulta(formatearCantidadInsumosFichaClinicaConsulta(fila.stock_actual)) + "</small></div>";
					html += "<b>" + escaparHtmlConsulta(formatearCantidadInsumosFichaClinicaConsulta(fila.cantidad)) + (fila.unidad_medida ? " " + escaparHtmlConsulta(fila.unidad_medida) : "") + "</b>";
					html += "<span>" + escaparHtmlConsulta(etiquetaEstado) + "</span></article>";
				}
				lista.innerHTML = html || "<div class='consulta-insumos-clinica__vacio'>Todavia no hay insumos registrados para esta cita.</div>";
			} catch (error) {
				if (lista) { lista.innerHTML = "<div class='consulta-insumos-clinica__vacio'>No se pudieron cargar los insumos.</div>"; }
			}
		}
	});
}

function toggleAgregarInsumosFichaClinicaConsulta(mostrar) {
	var editor = document.getElementById("editorInsumosFichaClinicaConsulta");
	if (!editor) { return; }
	editor.hidden = !mostrar;
	if (mostrar) {
		insumosFichaClinicaConsultaSeleccionados = {};
		actualizarResumenSeleccionInsumosFichaClinicaConsulta();
		var buscador = document.getElementById("buscarInsumosFichaClinicaConsulta");
		if (buscador) { buscador.value = ""; }
		buscarInsumosFichaClinicaConsulta("");
	} else {
		insumosFichaClinicaConsultaSeleccionados = {};
	}
}

function programarBusquedaInsumosFichaClinicaConsulta(texto) {
	if (temporizadorBuscarInsumosFichaClinicaConsulta) {
		clearTimeout(temporizadorBuscarInsumosFichaClinicaConsulta);
	}
	temporizadorBuscarInsumosFichaClinicaConsulta = setTimeout(function () {
		buscarInsumosFichaClinicaConsulta(texto);
	}, 250);
}

function buscarInsumosFichaClinicaConsulta(texto) {
	var idAgenda = contextoAtencionAgendaConsulta.id_agenda;
	var catalogo = document.getElementById("catalogoInsumosFichaClinicaConsulta");
	if (!idAgenda || !catalogo) { return; }
	var solicitud = ++solicitudInsumosFichaClinicaConsulta;
	catalogo.innerHTML = "<div class='consulta-insumos-clinica__vacio'>Buscando insumos...</div>";
	obtener_datos_user();
	$.ajax({
		data: {
			"useru": userid,
			"passu": passuser,
			"navegador": navegador,
			"id_agenda": idAgenda,
			"buscar": texto || "",
			"funt": "buscarInsumosFichaClinica"
		},
		url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
		type: "post",
		success: function (responseText) {
			if (solicitud != solicitudInsumosFichaClinicaConsulta) { return; }
			try {
				var respuesta = $.parseJSON(responseText);
				insumosFichaClinicaConsultaCatalogo = respuesta.filas || [];
				renderCatalogoInsumosFichaClinicaConsulta();
			} catch (error) {
				catalogo.innerHTML = "<div class='consulta-insumos-clinica__vacio'>No se pudo cargar el catalogo.</div>";
			}
		}
	});
}

function renderCatalogoInsumosFichaClinicaConsulta() {
	var catalogo = document.getElementById("catalogoInsumosFichaClinicaConsulta");
	if (!catalogo) { return; }
	var html = "";
	for (var i = 0; i < insumosFichaClinicaConsultaCatalogo.length; i++) {
		var fila = insumosFichaClinicaConsultaCatalogo[i];
		var clave = String(fila.id_insumo) + ":" + String(fila.id_variante || 0);
		var seleccionado = !!insumosFichaClinicaConsultaSeleccionados[clave];
		var bloqueado = String(fila.ya_registrado) == "1";
		var nombre = fila.nombre_insumo + (fila.nombre_variante ? " - " + fila.nombre_variante : "");
		var cantidad = seleccionado ? insumosFichaClinicaConsultaSeleccionados[clave].cantidad : "";
		html += "<article class='consulta-insumos-clinica__catalogo-item" + (seleccionado ? " is-selected" : "") + (bloqueado ? " is-disabled" : "") + "'>";
		html += "<input type='checkbox' " + (seleccionado ? "checked" : "") + " onchange='seleccionarInsumoFichaClinicaConsulta(" + parseInt(fila.id_insumo, 10) + "," + parseInt(fila.id_variante || 0, 10) + ",this.checked)'>";
		html += "<div><strong>" + escaparHtmlConsulta(nombre) + "</strong><small>" + escaparHtmlConsulta(fila.descripcion || "Sin descripcion") + " &middot; Stock " + escaparHtmlConsulta(formatearCantidadInsumosFichaClinicaConsulta(fila.stock_actual)) + "</small></div>";
		html += bloqueado ? "<em>Ya registrado</em>" : "<input type='number' min='0.001' step='any' placeholder='Cantidad' value='" + escaparHtmlConsulta(cantidad) + "' " + (seleccionado ? "" : "disabled") + " oninput='actualizarCantidadInsumoFichaClinicaConsulta(\"" + clave + "\",this.value)'>";
		html += "</article>";
	}
	catalogo.innerHTML = html || "<div class='consulta-insumos-clinica__vacio'>No se encontraron insumos disponibles.</div>";
}

function seleccionarInsumoFichaClinicaConsulta(idInsumo, idVariante, seleccionado) {
	var clave = String(idInsumo) + ":" + String(idVariante || 0);
	if (seleccionado) {
		var fila = null;
		for (var i = 0; i < insumosFichaClinicaConsultaCatalogo.length; i++) {
			if (String(insumosFichaClinicaConsultaCatalogo[i].id_insumo) == String(idInsumo)
				&& String(insumosFichaClinicaConsultaCatalogo[i].id_variante || 0) == String(idVariante || 0)) {
				fila = insumosFichaClinicaConsultaCatalogo[i];
				break;
			}
		}
		if (!fila || String(fila.ya_registrado) == "1") {
			ver_vetana_informativa("Ese insumo ya existe en esta consulta.", "", "error");
			renderCatalogoInsumosFichaClinicaConsulta();
			return;
		}
		var claves = Object.keys(insumosFichaClinicaConsultaSeleccionados);
		for (var c = 0; c < claves.length; c++) {
			if (String(insumosFichaClinicaConsultaSeleccionados[claves[c]].id_insumo) == String(idInsumo)) {
				delete insumosFichaClinicaConsultaSeleccionados[claves[c]];
			}
		}
		insumosFichaClinicaConsultaSeleccionados[clave] = {
			id_insumo: idInsumo,
			id_variante: idVariante || 0,
			nombre: fila.nombre_insumo + (fila.nombre_variante ? " - " + fila.nombre_variante : ""),
			unidad_medida: fila.unidad_medida || "",
			cantidad: ""
		};
	} else {
		delete insumosFichaClinicaConsultaSeleccionados[clave];
	}
	renderCatalogoInsumosFichaClinicaConsulta();
	actualizarResumenSeleccionInsumosFichaClinicaConsulta();
}

function actualizarCantidadInsumoFichaClinicaConsulta(clave, cantidad) {
	if (insumosFichaClinicaConsultaSeleccionados[clave]) {
		insumosFichaClinicaConsultaSeleccionados[clave].cantidad = cantidad;
	}
}

function actualizarResumenSeleccionInsumosFichaClinicaConsulta() {
	var total = Object.keys(insumosFichaClinicaConsultaSeleccionados).length;
	var resumen = document.getElementById("resumenSeleccionInsumosFichaClinicaConsulta");
	if (resumen) {
		resumen.textContent = total + (total == 1 ? " insumo seleccionado" : " insumos seleccionados");
	}
}

function guardarInsumosFichaClinicaConsulta() {
	var claves = Object.keys(insumosFichaClinicaConsultaSeleccionados);
	if (claves.length == 0) {
		ver_vetana_informativa("Seleccione al menos un insumo.", "", "error");
		return;
	}
	var detalleConfirmacion = [];
	for (var i = 0; i < claves.length; i++) {
		var item = insumosFichaClinicaConsultaSeleccionados[claves[i]];
		if (Number(item.cantidad) <= 0) {
			ver_vetana_informativa("Ingrese una cantidad valida para todos los insumos.", "", "error");
			return;
		}
		detalleConfirmacion.push("- " + item.nombre + ": " + formatearCantidadInsumosFichaClinicaConsulta(item.cantidad) + (item.unidad_medida ? " " + item.unidad_medida : ""));
	}
	if (!confirm("Revise los insumos utilizados:\n\n" + detalleConfirmacion.join("\n") + "\n\nTodo esta correcto? Al confirmar no podra editar ni eliminar directamente este registro.")) {
		return;
	}
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("id_agenda", contextoAtencionAgendaConsulta.id_agenda);
	datos.append("funt", "guardarInsumosFichaClinica");
	for (var d = 0; d < claves.length; d++) {
		var seleccionado = insumosFichaClinicaConsultaSeleccionados[claves[d]];
		datos.append("id_insumo[]", seleccionado.id_insumo);
		datos.append("id_variante[]", seleccionado.id_variante || 0);
		datos.append("cantidad[]", seleccionado.cantidad);
	}
	var boton = document.getElementById("guardarInsumosFichaClinicaConsulta");
	if (boton) { boton.disabled = true; }
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		complete: function () { if (boton) { boton.disabled = false; } },
		success: function (responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuestaJqueryAjax(respuesta["1"]) == true) {
					ver_vetana_informativa(respuesta.mensaje || "Insumos registrados.");
					insumosFichaClinicaConsultaSeleccionados = {};
					toggleAgregarInsumosFichaClinicaConsulta(false);
					cargarInsumosFichaClinicaConsulta(true);
					if (typeof buscarDashboardInsumosStock == "function") { buscarDashboardInsumosStock(); }
					if (typeof listarAlertasStockInsumos == "function") { listarAlertasStockInsumos(); }
				} else {
					ver_vetana_informativa(respuesta.mensaje || "No se pudieron guardar los insumos.", "", "error");
				}
			} catch (error) {
				GuardarArchivosLog("Error guardarInsumosFichaClinicaConsulta: " + error + " \r\n Consola: " + responseText);
			}
		}
	});
}

function destacarTratamientosAgendaEnPlanConsulta() {
	var items = document.querySelectorAll("#divPreConsultaDetalle_Consulta .plan-definitivo-item[data-detalle-tratamiento]");
	var mapa = {};
	var ids = contextoAtencionAgendaConsulta.tratamientos_ids || [];
	if (!contextoAtencionAgendaConsulta.activo || ids.length == 0) {
		for (var q = 0; q < items.length; q++) {
			items[q].classList.remove("plan-definitivo-item--agenda-target");
		}
		return;
	}
	for (var i = 0; i < ids.length; i++) { mapa[String(ids[i])] = true; }
	for (var j = 0; j < items.length; j++) {
		var detalle = items[j].getAttribute("data-detalle-tratamiento") || "";
		var esObjetivo = !!mapa[String(detalle)];
		items[j].classList.toggle("plan-definitivo-item--agenda-target", esObjetivo);
		var top = items[j].querySelector(".plan-definitivo-item__top");
		var badge = items[j].querySelector(".consulta-agenda-target-badge");
		if (esObjetivo && top && !badge) {
			var etiqueta = document.createElement("em");
			etiqueta.className = "consulta-agenda-target-badge";
			etiqueta.textContent = "Tratamiento de esta cita";
			top.appendChild(etiqueta);
		} else if (!esObjetivo && badge && badge.parentNode) {
			badge.parentNode.removeChild(badge);
		}
	}
}

function aplicarContextoAtencionAgendaEnFicha() {
	if (!contextoAtencionAgendaConsulta.activo) {
		renderizarGuiaAtencionAgendaConsulta();
		return;
	}
	if (contextoAtencionAgendaConsulta.cod_venta != "" && cod_ventaFKConsulta != "" && String(contextoAtencionAgendaConsulta.cod_venta) != String(cod_ventaFKConsulta)) {
		limpiarContextoAtencionAgendaConsulta();
		renderizarGuiaAtencionAgendaConsulta();
		return;
	}
	if (contextoAtencionAgendaConsulta.id_agenda != "") {
		cod_Agendamiento = contextoAtencionAgendaConsulta.id_agenda;
		if (typeof idAbmAgenda !== "undefined") {
			idAbmAgenda = contextoAtencionAgendaConsulta.id_agenda;
		}
	}
	renderizarGuiaAtencionAgendaConsulta();
	destacarTratamientosAgendaEnPlanConsulta();
	if (typeof cambiarTabFichaClinicaConsulta == "function") {
		cambiarTabFichaClinicaConsulta("evolucion");
	}
}

function obtenerDetallePreferidoAgendaConsulta(opciones) {
	if (!contextoAtencionAgendaConsulta.activo) { return ""; }
	var mapaOpciones = {};
	var primeraCoincidencia = "";
	for (var i = 0; i < opciones.length; i++) {
		mapaOpciones[String(opciones[i].detalle)] = opciones[i];
	}
	var candidatos = normalizarIdsAgendaConsulta(contextoAtencionAgendaConsulta.tratamientos_ids || []);
	var idsAgenda = contextoAgendaPlanMadreConsulta.ids || [];
	for (var a = 0; a < idsAgenda.length; a++) {
		candidatos.push(String(idsAgenda[a]));
	}
	candidatos = normalizarIdsAgendaConsulta(candidatos);
	for (var j = 0; j < candidatos.length; j++) {
		var opcion = mapaOpciones[String(candidatos[j])];
		if (!opcion) { continue; }
		if (primeraCoincidencia == "") { primeraCoincidencia = opcion.detalle; }
		if (opcion.avance < 100 && String(opcion.estadoClase || "").toLowerCase() != "completado") {
			return opcion.detalle;
		}
	}
	return primeraCoincidencia;
}

function preseleccionarTratamientoAgendaConsulta(opciones, forzar) {
	var select = document.getElementById("inptTratamientoPlanMadreConsulta");
	if (!select || !contextoAtencionAgendaConsulta.activo) { return false; }
	if (select.value != "" && forzar !== true) { return false; }
	var detalle = obtenerDetallePreferidoAgendaConsulta(opciones || []);
	if (detalle == "") { return false; }
	select.value = detalle;
	sincronizarTratamientoRealizadoConsulta();
	destacarTratamientosAgendaEnPlanConsulta();
	return true;
}

function abrirNuevaConsultaGuiadaDesdeAgendaConsulta() {
	if (typeof cambiarTabFichaClinicaConsulta == "function") {
		cambiarTabFichaClinicaConsulta("evolucion");
	}
	verCerrarAbmDetalleConsulta(true);
	setTimeout(function () {
		if (typeof cargarTratamientosPlanMadreParaConsulta == "function") {
			cargarTratamientosPlanMadreParaConsulta(false);
		}
	}, 120);
}

function obtenerNombreResponsableConsulta() {
	var nombre = "";
	try {
		nombre = localStorage.getItem("nombreUsuario" + userid) || "";
	} catch (error) {
		nombre = "";
	}
	return nombre.trim() != "" ? nombre : "Usuario actual";
}

function obtenerInicialesResponsableConsulta(nombre) {
	var iniciales = "";
	var partes = (nombre || "").trim().split(/\s+/);
	for (var i = 0; i < partes.length && iniciales.length < 2; i++) {
		if (partes[i] != "") {
			iniciales += partes[i].charAt(0);
		}
	}
	return (iniciales || "U").toUpperCase();
}

function actualizarResponsableRegistroConsulta() {
	if (typeof obtener_datos_user == "function") {
		obtener_datos_user();
	}

	var especialista = document.getElementById("inptEspecialistaConsulta");
	if (especialista) {
		especialista.value = userid;
	}

	var nombre = obtenerNombreResponsableConsulta();
	var nombreElemento = document.getElementById("consultaResponsableNombre");
	if (nombreElemento) {
		nombreElemento.textContent = nombre;
	}

	var avatar = document.getElementById("consultaResponsableAvatar");
	if (!avatar) {
		return;
	}

	avatar.textContent = obtenerInicialesResponsableConsulta(nombre);
	avatar.style.backgroundImage = "";
	avatar.classList.remove("clinical-register-owner__avatar--image");

	var foto = "";
	if (typeof fotocliente3 != "undefined") {
		foto = fotocliente3;
	}
	if (typeof normalizarFotoUsuario == "function") {
		foto = normalizarFotoUsuario(foto);
	}

	if (foto && foto.indexOf("sinperfil.png") == -1) {
		avatar.style.backgroundImage = "url(" + foto + ")";
		avatar.textContent = "";
		avatar.classList.add("clinical-register-owner__avatar--image");
	}
}

function ObtenerdatosAbmConsulta(elemento) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});		
	
	cod_Agendamiento= elemento.querySelector('#td_datos_4')?.textContent.trim();
	cod_ventaFKConsulta= elemento.querySelector('#td_datos_5')?.textContent.trim();
	cod_clienteConsulta= elemento.querySelector('#td_datos_6')?.textContent.trim();
	nombre_localVentaConsulta= elemento.querySelector('#td_datos_8')?.textContent.trim() || "";
	cod_localVentaConsulta= elemento.querySelector('#td_datos_9')?.textContent.trim() || "";
	
	switch (controlVentanaConsulta) {
		case "consulta":
			actualizarResponsableRegistroConsulta()
			document.getElementById("inptPacienteConsulta").value= elemento.querySelector('#td_datos_1')?.textContent.trim();
			document.getElementById("inptCIConsulta").value= elemento.querySelector('#td_datos_2')?.textContent.trim();
			document.getElementById("inptCodigoConsulta").value= elemento.querySelector('#td_datos_3')?.textContent.trim();
			document.getElementById("inptApodoConsulta").value= elemento.querySelector('#td_datos_7')?.textContent.trim();
			buscarDetalleVentaConsulta(cod_ventaFKConsulta)
			buscarabmConsultaParaConsulta(cod_ventaFKConsulta)
			if (typeof cargarOdontogramaFichaClinica == "function") {
				cargarOdontogramaFichaClinica();
			}
			if (typeof buscarHistorialRecetariosDesdeConsulta == "function") {
				buscarHistorialRecetariosDesdeConsulta()
			}
			vercuotasatrazadas(cod_ventaFKConsulta)
			buscarPacienteConsulta()	
			buscarResumenAntecedenteConsulta()
			buscarVistaGaleriaFoto();
			verCerrarAbmConsulta()
			aplicarContextoAtencionAgendaEnFicha();
			break;
		case "interConsulta":
			document.getElementById("inptNombreClienteAbmInterConsulta").value= elemento.querySelector('#td_datos_1')?.textContent.trim();
			if (typeof asignarLocalAbmInterConsulta == "function") {
				asignarLocalAbmInterConsulta(document.getElementById("inptBuscarLocalPaciente").value);
			} else {
				document.getElementById('inptLocalAbmInterConsulta').value= document.getElementById("inptBuscarLocalPaciente").value;
			}
            buscarInterConsultasAsociadasPaciente(cod_clienteConsulta);
			break;
		default:
			break;
	}
	verCerrarAbmVistaConsulta();
}

function agregarObservacionConsulta(){
	let descripcion = document.getElementById('inputObservacion').value;
	if(descripcion ==''){
		ver_vetana_informativa("FALTO INGRESAR UN COMENTARIO INTERNO");
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



function buscarDetalleVentaConsulta(cod_ventaConsultaDetalle, modoSilencioso) {
// if(controlacceso("BUSCARLISTADOCOBRADORES","accion")==false){return;}
 			
	if (!modoSilencioso) {
		document.getElementById("divPreConsultaDetalle_Consulta").innerHTML = paginacargando
	}
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_venta": cod_ventaConsultaDetalle,
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
			if (!modoSilencioso) {
				document.getElementById("divPreConsultaDetalle_Consulta").innerHTML = ''
			}
		},
		success: function (responseText) {
			if (cod_ventaFKConsulta != "" && String(cod_ventaConsultaDetalle) != String(cod_ventaFKConsulta)) {
				return;
			}
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
					inicializarTabsPlanConsulta(document.getElementById("divPreConsultaDetalle_Consulta"));
					if (typeof cargarTratamientosPlanMadreParaConsulta == "function") {
						cargarTratamientosPlanMadreParaConsulta();
					}
					aplicarContextoAtencionAgendaEnFicha();
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





function estadoVacioHistorialConsulta(modo) {
	var esError = modo == "error";
	var titulo = esError ? "No se pudo cargar el historial" : "Sin historial de consultas";
	var texto = esError
		? "Intenta nuevamente. Si el problema continua, revisa la conexion o avisa a administracion."
		: "Todavia no hay consultas, procedimientos ni proximos pasos registrados para visualizar.";
	return "<div class='consulta-history-empty-state" + (esError ? " consulta-history-empty-state--error" : "") + "'>" +
		"<strong>" + titulo + "</strong>" +
		"<span>" + texto + "</span>" +
		"</div>";
}

function normalizarHistorialConsultaHtml(html) {
	var contenido = String(html || "").replace(/<!--[\s\S]*?-->/g, "").trim();
	if (contenido == "" || contenido == "null" || contenido == "undefined") {
		return estadoVacioHistorialConsulta();
	}
	return html;
}

function mostrarEstadoHistorialConsulta(modo) {
	var contenedor = document.getElementById("divHistorial_Consulta");
	if (contenedor) {
		contenedor.innerHTML = estadoVacioHistorialConsulta(modo);
	}
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
			mostrarEstadoHistorialConsulta("error")
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			mostrarEstadoHistorialConsulta()
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("divHistorial_Consulta").innerHTML = normalizarHistorialConsultaHtml(datos_buscados)
					if (typeof buscarHistorialRecetariosDesdeConsulta == "function") {
						buscarHistorialRecetariosDesdeConsulta()
					}
				} else {
					mostrarEstadoHistorialConsulta()
				}
			} catch (error) {
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					mostrarEstadoHistorialConsulta("error")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
}

 

function verCerrarAbmConsulta(){
if(document.getElementById("divAbmConsulta").style.display==""){
cerrarModalConsultaLecturaSiAbierta()
document.getElementById("tdEfectoAbmConsulta").className="magictime vanishOut"
	$("div[id=divAbmConsulta]").fadeOut(500);
	cod_consulta = "";
	window.tabPlanConsultaSeleccionado = "";
	window.tabPlanConsultaVenta = "";
	window.forzarTabPlanDefinitivoConsulta = false;
 
limpiarcamposConsulta()
limpiarContextoAtencionAgendaConsulta()
document.getElementById('btn_flotante_consulta').style.display= 'none'
verCerrarAbmDetalleConsulta(false);
verCerrarAbmVistaConsulta("consulta");
}else{		
	if (typeof cargarCatalogoMedicosSistema == "function") { cargarCatalogoMedicosSistema(); }
	document.getElementById('btn_flotante_consulta').style.display= ''
	document.getElementById("divAbmConsulta").style.display=""
    document.getElementById("tdEfectoAbmConsulta").className="magictime slideDownReturn"
}
}

function ajustarVisualModalNuevaConsulta() {
	var modal = document.getElementById("divAbmDetalleConsulta");
	if (!modal) { return; }
	var shell = modal.querySelector(".consulta-nueva-modal-shell") || modal.querySelector(".modal-detalle-agenda-box");
	var cuerpo = modal.querySelector(".modal-filtros-body");
	var lista = document.getElementById("consultaTratamientoPlanLista");
	var esTabletOTactil = window.matchMedia && window.matchMedia("(max-width: 1100px), (pointer: coarse)").matches;
	modal.style.alignItems = "flex-start";
	modal.style.padding = esTabletOTactil ? "12px" : "32px 24px";
	modal.style.overflow = esTabletOTactil ? "hidden" : "auto";
	if (shell) {
		shell.style.width = esTabletOTactil ? "calc(100vw - 24px)" : "min(1000px, calc(100vw - 48px))";
		shell.style.maxWidth = "1000px";
		shell.style.height = esTabletOTactil ? "calc(100vh - 24px)" : "auto";
		shell.style.height = esTabletOTactil ? "calc(100dvh - 24px)" : "auto";
		shell.style.maxHeight = esTabletOTactil ? "calc(100dvh - 24px)" : "calc(100vh - 64px)";
		shell.style.display = "flex";
		shell.style.flexDirection = "column";
		shell.style.margin = "0 auto";
		shell.style.borderRadius = "14px";
	}
	if (cuerpo) {
		cuerpo.style.flex = "1 1 auto";
		cuerpo.style.minHeight = "0";
		cuerpo.style.overflowX = "hidden";
		cuerpo.style.overflowY = "auto";
		cuerpo.style.webkitOverflowScrolling = "touch";
		cuerpo.style.touchAction = "pan-y";
	}
	if (lista) {
		lista.style.maxHeight = esTabletOTactil ? "none" : "clamp(230px, 34vh, 360px)";
		lista.style.overflowY = esTabletOTactil ? "visible" : "auto";
		lista.style.alignContent = "start";
		lista.style.padding = "8px";
		lista.style.border = "1px solid #d9e4ee";
		lista.style.borderRadius = "10px";
		lista.style.background = "#f8fafc";
		lista.style.boxSizing = "border-box";
	}
	modal.scrollTop = 0;
	if (cuerpo) { cuerpo.scrollTop = 0; }
}

function verCerrarAbmDetalleConsulta(mostrar){
	const detalle = document.getElementById("divAbmDetalleConsulta");
	if (!detalle) { return; }
	const overlay = document.getElementById("overlayAbmDetalleConsulta");

	if (mostrar === true) {
		prepararFormularioNuevaConsulta();
		detalle.style.display = "";
		if (overlay) { overlay.style.display= ""; }
		setTimeout(ajustarVisualModalNuevaConsulta, 0);
		return;
	}

	if (mostrar === false) {
		detalle.style.display = "none";
		if (overlay) { overlay.style.display= "none"; }
		prepararFormularioNuevaConsulta();
		return;
	}

	var abrir = detalle.style.display != "";
	if (abrir) {
		prepararFormularioNuevaConsulta();
	}
	detalle.style.display = abrir ? "" : "none";
	if (overlay) { overlay.style.display = abrir ? "" : "none"; }
	if (abrir) {
		setTimeout(ajustarVisualModalNuevaConsulta, 0);
	}
	if (!abrir) {
		prepararFormularioNuevaConsulta();
	}
}



function prepararFormularioNuevaConsulta(){

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
	actualizarResponsableRegistroConsulta();
 
	document.getElementById("inptMotivoConsulta").value="";	
	document.getElementById("inptDiagnosticoConsulta").value=""; 
	document.getElementById("inptTrabajoRealizadoConsulta").value=""; 
	document.getElementById("inptProximaConsultaConsulta").value=""; 
	if (document.getElementById("inptAvanceTratamientoConsulta")) {
		document.getElementById("inptAvanceTratamientoConsulta").value = "0";
	}
	if (document.getElementById("inptTratamientoPlanMadreConsulta")) {
		document.getElementById("inptTratamientoPlanMadreConsulta").value = "";
	}
	if (document.getElementById("consultaTratamientoPlanHint")) {
		document.getElementById("consultaTratamientoPlanHint").textContent = "Seleccione un tratamiento planificado para registrar su evolucion.";
	}
	if (typeof cargarTratamientosPlanMadreParaConsulta == "function") {
		cargarTratamientosPlanMadreParaConsulta(false);
	}
 
	document.getElementById("btnAbmConsulta").value="Guardar registro clínico"
 
	cod_consulta=""
}

function limpiarcamposConsulta(){

	prepararFormularioNuevaConsulta();
 	cod_ventaFKConsulta="";
	cod_localVentaConsulta="";
	nombre_localVentaConsulta="";
}

let cod_consulta ="";
function VerificarAbmConsulta() {
	
	let inptMotivoConsulta  = document.getElementById("inptMotivoConsulta").value
	let inptDiagnosticoConsulta  = document.getElementById("inptDiagnosticoConsulta").value
	let inptTrabajoRealizadoConsulta  = document.getElementById("inptTrabajoRealizadoConsulta").value
	let inptProximaConsultaConsulta  = document.getElementById("inptProximaConsultaConsulta").value
	let inptFechaConsulta  = document.getElementById("inptFechaConsulta").value
	let inptApodoConsulta  = document.getElementById("inptApodoConsulta").value
	let inptTratamientoPlanMadreConsulta = document.getElementById("inptTratamientoPlanMadreConsulta") ? document.getElementById("inptTratamientoPlanMadreConsulta").value : "";
	let inptAvanceTratamientoConsulta = document.getElementById("inptAvanceTratamientoConsulta") ? document.getElementById("inptAvanceTratamientoConsulta").value : "0";
	
	var cod_especialista = userid;
	actualizarResponsableRegistroConsulta();
 
	if(cod_especialista==""){
		ver_vetana_informativa("NO SE PUDO IDENTIFICAR AL USUARIO ACTUAL")
		return
	}

	if(inptMotivoConsulta==""){
		ver_vetana_informativa("Falto agregar un motivo")
		return
	}

	if(inptTratamientoPlanMadreConsulta==""){
		if (document.getElementById("consultaTratamientoPlanLista")) {
			document.getElementById("consultaTratamientoPlanLista").focus()
		} else if (document.getElementById("inptTratamientoPlanMadreConsulta")) {
			document.getElementById("inptTratamientoPlanMadreConsulta").focus()
		}
		ver_vetana_informativa("Seleccione el tratamiento realizado del plan madre")
		return
	}
	
	if(inptTrabajoRealizadoConsulta==""){
		document.getElementById("inptTrabajoRealizadoConsulta").focus()
		ver_vetana_informativa("Falto agregar la evolucion del tratamiento realizado")
		return
	}

	inptAvanceTratamientoConsulta = parseInt(inptAvanceTratamientoConsulta, 10);
	if (isNaN(inptAvanceTratamientoConsulta) || inptAvanceTratamientoConsulta < 0 || inptAvanceTratamientoConsulta > 100) {
		if (document.getElementById("inptAvanceTratamientoConsulta")) {
			document.getElementById("inptAvanceTratamientoConsulta").focus()
		}
		ver_vetana_informativa("El avance del tratamiento debe estar entre 0 y 100")
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
	
	AbmConsulta(inptApodoConsulta,inptMotivoConsulta,inptDiagnosticoConsulta,inptTrabajoRealizadoConsulta,inptProximaConsultaConsulta,inptFechaConsulta,cod_consulta,cod_especialista,accion,inptTratamientoPlanMadreConsulta,inptAvanceTratamientoConsulta)

}





function VerificarAbmApodo() { 
	let inptApodoConsulta  = document.getElementById("inptApodoConsulta").value 
	actualizarApodo(inptApodoConsulta,"actualizarApodo")
}


function actualizarApodo(apodo,accion) {	
		
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
  
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_venta", cod_ventaFKConsulta) 
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
					
					buscarVistaConsulta()
 
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}







function AbmConsulta(apodo,motivo,diagnostico,trabajoreali,prxtrabajo,fecha,cod_consulta,cod_especialista,accion,cod_detalle_tratamiento,avance_tratamiento) {	
		
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
	datos.append("cod_estecialista", userid) 
	datos.append("cod_agendamiento", idAbmAgenda) 
	datos.append("cod_venta", cod_ventaFKConsulta) 
	datos.append("cod_clienteConsulta", cod_clienteConsulta) 
	datos.append("apodo", apodo) 
	datos.append("cod_detalle_tratamiento", cod_detalle_tratamiento || "") 
	datos.append("avance_tratamiento", avance_tratamiento || "0") 
 
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
					var mensajeOk = "DATOS CARGADO CORRECTAMENTE...";
					if (datos.agenda_imprevista_creada == "1") {
						mensajeOk = "DATOS CARGADO CORRECTAMENTE. Se creo un agendamiento imprevisto para la atencion realizada.";
					}
					ver_vetana_informativa(mensajeOk)
					if (idAbmAgenda && datos.agenda_actualizar_original != "0") {
					    actualizarAgenda(idAbmAgenda, '', '', "ATENDIDO");
					} else if (datos.agenda_imprevista_creada == "1" && typeof cargarAgendaConsultoriosDesdePHP == "function") {
						cargarAgendaConsultoriosDesdePHP();
					}

					buscarabmConsultaParaConsulta(cod_ventaFKConsulta)
					buscarDetalleVentaConsulta(cod_ventaFKConsulta, true)
					verCerrarAbmDetalleConsulta(false)
					if (datos.laboratorio && typeof tratamientoLaboratorioClinicoProcesarContexto == "function") {
						tratamientoLaboratorioClinicoProcesarContexto(datos.laboratorio);
					}
				} else if (datos.mensaje) {
					ver_vetana_informativa(datos.mensaje)
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
  if (document.getElementById('modalTratamiento')) {
    document.getElementById('modalTratamiento').value = el.dataset.tratamiento || "Sin tratamiento vinculado";
  }

  document.getElementById("modalConsulta").style.display = "block";
}
 
function cerrarModal() {
  cerrarModalConsultaLecturaSiAbierta();
}

function cerrarModalConsultaLecturaSiAbierta() {
  var modal = document.getElementById("modalConsulta");
  if (modal) {
    modal.style.display = "none";
  }
}

function actualizarEstadoCuentaConsultaVisual(estadoForzado) {
	var campo = document.getElementById("inptEstadoCuentaConsulta");
	var contenedor = document.querySelector("#divAbmConsulta .consulta-account-status");
	if (!campo || !contenedor) {
		return;
	}
	contenedor.classList.remove(
		"consulta-account-status--neutral",
		"consulta-account-status--loading",
		"consulta-account-status--ok",
		"consulta-account-status--warning",
		"consulta-account-status--danger"
	);
	if (estadoForzado == "loading") {
		campo.value = "Consultando estado de cuenta...";
		campo.removeAttribute("title");
		contenedor.classList.add("consulta-account-status--loading");
		return;
	}
	if (estadoForzado == "error") {
		campo.value = "No se pudo verificar el estado.";
		campo.removeAttribute("title");
		contenedor.classList.add("consulta-account-status--danger");
		return;
	}
	var textoOriginal = (campo.value || "").trim();
	if (textoOriginal == "") {
		campo.value = "Sin estado de cuenta registrado.";
		campo.removeAttribute("title");
		contenedor.classList.add("consulta-account-status--neutral");
		return;
	}
	var texto = textoOriginal.toLowerCase();
	if (texto.normalize) {
		texto = texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
	}
	var cuotas = 0;
	var dias = 0;
	var matchCuotas = texto.match(/vencidas:\s*(\d+)/) || texto.match(/(\d+)\s*cuotas/);
	var matchDias = texto.match(/total de\s*(\d+)/) || texto.match(/(\d+)\s*dias/);
	if (matchCuotas) {
		cuotas = parseInt(matchCuotas[1], 10) || 0;
	}
	if (matchDias) {
		dias = parseInt(matchDias[1], 10) || 0;
	}
	campo.setAttribute("title", textoOriginal);
	if (cuotas == 0 && dias == 0) {
		campo.value = "Sin cuotas vencidas\nCuenta al dia";
		contenedor.classList.add("consulta-account-status--ok");
		return;
	}
	campo.value = (cuotas == 1 ? "1 cuota vencida" : cuotas + " cuotas vencidas") + "\n" + dias + " dias de atraso";
	if (dias >= 60 || cuotas >= 3) {
		contenedor.classList.add("consulta-account-status--danger");
	} else {
		contenedor.classList.add("consulta-account-status--warning");
	}
}

 
function vercuotasatrazadas(cod_ventaFKConsulta) {
// if(controlacceso("BUSCARLISTADOCOBRADORES","accion")==false){return;}
 			
	actualizarEstadoCuentaConsultaVisual("loading");
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
          actualizarEstadoCuentaConsultaVisual("error");
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
					actualizarEstadoCuentaConsultaVisual();
				} else {
					actualizarEstadoCuentaConsultaVisual("error");
				}
			} catch (error) {
					actualizarEstadoCuentaConsultaVisual("error");
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
		 document.getElementById("inptDoctorTrabajoMecanicoDental").innerHTML=""
		 document.getElementById("inptBuscarInfHistorialEspecialista").innerHTML=""
		 
			obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador, 
			 "cod_venta": cod_localFKUSer, 
			"funt": "obtenermedicos"
			};
	 return $.ajax({
			
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
			document.getElementById("inptDoctorTrabajoMecanicoDental").innerHTML="<option value='' >SELECCIONAR</option>"+datos_buscados	
			document.getElementById("inptEspecialistaConsulta").innerHTML="<option value='' >SELECCIONAR</option>"+datos_buscados	
			document.getElementById("inptBuscarInfHistorialEspecialista").innerHTML="<option value='' >SELECCIONAR</option>"+datos_buscados	
			
 
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}

function buscarobtenertodosmedicos() {
	document.getElementById("inptDoctorConsultorio").innerHTML = "";
	document.getElementById("inptDoctorAbmConsultorioAgenda").innerHTML = "";

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_venta": "",
		"funt": "obtenermedicos"
	};
	return $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmusuarios.php",
		type: "post",
		xhr: function () {
			var xhr = new window.XMLHttpRequest();
			//Uload progress
			xhr.upload.addEventListener("progress", function (evt) {
				var kb = ((evt.loaded * 1) / 1000).toFixed(1)
				if (kb == "0.0") {
					kb = 0.1;
				}

			}, false);
			//Download progress
			xhr.addEventListener("progress", function (evt) {
				var kb = ((evt.loaded * 1) / 1000).toFixed(1)
				if (kb == "0.0") {
					kb = 0.1;
				}
			}, false);
			return xhr;
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana")
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					var datos_buscados = datos[2];
					document.getElementById("inptDoctorConsultorio").innerHTML = "<option value='' >SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptDoctorAbmConsultorioAgenda").innerHTML = "<option value='' >SELECCIONAR</option>" + datos_buscados;
				}
			} catch (error) {

			}
		}
	});
}


let idTratamientoAgendaSeleccionado = "";
let nombreTratamientoAgendaSeleccionado = "";

function verCerrarAsignarTratamiento(mostrar) {
	const modal = document.getElementById("modalAsignarTratamientoAgenda");
	const overlay = document.getElementById("overlayAsignarTratamientoAgenda");

	if (!modal || !overlay) {
		ver_vetana_informativa("No se encontro la vista para asignar tratamiento");
		return;
	}

	if (mostrar === false || modal.style.display == "") {
		modal.style.display = "none";
		overlay.style.display = "none";
		return;
	}

	if (document.getElementById("detAgendaCedula").textContent == "") {
		ver_vetana_informativa("FALTA COMPLETAR LOS DATOS DEL PACIENTE");
		return;
	}

	if (typeof idAbmAgenda === "undefined" || idAbmAgenda == "") {
		idAbmAgenda = document.getElementById("detAgendaId") ? document.getElementById("detAgendaId").innerHTML : "";
	}

	if (idAbmAgenda == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN AGENDAMIENTO");
		return;
	}

	idTratamientoAgendaSeleccionado = "";
	nombreTratamientoAgendaSeleccionado = "";
	document.getElementById("inptBuscarTratamientoAgenda").value = "";
	document.getElementById("divTratamientosAgenda").innerHTML = paginacargando;
	overlay.style.display = "";
	modal.style.display = "";
	buscarTratamientosParaAgenda();
}

function buscarTratamientosParaAgenda() {
	if (typeof idAbmAgenda === "undefined" || idAbmAgenda == "") {
		idAbmAgenda = document.getElementById("detAgendaId") ? document.getElementById("detAgendaId").innerHTML : "";
	}

	if (idAbmAgenda == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN AGENDAMIENTO");
		return;
	}

	let buscar = document.getElementById("inptBuscarTratamientoAgenda").value;
	document.getElementById("divTratamientosAgenda").innerHTML = paginacargando;
	obtener_datos_user();

	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"id_agenda": idAbmAgenda,
		"buscar": buscar,
		"funt": "buscarTratamientosAgenda"
	};

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
			document.getElementById("divTratamientosAgenda").innerHTML = "";
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta);
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta = respuestaJqueryAjax(Respuesta);
				if (Respuesta == true) {
					document.getElementById("divTratamientosAgenda").innerHTML = datos[2];
					const cardActiva = document.querySelector("#divTratamientosAgenda .tratamiento-agenda-card--activo");

					idTratamientoAgendaSeleccionado = "";
					nombreTratamientoAgendaSeleccionado = "";
					if (cardActiva) {
						idTratamientoAgendaSeleccionado = cardActiva.getAttribute("data-id");
						nombreTratamientoAgendaSeleccionado = cardActiva.getAttribute("data-nombre") || "";
					}
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
				var titulo = "Error: " + error + " \r\n Consola: " + responseText;
				GuardarArchivosLog(titulo);
			}
		}
	});
}

function seleccionarTratamientoAgenda(elemento) {
	const cards = document.querySelectorAll("#divTratamientosAgenda .tratamiento-agenda-card");
	for (let i = 0; i < cards.length; i++) {
		cards[i].classList.remove("tratamiento-agenda-card--activo");
	}

	elemento.classList.add("tratamiento-agenda-card--activo");
	idTratamientoAgendaSeleccionado = elemento.getAttribute("data-id");
	nombreTratamientoAgendaSeleccionado = elemento.getAttribute("data-nombre") || "";
}

function vincularTratamientoCalendario(id_agenda, cod_tratamiento) {
	if (idTratamientoAgendaSeleccionado == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN TRATAMIENTO");
		return;
	}

	verCerrarEfectoCargando("1");
	obtener_datos_user();

	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"id_agenda": id_agenda,
		"cod_detalle": cod_tratamiento,
		"funt": "vincularTratamientoAgenda"
	};

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
		},
		success: function (responseText) {
			verCerrarEfectoCargando("");
			var Respuesta = responseText;
			console.log(Respuesta);
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta = respuestaJqueryAjax(Respuesta);
				if (Respuesta == true) {
					ver_vetana_informativa("TRATAMIENTO VINCULADO CORRECTAMENTE","", "info");
					verCerrarAsignarTratamiento(false);
					if (document.getElementById("detAgendaTratamientoAsignado") && nombreTratamientoAgendaSeleccionado != "") {
						document.getElementById("detAgendaTratamientoAsignado").innerHTML = nombreTratamientoAgendaSeleccionado + "<br>";
					}
					if (typeof cargarAgendaConsultoriosDesdePHP === "function") {
						cargarAgendaConsultoriosDesdePHP(function () {
							if (
								typeof cargarResumenAbmConsultorioAgenda === "function" &&
								document.getElementById("modalAbmConsultorioAgenda").style.display == ""
							) {
								cargarResumenAbmConsultorioAgenda();
							}
						});
					}
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
				var titulo = "Error: " + error + " \r\n Consola: " + responseText;
				GuardarArchivosLog(titulo);
			}
		}
	});
}


function cambiarVistaPlanTratamientosConsulta(selectVista) {
	var panel = selectVista ? selectVista.closest(".plan-tratamientos-panel") : null;
	if (!panel) { return; }
	var vista = selectVista.value || "plan_sugerido";
	panel.setAttribute("data-plan-vista", vista);
	var secciones = panel.querySelectorAll(".plan-tratamientos-seccion");
	for (var i = 0; i < secciones.length; i++) {
		var grupo = secciones[i].getAttribute("data-plan-seccion") || "";
		var visible = vista === "plan_sugerido" || vista === "todos" || vista === grupo;
		secciones[i].style.display = visible ? "" : "none";
	}
}

var motivosEdicionPlanDefinitivoConsulta = {};
var ultimoItemMovidoPlanDefinitivoConsulta = {};

function activarTabPlanConsulta(contenedor, tab) {
	if (!contenedor) { return; }
	var botones = contenedor.querySelectorAll(".consulta-plan-tabs__nav button");
	for (var i = 0; i < botones.length; i++) {
		var activoBoton = botones[i].getAttribute("data-plan-tab-button") === tab;
		botones[i].classList.toggle("is-active", activoBoton);
		botones[i].setAttribute("aria-selected", activoBoton ? "true" : "false");
		botones[i].setAttribute("tabindex", activoBoton ? "0" : "-1");
	}
	var paneles = contenedor.querySelectorAll(".consulta-plan-tabs__panel");
	for (var j = 0; j < paneles.length; j++) {
		var activo = paneles[j].getAttribute("data-plan-tab") === tab;
		paneles[j].classList.toggle("is-active", activo);
		paneles[j].setAttribute("aria-hidden", activo ? "false" : "true");
	}
	contenedor.setAttribute("data-vista-actual", tab);
}

function cambiarTabPlanConsulta(boton, tab) {
	var contenedor = boton ? boton.closest("[data-consulta-plan-tabs]") : null;
	if (!contenedor) { return; }
	activarTabPlanConsulta(contenedor, tab);
	window.tabPlanConsultaSeleccionado = tab;
	window.tabPlanConsultaVenta = typeof cod_ventaFKConsulta != "undefined" ? cod_ventaFKConsulta : "";
}

function moverTabPlanConsulta(event, boton) {
	if (!event || !boton) { return; }
	var tecla = event.key || event.keyCode;
	if (tecla !== "ArrowRight" && tecla !== "ArrowLeft" && tecla !== 39 && tecla !== 37) { return; }
	event.preventDefault();
	var contenedor = boton.closest("[data-consulta-plan-tabs]");
	if (!contenedor) { return; }
	var botones = contenedor.querySelectorAll(".consulta-plan-tabs__nav button");
	if (botones.length == 0) { return; }
	var indice = 0;
	for (var i = 0; i < botones.length; i++) {
		if (botones[i] === boton) { indice = i; break; }
	}
	var direccion = (tecla === "ArrowRight" || tecla === 39) ? 1 : -1;
	var nuevoIndice = (indice + direccion + botones.length) % botones.length;
	var nuevoBoton = botones[nuevoIndice];
	var tab = nuevoBoton.getAttribute("data-plan-tab-button") || "definitivo";
	cambiarTabPlanConsulta(nuevoBoton, tab);
	nuevoBoton.focus();
}

function inicializarTabsPlanConsulta(root) {
	var base = root || document;
	var contenedor = base.querySelector ? base.querySelector("[data-consulta-plan-tabs]") : null;
	if (!contenedor) { return; }
	var botones = contenedor.querySelectorAll(".consulta-plan-tabs__nav button");
	for (var i = 0; i < botones.length; i++) {
		if (!botones[i].getAttribute("data-plan-tab-keyboard")) {
			botones[i].setAttribute("data-plan-tab-keyboard", "1");
			botones[i].onkeydown = function (event) { moverTabPlanConsulta(event, this); };
		}
	}
	var tab = contenedor.getAttribute("data-vista-inicial") || "sugerido";
	if (window.forzarTabPlanDefinitivoConsulta) {
		tab = "definitivo";
		window.forzarTabPlanDefinitivoConsulta = false;
	} else if (
		window.tabPlanConsultaSeleccionado &&
		window.tabPlanConsultaVenta &&
		typeof cod_ventaFKConsulta != "undefined" &&
		window.tabPlanConsultaVenta == cod_ventaFKConsulta
	) {
		tab = window.tabPlanConsultaSeleccionado;
	}
	activarTabPlanConsulta(contenedor, tab);
	var panelesEditando = contenedor.querySelectorAll(".plan-definitivo-panel.is-editing");
	for (var k = 0; k < panelesEditando.length; k++) {
		prepararEdicionOrdenPlanDefinitivoConsulta(panelesEditando[k]);
	}
	aplicarResaltadoOrdenPlanDefinitivoConsulta(contenedor);
	if (typeof tratamientoLaboratorioClinicoInicializarMicrohilos === "function") {
		tratamientoLaboratorioClinicoInicializarMicrohilos(contenedor);
	}
}

function volverARutaVigentePlanConsulta(origen) {
	var contenedor = origen ? origen.closest("[data-consulta-plan-tabs]") : document.querySelector("[data-consulta-plan-tabs]");
	if (!contenedor) { return; }
	var boton = contenedor.querySelector("[data-plan-tab-button='definitivo']");
	if (boton) {
		cambiarTabPlanConsulta(boton, "definitivo");
		boton.focus();
	}
}

function obtenerPanelPlanDefinitivoConsulta(planId) {
	return document.querySelector(".plan-definitivo-panel[data-plan-id='" + planId + "']");
}

function tieneOrdenPendientePlanDefinitivoConsulta(planId) {
	var panel = obtenerPanelPlanDefinitivoConsulta(planId);
	return !!(panel && panel.classList.contains("is-order-dirty"));
}

function detenerEventoAccionPlanDefinitivoConsulta(event) {
	if (!event) { return; }
	event.preventDefault();
	event.stopPropagation();
	if (event.stopImmediatePropagation) {
		event.stopImmediatePropagation();
	}
}

function esAccionInternaPlanDefinitivoConsulta(elemento) {
	if (!elemento || !elemento.closest) { return false; }
	return !!elemento.closest(".plan-definitivo-item__actions, button, a, input, textarea, select, .odontograma-plan-ubicar-btn");
}

function obtenerItemsOrdenPlanDefinitivoConsulta(panel) {
	if (!panel) { return []; }
	var lista = panel.querySelector(".plan-definitivo-lista");
	if (!lista) { return []; }
	return Array.prototype.slice.call(lista.querySelectorAll(".plan-definitivo-item[data-plan-item]"));
}

function obtenerIdsOrdenPlanDefinitivoConsulta(panel) {
	var items = obtenerItemsOrdenPlanDefinitivoConsulta(panel);
	var ids = [];
	for (var i = 0; i < items.length; i++) {
		var id = items[i].getAttribute("data-plan-item") || "";
		if (id != "") { ids.push(id); }
	}
	return ids;
}

function actualizarNumeracionOrdenPlanDefinitivoConsulta(panel) {
	var items = obtenerItemsOrdenPlanDefinitivoConsulta(panel);
	for (var i = 0; i < items.length; i++) {
		var numero = i + 1;
		var item = items[i];
		var completado = item.classList.contains("plan-definitivo-item--completado");
		var nodo = item.querySelector(".plan-ruta-nodo");
		var nodoTexto = nodo ? nodo.querySelector("span") : null;
		var paso = item.querySelector(".plan-definitivo-item__top span");
		item.setAttribute("data-plan-numero", numero);
		if (nodo) {
			nodo.setAttribute("title", completado ? ("Paso " + numero + " completado") : ("Paso " + numero));
		}
		if (nodoTexto && !completado) {
			nodoTexto.textContent = numero;
		}
		if (paso) {
			paso.textContent = "Paso " + numero + " de la ruta clinica";
		}
	}
	actualizarBotonesOrdenPlanDefinitivoConsulta(panel);
}

function actualizarBotonesOrdenPlanDefinitivoConsulta(panel) {
	var items = obtenerItemsOrdenPlanDefinitivoConsulta(panel);
	for (var i = 0; i < items.length; i++) {
		var botones = items[i].querySelectorAll(".plan-definitivo-order-btn");
		if (botones.length >= 2) {
			botones[0].disabled = (i == 0);
			botones[1].disabled = (i == items.length - 1);
		}
	}
}

function prepararEdicionOrdenPlanDefinitivoConsulta(panel) {
	if (!panel) { return; }
	if (!panel.getAttribute("data-order-original")) {
		panel.setAttribute("data-order-original", obtenerIdsOrdenPlanDefinitivoConsulta(panel).join(","));
	}
	actualizarBotonesOrdenPlanDefinitivoConsulta(panel);
}

function marcarCambioOrdenPlanDefinitivoConsulta(panel, itemId) {
	if (!panel) { return; }
	panel.classList.add("is-order-dirty");
	if (itemId) {
		ultimoItemMovidoPlanDefinitivoConsulta[panel.getAttribute("data-plan-id") || ""] = itemId;
		var item = panel.querySelector(".plan-definitivo-item[data-plan-item='" + itemId + "']");
		if (item) {
			item.classList.add("plan-definitivo-item--orden-movido");
			setTimeout(function () {
				item.classList.remove("plan-definitivo-item--orden-movido");
			}, 1600);
		}
	}
}

function aplicarResaltadoOrdenPlanDefinitivoConsulta(root) {
	if (!window.itemPlanDefinitivoResaltarOrden) { return; }
	var base = root || document;
	var item = base.querySelector ? base.querySelector(".plan-definitivo-item[data-plan-item='" + window.itemPlanDefinitivoResaltarOrden + "']") : null;
	if (!item) { return; }
	item.classList.add("plan-definitivo-item--orden-confirmado");
	setTimeout(function () {
		if (item.scrollIntoView) {
			try {
				item.scrollIntoView({ behavior: "smooth", block: "center" });
			} catch (error) {
				item.scrollIntoView();
			}
		}
	}, 100);
	setTimeout(function () {
		item.classList.remove("plan-definitivo-item--orden-confirmado");
	}, 2600);
	window.itemPlanDefinitivoResaltarOrden = "";
}

function refrescarPlanDefinitivoConsulta() {
	if (cod_ventaFKConsulta != "") {
		window.forzarTabPlanDefinitivoConsulta = true;
		buscarDetalleVentaConsulta(cod_ventaFKConsulta);
	}
}

function ajaxPlanDefinitivoConsulta(funt, datosExtra, callback) {
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": funt
	};
	for (var clave in datosExtra) {
		if (Object.prototype.hasOwnProperty.call(datosExtra, clave)) {
			datos[clave] = datosExtra[clave];
		}
	}
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
		},
		success: function (responseText) {
			try {
				var datosRespuesta = $.parseJSON(responseText);
				if (datosRespuesta["1"] == "exito") {
					if (typeof callback == "function") {
						callback(true, datosRespuesta);
					}
					return;
				}
				ver_vetana_informativa(datosRespuesta.mensaje || "No se pudo completar la accion.");
				if (typeof callback == "function") {
					callback(false, datosRespuesta);
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
				var titulo = "Error: " + error + " \r\n Consola: " + responseText;
				GuardarArchivosLog(titulo);
			}
		}
	});
}

function asegurarModalPlanDefinitivoConsulta() {
	var existente = document.getElementById("modalPlanDefinitivoConsulta");
	if (existente) { return existente; }
	var overlay = document.createElement("div");
	overlay.id = "overlayPlanDefinitivoConsulta";
	overlay.className = "plan-definitivo-modal-overlay";
	overlay.style.display = "none";
	overlay.onclick = function () { cerrarModalPlanDefinitivoConsulta(); };
	document.body.appendChild(overlay);
	var modal = document.createElement("div");
	modal.id = "modalPlanDefinitivoConsulta";
	modal.className = "plan-definitivo-modal";
	modal.style.display = "none";
	modal.innerHTML = "<div class='plan-definitivo-modal__head'>" +
		"<div><h3 id='modalPlanDefinitivoTitulo'>Plan madre</h3><span id='modalPlanDefinitivoSubtitulo'></span></div>" +
		"<button type='button' onclick='cerrarModalPlanDefinitivoConsulta()'>&times;</button>" +
		"</div><div class='plan-definitivo-modal__body' id='modalPlanDefinitivoCuerpo'></div>" +
		"<div class='plan-definitivo-modal__footer' id='modalPlanDefinitivoFooter'></div>";
	document.body.appendChild(modal);
	return modal;
}

function abrirModalPlanDefinitivoConsulta(titulo, subtitulo, cuerpo, footer) {
	asegurarModalPlanDefinitivoConsulta();
	document.getElementById("modalPlanDefinitivoTitulo").innerHTML = titulo || "Plan madre";
	document.getElementById("modalPlanDefinitivoSubtitulo").innerHTML = subtitulo || "";
	document.getElementById("modalPlanDefinitivoCuerpo").innerHTML = cuerpo || "";
	document.getElementById("modalPlanDefinitivoFooter").innerHTML = footer || "";
	document.getElementById("overlayPlanDefinitivoConsulta").style.display = "";
	document.getElementById("modalPlanDefinitivoConsulta").style.display = "";
}

function cerrarModalPlanDefinitivoConsulta() {
	var overlay = document.getElementById("overlayPlanDefinitivoConsulta");
	var modal = document.getElementById("modalPlanDefinitivoConsulta");
	if (overlay) { overlay.style.display = "none"; }
	if (modal) { modal.style.display = "none"; }
}

function solicitarMotivoPlanDefinitivoConsulta(planId, callback) {
	var cuerpo = "<div class='plan-definitivo-motivo-box'>" +
		"<p>Esta ruta ya fue definida. Los cambios quedar&aacute;n registrados en el historial.</p>" +
		"<label>Motivo de modificaci&oacute;n</label>" +
		"<textarea id='txtMotivoPlanDefinitivoConsulta' class='textarea-agenda' placeholder='Ej.: estudio necesario antes de continuar'></textarea>" +
		"</div>";
	var footer = "<button type='button' class='plan-definitivo-secondary' onclick='cerrarModalPlanDefinitivoConsulta()'>Cancelar</button>" +
		"<button type='button' class='plan-definitivo-primary' onclick='confirmarMotivoPlanDefinitivoConsulta(\"" + planId + "\")'>Continuar edici&oacute;n</button>";
	abrirModalPlanDefinitivoConsulta("Modificar plan madre", "Trazabilidad de cambios", cuerpo, footer);
	window.callbackMotivoPlanDefinitivoConsulta = callback;
	setTimeout(function () {
		var campo = document.getElementById("txtMotivoPlanDefinitivoConsulta");
		if (campo) { campo.focus(); }
	}, 80);
}

function confirmarMotivoPlanDefinitivoConsulta(planId) {
	var campo = document.getElementById("txtMotivoPlanDefinitivoConsulta");
	var motivo = campo ? campo.value.trim() : "";
	if (motivo == "") {
		ver_vetana_informativa("Debe indicar el motivo de modificacion.");
		return;
	}
	motivosEdicionPlanDefinitivoConsulta[planId] = motivo;
	cerrarModalPlanDefinitivoConsulta();
	if (typeof window.callbackMotivoPlanDefinitivoConsulta == "function") {
		window.callbackMotivoPlanDefinitivoConsulta(motivo);
	}
	window.callbackMotivoPlanDefinitivoConsulta = null;
}

function ejecutarConMotivoPlanDefinitivoConsulta(planId, callback) {
	var panel = obtenerPanelPlanDefinitivoConsulta(planId);
	var estado = panel ? (panel.getAttribute("data-plan-estado") || "") : "";
	if (estado == "definido" || estado == "modificado") {
		if (motivosEdicionPlanDefinitivoConsulta[planId]) {
			callback(motivosEdicionPlanDefinitivoConsulta[planId]);
			return;
		}
		solicitarMotivoPlanDefinitivoConsulta(planId, callback);
		return;
	}
	callback("");
}

function editarPlanDefinitivoConsulta(planId) {
	ejecutarConMotivoPlanDefinitivoConsulta(planId, function () {
		var panel = obtenerPanelPlanDefinitivoConsulta(planId);
		if (panel) {
			panel.classList.add("is-editing");
			prepararEdicionOrdenPlanDefinitivoConsulta(panel);
		}
	});
}

function crearPlanDefinitivoDesdeSugeridoConsulta(codVenta) {
	ajaxPlanDefinitivoConsulta("crearPlanDefinitivoDesdeSugerido", { "cod_venta": codVenta }, function (ok, datos) {
		if (!ok) { return; }
		ver_vetana_informativa(datos.mensaje || "Plan madre creado.");
		refrescarPlanDefinitivoConsulta();
	});
}

function guardarBorradorPlanDefinitivoConsulta(planId) {
	ejecutarConMotivoPlanDefinitivoConsulta(planId, function (motivo) {
		ajaxPlanDefinitivoConsulta("guardarBorradorPlanDefinitivo", { "plan_id": planId, "motivo": motivo }, function (ok, datos) {
			if (!ok) { return; }
			ver_vetana_informativa(datos.mensaje || "Borrador guardado.");
			refrescarPlanDefinitivoConsulta();
		});
	});
}

function confirmarPlanDefinitivoConsulta(planId) {
	if (tieneOrdenPendientePlanDefinitivoConsulta(planId)) {
		ver_vetana_informativa("Primero confirme el orden pendiente del plan madre.");
		return;
	}
	ajaxPlanDefinitivoConsulta("confirmarPlanDefinitivo", { "plan_id": planId }, function (ok, datos) {
		if (!ok) { return; }
		delete motivosEdicionPlanDefinitivoConsulta[planId];
		ver_vetana_informativa(datos.mensaje || "Plan madre confirmado.");
		refrescarPlanDefinitivoConsulta();
	});
}

function moverItemPlanDefinitivoConsulta(event, planId, itemId, direccion) {
	detenerEventoAccionPlanDefinitivoConsulta(event);
	var panel = obtenerPanelPlanDefinitivoConsulta(planId);
	if (!panel || !panel.classList.contains("is-editing")) {
		ver_vetana_informativa("Primero presione Editar ruta.");
		return false;
	}
	prepararEdicionOrdenPlanDefinitivoConsulta(panel);
	var item = panel.querySelector(".plan-definitivo-item[data-plan-item='" + itemId + "']");
	var lista = item ? item.parentNode : null;
	if (!item || !lista) {
		ver_vetana_informativa("No se pudo identificar el tratamiento.");
		return false;
	}
	var items = obtenerItemsOrdenPlanDefinitivoConsulta(panel);
	var indice = items.indexOf(item);
	var destinoIndice = indice + (parseInt(direccion, 10) || 0);
	if (indice < 0 || destinoIndice < 0 || destinoIndice >= items.length) {
		ver_vetana_informativa("El tratamiento ya esta en el limite de la ruta.");
		return false;
	}
	var destino = items[destinoIndice];
	if ((parseInt(direccion, 10) || 0) < 0) {
		lista.insertBefore(item, destino);
	} else {
		lista.insertBefore(destino, item);
	}
	actualizarNumeracionOrdenPlanDefinitivoConsulta(panel);
	marcarCambioOrdenPlanDefinitivoConsulta(panel, itemId);
	return false;
}

function guardarOrdenPlanDefinitivoConsulta(planId) {
	var panel = obtenerPanelPlanDefinitivoConsulta(planId);
	if (!panel) {
		ver_vetana_informativa("No se encontro el plan madre.");
		return;
	}
	prepararEdicionOrdenPlanDefinitivoConsulta(panel);
	if (!panel.classList.contains("is-order-dirty")) {
		ver_vetana_informativa("No hay cambios de orden para guardar.");
		return;
	}
	var ids = obtenerIdsOrdenPlanDefinitivoConsulta(panel);
	ejecutarConMotivoPlanDefinitivoConsulta(planId, function (motivo) {
		ajaxPlanDefinitivoConsulta("guardarOrdenPlanDefinitivo", {
			"plan_id": planId,
			"orden_ids": ids.join(","),
			"motivo": motivo
		}, function (ok, datos) {
			if (!ok) { return; }
			var itemResaltar = ultimoItemMovidoPlanDefinitivoConsulta[planId] || ids[0] || "";
			window.itemPlanDefinitivoResaltarOrden = itemResaltar;
			delete motivosEdicionPlanDefinitivoConsulta[planId];
			delete ultimoItemMovidoPlanDefinitivoConsulta[planId];
			ver_vetana_informativa(datos.mensaje || "Orden del plan madre confirmado.");
			refrescarPlanDefinitivoConsulta();
		});
	});
}

function cancelarEdicionOrdenPlanDefinitivoConsulta(planId) {
	var panel = obtenerPanelPlanDefinitivoConsulta(planId);
	if (!panel) { return; }
	if (panel.classList.contains("is-order-dirty") && !confirm("Descartar los movimientos de orden pendientes?")) {
		return;
	}
	delete motivosEdicionPlanDefinitivoConsulta[planId];
	delete ultimoItemMovidoPlanDefinitivoConsulta[planId];
	refrescarPlanDefinitivoConsulta();
}

function editarObservacionItemPlanDefinitivoConsulta(event, planId, itemId) {
	detenerEventoAccionPlanDefinitivoConsulta(event);
	if (tieneOrdenPendientePlanDefinitivoConsulta(planId)) {
		ver_vetana_informativa("Primero confirme el orden pendiente del plan madre.");
		return;
	}
	var card = document.querySelector(".plan-definitivo-item[data-plan-item='" + itemId + "']");
	var actual = card ? (card.getAttribute("data-observacion") || "") : "";
	var actualSeguro = actual.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
	var cuerpo = "<label class='plan-definitivo-modal-label'>Observaci&oacute;n cl&iacute;nica breve</label>" +
		"<textarea id='txtObservacionPlanDefinitivoConsulta' class='textarea-agenda' style='min-height:120px;'>" + actualSeguro + "</textarea>";
	var footer = "<button type='button' class='plan-definitivo-secondary' onclick='cerrarModalPlanDefinitivoConsulta()'>Cancelar</button>" +
		"<button type='button' class='plan-definitivo-primary' onclick='guardarObservacionItemPlanDefinitivoConsulta(\"" + planId + "\",\"" + itemId + "\")'>Guardar observaci&oacute;n</button>";
	abrirModalPlanDefinitivoConsulta("Editar observaci&oacute;n", "Plan madre", cuerpo, footer);
}

function guardarObservacionItemPlanDefinitivoConsulta(planId, itemId) {
	var campo = document.getElementById("txtObservacionPlanDefinitivoConsulta");
	var observacion = campo ? campo.value : "";
	ejecutarConMotivoPlanDefinitivoConsulta(planId, function (motivo) {
		ajaxPlanDefinitivoConsulta("actualizarObservacionItemPlanDefinitivo", {
			"plan_id": planId,
			"item_id": itemId,
			"observacion": observacion,
			"motivo": motivo
		}, function (ok, datos) {
			if (!ok) { return; }
			cerrarModalPlanDefinitivoConsulta();
			ver_vetana_informativa(datos.mensaje || "Observacion guardada.");
			refrescarPlanDefinitivoConsulta();
		});
	});
}

function quitarItemPlanDefinitivoConsulta(event, planId, itemId) {
	detenerEventoAccionPlanDefinitivoConsulta(event);
	if (tieneOrdenPendientePlanDefinitivoConsulta(planId)) {
		ver_vetana_informativa("Primero confirme el orden pendiente del plan madre.");
		return;
	}
	if (!confirm("Quitar este tratamiento solo del plan madre?")) {
		return;
	}
	ejecutarConMotivoPlanDefinitivoConsulta(planId, function (motivo) {
		ajaxPlanDefinitivoConsulta("quitarItemPlanDefinitivo", {
			"plan_id": planId,
			"item_id": itemId,
			"motivo": motivo
		}, function (ok, datos) {
			if (!ok) { return; }
			ver_vetana_informativa(datos.mensaje || "Tratamiento quitado de la ruta.");
			refrescarPlanDefinitivoConsulta();
		});
	});
}

function abrirAnexarTratamientosPlanDefinitivoConsulta(planId) {
	if (tieneOrdenPendientePlanDefinitivoConsulta(planId)) {
		ver_vetana_informativa("Primero confirme el orden pendiente del plan madre.");
		return;
	}
	ajaxPlanDefinitivoConsulta("buscarVentasAnexablesPlanDefinitivo", { "plan_id": planId }, function (ok, datos) {
		if (!ok) { return; }
		var cuerpo = "<p class='plan-definitivo-modal-help'>Solo se muestran ventas asociadas a la misma c&eacute;dula/paciente. Se anexa la venta completa con todos sus tratamientos activos.</p>" + (datos[2] || "");
		var footer = "<button type='button' class='plan-definitivo-secondary' onclick='cerrarModalPlanDefinitivoConsulta()'>Cancelar</button>" +
			"<button type='button' class='plan-definitivo-primary' onclick='anexarSeleccionadosPlanDefinitivoConsulta(\"" + planId + "\")'>Anexar ventas seleccionadas</button>";
		abrirModalPlanDefinitivoConsulta("Anexar tratamientos de otras ventas", "Plan madre", cuerpo, footer);
	});
}

function anexarSeleccionadosPlanDefinitivoConsulta(planId) {
	var seleccionados = [];
	var checks = document.querySelectorAll("#modalPlanDefinitivoConsulta .plan-definitivo-anexar-venta-selector input[type='checkbox']:checked");
	for (var i = 0; i < checks.length; i++) {
		seleccionados.push(checks[i].value);
	}
	if (seleccionados.length == 0) {
		ver_vetana_informativa("Seleccione al menos una venta.");
		return;
	}
	ejecutarConMotivoPlanDefinitivoConsulta(planId, function (motivo) {
		ajaxPlanDefinitivoConsulta("anexarTratamientosPlanDefinitivo", {
			"plan_id": planId,
			"venta_ids": seleccionados.join(","),
			"motivo": motivo
		}, function (ok, datos) {
			if (!ok) { return; }
			cerrarModalPlanDefinitivoConsulta();
			ver_vetana_informativa(datos.mensaje || "Ventas anexadas.");
			refrescarPlanDefinitivoConsulta();
		});
	});
}

function verHistorialPlanDefinitivoConsulta(planId) {
	ajaxPlanDefinitivoConsulta("obtenerHistorialPlanDefinitivo", { "plan_id": planId }, function (ok, datos) {
		if (!ok) { return; }
		var footer = "<button type='button' class='plan-definitivo-primary' onclick='cerrarModalPlanDefinitivoConsulta()'>Entendido</button>";
		abrirModalPlanDefinitivoConsulta("Historial del plan madre", "Trazabilidad cl&iacute;nica", datos[2] || "", footer);
	});
}

function mostrarGuiaPlanDefinitivoConsulta() {
	var cuerpo = "<ol class='plan-definitivo-guia'>" +
		"<li>La Sugerencia autom&aacute;tica es calculada por el sistema seg&uacute;n estado y riesgo financiero.</li>" +
		"<li>El Plan madre agrupa la ruta cl&iacute;nica de un beneficiario bajo la misma c&eacute;dula.</li>" +
		"<li>Cuando esta venta ya pertenece a un Plan madre, se abre como vista principal.</li>" +
		"<li>El Plan madre no cambia autom&aacute;ticamente aunque cambie la sugerencia.</li>" +
		"<li>Cualquier modificaci&oacute;n del Plan madre queda registrada en el historial.</li>" +
		"<li>Pod&eacute;s consultar la sugerencia autom&aacute;tica como referencia cuando lo necesites.</li>" +
		"</ol>";
	var footer = "<button type='button' class='plan-definitivo-primary' onclick='cerrarModalPlanDefinitivoConsulta()'>Entendido</button>";
	abrirModalPlanDefinitivoConsulta("Plan sugerido y Plan madre", "Gu&iacute;a r&aacute;pida", cuerpo, footer);
}


let id_detalle_tratamientoConsulta = '';
let contextoPorcentajeProgresoConsulta = null;

function guardarContextoPorcentajeProgresoConsulta() {
	contextoPorcentajeProgresoConsulta = {
		cod_venta: cod_ventaFKConsulta,
		cod_cliente: cod_clienteConsulta,
		id_agenda: (typeof idAbmAgenda !== "undefined" ? idAbmAgenda : "")
	};

	if (contextoPorcentajeProgresoConsulta.id_agenda == "" && document.getElementById("detAgendaId")) {
		contextoPorcentajeProgresoConsulta.id_agenda = document.getElementById("detAgendaId").innerHTML;
	}
}

function restaurarContextoPorcentajeProgresoConsulta() {
	if (!contextoPorcentajeProgresoConsulta) {
		return;
	}

	cod_ventaFKConsulta = contextoPorcentajeProgresoConsulta.cod_venta || cod_ventaFKConsulta;
	cod_clienteConsulta = contextoPorcentajeProgresoConsulta.cod_cliente || cod_clienteConsulta;

	if (typeof idAbmAgenda !== "undefined" && contextoPorcentajeProgresoConsulta.id_agenda != "") {
		idAbmAgenda = contextoPorcentajeProgresoConsulta.id_agenda;
	}
}

let tratamientoProgresoActualConsulta = {
	id: "",
	nombre: "",
	porcentaje: 0,
	estado: "",
	estadoClase: "",
	laboratorio: false,
	laboratorioDatos: null,
	elemento: null
};

function escaparHtmlConsulta(valor) {
	return String(valor || "")
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

function normalizarAvanceTratamientoConsulta(valor) {
	valor = parseInt(valor, 10);
	if (isNaN(valor)) { valor = 0; }
	if (valor < 0) { valor = 0; }
	if (valor > 100) { valor = 100; }
	return valor;
}

let contextoAgendaPlanMadreConsulta = {
	id_agenda: "",
	cargado: false,
	agenda: { existe: false },
	tratamientos: [],
	ids: [],
	idsMapa: {}
};

function obtenerIdAgendaActualConsulta() {
	var id = (typeof idAbmAgenda !== "undefined" ? String(idAbmAgenda || "").trim() : "");
	if (id == "" && document.getElementById("detAgendaId")) {
		id = String(document.getElementById("detAgendaId").innerHTML || "").trim();
	}
	return id;
}

function cargarContextoAgendaPlanMadreConsulta(callback) {
	var idAgenda = obtenerIdAgendaActualConsulta();
	if (idAgenda == "") {
		contextoAgendaPlanMadreConsulta = {
			id_agenda: "",
			cargado: true,
			agenda: { existe: false },
			tratamientos: [],
			ids: [],
			idsMapa: {}
		};
		if (typeof callback == "function") { callback(); }
		return;
	}
	if (contextoAgendaPlanMadreConsulta.cargado && contextoAgendaPlanMadreConsulta.id_agenda == idAgenda) {
		if (typeof callback == "function") { callback(); }
		return;
	}
	contextoAgendaPlanMadreConsulta = {
		id_agenda: idAgenda,
		cargado: false,
		agenda: { existe: false },
		tratamientos: [],
		ids: [],
		idsMapa: {}
	};
	ajaxPlanDefinitivoConsulta("obtenerContextoAgendaConsulta", { "id_agenda": idAgenda }, function (ok, datos) {
		var ids = [];
		var mapa = {};
		if (ok && datos.ids && datos.ids.length) {
			ids = datos.ids;
			for (var i = 0; i < ids.length; i++) {
				mapa[String(ids[i])] = true;
			}
		}
		contextoAgendaPlanMadreConsulta = {
			id_agenda: idAgenda,
			cargado: true,
			agenda: ok && datos.agenda ? datos.agenda : { existe: false },
			tratamientos: ok && datos.tratamientos ? datos.tratamientos : [],
			ids: ids,
			idsMapa: mapa
		};
		if (typeof callback == "function") { callback(); }
	});
}

function etiquetaFechaHoraAgendaConsulta(agenda) {
	if (!agenda || !agenda.existe) { return ""; }
	var partes = [];
	if (agenda.fecha) { partes.push(agenda.fecha); }
	if (agenda.hora_inicio || agenda.hora_fin) {
		partes.push((agenda.hora_inicio || "--:--") + " a " + (agenda.hora_fin || "--:--"));
	}
	return partes.join(" - ");
}

function nombresTratamientosAgendaConsulta() {
	var tratamientos = contextoAgendaPlanMadreConsulta.tratamientos || [];
	var nombres = [];
	for (var i = 0; i < tratamientos.length; i++) {
		if (tratamientos[i].nombre) { nombres.push(tratamientos[i].nombre); }
	}
	return nombres;
}

function obtenerSiguienteSugeridoPlanMadreConsulta(opciones) {
	for (var i = 0; i < opciones.length; i++) {
		var estadoClase = String(opciones[i].estadoClase || "").toLowerCase();
		if (opciones[i].avance < 100 && estadoClase != "completado" && estadoClase != "cancelado") {
			return opciones[i].detalle;
		}
	}
	return "";
}

function renderizarContextoPlanMadreConsulta(opciones) {
	var contenedor = document.getElementById("consultaPlanMadreResumen");
	if (!contenedor) { return; }
	var panelPlanMadre = document.querySelector("#divPreConsultaDetalle_Consulta #consultaPlanDefinitivoPanel .plan-definitivo-panel[data-plan-id]");
	if (!panelPlanMadre) {
		contenedor.innerHTML = "<div class='clinical-treatment-context-empty'>Esta venta todavia no tiene plan madre activo.</div>";
		return;
	}
	var planLabel = panelPlanMadre.getAttribute("data-plan-label") || ($(panelPlanMadre).find(".plan-definitivo-header h4").first().text() || "Plan madre");
	var estado = ($(panelPlanMadre).find(".plan-definitivo-status").first().text() || "").trim();
	var total = opciones.length;
	var completados = 0;
	var proceso = 0;
	for (var i = 0; i < opciones.length; i++) {
		if (opciones[i].avance >= 100 || String(opciones[i].estadoClase).toLowerCase() == "completado") { completados++; }
		else if (opciones[i].avance > 0) { proceso++; }
	}
	var agenda = contextoAgendaPlanMadreConsulta.agenda || { existe: false };
	var agendaHtml = "";
	if (agenda.existe) {
		var nombres = nombresTratamientosAgendaConsulta();
		agendaHtml = "<div class='clinical-treatment-agenda-context'>" +
			"<strong>Cita del calendario</strong>" +
			"<span>" + escaparHtmlConsulta(etiquetaFechaHoraAgendaConsulta(agenda) || "Fecha no definida") + "</span>" +
			(nombres.length ? "<em>Recomendado: " + escaparHtmlConsulta(nombres.join(", ")) + "</em><small>Insumos odontologicos contemplados para este tratamiento.</small>" : "<em>Sin tratamiento vinculado desde calendario.</em><small>Al guardar un tratamiento se registrara como imprevisto.</small>") +
			"</div>";
	} else {
		agendaHtml = "<div class='clinical-treatment-agenda-context clinical-treatment-agenda-context--warning'><strong>Sin cita de calendario vinculada</strong><span>La seleccion se guardara como evolucion clinica del plan madre.</span></div>";
	}
	contenedor.innerHTML = "<div class='clinical-treatment-plan-card'>" +
		"<div><span>Plan madre activo</span><strong>" + escaparHtmlConsulta(planLabel) + "</strong><small>Ruta clinica agrupada &middot; Orden sugerido N1 a N5</small></div>" +
		"<div class='clinical-treatment-plan-card__stats'><b>" + total + "</b><span>Tratamientos</span><b>" + proceso + "</b><span>En proceso</span><b>" + completados + "</b><span>100%</span></div>" +
		(estado ? "<em>" + escaparHtmlConsulta(estado) + "</em>" : "") +
		"</div>" + agendaHtml;
}

function etiquetaGrupoTratamientoPlanMadreConsulta(item) {
	var origen = String(item && item.origen ? item.origen : "").toLowerCase();
	return origen.indexOf("anexada") >= 0 ? "Venta anexada" : "Venta base";
}

function renderizarOpcionTratamientoPlanMadreConsulta(item, contexto) {
	contexto = contexto || {};
	var recomendado = !!contexto.recomendado;
	var sugerido = !!contexto.sugerido;
	var completado = !!contexto.completado;
	var seleccionado = !!contexto.seleccionado;
	var hayAgenda = !!contexto.hayAgenda;
	var clases = "clinical-treatment-plan-option" +
		(seleccionado ? " is-selected" : "") +
		(recomendado ? " is-recommended" : "") +
		(sugerido ? " is-suggested" : "") +
		(completado ? " is-completed" : "");
	var badges = "";
	if (recomendado) { badges += "<b class='clinical-treatment-plan-badge clinical-treatment-plan-badge--recommended'>" + (contextoAtencionAgendaConsulta.activo ? "Tratamiento de esta cita" : "Recomendado agenda") + "</b>"; }
	if (sugerido) { badges += "<b class='clinical-treatment-plan-badge clinical-treatment-plan-badge--suggested'>Siguiente sugerido</b>"; }
	if (completado) { badges += "<b class='clinical-treatment-plan-badge clinical-treatment-plan-badge--done'>Evolucionado al 100%</b>"; }
	if (hayAgenda && !recomendado) { badges += "<b class='clinical-treatment-plan-badge clinical-treatment-plan-badge--warning'>No agendado para esta cita</b>"; }
	return "<button type='button' class='" + clases + "' role='listitem' data-detalle='" + escaparHtmlConsulta(item.detalle) + "' onclick='seleccionarTratamientoPlanMadreConsulta(\"" + escaparHtmlConsulta(item.detalle) + "\")'>" +
		"<span class='clinical-treatment-plan-option__radio' aria-hidden='true'></span>" +
		"<span class='clinical-treatment-plan-option__body'>" +
			"<strong>" + escaparHtmlConsulta(item.nombre) + "</strong>" +
			"<small>" + escaparHtmlConsulta(item.estado) + " &middot; Avance " + item.avance + "%</small>" +
			"<span class='clinical-treatment-plan-option__badges'><em>" + escaparHtmlConsulta(item.riesgoTexto || ("N" + item.riesgo)) + "</em>" + badges + "</span>" +
		"</span>" +
	"</button>";
}

function compactarListaTratamientosPlanMadreConsulta() {
	var lista = document.getElementById("consultaTratamientoPlanLista");
	if (!lista) { return; }
	lista.style.maxHeight = "clamp(230px, 34vh, 360px)";
	lista.style.overflowY = "auto";
	lista.style.alignContent = "start";
	lista.style.padding = "8px";
	lista.style.border = "1px solid #d9e4ee";
	lista.style.borderRadius = "10px";
	lista.style.background = "#f8fafc";
	lista.style.boxSizing = "border-box";
	var opciones = lista.querySelectorAll(".clinical-treatment-plan-option");
	for (var i = 0; i < opciones.length; i++) {
		opciones[i].style.minHeight = "48px";
		opciones[i].style.padding = "6px 8px";
		opciones[i].style.gridTemplateColumns = "22px minmax(0, 1fr)";
		opciones[i].style.gap = "7px";
	}
	var rutas = lista.querySelectorAll(".clinical-treatment-plan-option__route");
	for (var j = 0; j < rutas.length; j++) {
		rutas[j].style.display = "none";
	}
}

function renderizarTratamientosPlanMadreVisualConsulta(opciones) {
	var lista = document.getElementById("consultaTratamientoPlanLista");
	if (!lista) { return; }
	renderizarContextoPlanMadreConsulta(opciones);
	if (!opciones.length) {
		lista.innerHTML = "<div class='clinical-treatment-plan-empty'>No hay tratamientos activos vinculados al plan madre.</div>";
		return;
	}
	var select = document.getElementById("inptTratamientoPlanMadreConsulta");
	var seleccionado = select ? String(select.value || "") : "";
	var recomendados = contextoAgendaPlanMadreConsulta.idsMapa || {};
	var hayAgenda = contextoAgendaPlanMadreConsulta.agenda && contextoAgendaPlanMadreConsulta.agenda.existe;
	var siguiente = obtenerSiguienteSugeridoPlanMadreConsulta(opciones);
	var grupos = [];
	var indices = {};
	var destacados = [];
	for (var i = 0; i < opciones.length; i++) {
		var tipoGrupo = etiquetaGrupoTratamientoPlanMadreConsulta(opciones[i]);
		var clave = tipoGrupo + "||" + opciones[i].venta;
		if (!indices[clave]) {
			indices[clave] = { titulo: opciones[i].venta || "Venta sin numero", tipo: tipoGrupo, items: [] };
			grupos.push(indices[clave]);
		}
		indices[clave].items.push(opciones[i]);
		if (recomendados[String(opciones[i].detalle)]) {
			destacados.push(opciones[i]);
		}
	}
	grupos.sort(function (a, b) {
		if (a.tipo == b.tipo) { return 0; }
		return a.tipo == "Venta base" ? -1 : 1;
	});
	var html = "<div class='clinical-treatment-plan-tree'>" +
		"<div class='clinical-treatment-plan-tree__head'><span>Plan madre</span><strong>Venta base / Ventas anexadas</strong><small>Seleccione el tratamiento realizado para registrar la evolucion.</small></div>";
	if (destacados.length) {
		html += "<section class='clinical-treatment-plan-recommended'>" +
			"<div class='clinical-treatment-plan-group__head'><strong>Recomendado para esta cita</strong><span>Tratamiento vinculado desde agenda</span></div>";
		for (var d = 0; d < destacados.length; d++) {
			var destacado = destacados[d];
			html += renderizarOpcionTratamientoPlanMadreConsulta(destacado, {
				recomendado: true,
				sugerido: false,
				completado: destacado.avance >= 100 || String(destacado.estadoClase).toLowerCase() == "completado",
				seleccionado: String(destacado.detalle) == seleccionado,
				hayAgenda: hayAgenda
			});
		}
		html += "</section>";
	}
	for (var g = 0; g < grupos.length; g++) {
		html += "<section class='clinical-treatment-plan-group clinical-treatment-plan-group--" + (grupos[g].tipo == "Venta anexada" ? "anexada" : "base") + "'>" +
			"<div class='clinical-treatment-plan-group__head'><strong>" + escaparHtmlConsulta(grupos[g].tipo) + "</strong><span>" + escaparHtmlConsulta(grupos[g].titulo) + " &middot; " + grupos[g].items.length + " tratamientos</span></div>";
		for (var j = 0; j < grupos[g].items.length; j++) {
			var item = grupos[g].items[j];
			var recomendado = !!recomendados[String(item.detalle)];
			var sugerido = !recomendado && String(item.detalle) == String(siguiente);
			var completado = item.avance >= 100 || String(item.estadoClase).toLowerCase() == "completado";
			html += renderizarOpcionTratamientoPlanMadreConsulta(item, {
				recomendado: recomendado,
				sugerido: sugerido,
				completado: completado,
				seleccionado: String(item.detalle) == seleccionado,
				hayAgenda: hayAgenda
			});
		}
		html += "</section>";
	}
	html += "</div>";
	lista.innerHTML = html;
	compactarListaTratamientosPlanMadreConsulta();
}

function seleccionarTratamientoPlanMadreConsulta(detalle) {
	var select = document.getElementById("inptTratamientoPlanMadreConsulta");
	if (!select) { return; }
	select.value = detalle;
	sincronizarTratamientoRealizadoConsulta();
}

function cargarTratamientosPlanMadreParaConsulta(conservarSeleccion) {
	var select = document.getElementById("inptTratamientoPlanMadreConsulta");
	if (!select) { return; }
	var hint = document.getElementById("consultaTratamientoPlanHint");
	var mantenerSeleccion = conservarSeleccion !== false;
	var valorActual = mantenerSeleccion ? (select.value || "") : "";
	var panelPlanMadre = document.querySelector("#divPreConsultaDetalle_Consulta #consultaPlanDefinitivoPanel .plan-definitivo-panel[data-plan-id]");
	var planIdActivo = panelPlanMadre ? (panelPlanMadre.getAttribute("data-plan-id") || "") : "";
	var items = panelPlanMadre ? panelPlanMadre.querySelectorAll(".plan-definitivo-item[data-detalle-tratamiento]") : [];
	var usados = {};
	var opciones = [];

	for (var i = 0; i < items.length; i++) {
		var item = items[i];
		var detalle = item.getAttribute("data-detalle-tratamiento") || "";
		var planItem = item.getAttribute("data-plan-id") || "";
		if (planIdActivo != "" && planItem != "" && planItem != planIdActivo) { continue; }
		if (detalle == "" || usados[detalle]) { continue; }
		usados[detalle] = true;
		var nombre = item.getAttribute("data-tratamiento-nombre") || ($(item).find(".plan-definitivo-item__top strong").first().text() || "Tratamiento");
		var avance = normalizarAvanceTratamientoConsulta(item.getAttribute("data-tratamiento-avance") || "0");
		var estado = item.getAttribute("data-tratamiento-estado") || ($(item).find(".consulta-treatment-status").first().text() || "Pendiente");
		var venta = item.getAttribute("data-tratamiento-venta") || "";
		var origen = ($(item).find(".plan-ruta-origen").first().text() || "").split("\u00b7")[0].trim();
		if (origen != "") { venta = origen; }
		opciones.push({
			detalle: detalle,
			nombre: nombre,
			avance: avance,
			estado: estado,
			estadoClase: item.getAttribute("data-tratamiento-estado-clase") || "",
			venta: venta,
			origen: item.getAttribute("data-tratamiento-origen") || "",
			riesgo: item.getAttribute("data-tratamiento-riesgo") || "",
			riesgoTexto: item.getAttribute("data-tratamiento-riesgo-texto") || ($(item).find(".riesgo-financiero-badge").first().text() || "")
		});
	}

	select.innerHTML = "";
	var placeholder = document.createElement("option");
	placeholder.value = "";
	placeholder.textContent = opciones.length ? "Seleccionar tratamiento del plan madre" : "Sin tratamientos planificados en plan madre";
	select.appendChild(placeholder);

	for (var j = 0; j < opciones.length; j++) {
		var opcion = document.createElement("option");
		opcion.value = opciones[j].detalle;
		opcion.textContent = opciones[j].nombre + " - " + opciones[j].avance + "%" + (opciones[j].venta ? " - " + opciones[j].venta : "");
		opcion.dataset.nombre = opciones[j].nombre;
		opcion.dataset.avance = opciones[j].avance;
		opcion.dataset.estado = opciones[j].estado;
		opcion.dataset.estadoClase = opciones[j].estadoClase;
		opcion.dataset.venta = opciones[j].venta;
		opcion.dataset.origen = opciones[j].origen;
		opcion.dataset.riesgo = opciones[j].riesgo;
		opcion.dataset.riesgoTexto = opciones[j].riesgoTexto;
		select.appendChild(opcion);
	}

	select.disabled = opciones.length == 0;
	select.value = "";
	if (mantenerSeleccion && valorActual != "" && usados[valorActual]) {
		select.value = valorActual;
	}
	if (hint && opciones.length == 0) {
		hint.textContent = planIdActivo == "" ? "Esta venta todavia no tiene un plan madre activo." : "Este plan madre todavia no tiene tratamientos activos para evolucionar.";
	}
	renderizarTratamientosPlanMadreVisualConsulta(opciones);
	cargarContextoAgendaPlanMadreConsulta(function () {
		preseleccionarTratamientoAgendaConsulta(opciones, contextoAtencionAgendaConsulta.activo && !mantenerSeleccion);
		renderizarTratamientosPlanMadreVisualConsulta(opciones);
		sincronizarTratamientoRealizadoConsulta();
	});
	sincronizarTratamientoRealizadoConsulta();
}

function sincronizarTratamientoRealizadoConsulta() {
	var select = document.getElementById("inptTratamientoPlanMadreConsulta");
	var avance = document.getElementById("inptAvanceTratamientoConsulta");
	var hint = document.getElementById("consultaTratamientoPlanHint");
	var aviso = document.getElementById("consultaTratamientoAgendaAviso");
	if (!select) { return; }
	var opcion = select.options[select.selectedIndex];
	var cards = document.querySelectorAll("#consultaTratamientoPlanLista .clinical-treatment-plan-option");
	for (var i = 0; i < cards.length; i++) {
		cards[i].classList.remove("is-selected");
	}
	if (!opcion || select.value == "") {
		if (avance) { avance.value = "0"; }
		if (hint && !select.disabled) {
			hint.textContent = "Seleccione un tratamiento planificado para registrar su evolucion.";
		}
		if (aviso) {
			aviso.className = "clinical-treatment-agenda-alert";
			aviso.innerHTML = "";
		}
		return;
	}
	var cardsSeleccionadas = document.querySelectorAll("#consultaTratamientoPlanLista .clinical-treatment-plan-option[data-detalle='" + select.value + "']");
	for (var j = 0; j < cardsSeleccionadas.length; j++) {
		cardsSeleccionadas[j].classList.add("is-selected");
	}
	var avanceActual = normalizarAvanceTratamientoConsulta(opcion.dataset.avance || "0");
	if (avance) { avance.value = avanceActual; }
	if (hint) {
		hint.textContent = "Tratamiento: " + (opcion.dataset.nombre || "Tratamiento") + " - Avance actual " + avanceActual + "% - " + (opcion.dataset.estado || "Pendiente");
	}
	if (aviso) {
		var agenda = contextoAgendaPlanMadreConsulta.agenda || { existe: false };
		var mapa = contextoAgendaPlanMadreConsulta.idsMapa || {};
		var ids = contextoAgendaPlanMadreConsulta.ids || [];
		if (agenda.existe && ids.length > 0 && !mapa[String(select.value)]) {
			aviso.className = "clinical-treatment-agenda-alert is-warning";
			aviso.innerHTML = "<strong>Atencion: tratamiento distinto al agendado.</strong><span>Los insumos odontologicos contemplados corresponden al tratamiento recomendado de la cita. Al guardar se creara un agendamiento imprevisto atendido.</span>";
		} else if (agenda.existe && ids.length == 0) {
			aviso.className = "clinical-treatment-agenda-alert is-warning";
			aviso.innerHTML = "<strong>Esta cita no tiene tratamiento vinculado desde calendario.</strong><span>Se permite registrar la evolucion, pero al guardar se creara un agendamiento imprevisto atendido.</span>";
		} else if (agenda.existe) {
			aviso.className = "clinical-treatment-agenda-alert is-ok";
			aviso.innerHTML = "<strong>Tratamiento recomendado para esta cita.</strong><span>Los insumos odontologicos de este tratamiento ya fueron contemplados.</span>";
		} else {
			aviso.className = "clinical-treatment-agenda-alert is-info";
			aviso.innerHTML = "<strong>Sin cita de calendario vinculada.</strong><span>La evolucion se registrara dentro del plan madre seleccionado.</span>";
		}
	}
}

/* PORCENTAJE DE TRATAMIENTOS */
function obtenerdatostrConsultaTratamiento(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	id_detalle_tratamientoConsulta = datostr.getAttribute("data-detalle-tratamiento") || $(datostr).children('td[id="td_id_1"]').html();
	let porcentaje = datostr.getAttribute("data-tratamiento-avance") || $(datostr).children('td[id="td_datos_1"]').html();
	let nombre = datostr.getAttribute("data-tratamiento-nombre") || ($(datostr).find(".consulta-treatment-row__name strong").first().text() || "Tratamiento");
	let estado = datostr.getAttribute("data-tratamiento-estado") || ($(datostr).find(".consulta-treatment-status").first().text() || "");
	let estadoClase = datostr.getAttribute("data-tratamiento-estado-clase") || "";
	let laboratorioDatos = tratamientoLaboratorioClinicoDatosTarjeta(datostr);
	guardarContextoPorcentajeProgresoConsulta()
	tratamientoProgresoActualConsulta = {
		id: id_detalle_tratamientoConsulta,
		nombre: nombre,
		porcentaje: parseInt(porcentaje, 10) || 0,
		estado: estado,
		estadoClase: estadoClase,
		laboratorio: laboratorioDatos.laboratorio,
		laboratorioDatos: laboratorioDatos,
		elemento: datostr
	};
	abrirModalEvolucionTratamientoConsulta(tratamientoProgresoActualConsulta);
	verEvolucion()
}

function obtenerDatosPlanDefinitivoTratamientoConsulta(event, item) {
	if (item === undefined) {
		item = event;
		event = null;
	}
	if (!item) { return; }
	if (event && esAccionInternaPlanDefinitivoConsulta(event.target)) {
		detenerEventoAccionPlanDefinitivoConsulta(event);
		return;
	}
	var seleccionados = document.querySelectorAll(".plan-definitivo-item.is-selected");
	for (var i = 0; i < seleccionados.length; i++) {
		seleccionados[i].classList.remove("is-selected");
	}
	item.classList.add("is-selected");
	id_detalle_tratamientoConsulta = item.getAttribute("data-detalle-tratamiento") || item.getAttribute("data-detalle-odontograma") || "";
	var codVentaTratamiento = item.getAttribute("data-tratamiento-venta") || "";
	var porcentaje = item.getAttribute("data-tratamiento-avance") || "0";
	var nombre = item.getAttribute("data-tratamiento-nombre") || ($(item).find(".plan-definitivo-item__top strong").first().text() || "Tratamiento");
	var estado = item.getAttribute("data-tratamiento-estado") || ($(item).find(".consulta-treatment-status").first().text() || "");
	var estadoClase = item.getAttribute("data-tratamiento-estado-clase") || "";
	var laboratorioDatos = tratamientoLaboratorioClinicoDatosTarjeta(item);
	guardarContextoPorcentajeProgresoConsulta();
	if (contextoPorcentajeProgresoConsulta && codVentaTratamiento != "") {
		contextoPorcentajeProgresoConsulta.cod_venta_evolucion = codVentaTratamiento;
	}
	tratamientoProgresoActualConsulta = {
		id: id_detalle_tratamientoConsulta,
		nombre: nombre,
		porcentaje: parseInt(porcentaje, 10) || 0,
		estado: estado,
		estadoClase: estadoClase,
		laboratorio: laboratorioDatos.laboratorio,
		laboratorioDatos: laboratorioDatos,
		elemento: item
	};
	abrirModalEvolucionTratamientoConsulta(tratamientoProgresoActualConsulta);
	verEvolucion();
}

function asegurarModalEvolucionTratamientoConsulta() {
	var existente = document.getElementById("modalEvolucionTratamientoConsulta");
	if (existente) { return existente; }
	var overlay = document.createElement("div");
	overlay.id = "overlayEvolucionTratamientoConsulta";
	overlay.className = "tratamiento-evolucion-overlay";
	overlay.style.display = "none";
	overlay.onclick = function () { cerrarModalEvolucionTratamientoConsulta(); };
	document.body.appendChild(overlay);
	var modal = document.createElement("div");
	modal.id = "modalEvolucionTratamientoConsulta";
	modal.className = "tratamiento-evolucion-modal";
	modal.style.display = "none";
	modal.innerHTML = "" +
		"<div class='tratamiento-evolucion-modal__head'>" +
		"	<div><span>Evoluci&oacute;n del tratamiento</span><h3 id='tituloEvolucionTratamientoConsulta'>Tratamiento</h3></div>" +
		"	<button type='button' title='Cerrar' onclick='cerrarModalEvolucionTratamientoConsulta()'>&times;</button>" +
		"</div>" +
		"<div class='tratamiento-evolucion-modal__body'>" +
		"	<div class='tratamiento-evolucion-resumen'>" +
		"		<span><strong>Actual</strong><b id='lblProgresoActualTratamientoConsulta'>0%</b></span>" +
		"		<span><strong>Nuevo</strong><b id='lblProgresoNuevoTratamientoConsulta'>0%</b></span>" +
		"		<span><strong>Estado</strong><b id='lblEstadoTratamientoConsulta'>Pendiente</b></span>" +
		"	</div>" +
		"	<section id='tratamientoDobleSeguimientoConsulta' class='tratamiento-doble-seguimiento' aria-live='polite' hidden></section>" +
		"	<input type='range' id='porcentajeEvolucionTratamientoConsulta' class='tratamiento-evolucion-range' min='0' max='100' value='0' oninput='mostrarValorSlider(this.value)'>" +
		"	<div class='tratamiento-evolucion-quick'>" +
		"		<button type='button' onclick='seleccionarPorcentajeEvolucionConsulta(0)'>0%</button>" +
		"		<button type='button' onclick='seleccionarPorcentajeEvolucionConsulta(25)'>25%</button>" +
		"		<button type='button' onclick='seleccionarPorcentajeEvolucionConsulta(50)'>50%</button>" +
		"		<button type='button' onclick='seleccionarPorcentajeEvolucionConsulta(75)'>75%</button>" +
		"		<button type='button' class='tratamiento-evolucion-quick__done' onclick='seleccionarPorcentajeEvolucionConsulta(100)'>Realizado</button>" +
		"	</div>" +
		"	<div class='tratamiento-evolucion-confirm' id='confirmarCompletadoTratamientoConsulta' style='display:none;'>" +
		"		<label><input type='checkbox' id='chkConfirmarTratamientoCompletado'> Confirmo que este tratamiento debe quedar como completado.</label>" +
		"	</div>" +
		"	<label class='tratamiento-evolucion-observacion'>Observaci&oacute;n breve <textarea id='txtObservacionEvolucionTratamientoConsulta' class='textarea-agenda' placeholder='Opcional'></textarea></label>" +
		"	<div class='tratamiento-evolucion-historial'>" +
		"		<div class='tratamiento-evolucion-historial__head'><strong>Historial reciente</strong><span>&Uacute;ltimas evoluciones</span></div>" +
		"		<div id='divEvolucionTratamientoConsulta' class='tratamiento-evolucion-historial__list'></div>" +
		"	</div>" +
		"</div>" +
		"<div class='tratamiento-evolucion-modal__footer'>" +
		"	<button type='button' class='tratamiento-evolucion-secondary' onclick='cerrarModalEvolucionTratamientoConsulta()'>Cancelar</button>" +
		"	<button type='button' class='tratamiento-evolucion-primary' onclick='guardarPorcentajeProgreso()'>Guardar evoluci&oacute;n</button>" +
		"</div>";
	document.body.appendChild(modal);
	return modal;
}

function abrirModalEvolucionTratamientoConsulta(datosTratamiento) {
	asegurarModalEvolucionTratamientoConsulta();
	document.getElementById("tituloEvolucionTratamientoConsulta").innerHTML = escaparHtmlConsulta(datosTratamiento.nombre);
	document.getElementById("lblProgresoActualTratamientoConsulta").textContent = datosTratamiento.porcentaje + "%";
	document.getElementById("lblEstadoTratamientoConsulta").textContent = datosTratamiento.estado || "Pendiente";
	document.getElementById("txtObservacionEvolucionTratamientoConsulta").value = "";
	var check = document.getElementById("chkConfirmarTratamientoCompletado");
	if (check) { check.checked = false; }
	mostrarValorSlider(datosTratamiento.porcentaje);
	document.getElementById("overlayEvolucionTratamientoConsulta").style.display = "";
	document.getElementById("modalEvolucionTratamientoConsulta").style.display = "";
	tratamientoLaboratorioClinicoRenderDobleSeguimiento();
}

function cerrarModalEvolucionTratamientoConsulta() {
	var overlay = document.getElementById("overlayEvolucionTratamientoConsulta");
	var modal = document.getElementById("modalEvolucionTratamientoConsulta");
	if (overlay) { overlay.style.display = "none"; }
	if (modal) { modal.style.display = "none"; }
}

function verCerrarCargarPorcentajeProgreso(){
	cerrarModalEvolucionTratamientoConsulta();
	var legacy = document.getElementById("divCargarTratamientoPorcentajeProgreso");
	if (legacy) {
		legacy.style.display = "none";
	}
}

function seleccionarPorcentajeEvolucionConsulta(valor) {
	mostrarValorSlider(valor);
}

function mostrarValorSlider(valor) {
	valor = parseInt(valor, 10);
	if (isNaN(valor)) { valor = 0; }
	if (valor < 0) { valor = 0; }
	if (valor > 100) { valor = 100; }
	var lblNuevo = document.getElementById("lblProgresoNuevoTratamientoConsulta");
	var rangeNuevo = document.getElementById("porcentajeEvolucionTratamientoConsulta");
	if (lblNuevo) { lblNuevo.textContent = valor + "%"; }
	if (rangeNuevo) { rangeNuevo.value = valor; }
	var confirmar = document.getElementById("confirmarCompletadoTratamientoConsulta");
	if (confirmar) {
		confirmar.style.display = (valor >= 100 && tratamientoProgresoActualConsulta.porcentaje < 100) ? "" : "none";
	}
	var legacyValor = document.getElementById("valor_slider_progreso_tratamiento");
	var legacyRange = document.getElementById("porcentaje");
	var legacyOculto = document.getElementById("inpt_progreso_tratamiento_oculto");
    if (legacyValor) { legacyValor.textContent = valor; }
    if (legacyRange) { legacyRange.value = valor; }
    if (legacyOculto) { legacyOculto.value = valor; }
  }

function guardarPorcentajeProgreso(){
		 restaurarContextoPorcentajeProgresoConsulta()
		 let codVentaConsultaVista = cod_ventaFKConsulta;
		 let codVentaConsultaProgreso = (contextoPorcentajeProgresoConsulta && contextoPorcentajeProgresoConsulta.cod_venta_evolucion)
			 ? contextoPorcentajeProgresoConsulta.cod_venta_evolucion
			 : codVentaConsultaVista;
		 let idAgendaProgreso = (contextoPorcentajeProgresoConsulta && contextoPorcentajeProgresoConsulta.id_agenda != "")
			 ? contextoPorcentajeProgresoConsulta.id_agenda
			 : (typeof idAbmAgenda !== "undefined" ? idAbmAgenda : "");
		 let inputNuevo = document.getElementById("porcentajeEvolucionTratamientoConsulta");
		 let inputLegacy = document.getElementById("inpt_progreso_tratamiento_oculto");
		 let porcentaje = inputNuevo ? inputNuevo.value : (inputLegacy ? inputLegacy.value : 0);
		 porcentaje = parseInt(porcentaje, 10) || 0;
		 if (porcentaje >= 100 && tratamientoProgresoActualConsulta.porcentaje < 100) {
			var check = document.getElementById("chkConfirmarTratamientoCompletado");
			if (!check || !check.checked) {
				ver_vetana_informativa("Para marcarlo como realizado, confirmá primero la evolución.");
				return;
			}
		 }
		 let observacion = document.getElementById("txtObservacionEvolucionTratamientoConsulta") ? document.getElementById("txtObservacionEvolucionTratamientoConsulta").value : "";
			obtener_datos_user();
			 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador, 
			 "id_detalle_tratamientoConsulta": id_detalle_tratamientoConsulta, 
			 "porcentaje": porcentaje, 
			 "cod_agendaFK": idAgendaProgreso,
			 "cod_venta": codVentaConsultaProgreso,
			 "observacion": observacion,
			"funt": "guardarPorcentajeProgreso"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
			type:"post",
		
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
				actualizarTarjetaTratamientoConsulta(id_detalle_tratamientoConsulta, (datos.porcentaje_nuevo !== undefined ? datos.porcentaje_nuevo : porcentaje), datos.estado_texto || "", datos.estado_clase || "");
				cerrarModalEvolucionTratamientoConsulta();
				ver_vetana_informativa('EVOLUCIÓN GUARDADA');
				if (datos.laboratorio && typeof tratamientoLaboratorioClinicoProcesarContexto == "function") {
					tratamientoLaboratorioClinicoProcesarContexto(datos.laboratorio);
				}
				var codVentaRefrescar = codVentaConsultaVista || codVentaConsultaProgreso;
				if (codVentaRefrescar != "") {
					setTimeout(function () {
						buscarDetalleVentaConsulta(codVentaRefrescar, true);
					}, 450);
				}
 if (idAgendaProgreso) {
	if (typeof cambiarEstadoAgendaDesdeModal === "function") {
		cambiarEstadoAgendaDesdeModal("ATENDIDO");
	} else if (typeof actualizarAgenda === "function") {
		actualizarAgenda(idAgendaProgreso, "", "", "ATENDIDO", { mantenerDetalle: true });
	}
 }
			} else {
				ver_vetana_informativa(datos.mensaje || "No se pudo guardar la evolución.");
			}
			}catch(error)
				{
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
				}
			}
			});
	
	
}

function actualizarTarjetaTratamientoConsulta(idDetalle, porcentaje, estadoTexto, estadoClase) {
	var items = document.querySelectorAll("#divPreConsultaDetalle_Consulta [data-detalle-tratamiento='" + idDetalle + "']");
	if (!items.length) {
		items = document.querySelectorAll("#divPreConsultaDetalle_Consulta [data-detalle-odontograma='" + idDetalle + "']");
	}
	if (!items.length) { return; }
	var estados = ["pendiente", "proceso", "completado", "cancelado"];
	for (var i = 0; i < items.length; i++) {
		var item = items[i];
		item.setAttribute("data-tratamiento-avance", porcentaje);
		if (estadoTexto) { item.setAttribute("data-tratamiento-estado", estadoTexto); }
		if (estadoClase) { item.setAttribute("data-tratamiento-estado-clase", estadoClase); }
		var porcentajeEl = item.querySelector(".consulta-treatment-percent");
		if (porcentajeEl) { porcentajeEl.textContent = porcentaje + "%"; }
		var estadoEl = item.querySelector(".consulta-treatment-status");
		if (estadoEl && estadoTexto) {
			estadoEl.textContent = estadoTexto;
			estadoEl.className = "consulta-treatment-status consulta-treatment-status--" + (estadoClase || "pendiente");
		}
		if (item.classList && estadoClase) {
			for (var j = 0; j < estados.length; j++) {
				item.classList.remove("consulta-treatment-row--" + estados[j]);
				item.classList.remove("plan-definitivo-item--" + estados[j]);
			}
			item.classList.add("consulta-treatment-row--" + estadoClase);
			if (item.classList.contains("plan-definitivo-item")) {
				item.classList.add("plan-definitivo-item--" + estadoClase);
				item.classList.toggle("plan-ruta-finalizado", estadoClase == "completado" || estadoClase == "cancelado");
				var nodo = item.querySelector(".plan-ruta-nodo span");
				if (nodo) {
					nodo.innerHTML = estadoClase == "completado" ? "&#10003;" : (item.getAttribute("data-plan-numero") || nodo.innerHTML);
				}
			}
		}
	}
}





function verEvolucion(){
	 
		 var contenedorEvolucion = document.getElementById("divEvolucionTratamientoConsulta") || document.getElementById("divTable_evolucionTratamiento");
		 if (contenedorEvolucion) {
			contenedorEvolucion.innerHTML = "<div class='tratamiento-evolucion-vacio'>Cargando historial...</div>";
		 }
		 
			obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador, 
			"cod_venta": id_detalle_tratamientoConsulta, 
			"funt": "verEvolucion"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmConsulta.php",
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
			var contenedorEvolucion = document.getElementById("divEvolucionTratamientoConsulta") || document.getElementById("divTable_evolucionTratamiento");
			if (contenedorEvolucion) {
				contenedorEvolucion.innerHTML=datos_buscados;
			}
 
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}


/* Integracion clinica minima con Trabajos de laboratorio.
 * Los hitos operativos elevan el piso de avance clinico, pero alcanzar 100%
 * nunca registra una instalacion automaticamente.
 */
var tratamientoLaboratorioClinicoEstado = {
	contexto: null,
	origen: {},
	cargando: false,
	error: "",
	fuente: "evolucion",
	mostrarPanel: true,
	elemento: null,
	detalleSolicitado: "",
	solicitudSecuencia: 0
};
var tratamientoLaboratorioClinicoCache = {};
var tratamientoLaboratorioRegularizacionUnidadesEstado = null;
var tratamientoLaboratorioClinicoMicrohiloSolicitudes = {};
var tratamientoLaboratorioClinicoMicrohiloDetalleCache = {};
var tratamientoLaboratorioClinicoMicrohiloNodoActual = null;
var tratamientoLaboratorioClinicoMicrohiloEventosInstalados = false;

function tratamientoLaboratorioClinicoEscapar(valor) {
	return String(valor === undefined || valor === null ? "" : valor)
		.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function tratamientoLaboratorioClinicoVerdadero(valor) {
	return valor === true || valor === 1 || valor === "1" || String(valor).toLowerCase() === "true";
}

function tratamientoLaboratorioClinicoDatosTarjeta(elemento) {
	var tarjeta = elemento && elemento.closest
		? elemento.closest("[data-detalle-tratamiento]") : elemento;
	if (!tarjeta || !tarjeta.getAttribute) {
		return { laboratorio: false };
	}
	return {
		laboratorio: tratamientoLaboratorioClinicoVerdadero(tarjeta.getAttribute("data-tratamiento-laboratorio")),
		cod_detalle_venta: tarjeta.getAttribute("data-detalle-tratamiento") || tarjeta.getAttribute("data-detalle-odontograma") || "",
		cod_venta: tarjeta.getAttribute("data-tratamiento-venta") || "",
		cod_producto: tarjeta.getAttribute("data-tratamiento-producto") || "",
		nombre_producto: tarjeta.getAttribute("data-tratamiento-nombre") || "Tratamiento",
		alcance_odontologico: tarjeta.getAttribute("data-tratamiento-alcance") || "",
		categoria: tarjeta.getAttribute("data-tratamiento-categoria") || "",
		ubicacion_falta: tratamientoLaboratorioClinicoVerdadero(tarjeta.getAttribute("data-laboratorio-ubicacion-falta")),
		trabajo_activo: tratamientoLaboratorioClinicoVerdadero(tarjeta.getAttribute("data-laboratorio-trabajo-activo")),
		antecedente_historico: tratamientoLaboratorioClinicoVerdadero(tarjeta.getAttribute("data-laboratorio-antecedente-historico")),
		requiere_regularizacion_administrativa: tratamientoLaboratorioClinicoVerdadero(tarjeta.getAttribute("data-laboratorio-regularizacion")),
		cantidad: tarjeta.getAttribute("data-tratamiento-cantidad") || "1",
		elemento: tarjeta
	};
}

function tratamientoLaboratorioClinicoPersonaTexto(persona, vacio) {
	vacio = vacio || "Sin informar";
	if (typeof persona === "string") { return persona || vacio; }
	persona = persona || {};
	return persona.nombre || persona.nombre_persona || persona.etiqueta || persona.label || vacio;
}

function tratamientoLaboratorioClinicoTrabajo(contexto) {
	contexto = contexto || {};
	return contexto.trabajo_activo || contexto.trabajo_resumen || null;
}

function tratamientoLaboratorioClinicoTrabajos(contexto) {
	contexto = contexto || {};
	var trabajos = Array.isArray(contexto.trabajos_activos) ? contexto.trabajos_activos : [];
	if (!trabajos.length && tratamientoLaboratorioClinicoTrabajo(contexto)) {
		trabajos = [tratamientoLaboratorioClinicoTrabajo(contexto)];
	}
	return trabajos;
}

function tratamientoLaboratorioClinicoRequiereUnidades(contexto) {
	contexto = contexto || {};
	return tratamientoLaboratorioClinicoVerdadero(contexto.requiere_regularizacion_unidades)
		|| tratamientoLaboratorioClinicoVerdadero((contexto.detalle || {}).requiere_regularizacion_unidades);
}

function tratamientoLaboratorioClinicoRegularizacionUnidades(contexto) {
	contexto = contexto || {};
	return contexto.regularizacion_unidades || null;
}

function tratamientoLaboratorioClinicoPuedeRegularizarUnidades(contexto) {
	contexto = contexto || {};
	return tratamientoLaboratorioClinicoVerdadero(contexto.puede_regularizar_unidades);
}

function tratamientoLaboratorioClinicoAntecedente(contexto) {
	contexto = contexto || {};
	return contexto.antecedente_historico || contexto.trabajo_historico || null;
}

function tratamientoLaboratorioClinicoEstadoNombre(trabajo) {
	trabajo = trabajo || {};
	if (trabajo.estado_nombre) { return trabajo.estado_nombre; }
	var codigo = String(trabajo.estado_derivado || trabajo.estado || "");
	var nombres = {
		pendiente_tecnico: "Técnico pendiente",
		pendiente_entrega_mecanico: "Pendiente de entrega al mecanico",
		en_transferencia_mecanico: "En traslado al laboratorio",
		en_laboratorio: "En poder del mecanico",
		en_transferencia_clinica: "En traslado a la clinica",
		pendiente_revision: "Pendiente de revision clinica",
		ajuste_solicitado: "Ajuste solicitado",
		listo_instalacion: "Listo para instalar",
		instalado: "Instalado",
		cancelado: "Cancelado"
	};
	return nombres[codigo] || (codigo ? codigo.replace(/_/g, " ") : "Sin estado operativo");
}

function tratamientoLaboratorioClinicoCicloTexto(trabajo) {
	trabajo = trabajo || {};
	var ciclo = trabajo.ciclo || {};
	if (typeof ciclo === "string") { return ciclo; }
	if (ciclo.etiqueta || ciclo.nombre) { return ciclo.etiqueta || ciclo.nombre; }
	var numero = ciclo.numero || ciclo.numero_ciclo || trabajo.ciclo_actual || 0;
	if (parseInt(numero, 10) > 1) { return "Ajuste " + (parseInt(numero, 10) - 1); }
	return parseInt(numero, 10) === 1 ? "Original" : "Sin informar";
}

function tratamientoLaboratorioClinicoHitos(contexto) {
	var trabajo = tratamientoLaboratorioClinicoTrabajo(contexto);
	var hitos = trabajo && Array.isArray(trabajo.hitos_recientes) ? trabajo.hitos_recientes : [];
	if (!hitos.length && Array.isArray((contexto || {}).hitos_recientes)) { hitos = contexto.hitos_recientes; }
	if (!hitos.length && Array.isArray((contexto || {}).recorrido)) { hitos = contexto.recorrido.slice(-3); }
	return hitos.slice(-3);
}

function tratamientoLaboratorioClinicoFechaCorta(valor) {
	if (!valor) { return ""; }
	var fecha = new Date(String(valor).replace(" ", "T"));
	if (isNaN(fecha.getTime())) { return String(valor); }
	return fecha.toLocaleDateString("es-PY", { day: "2-digit", month: "2-digit", year: "numeric" });
}

function tratamientoLaboratorioClinicoPuedeVerResumen(contexto) {
	contexto = contexto || {};
	return contexto.puede_ver_resumen === undefined
		? true : tratamientoLaboratorioClinicoVerdadero(contexto.puede_ver_resumen);
}

function tratamientoLaboratorioClinicoPuedeAbrir(contexto) {
	contexto = contexto || {};
	return contexto.puede_abrir_ficha === undefined
		? true : tratamientoLaboratorioClinicoVerdadero(contexto.puede_abrir_ficha);
}

function tratamientoLaboratorioClinicoSoloLectura(contexto) {
	contexto = contexto || {};
	if (contexto.solo_lectura !== undefined) {
		return tratamientoLaboratorioClinicoVerdadero(contexto.solo_lectura);
	}
	if (tratamientoLaboratorioClinicoTrabajo(contexto)) {
		return !tratamientoLaboratorioClinicoPuedeAbrir(contexto);
	}
	return contexto.puede_iniciar !== undefined
		&& !tratamientoLaboratorioClinicoVerdadero(contexto.puede_iniciar);
}

function tratamientoLaboratorioClinicoPuedeAsignarUbicacion(contexto) {
	contexto = contexto || {};
	return tratamientoLaboratorioClinicoVerdadero(contexto.puede_asignar_ubicacion);
}

function tratamientoLaboratorioClinicoUbicacionFalta(contexto) {
	var datos = tratamientoLaboratorioClinicoEstado.elemento
		? tratamientoLaboratorioClinicoDatosTarjeta(tratamientoLaboratorioClinicoEstado.elemento) : {};
	if (datos.ubicacion_falta) { return true; }
	return ((contexto && contexto.bloqueos) || []).some(function(bloqueo) {
		var codigo = String((bloqueo && bloqueo.codigo) || "").toLowerCase();
		return codigo.indexOf("ubicacion") >= 0 || codigo.indexOf("pieza") >= 0
			|| codigo.indexOf("arcada") >= 0 || codigo.indexOf("sector") >= 0;
	});
}

function tratamientoLaboratorioClinicoRequiereRegularizacion(contexto) {
	contexto = contexto || {};
	var detalle = contexto.detalle || {};
	if (tratamientoLaboratorioClinicoVerdadero(contexto.requiere_regularizacion_administrativa)
		|| tratamientoLaboratorioClinicoVerdadero(detalle.requiere_regularizacion_administrativa)) {
		return true;
	}
	if (tratamientoLaboratorioClinicoVerdadero(detalle.es_detalle_agrupado)
		|| tratamientoLaboratorioClinicoRequiereUnidades(contexto)
		|| tratamientoLaboratorioClinicoRegularizacionUnidades(contexto)) {
		return false;
	}
	var cantidad = parseFloat(detalle.cantidad || contexto.cantidad_detalle || contexto.cantidad || 1);
	return !isNaN(cantidad) && Math.abs(cantidad - 1) > 0.000001;
}

function tratamientoLaboratorioClinicoEsBloqueoUbicacion(bloqueo) {
	var codigo = String((bloqueo && bloqueo.codigo) || "").toLowerCase();
	if (codigo.indexOf("ubicacion") >= 0) { return true; }
	return [
		"pieza_individual_invalida", "multipieza_invalida", "arcada_requerida",
		"sector_requerido", "pieza_requerida", "piezas_requeridas"
	].indexOf(codigo) >= 0;
}

function tratamientoLaboratorioClinicoBloqueosNoUbicacion(contexto) {
	return ((contexto && contexto.bloqueos) || []).filter(function (bloqueo) {
		return !tratamientoLaboratorioClinicoEsBloqueoUbicacion(bloqueo);
	});
}

function tratamientoLaboratorioClinicoPuedeAbrirHistoricos(contexto) {
	contexto = contexto || {};
	return tratamientoLaboratorioClinicoVerdadero(contexto.es_auditor)
		&& (contexto.historicos_disponibles === undefined
			|| tratamientoLaboratorioClinicoVerdadero(contexto.historicos_disponibles));
}

function tratamientoLaboratorioClinicoDetalle(contexto) {
	contexto = contexto || {};
	var detalle = contexto.detalle || {};
	if (!detalle.cod_detalle_venta) { detalle.cod_detalle_venta = contexto.cod_detalle_venta || 0; }
	if (!detalle.cod_venta) { detalle.cod_venta = contexto.cod_venta || 0; }
	if (!detalle.nro_venta) { detalle.nro_venta = contexto.numero_venta || ""; }
	if (!detalle.nombre_producto) { detalle.nombre_producto = contexto.producto || "Tratamiento"; }
	if (detalle.requiere_laboratorio === undefined) { detalle.requiere_laboratorio = contexto.requiere_laboratorio; }
	return detalle;
}

function tratamientoLaboratorioClinicoAccionPermitida(contexto, codigo) {
	var acciones = (contexto && contexto.acciones_permitidas) || {};
	if (Array.isArray(acciones)) {
		return acciones.some(function(accion) {
			if (typeof accion === "string") { return accion === codigo; }
			return accion && (accion.codigo === codigo || accion.code === codigo)
				&& accion.permitido !== false && accion.permitido !== 0 && accion.permitido !== "0";
		});
	}
	if (!Object.prototype.hasOwnProperty.call(acciones, codigo)) { return false; }
	var valor = acciones[codigo];
	return valor !== false && valor !== 0 && valor !== "0" && valor !== null;
}

function tratamientoLaboratorioClinicoTextoUbicacion(ubicacion) {
	ubicacion = ubicacion || {};
	if (tratamientoLaboratorioClinicoVerdadero(ubicacion.boca_completa)) { return "Boca completa"; }
	if (Array.isArray(ubicacion.piezas) && ubicacion.piezas.length) { return "Piezas " + ubicacion.piezas.join(", "); }
	if (ubicacion.pieza) { return "Pieza " + ubicacion.pieza; }
	if (ubicacion.arcada) { return "Arcada " + String(ubicacion.arcada).replace(/_/g, " "); }
	if (ubicacion.cuadrante) { return "Cuadrante " + String(ubicacion.cuadrante).replace(/_/g, " "); }
	return ubicacion.alcance ? String(ubicacion.alcance).replace(/_/g, " ") : "Ubicacion clinica";
}

function tratamientoLaboratorioClinicoActualizarTarjetas(contexto) {
	var detalle = tratamientoLaboratorioClinicoDetalle(contexto || tratamientoLaboratorioClinicoEstado.contexto || {});
	var idDetalle = String(detalle.cod_detalle_venta || tratamientoLaboratorioClinicoEstado.detalleSolicitado || "");
	if (!idDetalle) { return; }
	var trabajo = tratamientoLaboratorioClinicoTrabajo(contexto);
	var antecedente = tratamientoLaboratorioClinicoAntecedente(contexto);
	var faltaUbicacion = tratamientoLaboratorioClinicoUbicacionFalta(contexto);
	var soloLectura = tratamientoLaboratorioClinicoSoloLectura(contexto);
	var requiereRegularizacion = tratamientoLaboratorioClinicoRequiereRegularizacion(contexto);
	var requiereUnidades = tratamientoLaboratorioClinicoRequiereUnidades(contexto);
	var regularizacionUnidades = tratamientoLaboratorioClinicoRegularizacionUnidades(contexto);
	var trabajos = tratamientoLaboratorioClinicoTrabajos(contexto);
	var bloqueosNoUbicacion = tratamientoLaboratorioClinicoBloqueosNoUbicacion(contexto);
	var texto = "Preparar trabajo de laboratorio";
	var resumen = "";
	if (tratamientoLaboratorioClinicoEstado.cargando) {
		texto = "Consultando laboratorio...";
	} else if (trabajo) {
		texto = tratamientoLaboratorioClinicoPuedeAbrir(contexto)
			? "Abrir trabajo de laboratorio" : "Ver resumen de laboratorio";
		resumen = tratamientoLaboratorioClinicoEstadoNombre(trabajo) + (soloLectura ? " · Solo lectura" : "");
	} else if (antecedente && antecedente.disponible !== false) {
		texto = "Ver antecedente de laboratorio";
		resumen = "Declarado por Administracion" + (antecedente.etiqueta ? " · " + antecedente.etiqueta : "");
	} else if (requiereRegularizacion) {
		texto = "Regularizar para laboratorio";
		resumen = String(detalle.cantidad || contexto.cantidad_detalle || 1)
			+ " unidades registradas \u00b7 Administracion";
	} else if (faltaUbicacion) {
		texto = tratamientoLaboratorioClinicoPuedeAsignarUbicacion(contexto)
			? "Asignar ubicacion para iniciar" : "Ver requisito de ubicacion";
		resumen = tratamientoLaboratorioClinicoPuedeAsignarUbicacion(contexto)
			? "Ubicacion clinica pendiente" : "Ubicacion pendiente · Solo lectura";
	} else if (bloqueosNoUbicacion.length) {
		texto = "Revisar requisitos de laboratorio";
		resumen = bloqueosNoUbicacion[0].mensaje || "Hay requisitos pendientes";
	} else if (soloLectura) {
		texto = "Ver resumen de laboratorio";
		resumen = "Sin trabajo activo · Solo lectura";
	}
	if (trabajo && trabajos.length > 1) {
		texto = "Abrir " + trabajos.length + " trabajos de laboratorio";
		resumen = "Mismo origen \u00b7 Seguimientos independientes";
	} else if (!trabajo && requiereUnidades) {
		texto = tratamientoLaboratorioClinicoPuedeRegularizarUnidades(contexto)
			? "Designar " + (detalle.cantidad_unidades_laboratorio || detalle.cantidad || 0) + " trabajos"
			: "Ver regularizacion por unidades";
		resumen = "Un selector por cada trabajo \u00b7 Mismo origen";
	} else if (!trabajo && regularizacionUnidades) {
		texto = "Preparar " + (regularizacionUnidades.cantidad_unidades || 0) + " trabajos";
		resumen = "Piezas designadas \u00b7 Origen " + (regularizacionUnidades.codigo_origen || "-");
	}
	Array.prototype.forEach.call(document.querySelectorAll("[data-tratamiento-laboratorio='1'][data-detalle-tratamiento]"), function(tarjeta) {
		if (String(tarjeta.getAttribute("data-detalle-tratamiento") || "") !== idDetalle) { return; }
		var boton = tarjeta.querySelector("[data-tratamiento-laboratorio-accion]");
		var etiqueta = tarjeta.querySelector("[data-tratamiento-laboratorio-accion-texto]");
		var resumenEl = tarjeta.querySelector("[data-tratamiento-laboratorio-resumen]");
		if (boton) { boton.disabled = tratamientoLaboratorioClinicoEstado.cargando; }
		if (etiqueta) { etiqueta.textContent = texto; }
		if (resumenEl) {
			resumenEl.textContent = resumen;
			resumenEl.hidden = !resumen;
			resumenEl.classList.toggle("is-readonly", soloLectura);
			resumenEl.classList.toggle("is-historical", (!!antecedente || requiereRegularizacion) && !trabajo);
			resumenEl.classList.toggle("is-regularization", (requiereRegularizacion || requiereUnidades || !!regularizacionUnidades) && !trabajo && !antecedente);
		}
	});
}

function tratamientoLaboratorioClinicoHitosHtml(hitos, clase) {
	if (!hitos || !hitos.length) { return ""; }
	return "<ol class='" + clase + "'>" + hitos.map(function(hito) {
		var titulo = hito.titulo || hito.estado_nombre || hito.tipo_evento || hito.tipo || "Hito registrado";
		var fecha = tratamientoLaboratorioClinicoFechaCorta(hito.fecha_servidor || hito.fecha || hito.fecha_hora);
		return "<li><span aria-hidden='true'></span><b>" + tratamientoLaboratorioClinicoEscapar(titulo) + "</b>"
			+ (fecha ? "<time>" + tratamientoLaboratorioClinicoEscapar(fecha) + "</time>" : "") + "</li>";
	}).join("") + "</ol>";
}

function tratamientoLaboratorioClinicoMicrohilos(contexto) {
	contexto = contexto || {};
	if (Array.isArray(contexto.micro_hilos_activos) && contexto.micro_hilos_activos.length) {
		return contexto.micro_hilos_activos;
	}
	return tratamientoLaboratorioClinicoTrabajos(contexto).map(function (trabajo) {
		var hitos = Array.isArray(trabajo.hitos_recientes) ? trabajo.hitos_recientes : [];
		return {
			id: trabajo.id,
			codigo_visible: trabajo.codigo_visible || "",
			codigo_origen: trabajo.codigo_origen || "",
			unidad_origen: trabajo.unidad_origen || 1,
			cantidad_unidades_origen: trabajo.cantidad_unidades_origen || 1,
			estado_derivado: trabajo.estado_derivado || "",
			estado_nombre: tratamientoLaboratorioClinicoEstadoNombre(trabajo),
			nodos: hitos,
			total_nodos: hitos.length,
			nodos_ocultos: 0
		};
	});
}

function tratamientoLaboratorioClinicoIniciales(nombre) {
	var partes = String(nombre || "Usuario").trim().split(/\s+/).filter(function (parte) { return parte; });
	if (!partes.length) { return "U"; }
	return (partes[0].charAt(0) + (partes.length > 1 ? partes[partes.length - 1].charAt(0) : "")).toUpperCase();
}

function tratamientoLaboratorioClinicoAvatarMicrohilo(persona) {
	persona = persona || {};
	var nombre = persona.nombre || persona.nombre_persona || persona.etiqueta || "Usuario registrado";
	var avatar = persona.avatar || persona.avatar_url || persona.foto || "";
	return "<span class='consulta-laboratorio-microhilo__avatar' aria-hidden='true'>"
		+ "<span>" + tratamientoLaboratorioClinicoEscapar(tratamientoLaboratorioClinicoIniciales(nombre)) + "</span>"
		+ (avatar ? "<img src='" + tratamientoLaboratorioClinicoEscapar(avatar) + "' alt='' loading='lazy' onerror='this.remove()'>" : "")
		+ "</span>";
}

function tratamientoLaboratorioClinicoNodoMicrohiloHtml(nodo, trabajo) {
	nodo = nodo || {};
	var actor = nodo.actor || nodo.responsable || {};
	var titulo = nodo.titulo || nodo.estado_nombre || nodo.tipo_evento || "Hito registrado";
	var fecha = tratamientoLaboratorioClinicoFechaCorta(nodo.fecha || nodo.fecha_servidor || nodo.fecha_inicio);
	var clases = "consulta-laboratorio-microhilo__nodo";
	if (tratamientoLaboratorioClinicoVerdadero(nodo.actual)) { clases += " is-current"; }
	if (tratamientoLaboratorioClinicoVerdadero(nodo.pendiente)) { clases += " is-pending"; }
	var etiqueta = "Consultar " + titulo + (actor.nombre ? ", responsable " + actor.nombre : "");
	return "<li class='" + clases + "'>"
		+ "<button type='button' data-laboratorio-mini-nodo data-laboratorio-mini-trabajo='"
		+ tratamientoLaboratorioClinicoEscapar(trabajo.id || "") + "' data-laboratorio-mini-evento='"
		+ tratamientoLaboratorioClinicoEscapar(nodo.id_evento || "") + "' data-laboratorio-mini-origen='"
		+ tratamientoLaboratorioClinicoEscapar(nodo.origen || "operativo")
		+ "' aria-haspopup='dialog' aria-expanded='false' aria-label='"
		+ tratamientoLaboratorioClinicoEscapar(etiqueta)
		+ "' onclick='event.stopPropagation(); tratamientoLaboratorioClinicoAbrirNodoMicrohilo(this)'>"
		+ tratamientoLaboratorioClinicoAvatarMicrohilo(actor)
		+ (tratamientoLaboratorioClinicoVerdadero(nodo.tiene_media)
			? "<i class='fa-solid fa-camera consulta-laboratorio-microhilo__media' aria-label='Con archivos'></i>" : "")
		+ "<span class='consulta-laboratorio-microhilo__nodo-copy'><strong>"
		+ tratamientoLaboratorioClinicoEscapar(titulo) + "</strong>"
		+ (fecha ? "<time>" + tratamientoLaboratorioClinicoEscapar(fecha) + "</time>" : "")
		+ "</span></button></li>";
}

function tratamientoLaboratorioClinicoCadenaMicrohiloHtml(trabajo, cantidad) {
	trabajo = trabajo || {};
	var nodos = Array.isArray(trabajo.nodos) ? trabajo.nodos : [];
	var ocultos = parseInt(trabajo.nodos_ocultos, 10) || 0;
	var unidad = parseInt(trabajo.unidad_origen, 10) || 1;
	var total = Math.max(parseInt(trabajo.cantidad_unidades_origen, 10) || 1, cantidad || 1);
	var estado = trabajo.estado_nombre || tratamientoLaboratorioClinicoEstadoNombre(trabajo);
	var htmlNodos = "";
	nodos.forEach(function (nodo, indice) {
		if (indice === 1 && ocultos > 0) {
			htmlNodos += "<li class='consulta-laboratorio-microhilo__ocultos' title='"
				+ tratamientoLaboratorioClinicoEscapar(ocultos + " nodos intermedios")
				+ "'><span>+" + ocultos + "</span></li>";
		}
		htmlNodos += tratamientoLaboratorioClinicoNodoMicrohiloHtml(nodo, trabajo);
	});
	if (!htmlNodos) {
		htmlNodos = "<li class='consulta-laboratorio-microhilo__vacio'>Sin nodos disponibles</li>";
	}
	return "<article class='consulta-laboratorio-microhilo__cadena"
		+ (total > 1 ? " is-grouped" : "") + "' data-laboratorio-mini-cadena='"
		+ tratamientoLaboratorioClinicoEscapar(trabajo.id || "") + "'>"
		+ "<header><span><i class='fa-solid fa-diagram-project' aria-hidden='true'></i>"
		+ (total > 1 ? "Trabajo " + unidad + " de " + total : "Laboratorio")
		+ "</span><strong title='" + tratamientoLaboratorioClinicoEscapar(estado) + "'>"
		+ tratamientoLaboratorioClinicoEscapar(estado) + "</strong>"
		+ "<button type='button' aria-label='Abrir trabajo completo' title='Abrir trabajo completo' "
		+ "data-laboratorio-mini-abrir='" + tratamientoLaboratorioClinicoEscapar(trabajo.id || "")
		+ "' onclick='event.stopPropagation(); tratamientoLaboratorioClinicoAbrirTrabajoMicrohilo(this)'>"
		+ "<i class='fa-solid fa-chevron-right' aria-hidden='true'></i></button></header>"
		+ "<ol class='consulta-laboratorio-microhilo__pista'>" + htmlNodos + "</ol></article>";
}

function tratamientoLaboratorioClinicoPrepararSlotMicrohilo(tarjeta) {
	if (!tarjeta || !tarjeta.querySelector) { return null; }
	var slot = tarjeta.querySelector("[data-laboratorio-mini-hilo-slot]");
	if (!slot) { return null; }
	slot.hidden = false;
	var cuerpo = tarjeta.querySelector(".plan-definitivo-item__body");
	if (cuerpo) { cuerpo.classList.add("is-laboratorio-microhilo"); }
	var accionAnterior = tarjeta.querySelector(".plan-ruta-ubicacion .consulta-laboratorio-card-action");
	if (accionAnterior) { accionAnterior.hidden = true; }
	return slot;
}

function tratamientoLaboratorioClinicoRenderizarMicrohilos(contexto, idDetalle) {
	var microhilos = tratamientoLaboratorioClinicoMicrohilos(contexto);
	var selector = "[data-tratamiento-laboratorio='1'][data-detalle-tratamiento='"
		+ String(idDetalle).replace(/'/g, "\\'") + "']";
	Array.prototype.forEach.call(document.querySelectorAll(selector), function (tarjeta) {
		var slot = tratamientoLaboratorioClinicoPrepararSlotMicrohilo(tarjeta);
		if (!slot) { return; }
		var cuerpo = tarjeta.querySelector(".plan-definitivo-item__body");
		var agrupado = microhilos.length > 1;
		if (cuerpo) { cuerpo.classList.toggle("is-laboratorio-microhilo-agrupado", agrupado); }
		slot.classList.toggle("is-grouped", agrupado);
		slot.innerHTML = "<section class='consulta-laboratorio-microhilo' data-laboratorio-mini-hilo "
			+ "data-laboratorio-mini-hilo-estado='listo' aria-busy='false' aria-label='Estado actual del laboratorio'>"
			+ microhilos.map(function (trabajo) {
				return tratamientoLaboratorioClinicoCadenaMicrohiloHtml(trabajo, microhilos.length);
			}).join("") + "</section>";
	});
}

function tratamientoLaboratorioClinicoRenderizarErrorMicrohilo(idDetalle, mensaje) {
	var selector = "[data-tratamiento-laboratorio='1'][data-detalle-tratamiento='"
		+ String(idDetalle).replace(/'/g, "\\'") + "']";
	Array.prototype.forEach.call(document.querySelectorAll(selector), function (tarjeta) {
		var slot = tratamientoLaboratorioClinicoPrepararSlotMicrohilo(tarjeta);
		if (!slot) { return; }
		slot.innerHTML = "<section class='consulta-laboratorio-microhilo is-error' data-laboratorio-mini-hilo "
			+ "data-laboratorio-mini-hilo-estado='error' aria-busy='false'><span>"
			+ tratamientoLaboratorioClinicoEscapar(mensaje || "No se pudo consultar el hilo.")
			+ "</span><button type='button' onclick='event.stopPropagation(); "
			+ "tratamientoLaboratorioClinicoReintentarMicrohilo(this)'>Reintentar</button></section>";
	});
}

function tratamientoLaboratorioClinicoCargarMicrohiloTarjeta(tarjeta, forzar) {
	var datos = tratamientoLaboratorioClinicoDatosTarjeta(tarjeta);
	if (!datos.laboratorio || !datos.trabajo_activo || !datos.cod_detalle_venta) { return; }
	var idDetalle = String(datos.cod_detalle_venta);
	if (!forzar && tratamientoLaboratorioClinicoCache[idDetalle]
		&& tratamientoLaboratorioClinicoMicrohilos(tratamientoLaboratorioClinicoCache[idDetalle]).length) {
		tratamientoLaboratorioClinicoRenderizarMicrohilos(tratamientoLaboratorioClinicoCache[idDetalle], idDetalle);
		return;
	}
	if (tratamientoLaboratorioClinicoMicrohiloSolicitudes[idDetalle]) { return; }
	tratamientoLaboratorioClinicoPrepararSlotMicrohilo(datos.elemento);
	var base = tratamientoLaboratorioClinicoContextoDesdeDatos(datos);
	var solicitud = tratamientoLaboratorioClinicoSolicitudContexto(idDetalle);
	tratamientoLaboratorioClinicoMicrohiloSolicitudes[idDetalle] = solicitud;
	solicitud.done(function (respuesta) {
		if (!respuesta || (respuesta.ok !== undefined && !tratamientoLaboratorioClinicoVerdadero(respuesta.ok))) {
			tratamientoLaboratorioClinicoRenderizarErrorMicrohilo(
				idDetalle,
				(respuesta && respuesta.mensaje) || "No se pudo consultar el hilo."
			);
			return;
		}
		var contexto = tratamientoLaboratorioClinicoFusionarRespuesta(base, respuesta);
		tratamientoLaboratorioClinicoCache[idDetalle] = contexto;
		tratamientoLaboratorioClinicoRenderizarMicrohilos(contexto, idDetalle);
	}).fail(function (jqXHR) {
		var mensaje = jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.mensaje
			? jqXHR.responseJSON.mensaje : "No se pudo consultar el hilo.";
		tratamientoLaboratorioClinicoRenderizarErrorMicrohilo(idDetalle, mensaje);
	}).always(function () {
		delete tratamientoLaboratorioClinicoMicrohiloSolicitudes[idDetalle];
	});
}

function tratamientoLaboratorioClinicoInicializarMicrohilos(root) {
	var base = root && root.querySelectorAll ? root : document;
	Array.prototype.forEach.call(
		base.querySelectorAll("[data-tratamiento-laboratorio='1'][data-laboratorio-trabajo-activo='1'] [data-laboratorio-mini-hilo-slot]"),
		function (slot) {
			var tarjeta = slot.closest("[data-detalle-tratamiento]");
			if (tarjeta) { tratamientoLaboratorioClinicoCargarMicrohiloTarjeta(tarjeta, false); }
		}
	);
}

function tratamientoLaboratorioClinicoReintentarMicrohilo(elemento) {
	var tarjeta = elemento && elemento.closest ? elemento.closest("[data-detalle-tratamiento]") : null;
	if (!tarjeta) { return; }
	var idDetalle = String(tarjeta.getAttribute("data-detalle-tratamiento") || "");
	if (idDetalle) {
		delete tratamientoLaboratorioClinicoCache[idDetalle];
		delete tratamientoLaboratorioClinicoMicrohiloSolicitudes[idDetalle];
	}
	var slot = tratamientoLaboratorioClinicoPrepararSlotMicrohilo(tarjeta);
	if (slot) {
		slot.innerHTML = "<section class='consulta-laboratorio-microhilo is-loading' data-laboratorio-mini-hilo "
			+ "data-laboratorio-mini-hilo-estado='cargando' aria-busy='true'>"
			+ "<span class='consulta-laboratorio-microhilo__loader' aria-hidden='true'></span>"
			+ "<span>Consultando hilo del trabajo...</span></section>";
	}
	tratamientoLaboratorioClinicoCargarMicrohiloTarjeta(tarjeta, true);
}

function tratamientoLaboratorioClinicoAbrirTrabajoMicrohilo(elemento) {
	var idTrabajo = elemento ? elemento.getAttribute("data-laboratorio-mini-abrir") : "";
	if (!idTrabajo || !tratamientoLaboratorioClinicoModuloDisponible()) { return; }
	tratamientoLaboratorioClinicoCerrarNodoMicrohilo();
	window.TrabajoLaboratorio.abrirTrabajo(idTrabajo);
}

function tratamientoLaboratorioClinicoSolicitudTrabajo(idTrabajo) {
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "obtenerTrabajo");
	datos.append("id_trabajo", idTrabajo);
	return $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmTrabajoLaboratorio.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json"
	});
}

function tratamientoLaboratorioClinicoObtenerDetalleMicrohilo(idTrabajo) {
	var clave = String(idTrabajo || "");
	if (!clave) { return null; }
	if (tratamientoLaboratorioClinicoMicrohiloDetalleCache[clave]) {
		return tratamientoLaboratorioClinicoMicrohiloDetalleCache[clave];
	}
	var solicitud = tratamientoLaboratorioClinicoSolicitudTrabajo(clave).then(function (respuesta) {
		if (!respuesta || (respuesta.ok !== undefined && !tratamientoLaboratorioClinicoVerdadero(respuesta.ok))) {
			throw new Error((respuesta && respuesta.mensaje) || "No se pudo consultar el nodo.");
		}
		return (respuesta && respuesta.datos) || respuesta || {};
	}, function (jqXHR) {
		var mensaje = jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.mensaje
			? jqXHR.responseJSON.mensaje : "No se pudo consultar el nodo.";
		throw new Error(mensaje);
	});
	tratamientoLaboratorioClinicoMicrohiloDetalleCache[clave] = solicitud;
	solicitud.then(null, function () {
		delete tratamientoLaboratorioClinicoMicrohiloDetalleCache[clave];
	});
	return solicitud;
}

function tratamientoLaboratorioClinicoAsegurarPopoverMicrohilo() {
	var popover = document.getElementById("tratamientoLaboratorioMicrohiloPopover");
	if (!popover) {
		popover = document.createElement("section");
		popover.id = "tratamientoLaboratorioMicrohiloPopover";
		popover.className = "consulta-laboratorio-nodo-popover";
		popover.setAttribute("role", "dialog");
		popover.setAttribute("aria-modal", "false");
		popover.setAttribute("aria-label", "Detalle del nodo del laboratorio");
		popover.hidden = true;
		document.body.appendChild(popover);
	}
	if (!tratamientoLaboratorioClinicoMicrohiloEventosInstalados) {
		tratamientoLaboratorioClinicoMicrohiloEventosInstalados = true;
		document.addEventListener("click", function (event) {
			var actual = tratamientoLaboratorioClinicoMicrohiloNodoActual;
			var abierto = document.getElementById("tratamientoLaboratorioMicrohiloPopover");
			if (!actual || !abierto || abierto.hidden) { return; }
			if (abierto.contains(event.target)
				|| (event.target.closest && event.target.closest("[data-laboratorio-mini-nodo]"))) { return; }
			tratamientoLaboratorioClinicoCerrarNodoMicrohilo();
		});
		document.addEventListener("keydown", function (event) {
			if (event.key === "Escape" || event.keyCode === 27) {
				if (document.getElementById("tratamientoLaboratorioMicrohiloVisor")) {
					tratamientoLaboratorioClinicoCerrarVisorMicrohilo();
					return;
				}
				tratamientoLaboratorioClinicoCerrarNodoMicrohilo();
			}
		});
	}
	return popover;
}

function tratamientoLaboratorioClinicoPosicionarPopoverMicrohilo(disparador, popover) {
	if (!disparador || !popover) { return; }
	popover.style.left = "8px";
	popover.style.top = "8px";
	var origen = disparador.getBoundingClientRect();
	var caja = popover.getBoundingClientRect();
	var ancho = window.innerWidth || document.documentElement.clientWidth;
	var alto = window.innerHeight || document.documentElement.clientHeight;
	var izquierda = Math.max(8, Math.min(origen.left + origen.width / 2 - caja.width / 2, ancho - caja.width - 8));
	var arriba = origen.bottom + 8;
	if (arriba + caja.height > alto - 8) {
		arriba = Math.max(8, origen.top - caja.height - 8);
	}
	popover.style.left = Math.round(izquierda) + "px";
	popover.style.top = Math.round(arriba) + "px";
}

function tratamientoLaboratorioClinicoCerrarNodoMicrohilo() {
	var popover = document.getElementById("tratamientoLaboratorioMicrohiloPopover");
	if (tratamientoLaboratorioClinicoMicrohiloNodoActual) {
		tratamientoLaboratorioClinicoMicrohiloNodoActual.setAttribute("aria-expanded", "false");
	}
	tratamientoLaboratorioClinicoMicrohiloNodoActual = null;
	if (popover) {
		popover.hidden = true;
		popover.innerHTML = "";
	}
}

function tratamientoLaboratorioClinicoBuscarNodoDetalle(datos, idEvento, origen) {
	datos = datos || {};
	var trabajo = datos.trabajo || datos.item || {};
	var eventos = Array.isArray(datos.recorrido_operativo) ? datos.recorrido_operativo
		: (Array.isArray(datos.eventos) ? datos.eventos : []);
	var cadena = Array.isArray(datos.cadena_custodia) ? datos.cadena_custodia
		: (Array.isArray(datos.hilo_custodia) ? datos.hilo_custodia : []);
	var id = String(idEvento || "");
	var buscar = function (lista) {
		for (var i = 0; i < lista.length; i++) {
			if (String(lista[i].id_evento || lista[i].id || "") === id) { return lista[i]; }
		}
		return null;
	};
	var nodoCustodia = buscar(cadena);
	var nodoOperativo = buscar(eventos);
	var nodo = origen === "custodia" ? (nodoCustodia || nodoOperativo) : (nodoOperativo || nodoCustodia);
	nodo = nodo || {};
	var idsVersion = Array.isArray(nodo.eventos_version) ? nodo.eventos_version.map(String) : [id];
	if (idsVersion.indexOf(id) < 0) { idsVersion.push(id); }
	var media = (Array.isArray(datos.media) ? datos.media : []).filter(function (archivo) {
		return idsVersion.indexOf(String(archivo.id_evento || "")) >= 0;
	});
	return { trabajo: trabajo, nodo: nodo, media: media, origen: origen };
}

function tratamientoLaboratorioClinicoCampoNodoHtml(etiqueta, valor, modificado) {
	if (valor === undefined || valor === null || String(valor).trim() === "") { return ""; }
	return "<div class='consulta-laboratorio-nodo-popover__campo"
		+ (modificado ? " is-modified" : "") + "'><small>"
		+ tratamientoLaboratorioClinicoEscapar(etiqueta) + "</small><strong>"
		+ tratamientoLaboratorioClinicoEscapar(valor) + "</strong></div>";
}

function tratamientoLaboratorioClinicoRenderNodoMicrohilo(detalle) {
	var nodo = detalle.nodo || {};
	var trabajo = detalle.trabajo || {};
	var actor = nodo.responsable || nodo.actor || {};
	var titulo = nodo.titulo || nodo.estado_nombre || nodo.tipo_evento || "Hito registrado";
	var fecha = nodo.fecha_inicio || nodo.fecha_servidor || nodo.fecha_hora || nodo.fecha || "";
	var datosTrabajo = nodo.datos_trabajo || {
		tipo_trabajo: trabajo.tipo_trabajo,
		producto: trabajo.nombre_producto || trabajo.producto_nombre,
		colorimetro: trabajo.colorimetro,
		paciente: trabajo.nombre_paciente || trabajo.paciente_nombre,
		doctor: trabajo.nombre_doctor || trabajo.doctor_nombre,
		mecanico_dental: trabajo.nombre_tecnico || trabajo.mecanico_nombre,
		fecha_retiro: trabajo.fecha_retiro,
		fecha_entrega: trabajo.fecha_entrega,
		costo_estimado: trabajo.costo_estimado,
		local: trabajo.nombre_local || trabajo.local_nombre,
		observacion: trabajo.instrucciones
	};
	var modificados = Array.isArray(nodo.campos_modificados) ? nodo.campos_modificados : [];
	var cambio = function (clave) { return modificados.indexOf(clave) >= 0; };
	var campos = ""
		+ tratamientoLaboratorioClinicoCampoNodoHtml("Tipo de trabajo", datosTrabajo.tipo_trabajo || datosTrabajo.producto, cambio("cod_tipo_trabajo"))
		+ tratamientoLaboratorioClinicoCampoNodoHtml("Colorimetria", datosTrabajo.colorimetro, cambio("colorimetro"))
		+ tratamientoLaboratorioClinicoCampoNodoHtml("Paciente", datosTrabajo.paciente, false)
		+ tratamientoLaboratorioClinicoCampoNodoHtml("Doctor", datosTrabajo.doctor || "No asignado", cambio("cod_especialista"))
		+ tratamientoLaboratorioClinicoCampoNodoHtml("Mecanico dental", datosTrabajo.mecanico_dental || "No asignado", cambio("cod_mecanico_dental") || cambio("cod_tecnico_usuario"))
		+ tratamientoLaboratorioClinicoCampoNodoHtml("Retiro", datosTrabajo.fecha_retiro, cambio("fecha_retiro"))
		+ tratamientoLaboratorioClinicoCampoNodoHtml("Entrega", datosTrabajo.fecha_entrega || "Sin fecha definida", cambio("fecha_entrega"))
		+ tratamientoLaboratorioClinicoCampoNodoHtml("Local", datosTrabajo.local || nodo.local, cambio("cod_local"))
		+ tratamientoLaboratorioClinicoCampoNodoHtml("Costo", datosTrabajo.costo_estimado, cambio("costo_estimado"));
	var observacion = nodo.observacion || datosTrabajo.observacion || "";
	var archivos = detalle.media.length ? "<div class='consulta-laboratorio-nodo-popover__media'><small>Archivos del nodo</small><div>"
		+ detalle.media.map(function (archivo) {
			var miniatura = archivo.miniatura_url || archivo.url_visualizacion || "";
			var esPdf = String(archivo.mime || "").toLowerCase() === "application/pdf";
			return "<button type='button' data-laboratorio-mini-media='"
				+ tratamientoLaboratorioClinicoEscapar(archivo.id || archivo.id_media || "")
				+ "' data-laboratorio-mini-media-nombre='"
				+ tratamientoLaboratorioClinicoEscapar(archivo.nombre || archivo.nombre_original || "Archivo")
				+ "' onclick='event.stopPropagation(); tratamientoLaboratorioClinicoAbrirMediaMicrohilo(this)'>"
				+ (esPdf ? "<i class='fa-solid fa-file-pdf' aria-hidden='true'></i>"
					: (miniatura ? "<img src='" + tratamientoLaboratorioClinicoEscapar(miniatura) + "' alt=''>"
						: "<i class='fa-solid fa-image' aria-hidden='true'></i>"))
				+ "<span>" + tratamientoLaboratorioClinicoEscapar(archivo.nombre || archivo.nombre_original || "Archivo") + "</span></button>";
		}).join("") + "</div></div>" : "";
	return "<header class='consulta-laboratorio-nodo-popover__header'><div><small>Nodo del hilo</small><strong>"
		+ tratamientoLaboratorioClinicoEscapar(titulo)
		+ "</strong></div><button type='button' aria-label='Cerrar' onclick='tratamientoLaboratorioClinicoCerrarNodoMicrohilo()'>&times;</button></header>"
		+ "<div class='consulta-laboratorio-nodo-popover__responsable'>"
		+ tratamientoLaboratorioClinicoAvatarMicrohilo(actor)
		+ "<span><strong>" + tratamientoLaboratorioClinicoEscapar(actor.nombre || "Usuario registrado")
		+ "</strong><small>" + tratamientoLaboratorioClinicoEscapar(actor.rol || "Responsable")
		+ (fecha ? " · " + tratamientoLaboratorioClinicoFechaCorta(fecha) : "")
		+ (nodo.local ? " · " + tratamientoLaboratorioClinicoEscapar(nodo.local) : "")
		+ "</small></span></div>"
		+ (campos ? "<div class='consulta-laboratorio-nodo-popover__grid'>" + campos + "</div>" : "")
		+ (observacion ? "<div class='consulta-laboratorio-nodo-popover__observacion'><small>Observacion</small><p>"
			+ tratamientoLaboratorioClinicoEscapar(observacion) + "</p></div>" : "")
		+ archivos;
}

function tratamientoLaboratorioClinicoAbrirNodoMicrohilo(disparador) {
	if (!disparador) { return; }
	var idTrabajo = disparador.getAttribute("data-laboratorio-mini-trabajo");
	var idEvento = disparador.getAttribute("data-laboratorio-mini-evento");
	var origen = disparador.getAttribute("data-laboratorio-mini-origen") || "operativo";
	if (!idTrabajo || !idEvento) { return; }
	tratamientoLaboratorioClinicoCerrarNodoMicrohilo();
	var popover = tratamientoLaboratorioClinicoAsegurarPopoverMicrohilo();
	tratamientoLaboratorioClinicoMicrohiloNodoActual = disparador;
	disparador.setAttribute("aria-expanded", "true");
	popover.innerHTML = "<div class='consulta-laboratorio-nodo-popover__loading'>"
		+ "<span class='consulta-laboratorio-microhilo__loader' aria-hidden='true'></span>"
		+ "<strong>Consultando nodo...</strong></div>";
	popover.hidden = false;
	tratamientoLaboratorioClinicoPosicionarPopoverMicrohilo(disparador, popover);
	var solicitud = tratamientoLaboratorioClinicoObtenerDetalleMicrohilo(idTrabajo);
	if (!solicitud) { return; }
	solicitud.then(function (datos) {
		if (tratamientoLaboratorioClinicoMicrohiloNodoActual !== disparador) { return; }
		var detalle = tratamientoLaboratorioClinicoBuscarNodoDetalle(datos, idEvento, origen);
		popover.innerHTML = tratamientoLaboratorioClinicoRenderNodoMicrohilo(detalle);
		tratamientoLaboratorioClinicoPosicionarPopoverMicrohilo(disparador, popover);
	}, function (error) {
		if (tratamientoLaboratorioClinicoMicrohiloNodoActual !== disparador) { return; }
		popover.innerHTML = "<div class='consulta-laboratorio-nodo-popover__error'><i class='fa-solid fa-triangle-exclamation' aria-hidden='true'></i>"
			+ "<strong>No se pudo consultar el nodo</strong><span>"
			+ tratamientoLaboratorioClinicoEscapar((error && error.message) || "Intente nuevamente.")
			+ "</span><button type='button' onclick='tratamientoLaboratorioClinicoCerrarNodoMicrohilo()'>Cerrar</button></div>";
		tratamientoLaboratorioClinicoPosicionarPopoverMicrohilo(disparador, popover);
	});
}

function tratamientoLaboratorioClinicoCerrarVisorMicrohilo() {
	var visor = document.getElementById("tratamientoLaboratorioMicrohiloVisor");
	if (visor && visor.parentNode) { visor.parentNode.removeChild(visor); }
}

function tratamientoLaboratorioClinicoAbrirMediaMicrohilo(elemento) {
	var idMedia = elemento ? elemento.getAttribute("data-laboratorio-mini-media") : "";
	var nombre = elemento ? elemento.getAttribute("data-laboratorio-mini-media-nombre") : "Archivo del nodo";
	if (!idMedia || !window.TrabajoLaboratorio || typeof window.TrabajoLaboratorio.obtenerMedia !== "function") { return; }
	tratamientoLaboratorioClinicoCerrarVisorMicrohilo();
	var visor = document.createElement("div");
	visor.id = "tratamientoLaboratorioMicrohiloVisor";
	visor.className = "consulta-laboratorio-media-visor";
	visor.innerHTML = "<section role='dialog' aria-modal='true' aria-label='"
		+ tratamientoLaboratorioClinicoEscapar(nombre)
		+ "'><header><strong>" + tratamientoLaboratorioClinicoEscapar(nombre)
		+ "</strong><button type='button' aria-label='Cerrar' onclick='tratamientoLaboratorioClinicoCerrarVisorMicrohilo()'>&times;</button></header>"
		+ "<div class='consulta-laboratorio-media-visor__body'><span class='consulta-laboratorio-microhilo__loader' aria-hidden='true'></span><strong>Abriendo archivo...</strong></div></section>";
	visor.onclick = function (event) {
		if (event.target === visor) { tratamientoLaboratorioClinicoCerrarVisorMicrohilo(); }
	};
	document.body.appendChild(visor);
	window.TrabajoLaboratorio.obtenerMedia(idMedia).then(function (media) {
		var cuerpo = visor.querySelector(".consulta-laboratorio-media-visor__body");
		if (!cuerpo) { return; }
		var mime = String(media.mime || "").toLowerCase();
		cuerpo.innerHTML = mime === "application/pdf"
			? "<iframe src='" + tratamientoLaboratorioClinicoEscapar(media.src) + "' title='"
				+ tratamientoLaboratorioClinicoEscapar(nombre) + "'></iframe>"
			: "<img src='" + tratamientoLaboratorioClinicoEscapar(media.src) + "' alt='"
				+ tratamientoLaboratorioClinicoEscapar(nombre) + "'>";
	}, function (error) {
		var cuerpo = visor.querySelector(".consulta-laboratorio-media-visor__body");
		if (cuerpo) {
			cuerpo.innerHTML = "<i class='fa-solid fa-triangle-exclamation' aria-hidden='true'></i><strong>"
				+ tratamientoLaboratorioClinicoEscapar((error && error.message) || "No se pudo abrir el archivo.")
				+ "</strong>";
		}
	});
}

function tratamientoLaboratorioClinicoRenderDobleSeguimiento() {
	var bloque = document.getElementById("tratamientoDobleSeguimientoConsulta");
	if (!bloque) { return; }
	var datos = tratamientoProgresoActualConsulta || {};
	if (!datos.laboratorio) {
		bloque.hidden = true;
		bloque.innerHTML = "";
		return;
	}
	var idDetalle = String(datos.id || "");
	var contexto = tratamientoLaboratorioClinicoCache[idDetalle] || null;
	if (tratamientoLaboratorioClinicoEstado.detalleSolicitado === idDetalle && tratamientoLaboratorioClinicoEstado.contexto) {
		contexto = tratamientoLaboratorioClinicoEstado.contexto;
	}
	var cargando = tratamientoLaboratorioClinicoEstado.detalleSolicitado === idDetalle && tratamientoLaboratorioClinicoEstado.cargando;
	var trabajo = contexto ? tratamientoLaboratorioClinicoTrabajo(contexto) : null;
	var trabajos = contexto ? tratamientoLaboratorioClinicoTrabajos(contexto) : [];
	var antecedente = contexto ? tratamientoLaboratorioClinicoAntecedente(contexto) : null;
	var laboratorioHtml = "<strong>Estado disponible bajo consulta</strong><span>Los hitos operativos actualizaran el piso de avance clinico.</span>";
	var accionTexto = "Consultar seguimiento de laboratorio";
	if (cargando) {
		laboratorioHtml = "<strong>Consultando seguimiento...</strong><span>Revisando estado, responsables y permisos.</span>";
		accionTexto = "Consultando...";
	} else if (trabajo && trabajos.length > 1) {
		laboratorioHtml = "<strong>" + trabajos.length + " trabajos independientes</strong>"
			+ "<span>Comparten el mismo codigo de origen y cada uno conserva su pieza, estado y recorrido.</span>";
		accionTexto = tratamientoLaboratorioClinicoPuedeAbrir(contexto)
			? "Abrir " + trabajos.length + " trabajos de laboratorio" : "Ver resumen del lote";
	} else if (trabajo) {
		var tecnico = tratamientoLaboratorioClinicoPersonaTexto(trabajo.tecnico, "Sin mecanico informado");
		var custodio = tratamientoLaboratorioClinicoPersonaTexto(trabajo.custodio, "Sin custodio informado");
		laboratorioHtml = "<strong>" + tratamientoLaboratorioClinicoEscapar(tratamientoLaboratorioClinicoEstadoNombre(trabajo)) + "</strong>"
			+ "<span>Mecanico: " + tratamientoLaboratorioClinicoEscapar(tecnico) + " · Custodio: " + tratamientoLaboratorioClinicoEscapar(custodio) + " · Ciclo: " + tratamientoLaboratorioClinicoEscapar(tratamientoLaboratorioClinicoCicloTexto(trabajo)) + "</span>"
			+ tratamientoLaboratorioClinicoHitosHtml(tratamientoLaboratorioClinicoHitos(contexto), "tratamiento-doble-seguimiento__hitos");
		accionTexto = tratamientoLaboratorioClinicoPuedeAbrir(contexto) ? "Abrir trabajo de laboratorio" : "Ver resumen de laboratorio";
	} else if (antecedente && antecedente.disponible !== false) {
		laboratorioHtml = "<em>Declarado por Administracion</em><strong>" + tratamientoLaboratorioClinicoEscapar(antecedente.etiqueta || "Antecedente historico") + "</strong>"
			+ "<span>" + tratamientoLaboratorioClinicoEscapar(antecedente.descripcion || "Antecedente convalidado; no representa progreso clinico ni una instalacion clinica confirmada.") + "</span>";
		accionTexto = "Ver antecedente de laboratorio";
	} else if (contexto && tratamientoLaboratorioClinicoRequiereUnidades(contexto)) {
		var detalleUnidades = tratamientoLaboratorioClinicoDetalle(contexto);
		var cantidadUnidades = detalleUnidades.cantidad_unidades_laboratorio || detalleUnidades.cantidad || 0;
		laboratorioHtml = "<em>Designacion guiada</em><strong>" + tratamientoLaboratorioClinicoEscapar(cantidadUnidades) + " trabajos independientes</strong>"
			+ "<span>Se abrira un selector dental por cada trabajo; cada selector permite una o varias piezas.</span>";
		accionTexto = tratamientoLaboratorioClinicoPuedeRegularizarUnidades(contexto)
			? "Designar trabajo 1 de " + cantidadUnidades : "Ver designacion requerida";
	} else if (contexto && tratamientoLaboratorioClinicoRegularizacionUnidades(contexto)) {
		var regularizacion = tratamientoLaboratorioClinicoRegularizacionUnidades(contexto);
		laboratorioHtml = "<em>Piezas designadas</em><strong>" + tratamientoLaboratorioClinicoEscapar(regularizacion.cantidad_unidades || 0) + " trabajos listos para preparar</strong>"
			+ "<span>Origen " + tratamientoLaboratorioClinicoEscapar(regularizacion.codigo_origen || "-") + ".</span>";
		accionTexto = tratamientoLaboratorioClinicoAccionPermitida(contexto, "iniciarTrabajosAgrupados")
			? "Preparar " + (regularizacion.cantidad_unidades || 0) + " trabajos" : "Ver piezas designadas";
	} else if (contexto && tratamientoLaboratorioClinicoRequiereRegularizacion(contexto)) {
		laboratorioHtml = "<em>Regularizacion administrativa</em><strong>Cantidad no convertible automaticamente</strong>"
			+ "<span>Administracion debe revisar el detalle sin modificar la venta antes de iniciar un trabajo nuevo.</span>";
		accionTexto = "Ver regularizacion requerida";
	} else if (contexto && tratamientoLaboratorioClinicoUbicacionFalta(contexto)) {
		var puedeAsignar = tratamientoLaboratorioClinicoPuedeAsignarUbicacion(contexto);
		laboratorioHtml = "<strong>Ubicacion pendiente</strong><span>"
			+ (puedeAsignar ? "Asigna la ubicacion clinica antes de iniciar el trabajo."
				: "Un usuario autorizado debe completar la ubicacion clinica antes de iniciar el trabajo.") + "</span>";
		accionTexto = puedeAsignar ? "Asignar ubicacion para iniciar" : "Ver requisito pendiente";
	} else if (contexto && Array.isArray(contexto.bloqueos) && contexto.bloqueos.length) {
		laboratorioHtml = "<strong>Requisitos pendientes</strong><span>"
			+ tratamientoLaboratorioClinicoEscapar(contexto.bloqueos[0].mensaje || "Revise los requisitos antes de iniciar el trabajo.") + "</span>";
		accionTexto = "Revisar requisitos de laboratorio";
	} else if (contexto && tratamientoLaboratorioClinicoSoloLectura(contexto)) {
		laboratorioHtml = "<strong>Sin trabajo activo</strong><span>Resumen disponible en modo solo lectura.</span>";
		accionTexto = "Ver resumen de laboratorio";
	} else if (contexto) {
		laboratorioHtml = "<strong>Listo para preparar</strong><span>El trabajo se inicia en el modulo de laboratorio.</span>";
		accionTexto = "Preparar trabajo de laboratorio";
	}
	bloque.innerHTML = "<header><div><small>Tratamiento con laboratorio</small><h4>Seguimiento clinico y de laboratorio</h4></div><span>Coordinados por hitos trazables</span></header>"
		+ "<div class='tratamiento-doble-seguimiento__carriles'>"
		+ "<section class='tratamiento-doble-seguimiento__carril tratamiento-doble-seguimiento__carril--clinico'><i class='fa-solid fa-tooth' aria-hidden='true'></i><div><small>Avance clinico</small><strong>" + tratamientoLaboratorioClinicoEscapar(datos.porcentaje || 0) + "% · " + tratamientoLaboratorioClinicoEscapar(datos.estado || "Pendiente") + "</strong><span>Tambien avanza por hitos de laboratorio y nunca retrocede.</span></div></section>"
		+ "<section class='tratamiento-doble-seguimiento__carril tratamiento-doble-seguimiento__carril--laboratorio'><i class='fa-solid fa-microscope' aria-hidden='true'></i><div><small>Recorrido de laboratorio</small>" + laboratorioHtml + "</div></section>"
		+ "</div><div class='tratamiento-doble-seguimiento__acciones'><button type='button' onclick='tratamientoLaboratorioClinicoAccionDobleSeguimiento()' " + (cargando ? "disabled" : "") + ">" + tratamientoLaboratorioClinicoEscapar(accionTexto) + "</button></div>";
	bloque.hidden = false;
}

function tratamientoLaboratorioClinicoAsegurarPanel() {
	if (!document.getElementById("tratamientoLaboratorioClinicoEstilos")) {
		var estilos = document.createElement("style");
		estilos.id = "tratamientoLaboratorioClinicoEstilos";
		estilos.textContent = ""
			+ "#tratamientoLaboratorioClinicoPanel{position:fixed;z-index:100020;right:18px;bottom:18px;width:min(420px,calc(100vw - 28px));max-height:calc(100vh - 36px);overflow:auto;background:#fff;border:1px solid #cfe1e6;border-radius:15px;box-shadow:0 20px 55px rgba(8,38,66,.24);font-family:inherit;color:#17364b}"
			+ "#tratamientoLaboratorioClinicoPanel[hidden]{display:none!important}.tlc-head{display:flex;justify-content:space-between;gap:12px;padding:15px 17px;background:linear-gradient(135deg,#092f55,#075f78);color:#fff;border-radius:14px 14px 0 0}.tlc-head small{color:#8be0d0;font-weight:700;text-transform:uppercase}.tlc-head h3{font-size:17px;margin:3px 0}.tlc-head p{font-size:12px;margin:0;color:#dbeff5}.tlc-close{border:0;background:rgba(255,255,255,.14);color:#fff;border-radius:8px;width:31px;height:31px;font-size:20px;cursor:pointer}"
			+ ".tlc-body{padding:14px 17px;display:grid;gap:10px}.tlc-summary{border:1px solid #dce9ed;border-radius:11px;padding:11px;background:#fbfdfd}.tlc-summary b{display:block;font-size:14px}.tlc-summary span{display:block;color:#5a707a;font-size:11px;margin-top:3px}.tlc-tags{display:flex;flex-wrap:wrap;gap:5px;margin-top:8px}.tlc-tag{padding:5px 8px;border-radius:999px;background:#e9f7f4;color:#096a65;font-size:11px;font-weight:700}.tlc-alert{padding:9px 10px;border-radius:9px;background:#fff3df;color:#7a4b00;font-size:12px;border-left:4px solid #ef9b28}.tlc-alert--error{background:#fff0f0;color:#8b2525;border-left-color:#d84d4d}.tlc-status{padding:9px 10px;border-radius:9px;background:#eef5fb;color:#234f6b;font-size:12px}"
			+ ".tlc-actions{display:flex;justify-content:flex-end;gap:7px;flex-wrap:wrap;padding:0 17px 15px}.tlc-btn{border:0;border-radius:8px;padding:9px 12px;cursor:pointer;font-weight:700;font-size:12px}.tlc-btn--primary{background:#087f75;color:#fff}.tlc-btn--install{background:#235fa4;color:#fff}.tlc-btn--secondary{background:#eaf1f4;color:#24495d}@media(max-width:600px){#tratamientoLaboratorioClinicoPanel{right:8px;bottom:8px;width:calc(100vw - 16px)}}";
		estilos.textContent += ".tlc-unit-list{display:grid;gap:7px;margin-top:9px}.tlc-unit{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 9px;border:1px solid #d6e7eb;border-radius:9px;background:#fff}.tlc-unit div{min-width:0}.tlc-unit b,.tlc-unit span{display:block}.tlc-unit span{font-size:11px;color:#58717d}.tlc-unit button{border:0;background:#e7f5f3;color:#086b65;border-radius:7px;padding:6px 8px;font-weight:700;cursor:pointer}.tlc-origin{font-family:monospace;font-size:11px;color:#5a4a86}.tlc-status--units{border-left:4px solid #6f54a5;background:#f6f2fb}";
		document.head.appendChild(estilos);
	}
	var panel = document.getElementById("tratamientoLaboratorioClinicoPanel");
	if (!panel) {
		panel = document.createElement("section");
		panel.id = "tratamientoLaboratorioClinicoPanel";
		panel.setAttribute("role", "dialog");
		panel.setAttribute("aria-modal", "false");
		panel.setAttribute("aria-labelledby", "tratamientoLaboratorioClinicoTitulo");
		panel.hidden = true;
		document.body.appendChild(panel);
	}
	return panel;
}

function tratamientoLaboratorioClinicoRender() {
	var panel = tratamientoLaboratorioClinicoAsegurarPanel();
	var contexto = tratamientoLaboratorioClinicoEstado.contexto || {};
	var detalle = tratamientoLaboratorioClinicoDetalle(contexto);
	var ubicaciones = Array.isArray(contexto.ubicaciones) ? contexto.ubicaciones : [];
	var bloqueos = Array.isArray(contexto.bloqueos) ? contexto.bloqueos : [];
	var trabajo = tratamientoLaboratorioClinicoTrabajo(contexto);
	var trabajosPanel = tratamientoLaboratorioClinicoTrabajos(contexto);
	var antecedente = tratamientoLaboratorioClinicoAntecedente(contexto);
	var soloLectura = tratamientoLaboratorioClinicoSoloLectura(contexto);
	var faltaUbicacion = tratamientoLaboratorioClinicoUbicacionFalta(contexto);
	var requiereRegularizacion = tratamientoLaboratorioClinicoRequiereRegularizacion(contexto);
	var requiereUnidades = tratamientoLaboratorioClinicoRequiereUnidades(contexto);
	var regularizacionUnidades = tratamientoLaboratorioClinicoRegularizacionUnidades(contexto);
	var regularizacionGuiada = tratamientoLaboratorioRegularizacionUnidadesEstado;
	if (regularizacionGuiada && regularizacionGuiada.detalle) {
		detalle = regularizacionGuiada.detalle;
	}
	var bloqueosVisibles = requiereRegularizacion ? bloqueos.filter(function (bloqueo) {
		return String((bloqueo && bloqueo.codigo) || "") === "cantidad_laboratorio_invalida";
	}) : (requiereUnidades ? bloqueos.filter(function (bloqueo) {
		return String((bloqueo && bloqueo.codigo) || "") === "unidades_agrupadas_sin_designar";
	}) : (regularizacionUnidades ? bloqueos.filter(function (bloqueo) {
		var codigo = String((bloqueo && bloqueo.codigo) || "");
		return codigo !== "unidades_agrupadas_sin_designar";
	}) : bloqueos));
	var subtitulo = tratamientoLaboratorioClinicoEstado.fuente === "evolucion"
		? "La evolucion ya fue guardada; los hitos de laboratorio mantendran sincronizado el avance."
		: "Consulta guiada con avance clinico sincronizado por hitos de laboratorio.";
	tratamientoLaboratorioClinicoActualizarTarjetas(contexto);
	tratamientoLaboratorioClinicoRenderDobleSeguimiento();
	if (!tratamientoLaboratorioClinicoEstado.mostrarPanel) {
		panel.hidden = true;
		return;
	}
	var html = "<header class='tlc-head'><div><small>Tratamiento con laboratorio</small><h3 id='tratamientoLaboratorioClinicoTitulo'>Siguiente paso guiado</h3><p>" + tratamientoLaboratorioClinicoEscapar(subtitulo) + "</p></div><button type='button' class='tlc-close' onclick='tratamientoLaboratorioClinicoCerrar()' aria-label='Cerrar'>&times;</button></header><div class='tlc-body'>";
	html += "<section class='tlc-summary'><b>" + tratamientoLaboratorioClinicoEscapar(detalle.nombre_producto || "Tratamiento") + "</b><span>Venta " + tratamientoLaboratorioClinicoEscapar(detalle.nro_venta || detalle.cod_venta || "-") + " &middot; Detalle #" + tratamientoLaboratorioClinicoEscapar(detalle.cod_detalle_venta || "-") + " &middot; Cantidad " + tratamientoLaboratorioClinicoEscapar(detalle.cantidad || contexto.cantidad_detalle || 1) + "</span>";
	if (ubicaciones.length) {
		html += "<div class='tlc-tags'>" + ubicaciones.map(function(ubicacion) {
			return "<span class='tlc-tag'>" + tratamientoLaboratorioClinicoEscapar(tratamientoLaboratorioClinicoTextoUbicacion(ubicacion)) + "</span>";
		}).join("") + "</div>";
	}
	html += "</section>";
	if (tratamientoLaboratorioClinicoEstado.cargando) {
		html += "<div class='tlc-status'>Revisando trabajos abiertos, permisos y responsables...</div>";
	}
	if (tratamientoLaboratorioClinicoEstado.error) {
		html += "<div class='tlc-alert tlc-alert--error' role='alert'>" + tratamientoLaboratorioClinicoEscapar(tratamientoLaboratorioClinicoEstado.error) + "</div>";
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && bloqueosVisibles.length) {
		html += bloqueosVisibles.map(function(bloqueo) {
			return "<div class='tlc-alert'>" + tratamientoLaboratorioClinicoEscapar(bloqueo.mensaje || "Este trabajo necesita una revision antes de continuar.") + "</div>";
		}).join("");
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && trabajo) {
		if (trabajosPanel.length > 1) {
			html += "<section class='tlc-status tlc-status--units'><strong>" + trabajosPanel.length + " trabajos independientes</strong><br>Comparten el origen "
				+ tratamientoLaboratorioClinicoEscapar(trabajosPanel[0].codigo_origen || trabajo.codigo_origen || "-")
				+ " y se gestionan por separado en la vista operativa.</section>";
		} else {
			html += "<section class='tlc-status tlc-status--work'><strong>" + tratamientoLaboratorioClinicoEscapar(trabajo.codigo_visible || ("Trabajo #" + trabajo.id)) + "</strong><span>" + tratamientoLaboratorioClinicoEscapar(tratamientoLaboratorioClinicoEstadoNombre(trabajo)) + "</span>"
			+ "<div class='tlc-detail-grid'><span><small>Mecanico</small><b>" + tratamientoLaboratorioClinicoEscapar(tratamientoLaboratorioClinicoPersonaTexto(trabajo.tecnico)) + "</b></span><span><small>Custodio</small><b>" + tratamientoLaboratorioClinicoEscapar(tratamientoLaboratorioClinicoPersonaTexto(trabajo.custodio)) + "</b></span><span><small>Ciclo</small><b>" + tratamientoLaboratorioClinicoEscapar(tratamientoLaboratorioClinicoCicloTexto(trabajo)) + "</b></span></div>"
			+ tratamientoLaboratorioClinicoHitosHtml(tratamientoLaboratorioClinicoHitos(contexto), "tlc-hitos") + "</section>";
		}
		if (soloLectura) {
			html += "<div class='tlc-status tlc-status--readonly'><strong>Modo solo lectura</strong><br>Puede consultar el estado, pero las acciones estan reservadas a usuarios autorizados.</div>";
		}
		if (trabajosPanel.length <= 1 && tratamientoLaboratorioClinicoAccionPermitida(contexto, "registrarInstalacion")
			&& !tratamientoLaboratorioClinicoEstado.origen.cod_evolucion_origen) {
			html += "<div class='tlc-alert'>Para registrar la instalacion, guarde una nueva evolucion clinica de este tratamiento.</div>";
		}
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && !trabajo && antecedente && antecedente.disponible !== false) {
		html += "<section class='tlc-status tlc-status--historical'><em>Declarado por Administracion</em><strong>" + tratamientoLaboratorioClinicoEscapar(antecedente.etiqueta || "Antecedente historico") + "</strong><span>" + tratamientoLaboratorioClinicoEscapar(antecedente.descripcion || "Antecedente convalidado del modulo historico.") + "</span><small>No representa progreso clinico ni una instalacion clinica confirmada.</small></section>";
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && !trabajo && !antecedente && requiereRegularizacion) {
		html += "<section class='tlc-status tlc-status--historical'><em>Regularizacion administrativa</em><strong>Detalle con unidades agrupadas</strong><span>Administracion debe vincular o convalidar el registro historico que corresponda antes de iniciar un trabajo nuevo.</span><small>La venta, su cantidad y el registro clinico-financiero original se conservan sin sobrescrituras.</small></section>";
		if (!tratamientoLaboratorioClinicoPuedeAbrirHistoricos(contexto)) {
			html += "<div class='tlc-status tlc-status--readonly'><strong>Intervencion de Administracion</strong><br>Solicite a un usuario auditor que regularice este detalle desde los trabajos historicos.</div>";
		}
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && !trabajo && !antecedente && regularizacionGuiada) {
		var unidadesGuiadas = regularizacionGuiada.unidades || [];
		html += "<section class='tlc-status tlc-status--units'><em>Regularizacion guiada</em><strong>Un trabajo por cada unidad</strong><span>Completa las piezas en "
			+ tratamientoLaboratorioClinicoEscapar(regularizacionGuiada.cantidad) + " selecciones separadas. Nada se modifica en la venta original.</span>"
			+ "<div class='tlc-unit-list'>";
		for (var indiceUnidad = 0; indiceUnidad < regularizacionGuiada.cantidad; indiceUnidad++) {
			var unidadGuiada = unidadesGuiadas[indiceUnidad] || null;
			var piezasGuiadas = unidadGuiada && Array.isArray(unidadGuiada.piezas) ? unidadGuiada.piezas : [];
			html += "<div class='tlc-unit'><div><b>Trabajo " + (indiceUnidad + 1) + " de " + regularizacionGuiada.cantidad + "</b><span>"
				+ (piezasGuiadas.length ? "Piezas " + tratamientoLaboratorioClinicoEscapar(piezasGuiadas.join(", ")) : "Ubicacion pendiente")
				+ "</span></div><button type='button' onclick='tratamientoLaboratorioClinicoEditarUnidad(" + (indiceUnidad + 1) + ")'>"
				+ (piezasGuiadas.length ? "Editar" : "Seleccionar") + "</button></div>";
		}
		html += "</div></section>";
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && !trabajo && !antecedente && !regularizacionGuiada && requiereUnidades) {
		html += "<section class='tlc-status tlc-status--units'><em>Regularizacion por unidades</em><strong>Se crearan "
			+ tratamientoLaboratorioClinicoEscapar(detalle.cantidad_unidades_laboratorio || detalle.cantidad || 0)
			+ " trabajos independientes</strong><span>El selector se abrira una vez por cada trabajo para evitar mezclar sus piezas.</span><small>Todos conservaran el mismo codigo de origen y la venta no sera modificada.</small></section>";
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && !trabajo && !antecedente && !regularizacionGuiada && regularizacionUnidades) {
		html += "<section class='tlc-status tlc-status--units'><em>Piezas designadas</em><strong>"
			+ tratamientoLaboratorioClinicoEscapar(regularizacionUnidades.cantidad_unidades || 0)
			+ " trabajos listos para preparar</strong><span class='tlc-origin'>Origen "
			+ tratamientoLaboratorioClinicoEscapar(regularizacionUnidades.codigo_origen || "-")
			+ "</span><div class='tlc-unit-list'>" + (regularizacionUnidades.unidades || []).map(function (unidad) {
				return "<div class='tlc-unit'><div><b>Trabajo " + tratamientoLaboratorioClinicoEscapar(unidad.numero_unidad) + " de "
					+ tratamientoLaboratorioClinicoEscapar(regularizacionUnidades.cantidad_unidades) + "</b><span>"
					+ tratamientoLaboratorioClinicoEscapar(tratamientoLaboratorioClinicoTextoUbicacion(unidad))
					+ "</span></div></div>";
			}).join("") + "</div></section>";
		if (!tratamientoLaboratorioClinicoAccionPermitida(contexto, "iniciarTrabajosAgrupados")) {
			html += "<div class='tlc-status tlc-status--readonly'><strong>Preparacion pendiente</strong><br>Las piezas quedaron guardadas. Un profesional autorizado puede completar tecnico, requisitos y evidencia inicial.</div>";
		}
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && !trabajo && !antecedente && !requiereRegularizacion
		&& !requiereUnidades && !regularizacionUnidades && !regularizacionGuiada && soloLectura) {
		html += "<div class='tlc-status tlc-status--readonly'><strong>Sin trabajo activo</strong><br>El seguimiento esta disponible en modo solo lectura.</div>";
	}
	html += "</div><div class='tlc-actions'><button type='button' class='tlc-btn tlc-btn--secondary' onclick='tratamientoLaboratorioClinicoCerrar()'>Ahora no</button>";
	if (!tratamientoLaboratorioClinicoEstado.cargando && regularizacionGuiada) {
		var primeraUnidadPendiente = 0;
		for (var indicePendiente = 0; indicePendiente < regularizacionGuiada.cantidad; indicePendiente++) {
			if (!regularizacionGuiada.unidades[indicePendiente]) { primeraUnidadPendiente = indicePendiente + 1; break; }
		}
		if (regularizacionGuiada.completa) {
			html += "<button type='button' class='tlc-btn tlc-btn--primary' onclick='tratamientoLaboratorioClinicoConfirmarRegularizacionUnidades()' "
				+ (regularizacionGuiada.guardando ? "disabled" : "") + ">"
				+ (regularizacionGuiada.guardando ? "Guardando..." : "Confirmar y preparar " + regularizacionGuiada.cantidad + " trabajos") + "</button>";
		} else if (primeraUnidadPendiente) {
			html += "<button type='button' class='tlc-btn tlc-btn--primary' onclick='tratamientoLaboratorioClinicoEditarUnidad(" + primeraUnidadPendiente + ")'>Continuar con trabajo "
				+ primeraUnidadPendiente + " de " + regularizacionGuiada.cantidad + "</button>";
		}
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && !regularizacionGuiada && requiereUnidades
		&& tratamientoLaboratorioClinicoPuedeRegularizarUnidades(contexto)) {
		html += "<button type='button' class='tlc-btn tlc-btn--primary' onclick='tratamientoLaboratorioClinicoIniciarRegularizacionUnidades()'>Designar trabajo 1 de "
			+ tratamientoLaboratorioClinicoEscapar(detalle.cantidad_unidades_laboratorio || detalle.cantidad || 0) + "</button>";
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && !regularizacionGuiada && regularizacionUnidades
		&& tratamientoLaboratorioClinicoAccionPermitida(contexto, "iniciarTrabajosAgrupados")) {
		html += "<button type='button' class='tlc-btn tlc-btn--primary' onclick='tratamientoLaboratorioClinicoAbrirPreparacionUnidades()'>Preparar "
			+ tratamientoLaboratorioClinicoEscapar(regularizacionUnidades.cantidad_unidades || 0) + " trabajos</button>";
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && !trabajo && !antecedente
		&& !requiereRegularizacion && faltaUbicacion
		&& tratamientoLaboratorioClinicoPuedeAsignarUbicacion(contexto)) {
		html += "<button type='button' class='tlc-btn tlc-btn--primary' onclick='tratamientoLaboratorioClinicoAsignarUbicacion()'>Asignar ubicacion</button>";
	} else if (!tratamientoLaboratorioClinicoEstado.cargando && !requiereRegularizacion && !bloqueos.length && !trabajo && !antecedente && tratamientoLaboratorioClinicoVerdadero(contexto.puede_iniciar)) {
		html += "<button type='button' class='tlc-btn tlc-btn--primary' onclick='tratamientoLaboratorioClinicoAbrirInicio()'>Preparar trabajo</button>";
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && !requiereRegularizacion && !trabajo && !antecedente && tratamientoLaboratorioClinicoVerdadero(contexto.puede_asegurar_hilo)) {
		html += "<button type='button' class='tlc-btn tlc-btn--primary' onclick='tratamientoLaboratorioClinicoAsegurarHilo()'>Preparar Hilo maestro</button>";
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && requiereRegularizacion
		&& tratamientoLaboratorioClinicoPuedeAbrirHistoricos(contexto)) {
		html += "<button type='button' class='tlc-btn tlc-btn--primary' onclick='tratamientoLaboratorioClinicoAbrirRegularizacion()'>Abrir historicos de esta venta</button>";
	}
	if (!tratamientoLaboratorioClinicoEstado.cargando && trabajo && tratamientoLaboratorioClinicoPuedeAbrir(contexto)) {
		html += "<button type='button' class='tlc-btn tlc-btn--primary' onclick='tratamientoLaboratorioClinicoAbrirTrabajo()'>"
			+ (trabajosPanel.length > 1 ? "Abrir " + trabajosPanel.length + " trabajos" : "Abrir trabajo") + "</button>";
		if (trabajosPanel.length <= 1 && tratamientoLaboratorioClinicoAccionPermitida(contexto, "registrarInstalacion")
			&& tratamientoLaboratorioClinicoEstado.origen.cod_evolucion_origen) {
			html += "<button type='button' class='tlc-btn tlc-btn--install' onclick='tratamientoLaboratorioClinicoAbrirInstalacion()'>Registrar instalacion</button>";
		}
	}
	html += "</div>";
	panel.innerHTML = html;
	panel.hidden = false;
}

function tratamientoLaboratorioClinicoCerrar() {
	var panel = document.getElementById("tratamientoLaboratorioClinicoPanel");
	if (panel) { panel.hidden = true; }
}

function tratamientoLaboratorioClinicoSolicitudContexto(codDetalle) {
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "obtenerContextoDetalle");
	datos.append("cod_detalle_venta", codDetalle);
	return $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmTrabajoLaboratorio.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json"
	});
}

function tratamientoLaboratorioClinicoFusionarRespuesta(base, respuesta) {
	base = base || {};
	var datos = (respuesta && respuesta.datos) || respuesta || {};
	var actualizado = datos.contexto || datos;
	var salida = Object.assign({}, base, actualizado);
	Object.keys(datos).forEach(function(clave) {
		if (clave !== "contexto" && salida[clave] === undefined) { salida[clave] = datos[clave]; }
	});
	salida.detalle = Object.assign({}, tratamientoLaboratorioClinicoDetalle(base), actualizado.detalle || datos.detalle || {});
	return salida;
}

function tratamientoLaboratorioClinicoConsultarContexto(contextoInicial, opciones) {
	contextoInicial = contextoInicial || {};
	opciones = opciones || {};
	var detalle = tratamientoLaboratorioClinicoDetalle(contextoInicial);
	var idDetalle = String(detalle.cod_detalle_venta || "");
	if (!idDetalle) { return null; }
	var solicitudActual = ++tratamientoLaboratorioClinicoEstado.solicitudSecuencia;
	tratamientoLaboratorioClinicoEstado.contexto = contextoInicial;
	tratamientoLaboratorioClinicoEstado.detalleSolicitado = idDetalle;
	tratamientoLaboratorioClinicoEstado.fuente = opciones.fuente || "tarjeta";
	tratamientoLaboratorioClinicoEstado.mostrarPanel = opciones.mostrarPanel !== false;
	tratamientoLaboratorioClinicoEstado.elemento = Object.prototype.hasOwnProperty.call(opciones, "elemento")
		? opciones.elemento : tratamientoLaboratorioClinicoEstado.elemento;
	tratamientoLaboratorioClinicoEstado.cargando = true;
	tratamientoLaboratorioClinicoEstado.error = "";
	tratamientoLaboratorioClinicoRender();
	return tratamientoLaboratorioClinicoSolicitudContexto(idDetalle).done(function(respuesta) {
		if (solicitudActual !== tratamientoLaboratorioClinicoEstado.solicitudSecuencia
			|| idDetalle !== tratamientoLaboratorioClinicoEstado.detalleSolicitado) { return; }
		tratamientoLaboratorioClinicoEstado.cargando = false;
		if (!respuesta || (respuesta.ok !== undefined && !tratamientoLaboratorioClinicoVerdadero(respuesta.ok))) {
			tratamientoLaboratorioClinicoEstado.error = (respuesta && respuesta.mensaje) || "No se pudo revisar el contexto de laboratorio.";
			if (opciones.mostrarPanelError !== false) { tratamientoLaboratorioClinicoEstado.mostrarPanel = true; }
			tratamientoLaboratorioClinicoRender();
			return;
		}
		var actualizado = tratamientoLaboratorioClinicoFusionarRespuesta(contextoInicial, respuesta);
		tratamientoLaboratorioClinicoEstado.contexto = actualizado;
		tratamientoLaboratorioClinicoEstado.error = "";
		tratamientoLaboratorioClinicoCache[idDetalle] = actualizado;
		tratamientoLaboratorioClinicoRender();
		if (typeof opciones.alResolver === "function") {
			try {
				opciones.alResolver(actualizado);
			} catch (error) {
				tratamientoLaboratorioClinicoEstado.error = "No se pudo abrir el siguiente paso de laboratorio.";
				tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
				tratamientoLaboratorioClinicoRender();
			}
		}
	}).fail(function(jqXHR) {
		if (solicitudActual !== tratamientoLaboratorioClinicoEstado.solicitudSecuencia
			|| idDetalle !== tratamientoLaboratorioClinicoEstado.detalleSolicitado) { return; }
		tratamientoLaboratorioClinicoEstado.cargando = false;
		var mensaje = "No se pudo consultar el seguimiento de laboratorio. Puede intentarlo nuevamente.";
		if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.mensaje) { mensaje = jqXHR.responseJSON.mensaje; }
		tratamientoLaboratorioClinicoEstado.error = mensaje;
		if (opciones.mostrarPanelError !== false) { tratamientoLaboratorioClinicoEstado.mostrarPanel = true; }
		tratamientoLaboratorioClinicoRender();
	});
}

function tratamientoLaboratorioClinicoContextoDesdeDatos(datos) {
	datos = datos || {};
	return {
		disponible: true,
		requiere_laboratorio: true,
		requiere_regularizacion_administrativa: tratamientoLaboratorioClinicoVerdadero(datos.requiere_regularizacion_administrativa),
		cantidad_detalle: datos.cantidad || 1,
		cod_detalle_venta: datos.cod_detalle_venta || 0,
		cod_venta: datos.cod_venta || 0,
		producto: datos.nombre_producto || "Tratamiento",
		detalle: {
			cod_detalle_venta: datos.cod_detalle_venta || 0,
			cod_venta: datos.cod_venta || 0,
			cod_producto: datos.cod_producto || "",
			nombre_producto: datos.nombre_producto || "Tratamiento",
			requiere_laboratorio: true,
			requiere_regularizacion_administrativa: tratamientoLaboratorioClinicoVerdadero(datos.requiere_regularizacion_administrativa),
			cantidad: datos.cantidad || 1
		},
		ubicaciones: [],
		bloqueos: []
	};
}

function tratamientoLaboratorioClinicoMostrarResumen(contexto, fuente) {
	tratamientoLaboratorioClinicoEstado.contexto = contexto || tratamientoLaboratorioClinicoEstado.contexto || {};
	tratamientoLaboratorioClinicoEstado.fuente = fuente || tratamientoLaboratorioClinicoEstado.fuente || "tarjeta";
	tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
	tratamientoLaboratorioClinicoRender();
}

function tratamientoLaboratorioClinicoResolverAccionContexto(contexto, fuente) {
	contexto = contexto || {};
	tratamientoLaboratorioClinicoEstado.contexto = contexto;
	var trabajo = tratamientoLaboratorioClinicoTrabajo(contexto);
	var antecedente = tratamientoLaboratorioClinicoAntecedente(contexto);
	var requiereRegularizacion = tratamientoLaboratorioClinicoRequiereRegularizacion(contexto);
	var bloqueos = Array.isArray(contexto.bloqueos) ? contexto.bloqueos : [];
	if (trabajo) {
		if (tratamientoLaboratorioClinicoPuedeAbrir(contexto)) {
			tratamientoLaboratorioClinicoAbrirTrabajo();
			return "abrir_trabajo";
		}
		tratamientoLaboratorioClinicoMostrarResumen(contexto, fuente);
		return "resumen_trabajo";
	}
	if (antecedente && antecedente.disponible !== false) {
		tratamientoLaboratorioClinicoMostrarResumen(contexto, fuente);
		return "resumen_historico";
	}
	if (tratamientoLaboratorioClinicoRequiereUnidades(contexto)) {
		if (tratamientoLaboratorioClinicoPuedeRegularizarUnidades(contexto)) {
			tratamientoLaboratorioClinicoIniciarRegularizacionUnidades();
			return "designar_unidades";
		}
		tratamientoLaboratorioClinicoMostrarResumen(contexto, fuente);
		return "resumen_unidades";
	}
	if (tratamientoLaboratorioClinicoRegularizacionUnidades(contexto)) {
		if (tratamientoLaboratorioClinicoAccionPermitida(contexto, "iniciarTrabajosAgrupados")) {
			tratamientoLaboratorioClinicoAbrirPreparacionUnidades();
			return "preparar_unidades";
		}
		tratamientoLaboratorioClinicoMostrarResumen(contexto, fuente);
		return "resumen_preparacion_unidades";
	}
	if (requiereRegularizacion) {
		tratamientoLaboratorioClinicoMostrarResumen(contexto, fuente);
		return "regularizacion";
	}
	if (tratamientoLaboratorioClinicoUbicacionFalta(contexto)) {
		if (tratamientoLaboratorioClinicoPuedeAsignarUbicacion(contexto)) {
			tratamientoLaboratorioClinicoAsignarUbicacion();
			return "asignar_ubicacion";
		}
		tratamientoLaboratorioClinicoMostrarResumen(contexto, fuente);
		return "resumen_ubicacion";
	}
	if (!bloqueos.length && tratamientoLaboratorioClinicoVerdadero(contexto.puede_iniciar)) {
		tratamientoLaboratorioClinicoAbrirInicio();
		return "iniciar_trabajo";
	}
	tratamientoLaboratorioClinicoMostrarResumen(contexto, fuente);
	return "resumen";
}

function tratamientoLaboratorioClinicoAbrirDesdeTarjeta(elemento) {
	var datos = tratamientoLaboratorioClinicoDatosTarjeta(elemento);
	if (!datos.laboratorio || !datos.cod_detalle_venta) { return; }
	tratamientoLaboratorioClinicoEstado.origen = {};
	tratamientoLaboratorioClinicoConsultarContexto(
		tratamientoLaboratorioClinicoContextoDesdeDatos(datos),
		{
			fuente: "tarjeta",
			mostrarPanel: false,
			elemento: datos.elemento,
			alResolver: function (contexto) {
				tratamientoLaboratorioClinicoResolverAccionContexto(contexto, "tarjeta");
			}
		}
	);
}

function tratamientoLaboratorioClinicoConsultarDesdeModal(ejecutarAccion) {
	var datos = (tratamientoProgresoActualConsulta && tratamientoProgresoActualConsulta.laboratorioDatos) || {};
	if (!datos.cod_detalle_venta) { return; }
	tratamientoLaboratorioClinicoEstado.origen = {};
	tratamientoLaboratorioClinicoConsultarContexto(
		tratamientoLaboratorioClinicoContextoDesdeDatos(datos),
		{
			fuente: "modal",
			mostrarPanel: false,
			elemento: datos.elemento,
			alResolver: ejecutarAccion === true ? function (contexto) {
				tratamientoLaboratorioClinicoResolverAccionContexto(contexto, "modal");
			} : null
		}
	);
}

function tratamientoLaboratorioClinicoAccionDobleSeguimiento() {
	var datos = (tratamientoProgresoActualConsulta && tratamientoProgresoActualConsulta.laboratorioDatos) || {};
	var contexto = tratamientoLaboratorioClinicoCache[String(datos.cod_detalle_venta || "")];
	if (!contexto) {
		tratamientoLaboratorioClinicoConsultarDesdeModal(true);
		return;
	}
	tratamientoLaboratorioClinicoEstado.contexto = contexto;
	tratamientoLaboratorioClinicoEstado.detalleSolicitado = String(datos.cod_detalle_venta || "");
	tratamientoLaboratorioClinicoEstado.elemento = datos.elemento || null;
	tratamientoLaboratorioClinicoResolverAccionContexto(contexto, "modal");
}

function tratamientoLaboratorioClinicoProcesarContexto(contexto) {
	contexto = contexto || {};
	var detalle = tratamientoLaboratorioClinicoDetalle(contexto);
	if (!tratamientoLaboratorioClinicoVerdadero(detalle.requiere_laboratorio)) { return; }
	tratamientoLaboratorioClinicoEstado.origen = {
		cod_consulta_origen: contexto.cod_consulta_origen || null,
		cod_evolucion_origen: contexto.cod_evolucion_origen || null
	};
	tratamientoLaboratorioClinicoConsultarContexto(contexto, {
		fuente: "evolucion",
		mostrarPanel: true,
		elemento: null
	});
}

function tratamientoLaboratorioClinicoOpcionesOrigen() {
	return {
		cod_consulta_origen: tratamientoLaboratorioClinicoEstado.origen.cod_consulta_origen || null,
		cod_evolucion_origen: tratamientoLaboratorioClinicoEstado.origen.cod_evolucion_origen || null
	};
}

function tratamientoLaboratorioClinicoModuloDisponible() {
	return window.TrabajoLaboratorio
		&& typeof window.TrabajoLaboratorio.abrirDesdeDetalleVenta === "function"
		&& typeof window.TrabajoLaboratorio.abrirTrabajo === "function";
}

function tratamientoLaboratorioClinicoAsignarUbicacion() {
	var contexto = tratamientoLaboratorioClinicoEstado.contexto || {};
	if (tratamientoLaboratorioClinicoRequiereRegularizacion(contexto)) {
		tratamientoLaboratorioClinicoEstado.error = "Este detalle historico debe regularizarlo Administracion antes de asignar ubicaciones.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	if (!tratamientoLaboratorioClinicoPuedeAsignarUbicacion(tratamientoLaboratorioClinicoEstado.contexto)) {
		tratamientoLaboratorioClinicoEstado.error = "Necesita acceso a Consulta y al local de esta venta para asignar la ubicacion.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	var datos = tratamientoLaboratorioClinicoEstado.elemento
		? tratamientoLaboratorioClinicoDatosTarjeta(tratamientoLaboratorioClinicoEstado.elemento)
		: ((tratamientoProgresoActualConsulta && tratamientoProgresoActualConsulta.laboratorioDatos) || {});
	var detalle = tratamientoLaboratorioClinicoDetalle(contexto);
	if (!(detalle.cod_detalle_venta || datos.cod_detalle_venta)
		|| (typeof odontogramaAbrirSelectorRapidoLaboratorio !== "function"
			&& typeof odontogramaAsignarTratamientoFicha !== "function")) {
		tratamientoLaboratorioClinicoEstado.error = "No se pudo abrir la asignacion de ubicacion para este tratamiento.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	var idDetalle = detalle.cod_detalle_venta || datos.cod_detalle_venta;
	var origen = tratamientoLaboratorioClinicoOpcionesOrigen();
	tratamientoLaboratorioClinicoCerrar();
	if (typeof cerrarModalEvolucionTratamientoConsulta === "function") {
		cerrarModalEvolucionTratamientoConsulta();
	}
	var abrirSelector = typeof odontogramaAbrirSelectorRapidoLaboratorio === "function"
		? odontogramaAbrirSelectorRapidoLaboratorio : odontogramaAsignarTratamientoFicha;
	abrirSelector(
		idDetalle,
		detalle.cod_venta || datos.cod_venta || "",
		detalle.cod_producto || datos.cod_producto || "",
		detalle.nombre_producto || datos.nombre_producto || "Tratamiento",
		detalle.alcance_odontologico || datos.alcance_odontologico || "",
		detalle.modo_individualizacion || "",
		tratamientoLaboratorioClinicoVerdadero(detalle.requiere_laboratorio) ? 1 : 0,
		{
			abrirPestana: typeof odontogramaAbrirSelectorRapidoLaboratorio !== "function",
			enfocar: true,
			alGuardar: function () {
				setTimeout(function () {
					tratamientoLaboratorioClinicoAbrirPreparacionDetalle(idDetalle, origen);
				}, 0);
			},
			alError: function (mensaje) {
				tratamientoLaboratorioClinicoEstado.error = mensaje || "No se pudo preparar la ubicacion en el odontograma.";
				tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
				tratamientoLaboratorioClinicoRender();
			}
		}
	);
}

function tratamientoLaboratorioClinicoAbrirPreparacionDetalle(codDetalle, opcionesOrigen) {
	if (!tratamientoLaboratorioClinicoModuloDisponible()) {
		tratamientoLaboratorioClinicoEstado.error = "La ubicacion quedo guardada, pero el panel de Trabajos de laboratorio todavia no termino de cargar.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	window.TrabajoLaboratorio.abrirDesdeDetalleVenta(codDetalle, opcionesOrigen || {});
}

function tratamientoLaboratorioClinicoClaveRegularizacion() {
	return "regularizacion-unidades-" + Date.now() + "-" + Math.random().toString(36).slice(2, 12);
}

function tratamientoLaboratorioClinicoIniciarRegularizacionUnidades() {
	var contexto = tratamientoLaboratorioClinicoEstado.contexto || {};
	var detalle = tratamientoLaboratorioClinicoDetalle(contexto);
	var cantidad = parseInt(detalle.cantidad_unidades_laboratorio || detalle.cantidad || 0, 10);
	if (!tratamientoLaboratorioClinicoPuedeRegularizarUnidades(contexto) || cantidad < 2) {
		tratamientoLaboratorioClinicoEstado.error = "No se pudo iniciar la designacion separada de los trabajos.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	tratamientoLaboratorioRegularizacionUnidadesEstado = {
		detalle: detalle,
		contexto: contexto,
		cantidad: cantidad,
		actual: 1,
		unidades: [],
		guardando: false,
		completa: false,
		editando: false,
		clave: tratamientoLaboratorioClinicoClaveRegularizacion()
	};
	tratamientoLaboratorioClinicoCerrar();
	if (typeof cerrarModalEvolucionTratamientoConsulta === "function") {
		cerrarModalEvolucionTratamientoConsulta();
	}
	tratamientoLaboratorioClinicoAbrirSelectorUnidad(1);
}

function tratamientoLaboratorioClinicoAbrirSelectorUnidad(numero) {
	var estado = tratamientoLaboratorioRegularizacionUnidadesEstado;
	if (!estado || typeof odontogramaAbrirSelectorRapidoLaboratorio !== "function") {
		tratamientoLaboratorioClinicoEstado.error = "El selector dental rapido no esta disponible.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	numero = Math.max(1, Math.min(estado.cantidad, parseInt(numero, 10) || 1));
	estado.actual = numero;
	var seleccionAnterior = estado.unidades[numero - 1];
	odontogramaAbrirSelectorRapidoLaboratorio(
		estado.detalle.cod_detalle_venta,
		estado.detalle.cod_venta || "",
		estado.detalle.cod_producto || "",
		estado.detalle.nombre_producto || "Tratamiento",
		estado.detalle.alcance_odontologico || "pieza_dental",
		estado.detalle.modo_individualizacion || "",
		1,
		{
			soloCapturar: true,
			trabajoActual: numero,
			cantidadTrabajos: estado.cantidad,
			seleccionInicial: seleccionAnterior && Array.isArray(seleccionAnterior.piezas)
				? seleccionAnterior.piezas : [],
			alGuardar: function (ubicacion) {
				var vigente = tratamientoLaboratorioRegularizacionUnidadesEstado;
				if (!vigente) { return; }
				vigente.unidades[numero - 1] = {
					numero_unidad: numero,
					pieza: ubicacion.pieza || "",
					piezas: Array.isArray(ubicacion.piezas) ? ubicacion.piezas.slice() : [],
					denticion: ubicacion.denticion || "permanente",
					alcance: ubicacion.alcance || ((ubicacion.piezas || []).length > 1 ? "piezas_multiples" : "pieza_dental")
				};
				vigente.completa = vigente.unidades.filter(Boolean).length === vigente.cantidad;
				if (vigente.editando) {
					vigente.editando = false;
					tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
					tratamientoLaboratorioClinicoEstado.error = "";
					tratamientoLaboratorioClinicoRender();
					return;
				}
				if (numero < vigente.cantidad) {
					setTimeout(function () { tratamientoLaboratorioClinicoAbrirSelectorUnidad(numero + 1); }, 0);
					return;
				}
				tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
				tratamientoLaboratorioClinicoEstado.error = "";
				tratamientoLaboratorioClinicoRender();
			},
			alCancelar: function () {
				tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
				tratamientoLaboratorioClinicoRender();
			}
		}
	);
}

function tratamientoLaboratorioClinicoEditarUnidad(numero) {
	if (!tratamientoLaboratorioRegularizacionUnidadesEstado
		|| tratamientoLaboratorioRegularizacionUnidadesEstado.guardando) { return; }
	tratamientoLaboratorioRegularizacionUnidadesEstado.editando = !!tratamientoLaboratorioRegularizacionUnidadesEstado.unidades[numero - 1];
	tratamientoLaboratorioClinicoCerrar();
	tratamientoLaboratorioClinicoAbrirSelectorUnidad(numero);
}

function tratamientoLaboratorioClinicoSolicitudGuardarUnidades(estado) {
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "guardarRegularizacionUnidades");
	datos.append("cod_detalle_venta", estado.detalle.cod_detalle_venta);
	datos.append("unidades_json", JSON.stringify(estado.unidades));
	datos.append("clave_idempotencia", estado.clave);
	return $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmTrabajoLaboratorio.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json"
	});
}

function tratamientoLaboratorioClinicoConfirmarRegularizacionUnidades() {
	var estado = tratamientoLaboratorioRegularizacionUnidadesEstado;
	if (!estado || estado.guardando || !estado.completa) { return; }
	estado.guardando = true;
	tratamientoLaboratorioClinicoEstado.error = "";
	tratamientoLaboratorioClinicoRender();
	tratamientoLaboratorioClinicoSolicitudGuardarUnidades(estado).done(function (respuesta) {
		if (!respuesta || !tratamientoLaboratorioClinicoVerdadero(respuesta.ok)) {
			estado.guardando = false;
			tratamientoLaboratorioClinicoEstado.error = (respuesta && respuesta.mensaje)
				|| "No se pudieron guardar las ubicaciones de los trabajos.";
			tratamientoLaboratorioClinicoRender();
			return;
		}
		var datos = respuesta.datos || respuesta.data || {};
		var regularizacion = datos.regularizacion || {};
		delete tratamientoLaboratorioClinicoCache[String(estado.detalle.cod_detalle_venta || "")];
		tratamientoLaboratorioRegularizacionUnidadesEstado = null;
		tratamientoLaboratorioClinicoCerrar();
		if (!window.TrabajoLaboratorio || typeof window.TrabajoLaboratorio.abrirRegularizacionUnidades !== "function") {
			tratamientoLaboratorioClinicoEstado.error = "Las ubicaciones quedaron guardadas, pero el panel de preparacion todavia no termino de cargar.";
			tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
			tratamientoLaboratorioClinicoRender();
			return;
		}
		window.TrabajoLaboratorio.abrirRegularizacionUnidades(
			estado.detalle.cod_detalle_venta,
			regularizacion,
			tratamientoLaboratorioClinicoOpcionesOrigen()
		);
	}).fail(function (jqXHR) {
		estado.guardando = false;
		tratamientoLaboratorioClinicoEstado.error = jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.mensaje
			? jqXHR.responseJSON.mensaje : "No se pudo confirmar la regularizacion. Puede volver a intentarlo.";
		tratamientoLaboratorioClinicoRender();
	});
}

function tratamientoLaboratorioClinicoAbrirPreparacionUnidades() {
	var contexto = tratamientoLaboratorioClinicoEstado.contexto || {};
	var detalle = tratamientoLaboratorioClinicoDetalle(contexto);
	var regularizacion = tratamientoLaboratorioClinicoRegularizacionUnidades(contexto);
	if (!regularizacion || !window.TrabajoLaboratorio
		|| typeof window.TrabajoLaboratorio.abrirRegularizacionUnidades !== "function") {
		tratamientoLaboratorioClinicoEstado.error = "No se pudo abrir la preparacion de los trabajos agrupados.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	tratamientoLaboratorioClinicoCerrar();
	window.TrabajoLaboratorio.abrirRegularizacionUnidades(
		detalle.cod_detalle_venta,
		regularizacion,
		tratamientoLaboratorioClinicoOpcionesOrigen()
	);
}

function tratamientoLaboratorioClinicoAbrirInicio() {
	var detalle = tratamientoLaboratorioClinicoDetalle(tratamientoLaboratorioClinicoEstado.contexto);
	if (!tratamientoLaboratorioClinicoModuloDisponible()) {
		tratamientoLaboratorioClinicoEstado.error = "El panel central de Trabajos de laboratorio todavia no termino de cargar.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	tratamientoLaboratorioClinicoCerrar();
	window.TrabajoLaboratorio.abrirDesdeDetalleVenta(detalle.cod_detalle_venta, tratamientoLaboratorioClinicoOpcionesOrigen());
}

function tratamientoLaboratorioClinicoAbrirRegularizacion() {
	var contexto = tratamientoLaboratorioClinicoEstado.contexto || {};
	var detalle = tratamientoLaboratorioClinicoDetalle(contexto);
	if (!tratamientoLaboratorioClinicoPuedeAbrirHistoricos(contexto)) {
		tratamientoLaboratorioClinicoEstado.error = "La regularizacion debe realizarla un usuario auditor desde Trabajos historicos.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	if (!window.TrabajoLaboratorio || typeof window.TrabajoLaboratorio.abrir !== "function") {
		tratamientoLaboratorioClinicoEstado.error = "El modulo de trabajos historicos todavia no termino de cargar.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	tratamientoLaboratorioClinicoCerrar();
	window.TrabajoLaboratorio.abrir({
		vista: "historicos",
		cod_venta_historica: detalle.cod_venta || "",
		busqueda: String(detalle.cod_venta || detalle.nro_venta || "")
	});
}

function tratamientoLaboratorioClinicoAsegurarHilo() {
	var detalle = tratamientoLaboratorioClinicoDetalle(tratamientoLaboratorioClinicoEstado.contexto);
	if (!window.TrabajoLaboratorio || typeof window.TrabajoLaboratorio.asegurarHiloDetalle !== "function") {
		tratamientoLaboratorioClinicoEstado.error = "El proceso para preparar el Hilo maestro todavia no termino de cargar.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	tratamientoLaboratorioClinicoCerrar();
	window.TrabajoLaboratorio.asegurarHiloDetalle(
		detalle.cod_detalle_venta,
		tratamientoLaboratorioClinicoOpcionesOrigen()
	);
}

function tratamientoLaboratorioClinicoAbrirTrabajo() {
	var contexto = tratamientoLaboratorioClinicoEstado.contexto || {};
	var trabajo = tratamientoLaboratorioClinicoTrabajo(contexto);
	var trabajos = tratamientoLaboratorioClinicoTrabajos(contexto);
	if (!tratamientoLaboratorioClinicoModuloDisponible() || !trabajo || !trabajo.id) {
		tratamientoLaboratorioClinicoEstado.error = "No se pudo abrir el trabajo seleccionado.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	tratamientoLaboratorioClinicoCerrar();
	if (trabajos.length > 1) {
		window.TrabajoLaboratorio.abrirDesdeDetalleVenta(
			tratamientoLaboratorioClinicoDetalle(contexto).cod_detalle_venta,
			Object.assign({}, tratamientoLaboratorioClinicoOpcionesOrigen(), { soloListadoDetalle: true })
		);
		return;
	}
	window.TrabajoLaboratorio.abrirTrabajo(trabajo.id);
}

function tratamientoLaboratorioClinicoAbrirInstalacion() {
	var trabajo = tratamientoLaboratorioClinicoTrabajo(tratamientoLaboratorioClinicoEstado.contexto || {});
	var opciones = tratamientoLaboratorioClinicoOpcionesOrigen();
	if (!trabajo || !trabajo.id || !tratamientoLaboratorioClinicoModuloDisponible()) {
		tratamientoLaboratorioClinicoEstado.error = "No se pudo abrir la instalacion clinica.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	if (!opciones.cod_evolucion_origen) {
		tratamientoLaboratorioClinicoEstado.error = "La instalacion debe quedar vinculada a una evolucion clinica nueva.";
		tratamientoLaboratorioClinicoEstado.mostrarPanel = true;
		tratamientoLaboratorioClinicoRender();
		return;
	}
	tratamientoLaboratorioClinicoCerrar();
	if (typeof window.TrabajoLaboratorio.registrarInstalacion === "function") {
		window.TrabajoLaboratorio.registrarInstalacion(trabajo.id, opciones);
		return;
	}
	window.TrabajoLaboratorio.abrirTrabajo(trabajo.id);
	if (typeof ver_vetana_informativa === "function") {
		ver_vetana_informativa("Abra la accion Registrar instalacion dentro de la ficha del trabajo.");
	}
}

function tratamientoLaboratorioClinicoAplicarRespuestaOperacion(respuesta) {
	var datos = (respuesta && (respuesta.data || respuesta.datos)) || respuesta || {};
	var idDetalle = String(datos.cod_detalle_venta || datos.id_detalle_venta
		|| (datos.trabajo && (datos.trabajo.cod_detalle_venta || datos.trabajo.id_detalle_venta)) || "");
	var porcentaje = parseInt(datos.progreso_clinico, 10);
	if (!idDetalle) { return; }
	var actual = 0;
	Array.prototype.forEach.call(document.querySelectorAll("[data-detalle-tratamiento='" + idDetalle + "'],[data-detalle-odontograma='" + idDetalle + "']"), function (item) {
		actual = Math.max(actual, parseInt(item.getAttribute("data-tratamiento-avance"), 10) || 0);
		if (datos.trabajo) { item.setAttribute("data-laboratorio-trabajo-activo", "1"); }
	});
	if (typeof tratamientoProgresoActualConsulta !== "undefined"
		&& String(tratamientoProgresoActualConsulta.id || "") === idDetalle) {
		actual = Math.max(actual, parseInt(tratamientoProgresoActualConsulta.porcentaje, 10) || 0);
	}
	if (!isNaN(porcentaje)) {
		porcentaje = Math.max(actual, Math.max(0, Math.min(100, porcentaje)));
		var estadoTexto = porcentaje >= 100 ? "Completado" : (porcentaje > 0 ? "En proceso" : "Pendiente");
		var estadoClase = porcentaje >= 100 ? "completado" : (porcentaje > 0 ? "proceso" : "pendiente");
		actualizarTarjetaTratamientoConsulta(idDetalle, porcentaje, estadoTexto, estadoClase);
		if (typeof tratamientoProgresoActualConsulta !== "undefined"
			&& String(tratamientoProgresoActualConsulta.id || "") === idDetalle) {
			tratamientoProgresoActualConsulta.porcentaje = porcentaje;
			tratamientoProgresoActualConsulta.estado = estadoTexto;
			if (tratamientoProgresoActualConsulta.laboratorioDatos) {
				tratamientoProgresoActualConsulta.laboratorioDatos.porcentaje = porcentaje;
			}
			var actualEl = document.getElementById("lblProgresoActualTratamientoConsulta");
			if (actualEl) { actualEl.textContent = porcentaje + "%"; }
			var estadoEl = document.getElementById("lblEstadoTratamientoConsulta");
			if (estadoEl) { estadoEl.textContent = estadoTexto; }
			mostrarValorSlider(porcentaje);
			tratamientoLaboratorioClinicoRenderDobleSeguimiento();
		}
	}
	delete tratamientoLaboratorioClinicoCache[idDetalle];
	delete tratamientoLaboratorioClinicoMicrohiloSolicitudes[idDetalle];
	if (datos.trabajo && datos.trabajo.id) {
		delete tratamientoLaboratorioClinicoMicrohiloDetalleCache[String(datos.trabajo.id)];
	}
	if (datos.trabajo) {
		var cantidadTrabajos = Array.isArray(datos.trabajos) ? datos.trabajos.length : (parseInt(datos.cantidad_trabajos, 10) || 1);
		var primeraTarjetaMicrohilo = null;
		Array.prototype.forEach.call(document.querySelectorAll("[data-tratamiento-laboratorio='1'][data-detalle-tratamiento='" + idDetalle + "']"), function (tarjeta) {
			var etiqueta = tarjeta.querySelector("[data-tratamiento-laboratorio-accion-texto]");
			var resumen = tarjeta.querySelector("[data-tratamiento-laboratorio-resumen]");
			if (etiqueta) { etiqueta.textContent = cantidadTrabajos > 1 ? "Abrir " + cantidadTrabajos + " trabajos de laboratorio" : "Abrir trabajo de laboratorio"; }
			if (resumen) {
				resumen.textContent = cantidadTrabajos > 1
					? "Mismo origen \u00b7 Seguimientos independientes"
					: tratamientoLaboratorioClinicoEstadoNombre(datos.trabajo);
				resumen.hidden = false;
			}
			var slot = tratamientoLaboratorioClinicoPrepararSlotMicrohilo(tarjeta);
			if (slot) {
				slot.innerHTML = "<section class='consulta-laboratorio-microhilo is-loading' data-laboratorio-mini-hilo "
					+ "data-laboratorio-mini-hilo-estado='cargando' aria-busy='true'>"
					+ "<span class='consulta-laboratorio-microhilo__loader' aria-hidden='true'></span>"
					+ "<span>Actualizando hilo del trabajo...</span></section>";
				primeraTarjetaMicrohilo = primeraTarjetaMicrohilo || tarjeta;
			}
		});
		if (primeraTarjetaMicrohilo) {
			tratamientoLaboratorioClinicoCargarMicrohiloTarjeta(primeraTarjetaMicrohilo, true);
		}
	}
}
