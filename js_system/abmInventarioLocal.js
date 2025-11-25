var cod_inventarioLocal= "";
var totalregistroinformeInsumoLocal= 0;
var registrocargadoInsumoLocal= 0;
var controldebusquedadInformeInsumoLocal= true;

function verCerrarAbmInventarioLocal(mostrar, abm) {
    if(controlacceso("VERLISTADOINVENTARIOLOCAL","accion")==false){ return;}
    if (mostrar) {
        document.getElementById("divAbmInventarioLocal").style.display="";
        if (abm) {
            if(controlacceso("EDITARLISTADOINVENTARIOLOCAL","accion")==false){ return;}
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
    $("tr[id=tbSelecRegistro]").each(function(i, td){		
        td.className=''
    });
    datostr.className='tableRegistroSelec'
    cod_inventarioLocal= $(datostr).children('td[id="td_id"]').html();
    document.getElementById('inptCodigoInventarioInsumo').value= cod_inventarioLocal.toString().padStart(6, "0");
    document.getElementById('inptRegistroSeleccInventarioLocal').value= $(datostr).children('td[id="td_id"]').html();
    document.getElementById('inptNombreInventarioInsumo').value= $(datostr).children('td[id="td_datos_1"]').html();
    document.getElementById('inptDescripcionInventarioInsumo').value= $(datostr).children('td[id="td_datos_2"]').html();
    document.getElementById('inptCantidadInventarioInsumo').value= $(datostr).children('td[id="td_datos_5"]').html();
    document.getElementById('inptCostoInventarioInsumo').value= $(datostr).children('td[id="td_datos_6"]').html();
    document.getElementById('inptLocalInventarioInsumo').value= $(datostr).children('td[id="td_datos_8"]').html();
    document.getElementById('inptEstadoInventarioInsumo').value= $(datostr).children('td[id="td_datos_4"]').html().toLowerCase();
    document.getElementById('inptObservacionInventarioInsumo').innerHTML= $(datostr).children('td[id="td_datos_7"]').html();
    document.getElementById('btnEditarInventarioLocal').style.backgroundColor= "rgb(33, 150, 243)";
    document.getElementById('btnEditarInventarioLocal').disabled= false;
}

function limpiarcamposInventarioLocal() {
    cod_inventarioLocal= "";
    document.getElementById('inptCodigoInventarioInsumo').value= "";
    document.getElementById('inptNombreInventarioInsumo').value= "";
    document.getElementById('inptDescripcionInventarioInsumo').value= "";
    document.getElementById('inptCantidadInventarioInsumo').value= 1;
    document.getElementById('inptCostoInventarioInsumo').value= "";
    document.getElementById('inptLocalInventarioInsumo').value= "";
    document.getElementById('inptEstadoInventarioInsumo').value= "activo";
    document.getElementById('inptObservacionInventarioInsumo').innerHTML= "";
}

function verificarCamposInventarioLocal() {
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
					ver_vetana_informativa("Datos guardados exitosamente.");
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