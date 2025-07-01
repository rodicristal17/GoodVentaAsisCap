

var controlMenuWd=1;
function MeniWindows(){
	if(controlMenuWd==0){
		document.getElementById("divMenuWindowsB").style.display="none";
	document.getElementById("divCerrarSesion").style.display="none";
		document.getElementById("divAcercade").style.display="none";
		document.getElementById("divComodin1").style.display="none";
		document.getElementById("divComodin2").style.display="none";
		 $("div[id=divMenuWindowsB]").show(300);				
		 $("div[id=divMantenimiento]").fadeIn(500);
		 $("div[id=divAdministrativo]").fadeIn(500);
		 $("div[id=divConsultar]").fadeIn(500);
		 $("div[id=divReportes]").fadeIn(500);
		document.getElementById("lblTituloInicio").innerHTML="Inicio";
		 controlMenuWd=1
		 }else{
		document.getElementById("divMantenimiento").style.display="none";
		document.getElementById("divAdministrativo").style.display="none";
		document.getElementById("divConsultar").style.display="none";
		document.getElementById("divReportes").style.display="none";
		document.getElementById("divMenuWindowsB").style.display="none";
		$("div[id=divMenuWindowsB]").show(300);	
		$("div[id=divComodin1]").fadeIn(500);		 
		 $("div[id=divCerrarSesion]").fadeIn(500);
		 $("div[id=divAcercade]").fadeIn(500);		
		 $("div[id=divComodin2]").fadeIn(500);		
		controlMenuWd=0
		document.getElementById("lblTituloInicio").innerHTML="Menú";
	}
}
function ocultarMenusWindows(elemento){
document.getElementById(elemento).style.fontSize="";
}
function verMenusWindows(elemento){
document.getElementById(elemento).style.fontSize="18px";
}

function vaciar(txt){
	document.getElementById(txt).value="";
}

function verCerrarMenub(d){
	if(d=="0"){
		document.getElementById("divEfectoMantenimiento").className="magictime slideDown"
		document.getElementById("divMenuAcercade").className="magictime slideDown"
		
	 $("div[id=principalMenub]").fadeOut(500);
$("div[id=divMenuMantenimiento]").animate({ scrollTop:(0) },500);	 
	}


	if(d=="1"){
		document.getElementById("divMenuAcercade").style.display="none"
		document.getElementById("divMenuMantenimiento").style.display=""
		document.getElementById("principalMenub").style.display=""
		document.getElementById("divEfectoMantenimiento").className="magictime slideDownReturn"	
	

	//	$("div[id=divMenuMantenimiento]").fadeIn(500);	
		
	}
	if(d=="2"){
		 document.getElementById("divMenuMantenimiento").style.display="none"
		document.getElementById("divMenuAcercade").style.display=""
		document.getElementById("principalMenub").style.display=""
		document.getElementById("divMenuAcercade").className="magictime slideDownReturn"	
		
	}
}

function vercerrarOpciones(){
	if(document.getElementById("divVentanaUser").style.display==""){
		document.getElementById("divVentanaUser").style.display="none"		
	}else{
		$("div[id=divVentanaUser]").fadeIn(500);		
	}
}
function mueveReloj(){
    momentoActual = new Date()
    hora = momentoActual.getHours()
    minuto = momentoActual.getMinutes()
    segundo = momentoActual.getSeconds()
  if(hora<10){
	  hora="0"+hora
  } 
  if(segundo<10){
	  segundo="0"+segundo
  }
  if(minuto<10){
	  minuto="0"+minuto
  }
    horaImprimible = hora + " : " + minuto + " : " + segundo
   // document.getElementById("inptreloj").value = horaImprimible
    document.getElementById("inptreloj2").value = horaImprimible
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('pfechaactual').innerHTML =dia+"/"+mes+"/"+ f.getFullYear();
    setTimeout("mueveReloj()",1000)
}

function verCerrarTemas(d){
	
	if(d=="1"){
	document.getElementById("divCambiarTemas").style.display="";	
document.getElementById("tdEfectoCambiarTema").className="magictime slideDownReturn"
	}else{
	//document.getElementById("divCambiarTemas").style.display="none";
document.getElementById("tdEfectoCambiarTema").className="magictime vanishOut"
	$("div[id=divCambiarTemas]").fadeOut(500);	
	
	}
}


window.onload = function () {
obtener_datos_user();
temaActual=localStorage.getItem("tema"+userid);
if (temaActual == "undefined" || temaActual == "" || temaActual == "Null" || temaActual == null ) {		
   temaActual="white";
}

if(temaActual=="white"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/inicio.css")
}
if(temaActual=="black"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/inicioblack.css")
}
if(temaActual=="azul"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/inicioazul.css?X-IOSV.03")
}
if(temaActual=="crema"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/inicioanaranjado.css?X-IOSV.03")
}
if(temaActual=="rosado"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/iniciorosado.css?X-IOSV.03")
}
if(temaActual=="iniciogrisazul"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/iniciogrisazul.css?X-IOSV.03")
}

FondoActual=localStorage.getItem("fondo"+userid);
if (FondoActual == "undefined" || FondoActual == "" || FondoActual == "Null" || FondoActual == null ) {		
   FondoActual="fondo";
}

if(FondoActual=="fondo"){
document.getElementById("imgFondoSytem1").src="/GoodVentaByR/iconos/fondo.jpg"
document.getElementById("imgFondoSytem2").src="/GoodVentaByR/iconos/fondo.jpg"
	
}
if(FondoActual=="fondo2"){
	
	document.getElementById("imgFondoSytem1").src="/GoodVentaByR/iconos/fondo2.jpg"
document.getElementById("imgFondoSytem2").src="/GoodVentaByR/iconos/fondo2.jpg"
}
if(FondoActual=="fondo3"){
	
document.getElementById("imgFondoSytem1").src="/GoodVentaByR/iconos/fondo3.jpg"
document.getElementById("imgFondoSytem2").src="/GoodVentaByR/iconos/fondo3.jpg"
}

var FondoUrl=localStorage.getItem("fondourl"+userid);
if (FondoUrl == "undefined" || FondoUrl == "" || FondoUrl == "Null" || FondoUrl == null ) {		
   FondoUrl="";
}
if(FondoUrl!=""){
	document.getElementById("inptUrlFondo").value=FondoUrl;
document.getElementById("imgFondoSytem1").src=FondoUrl
document.getElementById("imgFondoSytem2").src=FondoUrl
}

	if (typeof history.pushState === "function") {
		history.pushState("jibberish", null, null);
		window.onpopstate = function () {
			history.pushState('newjibberish', null, null);
			evento_atras();
			//volver_atras_pagina()
			// Handle the back (or forward) buttons here
			// Will NOT handle refresh, use onbeforeunload for this.
		};
	} else {
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
	mueveReloj()
	
	buscar_datos_del_usuario();
eventoScrollTable(document.getElementById('TableScroollProductos2'));
eventoScrollTable(document.getElementById('TableScroollHistorialVenta2'));
eventoScrollTable(document.getElementById('TableScroollHistorialVentaExpediente2'));
eventoScrollTable(document.getElementById('TableScroollHistorialVentaCanceladasExpediente2'));
eventoScrollTable(document.getElementById('TableScroollHistorialCompra2'));
eventoScrollTable(document.getElementById('TableScroollCuentasACobrar2'));
eventoScrollTable(document.getElementById('TableScroollGananciaPorVenta2'));
eventoScrollTable(document.getElementById('TableScroollMasReferencias2'));
eventoScrollTable(document.getElementById('TableScroollHistorialGarantia2'));
eventoScrollTable(document.getElementById('TableScroollCredito2'));
eventoScrollTable(document.getElementById('TableScroollArqeo2'));
scrollevents(document.getElementById('divMenuMantenimiento'));
var controlactualizacion=0;
 var counter=setInterval(timer,1000);
		function timer(){
				if(controlactualizacion==60){
			controlactualizacion=0;
			var codigopc=localStorage.getItem("codpc");

if (codigopc == "undefined" || codigopc == "" || codigopc == "Null" || codigopc == null ) {	
   resgistrardispositivo()
   vercerrarventanactualizacion("1")
}else{
	controldeactualizacion(codigopc)
}
				}
			controlactualizacion=controlactualizacion+1;
			
			}
}
function scrollevents(elemento) {
			
			
			$(elemento).on("scroll", function(){		
		 var scrollelemento=document.getElementById("divMenuMantenimiento").scrollTop;
  if (scrollelemento > 120 ) {
	
	$("table[id=divEncabezadofixed]").fadeIn(100)
  } else {
	document.getElementById('divEncabezadofixed').style.display='none';

  }		

			});
}
function eventoScrollTable(elemento){
	$(elemento).on("scroll", function(){		
		 var desplamiento = $(elemento).scrollLeft();		
		 	if($(elemento).attr("id")=="TableScroollProductos2"){
			document.getElementById("TableScroollProductos1").scrollLeft=desplamiento
			}
			if($(elemento).attr("id")=="TableScroollHistorialVenta2"){
			document.getElementById("TableScroollHistorialVenta1").scrollLeft=desplamiento
			}
			if($(elemento).attr("id")=="TableScroollHistorialVentaExpediente2"){
			document.getElementById("TableScroollHistorialVentaExpediente1").scrollLeft=desplamiento
			}	
			if($(elemento).attr("id")=="TableScroollHistorialVentaCanceladasExpediente2"){
			document.getElementById("TableScroollHistorialVentaCanceladasExpediente1").scrollLeft=desplamiento
			}	
			if($(elemento).attr("id")=="TableScroollHistorialCompra2"){
			document.getElementById("TableScroollHistorialCompra1").scrollLeft=desplamiento
			}	
			if($(elemento).attr("id")=="TableScroollCuentasACobrar2"){
			document.getElementById("TableScroollCuentasACobrar1").scrollLeft=desplamiento
			}	
			if($(elemento).attr("id")=="TableScroollGananciaPorVenta2"){
			document.getElementById("TableScroollGananciaPorVenta1").scrollLeft=desplamiento
			}	
			if($(elemento).attr("id")=="TableScroollMasReferencias2"){
			document.getElementById("TableScroollMasReferencias1").scrollLeft=desplamiento
			}	
			if($(elemento).attr("id")=="TableScroollHistorialGarantia2"){
			document.getElementById("TableScroollHistorialGarantia1").scrollLeft=desplamiento
			}			
			if($(elemento).attr("id")=="TableScroollCredito2"){
			document.getElementById("TableScroollCredito1").scrollLeft=desplamiento
			}
			if($(elemento).attr("id")=="TableScroollArqeo2"){
			document.getElementById("TableScroollArqeo1").scrollLeft=desplamiento
			}					
			}); 
}
//buscar datos del usuario
var cod_localFKUSer="";
var niveluser="";
var cajapredeterminada="";
var accesosuser;
var ControlCobradorUser="";
var CodCobradorUser="";
function buscar_datos_del_usuario() {	
	verCerrarEfectoCargando("1")
	//document.getElementById("divPresentacion").style.display = "none"
	 
	obtener_datos_user();
	document.cookie = "user=" + userid + ";max-age=86400;path=/";
	document.cookie = "pass=" + passuser + ";max-age=86400;path=/";
	var datos = new FormData();
	datos.append("user", userid)
	datos.append("pass", passuser)
	datos.append("navegador", navegador)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/buscar_datos_usuario.php",
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
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			$("div[id=divPresentacion]").fadeOut(500);	
			verCerrarEfectoCargando("2")
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			//console.log(new Blob([Respuesta]).size)
			document.getElementById("tdEfectoDivPresentacion").className="magictime vanishOut"
$("div[id=divPresentacion]").fadeOut(500);	
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos[1]
				  Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					
					verCerrarEfectoCargando("2")
					 niveluser = datos["3"];
					var nombre = datos["2"];
					 cod_localFKUSer = datos["4"];
					 ControlCobradorUser = datos["6"];
					codEncargadoSolicitud = userid;
					CodCobradorUser = datos["7"];
 accesosuser=datos["5"];  
      cajapredeterminada=buscar_datos_url_usuario('c');

	 if(accesosuser["CAMBIARCAJA"]["accion"]!="SI"){
	         document.getElementById('inptcajaAperturaCierreCaja').disabled=true
	         }
		 buscarVendedorSelec() 

					document.getElementById('lblUser').innerHTML = nombre
					document.getElementById('ptituloUser2').innerHTML = nombre
					document.getElementById('pUsuarioCabecera').innerHTML = nombre
					document.getElementById('pCajeraVenta').innerHTML = "("+nombre+")"
						document.getElementById("bNombreUser").innerHTML=nombre	
						document.getElementById("inptNombreMisDatos").value=nombre	
        
			// if(cajapredeterminada!=""){
				// if(cajapredeterminada=="001-001"){
					// document.getElementById("pCaja").innerHTML="Caja 01"
				// }
				// if(cajapredeterminada=="001-002"){
					// document.getElementById("pCaja").innerHTML="Caja 02"
				// }
			// }else{
				// document.getElementById("pCaja").innerHTML="Caja 00"
			// }
			  document.cookie = "caja=" + cajapredeterminada  +";max-age=86400;path=/";			  
	           
				removeToMenu()
					buscarabmCasaOption()
					buscarabmCasaOptionCuentas()
buscarabmZonaOption()
BuscarSelectMarca() 
BuscarSelecCategoria()
BuscarNivelesSelect()
limpiarcamposventa()

var saludo=localStorage.getItem("saludo"+userid);
if(saludo=="si"){
	
verCerrarSaludo(nombre)
}

var codigopc=localStorage.getItem("codpc");

if (codigopc == "undefined" || codigopc == "" || codigopc == "Null" || codigopc == null ) {	
   resgistrardispositivo()
   vercerrarventanactualizacion("1")
}else{
	controldeactualizacion(codigopc)
}

if(cod_localFKUSer=="1"){
	document.getElementById("divTiempo").innerHTML='<img src="https://www.meteored.com.py/wimages/fotoa8b656f3ee21d9c8f36d25d7f2aa2860.png">'
}
if(cod_localFKUSer=="2"){
	document.getElementById("divTiempo").innerHTML='<img src="https://www.meteored.com.py/wimages/fotoe0eec627281edea03ad333ec6bd954dd.png">'
}

				}
try {				
			}catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function verCerrarSaludo(){
	var control=0
	 var counter=setInterval(timer,1000);
			function timer(){
				if(control==1){
				var	 horafecha = new Date()
   var hora = horafecha.getHours()
   if(hora<12){
var saludo="Que tengas un lindo día";  
}
 if(hora>12 && hora<18){
var saludo="Que tengas una linda tarde";  

}
if(hora>18 && hora<24){
var saludo="Que tengas una linda noche";  

}
						document.getElementById("divSaludoGoodSystem").style.display=""	
	document.getElementById("spanSaludo").innerHTML=saludo	
	document.getElementById("spanMensaje").innerHTML="Un gusto volver a verte"	

	 document.getElementById("pEfectoSaludo").className="tbInfoSaludo magictime slideLeftReturn"
				}
				
					if(control==9){
						document.getElementById("pEfectoSaludo").className="tbInfoSaludo magictime slideLeft"
$("div[id=divSaludoGoodSystem]").fadeOut(500);	
				}
				if(control==10){
						clearTimeout(timer)
						localStorage.setItem("saludo"+userid, "");	 
				}
			control=control+1;
			}
	

		
	
	
}

var codigodeactualizacion="X-GT-1-JMTG-V1.25"
function controldeactualizacion(codigopc) {	
	obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("codigopc", codigopc)
	datos.append("codigodeactualizacion", codigodeactualizacion)
	datos.append("funt", "asistenciadeactualizacion")
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/system.php",
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
	
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
		
				if (Respuesta == "exito") {
				var control = datos["2"];
				if(control=="no"){
					  vercerrarventanactualizacion("1")
				}
				}
			} catch (error) {
           console.log(error)
			}
		}
	});
}
function resgistrardispositivo(codigopc) {	
	obtener_datos_user()
	var codigopc=stringGenerador(20)	
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("codigopc", codigopc)
	datos.append("funt", "registrardispositivo")
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/system.php",
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
	
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];			
				if (Respuesta == "exito") {
				localStorage.setItem("codpc",codigopc);
				vercerrarventanactualizacion("1")
				}
			} catch (error) {
           console.log(error)
			}
		}
	});
}
function resgistraractualizacion(datos) {	
	obtener_datos_user()
	datos.innerHTML="Comprobando datos...."
	var codigopc=localStorage.getItem("codpc");
	if (codigopc == "undefined" || codigopc == "" || codigopc == "Null" || codigopc == null ) {	
	location.reload(true)
	Cache.delete()
    return
    }
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("codigopc", codigopc)
	datos.append("funt", "registraractualizacion")
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/system.php",
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
	
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
		
			location.reload(true)
			window.parent.caches.delete()
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];			
				if (Respuesta == "exito") {
				
				}
			} catch (error) {
           console.log(error)
			}
		}
	});
}
function stringGenerador(length) {
   var result           = '';
   var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
   var charactersLength = characters.length;
   for ( var i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
   }
   return result;
}

var temaActual="";
function CambiarTema(d){
	obtener_datos_user();
	 localStorage.setItem("tema"+userid, d);	 
	 if(d=="white"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/inicio.css")
}
if(d=="black"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/inicioblack.css")
}
if(d=="azul"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/inicioazul.css?X-IOSV.03")
}
if(d=="crema"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/inicioanaranjado.css?X-IOSV.03")
}
if(d=="rosado"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/iniciorosado.css?X-IOSV.03")
}
if(d=="iniciogrisazul"){
	$("link[id=cssTema]").attr("href","/GoodVentaByR/css_system/iniciogrisazul.css?X-IOSV.03")
}
ver_vetana_informativa("SE HA CAMBIADO EL TEMA")
}
var FondoActual="";
function CambiarFondo(d){
	obtener_datos_user();
	 localStorage.setItem("fondo"+userid, d);	 
	  localStorage.setItem("fondourl"+userid, "");	
	  document.getElementById("inptUrlFondo").value=""
if(d=="fondo"){
document.getElementById("imgFondoSytem1").src="/GoodVentaByR/iconos/fondo.jpg"
document.getElementById("imgFondoSytem2").src="/GoodVentaByR/iconos/fondo.jpg"
	
}
if(d=="fondo2"){
	
	document.getElementById("imgFondoSytem1").src="/GoodVentaByR/iconos/fondo2.jpg"
document.getElementById("imgFondoSytem2").src="/GoodVentaByR/iconos/fondo2.jpg"
}
if(d=="fondo3"){
	
document.getElementById("imgFondoSytem1").src="/GoodVentaByR/iconos/fondo3.jpg"
document.getElementById("imgFondoSytem2").src="/GoodVentaByR/iconos/fondo3.jpg"
}
ver_vetana_informativa("SE HA CAMBIADO EL FONDO")
}	
function guardarFondoPerzonalizado(){
	
	var fondo=document.getElementById("inptUrlFondo").value
	 localStorage.setItem("fondourl"+userid, fondo);	
	 document.getElementById("imgFondoSytem1").src=fondo
document.getElementById("imgFondoSytem2").src=fondo
ver_vetana_informativa("SE HA CAMBIADO EL FONDO")
}
		/*
CERRAR SESION 
*/

function cerrarSesion(){
	var control=0
	document.getElementById("btitulocargarinicio").innerHTML=" Cerrando..."
	document.getElementById("divPresentacion").style.display=""
	document.getElementById("tdEfectoDivPresentacion").className="magictime vanishIn"
	document.getElementById("spanSaludo").innerHTML=""	
	document.getElementById("spanMensaje").innerHTML="Hasta pronto "	
	document.getElementById("divSaludoGoodSystem").style.display=""		
	document.getElementById("pEfectoSaludo").className="tbInfoSaludo magictime slideLeftReturn"
	 var counter=setInterval(timer,1000);
			function timer(){
			if(control==3){
				cerrarSesion2()
			}
			control=control+1;
			}
	

		
	
	
}
function cerrarSesion2() {
	verCerrarEfectoCargando("1")	
	document.cookie = "user=;max-age=86400;path=/";
	document.cookie = "pass=;max-age=86400;path=/";
	obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/cerrarsesion.php",
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
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			verCerrarEfectoCargando("2")
			return false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					
				ir_a_login()
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
MENU 
*/
var paginacargando = "<center><br><br><img src='/GoodVentaByR/iconos/cargando.gif' style='width:100%' /></center>";
function menu(d) {
	document.getElementById("tbHome").style.display = "none"
	document.getElementById("tbMantenimiento").style.display = "none"
	document.getElementById("tbMovimiento").style.display = "none"
	document.getElementById("tbReporte").style.display = "none"
	if (d == "1") {
		document.getElementById("tbHome").style.display = ""
	}
	if (d == "2") {
		document.getElementById("tbMantenimiento").style.display = ""
	}
	if (d == "3") {
		document.getElementById("tbMovimiento").style.display = ""
	}
	if (d == "4") {
		document.getElementById("tbReporte").style.display = ""
	}
}
/*
AUDITORIA
*/
function verCerrarAuditoria(d){
	
	
	if(d=="1"){
	document.getElementById("divAuditoria").style.display="";	
	  document.getElementById("tdEfectoAuditoria").className="magictime slideDownReturn"
	}else{
	document.getElementById("tdEfectoAuditoria").className="magictime vanishOut"
	$("div[id=divAuditoria]").fadeOut(500);	
	
	}
}

/*
ABM USUARIOS
*/
function verCerrarAbmUsuarios(){
	document.getElementById("divSegundoPlano").style.display="none";
if(document.getElementById("divAbmUsuario").style.display==""){
	document.getElementById("divMinimizadoUsuarios").style.display="none"
document.getElementById("tdEfectoAbmUsuario").className="magictime vanishOut"
	$("div[id=divAbmUsuario]").fadeOut(500);	
	limpiarcamposbuscarusuarios()
	limpiarcamposusuarios()
}else{		
if(controlacceso("VERLISTADOUSUARIO","accion")==false){return;}

document.getElementById("divAbmUsuario").style.display=""
  document.getElementById("tdEfectoAbmUsuario").className="magictime slideDownReturn"
}
}
function verCerrarMisDatos(d){
	
	
	if(d=="1"){
	document.getElementById("divCambiarMisDatosPersonales").style.display="";	
	  document.getElementById("tdEfectoAbmMisDatos").className="magictime slideDownReturn"
	}else{
	document.getElementById("tdEfectoAbmMisDatos").className="magictime vanishOut"
	$("div[id=divCambiarMisDatosPersonales]").fadeOut(500);	
	
	}
}
function limpiarcamposbuscarusuarios(){
		document.getElementById('inptBuscarUsuario1').value=""
	    document.getElementById('inptBuscarUsuario2').value=""
	   document.getElementById('inptBuscarUsuario3').value=""
	   document.getElementById('table_abm_usuarios').innerHTML=""
}
function minimizarusuarios(){
document.getElementById("tdEfectoAbmUsuario").className="magictime slideDown"
	$("div[id=divAbmUsuario]").fadeOut(500);
	document.getElementById("divMinimizadoUsuarios").style.display=""
}
function verCerrarVentanaAbmUsuarios(d, l) {
	
	
	if (d == "1") {		
		if (l == "1") {
			if(controlacceso("INSERTARLISTADOUSUARIO","accion")==false){return;}
			limpiarcamposusuarios()
		}
		document.getElementById('divAbmUsuario1').style.display = "none"
		$("div[id=divAbmUsuario2]").fadeIn(250)
	} else {
		$("div[id=divAbmUsuario1]").fadeIn(250)
		document.getElementById('divAbmUsuario2').style.display = "none"
	}
}
function verVentanaEditarUsuario() {
	if (idAbmUsuario == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	if(controlacceso("EDITARLISTADOUSUARIO","accion")==false){return;}
	verCerrarVentanaAbmUsuarios("1", "2")
}
var idAbmUsuario = ""
function obtenerdatosabmusuario(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptNombreApellidoUsuario').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccUser').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inpt_user_selecc').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNroDocUsuario').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptNroTelefUsuario').value = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptClaveAcceso').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptContrasenhaUser').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptTipoUser').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptEstadoUser').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptlocaluser').value = $(datostr).children('td[id="td_datos_7"]').html();
	idAbmUsuario = $(datostr).children('td[id="td_id"]').html();
    document.getElementById('btnEditarUsuario').style.backgroundColor="";
    document.getElementById('btnAbmUsuario').value = "Editar datos";
}
function verificarcamposusuario() {
	var inptNombreApellidoUsuario = document.getElementById('inptNombreApellidoUsuario').value
	var inptNroDocUsuario = document.getElementById('inptNroDocUsuario').value
	var inptNroTelefUsuario = document.getElementById('inptNroTelefUsuario').value
	var inptClaveAcceso = document.getElementById('inptClaveAcceso').value
	var inptContrasenhaUser = document.getElementById('inptContrasenhaUser').value
	var inptTipoUser = document.getElementById('inptTipoUser').value
	var inptEstadoUser = document.getElementById('inptEstadoUser').value
	var inptlocaluser = document.getElementById('inptlocaluser').value
	if (inptNombreApellidoUsuario == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DE USUARIO", "#")
		return false;
	}
	if (inptNroDocUsuario == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE DOCUMENTO", "#")
		return false;
	}
	if (inptClaveAcceso == "") {
		ver_vetana_informativa("FALTO INGRESAR LA CLAVE DE ACCESO", "#")
		return false;
	}
	if (inptContrasenhaUser == "") {
		ver_vetana_informativa("FALTO INGRESAR LA CONTRASEÑA", "#")
		return false;
	}
	var accion = "";
	if (idAbmUsuario != "") {
		accion = "editar";
		if(controlacceso("INSERTARLISTADOUSUARIO","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("EDITARLISTADOUSUARIO","accion")==false){return;}
	}
	abmusuario(inptNombreApellidoUsuario, inptNroDocUsuario, inptNroTelefUsuario, inptClaveAcceso, inptContrasenhaUser, inptTipoUser, inptEstadoUser, inptlocaluser, idAbmUsuario, accion);
}
function abmusuario(nombre_persona, rut_usuario, telefono, login, pass, acceso, estado, cod_localFK, cod_persona, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_persona", cod_persona)
	datos.append("nombre_persona", nombre_persona)
	datos.append("rut_usuario", rut_usuario)
	datos.append("telefono", telefono)
	datos.append("login", login)
	datos.append("password", pass)
	datos.append("estado", estado)
	datos.append("cod_localFK", cod_localFK)
	datos.append("acceso", acceso)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmusuarios.php",
		type: "post",
		 cache:false,
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
					limpiarcamposusuarios()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmUsuario = ""
					buscarabmusuario()
				}
					
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function AbmEditarMisDatos() {
	
	var nombre=document.getElementById("inptNombreMisDatos").value;
	if(nombre==""){
		ver_vetana_informativa("EL USUARIO NO PUEDE QUEDAR VACÍO")
	}
	var user=document.getElementById("inptUserMisDatos").value;
	if(user==""){
		ver_vetana_informativa("EL USUARIO NO PUEDE QUEDAR VACÍO")
	}
	var pass=document.getElementById("inptPassMisDatos").value;
	if(pass==""){
		ver_vetana_informativa("EL CONTRASEÑA NO PUEDE QUEDAR VACÍO")
	}
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "editarMisDatos")
	datos.append("user", user)
	datos.append("pass", pass)
	datos.append("nombre", nombre)
	datos.append("local", cod_localFKUSer)
	var OpAjax = $.ajax({
		
		data: datos,
		url: "/GoodVentaByR/php_system/abmusuarios.php",
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
			   Respuesta=respuestaJqueryAjax(Respuesta)
		if (Respuesta == true) {	
		document.getElementById("inptUserMisDatos").value;
	document.getElementById("inptPassMisDatos").value;	
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")					
					}

			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function checkestadouser(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarUser1').checked=true
		document.getElementById('inptSeleccEstadoBuscarUser2').checked=false	
	}else{
		
		document.getElementById('inptSeleccEstadoBuscarUser1').checked=false
		document.getElementById('inptSeleccEstadoBuscarUser2').checked=true
	}
}
function buscarabmusuario() {
if(controlacceso("BUSCARLISTADOUSUARIO","accion")==false){return;}
	var codigo = document.getElementById('inptBuscarUsuario1').value
	var documento = document.getElementById('inptBuscarUsuario2').value
	var usuario = document.getElementById('inptBuscarUsuario3').value
	var local = document.getElementById('inptBuscarUsuario4').value
	if(document.getElementById('inptSeleccEstadoBuscarUser1').checked==true){
		estado='Activo'
	}else{
		estado='Inactivo'
	}
	document.getElementById("table_abm_usuarios").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codigo": codigo,
		"documento": documento,
		"usuario": usuario,
		"estado": estado,
		"local": local,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmusuarios.php",
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
			document.getElementById("table_abm_usuarios").innerHTML = ''
		},
		success: function (responseText) {
		var Respuesta = responseText;
		console.log(Respuesta)
		document.getElementById("table_abm_usuarios").innerHTML = ''
		try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
                document.getElementById("table_abm_usuarios").innerHTML = datos_buscados
				
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function limpiarcamposusuarios() {
	document.getElementById('inptNombreApellidoUsuario').value = ""
	document.getElementById('inptNroDocUsuario').value = ""
	document.getElementById('inptNroTelefUsuario').value = ""
	document.getElementById('inptClaveAcceso').value = ""
	document.getElementById('inptContrasenhaUser').value = ""
	document.getElementById('inptRegistroSeleccUser').value = ""
	document.getElementById('inptEstadoUser').value = "Activo";
	document.getElementById('btnAbmUsuario').value = "Guardar datos";
	document.getElementById('btnEditarUsuario').style.backgroundColor="#b7b7b7";
	idAbmUsuario = "";
	seleccionarLocalUSer()
}
function verCerrarAccesoUsuario(d) {
   
    if (d == "1") {
	if(controlacceso("VERACCESOSUARIOS","accion")==false){return;}
	if(idAbmUsuario==""){
	ver_vetana_informativa("FALTO SELCCIONAR UN REGISTRO")
	return false;
	}
        document.getElementById("divVistaAcceso").style.display = ""
 document.getElementById("tdEfectoVistaAcceso").className="magictime slideLeftReturn"

		 idAbmAccesoUser="";
		buscarAccesosUser()
    }else{
document.getElementById("tdEfectoVistaAcceso").className="magictime slideRight"
$("div[id=divVistaAcceso]").fadeOut(500);
 
}
}
function buscarAccesosUser() {
	if(controlacceso("VERACCESOSUARIOS","accion")==false){return;}
	document.getElementById("table_abm_accesos_Abm").innerHTML = paginacargando
	var buscador=document.getElementById("inptBuscarAccesos").value
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscador": buscador,
		"buscar": idAbmUsuario,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
        url: "/GoodVentaByR/php_system/abmAccesos.php",
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
			document.getElementById("table_abm_accesos_Abm").innerHTML = ""
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
				document.getElementById("table_abm_accesos_Abm").innerHTML = ""
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados1 = datos[2];
             	   document.getElementById("table_abm_accesos_Abm").innerHTML =datos[2]
             	   document.getElementById("inpt_nivel_selecc").value = datos[3]
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function abmacceso(d) {
	if(controlacceso("VERACCESOSUARIOS","accion")==false){return;}	
	var intpu=$(d)
	var idabm=d.id
	var accion="NO"
	if ($(intpu).is(':checked') ){
	accion="SI"
	}
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("usuarios_idusario", userid)
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "editar")
	datos.append("idabm", idabm)
	datos.append("idAbmUsuario", idAbmUsuario)
	datos.append("acciones", accion)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmAccesos.php",
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")	
					document.getElementById("inpt_nivel_selecc").value=datos["2"];
					}			
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
//Abm DarBaja
function MinimizardarDeBaja(){
	
document.getElementById("tdEfectoAbmBaja").className="magictime slideDown"
	$("div[id=divAbmdebaja]").fadeOut(500);	
	$("div[id=divAbmbajaproducto1]").fadeOut(500);	

	document.getElementById("divMinimizadoDarBaja").style.display=""
}
function vercerrardarDeBaja(d) {
	document.getElementById('divSegundoPlano').style.display = "none"
	
	
	document.getElementById("divMinimizadoDarBaja").style.display="none"
	if (d == "1") {
		document.getElementById('divAbmdebaja').style.display = ""
		document.getElementById("divAbmbajaproducto1").style.display=""
 document.getElementById("tdEfectoAbmBaja").className="magictime slideDownReturn"
	}else{
			if(controldebusquedadProductosdeBaja==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
				document.getElementById("tdEfectoAbmBaja").className="magictime vanishOut"
$("div[id=divAbmdebaja]").fadeOut(500);	
$("div[id=divAbmbajaproducto1]").fadeOut(500);	
	}
}
function verCerrarVentanaAbmdarDeBaja(d) {
	
	if (d == "2") {
	//	document.getElementById('divAbmdebaja').style.display = "none"
		document.getElementById("tdEfectoAbmBaja").className="magictime slideRight"
$("div[id=divAbmdebaja]").fadeOut(500);	
$("div[id=divAbmbajaproducto2]").fadeOut(500);	
	
	} else {
		if (idAbmProducto != "") {
		document.getElementById('divAbmdebaja').style.display = ""
		 document.getElementById("tdEfectoAbmBaja").className="magictime slideLeftReturn"
		document.getElementById("divAbmbajaproducto2").style.display=""	
		var nombreProducto = document.getElementById('inptRegistroSeleccProducto').value
		
		document.getElementById('inptNombreProductobaja').value =nombreProducto;
		}else{
			ver_vetana_informativa("DEBES SELECCIONAR UN REGISTRO", "#")
		}
	}
}
function verificarcamposProductobaja() {
	var inptcantidadProductobaja = document.getElementById('inptcantidadProductobaja').value
	var inptMotivoProductobaja = document.getElementById('inptMotivoProductobaja').value
	var inptNombreProductobaja = document.getElementById('inptNombreProductobaja').value

	if (inptNombreProductobaja == "") {
		ver_vetana_informativa("DEBES SELECCIONAR UN PRODUCTO", "#")
		return false;
	}

	if (inptcantidadProductobaja == "") {
		ver_vetana_informativa("FALTO INGRESAR UNA CANTIDAD", "#")
		return false;
	}

	if (inptMotivoProductobaja == "") {
		ver_vetana_informativa("FALTO INGRESAR UN MOTIVO", "#")
		return false;
	}

	var accion = "";
	if (idAbmProducto != "") {
		
		accion = "nuevo";
		abmProductobaja(inptcantidadProductobaja, inptMotivoProductobaja , idAbmProducto, accion);
		
	} else {
		ver_vetana_informativa("DEBES SELECCIONAR UN PRODUCTO", "#")
		
	}
	
}
function abmProductobaja(cantidad, motivo , idAbmProducto, accion) {
	
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("Cantidad", cantidad)
	datos.append("Motivo", motivo)
	datos.append("Cod_usuarioFK", userid)
	datos.append("Cod_productoFK", idAbmProducto)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmDebaja.php",
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
					limpiarcamposProductobaja()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmProducto = ""
					buscarabmproducto()
					verCerrarVentanaAbmdarDeBaja("2");
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});

}
function limpiarcamposProductobaja() {
	document.getElementById('inptcantidadProductobaja').value = "";
	document.getElementById('inptMotivoProductobaja').value = "";
	document.getElementById('inptRegistroSeleccCaja').value = "";
	document.getElementById('btnBajaDatosProductos').style.backgroundColor="#2196F3";
	document.getElementById('btnAbmProductobaja').value = "Guardar datos";
	idAbmProducto = "";
	document.getElementById('btnBajaDatosProductos').disabled=false;
}
function checkproductoBaja(d){
	if(d=="1"){
	document.getElementById('inptSelecproductoBaja2').checked=false
	document.getElementById('inptSelecproductoBaja1').checked=true
	document.getElementById('inptFechaproductoBaja1').value="";
      document.getElementById('inptFechaproductoBaja2').value="";
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
	document.getElementById('inptSelecproductoBaja2').checked= true
	document.getElementById('inptSelecproductoBaja1').checked=false
	
	document.getElementById('inptFechaproductoBaja1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptFechaproductoBaja2').value = f.getFullYear() + "-" + mes + "-" + dia;
	}
}
var registrocargadoproductosdebaja="";
var totalregistroproductosdebaja="";
var controldebusquedadProductosdeBaja=false
function cancelarProductosDeBaja(){
	controldebusquedadProductosdeBaja=false
	document.getElementById("divProgressProductosDeBaja").style.backgroundColor='#ff5722'
}
function buscarProductoBaja() {
	var usuario = document.getElementById('inptBuscarproductoBaja4').value
	var fecha1 = document.getElementById('inptFechaproductoBaja1').value
	var fecha2 = document.getElementById('inptFechaproductoBaja2').value
	var fechaFija = document.getElementById('inptBuscarproductoBaja3').value
	var Codigo = document.getElementById('inptBuscarproductoBaja1').value
	var Producto = document.getElementById('inptBuscarproductoBaja2').value
	if(controldebusquedadProductosdeBaja==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadProductosdeBaja=true
	document.getElementById("table_Vista_productoBaja").innerHTML = paginacargando
	document.getElementById("tbProcessProductosDeBaja").style.display = "none"
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"Fecha1": fecha1,
		"Fecha2": fecha2,
		"Nombre": Producto,
		"Fechafijo": fechaFija,
		"Cod_productoFK": Codigo,
		"Usuario": usuario,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmDebaja.php",
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
			document.getElementById("table_Vista_productoBaja").innerHTML = ''
			controldebusquedadProductosdeBaja=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_Vista_productoBaja").innerHTML = ''
				try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					var Total_Registros = datos[3];
					var Total_Efectivo = datos[4];
					
					document.getElementById("table_Vista_productoBaja").innerHTML = datos_buscados
					document.getElementById('inptTotalRegistoproductoBaja').value = Total_Registros;
					
						registrocargadoproductosdebaja=datos[99];
					totalregistroproductosdebaja=datos[100];
			
						 if(totalregistroproductosdebaja>registrocargadoproductosdebaja){
						 	var porce=((registrocargadoproductosdebaja*100)/totalregistroproductosdebaja).toFixed(0)
	document.getElementById("divProgressProductosDeBaja").style.width=porce+"%"
						 document.getElementById("table_Vista_productoBaja").innerHTML += "<div id='table_mas_Vista_productoBaja'></div>"
						  buscarMasProductoBaja();
					 }else{
						 controldebusquedadProductosdeBaja=false
					 }
					
				}
			
			} catch (error) {
				controldebusquedadProductosdeBaja=false
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
	}
function buscarMasProductoBaja(c) {
	var usuario = document.getElementById('inptBuscarproductoBaja4').value
	var fecha1 = document.getElementById('inptFechaproductoBaja1').value
	var fecha2 = document.getElementById('inptFechaproductoBaja2').value
	var fechaFija = document.getElementById('inptBuscarproductoBaja3').value
	var Codigo = document.getElementById('inptBuscarproductoBaja1').value
	var Producto = document.getElementById('inptBuscarproductoBaja2').value
	if(c=="1"){
		controldebusquedadProductosdeBaja=true
	}
	if(controldebusquedadProductosdeBaja==false){
	return
}
controldebusquedadProductosdeBaja=true
	document.getElementById("table_mas_Vista_productoBaja").innerHTML = paginacargando
	document.getElementById("tbProcessProductosDeBaja").style.display = ""
	document.getElementById("divProgressProductosDeBaja").style.backgroundColor=''
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"Fecha1": fecha1,
		"Fecha2": fecha2,
		"Nombre": Producto,
		"Fechafijo": fechaFija,
		"Cod_productoFK": Codigo,
		"Usuario": usuario,
		"registrocargado": registrocargadoproductosdebaja,
		"funt": "buscarmas"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmDebaja.php",
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
			document.getElementById("table_mas_Vista_productoBaja").innerHTML = ''
			document.getElementById("divProgressProductosDeBaja").style.backgroundColor='#ff5722'
			controldebusquedadProductosdeBaja=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_mas_Vista_productoBaja").innerHTML = ''
				try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					var Total_Registros = datos[3];
					var Total_Efectivo = datos[4];
					
					document.getElementById("table_mas_Vista_productoBaja").innerHTML = datos_buscados
					document.getElementById('inptTotalRegistoproductoBaja').value = Total_Registros;
					
						registrocargadoproductosdebaja=datos[99];
					
						 if(totalregistroproductosdebaja>registrocargadoproductosdebaja){
						 	var porce=((registrocargadoproductosdebaja*100)/totalregistroproductosdebaja).toFixed(0)
	document.getElementById("divProgressProductosDeBaja").style.width=porce+"%"
						 document.getElementById("table_mas_Vista_productoBaja").innerHTML += "<div id='table_mas_Vista_productoBaja'></div>"
						 document.getElementById("table_mas_Vista_productoBaja").id=""
						  buscarMasProductoBaja();
					 }else{
						 document.getElementById("tbProcessProductosDeBaja").style.display = "none"
						 controldebusquedadProductosdeBaja=false
					 }
					
				}
			
			} catch (error) {
				document.getElementById("divProgressProductosDeBaja").style.backgroundColor='#ff5722'
				controldebusquedadProductosdeBaja=false
					ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
					GuardarArchivosLog(titulo)
			}
		}
	});
	}
/*
INFO
*/
function ver_vetana_informativa(titulo) {

	document.getElementById('lbltitulomensaje').innerHTML = titulo
	document.getElementById('div_principal_info').style.display = ''
    document.getElementById("btnocultarinfo").focus()
}
var idFkCobrador = ""
var idFkVendedor = ""
var controlseleccvistaCobrador = ""
function vercerrarvistacobrador(d, ventana) {
	if (d == "1") {
		document.getElementById("divVistaCobrador").style.display=""
		controlseleccvistaCobrador = ventana
		buscarvistacobrador();
	} else {
		document.getElementById("divVistaCobrador").style.display="none"
	}
}
function buscarvistacobrador() {
	var buscador = document.getElementById('inptBuscarVistaCobrador').value
	document.getElementById("table_vista_cobrador").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"funt": "buscarvista"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmusuarios.php",
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
			document.getElementById("table_vista_cobrador").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_cobrador").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_vista_cobrador").innerHTML = datos_buscados
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function obtenerdatosvistacobrador(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	if (controlseleccvistaCobrador == "arqueo") {
		cobradorarqueo = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptCobradorArqueo').value = $(datostr).children('td[id="td_datos_2"]').html();
	}
	if (controlseleccvistaCobrador == "aperturacierre") {
		codCajeroapertura = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptcajeroAperturaCierreCaja').value = $(datostr).children('td[id="td_datos_2"]').html();
	}
	document.getElementById("divVistaCobrador").style.display = "none"
}
/*
ABM PROVEEDOR
*/
function verCerrarAbmProveedor(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmProveedor").style.display==""){
		document.getElementById("divMinimizadoListadoProveedor").style.display="none"	
		limpiarcamposbuscarproveedor()
		limpiarcamposProveedor()
document.getElementById("tdEfectoAbmProveedor").className="magictime vanishOut"
	$("div[id=divAbmProveedor]").fadeOut(500);	
	}else{		
	if(controlacceso("VERLISTADOPROVEEDORES","accion")==false){return;}
		document.getElementById("divAbmProveedor").style.display=""
document.getElementById("tdEfectoAbmProveedor").className="magictime slideDownReturn"
		
	}
}
function limpiarcamposbuscarproveedor(){
	document.getElementById('inptBuscarProveedor1').value=""
	document.getElementById('inptBuscarProveedor2').value=""
	document.getElementById('inptBuscarProveedor3').value=""
	document.getElementById("table_abm_proveedor").innerHTML = ""
	document.getElementById("inptRegistroNroProveedor").value ="";
}
function minimizarabmproveedor(){
document.getElementById("tdEfectoAbmProveedor").className="magictime slideDown"
	$("div[id=divAbmProveedor]").fadeOut(500);
	document.getElementById("divMinimizadoListadoProveedor").style.display=""	
}
function verCerrarVentanaAbmVistaProveedor(d) {
	if (d == "1") {
		if(controlacceso("INSERTARLISTADOPROVEEDORES","accion")==false){return;}
		$("div[id=divAbmProveedorVista]").fadeIn(250)
		document.getElementById("inptNombreApellidoProveedorVista").value = ""
		document.getElementById("inptNroDocProveedorVista").value = ""
		document.getElementById("inptNroTelefProveedorVista").value = ""
		document.getElementById("inptDireccionProveedorVista").value = ""
		document.getElementById("inptCorreoProveedorVista").value = ""
	} else {
		$("div[id=divAbmProveedorVista]").fadeOut(250)
	}
}
function verificarcamposProveedorVista() {
	var inptNombreApellidoProveedor = document.getElementById('inptNombreApellidoProveedorVista').value
	var inptNroDocProveedor = document.getElementById('inptNroDocProveedorVista').value
	var inptNroTelefProveedor = document.getElementById('inptNroTelefProveedorVista').value
	var inptDireccionProveedor = document.getElementById('inptDireccionProveedorVista').value
	var inptCorreoProveedor = document.getElementById('inptCorreoProveedorVista').value
	var inptEstadoProveedor = "Activo"
	if (inptNombreApellidoProveedor == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL PROVEEDOR", "#")
		return false;
	}
	if (inptNroDocProveedor == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE R.U.C", "#")
		return false;
	}
	var accion = "nuevo";
	if(controlacceso("INSERTARLISTADOPROVEEDORES","accion")==false){return;}
	abmproveedor(inptNombreApellidoProveedor, inptNroDocProveedor, inptNroTelefProveedor, inptDireccionProveedor, inptCorreoProveedor, inptEstadoProveedor, idAbmProveedor, accion);
}
function verCerrarVentanaAbmProveedor(d, l) {
	if (d == "1") {
		if (l == "1") {
			if(controlacceso("INSERTARLISTADOPROVEEDORES","accion")==false){return;}
			limpiarcamposProveedor()
		}
		$("div[id=divAbmProveedor2]").fadeIn(250)
		document.getElementById('divAbmProveedor1').style.display = "none"
	} else {
		$("div[id=divAbmProveedor1]").fadeIn(250)
		document.getElementById('divAbmProveedor2').style.display = "none"
	}
}
function verVentanaEditarProveedor() {
	if(controlacceso("EDITARLISTADOPROVEEDORES","accion")==false){return;}
	if (idAbmProveedor == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmProveedor("1", "2")
}
var idAbmProveedor = ""
function obtenerdatosabmProveedor(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptNombreApellidoProveedor').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccProveedor').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNroDocProveedor').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptNroTelefProveedor').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptDireccionProveedor').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptCorreoProveedor').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptEstadoProveedor').value = $(datostr).children('td[id="td_datos_6"]').html();
			document.getElementById('inptUsuarioInsertadoPor').value=$(datostr).children('td[id="td_datos_100"]').html()
	document.getElementById('inptFechaInsertadoPor').value=$(datostr).children('td[id="td_datos_102"]').html()
	document.getElementById('inptUsuarioEditadoPor').value=$(datostr).children('td[id="td_datos_101"]').html()
	document.getElementById('inptFechaEditadoPor').value=$(datostr).children('td[id="td_datos_103"]').html()
	idAbmProveedor = $(datostr).children('td[id="td_id"]').html();
document.getElementById('btnAbmProveedor').value = "Editar Datos";
document.getElementById('btnEditarProveedores').style.backgroundColor="";
document.getElementById('btnAuditoriaProveedor').style.backgroundColor="#673ab7";
}
function verificarcamposProveedor() {
	var inptNombreApellidoProveedor = document.getElementById('inptNombreApellidoProveedor').value
	var inptNroDocProveedor = document.getElementById('inptNroDocProveedor').value
	var inptNroTelefProveedor = document.getElementById('inptNroTelefProveedor').value
	var inptDireccionProveedor = document.getElementById('inptDireccionProveedor').value
	var inptCorreoProveedor = document.getElementById('inptCorreoProveedor').value
	var inptEstadoProveedor = document.getElementById('inptEstadoProveedor').value
	if (inptNombreApellidoProveedor == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL PROVEEDOR", "#")
		return false;
	}
	if (inptNroDocProveedor == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE R.U.C", "#")
		return false;
	}
	var accion = "";
	if (idAbmProveedor != "") {
		accion = "editar";
		if(controlacceso("EDITARLISTADOPROVEEDORES","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("INSERTARLISTADOPROVEEDORES","accion")==false){return;}
	}
	abmproveedor(inptNombreApellidoProveedor, inptNroDocProveedor, inptNroTelefProveedor, inptDireccionProveedor, inptCorreoProveedor, inptEstadoProveedor, idAbmProveedor, accion);
}
function abmproveedor(nombre_persona, rut_proveedor, telefono, direccion, email, estado, cod_persona, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_persona", cod_persona)
	datos.append("nombre_persona", nombre_persona)
	datos.append("direccion", direccion)
	datos.append("telefono", telefono)
	datos.append("email", email)
	datos.append("rut_proveedor", rut_proveedor)
	datos.append("estado", estado)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproveedor.php",
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
					limpiarcamposProveedor()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmProveedor = ""
					buscarabmProveedor()
					verCerrarVentanaAbmVistaProveedor("2")
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function checkestadoproveedor(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarProveedor1').checked=true
		document.getElementById('inptSeleccEstadoBuscarProveedor2').checked=false	
	}else{
		
		document.getElementById('inptSeleccEstadoBuscarProveedor1').checked=false
		document.getElementById('inptSeleccEstadoBuscarProveedor2').checked=true
	}
}
function buscarabmProveedor() {
if(controlacceso("BUSCARLISTADOPROVEEDORES","accion")==false){return;}
	var codigo = document.getElementById('inptBuscarProveedor1').value
	var ruc = document.getElementById('inptBuscarProveedor2').value
	var proveedor = document.getElementById('inptBuscarProveedor3').value
	var estado =""
	if(document.getElementById('inptSeleccEstadoBuscarProveedor1').checked==true){
		estado ="Activo"
	}else{
		estado ="Inactivo"
	}
	document.getElementById("table_abm_proveedor").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codigo": codigo,
		"ruc": ruc,
		"proveedor": proveedor,
		"estado": estado,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproveedor.php",
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
			document.getElementById("table_abm_proveedor").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_proveedor").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				  Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {	
				var datos_buscados = datos[2];
					document.getElementById("table_abm_proveedor").innerHTML = datos_buscados
					document.getElementById("inptRegistroNroProveedor").value = datos[3];
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
			var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function limpiarcamposProveedor() {
	document.getElementById('inptNombreApellidoProveedor').value = "";
	document.getElementById('inptRegistroSeleccProveedor').value = "";
	document.getElementById('inptNroDocProveedor').value = "";
	document.getElementById('inptNroTelefProveedor').value = "";
	document.getElementById('inptDireccionProveedor').value = "";
	document.getElementById('inptCorreoProveedor').value = "";
	document.getElementById('btnAbmProveedor').value = "Guardar Datos";
	document.getElementById('inptEstadoProveedor').value = "Activo";
	document.getElementById('btnEditarProveedores').style.backgroundColor = "#b7b7b7";
	document.getElementById('btnAuditoriaProveedor').style.backgroundColor = "#b7b7b7";
	idAbmProveedor = "";
}
var controlseleccvistaProveedor = ""
var codProveedorCompra = ""
function vercerrarvistaProveedor(d, ventana) {
	if (d == "1") {
	
		 document.getElementById("divVistaProveedor").style.display = ""
 document.getElementById("tdEfectoVistaProveedor").className="magictime slideLeftReturn"
		controlseleccvistaProveedor = ventana
		buscarvistaproveedor();
	} else {
		
		document.getElementById("tdEfectoVistaProveedor").className="magictime slideRight"
$("div[id=divVistaProveedor]").fadeOut(500);
	}
	
}
function buscarvistaproveedor() {
	var buscador = document.getElementById('inptBuscarVistaProveedor').value
	document.getElementById("table_vista_Proveedor").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"funt": "buscarvista"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmproveedor.php",
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
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_vista_Proveedor").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_Proveedor").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {	
					var datos_buscados = datos[2];
					document.getElementById("table_vista_Proveedor").innerHTML = datos_buscados
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function obtenerdatosvistaProveedor(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	if (controlseleccvistaProveedor == "compra") {
		codProveedorCompra = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptProveedorCompra').value = $(datostr).children('td[id="td_datos_1"]').html();
	}
	if (controlseleccvistaProveedor == "abmproducto") {
		codProveedorAbmProducto = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptProveesorProducto').value = $(datostr).children('td[id="td_datos_1"]').html();
	}
	if (controlseleccvistaProveedor == "productoComprado") {
		codProveedorComprainf = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptProveedorProductoComprado').value = $(datostr).children('td[id="td_datos_1"]').html();
	}

			document.getElementById("tdEfectoVistaProveedor").className="magictime slideRight"
$("div[id=divVistaProveedor]").fadeOut(500);
}
/*ABM PRODUCTO*/
function verCerrarAbmProducto(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmProducto").style.display==""){
			if(controldebusquedadProductos==true){
	ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
    }
limpiarcamposproducto()
		limpiarcamposbuscarproductos()
		
document.getElementById("tdEfectoAbmProductos").className="magictime vanishOut"
	$("div[id=divAbmProducto]").fadeOut(500);	
		document.getElementById("divMinimizadoListadoProducto1").style.display="none"
		document.getElementById("divMinimizadoListadoProductos").style.display="none"
		
	}else{	
		if(controlacceso("VERLISTADOPRODUCTOS","accion")==false){return;}
		
	document.getElementById("divAbmProducto").style.display=""
document.getElementById("tdEfectoAbmProductos").className="magictime slideDownReturn"
		
	}
}
function verCerrarAbmProducto2(){
		document.getElementById("imgMinimizaeProducto").style.display="none"
		document.getElementById("imgCerrarProducto").style.display=""
		document.getElementById("tdEfectoAbmProductos").className="magictime vanishOut"
	$("div[id=divAbmProducto]").fadeOut(500);
		document.getElementById('divAbmProducto2').style.display = "none"
		document.getElementById('divAbmProducto1').style.display = ""
		
}
function minimizarabmproductos(){
document.getElementById("tdEfectoAbmProductos").className="magictime slideDown"
	$("div[id=divAbmProducto]").fadeOut(500);
		document.getElementById("divMinimizadoListadoProducto1").style.display=""
		document.getElementById("divMinimizadoListadoProductos").style.display=""
		
}
function verCerrarVentanaAbmProducto(d, l) {
		document.getElementById("imgMinimizaeProducto").style.display=""
		document.getElementById("imgCerrarProducto").style.display="none"
	if (d == "1") {
		
		if (l == "1") {
			if(controlacceso("INSERTARLISTADOPRODUCTOS","accion")==false){return;}
			limpiarcamposproducto()
			limpiarcamposDetallePrecio()
		}else{
			buscardetallesprecio()
			limpiarcamposDetallePrecio()
		}
		$("div[id=divAbmProducto2]").fadeIn(250)
		document.getElementById('divAbmProducto1').style.display = "none"
	} else {
		$("div[id=divAbmProducto1]").fadeIn(250)
		document.getElementById('divAbmProducto2').style.display = "none"
	}
}
function verVentanaEditarProducto() {
	if(controlacceso("EDITARLISTADOPRODUCTOS","accion")==false){return;}
	if (idAbmProducto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmProducto("1", "2")
}
var idAbmProducto = ""
var idFkProductoCategoria= ""
var idFkProductoMarca = ""
var idFkProductoTipoImpuesto = ""
var codProveedorAbmProducto = ""
function obtenerdatosabmProducto(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptCodProducto').value = $(datostr).children('td[id="td_id"]').html();
	document.getElementById('inptRegistroSeleccProducto').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNombreProducto').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptPrecioCompraProducto').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptPrecioVentaProducto').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptStockProducto').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptComisionProducto').value = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptUnidadProducto').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptEstadoProducto').value = $(datostr).children('td[id="td_datos_9"]').html();
	document.getElementById('inptDescripProducto').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptlocalProducto').value = $(datostr).children('td[id="td_datos_10"]').html();

	document.getElementById('inptTipoImpuestoProducto').value = $(datostr).children('td[id="td_datos_12"]').html();
	document.getElementById('inptMarcaProducto').value = $(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('inptCategoriaProducto').value = $(datostr).children('td[id="td_datos_11"]').html();
	document.getElementById('inptPorcVentaProducto').value = $(datostr).children('td[id="td_datos_17"]').html();
	document.getElementById('inptTotalInversionProducto').value = $(datostr).children('td[id="td_datos_18"]').html();
	document.getElementById('inptCodBarraProducto').value = $(datostr).children('td[id="td_datos_19"]').html();
	document.getElementById('inptTipoProducto').value = $(datostr).children('td[id="td_datos_20"]').html();
	document.getElementById('inptProveesorProducto').value = $(datostr).children('td[id="td_datos_22"]').html();
		document.getElementById('inptUsuarioInsertadoPor').value=$(datostr).children('td[id="td_datos_100"]').html()
	document.getElementById('inptFechaInsertadoPor').value=$(datostr).children('td[id="td_datos_102"]').html()
	document.getElementById('inptUsuarioEditadoPor').value=$(datostr).children('td[id="td_datos_101"]').html()
	document.getElementById('inptFechaEditadoPor').value=$(datostr).children('td[id="td_datos_103"]').html()
	idAbmProducto = $(datostr).children('td[id="td_id"]').html();
	idFkProductoCategoria = $(datostr).children('td[id="td_datos_14"]').html();
	idFkProductoMarca = $(datostr).children('td[id="td_datos_15"]').html();
	idFkProductoTipoImpuesto = $(datostr).children('td[id="td_datos_16"]').html();
	codProveedorAbmProducto = $(datostr).children('td[id="td_datos_23"]').html();
		document.getElementById('btnAbmProducto').value ="Editar Datos";
			document.getElementById("tdAnhaMasPrecios").style.display=""
				document.getElementById("btnVerConfigPrecios").style.backgroundColor=""
				document.getElementById("btnEditarProductos").style.backgroundColor=""
				document.getElementById("btnEnviarProductos").style.backgroundColor=""
				document.getElementById('btnBajaDatosProductos').style.backgroundColor="#f53b3b";
				document.getElementById('btnAuditoriaProducto').style.backgroundColor="#673ab7";
				// document.getElementById("tdTotalStockProducto").style.display=""
		// document.getElementById("tdTotalInversion").style.display=""
		// document.getElementById("tdLocalProducto").style.display=""
				buscardetallesprecioenbuscarproductos()
				
}
function calcularGananciaDesdePorcentaje(d){
	var montocompra=document.getElementById("inptPrecioCompraProducto").value
	var porcentaje=document.getElementById("inptPorcVentaProducto").value
	montocompra=QuitarSeparadorMilValor(montocompra)
	porcentaje=QuitarSeparadorMilValor(porcentaje)
	if (isNaN(montocompra)) {
		ver_vetana_informativa("FALTO INGRESAR UN MONTO DE COMPRA")
        return false;
	}
	if (isNaN(porcentaje)) {
		ver_vetana_informativa("FALTO INGRESAR EL PORCENTAJE DE GANANCIA")
		return false;
	}
	var total=Math.round((Number(porcentaje)*Number(montocompra))/100)
	total=Number(total)+Number(montocompra)
	document.getElementById("inptPrecioVentaProducto").value=separadordemilesnumero(total)	
}
function calcularPorcentajeDesdeGanancia(d){
	var montocompra=document.getElementById("inptPrecioCompraProducto").value
	var precioventa=document.getElementById("inptPrecioVentaProducto").value
	montocompra=QuitarSeparadorMilValor(montocompra)
	precioventa=QuitarSeparadorMilValor(precioventa)
	if (isNaN(montocompra)) {
		ver_vetana_informativa("FALTO INGRESAR UN MONTO DE COMPRA")
        return false;
	}
	if (isNaN(precioventa)) {
		ver_vetana_informativa("FALTO INGRESAR EL PRECIO VENTA")
		return false;
	}
	var ganancias=Math.round(Number(precioventa)-Number(montocompra))
	var porcentaje=((Number(ganancias)*100)/ Number(montocompra)).toFixed(1)
	porcentaje=porcentaje.replace('.',',')
	document.getElementById("inptPrecioVentaProducto").value=separadordemilesnumero(precioventa)
	document.getElementById("inptPorcVentaProducto").value=porcentaje
}
function CalcularTotalInversion(){
	var montocompra=document.getElementById("inptPrecioCompraProducto").value
	var stock=document.getElementById("inptStockProducto").value
	montocompra=QuitarSeparadorMilValor(montocompra)
	stock=QuitarSeparadorMilValor(stock)
	if (isNaN(montocompra)) {
        montocompra=0;
	}
	if (isNaN(stock)) {
		stock=0;
	}
	var total=Math.round(Number(stock)*Number(montocompra))
	document.getElementById("inptTotalInversionProducto").value=separadordemilesnumero(total)
	document.getElementById("inptPrecioCompraProducto").value=separadordemilesnumero(montocompra)
}
function verificarcamposProducto() {
	var inptCodProducto = document.getElementById('inptCodProducto').value
	var inptCodBarraProducto = document.getElementById('inptCodBarraProducto').value
	var inptNombreProducto = document.getElementById('inptNombreProducto').value
	var inptDescripProducto = document.getElementById('inptDescripProducto').value
	var inptPrecioCompraProducto = document.getElementById('inptPrecioCompraProducto').value
	var inptPrecioVentaProducto = document.getElementById('inptPrecioVentaProducto').value
	var inptStockProducto = document.getElementById('inptStockProducto').value
	var inptComisionProducto = document.getElementById('inptComisionProducto').value
	var inptlocalProducto = document.getElementById('inptlocalProducto').value
	var inptUnidadProducto = document.getElementById('inptUnidadProducto').value
	var inptEstadoProducto = document.getElementById('inptEstadoProducto').value
	var inptTipoProducto = document.getElementById('inptTipoProducto').value
    var porcentaje=document.getElementById("inptPorcVentaProducto").value	
	if (inptCodBarraProducto == "") {
		inptCodBarraProducto="0000"
		return false;
	}	
	if (inptNombreProducto == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL PRODUCTO", "#")
		return false;
	}
	if (idFkProductoCategoria == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA CATEGORIA DEL PRODUCTO", "#")
		return false;
	}
	if (idFkProductoMarca == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA MARCAR DEL PRODUCTO", "#")
		return false;
	}
	if (idFkProductoTipoImpuesto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL TIPO DEL PRODUCTO", "#")
		return false;
	}
	if (inptEstadoProducto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL ESTADO", "#")
		return false;
	}
	var accion = "";
	if (idAbmProducto != "") {
		accion = "editar";
		if(controlacceso("EDITARLISTADOPRODUCTOS","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("INSERTARLISTADOPRODUCTOS","accion")==false){return;}
	}

	abmproducto(codProveedorAbmProducto,inptTipoProducto,inptCodBarraProducto,porcentaje,idFkProductoCategoria,idFkProductoMarca,idFkProductoTipoImpuesto,inptCodProducto, inptNombreProducto, inptDescripProducto, inptPrecioCompraProducto, inptPrecioVentaProducto, inptStockProducto, inptComisionProducto, inptlocalProducto, inptUnidadProducto, inptEstadoProducto, idAbmProducto, accion);
}
function abmproducto(CodProveedorFK,tipoproducto,codBarras,porcentaje,cod_categoriaFK,cod_marcasFK,cod_ImpuestoFK,cod_producto, nombre_producto, descripcion_producto, precio_compra, precio_producto, stock_producto, comision, cod_localFK, unidad_producto, estado, idProducto, accion) {
	
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_producto", cod_producto)
	datos.append("nombre_producto", nombre_producto)
	datos.append("descripcion_producto", descripcion_producto)
	datos.append("unidad_producto", unidad_producto)
	datos.append("precio_producto", precio_producto)
	datos.append("precio_compra", precio_compra)
	datos.append("cod_localFK", cod_localFK)
	datos.append("comision", comision)
	datos.append("estado", estado)
	datos.append("stock_producto", stock_producto)
	datos.append("cod_categoriaFK", cod_categoriaFK)
	datos.append("cod_marcasFK", cod_marcasFK)
	datos.append("cod_ImpuestoFK", cod_ImpuestoFK)
	datos.append("porcentaje", porcentaje)
	datos.append("codBarras", codBarras)
	datos.append("tipoproducto", tipoproducto)
	datos.append("CodProveedorFK", CodProveedorFK)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmProducto =  datos["2"]
					document.getElementById('inptCodProducto').value=idAbmProducto;
					document.getElementById("btnAbmProducto").value='Editar Datos'
						document.getElementById("tdAnhaMasPrecios").style.display=""
						// document.getElementById("tdTotalStockProducto").style.display=""
		// document.getElementById("tdTotalInversion").style.display=""
		//document.getElementById("tdLocalProducto").style.display=""
				}
				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function checkestadoproductos(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarProducto1').checked=true
		document.getElementById('inptSeleccEstadoBuscarProducto2').checked=false	
	}else{
		
		document.getElementById('inptSeleccEstadoBuscarProducto1').checked=false
		document.getElementById('inptSeleccEstadoBuscarProducto2').checked=true
	}
}
var registrocargadoproductos="";
var totalregistroproductos="";
var controldebusquedadProductos=false
function cancelarCargaProducto(){
	controldebusquedadProductos=false
	document.getElementById("divProgressProducto").style.backgroundColor='#ff5722'
}
function buscarabmproducto() {
if(controlacceso("BUSCARLISTADOPRODUCTOS","accion")==false){return;}
	var codigo = document.getElementById('inptBuscarProducto1').value
	var producto = document.getElementById('inptBuscarProducto2').value
	var marca = document.getElementById('inptBuscarProducto3').value
	var categoria = document.getElementById('inptBuscarProducto4').value
	var stock = document.getElementById('inptBuscarProducto5').value
	var proveedor = document.getElementById('inptBuscarProducto6').value
	var estado = ""
	var local = document.getElementById('inptBuscarProducto7').value
	
	if(document.getElementById('inptSeleccEstadoBuscarProducto1').checked==true){
		estado = "Activo"
	}else{
		estado = "Inactivo"
	}
	if(controldebusquedadProductos==true){
	ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
    }
	controldebusquedadProductos=true
	document.getElementById("inptRegistoCargadoProducto").value = "";
	document.getElementById("table_abm_producto").innerHTML = paginacargando
    document.getElementById("tbProcessProducto").style.display="none"
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codigo": codigo,
		"producto": producto,
		"marca": marca,
		"categoria": categoria,
		"stock": stock,
		"proveedor": proveedor,
		"estado": estado,
		"local": local,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_abm_producto").innerHTML = ''
			controldebusquedadProductos=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_producto").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				
					document.getElementById("table_abm_producto").innerHTML = datos_buscados
					document.getElementById("inptRegistoCargadoProducto").value = datos[3];
						registrocargadoproductos=Number(datos[99]);
					totalregistroproductos=Number(datos[100]);
			
					
						 if(totalregistroproductos>registrocargadoproductos){
						 	var porce=((registrocargadoproductos*100)/totalregistroproductos).toFixed(0)
	document.getElementById("divProgressProducto").style.width=porce+"%"
						 document.getElementById("table_abm_producto").innerHTML += "<div id='table_abm_mas_producto'></div>"
						  buscarabmmasproducto();
					 }else{
						 controldebusquedadProductos=false
					 }
					
				}
			} catch (error) {
				controldebusquedadProductos=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
	var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function buscarabmmasproducto(c) {
if(controlacceso("BUSCARLISTADOPRODUCTOS","accion")==false){return;}
	var codigo = document.getElementById('inptBuscarProducto1').value
	var producto = document.getElementById('inptBuscarProducto2').value
	var marca = document.getElementById('inptBuscarProducto3').value
	var categoria = document.getElementById('inptBuscarProducto4').value
	var stock = document.getElementById('inptBuscarProducto5').value
	var proveedor = document.getElementById('inptBuscarProducto6').value
	var estado = ""
	var local = document.getElementById('inptBuscarProducto7').value
	
	if(document.getElementById('inptSeleccEstadoBuscarProducto1').checked==true){
		estado = "Activo"
	}else{
		estado = "Inactivo"
	}
	if(c=="1"){
		controldebusquedadProductos=true
	}
	if(controldebusquedadProductos==false){
	
	return
    }
	controldebusquedadProductos=true
	
	document.getElementById("table_abm_mas_producto").innerHTML = paginacargando
    document.getElementById("tbProcessProducto").style.display=""
	document.getElementById("divProgressProducto").style.backgroundColor=''
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codigo": codigo,
		"producto": producto,
		"marca": marca,
		"categoria": categoria,
		"stock": stock,
		"proveedor": proveedor,
		"estado": estado,
		"local": local,
		"registrocargado": registrocargadoproductos,
		"funt": "buscarmas"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_abm_mas_producto").innerHTML = ''
			controldebusquedadProductos=false
			document.getElementById("divProgressProducto").style.backgroundColor='#ff5722'
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_mas_producto").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				
					document.getElementById("table_abm_mas_producto").innerHTML = datos_buscados
					document.getElementById("inptRegistoCargadoProducto").value = datos[3];
						registrocargadoproductos=Number(datos[99]);
					
			
					
						 if(totalregistroproductos>registrocargadoproductos){
						 	var porce=((registrocargadoproductos*100)/totalregistroproductos).toFixed(0)
	document.getElementById("divProgressProducto").style.width=porce+"%"
						 document.getElementById("table_abm_mas_producto").innerHTML += "<div id='table_abm_mas_producto'></div>"
						 document.getElementById("table_abm_mas_producto").id=""
						  buscarabmmasproducto();
					 }else{
						  document.getElementById("tbProcessProducto").style.display="none"
						 controldebusquedadProductos=false
					 }
					
				}
			} catch (error) {
				document.getElementById("divProgressProducto").style.backgroundColor='#ff5722'
				controldebusquedadProductos=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
	var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function limpiarcamposproducto() {    
	document.getElementById('inptCodProducto').value = "";
	document.getElementById('inptRegistroSeleccProducto').value = "";
	document.getElementById('inptNombreProducto').value = "";
	document.getElementById('inptPrecioCompraProducto').value = "";
	document.getElementById('inptPrecioVentaProducto').value = "";
	document.getElementById('inptTotalInversionProducto').value = "";
	document.getElementById('inptStockProducto').value = "";
	document.getElementById('inptComisionProducto').value = "";
	document.getElementById('inptDescripProducto').value = "";
	document.getElementById('inptTipoImpuestoProducto').value = "GRAVADAS 10%";
	document.getElementById('inptMarcaProducto').value = "";
	document.getElementById('inptPorcVentaProducto').value = "";
	document.getElementById('inptCategoriaProducto').value = "";
	document.getElementById('inptCodBarraProducto').value = "";
	document.getElementById('inptProveesorProducto').value = "";
	
	if(document.getElementById("inptBuscarProducto7").value==""){
		document.getElementById('inptlocalProducto').value=cod_localFKUSer
	}else{
		
		document.getElementById('inptlocalProducto').value=document.getElementById("inptBuscarProducto7").value
		
	}
	 
	document.getElementById('table_abm_producto_detalles_precios').innerHTML = "";
	document.getElementById('inptEstadoProducto').value = "Activo";
	document.getElementById('btnAbmProducto').value ="Guardar Datos";
		document.getElementById("tdAnhaMasPrecios").style.display="none"
		// document.getElementById("tdTotalStockProducto").style.display="none"
		// document.getElementById("tdTotalInversion").style.display="none"
		// document.getElementById("tdLocalProducto").style.display="none"
		document.getElementById("btnVerConfigPrecios").style.backgroundColor="#b7b7b7"
		document.getElementById("btnEditarProductos").style.backgroundColor="#b7b7b7"
		document.getElementById("btnEnviarProductos").style.backgroundColor="#b7b7b7"
		document.getElementById("btnAuditoriaProducto").style.backgroundColor="#b7b7b7"
		document.getElementById("btnBajaDatosProductos").style.backgroundColor="#b7b7b7"
		
	idAbmProducto = "";
	 idFkProductoCategoria= ""
 idFkProductoMarca = ""
 idFkProductoTipoImpuesto = "3"
 codProveedorAbmProducto = ""
seleccionarLocalUSer()
limpiarcamposDetallePrecio()
buscardetallesprecio()
}
function limpiarcamposbuscarproductos(){
		if(controldebusquedadProductos==true){
	
	return
    }
	document.getElementById('inptBuscarProducto1').value=""
	 document.getElementById('inptBuscarProducto2').value=""
	 document.getElementById('inptBuscarProducto3').value=""
	document.getElementById('inptBuscarProducto4').value=""
	document.getElementById('inptBuscarProducto5').value=""
	document.getElementById('inptBuscarProducto6').value=""
	
	document.getElementById('inptRegistoCargadoProducto').value=""
	document.getElementById('inptRegistroSeleccProducto').value=""
	document.getElementById('tbProcessProducto').style.display="none"
	
	document.getElementById('table_abm_producto').innerHTML=""
	document.getElementById('table_abm_producto_detalles_precios').innerHTML=""

}
var idFkProducto = ""
var idFkProductocompra = ""
function vercerrarvistaproducto(d, ventana) {
	if (d == "1") {
		document.getElementById("divVistaProducto").style.display=""
 document.getElementById("tdEfectoVistaProductos").className="magictime vanishIn"
		controlseleccvistaproducto = ventana
		
	} else {
document.getElementById("tdEfectoVistaProductos").className="magictime vanishOut"
		$("div[id=divVistaProducto]").fadeOut(500)
	}
}
function buscarvistaproducto() {
	var buscador = document.getElementById('inptBuscarVistaProducto').value
	var local = document.getElementById('inptlocalProductoBuscarVista').value
	var Categoria = document.getElementById('inptCategoriaProductoBuscarVista').value
	var Marca = document.getElementById('inptMarcaProductoBuscarVista').value
	var codProveedor="";
	if (controlseleccvistaproducto == "compra"){
		codProveedor=codProveedorCompra
	}
	
	document.getElementById("table_abm_vista_producto").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"local": local,
		"Categoria": Categoria,
		"Marca": Marca,
		"codProveedor": codProveedor,
		"funt": "buscarvista"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_abm_vista_producto").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_vista_producto").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				document.getElementById("table_abm_vista_producto").innerHTML = datos_buscados
				
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarvistaproductodesdeventa() {
	var buscador = document.getElementById('inptProductoVenta').value
	var local = document.getElementById("inptlocalVenta").value;
	document.getElementById("table_vista_producto_venta").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"local": local,
		"funt": "buscarvistaventa"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_vista_producto_venta").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_producto_venta").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				if(datos_buscados!=""){
				document.getElementById("table_vista_producto_venta").innerHTML = datos_buscados
				if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
			  $("td[id=td_datos_precio_contado]").each(function(i, elementohtml){  
	         elementohtml.style.display="none"
	          });
	      $("td[id=td_datos_precios_creditos]").each(function(i, elementohtml){
              elementohtml.style.display=""
	     });  
	   
	}else{
		
	  $("td[id=td_datos_precio_contado]").each(function(i, elementohtml){  
	   elementohtml.style.display=""
	   });
	$("td[id=td_datos_precios_creditos]").each(function(i, elementohtml){
elementohtml.style.display="none"
	   });
	}
	
				
					document.getElementById('btnfocusProducto').focus();
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
function buscarvistaproductodesdecompra() {
	var buscador = document.getElementById('inptProductoCompra').value
	var local = document.getElementById('inptlocalCompra').value
	
	document.getElementById("table_vista_producto_compra").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"local": local,
		"funt": "buscarvistacompras"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_vista_producto_compra").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_producto_compra").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				
				
				if(datos_buscados!=""){
					document.getElementById("table_vista_producto_compra").innerHTML = datos_buscados
				document.getElementById('btnfocusProductocompra').focus();
				
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
function buscarvistaproductodesdelistadospacho() {
	var buscador = document.getElementById('inptProductoListadoDespachar').value
	var local = document.getElementById("inptLocalProductoListadoDespachar1").value;
	
	document.getElementById("table_vista_producto_despacho").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"local": local,
		"funt": "buscarvistalistadodespacho"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_vista_producto_despacho").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_producto_despacho").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				if(datos_buscados!=""){
				document.getElementById("table_vista_producto_despacho").innerHTML = datos_buscados
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

function buscarvistaproductodesdesalidadeposito() {
	var buscador = document.getElementById('inptProductoSalidadDeposito').value
	var local = document.getElementById("inptLocalProductoSalidadDeposito1").value;
	
	document.getElementById("table_abm_listado_salida_deposito").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"local": local,
		"funt": "buscarvistasalidadeposito"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_abm_listado_salida_deposito").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_listado_salida_deposito").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				if(datos_buscados!=""){
				document.getElementById("table_abm_listado_salida_deposito").innerHTML = datos_buscados
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
var codProductoFkSalidaDeposito="";
var codigoProductoSalidadDeposito="";
function obtenerdatosvistaproductodesdeSalidadDeposito(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	codProductoFkSalidaDeposito = $(datostr).children('td[id="td_id"]').html();
	codigoProductoSalidadDeposito = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoSalidadDeposito').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptCantProductoListadoDespachar').value = "";
		document.getElementById('btnGuardarSalidaDeposito').style = "";
		
}


function recorrerFocusTableProductoCompra(datos){

	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	var id=$(datos).attr("name")
	var total=$(datos).attr("class")
	var control=$(datos).attr("value")
	$("tr[name=trVistaProducto_"+id+"]").attr("class","tableRegistroSelec")
	
	
}
function obtenerdatosvistaproductodesdecompra(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
ElementoProductoVista=datostr	
idFkProductocompra = $(datostr).children('td[id="td_id"]').html();
	
		document.getElementById('inptCodProductoCompra').value = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoCompra').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptCantProductoCompra').value = "0";
		document.getElementById('inptCostoProductoCompra').value = $(datostr).children('td[id="td_datos_5"]').html();
		document.getElementById('inptPorcVentaProductoContadoCompra').value = $(datostr).children('td[id="td_datos_14"]').html();
		document.getElementById('inptPrecioVentaProductoContadoCompra').value = $(datostr).children('td[id="td_datos_4"]').html();
		document.getElementById('inptCantProductoCompra').focus();
		document.getElementById("btneditarproductocompras").style.backgroundColor="#FF5722";
		document.getElementById("btneditarprecioscompras").style.backgroundColor="rgb(14 14 14)";
		document.getElementById("btnAddDetalleCompra").style.backgroundColor="#2196F3";
		
		
}
function recorrerFocusTableProductoVenta(datos){

	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	var id=$(datos).attr("name")
	var total=$(datos).attr("class")
	var control=$(datos).attr("value")
	$("tr[name=trVistaProducto_"+id+"]").attr("class","tableRegistroSelec")
	
	
}
var preciocostocontado="";
var preciocostocredito="";
var StockVenta="";
function obtenerdatosvistaproductodesdeventa(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
ElementoProductoVista=datostr	
var controlstock = $(datostr).children('td[id="td_datos_15"]').html();
StockVenta = $(datostr).children('td[id="td_datos_15"]').html();
		
		if( accesosuser["STOCK0"]["accion"]!="SI")
	{
			if(controlstock<=0){
				ver_vetana_informativa("NO PUEDES VENDER PRODUCTOS CON STOCK MENOR A 0")
				return
			}
	}
		idFkProducto = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptCodProductoVenta').value = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoVenta').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inpTSeleccCosto').innerHTML = $(datostr).children('td[id="td_datos_11"]').html();
		document.getElementById('inptCantProductoVenta').value = "1";
		document.getElementById('inptCostoProductoVenta').value = $(datostr).children('td[id="td_datos_4"]').html();
		preciocostocontado= $(datostr).children('td[id="td_datos_4"]').html();
		preciocostocontado=QuitarSeparadorMilValor(preciocostocontado)
		preciocostocredito=QuitarSeparadorMilValor(preciocostocontado)
		document.getElementById('inptDescuentoProductoVenta').value = "0";
		document.getElementById('inptComisionVenta').value = "0";
		document.getElementById('inptObservacionDetalleVenta').value = "Contado";
		if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
		document.getElementById('inptObservacionDetalleVenta').value = "Credito";
		document.getElementById("inptCostoProductoVenta").value= $("#inpTSeleccCosto option:first").val();
		preciocostocredito=QuitarSeparadorMilValor(document.getElementById("inptCostoProductoVenta").value)
		}
		document.getElementById('btnAddDetallesaVenta').style.backgroundColor = "#2196F3";
		document.getElementById('inptCantProductoVenta').focus();
		calcularTotalVentasCosto(document.getElementById('inptCostoProductoVenta'))
		buscardetallespreciodesdevista("vistaventa");
}
var controlseleccvistaproducto = ""
var ElementoProductoVista = ""
function obtenerdatosvistaproducto(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
ElementoProductoVista=datostr	
		idFkProducto = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptProductoNombreVista').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptProductoCategoriaVista').value = $(datostr).children('td[id="td_datos_12"]').html();
		document.getElementById('inptProductoStockVista').value = $(datostr).children('td[id="td_datos_6"]').html();
		document.getElementById('inptProductoPrecioContadoVista').value = $(datostr).children('td[id="td_datos_4"]').html();
		buscardetallespreciodesdevista("vista") 
}
function EnviarProductoDesde() {    
	if(ElementoProductoVista==""){
	ver_vetana_informativa("FALTO SELECCIONAR EL PRODUCTO")
	return false;
	}
    datostr=ElementoProductoVista
	if (controlseleccvistaproducto == "venta") {
		
		
		var controlstock = $(datostr).children('td[id="td_datos_15"]').html();
		
		if( accesosuser["STOCK0"]["accion"]!="SI")
	{
	if(controlstock<=0){
			ver_vetana_informativa("NO PUEDES VENDER PRODUCTOS CON STOCK MENOR A 0")
		return
	}
	}
		idFkProducto = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptCodProductoVenta').value = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoVenta').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptCostoProductoVenta').value = $(datostr).children('td[id="td_datos_4"]').html();
		preciocostocontado= $(datostr).children('td[id="td_datos_4"]').html();
		preciocostocontado=QuitarSeparadorMilValor(preciocostocontado)
		preciocostocredito=QuitarSeparadorMilValor(preciocostocontado)
		document.getElementById('inpTSeleccCosto').innerHTML = $(datostr).children('td[id="td_datos_11"]').html();
		document.getElementById('inpTotalCostoVenta').value = $(datostr).children('td[id="td_datos_4"]').html();
		document.getElementById('inptCantProductoVenta').value = "1";
		document.getElementById('inptDescuentoProductoVenta').value = "0";
		document.getElementById('inptObservacionDetalleVenta').value = "Contado";
		document.getElementById('inptDetallesVentaProductos').value = "";
		document.getElementById('btnAddDetallesaVenta').style.backgroundColor = "#2196F3";
		document.getElementById("inptComisionVenta").value = $(datostr).children('td[id="td_datos_8"]').html();
		if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
		document.getElementById('inptObservacionDetalleVenta').value = "";
		document.getElementById("inptCostoProductoVenta").value= $("#inpTSeleccCosto option:first").val();
		preciocostocredito=QuitarSeparadorMilValor(document.getElementById("inptCostoProductoVenta").value)
		}
	}
	
	if (controlseleccvistaproducto == "compra") {
		idFkProductocompra = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptProductoCompra').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptCostoProductoCompra').value = $(datostr).children('td[id="td_datos_5"]').html();
		document.getElementById('inptPorcVentaProductoContadoCompra').value = $(datostr).children('td[id="td_datos_14"]').html();
		document.getElementById('inptPrecioVentaProductoContadoCompra').value = $(datostr).children('td[id="td_datos_4"]').html();
		document.getElementById('inptCantProductoVenta').value = "";
		document.getElementById("btneditarproductocompras").style.backgroundColor="#FF5722";
		document.getElementById("btneditarprecioscompras").style.backgroundColor="rgb(14 14 14)";
		document.getElementById("btnAddDetalleCompra").style.backgroundColor="#2196F3";
	}
	if (controlseleccvistaproducto == "cambiodevolucion") {
		limpiarCamposProductosCambios()
		idFkProductocompraCambio = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptProductoSeleccCambio').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptCostoCambio').value = $(datostr).children('td[id="td_datos_4"]').html();
		document.getElementById('inpTotalCostoCambio').value = $(datostr).children('td[id="td_datos_4"]').html();
		document.getElementById('inpTSeleccCostoCambio').innerHTML = $(datostr).children('td[id="td_datos_11"]').html();
		document.getElementById('inptCantCambio').value = "1";
		document.getElementById('inptDescuentoCambio').value = "0";
       document.getElementById('inptObservacionCambio').value = "Contado";
	}
	
	if (controlseleccvistaproducto == "solicitud1") {
		codProductoSolicitud1 = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptSeleccProductoSolicitud1').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById("inptlocalSolicitud1").value= $(datostr).children('td[id="td_datos_7"]').html();
	}
	if (controlseleccvistaproducto == "solicitud2") {
		codProductoSolicitud2 = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptSeleccProductoSolicitud2').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById("inptlocalSolicitud2").value= $(datostr).children('td[id="td_datos_7"]').html();
	}
	document.getElementById("divVistaProducto").style.display = "none"
	document.getElementById("table_abm_vista_producto").innerHTML = ""
	document.getElementById("table_abm_vista_precios_producto").innerHTML = ""
	document.getElementById("inptProductoNombreVista").value = ""
	document.getElementById("inptProductoCategoriaVista").value = ""
	document.getElementById("inptProductoPrecioContadoVista").value = ""
	document.getElementById("inptProductoStockVista").value = ""
	document.getElementById("inptBuscarVistaProducto").value = ""
}
var codProveedorFkCompra="";
function EditarProductodesdecompra() {
	if(idFkProductocompra==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO ")
		return
	}
	
	verCerrarEfectoCargando("1")
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idFkProductocompra,
		"funt": "buscarporcodigoeditar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			verCerrarEfectoCargando("2")
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			verCerrarEfectoCargando("2")
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
					 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_vista_producto").innerHTML = datos_buscados
					obtenerdatosabmProducto($("tr[id=tbRegistroCodProducto]"))
					document.getElementById("divAbmProducto").style.display=""
					document.getElementById("imgCerrarProducto").style.display=""
					document.getElementById("imgMinimizaeProducto").style.display="none"
					document.getElementById('divAbmProducto1').style.display = "none"
	document.getElementById('divAbmProducto2').style.display = ""
	document.getElementById("tdEfectoAbmProductos").className="magictime slideDownReturn"
	document.getElementById('inptlocalProducto').value =document.getElementById("inptlocalCompra").value
	buscardetallesprecio()
	
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarproductoporcodigo(d) {
	if(d=="venta"){
		var buscador = document.getElementById('inptCodProductoVenta').value
		var local = document.getElementById("inptlocalVenta").value;
		document.getElementById('btnAddDetallesaVenta').style.backgroundColor = "#b7b7b7";
	}
	if(d=="compra"){
		var buscador = document.getElementById('inptCodProductoCompra').value
		var local = document.getElementById("inptlocalCompra").value;
	}	
	if(buscador==""){
		return
	}
	verCerrarEfectoCargando("1")
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"local": cod_localFKUSer,
		"funt": "buscarporcodigo"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			verCerrarEfectoCargando("2")
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			verCerrarEfectoCargando("2")
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				
					 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					datos_buscados = datos["2"];
					if(datos_buscados==""){
						ver_vetana_informativa("PRODUCTO NO ENCONTRADO")
						return false;
					}
					if(d=="venta"){
	            	controlseleccvistaproducto="venta"
					document.getElementById("table_abm_vista_producto").innerHTML = datos_buscados
					obtenerdatosvistaproducto($("tr[name=trVistaProducto_"+buscador+"]"))
					EnviarProductoDesde()
					
					document.getElementById('inptCantProductoVenta').focus();
	                }
					if(d=="compra"){
	            	controlseleccvistaproducto="compra"
			document.getElementById("table_abm_vista_producto").innerHTML = datos_buscados
					obtenerdatosvistaproducto($("tr[name=trVistaProducto_"+buscador+"]"))
					EnviarProductoDesde()
					
					document.getElementById('inptCantProductoCompra').focus();
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
var codProductoFkListadoDespacho="";
var codigoProductolistadoDespacho="";
function obtenerdatosvistaproductodesdelistadodespacho(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	codProductoFkListadoDespacho = $(datostr).children('td[id="td_id"]').html();
	codigoProductolistadoDespacho = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptProductoListadoDespachar').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptCantProductoListadoDespachar').value = "";
		document.getElementById('btnGuardarListadoDespacho').style = "";
		
}

/*
ABM CATEGORIA
*/
var idAbmCategoria="";
var ElementoSeleccCategoria="";
var VentanaCategoria="";
function verCerrarFrmCategoria(d,v){
	if(d=="1"){
		$("div[id=divAbmCategoria]").fadeIn(500);
		VentanaCategoria=v;
		BuscarAbmCategoria()
	}else{
		$("div[id=divAbmCategoria]").fadeOut(500);
	}
}
function LimpiarCamposCategoria(){
	document.getElementById("inptNombreCategoria").value="";
	document.getElementById("inptEstadoCategoria").value="";
	document.getElementById("btnCategoria1").value="Guardar Datos"
	idAbmCategoria="";
	ElementoSeleccCategoria="";
}
function ObtenerdatosAbmCategoria(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});		
	ElementoSeleccCategoria=datostr
	datostr.className = 'tableRegistroSelec'
    document.getElementById("inptNombreCategoria").value = $(datostr).children('td[id="td_datos_1"]').html();
    document.getElementById("inptEstadoCategoria").value = $(datostr).children('td[id="td_datos_2"]').html();
	idAbmCategoria = $(datostr).children('td[id="td_id"]').html();
     document.getElementById("btnCategoria1").value="Editar Datos"
}
function SeleccionarRegistroCategoria(){
	if(ElementoSeleccCategoria==""){
		ver_vetana_informativa("Falto Seleccionar un registro")
		return;
	}
    if(VentanaCategoria=="abmproducto"){
	 document.getElementById("inptCategoriaProducto").value = $(ElementoSeleccCategoria).children('td[id="td_datos_1"]').html();
	 idFkProductoCategoria = $(ElementoSeleccCategoria).children('td[id="td_id"]').html();
	}	
	 document.getElementById("divAbmCategoria").style.display="none";
	 LimpiarCamposCategoria()
}
function VerificarDatosCategoria(){
	var inptNombreCategoria = document.getElementById("inptNombreCategoria").value
	var inptEstadoCategoria = document.getElementById("inptEstadoCategoria").value	
	if(inptNombreCategoria==""){
		document.getElementById("inptNombreCategoria").focus()
		ver_vetana_informativa("Falto Ingresar el nombre")
		return
	}
	if(inptEstadoCategoria==""){
		document.getElementById("inptEstadoCategoria").focus()
		ver_vetana_informativa("Falto seleccionar el estado del registro")
		return
	}	
	var accion = "";
	if (idAbmCategoria != "") {		
		accion = "editar";
	} else {		
		accion = "nuevo";
	}
	AbmCategoria(inptNombreCategoria,inptEstadoCategoria,idAbmCategoria,accion)
}
function AbmCategoria(descripcion,Estado,idabm,accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idabm", idabm)
	datos.append("descripcion", descripcion)
	datos.append("Estado", Estado)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/ABMCategoria.php",
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
				LimpiarCamposCategoria()
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
				BuscarAbmCategoria()
				BuscarSelecCategoria()
				}
				else {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function BuscarAbmCategoria() {
	var buscador = document.getElementById("inptBuscarAbmCategorias").value
	var estado = "Activo"
	document.getElementById("divBuscadorCategoria").innerHTML = paginacargando
    document.getElementById("lblNroRegistroCategoria").innerHTML="";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"estado": estado,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
        url: "/GoodVentaByR/php_system/ABMCategoria.php",
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
			document.getElementById("divBuscadorCategoria").innerHTML = ''
			document.getElementById("lblNroRegistroCategoria").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorCategoria").innerHTML = ''
			document.getElementById("lblNroRegistroCategoria").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("divBuscadorCategoria").innerHTML = datos_buscados
                   document.getElementById("lblNroRegistroCategoria").innerHTML="Se encontraron "+datos[3]+" registro(s)";
				   
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function BuscarSelecCategoria() {
	document.getElementById("inptCategoriaProductoBuscarInventario").innerHTML = paginacargando
	document.getElementById("inptCategoriaProductoInformeProductosVendidos").innerHTML = paginacargando
	document.getElementById("inptCategoriaProductoBuscarVista").innerHTML = paginacargando
	document.getElementById("inptBuscarProducto4").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscarOption"
	};
	$.ajax({
		data: datos,
        url: "/GoodVentaByR/php_system/ABMCategoria.php",
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
			document.getElementById("inptCategoriaProductoBuscarInventario").innerHTML = ''
			document.getElementById("inptCategoriaProductoInformeProductosVendidos").innerHTML = ''
			document.getElementById("inptCategoriaInformeProductosComprados").innerHTML = ''
			document.getElementById("inptCategoriaProductoBuscarVista").innerHTML = ''
			document.getElementById("inptBuscarProducto4").innerHTML = ''
						},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("inptCategoriaProductoBuscarInventario").innerHTML = ''
			document.getElementById("inptCategoriaProductoInformeProductosVendidos").innerHTML = ''
			document.getElementById("inptCategoriaInformeProductosComprados").innerHTML = ''
			document.getElementById("inptCategoriaProductoBuscarVista").innerHTML = ''
			document.getElementById("inptBuscarProducto4").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("inptCategoriaProductoBuscarInventario").innerHTML = datos_buscados
					document.getElementById("inptCategoriaProductoInformeProductosVendidos").innerHTML = datos_buscados
					document.getElementById("inptCategoriaInformeProductosComprados").innerHTML = datos_buscados
					document.getElementById("inptCategoriaProductoBuscarVista").innerHTML = datos_buscados
					document.getElementById("inptBuscarProducto4").innerHTML = datos_buscados
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
ABM MARCA
*/
var idAbmMarca="";
var ElementoSeleccMarca="";
var VentanaMarca="";
function verCerrarFrmMarca(d,v){
	if(d=="1"){
		$("div[id=divAbmMarca]").fadeIn(500);
		VentanaMarca=v;
		BuscarAbmMarca()
	}else{
		$("div[id=divAbmMarca]").fadeOut(500);
	}
}
function LimpiarCamposMarca(){
	document.getElementById("inptNombreMarca").value="";
	document.getElementById("inptEstadoMarca").value="";
	document.getElementById("btnMarca1").value="Guardar Datos"
	idAbmMarca="";
	ElementoSeleccMarca="";
}
function ObtenerdatosAbmMarca(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});		
	ElementoSeleccMarca=datostr
	datostr.className = 'tableRegistroSelec'
    document.getElementById("inptNombreMarca").value = $(datostr).children('td[id="td_datos_1"]').html();
    document.getElementById("inptEstadoMarca").value = $(datostr).children('td[id="td_datos_2"]').html();
	idAbmMarca = $(datostr).children('td[id="td_id"]').html();
     document.getElementById("btnMarca1").value="Editar Datos"
}
function SeleccionarRegistroMarca(){
	if(ElementoSeleccMarca==""){
		ver_vetana_informativa("Falto Seleccionar un registro")
		return;
	}
    if(VentanaMarca=="abmproducto"){
	 document.getElementById("inptMarcaProducto").value = $(ElementoSeleccMarca).children('td[id="td_datos_1"]').html();
	 idFkProductoMarca = $(ElementoSeleccMarca).children('td[id="td_id"]').html();
	}	
	 document.getElementById("divAbmMarca").style.display="none";
	 LimpiarCamposMarca()
}
function VerificarDatosMarca(){
	var inptNombreMarca = document.getElementById("inptNombreMarca").value
	var inptEstadoMarca = document.getElementById("inptEstadoMarca").value	
	if(inptNombreMarca==""){
		document.getElementById("inptNombreMarca").focus()
		ver_vetana_informativa("Falto Ingresar el nombre")
		return
	}
	if(inptEstadoMarca==""){
		document.getElementById("inptEstadoMarca").focus()
		ver_vetana_informativa("Falto seleccionar el estado del registro")
		return
	}	
	var accion = "";
	if (idAbmMarca != "") {		
		accion = "editar";
	} else {
		accion = "nuevo";
	}
	AbmMarca(inptNombreMarca,inptEstadoMarca,idAbmMarca,accion)
}
function AbmMarca(descripcion,Estado,idabm,accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idabm", idabm)
	datos.append("descripcion", descripcion)
	datos.append("Estado", Estado)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/ABMMarca.php",
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
					LimpiarCamposMarca()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					BuscarAbmMarca()
					BuscarSelectMarca() 
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function BuscarAbmMarca() {
	var buscador = document.getElementById("inptBuscarAbmMarcas").value
	var estado = "Activo"
	document.getElementById("divBuscadorMarca").innerHTML = paginacargando
    document.getElementById("lblNroRegistroMarca").innerHTML="";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"estado": estado,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
        url: "/GoodVentaByR/php_system/ABMMarca.php",
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
			document.getElementById("divBuscadorMarca").innerHTML = ''
			document.getElementById("lblNroRegistroMarca").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorMarca").innerHTML = ''
			document.getElementById("lblNroRegistroMarca").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("divBuscadorMarca").innerHTML = datos_buscados
                   document.getElementById("lblNroRegistroMarca").innerHTML="Se encontraron "+datos[3]+" registro(s)";
				   
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
	}
function BuscarSelectMarca() {
	document.getElementById("inptMarcasProductoBuscarInventario").innerHTML = paginacargando
	document.getElementById("inptMarcaProductoBuscarVista").innerHTML = paginacargando
	document.getElementById("inptMarcaInformeProductosVendidos").innerHTML = paginacargando
	document.getElementById("inptBuscarProducto3").innerHTML = paginacargando
	document.getElementById("inptMarcaInformeProductosComprados").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscarOption"
	};
	$.ajax({
		data: datos,
        url: "/GoodVentaByR/php_system/ABMMarca.php",
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
			document.getElementById("inptMarcasProductoBuscarInventario").innerHTML = ''
			document.getElementById("inptMarcaProductoBuscarVista").innerHTML = ''
			document.getElementById("inptMarcaInformeProductosVendidos").innerHTML = ''
			document.getElementById("inptBuscarProducto3").innerHTML = ''
			document.getElementById("inptMarcaInformeProductosComprados").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("inptMarcasProductoBuscarInventario").innerHTML = ''
			document.getElementById("inptMarcaProductoBuscarVista").innerHTML = ''
			document.getElementById("inptMarcaInformeProductosVendidos").innerHTML = ''
			document.getElementById("inptBuscarProducto3").innerHTML = ''
			document.getElementById("inptMarcaInformeProductosComprados").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("inptMarcasProductoBuscarInventario").innerHTML = datos_buscados
					document.getElementById("inptMarcaProductoBuscarVista").innerHTML = datos_buscados
					document.getElementById("inptMarcaInformeProductosVendidos").innerHTML = datos_buscados
					document.getElementById("inptBuscarProducto3").innerHTML = datos_buscados
					document.getElementById("inptMarcaInformeProductosComprados").innerHTML = datos_buscados

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
SALIDA DE PRODUCTOS DEPOSITO
*/
function verCerrarAbmSalidadDeposito(d){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmSalidadDeposito").style.display==""){
			
document.getElementById("tdEfectoAbmSalidadDeposito").className="magictime vanishOut"
	$("div[id=divAbmSalidadDeposito]").fadeOut(500);	
		document.getElementById("divMinimizadoSalidadDeposito").style.display="none"
		limpiarTodoListadoDespacharProductos()
	}else{	
if(controlacceso("VERCONTROLDEPOSITO","accion")==false){return;}
document.getElementById("divAbmSalidadDeposito").style.display=""
document.getElementById("tdEfectoAbmSalidadDeposito").className="magictime slideDownReturn"
		
	}
}
function minimizarabmsalidaddeposito(){
document.getElementById("tdEfectoAbmSalidadDeposito").className="magictime slideDown"
$("div[id=divAbmSalidadDeposito]").fadeOut(500);
document.getElementById("divMinimizadoSalidadDeposito").style.display=""		
}
function VerificarDatosSalidaDeposito(){
	var inptFechaSalidadDeposito = document.getElementById("inptFechaSalidadDeposito").value
	var inptCantProductoSalidadDeposito = document.getElementById("inptCantProductoSalidadDeposito").value
	var inptLocalProductoSalidadDeposito1=document.getElementById('inptLocalProductoSalidadDeposito1').value
	if(codProductoFkSalidaDeposito==""){
		ver_vetana_informativa("Falto seleccionar el producto")
		return
	}
	if(inptFechaSalidadDeposito==""){
		ver_vetana_informativa("Falto seleccionar la fecha")
		return
	}
	if(inptCantProductoSalidadDeposito==""){
		document.getElementById("inptCantProductoSalidadDeposito").focus()
		ver_vetana_informativa("Falto ingresar el stock")
		return
	}	
	if(inptLocalProductoSalidadDeposito1==""){
		document.getElementById("inptLocalProductoSalidadDeposito1").focus()
		ver_vetana_informativa("Falto seleccionar el local deposito")
		return
	}	
		
	AbmEnviarSalidaDeposito(inptFechaSalidadDeposito,inptCantProductoSalidadDeposito,inptLocalProductoSalidadDeposito1,"SalidaDeposito")
}
function AbmEnviarSalidaDeposito(fecha,stock,cod_local_deposito,accion) {
	verCerrarEfectoCargando("1")
	var codigo=stringGenerador(5)

	var codigo_table=codigo+"_"+codProductoFkSalidaDeposito
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("fecha", fecha)
	datos.append("stock", stock)
	datos.append("cod_local_deposito", cod_local_deposito)
	datos.append("cod_ext", codigo_table)
	datos.append("cod_producto_fk", codProductoFkSalidaDeposito)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
										ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")

	
                     var pagina="<table id='tdDetalleSalidaDeposito_"+codigo_table+"' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>"
+"<tr id='tbSelecRegistro' onclick='ObtenerdatosAbmSalidad(this)'  name='tdDetalleSalidaDepositoOffline'>"
+"<td id='td_id_1' style='display:none'>"+codigo_table+"</td>"
+"<td id='td_id_2' style='display:none'>"+codProductoFkSalidaDeposito+"</td>"
+"<td  id='td_datos_1' style='width:10%;'>"+codigoProductoSalidadDeposito+"</td>"
+"<td  id='td_datos_3' style='width:10%'>"+document.getElementById("inptProductoSalidadDeposito").value+"</td>"
+"<td  id='td_datos_4' style='width:5%'>"+stock+"</td>"
+"<td  id='td_datos_7' style='width:10%'>"+$("select[id=inptLocalProductoSalidadDeposito1]").children(":selected").text()+"</td>"
+"</tr>"
+"</table>"
document.getElementById("table_abm_SalidaDeposito").innerHTML+=pagina;

var control=0;
$("tr[name=tdDetalleSalidaDepositoOffline]").each(function(i, elementohtml){
control=control+1;
	   })	
	   document.getElementById("btnImprimirSalidaDeposito").style.display=""
	   document.getElementById("btnLimpiarCamposSalidaDepostio").style.display=""
document.getElementById("inptRegistroNroSalidadDeposito").value=control
	   
					limpiarCamposListadoSalidaDeposito()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function limpiarCamposListadoSalidaDeposito(){
codProductoFkSalidaDeposito=""	
codigoProductoSalidadDeposito=""	
document.getElementById("inptFechaSalidadDeposito").value=""
document.getElementById("inptCantProductoSalidadDeposito").value=""
document.getElementById("inptProductoSalidadDeposito").value=""
document.getElementById("inptRegistroSeleccSalidaDeposito").value=""
document.getElementById("btnGuardarSalidaDeposito").style.backgroundColor="#b7b7b7"
document.getElementById("btnAnularSalidadDeposito").style.backgroundColor="#b7b7b7"
}
function limpiarTodoListadoSalidaDeposito(){

codProductoFkSalidaDeposito=""	
codigoProductoSalidadDeposito=""	
document.getElementById("inptFechaSalidadDeposito").value=""
document.getElementById("inptProductoSalidadDeposito").value=""
document.getElementById("inptCantProductoSalidadDeposito").value=""
document.getElementById("btnGuardarSalidaDeposito").style.backgroundColor="#b7b7b7"
document.getElementById("btnAnularSalidadDeposito").style.backgroundColor="#b7b7b7"

document.getElementById("table_abm_SalidaDeposito").innerHTML=""
document.getElementById("inptRegistroSeleccSalidaDeposito").value=""
document.getElementById("inptRegistroNroSalidadDeposito").value=""
document.getElementById("btnImprimirSalidaDeposito").style.display="none"
document.getElementById("btnLimpiarCamposSalidaDepostio").style.display="none"
}
var elementoSalidadDeposito="";
function ObtenerdatosAbmSalidad(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});		
	elementoSalidadDeposito=datostr
	datostr.className = 'tableRegistroSelec'
    document.getElementById("inptRegistroSeleccSalidaDeposito").value = $(datostr).children('td[id="td_datos_3"]').html();
     document.getElementById("btnAnularSalidadDeposito").style=""
}
function AbmAnularSalidaDeposito() {
	verCerrarEfectoCargando("1")
	var cod_ext=$(elementoSalidadDeposito).children('td[id="td_id_1"]').html();
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "anularsalidaProducto")
	datos.append("cod_ext", cod_ext)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				   Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
										ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
           $("table[id=tdDetalleSalidaDeposito_"+cod_ext+"]").remove()    
var control=0;
$("tr[name=tdDetalleSalidaDepositoOffline]").each(function(i, elementohtml){
control=control+1;
	   })	

document.getElementById("inptRegistroNroSalidadDeposito").value=control
	   
					limpiarCamposListadoSalidaDeposito()
					
				}
				try {
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function ImprimirTicketSalidaDeposito(){
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
	
	var paginadetalle="";
	$("tr[name=tdDetalleSalidaDepositoOffline]").each(function(i, elementohtml){
      var codigo=$(elementohtml).children('td[id="td_datos_1"]').html();
      var producto=$(elementohtml).children('td[id="td_datos_3"]').html();
      var cantidad=$(elementohtml).children('td[id="td_datos_4"]').html();
      var deposito=$(elementohtml).children('td[id="td_datos_7"]').html();
	 paginadetalle+="<table class='tableTicket'><tr>"
    +"<td style='width:100%'>"+codigo+" * "+producto+", CANT. "+cantidad+"</td>"
    +"</tr></table>"
    +"<table class='tableTicket'><tr>"
   +"<td style='width:33%'>"+deposito+"</td></tr></table>"
	  
	   })	
	
pagina="<div  style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >CONTROL DE DEPOSITO</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Imp.:</b></td>"
+"<td style=''>"+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100%'><b>Producto</b></td>"
+"</tr>"
+"</table>"
+paginadetalle
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Cajero :</b></td>"
+"<td style=''>"+document.getElementById("lblUser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
+"</div>"


var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("https://systemsrepository.com/GoodVentaByR/system/reportticket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
}



/*
DESPACHAR PRODUCTOS
*/
function verCerrarDespacharProducto(d){
	if(d=="1"){
		$("div[id=divAbmDespacharProducto]").fadeIn(500);
		limpiarCamposDespacharProductos()
	}else{
		$("div[id=divAbmDespacharProducto]").fadeOut(500);
	}
}
function limpiarCamposDespacharProductos(){
	
	document.getElementById("inptStockProductoEnviarA").value=""
	var f = new Date();
	var dia =f.getDate()
	if(dia<10){
		dia="0"+dia;
	}
	var mes =f.getMonth()+1
	if(mes<10){
		mes="0"+mes;
	}
	document.getElementById('inptFechaProductoEnviarA').value=f.getFullYear()+"-"+mes+"-"+dia;
	
	
}
function VerificarDatosProductosEnviarA(){
	var inptFechaProductoEnviarA = document.getElementById("inptFechaProductoEnviarA").value
	var inptStockProductoEnviarA = document.getElementById("inptStockProductoEnviarA").value
	var inptLocalProductoEnviarA = document.getElementById("inptLocalProductoEnviarA").value
	var inptlocalProducto=document.getElementById('inptlocalProducto').value
	if(inptFechaProductoEnviarA==""){
		ver_vetana_informativa("Falto seleccionar la fecha")
		return
	}
	if(inptStockProductoEnviarA==""){
		document.getElementById("inptStockProductoEnviarA").focus()
		ver_vetana_informativa("Falto ingresar el stock")
		return
	}
	if(inptLocalProductoEnviarA==""){
		document.getElementById("inptLocalProductoEnviarA").focus()
		ver_vetana_informativa("Falto seleccionar el local")
		return
	}	
	AbmEnviarProductoA(inptlocalProducto,inptFechaProductoEnviarA,inptStockProductoEnviarA,inptLocalProductoEnviarA,"EnviarProductoA")
}
function AbmEnviarProductoA(cod_local_de,fecha,stock,cod_local_a,accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("fecha", fecha)
	datos.append("stock", stock)
	datos.append("cod_local_a", cod_local_a)
	datos.append("cod_local_de", cod_local_de)
	datos.append("cod_producto_fk", idAbmProducto)
	datos.append("cod_ext", "Undefined")
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
					
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					ImprimirTicketDespacho()
					limpiarCamposDespacharProductos()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function verCerrarAbmListadoDespacho(d){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmListadoDespacho").style.display==""){
			
document.getElementById("tdEfectoAbmListadoDespacho").className="magictime vanishOut"
	$("div[id=divAbmListadoDespacho]").fadeOut(500);	
		document.getElementById("divMinimizadoListadoDespachado").style.display="none"
		limpiarTodoListadoDespacharProductos()
	}else{	
if(controlacceso("VERLISTADOPRODUCTOS","accion")==false){return;}
document.getElementById("divAbmListadoDespacho").style.display=""
document.getElementById("tdEfectoAbmListadoDespacho").className="magictime slideDownReturn"
		
	}
}
function minimizarabmlistadodespacho(){
document.getElementById("tdEfectoAbmListadoDespacho").className="magictime slideDown"
$("div[id=divAbmListadoDespacho]").fadeOut(500);
document.getElementById("divMinimizadoListadoDespachado").style.display=""		
}

var controlDespachoProducto="1";
function VerificarDatosListadoDespacho(){
	
	var inptFechaListadoDespacho = document.getElementById("inptFechaListadoDespacho").value
	var inptCantProductoListadoDespachar = document.getElementById("inptCantProductoListadoDespachar").value
	var inptLocalProductoListadoDespachar2 = document.getElementById("inptLocalProductoListadoDespachar2").value
	var inptLocalProductoListadoDespachar1=document.getElementById('inptLocalProductoListadoDespachar1').value
	if(codProductoFkListadoDespacho==""){
		ver_vetana_informativa("Falto seleccionar el producto")
		return
	}
	if(inptFechaListadoDespacho==""){
		ver_vetana_informativa("Falto seleccionar la fecha")
		return
	}
	if(inptCantProductoListadoDespachar==""){
		document.getElementById("inptCantProductoListadoDespachar").focus()
		ver_vetana_informativa("Falto ingresar el stock")
		return
	}
	if(inptLocalProductoListadoDespachar2==""){
		document.getElementById("inptLocalProductoListadoDespachar2").focus()
		ver_vetana_informativa("Falto seleccionar el local destino")
		return
	}	
	if(inptLocalProductoListadoDespachar1==""){
		document.getElementById("inptLocalProductoListadoDespachar1").focus()
		ver_vetana_informativa("Falto seleccionar el local Origen")
		return
	}	
	
	if(controlDespachoProducto=="2"){
		ver_vetana_informativa("TRANSACCION EN CURSO FAVOR ESPERE")
		return;
	}
	controlDespachoProducto="2";
	
	AbmEnviarListadoDespacho(inptLocalProductoListadoDespachar1,inptLocalProductoListadoDespachar2,inptFechaListadoDespacho,inptCantProductoListadoDespachar,"EnviarProductoA")
}
function AbmEnviarListadoDespacho(cod_local_de,cod_local_a,fecha,stock,accion) {
	verCerrarEfectoCargando("1")
	var codigo=stringGenerador(5)
	var codigo_table=codigo+"_"+codProductoFkListadoDespacho
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("fecha", fecha)
	datos.append("stock", stock)
	datos.append("cod_local_a", cod_local_a)
	datos.append("cod_local_de", cod_local_de)
	datos.append("cod_producto_fk", codProductoFkListadoDespacho)
	datos.append("cod_ext", codigo_table)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
					
					controlDespachoProducto="1";
										ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")

	
                     var pagina="<table id='tdDetalleListadoDespacho_"+codigo_table+"' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>"
+"<tr id='tbSelecRegistro' onclick='ObtenerdatosAbmDespacho(this)'  name='tdDetalleListadoDespachoOffline'>"
+"<td id='td_id_1' style='display:none'>"+codigo_table+"</td>"
+"<td id='td_id_2' style='display:none'>"+codProductoFkListadoDespacho+"</td>"
+"<td  id='td_datos_1' style='width:10%;'>"+codigoProductolistadoDespacho+"</td>"
+"<td  id='td_datos_3' style='width:10%'>"+document.getElementById("inptProductoListadoDespachar").value+"</td>"
+"<td  id='td_datos_4' style='width:5%'>"+stock+"</td>"
+"<td  id='td_datos_5' style='width:10%'>"+$("select[id=inptLocalProductoListadoDespachar1]").children(":selected").text()+"</td>"
+"<td  id='td_datos_7' style='width:10%'>"+$("select[id=inptLocalProductoListadoDespachar2]").children(":selected").text()+"</td>"
+"</tr>"
+"</table>"
document.getElementById("table_abm_listado_despacho").innerHTML+=pagina;

var control=0;
$("tr[name=tdDetalleListadoDespachoOffline]").each(function(i, elementohtml){
control=control+1;
	   })	
	   document.getElementById("btnImprimirListadoDespacho").style.display=""
	   document.getElementById("btnLimpiarCamposListadoDespacho").style.display=""
document.getElementById("inptRegistroNroListadoDespacho").value=control
	   
					limpiarCamposListadoDespacharProductos()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function limpiarCamposListadoDespacharProductos(){
codProductoFkListadoDespacho=""	
codigoProductolistadoDespacho=""	
document.getElementById("inptRegistroSeleccListadoDepacho").value=""
document.getElementById("inptCantProductoListadoDespachar").value=""
document.getElementById("inptProductoListadoDespachar").value=""
document.getElementById("btnGuardarListadoDespacho").style.backgroundColor="#b7b7b7"
document.getElementById("btnAnularListadoDepachoProducto").style.backgroundColor="#b7b7b7"
}
function limpiarTodoListadoDespacharProductos(){
codProductoFkListadoDespacho=""	
codigoProductolistadoDespacho=""	
document.getElementById("table_abm_listado_despacho").innerHTML=""
document.getElementById("inptCantProductoListadoDespachar").value=""
document.getElementById("inptRegistroSeleccListadoDepacho").value=""
document.getElementById("inptProductoListadoDespachar").value=""
document.getElementById("inptRegistroNroListadoDespacho").value=""
document.getElementById("btnAnularListadoDepachoProducto").style.backgroundColor="#b7b7b7"
document.getElementById("btnGuardarListadoDespacho").style.backgroundColor="#b7b7b7"
document.getElementById("btnImprimirListadoDespacho").style.display="none"
document.getElementById("btnLimpiarCamposListadoDespacho").style.display="none"
}
var elementoDespachado="";
function ObtenerdatosAbmDespacho(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});		
	elementoDespachado=datostr
	datostr.className = 'tableRegistroSelec'
    document.getElementById("inptRegistroSeleccListadoDepacho").value = $(datostr).children('td[id="td_datos_3"]').html();
     document.getElementById("btnAnularListadoDepachoProducto").style=""
}
function AbmAnularListadoDespacho() {
	verCerrarEfectoCargando("1")
	var cod_ext=$(elementoDespachado).children('td[id="td_id_1"]').html();
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "anulardespacho")
	datos.append("cod_ext", cod_ext)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				   Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
										ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
           $("table[id=tdDetalleListadoDespacho_"+cod_ext+"]").remove()    
var control=0;
$("tr[name=tdDetalleListadoDespachoOffline]").each(function(i, elementohtml){
control=control+1;
	   })	

document.getElementById("inptRegistroNroListadoDespacho").value=control
	   limpiarCamposListadoDespacharProductos()
					
				}
				try {
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}



/*
REPORTE DESPACHO
*/
function verCerrarInformeProductosDespachados(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divinfoProductosDespachados").style.display==""){
		document.getElementById("divMinimizadoProductosDespachados").style.display="none"
document.getElementById("tdInfoProductosDespachados").className="magictime vanishOut"
	$("div[id=divinfoProductosDespachados]").fadeOut(500);	
	}else{		
	if(controlacceso("VERDESPACHADOS","accion")==false){return;}
		if(controldebusquedadDespachado==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
limpiarcamposbuscardespachados()
		document.getElementById("divinfoProductosDespachados").style.display=""
document.getElementById("tdInfoProductosDespachados").className="magictime slideDownReturn"

		
	}
}
function checkHistorialProductosDespachados(d){	
	if(d=="1"){
		document.getElementById('checkHistorialVentasProductosDespachados1').checked=true
		document.getElementById('checkHistorialProductosDespachados2').checked=false
		document.getElementById('inptBuscarInfProdutosDespachadosF1').value = "";
	    document.getElementById('inptBuscarInfProdutosDespachadosF2').value = "";	
	}else{		
		document.getElementById('checkHistorialVentasProductosDespachados1').checked=false
		document.getElementById('checkHistorialProductosDespachados2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarInfProdutosDespachadosF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarInfProdutosDespachadosF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
function limpiarcamposbuscardespachados(){
		if(controldebusquedadDespachado==true){
		
	return
}
 document.getElementById('inptlocal1InformeProductoDespachado').value=""
 document.getElementById('inptlocal2InformeProductoDespachado').value=""
 document.getElementById('inptBuscarInfProductoDespachado1').value=""
 document.getElementById('inptBuscarInfProductoDespachado2').value=""
 document.getElementById('inptBuscarInfProdutosDespachadosF1').value=""
 document.getElementById('inptBuscarInfProductosDespachadosF2').value=""
  document.getElementById("table_historial_producto_despachado").innerHTML=""
		 document.getElementById("inptRegistroNroHistorialProductoDespachado").value=""
document.getElementById("tbProcessDespachado").style.display="none"
}
var registrocargadodespachado="";
var totalregistrodespachado="";
var controldebusquedadDespachado=false
function cancelarCargaDespachado(){
	controldebusquedadDespachado=false
	document.getElementById("divProgressDespachado").style.backgroundColor='#ff5722'
}
function buscarhistorialProductosDespachados(){
	if(controlacceso("VERDESPACHADOS","accion")==false){return;}
  var codlocal1=document.getElementById('inptlocal1InformeProductoDespachado').value
 var codlocal2=document.getElementById('inptlocal2InformeProductoDespachado').value
 var cod_producto=document.getElementById('inptBuscarInfProductoDespachado1').value
 var producto=document.getElementById('inptBuscarInfProductoDespachado2').value
 var fecha1=document.getElementById('inptBuscarInfProdutosDespachadosF1').value
 var fecha2=document.getElementById('inptBuscarInfProductosDespachadosF2').value
 
 if(document.getElementById('checkHistorialProductosDespachados2').checked==true){
	 if(fecha1==""){
		 	ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO","#")
		 return
	 }
	 if(fecha2==""){
		 	ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN","#")
		 return
	 }
 }else{
 fecha1=""
 fecha2=""
 }
 	if(controldebusquedadDespachado==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadDespachado=true
		 document.getElementById("table_historial_producto_despachado").innerHTML=paginacargando
		 document.getElementById("inptRegistroNroHistorialProductoDespachado").value=""
		 document.getElementById("tbProcessDespachado").style.display="none"
		 	obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"fecha1": fecha1,
			"fecha2": fecha2,
			"codlocal1": codlocal1,
			"codlocal2": codlocal2,
			"cod_producto": cod_producto,
			"producto": producto,
			"funt": "historialdespachado"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_historial_producto_despachado").innerHTML=''
			controldebusquedadDespachado=false
			},
			success: function(responseText)
			{	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_historial_producto_despachado").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		  Respuesta=respuestaJqueryAjax(Respuesta)
		if (Respuesta == true) {					
		    var datos_buscados=datos[2];
			document.getElementById("table_historial_producto_despachado").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroHistorialProductoDespachado").value=datos[3];	
			
					    	registrocargadodespachado=datos[99];
					totalregistrodespachado=datos[100];
			
					
						 if(totalregistrodespachado>registrocargadodespachado){
						 	var porce=((registrocargadodespachado*100)/totalregistrodespachado).toFixed(0)
	document.getElementById("divProgressDespachado").style.width=porce+"%"
						 document.getElementById("table_historial_producto_despachado").innerHTML += "<div id='table_historial_mas_producto_despachado'></div>"
						  buscarmashistorialProductosDespachados();
					 }else{
						 controldebusquedadDespachado=false
					 }
			}
			}catch(error)
				{
					controldebusquedadDespachado=false
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				}
			}
			});		
}
function buscarmashistorialProductosDespachados(c){
	if(controlacceso("VERDESPACHADOS","accion")==false){return;}
  var codlocal1=document.getElementById('inptlocal1InformeProductoDespachado').value
 var codlocal2=document.getElementById('inptlocal2InformeProductoDespachado').value
 var cod_producto=document.getElementById('inptBuscarInfProductoDespachado1').value
 var producto=document.getElementById('inptBuscarInfProductoDespachado2').value
 var fecha1=document.getElementById('inptBuscarInfProdutosDespachadosF1').value
 var fecha2=document.getElementById('inptBuscarInfProductosDespachadosF2').value
 
 if(document.getElementById('checkHistorialProductosDespachados2').checked==true){
	 if(fecha1==""){
		 	ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO","#")
		 return
	 }
	 if(fecha2==""){
		 	ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN","#")
		 return
	 }
 }else{
 fecha1=""
 fecha2=""
 }
 if(c=="1"){
	 controldebusquedadDespachado=true
 }
 	if(controldebusquedadDespachado==false){
	return
}
controldebusquedadDespachado=true
		 document.getElementById("table_historial_mas_producto_despachado").innerHTML=paginacargando
		 document.getElementById("tbProcessDespachado").style.display=""
		 document.getElementById("divProgressDespachado").style.backgroundColor=''
		 	obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"fecha1": fecha1,
			"fecha2": fecha2,
			"codlocal1": codlocal1,
			"codlocal2": codlocal2,
			"cod_producto": cod_producto,
			"producto": producto,
			"registrocargado": registrocargadodespachado,
			"funt": "historialmasdespachado"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_historial_mas_producto_despachado").innerHTML=''
			document.getElementById("divProgressDespachado").style.backgroundColor='#ff5722'
			controldebusquedadDespachado=false
			},
			success: function(responseText)
			{	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_historial_mas_producto_despachado").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		  Respuesta=respuestaJqueryAjax(Respuesta)
		if (Respuesta == true) {					
		    var datos_buscados=datos[2];
			document.getElementById("table_historial_mas_producto_despachado").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroHistorialProductoDespachado").value=datos[3];	
			
					    	registrocargadodespachado=datos[99];
		
					
						 if(totalregistrodespachado>registrocargadodespachado){
						 	var porce=((registrocargadodespachado*100)/totalregistrodespachado).toFixed(0)
	document.getElementById("divProgressDespachado").style.width=porce+"%"
						 document.getElementById("table_historial_mas_producto_despachado").innerHTML += "<div id='table_historial_mas_producto_despachado'></div>"
						 document.getElementById("table_historial_mas_producto_despachado").id=""
						  buscarmashistorialProductosDespachados();
					 }else{
						  document.getElementById("tbProcessDespachado").style.display="none"
						 controldebusquedadDespachado=false
					 }
			}
			}catch(error)
				{
					document.getElementById("divProgressDespachado").style.backgroundColor='#ff5722'
					controldebusquedadDespachado=false
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				}
			}
			});		
}


/*
ABM TIPO IMPUESTO
*/
var idAbmTipoImpuesto="";
var ElementoSeleccTipoImpuesto="";
var VentanaTipoImpuesto="";
function verCerrarFrmTipoImpuesto(d,v){
	if(d=="1"){
		$("div[id=divAbmTipoImpuesto]").fadeIn(500);
		VentanaTipoImpuesto=v;
		BuscarAbmTipoImpuesto()
	}else{
		$("div[id=divAbmTipoImpuesto]").fadeOut(500);
	}
}
function LimpiarCamposTipoImpuesto(){
	document.getElementById("inptNombreTipoImpuesto").value="";
	document.getElementById("inptEstadoTipoImpuesto").value="";
	document.getElementById("inptPorcentajeTipoImpuesto").value="";
	document.getElementById("btnTipoImpuesto1").value="Guardar Datos"
	idAbmTipoImpuesto="";
	ElementoSeleccTipoImpuesto="";
}
function ObtenerdatosAbmTipoImpuesto(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});		
	ElementoSeleccTipoImpuesto=datostr
	datostr.className = 'tableRegistroSelec'
    document.getElementById("inptNombreTipoImpuesto").value = $(datostr).children('td[id="td_datos_1"]').html();
    document.getElementById("inptEstadoTipoImpuesto").value = $(datostr).children('td[id="td_datos_2"]').html();
    document.getElementById("inptPorcentajeTipoImpuesto").value = $(datostr).children('td[id="td_datos_3"]').html();
	idAbmTipoImpuesto = $(datostr).children('td[id="td_id"]').html();
     document.getElementById("btnTipoImpuesto1").value="Editar Datos"
}
function SeleccionarRegistroTipoImpuesto(){
	if(ElementoSeleccTipoImpuesto==""){
		ver_vetana_informativa("Falto Seleccionar un registro")
		return;
	}
    if(VentanaTipoImpuesto=="abmproducto"){
	 document.getElementById("inptTipoImpuestoProducto").value = $(ElementoSeleccTipoImpuesto).children('td[id="td_datos_1"]').html();
	 idFkProductoTipoImpuesto = $(ElementoSeleccTipoImpuesto).children('td[id="td_id"]').html();
	}	
	 document.getElementById("divAbmTipoImpuesto").style.display="none";
		 LimpiarCamposTipoImpuesto()
}
function VerificarDatosTipoImpuesto(){
	var inptNombreTipoImpuesto = document.getElementById("inptNombreTipoImpuesto").value
	var inptEstadoTipoImpuesto = document.getElementById("inptEstadoTipoImpuesto").value
	var inptPorcentajeTipoImpuesto = document.getElementById("inptPorcentajeTipoImpuesto").value
	if(inptNombreTipoImpuesto==""){
		document.getElementById("inptNombreTipoImpuesto").focus()
		ver_vetana_informativa("Falto Ingresar el nombre")
		return
	}
	if(inptPorcentajeTipoImpuesto==""){
		document.getElementById("inptPorcentajeTipoImpuesto").focus()
		ver_vetana_informativa("Falto ingresar el porcentaje")
		return
	}
	if(inptEstadoTipoImpuesto==""){
		document.getElementById("inptEstadoTipoImpuesto").focus()
		ver_vetana_informativa("Falto seleccionar el estado del registro")
		return
	}	
	var accion = "";
	if (idAbmTipoImpuesto != "") {
		
		accion = "editar";
	} else {
		
		accion = "nuevo";
	}
	AbmTipoImpuesto(inptNombreTipoImpuesto,inptEstadoTipoImpuesto,inptPorcentajeTipoImpuesto,idAbmTipoImpuesto,accion)
}
function AbmTipoImpuesto(descripcion,Estado,monto_impuesto,idabm,accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idabm", idabm)
	datos.append("descripcion", descripcion)
	datos.append("monto_impuesto", monto_impuesto)
	datos.append("Estado", Estado)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/ABMTipoImpuesto.php",
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
					LimpiarCamposTipoImpuesto()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					BuscarAbmTipoImpuesto()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function BuscarAbmTipoImpuesto() {
	var buscador = ""
	var estado = "Activo"
	document.getElementById("divBuscadorTipoImpuesto").innerHTML = paginacargando
    document.getElementById("lblNroRegistroTipoImpuesto").innerHTML="";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"estado": estado,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
        url: "/GoodVentaByR/php_system/ABMTipoImpuesto.php",
		type: "post",
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
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("divBuscadorTipoImpuesto").innerHTML = ''
			document.getElementById("lblNroRegistroTipoImpuesto").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorTipoImpuesto").innerHTML = ''
			document.getElementById("lblNroRegistroTipoImpuesto").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				document.getElementById("divBuscadorTipoImpuesto").innerHTML = datos_buscados
				document.getElementById("lblNroRegistroTipoImpuesto").innerHTML="Se encontraron "+datos[3]+" registro(s)";

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
ABM DETALLES PRECIOS
*/
function verCerrarAbmDetallesPrecio(d){
	if(d=="2"){
		//document.getElementById("divAbmDetallesPrecios").style.display="none"
		document.getElementById("tdEfectoAbmDetallePrecios").className="magictime slideRight"
        $("div[id=divAbmDetallesPrecios]").fadeOut(500);
		document.getElementById("inptPrecioCompraDetallesPrecio").value ="";
		
		
	}else{		
if(controlacceso("EDITARPRECIOSLISTADOPRODUCTOS","accion")==false){return;}
		document.getElementById("divAbmDetallesPrecios").style.display=""
		 document.getElementById("tdEfectoAbmDetallePrecios").className="magictime slideLeftReturn"
		var inptPrecioCompraProducto = document.getElementById("inptPrecioCompraProducto").value
		document.getElementById("inptPrecioCompraDetallesPrecio").value = inptPrecioCompraProducto;
		
		
	}
}
function verCerrarAbmDetallesPrecio2(){

if(controlacceso("EDITARPRECIOSLISTADOPRODUCTOS","accion")==false){return;}
		document.getElementById("divAbmDetallesPrecios").style.display=""
		 document.getElementById("tdEfectoAbmDetallePrecios").className="magictime slideLeftReturn"
		buscardetallesprecio()
	
}
var idAbmDetallePrecio = "";
function obtenerdatosabmdetallesprecio(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptDetallePrecio').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptDetalleDescrip').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptComisionDetallesPrecio').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptPorcDetallesPrecio').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptDetalleCuotaPrecio').value = $(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptNroCuotaDetallesPrecio').value = $(datostr).children('td[id="td_datos_6"]').html();
	idAbmDetallePrecio = $(datostr).children('td[id="td_datos_3"]').html();
document.getElementById('btnAddPreciosProductos1').value = "Editar";
	document.getElementById('btnAddPreciosProductos1').style.display = "";
	document.getElementById('btnAddPreciosProductos2').style.display = "";
	document.getElementById('btnAddPreciosProductos3').style.display = "";
}
function calcularPorcentajeDesdeMontoCuota(d){
	var montocompra=document.getElementById("inptPrecioCompraProducto").value
	var montocuota=document.getElementById("inptDetalleCuotaPrecio").value
	var nrocuota=document.getElementById("inptNroCuotaDetallesPrecio").value
	montocompra=QuitarSeparadorMilValor(montocompra)
	montocuota=QuitarSeparadorMilValor(montocuota)
	if (isNaN(nrocuota)) {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE COUTA")
        return false;
	}
	if (isNaN(montocompra)) {
		ver_vetana_informativa("FALTO INGRESAR UN MONTO DE COMPRA")
        return false;
	}
	if (isNaN(montocuota)) {
		ver_vetana_informativa("FALTO INGRESAR EL MONTO DE LA CUOTA")
		return false;
	}
	var totalcuotax=Number(montocuota)*Number(nrocuota)
	var totalcuota=Number(totalcuotax)-Number(montocompra)
	var porcentaje=((Number(totalcuota)*100)/ Number(montocompra)).toFixed(1)
	porcentaje=porcentaje.replace('.',',')
	document.getElementById("inptDetallePrecio").value=separadordemilesnumero(totalcuotax)
	document.getElementById("inptPorcDetallesPrecio").value=porcentaje
	document.getElementById("inptDetalleCuotaPrecio").value=separadordemilesnumero(montocuota)
	CargarTituloCredito()
}
function calcularGananciaDesdePorcentajeDetallesPrecio(d){
	var montocompra=document.getElementById("inptPrecioCompraProducto").value
	var porcentaje=document.getElementById("inptPorcDetallesPrecio").value
	montocompra=QuitarSeparadorMilValor(montocompra)
	porcentaje=QuitarSeparadorMilValor(porcentaje)
	if (isNaN(montocompra)) {
		ver_vetana_informativa("FALTO INGRESAR UN MONTO DE COMPRA")
        return false;
	}
	if (isNaN(porcentaje)) {
		ver_vetana_informativa("FALTO INGRESAR EL PORCENTAJE DE GANANCIA")
		return false;
	}
	var total=Math.round((Number(porcentaje)*Number(montocompra))/100)
	total=Number(total)+Number(montocompra)
	document.getElementById("inptDetallePrecio").value=separadordemilesnumero(total)
	CalcularMontoCuota();
	CargarTituloCredito()
}
function CalcularMontoCuota(){
	var precioventafinal=document.getElementById("inptDetallePrecio").value
	var cuota=document.getElementById("inptNroCuotaDetallesPrecio").value
	precioventafinal=QuitarSeparadorMilValor(precioventafinal)
	cuota=QuitarSeparadorMilValor(cuota)
	if (isNaN(precioventafinal)) {
        precioventafinal=0;
	}
	if (isNaN(cuota)) {
		cuota=0;
	}
	var total=Math.round(Number(precioventafinal)/Number(cuota))
	document.getElementById("inptDetalleCuotaPrecio").value=separadordemilesnumero(total)
}
function CargarTituloCredito(){
	var costo=document.getElementById("inptDetalleCuotaPrecio").value
	var cuotanro=document.getElementById("inptNroCuotaDetallesPrecio").value
	document.getElementById("inptDetalleDescrip").value=cuotanro+" x "+costo
}
function verificarcamposdetallesprecio() {
	var inptDetallePrecio = document.getElementById('inptDetallePrecio').value
	var inptDetalleDescrip = document.getElementById('inptDetalleDescrip').value
	var inptComisionDetallesPrecio = document.getElementById('inptComisionDetallesPrecio').value
	var inptPorcDetallesPrecio = document.getElementById('inptPorcDetallesPrecio').value
	var inptDetalleCuotaPrecio = document.getElementById('inptDetalleCuotaPrecio').value
	var inptNroCuotaDetallesPrecio = document.getElementById('inptNroCuotaDetallesPrecio').value
	if (idAbmProducto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO O GUARDAR EL PRODUCTO QUE QUIERES AÑADIR", "#")
		return false;
	}
	if (inptNroCuotaDetallesPrecio == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE COUTA", "#")
		return false;
	}
	if (inptDetallePrecio == "") {
		ver_vetana_informativa("FALTO INGRESAR EL PRECIO", "#")
		return false;
	}
	if (inptDetalleDescrip == "") {
		ver_vetana_informativa("FALTO INGRESAR LA DESCRIPCION", "#")
		return false;
	}
	if (inptComisionDetallesPrecio == "") {
		ver_vetana_informativa("FALTO INGRESAR LA COMISIÓN", "#")
		return false;
	}
	var accion = "";
	if (idAbmDetallePrecio != "") {
		accion = "editar";
		if(controlacceso("EDITARPRECIOSLISTADOPRODUCTOS","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("EDITARPRECIOSLISTADOPRODUCTOS","accion")==false){return;}
	}
	abmdetallesprecio(inptDetallePrecio, inptDetalleDescrip, inptComisionDetallesPrecio,inptPorcDetallesPrecio,inptDetalleCuotaPrecio,inptNroCuotaDetallesPrecio, idAbmDetallePrecio, accion);
}
function eliminardetallesprecio() {
	var inptDetallePrecio = "XX"
	var inptDetalleDescrip = "XX"
	var inptComisionDetallesPrecio = "XX"
	if (idAbmDetallePrecio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR REGISTRO", "#")
		return false;
	}
	var accion = "eliminar";
if(controlacceso("EDITARPRECIOSLISTADOPRODUCTOS","accion")==false){return;}
	abmdetallesprecio(0, 0, 0,0,0,0, idAbmDetallePrecio, accion);
}
function abmdetallesprecio(precio, descripcion, comision,Porcentaje,preciocuota ,Cuota,iddetallesprecio, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("precio", precio)
	datos.append("descripcion", descripcion)
	datos.append("iddetallesprecio", iddetallesprecio)
	datos.append("comision", comision)
	datos.append("Porcentaje", Porcentaje)
	datos.append("cod_producto", idAbmProducto)
	datos.append("Cuota", Cuota)
	datos.append("preciocuota", preciocuota)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallesprecio.php",
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
				limpiarcamposDetallePrecio()
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
				idAbmDetallePrecio = ""
				buscardetallesprecio()
					buscardetallesprecioenbuscarproductos()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function buscardetallesprecio() {

	document.getElementById("table_vista_detalles_precio").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idAbmProducto,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallesprecio.php",
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
			document.getElementById("table_vista_detalles_precio").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_detalles_precio").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_vista_detalles_precio").innerHTML = datos_buscados
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});

}
function buscardetallesprecioenbuscarproductos() {

	document.getElementById("table_abm_producto_detalles_precios").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idAbmProducto,
		"funt": "buscarabmproductos"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallesprecio.php",
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
			document.getElementById("table_abm_producto_detalles_precios").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_producto_detalles_precios").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_producto_detalles_precios").innerHTML = datos_buscados
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});

}
function buscardetallespreciodesdevista(donde) {
if(donde=="vista"){
	document.getElementById("table_abm_vista_precios_producto").innerHTML = paginacargando
}
if(donde=="precios"){
	
document.getElementById("table_precios_productos_consultar_precios").innerHTML = paginacargando
}
if(donde=="vistaventa"){
	
document.getElementById("table_vista_producto_venta_costos").innerHTML = paginacargando
}
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idFkProducto,
		"funt": "buscarvista"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallesprecio.php",
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
if(donde=="vista"){
	document.getElementById("table_abm_vista_precios_producto").innerHTML = ""
}
if(donde=="precios"){	
document.getElementById("table_precios_productos_consultar_precios").innerHTML = ""
}
if(donde=="vistaventa"){
	
document.getElementById("table_vista_producto_venta_costos").innerHTML = ""
}
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			if(donde=="vista"){
	document.getElementById("table_abm_vista_precios_producto").innerHTML = ""
}
if(donde=="precios"){
	
document.getElementById("table_precios_productos_consultar_precios").innerHTML = ""
}
if(donde=="vistaventa"){
	
document.getElementById("table_vista_producto_venta_costos").innerHTML = ""
}
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					if(donde=="vista"){
						document.getElementById("table_abm_vista_precios_producto").innerHTML = datos_buscados
						}
						if(donde=="precios"){
							document.getElementById("table_precios_productos_consultar_precios").innerHTML = datos_buscados
							
							}
							if(donde=="vistaventa"){
	
document.getElementById("table_vista_producto_venta_costos").innerHTML = datos_buscados
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
function limpiarcamposDetallePrecio() {
document.getElementById('btnAddPreciosProductos1').value = "Añadir";
	document.getElementById('btnAddPreciosProductos1').style.display = "";
	document.getElementById('btnAddPreciosProductos2').style.display = "none";
	document.getElementById('btnAddPreciosProductos3').style.display = "none";
	document.getElementById('inptDetallePrecio').value = "";
	document.getElementById('inptDetalleDescrip').value = "";
	document.getElementById('inptPorcDetallesPrecio').value = "";
	document.getElementById('inptComisionDetallesPrecio').value = "";
	document.getElementById('inptDetalleCuotaPrecio').value=""
	document.getElementById('inptNroCuotaDetallesPrecio').value=""
	idAbmDetallePrecio = "";
}

/*DETALLES PRECIOS COMPRA*/
var idAbmDetallePrecioCompra="";
function verCerrarAbmDetallesPrecioCompra(d){
	if(d=="2"){
		//document.getElementById("divAbmDetallesPrecios").style.display="none"
		document.getElementById("tdEfectoAbmDetallePreciosCompras").className="magictime slideRight"
        $("div[id=divAbmDetallesPreciosCompras]").fadeOut(500);
		document.getElementById("inptPrecioCompraDetallesPrecioCompra").value ="";
		
		
	}else{		
if(controlacceso("EDITARPRECIOSLISTADOPRODUCTOS","accion")==false){return;}
		document.getElementById("divAbmDetallesPreciosCompras").style.display=""
		 document.getElementById("tdEfectoAbmDetallePreciosCompras").className="magictime slideLeftReturn"
		var inptPrecioCompraProducto = document.getElementById("inptCostoProductoCompra").value
		document.getElementById("inptPrecioCompraDetallesPrecioCompra").value = inptPrecioCompraProducto;
		 buscardetallespreciocmpras()
		
	}
}
function obtenerdatosabmdetallespreciocompra(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptDetallePrecioCompra').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptDetalleDescripCompra').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptComisionDetallesPrecioCompra').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptPorcDetallesPrecioCompra').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptDetalleCuotaPrecioCompra').value = $(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptNroCuotaDetallesPrecioCompra').value = $(datostr).children('td[id="td_datos_6"]').html();
	idAbmDetallePrecioCompra = $(datostr).children('td[id="td_datos_3"]').html();
document.getElementById('btnAddPreciosProductosCompra1').value = "Editar";
	document.getElementById('btnAddPreciosProductosCompra1').style.display = "";
	document.getElementById('btnAddPreciosProductosCompra2').style.display = "";
}
function verificarcamposdetallesprecioCompra() {
	var inptDetallePrecio = document.getElementById('inptDetallePrecioCompra').value
	var inptDetalleDescrip = document.getElementById('inptDetalleDescripCompra').value
	var inptComisionDetallesPrecio = document.getElementById('inptComisionDetallesPrecioCompra').value
	var inptPorcDetallesPrecio = document.getElementById('inptPorcDetallesPrecioCompra').value
	var inptDetalleCuotaPrecio = document.getElementById('inptDetalleCuotaPrecioCompra').value
	var inptNroCuotaDetallesPrecio = document.getElementById('inptNroCuotaDetallesPrecioCompra').value
	if (idFkProductocompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO O GUARDAR EL PRODUCTO QUE QUIERES AÑADIR", "#")
		return false;
	}
	if (inptNroCuotaDetallesPrecio == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE COUTA", "#")
		return false;
	}
	if (inptDetallePrecio == "") {
		ver_vetana_informativa("FALTO INGRESAR EL PRECIO", "#")
		return false;
	}
	if (inptDetalleDescrip == "") {
		ver_vetana_informativa("FALTO INGRESAR LA DESCRIPCION", "#")
		return false;
	}
	if (inptComisionDetallesPrecio == "") {
		ver_vetana_informativa("FALTO INGRESAR LA COMISIÓN", "#")
		return false;
	}
	var accion = "";
	if (idAbmDetallePrecioCompra != "") {
		accion = "editar";
		if(controlacceso("EDITARPRECIOSLISTADOPRODUCTOS","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("EDITARPRECIOSLISTADOPRODUCTOS","accion")==false){return;}
	}
	abmdetallesprecioCompra(inptDetallePrecio, inptDetalleDescrip, inptComisionDetallesPrecio,inptPorcDetallesPrecio,inptDetalleCuotaPrecio,inptNroCuotaDetallesPrecio, idAbmDetallePrecioCompra, accion);
}
function eliminardetallesprecioCompra() {
	var inptDetallePrecio = "XX"
	var inptDetalleDescrip = "XX"
	var inptComisionDetallesPrecio = "XX"
	if (idAbmDetallePrecioCompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR REGISTRO", "#")
		return false;
	}
	var accion = "eliminar";
if(controlacceso("EDITARPRECIOSLISTADOPRODUCTOS","accion")==false){return;}
	abmdetallesprecioCompra(0, 0, 0,0,0,0, idAbmDetallePrecioCompra, accion);
}
function abmdetallesprecioCompra(precio, descripcion, comision,Porcentaje,preciocuota ,Cuota,iddetallesprecio, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("precio", precio)
	datos.append("descripcion", descripcion)
	datos.append("iddetallesprecio", iddetallesprecio)
	datos.append("comision", comision)
	datos.append("Porcentaje", Porcentaje)
	datos.append("cod_producto", idFkProductocompra)
	datos.append("Cuota", Cuota)
	datos.append("preciocuota", preciocuota)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallesprecio.php",
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
				limpiarcamposDetallePrecioCompra()
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
				idAbmDetallePrecioCompra = ""
				buscardetallespreciocmpras()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function abmeditarprecioproductodesdecompra() {
	var porcentaje=document.getElementById("inptPorcVentaProductoContadoCompra").value
	var precioventa=document.getElementById("inptPrecioVentaProductoContadoCompra").value
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "editarpreciocontado")
	datos.append("precioventa", precioventa)
	datos.append("Porcentaje", porcentaje)
	datos.append("cod_producto", idFkProductocompra)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
				limpiarcamposDetallePrecioCompra()
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
				
				buscarvistaproductodesdecompra()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function limpiarcamposDetallePrecioCompra() {
document.getElementById('btnAddPreciosProductosCompra1').value = "Añadir";
	document.getElementById('btnAddPreciosProductosCompra1').style.display = "";
	document.getElementById('btnAddPreciosProductosCompra2').style.display = "none";
	document.getElementById('inptNroCuotaDetallesPrecioCompra').value = "";
	document.getElementById('inptPorcDetallesPrecioCompra').value = "";
	document.getElementById('inptDetallePrecioCompra').value = "";
	document.getElementById('inptDetalleCuotaPrecioCompra').value=""
	document.getElementById('inptDetalleDescripCompra').value=""
	document.getElementById('inptComisionDetallesPrecioCompra').value=""
	idAbmDetallePrecioCompra = "";
}

function buscardetallespreciocmpras() {

document.getElementById("table_vista_detalles_precioCompra").innerHTML = paginacargando

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idFkProductocompra,
		"funt": "buscarvistacompra"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallesprecio.php",
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

document.getElementById("table_vista_detalles_precioCompra").innerHTML = ""

		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			
document.getElementById("table_vista_detalles_precioCompra").innerHTML = ""

			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					
	
document.getElementById("table_vista_detalles_precioCompra").innerHTML = datos_buscados

							
					}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function CalcularMontoCuotaDesdeCompra(){
	var precioventafinal=document.getElementById("inptDetalleCuotaPrecioCompra").value
	var cuota=document.getElementById("inptNroCuotaDetallesPrecioCompra").value
	precioventafinal=QuitarSeparadorMilValor(precioventafinal)
	cuota=QuitarSeparadorMilValor(cuota)
	if (isNaN(precioventafinal)) {
        precioventafinal=0;
	}
	if (isNaN(cuota)) {
		cuota=0;
	}
	var total=Math.round(Number(precioventafinal)/Number(cuota))
	document.getElementById("inptDetalleCuotaPrecioCompra").value=separadordemilesnumero(total)
}
function calcularGananciaDesdePorcentajeDetallesPrecioDesdeCompra(d){
	var montocompra=document.getElementById("inptPrecioCompraDetallesPrecioCompra").value
	var porcentaje=document.getElementById("inptPorcDetallesPrecioCompra").value
	montocompra=QuitarSeparadorMilValor(montocompra)
	porcentaje=QuitarSeparadorMilValor(porcentaje)
	if (isNaN(montocompra)) {
		ver_vetana_informativa("FALTO INGRESAR UN MONTO DE COMPRA")
        return false;
	}
	if (isNaN(porcentaje)) {
		ver_vetana_informativa("FALTO INGRESAR EL PORCENTAJE DE GANANCIA")
		return false;
	}
	var total=Math.round((Number(porcentaje)*Number(montocompra))/100)
	total=Number(total)+Number(montocompra)
	document.getElementById("inptDetallePrecioCompra").value=separadordemilesnumero(total)
	CalcularMontoCuotaCompra();
	CargarTituloCreditoCompra()
}
function CalcularMontoCuotaCompra(){
	var precioventafinal=document.getElementById("inptDetallePrecioCompra").value
	var cuota=document.getElementById("inptNroCuotaDetallesPrecioCompra").value
	precioventafinal=QuitarSeparadorMilValor(precioventafinal)
	cuota=QuitarSeparadorMilValor(cuota)
	if (isNaN(precioventafinal)) {
        precioventafinal=0;
	}
	if (isNaN(cuota)) {
		cuota=0;
	}
	var total=Math.round(Number(precioventafinal)/Number(cuota))
	document.getElementById("inptDetalleCuotaPrecioCompra").value=separadordemilesnumero(total)
}
function CargarTituloCreditoCompra(){
	var costo=document.getElementById("inptDetalleCuotaPrecioCompra").value
	var cuotanro=document.getElementById("inptNroCuotaDetallesPrecioCompra").value
	document.getElementById("inptDetalleDescripCompra").value=cuotanro+" x "+costo
}
function calcularPorcentajeDesdeGananciaCompra(d){
	var montocompra=document.getElementById("inptPrecioCompraDetallesPrecioCompra").value
	var precioventa=document.getElementById("inptPrecioVentaProductoContadoCompra").value
	montocompra=QuitarSeparadorMilValor(montocompra)
	precioventa=QuitarSeparadorMilValor(precioventa)
	if (isNaN(montocompra)) {
		ver_vetana_informativa("FALTO INGRESAR UN MONTO DE COMPRA")
        return false;
	}
	if (isNaN(precioventa)) {
		ver_vetana_informativa("FALTO INGRESAR EL PRECIO VENTA")
		return false;
	}
	var ganancias=Math.round(Number(precioventa)-Number(montocompra))
	var porcentaje=((Number(ganancias)*100)/ Number(montocompra)).toFixed(1)
	porcentaje=porcentaje.replace('.',',')
	document.getElementById("inptPrecioVentaProductoContadoCompra").value=separadordemilesnumero(precioventa)
	document.getElementById("inptPorcVentaProductoContadoCompra").value=porcentaje
}
function calcularGananciaDesdePorcentajeCompra(d){
	var montocompra=document.getElementById("inptPrecioCompraDetallesPrecioCompra").value
	var porcentaje=document.getElementById("inptPorcVentaProductoContadoCompra").value
	montocompra=QuitarSeparadorMilValor(montocompra)
	porcentaje=QuitarSeparadorMilValor(porcentaje)
	if (isNaN(montocompra)) {
		ver_vetana_informativa("FALTO INGRESAR UN MONTO DE COMPRA")
        return false;
	}
	if (isNaN(porcentaje)) {
		ver_vetana_informativa("FALTO INGRESAR EL PORCENTAJE DE GANANCIA")
		return false;
	}
	var total=Math.round((Number(porcentaje)*Number(montocompra))/100)
	total=Number(total)+Number(montocompra)
	document.getElementById("inptPrecioVentaProductoContadoCompra").value=separadordemilesnumero(total)	
}

/*
ABM COBRADOR
*/
function verCerrarAbmCobrador(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmCobrador").style.display==""){
		document.getElementById("divMinimizadoListadoCobradores").style.display="none"
		limpiarcamposbuscarCobrador()
		limpiarcamposCobrador()
document.getElementById("tdEfectoAbmCobrador").className="magictime vanishOut"
	$("div[id=divAbmCobrador]").fadeOut(500);	
	}else{		
	if(controlacceso("VERLISTADOCOBRADORES","accion")==false){return;}
		document.getElementById("divAbmCobrador").style.display=""
document.getElementById("tdEfectoAbmCobrador").className="magictime slideDownReturn"
		
	}
}
function limpiarcamposbuscarCobrador(){
	document.getElementById('inptBuscarAbmCobrador1').value=""
	document.getElementById('inptBuscarAbmCobrador2').value=""
	document.getElementById("table_abm_cobrador").innerHTML = ""
	document.getElementById("inptRegistroNroCobrador").value = ""
}
function minimizarabmcobrador(){
document.getElementById("tdEfectoAbmCobrador").className="magictime slideDown"
	$("div[id=divAbmCobrador]").fadeOut(500);	
	document.getElementById("divMinimizadoListadoCobradores").style.display=""
}
function verCerrarVentanaAbmVistaCobrador(d) {
	if (d == "1") {
		if(controlacceso("INSERTARLISTADOCOBRADORES","accion")==false){return;}
		document.getElementById("tdEfectoVistaCobrador").className="magictime slideRight"
		$("div[id=divVistaCobrador]").fadeOut(500)
		document.getElementById("divAbmCobradorVista").style.display=""
		  document.getElementById("tdEfectoAbmCobradorVista").className=""
		document.getElementById("inptNombreApellidoCobradorVista").value = ""
		document.getElementById("inptUsuarioCobradorVista").value = ""
		document.getElementById("inptPassCobradorVista").value = ""
		document.getElementById("inptNroTelefCobradorVista").value = ""
		document.getElementById("inptZonaCobradorVista").value = ""
		idFKZona = "1";
		idAbmCobrador = "";
	} else {
		document.getElementById("tdEfectoAbmCobradorVista").className="magictime vanishOut"
	$("div[id=divAbmCobradorVista]").fadeOut(500);	
	}
}
function verificarcamposCobradorvista() {
	var inptNombreApellidoCobrador = document.getElementById('inptNombreApellidoCobradorVista').value
	var inptUsuarioCobrador = document.getElementById('inptUsuarioCobradorVista').value
	var inptPassCobrador = document.getElementById('inptPassCobradorVista').value
	var inptNroTelefCobrador = document.getElementById('inptNroTelefCobradorVista').value
	var inptZonaCobrador = document.getElementById('inptZonaCobradorVista').value
	var inptEstadoCobrador = "Activo"
	if (inptNombreApellidoCobrador == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DE COBRADOR", "#")
		return false;
	}
	if (inptUsuarioCobrador == "") {
		ver_vetana_informativa("FALTO INGRESAR EL USUARIO DE ACCESO", "#")
		return false;
	}
	if (inptPassCobrador == "") {
		ver_vetana_informativa("FALTO INGRESAR LA CONTRASEÑA DEL COBRADOR", "#")
		return false;
	}
	if (idFKZona == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA ZONA", "#")
		return false;
	}
	var accion = "nuevo";
	if(controlacceso("INSERTARLISTADOCOBRADORES","accion")==false){return;}
	abmcobrador(inptNombreApellidoCobrador, inptUsuarioCobrador, inptPassCobrador, inptNroTelefCobrador, idFKZona, inptEstadoCobrador, idAbmCobrador, accion);
}
function verCerrarVentanaAbmCobrador(d, l) {
	if (d == "1") {		
		if (l == "1") {
			if(controlacceso("INSERTARLISTADOCOBRADORES","accion")==false){return;}
			limpiarcamposCobrador()
		}
		$("div[id=divAbmCobrador2]").fadeIn(250)
		document.getElementById('divAbmCobrador1').style.display = "none"
	} else {
		$("div[id=divAbmCobrador1]").fadeIn(250)
		document.getElementById('divAbmCobrador2').style.display = "none"
	}
}
function verVentanaEditarCobrador() {
	if(controlacceso("EDITARLISTADOCOBRADORES","accion")==false){return;}
	if (idAbmCobrador == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmCobrador("1", "2")
}
var idAbmCobrador = ""
function obtenerdatosabmCobrador(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptNombreApellidoCobrador').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccCobrador').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptUsuarioCobrador').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptPassCobrador').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptNroTelefCobrador').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptZonaCobrador').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptEstadoCobrador').value = $(datostr).children('td[id="td_datos_7"]').html();
	inptSeleccAccesoCliente = $(datostr).children('td[id="td_datos_8"]').html();
	inptSeleccAccesoConsulta = $(datostr).children('td[id="td_datos_9"]').html();
	inptSeleccAccesoCuentas = $(datostr).children('td[id="td_datos_10"]').html();
	inptSeleccAccesoOffline = $(datostr).children('td[id="td_datos_11"]').html();
	inptSeleccAccesoRealizarCobranzas= $(datostr).children('td[id="td_datos_12"]').html();
	
	if(inptSeleccAccesoCliente=="si"){
	document.getElementById("inptSeleccAccesoCliente").checked=true
	}else{
	document.getElementById("inptSeleccAccesoCliente").checked=false
	}
	if(inptSeleccAccesoConsulta=="si"){
	document.getElementById("inptSeleccAccesoConsulta").checked=true
	}else{
	document.getElementById("inptSeleccAccesoConsulta").checked=false
	}
	if(inptSeleccAccesoCuentas=="si"){
	document.getElementById("inptSeleccAccesoCuentas").checked=true
	}else{
	document.getElementById("inptSeleccAccesoCuentas").checked=false
	}
	if(inptSeleccAccesoOffline=="si"){
	document.getElementById("inptSeleccAccesoOffline").checked=true
	}else{
	document.getElementById("inptSeleccAccesoOffline").checked=false
	}
	if(inptSeleccAccesoRealizarCobranzas=="si"){
	document.getElementById("inptSeleccAccesoRealizarCobranzas").checked=true
	}else{
	document.getElementById("inptSeleccAccesoRealizarCobranzas").checked=false
	}
	
	document.getElementById('btnAbmCobrador').value ="Editar datos";
	document.getElementById('btnEditarCobradores').style.backgroundColor="";
	idAbmCobrador = $(datostr).children('td[id="td_id"]').html();
	idFKZona = $(datostr).children('td[id="td_datos_6"]').html();

}
function checkaccesocobrador(d){
	if(document.getElementById(d).checked==true){
		document.getElementById(d).checked=false
	}else{
		document.getElementById(d).checked=true
	}
}
function verificarcamposCobrador() {
	var inptNombreApellidoCobrador = document.getElementById('inptNombreApellidoCobrador').value
	var inptUsuarioCobrador = document.getElementById('inptUsuarioCobrador').value
	var inptPassCobrador = document.getElementById('inptPassCobrador').value
	var inptNroTelefCobrador = document.getElementById('inptNroTelefCobrador').value
	var inptZonaCobrador = document.getElementById('inptZonaCobrador').value
	var inptEstadoCobrador = document.getElementById('inptEstadoCobrador').value
	var inptSeleccAccesoCliente="no"
	var inptSeleccAccesoConsulta="no"
	var inptSeleccAccesoCuentas="no"
	var inptSeleccAccesoOffline="no"
	var inptSeleccAccesoRealizarCobranzas="no"
	if(document.getElementById("inptSeleccAccesoCliente").checked==true){
	inptSeleccAccesoCliente="si"
	}
	if(document.getElementById("inptSeleccAccesoConsulta").checked==true){
	inptSeleccAccesoConsulta="si"
	}
	if(document.getElementById("inptSeleccAccesoCuentas").checked==true){
	inptSeleccAccesoCuentas="si"
	}
	if(document.getElementById("inptSeleccAccesoOffline").checked==true){
	inptSeleccAccesoOffline="si"
	}
	if(document.getElementById("inptSeleccAccesoRealizarCobranzas").checked==true){
	inptSeleccAccesoRealizarCobranzas="si"
	}
	if (inptNombreApellidoCobrador == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DE COBRADOR", "#")
		return false;
	}
	if (inptUsuarioCobrador == "") {
		ver_vetana_informativa("FALTO INGRESAR EL USUARIO DE ACCESO", "#")
		return false;
	}
	if (inptPassCobrador == "") {
		ver_vetana_informativa("FALTO INGRESAR LA CONTRASEÑA DEL COBRADOR", "#")
		return false;
	}
	if (idFKZona == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA ZONA", "#")
		return false;
	}
	var accion = "";
	if (idAbmCobrador != "") {
		accion = "editar";
	if(controlacceso("EDITARLISTADOCOBRADORES","accion")==false){return;}
	} else {		
		accion = "nuevo";
		if(controlacceso("INSERTARLISTADOCOBRADORES","accion")==false){return;}
	}
	abmcobrador(inptSeleccAccesoCliente,inptSeleccAccesoConsulta,inptSeleccAccesoCuentas,inptSeleccAccesoOffline,inptSeleccAccesoRealizarCobranzas,inptNombreApellidoCobrador, inptUsuarioCobrador, inptPassCobrador, inptNroTelefCobrador, idFKZona, inptEstadoCobrador, idAbmCobrador, accion);
}
function abmcobrador(accesocliente,accesoproducto,accesocuentas,modosinconexion,realizarcobranzas,nombre_persona, usu, con, telefono, idzona, estado, cod_persona, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_persona", cod_persona)
	datos.append("nombre_persona", nombre_persona)
	datos.append("telefono", telefono)
	datos.append("idzona", idzona)
	datos.append("usu", usu)
	datos.append("con", con)
	datos.append("estado", estado)
	datos.append("accesocliente", accesocliente)
	datos.append("accesoproducto", accesoproducto)
	datos.append("accesocuentas", accesocuentas)
	datos.append("modosinconexion", modosinconexion)
	datos.append("realizarcobranzas", realizarcobranzas)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcobrador.php",
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
					limpiarcamposCobrador()
				verCerrarVentanaAbmVistaCobrador("2")
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmCobrador = ""
					buscarabmCobrador()
					buscarvistacobrador()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function checkestadocobrador(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarCobrador1').checked=true
		document.getElementById('inptSeleccEstadoBuscarCobrador2').checked=false	
	}else{
		
		document.getElementById('inptSeleccEstadoBuscarCobrador1').checked=false
		document.getElementById('inptSeleccEstadoBuscarCobrador2').checked=true
	}
}
function buscarabmCobrador() {
if(controlacceso("BUSCARLISTADOCOBRADORES","accion")==false){return;}
	var codigo = document.getElementById('inptBuscarAbmCobrador1').value
	var cobrador = document.getElementById('inptBuscarAbmCobrador2').value
	var estado = "";
	if(document.getElementById('inptSeleccEstadoBuscarCobrador1').checked==true){
		 estado = "Activo";
	}else{
		estado = "Inactivo";
	}
	document.getElementById("table_abm_cobrador").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codigo": codigo,
		"cobrador": cobrador,
		"estado": estado,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcobrador.php",
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
			document.getElementById("table_abm_cobrador").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_cobrador").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_cobrador").innerHTML = datos_buscados
					document.getElementById("inptRegistroNroCobrador").value = datos[3]
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function limpiarcamposCobrador() {
	document.getElementById('inptNombreApellidoCobrador').value = "";
	document.getElementById('inptRegistroSeleccCobrador').value = "";
	document.getElementById('inptUsuarioCobrador').value = "";
	document.getElementById('inptPassCobrador').value = "";
	document.getElementById('inptNroTelefCobrador').value = "";
	document.getElementById('inptZonaCobrador').value = "1";
	document.getElementById('inptEstadoCobrador').value = "Activo";
	document.getElementById('btnAbmCobrador').value = "Guardar datos";
	document.getElementById('btnEditarCobradores').style.backgroundColor="#b7b7b7";
	idAbmCobrador = "";
	idFKZona="1"
}
var idFkCobrador = ""
var idFkVendedor = ""
var controlseleccvistaCobrador = ""
function vercerrarvistacobrador(d, ventana) {
	if (d == "1") {
			document.getElementById("divVistaCobrador").style.display = ""
			 document.getElementById("tdEfectoVistaCobrador").className="magictime slideLeftReturn"
		controlseleccvistaCobrador = ventana
		buscarvistacobrador();
	} else {
		document.getElementById("tdEfectoVistaCobrador").className="magictime slideRight"
		$("div[id=divVistaCobrador]").fadeOut(500)
	}
}
function buscarvistacobrador() {
	var buscador = document.getElementById('inptBuscarVistaCobrador').value
	document.getElementById("table_vista_cobrador").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"funt": "buscarvista"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmcobrador.php",
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
			document.getElementById("table_vista_cobrador").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_cobrador").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_vista_cobrador").innerHTML = datos_buscados
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function obtenerdatosvistacobrador(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	if (controlseleccvistaCobrador == "ventacobrador") {
		idFkCobrador = $(datostr).children('td[id="td_id"]').html();
		cobradorcredito = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptCobradorVenta').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptCobradorCargarPago').value = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptCobradorConfirmar').value = $(datostr).children('td[id="td_datos_1"]').html();

	}
	if (controlseleccvistaCobrador == "confimarpago") {
		cobradorcredito = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptCobradorConfirmar').value = $(datostr).children('td[id="td_datos_1"]').html();

	}
	if (controlseleccvistaCobrador == "cargarpago") {
		cobradorcargarpagos = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptCobradorCargarPago').value = $(datostr).children('td[id="td_datos_1"]').html();

	}
	if (controlseleccvistaCobrador == "arqueo") {
		cobradorarqueo = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptCobradorArqueo').value = $(datostr).children('td[id="td_datos_1"]').html();

	}
	if (controlseleccvistaCobrador == "comision") {
		codCobradorComision = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptCobradorComision').value = $(datostr).children('td[id="td_datos_1"]').html();

	}
	
	document.getElementById("divVistaCobrador").style.display = "none"
}
/*
ABM VENDEDOR
*/
function verCerrarAbmVendedor(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmVendedor").style.display==""){
		document.getElementById("divMinimizadoListadoVendedor").style.display="none"
		limpiarcamposbuscarvendedor()
		limpiarcamposVendedor()
document.getElementById("tdEfectoAbmVendedor").className="magictime vanishOut"
	$("div[id=divAbmVendedor]").fadeOut(500);	
	}else{
if(controlacceso("VERLISTADOVENDEDORES","accion")==false){return;}		
		document.getElementById("divAbmVendedor").style.display=""
 document.getElementById("tdEfectoAbmVendedor").className="magictime slideDownReturn"

		
	}
}
function limpiarcamposbuscarvendedor(){
	document.getElementById('inptBuscarAbmVendedor1').value=""
	document.getElementById('inptBuscarAbmVendedor2').value=""
	document.getElementById("table_abm_vendedor").innerHTML = ""
	document.getElementById("inptRegistroNroVendedor").value = ""
}
function minimizarabmvendedor(){
document.getElementById("tdEfectoAbmVendedor").className="magictime slideDown"
	$("div[id=divAbmVendedor]").fadeOut(500);
	document.getElementById("divMinimizadoListadoVendedor").style.display=""
}
function verCerrarVentanaAbmVendedorVista(d) {
	if (d == "1") {
	   if(controlacceso("INSERTARLISTADOVENDEDORES","accion")==false){return;}		
		$("div[id=divAbmVendedorVista]").fadeIn(250)
		document.getElementById("inptNombreApellidoVendedorVista").value = ""
		document.getElementById("inptNroTelefVendedorVista").value = ""

	} else {
		$("div[id=divAbmVendedorVista]").fadeOut(250)
	}
}
function verificarcamposVendedorVista() {

	var inptNombreApellidoVendedor = document.getElementById('inptNombreApellidoVendedorVista').value
	var inptNroTelefVendedor = document.getElementById('inptNroTelefVendedorVista').value
	var inptEstadoVendedor = "Activo"


	if (inptNombreApellidoVendedor == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL VENDEDOR", "#")
		return false;
	}



	var accion = "nuevo";

if(controlacceso("INSERTARVENDEDOR","accion")==false){
		
	//SIN PERMISO
	  return;
		}	
	abmvendedor(inptNombreApellidoVendedor, inptNroTelefVendedor, inptEstadoVendedor, idAbmVendedor, accion);
}
function verCerrarVentanaAbmVendedor(d, l) {
	
	
	if (d == "1") {
		
		
		if (l == "1") {
			if(controlacceso("INSERTARLISTADOVENDEDORES","accion")==false){return;}	
			limpiarcamposVendedor()
		}
		$("div[id=divAbmVendedor2]").fadeIn(250)
		document.getElementById('divAbmVendedor1').style.display = "none"
		
	} else {
		$("div[id=divAbmVendedor1]").fadeIn(250)
		document.getElementById('divAbmVendedor2').style.display = "none"
	}
}
function verVentanaEditarVendedor() {
	if(controlacceso("EDITARLISTADOVENDEDORES","accion")==false){return;}	
	if (idAbmVendedor == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmVendedor("1", "2")
}
var idAbmVendedor = ""
function obtenerdatosabmVendedor(datostr) {


	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});

	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptNombreApellidoVendedor').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccVendedor').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNroTelefVendedor').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptAbmLocalVendedor').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptEstadoVendedor').value = $(datostr).children('td[id="td_datos_3"]').html();
	idAbmVendedor = $(datostr).children('td[id="td_id"]').html();
document.getElementById('btnAbmVendedor').value = "Editar datos";
document.getElementById('btnEditarVendedor').style.backgroundColor="";



}
function verificarcamposVendedor() {

	var inptNombreApellidoVendedor = document.getElementById('inptNombreApellidoVendedor').value
	var inptNroTelefVendedor = document.getElementById('inptNroTelefVendedor').value
	var inptEstadoVendedor = document.getElementById('inptEstadoVendedor').value
	var inptAbmLocalVendedor = document.getElementById('inptAbmLocalVendedor').value


	if (inptNombreApellidoVendedor == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL VENDEDOR", "#")
		return false;
	}

if (inptAbmLocalVendedor == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL LOCAL", "#")
		return false;
	}

if (inptEstadoVendedor == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL ESTADO", "#")
		return false;
	}



	var accion = "";
	if (idAbmVendedor != "") {
		accion = "editar";
		if(controlacceso("EDITARLISTADOVENDEDORES","accion")==false){return;}	
	} else {
		if(controlacceso("INSERTARLISTADOVENDEDORES","accion")==false){return;}	
		accion = "nuevo";
	}
	abmvendedor(inptAbmLocalVendedor,inptNombreApellidoVendedor, inptNroTelefVendedor, inptEstadoVendedor, idAbmVendedor, accion);
}
function abmvendedor(cod_localfk,nombre, nrotelef, estado, idvendedor, accion) {

	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idvendedor", idvendedor)
	datos.append("nombre", nombre)
	datos.append("nrotelef", nrotelef)
	datos.append("estado", estado)
	datos.append("cod_localfk", cod_localfk)
	var OpAjax = $.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmvendedor.php",
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
					limpiarcamposVendedor()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmVendedor = ""
					buscarabmVendedor()
					 buscarVendedorSelec() 
					verCerrarVentanaAbmVendedorVista("2")
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function checkestadoVendedor(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarVendedor1').checked=true
		document.getElementById('inptSeleccEstadoBuscarVendedor2').checked=false	
	}else{
		
		document.getElementById('inptSeleccEstadoBuscarVendedor1').checked=false
		document.getElementById('inptSeleccEstadoBuscarVendedor2').checked=true
	}
}
function buscarabmVendedor() {
if(controlacceso("EDITARLISTADOVENDEDORES","accion")==false){return;}
	var codigo = document.getElementById('inptBuscarAbmVendedor1').value
	var vendedor = document.getElementById('inptBuscarAbmVendedor2').value
	var cod_local = document.getElementById('inptBuscarAbmCobrador3').value
	var estado = ""
	if(	document.getElementById('inptSeleccEstadoBuscarVendedor1').checked==true){
		estado = "Activo"
	}else{
		estado = "Inactivo"
	}
	document.getElementById("table_abm_vendedor").innerHTML = paginacargando

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codigo": codigo,
		"vendedor": vendedor,
		"estado": estado,
		"cod_local": cod_local,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmvendedor.php",
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
			document.getElementById("table_abm_vendedor").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_vendedor").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				   var datos_buscados = datos[2];
					document.getElementById("table_abm_vendedor").innerHTML = datos_buscados
					document.getElementById("inptRegistroNroVendedor").value = datos[3]


				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function buscarVendedorSelec() {

	
	document.getElementById("inptBuscarComisionVendedor1").innerHTML = ""

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscarselect"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmvendedor.php",
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
			document.getElementById("inptBuscarComisionVendedor1").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("inptBuscarComisionVendedor1").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				   var datos_buscados = datos[2];
					document.getElementById("inptBuscarComisionVendedor1").innerHTML = datos_buscados
				

				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function limpiarcamposVendedor() {
	document.getElementById('inptRegistroSeleccVendedor').value = "";
	document.getElementById('inptNombreApellidoVendedor').value = "";
	document.getElementById('inptNroTelefVendedor').value = "";
	document.getElementById('inptAbmLocalVendedor').value = "";
	document.getElementById('inptEstadoVendedor').value = "Activo";
	document.getElementById('btnAbmVendedor').value = "Guardar datos";
	document.getElementById('btnEditarVendedor').style.backgroundColor="#b7b7b7"
	idAbmVendedor = "";
}
var idFkVendedor1 = ""
var idFkVendedor2 = ""
var controlseleccvistavendedor = ""
function vercerrarvistavendedor(d, ventana) {

	if (d == "1") {
		document.getElementById("divVistaVendedor").style.display = ""
		 document.getElementById("tdEfectoVistaVendedor").className="magictime slideLeftReturn"
		controlseleccvistavendedor = ventana
		buscarvistavendedor();
	} else {
		document.getElementById("tdEfectoVistaVendedor").className="magictime slideRight"
		$("div[id=divVistaVendedor]").fadeOut(500)
	}

}
function buscarvistavendedor() {
	var buscador = document.getElementById('inptBuscarVistaVendedor').value
	var codlocal = document.getElementById('inptBuscarVistaVendedor1').value
	document.getElementById("table_vista_vendedor").innerHTML = paginacargando

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"codlocal": codlocal,
		"funt": "buscarvista"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmvendedor.php",
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
			document.getElementById("table_vista_vendedor").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_vendedor").innerHTML = ''
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

					document.getElementById("table_vista_vendedor").innerHTML = datos_buscados


				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function obtenerdatosvistavendedor(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});

	datostr.className = 'tableRegistroSelec'

	if (controlseleccvistavendedor == "venta1") {
		idFkVendedor1 = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptVendedorVenta1').value = $(datostr).children('td[id="td_datos_1"]').html();

	}
	if (controlseleccvistavendedor == "venta2") {
		idFkVendedor2 = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptVendedorVenta2').value = $(datostr).children('td[id="td_datos_1"]').html();

	}
	if (controlseleccvistavendedor == "comision") {
		codvendedorComision = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptVendedorComision').value = $(datostr).children('td[id="td_datos_1"]').html();

	}
	document.getElementById("divVistaVendedor").style.display = "none"







}

/*
ABM ZONA
*/
function verCerrarAbmZona(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmZona").style.display==""){
		document.getElementById("divMinimizadoListadoZona").style.display="none"
		limpiarcamposbuscarzona();
		limpiarcamposZona();
document.getElementById("tdEfectoAbmZona").className="magictime vanishOut"
	$("div[id=divAbmZona]").fadeOut(500);	
	}else{		
	if(controlacceso("VERLISTADODEZONAS","accion")==false){return;}
		document.getElementById("divAbmZona").style.display=""
		document.getElementById("tdEfectoAbmZona").className="magictime slideDownReturn"
		
	}
}
function limpiarcamposbuscarzona(){
		 document.getElementById('inptBuscarAbmZona1').value=""
	    document.getElementById('inptBuscarAbmZona2').value=""
		document.getElementById("table_abm_zona").innerHTML = ""
		document.getElementById("inptTotalRegistoZano").value = "";
}
function minimizarzonas(){
document.getElementById("tdEfectoAbmZona").className="magictime slideDown"
	$("div[id=divAbmZona]").fadeOut(500);
			document.getElementById("divMinimizadoListadoZona").style.display=""
}
function verCerrarVentanaAbmZona(d, l) {
	if (d == "1") {	
		if (l == "1") {
			if(controlacceso("INSERTARLISTADODEZONAS","accion")==false){return;}
			limpiarcamposZona()
		}
		$("div[id=divAbmZona2]").fadeIn(250)
		document.getElementById('divAbmZona1').style.display = "none"
	} else {
		$("div[id=divAbmZona1]").fadeIn(250)
		document.getElementById('divAbmZona2').style.display = "none"
	}
}
function verVentanaEditarZona() {
	if(controlacceso("EDITARLISTADODEZONAS","accion")==false){return;}
	if (idAbmZona == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmZona("1", "2")
}
var idAbmZona = ""
function obtenerdatosabmZona(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});

	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptNombreZona').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccZona').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptEstadoZona').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptEncargadoZona').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('btnAbmZona').value ="Editar datos";
	document.getElementById('btnEditarZonas').style.backgroundColor="";
	idAbmZona = $(datostr).children('td[id="td_id"]').html();




}
function verificarcamposZona() {
	var inptNombreZona = document.getElementById('inptNombreZona').value
	var inptEstadoZona = document.getElementById('inptEstadoZona').value
	var inptEncargadoZona = document.getElementById('inptEncargadoZona').value
	if (inptEncargadoZona == "") {
		ver_vetana_informativa("FALTO INGRESAR EL ENCARGADO", "#")
		return false;
	}
	if (inptNombreZona == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DE LA ZONA", "#")
		return false;
	}
	var accion = "";
	if (idAbmZona != "") {
		accion = "editar";
		if(controlacceso("EDITARLISTADODEZONAS","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("INSERTARLISTADODEZONAS","accion")==false){return;}
	}
	abmzonas(inptNombreZona, inptEstadoZona, idAbmZona, inptEncargadoZona, accion);
}
function verificarcamposZonaVista() {
	var inptNombreZona = document.getElementById('inptNombreZonaVista').value
	var inptEncargadoZona= document.getElementById('inptEncargadoZonaVista').value
	var inptEstadoZona = "Activo"
	if (inptEncargadoZona == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL ENCARGADO", "#")
		return false;
	}
	if (inptNombreZona == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DE LA ZONA", "#")
		return false;
	}
	var accion = "nuevo";
	if(controlacceso("ZONA","insertar")==false){
		
	//SIN PERMISO
	  return;
		}
	abmzonas(inptNombreZona, inptEstadoZona, idAbmZona, inptEncargadoZona , accion);
}
function abmzonas(nombre, estado, idzona, encargado , accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idzona", idzona)
	datos.append("nombre", nombre)
	datos.append("estado", estado)
	datos.append("encargado", encargado)

	var OpAjax = $.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmzona.php",
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
		
		success: function (responseText) {
			verCerrarEfectoCargando("")
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					limpiarcamposZona()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmZona = ""
					buscarabmZona()
					buscarabmZonaOption();
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
			var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function checkestadoZonas(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarZona1').checked=true
		document.getElementById('inptSeleccEstadoBuscarZona2').checked=false	
	}else{
		
		document.getElementById('inptSeleccEstadoBuscarZona1').checked=false
		document.getElementById('inptSeleccEstadoBuscarZona2').checked=true
	}
}
function buscarabmZona() {
if(controlacceso("BUSCARLISTADODEZONAS","accion")==false){return;}
	var codigo = document.getElementById('inptBuscarAbmZona1').value
	var nombre = document.getElementById('inptBuscarAbmZona2').value
	var estado = ""
	if(document.getElementById('inptSeleccEstadoBuscarZona1').checked==true){
		estado = "Activo"
	}else{
		estado = "Inactivo"
	}
	document.getElementById("table_abm_zona").innerHTML = paginacargando

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codigo": codigo,
		"nombre": nombre,
		"estado": estado,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmzona.php",
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
			document.getElementById("table_abm_zona").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_zona").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
					document.getElementById("table_abm_zona").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoZano").value = datos[3];
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function limpiarcamposZona() {
	document.getElementById('inptRegistroSeleccZona').value = "";
	document.getElementById('inptEncargadoZona').value = "";
	document.getElementById('inptNombreZona').value = "";
	document.getElementById('inptEstadoZona').value = "Activo";
	document.getElementById('btnAbmZona').value ="Guardar datos";
	document.getElementById('btnEditarZonas').style.backgroundColor="#b7b7b7";
	idAbmZona = "";
}
var controlVistaZona = "";
function verCerrarVistaZona(d, ventana) {
	if (d == "1") {
		$("div[id=divVistaZona]").fadeIn(250)
		controlVistaZona = ventana
		buscarVistaZona()
	} else {
		$("div[id=divVistaZona]").fadeOut(250)
	}
}
function verCerrarNuevoRegistroZona(d, ventana) {
	if (d == "1") {
		$("div[id=divAbmZonaVista]").fadeIn(250)
	} else {
		$("div[id=divAbmZonaVista]").fadeOut(250)
	}
}
function buscarVistaZona() {
	var buscador = document.getElementById('inptBuscarVistaZona').value
	document.getElementById("table_vista_zona").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"funt": "buscarvista"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmzona.php",
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
			document.getElementById("table_vista_zona").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_zona").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				var datos_buscados = datos[2];
				document.getElementById("table_vista_zona").innerHTML = datos_buscados
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
var idFKZona = "";
function obtenerdatosVistaZona(datostr) {
	idFKZona = $(datostr).children('td[id="td_id"]').html();
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	if (controlVistaZona == "cobradorabm") {
		document.getElementById('inptZonaCobrador').value = $(datostr).children('td[id="td_datos_1"]').html();

	}
	if (controlVistaZona == "clienteabm") {
		document.getElementById('inptZonaCliente').value = $(datostr).children('td[id="td_datos_1"]').html();
	}
	if (controlVistaZona == "clienteabmvista") {
		document.getElementById('inptZonaClienteVista').value = $(datostr).children('td[id="td_datos_1"]').html();
	}
	if (controlVistaZona == "cobradorabmvista") {
		document.getElementById('inptZonaCobradorVista').value = $(datostr).children('td[id="td_datos_1"]').html();
	}
	document.getElementById("divVistaZona").style.display = "none"
}
function buscarabmZonaOption() {
	document.getElementById("inputSelectZonaInfHistorialVenta").innerHTML = "";
	document.getElementById("inptBuscarAbmCliente4").innerHTML = "";
	document.getElementById("inputSelectZonaInfCuentasAcobrarinforme").innerHTML = "";
	document.getElementById("inputSelectZonaInfCuentasAcobrar").innerHTML = "";
	document.getElementById("inputSelectZonaArqueo").innerHTML = "";
	document.getElementById("inputSelectZonaComisionCobrador").innerHTML = "";
	document.getElementById("inputSelectZonaComisionClientesInactivos").innerHTML = "";
	document.getElementById("inptZonaMoroso").innerHTML = "";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscaroption"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmzona.php",
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
					document.getElementById("inputSelectZonaInfHistorialVenta").innerHTML = datos_buscados
					document.getElementById("inputSelectZonaInfCuentasAcobrarinforme").innerHTML = datos_buscados
					document.getElementById("inptBuscarAbmCliente4").innerHTML = datos_buscados
					document.getElementById("inputSelectZonaInfCuentasAcobrar").innerHTML = datos_buscados
					document.getElementById("inputSelectZonaArqueo").innerHTML = datos_buscados
					document.getElementById("inputSelectZonaComisionCobrador").innerHTML = datos_buscados
					document.getElementById("inputSelectZonaComisionClientesInactivos").innerHTML = datos_buscados
					document.getElementById("inptZonaMoroso").innerHTML = datos_buscados;


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
ABM CAJA
*/
function verCerrarAbmCaja(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmCaja").style.display==""){
		document.getElementById("divMinimizadoListadoCaja").style.display="none"
		limpiarcamposCaja()
		limpiarcamposbuscarcajas()
document.getElementById("tdEfectoAbmCaja").className="magictime vanishOut"
	$("div[id=divAbmCaja]").fadeOut(500);	
	}else{		
		
		if(controlacceso("VERLISTADODECAJA","accion")==false){return;}
		
		document.getElementById("divAbmCaja").style.display=""
document.getElementById("tdEfectoAbmCaja").className="magictime slideDownReturn"
	}
}
function limpiarcamposbuscarcajas(){
		 document.getElementById('inptBuscarAbmCaja1').value=""
	    document.getElementById('inptBuscarAbmCaja2').value=""
		document.getElementById("table_abm_Caja").innerHTML = ""
		document.getElementById("inptTotalRegistoCaja").value = "";
}
function minimizarabmcaja(){
document.getElementById("tdEfectoAbmCaja").className="magictime slideDown"
	$("div[id=divAbmCaja]").fadeOut(500);	
	document.getElementById("divMinimizadoListadoCaja").style.display=""
}
function verCerrarVentanaAbmCaja(d, l) {
	if (d == "1") {		
		if (l == "1") {
			if(controlacceso("INSERTARLISTADODECAJA","accion")==false){return;}
			limpiarcamposCaja()
		}
		$("div[id=divAbmCaja2]").fadeIn(250)
		document.getElementById('divAbmCaja1').style.display = "none"
	} else {
		$("div[id=divAbmCaja1]").fadeIn(250)
		document.getElementById('divAbmCaja2').style.display = "none"
	}
}
function verVentanaEditarCaja() {
	if (idAbmCaja == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	if(controlacceso("EDITARLISTADODECAJA","accion")==false){return;}
	verCerrarVentanaAbmCaja("1", "2")
}
var idAbmCaja = ""
function obtenerdatosabmCaja(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptNombreCaja').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccCaja').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptExpedicionCaja').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptlocalCaja').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptEstadoCaja').value = $(datostr).children('td[id="td_datos_4"]').html();
				document.getElementById('inptUsuarioInsertadoPor').value=$(datostr).children('td[id="td_datos_100"]').html()
	document.getElementById('inptFechaInsertadoPor').value=$(datostr).children('td[id="td_datos_102"]').html()
	document.getElementById('inptUsuarioEditadoPor').value=$(datostr).children('td[id="td_datos_101"]').html()
	document.getElementById('inptFechaEditadoPor').value=$(datostr).children('td[id="td_datos_103"]').html()
	
	document.getElementById('btnAbmCaja').value = "Editar datos";
	document.getElementById('btnEditarDatosCaja').style.backgroundColor="";
	document.getElementById('btnAuditoriaCajas').style.backgroundColor="#673ab7";
	idAbmCaja = $(datostr).children('td[id="td_id"]').html();
}
function verificarcamposCaja() {
	var inptNombreCaja = document.getElementById('inptNombreCaja').value
	var inptExpedicionCaja = document.getElementById('inptExpedicionCaja').value
	var inptlocalCaja = document.getElementById('inptlocalCaja').value
	var inptEstadoCaja = document.getElementById('inptEstadoCaja').value
	if (inptNombreCaja == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DE LA CAJA", "#")
		return false;
	}

	var accion = "";
	if (idAbmCaja != "") {
		accion = "editar";
		if(controlacceso("EDITARLISTADODECAJA","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("INSERTARLISTADODECAJA","accion")==false){return;}
	}
	abmCaja(inptNombreCaja, inptExpedicionCaja ,inptlocalCaja ,inptEstadoCaja , idAbmCaja, accion);
}
function abmCaja(cajanro, puntoexpedicion ,cod_localFK ,estado , idcaja, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idcaja", idcaja)
	datos.append("cajanro", cajanro)
	datos.append("puntoexpedicion", puntoexpedicion)
	datos.append("estado", estado)
	datos.append("cod_localFK", cod_localFK)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmCaja.php",
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
					limpiarcamposCaja()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmCaja = ""
					buscarabmCaja();
					buscarOptionCaja();
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function checkestadoCaja(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarCaja1').checked=true
	document.getElementById('inptSeleccEstadoBuscarCaja2').checked=false	
	}else{
	document.getElementById('inptSeleccEstadoBuscarCaja1').checked=false
	document.getElementById('inptSeleccEstadoBuscarCaja2').checked=true
	}
}
function buscarabmCaja() {
if(controlacceso("BUSCARLISTADODECAJA","accion")==false){return;}
	var codigo = document.getElementById('inptBuscarAbmCaja1').value
	var descrip = document.getElementById('inptBuscarAbmCaja2').value
	var estado = ""
	if(document.getElementById('inptSeleccEstadoBuscarCaja1').checked==true){
		estado = "Activo"
	}else{
		estado = "Inactivo"
	}
	document.getElementById("table_abm_Caja").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codigo": codigo,
		"descrip": descrip,
		"estado": estado,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmCaja.php",
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
			document.getElementById("table_abm_Caja").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_Caja").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_Caja").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoCaja").value = datos[3];
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarOptionCaja() {	
	document.getElementById("inptcajaAperturaCierreCaja").innerHTML = ""
	document.getElementById("inptSeleccPuntoExpedicionVenta").innerHTML = ""
	//document.getElementById("inptCajalNroFactura").innerHTML = ""
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_local": cod_localFKUSer,
		"funt": "buscaroption"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmCaja.php",
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
			document.getElementById("inptcajaAperturaCierreCaja").innerHTML = ''
			document.getElementById("inptSeleccPuntoExpedicionVenta").innerHTML = ''
			//document.getElementById("inptCajalNroFactura").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("inptcajaAperturaCierreCaja").innerHTML = ''
			document.getElementById("inptSeleccPuntoExpedicionVenta").innerHTML = ''
			//document.getElementById("inptCajalNroFactura").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					var datos_expedicion = datos[4];
					document.getElementById("inptcajaAperturaCierreCaja").innerHTML = datos_buscados
					document.getElementById("inptSeleccPuntoExpedicionVenta").innerHTML = datos_expedicion
					//document.getElementById("inptCajalNroFactura").innerHTML = datos_expedicion
					document.getElementById("inptSeleccPuntoExpedicionVenta").value="";
					seleccionarcaja()
					
					controldecaja()
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function seleccionarcaja(){
	document.getElementById("inptcajaAperturaCierreCaja").value=cajapredeterminada

	document.getElementById("pCaja").innerHTML = $("select[id=inptcajaAperturaCierreCaja]").children(":selected").text()
	
}
function buscarOptionCaja2(d) {

	var codLocal="";
	if(d=="1"){
		var codLocal=document.getElementById("inptlocalAperturaCierre").value;
		document.getElementById("inptcajaAperturaCierreCaja").innerHTML = ""
	}
	
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_local": codLocal,
		"funt": "buscaroption"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmCaja.php",
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
				if(d=="1"){
		document.getElementById("inptcajaAperturaCierreCaja").innerHTML = ""
	}
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
						if(d=="1"){
		document.getElementById("inptcajaAperturaCierreCaja").innerHTML = ""
	}
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
				
							if(d=="1"){
			document.getElementById("inptcajaAperturaCierreCaja").innerHTML = datos_buscados
			controldecaja()
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
function limpiarcamposCaja() {
	document.getElementById('inptNombreCaja').value = "";
	document.getElementById('inptExpedicionCaja').value = "";
	document.getElementById('inptRegistroSeleccCaja').value = "";
	document.getElementById('inptEstadoCaja').value = "Activo";
	document.getElementById('btnEditarDatosCaja').style.backgroundColor="#d5d3d3";
	document.getElementById('btnAuditoriaCajas').style.backgroundColor="#d5d3d3";
	document.getElementById('btnAbmCaja').value = "Guardar datos";
	idAbmCaja = "";
}
/*
ABM APERTURA CIERRE CAJA
*/
function verCerrarVentanaAbmAperturaCierreCaja(){
	if(document.getElementById("divAbmAperturaCierreCaja").style.display==""){
		document.getElementById("tdEfectoAbmAperturaCierreCaja").className="magictime vanishOut"
	$("div[id=divAbmAperturaCierreCaja]").fadeOut(500);	
		
	}else{	
      if(controlacceso(controlaperturacierrecaja,"accion")==false){
			//SIN PERMISO
	  return;
		}
		
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
     
	  if(controlaperturacierrecaja!="ABRIRCERRARCAJA"){
	  document.getElementById('inptFechaCierreAperturaCierreCaja').value =  anho+"-" + mes + "-" +dia +"T"+hora+":"+minuto;
		  
	  }else{
		  document.getElementById('inptFechaAperturaCierreCaja').value =  anho+"-" + mes + "-" +dia +"T"+hora+":"+minuto;
		  
	  }		
		document.getElementById("divAbmAperturaCierreCaja").style.display=""
		 document.getElementById("tdEfectoAbmAperturaCierreCaja").className="magictime slideDownReturn"
		document.getElementById("imgVolverCerrarApCieCaja").style.display=""
		document.getElementById("imgVolverAtrasApCieCaja").style.display="none"
		 buscartotalmovimientos()
		
		
	}
}
function verCerrarVentanaAbmAperturaCierreCaja1(){
	if(document.getElementById("divAbmAperturaCierreCaja").style.display==""){
		//document.getElementById("divAbmAperturaCierreCaja").style.display="none"
		document.getElementById("tdEfectoAbmAperturaCierreCaja").className="magictime vanishOut"
	$("div[id=divAbmAperturaCierreCaja]").fadeOut(500);	
	}else{		
	if(controlacceso(controlaperturacierrecaja,"accion")==false){
			//SIN PERMISO
	  return;
		}
		document.getElementById("divAbmAperturaCierreCaja").style.display=""
		 document.getElementById("tdEfectoAbmAperturaCierreCaja").className="magictime slideDownReturn"
		document.getElementById("imgVolverCerrarApCieCaja").style.display="none"
		document.getElementById("imgVolverAtrasApCieCaja").style.display=""
	
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
     
	  if(controlaperturacierrecaja!="ABRIRCERRARCAJA"){
	  document.getElementById('inptFechaCierreAperturaCierreCaja').value =  anho+"-" + mes + "-" +dia +"T"+hora+":"+minuto;
		  
	  }else{
		  document.getElementById('inptFechaAperturaCierreCaja').value =  anho+"-" + mes + "-" +dia +"T"+hora+":"+minuto;
		  
	  }		
		
	}
}
var controlaperturacierrecaja="ABRIRCERRARCAJA";
var codCajeroapertura="";
function controldecaja() {
	var caja = document.getElementById('inptcajaAperturaCierreCaja').value
	var codlocal = document.getElementById('inptlocalAperturaCierre').value
	document.getElementById('PTituloApCieCaja').innerHTML="Cargando datos de caja...";
	document.getElementById('btnAbmAperturaCierreCaja').value="Cargando...";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_local": codlocal,
		"buscar": caja,
		"Usuario": userid,
		"funt": "controldecaja"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmaperturacierrecaja.php",
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
			
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					if(datos[2]=="1"){
						document.getElementById("inptEstadoAperturaCierreCaja").value="Cerrado"
						document.getElementById('inptMontoCierreCierreCaja').disabled=false
						document.getElementById('inptFechaCierreAperturaCierreCaja').disabled=true
						document.getElementById('inptFechaAperturaCierreCaja').disabled=true
						document.getElementById('inptMontoAperturaCierreCaja').disabled=true						
						idabmAperturacierrecaja=datos[3];
						$("input[id=inptFechaAperturaCierreCaja]").attr("type","text")
						document.getElementById('inptFechaAperturaCierreCaja').value=datos[7];
						document.getElementById('inptMontoAperturaCierreCaja').value=datos[5];
						document.getElementById('inptMontoRecaudadoCierreCaja').value=datos[10];
						document.getElementById('inptcajeroAperturaCierreCaja').value=datos[12];
						codCajeroapertura=datos[11];
						document.getElementById('btnAbmAperturaCierreCaja').value="Cerrar caja";
						document.getElementById('PTituloApCieCaja').innerHTML="Cerrar caja";
                        controlaperturacierrecaja="CERRARCERRARCAJA"						
					}else{
						document.getElementById("inptEstadoAperturaCierreCaja").value="Activo"
						document.getElementById('inptMontoCierreCierreCaja').disabled=true
						document.getElementById('inptFechaCierreAperturaCierreCaja').disabled=true
						document.getElementById('inptFechaAperturaCierreCaja').disabled=true
						document.getElementById('inptMontoAperturaCierreCaja').disabled=false
							 if(accesosuser["CAMBIARMONTOAPERTURA"]["accion"]=="NO"){
	         document.getElementById('inptMontoAperturaCierreCaja').disabled=true
	         }
						document.getElementById('inptMontoAperturaCierreCaja').value=datos[3];
						document.getElementById('inptFechaCierreAperturaCierreCaja').value="";
						$("input[id=inptFechaAperturaCierreCaja]").attr("type","datetime-local")
						document.getElementById('inptMontoCierreCierreCaja').value="0";
						document.getElementById('inptMontoRecaudadoCierreCaja').value="0";
						document.getElementById('inptcajeroAperturaCierreCaja').value=document.getElementById("lblUser").innerHTML;
						 controlaperturacierrecaja="ABRIRCERRARCAJA"	
						 codCajeroapertura=userid
						 document.getElementById('btnAbmAperturaCierreCaja').value="Iniciar caja";
						 document.getElementById('PTituloApCieCaja').innerHTML="Apertura de caja";
						 idabmAperturacierrecaja="";

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
function buscartotalmovimientos() {
	if(idabmAperturacierrecaja==""){
		return
	}
	document.getElementById('inptMontoRecaudadoCierreCaja').value="...."
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idArqeoFk": idabmAperturacierrecaja,
		"funt": "buscarmoviemientocaja"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmaperturacierrecaja.php",
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
			
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				
				  document.getElementById('inptMontoRecaudadoCierreCaja').value=datos[2];
				  DetalleticketCaja=datos[3];
						
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
						var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
var idabmAperturacierrecaja="";
function verificarcamposaperturacierredecaja() {
	
	var movimiento = document.getElementById("inptMontoRecaudadoCierreCaja").value
	if(movimiento=="...." || movimiento==""){
		
		return;
	}
	
	//var inptlocalAperturaCierre = document.getElementById('inptlocalAperturaCierre').value
	var inptlocalAperturaCierre = cod_localFKUSer
	var inptcajaAperturaCierreCaja = document.getElementById('inptcajaAperturaCierreCaja').value
	var inptMontoAperturaCierreCaja = document.getElementById('inptMontoAperturaCierreCaja').value
	var inptFechaAperturaCierreCaja = document.getElementById('inptFechaAperturaCierreCaja').value
	var inptFechaCierreAperturaCierreCaja = document.getElementById('inptFechaCierreAperturaCierreCaja').value
	var inptMontoCierreCierreCaja = document.getElementById('inptMontoCierreCierreCaja').value
	var inptEstadoAperturaCierreCaja = document.getElementById('inptEstadoAperturaCierreCaja').value
	if (inptlocalAperturaCierre == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN LOCAL", "#")
		return false;
	}
	if (codCajeroapertura == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN COBRADOR", "#")
		return false;
	}

  if(controlacceso(controlaperturacierrecaja,"accion")==false){
			//SIN PERMISO
	  return;
		}
  
	var accion = "";
	if (idabmAperturacierrecaja != "") {
		accion = "editar";
		if(inptMontoCierreCierreCaja==""){
		ver_vetana_informativa("FALTO SELECCIONAR LA CIERRE", "#")
		return false;
		}
		if(inptFechaCierreAperturaCierreCaja==""){
			
			ver_vetana_informativa("FALTO INGRESAR EL MONTO APERTURA", "#")
		return false;
		}
		
		
			
	} else {
		accion = "nuevo";
		if(inptMontoAperturaCierreCaja==""){
			ver_vetana_informativa("FALTO INGRESAR EL MONTO APERTURA", "#")
		return false;
		}
		
		if(inptFechaAperturaCierreCaja==""){
			ver_vetana_informativa("FALTO SELECCIONAR LA APERTURA", "#")
		return false;
		}
	}
	abmaperturacierrecaja(codCajeroapertura,inptlocalAperturaCierre, inptcajaAperturaCierreCaja, inptMontoAperturaCierreCaja,inptFechaAperturaCierreCaja,inptMontoCierreCierreCaja,inptFechaCierreAperturaCierreCaja,inptEstadoAperturaCierreCaja,idabmAperturacierrecaja, accion);
}
function abmaperturacierrecaja(codusuarioap,cod_local, caja_idcaja, montoapertura,fechaapertura,montocierre,fechacierre,estado,idarqueocaja, accion){
verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idarqueocaja", idarqueocaja)
	datos.append("cod_local", cod_local)
	datos.append("caja_idcaja", caja_idcaja)
	datos.append("montoapertura", montoapertura)
	datos.append("montocierre", montocierre)
	datos.append("fechaapertura", fechaapertura)
	datos.append("fechacierre", fechacierre)
	datos.append("estado", estado)
	datos.append("codusuarioap", codusuarioap)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmaperturacierrecaja.php",
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
					
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					if(estado=="Activo"){
					 ImprimirTicketReportCaja()
					}else{
						ImprimirTicketReportCierreCaja()
					}
					controldecaja()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});

}
function vercerraropcioneimpresionapcie(d,v){
	if(d=="1"){
		document.getElementById("divOpcionesImpresionArpeturacierre").style.display=""
	}else{
		document.getElementById("divOpcionesImpresionArpeturacierre").style.display="none"
	}
}
function vercerrarvistaapcie(d,v){
	if(d=="1"){
		document.getElementById("divVistaArqueocierrecaja").style.display=""
		document.getElementById("tdEfectoArqueoCierroCaja").className="magictime slideLeftReturn"
	}else{
		//document.getElementById("divVistaArqueocierrecaja").style.display="none"
		document.getElementById("tdEfectoArqueoCierroCaja").className="magictime slideRight"
$("div[id=divVistaArqueocierrecaja]").fadeOut(500);	
	}
}

function vercerrarfiltrosBuscarVistaAperturaCierre(d,v){
	if(d=="1"){
		document.getElementById("divFiltrosAperturaCierreCaja").style.display=""
	
		if(v=="1"){
			bloquearBuscarVistaAperturaCaja("1")
		}
		if(v=="2"){
			bloquearBuscarVistaAperturaCaja("2")
		}
	}else{
		document.getElementById("divFiltrosAperturaCierreCaja").style.display="none"
	}
}

function buscarvistaaperturacierrecaja() {
	var caja = document.getElementById('inptBuscarVistaCaja1').value
	var estado = document.getElementById('inptBuscarVistaCaja2').value
	var local = document.getElementById('inptlocalVistaApCie').value
	var fechaapertura = document.getElementById('inptBuscarVistaCaja3').value
	var fechafin = document.getElementById('inptBuscarVistaCaja4').value
	var usuario = document.getElementById('inptBuscarVistaCaja5').value
	
	vercerrarfiltrosBuscarVistaAperturaCierre("2","2")
	document.getElementById("table_vista_ap_cie").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"caja": caja,
		"estado": estado,
		"local": local,
		"fechaapertura": fechaapertura,
		"fechafin": fechafin,
		"usuario": usuario,
		"funt": "buscarvista"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmaperturacierrecaja.php",
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
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("table_vista_ap_cie").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_ap_cie").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {	
					var datos_buscados = datos[2];
					document.getElementById("table_vista_ap_cie").innerHTML = datos_buscados
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
var idArqeoFk="";
function obtenerdatosaperturacierrecaja(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptBuscarVistaApCie1').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptBuscarVistaApCie2').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptBuscarVistaApCie3').value = $(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptBuscarVistaApCie4').value = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptBuscarVistaApCie5').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptBuscarVistaApCie6').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptBuscarVistaApCie7').value = $(datostr).children('td[id="td_datos_9"]').html();
	idArqeoFk = $(datostr).children('td[id="td_id_1"]').html();
	buscarinformecaja()
	document.getElementById("divVistaArqueocierrecaja").style.display="none"
}
function ImprimirTicketDeCaja(){
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
if(idabmAperturacierrecaja==""){
	ver_vetana_informativa("NO TIENES UNA CAJA ABIERTA")
		return
	}
pagina="<div  style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >REPORTE DE CAJA</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Imp.:</b></td>"
+"<td style=''>"+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Local:</b></td>"
+"<td style=''>"+ $("select[id=inptlocalAperturaCierre]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Caja:</b></td>"
+"<td style=''>"+ $("select[id=inptcajaAperturaCierreCaja]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Inicio :</b></td>"
+"<td style=''>"+ document.getElementById("inptFechaAperturaCierreCaja").value+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Monto Inicio:</b></td>"
+"<td style=''>"+document.getElementById("inptMontoAperturaCierreCaja").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total en caja:</b></td>"
+"<td style=''>"+document.getElementById("inptMontoRecaudadoCierreCaja").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:75%'><b>Descripción</b></td>"
+"<td style='width:25%'><b>Monto</b></td>"
+"</tr>"
+"</table>"
+DetalleticketCaja
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Cajero :</b></td>"
+"<td style=''>"+document.getElementById("lblUser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
+"</div>"


var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("https://systemsrepository.com/GoodVentaByR/system/reportticket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
}

function ImprimirTicketReportCaja(){
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
pagina="<div  style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >REPORTE DE CAJA</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Imp.:</b></td>"
+"<td style=''>"+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Local:</b></td>"
+"<td style=''>"+ $("select[id=inptlocalAperturaCierre]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Caja:</b></td>"
+"<td style=''>"+ $("select[id=inptcajaAperturaCierreCaja]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Inicio :</b></td>"
+"<td style=''>"+ document.getElementById("inptFechaAperturaCierreCaja").value+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Monto Inicio:</b></td>"
+"<td style=''>"+document.getElementById("inptMontoAperturaCierreCaja").value+" Gs.</td>"
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
+"</div>"


var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("https://systemsrepository.com/GoodVentaByR/system/reportticket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
}

function ImprimirTicketDespacho(){
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
pagina="<div  style='background-color:#fff;'>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >DESPACHO DE PRODUCTO</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Imp.:</b></td>"
+"<td style=''>"+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Producto :</b></td>"
+"<td style=''>"+ document.getElementById("inptNombreProducto").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:150px'><b>Enviado de (Local):</b></td>"
+"<td style=''>"+ $("select[id=inptlocalProducto]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:150px'><b>Enviado a (Local):</b></td>"
+"<td style=''>"+ $("select[id=inptLocalProductoEnviarA]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Stock :</b></td>"
+"<td style=''>"+ document.getElementById("inptStockProductoEnviarA").value+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Usuario Responsable :</b></td>"
+"<td style=''>"+document.getElementById("lblUser").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</div>"


var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("https://systemsrepository.com/GoodVentaByR/system/reportticket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
}

function ImprimirListadoDespacho(){
	
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
	pagina =
"<table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Encargado</b></p>"
+"<p class='pTituloC' >"+document.getElementById("ptituloUser2").innerHTML+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Local de Carga</b></p>"
+"<p class='pTituloC' >"+ $("select[id=inptlocaluser]").children(":selected").text()+"</p>"
+"</td>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Fecha de impresión</b></p>"
+"<p class='pTituloC' >"+fechaimpresion+"</p>"
+"</td>"
+"</tr>"
+"</table>"
+"<br><br><center><h1 class='pTituloD' >LISTADO DE PRODUCTOS DESPACHADOS</h1><br></center>"
+"<br><br><br><table style='width:100%'>"
+"<tr>"
+"<td style='width:40%;text-align:center'>"
+"<p class='pTituloC'><b>Firma del Responsable</b></p>"
+"</td>"
+"<td style='width:40%;text-align:center'>"
+"<p class='pTituloC'><b>Firma del Repositor</b></p>"
+"</td>"
+"</tr>"
+"</table><br><br><br>"
paginaPie =
"<br><br><table class='TableRepor0' style='width:100%'>"
+"<tr>"
+"<td style='width:20%;text-align:left'>"
+"<p class='pTituloC'><b>Total Registro</b></p>"
+"<p class='pTituloC' >"+ document.getElementById("inptRegistroNroListadoDespacho").value+"</p>"
+"</td>"
+"</tr>"
+"</table>"

document.getElementById("divCabeceraImpresiones").innerHTML=pagina
document.getElementById("divPieImpresiones").innerHTML=paginaPie
document.getElementById("tbTitulosImpresiones").innerHTML=document.getElementById("tdImpresionDespachadoZ").innerHTML
document.getElementById("tbDatosImpresiones").innerHTML=document.getElementById("table_abm_listado_despacho").innerHTML

	var documento=document.getElementById("DivImpresiones").innerHTML;

	 localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "reporte");
	 window.open("https://systemsrepository.com/GoodVentaByR/system/reportInformes.html");

     
}

function ImprimirTicketReportCierreCaja(){
	

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
pagina="<div  style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >REPORTE DE CAJA</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Imp.:</b></td>"
+"<td style=''>"+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Local:</b></td>"
+"<td style=''>"+ $("select[id=inptlocalAperturaCierre]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Caja:</b></td>"
+"<td style=''>"+ $("select[id=inptcajaAperturaCierreCaja]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Inicio :</b></td>"
+"<td style=''>"+ document.getElementById("inptFechaAperturaCierreCaja").value+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Monto Inicio:</b></td>"
+"<td style=''>"+document.getElementById("inptMontoAperturaCierreCaja").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Monto Cierre:</b></td>"
+"<td style=''>"+document.getElementById("inptMontoCierreCierreCaja").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total en caja:</b></td>"
+"<td style=''>"+document.getElementById("inptMontoRecaudadoCierreCaja").value+" Gs.</td>"
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
+"</div>"


var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("https://systemsrepository.com/GoodVentaByR/system/reportticket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
}

function ImprimirTicketReportCaja2(){
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
pagina="<div  style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' > RE-IMPRESION CAJA</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Imp.:</b></td>"
+"<td style=''>"+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Local:</b></td>"
+"<td style=''>"+ $("select[id=inptlocalVistaApCie]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Caja:</b></td>"
+"<td style=''>"+ document.getElementById("inptBuscarVistaApCie1").value +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Apertura :</b></td>"
+"<td style=''>"+ document.getElementById("inptBuscarVistaApCie5").value+"</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Monto Apertura:</b></td>"
+"<td style=''>"+document.getElementById("inptBuscarVistaApCie3").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Monto Cierre:</b></td>"
+"<td style=''>"+document.getElementById("inptBuscarVistaApCie4").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Total en caja:</b></td>"
+"<td style=''>"+document.getElementById("inptTotalConsularCaja").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<div class='divSeparadorTicket' style='margin-top:5px;margin-bottom:5px' ></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Cajero :</b></td>"
+"<td style=''>"+document.getElementById("inptBuscarVistaApCie2").value+"</td>"
+"</tr>"
+"</table>"
+"</div>"
+"</center>"
+"</div>"


var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("https://systemsrepository.com/GoodVentaByR/system/reportticket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
}

/*
ABM CASA
*/
function verCerrarAbmCasa(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmCasa").style.display==""){
		document.getElementById("divMinimizadoListadoDeLocales").style.display="none"
	limpiarCamposBuscarCasa()
		limpiarcamposCasa()
document.getElementById("tdEfectoAbmCasa").className="magictime vanishOut"
	$("div[id=divAbmCasa]").fadeOut(500);	
	
	}else{			
		if(controlacceso("VERLISTADODELOCALES","accion")==false){return;}
		document.getElementById("divAbmCasa").style.display=""
document.getElementById("tdEfectoAbmCasa").className="magictime slideDownReturn"
	
	}
}
function limpiarCamposBuscarCasa(){
	document.getElementById("inptBuscarAbmCasa1").value=""
	document.getElementById("inptBuscarAbmCasa2").value=""
	document.getElementById("inptTotalRegistoCasa").value=""
	document.getElementById("inptRegistroSeleccCasa").value=""
	document.getElementById("table_abm_casa").innerHTML=""
}
function minimizarcasa(){
		document.getElementById("divMinimizadoListadoDeLocales").style.display=""
document.getElementById("tdEfectoAbmCasa").className="magictime slideDown"
	$("div[id=divAbmCasa]").fadeOut(500);	
}
function verCerrarVentanaAbmCasa(d, l) {
	if (d == "1") {
		if (l == "1") {
			if(controlacceso("INSERTARLISTADODELOCALES","accion")==false){return;}
			limpiarcamposCasa()
		}
		$("div[id=divAbmCasa2]").fadeIn(250)
		document.getElementById('divAbmCasa1').style.display = "none"
	} else {
		$("div[id=divAbmCasa1]").fadeIn(250)
			document.getElementById('divAbmCasa2').style.display = "none"
	}
}
function verVentanaEditarCasa() {
	if(controlacceso("EDITARLISTADODELOCALES","accion")==false){return;}
	if (idAbmCasa == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmCasa("1", "2")
}
var idAbmCasa = ""
function obtenerdatosabmCasa(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptNombreCasa').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccCasa').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptEstadoCasa').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('btnEditarCasas').style.backgroundColor="";
	document.getElementById('btnAbmCasa').value = "Editar datos";
	idAbmCasa = $(datostr).children('td[id="td_id"]').html();
}
function verificarcamposCasa() {
	var inptNombreCasa = document.getElementById('inptNombreCasa').value
	var inptEstadoCasa = document.getElementById('inptEstadoCasa').value
	if (inptNombreCasa == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL LOCAL", "#")
		return false;
	}
	var accion = "";
	if (idAbmCasa != "") {
		accion = "editar";
		if(controlacceso("EDITARLISTADODELOCALES","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("INSERTARLISTADODELOCALES","accion")==false){return;}
	}
	abmcasa(inptNombreCasa, inptEstadoCasa, idAbmCasa, accion);
}
function abmcasa(nombre, estado, cod_local, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_local", cod_local)
	datos.append("nombre", nombre)
	datos.append("estado", estado)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcasa.php",
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
					limpiarcamposCasa()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmCasa = ""
					buscarabmCasa()
					buscarabmCasaOption();
					buscarabmCasaOptionCuentas()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function checkestadoCasas(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarCasa1').checked=true
		document.getElementById('inptSeleccEstadoBuscarCasa2').checked=false	
	}else{
		
		document.getElementById('inptSeleccEstadoBuscarCasa1').checked=false
		document.getElementById('inptSeleccEstadoBuscarCasa2').checked=true
	}
}
function buscarabmCasa() {
if(controlacceso("BUSCARLISTADODELOCALES","accion")==false){return;}
	var codigo = document.getElementById('inptBuscarAbmCasa1').value
	var nombre = document.getElementById('inptBuscarAbmCasa2').value
	var estado = ""
	if(document.getElementById('inptSeleccEstadoBuscarCasa1').checked==true){
		estado = "Activo"
	}else{
		estado = "Inactivo"
	}
	document.getElementById("table_abm_casa").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codigo": codigo,
		"nombre": nombre,
		"estado": estado,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcasa.php",
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
			document.getElementById("table_abm_casa").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_casa").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_casa").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoCasa").value = datos[3];
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function limpiarcamposCasa() {
	document.getElementById('inptNombreCasa').value = "";
	document.getElementById('inptRegistroSeleccCasa').value = "";
	document.getElementById('btnEditarCasas').style.backgroundColor="#b7b7b7";
	document.getElementById('inptEstadoCasa').value = "Activo";
	document.getElementById('btnAbmCasa').value = "Guardar datos";
	idAbmCasa = "";
}
function buscarabmCasaOption() {
	document.getElementById("inptlocaluser").innerHTML = "";
	document.getElementById("inptlocalProducto").innerHTML = "";
	document.getElementById("inptBuscarProducto7").innerHTML = "";
	document.getElementById("inptLocalProductoEnviarA").innerHTML = "";
	document.getElementById("inptlocalProductoBuscarVista").innerHTML = "";
	document.getElementById("inptlocalVenta").innerHTML = "";
	document.getElementById("inptlocalAperturaCierre").innerHTML = "";
	document.getElementById("inptlocalCaja").innerHTML = "";
	document.getElementById("inptlocalCompra").innerHTML = "";
	document.getElementById("inputSelectLocalVistaCompra").innerHTML = "";
	document.getElementById("inptBuscarUsuario4").innerHTML = "";
	document.getElementById("inptlocalProductoBuscarInventario").innerHTML = "";
	document.getElementById("inptBuscarHistorialVenta8").innerHTML = "";
	document.getElementById("inptBuscarHistorialLocal").innerHTML = "";
	
	document.getElementById("inptBuscarCuentasCobrar6").innerHTML = "";
	document.getElementById("inptlocalCuentasAcobrainforme").innerHTML = "";
	document.getElementById("inptlocalMisGastos").innerHTML = "";
	document.getElementById("inptlocalMisGastosBusca").innerHTML = "";
	document.getElementById("inptBuscarHistorialCompra5").innerHTML = "";
	
	
	document.getElementById("inptlocalArqueo").innerHTML = "";
	document.getElementById("inptlocalMetas").innerHTML = "";
	document.getElementById("inptlocalInformeDevoluciones").innerHTML = "";
	document.getElementById("inptlocalInformeProductosComprados").innerHTML = "";
	document.getElementById("inptlocalInformeProductosVendidos").innerHTML = "";
	document.getElementById("inptlocalInformeGananciaporventa").innerHTML = "";
	document.getElementById("inptlocalInformeVentaCanceladas").innerHTML = "";
	document.getElementById("inptlocal2InformeProductoDespachado").innerHTML = "";
	document.getElementById("inptlocal1InformeProductoDespachado").innerHTML = "";
	document.getElementById("inptlocalProductoBuscarCatalago").innerHTML = "";

	document.getElementById("inptlocalInformeEvaluacion").innerHTML = ""
	document.getElementById("inptlocalProductoBuscarCodBarra").innerHTML = ""
	document.getElementById("inptlocalNroFactura").innerHTML = ""
	document.getElementById("inptlocalCuentaApagar").innerHTML = ""
	document.getElementById("inptlocalProductoGarantia").innerHTML = ""
	document.getElementById("inptlocalVistaApCie").innerHTML = ""
	document.getElementById("inptlocalCobrosRealizados3").innerHTML = ""
	document.getElementById("inptAbmLocalVendedor").innerHTML = ""
	document.getElementById("inptBuscarAbmCobrador3").innerHTML = ""
	document.getElementById("inptBuscarVistaVendedor1").innerHTML = ""
	document.getElementById("inptLocalProductoListadoDespachar1").innerHTML = ""
	document.getElementById("inptLocalProductoSalidadDeposito1").innerHTML = ""
	document.getElementById("inptLocalProductoListadoDespachar2").innerHTML = ""
	document.getElementById("inptlocalMoroso").innerHTML = ""
	
	
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscaroption"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmcasa.php",
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
					document.getElementById("inptlocaluser").innerHTML ="<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalProducto").innerHTML = datos_buscados
					document.getElementById("inptBuscarUsuario4").innerHTML ="<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalVenta").innerHTML = datos_buscados
					document.getElementById("inptlocalAperturaCierre").innerHTML = "<option value=''>SELECCIONAR</option>" +datos_buscados
					document.getElementById("inptlocalCaja").innerHTML = "<option value=''>SELECCIONAR</option>" +datos_buscados
					document.getElementById("inptlocalProductoBuscarInventario").innerHTML = "<option value=''>SELECCIONAR</option>" +datos_buscados
					document.getElementById("inptLocalProductoEnviarA").innerHTML = "<option value=''>SELECCIONAR</option>" +datos_buscados
					document.getElementById("inptBuscarProducto7").innerHTML = datos_buscados
					document.getElementById("inptlocalProductoBuscarVista").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalMisGastos").innerHTML = "<option value=''>SELECCIONAR</option>" +datos_buscados
					document.getElementById("inptlocalCompra").innerHTML = datos_buscados
					document.getElementById("inputSelectLocalVistaCompra").innerHTML = "<option value=''>SELECCIONAR</option>" +datos_buscados
					document.getElementById("inptlocalNroFactura").innerHTML = "<option value=''>SELECCIONAR</option>" +datos_buscados
					document.getElementById("inptBuscarHistorialVenta8").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptBuscarCuentasCobrar6").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalCuentasAcobrainforme").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalMisGastosBusca").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptBuscarHistorialCompra5").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					
					document.getElementById("inptlocalArqueo").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalMetas").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalInformeDevoluciones").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalInformeProductosComprados").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalInformeProductosVendidos").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalInformeGananciaporventa").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalInformeVentaCanceladas").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocal2InformeProductoDespachado").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocal1InformeProductoDespachado").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalProductoBuscarCatalago").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalInformeEvaluacion").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptAbmLocalVendedor").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados				
					document.getElementById("inptlocalCuentaApagar").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalProductoBuscarCodBarra").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalProductoGarantia").innerHTML = datos_buscados
					document.getElementById("inptlocalVistaApCie").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalCobrosRealizados3").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptBuscarAbmCobrador3").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptBuscarVistaVendedor1").innerHTML = "<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptLocalProductoListadoDespachar1").innerHTML =datos_buscados
					document.getElementById("inptLocalProductoSalidadDeposito1").innerHTML =datos_buscados
					document.getElementById("inptBuscarHistorialLocal").innerHTML ="<option value=''>SELECCIONAR</option>" + datos_buscados
					document.getElementById("inptlocalMoroso").innerHTML ="<option value=''>SELECCIONAR</option>" + datos_buscados
	               document.getElementById("inptLocalProductoListadoDespachar2").innerHTML ="<option value=''>SELECCIONAR</option>" + datos_buscados
				  seleccionarLocalUSer()
                 buscarOptionCaja();
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}

function buscarabmCasaOptionCuentas() {
	document.getElementById("inptBuscarCuentasCobrar6").innerHTML = "";	
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscaroptionlogin"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmcasa.php",
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
					document.getElementById("inptBuscarCuentasCobrar6").innerHTML ="<option value=''>SELECCIONAR</option>" + datos_buscados
					
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}


function seleccionarLocalUSer(){
			document.getElementById("inptlocaluser").value = cod_localFKUSer
		
		document.getElementById("inptBuscarUsuario4").value = cod_localFKUSer
		document.getElementById("inptlocalVenta").value = cod_localFKUSer
		document.getElementById("inptlocalAperturaCierre").value = cod_localFKUSer
		document.getElementById("inptlocalCaja").value = cod_localFKUSer
		document.getElementById("inptlocalProductoBuscarInventario").value = cod_localFKUSer

		document.getElementById("inptLocalProductoEnviarA").value = cod_localFKUSer
		document.getElementById("inptlocalProductoBuscarVista").value = cod_localFKUSer
		document.getElementById("inptlocalMisGastos").value = cod_localFKUSer
		document.getElementById("inptlocalCompra").value = cod_localFKUSer
		document.getElementById("inputSelectLocalVistaCompra").value = cod_localFKUSer
		document.getElementById("inptBuscarHistorialVenta8").value = cod_localFKUSer
		document.getElementById("inptlocalNroFactura").value = cod_localFKUSer
		document.getElementById("inptBuscarCuentasCobrar6").value = cod_localFKUSer
		document.getElementById("inptlocalCuentasAcobrainforme").value = cod_localFKUSer
		document.getElementById("inptlocalMisGastosBusca").value = cod_localFKUSer
		document.getElementById("inptBuscarHistorialCompra5").value = cod_localFKUSer
		document.getElementById("inptBuscarHistorialLocal").value = cod_localFKUSer
		document.getElementById("inptlocalMetas").value = cod_localFKUSer
		document.getElementById("inptlocalArqueo").value = cod_localFKUSer
		document.getElementById("inptlocalInformeDevoluciones").value = cod_localFKUSer
		document.getElementById("inptlocalInformeProductosComprados").value = cod_localFKUSer
		document.getElementById("inptlocalInformeProductosVendidos").value = cod_localFKUSer
		document.getElementById("inptlocalInformeGananciaporventa").value = cod_localFKUSer
		document.getElementById("inptlocalInformeVentaCanceladas").value = cod_localFKUSer
		document.getElementById("inptlocalProductoBuscarCatalago").value = cod_localFKUSer
		document.getElementById("inptlocalInformeEvaluacion").value = cod_localFKUSer
		document.getElementById("inptlocalVistaApCie").value = cod_localFKUSer
			document.getElementById("inptlocalCuentaApagar").value = cod_localFKUSer
		document.getElementById("inptlocalEntregaCobrador3").value = cod_localFKUSer
		document.getElementById("inptlocalCobrosRealizados3").value = cod_localFKUSer
		document.getElementById("inptlocalMoroso").value = cod_localFKUSer
	
					
}
/*
ABM CLIENTES
*/
function verCerrarAbmClientes(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmCliente").style.display==""){
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
function verCerrarAbmClientes2(){
document.getElementById("divAbmCliente").style.display="none"
document.getElementById("btnVolverAtrasCliente").style.display=""
		document.getElementById("btnCerrarAtrasCliente").style.display="none"
		document.getElementById("divAbmCliente1").style.display=""
		document.getElementById("divAbmCliente2").style.display="none"
}
var controlventananuevocliente="";
function vernuevoclientevista(d) {
	if (d == "1") {
		if(controlacceso("INSERTARLISTADODECLIENTES","accion")==false){return;}
		document.getElementById("divAbmCliente").style.display=""
		 document.getElementById("tdEfectoAbmCliente").className="magictime vanishIn"
		document.getElementById("divAbmCliente1").style.display="none"
		document.getElementById("divAbmCliente2").style.display=""
		document.getElementById("btnVolverAtrasCliente").style.display="none"
		document.getElementById("btnCerrarAtrasCliente").style.display=""
		controlventananuevocliente="venta"
		limpiarcamposCliente()
	} else {
		document.getElementById("btnVolverAtrasCliente").style.display=""
		document.getElementById("btnCerrarAtrasCliente").style.display="none"
		document.getElementById("divAbmCliente1").style.display=""
		document.getElementById("divAbmCliente2").style.display="none"
		 document.getElementById("tdEfectoAbmCliente").className="magictime vanishOut"
		$("div[id=divAbmCliente]").fadeOut(500)
		
	}
}
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
		ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL CLIENTE", "#")
		return false;
	}
	if (inptNroDocCliente == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE DOCUMENTO", "#")
		return false;
	}
	if (idFKZona == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA ZONA", "#")
		return false;
	}
	var accion = "nuevo";
if(controlacceso("INSERTARLISTADODECLIENTES","accion")==false){return;}
	abmcliente(inptAccesoCreditoCliente,idFKZona, inptNombreApellidoCliente, inptNroDocCliente, inptNroTelefCliente, inptNrowhatsappCliente, inptDireccionCliente, inptReferenciaCliente, inptCalificaCliente, inptEstadoCliente, idAbmCliente, accion);
}
function verCerrarVentanaAbmCliente(d, l) {	
	if (d == "1") {
		if (l == "1") {
			if(controlacceso("INSERTARLISTADODECLIENTES","accion")==false){return;}
			limpiarcamposCliente()
		}
		$("div[id=divAbmCliente2]").fadeIn(250)
		document.getElementById('divAbmCliente1').style.display = "none"
	} else {
		$("div[id=divAbmCliente1]").fadeIn(250)
		document.getElementById('divAbmCliente2').style.display = "none"
	}
}
function verVentanaEditarCliente() {
	if(controlacceso("EDITARLISTADODECLIENTES","accion")==false){return;}
	if (idAbmCliente == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmCliente("1", "2")
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
function readFileCliente(input){		
var file=$("input[name="+input.name+"]")[0].files[0];
var filename= file.name;
var tamanho = file.size;
if (tamanho > 5000000){
ver_vetana_informativa("LA FOTO NO PUEDE EXCEDER LOS 5Mb")
return false
}
file_extension=filename.substring(filename.lastIndexOf('.')+1).toLowerCase();
if ((file_extension=="jpeg") || (file_extension=="jpg") || (file_extension=="png") ){
}else{
ver_vetana_informativa("LA FOTO SELECCIONADO NO ES JPEG")
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
function verCerrarVisorImagen(d,img){
	document.getElementById('divVistaFoto').style.display = "none"
	if (d == "1") {		
		var urlsrc="";
		if(img=="cliente1"){
		urlsrc=	fotocliente1
		}
		if(img=="cliente2"){
		urlsrc=	fotocliente2
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
window.open("https://systemsrepository.com/GoodVentaByR/system/report.html");
}
var idAbmCliente=""
function obtenerdatosabmCliente(datostr){	
		 $("tr[id=tbSelecRegistro]").each(function(i, td){		
		 td.className=''
		
	   });
    datostr.className='tableRegistroSelec'
	document.getElementById('inptNombreApellidoCliente').value=$(datostr).children('td[id="td_datos_1"]').html();
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
	fotocliente1= $(datostr).children('td[id="td_datos_11"]').html();
	fotocliente2= $(datostr).children('td[id="td_datos_12"]').html();
	 $("div[id=imgFotoCliente1]").css({"background-image":"url("+fotocliente1+")"})
	  $("div[id=imgFotoCliente2]").css({"background-image":"url("+fotocliente2+")"})
	idAbmCliente= $(datostr).children('td[id="td_id"]').html();
	idFKZona= $(datostr).children('td[id="td_datos_9"]').html();
    extcliente1="";
    extcliente2="";
	buscarmasreferenciasclientes();
  document.getElementById('btnAbmCliente').value="Editar datos";
  document.getElementById('btnEditarClientes').style.backgroundColor="";
  document.getElementById('btnAuditoriaClientes').style.backgroundColor="#673ab7";
  
	
}
function AnhadirMasReferencias(){
		var inptMasRefDireccionCliente=document.getElementById("inptMasRefDireccionCliente").value
	var inptMasRefReferenciaCliente=document.getElementById("inptMasRefReferenciaCliente").value
	var inptMasRefTelefCliente=document.getElementById("inptMasRefTelefCliente").value
	var inptMasRefObservacionCliente=document.getElementById("inptMasRefObservacionCliente").value
	
var pagina="<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>"
+"<tr id='tbSelecRegistro' onclick='obtenerdatosmasreferencias(this)'  name='tdMasReferencias'>"
+"<td  id='td_datos_1' style='width:10%'>"+inptMasRefObservacionCliente+"</td>"
+"<td  id='td_datos_2' style='width:10%;'>"+inptMasRefTelefCliente+"</td>"
+"<td  id='td_datos_3' style='width:10%'>"+inptMasRefDireccionCliente+"</td>"
+"<td  id='td_datos_4' style='width:10%'>"+inptMasRefReferenciaCliente+"</td>"
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
	elementoAddMasReferencias="";
}
function verificarcamposCliente(){
	var inptNombreApellidoCliente=document.getElementById('inptNombreApellidoCliente').value
	var inptNroDocCliente=document.getElementById('inptNroDocCliente').value
	var inptNroRucCliente=document.getElementById('inptNroRucCliente').value
	var inptNroTelefCliente=document.getElementById('inptNroTelefCliente').value
	var inptNrowhatsappCliente=document.getElementById('inptNrowhatsappCliente').value
	var inptDireccionCliente=document.getElementById('inptDireccionCliente').value
	var inptReferenciaCliente=document.getElementById('inptReferenciaCliente').value
	var inptCalificaCliente=document.getElementById('inptCalificaCliente').value
	var inptEstadoCliente=document.getElementById('inptEstadoCliente').value
	var inptLugrarTrabajoCliente=document.getElementById('inptLugrarTrabajoCliente').value
	var inptDireccionTrabajoCliente=document.getElementById('inptDireccionTrabajoCliente').value
	var inptSalarioCliente=document.getElementById('inptSalarioCliente').value
	var inptAntiguedadCliente=document.getElementById('inptAntiguedadCliente').value
	var inptNroTelefTrabajoCliente1=document.getElementById('inptNroTelefTrabajoCliente1').value
	var inptNroTelefTrabajoCliente2=document.getElementById('inptNroTelefTrabajoCliente2').value
	var inptAccesoCreditoCliente = document.getElementById('inptAccesoCreditoCliente').value
	  if(inptNombreApellidoCliente==""){
	ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DEL CLIENTE","#")
	  return false;
  }
  if(inptNroDocCliente==""){
	ver_vetana_informativa("FALTO INGRESAR EL NRO DE DOCUMENTO","#")
	  return false;
  }
  if(idFKZona==""){
	ver_vetana_informativa("FALTO SELECCIONAR UNA ZONA","#")
	  return false;
  }
 
 
 
  var accion="";
  if(idAbmCliente!=""){
	  accion="editar";
	 	if(controlacceso("EDITARLISTADODECLIENTES","accion")==false){return;}
  }else{
	 if(controlacceso("INSERTARLISTADODECLIENTES","accion")==false){return;}
	  accion="nuevo";
  }
  
  abmcliente(inptAccesoCreditoCliente,inptLugrarTrabajoCliente,inptDireccionTrabajoCliente,inptSalarioCliente,inptAntiguedadCliente,inptNroTelefTrabajoCliente1,inptNroTelefTrabajoCliente2,idFKZona,inptNombreApellidoCliente,inptNroRucCliente,inptNroDocCliente,inptNroTelefCliente,inptNrowhatsappCliente,inptDireccionCliente,inptReferenciaCliente,inptCalificaCliente,inptEstadoCliente,idAbmCliente,accion);
}
function  abmcliente(accesocredito,lugardetrabajo,direcciontrab,salario,antiguedad,teleftrab1,teleftrab2,idzonaFk,nombre_persona,rut_cliente,ci_cliente,telefono,whapp,direccion,email,Calificacion,estado,cod_persona,accion){
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
			 datos.append("telefono" , telefono)
			 datos.append("email" , email)//Sirve para la referencia
			 datos.append("rut_cliente" , rut_cliente)
			 datos.append("ci_cliente" , ci_cliente)
			 datos.append("Calificacion" , Calificacion)
			 datos.append("whapp" , whapp)
			 datos.append("estado" , estado)
			 datos.append("idzonaFk" , idzonaFk)
			datos.append("foto1", fotocliente1)
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
			url: "/GoodVentaByR/php_system/abmclientes.php",
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

limpiarcamposCliente()
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
			url: "/GoodVentaByR/php_system/abmclientes.php",
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
			url: "/GoodVentaByR/php_system/abmclientes.php",
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
			url: "/GoodVentaByR/php_system/abmclientes.php",
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
			url: "/GoodVentaByR/php_system/abmclientes.php",
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
	document.getElementById('table_mas_referenciasClientes').innerHTML="";
	document.getElementById('inptCalificaCliente').value="EXCELENTE";
	document.getElementById('inptEstadoCliente').value="Activo";
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
}
var idFkCliente = ""
var controlseleccvistacliente = ""
function vercerrarvistacliente(d, ventana) {
	if (d == "1") {
		
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
		url: "/GoodVentaByR/php_system/abmclientes.php",
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
	ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
	return;
	}
	var datostr=elementoCliente
	if (controlseleccvistacliente == "venta") {
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
	}
	if (controlseleccvistacliente == "garante") {
		idGaranteFk = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptGaranteVenta').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptDocGaranteVenta').value = $(datostr).children('td[id="td_datos_2"]').html();
	}
	if(controlseleccvistacliente=="expediente"){
		codClienteFkExpediente= $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptBuscarInfExpedientefiltro').value=$(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById('inptBuscarInfExpedienteNroDocumento').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptBuscarInfExpedienteNroTelef').value = $(datostr).children('td[id="td_datos_4"]').html();
	
	}
	document.getElementById("divVistaCliente").style.display = "none"
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
		url: "/GoodVentaByR/php_system/abmventa.php",
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
		url: "/GoodVentaByR/php_system/abmventa.php",
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
		url: "/GoodVentaByR/php_system/abmclientes.php",
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
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
/*
NRO FACTURA
*/
var idAbmNroFactura="";
var ElementoSeleccNroFactura="";
function verCerrarFrmNroFactura(d){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmNroFactura").style.display==""){
		document.getElementById("divMinimizadoNroFactura").style.display="none"
document.getElementById("tdEfectoNroFactura").className="magictime vanishOut"
	$("div[id=divAbmNroFactura]").fadeOut(500);	
		LimpiarCamposNroFactura()
	}else{		
	if(controlacceso("VERFACTURASHABILITADAS","accion")==false){return;	}
		document.getElementById("divAbmNroFactura").style.display=""
		document.getElementById("tdEfectoNroFactura").className="magictime slideDownReturn"
		BuscarAbmNroFactura()
	}
}
function minimizarventafacturanro(){
document.getElementById("tdEfectoNroFactura").className="magictime slideDown"
	$("div[id=divAbmNroFactura]").fadeOut(500);	
		document.getElementById("divMinimizadoNroFactura").style.display=""
}
function LimpiarCamposNroFactura(){
	document.getElementById("inptNroFactura").value="";		 
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
	 	document.getElementById('inptFechaNroFactura').value=f.getFullYear()+"-"+mes+"-"+dia;
	

	idAbmNroFactura="";

}
function ObtenerdatosAbmNroFactura(datostr) {
	// $("tr[id=tbSelecRegistro]").each(function (i, td) {
		// td.className = ''

	// });
		
	// datostr.className = 'tableRegistroSelec'
    // document.getElementById("inptNroFactura").value = $(datostr).children('td[id="td_datos_1"]').html();
    // document.getElementById("inptFechaNroFactura").value = $(datostr).children('td[id="td_datos_2"]').html();
	// idAbmNroFactura = $(datostr).children('td[id="td_id"]').html();
}
function VerificarDatosNroFactura(){
	var inptNroFactura = document.getElementById("inptNroFactura").value
	var inptFechaNroFactura = document.getElementById("inptFechaNroFactura").value
	var inptlocalNroFactura = document.getElementById("inptlocalNroFactura").value
	var inptCajalNroFactura =$("select[id=inptCajalNroFactura]").children(":selected").text() 

	
	if(inptNroFactura==""){
		document.getElementById("inptNroFactura").focus()
		ver_vetana_informativa("Falto Ingresar el nro de orden")
		return
	}
	if(inptFechaNroFactura==""){
		document.getElementById("inptFechaNroFactura").focus()
		ver_vetana_informativa("Falto seleccionar la fecha")
		return
	}
	
	if(inptlocalNroFactura==""){
		document.getElementById("inptlocalNroFactura").focus()
		ver_vetana_informativa("Falto seleccionar el local")
		return
	}
	
	var accion = "";
	if (idAbmNroFactura != "") {
		
		accion = "editar";
		if(controlacceso("INSERTARFACTURASHABILITADAS","accion")==false){ return;}
	} else {
		if(controlacceso("INSERTARFACTURASHABILITADAS","accion")==false){return;}
		accion = "nuevo";
	}
	AbmNroFactura(inptNroFactura,inptFechaNroFactura,inptlocalNroFactura,inptCajalNroFactura,idAbmNroFactura,accion)
}
function AbmNroFactura(nro,fecha,cod_localfk,nrocaja,idabm,accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idabm", idabm)
	datos.append("nro", nro)
	datos.append("fecha", fecha)
	datos.append("nrocaja", nrocaja)
	datos.append("cod_localfk", cod_localfk)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/ABMNroFactura.php",
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

               Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {

					LimpiarCamposNroFactura()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")				
					BuscarAbmNroFactura()


				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});

}
function BuscarAbmNroFactura() {
	if(controlacceso("BUSCARFACTURASHABILITADAS","accion")==false){ return;}
	var buscador = ""
	var estado = "Activo"
	document.getElementById("divBuscadorNroFactura").innerHTML = paginacargando
    document.getElementById("lblNroRegistroNroFactura").innerHTML="";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"estado": estado,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
        url: "/GoodVentaByR/php_system/ABMNroFactura.php",
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
			document.getElementById("divBuscadorNroFactura").innerHTML = ''
			document.getElementById("lblNroRegistroNroFactura").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorNroFactura").innerHTML = ''
			document.getElementById("lblNroRegistroNroFactura").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				   
					var datos_buscados = datos[2];
					document.getElementById("divBuscadorNroFactura").innerHTML = datos_buscados
                   document.getElementById("lblNroRegistroNroFactura").innerHTML="Se encontraron "+datos[3]+" registro(s)";
				   

				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
/*ABM NUEVA VENTA*/
var idabmVenta = ""
var idGaranteFk="";
function verCerrarAbmVenta(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmVenta").style.display==""){
limpiarcamposventa()
		document.getElementById("tdEfectoAbmVenta").className="magictime vanishOut"
	$("div[id=divAbmVenta]").fadeOut(500);	
		// document.getElementById("divMinimizadoNuevaVenta2").style.display="none"
	    document.getElementById("divMinimizadoNuevaVenta1").style.display="none"		
	}else{	
		if(controlacceso("VERVENTA","accion")==false){return;}
		if(idabmAperturacierrecaja==""){
		   ver_vetana_informativa("FALTO INICIAR UNA CAJA", "#")
		   verCerrarVentanaAbmAperturaCierreCaja1()
		   return
	   }
		document.getElementById("divAbmVenta").style.display=""
document.getElementById("tdEfectoAbmVenta").className="magictime slideDownReturn"
	
	
	}
}
function minimizarventa(){
document.getElementById("tdEfectoAbmVenta").className="magictime slideDown"
	$("div[id=divAbmVenta]").fadeOut(500);	
	// document.getElementById("divMinimizadoNuevaVenta2").style.display=""
	document.getElementById("divMinimizadoNuevaVenta1").style.display=""
}
function verOpcionesDeConfigVenta(datos){
	if(document.getElementById("divMasConfigVenta").style.display==""){
document.getElementById("tdEfectoMasConfigVenta").className="magictime slideRight"
$("div[id=divMasConfigVenta]").fadeOut(500);
	}else{
		document.getElementById("divMasConfigVenta").style.display=""
 document.getElementById("tdEfectoMasConfigVenta").className="magictime slideLeftReturn"
		if(datos=="nro"){
			document.getElementById("inptNroVenta").focus;
			$("#inptNroVenta").select();
		}
	}
}
var ControlVentanaVenta="0";
function limpiarcamposventa(ctrl) {
	idDetalleVenta = "";
	idabmVenta = ""
	idFkProducto = ""
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	controlVenta="1";
	
	document.getElementById('inptFechaVenta').value = f.getFullYear() + "-" + mes + "-" + dia;
	document.getElementById('inptClienteVenta').value = "CLIENTE OCACIONAL";
	document.getElementById('inptClienteVenta2').value = "CLIENTE OCACIONAL";
		document.getElementById('inptDocClienteVenta').value = "";
		document.getElementById('inptDocClienteVenta2').value = "";
	document.getElementById('inptSeleccTipoVenta').value = "CREDITO"
	document.getElementById('inptSeleccTipoVenta').disabled=false
	document.getElementById('inptSeleccTipoVenta').style.backgroundColor = ""
	controltipoventa = "CREDITO"
	document.getElementById('table_vista_producto_venta').innerHTML = ""
	document.getElementById('table_vista_producto_venta_costos').innerHTML = ""
	document.getElementById('inptDocGaranteVenta').value = ""
	document.getElementById('inptGaranteVenta').value = ""
	document.getElementById('inpCodVenta').value = ""
	document.getElementById('inptTotalVenta').value = ""
	document.getElementById('inptSubTotalVenta').value = ""
	document.getElementById('inptTotalDescuento').value = ""
	document.getElementById('inptTotalVenta2').innerHTML = "0"
	document.getElementById('inptTotalPagado').value = ""
	document.getElementById('inptDeudaActual').value = ""
	document.getElementById('inpCodVentaPagos').value = ""
	document.getElementById('inptTotalVentaPagos').value = ""
	document.getElementById('inptNroCuotasPagos').value = ""
	document.getElementById('inptMontoPagoOpciones').value = ""
	document.getElementById('inptFechaInicioPapo').value = ""
	document.getElementById('inptProductoVenta').value = ""
	document.getElementById('inptCantProductoVenta').value = ""
	document.getElementById('inptCostoProductoVenta').value = ""
	preciocostocontado="";
	preciocostocredito="";
	document.getElementById('inpTotalCostoVenta').value = ""
	document.getElementById('inptVendedorVenta1').value = ""
	document.getElementById('inptVendedorVenta2').value = ""
	document.getElementById('inptGaranteVenta').value = ""
	document.getElementById('inptDetallesVentaProductos').value = ""
	document.getElementById('inptNroVenta').value = ""
	document.getElementById('pNroFactuaCaja').innerHTML = ""
	if(document.getElementById('inptSeleccTipoComprobanteVenta').value=="FACTURA"){
	document.getElementById('inptSeleccPuntoExpedicionVenta').value = cajapredeterminada
	}else{
		document.getElementById('inptSeleccPuntoExpedicionVenta').value = ""
	}
	if(ControlCobradorUser==0){
		
	document.getElementById('inptCobradorVenta').value = "SIN COBRADOR";
	document.getElementById('inptCobradorCargarPago').value = "SIN COBRADOR";
	document.getElementById('inptCobradorConfirmar').value = "SIN COBRADOR";
	
	idFkCobrador = "9";
	cobradorcredito = "9";
	
	}else{
		
		document.getElementById('inptCobradorVenta').value = document.getElementById("lblUser").innerHTML;
	document.getElementById('inptCobradorCargarPago').value = document.getElementById("lblUser").innerHTML;
	document.getElementById('inptCobradorConfirmar').value = document.getElementById("lblUser").innerHTML;

	idFkCobrador = CodCobradorUser;
	cobradorcredito = CodCobradorUser;
		
	}
	
	document.getElementById('btnAbmVenta').style.display = "none"
	document.getElementById('btnAbmVenta').value = "Guardar datos"
	document.getElementById('inptComisionVentaCobrador').value = "0"
	document.getElementById('inptGaranteVenta').value = "SIN GARANTE";
	document.getElementById("inptEntregaConfCredito").value ="0"
	document.getElementById("inptConfirmarPagoEntrega").value ="SI"
	document.getElementById("btnFinalizarVenta").style.display="none"
	document.getElementById("btnCancelarVenta").style.display="none"
	document.getElementById("btnVerCreditos").style.display="none"
	DatosAutoCompleteCredito=new Array();
					document.getElementById("inptNroCuotasConfCredito").value ="0"
					document.getElementById("inptMontoPagoConfCredito").value = "0"
					document.getElementById("inptFechaInicioConfCredito").value = ""
					document.getElementById("inptInteresConfCredito").value = "0,10"
					document.getElementById("inptDiasConfCredito").value = "10"
					document.getElementById("inputSelectMetodoConfCredito").value = ""
					document.getElementById("lblInfoConfCredito").innerHTML = ""
					document.getElementById("lblInfoMotoCredito").innerHTML = ""
document.getElementById("inptEntregaConfCredito").disabled=false
					document.getElementById("inptNroCuotasConfCredito").disabled=false
					document.getElementById("inptMontoPagoConfCredito").disabled=false
					document.getElementById("inptFechaInicioConfCredito").disabled=false
					document.getElementById("inptInteresConfCredito").disabled=false
					document.getElementById("inptDiasConfCredito").disabled=false
					document.getElementById("inputSelectMetodoConfCredito").disabled=false
					document.getElementById("inpTSeleccCosto").disabled=false
	idFkVendedor1 = ""
	idFkVendedor2 = ""
	idGaranteFk = ""
	document.getElementById('table_abm_detalle_venta').innerHTML = ""
	idFkCliente = "10";
	
	idGaranteFk = "6";
	document.getElementById('inpCodVenta').disabled = false
	document.getElementById('inpCodVenta').className = "inputText"
	document.getElementById("btnMasInfoClienteVenta").style.display='none'
	document.getElementById("btnNuevoClienteVenta").style.display=''
	document.getElementById("tdImprimirVenta").style.display='none'
	if(ctrl!="1"){
	seleccionarLocalUSer()
	buscarnrodeventas();
	}

}
function buscarnrodeventas() {
	

	if(idabmVenta!=""){
		return false;
	}
	
	document.getElementById("inptNroVenta").value = "..."
	document.getElementById("pNroFactuaCaja").innerHTML = "..."
	var puntoExpedicion=$("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
	var tipo_comprobante=document.getElementById("inptSeleccTipoComprobanteVenta").value
	
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"puntoExpedicion": puntoExpedicion,
		"cod_local": cod_localFKUSer,
		"tipo_comprobante": tipo_comprobante,
		"funt": "buscarnroventa"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("inptNroVenta").value = ''
			document.getElementById("pNroFactuaCaja").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("pNroFactuaCaja").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {		
					document.getElementById("inptNroVenta").value = datos[2]
					
					if(puntoExpedicion==""){						
					document.getElementById("pNroFactuaCaja").innerHTML = "*"+datos[2]+"*"
					}else{
						document.getElementById("pNroFactuaCaja").innerHTML ="*"+puntoExpedicion+"-"+datos[2]+"*"
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
function SeleccTipoComprobanteVenta(){
	if(document.getElementById('inptSeleccTipoComprobanteVenta').value=="FACTURA"){
		document.getElementById('inptSeleccPuntoExpedicionVenta').disabled=false
		document.getElementById('inptSeleccPuntoExpedicionVenta').value=cajapredeterminada
		document.getElementById('inptSeleccTipoComprobanteVenta').style.backgroundColor=""
		 document.getElementById("btnImprimirticket").style.display=""
					 document.getElementById("btnImprimirFactura").style.display=""
					 document.getElementById("btnImprimirPagare").style.display=""
	}else{
		document.getElementById('inptSeleccPuntoExpedicionVenta').disabled=true
		document.getElementById('inptSeleccPuntoExpedicionVenta').value=""
		document.getElementById('inptSeleccTipoComprobanteVenta').style.backgroundColor="#c0e2fd"
		 document.getElementById("btnImprimirticket").style.display=""
					 document.getElementById("btnImprimirFactura").style.display="none"
					 document.getElementById("btnImprimirPagare").style.display=""
	}
	buscarnrodeventas()
}
function CambiarNroVenta1(datos) {
	document.getElementById('inpCodVenta').value = document.getElementById("inptNroVenta").value
	var puntoExpedicion=$("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
	if(puntoExpedicion==""){						
	document.getElementById("pNroFactuaCaja").innerHTML = "*"+ document.getElementById("inptNroVenta").value+"*";
	}else{
	document.getElementById("pNroFactuaCaja").innerHTML = "*"+puntoExpedicion+"-"+ document.getElementById("inptNroVenta").value+"*";
	}
}
function CambiarNroVenta2(datos) {
	document.getElementById('inptNroVenta').value = document.getElementById("inpCodVenta").value
	var puntoExpedicion=$("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
	if(puntoExpedicion==""){						
	document.getElementById("pNroFactuaCaja").innerHTML = "*"+ document.getElementById("inpCodVenta").value+"*";
	}else{
	document.getElementById("pNroFactuaCaja").innerHTML = "*"+puntoExpedicion+"-"+ document.getElementById("inpCodVenta").value+"*";
	}
}
function OpcionesTipoVenta(){	
	if( document.getElementById("inptTotalPagado").value!="" ){
	if(document.getElementById("inptTotalPagado").value!="0" ){
		document.getElementById("inptSeleccTipoVenta").value=controltipoventa		
	}
	}	
	controltipoventa=document.getElementById("inptSeleccTipoVenta").value;
	var controlDetalle=0;
	$("tr[name=tdDetalleVenta]").each(function(i, elementohtml){
controlDetalle=1;
	   });
	$("tr[name=tdDetalleVentaOffline]").each(function(i, elementohtml){
controlDetalle=0;
	   });
	   
	 
	if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
		if(controlDetalle=="0"){
		document.getElementById("btnFinalizarVenta").value="Guardar y Config. Credito";	
		}else{
		document.getElementById("btnFinalizarVenta").value="Datos del credito";
		}
		document.getElementById("inpTSeleccCosto").disabled=false
		document.getElementById('inptSeleccTipoVenta').style.backgroundColor=""
		document.getElementById("inptCostoProductoVenta").value= $("#inpTSeleccCosto option:first").val();
		preciocostocredito= QuitarSeparadorMilValor($("#inpTSeleccCosto option:first").val());	
		  $("td[id=td_datos_precio_contado]").each(function(i, elementohtml){  
	   elementohtml.style.display="none"
	   });
	$("td[id=td_datos_precios_creditos]").each(function(i, elementohtml){
elementohtml.style.display=""
	   });
	   
	   
	}else{
		if(controlDetalle=="0"){
		document.getElementById("btnFinalizarVenta").value="Añadir Pago";	
		}else{
		var inptTotalPagado = document.getElementById('inptTotalPagado').value
		if (inptTotalPagado!="0" && inptTotalPagado!="") {
		document.getElementById("btnFinalizarVenta").value="Añadir pago (No Disponible)";
		}else{
		document.getElementById("btnFinalizarVenta").value="Añadir Pago";	
		}
		}
		document.getElementById("inpTSeleccCosto").disabled=true		
		$("#inpTSeleccCosto option[id='contado'").attr("selected",true);
		seleccionarprecios(document.getElementById("inpTSeleccCosto"))
		document.getElementById('inptSeleccTipoVenta').style.backgroundColor="#c0e2fd"
	  $("td[id=td_datos_precio_contado]").each(function(i, elementohtml){  
	   elementohtml.style.display=""
	   });
	$("td[id=td_datos_precios_creditos]").each(function(i, elementohtml){
elementohtml.style.display="none"
	   });
	}
	calcular_total_venta()
	
}
function EditarDatosClienteDesdeVenta(){
	if(elementoCliente==""){
		ver_vetana_informativa("FALTO SELCCIONAR UN REGISTRO")
		return;
	}
	var datostr=elementoCliente
	document.getElementById('inptNombreApellidoCliente').value=$(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccCliente').value=$(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNroDocCliente').value=$(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptNroTelefCliente').value=$(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptNrowhatsappCliente').value=$(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptDireccionCliente').value=$(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptReferenciaCliente').value=$(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptCalificaCliente').value=$(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptEstadoCliente').value=$(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptZonaCliente').value=$(datostr).children('td[id="td_datos_10"]').html();
	document.getElementById('inptNroRucCliente').value=$(datostr).children('td[id="td_datos_13"]').html();
	fotocliente1= $(datostr).children('td[id="td_datos_11"]').html();
	fotocliente2= $(datostr).children('td[id="td_datos_12"]').html();
	 $("div[id=imgFotoCliente1]").css({"background-image":"url("+fotocliente1+")"})
	  $("div[id=imgFotoCliente2]").css({"background-image":"url("+fotocliente2+")"})
	idAbmCliente= $(datostr).children('td[id="td_id"]').html();
	idFKZona= $(datostr).children('td[id="td_datos_9"]').html();
    extcliente1="";
    extcliente2="";
  document.getElementById('btnAbmCliente').value="Editar datos";
  document.getElementById('divAbmCliente').style.display="";
  document.getElementById("btnVolverAtrasCliente").style.display="none"
		document.getElementById("btnCerrarAtrasCliente").style.display=""
		controlventananuevocliente="ventavista"
		buscarmasreferenciasclientes()
   verCerrarVentanaAbmCliente("1", "2")
  
}
function calcular_total_venta() {
	var c = QuitarSeparadorMilValor(document.getElementById('inptCantProductoVenta').value);
	var t = QuitarSeparadorMilValor(document.getElementById('inptCostoProductoVenta').value);
	var d = QuitarSeparadorMilValor(document.getElementById('inptDescuentoProductoVenta').value);
	if(t>0){
	if (isNaN(c)) {
		document.getElementById('inptCantProductoVenta').value = 0;
		c = 0;
	}
	if (isNaN(d)) {
		document.getElementById('inptDescuentoProductoVenta').value = 0;
		d = 0;
	}
	var c = parseFloat(c);
	var t = parseFloat(t);
	document.getElementById('inpTotalCostoVenta').value = (t * c)-d;
	//separadordemiles(document.getElementById('inpt_interes_pago_venta'))
	separadordemiles(document.getElementById('inpTotalCostoVenta'))
	separadordemiles(document.getElementById('inptDescuentoProductoVenta'))
	
	if(d>0){
		var obs=$("select[id=inpTSeleccCosto]").children(":selected").text() 
		document.getElementById("inptObservacionDetalleVenta").value=obs+", Descuento: "+d
	}
	}else{
	  document.getElementById('inpTotalCostoVenta').value=t*c
	}

}
function calcularTotalVentasCosto(datos) {
	calcular_total_venta()
}
function seleccionarprecios(datos) {
	if($("select[id=inpTSeleccCosto]").children(":selected").attr("name")!=undefined){
	document.getElementById("inptCostoProductoVenta").value = datos.value
	preciocostocredito = datos.value
	document.getElementById("inptObservacionDetalleVenta").value =  $("select[id=inpTSeleccCosto]").children(":selected").text() 
	document.getElementById("inptComisionVenta").value = $("select[id=inpTSeleccCosto]").children(":selected").attr("name")
	calcular_total_venta();
	}
}
function controldecostoventa(datos){
	var precionuevo=document.getElementById("inptCostoProductoVenta").value
	
	if(Number(preciocostocontado)>0){		
		precionuevo=QuitarSeparadorMilValor(precionuevo);
		if(document.getElementById("inptSeleccTipoVenta").value=="CONTADO"){
		if(Number(precionuevo)<Number(preciocostocontado)){			
		ver_vetana_informativa("EL PRECIO ESTA FUERA DE RANGO", "#")
		document.getElementById("inptCostoProductoVenta").value=separadordemilesnumero(preciocostocontado)
		document.getElementById("inpTotalCostoVenta").value=separadordemilesnumero(preciocostocontado)
		calcular_total_venta()		
		return false;
		}else{
		document.getElementById("inptCostoProductoVenta").value=separadordemilesnumero(precionuevo)
		}
		}
		if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
		if(Number(precionuevo)<Number(preciocostocredito)){
		ver_vetana_informativa("EL PRECIO ESTA FUERA DE RANGO", "#")
		document.getElementById("inptCostoProductoVenta").value=separadordemilesnumero(preciocostocredito)
		document.getElementById("inpTotalCostoVenta").value=separadordemilesnumero(preciocostocredito)
		calcular_total_venta()		
		return false;
		}else{
		document.getElementById("inptCostoProductoVenta").value=separadordemilesnumero(precionuevo)
		}
		}
	}
}
var masdetallesVenta="";
function verCerrarAbmMasDetallesVenta(){
	if(document.getElementById("divAddMasDetallesVenta").style.display==""){
		 document.getElementById("tdEfectoAddDetallesVenta").className="magictime vanishOut"
$("div[id=divAddMasDetallesVenta]").fadeOut(500);	
		document.getElementById("inptDetallesVentaProductos").value=masdetallesVenta;
	}else{	
		document.getElementById("divAddMasDetallesVenta").style.display=""
 document.getElementById("tdEfectoAddDetallesVenta").className="magictime vanishIn"
	}
}
function confirmarCambios(){
	document.getElementById("divAddMasDetallesVenta").style.display="none"
	masdetallesVenta=document.getElementById("inptDetallesVentaProductos").value
}
var DatosAutoCompleteCredito =new Array()
function anhadirProductoEnDetalleVenta(){
	
	var ContadoranhadirVenta=0;
	$("tr[name=tdDetalleVenta]").each(function(i, elementohtml){
ContadoranhadirVenta= ContadoranhadirVenta +1 ;
	   });
	   
	   if(ContadoranhadirVenta!=0){
		   ver_vetana_informativa("NO ES POSIBLE AGREGAR UN PRODUCTO A UNA VENTA FINALIZADO", "#")
		 
		   return false;
	   }
	
	if(idabmAperturacierrecaja==""){
		   ver_vetana_informativa("FALTO INICIAR UNA CAJA", "#")
		   verCerrarVentanaAbmAperturaCierreCaja1()
		   return
	   }
	DatosAutoCompleteCredito=new Array ();
	var inptTotalPagado = document.getElementById('inptTotalPagado').value
	if (inptTotalPagado > 0) {
		ver_vetana_informativa("NO SE PUEDE AÑADIR DETALLE A LA VENTA POR QUE ESTE YA CUENTA CON UN PAGO", "#")
		return false;
	}
	
	var acceso = document.getElementById('inptAccesoCreditoVentaCliente').value
	if(acceso=="Denegado"){
		var estado=document.getElementById("inptSeleccTipoVenta").value;
		if(estado=="CREDITO"){
			ver_vetana_informativa("EL CLIENTE NO ESTA AUTORIZADO PARA VENTAS A CREDITOS", "#")
		return false;
		}
	}
	
	
	document.getElementById('inptSeleccTipoVenta').disabled=true
	
	var inptCodProductoVenta = document.getElementById('inptCodProductoVenta').value
	var inptProductoVenta = document.getElementById('inptProductoVenta').value
	var inptCantProductoVenta = document.getElementById('inptCantProductoVenta').value
	var inpTotalCostoVenta = document.getElementById('inpTotalCostoVenta').value
	var inptCostoProductoVenta = document.getElementById('inptCostoProductoVenta').value
	var inptComisionVenta = document.getElementById('inptComisionVenta').value
	var inptlocalVenta = document.getElementById('inptlocalVenta').value
	var inptObservacionDetalleVenta = document.getElementById('inptObservacionDetalleVenta').value
	var inptDescuentoProductoVenta = document.getElementById('inptDescuentoProductoVenta').value
	var inptDetallesVentaProductos = document.getElementById('inptDetallesVentaProductos').value
	
	
	if(inptCantProductoVenta<=0|| inptCantProductoVenta==""){
				ver_vetana_informativa("FAVOR AGREGAR CANTIDAD")
				return false;
			}
	
	var resultado= Number(StockVenta) -  Number(inptCantProductoVenta)
	 
	
		if( accesosuser["STOCK0"]["accion"]!="SI")
	{
			if(resultado<0){
				ver_vetana_informativa("NO PUEDES VENDER PRODUCTOS CON STOCK MENOR A 0")
				return false;
			}
	}
	
	
	
	 inptDetallesVentaProductos =inptDetallesVentaProductos.replace(new RegExp("\n","g"), "<br>")
	var CuotaNro =$("select[id=inpTSeleccCosto]").children(":selected").attr("id")
	if (idFkProducto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO", "#")
		return false;
	}
	var nroid=Math.floor((Math.random() * 1000) + 1);
	var pagina="<table id='tdDetalleVenta_"+nroid+"' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>"
+"<tr id='tbSelecRegistro' onclick='SeleccionarProductoVentaOffline(this)'  name='tdDetalleVentaOffline'>"
+"<td id='td_id_1' style='display:none'>"+idFkProducto+"</td>"
+"<td id='td_id_2' style='display:none'>"+nroid+"</td>"
+"<td  id='td_datos_8' style='display:none'>"+inptCodProductoVenta+"</td>"
+"<td  id='td_datos_1' style='width:20%;'>"+inptProductoVenta+"</td>"
+"<td  id='td_datos_6' style='display:none'>"+inptDetallesVentaProductos+"</td>"
+"<td  id='td_datos_3' style='width:10%'>"+inptCostoProductoVenta+"</td>"
+"<td  id='td_datos_4' style='width:5%'>"+inptCantProductoVenta+"</td>"
+"<td  id='td_datos_9' style='display:none'>"+inptDescuentoProductoVenta+"</td>"
+"<td  id='td_datos_5' style='width:10%'>"+inpTotalCostoVenta+"</td>"
+"<td  id='td_datos_7' style='width:10%'>"+inptComisionVenta+"</td>"
+"<td  id='td_datos_10' style='display:none'>"+CuotaNro+"</td>"
+"</tr>"
+"</table>"
document.getElementById("table_abm_detalle_venta").innerHTML+=pagina;
var totalVenta=0;
var SubtotalVenta=0;
var totaldescuento=0;
var control=0;
$("tr[name=tdDetalleVentaOffline]").each(function(i, elementohtml){
var total=$(elementohtml).children('td[id="td_datos_5"]').html();
var totaldescuentos=$(elementohtml).children('td[id="td_datos_9"]').html();
totaldescuentos=QuitarSeparadorMilValor(totaldescuentos)
total=QuitarSeparadorMilValor(total)
totalVenta=Number(totalVenta)+Number(total)
totaldescuento=Number(totaldescuento)+Number(totaldescuentos)
SubtotalVenta=Number(totalVenta)+Number(totaldescuento)
control=control+1;
	   });
	   
	   if(control=="1"){
		   DatosAutoCompleteCredito.push(CuotaNro)
	   }
	
	   
document.getElementById("inptSubTotalVenta").value=separadordemilesnumero(SubtotalVenta);
document.getElementById("inptTotalVenta").value=separadordemilesnumero(totalVenta);
document.getElementById("inptTotalVenta2").innerHTML=separadordemilesnumero(totalVenta);
document.getElementById("inptTotalDescuento").value=separadordemilesnumero(totaldescuento);
OpcionesTipoVenta();
document.getElementById("btnFinalizarVenta").style.display=""
document.getElementById("btnCancelarVenta").style.display=""

document.getElementById('inptCantProductoVenta').value = ""
document.getElementById('inpTotalCostoVenta').value = ""
document.getElementById('inptCostoProductoVenta').value = ""
document.getElementById('inptDescuentoProductoVenta').value = "0"
document.getElementById('inptObservacionDetalleVenta').value = ""
document.getElementById('inptComisionVenta').value = ""
document.getElementById('inpTSeleccCosto').innerHTML = ""
document.getElementById('inptObservacionDetalleVenta').value = ""
document.getElementById('inptProductoVenta').value = ""
document.getElementById('inptDetallesVentaProductos').value = ""
document.getElementById('btnAbmVenta').style.display = "none"
document.getElementById('btnAddDetallesaVenta').style.backgroundColor = "#b7b7b7";
idFkProducto = ""

}
var elemSeleccDetalleProdVentaOff="";
var idfkTableDetalle="";
function SeleccionarProductoVentaOffline(datos){
	elemSeleccDetalleProdVentaOff=datos;
	idfkTableDetalle=$(datos).children('td[id="td_id_2"]').html();
	document.getElementById("inptCodDetalleOff").value=$(datos).children('td[id="td_datos_8"]').html();
	document.getElementById("inptNombreProductoDetalleOpcionOff").value=$(datos).children('td[id="td_datos_1"]').html();
	verCerraOpcionDetalleProducto()
}
function verCerraOpcionDetalleProducto(){
	if(document.getElementById("divEliminarProductoDetalle").style.display=="none"){
		document.getElementById("divEliminarProductoDetalle").style.display=""
	}else{
		document.getElementById("divEliminarProductoDetalle").style.display="none"
	}
}
function quitarEsteProductoDelDetalleVenta(){
	elemSeleccDetalleProdVentaOff.remove()
	$("table[id=tdDetalleVenta_"+idfkTableDetalle+"]").remove()	
	var totalVenta=0;
	var totalDescuento=0;
	var SubtotalVenta=0;
$("tr[name=tdDetalleVentaOffline]").each(function(i, elementohtml){
var total=$(elementohtml).children('td[id="td_datos_5"]').html();
var totaldescuentos=$(elementohtml).children('td[id="td_datos_9"]').html();
total=QuitarSeparadorMilValor(total)
totaldescuentos=QuitarSeparadorMilValor(totaldescuentos)
totalVenta=Number(totalVenta)+Number(total)
totalDescuento=Number(totalDescuento)+Number(totaldescuentos)
SubtotalVenta=Number(totalVenta)+Number(totalDescuento)
	   });
document.getElementById("inptSubTotalVenta").value=separadordemilesnumero(SubtotalVenta);
document.getElementById("inptTotalVenta").value=separadordemilesnumero(totalVenta);
document.getElementById("inptTotalVenta2").innerHTML=separadordemilesnumero(totalVenta);
document.getElementById("inptTotalDescuento").value=separadordemilesnumero(totalDescuento);
if(totalVenta==0){
	document.getElementById("btnFinalizarVenta").style.display="none"
	document.getElementById("btnVerCreditos").style.display="none"
	document.getElementById("btnCancelarVenta").style.display="none"
	document.getElementById('inptSeleccTipoVenta').disabled=false
}
	
	document.getElementById("divEliminarProductoDetalle").style.display="none"
}
function guardaryfinalizarventa(){
	
	if(idabmAperturacierrecaja==""){
		   ver_vetana_informativa("FALTO INICIAR UNA CAJA", "#")
		   verCerrarVentanaAbmAperturaCierreCaja1()
		   return
	   }
	
	var controlDetalle=0;
	$("tr[name=tdDetalleVenta]").each(function(i, elementohtml){
controlDetalle=2;
	   });
	$("tr[name=tdDetalleVentaOffline]").each(function(i, elementohtml){
controlDetalle=1;
	   });
	   
	   if(controlDetalle=="0"){
		ver_vetana_informativa("FALTA DETALLES A LA VENTA", "#")
		return false;
	}
	if(controlDetalle=="1"){
		verificarcamposdetallesventa()
	}
	if(controlDetalle=="2"){
		 verCerrarConfigCredito("1")
	}
}
var idDetalleVenta = "";
/*SI SE MODIFICA LA FUNCION VERIFICAR DETALLE VENTA TAMBIEN HAY QUE CAMBIAR VERIFICAR DETALLE VENTA CREDITO*/
function verificarcamposdetallesventa() {
	var inptTotalPagado = document.getElementById('inptTotalPagado').value
	if (inptTotalPagado > 0) {
		ver_vetana_informativa("NO SE PUEDE AÑADIR DETALLE A LA VENTA POR QUE ESTE YA CUENTA CON UN PAGO", "#")
		return false;
	}
	var controldetalle=0;
	$("tr[name=tdDetalleVentaOffline]").each(function(i, elementohtml){
controldetalle=controldetalle+1;
	   });
	if(controldetalle=="0"){
		ver_vetana_informativa("FALTO AÑADIR DETALLES", "#")
		return false;
	}
	var vendedor1 = document.getElementById('inptVendedorVenta1').value
	var inptFechaVenta = document.getElementById('inptFechaVenta').value
	var inptClienteVenta = document.getElementById('inptClienteVenta').value
	var inptSeleccTipoVenta = document.getElementById('inptSeleccTipoVenta').value
	var inptComisionVentaCobrador = document.getElementById('inptComisionVentaCobrador').value
	var inptCobradorVenta = document.getElementById('inptCobradorVenta').value
	var inpCodVenta = document.getElementById('inpCodVenta').value
	var inptlocalVenta = document.getElementById('inptlocalVenta').value
	var inptGaranteVenta = document.getElementById('inptGaranteVenta').value
	var inptSeleccTipoComprobanteVenta = document.getElementById('inptSeleccTipoComprobanteVenta').value
	var inptSeleccPuntoExpedicionVenta = $("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
	var nrocaja = document.getElementById('pCaja').innerHTML
	if (inpCodVenta == "") {
		document.getElementById('inpCodVenta').value = "";
		document.getElementById('inptNroVenta').value = "";
		inpCodVenta = "";
	}

	if (idFkVendedor1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN VENDEDOR", "#")
		return false;
	}
	if (inptFechaVenta == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA FECHA", "#")
		return false;
	}

	if (inptComisionVentaCobrador == "") {
		inptComisionVentaCobrador="0"
	}

	if (idFkCliente == "") {
		document.getElementById('inptClienteVenta').value = "CLIENTE OCACIONAL";
		document.getElementById('inptClienteVenta2').value = "CLIENTE OCACIONAL";
		document.getElementById('inptDocClienteVenta').value = "";
		document.getElementById('inptDocClienteVenta2').value = "";
		idFkCliente = "7";
	}
	

	if (inptCobradorVenta == "") {
		idFkCobrador = "9";
		cobradorcredito = "9";
		document.getElementById('inptCobradorVenta').value = "SIN COBRADOR";
		document.getElementById('inptCobradorCargarPago').value = "SIN COBRADOR";
		document.getElementById('inptCobradorConfirmar').value = "SIN COBRADOR";
	}
	if (inptGaranteVenta == "") {
		idGaranteFk = "5";
		document.getElementById('inptGaranteVenta').value = "SIN GARANTE";
	}
	if(inptSeleccTipoVenta=="CREDITO"){
	 if((document.getElementById('inptClienteVenta').value == "CLIENTE OCACIONAL")|| (document.getElementById('inptClienteVenta').value == "")){
		ver_vetana_informativa("EL CLIENTE NO ES VÁLIDO", "#")
		return
	}
	verCerrarConfigCredito("1")
	return
	}else{
		
		verCerrarConfigCredito("1")
		return;
	}
/*
    var accion = "nuevo";
	var tipo="1"
    abmdetalleventa(nrocaja,inptSeleccPuntoExpedicionVenta,inptSeleccTipoComprobanteVenta,inptFechaVenta,inptComisionVentaCobrador,idFkCliente,idGaranteFk,inptSeleccTipoVenta,idFkCobrador,idFkVendedor1, idFkVendedor2, idabmVenta, inpCodVenta, inptlocalVenta, accion,tipo);
	*/
}

var controlVenta="1";

function VerificarCamposVentaContago(){
	
	if(controlVenta=="2"){
		return;
	}
	controlVenta="2";
	var inptTotalPagado = document.getElementById('inptTotalPagado').value
	if (inptTotalPagado > 0) {
		ver_vetana_informativa("NO SE PUEDE AÑADIR DETALLE A LA VENTA POR QUE ESTE YA CUENTA CON UN PAGO", "#")
		return false;
	}
	var controldetalle=0;
	$("tr[name=tdDetalleVentaOffline]").each(function(i, elementohtml){
controldetalle=controldetalle+1;
	   });
	if(controldetalle=="0"){
		ver_vetana_informativa("FALTO AÑADIR DETALLES", "#")
		return false;
	}
	var vendedor1 = document.getElementById('inptVendedorVenta1').value
	var inptFechaVenta = document.getElementById('inptFechaVenta').value
	var inptClienteVenta = document.getElementById('inptClienteVenta').value
	var inptSeleccTipoVenta = document.getElementById('inptSeleccTipoVenta').value
	var inptComisionVentaCobrador = document.getElementById('inptComisionVentaCobrador').value
	var inptCobradorVenta = document.getElementById('inptCobradorVenta').value
	var inpCodVenta = document.getElementById('inpCodVenta').value
	var inptlocalVenta = document.getElementById('inptlocalVenta').value
	var inptGaranteVenta = document.getElementById('inptGaranteVenta').value
	var inptSeleccTipoComprobanteVenta = document.getElementById('inptSeleccTipoComprobanteVenta').value
	var inptSeleccPuntoExpedicionVenta = $("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
	var nrocaja = document.getElementById('pCaja').innerHTML
	if (inpCodVenta == "") {
		document.getElementById('inpCodVenta').value = "";
		document.getElementById('inptNroVenta').value = "";
		inpCodVenta = "";
	}

	if (idFkVendedor1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN VENDEDOR", "#")
		return false;
	}
	if (inptFechaVenta == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA FECHA", "#")
		return false;
	}

	if (inptComisionVentaCobrador == "") {
		inptComisionVentaCobrador="0"
	}

	if (idFkCliente == "") {
		document.getElementById('inptClienteVenta').value = "CLIENTE OCACIONAL";
		document.getElementById('inptClienteVenta2').value = "CLIENTE OCACIONAL";
		document.getElementById('inptDocClienteVenta').value = "";
		document.getElementById('inptDocClienteVenta2').value = "";
		idFkCliente = "7";
	}
	

	if (inptCobradorVenta == "") {
		idFkCobrador = "9";
		cobradorcredito = "9";
		document.getElementById('inptCobradorVenta').value = "SIN COBRADOR";
		document.getElementById('inptCobradorCargarPago').value = "SIN COBRADOR";
		document.getElementById('inptCobradorConfirmar').value = "SIN COBRADOR";
	}
	if (inptGaranteVenta == "") {
		idGaranteFk = "5";
		document.getElementById('inptGaranteVenta').value = "SIN GARANTE";
	}
	if(inptSeleccTipoVenta=="CREDITO"){
	 if((document.getElementById('inptClienteVenta').value == "CLIENTE OCACIONAL")|| (document.getElementById('inptClienteVenta').value == "")){
		ver_vetana_informativa("EL CLIENTE NO ES VÁLIDO", "#")
		return
	}
	verCerrarConfigCredito("1")
	return
	}
    var accion = "nuevo";
	var tipo="1"
	
	
    abmdetalleventa(nrocaja,inptSeleccPuntoExpedicionVenta,inptSeleccTipoComprobanteVenta,inptFechaVenta,inptComisionVentaCobrador,idFkCliente,idGaranteFk,inptSeleccTipoVenta,idFkCobrador,idFkVendedor1, idFkVendedor2, idabmVenta, inpCodVenta, inptlocalVenta, accion,tipo);
	
}


function verificarcamposdetallesventacredito() {
	var inptTotalPagado = document.getElementById('inptTotalPagado').value
	if (inptTotalPagado > 0) {
		ver_vetana_informativa("NO SE PUEDE AÑADIR DETALLE A LA VENTA POR QUE ESTE YA CUENTA CON UN PAGO", "#")
		return false;
	}
	var controldetalle=0;
	$("tr[name=tdDetalleVentaOffline]").each(function(i, elementohtml){
controldetalle=controldetalle+1;
	   });
	if(controldetalle=="0"){
		ver_vetana_informativa("FALTO AÑADIR DETALLES", "#")
		return false;
	}
	var inptFechaVenta = document.getElementById('inptFechaVenta').value
	var inptClienteVenta = document.getElementById('inptClienteVenta').value
	var inptSeleccTipoVenta = document.getElementById('inptSeleccTipoVenta').value
	var inptComisionVentaCobrador = document.getElementById('inptComisionVentaCobrador').value
	var inptCobradorVenta = document.getElementById('inptCobradorVenta').value
	var inpCodVenta = document.getElementById('inpCodVenta').value
	var inptlocalVenta = document.getElementById('inptlocalVenta').value
	var inptGaranteVenta = document.getElementById('inptGaranteVenta').value
	var inptSeleccTipoComprobanteVenta = document.getElementById('inptSeleccTipoComprobanteVenta').value
	var inptSeleccPuntoExpedicionVenta = $("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
	var nrocaja = document.getElementById('pCaja').innerHTML
	if (inpCodVenta == "") {
		document.getElementById('inpCodVenta').value = "";
		document.getElementById('inptNroVenta').value = "";
		inpCodVenta = "";
	}

	if (inptFechaVenta == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA FECHA", "#")
		return false;
	}

	if (inptComisionVentaCobrador == "") {
		inptComisionVentaCobrador="0"
	}

	if (idFkCliente == "") {
		document.getElementById('inptClienteVenta').value = "CLIENTE OCACIONAL";
		document.getElementById('inptClienteVenta2').value = "CLIENTE OCACIONAL";
		document.getElementById('inptDocClienteVenta').value = "";
		document.getElementById('inptDocClienteVenta2').value = "";
		idFkCliente = "7";
	}
	

	if (inptCobradorVenta == "") {
		idFkCobrador = "9";
		cobradorcredito = "9";
		document.getElementById('inptCobradorVenta').value = "SIN COBRADOR";
		document.getElementById('inptCobradorCargarPago').value = "SIN COBRADOR";
		document.getElementById('inptCobradorConfirmar').value = "SIN COBRADOR";
	}
	if (inptGaranteVenta == "") {
		idGaranteFk = "5";
		document.getElementById('inptGaranteVenta').value = "SIN GARANTE";
	}
	if(inptSeleccTipoVenta=="CREDITO"){
	 if((document.getElementById('inptClienteVenta').value == "CLIENTE OCACIONAL")|| (document.getElementById('inptClienteVenta').value == "")){
		ver_vetana_informativa("EL CLIENTE NO ES VÁLIDO", "#")
		return
	}
	
	}

    var accion = "nuevo";
	var tipo="2"
    abmdetalleventa(nrocaja,inptSeleccPuntoExpedicionVenta,inptSeleccTipoComprobanteVenta,inptFechaVenta,inptComisionVentaCobrador,idFkCliente,idGaranteFk,inptSeleccTipoVenta,idFkCobrador,idFkVendedor1, idFkVendedor2, idabmVenta, inpCodVenta, inptlocalVenta, accion,tipo);
}
function abmdetalleventa(caja,puntoexpedicion,tipo_comprobante,fecha_venta,comisioncobrador,cod_clienteFK,idGaranteFk,TipoVenta,cod_cobradorFK,idFkVendedor1, idFkVendedor2,cod_ventaFK, num_factura, cod_local, accion,tipo) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	var control=1;
	$("tr[name=tdDetalleVentaOffline]").each(function(i, elementohtml){	
	var idproducto=$(elementohtml).children('td[id="td_id_1"]').html();
    datos.append("cod_productoFK"+control, idproducto)	
	var cantidad=$(elementohtml).children('td[id="td_datos_4"]').html();
    datos.append("cantidad_detalle"+control, cantidad)
	var precio=$(elementohtml).children('td[id="td_datos_3"]').html();
    datos.append("precio_producto"+control, precio)	
	var subotal=$(elementohtml).children('td[id="td_datos_5"]').html();
    datos.append("subtotal"+control, subotal)	
	var comision=$(elementohtml).children('td[id="td_datos_7"]').html();
    datos.append("comision"+control, comision)	
	var descuento=$(elementohtml).children('td[id="td_datos_9"]').html();
    datos.append("descuento"+control, descuento)	
	var detalleproducto=$(elementohtml).children('td[id="td_datos_6"]').html();
    datos.append("detalleproducto"+control, detalleproducto)
	control=control+1;	
	   });
	control=control-1;	
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_ventaFK", cod_ventaFK)
	datos.append("num_factura", num_factura)
	datos.append("comisioncobrador", comisioncobrador)
	datos.append("cod_local", cod_local)
	datos.append("TipoPago", "Corrido")
	datos.append("fecha_venta", fecha_venta)
	datos.append("cod_clienteFK", cod_clienteFK)
	datos.append("idGaranteFk", idGaranteFk)
	datos.append("cod_cobradorFK", cod_cobradorFK)
	datos.append("vendedor1", idFkVendedor1)
	datos.append("vendedor2", idFkVendedor2)
	datos.append("TipoVenta", TipoVenta)
	datos.append("tipo_comprobante", tipo_comprobante)
	datos.append("puntoexpedicion", puntoexpedicion)
	datos.append("totalRegistro", control)
	datos.append("caja", caja)
	var OpAjax = $.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
				   controlVenta="1"
									
					idabmVenta = datos["3"]
					idFkVenta = datos["3"]
					var nrofactura = datos["4"]
                    var contador=0;            
					document.getElementById('inpCodVenta').disabled = true
					document.getElementById('inpCodVenta').className = "inputTextDisable"
					buscardetallesventa()
					if(document.getElementById('inptNroVenta').value==""){
					document.getElementById('inptNroVenta').value = nrofactura
					document.getElementById('inpCodVenta').value = nrofactura
					var puntoExpedicion=$("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
					if(puntoExpedicion==""){						
					document.getElementById("pNroFactuaCaja").innerHTML = "*"+nrofactura+"*"
					}else{
						document.getElementById("pNroFactuaCaja").innerHTML ="*"+puntoExpedicion+"-"+nrofactura+"*"
					}
					}
					document.getElementById('tdImprimirVenta').style.display = ""
					document.getElementById('btnAbmVenta').style.display = ""
					document.getElementById('btnAbmVenta').value = "Editar Datos"
					if(tipo=="1"){
					abmconfirmarPagoContado()
					}
					if(tipo=="2"){
						crearcreditodesdeventa()
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
function buscardetallesventa() {
	document.getElementById("table_abm_detalle_venta").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idabmVenta,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
			document.getElementById("table_abm_detalle_venta").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_detalle_venta").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];

				 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {

					var datos_buscados = datos[2];
					 detallesRecibo = datos[5];
					 paginaDetalleTicket = datos[14];
					 paginaticket = datos[14];
					 SubtotalRecibovaiva5 = datos[6];
					 SubtotalRecibovaiva10 = datos[7];
					 totalesReciboDetalleiva10 = datos[8];
					 totalesReciboDetalleiva15 = datos[9];
					 totalInteresRecibo = datos[36];
					document.getElementById("table_abm_detalle_venta").innerHTML = datos_buscados
					document.getElementById("inptTotalVenta").value = datos[3]
					document.getElementById("inptTotalDescuento").value = datos[37]
					document.getElementById("inptSubTotalVenta").value = datos[38]
					document.getElementById("inptTotalVenta2").innerHTML = datos[3]
					totalesRecibo = datos[3]
					ImportePagare = datos[3]
					document.getElementById("inptTotalPagado").value = datos[4]
					document.getElementById("inptTotalPagadoOpcionesPago").value = datos[4]
				    NombreRecibo=datos[10]
				    DireccionRecibo=datos[11]
                    telefonoRecino=datos[12]
                    DocumentoRecibo=datos[13]
                    document.getElementById('inptDocClienteVenta').value=datos[13]
                    document.getElementById('inptDocClienteVenta2').value=datos[13]
                    PlazoRecibo=datos[15]
                    facturanroPagare=datos[25]
                    vencimientopagare=datos[26]
                    ZonaRecibo=datos[27]
                    telefonoRecinoGarante=datos[28]
                    ZonaReciboGarante=datos[29]
                    InteresRecibo=datos[30]
                    DeudaActualRecibo=datos[31]
                    DiasAtrasado=datos[32]
                    RucRecibo=datos[33]
                    TotalDescuentoRecibo=datos[34]
                    CuotasRestante=datos[35]
                    nroPagare=idabmVenta			
					
					 zonagarante=datos[29]
	
					document.getElementById("inptEntregaConfCredito").value = datos[17]
					document.getElementById("inptNroCuotasConfCredito").value = datos[19]
					document.getElementById("inptMontoPagoConfCredito").value = datos[23]
					document.getElementById("inptFechaInicioConfCredito").value = datos[16]
					document.getElementById("inptInteresConfCredito").value = datos[21]
					document.getElementById("inptDiasConfCredito").value = datos[20]
					document.getElementById("inputSelectMetodoConfCredito").value = datos[22]
					document.getElementById("inptDocGaranteVenta").value = datos[39]
					if(datos[4]!="0"){
						document.getElementById("lblInfoConfCredito").innerHTML = "Estos datos ya no pueden ser editados por que la venta ya cuenta con un pago"
					document.getElementById("inptEntregaConfCredito").disabled=true
					document.getElementById("inptNroCuotasConfCredito").disabled=true
					document.getElementById("inptMontoPagoConfCredito").disabled=true
					document.getElementById("inptFechaInicioConfCredito").disabled=true
					document.getElementById("inptInteresConfCredito").disabled=true
					document.getElementById("inptDiasConfCredito").disabled=true
					document.getElementById("inputSelectMetodoConfCredito").disabled=true
					}else{
					document.getElementById("lblInfoConfCredito").innerHTML = ""					
					document.getElementById("inptEntregaConfCredito").disabled=false
					document.getElementById("inptNroCuotasConfCredito").disabled=false
					document.getElementById("inptMontoPagoConfCredito").disabled=false
					document.getElementById("inptFechaInicioConfCredito").disabled=false
					document.getElementById("inptInteresConfCredito").disabled=false
					document.getElementById("inptDiasConfCredito").disabled=false
					document.getElementById("inputSelectMetodoConfCredito").disabled=false
					}
					
					if(datos[24]>1){
						document.getElementById("lblInfoMotoCredito").innerHTML = "Este credito tiene diferentes montos"
					}else{
						document.getElementById("lblInfoMotoCredito").innerHTML = ""
					}
					if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
						 document.getElementById("btnVerCreditos").style.display=""
		                 AutoCompletarCamposCuotas()
					}else{
						 document.getElementById("btnVerCreditos").style.display="none"
					}
             		  
				  document.getElementById("btnFinalizarVenta").style.display=""
				  document.getElementById("btnCancelarVenta").style.display=""
					OpcionesTipoVenta();
					
					
					
					


				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function verCerrarConfigCredito(d){
	controlVenta="1";
	if(d=="1"){		
	if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
	
    if((document.getElementById('inptClienteVenta').value == "CLIENTE OCACIONAL")|| (document.getElementById('inptClienteVenta').value == "")){
		ver_vetana_informativa("EL CLIENTE NO ES VÁLIDO", "#")
		return
	}	
		document.getElementById("tdEfectoFinalizarVentaCredito").className=""
	document.getElementById("divFinalizarVentaAcredito").style.display="";
	var inptTotalPagado = document.getElementById('inptTotalPagado').value
	if (inptTotalPagado!="0" && inptTotalPagado!="") {
	document.getElementById("btnConfCredito").style.display='none'
	}else{	
	AutoCompletarCamposCuotas()
	document.getElementById("btnConfCredito").style.display=''
	}	
		}else{
			var inptTotalPagado = document.getElementById('inptTotalPagado').value	
	if (inptTotalPagado!="0" && inptTotalPagado!="") {
	return
	}
	document.getElementById('inptTotalVentaTerminar').value=document.getElementById('inptTotalVenta').value
	document.getElementById('inptDescuentoVentaTerminar').value="0"
	document.getElementById('inptMontoVentaTerminarEfectivo').value=document.getElementById('inptTotalVenta').value;
	document.getElementById('inptVueltoVentaTerminar').value="0"
	document.getElementById('inptMontoVentaTerminarTarjeta').value="0"
	document.getElementById("tdEfectoFinalizarVentaContado").className=""
		document.getElementById("divFinalizarVentaAContado").style.display="";	
			document.getElementById('inptMontoVentaTerminarEfectivo').focus()			
			$("#inptMontoVentaTerminarEfectivo").select();
		}
	}else{
		if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
		//document.getElementById("divFinalizarVentaAcredito").style.display="none";
		document.getElementById("tdEfectoFinalizarVentaCredito").className="magictime vanishOut"
	$("div[id=divFinalizarVentaAcredito]").fadeOut(500);	
		}else{
		//document.getElementById("divFinalizarVentaAContado").style.display="none";
		document.getElementById("tdEfectoFinalizarVentaContado").className="magictime vanishOut"
	$("div[id=divFinalizarVentaAContado]").fadeOut(500);	
		}
		
	}
}
function AutoCompletarCamposCuotas(){
	var inptTotalPagado = document.getElementById('inptTotalPagado').value
	if(DatosAutoCompleteCredito[0]!=undefined && (inptTotalPagado=="0" || inptTotalPagado=="")){
	document.getElementById("inptEntregaConfCredito").value=0;
	document.getElementById("inptConfirmarPagoEntrega").value="SI";
	document.getElementById("inputSelectMetodoConfCredito").value="Mensual";
	document.getElementById("inptNroCuotasConfCredito").value=DatosAutoCompleteCredito[0];
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptFechaInicioConfCredito').value = f.getFullYear() + "-" + mes + "-" + dia;
	SeleccEntregaInicial(document.getElementById("inptConfirmarPagoEntrega"))
	
	}
}
function VerCerrarConfCredito(d){
		try {
	var inptTotalPagado = QuitarSeparadorMilValor(document.getElementById('inptTotalPagado').value)	
	if (inptTotalPagado > 0) {
		document.getElementById("inptSeleccTipoVenta").value=controltipoventa				
	if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
		document.getElementById("divConfCreditoVenta").style.display="";
		document.getElementById("btnVerCreditos").style.display="";
		document.getElementById("btnPagoAlContado").style.display="none";
	}else{
		var inptTotalVenta = QuitarSeparadorMilValor(document.getElementById('inptTotalVenta').value)
		document.getElementById("divConfCreditoVenta").style.display="none";
		if(inptTotalVenta==inptTotalPagado){
		document.getElementById("btnVerCreditos").style.display="none";
		document.getElementById("btnPagoAlContado").style.display="none";
		}else{
				document.getElementById("btnVerCreditos").style.display="none";
		document.getElementById("btnPagoAlContado").style.display="";
		}
	}
	
	}else{
		
		var inptTotalVenta = QuitarSeparadorMilValor(document.getElementById('inptTotalVenta').value)
		if (inptTotalVenta > 0) {
			if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
		document.getElementById("divConfCreditoVenta").style.display="";
		document.getElementById("btnVerCreditos").style.display="";
		document.getElementById("btnPagoAlContado").style.display="none";
	}else{
		document.getElementById("divConfCreditoVenta").style.display="none";
		document.getElementById("btnVerCreditos").style.display="none";
		document.getElementById("btnPagoAlContado").style.display="";
	}
		}else {
				document.getElementById("btnVerCreditos").style.display="none";
				document.getElementById("btnPagoAlContado").style.display="none";
		}
		
		}
		} catch (error) {
				document.getElementById("btnVerCreditos").style.display="none";
				document.getElementById("btnPagoAlContado").style.display="none";
		
			}
			
				if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
		document.getElementById("divConfCreditoVenta").style.display="";
	}else{
		document.getElementById("divConfCreditoVenta").style.display="none";
		
	}
}
function SeleccEntregaInicial(datos){	
	if(datos.value=="NO"){
	document.getElementById("inptEntregaConfCredito").value="0";
	document.getElementById("inptEntregaConfCredito").disabled=true
	calcular_cuota_desde_venta()
	}else{
	document.getElementById("inptEntregaConfCredito").value="0";
	calcular_cuota_desde_venta()
	document.getElementById("inptEntregaConfCredito").value=document.getElementById("inptMontoPagoConfCredito").value
	calcular_cuota_desde_venta()
	document.getElementById("inptEntregaConfCredito").disabled=false
	}
}
function calcular_cuota_desde_venta() {
	var t = QuitarSeparadorMilValor(document.getElementById('inptTotalVenta').value);
	var c = QuitarSeparadorMilValor(document.getElementById('inptNroCuotasConfCredito').value);
	var e = QuitarSeparadorMilValor(document.getElementById('inptEntregaConfCredito').value);
	if (isNaN(t) || t=="" ) {
	 t = QuitarSeparadorMilValor(document.getElementById('inpTotalCostoVenta').value);
	}
	if (isNaN(e)) {
		document.getElementById('inptEntregaConfCredito').value = 0;	
		e = 0;
	}
	if (isNaN(c)) {
		document.getElementById('inptNroCuotasConfCredito').value = 1;
		document.getElementById('inptMontoPagoConfCredito').value = document.getElementById('inptTotalVenta').value;
		c = 0;
	}else{
		if(e>0){
		c=c-1;	
		}		
		if(c<0){
		c=1;
		}
	}
	t=Number(t)-Number(e)
	var c = parseFloat(c);
	var t = parseFloat(t);
	document.getElementById('inptMontoPagoConfCredito').value = Math.round(t / c);
	separadordemiles(document.getElementById('inptMontoPagoConfCredito'))
	separadordemiles(document.getElementById('inptEntregaConfCredito'))
}
function crearcreditodesdeventa() {
	
	
	var inptNroCuotasConfCredito = document.getElementById('inptNroCuotasConfCredito').value
	var inptMontoPagoConfCredito = document.getElementById('inptMontoPagoConfCredito').value
	var inptFechaInicioConfCredito = document.getElementById('inptFechaInicioConfCredito').value
	var inputSelectMetodoConfCredito = document.getElementById('inputSelectMetodoConfCredito').value
	var inptTotalPagado = document.getElementById('inptTotalPagado').value
	var inptInteresConfCredito = document.getElementById('inptInteresConfCredito').value
	var inptDiasConfCredito = document.getElementById('inptDiasConfCredito').value
	var inptEntregaConfCredito = document.getElementById('inptEntregaConfCredito').value
	var inptConfirmarPagoEntrega = document.getElementById('inptConfirmarPagoEntrega').value
	if (inptTotalPagado > 0) {
	return false;
	}
	if (inptNroCuotasConfCredito <=0) {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE CUOTA", "#")
		return false;
	}
	if (inptNroCuotasConfCredito == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE CUOTA", "#")
		return false;
	}
	if (inptMontoPagoConfCredito == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MONTO DE PAGO", "#")
		return false;
	}
	if (inptFechaInicioConfCredito == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO DE PAGO", "#")
		return false;
	}
	if (inputSelectMetodoConfCredito == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL METODO DE PAGO", "#")
		return false;
	}
	if (inptInteresConfCredito == "") {
		ver_vetana_informativa("FALTO INGRESAR EL INTERES DE PAGO", "#")
		return false;
	}
	if (inptDiasConfCredito == "") {
		ver_vetana_informativa("FALTO INGRESAR LOS DIAS DE GRACIA", "#")
		return false;
	}
	if(idabmVenta==""){
		verificarcamposdetallesventacredito()
		return
	}
    abmcreditosVenta(inptConfirmarPagoEntrega,inptNroCuotasConfCredito, inptMontoPagoConfCredito, inptFechaInicioConfCredito, inputSelectMetodoConfCredito, inptInteresConfCredito, inptDiasConfCredito, inptEntregaConfCredito, idabmVenta);
}
function abmcreditosVenta(pagoentrega,nroCuota, Monto, iniciopago, metodopago, interes, dias, entrega, cod_venta) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "nuevodesdeventa")
	datos.append("cod_venta", cod_venta)
	datos.append("Monto", Monto)
	datos.append("metodopago", metodopago)
	datos.append("iniciopago", iniciopago)
	datos.append("nroCuota", nroCuota)
	datos.append("dias", dias)
	datos.append("interes", interes)
	datos.append("entrega", entrega)
	datos.append("pagoentrega", pagoentrega)
	datos.append("idGaranteFk", idGaranteFk)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
								
				 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				   
				   DatosAutoCompleteCredito=new Array();
					document.getElementById("inptEntregaConfCredito").value = datos[17]
					document.getElementById("inptNroCuotasConfCredito").value = datos[19]
					PlazoRecibo = datos[19]
					document.getElementById("inptMontoPagoConfCredito").value = datos[23]
					document.getElementById("inptFechaInicioConfCredito").value = datos[16]
					document.getElementById("inptInteresConfCredito").value = datos[21]
					document.getElementById("inptDiasConfCredito").value = datos[20]
					document.getElementById("inputSelectMetodoConfCredito").value = datos[22]
					document.getElementById("inptTotalPagado").value = datos[27]
					document.getElementById("inptConfirmarNroFactura").value = document.getElementById("inptNroVenta").value	
					//totalesRecibo = datos[23]
					InteresRecibo = datos[24]
					DeudaActualRecibo = datos[25]
					DiasAtrasado = datos[26]
					CuotasRestante = datos[28]
					if(datos[27]!="0"){
						document.getElementById("btnConfCredito").style.display="none"
					}
					 if(document.getElementById("inptSeleccTipoComprobanteVenta").value=="FACTURA"){  
					 document.getElementById("inptSeleccPuntoExpedicionConfirmarNro").value=$("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
					 document.getElementById("inptConfirmarNroFactura").value=document.getElementById("inptNroVenta").value
					 document.getElementById("divOpcionesImpresion").style.display=""
					 document.getElementById("divConfirmarNroDeFactura").style.display=""
					 document.getElementById("btnImprimirticket").style.display=""
					 document.getElementById("btnImprimirFactura").style.display=""
					 document.getElementById("btnImprimirPagare").style.display=""
					 var tipo=document.getElementById("inptSeleccTipoComprobanteVenta").value
					 var caja=document.getElementById("pCaja").innerHTML
					 var subtotal=document.getElementById("inptSubTotalVenta").value
					 var descuento=document.getElementById("inptTotalDescuento").value
					 var totalpagado=document.getElementById("inptTotalPagado").value
					 var interespagado="0"
					 var totalInteres="0"
					 var saldointeres="0"
					 guardarendriverimpresion(cod_venta, tipo,"pendiente", caja, cod_localFKUSer, DiasAtrasado, subtotal,descuento,totalpagado,interespagado,totalInteres,saldointeres,DeudaActualRecibo,CuotasRestante,Monto,"0",userid) 
     					verCerrarConfigCredito("")
					 }else{
					 //imprimirDivticketFactura()
					 document.getElementById("divOpcionesImpresion").style.display=""
					 document.getElementById("btnImprimirticket").style.display=""
					 document.getElementById("btnImprimirFactura").style.display="none"
					 document.getElementById("btnImprimirPagare").style.display=""
					 var tipo=document.getElementById("inptSeleccTipoComprobanteVenta").value
					 var caja=document.getElementById("pCaja").innerHTML
					 var subtotal=document.getElementById("inptSubTotalVenta").value
					 var descuento=document.getElementById("inptTotalDescuento").value
					 var totalpagado=document.getElementById("inptTotalPagado").value
					 var interespagado="0"
					 var totalInteres="0"
					 var saldointeres="0"
					 guardarendriverimpresion(cod_venta, tipo,"pendiente", caja, cod_localFKUSer, DiasAtrasado, subtotal,descuento,totalpagado,interespagado,totalInteres,saldointeres,DeudaActualRecibo,CuotasRestante,Monto,"0",userid) 
					 verCerrarConfigCredito("")
					 }
					 buscardetallesventa()
					 if(document.getElementById("inptConfirmarPagoEntrega").value=="SI"){
						 vercerrarpagos("1")
					 }					 
					 return false;

				}
				try {
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function vercerrarOpcionesImpresion(d) {
	if (d == "1") {
		if(idabmVenta==""){
			return;
		}
		$("div[id=divOpcionesImpresion]").fadeIn(250)
	} else {
		$("div[id=divOpcionesImpresion]").fadeOut(250)		
	}
}
function vercerrarConfirmarNroFactura(d) {
	if (d == "1") {
		$("div[id=divConfirmarNroDeFactura]").fadeIn(250)
	} else {
		$("div[id=divConfirmarNroDeFactura]").fadeOut(250)
	}
}
function ActualizarNroFacturaVenta() {
    var puntoexpedicion=document.getElementById("inptSeleccPuntoExpedicionConfirmarNro").value
    var nrofactura=document.getElementById("inptConfirmarNroFactura").value	
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "actualizarnrofactura")
	datos.append("cod_venta", idFkVenta)
	datos.append("puntoexpedicion", puntoexpedicion)
	datos.append("nrofactura", nrofactura)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
		
		success: function (responseText) {
			verCerrarEfectoCargando("")
			Respuesta = responseText;
			console.log(Respuesta)
			
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
try {

					 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {

					
					
					 document.getElementById("divConfirmarNroDeFactura").style.display="none"
					 
ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE", "#")
		return false;

				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function verificarcamposventa() {
	var inptFechaVenta = document.getElementById('inptFechaVenta').value
	var inptClienteVenta = document.getElementById('inptClienteVenta').value
	var inptSeleccTipoVenta = document.getElementById('inptSeleccTipoVenta').value
	var inptComisionVentaCobrador = document.getElementById('inptComisionVentaCobrador').value
	var inptCobradorVenta = document.getElementById('inptCobradorVenta').value
	var inpCodVenta = document.getElementById('inpCodVenta').value
	var inptlocalVenta = document.getElementById('inptlocalVenta').value
var inptSeleccTipoComprobanteVenta = document.getElementById('inptSeleccTipoComprobanteVenta').value
	var inptSeleccPuntoExpedicionVenta = $("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
	var nrocaja = document.getElementById('pCaja').innerHTML
	if (inpCodVenta == "") {
		document.getElementById('inpCodVenta').value = "";
		document.getElementById('inptNroVenta').value = "";
		inpCodVenta = "";
	}
	if (nrocaja == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA CAJA", "#")
		return false;
	}
	if (inptFechaVenta == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA FECHA", "#")
		return false;
	}
	if (inptComisionVentaCobrador == "") {
		ver_vetana_informativa("FALTO INGRESAR LA COMISIÓN DEL COBRADOR", "#")
		return false;
	}
	if (idFkCliente == "") {
		idFkCliente = "7";
	}
	if (inptCobradorVenta == "") {
		idFkCobrador = "9";
		cobradorcredito = "9";
	}
	var accion = "";
	if (idabmVenta != "") {
		accion = "editar";
		if(controlacceso("INSERTARVENTA","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("INSERTARVENTA","accion")==false){return;}
	}
	abmventa(nrocaja,inptSeleccPuntoExpedicionVenta,inptSeleccTipoComprobanteVenta,idGaranteFk,inptFechaVenta, inptSeleccTipoVenta, inpCodVenta, idFkCliente, idFkCobrador, idabmVenta, "Corrido", idFkVendedor1, idFkVendedor2, inptComisionVentaCobrador, inptlocalVenta, accion);
}
function abmventa(caja,puntoexpedicion,tipo_comprobante,idGaranteFk,fecha_venta, TipoVenta, num_factura, cod_clienteFK, cod_cobradorFK, cod_venta, TipoPago, idFkVendedor1, idFkVendedor2, comision, cod_local, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_venta", cod_venta)
	datos.append("fecha_venta", fecha_venta)
	datos.append("cod_usuarioFK", userid)
	datos.append("cod_clienteFK", cod_clienteFK)
	datos.append("num_factura", num_factura)
	datos.append("cod_cobradorFK", cod_cobradorFK)
	datos.append("TipoVenta", TipoVenta)
	datos.append("TipoPago", TipoPago)
	datos.append("vendedor1", idFkVendedor1)
	datos.append("vendedor2", idFkVendedor2)
	datos.append("comision", comision)
	datos.append("cod_local", cod_local)
	datos.append("idGaranteFk", idGaranteFk)
	datos.append("tipo_comprobante", tipo_comprobante)
	datos.append("puntoexpedicion", puntoexpedicion)
	datos.append("caja", caja)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
					idabmVenta = datos["2"]
					idFkVenta = datos["2"]
					document.getElementById('inpCodVenta').disabled = true
					document.getElementById('inpCodVenta').className = "inputTextDisable"
					buscardetallesventa()
					document.getElementById('btnAbmVenta').style.display = ""
					document.getElementById('btnAbmVenta').value = "Editar datos"
					if(document.getElementById("inptSeleccTipoVenta").value=="CREDITO"){
						//crearcreditodesdeventa()
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
function vercerrarvistaventas(d) {
	if (d == "1") {
		document.getElementById("divVistaVentas").style.display=""
 document.getElementById("tdEfectoVistaVenta").className="magictime slideLeftReturn"
	} else {
 document.getElementById('inptBuscarVistaVentas').value=""
document.getElementById("table_vista_ventas").innerHTML = ""
document.getElementById("tdEfectoVistaVenta").className="magictime slideRight"
		$("div[id=divVistaVentas]").fadeOut(500)
	}
}
function buscarvistaventa() {
	var buscar = document.getElementById('inptBuscarVistaVentas').value
	var filtro = document.getElementById('inptOpcionesdeBusquedaVenta').value	
	document.getElementById("table_vista_ventas").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscar,
		"filtro": filtro,
		"funt": "historialvistaventa"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_vista_ventas").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_ventas").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				  Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				   
					var datos_buscados = datos[2];
					document.getElementById("table_vista_ventas").innerHTML = datos_buscados
				
				
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function obtenerdatosvistaventa(datostr) {
	limpiarcamposventa("1")
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	idabmVenta = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptFechaVenta').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptClienteVenta').value = $(datostr).children('td[id="td_datos_2"]').html();
	controltipoventa = $(datostr).children('td[id="td_datos_12"]').html();
	document.getElementById('inptSeleccTipoVenta').value = $(datostr).children('td[id="td_datos_12"]').html();
	document.getElementById('inptVendedorVenta1').value = $(datostr).children('td[id="td_datos_15"]').html();
	document.getElementById('inptVendedorVenta2').value = $(datostr).children('td[id="td_datos_16"]').html();
	document.getElementById('inptCobradorVenta').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptCobradorCargarPago').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inpCodVenta').value = $(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('inptNroVenta').value = $(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('inptSeleccPuntoExpedicionVenta').value = $(datostr).children('td[id="td_datos_33"]').html();
	document.getElementById('inptAccesoCreditoVentaCliente').value =  $(datostr).children('td[id="td_datos_34"]').html();
	var puntoExpedicion=$("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
	if(puntoExpedicion==""){						
	document.getElementById("pNroFactuaCaja").innerHTML = "*"+$(datostr).children('td[id="td_datos_13"]').html()+"*";
	}else{
	document.getElementById("pNroFactuaCaja").innerHTML = "*"+puntoExpedicion+"-"+$(datostr).children('td[id="td_datos_13"]').html()+"*";
	}

	document.getElementById('inptComisionVentaCobrador').value = $(datostr).children('td[id="td_datos_22"]').html();
	document.getElementById('inptlocalVenta').value = $(datostr).children('td[id="td_datos_23"]').html();
	document.getElementById('inptGaranteVenta').value = $(datostr).children('td[id="td_datos_31"]').html();
	document.getElementById('inptSeleccTipoComprobanteVenta').value = $(datostr).children('td[id="td_datos_32"]').html();
	
	if(document.getElementById('inptSeleccTipoComprobanteVenta').value=="FACTURA"){
		document.getElementById("btnImprimirticket").style.display=""
					 document.getElementById("btnImprimirFactura").style.display=""
					 document.getElementById("btnImprimirPagare").style.display=""
	}else{
		document.getElementById("btnImprimirticket").style.display=""
					 document.getElementById("btnImprimirFactura").style.display="none"
					 document.getElementById("btnImprimirPagare").style.display=""
	}
	 
	idGaranteFk = $(datostr).children('td[id="td_datos_30"]').html();
	idFkVendedor1 = $(datostr).children('td[id="td_datos_3"]').html();
	idFkVendedor2 = $(datostr).children('td[id="td_datos_14"]').html();
	idFkCliente = $(datostr).children('td[id="td_datos_10"]').html();
	idFkCobrador = $(datostr).children('td[id="td_datos_11"]').html();
	cobradorcargarpagos = $(datostr).children('td[id="td_datos_11"]').html();
	
	idFkVenta = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inpCodVenta').disabled = true
	document.getElementById('inpCodVenta').className = "inputTextDisable"
	document.getElementById('btnAbmVenta').style.display = ""
	document.getElementById('btnAbmVenta').value = "Editar datos"
	buscardetallesventa()
   document.getElementById("divVistaVentas").style.display='none'
   document.getElementById("btnMasInfoClienteVenta").style.display='none'
document.getElementById("btnNuevoClienteVenta").style.display=''
document.getElementById("tdImprimirVenta").style.display=''
SeleccTipoComprobanteVenta()
}

var cantidaDetalleSelec = "";
var codproductodetalleSelect = "";
function obtenerdatosabmdetalleventa(datostr) {


	// $("tr[id=tbSelecRegistro]").each(function (i, td) {
		// td.className = ''

	// });

	// datostr.className = 'tableRegistroSelec'
	// document.getElementById('inptCodDetalle').value = $(datostr).children('td[id="td_id_2"]').html();
	// document.getElementById('inptNombreProductoDetalleOpcion').value = $(datostr).children('td[id="td_datos_1"]').html();
	// idDetalleVenta = $(datostr).children('td[id="td_id_2"]').html();
	// cantidaDetalleSelec = $(datostr).children('td[id="td_datos_4"]').html();
	// codproductodetalleSelect = $(datostr).children('td[id="td_id_1"]').html();
	// vercerrarOpcionesDetalles("1")



}
function vercerrarOpcionesDetalles(d) {


	if (d == "1") {
		$("div[id=divOpcionesDetalles]").fadeIn(250)

	} else {
		$("div[id=divOpcionesDetalles]").fadeOut(250)
	}


}
function eliminardetalleventa() {
	var inptTotalPagado = document.getElementById('inptTotalPagado').value
	if (inptTotalPagado > 0) {
		ver_vetana_informativa("NO SE PUEDE EDITAR EL DETALLE A LA VENTA POR QUE ESTE YA CUENTA CON UN PAGO", "#")
		return false;
	}
	if (idDetalleVenta == "") {
		ver_vetana_informativa("FALTO SELCCIONAR UN REGITRO")
		return false;
	}
	var operacion=document.getElementById("inptOpcionesDetallesVentaEliminar").value;
	var motivo=document.getElementById("inptMotivoDetallesVentaEliminar").value;
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "eliminar")
	datos.append("cod_detalle", idDetalleVenta)
	datos.append("cod_ventaFK", idabmVenta)
	datos.append("cantida", cantidaDetalleSelec)
	datos.append("codProducto", codproductodetalleSelect)
	datos.append("operacion_stock", operacion)
	datos.append("motivo", motivo)
	datos.append("Local_FK", cod_localFKUSer)
	var OpAjax = $.ajax({

		data: datos,
			url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
  Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {

					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")

					document.getElementById("divOpcionesDetalles").style.display = "none"
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


/*ENTREGA COBRADOR*/
function verCerrarEntregaCobrador(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divEntregaCobrador").style.display==""){
		//document.getElementById("divEntregaCobrador").style.display="none"
		document.getElementById("divMinimizadoEntregaCobrador").style.display='none'
document.getElementById("tdEfectoEntregaCobrador").className="magictime vanishOut"
	$("div[id=divEntregaCobrador]").fadeOut(500);	
		limpiarcamposhistorialventa()
	}else{
       if(controlacceso("ENTREGACOBRADOR","accion")==false){	   
	   //SIN PERMISO
	   return;
		}
		document.getElementById("divEntregaCobrador").style.display=""
			document.getElementById("tdEfectoEntregaCobrador").className="magictime slideDownReturn"
	}
}
function minimizarEntregaCobrador(){
	//document.getElementById("divEntregaCobrador").style.display='none'
	document.getElementById("divMinimizadoEntregaCobrador").style.display=''
document.getElementById("tdEfectoEntregaCobrador").className="magictime slideDown"
	$("div[id=divEntregaCobrador]").fadeOut(500);
}
/*PAGOS AL CONTADO*/
function calcularVuelto(){
	var totalventa = QuitarSeparadorMilValor(document.getElementById('inptTotalVentaTerminar').value);
	var totaldescuento = QuitarSeparadorMilValor(document.getElementById('inptDescuentoVentaTerminar').value);
	var inptMontoVentaTerminarEfectivo = QuitarSeparadorMilValor(document.getElementById('inptMontoVentaTerminarEfectivo').value);
	console.log(inptMontoVentaTerminarEfectivo)
	if (isNaN(totalventa)) {
       ver_vetana_informativa("ERROR, TOTAL INVALIDO")
		return 
	}
	if (isNaN(totaldescuento)) {
		document.getElementById('inptDescuentoVentaTerminar').value = 0;
		totaldescuento = 0;
	}
	if (isNaN(inptMontoVentaTerminarEfectivo)) {
		document.getElementById('inptMontoVentaTerminarEfectivo').value = 0;
		inptMontoVentaTerminarEfectivo = 0;
	}
	var m = parseFloat(inptMontoVentaTerminarEfectivo);
	var d = parseFloat(totaldescuento);
	var t = parseFloat(totalventa);
	if(m<=0){
		ver_vetana_informativa("ERROR, MONTO INVALIDO")
		return 
	}
	var v=(m+d)-t
	if(v<0){
		v=0;
	}
	document.getElementById('inptMontoVentaTerminarEfectivo').value=separadordemilesnumero(m);
	document.getElementById('inptDescuentoVentaTerminar').value=separadordemilesnumero(d);
	document.getElementById('inptVueltoVentaTerminar').value=separadordemilesnumero(v);
}
function abmconfirmarPagoContado() {
	if (idabmVenta == "") {
		ver_vetana_informativa("FALTO INICIAR LA VENTA")
		return
	}
	if (document.getElementById("inptSeleccTipoVenta").value != "CONTADO") {
		ver_vetana_informativa("SOLO LAS VENTAS A CONTADO PUEDEN REALIZAR ESTA ACCION")
		return false;
	}
	if (document.getElementById("inptTotalPagado").value != "0") {
		if (document.getElementById("inptTotalPagado").value != "") {
			ver_vetana_informativa("ESTA VENTA YA CUENTA CON UN PAGO")
			return false;
		}
	} 
    var monto=QuitarSeparadorMilValor(document.getElementById("inptMontoVentaTerminarEfectivo").value)
	var montotarjerta=QuitarSeparadorMilValor(document.getElementById("inptMontoVentaTerminarTarjeta").value)
	var descuento=QuitarSeparadorMilValor(document.getElementById("inptDescuentoVentaTerminar").value)
	var total=QuitarSeparadorMilValor(document.getElementById("inptTotalVentaTerminar").value)
    
	if(Number(montotarjerta)>0){
		if(Number(monto)>Number(total)){
			ver_vetana_informativa("EL MONTO EN TARJETA ES INCORRECTO")
			return false;				
		}
		if((Number(monto)+Number(montotarjerta))>Number(total)-Number(descuento)){
			ver_vetana_informativa("EL MONTO EN TARJETA O EN EFECTIVO ES INCORRECTO")
			return false;				
		}
	}
	
	
	var monto=document.getElementById("inptMontoVentaTerminarEfectivo").value
	var montotarjerta=document.getElementById("inptMontoVentaTerminarTarjeta").value
	var descuento=document.getElementById("inptDescuentoVentaTerminar").value
		var vuelto=document.getElementById("inptVueltoVentaTerminar").value
   var m=QuitarSeparadorMilValor(monto);
	 var d=QuitarSeparadorMilValor(descuento);
	 var t=QuitarSeparadorMilValor(total);
	 var v=QuitarSeparadorMilValor(vuelto);
	 if(Number(v)>0){
		 t=Number(m)-Number(v);
		monto=separadordemilesnumero(t)
	 }
   
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "pagocontado")
	datos.append("cod_venta", idabmVenta)
	datos.append("descuento", descuento)
	datos.append("monto", monto)
	datos.append("montotarjerta", montotarjerta)
    datos.append("codcaja", cajapredeterminada)
    datos.append("codApertura", idabmAperturacierrecaja)



	var OpAjax = $.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
				   
					
					document.getElementById('inptTotalPagado').value = datos["2"];
	paginaticket=datos["3"];
		PlazoRecibo="1"
		document.getElementById("divFinalizarVentaAContado").style.display="none"
		document.getElementById("btnFinalizarVenta").value="Añadir Pago (No Disponible)"
		   
		    var tipo=document.getElementById("inptSeleccTipoComprobanteVenta").value
						 var caja=document.getElementById("pCaja").innerHTML
						 var subtotal=document.getElementById("inptTotalVenta2").innerHTML
						 var descuento=document.getElementById("inptTotalDescuento").value
						 var totalpagado=document.getElementById("inptMontoVentaTerminarEfectivo").value
						 var interespagado="0"
						 var totalInteres="0"
						 var saldointeres="0"
						 var DeudaActualRecibo="0"
						 DiasAtrasado="0"
						 var PuntoExpedicion=$("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
var NroVentas=document.getElementById("inptNroVenta").value;
if(PuntoExpedicion!=""){
NroVentas=PuntoExpedicion+"-"+NroVentas
}

guardarendriverimpresion(idabmVenta, tipo,"pendiente", caja, cod_localFKUSer, DiasAtrasado, subtotal,descuento,totalpagado,interespagado,totalInteres,saldointeres,DeudaActualRecibo,"CONTADO",monto,NroVentas,userid) 
						
		   
		   
		     if(document.getElementById("inptSeleccTipoComprobanteVenta").value=="FACTURA"){  
					document.getElementById("inptSeleccPuntoExpedicionConfirmarNro").value=$("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
					document.getElementById("inptConfirmarNroFactura").value=document.getElementById("inptNroVenta").value
					document.getElementById("divOpcionesImpresion").style.display="none"
					document.getElementById("divConfirmarNroDeFactura").style.display=""
					ImprimirFacrtura1()
					limpiarcamposventa()
					 }else{
					 limpiarcamposventa()
					 }
					 document.getElementById("divVueltoVentaAContado").style.display="";
                     document.getElementById("tdEfectoVueltoVentaContado").className="magictime vanishIn"
				}

			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function verCerrarUltimoVuelto(d){
	if(d=="1"){
			 document.getElementById("divVueltoVentaAContado").style.display="";
 document.getElementById("tdEfectoVueltoVentaContado").className="magictime vanishIn"
	}else{
		 document.getElementById("tdEfectoVueltoVentaContado").className="magictime vanishOut"
$("div[id=divVueltoVentaAContado]").fadeOut(500);	
	}
}
/*PAGOS A CREDITO*/
function vercerrarpagos(d,c) {
	if (d == "1") {		
    if(controlacceso("VERPAGOSCREDITO","accion")==false){return;}
		if (idabmVenta == "") {
			ver_vetana_informativa("FALTO INICIAR UNA VENTA", "#")
			return false;
		}		
		if (document.getElementById("inptSeleccTipoVenta").value == "CONTADO") {
		ver_vetana_informativa("SOLO LAS VENTAS A CREDITO PUEDEN REALIZAR ESTA ACCION")
		return false;
	}
	
		
		document.getElementById("divAbmOpcionesPagos").style.display=""
		 document.getElementById("tdEfectoOpcionesPagos").className="magictime slideLeftReturn"
		document.getElementById("tdOpcionesVolverAtrasPagos").style.display=""
		document.getElementById("inpCodVentaPagos").value = document.getElementById("inpCodVenta").value
		document.getElementById("inptTotalVentaPagos").value = document.getElementById("inptTotalVenta").value
		document.getElementById("inptTotalVentaPagosb").value = ""
		buscarDatosOpcionesPagos()
		buscarcreditos()
	} else {		
if(c=="0"){			
			limpiarcamposventa()
		}		
		document.getElementById("tdEfectoOpcionesPagos").className="magictime slideRight"
		$("div[id=divAbmOpcionesPagos]").fadeOut(500)
	}
	}
function buscarDatosOpcionesPagos() {

	verCerrarEfectoCargando("1")
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idFkVenta,
		"funt": "buscardatoscuenta"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			verCerrarEfectoCargando("2")
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			verCerrarEfectoCargando("2")
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {

					cuotaNro = datos[5];
					montoapagarcuota = datos[3];
					iniciopagocuota = datos[2];
					MetodoPago = datos[4];
					document.getElementById("inptNroCuotasPagos").value = cuotaNro
					document.getElementById("inptMontoPagoOpciones").value = montoapagarcuota
					document.getElementById("inptFechaInicioPapo").value = iniciopagocuota
					document.getElementById("inputSelectMetodo").value = MetodoPago
					document.getElementById("inptDiasGraciaPapo").value = datos[6]
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function buscarcreditos() {
  if(controlacceso("VERPAGOSCREDITO","accion")==false){return;}
	document.getElementById("table_abm_opciones_pago").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idFkVenta,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			document.getElementById("table_abm_opciones_pago").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_opciones_pago").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];				
               Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					paginaExtractoCuota = datos[12];
					document.getElementById("table_abm_opciones_pago").innerHTML = datos_buscados
					document.getElementById("inptTotalPagado").value = datos[3]
					document.getElementById("inptTotalPagadoOpcionesPago").value = datos[3]
					document.getElementById("inptDeudaActual").value = datos[4]
					document.getElementById('inptInteresPagoOpciones').value = datos[5]
					document.getElementById('inptTotalInteres').value = datos[7]
					document.getElementById('inptDiasAtrazadoCargarPago').value = datos[8]
					document.getElementById('inptEntregaPapo').value = datos[9]
					document.getElementById('inptTotalDescuentoOpcionesPago').value = datos[11]					
					document.getElementById('inptMontoCuotaPago').value = datos[15]
					document.getElementById('inptCuotasAtrazadoCargarPago').value = datos[14]
					document.getElementById('inptTotalinteresPago').value = datos[18]
					document.getElementById('inptSubtotalPago').value = datos[13]
					document.getElementById('inptTotalDeudaPago').value = datos[17]
					document.getElementById('inptDescuentoCargaPago').value = 0
					document.getElementById('inptMontoCargaPago').value = 0
					
					ImportePagare = datos[3]
					InteresRecibo=datos[19]
					DeudaActualRecibo=datos[17]
					TotalDescuentoRecibo=datos[11]	
					
					if(datos[3]>0){
					document.getElementById("btnAbmGenerarCuotas").style.display='none'
					}else{
					document.getElementById("btnAbmGenerarCuotas").style.display=''
					}
					if(datos_buscados==""){
						document.getElementById("btnAbmGenerarCuotas").value='Generar Cuotas'
					}else{
						document.getElementById("btnAbmGenerarCuotas").value='Volver a generar Cuotas'
					}
					
					calcular_total_con_entrega()

				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
			var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function verCerrarEditarCredito(d){
	if(d=="2"){
				var totalMonto=0;
    $("input[name=inptMontoCreditoEditar]").each(function (i, elemento) {
		var m=QuitarSeparadorMilValor(elemento.value)
		totalMonto=Number(totalMonto)+Number(m)
	});	
	var totalDescuento=0;
	// $("input[name=inptDescuentoCreditoEditar]").each(function (i, elemento) {
		// var d=QuitarSeparadorMilValor(elemento.value)
		// totalDescuento=Number(totalDescuento)+Number(d)
		
	// });	
	var totalventa=document.getElementById("inptTotalVenta").value;
	totalventa=QuitarSeparadorMilValor(totalventa);
	var total =totalMonto-totalDescuento;	
    if(totalventa!=total){
	if(confirm("El total #"+total+"# no coincide con el total ventas #"+totalventa+"#, Continuar de todas formas")){
		
	}else{		
	return 	
	}
	} 
		 document.getElementById("tdEfectoEditarCreditos").className="magictime vanishOut"
		$("div[id=divEditarCredistos]").fadeOut(500);
		
	}else{		
	 if(controlacceso("EDITARCREDITO","accion")==false){return;}	
		document.getElementById("divEditarCredistos").style.display=""
		 document.getElementById("tdEfectoEditarCreditos").className="magictime vanishIn"
		buscarcreditosaeditar()
		
	}
}
function buscarcreditosaeditar() {
 if(controlacceso("EDITARCREDITO","accion")==false){return;}	
	document.getElementById("table_abm_opciones_creditareditados").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idFkVenta,
		"funt": "buscarcreditoseditar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			document.getElementById("table_abm_opciones_creditareditados").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_opciones_creditareditados").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				  Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {

					var datos_buscados = datos[2];
                    document.getElementById("table_abm_opciones_creditareditados").innerHTML = datos_buscados
					

				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function EditarEsteCredito(datos) {	
	  if(controlacceso("EDITARCREDITO","accion")==false){return;}	
	var codCredito=datos.id
	var date=document.getElementById("inptDate_"+codCredito).value
	var monto=document.getElementById("inptMonto_"+codCredito).value
	var descuento=document.getElementById("inptDescuento_"+codCredito).value
	var dias=document.getElementById("inptDias_"+codCredito).value
	var interes=document.getElementById("inptInteres_"+codCredito).value
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "editarestecredito")
	datos.append("codCredito", codCredito)
	datos.append("date", date)
	datos.append("monto", monto)
	datos.append("descuento", descuento)
	datos.append("dias", dias)
	datos.append("interes", interes)
		var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")				
					buscarcreditos()
				}
				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function vercerraropcionespago(d) {
	if (d == "1") {
		
		document.getElementById("divOpcionesPagos").style.display=""
		 document.getElementById("tdEfectoEliminarPago").className="magictime vanishIn"
	} else {
		document.getElementById("tdEfectoEliminarPago").className="magictime vanishOut"
		$("div[id=divOpcionesPagos]").fadeOut(250)
	}
}
var idAbmPago = "";
function obtenerdatosabmpagosopciones(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptCuotaNroOpcionespagos').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptMontoOpcionpagos').value = $(datostr).children('td[id="td_datos_10"]').html();
	document.getElementById('inptMotivoOpcionpagos').value = "";	
	codCredito = $(datostr).children('td[id="td_datos_1"]').html();
	vercerraropcionespago("1")
}
var nrofacturaaeliminar=""
function abmeliminarestepagocredito() {	
	 if(controlacceso("ELIMINARPAGOCREDITO","accion")==false){return;}		
if(confirm("Estas Seguro que quieres eliminar este pago")){
	var motivo=document.getElementById("inptMotivoOpcionpagos").value
	var monto=document.getElementById("inptMontoOpcionpagos").value
	var cuota=document.getElementById("inptCuotaNroOpcionespagos").value
	if(motivo==""){
		ver_vetana_informativa("FALTO INGRESAR EL MOTIVO")
		return
	}
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "eliminar")
	datos.append("motivo", motivo)
	datos.append("monto", monto)
	datos.append("cuota", cuota)
	datos.append("cod_creditoFK", codCredito)
	datos.append("idFkVenta", idFkVenta)
	datos.append("nrofactura", nrofacturaaeliminar)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					vercerraropcionespago("2")
					buscarcreditos()
				}				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
}
function vercerrarhistorialdepago(d) {
	if (d == "1") {
		document.getElementById("divHistorialPagos").style.display=""
		 document.getElementById("tdEfectoHistorialPagos").className="magictime vanishIn"
		buscarhistorialdepagos();
	} else {
		 document.getElementById("tdEfectoHistorialPagos").className="magictime vanishOut"
		$("div[id=divHistorialPagos]").fadeOut(500)
	}
}
function buscarhistorialdepagos() {
	document.getElementById("table_historial_pagos").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": codCredito,
		"funt": "buscarHistorial"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
			document.getElementById("table_historial_pagos").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_pagos").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_historial_pagos").innerHTML = datos_buscados
					document.getElementById("intTotalPagado").value = datos[3]
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
	}
function abmeliminarestepagohistorial() {
	 if(controlacceso("ELIMINARPAGOCREDITO","accion")==false){		
	//SIN PERMISO
	  return;
		}
if(confirm("Estas Seguro que quieres eliminar este pago")){
	if (idHistorialPago == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "eliminarhistorialpago")
	datos.append("codPago", idHistorialPago)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
					ver_vetana_informativa("PAGO ELIMINADO CORRECTAMENTE...")
					if(codCreditoRefin!=""){
					buscarcreditosRefin()
				   }
					idHistorialPago = "";
					buscarhistorialdepagos()
					buscarcreditos()
					vercerrarconfirmarpagos("")
					
					buscararqueo2();
				}	

			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
}
var totalPagadoCuota=0;
function obtenerdatosabmpagos(datostr) {
	
	 if(idabmAperturacierrecaja==""){
		   ver_vetana_informativa("FALTO INICIAR UNA CAJA", "#")
		   verCerrarVentanaAbmAperturaCierreCaja1()
		   return
	   }
	
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
		cuotasNro=$(datostr).children('td[id="td_datos_2"]').html();
	deudaActual = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptInteresAPagar').value = $(datostr).children('td[id="td_datos_20"]').html();
	document.getElementById('inptCuotaAPagar').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptMontoAPagar').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptMontoClienteAPagar').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptMontoTarjetaClienteAPagar').value = "0";
	document.getElementById('inptDescuentoAPagar').value = "0";
	totalPagadoCuota= $(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('inptDiasAtrazadoAPagar').value = $(datostr).children('td[id="td_datos_9"]').html();
	document.getElementById('inptFechaCuotaModificar').value = $(datostr).children('td[id="td_datos_3"]').html();//FRM EDITAR CUOTA
	document.getElementById('inptMontoMaximoAPagar').value = $(datostr).children('td[id="td_datos_6"]').html();
	
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptFechaPagoConfirmar').value = f.getFullYear() + "-" + mes + "-" + dia;
	document.getElementById('inptCobradorConfirmar').value = document.getElementById('inptCobradorVenta').value
	codCredito = $(datostr).children('td[id="td_datos_1"]').html();
	cobradorcredito = idFkCobrador;
	vercerrarconfirmarpagos("1")
	document.getElementById('inptMontoClienteAPagar').focus;
	}
function vercerrarconfirmarpagos(d) {
	if (d == "1") {
			 if(controlacceso("INSERTARPAGOSCREDITO","accion")==false){return;}		
		cambiarCobradorCodEnPagos()
		document.getElementById("divConfirmarPago").style.display=""
		 document.getElementById("tdEfectoConfirmaPago").className="magictime vanishIn"

	} else {
		 document.getElementById("tdEfectoConfirmaPago").className="magictime vanishOut"
		$("div[id=divConfirmarPago]").fadeOut(500)
	}
}
var cobradorcargarpagos = "";
function cambiarCobradorCodEnPagos(){
	if(ControlCobradorUser!=0){		
	document.getElementById('inptCobradorCargarPago').value = document.getElementById("lblUser").innerHTML;
	document.getElementById('inptCobradorConfirmar').value = document.getElementById("lblUser").innerHTML;
	cobradorcargarpagos = CodCobradorUser;		
	cobradorcredito = CodCobradorUser;		
	}
}
var codCredito = "";
var cobradorcredito = "";
var controlInsercionPagos=false
function verificarConfirmaciondepago() {
   if(controlInsercionPagos==true){
	   ver_vetana_informativa("TIENES UN PAGO EN PROCESO AGUARDE UN MOMENTO", "#")
	   return 
   }   
   
    var inptMontoMaximoAPagar = document.getElementById('inptMontoMaximoAPagar').value
   
	var inptFechaPagoConfirmar = document.getElementById('inptFechaPagoConfirmar').value
	var inptInteresAPagar = document.getElementById('inptInteresAPagar').value
	var inptMontoAPagar = document.getElementById('inptMontoAPagar').value
	var MontoCobrado = document.getElementById('inptMontoClienteAPagar').value
	var inptNroReciboAPagar = document.getElementById('inptNroReciboAPagar').value
	var inptDescuentoAPagar = document.getElementById('inptDescuentoAPagar').value
	var MontoTarjeta = document.getElementById('inptMontoTarjetaClienteAPagar').value
	
	var montoc1 = QuitarSeparadorMilValor(MontoCobrado);
	var montoc2 = QuitarSeparadorMilValor(MontoTarjeta);
	var montoc3 = QuitarSeparadorMilValor(inptMontoMaximoAPagar);
	
	if(Number(montoc3)<(Number(montoc1)+Number(montoc2))){
		ver_vetana_informativa("LO SIENTO EL MONTO A PAGAR ES SUPERIOR A LA DEUDA.    !¡FAVOR UTILICE EL PAGO PARCIAL¡!", "#")
		
		return;
	}
	
	
   controlInsercionPagos=true
	if (inptNroReciboAPagar == "") {		
	}
	if (MontoCobrado == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MONTO A COBRAR", "#")
		return false;
	}
	if (inptMontoAPagar == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MONTO", "#")
		return false;
	}
	if (inptFechaPagoConfirmar == "") {
		ver_vetana_informativa("FALTO INGRESAR LA FECHA DE PAGO", "#")
		return false;
	}
	if (cobradorcredito == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	abmconfirmarpago(MontoTarjeta,inptMontoAPagar,inptDescuentoAPagar, inptFechaPagoConfirmar, cobradorcredito, codCredito, inptInteresAPagar, MontoCobrado,inptNroReciboAPagar);
}
function abmconfirmarpago(MontoTarjeta,totalDeudaCuota, descuento,Fecha, cod_cobradorFK, cod_creditoFK, totalInteres, MontoCobrado,nrofactura) {
 if(controlacceso("INSERTARPAGOSCREDITO","accion")==false){return;}	
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "nuevo")
	datos.append("Fecha", Fecha)
	datos.append("totalDeudaCuota", totalDeudaCuota)
	datos.append("cod_creditoFK", cod_creditoFK)
	datos.append("cod_cobradorFK", cod_cobradorFK)
	datos.append("cod_venta", idFkVenta)
	datos.append("totalInteres", totalInteres)
	datos.append("MontoCobrado", MontoCobrado)
	datos.append("nrofactura", nrofactura)
	datos.append("descuento", descuento)
	datos.append("MontoTarjeta", MontoTarjeta)
	  datos.append("codcaja", cajapredeterminada)
    datos.append("codApertura", idabmAperturacierrecaja)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmconfirmarpago")
            controlInsercionPagos=false
			return false;
		},
		success: function (responseText) {
			verCerrarEfectoCargando("")
			Respuesta = responseText;
			console.log(Respuesta)
			controlInsercionPagos=false
			
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
try {
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					vercerrarconfirmarpagos("2")
					buscarcreditos()
deudaActual=datos["4"];
	paginaticket=datos["5"];
	NombreCliente=datos["6"];
	CiCliente=datos["7"];
	var nrofactura=datos["8"];
    var tipoventa=datos["9"];
    var interespagado=datos["11"];
    var deuda=datos["12"];
    var totalpagado=datos["13"];
    var totalDescuento=datos["14"];
    var totalventa=datos["3"];
    var TotalInteresActual=datos["15"];
    var deudaActualsininteres=datos["16"];
     
	pagado=MontoCobrado;
	
       document.getElementById("table_cuentas_a_cobrar").innerHTML="";          	
	diaatrazado=document.getElementById('inptDiasAtrazadoAPagar').value;
//imprimirDivTickeFacturaPago(NombreCliente,CiCliente,nrofactura,tipoventa,totalinteres,deuda,totalpagado,totalDescuento,totalventa,TotalInteresActual,deudaActualsininteres)

						 var caja=document.getElementById("pCaja").innerHTML
						 
					
						
						 var totalinteres =(Number(QuitarSeparadorMilValor(TotalInteresActual))+Number(QuitarSeparadorMilValor(interespagado)))
		totalinteres=separadordemilesnumero(totalinteres)
		 guardarendriverimpresion(idFkVenta, "PAGO CUOTA","pendiente", caja, cod_localFKUSer, diaatrazado, totalventa,totalDescuento,totalpagado,interespagado,totalinteres,TotalInteresActual,deuda,cuotasNro,pagado,nrofactura,userid) 

		   
		}
				

			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
var idCreditoEditarCuota = "";
var idVentaEditarCuota = "";
function vercerrareditarPagos(d) {
	if (d == "1") {
		if(controlacceso("EDITARCREDITO","accion")==false){return;}
		$("div[id=divOpcionesCuota]").fadeIn(250)
		document.getElementById("inptMontoCuotaModificar").value = document.getElementById("inptMontoAPagar").value
		document.getElementById("inptDescuentoCuotaModificar").value = ""
		document.getElementById("inptOpcionesCuotaModificar").value = "1"
		idCreditoEditarCuota = codCredito
		idVentaEditarCuota = idFkVenta
	} else {
		$("div[id=divOpcionesCuota]").fadeOut(250)
	}

}
function verificareditarpago() {
	var inptMontoCargaPago = document.getElementById('inptMontoCargaPago').value
	var inptFechaCuotaModificar = document.getElementById('inptFechaCuotaModificar').value
	var inptDescuentoCuotaModificar = document.getElementById('inptDescuentoCuotaModificar').value
	var inptOpcionesCuotaModificar = document.getElementById('inptOpcionesCuotaModificar').value
	if (idCreditoEditarCuota == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	if (inptFechaCuotaModificar == "") {
		ver_vetana_informativa("FALTO INGRESAR LA FECHA DE PAGO", "#")
		return false;
	}
	abmeditarcuota(idCreditoEditarCuota,inptDescuentoCuotaModificar, idVentaEditarCuota, inptFechaCuotaModificar, inptOpcionesCuotaModificar);
}
function abmeditarcuota(idcredito,descuento, cod_venta, fecha, tipo) {
if(controlacceso("EDITARCREDITO","accion")==false){return;}
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "editarcuenta")
	datos.append("fecha", fecha)
	datos.append("cod_venta", cod_venta)
	datos.append("idcredito", idcredito)
	datos.append("tipo", tipo)
	datos.append("descuento", descuento)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					vercerrareditarPagos("2")
					buscarcreditos()
				}				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function verCerrarCargarPago(d) {
	if (d == "1") {		
		  if(controlacceso("INSERTARPAGOSCREDITO","accion")==false){return;}
		 if(idabmAperturacierrecaja==""){
		   ver_vetana_informativa("FALTO INICIAR UNA CAJA", "#")
		   verCerrarVentanaAbmAperturaCierreCaja1()
		   return
	   }
		document.getElementById("divCargaPagos").style.display=""
		 document.getElementById("tdEfectoCargarPagosVenta").className="magictime vanishIn"
		var f = new Date();
		var dia = f.getDate()
		if (dia < 10) {
			dia = "0" + dia;
		}
		var mes = f.getMonth() + 1
		if (mes < 10) {
			mes = "0" + mes;
		}
		document.getElementById('inptFechaPagoCargarPago').value = f.getFullYear() + "-" + mes + "-" + dia;
	cambiarCobradorCodEnPagos()	
	
	var DeudaActualAPagar = document.getElementById('inptDeudaActual').value
	
	document.getElementById('inptDeudaActualCargaPago').value= DeudaActualAPagar;
	
	document.getElementById('inptMontoCargaPago').value="0"
	document.getElementById('inptDescuentoCargaPago').value="0"
	document.getElementById('inptMontoTarjetaCargaPago').value="0"
	document.getElementById('inptMontoCargaPago').focus;
	} else {
		document.getElementById("tdEfectoCargarPagosVenta").className="magictime vanishOut"
		$("div[id=divCargaPagos]").fadeOut(250)
	}
}
var controldePagosParciales=false
function verificarcargarpago() {
    if(controldePagosParciales==true){
		ver_vetana_informativa("PAGO EN PROCESO, PUEDE REALIZAR ESTA ACCIÓN", "#")
		return
	}
	
	var inptDeudaActualCargaPago = document.getElementById('inptDeudaActualCargaPago').value
	
	
	var inptMontoCargaPago = document.getElementById('inptMontoCargaPago').value
	var inptFechaPagoCargarPago = document.getElementById('inptFechaPagoCargarPago').value
	var inputSelectFechaPago = document.getElementById('inputSelectFechaPago').value
	var inptNroReciboCargaPago = document.getElementById('inptNroReciboCargaPago').value
	var inptDescuentoCargaPago = document.getElementById('inptDescuentoCargaPago').value
	var inptMontoTarjetaCargaPago = document.getElementById('inptMontoTarjetaCargaPago').value
    controldePagosParciales=true
	
	
	var montop1 = QuitarSeparadorMilValor(inptMontoCargaPago);
	var montop2 = QuitarSeparadorMilValor(inptMontoTarjetaCargaPago);
	var montop3 = QuitarSeparadorMilValor(inptDeudaActualCargaPago);
	
	
	if( (Number(montop1)+Number(montop2))  - 1  >= Number(montop3)    ){
		ver_vetana_informativa("LO SIENTO EL MONTO A PAGAR ES SUPERIOR A LA DEUDA.", "#")
		document.getElementById('inptMontoCargaPago').value= inptDeudaActualCargaPago;
		inptMontoCargaPago=inptDeudaActualCargaPago;
		return;
	}
	
	if (inptMontoCargaPago == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MONTO", "#")
		return false;
	}
	if (inptFechaPagoCargarPago == "") {
		ver_vetana_informativa("FALTO INGRESAR LA FECHA DE PAGO", "#")
		return false;
	}
	if (cobradorcargarpagos == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN COBRADOR", "#")
		return false;
	}
	abmcargarpago(inptMontoTarjetaCargaPago,inptDescuentoCargaPago,inptMontoCargaPago, inptFechaPagoCargarPago, cobradorcargarpagos, inputSelectFechaPago,inptNroReciboCargaPago);
}
function abmcargarpago(MontoTarjeta,Descuento,Monto, Fecha, cod_cobradorFK, controlfecha,nrofactura) {
if(controlacceso("INSERTARPAGOSCREDITO","accion")==false){return;}
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "cargarpago")
	datos.append("Fecha", Fecha)
	datos.append("Monto", Monto)
	datos.append("cod_cobradorFK", cod_cobradorFK)
	datos.append("cod_venta", idFkVenta)
	datos.append("controlfecha", controlfecha)
	datos.append("nrofactura", nrofactura)
	datos.append("Descuento", Descuento)
	datos.append("MontoTarjeta", MontoTarjeta)
	 datos.append("codcaja", cajapredeterminada)
    datos.append("codApertura", idabmAperturacierrecaja)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmcargarpago")
controldePagosParciales=false
			return false;
		},
		success: function (responseText) {
			verCerrarEfectoCargando("")
			Respuesta = responseText;
			console.log(Respuesta)
			controldePagosParciales=false
			
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
 	try {
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {

					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					verCerrarCargarPago("2")
					buscarcreditos()
					deudaActual=datos["4"];
	paginaticket=datos["5"];
	cuotasNro=datos["6"];
	var nrofactura=datos["9"];
	var nombrecliente=datos["7"];
	var cicliente=datos["8"];
	var tipoventa=datos["10"];
	 var interespagado=datos["11"];
    var deuda=datos["12"];
    var totalpagado=datos["13"];
    var TotalDescuento=datos["14"];
	  var totalventa=datos["3"];
	  var TotalInteresActual=datos["15"];
	  var deudaActualsininteres=datos["16"];
	pagado=Monto;
	diaatrazado=document.getElementById('inptDiasAtrazadoCargarPago').value;
	//imprimirDivTickeFacturaPago(nombrecliente,cicliente,nrofactura,tipoventa,totalinteres,deuda,totalpagado,TotalDescuento,totalventa,TotalInteresActual,deudaActualsininteres)
	var caja=document.getElementById("pCaja").innerHTML
	var subtotal=document.getElementById("inptTotalVenta").value;
	var descuento=document.getElementById("inptDescuentoCargaPago").value;
							 var totalinteres =(Number(QuitarSeparadorMilValor(TotalInteresActual))+Number(QuitarSeparadorMilValor(interespagado)))

		totalinteres=separadordemilesnumero(totalinteres)
    guardarendriverimpresion(idFkVenta, "PAGO CUOTA","pendiente", caja, cod_localFKUSer, diaatrazado, subtotal,descuento,totalpagado,interespagado,totalinteres,TotalInteresActual,deudaActual,cuotasNro,Monto,nrofactura,userid)
    



				
					document.getElementById("inptDiasAtrazadoCargarPago").value = "";
					document.getElementById("inptMontoCargaPago").value = "";
					verCerrarCargarPago("2")
					buscarcreditos()

				}
			
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
/*RE-IMPRIMIR TICKET*/
function vercerrarhistorialdepagorimprimir(d) {
	if (d == "1") {
		document.getElementById("divHistorialPagosReimpresion").style.display=""
		 document.getElementById("tdEfectoHistorialPagosReImpresion").className="magictime vanishIn"

		buscarhistorialdepagosreimpresion();
	} else {
		 document.getElementById("tdEfectoHistorialPagosReImpresion").className="magictime vanishOut"
		$("div[id=divHistorialPagosReimpresion]").fadeOut(500)
	}
}
function buscarhistorialdepagosreimpresion() {
	document.getElementById("table_historial_pagos_reimpresion").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idFkVenta,
		"funt": "buscarHistorialPagosAReimprimir"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
			document.getElementById("table_historial_pagos_reimpresion").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_pagos_reimpresion").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"]; 
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_historial_pagos_reimpresion").innerHTML = datos_buscados	
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
var elementoPagoReimprimir="";
function obtenerPagosReImprimir(datostr){	
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	elementoPagoReimprimir=datostr;
}
function ReImprimirTicketPagos(){	
	if(elementoPagoReimprimir==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
		return
	}
	datostr=elementoPagoReimprimir
	var Fecha=$(datostr).children('td[id="td_datos_3"]').html();
	var Cajero=$(datostr).children('td[id="td_datos_4"]').html();
	var Pagado=$(datostr).children('td[id="td_datos_15"]').html();
	var DiasAtrazado=$(datostr).children('td[id="td_datos_5"]').html();
	var NombreCliente=$(datostr).children('td[id="td_datos_7"]').html();
	var CiCliente=$(datostr).children('td[id="td_datos_6"]').html();
	var NroRecibo=$(datostr).children('td[id="td_datos_1"]').html();
	var tipoventa=$(datostr).children('td[id="td_datos_8"]').html();
	var totalInteres=$(datostr).children('td[id="td_datos_10"]').html();
	var deudaActual=$(datostr).children('td[id="td_datos_11"]').html();
	var totalpagado=$(datostr).children('td[id="td_datos_15"]').html();
	var totaldescuento=$(datostr).children('td[id="td_datos_9"]').html();
	var TotalVenta=$(datostr).children('td[id="td_datos_16"]').html();
	var InteresActual=$(datostr).children('td[id="td_datos_20"]').html();
	var deudaActualsininteres=$(datostr).children('td[id="td_datos_18"]').html();
	var CuotasNro=$(datostr).children('td[id="td_datos_19"]').html();	
	ReImprimirDivTickeFacturaPago(Fecha,Cajero,CuotasNro,Pagado,DiasAtrazado,NombreCliente,CiCliente,NroRecibo,tipoventa,
	totalInteres,deudaActual,totalpagado,totaldescuento,TotalVenta,InteresActual,deudaActualsininteres)
}
/*HISTORIAL VENTA*/
function verCerrarHistorialVenta(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divHistorialVenta").style.display==""){
	
	if(controldebusquedadHistorialVenta==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
document.getElementById("tdEfectoHistorialVenta").className="magictime vanishOut"
	$("div[id=divHistorialVenta]").fadeOut(500);
		document.getElementById("divMinimizadoHistorialVenta2").style.display='none'
		document.getElementById("divMinimizadoHistorialVenta1").style.display='none'
		limpiarcamposhistorialventa()
	}else{
       if(controlacceso("VERHISTORIALVENTA","accion")==false){	   
	   //SIN PERMISO
	   return;
		}
		document.getElementById("divHistorialVenta").style.display=""
document.getElementById("tdEfectoHistorialVenta").className="magictime slideDownReturn"
			
	}
}
function minimizarHistorialVenta(){
	//document.getElementById("divHistorialVenta").style.display='none'
document.getElementById("tdEfectoHistorialVenta").className="magictime slideDown"
	$("div[id=divHistorialVenta]").fadeOut(500);	
	document.getElementById("divMinimizadoHistorialVenta2").style.display=''
	document.getElementById("divMinimizadoHistorialVenta1").style.display=''
}

function checkfiltroshistorialventa(d){
	if(d=="1"){
	document.getElementById('inptCheckHistorialVenta1').checked=true
	document.getElementById('inptCheckHistorialVenta2').checked=false	
     
	 	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarInfHistorialVentaF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarInfHistorialVentaF2').value = f.getFullYear() + "-" + mes + "-" + dia;
	 
	}else{		
	document.getElementById('inptCheckHistorialVenta1').checked=false
	document.getElementById('inptCheckHistorialVenta2').checked=true
	document.getElementById('inptBuscarInfHistorialVentaF1').value="";
      document.getElementById('inptBuscarInfHistorialVentaF2').value="";
	
	}
}
var registrocargadohistorialventa="";
var totalregistrohistorialventa="";
var controldebusquedadHistorialVenta=false
function cancelarHistorialVenta(){
	controldebusquedadHistorialVenta=false
	document.getElementById("divProgressHistorialVenta").style.backgroundColor='#ff5722'
}
function buscarhistorialventa() {    
	
	var tipoComprobante = document.getElementById("inptBuscarHistorialcomprobante").value
	var fechafiltro = document.getElementById('inptBuscarHistorialVenta1').value
	var nroventa = document.getElementById('inptBuscarHistorialVenta2').value
	var documento = document.getElementById('inptBuscarHistorialVenta3').value
	var cliente = document.getElementById('inptBuscarHistorialVenta4').value
	var telefono = document.getElementById('inptBuscarHistorialVenta5').value
	var tipoventa = document.getElementById('inptBuscarHistorialVenta6').value
	var estadocuenta = document.getElementById('inptBuscarHistorialVenta7').value
	var local = document.getElementById('inptBuscarHistorialVenta8').value
	var fecha1 = document.getElementById('inptBuscarInfHistorialVentaF1').value
	var fecha2 = document.getElementById('inptBuscarInfHistorialVentaF2').value
	if(document.getElementById('inptCheckHistorialVenta1').checked==true){
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
			return
		}
	}
	if(document.getElementById('inptCheckHistorialVenta2').checked==true){
		
	var fecha1 = ""
	var fecha2 = ""
	}	
	
	if(controldebusquedadHistorialVenta==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadHistorialVenta=true
document.getElementById("tbProcessHistorialVenta").style.display="none"
	document.getElementById("table_historial_venta").innerHTML = paginacargando
	document.getElementById("inptRegistroNroHistorialVenta").value = "";
    document.getElementById("inptTotalVentaHistorialVenta").value = "";
    document.getElementById("inptTotalPagosHistorialVenta").value = "";
    document.getElementById("inptTotalPendienteHistorialVenta").value = "";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,	
		"fecha1": fecha1,
		"fecha2": fecha2,
		"fechafiltro": fechafiltro,
		"nroventa": nroventa,
		"documento": documento,
		"cliente": cliente,
		"telefono": telefono,
		"tipoventa": tipoventa,
		"estadocuenta": estadocuenta,
		"local": local,
		"tipoComprobante": tipoComprobante,
		"funt": "historialventa"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_historial_venta").innerHTML = ''
			controldebusquedadHistorialVenta=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_venta").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];              
			  Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("inptRegistroNroHistorialVenta").value = datos[3];
					document.getElementById("inptTotalVentaHistorialVenta").value = datos[4];
					document.getElementById("inptTotalPagosHistorialVenta").value = datos[5];
					document.getElementById("inptTotalPendienteHistorialVenta").value = datos[6];
					document.getElementById("table_historial_venta").innerHTML = datos_buscados
						registrocargadohistorialventa=datos[99];
					totalregistrohistorialventa=datos[100];					
						 if(totalregistrohistorialventa>registrocargadohistorialventa){
						 	var porce=((registrocargadohistorialventa*100)/totalregistrohistorialventa).toFixed(0)
	document.getElementById("divProgressHistorialVenta").style.width=porce+"%"
						 document.getElementById("table_historial_venta").innerHTML += "<div id='table_mas_historial_venta'></div>"
						  buscarMashistorialventa();
					 }else{
						 controldebusquedadHistorialVenta=false
					 }
					
					}
			} catch (error) {
				controldebusquedadHistorialVenta=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarMashistorialventa(c) {    
	var tipoComprobante = document.getElementById("inptBuscarHistorialcomprobante").value
	var fechafiltro = document.getElementById('inptBuscarHistorialVenta1').value
	var nroventa = document.getElementById('inptBuscarHistorialVenta2').value
	var documento = document.getElementById('inptBuscarHistorialVenta3').value
	var cliente = document.getElementById('inptBuscarHistorialVenta4').value
	var telefono = document.getElementById('inptBuscarHistorialVenta5').value
	var tipoventa = document.getElementById('inptBuscarHistorialVenta6').value
	var estadocuenta = document.getElementById('inptBuscarHistorialVenta7').value
	var local = document.getElementById('inptBuscarHistorialVenta8').value
	var fecha1 = document.getElementById('inptBuscarInfHistorialVentaF1').value
	var fecha2 = document.getElementById('inptBuscarInfHistorialVentaF2').value
	if(document.getElementById('inptCheckHistorialVenta1').checked==true){
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
			return
		}
	}
	if(document.getElementById('inptCheckHistorialVenta2').checked==true){
		
	var fecha1 = ""
	var fecha2 = ""
	}	
	
	if(c=="1"){
		controldebusquedadHistorialVenta=true
	}
	if(controldebusquedadHistorialVenta==false){
		
	return
}
controldebusquedadHistorialVenta=true
document.getElementById("tbProcessHistorialVenta").style.display=""
document.getElementById("divProgressHistorialVenta").style.backgroundColor=''
	document.getElementById("table_mas_historial_venta").innerHTML = paginacargando
    var totalventa=document.getElementById("inptTotalVentaHistorialVenta").value;
    var totalpagado=document.getElementById("inptTotalPagosHistorialVenta").value;
    var totalpendiente=document.getElementById("inptTotalPendienteHistorialVenta").value;
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,	
		"fecha1": fecha1,
		"fecha2": fecha2,
		"fechafiltro": fechafiltro,
		"nroventa": nroventa,
		"documento": documento,
		"cliente": cliente,
		"telefono": telefono,
		"tipoventa": tipoventa,
		"estadocuenta": estadocuenta,
		"local": local,
		"totalventa": totalventa,
		"totalpagado": totalpagado,
		"totalpendiente": totalpendiente,
		"registrocargado": registrocargadohistorialventa,
		"tipoComprobante": tipoComprobante,
		"funt": "mashistorialventa"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_mas_historial_venta").innerHTML = ''
			document.getElementById("divProgressHistorialVenta").style.backgroundColor='#ff5722'
			controldebusquedadHistorialVenta=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_mas_historial_venta").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];              
			  Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("inptRegistroNroHistorialVenta").value = datos[3];
					document.getElementById("inptTotalVentaHistorialVenta").value = datos[4];
					document.getElementById("inptTotalPagosHistorialVenta").value = datos[5];
					document.getElementById("inptTotalPendienteHistorialVenta").value = datos[6];
					document.getElementById("table_mas_historial_venta").innerHTML = datos_buscados
						registrocargadohistorialventa=datos[99];
					
						 if(totalregistrohistorialventa>registrocargadohistorialventa){
						 	var porce=((registrocargadohistorialventa*100)/totalregistrohistorialventa).toFixed(0)
	document.getElementById("divProgressHistorialVenta").style.width=porce+"%"
						 document.getElementById("table_mas_historial_venta").innerHTML += "<div id='table_mas_historial_venta'></div>"
						 document.getElementById("table_mas_historial_venta").id=""
						  buscarMashistorialventa();
					 }else{
						 document.getElementById("tbProcessHistorialVenta").style.display="none"
						 controldebusquedadHistorialVenta=false
					 }
					
					}
			} catch (error) {
				document.getElementById("divProgressHistorialVenta").style.backgroundColor='#ff5722'
				controldebusquedadHistorialVenta=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

/*
OPCIONES DE HISTORIAL VENTA
*/
function verCerrarVentanasHistorialVenta(d){
	document.getElementById("btnHistoriaVenta1").style=''
	document.getElementById("btnHistoriaVenta2").style=''
	document.getElementById("btnHistoriaVenta4").style=''
	document.getElementById("btnHistoriaVenta6").style=''
	document.getElementById("cntHistVenta").style.display='none'
	document.getElementById("cntHistVentaPago").style.display='none'
	document.getElementById("cntHistDetalleVenta").style.display='none'
	if(d=="1"){
		document.getElementById("btnHistoriaVenta1").style='background-color:#ff9800;color:#fff'
		document.getElementById("cntHistVenta").style.display=''
	}
	if(d=="2"){
		if (codVentaVentanas == "") {
			ver_vetana_informativa("FALTO SELECCIONAR UNA VENTA", "#")
			verCerrarVentanasHistorialVenta("1")
			return
		}
		 buscarcreditosHistorialVenta()
		 	document.getElementById("btnHistoriaVenta2").style='background-color:#ff9800;color:#fff'
		document.getElementById("cntHistVentaPago").style.display=''
	}		
		if(d=="6"){
		if (codVentaVentanas == "") {
			ver_vetana_informativa("FALTO SELECCIONAR UNA VENTA", "#")
			verCerrarVentanasHistorialVenta("1")
			return
		}
		 buscarDetallesHistorialVenta()
		 	document.getElementById("btnHistoriaVenta6").style='background-color:#ff9800;color:#fff'
		document.getElementById("cntHistDetalleVenta").style.display=''
	}	
}
var codVentaVentanas="";
var codVentaClienteVentanas="";
function buscarcreditosHistorialVenta() {	
		if (codVentaVentanas == "") {
			ver_vetana_informativa("FALTO SELECCIONAR UNA VENTA", "#")
			return
		}
	document.getElementById("table_historial_venta_pagos").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": codVentaVentanas,
		"funt": "creditoshistorialventa"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			document.getElementById("table_historial_venta_pagos").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_venta_pagos").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_historial_venta_pagos").innerHTML = datos_buscados
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarDetallesHistorialVenta() {	
		if (codVentaVentanas == "") {
			ver_vetana_informativa("FALTO SELECCIONAR UNA VENTA", "#")
			return
		}
	document.getElementById("table_historial_venta_detalle").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": codVentaVentanas,
		"funt": "detalleenhistorial"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
			document.getElementById("table_historial_venta_detalle").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_venta_detalle").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_historial_venta_detalle").innerHTML = datos_buscados
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function editarventaselecc() {
	if (document.getElementById("inptRegistroSeleccHistorialVenta").value == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return
	}	
   document.getElementById("divHistorialVenta").style.display='none'
	ControlVentanaVenta="1"
	obtenerdatoshistorialventa(elementoventa)
	limpiarcamposhistorialventa()
	vercerrarOpcionesHistorialVenta("2")
	
	}
var elementoventa = ""
var controltipoventa="";
function obtenerdatoshistorialventa(datostr) {


	
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});

	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptFechaVenta').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptClienteVenta').value = $(datostr).children('td[id="td_datos_2"]').html();
	controltipoventa = $(datostr).children('td[id="td_datos_12"]').html();
	document.getElementById('inptSeleccTipoVenta').value = $(datostr).children('td[id="td_datos_12"]').html();
	document.getElementById('inptVendedorVenta1').value = $(datostr).children('td[id="td_datos_15"]').html();
	document.getElementById('inptVendedorVenta2').value = $(datostr).children('td[id="td_datos_16"]').html();
	document.getElementById('inptCobradorVenta').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptCobradorCargarPago').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inpCodVenta').value = $(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('inptNroVenta').value = $(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('inptSeleccPuntoExpedicionVenta').value = $(datostr).children('td[id="td_datos_36"]').html();
	document.getElementById('inptAccesoCreditoVentaCliente').value =  $(datostr).children('td[id="td_datos_39"]').html();
	var puntoExpedicion=$("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
	if(puntoExpedicion==""){						
	document.getElementById("pNroFactuaCaja").innerHTML ="*"+$(datostr).children('td[id="td_datos_13"]').html()+"*";
	}else{
	document.getElementById("pNroFactuaCaja").innerHTML = "*"+puntoExpedicion+"-"+$(datostr).children('td[id="td_datos_13"]').html()+"*";
	}
	document.getElementById('inptComisionVentaCobrador').value = $(datostr).children('td[id="td_datos_22"]').html();
	document.getElementById('inptlocalVenta').value = $(datostr).children('td[id="td_datos_23"]').html();
	document.getElementById('inptGaranteVenta').value = $(datostr).children('td[id="td_datos_31"]').html();
	document.getElementById('inptDocClienteVenta').value = $(datostr).children('td[id="td_datos_32"]').html();
	document.getElementById('inptDocClienteVenta2').value = $(datostr).children('td[id="td_datos_32"]').html();
	document.getElementById('inptDocGaranteVenta').value = $(datostr).children('td[id="td_datos_33"]').html();
	document.getElementById('inptSeleccTipoComprobanteVenta').value = $(datostr).children('td[id="td_datos_35"]').html();
		if(document.getElementById('inptSeleccTipoComprobanteVenta').value=="FACTURA"){
		document.getElementById("btnImprimirticket").style.display=""
					 document.getElementById("btnImprimirFactura").style.display=""
					 document.getElementById("btnImprimirPagare").style.display=""
	}else{
		document.getElementById("btnImprimirticket").style.display=""
					 document.getElementById("btnImprimirFactura").style.display="none"
					 document.getElementById("btnImprimirPagare").style.display=""
	}
	
	idGaranteFk = $(datostr).children('td[id="td_datos_30"]').html();
	idFkVendedor1 = $(datostr).children('td[id="td_datos_3"]').html();
	idFkVendedor2 = $(datostr).children('td[id="td_datos_14"]').html();
	idFkCliente = $(datostr).children('td[id="td_datos_10"]').html();
	idFkCobrador = $(datostr).children('td[id="td_datos_11"]').html();
	cobradorcargarpagos = $(datostr).children('td[id="td_datos_11"]').html();
	idabmVenta = $(datostr).children('td[id="td_datos_8"]').html();
	idFkVenta = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inpCodVenta').disabled = true
	document.getElementById('inpCodVenta').className = "inputTextDisable"
	document.getElementById('btnAbmVenta').style.display = ""
	document.getElementById('btnAbmVenta').value = "Editar datos"
	buscardetallesventa()
document.getElementById("divAbmVenta").style.display=""
document.getElementById("tdEfectoAbmVenta").className="magictime slideDownReturn"

   document.getElementById("btnMasInfoClienteVenta").style.display='none'
SeleccTipoComprobanteVenta()

}
function obtenerelementohistroialventa(datos) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datos.className = 'tableRegistroSelec'
	elementoventa = datos;
	codVentaVentanas = $(datos).children('td[id="td_datos_8"]').html();
	codVentaClienteVentanas = $(datos).children('td[id="td_datos_10"]').html();
	document.getElementById('inptRegistroSeleccHistorialVenta').value = $(datos).children('td[id="td_datos_13"]').html();
		document.getElementById('inptUsuarioInsertadoPor').value=$(datos).children('td[id="td_datos_100"]').html()
	document.getElementById('inptFechaInsertadoPor').value=$(datos).children('td[id="td_datos_102"]').html()
	document.getElementById('inptUsuarioEditadoPor').value=$(datos).children('td[id="td_datos_101"]').html()
	document.getElementById('inptFechaEditadoPor').value=$(datos).children('td[id="td_datos_103"]').html()
		document.getElementById("btnOpcionesHistorialVenta").style.backgroundColor="#4CAF50";
		document.getElementById("btnAuditoriaVentas").style.backgroundColor="#673ab7";
	
}
function limpiarcamposhistorialventa(){
	
	if(controldebusquedadHistorialVenta==true){
	return
}
	document.getElementById("inptBuscarInfHistorialVentaF1").value="";
	document.getElementById("inptBuscarInfHistorialVentaF2").value="";
	document.getElementById("inptBuscarHistorialVenta1").value="";
	document.getElementById("inptBuscarHistorialVenta2").value="";
	document.getElementById("inptBuscarHistorialVenta3").value="";
	document.getElementById("inptBuscarHistorialVenta4").value="";
	document.getElementById("inptBuscarHistorialVenta5").value="";
	document.getElementById("inptBuscarHistorialVenta6").value="";
	document.getElementById("inptBuscarHistorialVenta7").value="";
	document.getElementById("inptBuscarHistorialVenta8").value="";
	document.getElementById("inptBuscarHistorialcomprobante").value="";
	

	document.getElementById("inptRegistroNroHistorialVenta").value="";
	document.getElementById("inptTotalVentaHistorialVenta").value="";
	document.getElementById("inptTotalPagosHistorialVenta").value="";
	document.getElementById("inptTotalPendienteHistorialVenta").value="";
	document.getElementById("inptRegistroSeleccHistorialVenta").value="";
	document.getElementById("btnOpcionesHistorialVenta").style.backgroundColor="#ccc";
	document.getElementById("btnAuditoriaVentas").style.backgroundColor="#ccc";
	document.getElementById("table_historial_venta").innerHTML=""
	document.getElementById("table_historial_venta_pagos").innerHTML=""
	document.getElementById("table_historial_venta_detalle").innerHTML=""
	document.getElementById("table_historial_venta_detalle").innerHTML=""
	document.getElementById("tbProcessHistorialVenta").style.display="none"
	verCerrarVentanasHistorialVenta("1")
}
function vercerrarOpcionesHistorialVenta(d){
		
	if(d=="1"){
		if(document.getElementById("inptRegistroSeleccHistorialVenta").value==""){
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO","#")
			return false
		}
		document.getElementById("divOpcionesHistorialVenta").style.display=""
 document.getElementById("tdEfectoOpcionesHistorialVenta").className="magictime vanishIn"
	}else{
     document.getElementById("tdEfectoOpcionesHistorialVenta").className="magictime vanishOut"
$("div[id=divOpcionesHistorialVenta]").fadeOut(500);	
    }
}
function obtenerdatosabmdetalleventaDevoluciones(datos) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datos.className = 'tableRegistroSelec'
	elementoDevolucion = datos;
	document.getElementById('inptRegistroSeleccionadoDetalleVentaHistorial').value = $(datos).children('td[id="td_datos_2"]').html();
	
}
/*
ELIMINAR VENTA
*/
function vercerrareliminaregistroventa(d) {
	if (d == "1") {
		document.getElementById("divEliminarVenta").style.display=""
		 document.getElementById("tdEfectoEliminarVenta").className="magictime vanishIn"
	} else {
		 document.getElementById("tdEfectoEliminarVenta").className="magictime vanishOut"
		$("div[id=divEliminarVenta]").fadeOut(500)
	}
}
function eliminarRegistroVenta(){
	if(controlacceso("ELIMINARVENTA","accion")==false){	   
	   //SIN PERMISO
	   return;
		}
		var codventa=$(elementoventa).children('td[id="td_datos_8"]').html();
	var nro=$(elementoventa).children('td[id="td_datos_27"]').html();
	var nroFactura=$(elementoventa).children('td[id="td_datos_13"]').html();
vercerrarOpcionesHistorialVenta("2")		
limpiarcamposhistorialventa()
	if(confirm("Estas Seguro que quieres eliminar esta venta")){	
	if(codventa==""){
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO","#")
			return
	}
	
	if(nro>0){
		ver_vetana_informativa("NO PUEDES ELEMINAR ESTA VENTA POR QUE CUENTA CON UNO O VARIOS DETALLES","#")
			return
	}
	var motivo=document.getElementById("inptSeleccMotivoEliminarVenta").value
	if(motivo==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN MOTIVO","#")
		return
	}
	verCerrarEfectoCargando("1")
	  var datos = new FormData();
			obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", "eliminarVenta")
			 datos.append("codventa" , codventa)			
			 datos.append("motivo" , motivo)			
			 datos.append("nroFactura" , nroFactura)			
			var OpAjax= $.ajax({
						data: datos,
			url: "/GoodVentaByR/php_system/abmventa.php",
			type:"post",
	        cache:false,
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
		
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					document.getElementById("inptRegistroSeleccHistorialVenta").value="";
					document.getElementById("btnOpcionesHistorialVenta").style.backgroundColor="#ccc";
					
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
}
/*
ANULAR VENTA
*/
function verCerrarCancelarVenta(d){
	if(d=="1"){
	if(elementoventa==""){
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO","#")
	  return false;	
	}		
		if(controlacceso("ANULARVENTAS","accion")==false){	   
	   //SIN PERMISO
	   return;
		}	
		vercerrarOpcionesHistorialVenta("2")
	var datos=elementoventa;
	document.getElementById("inptPagadoCancelacion").value=$(datos).children('td[id="td_datos_6"]').html();
	document.getElementById("inptTotalVentaCancelacion").value=$(datos).children('td[id="td_datos_5"]').html();
	document.getElementById('inptMontoDevueltoCancelacion').value=""
	document.getElementById('inptMotivoCancelacion').value=""
	document.getElementById('inptFechaVentaCancelacion').value=""
	codVentaCambio=$(datos).children('td[id="td_datos_8"]').html();;
    vercerrarOpcionesDeCancelacion("1")
	limpiarcamposhistorialventa()
	}
}
function vercerrarOpcionesDeCancelacion(d){
	if(d=="1"){
		$("div[id=divCancelarCuenta]").fadeIn(250)		
	}else{
		$("div[id=divCancelarCuenta]").fadeOut(250)
	}
}
/*
REFINANCIAR COUTA RESTANTE
*/
function verificarcancelacionventa(){	 
	var inptMontoDevueltoCancelacion=document.getElementById('inptMontoDevueltoCancelacion').value
	var inptMotivoCancelacion=document.getElementById('inptMotivoCancelacion').value
	var inptFechaVentaCancelacion=document.getElementById('inptFechaVentaCancelacion').value	
	var inptStockCancelacion=document.getElementById('inptStockCancelacion').value	
  if(codVentaCambio==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN VENTA","#")
	  return false;
  }
  if(inptMontoDevueltoCancelacion==""){
	ver_vetana_informativa("EL MONTO DE DEVOLUCION NO PUEDE QUEDAR VACIO","#")
	  return false;
  }
  if(inptFechaVentaCancelacion==""){
	ver_vetana_informativa("FALTO INGRESAR UNA FECHA","#")
	  return false;
  }
  if(inptMotivoCancelacion==""){
	ver_vetana_informativa("FALTO INGRESAR EL MOTIVO","#")
	  return false;
  }
 abmcancelarventa(inptStockCancelacion,inptMontoDevueltoCancelacion,inptMotivoCancelacion,inptFechaVentaCancelacion,codVentaCambio)	
}
function abmcancelarventa(stock,montodevuelto,motivo,fecha,cod_venta){	
	
	verCerrarEfectoCargando("1")
	  var datos = new FormData();
			obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", "cancelarventa")
			 datos.append("montodevuelto" , montodevuelto)
			  datos.append("motivo" , motivo)
			 datos.append("fecha" , fecha)
			 datos.append("cod_venta" , cod_venta)					
			 datos.append("stock" , stock)					
			var OpAjax= $.ajax({			
			data: datos,
			url: "/GoodVentaByR/php_system/abmventa.php",
			type:"post",
	        cache:false,
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
				 vercerrarOpcionesDeCancelacion("2")
	document.getElementById('inptMontoDevueltoCancelacion').value=""
	document.getElementById('inptMotivoCancelacion').value=""
	document.getElementById('inptFechaVentaCancelacion').value=""
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
function verCerrarquitardevolucionrefinanciamiento2(d){
	if(d=="1"){
		
		if(controlacceso("REFINANCIARVENTA","accion")==false){	   
	   //SIN PERMISO
	   return;
		}
	if(elementoventa==""){
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO","#")
	  return false;	
	}	
	var datos=elementoventa;
	document.getElementById("inptTotalCuotaRefinanciamiento2").value=$(datos).children('td[id="td_datos_37"]').html();
	document.getElementById("inptCuotaNroCambioRefinanciamiento2").value=""
	document.getElementById("inptMonotCambioRefinanciamiento2").value=""
	document.getElementById("inptFechaVentaCambioRefinanciamiento2").value=""
	document.getElementById("inptDescuentoCambioRefinanciamiento2").value="0"
	document.getElementById("inputSelectMetodoCambioRefinanciamiento2").value=$(datos).children('td[id="td_datos_18"]').html();	
	codVentaCambio=$(datos).children('td[id="td_datos_8"]').html();;
    vercerrarOpcionesDeRefinanciamiento2("1")
	vercerrarOpcionesHistorialVenta("2")
	limpiarcamposhistorialventa()
	}
}
function vercerrarOpcionesDeRefinanciamiento2(d){
	if(controlacceso("REFINANCIARVENTA","accion")==false){	   
	   //SIN PERMISO
	   return;
		}			
	if(d=="1"){
		$("div[id=divRefinanciarcuota]").fadeIn(250)
        document.getElementById("inptInteresRefinanciamiento2").value="0,01"
        document.getElementById("inptDiasGraciaRefinanciamiento2").value="10"
	}else{
		$("div[id=divRefinanciarcuota]").fadeOut(250)
	}
}
function calcular_cuota_refinanciamiento() {
	var t = QuitarSeparadorMilValor(document.getElementById('inptTotalCuotaRefinanciamiento2').value);
	var c = QuitarSeparadorMilValor(document.getElementById('inptCuotaNroCambioRefinanciamiento2').value);
	if (isNaN(c)) {
		document.getElementById('inptCuotaNroCambioRefinanciamiento2').value = 1;
		document.getElementById('inptMonotCambioRefinanciamiento2').value = document.getElementById('inptTotalCuotaRefinanciamiento2').value;
		c = 0;
	}
	var c = parseFloat(c);
	var t = parseFloat(t);
	document.getElementById('inptMonotCambioRefinanciamiento2').value = t / c;
	//separadordemiles(document.getElementById('inpt_interes_pago_venta'))
	separadordemiles(document.getElementById('inptMonotCambioRefinanciamiento2'))
}
function verificarrefinanciamientoenCuota(){	 
	var inptTotalCuotaRefinanciamiento2=document.getElementById('inptTotalCuotaRefinanciamiento2').value
	var inputSelectMetodoCambioRefinanciamiento2=document.getElementById('inputSelectMetodoCambioRefinanciamiento2').value
	var inptCuotaNroCambioRefinanciamiento2=document.getElementById('inptCuotaNroCambioRefinanciamiento2').value
	var inptMonotCambioRefinanciamiento2=document.getElementById('inptMonotCambioRefinanciamiento2').value
	var inptFechaVentaCambioRefinanciamiento2=document.getElementById('inptFechaVentaCambioRefinanciamiento2').value
	var inptDescuentoCambioRefinanciamiento2=document.getElementById('inptDescuentoCambioRefinanciamiento2').value
	var inptDiasGraciaRefinanciamiento2=document.getElementById('inptDiasGraciaRefinanciamiento2').value
	var inptInteresRefinanciamiento2=document.getElementById('inptInteresRefinanciamiento2').value	
  if(codVentaCambio==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO","#")
	  return false;
  }
  if(inptTotalCuotaRefinanciamiento2==""){
	ver_vetana_informativa("FALTO INGRESAR EL TOTAL FINANCIADO","#")
	  return false;
  }
   if(inptCuotaNroCambioRefinanciamiento2==""){
	ver_vetana_informativa("FALTO INGRESAR EL NRO DE CUOTA","#")
	  return false;
  }
  if(inptMonotCambioRefinanciamiento2==""){
	ver_vetana_informativa("FALTO INGRESAR EL MONTO A PAGAR","#")
	  return false;
  }
  if(inptFechaVentaCambioRefinanciamiento2==""){
	ver_vetana_informativa("FALTO INGRESAR LA FECHA DE PAGO","#")
	  return false;
  }
 abmrefinacimientoCuota(inptInteresRefinanciamiento2,inptDescuentoCambioRefinanciamiento2,inptTotalCuotaRefinanciamiento2,inputSelectMetodoCambioRefinanciamiento2,inptCuotaNroCambioRefinanciamiento2,inptMonotCambioRefinanciamiento2,inptFechaVentaCambioRefinanciamiento2,inptDiasGraciaRefinanciamiento2,codVentaCambio)
}
function abmrefinacimientoCuota(interes,descuento,total,metodopago,nroCuota,Monto,iniciopago,dias,cod_venta) {
	if(controlacceso("REFINANCIARVENTA","accion")==false){	   
	   //SIN PERMISO
	   return;
		}		
	verCerrarEfectoCargando("1")
	  var datos = new FormData();
			obtener_datos_user();
			 datos.append("useru" , userid)
			 datos.append("passu" , passuser)
			 datos.append("navegador" , navegador)
			 datos.append("funt", "refinanciarcuotas")
			 datos.append("cod_venta" , cod_venta)
			  datos.append("Monto" , Monto)
			 datos.append("metodopago" , metodopago)
			 datos.append("iniciopago" , iniciopago)
			 datos.append("nroCuota" , nroCuota)
			 datos.append("total" , total)
			 datos.append("dias" , dias)
			 datos.append("interes" , interes)
			 datos.append("descuento" , descuento)			
			var OpAjax= $.ajax({			
			data: datos,
			url: "/GoodVentaByR/php_system/abmcreditos.php",
			type:"post",
	        cache:false,
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
				ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
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
/*
EXPEDIENTE
*/
function verCerrarInformeExpedientes(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divInfExpediente").style.display==""){
	document.getElementById("tdEfectoInfExpediente").className="magictime vanishOut"
	$("div[id=divInfExpediente]").fadeOut(500);	
	document.getElementById("divMinimizadoExpedienteCliente1").style.display="none"
	document.getElementById("divMinimizadoExpedienteCliente2").style.display="none"
	limpiarcamposexpedientesclientes()
	}else{		
	if(controlacceso("VEREXPEDIENTEDELCLIENTE","accion")==false){return;}		
		document.getElementById("divInfExpediente").style.display=""
		  document.getElementById("tdEfectoInfExpediente").className="magictime slideDownReturn"
		
	}
}
function limpiarcamposexpedientesclientes(){
	document.getElementById("inptBuscarInfExpedientefiltro").value=""
	document.getElementById("inptBuscarInfExpedienteNroDocumento").value=""
	document.getElementById("inptBuscarInfExpedienteNroTelef").value=""
	document.getElementById("inptRegistroNroExpVenta").value=""
	document.getElementById("inptTotalExpVenta").value=""
	document.getElementById("inptTotalPagExpVenta").value=""
	document.getElementById("inptTotalDeudaExpVenta").value=""
	document.getElementById("inptTotalDeudaExpVenta").value=""
	document.getElementById("inptRegistroNroExpVentaCancelado").value=""
	document.getElementById("inptTotalExpVentaCancelado").value=""
	document.getElementById("inptTotalPagExpVentaCancelado").value=""
	document.getElementById("inptRegistroNroExpProductosComprados").value=""
	document.getElementById("inptRegistroNroExpCambios").value=""
	document.getElementById("inptRegistroNroExpPagos").value=""
	document.getElementById("inptTotalDescExpPa").value=""
	document.getElementById("inptTotalInteresExpPa").value=""
	document.getElementById("inptTotalPagosExpPa").value=""
	document.getElementById("inptRegistroNroExpPagosPend").value=""
	document.getElementById("inptTotalDeudaExpPe").value=""
	document.getElementById("table_historial_expediente_ventas").innerHTML=""
	document.getElementById("table_historial_expediente_ventas_canceladas").innerHTML=""
	document.getElementById("table_historial_expediente_productos_comprados").innerHTML=""
	document.getElementById("table_historial_expediente_cambios").innerHTML=""
	document.getElementById("table_historial_expediente_pagos").innerHTML=""
	document.getElementById("table_historial_expediente_pagos_pendientes").innerHTML=""
}
function minimizarexpedientecliente(){
	document.getElementById("tdEfectoInfExpediente").className="magictime slideDown"
	$("div[id=divInfExpediente]").fadeOut(500);	
	document.getElementById("divMinimizadoExpedienteCliente1").style.display=""
	document.getElementById("divMinimizadoExpedienteCliente2").style.display=""
}
function irAExtractodesdeVenta() {
	if (document.getElementById("inptRegistroSeleccHistorialVenta").value == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return
	}
	if(controlacceso("VEREXPEDIENTEDELCLIENTE","accion")==false){return;}		
		 document.getElementById("divHistorialVenta").style.display='none'
	verCerrarInformeExpedientes()
	document.getElementById('inptBuscarInfExpedientefiltro').value = $(elementoventa).children('td[id="td_datos_2"]').html();
	document.getElementById('inptBuscarInfExpedienteNroDocumento').value = $(elementoventa).children('td[id="td_datos_32"]').html();
	document.getElementById('inptBuscarInfExpedienteNroTelef').value = $(elementoventa).children('td[id="td_datos_34"]').html();
	codClienteFkExpediente = $(elementoventa).children('td[id="td_datos_10"]').html();	
	buscarexpediente()
}
function verCerrarVentanasExpedientes(d){
	document.getElementById("btnEspedientes1").style=''
	document.getElementById("btnEspedientes2").style=''
	document.getElementById("btnEspedientes3").style=''
	document.getElementById("btnEspedientes4").style=''
	document.getElementById("btnEspedientes6").style=''
	document.getElementById("btnEspedientes7").style=''
	document.getElementById("cntExpHistVenta").style.display='none'
	document.getElementById("cntExpHistPagos").style.display='none'
	document.getElementById("cntExpHistPagosPend").style.display='none'
	document.getElementById("cntExpHistCambios").style.display='none'
	document.getElementById("cntExpHistExtr").style.display='none'
	document.getElementById("cntExpHistVentaCancelada").style.display='none'
	document.getElementById("cntExpProductosComprados").style.display='none'
	if(d=="1"){
		document.getElementById("btnEspedientes1").style='background-color:#FF9800;color:#fff'
		document.getElementById("cntExpHistVenta").style.display=''	}
	if(d=="2"){
		document.getElementById("btnEspedientes2").style='background-color:#FF9800;color:#fff'
			document.getElementById("cntExpHistPagos").style.display=''
	}	
	if(d=="3"){
		document.getElementById("btnEspedientes3").style='background-color:#FF9800;color:#fff'
			document.getElementById("cntExpHistPagosPend").style.display=''
	}
	if(d=="4"){
		document.getElementById("btnEspedientes4").style='background-color:#FF9800;color:#fff'
		document.getElementById("cntExpHistCambios").style.display=''
	}

	if(d=="6"){
		document.getElementById("btnEspedientes6").style='background-color:#FF9800;color:#fff'
		document.getElementById("cntExpHistVentaCancelada").style.display=''
	}
	if(d=="7"){
		document.getElementById("btnEspedientes7").style='background-color:#FF9800;color:#fff'
		document.getElementById("cntExpProductosComprados").style.display=''
	}	
}
var codClienteFkExpediente="";
function buscarexpediente(){
if(controlacceso("VEREXPEDIENTEDELCLIENTE","accion")==false){return;}		
 
	buscarexpedienteventa()
	buscarexpedienteCambios("Cambio")
	buscarexpedientepagos()
	buscarexpedientependientes()
	buscarexpedienteventascanceladas()
	buscarexpedienteproductosComprados()
}
function buscarexpedienteventa(){ 
if(controlacceso("VEREXPEDIENTEDELCLIENTE","accion")==false){return;}		
if(codClienteFkExpediente==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN CLIENTE")
						return false;
}
 var zona=""
 document.getElementById("table_historial_expediente_ventas").innerHTML=paginacargando		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"zona": zona,
			"cliente": codClienteFkExpediente,
			"funt": "buscarexpedientes"
			};
	 $.ajax({			
			data: datos,
			url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_historial_expediente_ventas").innerHTML=''
			},
			success: function(responseText)
			{	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_historial_expediente_ventas").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		
            Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {						
		  var datos_buscados=datos[2];		 
			document.getElementById("table_historial_expediente_ventas").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroExpVenta").value=datos[3];
			document.getElementById("inptTotalExpVenta").value=datos[4];
			document.getElementById("inptTotalPagExpVenta").value=datos[5];
			document.getElementById("inptTotalDeudaExpVenta").value=datos[6];	  
			
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
function buscarexpedienteproductosComprados(){ 
if(controlacceso("VEREXPEDIENTEDELCLIENTE","accion")==false){return;}		
if(codClienteFkExpediente==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN CLIENTE")
						return false;
}
 var zona=""
 document.getElementById("table_historial_expediente_productos_comprados").innerHTML=paginacargando		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"zona": zona,
			"cliente": codClienteFkExpediente,
			"funt": "buscarexpedientes"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
			document.getElementById("table_historial_expediente_productos_comprados").innerHTML=''
			},
			success: function(responseText)
			{	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_historial_expediente_productos_comprados").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  		  
			 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {					
		  var datos_buscados=datos[2];		 
			document.getElementById("table_historial_expediente_productos_comprados").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroExpProductosComprados").value=datos[3];		
			
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
function buscarexpedienteventascanceladas(){
	if(controlacceso("VEREXPEDIENTEDELCLIENTE","accion")==false){return;}		
if(codClienteFkExpediente==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN CLIENTE")
						return false;
}
 var zona=""
 document.getElementById("table_historial_expediente_ventas_canceladas").innerHTML=paginacargando		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"zona": zona,
			"cliente": codClienteFkExpediente,
			"funt": "buscarexpedientescancelados"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_historial_expediente_ventas_canceladas").innerHTML=''
			},
			success: function(responseText)
			{	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_historial_expediente_ventas_canceladas").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {						
		  var datos_buscados=datos[2];		 
			document.getElementById("table_historial_expediente_ventas_canceladas").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroExpVentaCancelado").value=datos[3];		
			document.getElementById("inptTotalExpVentaCancelado").value=datos[4];
			document.getElementById("inptTotalPagExpVentaCancelado").value=datos[5];	
			
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
function buscarexpedienteCambios(motivo){ 
if(controlacceso("VEREXPEDIENTEDELCLIENTE","accion")==false){return;}		
if(codClienteFkExpediente==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN CLIENTE")
						return false;
}

	document.getElementById("table_historial_expediente_cambios").innerHTML=paginacargando

 		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"motivo": motivo,
			"cliente": codClienteFkExpediente,
			"funt": "buscarexpedientescambios"
			};
	 $.ajax({			
			data: datos,
			url: "/GoodVentaByR/php_system/abmventa.php",
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
	
	document.getElementById("table_historial_expediente_cambios").innerHTML=""


			},
			success: function(responseText)
			{	
			var Respuesta=responseText;
     console.log(Respuesta)
				
	document.getElementById("table_historial_expediente_cambios").innerHTML=""


			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"]; 			
 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
		  var datos_buscados=datos[2];
		 	
	document.getElementById("table_historial_expediente_cambios").innerHTML=datos_buscados
		document.getElementById("inptRegistroNroExpCambios").value=datos[3];



  
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
function buscarexpedientepagos(){ 
if(controlacceso("VEREXPEDIENTEDELCLIENTE","accion")==false){return;}		
if(codClienteFkExpediente==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN CLIENTE")
						return false;
}
 document.getElementById("table_historial_expediente_pagos").innerHTML=paginacargando		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": codClienteFkExpediente,
			"funt": "buscarcuentasExpCobrados"
			};
	 $.ajax({			
			data: datos,
			url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			document.getElementById("table_historial_expediente_pagos").innerHTML=''
			},
			success: function(responseText)
			{	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_historial_expediente_pagos").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  		  
		 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {					
		  var datos_buscados=datos[2];		 
			document.getElementById("table_historial_expediente_pagos").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroExpPagos").value=datos[3];
			document.getElementById("inptTotalPagosExpPa").value=datos[4];
			document.getElementById("inptTotalDescExpPa").value=datos[5];
			document.getElementById("inptTotalInteresExpPa").value=datos[6];	
			
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
function buscarexpedientependientes(){ 
if(controlacceso("VEREXPEDIENTEDELCLIENTE","accion")==false){return;}		
if(codClienteFkExpediente==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN CLIENTE")
						return false;
}
 document.getElementById("table_historial_expediente_pagos_pendientes").innerHTML=paginacargando		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": codClienteFkExpediente,
			"funt": "buscarccuentasExpPendientes"
			};
	 $.ajax({			
			data: datos,
			url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			document.getElementById("table_historial_expediente_pagos_pendientes").innerHTML=''
			},
			success: function(responseText)
			{	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_historial_expediente_pagos_pendientes").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  			
			 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				
		  var datos_buscados=datos[2];		 
			document.getElementById("table_historial_expediente_pagos_pendientes").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroExpPagosPend").value=datos[3];
			document.getElementById("inptTotalDeudaExpPe").value=datos[4];  
			
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
/*
COMPRAS
*/
var idAbmCompra = "";
function verCerrarAbmCompra(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmCompra").style.display==""){
document.getElementById("tdEfectoAbmCompra").className="magictime vanishOut"
	$("div[id=divAbmCompra]").fadeOut(500);	
		document.getElementById("divMinimizadoCargarCompras1").style.display="none"
		document.getElementById("divMinimizadoCargarCompras2").style.display="none"
		limpiarCompras()
		
	}else{		
	if(controlacceso("VERCARGADECOMPRAS","accion")==false){return;}
		document.getElementById("divAbmCompra").style.display=""
		document.getElementById("tdEfectoAbmCompra").className="magictime slideDownReturn"
		document.getElementById("TdCerrarCompras1").style.display=""
		document.getElementById("TdCerrarCompras2").style.display="none"
	}
}
function verCerrarAbmCompra2(){
	if(document.getElementById("divAbmCompra").style.display==""){
		document.getElementById("tdEfectoAbmCompra").className="magictime vanishOut"
	$("div[id=divAbmCompra]").fadeOut(500);	
		
			document.getElementById("divMinimizadoCargarCompras1").style.display="none"
		document.getElementById("divMinimizadoCargarCompras2").style.display="none"
		limpiarCompras()
		
	}else{		
	if(controlacceso("VERCARGADECOMPRAS","accion")==false){return;}
			document.getElementById("divAbmCompra").style.display=""
		document.getElementById("tdEfectoAbmCompra").className="magictime slideDownReturn"
		document.getElementById("TdCerrarCompras2").style.display=""
		document.getElementById("TdCerrarCompras1").style.display="none"
	
	}
}
function minizarventaCompras(d){
	document.getElementById("tdEfectoAbmCompra").className="magictime slideDown"
	$("div[id=divAbmCompra]").fadeOut(500);
	document.getElementById("divMinimizadoCargarCompras1").style.display=""
	document.getElementById("divMinimizadoCargarCompras2").style.display=""
}
function verCerrarOpcionCompra(d) {
	if (d == "1") {
		$("div[id=divOpcionesCompra]").fadeIn(250)
	} else {
		$("div[id=divOpcionesCompra]").fadeOut(250)
	}
}
function verificarcamposCompra() {
	var inptNrocompra = document.getElementById('inptNrocompra').value
	document.getElementById('inpCodCompra').value = document.getElementById('inptNrocompra').value;
	var inptFechaCompra = document.getElementById('inptFechaCompra').value
	var inptProveedorCompra = document.getElementById('inptProveedorCompra').value
	var inptlocalCompra = document.getElementById('inptlocalCompra').value
	var inptPagadocompra1 = document.getElementById('inptPagadocompra1').value
	var inptPagadocompra2 = document.getElementById('inptPagadocompra2').value
	var inptDescuentocompra = document.getElementById('inptDescuentocompra').value
	if (inptNrocompra == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE COMPRA", "#")
		return false;
	}
	if (inptProveedorCompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL PROVEEDOR", "#")
		return false;
	}
	if (inptFechaCompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE COMPRA", "#")
		return false;
	}
	var accion = "";
	if (idAbmCompra != "") {
		accion = "editar";
		if(controlacceso("EDITARCARGADECOMPRAS","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("INSERTARCARGADECOMPRAS","accion")==false){return;}
	}
	abmcompra(inptNrocompra, inptFechaCompra, idAbmCompra, codProveedorCompra, inptlocalCompra,inptPagadocompra1,inptPagadocompra2,inptDescuentocompra, accion);
}
function abmcompra(num_comprobante, fecha_compra, cod_compra, cod_proveedorFK, cod_local,pagado1,pagado2,descuento ,accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cod_compra", cod_compra)
	datos.append("fecha_compra", fecha_compra)
	datos.append("cod_proveedorFK", cod_proveedorFK)
	datos.append("num_comprobante", num_comprobante)
	datos.append("cod_local", cod_local)
	datos.append("pagado1", pagado1)
	datos.append("pagado2", pagado2)
	datos.append("descuento", descuento)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmCompra = datos["2"];
					verCerrarOpcionCompra("2")
					buscardetallescompra()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function verificarcamposDetallesCompra() {
	var inptProductoCompra = document.getElementById('inptProductoCompra').value
	var inptCantProductoCompra = document.getElementById('inptCantProductoCompra').value
	var inptCostoProductoCompra = document.getElementById('inptCostoProductoCompra').value
	if (idFkProductocompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO", "#")
		return false;
	}	
	var inptNrocompra = document.getElementById('inptNrocompra').value
	var inptFechaCompra = document.getElementById('inptFechaCompra').value
	var inptProveedorCompra = document.getElementById('inptProveedorCompra').value
	var inptlocalCompra = document.getElementById('inptlocalCompra').value
	var inptPagadocompra1 = document.getElementById('inptPagadocompra1').value
	var inptPagadocompra2 = document.getElementById('inptPagadocompra2').value
	var inptDescuentocompra = document.getElementById('inptDescuentocompra').value
	if (inptNrocompra == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE COMPRA", "#")
		return false;
	}
	if (inptProveedorCompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL PROVEEDOR", "#")
		return false;
	}
	if (inptFechaCompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE COMPRA", "#")
		return false;
	}
    document.getElementById('inptlocalCompra').disabled=true
	if(controlacceso("EDITARCARGADECOMPRAS","accion")==false){return;}
	var accion = "nuevo";
	abmDetalleCompra(inptNrocompra,inptFechaCompra,codProveedorCompra,inptlocalCompra,inptPagadocompra1,inptPagadocompra2,inptDescuentocompra,idAbmCompra, idFkProductocompra, inptCantProductoCompra, inptCostoProductoCompra, idDetalleCompra, accion);
}
function abmDetalleCompra(num_comprobante,fecha_compra,cod_proveedorFK,cod_local,pagado1,pagado2,descuento,cod_compraFK, cod_productoFK, cantidad_detalle_compra, precio_producto, cod_detalle_compra, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("cantidad_detalle_compra", cantidad_detalle_compra)
	datos.append("precio_producto", precio_producto)
	datos.append("subTotal", 0)
	datos.append("cod_productoFK", cod_productoFK)
	datos.append("cod_compraFK", cod_compraFK)
	datos.append("cod_detalle_compra", cod_detalle_compra)
	datos.append("num_comprobante", num_comprobante)
	datos.append("fecha_compra", fecha_compra)
	datos.append("cod_proveedorFK", cod_proveedorFK)
	datos.append("cod_local", cod_local)
	datos.append("pagado1", pagado1)
	datos.append("pagado2", pagado2)
	datos.append("descuento", descuento)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallecompra.php",
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
					idAbmCompra=datos["2"]
					verCerrarOpcionDetalleCompra("2")
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					document.getElementById("inptProductoCompra").value = ""
					document.getElementById("inptCantProductoCompra").value = ""
					document.getElementById("inptCostoProductoCompra").value = ""
					document.getElementById("btnAbmCompra").value = "Editar Datos"
					document.getElementById("btnAbmCompra").style.display = ""
					document.getElementById("btneditarproductocompras").style.backgroundColor="#ccc";
					document.getElementById("btneditarprecioscompras").style.backgroundColor="#ccc";
		document.getElementById("btnAddDetalleCompra").style.backgroundColor="#ccc";
					idFkProductocompra = ""
					buscardetallescompra();
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscardetallescompra() {
	document.getElementById("table_abm_detalle_compra").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idAbmCompra,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallecompra.php",
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
			document.getElementById("table_abm_detalle_compra").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_detalle_compra").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {		
					var datos_buscados = datos[2];
					document.getElementById("table_abm_detalle_compra").innerHTML = datos_buscados
					document.getElementById("inptTotalCompra").value=datos[3]
					document.getElementById("inptTotalRegistro").value=datos[4]
					document.getElementById('inptDescuentocompra').value=datos[6];
					document.getElementById('inptDescuenCompra').value=datos[6];
	        	    document.getElementById('inptSubTotalCompra').value=datos[5];
	        	    document.getElementById('btnAddPagosCompas').style.backgroundColor='#4CAF50';
	        	    document.getElementById('btnEliminarCompas').style.backgroundColor='red';
					
					}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});

}
function verCerrarOpcionEliminarCompra(d) {
	if (d == "1") {
			if(controlacceso("ELIMINARCOMPRA","accion")==false){return;}
		if(idAbmCompra==""){
			ver_vetana_informativa("FALTO SELECCIONAR UNA COMPRA O INICIALIZAR UNA NUEVA COMPRA")
					return false;
		}		
		document.getElementById('inptNroCompraEliminar').value=document.getElementById('inptNrocompra').value
		document.getElementById("divOpcionesEliminarCompra").style.display=""
		 document.getElementById("tdEfectoEliminarCompra").className="magictime slideLeftReturn"
	
	} else {
		document.getElementById("tdEfectoEliminarCompra").className="magictime slideRight"
		$("div[id=divOpcionesEliminarCompra]").fadeOut(250)
	}
}
function EliminarEstaCompra() {
	var motivo=document.getElementById('inptMotivoEliminarCompra').value
	if(motivo==""){
		ver_vetana_informativa("FALTO INGRESAR EL MOTIVO")
		return
	}
if(idAbmCompra==""){
			ver_vetana_informativa("FALTO SELECCIONAR UNA COMPRA O INICIALIZAR UNA NUEVA COMPRA")
					return false;
		}	
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "eliminarcompra")
	datos.append("idAbmCompra", idAbmCompra)
	datos.append("motivo", motivo)
	
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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

					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					limpiarCompras()
					verCerrarOpcionEliminarCompra("2")

				}
				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}




function verCerrarOpcionPagosCompra(d) {
	if (d == "1") {
			if(controlacceso("CARGARPAGOS","accion")==false){return;}
		if(idAbmCompra==""){
			ver_vetana_informativa("FALTO SELECCIONAR UNA COMPRA O INICIALIZAR UNA NUEVA COMPRA")
					return false;
		}		
		limpiarCamposPagosCompra()
		document.getElementById("divCargarPagosCompra").style.display=""
		 document.getElementById("tdEfectoCargarPagosCompras").className="magictime slideLeftReturn"
		buscarhistorialdepagocompra()
	} else {
		document.getElementById("tdEfectoCargarPagosCompras").className="magictime slideRight"
		$("div[id=divCargarPagosCompra]").fadeOut(250)
	}
}
var idAbmPagoCompra="";
function limpiarCamposPagosCompra(){
	document.getElementById("inptMontoPagoCompra").value=""
	document.getElementById("inptNroChequePagoCompra").value=""
	document.getElementById("inptEstadoPagoCompra").value="Pagado"
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptFechaPagoCompra').value = f.getFullYear() + "-" + mes + "-" + dia;
	document.getElementById('inptFechadelPagoCompra').value = f.getFullYear() + "-" + mes + "-" + dia;
	idAbmPagoCompra="";
		document.getElementById("btnEliminarHistorialPago").style.display="none";
	document.getElementById("btnAddPagoCompra").value="Guardar"
	document.getElementById("inptMontoPagoCompra").style.width="295px";
}
function checkTipoPagoCompra(d){
	if(d=="1"){
	document.getElementById('inptSeleccTipoPagoCompra1').checked=true
		document.getElementById('inptSeleccTipoPagoCompra2').checked=false	
			document.getElementById("tbNroCheque").style.display="none";
	}else{
		
		document.getElementById('inptSeleccTipoPagoCompra1').checked=false
		document.getElementById('inptSeleccTipoPagoCompra2').checked=true
			document.getElementById("tbNroCheque").style.display="";
	}
}
function verificarpagoscompras() {
	var inptMontoPagoCompra = document.getElementById('inptMontoPagoCompra').value
	var inptEstadoPagoCompra = document.getElementById('inptEstadoPagoCompra').value
	var inptFechaPagoCompra = document.getElementById('inptFechaPagoCompra').value
	var inptFechadelPagoCompra = document.getElementById('inptFechadelPagoCompra').value
	var inptNroChequePagoCompra = document.getElementById('inptNroChequePagoCompra').value
   if(inptMontoPagoCompra==""){
	   ver_vetana_informativa("FALTO INGRESAR UN MONTO", "#")
		return false;
   }
   
   if(document.getElementById('inptSeleccTipoPagoCompra1').checked==true){
	   var inptTipoPagoCompra="Efectivo";
   }else{
	    var inptTipoPagoCompra="Cheque";
   }
   if(inptTipoPagoCompra==""){
	   ver_vetana_informativa("FALTO SELECCIONAR EL TIPO DE PAGO", "#")
		return false;
   }
   if(inptFechadelPagoCompra==""){
	   ver_vetana_informativa("FALTO SELECCIONAR LA FECHA A PAGO", "#")
		return false;
   }
   if(inptFechadelPagoCompra==""){
	   ver_vetana_informativa("FALTO SELECCIONAR EL TIPO DE PAGO", "#")
		return false;
   }
	var accion = "";
	if (idAbmPagoCompra != "") {
		accion = "editarpago";
	if(controlacceso("EDITARPAGOS","accion")==false){return;}
	} else {
		accion = "nuevopago";
		if(controlacceso("CARGARPAGOS","accion")==false){return;}
	}
	abmPagoDeCompra(inptNroChequePagoCompra,inptMontoPagoCompra,inptTipoPagoCompra,inptEstadoPagoCompra,inptFechaPagoCompra,inptFechadelPagoCompra,idAbmPagoCompra,idAbmCompra, accion);
}
function eliminarEstePagoVenta(){
	if(controlacceso("ELIMINARPAGOS","accion")==false){return;}
		abmPagoDeCompra("10","x","x","x","x","x",idAbmPagoCompra,"x", "eliminarpago");
}
function abmPagoDeCompra(nrocheque,monto,tipo,estado,fechaapagar,fechadelpago,codpago,cod_compraFk, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("monto", monto)
	datos.append("tipo", tipo)
	datos.append("estado", estado)
	datos.append("fechaapagar", fechaapagar)
	datos.append("fechadelpago", fechadelpago)
	datos.append("codpago", codpago)
	datos.append("cod_compraFk", cod_compraFk)
	datos.append("nrocheque", nrocheque)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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

					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					limpiarCamposPagosCompra()
					buscarhistorialdepagocompra()

				}
				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function buscarhistorialdepagocompra() { 

		 document.getElementById("table_vista_pagos_compra").innerHTML=paginacargando
		 	document.getElementById("inptTotalRegistroPagoCompra").value =""
		 	document.getElementById("inptTotalPagoCompra").value =""
		 	document.getElementById("inptTotalPagodoPagoCompra").value =""
		 	document.getElementById("inptTotalPendientePagoCompra").value =""
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": idAbmCompra,
			"funt": "buscarpagoscompra"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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
			document.getElementById("table_vista_pagos_compra").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_pagos_compra").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_vista_pagos_compra").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistroPagoCompra").value =datos[3];
		 	        document.getElementById("inptTotalPagoCompra").value  =datos[4];
		 	        document.getElementById("inptPagosRealizadoscompra").value  =datos[4];
		 	        document.getElementById("inptTotalPagodoPagoCompra").value =datos[5];
		 	         document.getElementById("inptTotalPendientePagoCompra").value =datos[6];
					 
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function obtenerdatoshistorialpago(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	idAbmPagoCompra = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById("inptMontoPagoCompra").value = $(datostr).children('td[id="td_datos_2"]').html();
	inptTipoPagoCompra = $(datostr).children('td[id="td_datos_3"]').html();
	if(inptTipoPagoCompra=="Efectivo"){
		checkTipoPagoCompra("1")
	}else{
		checkTipoPagoCompra("2")
	}
	
	document.getElementById("inptFechaPagoCompra").value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById("inptFechadelPagoCompra").value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById("inptEstadoPagoCompra").value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById("inptNroChequePagoCompra").value = $(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById("btnEliminarHistorialPago").style.display="";
	document.getElementById("inptMontoPagoCompra").style.width="150px";
document.getElementById("btnAddPagoCompra").value="Editar"
}
function verCerrarOpcionDetalleCompra(d) {
	if (d == "1") {
				if(controlacceso("EDITARCARGADECOMPRAS","accion")==false){return;}
		$("div[id=divOpcionesDetallesCmpra]").fadeIn(250)

	} else {
		$("div[id=divOpcionesDetallesCmpra]").fadeOut(250)
	}
}
var cantidaDetalleSelecCompra = "";
var codproductodetalleSelectCompra = "";
var idDetalleCompra = "";
function obtenerdatosabmdetallecompra(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptCodDetalleCompra').value = $(datostr).children('td[id="td_id_2"]').html();
	document.getElementById('inptNombreProductoDetalleCompra').value = $(datostr).children('td[id="td_datos_1"]').html();
	idDetalleCompra = $(datostr).children('td[id="td_id_2"]').html();
	cantidaDetalleSelecCompra = $(datostr).children('td[id="td_datos_3"]').html();
	codproductodetalleSelectCompra = $(datostr).children('td[id="td_id_1"]').html();
	verCerrarOpcionDetalleCompra("1")
}
function eliminarDetalleCompra() {
	if (idDetalleCompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
		if(controlacceso("EDITARCARGADECOMPRAS","accion")==false){return;}
		var inptlocalCompra = document.getElementById('inptlocalCompra').value
			
	abmDetalleCompra("0","0","0",inptlocalCompra,"0","0","0",idAbmCompra, codproductodetalleSelectCompra, cantidaDetalleSelecCompra, "0", idDetalleCompra, "quitar");
}
function limpiarCompras() {
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptFechaCompra').value = f.getFullYear() + "-" + mes + "-" + dia;
	document.getElementById('inptNrocompra').value = ""
	document.getElementById('inpCodCompra').value = "";
	document.getElementById('inptProveedorCompra').value = ""
	document.getElementById('inptPagadocompra1').value = ""
	document.getElementById('inptDescuentocompra').value = ""
	document.getElementById('inptPagosRealizadoscompra').value = ""
	document.getElementById('inptPagadocompra2').value = ""
	document.getElementById("table_vista_producto_compra").innerHTML=""
	document.getElementById("table_abm_detalle_compra").innerHTML=""
	 document.getElementById('inptlocalCompra').disabled=false
	idAbmCompra = "";
	document.getElementById("inptProductoCompra").value = ""
	document.getElementById("inptCantProductoCompra").value = ""
	document.getElementById("inptCostoProductoCompra").value = ""
	document.getElementById("inptTotalRegistro").value = ""
	document.getElementById("inptTotalCompra").value = "0"
	document.getElementById("inptDescuenCompra").value = "0"
	document.getElementById("inptSubTotalCompra").value = "0"
	document.getElementById("inptTotalRegistro").value = "0"
	document.getElementById("btneditarproductocompras").style.backgroundColor="#ccc";
		document.getElementById("btneditarprecioscompras").style.backgroundColor="#ccc";
		document.getElementById("btnAddDetalleCompra").style.backgroundColor="#ccc";
		document.getElementById("btnAddPagosCompas").style.backgroundColor="#ccc";
		document.getElementById("btnEliminarCompas").style.backgroundColor="#ccc";
	document.getElementById("btnAbmCompra").value = "Guardar Datos"
					document.getElementById("btnAbmCompra").style.display = "none"
	idFkProductocompra = ""
	seleccionarLocalUSer()
	buscarnrodecompras()
}
function buscarnrodecompras() {
	document.getElementById("inptNrocompra").value = "..."
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscarnro"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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
			document.getElementById("inptNrocompra").value = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("inptNrocompra").value = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {		
					document.getElementById("inptNrocompra").value = datos[2]
					
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
VISTA COMPRA
*/
function vercerrarvistacompras(d){
		

	if (d == "1") {
			
		$("div[id=divVistaCompras]").fadeIn(250)	
		document.getElementById("tdEfectoVistaCompras").className="magictime slideLeftReturn"
	
	} else {
		//$("div[id=divVistaCompras]").fadeOut(250)
		document.getElementById("tdEfectoVistaCompras").className="magictime slideRight"
$("div[id=divVistaCompras]").fadeOut(500);	
	}
}
function buscarvistacompras() {
var buscar = document.getElementById('inptBuscarVistaCompras').value
var local = document.getElementById('inputSelectLocalVistaCompra').value
		 document.getElementById("table_vista_compras").innerHTML=paginacargando		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": buscar,
			"local": local,
			"funt": "buscarvista"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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
			document.getElementById("table_vista_compras").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_compras").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];				
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_vista_compras").innerHTML = datos_buscados
				}				
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function obtenerdatosacompravista(datostr) {
	document.getElementById('inptNrocompra').value = $(datostr).children('td[id="td_datos_1"]').html()
	document.getElementById('inpCodCompra').value = $(datostr).children('td[id="td_datos_1"]').html()
	document.getElementById('inptFechaCompra').value = $(datostr).children('td[id="td_datos_2"]').html()
	document.getElementById('inptProveedorCompra').value = $(datostr).children('td[id="td_datos_3"]').html()
	document.getElementById('inptlocalCompra').value = $(datostr).children('td[id="td_datos_11"]').html()
	  document.getElementById('inptlocalCompra').disabled=true
	document.getElementById('inptDescuentocompra').value = $(datostr).children('td[id="td_datos_8"]').html()
	document.getElementById('inptPagadocompra1').value = $(datostr).children('td[id="td_datos_9"]').html()
	document.getElementById('inptPagadocompra2').value = $(datostr).children('td[id="td_datos_10"]').html()	
	document.getElementById('inptPagosRealizadoscompra').value = $(datostr).children('td[id="td_datos_12"]').html()	
	codProveedorCompra = $(datostr).children('td[id="td_datos_6"]').html()
	idAbmCompra = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById("btnAbmCompra").value = "Editar Datos"
	document.getElementById("btnAbmCompra").style.display = ""
	buscardetallescompra()
	document.getElementById("divVistaCompras").style.display = "none";
	}
/*
CUENTAS A COBRAR 
*/
var cuotaNro = "";
var montoapagarcuota = "";
var iniciopagocuota = "";
var MetodoPago = "";
var deudaActual = "";
var idFkVenta = "";
function verCerrarCuentasACobrar(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divCuentasAcobrar").style.display==""){
		if(controldebusquedadInformeCuentaCobrar==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
		document.getElementById("tdEfectoCuentasACobrar").className="magictime vanishOut"
	$("div[id=divCuentasAcobrar]").fadeOut(500);	
		document.getElementById("divMinimizadoCuentasCobrar1").style.display="none"	
		document.getElementById("divMinimizadoCuentasCobrar2").style.display="none"
		limpiarCamposCuentasAcobrar()
	}else{		
	if(controlacceso("VERCUENTASACOBRAR","accion")==false){return;}		
		document.getElementById("divCuentasAcobrar").style.display=""
		document.getElementById("tdEfectoCuentasACobrar").className="magictime slideDownReturn"

		
	}
}
function minimizarcuentascobrar(){
document.getElementById("tdEfectoCuentasACobrar").className="magictime slideDown"
	$("div[id=divCuentasAcobrar]").fadeOut(500);	
document.getElementById("divMinimizadoCuentasCobrar1").style.display=""	
document.getElementById("divMinimizadoCuentasCobrar2").style.display=""	
}
function vercerrarfiltroscuentasacobrar(d){
	if(d=="1"){
		document.getElementById("divFiltrosCuentasACobrar").style.display=""
	}else{
		document.getElementById("divFiltrosCuentasACobrar").style.display="none"
	}
}
function checkfiltrosCuentasACobrar(d){
	if(d=="1"){
	document.getElementById('checkfiltrosCuentasACobrar1').checked=true
	document.getElementById('checkfiltrosCuentasACobrar2').checked=false
	document.getElementById('checkfiltrosCuentasACobrar3').checked=false
	document.getElementById('checkfiltrosCuentasACobrar4').checked=false	
	document.getElementById("inptBuscarCuentasAcobrarF1").value="";
	document.getElementById("inptBuscarCuentasAcobrarF2").value="";

	}
	if(d=="2"){		
	document.getElementById('checkfiltrosCuentasACobrar1').checked=false
	document.getElementById('checkfiltrosCuentasACobrar2').checked=true
	document.getElementById('checkfiltrosCuentasACobrar3').checked=false
	document.getElementById('checkfiltrosCuentasACobrar4').checked=false
    	
		var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarCuentasAcobrarF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarCuentasAcobrarF2').value = f.getFullYear() + "-" + mes + "-" + dia;
	}
	if(d=="3"){		
	document.getElementById('checkfiltrosCuentasACobrar1').checked=false
	document.getElementById('checkfiltrosCuentasACobrar2').checked=false
	document.getElementById('checkfiltrosCuentasACobrar3').checked=true
	document.getElementById('checkfiltrosCuentasACobrar4').checked=false
		var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}

	document.getElementById('inptBuscarCuentasAcobrarF1').value = f.getFullYear() + "-" + mes + "-" + dia;
	document.getElementById("inptBuscarCuentasAcobrarF2").value="";
	}
	if(d=="4"){		
	document.getElementById('checkfiltrosCuentasACobrar1').checked=false
	document.getElementById('checkfiltrosCuentasACobrar2').checked=false
	document.getElementById('checkfiltrosCuentasACobrar3').checked=false
	document.getElementById('checkfiltrosCuentasACobrar4').checked=true
			var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}

	document.getElementById('inptBuscarCuentasAcobrarF1').value = f.getFullYear() + "-" + mes + "-" + dia;
	document.getElementById("inptBuscarCuentasAcobrarF2").value="";
	}
}
function limpiarCamposCuentasAcobrar(){
	if(controldebusquedadInformeCuentaCobrar==true){return}
	document.getElementById("inptRegistroSeleccCuentasAcobrar").value = "";
	document.getElementById("inptRegistroRegistrocargadoCuentaAcobrar").value = "";
	document.getElementById("inptRegistroNroHistorialTotalADeudad").value = "";
	document.getElementById("inptRegistroHistorialTotalACobrar").value = "";
	document.getElementById("inptBuscarCuentasAcobrarF1").value = "";
	document.getElementById("inptBuscarCuentasAcobrarF2").value = "";
	document.getElementById("inptBuscarCuentasCobrar1").value = "";
	document.getElementById("inptBuscarCuentasCobrar2").value = "";
	document.getElementById("inptBuscarCuentasCobrar3").value = "";
	document.getElementById("inptBuscarCuentasCobrar5").value = "";
	document.getElementById("inptBuscarCuentasCobrar6").value = "";
	document.getElementById("btnCuentasCobrar1").style.backgroundColor = "#ccc";
	document.getElementById("btnCuentasCobrar2").style.backgroundColor = "#ccc";
	document.getElementById("table_cuentas_a_cobrar").innerHTML=""
	  document.getElementById("tbProcessInformeCuentaACobrar").style.display='none'
}
function verCerrarVentanasHistorialCuenta(d){
	document.getElementById("btnCuentasCobrar1").style=""
	document.getElementById("btnCuentasCobrar2").style=""
	document.getElementById("divCuentaACobrar1").style.display="none"
	document.getElementById("divCuentaACobrar2").style.display="none"
	if(d=="1"){
		document.getElementById("btnCuentasCobrar1").style="background-color: rgb(255, 152, 0); color: rgb(255, 255, 255);"
		document.getElementById("divCuentaACobrar1").style.display=""
		if(document.getElementById("table_cuentas_a_cobrar").innerHTML==""){
			buscarcuentaacobrar()
		}
	}
	if(d=="2"){
		document.getElementById("btnCuentasCobrar2").style="background-color: rgb(255, 152, 0); color: rgb(255, 255, 255);"
		document.getElementById("divCuentaACobrar2").style.display=""
		if(document.getElementById("table_cuentas_a_cobrar_detallada").innerHTML==""){
			buscarcuentaacobrardetallada()
		}
	}
}
var registrocargadoinformecuentasacobrar="";
var totalregistroinformecuentacobrar="";
var controldebusquedadInformeCuentaCobrar=false
function cancelarInformeCuentaACobrar(){
	controldebusquedadInformeCuentaCobrar=false
	document.getElementById("divProgressInformeCuentaACobrar").style.backgroundColor='#ff5722'
}
function buscarcuentaacobrar() {
if(controlacceso("VERCUENTASACOBRAR","accion")==false){return;}		
	
	var fecha1 = document.getElementById("inptBuscarCuentasAcobrarF1").value
	var fecha2 = document.getElementById("inptBuscarCuentasAcobrarF2").value
	var filtro=""
	if(document.getElementById('checkfiltrosCuentasACobrar2').checked==true){
		var filtro="1"
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA DE FIN", "#")
			return
		}
	}
	if(document.getElementById('checkfiltrosCuentasACobrar1').checked==true){
	var fecha1 =""
	var fecha2 = ""
		
	}
	if(document.getElementById('checkfiltrosCuentasACobrar3').checked==true){
		var filtro="3"
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA", "#")
			return
		}
	}
	if(document.getElementById('checkfiltrosCuentasACobrar4').checked==true){
		var filtro="4"
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA", "#")
			return
		}
	}
	
	var cliente = document.getElementById("inptBuscarCuentasCobrar1").value
	var documento = document.getElementById("inptBuscarCuentasCobrar2").value
	var telefono = document.getElementById("inptBuscarCuentasCobrar3").value
	var filtrofecha = document.getElementById("inptBuscarCuentasCobrar5").value
	var codlocal = document.getElementById("inptBuscarCuentasCobrar6").value
	
	if(controldebusquedadInformeCuentaCobrar==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
	controldebusquedadInformeCuentaCobrar=true
    document.getElementById("tbProcessInformeCuentaACobrar").style.display='none'
     document.getElementById("inptRegistroRegistrocargadoCuentaAcobrar").value = ""
	document.getElementById("inptRegistroNroHistorialTotalADeudad").value =  ""
	document.getElementById("inptRegistroHistorialTotalACobrar").value =  ""
	document.getElementById("table_cuentas_a_cobrar").innerHTML = paginacargando

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cliente": cliente,
		"documento": documento,
		"telefono": telefono,
		"producto": "",
		"filtrofecha": filtrofecha,
		"codlocal": codlocal,
		"filtro": filtro,
		"funt": "cuentasacobrar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			document.getElementById("table_cuentas_a_cobrar").innerHTML = ''
			controldebusquedadInformeCuentaCobrar=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_cuentas_a_cobrar").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					
					document.getElementById("table_cuentas_a_cobrar").innerHTML = datos_buscados
					 document.getElementById("inptRegistroRegistrocargadoCuentaAcobrar").value =datos[3]
	                document.getElementById("inptRegistroNroHistorialTotalADeudad").value =  datos[4]
	                document.getElementById("inptRegistroHistorialTotalACobrar").value =  datos[5]
					
						registrocargadoinformecuentasacobrar=datos[99];
					totalregistroinformecuentacobrar=datos[100];			
					
						 if(totalregistroinformecuentacobrar>registrocargadoinformecuentasacobrar){
						 	var porce=((registrocargadoinformecuentasacobrar*100)/totalregistroinformecuentacobrar).toFixed(0)
	                      document.getElementById("divProgressInformeCuentaACobrar").style.width=porce+"%"
						 document.getElementById("table_cuentas_a_cobrar").innerHTML += "<div id='table_mas_cuentas_a_cobrar'></div>"
						  buscarmascuentaacobrar();
					 }else{
						 controldebusquedadInformeCuentaCobrar=false
					 }
					}
					
			} catch (error) {
				controldebusquedadInformeCuentaCobrar=false
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarmascuentaacobrar(c) {
if(controlacceso("VERCUENTASACOBRAR","accion")==false){return;}		
	
	var fecha1 = document.getElementById("inptBuscarCuentasAcobrarF1").value
	var fecha2 = document.getElementById("inptBuscarCuentasAcobrarF2").value
	var filtro=""
	if(document.getElementById('checkfiltrosCuentasACobrar2').checked==true){
		var filtro="1"
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA DE FIN", "#")
			return
		}
	}
	if(document.getElementById('checkfiltrosCuentasACobrar1').checked==true){
	var fecha1 =""
	var fecha2 = ""
		
	}
	if(document.getElementById('checkfiltrosCuentasACobrar3').checked==true){
		var filtro="3"
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA", "#")
			return
		}
	}
	if(document.getElementById('checkfiltrosCuentasACobrar4').checked==true){
		var filtro="4"
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA", "#")
			return
		}
	}
	
	var cliente = document.getElementById("inptBuscarCuentasCobrar1").value
	var documento = document.getElementById("inptBuscarCuentasCobrar2").value
	var telefono = document.getElementById("inptBuscarCuentasCobrar3").value
	var filtrofecha = document.getElementById("inptBuscarCuentasCobrar5").value
	var codlocal = document.getElementById("inptBuscarCuentasCobrar6").value
	if(c=="1"){
		controldebusquedadInformeCuentaCobrar=true
	}
	if(controldebusquedadInformeCuentaCobrar==false){
	return
}
	controldebusquedadInformeCuentaCobrar=true
    document.getElementById("tbProcessInformeCuentaACobrar").style.display=''
	document.getElementById("divProgressInformeCuentaACobrar").style.backgroundColor=''
	var totaldeuda=document.getElementById("inptRegistroNroHistorialTotalADeudad").value
	var totalcobrar=document.getElementById("inptRegistroHistorialTotalACobrar").value
	document.getElementById("table_mas_cuentas_a_cobrar").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cliente": cliente,
		"documento": documento,
		"telefono": telefono,
		"producto": "",
		"filtrofecha": filtrofecha,
		"codlocal": codlocal,
		"filtro": filtro,
		"totaldeuda": totaldeuda,
		"totalcobrar": totalcobrar,
		"registrocargado": registrocargadoinformecuentasacobrar,
		"funt": "mascuentasacobrar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			document.getElementById("table_mas_cuentas_a_cobrar").innerHTML = ''
			document.getElementById("divProgressInformeCuentaACobrar").style.backgroundColor='#ff5722'
			controldebusquedadInformeCuentaCobrar=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_mas_cuentas_a_cobrar").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					
					document.getElementById("table_mas_cuentas_a_cobrar").innerHTML = datos_buscados
					 document.getElementById("inptRegistroRegistrocargadoCuentaAcobrar").value =datos[3]
	                document.getElementById("inptRegistroNroHistorialTotalADeudad").value =  datos[4]
	                document.getElementById("inptRegistroHistorialTotalACobrar").value =  datos[5]
						registrocargadoinformecuentasacobrar=datos[99];
									
						 if(totalregistroinformecuentacobrar>registrocargadoinformecuentasacobrar){
						 	var porce=((registrocargadoinformecuentasacobrar*100)/totalregistroinformecuentacobrar).toFixed(0)
	                      document.getElementById("divProgressInformeCuentaACobrar").style.width=porce+"%"
						 document.getElementById("table_mas_cuentas_a_cobrar").innerHTML += "<div id='table_mas_cuentas_a_cobrar'></div>"
						 document.getElementById("table_mas_cuentas_a_cobrar").id=""
						  buscarmascuentaacobrar();
					 }else{
						 document.getElementById("tbProcessInformeCuentaACobrar").style.display='none'
						 controldebusquedadInformeCuentaCobrar=false
					 }
					}
			} catch (error) {
				document.getElementById("divProgressInformeCuentaACobrar").style.backgroundColor='#ff5722'
				controldebusquedadInformeCuentaCobrar=false
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function obtenerdatoscuentaacobrar(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	cobradorcargarpagos = $(datostr).children('td[id="td_datos_9"]').html();
	nrofacturaaeliminar = $(datostr).children('td[id="td_datos_2"]').html();
	idFkVenta = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById("inptCobradorCargarPago").value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById("inptDiasAtrazadoCargarPago").value = $(datostr).children('td[id="td_datos_10"]').html();
	document.getElementById("inptRegistroSeleccCuentasAcobrar").value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById("inptTotalVenta").value = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById("inptTotalVenta2").innerHTML = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById("inptMontoCargaPago").value = $(datostr).children('td[id="td_datos_22"]').html();
	document.getElementById("inptSubtotalPago").value = $(datostr).children('td[id="td_datos_21"]').html();
	document.getElementById("inptTotalDeudaPago").value = $(datostr).children('td[id="td_datos_22"]').html();
	document.getElementById("inptTotalinteresPago").value = $(datostr).children('td[id="td_datos_17"]').html();
	document.getElementById("inptCuotasAtrazadoCargarPago").value = $(datostr).children('td[id="td_datos_20"]').html();
	document.getElementById("inptMontoCuotaPago").value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById("inptDescuentoCargaPago").value = "0";
	document.getElementById("btnCuentasCobrar1").style.backgroundColor = "#2196F3";
	document.getElementById("btnCuentasCobrar2").style.backgroundColor = "#4CAF50";
}
function verCerrarCargarPagoDesdeCuentas(d) {
	if (d == "1") {		
			if (idFkVenta == "") {
			ver_vetana_informativa("FALTO INICIAR UNA VENTA", "#")
			return false;
		}
		if(controlacceso("VERPAGOSCREDITO","accion")==false){	   
	   //SIN PERMISO
	   return;
		}		
		
		document.getElementById("divAbmOpcionesPagos").style.display=""
		document.getElementById("tdEfectoOpcionesPagos").className="magictime slideLeftReturn"
		document.getElementById("tdOpcionesVolverAtrasPagos").style.display="none"
		document.getElementById("inpCodVentaPagos").value = document.getElementById("inpCodVenta").value
		document.getElementById("inptTotalVentaPagos").value = document.getElementById("inptTotalVenta").value
		document.getElementById("inptTotalVentaPagosb").value = ""
		buscarDatosOpcionesPagos()
		buscarcreditos()
		limpiarCamposCuentasAcobrar()
	} else {
	
	document.getElementById("tdEfectoOpcionesPagos").className="magictime slideRight"
$("div[id=divAbmOpcionesPagos]").fadeOut(500);	
	}
}
function irACargarPagodesdeCuentasACobrar() {
	if (document.getElementById("inptRegistroSeleccCuentasAcobrar").value == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	verCerrarCargarPago("1")
}
function irAventaDesdeCuentas()
{
	if(controlacceso("VERVENTA","accion")==false){return;}		
if(idFkVenta==""){
	ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	return
}
var buscador=idFkVenta;
document.getElementById("DivDatosVenta").innerHTML="";
   verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "buscardatosVenta")
	datos.append("buscar", buscador)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
try {
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {					
					
					var datosVenta=datos["2"];
	                if(datosVenta==""){
						ver_vetana_informativa("NO SE HA ENCONTRADO DATOS")
						return 
					}
					document.getElementById("DivDatosVenta").innerHTML=datosVenta
					var datostr=document.getElementById("datos_venta_"+buscador)
					document.getElementById('inptFechaVenta').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptClienteVenta').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptSeleccTipoVenta').value = $(datostr).children('td[id="td_datos_12"]').html();
	controltipoventa= $(datostr).children('td[id="td_datos_12"]').html();
	document.getElementById('inptVendedorVenta1').value = $(datostr).children('td[id="td_datos_15"]').html();
	document.getElementById('inptVendedorVenta2').value = $(datostr).children('td[id="td_datos_16"]').html();
	document.getElementById('inptCobradorVenta').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptCobradorCargarPago').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inpCodVenta').value = $(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('inptNroVenta').value = $(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('pNroFactuaCaja').innerHTML = $(datostr).children('td[id="td_datos_13"]').html();
		document.getElementById('inptSeleccPuntoExpedicionVenta').value = $(datostr).children('td[id="td_datos_33"]').html();	
	var puntoExpedicion=$("select[id=inptSeleccPuntoExpedicionVenta]").children(":selected").text() 
	if(puntoExpedicion==""){						
	document.getElementById("pNroFactuaCaja").innerHTML = "*"+$(datostr).children('td[id="td_datos_13"]').html()+"*";
	}else{
	document.getElementById("pNroFactuaCaja").innerHTML = "*"+puntoExpedicion+"-"+$(datostr).children('td[id="td_datos_13"]').html()+"*";
	}
	document.getElementById('inptComisionVentaCobrador').value = $(datostr).children('td[id="td_datos_22"]').html();
	document.getElementById('inptlocalVenta').value = $(datostr).children('td[id="td_datos_23"]').html();
	document.getElementById('inptGaranteVenta').value = $(datostr).children('td[id="td_datos_31"]').html();
	document.getElementById('inptSeleccTipoComprobanteVenta').value = $(datostr).children('td[id="td_datos_32"]').html();
		if(document.getElementById('inptSeleccTipoComprobanteVenta').value=="FACTURA"){
		document.getElementById("btnImprimirticket").style.display=""
					 document.getElementById("btnImprimirFactura").style.display=""
					 document.getElementById("btnImprimirPagare").style.display=""
	}else{
		document.getElementById("btnImprimirticket").style.display=""
					 document.getElementById("btnImprimirFactura").style.display="none"
					 document.getElementById("btnImprimirPagare").style.display=""
	}

	idGaranteFk = $(datostr).children('td[id="td_datos_30"]').html();
	idFkVendedor1 = $(datostr).children('td[id="td_datos_3"]').html();
	idFkVendedor2 = $(datostr).children('td[id="td_datos_14"]').html();
	idFkCliente = $(datostr).children('td[id="td_datos_10"]').html();
	idFkCobrador = $(datostr).children('td[id="td_datos_11"]').html();
	cobradorcargarpagos = $(datostr).children('td[id="td_datos_11"]').html();
	idabmVenta = $(datostr).children('td[id="td_datos_8"]').html();
	idFkVenta = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inpCodVenta').disabled = true
	document.getElementById('inpCodVenta').className = "inputTextDisable"
	document.getElementById('btnAbmVenta').style.display = ""
	document.getElementById('btnAbmVenta').value = "Editar datos"
	 document.getElementById("divAbmVenta").style.display="";
   buscardetallesventa()
   SeleccTipoComprobanteVenta();
  document.getElementById("btnMasInfoClienteVenta").style.display='none'
  document.getElementById("btnNuevoClienteVenta").style.display=''
  document.getElementById("tdImprimirVenta").style.display=''
   document.getElementById("divAbmVenta").style.display=""
	limpiarCamposCuentasAcobrar()
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
INFORME CUENTAS A COBRAR
*/
function verCerrarCuentasACobrarInforme(d){
	document.getElementById("divSegundoPlano").style.display="none";
if(document.getElementById("divCuentasACobrarDetalles").style.display==""){
	if(controldebusquedadCuentaCobrar==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
		document.getElementById("divMinimizadoInformeGeneralCuentas").style.display="none"
document.getElementById("tdEfectoCuentasACobrarDetalles").className="magictime vanishOut"
	$("div[id=divCuentasACobrarDetalles]").fadeOut(500);	
		limpiarCamposCuentasAcobrarinforme()
	}else{		
	if(controlacceso("VERINFORMECUENTAGENERAL","accion")==false){return;}		
		document.getElementById("divCuentasACobrarDetalles").style.display=""
 document.getElementById("tdEfectoCuentasACobrarDetalles").className="magictime slideDownReturn"
	
	}	
}
function minimizarCuestasCobrarDetalles(){
	document.getElementById("divMinimizadoInformeGeneralCuentas").style.display=""
document.getElementById("tdEfectoCuentasACobrarDetalles").className="magictime slideDown"
	$("div[id=divCuentasACobrarDetalles]").fadeOut(500);	
}
function limpiarCamposCuentasAcobrarinforme(){
	if(controldebusquedadCuentaCobrar==true){
	return
}
	document.getElementById("table_cuentas_a_cobrar_informe").innerHTML = "";
	document.getElementById("inptBuscarCuentasAcobrarF1informe").value = "";
	document.getElementById("inptBuscarCuentasAcobrarF2informe").value = "";
	document.getElementById("inputSelectZonaInfCuentasAcobrarinforme").value = "";
	document.getElementById("inptlocalCuentasAcobrainforme").value = "";
	document.getElementById("inptTipoCuentasAcobrainforme").value = "";
	document.getElementById("inptRegistroNroHistorialCuentaAcobrarinforme").value = "";
	document.getElementById("inptRegistroNroHistorialTotalACobrarinforme").value = "";
	document.getElementById("tbProcessCuentasCobrar").style.display = "none";
}
var registrocargadocuentascobrar="";
var totalregistrocuentacobrar="";
var controldebusquedadCuentaCobrar=false
function cancelarCuentaCobrar(){
	controldebusquedadCuentaCobrar=false
	document.getElementById("divProgressCuentaCobra").style.backgroundColor='#ff5722'
}
function buscarcuentaacobrarinforme() {
		if(controlacceso("VERINFORMECUENTAGENERAL","accion")==false){return;}
	var fecha1 = document.getElementById("inptBuscarCuentasAcobrarF1informe").value
	var fecha2 = document.getElementById("inptBuscarCuentasAcobrarF2informe").value
	var zona = document.getElementById("inputSelectZonaInfCuentasAcobrarinforme").value
	var cod_local = document.getElementById("inptlocalCuentasAcobrainforme").value
	var tipo = document.getElementById("inptTipoCuentasAcobrainforme").value

		if (fecha1 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA DE FIN", "#")
			return
		}
	
			if(controldebusquedadCuentaCobrar==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadCuentaCobrar=true
	document.getElementById("table_cuentas_a_cobrar_informe").innerHTML = paginacargando
	document.getElementById("inptRegistroNroHistorialCuentaAcobrarinforme").value = "..."
	document.getElementById("inptRegistroNroHistorialTotalACobrarinforme").value = "..."
	document.getElementById("tbProcessCuentasCobrar").style.display="none"
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"zona": zona,
		"cod_local": cod_local,
		"tipo": tipo,
		"funt": "cuentasacobrardetallado"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			document.getElementById("table_cuentas_a_cobrar_informe").innerHTML = ''
			document.getElementById("inptRegistroNroHistorialCuentaAcobrarinforme").value = ""
	document.getElementById("inptRegistroNroHistorialTotalACobrarinforme").value = ""
	controldebusquedadCuentaCobrar=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_cuentas_a_cobrar_informe").innerHTML = ''
			document.getElementById("inptRegistroNroHistorialCuentaAcobrarinforme").value = ""
	document.getElementById("inptRegistroNroHistorialTotalACobrarinforme").value = ""
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("inptRegistroNroHistorialCuentaAcobrarinforme").value = datos[3]
	document.getElementById("inptRegistroNroHistorialTotalACobrarinforme").value = datos[4]
					document.getElementById("table_cuentas_a_cobrar_informe").innerHTML = datos_buscados
					
					    	registrocargadocuentascobrar=datos[99];
					totalregistrocuentacobrar=datos[100];

						 if(totalregistrocuentacobrar>registrocargadocuentascobrar){
						 	var porce=((registrocargadocuentascobrar*100)/totalregistrocuentacobrar).toFixed(0)
	document.getElementById("divProgressCuentaCobra").style.width=porce+"%"
						 document.getElementById("table_cuentas_a_cobrar_informe").innerHTML += "<div id='table_mas_cuentas_a_cobrar_informe'></div>"
						  buscarmascuentaacobrarinforme();
					 }else{
						 controldebusquedadCuentaCobrar=false
					 }
					}
			} catch (error) {
				controldebusquedadCuentaCobrar=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarmascuentaacobrarinforme(c) {
		if(controlacceso("VERINFORMECUENTAGENERAL","accion")==false){return;}
	var fecha1 = document.getElementById("inptBuscarCuentasAcobrarF1informe").value
	var fecha2 = document.getElementById("inptBuscarCuentasAcobrarF2informe").value
	var zona = document.getElementById("inputSelectZonaInfCuentasAcobrarinforme").value
	var cod_local = document.getElementById("inptlocalCuentasAcobrainforme").value
	var tipo = document.getElementById("inptTipoCuentasAcobrainforme").value

		if (fecha1 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO INGRESAR LA FECHA DE FIN", "#")
			return
		}
	if(c=="1"){
		controldebusquedadCuentaCobrar=true
	}
			if(controldebusquedadCuentaCobrar==false){
	
	return
}
controldebusquedadCuentaCobrar=true
	document.getElementById("table_mas_cuentas_a_cobrar_informe").innerHTML = paginacargando
	var totalACobrar=document.getElementById("inptRegistroNroHistorialTotalACobrarinforme").value
	document.getElementById("tbProcessCuentasCobrar").style.display=""
	document.getElementById("divProgressCuentaCobra").style.backgroundColor=''
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"zona": zona,
		"cod_local": cod_local,
		"tipo": tipo,
		"registrocargado": registrocargadocuentascobrar,
		"totalACobrar": totalACobrar,
		"funt": "mascuentasacobrardetallado"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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
			document.getElementById("table_mas_cuentas_a_cobrar_informe").innerHTML = ''


	document.getElementById("divProgressCuentaCobra").style.backgroundColor='#ff5722'
	controldebusquedadCuentaCobrar=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_mas_cuentas_a_cobrar_informe").innerHTML = ''
			
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("inptRegistroNroHistorialCuentaAcobrarinforme").value = datos[3]
	document.getElementById("inptRegistroNroHistorialTotalACobrarinforme").value = datos[4]
					document.getElementById("table_mas_cuentas_a_cobrar_informe").innerHTML = datos_buscados
					
					    	registrocargadocuentascobrar=datos[99];
				

						 if(totalregistrocuentacobrar>registrocargadocuentascobrar){
						 	var porce=((registrocargadocuentascobrar*100)/totalregistrocuentacobrar).toFixed(0)
	document.getElementById("divProgressCuentaCobra").style.width=porce+"%"
						 document.getElementById("table_mas_cuentas_a_cobrar_informe").innerHTML += "<div id='table_mas_cuentas_a_cobrar_informe'></div>"
						 document.getElementById("table_mas_cuentas_a_cobrar_informe").id=""
						  buscarmascuentaacobrarinforme();
					 }else{
						 document.getElementById("tbProcessCuentasCobrar").style.display="none"
						 controldebusquedadCuentaCobrar=false
					 }
					}
			} catch (error) {
				document.getElementById("divProgressCuentaCobra").style.backgroundColor='#ff5722'
				controldebusquedadCuentaCobrar=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}


/*
HISTORIAL COMPRAS
*/
function verCerrarHistorialCompra(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divHistorialCompra").style.display==""){
		if(controldebusquedadHistorialCompra==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
limpiarcamposHistorialCompra()
		document.getElementById("divMinimizadoHistorialCompra").style.display="none";
    document.getElementById("tdEfectoHistorialCompra").className="magictime vanishOut"
	$("div[id=divHistorialCompra]").fadeOut(500);
	}else{		
	if(controlacceso("VERHISTORIALCOMPRA","accion")==false){	   
	   //SIN PERMISO
	   return;
		}
		
		document.getElementById("divHistorialCompra").style.display=""
     document.getElementById("tdEfectoHistorialCompra").className="magictime slideDownReturn"
	
	}
}
function minimizarHistorialCompra(){
	//document.getElementById("divHistorialCompra").style.display="none";
	document.getElementById("divMinimizadoHistorialCompra").style.display="";
   document.getElementById("tdEfectoHistorialCompra").className="magictime slideDown"
	$("div[id=divHistorialCompra]").fadeOut(500);	
}
function vercerrarfiltroshistorialcompra(d){
	if(d=="1"){
		document.getElementById("divFiltrosHistorialCompras").style.display=""
	}else{
		document.getElementById("divFiltrosHistorialCompras").style.display="none"
	}
}
function limpiarcamposHistorialCompra(){
	if(controldebusquedadHistorialCompra==true){
	return
}
document.getElementById('inptBuscarInfHistorialCompraF1').value=""
document.getElementById('inptBuscarInfHistorialCompraF2').value=""
document.getElementById('inptBuscarHistorialCompra1').value=""
document.getElementById('inptBuscarHistorialCompra2').value=""
document.getElementById('inptBuscarHistorialCompra3').value=""
document.getElementById('inptBuscarHistorialCompra4').value=""
document.getElementById('inptBuscarHistorialCompra5').value=""
document.getElementById("table_historial_compra").innerHTML = ""
document.getElementById("inptRegistroNroHistorialCompra").value = "";
document.getElementById("inptTotalHistorialCompra").value = "";
document.getElementById("inptDescHistorialCompra").value = "";
document.getElementById("inptTotalPendienteHistorialCompra").value = "";
document.getElementById("inptlTotalPagadoHistorialCompra").value = "";
document.getElementById("tbProcessHistorialCompra").style.display = "none";
elementocompra=""

}
function checkfiltroshistorialcompra(d){
	if(d=="1"){
	document.getElementById('inptCheckHistorialCompra1').checked=true
	document.getElementById('inptCheckHistorialCompra2').checked=false
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarInfHistorialCompraF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarInfHistorialCompraF2').value = f.getFullYear() + "-" + mes + "-" + dia;		
	}else{		
	document.getElementById('inptCheckHistorialCompra1').checked=false
	document.getElementById('inptCheckHistorialCompra2').checked=true
	document.getElementById('inptBuscarInfHistorialCompraF1').value=""
    document.getElementById('inptBuscarInfHistorialCompraF2').value=""
	}
}
var registrocargadohistorialcompra="";
var totalregistrohistorialcompra="";
var controldebusquedadHistorialCompra=false
function cancelarHistorialCompra(){
	controldebusquedadHistorialCompra=false
	document.getElementById("divProgressHistorialCompra").style.backgroundColor='#ff5722'
}
function buscarhistorialcompra() {
var fecha1=document.getElementById('inptBuscarInfHistorialCompraF1').value
var fecha2=document.getElementById('inptBuscarInfHistorialCompraF2').value
var nrocompra=document.getElementById('inptBuscarHistorialCompra1').value
var filtrofecha=document.getElementById('inptBuscarHistorialCompra2').value
var proveedor = document.getElementById('inptBuscarHistorialCompra3').value
var estadopago = document.getElementById('inptBuscarHistorialCompra4').value
var cod_local = document.getElementById('inptBuscarHistorialCompra5').value

if(document.getElementById('inptCheckHistorialCompra1').checked==true){
	
	if(fecha1==""){
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO","#")
	  return false;
		}
		if(fecha2==""){
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN","#")
	  return false;
		}
}
if(document.getElementById('inptCheckHistorialCompra1').checked==true){
	
    fecha1=""
	fecha2=""
		
}

if(controldebusquedadHistorialCompra==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadHistorialCompra=true

		 document.getElementById("tbProcessHistorialCompra").style.display="none"
		 document.getElementById("table_historial_compra").innerHTML=paginacargando
		 	
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"fecha1": fecha1,
			"fecha2": fecha2,
			"nrocompra": nrocompra,
			"filtrofecha": filtrofecha,
			"proveedor": proveedor,
			"estadopago": estadopago,
			"cod_local": cod_local,
			"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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
			document.getElementById("table_historial_compra").innerHTML = ''
			controldebusquedadHistorialCompra=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_compra").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
					Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					
					document.getElementById("table_historial_compra").innerHTML = datos_buscados
					document.getElementById("inptRegistroNroHistorialCompra").value = datos[3];
					document.getElementById("inptTotalHistorialCompra").value = datos[4];
					document.getElementById("inptDescHistorialCompra").value = datos[5];
					document.getElementById("inptTotalPendienteHistorialCompra").value = datos[6];
					document.getElementById("inptlTotalPagadoHistorialCompra").value = datos[7];
					
						registrocargadohistorialcompra=datos[99];
					totalregistrohistorialcompra=datos[100];
				
						 if(totalregistrohistorialcompra>registrocargadohistorialcompra){
						 	var porce=((registrocargadohistorialcompra*100)/totalregistrohistorialcompra).toFixed(0)
	document.getElementById("divProgressHistorialCompra").style.width=porce+"%"
						 document.getElementById("table_historial_compra").innerHTML += "<div id='table_historial_mas_compra'></div>"
						  buscarMashistorialcompra();
					 }else{
						 controldebusquedadHistorialCompra=false
					 }
					
				}
			} catch (error) {
				controldebusquedadHistorialCompra=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarMashistorialcompra(c) {
var fecha1=document.getElementById('inptBuscarInfHistorialCompraF1').value
var fecha2=document.getElementById('inptBuscarInfHistorialCompraF2').value
var nrocompra=document.getElementById('inptBuscarHistorialCompra1').value
var filtrofecha=document.getElementById('inptBuscarHistorialCompra2').value
var proveedor = document.getElementById('inptBuscarHistorialCompra3').value
var estadopago = document.getElementById('inptBuscarHistorialCompra4').value
var cod_local = document.getElementById('inptBuscarHistorialCompra5').value

if(document.getElementById('inptCheckHistorialCompra1').checked==true){
	
	if(fecha1==""){
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO","#")
	  return false;
		}
		if(fecha2==""){
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN","#")
	  return false;
		}
}
if(document.getElementById('inptCheckHistorialCompra1').checked==true){
	
    fecha1=""
	fecha2=""
		
}
if(c=="1"){
	controldebusquedadHistorialCompra=true
}
if(controldebusquedadHistorialCompra==false){
	return
}
controldebusquedadHistorialCompra=true

		 document.getElementById("tbProcessHistorialCompra").style.display=""
		 document.getElementById("table_historial_mas_compra").innerHTML=paginacargando
		 	document.getElementById("divProgressHistorialCompra").style.backgroundColor=''
			var totalCompra=document.getElementById("inptTotalHistorialCompra").value;
			var totalDescuento=document.getElementById("inptDescHistorialCompra").value;
			var totalPendiente=document.getElementById("inptTotalPendienteHistorialCompra").value
			var totalPagado=document.getElementById("inptlTotalPagadoHistorialCompra").value
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"fecha1": fecha1,
			"fecha2": fecha2,
			"nrocompra": nrocompra,
			"filtrofecha": filtrofecha,
			"proveedor": proveedor,
			"estadopago": estadopago,
			"cod_local": cod_local,
			"registrocargado": registrocargadohistorialcompra,
			"totalCompra": totalCompra,
			"totalDescuento": totalDescuento,
			"totalPendiente": totalPendiente,
			"totalPagado": totalPagado,
			"funt": "buscarmas"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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
			document.getElementById("table_historial_mas_compra").innerHTML = ''
			document.getElementById("divProgressHistorialCompra").style.backgroundColor='#ff5722'
			controldebusquedadHistorialCompra=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_mas_compra").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
					Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					
					document.getElementById("table_historial_mas_compra").innerHTML = datos_buscados
					document.getElementById("inptRegistroNroHistorialCompra").value = datos[3];
					document.getElementById("inptTotalHistorialCompra").value = datos[4];
					document.getElementById("inptDescHistorialCompra").value = datos[5];
					document.getElementById("inptTotalPendienteHistorialCompra").value = datos[6];
					document.getElementById("inptlTotalPagadoHistorialCompra").value = datos[7];
					
						registrocargadohistorialcompra=datos[99];
					
				
						 if(totalregistrohistorialcompra>registrocargadohistorialcompra){
						 	var porce=((registrocargadohistorialcompra*100)/totalregistrohistorialcompra).toFixed(0)
	document.getElementById("divProgressHistorialCompra").style.width=porce+"%"
						 document.getElementById("table_historial_mas_compra").innerHTML += "<div id='table_historial_mas_compra'></div>"
						 document.getElementById("table_historial_mas_compra").id=""
						  buscarMashistorialcompra();
					 }else{
						 document.getElementById("tbProcessHistorialCompra").style.display="none"
						 controldebusquedadHistorialCompra=false
					 }
					
				}
			} catch (error) {
				document.getElementById("divProgressHistorialCompra").style.backgroundColor='#ff5722'
				controldebusquedadHistorialCompra=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}


function verCerrarVentanasHistorialCompra(d){
	document.getElementById("btnHistorialCompra1").style=''
	document.getElementById("btnHistorialCompra2").style=''
	document.getElementById("btnHistorialCompra3").style=''
	document.getElementById("divHistorialCompra1").style.display='none'
	document.getElementById("divHistorialCompra2").style.display='none'
	document.getElementById("divHistorialCompra3").style.display='none'	
	if(d=="1"){
		document.getElementById("btnHistorialCompra1").style='background-color:#FF9800;color:#fff'
		document.getElementById("divHistorialCompra1").style.display=''
	}	
	if(d=="2"){
			if (elementocompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		verCerrarVentanasHistorialCompra("1")
		return false;
	}	
		 buscarDetallesHistorialCompra()
		 	document.getElementById("btnHistorialCompra2").style='background-color:#FF9800;color:#fff'
		document.getElementById("divHistorialCompra2").style.display=''
	}
	if(d=="3"){
			if (elementocompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		verCerrarVentanasHistorialCompra("1")
		return false;
	}	
		 buscarPagosHistorialCompra()
		 	document.getElementById("btnHistorialCompra3").style='background-color:#FF9800;color:#fff'
		document.getElementById("divHistorialCompra3").style.display=''
	}	
}
function buscarDetallesHistorialCompra() {	
			if (elementocompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	var codCompra = $(elementocompra).children('td[id="td_datos_5"]').html();
	document.getElementById("table_detalles_historial_compra").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": codCompra,
		"funt": "detalleenhistorial"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallecompra.php",
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
			document.getElementById("table_detalles_historial_compra").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_detalles_historial_compra").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_detalles_historial_compra").innerHTML = datos_buscados
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function buscarPagosHistorialCompra() { 
	var codCompra = $(elementocompra).children('td[id="td_datos_5"]').html();
		 document.getElementById("table_pagos_historial_compra").innerHTML=paginacargando
		 	document.getElementById("inptTotalRegistroPagoCompraHist").value =""
		 	document.getElementById("inptTotalPagoCompraHist").value =""
		 	document.getElementById("inptTotalPagodoPagoCompraHist").value =""
		 	document.getElementById("inptTotalPendientePagoCompraHist").value =""
				obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"buscar": codCompra,
			"funt": "buscarpagoscomprahistorial"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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
			document.getElementById("table_pagos_historial_compra").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_pagos_historial_compra").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_pagos_historial_compra").innerHTML = datos_buscados
						document.getElementById("inptTotalRegistroPagoCompraHist").value =datos[3];
		 	        document.getElementById("inptTotalPagoCompraHist").value  =datos[4];
		 	        document.getElementById("inptTotalPagodoPagoCompraHist").value =datos[5];
		 	         document.getElementById("inptTotalPendientePagoCompraHist").value =datos[6];
					 document.getElementById('btnAuditoriaPagosCompras').style.backgroundColor="#d5d3d3";
					 
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
			var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
var elementocompra = ""
function obtenerdatosacompra(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	elementocompra = datostr;
	document.getElementById('inptRegistroSeleccHistorialCompra').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccionadoCuentaApagar').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptUsuarioInsertadoPor').value=$(datostr).children('td[id="td_datos_100"]').html()
	document.getElementById('inptFechaInsertadoPor').value=$(datostr).children('td[id="td_datos_102"]').html()
	document.getElementById('inptUsuarioEditadoPor').value=$(datostr).children('td[id="td_datos_101"]').html()
	document.getElementById('inptFechaEditadoPor').value=$(datostr).children('td[id="td_datos_103"]').html()
	document.getElementById('btnAuditoriaCompras').style.backgroundColor="#673ab7";
}
function obtenerdatospagohistorial(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});

	document.getElementById('inptUsuarioInsertadoPor').value=$(datostr).children('td[id="td_datos_100"]').html()
	document.getElementById('inptFechaInsertadoPor').value=$(datostr).children('td[id="td_datos_102"]').html()
	document.getElementById('inptUsuarioEditadoPor').value=$(datostr).children('td[id="td_datos_101"]').html()
	document.getElementById('inptFechaEditadoPor').value=$(datostr).children('td[id="td_datos_103"]').html()
	document.getElementById('btnAuditoriaPagosCompras').style.backgroundColor="#673ab7";
}
var codProveedorComprainf = "";
/*
GARANTIAS Y CAMBIOS
*/

var elementoDevolucion = ""
var comisioncambio = ""
var codVentaCambio = ""
var codDetalleCambiio = ""
var idFkProductocompraCambio = ""//el selecc Para cambiar
var CodProductocompraCambio = ""//cod del producto
var cantidaCambio = ""
var MetodoPagoCambio = ""
function verCerrarGarantias(d) {
	if (d == "1") {
		if (elementoDevolucion == "") {
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
			return false;
		}
		
		var datos = elementoDevolucion;
		document.getElementById("inptProductoDevolucionGarantia").value = $(datos).children('td[id="td_datos_2"]').html();
		document.getElementById("inptCostoDevolucionGarantia").value = $(datos).children('td[id="td_datos_4"]').html();
		document.getElementById("inptNroVentaGarantia").value = $(datos).children('td[id="td_datos_12"]').html();
		document.getElementById("inptFechaGarantia").value = $(datos).children('td[id="td_datos_13"]').html();
		document.getElementById("inptTelefonoAvisoGarantia").value = $(datos).children('td[id="td_datos_14"]').html();
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
  document.getElementById("inptFechaRecibidoGarantia").value =fechaimpresion
		codDetalleCambiio = $(datos).children('td[id="td_datos_9"]').html();
		CodProductocompraCambio = $(datos).children('td[id="td_datos_1"]').html();
		codVentaCambio = $(datos).children('td[id="td_datos_10"]').html();
		cantidaCambio = $(datos).children('td[id="td_datos_5"]').html();
		
		document.getElementById("divGarantiaProductoDevolucion").style.display=""
 document.getElementById("tdEfectoGarantiaProductoDevolucion").className="magictime slideLeftReturn"
	} else {
document.getElementById("tdEfectoGarantiaProductoDevolucion").className="magictime slideRight"
		$("div[id=divGarantiaProductoDevolucion]").fadeOut(500)
	}
}
function verificargarantiaproducto() {
     var inptObservacionGarantia=document.getElementById("inptObservacionGarantia").value
     var inptFechaRecibidoGarantia=document.getElementById("inptFechaRecibidoGarantia").value
     var inptTelefonoAvisoGarantia=document.getElementById("inptTelefonoAvisoGarantia").value
	if (codDetalleCambiio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	if (CodProductocompraCambio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	if (inptObservacionGarantia == "") {
		ver_vetana_informativa("FALTO INGRESAR UNA OBSERVACIÓN ", "#")
		return false;
	}
	if (inptFechaRecibidoGarantia == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN FECHA ", "#")
		return false;
	}
	abmgarantia(inptTelefonoAvisoGarantia,inptObservacionGarantia,inptFechaRecibidoGarantia,codDetalleCambiio, CodProductocompraCambio, codVentaCambio, cantidaCambio, "NuevoGarantia")
}
function abmgarantia(telefonoaviso,observacion,fecharecibido,cod_detalle, cod_productoFK, cod_ventaFK, cantidaCambio, operacion) {
	
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", operacion)
	datos.append("cod_detalle", cod_detalle)
	datos.append("cod_productoFK", cod_productoFK)
	datos.append("cod_ventaFK", cod_ventaFK)
	datos.append("cantidaCambio", cantidaCambio)
	datos.append("observacion", observacion)
	datos.append("fecharecibido", fecharecibido)
	datos.append("telefonoaviso", telefonoaviso)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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

					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					ImprimirDivTicketGarantia()
					verCerrarGarantias("")
                   document.getElementById("inptProductoDevolucionGarantia").value = "";
	         	   document.getElementById("inptCostoDevolucionGarantia").value = "";
		           document.getElementById("inptNroVentaGarantia").value = "";
		           document.getElementById("inptFechaGarantia").value = ""; 
		           document.getElementById("inptObservacionGarantia").value = ""; 
		           document.getElementById("inptRegistroSeleccionadoDetalleVentaHistorial").value = ""; 
				   elementoDevolucion=""
				    buscarDetallesHistorialVenta()
				}
				

			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function verCerrarCambio(d) {
	if (d == "1") {
		if (elementoDevolucion == "") {
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
			return false;
		}
			
		var datos = elementoDevolucion;
		document.getElementById("inptProductoSeleccionadoCambio").value = $(datos).children('td[id="td_datos_2"]').html();		
		comisioncambio = $(datos).children('td[id="td_datos_8"]').html();
		codDetalleCambiio = $(datos).children('td[id="td_datos_9"]').html();
		codVentaCambio = $(datos).children('td[id="td_datos_10"]').html();
		cantidaCambio = $(datos).children('td[id="td_datos_5"]').html();
		CodProductocompraCambio = $(datos).children('td[id="td_datos_1"]').html();
		MetodoPagoCambio = $(datos).children('td[id="td_datos_11"]').html();
        limpiarCamposProductosCambios()
		document.getElementById("table_abm_detalle_Cambio").innerHTML="";
		document.getElementById("divCambiarProducto").style.display=""
 document.getElementById("tdEfectoCambiarProducto").className="magictime slideLeftReturn"
	} else {
 
document.getElementById("tdEfectoCambiarProducto").className="magictime slideRight"
		$("div[id=divCambiarProducto]").fadeOut(500)
	}
}
function seleccionarprecioscambio(datos) {
	document.getElementById("inptCostoCambio").value = datos.value
	calcular_total_venta_cambios();
}
function calcularTotalVentasCostoCambios(datos) {
	separadordemiles(datos)
	calcular_total_venta_cambios()
}
function calcular_total_venta_cambios() {
	var c = QuitarSeparadorMilValor(document.getElementById('inptCantCambio').value);
	var t = QuitarSeparadorMilValor(document.getElementById('inptCostoCambio').value);
	var d = QuitarSeparadorMilValor(document.getElementById('inptDescuentoCambio').value);
	if (isNaN(c)) {
		document.getElementById('inptCantCambio').value = 0;
		c = 0;
	}
	if (isNaN(d)) {
		document.getElementById('inptDescuentoCambio').value = 0;
		d = 0;
	}
	var c = parseFloat(c);
	var t = parseFloat(t);
	document.getElementById('inpTotalCostoCambio').value = (t * c)-d;
	//separadordemiles(document.getElementById('inpt_interes_pago_venta'))
	separadordemiles(document.getElementById('inptCostoCambio'))
	separadordemiles(document.getElementById('inpTotalCostoCambio'))	
	if(d>0){
		var obs=$("select[id=inpTSeleccCostoCambio]").children(":selected").text() 
		document.getElementById("inptObservacionCambio").value=obs+", Descuento: "+d
	}else{
		var obs=$("select[id=inpTSeleccCostoCambio]").children(":selected").text() 
		document.getElementById("inptObservacionCambio").value=obs
	}
}
function anhadirProductoEnDetalleCambio(){	
	var inptProductoVenta = document.getElementById('inptProductoSeleccCambio').value
	var inptCantProductoVenta = document.getElementById('inptCantCambio').value
	var inpTotalCostoVenta = document.getElementById('inpTotalCostoCambio').value
	var inptCostoProductoVenta = document.getElementById('inptCostoCambio').value
	var inptObservacionDetalleVenta = document.getElementById('inptObservacionCambio').value
	var inptDescuentoProductoVenta = document.getElementById('inptDescuentoCambio').value
	if (idFkProductocompraCambio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO", "#")
		return false;
	}	
	var pagina="<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>"
+"<tr id='tbSelecRegistro' onclick='SeleccionarProductoCambioOffline(this)'  name='tdDetalleCambioOffline'>"
+"<td id='td_id_1' style='display:none'>"+idFkProductocompraCambio+"</td>"
+"<td  id='td_datos_1' style='width:20%;'>"+inptProductoVenta+"</td>"
+"<td  id='td_datos_6' style='display:none'>"+inptObservacionDetalleVenta+"</td>"
+"<td  id='td_datos_3' style='width:10%'>"+inptCostoProductoVenta+"</td>"
+"<td  id='td_datos_9' style='display:none'>"+inptDescuentoProductoVenta+"</td>"
+"<td  id='td_datos_4' style='width:5%'>"+inptCantProductoVenta+"</td>"
+"<td  id='td_datos_5' style='width:10%'>"+inpTotalCostoVenta+"</td>"
+"</tr>"
+"</table>"
document.getElementById("table_abm_detalle_Cambio").innerHTML+=pagina;
// var totalProductoCambios=0;
// $("tr[name=tdDetalleCambioOffline]").each(function(i, elementohtml){
// var total=$(elementohtml).children('td[id="td_datos_5"]').html();
// total=QuitarSeparadorMilValor(total)
// totalProductoCambios=Number(totalProductoCambios)+Number(total)
	   // });
	   // var totalVentaAnterio=document.getElementById("inptTotalVentaCambioAnterior").value
// totalVentaAnterio=QuitarSeparadorMilValor(totalVentaAnterio);
// var totalProducto=document.getElementById("inptCostoCambio1").value
// totalProducto=QuitarSeparadorMilValor(totalProducto);
// var TotalVentaActual=Number(totalVentaAnterio)-Number(totalProducto)
// var TotalVentaActual=Number(TotalVentaActual)+Number(totalProductoCambios)
// document.getElementById("inptTotalVentaCambioActual").value=separadordemilesnumero(TotalVentaActual);
limpiarCamposProductosCambios()
}
var elementoDetalleCambio="";
function SeleccionarProductoCambioOffline(datos) {	
		elementoDetalleCambio= datos;
		document.getElementById("inptNombreProductoDetalleOpcionCambio").value = $(datos).children('td[id="td_datos_1"]').html();
		document.getElementById("inptObsProductoDetalleOpcionCambio").value = $(datos).children('td[id="td_datos_6"]').html();
		vercerrarOpcionesDetallesCambios("1")	
}
function vercerrarOpcionesDetallesCambios(d){
	if(d=="1"){
		document.getElementById('divOpcionesDetallesCambios').style.display=""
	}else{
		document.getElementById('divOpcionesDetallesCambios').style.display="none"
	}
}
function eleminarDetallesCambios(d){
	$(elementoDetalleCambio).remove()
	vercerrarOpcionesDetallesCambios("2")
	// var totalProductoCambios=0;
// $("tr[name=tdDetalleCambioOffline]").each(function(i, elementohtml){
// var total=$(elementohtml).children('td[id="td_datos_5"]').html();
// total=QuitarSeparadorMilValor(total)
// totalProductoCambios=Number(totalProductoCambios)+Number(total)
	   // });	   
// var totalVentaAnterio=document.getElementById("inptTotalVentaCambioAnterior").value
// totalVentaAnterio=QuitarSeparadorMilValor(totalVentaAnterio);
// var totalProducto=document.getElementById("inptCostoCambio1").value
// totalProducto=QuitarSeparadorMilValor(totalProducto);
// var TotalVentaActual=Number(totalVentaAnterio)-Number(totalProducto)
// var TotalVentaActual=Number(TotalVentaActual)+Number(totalProductoCambios)	   
// document.getElementById("inptTotalVentaCambioActual").value=separadordemilesnumero(TotalVentaActual);
limpiarCamposProductosCambios()
	ver_vetana_informativa("DETALLE ELIMINADO", "#")	
}
function limpiarCamposProductosCambios(){
	document.getElementById('inptProductoSeleccCambio').value = ""
document.getElementById('inptCantCambio').value = ""
document.getElementById('inpTotalCostoCambio').value = ""
document.getElementById('inptCostoCambio').value = ""
document.getElementById('inptObservacionCambio').value = ""
document.getElementById('inptDescuentoCambio').value = ""
idFkProductocompraCambio = ""
}
function verificarcambioproducto() {
var controlDetalle=0;
$("tr[name=tdDetalleCambioOffline]").each(function(i, elementohtml){
controlDetalle=Number(controlDetalle)+Number(1)
	   });
if(controlDetalle==0){
	ver_vetana_informativa("FALTO AÑADIR PRODUCTOS", "#")
	return
}
	if (codDetalleCambiio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}   
   if (codVentaCambio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	if(controlacceso("INSERTARCAMBIOSYGARANTIAS","accion")==false){	   
	   //SIN PERMISO
	   return;
		}
   abmcambio(codDetalleCambiio, codVentaCambio,MetodoPagoCambio)
}
function abmcambio(cod_detalle, cod_ventaFK,MetodoPagoCambio) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();	
		var control=1;
	$("tr[name=tdDetalleCambioOffline]").each(function(i, elementohtml){	
	var idproducto=$(elementohtml).children('td[id="td_id_1"]').html();
    datos.append("cod_productoFK"+control, idproducto)	
	var cantidad=$(elementohtml).children('td[id="td_datos_4"]').html();
    datos.append("cantidad_detalle"+control, cantidad)
	var precio=$(elementohtml).children('td[id="td_datos_3"]').html();
    datos.append("precio_producto"+control, precio)	
	var subotal=$(elementohtml).children('td[id="td_datos_5"]').html();
    datos.append("subtotal"+control, subotal)	
	//var comision=$(elementohtml).children('td[id="td_datos_7"]').html();
    datos.append("comision"+control, 0)	
	var descuento=$(elementohtml).children('td[id="td_datos_8"]').html();
    datos.append("descuento"+control, descuento)	
	var detalleproducto=$(elementohtml).children('td[id="td_datos_6"]').html();
    datos.append("detalleproducto"+control, detalleproducto)
	control=control+1;	
	   });
	control=control-1;	
	if(control<0){
	ver_vetana_informativa("FALTO AÑADIR PRODUCTOS", "#")
	return
}	
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "cambio")
	datos.append("cod_detalle", cod_detalle)
	datos.append("cod_ventaFK", cod_ventaFK)
	datos.append("cantidaCambio", cantidaCambio)
	datos.append("CodProductocompraCambio", CodProductocompraCambio)
	datos.append("MetodoPagoCambio", MetodoPagoCambio)
	datos.append("TotalRegistro", control)
	datos.append("Local_FK", cod_localFKUSer)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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

			ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
			document.getElementById("divCambiarProducto").style.display="none"
			document.getElementById("inptTotalVentaRefinanciadoCambio").value=datos["2"];
			
	        document.getElementById("divRefinanciar").style.display=""
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function verificarrefinanciamiento() {
if(controlacceso("REFINANCIARCUOTARESTANTE","accion")==false){	   
	   //SIN PERMISO
	   return;
		}
	var inptPendienteVentaRefinanciadoCambio = document.getElementById('inptPendienteVentaRefinanciadoCambio').value
	var inputSelectMetodoCambio = document.getElementById('inputSelectMetodoCambio').value
	var inptCuotaNroCambio = document.getElementById('inptCuotaNroCambio').value
	var inptFechaVentaCambio = document.getElementById('inptFechaVentaCambio').value
	var inptMonotCambio = document.getElementById('inptMonotCambio').value
	var inptInteresVentaCambio = document.getElementById('inptInteresVentaCambio').value
	var inptDiasVentaCambio = document.getElementById('inptDiasVentaCambio').value
	if (codVentaCambio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN PRODUCTO", "#")
		return false;
	}
	if (inptPendienteVentaRefinanciadoCambio == "") {
		ver_vetana_informativa("FALTO INGRESAR EL TOTAL FINANCIADO", "#")
		return false;
	}
	if (inputSelectMetodoCambio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR EL METODO DEL PAGO", "#")
		return false;
	}
	if (inptCuotaNroCambio == "") {
		ver_vetana_informativa("FALTO INGRESAR EL NRO DE CUOTA", "#")
		return false;
	}
	if (inptFechaVentaCambio == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO PAGO", "#")
		return false;
	}
	if (inptInteresVentaCambio == "") {
		inptInteresVentaCambio=0
	}
	if (inptDiasVentaCambio == "") {
		inptDiasVentaCambio=0
	}
	abmrefinacimiento(inptInteresVentaCambio,inptDiasVentaCambio,inptMonotCambio,inptPendienteVentaRefinanciadoCambio, inputSelectMetodoCambio, inptCuotaNroCambio, inptFechaVentaCambio, codVentaCambio)
}
function abmrefinacimiento(dias,interes,Monto,total,  metodopago, nroCuota,  iniciopago, cod_venta) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "refinanciarencambio")
	datos.append("cod_venta", cod_venta)
	datos.append("metodopago", metodopago)
	datos.append("iniciopago", iniciopago)
	datos.append("nroCuota", nroCuota)
	datos.append("total", total)
	datos.append("Monto", Monto)
	datos.append("dias", dias)
	datos.append("interes", interes)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcreditos.php",
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

					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")


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
ABM SUELDOS
*/
function verCerrarAbmSueldo(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmSueldo").style.display==""){
		limpiarcamposSueldo()
		limpiarcamposbuscarsueldo()
		document.getElementById("divMinimizadoCargarSueldo").style.display="none"
		document.getElementById("tdEfectoAbmSueldo").className="magictime vanishOut"
	$("div[id=divAbmSueldo]").fadeOut(500);			
	}else{		
	if(controlacceso("VERCARGARSUELDO","accion")==false){return;}
		document.getElementById("divAbmSueldo").style.display=""
		 document.getElementById("tdEfectoAbmSueldo").className="magictime slideDownReturn"

	}
}
function limpiarcamposbuscarsueldo(){
	document.getElementById("inptBuscarSueldo").value=""
	document.getElementById("inptBuscarSueldoF1").value=""
	document.getElementById("inptBuscarSueldoF2").value=""	
	document.getElementById("inptTotalTotalSueldo").value=""
	document.getElementById("inptRegistroSeleccSueldo").value=""
	document.getElementById("table_abm_Sueldo").innerHTML=""
}
function minimizarsueldos(){
	document.getElementById("tdEfectoAbmSueldo").className="magictime slideDown"
	$("div[id=divAbmSueldo]").fadeOut(500);	
	document.getElementById("divMinimizadoCargarSueldo").style.display=""
}
function verCerrarVentanaAbmSueldo(d, l) {
	if (d == "1") {
		if (l == "1") {
			if(controlacceso("INSERTARCARGARSUELDO","accion")==false){return;}
			limpiarcamposSueldo()
		}
		$("div[id=divAbmSueldo2]").fadeIn(250)
		document.getElementById('divAbmSueldo1').style.display = "none"
	} else {
		$("div[id=divAbmSueldo1]").fadeIn(250)
		document.getElementById('divAbmSueldo2').style.display = "none"
	}
}
function verVentanaEditarSueldo() {
	if(controlacceso("EDITARCARGARSUELDO","accion")==false){return;}
	if (idAbmSueldo == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmSueldo("1", "2")
}
var idAbmSueldo = ""
var CodPersonaSueldo = "";
var TipoUserSueldo="";
function obtenerdatosabmSueldo(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptSeleccFuncionariosueldo').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccSueldo').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inpttotalrecaudadoSueldo').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptcomisonporc').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptMontoSueldo').value = $(datostr).children('td[id="td_datos_4"]').html();
	document.getElementById('inptFechaSueldo').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptSeleccTipoFuncionario').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptEstadoSueldo').value = $(datostr).children('td[id="td_datos_7"]').html();
	idAbmSueldo = $(datostr).children('td[id="td_id"]').html();
	CodPersonaSueldo = $(datostr).children('td[id="td_datos_8"]').html();
	TipoUserSueldo = $(datostr).children('td[id="td_datos_9"]').html();
document.getElementById('btnAbmSueldo').value = "Editar datos";
document.getElementById('btnEditarSueldos').style.backgroundColor="";
}
function verificarcamposSueldo() {
	var inpttotalrecaudadoSueldo = document.getElementById('inpttotalrecaudadoSueldo').value
	var inptcomisonporc = document.getElementById('inptcomisonporc').value
	var inptMontoSueldo = document.getElementById('inptMontoSueldo').value
	var inptFechaSueldo = document.getElementById('inptFechaSueldo').value
	var inptEstadoSueldo = document.getElementById('inptEstadoSueldo').value
	var inptSeleccTipoSueldo = document.getElementById('inptSeleccTipoSueldo').value
	
	if (inptMontoSueldo == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MONTO DEL SUELDO", "#")
		return false;
	}
	if (inptFechaSueldo == "") {
		ver_vetana_informativa("FALTO INGRESAR LA FECHA DEL SUELDO", "#")
		return false;
	}
	if (CodPersonaSueldo == "") {
		ver_vetana_informativa("FALTO INGRESAR SELECCIONAR EL FUNCIONARIO", "#")
		return false;
	}
	var accion = "";
	if (idAbmSueldo != "") {
		accion = "editar";
		if(controlacceso("EDITARCARGARSUELDO","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("INSERTARCARGARSUELDO","accion")==false){return;}
	}
	abmsueldo(inpttotalrecaudadoSueldo, inptcomisonporc, inptMontoSueldo, inptFechaSueldo, inptEstadoSueldo, CodPersonaSueldo,TipoUserSueldo, inptSeleccTipoSueldo, idAbmSueldo, accion);
}
function abmsueldo(totalrecaudado, comision, sueldo, fecha, estado, cod_persona, tipouser, tipo, idsueldo, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idsueldo", idsueldo)
	datos.append("comision", comision)
	datos.append("totalrecaudado", totalrecaudado)
	datos.append("sueldo", sueldo)
	datos.append("fecha", fecha)
	datos.append("cod_persona", cod_persona)
	datos.append("estado", estado)
	datos.append("tipo", tipo)
	datos.append("tipouser", tipouser)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmsueldo.php",
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
					limpiarcamposSueldo()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmSueldo = ""
					buscarabmSueldo()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function checkestadoSueldos(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarSueldo1').checked=true
		document.getElementById('inptSeleccEstadoBuscarSueldo2').checked=false	
	}else{
		
		document.getElementById('inptSeleccEstadoBuscarSueldo1').checked=false
		document.getElementById('inptSeleccEstadoBuscarSueldo2').checked=true
	}
}
function buscarabmSueldo() {
if(controlacceso("BUSCARCARGARSUELDO","accion")==false){return;}
	var fecha1 = document.getElementById('inptBuscarSueldoF1').value
	var fecha2 = document.getElementById('inptBuscarSueldoF2').value
	var buscar = document.getElementById('inptBuscarSueldo').value
	var estado = ""
	if(document.getElementById('inptSeleccEstadoBuscarSueldo1').checked==true){
		 estado = "Activo"
	}else{
		 estado = "Inactivo"
	}
	var tipo = document.getElementById('inptSeleccTipoBuscarSueldo').value
	document.getElementById("table_abm_Sueldo").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"estado": estado,
		"buscar": buscar,
		"tipo": tipo,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmsueldo.php",
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
			document.getElementById("table_abm_Sueldo").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_Sueldo").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				   Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_abm_Sueldo").innerHTML = datos_buscados
					document.getElementById("inptTotalTotalSueldo").value = datos[4];
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function limpiarcamposSueldo() {
	document.getElementById('inptSeleccFuncionariosueldo').value = "";
	document.getElementById('inptRegistroSeleccSueldo').value = "";
	document.getElementById('inpttotalrecaudadoSueldo').value = "";
	document.getElementById('inptcomisonporc').value = "";
	document.getElementById('inptMontoSueldo').value = "";
	document.getElementById('inptFechaSueldo').value = "";
	document.getElementById('inptSeleccTipoSueldo').value = "SUELDO";
	document.getElementById('inptEstadoSueldo').value = "Activo";
	document.getElementById('btnAbmSueldo').value = "Guardar datos";
	document.getElementById('btnEditarSueldos').style.backgroundColor="#b7b7b7";
	idAbmSueldo = "";
	CodPersonaSueldo = "";
	TipoUserSueldo = "";
}
var controlseleccvistaFuncionario = ""
function vercerrarvistafuncionarios(d, ventana) {
	if (d == "1") {
		$("div[id=divVistaFuncinario]").fadeIn(250)
		controlseleccvistaFuncionario = ventana
		buscarvistafuncionario();
	} else {
		$("div[id=divVistaFuncinario]").fadeOut(250)
	}
}
function buscarvistafuncionario() {
	var buscador = document.getElementById('inptBuscarVistaFuncionario').value
	var tipo = document.getElementById('inptSeleccTipoFuncionario').value
	document.getElementById("table_vista_funcionario").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"tipo": tipo,
		"funt": "buscarfuncionario"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmusuarios.php",
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
			document.getElementById("table_vista_funcionario").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_funcionario").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				  Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				   	var datos_buscados = datos[2];
					document.getElementById("table_vista_funcionario").innerHTML = datos_buscados
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function obtenerdatosvistafuncionario(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	if (controlseleccvistaFuncionario == "sueldo") {
		CodPersonaSueldo = $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptSeleccFuncionariosueldo').value = $(datostr).children('td[id="td_datos_1"]').html();
		if(document.getElementById("inptSeleccTipoFuncionario").value=="1"){
			TipoUserSueldo=1;
		}
		if(document.getElementById("inptSeleccTipoFuncionario").value=="2"){
			TipoUserSueldo=2;
		}
		
	}
	document.getElementById("divVistaFuncinario").style.display = "none"
}
/*
ABM GASTO
*/
function verCerrarAbmGasto(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divAbmGastos").style.display==""){
	document.getElementById("divMinimizadoEgresoIngreso").style.display="none"
     document.getElementById("tdEfectoAbmGasto").className="magictime vanishOut"
	$("div[id=divAbmGastos]").fadeOut(500);	
	limpiarcamposGasto()
	limpiarcamposbuscadoregresoingreso()
	}else{	
if(controlacceso("VERLISTADOEGRESOINGRESO","accion")==false){return;}			
		document.getElementById("divAbmGastos").style.display=""
       document.getElementById("tdEfectoAbmGasto").className="magictime slideDownReturn"
	
	}
}
function limpiarcamposbuscadoregresoingreso(){
	document.getElementById("inptBuscarIngresoEgreso1").value=""
	document.getElementById("inptBuscarIngresoEgreso2").value=""
	document.getElementById("inptBuscarGastoF1").value=""
	document.getElementById("inptBuscarGastoF2").value=""
	document.getElementById("inptRegistroNroGastos").value=""
	document.getElementById("inptTotalGasto").value=""
	document.getElementById("inptRegistroSeleccGasto").value=""
	document.getElementById("table_abm_gasto").innerHTML=""
}
function minimizarventanaingresoegreso(){
	document.getElementById("divMinimizadoEgresoIngreso").style.display=""
    document.getElementById("tdEfectoAbmGasto").className="magictime slideDown"
	$("div[id=divAbmGastos]").fadeOut(500);
}
function verCerrarVentanaAbmGasto(d, l) {
	if (d == "1") {
		if(idabmAperturacierrecaja==""){
			document.getElementById("divAbmGastos").style.display="none"
		   ver_vetana_informativa("FALTO INICIAR UNA CAJA", "#")
		   verCerrarVentanaAbmAperturaCierreCaja1()
		   return
	   }
		
		if (l == "1") {
			limpiarcamposGasto()
			if(controlacceso("INSERTARLISTADOEGRESOINGRESO","accion")==false){return;}	
		}
		$("div[id=divAbmGasto2]").fadeIn(250)
		document.getElementById('divAbmGasto1').style.display = "none"
	} else {
		$("div[id=divAbmGasto1]").fadeIn(250)
		document.getElementById('divAbmGasto2').style.display = "none"
	}
}
function verVentanaEditarGasto() {
		if(controlacceso("EDITARLISTADOEGRESOINGRESO","accion")==false){return;}	
		if(idabmAperturacierrecaja==""){
			document.getElementById("divAbmGastos").style.display="none"
		   ver_vetana_informativa("FALTO INICIAR UNA CAJA", "#")
		   verCerrarVentanaAbmAperturaCierreCaja1()
		   return
	   }
	if (idAbmGasto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return;
	}
	verCerrarVentanaAbmGasto("1", "2")
}
var idAbmGasto = ""
function obtenerdatosabmGasto(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	document.getElementById('inptMontoGasto').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccGasto').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptMotivoGasto').value = $(datostr).children('td[id="td_datos_2"]').html();
	document.getElementById('inptFechaGasto').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptEstadoGasto').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptlocalMisGastos').value = $(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptNroBoletaGasto').value = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptBancoGasto').value = $(datostr).children('td[id="td_datos_9"]').html();
	document.getElementById('inptCuentaGasto').value = $(datostr).children('td[id="td_datos_10"]').html();
	document.getElementById('inptTipoGasto').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('btnAbmGastos').value = "Editar datos";
	document.getElementById('btnEditarGastos').style.backgroundColor="";
	idAbmGasto = $(datostr).children('td[id="td_id"]').html();
}
function verificarcamposGasto() {
	var inptMontoGasto = document.getElementById('inptMontoGasto').value
	var inptMotivoGasto = document.getElementById('inptMotivoGasto').value
	var inptFechaGasto = document.getElementById('inptFechaGasto').value
	var inptEstadoGasto = document.getElementById('inptEstadoGasto').value
	var inptlocalMisGastos = document.getElementById('inptlocalMisGastos').value
	var inptTipoGasto = document.getElementById('inptTipoGasto').value
	var inptNroBoletaGasto = document.getElementById('inptNroBoletaGasto').value
	var inptBancoGasto = document.getElementById('inptBancoGasto').value
	var inptCuentaGasto = document.getElementById('inptCuentaGasto').value
	if (inptMontoGasto == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MONTO DEL GASTO", "#")
		return false;
	}
	if (inptMotivoGasto == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MOTIVO DEL GASTO", "#")
		return false;
	}
	if (inptFechaGasto == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DEL GASTO", "#")
		return false;
	}
	var accion = "";
	if (idAbmGasto != "") {
		accion = "editar";
		if(controlacceso("EDITARLISTADOEGRESOINGRESO","accion")==false){return;}	
	} else {
		if(controlacceso("INSERTARLISTADOEGRESOINGRESO","accion")==false){return;}	
		accion = "nuevo";
	}
	abmgastos(inptNroBoletaGasto, inptBancoGasto , inptCuentaGasto ,inptMontoGasto, inptMotivoGasto, inptFechaGasto, inptEstadoGasto, idAbmGasto, inptTipoGasto, inptlocalMisGastos, accion);
}
function abmgastos(nroboleta ,banco ,nrocuenta,monto, motivo, fecha, estado, idgastos, tipo, cod_local, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idgastos", idgastos)
	datos.append("monto", monto)
	datos.append("motivo", motivo)
	datos.append("fecha", fecha)
	datos.append("estado", estado)
	datos.append("tipo", tipo)
	datos.append("cod_local", cod_local)
	datos.append("codcaja", cajapredeterminada)
	datos.append("idaperturacierrecaja", idabmAperturacierrecaja)
	datos.append("nroboleta", nroboleta)
	datos.append("banco", banco)
	datos.append("nrocuenta", nrocuenta)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmgasto.php",
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
				   if(accion=="nuevo"){
						ImprimirTicketEgreso()
					}
					limpiarcamposGasto()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					idAbmGasto = ""
					buscarabmGasto()
					
				}				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function ImprimirTicketEgreso(){
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

pagina="<div  style='background-color:#fff;'>"
+"<center>"
+"<div class='divTicket' >"
+"<p class='pTituloTicket1' >BOLETA DE CONTROL</p>"
+"<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Fecha Imp.:</b></td>"
+"<td style=''>"+f.getFullYear()+"-"+mes+"-"+dia+" "+hora+":"+min+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Usuario :</b></td>"
+"<td style=''>"+ document.getElementById("ptituloUser2").innerHTML+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Local:</b></td>"
+"<td style=''>"+ $("select[id=inptlocalMisGastos]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<br>"
+"<br>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:60px'><b>Caja:</b></td>"
+"<td style=''>"+ $("select[id=inptcajaAperturaCierreCaja]").children(":selected").text() +"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:100px'><b>Tipo :</b></td>"
+"<td style=''>"+ document.getElementById("inptTipoGasto").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Monto :</b></td>"
+"<td style=''>"+document.getElementById("inptMontoGasto").value+" Gs.</td>"
+"</tr>"
+"</table>"
+"<br>"
+"<br>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Motivo :</b></td>"
+"<td style=''>"+document.getElementById("inptMotivoGasto").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Boleta Nro :</b></td>"
+"<td style=''>"+document.getElementById("inptNroBoletaGasto").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Banco :</b></td>"
+"<td style=''>"+document.getElementById("inptBancoGasto").value+"</td>"
+"</tr>"
+"</table>"
+"<table class='tableTicket'>"
+"<tr>"
+"<td style='width:110px'><b>Cuenta :</b></td>"
+"<td style=''>"+document.getElementById("inptCuentaGasto").value+"</td>"
+"</tr>"
+"</table>"
+"</center>"
+"</div>"


var ficha=pagina;
document.getElementById("DivImprimir").innerHTML=ficha;
   var documento= document.getElementById("DivImprimir").innerHTML;
     localStorage.setItem("reporte", documento);
	   localStorage.setItem("tipo", "ticket");
	 window.open("https://systemsrepository.com/GoodVentaByR/system/reportticket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
}

function checkestadoGastos(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoBuscarGasto1').checked=true
		document.getElementById('inptSeleccEstadoBuscarGasto2').checked=false	
	}else{
		
		document.getElementById('inptSeleccEstadoBuscarGasto1').checked=false
		document.getElementById('inptSeleccEstadoBuscarGasto2').checked=true
	}
}
function checkfiltroshistorialegresoingreso(d){
	if(d=="1"){
	document.getElementById('inptCheckingresoegreso1').checked=true
	document.getElementById('inptCheckingresoegreso2').checked=false	
     
	 	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarGastoF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarGastoF2').value = f.getFullYear() + "-" + mes + "-" + dia;
	 
	}else{		
	document.getElementById('inptCheckingresoegreso1').checked=false
	document.getElementById('inptCheckingresoegreso2').checked=true
	document.getElementById('inptBuscarGastoF1').value="";
      document.getElementById('inptBuscarGastoF2').value="";
	
	}
}
function buscarabmGasto() {
if(controlacceso("BUSCARLISTADOEGRESOINGRESO","accion")==false){return;}	
	var fecha1 = document.getElementById('inptBuscarGastoF1').value
	var fecha2 = document.getElementById('inptBuscarGastoF2').value
	var estado =""
	if(document.getElementById('inptSeleccEstadoBuscarGasto1').checked==true){
		estado="Activo"
	}else{
		estado="Inactivo"
	}
	var tipo = document.getElementById('inptSeleccTipoBuscarGasto').value
	var cod_local = document.getElementById('inptlocalMisGastosBusca').value
	var fecha = document.getElementById('inptBuscarIngresoEgreso1').value
	var usuario = document.getElementById('inptBuscarIngresoEgreso2').value
	
	document.getElementById("table_abm_gasto").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"estado": estado,
		"cod_local": cod_local,
		"tipo": tipo,
		"usuario": usuario,
		"fecha": fecha,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmgasto.php",
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
			document.getElementById("table_abm_gasto").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_gasto").innerHTML = ''
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
					document.getElementById("table_abm_gasto").innerHTML = datos_buscados
					document.getElementById("inptTotalGasto").value = datos[4];
					document.getElementById("inptRegistroNroGastos").value = datos[3];
					
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function limpiarcamposGasto() {
	document.getElementById('inptMontoGasto').value = "";
	document.getElementById('inptRegistroSeleccGasto').value = "";
	document.getElementById('inptMotivoGasto').value = "";
	document.getElementById('inptFechaGasto').value = "";
	document.getElementById('inptPersonalGasto').value = "";
	document.getElementById('inptNroBoletaGasto').value = "";
	document.getElementById('inptBancoGasto').value = "";
	document.getElementById('inptCuentaGasto').value = "";
	document.getElementById('btnEditarGastos').style.backgroundColor="#b7b7b7";
	document.getElementById('inptEstadoGasto').value = "Activo";
	document.getElementById('btnAbmGastos').value = "Guardar datos";
	idAbmGasto = "";
	seleccionarLocalUSer()
}

/*VISTA DE CAJA*/
function verCerrarVistaCajaApp(){
	
	
	if(document.getElementById("divVistaCajaApp").style.display==""){
	//document.getElementById("divVistaCajaApp").style.display="none"
	
	limpiacamposvistacaja()
	document.getElementById("tdEfectoVistaCajaApp").className="magictime slideRight"
$("div[id=divVistaCajaApp]").fadeOut(500);	
		
	}else{		
	
		document.getElementById("divVistaCajaApp").style.display=""
		
		 document.getElementById("tdEfectoVistaCajaApp").className="magictime slideLeftReturn"
	}
}

function limpiacamposvistacaja(){
	document.getElementById('inptBuscarVistaCajaF1').value="";
    document.getElementById('inptBuscarVistaCajaF2').value="";
	document.getElementById('inptBuscarVistaCaja1').value="";
	document.getElementById('inptBuscarVistaCaja2').value="";
	document.getElementById('inptTotalRegistoVistaCaja').value="";
	document.getElementById('table_vista_caja_app').innerHTML="";
}

function checkHistorialVistadeCaja(d){	
	if(d=="1"){
		document.getElementById('checkHistorialVistadeCaja1').checked=true
		document.getElementById('checkHistorialVistadeCaja2').checked=false
		document.getElementById('inptBuscarVistaCajaF1').value = "";
	    document.getElementById('inptBuscarVistaCajaF2').value = "";	
	}else{		
		document.getElementById('checkHistorialVistadeCaja1').checked=false
		document.getElementById('checkHistorialVistadeCaja2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarVistaCajaF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarVistaCajaF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}

function buscarvistacajaapp() {

	var fecha1 = document.getElementById('inptBuscarVistaCajaF1').value
	var fecha2 = document.getElementById('inptBuscarVistaCajaF2').value
	var cobrador = document.getElementById('inptBuscarVistaCaja1').value
	var estado = document.getElementById('inptBuscarVistaCaja2').value
	document.getElementById("table_vista_caja_app").innerHTML = paginacargando;
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cobrador": cobrador,
		"estado": estado,
		"funt": "buscarcajaapp"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmaperturacierrecaja.php",
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
			document.getElementById("table_vista_caja_app").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vista_caja_app").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_vista_caja_app").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoVistaCaja").value = datos[3];
					
				}
				
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
	}
	var codCajaApp="";
function obtenerdatosaperturacierrecajaapp(datostr) {
	
	codCajaApp = $(datostr).children('td[id="td_id_1"]').html();
	buscararqueo3()
	
	document.getElementById("divVistaCajaApp").style.display="none"
}

/*
COBROS REALIZADOS
*/
function verCerrarInformeArqueo(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divArqueo").style.display==""){
	document.getElementById("tdEfectoDivArqueo").className="magictime vanishOut"
	$("div[id=divArqueo]").fadeOut(500);	
	document.getElementById("divMinimizadoCobrosRealizados1").style.display="none"
	document.getElementById("divMinimizadoCobrosRealizados2").style.display="none"
	limpiacamposArqueo()
		
	}else{		
	if(controlacceso("VERCOBROSREALIZADOS","accion")==false){return;}
		document.getElementById("divArqueo").style.display=""
		  document.getElementById("tdEfectoDivArqueo").className="magictime slideDownReturn"
		
	}
}
function limpiacamposArqueo(){
	document.getElementById('inptBuscarCobrosRealizados4').value="";
    document.getElementById('inptBuscarCobrosRealizados1').value="";
	document.getElementById('inptBuscarCobrosRealizados3').value="";
	document.getElementById('inptBuscarCobrosRealizadosF1').value="";
	document.getElementById('inptBuscarCobrosRealizadosF2').value="";
	document.getElementById('inptBuscarCobrosRealizados2').value="";
	document.getElementById('inptlocalCobrosRealizados3').value="";
	document.getElementById('inptBuscarCobrosRealizados5').value="";
	document.getElementById('inptTotalRegistoArqueo').value="";
	document.getElementById('inptTotalArqueo').value="";
	document.getElementById('inptTotalEfectivoArqueo').value="";
	document.getElementById('inptTotalTarjetaArqueo').value="";
	document.getElementById("table_arqeo").innerHTML=""
}
function minimizarArqueo(){
	document.getElementById("tdEfectoDivArqueo").className="magictime slideDown"
	$("div[id=divArqueo]").fadeOut(500);	
	document.getElementById("divMinimizadoCobrosRealizados1").style.display=""
	document.getElementById("divMinimizadoCobrosRealizados2").style.display=""
}
function checkfiltrosCobrosRealizados(d){	
	if(d=="1"){
		document.getElementById('checkfiltrosCobrosRealizados1').checked=true
		document.getElementById('checkfiltrosCobrosRealizados2').checked=false
		document.getElementById('inptBuscarCobrosRealizadosF1').value = "";
	    document.getElementById('inptBuscarCobrosRealizadosF2').value = "";	
	}else{		
		document.getElementById('checkfiltrosCobrosRealizados1').checked=false
		document.getElementById('checkfiltrosCobrosRealizados2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarCobrosRealizadosF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarCobrosRealizadosF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
var cobradorarqueo = "";
var idHistorialPago = "";
function obtenerdatospagos(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
	datostr.className = 'tableRegistroSelec'
	idHistorialPago = $(datostr).children('td[id="td_datos_1"]').html();
		document.getElementById("btnEliminarCobros1").style.backgroundColor="red"

}
/*
IMPRIMIR COD. DE BARRAS
*/
function verCerrarInformeCodBarra(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divInformeCodBarra").style.display==""){
	    document.getElementById("divMinimizadoImprimirCodBarra").style.display="none"
document.getElementById("tdEfectoInformeCodBarra").className="magictime vanishOut"
	$("div[id=divInformeCodBarra]").fadeOut(500);	
		limpiarCamposBuscadorCodBarra();
		}else{		
		if(controlacceso("VERINFORMECODIGOBARRA","accion")==false){return;}
		document.getElementById("divInformeCodBarra").style.display=""
 document.getElementById("tdEfectoInformeCodBarra").className="magictime slideDownReturn"
	
		
	}
}
function limpiarCamposBuscadorCodBarra(){
	document.getElementById("inptProveedorProductoCodBarra1").value=""
	document.getElementById("inptProveedorProductoCodBarra2").value=""
	document.getElementById("table_comision_productos_cod_barra").innerHTML=""
}
function minimizarventanacodbarra(){
	document.getElementById("divMinimizadoImprimirCodBarra").style.display=""
document.getElementById("tdEfectoInformeCodBarra").className="magictime slideDown"
	$("div[id=divInformeCodBarra]").fadeOut(500);	
}
function buscarcodBarraProducto() {
	if(controlacceso("VERINFORMECODIGOBARRA","accion")==false){return;}
var paginas = controldeSelecCodigoBarra()
	var codigo = document.getElementById('inptProveedorProductoCodBarra1').value
	var producto = document.getElementById('inptProveedorProductoCodBarra2').value
	var local = document.getElementById('inptlocalProductoBuscarCodBarra').value
	document.getElementById("table_comision_productos_cod_barra").innerHTML = paginas+paginacargando;
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"producto": producto,
		"codigo": codigo,
		"local": local,
		"funt": "buscarcodBarra"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_comision_productos_cod_barra").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_productos_cod_barra").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_comision_productos_cod_barra").innerHTML =paginas+ datos_buscados
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
	}
function controldeSelecCodigoBarra(){
	var pagina="";
	$("tr[id=tr_Codigo_barras]").each(function(i, elementohtml){        
		var tdCheck=$(elementohtml).children('td[id="td_datos_6"]');
		var inpt=$(tdCheck).children('input[id="btnCheck"]');
		var tdCant=$(elementohtml).children('td[id="td_datos_5"]');
		var inptCantidad=$(tdCant).children('input[id="inptCantidad"]').val();		
		if ($(inpt).is(':checked') || inptCantidad>0 ) {      
        pagina+="<table class='tableRegistroSearch'  border='0' cellspacing='0' cellpadding='0'>"
+"<tr id='tr_Codigo_barras' >"
+"<td id='td_datos_6' style='width:10%'><input id='btnCheck' type='checkbox'  checked  /></td>"
+"<td id='td_datos_1' style='width:15%'>"+$(elementohtml).children('td[id="td_datos_1"]').html()+"</td>"
+"<td id='td_datos_2' style='width:35%'>"+$(elementohtml).children('td[id="td_datos_2"]').html()+"</td>"
+"<td  id='td_datos_3' style='width:20%'>"+$(elementohtml).children('td[id="td_datos_3"]').html()+"</td>"
+"<td id='td_datos_5' style='width:10%'><input id='inptCantidad'  type='text'  class='input5' value='"+inptCantidad+"' /></td>"
+"</tr>"
+"</table>";     
       }		
	   });	   
	   return pagina;
}
function ImprimirCodigoBarra(){
	var pagina="";
	var nroimg=0;
	var nroimgCant=0;
	document.getElementById("DivTablasBarras").innerHTML="<center><div style='with:95%;overflow:auto'>";
	$("tr[id=tr_Codigo_barras]").each(function(i, elementohtml){        
		var tdCheck=$(elementohtml).children('td[id="td_datos_6"]');
		var inpt=$(tdCheck).children('input[id="btnCheck"]');
		var tdCant=$(elementohtml).children('td[id="td_datos_5"]');
		var inptCantidad=$(tdCant).children('input[id="inptCantidad"]').val();		
		if ($(inpt).is(':checked') || inptCantidad>0 ) {
      nroimgCant=0;
      while(nroimgCant<inptCantidad){
    pagina="<div class='divCodigobarra'><center><img id='CodBarra"+nroimg+"_"+nroimgCant+"' style='height:70px;' />"
	+"<p class='pTitulo6'>"+$(elementohtml).children('td[id="td_datos_3"]').html()+"Gs.</p></center></div>"
  document.getElementById("DivTablasBarras").innerHTML+=pagina;
     JsBarcode("#CodBarra"+nroimg+"_"+nroimgCant, $(elementohtml).children('td[id="td_datos_1"]').html());
	 nroimgCant=nroimgCant+1;
	  }	 
	 nroimg=nroimg+1;
       }		
	   });
	document.getElementById("DivTablasBarras").innerHTML+="</div></center>";
	var documento=document.getElementById("DivTablasBarras").innerHTML;
	// documento=b64EncodeUnicode(documento)
	 localStorage.setItem("reporte", documento);
	  localStorage.setItem("tipo", "reporte");
	 window.open("https://systemsrepository.com/GoodVentaByR/system/report.html");
	document.getElementById("DivTablasBarras").innerHTML="";
}
/*
IMPRIMIR CATALOGO
*/
function verCerrarInformeCatalogo(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divInformeCatalago").style.display==""){
		if(controldebusquedadCatalogo==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
		limpiarbuscadorcatalogo()
		document.getElementById("tdEfectoInformeCatalogo").className="magictime vanishOut"
	$("div[id=divInformeCatalago]").fadeOut(500);	
		document.getElementById("divMinimizadoCatalago").style.display="none"
		
		}else{
if(controlacceso("VERCATALOGO","accion")==false){return;}		
		document.getElementById("divInformeCatalago").style.display=""
		 document.getElementById("tdEfectoInformeCatalogo").className="magictime slideDownReturn"

	}
}
function limpiarbuscadorcatalogo(){
	if(controldebusquedadCatalogo==true){
	return
}
	document.getElementById("inptProveedorProductoCatalogo").value=""
	document.getElementById("table_comision_productos_catalago").innerHTML=""
	document.getElementById("tbProcessCatalogo").style.display="none"
}
function minimizarcatalogo(){
	document.getElementById("tdEfectoInformeCatalogo").className="magictime slideDown"
	$("div[id=divInformeCatalago]").fadeOut(500);
	document.getElementById("divMinimizadoCatalago").style.display=""
}

var registrocargadocatalogo="";
var totalregistrocatalogo="";
var controldebusquedadCatalogo=false
function cancelarCatalogo(){
	controldebusquedadCatalogo=false
	document.getElementById("divProgressCatalogo").style.backgroundColor='#ff5722'
}

function buscarproductoscatalago() {	
if(controlacceso("VERCATALOGO","accion")==false){return;}	
	if(controldebusquedadCatalogo==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadCatalogo=true
	var buscar = document.getElementById('inptProveedorProductoCatalogo').value
	var local = document.getElementById('inptlocalProductoBuscarCatalago').value
	document.getElementById("table_comision_productos_catalago").innerHTML = paginacargando
	document.getElementById("tbProcessCatalogo").style.display="none"

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscar,
		"local": local,
		"funt": "buscarCatalogo"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_comision_productos_catalago").innerHTML = ''
			controldebusquedadCatalogo=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_productos_catalago").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];				
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_comision_productos_catalago").innerHTML = datos_buscados
						registrocargadocatalogo=datos[99];
					totalregistrocatalogo=datos[100];
			
						 if(totalregistrocatalogo>registrocargadocatalogo){
						 	var porce=((registrocargadocatalogo*100)/totalregistrocatalogo).toFixed(0)
	document.getElementById("divProgressCatalogo").style.width=porce+"%"
						 document.getElementById("table_comision_productos_catalago").innerHTML += "<div id='table_mas_comision_productos_catalago'></div>"
						  buscarmasproductoscatalago();
					 }else{
						 controldebusquedadCatalogo=false
					 }
				}
			} catch (error) {
				controldebusquedadCatalogo=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarmasproductoscatalago(c) {	
if(controlacceso("VERCATALOGO","accion")==false){return;}	
if(c=="1"){
	controldebusquedadCatalogo=true
}
	if(controldebusquedadCatalogo==false){
	return
}
controldebusquedadCatalogo=true
	var buscar = document.getElementById('inptProveedorProductoCatalogo').value
	var local = document.getElementById('inptlocalProductoBuscarCatalago').value
	document.getElementById("table_mas_comision_productos_catalago").innerHTML = paginacargando
	document.getElementById("tbProcessCatalogo").style.display=""
document.getElementById("divProgressCatalogo").style.backgroundColor=''
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscar,
		"local": local,
		"registrocargado": registrocargadocatalogo,
		"funt": "buscarMasCatalogo"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_mas_comision_productos_catalago").innerHTML = ''
			document.getElementById("divProgressCatalogo").style.backgroundColor='#ff5722'
			controldebusquedadCatalogo=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_mas_comision_productos_catalago").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];				
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_mas_comision_productos_catalago").innerHTML = datos_buscados
						registrocargadocatalogo=datos[99];
				
			
						 if(totalregistrocatalogo>registrocargadocatalogo){
						 	var porce=((registrocargadocatalogo*100)/totalregistrocatalogo).toFixed(0)
	document.getElementById("divProgressCatalogo").style.width=porce+"%"
						 document.getElementById("table_mas_comision_productos_catalago").innerHTML += "<div id='table_mas_comision_productos_catalago'></div>"
						 document.getElementById("table_mas_comision_productos_catalago").id=""
						  buscarmasproductoscatalago();
					 }else{
						 	document.getElementById("tbProcessCatalogo").style.display="none"
						 controldebusquedadCatalogo=false
					 }
				}
			} catch (error) {
				document.getElementById("divProgressCatalogo").style.backgroundColor='#ff5722'
				controldebusquedadCatalogo=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}


/*
CLIENTES INACTIVOS
*/
function vercerrarclientesinactivos(d) {
	document.getElementById("divSegundoPlano").style.display="none";
	if (d == "1") {
		if(controlacceso("VERCLIENTESINACTIVOS","accion")==false){return;}
		document.getElementById("divClientesInactivos").style.display=""
		 document.getElementById("tdEfectoClientesInactivo").className="magictime slideDownReturn"		
		} else {
			if(controldebusquedadClientesInactivos==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
		limpiarcamposbuscarclientes()
		document.getElementById("divMinimizadoClientesInactivo").style.display="none"
		document.getElementById("tdEfectoClientesInactivo").className="magictime vanishOut"
	$("div[id=divClientesInactivos]").fadeOut(500);			
	}
}
function limpiarcamposbuscarclientes(){
	if(controldebusquedadClientesInactivos==true){
	
	return
}
	document.getElementById("inputBuscarClientesInactivos1").value=""
	document.getElementById("inputBuscarClientesInactivos2").value=""
	document.getElementById("inputBuscarClientesInactivos3").value=""
	document.getElementById("inptTotalRegistoClientesInactivos").value=""
	document.getElementById("table_clientes_inactivos").innerHTML=""
	document.getElementById("table_clientes_inactivos_productos").innerHTML=""
	document.getElementById("table_clientes_inactivos_mensaje").innerHTML=""
	document.getElementById("inptFechaMensaje").value=""
	document.getElementById("inptHoraMensaje").value=""
	document.getElementById("tbProcessClientesInactivo").style.display="none"
}
function minimizarclientesinactivo(){
	document.getElementById("tdEfectoClientesInactivo").className="magictime slideDown"
	$("div[id=divClientesInactivos]").fadeOut(500);	
	document.getElementById("divMinimizadoClientesInactivo").style.display=""
}
function vercerrarfiltrosarqueo(d){
	if(d=="1"){
		document.getElementById("divFiltrosArqueo").style.display=""
	}else{
		document.getElementById("divFiltrosArqueo").style.display="none"
	}
}
var registrocargadoclientesinactivos="";
var totalregistroclientesinactivos="";
var controldebusquedadClientesInactivos=false
function cancelarClientesInactivo(){
	controldebusquedadClientesInactivos=false
	document.getElementById("divProgressClientesInactivo").style.backgroundColor='#ff5722'
}
function buscarclientesinactivos() {
	if(controlacceso("VERCLIENTESINACTIVOS","accion")==false){return;}
	var documento=document.getElementById("inputBuscarClientesInactivos1").value
	var cliente=document.getElementById("inputBuscarClientesInactivos2").value
	var nrotelefono=document.getElementById("inputBuscarClientesInactivos3").value
	var buscar=document.getElementById("inputSelectZonaComisionClientesInactivos").value
	if(buscar==""){
			ver_vetana_informativa("FALTO SELECCIONAR UNA ZONA")
		return
	}
	if(controldebusquedadClientesInactivos==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadClientesInactivos=true
	document.getElementById("table_clientes_inactivos").innerHTML = paginacargando
	document.getElementById("inptTotalRegistoClientesInactivos").value = ""
	document.getElementById("tbProcessClientesInactivo").style.display = "none"
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"documento": documento,
		"cliente": cliente,
		"nrotelefono": nrotelefono,
		"buscar": buscar,
		"funt": "buscarclientesincativos"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_clientes_inactivos").innerHTML = ''
			controldebusquedadClientesInactivos=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_clientes_inactivos").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
					Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_clientes_inactivos").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoClientesInactivos").value = datos[3];
					registrocargadoclientesinactivos=datos[99];
					totalregistroclientesinactivos=datos[100];
			
					
						 if(totalregistroclientesinactivos>registrocargadoclientesinactivos){
						 	var porce=((registrocargadoclientesinactivos*100)/totalregistroclientesinactivos).toFixed(0)
	document.getElementById("divProgressClientesInactivo").style.width=porce+"%"
						 document.getElementById("table_clientes_inactivos").innerHTML += "<div id='table_mas_clientes_inactivos'></div>"
						  buscarmasclientesinactivos();
					 }else{
						 controldebusquedadClientesInactivos=false
					 }
				}
			} catch (error) {
				controldebusquedadClientesInactivos=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarmasclientesinactivos(c) {
	if(controlacceso("VERCLIENTESINACTIVOS","accion")==false){return;}
	var documento=document.getElementById("inputBuscarClientesInactivos1").value
	var cliente=document.getElementById("inputBuscarClientesInactivos2").value
	var nrotelefono=document.getElementById("inputBuscarClientesInactivos3").value
	var buscar=document.getElementById("inputSelectZonaComisionClientesInactivos").value
	if(buscar==""){
			ver_vetana_informativa("FALTO SELECCIONAR UNA ZONA")
		return
	}
	if(c=="1"){
		controldebusquedadClientesInactivos=true
	}
	if(controldebusquedadClientesInactivos==false){

	return
}
controldebusquedadClientesInactivos=true
	document.getElementById("table_mas_clientes_inactivos").innerHTML = paginacargando
	document.getElementById("divProgressClientesInactivo").style.backgroundColor=''
	document.getElementById("tbProcessClientesInactivo").style.display = ""
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"documento": documento,
		"cliente": cliente,
		"nrotelefono": nrotelefono,
		"buscar": buscar,
		"registrocargado": registrocargadoclientesinactivos,
		"funt": "buscarmasclientesincativos"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_mas_clientes_inactivos").innerHTML = ''
			document.getElementById("divProgressClientesInactivo").style.backgroundColor='#ff5722'
			controldebusquedadClientesInactivos=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_mas_clientes_inactivos").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
					Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_mas_clientes_inactivos").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoClientesInactivos").value = datos[3];
					registrocargadoclientesinactivos=datos[99];
					
					
						 if(totalregistroclientesinactivos>registrocargadoclientesinactivos){
						 	var porce=((registrocargadoclientesinactivos*100)/totalregistroclientesinactivos).toFixed(0)
	document.getElementById("divProgressClientesInactivo").style.width=porce+"%"
						 document.getElementById("table_mas_clientes_inactivos").innerHTML += "<div id='table_mas_clientes_inactivos'></div>"
						 document.getElementById("table_mas_clientes_inactivos").id=""
						  buscarmasclientesinactivos();
					 }else{
						 document.getElementById("tbProcessClientesInactivo").style.display = "none"
						 controldebusquedadClientesInactivos=false
					 }
				}
			} catch (error) {
				document.getElementById("divProgressClientesInactivo").style.backgroundColor='#ff5722'
				controldebusquedadClientesInactivos=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}


function obtenerdatosvistaclienteinactivo(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	idClienteInactivoFK = $(datostr).children('td[id="td_id"]').html();
	buscarproductoshistorialclienteinactivo()
	buscarmensajeslclienteinactivo()	
}
var idClienteInactivoFK="";
function buscarproductoshistorialclienteinactivo() {
	document.getElementById("table_clientes_inactivos_productos").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codCliente": idClienteInactivoFK,
		"funt": "productosCompradosclienteInactivo"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
			document.getElementById("table_clientes_inactivos_productos").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_clientes_inactivos_productos").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_clientes_inactivos_productos").innerHTML = datos_buscados
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
			}
	});
}
function buscarmensajeslclienteinactivo() {
	document.getElementById("table_clientes_inactivos_mensaje").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": idClienteInactivoFK,
		"funt": "buscarmensajes"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmclientes.php",
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
			document.getElementById("table_clientes_inactivos_mensaje").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_clientes_inactivos_mensaje").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				
					var datos_buscados = datos[2];
					document.getElementById("table_clientes_inactivos_mensaje").innerHTML = datos_buscados					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)		
				}
		}
		
	});
}
function actualizarFechaDeMensajes() {
	var fecha=document.getElementById("inptFechaMensaje").value
	var hora=document.getElementById("inptHoraMensaje").value
	if(idClienteInactivoFK==""){
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO")
					return false;
	}
	if(fecha==""){
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA")
					return false;
	}
	if(hora==""){
		ver_vetana_informativa("FALTO SELECCIONAR LA HORA")
					return false;
	}
verCerrarEfectoCargando("1")
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha": fecha,
		"hora": hora,
		"idcliente": idClienteInactivoFK,
		"funt": "guardarmensaje"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmclientes.php",
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
			verCerrarEfectoCargando("2")
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			verCerrarEfectoCargando("2")
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
			
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {			
				buscarmensajeslclienteinactivo();
				ver_vetana_informativa("DATOS GUARDADO CORRECTAMENTE")
					return false;
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
CUENTAS A PAGAR 
*/
function verCerrarInformeCuentasAPagar(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divCuentasAPagar").style.display==""){
		limpiarcamposbuscadorcuentaspagar()
		document.getElementById("tdEfectoCuentasPagar").className="magictime vanishOut"
	$("div[id=divCuentasAPagar]").fadeOut(500);	
		document.getElementById("divMinimizadoCuentasPagar").style.display="none"		
		}else{
if(controlacceso("VERCUENTASAPAGAR","accion")==false){return;}		
		document.getElementById("divCuentasAPagar").style.display=""
		  document.getElementById("tdEfectoCuentasPagar").className="magictime slideDownReturn"
		}
}
function limpiarcamposbuscadorcuentaspagar(){
	document.getElementById("inptBuscarCuentaApagarF1").value=""
	document.getElementById("inptBuscarCuentaApagarF2").value=""
	document.getElementById("inptCuentasPagar1").value=""
	document.getElementById("inptCuentasPagar2").value=""
	document.getElementById("inptCuentasPagar3").value=""
	document.getElementById("inptCuentasPagar4").value=""
	document.getElementById("inptTotalRegistoCuentaApagar").value=""
	document.getElementById("inptTotalCuentaApagar").value=""
	document.getElementById("inptRegistroSeleccionadoCuentaApagar").value=""
	document.getElementById("table_CuentaApagar").innerHTML=""
}
function minimizarcuentaspagar(){
	document.getElementById("tdEfectoCuentasPagar").className="magictime slideDown"
	$("div[id=divCuentasAPagar]").fadeOut(500);	
	document.getElementById("divMinimizadoCuentasPagar").style.display=""
}
function checkHistorialCuentaPagar(d){	
	if(d=="1"){
		document.getElementById('checkHistorialCuentaPagar1').checked=true
		document.getElementById('checkHistorialCuentaPagar2').checked=false
		document.getElementById('inptBuscarCuentaApagarF1').value = "";
	    document.getElementById('inptBuscarCuentaApagarF2').value = "";	
	}else{		
		document.getElementById('checkHistorialCuentaPagar1').checked=false
		document.getElementById('checkHistorialCuentaPagar2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarCuentaApagarF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarCuentaApagarF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
function buscarcuentasapagar() {
	if(controlacceso("VERCUENTASAPAGAR","accion")==false){return;}	
    var fecha1 = document.getElementById('inptBuscarCuentaApagarF1').value
	var fecha2 = document.getElementById('inptBuscarCuentaApagarF2').value
	var nrofactura = document.getElementById('inptCuentasPagar1').value
	var proveedor = document.getElementById('inptCuentasPagar2').value
	var filtrofecha = document.getElementById('inptCuentasPagar3').value
	var nrocheque = document.getElementById('inptCuentasPagar4').value
	var cod_local = document.getElementById('inptlocalCuentaApagar').value
	if(document.getElementById('checkHistorialCuentaPagar2').checked==true){
		 if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
		return false;
	}
	
	}
	
	if(document.getElementById('checkHistorialCuentaPagar1').checked==true){
		
	var fecha1 =""
	var fecha2 =""
	
	}
	
	  
	
	document.getElementById("table_CuentaApagar").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"proveedor": proveedor,
		"cod_local": cod_local,
		"nrofactura": nrofactura,
		"filtrofecha": filtrofecha,
		"nrocheque": nrocheque,
		"funt": "buscarcuentasapagar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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
			document.getElementById("table_CuentaApagar").innerHTML = ''
			},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_CuentaApagar").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				
					var datos_buscados = datos[2];
					document.getElementById("table_CuentaApagar").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoCuentaApagar").value = datos[3];
					document.getElementById("inptTotalCuentaApagar").value = datos[4];
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function iracompra() {
if(controlacceso("EDITARCARGADECOMPRAS","accion")==false){	   
	   //SIN PERMISO
	   return;
		}
		
if (elementocompra == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	datos = elementocompra
	document.getElementById('inptNrocompra').value = $(datos).children('td[id="td_datos_1"]').html()
	document.getElementById('inpCodCompra').value = $(datos).children('td[id="td_datos_1"]').html()
	document.getElementById('inptFechaCompra').value = $(datos).children('td[id="td_datos_2"]').html()
	document.getElementById('inptProveedorCompra').value = $(datos).children('td[id="td_datos_3"]').html()
	document.getElementById('inptlocalCompra').value = $(datos).children('td[id="td_datos_11"]').html()
	  document.getElementById('inptlocalCompra').disabled=true
	document.getElementById('inptDescuentocompra').value = $(datos).children('td[id="td_datos_8"]').html()	
	document.getElementById('inptPagosRealizadoscompra').value = $(datos).children('td[id="td_datos_12"]').html()	
	codProveedorCompra = $(datos).children('td[id="td_datos_6"]').html()
	idAbmCompra = $(datos).children('td[id="td_datos_5"]').html();
	document.getElementById("btnAbmCompra").value = "Editar Datos"
	document.getElementById("btnAbmCompra").style.display = ""
	buscardetallescompra()	
	verCerrarAbmCompra2()	
	verCerrarVentanasHistorialCompra("1")
}
/*
HISTORIAL DE GARANTIAS
*/
function verCerrarInformeProductoEnGarantia(d){
	document.getElementById("divSegundoPlano").style.display="none";
	if(d=="1"){
		if(controlacceso("VERINFORMEGARANTIA","accion")==false){return;}
		 document.getElementById("divProductoEnGarantia").style.display = "";
		document.getElementById("tdEfectoProductoEnGarantia").className="magictime slideDownReturn"		
	}else{
		document.getElementById("divMinimizadoProductoEnGarantia").style.display = "none";
		limpiarcamposproductosganrantia()
document.getElementById("tdEfectoProductoEnGarantia").className="magictime vanishOut"
	$("div[id=divProductoEnGarantia]").fadeOut(500);	
		
	}	
}
function limpiarcamposproductosganrantia(){
	document.getElementById("inptBuscarProductosGarantia1").value=""
	document.getElementById("inptBuscarProductosGarantia2").value=""
	document.getElementById("inptBuscarProductosGarantia3").value=""
	document.getElementById("table_ProductoGarantia").innerHTML = ""
	document.getElementById("inptTotalRegistoProductoGarantia").value = ""
	
}
function minimizarproductogarantia(){
	 document.getElementById("divMinimizadoProductoEnGarantia").style.display = "";
document.getElementById("tdEfectoProductoEnGarantia").className="magictime slideDown"
	$("div[id=divProductoEnGarantia]").fadeOut(500);	
}

function buscarHistorialGarantia() {
	if(controlacceso("BUSCARINFORMEGARANTIA","accion")==false){return;}
	var nrofactura = document.getElementById("inptBuscarProductosGarantia1").value
	var cod_local = document.getElementById("inptlocalProductoGarantia").value
	var documento = document.getElementById("inptBuscarProductosGarantia2").value
	var cliente = document.getElementById("inptBuscarProductosGarantia3").value
	var estado = document.getElementById("inptBuscarProductosGarantia6").value
	document.getElementById("table_ProductoGarantia").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"nrofactura": nrofactura,
		"documento": documento,
		"cliente": cliente,
		"cod_local": cod_local,
		"estado": estado,
		"funt": "buscarHistorialGarantia"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
			document.getElementById("table_ProductoGarantia").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_ProductoGarantia").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_ProductoGarantia").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoProductoGarantia").value = datos[3]
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
var idGarantiaModificar="";
var observacionGarantiaTikect="";
function obtenerdatosvistaproductosgarantia(datostr) {	
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	datostr.className = 'tableRegistroSelec'
	idGarantiaModificar = $(datostr).children('td[id="td_id_1"]').html();
	var estadogarantia = $(datostr).children('td[id="td_datos_9"]').html();
	var optionselect=""
	if(estadogarantia=="Pendiente a verificar"){
	optionselect="<option  value='verificacion' >EN VERIFICACION</option>";  
	}
	if(estadogarantia=="verificacion"){
	optionselect="<option  value='listo' >LISTO PARA ENTREGAR</option>";  
	}
	if(estadogarantia=="listo"){
	optionselect="<option  value='entregado' >ENTREGADO</option>";  
	}
	
	document.getElementById('inputSelectEstadoengarantiaHistorial').innerHTML = optionselect;
	document.getElementById('inptRegistroSeleccionadoProductoGarantia').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNroVentaGarantiaHistorial').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptProductoDevolucionGarantiaHistorial').value = $(datostr).children('td[id="td_datos_5"]').html();
	observacionGarantiaTikect = $(datostr).children('td[id="td_datos_6"]').html();
	SeleccEstadoGarantia()
	document.getElementById('inptFechaEntregaGarantiaHistorial').value ="";	
	if(estadogarantia=="entregado"){
	idGarantiaModificar="";
		document.getElementById('inptRegistroSeleccionadoProductoGarantia').value = "";
	}
	
}
function verCerrarHistorialProductoEnGarantia(d){
	if(d=="1"){
		if(idGarantiaModificar==""){
			ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
			return
		}
		
		 document.getElementById("divGarantiaProductoHistorial").style.display = "";
	}else{
		 document.getElementById("divGarantiaProductoHistorial").style.display = "none";
	}	
}
function SeleccEstadoGarantia(){		
    document.getElementById('divFechaEnvio').style.display="none"
	document.getElementById('divFechaDevuelto').style.display="none"
	document.getElementById('divFechaEntrega').style.display="none"
	if(document.getElementById('inputSelectEstadoengarantiaHistorial').value=="verificacion"){
		document.getElementById('divFechaEnvio').style.display=""
	}
	if(document.getElementById('inputSelectEstadoengarantiaHistorial').value=="listo"){
		document.getElementById('divFechaDevuelto').style.display=""
	}
	if(document.getElementById('inputSelectEstadoengarantiaHistorial').value=="entregado"){
		document.getElementById('divFechaEntrega').style.display=""
	}
}
function modificarRegistroGarantia() {
   	
     var inputSelectEstadoengarantiaHistorial=document.getElementById("inputSelectEstadoengarantiaHistorial").value
     var fecha=""
	 if(inputSelectEstadoengarantiaHistorial=="verificacion"){
		fecha=document.getElementById("inptFechaEnvioGarantiaHistorial").value		
	}
	if(inputSelectEstadoengarantiaHistorial=="listo"){
		fecha=document.getElementById("inptFechaDevueltaGarantiaHistorial").value
	}
	if(inputSelectEstadoengarantiaHistorial=="entregado"){
		fecha=document.getElementById("inptFechaEntregaGarantiaHistorial").value
	}	 
	if (idGarantiaModificar == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO", "#")
		return false;
	}
	if (inptFechaEntregaGarantiaHistorial == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UN FECHA ", "#")
		return false;
	}
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "editarusogarantia")
	datos.append("idgarantia", idGarantiaModificar)
	datos.append("fecha", fecha)
	datos.append("estado", inputSelectEstadoengarantiaHistorial)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					if(inputSelectEstadoengarantiaHistorial=="verificacion"){
						ImprimirDivTicketGarantiaVerificacion()
					}
					if(inputSelectEstadoengarantiaHistorial=="entregado"){
						ImprimirDivTicketGarantiaEntrega()
					}
					document.getElementById("divGarantiaProductoHistorial").style.display = "none";
				   buscarHistorialGarantia()
                   				   
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
CONSULTA DE CAJA
*/
function verCerrarInformeConsultaCaja(){
	if(document.getElementById("divConsultaCaja").style.display==""){
		document.getElementById("divMinimizadoConsultarCaja").style.display="none"
			limpiarcamposbuscadorConsultarCaja()
		document.getElementById("tdEfectoConsultaCaja").className="magictime vanishOut"
	$("div[id=divConsultaCaja]").fadeOut(500);			
	}else{		
	if(controlacceso("VERCONSULTADECAJA","accion")==false){return;}
		document.getElementById("divConsultaCaja").style.display=""
		 document.getElementById("tdEfectoConsultaCaja").className="magictime slideDownReturn"	
	}
}
function limpiarcamposbuscadorConsultarCaja(){
	document.getElementById("inptTotalIngresoConsularCaja").value = ""
					document.getElementById("inptTotalEgresoConsularCaja").value = ""
					document.getElementById("inptTotalConsularCaja").value = ""
					document.getElementById("inptTotalEfectivoConsultarCaja").value = ""
					document.getElementById("inptTotalTarjetaConsularCaja").value = ""
					
					document.getElementById("inptBuscarVistaApCie1").value = ""
					document.getElementById("inptBuscarVistaApCie7").value = ""
					document.getElementById("inptBuscarVistaApCie2").value = ""
					document.getElementById("inptBuscarVistaApCie3").value = ""
					document.getElementById("inptBuscarVistaApCie4").value = ""
					document.getElementById("inptBuscarVistaApCie5").value = ""
					document.getElementById("inptBuscarVistaApCie6").value = ""
					document.getElementById("table_Consultar_caja").innerHTML = ""
}
function minimizarconsultacaja(){
	document.getElementById("tdEfectoConsultaCaja").className="magictime slideDown"
	$("div[id=divConsultaCaja]").fadeOut(500);	
	document.getElementById("divMinimizadoConsultarCaja").style.display=""
}
var cobradorarqueo = "";
function buscarinformecaja() {
	if(controlacceso("VERCONSULTADECAJA","accion")==false){return;}
	document.getElementById("inptTotalIngresoConsularCaja").value = "..."
					document.getElementById("inptTotalEgresoConsularCaja").value = "..."
					document.getElementById("inptTotalConsularCaja").value = "..."
					document.getElementById("inptTotalEfectivoConsultarCaja").value = "..."
					document.getElementById("inptTotalTarjetaConsularCaja").value = "..."
	document.getElementById("table_Consultar_caja").innerHTML = paginacargando
	obtener_datos_user();
	
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idArqeoFk1": idArqeoFk,
		"funt": "buscarinformecaja"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abminforemcaja.php",
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
			document.getElementById("table_Consultar_caja").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_Consultar_caja").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_Consultar_caja").innerHTML = datos_buscados
					document.getElementById("inptTotalEfectivoConsultarCaja").value = datos[7]
					document.getElementById("inptTotalTarjetaConsularCaja").value = datos[6]
					document.getElementById("inptTotalIngresoConsularCaja").value = datos[3]
					document.getElementById("inptTotalEgresoConsularCaja").value = datos[4]
					document.getElementById("inptTotalConsularCaja").value = datos[5]
				
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
PRODUCTOS VENDIDOS
*/
function verCerrarInformeProductosVendidos(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divInformeProductosVentas").style.display==""){
			if(controldebusquedadProductosVendidos==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
	document.getElementById("divMinimizadoProductoVendido").style.display="none"
	limpiarcamposproductosvendidos()
document.getElementById("tdEfectoProductosVendidos").className="magictime vanishOut"
	$("div[id=divInformeProductosVentas]").fadeOut(500);	
	}else{		
	if(controlacceso("VERINFORMEDEPRODUCTOSVENDIDOS","accion")==false){return;}
		document.getElementById("divInformeProductosVentas").style.display=""
  document.getElementById("tdEfectoProductosVendidos").className="magictime slideDownReturn"
	
	}
}
function limpiarcamposproductosvendidos(){
		if(controldebusquedadProductosVendidos==true){

	return
}
	document.getElementById("inptBuscarProductosVendidosF1").value=""
	document.getElementById("inptBuscarProductosVendidosF2").value=""
	document.getElementById("inptBuscarProductosVendidos1").value=""
	document.getElementById("inptBuscarProductosVendidos2").value=""
	document.getElementById("inptTotalRegistroProductosVendidos").value=""
	document.getElementById("inptTotalRegistroTotalVentas").value=""
	document.getElementById("inptTotalVentasInvertido").value=""
	document.getElementById("table_comision_productosVendidos").innerHTML=""
	document.getElementById("tbProcessProductosVendidos").style.display="none"
}
function minimizarproductosvendidos(){
document.getElementById("tdEfectoProductosVendidos").className="magictime slideDown"
	$("div[id=divInformeProductosVentas]").fadeOut(500);	
	document.getElementById("divMinimizadoProductoVendido").style.display=""
}
function checkHistorialProductoVendidos(d){	
	if(d=="1"){
		document.getElementById('checkHistorialProductoVendido1').checked=true
		document.getElementById('checkHistorialProductoVendido2').checked=false
		document.getElementById('inptBuscarProductosVendidosF1').value = "";
	    document.getElementById('inptBuscarProductosVendidosF2').value = "";	
	}else{		
		document.getElementById('checkHistorialProductoVendido1').checked=false
		document.getElementById('checkHistorialProductoVendido2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarProductosVendidosF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarProductosVendidosF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}

function cambiarTipoInformeProductosVendidos(d){
	if(controldebusquedadProductosVendidos==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
document.getElementById("btnInformeProductoVendidos1").style=""
document.getElementById("btnInformeProductoVendidos2").style=""
if(d=="1"){
	document.getElementById("btnInformeProductoVendidos1").style="background-color:#ff9800;color:#fff"
	agrupacionproductovendidoinforme="1";
}else{
	document.getElementById("btnInformeProductoVendidos2").style="background-color:#ff9800;color:#fff"
	agrupacionproductovendidoinforme="2";
}
}
var agrupacionproductovendidoinforme="1";
var registrocargadoproductosvendidos="";
var totalregistroproductosvendidos="";
var controldebusquedadProductosVendidos=false
function cancelarProductosVendidos(){
	controldebusquedadProductosVendidos=false
	document.getElementById("divProgressProductosVendidos").style.backgroundColor='#ff5722'
}
function buscarproductosvendidos() {
if(controlacceso("VERINFORMEDEPRODUCTOSVENDIDOS","accion")==false){return;}
	var fecha1 = document.getElementById('inptBuscarProductosVendidosF1').value
	var fecha2 = document.getElementById('inptBuscarProductosVendidosF2').value
	var cod_local = document.getElementById('inptlocalInformeProductosVendidos').value
	var categoria = document.getElementById('inptCategoriaProductoInformeProductosVendidos').value
	var marca = document.getElementById('inptMarcaInformeProductosVendidos').value
	var codigo = document.getElementById('inptBuscarProductosVendidos1').value
	var producto = document.getElementById('inptBuscarProductosVendidos2').value
	if (document.getElementById('checkHistorialProductoVendido2').checked==true) {
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
		return false;
	}
	}else{
	fecha1="";	
	fecha2="";	
	}
		if(controldebusquedadProductosVendidos==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadProductosVendidos=true
	document.getElementById("table_comision_productosVendidos").innerHTML = paginacargando
	document.getElementById("tbProcessProductosVendidos").style.display='none'
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cod_local": cod_local,
		"categoria": categoria,
		"marca": marca,
		"codigo": codigo,
		"producto": producto,
		"agrupacionproductovendidoinforme": agrupacionproductovendidoinforme,
		"funt": "buscarproductovendidos"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
			document.getElementById("table_comision_productosVendidos").innerHTML = ''
			controldebusquedadProductosVendidos=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_productosVendidos").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];				
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_comision_productosVendidos").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistroProductosVendidos").value = datos[3];
					document.getElementById("inptTotalRegistroTotalVentas").value = datos[4];
					document.getElementById("inptTotalVentasInvertido").value = datos[5];
						registrocargadoproductosvendidos=datos[99];
					totalregistroproductosvendidos=datos[100];
				 if(totalregistroproductosvendidos>registrocargadoproductosvendidos){
						 	var porce=((registrocargadoproductosvendidos*100)/totalregistroproductosvendidos).toFixed(0)
	document.getElementById("divProgressProductosVendidos").style.width=porce+"%"
						 document.getElementById("table_comision_productosVendidos").innerHTML += "<div id='table_comision_mas_productosVendidos'></div>"
						  buscarmasproductosvendidos();
					 }else{
						 controldebusquedadProductosVendidos=false
					 }
					
				}
			} catch (error) {
				controldebusquedadProductosVendidos=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarmasproductosvendidos(c) {
if(controlacceso("VERINFORMEDEPRODUCTOSVENDIDOS","accion")==false){return;}
	var fecha1 = document.getElementById('inptBuscarProductosVendidosF1').value
	var fecha2 = document.getElementById('inptBuscarProductosVendidosF2').value
	var cod_local = document.getElementById('inptlocalInformeProductosVendidos').value
	var categoria = document.getElementById('inptCategoriaProductoInformeProductosVendidos').value
	var marca = document.getElementById('inptMarcaInformeProductosVendidos').value
	var codigo = document.getElementById('inptBuscarProductosVendidos1').value
	var producto = document.getElementById('inptBuscarProductosVendidos2').value
	if (document.getElementById('checkHistorialProductoVendido2').checked==true) {
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
		return false;
	}
	}else{
	fecha1="";	
	fecha2="";	
	}
	if(c=="1"){
		controldebusquedadProductosVendidos=true
	}
		if(controldebusquedadProductosVendidos==false){
		
	return
}
controldebusquedadProductosVendidos=true
	document.getElementById("table_comision_mas_productosVendidos").innerHTML = paginacargando
	document.getElementById("tbProcessProductosVendidos").style.display=''
	document.getElementById("divProgressProductosVendidos").style.backgroundColor=''
	var totalventa=document.getElementById("inptTotalRegistroTotalVentas").value;
	var totalinvertido=document.getElementById("inptTotalVentasInvertido").value;
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cod_local": cod_local,
		"categoria": categoria,
		"marca": marca,
		"codigo": codigo,
		"producto": producto,
		"totalventa": totalventa,
		"totalinvertido": totalinvertido,
		"registrocargado": registrocargadoproductosvendidos,
		"agrupacionproductovendidoinforme": agrupacionproductovendidoinforme,
		"funt": "buscarmasproductovendidos"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
			document.getElementById("table_comision_mas_productosVendidos").innerHTML = ''
			controldebusquedadProductosVendidos=false
			document.getElementById("divProgressProductosVendidos").style.backgroundColor='#ff5722'
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_mas_productosVendidos").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];				
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_comision_mas_productosVendidos").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistroProductosVendidos").value = datos[3];
					document.getElementById("inptTotalRegistroTotalVentas").value = datos[4];
					document.getElementById("inptTotalVentasInvertido").value = datos[5];
						registrocargadoproductosvendidos=datos[99];
				 if(totalregistroproductosvendidos>registrocargadoproductosvendidos){
						 	var porce=((registrocargadoproductosvendidos*100)/totalregistroproductosvendidos).toFixed(0)
	document.getElementById("divProgressProductosVendidos").style.width=porce+"%"
						 document.getElementById("table_comision_mas_productosVendidos").innerHTML += "<div id='table_comision_mas_productosVendidos'></div>"
						 document.getElementById("table_comision_mas_productosVendidos").id=""
						  buscarmasproductosvendidos();
					 }else{
						 	document.getElementById("tbProcessProductosVendidos").style.display='none'
						 controldebusquedadProductosVendidos=false
					 }
					
				}
			} catch (error) {
				document.getElementById("divProgressProductosVendidos").style.backgroundColor='#ff5722'
				controldebusquedadProductosVendidos=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

/*
PRODUCTOS COMPRADOS
*/
function verCerrarInformeProductosComprados(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divInformeProductosComprados").style.display==""){
			if(controldebusquedadProductosComprados==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
		document.getElementById("divMinimizadoProductoComprado").style.display="none"
		limpiarCamposBuscadorProductosComprados()
document.getElementById("tdEfectoInformeProductosComprados").className="magictime vanishOut"
	$("div[id=divInformeProductosComprados]").fadeOut(500);	
	}else{		
	if(controlacceso("VERINFORMEDEPRODUCTOSCOMPRADOS","accion")==false){return;}
		document.getElementById("divInformeProductosComprados").style.display=""
 document.getElementById("tdEfectoInformeProductosComprados").className="magictime slideDownReturn"
	
	}
}
function limpiarCamposBuscadorProductosComprados(){
		if(controldebusquedadProductosComprados==true){
		
	return
}
	document.getElementById("inptBuscarProductosCompradosF1").value=""
	document.getElementById("inptBuscarProductosCompradosF2").value=""
	document.getElementById("inptBuscarProductosComprados1").value=""
	document.getElementById("inptBuscarProductosComprados2").value=""
	document.getElementById("inptTotalRegistroProductoComprados").value=""
	document.getElementById("inptTotalRegistroProductosComprados").value=""
	document.getElementById("table_comision_productosComprados").innerHTML=""
	document.getElementById("tbProcessProductosComprados").style.display="none"
}
function minimizarventanaProductosComprados(){
document.getElementById("tdEfectoInformeProductosComprados").className="magictime slideDown"
	$("div[id=divInformeProductosComprados]").fadeOut(500);	
	document.getElementById("divMinimizadoProductoComprado").style.display=""
}
function checkHistorialProductoComprados(d){	
	if(d=="1"){
		document.getElementById('checkHistorialProductoComprados1').checked=true
		document.getElementById('checkHistorialProductoComprados2').checked=false
		document.getElementById('inptBuscarProductosCompradosF1').value = "";
	    document.getElementById('inptBuscarProductosCompradosF2').value = "";	
	}else{		
		document.getElementById('checkHistorialProductoComprados1').checked=false
		document.getElementById('checkHistorialProductoComprados2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarProductosCompradosF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarProductosCompradosF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
var registrocargadoproductoscomprados="";
var totalregistroproductoscomproados="";
var controldebusquedadProductosComprados=false
function cancelarProductosComprados(){
	controldebusquedadProductosComprados=false
	document.getElementById("divProgressProductosComprados").style.backgroundColor='#ff5722'
}
function buscarproductoscomprados() {
	if(controlacceso("VERINFORMEDEPRODUCTOSCOMPRADOS","accion")==false){return;}
	var cod_local = document.getElementById('inptlocalInformeProductosComprados').value
	var categoria = document.getElementById('inptCategoriaInformeProductosComprados').value
	var marca = document.getElementById('inptMarcaInformeProductosComprados').value
	var fecha1 = document.getElementById('inptBuscarProductosCompradosF1').value
	var fecha2 = document.getElementById('inptBuscarProductosCompradosF2').value
	var codigo = document.getElementById('inptBuscarProductosComprados1').value
	var producto = document.getElementById('inptBuscarProductosComprados2').value
	if(document.getElementById('checkHistorialProductoComprados2').checked==true){
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
		return false;
	}
	}else{
	fecha1 = ""
	fecha2 = ""
		
	}
	if(controldebusquedadProductosComprados==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
	controldebusquedadProductosComprados=true
	document.getElementById("table_comision_productosComprados").innerHTML = paginacargando
	document.getElementById("tbProcessProductosComprados").style.display="none"
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"marca": marca,
		"cod_local": cod_local,
		"categoria": categoria,
		"codigo": codigo,
		"producto": producto,
		"funt": "buscarproductocomprados"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallecompra.php",
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
			document.getElementById("table_comision_productosComprados").innerHTML = ''
			controldebusquedadProductosComprados=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_productosComprados").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_comision_productosComprados").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistroProductosComprados").value = datos[3];
					document.getElementById("inptTotalRegistroProductoComprados").value = datos[4];
					registrocargadoproductoscomprados=datos[99];
					totalregistroproductoscomproados=datos[100];
		
						 if(totalregistroproductoscomproados>registrocargadoproductoscomprados){
						 	var porce=((registrocargadoproductoscomprados*100)/totalregistroproductoscomproados).toFixed(0)
	document.getElementById("divProgressProductosComprados").style.width=porce+"%"
						 document.getElementById("table_comision_productosComprados").innerHTML += "<div id='table_comision_mas_productosComprados'></div>"
						  buscarmasproductoscomprados();
					 }else{
						 controldebusquedadProductosComprados=false
					 }
				}
			} catch (error) {
				controldebusquedadProductosComprados=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarmasproductoscomprados(c) {
	if(controlacceso("VERINFORMEDEPRODUCTOSCOMPRADOS","accion")==false){return;}
	var cod_local = document.getElementById('inptlocalInformeProductosComprados').value
	var categoria = document.getElementById('inptCategoriaInformeProductosComprados').value
	var marca = document.getElementById('inptMarcaInformeProductosComprados').value
	var fecha1 = document.getElementById('inptBuscarProductosCompradosF1').value
	var fecha2 = document.getElementById('inptBuscarProductosCompradosF2').value
	var codigo = document.getElementById('inptBuscarProductosComprados1').value
	var producto = document.getElementById('inptBuscarProductosComprados2').value
	if(document.getElementById('checkHistorialProductoComprados2').checked==true){
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
		return false;
	}
	}else{
	fecha1 = ""
	fecha2 = ""
		
	}
	if(c=="1"){
		controldebusquedadProductosComprados=true
	}
	if(controldebusquedadProductosComprados==false){
		
	return
}
	controldebusquedadProductosComprados=true
	document.getElementById("table_comision_mas_productosComprados").innerHTML = paginacargando
	document.getElementById("tbProcessProductosComprados").style.display=""
	document.getElementById("divProgressProductosComprados").style.backgroundColor=''
	var totalcompra=document.getElementById("inptTotalRegistroProductosComprados").value;
	
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"marca": marca,
		"cod_local": cod_local,
		"categoria": categoria,
		"codigo": codigo,
		"producto": producto,
		"registrocargado": registrocargadoproductoscomprados,
		"totalcompra": totalcompra,
		"funt": "buscarmasproductocomprados"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmdetallecompra.php",
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
			document.getElementById("table_comision_mas_productosComprados").innerHTML = ''
			document.getElementById("divProgressProductosComprados").style.backgroundColor='#ff5722'
			controldebusquedadProductosComprados=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_mas_productosComprados").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_comision_mas_productosComprados").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistroProductosComprados").value = datos[3];
					document.getElementById("inptTotalRegistroProductoComprados").value = datos[4];
					registrocargadoproductoscomprados=datos[99];
					
		
						 if(totalregistroproductoscomproados>registrocargadoproductoscomprados){
						 	var porce=((registrocargadoproductoscomprados*100)/totalregistroproductoscomproados).toFixed(0)
	document.getElementById("divProgressProductosComprados").style.width=porce+"%"
						 document.getElementById("table_comision_mas_productosComprados").innerHTML += "<div id='table_comision_mas_productosComprados'></div>"
						 document.getElementById("table_comision_mas_productosComprados").id=""
						  buscarmasproductoscomprados();
					 }else{
						 document.getElementById("tbProcessProductosComprados").style.display="none"
						 controldebusquedadProductosComprados=false
					 }
				}
			} catch (error) {
				document.getElementById("divProgressProductosComprados").style.backgroundColor='#ff5722'
				controldebusquedadProductosComprados=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

/*
COMISIÓN DE COBRADOR
*/
function verCerrarInformeComisionCobrador(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divComisionCobrador").style.display==""){
		if(controldebusquedadComisionCobrador==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
	document.getElementById("divMinimizadoComisionCobrador").style.display="none"
	limpiarcamposbuscarcomisioncobrador()
document.getElementById("tdEfectoComisionCobrador").className="magictime vanishOut"
	$("div[id=divComisionCobrador]").fadeOut(500);	
	}else{		
	if(controlacceso("VERINFORMEDECOMISIONCOBRADOR","accion")==false){return;}
		document.getElementById("divComisionCobrador").style.display=""
 document.getElementById("tdEfectoComisionCobrador").className="magictime slideDownReturn"
	
	}
}
function minimizarInformeComisionCobrador(){
document.getElementById("tdEfectoComisionCobrador").className="magictime slideDown"
	$("div[id=divComisionCobrador]").fadeOut(500);
	document.getElementById("divMinimizadoComisionCobrador").style.display=""
}
function limpiarcamposbuscarcomisioncobrador(){
	if(controldebusquedadComisionCobrador==true){
	
	return
}
	 document.getElementById('inptBuscarComisionCobradorF1').value=""
	 document.getElementById('inptBuscarComisionCobradorF2').value=""
	 document.getElementById('inputSelectZonaComisionCobrador').value=""
	  document.getElementById('inptBuscarComisionCobrador1').value=""
	 document.getElementById('inptBuscarComisionCobrador2').value=""
	 document.getElementById("inptTotalRecaudadoComision").value = "";
					document.getElementById("inptTotalRegistoComision").value = "";
					document.getElementById("inptTotalComision").value = "";
	 document.getElementById('table_comision_cobrador').innerHTML=""
	 document.getElementById('tbProcessComisionCobrador').style.display="none"
}
function minimizarventanaComisionCobrador(){
	document.getElementById("divComisionCobrador").style.display="none"
	document.getElementById("divMinimizadoComisionCobrador").style.display=""
}
function checkHistorialComisionCobrador(d){	
	if(d=="1"){
		document.getElementById('checkHistorialComisionCobrador1').checked=true
		document.getElementById('checkHistorialComisionCobrador2').checked=false
		document.getElementById('inptBuscarComisionCobradorF1').value = "";
	    document.getElementById('inptBuscarComisionCobradorF2').value = "";	
	}else{		
		document.getElementById('checkHistorialComisionCobrador1').checked=false
		document.getElementById('checkHistorialComisionCobrador2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarComisionCobradorF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarComisionCobradorF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
var codCobradorComision = "";
var registrocargadocomisioncobrador="";
var totalregistrocomisioncobrador="";
var controldebusquedadComisionCobrador=false
function cancelarComisionCobrador(){
	controldebusquedadComisionCobrador=false
	document.getElementById("divProgressComisionCobrador").style.backgroundColor='#ff5722'
}
function buscarcomisioncobrador() {
if(controlacceso("VERINFORMEDECOMISIONCOBRADOR","accion")==false){return;}
	var fecha1 = document.getElementById('inptBuscarComisionCobradorF1').value
	var fecha2 = document.getElementById('inptBuscarComisionCobradorF2').value
	var zona = document.getElementById('inputSelectZonaComisionCobrador').value
	var cobrado = document.getElementById('inptBuscarComisionCobrador1').value
	var fechafiltro = document.getElementById('inptBuscarComisionCobrador2').value
	if(document.getElementById('checkHistorialComisionCobrador2').checked==true){
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
		return
	}
	}else{
	fecha1 =""
	fecha2 = ""
	}
	if(controldebusquedadComisionCobrador==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadComisionCobrador=true
	document.getElementById("table_comision_cobrador").innerHTML = paginacargando
	document.getElementById("tbProcessComisionCobrador").style.display="none"
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cobrado": cobrado,
		"fechafiltro": fechafiltro,
		"zona": zona,
		"funt": "comisioncobrador"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
			document.getElementById("table_comision_cobrador").innerHTML = ''
			controldebusquedadComisionCobrador=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_cobrador").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_comision_cobrador").innerHTML = datos_buscados
					document.getElementById("inptTotalRecaudadoComision").value = datos[3];
					document.getElementById("inptTotalRegistoComision").value = datos[4];
					document.getElementById("inptTotalComision").value = datos[5];
						registrocargadocomisioncobrador=datos[99];
					totalregistrocomisioncobrador=datos[100];
						 if(totalregistrocomisioncobrador>registrocargadocomisioncobrador){
						 	var porce=((registrocargadocomisioncobrador*100)/totalregistrocomisioncobrador).toFixed(0)
	                       document.getElementById("divProgressComisionCobrador").style.width=porce+"%"
						 document.getElementById("table_comision_cobrador").innerHTML += "<div id='table_comision_mas_cobrador'></div>"
						  buscarmascomisioncobrador();
					 }else{
						 controldebusquedadComisionCobrador=false
					 }
					
				}
			} catch (error) {
				controldebusquedadComisionCobrador=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
		});
}
function buscarmascomisioncobrador(c) {
if(controlacceso("VERINFORMEDECOMISIONCOBRADOR","accion")==false){return;}
	var fecha1 = document.getElementById('inptBuscarComisionCobradorF1').value
	var fecha2 = document.getElementById('inptBuscarComisionCobradorF2').value
	var zona = document.getElementById('inputSelectZonaComisionCobrador').value
	var cobrado = document.getElementById('inptBuscarComisionCobrador1').value
	var fechafiltro = document.getElementById('inptBuscarComisionCobrador2').value
	if(document.getElementById('checkHistorialComisionCobrador2').checked==true){
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
		return
	}
	}else{
	fecha1 =""
	fecha2 = ""
	}
	if(c=="1"){
		controldebusquedadComisionCobrador=true
	}
	if(controldebusquedadComisionCobrador==false){
		return
}
controldebusquedadComisionCobrador=true
	document.getElementById("table_comision_mas_cobrador").innerHTML = paginacargando
	document.getElementById("tbProcessComisionCobrador").style.display=""
	document.getElementById("divProgressComisionCobrador").style.backgroundColor=''
	var totalrecaudacion=document.getElementById("inptTotalRecaudadoComision").value;
	var totalcomision=document.getElementById("inptTotalComision").value ;
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cobrado": cobrado,
		"fechafiltro": fechafiltro,
		"zona": zona,
		"totalrecaudacion": totalrecaudacion,
		"totalcomision": totalcomision,
		"registrocargado": registrocargadocomisioncobrador,
		"funt": "mascomisioncobrador"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
			document.getElementById("table_comision_mas_cobrador").innerHTML = ''
			document.getElementById("divProgressComisionCobrador").style.backgroundColor='#ff5722'
			controldebusquedadComisionCobrador=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_mas_cobrador").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("table_comision_mas_cobrador").innerHTML = datos_buscados
					document.getElementById("inptTotalRecaudadoComision").value = datos[3];
					document.getElementById("inptTotalRegistoComision").value = datos[4];
					document.getElementById("inptTotalComision").value = datos[5];
						registrocargadocomisioncobrador=datos[99];
					
						 if(totalregistrocomisioncobrador>registrocargadocomisioncobrador){
						 	var porce=((registrocargadocomisioncobrador*100)/totalregistrocomisioncobrador).toFixed(0)
	                       document.getElementById("divProgressComisionCobrador").style.width=porce+"%"
						 document.getElementById("table_comision_mas_cobrador").innerHTML += "<div id='table_comision_mas_cobrador'></div>"
						 document.getElementById("table_comision_mas_cobrador").id=""
						  buscarmascomisioncobrador();
					 }else{
						 	document.getElementById("tbProcessComisionCobrador").style.display="none"
						 controldebusquedadComisionCobrador=false
					 }
					
				}
			} catch (error) {
				document.getElementById("divProgressComisionCobrador").style.backgroundColor='#ff5722'
				controldebusquedadComisionCobrador=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
		});
}


/*
COMISION VENDEDOR
*/
function verCerrarInformeComisionVendedor(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divComisionvendedor").style.display==""){
		if(controldebusquedadComisionVendedor==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
		document.getElementById("divMinimizadoComisionVendedor").style.display="none"
		limpiarcamposbuscadorcomisionvendedor()
document.getElementById("tdEfectoComisionVendedor").className="magictime vanishOut"
	$("div[id=divComisionvendedor]").fadeOut(500);	
		
	}else{		
	if(controlacceso("VERINFORMEDECOMISIONVENDEDOR","accion")==false){return;}
		document.getElementById("divComisionvendedor").style.display=""
		 document.getElementById("tdEfectoComisionVendedor").className="magictime slideDownReturn"
	}
}
function minimizarInformeComisionVendedor(){
document.getElementById("tdEfectoComisionVendedor").className="magictime slideDown"
	$("div[id=divComisionvendedor]").fadeOut(500);	
	document.getElementById("divMinimizadoComisionVendedor").style.display="";
}
function limpiarcamposbuscadorcomisionvendedor(){
	if(controldebusquedadComisionVendedor==true){

	return
}
	document.getElementById('inptBuscarComisionVendedorF1').value=""
	document.getElementById('inptBuscarComisionVendedorF2').value=""
	document.getElementById('inptBuscarComisionVendedor1').value=""
	 document.getElementById('inptBuscarComisionVendedor2').value=""
	 document.getElementById('inptBuscarComisionVendedor2').value=""
	 document.getElementById("table_comision_vendedor").innerHTML = ""
    document.getElementById("inptTotalRecaudadoComisionVendedor").value = ""
		document.getElementById("inptTotalVentaComisionVendedor").value = ""
	document.getElementById("inptTotalRegistoComisionVendedor").value = ""
	document.getElementById("tbProcessComisionVendedor").style.display = "none"
}
function checkHistorialComisionVendedor(d){	
	if(d=="1"){
		document.getElementById('checkHistorialComisionVendedor1').checked=true
		document.getElementById('checkHistorialComisionVendedor2').checked=false
		document.getElementById('inptBuscarComisionCobradorF1').value = "";
	    document.getElementById('inptBuscarComisionCobradorF2').value = "";	
	}else{		
		document.getElementById('checkHistorialComisionVendedor1').checked=false
		document.getElementById('checkHistorialComisionVendedor2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarComisionVendedorF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarComisionVendedorF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
var codvendedorComision = "";
var registrocargadocomisionvendedor="";
var totalregistrocomisionvendedor="";
var controldebusquedadComisionVendedor=false
function cancelarComisionVendedor(){
	controldebusquedadComisionVendedor=false
	document.getElementById("divProgressComisionVendedor").style.backgroundColor='#ff5722'
}
function buscarcomisionvendedor() {
if(controlacceso("VERINFORMEDECOMISIONVENDEDOR","accion")==false){return;}
	var fecha1 = document.getElementById('inptBuscarComisionVendedorF1').value
	var fecha2 = document.getElementById('inptBuscarComisionVendedorF2').value
	var vendedor = document.getElementById('inptBuscarComisionVendedor1').value
	var fechafiltro = document.getElementById('inptBuscarComisionVendedor2').value
	var producto = document.getElementById('inptBuscarComisionVendedor3').value
	var cliente = document.getElementById('inptBuscarComisionVendedorCliente').value
	if(document.getElementById('checkHistorialComisionVendedor2').checked==true){
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
		return
	}
	}else{
	fecha1 = ""
	fecha2 = ""
	}
	if(controldebusquedadComisionVendedor==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
	var Descuento="";
	var Flete="";
	if(document.getElementById('checkVendedorDescuento').checked==true){
		Descuento ="SI";
	}
	if(document.getElementById('checkVendedorFlete').checked==true){
		Flete ="SI";
	}
	
	var Local = document.getElementById('inptBuscarHistorialLocal').value
	
	controldebusquedadComisionVendedor=true
	document.getElementById("table_comision_vendedor").innerHTML = paginacargando
	document.getElementById("tbProcessComisionVendedor").style.display = "none"
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"vendedor": vendedor,
		"producto": producto,
		"fechafiltro": fechafiltro,
		"Descuento": Descuento,
		"Flete": Flete,
		"cliente": cliente,	
		"Local": Local,			
		"funt": "comisionvendedor"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
			document.getElementById("table_comision_vendedor").innerHTML = ''
			controldebusquedadComisionVendedor=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_vendedor").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_comision_vendedor").innerHTML = datos_buscados
					document.getElementById("inptTotalRecaudadoComisionVendedor").value = datos[3];
					document.getElementById("inptTotalVentaComisionVendedor").value = datos[4];
					document.getElementById("inptTotalRegistoComisionVendedor").value = datos[5];
					document.getElementById("inptTotalDescuentoComisionVendedor").value = datos[101];
						registrocargadocomisionvendedor=datos[99];
					totalregistrocomisionvendedor=datos[100];
					
						 if(totalregistrocomisionvendedor>registrocargadocomisionvendedor){
						 	var porce=((registrocargadocomisionvendedor*100)/totalregistrocomisionvendedor).toFixed(0)
	document.getElementById("divProgressComisionVendedor").style.width=porce+"%"
						 document.getElementById("table_comision_vendedor").innerHTML += "<div id='table_comision_mas_vendedor'></div>"
						  buscarmascomisionvendedor();
					 }else{
						 controldebusquedadComisionVendedor=false
					 }
					
				}
			} catch (error) {
				controldebusquedadComisionVendedor=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
			}
	});
}
function buscarmascomisionvendedor(c) {
if(controlacceso("VERINFORMEDECOMISIONVENDEDOR","accion")==false){return;}
	var fecha1 = document.getElementById('inptBuscarComisionVendedorF1').value
	var fecha2 = document.getElementById('inptBuscarComisionVendedorF2').value
	var vendedor = document.getElementById('inptBuscarComisionVendedor1').value
	var fechafiltro = document.getElementById('inptBuscarComisionVendedor2').value
	var producto = document.getElementById('inptBuscarComisionVendedor3').value
	var cliente = document.getElementById('inptBuscarComisionVendedorCliente').value
	if(document.getElementById('checkHistorialComisionVendedor2').checked==true){
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
		return
	}
	}else{
	fecha1 = ""
	fecha2 = ""
	}
	if(c=="1"){
		controldebusquedadComisionVendedor=true
	}
	if(controldebusquedadComisionVendedor==false){
		
	return
}


var Descuento="";
	var Flete="";
	if(document.getElementById('checkVendedorDescuento').checked==true){
		Descuento ="SI";
	}
	if(document.getElementById('checkVendedorFlete').checked==true){
		Flete ="SI";
	}
	var Local = document.getElementById('inptBuscarHistorialLocal').value
	controldebusquedadComisionVendedor=true
	document.getElementById("table_comision_mas_vendedor").innerHTML = paginacargando
	document.getElementById("tbProcessComisionVendedor").style.display = ""
	
	var totalDescuento = document.getElementById("inptTotalDescuentoComisionVendedor").value;
	totalDescuento =QuitarSeparadorMilValor(totalDescuento);
	var totalventa=document.getElementById("inptTotalVentaComisionVendedor").value;
	var totalcomision=document.getElementById("inptTotalRecaudadoComisionVendedor").value;
	var registroscargados=document.getElementById("inptTotalRegistoComisionVendedor").value;
	document.getElementById("divProgressComisionVendedor").style.backgroundColor=''
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"vendedor": vendedor,
		"fechafiltro": fechafiltro,
		"registrocargado": registrocargadocomisionvendedor,
		"totalcomision": totalcomision,
		"totalventa": totalventa,
		"producto": producto,
		"registroscargados": registroscargados,
		"Descuento": Descuento,
		"Flete": Flete,
		"cliente": cliente,		
		"Local": Local,	
		"totalDescuento": totalDescuento,
		"funt": "mascomisionvendedor"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmdetalleventa.php",
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
			document.getElementById("table_comision_mas_vendedor").innerHTML = ''
			document.getElementById("divProgressComisionVendedor").style.backgroundColor='#ff5722'
			controldebusquedadComisionVendedor=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_mas_vendedor").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_comision_mas_vendedor").innerHTML = datos_buscados
					document.getElementById("inptTotalRecaudadoComisionVendedor").value = datos[3];
					document.getElementById("inptTotalVentaComisionVendedor").value = datos[4];
					document.getElementById("inptTotalRegistoComisionVendedor").value = datos[5];
					document.getElementById("inptTotalDescuentoComisionVendedor").value = datos[101];
						registrocargadocomisionvendedor=datos[99];
						 if(totalregistrocomisionvendedor>registrocargadocomisionvendedor){
						 	var porce=((registrocargadocomisionvendedor*100)/totalregistrocomisionvendedor).toFixed(0)
	document.getElementById("divProgressComisionVendedor").style.width=porce+"%"
						 document.getElementById("table_comision_mas_vendedor").innerHTML += "<div id='table_comision_mas_vendedor'></div>"
						 document.getElementById("table_comision_mas_vendedor").id=""
						  buscarmascomisionvendedor();
					 }else{
						 document.getElementById("tbProcessComisionVendedor").style.display = "none"
						 controldebusquedadComisionVendedor=false
					 }
					
				}
			} catch (error) {
				document.getElementById("divProgressComisionVendedor").style.backgroundColor='#ff5722'
				controldebusquedadComisionVendedor=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
			}
	});
}


/*
PAGOS ELIMINADOS
*/
function verCerrarInformePagosEliminados(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divPagosEliminados").style.display==""){
		document.getElementById("divMinimizadoInformePagosEliminados").style.display="none"
		limpiarcamposbuscadorInformePagosEliminados()
document.getElementById("tdEfectoPagosEliminados").className="magictime vanishOut"
	$("div[id=divPagosEliminados]").fadeOut(500);			
	}else{	
if(controlacceso("VERINFORMEDEPAGOSELIMINADOS","accion")==false){return;}	
		document.getElementById("divPagosEliminados").style.display=""
		 document.getElementById("tdEfectoPagosEliminados").className="magictime slideDownReturn"
	}
}
function minimizarInformePagosEliminados(){
document.getElementById("tdEfectoPagosEliminados").className="magictime slideDown"
	$("div[id=divPagosEliminados]").fadeOut(500);	
	document.getElementById("divMinimizadoInformePagosEliminados").style.display="";
}
function limpiarcamposbuscadorInformePagosEliminados(){
	document.getElementById('inptBuscarComisionPagosEliminadosF1').value=""
	document.getElementById('inptBuscarComisionPagosEliminadosF2').value=""
	 document.getElementById("table_pagos_eliminados_historial").innerHTML = ""
    document.getElementById("inptTotalRegistoPagosEliminados").value = ""
}
function checkHistorialPagosEliminados(d){	
	if(d=="1"){
		document.getElementById('checkHistorialPagosEliminados1').checked=true
		document.getElementById('checkHistorialPagosEliminados2').checked=false
		document.getElementById('inptBuscarComisionPagosEliminadosF1').value = "";
	    document.getElementById('inptBuscarComisionPagosEliminadosF2').value = "";	
	}else{		
		document.getElementById('checkHistorialPagosEliminados1').checked=false
		document.getElementById('checkHistorialPagosEliminados2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarComisionPagosEliminadosF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarComisionPagosEliminadosF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
function buscarpagoeliminados() {
if(controlacceso("VERINFORMEDEPAGOSELIMINADOS","accion")==false){return;}	
	var fecha1 = document.getElementById('inptBuscarComisionPagosEliminadosF1').value
	var fecha2 = document.getElementById('inptBuscarComisionPagosEliminadosF2').value

	if(document.getElementById('checkHistorialPagosEliminados2').checked==true){
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
		return
	}
	}else{
	fecha1 = ""
	fecha2 = ""
	}
	
	document.getElementById("table_pagos_eliminados_historial").innerHTML = paginacargando
	document.getElementById("inptTotalRegistoPagosEliminados").value =""
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"funt": "buscarpagoseliminados"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
			document.getElementById("table_pagos_eliminados_historial").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_pagos_eliminados_historial").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_pagos_eliminados_historial").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoPagosEliminados").value = datos[3];
					
					
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
COMPRAS ELIMINADOS
*/
function verCerrarInformeComprasEliminados(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divComprasEliminados").style.display==""){
		document.getElementById("divMinimizadoInformeComprasEliminados").style.display="none"
		limpiarcamposbuscadorInformePagosEliminados()
document.getElementById("tdEfectoComprasEliminados").className="magictime vanishOut"
	$("div[id=divComprasEliminados]").fadeOut(500);			
	}else{	
if(controlacceso("VERINFORMEDECOMPRASELIMINADO","accion")==false){return;}	
		document.getElementById("divComprasEliminados").style.display=""
		 document.getElementById("tdEfectoComprasEliminados").className="magictime slideDownReturn"
	}
}
function minimizarInformeComprasEliminados(){
document.getElementById("tdEfectoComprasEliminados").className="magictime slideDown"
	$("div[id=divComprasEliminados]").fadeOut(500);	
	document.getElementById("divMinimizadoInformeComprasEliminados").style.display="";
}
function limpiarcamposbuscadorInformePagosEliminados(){
	document.getElementById('inptBuscarComprasEliminadosF1').value=""
	document.getElementById('inptBuscarComprasEliminadosF2').value=""
	document.getElementById('inptBuscarComprasEliminados3').value=""
	 document.getElementById("table_compras_eliminados_historial").innerHTML = ""
    document.getElementById("inptTotalRegistoComprasEliminados").value = ""
}
function checkHistorialComprasEliminados(d){	
	if(d=="1"){
		document.getElementById('checkHistorialComprasEliminados1').checked=true
		document.getElementById('checkHistorialComprasEliminados2').checked=false
		document.getElementById('inptBuscarComprasEliminadosF1').value = "";
	    document.getElementById('inptBuscarComprasEliminadosF2').value = "";	
	}else{		
		document.getElementById('checkHistorialComprasEliminados1').checked=false
		document.getElementById('checkHistorialComprasEliminados2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarComprasEliminadosF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarComprasEliminadosF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
function buscarcompraseliminados() {
if(controlacceso("VERINFORMEDECOMPRASELIMINADO","accion")==false){return;}	
	var fecha1 = document.getElementById('inptBuscarComprasEliminadosF1').value
	var fecha2 = document.getElementById('inptBuscarComprasEliminadosF2').value
	var nrocompra = document.getElementById('inptBuscarComprasEliminados3').value

	if(document.getElementById('checkHistorialComprasEliminados2').checked==true){
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
		return
	}
	}else{
	fecha1 = ""
	fecha2 = ""
	}
	
	document.getElementById("table_compras_eliminados_historial").innerHTML = paginacargando
	document.getElementById("inptTotalRegistoComprasEliminados").value =""
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"nrocompra": nrocompra,
		"funt": "buscarcompraseliminados"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmcompra.php",
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
			document.getElementById("table_compras_eliminados_historial").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_compras_eliminados_historial").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_compras_eliminados_historial").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistoComprasEliminados").value = datos[3];
					
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
			}
	});
}


/*VER HISTORIAL DEVOLUCIONES*/
function verCerrarInformeDevoluciones(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divInformeDevoluciones").style.display==""){
		document.getElementById("divMinimizadoInformeCambios").style.display="none"
document.getElementById("tdEfectoInformeDevoluciones").className="magictime vanishOut"
	$("div[id=divInformeDevoluciones]").fadeOut(500);	
		limpiarCamposBuscadorDevoluciones()
	}else{		
	if(controlacceso("VERINFORMECAMBIOS","accion")==false){return;}
		document.getElementById("divInformeDevoluciones").style.display=""
document.getElementById("tdEfectoInformeDevoluciones").className="magictime slideDownReturn"
		
	}
}
function limpiarCamposBuscadorDevoluciones(){
	document.getElementById("inptBuscarDevolucionesF1").value=""
	document.getElementById("inptBuscarDevolucionesF2").value=""
	document.getElementById("inptBuscarDevoluciones1").value=""
	document.getElementById("inptBuscarDevoluciones2").value=""
	document.getElementById("inptTotalRegistroDevolucion").value=""
	document.getElementById("table_comision_devolucion").innerHTML=""
}
function minimizarventanaDevoluciones(){
	
	document.getElementById("divMinimizadoInformeCambios").style.display=""

document.getElementById("tdEfectoInformeDevoluciones").className="magictime slideDown"
	$("div[id=divInformeDevoluciones]").fadeOut(500);	
	
}
function checkHistorialDevoluciones(d){	
	if(d=="1"){
		document.getElementById('checkHistorialDevoluciones1').checked=true
		document.getElementById('checkHistorialDevoluciones2').checked=false
		document.getElementById('inptBuscarDevolucionesF1').value = "";
	    document.getElementById('inptBuscarDevolucionesF2').value = "";	
	}else{		
		document.getElementById('checkHistorialDevoluciones1').checked=false
		document.getElementById('checkHistorialDevoluciones2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarDevolucionesF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarDevolucionesF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
function buscarhistorialdevoluciones() {
	if(controlacceso("VERINFORMECAMBIOS","accion")==false){return;}
	var fecha1 = document.getElementById("inptBuscarDevolucionesF1").value
	var fecha2 = document.getElementById("inptBuscarDevolucionesF2").value
	var nrofactura = document.getElementById("inptBuscarDevoluciones1").value
	var fechafiltro = document.getElementById("inptBuscarDevoluciones2").value
	var cod_local = document.getElementById("inptlocalInformeDevoluciones").value
	if (document.getElementById('checkHistorialDevoluciones2').checked==true) {
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
			return false;
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
			return false;
		}
	}else{
	fecha1 = ""
	fecha2 = ""
	}

	document.getElementById("table_comision_devolucion").innerHTML = paginacargando

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"nrofactura": nrofactura,
		"fechafiltro": fechafiltro,
		"cod_local": cod_local,
		"funt": "buscarCambiosRealizados"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_comision_devolucion").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_devolucion").innerHTML = ''
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

					document.getElementById("table_comision_devolucion").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistroDevolucion").value = datos[3]




				}
			} catch (error) {

			}
		}
	});


}
/*
INFORME DE EVALUACIÓN
*/
function verCerrarInformeDeEvaluacion(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divInformeEvaluacion").style.display==""){
limpiarcamposinformeevaluacion()
		document.getElementById("divMinimizadoInformeEvaluacion").style.display="none"
document.getElementById("tdEfectoInformeEvaluacion").className="magictime vanishOut"
	$("div[id=divInformeEvaluacion]").fadeOut(500);	
	}else{	
if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
		document.getElementById("divInformeEvaluacion").style.display=""
document.getElementById("tdEfectoInformeEvaluacion").className="magictime slideDownReturn"
		
	}
}
function minimizarinformeevaluacion(){
	//document.getElementById("divInformeEvaluacion").style.display = "none";
	 document.getElementById("divMinimizadoInformeEvaluacion").style.display = "";
document.getElementById("tdEfectoInformeEvaluacion").className="magictime slideDown"
	$("div[id=divInformeEvaluacion]").fadeOut(500);	
}
function limpiarcamposinformeevaluacion(){
 document.getElementById("inptBuscarEvaluacionF1").value=""
 document.getElementById("inptBuscarEvaluacionF2").value=""
 document.getElementById("inptRegistroEvaluacionGastos").value=""
 document.getElementById("inptTotalEvaluacionGastos").value=""
 document.getElementById("table_evaluacion_gasto").innerHTML=""
 document.getElementById("inptRegistroEvaluacionPagos").value=""
 document.getElementById("inptTotalEvaluacionPagos").value=""
 document.getElementById("table_evaluacion_pagos").innerHTML=""
 document.getElementById("inptRegistroEvaluacionProductosVendidos").value=""
 document.getElementById("inptTotalEvaluacionProductosVendidos").value=""
 document.getElementById("table_evaluacion_producto_vendidos").innerHTML=""
 document.getElementById("inptRegistroEvaluacionProductoComprados").value=""
 document.getElementById("inptTotalEvaluacionProductosComprados").value=""
 document.getElementById("table_evaluacion_producto_comprados").innerHTML=""
 document.getElementById("inptRegistroEvaluacionPagosCompras").value=""
 document.getElementById("inptTotalEvaluacionPagosCompras").value=""
 document.getElementById("table_evaluacion_pagos_compras").innerHTML=""
}
function verCerrarVentanasEvaluacionInforme(d){
	document.getElementById("btnHistoriaEvaluacion1").style=''
	document.getElementById("btnHistoriaEvaluacion2").style=''
	document.getElementById("btnHistoriaEvaluacion4").style=''
	document.getElementById("btnHistoriaEvaluacion5").style=''
	document.getElementById("btnHistoriaEvaluacion6").style=''
	document.getElementById("divEvaluacionGastos").style.display='none'
	document.getElementById("divEvaluacionPagoCuota").style.display='none'
	document.getElementById("divEvualcionProductosComprados").style.display='none'
	document.getElementById("divEvualcionProductosVendidos").style.display='none'
	document.getElementById("divEvualcionPagosCompras").style.display='none'
	if(d=="1"){
		document.getElementById("btnHistoriaEvaluacion1").style='background-color:#ff9800;color:#fff'
		document.getElementById("divEvaluacionGastos").style.display=''
	}
	if(d=="2"){		
		 	document.getElementById("btnHistoriaEvaluacion2").style='background-color:#ff9800;color:#fff'
		document.getElementById("divEvaluacionPagoCuota").style.display=''
	}
		if(d=="3"){		
		document.getElementById("btnHistoriaEvaluacion3").style='background-color:#ff9800;color:#fff'
			document.getElementById("divEvaluacionEntrega").style.display=''			
		}
		if(d=="4"){	
		document.getElementById("btnHistoriaEvaluacion4").style='background-color:#ff9800;color:#fff'
			document.getElementById("divEvualcionProductosComprados").style.display=''			
		}
		if(d=="5"){	
		document.getElementById("btnHistoriaEvaluacion5").style='background-color:#ff9800;color:#fff'
			document.getElementById("divEvualcionProductosVendidos").style.display=''			
		}
		if(d=="6"){	
		document.getElementById("btnHistoriaEvaluacion6").style='background-color:#ff9800;color:#fff'
			document.getElementById("divEvualcionPagosCompras").style.display=''			
		}	
}
function buscarevaluacion(){
	buscarevaluacionGasto()
	buscarevaluacionPago()
	buscarevaluacionProductosvendidos()
	buscarevaluacionProductosComprados()
	buscarevaluacionPagosCompra()		
}
function buscarevaluacionGasto() {
	if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inptBuscarEvaluacionF1").value
	var fecha2 = document.getElementById("inptBuscarEvaluacionF2").value
	var local = document.getElementById("inptlocalInformeEvaluacion").value
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
		return false;
	}
	document.getElementById("table_evaluacion_gasto").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "evaluacionGasto"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmgasto.php",
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
			document.getElementById("table_evaluacion_gasto").innerHTML = ""	
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
				document.getElementById("table_evaluacion_gasto").innerHTML = ""	
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];				
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var pagina = datos[2];
					document.getElementById("table_evaluacion_gasto").innerHTML = pagina
		document.getElementById("inptRegistroEvaluacionGastos").value = datos[3]
	document.getElementById("inptTotalEvaluacionGastos").value = datos[4]	
	
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
	}
function buscarevaluacionPago() {
	if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inptBuscarEvaluacionF1").value
	var fecha2 = document.getElementById("inptBuscarEvaluacionF2").value
	var local = document.getElementById("inptlocalInformeEvaluacion").value
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
		return false;
	}
	document.getElementById("table_evaluacion_pagos").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "evaluacionpagosventa"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmgasto.php",
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
	document.getElementById("table_evaluacion_pagos").innerHTML = ""	
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)				
	document.getElementById("table_evaluacion_pagos").innerHTML = ""	
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var pagina = datos[2];
					document.getElementById("table_evaluacion_pagos").innerHTML = pagina	
					document.getElementById("inptRegistroEvaluacionPagos").value = datos[3]
					document.getElementById("inptTotalEvaluacionPagos").value = datos[4]	
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarevaluacionProductosvendidos() {
	if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inptBuscarEvaluacionF1").value
	var fecha2 = document.getElementById("inptBuscarEvaluacionF2").value
	var local = document.getElementById("inptlocalInformeEvaluacion").value
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
		return false;
	}
	document.getElementById("table_evaluacion_producto_vendidos").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "evaluacionproductodvendidos"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmgasto.php",
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
	document.getElementById("table_evaluacion_producto_vendidos").innerHTML = ""
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)				
	document.getElementById("table_evaluacion_producto_vendidos").innerHTML = ""
	try {	
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
			Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var pagina = datos[2];
					document.getElementById("table_evaluacion_producto_vendidos").innerHTML = pagina
	document.getElementById("inptRegistroEvaluacionProductosVendidos").value = datos[3]
	document.getElementById("inptTotalEvaluacionProductosVendidos").value = datos[4]
	
				}				
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarevaluacionProductosComprados() {
	if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inptBuscarEvaluacionF1").value
	var fecha2 = document.getElementById("inptBuscarEvaluacionF2").value
	var local = document.getElementById("inptlocalInformeEvaluacion").value
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
		return false;
	}
	document.getElementById("table_evaluacion_producto_comprados").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "evaluacionproductodcomprados"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmgasto.php",
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
	document.getElementById("table_evaluacion_producto_comprados").innerHTML = ""
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)				
	document.getElementById("table_evaluacion_producto_comprados").innerHTML = ""
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var pagina = datos[2];
				document.getElementById("table_evaluacion_producto_comprados").innerHTML = pagina		
	document.getElementById("inptRegistroEvaluacionProductoComprados").value = datos[3]
	document.getElementById("inptTotalEvaluacionProductosComprados").value = datos[4]
	
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function buscarevaluacionPagosCompra() {
	if(controlacceso("VERINFORMEEVALUACION","accion")==false){return;}	
	var fecha1 = document.getElementById("inptBuscarEvaluacionF1").value
	var fecha2 = document.getElementById("inptBuscarEvaluacionF2").value
	var local = document.getElementById("inptlocalInformeEvaluacion").value
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return false;
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE FIN", "#")
		return false;
	}
	document.getElementById("table_evaluacion_pagos_compras").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "evaluacionpagoscomprados"
	};
	$.ajax({

		data: datos,
		url: "/GoodVentaByR/php_system/abmgasto.php",
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
	document.getElementById("table_evaluacion_pagos_compras").innerHTML = ""
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)			
	document.getElementById("table_evaluacion_pagos_compras").innerHTML = ""
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   			   
					var paginaCompras = datos[2];
					document.getElementById("table_evaluacion_pagos_compras").innerHTML = paginaCompras
					document.getElementById("inptRegistroEvaluacionPagosCompras").value = datos[3]
					document.getElementById("inptTotalEvaluacionPagosCompras").value = datos[4]	
					
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
INFORME DE INVENTARIO
*/
function verCerrarInformeInventario(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divInformeInventario").style.display==""){
		if(controldebusquedadInventario==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
		document.getElementById("divMinimizadoInformeInventario").style.display="none"
document.getElementById("tdEfectoInformeInventario").className="magictime vanishOut"
	$("div[id=divInformeInventario]").fadeOut(500);	
		limpiarcamposbuscarinventario()
	}else{
if(controlacceso("VERINFORMEDEINVENTARIO","accion")==false){return;}			
		document.getElementById("divInformeInventario").style.display=""
	document.getElementById("tdEfectoInformeInventario").className="magictime slideDownReturn"
	
	}
}
function limpiarcamposbuscarinventario(){
	if(controldebusquedadInventario==true){
	
	return
}
	document.getElementById("inptBuscarInventario1").value=""
	document.getElementById("inptBuscarInventario2").value=""
	document.getElementById("inptBuscarInventario3").value=""
	document.getElementById("inptTotalRegistroInventario").value=""
	document.getElementById("inptTotalRegistroProductosCostoInventario").value=""
	document.getElementById("table_comision_productosInventario").innerHTML=""
	document.getElementById("tbProcessInventario").style.display="none"
}
function minimizarInventario(){
		document.getElementById("divMinimizadoInformeInventario").style.display=""
document.getElementById("tdEfectoInformeInventario").className="magictime slideDown"
	$("div[id=divInformeInventario]").fadeOut(500);
}
var registrocargadoinventario="";
var totalregistroinventario="";
var controldebusquedadInventario=false
function cancelarInventario(){
	controldebusquedadInventario=false
	document.getElementById("divProgressInventario").style.backgroundColor='#ff5722'
}
function buscarproductosinventario() {
if(controlacceso("VERINFORMEDEINVENTARIO","accion")==false){return;}	
	var codproducto = document.getElementById('inptBuscarInventario1').value
	var producto = document.getElementById('inptBuscarInventario2').value
	var stock = document.getElementById('inptBuscarInventario3').value
	var local = document.getElementById('inptlocalProductoBuscarInventario').value
	var Categoria = document.getElementById('inptCategoriaProductoBuscarInventario').value
	var Marcas = document.getElementById('inptMarcasProductoBuscarInventario').value
		if(controldebusquedadInventario==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadInventario=true
	document.getElementById("table_comision_productosInventario").innerHTML = paginacargando
document.getElementById("tbProcessInventario").style.display='none'
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codproducto": codproducto,
		"producto": producto,
		"stock": stock,
		"local": local,
		"Categoria": Categoria,
		"Marcas": Marcas,
		"funt": "buscarInventario"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_comision_productosInventario").innerHTML = ''
			controldebusquedadInventario=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_comision_productosInventario").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   			   							   
					var datos_buscados = datos[2];
					
					document.getElementById("table_comision_productosInventario").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistroInventario").value = datos[3];
					document.getElementById("inptTotalRegistroProductosCostoInventario").value = datos[4];
					
					registrocargadoinventario=datos[99];
					totalregistroinventario=datos[100];
		
						 if(totalregistroinventario>registrocargadoinventario){
						 	var porce=((registrocargadoinventario*100)/totalregistroinventario).toFixed(0)
	document.getElementById("divProgressInventario").style.width=porce+"%"
						 document.getElementById("table_comision_productosInventario").innerHTML += "<div id='table_mas_comision_productosInventario'></div>"
						  buscarmasproductosinventario();
					 }else{
						 controldebusquedadInventario=false
					 }
					
					
				}
			} catch (error) {
				controldebusquedadInventario=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
	}
	function buscarmasproductosinventario(c) {
if(controlacceso("VERINFORMEDEINVENTARIO","accion")==false){return;}	
	var codproducto = document.getElementById('inptBuscarInventario1').value
	var producto = document.getElementById('inptBuscarInventario2').value
	var stock = document.getElementById('inptBuscarInventario3').value
	var local = document.getElementById('inptlocalProductoBuscarInventario').value
	var Categoria = document.getElementById('inptCategoriaProductoBuscarInventario').value
	var Marcas = document.getElementById('inptMarcasProductoBuscarInventario').value
	if(c=="1"){
	controldebusquedadInventario=true	
	}
		if(controldebusquedadInventario==false){
		
	return
}
controldebusquedadInventario=true
	document.getElementById("table_mas_comision_productosInventario").innerHTML = paginacargando
document.getElementById("tbProcessInventario").style.display=''
document.getElementById("divProgressInventario").style.backgroundColor=''
var totalcostos=document.getElementById("inptTotalRegistroProductosCostoInventario").value
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"codproducto": codproducto,
		"producto": producto,
		"stock": stock,
		"local": local,
		"Categoria": Categoria,
		"Marcas": Marcas,
		"totalcostos": totalcostos,
		"registrocargados": registrocargadoinventario,
		"funt": "buscarMasInventario"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmproductos.php",
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
			document.getElementById("table_mas_comision_productosInventario").innerHTML = ''
			document.getElementById("divProgressInventario").style.backgroundColor='#ff5722'
			controldebusquedadInventario=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_mas_comision_productosInventario").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   			   							   
					var datos_buscados = datos[2];
					
					document.getElementById("table_mas_comision_productosInventario").innerHTML = datos_buscados
					document.getElementById("inptTotalRegistroInventario").value = datos[3];
					document.getElementById("inptTotalRegistroProductosCostoInventario").value = datos[4];
					
					registrocargadoinventario=datos[99];
		
						 if(totalregistroinventario>registrocargadoinventario){
						 	var porce=((registrocargadoinventario*100)/totalregistroinventario).toFixed(0)
	document.getElementById("divProgressInventario").style.width=porce+"%"
						 document.getElementById("table_mas_comision_productosInventario").innerHTML += "<div id='table_mas_comision_productosInventario'></div>"
						 document.getElementById("table_mas_comision_productosInventario").id=""
						  buscarmasproductosinventario();
					 }else{
						 document.getElementById("tbProcessInventario").style.display='none'
						 controldebusquedadInventario=false
					 }
					
					
				}
			} catch (error) {
				document.getElementById("divProgressInventario").style.backgroundColor='#ff5722'
				controldebusquedadInventario=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
	}
/*
GANANCIA POR VENTA
*/
function verCerrarInformeGananciasVentas(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divGananciasporventa").style.display==""){
		if(controldebusquedadGananciaPorVenta==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
limpiarcamposbuscadorgananciasporventas()
		document.getElementById("divMinimizadoInformeGananciaPorVenta").style.display="none"
document.getElementById("tdEfectoGananciaporVenta").className="magictime vanishOut"
	$("div[id=divGananciasporventa]").fadeOut(500);	
		
	}else{		
	if(controlacceso("VERINFORMEDEGANANCIAPORVENTA","accion")==false){return;}
		document.getElementById("divGananciasporventa").style.display=""
 document.getElementById("tdEfectoGananciaporVenta").className="magictime slideDownReturn"
		
	}
}
function limpiarcamposbuscadorgananciasporventas(){
	if(controldebusquedadGananciaPorVenta==true){
	return
}
	document.getElementById("inptBuscarInfGananciaVentaF1").value=""
	document.getElementById("inptBuscarInfGananciaVentaF2").value=""
	document.getElementById("inptBuscarInfGananciaVenta1").value=""
	document.getElementById("inptBuscarInfGananciaVenta2").value=""
	document.getElementById("inptBuscarInfGananciaVenta3").value=""
	document.getElementById("inptBuscarInfGananciaVenta4").value=""

	document.getElementById("inptTotalRegstroGananciasVenta").value=""
	document.getElementById("inptTotalCostoGananciasVenta").value=""
	document.getElementById("inptTotalComisionGananciasVenta").value=""
	document.getElementById("inptTotalPagadoGananciasVenta").value=""
	document.getElementById("inptTotalEvaluacionGananciasVenta").value=""
	document.getElementById("table_historial_ganancias_venta").innerHTML=""
	document.getElementById("tbProcessGananciaPorVenta").style.display="none"
}
function minimizargananciaporventas(){
	document.getElementById("divMinimizadoInformeGananciaPorVenta").style.display=""
document.getElementById("tdEfectoGananciaporVenta").className="magictime slideDown"
	$("div[id=divGananciasporventa]").fadeOut(500);	
}
function bloquearBuscarPorgananciaventa(d){
	document.getElementById('divFiltroGananciaporventa1').style.display="none";
	document.getElementById('divFiltroGananciaporventa2').style.display="none";
	document.getElementById('inptBuscarInfGananciaporventa').value=""
	document.getElementById('inptBuscarInfGananciaVentaF1').value=""
	document.getElementById('inptBuscarInfGananciaVentaF2').value=""
	if(d=="1"){
	document.getElementById('divFiltroGananciaporventa1').style.display="";
		document.getElementById('inputSelectTipoBuscarInfGananciaventa').value="2"
	}
	if(d=="2"){
	document.getElementById('divFiltroGananciaporventa2').style.display="";
		document.getElementById('inputSelectTipoBuscarInfGananciaventa').value="1"
	}	
}
var TotalRegistroCargadoGanancias=0;
function checkHistorialGananciaVenta(d){	
	if(d=="1"){
		document.getElementById('checkHistorialGananciaVenta1').checked=true
		document.getElementById('checkHistorialGananciaVenta2').checked=false
		document.getElementById('inptBuscarInfGananciaVentaF1').value = "";
	    document.getElementById('inptBuscarInfGananciaVentaF2').value = "";	
	}else{		
		document.getElementById('checkHistorialGananciaVenta1').checked=false
		document.getElementById('checkHistorialGananciaVenta2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarInfGananciaVentaF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarInfGananciaVentaF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}
var registrocargadogananciaporventa="";
var totalregistrogananciaporventa="";
var controldebusquedadGananciaPorVenta=false
function cancelarGananciaPorVenta(){
	controldebusquedadGananciaPorVenta=false
	document.getElementById("divProgressGananciaPorVenta").style.backgroundColor='#ff5722'
}
function buscargananciaventa() {
	if(controlacceso("VERINFORMEDEGANANCIAPORVENTA","accion")==false){return;}
	var nroventa = document.getElementById('inptBuscarInfGananciaVenta1').value
	var cliente = document.getElementById('inptBuscarInfGananciaVenta2').value
	var nrodocumento = document.getElementById('inptBuscarInfGananciaVenta3').value
	var fechafiltro = document.getElementById('inptBuscarInfGananciaVenta4').value
	var fecha1 = document.getElementById('inptBuscarInfGananciaVentaF1').value
	var fecha2 = document.getElementById('inptBuscarInfGananciaVentaF2').value
	var cod_local = document.getElementById('inptlocalInformeGananciaporventa').value
	var tipoventa = document.getElementById('inptBuscarInfGananciaVenta5').value
	
	if (document.getElementById('checkHistorialGananciaVenta2').checked==true) {
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
			return
		}
	}else{
	fecha1 = ""
	fecha2 = ""
	}
	
if(controldebusquedadGananciaPorVenta==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
controldebusquedadGananciaPorVenta=true
	document.getElementById("table_historial_ganancias_venta").innerHTML = paginacargando
	document.getElementById("tbProcessGananciaPorVenta").style.display="none"
		document.getElementById("inptTotalCostoGananciasVenta").value ="";
	document.getElementById("inptTotalComisionGananciasVenta").value ="";
	document.getElementById("inptTotalPagadoGananciasVenta").value = "";
	document.getElementById("inptTotalEvaluacionGananciasVenta").value = "";
	document.getElementById("inptTotalRegstroGananciasVenta").value = "";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"nroventa": nroventa,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cliente": cliente,
		"nrodocumento": nrodocumento,
		"fechafiltro": fechafiltro,
		"cod_local": cod_local,
		"tipoventa": tipoventa,
		"funt": "ganaciaventa"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_historial_ganancias_venta").innerHTML = ''
			controldebusquedadGananciaPorVenta=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_ganancias_venta").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
               Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				
				var datos_buscados = datos[2];
				document.getElementById("inptTotalCostoGananciasVenta").value = datos[3];
					document.getElementById("inptTotalComisionGananciasVenta").value = datos[4];
					document.getElementById("inptTotalPagadoGananciasVenta").value = datos[5];
					document.getElementById("inptTotalEvaluacionGananciasVenta").value = datos[6];
					document.getElementById("inptTotalRegstroGananciasVenta").value = datos[7];
				document.getElementById("table_historial_ganancias_venta").innerHTML = datos_buscados
			
				 	registrocargadogananciaporventa=datos[99];
					totalregistrogananciaporventa=datos[100];
				
						 if(totalregistrogananciaporventa>registrocargadogananciaporventa){
						 	var porce=((registrocargadogananciaporventa*100)/totalregistrogananciaporventa).toFixed(0)
	document.getElementById("divProgressGananciaPorVenta").style.width=porce+"%"
						 document.getElementById("table_historial_ganancias_venta").innerHTML += "<div id='table_historial_ganancias_venta_mas'></div>"
						  buscarmasgananciaventa();
					 }else{
						 controldebusquedadGananciaPorVenta=false
					 }
				}
			} catch (error) {
				controldebusquedadGananciaPorVenta=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
	}
function buscarmasgananciaventa(c) {
	if(controlacceso("VERINFORMEDEGANANCIAPORVENTA","accion")==false){return;}
	var nroventa = document.getElementById('inptBuscarInfGananciaVenta1').value
	var cliente = document.getElementById('inptBuscarInfGananciaVenta2').value
	var nrodocumento = document.getElementById('inptBuscarInfGananciaVenta3').value
	var fechafiltro = document.getElementById('inptBuscarInfGananciaVenta4').value
	var fecha1 = document.getElementById('inptBuscarInfGananciaVentaF1').value
	var fecha2 = document.getElementById('inptBuscarInfGananciaVentaF2').value
	var cod_local = document.getElementById('inptlocalInformeGananciaporventa').value
	var tipoventa = document.getElementById('inptBuscarInfGananciaVenta5').value
	
	if (document.getElementById('checkHistorialGananciaVenta2').checked==true) {
		if (fecha1 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
			return
		}
		if (fecha2 == "") {
			ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
			return
		}
	}else{
	fecha1 = ""
	fecha2 = ""
	}
	
	if(c=="1"){
		controldebusquedadGananciaPorVenta=true
	}
if(controldebusquedadGananciaPorVenta==false){
	return
}
controldebusquedadGananciaPorVenta=true
	document.getElementById("table_historial_ganancias_venta_mas").innerHTML = paginacargando
	document.getElementById("tbProcessGananciaPorVenta").style.display=""
	document.getElementById("divProgressGananciaPorVenta").style.backgroundColor=''
		var totalcostos=document.getElementById("inptTotalCostoGananciasVenta").value
	var totalcomision=document.getElementById("inptTotalComisionGananciasVenta").value
	var totalpagado=document.getElementById("inptTotalPagadoGananciasVenta").value
	var totalevaluacion=document.getElementById("inptTotalEvaluacionGananciasVenta").value
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"nroventa": nroventa,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cliente": cliente,
		"nrodocumento": nrodocumento,
		"fechafiltro": fechafiltro,
		"cod_local": cod_local,
		"tipoventa": tipoventa,
		"totalcostos": totalcostos,
		"totalcomision": totalcomision,
		"totalpagado": totalpagado,
		"totalevaluacion": totalevaluacion,
		"registrocargado": registrocargadogananciaporventa,
		"funt": "masganaciaventa"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_historial_ganancias_venta_mas").innerHTML = ''
			document.getElementById("divProgressGananciaPorVenta").style.backgroundColor='#ff5722'
			controldebusquedadGananciaPorVenta=false
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_historial_ganancias_venta_mas").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
               Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				
				var datos_buscados = datos[2];
				document.getElementById("inptTotalCostoGananciasVenta").value = datos[3];
					document.getElementById("inptTotalComisionGananciasVenta").value = datos[4];
					document.getElementById("inptTotalPagadoGananciasVenta").value = datos[5];
					document.getElementById("inptTotalEvaluacionGananciasVenta").value = datos[6];
					document.getElementById("inptTotalRegstroGananciasVenta").value = datos[7];
				document.getElementById("table_historial_ganancias_venta_mas").innerHTML = datos_buscados
				
				 	registrocargadogananciaporventa=datos[99];
					
						 if(totalregistrogananciaporventa>registrocargadogananciaporventa){
						 	var porce=((registrocargadogananciaporventa*100)/totalregistrogananciaporventa).toFixed(0)
	document.getElementById("divProgressGananciaPorVenta").style.width=porce+"%"
						 document.getElementById("table_historial_ganancias_venta_mas").innerHTML += "<div id='table_historial_ganancias_venta_mas'></div>"
						 document.getElementById("table_historial_ganancias_venta_mas").id=""
						  buscarmasgananciaventa();
					 }else{
						 document.getElementById("tbProcessGananciaPorVenta").style.display="none"
						 controldebusquedadGananciaPorVenta=false
					 }
				}
			} catch (error) {
				document.getElementById("divProgressGananciaPorVenta").style.backgroundColor='#ff5722'
				controldebusquedadGananciaPorVenta=false
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
	}


/*
VENTAS CANCELADAS 
*/
function verCerrarInformeVentasCanceladas(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divinfoVentasCanceladas").style.display==""){
		 if(controldebusquedadVentasCanceladas==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
		document.getElementById("divMinimizadoVentaCancelada").style.display="none"
		limpiarcamposventascanceladas()
document.getElementById("tdInfoVentasCanceladas").className="magictime vanishOut"
	$("div[id=divinfoVentasCanceladas]").fadeOut(500);	
	}else{		
	if(controlacceso("VERINFORMEDEVENTASCANCELADAS","accion")==false){return;}
		document.getElementById("divinfoVentasCanceladas").style.display=""
document.getElementById("tdInfoVentasCanceladas").className="magictime slideDownReturn"		
	}
}
function limpiarcamposventascanceladas(){	
 if(controldebusquedadVentasCanceladas==true){

	return
}
document.getElementById('inptBuscarInfVentasCanceladas1').value=""
 document.getElementById('inptBuscarInfVentasCanceladas2').value=""
 document.getElementById('inptBuscarInfVentasCanceladas3').value=""
 document.getElementById('inptlocalInformeVentaCanceladas').value=""
 document.getElementById('inptBuscarInfVentasCanceladasF1').value=""
 document.getElementById('inptBuscarInfVentasCanceladasF2').value=""
 document.getElementById('inptRegistroNroHistorialVentaCancelada').value=""
 document.getElementById('table_historial_venta_cancelado').innerHTML=""
 document.getElementById('tbProcessVentasCanceladas').style.display="none"
}
function minimizarventanacanceladas(){
	document.getElementById("divMinimizadoVentaCancelada").style.display=""
document.getElementById("tdInfoVentasCanceladas").className="magictime slideDown"
	$("div[id=divinfoVentasCanceladas]").fadeOut(500);	
}
function checkHistorialVentasCanceladas(d){	
	if(d=="1"){
		document.getElementById('checkHistorialVentasCanceladas1').checked=true
		document.getElementById('checkHistorialVentasCanceladas2').checked=false
		document.getElementById('inptBuscarInfVentasCanceladasF1').value = "";
	    document.getElementById('inptBuscarInfVentasCanceladasF2').value = "";	
	}else{		
		document.getElementById('checkHistorialVentasCanceladas1').checked=false
		document.getElementById('checkHistorialVentasCanceladas2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarInfVentasCanceladasF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarInfVentasCanceladasF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}

var registrocargadoventascanceladas="";
var totalregistroventascanceladas="";
var controldebusquedadVentasCanceladas=false
function cancelarVentasCanceladas(){
	controldebusquedadVentasCanceladas=false
	document.getElementById("divProgressVentasCanceladas").style.backgroundColor='#ff5722'
}

function buscarhistorialventaCancelada(){
	if(controlacceso("VERINFORMEDEVENTASCANCELADAS","accion")==false){return;}
  var filtrofecha=document.getElementById('inptBuscarInfVentasCanceladas1').value
 var nroventa=document.getElementById('inptBuscarInfVentasCanceladas2').value
 var cliente=document.getElementById('inptBuscarInfVentasCanceladas3').value
 var codlocal=document.getElementById('inptlocalInformeVentaCanceladas').value
 var fecha1=document.getElementById('inptBuscarInfVentasCanceladasF1').value
 var fecha2=document.getElementById('inptBuscarInfVentasCanceladasF2').value
 if(document.getElementById('checkHistorialVentasCanceladas2').checked==true){
	 if(fecha1==""){
		 	ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO","#")
		 return
	 }
	 if(fecha2==""){
		 	ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN","#")
		 return
	 }
 }else{
 fecha1=""
 fecha2=""
 }
 if(controldebusquedadVentasCanceladas==true){
		ver_vetana_informativa("CANCELE LA BUSQUEDA ACTUAL PARA CONTINUAR")
	return
}
 controldebusquedadVentasCanceladas=true
		 document.getElementById("table_historial_venta_cancelado").innerHTML=paginacargando
		 document.getElementById("tbProcessVentasCanceladas").style.display="none"
		 	obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"filtrofecha": filtrofecha,
			"fecha1": fecha1,
			"fecha2": fecha2,
			"nroventa": nroventa,
			"cliente": cliente,
			"codlocal": codlocal,
			"funt": "historialventacancelado"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_historial_venta_cancelado").innerHTML=''
			controldebusquedadVentasCanceladas=false
			},
			success: function(responseText)
			{	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_historial_venta_cancelado").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		  Respuesta=respuestaJqueryAjax(Respuesta)
		if (Respuesta == true) {					
		    var datos_buscados=datos[2];
			document.getElementById("table_historial_venta_cancelado").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroHistorialVentaCancelada").value=datos[3];	
			registrocargadoventascanceladas=datos[99];
			totalregistroventascanceladas=datos[100];
 		 if(totalregistroventascanceladas>registrocargadoventascanceladas){
	 	var porce=((registrocargadoventascanceladas*100)/totalregistroventascanceladas).toFixed(0)
    	document.getElementById("divProgressVentasCanceladas").style.width=porce+"%"
	    document.getElementById("table_historial_venta_cancelado").innerHTML += "<div id='table_historial_mas_venta_cancelado'></div>"
				  buscarMashistorialventaCancelada();
					 }else{
						 controldebusquedadVentasCanceladas=false
					 }
			}
			}catch(error)
				{
					controldebusquedadVentasCanceladas=false
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				}
			}
			});		
}
function buscarMashistorialventaCancelada(c){
	if(controlacceso("VERINFORMEDEVENTASCANCELADAS","accion")==false){return;}
  var filtrofecha=document.getElementById('inptBuscarInfVentasCanceladas1').value
 var nroventa=document.getElementById('inptBuscarInfVentasCanceladas2').value
 var cliente=document.getElementById('inptBuscarInfVentasCanceladas3').value
 var codlocal=document.getElementById('inptlocalInformeVentaCanceladas').value
 var fecha1=document.getElementById('inptBuscarInfVentasCanceladasF1').value
 var fecha2=document.getElementById('inptBuscarInfVentasCanceladasF2').value
 if(document.getElementById('checkHistorialVentasCanceladas2').checked==true){
	 if(fecha1==""){
		 	ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO","#")
		 return
	 }
	 if(fecha2==""){
		 	ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN","#")
		 return
	 }
 }else{
 fecha1=""
 fecha2=""
 }
 if(c=="1"){
	 controldebusquedadVentasCanceladas=true
 }
 if(controldebusquedadVentasCanceladas==false){
	return
}
 controldebusquedadVentasCanceladas=true
		 document.getElementById("table_historial_mas_venta_cancelado").innerHTML=paginacargando
		 document.getElementById("tbProcessVentasCanceladas").style.display=""
		 document.getElementById("divProgressVentasCanceladas").style.backgroundColor=''
		 	obtener_datos_user();
				 var datos = {
			 "useru":userid,
			 "passu":passuser,
			 "navegador": navegador,
			"filtrofecha": filtrofecha,
			"fecha1": fecha1,
			"fecha2": fecha2,
			"nroventa": nroventa,
			"cliente": cliente,
			"codlocal": codlocal,
			"registrocargado": registrocargadoventascanceladas,
			"funt": "mashistorialventacancelado"
			};
	 $.ajax({
			
			data: datos,
			url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_historial_mas_venta_cancelado").innerHTML=''
			document.getElementById("divProgressVentasCanceladas").style.backgroundColor='#ff5722'
			controldebusquedadVentasCanceladas=false
			},
			success: function(responseText)
			{	
			var Respuesta=responseText;
     console.log(Respuesta)
			  document.getElementById("table_historial_mas_venta_cancelado").innerHTML=''
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
		  Respuesta=respuestaJqueryAjax(Respuesta)
		if (Respuesta == true) {					
		    var datos_buscados=datos[2];
			document.getElementById("table_historial_mas_venta_cancelado").innerHTML=datos_buscados
			document.getElementById("inptRegistroNroHistorialVentaCancelada").value=datos[3];	
			registrocargadoventascanceladas=datos[99];
			
 		 if(totalregistroventascanceladas>registrocargadoventascanceladas){
	 	var porce=((registrocargadoventascanceladas*100)/totalregistroventascanceladas).toFixed(0)
    	document.getElementById("divProgressVentasCanceladas").style.width=porce+"%"
	    document.getElementById("table_historial_mas_venta_cancelado").innerHTML += "<div id='table_historial_mas_venta_cancelado'></div>"
		document.getElementById("table_historial_mas_venta_cancelado").id=""
				  buscarMashistorialventaCancelada();
					 }else{
						 document.getElementById("tbProcessVentasCanceladas").style.display="none"
						 controldebusquedadVentasCanceladas=false
					 }
			}
			}catch(error)
				{
					document.getElementById("divProgressVentasCanceladas").style.backgroundColor='#ff5722'
					controldebusquedadVentasCanceladas=false
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				}
			}
			});		
}


/*
ORDENES DE IMPRESION
*/
var nroventasticket="";
var nombreapellidoticket="";
var nrodocumentoticket="";
var paginadetalleticket="";
var totalventaticket="";
var subtotalventaticket="";
var descuentoticket=""
var totaldeudaticket="";
var saldoticket="";
var cuotasrestanteticket=""
var totalventaticke="";
function imprimirticketventa(){
	
}







/*
CONTROL DE RESPUESTAJQUERE
*/
function respuestaJqueryAjax(Respuesta){
	if (Respuesta == "UI") {
    ir_a_login()
	ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
	return false;
	}
    if (Respuesta == "NI") {
	ver_vetana_informativa("NO TIENES PERMISO PARA CONTINUA")
	return false;
    }
	if (Respuesta == "CI") {
	ver_vetana_informativa("CONTRASEÑA O USUARIO INVÁLIDOS")
	return false;
    }
	if (Respuesta == "CAMPOSVACIOS") {
    ver_vetana_informativa("FALTO INGRESAR ALGUNOS CAMPOS...")
	return false;
    }
	if(Respuesta == "EX") {
    ver_vetana_informativa("YA EXISTE UNA CLIENTE SIMILAR...")
	return false;
    }
	if (Respuesta == "exito") {
	return true;
    }
	
}
/*
Control de acceso 
*/
function controlacceso(frm,accion){
	
	if(accesosuser[frm][accion]!= "SI"){
		ver_vetana_informativa("NO TIENES PERMISO PARA ACCEDER")
		  return false;
	}else{
		return true;
	}
}

/*
GUARDAR ARCHIVOS LOG
*/
function GuardarArchivosLog(errorlog)
{
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	var hora = f.getHours() 
    var minuto = f.getMinutes() 
    var segundo = f.getSeconds() 
	var archivoname = f.getFullYear() + "_" + mes + "_" + dia+"_"+hora+"_"+minuto+"_"+segundo;
	var a = document.createElement("a");
    document.body.appendChild(a);
    a.style = "display: none";
    var blob = new File([errorlog], "log_"+archivoname+".txt");
    url = window.URL.createObjectURL(blob);
    a.href = url;
    a.download = blob.name;
    a.click();
    window.URL.revokeObjectURL(url);
    
}



var kbenviado=0;
var kbrecibido=0;
function cargarConectividad(datos,s,b){
	if(datos=="error"){
		kbenviado=0
		kbrecibido=0
		document.getElementById("pConectivida1").innerHTML=kbenviado+"/"+kbrecibido
		document.getElementById("imgConectividad1").style.display="none"
		document.getElementById("imgConectividad2").style.display=""
	}
	
	if(datos=="limipiar"){
		kbenviado=0
		kbrecibido=0
		//document.getElementById("pConectivida1").innerHTML=" - / -"
	}
	if(datos=="enviado"){
		kbenviado=s
		document.getElementById("pConectivida1").innerHTML=kbenviado+"/ - Kb"
		document.getElementById("imgConectividad1").style.display=""
		document.getElementById("imgConectividad2").style.display="none"
	}
	if(datos=="recibido"){
		kbrecibido=b
		document.getElementById("pConectivida1").innerHTML=kbenviado+"/"+kbrecibido+" Kb"
		document.getElementById("imgConectividad1").style.display=""
		document.getElementById("imgConectividad2").style.display="none"
		//
	}
	
}

function obtenerTotalKbTables(idTable){
	 var string=document.getElementById(idTable).innerHTML
	 var kb=new Blob([string]).size;
	 return kb
	
}

function iraenlances(d){
	if(d=="juegos"){
		 window.open("https://www.juegos.com/");
	}
	if(d=="word"){
		 window.open("https://www.offidocs.com/community/webofficenewdoc.php");
	}
	if(d=="excel"){
		 window.open("https://www.offidocs.com/community/webofficenewxls.php");
	}
	if(d=="presentacion"){
		 window.open("https://www.offidocs.com/community/webofficenewppt.php");
	}
	if(d=="abc"){
		 window.open("https://www.abc.com.py/");
	}
	if(d=="D10"){
		 window.open("https://d10.ultimahora.com/");
	}
	if(d=="pelicula"){
		 window.open("https://www.cinecalidad.is/");
	}
	if(d=="tv"){
		 window.open("https://www.futbolparaguayotv.xyz/");
	}
}




function buscararqueo2() {
var cobrador = document.getElementById('inptBuscarCobrosRealizados4').value
	var cliente = document.getElementById('inptBuscarCobrosRealizados1').value
	var fechafija = document.getElementById('inptBuscarCobrosRealizados3').value
	var fecha1 = document.getElementById('inptBuscarCobrosRealizadosF1').value
	var fecha2 = document.getElementById('inptBuscarCobrosRealizadosF2').value
	var factura = document.getElementById('inptBuscarCobrosRealizados2').value
	var local = document.getElementById('inptlocalCobrosRealizados3').value
	var metodo = document.getElementById('inptBuscarCobrosRealizados5').value
	document.getElementById("btnEliminarCobros1").style.backgroundColor="#ccc"
   idHistorialPago = "";
	document.getElementById("table_arqeo").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
			"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cobrador": cobrador,
		"cliente": cliente,
		"factura": factura,
		"fechafija": fechafija,
		"metodo": metodo,
		"local": local,
		"codCaja": "",
		"desde": "arqueo2",
		"funt": "arqueo"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
			document.getElementById("table_arqeo").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_arqeo").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_arqeo").innerHTML = datos_buscados
					document.getElementById("inptTotalArqueo").value = datos[3]
					document.getElementById("inptTotalRegistoArqueo").value = datos[4]
					document.getElementById("inptTotalEfectivoArqueo").value = datos[5]
					document.getElementById("inptTotalTarjetaArqueo").value = datos[6]
		
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscararqueo3() {
	var cobrador = ""
	var cliente = ""
	var fechafija = ""
	var fecha1 = ""
	var fecha2 = ""
	var factura = ""
	var local = ""
	var metodo = ""
	var codCaja=codCajaApp
	

 idHistorialPago = "";
	document.getElementById("btnEliminarCobros1").style.backgroundColor="#ccc"
	document.getElementById("table_arqeo").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"cobrador": cobrador,
		"cliente": cliente,
		"factura": factura,
		"fechafija": fechafija,
		"metodo": metodo,
		"local": local,
		"codCaja": codCaja,
		"desde": "arqueo3",
		"funt": "arqueo"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmpagos.php",
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
			document.getElementById("table_arqeo").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_arqeo").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_arqeo").innerHTML = datos_buscados
					document.getElementById("inptTotalArqueo").value = datos[3]
					document.getElementById("inptTotalRegistoArqueo").value = datos[4]
					document.getElementById("inptTotalEfectivoArqueo").value = datos[5]
					document.getElementById("inptTotalTarjetaArqueo").value = datos[6]
				
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}


/*CARGAR DRIVER IMPRESION*/
function guardarendriverimpresion(codigo, tipo,estado, caja, local, diasa, subtotal,descuento,totalpagado,interespagado,totalInteres,saldointeres,saldo,NroCuotas,montopagado,nrorecibopago,cod_usuarioFK) {

	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "nuevo")
	datos.append("codigo", codigo)
	datos.append("tipo", tipo)
	datos.append("estado", estado)
	datos.append("caja", caja)
	datos.append("local", local)
	datos.append("diasa", diasa)
	datos.append("subtotal", subtotal)
	datos.append("descuento", descuento)
	datos.append("totalpagado", totalpagado)
	datos.append("interespagado", interespagado)
	datos.append("totalInteres", totalInteres)
	datos.append("saldointeres", saldointeres)
	datos.append("saldo", saldo)
	datos.append("NroCuotas", NroCuotas)
	datos.append("montopagado", montopagado)
	datos.append("nrorecibopago", nrorecibopago)
	datos.append("cod_usuarioFK", cod_usuarioFK)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/driverimpresion.php",
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
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmconfirmarpago")
            controlInsercionPagos=false
			return false;
		},
		success: function (responseText) {
			verCerrarEfectoCargando("")
			Respuesta = responseText;
			console.log(Respuesta)
			controlInsercionPagos=false
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];

				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				
				
					
				
				}
				

			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}


function verCerrarInformeEntregaCobrador(){
	if(document.getElementById("divEntregaCobrador").style.display==""){
		
		//document.getElementById("divEntregaCobrador").style.display="none"
		document.getElementById("tdEfectoEntregaCobrador").className="magictime vanishOut"
	$("div[id=divEntregaCobrador]").fadeOut(500);	
		
	}else{		
	if(controlacceso("COBROSREALIZADOS","accion")==false){
	   	   //SIN PERMISO
	   return;
		}
		document.getElementById("divEntregaCobrador").style.display=""
		document.getElementById("tdEfectoEntregaCobrador").className="magictime slideDownReturn"
		
	}
}

function CrearProductoCompra(){
	
	document.getElementById("divAbmProducto").style.display=""

	document.getElementById("imgCerrarProducto").style.display=""
	document.getElementById("imgMinimizaeProducto").style.display="none"
	document.getElementById('divAbmProducto1').style.display = "none"
	document.getElementById('divAbmProducto2').style.display = ""
		document.getElementById("tdEfectoAbmProductos").className="magictime slideDownReturn"
	limpiarcamposproducto()
	
}

function vercerrarventanactualizacion(d){
	if(d=="1"){
		document.getElementById("divActualizarSistema").style.display=""
		 document.getElementById("tdEfectoActualizarSistema").className="magictime vanishIn"
	}else{
		//document.getElementById("divActualizarSistema").style.display="none"
		document.getElementById("tdEfectoActualizarSistema").className="magictime vanishOut"
	$("div[id=divActualizarSistema]").fadeOut(500);	
	}
	
}


 /*
LISTA DE ACCESOS
*/

var idAbmListaAccesos="";
var ControlVistaListaAccesos=""
var nombreformulario=""
function verCerrarFrmListaAccesos(d){
	  document.getElementById("divSegundoPlano").style.display="none"	
	if(controlacceso("VERLISTADODEACCESO","accion")==false){ return;}
	
	if(d=="1"){
		
	document.getElementById("divAbmListaAccesos").style.display="";
document.getElementById("tdEfetoListaAcceso").className="magictime slideDownReturn"
	
	}else{
document.getElementById("tdEfetoListaAcceso").className="magictime vanishOut"
	$("div[id=divAbmListaAccesos]").fadeOut(500);	
	LimpiarCamposListaAccesos()
	LimpiarCamposBusquedaListaAccesos()
	}
}
function LimpiarCamposListaAccesos(){
	document.getElementById("inptRegistroSeleccionadoListadoAcceso").value=""
	document.getElementById("inptRegistroSeleccionadoFormListadoAcceso").value=""
	
	idAbmListaAccesos=""
	nombreformulario=""
	
	document.getElementById("btnEditarNombreAcceso").style.backgroundColor="#b5f5b7"
     document.getElementById("btnEditarNombreFormulario").style.backgroundColor="#b5f5b7"
}
function LimpiarCamposBusquedaListaAccesos(){
	document.getElementById("inptBuscarListaAccesos1").value=""
	document.getElementById("divBuscadorListaAccesos").innerHTML=""
	
}
function ObtenerdatosAbmListaAccesos(datostr) {


	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
		
	datostr.className = 'tableRegistroSelec'
	idAbmListaAccesos = $(datostr).children('td[id="td_id"]').html();
	document.getElementById('inptRegistroSeleccionadoListadoAcceso').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptRegistroSeleccionadoFormListadoAcceso').value = $(datostr).children('td[id="td_datos_2"]').html();
	nombreformulario = $(datostr).children('td[id="td_datos_2"]').html();

     document.getElementById("btnEditarNombreAcceso").style.backgroundColor="#4CAF50"
     document.getElementById("btnEditarNombreFormulario").style.backgroundColor="#4CAF50"



}
function VerificarDatosListaAccesos1(){
	
	var inptRegistroSeleccionadoListadoAcceso = document.getElementById("inptRegistroSeleccionadoListadoAcceso").value
	
	if(inptRegistroSeleccionadoListadoAcceso==""){
		document.getElementById("inptRegistroSeleccionadoListadoAcceso").focus()
		ver_vetana_informativa("Falto ingresar el nombre")
		return
	}
	
	
	var accion = "editarnombreacceso";
		if(controlacceso("VERLISTADODEACCESO","accion")==false){ return;}
	
	AbmListaNombreAccesos(inptRegistroSeleccionadoListadoAcceso,idAbmListaAccesos, accion)
}
function AbmListaNombreAccesos(nombre,idabm, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idabm", idabm)
	datos.append("nombre", nombre)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/ABMListadoacceso.php",
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
               
			   Respuesta=respuestaJqueryAjax(Respuesta)
		       if (Respuesta == true) {
		
                   LimpiarCamposListaAccesos()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					BuscarAbmListaAccesos()
					
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function guardarordenlistadoacceso(datos) {
	verCerrarEfectoCargando("1")
	var idabm=datos.id
	var orden=datos.value
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "editarorden")
	datos.append("idabm", idabm)
	datos.append("orden", orden)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/ABMListadoacceso.php",
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
               
			   Respuesta=respuestaJqueryAjax(Respuesta)
		       if (Respuesta == true) {
		
                   LimpiarCamposListaAccesos()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					BuscarAbmListaAccesos()
					
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function VerificarDatosListaAccesos2(){
	
	var inptRegistroSeleccionadoFormListadoAcceso = document.getElementById("inptRegistroSeleccionadoFormListadoAcceso").value
	
	if(inptRegistroSeleccionadoFormListadoAcceso==""){
		document.getElementById("inptRegistroSeleccionadoFormListadoAcceso").focus()
		ver_vetana_informativa("Falto ingresar el nombre")
		return
	}
	
	
	var accion = "editarnombreformulario";
	
	
	AbmListaFormularioAccesos(inptRegistroSeleccionadoFormListadoAcceso,nombreformulario, accion)
}
function AbmListaFormularioAccesos(nombre,idabm, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idabm", idabm)
	datos.append("nombre", nombre)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/ABMListadoacceso.php",
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
               
			   Respuesta=respuestaJqueryAjax(Respuesta)
		       if (Respuesta == true) {
		
                   LimpiarCamposListaAccesos()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					BuscarAbmListaAccesos()
					
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function BuscarAbmListaAccesos() {
if(controlacceso("VERLISTADODEACCESO","accion")==false){ return;}
	var nombre = document.getElementById('inptBuscarListaAccesos1').value
	document.getElementById("divBuscadorListaAccesos").innerHTML = paginacargando

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": nombre,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
        url: "/GoodVentaByR/php_system/ABMListadoacceso.php",
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
			document.getElementById("divBuscadorListaAccesos").innerHTML = ''
			
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorListaAccesos").innerHTML = ''
			
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
		       if (Respuesta == true) {

					var datos_buscados = datos[2];
					document.getElementById("divBuscadorListaAccesos").innerHTML = datos_buscados					
                  
				  
					if(datos_buscados==""){
					   ver_vetana_informativa("NO ENCONTRARON REGISTROS COINCIDENTES")
				   }
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function VerCerrarAddAccesos(d){
	document.getElementById("divAbmNuevoAcceso").style.display="none"
	if(d=="1"){
		document.getElementById("divAbmNuevoAcceso").style.display=""
	}
	
}
function LimpiarCamposAddAcceso(){
	document.getElementById("inptCodigoAcceso").value=""
	document.getElementById("inptNombreAcceso").value=""
	document.getElementById("inptAccesoAcceso").value=""
	document.getElementById("inptOrdenAcceso").value=""
	document.getElementById("inptFormularioAcceso").value=""
}
function VerificarAddDatosAccesos(){
	
	var inptCodigoAcceso = document.getElementById("inptCodigoAcceso").value
	var inptNombreAcceso = document.getElementById("inptNombreAcceso").value
	var inptAccesoAcceso = document.getElementById("inptAccesoAcceso").value
	var inptOrdenAcceso = document.getElementById("inptOrdenAcceso").value
	var inptFormularioAcceso = document.getElementById("inptFormularioAcceso").value
	
	if(inptOrdenAcceso==""){
		document.getElementById("inptOrdenAcceso").focus()
		ver_vetana_informativa("Falto ingresar orden")
		return
	}
	if(inptFormularioAcceso==""){
		document.getElementById("inptFormularioAcceso").focus()
		ver_vetana_informativa("Falto ingresar el formulario")
		return
	}
	if(inptCodigoAcceso==""){
		document.getElementById("inptCodigoAcceso").focus()
		ver_vetana_informativa("Falto ingresar el codigo")
		return
	}
	if(inptNombreAcceso==""){
		document.getElementById("inptNombreAcceso").focus()
		ver_vetana_informativa("Falto ingresar el nombre")
		return
	}
	if(inptAccesoAcceso==""){
		document.getElementById("inptAccesoAcceso").focus()
		ver_vetana_informativa("Falto seleccionar el acceso")
		return
	}
	
	
	var accion = "addAcceso";	
	AbmAddAccesos(inptFormularioAcceso,inptOrdenAcceso,inptCodigoAcceso,inptNombreAcceso,inptAccesoAcceso, accion)
}
function AbmAddAccesos(formulario,orden,codigo,nombre,acceso, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("codigo", codigo)
	datos.append("nombre", nombre)
	datos.append("acceso", acceso)
	datos.append("orden", orden)
	datos.append("formulario", formulario)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/ABMListadoacceso.php",
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
               
			   Respuesta=respuestaJqueryAjax(Respuesta)
		       if (Respuesta == true) {
		
                  
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
				
					
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}




  /*
LISTA DE NIVELES
*/
var idAbmListaNiveles="";
var ControlVistaListaNiveles=""
function verCerrarFrmListaNiveles(d){
	
	  document.getElementById("divSegundoPlano").style.display="none"	
	if(controlacceso("VERLISTADODENIVELES","accion")==false){return;}
	
	if(d=="1"){
	
	document.getElementById("divAbmListaNiveles").style.display="";
document.getElementById("tdEfectoListaNiveles").className="magictime slideDownReturn"
	
	}else{
	//document.getElementById("divAbmListaNiveles").style.display="none";
document.getElementById("tdEfectoListaNiveles").className="magictime vanishOut"
	$("div[id=divAbmListaNiveles]").fadeOut(500);
	LimpiarCamposListaNiveles()
	LimpiarCamposBusquedaListaNiveles()
	}
}
function NuevoListaNivelesFrm(){
	if(controlacceso("VERLISTADODENIVELES","accion")==false){return;}
		document.getElementById("divAbmListaNiveles1").style.display="none"
		document.getElementById("divAbmListaNiveles2").style.display=""
		LimpiarCamposListaNiveles()
}
function EditarListaNivelesFrm(){
	if(controlacceso("VERLISTADODENIVELES","accion")==false){return;}
	if(idAbmListaNiveles==""){		
		ver_vetana_informativa("Falto seleccionar un registro")
		return
	}
		document.getElementById("divAbmListaNiveles1").style.display="none"
		document.getElementById("divAbmListaNiveles2").style.display=""
		
}
function BuscarListaNivelesFrm(){

		document.getElementById("divAbmListaNiveles1").style.display=""
		document.getElementById("divAbmListaNiveles2").style.display="none"
}
function LimpiarCamposListaNiveles(){
	document.getElementById("inptNombreListaNiveles").value=""
	document.getElementById("inptEstadoListaNiveles").value="Activo"
	document.getElementById("inptRegistroSeleccionadoListaNiveles").value=""
	document.getElementById("btnEditarDatosListaNiveles").style.backgroundColor='#b5f5b7'
     document.getElementById("btnEliminarDatosListaNiveles").style.backgroundColor='#ffcece'
     document.getElementById("btnDetallesDatosListaNiveles").style.backgroundColor='#aad9ff'
	idAbmListaNiveles=""
	document.getElementById("btnAbmListaNiveles").value="Guardar Datos"
}
function LimpiarCamposBusquedaListaNiveles(){
	document.getElementById("inptBuscarListaNiveles1").value=""
	document.getElementById("divBuscadorListaNiveles").innerHTML=""
	document.getElementById("lblNroRegistroListaNiveles").innerHTML=""
}
function ObtenerdatosAbmListaNiveles(datostr) {


	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''

	});
		
	datostr.className = 'tableRegistroSelec'
	idAbmListaNiveles = $(datostr).children('td[id="td_id"]').html();
	document.getElementById('inptRegistroSeleccionadoListaNiveles').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptNombreListaNiveles').value = $(datostr).children('td[id="td_datos_1"]').html();
	document.getElementById('inptEstadoListaNiveles').value = $(datostr).children('td[id="td_datos_2"]').html();
     document.getElementById("btnEditarDatosListaNiveles").style.backgroundColor='#4CAF50'
     document.getElementById("btnEliminarDatosListaNiveles").style.backgroundColor='red'
     document.getElementById("btnDetallesDatosListaNiveles").style.backgroundColor='#40a7fb'
     document.getElementById("btnAbmListaNiveles").value="Editar Datos"



}
function EliminarRegitroListaNiveles(){
		if(controlacceso("VERLISTADODENIVELES","accion")==false){return;}
	if(idAbmListaNiveles==""){		
		ver_vetana_informativa("Falto seleccionar un registro")
		return
	}
	if(confirm("Estas seguro que quieres eliminar el registro seleccionado")){
		 document.getElementById("inptEstadoListaNiveles").value="Inactivo";
	VerificarDatosListaNiveles()
	}

}
function VerificarDatosListaNiveles(){
	
	var inptNombreListaNiveles = document.getElementById("inptNombreListaNiveles").value
	var inptEstadoListaNiveles = document.getElementById("inptEstadoListaNiveles").value
	
	
	

	if(inptNombreListaNiveles==""){
		document.getElementById("inptNombreListaNiveles").focus()
		ver_vetana_informativa("Falto ingresar el nombre de la filial")
		return
	}
	
	var accion = "";
	if (idAbmListaNiveles != "") {
		accion = "editar";
		if(controlacceso("VERLISTADODENIVELES","accion")==false){return;}
	} else {
		accion = "nuevo";
		if(controlacceso("VERLISTADODENIVELES","accion")==false){return;}
	}
	AbmListaNiveles(inptNombreListaNiveles,inptEstadoListaNiveles,idAbmListaNiveles, accion)
}
function AbmListaNiveles(nombre,estado, idabm, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idabm", idabm)
	datos.append("nombre", nombre)
	datos.append("estado", estado)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/ABMListadoNiveles.php",
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
                Respuesta=respuestaJqueryAjax(Respuesta)
		       if (Respuesta == true) {
				   LimpiarCamposListaNiveles()
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")
					BuscarAbmListaNiveles()
					BuscarNivelesSelect()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function checkestadoListadoAcceso(d){
	if(d=="1"){
	document.getElementById('inptSeleccEstadoListadoAcceso1').checked=true
	document.getElementById('inptSeleccEstadoListadoAcceso2').checked=false	
	}else{
	document.getElementById('inptSeleccEstadoListadoAcceso1').checked=false
	document.getElementById('inptSeleccEstadoListadoAcceso2').checked=true
	}
}
function BuscarAbmListaNiveles() {
	if(controlacceso("VERLISTADODENIVELES","accion")==false){return;}
	var buscador = document.getElementById('inptBuscarListaNiveles1').value
	var estado = ""
	if(document.getElementById('inptSeleccEstadoListadoAcceso1').checked==true){
		estado = "Activo"
	}else{
		estado = "Inactivo"
	}
	document.getElementById("divBuscadorListaNiveles").innerHTML = paginacargando
  
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"estado": estado,
		"funt": "buscar"
	};
	$.ajax({

		data: datos,
        url: "/GoodVentaByR/php_system/ABMListadoNiveles.php",
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
			document.getElementById("divBuscadorListaNiveles").innerHTML = ''
		
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorListaNiveles").innerHTML = ''
			
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];				
				Respuesta=respuestaJqueryAjax(Respuesta)
		       if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("divBuscadorListaNiveles").innerHTML = datos_buscados					
if(datos_buscados==""){
					   ver_vetana_informativa("NO ENCONTRARON REGISTROS COINCIDENTES")
				   }
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function verCerrarFrmDetallesAccesoListaNiveles(d){
		if(controlacceso("VERLISTADODENIVELES","accion")==false){return;}
	if(d=="1"){
	if(idAbmListaNiveles==""){
			ver_vetana_informativa("Falto seleccionar un registro")
		return false;
	}
	document.getElementById("divDetallesAccesoListaNiveles").style.display="";
 document.getElementById("tdEfectoDetallesListaNiveles").className="magictime slideLeftReturn"
	buscardetalleslistaniveles()
	}else{
	//document.getElementById("divDetallesAccesoListaNiveles").style.display="none";
document.getElementById("tdEfectoDetallesListaNiveles").className="magictime slideRight"
	$("div[id=divDetallesAccesoListaNiveles]").fadeOut(500);	

	}
}
function buscardetalleslistaniveles() {
	if(controlacceso("VERLISTADODENIVELES","accion")==false){return;}
	var buscador = document.getElementById('inptBuscarDetallesAccesoListaNiveles1').value
	document.getElementById("divBuscadorDetallesAccesoListaNiveles").innerHTML = paginacargando
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"idAbmListaNiveles": idAbmListaNiveles,
		"funt": "buscardetalles"
	};
	$.ajax({

		data: datos,
        url: "/GoodVentaByR/php_system/ABMListadoNiveles.php",
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
			document.getElementById("divBuscadorDetallesAccesoListaNiveles").innerHTML = ''
		
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorDetallesAccesoListaNiveles").innerHTML = ''
		
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];				
				Respuesta=respuestaJqueryAjax(Respuesta)
		       if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("divBuscadorDetallesAccesoListaNiveles").innerHTML = datos_buscados					
                  
				  
if(datos_buscados==""){
					   ver_vetana_informativa("NO ENCONTRARON REGISTROS COINCIDENTES")
				   }
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});


}
function abmaccesolistanivel(d) {
	if(controlacceso("VERLISTADODENIVELES","accion")==false){return;}
	var intpu=$(d)
	var idabm=d.id
	var accion="NO"
	if ($(intpu).is(':checked') ){
	accion="SI"
	}
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("usuarios_idusario", userid)
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", "editaracceso")
	datos.append("idabm", idabm)
	datos.append("acciones", accion)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/ABMListadoNiveles.php",
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
					ver_vetana_informativa("DATOS CARGADO CORRECTAMENTE...")	
					
					}			
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}
function BuscarNivelesSelect() {

	document.getElementById("inptTipoUser").innerHTML = ""
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscarSelect"
	};
	$.ajax({

		data: datos,
        url: "/GoodVentaByR/php_system/ABMListadoNiveles.php",
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
			document.getElementById("inptTipoUser").innerHTML = ''
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("inptTipoUser").innerHTML = ''
			
		
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
		       if (Respuesta == true) {
				   
					var pagina = datos[2];
					document.getElementById("inptTipoUser").innerHTML=pagina
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});





}


function removeToMenu(){
	
	
	/*MENU LISTADOS*/
	var controllistado=0;

	if( accesosuser["VERLISTADODELOCALES"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmLocales]").remove()
	controllistado=controllistado+1;		
	}
	if( accesosuser["VERLISTADODEZONAS"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmZona]").remove()
	controllistado=controllistado+1;		
	}
	if( accesosuser["VERLISTADOCOBRADORES"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmCobradores]").remove()
	controllistado=controllistado+1;		
	}
	if( accesosuser["VERLISTADODECLIENTES"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmClientes]").remove()
	controllistado=controllistado+1;		
	}
	if( accesosuser["VERLISTADOPRODUCTOS"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmProductos1]").remove()
	$("table[id=divMenuAbmProductos2]").remove()
	controllistado=controllistado+1;		
	}
	if( accesosuser["VERLISTADOPROVEEDORES"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmProveedores]").remove()
	controllistado=controllistado+1;		
	}
	if( accesosuser["VERLISTADOVENDEDORES"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmVendedores]").remove()
	controllistado=controllistado+1;		
	}
	if( accesosuser["VERLISTADODECAJA"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmCajas]").remove()
	controllistado=controllistado+1;		
	}	
	if( accesosuser["VERFACTURASHABILITADAS"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmFacturas]").remove()
	controllistado=controllistado+1;		
	}
	if(controllistado==9){
		document.getElementById("divMenuListados").style.display="none"
	}
	
	/*MENU ADMINISTRATIVO*/
	var controladministrativo=0;
	
	if( accesosuser["VERINFORMEPRODUCTOSDEBAJA"]["accion"]!="SI")
	{
	$("table[id=divMenuProductosBaja]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERCARGADECOMPRAS"]["accion"]!="SI")
	{
	$("table[id=divMenuCompra1]").remove()
	$("table[id=divMenuCompra2]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERCARGARSUELDO"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmCargarSueldo]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERCOBROSREALIZADOS"]["accion"]!="SI")
	{
	$("table[id=divMenuCobrosRealizado1]").remove()
	$("table[id=divMenuCobrosRealizado2]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERCUENTASACOBRAR"]["accion"]!="SI")
	{
	$("table[id=divMenuCuentasCobar1]").remove()
	$("table[id=divMenuCuentasCobar2]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERCUENTASAPAGAR"]["accion"]!="SI")
	{
	$("table[id=divMenuCuentasPagar]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERCONSULTADECAJA"]["accion"]!="SI")
	{
	$("table[id=divMenuConsultadeCaja]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERLISTADOEGRESOINGRESO"]["accion"]!="SI")
	{
	$("table[id=divMenuEgreso_Ingreso]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VEREXPEDIENTEDELCLIENTE"]["accion"]!="SI")
	{
	$("table[id=divMenuExpedienteCliente2]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERHISTORIALCOMPRA"]["accion"]!="SI")
	{
	$("table[id=divMenuHistorialCompra]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERHISTORIALVENTA"]["accion"]!="SI")
	{
	$("table[id=divMenuHistorialVenta1]").remove()
	$("table[id=divMenuHistorialVenta2]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERINFORMEGARANTIA"]["accion"]!="SI")
	{
	$("table[id=divMenuProductosGarantia]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["HACERDESPACHO"]["accion"]!="SI")
	{
	$("table[id=divMenuListadoDespachado]").remove()
	controladministrativo=controladministrativo+1;		
	}
	if( accesosuser["VERCONTROLDEPOSITO"]["accion"]!="SI")
	{
	$("table[id=divMenuControlDeposito]").remove()
	controladministrativo=controladministrativo+1;		
	}
	
	if(controladministrativo==14){
		document.getElementById("divMenuAdministrativo").style.display="none"
	}
	
	/*MENU INFORME*/
	var controlinforme=0;
	
	if( accesosuser["VERINFORMECODIGOBARRA"]["accion"]!="SI")
	{
	$("table[id=divMenuCodigoBarra]").remove()
	controlinforme=controlinforme+1;		
	}

	if( accesosuser["VERINFORMECUENTAGENERAL"]["accion"]!="SI")
	{
	$("table[id=divMenuCuentasGeneral]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERINFORMEEVALUACION"]["accion"]!="SI")
	{
	$("table[id=divMenuInformeEvaluacion]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERINFORMEDEINVENTARIO"]["accion"]!="SI")
	{
	$("table[id=divMenuInformeInventario]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERINFORMEDEGANANCIAPORVENTA"]["accion"]!="SI")
	{
	$("table[id=divMenuInformeGanPorVenta]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERINFORMEDEPRODUCTOSCOMPRADOS"]["accion"]!="SI")
	{
	$("table[id=divMenuProductosComprados]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERINFORMEDEPRODUCTOSVENDIDOS"]["accion"]!="SI")
	{
	$("table[id=divMenuProductosVendidos]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERINFORMEDEVENTASCANCELADAS"]["accion"]!="SI")
	{
	$("table[id=divMenuVentasCanceladas]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERINFORMEDECOMISIONCOBRADOR"]["accion"]!="SI")
	{
	$("table[id=divMenuComisionCobrador]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERINFORMEDECOMISIONVENDEDOR"]["accion"]!="SI")
	{
	$("table[id=divMenuComisionVendedor]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERINFORMEDEPAGOSELIMINADOS"]["accion"]!="SI")
	{
	$("table[id=divMenuPagosEliminados]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERCATALOGO"]["accion"]!="SI")
	{
	$("table[id=divMenuCatalogo]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERCLIENTESINACTIVOS"]["accion"]!="SI")
	{
	$("table[id=divMenuClientesInactivo]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERDESPACHADOS"]["accion"]!="SI")
	{
	$("table[id=divMenuProductoDespachado]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERINFORMEDECOMPRASELIMINADO"]["accion"]!="SI")
	{
	$("table[id=divMenuComprasEliminados]").remove()
	controlinforme=controlinforme+1;		
	}
	
	if( accesosuser["VERINFORMEMOROSO"]["accion"]!="SI")
	{
	$("table[id=divMenuClienteMoroso]").remove()
	controlinforme=controlinforme+1;		
	}
	if(controlinforme==16){
		document.getElementById("divMenuInformes").style.display="none"
	}
	
	/*SISTEMA*/
	if( accesosuser["VERLISTADOUSUARIO"]["accion"]!="SI")
	{
	$("table[id=divMenuAbmUsuarios]").remove()
	
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERLISTADODEACCESO"]["accion"]!="SI")
	{
	$("table[id=divMenuListadoAcceso]").remove()
	controlinforme=controlinforme+1;		
	}
	if( accesosuser["VERLISTADODENIVELES"]["accion"]!="SI")
	{
	$("table[id=divMenuListadoNiveles]").remove()
	controlinforme=controlinforme+1;		
	}
	
	
	if( accesosuser["VERCERRARCAJA"]["accion"]!="SI")
	{
	document.getElementById("divMenuArqueo").style.display='none'		
	}
	
	if( accesosuser["VERVENTA"]["accion"]!="SI")
	{
	document.getElementById("divMenuVenta").style.display='none'		
	}
	
	
}






function verCerrarVendedorMetas(){
	
	if(document.getElementById("divVendedorMetasLista").style.display==""){
	document.getElementById("tdEfectoVendedorMetasLista").className="magictime vanishOut"
	$("div[id=divVendedorMetasLista]").fadeOut(500);	
		
		}else{		
				
	 document.getElementById("divVendedorMetasLista").style.display=""
     document.getElementById("tdEfectoVendedorMetasLista").className="magictime slideDownReturn"
	 buscarVendedorMetas();
	
		
	}
}



function buscarVendedorMetas() {

	document.getElementById("table_abm_Metas").innerHTML = paginacargando
   var cod_local = document.getElementById("inptBuscarAbmCobrador3").value;  
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_local": cod_local,		
		"funt": "buscarVendedor"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmvendedor.php",
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
			document.getElementById("table_abm_Metas").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_abm_Metas").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					var datos_buscados = datos[2];
					
					document.getElementById("table_abm_Metas").innerHTML = datos_buscados
					
					
					}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				
			}
		}
	});
}



function abmaccesoMetas(d) {
	
	var idMetas=d.name
	var nro=d.value
	nro=QuitarSeparadorMilValor(nro);
	var Funcion="editarAcceso"
	
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("idAbmUsuario", userid)
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", Funcion)
	datos.append("idMetas", idMetas)	
	datos.append("nro", nro)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmvendedor.php",
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
					
					}			
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				
			}
		}
	});
}


function verCerrarInformeMetasVendedores(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divVendedorMetas").style.display==""){
		document.getElementById("divMinimizadoInformeMetasVendedores").style.display="none"
		limpiarcamposbuscadorInformePagosEliminados()
document.getElementById("tdEfectoVendedorMetas").className="magictime vanishOut"
	$("div[id=divVendedorMetas]").fadeOut(500);			
	}else{	
if(controlacceso("VERINFORMEDEMETAS","accion")==false){return;}	
		document.getElementById("divVendedorMetas").style.display=""
		 document.getElementById("tdEfectoVendedorMetas").className="magictime slideDownReturn"
	}
}


function checkHistorialMetas(d){	
	if(d=="1"){
		document.getElementById('checkHistorialMetas1').checked=true
		document.getElementById('checkHistorialMetas2').checked=false
		document.getElementById('inptBuscarMetasF1').value = "";
	    document.getElementById('inptBuscarMetasF2').value = "";	
	}else{		
		document.getElementById('checkHistorialMetas1').checked=false
		document.getElementById('checkHistorialMetas2').checked=true
	var f = new Date();
	var dia = f.getDate()
	if (dia < 10) {
		dia = "0" + dia;
	}
	var mes = f.getMonth() + 1
	if (mes < 10) {
		mes = "0" + mes;
	}
	document.getElementById('inptBuscarMetasF1').value = f.getFullYear() + "-" + mes + "-" + "01";
	document.getElementById('inptBuscarMetasF2').value = f.getFullYear() + "-" + mes + "-" + dia;
		
	}
}


function buscarMetasVendedores() {
	var fecha1 = document.getElementById('inptBuscarMetasF1').value
	var fecha2 = document.getElementById('inptBuscarMetasF2').value
	var local = document.getElementById('inptlocalMetas').value

	if(document.getElementById('checkHistorialMetas2').checked==true){
	if (fecha1 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO", "#")
		return
	}
	if (fecha2 == "") {
		ver_vetana_informativa("FALTO SELECCIONAR LA FECHA FIN", "#")
		return
	}
	}else{
	fecha1 = ""
	fecha2 = ""
	}
	
	document.getElementById("table_vendedor_metas").innerHTML = paginacargando
	document.getElementById("inptTotalRegistoMetas").value =""
	document.getElementById("inptTotalVentaMetas").value =""
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"local": local,
		"funt": "buscarMetas"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmvendedor.php",
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
			document.getElementById("table_vendedor_metas").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("table_vendedor_metas").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_vendedor_metas").innerHTML = datos[2];
					
					document.getElementById("inptTotalRegistoMetas").value = datos[3];
					document.getElementById("inptTotalVentaMetas").value = datos[4];
					
					
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
			}
	});
}


function menu(d){
	
	

	
	if(d=="1"){
		
		$('#divMenuMantenimiento').stop().animate({scrollTop: 0}, 400);
		
		
	}
	if(d=="2"){
	
		$('#divMenuMantenimiento').stop().animate({scrollTop: 400}, 400);
		
	}
	if(d=="3"){
		
		$('#divMenuMantenimiento').stop().animate({scrollTop: 900}, 400);
	}
	if(d=="4"){
		
		$('#divMenuMantenimiento').stop().animate({scrollTop: 1400}, 400);
	}
	
	
}



function verCerrarInformeClieteMorosos(){
	document.getElementById("divSegundoPlano").style.display="none";
	if(document.getElementById("divClienteMoroso").style.display==""){
		document.getElementById("divMinimizadoInformeClienteMoroso").style.display="none"
		limpiarcamposbuscadorInformePagosEliminados()
document.getElementById("tdEfectoClienteMoroso").className="magictime vanishOut"
	$("div[id=divClienteMoroso]").fadeOut(500);			
	}else{	
if(controlacceso("VERINFORMEDEMETAS","accion")==false){return;}	
		document.getElementById("divClienteMoroso").style.display=""
		 document.getElementById("tdEfectoClienteMoroso").className="magictime slideDownReturn"
	}
}


function minimizarInformeClieteMorosos(){
document.getElementById("tdEfectoClienteMoroso").className="magictime slideDown"
	$("div[id=divClienteMoroso]").fadeOut(500);	
	document.getElementById("divMinimizadoInformeClienteMoroso").style.display="";
}



var Contenidomensaje ="";


function buscarcuentaMoroso() {

	 var filtro= document.getElementById("inputSelectTipoBuscarMoroso").value
	 var buscar= document.getElementById("inptBuscarInfClienteMoroso").value
	 var zona= document.getElementById("inptZonaMoroso").value
	 var Local= document.getElementById("inptlocalMoroso").value
   
	document.getElementById("table_Cliente_Moroso").innerHTML = paginacargando
	document.getElementById("inptTotalRegistoCliente_orosos").value =""
	document.getElementById("inptTotalDeudaMorosos").value =""
	obtener_datos_user();
	var datos = {
			"useru": userid,
			"passu": passuser,
			"navegador": navegador,
			"buscar": buscar,
			"filtro": filtro,
			"zona": zona,
			"Local": Local,
			"funt": "cuentasMoroso"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaByR/php_system/abmventa.php",
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
			document.getElementById("table_Cliente_Moroso").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			try {
			console.log(Respuesta)
			document.getElementById("table_Cliente_Moroso").innerHTML = ''
			
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {				   
					var datos_buscados = datos[2];
					document.getElementById("table_Cliente_Moroso").innerHTML = datos[2];
					
					document.getElementById("inptTotalRegistoCliente_orosos").value = datos[3];
					document.getElementById("inptTotalDeudaMorosos").value = datos[4];
					
					Contenidomensaje=datos[7]
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
			}
	});
}


function copiarAlPortapapeles() {
  var aux = document.createElement("input");
  aux.setAttribute("value", Contenidomensaje);
  document.body.appendChild(aux);
  aux.select();
  document.execCommand("copy");
  document.body.removeChild(aux);
  ver_vetana_informativa("MENSAJE GENERADO CON EXITO FAVOR PEGAR EN EL ARCHIVO EXCEL")
}



