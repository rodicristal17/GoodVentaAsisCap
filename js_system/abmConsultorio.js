
/*
ABM Consultorio
*/
function verCerrarAbmConsultorio(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmConsultorio").style.display==""){
		document.getElementById("divMinimizadoListadoDeConsultorios").style.display="none"
	limpiarCamposBuscarConsultorio()
		limpiarcamposConsultorio() 
	$("div[id=divAbmConsultorio]").fadeOut(500);	
	
	}else{			
		if(controlacceso("VERFORMULARIOCONSULTORIO","accion")==false){return;}
		
		document.getElementById("inptColorConsultorioHex").value =document.getElementById("inptColorConsultorio").value;
		document.getElementById("divAbmConsultorio").style.display="" 
	
	}
}
function limpiarCamposBuscarConsultorio(){
	document.getElementById("inptBuscarAbmConsultorio1").value=""
	document.getElementById("inptBuscarAbmConsultorio2").value=""
	document.getElementById("inptBuscarAbmConsultorio3").value=""
	document.getElementById("inptBuscarAbmConsultorio4").value=""
	document.getElementById("inptBuscarAbmConsultorio5").value="Activo"
	document.getElementById("inptTotalRegistoConsultorio").value=""
	document.getElementById("inptRegistroSeleccConsultorio").value=""
	document.getElementById("table_abm_Consultorio").innerHTML=""
	document.getElementById("overlayFiltrosConsultorio").style.display="none"
}
function verCerrarFiltrosConsultorio(mostrar){
	if (mostrar) {
		document.getElementById("overlayFiltrosConsultorio").style.display = "";
	} else {
		document.getElementById("overlayFiltrosConsultorio").style.display = "none";
	}
}
function limpiarFiltroConsultorio(){
	document.getElementById("inptBuscarAbmConsultorio1").value="";
	document.getElementById("inptBuscarAbmConsultorio2").value="";
	document.getElementById("inptBuscarAbmConsultorio3").value="";
	document.getElementById("inptBuscarAbmConsultorio4").value="";
	document.getElementById("inptBuscarAbmConsultorio5").value="Activo";
	buscarabmConsultorio();
}
function minimizarConsultorio(){
		document.getElementById("divMinimizadoListadoDeConsultorios").style.display="" 
	$("div[id=divAbmConsultorio]").fadeOut(500);	
}
function verCerrarVentanaAbmConsultorio(d, l) {
	if (d == "1") {
		if (l == "1") {
			if(controlacceso("INSERTARFORMULARIOCONSULTORIO","accion")==false){return;}
			limpiarcamposConsultorio()
		}
		$("div[id=divAbmConsultorio2]").fadeIn(250)
		document.getElementById('divAbmConsultorio1').style.display = "none"
	} else {
		$("div[id=divAbmConsultorio1]").fadeIn(250)
			document.getElementById('divAbmConsultorio2').style.display = "none"
	}
}
function verVentanaEditarConsultorio() {
	if(controlacceso("EDITARFORMULARIOCONSULTORIO","accion")==false){return;}
	if (idAbmConsultorio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		return;
	}
	verCerrarVentanaAbmConsultorio("1", "2")
}
var idAbmConsultorio = ""
function obtenerdatosabmConsultorio(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptNombreConsultorio').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptDescripcionConsultorio').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptColorConsultorioHex').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptRegistroSeleccConsultorio').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptEstadoConsultorio').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptLocalConsultorio').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptDoctorConsultorio').value = $(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('btnAbmConsultorio').value = "Editar datos";
	iniciarSelectorColorConsultorio()
	idAbmConsultorio = $(datostr).children('td[id="td_id"]').html();
}
function verificarcamposConsultorio() {
	var inptNombreConsultorio = document.getElementById('inptNombreConsultorio').value
	var inptDescripcionConsultorio = document.getElementById('inptDescripcionConsultorio').value
	var inptColorConsultorioHex = document.getElementById('inptColorConsultorioHex').value
	var inptLocalConsultorio = document.getElementById('inptLocalConsultorio').value
	var inptEstadoConsultorio = document.getElementById('inptEstadoConsultorio').value
	var inptDoctorConsultorio = document.getElementById('inptDoctorConsultorio').value
	if (inptLocalConsultorio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL LOCAL")
		return false;
	}
	if (inptNombreConsultorio == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL CONSULTORIO")
		return false;
	}
	var accion = "";
	if (idAbmConsultorio != "") {
		accion = "editar";
		if(controlacceso("EDITARFORMULARIOCONSULTORIO","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("INSERTARFORMULARIOCONSULTORIO","accion")==false){return;}
	}
	abmConsultorio(inptLocalConsultorio,inptNombreConsultorio, inptDescripcionConsultorio, inptColorConsultorioHex, inptEstadoConsultorio, inptDoctorConsultorio, idAbmConsultorio, accion);
}
function abmConsultorio(cod_local,nombre, descripcion, color, estado, cod_doctor, cod_Consultorio, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_Consultorio", cod_Consultorio)
	datos.append("nombre", nombre)
	datos.append("descripcion", descripcion)
	datos.append("color", color)
	datos.append("cod_local", cod_local)
	datos.append("estado", estado)
	datos.append("cod_doctor", cod_doctor)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsultorio.php",
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
					limpiarcamposConsultorio()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmConsultorio = ""
					buscarabmConsultorio() 
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}

function buscarabmConsultorio() {
if(controlacceso("BUSCARFORMULARIOCONSULTORIO","accion")==false){return;}
	var codigo = document.getElementById('inptBuscarAbmConsultorio1').value
	var nombre = document.getElementById('inptBuscarAbmConsultorio2').value
	var descripcion = document.getElementById('inptBuscarAbmConsultorio3').value
	var NombreLocal = document.getElementById('inptBuscarAbmConsultorio4').value
	var estado = document.getElementById('inptBuscarAbmConsultorio5').value
	document.getElementById("table_abm_Consultorio").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codigo": codigo,
		"nombre": nombre,
		"estado": estado,
		"descripcion": descripcion,
		"NombreLocal": NombreLocal,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsultorio.php",
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
			document.getElementById("table_abm_Consultorio").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_Consultorio").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_Consultorio").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoConsultorio").value = datos[3];
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function limpiarcamposConsultorio() {
	document.getElementById('inptNombreConsultorio').value = "";
	document.getElementById('inptDescripcionConsultorio').value = "";
	document.getElementById('inptRegistroSeleccConsultorio').value = "";
	document.getElementById('inptEstadoConsultorio').value = "ACTIVO";
	document.getElementById('inptDoctorConsultorio').value = "";
	document.getElementById('btnAbmConsultorio').value = "Guardar datos";
	idAbmConsultorio = "";
}
 
 
function iniciarSelectorColorConsultorio(){
    var colorInput = document.getElementById("inptColorConsultorio");
    var hexInput   = document.getElementById("inptColorConsultorioHex");

    if(!colorInput || !hexInput){
        return;
    }

    function esHexValido(valor){
        return /^#[0-9A-Fa-f]{6}$/.test(valor);
    }

    function sincronizarDesdeColor(){
        hexInput.value = colorInput.value;
    }

    function sincronizarDesdeHex(){
        var valor = hexInput.value.replace(/\s+/g, "").toUpperCase();

        if(valor.charAt(0) != "#"){
            valor = "#" + valor;
        }

        if(esHexValido(valor)){
            colorInput.value = valor;
            hexInput.value = valor;
        }
    }

    colorInput.addEventListener("input", sincronizarDesdeColor);
    colorInput.addEventListener("change", sincronizarDesdeColor);

    hexInput.addEventListener("input", sincronizarDesdeHex);
    hexInput.addEventListener("change", sincronizarDesdeHex);
    hexInput.addEventListener("blur", sincronizarDesdeHex);

    sincronizarDesdeColor();
}

document.addEventListener("DOMContentLoaded", function(){
    iniciarSelectorColorConsultorio();
});
