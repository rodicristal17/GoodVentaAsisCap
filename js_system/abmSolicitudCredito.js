/*
ABM Solicitud
*/
function verCerrarAbmsolicotud(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmSolicitudCredito").style.display==""){
	document.getElementById("divMinimizadoSolicitudCredito").style.display="none"
	document.getElementById("tdEfectoAbmSolicitudCredito").className="magictime vanishOut"
	$("div[id=divAbmSolicitudCredito]").fadeOut(500);	
		}else{		
	// if(controlacceso("VERLISTADODECLIENTES","accion")==false){return;}
		document.getElementById("divAbmSolicitudCredito").style.display=""
	document.getElementById("tdEfectoAbmSolicitudCredito").className="magictime slideDownReturn"
		
		
	}

}


function verCerrarVentanaAbmSolicitudCredito(d, l) {	
	if (d == "1") {
		if (l == "1") {
			// if(controlacceso("INSERTARLISTADODECLIENTES","accion")==false){return;}
			limpiarcampossolicitudCredito()
		}
		$("div[id=divAbmSolicitudCredito2]").fadeIn(250)
		document.getElementById('divAbmSolicitudCredito1').style.display = "none"
	} else {
		$("div[id=divAbmSolicitudCredito1]").fadeIn(250)
		document.getElementById('divAbmSolicitudCredito2').style.display = "none"
	}
}

function checkSolicitudCredito(d){	
	if(d=="2"){
		document.getElementById('inptSeleccSolicitudCredito1').checked=false
		document.getElementById('inptSeleccSolicitudCredito2').checked=true
		document.getElementById('inptBuscarsolicitudCredito1').value = "";
	    document.getElementById('inptBuscarsolicitudCredito2').value = "";	
	}else{		
		document.getElementById('inptSeleccSolicitudCredito1').checked=true
		document.getElementById('inptSeleccSolicitudCredito2').checked=false
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarsolicitudCredito1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarsolicitudCredito2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}

function AnhadirMasReferenciasSolicitudCredito(){
	var inptMasRefTelefSolicitudCredito=document.getElementById("inptMasRefTelefSolicitudCredito").value
	var inptMasRefDireccionSolicitudCredito=document.getElementById("inptMasRefDireccionSolicitudCredito").value
	var inptMasRefReferenciaSolicitudCredito=document.getElementById("inptMasRefReferenciaSolicitudCredito").value
	var inptMasRefObservacionSolicitudCredito=document.getElementById("inptMasRefObservacionSolicitudCredito").value
	var inptTipoRefSolicitudCredito=document.getElementById("inptTipoRefSolicitudCredito").value
	var inptObsRefSolicitudCredito=document.getElementById("inptObsRefSolicitudCredito").value
	
	if(inptTipoRefSolicitudCredito==""){
		ver_vetana_informativa("FALTO SELECCIONAR TIPOS DE REFERENCIA")
		return false;
			}
	
var pagina="<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>"
+"<tr id='tbSelecRegistro' onclick='obtenerdatosRefSolicitudCredito(this)'  name='tdMasReferenciasSolicitudCredito'>"
+"<td  id='td_datos_1' style='width:10%'>"+inptMasRefObservacionSolicitudCredito+"</td>"
+"<td  id='td_datos_2' style='width:10%;'>"+inptMasRefTelefSolicitudCredito+"</td>"
+"<td  id='td_datos_3' style='width:10%'>"+inptMasRefDireccionSolicitudCredito+"</td>"
+"<td  id='td_datos_4' style='width:10%'>"+inptMasRefReferenciaSolicitudCredito+"</td>"
+"<td  id='td_datos_5' style='width:10%'>"+inptTipoRefSolicitudCredito+"</td>"
+"<td  id='td_datos_6' style='display:none'>"+inptObsRefSolicitudCredito+"</td>"
+"</tr>"
+"</table>"
document.getElementById("table_mas_referenciasSolicitudCredito").innerHTML+=pagina;
		LimpiarSolicitudCredito()
}

function LimpiarSolicitudCredito(){
	document.getElementById('inptMasRefObservacionSolicitudCredito').value="";
	document.getElementById('inptMasRefReferenciaSolicitudCredito').value="";
	document.getElementById('inptMasRefDireccionSolicitudCredito').value="";
	document.getElementById('inptMasRefTelefSolicitudCredito').value="";
	document.getElementById('inptTipoRefSolicitudCredito').value="";
	elementoAddRefSolicitudCredito="";
}

var elementoAddRefSolicitudCredito="";
function obtenerdatosRefSolicitudCredito(datostr){
	 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });
    datostr.className='tableRegistroSelec'
	document.getElementById('inptMasRefDireccionSolicitudCredito').value=$(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptMasRefReferenciaSolicitudCredito').value=$(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptMasRefTelefSolicitudCredito').value=$(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptMasRefObservacionSolicitudCredito').value=$(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptTipoRefSolicitudCredito').value=$(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptObsRefSolicitudCredito').value=$(datostr).children('td[id="td_datos_6"]').html();
	
	document.getElementById('inptObsRefSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_6"]').html();
	
	elementoAddRefSolicitudCredito=datostr;
		document.getElementById("btnAddRefSolicitudCredito1").style.display="none"
		document.getElementById("btnAddRefSolicitudCredito2").style.display=""
		document.getElementById("btnAddRefSolicitudCredito3").style.display=""
		document.getElementById("btnAddRefSolicitudCredito4").style.display=""
}




function editarRefSolicitudCredito(){
	
	$(elementoAddRefSolicitudCredito).children('td[id="td_datos_3"]').text(document.getElementById('inptMasRefDireccionSolicitudCredito').value)
	$(elementoAddRefSolicitudCredito).children('td[id="td_datos_4"]').text(document.getElementById('inptMasRefReferenciaSolicitudCredito').value)
	$(elementoAddRefSolicitudCredito).children('td[id="td_datos_2"]').text(document.getElementById('inptMasRefTelefSolicitudCredito').value)
	$(elementoAddRefSolicitudCredito).children('td[id="td_datos_1"]').text(document.getElementById('inptMasRefObservacionSolicitudCredito').value)
	$(elementoAddRefSolicitudCredito).children('td[id="td_datos_5"]').text(document.getElementById('inptTipoRefSolicitudCredito').value)
	
	$(elementoAddRefSolicitudCredito).children('td[id="td_datos_6"]').text(document.getElementById('inptObsRefSolicitudCredito').value)
	
	
	document.getElementById("btnAddRefSolicitudCredito1").style.display=""
		document.getElementById("btnAddRefSolicitudCredito2").style.display="none"
		document.getElementById("btnAddRefSolicitudCredito3").style.display="none"
		document.getElementById("btnAddRefSolicitudCredito4").style.display="none"
		LimpiarRefSolicitudCredito()
}
function EliminarRefSolicitudCredito(){
	
	$(elementoAddRefSolicitudCredito).remove()
		document.getElementById("btnAddRefSolicitudCredito1").style.display=""
		document.getElementById("btnAddRefSolicitudCredito2").style.display="none"
		document.getElementById("btnAddRefSolicitudCredito3").style.display="none"
		document.getElementById("btnAddRefSolicitudCredito4").style.display="none"
	
		LimpiarRefSolicitudCredito()
}
function CancelarRefSolicitudCredito(){
		document.getElementById("btnAddRefSolicitudCredito1").style.display=""
		document.getElementById("btnAddRefSolicitudCredito2").style.display="none"
		document.getElementById("btnAddRefSolicitudCredito3").style.display="none"
		document.getElementById("btnAddRefSolicitudCredito4").style.display="none"
		LimpiarRefSolicitudCredito()
}


function LimpiarRefSolicitudCredito(){
	document.getElementById('inptMasRefDireccionSolicitudCredito').value="";
	document.getElementById('inptMasRefReferenciaSolicitudCredito').value="";
	document.getElementById('inptMasRefTelefSolicitudCredito').value="";
	document.getElementById('inptMasRefObservacionSolicitudCredito').value="";
	document.getElementById('inptTipoRefSolicitudCredito').value="";
	document.getElementById('inptObsRefSolicitudCredito').value="";
	elementoAddRefSolicitudCredito="";
}



function buscarvistaventaSolicitud() {
	var buscador = document.getElementById('inptRefNombreProducto').value
	var local = document.getElementById("inptlocalVenta").value;
	document.getElementById("table_vista_ProDuc_Solicitud_Credito").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"local": local,
		"funt": "buscarvistaventaSolicitud"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmproductos.php",
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
			document.getElementById("table_vista_ProDuc_Solicitud_Credito").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_ProDuc_Solicitud_Credito").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				if(datos_buscados!=""){
				document.getElementById("table_vista_ProDuc_Solicitud_Credito").innerHTML = datos_buscados
			  // $("td[id=td_datos_precio_contado]").each(function(i, elementohtml){  
				// elementohtml.style.display="none"
	          // });
			  // $("td[id=td_datos_precios_creditos]").each(function(i, elementohtml){
                // elementohtml.style.display=""
			  // });  
	   
					document.getElementById('btnADDProductoSolicitudCredito').focus();
				}else{
					ver_vetana_informativa("PRODUCTO NO ECONTRADO")
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




function anhadirProductoSolicitudCredito(){	
	
	var inptRefCodProducto = document.getElementById('inptRefCodProducto').value
	var inptRefNombreProducto = document.getElementById('inptRefNombreProducto').value
	var inptRefCantidadProducto = document.getElementById('inptRefCantidadProducto').value
	var inptRefproductoPrecio = document.getElementById('inptRefproductoPrecio').value
	
	var inpTPrecioSolicitud = document.getElementById('inpTPrecioSolicitud').value
	// $(document).ready(function(){
		// var id = $('#inpTPrecioSolicitud').val();  
                                       
		// alert(id);
		// });
	
	var select = document.getElementById("inpTPrecioSolicitud"); 
	var valor = select.options[select .selectedIndex].id;



	if(inptRefCantidadProducto<=0|| inptRefCantidadProducto==""){
				ver_vetana_informativa("FAVOR AGREGAR CANTIDAD")
				return false;
		}
	

	var CuotaNro =$("select[id=inpTSeleccCosto]").children(":selected").attr("id")
	if (idFkProducto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO")
		return false;
	}
	
	var nroid=Math.floor((Math.random() * 1000) + 1);
	var pagina="<table id='tdDetalleVenta_"+nroid+"' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>"
+"<tr id='tbSelecRegistro' onclick='obtenerdatosProductoCredito(this)'  name='tdDetalleSolicitudCredito'>"
+"<td id='td_id_1' style='display:none'>"+idFkProducto+"</td>"
+"<td  id='td_datos_1' style='width:20%'>"+inptRefCodProducto+"</td>"
+"<td  id='td_datos_2' style='width:40%;'>"+inptRefNombreProducto+"</td>"
+"<td  id='td_datos_3' style='width:10%'>"+inptRefCantidadProducto+"</td>"
+"<td  id='td_datos_4' style='width:20%'>"+inptRefproductoPrecio+"</td>"
+"<td  id='td_datos_5' style='width:10%'>"+valor+"</td>"
+"</tr>"
+"</table>"
document.getElementById("table_Solicitud_Credito_Producto").innerHTML+=pagina;
var totalVenta=0;

var cuotas="";

$("tr[name=tdDetalleSolicitudCredito]").each(function(i, elementohtml){

var cantidad=$(elementohtml).children('td[id="td_datos_3"]').html();
var precio=$(elementohtml).children('td[id="td_datos_4"]').html();
precio=QuitarSeparadorMilValor(precio)

totalVenta=Number(totalVenta)+(Number(precio) * Number(cantidad) )

cuotas = totalVenta / valor
});
 


document.getElementById("inptTotalSolicitudCredito").innerHTML="<p>"+separadordemilesnumero(totalVenta) + "</p><br> <p style='font-size: 17px; margin-top: -20px;' >"+valor+" * "+separadordemilesnumero(Math.round(cuotas))+"</p> ";


document.getElementById('inptRefCodProducto').value = ""
document.getElementById('inptRefCantidadProducto').value = ""
document.getElementById('inptRefNombreProducto').value = ""
document.getElementById('inptRefproductoPrecio').value = ""
document.getElementById('inpTSeleccCosto').innerHTML = ""
idFkProducto = ""

}



var elementoAddProductoCredito="";
function obtenerdatosProductoCredito(datostr){
	 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });

    datostr.className='tableRegistroSelec'
	elementoAddProductoCredito=datostr;

		document.getElementById("btnAddCredito_Producto3").style.display=""
		document.getElementById("btnAddCredito_Producto4").style.display=""
}

function EliminarCredito_Producto(){
	
	$(elementoAddProductoCredito).remove()

		document.getElementById("btnAddCredito_Producto3").style.display="none"
		document.getElementById("btnAddCredito_Producto4").style.display="none"
		
		calcularTotalSolicitudCredito()

	
		
}



function calcularTotalSolicitudCredito(){
	
	var totalVenta=0;
$("tr[name=tdDetalleSolicitudCredito]").each(function(i, elementohtml){

var cantidad=$(elementohtml).children('td[id="td_datos_3"]').html();
var precio=$(elementohtml).children('td[id="td_datos_4"]').html();
precio=QuitarSeparadorMilValor(precio)

totalVenta=Number(totalVenta)+(Number(precio) * Number(cantidad) )

});
 


document.getElementById("inptTotalSolicitudCredito").innerHTML=separadordemilesnumero(totalVenta);
}
function CancelarCredito_Producto(){

		document.getElementById("btnAddCredito_Producto3").style.display="none"
		document.getElementById("btnAddCredito_Producto4").style.display="none"
	
}



function seleccionarsolicitudCredito(datos) {
	if($("select[id=inpTPrecioSolicitud]").children(":selected").attr("name")!=undefined){
	document.getElementById("inptRefproductoPrecio").value = datos.value

	}
}

function minimizarsolicitudCredito(){
document.getElementById("tdEfectoAbmSolicitudCredito").className="magictime slideDown"
	$("div[id=divAbmSolicitudCredito]").fadeOut(500);	
	document.getElementById("divMinimizadoSolicitudCredito").style.display=""
}


function limpiarcampossolicitudCredito(){
	document.getElementById('inpVentaSolicitudCredito').innerHTML=""
	document.getElementById('inptNombreSolicitudCredito').value="";
	document.getElementById('inptNroDocSolicitudCredito').value="";
	document.getElementById('inptNroRucSolicitudCredito').value="";
	document.getElementById('inptNroTelefSolicitudCredito').value="";
	document.getElementById('inptNrowhatsappSolicitudCredito').value="";
	document.getElementById('inptFechaNacSolicitudCredito').value="";
	document.getElementById('inptLugrarTrabajoSolicitudCredito').value="";
	document.getElementById('inptDireccionTrabajoSolicitudCredito').value="";
	document.getElementById('inptSalarioSolicitudCredito').value="";
	document.getElementById('inptAntiguedadSolicitudCredito').value="";
	document.getElementById('inptNroTelefTrabajoSolicitudCredito1').value="";
	document.getElementById('inptNroTelefTrabajoSolicitudCredito2').value="";
	document.getElementById('inptDireccionSolicitudCredito').value="";
	document.getElementById('inptReferenciaSolicitudCredito').value="";
	document.getElementById('inptZonaSolicitudCredito').value="";
	document.getElementById('inptMasRefTelefSolicitudCredito').value="";
	document.getElementById('inptMasRefDireccionSolicitudCredito').value="";
	document.getElementById('inptMasRefReferenciaSolicitudCredito').value="";
	document.getElementById('inptMasRefObservacionSolicitudCredito').value="";
	document.getElementById('inptObservacionSolicitudCredito').value="";
	document.getElementById('inptTipoRefSolicitudCredito').value="";
	document.getElementById('table_mas_referenciasSolicitudCredito').innerHTML="";
	document.getElementById('inptEstadoCliente').value="Activo";
	document.getElementById("btnAddRefSolicitudCredito1").style.display=""
	document.getElementById("btnAddRefSolicitudCredito2").style.display="none"
	document.getElementById("btnAddRefSolicitudCredito3").style.display="none"
	document.getElementById("btnAddRefSolicitudCredito4").style.display="none"
	document.getElementById('inptGaranteSolicitudCredito').value="SIN GARANTE";
	document.getElementById('inptObsRefSolicitudCredito').value="";
	document.getElementById('inptObserbacionTrabajoSolicitudCredito2').value="";
	
	document.getElementById('table_vista_ProDuc_Solicitud_Credito').innerHTML="";
	document.getElementById('inptTotalSolicitudCredito').innerHTML="";
	document.getElementById('inptRefCodProducto').value="";
	document.getElementById('inptRefNombreProducto').value="";
	document.getElementById('inptRefproductoPrecio').value="";
	document.getElementById('inpTPrecioSolicitud').innerHTML="";
	document.getElementById('inptRefCantidadProducto').value="";
	document.getElementById('table_Solicitud_Credito_Producto').innerHTML="";
	document.getElementById('btnGuardarSolicitudCredito').value="Guardar Datos"
	
	 document.getElementById('btnEditarSolicitudCredito').style.backgroundColor="#b7b7b7";
  document.getElementById('btnEliminarSolicitudCredito').style.backgroundColor="#b7b7b7";
	

	
	
	idFkCliente="";
	cod_garanteFK="6";
	idFKZona="";
	idSolicitudCredito="";
	document.getElementById('btnEditarClientes').style.backgroundColor="#b7b7b7";
   
}


var cod_garanteFK="";
var idSolicitudCredito ="";

function verificarcamposSolicitudCredito(){
	var inptEstadoSolicitudCredito=document.getElementById('inptEstadoSolicitudCredito').value
	var inptNombreSolicitudCredito=document.getElementById('inptNombreSolicitudCredito').value
	var inptObservacionSolicitudCredito=document.getElementById('inptObservacionSolicitudCredito').value
	

  if(inptNombreSolicitudCredito==""){
	ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL CLIENTE","#")
	  return false;
  }

  if(idFKZona==""){
	ver_vetana_informativa("FALTO SELECCIONAR UNA ZONA","#")
	  return false;
  }
  
  if(idFkCliente==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN CLIENTE.","#")
	  return false;
  }
 
 
 
  var accion="";
  if(idSolicitudCredito!=""){
	 
	  accion="editar";
	 	// if(controlacceso("EDITARLISTADODECLIENTES","accion")==false){return;}
  }else{
	   accion="nuevo";
  }
  
  SolicitudCredito(inptEstadoSolicitudCredito,inptObservacionSolicitudCredito,accion);
}
function  SolicitudCredito(estado,observacion,accion){
	verCerrarEfectoCargando("1")
	

	  var datos = new FormData();
			obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("idAbm" , idSolicitudCredito)
			 datos.append("estado" , estado)
			 datos.append("idAbmCliente" , idFkCliente)
			 datos.append("cod_garanteFK" , cod_garanteFK)
			 datos.append("cod_cobradorFK" , idFkCobrador)
			 datos.append("cod_localFK" , cod_localFKUSer)
			 datos.append("observacion" , observacion)
					
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmSolicitudCredito.php",
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
				
				idSolicitudCredito = datos["2"];
				document.getElementById('btnGuardarSolicitudCredito').value="Editar Datos"
				buscarSolicitudCredito()
			    verificarcamposClienteSolicitudCredito()	
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


function verificarcamposClienteSolicitudCredito(){
	var inptNroTelefSolicitudCredito=document.getElementById('inptNroTelefSolicitudCredito').value
	var inptNrowhatsappSolicitudCredito=document.getElementById('inptNrowhatsappSolicitudCredito').value
	var inptLugrarTrabajoSolicitudCredito=document.getElementById('inptLugrarTrabajoSolicitudCredito').value
	var inptDireccionTrabajoSolicitudCredito=document.getElementById('inptDireccionTrabajoSolicitudCredito').value
	var inptSalarioSolicitudCredito=document.getElementById('inptSalarioSolicitudCredito').value
	var inptAntiguedadSolicitudCredito=document.getElementById('inptAntiguedadSolicitudCredito').value
	var inptNroTelefTrabajoSolicitudCredito1=document.getElementById('inptNroTelefTrabajoSolicitudCredito1').value
	var inptNroTelefTrabajoSolicitudCredito2=document.getElementById('inptNroTelefTrabajoSolicitudCredito2').value
	var inptDireccionSolicitudCredito=document.getElementById('inptDireccionSolicitudCredito').value
	var inptReferenciaSolicitudCredito=document.getElementById('inptReferenciaSolicitudCredito').value
	var estado=document.getElementById('inptEstadoSolicitudCredito').value
	var obsTrabajo=document.getElementById('inptObserbacionTrabajoSolicitudCredito2').value


	if(idFkCliente==""){
	ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL CLIENTE","#")
	  return false;
  }

  if(idFKZona==""){
	ver_vetana_informativa("FALTO SELECCIONAR UNA ZONA","#")
	  return false;
  }
 
 
 
  var accion="";
  if(idFkCliente!=""){
	  accion="EditarCliente";
	 	// if(controlacceso("EDITARLISTADODECLIENTES","accion")==false){return;}
  }
  
  AbmClienteSolicitudCredito(inptNroTelefSolicitudCredito,inptNrowhatsappSolicitudCredito,inptLugrarTrabajoSolicitudCredito,inptDireccionTrabajoSolicitudCredito,inptSalarioSolicitudCredito,inptAntiguedadSolicitudCredito,inptNroTelefTrabajoSolicitudCredito1,inptNroTelefTrabajoSolicitudCredito2,inptDireccionSolicitudCredito,inptReferenciaSolicitudCredito,idFKZona,idFkCliente,estado,obsTrabajo,accion);
}


function  AbmClienteSolicitudCredito(nroTelefono,nroWhatsapp,lugarTrabajo,dereccionTrabajo,salario,antiguedad,nrotelefonoTrabajo,nroTelefonoEncargado,direccionSolicitud,referencia,idzonaFk,cod_persona,estado,obsTrabajo,accion){
	verCerrarEfectoCargando("1")
	

	  var datos = new FormData();
			obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("cod_persona" , cod_persona)
			 datos.append("idzonaFk" , idzonaFk)
			 datos.append("direccion" , direccionSolicitud)
			 datos.append("telefono" , nroTelefono)
			 datos.append("email" , referencia)//Sirve para la referencia
			 datos.append("whapp" , nroWhatsapp)
				datos.append("lugardetrabajo", lugarTrabajo)		
				datos.append("direcciontrab", dereccionTrabajo)		
				datos.append("salario", salario)		
				datos.append("antiguedad", antiguedad)		
				datos.append("teleftrab1", nrotelefonoTrabajo)		
				datos.append("teleftrab2", nroTelefonoEncargado)
				datos.append("obsTrabajo", obsTrabajo)
			datos.append("estado", estado)				
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmSolicitudCredito.php",
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
				
				idFkCliente = datos["2"];
				
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
				abmmasreferenciasSolicitudCliente(idFkCliente)
				abmProductoSolicitudCredito(idSolicitudCredito)				
				
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


function  abmmasreferenciasSolicitudCliente(idcliente){
	  var datos = new FormData();
	var control=1;
	$("tr[name=tdMasReferenciasSolicitudCredito]").each(function(i, elementohtml){
	
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
	
	var obs=$(elementohtml).children('td[id="td_datos_6"]').html();
    datos.append("obs"+control, obs)
	
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
			url: "/GoodVentaAsisCap/php_system/abmSolicitudCredito.php",
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
			
				buscarmasreferenciasSolicitudCredito(idcliente)
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
function buscarmasreferenciasSolicitudCredito(idcliente){
		 document.getElementById("table_mas_referenciasSolicitudCredito").innerHTML=paginacargando
			obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idcliente,
			"funt": "buscarmasreferencias"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmSolicitudCredito.php",
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
			document.getElementById("table_mas_referenciasSolicitudCredito").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_mas_referenciasSolicitudCredito").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
		   var datos_buscados=datos[2];		 
			document.getElementById("table_mas_referenciasSolicitudCredito").innerHTML=datos_buscados	
			
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

//////////////////////////////////////////////////////////////////////////

function  abmProductoSolicitudCredito(idSolicitudCredito){
	  var datos = new FormData();
	var control=1;
	$("tr[name=tdDetalleSolicitudCredito]").each(function(i, elementohtml){
	
	var cod_Producto=$(elementohtml).children('td[id="td_id_1"]').html();
    datos.append("cod_Producto"+control, cod_Producto)
	
	var cantidad=$(elementohtml).children('td[id="td_datos_3"]').html();
    datos.append("cantidad"+control, cantidad)

	
	var precio=$(elementohtml).children('td[id="td_datos_4"]').html();
    datos.append("precio"+control, precio)
	
	var cuotas=$(elementohtml).children('td[id="td_datos_5"]').html();
    datos.append("cuotas"+control, cuotas)
	
	control=control+1;	
	
	
	// alert(cod_Producto)
	
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
			 datos.append("funt", "addProductoCredito")
			 datos.append("idSolicitudCredito" , idSolicitudCredito)
			  datos.append("totalCargado" , control)
	
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmSolicitudCredito.php",
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
			buscarProductoSolicitudCredito(idSolicitudCredito)
				
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
function buscarProductoSolicitudCredito(idSolicitudCredito){
		 document.getElementById("table_Solicitud_Credito_Producto").innerHTML=paginacargando
			obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idSolicitudCredito,
			"funt": "buscarProductoSolicitud"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmSolicitudCredito.php",
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
			document.getElementById("table_Solicitud_Credito_Producto").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_Solicitud_Credito_Producto").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
		   var datos_buscados=datos[2];		 
			document.getElementById("table_Solicitud_Credito_Producto").innerHTML=datos_buscados	
			var totalVenta=datos[3];	
			
				document.getElementById("inptTotalSolicitudCredito").innerHTML=separadordemilesnumero(totalVenta);
			
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



function buscarSolicitudCredito(){
	
	var fecha1 = document.getElementById('inptBuscarsolicitudCredito1').value
	var fecha2 = document.getElementById('inptBuscarsolicitudCredito2').value
	var local = document.getElementById('inptlocalsolicitudCredito').value
	var zona= document.getElementById("inptBuscarAbmsolicitudCredito4").value	
	var cliente= document.getElementById("inptBuscarAbmsolicitudCredito3").value
	var documento= document.getElementById("inptBuscarAbmsolicitudCredito2").value
	var estado= document.getElementById("inptBuscarAbmsolicitudCredito5").value
	var vendedor= document.getElementById("inptBuscarAbmsolicitudCredito6").value

	if(document.getElementById('inptSeleccSolicitudCredito1').checked==true){
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
	
	   document.getElementById("inptRegistroNrosolicitudCredito").value =""
	   document.getElementById("table_abm_solicitudCredito").innerHTML=paginacargando
			obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"fecha1": fecha1,
			"fecha2": fecha2,
			"local": local,
			"zona": zona,
			"cliente": cliente,
			"documento": documento,
			"estado": estado,
			"vendedor": vendedor,
			"funt": "buscarSolicitudCredito"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmSolicitudCredito.php",
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
			document.getElementById("table_abm_solicitudCredito").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_abm_solicitudCredito").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
		   var datos_buscados=datos[2];		 
			document.getElementById("table_abm_solicitudCredito").innerHTML=datos_buscados	
			 document.getElementById("inptRegistroNrosolicitudCredito").value =datos[3];	
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


function obtenerdatosSolicitudCredito(datostr){	
		 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });
	   
	   limpiarcampossolicitudCredito()
    datostr.className='tableRegistroSelec'
	document.getElementById('inptNombreSolicitudCredito').value=$(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptNroDocSolicitudCredito').value=$(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNroRucSolicitudCredito').value=$(datostr).children('td[id="rut_cliente"]').html();
	document.getElementById('inptNroTelefSolicitudCredito').value=$(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptNrowhatsappSolicitudCredito').value=$(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptFechaNacSolicitudCredito').value=$(datostr).children('td[id="td_datos_17"]').html();
	document.getElementById('inptDireccionSolicitudCredito').value=$(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptReferenciaSolicitudCredito').value=$(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptZonaSolicitudCredito').value=$(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptGaranteSolicitudCredito').value=$(datostr).children('td[id="td_datos_18"]').html();
	document.getElementById('inptLugrarTrabajoSolicitudCredito').value=$(datostr).children('td[id="td_datos_11"]').html();
	document.getElementById('inptDireccionTrabajoSolicitudCredito').value=$(datostr).children('td[id="td_datos_16"]').html()
	document.getElementById('inptSalarioSolicitudCredito').value=$(datostr).children('td[id="td_datos_12"]').html()
	document.getElementById('inptAntiguedadSolicitudCredito').value=$(datostr).children('td[id="td_datos_13"]').html()
	document.getElementById('inptNroTelefTrabajoSolicitudCredito1').value=$(datostr).children('td[id="td_datos_14"]').html()
	document.getElementById('inptNroTelefTrabajoSolicitudCredito2').value=$(datostr).children('td[id="td_datos_15"]').html()
	document.getElementById('inptObservacionSolicitudCredito').value=$(datostr).children('td[id="td_datos_23"]').html()
	document.getElementById('inptRegistroSeleccsolicitudCredito').value=$(datostr).children('td[id="td_datos_3"]').html()
	document.getElementById('inptEstadoSolicitudCredito').value=$(datostr).children('td[id="td_datos_9"]').html();
	document.getElementById('inptObserbacionTrabajoSolicitudCredito2').value=$(datostr).children('td[id="td_datos_24"]').html();
	
	var estadoSolicitud=$(datostr).children('td[id="td_datos_9"]').html()

	if(estadoSolicitud=="FINALIZADO"){
		document.getElementById('inpVentaSolicitudCredito').innerHTML=$(datostr).children('td[id="td_datos_3"]').html()+"/"+$(datostr).children('td[id="td_datos_22"]').html()
	}else{
		document.getElementById('inpVentaSolicitudCredito').innerHTML=""
	}
	

	idFkCliente= $(datostr).children('td[id="td_datos_21"]').html();
	idFKZona= $(datostr).children('td[id="td_datos_25"]').html();
   idSolicitudCredito= $(datostr).children('td[id="td_id"]').html();
   

   
	buscarmasreferenciasSolicitudCredito(idFkCliente);
	buscarProductoSolicitudCredito(idSolicitudCredito)
  document.getElementById('btnGuardarSolicitudCredito').value="Editar datos";
  document.getElementById('btnEditarSolicitudCredito').style.backgroundColor="";
  document.getElementById('btnEliminarSolicitudCredito').style.backgroundColor="#f4473a";
  
  
  
  	document.getElementById('inptNombreSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptNroDocSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNroRucSolicitudCreditoVista').value=$(datostr).children('td[id="rut_cliente"]').html();
	document.getElementById('inptNroTelefSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptNrowhatsappSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptFechaNacSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_17"]').html();
	document.getElementById('inptDireccionSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptReferenciaSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptZonaSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptGaranteSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_18"]').html();
	document.getElementById('inptLugrarTrabajoSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_11"]').html();
	document.getElementById('inptDireccionTrabajoSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_16"]').html()
	document.getElementById('inptSalarioSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_12"]').html()
	document.getElementById('inptAntiguedadSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_13"]').html()
	document.getElementById('inptNroTelefTrabajoSolicitudCredito1Vista').value=$(datostr).children('td[id="td_datos_14"]').html()
	document.getElementById('inptNroTelefTrabajoSolicitudCredito2Vista').value=$(datostr).children('td[id="td_datos_15"]').html()
	document.getElementById('inptObservacionSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_23"]').html()
	document.getElementById('inptEstadoSolicitudCreditoVista').value=$(datostr).children('td[id="td_datos_9"]').html();
	document.getElementById('inptObserbacionTrabajoSolicitudCredito2Vista').value=$(datostr).children('td[id="td_datos_24"]').html();
  
  
  
  
  
	
}

function verVentanaEditarsolicitudCredito() {
	// if(controlacceso("EDITARLISTADODECLIENTES","accion")==false){return;}
	if (idFkCliente == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		return;
	}
	verCerrarVentanaAbmSolicitudCredito("1", "2")
}


function vercerrarvistaSolicitudCredito(d, ventana) {

	if (d == "1") {
		document.getElementById("divVistaSolicitudCredito").style.display = ""
		 document.getElementById("tdEfectoVistaSolicitudCredito").className="magictime slideLeftReturn"
		buscarvistaSolicitudCredito();
	} else {
		document.getElementById("tdEfectoVistaSolicitudCredito").className="magictime slideRight"
		$("div[id=divVistaSolicitudCredito]").fadeOut(500)
	}

}


function buscarvistaSolicitudCredito() {
	var buscador = document.getElementById('inptSolicitudCredito').value
	document.getElementById("table_vista_SoliCredito").innerHTML = paginacargando

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"codlocal": cod_localFKUSer,
		"funt": "buscarvista"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmSolicitudCredito.php",
		type: "post",
		beforeSend: function () {


		},
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
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_vista_SoliCredito").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_SoliCredito").innerHTML = ''
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

					var datos_buscados = datos[2];

					document.getElementById("table_vista_SoliCredito").innerHTML = datos_buscados

				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}

var codSolcirudFK=""
function obtenerdatosvistaSolicitudCreditoVenta(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});

	datostr.className = 'tableRegistroSelec'
 
		codSolcirudFK = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptSolicitudCredito').value = $(datostr).children('td[id="td_datos_3"]').html();
		
		
		idFkCliente = $(datostr).children('td[id="td_datos_21"]').html();
		document.getElementById('inptClienteVenta').value = $(datostr).children('td[id="td_datos_3"]').html();
		document.getElementById('inptClienteVenta2').value = $(datostr).children('td[id="td_datos_3"]').html();
		document.getElementById('inptDocClienteVenta').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptDocClienteVenta2').value = $(datostr).children('td[id="td_datos_1"]').html();	
		document.getElementById('inptDireccionVenta').value =  $(datostr).children('td[id="td_datos_6"]').html();
		document.getElementById('inptTelefVenta').value =  $(datostr).children('td[id="td_datos_5"]').html();
		document.getElementById('inptAccesoCreditoVentaCliente').value =  $(datostr).children('td[id="td_datos_22"]').html();
		document.getElementById('inptLugrarTrabajoCliente').value =  $(datostr).children('td[id="td_datos_11"]').html();
		document.getElementById('inptDireccionTrabajoCliente').value =  $(datostr).children('td[id="td_datos_16"]').html();
		document.getElementById('inptSalarioCliente').value =  $(datostr).children('td[id="td_datos_12"]').html();
		document.getElementById('inptAntiguedadCliente').value =  $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptNroTelefTrabajoCliente1').value =  $(datostr).children('td[id="td_datos_14"]').html();
		document.getElementById('inptNroTelefTrabajoCliente2').value =  $(datostr).children('td[id="td_datos_15"]').html();
		// document.getElementById('inptTelefVenta').value =  $(datostr).children('td[id="td_datos_4"]').html();
		// alert($(datostr).children('td[id="td_datos_16"]').html())
		document.getElementById("btnMasInfoClienteVenta").style.display=''
		document.getElementById("btnNuevoClienteVenta").style.display='none'
		
		
 
	document.getElementById("divVistaSolicitudCredito").style.display = "none"



}


function BuscarImprimirSolicitudCredito(){
	
 NombreClienteSC =""
 DereccionClienteSC =""
 ReferenciaClienteSC =""
 ZonaClienteSC =""
 FechaNacCLienteSC =""
 EdadClienteSC =""
 NroTelClienteSC =""
 NroWharsappSC =""
 ViviendaClienteSC =""
 EstadoCivilClienteSC =""
 ciClienteSC=""
/////////////////////
 LucarTrabajoClienteSC =""
 DireccionTrabajoClienteSC =""
 TelefonoTrabajoClienteSC =""
 CargoClienteSC =""
 SalarioClienteSC =""
 AntiguedadClienteSC =""
////////////////////
 NombreGaranteSC =""
 CIGaranteSC =""
 DireccionGaranteSC =""
 ReferenciaGaranteSC =""
 NroTelGaranteSC =""
 LugarTrabajoGranteSC =""
 AntiguegagGatanteSC =""
 SalarioGaranteSC =""
 DivProductoSC =""
 DivReferenciaSC =""
 
 if(idSolicitudCredito==""){
	 ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		return;
 }


			obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idSolicitudCredito,
			"funt": "BuscarImprimirSolicitudCredito"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmSolicitudCredito.php",
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
			document.getElementById("table_mas_referenciasSolicitudCredito").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_mas_referenciasSolicitudCredito").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			Respuesta=respuestaJqueryAjax(Respuesta)
			if (Respuesta == true) {
				
		   var datos_buscados=datos[2];		 
		 NombreClienteSC =datos[3]
		 ciClienteSC=datos[4]
 DereccionClienteSC =datos[5]
 ReferenciaClienteSC =datos[6]
 ZonaClienteSC =datos[8]
 FechaNacCLienteSC =datos[7]
 EdadClienteSC =datos[11]
 NroTelClienteSC =datos[9]
 NroWharsappSC =datos[10]
 ViviendaClienteSC =datos[13]
 EstadoCivilClienteSC =datos[12]
/////////////////////
 LucarTrabajoClienteSC =datos[14]
 DireccionTrabajoClienteSC =datos[15]
 TelefonoTrabajoClienteSC =datos[16]
 CargoClienteSC =datos[17]
 SalarioClienteSC =datos[18]
 AntiguedadClienteSC =datos[19]
////////////////////
 NombreGaranteSC =datos[20]
 CIGaranteSC =datos[21]
 DireccionGaranteSC =datos[22]
 ReferenciaGaranteSC =datos[23]
 NroTelGaranteSC =datos[24]
 LugarTrabajoGranteSC =datos[25]
 AntiguegagGatanteSC =datos[26]
 SalarioGaranteSC =datos[27]
 DivProductoSC =datos[2]
 
 
 DivReferenciaSC =datos[28]+datos[29];
 
 
 imprimirSolicitudCredito()
			
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
