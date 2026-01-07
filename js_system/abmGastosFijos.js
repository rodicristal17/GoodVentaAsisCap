var cod_abmGastoFijo= "";
function verCerrarVentanaGastosFijos(mostrar) {
	if (mostrar) {
		buscarGastosFijos();
		document.getElementById('divAbmGastosFijos').style.display= "";
	} else {
		document.getElementById('divAbmGastosFijos').style.display= "none";
	}
}

function buscarGastosFijos() {
	if(controlacceso("BUSCARLISTADOGASTOSFIJOS","accion")==false){return;}	
    
	const buscarDercripcion= document.getElementById("inptBuscarAbmGastosFijos").value;
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"descripcion": buscarDercripcion,
		"accion": "buscarVista"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmGastosFijos.php",
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
			document.getElementById("divBuscadorGastosFijos").innerHTML = '';
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorGastosFijos").innerHTML = paginacargando;
			
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "UI") {
					ir_a_login()
					ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
					return false;
				}
				if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
				if (Respuesta == "exito") {
					document.getElementById("divBuscadorGastosFijos").innerHTML = datos[2];
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function obtenerDatosGastosFijos(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'

	cod_abmGastoFijo= $(datostr).children('td[id="td_id"]').html();

	document.getElementById("inptAbmDescripcionGastoFijo").value= $(datostr).children('td[id="td_datos_1"]').html();
    document.getElementById("inptAbmEstadoGastoFijo").value= $(datostr).children('td[id="td_datos_3"]').html().toLowerCase();
    document.getElementById("inptAbmDiaGastoFijo").value= $(datostr).children('td[id="td_datos_2"]').html();
    document.getElementById("inptAbmLocalGastoFijo").value= $(datostr).children('td[id="td_datos_5"]').html();
}

function limpiarCamposGastosFijos() {
	cod_abmGastoFijo= "";
	document.getElementById("inptAbmDescripcionGastoFijo").value= "";
    document.getElementById("inptAbmEstadoGastoFijo").value= "activo";
    document.getElementById("inptAbmDiaGastoFijo").value= "";
    document.getElementById("inptAbmLocalGastoFijo").value= "";
}

function verificarCamposGastosFijos() {
    const descripcion= document.getElementById("inptAbmDescripcionGastoFijo").value;
    const estado= document.getElementById("inptAbmEstadoGastoFijo").value;
    const dia= document.getElementById("inptAbmDiaGastoFijo").value;
    const cod_localFK= document.getElementById("inptAbmLocalGastoFijo").value;

    if (!descripcion) {
        ver_vetana_informativa("FALTO AGREGAR LA DESCRIPCION", "#");
        return false;
    }

    if (!dia) {
        ver_vetana_informativa("FALTO INDICAR EL DIA", "#");
        return false;
    }

	if (!cod_localFK) {
		ver_vetana_informativa("FALTO SELECCIONAR EL LOCAL", "#");
		return false;
	}

    abmGastosFijos(descripcion, estado, dia, cod_localFK);
}

function abmGastosFijos(descripcion, estado, dia, cod_localFK) {
    verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "nuevo/editar");
	datos.append("descripcion", descripcion);
	datos.append("estado", estado);
	datos.append("dia", dia);
    datos.append("cod_localFK", cod_localFK);
	datos.append("cod_interConsultaFK", cod_interConsulta);
    if (cod_abmGastoFijo != "") {
        datos.append("cod_gastos_fijos", idAbmMotivoEgresoIngreso)
    }

	var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmGastosFijos.php",
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")

					buscarGastosFijos()
					limpiarCamposGastosFijos()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}