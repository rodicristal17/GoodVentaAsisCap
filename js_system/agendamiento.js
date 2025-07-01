



/*
ABM AGENDAMIENTO
*/

let idPacienteFkAgendamiento = ""
let idAbmAgendamiento = ""

function verCerrarAbmAgendamiento(){
if(document.getElementById("divAbmAgendamiento").style.display==""){ 
	$("div[id=divAbmAgendamiento]").fadeOut(500);	
	
}else{
	LimpiarCamposAgendamiento()
	buscardatosdeAgendamientoBuscador();
	document.getElementById("divAbmAgendamiento").style.display="" 
}
}

 
function buscardatosParaCobranza(){
	

			obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"cod_Agen": idAbmAgendamiento,
			"funt": "buscardatosParaCobranza"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/ABMAgendamiento.php",
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
					document.getElementById('inptFechaCobranza').value=f.getFullYear()+"-"+mes+"-"+dia;

				cod_AgendamientoCobranzaFK = idAbmAgendamiento
				
				document.getElementById("inptPacienteCobranza").value=datos[4];
				document.getElementById("inptEspecialistaCobranza").value=datos[6];
				document.getElementById("inptTotalCobranza").value=datos[7];
				document.getElementById("inptMontoPagoCobranza").value=datos[7];
				document.getElementById("inptSeguroCobranza").value=datos[8];
				
				document.getElementById("divAgendamiento_Cobranza").innerHTML=""	
				
				
				
				document.getElementById("inptTipoPagoCobranza").value="3";
				
				buscardatosdePagoConsulta(idAbmAgendamiento)
	
				verCerrarAbmCobranzaConsulta()
				verCerrarOpcionesAgendamiento()
			
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}




function BuscarAgendamientoDatalis() {
 
	document.getElementById("ListConsultaAgendamiento").innerHTML = paginacargando
 
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,		
		 "cod_local": cod_localFKUSer, 
		"funt": "buscarDatalisAgendamiento"
	};
	$.ajax({

		data: datos,
        url: "/GoodVentaAsisCap/php_system/ABMAgendamiento.php",
		type: "post",
		beforeSend: function () {
 
		},
		error: function (jqXHR, textstatus, errorThrowm) {

			document.getElementById("ListConsultaAgendamiento").innerHTML = ''
		
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("ListConsultaAgendamiento").innerHTML = ''
			
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
				if (Respuesta == "UI") {

					ir_a_login()
					ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
					return false;
 }
				
				if (Respuesta == "exito") {
					var datos_buscados = datos[2];
					document.getElementById("ListConsultaAgendamiento").innerHTML = datos_buscados
                }
			} catch (error) {

			}
		}
	});


}


function NuevoAgendamiento(){
	LimpiarCamposAgendamiento()	 
	document.getElementById("inptPacienteAgendamiento").focus()
}
function LimpiarCamposAgendamiento(){

	document.getElementById("inptPacienteAgendamiento").value ="";
	document.getElementById("inptConsultaAgendamiento").value ="";
	document.getElementById("inptObservacionAgendamiento").value ="";
	 
	idPacienteFkAgendamiento="";
	idAbmAgendamiento="";
	document.getElementById("btnAbmAgendamiento").value="Guardar Datos"
	$("input[id=btnAbmAgendamiento]").css({"background-color":"#c8cdea"}) 
		 
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
	 	document.getElementById('inptFechaRecepcionAgendamiento').value=f.getFullYear()+"-"+mes+"-"+dia;
	 	document.getElementById('inptFechaEntregaAgendamiento').value=f.getFullYear()+"-"+mes+"-"+dia;

	
}
 

function buscardatosdeAgendamientoBuscador(){
	
	var paciente = document.getElementById("inptBuscarAgendamiento1").value
	var medico = document.getElementById("inptBuscarAgendamiento2").value
	var fecha = document.getElementById("inptBuscarAgendamiento3").value
	
		 document.getElementById("divTable_Agendamiento").innerHTML=paginacargando
			obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"paciente": paciente,
			"medico": medico,
			"fecha": fecha,
			"funt": "buscardatosdeAgendamientoBuscador"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/ABMAgendamiento.php",
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
			document.getElementById("divTable_Agendamiento").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("divTable_Agendamiento").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
			if (Respuesta == "exito") {
				
		   var datos_buscados=datos[2];		 
			document.getElementById("divTable_Agendamiento").innerHTML=datos_buscados	
			
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}

var cod_agendamientoFK="";
function obtenerdatosAgendamiento(datostr){
	 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });

    datostr.className='tableRegistroSelec'
	cod_agendamientoFK=$(datostr).children('td[id="td_id_1"]').html();
	
	verCerrarOpcionesAgendamiento()

}


function verCerrarOpcionesAgendamiento(){
if(document.getElementById("divOpcionesAgendamiento").style.display==""){
	
	document.getElementById("tdEfectoOpcionesAgendamiento").className="magictime vanishOut"
	$("div[id=divOpcionesAgendamiento]").fadeOut(500);	
	
}else{
	
	document.getElementById("divOpcionesAgendamiento").style.display=""
    document.getElementById("tdEfectoOpcionesAgendamiento").className="magictime slideDownReturn"
}
}


function ControlPacienteAgendamiento(inp){
	
	if(inp.value==""){
		 document.getElementById("btnAgendamiento1").style.display="";
		 document.getElementById("btnAgendamiento2").style.display="none";
	}else{
		 document.getElementById("btnAgendamiento1").style.display="none";
		 document.getElementById("btnAgendamiento2").style.display="";
	}
}




function VerificarAbmAgendamiento(){ 
	var inptPacienteAgendamiento = document.getElementById("inptPacienteAgendamiento").value  
	var inptConsultaAgendamiento = document.getElementById("inptConsultaAgendamiento").value
	var inptFechaRecepcionAgendamiento = document.getElementById("inptFechaRecepcionAgendamiento").value
	var inptFechaEntregaAgendamiento = document.getElementById("inptFechaEntregaAgendamiento").value
	var inptObservacionAgendamiento = document.getElementById("inptObservacionAgendamiento").value 
	
	
	if(inptFechaEntregaAgendamiento==""){ 
		document.getElementById("inptFechaEntregaAgendamiento").focus();		
		ver_vetana_informativa("Falto ingresar la fecha de Consulta")
		return false		
	}
	
	
	
	var idConsultaFK="";
	if(idConsultaFK==""){			
		$("input[id=inptConsultaAgendamiento]").each(function (i, Elemento) {
      var $input = $(this),
          val = $input.val();
		 
          list = $input.attr('list'),
          match = $('#'+list + ' option').filter(function() {
              return ($(this).val() === val);			 
          });

       if(match.length > 0) {
         idConsultaFK=$(match).attr("id")
       } else {
           // value is not in list
       }
});
	}
	

	if(idPacienteFkAgendamiento==""){
			
		$("input[id=inptPacienteAgendamiento]").each(function (i, Elemento) {
      var $input = $(this),
          val = $input.val();
		 
          list = $input.attr('list'),
          match = $('#'+list + ' option').filter(function() {
              return ($(this).val() === val);			 
          });

       if(match.length > 0) {
         idPacienteFkAgendamiento=$(match).attr("id")
       } else {
           // value is not in list
       }
});
	}
 
	if(inptFechaRecepcionAgendamiento==""){
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
	 	document.getElementById('inptFechaRecepcionAgendamiento').value=f.getFullYear()+"-"+mes+"-"+dia;
	 	inptFechaRecepcionAgendamiento=document.getElementById('inptFechaRecepcionAgendamiento').value;
		
	}
	if(idPacienteFkAgendamiento==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN PACIENTE")
		return false
	}
	if(idConsultaFK==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN MEDIDO")
		return false
	}
 
	var accion = "";
	if (idAbmAgendamiento != "") {
		accion = "editar";
		// if(controlacceso("ORDENTRABAJO","editar")==false){ return;}
		
	} else {
		accion = "nuevo";
		// if(controlacceso("ORDENTRABAJO","editar")==false){  return;}
		
	}
	AbmAgendamiento(idConsultaFK,idPacienteFkAgendamiento,inptFechaRecepcionAgendamiento,inptFechaEntregaAgendamiento,inptObservacionAgendamiento,idAbmAgendamiento, accion)
}


function AbmAgendamiento(MedicoFK,idPaciente,FechaRecepcion,FechaConsulta,obs,idAbm , accion) {


		document.getElementById("divTable_Agendamiento").innerHTML = ""
     // verCerrarVentanaCargando("1")
	 obtener_datos_user();
   var datos = new FormData();  
	
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	
	datos.append("MedicoFK", MedicoFK)
	datos.append("idPaciente", idPaciente)
	datos.append("FechaRecepcion", FechaRecepcion)
	datos.append("FechaConsulta", FechaConsulta)
	datos.append("obs", obs) 
	datos.append("idabm", idAbm)

	var OpAjax = $.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/ABMAgendamiento.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus, errorThrowm) {
			// verCerrarVentanaCargando("")
			ver_vetana_informativa("ERROR DE CONEXION")

			return false;
		},
		success: function (responseText) {
			// verCerrarVentanaCargando("")
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];

                if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
				if (Respuesta == "UI") {

					ir_a_login()
					ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
					return false;
				}
				
				if (Respuesta == "exito") {
					
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					buscardatosdeAgendamientoBuscador()							

				}
				else {
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				}

			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
			}


		}
	});


}




function VerificarAbmCambiarEstadoDesdeEliminar(estado){

	if (cod_agendamientoFK == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		return false;
	} 
	AbmCambiarEstado(cod_agendamientoFK,estado)
	verCerrarOpcionesAgendamiento()
}




function AbmCambiarEstado(cod_agendamientoFK ,estado) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "EliminarAgendamiento")
	datos.append("cod_agen", cod_agendamientoFK)
	datos.append("estado", estado)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/ABMAgendamiento.php",
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmAgendamiento = ""
					buscardatosdeAgendamientoBuscador();
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}





/*
ABM HISTORIAL AGENDAMIENTO
*/


function verCerrarHistorialAgendamiento(){

	if(document.getElementById("divHistorialAgendamiento").style.display==""){
	
	if(controldebusquedadHistorialAgendamiento==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
document.getElementById("tdEfectoHistorialAgendamiento").className="magictime vanishOut"
	$("div[id=divHistorialAgendamiento]").fadeOut(500);
		document.getElementById("divMinimizadoHistorialAgendamiento").style.display='none'

	}else{
       // if(controlacceso("VERHISTORIALVENTA","accion")==false){ return;}
		document.getElementById("divHistorialAgendamiento").style.display=""
		document.getElementById("tdEfectoHistorialAgendamiento").className="magictime slideDownReturn"
			
	}
}

var registrocargadohistorialAgendamiento="";
var totalregistrohistorialAgendamiento="";
var controldebusquedadHistorialAgendamiento=false
function cancelarHistorialAgendamiento(){
	controldebusquedadHistorialAgendamiento=false
	document.getElementById("divProgressHistorialAgendamiento").style.backgroundColor='#ff5722'
}

function minimizarHistorialAgendamiento(){
	//document.getElementById("divHistorialVenta").style.display='none'
document.getElementById("tdEfectoHistorialAgendamiento").className="magictime slideDown"
	$("div[id=divHistorialAgendamiento]").fadeOut(500);	
	document.getElementById("divMinimizadoHistorialAgendamiento").style.display=''
}

function checkfiltroshistorialAgendamiento(d){
	if(d=="1"){
	document.getElementById('inptCheckHistorialAgendamiento1').checked=true
	document.getElementById('inptCheckHistorialAgendamiento2').checked=false	
     
	 	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarInfHistorialAgendamientoF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarInfHistorialAgendamientoF2').value = f.getFullYear() + "-" + mes + "-" + dia;
	 
	}else{		
	document.getElementById('inptCheckHistorialAgendamiento1').checked=false
	document.getElementById('inptCheckHistorialAgendamiento2').checked=true
	document.getElementById('inptBuscarInfHistorialAgendamientoF1').value="";
      document.getElementById('inptBuscarInfHistorialAgendamientoF2').value="";
	
	}
}


function buscarhistorialAgendamiento() {    
	
	var fechafiltro = document.getElementById("inptBuscarHistorialAgendamiento1").value
	var documento = document.getElementById('inptBuscarHistorialAgendamiento2').value
	var paciente = document.getElementById('inptBuscarHistorialAgendamiento3').value
	var especialista = document.getElementById('inptBuscarHistorialAgendamiento4').value
	var estado = document.getElementById('inptBuscarHistorialAgendamiento5').value
	var usuario = document.getElementById('inptBuscarHistorialAgendamiento6').value
	var seguro = document.getElementById('inptBuscarHistorialAgendamiento7').value
	var fecha1 = document.getElementById('inptBuscarInfHistorialAgendamientoF1').value
	var fecha2 = document.getElementById('inptBuscarInfHistorialAgendamientoF2').value
	
	
	if(document.getElementById('inptCheckHistorialAgendamiento1').checked==true){
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
			return
		}
	}
	if(document.getElementById('inptCheckHistorialAgendamiento2').checked==true){
		var fecha1 = ""
		var fecha2 = ""
	}	
	
	if(controldebusquedadHistorialAgendamiento==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadHistorialAgendamiento=true

	document.getElementById("tbProcessHistorialAgendamiento").style.display="none"
	document.getElementById("table_historial_Agendamiento").innerHTML = paginacargando
	document.getElementById("inptRegistroNroHistorialAgendamiento").value = "";
    document.getElementById("inptTotalHistorialAgendamiento").value = "";
    document.getElementById("inptTotalPagosHistorialAgendamiento").value = "";
    document.getElementById("inptTotalPendienteHistorialAgendamiento").value = "";
	document.getElementById("inptTotalComisionHistorialAgendamiento").value = "";
	document.getElementById("inptTotalSeguroHistorialAgendamiento").value = "";
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
		"estado": estado,
		"seguro": seguro,
		"usuario": usuario,
		"funt": "historialAgendamiento"
	};
	$.ajax({

		data: datos,
		url: "/GoodClinicaNC/php_system/ABMAgendamiento.php",
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
			document.getElementById("table_historial_Agendamiento").innerHTML = ''
			controldebusquedadHistorialAgendamiento=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_Agendamiento").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];              
			  Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("inptRegistroNroHistorialAgendamiento").value = datos[3];
					document.getElementById("inptTotalHistorialAgendamiento").value = datos[4];
					document.getElementById("inptTotalPagosHistorialAgendamiento").value = datos[5];
					document.getElementById("inptTotalPendienteHistorialAgendamiento").value = datos[6];
					document.getElementById("inptTotalComisionHistorialAgendamiento").value = datos[7];
					document.getElementById("inptTotalSeguroHistorialAgendamiento").value = datos[8];
					document.getElementById("table_historial_Agendamiento").innerHTML = datos_buscados
					registrocargadohistorialAgendamiento=datos[99];
					totalregistrohistorialAgendamiento=datos[100];					
						 if(totalregistrohistorialAgendamiento>registrocargadohistorialAgendamiento){
						 	var porce=((registrocargadohistorialAgendamiento*100)/totalregistrohistorialAgendamiento).toFixed(0)
							document.getElementById("divProgressHistorialAgendamiento").style.width=porce+"%"
						 document.getElementById("table_historial_Agendamiento").innerHTML += "<div id='table_mas_historial_Agendamiento'></div>"
						  buscarMashistorialAgendamiento();
					 }else{
						 controldebusquedadHistorialAgendamiento=false
					 }
					
					}
			} catch (error) {
				controldebusquedadHistorialAgendamiento=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarMashistorialAgendamiento() {    
	var fechafiltro = document.getElementById("inptBuscarHistorialAgendamiento1").value
	var documento = document.getElementById('inptBuscarHistorialAgendamiento2').value
	var paciente = document.getElementById('inptBuscarHistorialAgendamiento3').value
	var especialista = document.getElementById('inptBuscarHistorialAgendamiento4').value
	var estado = document.getElementById('inptBuscarHistorialAgendamiento5').value
	var usuario = document.getElementById('inptBuscarHistorialAgendamiento6').value 
	var seguro = document.getElementById('inptBuscarHistorialAgendamiento7').value
	var fecha1 = document.getElementById('inptBuscarInfHistorialAgendamientoF1').value
	var fecha2 = document.getElementById('inptBuscarInfHistorialAgendamientoF2').value
	
	
	if(document.getElementById('inptCheckHistorialAgendamiento1').checked==true){
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
			return
		}
	}
	if(document.getElementById('inptCheckHistorialAgendamiento2').checked==true){
		var fecha1 = ""
		var fecha2 = ""
	}	
	
	
	if(controldebusquedadHistorialAgendamiento==false){
			return
	}
		controldebusquedadHistorialAgendamiento=true
document.getElementById("tbProcessHistorialAgendamiento").style.display=""
document.getElementById("divProgressHistorialAgendamiento").style.backgroundColor=''
	document.getElementById("table_mas_historial_Agendamiento").innerHTML = paginacargando
    var totalAgendamiento=document.getElementById("inptTotalHistorialAgendamiento").value;
    var totalpagado=document.getElementById("inptTotalPagosHistorialAgendamiento").value;
    var totalpendiente=document.getElementById("inptTotalPendienteHistorialAgendamiento").value;
	var totalComision=document.getElementById("inptTotalComisionHistorialAgendamiento").value;
	var totalSeguro=document.getElementById("inptTotalPagosHistorialAgendamiento").value;
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
		"estado": estado,
		"seguro": seguro,
		"usuario": usuario,
		"totalAgendamiento": totalAgendamiento,
		"totalpagado": totalpagado,
		"totalpendiente": totalpendiente,
		"totalComision": totalComision,
		"totalSeguro": totalSeguro,
		"registrocargado": registrocargadohistorialAgendamiento,		
		"funt": "mashistorialAgendamiento"
	};
	$.ajax({

		data: datos,
		url: "/GoodClinicaNC/php_system/ABMAgendamiento.php",
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
			document.getElementById("table_mas_historial_Agendamiento").innerHTML = ''
			document.getElementById("divProgressHistorialAgendamiento").style.backgroundColor='#ff5722'
			controldebusquedadHistorialAgendamiento=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_mas_historial_Agendamiento").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];              
			  Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("inptRegistroNroHistorialAgendamiento").value = datos[3];
					document.getElementById("inptTotalHistorialAgendamiento").value = datos[4];
					document.getElementById("inptTotalPagosHistorialAgendamiento").value = datos[5];
					document.getElementById("inptTotalPendienteHistorialAgendamiento").value = datos[6];
					document.getElementById("inptTotalComisionHistorialAgendamiento").value = datos[6];
					document.getElementById("table_mas_historial_Agendamiento").innerHTML = datos_buscados
						registrocargadohistorialAgendamiento=datos[99];
					
						 if(totalregistrohistorialAgendamiento>registrocargadohistorialAgendamiento){
						 	var porce=((registrocargadohistorialAgendamiento*100)/totalregistrohistorialAgendamiento).toFixed(0)
							document.getElementById("divProgressHistorialAgendamiento").style.width=porce+"%"
				 document.getElementById("table_mas_historial_Agendamiento").innerHTML += "<div id='table_mas_historial_Agendamiento'></div>"
						 document.getElementById("table_mas_historial_Agendamiento").id=""
						  buscarMashistorialAgendamiento();
					 }else{
						 document.getElementById("tbProcessHistorialAgendamiento").style.display="none"
						 controldebusquedadHistorialAgendamiento=false
					 }
					
					}
			} catch (error) {
					document.getElementById("divProgressHistorialAgendamiento").style.backgroundColor='#ff5722'
					controldebusquedadHistorialAgendamiento=false
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
}




function buscarobtenermedicos(){
	 
	
		 document.getElementById("ListConsultaAgendamiento").innerHTML=""
		 document.getElementById("ListEspecialistaVistaConsulta").innerHTML=""
		 document.getElementById("inptEspecialistaConsulta").innerHTML=""
		 
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
			document.getElementById("ListEspecialistaVistaConsulta").innerHTML=datos_buscados	
			document.getElementById("inptEspecialistaConsulta").innerHTML="<option value='' >SELECCIONAR</option>"+datos_buscados	
			
 
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}




function buscarobtenerPacientes(){
	 
		 document.getElementById("ListPacientesAgendamiento").innerHTML=""
		obtener_datos_user();
		 var datos = {
		 "useru":userid,
		 "passu":passuser,
		 "navegador": navegador, 
		"funt": "obtenerPacientes"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/ABMAgendamiento.php",
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
			document.getElementById("ListPacientesAgendamiento").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
	
     console.log(Respuesta)
			  document.getElementById("ListPacientesAgendamiento").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
			if (Respuesta == "exito") {
				
		   var datos_buscados=datos[2];		 
			document.getElementById("ListPacientesAgendamiento").innerHTML=datos_buscados	
			
			
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}

