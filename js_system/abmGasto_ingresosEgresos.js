function mostrarItemsZona(opcion) {
	switch (opcion) {
		case 'GastosOperativos':
        	if(controlacceso("VERGASTOSZONAOPERATIVOS","accion")==false){return;}
			break;
		case 'CostosDirectos':
			if(controlacceso("VERGASTOSZONACOSTOSDIRECTOS","accion")==false){return;}
			break;
		case 'Ingreso':
			if(controlacceso("VERGASTOSZONAINGRESOS","accion")==false){return;}
			break;
	}
	const zona= document.getElementById('zonaGastos'+opcion);
	
	// Despliega o oculta segun el estado actual
	bootstrap.Collapse.getOrCreateInstance(zona).toggle();
}

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
        buscaroptionMotivoEgresoIngreso()
		buscarabmGasto();
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
	document.getElementById("table_abm_gasto").innerHTML="";
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
			limpiarcamposGasto();
            BuscarAbmMotivoEgresoIngreso();
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
	document.getElementById('inptDescripcionGasto').value = $(datostr).children('td[id="td_datos_13"]').html();
	document.getElementById('inptMotivoMisGastos').value = $(datostr).children('td[id="td_datos_14"]').html();
	document.getElementById('inptFechaGasto').value = $(datostr).children('td[id="td_datos_3"]').html();
	document.getElementById('inptEstadoGasto').value = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById('inptlocalMisGastos').value = $(datostr).children('td[id="td_datos_7"]').html();
	document.getElementById('inptNroBoletaGasto').value = $(datostr).children('td[id="td_datos_8"]').html();
	document.getElementById('inptBancoGasto').value = $(datostr).children('td[id="td_datos_9"]').html();
	document.getElementById('inptCuentaGasto').value = $(datostr).children('td[id="td_datos_10"]').html();
	document.getElementById('inptTipoGasto').value = $(datostr).children('td[id="td_datos_6"]').html();
	document.getElementById('inptArregloGasto').value = $(datostr).children('td[id="td_datos_11"]').html();
	document.getElementById('btnAbmGastos').value = "Editar datos";
	document.getElementById('btnEditarGastos').style.backgroundColor="";
	document.getElementById('btnAutorizarGastos').style.backgroundColor="#28a745";
	idAbmGasto = $(datostr).children('td[id="td_id"]').html();
	
	cod_interConsulta= $(datostr).children('td[id="td_datos_15"]').html();
	document.getElementById("inptAbmInterConsultaGasto").value= $(datostr).children('td[id="td_datos_16"]').html();

	// Auditoria de autorizacion
	document.getElementById("inptCodigoAutorizacionEgreso").value= $(datostr).children('td[id="td_id"]').html();
	document.getElementById("inptMotivoAutorizacionEgreso").value= $(datostr).children('td[id="td_datos_14"]').html();
	document.getElementById('inptMontoAutorizacionEgreso').value = $(datostr).children('td[id="td_datos_1"]').html();
	if ($(datostr).children('td[id="td_datos_5"]').html() == 'pendiente') {
		document.getElementById("inptCodUsuarioAutorizacionEgreso").value= "";
		document.getElementById("inptUsuarioAutorizacionEgreso").value= "";
		document.getElementById("inptFechaAutorizacionEgreso").value= "";
		document.getElementById('divbtnAprobarMovimiento').style.display= "";
	} else {
		document.getElementById("inptCodUsuarioAutorizacionEgreso").value= $(datostr).children('td[id="td_datos_17"]').html();
		document.getElementById("inptUsuarioAutorizacionEgreso").value= $(datostr).children('td[id="td_datos_18"]').html();
		document.getElementById("inptFechaAutorizacionEgreso").value= $(datostr).children('td[id="td_datos_19"]').html();
		document.getElementById('divbtnAprobarMovimiento').style.display= "none";
	}

	// Carga la imagen
	let imagen= $(datostr).children('td[id="td_datos_12"]').html();
	imagen= imagen ? imagen : '/GoodVentaAsisCap/iconos/imagenphoto.png';
    document.getElementById('imgfotoGasto').style.backgroundImage= "url("+ imagen +")";
}

function verCerrarAutorizacionEgreso(mostrar) {
	if(controlacceso("AUTORIZAREGRESOINGRESO","accion")==false){return;}
	if (mostrar) {
		document.getElementById('divAutorizacionEgreso').style.display= "";
	} else {
		document.getElementById('divAutorizacionEgreso').style.display= "none";
	}
}

function aprobarMovimiento() {
	const inptCodigoAutorizacionEgreso= document.getElementById('inptCodigoAutorizacionEgreso').value;
	obtener_datos_user();

	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", 'aprobarMovimiento');
	datos.append("idgastos", inptCodigoAutorizacionEgreso);

	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
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
			Respuesta = responseText;
			verCerrarEfectoCargando("")
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				   ver_vetana_informativa("Datos guardados exitosamente");
				   verCerrarAutorizacionEgreso(false);
				   buscarabmGasto();
				}				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function verificarcamposGasto() {
	var inptMontoGasto = document.getElementById('inptMontoGasto').value
	var inptDescripcionGasto = document.getElementById('inptDescripcionGasto').value
	var inptFechaGasto = document.getElementById('inptFechaGasto').value
	var inptEstadoGasto = document.getElementById('inptEstadoGasto').value
	var inptArregloGasto = document.getElementById('inptArregloGasto').value
	var inptlocalMisGastos = document.getElementById('inptlocalMisGastos').value
	var inptTipoGasto = document.getElementById('inptTipoGasto').value
	var inptNroBoletaGasto = document.getElementById('inptNroBoletaGasto').value
	var inptBancoGasto = document.getElementById('inptBancoGasto').value
	var inptCuentaGasto = document.getElementById('inptCuentaGasto').value

    let inptMotivoMisGastos= '';
    $("input[id=inptMotivoMisGastos]").each(function (i, Elemento) {
      var $input = $(this),
          val = $input.val();
		 
          list = $input.attr('list'),
          match = $('#'+list + ' option').filter(function() {
              return ($(this).val() === val);			 
          });

       if(match.length > 0) {
         inptMotivoMisGastos=$(match).attr("id")
       } else {
           // value is not in list
		   inptMotivoMisGastos= '';
       }
    });

    if (inptMotivoMisGastos == '') {
        ver_vetana_informativa("FALTO SELECCIONAR UN MOTIVO DE LA LISTA.");
        return false;
    }

    if (inptArregloGasto == "" && inptTipoGasto=="Egreso") {
		ver_vetana_informativa("FALTO SELECCIONAR UN ARREGLO", "#")
		return false;
	}
	if (inptMontoGasto == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MONTO DEL GASTO", "#")
		return false;
	}
	if (inptDescripcionGasto == "") {
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
	abmgastos(inptArregloGasto,inptNroBoletaGasto, inptBancoGasto , inptCuentaGasto ,inptMontoGasto, inptDescripcionGasto, inptFechaGasto, inptEstadoGasto, idAbmGasto, inptTipoGasto, inptlocalMisGastos, inptMotivoMisGastos,accion);

	// Comprueba y registra en caso de ser gasto fijo
	if (document.getElementById("inptCheckGastoFijoGasto").checked) {
		const descripcion= document.getElementById('inptMotivoMisGastos').value;
		const dia= document.getElementById('inptFechaGasto').value.substr(-2);
		let costo= document.getElementById('inptMontoGasto').value;
		costo= costo.replace('.','');
		abmGastosFijos(descripcion, 'activo', dia, inptlocalMisGastos,costo);
	}
}
function abmgastos(Arreglo,nroboleta ,banco ,nrocuenta,monto, descripcion, fecha, estado, idgastos, tipo, cod_local,cod_motivoFK, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("idgastos", idgastos)
	datos.append("monto", monto)
	datos.append("motivo", descripcion);
    datos.append("cod_motivoFK", cod_motivoFK)
	datos.append("fecha", fecha)
	datos.append("estado", estado)
	datos.append("tipo", tipo)
	datos.append("cod_local", cod_local)
	datos.append("codcaja", cajapredeterminada)
	datos.append("idaperturacierrecaja", idabmAperturacierrecaja)
	datos.append("nroboleta", nroboleta)
	datos.append("banco", banco)
	datos.append("Arreglo", Arreglo)
	datos.append("nrocuenta", nrocuenta)
	datos.append("foto", fotoGasto);
    datos.append("ext", extGasto);
	datos.append("cod_interConsultaFK", cod_interConsulta);
	
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
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
			Respuesta = responseText;
			verCerrarEfectoCargando("")
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
					if (Number.isNaN(parseInt(datos["2"]))) {
						ver_vetana_informativa(datos["2"]);
						return false;
					}
				   if(accion=="nuevo"){
						ImprimirTicketEgreso()
					}
					
					ver_vetana_informativa("Datos guardados exitosamente.");
					limpiarcamposGasto()

					idAbmGasto = "";
					buscarabmGasto();
					verCerrarVentanaAbmGasto("2","");
					comprobarLimiteMotivo(cod_motivoFK, cod_local);
				}				
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function comprobarLimiteMotivo(cod_motivo, cod_local) {
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", 'verficiarLimiteMotivo')
    datos.append("cod_motivo", cod_motivo)
    datos.append("cod_local", cod_local)
	
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
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
			Respuesta = responseText;
			verCerrarEfectoCargando("")
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
			   if (Respuesta == true) {
				   const limite = parseInt(datos["2"]);
				   const total = parseInt(datos["3"].replace('.',''));

				   if (!(Number.isNaN(limite)) && total >= limite) {
					   ver_vetana_informativa("Ha superado el limite permitido para este motivo de gasto.");
				   } else if (total >= (limite * 0.9)) {
					   ver_vetana_informativa("Esta llegando al limite presupuestado para este motivo de gasto.");
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

var fotoGasto= "";
var extGasto= "";
function subirImagenGasto(cod_abmGasto) {
    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("funt", "cargar_imagen");
    datos.append("idgastos", cod_abmGasto);
    datos.append("foto", fotoGasto);
    datos.append("ext", extGasto);
    
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmgasto.php",
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
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					ver_vetana_informativa("Datos guardados exitosamente.");
					limpiarcamposGasto()

					idAbmGasto = "";
					buscarabmGasto();
					verCerrarVentanaAbmGasto("2","");
				} else {
					throw new Error("Error producido en subirImagenGasto de JavaScript.");
                }
				verCerrarEfectoCargando("");
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
				verCerrarEfectoCargando("");
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
+"<td style=''>"+document.getElementById("inptDescripcionGasto").value+"</td>"
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
	 window.open("/GoodVentaAsisCap/system/reportTicket.html");
	 document.getElementById("DivImprimir").innerHTML = "";
//buscarDatosVentaticket(idabmVenta)
     
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
	const ocultar_inactivos = document.getElementById("inptSeleccEstadoBuscarGasto2").checked;
	var estado =""
	var tipo = document.getElementById('inptSeleccTipoBuscarGasto').value
	var arreglo = ""; //document.getElementById('inptSeleccArregloBuscarGasto').value
	var cod_local = document.getElementById('inptlocalMisGastosBusca').value
	var fecha = document.getElementById('inptBuscarIngresoEgreso1').value
	var usuario = document.getElementById('inptBuscarIngresoEgreso2').value
    let cod_motivoFK= '';
    $("input[id=inptBuscarIngresoEgreso3]").each(function (i, Elemento) {
      var $input = $(this),
          val = $input.val();
		 
          list = $input.attr('list'),
          match = $('#'+list + ' option').filter(function() {
              return ($(this).val() === val);			 
          });

       if(match.length > 0) {
         cod_motivoFK=$(match).attr("id")
       } else {
           // value is not in list
       }
    });
	
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"fecha1": fecha1,
		"fecha2": fecha2,
		"estado": estado,
		"ocultar_inactivos": ocultar_inactivos,
		"cod_local": cod_local,
		"tipo": tipo,
		"usuario": usuario,
		"fecha": fecha,
		"arreglo": arreglo,
        "cod_motivoFK": cod_motivoFK,
		"funt": "buscar"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
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
			document.getElementById("zonaGastosSinCategorizar").innerHTML = '';
			document.getElementById("table_abm_gasto").innerHTML = '';
			document.getElementById("zonaGastosIngreso").innerHTML= '';
			document.getElementById("zonaGastosCostosDirectos").innerHTML= '';
			document.getElementById("zonaGastosGastosOperativos").innerHTML= '';
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("zonaGastosSinCategorizar").innerHTML = '';
			document.getElementById("zonaGastosIngreso").innerHTML= '';
			document.getElementById("table_abm_gasto").innerHTML = '';
			document.getElementById("zonaGastosCostosDirectos").innerHTML= '';
			document.getElementById("zonaGastosGastosOperativos").innerHTML= '';
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
					document.getElementById("table_abm_gasto").innerHTML = datos[12];
					document.getElementById("zonaGastosSinCategorizar").innerHTML = datos_buscados;
					document.getElementById("tituloTotalSinCategorizar").innerHTML= datos[11];
					document.getElementById("inptTotalGasto").value = datos[4];
					document.getElementById("inptRegistroNroGastos").value = datos[3];
					
					document.getElementById("tituloTotalIngreso").innerHTML= datos[5];
					document.getElementById("zonaGastosIngreso").innerHTML= datos[8];
					document.getElementById("tituloTotalCostosDirectos").innerHTML= datos[6];
					document.getElementById("zonaGastosCostosDirectos").innerHTML= datos[9];
					document.getElementById("tituloTotalGastosOperativos").innerHTML= datos[7];
					document.getElementById("zonaGastosGastosOperativos").innerHTML= datos[10];
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
	document.getElementById('inptDescripcionGasto').value = "";
	document.getElementById('inptFechaGasto').value = "";
	document.getElementById('inptPersonalGasto').value = "";
	document.getElementById('inptNroBoletaGasto').value = "";
	document.getElementById('inptBancoGasto').value = "";
	document.getElementById('inptCuentaGasto').value = "";
	document.getElementById('inptArregloGasto').value = "";
	document.getElementById('btnEditarGastos').style.backgroundColor="#b7b7b7";
	document.getElementById('btnAutorizarGastos').style.backgroundColor="#b7b7b7";
	document.getElementById('inptEstadoGasto').value = "Activo";
	document.getElementById('btnAbmGastos').value = "Guardar datos";
	document.getElementById('inptMotivoMisGastos').value ="";
	document.getElementById('inptAbmInterConsultaGasto').value= "";
	cod_interConsulta= "";
	idAbmGasto = "";
	seleccionarLocalUSer()
	fotoGasto= "";
	extGasto= "";
    document.getElementById('imgfotoGasto').style.backgroundImage= "url("+ '/GoodVentaAsisCap/iconos/imagenphoto.png' +")";
}

/* ABM MOTIVO EN EGRESO/INGRESO */
function verCerrarAbmNuevoMotivo(){
	if(controlacceso("CREARNUEVOMOTIVO","accion")==false){return;}
	if(document.getElementById("divAbmNuevoMotivo").style.display==""){
		
		$("div[id=divAbmNuevoMotivo]").fadeOut(500);	

		// Se indica el motivo seleccionado si el estado es activo
		if (idAbmMotivoEgresoIngreso && document.getElementById("inptEstadoMotivoEgresoIngreso").value == 'activo') {
			document.getElementById('inptMotivoMisGastos').value= document.getElementById("inptNuevoMotivoEgresoIngreso").value;
		} else {
			document.getElementById('inptMotivoMisGastos').value= "";
		}		
	}else{		
		document.getElementById("divAbmNuevoMotivo").style.display=""
		BuscarAbmMotivoEgresoIngreso();
	}
}
function VerificarDatosMotivoEgresoIngreso() {
	var inptNuevoMotivo = document.getElementById('inptNuevoMotivoEgresoIngreso').value
	var inptEstadoMotivoEgresoIngreso = document.getElementById('inptEstadoMotivoEgresoIngreso').value
	const inptCategoriaMotivoEgresoIngreso = document.getElementById('inptCategoriaMotivoEgresoIngreso').value;
	const inptAutorizacionMotivoEgresoIngreso= document.getElementById('inptAutorizacionMotivoEgresoIngreso').checked;
	const inptPresupuestoIngresoEgreso= document.getElementById('inptPresupuestoIngresoEgreso').value.replace('.', '');
	let accion = "";
	
	if (inptNuevoMotivo == "") {
		ver_vetana_informativa("FALTO AGREGAR NUEVO MOTIVO", "#")
		return false;
	}
	if (!inptCategoriaMotivoEgresoIngreso) {
		ver_vetana_informativa("FALTO SELECCIONAR LA CATEGORIA", "#");
		return false;
	}

	if(idAbmMotivoEgresoIngreso != ''){
		accion = "editarMotivo";
	}else{
		accion = "NuevoMotivo";
	}
		
	abmNuevoMotivo(inptNuevoMotivo,inptEstadoMotivoEgresoIngreso, inptCategoriaMotivoEgresoIngreso, inptAutorizacionMotivoEgresoIngreso, inptPresupuestoIngresoEgreso, accion);
}

function abmNuevoMotivo(motivo, estado , categoria, necesita_autorizacion, presupuesto, accion) {
	verCerrarEfectoCargando("1")
	var datos = new FormData();
	obtener_datos_user();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("funt", accion)
	datos.append("motivo", motivo)
	datos.append("estado", estado)
	datos.append("categoria", categoria);
	datos.append("idabm", idAbmMotivoEgresoIngreso)
	datos.append("presupuesto", presupuesto);
	datos.append("necesita_autorizacion", (necesita_autorizacion ? 1 : 0)); // El 1 es equivalente a true
	
	var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmgasto.php",
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
					buscaroptionMotivoEgresoIngreso()
					// verCerrarAbmNuevoMotivo()
					BuscarAbmMotivoEgresoIngreso()
					limpiarcamposmotivoegresoingreso()
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}


		}
	});


}
function buscaroptionMotivoEgresoIngreso() {

	document.getElementById("ListMotivoMisGastos").innerHTML = ""
	document.getElementById("listBuscarIngresoEgreso3").innerHTML = ""

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "buscaroption"
	};
	$.ajax({

		data: datos,
		url: "../php_system/abmgasto.php",
		type: "post",
		
		beforeSend: function () {


		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("ListMotivoMisGastos").innerHTML = ''
			document.getElementById("listBuscarIngresoEgreso3").innerHTML = ""
		},
		success: function (responseText) {

			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("ListMotivoMisGastos").innerHTML = ''
			document.getElementById("listBuscarIngresoEgreso3").innerHTML = ""
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
				   var datos_buscados = datos[2];
					document.getElementById("ListMotivoMisGastos").innerHTML = datos[4]
					document.getElementById("listBuscarIngresoEgreso3").innerHTML = datos[4]
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function combinarMotivoEgresoIngreso() {
	// Verifica si posee el permiso
	if (!controlacceso("COMBINARMOTIVOSEGRESOINGRESO", "accion")) {return false;}

	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"cod_motivo_ingreso_egreso": document.getElementById('inptCodAbmMotivoEgresoIngreso1').value,
		"cod_motivo_ingreso_egreso_destino": document.getElementById('inptCodAbmMotivoEgresoIngreso2').value,
		"funt": "combinarmotivoingresoegreso"
	};
	$.ajax({
		data: datos,
        url: "../php_system/abmgasto.php",
		type: "post",
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("divBuscadorMotivoEgresoIngreso").innerHTML = ''
			document.getElementById("lblNroRegistroMotivoEgresoIngreso").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					limpiarcamposmotivoegresoingreso();
					BuscarAbmMotivoEgresoIngreso();
					ver_vetana_informativa("Datos guardados exitosamente", "#");
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function BuscarAbmMotivoEgresoIngreso() {
	var buscador = document.getElementById("inptBuscarAbmMotivoEgresoIngreso").value
	var estado = "Activo"
	document.getElementById("divBuscadorMotivoEgresoIngreso").innerHTML = paginacargando
    document.getElementById("lblNroRegistroMotivoEgresoIngreso").innerHTML="";
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"buscar": buscador,
		"estado": estado,
		"funt": "buscarabmmotivoingresoegreso"
	};
	$.ajax({
		data: datos,
        url: "../php_system/abmgasto.php",
		type: "post",
		 
		
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			document.getElementById("divBuscadorMotivoEgresoIngreso").innerHTML = ''
			document.getElementById("lblNroRegistroMotivoEgresoIngreso").innerHTML = ''
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			document.getElementById("divBuscadorMotivoEgresoIngreso").innerHTML = ''
			document.getElementById("lblNroRegistroMotivoEgresoIngreso").innerHTML = ''
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					var datos_buscados = datos[2];
					document.getElementById("divBuscadorMotivoEgresoIngreso").innerHTML = datos_buscados
                   document.getElementById("lblNroRegistroMotivoEgresoIngreso").innerHTML="Se encontraron "+datos[3]+" registro(s)";
				   buscaroptionMotivoEgresoIngreso()
				}
			} catch (error) {
ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
					var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

var idAbmMotivoEgresoIngreso = "";
function ObtenerdatosAbmMotivoEgresoIngreso(datostr) {
	$("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	ElementoSeleccMarca=datostr
	datostr.className = 'tableRegistroSelec'
    document.getElementById("inptNuevoMotivoEgresoIngreso").value = $(datostr).children('td[id="td_datos_1"]').html();
    document.getElementById("inptEstadoMotivoEgresoIngreso").value = $(datostr).children('td[id="td_datos_2"]').html();
    document.getElementById("inptCategoriaMotivoEgresoIngreso").value = $(datostr).children('td[id="td_datos_3"]').html().toLowerCase();
    document.getElementById("inptPresupuestoIngresoEgreso").value = $(datostr).children('td[id="td_datos_5"]').html();
	const necesita_autorizacion= $(datostr).children('td[id="td_datos_4"]').html();
	if (necesita_autorizacion == "1") {
		document.getElementById("inptAutorizacionMotivoEgresoIngreso").checked = true;
	} else {
		document.getElementById("inptAutorizacionMotivoEgresoIngreso").checked = false;
	}
	idAbmMotivoEgresoIngreso= $(datostr).children('td[id="td_id"]').html();
     document.getElementById("btnMotivoIngresoEgreso").value="Editar Datos";

	// DAtos para combinacion de motivos
	if (!(document.getElementById('inptCodAbmMotivoEgresoIngreso1').value)) {
		document.getElementById('inptCodAbmMotivoEgresoIngreso1').value= $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptNombreAbmMotivoEgresoIngreso1').value= $(datostr).children('td[id="td_datos_1"]').html();
	} else {
		document.getElementById('inptCodAbmMotivoEgresoIngreso2').value= $(datostr).children('td[id="td_id"]').html();
		document.getElementById('inptNombreAbmMotivoEgresoIngreso2').value= $(datostr).children('td[id="td_datos_1"]').html();
	}
}

function limpiarcamposmotivoegresoingreso(){
	  document.getElementById("inptNuevoMotivoEgresoIngreso").value = ''
	  document.getElementById("inptCategoriaMotivoEgresoIngreso").value = '';
    document.getElementById("inptEstadoMotivoEgresoIngreso").value = 'activo';
    document.getElementById("inptPresupuestoIngresoEgreso").value = '';
	document.getElementById("inptAutorizacionMotivoEgresoIngreso").checked = false;
	cod_interConsulta= "";
	document.getElementById("inptAbmInterConsultaGasto").value= "";
	idAbmMotivoEgresoIngreso=''
     document.getElementById("btnMotivoIngresoEgreso").value="Guardar"

	 // DAtos para combinacion de motivos
	 document.getElementById('inptCodAbmMotivoEgresoIngreso1').value= "";
	 document.getElementById('inptNombreAbmMotivoEgresoIngreso1').value= "";
	 document.getElementById('inptCodAbmMotivoEgresoIngreso2').value= "";
	 document.getElementById('inptNombreAbmMotivoEgresoIngreso2').value= "";
}

function verCerrarVentanaABMLimiteCaja(mostrar) {
	if (controlacceso("VERABMLIMITECAJA","accion")==false){return;}
	if (mostrar) {
		$("div[id=divAbmLimiteCaja]").fadeIn(250);
	} else {
		$("div[id=divAbmLimiteCaja]").fadeOut(500);
	}
}

function agregarLimiteCaja() {
	let inptLimiteCaja = document.getElementById("inptLimitecaja").value;
	if (inptLimiteCaja === "") {
		ver_vetana_informativa("FALTO INGRESAR EL LIMITE DE CAJA", "#");
		return false;
	}

	// Elimina los puntos de miles
	inptLimiteCaja = inptLimiteCaja.replace(/\./g, '');
	verCerrarEfectoCargando("1");
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"monto": inptLimiteCaja,
		"funt": "agregarLimiteCaja"
	};
	$.ajax({
		data: datos,
        url: "../php_system/abmgasto.php",
		type: "post",
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					ver_vetana_informativa("LIMITE DE CAJA AGREGADO CORRECTAMENTE.");
					verCerrarVentanaABMLimiteCaja(false);
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
				verCerrarEfectoCargando("");
			}
		}
	});
}

var limiteCajaMonto= "";
function obtenerUltimoLimiteCaja() {
	obtener_datos_user();
	const datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"funt": "obtenerUltimoLimiteCaja",
	}
	var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmgasto.php",
		type: "post",
		beforeSend: function () {
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
			ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					limiteCajaMonto = datos["2"];
					document.getElementById("inptLimitecaja").value = limiteCajaMonto;
					separadordemiles(document.getElementById("inptLimitecaja"));
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
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
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
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
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
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
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
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
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
		url: "/GoodVentaAsisCap/php_system/abmgasto.php",
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