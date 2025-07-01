/*
ABM LISTADO
*/
function verCerrarVentanaAbmListado(d, l) {
	document.getElementById('divAbmListado1').style.display = "none"
	document.getElementById('divAbmListado2').style.display = "none"
	if (d == "1") {
		$("div[id=divAbmListado2]").fadeIn(250)
		if (l == "1") {
			limpiarcamposListado()
		}
	} else {
		$("div[id=divAbmListado1]").fadeIn(250)
	}
}
function verVentanaEditarListado() {
	if (idAbmListado == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmListado("1", "2")
}
var idAbmListado = ""
var codProductoListado = "";
var codEncargadoListado = "";
function obtenerdatosabmListado(datostr) {


	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});

	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptSeleccProductoListado').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptRegistroSeleccListado').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptSeleccEncargadoProductoListado').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptCantProductoListado').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptFechaProductoListado').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptEstadoProductoListado').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptCantVentaProductoListado').value = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptlocalListado').value = $(datostr).children('td[id="td_datos_9"]').html();
	idAbmListado = $(datostr).children('td[id="td_id"]').html();
	codProductoListado = $(datostr).children('td[id="td_datos_1"]').html();
	codEncargadoListado = $(datostr).children('td[id="td_datos_7"]').html();



}
function verificarcamposListado() {

	var inptSeleccEncargadoProductoListado = document.getElementById('inptSeleccEncargadoProductoListado').value
	var inptSeleccProductoListado = document.getElementById('inptSeleccProductoListado').value
	var inptCantProductoListado = document.getElementById('inptCantProductoListado').value
	var inptFechaProductoListado = document.getElementById('inptFechaProductoListado').value
	var inptEstadoProductoListado = document.getElementById('inptEstadoProductoListado').value
	var inptCantVentaProductoListado = document.getElementById('inptCantVentaProductoListado').value
	var inptlocalListado = document.getElementById('inptlocalListado').value


	if (inptSeleccEncargadoProductoListado == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN ENCARGADO", "#")
		return false;
	}
	if (inptSeleccProductoListado == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO", "#")
		return false;
	}
	if (inptCantProductoListado == "") {
		ver_vetana_informativa("FALTO INGRESAR LA CANTIDAD", "#")
		return false;
	}
	if (inptFechaProductoListado == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA FECHA", "#")
		return false;
	}



	var accion = "";
	if (idAbmListado != "") {
		accion = "editar";
	} else {
		accion = "nuevo";
	}
	abmlistado(inptCantProductoListado, inptFechaProductoListado, inptEstadoProductoListado, codProductoListado, codEncargadoListado, inptCantVentaProductoListado, inptlocalListado, idAbmListado, accion);
}
function abmlistado(cant, fecha, estado, cod_producto, cod_cobrador, cantvendido, cod_local, idlistado, accion) {


	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idlistado", idlistado)
	datos.append("cant", cant)
	datos.append("fecha", fecha)
	datos.append("estado", estado)
	datos.append("cod_producto", cod_producto)
	datos.append("cod_cobrador", cod_cobrador)
	datos.append("cantvendido", cantvendido)
	datos.append("cod_local", cod_local)



	var OpAjax = $.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmlistado.php",
		type: "post",
		cache: false,
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


				if (Respuesta == "UI") {

					ir_a_login()
					ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
					return false;



				}
				if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
				if (Respuesta == "camposvacio") {


					ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
					return false;



				}
				if (Respuesta == "EX") {


					ver_vetana_informativa("YA EXISTE UN DATO SIMILAR...")
					return false;



				}
				if (Respuesta == "exito") {

					document.getElementById('inptSeleccProductoListado').value = "";
					document.getElementById('inptRegistroSeleccListado').value = "";
					document.getElementById('inptCantProductoListado').value = "";
					document.getElementById('inptCantVentaProductoListado').value = "";
					document.getElementById('inptEstadoProductoListado').value = "Activo";

					codProductoListado = "";
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmListado = ""
					buscarabmListado()



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
function buscarabmListado() {





	var fecha1 = document.getElementById('inptBuscarListaF1').value
	var fecha2 = document.getElementById('inptBuscarListaF2').value
	var estado = document.getElementById('inptSeleccEstadoBuscarLista').value
	var buscar = document.getElementById('inptBuscarListado').value
	var cod_local = document.getElementById('inptlocalListadoBuscar').value
	document.getElementById("table_abm_listado").innerHTML = paginacargando

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"estado": estado,
		"buscar": buscar,
		"cod_local": cod_local,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmlistado.php",
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
			document.getElementById("table_abm_listado").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_listado").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];

				if (Respuesta == "UI") {

					ir_a_login()
					ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
					return false;



				}
				if (Respuesta == "NI") {


					ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - BUSCAR CLIENTE")
					return false;



				}
				if (Respuesta == "exito") {


					document.getElementById('inptSeleccProductoListado').value = "";
					document.getElementById('inptCantProductoListado').value = "";
					document.getElementById('inptEstadoProductoListado').value = "Activo";
					var datos_buscados = datos[2];

					document.getElementById("table_abm_listado").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoListado").value = datos[4];


				}
			} catch (error) {

			}
		}
	});


}
function limpiarcamposListado() {


	document.getElementById('inptSeleccProductoListado').value = "";
	document.getElementById('inptRegistroSeleccListado').value = "";
	document.getElementById('inptSeleccEncargadoProductoListado').value = "";
	document.getElementById('inptCantProductoListado').value = "";
	document.getElementById('inptFechaProductoListado').value = "";
	document.getElementById('inptCantVentaProductoListado').value = "";
	document.getElementById('inptEstadoProductoListado').value = "Activo";
	idAbmListado = "";
	codProductoListado = "";
	codEncargadoListado = "";
}

/*
COBRADOR SOBRANTE
*/

var idPagoComision = "";
function obtenerdatoscomisioncobrador(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});

	datostr.className = 'tableRegistroSelec'
	idPagoComision = $(datostr).children('td[id="td_id_1"]').html();
	document.getElementById('inptMontoComisionCobrador').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptComisionCobradorupdate').value = $(datostr).children('td[id="td_datos_5"]').html();
	vercerrarcambiarporcomision("1")
}
function vercerrarcambiarporcomision(d) {

	if (d == "1") {
		$("div[id=divOpcionesComisionCobrador]").fadeIn(250)

	} else {
		$("div[id=divOpcionesComisionCobrador]").fadeOut(250)
	}

}
function cambiarcomisioncobrador() {
	if (idPagoComision == "") {

		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return

	}
	var comision = document.getElementById('inptComisionCobradorupdate').value
	if (comision == "") {

		ver_vetana_informativa("FALTO INGRESAR LA COMISION", "#")
		return

	}

	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "editarcomision")
	datos.append("comision", comision)
	datos.append("idPagoComision", idPagoComision)




	var OpAjax = $.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmpagos.php",
		type: "post",
		cache: false,
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


				if (Respuesta == "UI") {

					ir_a_login()
					ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
					return false;



				}
				if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
				if (Respuesta == "camposvacio") {


					ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
					return false;



				}
				if (Respuesta == "EX") {


					ver_vetana_informativa("YA EXISTE UN DATO SIMILAR...")
					return false;



				}
				if (Respuesta == "exito") {

					vercerrarcambiarporcomision("2")
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")

					buscarcomisioncobrador()



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


/*
VENTA SOBRANTE
*/



function bloquearBuscarPorVentaCancelada(d){
	document.getElementById('divFiltroVentaCanceladas1').style.display="none"
	document.getElementById('divFiltroVentaCanceladas2').style.display="none"
	document.getElementById('inptBuscarInfVentasCanceladas').value = ""
	document.getElementById('inptBuscarInfVentasCanceladasF1').value = ""
	document.getElementById('inptBuscarInfVentasCanceladasF2').value = ""

	if(d=="2"){
		document.getElementById('inptBuscarporHistorialVentaCancelado').value = "2"
	document.getElementById('divFiltroVentaCanceladas1').style.display=""
	}
	if(d=="1"){
		document.getElementById('inptBuscarporHistorialVentaCancelado').value = "1"
	document.getElementById('divFiltroVentaCanceladas2').style.display=""
	}
	
	
}
function verCerrarRefinanciamiento3(d){
	if(d=="1"){
	if(elementoventa==""){
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO","#")
	  return false;
	
	}
		
	
	var datos=elementoventa;
		document.getElementById("inpCodVentaRefinVenta").value=$(datos).children('td[id="td_datos_13"]').html();
	document.getElementById("inptTotalVentaRefinVenta").value=$(datos).children('td[id="td_datos_5"]').html();
	document.getElementById("inptTotalActualVentaRefinVenta").value=$(datos).children('td[id="td_datos_5"]').html();
	document.getElementById("inptNroCuotaRefinVenta").value=""
	document.getElementById("inptMontoRefinVenta").value=""
	document.getElementById("inptDecuentoRefinVenta").value=""
	document.getElementById("inptFechaRefinVenta").value=""
	document.getElementById("inputSelectMetodoRefinVenta").value=$(datos).children('td[id="td_datos_18"]').html();
	
	codVentaCambio=$(datos).children('td[id="td_datos_8"]').html();;

    vercerrarOpcionesDeRefinanciamiento3("1")
	}
}
function MasFiltrosRefinaciar(datos){
	if(document.getElementById("divMasFiltrosRefinaciar").style.display==""){
		document.getElementById("divMasFiltrosRefinaciar").style.display="none"
		datos.src="/GoodVentaAsisCap/iconos/filtros.png";
	}else{
		$("div[id=divMasFiltrosRefinaciar]").slideDown(500);
		datos.src="/GoodVentaAsisCap/iconos/filtros2.png";
	}
}
function vercerrarOpcionesDeRefinanciamiento3(d){
	
			
	if(d=="1"){
		$("div[id=divAbmRefinanciarCuenta]").fadeIn(250)
		document.getElementById("btnEliminarCreditoSelecc").style.display='none'
		buscarcreditosRefin();
	}else{
		$("div[id=divAbmRefinanciarCuenta]").fadeOut(250)
		document.getElementById("btnEliminarCreditoSelecc").style.display='none'
		codVentaCambio="";
		codCreditoRefin="";
	}
	

}
function buscarcreditosRefin(){
 
 

	 
	

		 document.getElementById("table_abm_opciones_RefinVenta").innerHTML=paginacargando
		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": codVentaCambio,
			"funt": "buscarcreditoenrenfi"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmcreditos.php",
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
			document.getElementById("table_abm_opciones_RefinVenta").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_abm_opciones_RefinVenta").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
		 if (Respuesta=="UI")
			{
		
			  ir_a_login()
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
			if (Respuesta == "exito")
			{
				
				
				
		  var datos_buscados=datos[2];
		 
			document.getElementById("table_abm_opciones_RefinVenta").innerHTML=datos_buscados
			document.getElementById("inptTotalPagadoRefinVenta").value=datos[3]
			document.getElementById("inptDeudaActualRefinVenta").value=datos[4]
			

	  
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}
function verificarcamposrefin1(){
	
	 
var inptTotalActualVentaRefinVenta =document.getElementById("inptTotalActualVentaRefinVenta").value
  if(codVentaCambio==""){
	ver_vetana_informativa("FALTO SELECCIONAR UNA VENTA","#")
	  return false;
  }
  if(inptTotalActualVentaRefinVenta==""){
	ver_vetana_informativa("FALTO INGRESAR UN TOTAL","#")
	  return false;
  }


 abmrefinaciartotalventa(codVentaCambio,inptTotalActualVentaRefinVenta)
	
}
function abmrefinaciartotalventa(cod_ventaFK,Total) {
	
	
	      verCerrarEfectoCargando("1")
	        var datos = new FormData();
		   	obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", "refinanciartotalventa")
			 datos.append("total" , Total)
			 datos.append("cod_ventaFK" , cod_ventaFK)
			 		
			var OpAjax= $.ajax({
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmventa.php",
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
		 

		 if (Respuesta=="UI")
			{
		
			  ir_a_login()
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
		 if (Respuesta=="camposvacio")
			{
		
			
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
						return false;
					


			}
			if (Respuesta=="EX")
			{
		
			
				ver_vetana_informativa("YA EXISTE UN DATO SIMILAR...")
						return false;
					


			}
			if (Respuesta=="exito")
			{
		
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
			
			
			}
			else
			{
			
	
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")


			}
			
			}catch(error)
				{
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				}
		 
					
			}
			});
			
	
}
var elementocreditorefin="";
var codCreditoRefin="";
function obtenerDatosCreditosRefinanciacion(datos){
	
		 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });

    datos.className='tableRegistroSelec'
	elementocreditorefin=datos
		document.getElementById("btnEliminarCreditoSelecc").style.display=''
	
}
function eliminarcreditorefinanciar() {
	 if(confirm("Estas Seguro que quieres eliminar este pago")){
	 if(elementocreditorefin==""){
		 ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		 return false
	 }
	var codCredito=$(elementocreditorefin).children('td[id="td_datos_1"]').html();
	      verCerrarEfectoCargando("1")
	        var datos = new FormData();
		   	obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", "eliminarcreditorefin")
			 datos.append("codcredito" , codCredito)
			 datos.append("cod_venta" , codVentaCambio)
			 		
			var OpAjax= $.ajax({
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmcreditos.php",
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
		 

		 if (Respuesta=="UI")
			{
		
			  ir_a_login()
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
		 if (Respuesta=="camposvacio")
			{
		
			
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
						return false;
					


			}
			if (Respuesta=="EX")
			{
		
			
				ver_vetana_informativa("YA EXISTE UN DATO SIMILAR...")
						return false;
					


			}
			if (Respuesta=="exito")
			{
		elementocreditorefin="";
		document.getElementById("btnEliminarCreditoSelecc").style.display='none'
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
			buscarcreditosRefin()
			
			}
			else
			{
			
	
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")


			}
			
			}catch(error)
				{
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				}
		 
					
			}
			});
	 }
	
}
function obtenerPagosCreditosRefinanciacion(datos){
	$("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });

    datos.className='tableRegistroSelec'
	codCredito=$(datos).children('td[id="td_datos_1"]').html();;
	codCreditoRefin=$(datos).children('td[id="td_datos_1"]').html();;
	document.getElementById("divHistorialPagos").style.display="";
	buscarhistorialdepagos()
	
}
function obtenerDatosCreditosRefinEditar(datos){
	
		 
	
	
		 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });

    datos.className='tableRegistroSelec'
	codCredito=$(datos).children('td[id="td_datos_1"]').html();;
	codCreditoRefin=$(datos).children('td[id="td_datos_1"]').html();
	var plazo=$(datos).children('td[id="td_datos_2"]').html();
	if(plazo=="ENTREGA"){
		document.getElementById("inptNroCuotaRefinVenta").value='ENTREGA'
	}
	if(plazo=="Contado"){
		document.getElementById("inptNroCuotaRefinVenta").value='Contado'
	}
	if(plazo!="Contado" && plazo!="ENTREGA"){
		document.getElementById("inptNroCuotaRefinVenta").value='0'
	}
	
	
	document.getElementById("inptMontoRefinVenta").value=$(datos).children('td[id="td_datos_5"]').html();
	document.getElementById("inptDecuentoRefinVenta").value=$(datos).children('td[id="td_datos_12"]').html();
	document.getElementById("inptFechaRefinVenta").value=$(datos).children('td[id="td_datos_3"]').html();
	document.getElementById("inputSelectMetodoRefinVenta").value=$(datos).children('td[id="td_datos_13"]').html();
	document.getElementById("inptInteresRefinVenta").value=$(datos).children('td[id="td_datos_14"]').html();
	document.getElementById("inptDiasGraciaRefinVenta").value=$(datos).children('td[id="td_datos_15"]').html();
}
function limpiarcamposcreditosrefin(){
	document.getElementById("inptNroCuotaRefinVenta").value="0";
	document.getElementById("inptMontoRefinVenta").value="";
	document.getElementById("inptDiasGraciaRefinVenta").value="";
	document.getElementById("inptDecuentoRefinVenta").value="";
	document.getElementById("inptFechaRefinVenta").value="";
	document.getElementById("inputSelectMetodoRefinVenta").value="";
	document.getElementById("inptInteresRefinVenta").value="";
	codCreditoRefin="";
	codCredito="";
}
function verificarcamposeditarcreditorefin(){
	
	 var inptNroCuotaRefinVenta = document.getElementById("inptNroCuotaRefinVenta").value;
	var inptMontoRefinVenta =document.getElementById("inptMontoRefinVenta").value;
	var inptDecuentoRefinVenta =document.getElementById("inptDecuentoRefinVenta").value;
	var inptFechaRefinVenta = document.getElementById("inptFechaRefinVenta").value;
	var inputSelectMetodoRefinVenta=document.getElementById("inputSelectMetodoRefinVenta").value;
	var inptInteresRefinVenta=document.getElementById("inptInteresRefinVenta").value;
	var inptDiasGraciaRefinVenta=document.getElementById("inptDiasGraciaRefinVenta").value;

 
  if(inptNroCuotaRefinVenta==""){
	ver_vetana_informativa("EL NRO DE CUOTA NO PUEDE QUEDAR VACIO","#")
	  return false;
  }
  if(inptMontoRefinVenta==""){
	ver_vetana_informativa("EL MONTO DE LA CUOTA NO PUEDE QUEDAR VACIO","#")
	  return false;
  }
  if(inptDecuentoRefinVenta==""){
	ver_vetana_informativa("EL DESC. DE LA CUOTA NO PUEDE QUEDAR VACIO","#")
	  return false;
  }
  if(inptFechaRefinVenta==""){
	ver_vetana_informativa("LA FECHA DE LA CUOTA NO PUEDE QUEDAR VACIO","#")
	  return false;
  }
 var accion="nuevocreditorefin"
if(codCreditoRefin!=""){
	accion="editarcreditorefin"
}

 abmcreditorefin(inptDiasGraciaRefinVenta,inptNroCuotaRefinVenta,inptInteresRefinVenta,inptMontoRefinVenta,inptDecuentoRefinVenta,inptFechaRefinVenta,codCreditoRefin,accion)
	
}
function abmcreditorefin(dias,cuotaNro,interes,Monto,descuento,fecha,idcredito,operacion) {
	
	
	verCerrarEfectoCargando("1")
	  var datos = new FormData();
			obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", operacion)
			 datos.append("plazo" , cuotaNro)
			 datos.append("Monto" , Monto)
			 datos.append("descuento" , descuento)
			 datos.append("fechapago" , fecha)
			 datos.append("idcredito" , idcredito)
			 datos.append("cod_venta" , codVentaCambio)
			 datos.append("interes" , interes)
			 datos.append("dias" , dias)
			 
			
		
		
			
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/abmcreditos.php",
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
		 

		 if (Respuesta=="UI")
			{
		
			  ir_a_login()
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
		 if (Respuesta=="camposvacio")
			{
		
			
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
						return false;
					


			}
			if (Respuesta=="EX")
			{
		
			
				ver_vetana_informativa("YA EXISTE UN DATO SIMILAR...")
						return false;
					


			}
			if (Respuesta=="exito")
			{
		
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
			
				limpiarcamposcreditosrefin()
				buscarcreditosRefin()

			}
			else
			{
			
	
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")


			}
			
			}catch(error)
				{
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				}
		 
					
			}
			});
			
	
}






var detallesRecibo = 0;

var NroFacturaLegal = 0;
var SubtotalRecibovaiva5 = 0;
var SubtotalRecibovaiva10 = 0;
var totalesReciboDetalleiva10 = 0;
var totalesReciboDetalleiva15 = 0;
var totalInteresRecibo = 0;
var totalesReciboLetras = 0;
var totaletrasRecibo=""
var totalesRecibo=""
var InteresRecibo=""
var DeudaActualRecibo=""
var DiasAtrasado=""
var CuotasRestante=""

var NombreRecibo=""
var DireccionRecibo=""
var ZonaRecibo=""
var ZonaReciboGarante=""
var telefonoRecinoGarante=""
var telefonoRecino=""
var DocumentoRecibo=""
var RucRecibo=""
var TotalDescuentoRecibo=""
var PlazoRecibo=""
var paginaDetalleTicket=""
var TipoFactura=""

var CiRecibo=""
function ImprimirFacrtura1() {
	
	if(RucRecibo==""){
		RucRecibo=CiRecibo
		
	}

document.getElementById("DivDetalleRecibo1").innerHTML = ""
document.getElementById("DivDetalleRecibo2").innerHTML = ""
document.getElementById("DivDetalleRecibo3").innerHTML = ""

document.getElementById("DivDetalleRecibo1").innerHTML = detallesRecibo
document.getElementById("DivDetalleRecibo2").innerHTML = detallesRecibo
document.getElementById("DivDetalleRecibo3").innerHTML = detallesRecibo
document.getElementById("lblReciboTotal5iva1").innerHTML = totalesReciboDetalleiva15
document.getElementById("lblReciboTotal5iva2").innerHTML = totalesReciboDetalleiva15
document.getElementById("lblReciboTotal5iva3").innerHTML = totalesReciboDetalleiva15
document.getElementById("lblReciboTotal10iva1").innerHTML = totalesReciboDetalleiva10
document.getElementById("lblReciboTotal10iva2").innerHTML = totalesReciboDetalleiva10
document.getElementById("lblReciboTotal10iva3").innerHTML = totalesReciboDetalleiva10
document.getElementById("lblReciboSubTotal10iva1").innerHTML = SubtotalRecibovaiva10
document.getElementById("lblReciboSubTotal10iva2").innerHTML = SubtotalRecibovaiva10
document.getElementById("lblReciboSubTotal10iva3").innerHTML = SubtotalRecibovaiva10
document.getElementById("lblReciboSubTotal5iva1").innerHTML = SubtotalRecibovaiva5
document.getElementById("lblReciboSubTotal5iva2").innerHTML = SubtotalRecibovaiva5
document.getElementById("lblReciboSubTotal5iva3").innerHTML = SubtotalRecibovaiva5
document.getElementById("lblReciboTotalVenta1").innerHTML = totalesRecibo
document.getElementById("lblReciboTotalVenta2").innerHTML = totalesRecibo
document.getElementById("lblReciboTotalVenta3").innerHTML = totalesRecibo
document.getElementById("lblReciboTotaliva1").innerHTML = totalInteresRecibo
document.getElementById("lblReciboTotaliva2").innerHTML = totalInteresRecibo
document.getElementById("lblReciboTotaliva3").innerHTML = totalInteresRecibo
var t=QuitarSeparadorMilValor(totalesRecibo);
  var totaletrasRecibo=numeroALetras(t, {
  plural: 'GUARANIES',
  singular: 'GUARANIES',
  centPlural: 'GUARANIES',
  centSingular: 'GUARANIES'
});
document.getElementById("lblReciboNroEnLetras1").innerHTML = totaletrasRecibo
document.getElementById("lblReciboNroEnLetras2").innerHTML = totaletrasRecibo
document.getElementById("lblReciboNroEnLetras3").innerHTML = totaletrasRecibo
		
document.getElementById("lblReciboNombre1").innerHTML = NombreRecibo
document.getElementById("lblReciboCi1").innerHTML = ""
document.getElementById("lblReciboRUC1").innerHTML = RucRecibo
document.getElementById("lblReciboDireccion1").innerHTML =  DireccionRecibo
document.getElementById("lblReciboTelef1").innerHTML =  ""

document.getElementById("lblReciboNombre2").innerHTML = NombreRecibo
document.getElementById("lblReciboCi2").innerHTML = ""
document.getElementById("lblReciboRUC2").innerHTML = RucRecibo
document.getElementById("lblReciboDireccion2").innerHTML =  DireccionRecibo
document.getElementById("lblReciboTelef2").innerHTML =  ""

document.getElementById("lblReciboNombre3").innerHTML = NombreRecibo
document.getElementById("lblReciboCi3").innerHTML = ""
document.getElementById("lblReciboRUC3").innerHTML = RucRecibo
document.getElementById("lblReciboDireccion3").innerHTML =  DireccionRecibo
document.getElementById("lblReciboTelef3").innerHTML =  ""

if(TipoFactura=="CONTADO"){
	document.getElementById("lblReciboContado1").innerHTML =  "X"
	document.getElementById("lblReciboContado2").innerHTML =  "X"
	document.getElementById("lblReciboContado3").innerHTML =  "X"
	
	document.getElementById("lblRecibocredito1").innerHTML =  ""
	document.getElementById("lblRecibocredito2").innerHTML =  ""
	document.getElementById("lblRecibocredito3").innerHTML =  ""
}else{
	document.getElementById("lblRecibocredito1").innerHTML =  "X"
	document.getElementById("lblRecibocredito2").innerHTML =  "X"
	document.getElementById("lblRecibocredito3").innerHTML =  "X"
	
	document.getElementById("lblReciboContado1").innerHTML =  ""
	document.getElementById("lblReciboContado2").innerHTML =  ""
	document.getElementById("lblReciboContado3").innerHTML =  ""
}

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
  
document.getElementById("lblReciboFechaEmision1").innerHTML = f.getFullYear()+"-"+mes+"-"+dia;
document.getElementById("lblReciboFechaEmision2").innerHTML =  f.getFullYear()+"-"+mes+"-"+dia;
document.getElementById("lblReciboFechaEmision3").innerHTML =  f.getFullYear()+"-"+mes+"-"+dia;

document.getElementById("lblReciboElaborado1").innerHTML =  ""
document.getElementById("lblReciboElaborado2").innerHTML =""
document.getElementById("lblReciboElaborado3").innerHTML = ""

document.getElementById("lblReciboCondicion1").innerHTML =  ""
document.getElementById("lblReciboCondicion2").innerHTML = ""
document.getElementById("lblReciboCondicion3").innerHTML = ""

document.getElementById("lblReciboPlazo1").innerHTML =  ""
document.getElementById("lblReciboPlazo2").innerHTML = ""
document.getElementById("lblReciboPlazo3").innerHTML = ""


var documento= document.getElementById("divImpresionRecibo").innerHTML;
     localStorage.setItem("reporte", documento);
	  localStorage.setItem("tipo", "factura");
	  
	  
	  if(cod_localFKUSer=="1"){
	 var URL= "/GoodVentaAsisCap/system/reportFacturas.html"
        window.open(URL, 'Imprimir', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,left = 0');
	  }
		 if(cod_localFKUSer=="2"){
		 var URL= "/GoodVentaAsisCap/system/reportFacturasSucUno.html"
        window.open(URL, 'Imprimir', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,left = 0');
		 }
	 if(cod_localFKUSer=="3"){
		 var URL= "/GoodVentaAsisCap/system/reportFacturasSucUno.html"
        window.open(URL, 'Imprimir', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,left = 0');
		 }

}




var nroPagare=0;
var vencimientopagare=0;
var facturanroPagare=0;
var ImportePagare=0;

var EntregaPagare=0;
function imprimirPagare(){
	
	if(EntregaPagare==""){
		EntregaPagare="0";
	}
	
	var t=QuitarSeparadorMilValor(totalesRecibo);
	
	EntregaPagare=QuitarSeparadorMilValor(EntregaPagare);
	
	let totalpagare = Number(t) - Number(EntregaPagare)
	
	
  var totaletrasRecibo=numeroALetras(totalpagare, {
  plural: 'GUARANIES',
  singular: 'GUARANIES',
  centPlural: 'GUARANIES',
  centSingular: 'GUARANIES'
});

var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()+1
	if(mes<10){
		mes="0"+mes;
	}
	var anho =f.getFullYear()
	
	var emision= f.getFullYear()+"-"+mes+"-"+dia;
	
	
	
var fecha = new Date(vencimientopagare);
	fecha.setDate(fecha.getDate() + 1);
	
	var dia =fecha.getDate() ;
	if(dia<10){
		dia="0"+dia;
	}
	var mes =fecha.getMonth()+1
	var mesletras=obtenerMes(mes);
	if(mes<10){
		mes="0"+mes;
	}
	
	var hora =fecha.getHours()
	
	if(hora<10){
		hora="0"+hora;
	}
	var min =fecha.getMinutes()
	if(min<10){
		min="0"+min;
	}
	
	var anhoVencimiendo =fecha.getFullYear()

	ImportePagare=totalpagare
	var pagina="<table style='width:82%'>"
+"<tr>"
+"<td style='width:50%;text-align:left'>"
+"<p class='pTituloC'><b>Pagaré Nro:</b>&nbsp&nbsp"+nroPagare+"</p>"
+"<p class='pTituloC'><b>Fecha de Emisión:</b>&nbsp&nbsp_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ </p>"
+"<p class='pTituloC'><b>Vencimiento:</b>&nbsp&nbsp_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ </p>"
+"</td>"
+"<td style='width:50%;text-align:left'>"
+"<p class='pTituloC'><b>Factura Nro:</b>&nbsp&nbsp"+facturanroPagare+"</p>"
+"<p class='pTituloC'><b>Importe:</b>&nbsp&nbsp"+separadordemilesnumero(ImportePagare)+"</p>"
+"</td>"
+"</tr>"
+"</table>"
+"<br>"
+"<div style='width:82%' style='text-align:left'>"
+"<p class='pTituloC' style='text-align:left;    text-align: justify; line-height: 15px;'>El día _______________________________________  por este PAGARÉ A LA ORDEN, me(nos) obligo(amos) a PAGAR A LA VISTA, a _____________________________________ , o a su orden, en su domicilio _______________________________________________________, sin protesto, la cantidad de guaraníes:"
+"<b>&nbsp&nbsp"+totaletrasRecibo+"&nbsp&nbsp</b>.---------"
+"<br>"
+"<br>"
+"Queda expresamente convenido entre ____________________________________ (el acreedor) y el(los) deudor(es), que la falta de pago a su vencimiento de éste pagaré, producirá la caducidad automática y el decaimiento anticipado de los plazos establecidos en todos los demás pagarés o documentos cualquiera sea su naturaleza, causa u origen y causará de pleno derecho el vencimiento anticipado de los pagarés o documentos no vencidos, facultando al acreedor irrevocablemente a exigir el pago inmediato del saldo total de la deuda. La mora se producirá por el mero vencimiento del plazo, sin necesidad de protesto ni de ningún requerimiento judicial o extrajudicial por parte del acreedor.---------"
+"<br>"
+"<br>"
+"Se establece un interés moratorio de _% , interés punitorio del _ %, comisión del _ %, como así el _ % por daños y perjuicios ocasionados por el simple retardo sin que esto implique prórroga en el plazo de la obligación.---------"
+"<br>"
+"<br>"
+"Declaro (amos) expresamente con carácter irrevocable que la(s) firma(s) puestas al pie de este instrumento me(nos) obliga(n) al cumplimiento de todas y cada una de las cuotas establecidas y al condicionamiento general obrante en este pagaré.---------"
+"<br>"
+"<br>"
+"Este pagaré se rige por la leyes de la República del Paraguay y en especial por los artículos 51, 53 siguientes y concordantes de la ley 489/95. El simple vencimiento de una cuota autoriza al acreedor de forma irrevocable a la consulta e inclusión a la base de datos de INFORMCONF u otra agencia de informaciones. A todos los efectos legales y procesales queda aceptada la jurisdicción y competencia de los juzgados en lo civil y comercial de la Circunscripción Judicial Guairá.---------"
+"</p>"
+"</div>"
+"<br>"
+"<br>"
+"<br>"
+"<br>"
+"<table style='width:85%'>"
+"<tr>"
+"<td style='width:50%;text-align:left'>"
+"<p class='pTituloC' style='text-align:center'><b>-------------------------------------</b></p>"
+"<p class='pTituloC' style='text-align:center'><b>DEUDOR</b></p>"
+"<p class='pTituloC'><b>NOMBRE:</b>&nbsp&nbsp"+document.getElementById("inptClienteVenta").value+"</p>"
+"<p class='pTituloC'><b>C.I.:</b>&nbsp&nbsp"+document.getElementById("inptDocClienteVenta").value+"</p>"
+"<p class='pTituloC'><b>DIRECCIÓN:</b>&nbsp&nbsp"+ZonaRecibo+"</p>"
+"<p class='pTituloC'><b>TELEF.:</b>&nbsp&nbsp"+telefonoRecino+"</p>"
+"</td>"
+"<td style='width:50%;text-align:left'>"
+"<p class='pTituloC' style='text-align:center'><b>-------------------------------------</b></p>"
+"<p class='pTituloC' style='text-align:center'><b>CODEUDOR</b></p>"
+"<p class='pTituloC'><b>NOMBRE:</b>&nbsp&nbsp"+document.getElementById("inptGaranteVenta").value+"</p>"
+"<p class='pTituloC'><b>C.I.:</b>&nbsp&nbsp"+document.getElementById("inptDocGaranteVenta").value+"</p>"
+"<p class='pTituloC'><b>DIRECCIÓN:</b>&nbsp&nbsp"+ZonaReciboGarante+"</p>"
+"<p class='pTituloC'><b>TELEF.:</b>&nbsp&nbsp"+telefonoRecinoGarante+"</p>"
+"</td>"
+"</tr>"
+"</table>"
document.getElementById("divpagare").innerHTML=pagina;
 var documento= document.getElementById("DivImprimirPagares").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "reporte");
	
	  var URL= "/GoodVentaAsisCap/system/report.html"
        window.open(URL, 'Imprimir', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,left = 0');
}


function obtenerMes(m){
	if(m=="1"){
		controlMes=m
		return "ENERO";
	}
	if(m=="2"){
		controlMes=m
		return "FEBRERO";
	}
	if(m=="3"){
		controlMes=m
		return "MARZO";
	}
	if(m=="4"){
		controlMes=m
		return "ABRIL";
	}
	if(m=="5"){
		return "MAYO";
	}
	if(m=="6"){
		controlMes=m
		return "JUNIO";
	}
	if(m=="7"){
		controlMes=m
		return "JULIO";
	}
	if(m=="8"){
		controlMes=m
		return "AGOSTO";
	}
	if(m=="9"){
		controlMes=m
		return "SEPTIEMBRE";
	}
	if(m=="10"){
		controlMes=m
		return "OCTUBRE";
	}
	if(m=="11"){
		controlMes=m
		return "NOVIEMBRE";
	}
	if(m=="12"){
		controlMes=m
		return "DICIEMBRE";
	}
}
function imprimirDivticketFacturaViejo(){
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
var fechaVenta =document.getElementById("inptFechaVenta").value; 
var PuntoExpedicion=document.getElementById("inptSeleccPuntoExpedicionVenta").value;
var NroVentas=document.getElementById("inptNroVenta").value;
if(PuntoExpedicion!=""){
NroVentas=PuntoExpedicion+"-"+NroVentas
}
var totalP=document.getElementById("inptTotalPagado").value;
totalP=QuitarSeparadorMilValor(totalP)
var totalVe=totalesRecibo;
totalVe=QuitarSeparadorMilValor(totalVe)
var Pendiente=Number(totalVe)-Number(totalP)
if(document.getElementById("inptSeleccTipoVenta").value=="CONTADO"){
	PlazoRecibo=="1"
	pagina="<div  style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >B&R Emprendimientos S.A.</p>"
+"<p class='pTituloTicket2'>"
+"RUC: 80114316-0 <br>"
+"Cel:(0984) 145-450 <br>"
+"Natalicio Talavera - Paraguay "
+"</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Nro de Venta:</b></td>"
+"<td style=''>"+NroVentas+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+fechaVenta+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+ NombreRecibo+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>RUC o C.I.:</b></td>"
+"<td style=''>"+ DocumentoRecibo+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Tipo Venta:</b></td>"
+"<td style=''>CONTADO</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:25%'><b>Cant.</b></td>"
+"<td style='width:25%'><b>Producto</b></td>"
+"<td style='width:25%'><b>Costo</b></td>"
+"<td style='width:25%'><b>Total</b></td>"
+"</tr>"
+"</table>"
+paginaDetalleTicket
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Deuda:</b></td>"
+"<td style=''>"+totalesRecibo+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Pagado:</b></td>"
+"<td style=''>"+document.getElementById("inptTotalPagado").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Saldo:</b></td>"
+"<td style=''>"+separadordemilesnumero(Pendiente)+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Cajero :</b></td>"
+"<td style=''>"+document.getElementById("lblUser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
"</div>"
}else{
	pagina="<div  style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >B&R Emprendimientos S.A.</p>"
+"<p class='pTituloTicket2'>"
+"RUC: 80114316-0 <br>"
+"Cel:(0984) 145-450 <br>"
+"Natalicio Talavera - Paraguay "
+"</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Nro de Venta:</b></td>"
+"<td style=''>"+NroVentas+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+ NombreRecibo+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>RUC o C.I.:</b></td>"
+"<td style=''>"+ DocumentoRecibo+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Tipo Venta:</b></td>"
+"<td style=''>CREDITO</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Cuotas :</b></td>"
+"<td style=''>"+CuotasRestante+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:25%'><b>Cant.</b></td>"
+"<td style='width:25%'><b>Producto</b></td>"
+"<td style='width:25%'><b>Costo</b></td>"
+"<td style='width:25%'><b>Total</b></td>"
+"</tr>"
+"</table>"
+paginaDetalleTicket
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Deuda:</b></td>"
+"<td style=''>"+totalesRecibo+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Intereses Pagado:</b></td>"
+"<td style=''>"+InteresRecibo+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Descuento:</b></td>"
+"<td style=''>"+TotalDescuentoRecibo+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Pagado:</b></td>"
+"<td style=''>"+document.getElementById("inptTotalPagado").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Días Atrasado :</b></td>"
+"<td style=''>"+DiasAtrasado+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Saldo:</b></td>"
+"<td style=''>"+DeudaActualRecibo+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cajero :</b></td>"
+"<td style=''>"+document.getElementById("lblUser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
"</div>"
}



var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
}


function imprimirDivTickeFacturaPago(NombreCliente,CiCliente,NroRecibo,tipoventa,totalInteres,deudaActual,totalpagado,totaldescuento,TotalVenta,InteresActual,deudaActualsininteres){
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
    document.getElementById('inptFechaPagoConfirmar').value=f.getFullYear()+"-"+mes+"-"+dia;
	if(tipoventa=="CREDITO"){

	pagina="<!DOCTYPE html><html><head></head><body><div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >B&R Emprendimientos S.A.</p>"
+"<p class='pTituloTicket2'>"
+"RUC: 80114316-0 <br>"
+"Cel:(0984)145-450 <br>"
+"Natalicio Talavera, Paraguay"
+"</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Recibo de Dinero</b></p>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Numero :</b></td>"
+"<td style=''>"+NroRecibo+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+NombreCliente+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:85px'><b>RUC o C.I.:</b></td>"
+"<td style=''>"+CiCliente+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaPagoConfirmar").value+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>D. Atrasado</b></td>"
+"<td style=''>"+diaatrazado+" Día(s)</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Pagado:</b></td>"
+"<td style=''>"+pagado+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Cuota N.:</b></td>"
+"<td style=''>"+cuotasNro+"</td>"
+"</tr>"
+"</table>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>En Concepto de:</b></p>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:25%'><b>Cant.</b></td>"
+"<td style='width:25%'><b>Producto</b></td>"
+"<td style='width:25%'><b>Costo</b></td>"
+"<td style='width:25%'><b>Total</b></td>"
+"</tr>"
+"</table>"
+paginaticket
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Sub-total:</b></td>"
+"<td style=''>"+TotalVenta+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Descuento:</b></td>"
+"<td style=''>"+totaldescuento+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Interes Pagados:</b></td>"
+"<td style=''>"+totalInteres+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Pagado:</b></td>"
+"<td style=''>"+totalpagado+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div style='display:none'>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Intereses Pendientes:</b></td>"
+"<td style=''>"+InteresActual+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Sin Intereses:</b></td>"
+"<td style=''>"+deudaActualsininteres+" Gs.</td>"
+"</tr>"
+"</table>"
+"</div>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Saldo :</b></td>"
+"<td style=''>"+deudaActual+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cajero :</b></td>"
+"<td style=''>"+document.getElementById("lblUser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
"</div></body></html>"

	}else{
			pagina="<!DOCTYPE html><html><head></head><body><div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >B&R Emprendimientos S.A.</p>"
+"<p class='pTituloTicket2'>"
+"RUC: 80114316-0 <br>"
+"Cel: (0984) 145-450 <br>"
+"<br>Natalicio Talavera, Paraguay"
+"</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Recibo de Dinero</b></p>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Numero :</b></td>"
+"<td style=''>"+NroRecibo+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+NombreCliente+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>RUC o C.I.:</b></td>"
+"<td style=''>"+CiCliente+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaPagoConfirmar").value+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>En Concepto de:</b></p>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:25%'><b>Cant.</b></td>"
+"<td style='width:25%'><b>Producto</b></td>"
+"<td style='width:25%'><b>Costo</b></td>"
+"<td style='width:25%'><b>Total</b></td>"
+"</tr>"
+"</table>"
+paginaticket
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Sub-total:</b></td>"
+"<td style=''>"+TotalVenta+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Descuento:</b></td>"
+"<td style=''>"+totaldescuento+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Pagado:</b></td>"
+"<td style=''>"+pagado+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Saldo :</b></td>"
+"<td style=''>"+deudaActual+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cajero :</b></td>"
+"<td style=''>"+document.getElementById("lblUser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
"</div></body></html>"
	}
var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
 
}

// function ReImprimirDivTickeFacturaPago(Fecha,Cajero,CuotasNro,Pagado,DiasAtrazado,NombreCliente,CiCliente,NroRecibo,tipoventa,totalInteres,deudaActual,totalpagado,totaldescuento,TotalVenta,InteresActual,deudaActualsininteres){
	
// pagina="<!DOCTYPE html><html><head></head><body><div   style='background-color:#fff;'>"
// +"<center>"
// +"<div class='divTicket' >"
// +"<p class='pTituloTicket1' >B&R Emprendimientos S.A.</p>"
// +"<p class='pTituloTicket2'>"
// +"RUC: 80114316-0 <br>"
// +"Cel: (0984)145-450 <br>"
// +"Natalicio Talavera, Paraguay"
// +"</p>"
// +"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
// +"<p class='pTituloTicket1' style='font-size:12px;' ><b>Recibo de Dinero</b></p>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:80px'><b>Numero :</b></td>"
// +"<td style=''>"+NroRecibo+"</td>"
// +"</tr>"
// +"</table>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:60px'><b>Cliente:</b></td>"
// +"<td style=''>"+NombreCliente+"</td>"
// +"</tr>"
// +"</table>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:85px'><b>RUC o C.I.:</b></td>"
// +"<td style=''>"+CiCliente+"</td>"
// +"</tr>"
// +"</table>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:60px'><b>Fecha:</b></td>"
// +"<td style=''>"+Fecha+"</td>"
// +"</tr>"
// +"</table>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:100px'><b>D. Atrasado</b></td>"
// +"<td style=''>"+DiasAtrazado+" Día(s)</td>"
// +"</tr>"
// +"</table>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:80px'><b>Pagado:</b></td>"
// +"<td style=''>"+Pagado+" Gs.</td>"
// +"</tr>"
// +"</table>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:50px'><b>Cuota N.:</b></td>"
// +"<td style=''>"+CuotasNro+"</td>"
// +"</tr>"
// +"</table>"
// +"<p class='pTituloTicket1' style='font-size:12px;' ><b>En Concepto de:</b></p>"
// +"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:25%'><b>Cant.</b></td>"
// +"<td style='width:25%'><b>Producto</b></td>"
// +"<td style='width:25%'><b>Costo</b></td>"
// +"<td style='width:25%'><b>Total</b></td>"
// +"</tr>"
// +"</table>"
// +paginaticket
// +"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:110px'><b>Sub-total:</b></td>"
// +"<td style=''>"+TotalVenta+" Gs.</td>"
// +"</tr>"
// +"</table>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:110px'><b>Total Descuento:</b></td>"
// +"<td style=''>"+totaldescuento+" Gs.</td>"
// +"</tr>"
// +"</table>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:110px'><b>Interes Pagados:</b></td>"
// +"<td style=''>"+totalInteres+" Gs.</td>"
// +"</tr>"
// +"</table>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:110px'><b>Total Pagado:</b></td>"
// +"<td style=''>"+totalpagado+" Gs.</td>"
// +"</tr>"
// +"</table>"
// +"<div style='display:none'>"
// +"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:110px'><b>Intereses Pendientes:</b></td>"
// +"<td style=''>"+InteresActual+" Gs.</td>"
// +"</tr>"
// +"</table>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:110px'><b>Total Sin Intereses:</b></td>"
// +"<td style=''>"+deudaActualsininteres+" Gs.</td>"
// +"</tr>"
// +"</table>"
// +"</div>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:110px'><b>Saldo :</b></td>"
// +"<td style=''>"+deudaActual+" Gs.</td>"
// +"</tr>"
// +"</table>"
// +"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
// +"<table class='tableTicket'>"
// +"<tr>"
// +"<td style='width:60px'><b>Cajero :</b></td>"
// +"<td style=''>"+Cajero+"</td>"
// +"</tr>"
// +"</table>"
// +"</div>"
// +"</center>"
// "</div></body></html>"

	
// var ficha=pagina;
// document.getElementById("DivImprimir").innerHTML=ficha;
   // var documento= document.getElementById("DivImprimir").innerHTML;
     // localStorage.setItem("reporte", documento);
	   // localStorage.setItem("tipo", "ticket");
	 // window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 // document.getElementById("DivImprimir").innerHTML = "";
 
// }



function verCerrarquitardevolucion(d) {
	if (d == "1") {
		if (elementoDevolucion == "") {
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
			return false;

		}
		var datos = elementoDevolucion;
		document.getElementById("inptProductoDevolucionquitar").value = $(datos).children('td[id="td_datos_5"]').html();
		document.getElementById("inptNroVentaCambio").value = $(datos).children('td[id="td_datos_18"]').html();
		document.getElementById("inptFechaVentaCambioAct").value = $(datos).children('td[id="td_datos_14"]').html();
		document.getElementById("inptMonotCambioAct").value = $(datos).children('td[id="td_datos_15"]').html();
		document.getElementById("inptCuotaNroCambioAct").value = $(datos).children('td[id="td_datos_13"]').html();
		document.getElementById("inptTotalVentaCambio").value = $(datos).children('td[id="td_datos_12"]').html();
		document.getElementById("inptPagadoCambio").value = $(datos).children('td[id="td_datos_11"]').html();
		document.getElementById("inptCostoDevolucionquita").value = $(datos).children('td[id="td_datos_6"]').html();
		document.getElementById("inputSelectMetodoCambioAct").value = $(datos).children('td[id="td_datos_16"]').html();
		comisioncambio = $(datos).children('td[id="td_datos_10"]').html();
		codDetalleCambiio = $(datos).children('td[id="td_datos_4"]').html();
		codVentaCambio = $(datos).children('td[id="td_datos_17"]').html();
		cantidaCambio = $(datos).children('td[id="td_datos_7"]').html();
		CodProductocompraCambio = $(datos).children('td[id="td_datos_3"]').html();
		$("div[id=divQuitarProductoDevolucion]").fadeIn(250)

	} else {
		$("div[id=divQuitarProductoDevolucion]").fadeOut(250)
	}
}



function verCerrarquitardevolucionx(d) {
	if (d == "1") {
		if (elementoventa == "") {
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
			return false;

		}


		var datos = elementoventa;
		document.getElementById("inptPagadoCambio").value = $(datos).children('td[id="td_datos_6"]').html();
		document.getElementById("inptTotalVentaCambio").value = $(datos).children('td[id="td_datos_5"]').html();
		
		document.getElementById("inptTotalVentaRefinanciadoCambio").value = ""
		document.getElementById("inputSelectMetodoCambioAct").value = $(datos).children('td[id="td_datos_18"]').html();
		document.getElementById("inputSelectMetodoCambio").value = $(datos).children('td[id="td_datos_18"]').html();
		document.getElementById("inptCuotaNroCambioAct").value = $(datos).children('td[id="td_datos_19"]').html();
		document.getElementById("inptCuotaNroCambio").value = "";
		document.getElementById("inptMonotCambioAct").value = "";
		document.getElementById("inptFechaVentaCambioAct").value = $(datos).children('td[id="td_datos_21"]').html();
		codVentaCambio = $(datos).children('td[id="td_datos_8"]').html();;
		vercerrarOpcionesDeRefinanciamiento("1")
	}
}

function vercerrarOpcionesDeRefinanciamiento(d) {


	if (d == "1") {
		$("div[id=divRefinanciar]").fadeIn(250)

	} else {
		$("div[id=divRefinanciar]").fadeOut(250)
	}


}


function verificarquitardevolucion() {



	if (codDetalleCambiio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO 1", "#")
		return false;
	}

	if (CodProductocompraCambio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO 2", "#")
		return false;
	}

	if (codVentaCambio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO 3", "#")
		return false;
	}



	quitarDevolucion(CodProductocompraCambio, codDetalleCambiio, codVentaCambio, "Devolucion", cantidaCambio)

}
function quitarDevolucion(cod_productoFK, cod_detalle, cod_ventaFK, motivo, cantidaC) {


	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "quitarDevolucion")
	datos.append("cod_detalle", cod_detalle)
	datos.append("cod_productoFK", cod_productoFK)
	datos.append("cod_ventaFK", cod_ventaFK)
	datos.append("cantidaCambio", cantidaC)
	datos.append("motivo", motivo)
	datos.append("Local_FK", cod_localFKUSer)


	var OpAjax = $.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmdetalleventa.php",
		type: "post",
		cache: false,
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


				if (Respuesta == "UI") {

					ir_a_login()
					ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
					return false;



				}
				if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
				if (Respuesta == "camposvacio") {


					ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
					return false;



				}
				if (Respuesta == "EX") {


					ver_vetana_informativa("YA EXISTE UN DATO SIMILAR...")
					return false;



				}
				if (Respuesta == "exito") {

					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					// document.getElementById("inptTotalVentaRefinanciadoCambio").value = datos["2"];
					// vercerrarOpcionesDeRefinanciamiento("1")
					buscarDetallesVentaDevoluciones()
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


function irARefinanciamientoDesdeCambio(){
	vercerrarOpcionesDeRefinanciamiento("1")
	buscarDetallesVentaDevoluciones()
}
function calcular_cuota_desde_refinanciamiento() {
	var t = QuitarSeparadorMilValor(document.getElementById('inptPendienteVentaRefinanciadoCambio').value);
	var c = QuitarSeparadorMilValor(document.getElementById('inptCuotaNroCambio').value);
	

	if (isNaN(t) || t=="" ) {

	 t = QuitarSeparadorMilValor(document.getElementById('inptTotalVentaRefinanciadoCambio').value);
	}
	
	if (isNaN(c)) {

		document.getElementById('inptCuotaNroCambio').value = 1;
		document.getElementById('inptMonotCambio').value = document.getElementById('inptPendienteVentaRefinanciadoCambio').value;
		c = 0;
	}
	
	
	var c = parseFloat(c);
	var t = parseFloat(t);
	document.getElementById('inptMonotCambio').value = Math.round(t / c);
	//separadordemiles(document.getElementById('inpt_interes_pago_venta'))
	separadordemiles(document.getElementById('inptMonotCambio'))
}



function ImprimirDivTicketGarantia(){
	
	pagina="<!DOCTYPE html><html><head></head><body><div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >B&R Emprendimientos S.A.</p>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Recibo de Garantía</b></p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:63'><b>Nro Venta :</b></td>"
+"<td style=''>"+document.getElementById("inptNroVentaGarantia").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:38px'><b>Fecha Venta:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaGarantia").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:38px'><b>Fecha Recibido:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaRecibidoGarantia").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Producto :</b></td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td>"+document.getElementById("inptProductoDevolucionGarantia").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:70px'><b>Obsevación :</b></td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td>"+document.getElementById("inptObservacionGarantia").value+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Recibido por :</b></td>"
+"<td style=''>"+document.getElementById("lblUser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
"</div></body></html>"
var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
}

function ImprimirDivTicketGarantiaVerificacion(){
	
	pagina="<!DOCTYPE html><html><head></head><body><div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >B&R Emprendimientos S.A.</p>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>CONTROL DE GARANTIA</b></p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100%;text-align:left'><b>Nro Venta :</b>"
+document.getElementById("inptNroVentaGarantiaHistorial").value
+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Producto :</b></td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td>"+document.getElementById("inptProductoDevolucionGarantiaHistorial").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Obsevación :</b></td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td>"+observacionGarantiaTikect+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Enviado por :</b></td>"
+"<td style=''>"+document.getElementById("lblUser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
"</div></body></html>"
var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
}

function ImprimirDivTicketGarantiaEntrega(){
	
	pagina="<!DOCTYPE html><html><head></head><body><div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >B&R Emprendimientos S.A.</p>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>CONTROL DE GARANTIA</b></p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100%;text-align:left'><b>Nro Venta :</b>"
+document.getElementById("inptNroVentaGarantiaHistorial").value
+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Producto :</b></td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td>"+document.getElementById("inptProductoDevolucionGarantiaHistorial").value+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:83px'><b>Entregado por :</b></td>"
+"<td style=''>"+document.getElementById("lblUser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100%'><i>Boleta de control nro <b>"+idGarantiaModificar+"</b> , que garantiza que el producto a sido devuelto al propietario </i></td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
"</div></body></html>"
var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
}




function cambiarCabecerasTablaDevoluciones(d){
	var paginaCabecera=""
	if(d.value=="Garantia"){
		paginaCabecera="<tr>"
+"<td class='td_registro' style='width:10%;'>"
+"NRO FACTURA"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"FECHA"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"PRODUCTO"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"MOTIVO"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"LOCAL"
+"</td>"
+"</tr>"
	}
	if(d.value=="Cambio"){
			paginaCabecera="<tr>"
+"<td class='td_registro' style='width:10%;'>"
+"NRO FACTURA"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"FECHA"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"PRODUCTO"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"CAMBIADO POR"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"MOTIVO"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"LOCAL"
+"</td>"
+"</tr>"
	}
	if(d.value=="Devolucion"){
			paginaCabecera="<tr>"
+"<td class='td_registro' style='width:10%;'>"
+"NRO FACTURA"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"FECHA"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"PRODUCTO"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"MOTIVO"
+"</td>"
+"<td class='td_registro' style='width:10%;'>"
+"LOCAL"
+"</td>"
+"</tr>"
	}
	document.getElementById("tBodyCabeceraDevoluciones").innerHTML=paginaCabecera
}
function bloquearBuscarPorInformeDevolucion(d){
	document.getElementById('inptBuscarDevolucionesF1').disabled = true
	document.getElementById('inptBuscarDevolucionesF2').disabled = true
	document.getElementById('inptlocalInformeDevoluciones').disabled = true
	document.getElementById('inputSelectMotivoBuscarDevoluciones').disabled = true
	document.getElementById('inptBuscarDevolucionesF1').style.backgroundColor = "#cccc"
	document.getElementById('inptBuscarDevolucionesF2').style.backgroundColor = "#cccc"
	document.getElementById('inptlocalInformeDevoluciones').style.backgroundColor = "#cccc"
	document.getElementById('inputSelectMotivoBuscarDevoluciones').style.backgroundColor = "#cccc"
	if(d.value=="1"){
	document.getElementById('inptBuscarDevolucionesF1').disabled = false
	document.getElementById('inptBuscarDevolucionesF2').disabled = false
	document.getElementById('inptlocalInformeDevoluciones').disabled = false
	document.getElementById('inputSelectMotivoBuscarDevoluciones').disabled = false
	document.getElementById('inptBuscarDevolucionesF1').style.backgroundColor = ""
	document.getElementById('inptBuscarDevolucionesF2').style.backgroundColor = ""
	document.getElementById('inptlocalInformeDevoluciones').style.backgroundColor = ""
	document.getElementById('inputSelectMotivoBuscarDevoluciones').style.backgroundColor = ""
	}
	if(d.value=="2"){
	
	document.getElementById('inptlocalInformeDevoluciones').disabled = false
	document.getElementById('inputSelectMotivoBuscarDevoluciones').disabled = false

	document.getElementById('inptlocalInformeDevoluciones').style.backgroundColor = ""
	document.getElementById('inputSelectMotivoBuscarDevoluciones').style.backgroundColor = ""
	}
	if(d.value=="3"){
	document.getElementById('inptlocalInformeDevoluciones').disabled = false
	document.getElementById('inputSelectMotivoBuscarDevoluciones').disabled = false

	document.getElementById('inptlocalInformeDevoluciones').style.backgroundColor = ""
	document.getElementById('inputSelectMotivoBuscarDevoluciones').style.backgroundColor = ""
	}
	
	
}



var idDetalleGarantia = "";
function obtenerdatosabmGarantia(datostr) {


	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});

	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptCodDetalleGarantia').value = $(datostr).children('td[id="td_id_2"]').html();
	document.getElementById('inptNombreProductoDetalleOpcionGarantia').value = $(datostr).children('td[id="td_datos_1"]').html();
	idDetalleGarantia = $(datostr).children('td[id="td_id_2"]').html();
	vercerrarOpcionesGarantia("1")



}
function vercerrarOpcionesGarantia(d) {


	if (d == "1") {
		$("div[id=divOpcionesDetallesGarantia]").fadeIn(250)

	} else {
		$("div[id=divOpcionesDetallesGarantia]").fadeOut(250)
	}


}
function quitardegarantia() {

	if (idDetalleGarantia == "") {
		ver_vetana_informativa("FALTO SELCCIONAR UN REGITRO")
		return false;
	}
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "quitardegarantia")
	datos.append("cod_detalle", idDetalleGarantia)





	var OpAjax = $.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmdetalleventa.php",
		type: "post",
		cache: false,
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


				if (Respuesta == "UI") {

					ir_a_login()
					ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
					return false;



				}
				if (Respuesta == "NI") {
					ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
					return false;
                  }
				if (Respuesta == "camposvacio") {


					ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
					return false;



				}
				if (Respuesta == "EX") {


					ver_vetana_informativa("YA EXISTE UN DATO SIMILAR...")
					return false;



				}
				if (Respuesta == "exito") {

					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					vercerrarOpcionesGarantia("2")
					buscardetallesventa()


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

/*
ABM CUENTAS A COBRAR SOBRANTE
*/


function calcular_cuota() {
	var t = QuitarSeparadorMilValor(document.getElementById('inptTotalVentaPagosb').value);
	var c = QuitarSeparadorMilValor(document.getElementById('inptNroCuotasPagos').value);
	if (isNaN(c)) {

		document.getElementById('inptNroCuotasPagos').value = 1;
		document.getElementById('inptMontoPagoOpciones').value = document.getElementById('inptTotalVentaPagosb').value;
		c = 0;
	}
	var c = parseFloat(c);
	var t = parseFloat(t);
	document.getElementById('inptMontoPagoOpciones').value = t / c;
	//separadordemiles(document.getElementById('inpt_interes_pago_venta'))
	separadordemiles(document.getElementById('inptMontoPagoOpciones'))
}
function calcular_total_con_entrega() {
	var t = QuitarSeparadorMilValor(document.getElementById('inptTotalVentaPagos').value);
	var c = QuitarSeparadorMilValor(document.getElementById('inptEntregaPapo').value);
	if (isNaN(c)) {

		document.getElementById('inptEntregaPapo').value = 0;
		document.getElementById('inptTotalVentaPagosb').value = document.getElementById('inptTotalVentaPagos').value;
		c = 0;
	}
	var c = parseFloat(c);
	var t = parseFloat(t);
	if (c > t) {
		ver_vetana_informativa("EL MONTO NO PUEDE SER MAYOR AL TOTAL")
		document.getElementById('inptEntregaPapo').value = 0;

		document.getElementById('inptTotalVentaPagosb').value = document.getElementById('inptTotalVentaPagos').value;

		return
	}
	document.getElementById('inptTotalVentaPagosb').value = t - c;
	separadordemiles(document.getElementById('inptTotalVentaPagosb'))
	separadordemiles(document.getElementById('inptEntregaPapo'))
}



function MasFiltrosPagosCreditos(datos){
	if(document.getElementById("divMasFiltrosPagosCreditos").style.display==""){
		document.getElementById("divMasFiltrosPagosCreditos").style.display="none"
		datos.src="/GoodVentaAsisCap/iconos/filtros.png";
	}else{
		$("div[id=divMasFiltrosPagosCreditos]").slideDown(500);
		datos.src="/GoodVentaAsisCap/iconos/filtros2.png";
	}
}
function verificarcreditos() {

	var inptNroCuotasPagos = document.getElementById('inptNroCuotasPagos').value
	var inptMontoPagoOpciones = document.getElementById('inptMontoPagoOpciones').value
	var inptFechaInicioPapo = document.getElementById('inptFechaInicioPapo').value
	var inputSelectMetodo = document.getElementById('inputSelectMetodo').value
	var inptTotalPagadoOpcionesPago = document.getElementById('inptTotalPagadoOpcionesPago').value
	var inptTotalVentaPagos = document.getElementById('inptTotalVentaPagosb').value
	var inptInteresPagoOpciones = document.getElementById('inptInteresPagoOpciones').value
	var inptDiasGraciaPapo = document.getElementById('inptDiasGraciaPapo').value
	var inptEntregaPapo = document.getElementById('inptEntregaPapo').value



	if (inptTotalPagadoOpcionesPago > 0) {
		ver_vetana_informativa("NO SE PUEDE MODIFCAR LAS CUOTAS POR QUE ESTE YA CUENTA CON UN PAGO", "#")
		return false;
	}
	if (inptNroCuotasPagos == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE CUOTA", "#")
		return false;
	}

	if (inptMontoPagoOpciones == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MONTO DE PAGO", "#")
		return false;
	}
	if (inptFechaInicioPapo == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO DE PAGO", "#")
		return false;
	}
	if (inputSelectMetodo == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL METODO DE PAGO", "#")
		return false;
	}
	if (inptInteresPagoOpciones == "") {
		ver_vetana_informativa("FALTO INGRESAR EL INTERES DE PAGO", "#")
		return false;
	}
	if (inptDiasGraciaPapo == "") {
		ver_vetana_informativa("FALTO INGRESAR LOS DIAS DE GRACIA", "#")
		return false;
	}


	abmcreditos(inptNroCuotasPagos, inptMontoPagoOpciones, inptFechaInicioPapo, inputSelectMetodo, inptTotalVentaPagos, inptInteresPagoOpciones, inptDiasGraciaPapo, inptEntregaPapo, idFkVenta);
}
function abmcreditos(nroCuota, Monto, iniciopago, metodopago, total, interes, dias, entrega, cod_venta) {


	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "nuevo")
	datos.append("cod_venta", cod_venta)
	datos.append("Monto", Monto)
	datos.append("metodopago", metodopago)
	datos.append("iniciopago", iniciopago)
	datos.append("nroCuota", nroCuota)
	datos.append("total", total)
	datos.append("dias", dias)
	datos.append("interes", interes)
	datos.append("entrega", entrega)




	var OpAjax = $.ajax({

		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmcreditos.php",
		type: "post",
		cache: false,
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
		
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("")
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")

			return false;
		},
		success: function (responseText) {
			verCerrarEfectoCargando("")
			Respuesta = responseText;
			console.log(Respuesta)
			
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
				if (Respuesta == "camposvacio") {


					ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
					return false;



				}
				if (Respuesta == "EX") {


					ver_vetana_informativa("YA EXISTE UNA DATO SIMILAR...")
					return false;

				}
				if (Respuesta == "exito") {

					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					total = document.getElementById('inptTotalVentaPagos').value				
paginaticket=datos["2"]
if(entrega>0){
	totalPagado=entrega
}else{
	totalPagado=0;
}

total=QuitarSeparadorMilValor(total)
totalPagado=QuitarSeparadorMilValor(totalPagado)

var pendiente=total-totalPagado;
if(pendiente<0){
	pendiente=0;
}
//imprimirDivticket2(document.getElementById("inptSeleccTipoVenta").value,separadordemilesnumero(total),separadordemilesnumero(totalPagado),separadordemilesnumero(pendiente))

					buscarcreditos()


				}
				else {


					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")


				}
try {
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
			}


		}
	});


}






function calcularIntereseEnCuota() {
	var t = QuitarSeparadorMilValor(document.getElementById('inptCuotaAPagar').value);
	var i = QuitarSeparadorMilValor(document.getElementById('inptInteresAPagar').value);
	if (isNaN(i)) {
		i = 0
		document.getElementById('inptInteresAPagar').value = 0;

	}
	var pagado=QuitarSeparadorMilValor(totalPagadoCuota)
	var i = parseFloat(i);
	var t = parseFloat(t);
	var p = parseFloat(pagado);
	document.getElementById('inptMontoAPagar').value = (t + i)-p;

	separadordemiles(document.getElementById('inptInteresAPagar'))
	separadordemiles(document.getElementById('inptMontoAPagar'))
	document.getElementById('inptMontoClienteAPagar').value = document.getElementById('inptMontoAPagar').value;
}



function calcularDescuentoCuenta(){
	var totalDeuda = QuitarSeparadorMilValor(document.getElementById('inptTotalDeudaPago').value);
	var totaldescuento = QuitarSeparadorMilValor(document.getElementById('inptDescuentoCargaPago').value);
	
	
	if (isNaN(totalDeuda)) {
       ver_vetana_informativa("ERROR, TOTAL INVALIDO")
		return 
	}
	if (isNaN(totaldescuento)) {

		document.getElementById('inptDescuentoVentaTerminar').value = 0;
		totaldescuento = 0;
	}
	
	var d = parseFloat(totaldescuento);
	var t = parseFloat(totalDeuda);
	var ac=t-d
	document.getElementById('inptMontoCargaPago').value=separadordemilesnumero(ac);
	document.getElementById('inptDescuentoCargaPago').value=separadordemilesnumero(d);
	
}
function calcularDescuentoCuenta2(){
	var totalDeuda = QuitarSeparadorMilValor(document.getElementById('inptMontoAPagar').value);
	var totaldescuento = QuitarSeparadorMilValor(document.getElementById('inptDescuentoAPagar').value);
	

	if (isNaN(totalDeuda)) {
       ver_vetana_informativa("ERROR, TOTAL INVALIDO")
		return 
	}
	if (isNaN(totaldescuento)) {

		document.getElementById('inptDescuentoAPagar').value = 0;
		totaldescuento = 0;
	}
	
	var d = parseFloat(totaldescuento);
	var t = parseFloat(totalDeuda);
	var ac=t-d
	document.getElementById('inptMontoClienteAPagar').value=separadordemilesnumero(ac);
	document.getElementById('inptDescuentoAPagar').value=separadordemilesnumero(d);
	
}


/*
COMPRAS SOBRANTE
*/




/*
INFO
*/
var diaatrazado="";
var paginaticket="";
var pagado="";
var cuotasNro="";
var deudaActual="";
function imprimirDivticket(idCodVenta){
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
    document.getElementById('inptFechaPagoConfirmar').value=f.getFullYear()+"-"+mes+"-"+dia;
	
	var stylos =".divTicket {width:279px}.pTituloTicket1{ font-size: 20px; font-family: Arial;}.pTituloTicket2{font-size:14px; font-family: arial;line-height: 20px;}.tableTicket {	width:95%;font-family:arial;font-size:14px;margin-top:2px}.divSeparadorTicket {width:95%;	height:1px;background-color:#cccc}";
	
	pagina="<!DOCTYPE html><html><head><style type='text/css'>"+stylos+" </style> 	</head><body><div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >B&R Emprendimientos S.A.</p>"
+"<p class='pTituloTicket2'>"
+"RUC: 80114316-0 <br>"
+"Cel: <br>(0984) 145-450 <br>"
+"<br>Natalicio Talavera, Paraguay"
+"</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Numero :</b></td>"
+"<td style=''>"+document.getElementById("inptClienteVenta").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+document.getElementById("inptClienteVenta").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>RUC o C.I.:</b></td>"
+"<td style=''>"+document.getElementById("inptClienteVenta").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaPagoConfirmar").value+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>D. Atrasado</b></td>"
+"<td style=''>"+diaatrazado+" Día(s)</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100%'><b>Producto.</b></td>"
+"</tr>"
+"</table>"
+paginaticket
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Subtotal:</b></td>"
+"<td style=''>"+document.getElementById("inptTotalVenta").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Pagado:</b></td>"
+"<td style=''>"+pagado+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Cuota N.:</b></td>"
+"<td style=''>"+cuotasNro+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>D. Actual:</b></td>"
+"<td style=''>"+deudaActual+" Gs.</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
"</div></body></html>"
var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
 
}

function imprimirDivticket2(TipoVenta,totalVenta,totalPagado,pediente){
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
    document.getElementById('inptFechaPagoConfirmar').value=f.getFullYear()+"-"+mes+"-"+dia;
	var paginacuota="<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cuotas</b></td>"
+"<td style=''>"+document.getElementById("inptNroCuotasPagos").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Monto</b></td>"
+"<td style=''>"+document.getElementById("inptMontoPagoOpciones").value+"</td>"
+"</tr>"
+"</table>";
var paginacuotab ="<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cuota N.</b></td>"
+"<td style=''>ENTREGA / "+document.getElementById("inptNroCuotasPagos").value+"</td>"
+"</tr>"
+"</table>";
if(TipoVenta!="CREDITO"  ){
	paginacuota="";
	paginacuotab="";
}else{
	if( totalPagado=="0"){
		 paginacuotab ="<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cuotas N:</b></td>"
+"<td style=''>"+document.getElementById("inptNroCuotasPagos").value+"</td>"
+"</tr>"
+"</table>";
	}
}
	var stylos =".divTicket {width:300px}.pTituloTicket1{ font-size: 20px; font-family: Arial;}.pTituloTicket2{font-size:14px; font-family: arial;line-height: 20px;}.tableTicket {	width:95%;font-family:arial;font-size:14px;margin-top:2px}.divSeparadorTicket {width:95%;	height:1px;background-color:#cccc}";
	
	pagina="<style type='text/css'>"+stylos+" </style><div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >B&R Emprendimientos S.A.</p>"
+"<p class='pTituloTicket2'>"
+"RUC 80114316-5"
+"<br>Villarrica, py "
+"</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+document.getElementById("inptClienteVenta").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaPagoConfirmar").value+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+paginacuota
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Tipo Venta:</b></td>"
+"<td style=''>"+TipoVenta+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100%'><b>Producto.</b></td>"
+"</tr>"
+"</table>"
+paginaticket
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Total Venta:</b></td>"
+"<td style=''>"+totalVenta+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Pagado:</b></td>"
+"<td style=''>"+totalPagado+" Gs.</td>"
+"</tr>"
+"</table>"
+paginacuotab
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Pendiente:</b></td>"
+"<td style=''>"+pediente+" Gs.</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
"</div>"
var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
}


var paginaExtractoCuota="";

var paginaExtractoDetalle="";

function ImprimirExtracto() {
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
	if(document.getElementById("inptSeleccPuntoExpedicionVenta").value!=""){
		var nroF=document.getElementById("inptSeleccPuntoExpedicionVenta").value+"-"+document.getElementById("inptNroVenta").value
		}else{
		var nroF=document.getElementById("inptNroVenta").value
		}
   document.getElementById('pExtFechaImpresion').innerHTML=f.getFullYear()+"-"+mes+"-"+dia;
	// document.getElementById("pExtCliente").innerHTML=document.getElementById("inptClienteVenta").value
	// document.getElementById("pExtTipoVenta").innerHTML=document.getElementById("inptSeleccTipoVenta").value
	// document.getElementById("pExtNroVenta").innerHTML=nroF
	// document.getElementById("pExtFecha").innerHTML=document.getElementById("inptFechaVenta").value
	document.getElementById("pExTotalPagado").innerHTML=document.getElementById("inptTotalPagadoOpcionesPago").value
	document.getElementById("pExTotalDesc").innerHTML=document.getElementById("inptTotalDescuentoOpcionesPago").value
	document.getElementById("pExDeudaAct").innerHTML=document.getElementById("inptDeudaActual").value
	document.getElementById("pExTotalInter").innerHTML=document.getElementById("inptTotalInteres").value
	
	
	
	document.getElementById("pExtCliente").innerHTML=nombreClienteImprimir
	document.getElementById("pExtTipoVenta").innerHTML=TipoVentaClienteImprimir
	document.getElementById("pExtNroVenta").innerHTML=NroVentaClienteImprimir
	document.getElementById("pExtFecha").innerHTML=FechaClienteImprimir
	document.getElementById("tbExtCuotas").innerHTML= paginaExtractoCuota 
	var paginaProducto="";
	$("tr[name=tdDetalleVenta]").each(function(i, elementohtml){
        
		var nombre=$(elementohtml).children('td[id="td_datos_1"]').html();
		var costo=$(elementohtml).children('td[id="td_datos_3"]').html();
		var cantida=$(elementohtml).children('td[id="td_datos_4"]').html();
		var total=$(elementohtml).children('td[id="td_datos_5"]').html();
		 paginaProducto+="<table class='tableRegistroSearchRepor' border='0' cellspacing='0' cellpadding='0'>"
+"<tr >"
+"<td  style='width:10%' >"+nombre+"</td>"
+"<td  style='width:10%' >"+costo+"</td>"
+"<td  style='width:10%' >"+cantida+"</td>"
+"<td  style='width:10%' >"+total+"</td>"
+"</tr>"
+"</table>";
	   });
		document.getElementById("tbExtProducto").innerHTML=DetalleVentaClienteImprimir
	var documento=document.getElementById("DivImprimirExtr").innerHTML;

	 localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportInformes.html");
	 document.getElementById("DivImprimir").innerHTML = "";
	/* document.getElementById("DivImprimir").innerHTML = cabecera + pagina;
	imprimirDiv() */
}

function ordenimpresion(ventana){
	var pagina=""
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

if (ventana == "cheque") {

		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA INICIO:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptFechaCheque1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA FIN:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptFechaCheque2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>TOTAL:</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalMontoRegistoCheque").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >LISTADO DE CHEQUES</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tablacabeceraCheque").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_abm_Cheque").innerHTML
}
 
if (ventana == "Agenda") {

		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA INICIO:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarClieteImpagoF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA FIN:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscaClieteImpagoF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>CLIENTE:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarInfClienteImpago1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>COBRADOR:</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarInfClienteImpago2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >AGENDA DE CLIENTES</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloClienteImpago").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_Cliente_Impago").innerHTML
}
 
if (ventana == "comisionpagos") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha Inicio</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarComisionPagosEliminadosF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha Fin</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarComisionPagosEliminadosF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >PAGOS ELIMINADOSS</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImprePagosEliminado").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_pagos_eliminados_historial").innerHTML
}
if (ventana == "pagosralizados") {
		if(document.getElementById("inptSeleccPuntoExpedicionVenta").value!=""){
		var nroF=document.getElementById("inptSeleccPuntoExpedicionVenta").value+"-"+document.getElementById("inptNroVenta").value
		}else{
		var nroF=document.getElementById("inptNroVenta").value
		}
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Nro Factura</b></p>"
+"<p class='pTituloC' >"+nroF+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Cliente</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptClienteVenta").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Cuota Nro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptCuotaNroOpcionespagos").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Cobrado</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptMontoOpcionpagos").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >PAGOS REALIZADOS</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tbTituloImprePagosRealizados").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_historial_pagos").innerHTML
}


if (ventana == "historialventa") {
		
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Tipo Venta</b></p>"
+"<p class='pTituloC' >"+ $("select[id=inptTipoVentaHistorialVenta]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Estado Cuenta</b></p>"
+"<p class='pTituloC' >"+ $("select[id=inptCuentaHistorialVenta]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Zona</b> </p>"
+"<p class='pTituloC' >"+ $("select[id=inputSelectZonaInfHistorialVenta]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local</b></p>"
+"<p class='pTituloC' >"+ $("select[id=inptlocalHistorialVenta]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de Impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >HISTORIAL VENTA</h1><br></center>"
paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptRegistroNroHistorialVenta").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Venta</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalVentaHistorialVenta").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Pagado</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalPagosHistorialVenta").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Pendiente</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalPendienteHistorialVenta").value+"</p>"
+"</td>"
+"</tr>"
+"</table>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tbTituloImpreHistorialVenta").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_historial_venta").innerHTML
}
if (ventana == "cuentasacobrar") {
		
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local</b></p>"
+"<p class='pTituloC' >"+ $("select[id=inptlocalCuentasAcobra]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Zona</b></p>"
+"<p class='pTituloC' >"+ $("select[id=inputSelectZonaInfCuentasAcobrar]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptBuscarCuentasAcobrarF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+  document.getElementById("inptBuscarCuentasAcobrarF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de Impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >CUENTAS A COBRAR</h1><br></center>"
paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Registro Cargado</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptRegistroRegistrocargadoCuentaAcobrar").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Deuda</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptRegistroNroHistorialTotalADeudad").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"</td>"
+"</tr>"
+"</table>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreCuenta").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_cuentas_a_cobrar").innerHTML
}
if (ventana == "cuentasacobrardetalles") {
		
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local</b></p>"
+"<p class='pTituloC' >"+ $("select[id=inptlocalCuentasAcobrainforme]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Zona</b></p>"
+"<p class='pTituloC' >"+ $("select[id=inputSelectZonaInfCuentasAcobrarinforme]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptBuscarCuentasAcobrarF1informe").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+  document.getElementById("inptBuscarCuentasAcobrarF2informe").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de Impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >CUENTAS PENDIENTES</h1><br></center>"
paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registros</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptRegistroNroHistorialCuentaAcobrarinforme").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total a Cobrar</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptRegistroNroHistorialTotalACobrarinforme").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"</td>"
+"</tr>"
+"</table>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=""
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_cuentas_a_cobrar_informe").innerHTML
}
if (ventana == "solicitudes") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Filtro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarSolicitud").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarSolicitudF1").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarSolicitudF2").value+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Local</b> </p>"
+"<p class='pTituloC' >"+ $("select[id=inptlocalSolicitudBuscar]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Estado</b> </p>"
+"<p class='pTituloC' >"+ $("select[id=inptSeleccEstadoBuscarSolicitud]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:15%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >SOLICITUDES</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreSolicitud").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_abm_Solicitud").innerHTML
}
if (ventana == "sueldos") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Filtro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarSueldo").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarSueldoF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarSueldoF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalTotalSueldo").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >SUELDOS DE FUNCIONARIOS</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreSueldo").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_abm_Sueldo").innerHTML
}
if (ventana == "gastos") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptlocalMisGastosBusca]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarGastoF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarGastoF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalGasto").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >GASTOS REALIZADOS</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreGastos").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_abm_gasto").innerHTML
}
if (ventana == "productovendidos") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptlocalInformeProductosVendidos]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Categoria</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptCategoriaProductoInformeProductosVendidos]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarProductosVendidosF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarProductosVendidosF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >PRODUCTOS VENDIDOS</h1><br></center>"

paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Registro </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalRegistroProductosVendidos").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Venta</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalRegistroTotalVentas").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Invetido</b> </p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalVentasInvertido").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"</td>"
+"</tr>"
+"</table>"

document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreVendidos").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_comision_productosVendidos").innerHTML
}
if (ventana == "productoscomprados") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptlocalInformeProductosComprados]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Categoria</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptCategoriaInformeProductosComprados]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarProductosCompradosF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarProductosCompradosF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >PRODUCTOS COMPRADOS</h1><br></center>"

paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Registro </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalRegistroProductoComprados").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Compra</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalRegistroProductosComprados").value+"</p>"
+"</td>"
+"<td style='width:60%;text-align:left'>"
+"</td>"
+"</tr>"
+"</table>"

document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreComprados").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_comision_productosComprados").innerHTML
}
if (ventana == "arqueo") {
	
		pagina =""
paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Registro </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalRegistoArqueo").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Compra</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalArqueo").value+"</p>"
+"</td>"
+"<td style='width:60%;text-align:left'>"
+"</td>"
+"</tr>"
+"</table>"

document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreArqueo").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_arqeo").innerHTML
}
if (ventana == "arqueocaja") {
	
	
	pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>LOTE:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarLoteVistaApCie").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA INICIO:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarVistaApCie5").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA FIN:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarVistaApCie6").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>USUARIO:</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarVistaApCie2").value+"</p>"
+"</td>"

+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >CONSULTAR CAJA</h1><br></center>"
	
	
		// pagina ="<center><h1 class='pTituloD' >CONSULTAR CAJA</h1><br></center>"
paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Efectivo </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalEfectivoConsultarCaja").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Tarjeta</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalTarjetaConsularCaja").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Ingreso</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalIngresoConsularCaja").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Egreso</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalEgresoConsularCaja").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Caja</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalConsularCaja").value+"</p>"
+"</td>"
+"</tr>"
+"</table>"

document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreConsultarCaja").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_Consultar_caja").innerHTML
}
if (ventana == "catalago") {
document.getElementById("divCabeceraImpresiones").innerHTML="<br>"
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreCatalago").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_comision_productos_catalago").innerHTML
}
if (ventana == "comisioncobrador") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Zona</b></p>"
+"<p class='pTituloC' >"+$("select[id=inputSelectZonaComisionCobrador]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Cobrador</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptCobradorComision").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarComisionCobradorF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarComisionCobradorF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >COMISIÓN COBRADOR</h1><br></center>"

paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Registro </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalRegistoComision").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalComision").value+"</p>"
+"</td>"
+"<td style='width:60%;text-align:left'>"
+"</td>"
+"</tr>"
+"</table>"

document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreComisionCobrador").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_comision_cobrador").innerHTML
}
if (ventana == "comisionvendedor") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Vendedor</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarComisionVendedor1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarComisionVendedorF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarComisionVendedorF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >COMISIÓN VENDEDOR</h1><br></center>"

paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Registro </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalRegistoComisionVendedor").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalVentaComisionVendedor").value+"</p>"
+"</td>"
+"<td style='width:60%;text-align:left'>"
+"</td>"
+"</tr>"
+"</table>"

document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreComisionVendedor").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_comision_vendedor").innerHTML
}
if (ventana == "metasvendedor") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarMetasF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarMetasF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptlocalMetas]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >VENTAS DE VENDEDORES</h1><br></center>"

paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Ventas </b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptTotalVentaMetas").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Recaudado</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalRegistoMetas").value+"</p>"
+"</td>"
+"<td style='width:60%;text-align:left'>"
+"</td>"
+"</tr>"
+"</table>"

document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreMetasVendedor").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_vendedor_metas").innerHTML
}
if (ventana == "devolucion") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Buscador por</b></p>"
+"<p class='pTituloC' >"+$("select[id=inputSelectMotivoBuscarDevoluciones]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptlocalInformeDevoluciones]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 1</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarDevolucionesF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha 2</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarDevolucionesF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >INFORME DE DEVOLUCIONES</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreDevolucion").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_comision_devolucion").innerHTML
}
if (ventana == "inventario") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Buscador por</b></p>"
+"<p class='pTituloC' >"+$("select[id=inputSelectTipoBuscarInvetario]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Categoria</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptCategoriaProductoBuscarInventario]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Marca</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptMarcasProductoBuscarInventario]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptlocalProductoBuscarInventario]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >INVENTARIO</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreInventario").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_comision_productosInventario").innerHTML
}
if (ventana == "ProductosDespachados") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha Inicio</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarInfProdutosDespachadosF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha Fin</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarInfProductosDespachadosF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Enviado de</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptlocal1InformeProductoDespachado]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Enviado a </b></p>"
+"<p class='pTituloC' >"+$("select[id=inptlocal2InformeProductoDespachado]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >PRODUCTOS DESPACHADOS</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdImpresionDespachado").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_historial_producto_despachado").innerHTML
}
if (ventana == "compraseliminadas") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha Inicio</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarComprasEliminadosF1").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha Fin</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarComprasEliminadosF2").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Nro Compra</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptBuscarComprasEliminados3").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >COMPRAS ELIMINADAS</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloImpreComprasEliminado").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_compras_eliminados_historial").innerHTML
}
if (ventana == "ClienteInactivo") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>ZONA :</b></p>"
+"<p class='pTituloC' >"+$("select[id=inputSelectZonaComisionClientesInactivos]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA IMPRESION :</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b></b></p>"
+"<p class='pTituloC' ></p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b></b></p>"
+"<p class='pTituloC' ></p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >CLIENTE INACTIVOS</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("Contenedorclienteinactivo").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_clientes_inactivos").innerHTML
}
if (ventana == "ClienteMoroso") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>TIPO :</b></p>"
+"<p class='pTituloC' >"+$("select[id=inputSelectTipoBuscarMoroso]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA IMPRESION :</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>ZONA:</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptZonaMoroso]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>LOCAL:</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptlocalMoroso]").children(":selected").text()+"</p>"
+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >CLIENTE MOROSOS</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdTituloClienteMoroso").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_Cliente_Moroso").innerHTML
}

if (ventana == "Presupuesto") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>ENTREGA: :</b></p>"
+"<p class='pTituloC' style='font-size: 20px;' >"+document.getElementById("inptEntregaPresupuesto").value+" Gs.</p>"
+"</td>"
+"<td style='width:40%;text-align:left'>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>FECHA IMPRESION:</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"

+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >PRESUPUESTO</h1><br></center>"

var piedepagina="<div style='text-align: -webkit-right;'> <table style='width:40%;' class='tableCabeceraRegistro'>"
+"<tbody>"
+"<tr>"
+"<td  style='width:10%;'>"
+"<p class='pTituloC' style='color: aliceblue;' >TOTAL PRESUPUESTO :</p>"
+"<p class='pTituloC' style='font-size: 20px;color: #f3efef;' >"+document.getElementById("inptTOTALPresupuestoFORM").value+" Gs.</p>"
+"</td>"
+"</tr>"
+"</tbody>"
+"</table> </div>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=""
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("tdTablaPresupuesto").innerHTML

document.getElementById("divPieImpresiones").innerHTML=piedepagina

}


if (ventana == "SolicitudCredito") {
	
		pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local:</b></p>"
+"<p class='pTituloC' >"+$("select[id=inptlocalsolicitudCredito]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Zona:</b></p>"
+"<p class='pTituloC'  >"+$("select[id=inptBuscarAbmsolicitudCredito4]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Estado</b></p>"
+"<p class='pTituloC'  >"+$("select[id=inptBuscarAbmsolicitudCredito5]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:40%;text-align:left'>"

+"</td>"
+"</tr>"
+"</table><br><br><center><h1 class='pTituloD' >SOLICITUD CREDITO</h1><br></center>"
document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tbSolicitudCredito").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_abm_solicitudCredito").innerHTML
}


	
	var documento=document.getElementById("DivImpresiones").innerHTML;

	 localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "reporte");
	 window.open("/GoodVentaAsisCap/system/reportInformes.html");

}


function imprimirexpedientes(){
	var pagina=""
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

		
			
		document.getElementById("pExpCliente").innerHTML=document.getElementById("inptBuscarInfExpedientefiltro").value
		document.getElementById("pExpDocumento").innerHTML=document.getElementById("inptBuscarInfExpedienteNroDocumento").value
		document.getElementById("pExpTelef").innerHTML=document.getElementById("inptBuscarInfExpedienteNroTelef").value
		document.getElementById("pExpTotalVenta").innerHTML=document.getElementById("inptTotalExpVenta").value
		document.getElementById("pExpTotalPagado").innerHTML=document.getElementById("inptTotalPagExpVenta").value
		document.getElementById("pExpFechaImpresion").innerHTML=fechaimpresion
		
		document.getElementById("tdTituloExp1").innerHTML=document.getElementById("tdTituloImpreExp1").innerHTML
		document.getElementById("tbDatosExp1").innerHTML=document.getElementById("table_historial_expediente_ventas").innerHTML
		document.getElementById("tbPieExp1").innerHTML="<br><table class='TableRepor0'><tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptRegistroNroExpVenta").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Ventas</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalExpVenta").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Pagado</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalPagExpVenta").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Deuda Actual</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalDeudaExpVenta").value+"</p>"
+"</td>"
+"</tr></table><br>"
		
		document.getElementById("tdTituloExp2").innerHTML=document.getElementById("tdTituloImpreExp2").innerHTML
		document.getElementById("tbDatosExp2").innerHTML=document.getElementById("table_historial_expediente_ventas_canceladas").innerHTML
		document.getElementById("tbPieExp2").innerHTML="<br><table class='TableRepor0'><tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptRegistroNroExpVentaCancelado").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Ventas</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalExpVentaCancelado").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Pagado</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalPagExpVentaCancelado").value+"</p>"
+"</td>"
+"<td style='width:40%;text-align:left'>"
+"</td>"
+"</tr></table><br>"
		
		document.getElementById("tdTituloExp3").innerHTML=document.getElementById("tdTituloImpreExp3").innerHTML
		document.getElementById("tbDatosExp3").innerHTML=document.getElementById("table_historial_expediente_productos_comprados").innerHTML
			document.getElementById("tbPieExp3").innerHTML="<br><table class='TableRepor0'><tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptRegistroNroExpProductosComprados").value+"</p>"
+"</td>"
+"<td style='width:80%;text-align:left'>"
+"</td>"
+"</tr></table><br>"

		// document.getElementById("tdTituloExp4").innerHTML=document.getElementById("tdTituloImpreExp4").innerHTML
		// document.getElementById("tbDatosExp4").innerHTML=document.getElementById("table_historial_expediente_cambios").innerHTML
			// document.getElementById("tbPieExp4").innerHTML="<br><table class='TableRepor0'><tr>"
// +"<td style='width:20%;text-align:left'>"
// +"<p class='pTituloC'><b>Total Registro</b></p>"
// +"<p class='pTituloC' >"+document.getElementById("inptRegistroNroExpCambios").value+"</p>"
// +"</td>"
// +"<td style='width:80%;text-align:left'>"
// +"</td>"
// +"</tr></table><br>"

		document.getElementById("tdTituloExp5").innerHTML=document.getElementById("tdTituloImpreExp5").innerHTML
		document.getElementById("tbDatosExp5").innerHTML=document.getElementById("table_historial_expediente_Extraidos").innerHTML
		document.getElementById("tbPieExp5").innerHTML="<br><table class='TableRepor0'><tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptRegistroNroExpExtr").value+"</p>"
+"</td>"
+"<td style='width:80%;text-align:left'>"
+"</td>"
+"</tr></table><br>"

		document.getElementById("tdTituloExp6").innerHTML=document.getElementById("tdTituloImpreExp6").innerHTML
		document.getElementById("tbDatosExp6").innerHTML=document.getElementById("table_historial_expediente_pagos").innerHTML
		document.getElementById("tbPieExp6").innerHTML="<br><table class='TableRepor0'><tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptRegistroNroExpPagos").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Descuento</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalDescExpPa").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Interes</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalInteresExpPa").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Pagado</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalPagosExpPa").value+"</p>"
+"</td>"
+"</tr></table><br>"

		document.getElementById("tdTituloExp7").innerHTML=document.getElementById("tdTituloImpreExp7").innerHTML
		document.getElementById("tbDatosExp7").innerHTML=document.getElementById("table_historial_expediente_pagos_pendientes").innerHTML
			document.getElementById("tbPieExp7").innerHTML="<br><table class='TableRepor0'><tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+document.getElementById("inptRegistroNroExpPagosPend").value+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Deuda</b> </p>"
+"<p class='pTituloC' >"+document.getElementById("inptTotalDeudaExpPe").value+"</p>"
+"</td>"
+"<td style='width:60%;text-align:left'>"
+"</td>"
+"</tr></table><br>"


	var documento=document.getElementById("DivImprimirExpediente").innerHTML;

	 localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "reporte");
	 window.open("/GoodVentaAsisCap/system/reportInformes.html");

}

function b64EncodeUnicode(str) {
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
        return String.fromCharCode('0x' + p1);
    }));
}
function b64_to_utf8( str ) {
  return decodeURIComponent(escape(window.atob( str )));
}
function imprimirDiv() {
	/*var ficha=document.getElementById('divimpr');
	  var ventimp=window.open();
	   ventimp.document.write(ficha.innerHTML);;
	  ventimp.print();
	  ventimp.close();
	  
		  document.open();
		  document.write(ficha.innerHTML);
		  document.close();
		  document.getElementById("DivTablas").innerHTML=""*/


	var contenido = document.getElementById("DivImprimir").innerHTML;
	var contenidoOriginal = document.body.innerHTML;

	document.body.innerHTML = contenido;

	window.print();

	document.body.innerHTML = contenidoOriginal;
	document.getElementById("DivImprimir").innerHTML = "";
}

/*
INFO
*/



function ver_vetana_informativa(titulo) {

	document.getElementById('lbltitulomensaje').innerHTML = titulo
	document.getElementById('div_principal_info').style.display = ''
    document.getElementById("btnocultarinfo").focus()
}


function ocultarmensaje() {

	document.getElementById('lbltitulomensaje').innerHTML = ""
	document.getElementById('div_principal_info').style.display = 'none'
}

function verCerrarEfectoCargando(d) {
	document.getElementById("div_principal_info_carga").style.display = 'none'
	if (d == "1") {
		document.getElementById("div_principal_info_carga").style.display = ''
		document.getElementById("lbltitulomensaje_b").innerHTML = 'Cargando...'
	}

}
function verCerrarEfectoCargandoVerificacion(d) {
	document.getElementById("div_principal_info_carga").style.display = 'none'
	if (d == "1") {
		document.getElementById("div_principal_info_carga").style.display = ''
		document.getElementById("lbltitulomensaje_b").innerHTML = 'Verificando...'
	}

}


/*OTROS*/

function QuitarSeparadorMil(inputs) {
	var i = inputs.value.toString();
	i = i.replace(/\./g, '')
	i = i.replace(',', '.')
	return i;


}
function QuitarSeparadorMilValor(inputs) {
	try {
			var i = inputs.toString();
	i = i.replace(/\./g, '')
	i = i.replace(',', '.')
	return i;
			} catch (error) {
				return "0";
			}



}
function separadordemiles(input) {

	var num = input.value.replace(/\./g, '');
	if (!isNaN(num)) {
		var num2 = num.toString().split('.');
		var thousands = num2[0].split('').reverse().join('').match(/.{1,3}/g).join('.');
		var decimals = (num2[1]) ? ',' + num2[1] : '';

		var answer = thousands.split('').reverse().join('') + decimals;
		input.value = answer
	} else {
		/*alert('Esto no es un número')
		//input.value=input.value.replace(/[˄\d\.]*g,'');
		 asi va antes de la /g */

	}

}
function separadordemilesnumero(input) {

	var num = input.toString().replace(/\./g, '');
	if (!isNaN(num)) {
		var num2 = num.toString().split('.');
		var thousands = num2[0].split('').reverse().join('').match(/.{1,3}/g).join('.');
		var decimals = (num2[1]) ? ',' + num2[1] : '';

		var answer = thousands.split('').reverse().join('') + decimals;
		input = answer
	} else {
		/*alert('Esto no es un número')
		//input.value=input.value.replace(/[˄\d\.]*g,'');
		 asi va antes de la /g */

	}
	return input;
}



/*
Control de acceso 
*/
function controlacceso(frm,accion){
	
	if(accesosuser[frm][accion]!="SI"){
		
		  ver_vetana_informativa("NO TIENES PERMISO PARA ACCEDER")
		  return false;
	}else{
		return true;
	}
	
	
	
}

function manejadordeerroresjquery(error,textStatus,funcion){
	if (error === 0) {

    ver_vetana_informativa('VERIFIQUE SU CONECCIÓN A INTERNET');

  } else if (error == 404) {

    ver_vetana_informativa('DIRECCIÓN URL INCORRECTO');

  } else if (error == 500) {

   ver_vetana_informativa('ERROR INTERNO DEL PHP');

  } else if (textStatus === 'parsererror') {

    ver_vetana_informativa('Requested JSON parse failed.');

  } else if (textStatus === 'timeout') {

  ver_vetana_informativa('ERROR, TIEMPO AGOTADO');
  
  } else if (textStatus === 'abort') {

    ver_vetana_informativa('ERROR, FUNCIÓN ABORTADO');

  } else {

    alert('Uncaught Error: ' + jqXHR.responseText);

  }
	
}




var NombreClienteSC =""
var DereccionClienteSC =""
var ReferenciaClienteSC =""
var ZonaClienteSC =""
var FechaNacCLienteSC =""
var EdadClienteSC =""
var NroTelClienteSC =""
var NroWharsappSC =""
var ViviendaClienteSC =""
var EstadoCivilClienteSC =""
var ciClienteSC =""
/////////////////////
var LucarTrabajoClienteSC =""
var DireccionTrabajoClienteSC =""
var TelefonoTrabajoClienteSC =""
var CargoClienteSC =""
var SalarioClienteSC =""
var AntiguedadClienteSC =""
////////////////////
var NombreGaranteSC =""
var CIGaranteSC =""
var DireccionGaranteSC =""
var ReferenciaGaranteSC =""
var NroTelGaranteSC =""
var LugarTrabajoGranteSC =""
var AntiguegagGatanteSC =""
var SalarioGaranteSC =""
var DivProductoSC =""
var DivReferenciaSC =""



function imprimirSolicitudCredito(){


var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()+1
	if(mes<10){
		mes="0"+mes;
	}
	var anho =f.getFullYear()
	
	var emision= f.getFullYear()+"-"+mes+"-"+dia;
	

	var pagina="<table style='width:95%'>"
+"<tr>"
+"<td style='width:50%;text-align:left'>"
+"<p class='pTituloC' style='font-size: 15px;'><b>Señores FLEYKOOP.</b> </p>"
+"</td>"
+"<td style='width:50%;text-align:left'>"
+"</td>"
+"</tr>"
+"</table>"
+"<div style='width:95%' style='text-align:left'>"
+"<p class='pTituloC' style=' font-size: 9px;text-align:left;text-align: justify; line-height: 15px;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Solocito una linea de crédito para la adquisición de vuestra empresa. Del mismo modo acepto las condiciones y términos estipulados por ella, así como la política de descuentos de mis haberes, en caso que la compra sea vía descuento. Por lo tanto autorizo a FLEYKOOP a descontar el monto de mis cuotas, así como de los intereses en caso de atraso de mis cuotas a través del medio que la empresa crea conveniente, independientemente al motivo del no descuento. Ante cualquier consulta o discrepancia en las cuotas descontadasarreglaré dicha situación con la casa comercial<b>&nbsp&nbsp</b>.---------</p>"

+"<p class='pTituloC' style=' font-size: 9px;text-align:left; text-align: justify; line-height: 15px;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp De conformidad a lo establecido en el Art. 5 inc. A y de conformidad con los dispuesto en la ley 1682, autorizo suficientemente y otorgo mandato suficiente  a la empresa FLEYKOOP  a obtener información de mi persona através de cualquier persona, entidad o empresa que procesa y difunda ese yipo de informción<b>&nbsp&nbsp</b>.---------</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px; background-color: #000c;width: 100%;'></div>"
+"</div>"
+"<p class='pTituloC'><b>DATOS PERSONALES</b> </p>"
+"<div  class='divMenuf'>"
+"<table style='width:90%;'><tr><td style='width:50%;'>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Nombre y Apellido : <b id='inpNombreCliente'>"+NombreClienteSC+"</b> </p> </td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >CI : <b >"+ciClienteSC+"</b> </p></td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Direccion : <b id='inpDireccionCliente'>"+DereccionClienteSC+"</b> </p></td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Referencia : <b id='inpReferenciaCliente'>"+ReferenciaClienteSC+"</b></p> </td></tr></table>"
+"<table style='width:100%'><tr><td style='width:50%'> <p class='pTituloW' >Fecha Nac.: <b id='inpFechaNacCliente'>"+FechaNacCLienteSC+"</b> </p> </td><td style='width:50%'> <p class='pTituloW' >Edad: <b id='inpEdadCliente'>"+EdadClienteSC+"</b> </p> </td></tr></table>"
+"</td><td style='width:50%;'>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Zona : <b id='inpZonaCliente'>"+ZonaClienteSC+"</b> </p></td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Nro Tel : <b id='inpNroTelCliente'>"+NroTelClienteSC+"</b> </p> </td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Nro Whatsapp : <b id='inpNroWhatsappCliente'>"+NroWharsappSC+"</b> </p></td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Vivienda : <b id='inpViviendaCliente'>"+ViviendaClienteSC+"</b></p> </td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Estado civil : <b id='inpEcivilCliente'>"+EstadoCivilClienteSC+"</b> </p></td></tr></table></td></tr></table></div>"

+"<p class='pTituloC'><b>DATOS LABORALES</b> </p>"
+"<div  class='divMenuf'>"
+"<table style='width:90%;'><tr><td style='width:50%;'>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Lugar de trabajo : <b id='inpLtrabajoCliente' >"+LucarTrabajoClienteSC+"</b> </p> </td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Direccion de trabajo : <b id='inpDtrabajoCliente'>"+DireccionTrabajoClienteSC+"</b> </p></td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Telefono de trabajo : <b id='inpTelTrabajoCliente'>"+TelefonoTrabajoClienteSC+"</b></p> </td></tr></table>"

+"</td><td style='width:50%;'>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Cargo: <b id='inpCargoCliente'></b> "+CargoClienteSC+"</p> </td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Salario : <b id='inpSalarioCliente'>"+SalarioClienteSC+"</b> </p></td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Antigüedad : <b id='inpAntiguedadCliente'>"+AntiguedadClienteSC+"</b></p> </td></tr></table>"
+"</td></tr></table></div>"
+"<p class='pTituloC'><b>REFERENCIA COMERCIALES Y PERSONALES</b> </p>"
+"<div  class='divMenuf' id='DivReferenciaComPer'>"+DivReferenciaSC+" "
+"</div>"
+"<p class='pTituloC'><b>DATOS DEL GARANTE</b> </p>"
+"<div  class='divMenuf'>"
+"<table style='width:90%;'><tr><td style='width:50%;'>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Nombre y Apellido : <b id='inpNombreGarante'>"+NombreGaranteSC+"</b> </p> </td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >CI : <b id='inpCIGarante'> "+CIGaranteSC+"</b> </p></td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Dirección : <b id='inpDireccionGarante'>"+DireccionGaranteSC+"</b></p> </td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Referencia : <b id='inpReferenciaGarante'>"+ReferenciaGaranteSC+"</b> </p></td></tr></table>"
+"<table style='width:100%'><tr><td style='width:50%'> <p class='pTituloW' >Nro Tel.: <b id='inpNroTelGarante'>"+NroTelGaranteSC+"</b> </p> </td> </tr></table>"
+"</td><td style='width:50%;'>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Lugar de trabajo : <b id='inpLugarTGarante'>"+LugarTrabajoGranteSC+"</b> </p> </td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Antigüedad : <b id='inpAntiguedadGarante'>"+AntiguegagGatanteSC+"</b> </p></td></tr></table>"
+"<table style='width:100%'><tr><td style='width:100%'> <p class='pTituloW' >Salario : <b id='inpSalarioGarante'>"+SalarioGaranteSC+"</b></p> </td></tr></table>" 
+"</td></tr></table></div>"
+"<p class='pTituloC'><b>PRODUCTOS Y FORMA DE PAGO</b> </p>"
+"<div  class='divMenuf' id='DivProForPA'> "+DivProductoSC+""
+" </div>"
+"<br>"
+"<table style='width:90%'>"
+"<tr>"
+"<td style='width:33%;text-align:left'>"
+"<p class='pTituloC' style='text-align:center'><b>-------------------------------------</b></p>"
+"<p class='pTituloC' style='text-align:center'><b>DEUDOR</b></p>"
+"</td>"
+"<td style='width:33%;text-align:left'>"
+"<p class='pTituloC' style='text-align:center'><b>-------------------------------------</b></p>"
+"<p class='pTituloC' style='text-align:center'><b>GARANTE</b></p>"

+"</td>"
+"<td style='width:33%;text-align:left'>"
+"<p class='pTituloC' style='text-align:center'><b>-------------------------------------</b></p>"
+"<p class='pTituloC' style='text-align:center'><b>VERIFICACOR</b></p>"
+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px; background-color: #000c;width: 90%;'></div>"
+"<p class='pTituloC' style='font-size: 15px;'><b>USO INTERNO DE LA EMPRESA</b></p>"
document.getElementById("divSolicitudCredito").innerHTML=pagina;
 var documento= document.getElementById("DivImprimirSolicitudCredito").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "reporte");
	
	  var URL= "/GoodVentaAsisCap/system/report.html"
        window.open(URL, 'Imprimir', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,left = 0');
}




function ImprimirReciboDinero(CuotasNro,Pagado,NombreCliente,CiCliente,NroRecibo){
	
var t=QuitarSeparadorMilValor(Pagado);
  var totaletrasRecibo=numeroALetras(t, {
  plural: 'GUARANIES',
  singular: 'GUARANIES',
  centPlural: 'GUARANIES',
  centSingular: 'GUARANIES'
});	
	
	
	
pagina="<!DOCTYPE html><html><head></head><body><div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicketrecibo' >"
+"<div style='height:130px' ></div>"
+"<table class='tableTicketrecibo'>"
+"<tr>"
+"<td style='width:65%'> </td>"
+"<td style='width:35%;font-size:30px'>"+Pagado+"##</td>"
+"</tr>"
+"</table>"
+"<div style='height:150px' ></div>"

+"<table class='tableTicketrecibo'>"
+"<tr>"
+"<td style='width:12%'></td>"
+"<td style='width:68%;font-size:15px'>"+NombreCliente+"</td>"
+"<td style='width:20%;font-size:15px'>"+CiCliente+"</td>"
+"</tr>"
+"</table>"
+"<div style='height:45px' ></div>"
+"<table class='tableTicketrecibo'>"
+"<tr>"
+"<td style='width:25%'></td>"
+"<td style='width:75%;font-size:15px'> "+totaletrasRecibo+"</td>"
+"</tr>"
+"</table>"

+"<div style='height:85px' ></div>"
+"<table class='tableTicketrecibo'>"
+"<tr>"
+"<td style='width:15%'></td>"
+"<td style='width:85%;font-size:15px'> "+CuotasNro+" -- "+NroRecibo+"</td>"
+"</tr>"
+"</table>"


+"<div style='height:317px' ></div>"


+"<table class='tableTicketrecibo'>"
+"<tr>"
+"<td style='width:65%'> </td>"
+"<td style='width:35%;font-size:30px'>"+Pagado+"##</td>"
+"</tr>"
+"</table>"
+"<div style='height:150px' ></div>"

+"<table class='tableTicketrecibo'>"
+"<tr>"
+"<td style='width:12%'></td>"
+"<td style='width:68%;font-size:15px'>"+NombreCliente+"</td>"
+"<td style='width:20%;font-size:15px'>"+CiCliente+"</td>"
+"</tr>"
+"</table>"
+"<div style='height:45px' ></div>"
+"<table class='tableTicketrecibo'>"
+"<tr>"
+"<td style='width:25%'></td>"
+"<td style='width:75%;font-size:15px'> "+totaletrasRecibo+"</td>"
+"</tr>"
+"</table>"

+"<div style='height:85px' ></div>"
+"<table class='tableTicketrecibo'>"
+"<tr>"
+"<td style='width:15%'></td>"
+"<td style='width:85%;font-size:15px'> "+CuotasNro+" -- "+NroRecibo+"</td>"
+"</tr>"
+"</table>"

+"</div>"
+"</center>"
"</div></body></html>"

	
var ficha=pagina+"<br><br><br>"+pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportRecibo.html");
	 document.getElementById("DivImprimir").innerHTML = "";
 
}

let TituloRecibo="";


function ReImprimirDivTickeFacturaPago(Fecha,Cajero,CuotasNro,Pagado,DiasAtrazado,NombreCliente,CiCliente,NroRecibo,tipoventa,totalInteres,deudaActual,totalpagado,totaldescuento,TotalVenta,InteresActual,deudaActualsininteres){


if(cod_localFKUSer =="3" || cod_localFKUSer =="4"){
	TituloRecibo="CLINIDENT" ;
}else{
	TituloRecibo="ASISCAP";
}


pagina="<br><div style='background-color:#fff;'>"
+"<center>"

+"<div class='divTicket' style='width: 90%;border: solid 1px;border-radius: 10px; min-height: 450px;' > "
+"<center><img src='/GoodVentaAsisCap/iconos/iconoEmpresa.JPG' style='display: flex;  align-items: center;  justify-content: center;  margin: 45px 30px;   height: 30%;  width: 81%;  opacity: 0.15; position: absolute;' /></center>"
+"<p class='pTituloTicket1' style='font-size: 30px;font-weight: 700;background: #7d7c7b; color: #ffff; border-radius: 10px;' >"+TituloRecibo+"</p>"
+"<p class='pTituloTicket2'>Humaitá esq. Dr. Bottrel <br> Cel: (0982) 104 622   <br> Villarrica - Paraguay </p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"

+"<p class='pTituloTicket1' style='font-size:12px;' ><b style='font-size: 15px;font-weight: 800;'>RECIBO DE DINERO</b></p>"

+"<table style='width:100%; margin-left: 30px;'>"
+"<td style='width:50%'>"
+"<table class='tableTicket'> <tr>"
+"<td style='width:80px'><b>Numero :</b></td>"
+"<td style=''>"+NroRecibo+"</td>"
+"</tr> </table>"
+"<table class='tableTicket'> <tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+NombreCliente+"</td>"
+"</tr> </table>"
+"<table class='tableTicket'> <tr>"
+"<td style='width:85px'><b>RUC o C.I.:</b></td>"
+"<td style=''>"+CiCliente+"</td>"
+"</tr> </table>"
+"</td>"
+"<td style='width:50%'>"
+"<table class='tableTicket'> <tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+Fecha+"</td>"
+"</tr> </table>"
+"<table class='tableTicket'> <tr>"
+"<td style='width:60px'><b>Cajero :</b></td>"
+"<td style=''>"+Cajero+"</td>"
+"</tr> </table>"
+"<table class='tableTicket'> <tr>"
+"<td style='width:100px'><b>D. Atrasado</b></td>"
+"<td style=''>"+DiasAtrazado+" Día(s)</td>"
+"</tr></table>"
+"</td>"
+"</table> <br>"
+"<p class='pTituloTicket1' style='    font-size: 15px;    text-align: left;    margin-left: 5%;' ><b>EN CONCEPTO DE :</b>"+paginaticket+"</p>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
// +"<table class='tableTicket'  style='padding: 4px;border: solid 1px;background: #e1e1e182; font-size: 13px;'>"
// +"<tr>"
// +"<td style='width:10%'><b>CANT.</b></td>"
// +"<td style='width:50%'><b>PRODUCTO</b></td>"
// +"<td style='width:15%'><b>PRECIO</b></td>"
// +"<td style='width:25%'><b>TOTAL</b></td>"
// +"</tr>"
// +"</table>"


+" <br>"
+"<div style='width: 100%;'>"
+"<table style='width: 95%;'> <tr> <td style='width:70%'> "
+"<table class='tableTicket'  style='padding: 4px;border: solid 1px;background: #e1e1e182; font-size: 13px;'>"
+"<tr>"
+"<td style='width:20%'><b>FECHA P.</b></td>"
+"<td style='width:20%'><b>FECHA VENC.</b></td>"
+"<td style='width:40%'><b>DESCRIPCION</b></td>"
+"<td style='width:20%'><b>IMPORTE</b></td>"
+"</tr>"
+"</table>"

+CuotasNro

+"</td> "
+"<td style='width:30%'>"
+"<table class='tableTicket' style='font-size: 15px;font-size: 12px;'> <tr>"
+"<td style='width:40%'><b>TOTAL FACTURA:</b></td> <td style='width:60%;'><b> "+TotalVenta+" Gs. </b></td> </tr> </table> "
+"<table class='tableTicket' style='font-size: 15px;font-size: 12px;'> <tr> "
+"<td style='width:40%'><b>TOTAL PAGADO:</b></td> <td style='width:60%'><b> "+totalpagado+" Gs. </b></td> </tr> </table>"
+"<table class='tableTicket' style='font-size: 15px;font-size: 12px;'> <tr>"
+"<td style='width:40%'><b>SALDO ACTUAL:</b></td> <td style='width:60%'><b> "+deudaActual+" Gs. </b></td> </tr> </table> "
+"</td>"
+"</tr></table>"
+"</div>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"

+"</div>"
+"</center>"
+"</div>"

	
// var ficha="<!DOCTYPE html><html><head></head><body>"+pagina+pagina+"</body></html>";
var ficha=pagina+"<br><br><br>"+pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
 
}



var TotalPagado = "";
var CuotasNro ="";
function imprimirDivticketFactura(){
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
var fechaVenta =document.getElementById("inptFechaVenta").value; 
var PuntoExpedicion=document.getElementById("inptSeleccPuntoExpedicionVenta").value;
var NroVentas=document.getElementById("inptNroVenta").value;
if(PuntoExpedicion!=""){
NroVentas=PuntoExpedicion+"-"+NroVentas
}
var totalP=document.getElementById("inptTotalPagado").value;
totalP=QuitarSeparadorMilValor(totalP)
var totalVe=totalesRecibo;
totalVe=QuitarSeparadorMilValor(totalVe)
var Pendiente=Number(totalVe)-Number(totalP)





if(document.getElementById("inptSeleccTipoVenta").value=="CONTADO"){
	
		
	
 var cajera = document.getElementById("lblUser").innerHTML;	
 var TotalDescuentoRecibo =0

ReImprimirDivTickeFacturaPago(fechaVenta,cajera,CuotasNro,TotalPagado,"0",NombreRecibo,DocumentoRecibo,NroVentas,"","0","0",TotalPagado,TotalDescuentoRecibo,totalesRecibo,"0","0");	

}else{
	
	
	NroVentas
	fechaVenta
	NombreRecibo
	DeudaActualRecibo
	DocumentoRecibo
	CuotasRestante
	paginaDetalleTicket
	totalesRecibo
	InteresRecibo
	TotalDescuentoRecibo
	 TotalPagado =  document.getElementById("inptTotalPagado").value
	DiasAtrasado
	
	
	paginaticket =  paginaDetalleTicket	
	var cajera = document.getElementById("lblUser").innerHTML;
ReImprimirDivTickeFacturaPago(fechaVenta,cajera,CuotasNro,TotalPagado,DiasAtrasado,NombreRecibo,DocumentoRecibo,NroVentas,"",InteresRecibo,separadordemilesnumero(Pendiente),TotalPagado,TotalDescuentoRecibo,totalesRecibo,"0","0");


}



// var ficha=pagina;
// document.getElementById("DivImprimir").innerHTML=ficha;
   // var documento= document.getElementById("DivImprimir").innerHTML;
     // localStorage.setItem("reporte", documento);
	   // localStorage.setItem("tipo", "ticket");
	 // window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 // document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
}









