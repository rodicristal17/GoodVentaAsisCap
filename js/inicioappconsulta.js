//Cobrador app V1.0
var imgCargandoA="<img src='/GoodVentaElim/iconos/cargando.gif' style='width:30px' />"

 window.onload=function()

		{
			
			

			
			if (typeof history.pushState === "function") {
        history.pushState("jibberish", null, null);
        window.onpopstate = function () {
            history.pushState('newjibberish', null, null);
				evento_atras();
			//volver_atras_pagina()
            // Handle the back (or forward) buttons here
            // Will NOT handle refresh, use onbeforeunload for this.
        };
    }  else {
        var ignoreHashChange = true;
        window.onhashchange = function () {
            if (!ignoreHashChange) {
                ignoreHashChange = true;
                window.location.hash = Math.random();
				//evento_atras();
				//volver_atras_pagina()
                // Detect and redirect change here
                // Works in older FF and IE9
                // * it does mess with your hash symbol (anchor?) pound sign
                // delimiter on the end of the URL
            }
            else {
                ignoreHashChange = false;   
            }
        };
    }
	
	// if ('serviceWorker' in navigator) {
       // console.log("Will the service worker register?");
       // navigator.serviceWorker.register('/service-worker.js')
         // .then(function(reg){
           // console.log("Yes, it did.");

        // }).catch(function(err) {
           // console.log("No it didn't. This happened:", err)
       // });
    // }

				
buscar_datos_del_usuario();
 //buscarabmZonaOption()
 buscarabmLocalOption()
	
}
function buscarabmZonaOption(){
 
 	obtener_datos_user();

	 
	document.getElementById("inputBuscadorzona").innerHTML="";
	document.getElementById("inputzonaCliente").innerHTML="";
	document.getElementById("inputzonaCliente").innerHTML="";
	
		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"funt": "buscaroption"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php_system/abmzona.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
		 if (Respuesta=="UI")
			{
		
			
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			
					ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - BUSCAR CLIENTE")
						return false;
					


			} 
			if (Respuesta == "exito")
			{
				
				
				
		  var datos_buscados=datos[2];
		 
			document.getElementById("inputBuscadorzona").innerHTML=datos_buscados
			document.getElementById("inputzonaCliente").innerHTML=datos_buscados
			document.getElementById("inputBuscadorzonaSinConexion").innerHTML=datos_buscados
		
	  
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}

function buscarabmLocalOption(){
 
 	obtener_datos_user();

	 
	document.getElementById("inputLocalProducto").innerHTML="";
	
		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"funt": "buscaroption"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmcasa.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
		 if (Respuesta=="UI")
			{
		
			
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			
					ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - BUSCAR CLIENTE")
						return false;
					


			} 
			if (Respuesta == "exito")
			{
				
				
				
		  var datos_buscados=datos[2];
		 
			document.getElementById("inputLocalProducto").innerHTML=datos_buscados
		
	  
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}



//buscar datos del usuario
function buscar_datos_del_usuario(){
	obtener_datos_user();
	verCerrarVentanaCargando("1")
	document.cookie="user="+userid+";max-age=86400;path=/";
               document.cookie="pass="+passuser+";max-age=86400;path=/";
			 
	 var datos = new FormData();
			
			 datos.append("user" , userid)
			 datos.append("pass" , passuser)
			 datos.append("navegador" , navegador)
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/buscar_datos_usuario.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 ver_vetana_informativa("ERROR DE CONECCIÓN")
verCerrarVentanaCargando("2")
					 return false;
			},
			success: function(responseText)
			{
			  	 verCerrarVentanaCargando("2")	
			Respuesta=responseText;	
			
				console.log(Respuesta)
	try {
		
		var datos = $.parseJSON(Respuesta); 
		Respuesta=datos[1]
		 if (Respuesta=="UI")
			{
		
			
				window.location="/GoodVentaElim/app/login.html";
						return false;
					


			}
			
			if (Respuesta!="error")
			{
		
		
         var nivel=datos["1"];  
         var nombre=datos["2"];  

				
				document.getElementById('lbluser').innerHTML=nombre
				document.getElementById('inptUsuarioCaja').value=nombre
	 // document.getElementById("tdListado").style.display=""
					 // if(userid=="454"){
		   // document.getElementById("tdListado").style.display=""
	   // }
				//buscarcajadelusario()	


			}
			else
			{
			
	
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")


			}
			
			 }catch(error){
					
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
					
				}
		 
					
			}
			});
}

var idcajaApp="";
function buscarcajadelusario(){
	obtener_datos_user();
	verCerrarVentanaCargando("1")
	document.getElementById("lblAperturaCierreCaja1").innerHTML="Caja..."
			  document.getElementById("lblAperturaCierreCaja2").innerHTML="Obteniendo datos..."
			 
	 var datos = new FormData();
			
			 datos.append("user" , userid)
			 datos.append("pass" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt" , "buscarcodcaja")
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/AperturaCierreCaja.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 ver_vetana_informativa("ERROR DE CONECCIÓN")
verCerrarVentanaCargando("2")
					 return false;
			},
			success: function(responseText)
			{
			  	 verCerrarVentanaCargando("2")	
			Respuesta=responseText;	
			
				console.log(Respuesta)
	try {
		
		var datos = $.parseJSON(Respuesta); 
		Respuesta=datos[1]
		 if (Respuesta=="UI")
			{
		
			
				window.location="/GoodVentaElim/app/login.html";
						return false;
					


			}
			
			if (Respuesta!="error")
			{
		
		
        
          idcajaApp=datos["2"];  
        var  fecha=datos["3"];  
          if(idcajaApp==""){
			   document.getElementById("ptituloaperturacierrecaja").innerHTML="ABRIR CAJA"
			   document.getElementById("lblAperturaCierreCaja1").innerHTML="Apertura de Caja"
			  document.getElementById("lblAperturaCierreCaja2").innerHTML="Presione para abrir su caja"
			  document.getElementById("btnAperturaCierreCaja").value="ABRIR CAJA"
			   document.getElementById("divFechaCierre").style.display="none"
			   
		  }else{
			  document.getElementById("ptituloaperturacierrecaja").innerHTML="CERRAR CAJA"
			  document.getElementById("lblAperturaCierreCaja1").innerHTML="Cerrar Caja"
			  document.getElementById("lblAperturaCierreCaja2").innerHTML="Presione para cerrar su caja"
			   document.getElementById("btnAperturaCierreCaja").value="CERRAR CAJA"
			   document.getElementById("divFechaCierre").style.display=""
			   document.getElementById("inptFechaAperturaCaja").value=fecha
		  }
				
		
					


			}
			else
			{
			
	
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")


			}
			
			 }catch(error){
					
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
					
				}
		 
					
			}
			});
}

function verCerrarCaja(d){
	if(d=="1"){
		   var f = new Date();
	
	var anho = f.getFullYear()
	
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	var hora = f.getHours()
	if (hora < 10) {
		hora = "0" + hora;
	}
    var minuto = f.getMinutes()
	if (minuto < 10) {
		minuto = "0" + minuto;
	}
    var segundo = f.getSeconds()
	if (segundo < 10) {
		segundo = "0" + segundo;
	}
     
		 if(idcajaApp==""){	
	  
	  document.getElementById('inptFechaAperturaCaja').value =  anho+"-" + mes + "-" +dia +" "+hora+":"+minuto;
		  }else{
			    
	  
	  document.getElementById('inptFechaCierreCaja').value =  anho+"-" + mes + "-" +dia +" "+hora+":"+minuto;
			 
		  }
				
		document.getElementById("divPrincipalAperturaCierreCaja").style.display=""
	}else{
		document.getElementById("divPrincipalAperturaCierreCaja").style.display="none"
	}
	
}

function abmaperturacierrecaja(){
	 verCerrarVentanaCargando("1")	
	 
	var accion="nuevo"
	if(idcajaApp!=""){
		accion="editar"
	}
	 var fechapertura=document.getElementById('inptFechaAperturaCaja').value;
	 var fechacierre=document.getElementById('inptFechaCierreCaja').value;
	obtener_datos_user()
	  var datos = new FormData();
			 datos.append("user" , userid)
			 datos.append("pass" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("fechaapertura" , fechapertura)
			 datos.append("fechacierre" , fechacierre)
			  datos.append("idaperturacajaapp" , idcajaApp)
			  
					
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/AperturaCierreCaja.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 ver_vetana_informativa("ERROR DE CONEXIÓN","error")
 verCerrarVentanaCargando("2")	
					 return false;
			},
			success: function(responseText)
			{
			  	 verCerrarVentanaCargando("2")	 
			Respuesta=responseText;			
				console.log(Respuesta)
	
	
	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		
		  if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				} 
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
			
			if (Respuesta=="camposvacio")
			{
		
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...","alert")
						return false;
				}
			if (Respuesta=="duplicado")
			{
		ver_vetana_informativa("YA EXISTE UN REGISTRO SIMILAR...","alert")
						return false;
			}
		
		if (Respuesta=="exito")
			{					 
			if(idcajaApp=="")	{
				ver_vetana_informativa("SE HA INICIALIZADO UNA CAJA","alert")
			}else{
				ver_vetana_informativa("SE HA FINALIZADO UNA CAJA","alert")
			}
             
		document.getElementById("divPrincipalAperturaCierreCaja").style.display="none"
buscarcajadelusario()
			}
			else
			{
			
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR","alert")

			}
			
		
          }catch(error){
					
					alert("Error Fatal: "+error)
					
				}
	
	
	
		
		 
			
			
		 
					
			}
			});
			
	
}


	
	 		function evento_del_scroll_ventanas(elemento){

	$(elemento).on("scroll", function(){
		
		 var desplazamientoActual = $(elemento).scrollTop();
		 

			console.log(desplazamientoActual)

			 if(document.getElementById("divOpcionesCreditos").style.display==""){
	 
	 //var topelb=document.getElementById("div_comodin_buscar_cliente").getBoundingClientRect();
				
				if( desplazamientoActual==0){
					  $("div[id=divOpcionesCreditos]").css({"overflow":"hidden"})
					$("div[id=divOpcionesCreditos]").animate({ scrollTop: 0 },0);
					ver_cerrar_abm_opciones_creditos("2")
					
				
					 
				 }
	 
	  return false;
			 } 
			 if(document.getElementById("divPrincipalCreditos").style.display==""){
	 
	 //var topelb=document.getElementById("div_comodin_buscar_cliente").getBoundingClientRect();
				
				if( desplazamientoActual==0){
					  $("div[id=divPrincipalCreditos]").css({"overflow":"hidden"})
					$("div[id=divPrincipalCreditos]").animate({ scrollTop: 0 },0);
					ver_cerrar_abm_creditos("2")
					
				
					 
				 }
	 
	  return false;
			 } 
			 
			 if(document.getElementById("divPrincipalVistaPedidos").style.display==""){
	 
	 //var topelb=document.getElementById("div_comodin_buscar_cliente").getBoundingClientRect();
				
				if( desplazamientoActual==0){
					  $("div[id=divPrincipalVistaPedidos]").css({"overflow":"hidden"})
					$("div[id=divPrincipalVistaPedidos]").animate({ scrollTop: 0 },0);
					ver_cerrar_vista_pedidos("2")
					
				
					 
				 }
	 
	  return false;
			 } 
			 
		
			 
			 if(document.getElementById("divPrincipalVistaProductos").style.display==""){
	 
	 //var topelb=document.getElementById("div_comodin_buscar_cliente").getBoundingClientRect();
				
				if( desplazamientoActual==0){
					  $("div[id=divPrincipalVistaProductos]").css({"overflow":"hidden"})
					$("div[id=divPrincipalVistaProductos]").animate({ scrollTop: 0 },0);
					ver_cerrar_vista_productos("2")
					
				
					 
				 }
	 
	  return false;
			 } 
			
			if(document.getElementById("divPrincipalVistaCliente").style.display==""){
	 
	 //var topelb=document.getElementById("div_comodin_buscar_cliente").getBoundingClientRect();
				
				if( desplazamientoActual==0){
					  $("div[id=divPrincipalVistaCliente]").css({"overflow":"hidden"})
					$("div[id=divPrincipalVistaCliente]").animate({ scrollTop: 0 },0);
					ver_cerrar_vista_clientes("2")
					
				
					 
				 }
	 
	  return false;
			 } 
			
			  	
			 if(document.getElementById("divPrincipalDevoluciones").style.display==""){
	 
	 //var topelb=document.getElementById("div_comodin_buscar_cliente").getBoundingClientRect();
				
				if( desplazamientoActual==0){
					  $("div[id=divPrincipalDevoluciones]").css({"overflow":"hidden"})
					$("div[id=divPrincipalDevoluciones]").animate({ scrollTop: 0 },0);
					ver_cerrar_devoluciones("2")
					
				
					 
				 }
	 
	  return false;
			 }
			 
			 if(document.getElementById("divPrincipalVistaCuentas").style.display==""){
	 
	 //var topelb=document.getElementById("div_comodin_cliente").getBoundingClientRect();
				
				if( desplazamientoActual==0){
					  $("div[id=divPrincipalVistaCuentas]").css({"overflow":"hidden"})
					$("div[id=divPrincipalVistaCuentas]").animate({ scrollTop: 0 },0);
					ver_cerrar_nuevo_cuenta("2")
					
				
					 
				 }
	 
	  return false;
			 }
		
		if(document.getElementById("divPrincipalEnviarPedidos").style.display==""){
	 
	 //var topelb=document.getElementById("div_comodin_cliente").getBoundingClientRect();
				
				if( desplazamientoActual==0){
					  $("div[id=divPrincipalEnviarPedidos]").css({"overflow":"hidden"})
					$("div[id=divPrincipalEnviarPedidos]").animate({ scrollTop: 0 },0);
					ver_cerrar_nuevo_pedido("2")
					
				
					 
				 }
	 
	  return false;
			 }
		
		
			 if(document.getElementById("divPrincipalCliente").style.display==""){
	 
	 //var topelb=document.getElementById("div_comodin_cliente").getBoundingClientRect();
				
				if( desplazamientoActual==0){
					  $("div[id=divPrincipalCliente]").css({"overflow":"hidden"})
					$("div[id=divPrincipalCliente]").animate({ scrollTop: 0 },0);
					ver_cerrar_clientes("2")
					
				
					 
				 }
	 
	  return false;
			 }
		
		
		

		

	
});
			 
  

}	

function evento_atras(){
	
	// if(controlImpresion=="on"){
	// document.body.innerHTML = contenidoOriginal;
	// buscarcuentasdetalles(idVenta)
	// document.getElementById('divimpr').innerHTML="";
	// controlImpresion="off"
	// }
	 if(document.getElementById("divPrincipalMiLista").style.display==""){
	ver_cerrar_mi_listado("2")
					
					  return false;
			 } 
			 if(document.getElementById("divPrincipalCuentasoffline").style.display==""){
	ver_cerrar_nuevo_offline("2")
					
					  return false;
			 } 
			 if(document.getElementById("divPrincipalVistaEncargado").style.display==""){
	ver_cerrar_vista_productos_encargado("2")
					
					  return false;
			 } 
			
			 if(document.getElementById("divPrincipalVistaMisReacuadaciones").style.display==""){
	ver_cerrar_mis_recaudaciones("2")
					
					  return false;
			 } 
			 if(document.getElementById("cntPrincipaLugares").style.display==""){
	 verCerrarMapa("2")
					
					  return false;
			 } 
	
	  if(document.getElementById("divOpcionesCreditos").style.display==""){
	 ver_cerrar_abm_opciones_creditos("2")
					
					  return false;
			 } 
			 if(document.getElementById("divPrincipalCreditos").style.display==""){
	 
					ver_cerrar_abm_creditos("2")
					  return false;
			 } 
			 
			 
	  if(document.getElementById("divPrincipalVistaPedidos").style.display==""){
	 
					ver_cerrar_vista_pedidos("2")
					  return false;
			 } 
			 
		
			 
			 if(document.getElementById("divPrincipalVistaProductos").style.display==""){
	 
					ver_cerrar_vista_productos("2")
					 return false;
			 } 
			
			if(document.getElementById("divPrincipalVistaLista").style.display==""){
	 
					verCerrarVentanaAbmListado("2")
					 return false;
			 } 
			
			  if(document.getElementById("divPrincipalVistaLista").style.display==""){
	ver_cerrar_listado("2")
					
					  return false;
			 } 
			 if(document.getElementById("divPrincipalListadoCamiones").style.display==""){
	ver_cerrar_listado("2")
					
					  return false;
			 } 
	 if(document.getElementById("divPrincipalDevoluciones").style.display==""){
	 
					ver_cerrar_devoluciones("2")
					
				
	  return false;
			 }
			 
			 if(document.getElementById("divPrincipalVistaCuentas").style.display==""){
	 
					ver_cerrar_nuevo_cuenta("2")
					 return false;
			 }
		
		if(document.getElementById("divPrincipalEnviarPedidos").style.display==""){
	
					ver_cerrar_nuevo_pedido("2")		
					return false;
			 }
		
		
			 if(document.getElementById("divPrincipalCliente").style.display==""){
	 
	 
					ver_cerrar_clientes("2")
					return false;
			 }
 }
	



	
	 function vercerrarmenu(d){
	document.getElementById('divMenuApp').style.display='none'
	if(d=="1"){
		document.getElementById('divMenuApp').style.display=''
		
	}
}

	 function ver_cerrar_abm_opciones_creditos(d){
	document.getElementById('divOpcionesCreditos').style.display='none'
	if(d=="1"){
				// $("div[id=divOpcionesCreditos]").css({"overflow":"auto"})
	//$("div[id=divOpcionesCreditos]").animate({ scrollTop: 250 },400);
			document.getElementById('divOpcionesCreditos').style.display=''
				
			
	}else{
		//$("div[id=divOpcionesCreditos]").animate({ scrollTop: 0 },0);
		  $("div[id=divOpcionesCreditos]").css({"overflow":"hidden"})
		
	}
}
 function ver_cerrar_abm_creditos(d){
	document.getElementById('divPrincipalCreditos').style.display='none'
	if(d=="1"){
				// $("div[id=divPrincipalCreditos]").css({"overflow":"auto"})
	//$("div[id=divPrincipalCreditos]").animate({ scrollTop: 250 },400);
			document.getElementById('divPrincipalCreditos').style.display=''
				
			
	}else{
		//$("div[id=divPrincipalCreditos]").animate({ scrollTop: 0 },0);
		  $("div[id=divPrincipalCreditos]").css({"overflow":"hidden"})
		
	}
}
 function ver_cerrar_nuevo_cuenta(d){
	document.getElementById('divPrincipalVistaCuentas').style.display='none'
	if(d=="1"){
		
				// $("div[id=divPrincipalVistaCuentas]").css({"overflow":"auto"})
	//$("div[id=divPrincipalVistaCuentas]").animate({ scrollTop: 250 },400);
			document.getElementById('divPrincipalVistaCuentas').style.display=''
			
				latCuenta=latitud
		logCuenta=longitud
		var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()
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
		 latCuenta=buscar_este_cookie('ubicacion2');
		 logCuenta=buscar_este_cookie('ubicacion3');
		var tiempo=buscar_este_cookie('ubicacion1');
		document.cookie="ubicacion1="+tiempo+";max-age=86400;path=/";
		document.cookie="ubicacion2="+latCuenta+";max-age=86400;path=/";
		document.cookie="ubicacion3="+logCuenta+";max-age=86400;path=/";
			if(tiempo!="" && tiempo!=null && tiempo!='null'){
				document.getElementById('lblUbicacionActualizado').innerHTML='Ubi. Actulizado el '+tiempo
			}else{
				document.getElementById('lblUbicacionActualizado').innerHTML='No se ha seleccionado la Ubi.'
			}
			
				
			
	}else{
		//$("div[id=divPrincipalVistaCuentas]").animate({ scrollTop: 0 },0);
		  $("div[id=divPrincipalVistaCuentas]").css({"overflow":"hidden"})
		
	}
}

		 //buscar cookies
	 function buscar_este_cookie(name)
	 {
		 var nameEQ=name+"=";
		 var ca= document.cookie.split(";");
		 for(var i=0;i<ca.length;i++)
		 {
			 var c =ca[i];
			 while(c.charAt(0)==' ')c=c.substring(1,c.length);
			 if(c.indexOf(nameEQ)==0){
				 return decodeURIComponent (c.substring(nameEQ.length,c.length));
			 }
		 }
		 return "invitado";
	 }
	 

 function ver_cerrar_nuevo_pedido(d){
	document.getElementById('divPrincipalEnviarPedidos').style.display='none'
	if(d=="1"){
			
			document.getElementById('divPrincipalEnviarPedidos').style.display=''
			
				
	}else{
		document.getElementById('divPrincipalVistaProductos').style.display=''
		NuevoRegistroPedidos()
	}
}

 function ver_cerrar_devoluciones(d){
	document.getElementById('divPrincipalDevoluciones').style.display='none'
	if(d=="1"){
	
			document.getElementById('divPrincipalDevoluciones').style.display=''
				
			

		
	}
}


 function ver_cerrar_clientes(d){
	document.getElementById('divPrincipalCliente').style.display='none'
	if(d=="1"){
				NuevoRegistroCliente()
			document.getElementById('divPrincipalCliente').style.display=''
				if(document.getElementById("inputzonaCliente").value==""){
					buscarabmZonaOption()
				}
			
	}else{
		
		
	}
}
	 
	 function ver_cerrar_vista_clientes(d){
	document.getElementById('divPrincipalVistaCliente').style.display='none'
	if(d=="1"){
			
			document.getElementById('divPrincipalVistaCliente').style.display=''
			document.getElementById('divPrincipalCliente').style.display='none'
				controlSeleccCliente="1"

		
		
	}else{
		if(controlSeleccCliente=="1"){
			document.getElementById('divPrincipalCliente').style.display=''
		}
	}
}	 

 function ver_cerrar_listado(d){
	document.getElementById('divPrincipalListadoCamiones').style.display='none'
	if(d=="1"){
			
			document.getElementById('divPrincipalListadoCamiones').style.display=''
			limpiarcamposListado()
	
		
	}
}

 function ver_cerrar_mi_listado(d){
	document.getElementById('divPrincipalMiLista').style.display='none'
	if(d=="1"){
			
			document.getElementById('divPrincipalMiLista').style.display=''
			
	
		
	}
}	 

	 function ver_cerrar_vista_productos(d){
	document.getElementById('divPrincipalVistaProductos').style.display='none'
	if(d=="1"){	
			document.getElementById('divPrincipalVistaProductos').style.display=''
			contolSearchProductos="1";
			

		
	}
}
 function ver_cerrar_vista_productos_lista(d){
	document.getElementById('divPrincipalVistaProductos').style.display='none'
	if(d=="1"){	
			document.getElementById('divPrincipalVistaProductos').style.display=''
			contolSearchProductos="3";
			

		
	}
}
 function ver_cerrar_vista_productos_encargado(d){
	document.getElementById('divPrincipalVistaEncargado').style.display='none'
	if(d=="1"){	
			document.getElementById('divPrincipalVistaEncargado').style.display=''
		
			

		
	}
}

 function ver_cerrar_vista_pedidos(d){
	document.getElementById('divPrincipalVistaPedidos').style.display='none'
	
	if(d=="1"){
		
			
			document.getElementById('divPrincipalVistaPedidos').style.display=''
						

		
	}
}
 function ver_cerrar_vista_opciones_pago(d){
	document.getElementById('divOpcionesPagos').style.display='none'
	
	if(d=="1"){
		if(idcajaApp==""){	
		 ver_vetana_informativa("FALTO INICIALIZAR UNA CAJA")
		return
		}
				
			document.getElementById('inptMontoaCobrarPago').value=document.getElementById("inptTotalPendienteCredito").value
			document.getElementById('divOpcionesPagos').style.display=''
			document.getElementById('btnCargarPagosParcial1').style.display=''
			document.getElementById('btnCargarPagosParcial2').style.display='none'
						

		
	}
}
 function ver_cerrar_mis_recaudaciones(d){
	document.getElementById('divPrincipalVistaMisReacuadaciones').style.display='none'
	
	if(d=="1"){
		
			document.getElementById('divPrincipalVistaMisReacuadaciones').style.display=''

		
	}
}

/*
ABM CLIENTE
*/
var controlfotocliente="";
function ExploradorImagenCliente(File){	
$("input[name=file_1]").click();
controlfotocliente=File;
}
var fotocliente1="";
var extcliente1="";
var fotocliente2="";
var extcliente2="";
function readFileCliente(input){		
var file=$("input[name="+input.name+"]")[0].files[0];
var filename= file.name;
var tamanho = file.size;
if (tamanho > 5000000){
alertmensaje("LA FOTO NO PUEDE EXCEDER LOS 5Mb")
return false
}
file_extension=filename.substring(filename.lastIndexOf('.')+1).toLowerCase();
if ((file_extension=="jpeg") || (file_extension=="jpg") || (file_extension=="png") ){
}else{
alertmensaje("LA FOTO SELECCIONADO NO ES JPEG")
return false;
}
var reader = new FileReader();
reader.onload = function(e){
if(controlfotocliente=="foto1"){
	extcliente1=file_extension;
fotocliente1=e.target.result;
 $("div[id=imgFotoCliente1]").css({"background-image":"url("+fotocliente1+")"})

}
if(controlfotocliente=="foto2"){
	extcliente2=file_extension;
fotocliente2=e.target.result;
 $("div[id=imgFotoCliente2]").css({"background-image":"url("+fotocliente2+")"})

}


}
reader.readAsDataURL(input.files[0]);
}

var latitud="";
var longitud="";
var idAbmCliente="";
var latitudCliente="";
var longitudCliente="";
function NuevoRegistroCliente(){
	idAbmCliente="";
	document.getElementById('inptRucCliente').value=""
	document.getElementById('inptCiCliente').value=""
	document.getElementById('inptNombreApellidoCliente').value=""
	document.getElementById('inptTelefCliente').value=""
	document.getElementById('inptDireccionCliente').value=""
	 $("div[id=imgFotoCliente1]").css({"background-image":"url()"})
	  $("div[id=imgFotoCliente2]").css({"background-image":"url()"})
	 fotocliente1="";
 extcliente1="";
 fotocliente2="";
 extcliente2="";
}
function obtenerdatoscliente(elemento){
	if(controlSeleccCliente=="1"){
		NuevoRegistroCliente();
	idAbmCliente=$(elemento).children('td[id="td_1"]').html();
	latitudCliente=$(elemento).children('td[id="td_7"]').html();
	longitudCliente=$(elemento).children('td[id="td_8"]').html();
	document.getElementById('inptRucCliente').value=$(elemento).children('td[id="td_15"]').html();
	document.getElementById('inptCiCliente').value=$(elemento).children('td[id="td_5"]').html();
	document.getElementById('inptNombreApellidoCliente').value=$(elemento).children('td[id="td_2"]').html();
	document.getElementById('inptTelefCliente').value=$(elemento).children('td[id="td_4"]').html();
	document.getElementById('inptDireccionCliente').value=$(elemento).children('td[id="td_3"]').html();
	document.getElementById('inptUbicacionCliente').value=$(elemento).children('td[id="td_9"]').html();
	document.getElementById('inputzonaCliente').value=$(elemento).children('td[id="td_12"]').html();
	document.getElementById('inptReferenciaCliente').value=$(elemento).children('td[id="td_16"]').html();

	var foto1=$(elemento).children('td[id="td_13"]').html();
	var foto2=$(elemento).children('td[id="td_14"]').html();
	 $("div[id=imgFotoCliente1]").css({"background-image":"url("+foto1+")"})
	 $("div[id=imgFotoCliente2]").css({"background-image":"url("+foto2+")"})
	}
	
	if(controlSeleccCliente=="2"){
	
	idAbmCliente=$(elemento).children('td[id="td_1"]').html();
	document.getElementById('inptDocClientePedido').value=$(elemento).children('td[id="td_2"]').html();
	//document.getElementById('inptNombreClientePedido').value=$(elemento).children('td[id="td_2"]').html();
	}
	if(controlSeleccCliente=="3"){
	
	idAbmCliente=$(elemento).children('td[id="td_1"]').html();
	document.getElementById('inptDocClienteDevoluciones').value=$(elemento).children('td[id="td_5"]').html();
	document.getElementById('inptNombreClienteDevoluciones').value=$(elemento).children('td[id="td_2"]').html();
	 buscarhistorialDevolucione();
	}
	if(controlSeleccCliente=="4"){
	
	idclienteVenta=$(elemento).children('td[id="td_1"]').html();
	document.getElementById('inptClienteVenta').value=$(elemento).children('td[id="td_2"]').html();
	
	}
	
ver_cerrar_vista_clientes("2")
}
function VerificarCampoCliente(){
	var inptCiCliente = document.getElementById('inptCiCliente').value
	var inptRucCliente = document.getElementById('inptRucCliente').value
	var inptNombreApellidoCliente=document.getElementById('inptNombreApellidoCliente').value
	var inptTelefCliente=document.getElementById('inptTelefCliente').value
	var inptDireccionCliente=document.getElementById('inptDireccionCliente').value
	var idZona=document.getElementById('inputzonaCliente').value
	var inptReferenciaCliente=document.getElementById('inptReferenciaCliente').value
	if(inptCiCliente==""){
		ver_vetana_informativa("FALTO INGRESAR EL RUC O CI","abmCliente")
		return
	}
	if(inptNombreApellidoCliente==""){
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE Y APELLIDO","abmCliente")
		return
	}
	

	AbmCliente(inptReferenciaCliente,inptCiCliente,inptRucCliente,inptNombreApellidoCliente,inptTelefCliente,inptDireccionCliente,idZona,idAbmCliente)
	
}
function AbmCliente(referencia,ci_cliente,rut_cliente,nombre_persona,telefono,direccion,idZona,cod_persona){
	 verCerrarVentanaCargando("1")	
	var accion="nuevo"
	if(idAbmCliente!=""){
		accion="editar"
	}
	
	obtener_datos_user()
	  var datos = new FormData();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("cod_persona" , cod_persona)
			 datos.append("nombre_persona" , nombre_persona)
			  datos.append("direccion" , direccion)
			  datos.append("telefono" , telefono)
			  datos.append("ci_cliente" , ci_cliente)
			  datos.append("rut_cliente" , rut_cliente)
			  datos.append("Calificacion" , "")
			  datos.append("email" , referencia)
			  datos.append("latitudCliente" , latitudCliente)
			  datos.append("longitudCliente" , longitudCliente)
			  datos.append("idZona" , idZona)
			  datos.append("foto1", fotocliente1)
	          datos.append("ext1", extcliente1)
	         datos.append("foto2", fotocliente2)
	          datos.append("ext2", extcliente2)
					
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmclientes.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 ver_vetana_informativa("ERROR DE CONEXIÓN","error")
 verCerrarVentanaCargando("2")	
					 return false;
			},
			success: function(responseText)
			{
			  	 verCerrarVentanaCargando("2")	 
			Respuesta=responseText;			
				console.log(Respuesta)
	
	
	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		
		  if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				} 
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
			
			if (Respuesta=="camposvacio")
			{
		
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...","alert")
						return false;
				}
			if (Respuesta=="duplicado")
			{
		ver_vetana_informativa("YA EXISTE UN REGISTRO SIMILAR...","alert")
						return false;
			}
		
		if (Respuesta=="exito")
			{					 
				ver_vetana_informativa("DATOS GUARDADOS...","alert")
NuevoRegistroCliente();				
             
		

			}
			else
			{
			
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR","alert")

			}
			
		
          }catch(error){
					
					alert("Error Fatal: "+error)
					
				}
	
	
	
		
		 
			
			
		 
					
			}
			});
			
	
}
function buscarclientes(){

	
	document.getElementById('divClienteBuscado').innerHTML=imgCargandoA;
var buscar=document.getElementById('inptBuscarClientes').value
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": buscar,
			"funt": "buscarporvista"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/abmclientes.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divClienteBuscado').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divClienteBuscado').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				var nroRegistro=datos["3"];
				if(datosBuscado==""){
		document.getElementById('lblRegistrosEncontradosClientes').innerHTML="No se encontraron registros";
					
					return;
				}
				
				document.getElementById('divClienteBuscado').innerHTML=datosBuscado;
				document.getElementById('lblRegistrosEncontradosClientes').innerHTML="Se encontraron "+nroRegistro+" resgitro(s)";

					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}
function seleccionarEstaUbicacion(){
	if(placenames==""){
	    
		ver_vetana_informativa("FALTO SELECCIONAR LA UBICACION","abmubicacion")
		return;
	}else{
		if(controlMapa=="1"){
			
			latitudCliente=latitud
		longitudCliente=longitud
		document.getElementById('inptUbicacionCliente').value=placenames;
		}
		if(controlMapa=="2"){
			
			latCuenta=latitud
		logCuenta=longitud
		var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()
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
		
		document.cookie="ubicacion1="+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min+";max-age=86400;path=/";
		document.cookie="ubicacion2="+latCuenta+";max-age=86400;path=/";
		document.cookie="ubicacion3="+logCuenta+";max-age=86400;path=/";
		document.getElementById('lblUbicacionActualizado').innerHTML='Ubi. Actulizado el '+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min
		}
		
		verCerrarMapa("2");
	}
}
var controlMapa="";
function verCerrarMapa(d){
	document.getElementById("cntPrincipaLugares").style.display="none";
	if(d=="1"){
		controlMapa="1";
		document.getElementById("cntPrincipaLugares").style.display="";
	}
}
function verCerrarMapaCuentas(){
	controlMapa="2";
	document.getElementById("cntPrincipaLugares").style.display="";
}

/*
PRODUCTOS
*/
var idProductoPedido="";
function obtenerdatosProductospedidos(elemento){

	idProductoPedido=$(elemento).children('td[id="td_1"]').html();
	document.getElementById('inptProductoPedido').value=$(elemento).children('td[id="td_2"]').html();
	document.getElementById('inptProductoCostoPedido').value=$(elemento).children('td[id="td_datos_1"]').html();
	document.getElementById('inptProductoMarcaPedido').value=$(elemento).children('td[id="td_5"]').html();
	
	var paginaOpciones=$(elemento).children('td[id="td_4"]').html();
	document.getElementById('inptSeleccPrecio').innerHTML=paginaOpciones;
	seleccionarestecosto()
	ver_cerrar_vista_productos("2")
    ver_cerrar_nuevo_pedido("1")
	 buscarpreciosproductos()
	
}
function buscarpreciosproductos(){

	document.getElementById('table_precios_creditos').innerHTML=imgCargandoA;

				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idProductoPedido,
			"funt": "buscarprecios"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/buscarProducto.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('table_precios_creditos').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('table_precios_creditos').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				var nroRegistro=datos["3"];
				document.getElementById('table_precios_creditos').innerHTML=datosBuscado;
				return false;
				
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}

function seleccionarestecosto(){
	document.getElementById('inptSeleccPrecio2').value=document.getElementById('inptSeleccPrecio').value;
}
function buscarproductos(){
var funct=""
	if(contolSearchProductos=="1"){
		 funct="buscarporpedido"
	}
	if(contolSearchProductos=="2"){
		 funct="buscarpordevolucion"
	}
	if(contolSearchProductos=="3"){
		 funct="buscarlista"
	}
	document.getElementById('divProductoBuscado').innerHTML=imgCargandoA;
var buscar=document.getElementById('inptBuscarProductos').value
var local=document.getElementById('inputLocalProducto').value
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": buscar,
			"local": local,
			"idCliente": idAbmCliente,
			"funt": funct
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/buscarProducto.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divProductoBuscado').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divProductoBuscado').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				var nroRegistro=datos["3"];
				if(datosBuscado==""){
		document.getElementById('lblRegistrosEncontradosProducto').innerHTML="No se encontraron registros";
					
					return;
				}
				
				document.getElementById('divProductoBuscado').innerHTML=datosBuscado;
				document.getElementById('lblRegistrosEncontradosProducto').innerHTML="Se encontraron "+nroRegistro+" resgitro(s)";

					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}

/*
CARGAR PEDIDOS
*/
var controlSeleccCliente="";
function verclienteenpedidos(){
   document.getElementById('divPrincipalVistaCliente').style.display=''
	controlSeleccCliente="2"

}
var idAbmPedidos="";
function NuevoRegistroPedidos(){
	idAbmCliente="";
	idAbmPedidos="";
	idProductoPedido="";
	document.getElementById('inptProductoPedido').value=""
	document.getElementById('inptSeleccPrecio').value=""
	document.getElementById('inptDocClientePedido').value=""
	document.getElementById('inptCantidadPerdido').value=""
}
function obtenerdatospedidos(elemento){
	
		NuevoRegistroPedidos();
		
	idAbmCliente=$(elemento).children('td[id="td_3"]').html();
	idAbmPedidos=$(elemento).children('td[id="td_1"]').html();
	idProductoPedido=$(elemento).children('td[id="td_2"]').html();
	document.getElementById('inptProductoPedido').value=$(elemento).children('td[id="td_6"]').html();
	document.getElementById('inptDocClientePedido').value=$(elemento).children('td[id="td_4"]').html();
	document.getElementById('inptCantidadPerdido').value=$(elemento).children('td[id="td_10"]').html();
     
	var paginaOpciones=$(elemento).children('td[id="td_9"]').html();
	
	document.getElementById('inptSeleccPrecio').innerHTML=paginaOpciones; 
	seleccionarestecosto()
	document.getElementById('inptSeleccPrecio').value=$(elemento).children('td[id="td_7"]').html();;

	ver_cerrar_vista_pedidos("2")
	ver_cerrar_nuevo_pedido("1")
	
}
function VerificarCampoPedidos(){
	var inptDocClientePedido = document.getElementById('inptDocClientePedido').value
	var inptProductoPedido=document.getElementById('inptProductoPedido').value
	var precio=document.getElementById('inptSeleccPrecio').value
	var inptCantidadPerdido=document.getElementById('inptCantidadPerdido').value
	if(inptDocClientePedido==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN CLIENTE","abmpedidos")
		return
	}
	if(inptProductoPedido==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO ","abmpedidos")
		return
	}
	if(inptCantidadPerdido==""){
		ver_vetana_informativa("FALTO INGRESAR UNA CANTIDAD ","abmpedidos")
		return
	}
	
	
	AbmPedidos(idAbmCliente,idProductoPedido,precio,inptCantidadPerdido,idAbmPedidos)
	
}
function AbmPedidos(cod_clienteFK,cod_productoFK,costo,cantidad,idpedidos){
	 verCerrarVentanaCargando("1")	
	var accion="nuevo"
	if(idpedidos!=""){
		accion="editar"
	}
	
	obtener_datos_user()
	  var datos = new FormData();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("idpedidos" , idpedidos)
			 datos.append("cod_productoFK" , cod_productoFK)
			  datos.append("costo" , costo)
			  datos.append("cantidad" , cantidad)
			  datos.append("cod_clienteFK" , cod_clienteFK)
			 
			
					
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmpedidos.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 ver_vetana_informativa("ERROR DE CONEXIÓN","error")
 verCerrarVentanaCargando("2")	
					 return false;
			},
			success: function(responseText)
			{
			  	  verCerrarVentanaCargando("2")	
			Respuesta=responseText;			
				console.log(Respuesta)
	
	
	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		
		  if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				} 
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
			
			if (Respuesta=="camposvacio")
			{
		
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...","alert")
						return false;
				}
			if (Respuesta=="duplicado")
			{
		ver_vetana_informativa("YA EXISTE UN REGISTRO SIMILAR...","alert")
						return false;
			}
		
		if (Respuesta=="exito")
			{					 
				ver_vetana_informativa("DATOS GUARDADOS...","alert")
			
             
		

			}
			else
			{
			
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR","alert")

			}
			
		
          }catch(error){
					
					alert("Error Fatal: "+error)
					
				}
	
	
	
		
		 
			
			
		 
					
			}
			});
			
	
}
function buscarpedidos(){

	
	document.getElementById('divPedidosBuscado').innerHTML=imgCargandoA;
var buscar=document.getElementById('inptBuscarClientes').value
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": buscar,
			"funt": "buscarporvista"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/abmpedidos.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divPedidosBuscado').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divPedidosBuscado').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				var nroRegistro=datos["3"];
				if(datosBuscado==""){
		document.getElementById('lblRegistrosEncontradosPedidos').innerHTML="No se encontraron registros";
					
					return;
				}
				
				document.getElementById('divPedidosBuscado').innerHTML=datosBuscado;
				document.getElementById('lblRegistrosEncontradosPedidos').innerHTML="Se encontraron "+nroRegistro+" resgitro(s)";

					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}

/*
CUENTAS
*/
var idCuenta=""
var idVenta=""
var idClienteCuenta=""
var buscarporcuenta="cliente";
var latCuenta="";
var logCuenta="";
function buscar_cuenta_por(d){
	if(d=="1"){
	buscarporcuenta="cliente";	
		document.getElementById('btnOpcione1').style="border-bottom: 1px solid #ccc;";
		document.getElementById('btnOpcione2').style="";
		document.getElementById('btnOpcione3').style="";
		document.getElementById('btnOpcione4').style="";
		document.getElementById('btnOpcione5').style="";
		document.getElementById('inptBuscarCuentas').style.display="";
		document.getElementById('tituloBuscarCuenta').innerHTML="Nombre o C.I. del Cliente";
		document.getElementById('inptBuscarFechaCuentas').style.display="none";
		document.getElementById('inptBuscarFechaCuentas2').style.display="none";
	}
	if(d=="2"){
		buscarporcuenta="fecha";
		document.getElementById('inptBuscarFechaCuentas2').style.display="none";
		document.getElementById('inptBuscarCuentas').style.display="none";
		document.getElementById('inptBuscarFechaCuentas').style.display="";
			document.getElementById('tituloBuscarCuenta').innerHTML="Seleccione un fecha";
			document.getElementById('btnOpcione2').style="border-bottom: 1px solid #ccc;";
		document.getElementById('btnOpcione1').style="";
		document.getElementById('btnOpcione3').style="";
		document.getElementById('btnOpcione4').style="";
		document.getElementById('btnOpcione5').style="";
	}
	if(d=="3"){
		buscarporcuenta="solohoy";
		document.getElementById('inptBuscarCuentas').style.display="";
		document.getElementById('inptBuscarFechaCuentas').style.display="none";
		document.getElementById('inptBuscarFechaCuentas2').style.display="none";
			document.getElementById('tituloBuscarCuenta').innerHTML="Nombre o C.I. del Cliente";
			document.getElementById('btnOpcione3').style="border-bottom: 1px solid #ccc;";
		document.getElementById('btnOpcione1').style="";
		document.getElementById('btnOpcione2').style="";
		document.getElementById('btnOpcione4').style="";
		document.getElementById('btnOpcione5').style="";
	}
	if(d=="4"){
		buscarporcuenta="visitas";
		document.getElementById('inptBuscarCuentas').style.display="none";
		document.getElementById('inptBuscarFechaCuentas').style.display="";
		document.getElementById('inptBuscarFechaCuentas2').style.display="";
			document.getElementById('tituloBuscarCuenta').innerHTML="Rango de Fechas";
			document.getElementById('btnOpcione4').style="border-bottom: 1px solid #ccc;";
		document.getElementById('btnOpcione1').style="";
		document.getElementById('btnOpcione2').style="";
		document.getElementById('btnOpcione3').style="";
		document.getElementById('btnOpcione5').style="";
	}
	if(d=="5"){
		buscarporcuenta="entrefecha";
		document.getElementById('inptBuscarFechaCuentas2').style.display="";
		document.getElementById('inptBuscarCuentas').style.display="none";
		document.getElementById('inptBuscarFechaCuentas').style.display="";
			document.getElementById('tituloBuscarCuenta').innerHTML="Defina el rango ";
			document.getElementById('btnOpcione5').style="border-bottom: 1px solid #ccc;";
		document.getElementById('btnOpcione1').style="";
		document.getElementById('btnOpcione3').style="";
		document.getElementById('btnOpcione4').style="";
		document.getElementById('btnOpcione2').style="";
	}
}
function buscarcuentas(){

	
	document.getElementById('divCuentasBuscado').innerHTML=imgCargandoA;
	if(buscarporcuenta=="cliente" || buscarporcuenta=="solohoy"){
		var buscar=document.getElementById('inptBuscarCuentas').value
	var buscar2=""
	}else{
		var buscar=document.getElementById('inptBuscarFechaCuentas').value
		var buscar2=document.getElementById('inptBuscarFechaCuentas2').value
		
	}
var idzona=document.getElementById('inputBuscadorzona').value
var tipo=document.getElementById('inputTipoCuenta').value
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": buscar,
			"buscarpor": buscarporcuenta,
			"lat": latCuenta,
			"lot": logCuenta,
			"idzona": idzona,
			"buscar2": buscar2,
			"tipo": tipo,
			"funt": "buscar"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/abmcuentas.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divCuentasBuscado').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divCuentasBuscado').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				var nroRegistro=datos["3"];
				if(datosBuscado==""){
		document.getElementById('lblRegistrosEncontradosCuentas').innerHTML="No se encontraron registros";
					
					return;
				}
				
				document.getElementById('divCuentasBuscado').innerHTML=datosBuscado;
				document.getElementById('lblRegistrosEncontradosCuentas').innerHTML="Se encontraron "+nroRegistro+" resgitro(s)";

					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}
function obtenerdatoscuentas(elemento){
	
		

	idVenta=$(elemento).children('td[id="td_1"]').html();
	idClienteCuenta=$(elemento).children('td[id="td_2"]').html();
	document.getElementById('inptNroVentaCredito').value=$(elemento).children('td[id="td_17"]').html();
	document.getElementById('inptDocClienteCredito').value=$(elemento).children('td[id="td_4"]').html();
	document.getElementById('inptNombreClienteCredito').value=$(elemento).children('td[id="td_3"]').html();
	document.getElementById('inptTotalVentaCredito').value=$(elemento).children('td[id="td_5"]').html();
	document.getElementById('inptTotalPagadoCredito').value=$(elemento).children('td[id="td_8"]').html();
	document.getElementById('inptTotalDescuentoCredito').value=$(elemento).children('td[id="td_7"]').html();
	document.getElementById('inptDiasAtrazadoCargarPago').value=$(elemento).children('td[id="td_11"]').html();
	document.getElementById('inptDireccionClienteCredito').value=$(elemento).children('td[id="td_19"]').html();
	document.getElementById('inptReferenciaClienteCredito').value=$(elemento).children('td[id="td_18"]').html();
		document.getElementById('inptTelefClienteCredito').value=$(elemento).children('td[id="td_12"]').html();
		document.getElementById('inptTotalInteresCreditoActual').value=$(elemento).children('td[id="td_21"]').html();
		document.getElementById('inptTotalPendienteSinInteresCredito').value=$(elemento).children('td[id="td_22"]').html();

	document.getElementById('inptTotalInteresCredito').value=$(elemento).children('td[id="td_20"]').html();
	document.getElementById('inptTotalPendienteCredito').value=$(elemento).children('td[id="td_14"]').html();
	document.getElementById('inptTotalDeudaCredito').value=$(elemento).children('td[id="td_30"]').html();
	//document.getElementById('divDetalleCuenta').innerHTML=$(elemento).children('td[id="td_12"]').html();
	paginaticket=$(elemento).children('td[id="td_13"]').html();
    longitudSol=$(elemento).children('td[id="td_10"]').html();
    latitudSol=$(elemento).children('td[id="td_9"]').html();
    deudaActualOffline=$(elemento).children('td[id="td_14"]').html();
	 document.getElementById("btnCargarPagos1").style.display="";
	document.getElementById("btnCargarPagos2").style.display="none";
	document.getElementById("divTotalPagoOffline").style.display="none";
	ver_cerrar_abm_creditos('1')
	buscarcuentasdetalles(idVenta)
	buscarcuentasdetallesproductos(idVenta)
	buscarcuentaspagosrealizados(idVenta)
}
function buscarcuentasdetalles(idCodVenta){

	
	document.getElementById('divDetalleCuenta').innerHTML=imgCargandoA;
	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idCodVenta,
			"funt": "buscarDetalle"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/abmcuentas.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divDetalleCuenta').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divDetalleCuenta').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				
				if(datosBuscado==""){
		        document.getElementById('divDetalleCuenta').innerHTML="";
					
					return;
				}
				
				document.getElementById('divDetalleCuenta').innerHTML=datosBuscado;
				// document.getElementById('inptTotalPagadoCredito').value=datos["3"];
				// document.getElementById('inptTotalInteresCredito').value=datos["4"];

					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}

function buscarcuentasdetallesproductos(idCodVenta){

	
	document.getElementById('divProductosComprados').innerHTML=imgCargandoA;
	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idCodVenta,
			"funt": "buscarDetalleProductos"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/abmcuentas.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divProductosComprados').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divProductosComprados').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				
				if(datosBuscado==""){
		        document.getElementById('divProductosComprados').innerHTML="";
					
					return;
				}
				
				document.getElementById('divProductosComprados').innerHTML=datosBuscado;

					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}

function buscarcuentaspagosrealizados(idCodVenta){

	
	document.getElementById('divPagosRealizados').innerHTML=imgCargandoA;
	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idCodVenta,
			"funt": "buscarDetallePagosrealizados"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/abmcuentas.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divPagosRealizados').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divPagosRealizados').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				
				if(datosBuscado==""){
		        document.getElementById('divPagosRealizados').innerHTML="";
					
					return;
				}
				
				document.getElementById('divPagosRealizados').innerHTML=datosBuscado;

					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}


function obtenerdatoscuotero(elemento){
	var estado=$(elemento).children('td[id="td_6"]').html();
	if(estado=="Pagado"){
	return
	}
	if(idcajaApp==""){	
		 ver_vetana_informativa("FALTO INICIALIZAR UNA CAJA")
		return
		}
	idCuenta=$(elemento).children('td[id="td_1"]').html();
	document.getElementById('inptReciboCobro').value="";
	document.getElementById('inptCuotaCobro').value=$(elemento).children('td[id="td_2"]').html();
	document.getElementById('inptTotalPagadoCobro').value=$(elemento).children('td[id="td_12"]').html();
	document.getElementById('inptTotalInteresCobro').value=$(elemento).children('td[id="td_11"]').html();
	document.getElementById('inptDeudaActualCobro').value=$(elemento).children('td[id="td_9"]').html();
	document.getElementById('inptMontoaPagar').value=$(elemento).children('td[id="td_9"]').html();
	document.getElementById('inptMontocrobrado').value=$(elemento).children('td[id="td_5"]').html();
	document.getElementById('inptDiasAtrazado').value=$(elemento).children('td[id="td_8"]').html();
	
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
    document.getElementById('inptFechaPago').value=f.getFullYear()+"-"+mes+"-"+dia;
	document.getElementById("btnPagosDeCuotas1").style.display="";
	document.getElementById("btnPagosDeCuotas2").style.display="none";
	document.getElementById("divTotalPagoOffline").style.display="none";
	ver_cerrar_abm_opciones_creditos("1");
	
}
var controlInsercionsPagos="on"
function verificarPagos(){
	if(controlInsercionsPagos=="off"){
		ver_vetana_informativa("AGUARDE UN MOMENTO A QUE FINALIZE EL PROCESO ANTERIOR","abmpagos")
		return;
	}
	controlInsercionsPagos="off"
	var inptDeudaActualPagar = document.getElementById('inptDeudaActualCobro').value
	var inptFechaPago=document.getElementById('inptFechaPago').value
	var inptMontoaPagar=document.getElementById('inptMontoaPagar').value
	var cuotanro=document.getElementById('inptCuotaCobro').value
	var nrorecibo=document.getElementById('inptReciboCobro').value
	var inptTotalInteresCobro=document.getElementById('inptTotalInteresCobro').value
	

	if(idcajaApp==""){
		ver_vetana_informativa("FALTO INICIALIZAR UN CAJA","abmpagos")
		return
	}
	
	if(idCuenta==""){
		ver_vetana_informativa("FALTO SELECCIONAR UNA CUENTA","abmpagos")
		return
	}
	if(idVenta==""){
		ver_vetana_informativa("FALTO SELECCIONAR UNA CUENTA","abmpagos")
		return
	}
	if(inptMontocrobrado==""){
		ver_vetana_informativa("FALTO INGRESAR EL MONTO","abmpagos")
		return
	}
	if(inptFechaPago==""){
		ver_vetana_informativa("FALTO SELECCIONAR UNA FECHA","abmpagos")
		return
	}
	
	
	abmpagos(idcajaApp,inptTotalInteresCobro,idCuenta,idVenta,inptMontoaPagar,inptDeudaActualPagar,inptFechaPago,cuotanro,nrorecibo)
	
}
var pagoreciente="";
var deudaActual="";
var pagado="";
var diaatrazado="";
var paginaticket="";
var cuotasNro="";
var latPago="";
var logPago="";
function abmpagos(codAperturaApp,totalInteres,cod_creditoFK,idCodVenta,Monto,totalDeudaCuota,Fecha,cuotanro,nrorecibo){
	 verCerrarVentanaCargando("1")	
	var accion="nuevo"

	obtener_datos_user()
	  var datos = new FormData();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("cod_creditoFK" , cod_creditoFK)
			 datos.append("cod_venta" , idCodVenta)
			  datos.append("Monto" , Monto)
			  datos.append("totalDeudaCuota" , totalDeudaCuota)
			  datos.append("Fecha" , Fecha)
			  datos.append("cuotanro" , cuotanro)
			  datos.append("lot" , logPago)
			  datos.append("lat" , latPago)
			  datos.append("nrorecibo" , nrorecibo)
			  datos.append("totalInteres" , totalInteres)
			  datos.append("codAperturaApp" , codAperturaApp)
			 
			
					
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmpagos.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 ver_vetana_informativa("ERROR DE CONEXIÓN","error")
					 controlInsercionsPagos="on"
 verCerrarVentanaCargando("2")	
					 return false;
			},
			success: function(responseText)
			{
				 controlInsercionsPagos="on"
			  	 verCerrarVentanaCargando("2")	 
			Respuesta=responseText;			
				console.log(Respuesta)
	
	
try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		
		  if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				} 
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
			
			if (Respuesta=="camposvacio")
			{
		 
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...","alert")
						return false;
				}
			if (Respuesta=="duplicado")
			{
				
		ver_vetana_informativa("YA EXISTE UN REGISTRO SIMILAR...","alert")
						return false;
			}
		
		if (Respuesta=="exito")
			{	

ver_cerrar_abm_opciones_creditos("2"); 
				ver_vetana_informativa("DATOS GUARDADOS...","alert")
			
             ver_cerrar_abm_opciones_creditos("2");
			 	document.getElementById('inptTotalVentaCredito').value=datos["3"]
	document.getElementById('inptTotalPagadoCredito').value=datos["2"];
	deudaActual=datos["12"];
	paginaticket=datos["5"];
	cuotasNro=datos["6"];
	document.getElementById("inptReciboCobro").value=datos["7"];
	pagado=Monto;
	nroRecibo=datos["7"];
	var totalInteresPagado=datos["11"];
	var TotalDeuda=datos["12"];
	//var DeudaActual=datos["17"];
	var totalpagado=datos["13"];
	var totaldescuento=datos["14"];
	var TotalVenta=datos["3"];
	var InteresActual=datos["15"];
	var deudaActualsininteres=datos["16"];
	
	 document.getElementById('inptTotalInteresCreditoActual').value=InteresActual;
	 document.getElementById('inptTotalPendienteSinInteresCredito').value=deudaActualsininteres;
	 document.getElementById('inptTotalPendienteCredito').value=deudaActual;
	 document.getElementById('inptTotalDeudaCredito').value=TotalDeuda;
	 diaatrazado=document.getElementById('inptDiasAtrazado').value;

imprimirDiv(nroRecibo,totalInteresPagado,TotalDeuda,totalpagado,totaldescuento,TotalVenta,InteresActual,deudaActualsininteres)
cargarPagoAuditoria(Monto)
buscarcuentasdetalles(idCodVenta)
buscarcuentaspagosrealizados(idCodVenta)
 
			}
			else
			{
			
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR","alert")

			}
			
			
          }catch(error){
					
					alert("Error Fatal: "+error)
					
				}
	
	
	
		
		 
			
			
		 
					
			}
			});
				
	
}
var controlInsercionsPagosParciales=true;
function verificarCargarPagos(){
	var inptMontoaCobrarPago = document.getElementById('inptMontoaCobrarPago').value
	var inptFechaCobrarPago = document.getElementById('inptFechaCobrarPago').value
	var inptNroRreciboPago = document.getElementById('inptNroRreciboPago').value
   if(controlInsercionsPagosParciales==false){
	   	ver_vetana_informativa("AGUARDE UN MOMENTO, SE ESTA PROCESANDO OTRO PAGO","abmpagos")
	   return;
   }
controlInsercionsPagosParciales=false;

	if(idVenta==""){
		ver_vetana_informativa("FALTO SELECCIONAR UNA CUENTA","abmpagos")
		return
	}
	if(inptMontoaCobrarPago==""){
		ver_vetana_informativa("FALTO INGRESAR EL MONTO","abmpagos")
		return
	}
	if(idcajaApp==""){
		ver_vetana_informativa("FALTO INICIALIZAR UNA CAJA","abmpagos")
		return
	}
	
	
	
	abmcargarpagos(idcajaApp,idVenta,inptMontoaCobrarPago,inptFechaCobrarPago,inptNroRreciboPago)
	
}
function abmcargarpagos(codAperturaApp,cod_venta,Monto,fecha,nrorecibo){
	 verCerrarVentanaCargando("1")	
	var accion="cargarpago"

	obtener_datos_user()
	  var datos = new FormData();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("cod_venta" , cod_venta)
			  datos.append("Monto" , Monto)
			  datos.append("fecha" , fecha)
			   datos.append("lot" , logPago)
			  datos.append("lat" , latPago)
			  datos.append("nrorecibo" , nrorecibo)
			 datos.append("codAperturaApp" , codAperturaApp)
					
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmpagos.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 ver_vetana_informativa("ERROR DE CONEXIÓN","error")
 verCerrarVentanaCargando("2")	
controlInsercionsPagosParciales=true;
					 return false;
			},
			success: function(responseText)
			{
				 controlInsercionsPagosParciales=true;
			  	 verCerrarVentanaCargando("2")	 
			Respuesta=responseText;			
				console.log(Respuesta)
	
	try{
		
	
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		
		  if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				} 
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
			
			if (Respuesta=="camposvacio")
			{
		
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...","alert")
						return false;
				}
			if (Respuesta=="duplicado")
			{
		ver_vetana_informativa("YA EXISTE UN REGISTRO SIMILAR...","alert")
						return false;
			}
		
		if (Respuesta=="exito")
			{					 
				ver_vetana_informativa("DATOS GUARDADOS...","alert")
			ver_cerrar_vista_opciones_pago("2")
			 	document.getElementById('inptTotalVentaCredito').value=datos["3"]
	document.getElementById('inptTotalPagadoCredito').value=datos["2"];
	totalpagado=datos["2"];
	deudaActual=datos["4"];
	paginaticket=datos["5"];
	cuotasNro=datos["6"];
	nroRecibo=datos["7"];
	pagado=Monto;
	
 var totalInteresPagado=datos["11"];
	var TotalDeuda=datos["12"];
	var DeudaActual=datos["17"];
	var totalpagado=datos["13"];
	var totaldescuento=datos["14"];
	var TotalVenta=datos["3"];
	var InteresActual=datos["15"];
	var deudaActualsininteres=datos["16"];
	 document.getElementById('inptTotalInteresCreditoActual').value=InteresActual;
	 document.getElementById('inptTotalPendienteSinInteresCredito').value=deudaActualsininteres;
	 document.getElementById('inptTotalPendienteCredito').value=DeudaActual;
	 document.getElementById('inptTotalDeudaCredito').value=TotalDeuda;
	 diaatrazado=document.getElementById('inptDiasAtrazadoCargarPago').value;

imprimirDiv(nroRecibo,totalInteresPagado,TotalDeuda,totalpagado,totaldescuento,TotalVenta,InteresActual,deudaActualsininteres)
cargarPagoAuditoria(Monto)
	buscarcuentasdetalles(cod_venta)
buscarcuentaspagosrealizados(cod_venta)
			}
			else
			{
			
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR","alert")

			}
			
          }catch(error){
					
					alert("Error Fatal: "+error)
					
				}
	
	
	
		
		 
			
			
		 
					
			}
			});
				
	
}



var contenidoOriginal="";
var controlImpresion=""
var totalpagado=""
function ReimprimirDiv (datos){

    var pago=document.getElementById("td_4_"+datos.id).innerHTML
    var fechapago=document.getElementById("td_3_"+datos.id).innerHTML
    var cobrador=document.getElementById("td_5_"+datos.id).innerHTML
    var nrofactura=document.getElementById("td_2_"+datos.id).innerHTML
    var paginatic=document.getElementById("td_6_"+datos.id).innerHTML
    var plazo=document.getElementById("td_7_"+datos.id).innerHTML
    var totalpagoencuota=document.getElementById("td_8_"+datos.id).innerHTML
    var totalpagoeninteres=document.getElementById("td_9_"+datos.id).innerHTML
	
	
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
    document.getElementById('inptFechaPago').value=f.getFullYear()+"-"+mes+"-"+dia;
	
pagina="<div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >FLEYKOOP</p>"
+"<p class='pTituloTicket2'>"
+"RUC 4554943-5"
+"<br>Villarrica, py "
+"</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Recibo de Dinero</b></p>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Numero :</b></td>"
+"<td style=''>"+nrofactura+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+document.getElementById("inptNombreClienteCredito").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>RUC o C.I.:</b></td>"
+"<td style=''>"+document.getElementById("inptDocClienteCredito").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Re-Impresión:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaPago").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Pago:</b></td>"
+"<td style=''>"+fechapago+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cobrador:</b></td>"
+"<td style=''>"+cobrador+" </td>"
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
+paginatic
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Interes Pagado:</b></td>"
+"<td style=''>"+totalpagoeninteres+" Gs</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Cuotas Pagadas:</b></td>"
+"<td style=''>"+totalpagoencuota+" Gs</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Pagado:</b></td>"
+"<td style=''>"+pago+" Gs</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Cuota :</b></td>"
+"<td style=''>"+plazo+" </td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center></div>"


var ficha=pagina;
	
	controlImpresion="on"
	document.getElementById("divimpr").innerHTML=pagina
	   var contenido= document.getElementById("divimpr").innerHTML;
	  contenido=b64EncodeUnicode(contenido)
	  urlRepor="/GoodVentaElim/app/reporte.html?ticket="+contenido;
     activarAndroid("report")
	 document.getElementById("divimpr").innerHTML="";
      // contenidoOriginal= document.body.innerHTML;

     // document.body.innerHTML = contenido;

     // window.print();

     
}

function imprimirDiv(NroRecibo,totalInteresPagado,DeudaActual,totalpagado,totaldescuento,TotalVenta,InteresActual,deudaActualsininteres){
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
    document.getElementById('inptFechaPago').value=f.getFullYear()+"-"+mes+"-"+dia;
	
pagina="<div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >FLEYKOOP</p>"
+"<p class='pTituloTicket2'>"
+"RUC 4554943-5"
+"<br>Villarrica, py "
+"</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Recibo de Dinero</b></p>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Numero :</b></td>"
+"<td style=''>"+nroRecibo+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+document.getElementById("inptNombreClienteCredito").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>RUC o C.I.:</b></td>"
+"<td style=''>"+document.getElementById("inptDocClienteCredito").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaPago").value+" "+hora+":"+min+"</td>"
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
+"<td style='width:100px'><b>Pagado</b></td>"
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
+"<td style='width:110px'><b>Interes Pagado:</b></td>"
+"<td style=''>"+totalInteresPagado+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Pagado:</b></td>"
+"<td style=''>"+totalpagado+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Intereses Pendiente:</b></td>"
+"<td style=''>"+InteresActual+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total sin Interes:</b></td>"
+"<td style=''>"+deudaActualsininteres+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Deuda:</b></td>"
+"<td style=''>"+DeudaActual+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:85px'><b>Cobrador/a:</b></td>"
+"<td style=''>"+document.getElementById("lbluser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center></div>"


var ficha=pagina;
	
	controlImpresion="on"
	document.getElementById("divimpr").innerHTML=pagina
	   var contenido= document.getElementById("divimpr").innerHTML;
	  contenido=b64EncodeUnicode(contenido)
	  urlRepor="/GoodVentaElim/app/reporte.html?ticket="+contenido;
     activarAndroid("report")
	 document.getElementById("divimpr").innerHTML="";
      // contenidoOriginal= document.body.innerHTML;

     // document.body.innerHTML = contenido;

     // window.print();

     
}

function b64EncodeUnicode(str) {
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
        return String.fromCharCode(parseInt(p1, 16))
    }))
}

function b64_to_utf8(str) {
    return decodeURIComponent(Array.prototype.map.call(atob(str), function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
    }).join(''))
}



function imprimirDivVenta(idCodVenta){
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
    document.getElementById('inptFechaPago').value=f.getFullYear()+"-"+mes+"-"+dia;
	

	
	pagina="<div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >COMPROBANTE DE PAGO</p>"
+"<p class='pTituloTicket2'>CASA TOLEDO"
+"<br>Telf. (0983) 859078"
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
+"<td style=''>"+document.getElementById("inptFechaPago").value+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>D. Atrasado</b></td>"
+"<td style=''>0 Día(s)</td>"
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
+"<td style='width:80px'><b>D. Actual:</b></td>"
+"<td style=''>"+deudaActual+" Gs.</td>"
+"</tr>"
+"</table>"
+"</div>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:85px'><b>Cobrador/a:</b></td>"
+"<td style=''>"+document.getElementById("lbluser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</center>"
"</div>"
var ficha=pagina;
 /*
  var ventimp=window.open();
   ventimp.document.write(ficha);;
  ventimp.print();
  ventimp.close();
  
     document.open();
      document.write(ficha.innerHTML);
	  document.print();
      document.close();*/
 
	
	controlImpresion="on"
	document.getElementById("divimpr").innerHTML=pagina
	 
	    var contenido= document.getElementById("divimpr").innerHTML;
	  contenido=b64EncodeUnicode(contenido)
	   urlRepor="/GoodVentaElim/app/reporte.html?ticket="+contenido;
	
     activarAndroid("report")
      // contenidoOriginal= document.body.innerHTML;

     // document.body.innerHTML = contenido;

     // window.print();
 document.getElementById("divimpr").innerHTML="";
     
}



/*
DEVOLUCIONES
*/
var idAbmDetalle="";
var idAbmVenta=""
var idAbmGarantia=""
function verClienteDevoluciones(){
	//$("div[id=divPrincipalVistaCliente]").css({"overflow":"auto"})
	//$("div[id=divPrincipalVistaCliente]").animate({ scrollTop: 250 },400);
	document.getElementById('divPrincipalVistaCliente').style.display=''
	controlSeleccCliente="3"
}
var contolSearchProductos="";
function verProductosDevoluciones(){
	// $("div[id=divPrincipalVistaProductos]").css({"overflow":"auto"})
	//$("div[id=divPrincipalVistaProductos]").animate({ scrollTop: 250 },400);
	document.getElementById('divPrincipalVistaProductos').style.display=''
	contolSearchProductos="2"
}
function obtenerdatosProductoDevoluciones(elemento){

	idProductoPedido=$(elemento).children('td[id="td_1"]').html();
	document.getElementById('inptProductoDevolucion').value=$(elemento).children('td[id="td_2"]').html();
	 idAbmDetalle=$(elemento).children('td[id="td_3"]').html();
	 idAbmVenta=$(elemento).children('td[id="td_4"]').html();
	
	ver_cerrar_vista_productos("2")
  
	
}
function buscarhistorialDevolucione(){

	document.getElementById('divDetalleDevolucion').innerHTML=imgCargandoA;

				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idAbmCliente,
			"funt": "buscarporvista"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/abmDevoluciones.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divDetalleDevolucion').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divDetalleDevolucion').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				var nroRegistro=datos["3"];
				if(datosBuscado==""){
					
					return;
				}
				
				document.getElementById('divDetalleDevolucion').innerHTML=datosBuscado;

					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}
function VerificarCampoDevoluciones(){
	var inptFechaDevolucion = document.getElementById('inptFechaDevolucion').value
	var inptSeleccOperacion=document.getElementById('inptSeleccOperacion').value
	var inptProductoMotivoDevolucion=document.getElementById('inptProductoMotivoDevolucion').value
	if(idAbmCliente==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN CLIENTE","abmpedidos")
		return
	}
	if(idAbmDetalle==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO ","abmpedidos")
		return
	}
	if(inptFechaDevolucion==""){
		ver_vetana_informativa("FALTO SELECCIONAR UNA FECHA ","abmpedidos")
		return
	}
	
	
	AbmDevoluciones(idAbmCliente,idAbmDetalle,inptFechaDevolucion,inptSeleccOperacion,inptProductoMotivoDevolucion,idAbmVenta,idAbmGarantia)
	
}
function AbmDevoluciones(idAbmCliente,cod_detalleFK,Fecha,estado,Motivo,cod_ventaFK,Cod_Garantia){
	 verCerrarVentanaCargando("1")	
	var accion="nuevo"
	if(Cod_Garantia!=""){
		accion="editar"
	}
	
	obtener_datos_user()
	  var datos = new FormData();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("Cod_Garantia" , Cod_Garantia)
			 datos.append("Fecha" , Fecha)
			  datos.append("Motivo" , Motivo)
			  datos.append("cod_detalleFK" , cod_detalleFK)
			  datos.append("estado" , estado)
			  datos.append("cod_ventaFK" , cod_ventaFK)
			 
			
					
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmDevoluciones.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 ver_vetana_informativa("ERROR DE CONEXIÓN","error")
 verCerrarVentanaCargando("2")	
					 return false;
			},
			success: function(responseText)
			{
			   verCerrarVentanaCargando("2")		 
			Respuesta=responseText;			
				console.log(Respuesta)
	
	
	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		
		  if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				} 
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
			
			if (Respuesta=="camposvacio")
			{
		
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...","alert")
						return false;
				}
			if (Respuesta=="duplicado")
			{
		ver_vetana_informativa("YA EXISTE UN REGISTRO SIMILAR...","alert")
						return false;
			}
		
		if (Respuesta=="exito")
			{					 
				ver_vetana_informativa("DATOS GUARDADOS...","alert")
			document.getElementById('inptProductoMotivoDevolucion').value="";
			document.getElementById('inptFechaDevolucion').value="";
			document.getElementById('inptProductoDevolucion').value="";
			cod_detalleFK="";
			cod_ventaFK="";
              buscarhistorialDevolucione()
		

			}
			else
			{
			
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR","alert")

			}
			
		
          }catch(error){
					
					alert("Error Fatal: "+error)
					
				}
	
	
	
		
		 
			
			
		 
					
			}
			});
			
	
}


/*
CERRAR SESION 
*/
function cerrarSesion(){
	 verCerrarVentanaCargando("1")	
	document.cookie="user=;max-age=86400;path=/";
    document.cookie="pass=;max-age=86400;path=/";
	obtener_datos_user()
	  var datos = new FormData();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
						
					
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/cerrarsesion.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 ver_vetana_informativa("ERROR DE CONEXIÓN","error")
 verCerrarVentanaCargando("2")	
					 return false;
			},
			success: function(responseText)
			{
			  	 verCerrarVentanaCargando("2")	 
			Respuesta=responseText;			
				console.log(Respuesta)
	
	
	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		
		  if (Respuesta=="usuarioincorrecto")
			{
			  localStorage.clear();
				window.location="/GoodVentaElim/app/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				} 
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
			
			if (Respuesta=="camposvacio")
			{
		
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...","alert")
						return false;
				}
			if (Respuesta=="duplicado")
			{
		ver_vetana_informativa("YA EXISTE UN REGISTRO SIMILAR...","alert")
						return false;
			}
		
		if (Respuesta=="exito")
			{		
  localStorage.clear();

				window.location="/GoodVentaElim/app/login.html";		
           
		

			}
			else
			{
			
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR","alert")

			}
			
		
          }catch(error){
					
					alert("Error Fatal: "+error)
					
				}
	
	
	
		
		 
			
			
		 
					
			}
			});
			
	
}



/*
MIS RECAUDACIONES
*/
function buscarmisRecaudaciones(){

	
	document.getElementById('divMisRecaudacionesBuscado').innerHTML=imgCargandoA;
var buscar=document.getElementById('inptBuscarFechaMisRecaudaciones').value
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": buscar,
			"funt": "buscarmisrecaudaciones"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/abmpagos.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divMisRecaudacionesBuscado').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divMisRecaudacionesBuscado').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				var nroRegistro=datos["4"];
				if(datosBuscado==""){
		document.getElementById('lblRegistrosEncontradosMisRecaudaciones').innerHTML="No se encontraron registros";
					
					return;
				}
				
				document.getElementById('divMisRecaudacionesBuscado').innerHTML=datosBuscado;
				document.getElementById('inptTotalRecaudado').value=datos["3"];;
				document.getElementById('lblRegistrosEncontradosMisRecaudaciones').innerHTML="Se encontraron "+nroRegistro+" resgitro(s)";

					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}

/*
ABM LISTADO
*/
function obtenerdatosProductoslista(elemento){

	codProductoListado=$(elemento).children('td[id="td_1"]').html();
	document.getElementById('inptSeleccProductoListado').value=$(elemento).children('td[id="td_2"]').html();
	ver_cerrar_vista_productos("2")
  
}
function buscarvistacobrador(){
 
 

	 
	
var buscador=document.getElementById('inptBuscarVistaCobrador').value
		 document.getElementById("table_vista_cobrador").innerHTML=imgCargandoA
		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": buscador,
			"funt": "buscarvista"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php_system/abmcobrador.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	
			document.getElementById("table_vista_cobrador").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_vista_cobrador").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
		 if (Respuesta=="UI")
			{
		
			
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			
					ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - BUSCAR CLIENTE")
						return false;
					


			} 
			if (Respuesta == "exito")
			{
				
				
				
		  var datos_buscados=datos[2];
		 
			document.getElementById("table_vista_cobrador").innerHTML=datos_buscados

	  
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}
function obtenerdatosvistacobrador(datostr){
	
	
		 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });

    datostr.className='tableRegistroSelec'
	
	
	
		codEncargadoListado= $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptSeleccEncargadoProductoListado').value=$(datostr).children('td[id="td_datos_1"]').html();
	
	
	document.getElementById("divPrincipalVistaEncargado").style.display="none"
	
	
	
  
  
  
	
}
function verCerrarVentanaAbmListado(d,l){
	document.getElementById('divPrincipalVistaLista').style.display="none"
	if(d=="1"){
		document.getElementById('divPrincipalVistaLista').style.display=""
	
	}
}
function verVentanaEditarListado(){
	if(idAbmListado==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO","#")
		return;
	}
	verCerrarVentanaAbmListado("1","2")
}
var idAbmListado=""
var codProductoListado="";
var codEncargadoListado="";
function obtenerdatosabmListado(datostr){
	
	
		 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });

    datostr.className='tableRegistroSelec'
	document.getElementById('inptSeleccProductoListado').value=$(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptSeleccEncargadoProductoListado').value=$(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptCantProductoListado').value=$(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptFechaProductoListado').value=$(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptEstadoProductoListado').value=$(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptCantVentaProductoListado').value=$(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptAccesoProductoListado').value=$(datostr).children('td[id="td_datos_9"]').html();
	idAbmListado= $(datostr).children('td[id="td_id"]').html();
	codProductoListado= $(datostr).children('td[id="td_datos_1"]').html();
	codEncargadoListado= $(datostr).children('td[id="td_datos_7"]').html();
  verCerrarVentanaAbmListado("2")
  
	
}
function verificarcamposListado(){
	
	var inptSeleccEncargadoProductoListado=document.getElementById('inptSeleccEncargadoProductoListado').value
	var inptSeleccProductoListado=document.getElementById('inptSeleccProductoListado').value
	var inptCantProductoListado=document.getElementById('inptCantProductoListado').value
	var inptFechaProductoListado=document.getElementById('inptFechaProductoListado').value
	var inptEstadoProductoListado=document.getElementById('inptEstadoProductoListado').value
	var inptCantVentaProductoListado=document.getElementById('inptCantVentaProductoListado').value
	var inptAccesoProductoListado=document.getElementById('inptAccesoProductoListado').value
	

  if(inptSeleccEncargadoProductoListado==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN ENCARGADO","#")
	  return false;
  }
  if(inptSeleccProductoListado==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO","#")
	  return false;
  }
  if(inptCantProductoListado==""){
	ver_vetana_informativa("FALTO INGRESAR LA CANTIDAD","#")
	  return false;
  }
  if(inptFechaProductoListado==""){
	ver_vetana_informativa("FALTO SELECCIONAR UNA FECHA","#")
	  return false;
  }
 
 
 
  var accion="";
  if(idAbmListado!=""){
	  accion="editar";
  }else{
	  accion="nuevo";
  }
  abmlistado(inptAccesoProductoListado,inptCantProductoListado,inptFechaProductoListado,inptEstadoProductoListado,codProductoListado,codEncargadoListado,inptCantVentaProductoListado,idAbmListado,accion);
}
function abmlistado(acceso,cant,fecha,estado,cod_producto,cod_cobrador,cantvendido,idlistado,accion){
	
	
	verCerrarVentanaCargando("1")
	  var datos = new FormData();
			obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("idlistado" , idlistado)
			  datos.append("cant" , cant)
			 datos.append("fecha" , fecha)
			 datos.append("estado" , estado)
			 datos.append("cod_producto" , cod_producto)
			 datos.append("cod_cobrador" , cod_cobrador)
			 datos.append("cantvendido" , cantvendido)
			 datos.append("acceso" , acceso)
		
		
			
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmlistado.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
						verCerrarVentanaCargando("")
					 ver_vetana_informativa("ERROR DE CONECCIÓN")

					 return false;
			},
			success: function(responseText)
			{
			  	 verCerrarVentanaCargando("")
			Respuesta=responseText;			
				console.log(Respuesta)
		try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		 

		 if (Respuesta=="UI")
			{
		
			
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			ocultarmensaje()
				ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - ABM CLIENTE")
						return false;
					


			} 
		 if (Respuesta=="camposvacio")
			{
		
			
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
						return false;
					


			}
			if (Respuesta=="EX")
			{
		
			
				ver_vetana_informativa("YA EXISTE UNA CLIENTE SIMILAR...")
						return false;
					


			}
			if (Respuesta=="exito")
			{
		
				 document.getElementById('inptSeleccProductoListado').value="";
	document.getElementById('inptCantProductoListado').value="";
	document.getElementById('inptCantVentaProductoListado').value="";
		document.getElementById('inptEstadoProductoListado').value="Activo";
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
						
					buscarabmListado()
					


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
function buscarabmListado(){
 
 

	 
	
var fecha1=document.getElementById('inptBuscarListaF1').value
var fecha2=document.getElementById('inptBuscarListaF1').value
var estado="Activo"
var buscar=document.getElementById('inptBuscarListado').value
		 document.getElementById("table_abm_listado").innerHTML=imgCargandoA
		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"fecha1": fecha1,
			"fecha2": fecha2,
			"estado": estado,
			"buscar": buscar,
			"funt": "buscar"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmlistado.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	
			document.getElementById("table_abm_listado").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_abm_listado").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
		 if (Respuesta=="UI")
			{
		
			
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			
					ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - BUSCAR CLIENTE")
						return false;
					


			} 
			if (Respuesta == "exito")
			{
				
				
				
		  var datos_buscados=datos[2];
		 
			document.getElementById("table_abm_listado").innerHTML=datos_buscados
		

	  
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}
function limpiarcamposListado(){
		
	 
  document.getElementById('inptSeleccProductoListado').value="";
	document.getElementById('inptSeleccEncargadoProductoListado').value="";
	document.getElementById('inptCantVentaProductoListado').value="";
	document.getElementById('inptCantProductoListado').value="";
	document.getElementById('inptFechaProductoListado').value="";
	document.getElementById('inptEstadoProductoListado').value="Activo";
	document.getElementById('inptAccesoProductoListado').value="SI";
	idAbmListado= "";
	codProductoListado= "";
	codEncargadoListado= "";
}
function buscaramiListado(){
 	
var buscar=document.getElementById('inptBuscarMiListado').value
document.getElementById("table_mi_listado").innerHTML=imgCargandoA
		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": buscar,
			"funt": "buscarmislista"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmlistado.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	
			document.getElementById("table_mi_listado").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_mi_listado").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
		 if (Respuesta=="UI")
			{
		
			
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			
					ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - BUSCAR CLIENTE")
						return false;
					


			} 
			if (Respuesta == "exito")
			{
				
				
				
		  var datos_buscados=datos[2];
		 
			document.getElementById("table_mi_listado").innerHTML=datos_buscados
		

	  
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}
var elementodetallesproducto;
var codproductopedido="";
var idlistadopedido="";
function seleccionarproductodesdelista(datos){
	elementodetallesproducto=datos;
	vercerrardetallesproducto("1")
}
var costoProducto=0;
function vercerrardetallesproducto(d){
	document.getElementById("divPrincipalDetalles").style.display="none"
	if(d=="1"){
		document.getElementById("divPrincipalDetalles").style.display=""
		var datostr=elementodetallesproducto;
		idlistadopedido=$(datostr).children('td[id="td_id"]').html();
		codproductopedido=$(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById("inptProductoDetalle").value=$(datostr).children('td[id="td_datos_2"]').html();
		document.getElementById("inptSelecCostoProductoDetalle").innerHTML=$(datostr).children('td[id="td_datos_10"]').html();
		document.getElementById("inptCostoProductoDetalle").value=$(datostr).children('td[id="td_datos_9"]').html();
		document.getElementById("inptComisionProductoDetalle").value=$(datostr).children('td[id="td_datos_11"]').html();
		costoProducto=$(datostr).children('td[id="td_datos_12"]').html();
		document.getElementById("inptCantProductoDetalle").value="1";
		
	}
	
}

function seleccionarcostodetalle(datos){
	document.getElementById("inptCostoProductoDetalle").value=datos.value;
	document.getElementById("inptComisionProductoDetalle").value=$(datos).children(":selected").attr("name");
}
function calcular_total_con_entrega(){
	var t=QuitarSeparadorMilValor(document.getElementById('inptTotalVentaPagos').value);
	var c=QuitarSeparadorMilValor(document.getElementById('inptEntregaPapo').value);
	if(isNaN(c)){
	
		document.getElementById('inptEntregaPapo').value=0;
		document.getElementById('inptTotalVentaPagosb').value=document.getElementById('inptTotalVentaPagos').value;
		c=0;
	}
	var c=parseFloat(c);
	var t=parseFloat(t);
	if(c>t){
		ver_vetana_informativa("EL MONTO NO PUEDE SER MAYOR AL TOTAL")
				document.getElementById('inptEntregaPapo').value=0;

				document.getElementById('inptTotalVentaPagosb').value=document.getElementById('inptTotalVentaPagos').value;

		return
	}
	document.getElementById('inptTotalVentaPagosb').value=t-c;
	separadordemiles(document.getElementById('inptTotalVentaPagosb'))
	separadordemiles(document.getElementById('inptEntregaPapo'))
	if(document.getElementById("inptNroCuotasPagos").value!=""){
	calcular_cuota()
	}
}
function calcular_cuota(){
	var t=QuitarSeparadorMilValor(document.getElementById('inptTotalVentaPagosb').value);
	var c=QuitarSeparadorMilValor(document.getElementById('inptNroCuotasPagos').value);
	if(isNaN(c)){
	
		document.getElementById('inptNroCuotasPagos').value=1;
		document.getElementById('inptMontoPagoOpciones').value=document.getElementById('inptTotalVentaPagosb').value;
		c=0;
	}
	var c=parseFloat(c);
	var t=parseFloat(t);
	document.getElementById('inptMontoPagoOpciones').value=t/c;
	//separadordemiles(document.getElementById('inpt_interes_pago_venta'))
	separadordemiles(document.getElementById('inptMontoPagoOpciones'))
}

var nropedidocarrito=0;
var paginacarrito="";
function addProductoCarrito(){
paginacarrito+="<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>"+
"<tr id='tbSelecRegistroVenta'  >"+
"<td id='td_datos_1' style='display:none'>"+idlistadopedido+"</td>"+
"<td id='td_datos_2' style='display:none'>"+codproductopedido+"</td>"+
"<td id='td_datos_3' style='width:33%'>"+document.getElementById("inptProductoDetalle").value+"</td>"+
"<td id='td_datos_4' style='width:33%'>"+document.getElementById("inptCantProductoDetalle").value+"</td>"+
"<td  id='td_datos_5' style='width:33%'>"+document.getElementById("inptSelecCostoProductoDetalle").value+"</td>"+
"<td  id='td_datos_6' style='display:none'>"+document.getElementById("inptSelecCostoProductoDetalle").innerHTML+"</td>"+
"<td  id='td_datos_7' style='display:none'>"+document.getElementById("inptComisionProductoDetalle").innerHTML+"</td>"+
"<td  id='td_datos_8' style='display:none'>"+costoProducto+"</td>"+
"</tr>"+
"</table>";
	nropedidocarrito=nropedidocarrito+1;
	document.getElementById('lblidcarrito').innerHTML=nropedidocarrito
	
	vercerrardetallesproducto("2")
}
function calcularElTotalVenta(){
	var total=0;
	$("tr[id=tbSelecRegistroVenta]").each(function(i, td){		
		var cant=$(td).children('td[id="td_datos_4"]').html();
		var costo=$(td).children('td[id="td_datos_5"]').html();
		cant=QuitarSeparadorMilValor(cant);
		costo=QuitarSeparadorMilValor(costo);
		total=Number(total)+(Number(cant)*Number(costo));
	 });
	 document.getElementById("inptTotalVentaPagos").value=separadordemilesnumero(total);
	 document.getElementById("inptTotalVentaPagosb").value=separadordemilesnumero(total);
	 document.getElementById("inptTotalVenta").value=separadordemilesnumero(total);
}
function verCerrarMiCarrito(d){
	
	document.getElementById("divMiCarrito").style.display="none"
	if(d=="1"){
		if(nropedidocarrito==0){
			ver_vetana_informativa("NO TIENES PRODUCTOS EN TU CARRITO")
			return;
		}
		document.getElementById('divVenta').innerHTML=paginacarrito
		document.getElementById("divMiCarrito").style.display=""
		document.getElementById("inptEntregaPapo").value=0
				var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()+1
	if(mes<10){
		mes="0"+mes;
	}
	 document.getElementById('inptFechaInicioPapo').value=f.getFullYear()+"-"+mes+"-"+dia;
		document.getElementById("inptCobradorVenta").value=document.getElementById('lbluser').innerHTML
	idCobradorVenta=userid;
	calcularElTotalVenta()
		
	}
	
}
var idclienteVenta="";
var idCobradorVenta="";
var idvendedor1="";
var idvendedor2="";
function verclienteenventa(){

			document.getElementById('divPrincipalVistaCliente').style.display=''
	controlSeleccCliente="4"
}

function verificarcamposVenta(){
	
	var inptNroventa=document.getElementById('inptNroventa').value
	var inptClienteVenta=document.getElementById('inptClienteVenta').value
	var inptEntregaPapo=document.getElementById('inptEntregaPapo').value
	var inptTotalVentaPagos=document.getElementById('inptTotalVentaPagos').value
	var inptTotalVentaPagosb=document.getElementById('inptTotalVentaPagosb').value
	var inptNroCuotasPagos=document.getElementById('inptNroCuotasPagos').value
	var inptMontoPagoOpciones=document.getElementById('inptMontoPagoOpciones').value
	var inptFechaInicioPapo=document.getElementById('inptFechaInicioPapo').value
	var inptSeleccTipoVenta=document.getElementById('inptSeleccTipoVenta').value
	var inputSelectMetodoCambio=document.getElementById('inputSelectMetodoCambio').value
	var inptEntregaCobrado=document.getElementById('inptPagoVentaPagosb').value
	
  if(inptSeleccTipoVenta=="CREDITO"){
	   if(inptEntregaCobrado==""){
	ver_vetana_informativa("FALTO INGRESAR EL PAGO INICIAL","#")
	  return false;
  }
   if(inptEntregaPapo==""){
	ver_vetana_informativa("FALTO INGRESAR LA ENTREGA","#")
	  return false;
  }
  if(inptNroCuotasPagos==""){
	ver_vetana_informativa("FALTO INGRESAR EL NRO DE CUOTAS","#")
	  return false;
  }
  if(inptMontoPagoOpciones==""){
	ver_vetana_informativa("FALTO INGRESAR EL MONTO DE CUOTAS","#")
	  return false;
  } 
  if(inptFechaInicioPapo==""){
	ver_vetana_informativa("FALTO INGRESAR LA FECHA DE INICIO DE PAGO","#")
	  return false;
  }
  if(inputSelectMetodoCambio==""){
	ver_vetana_informativa("FALTO SELECCIONAR EL METODO DE COBRO","#")
	  return false;
  }
  
  }
    
 
  if(inptNroventa==""){
	ver_vetana_informativa("FALTO INGRESAR EL NRO DE VENTA","#")
	  return false;
  }
  if(inptClienteVenta==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN CLIENTE","#")
	  return false;
  }
 
 
  
  var accion="nuevo";
  
  abmVentas(idclienteVenta,idCobradorVenta,idvendedor1,idvendedor2,inptSeleccTipoVenta,inputSelectMetodoCambio,inptNroventa,inptEntregaPapo,inptTotalVentaPagos,inptTotalVentaPagosb,inptNroCuotasPagos,inptMontoPagoOpciones,inptFechaInicioPapo,inptEntregaCobrado,accion);
}


function abmVentas(idclienteVenta,cod_cobrador,idvendedor1,idvendedor2,tipoventa,metodo,nroventa,entrega,total,totalb,nrocuota,monto,fecha,EntregaCobrado,accion){
	
	
	verCerrarVentanaCargando("1")
	  var datos = new FormData();
	 var nro=0;
	  $("tr[id=tbSelecRegistroVenta]").each(function(i, td){
		  
		var idlistado=$(td).children('td[id="td_datos_1"]').html();
		var codProduc=$(td).children('td[id="td_datos_2"]').html();
		var cant=$(td).children('td[id="td_datos_4"]').html();
		var precio=$(td).children('td[id="td_datos_5"]').html();
		var comision=$(td).children('td[id="td_datos_7"]').html();
		var costo=$(td).children('td[id="td_datos_8"]').html();
		 datos.append("idlistado"+nro, idlistado)
		 datos.append("codProduc"+nro, codProduc)
		 datos.append("cant"+nro, cant)
		 datos.append("precio"+nro, precio)
		 datos.append("comision"+nro, comision)
		 datos.append("costo"+nro, costo)
		 nro=nro+1;
	 });
			obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("nrodetalle", nro)
			 datos.append("idclienteVenta" , idclienteVenta)
			 datos.append("idCobradorVenta" , idCobradorVenta)
			 datos.append("idvendedor1" , idvendedor1)
			 datos.append("cod_cobrador" , cod_cobrador)
			 datos.append("idvendedor2" , idvendedor2)
			 datos.append("nrocuota" , nrocuota)
			 datos.append("nroventa" , nroventa)
			 datos.append("entrega" , entrega)
			 datos.append("total" , total)
			 datos.append("totalb" , totalb)
			 datos.append("monto" , monto)
			 datos.append("fecha" , fecha)
			 datos.append("tipoventa" , tipoventa)
			 datos.append("metodo" , metodo)
			 datos.append("EntregaCobrado" , EntregaCobrado)
		     datos.append("lot" , logPago)
			  datos.append("lat" , latPago)
		
			
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmventa.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
						verCerrarVentanaCargando("")
					 ver_vetana_informativa("ERROR DE CONECCIÓN")

					 return false;
			},
			success: function(responseText)
			{
			  	 verCerrarVentanaCargando("")
			Respuesta=responseText;			
				console.log(Respuesta)
		try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		 

		 if (Respuesta=="UI")
			{
		
			
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			ocultarmensaje()
				ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - ABM CLIENTE")
						return false;
					


			} 
		 if (Respuesta=="camposvacio")
			{
		
			
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
						return false;
					


			}
			
			if (Respuesta=="exito")
			{
		
		
	deudaActual=datos["4"];
	paginaticket=datos["5"];
	cuotasNro=datos["6"];
	pagado=datos["2"];;
	imprimirDivVenta()
	
	limpiarCamposventa();
	// document.getElementById('inptNroventa').value=""
	// document.getElementById('inptClienteVenta').value=""
	// document.getElementById('inptEntregaPapo').value=""
	// document.getElementById('inptTotalVentaPagos').value=""
	// document.getElementById('inptTotalVentaPagosb').value=""
	// document.getElementById('inptNroCuotasPagos').value=""
	// document.getElementById('inptMontoPagoOpciones').value=""
	// document.getElementById('inptFechaInicioPapo').value=""
	// document.getElementById('inptSeleccTipoVenta').value=""
	// document.getElementById('inputSelectMetodoCambio').value=""
	// document.getElementById('inptEntregaCobrado').value=""
	// idclienteVenta="";
    // idCobradorVenta="";
    // idvendedor1="";
    // idvendedor2="";
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

function limpiarCamposventa(){
	document.getElementById("inptNroventa").value=""
	document.getElementById("inptClienteVenta").value=""
	document.getElementById("inptVendedor1Venta").value=""
	document.getElementById("inptVendedor2Venta").value=""
	document.getElementById("inptTotalVenta").value=""
	document.getElementById("inptPagoVentaPagosb").value=""
	document.getElementById("inptTotalVentaPagos").value=""
	document.getElementById("inptEntregaPapo").value=""
	document.getElementById("inptTotalVentaPagosb").value=""
	document.getElementById("inptNroCuotasPagos").value=""
	document.getElementById("inputSelectMetodoCambio").value=""
	document.getElementById("inptMontoPagoOpciones").value=""
	document.getElementById("divVenta").innerHTML=""
	
    idclienteVenta="";
    idCobradorVenta="";
    idvendedor1="";
    idvendedor2="";
	paginacarrito="";
	nropedidocarrito=0;
	document.getElementById('lblidcarrito').innerHTML=nropedidocarrito
	verCerrarMiCarrito("2")
}


/*VISTA VENDEDOR*/
var controlSeleccVendedor="";
function vercerrarvendedor(d,v){
document.getElementById('divPrincipalVistaVendedor').style.display='none'
if(d=="1"){
	document.getElementById('divPrincipalVistaVendedor').style.display=''
	controlSeleccVendedor=v
}
			
	
}

function buscarvendedor(){

	
	document.getElementById('divVendedorBuscado').innerHTML=imgCargandoA;
var buscar=document.getElementById('inptBuscarVendedor').value
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": buscar,
			"funt": "buscarvista"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/abmvendedor.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divVendedorBuscado').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divVendedorBuscado').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				var nroRegistro=datos["3"];
				if(datosBuscado==""){
		document.getElementById('lblRegistrosEncontradosvendedor').innerHTML="No se encontraron registros";
					
					return;
				}
				
				document.getElementById('divVendedorBuscado').innerHTML=datosBuscado;
				document.getElementById('lblRegistrosEncontradosvendedor').innerHTML="Se encontraron "+nroRegistro+" resgitro(s)";

					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}

function obtenerdatosvistavendedor(elemento){
	
	
	if(controlSeleccVendedor=="1"){
	
	idvendedor1=$(elemento).children('td[id="td_id"]').html();
	document.getElementById('inptVendedor1Venta').value=$(elemento).children('td[id="td_datos_1"]').html();
	}
	if(controlSeleccVendedor=="2"){
	
	idvendedor2=$(elemento).children('td[id="td_id"]').html();
	document.getElementById('inptVendedor2Venta').value=$(elemento).children('td[id="td_datos_1"]').html();
	}
	
	
vercerrarvendedor("2")
}


/*VENTANA INFORMATIVA*/
function cerrar_esta_ventanas(datos)
{
	$("div[id="+datos+"]").remove();
	var control='off'
		 	 $("div[name=ventanas_infos]").each(function(i, historial_publicacion){		
		 control='on'
		});
		if(control=='off')
		{
			document.getElementById('capa_informativa').style.display='none'
		}
}
function cerrar_ventanas(datos)
{
	$(datos).remove();
	var control='off'
		 	 $("div[name=ventanas_infos]").each(function(i, historial_publicacion){		
		 control='on'
		});
		if(control=='off')
		{
			document.getElementById('capa_informativa').style.display='none'
		}
}
function ver_vetana_informativa(titulo,id_c){
	var pagina_informativa="<div class='div_info_3' title='click para cerrar' onclick='cerrar_ventanas(this)' id='"+id_c+"' name='ventanas_infos'>"+
	"<table style='width:100%;height:100%;padding:15px'>"+
	"<tr>"+
	"<td >"+
	"<label class='label_info_a'>"+titulo+"</label>"+
	"</td>"+
	"</tr>"+
	"</table>"+
	"</div>"
	document.getElementById('capa_informativa').innerHTML=pagina_informativa
	document.getElementById('capa_informativa').style.display=''
	$("div[id=capa_informativa]").fadeOut(1500)
}

function ocultarmensaje(){

	document.getElementById('capa_informativa').innerHTML=""
	document.getElementById('capa_informativa').style.display='none'
}

/*UBICACION*/
var longitudSol="";
var latitudSol="";
function verGoogleMapsOr(){
	if(longitudSol!="" && latitudSol!="" ){
		 coordenasmap= longitudSol + ',' + latitudSol;
		 //window.open("https://www.google.com.py/maps/place/"+longitudSol+","+latitudSol);
		 activarAndroid("maps")
		//window.open("geo:" + latLon+"?daddr="+document.getElementById('inpDireccServicioOrdenTrabajo').value, "_system")
			//window.open("geo:"+longitudSol+","+latitudSol)
			//window.open("https://www.google.com.py/maps/place/"+longitudSol+","+latitudSol)

	}else{
				ver_vetana_informativa("NO SE ENCONTRARON COORDENADAS PARA VER")

		
	}
}
/*OTROS*/
function QuitarSeparadorMil(inputs){
	var i=inputs.value;
	i=i.replace(/\./g,'')
	i=i.replace(',','.')
	return i;

	
}
function QuitarSeparadorMilValor(inputs){
	var i=inputs;
   i=i.replace(/\./g, '')
	i=i.replace(',','.')
	return i;

	
}
function separadordemiles(input){
	
	var num=input.value.replace(/\./g,'');
	if(!isNaN(num)){
	var num2 = num.toString().split('.');
var thousands = num2[0].split('').reverse().join('').match(/.{1,3}/g).join('.');
var decimals = (num2[1]) ? ','+num2[1] : '';

var answer =  thousands.split('').reverse().join('')+decimals;  
		input.value=answer
	}else{
		/*alert('Esto no es un número')
		//input.value=input.value.replace(/[˄\d\.]*g,'');
		 asi va antes de la /g */
		
	}
	
}
function separadordemilesnumero(input){
	
	var num=input.toString().replace(/\./g,'');
	if(!isNaN(num)){
	var num2 = num.toString().split('.');
var thousands = num2[0].split('').reverse().join('').match(/.{1,3}/g).join('.');
var decimals = (num2[1]) ? ','+num2[1] : '';

var answer =  thousands.split('').reverse().join('')+decimals;  
		input=answer
	}else{
		/*alert('Esto no es un número')
		//input.value=input.value.replace(/[˄\d\.]*g,'');
		 asi va antes de la /g */
		
	}
	return input;
}
function verCerrarVentanaCargando(d){
	document.getElementById("div_principal_info_carga").style.display="none";
	if(d=="1"){
		document.getElementById("div_principal_info_carga").style.display="";
	}
	
}
/*OFFLINE*/

function cargarlistaoffline(){
		if (typeof(Storage) == "undefined") {
		
   ver_vetana_informativa("OFFLINE NO DISPONIBLE PARA SU WEBVIEW")
   return;
}

	var listaCuenta=localStorage.getItem("cuentasoffline");
	document.getElementById("divCuentasBuscado").innerHTML=listaCuenta;

}
var deudaActualOffline;
function cargarpagomodoffline(){
		if (typeof(Storage) == "undefined") {
		
   ver_vetana_informativa("OFFLINE NO DISPONIBLE PARA SU WEBVIEW")
   return;
}

	var inptMontoaCobrarPago = document.getElementById('inptMontoaCobrarPago').value
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
    var inptFechaCobrarPago =f.getFullYear()+"-"+mes+"-"+dia;
	var nrorecibo= document.getElementById('inptNroRreciboPago').value;
	var nombrecliente= document.getElementById('inptNombreClienteCredito').value;
	var pagosoffline=localStorage.getItem("pagosoffline");
if (pagosoffline == "undefined" || pagosoffline == "" || pagosoffline == "Null" || pagosoffline == null ) {
		
   pagosoffline="";
}
	var paginalistapagosoffline="<table id='tb_pagos_offline_"+idVenta+"'  class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>"
   +"<tr id='pagos_offline' name='pagos_offline_"+idVenta+"'  onclick='obtenerdatospagosoffline(this)' >"
   	+"<td id='' style='width:25%' >"+nombrecliente+"</td>"
   +"<td id='td_1' style='width:25%' >"+inptMontoaCobrarPago+"</td>"
	+"<td id='td_2' style='width:25%' >"+inptFechaCobrarPago+"</td>"
	+"<td id='td_3' style='display:none' >"+idVenta+"</td>"
	+"<td id='td_5' style='display:none' >"+nrorecibo+"</td>"
	+"<td id='td_4' style='width:25%' >"+hora+":"+min+"</td>"
	+"</tr>"
  +"</table>"
  localStorage.setItem("pagosoffline", paginalistapagosoffline+pagosoffline);
  diaatrazado=document.getElementById('inptDiasAtrazadoCargarPago').value;
  pagado=document.getElementById('inptMontoaCobrarPago').value;
   var montoPagado=QuitarSeparadorMilValor(inptMontoaCobrarPago);
   var diasatrazado=0;
	   var controlCuota=QuitarSeparadorMilValor(inptMontoaCobrarPago);
	   var control='on'
   $("tr[name=pagoscuenta_"+idVenta+"]").each(function(i, elementohtml){
	  
	  var estado=$(elementohtml).children('td[id="td_6"]').html();
	    control="on"
	  if(estado=="Pendiente" && controlCuota>1){
		   var deudad=$(elementohtml).children('td[id="td_9"]').html();
		   	  deudad=QuitarSeparadorMilValor(deudad)
		 
		

	  if(controlCuota>=deudad){
		  cuotasNro+=" "+$(elementohtml).children('td[id="td_2"]').html();
		  controlCuota=controlCuota-deudad;
		$(elementohtml).children('td[id="td_9"]').html(0);
		 elementohtml.style='color: #03A9F4'
		 $(elementohtml).children('td[id="td_6"]').html('Pagago');
		 if(controlCuota<=0){
			 control="off" 
		 }
	  }else{
		  if(control=="on"){
			  control="off"
			  cuotasNro+=" "+$(elementohtml).children('td[id="td_2"]').html();
			  var diferencia=deudad-controlCuota;
			 // controlCuota=deudad-diferencia;
			  $(elementohtml).children('td[id="td_9"]').html(separadordemilesnumero(controlCuota));
			  
			  controlCuota=0;
		  }
	  }
	  	if(control=='off'){
			 diasatrazado=$(elementohtml).children('td[id="td_8"]').html();
		}
	  
	  }
            			 		
	   });
	 
	 var totalPagado=QuitarSeparadorMilValor(document.getElementById('inptTotalPagadoCredito').value);
	 totalPagado=Number(totalPagado)+Number(montoPagado);
	document.getElementById('inptTotalPagadoCredito').value=separadordemilesnumero(totalPagado);
	document.getElementById('inptDiasAtrazadoCargarPago').value=diasatrazado;
	deudaActualOffline=QuitarSeparadorMilValor(deudaActualOffline)-montoPagado;
	deudaActual=separadordemilesnumero(deudaActualOffline);
	$("tr[name=tb_offline_"+idVenta+"]").children('td[id="td_15"]').html(document.getElementById('inptTotalPagadoCredito').value)
	$("tr[name=tb_offline_"+idVenta+"]").children('td[id="td_8"]').html(document.getElementById('inptTotalPagadoCredito').value)
	$("tr[name=tb_offline_"+idVenta+"]").children('td[id="td_11"]').html(document.getElementById('inptDiasAtrazadoCargarPago').value)
	$("tr[name=tb_offline_"+idVenta+"]").children('td[id="td_14"]').html(deudaActual)


	ver_cerrar_vista_opciones_pago("2")
	   imprimirDiv(idVenta);
	   	var listaCuenta=document.getElementById("divCuentasBuscado").innerHTML
    localStorage.setItem("cuentasoffline", listaCuenta);
  // paginaticket se carga en forma predeterminada 
  
	
	
}
 function ver_cerrar_nuevo_offline(d){
	document.getElementById('divPrincipalCuentasoffline').style.display='none'
	if(d=="1"){
			
			document.getElementById('btnquitarPagooffline').style.display='none'
			document.getElementById('divPrincipalCuentasoffline').style.display=''
			var pagosoffline=localStorage.getItem("pagosoffline");
			document.getElementById('divCuentasASincronizar').innerHTML=pagosoffline
				
	
		
	}
}

var idventapagooffline=""
var elementoPagoOffline="";
function obtenerdatospagosoffline(elemento){
	
		elementoPagoOffline=elemento;
	
	    document.getElementById('btnGuardarPagoOnline').style.display=''

	
}
function quitarPagooffline(){
	var controlcarga=0;
		if (idventapagooffline == "") {
		
   ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
   return;
}
	$("tr[name=pagos_offline_"+idventapagooffline+"]").remove()
	$("tr[id=pagos_offline]").each(function(i, elementohtml){
	  controlcarga=controlcarga+1;
	   });
	   	if (controlcarga == 0) {
			 localStorage.setItem("pagosoffline", "");

}else{
	 localStorage.setItem("pagosoffline", document.getElementById('divCuentasASincronizar').innerHTML);
}
	 idventapagooffline="";
	 
	 document.getElementById('btnquitarPagooffline').style.display='none'
}

function guardarEstePagoOffline(){
	 var monto=$(elementoPagoOffline).children('td[id="td_1"]').html();
	  var fecha=$(elementoPagoOffline).children('td[id="td_2"]').html();
	  var idventa=$(elementoPagoOffline).children('td[id="td_3"]').html();
	  var nrorecibo=$(elementoPagoOffline).children('td[id="td_5"]').html();
	  abmcargarpagosoffline(idventa,monto,fecha,nrorecibo,1,1)
	   document.getElementById('btnGuardarPagoOnline').style.display='none'
}


/*MODO SIN CONEXION*/
 function vercerrarrmodosinconexion(d){
	document.getElementById('divPrincipalModoSinConexion').style.display='none'
	if(d=="1"){
		document.getElementById('divPrincipalModoSinConexion').style.display=''
		var pagosoffline=localStorage.getItem("pagosoffline");
		
			if(pagosoffline!=undefined && pagosoffline!=""){
		   document.getElementById('tdOfflineOption').style.display=''
			}else{
			document.getElementById('tdOfflineOption').style.display='none'
			}
	
	}
}
function salirDelModoSinConexion(){
	if(confirm("Seguro que quieres salir de este modo")){
	localStorage.setItem("pagosoffline", "");
	localStorage.setItem("cuentasoffline", "");
	vercerraropcionesinconexion("1")
	document.getElementById('divPrincipalModoSinConexion').style.display='none'
	}
}
function cargarListaCuentaOffline(){
	
	if (typeof(Storage) !== "undefined") {
		var listaCuenta=document.getElementById("divCuentasBuscadoSinConexion").innerHTML
		localStorage.setItem("cuentasoffline", listaCuenta);

    ver_vetana_informativa("SE HA ACTUALIZADO TU LISTADO OFFLINE")
} else {
   ver_vetana_informativa("OFFLINE NO DISPONIBLE PARA SU WEBVIEW")
}

}
function buscarcuentasparasinconexion(){

	
	
	
		var fecha1=document.getElementById('inptBuscarFechaSinConexion1').value
		var fecha2=document.getElementById('inptBuscarFechaSinConexion2').value
		var idzona=document.getElementById('inputBuscadorzonaSinConexion').value
		if(fecha1==""){
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO","alert")
			return
		}
		if(fecha2==""){
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN","alert")
			return
		}
		if(idzona==""){
			ver_vetana_informativa("FALTO SELECCIONAR UNA ZONA","alert")
			return
		}
		
		document.getElementById('divCuentasBuscadoSinConexion').innerHTML=imgCargandoA;
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"fecha1": fecha1,
			"fecha2": fecha2,
			"idzona": idzona,
			"funt": "buscarlistaasinconexion"
			
		
			};
	 $.ajax({
			
			data: datos,
		url: "/GoodVentaElim/php/abmcuentas.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	document.getElementById('divCuentasBuscado').innerHTML="";
			
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
	 document.getElementById('divCuentasBuscado').innerHTML="";
	 
			 	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				}  
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
					if (Respuesta=="exito")
			{
		
				
				var datosBuscado=datos["2"];
				var nroRegistro=datos["3"];
				if(datosBuscado==""){
		document.getElementById('lblRegistrosEncontradosCuentasSinConexion').innerHTML="No se encontraron registros";
					document.getElementById('divCuentasBuscadoSinConexion').innerHTML="";
					return;
				}
				
				document.getElementById('divCuentasBuscadoSinConexion').innerHTML=datosBuscado;
				document.getElementById('lblRegistrosEncontradosCuentasSinConexion').innerHTML="Se encontraron "+nroRegistro+" resgitro(s)";
cargarListaCuentaOffline()
					
						return false;
					}else{
						ver_vetana_informativa("LO SENTIMOS A OCURRIDO UN ERROR...","alert")
					} 
		
		 
			
			
             }catch(error){
					alert("Error Fatal: "+error)
				}
	  
			
			}
			
			});
	
	
}

function vercerraropcionesinconexion(d){
	document.getElementById('btnopcionsinconexion1').style="";
		document.getElementById('btnopcionsinconexion2').style="";
		document.getElementById('btnopcionsinconexion3').style="";
		document.getElementById('DivGenerarListaOffLine').style.display="none";
		document.getElementById('DivMiListaOffLine').style.display="none";
		document.getElementById('DivMiCobrosOffLine').style.display="none";
	
	if(d=="1"){
	
		document.getElementById('btnopcionsinconexion1').style="border-bottom: 1px solid #ccc;";
		document.getElementById('DivGenerarListaOffLine').style="";
		
	}
	if(d=="2"){
		document.getElementById('DivMiListaOffLine').style.display="";
		document.getElementById('btnopcionsinconexion2').style="border-bottom: 1px solid #ccc;";
			var cuentas=localStorage.getItem("cuentasoffline");
			if(cuentas!=undefined && cuentas!=""){
			document.getElementById('divCuentasBuscadoSinConexionListado').innerHTML=cuentas
			}else{
				ver_vetana_informativa("TU LISTA ESTA VACÍA","#")
				document.getElementById('divCuentasBuscadoSinConexionListado').innerHTML=""
				vercerraropcionesinconexion("1")
				return
			}
		document.getElementById('divCuentasBuscadoSinConexion').innerHTML=""
	}
	if(d=="3"){
		
		document.getElementById('DivMiCobrosOffLine').style.display="";
		document.getElementById('btnopcionsinconexion3').style="border-bottom: 1px solid #ccc;";
		var pagos=localStorage.getItem("pagosoffline");
			if(pagos!=undefined && pagos!=""){
			document.getElementById('divPagosBuscadoSinConexionListado').innerHTML=pagos
			}else{
				document.getElementById('divPagosBuscadoSinConexionListado').innerHTML=""
				ver_vetana_informativa("TU LISTA ESTA VACÍA","#")
				vercerraropcionesinconexion("2")
				return
			}
	}
	
}

function buscarClienteEnListado(d){
	
	var texto=document.getElementById("inptBuscarClienteSinConexion1").value
	 texto=texto.toLowerCase()
	if(texto!=""){
	$("table[name=tableCuentaOffline]").each(function (i, elemento) {
		elemento.style.display='none'
	})
		$("div[name=Cuentasoffline]").each(function (i, elemento) {
		var control=elemento.innerHTML.toLowerCase()
		control=control.indexOf(texto)
		
	 if(control>=0){
		var idVentas=elemento.id
		document.getElementById("tableCuentaOffline_"+idVentas).style.display="";
		 
	 }
    
	});
}else{
	$("table[name=tableCuentaOffline]").each(function (i, elemento) {
		elemento.style.display=''
	})
}
}
var PagadoOffilineVenta=""
var PendienteOffilineVenta=""
function obtenerdatoscuentasOfline(elemento){
	PagadoOffilineVenta=$(elemento).children('td[name="td_26"]').html();
	if(PagadoOffilineVenta!="0"){
		return;
	}
	idVenta=$(elemento).children('td[id="td_1"]').html();
	idClienteCuenta=$(elemento).children('td[id="td_2"]').html();
	document.getElementById('inptNroVentaCredito').value=$(elemento).children('td[id="td_17"]').html();
	document.getElementById('inptDocClienteCredito').value=$(elemento).children('td[id="td_4"]').html();
	document.getElementById('inptNombreClienteCredito').value=$(elemento).children('td[id="td_3"]').html();
	document.getElementById('inptReferenciaClienteCredito').value=$(elemento).children('td[id="td_3"]').html();
	document.getElementById('inptTotalVentaCredito').value=$(elemento).children('td[id="td_5"]').html();
	document.getElementById('inptTotalPagadoCredito').value=$(elemento).children('td[id="td_8"]').html();
	document.getElementById('inptTotalDescuentoCredito').value=$(elemento).children('td[id="td_7"]').html();
	document.getElementById('inptDiasAtrazadoCargarPago').value=$(elemento).children('td[id="td_11"]').html();
	document.getElementById('inptDireccionClienteCredito').value=$(elemento).children('td[id="td_19"]').html();
	document.getElementById('inptReferenciaClienteCredito').value=$(elemento).children('td[id="td_18"]').html();
	document.getElementById('inptTotalInteresCredito').value=$(elemento).children('td[id="td_20"]').html();
	document.getElementById('inptTotalInteresCreditoActual').value=$(elemento).children('td[id="td_27"]').html();
		document.getElementById('inptTotalPendienteSinInteresCredito').value=$(elemento).children('td[id="td_28"]').html();
	document.getElementById('inptTotalPendienteCredito').value=$(elemento).children('td[id="td_14"]').html();
	document.getElementById('inptTotalDeudaCredito').value=$(elemento).children('td[id="td_30"]').html();
    document.getElementById('inptTotalPagoOfflineCredito').value=PagadoOffilineVenta;
	document.getElementById('divDetalleCuenta').innerHTML=$(elemento).children('td[id="td_21"]').html();
	document.getElementById('divProductosComprados').innerHTML=$(elemento).children('td[id="td_24"]').html();
	document.getElementById('divPagosRealizados').innerHTML=$(elemento).children('td[id="td_23"]').html();
	document.getElementById('inptTelefClienteCredito').value=$(elemento).children('td[id="td_12"]').html();
	paginaticket=$(elemento).children('td[id="td_22"]').html();
      deudaActualOffline=$(elemento).children('td[id="td_14"]').html();
	  document.getElementById("btnCargarPagos1").style.display="none";
	document.getElementById("btnCargarPagos2").style.display="";
	document.getElementById("divTotalPagoOffline").style.display="";
	ver_cerrar_abm_creditos('1')

}


function obtenerdatoscuoterooffline(elemento){
	
	
	
	var estado=$(elemento).children('td[id="td_6"]').html();
	if(estado=="Pagado"){
	return
	}
	
	if(idcajaApp==""){	
		 ver_vetana_informativa("FALTO INICIALIZAR UNA CAJA")
		return
		}
		if(PendienteOffilineVenta>0){
			 ver_vetana_informativa("YA NO ES POSIBLE AÑADIR PAGOS","#")
			return
		}
	idCuenta=$(elemento).children('td[id="td_1"]').html();
	document.getElementById('inptReciboCobro').value="";
	document.getElementById('inptCuotaCobro').value=$(elemento).children('td[id="td_2"]').html();
	document.getElementById('inptTotalPagadoCobro').value=$(elemento).children('td[id="td_12"]').html();
	document.getElementById('inptTotalInteresCobro').value=$(elemento).children('td[id="td_11"]').html();
	document.getElementById('inptDeudaActualCobro').value=$(elemento).children('td[id="td_9"]').html();
	document.getElementById('inptMontoaPagar').value=$(elemento).children('td[id="td_9"]').html();
	document.getElementById('inptMontocrobrado').value=$(elemento).children('td[id="td_5"]').html();
	document.getElementById('inptDiasAtrazado').value=$(elemento).children('td[id="td_8"]').html();
	
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
    document.getElementById('inptFechaPago').value=f.getFullYear()+"-"+mes+"-"+dia;
	document.getElementById("btnPagosDeCuotas1").style.display="none";
	document.getElementById("btnPagosDeCuotas2").style.display="";
	
	ver_cerrar_abm_opciones_creditos("1");
	ver_cerrar_abm_creditos("1");
	
}

 function ver_cerrar_vista_opciones_pago_offline(d){
	document.getElementById('divOpcionesPagos').style.display='none'
	
	if(d=="1"){
		if(idcajaApp==""){	
		 ver_vetana_informativa("FALTO INICIALIZAR UNA CAJA")
		return
		}
		
		if(PagadoOffilineVenta>0){
			 ver_vetana_informativa("YA NO ES POSIBLE AÑADIR PAGOS","#")
			return
		}
			document.getElementById('divOpcionesPagos').style.display=''
			document.getElementById('btnCargarPagosParcial1').style.display='none'
			document.getElementById('btnCargarPagosParcial2').style.display=''
			document.getElementById('inptMontoaCobrarPago').value=document.getElementById('inptTotalPendienteCredito').value
						

		
	}
}

function cargarPagoSinConexion1(){
	if(document.getElementById("inptMontoaPagar").value=="" || document.getElementById("inptMontoaPagar").value==0){
		return
	}
	var pagosoffline=localStorage.getItem("pagosoffline");
if (pagosoffline == "undefined" || pagosoffline == "" || pagosoffline == "Null" || pagosoffline == null ) {
		
   pagosoffline="";
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
	var nroRecibo= Math.round(Math.random()*1000);
    var fecha =f.getFullYear()+"-"+mes+"-"+dia;
    var pagado=document.getElementById("inptMontoaPagar").value
    var Totalpagado=document.getElementById("inptTotalPagadoCredito").value
    var cliente=document.getElementById("inptNombreClienteCredito").value
	var factura=document.getElementById("inptNroVentaCredito").value
	var diaatrazado = document.getElementById("inptDiasAtrazado").value
	var totalInteresActual = document.getElementById("inptTotalInteresCobro").value
	var cuotasNro = document.getElementById("inptCuotaCobro").value
    var pa=QuitarSeparadorMilValor(pagado);
    var topa=QuitarSeparadorMilValor(Totalpagado);
	var pe=QuitarSeparadorMilValor(document.getElementById("inptTotalDeudaCredito").value);
	var deudaActual=Number(pe)-(Number(pa))
	if(deudaActual<0){
		deudaActual=0;
	}
	  var totalInteresesPagado=QuitarSeparadorMilValor(document.getElementById("inptTotalInteresCredito").value);
	   var totalInteresesActual=QuitarSeparadorMilValor(document.getElementById("inptTotalInteresCreditoActual").value);
	   if(totalInteresesActual>pa){
		   var totalIntereses=(Number(totalInteresesPagado)+Number(totalInteresesActual)).toFixed(0);
	   }else{
		   var totalIntereses=(Number(totalInteresesPagado)+Number(pa)).toFixed(0);
	   }
	var paginalistapagosoffline="<table id='tb_pagos_offline_"+idVenta+"'  class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>"
   +"<tr id='pagos_offline' name='pagos_offline_"+idVenta+"'  onclick='reimprimirticketOffline(this)'  >"
   +"<td id='td_1' style='width:33%;text-Align:left' >"+cliente+"</td>"
   +"<td id='td_8' style='width:33%' >"+pagado+"</td>"
	+"<td id='td_17' style='width:33%' >"+fecha+" "+hora+":"+min+"</td>"
	+"<td id='td_2' style='display:none' >"+fecha+"</td>"
	+"<td id='td_3' style='display:none' >"+idVenta+"</td>"
	+"<td id='td_4' style='display:none' >"+hora+":"+min+"</td>"
	+"<td id='td_5' style='display:none' >cargarpago</td>"
	+"<td id='td_6' style='display:none' >"+nroRecibo+"</td>"
	+"<td id='td_7' style='display:none' >"+factura+"</td>"
	+"<td id='td_9' style='display:none' >"+diaatrazado+"</td>"
	+"<td id='td_10' style='display:none' >"+separadordemilesnumero(totalIntereses)+"</td>"
	+"<td id='td_11' style='display:none' >"+document.getElementById("inptTotalDescuentoCredito").value+"</td>"
	+"<td id='td_12' style='display:none' >"+separadordemilesnumero(deudaActual)+"</td>"
	+"<td id='td_13' style='display:none' name='td_cuotaAtrazada_"+idVenta+"' >"+cuotasNro+"</td>"
	 +"<td id='td_14' style='display:none' >"+Totalpagado+"</td>"
	+"<td id='td_15' style='display:none' >"+document.getElementById("inptNombreClienteCredito").value+"</td>"
    +"<td id='td_16' style='display:none' >"+document.getElementById("inptDocClienteCredito").value+"</td>"
    +"<td id='td_18' style='display:none' >"+document.getElementById("inptTotalVentaCredito").value+"</td>"
	+"<td id='td_19' style='display:none' >"+paginaticket+"</td>"
	+"<td id='td_20' style='display:none' >"+totalInteresActual+"</td>"
	+"</tr>"
    +"</table>"
  localStorage.setItem("pagosoffline", paginalistapagosoffline+pagosoffline);
  document.getElementById("tableCuentaOffline2_"+idVenta).style='background-color:#ccc;color:#000'
  document.getElementById("tb_pagado_offline_"+idVenta).innerHTML=pagado
  document.getElementById("tb_pagado_offline2_"+idVenta).innerHTML=pagado+" Gs."
  document.getElementById("tb_pagado_offline3_"+idVenta).innerHTML=pagado
   document.getElementById("tableCuentaOffline3_"+idVenta).style.display="";
  var listaCuenta=document.getElementById("divCuentasBuscadoSinConexionListado").innerHTML
  localStorage.setItem("cuentasoffline", listaCuenta);


	   var tp=QuitarSeparadorMilValor(document.getElementById("inptTotalPagadoCredito").value);
	   var totalPagado=Number(tp)+Number(pa)
	
		   document.getElementById('tdOfflineOption').style.display=''

  ver_vetana_informativa("PAGOS GUARDADOS EN MODO OFF-LINE","#")
  cargarPagoAuditoria(pagado)
  ver_cerrar_vista_opciones_pago("2")
ver_cerrar_abm_creditos("2")
  ver_cerrar_abm_opciones_creditos("2")
  	imprimirDivVentaOffline(diaatrazado,pagado,cuotasNro,separadordemilesnumero(deudaActual),nroRecibo,separadordemilesnumero(totalPagado),separadordemilesnumero(totalIntereses))

}


function cargarPagoSinConexion2(){
	if(document.getElementById("inptMontoaCobrarPago").value=="" || document.getElementById("inptMontoaCobrarPago").value==0){
		return
	}
	var pagosoffline=localStorage.getItem("pagosoffline");
if (pagosoffline == "undefined" || pagosoffline == "" || pagosoffline == "Null" || pagosoffline == null ) {
		
   pagosoffline="";
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
     var fecha =f.getFullYear()+"-"+mes+"-"+dia;
	 var nroRecibo= Math.round(Math.random()*1000);
    var pagado=document.getElementById("inptMontoaCobrarPago").value
    var cliente=document.getElementById("inptNombreClienteCredito").value
    var factura=document.getElementById("inptNroVentaCredito").value
	 var diaatrazado = document.getElementById("inptDiasAtrazadoCargarPago").value
	  var pa=QuitarSeparadorMilValor(pagado);
	   var pe=QuitarSeparadorMilValor(document.getElementById("inptTotalDeudaCredito").value);
	   var tp=QuitarSeparadorMilValor(document.getElementById("inptTotalPagadoCredito").value);
	   var totalInteresesPagado=QuitarSeparadorMilValor(document.getElementById("inptTotalInteresCredito").value);
	   var totalInteresesActual=QuitarSeparadorMilValor(document.getElementById("inptTotalInteresCreditoActual").value);
	     if(totalInteresesActual>pa){
		   var totalIntereses=(Number(totalInteresesPagado)+Number(totalInteresesActual)).toFixed(0);
	   }else{
		   var totalIntereses=(Number(totalInteresesPagado)+Number(pa)).toFixed(0);
	   }
	   
	   var totalPagado=Number(tp)+Number(pa)
	 var deudaActual=Number(pe)-Number(pa)
	 
	var paginalistapagosoffline="<table id='tb_pagos_offline_"+idVenta+"'  class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>"
   +"<tr id='pagos_offline' name='pagos_offline_"+idVenta+"' onclick='reimprimirticketOffline(this)'   >"
   +"<td id='td_1' style='width:33%;text-Align:left' >"+cliente+"</td>"
   +"<td id='td_8' style='width:33%' >"+pagado+"</td>"
   +"<td id='td_17'  style='width:33%' >"+fecha+" "+hora+":"+min+"</td>"
   +"<td id='td_2' style='display:none' >"+fecha+"</td>"
   +"<td id='td_3' style='display:none' >"+idVenta+"</td>"
   +"<td id='td_4' style='display:none' >"+hora+":"+min+"</td>"
   +"<td id='td_5' style='display:none' >cargarpago</td>"
   +"<td id='td_6' style='display:none' >"+nroRecibo+"</td>"
   +"<td id='td_7' style='display:none' >"+factura+"</td>"
   +"<td id='td_9' style='display:none' >"+diaatrazado+"</td>"
   +"<td id='td_10' style='display:none' >"+separadordemilesnumero(totalIntereses)+"</td>"
   +"<td id='td_11' style='display:none' >"+document.getElementById("inptTotalDescuentoCredito").value+"</td>"
   +"<td id='td_12' style='display:none' >"+separadordemilesnumero(deudaActual)+"</td>"
   +"<td id='td_13' style='display:none' name='td_cuotaAtrazada_"+idVenta+"' ></td>"
   +"<td id='td_14' style='display:none' >"+separadordemilesnumero(totalPagado)+"</td>"
   +"<td id='td_15' style='display:none' >"+document.getElementById("inptNombreClienteCredito").value+"</td>"
   +"<td id='td_16' style='display:none' >"+document.getElementById("inptDocClienteCredito").value+"</td>"
   +"<td id='td_18' style='display:none' >"+document.getElementById("inptTotalVentaCredito").value+"</td>"
   +"<td id='td_19' style='display:none' >"+paginaticket+"</td>"
   +"</tr>"
   +"</table>"
  localStorage.setItem("pagosoffline", paginalistapagosoffline+pagosoffline);
    
	 document.getElementById("tableCuentaOffline2_"+idVenta).style='background-color:#ccc;color:#000'
	  document.getElementById("tb_pagado_offline_"+idVenta).innerHTML=pagado
	  document.getElementById("tb_pagado_offline2_"+idVenta).innerHTML=pagado+" Gs."
	  document.getElementById("tb_pagado_offline3_"+idVenta).innerHTML=pagado
	  document.getElementById("tableCuentaOffline3_"+idVenta).style.display="";
  var listaCuenta=document.getElementById("divCuentasBuscadoSinConexionListado").innerHTML
  localStorage.setItem("cuentasoffline", listaCuenta);
   
   
	var controlCuota=QuitarSeparadorMilValor(pagado);
	   var control='on'
	   var cuotasNro=''
   $("tr[name=pagoscuenta_"+idVenta+"]").each(function(i, elementohtml){
	  
	  var estado=$(elementohtml).children('td[id="td_6"]').html();
	    control="on"
	  if(estado=="Pendiente" && controlCuota>1){
		   var deudad=$(elementohtml).children('td[id="td_9"]').html();
		   	  deudad=QuitarSeparadorMilValor(deudad)
		 
		

	  if(controlCuota>=deudad){
		  if(cuotasNro==""){
		  cuotasNro+=$(elementohtml).children('td[id="td_2"]').html();
		  }else{
			  cuotasNro+=", "+$(elementohtml).children('td[id="td_2"]').html();
		  }
		  controlCuota=controlCuota-deudad;

		 if(controlCuota<=0){
			 control="off" 
		 }
	  }else{
		  
		  if(control=="on"){
			  control="off"
			  var c=deudad-controlCuota
			  var titulo=""
			  if(c>0){
				 titulo="Pago parcial en cuota " 
			  }
			if(cuotasNro==""){
		 cuotasNro+=titulo+$(elementohtml).children('td[id="td_2"]').html();
		  }else{
			  cuotasNro+=", "+titulo+$(elementohtml).children('td[id="td_2"]').html();
		  }
			  
			  var diferencia=deudad-controlCuota;
			  controlCuota=0;
		  }
	  }
	  	
	  
	  }
            			 		
	   });
	  
	 		   document.getElementById('tdOfflineOption').style.display=''
			    
  $("td[name=td_cuotaAtrazada_"+idVenta+"]").text(cuotasNro)
cargarPagoAuditoria(pagado)
ver_vetana_informativa("PAGOS GUARDADOS EN MODO OFF-LINE","#")
ver_cerrar_vista_opciones_pago("2")
ver_cerrar_abm_creditos("2")
imprimirDivVentaOffline(diaatrazado,pagado,cuotasNro,separadordemilesnumero(deudaActual),nroRecibo,separadordemilesnumero(totalPagado),separadordemilesnumero(totalIntereses))


}
var elementoReimprimirticketOfline="";
function reimprimirticketOffline(datos){
	elementoReimprimirticketOfline=datos;
	verCerrarReimpresioncuentascobradas("1")
}
function verCerrarReimpresioncuentascobradas(d){
	if(d=="1"){
		document.getElementById("divReeimprimirCobrados").style.display=""
	}else{
		document.getElementById("divReeimprimirCobrados").style.display="none"
	}
}
function obtenerDatosReImpresionOffline(){
	if(elementoReimprimirticketOfline==""){
			return
	}
		datostr=elementoReimprimirticketOfline
		var nombreCliente=$(datostr).children('td[id="td_15"]').html();
		var Documento=$(datostr).children('td[id="td_16"]').html();
		var totalDescuento=$(datostr).children('td[id="td_11"]').html();
		var totalInteres=$(datostr).children('td[id="td_10"]').html();
		var deudaActual=$(datostr).children('td[id="td_12"]').html();
		var fechaPago=$(datostr).children('td[id="td_17"]').html();
		var diaatrazado=$(datostr).children('td[id="td_9"]').html();
		var pagado=$(datostr).children('td[id="td_8"]').html();
		var cuotasNro=$(datostr).children('td[id="td_13"]').html();
		var nroRecibo=$(datostr).children('td[id="td_6"]').html();
		var totalPagado=$(datostr).children('td[id="td_14"]').html();
		var Subtotal=$(datostr).children('td[id="td_18"]').html();
		paginaticket=$(datostr).children('td[id="td_19"]').html();
		ReimprimirDivVentaOffline(Subtotal,totalDescuento,totalInteres,nombreCliente,Documento,fechaPago,diaatrazado,pagado,cuotasNro,deudaActual,nroRecibo,totalPagado)
	
}

function ReimprimirDivVentaOffline(Subtotal,totalDescuento,totalInteres,nombreCliente,Documento,fechaPago,diaatrazado,pagado,cuotasNro,deudaActual,nroRecibo,totalPagado)
{

pagina="<div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >FLEYKOOP</p>"
+"<p class='pTituloTicket2'>"
+"RUC 4554943-5"
+"<br>Villarrica, py "
+"</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Recibo de Dinero</b></p>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Numero :</b></td>"
+"<td style=''>"+nroRecibo+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+nombreCliente+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>RUC o C.I.:</b></td>"
+"<td style=''>"+Documento+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+fechaPago+"</td>"
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
+"<td style='width:100px'><b>Pagado</b></td>"
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
+"<td style='width:110px'><b>Subtotal:</b></td>"
+"<td style=''>"+Subtotal+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Interes Pagado:</b></td>"
+"<td style=''>"+totalInteres+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Descuento:</b></td>"
+"<td style=''>"+totalDescuento+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Pagado:</b></td>"
+"<td style=''>"+totalPagado+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Deuda:</b></td>"
+"<td style=''>"+deudaActual+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:85px'><b>Cobrador/a:</b></td>"
+"<td style=''>"+document.getElementById("lbluser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center></div>"


var ficha=pagina;
 /*
  var ventimp=window.open();
   ventimp.document.write(ficha);;
  ventimp.print();
  ventimp.close();
  
     document.open();
      document.write(ficha.innerHTML);
	  document.print();
      document.close();*/
 

	
	controlImpresion="on"
	document.getElementById("divimpr").innerHTML=pagina
	 
	    var contenido= document.getElementById("divimpr").innerHTML;
	  contenido=b64EncodeUnicode(contenido)
	   urlRepor="/GoodVentaElim/app/reporte.html?ticket="+contenido;
	
     activarAndroid("report")
      // contenidoOriginal= document.body.innerHTML;

     // document.body.innerHTML = contenido;

     // window.print();
 document.getElementById("divimpr").innerHTML="";
     
}

function imprimirDivVentaOffline(diaatrazado,pagado,cuotasNro,deudaActual,nroRecibo,totalPagado,totalIntereses){
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
    document.getElementById('inptFechaPago').value=f.getFullYear()+"-"+mes+"-"+dia;
	

	
pagina="<div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >FLEYKOOP</p>"
+"<p class='pTituloTicket2'>"
+"RUC 4554943-5"
+"<br>Villarrica, py "
+"</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Recibo de Dinero</b></p>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:80px'><b>Numero :</b></td>"
+"<td style=''>"+nroRecibo+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Cliente:</b></td>"
+"<td style=''>"+document.getElementById("inptNombreClienteCredito").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>RUC o C.I.:</b></td>"
+"<td style=''>"+document.getElementById("inptDocClienteCredito").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaPago").value+" "+hora+":"+min+"</td>"
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
+"<td style='width:100px'><b>Pagado</b></td>"
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
+"<td style='width:110px'><b>Subtotal:</b></td>"
+"<td style=''>"+document.getElementById("inptTotalVentaCredito").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Interes Pagado:</b></td>"
+"<td style=''>"+totalIntereses+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Descuento:</b></td>"
+"<td style=''>"+document.getElementById("inptTotalDescuentoCredito").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Pagado:</b></td>"
+"<td style=''>"+totalPagado+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total Deuda:</b></td>"
+"<td style=''>"+deudaActual+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:85px'><b>Cobrador/a:</b></td>"
+"<td style=''>"+document.getElementById("lbluser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center></div>"


var ficha=pagina;
 /*
  var ventimp=window.open();
   ventimp.document.write(ficha);;
  ventimp.print();
  ventimp.close();
  
     document.open();
      document.write(ficha.innerHTML);
	  document.print();
      document.close();*/
 

	
	controlImpresion="on"
	document.getElementById("divimpr").innerHTML=pagina
	 
	    var contenido= document.getElementById("divimpr").innerHTML;
	  contenido=b64EncodeUnicode(contenido)
	   urlRepor="/GoodVentaElim/app/reporte.html?ticket="+contenido;
	
     activarAndroid("report")
      // contenidoOriginal= document.body.innerHTML;

     // document.body.innerHTML = contenido;

     // window.print();
 document.getElementById("divimpr").innerHTML="";
     
}

function imprimirListaOffline(){
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
	var totalCobrado=0;
	var totalACobrar=0;
	var nroDeRegistro=0;
	var nroSincronizado=0;
    document.getElementById('inptFechaPago').value=f.getFullYear()+"-"+mes+"-"+dia;
	 var paginadetalles="";
	 $("tr[id=pagos_offline]").each(function(i, elementohtml){
		 
	
		 
		 var codVenta= $(elementohtml).children('td[id="td_3"]').html();
		  var factura= $(elementohtml).children('td[id="td_7"]').html();
		  var Cliente= $(elementohtml).children('td[id="td_1"]').html();
		  var monto= $(elementohtml).children('td[id="td_8"]').html();
		  var fecha= $(elementohtml).children('td[id="td_2"]').html();
		  var recibo= $(elementohtml).children('td[id="td_6"]').html();
		 totalACobrar=Number(totalACobrar)+Number(QuitarSeparadorMilValor(monto))
		 nroDeRegistro=Number(nroDeRegistro)+1
		  if(document.getElementById("tb_pagos_offline_"+codVenta).style.backgroundColor=="rgb(63, 81, 181)"){
		 document.getElementById("tb_pagos_offline_"+codVenta).style='background-color:#3F51B5;color:#fff'
		
	
	 paginadetalles+="<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100%'>"+Cliente+"</td>"
+"</tr>"
+"</tr>"
+"</table>"
+"<tr>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:15%'>"+recibo+"</td>"
+"<td style='width:33%'>"+monto+"</td>"
+"<td style='width:40%'>"+fecha+"</td>"
+"</tr>"
+"</table>";
  totalCobrado=Number(totalCobrado)+Number(QuitarSeparadorMilValor(monto))
  nroSincronizado=Number(nroSincronizado)+1
 
	 }
	 
	 })

	
pagina="<div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >FLEYKOOP</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Boleta de control:</b></p>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Impreso por :</b></td>"
+"<td style=''>"+document.getElementById("lbluser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaPago").value+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Balance:</b></p>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Registros:</b></td>"
+"<td style=''>"+separadordemilesnumero(nroDeRegistro)+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Sincronizado:</b></td>"
+"<td style=''>"+separadordemilesnumero(nroSincronizado)+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Total:</b></td>"
+"<td style=''>"+separadordemilesnumero(totalACobrar)+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Total Enviado:</b></td>"
+"<td style=''>"+separadordemilesnumero(totalCobrado)+" Gs.</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center></div>"


var ficha=pagina;
 /*
  var ventimp=window.open();
   ventimp.document.write(ficha);;
  ventimp.print();
  ventimp.close();
  
     document.open();
      document.write(ficha.innerHTML);
	  document.print();
      document.close();*/
 
	
	controlImpresion="on"
	document.getElementById("divimpr").innerHTML=pagina
	 
	    var contenido= document.getElementById("divimpr").innerHTML;
	  contenido=b64EncodeUnicode(contenido)
	   urlRepor="/GoodVentaElim/app/reporte.html?ticket="+contenido;
	
     activarAndroid("report")
      // contenidoOriginal= document.body.innerHTML;

     // document.body.innerHTML = contenido;

     // window.print();
 document.getElementById("divimpr").innerHTML="";
     
}

function imprimirListadePagod(){
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
	var totalCobrado=0;
	var totalACobrar=0;
	var nroDeRegistro=0;
	var nroSincronizado=0;
    document.getElementById('inptFechaPago').value=f.getFullYear()+"-"+mes+"-"+dia;
	 var paginadetalles="";
	 $("tr[name=tdRecaudacionCargada]").each(function(i, elementohtml){
		 
	
		 
		 var Cliente= $(elementohtml).children('td[id="td_1"]').html();
		  var factura= $(elementohtml).children('td[id="td_2"]').html();
		  var fecha= $(elementohtml).children('td[id="td_3"]').html();
		  var monto= $(elementohtml).children('td[id="td_4"]').html();
		 nroDeRegistro=Number(nroDeRegistro)+1
				
	
	 paginadetalles+="<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100%'>"+Cliente+"</td>"
+"</tr>"
+"</tr>"
+"</table>"
+"<tr>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:15%'>"+factura+"</td>"
+"<td style='width:33%'>"+monto+"</td>"
+"<td style='width:40%'>"+fecha+"</td>"
+"</tr>"
+"</table>";
  totalCobrado=Number(totalCobrado)+Number(QuitarSeparadorMilValor(monto))
 
	 
	 })

	
pagina="<div   style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >FLEYKOOP</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Boleta de control:</b></p>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Impreso por :</b></td>"
+"<td style=''>"+document.getElementById("lbluser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Fecha:</b></td>"
+"<td style=''>"+document.getElementById("inptFechaPago").value+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<p class='pTituloTicket1' style='font-size:12px;' ><b>Balance:</b></p>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Total Cobrado:</b></td>"
+"<td style=''>"+separadordemilesnumero(totalCobrado)+" Gs.</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center></div>"


var ficha=pagina;
 /*
  var ventimp=window.open();
   ventimp.document.write(ficha);;
  ventimp.print();
  ventimp.close();
  
     document.open();
      document.write(ficha.innerHTML);
	  document.print();
      document.close();*/
 
	
	controlImpresion="on"
	document.getElementById("divimpr").innerHTML=pagina
	 
	    var contenido= document.getElementById("divimpr").innerHTML;
	  contenido=b64EncodeUnicode(contenido)
	   urlRepor="/GoodVentaElim/app/reporte.html?ticket="+contenido;
	
     activarAndroid("report")
      // contenidoOriginal= document.body.innerHTML;

     // document.body.innerHTML = contenido;

     // window.print();
 document.getElementById("divimpr").innerHTML="";
     
}


var datosPagosOffline=new Array()
var totalRegistroPagosOffline;
var RegistroCargadoMasivo=0;
var controlSincronizacion=true
function enviarpagosonline(){
	if(idcajaApp==""){
		ver_vetana_informativa("FALTO INICIALIZAR UN CAJA","abmpagos")
		return
	}
	if(controlSincronizacion==true){
	 controlSincronizacion=false
	 totalRegistroPagosOffline=0
	 RegistroCargadoMasivo=0
	 $("tr[id=pagos_offline]").each(function(i, elementohtml){
	
		 var codVenta= $(elementohtml).children('td[id="td_3"]').html();
		 datosPagosOffline.push(codVenta)
	  totalRegistroPagosOffline=totalRegistroPagosOffline+1;
	   });
	   	if (totalRegistroPagosOffline == 0) {
		
   ver_vetana_informativa("NO SE ENCONTRARO REGISTROS")
   return;
}
	
	procesarpagosoffline()
	}else{
		 ver_vetana_informativa("NO SE PUEDE REALIZAR ESTA ACCIÓN")
	}
}
function procesarpagosoffline(){
	
	
	 var pagina=""
     if(RegistroCargadoMasivo < totalRegistroPagosOffline){
		 
		 var CodVenta=datosPagosOffline[RegistroCargadoMasivo]
	
		 $("tr[name=pagos_offline_"+CodVenta+"]").each(function(i, elementohtml){
	
		 var pago= $(elementohtml).children('td[id="td_8"]').html();
		 var codVenta= $(elementohtml).children('td[id="td_3"]').html();
		 var fecha= $(elementohtml).children('td[id="td_2"]').html();
		 var accion= $(elementohtml).children('td[id="td_5"]').html();
		 var nrorecibo= $(elementohtml).children('td[id="td_6"]').html();
		  RegistroCargadoMasivo=RegistroCargadoMasivo+1;
		 if(document.getElementById("tb_pagos_offline_"+codVenta).style.backgroundColor!="rgb(63, 81, 181)"){
		 document.getElementById("tb_pagos_offline_"+codVenta).style='background-color:#3F51B5;color:#fff'
		 abmcargarpagosoffline(idcajaApp,codVenta,pago,fecha,nrorecibo,accion)
		 }else{
			 procesarpagosoffline()
		 }
	 
	   });
		  
	 }	else{
		 controlSincronizacion=true
       var listapagos=document.getElementById("divPagosBuscadoSinConexionListado").innerHTML
        localStorage.setItem("pagosoffline", listapagos);
		
		 ver_vetana_informativa("DATOS SINCRONIZADO CORRECTAMENTE")
	 } 
	 
	 
	 }
	 
	 
	 function abmcargarpagosoffline(codAperturaApp,cod_venta,Monto,fecha,nrorecibo,accion){
	
	

	obtener_datos_user()
	  var datos = new FormData();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("cod_venta" , cod_venta)
			  datos.append("Monto" , Monto)
			  datos.append("fecha" , fecha)
			 datos.append("lot" , logPago)
			  datos.append("lat" , latPago)
			  datos.append("nrorecibo" , nrorecibo)
			  datos.append("codAperturaApp" , codAperturaApp)
			  datos.append("totalDeudaCuota" , 0)
			
					
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmpagos.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					
					 document.getElementById("tb_pagos_offline_"+cod_venta).style='background-color:red;color:#fff'
procesarpagosoffline()
					 return false;
			},
			success: function(responseText)
			{
			  
			Respuesta=responseText;			
				console.log(Respuesta)
	
	
	try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		
		  if (Respuesta=="usuarioincorrecto")
			{
				window.location="/GoodVentaElim/login.html";
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...","alert")
						return false;
				} 
			if (Respuesta=="bajonivel")
			{
		
				ver_vetana_informativa("NO PUEDES REALIZAR ESTA ACCIÓN...","alert")
						return false;
					} 
			
			if (Respuesta=="camposvacio")
			{
		
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...","alert")
						return false;
				}
			if (Respuesta=="duplicado")
			{
		ver_vetana_informativa("YA EXISTE UN REGISTRO SIMILAR...","alert")
						return false;
			}
		
		if (Respuesta=="exito")
			{					 
			//	$("table[id=tb_pagos_offline_"+cod_venta+"]").remove()
	
	procesarpagosoffline()

			}
			else
			{
			
					 
					 document.getElementById("tb_pagos_offline_"+cod_venta).style='background-color:red;color:#fff'
procesarpagosoffline()
			}
			
		
          }catch(error){
			  	 document.getElementById("tb_pagos_offline_"+cod_venta).style='background-color:red;color:#fff'
		procesarpagosoffline()
				
					
				}
	
	
	
		
		 
			
			
		 
					
			}
			});
				
	
}



 function vercerrarrecalendarizar(d){
	document.getElementById('divReCalendarizar').style.display='none'
	if(d=="1"){
		document.getElementById('divReCalendarizar').style.display=''
	 buscarabmvisitas()
		
	}
}
function verificarcamposvisita(){
	
	var inptFechaDevisitas=document.getElementById('inptFechaDevisitas').value
	
	

  if(inptFechaDevisitas==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN FECHA","#")
	  return false;
  }
 
 
  abmvisita(inptFechaDevisitas,"nuevo");
}
function  abmvisita(fecha,accion){
	
	
	verCerrarVentanaCargando("1")
	  var datos = new FormData();
			obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", accion)
			 datos.append("fecha" , fecha)
			  datos.append("cod_cobrador" , userid)
			 datos.append("fecha" , fecha)
			 datos.append("cod_venta" , idVenta)
			
		
		
			
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmvisitas.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
						verCerrarVentanaCargando("")
					 ver_vetana_informativa("ERROR DE CONECCIÓN")

					 return false;
			},
			success: function(responseText)
			{
			  	 verCerrarVentanaCargando("")
			Respuesta=responseText;			
				console.log(Respuesta)
		try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		 

		 if (Respuesta=="UI")
			{
		
			
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			
				ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - ABM CLIENTE")
						return false;
					


			} 
		 if (Respuesta=="camposvacio")
			{
		
			
				ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
						return false;
					


			}
			if (Respuesta=="EX")
			{
		
			
				ver_vetana_informativa("YA EXISTE UNA CLIENTE SIMILAR...")
						return false;
					


			}
			if (Respuesta=="exito")
			{
		
				 document.getElementById('inptFechaDevisitas').value=""
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
				buscarabmvisitas()
					


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
function buscarabmvisitas(){
 
 

	
		 document.getElementById("divVisitasBuscado").innerHTML=imgCargandoA
		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idVenta,
			"funt": "buscar"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/abmvisitas.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	
			document.getElementById("divVisitasBuscado").innerHTML=''
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("divVisitasBuscado").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
		 if (Respuesta=="UI")
			{
		
			
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			
					ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - BUSCAR CLIENTE")
						return false;
					


			} 
			if (Respuesta == "exito")
			{
				
				
				
		  var datos_buscados=datos[2];
		 
			document.getElementById("divVisitasBuscado").innerHTML=datos_buscados
			document.getElementById("lblRegistrosEncontradosVisita").innerHTML="Registros encontrados "+datos[3];
		

	  
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}
function IrALlamadas(){
	activarAndroid("llamar")
}
var coordenasmap="";
var urlRepor="";
function activarAndroid(accion){
	
	var texto="";
	
	if(accion=="maps"){		 
    
	try {
		
    texto=Android.abrirgooglemap(coordenasmap)
    }catch(error){
  window.open("https://www.google.com.py/maps/place/"+coordenasmap);
	}
	
	}
	
	
	
		
	if(accion=="report"){		 
    
	
	try {
	texto=Android.abrirChrome(urlRepor)
    }catch(error){
	window.open(urlRepor)  
	}
	
	// try {		
    // texto=Android.OnPrint(urlRepor)   
    // }catch(error){		
	   
	// }
	
	}
	
	//Por error de cargar ese al
	if(accion=="longitud"){		 
    
	try {
    texto=Android.obtener_longitud()
    }catch(error){
    texto="";
	}
	
	}
	
	if(accion=="latitud"){		 
    
	try {
    texto=Android.obtener_latitud()
    }catch(error){
    texto="";
	}
	
	}
	
	if(accion=="llamar"){		 
    
	try {
		var nro=document.getElementById("inptTelefClienteCredito").value
    texto=Android.Abrirllamadas(nro)
    }catch(error){
		ver_vetana_informativa(erro)
    texto="";
	}
	
	}
	
	
	
	return texto;
	
}


function cargarPagoAuditoria(pago){
  try {
   
	var auditoriapago=localStorage.getItem("auditoriapago");
if (auditoriapago == "undefined" || auditoriapago == "" || auditoriapago == "Null" || auditoriapago == null ) {
		
   auditoriapago="";
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
	
    var fecha =f.getFullYear()+"-"+mes+"-"+dia;
  
    var cliente=document.getElementById("inptNombreClienteCredito").value
	
   
	var paginaauditoriapago="<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>"
   +"<tr >"
   +"<td id='td_1' style='width:33%;text-Align:left' >"+cliente+"</td>"
   +"<td id='td_8' style='width:33%' >"+pago+"</td>"
	+"<td id='td_17' style='width:33%' >"+fecha+" "+hora+":"+min+"</td>"
	+"</tr>"
    +"</table>"
	
  localStorage.setItem("auditoriapago", "");


   }catch(error){
    
	}
}



	