 
/*
ABM ANTECEDENTE CONSULTA
*/
function verCerrarCargarAntecedenteCliente(){

	if(document.getElementById("divCargarAntecedenteCliente").style.display==""){
	$("div[id=divCargarAntecedenteCliente]").fadeOut(500);
limpiarcamposAntecedenteCliente()
document.getElementById('divTable_AntecedentePaciente').innerHTML = ''	
	}else{		
	// if(controlacceso("VERLISTADOCOBRADORES","accion")==false){return;}
		document.getElementById("divCargarAntecedenteCliente").style.display=""
			buscarAbmAntecedenteConsulta()
		
	}
}

function limpiarcamposAntecedenteCliente(){
	document.getElementById('inptObservacionAntecedenteCliente').value ='';
}

function verificarcamposCargarAntecedenteCliente() {

	let observacion = document.getElementById('inptObservacionAntecedenteCliente').value;
	
	if(observacion ==''){
		ver_vetana_informativa('FALTÓ INGRESAR UNA OBSERVACIÓN');
		return;
	}
	
	abmAntecedentePaciente(observacion);
}

function abmAntecedentePaciente(observacion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", 'cargar_antecedente_paciente')
	datos.append("cod_ventaFK", cod_ventaFKConsulta)
	datos.append("cod_clienteFK", cod_clienteConsulta)
	datos.append("observacion", observacion)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmclientes.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		
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
					ver_vetana_informativa("CARGADO CORRECTAMENTE");
					limpiarcamposAntecedenteCliente()
					buscarAbmAntecedenteConsulta()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarAbmAntecedenteConsulta(){
	document.getElementById("divTable_AntecedentePaciente").innerHTML = '';
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_clienteFK": cod_clienteConsulta,
		"funt": "buscar_antecedente_consulta"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmclientes.php",
		type: "post",

		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
          manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
		  document.getElementById("divTable_AntecedentePaciente").innerHTML = '';
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					document.getElementById("divTable_AntecedentePaciente").innerHTML = datos[2];	 
				}
			} catch (error) {
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
}


function buscarResumenAntecedenteConsulta(){
	document.getElementById("resumen_antencedente_paciente").innerHTML = '';
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_clienteFK": cod_clienteConsulta,
		"funt": "buscar_antecedente_resumen_consulta"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmclientes.php",
		type: "post",

		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
          manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
		  document.getElementById("resumen_antencedente_paciente").innerHTML = '';
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					document.getElementById("resumen_antencedente_paciente").innerHTML = datos[2];	 
				}
			} catch (error) {
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
}
