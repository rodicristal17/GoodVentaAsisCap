
var cod_dictamenSeleccionado= "";
function verCerrarVentanaSeleccionDictamen(mostrar) {
    const ventanaSeleccion= document.getElementById('divSeleccionDictamenInterConsulta');

    if (!ventanaSeleccion) {
        return;
    }

    if (mostrar) {
        document.getElementById('divAbmDetallesInterConsulta').style.display= "none";
        ventanaSeleccion.style.display= "";
    } else {
        ventanaSeleccion.style.display= "none";
        document.getElementById('divAbmDetallesInterConsulta').style.display= "";
    }
}

function verificarCamposDictamen() {
    const resultado= document.getElementById('inptContenidoAbmMensaje').innerHTML.trim();

    if (!cod_interConsulta) {
        ver_vetana_informativa("Faltan datos", "No se encontró la interconsulta para registrar el dictamen.", "advertencia");
        return false;
    }
    if (!resultado) {
        ver_vetana_informativa("Falto ingresar un contenido");
        return false;
    }

    const asunto_dictamen= document.getElementById("inptAsuntoAbmInterConsulta").value;
    document.getElementById('btnDictamenContenidoAbmMensaje').disabled= true;
    abmDictamen(resultado, asunto_dictamen, 'solicitado');
}

function abmDictamen(resultado, asunto, estado= 'solicitado') {
    if (cod_dictamenSeleccionado) {
        if(controlacceso("CREARDICTAMEN","accion")==false){ return;}
    } else {
        if(controlacceso("EDITARDICTAMEN","accion")==false){ return;}
    }
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'nuevo/editar dictamen');
    datos.append("id", cod_dictamenSeleccionado);
    datos.append("resultado", resultado);
    datos.append("asunto", asunto);
    datos.append("estado", estado);
    datos.append("cod_interConsultaFK", cod_interConsulta);

    verCerrarEfectoCargando("1");
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmDictamen.php",
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
            document.getElementById('btnDictamenContenidoAbmMensaje').disabled= false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
            document.getElementById('btnDictamenContenidoAbmMensaje').disabled= false;
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
                    // Se evalua si se edito o se creo
                    if (cod_dictamenSeleccionado) {
                        buscarInterConsultasYContenido(cod_interConsulta);
                    } else {
                        cod_dictamenSeleccionado= datos["2"];
                        buscarListadoInterConsultas();
                        verCerrarVentanaSeleccionDictamen(true);
                    }
                    ver_vetana_informativa("Dictamen guardado.", "", "info");
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

function asignarMensajesDictamen() {
    const div= document.getElementById('divMensajesSelectDictamenInterConsulta');
    const mensajesSeleccionados= [];

    if (!cod_dictamenSeleccionado) {
        ver_vetana_informativa("Faltan datos", "Primero debe registrarse el dictamen.", "advertencia");
        return false;
    }

    // Obtiene un array solo de los mensajes seleccionados
    div.querySelectorAll('table').forEach(function(tabla) {
        tabla.querySelectorAll('tr').forEach(function(fila) {
            const checkbox= fila.querySelector('#td_datos_1 input[type="checkbox"]');
            const tdId= fila.querySelector('#td_id');

            if (checkbox && checkbox.checked && tdId) {
                mensajesSeleccionados.push(tdId.textContent.trim());
            }
        });
    });

    if (mensajesSeleccionados.length === 0) {
        ver_vetana_informativa("Faltan datos", "Seleccione al menos un mensaje para asignar al dictamen.", "advertencia");
        return false;
    }

    const asunto_dictamen= document.getElementById('inptAsuntoDictamenInterConsulta').value;
    if (!asunto_dictamen) {
        ver_vetana_informativa("Faltan datos", "El asunto del dictamen se asignará a los mensajes seleccionados. Si desea mantener el asunto actual de los mensajes, deje el campo de asunto vacío.", "advertencia");
        return false;
    }
    
    obtener_datos_user();
    var datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "asignarMensajesDictamen");
    datos.append("asunto", asunto_dictamen);
    datos.append("cod_mensajeFK", mensajesSeleccionados.join(";"));
    datos.append("cod_dictamen", cod_dictamenSeleccionado);

    verCerrarEfectoCargando("1");
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmDictamen.php",
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
			console.log(Respuesta);
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
                    ver_vetana_informativa("Mensajes asignados.", "", "info");
                    cod_dictamenSeleccionado= "";
                    document.getElementById('divMensajesSelectDictamenInterConsulta').innerHTML= "";
                    verCerrarVentanaSeleccionDictamen(false);
                    buscarInterConsultasYContenido(cod_interConsulta);
                    limpiarcamposMensaje();
				} else {
                    throw new Error("Error producido en eliminarMencionMensaje de JavaScript.");
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

function verCerrarInformeDictamen(mostrar, auditoria) {
    document.getElementById('divMinimizadoInformeDictamen').style.display= "none";

    if (mostrar) {
        if (auditoria) {
            document.getElementById('divInformeAuditoriaDictamen').style.display= "";
        } else {
            document.getElementById('divInformeDictamen').style.display= "";
        }
    } else {
        if (auditoria) {
            document.getElementById('divInformeAuditoriaDictamen').style.display= "none";
            document.getElementById('divInformeDictamen').style.display= "";
        } else {
            document.getElementById('divInformeDictamen').style.display= "none";
        }
    }
}

function minimizarInformeDictamen() {
    document.getElementById('divInformeDictamen').style.display= "none";
    document.getElementById('divMinimizadoInformeDictamen').style.display= "";
}

controldebusquedadInformeDictamen= true;
registrocargadoDictamen= 0;
totalregistroinformeDictamen= 0;
function cancelarInformeInterConsulta() {
	controldebusquedadInformeDictamen=false
	document.getElementById("divProgressInformeDictamen").style.backgroundColor='#ff5722'
}

function buscarInformeDictamenVista() {
    // Filtros
    const cod_dictamen= document.getElementById('inptInformeDictamen1').value;
    const asunto= document.getElementById('inptInformeDictamen2').value;
    const nombre_interconsulta= document.getElementById('inptInformeDictamen3').value;
    const estado= document.getElementById('inptInformeDictamen4').value;
    
    // Limpia variables y tabla
    registrocargadoInterConsulta= 0;
    registroInterConsultaAbierta= 0;
    document.getElementById('table_frm_VistaInterConsulta').innerHTML= paginacargando;
    
    buscarInformeDictamen(cod_dictamen, asunto, nombre_interconsulta, estado, 10)
}

function buscarInformeDictamen(cod_dictamen, asunto, interconsulta, estado, limite) {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'buscarDictamen');
    datos.append("cod_dictamen", cod_dictamen);
    datos.append("asunto", asunto);
    datos.append("interconsulta", interconsulta);
    datos.append("estado", estado);
    datos.append("limite", limite);

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmDictamen.php",
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
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
            controldebusquedadInformeDictamen= false;
		},
		success: function (responseText) {
            Respuesta = responseText;
			console.log(Respuesta)
			try {
                var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta) {
                    document.getElementById('table_InformeDictamen').innerHTML= datos["3"];

                    registrocargadoDictamen= Number(datos["4"]);
                    totalregistroinformeDictamen= Number(datos["5"]);
                    document.getElementById('inptTotalRegistoInformeDictamen').value= registrocargadoDictamen;
                    if(totalregistroinformeDictamen>registrocargadoDictamen){
                        var porce=((registrocargadoDictamen*100)/totalregistroinformeDictamen).toFixed(0)
                        document.getElementById("tbProcessInformeDictamen").style.display=""
                        document.getElementById("divProgressInformeDictamen").style.width=porce+"%"
                        document.getElementById("divProgressInformeDictamen").style.backgroundColor="rgb(76, 175, 80)";

                        controldebusquedadInformeDictamen=true;
                        limite= Number(limite) + 10;
                        buscarMasInformeDictamen(cod_dictamen, asunto, interconsulta, estado, ("10 OFFSET "+registrocargadoDictamen))
                    }else{
                        controldebusquedadInformeDictamen=false
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

function buscarMasInformeDictamen(cod_dictamen, asunto, interconsulta, estado, limite) {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'buscarDictamen');
    datos.append("cod_dictamen", cod_dictamen);
    datos.append("asunto", asunto);
    datos.append("interconsulta", interconsulta);
    datos.append("estado", estado);
    datos.append("limite", limite);

    if (!controldebusquedadInformeInterConsulta) {
        return;
    }

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmDictamen.php",
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
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
            controldebusquedadInformeDictamen= false;
		},
		success: function (responseText) {
            Respuesta = responseText;
			console.log(Respuesta)
			try {
                var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta) {
                    document.getElementById('table_InformeDictamen').innerHTML= datos["3"];

                    registrocargadoDictamen += Number(datos["4"]);
                    document.getElementById('inptTotalRegistoInformeDictamen').value= registrocargadoDictamen;
                    if(totalregistroinformeDictamen>registrocargadoDictamen){
                        var porce=((registrocargadoDictamen*100)/totalregistroinformeDictamen).toFixed(0)
                        document.getElementById("tbProcessInformeDictamen").style.display=""
                        document.getElementById("divProgressInformeDictamen").style.width=porce+"%"
                        document.getElementById("divProgressInformeDictamen").style.backgroundColor="rgb(76, 175, 80)";

                        controldebusquedadInformeDictamen=true;

                        buscarMasInformeDictamen(cod_dictamen, asunto, interconsulta, estado, ocultar_inactivos, ("10 OFFSET "+registrocargadoDictamen))
                    }else{
                        document.getElementById("tbProcessInformeDictamen").style.display="none"
                        controldebusquedadInformeDictamen=false
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

function obtenerDatosDictamen(elemento) {
    // Limpiar estilos
    $("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
    elemento.className= "tableRegistroSelec";

    // Datos de auditoria
    document.getElementById('inptDictamenUsuarioEmitidoPor').value= $(elemento).children('#td_datos_4').html();
    document.getElementById('inptDictamenFechaEmitidoPor').value= $(elemento).children('#td_datos_17').html();
    document.getElementById('inptDictamenUsuarioAutorizadoPor').value= $(elemento).children('#td_datos_18').html();
    document.getElementById('inptDictamenFechaAutorizadoPor').value= $(elemento).children('#td_datos_19').html();
    document.getElementById('inptDictamenUsuarioEjecutadoPor').value= $(elemento).children('#td_datos_20').html();
    document.getElementById('inptDictamenFechaEjecutadoPor').value= $(elemento).children('#td_datos_21').html();
    document.getElementById('btnAuditoriaInformeDictamen').style.backgroundColor= "";
}