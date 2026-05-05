/*
ABM CLIENTES
*/
var cod_clienteFK= "";
function verCerrarAbmClientes(mostrar){
	document.getElementById("divSegundoPlano").style.display="none";
	if(!mostrar){
		if(controldebusquedadClientes==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
		document.getElementById("divMinimizadoListadoCliente").style.display="none"
		limpiarcamposbucarabmcliente()
		limpiarcamposCliente()
	document.getElementById("tdEfectoAbmCliente").className="magictime vanishOut"
	$("div[id=divAbmCliente]").fadeOut(500);	
		}else{		
	if(controlacceso("VERLISTADODECLIENTES","accion")==false){return;}
		document.getElementById("divAbmCliente").style.display=""
	document.getElementById("tdEfectoAbmCliente").className="magictime slideDownReturn"
		
		controlventananuevocliente="";
		
	}

}


function limpiarcamposbucarabmcliente(){
	if(controldebusquedadClientes==true){
		
	return
}
	document.getElementById('inptBuscarAbmCliente1').value=""
    document.getElementById('inptBuscarAbmCliente2').value=""
   document.getElementById('inptBuscarAbmCliente3').value=""
 document.getElementById('inptBuscarAbmCliente4').value=""
 document.getElementById("table_abm_clientes").innerHTML=""
			document.getElementById("inptRegistroNroClientes").value=""
			document.getElementById("tbProcessClientes").style.display="none"
			
}
function minimizarabmcliente(){
document.getElementById("tdEfectoAbmCliente").className="magictime slideDown"
	$("div[id=divAbmCliente]").fadeOut(500);	
	document.getElementById("divMinimizadoListadoCliente").style.display=""
}

var controlventananuevocliente="";
function verificarcamposClienteVista() {
	var inptNombreApellidoCliente = document.getElementById('inptNombreApellidoClienteVista').value
	var inptNroDocCliente = document.getElementById('inptNroDocClienteVista').value
	var inptNroTelefCliente = document.getElementById('inptNroTelefClienteVista').value
	var inptNrowhatsappCliente = document.getElementById('inptNrowhatsappClienteVista').value
	var inptDireccionCliente = document.getElementById('inptDireccionClienteVista').value
	var inptReferenciaCliente = document.getElementById('inptReferenciaClienteVista').value
	var inptCalificaCliente = document.getElementById('inptCalificaClienteVista').value
	var inptAccesoCreditoCliente = document.getElementById('inptAccesoCreditoCliente').value
	var inptEstadoCliente = "Activo"
	if (inptNombreApellidoCliente == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL CLIENTE")
		return false;
	}
	if (inptNroDocCliente == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE DOCUMENTO")
		return false;
	}
	if (idFKZona == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA ZONA")
		return false;
	}
	var accion = "nuevo";
if(controlacceso("INSERTARLISTADODECLIENTES","accion")==false){return;}
	abmcliente(inptAccesoCreditoCliente,idFKZona, inptNombreApellidoCliente, inptNroDocCliente, inptNroTelefCliente, inptNrowhatsappCliente, inptDireccionCliente, inptReferenciaCliente, inptCalificaCliente, inptEstadoCliente, idAbmCliente, accion);
}

function verCerrarVentanaAbmCliente(mostrar, abm, ocultar_datos_extras= false) {	
	if (mostrar) {
		document.getElementById('divAbmCliente').style.display = ""
		controlventananuevocliente="venta"

		if (abm) {
			$("div[id=divAbmCliente2]").fadeIn(250)
			document.getElementById('divAbmCliente1').style.display = "none"

			// Ocultar secciones de datos extras
			if (ocultar_datos_extras || controlseleccvistacliente == "presupuesto" || controlseleccvistacliente == "presupuestoDoctor") {
				$("#divAbmCliente2 .abm-cliente-datos-extra").hide();
				document.getElementById('divAbmCliente2').style.width= "850px";
			} else {
				$("#divAbmCliente2 .abm-cliente-datos-extra").show();
				document.getElementById('divAbmCliente2').style.width= "auto";
			}
		} else {
			$("div[id=divAbmCliente1]").fadeIn(250)
			document.getElementById('divAbmCliente2').style.display = "none"	
		}
	} else {
		if (abm) {
			$("div[id=divAbmCliente2]").fadeOut(250)
			document.getElementById('divAbmCliente1').style.display = ""

			if (ventanaAnterior[ventanaAnterior.length - 1] == "listCliente") {
				$("div[id=divAbmCliente1]").fadeOut(250)
				document.getElementById('divAbmCliente').style.display = "none"
			}
		} else {
			$("div[id=divAbmCliente1]").fadeOut(250)
			document.getElementById('divAbmCliente').style.display = "none"
			
			// Evalua si existe una ventana de origen y se actua
			switch (controlseleccvistacliente) {
				case 'calendario':
					AbrirAgendaConsultorios(false);
					break;
			}
		}
	}
}

function verVentanaEditarCliente() {
	if(controlacceso("EDITARLISTADODECLIENTES","accion")==false){return;}
	if (idAbmCliente == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		return;
	}
	verCerrarVentanaAbmCliente(true)
}
var controlfotocliente="";
function ExploradorImagenCliente(File){	
$("input[name=file_1]").click();
controlfotocliente=File;
}
var fotocliente1="";
var extcliente1="";
var fotocliente2="";
var extcliente2="";

 
var fotocliente3 = "";
var extcliente3 = "";
function readFileCliente2(input) {
	var file = $("input[name=" + input.name + "]")[0].files[0];
	var filename = file.name;
	file_extension = filename.split('.').pop();
	var tamanho = file.size;
	if (tamanho > 5000000) {
		ver_vetana_informativa("LA FOTO NO PUEDE EXCEDER LOS 5Mb")
		return false
	}
	//file_extension=filename.substring(filename.lastIndexOf('.')+1).toLowerCase();
	if ((file_extension == "jpeg") || (file_extension == "jpg") || (file_extension == "png")) {
	} else {
		ver_vetana_informativa("LA FOTO SELECCIONADO NO ES JPEG")
		return false;
	}
	var reader = new FileReader();
	reader.onload = function (e) {
		if (controlfotocliente == "foto1") {
			extcliente1 = file_extension;
			fotocliente1 = e.target.result;
			$("div[id=imgFotoCliente1]").css({ "background-image": "url(" + fotocliente1 + ")" })

		}
		if (controlfotocliente == "foto2") {
			extcliente2 = file_extension;
			fotocliente2 = e.target.result;
			$("div[id=imgFotoCliente2]").css({ "background-image": "url(" + fotocliente2 + ")" })

		}
		if (controlfotocliente == "Perfil") {
			extcliente3 = file_extension;
			fotocliente3 = e.target.result;

			$("div[id=imgFotoPerfil1]").css({ "background-image": "url(" + fotocliente3 + ")" })
			$("div[id=imgFotoPerfilMisDatos]").css({ "background-image": "url(" + fotocliente3 + ")" })
		}
	}
	reader.readAsDataURL(input.files[0]);
}
function verCerrarVisorImagen(d,img){
	document.getElementById('divVistaFoto').style.display = "none"
	if (d == "1") {		
		var urlsrc="";
		switch (img) {
			case "cliente1":
				urlsrc = fotocliente1;
				break;
			case "cliente2":
				urlsrc = fotocliente2;
			case "usuario":
				urlsrc = fotocliente3;
				break;
		}
		
		if(urlsrc==""){
			 ver_vetana_informativa("NO SE ENCONTRO NINGUNA IMAGEN PARA VIZUALIZAR")

					 return false;
		}
		$("div[id=divVistaFoto]").fadeIn(250)
		document.getElementById("imgVisor").src=urlsrc
		
	}
}
function imprimirFotoCI(){
$("div[id=imgPrint1]").css({"background-image":"url("+fotocliente1+")"})
$("div[id=imgPrint2]").css({"background-image":"url("+fotocliente2+")"})
var documento=document.getElementById("DivImprimirCi").innerHTML;
localStorage.setItem("reporte", documento);
localStorage.setItem("tipo", "reporte");
window.open("/GoodVentaAsisCap/system/report.html");
}
var idAbmCliente=""
function obtenerdatosabmCliente(datostr){	
		 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });
    datostr.className='tableRegistroSelec'
	document.getElementById('inptNombreApellidoCliente').value=$(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptFechaNacCliente').value=$(datostr).children('td[id="td_datos_105"]').html();
	document.getElementById('inptRegistroSeleccCliente').value=$(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNroDocCliente').value=$(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('inptNroTelefCliente').value=$(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptNrowhatsappCliente').value=$(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptDireccionCliente').value=$(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptReferenciaCliente').value=$(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptCalificaCliente').value=$(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptEstadoCliente').value=$(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptZonaCliente').value=$(datostr).children('td[id="td_datos_10"]').html();
	document.getElementById('inptNroRucCliente').value=$(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptLugrarTrabajoCliente').value=$(datostr).children('td[id="td_datos_15"]').html()
	document.getElementById('inptSalarioCliente').value=$(datostr).children('td[id="td_datos_16"]').html()
	document.getElementById('inptAntiguedadCliente').value=$(datostr).children('td[id="td_datos_17"]').html()
	document.getElementById('inptNroTelefTrabajoCliente1').value=$(datostr).children('td[id="td_datos_18"]').html()
	document.getElementById('inptNroTelefTrabajoCliente2').value=$(datostr).children('td[id="td_datos_19"]').html()
	document.getElementById('inptDireccionTrabajoCliente').value=$(datostr).children('td[id="td_datos_20"]').html()
	document.getElementById('inptAccesoCreditoCliente').value=$(datostr).children('td[id="td_datos_21"]').html()
	document.getElementById('inptUsuarioInsertadoPor').value=$(datostr).children('td[id="td_datos_100"]').html()
	document.getElementById('inptFechaInsertadoPor').value=$(datostr).children('td[id="td_datos_102"]').html()
	document.getElementById('inptUsuarioEditadoPor').value=$(datostr).children('td[id="td_datos_101"]').html()
	document.getElementById('inptFechaEditadoPor').value=$(datostr).children('td[id="td_datos_103"]').html()
	var sms =$(datostr).children('td[id="td_datos_104"]').html()

	if(sms=="SI"){
		document.getElementById('inptSeleccSMS1').checked=true
		document.getElementById('inptSeleccSMS2').checked=false	
	}else{
		document.getElementById('inptSeleccSMS1').checked=false
		document.getElementById('inptSeleccSMS2').checked=true	
	}

	
	
	fotocliente1= $(datostr).children('td[id="td_datos_11"]').html();
	fotocliente2= $(datostr).children('td[id="td_datos_12"]').html();
	 $("div[id=imgFotoCliente1]").css({"background-image":"url("+fotocliente1+")"})
	  $("div[id=imgFotoCliente2]").css({"background-image":"url("+fotocliente2+")"})
	idAbmCliente= $(datostr).children('td[id="td_id"]').html();
	idFKZona= $(datostr).children('td[id="td_datos_9"]').html();
    extcliente1="";
    extcliente2="";
	buscarmasreferenciasclientes();
	buscarFotosCliente()
  document.getElementById('btnAbmCliente').value="Editar datos";
  document.getElementById('btnEditarClientes').style.backgroundColor="";
  document.getElementById('btnAuditoriaClientes').style.backgroundColor="#673ab7";
  document.getElementById('btnUbiClientes').style.backgroundColor="";
  
	
}


function AnhadirMasReferencias(){
	var inptMasRefDireccionCliente=document.getElementById("inptMasRefDireccionCliente").value
	var inptMasRefReferenciaCliente=document.getElementById("inptMasRefReferenciaCliente").value
	var inptMasRefTelefCliente=document.getElementById("inptMasRefTelefCliente").value
	var inptMasRefObservacionCliente=document.getElementById("inptMasRefObservacionCliente").value
	var inptMasRefTipoCliente=document.getElementById("inptMasRefTipoCliente").value
	
var pagina="<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>"
+"<tr id='tbSelecRegistro' onclick='obtenerdatosmasreferencias(this)'  name='tdMasReferencias'>"
+"<td  id='td_datos_1' style='width:10%'>"+inptMasRefObservacionCliente+"</td>"
+"<td  id='td_datos_2' style='width:10%;'>"+inptMasRefTelefCliente+"</td>"
+"<td  id='td_datos_3' style='width:10%'>"+inptMasRefDireccionCliente+"</td>"
+"<td  id='td_datos_4' style='width:10%'>"+inptMasRefReferenciaCliente+"</td>"
+"<td  id='td_datos_5' style='width:10%'>"+inptMasRefTipoCliente+"</td>"
+"</tr>"
+"</table>"
document.getElementById("table_mas_referenciasClientes").innerHTML+=pagina;
		LimpiarMasReferencia()
}
var elementoAddMasReferencias="";
function obtenerdatosmasreferencias(datostr){
	 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });

    datostr.className='tableRegistroSelec'
	document.getElementById('inptMasRefDireccionCliente').value=$(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptMasRefReferenciaCliente').value=$(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptMasRefTelefCliente').value=$(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptMasRefObservacionCliente').value=$(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptMasRefTipoCliente').value=$(datostr).children('td[id="td_datos_5"]').html();
	elementoAddMasReferencias=datostr;
		document.getElementById("btnAddMasReferencias1").style.display="none"
		document.getElementById("btnAddMasReferencias2").style.display=""
		document.getElementById("btnAddMasReferencias3").style.display=""
		document.getElementById("btnAddMasReferencias4").style.display=""
}
function editarMasRefencia(){
	$(elementoAddMasReferencias).children('td[id="td_datos_3"]').text(document.getElementById('inptMasRefDireccionCliente').value)
	$(elementoAddMasReferencias).children('td[id="td_datos_4"]').text(document.getElementById('inptMasRefReferenciaCliente').value)
	$(elementoAddMasReferencias).children('td[id="td_datos_2"]').text(document.getElementById('inptMasRefTelefCliente').value)
	$(elementoAddMasReferencias).children('td[id="td_datos_1"]').text(document.getElementById('inptMasRefObservacionCliente').value)
	document.getElementById("btnAddMasReferencias1").style.display=""
		document.getElementById("btnAddMasReferencias2").style.display="none"
		document.getElementById("btnAddMasReferencias3").style.display="none"
		document.getElementById("btnAddMasReferencias4").style.display="none"
		LimpiarMasReferencia()
}
function EliminarMasReferencia(){
	$(elementoAddMasReferencias).remove()
	document.getElementById("btnAddMasReferencias1").style.display=""
		document.getElementById("btnAddMasReferencias2").style.display="none"
		document.getElementById("btnAddMasReferencias3").style.display="none"
		document.getElementById("btnAddMasReferencias4").style.display="none"
		LimpiarMasReferencia()
}
function CancelarMasReferencia(){
	document.getElementById("btnAddMasReferencias1").style.display=""
		document.getElementById("btnAddMasReferencias2").style.display="none"
		document.getElementById("btnAddMasReferencias3").style.display="none"
		document.getElementById("btnAddMasReferencias4").style.display="none"
		LimpiarMasReferencia()
}
function LimpiarMasReferencia(){
	document.getElementById('inptMasRefDireccionCliente').value="";
	document.getElementById('inptMasRefReferenciaCliente').value="";
	document.getElementById('inptMasRefTelefCliente').value="";
	document.getElementById('inptMasRefObservacionCliente').value="";
	document.getElementById("inptMasRefTipoCliente").value="";
	elementoAddMasReferencias="";
}

function verificarcamposCliente() {
	var inptFechaNacCliente = document.getElementById('inptFechaNacCliente').value
	var inptNombreApellidoCliente = document.getElementById('inptNombreApellidoCliente').value
	var inptNroDocCliente = document.getElementById('inptNroDocCliente').value
	var inptNroRucCliente = document.getElementById('inptNroRucCliente').value
	var inptNroTelefCliente = document.getElementById('inptNroTelefCliente').value
	var inptNrowhatsappCliente = document.getElementById('inptNrowhatsappCliente').value
	var inptDireccionCliente = document.getElementById('inptDireccionCliente').value
	var inptReferenciaCliente = document.getElementById('inptReferenciaCliente').value
	var inptCalificaCliente = document.getElementById('inptCalificaCliente').value
	var inptEstadoCliente = document.getElementById('inptEstadoCliente').value
	var inptLugrarTrabajoCliente = document.getElementById('inptLugrarTrabajoCliente').value
	var inptDireccionTrabajoCliente = document.getElementById('inptDireccionTrabajoCliente').value
	var inptSalarioCliente = document.getElementById('inptSalarioCliente').value
	var inptAntiguedadCliente = document.getElementById('inptAntiguedadCliente').value
	var inptNroTelefTrabajoCliente1 = document.getElementById('inptNroTelefTrabajoCliente1').value
	var inptNroTelefTrabajoCliente2 = document.getElementById('inptNroTelefTrabajoCliente2').value
	var inptAccesoCreditoCliente = document.getElementById('inptAccesoCreditoCliente').value
	var sms = "";

	if (inptNombreApellidoCliente == "") {
		ver_vetana_informativa("FALTAN DATOS", "FALTO INGRESAR EL NOMBRE DEL CLIENTE", "advertencia")
		return false;
	}
	if (inptNroDocCliente == "") {
		ver_vetana_informativa("FALTAN DATOS", "FALTO INGRESAR EL NRO DE DOCUMENTO", "advertencia")
		return false;
	}
	
	if (idFKZona == "" || idFKZona == 0) {
		ver_vetana_informativa("FALTAN DATOS", "FALTO SELECCIONAR UNA ZONA", "advertencia")
		return false;
	}
	
	// Evalua si se estan mostrando los datos de campo extra
	if (document.querySelectorAll("#divAbmCliente2 .abm-cliente-datos-extra")[0].style.display != 'none') {
		if (document.getElementById('inptSeleccSMS1').checked == true) {
			sms = "SI";
		} else {
			sms = "NO";
		}
	
		if (inptDireccionCliente == "") {
			ver_vetana_informativa("FALTAN DATOS", "FALTO INGRESAR LA DIRECCION DEL CLIENTE", "advertencia")
			return false;
		}
	
		if (inptNroTelefCliente == "") {
			ver_vetana_informativa("FALTAN DATOS", "FALTO INGRESAR EL NRO DE TELEFONO", "advertencia")
			return false;
		}
	
		if (inptReferenciaCliente == "") {
			ver_vetana_informativa("FALTAN DATOS", "FALTO INGRESAR UNA REFERENCIA DEL CLIENTE", "advertencia")
			return false;
		}
	}

	var accion = "";
	if (idAbmCliente != "") {
		accion = "editar";
		if (controlacceso("EDITARLISTADODECLIENTES", "accion") == false) { return; }
	} else {
		if (controlacceso("INSERTARLISTADODECLIENTES", "accion") == false) { return; }
		accion = "nuevo";
	}

	abmcliente(inptFechaNacCliente, sms, inptAccesoCreditoCliente, inptLugrarTrabajoCliente, inptDireccionTrabajoCliente, inptSalarioCliente, inptAntiguedadCliente, inptNroTelefTrabajoCliente1, inptNroTelefTrabajoCliente2, idFKZona, inptNombreApellidoCliente, inptNroRucCliente, inptNroDocCliente, inptNroTelefCliente, inptNrowhatsappCliente, inptDireccionCliente, inptReferenciaCliente, inptCalificaCliente, inptEstadoCliente, idAbmCliente, accion);
}

function  abmcliente(FechaNac,sms,accesocredito,lugardetrabajo,direcciontrab,salario,antiguedad,teleftrab1,teleftrab2,idzonaFk,nombre_persona,rut_cliente,ci_cliente,telefono,whapp,direccion,email,Calificacion,estado,cod_persona,accion){
	verCerrarEfectoCargando("1")

	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru" , userid)
	datos.append("passu" , passuser)
	datos.append("navegador" , navegador)
	datos.append("funt", accion)
	datos.append("cod_persona" , cod_persona)
	datos.append("nombre_persona" , nombre_persona)
	datos.append("direccion" , direccion)
	datos.append("FechaNac" , FechaNac)
	datos.append("telefono" , telefono)
	datos.append("email" , email)//Sirve para la referencia
	datos.append("rut_cliente" , rut_cliente)
	datos.append("ci_cliente" , ci_cliente)
	datos.append("Calificacion" , Calificacion)
	datos.append("whapp" , whapp)
	datos.append("estado" , estado)
	datos.append("idzonaFk" , idzonaFk)
	datos.append("foto1", fotocliente1)
	datos.append("sms", sms)
	datos.append("ext1", extcliente1)
	datos.append("foto2", fotocliente2)
	datos.append("ext2", extcliente2)		
	datos.append("lugardetrabajo", lugardetrabajo)		
	datos.append("direcciontrab", direcciontrab)		
	datos.append("salario", salario)		
	datos.append("antiguedad", antiguedad)		
	datos.append("teleftrab1", teleftrab1)		
	datos.append("teleftrab2", teleftrab2)		
	datos.append("accesocredito", accesocredito)			
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmclientes.php",
			type:"post",
	        cache:false,
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
		
				error: function(jqXHR, textstatus, errorThrowm){
						verCerrarEfectoCargando("")
					manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
					 return false;
			},
			success: function(responseText)
			{
			  	 verCerrarEfectoCargando("")
			Respuesta=responseText;			
				console.log(Respuesta)
		try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		   Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				var zonaCliente = "";
                if (document.getElementById("inptZonaCliente")) {
                    zonaCliente = document.getElementById("inptZonaCliente").value;
                }

                var styleFondo = "";
                if (accesocredito == "Denegado") {
                    styleFondo = "background-color:#ff5722;color:#fff";
                }

                var tablaClienteFicticia = $(
                    "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>" +
                        "<tr class='tableRegistroSelec' id='trdatoClienteCi' onclick='obtenerdatosvistacliente(this)' style='" + styleFondo + "'>" +
                            "<td id='td_id' style='display:none'>" + datos["2"] + "</td>" +
                            "<td id='td_datos_2' style='width:10%'>" + ci_cliente + "</td>" +
                            "<td id='td_datos_13' style='width:10%'>" + rut_cliente + "</td>" +
                            "<td id='td_datos_1' style='width:10%'>" + nombre_persona + "</td>" +
                            "<td id='td_datos_10' style='display:none'>" + zonaCliente + "</td>" +
                            "<td id='td_datos_3' style='width:10%'>" + direccion + "</td>" +
                            "<td id='td_datos_4' style='width:10%'>" + telefono + "</td>" +
                            "<td id='td_datos_5' style='display:none'>" + email + "</td>" +
                            "<td id='td_datos_6' style='display:none'>" + Calificacion + "</td>" +
                            "<td id='td_datos_7' style='display:none'>" + whapp + "</td>" +
                            "<td id='td_datos_8' style='display:none'>" + estado + "</td>" +
                            "<td id='td_datos_9' style='display:none'>" + idzonaFk + "</td>" +
                            "<td id='td_datos_11' style='display:none'>" + fotocliente1 + "</td>" +
                            "<td id='td_datos_12' style='display:none'>" + fotocliente2 + "</td>" +
                            "<td id='td_datos_14' style='display:none'>" + accesocredito + "</td>" +
                            "<td id='td_datos_15' style='display:none'></td>" +
                            "<td id='td_datos_16' style='display:none'>" + lugardetrabajo + "</td>" +
                            "<td id='td_datos_17' style='display:none'>" + salario + "</td>" +
                            "<td id='td_datos_18' style='display:none'>" + antiguedad + "</td>" +
                            "<td id='td_datos_19' style='display:none'>" + teleftrab1 + "</td>" +
                            "<td id='td_datos_20' style='display:none'>" + teleftrab2 + "</td>" +
                            "<td id='td_datos_21' style='display:none'>" + direcciontrab + "</td>" +
                            "<td id='td_datos_22' style='display:none'>" + FechaNac + "</td>" +
                        "</tr>" +
                    "</table>"
                );

                elementoCliente = tablaClienteFicticia.find("tr")[0];

				idFkCliente = datos["2"];
                if (controlseleccvistacliente) {
                    
                    EnviarClienteDesde();
                }
				
				var control=0;
				$("tr[name=tdDetalleItemImagen]").each(function(i, elementohtml){
					if($(elementohtml).children('td[id="td_id_2"]').html()==""){
						control++;
					}
				});
				
				if(control > 0){
					VerificarCargarFotosCliente(idFkCliente)
				}
				
				
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
				abmmasreferenciascliente(idFkCliente)
				if(controlventananuevocliente=="ventavista"){
					document.getElementById("divAbmCliente").style.display="none"
					buscarvistacliente()
				return
				}
				
				if(controlventananuevocliente=="venta"){
					document.getElementById("divAbmCliente").style.display="none"
					
		document.getElementById('inptClienteVenta').value = nombre_persona;
		document.getElementById('inptClienteVenta2').value = nombre_persona;
		document.getElementById('inptDocClienteVenta').value = ci_cliente
		document.getElementById('inptDocClienteVenta2').value = ci_cliente
		document.getElementById('inptDireccionVenta').value = direccion
		document.getElementById('inptTelefVenta').value = telefono
			    vercerrarvistacliente("","")
				}else{
					idAbmCliente=""	
				buscarabmCliente()
				
				}

//limpiarcamposCliente()
verCerrarVentanaAbmCliente(false, false);
			}
			
			}catch(error)
				{
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				}
		 
					
			}
			});
			
	
}
function  abmmasreferenciascliente(idcliente){
	  var datos = new FormData();
	var control=1;
	$("tr[name=tdMasReferencias]").each(function(i, elementohtml){
	
	var observacion=$(elementohtml).children('td[id="td_datos_1"]').html();
    datos.append("observacion"+control, observacion)
	
	var telefono=$(elementohtml).children('td[id="td_datos_2"]').html();
    datos.append("telefono"+control, telefono)

	var direccion=$(elementohtml).children('td[id="td_datos_3"]').html();
    datos.append("direccion"+control, direccion)
	
	var referencia=$(elementohtml).children('td[id="td_datos_4"]').html();
    datos.append("referencia"+control, referencia)
	
	var Tipo=$(elementohtml).children('td[id="td_datos_5"]').html();
    datos.append("Tipo"+control, Tipo)
	
	control=control+1;	
	
	   });
	control=control-1;
	
	if(control==0){
		return
	}
	
	verCerrarEfectoCargando("1")
	
			obtener_datos_user();
			
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", "addmasreferencias")
			 datos.append("idcliente" , idcliente)
			  datos.append("totalCargado" , control)
	
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmclientes.php",
			type:"post",
	        cache:false,
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
		
				error: function(jqXHR, textstatus, errorThrowm){
						verCerrarEfectoCargando("")
					 manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")

					 return false;
			},
			success: function(responseText)
			{
			  	 verCerrarEfectoCargando("")
			Respuesta=responseText;			
				console.log(Respuesta)
		try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		  Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {		
			
				
			}			
			}catch(error)
				{
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				} 
					
			}
			});
			
	
}
function buscarmasreferenciasclientes(){
		 document.getElementById("table_mas_referenciasClientes").innerHTML=paginacargando
			obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idAbmCliente,
			"funt": "buscarmasreferencias"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmclientes.php",
			type:"post",
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
		
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_mas_referenciasClientes").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_mas_referenciasClientes").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
		   var datos_buscados=datos[2];		 
			document.getElementById("table_mas_referenciasClientes").innerHTML=datos_buscados	
			
			}
			}catch(error)
				{
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				}
			}
			});
	
	
}




function checkestadoClientes(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarCliente1').checked=true
		document.getElementById('inptSeleccEstadoBuscarCliente2').checked=false	
	}else{
		
		document.getElementById('inptSeleccEstadoBuscarCliente1').checked=false
		document.getElementById('inptSeleccEstadoBuscarCliente2').checked=true
	}
}
var registrocargadoclientes="";
var totalregistroclientes="";
var controldebusquedadClientes=false
function cancelarCargaClientes(){
	controldebusquedadClientes=false
	document.getElementById("divProgressClientes").style.backgroundColor='#ff5722'
}

function buscarabmCliente(){
if(controlacceso("BUSCARLISTADODECLIENTES","accion")==false){return;}
var codigo=document.getElementById('inptBuscarAbmCliente1').value
var documento=document.getElementById('inptBuscarAbmCliente2').value
var cliente=document.getElementById('inptBuscarAbmCliente3').value
var zona=document.getElementById('inptBuscarAbmCliente4').value
var accesocredito=document.getElementById('inptBuscarAbmCliente5').value
var estado="";
if(document.getElementById('inptSeleccEstadoBuscarCliente1').checked==true){
estado="Activo"
}else{
estado="Inactivo"	
}
if(controldebusquedadClientes==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadClientes=true
		 document.getElementById("table_abm_clientes").innerHTML=paginacargando
		 	document.getElementById("tbProcessClientes").style.display="none"
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"codigo": codigo,
			"documento": documento,
			"cliente": cliente,
			"zona": zona,
			"estado": estado,
			"accesocredito": accesocredito,
			"funt": "buscar"
			};
			
			
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmclientes.php",
			type:"post",
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
		
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_abm_clientes").innerHTML=''
			controldebusquedadClientes=false
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_abm_clientes").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
		  var datos_buscados=datos[2];		 
		 	 
			document.getElementById("table_abm_clientes").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroClientes").value=datos[3]
	
registrocargadoclientes=Number(datos[99]);
			totalregistroclientes=Number(datos[100]);
						 if(totalregistroclientes>registrocargadoclientes){
						 	var porce=((registrocargadoclientes*100)/totalregistroclientes).toFixed(0)
	document.getElementById("divProgressClientes").style.width=porce+"%"
						 document.getElementById("table_abm_clientes").innerHTML += "<div id='table_abm_mas_clientes'></div>"
						  buscarabmMasCliente();
					 }else{
						 controldebusquedadClientes=false
					 }
	  
			}
			}catch(error)
				{
					controldebusquedadClientes=false
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				}
			}
			});
	
	
}
function buscarabmMasCliente(c){
if(controlacceso("BUSCARLISTADODECLIENTES","accion")==false){return;}
var codigo=document.getElementById('inptBuscarAbmCliente1').value
var documento=document.getElementById('inptBuscarAbmCliente2').value
var cliente=document.getElementById('inptBuscarAbmCliente3').value
var zona=document.getElementById('inptBuscarAbmCliente4').value
var accesocredito=document.getElementById('inptBuscarAbmCliente5').value
var estado="";
if(document.getElementById('inptSeleccEstadoBuscarCliente1').checked==true){
estado="Activo"
}else{
estado="Inactivo"	
}
if(c=="1"){
	controldebusquedadClientes=true
}
if(controldebusquedadClientes==false){
return
}
controldebusquedadClientes=true
		 document.getElementById("table_abm_mas_clientes").innerHTML=paginacargando
		 	document.getElementById("tbProcessClientes").style.display=""
			document.getElementById("divProgressClientes").style.backgroundColor=''
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"codigo": codigo,
			"documento": documento,
			"cliente": cliente,
			"zona": zona,
			"estado": estado,
			"registrocargado": registrocargadoclientes,
			"accesocredito": accesocredito,
			"funt": "buscarmas"
			};
			
			
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmclientes.php",
			type:"post",
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
		
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_abm_mas_clientes").innerHTML=''
			document.getElementById("divProgressClientes").style.backgroundColor='#ff5722'
			controldebusquedadClientes=false
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_abm_mas_clientes").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
		  var datos_buscados=datos[2];		 
		 	 
			document.getElementById("table_abm_mas_clientes").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroClientes").value=datos[3]
			
registrocargadoclientes=Number(datos[99]);
					
						 if(totalregistroclientes>registrocargadoclientes){
						 	var porce=((registrocargadoclientes*100)/totalregistroclientes).toFixed(0)
	document.getElementById("divProgressClientes").style.width=porce+"%"
						 document.getElementById("table_abm_mas_clientes").innerHTML += "<div id='table_abm_mas_clientes'></div>"
						 document.getElementById("table_abm_mas_clientes").id=""
						  buscarabmMasCliente();
					 }else{
						 document.getElementById("tbProcessClientes").style.display="none"
						 controldebusquedadClientes=false
					 }
	  
			}
			}catch(error)
				{
					document.getElementById("divProgressClientes").style.backgroundColor='#ff5722'
					controldebusquedadClientes=false
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				}
			}
			});
	
	
}


function limpiarcamposCliente(){
	document.getElementById('inptLugrarTrabajoCliente').value="";
	document.getElementById('inptSalarioCliente').value="";
	document.getElementById('inptAntiguedadCliente').value="";
	document.getElementById('inptNroTelefTrabajoCliente1').value="";
	document.getElementById('inptNroTelefTrabajoCliente2').value="";
	document.getElementById('inptNombreApellidoCliente').value="";
	document.getElementById('inptFechaNacCliente').value="";
	document.getElementById('inptRegistroSeleccCliente').value="";
	document.getElementById('inptNroDocCliente').value="";
	document.getElementById('inptNroRucCliente').value="";
	document.getElementById('inptNroTelefCliente').value="";
	document.getElementById('inptNrowhatsappCliente').value="";
	document.getElementById('inptDireccionCliente').value="";
	document.getElementById('inptReferenciaCliente').value="";
	document.getElementById('inptMasRefDireccionCliente').value="";
	document.getElementById('inptMasRefReferenciaCliente').value="";
	document.getElementById('inptMasRefTelefCliente').value="";
	document.getElementById('inptMasRefObservacionCliente').value="";
	LimpiarMasReferencia()
	document.getElementById('table_mas_referenciasClientes').innerHTML="";
	document.getElementById('inptCalificaCliente').value="EXCELENTE";
	document.getElementById('inptEstadoCliente').value="Activo";
	document.getElementById('inptDireccionTrabajoCliente').value="";
	document.getElementById('inptZonaCliente').value="";
	document.getElementById('inptSeleccSMS1').checked=true;
	document.getElementById('inptSeleccSMS2').checked=false;
	
	document.getElementById('btnAbmCliente').value="Guardar datos";
	 $("div[id=imgFotoCliente1]").css({"background-image":"url()"})
	  $("div[id=imgFotoCliente2]").css({"background-image":"url()"})
	idAbmCliente="";
	 fotocliente1="";
  extcliente1="";
  fotocliente2="";
  extcliente2="";
   document.getElementById('btnEditarClientes').style.backgroundColor="#b7b7b7";
   document.getElementById('btnAuditoriaClientes').style.backgroundColor="#b7b7b7";
   document.getElementById('btnUbiClientes').style.backgroundColor="#b7b7b7";
   
   LimpiarCamposCargarFotosCliente()
   document.getElementById('table_abm_imagen_clientes').innerHTML = "";
}


var idFkCliente = ""
var controlseleccvistacliente = ""
function vercerrarvistacliente(d, ventana) {
	if (d == "1") {
		buscarvistacliente();
		document.getElementById("divVistaCliente").style.display=""
		document.getElementById("tdEfectoVistaCliente").className="magictime slideLeftReturn"
		controlseleccvistacliente = ventana
	} else {
		document.getElementById("tdEfectoVistaCliente").className="magictime slideRight"
		$("div[id=divVistaCliente]").fadeOut(500)
	}
}
function buscarvistacliente() {
	var documento = document.getElementById('inptBuscarVistaCliente1').value
	var ruc = document.getElementById('inptBuscarVistaCliente2').value
	var cliente = document.getElementById('inptBuscarVistaCliente3').value
	var telef = document.getElementById('inptBuscarVistaCliente4').value
	document.getElementById("table_vista_cliente").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"ruc": ruc,
		"documento": documento,
		"cliente": cliente,
		"telef": telef,
		"funt": "buscarvista"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmclientes.php",
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
			document.getElementById("table_vista_cliente").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_cliente").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
				var datos_buscados = datos[2];
					document.getElementById("table_vista_cliente").innerHTML = datos_buscados


				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}


var elementoCliente="";
function obtenerdatosvistacliente(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	IdClienteFKCuentas = $(datostr).children('td[id="td_id"]').html();
    elementoCliente=datostr;
	buscarcuentasClienteCancelados()
	buscarcuentasClientePendientes()	
}
function EnviarClienteDesde() {
	if(elementoCliente==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
	return;
	}
	var datostr=elementoCliente;
	switch (controlseleccvistacliente) {
		case "venta":
			idFkCliente = $(datostr).children('td[id="td_id"]').html();
			document.getElementById('inptClienteVenta').value = $(datostr).children('td[id="td_datos_1"]').html();
			document.getElementById('inptClienteVenta2').value = $(datostr).children('td[id="td_datos_1"]').html();
			document.getElementById('inptDocClienteVenta').value = $(datostr).children('td[id="td_datos_2"]').html();
			document.getElementById('inptDocClienteVenta2').value = $(datostr).children('td[id="td_datos_2"]').html();	
			document.getElementById('inptDireccionVenta').value =  $(datostr).children('td[id="td_datos_3"]').html();
			document.getElementById('inptTelefVenta').value =  $(datostr).children('td[id="td_datos_4"]').html();
			document.getElementById('inptAccesoCreditoVentaCliente').value =  $(datostr).children('td[id="td_datos_14"]').html();
			document.getElementById('inptLugrarTrabajoCliente').value =  $(datostr).children('td[id="td_datos_16"]').html();
			document.getElementById('inptDireccionTrabajoCliente').value =  $(datostr).children('td[id="td_datos_21"]').html();
			document.getElementById('inptSalarioCliente').value =  $(datostr).children('td[id="td_datos_17"]').html();
			document.getElementById('inptAntiguedadCliente').value =  $(datostr).children('td[id="td_datos_18"]').html();
			document.getElementById('inptNroTelefTrabajoCliente1').value =  $(datostr).children('td[id="td_datos_19"]').html();
			document.getElementById('inptNroTelefTrabajoCliente2').value =  $(datostr).children('td[id="td_datos_20"]').html();
			// document.getElementById('inptTelefVenta').value =  $(datostr).children('td[id="td_datos_4"]').html();
			// alert($(datostr).children('td[id="td_datos_16"]').html())
			document.getElementById("btnMasInfoClienteVenta").style.display=''
			document.getElementById("btnNuevoClienteVenta").style.display='none'	
			break;
		case 'garante':
			idGaranteFk = $(datostr).children('td[id="td_id"]').html();
			document.getElementById('inptGaranteVenta').value = $(datostr).children('td[id="td_datos_1"]').html();
			document.getElementById('inptDocGaranteVenta').value = $(datostr).children('td[id="td_datos_2"]').html();
			break;
		case 'Agenda':
			cod_clienteAgenda = $(datostr).children('td[id="td_id"]').html();
			document.getElementById('inptClienteAgenda').value = $(datostr).children('td[id="td_datos_1"]').html();
			break;
		case 'expediente':
			codClienteFkExpediente= $(datostr).children('td[id="td_id"]').html();
			document.getElementById('inptBuscarInfExpedientefiltro').value=$(datostr).children('td[id="td_datos_1"]').html();
			document.getElementById('inptBuscarInfExpedienteNroDocumento').value = $(datostr).children('td[id="td_datos_2"]').html();
			document.getElementById('inptBuscarInfExpedienteNroTelef').value = $(datostr).children('td[id="td_datos_4"]').html();
			break;
		case 'Credito':
			idFkCliente = $(datostr).children('td[id="td_id"]').html();
			document.getElementById('inptNombreSolicitudCredito').value = $(datostr).children('td[id="td_datos_1"]').html();
			document.getElementById('inptNroDocSolicitudCredito').value = $(datostr).children('td[id="td_datos_2"]').html();
			document.getElementById('inptDireccionSolicitudCredito').value =  $(datostr).children('td[id="td_datos_3"]').html();
			document.getElementById('inptNroTelefSolicitudCredito').value =  $(datostr).children('td[id="td_datos_4"]').html();
			document.getElementById('inptLugrarTrabajoSolicitudCredito').value =  $(datostr).children('td[id="td_datos_15"]').html();
			document.getElementById('inptDireccionTrabajoSolicitudCredito').value =  $(datostr).children('td[id="td_datos_20"]').html();
			document.getElementById('inptSalarioSolicitudCredito').value =  $(datostr).children('td[id="td_datos_16"]').html();
			document.getElementById('inptAntiguedadSolicitudCredito').value =  $(datostr).children('td[id="td_datos_17"]').html();
			document.getElementById('inptNroTelefTrabajoSolicitudCredito1').value =  $(datostr).children('td[id="td_datos_18"]').html();
			document.getElementById('inptNroTelefTrabajoSolicitudCredito2').value =  $(datostr).children('td[id="td_datos_19"]').html();
			document.getElementById('inptNrowhatsappSolicitudCredito').value =  $(datostr).children('td[id="td_datos_7"]').html();
			document.getElementById('inptFechaNacSolicitudCredito').value =  $(datostr).children('td[id="td_datos_22"]').html();
			document.getElementById('inptNroRucSolicitudCredito').value =  $(datostr).children('td[id="td_datos_13"]').html();
			document.getElementById('inptReferenciaSolicitudCredito').value =  $(datostr).children('td[id="td_datos_5"]').html();
			
			document.getElementById('inptZonaSolicitudCredito').value =  $(datostr).children('td[id="td_datos_10"]').html();
			idFKZona =  $(datostr).children('td[id="td_datos_9"]').html();
			
			buscarmasreferenciasSolicitudCredito(idFkCliente)
			break;
		case 'Solicitud_garante':
			cod_garanteFK = $(datostr).children('td[id="td_id"]').html();
			document.getElementById('inptGaranteSolicitudCredito').value = $(datostr).children('td[id="td_datos_1"]').html();
			break;
		case 'presupuesto':
			idFkCliente = $(datostr).children('td[id="td_id"]').html();
			document.getElementById('inptDocumentoClientePresupuesto').value = $(datostr).children('td[id="td_datos_2"]').html();	
			document.getElementById('inptNombreClientePresupuesto').value = $(datostr).children('td[id="td_datos_1"]').html();
			abmPresupuesto(idabmPresupuesto, "", idFkCliente, null, null);
			break;
		case 'presupuestoDoctor':
			idFkCliente = $(datostr).children('td[id="td_id"]').html();
			document.getElementById('inptDocumentoClientePresupuestoDoc').value = $(datostr).children('td[id="td_datos_2"]').html();	
			document.getElementById('inptNombreClientePresupuestoDoc').value = $(datostr).children('td[id="td_datos_1"]').html();
			if (typeof actualizarResumenPacientePresupuestoDoc === "function") {
				actualizarResumenPacientePresupuestoDoc();
			}
			if (typeof verPasoPresupuestoDoc === "function") {
				verPasoPresupuestoDoc(1);
			}
			abmPresupuesto(idabmPresupuesto, "", idFkCliente, null, null);
			break;
		case 'calendario':
			AbrirAgendaConsultorios(false);
			break;
	}
	
	// Verifica los datos del cliente
	if (
		(controlseleccvistacliente == "presupuesto" || controlseleccvistacliente == "agenda") && (
			($(datostr).children('td[id="td_datos_2"]').html() == "") ||
			($(datostr).children('td[id="td_datos_7"]').html() == "") ||
			($(datostr).children('td[id="td_datos_10"]').html() == "")
		)
	) {
		ver_vetana_informativa("Faltan datos del cliente", "Se debe completar los datos del cliente.", "advertencia");
		EditarDatosClienteDesdeVenta(true);
	} else if (
		controlseleccvistacliente != "presupuestoDoctor" && !(controlseleccvistacliente == "presupuesto" || controlseleccvistacliente == "agenda") && (
			($(datostr).children('td[id="td_datos_2"]').html() == "") ||
			($(datostr).children('td[id="td_datos_7"]').html() == "") ||
			($(datostr).children('td[id="td_datos_10"]').html() == "") ||
			($(datostr).children('td[id="td_datos_3"]').html() == "")
		)
	) {
		ver_vetana_informativa("Faltan datos del cliente", "Se debe completar los datos del cliente.", "advertencia");
		EditarDatosClienteDesdeVenta();
	} else {
		document.getElementById("divVistaCliente").style.display = "none"
	}

	document.getElementById("table_vista_cliente").innerHTML = ""
	document.getElementById("table_clientes_cuentas1").innerHTML = ""
	document.getElementById("table_clientes_cuentas2").innerHTML = ""
	
}

var IdClienteFKCuentas="";
function buscarcuentasClienteCancelados() {
	document.getElementById("table_clientes_cuentas1").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": IdClienteFKCuentas,
		"funt": "buscarCuentasCanceladas"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmventa.php",
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
			document.getElementById("table_clientes_cuentas1").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_clientes_cuentas1").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {

				var datos_buscados = datos[2];
				document.getElementById("table_clientes_cuentas1").innerHTML = datos_buscados


				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function buscarcuentasClientePendientes() {
	document.getElementById("table_clientes_cuentas2").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": IdClienteFKCuentas,
		"funt": "buscarCuentasPendientes"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmventa.php",
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
			document.getElementById("table_clientes_cuentas2").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_clientes_cuentas2").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];

			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
					var datos_buscados = datos[2];
					document.getElementById("table_clientes_cuentas2").innerHTML = datos_buscados


				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function buscarclienteporci() {
	var buscador = document.getElementById('inptDocClienteVenta').value
	if(buscador==""){
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE DOCUMENTO ")
		return;
	}
	document.getElementById('inptDocClienteVenta').value='....'
	document.getElementById('inptClienteVenta').value='Buscandoo....'
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"funt": "buscarporci"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmclientes.php",
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
			document.getElementById('inptDocClienteVenta').value=""
	document.getElementById('inptClienteVenta').value=''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_cliente").innerHTML = ''
				document.getElementById('inptDocClienteVenta').value=""
	document.getElementById('inptClienteVenta').value=''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
				var datos_buscados = datos[2];
				if(datos_buscados!=""){
					document.getElementById("table_vista_cliente").innerHTML = datos_buscados
                    obtenerdatosvistacliente(document.getElementById("trdatoClienteCi"))
					controlseleccvistacliente ="venta"
					EnviarClienteDesde()
				}else{
					ver_vetana_informativa("REGISTRO NO ENCONTRADO")
					
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

//ABM Imagenes Cliente
function verCerrarAbmCargarFotosCliente(d){
	if(d=="1"){
		document.getElementById("divAbmCargarFotosCliente").style.display = "";
		/* buscarAbmContratoDocumentos() */
		
	}else{
		document.getElementById("divAbmCargarFotosCliente").style.display="none"
	}
}
function ExploradorArchivoClientes(File){	
$("input[id="+File+"]").click();
}
var archivo="";
var extension="";	
var urlarchivopdf="";
function readFileDoc(input) {
	var file = $("input[name=" + input.name + "]")[0].files[0];
	var filename = file.name;
	var tamanho = file.size;
	if (tamanho > 5000000) {
		ver_vetana_informativa("EL DOCUMENTO NO PUEDE EXCEDER LOS 5Mb")
		return false
	}

	file_extension = filename.substring(filename.lastIndexOf('.') + 1).toLowerCase();
	
	if (!(file_extension.toLowerCase() == "jpeg") || (file_extension.toLowerCase() == "jpg") || (file_extension.toLowerCase() == "png")) {
		ver_vetana_informativa("LA IMAGEN SELECCIONADO DEBE TENER UNA EXTENSIÓN JPEG, JPG O PNG")
		return false;
	}

	urlarchivopdf = URL.createObjectURL(file);
	/*
	var reader = new FileReader();
	reader.onload = function (e) {
		extension = file_extension;
		archivo = e.target.result;
		*/
		document.getElementById("text-carga-2").style.display = ""
		document.getElementById("text-carga").style.display = "none"

		document.getElementById("btnAddImagen").style.backgroundColor = "";
		document.getElementById("btnEliminarImagen").style.backgroundColor = "#d5d3d3";
		document.getElementById("btnVerImagenCliente").style.backgroundColor = "#d5d3d3";
		$("tr[id=tbSelecRegistroImagen]").each(function (i, td) {
			td.className = ''
		});

		elementoimagenseleccionado = "";
		document.getElementById("file_2").value = "";
	/*
	}
	reader.readAsDataURL(input.files[0]);
	*/
}

function AddCargarFotosCliente(){
  	var codigo=stringGenerador(5);
	if(archivo ==""){
		ver_vetana_informativa("FALTÓ SELECCIONAR UN ARCHIVO")
		return;
	}
	
	let descripcion = document.getElementById('inptDescripcionCargarFotosClientes').value
	let fecha = document.getElementById('inptFechaCargarFotosCliente').value
	
	if(fecha == ""){
		ver_vetana_informativa("FALTÓ SELECCIONAR UNA FECHA")
		return;
	}
	
	var pagina="<table id='"+codigo+"' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>"
+"<tr id='tbSelecRegistroImagen' onclick='SeleccionarItemImagen(this)'  name='tdDetalleItemImagen'>"
+"<td id='td_id_1' style='display:none'>"+codigo+"</td>"
+"<td id='td_id_2' style='display:none'></td>"
+"<td id='td_id_3' style='display:none'></td>"
+"<td  id='td_datos_1' style='display:none'>"+archivo+"</td>"
+"<td  id='td_datos_2' style='display:none'>"+extension+"</td>"
+"<td  id='td_datos_3' style='display:none'>"+urlarchivopdf+"</td>"
+"<td id='' style='width:20%'>IMAGEN</td>"
+"<td id='td_datos_4' style='width:60%'>"+descripcion+"</td>"
+"<td id='td_datos_5' style='width:20%'>"+fecha+"</td>"
+"<tr>"
+"</table>"


document.getElementById("table_abm_imagen_clientes").innerHTML+=pagina;
document.getElementById("btnAddImagen").style.backgroundColor = "#d5d3d3";

document.getElementById('inptDescripcionCargarFotosClientes').value=""
document.getElementById('inptFechaCargarFotosCliente').value=""
document.getElementById('text-carga').style.display=""
document.getElementById('text-carga-2').style.display="none"

archivo = "";
extension = "";
$("tr[id=tbSelecRegistroImagen]").each(function(i, td){
	td.className=''
});

}
var elementoimagenseleccionado="";
function SeleccionarItemImagen(datostr) {
	elementoimagenseleccionado = datostr
	$("tr[id=tbSelecRegistroImagen]").each(function(i, td){		
		 td.className=''
		
	   });
	datostr.className='tableRegistroSelec'	
	
	document.getElementById("btnEliminarImagen").style.backgroundColor = "#f32121d1";
	document.getElementById("btnVerImagenCliente").style.backgroundColor = "#2196F3";
	

	document.getElementById("btnAddImagen").style.backgroundColor = "#d5d3d3";
	archivo = "";
	extension = "";
}
function VerificarCargarFotosCliente(idabm){
	var control=0;
	$("tr[name=tdDetalleItemImagen]").each(function(i, elementohtml){
	if($(elementohtml).children('td[id="td_id_2"]').html()==""){
		control++;
	}
	});
	   
	   if(control==0){
		/* ver_vetana_informativa("FALTA AGREGAR DOCUMENTO(S) PARA GUARDAR") */
		return
	   }
	   
	   var accion = "";
	   /* if(controlacceso("INSERTARLICITACION","accion")==false){return;} */
		accion = "addImagenes";
	AbmCargarFotosCliente(accion,idabm);
}
function AbmCargarFotosCliente(accion,idAbmCliente){
	
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	
	var control=1
	$("tr[name=tdDetalleItemImagen]").each(function(i, elementohtml){
			
			if($(elementohtml).children('td[id="td_id_2"]').html()==""){
			var archivo=$(elementohtml).children('td[id="td_datos_1"]').html();
			datos.append("archivo"+control, archivo)
			
			var extension=$(elementohtml).children('td[id="td_datos_2"]').html();
			datos.append("ext"+control, extension)
			
			var descripcion=$(elementohtml).children('td[id="td_datos_4"]').html();
			datos.append("descripcion"+control, descripcion)
	   
			var fecha=$(elementohtml).children('td[id="td_datos_5"]').html();
			datos.append("fecha"+control, fecha)

			control=control+1;
			}
	   });
	
	
	 control=control-1;
	 console.log("Cantidad registro:"+control);
	 
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idclientefk", idAbmCliente)
	datos.append("totalregistro", control)
	
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
					
					buscarFotosCliente()
				}
				else {
				ver_vetana_informativa("Error inesperado",  "Lo sentimos, ha ocurrido un error", "error")
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function VerCargarFotosCliente(d){
	
	if(d == "2"){
		document.getElementById('divVistaDocumento').style.display = "none"
		document.getElementById("docVisor").setAttribute('src',"");
		let aumentados= document.getElementsByClassName("magnifier-class");
		aumentados.forEach(element => {
			element.remove();
		});
		return;
	}
	
	if(elementoimagenseleccionado == ""){
		ver_vetana_informativa("FALTO SELECCIONAR UN DOCUMENTO PARA VISUALIZAR")
		return;
	}
	
	if(d == "1"){
	document.getElementById('divVistaDocumento').style.display = ""
	if($(elementoimagenseleccionado).children('td[id="td_id_2"]').html()==""){
		document.getElementById("docVisor").setAttribute('src',$(elementoimagenseleccionado).children('td[id="td_datos_3"]').html());
		enableMagnifier(".magnifier-container");
	}else{
		document.getElementById("docVisor").setAttribute('src',$(elementoimagenseleccionado).children('td[id="td_datos_1"]').html());
		enableMagnifier(".magnifier-container");
	}
	
	}else{
		document.getElementById('divVistaDocumento').style.display = "none"
		document.getElementById("docVisor").setAttribute('src',"");
	}
	
}
function EliminarCargarFotosCliente() {
//Comprobar si hay algun elemento cargado en el div o de otra forma si exiten registros
	var control=0;
$("tr[name=tdDetalleItemImagen]").each(function(i, elementohtml){
control++;
});

//Si no exiten registros vaciar elementodetalleseleccionado
if(control == 0){
	elementoimagenseleccionado = ""
}
	
	//Comprobar si existen algun elemento seleccionado
	if(elementoimagenseleccionado == ""){
		ver_vetana_informativa("FALTO SELECCIONAR UN ARCHIVO PARA ELIMINAR")
		return;
	}
	
	var urlarchivo = $(elementoimagenseleccionado).children('td[id="td_datos_1"]').html()
	var iddocumento = $(elementoimagenseleccionado).children('td[id="td_id_2"]').html()
	var idcontrato = $(elementoimagenseleccionado).children('td[id="td_id_3"]').html()
	
	if(iddocumento != ""){
		EliminarArchivo(iddocumento,urlarchivo,idcontrato)
	}
	
	//Obtener la ID del registro
		var cod_table=$(elementoimagenseleccionado).children('td[id="td_id_1"]').html()
		$("table[id="+cod_table+"]").remove()
		
		
		//Restaurar los botones y vaciar elementodetalleseleccionado
		document.getElementById("btnEliminarImagen").style.backgroundColor = "#d5d3d3";
		document.getElementById("btnVerImagenCliente").style.backgroundColor = "#d5d3d3";
		elementoimagenseleccionado="";
		/* control = 0;
$("tr[name=tdDetalleItemDoc]").each(function(i, elementohtml){
if($(elementohtml).children('td[id="td_id_2"]').html()==""){
	control++;
}
});

if(control > 0){
	document.getElementById("btnGuardarDocumento").style="background-color:#d5d3d3"
} */
}
function EliminarArchivo(iddocumento,urldocumento,idcliente){
	verCerrarEfectoCargando("1")
	obtener_datos_user();
	
	let pos=urldocumento.indexOf("/");
	urldocumento = urldocumento.slice(pos+1)
	pos= urldocumento.indexOf("/")
	urldocumento = urldocumento.slice(pos)
	
	
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idcliente": idcliente,
		"iddocumento": iddocumento,
		"urldocumento": urldocumento,
		"funt": "eliminardocumento"
	};
	$.ajax({
		data: datos,
        url: "/GoodVentaAsisCap/php_system/abmclientes.php",
		type: "post",
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
verCerrarEfectoCargando("")
		},
		success: function (responseText) {
			verCerrarEfectoCargando("")
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					ver_vetana_informativa("SE HA ELIMINADO CORRECTAMENTE")
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function LimpiarCamposCargarFotosCliente(){
	document.getElementById("btnAddImagen").style.backgroundColor="#d5d3d3";
	document.getElementById("btnEliminarImagen").style.backgroundColor="#d5d3d3";
	document.getElementById("btnVerImagenCliente").style.backgroundColor="#d5d3d3";
	document.getElementById("inptDescripcionCargarFotosClientes").value=""
	document.getElementById("inptFechaCargarFotosCliente").value=""
	document.getElementById("text-carga").style.display=""
	document.getElementById("text-carga-2").style.display="none"
	elementoimagenseleccionado =""
	archivo="";
	extension = "";
	urlarchivopdf="";
}
function buscarFotosCliente(){
	
	document.getElementById("table_abm_imagen_clientes").innerHTML = ''
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idcliente": idAbmCliente,
		"funt": "buscarDocumentos"
	};
	$.ajax({
		data: datos,
        url: "/GoodVentaAsisCap/php_system/abmclientes.php",
		type: "post",
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
		manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_abm_imagen_clientes").innerHTML = ''
		},
		success: function (responseText) {
			
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_imagen_clientes").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_imagen_clientes").innerHTML = datos_buscados
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
COMPRAS DE CLIENTES
*/
var idVentaCuentaCliente="";
function ObtenerdatosCuentaCliente(datostr) {
	idVentaCuentaCliente = $(datostr).children('td[id="td_id"]').html();
	vercerrarvistacuentacliente("1")
	buscarproductoshistorialcliente()
	buscarcreditospagadocliente()
	buscarcreditospendientescliente()	
}
function buscarproductoshistorialcliente() {
	document.getElementById("table_vista_cliente_productos_comprados").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idVentaCuentaCliente,
		"funt": "productosCompradoscliente"
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
			document.getElementById("table_vista_cliente_productos_comprados").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_cliente_productos_comprados").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
					var datos_buscados = datos[2];
					document.getElementById("table_vista_cliente_productos_comprados").innerHTML = datos_buscados
					document.getElementById("inptNroFacturaCuentaCliente").value = datos[6]
					document.getElementById("inptTotalVentaCuentaCliente").value = datos[3]
					document.getElementById("inptTotalPagadoCuentaCliente").value = datos[4]
					document.getElementById("inptDeudaCuentaCliente").value = datos[5]



				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function buscarcreditospagadocliente() {
	document.getElementById("table_clientes_cuotas1").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idVentaCuentaCliente,
		"funt": "cuentasClientesCobrados"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmcreditos.php",
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
			document.getElementById("table_clientes_cuotas1").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_clientes_cuotas1").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {

					var datos_buscados = datos[2];
					document.getElementById("table_clientes_cuotas1").innerHTML = datos_buscados

				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function buscarcreditospendientescliente() {
	document.getElementById("table_clientes_cuotas2").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idVentaCuentaCliente,
		"funt": "cuentasClientesPendientes"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmcreditos.php",
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
			document.getElementById("table_clientes_cuotas2").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_clientes_cuotas2").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {

					var datos_buscados = datos[2];
					document.getElementById("table_clientes_cuotas2").innerHTML = datos_buscados


				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function vercerrarvistacuentacliente(d) {
	if (d == "1") {
		document.getElementById("divHistorialCuentaCliente").style.display=""
		document.getElementById("tdEfectoHistorialCuentaCliente").className="magictime vanishIn"
	} else {
		 document.getElementById("tdEfectoHistorialCuentaCliente").className="magictime vanishOut"
		$("div[id=divHistorialCuentaCliente]").fadeOut(500)
	}
}

function buscarClientePorCiVista(elementoLlamando, nombreElementoCedula, nombreElementoNombre, ventanaOrigen) {
    let cedula= "";
    let nombre= "";

    if (elementoLlamando.id == nombreElementoCedula) {
        cedula= document.getElementById(nombreElementoCedula).value;
    } else {
        nombre= document.getElementById(nombreElementoNombre).value;
    }

	verCerrarEfectoCargando("1");
    obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "obtenerCliente",
		"cedula": cedula,
		"nombre_persona": nombre,
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmclientes.php",
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
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
			verCerrarEfectoCargando("");
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
                if (Respuesta == true) {
                   const origenVistaCliente = (ventanaOrigen == "presupuesto" && vistaPresupuestoOrigen == "doctor") ? "presupuestoDoctor" : ventanaOrigen;
                   controlseleccvistacliente= origenVistaCliente;
                    if (datos[2].length == 1) {
                        let registro= datos[2][0];
                        idFkCliente = registro['cod_cliente'];
                        document.getElementById(nombreElementoCedula).value= registro['rut_cliente'] || registro['ci_cliente'];
                        document.getElementById(nombreElementoNombre).value= registro['nombre_persona'];
                        
                        var zonaCliente = "";
                        if (document.getElementById("inptZonaCliente")) {
                            zonaCliente = document.getElementById("inptZonaCliente").value;
                        }

                        var styleFondo = "";
                        if (registro['accesocredito'] == "Denegado") {
                            styleFondo = "background-color:#ff5722;color:#fff";
                        }

                        var tablaClienteFicticia = $(
                            "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>" +
                                "<tr class='tableRegistroSelec' id='trdatoClienteCi' onclick='obtenerdatosvistacliente(this)' style='" + styleFondo + "'>" +
                                    "<td id='td_id' style='display:none'>" + registro['cod_persona'] + "</td>" +
                                    "<td id='td_datos_2' style='width:10%'>" + registro['ci_cliente'] + "</td>" +
                                    "<td id='td_datos_13' style='width:10%'>" + registro['rut_cliente'] + "</td>" +
                                    "<td id='td_datos_1' style='width:10%'>" + registro['nombre_persona'] + "</td>" +
                                    "<td id='td_datos_10' style='display:none'>" + registro['zona'] + "</td>" +
                                    "<td id='td_datos_3' style='width:10%'>" + registro['direccion'] + "</td>" +
                                    "<td id='td_datos_4' style='width:10%'>" + registro['telefono'] + "</td>" +
                                    "<td id='td_datos_5' style='display:none'>" + registro['email'] + "</td>" +
                                    "<td id='td_datos_6' style='display:none'>" + registro['Calificacion'] + "</td>" +
                                    "<td id='td_datos_7' style='display:none'>" + registro['whapp'] + "</td>" +
                                    "<td id='td_datos_8' style='display:none'>" + registro['estado'] + "</td>" +
                                    "<td id='td_datos_9' style='display:none'>" + registro['idzonaFk'] + "</td>" +
                                    "<td id='td_datos_11' style='display:none'>" + registro['foto1'] + "</td>" +
                                    "<td id='td_datos_12' style='display:none'>" + registro['foto2'] + "</td>" +
                                    "<td id='td_datos_14' style='display:none'>" + registro['accesocredito'] + "</td>" +
                                    "<td id='td_datos_15' style='display:none'></td>" +
                                    "<td id='td_datos_16' style='display:none'>" + registro['lugardetrabajo'] + "</td>" +
                                    "<td id='td_datos_17' style='display:none'>" + registro['salario'] + "</td>" +
                                    "<td id='td_datos_18' style='display:none'>" + registro['antiguedad'] + "</td>" +
                                    "<td id='td_datos_19' style='display:none'>" + registro['teleftrab1'] + "</td>" +
                                    "<td id='td_datos_20' style='display:none'>" + registro['teleftrab2'] + "</td>" +
                                    "<td id='td_datos_21' style='display:none'>" + registro['direcciontrab'] + "</td>" +
                                    "<td id='td_datos_22' style='display:none'>" + registro['fechanac'] + "</td>" +
                                "</tr>" +
                            "</table>"
                        );
                        elementoCliente = tablaClienteFicticia.find("tr")[0];
                        EnviarClienteDesde();
                    } else if (datos[2].length == 0) {
                        // Abre la ventana para agregar un nuevo cliente
                        limpiarcamposCliente();
						verCerrarVentanaAbmCliente(true, true);
					} else {
						document.getElementById('inptBuscarVistaCliente1').value= document.getElementById(nombreElementoCedula).value;
						document.getElementById('inptBuscarVistaCliente3').value= (document.getElementById(nombreElementoNombre).value != "CLIENTE OCASIONAL") ? document.getElementById(nombreElementoNombre).value : '';
						vercerrarvistacliente("1", origenVistaCliente);
					}
				} else {
                    ver_vetana_informativa(datos[2], datos[3], "advertencia");
                }
				verCerrarEfectoCargando("");
			} catch (error) {
				verCerrarEfectoCargando("");
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function verificarDatosCliente(solo_basicos) {
	const nombre= document.getElementById('inptNombreApellidoCliente').value;
	const nroDoc= document.getElementById('inptNroDocCliente').value;
	const whapp= document.getElementById('inptNrowhatsappCliente').value;
	if (solo_basicos) {
		if (
			nombre == "" ||
			nroDoc == "" ||
			whapp == "" ||
			idFKZona == "" || idFKZona == 0
		) {
			document.getElementById('divAbmCliente').style.display= "";
			document.getElementById('divAbmCliente2').style.display= "";
			document.getElementById('divAbmCliente1').style.display= "none";

			$("#divAbmCliente2 .abm-cliente-datos-extra").hide();
			document.getElementById('divAbmCliente2').style.width= "850px";

			ver_vetana_informativa("Faltan datos del cliente", "Se debe completar los datos del cliente.", "advertencia");
			return false;
		} else {
			return true
		}
	} else {
		if (
			nombre == "" ||
			nroDoc == "" ||
			whapp == "" ||
			idFKZona == "" || idFKZona == 0 ||
			document.getElementById('inptDireccionCliente').value == ""
		) {
			document.getElementById('divAbmCliente').style.display= "";
			document.getElementById('divAbmCliente2').style.display= "";
			document.getElementById('divAbmCliente1').style.display= "none";

			$("#divAbmCliente2 .abm-cliente-datos-extra").hide();
			document.getElementById('divAbmCliente2').style.width= "850px";

			ver_vetana_informativa("Faltan datos del cliente", "Se debe completar los datos del cliente.", "advertencia");
			return false;
		} else {
			return true
		}
	}
}