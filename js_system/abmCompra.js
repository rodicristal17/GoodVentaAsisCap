/* 
    divEliminados:
    - divAbmCompra
    - divInformeProductosComprados
*/
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
		url: "/GoodVentaAsisCap/php_system/abmcompra.php",
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
	var inptprecioListaProductoCompra = document.getElementById('inptprecioListaProductoCompra').value
	var inptNrocompra = document.getElementById('inptNrocompra').value
	var inptFechaCompra = document.getElementById('inptFechaCompra').value
	var inptProveedorCompra = document.getElementById('inptProveedorCompra').value
	var inptlocalCompra = document.getElementById('inptlocalCompra').value
	var inptPagadocompra1 = document.getElementById('inptPagadocompra1').value
	var inptPagadocompra2 = document.getElementById('inptPagadocompra2').value
	var inptDescuentocompra = document.getElementById('inptDescuentocompra').value
	
	
	var inptTipoCompra = document.getElementById('inptTipoCompra').value
	var inptTimbradocompra = document.getElementById('inptTimbradocompra').value
	var inptTipoFacturaCompra = document.getElementById('inptTipoFacturaCompra').value
	
	var editPrecioLista = ""
	
	if(document.getElementById('inptSeleccCambiarPrecio').checked==true){
		editPrecioLista="si"
	}else{
		editPrecioLista="no"
	}
	
	if (inptCantProductoCompra == "" || inptCantProductoCompra == "0") {
		ver_vetana_informativa("FALTO INGRESAR LA CANTIDAD", "#")
		return false;
	}
	
	if (inptprecioListaProductoCompra == "") {
		ver_vetana_informativa("FALTO INGRESAR EL PRECIO DE LISTA", "#")
		return false;
	}
	
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
	abmDetalleCompra(inptTipoCompra,inptTimbradocompra,inptTipoFacturaCompra,editPrecioLista,inptprecioListaProductoCompra,inptNrocompra,inptFechaCompra,codProveedorCompra,inptlocalCompra,inptPagadocompra1,inptPagadocompra2,inptDescuentocompra,idAbmCompra, idFkProductocompra, inptCantProductoCompra, inptCostoProductoCompra, idDetalleCompra, accion);
}
function abmDetalleCompra(tipocompra,timbrado,tipofactura,editPrecioLista,precioLista,num_comprobante,fecha_compra,cod_proveedorFK,cod_local,pagado1,pagado2,descuento,cod_compraFK, cod_productoFK, cantidad_detalle_compra, precio_producto, cod_detalle_compra, accion) {
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
	datos.append("precioLista", precioLista)
	datos.append("editPrecioLista", editPrecioLista)
	datos.append("tipocompra", tipocompra)
	datos.append("timbrado", timbrado)
	datos.append("tipofactura", tipofactura)
	var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmdetallecompra.php",
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
		url: "/GoodVentaAsisCap/php_system/abmdetallecompra.php",
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
		url: "/GoodVentaAsisCap/php_system/abmcompra.php",
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
		url: "/GoodVentaAsisCap/php_system/abmcompra.php",
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
		url: "/GoodVentaAsisCap/php_system/abmcompra.php",
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
			
	abmDetalleCompra("0","0","0","0","0","0","0","0",inptlocalCompra,"0","0","0",idAbmCompra, codproductodetalleSelectCompra, cantidaDetalleSelecCompra, "0", idDetalleCompra, "quitar");
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
	
	document.getElementById('inptTimbradocompra').value = ""
	document.getElementById('inptTipoCompra').value = "CONTADO"
	document.getElementById('inptTipoFacturaCompra').value = "FACTURA LEGAL"
	
	document.getElementById('inptprecioListaProductoCompra').value = ""
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
		url: "/GoodVentaAsisCap/php_system/abmcompra.php",
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
		url: "/GoodVentaAsisCap/php_system/abmcompra.php",
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
	document.getElementById('inptTipoCompra').value = $(datostr).children('td[id="td_datos_13"]').html()	
	document.getElementById('inptTimbradocompra').value = $(datostr).children('td[id="td_datos_14"]').html()	
	document.getElementById('inptTipoFacturaCompra').value = $(datostr).children('td[id="td_datos_15"]').html()	
	codProveedorCompra = $(datostr).children('td[id="td_datos_6"]').html()
	idAbmCompra = $(datostr).children('td[id="td_datos_5"]').html();
	document.getElementById("btnAbmCompra").value = "Editar Datos"
	document.getElementById("btnAbmCompra").style.display = ""
	buscardetallescompra()
	document.getElementById("divVistaCompras").style.display = "none";
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
		url: "/GoodVentaAsisCap/php_system/abmdetallecompra.php",
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
		url: "/GoodVentaAsisCap/php_system/abmdetallecompra.php",
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
