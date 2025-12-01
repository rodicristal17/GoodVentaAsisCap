var cod_inventarioLocal= "";
var totalregistroinformeInsumoLocal= 0;
var registrocargadoInsumoLocal= 0;
var controldebusquedadInformeInsumoLocal= true;

function verCerrarAbmInventarioLocal(mostrar, abm) {
    if(controlacceso("VERLISTADOINVENTARIOLOCAL","accion")==false){ return;}
    if (mostrar) {
        document.getElementById("divAbmInventarioLocal").style.display="";
        if (abm) {
            $("div[id=divAbmInventarioLocal1]").fadeOut(250);
            $("div[id=divAbmInventarioLocal2]").fadeIn(250);
        } else {
            $("div[id=divAbmInventarioLocal1]").fadeIn(250);
        }
    } else {
        if (abm) {
            $("div[id=divAbmInventarioLocal2]").fadeOut(250);
            $("div[id=divAbmInventarioLocal1]").fadeIn(250);
        } else {
            $("div[id=divAbmInventarioLocal1]").fadeOut(250);
            document.getElementById("divAbmInventarioLocal").style.display="none";
            document.getElementById("divMinimizadoInventarioLocal").style.display= "none";
        }
    }
}

function minimizarabmInventarioLocal() {
    document.getElementById("divMinimizadoInventarioLocal").style.display= "";
    document.getElementById("divAbmInventarioLocal").style.display="none";
}

function obtenerVistaInformeInsumoLocal() {
    const cod_inventario= document.getElementById('inptBuscarAbmInventarioLocal1').value;
    const nombre= document.getElementById('inptBuscarAbmInventarioLocal2').value;
    const local= document.getElementById('inptBuscarAbmInventarioLocal3').value;
    const estado= document.getElementById('inptBuscarAbmInventarioLocal4').value;

    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "buscarVista");
    datos.append("cod_inventario", cod_inventario);
    datos.append("nombre", nombre);
    datos.append("local", local);
    datos.append("estado", estado);
    datos.append("limite", 10);

    // Limpiar opciones de seleccionado
    document.getElementById('btnEditarInventarioLocal').style.backgroundColor= "#b7b7b7";
    document.getElementById('btnEditarInventarioLocal').disabled= true;
    document.getElementById('inptRegistroSeleccInventarioLocal').value= "";

	verCerrarEfectoCargando("1");
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInventarioLocal.php",
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
			document.getElementById("table_abm_InventarioLocal").innerHTML= '';
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					document.getElementById("table_abm_InventarioLocal").innerHTML= datos["2"];
					document.getElementById("inptTotalRegistoInventarioLocal").value= datos["5"];
					
                    totalregistroinformeInsumoLocal= parseInt(datos["5"]);
					registrocargadoInsumoLocal= parseInt(datos["4"]);

					// Controla el progreso de la busqueda
					if(totalregistroinformeInsumoLocal>registrocargadoInsumoLocal){
						controldebusquedadInformeInsumoLocal=true;
						var porce=((registrocargadoInsumoLocal*100)/totalregistroinformeInsumoLocal).toFixed(0)
                        document.getElementById("tbProcessInformeInsumoLocal").style.display= "";
						document.getElementById("divProgressInformeInventarioLocal").style.width=porce+"%";

						obtenermasVistaInformeInsumoLocal();
					 }else{
                        document.getElementById("tbProcessInformeInsumoLocal").style.display= "none";
						controldebusquedadInformeInsumoLocal=false
					 }
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

function obtenermasVistaInformeInsumoLocal() {
    const cod_inventario= document.getElementById('inptBuscarAbmInventarioLocal1').value;
    const nombre= document.getElementById('inptBuscarAbmInventarioLocal2').value;
    const local= document.getElementById('inptBuscarAbmInventarioLocal3').value;
    const estado= document.getElementById('inptBuscarAbmInventarioLocal4').value;

    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "buscarVista");
    datos.append("cod_inventario", cod_inventario);
    datos.append("nombre", nombre);
    datos.append("local", local);
    datos.append("estado", estado);
    datos.append("limite", "10 OFFSET "+registrocargadoInsumoLocal);

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInventarioLocal.php",
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
					document.getElementById("table_InformeAsistencia").innerHTML += datos["2"];
					let totalMinutos= parseFloat(document.getElementById("inptTotalMinutosInformeAsistencia").value);
					document.getElementById("inptTotalMinutosInformeAsistencia").value= parseFloat(datos["6"]) + totalMinutos;
					registrocargadoinformeAsistencia += parseInt(datos["4"]);

					// Controla el progreso de la busqueda
					if(totalregistroinformeAsistencia>registrocargadoinformeAsistencia){
						var porce=((registrocargadoinformeAsistencia*100)/totalregistroinformeAsistencia).toFixed(0)
						document.getElementById("divProgressInformeAsistencia").style.width=porce+"%"
						//document.getElementById("table_InformeAsistencia").innerHTML += "<div id='table_mas_InformeAsistencia' style='width: 100%;'></div>"
						obtenermasVistaInformeAsistencia();
					 }else{
						controldebusquedadInformeAsistencia=false;
						document.getElementById("divProgressInformeAsistencia").style.display="none"
                        document.getElementById("tbProcessInformeInsumoLocal").style.display= "none";
					 }
				}
			} catch (error) {
				controldebusquedadInformeAsistencia=false;
				document.getElementById("divProgressInformeAsistencia").style.backgroundColor='#ff5722'

                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function cancelarInformeInventarioLocal() {
	controldebusquedadInformeInsumoLocal=false
	document.getElementById("divProgressInformeInventarioLocal").style.backgroundColor='#ff5722'
}

function obtenerDatosInsumoLocal(datostr) {
    // Limpieza de clases de otros registros
    $("tr[id=tbSelecRegistro]").each(function(i, td){		
        td.className=''
    });
    datostr.className='tableRegistroSelec'

    // Obtiene datos y asigna
    let urlFoto1= $(datostr).children('td[id="td_datos_10"]').html();
    let urlFoto2= $(datostr).children('td[id="td_datos_11"]').html();
    let urlFoto3= $(datostr).children('td[id="td_datos_12"]').html();
    
    urlFoto1= (!urlFoto1) ? '/GoodVentaAsisCap/iconos/imagenphoto.png' : urlFoto1;
    urlFoto2= (!urlFoto2) ? '/GoodVentaAsisCap/iconos/imagenphoto.png' : urlFoto2;
    urlFoto3= (!urlFoto3) ? '/GoodVentaAsisCap/iconos/imagenphoto.png' : urlFoto3;

    cod_inventarioLocal= $(datostr).children('td[id="td_id"]').html();
    document.getElementById('inptCodigoInventarioInsumo').value= cod_inventarioLocal.toString().padStart(3, "0");
    document.getElementById('inptRegistroSeleccInventarioLocal').value= $(datostr).children('td[id="td_id"]').html();
    document.getElementById('inptNombreInventarioInsumo').value= $(datostr).children('td[id="td_datos_1"]').html();
    document.getElementById('inptDescripcionInventarioInsumo').value= $(datostr).children('td[id="td_datos_2"]').html();
    document.getElementById('inptCantidadInventarioInsumo').value= $(datostr).children('td[id="td_datos_5"]').html();
    document.getElementById('inptCostoInventarioInsumo').value= $(datostr).children('td[id="td_datos_6"]').html();
    document.getElementById('inptLocalInventarioInsumo').value= $(datostr).children('td[id="td_datos_8"]').html();
    document.getElementById('inptEstadoInventarioInsumo').value= $(datostr).children('td[id="td_datos_4"]').html().toLowerCase();
    document.getElementById('inptObservacionInventarioInsumo').innerHTML= $(datostr).children('td[id="td_datos_7"]').html();
    document.getElementById('imgfotoInventarioLocal1').style.backgroundImage= "url("+ urlFoto1 +")";
    document.getElementById('imgfotoInventarioLocal2').style.backgroundImage= "url("+ urlFoto2 +")";
    document.getElementById('imgfotoInventarioLocal3').style.backgroundImage= "url("+ urlFoto3 +")";
    document.getElementById('btnEditarInventarioLocal').style.backgroundColor= "rgb(33, 150, 243)";
    document.getElementById('btnEditarInventarioLocal').disabled= false;
}

function consultarUltimoIdInventarioLocal() {
    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "obtenerUltimoId");

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInventarioLocal.php",
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
                    document.getElementById('inptCodigoInventarioInsumo').value = datos["2"].toString().padStart(3, "0");
				} else {
                    throw new Error("Error producido en consultarUltimoIdInventarioLocal de JavaScript.");
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

function limpiarcamposInventarioLocal() {
    consultarUltimoIdInventarioLocal();
    cod_inventarioLocal= "";
    document.getElementById('inptNombreInventarioInsumo').value= "";
    document.getElementById('inptDescripcionInventarioInsumo').value= "";
    document.getElementById('inptCantidadInventarioInsumo').value= 1;
    document.getElementById('inptCostoInventarioInsumo').value= "";
    document.getElementById('inptLocalInventarioInsumo').value= "";
    document.getElementById('inptEstadoInventarioInsumo').value= "activo";
    document.getElementById('inptObservacionInventarioInsumo').innerHTML= "";
    document.getElementById('imgfotoInventarioLocal1').style.backgroundImage= "url("+ '/GoodVentaAsisCap/iconos/imagenphoto.png' +")";
    document.getElementById('imgfotoInventarioLocal2').style.backgroundImage= "url("+ '/GoodVentaAsisCap/iconos/imagenphoto.png' +")";
    document.getElementById('imgfotoInventarioLocal3').style.backgroundImage= "url("+ '/GoodVentaAsisCap/iconos/imagenphoto.png' +")";
}

function verificarCamposInventarioLocal() {
    // Verifica los permisos
    if (cod_inventarioLocal == "") {
        if(controlacceso("CREARLISTADOINVENTARIOLOCAL","accion")==false){ return;}
    } else {
        if(controlacceso("EDITARLISTADOINVENTARIOLOCAL","accion")==false){ return;}
    }
    const nombre= document.getElementById('inptNombreInventarioInsumo').value;
    const descripcion= document.getElementById('inptDescripcionInventarioInsumo').value;
    const estado= document.getElementById('inptEstadoInventarioInsumo').value;
    const cantidad= document.getElementById('inptCantidadInventarioInsumo').value;
    const costo= document.getElementById('inptCostoInventarioInsumo').value;
    const observacion= document.getElementById('inptObservacionInventarioInsumo').value;
    const cod_localFK= document.getElementById('inptLocalInventarioInsumo').value;

    if (!cod_localFK) {
        ver_vetana_informativa("Falto seleccionar un local");
        return false;
    }
    if (!nombre) {
        ver_vetana_informativa("Falto ingresar un nombre");
        return false;
    }

    abmInventarioLocal(nombre,descripcion,estado,cantidad,costo,observacion,cod_localFK);
}

function abmInventarioLocal(nombre,descripcion,estado,cantidad,costo,observacion,cod_localFK) {
    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "nuevo/editar");
    datos.append("cod_inventario", cod_inventarioLocal);
    datos.append("nombre", nombre);
    datos.append("cod_localFK", cod_localFK);
    datos.append("estado", estado);
    datos.append("descripcion", descripcion);
    datos.append("cantidad", cantidad);
    datos.append("costo", costo);
    datos.append("observacion", observacion);

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInventarioLocal.php",
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
					subirImagenes();
                    obtenerVistaInformeInsumoLocal();
                    verCerrarAbmInventarioLocal(false, true);
				} else {
                    throw new Error("Error producido en abmInventarioLocal de JavaScript.");
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

var fotoInventario1= "";
var extInventario1= "";
var fotoInventario2= "";
var extInventario2= "";
var fotoInventario3= "";
var extInventario3= "";
function subirImagenes() {
    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "cargar_imagen");
    datos.append("cod_inventario", cod_inventarioLocal);
    datos.append("fotos[]", fotoInventario1);
    datos.append("exts[]", extInventario1);
    datos.append("fotos[]", fotoInventario2);
    datos.append("exts[]", extInventario2);
    datos.append("fotos[]", fotoInventario3);
    datos.append("exts[]", extInventario3);

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInventarioLocal.php",
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
				} else {
                    throw new Error("Error producido en subirImagenes de JavaScript.");
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